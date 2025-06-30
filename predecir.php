<?php
// Encabezado para indicar que la respuesta es JSON
header('Content-Type: application/json; charset=utf-8');

// Obtener evaluation_id desde la URL
if (!isset($_GET['evaluation_id'])) {
    echo json_encode(["error" => "Debe proporcionar un evaluation_id"]);
    exit();
}

$evaluation_id = intval($_GET['evaluation_id']);

// Ajusta la ruta a python3 y al script predecir.py según tu entorno
$command = "/home/jfrez/miniconda3/bin/python /var/www/html/protege2/predecir.py {$evaluation_id}";
// Ejecutar script Python
// Redirigir errores estándar (stderr) a la salida estándar (stdout)
$output = shell_exec("$command 2>&1");

// Mostrar el comando ejecutado y su salida
if ($output === null) {
    // Error al ejecutar el script o no produce salida
    echo json_encode(["error" => "Error al ejecutar el script de predicción."]);
} else {
    // Eliminar espacios extra del output
    $output = trim($output);

    // Crear arreglo asociativo con la respuesta
    $response = [
        "evaluation_id" => $evaluation_id,
        "prediccion" => $output
    ];

    // Convertir a JSON y mostrar
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}
