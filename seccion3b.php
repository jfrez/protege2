<?php
session_start();
include_once("config.php");
include_once("header.php");

// Verificar si hay una evaluación en curso
if (!isset($_SESSION['inserted_id'])) {
    echo "Error: No se ha iniciado una evaluación.";
    exit();
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
        $stmt_check = $conn->prepare($query_check);
        $stmt_check->bind_param("i", $evaluacion_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $existing_data = $result_check->fetch_assoc();
        $stmt_check->close();

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
            $stmt = $conn->prepare($query);
            if ($stmt === false) {
                die('Error en la preparación de la consulta: ' . $conn->error);
            }
            $stmt->bind_param(
                "sssssssssssssssssi",
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
            );
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
            $stmt = $conn->prepare($query);
            if ($stmt === false) {
                die('Error en la preparación de la consulta: ' . $conn->error);
            }
            $stmt->bind_param(
                "isssssssssssssssss",
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
            );
        }

        if ($stmt->execute()) {
            // Redirigir a la siguiente sección
            header("Location: seccion4b.php");
            exit();
        } else {
            $errors['general'] = "Error al guardar los datos: " . $stmt->error;
        }

        $stmt->close();
    }
} else {
    // Si no se ha enviado el formulario, verificar si ya existe un registro
    $query = "SELECT * FROM factores_familiares WHERE evaluacion_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $evaluacion_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_data = $result->fetch_assoc();
    $stmt->close();

    if ($existing_data) {
        // Si hay datos existentes, llenar las variables con esos valores
        foreach ($campos as $campo => $valor) {
            $campos[$campo] = $existing_data[$campo];
        }
    }
}

/**
 * Escalas de valoración, con cada alternativa (a, b, c, d)
 * incluyendo un breve resumen de lo que significa, 
 * según el documento que proporcionaste.
 */
