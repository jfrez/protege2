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

    // Create evaluacion table if it doesn't exist
    // Ensure evaluacion table exists under dbo schema
    "IF OBJECT_ID(N'dbo.evaluacion', N'U') IS NULL BEGIN CREATE TABLE dbo.evaluacion (" .
        " id INT IDENTITY PRIMARY KEY," .
        " nombre NVARCHAR(255)," .
        " rut NVARCHAR(50)," .
        " fecha_nacimiento DATE," .
        " edad INT," .
        " escolaridad NVARCHAR(255)," .
        " region NVARCHAR(255)," .
        " localidad NVARCHAR(255)," .
        " zona NVARCHAR(255)," .
        " sexo NVARCHAR(50)," .
        " diversidad NVARCHAR(50)," .
        " diversidad_cual NVARCHAR(255)," .
        " nacionalidad NVARCHAR(255)," .
        " pais_origen NVARCHAR(255)," .
        " situacion_migratoria NVARCHAR(255)," .
        " pueblo NVARCHAR(255)," .
        " pueblo_cual NVARCHAR(255)," .
        " convivencia NVARCHAR(255)," .
        " maltrato NVARCHAR(255)," .
        " otro_maltrato NVARCHAR(255)," .
        " relacion_perpetrador NVARCHAR(255)," .
        " otro_relacion NVARCHAR(255)," .
        " fuente NVARCHAR(255)," .
        " evaluador NVARCHAR(255)," .
        " profesion NVARCHAR(255)," .
        " centro NVARCHAR(255)," .
        " fecha_evaluacion DATE," .
        " user_id INT," .
        " token NVARCHAR(64)," .
        " login_method NVARCHAR(20)," .
        " valoracion_global NVARCHAR(255)," .
        " comentarios NVARCHAR(MAX)," .
        " obs_caracterizacion NVARCHAR(MAX)," .
        " obs_variables_extra NVARCHAR(MAX)," .
        " CONSTRAINT FK_evaluacion_users FOREIGN KEY (user_id) REFERENCES dbo.users(userid)" .
    "); END"
];

foreach ($schemaQueries as $query) {
    $stmt = sqlsrv_query($conn, $query);
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    sqlsrv_free_stmt($stmt);
}

$adminEmail = 'admin@example.com';
$adminPassword = password_hash('adminadmin', PASSWORD_BCRYPT);

$checkStmt = sqlsrv_query($conn, "SELECT userid FROM users WHERE email = ?", [$adminEmail]);
if ($checkStmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

if ($row = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC)) {
    $updateStmt = sqlsrv_query($conn, "UPDATE users SET password = ?, role = 'admin' WHERE userid = ?", [$adminPassword, $row['userid']]);
    if ($updateStmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    sqlsrv_free_stmt($updateStmt);
} else {
    $insertStmt = sqlsrv_query($conn, "INSERT INTO users (name, last_name, email, password, role) VALUES (?, ?, ?, ?, 'admin')", ['Admin', 'User', $adminEmail, $adminPassword]);
    if ($insertStmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    sqlsrv_free_stmt($insertStmt);
}
sqlsrv_free_stmt($checkStmt);

$error = '';
if (isset($_SESSION['email'])) {
    $user_email = $_SESSION['email'];
    $userid = $_SESSION['userid'];
}
?>
