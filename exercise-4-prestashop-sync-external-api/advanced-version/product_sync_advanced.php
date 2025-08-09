<?php
declare(strict_types=1);

// Advanced (Hook & Worker) demo with file-based queue, retries, and DLQ.
// No PrestaShop, DB, or external services required.
// Run producer (hook simulation): php product_sync_advanced.php produce
// Run worker: php product_sync_advanced.php work

const ADV_API_URL = 'https://httpbin.org/post';
const ADV_API_TOKEN = 'TEST_TOKEN';
const ADV_BASE_DIR = __DIR__;
const ADV_QUEUE_DIR = ADV_BASE_DIR . '/queue';
const ADV_WORK_DIR = ADV_BASE_DIR . '/work';
const ADV_DLQ_DIR = ADV_BASE_DIR . '/dlq';
const ADV_LOG_FILE = ADV_BASE_DIR . '/advanced.log';

const ADV_MAX_RETRIES = 3;
const ADV_BACKOFF_SECONDS = [1, 2, 4]; // exponential-like

function adv_log(string $message): void
{
    $timestamp = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('c');
    $line = "[$timestamp] $message\n";
    echo $line;
    file_put_contents(ADV_LOG_FILE, $line, FILE_APPEND);
}

function adv_bootstrap_dirs(): void
{
    foreach ([ADV_QUEUE_DIR, ADV_WORK_DIR, ADV_DLQ_DIR] as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }
}

function adv_build_payload(array $product): array
{
    return [
        'id'    => (int)($product['id'] ?? 0),
        'name'  => (string)($product['name'] ?? 'Unnamed'),
        'price' => (float)($product['price'] ?? 0.0),
        'stock' => (int)($product['stock'] ?? 0),
    ];
}

function adv_enqueue(array $payload): string
{
    $msg = [
        'payload' => $payload,
        'idempotency_key' => 'product:' . $payload['id'] . ':' . time(),
        'created_at' => gmdate('c'),
        'attempts' => 0,
        'next_attempt_at' => time(),
    ];
    $id = bin2hex(random_bytes(8));
    $file = ADV_QUEUE_DIR . "/$id.json";
    file_put_contents($file, json_encode($msg, JSON_PRETTY_PRINT));
    return $file;
}

function adv_pop_message(): ?string
{
    $files = glob(ADV_QUEUE_DIR . '/*.json');
    if (!$files) {
        return null;
    }
    sort($files);
    foreach ($files as $file) {
        $content = json_decode((string)file_get_contents($file), true);
        if (!is_array($content)) {
            // corrupt -> DLQ
            rename($file, ADV_DLQ_DIR . '/' . basename($file));
            continue;
        }
        if (($content['next_attempt_at'] ?? 0) > time()) {
            continue; // not yet time
        }
        $workPath = ADV_WORK_DIR . '/' . basename($file);
        if (@rename($file, $workPath)) {
            return $workPath;
        }
    }
    return null;
}

function adv_ack(string $workFile): void
{
    @unlink($workFile);
}

function adv_retry_or_fail(string $workFile, string $error): void
{
    $data = json_decode((string)file_get_contents($workFile), true);
    if (!is_array($data)) {
        rename($workFile, ADV_DLQ_DIR . '/' . basename($workFile));
        return;
    }
    $attempts = (int)($data['attempts'] ?? 0);
    $attempts++;
    $data['attempts'] = $attempts;
    $data['last_error'] = $error;

    if ($attempts >= ADV_MAX_RETRIES) {
        adv_log('Advanced: moving message to DLQ after max retries: ' . basename($workFile));
        rename($workFile, ADV_DLQ_DIR . '/' . basename($workFile));
        return;
    }

    $backoff = ADV_BACKOFF_SECONDS[min($attempts - 1, count(ADV_BACKOFF_SECONDS) - 1)];
    $data['next_attempt_at'] = time() + $backoff;

    $queuePath = ADV_QUEUE_DIR . '/' . basename($workFile);
    file_put_contents($queuePath, json_encode($data, JSON_PRETTY_PRINT));
    @unlink($workFile);
}

function adv_http_post(string $url, array $payload, string $token, string $idempotencyKey): array
{
    $ch = curl_init($url);
    $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $token,
        'Idempotency-Key: ' . $idempotencyKey,
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

// Simulated hook (producer)
function hookActionObjectProductAddAfterAdvanced(array $params): void
{
    $product = $params['object'] ?? [];
    $payload = adv_build_payload($product);
    $file = adv_enqueue($payload);
    adv_log('Advanced: enqueued payload -> ' . basename($file));
}

// Worker
function adv_worker_once(): bool
{
    $workFile = adv_pop_message();
    if ($workFile === null) {
        return false; // nothing to do
    }

    $message = json_decode((string)file_get_contents($workFile), true);
    if (!is_array($message)) {
        rename($workFile, ADV_DLQ_DIR . '/' . basename($workFile));
        return true;
    }

    try {
        $res = adv_http_post(ADV_API_URL, $message['payload'], ADV_API_TOKEN, $message['idempotency_key']);
        if ($res['error']) {
            throw new RuntimeException($res['error']);
        }
        if ($res['status'] >= 400) {
            throw new RuntimeException('HTTP ' . $res['status'] . ' body=' . substr((string)$res['body'], 0, 200));
        }
        adv_log('Advanced: processed ' . basename($workFile) . ' with HTTP ' . $res['status']);
        adv_ack($workFile);
    } catch (Throwable $e) {
        adv_log('Advanced: error processing ' . basename($workFile) . ': ' . $e->getMessage());
        adv_retry_or_fail($workFile, $e->getMessage());
    }

    return true;
}

// Entrypoint
adv_bootstrap_dirs();

if (PHP_SAPI === 'cli') {
    $cmd = $argv[1] ?? '';
    if ($cmd === 'produce') {
        adv_log('--- ADVANCED PRODUCER DEMO START ---');
        $demo = [
            'id' => 789,
            'name' => 'Advanced Demo Product',
            'price' => 99.99,
            'stock' => 3,
        ];
        hookActionObjectProductAddAfterAdvanced(['object' => $demo]);
        adv_log('--- ADVANCED PRODUCER DEMO END ---');
        exit(0);
    }
    if ($cmd === 'work') {
        adv_log('--- ADVANCED WORKER START ---');
        $idleRounds = 0;
        while (true) {
            $didWork = adv_worker_once();
            if (!$didWork) {
                $idleRounds++;
                if ($idleRounds > 10) { // exit after some idle time in demo
                    adv_log('Worker idle; exiting demo.');
                    break;
                }
                usleep(200_000); // 200ms
            } else {
                $idleRounds = 0;
            }
        }
        adv_log('--- ADVANCED WORKER END ---');
        exit(0);
    }
}

// Help text
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    echo "Usage:\n";
    echo "  php product_sync_advanced.php produce  # enqueue a demo payload\n";
    echo "  php product_sync_advanced.php work     # run worker to process queue\n";
}


