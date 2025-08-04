<?php
include_once("config.php");

if (isset($_GET['token']) && $_GET['token'] !== '') {
    $token = $_GET['token'];
    $stmt = $conn->prepare("SELECT userid, email, name FROM users WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $_SESSION['userid'] = $row['userid'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['name'] = $row['name'];
        $_SESSION['token'] = $token;
        $_SESSION['login_method'] = 'token';
        header('Location: homepage.php');
        exit();
    } else {
        header('HTTP/1.1 400 Bad Request');
        echo 'Invalid token';
        exit();
    }
} else {
    header('HTTP/1.1 400 Bad Request');
    echo 'Token is required';
    exit();
}
?>
