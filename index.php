<?php
include_once("config.php");
include_once("utils/password_utils.php");

// Aseguramos que $error esté definido para evitar warnings
$error = "";

// Si ya ha iniciado sesión, redirigimos a homepage
if (isset($_SESSION['userid'])) {
    header('Location: homepage.php');
    exit();
}

// Verificamos si se envió el formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }
}

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = ?";
    $params = array($email);
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        if (password_verify($password, $row['password'])) {
            // Login exitoso
            session_regenerate_id(true);
            $_SESSION['userid'] = $row['userid'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['must_change_password'] = $row['must_change_password'];

            if (!passwordMeetsPolicy($password)) {
                $_SESSION['must_change_password'] = 1;
                sqlsrv_query($conn, 'UPDATE users SET must_change_password = 1 WHERE userid = ?', [$row['userid']]);
                $_SESSION['policy_message'] = 'Su clave no cumple con la política de complejidad: debe tener al menos 8 caracteres e incluir letras mayúsculas, minúsculas, números y símbolos. Debe cambiarla.';
            }

            $token = bin2hex(random_bytes(16));
            $tokenHash = hash('sha256', $token);
            $expiresAt = date('Y-m-d H:i:s', time() + 3600);
            $updateSql = "UPDATE users SET token_hash = ?, token_expires_at = ?, token_used = 0 WHERE userid = ?";
            $updateParams = array($tokenHash, $expiresAt, $row['userid']);
            $updateStmt = sqlsrv_query($conn, $updateSql, $updateParams);
            if ($updateStmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }
            sqlsrv_free_stmt($updateStmt);

            $_SESSION['token'] = $token;
            $_SESSION['login_method'] = 'userpass';
            sqlsrv_free_stmt($stmt);
            if ($_SESSION['must_change_password']) {
                header('Location: change_password.php');
            } else {
                header('Location: homepage.php');
            }
            exit();
        } else {
            $error = 'Contraseña equivocada';
        }
    } else {
        $error = 'Correo inválido';
    }

    sqlsrv_free_stmt($stmt);
}

include_once("header.php");
?>

<link rel="stylesheet" href="index.css">

<div class="background"></div>
<div class="main-container">
    <div class="left-side">
        <div class="container">
            <h2>PROTEGE</h2>
            <form action="index.php" method="POST">
                <?php csrf_input(); ?>
                <input type="text" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <button type="submit" name="login">Login</button>
            </form>
            <?php if (!empty($error)): ?>
                <div style="color: red;"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="switch">
                <!-- Aquí podrías poner un enlace a "¿Olvidaste tu contraseña?" o a un registro, si corresponde -->
            </div>
        </div>
    </div>

<!-- Sección informativa sobre PROTEGE -->
<div class="right-side" style="overflow-y: auto; min-width:40%; max-height: 900px;">

<div class="container " style="min-width:90%; background: rgba(255,255,255,0.5);">
    <h3>¿Qué es PROTEGE?</h3>
    <p>
        PROTEGE es una guía de juicio profesional estructurado, basado en evidencia, cuyo objetivo es la evaluación
        del riesgo de victimización infantil recurrente, dirigida a niños, niñas y adolescentes que se encuentran
        en el Servicio Nacional de Protección Especializada a la Niñez y Adolescencia. PROTEGE ha sido creado para
        evaluar el riesgo de recurrencia victimal, entendida como la probabilidad de que un niño, niña o adolescente 
        que ya ha tenido un reporte por algún tipo de vulneración vuelva a tener un reporte por una nueva situación a futuro.
    </p>

    <h3>¿Por qué se creó PROTEGE?</h3>
    <p>
        PROTEGE ha sido creado pues existe clara evidencia en la literatura de que los niños, niñas o adolescentes 
        afectados por violencia presentan alto riesgo de sufrir nuevas victimizaciones. Por lo mismo, el Servicio 
        Nacional de Protección Especializada a la Niñez y Adolescencia enfrenta el desafío de evitar que estos hechos
        ocurran y se reiteren en el tiempo. Para esto, es necesario contar con una metodología rigurosa que permita 
        evaluar el riesgo de recurrencia victimal en niños, niñas y adolescentes que están en el Servicio. PROTEGE 
        responde al desafío de contar con una metodología de evaluación de riesgo de recurrencia victimal afianzada 
        en la evidencia y ajustada al contexto nacional.
    </p>

    <h3>¿Cómo se creó PROTEGE?</h3>
    <p>
        Para generar PROTEGE, se efectuó una revisión sistemática de la literatura que recoge la evidencia nacional 
        e internacional sobre factores de riesgo y protección de recurrencia de victimización infantil publicada 
        durante los últimos 10 años en revistas académicas. Junto con esto, se realizó una consulta a informantes 
        clave que  permitió conocer la experiencia de otros países en esta temática. Contando con los aportes de la 
        literatura y la experiencia comparada, se generó una metodología de evaluación de riesgo de recurrencia de 
        victimización infantil basada en el juicio profesional estructurado, la cual incorpora para su evaluación 
        los factores de riesgo y de protección que cuentan con apoyo en la evidencia.
    </p>

    <h3>¿Qué tipo de victimización evalúa PROTEGE?</h3>
    <p>
        PROTEGE permite la evaluación de riesgo de una nueva victimización de cualquier tipo, incluyendo maltrato 
        físico, psicológico, violencia sexual y/o negligencia. El riesgo que evalúa no es de una forma específica 
        de victimización futura, sino de una nueva denuncia o reporte administrativo por cualquier tipo de victimización.
    </p>

    <h3>¿Con qué fin debe utilizarse PROTEGE?</h3>
    <p>
        PROTEGE es un insumo orientado a operativizar la protección a la infancia, mediante una herramienta que permite 
        identificar a las víctimas con mayor riesgo de futura victimización y focalizar los recursos de la política pública 
        para reforzar la protección de quienes más lo requieren. PROTEGE debe utilizarse para potenciar el trabajo de los 
        profesionales desde una mirada preventiva, orientada a disminuir los factores de riesgo y potenciar los factores 
        protectores, ayudando a que niños, niñas y adolescentes puedan desarrollar sus máximas potencialidades en el marco 
        de una vida sin violencia.
    </p>

    <h3>¿Quiénes pueden aplicar PROTEGE?</h3>
    <p>
        PROTEGE puede ser aplicado por profesionales de la psicología y del trabajo social o similares, que cuenten con 
        formación especializada y con experiencia en el ámbito de vulneraciones de derecho hacia la infancia y la adolescencia.
    </p>
</div>
</div>
</div>

</body>
</html>

