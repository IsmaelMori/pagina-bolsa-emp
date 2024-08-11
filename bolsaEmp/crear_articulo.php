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
        // Redireccionar al usuario a la página de artículos después de publicar
        header("Location: articulos.php");
        exit();
    } else {
        $error = "Error al publicar el artículo: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/navar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Crear Artículo</title>
    <style>
        body {
            background-color: #f4f4f4;
            color: #333;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0;
            background-image: url('img/img7.webp');
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
            margin-top: 80px; /* Aumentar el margen superior para alejar el contenedor del navbar */
            width: 80%;
            max-width: 800px;
            padding: 20px;
            background-color: #ffffff;
            color: #333;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            transition: box-shadow 0.3s ease;
        }

        .container:hover {
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.4);
        }

        .container h2 {
            text-align: center;
            color: #333;
        }

        .form-container {
            margin-bottom: 30px;
        }

        .form-container form {
            display: flex;
            flex-direction: column;
        }

        .form-container label, .form-container input, .form-container textarea {
            width: 97%;
            margin-bottom: 15px;
        }

        .form-container label {
            font-weight: bold;
        }

        .form-container input, .form-container textarea {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
            color: #333;
        }

        .form-container input[type="submit"] {
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
            font-size: 16px;
            display: inline-flex;
            align-items: center;
            padding: 10px;
            border: none;
        }

        .form-container input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .error, .success {
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 5px;
        }

        .error {
            color: #dc3545;
            background-color: #f8d7da;
        }

        .success {
            color: #28a745;
            background-color: #d4edda;
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
    <h2>Crear Nuevo Artículo</h2>
    <?php if (isset($success)) { echo "<p class='success'>$success</p>"; } ?>
    <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
    
    <div class="form-container">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <label for="title">Título del Artículo:</label>
            <input type="text" id="title" name="title" required>
            <label for="content">Contenido del Artículo:</label>
            <textarea id="content" name="content" rows="10" required></textarea>
            <input type="submit" value="Publicar Artículo">
        </form>
    </div>
</div>
</body>
</html>
