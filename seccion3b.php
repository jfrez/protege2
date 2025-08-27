<?php
session_start();
// Buffer output so headers can be sent after DB operations
ob_start();
include_once("config.php");
include_once("header.php");

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
        header("Location: seccion1.php");
        ob_end_clean();
        exit();
    }
}
$evaluacion_id = $_SESSION['inserted_id'];

// Inicializar variables para almacenar mensajes de error
$errors = [];

// Inicializar variables para los campos del formulario
$campos = [
    'problemas_salud_mental_cuidadores' => '',
    'consumo_problematico_cuidadores' => '',
    'violencia_pareja' => '',
    'historia_maltrato_cuidadores' => '',
    'antecedentes_penales_cuidadores' => '',
    'dificultades_soporte_social' => '',
    'estres_supervivencia' => '',
    'deficiencia_habilidades_cuidado' => '',
    'actitudes_negativas_nna' => '',
    'atencion_prenatal_retrasada_ausente' => '',
    'inestabilidad_cuidados' => '',
    'ideacion_suicida_cuidadores' => '',
    'actitudes_negativas_intervencion' => '',
    'compromiso_colaborativo' => '',
    'extrema_minimizacion_negacion_maltrato' => '',
    'terapia_cuidadores' => '',
    'reunificaciones_fallidas' => ''
];

// Procesar el formulario al enviarlo
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los valores enviados desde el formulario
    foreach ($campos as $campo => $valor) {
        $campos[$campo] = $_POST[$campo] ?? '';
        if (empty($campos[$campo])) {
            $errors[$campo] = "Por favor, seleccione una opción.";
        }
    }

    // Si no hay errores, insertar o actualizar los datos en la base de datos
    if (empty($errors)) {
        // Verificar si ya existe un registro en factores_familiares para esta evaluación
        $query_check = "SELECT id FROM factores_familiares WHERE evaluacion_id = ?";
        $stmt_check = sqlsrv_query($conn, $query_check, [$evaluacion_id]);
        $existing_data = $stmt_check !== false ? sqlsrv_fetch_array($stmt_check, SQLSRV_FETCH_ASSOC) : [];
        if ($stmt_check !== false) {
            sqlsrv_free_stmt($stmt_check);
        }

        if ($existing_data) {
            // Actualizar el registro existente
            $query = "UPDATE factores_familiares SET
                problemas_salud_mental_cuidadores = ?,
                consumo_problematico_cuidadores = ?,
                violencia_pareja = ?,
                historia_maltrato_cuidadores = ?,
                antecedentes_penales_cuidadores = ?,
                dificultades_soporte_social = ?,
                estres_supervivencia = ?,
                deficiencia_habilidades_cuidado = ?,
                actitudes_negativas_nna = ?,
                atencion_prenatal_retrasada_ausente = ?,
                inestabilidad_cuidados = ?,
                ideacion_suicida_cuidadores = ?,
                actitudes_negativas_intervencion = ?,
                compromiso_colaborativo = ?,
                extrema_minimizacion_negacion_maltrato = ?,
                terapia_cuidadores = ?,
                reunificaciones_fallidas = ?
                WHERE evaluacion_id = ?";
            $params = [
                $campos['problemas_salud_mental_cuidadores'],
                $campos['consumo_problematico_cuidadores'],
                $campos['violencia_pareja'],
                $campos['historia_maltrato_cuidadores'],
                $campos['antecedentes_penales_cuidadores'],
                $campos['dificultades_soporte_social'],
                $campos['estres_supervivencia'],
                $campos['deficiencia_habilidades_cuidado'],
                $campos['actitudes_negativas_nna'],
                $campos['atencion_prenatal_retrasada_ausente'],
                $campos['inestabilidad_cuidados'],
                $campos['ideacion_suicida_cuidadores'],
                $campos['actitudes_negativas_intervencion'],
                $campos['compromiso_colaborativo'],
                $campos['extrema_minimizacion_negacion_maltrato'],
                $campos['terapia_cuidadores'],
                $campos['reunificaciones_fallidas'],
                $evaluacion_id
            ];
            $stmt = sqlsrv_query($conn, $query, $params);
        } else {
            // Insertar un nuevo registro
            $query = "INSERT INTO factores_familiares (
                evaluacion_id,
                problemas_salud_mental_cuidadores,
                consumo_problematico_cuidadores,
                violencia_pareja,
                historia_maltrato_cuidadores,
                antecedentes_penales_cuidadores,
                dificultades_soporte_social,
                estres_supervivencia,
                deficiencia_habilidades_cuidado,
                actitudes_negativas_nna,
                atencion_prenatal_retrasada_ausente,
                inestabilidad_cuidados,
                ideacion_suicida_cuidadores,
                actitudes_negativas_intervencion,
                compromiso_colaborativo,
                extrema_minimizacion_negacion_maltrato,
                terapia_cuidadores,
                reunificaciones_fallidas
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [
                $evaluacion_id,
                $campos['problemas_salud_mental_cuidadores'],
                $campos['consumo_problematico_cuidadores'],
                $campos['violencia_pareja'],
                $campos['historia_maltrato_cuidadores'],
                $campos['antecedentes_penales_cuidadores'],
                $campos['dificultades_soporte_social'],
                $campos['estres_supervivencia'],
                $campos['deficiencia_habilidades_cuidado'],
                $campos['actitudes_negativas_nna'],
                $campos['atencion_prenatal_retrasada_ausente'],
                $campos['inestabilidad_cuidados'],
                $campos['ideacion_suicida_cuidadores'],
                $campos['actitudes_negativas_intervencion'],
                $campos['compromiso_colaborativo'],
                $campos['extrema_minimizacion_negacion_maltrato'],
                $campos['terapia_cuidadores'],
                $campos['reunificaciones_fallidas']
            ];
            $stmt = sqlsrv_query($conn, $query, $params);
        }

        if ($stmt !== false) {
            sqlsrv_free_stmt($stmt);
            // Redirigir a la siguiente sección
            header("Location: seccion4b.php");
            ob_end_clean();
            exit();
        } else {
            $errors['general'] = "Error al guardar los datos: " . print_r(sqlsrv_errors(), true);
        }
    }
} else {
    // Si no se ha enviado el formulario, verificar si ya existe un registro
    $query = "SELECT * FROM factores_familiares WHERE evaluacion_id = ?";
    $stmt = sqlsrv_query($conn, $query, [$evaluacion_id]);
    $existing_data = $stmt !== false ? sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) : [];
    if ($stmt !== false) {
        sqlsrv_free_stmt($stmt);
    }

    if ($existing_data) {
        // Si hay datos existentes, llenar las variables con esos valores
        foreach ($campos as $campo => $valor) {
            $campos[$campo] = $existing_data[$campo];
        }
    }
}

