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

// Configuración de paginación
$limit = 4; // Número de elementos por página

// Paginación para Nuevos Empleos
$jobs_page = isset($_GET['jobs_page']) ? (int)$_GET['jobs_page'] : 1;
$jobs_start = ($jobs_page - 1) * $limit;

// Contar el total de empleos
$total_jobs_sql = "SELECT COUNT(*) as total FROM jobs";
$total_jobs_result = $conn->query($total_jobs_sql);
$total_jobs_row = $total_jobs_result->fetch_assoc();
$total_jobs = $total_jobs_row['total'];
$total_jobs_pages = ceil($total_jobs / $limit);

// Obtener los nuevos empleos
$sql = "SELECT jobs.id, jobs.title, jobs.description, jobs.company, jobs.ubicacion, jobs.created_at, users.email
        FROM jobs
        JOIN users ON jobs.user_id = users.id
        ORDER BY jobs.created_at DESC
        LIMIT $jobs_start, $limit";
$jobs_result = $conn->query($sql);

// Paginación para Empresas
$companies_page = isset($_GET['companies_page']) ? (int)$_GET['companies_page'] : 1;
$companies_start = ($companies_page - 1) * $limit;

// Contar el total de empresas
$total_companies_sql = "SELECT COUNT(*) as total FROM empresas";
$total_companies_result = $conn->query($total_companies_sql);
$total_companies_row = $total_companies_result->fetch_assoc();
$total_companies = $total_companies_row['total'];
$total_companies_pages = ceil($total_companies / $limit);

// Obtener las empresas registradas
$sql = "SELECT id, nombre_empresa, descripcion_empresa, ubicacion_empresa, fecha_registro, logo
        FROM empresas
        ORDER BY fecha_registro DESC
        LIMIT $companies_start, $limit";
$companies_result = $conn->query($sql);

$conn->close();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bolsa de Empleo - MiApp</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        body {
            background-color: #fff;
            color: #ffffff;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0;
            background-image: url('img/fondo4.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
    
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

        .jobs-container {
            margin-top: 80px;
            width: 80%;
            max-width: 800px;
            padding: 20px;
            background-color: #ffffff;
            color: #000000;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .jobs-container h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333333;
        }

        .jobs-container h2 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .jobs-container ul {
            list-style-type: none;
            padding: 0;
        }

        .jobs-container li {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 10px;
            border: 2px solid transparent;
            cursor: pointer;
            transition: border 0.3s;
        }

        .jobs-container li:hover {
            border-color: #007bff;
        }

        .jobs-container h3 {
            font-size: 20px;
            margin-bottom: 10px;
        }

        .jobs-container p {
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .jobs-container small {
            color: #666;
        }

        .jobs-container img {
            width: 100%; /* Ajusta al contenedor */
            max-width: 150px; /* Ancho máximo deseado */
            height: auto; /* Mantiene la proporción */
            object-fit: contain; /* Mantiene la imagen dentro del contenedor */
            border-radius: 5px;
            margin-top: 10px;
        }

        .centered-image {
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .error, .success {
            color: #ff5555;
            text-align: center;
            margin-bottom: 20px;
        }

        .success {
            color: #55ff55;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin: 20px auto;
            width: 100%;
        }

        .pagination a {
            color: #000;
            padding: 10px;
            border: 1px solid #666;
            border-radius: 5px;
            margin: 0 5px;
            text-decoration: none;
            background-color: #ffffff;
        }

        .pagination a.active {
            background-color: #f0f0f0;
            border-color: #f0f0f0;
        }

        .pagination a:hover {
            background-color: #f9f9f9;
        }

        .arrow {
            font-size: 18px;
            text-decoration: none;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="login.php" class="logo"><i class="fas fa-home"></i></a>
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

<div class="jobs-container">
    <h1>Bolsa de Empleo</h1>

    <section id="nuevos-empleos">
        <h2>Nuevos Empleos</h2>
        <ul>
            <?php
            if ($jobs_result->num_rows > 0) {
                while ($row = $jobs_result->fetch_assoc()) {
                    echo '<li onclick="window.location.href=\'empleo_detalle.php?id=' . $row['id'] . '\'">';
                    echo "<h3>" . htmlspecialchars($row["title"]) . "</h3>";
                    echo "<p>" . htmlspecialchars($row["description"]) . "</p>";
                    echo "<p><strong>Ubicación:</strong> " . htmlspecialchars($row["ubicacion"]) . "</p>";
                    echo "<p><small>Publicado por: " . htmlspecialchars($row['company']) . " (" . htmlspecialchars($row['email']) . ") el " . htmlspecialchars($row['created_at']) . "</small></p>";
                    echo "</li>";
                }
            } else {
                echo "<li>No hay nuevos empleos disponibles.</li>";
            }
            ?>
        </ul>
        <div class="pagination">
            <?php
            if ($jobs_page > 1) {
                echo '<a href="?jobs_page=' . ($jobs_page - 1) . '" class="arrow"><i class="fas fa-chevron-left"></i></a>';
            }
            if ($jobs_page < $total_jobs_pages) {
                echo '<a href="?jobs_page=' . ($jobs_page + 1) . '" class="arrow"><i class="fas fa-chevron-right"></i></a>';
            }
            ?>
        </div>
    </section>

    <section id="empresas">
        <h2>Empresas</h2>
        <ul>
            <?php
            if ($companies_result->num_rows > 0) {
                while ($row = $companies_result->fetch_assoc()) {
                    echo '<li onclick="window.location.href=\'empresa_detalle.php?id=' . $row['id'] . '\'">';
                    echo "<h3>" . htmlspecialchars($row["nombre_empresa"]) . "</h3>";
                    echo "<p>" . htmlspecialchars($row["descripcion_empresa"]) . "</p>";
                    echo "<p><strong>Ubicación:</strong> " . htmlspecialchars($row["ubicacion_empresa"]) . "</p>";
                    echo "<p><small><strong>Registrado el:</strong> " . htmlspecialchars($row["fecha_registro"]) . "</small></p>";
                    
                    // Mostrar la imagen de logo si existe
                    if (!empty($row['logo'])) {
                        $logoBase64 = base64_encode($row['logo']); // Convertir la imagen a base64
                        echo '<img src="data:image/jpeg;base64,' . $logoBase64 . '" alt="Logo" class="centered-image">';
                    } else {
                        echo '<p>No hay logo disponible</p>';
                    }
                    
                    echo "</li>";
                }
            } else {
                echo "<li>No hay empresas registradas.</li>";
            }
            ?>
        </ul>
        <div class="pagination">
            <?php
            if ($companies_page > 1) {
                echo '<a href="?companies_page=' . ($companies_page - 1) . '" class="arrow"><i class="fas fa-chevron-left"></i></a>';
            }
            if ($companies_page < $total_companies_pages) {
                echo '<a href="?companies_page=' . ($companies_page + 1) . '" class="arrow"><i class="fas fa-chevron-right"></i></a>';
            }
            ?>
        </div>
    </section>
</div>

<script>
    let prevScrollpos = window.pageYOffset;
    const navbar = document.querySelector('.navbar');

    window.onscroll = function() {
        let currentScrollPos = window.pageYOffset;
        if (prevScrollpos > currentScrollPos) {
            navbar.classList.remove("hidden");
        } else {
            navbar.classList.add("hidden");
        }
        prevScrollpos = currentScrollPos;
    }
</script>
</body>
</html>
