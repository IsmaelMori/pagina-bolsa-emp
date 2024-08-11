<?php
session_start();
include 'config.php';

$isAuthenticated = isset($_SESSION['user_id']);

if (!isset($_GET['id'])) {
    header("Location: articulos.php");
    exit();
}

$article_id = $_GET['id'];

// Obtener los detalles del artículo
$sql = "SELECT a.id, a.title, a.content, a.created_at, u.email FROM articles a JOIN users u ON a.user_id = u.id WHERE a.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $article_id);
$stmt->execute();
$result = $stmt->get_result();
$article = $result->fetch_assoc();

if (!$article) {
    header("Location: articulos.php");
    exit();
}

$stmt->close();
$conn->close();
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Detalle del Artículo</title>
    <style>
        body {
            background-color: #ffffff;
            color: #000000;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
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

        .container {
            width: 70%;
            max-width: 700px;
            padding: 10px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            color: #000000;
            text-align: auto;
            margin-top: 90px; /* Ajusta este valor para subir el contenedor más arriba del centro */
            opacity: 0;
            animation: fadeInUp 1s forwards;
        }

        @keyframes fadeInUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .container h2 {
            text-align: center;
            color: #bd0102;
        }

        .article-detail {
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            margin-bottom: 20px;
            color: #000000;
            text-align: left;
        }

        .article-detail h3 {
            margin-top: 0;
            color: #bd0102;
        }

        .article-detail p {
            white-space: pre-wrap;
            color: #000000;
        }

        .article-detail small {
            display: block;
            margin-top: 10px;
            color: #bd0102;
        }

        .error, .success {
            color: #ff5555;
            text-align: center;
            margin-bottom: 20px;
        }

        .success {
            color: #55ff55;
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

<div class="container">
    <h2>Detalle del Artículo</h2>
    <div class="article-detail">
        <h3><?php echo $article['title']; ?></h3>
        <p><?php echo $article['content']; ?></p>
        <small>Publicado por: <?php echo $article['email']; ?> el <?php echo $article['created_at']; ?></small>
    </div>
    <p>Para interactuar con el artículo, simplemente desplácese hacia abajo y disfrute de la lectura. Si tiene alguna pregunta, no dude en contactarnos.</p>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
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
