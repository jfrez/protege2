<?php
include_once("config.php");
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$query = "SELECT u.userid, u.name, u.last_name, COUNT(e.id) AS completed
          FROM users u
          LEFT JOIN evaluacion e ON u.userid = e.user_id
          WHERE u.role = 'user'
          GROUP BY u.userid, u.name, u.last_name
          ORDER BY u.name";
$stmt = sqlsrv_query($conn, $query);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

include_once("header.php");
?>
<div class="container mt-4">
    <h2>Reporte de Evaluaciones</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Completadas</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
            <tr>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['last_name']) ?></td>
                <td><?= $row['completed'] ?></td>
                <td>
                    <a href="admin_user_evaluations.php?userid=<?= $row['userid'] ?>" class="btn btn-sm btn-primary">Ver Evaluaciones</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php sqlsrv_free_stmt($stmt); ?>

