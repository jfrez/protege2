<?php
include_once("config.php");
include_once("utils/password_utils.php");

if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit();
}

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }
}

if (isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if ($new !== $confirm) {
        $message = 'Las nuevas contraseñas no coinciden.';
    } elseif (!passwordMeetsPolicy($new)) {
        $message = 'La nueva clave debe tener al menos 8 caracteres e incluir al menos una letra minúscula, una letra mayúscula, un número y un carácter especial.';
    } else {
        $stmt = sqlsrv_query($conn, 'SELECT password FROM users WHERE userid = ?', [$_SESSION['userid']]);
        if ($stmt && ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))) {
            if (password_verify($current, $row['password'])) {
                $hashed = password_hash($new, PASSWORD_BCRYPT);
                $update = sqlsrv_query($conn, 'UPDATE users SET password = ?, must_change_password = 0 WHERE userid = ?', [$hashed, $_SESSION['userid']]);
                if ($update) {
                    $_SESSION['must_change_password'] = 0;
                    $message = 'Clave actualizada correctamente.';
                    $success = true;
                } else {
                    $message = 'Error al actualizar la clave.';
                }
            } else {
                $message = 'Clave actual incorrecta.';
            }
            sqlsrv_free_stmt($stmt);
        } else {
            $message = 'Error al verificar la clave.';
        }
    }
}

include_once("header.php");
?>
<div class="container mt-4">
    <h2>Cambiar Clave</h2>
    <?php if ($message): ?>
        <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="POST">
        <?php csrf_input(); ?>
        <div class="form-group">
            <label>Clave Actual</label>
            <input type="password" name="current_password" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Nueva Clave</label>
            <input type="password" name="new_password" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Confirmar Nueva Clave</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>
        <button type="submit" name="change_password" class="btn btn-primary">Cambiar</button>
    </form>
</div>
