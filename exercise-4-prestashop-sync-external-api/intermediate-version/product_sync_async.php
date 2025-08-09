<?php
declare(strict_types=1);

// Intermediate (async HTTP) demo using curl_multi.
// No PrestaShop, DB, or external deps required.
// Run: php product_sync_async.php

const ASYNC_API_URL = 'https://httpbin.org/post';
const ASYNC_API_TOKEN = 'TEST_TOKEN';
const ASYNC_LOG_FILE = __DIR__ . '/async.log';

function log_message_async(string $message): void
{
    $timestamp = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('c');
    $line = "[$timestamp] $message\n";
    echo $line;
    file_put_contents(ASYNC_LOG_FILE, $line, FILE_APPEND);
}

function buildProductPayloadAsync(array $product): array
{
    return [
        'id'    => (int)($product['id'] ?? 0),
        'name'  => (string)($product['name'] ?? 'Unnamed'),
        'price' => (float)($product['price'] ?? 0.0),
        'stock' => (int)($product['stock'] ?? 0),
    ];
}

/**
 * Fire-and-forget style using curl_multi; we still drive the event loop
 * for a short grace window, but we do not block the caller significantly.
 */
final class AsyncHttpClient
{
    private $multiHandle;
    private array $handles = [];

    public function __construct()
    {
        $this->multiHandle = curl_multi_init();
    }

    public function __destruct()
    {
        foreach ($this->handles as $h) {
            curl_multi_remove_handle($this->multiHandle, $h);
            curl_close($h);
        }
        curl_multi_close($this->multiHandle);
    }

    public function postAsync(string $url, array $payload, array $headers = []): void
    {
        $ch = curl_init($url);
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $headers = array_merge(['Content-Type: application/json', 'Accept: application/json'], $headers);

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_TIMEOUT => 10,
        ]);

        $this->handles[(int)$ch] = $ch;
        curl_multi_add_handle($this->multiHandle, $ch);
    }

    public function driveEventLoop(int $maxMillis = 100): void
    {
        $start = (int)(microtime(true) * 1000);
        do {
            $active = 0;
            $status = curl_multi_exec($this->multiHandle, $active);
            while ($info = curl_multi_info_read($this->multiHandle)) {
                $ch = $info['handle'];
                $errno = curl_errno($ch);
                $error = $errno ? curl_error($ch) : null;
                $statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $id = (int)$ch;
                if ($error) {
                    log_message_async("Async: request $id failed: $error");
                } else {
                    log_message_async("Async: request $id completed with HTTP $statusCode");
                }
                curl_multi_remove_handle($this->multiHandle, $ch);
                curl_close($ch);
                unset($this->handles[$id]);
            }

            if ($active) {
                curl_multi_select($this->multiHandle, 0.05);
            }
        } while ($active && ((int)(microtime(true) * 1000) - $start) < $maxMillis);

        // Anything not done yet will continue in background if PHP stays alive;
        // for a CLI demo, we just exit after the grace window.
    }
}

// Simulated PrestaShop hook using async client
function hookActionObjectProductAddAfterAsync(array $params): void
{
    static $client = null;
    if ($client === null) {
        $client = new AsyncHttpClient();
    }

    $product = $params['object'] ?? [];
    $payload = buildProductPayloadAsync($product);

    log_message_async('Async: scheduling product sync (non-blocking)...');
    $client->postAsync(
        ASYNC_API_URL,
        $payload,
        ['Authorization: Bearer ' . ASYNC_API_TOKEN]
    );

    // Drive a very small loop so we demonstrate completion logs without blocking
    $client->driveEventLoop(150);
}

// Demo entrypoint
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $demoProduct = [
        'id'    => 456,
        'name'  => 'Async Demo Product',
        'price' => 29.90,
        'stock' => 7,
    ];

    log_message_async('--- ASYNC DEMO START ---');
    log_message_async('Simulating product creation...');
    hookActionObjectProductAddAfterAsync(['object' => $demoProduct]);
    log_message_async('Returned immediately from hook (HTTP running asynchronously).');
    log_message_async('--- ASYNC DEMO END ---');
}