sqlsrv_close($conn);

/**
 * Escalas de valoración, con cada alternativa (a, b, c, d)
 * incluyendo un breve resumen de lo que significa, 
 * según el documento que proporcionaste.
 */

$escalas = [
    // 2.1. Problemas de salud mental
    'problemas_salud_mental_cuidadores' => [
        'a' => 'No es posible determinar — Falta información, por lo cual no es posible determinar la existencia de riesgo asociado a problemas de salud mental en personas cuidadoras.',
        'b' => 'Riesgo nulo o bajo — Ninguna de las personas cuidadoras presenta problemas significativos de salud mental, lo cual les permite desarrollar sus actividades cotidianas con normalidad.',
        'c' => 'Riesgo medio — En la actualidad al menos una de las personas cuidadoras presenta algún problema de salud mental leve o moderado, que ha interferido el desarrollo de sus actividades cotidianas.',
        'd' => 'Riesgo alto — En la actualidad al menos una de las personas cuidadoras presenta un problema mayor de salud mental, agudo o crónico, que interfiere de manera significativa el desarrollo de sus actividades cotidianas.'
    ],

    // 2.2. Consumo problemático de alcohol y sustancias
    'consumo_problematico_cuidadores' => [
        'a' => 'No es posible determinar — Falta información, por lo cual no es posible determinar la existencia de riesgo asociado a consumo de alcohol y/o drogas por parte de padres o personas cuidadoras.',
        'b' => 'Riesgo nulo o bajo — Las personas cuidadoras no presentan consumo de alcohol o drogas. Si lo presentan, es de carácter ocasional y no se considera como uso problemático.',
        'c' => 'Riesgo medio — A lo menos una de las personas cuidadoras presenta consumo recurrente, que amenaza con constituirse en un consumo problemático, pero aún no se ha configurado como tal.',
        'd' => 'Riesgo alto — A lo menos una de las personas cuidadoras presenta consumo problemático de alcohol y /o drogas.'
    ],

    // 2.3. Violencia en la pareja
    'violencia_pareja' => [
        'a' => 'No es posible determinar — Falta información, por lo cual no es posible determinar la existencia de riesgo asociado a algún tipo de violencia doméstica.',
        'b' => 'Riesgo nulo o bajo — No se registran antecedentes de violencia en la pareja en personas cuidadoras, si bien pueden existir situaciones de conflicto no constituyen hechos de violencia.',
        'c' => 'Riesgo medio — Existen antecedentes de violencia en la pareja, pero esta violencia no está activa al momento del estudio del caso.',
        'd' => 'Riesgo alto — Existen hechos de violencia de pareja que se producen actualmente en el contexto de cuidado del niño, niña o adolescente.'
    ],

    // 2.4. Historia de maltrato de personas cuidadoras
    'historia_maltrato_cuidadores' => [
        'a' => 'No es posible determinar — Falta información, por lo cual no es posible determinar la existencia de riesgo asociado a historia de violencia de padres o personas cuidadoras.',
        'b' => 'Riesgo nulo o bajo — Ninguna de las personas cuidadoras presenta antecedentes de maltrato o negligencia durante su infancia. En caso de que alguno refiera experiencias de maltrato o negligencia durante su infancia, contaron con el apoyo terapéutico necesario.',
        'c' => 'Riesgo medio — Alguna o ambas personas cuidadoras refieren episodios de maltrato infantil o negligencia de carácter leve o moderado.',
        'd' => 'Riesgo alto — Alguna o ambas personas cuidadoras refieren episodios de maltrato infantil o negligencia de carácter grave y/o sistemáticos en el tiempo.'
    ],

    // 2.5. Antecedentes penales de personas cuidadoras
    'antecedentes_penales_cuidadores' => [
        'a' => 'No es posible determinar — Falta información, por lo cual no es posible determinar la existencia de riesgo asociado a antecedentes penales de padres o personas cuidadoras.',
        'b' => 'Riesgo nulo o bajo — Ninguno de los padres o personas cuidadoras del niño, niña o adolescente cuenta con antecedentes penales.',
        'c' => 'Riesgo medio — Uno o más de los padres o personas cuidadoras del niño, niña o adolescente cuenta con antecedentes penales, pero son delitos menores sin uso de la violencia.',
        'd' => 'Riesgo alto — Uno o más padres o personas cuidadoras del niño, niña o adolescente cuentan con antecedentes penales por hechos graves, o bien son delitos menos graves con utilización de la violencia.'
    ],

    // 2.6. Dificultades de soporte social
    'dificultades_soporte_social' => [
        'a' => 'No es posible determinar — Falta información, por lo cual no es posible determinar la existencia de riesgo asociado a dificultad de soporte social.',
        'b' => 'Riesgo nulo o bajo — El entorno familiar cuenta con buenas redes de apoyo en el contexto social y/o comunitario, así como un adecuado acceso a servicios.',
        'c' => 'Riesgo medio — El entorno familiar cuenta con algunas redes de apoyo en el contexto social y/o comunitario, así como un acotado acceso a servicios.',
        'd' => 'Riesgo alto — El entorno familiar se encuentra en condición de aislamiento social y/o un acceso muy limitado a servicios.'
    ],

    // 2.7. Estrés de supervivencia
    'estres_supervivencia' => [
        'a' => 'No es posible determinar — Falta información, por lo cual no es posible determinar la existencia de riesgo asociado a estrés de supervivencia.',
        'b' => 'Riesgo nulo o bajo — La situación financiera y de empleo de la familia o personas cuidadoras es suficiente para cubrir las necesidades básicas de sus miembros. No se aprecian problemas mayores de ingresos, empleo o vivienda.',
        'c' => 'Riesgo medio — Durante el último año la familia ha enfrentado algunos problemas financieros, de empleo, vivienda inadecuada.',
        'd' => 'Riesgo alto — Durante el último año la familia se ha visto desafiada en su supervivencia enfrentando serios problemas financieros, de empleo o vivienda.'
    ],

    // 2.8. Deficiencia en habilidades de cuidado de personas cuidadoras
    'deficiencia_habilidades_cuidado' => [
        'a' => 'No es posible determinar — Falta información, por lo cual no es posible determinar la existencia de riesgo asociado a deficiencia en habilidades de cuidado de personas cuidadoras.',
        'b' => 'Riesgo nulo o bajo — Las habilidades de cuidado de padres o personas cuidadoras resultan apropiadas para responder a las necesidades del niño, niña o adolescente, de acuerdo a su nivel de desarrollo.',
        'c' => 'Riesgo medio — Se identifican ciertas carencias en las habilidades de cuidado en las personas cuidadoras.',
        'd' => 'Riesgo alto — Se identifican serios déficit en las habilidades de cuidado en los adultos a cargo del niño, niña o adolescente.'
    ],

    // 2.9. Actitudes negativas hacia el niño, niña o adolescente
    'actitudes_negativas_nna' => [
        'a' => 'No es posible determinar — Falta información, por lo cual no es posible determinar la existencia de riesgo asociado a actitudes negativas hacia el niño, niña o adolescente.',
        'b' => 'Riesgo nulo o bajo — No se observan actitudes negativas hacia el niño, niña o adolescente, por parte de la(s) persona(s) cuidadora(s).',
        'c' => 'Riesgo medio — De manera ocasional se identifican actitudes negativas hacia el niño, niña o adolescente por parte de la(s) persona(s) cuidadora(s).',
        'd' => 'Riesgo alto — Frecuentemente se identifican actitudes negativas o abiertamente hostiles hacia el niño, niña o adolescente por parte de la(s) persona(s) cuidadora(s).'
    ],

    // 2.10. Atención prenatal retrasada o ausente
    'atencion_prenatal_retrasada_ausente' => [
        'a' => 'No es posible determinar — Falta información, por lo cual no es posible determinar la existencia de riesgo asociado a atención prenatal retrasada o ausente.',
        'b' => 'Riesgo nulo o bajo — La madre recibió atención prenatal desde el primer trimestre del embarazo y durante toda su gestación.',
        'c' => 'Riesgo medio — La madre recibió atención prenatal a partir del segundo trimestre del embarazo o de forma intermitente durante su gestación.',
        'd' => 'Riesgo alto — La madre sólo recibió atención prenatal durante el tercer y último trimestre del embarazo o no recibió ningún seguimiento médico.'
    ],

    // 2.11. Inestabilidad en los cuidados
    'inestabilidad_cuidados' => [
        'a' => 'No es posible determinar — Falta información, por lo cual no es posible determinar el riesgo asociado a inestabilidad en los cuidados (cambios de persona cuidadora).',
        'b' => 'Riesgo nulo o bajo — El niño, niña o adolescente tiene una dinámica de cuidados estable, con personas cuidadoras definidas que se han mantenido en el tiempo.',
        'c' => 'Riesgo medio — El niño, niña o adolescente presenta algunas irregularidades en la estabilidad de sus personas cuidadoras y en ocasiones alterna entre diferentes figuras.',
        'd' => 'Riesgo alto — El niño, niña o adolescente presenta multiplicidad de personas cuidadoras, siendo complejo definir a las personas a cargo del cuidado, con alta inestabilidad en la dinámica familiar.'
    ],

    // 2.12. Ideación suicida de personas cuidadoras
    'ideacion_suicida_cuidadores' => [
        'a' => 'No es posible determinar — Falta información, por lo cual no es posible determinar la existencia de riesgo asociado a ideación suicida de personas cuidadoras.',
        'b' => 'Riesgo nulo o bajo — Las personas cuidadoras no presentan antecedentes de ideación suicida en su biografía.',
        'c' => 'Riesgo medio — Al menos una de las personas cuidadoras ha recibido atención psicológica/psiquiátrica por ideación suicida, o se cuenta con reportes previos que indica presencia de esta condición.',
        'd' => 'Riesgo alto — Al menos una de las personas cuidadoras ha requerido internación en salud mental por ideación o intento de suicidio.'
    ],

    // 2.13. Actitudes negativas hacia la intervención
    'actitudes_negativas_intervencion' => [
        'a' => 'No es posible determinar — Falta información, por lo cual no es posible determinar el riesgo asociado a actitudes negativas hacia la intervención.',
        'b' => 'Riesgo nulo o bajo — No existen actitudes negativas respecto a la intervención o los profesionales por parte de los padres o personas cuidadoras.',
        'c' => 'Riesgo medio — Los padres o personas cuidadoras presentan ocasionalmente actitudes negativas respecto a la intervención o los profesionales, pero es posible trabajar con las personas a cargo del cuidado.',
        'd' => 'Riesgo alto — Los padres o personas cuidadoras presentan actitudes negativas respecto a la intervención o los profesionales, lo que impide trabajar con las personas a cargo del cuidado.'
    ],

    // 2.14. Compromiso colaborativo (Escala de protección)
    'compromiso_colaborativo' => [
        'a' => 'No es posible determinar — Falta información, por lo cual no es posible determinar la existencia de protección asociada al compromiso colaborativo hacia la intervención de parte de las personas cuidadoras.',
        'b' => 'Protección nula o baja — Personas cuidadoras y profesionales no establecen una relación de compromiso colaborativo.',
        'c' => 'Protección media — La relación de compromiso colaborativo entre personas cuidadoras y profesionales está posiblemente presente o lo está de manera limitada.',
        'd' => 'Protección alta — Personas cuidadoras y profesionales establecen una relación de compromiso colaborativo que permite avanzar en la intervención.'
    ],

    // 2.15. Extrema minimización o negación del maltrato
    'extrema_minimizacion_negacion_maltrato' => [
        'a' => 'No es posible determinar — Falta información, por lo cual no es posible determinar el riesgo asociado a la extrema minimización o negación del maltrato.',
        'b' => 'Riesgo nulo o bajo — Las personas cuidadoras reconocen la presencia de abuso y maltrato en el niño, niña o adolescente.',
        'c' => 'Riesgo medio — Las personas cuidadoras presentan alguna de las manifestaciones de minimización del maltrato, que son incoherentes con los antecedentes del caso.',
        'd' => 'Riesgo alto — Las personas cuidadoras presentan alguna de las manifestaciones de negación del maltrato, que son incoherentes con los antecedentes del caso.'
    ],

    // 2.16. Terapia para padres o personas cuidadoras (Escala de protección)
    'terapia_cuidadores' => [
        'a' => 'No es posible determinar — Falta información, por lo cual no es posible determinar la existencia de protección asociada a la terapia de las personas cuidadoras.',
        'b' => 'Protección nula o baja — Las personas cuidadoras nunca han asistido a terapia o se han negado a participar.',
        'c' => 'Protección media — Las personas cuidadoras han asistido a terapia, pero han desertado del proceso.',
        'd' => 'Protección alta — Las personas cuidadoras han asistido a terapia, y han finalizado el proceso.'
    ],

    // 2.17. Reunificaciones fallidas
    'reunificaciones_fallidas' => [
        'a' => 'No es posible determinar — Falta información, por lo cual no es posible determinar la existencia de riesgo asociado a reunificaciones fallidas.',
        'b' => 'Riesgo nulo o bajo — La reunificación ha sido exitosa. El niño, niña o adolescente se ha mantenido con su familia nuclear por al menos 2 años.',
        'c' => 'Riesgo medio — La reunificación se ha producido, pero se observan dificultades en la inserción del niño, niña o adolescente en ese núcleo familiar.',
        'd' => 'Riesgo alto — Ha existido al menos una reunificación fallida, generando la salida del hogar del niño, niña o adolescente luego de un intento de devolverle a su familia.'
    ]
];






