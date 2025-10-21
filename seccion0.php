<?php
session_start();

// Al iniciar una nueva evaluación, reiniciar cualquier dato previo almacenado en la sesión
if (isset($_SESSION['inserted_id'])) {
    unset($_SESSION['inserted_id']);
}
if (isset($_SESSION['form_data'])) {
    unset($_SESSION['form_data']);
}
if (isset($_SESSION['token'])) {
    unset($_SESSION['token']);
}

// Incluye la cabecera, donde posiblemente tienes tus <head>, estilos, etc.
include_once("header.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio de Evaluación - PROTEGE</title>
    <!-- Si quieres más estilos, agrégalos aquí o en tu header.php -->
</head>
<body>

<div class="container mt-4 mb-5">
    <h1>Guía para la valoración del riesgo de recurrencia de victimización infantil</h1>

    <!-- =============================== -->
    <!-- ¿CÓMO SE APLICA PROTEGE?        -->
    <!-- =============================== -->
    <div class="accordion-section">
        <h3 onclick="toggleAccordion(this)">¿Cómo se aplica PROTEGE?</h3>
        <div class="accordion-content">
            <p>La utilización de PROTEGE supone cuatro pasos generales.</p>
        </div>
    </div>

    <!-- =============================== -->
    <!-- 1. PRIMER PASO                  -->
    <!-- =============================== -->
    <div class="accordion-section">
        <h3 onclick="toggleAccordion(this)">1. Primer Paso: evaluación integral del caso</h3>
        <div class="accordion-content">
            <p>
                Para aplicar PROTEGE, se deben recopilar primero todos los antecedentes que se tienen sobre el niño, niña 
                o adolescente y su familia a la fecha de la evaluación. La aplicación de PROTEGE debe darse en el marco 
                de la evaluación integral del caso, incluyendo diferentes metodologías de evaluación, tales como entrevistas, 
                aplicación de instrumentos, visitas domiciliarias, técnicas de observación, entre otras que se estimen 
                pertinentes. La información puede obtenerse tanto directamente del niño, niña y adolescente como de sus familiares, 
                personas cuidadoras, personal escolar, sanitario, registros judiciales o administrativos, entre otros. 
                Una correcta triangulación de la información permitirá arribar a resultados más precisos con menor riesgo de sesgo.
                <br><br>
                Una vez que el proceso de evaluación se haya completado, se deben llenar las distintas secciones del instrumento, 
                realizando una valoración de la situación del niño, niña o adolescente en base a los ejes que propone la guía.
            </p>
        </div>
    </div>

    <!-- =============================== -->
    <!-- 2. SEGUNDO PASO                 -->
    <!-- =============================== -->
    <div class="accordion-section">
        <h3 onclick="toggleAccordion(this)">2. Segundo Paso: información de caracterización</h3>
        <div class="accordion-content">
            <p>
                Para comenzar a aplicar PROTEGE, se debe ingresar a la plataforma o formulario creado para su aplicación. 
                En la primera sección, se solicita información de caracterización del niño, niña o adolescente que es vital 
                como contexto de la evaluación. Esta información refiere a elementos sociodemográficos del niño, niña o adolescente, 
                así como a antecedentes clave del caso, asociados al tipo de maltrato reportado, así como su relación con quienes 
                habrían perpetrado estos hechos. También debe registrar información sobre el evaluador o evaluadora, incluyendo 
                la fecha en que se realiza la evaluación.
            </p>
        </div>
    </div>

    <!-- =============================== -->
    <!-- 3. TERCER PASO                  -->
    <!-- =============================== -->
    <div class="accordion-section">
        <h3 onclick="toggleAccordion(this)">3. Tercer paso: valoración de cada factor de riesgo y protección</h3>
        <div class="accordion-content">
            <p>
                En la segunda sección de PROTEGE, se debe evaluar la presencia o ausencia de 25 factores que cuentan con evidencia 
                en el aumento o disminución del riesgo de victimización recurrente. Los factores estarán agrupados temáticamente en 
                factores relativos al niño, niña o adolescente; su familia; y variables contextuales. A su vez, estarán agrupados 
                en factores de riesgo y factores protectores.
                <br><br>
                Para cada uno de estos factores, se debe evaluar el riesgo que enfrenta el niño, niña o adolescente considerando 
                una escala de evaluación de 4 niveles. 
                <br><strong>Cuando el factor es de riesgo,</strong> utiliza la siguiente escala de evaluación:
            </p>
            <ul>
                <li>a) no es posible determinar</li>
                <li>b) riesgo nulo o bajo</li>
                <li>c) riesgo medio</li>
                <li>d) riesgo alto</li>
            </ul>
            <p><strong>Cuando el factor es de protección,</strong> se utiliza la siguiente escala de evaluación:</p>
            <ul>
                <li>a) no es posible determinar</li>
                <li>b) protección nula o baja</li>
                <li>c) protección media</li>
                <li>d) protección alta</li>
            </ul>
            <p>
                Para apoyar al o la profesional en el proceso de valoración, el instrumento ofrece una descripción del tipo de 
                situación que sería esperable en cada uno de los niveles de la escala, sin pretender ser exhaustivo en esta descripción.
            </p>
        </div>
    </div>

    <!-- =============================== -->
    <!-- 4. INFORMACIÓN ADICIONAL        -->
    <!-- =============================== -->
    <div class="accordion-section">
        <h3 onclick="toggleAccordion(this)">4. Información adicional</h3>
        <div class="accordion-content">
            <p>
                Luego de analizar cada factor de riesgo o protección sustentado en la literatura, en la tercera sección de PROTEGE 
                se le pide ponderar, en base a su experiencia y conocimiento del caso, si algún elemento del perfil sociodemográfico 
                del niño, niña o adolescente -tal como su edad, nivel socioeconómico, identidad de género, entre otros- constituye un 
                factor adicional de riesgo o protección. Además, se le pedirá considerar si hay factores de riesgo o protección no 
                mencionados en la guía relevantes de considerar para el caso bajo análisis.
            </p>
        </div>
    </div>

    <!-- =============================== -->
    <!-- 5. VALORACIÓN GLOBAL            -->
    <!-- =============================== -->
    <div class="accordion-section">
        <h3 onclick="toggleAccordion(this)">5. Valoración global del riesgo de recurrencia</h3>
        <div class="accordion-content">
            <p>
                Finalmente, una vez analizada la situación del niño, niña o adolescente considerando cada factor de riesgo y protección, 
                quien realiza la evaluación debe efectuar una valoración global sobre el nivel de riesgo que ese niño, niña o adolescente 
                corre de registrar un nuevo reporte de victimización. Esta valoración global se debe realizar luego de ponderar toda la 
                información recogida en la guía.
                <br><br>
                Para ayudarle en la valoración global, los factores de riesgo y protección serán presentados en un cuadro resumen 
                denominado “Síntesis de factores de riesgo y protección”. En él se podrá ver con claridad cuáles son las categorías 
                de riesgo y protección que usted ha seleccionado para cada factor.
                <br><br>
                El juicio o valoración global del riesgo es de índole cualitativa, y no debe estar guiada por una simple sumatoria de 
                los factores analizados, pues su peso y configuración es única en cada caso particular. De hecho, la presencia de ciertos 
                factores puede ser gravitante por sí misma para incrementar el riesgo asociado de recurrencia victimal, como lo es, por 
                ejemplo, la ideación suicida de la persona cuidadora.
                <br><br>
                <strong>El juicio global respecto al nivel de riesgo está estructurado en tres niveles:</strong>
            </p>
            <ul>
                <li>a) riesgo bajo: el niño, niña o adolescente presenta un riesgo bajo de reportar una nueva victimización</li>
                <li>b) riesgo medio: el niño, niña o adolescente presenta un riesgo medio de reportar una nueva victimización</li>
                <li>c) riesgo alto: el niño, niña o adolescente presenta un riesgo alto de reportar una nueva victimización</li>
            </ul>
            <p>La aplicación de PROTEGE termina en este punto.</p>
        </div>
    </div>

    <!-- Botón para iniciar la evaluación -->
    <div class="text-right mt-4">
        <a href="seccion1.php" class="btn btn-primary">Iniciar Evaluación</a>
    </div>
</div>

<!-- ===== Estilos para el acordeón ===== -->
<style>
.accordion-section h3 {
    cursor: pointer;
    background: #f0f0f0;
    padding: 10px;
    margin-top: 20px;
    border-radius: 5px;
}
.accordion-section h3:hover {
    background: #e0e0e0;
}
.accordion-content {
    display: none;          /* cerrado por defecto */
    padding: 10px 0;
}
</style>

<!-- ===== JavaScript del acordeón ===== -->
<script>
function toggleAccordion(header) {
    const content = header.nextElementSibling;
    content.style.display = (content.style.display === 'block') ? 'none' : 'block';
}
</script>

<!-- Cierre de body y html si no se hace en header.php -->
</body>
</html>

