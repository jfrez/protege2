<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

$serverName = getenv('DB_HOST') ?: 'db';
$connectionOptions = [
    "Database" => getenv('DB_NAME') ?: 'protege',
    "Uid" => getenv('DB_USER') ?: 'sa',
    "PWD" => getenv('DB_PASSWORD') ?: 'YourStrong@Passw0rd',
    "CharacterSet" => 'UTF-8',
    "Encrypt" => 1,
    "TrustServerCertificate" => 1
];

$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

$error = '';
if (isset($_SESSION['email'])) {
    $user_email = $_SESSION['email'];
    $userid = $_SESSION['userid'];
}
?>
