<?php
session_start();
include 'config.php';

// Verificar si el usuario está autenticado
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT nombre FROM users WHERE id = $user_id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $nombre_usuario = $row['nombre'];
} else {
    header("Location: login.php");
    exit;
}

// Paginación
$limit = 20; // 4 filas x 5 columnas
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Obtener el total de empleados
$total_empleados_sql = "SELECT COUNT(*) as total FROM users WHERE role = 'empleado'";
$total_empleados_result = $conn->query($total_empleados_sql);
$total_empleados_row = $total_empleados_result->fetch_assoc();
$total_empleados = $total_empleados_row['total'];
$total_empleados_pages = ceil($total_empleados / $limit);

// Obtener el total de empleadores
$total_empleadores_sql = "SELECT COUNT(*) as total FROM users WHERE role = 'empleador'";
$total_empleadores_result = $conn->query($total_empleadores_sql);
$total_empleadores_row = $total_empleadores_result->fetch_assoc();
$total_empleadores = $total_empleadores_row['total'];
$total_empleadores_pages = ceil($total_empleadores / $limit);

// Obtener empleados para la página actual
$empleados_sql = "SELECT id, nombre, foto_perfil FROM users WHERE role = 'empleado' ORDER BY nombre ASC LIMIT $start, $limit";
$empleados_result = $conn->query($empleados_sql);

// Obtener empleadores para la página actual
$empleadores_sql = "SELECT id, nombre, foto_perfil FROM users WHERE role = 'empleador' ORDER BY nombre ASC LIMIT $start, $limit";
$empleadores_result = $conn->query($empleadores_sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - MiApp</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #fff;
            color: #ffffff;
            font-family: Arial, sans-serif;
            margin: 0;
            background-image: url('img/fondo4.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #fff;
            padding: 10px;
            width: 100%;
            position: fixed;
            top: 0;
            z-index: 1000;
            transition: top 0.3s, box-shadow 0.3s;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .navbar.hidden {
            top: -60px;
        }

        .navbar .logo {
            color: rgb(189, 1, 2);
            font-size: 24px;
            text-decoration: none;
            margin-right: 15px;
        }

        .navbar .nav-links {
            list-style: none;
            display: flex;
            align-items: center;
            color: rgb(189, 1, 2);
        }

        .navbar .nav-links li {
            margin: 0 15px;
        }

        .navbar .nav-links a {
            color: rgb(189, 1, 2);
            text-decoration: none;
            font-size: 18px;
            display: flex;
            align-items: center;
        }

        .navbar .nav-links .fa {
            margin-right: 5px;
        }

        .welcome h3 {
            color: rgb(189, 1, 2);
        }

        /* SEPARACIÓN ADICIONAL DEL CONTENIDO DESDE EL NAVBAR */
        .content {
            margin-top: 90px; /* Ajusta este valor según la altura del navbar */
            padding-bottom: 60px; /* Espacio para el paginador y el pie de página */
        }

        .users-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
            width: 90%;
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start; /* Alineación desde la izquierda */
        }

        .user-item {
            margin: 10px;
            padding: 15px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s;
            width: calc(20% - 20px); /* 5 columnas */
            text-align: center;
        }

        .user-item:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .user-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 10px;
        }

        .user-item h3 {
            margin: 0;
            font-size: 1.2em;
            color: #3B3F51 !important;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin: 20px auto;
            width: 90%;
            padding: 10px 0;
        }

        .pagination a {
            color: rgb(189,1,2);
            padding: 10px;
            border: 1px solid rgb(189,1,2);
            border-radius: 5px;
            margin: 5px;
            text-decoration: none;
            background-color: rgb(250,250,250);
        }

        .pagination a.active {
            background-color: rgb(250,250,250);
            border-color: rgb(189,1,2);
        }

        .pagination a:hover {
            background-color: rgb(189,1,2);
            border-color: rgb(250,250,250);
            color: rgb(250,250,250);
        }

        footer {
            background-color: transparent;
            padding: 10px 0;
            text-align: center;
            position: fixed;
            bottom: 0;
            width: 100%;
        }

        .section-title {
            text-align: center;
            margin: 20px 0;
            font-size: 1.5em;
            color: #3B3F51;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="welcome">
            <h3>Bienvenido, <?php echo htmlspecialchars($nombre_usuario); ?>!</h3>
        </div>
        <ul class="nav-links">
            <li><a href="articulos.php"><i class="fas fa-box"></i> Artículos</a></li>
            <li><a href="jobs.php"><i class="fas fa-home"></i> Inicio</a></li>
            <li><a href="empleo.php"><i class="fas fa-briefcase"></i> Empleo</a></li>
            <li><a href="empresas.php"><i class="fas fa-building"></i> Empresas</a></li>
            <li><a href="perfil.php"><i class="fas fa-user"></i> Perfil</a></li>
            <li><a href="usuarios.php"><i class="fas fa-users"></i> Usuarios</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
        </ul>
    </nav>

    <div class="content">
        <section id="empleados">
            <h2 class="section-title">Empleados</h2>
            <ul class="users-list">
                <?php
                if ($empleados_result->num_rows > 0) {
                    while ($row = $empleados_result->fetch_assoc()) {
                        $id = $row['id'];
                        $nombre = htmlspecialchars($row['nombre']);
                        $foto_perfil = $row['foto_perfil'] ? 'data:image/jpeg;base64,' . base64_encode($row['foto_perfil']) : 'img/default-user.png';
                        echo "<li class='user-item' onclick='location.href=\"ver-perfil.php?id=$id\"'>";
                        echo "<img src='$foto_perfil' alt='Foto de perfil'>";
                        echo "<h3>$nombre</h3>";
                        echo "</li>";
                    }
                } else {
                    echo "<p>No hay empleados registrados.</p>";
                }
                ?>
            </ul>
        </section>

        <section id="empleadores">
            <h2 class="section-title">Empleadores</h2>
            <ul class="users-list">
                <?php
                if ($empleadores_result->num_rows > 0) {
                    while ($row = $empleadores_result->fetch_assoc()) {
                        $id = $row['id'];
                        $nombre = htmlspecialchars($row['nombre']);
                        $foto_perfil = $row['foto_perfil'] ? 'data:image/jpeg;base64,' . base64_encode($row['foto_perfil']) : 'img/default-user.png';
                        echo "<li class='user-item' onclick='location.href=\"ver-perfil.php?id=$id\"'>";
                        echo "<img src='$foto_perfil' alt='Foto de perfil'>";
                        echo "<h3>$nombre</h3>";
                        echo "</li>";
                    }
                } else {
                    echo "<p>No hay empleadores registrados.</p>";
                }
                ?>
            </ul>
        </section>

        <div class="pagination">
            <?php if ($total_empleados_pages > 1): ?>
                <?php for ($i = 1; $i <= $total_empleados_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" class="<?php echo ($page == $i) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            <?php endif; ?>
        </div>

        <div class="pagination">
            <?php if ($total_empleadores_pages > 1): ?>
                <?php for ($i = 1; $i <= $total_empleadores_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" class="<?php echo ($page == $i) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            <?php endif; ?>
        </div>
    </div>

   
</body>
</html>
