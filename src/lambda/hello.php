<?php

/**
 * @throws JsonException
 */
function hello($data)
{
    echo '[function] processing ...'.PHP_EOL;

    $descriptorSpec = [
        ["pipe", "r"],
        ["pipe", "w"],
        ["pipe", "w"],
    ];

    $process = proc_open($data['command'] ?? "whoami", $descriptorSpec, $pipes, "/tmp", null);

    list($stdinPipe, $stdoutPipe, $stderrPipe) = $pipes;
    fclose($stdinPipe);

    $stdout = stream_get_contents($stdoutPipe);
    echo "stream : ".$stdout.PHP_EOL;

    $returnValue = proc_close($process);
    echo "return value proc : ".$returnValue.PHP_EOL;


    return json_encode([
        'stdout' => base64_encode($stdout),
        'stderr' => base64_encode($stderrPipe),
        'error' => '',
    ], JSON_THROW_ON_ERROR);
}