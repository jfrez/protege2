<?php include_once("config.php"); ?>
<?php include_once("header.php"); ?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }
}
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
            $error = 'ok!!';
            $_SESSION['userid'] = $row['userid'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['role'] = $row['role'];

            if (empty($row['token'])) {
                $row['token'] = bin2hex(random_bytes(16));
                $updateSql = "UPDATE users SET token = ? WHERE userid = ?";
                $updateParams = array($row['token'], $row['userid']);
                $updateStmt = sqlsrv_query($conn, $updateSql, $updateParams);
                if ($updateStmt === false) {
                    die(print_r(sqlsrv_errors(), true));
                }
                sqlsrv_free_stmt($updateStmt);
            }

            $_SESSION['token'] = $row['token'];
            $_SESSION['login_method'] = 'userpass';
            header('Location: homepage.php');
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

<link rel="stylesheet" href="login.css">

<body>

<br>
    <img src="/images/logo.jpg" alt="Logo UDP"> <!-- Logo de la UDP -->
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
    <div class="switch">
    </div>
</div>

</body>
</html>

