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

// Delete user
if (isset($_POST['delete'])) {
    $userid = $_POST['userid'];
    // Remove related assessments and unlink evaluations
    sqlsrv_query($conn, "DELETE FROM assessments WHERE userid=?", array($userid));
    sqlsrv_query($conn, "UPDATE evaluacion SET user_id=NULL WHERE user_id=?", array($userid));
    sqlsrv_query($conn, "DELETE FROM users WHERE userid=?", array($userid));
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
    <button class="btn btn-success mb-3" data-toggle="modal" data-target="#createUserModal">Agregar Usuario</button>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Assessments</th>
                <th>Completos</th>
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
                <td><?= $row['total'] ?></td>
                <td><?= $row['completed'] ?></td>
                <td>
                    <button type="button" class="btn btn-primary btn-sm edit-btn" data-toggle="modal" data-target="#editUserModal"
                        data-userid="<?= $row['userid'] ?>" data-name="<?= htmlspecialchars($row['name']) ?>"
                        data-last_name="<?= htmlspecialchars($row['last_name']) ?>" data-email="<?= htmlspecialchars($row['email']) ?>"
                        data-role="<?= $row['role'] ?>">Editar</button>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('¿Está seguro de que desea eliminar este usuario y todas sus evaluaciones?');">
                        <input type="hidden" name="userid" value="<?= $row['userid'] ?>" />
                        <button type="submit" name="delete" class="btn btn-danger btn-sm">Eliminar</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Modal Crear Usuario -->
<div class="modal fade" id="createUserModal" tabindex="-1" role="dialog" aria-labelledby="createUserModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="POST">
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

<!-- Modal Editar Usuario -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="POST">
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
