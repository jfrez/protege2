<?php
session_start();
// Buffer output so headers can be sent after DB operations
ob_start();
include_once("config.php");
include_once("header.php");

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
            $evaluacion_id = $row['id'];
        }
        if ($stmt !== false) {
            sqlsrv_free_stmt($stmt);
        }
    }
    if (!isset($_SESSION['inserted_id'])) {
        // No hay evaluación disponible; redirigir a la primera sección
        header("Location: seccion1.php");
        ob_end_clean();
        exit();
    }
}

$evaluacion_id = isset($_SESSION['inserted_id']) ? (int) $_SESSION['inserted_id'] : null;
$evaluacionIdQuery = $evaluacion_id !== null ? '?evaluacion_id=' . $evaluacion_id : '';

// Mostrar el ID de la evaluación para debug (opcional)
// echo $evaluacion_id;

// Inicializar variables para almacenar mensajes de error
$errors = [];

// Inicializar variables para los campos del formulario
$campos = [
    'enfermedades_cronicas_discapacidad' => '',
    'alteraciones_graves_comportamiento' => '',
    'desvinculacion_ausentismo_escolar' => '',
    'denuncias_ingresos_maltrato_previo' => '',
    'terapia_nna' => ''
];

// Cargar valores existentes si ya hay datos en la base de datos
$query = "SELECT * FROM factores_individuales WHERE evaluacion_id = ?";
$stmt = sqlsrv_query($conn, $query, [$evaluacion_id]);
$existing_data = [];
if ($stmt !== false) {
    $existing_data = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) ?: [];
    sqlsrv_free_stmt($stmt);
}

if ($existing_data) {
    foreach ($campos as $campo => $valor) {
        // Si en la BD existe un valor, lo cargamos en $campos.
        $campos[$campo] = $existing_data[$campo] ?? '';
    }
}

/**
 * Escalas de valoración para cada factor según el documento:
 *  - 1.1 a 1.4 usan la escala de "No es posible determinar / Riesgo nulo o bajo / Riesgo medio / Riesgo alto"
 *  - 1.5 (Terapia) usa la escala de "No es posible determinar / Protección nula o baja / Protección media / Protección alta"
 */
$escalas = [
    // 1.1. Enfermedades crónicas / discapacidad (RIESGO)
    'enfermedades_cronicas_discapacidad' => [
        'a' => 'No es posible determinar — Falta información, por lo cual no es posible determinar la existencia de riesgo asociado a la presencia de enfermedades crónicas y/o discapacidad.',
        'b' => 'Riesgo nulo o bajo — El niño, niña o adolescente no presenta ninguna enfermedad crónica y/o discapacidad. O bien, el niño, niña o adolescente presenta una enfermedad crónica y/o discapacidad que no requiere de especial cuidado o apoyo para su participación efectiva.',
        'c' => 'Riesgo medio — El niño, niña o adolescente presenta alguna enfermedad crónica y/o discapacidad, requiriendo de cuidados o apoyos para garantizar su participación efectiva, los que son provistos por el entorno. ',
        'd' => 'Riesgo alto — El niño, niña o adolescente presenta una enfermedad crónica y/o discapacidad que requiere de cuidados y apoyos intensivos para garantizar su participación efectiva, los cuales difícilmente pueden ser provistos por el entorno.     '
    ],
    // 1.2. Alteraciones graves del comportamiento (RIESGO)
    'alteraciones_graves_comportamiento' => [
        'a' => 'No es posible determinar — Falta información, por lo cual no es posible determinar el riesgo asociado a la existencia de alteraciones graves del comportamiento.',
        'b' => 'Riesgo nulo o bajo — La conducta del niño, niña o adolescente no presenta ningún tipo de desajuste significativo, que ponga en riesgo a sí mismo o a los demás. ',
        'c' => 'Riesgo medio — El niño, niña o adolescente presenta algunas alteraciones del comportamiento que dificultan su interacción social y que son desafiantes en su manejo para los adultos a cargo.',
        'd' => 'Riesgo alto — El niño, niña o adolescente presenta alteraciones graves del comportamiento, que lo han hecho ponerse en riesgo a sí mismo o han puesto en riesgo a otros.'
    ],
    // 1.3. Desvinculación y ausentismo escolar (RIESGO)
    'desvinculacion_ausentismo_escolar' => [
        'a' => 'No es posible determinar — Falta información, por lo cual no es posible determinar la existencia de riesgo asociado a desvinculación y ausentismo escolar. ',
        'b' => 'Riesgo nulo o bajo — El niño, niña o adolescente se encuentra matriculado en el sistema escolar y asiste con regularidad. Las ausencias son esporádicas y suelen obedecer a razones médicas o de fuerza mayor.',
        'c' => 'Riesgo medio — El niño, niña o adolescente se encuentra matriculado, pero se registra un promedio de asistencia entre el 85 y 90%.',
        'd' => 'Riesgo alto — El niño, niña o adolescente no se encuentra matriculado, presentando una desvinculación del sistema escolar, o bien está matriculado pero su promedio de asistencia es menor al 85%.'
    ],
    // 1.4. Denuncias o ingresos por maltrato previo (RIESGO)
    'denuncias_ingresos_maltrato_previo' => [
        'a' => 'No es posible determinar — Falta información, por lo cual no es posible determinar la existencia de riesgo asociado a denuncias en Tribunales de Familia o Fiscalía o ingresos al sistema de protección por maltrato previo. ',
        'b' => 'Riesgo nulo o bajo — No se registran denuncias en Tribunales de Familia o Fiscalía ni ingresos previos del niño, niña o adolescente al sistema de protección.',
        'c' => 'Riesgo medio — Se registra alguna denuncia previa en Tribunales de Familia o Fiscalía de maltrato o algún ingreso del niño, niña o adolescente al sistema de protección. Este ingreso o denuncia refiere a maltrato de un solo tipo y/o su severidad es moderada.',
        'd' => 'Riesgo alto — Se registran múltiples denuncias en Tribunales de Familia o Fiscalía o ingresos previos por maltrato hacia el niño, niña o adolescente en el sistema de protección especializada. Las formas de violencia son múltiples y/o severas.'
    ],
    // 1.5. Terapia para el niño, niña o adolescente (PROTECCIÓN)
    'terapia_nna' => [
        'a' => 'No es posible determinar — Falta información, por lo cual no es posible determinar la existencia de protección asociada a la asistencia a psicoterapia por parte del niño, niña o adolescente.',
        'b' => 'Protección nula o baja — El niño, niña o adolescente no ha recibido tratamiento psicoterapéutico previamente.      ',
        'c' => 'Protección media — El niño, niña o adolescente ha seguido un proceso psicoterapéutico durante un período, pero se ha presentado una deserción.',
        'd' => 'Protección alta — El niño, niña o adolescente ha seguido un proceso psicoterapéutico y lo ha finalizado.'
    ]
];

