<?php
session_start();
include 'config.php';

// Verificar sesión de usuario
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Procesar el formulario de agregar empleo si se envió
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];

    $title = $_POST['title'];
    $description = $_POST['description'];
    $salary = $_POST['salary'];
    $employment_type = $_POST['employment_type'];
    $job_type = $_POST['job_type'];
    $requirements = $_POST['requirements'];
    $benefits = $_POST['benefits'];

    $sql = "INSERT INTO jobs (user_id, title, description, salary, employment_type, company, job_type, requirements, benefits) VALUES ('$user_id', '$title', '$description', '$salary', '$employment_type', 'Company Name', '$job_type', '$requirements', '$benefits')";
    if ($conn->query($sql) === TRUE) {
        $success = "Empleo añadido con éxito";
    } else {
        $error = "Error al añadir el empleo: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Agregar Empleo</title>
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0;
            background-image: url('img/img7.webp'); /* URL de la imagen de fondo */
            background-size: cover; /* Asegura que la imagen cubra toda la pantalla */
            background-position: center; /* Centra la imagen */
            background-attachment: fixed; /* Fija la imagen de fondo */
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
            margin-top: 70px;
            width: 100%;
            max-width: 1300px;
            display: flex;
            justify-content: center;
            padding: 20px;
        }

        .form-container {
            width: 100%;
            max-width: 800px;
            background-color: rgba(250, 250, 250, 0.8);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }

        .form-container h2 {
            text-align: center;
            color: black;
        }

        .form-container form {
            display: flex;
            flex-direction: column;
        }

        .form-container label {
            color: black;
            margin-bottom: 5px;
        }

        .form-container input, .form-container textarea, .form-container select {
            width: 95%;
            margin-bottom: 15px;
            padding: 8px;
            border: 1px solid #333;
            border-radius: 5px;
            background-color: #ffffff;
            color: #000000;
        }

        .form-container input[type="submit"] {
            background-color: #ff5555;
            color: black;
            cursor: pointer;
            font-size: 16px;
        }

        .form-container input[type="submit"]:hover {
            background-color: #ff4444;
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
    <div class="form-container">
        <h2>Agregar Nuevo Empleo</h2>
        <?php if (isset($success)) { echo "<p class='success'>$success</p>"; } ?>
        <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <label for="title">Título del Empleo:</label>
            <input type="text" id="title" name="title" required>

            <label for="description">Descripción del Empleo:</label>
            <textarea id="description" name="description" rows="5" required></textarea>

            <label for="salary">Salario:</label>
            <input type="text" id="salary" name="salary" required>

            <label for="employment_type">Tipo de Empleo:</label>
            <select id="employment_type" name="employment_type" required>
                <option value="Administración, Contabilidad y Finanzas">Administración, Contabilidad y Finanzas</option>
                <option value="Tecnología, Sistemas y Comunicaciones">Tecnología, Sistemas y Comunicaciones</option>
                <option value="Diseño Gráfico">Diseño Gráfico</option>
            </select>

            <label for="job_type">Tipo de Trabajo:</label>
            <select id="job_type" name="job_type" required>
                <option value="Full-time">Full-time</option>
                <option value="Part-time">Part-time</option>
                <option value="Pasantía">Pasantía</option>
                <option value="Freelance">Freelance</option>
                <option value="Práctica">Práctica</option>
                <option value="Eventual">Eventual</option>
            </select>

            <label for="requirements">Requisitos:</label>
            <textarea id="requirements" name="requirements" rows="4"></textarea>

            <label for="benefits">Beneficios:</label>
            <textarea id="benefits" name="benefits" rows="4"></textarea>

            <input type="submit" value="Agregar Empleo">
        </form>
    </div>
</div>
</body>
</html>

<?php
$conn->close();
?>
