<?php
include_once("config.php");
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'supervisor'], true)) {
    header('Location: login.php');
    exit();
}

$allowedRoles = ['user', 'admin', 'supervisor'];

$bulkMessages = [
    'success' => [],
    'error' => [],
];
$bulkPreviousInput = '';
if (isset($_SESSION['flash_messages']['bulk'])) {
    $bulkMessages = array_merge(
        $bulkMessages,
        array_intersect_key(
            $_SESSION['flash_messages']['bulk'],
            $bulkMessages
        )
    );
    unset($_SESSION['flash_messages']['bulk']);
    if (empty($_SESSION['flash_messages'])) {
        unset($_SESSION['flash_messages']);
    }
}
if (isset($_SESSION['bulk_previous_input'])) {
    $bulkPreviousInput = $_SESSION['bulk_previous_input'];
    unset($_SESSION['bulk_previous_input']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }
}

if (isset($_POST['bulk_create'])) {
    $bulkData = $_POST['bulk_data'] ?? '';
    $lines = preg_split('/\r\n|\r|\n/', $bulkData);
    $validEntries = [];
    $errors = [];
    $successes = [];
    $seenEmails = [];

    $lineNumber = 0;
    foreach ($lines as $line) {
        $lineNumber++;
        $trimmedLine = trim($line);
        if ($trimmedLine === '') {
            continue;
        }

        $parts = array_map('trim', explode(';', $trimmedLine));
        if (count($parts) !== 5) {
            $errors[] = sprintf(
                'Línea %d: formato inválido. Usa Nombre;Apellido;correo@dominio;rol;clave.',
                $lineNumber
            );
            continue;
        }

        [$name, $lastName, $email, $role, $password] = $parts;

        if ($name === '' || $lastName === '' || $password === '') {
            $errors[] = sprintf(
                'Línea %d: nombre, apellido y clave son obligatorios.',
                $lineNumber
            );
            continue;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = sprintf(
                'Línea %d: el correo "%s" no es válido.',
                $lineNumber,
                $email
            );
            continue;
        }

        $normalizedEmail = function_exists('mb_strtolower') ? mb_strtolower($email) : strtolower($email);
        if (isset($seenEmails[$normalizedEmail])) {
            $errors[] = sprintf(
                'Línea %d: el correo "%s" está duplicado en la carga.',
                $lineNumber,
                $email
            );
            continue;
        }

        if (!in_array($role, $allowedRoles, true)) {
            $errors[] = sprintf(
                'Línea %d: el rol "%s" no es válido. Roles permitidos: %s.',
                $lineNumber,
                $role,
                implode(', ', $allowedRoles)
            );
            continue;
        }

        $seenEmails[$normalizedEmail] = true;
        $validEntries[] = [
            'line' => $lineNumber,
            'name' => $name,
            'last_name' => $lastName,
            'email' => $email,
            'role' => $role,
            'password' => $password,
        ];
    }

    if (!empty($validEntries)) {
        if (!sqlsrv_begin_transaction($conn)) {
            $errors[] = 'No se pudo iniciar la transacción para crear usuarios.';
        } else {
            $insertSql = 'INSERT INTO users (name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)';
            $createdCount = 0;

            foreach ($validEntries as $entry) {
                $hashedPassword = password_hash($entry['password'], PASSWORD_BCRYPT);
                $params = [
                    $entry['name'],
                    $entry['last_name'],
                    $entry['email'],
                    $hashedPassword,
                    $entry['role'],
                ];

                $result = sqlsrv_query($conn, $insertSql, $params);
                if ($result === false) {
                    $sqlsrvErrors = sqlsrv_errors();
                    $duplicateError = false;
                    $detailedMessage = '';
                    if ($sqlsrvErrors) {
                        foreach ($sqlsrvErrors as $sqlsrvError) {
                            $detailedMessage = $sqlsrvError['message'] ?? '';
                            if (in_array($sqlsrvError['code'], [2601, 2627], true)) {
                                $duplicateError = true;
                                break;
                            }
                        }
                    }

                    if ($duplicateError) {
                        $errors[] = sprintf(
                            'Línea %d: el correo "%s" ya existe y se omitió.',
                            $entry['line'],
                            $entry['email']
                        );
                    } else {
                        $errors[] = sprintf(
                            'Línea %d: no se pudo crear el usuario "%s". %s',
                            $entry['line'],
                            $entry['email'],
                            $detailedMessage
                        );
                    }
                    continue;
                }

                $successes[] = sprintf(
                    'Línea %d: usuario "%s" creado correctamente.',
                    $entry['line'],
                    $entry['email']
                );
                $createdCount++;
            }

            if (!sqlsrv_commit($conn)) {
                $commitErrors = sqlsrv_errors();
                $errors[] = 'No se pudo confirmar la transacción. No se realizaron cambios.';
                if ($commitErrors) {
                    foreach ($commitErrors as $commitError) {
                        if (!empty($commitError['message'])) {
                            $errors[] = $commitError['message'];
                        }
                    }
                }
                sqlsrv_rollback($conn);
                $successes = [];
                $createdCount = 0;
            } else {
                $successes[] = sprintf(
                    'Se crearon %d usuario(s) correctamente.',
                    $createdCount
                );
            }
        }
    } else {
        $errors[] = 'No se encontraron entradas válidas para procesar.';
    }

    if (!empty($errors)) {
        $_SESSION['bulk_previous_input'] = $bulkData;
    }

    $_SESSION['flash_messages']['bulk'] = [
        'success' => $successes,
        'error' => $errors,
    ];

    header('Location: user_management.php');
    exit();
}