// Factores con descripciones
$factores = [
    'enfermedades_cronicas_discapacidad' => [
	    'label' => '1.1. Enfermedades crónicas / discapacidad',
	    'info'=>'La presencia de alguna clase de enfermedad crónica (de Ruiter et al., 2020) o de discapacidad (Kim et al., 2020) en el niño, niña o adolescente han sido identificados como factores de riesgo para que se presente un nuevo hecho de victimización.<br>Las enfermedades crónicas no transmisibles generan consecuencias para la salud en el largo plazo, lo cual suele requerir tratamiento y cuidados que se extienden en el tiempo (OPS, s/f), pudiendo resultar demandante para quienes están a cargo de su cuidado. Entre las enfermedades crónicas no transmisibles se consideran, por ejemplo, “enfermedades cardiovasculares, el cáncer, la diabetes y las enfermedades respiratorias crónicas” (OPS, 2019, p. 2).<br>Por su parte, la discapacidad “abarca todas las deficiencias, las limitaciones para realizar actividades y las restricciones de participación, y se refiere a los aspectos negativos de la interacción entre una persona (que tiene una condición de salud) y los factores contextuales de esa persona” (OMS, 2011, p. 4). Desde el modelo biopsicosocial (Pérez y Chhabra, 2019), la discapacidad no se sitúa en la persona ni en la sociedad, sino que es producto de la interacción entre ambos. El foco está puesto en los apoyos que requiere una persona en situación de discapacidad para alcanzar la plena participación comunitaria, social y política, en igualdad de condiciones.<br>De acuerdo al Decreto 47 (2013), las condiciones de salud que pueden causar discapacidad (art. 9) son: (1) deficiencias físicas, relativas al menoscabo de la capacidad física o destreza motora; (2) deficiencias sensoriales, relativas a condiciones visuales, auditivas o de la comunicación y; (3) deficiencias mentales, de causa psíquica (relativas a trastornos del comportamiento adaptativo) o intelectual (vinculadas a bajo rendimiento intelectual). ',
        'descripcion' => 'Presencia de enfermedades crónicas o discapacidad que pueden aumentar el riesgo de victimización.'
    ],
    'alteraciones_graves_comportamiento' => [
	    'label' => '1.2. Alteraciones graves del comportamiento',
	    'info'=> 'Las alteraciones del comportamiento en el niño, niña o adolescente pueden asociarse con mayor riesgo de recurrencia de victimización (Hèlie, 2014) al desafiar los recursos con los cuales cuentan las personas cuidadoras. De este modo, se puede recurrir a métodos de crianza o de control de la conducta que podrían resultar en nuevos episodios de victimización.<br>Las alteraciones graves del comportamiento coinciden con lo que el DSM-V TR denomina como trastornos de conducta, definidos como un “Un patrón de comportamiento repetitivo y persistente en el que se violan los derechos básicos de los demás o las principales normas o reglas sociales apropiadas para la edad,” (APA, 2022, p. 530). Por cierto, el criterio de nivel de desarrollo resulta central para poder identificarlo, pues es muy distinto lo que se espera de un preescolar, escolar o adolescente, en términos de conducta.<br>Entre las conductas observables que pueden considerarse en esta categoría se señala la agresión a personas y animales (ej: causar daño físico a otros, crueldad física a animales), destrucción deliberada de la propiedad, entrar a la fuerza en una propiedad, robo, o incumplimiento grave de la norma (ej: fugas del hogar durante la noche) (APA, 2022).<br>Las alteraciones de comportamiento del niño, niña o adolescente pueden constituir  expresión sintomática del trauma previo, lo cual no necesariamente se condice con la capacidad de las personas cuidadoras de comprender el significado de dichas alteraciones. ',
        'descripcion' => 'Conductas desafiantes o sintomatología que puede incrementar el riesgo de recurrencia.'
    ],
    'desvinculacion_ausentismo_escolar' => [
	    'label' => '1.3. Desvinculación y ausentismo escolar',
    'info'=>'La desvinculación (también llamada abandono escolar o desescolarización) y el  ausentismo escolar han sido identificados en la literatura como factor de riesgo para la recurrencia en la victimización en ciertas poblaciones (Meinck et al., 2017). Esto puede deberse a que el entorno escolar provee un contexto de cuidado, así como redes de apoyo capaces de potenciar la autoestima y autoeficacia, disminuyendo su vulnerabilidad (Meinck et al., 2017).<br>La desvinculación escolar constituye un hito de ruptura en la trayectoria educativa, que comienza con un progresivo distanciamiento, para finalizar con el abandono de los estudios (Espinoza- Díaz et al., 2014). Esta se explica por una “falta de ajuste entre el estudiante y el sistema escolar, que no está capacitado, en su estructura y dinámicas interaccionales, para hacerse cargo de las diferencias de los estudiantes” (ONU Mujeres, 2021, p. 21). <br>Por su parte, el ausentismo escolar es definido como “la falta de asistencia, ya sea justificada o injustificada, de las y los estudiantes al periodo de la jornada lectiva en los centros educativos” (MINEDUC, 2020, p. 2). La inasistencia puede ser reiterada (asistencia entre 90% y el 85%) o  grave (asistencia promedio menor al 85%) (MINEDUC, 2023).',
        'descripcion' => 'El abandono o las faltas reiteradas a la escuela aumentan la vulnerabilidad del NNA.'
    ],
    'denuncias_ingresos_maltrato_previo' => [
	    'label' => '1.4. Denuncias o ingresos por maltrato previo',
	    'info'=>'La presencia de episodios anteriores de vulneración es una de las variables de riesgo más respaldadas por la literatura. De esta manera, es necesario indagar si existen denuncias o reportes previos de alguna forma de maltrato (de Ruiter et al., 2020, Hauck et al., 2022, Holbrook & Hudziak, 2020, Kim et al., 2022, Putnam-Hornstein et al., 2015), incluyendo maltrato físico (Cheung et al., 2020, Choi & Kim, 2022, Kim et al., 2022, Pierce et al., 2017, Putnam-Hornstein et al., 2015; Vial et al., 2021), psicológico (Cheung et al., 2020; Vial et al., 2021), negligencia (Cheung et al., 2020, Eastman et al., 2016; Vial et al., 2021) o cualquier forma de violencia sexual (Cheng & Lo, 2015, Contreras et al., 2022, Meinck et al., 2017; Vial et al., 2021) en contra del niño, niña o adolescente.<br>También es necesario evaluar el número de denuncias o ingresos previos por maltrato al sistema de protección, puesto que estudios anteriores han evidenciado un mayor riesgo asociado a un mayor número de reportes, considerando además su gravedad (Kim et al., 2020). ',
        'descripcion' => 'Historial de reportes previos de maltrato que incrementan el riesgo de revictimización.'
    ],
    'terapia_nna' => [
	    'label' => '1.5. Terapia para el niño, niña o adolescente',
	    'info'=>'De acuerdo a la investigación de Solomon et al. (2016), la psicoterapia para el niño, niña o adolescente se relaciona con una menor probabilidad de sufrir una nueva victimización, convirtiéndose así en un factor protector. Esto puede obedecer a que la terapia permite reducir los problemas emocionales y conductuales de niños, niñas y adolescentes, ayudando así a mitigar el estrés parental (Solomon et al., 2016).<br>La decisión de asistir a terapia no descansa exclusivamente en el niño, niña o adolescente, sino de manera muy importante en sus figuras cuidadoras y sus posibilidades. Cuando este espacio de psicoterapia se produce, en cualquiera de sus formatos, esto constituye un factor protector que tiende a fortalecer los recursos del niño, niña o adolescente, protegiéndolo de nuevas vulneraciones.',
        'descripcion' => 'Un proceso psicoterapéutico puede actuar como factor protector para el NNA.'
    ]
];

