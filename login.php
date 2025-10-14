<?php
include_once("config.php");
include_once("utils/password_utils.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }
}

$error = '';
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = ?";
    $params = array($email);
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        if (password_verify($password, $row['password'])) {
            session_regenerate_id(true);
            $_SESSION['userid'] = $row['userid'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['must_change_password'] = $row['must_change_password'];

            if (!passwordMeetsPolicy($password)) {
                $_SESSION['must_change_password'] = 1;
                sqlsrv_query($conn, 'UPDATE users SET must_change_password = 1 WHERE userid = ?', [$row['userid']]);
                $_SESSION['policy_message'] = 'Su clave no cumple con la política de complejidad: debe tener al menos 8 caracteres e incluir letras mayúsculas, minúsculas, números y símbolos. Debe cambiarla.';
            }

            $token = bin2hex(random_bytes(16));
            $tokenHash = hash('sha256', $token);
            $expiresAt = date('Y-m-d H:i:s', time() + 3600);
            $updateSql = "UPDATE users SET token_hash = ?, token_expires_at = ?, token_used = 0 WHERE userid = ?";
            $updateParams = array($tokenHash, $expiresAt, $row['userid']);
            $updateStmt = sqlsrv_query($conn, $updateSql, $updateParams);
            if ($updateStmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }
            sqlsrv_free_stmt($updateStmt);

            $_SESSION['token'] = $token;
            $_SESSION['login_method'] = 'userpass';
            if ($_SESSION['must_change_password']) {
                header('Location: change_password.php');
            } else {
                header('Location: homepage.php');
            }
            exit();
        } else {
            $error = 'Invalid password!';
        }
    } else {
        $error = 'Invalid email!';
    }

    sqlsrv_free_stmt($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
    <style>
        .login-footer {
            margin-top: 2rem;
            text-align: center;
            color: #58595B;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
<br>
    <img src="/images/logo.jpg" alt="Logo UDP">
<div class="container">
    <h2>PROTEGE</h2>
    <form action="login.php" method="POST">
        <?php csrf_input(); ?>
        <input type="text" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit" name="login">Login</button>
    </form>
    <?php if ($error): ?>
        <div style="color: red;"><?php echo $error; ?></div>
    <?php endif; ?>
    <div class="switch"></div>
</div>
<footer class="login-footer">Versión <?php echo htmlspecialchars(APP_VERSION); ?></footer>
</body>
</html>
