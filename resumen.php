<?php include_once("config.php"); ?>
<?php include_once("header.php"); ?>
<?php
if (isset($_GET['evaluacion_id'])) {
    $evaluacion_id = intval($_GET['evaluacion_id']);
    $_SESSION['inserted_id'] = $evaluacion_id;
} elseif (isset($_SESSION['inserted_id'])) {
    $evaluacion_id = $_SESSION['inserted_id'];
} else {
    echo "Error: No hay un ID de evaluación vinculado.";
    exit;
}

$evaluacion_id = $_SESSION['inserted_id'];

// Recuperar datos de la tabla evaluacion
$query_evaluacion = "SELECT * FROM evaluacion WHERE id = ?";
$stmt = $conn->prepare($query_evaluacion);
$stmt->bind_param("i", $evaluacion_id);
$stmt->execute();
$result_evaluacion = $stmt->get_result();
$data_evaluacion = $result_evaluacion->fetch_assoc();
$stmt->close();

// Función para recuperar datos de una sección
function get_section_data($conn, $evaluacion_id, $table_name) {
    $query = "SELECT * FROM $table_name WHERE evaluacion_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $evaluacion_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    return $data;
}

// Recuperar datos de las secciones 2 a 8
$data_seccion2 = get_section_data($conn, $evaluacion_id, 'seccion2');
$data_seccion3 = get_section_data($conn, $evaluacion_id, 'seccion3');
$data_seccion4 = get_section_data($conn, $evaluacion_id, 'seccion4');
$data_seccion5 = get_section_data($conn, $evaluacion_id, 'seccion5');
$data_seccion6 = get_section_data($conn, $evaluacion_id, 'seccion6');
$data_seccion7 = get_section_data($conn, $evaluacion_id, 'seccion7');
$data_seccion8 = get_section_data($conn, $evaluacion_id, 'seccion8');

