<?php

$host = "127.0.0.1";
$user = "user";
$passwd = "password";
$database = "fr";

$conn = new mysqli($host, $user, $passwd, $database);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$error = '';

    echo("holaaaa");
    $name = "1";
    $middleName = "1";
    $lastName1 = "1";
    $lastName2 = "1";
    $age = "1";
    $rut = "1";
    $address = "1";
    $riesgo = [1];
    $userid = "1";
    $result = "1";

    // save child
    $sql = "INSERT INTO people (name, address, age, last_name, last_name2, middle_name, rut) 
            VALUES ('$name', '$address', '$age', '$lastName1', '$lastName2', '$middleName', '$rut')";

    if ($conn->query($sql)) {
        $personId = $conn->insert_id;
        // save form
        $sql2 = "INSERT INTO assessments (input, result, personid, userid) 
                 VALUES ('" . json_encode($riesgo) . "', '$result', '$personId', '$userid')";

        if ($conn->query($sql2)) {
            echo json_encode(['status' => 'success', 'personId' => $personId]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error saving assessment.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error saving person.']);
    }
    
    // Close the database connection
    $conn->close();
    echo json_encode(['status' => 'success', 'personId' => $personId]);
?>





