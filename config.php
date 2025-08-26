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

// Ensure essential tables and data exist
$adminPassword = password_hash('adminadmin', PASSWORD_BCRYPT);
$schemaQueries = [
    // Create users table if it doesn't exist
    "IF OBJECT_ID(N'users', N'U') IS NULL BEGIN CREATE TABLE users (" .
        " userid INT IDENTITY PRIMARY KEY," .
        " name NVARCHAR(100)," .
        " last_name NVARCHAR(100)," .
        " email NVARCHAR(255) UNIQUE NOT NULL," .
        " password NVARCHAR(255) NOT NULL," .
        " token NVARCHAR(64)
    ); END",

    // Ensure role column is available
    "IF COL_LENGTH('users', 'role') IS NULL BEGIN ALTER TABLE users ADD role NVARCHAR(20) NOT NULL DEFAULT 'user'; END",

    // Seed default admin user
    "IF NOT EXISTS (SELECT 1 FROM users WHERE email = 'admin@example.com') BEGIN " .
        "INSERT INTO users (name, last_name, email, password, role) VALUES ('Admin', 'User', 'admin@example.com', '" . $adminPassword . "', 'admin'); END"
];

foreach ($schemaQueries as $query) {
    $stmt = sqlsrv_query($conn, $query);
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    sqlsrv_free_stmt($stmt);
}

$error = '';
if (isset($_SESSION['email'])) {
    $user_email = $_SESSION['email'];
    $userid = $_SESSION['userid'];
}
?>
