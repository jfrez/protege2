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
$query = "SELECT * FROM seccion5 WHERE evaluacion_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $evaluacion_id);
$stmt->execute();
$result = $stmt->get_result();
$existing_data = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }
    $vivienda_inadecuada = $_POST['vivienda_inadecuada'] ?? 1;
    $acceso_servicios = $_POST['acceso_servicios'] ?? 1;
    $desastres_crisis = $_POST['desastres_crisis'] ?? 1;
    $violencia_comunitaria = $_POST['violencia_comunitaria'] ?? 1;

    if ($existing_data) {
        // Actualizar el registro existente
        $query = "UPDATE seccion5 SET vivienda_inadecuada = ?, acceso_servicios = ?, desastres_crisis = ?, violencia_comunitaria = ? WHERE evaluacion_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiiii", $vivienda_inadecuada, $acceso_servicios, $desastres_crisis, $violencia_comunitaria, $evaluacion_id);
        if ($stmt->execute()) {
            echo "Datos de la sección 5 actualizados exitosamente.";
            header('Location: seccion6.php' . $evaluacionIdQuery);
        } else {
            echo "Error al actualizar los datos de la sección 5: " . $stmt->error;
        }
        $stmt->close();
    } else {
        // Insertar un nuevo registro si no existe
        $query = "INSERT INTO seccion5 (evaluacion_id, vivienda_inadecuada, acceso_servicios, desastres_crisis, violencia_comunitaria) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiiii", $evaluacion_id, $vivienda_inadecuada, $acceso_servicios, $desastres_crisis, $violencia_comunitaria);
        if ($stmt->execute()) {
            echo "Datos de la sección 5 guardados exitosamente.";
            header('Location: seccion6.php' . $evaluacionIdQuery);

        } else {
            echo "Error al guardar los datos de la sección 5: " . $stmt->error;
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<div class="container mt-5">
    <h3>Factores Riesgo Ambientales</h3>
    <form method="POST" action="seccion5.php<?= htmlspecialchars($evaluacionIdQuery); ?>">
        <?php csrf_input(); ?>
        <div class="accordion" id="accordionExampleAmbientales">
            <!-- Card 1 -->
            <div class="card">
                <div class="card-header" id="headingSeventeen">
                    <h5 class="mb-0">Vivienda Inadecuada o Condiciones de Vida Peligrosas</h5>
                </div>
                <div id="collapseSeventeen" class="collapse show" data-parent="#accordionExampleAmbientales">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['vivienda_inadecuada'] ?? 1); ?>" class="form-control-range" id="vivienda_inadecuada" name="vivienda_inadecuada">
                    </div>
                </div>
            </div>
            <!-- Card 2 -->
            <div class="card">
                <div class="card-header" id="headingEighteen">
                    <h5 class="mb-0">Acceso Limitado a Servicios de Salud y Educación</h5>
                </div>
                <div id="collapseEighteen" class="collapse show" data-parent="#accordionExampleAmbientales">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['acceso_servicios'] ?? 1); ?>" class="form-control-range" id="acceso_servicios" name="acceso_servicios">
                    </div>
                </div>
            </div>
            <!-- Card 3 -->
            <div class="card">
                <div class="card-header" id="headingNineteen">
                    <h5 class="mb-0">Desastres Naturales y Crisis Humanitarias</h5>
                </div>
                <div id="collapseNineteen" class="collapse show" data-parent="#accordionExampleAmbientales">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['desastres_crisis'] ?? 1); ?>" class="form-control-range" id="desastres_crisis" name="desastres_crisis">
                    </div>
                </div>
            </div>
            <!-- Card 4 -->
            <div class="card">
                <div class="card-header" id="headingTwenty">
                    <h5 class="mb-0">Exposición a Violencia Comunitaria</h5>
                </div>
                <div id="collapseTwenty" class="collapse show" data-parent="#accordionExampleAmbientales">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['violencia_comunitaria'] ?? 1); ?>" class="form-control-range" id="violencia_comunitaria" name="violencia_comunitaria">
                    </div>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Guardar y Continuar</button>
        <a type="button" class="btn btn-secondary mt-3" href="seccion4.php<?= htmlspecialchars($evaluacionIdQuery); ?>">Anterior</a>
        <a href="resumen.php<?= htmlspecialchars($evaluacionIdQuery); ?>" class="btn btn-secondary mt-3">Ir al resumen</a>
    </form>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
