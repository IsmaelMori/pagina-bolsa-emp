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
    header("Location: login.php"); // Redirigir al usuario al inicio de sesión si no ha iniciado sesión
    exit;
}

// Paginación
$limit = 4; // Número de empresas por página
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Obtener el total de empresas
$total_sql = "SELECT COUNT(*) as total FROM empresas";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_empresas = $total_row['total'];
$total_pages = ceil($total_empresas / $limit);

// Obtener las empresas para la página actual
$sql = "SELECT id, nombre_empresa, descripcion_empresa, ubicacion_empresa, fecha_registro, logo FROM empresas ORDER BY fecha_registro DESC LIMIT $start, $limit";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empresas - MiApp</title>
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

        .welcome h3 {
            color: rgb(189, 1, 2);
        }

        .container {
            margin: 60px auto 80px; /* Ajusta el margen inferior para el espacio del footer */
            padding: 20px;
            background-color: rgba(250, 250, 250);
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(250, 250, 250);
            max-width: 1000px;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            flex-grow: 1;
        }

        .container h1, .container h2 {
            margin-bottom: 20px;
            color: #7e8890;
            text-align: center;
        }

        .search-container {
            margin-bottom: 20px;
            text-align: center;
            color: #fff;
            width: 100%;
            display: flex;
            justify-content: center;
        }

        .search-container input {
            padding: 10px;
            border: 1px solid #333;
            border-radius: 5px;
            background-color: #fff;
            color: #000;
            width: 50%;
            max-width: 500px;
            font-size: 16px;
            box-sizing: border-box;
        }

        .empresas-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
            color: #fff;
            width: 100%;
        }

        .empresa-item {
            margin: 10px 0;
            padding: 10px;
            background-color: #fff;
            border: 1px solid #fff;
            border-radius: 4px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: border-color 0.3s;
        }

        .empresa-item:hover {
            border-color: blue;
        }

        .empresa-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 20px;
        }

        .empresa-item .details {
            flex: 1;
        }

        .empresa-item h3 {
            margin: 0;
            font-size: 1.2em;
            color: #3B3F51 !important;
        }

        .empresa-item p {
            margin: 1px 0;
            color: #7e8890;
        }

        .empresa-details {
            display: none;
            background-color: #333;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
            color: #fff;
        }

        .empresa-item.open .empresa-details {
            display: block;
        }

        .error {
            color: #f44336;
            text-align: center;
            margin-top: 10px;
        }

        h2 {
            color: #fff;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin: 0; /* Ajusta el margen para centrar */
            width: 100%; /* Ajusta el ancho para centrar */
            background-color: transparent;
            padding: 10px 0;
            position: fixed;
            bottom: 0;
        }

        .pagination a {
            color: rgb(189,1,2);
            padding: 10px;
            border: 1px solid rgb(189,1,2);
            border-radius: 5px;
            margin: 0 5px;
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

        .posted-date {
            color: #000;
        }

        .posted-text {
            display: inline;
            margin-right: 5px;
            color: #000;
        }

        .posted-date-text {
            display: inline;
            color: #000;
        }

        footer {
            background-color: transparent;
            padding: 10px 0;
            text-align: center;
            position: fixed;
            bottom: 0;
            width: 100%;
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

    <div class="container">
        <h1>Empresas</h1>
        <div class="search-container">
            <input type="text" id="search" placeholder="Buscar empresas...">
        </div>

        <section id="lista-empresas">
            <h2>Lista de Empresas</h2>
            <ul class="empresas-list">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $logoData = base64_encode($row['logo']);
                        $logoType = pathinfo($row['logo'], PATHINFO_EXTENSION);
                        $logoMimeType = "image/" . ($logoType === 'jpg' ? 'jpeg' : $logoType);

                        echo '<li class="empresa-item" onclick="window.location.href=\'empresa_detalle.php?id=' . $row['id'] . '\'">';
                        echo '<img src="data:' . $logoMimeType . ';base64,' . $logoData . '" alt="Logo">';
                        echo '<div class="details">';
                        echo '<h3>' . htmlspecialchars($row['nombre_empresa']) . '</h3>';
                        echo '<p>' . htmlspecialchars($row['descripcion_empresa']) . '</p>';
                        echo '<div class="posted-date"><p class="posted-text">Publicado el :</p> <p class="posted-date-text">' . date('d/m/Y', strtotime($row['fecha_registro'])) . '</p></div>';
                        echo '</div>';
                        echo '</li>';
                    }
                } else {
                    echo '<li>No hay empresas registradas.</li>';
                }
                ?>
            </ul>
        </section>
    </div>

    <footer>
        <div class="pagination">
            <?php
            if ($page > 1) {
                echo '<a href="?page=' . ($page - 1) . '">&laquo; Anterior</a>';
            }

            for ($i = 1; $i <= $total_pages; $i++) {
                $active = ($i == $page) ? 'active' : '';
                echo '<a href="?page=' . $i . '" class="' . $active . '">' . $i . '</a>';
            }

            if ($page < $total_pages) {
                echo '<a href="?page=' . ($page + 1) . '">Siguiente &raquo;</a>';
            }
            ?>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search');
            const empresaItems = document.querySelectorAll('.empresa-item');

            searchInput.addEventListener('input', function() {
                const searchTerm = searchInput.value.toLowerCase();
                empresaItems.forEach(item => {
                    const name = item.querySelector('h3').textContent.toLowerCase();
                    if (name.includes(searchTerm)) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });

            let lastScrollTop = 0;
            const navbar = document.querySelector('.navbar');

            window.addEventListener('scroll', () => {
                const currentScroll = window.pageYOffset || document.documentElement.scrollTop;
                if (currentScroll > lastScrollTop) {
                    navbar.classList.add('hidden');
                } else {
                    navbar.classList.remove('hidden');
                }
                lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
            });
        });
    </script>
</body>
</html>
