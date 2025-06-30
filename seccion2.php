<?php include_once("config.php"); ?>
<?php include_once("header.php"); ?>
<?php
if (!isset($_SESSION['inserted_id'])) {
    echo "Error: No hay un ID de evaluación vinculado.";
    exit;
}

$evaluacion_id = $_SESSION['inserted_id'];

// Recuperar los valores existentes si ya hay un registro
$query = "SELECT * FROM seccion2 WHERE evaluacion_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $evaluacion_id);
$stmt->execute();
$result = $stmt->get_result();
$existing_data = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $historia_salud_mental = $_POST['historia_salud_mental'] ?? 1;
    $abuso_sustancias = $_POST['abuso_sustancias'] ?? 1;
    $comportamiento_emocional = $_POST['comportamiento_emocional'] ?? 1;
    $autoestima = $_POST['autoestima'] ?? 1;
    $enfermedades_cronicas = $_POST['enfermedades_cronicas'] ?? 1;
    $estres_traumatico = $_POST['estres_traumatico'] ?? 1;

    if ($existing_data) {
        // Actualizar el registro existente
        $query = "UPDATE seccion2 SET historia_salud_mental = ?, abuso_sustancias = ?, comportamiento_emocional = ?, autoestima = ?, enfermedades_cronicas = ?, estres_traumatico = ? WHERE evaluacion_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiiiiii", $historia_salud_mental, $abuso_sustancias, $comportamiento_emocional, $autoestima, $enfermedades_cronicas, $estres_traumatico, $evaluacion_id);
        if ($stmt->execute()) {
            header('Location: seccion3.php');
        } else {
            echo "Error al actualizar los datos de la sección 2: " . $stmt->error;
        }
        $stmt->close();
    } else {
        // Insertar un nuevo registro si no existe
        $query = "INSERT INTO seccion2 (evaluacion_id, historia_salud_mental, abuso_sustancias, comportamiento_emocional, autoestima, enfermedades_cronicas, estres_traumatico) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiiiiii", $evaluacion_id, $historia_salud_mental, $abuso_sustancias, $comportamiento_emocional, $autoestima, $enfermedades_cronicas, $estres_traumatico);
        if ($stmt->execute()) {
            header('Location: seccion3.php');
        } else {
            echo "Error al guardar los datos de la sección 2: " . $stmt->error;
        }
        $stmt->close();
    }
    $conn->close();
}
?>
<div class="container mt-5">
    <h3>Factores Personales</h3>
    <form method="POST" action="seccion2.php">
        <div class="accordion" id="accordionExample">
            <!-- Card 1 -->
            <div class="card">
                <div class="card-header" id="headingOne">
                    <h5 class="mb-0">
                        Historia Familiar de Problemas de Salud Mental
                    </h5>
                </div>
                <div id="collapseOne" class="collapse show" data-parent="#accordionExample">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['historia_salud_mental'] ?? 1); ?>" class="form-control-range" id="historia_salud_mental" name="historia_salud_mental">
                    </div>
                </div>
            </div>
            <!-- Card 2 -->
            <div class="card">
                <div class="card-header" id="headingTwo">
                    <h5 class="mb-0">
                        Antecedentes de Abuso de Sustancias
                    </h5>
                </div>
                <div id="collapseTwo" class="collapse show" data-parent="#accordionExample">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['abuso_sustancias'] ?? 1); ?>" class="form-control-range" id="abuso_sustancias" name="abuso_sustancias">
                    </div>
                </div>
            </div>
            <!-- Card 3 -->
            <div class="card">
                <div class="card-header" id="headingThree">
                    <h5 class="mb-0">
                        Problemas de Comportamiento y de Regulación Emocional
                    </h5>
                </div>
                <div id="collapseThree" class="collapse show" data-parent="#accordionExample">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['comportamiento_emocional'] ?? 1); ?>" class="form-control-range" id="comportamiento_emocional" name="comportamiento_emocional">
                    </div>
                </div>
            </div>
            <!-- Card 4 -->
            <div class="card">
                <div class="card-header" id="headingFour">
                    <h5 class="mb-0">
                        Baja Autoestima
                    </h5>
                </div>
                <div id="collapseFour" class="collapse show" data-parent="#accordionExample">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['autoestima'] ?? 1); ?>" class="form-control-range" id="autoestima" name="autoestima">
                    </div>
                </div>
            </div>
            <!-- Card 5 -->
            <div class="card">
                <div class="card-header" id="headingFive">
                    <h5 class="mb-0">
                        Enfermedades Crónicas o Discapacidades Físicas
                    </h5>
                </div>
                <div id="collapseFive" class="collapse show" data-parent="#accordionExample">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['enfermedades_cronicas'] ?? 1); ?>" class="form-control-range" id="enfermedades_cronicas" name="enfermedades_cronicas">
                    </div>
                </div>
            </div>
            <!-- Card 6 -->
            <div class="card">
                <div class="card-header" id="headingSix">
                    <h5 class="mb-0">
                        Estrés Prolongado o Traumático
                    </h5>
                </div>
                <div id="collapseSix" class="collapse show" data-parent="#accordionExample">
                    <div class="card-body">
                        <input type="range" min="1" max="4" step="1" value="<?php echo htmlspecialchars($existing_data['estres_traumatico'] ?? 1); ?>" class="form-control-range" id="estres_traumatico" name="estres_traumatico">
                    </div>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Guardar y Continuar</button>
        <a type="button" class="btn btn-secondary mt-3" href="seccion1.php">Anterior</a>
        <a href="resumen.php" class="btn btn-secondary mt-3">Ir al resumen</a>
    </form>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>