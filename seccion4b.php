<?php
session_start();
// Buffer output so headers can be sent after DB operations
ob_start();
include_once("config.php");
include_once("header.php");
include_once("utils/authorization.php");

if (isset($_GET['evaluacion_id']) && is_numeric($_GET['evaluacion_id'])) {
    $_SESSION['inserted_id'] = (int) $_GET['evaluacion_id'];
}

// Verificar si hay una evaluación en curso o recuperar la más reciente del usuario
if (!isset($_SESSION['inserted_id'])) {
    $userid = $_SESSION['userid'] ?? null;
    if ($userid) {
        $stmt = sqlsrv_query(
            $conn,
            "SELECT TOP 1 id FROM dbo.evaluacion WHERE user_id = ? ORDER BY id DESC",
            [$userid]
        );
        if ($stmt !== false && ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))) {
            $_SESSION['inserted_id'] = $row['id'];
        }
        if ($stmt !== false) {
            sqlsrv_free_stmt($stmt);
        }
    }
    if (!isset($_SESSION['inserted_id'])) {
        header("Location: seccion1.php");
        ob_end_clean();
        exit();
    }
}
$evaluacion_id = isset($_SESSION['inserted_id']) ? (int) $_SESSION['inserted_id'] : null;
$evaluacionIdQuery = $evaluacion_id !== null ? '?evaluacion_id=' . $evaluacion_id : '';

verificarAccesoEvaluacion($conn, $evaluacion_id);

// Inicializar variables para almacenar mensajes de error
$errors = [];

// Inicializar variables para los campos del formulario
$campos = [
    'historia_maltrato_perpetrador' => '',
    'presencia_pares_confianza_nna' => '',
    'involucramiento_previo_servicio_proteccion' => ''
];

// Procesar el formulario al enviarlo
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        ob_end_clean();
        die('Invalid CSRF token');
    }
    // Obtener los valores enviados desde el formulario
    foreach ($campos as $campo => $valor) {
        $campos[$campo] = $_POST[$campo] ?? '';
        if (empty($campos[$campo])) {
            $errors[$campo] = "Por favor, seleccione una opción.";
        }
    }

    // Si no hay errores, insertar o actualizar los datos en la base de datos
    if (empty($errors)) {
        // Verificar si ya existe un registro en factores_contextuales para esta evaluación
        $query_check = "SELECT id FROM dbo.factores_contextuales WHERE evaluacion_id = ?";
        verificarAccesoEvaluacion($conn, $evaluacion_id);
        $stmt_check = sqlsrv_query($conn, $query_check, [$evaluacion_id]);
        $existing_data = $stmt_check !== false ? sqlsrv_fetch_array($stmt_check, SQLSRV_FETCH_ASSOC) : [];
        if ($stmt_check !== false) {
            sqlsrv_free_stmt($stmt_check);
        }

        if ($existing_data) {
            // Actualizar el registro existente
            $query = "UPDATE dbo.factores_contextuales SET
                historia_maltrato_perpetrador = ?,
                presencia_pares_confianza_nna = ?,
                involucramiento_previo_servicio_proteccion = ?
                WHERE evaluacion_id = ?";
            $params = [
                $campos['historia_maltrato_perpetrador'],
                $campos['presencia_pares_confianza_nna'],
                $campos['involucramiento_previo_servicio_proteccion'],
                $evaluacion_id
            ];
            verificarAccesoEvaluacion($conn, $evaluacion_id);
            $stmt = sqlsrv_query($conn, $query, $params);
        } else {
            // Insertar un nuevo registro
            $query = "INSERT INTO dbo.factores_contextuales (
                evaluacion_id,
                historia_maltrato_perpetrador,
                presencia_pares_confianza_nna,
                involucramiento_previo_servicio_proteccion
            ) VALUES (?, ?, ?, ?)";
            $params = [
                $evaluacion_id,
                $campos['historia_maltrato_perpetrador'],
                $campos['presencia_pares_confianza_nna'],
                $campos['involucramiento_previo_servicio_proteccion']
            ];
            verificarAccesoEvaluacion($conn, $evaluacion_id);
            $stmt = sqlsrv_query($conn, $query, $params);
        }

        if ($stmt !== false) {
            sqlsrv_free_stmt($stmt);
            // Redirigir al resumen o siguiente sección
            header('Location: resumenb.php' . $evaluacionIdQuery);
            ob_end_clean();
            exit();
        } else {
            $errors['general'] = "Error al guardar los datos: " . print_r(sqlsrv_errors(), true);
        }
    }
} else {
    // Si no se ha enviado el formulario, verificar si ya existe un registro
    $query = "SELECT * FROM dbo.factores_contextuales WHERE evaluacion_id = ?";
    verificarAccesoEvaluacion($conn, $evaluacion_id);
    $stmt = sqlsrv_query($conn, $query, [$evaluacion_id]);
    $existing_data = $stmt !== false ? sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) : [];
    if ($stmt !== false) {
        sqlsrv_free_stmt($stmt);
    }

    if ($existing_data) {
        foreach ($campos as $campo => $valor) {
            $campos[$campo] = $existing_data[$campo];
        }
    }
}

