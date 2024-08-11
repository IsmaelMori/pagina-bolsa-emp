<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redirigir si el usuario ya está conectado
}

include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $cedula = $_POST['cedula'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role']; // Obtener el rol del formulario

    $sql = "INSERT INTO users (nombre, apellido, cedula, email, password, role) VALUES ('$nombre', '$apellido', '$cedula', '$email', '$password', '$role')";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['user_id'] = $conn->insert_id; // Obtener el ID del usuario recién registrado
        $_SESSION['role'] = $role;
        header("Location: index.php"); // Redirigir al usuario al área protegida
    } else {
        $error = "Error al registrar usuario: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/rg.css">
    <title>Registro</title>
</head>
<body>
<div class="buttons-container">
        <button onclick="location.href='formulario_empleado.php'">Registrarse como Empleado</button>
        <button onclick="location.href='formulario_empleador.php'">Registrarse como Empleador</button>
    </div>
    <div>
    <p>¿Ya tienes una cuenta? <a href="login.php">regresar</a>.</p>
    </div>
</body>
</html>
