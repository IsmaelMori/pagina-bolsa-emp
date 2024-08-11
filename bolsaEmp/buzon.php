<?php
session_start();
include 'config.php';

// Verificar si el usuario está autenticado y es empleado o admin
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role']; // Asegúrate de que el rol esté guardado en la sesión

    // Solo empleadores y administradores pueden acceder a esta página
    if ($role !== 'empleador' && $role !== 'admin') {
        header("Location: perfil.php");
        exit();
    }

    // Paginación
    $limit = 10; // Número de usuarios por página
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    // Consulta para obtener los usuarios que han solicitado empleos generados por el empleador actual
    $sql = "SELECT u.id, u.nombre, u.email, u.foto_perfil, j.title
            FROM users u
            INNER JOIN favorites f ON u.id = f.user_id
            INNER JOIN jobs j ON f.job_id = j.id
            WHERE j.user_id = ?
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $user_id, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    // Consulta para contar el total de solicitudes
    $sql_count = "SELECT COUNT(*) as total
                  FROM users u
                  INNER JOIN favorites f ON u.id = f.user_id
                  INNER JOIN jobs j ON f.job_id = j.id
                  WHERE j.user_id = ?";
    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->bind_param("i", $user_id);
    $stmt_count->execute();
    $result_count = $stmt_count->get_result();
    $total_users = $result_count->fetch_assoc()['total'];
    $total_pages = ceil($total_users / $limit);

    // Mostrar solicitudes
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <title>Buzón de Solicitudes</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                margin: 0;
                padding: 0;
                background-image: url('img/fondo3.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            } .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #fff;
            padding: 10px;
            width: 100%;
            position: fixed;
            top: 0;
            z-index: 1000;
            transition: top 0.3s, box-shadow 0.3s; /* Transiciones suaves */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3); /* Efecto de sombra */
        }

        .navbar.hidden {
            top: -60px; /* Ajusta este valor al tamaño de tu barra de navegación */
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
                margin-top: 60px;
                padding: 20px;
                max-width: 1200px;
                margin-left: auto;
                margin-right: auto;
            }
            .back-button {
                display: flex;
                align-items: center;
                margin-bottom: 20px;
                color: rgb(189,1,2);
                text-decoration: none;
                font-size: 18px;
            }
            .back-button i {
                margin-right: 10px;
                font-size: 20px;
            }
            .user-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 20px;
            }
            .user-card {
                display: flex;
                flex-direction: column;
                align-items: center;
                background-color: #fff;
                border: 1px solid #ddd;
                border-radius: 5px;
                padding: 10px;
                box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
                transition: all 0.3s ease;
                cursor: pointer;
                text-align: center;
            }
            .user-card:hover {
                border-color: #007BFF;
                transform: scale(1.05);
            }
            .user-card img {
                border-radius: 50%;
                width: 100px;
                height: 100px;
                object-fit: cover;
                margin-bottom: 10px;
            }
            .user-card .info {
                text-align: center;
            }
            .user-card .info h3 {
                margin: 0 0 5px 0;
                font-size: 16px;
            }
            .user-card .info p {
                margin: 0;
                color: #555;
                font-size: 14px;
            }
            .pagination {
                margin-top: 20px;
                text-align: center;
            }
            .pagination a {
                padding: 10px 15px;
                margin: 0 5px;
                border: 1px solid #ddd;
                border-radius: 5px;
                color: #007BFF;
                text-decoration: none;
            }
            .pagination a:hover {
                background-color: #f1f1f1;
            }
            .pagination .current {
                background-color: #007BFF;
                color: white;
                border: 1px solid #007BFF;
            }
        </style>
    </head>
    <body>
        <nav class="navbar">
            <a href="perfil.php" class="logo"><i class="fas fa-user-circle"></i></a>
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
            <a href="perfil.php" class="back-button"><i class="fas fa-arrow-left"></i> Regresar al Perfil</a>
            
            <h1>Buzón de Solicitudes</h1>
            
            <div class="user-grid">
HTML;

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $user_id = htmlspecialchars($row['id']);
            $user_name = htmlspecialchars($row['nombre']);
            $user_email = htmlspecialchars($row['email']);
            $job_title = htmlspecialchars($row['title']);

            // Verificar si hay foto de perfil
            $foto_perfil_blob = $row['foto_perfil'];
            if ($foto_perfil_blob) {
                // Convertir el BLOB en una imagen en base64
                $foto_perfil_base64 = 'data:image/jpeg;base64,' . base64_encode($foto_perfil_blob);
                $foto_perfil = $foto_perfil_base64;
            } else {
                // Usar un ícono predeterminado si no hay foto de perfil
                $foto_perfil = 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/6e/User_font_awesome.svg/2048px-User_font_awesome.svg.png'; // Cambia esta URL por la de tu ícono
            }

            echo <<<HTML
            <a href="ver-perfil.php?id=$user_id" class="user-card">
                <img src="$foto_perfil" alt="Foto de perfil">
                <div class="info">
                    <h3>$user_name</h3>
                    <p>$user_email</p>
                    <p><strong>Empleo:</strong> $job_title</p>
                </div>
            </a>
HTML;
        }
    } else {
        echo "<p>No hay solicitudes para mostrar.</p>";
    }

    echo <<<HTML
            </div>
            <div class="pagination">
HTML;

    // Paginación
    if ($total_pages > 1) {
        for ($i = 1; $i <= $total_pages; $i++) {
            $class = ($i == $page) ? 'current' : '';
            echo "<a href='?page=$i' class='$class'>$i</a>";
        }
    }

    echo <<<HTML
            </div>
        </div>
    </body>
    </html>
HTML;

} else {
    header("Location: login.php");
    exit();
}

$conn->close();
?>
