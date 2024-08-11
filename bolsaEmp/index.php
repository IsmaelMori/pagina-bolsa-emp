<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirigir al usuario al inicio de sesión si no ha iniciado sesión
    exit;
}

include 'config.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $nombre = $row['nombre'];
    $apellido = $row['apellido'];
    $email = $row['email'];
} else {
    $error = "Error: Usuario no encontrado en la base de datos.";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/mm.css">
    <title>Página Principal</title>
    <script>
        // Redirigir automáticamente después de 5 segundos
        setTimeout(function(){
            window.location.href = 'empresas.php';
        }, 3000);
    </script>
</head>
<body>
    <div class="container">
        <h2>Bienvenido, <?php echo $nombre; ?>!</h2>
        <p>Correo electrónico: <?php echo $email; ?></p>
        <p>Rol: <?php echo $role; ?></p>
        <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
        <p><a href="logout.php">Cerrar Sesión</a></p>
        <p class="message">Serás redirigido a la bolsa de empleos en 3 segundos...</p>
    </div>
</body>
</html>
