#!/opt/php
<?php

declare(ticks = 1);

// This invokes Composer's autoloader so that we'll be able to use Guzzle and any other 3rd party libraries we need.
require __DIR__ . '/../vendor/autoload.php';

function register(): string
{
    echo '[extension] registering'.PHP_EOL;
    $client = new \GuzzleHttp\Client();
    $response = $client->post(
        'http://' . $_ENV['AWS_LAMBDA_RUNTIME_API'] . '/2020-01-01/extension/register',
            [
                'json' => [
                    'events' => ['INVOKE', 'SHUTDOWN'],
                ],
                'headers' => [
                    'Lambda-Extension-Name' => 'extension.php',
                ],
            ]
    );

    echo '[extension] [debug] extension api response : '.json_encode([
            'headers' => $response->getHeaders(),
            'body' => (string) $response->getBody(),
   ]).PHP_EOL;

    return $response->getHeaderLine('Lambda-Extension-Identifier');
}

function processEvents(string $extensionId)
{
    $headers = [
        'Lambda-Extension-Identifier' => $extensionId,
    ];

    $client = new \GuzzleHttp\Client();

    while(1) {
        echo "[extension] waiting for event...".PHP_EOL;
        $response = $client->get(
            'http://'.$_ENV['AWS_LAMBDA_RUNTIME_API'].'/2020-01-01/extension/event/next',
            ['headers' => $headers],
        );

        $payload = json_decode((string) $response->getBody(), true);
        if ($payload['eventType'] === 'SHUTDOWN') {
            echo '[extension] Received shutdown : '.((string) $response->getBody()).PHP_EOL;
            exit(0);
        } else {
            echo '[extension] Processed event : '.((string) $response->getBody()).PHP_EOL;
        }
    }
}


function onSignal($signo)
{
    switch ($signo) {
        case SIGINT:
            echo "[extension] runtime SIGITN...".PHP_EOL;
            break;
        case SIGKILL:
            echo "[extension] runtime SIGKILL...".PHP_EOL;
            break;
    }
    exit(0);
}

function main()
{
    echo '[extension] starting'.PHP_EOL;
    $extensionId = register();
    processEvents($extensionId);
}

//pcntl_signal(SIGINT, 'onSignal');
//pcntl_signal(SIGKILL, 'onSignal');
main();