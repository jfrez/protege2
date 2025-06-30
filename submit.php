<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // Start the session

$host = "127.0.0.1";
$user = "user";
$passwd = "password";
$database = "fr";

$conn = new mysqli($host, $user, $passwd, $database);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$error = '';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = json_decode(file_get_contents('php://input'), true);
    $name = $data['name'];
    $middleName = $data['middleName'];
    $lastName1 = $data['lastName1'];
    $lastName2 = $data['lastName2'];
    $age = $data['age'];
    $rut = $data['rut'];
    $address = $data['address'];
    $riesgo = $data['riesgo'];
    $userid = $data['userid'];
    $predictionValue = $data['predictionValue'];

    // save child
    $sql = "INSERT INTO people (name, address, age, last_name, last_name2, middle_name, rut) 
            VALUES ('$name', '$address', '$age', '$lastName1', '$lastName2', '$middleName', '$rut')";

    if ($conn->query($sql)) {
        $personId = $conn->insert_id;
        // save form
        $sql2 = "INSERT INTO assessments (input, result, personid, userid) 
                 VALUES ('" . json_encode($riesgo) . "', '$predictionValue', '$personId', '$userid')";

        if ($conn->query($sql2)) {
            echo json_encode(['status' => 'success', 'personId' => $personId]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error saving assessment.']);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error saving person.']);
        exit;
    }
    // Close the database connection
    $conn->close();
    echo json_encode(['status' => 'success', 'personId' => $personId]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
