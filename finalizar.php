<?php include_once("config.php"); ?>
<?php include_once("header.php"); ?>
<?php


if (!isset($_SESSION['inserted_id'])) {
    echo "Error: No hay un ID de evaluación vinculado.";
    exit;
}

$evaluacion_id = (int) $_SESSION['inserted_id'];

// Aquí puedes agregar cualquier lógica adicional que necesites, como cálculos finales

// Por ejemplo, si deseas calcular un puntaje total basado en las respuestas:
function calcular_puntaje_total($conn, $evaluacion_id) {
    $total_puntaje = 0;

    // Listado de secciones y campos
    $secciones = [
        'seccion2' => ['historia_salud_mental', 'abuso_sustancias', 'comportamiento_emocional', 'autoestima', 'enfermedades_cronicas', 'estres_traumatico'],
        'seccion3' => ['conflictos_familiares', 'falta_apoyo_emocional', 'abuso_fisico_emocional', 'perdida_padres', 'padres_salud_mental'],
        'seccion4' => ['exclusion_social', 'pobreza_dificultades', 'discriminacion', 'influencias_negativas', 'ambientes_escolares'],
        'seccion5' => ['vivienda_inadecuada', 'acceso_servicios', 'desastres_crisis', 'violencia_comunitaria'],
        'seccion6' => ['relaciones_familiares', 'supervision_padres', 'comunicacion_familiar', 'adulto_significativo', 'practicas_crianza'],
        'seccion7' => ['redes_apoyo_social', 'participacion_comunitaria', 'ambiente_escolar', 'acceso_salud_mental', 'experiencias_exito'],
        'seccion8' => ['entorno_fisico', 'politicas_programas', 'acceso_educacion', 'servicios_salud', 'programas_prevencion'],
    ];

    foreach ($secciones as $tabla => $campos) {
        $query = "SELECT * FROM $tabla WHERE evaluacion_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $evaluacion_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $datos = $result->fetch_assoc();
        $stmt->close();

        if ($datos) {
            foreach ($campos as $campo) {
                $total_puntaje += (int)$datos[$campo];
            }
        }
    }

    return $total_puntaje;
}

// Calcular el puntaje total
$total_puntaje = calcular_puntaje_total($conn, $evaluacion_id);

// Limpiar el estado de la sesión utilizado durante el flujo de evaluación
unset($_SESSION['inserted_id']);
unset($_SESSION['token']);
if (isset($_SESSION['form_data'])) {
    unset($_SESSION['form_data']);
}




?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Proceso Completado</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1>Gracias por completar la evaluación.</h1>
    <p>El puntaje total es: <strong><?php echo $total_puntaje; ?></strong></p>
    <a href="homepage.php" class="btn btn-primary mt-3">Volver al inicio</a>
</div>
</body>
</html>