// Procesar el formulario al enviarlo
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recorrer los campos y validar que no vengan vacíos
    foreach ($campos as $campo => $valor) {
        $campos[$campo] = $_POST[$campo] ?? '';
        if (empty($campos[$campo])) {
            $errors[$campo] = "Por favor, seleccione una opción.";
        }
    }

    // Si no hay errores, insertar o actualizar los datos en la base de datos
    if (empty($errors)) {
        // Verificar si ya existe un registro
        $query_check = "SELECT id FROM factores_individuales WHERE evaluacion_id = ?";
        $stmt_check = sqlsrv_query($conn, $query_check, [$evaluacion_id]);
        $existing_data = $stmt_check !== false ? sqlsrv_fetch_array($stmt_check, SQLSRV_FETCH_ASSOC) : [];
        if ($stmt_check !== false) {
            sqlsrv_free_stmt($stmt_check);
        }

        if ($existing_data) {
            // Actualizar registro existente
            $query = "UPDATE factores_individuales SET
                enfermedades_cronicas_discapacidad = ?,
                alteraciones_graves_comportamiento = ?,
                desvinculacion_ausentismo_escolar = ?,
                denuncias_ingresos_maltrato_previo = ?,
                terapia_nna = ?
                WHERE evaluacion_id = ?";
            $params = [
                $campos['enfermedades_cronicas_discapacidad'],
                $campos['alteraciones_graves_comportamiento'],
                $campos['desvinculacion_ausentismo_escolar'],
                $campos['denuncias_ingresos_maltrato_previo'],
                $campos['terapia_nna'],
                $evaluacion_id
            ];
            $stmt = sqlsrv_query($conn, $query, $params);
        } else {
            // Insertar un nuevo registro
            $query = "INSERT INTO factores_individuales (
                evaluacion_id,
                enfermedades_cronicas_discapacidad,
                alteraciones_graves_comportamiento,
                desvinculacion_ausentismo_escolar,
                denuncias_ingresos_maltrato_previo,
                terapia_nna
            ) VALUES (?, ?, ?, ?, ?, ?)";
            $params = [
                $evaluacion_id,
                $campos['enfermedades_cronicas_discapacidad'],
                $campos['alteraciones_graves_comportamiento'],
                $campos['desvinculacion_ausentismo_escolar'],
                $campos['denuncias_ingresos_maltrato_previo'],
                $campos['terapia_nna']
            ];
            $stmt = sqlsrv_query($conn, $query, $params);
        }

        if ($stmt !== false) {
            sqlsrv_free_stmt($stmt);
            // Redirigir a la siguiente sección
            header('Location: seccion3b.php' . $evaluacionIdQuery);
            ob_end_clean();
            exit();
        } else {
            $errors['general'] = "Error al guardar los datos: " . print_r(sqlsrv_errors(), true);
        }
    }
}

