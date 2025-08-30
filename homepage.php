<?php
session_start();
include_once("config.php");
include_once("header.php");

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['userid'])) {
    echo "Error: Debes iniciar sesión para acceder a esta página.";
    exit();
}

$user_id = $_SESSION['userid'];

// Ensure cod_nino column exists in evaluacion table
$colCheckSql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'evaluacion' AND COLUMN_NAME = 'cod_nino'";
$colCheckStmt = sqlsrv_query($conn, $colCheckSql);
if ($colCheckStmt && !sqlsrv_fetch_array($colCheckStmt, SQLSRV_FETCH_ASSOC)) {
    sqlsrv_free_stmt($colCheckStmt);
    $addColSql = "ALTER TABLE evaluacion ADD cod_nino NVARCHAR(50)";
    $addColStmt = sqlsrv_query($conn, $addColSql);
    if ($addColStmt === false) {
        error_log('Error al agregar cod_nino: ' . print_r(sqlsrv_errors(), true));
    } else {
        sqlsrv_free_stmt($addColStmt);
    }
} elseif ($colCheckStmt) {
    sqlsrv_free_stmt($colCheckStmt);
}

// Consultar evaluaciones; para administradores, mostrar todas
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $query = "
        SELECT e.*, e.evaluador AS evaluador_nombre
        FROM evaluacion e
        JOIN users u ON e.user_id = u.userid
    ";
    $params = [];
    $stmt = sqlsrv_query($conn, $query);
} else {
    $query = "
        SELECT e.*, e.evaluador AS evaluador_nombre
        FROM evaluacion e
        JOIN users u ON e.user_id = u.userid
        WHERE e.user_id = ?
    ";
    $params = [$user_id];
    $stmt = sqlsrv_query($conn, $query, $params);
}
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
$evaluaciones = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $evaluaciones[] = $row;
}
sqlsrv_free_stmt($stmt);
?>

<!-- Main Content -->
<div class="container mt-5">
    <div class="row">
        <div class="col-md-12 mb-4">
            <h2 class="font-weight-bold">Evaluaciones Previas</h2>
            <div class="input-group mb-3">
                <input type="text" id="search-bar" class="form-control" placeholder="Buscar evaluaciones..." onkeyup="filterEvaluations()">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" onclick="toggleSearchBar()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-4">
            <h3>Evaluaciones</h3>
            <!-- Contenedor con scroll -->
            <div class="list-group" id="evaluation-list" style="max-height:400px; overflow-y:auto;">
                <?php foreach ($evaluaciones as $evaluacion): ?>
                    <button class="list-group-item list-group-item-action" 
                            data-name="<?php echo htmlspecialchars($evaluacion['nombre']); ?>" 
                            data-rut="<?php echo htmlspecialchars($evaluacion['rut']); ?>" 
                            onclick="showDetails(<?php echo $evaluacion['id']; ?>)">
                        <?php echo htmlspecialchars($evaluacion['nombre']); ?> - <?php echo htmlspecialchars($evaluacion['rut']); ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <button class="btn btn-primary mt-3 w-100" onclick="location.href='seccion0.php'">Nueva Evaluación</button>

            <!-- Dropdown para exportar -->
            <div class="dropdown mt-3">
                <button class="btn btn-secondary dropdown-toggle w-100" type="button" id="exportDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Exportar
                </button>
                <div class="dropdown-menu w-100" aria-labelledby="exportDropdown">
                    <a class="dropdown-item" href="exportar.php?tipo=bruto">En bruto</a>
                    <a class="dropdown-item" href="exportar.php?tipo=anonimizado">Anonimizado</a>
                </div>
            </div>
        </div>

        <!-- Detail Area -->
        <div class="col-md-8">
            <h3>Detalles</h3>
            <div id="detail-info" class="border p-3 bg-light" style="min-height: 200px;">
                <p class="text-muted">Selecciona una evaluación para ver los detalles.</p>
            </div>
        </div>
    </div>
</div>

<script>
    const evaluationsData = <?php echo json_encode($evaluaciones); ?>;

    function toggleSearchBar() {
        const searchBar = document.getElementById('search-bar');
        searchBar.style.display = (searchBar.style.display === "none" || searchBar.style.display === "") ? "block" : "none";
        if (searchBar.style.display === "block") searchBar.focus();
    }

    function showDetails(evaluationId) {
        const evaluation = evaluationsData.find(e => e.id == evaluationId);
        const detailDiv = document.getElementById('detail-info');

        if (evaluation) {
            detailDiv.innerHTML = `
                <h4>${evaluation.nombre}</h4>
                <p><strong>Valoración de riesgo:</strong> ${evaluation.valoracion_global}</p>

                <p><strong>Edad:</strong> ${evaluation.edad}</p>
                <p><strong>RUT:</strong> ${evaluation.rut}</p>
                <p><strong>Fecha de Evaluación:</strong> ${evaluation.fecha_evaluacion}</p>
                <p><strong>Evaluador:</strong> ${evaluation.evaluador_nombre}</p>
                <a href="resumenb.php?evaluacion_id=${evaluation.id}" class="btn btn-primary">Ver Resumen</a>
            `;
        } else {
            detailDiv.innerHTML = '<p>No hay detalles disponibles.</p>';
        }
    }

    function filterEvaluations() {
        const query = document.getElementById('search-bar').value.toLowerCase();
        const evaluationItems = document.querySelectorAll('.list-group-item');

        evaluationItems.forEach(item => {
            const name = item.getAttribute('data-name').toLowerCase();
            const rut = item.getAttribute('data-rut').toLowerCase();
            const combinedText = name + ' ' + rut;
            item.style.display = combinedText.includes(query) ? '' : 'none';
        });
    }
</script>
<!-- Agrega los scripts de Bootstrap y jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</body>
</html>