sqlsrv_close($conn);

/**
 * Definimos las opciones directamente en el array de factores,
 * cada alternativa incluye un resumen de su significado.
 */

$factores = [
    'historia_maltrato_perpetrador' => [
        'label' => '3.1. Historia de maltrato durante la infancia del perpetrador o perpetradora (no cuidador/a)',
        'descripcion' => 'Aplica solo si la persona que perpetra la victimización NO es la persona a cargo del cuidado.',
        'info' => 'La persona que perpetra la victimización no es necesariamente quien ostenta el cuidado. Por lo tanto, en este ítem se recoge la historia de maltrato de aquella figura no cuidadora que ha perpetrado la victimización y solo aplica en este caso.\nLa vivencia de abuso en la infancia representa un factor de riesgo para la adopción de conductas abusivas en la edad adulta, aunque la conexión entre estos dos factores no debe ser interpretada como una relación directa e inevitable (Paúl et al., 2002). La recurrencia del maltrato hacia niños, niñas y adolescentes se asocia a la historia de maltrato infantil del perpetrador o perpetradora (Horikawa et al., 2016).',
        'opciones' => [
            'a' => 'No es posible determinar / No aplica — Falta información, por lo cual no es posible determinar el riesgo asociado a la historia de maltrato en la infancia de aquella figura no cuidadora que perpetra la victimización. No aplica cuando la figura perpetradora es, a la vez, cuidadora.',
            'b' => 'Riesgo nulo o bajo — Se carece de reportes de abuso o maltrato durante la infancia del perpetrador o perpetradora no cuidadora. En caso de que refiera estas experiencias, estos hechos fueron leves, acotados y no sistemáticos.',
            'c' => 'Riesgo medio — El perpetrador o perpetradora no cuidadora sufrió episodios de maltrato infantil o negligencia de carácter moderado.',
            'd' => 'Riesgo alto — El perpetrador o perpetradora no cuidadora sufrió episodios de maltrato infantil o negligencia de carácter grave y/o sistemáticos en el tiempo.'
        ]
    ],

    'presencia_pares_confianza_nna' => [
        'label' => '3.2. Presencia de pares de confianza para el niño, niña o adolescente',
        'descripcion' => 'La calidad de las relaciones con pares puede funcionar como factor protector.',
        'info' => 'La calidad de las relaciones con los pares tiene un efecto moderador y protector del riesgo de recurrencia victimal en niños, niñas y adolescentes que han sufrido abuso o maltrato (Huanhuan-Wang et al., 2023).\nEstudios en este tema han señalado que las relaciones de calidad con los pares funcionan como un factor protector en experiencias de victimización por abusos o maltratos (Meinck, 2017), siendo personas con las que puede contar y que pueden ayudarle cuando tiene un problema personal (Meinck, 2017).\nEn contraste, las relaciones escasas o de baja calidad con el grupo constituyen un factor de riesgo en estas poblaciones, siendo estos jóvenes potencialmente rechazados o impopulares entre sus pares (Favre et al., 2022).',
        'opciones' => [
            'a' => 'No es posible determinar — Falta información, por lo cual no es posible determinar la existencia de protección asociada a la presencia de pares de confianza para el niño, niña o adolescente.',
            'b' => 'Protección nula o baja — El niño, niña o adolescente no cuenta con relaciones cercanas ni de calidad con pares.',
            'c' => 'Protección media — El niño, niña o adolescente cuenta con al menos un par de confianza al cual recurrir.',
            'd' => 'Protección alta — El niño, niña o adolescente mantiene relaciones de calidad con varios de sus pares, pudiendo recurrir a varios de ellos.'
        ]
    ],

    'involucramiento_previo_servicio_proteccion' => [
        'label' => '3.3. Involucramiento previo en Servicio de Protección',
        'descripcion' => 'Los ingresos previos al sistema de protección aumentan el riesgo de recurrencia victimal.',
        'info' => 'Los niños, niñas y adolescentes previamente involucrados con los sistemas proteccionales tienen una mayor probabilidad de presentar nuevos reportes o recurrencia victimal, en comparación con aquellos que no han estado involucrados con el sistema (Casanueva et al., 2015; Choi & Kim, 2022; Eastman et al., 2016; Hélie et al., 2014; Kim et al., 2022; Pierce et al., 2017).\nEn Chile, los niños, niñas y adolescentes atendidos por el sistema de protección especializada presentan –en su mayoría– una historia de institucionalización e intervención de larga data por parte del Estado (SENAME, 2019). En este caso, la multiplicidad de ingresos al Servicio de Protección Especializada constituyen un proxy a los nuevos reportes de abuso o maltrato, que aumentan el riesgo de futuras victimizaciones.',
        'opciones' => [
            'a' => 'No es posible determinar — Falta información, por lo cual no es posible determinar el riesgo asociado a involucramiento previo en servicios de protección.',
            'b' => 'Riesgo nulo o bajo — El niño, niña o adolescente no presenta historial de ingresos previos al Servicio de Protección Especializada.',
            'c' => 'Riesgo medio — El niño, niña o adolescente presenta 1 o 2 ingresos previos al Servicio de Protección Especializada.',
            'd' => 'Riesgo alto — El niño, niña o adolescente presenta historial de numerosos ingresos previos a los Servicios de Protección Especializada (tres o más).'
        ]
    ]
];

