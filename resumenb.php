<?php
session_start();
include_once("config.php");
include_once("header.php");

// Posible cambio o carga de evaluación actual mediante GET
if (isset($_GET['evaluacion_id'])) {
    $_SESSION['inserted_id'] = $_GET['evaluacion_id'];
}

// Verificar que exista una evaluación en curso
if (!isset($_SESSION['inserted_id'])) {
    echo "Error: No se ha iniciado una evaluación.";
    exit();
}

$evaluacion_id = $_SESSION['inserted_id'];

// Arreglo para almacenar posibles errores
$errors = [];

// Procesamiento del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $valoracion_global = $_POST['valoracion_global'] ?? '';
    $comentarios = $_POST['comentarios'] ?? '';

    if (empty($valoracion_global)) {
        $errors['valoracion_global'] = "Por favor, seleccione una valoración global del nivel de riesgo.";
    }

    if (empty($errors)) {
        $query = "UPDATE evaluacion SET valoracion_global = ?, comentarios = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        if ($stmt === false) {
            die('Error en la preparación de la consulta: ' . $conn->error);
        }
        $stmt->bind_param("ssi", $valoracion_global, $comentarios, $evaluacion_id);

        if ($stmt->execute()) {
            header("Location: homepage.php");
            exit();
        } else {
            $errors['general'] = "Error al guardar los datos: " . $stmt->error;
        }
        $stmt->close();
    }
}

/** 
 * Función para obtener los valores guardados en las tablas
 * de factores (individuales, familiares, contextuales).
 */
function obtenerFactores($tabla, $evaluacion_id, $conn) {
    $query = "SELECT * FROM $tabla WHERE evaluacion_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $evaluacion_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    return $data;
}

// Obtenemos los datos ya guardados en BD
$factores_individuales = obtenerFactores('factores_individuales', $evaluacion_id, $conn);
$factores_familiares   = obtenerFactores('factores_familiares',   $evaluacion_id, $conn);
$factores_contextuales = obtenerFactores('factores_contextuales', $evaluacion_id, $conn);

// Recuperar la valoración global y comentarios
$query = "SELECT valoracion_global, comentarios FROM evaluacion WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $evaluacion_id);
$stmt->execute();
$stmt->bind_result($valoracion_global_actual, $comentarios_actuales);
$stmt->fetch();
$stmt->close();

// ===========================================================================
// Definición de factores de RIESGO y PROTECCIÓN en arreglos separados
// ===========================================================================
$factores_riesgo = [
    'Factores Individuales del Niño, Niña o Adolescente' => [
        'enfermedades_cronicas_discapacidad' => '1.1. Enfermedades crónicas / discapacidad',
        'alteraciones_graves_comportamiento' => '1.2. Alteraciones graves del comportamiento',
        'desvinculacion_ausentismo_escolar' => '1.3. Desvinculación y ausentismo escolar',
        'denuncias_ingresos_maltrato_previo' => '1.4. Denuncias o ingresos por maltrato previo'
    ],
    'Factores Familiares' => [
        'problemas_salud_mental_cuidadores' => '2.1. Problemas de salud mental de personas cuidadoras',
        'consumo_problematico_cuidadores' => '2.2. Consumo problemático de alcohol y sustancias',
        'violencia_pareja' => '2.3. Violencia en la pareja',
        'historia_maltrato_cuidadores' => '2.4. Historia de maltrato de personas cuidadoras',
        'antecedentes_penales_cuidadores' => '2.5. Antecedentes penales de personas cuidadoras',
        'dificultades_soporte_social' => '2.6. Dificultades de soporte social',
        'estres_supervivencia' => '2.7. Estrés de supervivencia',
        'deficiencia_habilidades_cuidado' => '2.8. Deficiencia en habilidades de cuidado',
        'actitudes_negativas_nna' => '2.9. Actitudes negativas hacia el niño, niña o adolescente',
        'atencion_prenatal_retrasada_ausente' => '2.10. Atención prenatal retrasada o ausente',
        'inestabilidad_cuidados' => '2.11. Inestabilidad en los cuidados',
        'ideacion_suicida_cuidadores' => '2.12. Ideación suicida de personas cuidadoras',
        'actitudes_negativas_intervencion' => '2.13. Actitudes negativas hacia la intervención',
        'extrema_minimizacion_negacion_maltrato' => '2.15. Extrema minimización o negación del maltrato',
        'reunificaciones_fallidas' => '2.17. Reunificaciones fallidas'
    ],
    'Factores Contextuales' => [
        'historia_maltrato_perpetrador' => '3.1. Historia de maltrato durante la infancia del perpetrador o perpetradora',
        'involucramiento_previo_servicio_proteccion' => '3.3. Involucramiento previo en Servicio de Protección'
    ]
];