// Mapear los datos a variables para facilitar su uso en el HTML
$nombre = $data_evaluacion['nombre'] ?? '';
$rut = $data_evaluacion['rut'] ?? '';
$cod_nino = $data_evaluacion['cod_nino'] ?? '';
$fecha_nacimiento = $data_evaluacion['fecha_nacimiento'] ?? '';
$edad = $data_evaluacion['edad'] ?? '';
$direccion = $data_evaluacion['direccion'] ?? ''; // Si tienes un campo de dirección

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resumen de las Respuestas</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .summary-box {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 20px;
            position: relative;
        }
        .name-grid {
            display: flex;
            flex-wrap: wrap;
        }
        .summary-item {
            flex: 1 1 200px;
            margin-right: 20px;
        }
        .edit-button {
            position: absolute;
            top: 15px;
            right: 15px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h3>Resumen de las Respuestas</h3>
    <div id="summaryContent">
        <div class="summary-box">
            <h3>Información Personal</h3>
            <!-- Botón para editar la sección -->
            <a href="seccion1.php?evaluacion_id=<?php echo (int) $evaluacion_id; ?>" class="btn btn-sm btn-warning edit-button">Editar</a>

            <!-- Names in a Grid Layout -->
            <div class="name-grid">
                <div class="summary-item">
                    <label for="summaryName">Nombre:</label>
                    <span id="summaryName"><?php echo htmlspecialchars($nombre); ?></span>
                </div>
                <!-- Si tienes campos adicionales como segundo nombre y apellidos, agrégalos aquí -->
            </div>

            <!-- Other Fields in Normal Layout -->
            <p>Edad: <span id="summaryAge"><?php echo htmlspecialchars($edad); ?></span></p>
            <p>RUT: <span id="summaryRUT"><?php echo htmlspecialchars($rut); ?></span></p>
            <p>CodNino: <span id="summaryCodNino"><?php echo htmlspecialchars($cod_nino); ?></span></p>
        </div>

        <!-- Sección de Factores Personales -->
        <div class="summary-box">
            <h3>Factores Personales</h3>
            <!-- Botón para editar la sección -->
            <a href="seccion2.php?evaluacion_id=<?php echo (int) $evaluacion_id; ?>" class="btn btn-sm btn-warning edit-button">Editar</a>

            <p>Historia Familiar de Problemas de Salud Mental: <span id="summaryRiesgo1"><?php echo htmlspecialchars($data_seccion2['historia_salud_mental'] ?? 'N/A'); ?></span></p>
            <p>Antecedentes de Abuso de Sustancias: <span id="summaryRiesgo2"><?php echo htmlspecialchars($data_seccion2['abuso_sustancias'] ?? 'N/A'); ?></span></p>
            <p>Problemas de Comportamiento y de Regulación Emocional: <span id="summaryRiesgo3"><?php echo htmlspecialchars($data_seccion2['comportamiento_emocional'] ?? 'N/A'); ?></span></p>
            <p>Baja Autoestima: <span id="summaryRiesgo4"><?php echo htmlspecialchars($data_seccion2['autoestima'] ?? 'N/A'); ?></span></p>
            <p>Enfermedades Crónicas o Discapacidades Físicas: <span id="summaryRiesgo5"><?php echo htmlspecialchars($data_seccion2['enfermedades_cronicas'] ?? 'N/A'); ?></span></p>
            <p>Estrés Prolongado o Traumático: <span id="summaryRiesgo6"><?php echo htmlspecialchars($data_seccion2['estres_traumatico'] ?? 'N/A'); ?></span></p>
        </div>

        <!-- Sección de Factores Familiares -->
        <div class="summary-box">
            <h3>Factores de Riesgo Familiares</h3>
            <!-- Botón para editar la sección -->
            <a href="seccion3.php?evaluacion_id=<?php echo (int) $evaluacion_id; ?>" class="btn btn-sm btn-warning edit-button">Editar</a>

            <p>Conflictos Familiares y Violencia Doméstica: <span id="summaryRiesgo7"><?php echo htmlspecialchars($data_seccion3['conflictos_familiares'] ?? 'N/A'); ?></span></p>
            <p>Falta de Apoyo Emocional y Supervisión: <span id="summaryRiesgo8"><?php echo htmlspecialchars($data_seccion3['falta_apoyo_emocional'] ?? 'N/A'); ?></span></p>
            <p>Abuso Físico, Emocional o Sexual: <span id="summaryRiesgo9"><?php echo htmlspecialchars($data_seccion3['abuso_fisico_emocional'] ?? 'N/A'); ?></span></p>
            <p>Pérdida de uno o ambos Padres: <span id="summaryRiesgo10"><?php echo htmlspecialchars($data_seccion3['perdida_padres'] ?? 'N/A'); ?></span></p>
            <p>Padres con Problemas de Salud Mental o Abuso de Sustancias: <span id="summaryRiesgo11"><?php echo htmlspecialchars($data_seccion3['padres_salud_mental'] ?? 'N/A'); ?></span></p>
        </div>

        <!-- Sección de Factores Sociales -->
        <div class="summary-box">
            <h3>Factores de Riesgo Sociales</h3>
            <!-- Botón para editar la sección -->
            <a href="seccion4.php?evaluacion_id=<?php echo (int) $evaluacion_id; ?>" class="btn btn-sm btn-warning edit-button">Editar</a>

            <p>Exclusión Social y Falta de Redes de Apoyo: <span id="summaryRiesgo12"><?php echo htmlspecialchars($data_seccion4['exclusion_social'] ?? 'N/A'); ?></span></p>
            <p>Pobreza y Dificultades Económicas: <span id="summaryRiesgo13"><?php echo htmlspecialchars($data_seccion4['pobreza_dificultades'] ?? 'N/A'); ?></span></p>
            <p>Experiencias de Discriminación y Estigmatización: <span id="summaryRiesgo14"><?php echo htmlspecialchars($data_seccion4['discriminacion'] ?? 'N/A'); ?></span></p>
            <p>Influencias Negativas de Pares y Amigos: <span id="summaryRiesgo15"><?php echo htmlspecialchars($data_seccion4['influencias_negativas'] ?? 'N/A'); ?></span></p>
            <p>Ambientes Escolares Poco Seguros o Violentos: <span id="summaryRiesgo16"><?php echo htmlspecialchars($data_seccion4['ambientes_escolares'] ?? 'N/A'); ?></span></p>
        </div>

        <!-- Sección de Factores Ambientales -->
        <div class="summary-box">
            <h3>Factores de Riesgo Ambientales</h3>
            <!-- Botón para editar la sección -->
            <a href="seccion5.php?evaluacion_id=<?php echo (int) $evaluacion_id; ?>" class="btn btn-sm btn-warning edit-button">Editar</a>

            <p>Vivienda Inadecuada o Condiciones de Vida Peligrosas: <span id="summaryRiesgo17"><?php echo htmlspecialchars($data_seccion5['vivienda_inadecuada'] ?? 'N/A'); ?></span></p>
            <p>Acceso Limitado a Servicios de Salud y Educación: <span id="summaryRiesgo18"><?php echo htmlspecialchars($data_seccion5['acceso_servicios'] ?? 'N/A'); ?></span></p>
            <p>Desastres Naturales y Crisis Humanitarias: <span id="summaryRiesgo19"><?php echo htmlspecialchars($data_seccion5['desastres_crisis'] ?? 'N/A'); ?></span></p>
            <p>Exposición a Violencia Comunitaria: <span id="summaryRiesgo20"><?php echo htmlspecialchars($data_seccion5['violencia_comunitaria'] ?? 'N/A'); ?></span></p>
        </div>

        <!-- Sección de Factores de Protección Personales -->
        <div class="summary-box">
            <h3>Factores de Protección Personales</h3>
            <!-- Botón para editar la sección -->
            <a href="seccion6.php?evaluacion_id=<?php echo (int) $evaluacion_id; ?>" class="btn btn-sm btn-warning edit-button">Editar</a>

            <p>Buena Salud Física y Mental: <span id="summaryRiesgo21"><?php echo htmlspecialchars($data_seccion6['buena_salud'] ?? 'N/A'); ?></span></p>
            <p>Habilidades de Afrontamiento y Manejo del Estrés: <span id="summaryRiesgo22"><?php echo htmlspecialchars($data_seccion6['habilidades_afrontamiento'] ?? 'N/A'); ?></span></p>
            <p>Alta Autoestima y Autoconfianza: <span id="summaryRiesgo23"><?php echo htmlspecialchars($data_seccion6['autoestima_alta'] ?? 'N/A'); ?></span></p>
            <p>Buen Rendimiento Académico y Habilidades Cognitivas: <span id="summaryRiesgo24"><?php echo htmlspecialchars($data_seccion6['rendimiento_academico'] ?? 'N/A'); ?></span></p>
            <p>Participación en Actividades Recreativas y Deportivas: <span id="summaryRiesgo25"><?php echo htmlspecialchars($data_seccion6['participacion_actividades'] ?? 'N/A'); ?></span></p>
        </div>

        <!-- Sección de Factores de Protección Familiares -->
        <div class="summary-box">
            <h3>Factores de Protección Familiares</h3>
            <!-- Botón para editar la sección -->
            <a href="seccion7.php?evaluacion_id=<?php echo (int) $evaluacion_id; ?>" class="btn btn-sm btn-warning edit-button">Editar</a>

            <p>Relaciones Familiares Cálidas y de Apoyo: <span id="summaryRiesgo26"><?php echo htmlspecialchars($data_seccion7['relaciones_familiares'] ?? 'N/A'); ?></span></p>
            <p>Supervisión y Guía Adecuada por Parte de los Padres: <span id="summaryRiesgo27"><?php echo htmlspecialchars($data_seccion7['supervision_padres'] ?? 'N/A'); ?></span></p>
            <p>Comunicación Abierta y Efectiva en la Familia: <span id="summaryRiesgo28"><?php echo htmlspecialchars($data_seccion7['comunicacion_familiar'] ?? 'N/A'); ?></span></p>
            <p>Presencia de al Menos un Adulto Significativo y de Confianza: <span id="summaryRiesgo29"><?php echo htmlspecialchars($data_seccion7['adulto_significativo'] ?? 'N/A'); ?></span></p>
            <p>Prácticas de Crianza Positivas y Consistentes: <span id="summaryRiesgo30"><?php echo htmlspecialchars($data_seccion7['practicas_crianza'] ?? 'N/A'); ?></span></p>
        </div>

        <!-- Sección de Factores de Protección Sociales -->
        <div class="summary-box">
            <h3>Factores de Protección Sociales</h3>
            <!-- Botón para editar la sección -->
            <a href="seccion8.php?evaluacion_id=<?php echo (int) $evaluacion_id; ?>" class="btn btn-sm btn-warning edit-button">Editar</a>

            <p>Redes de Apoyo Social Sólidas (Amigos, Comunidad): <span id="summaryRiesgo31"><?php echo htmlspecialchars($data_seccion8['redes_apoyo_social'] ?? 'N/A'); ?></span></p>
            <p>Participación en Grupos y Actividades Comunitarias: <span id="summaryRiesgo32"><?php echo htmlspecialchars($data_seccion8['participacion_comunitaria'] ?? 'N/A'); ?></span></p>
            <p>Ambiente Escolar Seguro y de Apoyo: <span id="summaryRiesgo33"><?php echo htmlspecialchars($data_seccion8['ambiente_escolar'] ?? 'N/A'); ?></span></p>
            <p>Acceso a Servicios de Salud Mental y Otros Recursos: <span id="summaryRiesgo34"><?php echo htmlspecialchars($data_seccion8['acceso_salud_mental'] ?? 'N/A'); ?></span></p>
            <p>Experiencias de Éxito y Reconocimiento Social: <span id="summaryRiesgo35"><?php echo htmlspecialchars($data_seccion8['experiencias_exito'] ?? 'N/A'); ?></span></p>
        </div>

        <!-- Sección de Factores de Protección Ambientales -->
        <div class="summary-box">
            <h3>Factores de Protección Ambientales</h3>
            <!-- Botón para editar la sección -->
            <a href="seccion9.php?evaluacion_id=<?php echo (int) $evaluacion_id; ?>" class="btn btn-sm btn-warning edit-button">Editar</a>

            <p>Entorno Físico Seguro y Saludable: <span id="summaryRiesgo36"><?php echo htmlspecialchars($data_seccion8['entorno_fisico'] ?? 'N/A'); ?></span></p>
            <p>Políticas y Programas Comunitarios de Apoyo: <span id="summaryRiesgo37"><?php echo htmlspecialchars($data_seccion8['politicas_programas'] ?? 'N/A'); ?></span></p>
            <p>Acceso a Educación de Calidad y Oportunidades de Empleo: <span id="summaryRiesgo38"><?php echo htmlspecialchars($data_seccion8['acceso_educacion'] ?? 'N/A'); ?></span></p>
            <p>Servicios de Salud Accesibles y de Buena Calidad: <span id="summaryRiesgo39"><?php echo htmlspecialchars($data_seccion8['servicios_salud'] ?? 'N/A'); ?></span></p>
            <p>Programas de Prevención y Promoción de la Salud: <span id="summaryRiesgo40"><?php echo htmlspecialchars($data_seccion8['programas_prevencion'] ?? 'N/A'); ?></span></p>
        </div>
    </div>
    <a href="seccion8.php?evaluacion_id=<?php echo (int) $evaluacion_id; ?>" class="btn btn-secondary">Anterior</a>
    <a href="finalizar.php" class="btn btn-primary">Completar</a>
    
</div>
<br>
<hr>
</body>
</html>