?>
<div class="container mt-5">
    <h2>Sección 4: Factores Contextuales</h2>
    <p>Por favor, selecciona la opción que mejor describa cada factor.</p>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($errors['general']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="seccion4b.php<?= htmlspecialchars($evaluacionIdQuery); ?>">
        <?php csrf_input(); ?>
        <?php foreach ($factores as $campo => $data): ?>
            <div class="card mb-4">
          <div class="card-header d-flex justify-content-between align-items-start">
            <div>
                <strong><?php echo htmlspecialchars($data['label']); ?></strong>
            </div>
            <button type="button"
                    class="btn btn-info btn-sm"
                    data-toggle="modal"
                    data-target="#modal<?php echo $campo; ?>">
                Saber más
            </button>
        </div>

                <div class="card-body">
                    <?php foreach ($data['opciones'] as $key => $value): ?>
                        <div class="custom-control custom-radio mb-2">
                            <input
                                type="radio"
                                id="<?php echo $campo . '_' . $key; ?>"
                                name="<?php echo $campo; ?>"
                                value="<?php echo $key; ?>"
                                class="custom-control-input"
                                <?php if ($campos[$campo] === $key) echo 'checked'; ?>
                            >
                            <label class="custom-control-label" for="<?php echo $campo . '_' . $key; ?>">
                                <?php echo htmlspecialchars($value); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                    <?php if (isset($errors[$campo])): ?>
                        <small class="text-danger">
                            <?php echo htmlspecialchars($errors[$campo]); ?>
                        </small>
                    <?php endif; ?>
                </div>
            </div>

    <!-- Modal específico para este factor -->
    <div class="modal fade" id="modal<?php echo $campo; ?>" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><?= $data['label'] ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if (isset($data['info'])): ?>
                        <div class="lead">Información detallada:</div>
                        <div class="mt-3"><?= nl2br(htmlspecialchars($data['info'])) ?></div>

    <?php else: ?>
                        <div class="text-muted">No hay información adicional disponible</div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

        <?php endforeach; ?>

        <div class="d-flex justify-content-between">
            <a href="seccion3b.php<?= htmlspecialchars($evaluacionIdQuery); ?>" class="btn btn-secondary">Anterior</a>
            <button type="submit" class="btn btn-primary">Siguiente</button>
        </div>
    </form>
</div>

<!-- Scripts de Bootstrap y jQuery (si no están ya incluidos) -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>