$factores_proteccion = [
    'Factores Individuales del Niño, Niña o Adolescente' => [
        'terapia_nna' => '1.5. Terapia para el niño, niña o adolescente'
    ],
    'Factores Familiares' => [
        'compromiso_colaborativo' => '2.14. Compromiso colaborativo',
        'terapia_cuidadores' => '2.16. Terapia para padres o personas cuidadoras'
    ],
    'Factores Contextuales' => [
        'presencia_pares_confianza_nna' => '3.2. Presencia de pares de confianza para el niño, niña o adolescente'
    ]
];

// Etiquetas de columnas
$columnas_riesgo = [
    'a' => 'No es posible determinar',
    'b' => 'Riesgo nulo o bajo',
    'c' => 'Riesgo medio',
    'd' => 'Riesgo alto'
];
$columnas_proteccion = [
    'a' => 'No es posible determinar',
    'b' => 'Protección nula o baja',
    'c' => 'Protección media',
    'd' => 'Protección alta'
];

/**
 * Devuelve el valor (a,b,c,d) para el factor, o 'a' si no existe
 */
function obtenerValorFactor($campo, $ind, $fam, $ctx) {
    return $ind[$campo] ?? $fam[$campo] ?? $ctx[$campo] ?? 'a';
}

/**
 * Devuelve la clase de color de fondo según sea factor de riesgo/protección,
 * y la clave (a,b,c,d).
 */
function obtenerClaseColor($tipo, $clave, $valor) {
    // Si no coincide el valor seleccionado con esta columna, no ponemos color
    if ($valor !== $clave) {
        return '';
    }
    // Si coincide, coloreamos según tipo y nivel
    if ($tipo === 'riesgo') {
        switch ($clave) {
            case 'a': return 'bg-secondary'; // Gris
            case 'b': return 'bg-success';   // Verde
            case 'c': return 'bg-warning';   // Amarillo
            case 'd': return 'bg-danger';    // Rojo
        }
    } else {
        // tipo === 'proteccion'
        switch ($clave) {
            case 'a': return 'bg-secondary'; // Gris
            case 'b': return 'bg-danger';    // Rojo (protección nula/baja)
            case 'c': return 'bg-warning';   // Amarillo
            case 'd': return 'bg-success';   // Verde
        }
    }
    return '';
}

/**
 * Imprime una fila con 5 columnas:
 * - 1 para el nombre del factor
 * - 4 columnas (a,b,c,d) donde la que coincida con $valor se pinta.
 */
function imprimirFilaFactor($campo, $label, $valor, $columnas, $tipo) {
    echo "<tr>";
    // Nombre del factor en la primera columna
    echo "<td>" . htmlspecialchars($label) . "</td>";
    // Para cada columna (a, b, c, d), vemos si coincide con $valor
    foreach ($columnas as $clave => $etiqueta) {
        $clase = obtenerClaseColor($tipo, $clave, $valor);
        $contenido = ($valor === $clave) ? 'X' : '';
        echo "<td class='text-center $clase'>$contenido</td>";
    }
    echo "</tr>";
}

/**
 * Determina el link de edición en función de la dimensión
 */
