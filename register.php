<?php
$host = "127.0.0.1";
$user = "root";
$passwd = "holahola";
$database = "fr";

$conn = new mysqli($host, $user, $passwd, $database);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
$error = '';
$generatedPassword = '';
$email = '';

if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $lastname = $_POST['last_name'];
    $email = $_POST['email'];

    // Generate a random password
    $generatedPassword = bin2hex(random_bytes(4)); // Generates a 8-character random password
    $hashedPassword = password_hash($generatedPassword, PASSWORD_BCRYPT);

    // Check if email already exists
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $error = 'email already exists!';
    } else {
        $sql = "INSERT INTO users (name, last_name, email, password) VALUES ('$name', '$lastname', '$email', '$hashedPassword')";
        if ($conn->query($sql)) {
            // Use JavaScript to show credentials in a popup
            echo "<script>
                    alert('Registro exitoso!\\nEmail: $email\\nClave: $generatedPassword');
                  </script>";
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
