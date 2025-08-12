<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

$serverName = getenv('DB_HOST') ?: 'db';
$dbName = getenv('DB_NAME') ?: 'protege';
$connectionOptions = [
    "Uid" => getenv('DB_USER') ?: 'sa',
    "PWD" => getenv('DB_PASSWORD') ?: 'YourStrong@Passw0rd',
    "CharacterSet" => 'UTF-8',
    "Encrypt" => 1,
    "TrustServerCertificate" => 1
];

// Connect to the server without specifying a database so we can create it if needed
$maxRetries = 5;
$conn = false;
for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
    $conn = sqlsrv_connect($serverName, $connectionOptions);
    if ($conn !== false) {
        break;
    }
    sleep(2);
}
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Ensure the requested database exists
sqlsrv_query($conn, "IF DB_ID(N'$dbName') IS NULL EXEC('CREATE DATABASE [$dbName]');");
sqlsrv_close($conn);

// Reconnect targeting the desired database
$connectionOptions['Database'] = $dbName;
$conn = false;
for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
    $conn = sqlsrv_connect($serverName, $connectionOptions);
    if ($conn !== false) {
        break;
    }
    sleep(2);
}
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

$error = '';
if (isset($_SESSION['email'])) {
    $user_email = $_SESSION['email'];
    $userid = $_SESSION['userid'];
}
?>
