<?php

$input = json_decode(file_get_contents('php://input'), true);

$riesgo = $input['riesgo'];

$command = '/usr/local/bin/python3 predict.py';
$inputJson = json_encode(['riesgo' => $riesgo]);

// Execute the Python script and capture the output
$descriptorspec = [
    0 => ["pipe", "r"],  // stdin is a pipe that the child will read from
    1 => ["pipe", "w"],  // stdout is a pipe that the child will write to
    2 => ["pipe", "w"]   // stderr is a pipe to write errors to
];
$process = proc_open($command, $descriptorspec, $pipes);
if (is_resource($process)) {
    fwrite($pipes[0], $inputJson);
    fclose($pipes[0]);

    $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    $error = stream_get_contents($pipes[2]);
    fclose($pipes[2]);

    proc_close($process);

    if ($error) {
        // Handle error (for example, log it or return a message)
        echo json_encode(['error' => 'Error executing Python script: ' . $error]);
    } else {
        // Return the prediction as JSON
        header('Content-Type: application/json');
        echo $output;
    }
} else {
    echo json_encode(['error' => 'Could not open process.']);
}
?>