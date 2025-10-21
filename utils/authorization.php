<?php
if (!function_exists('abort_unauthorized_evaluation_access')) {
    function abort_unauthorized_evaluation_access() {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        http_response_code(403);
        echo 'Error: acceso no autorizado.';
        exit();
    }
}

if (!function_exists('verificarAccesoEvaluacion')) {
    function verificarAccesoEvaluacion($conn, $evaluacion_id) {
        static $authorizedEvaluations = [];

        $evaluacion_id = (int) $evaluacion_id;
        if ($evaluacion_id <= 0) {
            abort_unauthorized_evaluation_access();
        }

        if (isset($authorizedEvaluations[$evaluacion_id])) {
            return true;
        }

        $stmt = sqlsrv_query($conn, "SELECT user_id FROM dbo.evaluacion WHERE id = ?", [$evaluacion_id]);
        if ($stmt === false) {
            abort_unauthorized_evaluation_access();
        }

        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        if ($stmt !== false) {
            sqlsrv_free_stmt($stmt);
        }

        if (!$row) {
            abort_unauthorized_evaluation_access();
        }

        $sessionUserId = $_SESSION['userid'] ?? null;
        $role = $_SESSION['role'] ?? null;
        $isPrivilegedRole = in_array($role, ['admin', 'supervisor'], true);
        $ownsEvaluation = $sessionUserId !== null && isset($row['user_id']) && (int) $row['user_id'] === (int) $sessionUserId;

        if (!$isPrivilegedRole && !$ownsEvaluation) {
            abort_unauthorized_evaluation_access();
        }

        $authorizedEvaluations[$evaluacion_id] = true;
        return true;
    }
}
