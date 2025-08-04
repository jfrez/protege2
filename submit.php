<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // Start the session
include_once("config.php");

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
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $params = array($name, $address, $age, $lastName1, $lastName2, $middleName, $rut);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt) {
        $res = sqlsrv_query($conn, "SELECT SCOPE_IDENTITY() AS id");
        $row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC);
        $personId = $row['id'];
        // save form
        $sql2 = "INSERT INTO assessments (input, result, personid, userid)
                 VALUES (?, ?, ?, ?)";
        $params2 = array(json_encode($riesgo), $predictionValue, $personId, $userid);
        $stmt2 = sqlsrv_query($conn, $sql2, $params2);

        if ($stmt2) {
            echo json_encode(['status' => 'success', 'personId' => $personId]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error saving assessment.']);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error saving person.']);
        exit;
    }
    sqlsrv_close($conn);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
