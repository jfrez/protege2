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
$query = "SELECT * FROM seccion3 WHERE evaluacion_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $evaluacion_id);
$stmt->execute();
$result = $stmt->get_result();
$existing_data = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conflictos_familiares = $_POST['conflictos_familiares'] ?? 1;
    $falta_apoyo_emocional = $_POST['falta_apoyo_emocional'] ?? 1;
    $abuso_fisico_emocional = $_POST['abuso_fisico_emocional'] ?? 1;
    $perdida_padres = $_POST['perdida_padres'] ?? 1;
    $padres_salud_mental = $_POST['padres_salud_mental'] ?? 1;

    if ($existing_data) {
        // Actualizar el registro existente
        $query = "UPDATE seccion3 SET conflictos_familiares = ?, falta_apoyo_emocional = ?, abuso_fisico_emocional = ?, perdida_padres = ?, padres_salud_mental = ? WHERE evaluacion_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiiiii", $conflictos_familiares, $falta_apoyo_emocional, $abuso_fisico_emocional, $perdida_padres, $padres_salud_mental, $evaluacion_id);
        if ($stmt->execute()) {
            header('Location: seccion4.php' . $evaluacionIdQuery);
        } else {
            echo "Error al actualizar los datos de la sección 3: " . $stmt->error;
        }
        $stmt->close();
    } else {
        // Insertar un nuevo registro si no existe
        $query = "INSERT INTO seccion3 (evaluacion_id, conflictos_familiares, falta_apoyo_emocional, abuso_fisico_emocional, perdida_padres, padres_salud_mental) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiiiii", $evaluacion_id, $conflictos_familiares, $falta_apoyo_emocional, $abuso_fisico_emocional, $perdida_padres, $padres_salud_mental);
        if ($stmt->execute()) {
            header('Location: seccion4.php' . $evaluacionIdQuery);

        } else {
            echo "Error al guardar los datos de la sección 3: " . $stmt->error;
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<div class="container mt-5">
    <h3>Factores Familiares</h3>
    <form method="POST" action="seccion3.php<?= htmlspecialchars($evaluacionIdQuery); ?>">
        <div class="accordion" id="accordionExample">
            <!-- Card 1 -->
            <div class="card">
                <div class="card-header" id="headingSeven">
                    <h5 class="mb-0">Conflictos Familiares y Violencia Doméstica</h5>
                </div>
                <div id="collapseSeven" class="collapse show" data-parent="#accordionExample">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['conflictos_familiares'] ?? 1); ?>" class="form-control-range" id="conflictos_familiares" name="conflictos_familiares">
                    </div>
                </div>
            </div>
            <!-- Card 2 -->
            <div class="card">
                <div class="card-header" id="headingEight">
                    <h5 class="mb-0">Falta de Apoyo Emocional y Supervisión</h5>
                </div>
                <div id="collapseEight" class="collapse show" data-parent="#accordionExample">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['falta_apoyo_emocional'] ?? 1); ?>" class="form-control-range" id="falta_apoyo_emocional" name="falta_apoyo_emocional">
                    </div>
                </div>
            </div>
            <!-- Card 3 -->
            <div class="card">
                <div class="card-header" id="headingNine">
                    <h5 class="mb-0">Abuso Físico, Emocional o Sexual</h5>
                </div>
                <div id="collapseNine" class="collapse show" data-parent="#accordionExample">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['abuso_fisico_emocional'] ?? 1); ?>" class="form-control-range" id="abuso_fisico_emocional" name="abuso_fisico_emocional">
                    </div>
                </div>
            </div>
            <!-- Card 4 -->
            <div class="card">
                <div class="card-header" id="headingTen">
                    <h5 class="mb-0">Pérdida de uno o ambos Padres</h5>
                </div>
                <div id="collapseTen" class="collapse show" data-parent="#accordionExample">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['perdida_padres'] ?? 1); ?>" class="form-control-range" id="perdida_padres" name="perdida_padres">
                    </div>
                </div>
            </div>
            <!-- Card 5 -->
            <div class="card">
                <div class="card-header" id="headingEleven">
                    <h5 class="mb-0">Padres con Problemas de Salud Mental o Abuso de Sustancias</h5>
                </div>
                <div id="collapseEleven" class="collapse show" data-parent="#accordionExample">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['padres_salud_mental'] ?? 1); ?>" class="form-control-range" id="padres_salud_mental" name="padres_salud_mental">
                    </div>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Guardar y Continuar</button>
        <a type="button" class="btn btn-secondary mt-3" href="seccion2.php<?= htmlspecialchars($evaluacionIdQuery); ?>">Anterior</a>
        <a href="resumen.php<?= htmlspecialchars($evaluacionIdQuery); ?>" class="btn btn-secondary mt-3">Ir al resumen</a>
    </form>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
