<?php
session_start();

// Check if data has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize input data
    $name = htmlspecialchars($_POST['name']);
    $middleName = htmlspecialchars($_POST['middleName']);
    $lastName1 = htmlspecialchars($_POST['lastName1']);
    $lastName2 = htmlspecialchars($_POST['lastName2']);
    $age = htmlspecialchars($_POST['age']);
    $rut = htmlspecialchars($_POST['rut']);
    $address = htmlspecialchars($_POST['address']);
    
    // Save the data into session or database if needed
    $_SESSION['form_data'] = [
        'name' => $name,
        'middleName' => $middleName,
        'lastName1' => $lastName1,
        'lastName2' => $lastName2,
        'age' => $age,
        'rut' => $rut,
        'address' => $address,
    ];

    // Display the submitted information
    echo "<h1>Submitted Information</h1>";
    echo "<p><strong>Name:</strong> $name</p>";
    echo "<p><strong>Middle Name:</strong> $middleName</p>";
    echo "<p><strong>Last Name 1:</strong> $lastName1</p>";
    echo "<p><strong>Last Name 2:</strong> $lastName2</p>";
    echo "<p><strong>Age:</strong> $age</p>";
    echo "<p><strong>RUT:</strong> $rut</p>";
    echo "<p><strong>Address:</strong> $address</p>";

    // Navigation to the next part
    echo '<h2>Factores de Riesgo</h2>';
    echo '<form method="POST" action="nextpage_riesgo.php">';
    echo '<h3>Factores Personales</h3>';
    foreach ([
        "Historia Familiar de Problemas de Salud Mental",
        "Antecedentes de Abuso de Sustancias",
        "Problemas de Comportamiento y de Regulación Emocional",
        "Baja Autoestima",
        "Enfermedades Crónicas o Discapacidades Físicas",
        "Estrés Prolongado o Traumático"
    ] as $factor) {
        echo "<label>$factor</label><input type='range' min='1' max='4' step='1' name='factores_riesgo_personales[]' required>";
    }
    
    echo '<h3>Factores Familiares</h3>';
    foreach ([
        "Conflictos Familiares y Violencia Doméstica",
        "Falta de Apoyo Emocional y Supervisión",
        "Abuso Físico, Emocional o Sexual",
        "Pérdida de uno o ambos Padres",
        "Padres con Problemas de Salud Mental o Abuso de Sustancias"
    ] as $factor) {
        echo "<label>$factor</label><input type='range' min='1' max='4' step='1' name='factores_riesgo_familiares[]' required>";
    }
    
    echo '<h3>Factores Sociales</h3>';
    foreach ([
        "Exclusión Social y Falta de Redes de Apoyo",
        "Pobreza y Dificultades Económicas",
        "Experiencias de Discriminación y Estigmatización",
        "Influencias Negativas de Pares y Amigos",
        "Ambientes Escolares Poco Seguros o Violentos"
    ] as $factor) {
        echo "<label>$factor</label><input type='range' min='1' max='4' step='1' name='factores_riesgo_sociales[]' required>";
    }

    echo '<h3>Factores Ambientales</h3>';
    foreach ([
        "Vivienda Inadecuada o Condiciones de Vida Peligrosas",
        "Acceso Limitado a Servicios de Salud y Educación",
        "Desastres Naturales y Crisis Humanitarias",
        "Exposición a Violencia Comunitaria"
    ] as $factor) {
        echo "<label>$factor</label><input type='range' min='1' max='4' step='1' name='factores_riesgo_ambientales[]' required>";
    }

    // Add a button to submit this part
    echo '<button type="submit">Continuar a Factores de Protección</button>';
    echo '</form>';
    
} else {
    echo "No data received.";
}
?>
