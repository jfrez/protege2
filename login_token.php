<?php
include_once("config.php");

if (isset($_POST['token']) && $_POST['token'] !== '') {
    $token = $_POST['token'];
    $tokenHash = hash('sha256', $token);
    $sql = "SELECT userid, email, name, role FROM users WHERE token_hash = ? AND token_used = 0 AND token_expires_at > GETDATE()";
    $params = array($tokenHash);
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $_SESSION['userid'] = $row['userid'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['name'] = $row['name'];
        $_SESSION['login_method'] = 'token';
        $_SESSION['role'] = $row['role'];
        $_SESSION['token'] = bin2hex(random_bytes(16));
        sqlsrv_free_stmt($stmt);
        $invalidateSql = "UPDATE users SET token_hash = NULL, token_expires_at = NULL, token_used = 1 WHERE userid = ?";
        $invalidateStmt = sqlsrv_query($conn, $invalidateSql, array($row['userid']));
        if ($invalidateStmt !== false) {
            sqlsrv_free_stmt($invalidateStmt);
        }
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
