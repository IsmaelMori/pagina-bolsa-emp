<?php
session_start();
include 'config.php';

$isAuthenticated = isset($_SESSION['user_id']);

// Procesar el formulario para agregar un nuevo artículo
if ($_SERVER["REQUEST_METHOD"] == "POST" && $isAuthenticated) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $user_id = $_SESSION['user_id'];

    $sql = "INSERT INTO articles (user_id, title, content) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_id, $title, $content);

    if ($stmt->execute()) {
        $success = "Artículo publicado con éxito";
    } else {
        $error = "Error al publicar el artículo: " . $stmt->error;
    }

    $stmt->close();
}

// Paginación
$limit = 4; // Número de artículos por página
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Obtener el total de artículos
$total_sql = "SELECT COUNT(*) as total FROM articles";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_articles = $total_row['total'];
$total_pages = ceil($total_articles / $limit);

// Obtener los artículos para la página actual
$sql = "SELECT id, title, content, created_at FROM articles ORDER BY created_at DESC LIMIT $start, $limit";
$result = $conn->query($sql);

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/pagina_principal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Artículos</title>
    <style>
        body {
            background-color: #fff;
            color: #000; /* Color de texto negro para todo el contenido */
            font-family: Arial, sans-serif;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
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

        .container {
            width: 80%;
            max-width: 1000px;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            margin-top: 80px; /* Separación para el navbar */
            margin-bottom: 30px; /* Espacio para la paginación */
            color: #000000;
        }

        .container h2 {
            text-align: center;
            color: #000000;
        }

        .form-container {
            margin-bottom: 30px;
            text-align: center;
        }

        .form-container a.button {
            display: inline-flex;
            align-items: center;
            padding: 10px;
            background-color: #007bff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
        }

        .form-container a.button i {
            margin-right: 5px;
        }

        .form-container a.button:hover {
            background-color: #0056b3;
        }

        .articles {
            margin-top: 20px;
        }

        .article {
            padding: 20px;
            background-color: #ffffff;
            color: #000000;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            cursor: pointer;
            transition: border-color 0.3s;
        }

        .article:hover {
            border-color: #007bff;
        }

        .article h3 {
            margin-top: 0;
        }

        .article a {
            color: #000000;
            text-decoration: none;
        }

        .article a:hover {
            text-decoration: underline;
        }

        .article p {
            white-space: pre-wrap;
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
            margin: 10px 0; /* Separación del contenedor */
            width: 70%;
            max-width: 1000px;
            position: fixed;
            bottom: 0;
            background-color: transparent;
            padding: 10px 0;
            
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
            color:rgb(250,250,250)

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
    <h2>Artículos</h2>
    <?php if (isset($success)) { echo "<p class='success'>$success</p>"; } ?>
    <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>

    <?php if ($isAuthenticated): ?>
        <div class="form-container">
            <a href="crear_articulo.php" class="button"><i class="fas fa-plus"></i> Nuevo Artículo</a>
        </div>
    <?php endif; ?>

    <div class="articles">
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<div class='article' onclick=\"window.location.href='articulo_detalle.php?id=" . $row['id'] . "'\">";
                echo "<h3>" . $row['title'] . "</h3>";
                echo "<p>" . substr($row['content'], 0, 100) . "...</p>";
                echo "<small>Publicado el " . $row['created_at'] . "</small>";
                echo "</div>";
            }
        } else {
            echo "<p>No hay artículos publicados.</p>";
        }
        ?>
    </div>
</div>

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
</body>
</html>
