<?php
include_once("config.php");
include_once("utils/anonymization.php");

$role = $_SESSION['role'] ?? '';
$isAdmin = $role === 'admin';
$isSupervisor = $role === 'supervisor';

if (!$isAdmin && !$isSupervisor) {
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
$userName = $user ? trim(($user['name'] ?? '') . ' ' . ($user['last_name'] ?? '')) : 'Usuario';
$displayedUserName = $isSupervisor ? 'Profesional anonimizado' : $userName;

$evalQuery = "SELECT id, nombre, rut, cod_nino, valoracion_global, fecha_evaluacion
              FROM evaluacion WHERE user_id = ? ORDER BY fecha_evaluacion DESC";
$params = [$user_id];
$stmt = sqlsrv_query($conn, $evalQuery, $params);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

include_once("header.php");
$evaluaciones = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    if (isset($row['fecha_evaluacion']) && $row['fecha_evaluacion'] instanceof DateTime) {
        $row['fecha_evaluacion_formatted'] = $row['fecha_evaluacion']->format('Y-m-d');
    } else {
        $row['fecha_evaluacion_formatted'] = $row['fecha_evaluacion'] ?? '';
    }

    if ($isSupervisor) {
        $row = build_supervisor_display(anonymize_sensitive_fields($row));
    } else {
        $row = build_standard_display($row);
    }

    $evaluaciones[] = $row;
}
sqlsrv_free_stmt($stmt);
?>
<div class="container mt-4">
    <h2>Evaluaciones de <?= htmlspecialchars($displayedUserName) ?></h2>
    <?php if ($isSupervisor): ?>
        <div class="alert alert-info">Los datos mostrados han sido anonimizados para proteger la información personal.</div>
    <?php endif; ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nombre / Caso</th>
                <th>Identificador</th>
                <th>Código Niño</th>
                <th>Riesgo</th>
                <th>Fecha</th>
                <?php if ($isAdmin): ?>
                    <th>Acciones</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($evaluaciones as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['display_name']) ?></td>
                <td><?= htmlspecialchars($row['display_rut']) ?></td>
                <td><?= htmlspecialchars($row['display_cod_nino']) ?></td>
                <td><?= htmlspecialchars($row['valoracion_global'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['fecha_evaluacion_formatted'] ?? '') ?></td>
                <?php if ($isAdmin): ?>
                    <td>
                        <?php if (!empty($row['can_view_details'])): ?>
                            <a href="resumenb.php?evaluacion_id=<?= (int) $row['id'] ?>" class="btn btn-sm btn-secondary">Ver</a>
                        <?php else: ?>
                            <span class="text-muted">Restringido</span>
                        <?php endif; ?>
                    </td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>


