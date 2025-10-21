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
$query = "SELECT * FROM seccion7 WHERE evaluacion_id = ?";
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
    $redes_apoyo_social = $_POST['redes_apoyo_social'] ?? 1;
    $participacion_comunitaria = $_POST['participacion_comunitaria'] ?? 1;
    $ambiente_escolar = $_POST['ambiente_escolar'] ?? 1;
    $acceso_salud_mental = $_POST['acceso_salud_mental'] ?? 1;
    $experiencias_exito = $_POST['experiencias_exito'] ?? 1;

    if ($existing_data) {
        // Actualizar el registro existente
        $query = "UPDATE seccion7 SET redes_apoyo_social = ?, participacion_comunitaria = ?, ambiente_escolar = ?, acceso_salud_mental = ?, experiencias_exito = ? WHERE evaluacion_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiiiii", $redes_apoyo_social, $participacion_comunitaria, $ambiente_escolar, $acceso_salud_mental, $experiencias_exito, $evaluacion_id);
        if ($stmt->execute()) {
            echo "Datos de la sección 7 actualizados exitosamente.";
            header('Location: seccion8.php' . $evaluacionIdQuery);
        } else {
            echo "Error al actualizar los datos de la sección 7: " . $stmt->error;
        }
        $stmt->close();
    } else {
        // Insertar un nuevo registro si no existe
        $query = "INSERT INTO seccion7 (evaluacion_id, redes_apoyo_social, participacion_comunitaria, ambiente_escolar, acceso_salud_mental, experiencias_exito) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiiiii", $evaluacion_id, $redes_apoyo_social, $participacion_comunitaria, $ambiente_escolar, $acceso_salud_mental, $experiencias_exito);
        if ($stmt->execute()) {
            echo "Datos de la sección 7 guardados exitosamente.";
            header('Location: seccion8.php' . $evaluacionIdQuery);

        } else {
            echo "Error al guardar los datos de la sección 7: " . $stmt->error;
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<div class="container mt-5">
    <h3>Factores Protección Sociales</h3>
    <form method="POST" action="seccion7.php<?= htmlspecialchars($evaluacionIdQuery); ?>">
        <?php csrf_input(); ?>
        <div class="accordion" id="accordionExampleProteccionSociales">
            <!-- Card 1 -->
            <div class="card">
                <div class="card-header" id="headingThirtyOne">
                    <h5 class="mb-0">Redes de Apoyo Social Sólidas (Amigos, Comunidad)</h5>
                </div>
                <div id="collapseThirtyOne" class="collapse show" data-parent="#accordionExampleProteccionSociales">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['redes_apoyo_social'] ?? 1); ?>" class="form-control-range" id="redes_apoyo_social" name="redes_apoyo_social">
                    </div>
                </div>
            </div>
            <!-- Card 2 -->
            <div class="card">
                <div class="card-header" id="headingThirtyTwo">
                    <h5 class="mb-0">Participación en Grupos y Actividades Comunitarias</h5>
                </div>
                <div id="collapseThirtyTwo" class="collapse show" data-parent="#accordionExampleProteccionSociales">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['participacion_comunitaria'] ?? 1); ?>" class="form-control-range" id="participacion_comunitaria" name="participacion_comunitaria">
                    </div>
                </div>
            </div>
            <!-- Card 3 -->
            <div class="card">
                <div class="card-header" id="headingThirtyThree">
                    <h5 class="mb-0">Ambiente Escolar Seguro y de Apoyo</h5>
                </div>
                <div id="collapseThirtyThree" class="collapse show" data-parent="#accordionExampleProteccionSociales">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['ambiente_escolar'] ?? 1); ?>" class="form-control-range" id="ambiente_escolar" name="ambiente_escolar">
                    </div>
                </div>
            </div>
            <!-- Card 4 -->
            <div class="card">
                <div class="card-header" id="headingThirtyFour">
                    <h5 class="mb-0">Acceso a Servicios de Salud Mental y Otros Recursos</h5>
                </div>
                <div id="collapseThirtyFour" class="collapse show" data-parent="#accordionExampleProteccionSociales">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['acceso_salud_mental'] ?? 1); ?>" class="form-control-range" id="acceso_salud_mental" name="acceso_salud_mental">
                    </div>
                </div>
            </div>
            <!-- Card 5 -->
            <div class="card">
                <div class="card-header" id="headingThirtyFive">
                    <h5 class="mb-0">Experiencias de Éxito y Reconocimiento Social</h5>
                </div>
                <div id="collapseThirtyFive" class="collapse show" data-parent="#accordionExampleProteccionSociales">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['experiencias_exito'] ?? 1); ?>" class="form-control-range" id="experiencias_exito" name="experiencias_exito">
                    </div>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Guardar y Continuar</button>
        <a type="button" class="btn btn-secondary mt-3" href="seccion6.php<?= htmlspecialchars($evaluacionIdQuery); ?>">Anterior</a>
        <a href="resumen.php<?= htmlspecialchars($evaluacionIdQuery); ?>" class="btn btn-secondary mt-3">Ir al resumen</a>

    </form>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
