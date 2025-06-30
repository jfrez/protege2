<?php include_once("config.php"); ?>
<?php include_once("header.php"); ?>
<?php
if (!isset($_SESSION['inserted_id'])) {
    echo "Error: No hay un ID de evaluación vinculado.";
    exit;
}

$evaluacion_id = $_SESSION['inserted_id'];

// Recuperar los valores existentes si ya hay un registro
$query = "SELECT * FROM seccion6 WHERE evaluacion_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $evaluacion_id);
$stmt->execute();
$result = $stmt->get_result();
$existing_data = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $relaciones_familiares = $_POST['relaciones_familiares'] ?? 1;
    $supervision_padres = $_POST['supervision_padres'] ?? 1;
    $comunicacion_familiar = $_POST['comunicacion_familiar'] ?? 1;
    $adulto_significativo = $_POST['adulto_significativo'] ?? 1;
    $practicas_crianza = $_POST['practicas_crianza'] ?? 1;

    if ($existing_data) {
        // Actualizar el registro existente
        $query = "UPDATE seccion6 SET relaciones_familiares = ?, supervision_padres = ?, comunicacion_familiar = ?, adulto_significativo = ?, practicas_crianza = ? WHERE evaluacion_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiiiii", $relaciones_familiares, $supervision_padres, $comunicacion_familiar, $adulto_significativo, $practicas_crianza, $evaluacion_id);
        if ($stmt->execute()) {
            echo "Datos de la sección 6 actualizados exitosamente.";
            header('Location: seccion7.php');
        } else {
            echo "Error al actualizar los datos de la sección 6: " . $stmt->error;
        }
        $stmt->close();
    } else {
        // Insertar un nuevo registro si no existe
        $query = "INSERT INTO seccion6 (evaluacion_id, relaciones_familiares, supervision_padres, comunicacion_familiar, adulto_significativo, practicas_crianza) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiiiii", $evaluacion_id, $relaciones_familiares, $supervision_padres, $comunicacion_familiar, $adulto_significativo, $practicas_crianza);
        if ($stmt->execute()) {
            echo "Datos de la sección 6 guardados exitosamente.";
            header('Location: seccion7.php');

        } else {
            echo "Error al guardar los datos de la sección 6: " . $stmt->error;
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<div class="container mt-5">
    <h3>Factores Protección Familiares</h3>
    <form method="POST" action="seccion6.php">
        <div class="accordion" id="accordionExampleProteccionFamiliares">
            <!-- Card 1 -->
            <div class="card">
                <div class="card-header" id="headingTwentySix">
                    <h5 class="mb-0">Relaciones Familiares Cálidas y de Apoyo</h5>
                </div>
                <div id="collapseTwentySix" class="collapse show" data-parent="#accordionExampleProteccionFamiliares">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['relaciones_familiares'] ?? 1); ?>" class="form-control-range" id="relaciones_familiares" name="relaciones_familiares">
                    </div>
                </div>
            </div>
            <!-- Card 2 -->
            <div class="card">
                <div class="card-header" id="headingTwentySeven">
                    <h5 class="mb-0">Supervisión y Guía Adecuada por Parte de los Padres</h5>
                </div>
                <div id="collapseTwentySeven" class="collapse show" data-parent="#accordionExampleProteccionFamiliares">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['supervision_padres'] ?? 1); ?>" class="form-control-range" id="supervision_padres" name="supervision_padres">
                    </div>
                </div>
            </div>
            <!-- Card 3 -->
            <div class="card">
                <div class="card-header" id="headingTwentyEight">
                    <h5 class="mb-0">Comunicación Abierta y Efectiva en la Familia</h5>
                </div>
                <div id="collapseTwentyEight" class="collapse show" data-parent="#accordionExampleProteccionFamiliares">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['comunicacion_familiar'] ?? 1); ?>" class="form-control-range" id="comunicacion_familiar" name="comunicacion_familiar">
                    </div>
                </div>
            </div>
            <!-- Card 4 -->
            <div class="card">
                <div class="card-header" id="headingTwentyNine">
                    <h5 class="mb-0">Presencia de al Menos un Adulto Significativo y de Confianza</h5>
                </div>
                <div id="collapseTwentyNine" class="collapse show" data-parent="#accordionExampleProteccionFamiliares">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['adulto_significativo'] ?? 1); ?>" class="form-control-range" id="adulto_significativo" name="adulto_significativo">
                    </div>
                </div>
            </div>
            <!-- Card 5 -->
            <div class="card">
                <div class="card-header" id="headingThirty">
                    <h5 class="mb-0">Prácticas de Crianza Positivas y Consistentes</h5>
                </div>
                <div id="collapseThirty" class="collapse show" data-parent="#accordionExampleProteccionFamiliares">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['practicas_crianza'] ?? 1); ?>" class="form-control-range" id="practicas_crianza" name="practicas_crianza">
                    </div>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Guardar y Continuar</button>
        <a type="button" class="btn btn-secondary mt-3" href="seccion5.php">Anterior</a>
        <a href="resumen.php" class="btn btn-secondary mt-3">Ir al resumen</a>
    </form>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