sqlsrv_close($conn);
?>

<div class="container mt-5">
    <h2>Sección 2: Factores Individuales del Niño, Niña o Adolescente</h2>
    <p>Por favor, selecciona la opción que mejor describa cada factor.</p>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($errors['general']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="seccion2b.php<?= htmlspecialchars($evaluacionIdQuery); ?>">
      

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
            <?php $opciones = $escalas[$campo]; ?>
            <?php foreach ($opciones as $key => $description): ?>
                <div class="custom-control custom-radio mb-2">
                    <input 
                        type="radio" 
                        id="<?php echo $campo . '_' . $key; ?>" 
                        name="<?php echo $campo; ?>" 
                        value="<?php echo $key; ?>" 
                        class="custom-control-input"
                        <?= $campos[$campo] == $key ? 'checked' : '' ?>
                    >
                    <label class="custom-control-label" for="<?php echo $campo . '_' . $key; ?>">
                        <?= htmlspecialchars($description) ?>
                    </label>
                </div>
            <?php endforeach; ?>
            
            <?php if (isset($errors[$campo])): ?>
                <small class="text-danger"><?= htmlspecialchars($errors[$campo]) ?></small>
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
                        <div class="mt-3"><?= $data['info'] ?></div>
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
            <a href="seccion1.php<?= htmlspecialchars($evaluacionIdQuery); ?>" class="btn btn-secondary">Anterior</a>
            <button type="submit" class="btn btn-primary">Siguiente</button>
        </div>
    </form>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function () {
        // Inicializar tooltips si lo requieres
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>

