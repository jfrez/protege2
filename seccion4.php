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
$query = "SELECT * FROM seccion4 WHERE evaluacion_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $evaluacion_id);
$stmt->execute();
$result = $stmt->get_result();
$existing_data = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $exclusion_social = $_POST['exclusion_social'] ?? 1;
    $pobreza_dificultades = $_POST['pobreza_dificultades'] ?? 1;
    $discriminacion = $_POST['discriminacion'] ?? 1;
    $influencias_negativas = $_POST['influencias_negativas'] ?? 1;
    $ambientes_escolares = $_POST['ambientes_escolares'] ?? 1;

    if ($existing_data) {
        // Actualizar el registro existente
        $query = "UPDATE seccion4 SET exclusion_social = ?, pobreza_dificultades = ?, discriminacion = ?, influencias_negativas = ?, ambientes_escolares = ? WHERE evaluacion_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiiiii", $exclusion_social, $pobreza_dificultades, $discriminacion, $influencias_negativas, $ambientes_escolares, $evaluacion_id);
        if ($stmt->execute()) {
            header('Location: seccion5.php' . $evaluacionIdQuery);
        } else {
            echo "Error al actualizar los datos de la sección 4: " . $stmt->error;
        }
        $stmt->close();
    } else {
        // Insertar un nuevo registro si no existe
        $query = "INSERT INTO seccion4 (evaluacion_id, exclusion_social, pobreza_dificultades, discriminacion, influencias_negativas, ambientes_escolares) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiiiii", $evaluacion_id, $exclusion_social, $pobreza_dificultades, $discriminacion, $influencias_negativas, $ambientes_escolares);
        if ($stmt->execute()) {
            header('Location: seccion5.php' . $evaluacionIdQuery);

        } else {
            echo "Error al guardar los datos de la sección 4: " . $stmt->error;
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<div class="container mt-5">
    <h3>Factores Riesgo Sociales</h3>
    <form method="POST" action="seccion4.php<?= htmlspecialchars($evaluacionIdQuery); ?>">
        <div class="accordion" id="accordionExampleSociales">
            <!-- Card 1 -->
            <div class="card">
                <div class="card-header" id="headingTwelve">
                    <h5 class="mb-0">Exclusión Social y Falta de Redes de Apoyo</h5>
                </div>
                <div id="collapseTwelve" class="collapse show" data-parent="#accordionExampleSociales">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['exclusion_social'] ?? 1); ?>" class="form-control-range" id="exclusion_social" name="exclusion_social">
                    </div>
                </div>
            </div>
            <!-- Card 2 -->
            <div class="card">
                <div class="card-header" id="headingThirteen">
                    <h5 class="mb-0">Pobreza y Dificultades Económicas</h5>
                </div>
                <div id="collapseThirteen" class="collapse show" data-parent="#accordionExampleSociales">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['pobreza_dificultades'] ?? 1); ?>" class="form-control-range" id="pobreza_dificultades" name="pobreza_dificultades">
                    </div>
                </div>
            </div>
            <!-- Card 3 -->
            <div class="card">
                <div class="card-header" id="headingFourteen">
                    <h5 class="mb-0">Experiencias de Discriminación y Estigmatización</h5>
                </div>
                <div id="collapseFourteen" class="collapse show" data-parent="#accordionExampleSociales">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['discriminacion'] ?? 1); ?>" class="form-control-range" id="discriminacion" name="discriminacion">
                    </div>
                </div>
            </div>
            <!-- Card 4 -->
            <div class="card">
                <div class="card-header" id="headingFifteen">
                    <h5 class="mb-0">Influencias Negativas de Pares y Amigos</h5>
                </div>
                <div id="collapseFifteen" class="collapse show" data-parent="#accordionExampleSociales">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['influencias_negativas'] ?? 1); ?>" class="form-control-range" id="influencias_negativas" name="influencias_negativas">
                    </div>
                </div>
            </div>
            <!-- Card 5 -->
            <div class="card">
                <div class="card-header" id="headingSixteen">
                    <h5 class="mb-0">Ambientes Escolares Poco Seguros o Violentos</h5>
                </div>
                <div id="collapseSixteen" class="collapse show" data-parent="#accordionExampleSociales">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['ambientes_escolares'] ?? 1); ?>" class="form-control-range" id="ambientes_escolares" name="ambientes_escolares">
                    </div>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Guardar y Continuar</button>
        <a type="button" class="btn btn-secondary mt-3" href="seccion3.php<?= htmlspecialchars($evaluacionIdQuery); ?>">Anterior</a>
        <a href="resumen.php<?= htmlspecialchars($evaluacionIdQuery); ?>" class="btn btn-secondary mt-3">Ir al resumen</a>
    </form>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
