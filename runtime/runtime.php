<?php

declare(ticks = 1);

// This invokes Composer's autoloader so that we'll be able to use Guzzle and any other 3rd party libraries we need.
require __DIR__ . '/vendor/autoload.php';

function getNextRequest()
{
    echo '[runtime] waiting for next invvocation'.PHP_EOL;
    $client = new \GuzzleHttp\Client();
    $response = $client->get('http://' . $_ENV['AWS_LAMBDA_RUNTIME_API'] . '/2018-06-01/runtime/invocation/next');

    return [
        'headers' => $response->getHeaders(),
        'invocationId' => $response->getHeader('Lambda-Runtime-Aws-Request-Id')[0],
        'payload' => json_decode((string) $response->getBody(), true)
    ];
}

function sendResponse($invocationId, $response)
{
    $client = new \GuzzleHttp\Client();
    $client->post(
        'http://' . $_ENV['AWS_LAMBDA_RUNTIME_API'] . '/2018-06-01/runtime/invocation/' . $invocationId . '/response',
        ['body' => $response]
    );
}

function onSignal($signo)
{
    switch ($signo) {
        case SIGINT:
            echo "[runtime] runtime SIGITN...".PHP_EOL;
            break;
        case SIGKILL:
            echo "[runtime] runtime SIGKILL...".PHP_EOL;
            break;
    }
    exit(0);
}

//pcntl_signal(SIGINT, 'onSignal');
//pcntl_signal(SIGKILL, 'onSignal');

echo '[runtime] starting'.PHP_EOL;
// This is the request processing loop. Barring unrecoverable failure, this loop runs until the environment shuts down.
do {
    // Ask the runtime API for a request to handle.
    $request = getNextRequest();

    // Obtain the function name from the _HANDLER environment variable and ensure the function's code is available.
    $handlerFunction = array_slice(explode('.', $_ENV['_HANDLER']), -1)[0];
    require_once $_ENV['LAMBDA_TASK_ROOT'] . '/src/lambda/' . $handlerFunction . '.php';

    echo '[runtime] invoking lambda function : '.json_encode($request).PHP_EOL;
    // Execute the desired function and obtain the response.
    $response = $handlerFunction($request['payload']);


    echo '[runtime] sending  lambda response : '.json_encode($response).PHP_EOL;
    // Submit the response back to the runtime API.
    sendResponse($request['invocationId'], $response);
} while (true);