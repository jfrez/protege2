<?php include_once("config.php"); ?>
<?php include_once("header.php"); ?>
<?php
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result(); 
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $error = 'ok!!';
            $_SESSION['userid'] = $row['userid'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['name'] = $row['name'];
            header('Location: homepage.php');
            exit(); 
        } else {
            $error = 'Invalid password!';
        }
    } else {
        $error = 'Invalid email!';
    }

    $stmt->close(); // Close the statement
}
?>

<link rel="stylesheet" href="login.css">

<body>

<br>
    <img src="/images/logo.jpg" alt="Logo UDP"> <!-- Logo de la UDP -->
<div class="container">
    <h2>PROTEGE</h2>
    <form action="login.php" method="POST">
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

