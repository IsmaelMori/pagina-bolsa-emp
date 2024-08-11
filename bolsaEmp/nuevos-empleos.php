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
    <title>Nuevos Empleos - MiApp</title>
    <link rel="stylesheet" href="css/pg.css">
</head>
<body>
<nav class="navbar">
<?php if (!empty($nombre)) { ?>
        <h2>Bienvenido, <?php echo $nombre; ?>!</h2>
    <?php } ?>
    <ul>
        <li><a href="jobs.php">Inicio</a></li>
        <li><a href="nuevos-empleos.php">Nuevos Empleos</a></li>
        <li><a href="empresas.php">Empresas</a></li>
        <li><a href="logout.php">Cerrar Sesión</a></li>
    </ul>
   
</nav>

    <div class="jobs-container">
        <h1>Nuevos Empleos</h1>
        <section id="nuevos-empleos">
            <h2>Publicar Nuevo Empleo</h2>
            <?php
        
            include 'config.php';

            if (isset($_SESSION['user_id'])) {
                $user_id = $_SESSION['user_id'];
                $sql = "SELECT role FROM users WHERE id = $user_id";
                $result = $conn->query($sql);
                $row = $result->fetch_assoc();
                
                if ($row['role'] == 'empleador') {
                    echo '<form action="subir-empleo.php" method="post" class="form-container">';
                    echo '<input type="text" name="titulo" placeholder="Título del Empleo" required>';
                    echo '<textarea name="descripcion" placeholder="Descripción del Empleo" required></textarea>';
                    echo '<input type="text" name="empresa" placeholder="Nombre de la Empresa" required>';
                    echo '<input type="text" name="ubicacion" placeholder="Ubicación" required>';
                    echo '<button type="submit">Publicar Empleo</button>';
                    echo '</form>';
                } else {
                    echo '<p>Solo los empleadores pueden publicar nuevos empleos.</p>';
                }
            } else {
                echo '<p>Debe iniciar sesión para publicar nuevos empleos.</p>';
            }
            ?>
        </section>
        <section id="lista-empleos">
            <h2>Lista de Empleos</h2>
            <!-- Aquí se mostrarán los empleos publicados -->
        </section>
    </div>
</body>
</html>
