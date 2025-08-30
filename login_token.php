<?php
include_once("config.php");

if (isset($_GET['token']) && $_GET['token'] !== '') {
    $token = $_GET['token'];
    $sql = "SELECT userid, email, name, role FROM users WHERE token = ?";
    $params = array($token);
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $_SESSION['userid'] = $row['userid'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['name'] = $row['name'];
        $_SESSION['token'] = $token;
        $_SESSION['login_method'] = 'token';
        $_SESSION['role'] = $row['role'];
        sqlsrv_free_stmt($stmt);
        header('Location: homepage.php');
        exit();
    } else {
        sqlsrv_free_stmt($stmt);
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
