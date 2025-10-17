<?php
include_once("config.php");
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'supervisor'], true)) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['userid'])) {
    echo "Error: Usuario no especificado.";
    exit();
}

$user_id = (int)$_GET['userid'];

$userQuery = "SELECT name, last_name FROM users WHERE userid = ?";
$userStmt = sqlsrv_query($conn, $userQuery, [$user_id]);
$user = sqlsrv_fetch_array($userStmt, SQLSRV_FETCH_ASSOC);
sqlsrv_free_stmt($userStmt);
$userName = $user ? $user['name'] . ' ' . $user['last_name'] : 'Usuario';

$evalQuery = "SELECT id, nombre, rut, cod_nino, valoracion_global, fecha_evaluacion
              FROM evaluacion WHERE user_id = ? ORDER BY fecha_evaluacion DESC";
$params = [$user_id];
$stmt = sqlsrv_query($conn, $evalQuery, $params);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

include_once("header.php");
?>
<div class="container mt-4">
    <h2>Evaluaciones de <?= htmlspecialchars($userName) ?></h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>RUT</th>
                <th>Código Niño</th>
                <th>Riesgo</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
            <tr>
                <td><?= htmlspecialchars($row['nombre']) ?></td>
                <td><?= htmlspecialchars($row['rut']) ?></td>
                <td><?= htmlspecialchars($row['cod_nino']) ?></td>
                <td><?= htmlspecialchars($row['valoracion_global']) ?></td>
                <td><?= $row['fecha_evaluacion'] instanceof DateTime ? $row['fecha_evaluacion']->format('Y-m-d') : htmlspecialchars($row['fecha_evaluacion']) ?></td>
                <td>
                    <a href="resumenb.php?evaluacion_id=<?= $row['id'] ?>" class="btn btn-sm btn-secondary">Ver</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php sqlsrv_free_stmt($stmt); ?>

