<?php include_once("config.php"); ?>
<?php include_once("header.php"); ?>
<?php
if (isset($_GET['evaluacion_id']) && is_numeric($_GET['evaluacion_id'])) {
    $_SESSION['inserted_id'] = (int) $_GET['evaluacion_id'];
}

if (!isset($_SESSION['inserted_id'])) {
    echo "Error: No hay un ID de evaluación vinculado.";
    exit;
}

$evaluacion_id = isset($_SESSION['inserted_id']) ? (int) $_SESSION['inserted_id'] : null;
$evaluacionIdQuery = $evaluacion_id !== null ? '?evaluacion_id=' . $evaluacion_id : '';

// Recuperar los valores existentes si ya hay un registro
$query = "SELECT * FROM seccion8 WHERE evaluacion_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $evaluacion_id);
$stmt->execute();
$result = $stmt->get_result();
$existing_data = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entorno_fisico = $_POST['entorno_fisico'] ?? 1;
    $politicas_programas = $_POST['politicas_programas'] ?? 1;
    $acceso_educacion = $_POST['acceso_educacion'] ?? 1;
    $servicios_salud = $_POST['servicios_salud'] ?? 1;
    $programas_prevencion = $_POST['programas_prevencion'] ?? 1;

    if ($existing_data) {
        // Actualizar el registro existente
        $query = "UPDATE seccion8 SET entorno_fisico = ?, politicas_programas = ?, acceso_educacion = ?, servicios_salud = ?, programas_prevencion = ? WHERE evaluacion_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiiiii", $entorno_fisico, $politicas_programas, $acceso_educacion, $servicios_salud, $programas_prevencion, $evaluacion_id);
        if ($stmt->execute()) {
            echo "Datos de la sección 8 actualizados exitosamente.";
            header('Location: resumen.php' . $evaluacionIdQuery); // Redirigir a la página de resumen o siguiente sección
        } else {
            echo "Error al actualizar los datos de la sección 8: " . $stmt->error;
        }
        $stmt->close();
    } else {
        // Insertar un nuevo registro si no existe
        $query = "INSERT INTO seccion8 (evaluacion_id, entorno_fisico, politicas_programas, acceso_educacion, servicios_salud, programas_prevencion) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiiiii", $evaluacion_id, $entorno_fisico, $politicas_programas, $acceso_educacion, $servicios_salud, $programas_prevencion);
        if ($stmt->execute()) {
            echo "Datos de la sección 8 guardados exitosamente.";
            header('Location: resumen.php' . $evaluacionIdQuery); // Redirigir a la página de resumen o siguiente sección

        } else {
            echo "Error al guardar los datos de la sección 8: " . $stmt->error;
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<div class="container mt-5">
    <h3>Factores Protección Ambientales</h3>
    <form method="POST" action="seccion8.php<?= htmlspecialchars($evaluacionIdQuery); ?>">
        <div class="accordion" id="accordionExampleProteccionAmbientales">
            <!-- Card 1 -->
            <div class="card">
                <div class="card-header" id="headingThirtySix">
                    <h5 class="mb-0">Entorno Físico Seguro y Saludable</h5>
                </div>
                <div id="collapseThirtySix" class="collapse show" data-parent="#accordionExampleProteccionAmbientales">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['entorno_fisico'] ?? 1); ?>" class="form-control-range" id="entorno_fisico" name="entorno_fisico">
                    </div>
                </div>
            </div>
            <!-- Card 2 -->
            <div class="card">
                <div class="card-header" id="headingThirtySeven">
                    <h5 class="mb-0">Políticas y Programas Comunitarios de Apoyo</h5>
                </div>
                <div id="collapseThirtySeven" class="collapse show" data-parent="#accordionExampleProteccionAmbientales">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['politicas_programas'] ?? 1); ?>" class="form-control-range" id="politicas_programas" name="politicas_programas">
                    </div>
                </div>
            </div>
            <!-- Card 3 -->
            <div class="card">
                <div class="card-header" id="headingThirtyEight">
                    <h5 class="mb-0">Acceso a Educación de Calidad y Oportunidades de Empleo</h5>
                </div>
                <div id="collapseThirtyEight" class="collapse show" data-parent="#accordionExampleProteccionAmbientales">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['acceso_educacion'] ?? 1); ?>" class="form-control-range" id="acceso_educacion" name="acceso_educacion">
                    </div>
                </div>
            </div>
            <!-- Card 4 -->
            <div class="card">
                <div class="card-header" id="headingThirtyNine">
                    <h5 class="mb-0">Servicios de Salud Accesibles y de Buena Calidad</h5>
                </div>
                <div id="collapseThirtyNine" class="collapse show" data-parent="#accordionExampleProteccionAmbientales">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['servicios_salud'] ?? 1); ?>" class="form-control-range" id="servicios_salud" name="servicios_salud">
                    </div>
                </div>
            </div>
            <!-- Card 5 -->
            <div class="card">
                <div class="card-header" id="headingForty">
                    <h5 class="mb-0">Programas de Prevención y Promoción de la Salud</h5>
                </div>
                <div id="collapseForty" class="collapse show" data-parent="#accordionExampleProteccionAmbientales">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['programas_prevencion'] ?? 1); ?>" class="form-control-range" id="programas_prevencion" name="programas_prevencion">
                    </div>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Guardar y Continuar</button>
        <a type="button" class="btn btn-secondary mt-3" href="seccion7.php<?= htmlspecialchars($evaluacionIdQuery); ?>">Anterior</a>
        <a href="resumen.php<?= htmlspecialchars($evaluacionIdQuery); ?>" class="btn btn-secondary mt-3">Ir al resumen</a>
    </form>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
