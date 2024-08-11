<?php
session_start();
include 'config.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Obtener el nombre y email del usuario
    $sql = "SELECT nombre, email FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $nombre_usuario = $row['nombre'];
    $user_email = $row['email'];
} else {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $empresa_id = intval($_GET['id']);

    // Obtener la información de la empresa
    $sql = "SELECT nombre_empresa, descripcion_empresa, ubicacion_empresa, logo FROM empresas WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $empresa_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $empresa = $result->fetch_assoc();
    } else {
        $empresa = null; // No se encontró la empresa
    }
} else {
    echo "ID de empresa no proporcionado.";
    exit;
}

// Obtener los empleos de la empresa actual
$sql = "SELECT j.id, j.title, j.description, j.company, j.created_at, u.email
        FROM jobs j
        INNER JOIN users u ON j.user_id = u.id
        INNER JOIN empresas e ON u.email = e.email
        WHERE e.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $empresa_id);
$stmt->execute();
$result = $stmt->get_result();

$jobs = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $jobs[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Empresa - MiApp</title>

    <!-- Incluir FontAwesome para los iconos -->
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
    top: -60px; /* Ajusta el valor según el alto del navbar */
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


        .container {
            flex: 1;
            margin: 80px auto 20px;
            padding: 20px;
            background-color: rgba(250, 250, 250);
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(250, 250, 250, );
            max-width: 1000px;
            width: 100%;
        }

        .container h1 {
            margin-bottom: 20px;
            color: #000;
            text-align: center;
        }

        .empresa-detalle {
            display: flex;
            align-items: flex-start;
            background-color: #444;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(250, 250, 250, 0.8);
            background-color: rgba(250, 250, 250, 0.8);
        }

        .empresa-detalle img {
            max-width: 150px;
            height: auto;
            border-radius: 5px;
            margin-right: 20px;
        }

        .empresa-info {
            flex: 1;
        }

        .empresa-info h2 {
            margin: 0;
            color: #000;
            font-size: 2em;
        }

        .empresa-info p {
            margin: 10px 0;
            color: #000;
        }

        .empresa-info .ubicacion {
            margin-top: 20px;
            color: #fff;
        }

        .empresa-info .ubicacion i {
            margin-right: 5px;
        }

        .jobs-list {
            margin-top: 30px;
        }

        .job-item {
            background-color: #ffffff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: background-color 0.3s, border 0.3s;
        }

        .job-item:hover {
            background-color: #e7f0ff;
            border-color: #007bff;
        }

        .job-item h3 {
            margin: 0;
            color: #000;
        }

        .job-item p {
            color: #000;
        }
      
        h2 {
            color: #000;
        }
        p{
            color: #000;

        }
h3{
    color: rgb(189,1,2);
}

    </style>
</head>
<body>
    <nav class="navbar">
        <div class="welcome">
            <h3>Bienvenido, <?php echo $nombre_usuario; ?>!</h3>
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

    <div class="container">
        <h1>Detalles de la Empresa</h1>
        <?php if (isset($empresa)) { ?>
            <div class="empresa-detalle">
                <img src="data:image/png;base64,<?php echo base64_encode($empresa['logo']); ?>" alt="Logo">
                <div class="empresa-info">
                    <h2><?php echo htmlspecialchars($empresa['nombre_empresa']); ?></h2>
                    <p><?php echo htmlspecialchars($empresa['descripcion_empresa']); ?></p>
                    <div class="ubicacion">
                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($empresa['ubicacion_empresa']); ?>
                    </div>
                </div>
            </div>
        <?php } else { ?>
            <p>Información de la empresa no disponible.</p>
        <?php } ?>

        <div class="jobs-list">
            <h2>Empleos de la Empresa</h2>
            <?php if (!empty($jobs)) { ?>
                <?php foreach ($jobs as $job) { ?>
                    <div class="job-item" onclick="window.location.href='empleo_detalle.php?id=<?php echo $job['id']; ?>'">
                        <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                        <p><?php echo htmlspecialchars($job['description']); ?></p>
                       <p> <small>Publicado por: <?php echo htmlspecialchars($job['company']); ?> (<?php echo htmlspecialchars($job['email']); ?>) el <?php echo htmlspecialchars($job['created_at']); ?></small></p>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <p>No hay empleos disponibles para esta empresa.</p>
            <?php } ?>
        </div>
    </div>
</body>
</html>