// Create user
if (isset($_POST['create'])) {
    $name = $_POST['name'];
    $lastname = $_POST['last_name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    if (!in_array($role, $allowedRoles, true)) {
        die('Rol no válido');
    }
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
    if (!in_array($role, $allowedRoles, true)) {
        die('Rol no válido');
    }

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

// Delete user
if (isset($_POST['delete'])) {
    $userid = $_POST['userid'];
    // Remove related assessments and unlink evaluations
    sqlsrv_query($conn, "DELETE FROM assessments WHERE userid=?", array($userid));
    sqlsrv_query($conn, "UPDATE evaluacion SET user_id=NULL WHERE user_id=?", array($userid));
    sqlsrv_query($conn, "DELETE FROM users WHERE userid=?", array($userid));
}

// Fetch users
$query = "SELECT userid, name, last_name, email, role FROM users";

$stmt = sqlsrv_query($conn, $query);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
include_once("header.php");
?>
<div class="container mt-4">
    <h2>Gestión de Usuarios</h2>
    <div class="mb-3">
        <button class="btn btn-success mr-2" data-toggle="modal" data-target="#createUserModal">Agregar Usuario</button>
        <button class="btn btn-outline-secondary" data-toggle="modal" data-target="#bulkCreateModal">Carga masiva</button>
    </div>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
            <tr>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['last_name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= $row['role'] ?></td>
                <td>
                    <button type="button" class="btn btn-primary btn-sm edit-btn" data-toggle="modal" data-target="#editUserModal"
                        data-userid="<?= $row['userid'] ?>" data-name="<?= htmlspecialchars($row['name']) ?>"
                        data-last_name="<?= htmlspecialchars($row['last_name']) ?>" data-email="<?= htmlspecialchars($row['email']) ?>"
                        data-role="<?= $row['role'] ?>">Editar</button>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('¿Está seguro de que desea eliminar este usuario y todas sus evaluaciones?');">
                        <?php csrf_input(); ?>
                        <input type="hidden" name="userid" value="<?= $row['userid'] ?>" />
                        <button type="submit" name="delete" class="btn btn-danger btn-sm">Eliminar</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Modal Carga Masiva -->
<div class="modal fade" id="bulkCreateModal" tabindex="-1" role="dialog" aria-labelledby="bulkCreateModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <form method="POST">
        <?php csrf_input(); ?>
        <div class="modal-header">
          <h5 class="modal-title" id="bulkCreateModalLabel">Carga masiva de usuarios</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p class="mb-2">Ingresa un usuario por línea con el formato:</p>
          <pre class="bg-light p-2">Nombre;Apellido;correo@dominio;rol;clave</pre>
          <p class="text-muted">Roles permitidos: <?= htmlspecialchars(implode(', ', $allowedRoles)) ?></p>
          <?php if (!empty($bulkMessages['success']) || !empty($bulkMessages['error'])): ?>
          <div class="mb-3">
            <?php if (!empty($bulkMessages['success'])): ?>
            <div class="alert alert-success" role="alert">
              <ul class="mb-0">
                <?php foreach ($bulkMessages['success'] as $message): ?>
                <li><?= htmlspecialchars($message) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <?php endif; ?>
            <?php if (!empty($bulkMessages['error'])): ?>
            <div class="alert alert-danger" role="alert">
              <ul class="mb-0">
                <?php foreach ($bulkMessages['error'] as $message): ?>
                <li><?= htmlspecialchars($message) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <?php endif; ?>
          </div>
          <?php endif; ?>
          <div class="form-group">
            <label for="bulkDataTextarea">Usuarios (uno por línea)</label>
            <textarea name="bulk_data" id="bulkDataTextarea" class="form-control" rows="8" placeholder="Juan;Pérez;juan@example.com;user;ClaveSegura123" required><?= htmlspecialchars($bulkPreviousInput) ?></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="bulk_create" class="btn btn-primary">Procesar carga</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Crear Usuario -->
<div class="modal fade" id="createUserModal" tabindex="-1" role="dialog" aria-labelledby="createUserModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="POST">
        <?php csrf_input(); ?>
        <div class="modal-header">
          <h5 class="modal-title" id="createUserModalLabel">Agregar Usuario</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Apellido</label>
            <input type="text" name="last_name" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Clave</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Rol</label>
            <select name="role" class="form-control">
              <option value="user">user</option>
              <option value="admin">admin</option>
              <option value="supervisor">supervisor</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="create" class="btn btn-success">Crear</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php if (!empty($bulkMessages['success']) || !empty($bulkMessages['error'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#bulkCreateModal').modal('show');
});
</script>
<?php endif; ?>

<!-- Modal Editar Usuario -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="POST">
        <?php csrf_input(); ?>
        <div class="modal-header">
          <h5 class="modal-title" id="editUserModalLabel">Editar Usuario</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="name" id="editName" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Apellido</label>
            <input type="text" name="last_name" id="editLastName" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" id="editEmail" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Clave</label>
            <input type="password" name="password" id="editPassword" class="form-control" placeholder="Nueva clave">
          </div>
          <div class="form-group">
            <label>Rol</label>
            <select name="role" id="editRole" class="form-control">
              <option value="user">user</option>
              <option value="admin">admin</option>
              <option value="supervisor">supervisor</option>
            </select>
          </div>
          <input type="hidden" name="userid" id="editUserId">
        </div>
        <div class="modal-footer">
          <button type="submit" name="update" class="btn btn-primary">Guardar</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    $('.edit-btn').on('click', function() {
        var button = $(this);
        $('#editUserId').val(button.data('userid'));
        $('#editName').val(button.data('name'));
        $('#editLastName').val(button.data('last_name'));
        $('#editEmail').val(button.data('email'));
        $('#editRole').val(button.data('role'));
        $('#editPassword').val('');
    });
});
</script>

<?php sqlsrv_free_stmt($stmt); ?>
</body>
</html>
