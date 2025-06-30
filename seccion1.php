<?php
session_start();
include_once("config.php");
include_once("header.php");

// Inicializar arreglo para errores
$errors = [];

// Obtener datos existentes si hay un 'inserted_id' en la sesión
$existing_data = array();

if (isset($_SESSION['inserted_id']) && $_SESSION['inserted_id'] != '') {
    $evaluacion_id = $_SESSION['inserted_id'];

    // Recuperar datos existentes
    $query = "SELECT * FROM evaluacion WHERE id = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $evaluacion_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $existing_data = $result->fetch_assoc();
        }
        $stmt->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibir y sanitizar datos del formulario
    $nombre = trim($_POST['nombre'] ?? '');
    $rut = trim($_POST['rut'] ?? '');
    $fecha_nacimiento = trim($_POST['fecha-nacimiento'] ?? '');
    $edad = trim($_POST['edad'] ?? '');
    $escolaridad = trim($_POST['escolaridad'] ?? '');
    $region = trim($_POST['region'] ?? '');
    $localidad = trim($_POST['localidad'] ?? '');
    $zona = trim($_POST['zona'] ?? '');
    $sexo = trim($_POST['sexo'] ?? '');
    $diversidad = trim($_POST['diversidad'] ?? '');
    $diversidad_cual = trim($_POST['diversidad-cual'] ?? '');
    $nacionalidad = trim($_POST['nacionalidad'] ?? '');
    $pais_origen = trim($_POST['pais-origen'] ?? '');
    $situacion_migratoria = trim($_POST['situacion-migratoria'] ?? '');
    $pueblo = trim($_POST['pueblo'] ?? '');
    $pueblo_cual = trim($_POST['pueblo-cual'] ?? '');
    $convivencia = isset($_POST['convivencia']) ? implode(",", $_POST['convivencia']) : '';
    $maltrato = isset($_POST['maltrato']) ? implode(",", $_POST['maltrato']) : '';
    $otro_maltrato = trim($_POST['otro-maltrato'] ?? '');
    $relacion_perpetrador = trim($_POST['relacion-perpetrador'] ?? '');
    $otro_relacion = trim($_POST['otro-relacion'] ?? '');
    $fuente = isset($_POST['fuente']) ? implode(",", $_POST['fuente']) : '';
    $evaluador = trim($_POST['evaluador'] ?? '');
    $profesion = trim($_POST['profesion'] ?? '');
    $centro = trim($_POST['centro'] ?? '');
    $fecha_evaluacion = trim($_POST['fecha-evaluacion'] ?? '');
    $userid = $_SESSION['userid'] ?? '';

    // Validaciones

    // 1. Información Personal
    if (empty($nombre)) {
        $errors['nombre'] = "El campo Nombre es obligatorio.";
    }

    if (empty($rut)) {
        $errors['rut'] = "El campo RUT es obligatorio.";
    } else {
        // Validar formato de RUT (simplificado)
        if (!preg_match("/^\d{1,8}-[0-9kK]$/", $rut)) {
            $errors['rut'] = "El RUT no tiene un formato válido. Formato esperado: 12345678-9 o 12345678-K.";
        }
    }

    if (empty($fecha_nacimiento)) {
        $errors['fecha_nacimiento'] = "El campo Fecha de Nacimiento es obligatorio.";
    } else {
        // Validar si es una fecha válida
        try {
            $fecha_nacimiento_obj = new DateTime($fecha_nacimiento);
            $fecha_nacimiento = $fecha_nacimiento_obj->format('Y-m-d');
        } catch (Exception $e) {
            $errors['fecha_nacimiento'] = "La Fecha de Nacimiento no es válida.";
        }
    }

    if (empty($edad)) {
        $errors['edad'] = "El campo Edad es obligatorio.";
    } elseif (!filter_var($edad, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 120]])) {
        $errors['edad'] = "La Edad debe ser un número entre 1 y 120.";
    }

    if (empty($escolaridad)) {
        $errors['escolaridad'] = "El campo Nivel de Escolaridad es obligatorio.";
    }

    // 2. Información Demográfica
    if (empty($region)) {
        $errors['region'] = "El campo Región es obligatorio.";
    }

    if (empty($localidad)) {
        $errors['localidad'] = "El campo Localidad es obligatorio.";
    }

    if (empty($zona)) {
        $errors['zona'] = "El campo Zona de Residencia es obligatorio.";
    }

    if (empty($sexo)) {
        $errors['sexo'] = "El campo Sexo es obligatorio.";
    }

    if (empty($diversidad)) {
        $errors['diversidad'] = "El campo Diversidad Sexual o de Género es obligatorio.";
    }

    if ($diversidad === 'si' && empty($diversidad_cual)) {
        $errors['diversidad_cual'] = "Debe especificar la Diversidad Sexual o de Género.";
    }

    if (empty($nacionalidad)) {
        $errors['nacionalidad'] = "El campo Nacionalidad es obligatorio.";
    }

    if ($nacionalidad === 'extranjero') {
        if (empty($pais_origen)) {
            $errors['pais_origen'] = "El campo País de Origen es obligatorio para Nacionalidad Extranjera.";
        }
        if (empty($situacion_migratoria)) {
            $errors['situacion_migratoria'] = "El campo Situación Migratoria es obligatorio para Nacionalidad Extranjera.";
        }
    }

    if (empty($pueblo)) {
        $errors['pueblo'] = "El campo Pertenece a un Pueblo Originario es obligatorio.";
    }

    if ($pueblo === 'si' && empty($pueblo_cual)) {
        $errors['pueblo_cual'] = "Debe especificar el Pueblo Originario.";
    }

    if (empty($convivencia)) {
        $errors['convivencia'] = "El campo Con Quiénes Vive es obligatorio.";
    }

    // 3. Información del Maltrato
    if (empty($maltrato)) {
        $errors['maltrato'] = "Debe seleccionar al menos un Tipo de Maltrato.";
    }

    if (empty($relacion_perpetrador)) {
        $errors['relacion_perpetrador'] = "El campo Relación con el Presunto Perpetrador es obligatorio.";
    }

    // 4. Información del Evaluador
    if (empty($evaluador)) {
        $errors['evaluador'] = "El campo Nombre del Evaluador es obligatorio.";
    }

    if (empty($profesion)) {
        $errors['profesion'] = "El campo Profesión es obligatorio.";
    }

    if (empty($centro)) {
        $errors['centro'] = "El campo Nombre del Centro es obligatorio.";
    }

    if (empty($fecha_evaluacion)) {
        $errors['fecha_evaluacion'] = "El campo Fecha de Evaluación es obligatorio.";
    } else {
        // Validar si es una fecha válida
        try {
            $fecha_evaluacion_obj = new DateTime($fecha_evaluacion);
            $fecha_evaluacion = $fecha_evaluacion_obj->format('Y-m-d');
        } catch (Exception $e) {
            $errors['fecha_evaluacion'] = "La Fecha de Evaluación no es válida.";
        }
    }

    // 5. Otros posibles campos obligatorios según tu modelo
    // (Añade aquí más validaciones si es necesario)

    // Si no hay errores, proceder con la inserción o actualización
    if (empty($errors)) {
        if (isset($_SESSION['inserted_id']) && $_SESSION['inserted_id'] != '') {
            // Actualizar registro existente
            $evaluacion_id = $_SESSION['inserted_id'];

            $query = "UPDATE evaluacion SET 
                        nombre = ?, 
                        rut = ?, 
                        fecha_nacimiento = ?, 
                        edad = ?, 
                        escolaridad = ?, 
                        region = ?, 
                        localidad = ?, 
                        zona = ?, 
                        sexo = ?, 
                        diversidad = ?, 
                        diversidad_cual = ?, 
                        nacionalidad = ?, 
                        pais_origen = ?, 
                        situacion_migratoria = ?, 
                        pueblo = ?, 
                        pueblo_cual = ?, 
                        convivencia = ?, 
                        maltrato = ?, 
                        otro_maltrato = ?, 
                        relacion_perpetrador = ?, 
                        otro_relacion = ?, 
                        fuente = ?, 
                        evaluador = ?, 
                        profesion = ?, 
                        centro = ?, 
                        fecha_evaluacion = ? 
                      WHERE id = ?";

            if ($stmt = $conn->prepare($query)) {
                $stmt->bind_param(
                    "sssissssssssssssssssssssssi", 
                    $nombre, 
                    $rut, 
                    $fecha_nacimiento, 
                    $edad, 
                    $escolaridad, 
                    $region, 
                    $localidad, 
                    $zona, 
                    $sexo, 
                    $diversidad, 
                    $diversidad_cual, 
                    $nacionalidad, 
                    $pais_origen, 
                    $situacion_migratoria, 
                    $pueblo, 
                    $pueblo_cual, 
                    $convivencia, 
                    $maltrato, 
                    $otro_maltrato, 
                    $relacion_perpetrador, 
                    $otro_relacion, 
                    $fuente, 
                    $evaluador, 
                    $profesion, 
                    $centro, 
                    $fecha_evaluacion, 
                    $evaluacion_id
                );

                if ($stmt->execute()) {
                    header('Location: seccion2b.php');
                    exit();
                } else {
                    $errors['general'] = "Error al actualizar el registro: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $errors['general'] = "Error de preparación de la consulta: " . $conn->error;
            }
        } else {
            // Insertar nuevo registro
            $query = "INSERT INTO evaluacion 
                        (nombre, rut, fecha_nacimiento, edad, escolaridad, region, localidad, zona, sexo, diversidad, diversidad_cual, nacionalidad, pais_origen, situacion_migratoria, pueblo, pueblo_cual, convivencia, maltrato, otro_maltrato, relacion_perpetrador, otro_relacion, fuente, evaluador, profesion, centro, fecha_evaluacion, user_id) 
                      VALUES 
                        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            if ($stmt = $conn->prepare($query)) {
                $stmt->bind_param(
                    "sssissssssssssssssssssssssi", 
                    $nombre, 
                    $rut, 
                    $fecha_nacimiento, 
                    $edad, 
                    $escolaridad, 
                    $region, 
                    $localidad, 
                    $zona, 
                    $sexo, 
                    $diversidad, 
                    $diversidad_cual, 
                    $nacionalidad, 
                    $pais_origen, 
                    $situacion_migratoria, 
                    $pueblo, 
                    $pueblo_cual, 
                    $convivencia, 
                    $maltrato, 
                    $otro_maltrato, 
                    $relacion_perpetrador, 
                    $otro_relacion, 
                    $fuente, 
                    $evaluador, 
                    $profesion, 
                    $centro, 
                    $fecha_evaluacion, 
                    $userid
                );

                if ($stmt->execute()) {
                    $inserted_id = $conn->insert_id;
                    $_SESSION['inserted_id'] = $inserted_id;
                    header('Location: seccion2b.php');
                    exit();
                } else {
                    $errors['general'] = "Error al guardar el registro: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $errors['general'] = "Error de preparación de la consulta: " . $conn->error;
            }
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Información del Niño, Niña o Adolescente</title>
    <!-- Incluir Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Estilos personalizados -->
    <style>
        .card-header {
            background-color: #d50032;
            color: #ffffff;
        }
        .required::after {
            content: " *";
            color: red;
        }
        .form-section {
            margin-bottom: 2rem;
        }
        .error-message {
            color: red;
            font-size: 0.9em;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h2 class="mb-4 text-center">Información del Niño, Niña o Adolescente</h2>
    
    <!-- Mostrar errores generales -->
    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($errors['general']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <!-- Información Personal -->
        <div class="card form-section">
            <div class="card-header">
                <h5 class="mb-0">Información Personal</h5>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <!-- Nombre -->
                    <div class="form-group col-md-6">
                        <label for="nombre" class="required">Nombre</label>
                        <input type="text" class="form-control <?php echo isset($errors['nombre']) ? 'is-invalid' : ''; ?>" id="nombre" name="nombre" value="<?php echo htmlspecialchars($_POST['nombre'] ?? $existing_data['nombre'] ?? ''); ?>" required>
                        <?php if (isset($errors['nombre'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['nombre']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- RUT -->
                    <div class="form-group col-md-6">
                        <label for="rut" class="required">RUT</label>
                        <input type="text" class="form-control <?php echo isset($errors['rut']) ? 'is-invalid' : ''; ?>" id="rut" name="rut" value="<?php echo htmlspecialchars($_POST['rut'] ?? $existing_data['rut'] ?? ''); ?>" required pattern="\d{1,8}-[0-9kK]" title="Formato: 12345678-9 o 12345678-K">
                        <?php if (isset($errors['rut'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['rut']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-row">
                    <!-- Fecha de nacimiento -->
                    <div class="form-group col-md-4">
                        <label for="fecha-nacimiento" class="required">Fecha de Nacimiento</label>
                        <input type="date" class="form-control <?php echo isset($errors['fecha_nacimiento']) ? 'is-invalid' : ''; ?>" id="fecha-nacimiento" name="fecha-nacimiento" value="<?php echo htmlspecialchars($_POST['fecha-nacimiento'] ?? $existing_data['fecha_nacimiento'] ?? ''); ?>" required>
                        <?php if (isset($errors['fecha_nacimiento'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['fecha_nacimiento']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Edad -->
                    <div class="form-group col-md-2">
                        <label for="edad" class="required">Edad</label>
                        <input type="number" class="form-control <?php echo isset($errors['edad']) ? 'is-invalid' : ''; ?>" id="edad" name="edad" value="<?php echo htmlspecialchars($_POST['edad'] ?? $existing_data['edad'] ?? ''); ?>" min="1" max="120" required>
                        <?php if (isset($errors['edad'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['edad']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Nivel de escolaridad -->
                    <div class="form-group col-md-6">
                        <label for="escolaridad" class="required">Nivel de Escolaridad</label>
                        <select class="form-control <?php echo isset($errors['escolaridad']) ? 'is-invalid' : ''; ?>" id="escolaridad" name="escolaridad" required>
                            <option value="">Seleccione nivel</option>
                            <?php
                            $niveles_escolaridad = [
                                "Prekínder", "Kínder", "Primero Básico", "Segundo Básico", "Tercero Básico",
                                "Cuarto Básico", "Quinto Básico", "Sexto Básico", "Séptimo Básico", "Octavo Básico",
                                "Primero Medio", "Segundo Medio", "Tercero Medio", "Cuarto Medio",
                                "Técnico Superior", "Universitario", "Postgrado"
                            ];
                            foreach ($niveles_escolaridad as $nivel) {
                                $selected = (($_POST['escolaridad'] ?? $existing_data['escolaridad'] ?? '') == $nivel) ? 'selected' : '';
                                echo "<option value=\"$nivel\" $selected>$nivel</option>";
                            }
                            ?>
                        </select>
                        <?php if (isset($errors['escolaridad'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['escolaridad']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información Demográfica -->
        <div class="card form-section">
            <div class="card-header">
                <h5 class="mb-0">Información Demográfica</h5>
            </div>
            <div class="card-body">

                <div class="form-row">
                    <!-- Región -->
                    <div class="form-group col-md-6">
                        <label for="region" class="required">Región</label>
                        <select class="form-control <?php echo isset($errors['region']) ? 'is-invalid' : ''; ?>" id="region" name="region" required>
                            <option value="">Seleccione una región</option>
                            <?php
                            $regiones = [
                                "Arica y Parinacota", "Tarapacá", "Antofagasta", "Atacama", "Coquimbo", "Valparaíso",
                                "Metropolitana", "O'Higgins", "Maule", "Ñuble", "Biobío", "Araucanía",
                                "Los Ríos", "Los Lagos", "Aysén", "Magallanes"
                            ];
                            foreach ($regiones as $reg) {
                                $selected = (($_POST['region'] ?? $existing_data['region'] ?? '') == $reg) ? 'selected' : '';
                                echo "<option value=\"$reg\" $selected>$reg</option>";
                            }
                            ?>
                        </select>
                        <?php if (isset($errors['region'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['region']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Localidad -->
                    <div class="form-group col-md-6">
                        <label for="localidad" class="required">Localidad</label>
                        <input type="text" class="form-control <?php echo isset($errors['localidad']) ? 'is-invalid' : ''; ?>" id="localidad" name="localidad" value="<?php echo htmlspecialchars($_POST['localidad'] ?? $existing_data['localidad'] ?? ''); ?>" required>
                        <?php if (isset($errors['localidad'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['localidad']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-row">
                    <!-- Zona -->
                    <div class="form-group col-md-4">
                        <label class="required">Zona de Residencia</label>
                        <div class="custom-control custom-radio">
                            <input type="radio" class="custom-control-input <?php echo isset($errors['zona']) ? 'is-invalid' : ''; ?>" id="urbana" name="zona" value="urbana" <?php if (($_POST['zona'] ?? $existing_data['zona'] ?? '') == 'urbana') echo 'checked'; ?> required>
                            <label class="custom-control-label" for="urbana">Urbana</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input type="radio" class="custom-control-input <?php echo isset($errors['zona']) ? 'is-invalid' : ''; ?>" id="rural" name="zona" value="rural" <?php if (($_POST['zona'] ?? $existing_data['zona'] ?? '') == 'rural') echo 'checked'; ?> required>
                            <label class="custom-control-label" for="rural">Rural</label>
                        </div>
                        <?php if (isset($errors['zona'])): ?>
                            <div class="error-message">
                                <?php echo htmlspecialchars($errors['zona']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Sexo -->
                    <div class="form-group col-md-4">
                        <label class="required">Sexo</label>
                        <div class="custom-control custom-radio">
                            <input type="radio" class="custom-control-input <?php echo isset($errors['sexo']) ? 'is-invalid' : ''; ?>" id="hombre" name="sexo" value="hombre" <?php if (($_POST['sexo'] ?? $existing_data['sexo'] ?? '') == 'hombre') echo 'checked'; ?> required>
                            <label class="custom-control-label" for="hombre">Hombre</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input type="radio" class="custom-control-input <?php echo isset($errors['sexo']) ? 'is-invalid' : ''; ?>" id="mujer" name="sexo" value="mujer" <?php if (($_POST['sexo'] ?? $existing_data['sexo'] ?? '') == 'mujer') echo 'checked'; ?> required>
                            <label class="custom-control-label" for="mujer">Mujer</label>
                        </div>
                        <?php if (isset($errors['sexo'])): ?>
                            <div class="error-message">
                                <?php echo htmlspecialchars($errors['sexo']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Diversidad sexual o de género -->
                    <div class="form-group col-md-4">
                        <label class="required">Diversidad Sexual o de Género</label>
                        <div class="custom-control custom-radio">
                            <input type="radio" class="custom-control-input <?php echo isset($errors['diversidad']) ? 'is-invalid' : ''; ?>" id="no-diversidad" name="diversidad" value="no" <?php if (($_POST['diversidad'] ?? $existing_data['diversidad'] ?? '') == 'no') echo 'checked'; ?> required>
                            <label class="custom-control-label" for="no-diversidad">No</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input type="radio" class="custom-control-input <?php echo isset($errors['diversidad']) ? 'is-invalid' : ''; ?>" id="si-diversidad" name="diversidad" value="si" <?php if (($_POST['diversidad'] ?? $existing_data['diversidad'] ?? '') == 'si') echo 'checked'; ?> required>
                            <label class="custom-control-label" for="si-diversidad">Sí</label>
                        </div>
                        <?php if (isset($errors['diversidad'])): ?>
                            <div class="error-message">
                                <?php echo htmlspecialchars($errors['diversidad']); ?>
                            </div>
                        <?php endif; ?>
                        <input type="text" class="form-control mt-2 <?php echo isset($errors['diversidad_cual']) ? 'is-invalid' : ''; ?>" id="diversidad-cual" name="diversidad-cual" placeholder="Especifique" value="<?php echo htmlspecialchars($_POST['diversidad-cual'] ?? $existing_data['diversidad_cual'] ?? ''); ?>" <?php if (($_POST['diversidad'] ?? $existing_data['diversidad'] ?? '') == 'si') echo 'required'; ?>>
                        <?php if (isset($errors['diversidad_cual'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['diversidad_cual']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-row">
                    <!-- Nacionalidad -->
                    <div class="form-group col-md-4">
                        <label class="required">Nacionalidad</label>
                        <div class="custom-control custom-radio">
                            <input type="radio" class="custom-control-input <?php echo isset($errors['nacionalidad']) ? 'is-invalid' : ''; ?>" id="chileno" name="nacionalidad" value="chileno" <?php if (($_POST['nacionalidad'] ?? $existing_data['nacionalidad'] ?? '') == 'chileno') echo 'checked'; ?> required>
                            <label class="custom-control-label" for="chileno">Chileno/a</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input type="radio" class="custom-control-input <?php echo isset($errors['nacionalidad']) ? 'is-invalid' : ''; ?>" id="extranjero" name="nacionalidad" value="extranjero" <?php if (($_POST['nacionalidad'] ?? $existing_data['nacionalidad'] ?? '') == 'extranjero') echo 'checked'; ?> required>
                            <label class="custom-control-label" for="extranjero">Extranjero/a</label>
                        </div>
                        <?php if (isset($errors['nacionalidad'])): ?>
                            <div class="error-message">
                                <?php echo htmlspecialchars($errors['nacionalidad']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- País de origen -->
                    <div class="form-group col-md-4">
                        <label for="pais-origen">País de Origen</label>
                        <select class="form-control <?php echo isset($errors['pais_origen']) ? 'is-invalid' : ''; ?>" id="pais-origen" name="pais-origen" <?php echo (($_POST['nacionalidad'] ?? $existing_data['nacionalidad'] ?? '') == 'extranjero') ? 'required' : 'disabled'; ?>>
                            <option value="">Seleccione el país de origen</option>
                            <?php
                            $paises = [
                                "Afganistán", "Albania", "Alemania", "Andorra", "Angola", "Antigua y Barbuda",
                                "Arabia Saudita", "Argelia", "Argentina", "Armenia", "Australia", "Austria",
                                "Azerbaiyán", "Bahamas", "Bangladés", "Barbados", "Baréin", "Bélgica",
                                "Belice", "Benín", "Bielorrusia", "Birmania", "Bolivia", "Bosnia y Herzegovina",
                                "Botsuana", "Brasil", "Brunéi", "Bulgaria", "Burkina Faso", "Burundi",
                                "Bután", "Cabo Verde", "Camboya", "Camerún", "Canadá", "Catar", "Chad",
                                "Chequia", "Chile", "China", "Chipre", "Ciudad del Vaticano", "Colombia",
                                "Comoras", "Corea del Norte", "Corea del Sur", "Costa de Marfil",
                                "Costa Rica", "Croacia", "Cuba", "Dinamarca", "Dominica", "Ecuador",
                                "Egipto", "El Salvador", "Emiratos Árabes Unidos", "Eritrea", "Eslovaquia",
                                "Eslovenia", "España", "Estados Unidos", "Estonia", "Esuatini", "Etiopía",
                                "Filipinas", "Finlandia", "Fiyi", "Francia", "Gabón", "Gambia", "Georgia",
                                "Ghana", "Granada", "Grecia", "Guatemala", "Guyana", "Guinea",
                                "Guinea-Bisáu", "Guinea Ecuatorial", "Haití", "Honduras", "Hungría", "India",
                                "Indonesia", "Irak", "Irán", "Irlanda", "Islandia", "Islas Marshall",
                                "Islas Salomón", "Israel", "Italia", "Jamaica", "Japón", "Jordania",
                                "Kazajistán", "Kenia", "Kirguistán", "Kiribati", "Kuwait", "Laos",
                                "Lesoto", "Letonia", "Líbano", "Liberia", "Libia", "Liechtenstein",
                                "Lituania", "Luxemburgo", "Madagascar", "Malasia", "Malaui", "Maldivas",
                                "Malí", "Malta", "Marruecos", "Mauricio", "Mauritania", "México",
                                "Micronesia", "Moldavia", "Mónaco", "Mongolia", "Montenegro", "Mozambique",
                                "Namibia", "Nauru", "Nepal", "Nicaragua", "Níger", "Nigeria", "Noruega",
                                "Nueva Zelanda", "Omán", "Países Bajos", "Pakistán", "Palaos", "Panamá",
                                "Papúa Nueva Guinea", "Paraguay", "Perú", "Polonia", "Portugal",
                                "Reino Unido", "República Centroafricana", "República Checa", "República del Congo",
                                "República Democrática del Congo", "República Dominicana", "Ruanda",
                                "Rumania", "Rusia", "Samoa", "San Cristóbal y Nieves", "San Marino",
                                "San Vicente y las Granadinas", "Santa Lucía", "Santo Tomé y Príncipe",
                                "Senegal", "Serbia", "Seychelles", "Sierra Leona", "Singapur", "Siria",
                                "Somalia", "Sri Lanka", "Sudáfrica", "Sudán", "Sudán del Sur", "Suecia",
                                "Suiza", "Surinam", "Tailandia", "Tanzania", "Tayikistán", "Timor Oriental",
                                "Togo", "Tonga", "Trinidad y Tobago", "Túnez", "Turkmenistán", "Turquía",
                                "Tuvalu", "Ucrania", "Uganda", "Uruguay", "Uzbekistán", "Vanuatu",
                                "Venezuela", "Vietnam", "Yemen", "Yibuti", "Zambia", "Zimbabue"
                            ];
                            foreach ($paises as $pais) {
                                $selected = (($_POST['pais-origen'] ?? $existing_data['pais_origen'] ?? '') == $pais) ? 'selected' : '';
                                echo "<option value=\"" . htmlspecialchars($pais) . "\" $selected>" . htmlspecialchars($pais) . "</option>";
                            }
                            ?>
                        </select>
                        <?php if (isset($errors['pais_origen'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['pais_origen']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Situación migratoria -->
                    <div class="form-group col-md-4">
                        <label for="situacion-migratoria">Situación Migratoria</label>
                        <select class="form-control <?php echo isset($errors['situacion_migratoria']) ? 'is-invalid' : ''; ?>" id="situacion-migratoria" name="situacion-migratoria" <?php echo (($_POST['nacionalidad'] ?? $existing_data['nacionalidad'] ?? '') == 'extranjero') ? 'required' : 'disabled'; ?>>
                            <option value="">Seleccione su situación migratoria</option>
                            <?php
                            $situaciones = [
                                "Residente permanente", "Residente temporal", "Visa de trabajo", "Visa de estudiante",
                                "Asilado o refugiado", "En proceso de regularización", "Indocumentado", "Turista", "Otro"
                            ];

                            foreach ($situaciones as $situacion) {
                                $selected = (($_POST['situacion-migratoria'] ?? $existing_data['situacion_migratoria'] ?? '') == $situacion) ? 'selected' : '';
                                echo "<option value=\"" . htmlspecialchars($situacion) . "\" $selected>" . htmlspecialchars($situacion) . "</option>";
                            }
                            ?>
                        </select>
                        <?php if (isset($errors['situacion_migratoria'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['situacion_migratoria']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-row">
                    <!-- Pueblo originario -->
                    <div class="form-group col-md-6">
                        <label class="required">¿Pertenece a un Pueblo Originario?</label>
                        <div class="custom-control custom-radio">
                            <input type="radio" class="custom-control-input <?php echo isset($errors['pueblo']) ? 'is-invalid' : ''; ?>" id="no-pueblo" name="pueblo" value="no" <?php if (($_POST['pueblo'] ?? $existing_data['pueblo'] ?? '') == 'no') echo 'checked'; ?> required>
                            <label class="custom-control-label" for="no-pueblo">No</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input type="radio" class="custom-control-input <?php echo isset($errors['pueblo']) ? 'is-invalid' : ''; ?>" id="si-pueblo" name="pueblo" value="si" <?php if (($_POST['pueblo'] ?? $existing_data['pueblo'] ?? '') == 'si') echo 'checked'; ?> required>
                            <label class="custom-control-label" for="si-pueblo">Sí</label>
                        </div>
                        <?php if (isset($errors['pueblo'])): ?>
                            <div class="error-message">
                                <?php echo htmlspecialchars($errors['pueblo']); ?>
                            </div>
                        <?php endif; ?>
                        <select class="form-control mt-2 <?php echo isset($errors['pueblo_cual']) ? 'is-invalid' : ''; ?>" id="pueblo-cual" name="pueblo-cual" <?php echo (($_POST['pueblo'] ?? $existing_data['pueblo'] ?? '') == 'si') ? 'required' : 'disabled'; ?>>
                            <option value="">Seleccione el pueblo originario</option>
                            <?php
                            $pueblos = [
                                "Aymara", "Diaguita", "Kawésqar", "Lican Antai (Atacameño)", "Mapuche",
                                "Quechua", "Rapa Nui", "Yagán", "Otro"
                            ];

                            foreach ($pueblos as $pueblo_opt) {
                                $selected = (($_POST['pueblo-cual'] ?? $existing_data['pueblo_cual'] ?? '') == $pueblo_opt) ? 'selected' : '';
                                echo "<option value=\"" . htmlspecialchars($pueblo_opt) . "\" $selected>" . htmlspecialchars($pueblo_opt) . "</option>";
                            }
                            ?>
                        </select>
                        <?php if (isset($errors['pueblo_cual'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['pueblo_cual']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Con quiénes vive -->
                    <div class="form-group col-md-6">
                        <label class="required">Con Quiénes Vive</label>
                        <?php
                        $convivencia_options = ['madre', 'padre', 'otros'];
                        $convivencia_selected = explode(',', $_POST['convivencia'] ?? $existing_data['convivencia'] ?? '');
                        foreach ($convivencia_options as $option) {
                            $checked = in_array($option, $convivencia_selected) ? 'checked' : '';
                            echo '<div class="custom-control custom-checkbox">';
                            echo "<input type=\"checkbox\" class=\"custom-control-input\" id=\"convivencia-$option\" name=\"convivencia[]\" value=\"$option\" $checked>";
                            echo "<label class=\"custom-control-label\" for=\"convivencia-$option\">" . ucfirst($option) . "</label>";
                            echo '</div>';
                        }
                        ?>
                        <?php if (isset($errors['convivencia'])): ?>
                            <div class="error-message">
                                <?php echo htmlspecialchars($errors['convivencia']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información del Maltrato -->
        <div class="card form-section">
            <div class="card-header">
                <h5 class="mb-0">Información del Maltrato</h5>
            </div>
            <div class="card-body">
                <!-- Tipo de maltrato -->
                <div class="form-group">
                    <label class="required">Tipo de Maltrato del Ingreso Actual</label>
                    <?php
                    $maltrato_options = ['fisico', 'psicologico', 'negligencia', 'abuso'];
                    $maltrato_selected = explode(',', $_POST['maltrato'] ?? $existing_data['maltrato'] ?? '');
                    foreach ($maltrato_options as $option) {
                        $checked = in_array($option, $maltrato_selected) ? 'checked' : '';
                        echo '<div class="custom-control custom-checkbox">';
                        echo "<input type=\"checkbox\" class=\"custom-control-input\" id=\"maltrato-$option\" name=\"maltrato[]\" value=\"$option\" $checked>";
                        echo "<label class=\"custom-control-label\" for=\"maltrato-$option\">Maltrato " . ucfirst($option) . "</label>";
                        echo '</div>';
                    }
                    ?>
                    <?php if (isset($errors['maltrato'])): ?>
                        <div class="error-message">
                            <?php echo htmlspecialchars($errors['maltrato']); ?>
                        </div>
                    <?php endif; ?>
                    <input type="text" class="form-control mt-2 <?php echo isset($errors['otro_maltrato']) ? 'is-invalid' : ''; ?>" id="otro-maltrato" name="otro-maltrato" placeholder="Otro tipo de maltrato (especificar)" value="<?php echo htmlspecialchars($_POST['otro-maltrato'] ?? $existing_data['otro_maltrato'] ?? ''); ?>">
                    <?php if (isset($errors['otro_maltrato'])): ?>
                        <div class="invalid-feedback">
                            <?php echo htmlspecialchars($errors['otro_maltrato']); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Relación con el presunto perpetrador -->
                <div class="form-group">
                    <label for="relacion-perpetrador" class="required">Relación con el Presunto Perpetrador</label>
                    <select class="form-control <?php echo isset($errors['relacion_perpetrador']) ? 'is-invalid' : ''; ?>" id="relacion-perpetrador" name="relacion-perpetrador" required>
                        <option value="" disabled <?php if (empty($_POST['relacion-perpetrador'] ?? $existing_data['relacion_perpetrador'] ?? '')) echo 'selected'; ?>>Seleccione una opción</option>
                        <?php
                        $relaciones = ['padre', 'madre', 'hermano/a', 'otro-familiar', 'conocido', 'desconocido', 'otro'];
                        foreach ($relaciones as $relacion) {
                            $selected = (($_POST['relacion-perpetrador'] ?? $existing_data['relacion_perpetrador'] ?? '') == $relacion) ? 'selected' : '';
                            echo "<option value=\"" . htmlspecialchars($relacion) . "\" $selected>" . ucfirst($relacion) . "</option>";
                        }
                        ?>
                    </select>
                    <?php if (isset($errors['relacion_perpetrador'])): ?>
                        <div class="invalid-feedback">
                            <?php echo htmlspecialchars($errors['relacion_perpetrador']); ?>
                        </div>
                    <?php endif; ?>
                    <input type="text" class="form-control mt-2 <?php echo isset($errors['otro_relacion']) ? 'is-invalid' : ''; ?>" id="otro-relacion" name="otro-relacion" placeholder="Especifique otra relación" value="<?php echo htmlspecialchars($_POST['otro-relacion'] ?? $existing_data['otro_relacion'] ?? ''); ?>" <?php echo (($_POST['relacion-perpetrador'] ?? $existing_data['relacion_perpetrador'] ?? '') == 'otro') ? 'required' : 'disabled'; ?>>
                    <?php if (isset($errors['otro_relacion'])): ?>
                        <div class="invalid-feedback">
                            <?php echo htmlspecialchars($errors['otro_relacion']); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Fuente de derivación -->
                <div class="form-group">
                    <label class="required">Fuente de Derivación</label>
                    <?php
                    $fuente_options = ['tribunales', 'oln'];
                    $fuente_selected = explode(',', $_POST['fuente'] ?? $existing_data['fuente'] ?? '');
                    foreach ($fuente_options as $option) {
                        $checked = in_array($option, $fuente_selected) ? 'checked' : '';
                        echo '<div class="custom-control custom-checkbox">';
                        echo "<input type=\"checkbox\" class=\"custom-control-input\" id=\"fuente-$option\" name=\"fuente[]\" value=\"$option\" $checked >";
                        echo "<label class=\"custom-control-label\" for=\"fuente-$option\">" . ucfirst($option) . "</label>";
                        echo '</div>';
                    }
                    ?>
                    <?php if (isset($errors['fuente'])): ?>
                        <div class="error-message">
                            <?php echo htmlspecialchars($errors['fuente']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Información del Evaluador -->
        <div class="card form-section">
            <div class="card-header">
                <h5 class="mb-0">Información del Evaluador</h5>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <!-- Nombre del evaluador -->
                    <div class="form-group col-md-6">
                        <label for="evaluador" class="required">Nombre del Evaluador</label>
                        <input type="text" class="form-control <?php echo isset($errors['evaluador']) ? 'is-invalid' : ''; ?>" id="evaluador" name="evaluador" value="<?php echo htmlspecialchars($_POST['evaluador'] ?? $existing_data['evaluador'] ?? ($_SESSION['name'] ?? '')); ?>" required>
                        <?php if (isset($errors['evaluador'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['evaluador']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Profesión -->
                    <div class="form-group col-md-6">
                        <label class="required">Profesión</label>
                        <?php
                        $profesiones = ['psicologia' => 'Psicología', 'trabajo-social' => 'Trabajo Social'];
                        $profesion_selected = $_POST['profesion'] ?? $existing_data['profesion'] ?? '';
                        foreach ($profesiones as $value => $label) {
                            $checked = ($profesion_selected == $value) ? 'checked' : '';
                            echo '<div class="custom-control custom-radio">';
                            echo "<input type=\"radio\" class=\"custom-control-input\" id=\"$value\" name=\"profesion\" value=\"$value\" $checked required>";
                            echo "<label class=\"custom-control-label\" for=\"$value\">$label</label>";
                            echo '</div>';
                        }
                        ?>
                        <?php if (isset($errors['profesion'])): ?>
                            <div class="error-message">
                                <?php echo htmlspecialchars($errors['profesion']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Nombre del centro -->
                <div class="form-group">
                    <label for="centro" class="required">Nombre del Centro</label>
                    <input type="text" class="form-control <?php echo isset($errors['centro']) ? 'is-invalid' : ''; ?>" id="centro" name="centro" value="<?php echo htmlspecialchars($_POST['centro'] ?? $existing_data['centro'] ?? ''); ?>" required>
                    <?php if (isset($errors['centro'])): ?>
                        <div class="invalid-feedback">
                            <?php echo htmlspecialchars($errors['centro']); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Fecha de evaluación -->
                <div class="form-group">
                    <label for="fecha-evaluacion" class="required">Fecha de Evaluación</label>
                    <input type="date" class="form-control <?php echo isset($errors['fecha_evaluacion']) ? 'is-invalid' : ''; ?>" id="fecha-evaluacion" name="fecha-evaluacion" value="<?php echo htmlspecialchars($_POST['fecha-evaluacion'] ?? $existing_data['fecha_evaluacion'] ?? ''); ?>" required>
                    <?php if (isset($errors['fecha_evaluacion'])): ?>
                        <div class="invalid-feedback">
                            <?php echo htmlspecialchars($errors['fecha_evaluacion']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="form-group text-right">
            <button type="submit" class="btn btn-primary">Guardar y Continuar</button>
            <?php 
            if(isset($_SESSION['inserted_id']) && $_SESSION['inserted_id'] != ''){
                echo '<a href="resumenb.php" class="btn btn-secondary">Ir al Resumen</a>';
            }
            ?>
        </div>
    </form>
</div>

<!-- Scripts de Bootstrap y dependencias -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
    // Mostrar/ocultar campos según selección
    $(document).ready(function() {
        // Diversidad sexual o de género
        $('input[name="diversidad"]').change(function() {
            if ($(this).val() === 'si') {
               $('#diversidad-cual').prop('disabled', false).prop('required', true);
            } else {
                $('#diversidad-cual').prop('disabled', true).prop('required', false).val('');
            }
        }).trigger('change');

        // Pertenece a pueblo originario
        $('input[name="pueblo"]').change(function() {
            if ($(this).val() === 'si') {
                $('#pueblo-cual').prop('disabled', false).prop('required', true);
            } else {
                $('#pueblo-cual').prop('disabled', true).prop('required', false).val('');
            }
        }).trigger('change');

        // Nacionalidad
        $('input[name="nacionalidad"]').change(function() {
            if ($(this).val() === 'extranjero') {
                $('#pais-origen, #situacion-migratoria').prop('disabled', false).prop('required', true);
            } else {
                $('#pais-origen, #situacion-migratoria').prop('disabled', true).prop('required', false).val('');
            }
        }).trigger('change');

        // Relación con el presunto perpetrador
        $('#relacion-perpetrador').change(function() {
            if ($(this).val() === 'otro') {
               $('#otro-relacion').prop('disabled', false).prop('required', true);
            } else {
                $('#otro-relacion').prop('disabled', true).prop('required', false).val('');
            }
        }).trigger('change');
    });
</script>
<!-- Bootstrap JS Bundle (incluye Popper.js) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

