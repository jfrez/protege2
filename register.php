<?php
include_once("config.php");
include_once("utils/password_utils.php");

$error = '';
$generatedPassword = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }
}

if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $lastname = $_POST['last_name'];
    $email = $_POST['email'];

    // Generate a secure temporary password
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
            // Send credentials via email instead of displaying them
            $subject = 'Credenciales de acceso PROTEGE';
            $message = "Su clave temporal es: $generatedPassword\nPor favor, cámbiela al iniciar sesión.";
            $headers = 'Content-Type: text/plain; charset=UTF-8';
            @mail($email, $subject, $message, $headers);
            echo '<p style="color:green;">Registro exitoso. La clave inicial se envió al correo proporcionado.</p>';
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
    <form action="register.php" method="POST">
        <?php csrf_input(); ?>
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