function enlaceEdicion($dimension) {
    if ($dimension === 'Factores Individuales del Niño, Niña o Adolescente') {
        return 'seccion2b.php';
    } elseif ($dimension === 'Factores Familiares') {
        return 'seccion3b.php';
    } elseif ($dimension === 'Factores Contextuales') {
        return 'seccion4b.php';
    }
    return '#'; // Por defecto
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Evaluación de Riesgo</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" 
          href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Síntesis de Factores</h2>

    <!-- Mostrar errores si los hubiera -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
            <?php foreach ($errors as $campo => $mensaje): ?>
                <li><?php echo htmlspecialchars($mensaje); ?></li>
            <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- =========================================================
         TABLA DE FACTORES DE RIESGO
         ========================================================= -->
    <h3 class="mt-4">Factores de Riesgo</h3>
    <p class="mb-3">
        Las siguientes tablas sintetizan las respuestas que usted indicó en cada uno de los factores de riesgo.
        Considerando la información aquí contenida, evalúe el nivel de riesgo global que presenta el niño, niña o adolescente.
    </p>
    
    <table class="table table-bordered table-sm">
        <thead>
            <tr class="bg-light">
                <th>Dimensión / Factores de Riesgo</th>
                <th><?php echo $columnas_riesgo['a']; ?></th>
                <th><?php echo $columnas_riesgo['b']; ?></th>
                <th><?php echo $columnas_riesgo['c']; ?></th>
                <th><?php echo $columnas_riesgo['d']; ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($factores_riesgo as $dimension => $factoresGrupo): ?>
            <!-- Fila que marca la DIMENSIÓN -->
            <tr class="table-secondary font-weight-bold">
                <td colspan="5">
                    <?php echo htmlspecialchars($dimension); ?>
                    <a href="<?php echo enlaceEdicion($dimension); ?>" class="btn btn-sm btn-link float-right">
                        Editar Sección
                    </a>
                </td>
            </tr>
            <!-- Filas para cada factor de riesgo de esta dimensión -->
            <?php foreach ($factoresGrupo as $campo => $label): ?>
                <?php 
                $valor = obtenerValorFactor($campo, $factores_individuales, $factores_familiares, $factores_contextuales); 
                imprimirFilaFactor($campo, $label, $valor, $columnas_riesgo, 'riesgo');
                ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- =========================================================
         TABLA DE FACTORES DE PROTECCIÓN
         ========================================================= -->
    <h3 class="mt-5">Factores de Protección</h3>
    <p class="mb-3">
        Las siguientes tablas sintetizan las respuestas que usted indicó en cada uno de los factores protectores.
        Considerando la información aquí contenida, evalúe el nivel de riesgo global que presenta el niño, niña o adolescente.
    </p>
    
    <table class="table table-bordered table-sm">
        <thead>
            <tr class="bg-light">
                <th>Dimensión / Factores Protectores</th>
                <th><?php echo $columnas_proteccion['a']; ?></th>
                <th><?php echo $columnas_proteccion['b']; ?></th>
                <th><?php echo $columnas_proteccion['c']; ?></th>
                <th><?php echo $columnas_proteccion['d']; ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($factores_proteccion as $dimension => $factoresGrupo): ?>
            <tr class="table-secondary font-weight-bold">
                <td colspan="5">
                    <?php echo htmlspecialchars($dimension); ?>
                    <a href="<?php echo enlaceEdicion($dimension); ?>" class="btn btn-sm btn-link float-right">
                        Editar Sección
                    </a>
                </td>
            </tr>
            <?php foreach ($factoresGrupo as $campo => $label): ?>
                <?php 
                $valor = obtenerValorFactor($campo, $factores_individuales, $factores_familiares, $factores_contextuales);
                imprimirFilaFactor($campo, $label, $valor, $columnas_proteccion, 'proteccion');
                ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
        </tbody>
    </table>

    <hr>

    <!-- Sección para la predicción automática (opcional) y la valoración global -->
    <h3>Valoración Global del Nivel de Riesgo de Revictimización</h3>
    <form method="POST" action="">
        <div class="card mb-4">
            <div class="card-body">
                <!-- Selección manual de nivel de riesgo (bajo, medio, alto) -->
                <div class="form-group">
                    <label>Seleccione el nivel de riesgo (manual):</label>
                    <?php
                    $niveles_riesgo = [
                        'bajo' => 'Riesgo Bajo',
                        'medio' => 'Riesgo Medio',
                        'alto' => 'Riesgo Alto'
                    ];
                    ?>
                    <?php foreach ($niveles_riesgo as $key => $value): ?>
                        <div class="custom-control custom-radio mb-2">
                            <input 
                                class="custom-control-input"
                                type="radio"
                                name="valoracion_global"
                                id="valoracion_<?php echo $key; ?>"
                                value="<?php echo $key; ?>"
                                <?php if (($valoracion_global_actual ?? '') === $key) echo 'checked'; ?>
                            >
                            <label class="custom-control-label" for="valoracion_<?php echo $key; ?>">
                                <?php echo htmlspecialchars($value); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- Error específico para valoracion_global, si existe -->
                    <?php if (isset($errors['valoracion_global'])): ?>
                        <small class="text-danger">
                            <?php echo htmlspecialchars($errors['valoracion_global']); ?>
                        </small>
                    <?php endif; ?>
                </div>

                <hr>
                <!-- (Si lo deseas, conserva la funcionalidad del modelo automático) -->
                <div class="form-group">
                    <label>Valoración basada en el modelo (automática):</label>
                    <p id="prediccion-modelo" class="font-weight-bold text-primary"></p>
                    <div class="d-flex align-items-center">
                        <button type="button" class="btn btn-info mr-2" onclick="obtenerPrediccion()">
                            Obtener Predicción del Modelo
                        </button>
                        <button type="button" class="btn btn-link p-0 m-0" style="font-size:1.2em; line-height:1;"
                                onclick="toggleExplicacionModelo()"
                                title="¿Qué toma en cuenta el modelo?">
                            <span>❓</span>
                        </button>
                    </div>

                    <div id="explicacion-modelo" style="display:none; margin-top:10px; background:#f8f9fa; padding:10px; border-radius:5px;">
                        <h5>¿Qué toma en cuenta el modelo?</h5>
                        <p>Este modelo se basa en la metodología PROTEGE y considera una amplia gama de factores para estimar el riesgo de revictimización infantil. Entre ellos se incluyen:</p>
                        <ul>
                            <li><strong>Factores individuales:</strong> características propias del NNA (enfermedades, comportamiento, escolaridad, etc.).</li>
                            <li><strong>Factores familiares:</strong> condiciones del entorno familiar (salud mental de cuidadores, consumo problemático, antecedentes penales, etc.).</li>
                            <li><strong>Factores contextuales:</strong> elementos externos al núcleo familiar (historia de maltrato del perpetrador, pares de confianza, involucramiento en sistemas de protección, etc.).</li>
                        </ul>
                        <p>Al integrar y analizar todas estas variables, el modelo produce una estimación informada del nivel de riesgo de recurrencia en la victimización, ayudando a orientar la toma de decisiones y la asignación de recursos de protección de forma más efectiva.</p>
                    </div>
                </div>

                <hr>
                <!-- Comentarios adicionales -->
                <div class="form-group">
                    <label>Comentarios adicionales (opcional):</label>
                    <textarea class="form-control" name="comentarios" rows="4"><?php echo htmlspecialchars($comentarios_actuales ?? ''); ?></textarea>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary">Guardar Valoración</button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Scripts (jQuery y Bootstrap) -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    const evaluationId = <?php echo (int)$evaluacion_id; ?>;

    async function obtenerPrediccion() {
        const url = 'predecir.php?evaluation_id=' + evaluationId;
        const predDiv = document.getElementById('prediccion-modelo');
        predDiv.className = '';
        predDiv.textContent = '';

        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error('Error en la respuesta de la API');
            }
            const data = await response.json();
            if (data.error) {
                predDiv.textContent = 'Error: ' + data.error;
            } else {
                let texto = '';
                let clase = '';

                if (data.prediccion === 'bajo') {
                    texto = 'Riesgo Bajo';
                    clase = 'text-success font-weight-bold';
                } else if (data.prediccion === 'medio') {
                    texto = 'Riesgo Medio';
                    clase = 'text-warning font-weight-bold';
                } else if (data.prediccion === 'alto') {
                    texto = 'Riesgo Alto';
                    clase = 'text-danger font-weight-bold';
                }
                predDiv.textContent = 'Predicción del modelo: ' + texto;
                predDiv.className = clase;
            }
        } catch (error) {
            predDiv.textContent = 'Error al obtener la predicción: ' + error.message;
        }
    }

    function toggleExplicacionModelo() {
        const seccion = document.getElementById('explicacion-modelo');
        seccion.style.display = (seccion.style.display === 'none' || seccion.style.display === '') ? 'block' : 'none';
    }
</script>
</body>
</html>

