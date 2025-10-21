<?php
session_start();
include_once("config.php");
include_once("utils/anonymization.php");

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['userid'])) {
    echo "Error: Debes iniciar sesión para acceder a esta página.";
    exit();
}

$role = $_SESSION['role'] ?? '';
$isAdmin = $role === 'admin';
$isSupervisor = $role === 'supervisor';

// Obtener el tipo de exportación
$tipo = $_GET['tipo'] ?? '';

if ($isSupervisor) {
    $tipo = 'anonimizado';
}

if (!in_array($tipo, ['bruto', 'anonimizado'], true)) {
    echo "Error: Tipo de exportación no válido.";
    exit();
}

// Consulta base para obtener los datos de evaluación con factores
$query_base = "
    SELECT
        e.cod_nino,
        e.*,
        fi.*,
        ff.*,
        fc.*
    FROM evaluacion e
    LEFT JOIN factores_individuales fi ON e.id = fi.evaluacion_id
    LEFT JOIN factores_familiares ff ON e.id = ff.evaluacion_id
    LEFT JOIN factores_contextuales fc ON e.id = fc.evaluacion_id
";

$params = [];
if (!$isAdmin && !$isSupervisor) {
    $query_base .= " WHERE e.user_id = ?";
    $params[] = $_SESSION['userid'];
}

$stmt = sqlsrv_query($conn, $query_base, $params);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
$data = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $data[] = $row;
}
sqlsrv_free_stmt($stmt);

// Modificar datos según el tipo de exportación o el rol
$shouldAnonymize = ($tipo === 'anonimizado') || $isSupervisor;
if ($shouldAnonymize) {
    foreach ($data as &$row) {
        $row = anonymize_sensitive_fields($row);
    }
    unset($row);
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
    foreach ($row as $key => $value) {
        if ($value instanceof DateTime) {
            $row[$key] = $value->format('Y-m-d H:i:s');
        }
    }
    fputcsv($output, $row);
}

fclose($output);
exit();
?>
