<?php
declare(strict_types=1);

// Simple (synchronous) demo for Exercise 4
// No PrestaShop, DB, or external deps required.
// Run: php product_sync_simple.php

const SIMPLE_API_URL = 'https://httpbin.org/post'; // change if desired
const SIMPLE_API_TOKEN = 'TEST_TOKEN';
const SIMPLE_LOG_FILE = __DIR__ . '/simple.log';

function log_message(string $message): void
{
    $timestamp = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('c');
    $line = "[$timestamp] $message\n";
    echo $line;
    file_put_contents(SIMPLE_LOG_FILE, $line, FILE_APPEND);
}

function buildProductPayload(array $product): array
{
    return [
        'id'    => (int)($product['id'] ?? 0),
        'name'  => (string)($product['name'] ?? 'Unnamed'),
        'price' => (float)($product['price'] ?? 0.0),
        'stock' => (int)($product['stock'] ?? 0),
    ];
}

function send_post_sync(string $url, array $payload, string $token): array
{
    $ch = curl_init($url);
    $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $token,
    ];

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => $json,
        CURLOPT_TIMEOUT => 10,
    ]);

    $responseBody = curl_exec($ch);
    $errno = curl_errno($ch);
    $error = $errno ? curl_error($ch) : null;
    $statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'status' => $statusCode,
        'error'  => $error,
        'body'   => $responseBody,
    ];
}

// Simulated PrestaShop hook: actionObjectProductAddAfter
function hookActionObjectProductAddAfter(array $params): void
{
    $product = $params['object'] ?? [];
    $payload = buildProductPayload($product);

    log_message('Simple: sending product sync (synchronous)...');
    $res = send_post_sync(SIMPLE_API_URL, $payload, SIMPLE_API_TOKEN);

    if ($res['error']) {
        log_message('Simple: sync failed: ' . $res['error']);
        return;
    }

    log_message('Simple: sync success with HTTP ' . $res['status']);
}

// Demo entrypoint
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $demoProduct = [
        'id'    => 123,
        'name'  => 'Demo Product',
        'price' => 49.90,
        'stock' => 15,
    ];

    log_message('--- SIMPLE DEMO START ---');
    log_message('Simulating product creation...');
    hookActionObjectProductAddAfter(['object' => $demoProduct]);
    log_message('Product creation finished (note: this waited for HTTP).');
    log_message('--- SIMPLE DEMO END ---');
}


