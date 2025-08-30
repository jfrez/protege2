<?php
include_once("config.php");
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Create user
if (isset($_POST['create'])) {
    $name = $_POST['name'];
    $lastname = $_POST['last_name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $sql = "INSERT INTO users (name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)";
    $params = array($name, $lastname, $email, $password, $role);
    sqlsrv_query($conn, $sql, $params);
}

// Update user
if (isset($_POST['update'])) {
    $userid = $_POST['userid'];
    $name = $_POST['name'];
    $lastname = $_POST['last_name'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $sql = "UPDATE users SET name=?, last_name=?, email=?, role=?, password=? WHERE userid=?";
        $params = array($name, $lastname, $email, $role, $password, $userid);
    } else {
        $sql = "UPDATE users SET name=?, last_name=?, email=?, role=? WHERE userid=?";
        $params = array($name, $lastname, $email, $role, $userid);
    }
    sqlsrv_query($conn, $sql, $params);
}

// Fetch users with assessment stats
$query = "SELECT u.userid, u.name, u.last_name, u.email, u.role, COUNT(a.id) AS total, SUM(CASE WHEN a.result IS NOT NULL THEN 1 ELSE 0 END) AS completed FROM users u LEFT JOIN assessments a ON u.userid=a.userid GROUP BY u.userid, u.name, u.last_name, u.email, u.role";
$stmt = sqlsrv_query($conn, $query);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
include_once("header.php");
?>
<div class="container mt-4">
    <h2>Gestión de Usuarios</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Assessments</th>
                <th>Completos</th>
                <th>Actualizar</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
            <tr>
                <form method="POST">
                    <td><input type="text" name="name" value="<?= htmlspecialchars($row['name']) ?>" class="form-control"/></td>
                    <td><input type="text" name="last_name" value="<?= htmlspecialchars($row['last_name']) ?>" class="form-control"/></td>
                    <td><input type="text" name="email" value="<?= htmlspecialchars($row['email']) ?>" class="form-control"/></td>
                    <td>
                        <select name="role" class="form-control">
                            <option value="user" <?= $row['role'] === 'user' ? 'selected' : '' ?>>user</option>
                            <option value="admin" <?= $row['role'] === 'admin' ? 'selected' : '' ?>>admin</option>
                        </select>
                    </td>
                    <td><?= $row['total'] ?></td>
                    <td><?= $row['completed'] ?></td>
                    <td>
                        <input type="password" name="password" placeholder="Nueva clave" class="form-control mb-1"/>
                        <input type="hidden" name="userid" value="<?= $row['userid'] ?>" />
                        <button type="submit" name="update" class="btn btn-primary btn-sm">Guardar</button>
                    </td>
                </form>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h3>Agregar Usuario</h3>
    <form method="POST" class="form-inline">
        <input type="text" name="name" placeholder="Nombre" class="form-control mb-2 mr-sm-2" required/>
        <input type="text" name="last_name" placeholder="Apellido" class="form-control mb-2 mr-sm-2" required/>
        <input type="email" name="email" placeholder="Email" class="form-control mb-2 mr-sm-2" required/>
        <input type="password" name="password" placeholder="Clave" class="form-control mb-2 mr-sm-2" required/>
        <select name="role" class="form-control mb-2 mr-sm-2">
            <option value="user">user</option>
            <option value="admin">admin</option>
        </select>
        <button type="submit" name="create" class="btn btn-success mb-2">Crear</button>
    </form>
</div>
<?php sqlsrv_free_stmt($stmt); ?>
