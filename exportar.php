<?php
session_start();
include_once("config.php");

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['userid'])) {
    echo "Error: Debes iniciar sesión para acceder a esta página.";
    exit();
}

// Obtener el tipo de exportación
$tipo = $_GET['tipo'] ?? '';

if (!in_array($tipo, ['bruto', 'anonimizado'])) {
    echo "Error: Tipo de exportación no válido.";
    exit();
}

// Consulta base para obtener los datos de evaluación con factores
$query_base = "
    SELECT 
        e.*,
        fi.*,
        ff.*,
        fc.*
    FROM evaluacion e
    LEFT JOIN factores_individuales fi ON e.id = fi.evaluacion_id
    LEFT JOIN factores_familiares ff ON e.id = ff.evaluacion_id
    LEFT JOIN factores_contextuales fc ON e.id = fc.evaluacion_id
    WHERE e.user_id = ?
";

$user_id = $_SESSION['userid'];
$stmt = $conn->prepare($query_base);
if ($stmt === false) {
    die('Error en la preparación de la consulta: ' . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Modificar datos según el tipo de exportación
if ($tipo === 'anonimizado') {
    foreach ($data as &$row) {
        // Ocultar nombre
        $row['nombre'] = 'ANONIMIZADO';

        // Convertir RUT a un hash único (sin caracteres raros)
        if (!empty($row['rut'])) {
            $row['rut'] = substr(hash('md5', $row['rut']), 0, 32); // Genera un hash truncado a 10 caracteres
        } else {
            $row['rut'] = 'DESCONOCIDO';
        }

        // Ocultar fecha de nacimiento
        $row['fecha_nacimiento'] = 'XXXX-XX-XX';

        // La edad permanece sin cambios
    }
}

// Generar CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="evaluaciones_' . $tipo . '.csv"');

$output = fopen('php://output', 'w');

// Agregar encabezados al CSV
if (!empty($data)) {
    fputcsv($output, array_keys($data[0]));
}

// Agregar datos al CSV
foreach ($data as $row) {
    fputcsv($output, $row);
}

fclose($output);
exit();
?>