$escalas = [
    // 2.1. Problemas de salud mental
    'problemas_salud_mental_cuidadores' => [
        'a' => 'No es posible determinar — Falta información para evaluar el riesgo asociado a salud mental de las personas cuidadoras.',
        'b' => 'Riesgo nulo o bajo — Ninguna persona cuidadora presenta problemas mayores de salud mental.',
        'c' => 'Riesgo medio — Al menos una persona cuidadora presenta problemas leves o moderados que interfieren parcialmente en su vida.',
        'd' => 'Riesgo alto — Al menos una persona cuidadora presenta un problema mayor de salud mental que interfiere significativamente.'
    ],
    // 2.2. Consumo problemático de alcohol y sustancias
    'consumo_problematico_cuidadores' => [
        'a' => 'No es posible determinar — Falta información acerca del consumo de alcohol o drogas en personas cuidadoras.',
        'b' => 'Riesgo nulo o bajo — No hay evidencia de consumo o es ocasional sin constituir uso problemático.',
        'c' => 'Riesgo medio — Consumo recurrente que amenaza con volverse problemático, pero aún no se configura como tal.',
        'd' => 'Riesgo alto — Al menos una persona cuidadora presenta consumo problemático de alcohol y/o drogas.'
    ],
    // 2.3. Violencia en la pareja
    'violencia_pareja' => [
        'a' => 'No es posible determinar — Falta información para evaluar la existencia de violencia doméstica.',
        'b' => 'Riesgo nulo o bajo — No se registran hechos de violencia; puede haber conflictos, pero no constituyen violencia.',
        'c' => 'Riesgo medio — Hubo violencia en la pareja, pero actualmente no está activa.',
        'd' => 'Riesgo alto — Existen hechos de violencia en la pareja que ocurren actualmente.'
    ],
    // 2.4. Historia de maltrato de personas cuidadoras
    'historia_maltrato_cuidadores' => [
        'a' => 'No es posible determinar — Falta información sobre la historia de maltrato infantil en las personas cuidadoras.',
        'b' => 'Riesgo nulo o bajo — Sin antecedentes de maltrato en la infancia o se recibió apoyo terapéutico oportuno.',
        'c' => 'Riesgo medio — Episodios de maltrato o negligencia leve/moderada durante la infancia de la persona cuidadora.',
        'd' => 'Riesgo alto — Maltrato o negligencia grave y/o sistemática en la infancia de la persona cuidadora.'
    ],
    // 2.5. Antecedentes penales de personas cuidadoras
    'antecedentes_penales_cuidadores' => [
        'a' => 'No es posible determinar — No hay información suficiente acerca de antecedentes penales de las personas cuidadoras.',
        'b' => 'Riesgo nulo o bajo — Ninguna de las personas cuidadoras cuenta con antecedentes penales.',
        'c' => 'Riesgo medio — Antecedentes penales leves (sin violencia) en una o más personas cuidadoras.',
        'd' => 'Riesgo alto — Antecedentes penales graves o delitos con violencia en una o más personas cuidadoras.'
    ],
    // 2.6. Dificultades de soporte social
    'dificultades_soporte_social' => [
        'a' => 'No es posible determinar — Falta información acerca de la red de apoyo social de la familia.',
        'b' => 'Riesgo nulo o bajo — Existen buenas redes de apoyo y/o adecuado acceso a servicios sociales.',
        'c' => 'Riesgo medio — Hay algunas redes de apoyo, pero limitadas; acceso a servicios acotado.',
        'd' => 'Riesgo alto — Aislamiento social y/o acceso muy limitado a servicios que dificulta el cuidado.'
    ],
    // 2.7. Estrés de supervivencia
    'estres_supervivencia' => [
        'a' => 'No es posible determinar — Falta información para evaluar condiciones socioeconómicas de la familia.',
        'b' => 'Riesgo nulo o bajo — La situación financiera/empleo cubre necesidades básicas; no hay estrés socioeconómico mayor.',
        'c' => 'Riesgo medio — En el último año, la familia enfrentó problemas financieros o de empleo con repercusiones moderadas.',
        'd' => 'Riesgo alto — Serios problemas financieros, de empleo o vivienda que ponen en riesgo la supervivencia familiar.'
    ],
    // 2.8. Deficiencia en habilidades de cuidado
    'deficiencia_habilidades_cuidado' => [
        'a' => 'No es posible determinar — Falta información para evaluar habilidades de crianza.',
        'b' => 'Riesgo nulo o bajo — Habilidades de cuidado apropiadas al nivel de desarrollo del niño, niña o adolescente.',
        'c' => 'Riesgo medio — Ciertas carencias en habilidades de cuidado que requieren atención o mejora.',
        'd' => 'Riesgo alto — Serios déficits en habilidades de cuidado que ponen en riesgo el bienestar del NNA.'
    ],
    // 2.9. Actitudes negativas hacia el niño, niña o adolescente
    'actitudes_negativas_nna' => [
        'a' => 'No es posible determinar — Falta información sobre las actitudes de las personas cuidadoras hacia el NNA.',
        'b' => 'Riesgo nulo o bajo — No se observan actitudes negativas o hostiles hacia el NNA.',
        'c' => 'Riesgo medio — Ocasionalmente se identifican actitudes negativas o conflictivas hacia el NNA.',
        'd' => 'Riesgo alto — Frecuentes actitudes negativas o abiertamente hostiles hacia el NNA.'
    ],
    // 2.10. Atención prenatal retrasada o ausente
    'atencion_prenatal_retrasada_ausente' => [
        'a' => 'No es posible determinar — Falta información para evaluar la atención prenatal de la madre.',
        'b' => 'Riesgo nulo o bajo — Atención prenatal desde el primer trimestre y de forma completa.',
        'c' => 'Riesgo medio — Atención prenatal iniciada en el segundo trimestre o intermitente.',
        'd' => 'Riesgo alto — Atención prenatal solo en el tercer trimestre o nula.'
    ],
    // 2.11. Inestabilidad en los cuidados
    'inestabilidad_cuidados' => [
        'a' => 'No es posible determinar — Falta información sobre la estabilidad de personas cuidadoras.',
        'b' => 'Riesgo nulo o bajo — Cuidado estable con figuras claramente definidas y constantes.',
        'c' => 'Riesgo medio — Algunas irregularidades o alternancia ocasional de personas cuidadoras.',
        'd' => 'Riesgo alto — Multiplicidad de cuidadores y alta rotación que genera inestabilidad significativa.'
    ],
    // 2.12. Ideación suicida de personas cuidadoras
    'ideacion_suicida_cuidadores' => [
        'a' => 'No es posible determinar — Falta información acerca de ideaciones suicidas en las personas cuidadoras.',
        'b' => 'Riesgo nulo o bajo — No hay antecedentes ni indicios de ideación suicida en las personas cuidadoras.',
        'c' => 'Riesgo medio — Al menos una persona cuidadora presenta ideación suicida o ha recibido atención al respecto.',
        'd' => 'Riesgo alto — Una persona cuidadora ha requerido internación por intento/ideación suicida grave.'
    ],
    // 2.13. Actitudes negativas hacia la intervención
    'actitudes_negativas_intervencion' => [
        'a' => 'No es posible determinar — Falta información acerca de las actitudes hacia la intervención.',
        'b' => 'Riesgo nulo o bajo — No existen actitudes negativas frente a la intervención o los profesionales.',
        'c' => 'Riesgo medio — Hay actitudes negativas ocasionales pero aún se puede trabajar con la familia.',
        'd' => 'Riesgo alto — Actitudes de rechazo frontal que impiden la labor de intervención.'
    ],
    // 2.14. Compromiso colaborativo (Escala de protección)
    'compromiso_colaborativo' => [
        'a' => 'No es posible determinar — Falta información acerca del nivel de compromiso con la intervención.',
        'b' => 'Protección nula o baja — No se establece una relación de compromiso colaborativo con profesionales.',
        'c' => 'Protección media — La relación de compromiso colaborativo es limitada, pero con disposición a mejorar.',
        'd' => 'Protección alta — Las personas cuidadoras colaboran de forma activa y sostienen la intervención.'
    ],
    // 2.15. Extrema minimización o negación del maltrato
    'extrema_minimizacion_negacion_maltrato' => [
        'a' => 'No es posible determinar — Falta información para evaluar la actitud ante el maltrato.',
        'b' => 'Riesgo nulo o bajo — Reconocimiento claro del abuso o maltrato sufrido por el NNA.',
        'c' => 'Riesgo medio — Alguna manifestación de minimización, aunque no niegan completamente los hechos.',
        'd' => 'Riesgo alto — Negación total o justificación del maltrato, contradictorio con los antecedentes del caso.'
    ],
    // 2.16. Terapia para padres o personas cuidadoras (Escala de protección)
    'terapia_cuidadores' => [
        'a' => 'No es posible determinar — Falta información acerca de la asistencia a terapia de las personas cuidadoras.',
        'b' => 'Protección nula o baja — Nunca han asistido a terapia o se niegan a participar.',
        'c' => 'Protección media — Asisten a terapia pero abandonan el proceso antes de finalizar.',
        'd' => 'Protección alta — Participan activamente en terapia y finalizan el proceso exitosamente.'
    ],
    // 2.17. Reunificaciones fallidas
    'reunificaciones_fallidas' => [
        'a' => 'No es posible determinar — Falta información sobre intentos de reunificación familiar previos.',
        'b' => 'Riesgo nulo o bajo — La reunificación fue exitosa y se ha mantenido por al menos 2 años.',
        'c' => 'Riesgo medio — La reunificación se produjo, pero con dificultades en la inserción del NNA en su núcleo familiar.',
        'd' => 'Riesgo alto — Al menos una reunificación fallida, generando la salida del NNA tras un intento de devolución al hogar.'
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
	    'label' => '2.2. Consumo problemático de alcohol y sustancias',
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
	    'label' => '2.8. Deficiencia en habilidades de cuidado',
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
	    'info'=>'La presencia de múltiples personas encargadas del cuidado de un niño, niña o adolescente puede indicar una falta de estabilidad en su entorno familiar. La presencia de múltiples personas cuidadoras puede sugerir que la atención al menor cambia con regularidad, lo que podría exponerlo a situaciones de abuso y abandono. Además, la diversidad de personas cuidadoras puede afectar la calidad de los vínculos de apego con los padres, aumentando así el riesgo de maltrato infantil (Duffy et al., 2015; Vial et al., 2021).',
        'descripcion' => 'Cambios frecuentes o múltiples personas a cargo del cuidado del NNA.'
    ],
    'ideacion_suicida_cuidadores' => [
	    'label' => '2.12. Ideación suicida de personas cuidadoras',
	    'info'=>'La ideación suicida constituye un preocupante factor de riesgo, que expone al niño, niña o adolescente a nuevas situaciones de victimización (de Ruiter et al., 2020), denotando alteraciones psicopatológicas en las personas cuidadoras. Esta implica pensar, considerar o planificar el suicidio y suele estar asociada a cuadros depresivos o trastornos del estado de ánimo (Meyer et al., 2010).      
En casos extremos, la ideación suicida puede dar lugar a lo que se ha denominado como suicidio extendido (Xu, et al, 2024). En este escenario se ha descrito el homicidio-suicidio por motivación “altruista”, en que el perpetrador termina con la vida de sus allegados antes de suicidarse (Flynn et al., 2016), en particular de los niños a su cargo, pues los considera demasiado débiles para sobrevivir sin su cuidado (Xu, et al, 2024). También el homicidio puede estar motivado por un deseo de causar el máximo daño a la pareja o ex pareja, particularmente en el marco de violencia de género.',
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
                <p><i><?php echo $data['descripcion']; ?></i></p>
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

