<? include_once("config.php"); ?>
<?php

// Generar el hash de la contraseña
$password = password_hash("holahola", PASSWORD_DEFAULT);

// Insertar el usuario admin
$sql = "INSERT INTO users (email, password, name) VALUES ('admin@example.com', '$password', 'Admin')";
if ($conn->query($sql) === TRUE) {
    echo "Usuario admin insertado exitosamente";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Cerrar conexión
$conn->close();
?>