// Factores con sus etiquetas y descripciones
$factores = [
    'problemas_salud_mental_cuidadores' => [
	    'label' => '2.1. Problemas de salud mental de personas cuidadoras',
	    'info'=>'Los problemas de salud mental han sido estudiados en los padres y en las personas cuidadoras, lo cual se asocia a un mayor riesgo de nuevas victimizaciones hacia el niño, niña o adolescente (Casanueva et al., 2015; Holbrook & Hudziak, 2020), especialmente cuando son problemas mayores de salud mental (de Ruiter et al., 2020; Vial et al., 2021), presentando un ritmo más rápido de recurrencia (Casanueva et al., 2015).
Entre los problemas de salud mental estudiados en padres o personas cuidadoras se incluyen trastornos emocionales (Holbrook & Hudziak, 2020), sintomatología depresiva (Jedwab et al., 2017), y trastornos de personalidad vinculados a la ira, impulsividad y/o inestabilidad emocional (de Ruiter et al., 2020). De particular relevancia resultan los problemas de salud mental que las personas cuidadoras presentan en la actualidad (2015).',
        'descripcion' => 'Problemas de salud mental que pueden aumentar el riesgo de una nueva victimización.'
    ],
    'consumo_problematico_cuidadores' => [
	    'label' => '2.2. Consumo problemático de alcohol y sustancias de personas cuidadoras',
	    'info'=>'Las investigaciones señalan que la dependencia al alcohol (Cheng y Lo, 2015) y el abuso del alcohol (Choi y Kim, 2022), así como el abuso de sustancias (de Ruiter et al., 2020, Holbrook y Hudziak, 2020) por parte de personas cuidadoras, están relacionados con un mayor riesgo de nuevas victimizaciones hacia los niños, niñas y adolescentes a su cargo.
El consumo de alcohol y drogas puede exhibir patrones distintos de una persona a otra y no todos ellos ponen en riesgo a los niños, niñas y adolescentes.
La CIE-11 (OMS, 2019) se refiere al uso peligroso de alcohol o al uso de drogas peligrosas, lo cual incrementa considerablemente el riesgo, ya sea producto de la frecuencia del consumo, de la cantidad que se consume en cada ocasión, de comportamientos de riesgo derivados del consumo o del contexto en que se consume, así como a una combinación de estos factores.
Por su parte, el DSM-V TR (APA, 2022) se refiere a los trastornos relacionados con el uso de sustancias como un patrón patológico de conductas asociadas al consumo, identificando los siguientes criterios para su diagnóstico: control deficitario sobre el consumo; deterioro social que lleva al incumplimiento de deberes en el hogar, el trabajo o la escuela; consumo de riesgo de la sustancia; así como criterios farmacológicos que incluyen la tolerancia y el síndrome de abstinencia.',
        'descripcion' => 'Abuso de alcohol u otras sustancias por parte de las personas cuidadoras.'
    ],
    'violencia_pareja' => [
	    'label' => '2.3. Violencia en la pareja',
	    'info'=>'Se ha identificado mayor riesgo de un nuevo episodio de maltrato hacia el niño, niña o adolescente cuando existe violencia en la pareja o ex pareja (Casanueva et al., 2015; Duffy et al., 2015; de Ruiter et al., 2020; Pierce et al., 2017; Vial et al., 2021, van der Put et al., 2016). De este modo, de Ruiter et al. (2020) señalan que en aquellos hogares en que se produce violencia en la pareja parental, los niños, niñas y adolescentes también suelen ser objeto de estas dinámicas de violencia.
La violencia en la pareja, “se refiere a cualquier comportamiento, dentro de una relación íntima, que cause o pueda causar daño físico, psíquico o sexual a los miembros de la relación” (OMS, 2012, p. 1). Esta incluye “maltrato físico, sexual o emocional y comportamientos controladores por un compañero íntimo” (p. 1).
Si bien suele darse desde un hombre hacia una mujer, constituyendo violencia de género en la pareja, debe considerarse en cualquiera de los miembros de una relación, pues la investigación indica que puede constituir un factor de riesgo, sin importar el género de la víctima o el perpetrador.',
        'descripcion' => 'Violencia en la relación de pareja de las personas cuidadoras.'
    ],
    'historia_maltrato_cuidadores' => [
	    'label' => '2.4. Historia de maltrato de personas cuidadoras',
	    'info'=>'Atendiendo a la transgeneracionalidad que pueden presentar los patrones de violencia en el contexto familiar, uno de los factores que ha demostrado relación con la recurrencia es el hecho de que el padre, madre o persona cuidadora haya sido víctima de maltrato o negligencia durante su infancia (de Ruiter et al., 2020; Horikawa et al., 2016; Vial et al., 2021; van der Put et al., 2016).',
        'descripcion' => 'Antecedentes de maltrato o negligencia infantil en la historia de los cuidadores.'
    ],
    'antecedentes_penales_cuidadores' => [
	    'label' => '2.5. Antecedentes penales de personas cuidadoras',
	    'info'=>'Los antecedentes penales de padres o personas cuidadoras pueden incrementar la probabilidad de que un niño, niña o adolescente enfrente una nueva situación de victimización (Pierce et al., 2017). Entre los antecedentes más frecuentes se identifica el robo en residencias, robo con violencia o intimidación, tenencia de drogas para la venta y encarcelamiento (Pierce et al., 2017), aunque también pueden presentarse otros hechos.
Esto se ha vinculado con la estrecha relación que se establece entre la criminalidad y el ejercicio de la violencia en el espacio familiar (Duffy et al., 2015). En este sentido, “un historial criminal violento subraya la capacidad y propensión a la violencia de la     persona cuidadora y puede reflejar una falta de control de los impulsos” (Pierce et al., 2017, p. 275).
La relación entre los antecedentes penales de la madre y el resultado de victimización recurrente resulta particularmente significativa, dado que las madres suelen ser las cuidadoras principales de niños, niñas y adolescentes (Duffy et al., 2015)',
        'descripcion' => 'Existencia de antecedentes penales en los cuidadores.'
    ],
    'dificultades_soporte_social' => [
	    'label' => '2.6. Dificultades de soporte social',
	    'info'=>'El soporte social es fundamental para el cuidado de niños, niñas y adolescentes, relacionado con las redes familiares, comunitarias y los servicios provistos por el entorno (ej: salud, educación), los cuales pueden resultar de difícil acceso en contexto rural. 
El aislamiento social constituye una variable de riesgo de recurrencia victimal pues, al contar con menos redes y apoyo social, se torna más difícil para la familia aliviar el estrés mediante el apoyo emocional y social provisto por el entorno (Choi y Kim, 2022). Asimismo, la ausencia de miembros de la comunidad que pueda velar por el niño, niña o adolescente hace difícil hacerlo visible (Horikawa et al., 2016), al carecer de personas ajenas al entorno familiar que puedan mantenerse informados sobre su estado. Estas redes pueden incluir vecinos, amigos de la familia, familia extensa, comunidades religiosas, agrupaciones locales o barriales, entre otros. En particular, la falta de soporte social en el último año ha demostrado una relación significativa con la recurrencia (de Ruiter et al., 2020).',
        'descripcion' => 'Limitaciones en las redes de apoyo y acceso a servicios de la familia.'
    ],
    'estres_supervivencia' => [
	    'label' => '2.7. Estrés de supervivencia',
	    'info'=>'Es necesario estudiar con detención las condiciones de supervivencia de la familia y el estrés que esto puede suponer para las y los adultos a cargo, pues la literatura ha encontrado una significativa relación con la recurrencia.
Así, diferentes autores  han identificado un alto riesgo de nuevas victimizaciones cuando la familia enfrenta una situación de inestabilidad financiera o pobreza (Cheng & Lo, 2015; Horikawa et al., 2016; Holbrook & Hudziak 2020; Kim et al., 2020; Kim et al., 2022; Vial et al., 2021). Investigaciones recientes han identificado como una variable crucial los estresores socioeconómicos registrados en el último año (de Ruiter et al., 2020). 
Además, se destaca la importancia del empleo, pues “el estrés socioeconómico sin sistemas de apoyo adecuados puede estar asociado con el abuso y la negligencia infantil” (Choi & Kim 2022, p. 1525), particularmente cuando las personas cuidadoras carecen del tiempo que requieren para cuidar a sus hijos(as).
Para describir este fenómeno, se ha acuñado el término “estrés de supervivencia” (Kim et al., 2022), que abarca no sólo de las condiciones materiales de existencia de una determinada familia, sino de la forma en que esto se constituye en un estresor que desafía a las personas cuidadoras en su rol. El concepto de estrés de supervivencia no equivale al de pobreza, en tanto también familias con nivel socioeconómico más acomodado pueden experimentarlo al verse enfrentadas a una crisis económica o a una pérdida del empleo, por ejemplo. ',
        'descripcion' => 'Estrés derivado de condiciones socioeconómicas precarias, desempleo o vivienda inestable.'
    ],
    'deficiencia_habilidades_cuidado' => [
            'label' => '2.8. Deficiencia en habilidades de cuidado de personas cuidadoras',
	    'info'=>'A nivel del funcionamiento familiar, una de las variables que se ha relacionado con nuevas victimizaciones hacia niños, niñas y adolescentes son las habilidades de cuidado que demuestran padres y personas cuidadoras (Pierce et al., 2017).
En esta línea, se ha investigado la falta de conocimientos sobre la crianza de los(as) hijos(as) o falta de habilidades parentales (de Ruiter et al., 2020). Asimismo, las escasas habilidades de resolución de problemas de personas cuidadoras son un predictor importante de nuevas denuncias (Kim et al., 2022).',
        'descripcion' => 'Carencias importantes en las habilidades de crianza y cuidado de las personas cuidadoras.'
    ],
    'actitudes_negativas_nna' => [
	    'label' => '2.9. Actitudes negativas hacia el niño, niña o adolescente',
	    'info'=>'Las actitudes negativas o incluso hostiles hacia el niño, niña o adolescente por parte de los cuidadores incrementan la probabilidad de nuevos reportes (de Ruiter et al., 2020, Pierce et al., 2017). De este modo, se corre un mayor riesgo cuando una persona a cargo del cuidado interpreta las acciones o comportamientos del niño, niña o adolescente como intencionalmente negativos (por ejemplo: el niño molesta intencionalmente o llora para manipularme), lo cual habla de las dificultades de mentalización por parte de quien ejerce el cuidado',
        'descripcion' => 'Actitudes hostiles o negativas de los cuidadores hacia el NNA.'
    ],
    'atencion_prenatal_retrasada_ausente' => [
	    'label' => '2.10. Atención prenatal retrasada o ausente',
	    'info'=>'La atención prenatal consiste en la asistencia, orientación, atención y apoyo a la gestante por parte de los profesionales sanitarios. Su objetivo es promover la salud tanto de la madre como del feto, incluyendo una nutrición equilibrada, un estilo de vida sano, la prevención y detección de enfermedades, así como el apoyo ante situaciones de violencia (OMS, 2016). Se orientan a garantizar la intervención oportuna a fin de controlar riesgos y promover una gestación saludable (Aguilera y Soothill, 2014). Se compone de exámenes físicos, de laboratorio, de ultrasonido, la revisión de la historia clínica y reproductiva.
La evidencia identifica entre los niños, niñas o adolescentes en mayor riesgo de nuevos reportes de abuso o maltrato aquellos cuyas madres habían tenido una atención prenatal retrasada (tercer trimestre) o ausente (Eastman et al., 2016).',
        'descripcion' => 'Ausencia de controles prenatales oportunos o gestación sin supervisión médica adecuada.'
    ],
    'inestabilidad_cuidados' => [
	    'label' => '2.11. Inestabilidad en los cuidados',
            'info'=>'La presencia de múltiples personas encargadas del cuidado de un niño, niña o adolescente puede indicar una falta de estabilidad en su entorno familiar. La presencia de múltiples personas cuidadoras puede sugerir que la atención al niño, niña o adolescente cambia con regularidad, lo que podría exponerle a situaciones de abuso y abandono. Además, la diversidad de personas cuidadoras puede afectar la calidad de los vínculos de apego con los padres, aumentando así el riesgo de maltrato infantil (Duffy et al., 2015; Vial et al., 2021).',
        'descripcion' => 'Cambios frecuentes o múltiples personas a cargo del cuidado del NNA.'
    ],
    'ideacion_suicida_cuidadores' => [
	    'label' => '2.12. Ideación suicida de personas cuidadoras',
	    'info'=>'La ideación suicida constituye un preocupante factor de riesgo, que expone al niño, niña o adolescente a nuevas situaciones de victimización (de Ruiter et al., 2020), denotando alteraciones psicopatológicas en las personas cuidadoras. Esta implica pensar, considerar o planificar el suicidio y suele estar asociada a cuadros depresivos o trastornos del estado de ánimo (Meyer et al., 2010).      
En casos extremos, la ideación suicida puede dar lugar a lo que se ha denominado como suicidio extendido (Xu, et al, 2024). En este escenario se ha descrito el homicidio-suicidio por motivación “altruista”, en que el perpetrador termina con la vida de sus allegados antes de suicidarse (Flynn et al., 2016), en particular de los niños a su cargo, pues los considera demasiado débiles para sobrevivir sin su cuidado (Xu, et al, 2024).',
        'descripcion' => 'Pensamientos o intentos suicidas en las personas cuidadoras.'
    ],
    'actitudes_negativas_intervencion' => [
	    'label' => '2.13. Actitudes negativas hacia la intervención',
	    'info'=>'Las actitudes negativas hacia la intervención implican valoraciones, creencias y comportamientos por parte de padres o personas cuidadoras que pueden obstaculizar el proceso en los servicios de protección, constituyendo un factor de riesgo de recurrencia (de Ruiter et al., 2019). 
Estas actitudes negativas no responden a dificultades contextuales de la familia para realizar un proceso de intervención, como serían los obstáculos laborales que dificultan concurrir a las entrevistas. Sino más bien constituyen los componentes actitudinales acerca del proceso, manifestándose en verbalizaciones contrarias a la intervención sin fundamento justificado, manipulación de la información, resistencia al proceso, entre otros.',
        'descripcion' => 'Resistencia o rechazo de los cuidadores ante la intervención.'
    ],
    'compromiso_colaborativo' => [
	    'label' => '2.14. Compromiso colaborativo',
	    'info'=>'Es necesario evaluar las actitudes que las personas cuidadoras demuestran ante la intervención. Se ha observado que la actitud positiva hacia la intervención actúa como factor protector (García-Mollá et al., 2023), asociándose a una menor probabilidad de la recurrencia de victimización infantil (Cheng & Lo, 2015). Esto, en tanto la relación colaborativa entre personas cuidadoras y profesionales facilita el acceso a los servicios y el cumplimiento de objetivos propuestos (Cheng & Lo, 2015). El compromiso colaborativo no solamente implica la asistencia a las sesiones o a los encuentros acordados, sino también el establecimiento de una relación de ayuda que permite la problematización, responsabilización y el trabajo activo.',
        'descripcion' => 'Grado de participación activa y colaborativa de las personas cuidadoras con los profesionales.'
    ],
    'extrema_minimizacion_negacion_maltrato' => [
	    'label' => '2.15. Extrema minimización o negación del maltrato',
	    'info'=>'La extrema minimización o negación del abuso se considera un factor de riesgo para la      recurrencia en estas conductas (Contreras et al., 2022; de Ruiter et al., 2019; Langton et al., 2008).
En la negación, se puede negar tanto la interacción del perpetrador con la víctima, como la naturaleza abusiva de la conducta o bien que esta constituya una vulneración o delito, pudiendo afirmar el consentimiento y la participación de la víctima (Nunes & Jung, 2013).
En la minimización, por su parte, se minimizan las conductas abusivas, se atribuye la culpa a la víctima, se presentan factores externos e internos como justificaciones, se minimiza el alcance y el daño a la víctima, así como el riesgo de posibles recurrencias (Nunes & Jung, 2013).
Tanto en la negación como en la minimización puede generarse la culpabilización del niño, niña o adolescente por la vulneración vivida.',
        'descripcion' => 'Negación total o justificación de los hechos de maltrato, ocultando su gravedad.'
    ],
    'terapia_cuidadores' => [
	    'label' => '2.16. Terapia para padres o personas cuidadoras',
	    'info'=>'La implicación de los padres o personas cuidadoras en procesos psicoterapéuticos ha sido descrita como un factor protector que reduce el riesgo de nuevos episodios de abuso y maltrato hacia los niños, niñas y adolescentes (Solomon et al., 2016). Esto puede obedecer a la utilidad de la terapia para lidiar con problemas de salud mental, consumo de sustancias y estrés parental, factores vinculados a la recurrencia de la victimización, permitiendo un mejor ajuste emocional (Solomon et al., 2016).',
        'descripcion' => 'Asistencia de los cuidadores a procesos terapéuticos para abordar problemáticas personales y parentales.'
    ],
    'reunificaciones_fallidas' => [
	    'label' => '2.17. Reunificaciones fallidas (en caso de separación de personas cuidadoras)',
	    'info'=>'Hay situaciones proteccionales que desencadenan la salida del niño, niña o adolescente de su núcleo familiar. Cuando el sistema de protección considera que las condiciones están dadas se genera una revinculación con su familia de origen. Sin embargo, si no funciona se puede provocar una nueva ruptura y salida de su núcleo familiar, a esto se ha denominado como reunificaciones fallidas.
En los casos en que ha existido separación del niño, niña o adolescente del núcleo familiar e intentos de reunificación, las reunificaciones fallidas son un indicador de inestabilidad familiar que resulta un predictor significativo del riesgo de recurrencia (Hélie et al., 2014). ',
        'descripcion' => 'Intentos de reunificación familiar infructuosos que afectan la estabilidad del NNA.'
    ]
];

?>
<div class="container mt-5">
    <h2>Sección 3: Factores Familiares</h2>
    <p>Por favor, selecciona la opción que mejor describa cada factor (riesgo o protección).</p>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($errors['general']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="seccion3b.php">
        <?php foreach ($factores as $campo => $data): ?>
            <div class="card mb-4">
           <div class="card-header d-flex justify-content-between align-items-start">
            <div>
                <strong><?php echo $data['label']; ?></strong>
            </div>
            <button type="button" 
                    class="btn btn-info btn-sm" 
                    data-toggle="modal" 
		    data-target="#modal<?php echo $campo; ?>">
                Saber más
            </button>
        </div>
        

                <div class="card-body">
                    <?php
                    // Obtener las opciones específicas para este factor desde $escalas
                    $opciones = $escalas[$campo];
                    ?>
                    <?php foreach ($opciones as $key => $value): ?>
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
            <a href="seccion2b.php" class="btn btn-secondary">Anterior</a>
            <button type="submit" class="btn btn-primary">Siguiente</button>
        </div>
    </form>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function () {
        // Inicializar tooltips si los requieres
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>

