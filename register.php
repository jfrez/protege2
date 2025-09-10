<?php
include_once("config.php");

$error = '';
$success = '';
$generatedPassword = '';
$email = '';

function generateSecurePassword($length = 16) {
    $length = max(16, $length);
    $lower = 'abcdefghijklmnopqrstuvwxyz';
    $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $digits = '0123456789';
    $special = '!@#$%^&*()-_+=<>?';
    $all = $lower . $upper . $digits . $special;
    $password = $lower[random_int(0, strlen($lower)-1)]
              . $upper[random_int(0, strlen($upper)-1)]
              . $digits[random_int(0, strlen($digits)-1)]
              . $special[random_int(0, strlen($special)-1)];
    for ($i = 4; $i < $length; $i++) {
        $password .= $all[random_int(0, strlen($all)-1)];
    }
    return str_shuffle($password);
}

function sendInitialPassword($email, $password) {
    // Implement secure email delivery or activation link here
    // mail($email, 'Clave temporal', "Su clave temporal es: $password");
}

if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $lastname = $_POST['last_name'];
    $email = $_POST['email'];

    // Generate a secure random password
    $generatedPassword = generateSecurePassword(16);
    $hashedPassword = password_hash($generatedPassword, PASSWORD_BCRYPT);

    // Check if email already exists
    $sql = "SELECT 1 FROM users WHERE email = ?";
    $params = array($email);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        $error = 'Error checking email!';
    } elseif (sqlsrv_fetch($stmt)) {
        $error = 'email already exists!';
    } else {
        $sql = "INSERT INTO users (name, last_name, email, password, role, must_change_password) VALUES (?, ?, ?, ?, 'user', 1)";
        $params = array($name, $lastname, $email, $hashedPassword);
        $stmt = sqlsrv_query($conn, $sql, $params);
        if ($stmt) {
            sendInitialPassword($email, $generatedPassword);
            $success = 'Registro exitoso. Por favor revise su correo para la clave inicial.';
        } else {
            $error = 'Registro fallido!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #00c6ff, #0072ff);
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            width: 450px;
            text-align: center;
        }
        .container h2 {
            margin-bottom: 25px;
            font-size: 24px;
        }
        .container form {
            display: flex;
            flex-direction: column;
        }
        .container form input {
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            outline: none;
            font-size: 16px;
        }
        .container form button {
            padding: 15px;
            background: #007bff;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }
        .container form button:hover {
            background: #0056b3;
        }
        .switch {
            margin-top: 15px;
        }
        .switch a {
            color: #A9BD93;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Create an Account</h2>
    <?php if ($error): ?>
        <p class="error" style="color:red;"><?= $error ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p class="success" style="color:green;"><?= $success ?></p>
    <?php endif; ?>
    <form action="register.php" method="POST">
        <input type="text" name="name" placeholder="First Name" required>
        <input type="text" name="last_name" placeholder="Last Name" required>
        <input type="text" name="email" placeholder="Email" required>
        <button type="submit" name="register">Register</button>
    </form>
    <div class="switch">
    </div>
</div>

</body>
</html>
