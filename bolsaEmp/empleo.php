<?php
session_start();
include 'config.php';

$isAuthenticated = isset($_SESSION['user_id']);

if ($isAuthenticated) {
    $user_id = $_SESSION['user_id'];

    // Obtener el rol del usuario
    $sql = "SELECT role FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $user_role = $user['role'];
}

// Procesar el formulario para agregar un nuevo empleo
if ($_SERVER["REQUEST_METHOD"] == "POST" && $isAuthenticated) {
    $title = $_POST['title'];
    $description = $_POST['description'];

    $sql = "INSERT INTO jobs (user_id, title, description, company) VALUES (?, ?, ?, 'Company Name')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_id, $title, $description);

    if ($stmt->execute()) {
        $success = "Empleo añadido con éxito";
    } else {
        $error = "Error al añadir el empleo: " . $stmt->error;
    }

    $stmt->close();
}

// Paginación
$limit = 4; // Número de empleos por página
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Obtener el total de empleos
$total_sql = "SELECT COUNT(*) as total FROM jobs";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_jobs = $total_row['total'];
$total_pages = ceil($total_jobs / $limit);

// Obtener los empleos para la página actual
$sql = "SELECT j.id, j.title, j.description, j.company, j.created_at, u.email, e.logo , e.provincia , e.canton
        FROM jobs j 
        JOIN users u ON j.user_id = u.id 
        JOIN empresas e ON e.email = u.email 
        ORDER BY j.created_at DESC 
        LIMIT $start, $limit";
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
    <title>Empleos</title>
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
            min-height: 100vh;
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
            margin-top: 60px;
            margin-bottom: 80px; /* Espacio para el paginador fijo */
            width: 80%;
            max-width: 800px;
            padding: 20px;
            background-color: rgba(250, 250, 250);
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(250, 250, 250);
            display: flex;
            flex-direction: column;
            align-items: center;
            flex-grow: 1;
        }

        .container h2 {
            text-align: center;
            color: #000;
        }

        .form-container {
            margin-bottom: 30px;
        }

        .form-container form {
            display: flex;
            flex-direction: column;
        }

        .form-container label, .form-container input, .form-container textarea {
            width: 100%;
            margin-bottom: 15px;
        }

        .form-container input, .form-container textarea {
            padding: 10px;
            border: 1px solid #333;
            border-radius: 5px;
            background-color: #292929;
            color: #ffffff;
        }

        .form-container input[type="submit"] {
            background-color: #ff5555;
            cursor: pointer;
            font-size: 16px;
            display: inline-flex;
            align-items: center;
            padding: 10px;
        }

        .form-container input[type="submit"]:hover {
            background-color: #ff4444;
        }

        .jobs {
            margin-top: 20px;
            width: 100%;
        }

        .job {
            display: flex;
            align-items: center;
            background-color: rgba(250, 250, 250, 0.8);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            cursor: pointer;
            transition: border 0.3s ease;
            text-decoration: none;
            color: #000;
        }

        .job:hover {
            border: 2px solid #007bff;
        }

        .job-logo {
            max-width: 100px;
            max-height: 50px;
            margin-right: 10px;
        }

        .job-details {
            flex: 1;
        }

        .job h3 {
            margin-top: 0;
            color: #000;
        }

        .job p {
            white-space: pre-wrap;
            color: #000;
        }

        .job-details small {
            color: #000;
            display: block;
            margin-top: 10px;
            font-size: 14px;
        }

        .job-details .fa {
            margin-right: 5px;
        }

        .error, .success {
            color: #ff5555;
            text-align: center;
            margin-bottom: 20px;
        }

        .success {
            color: #55ff55;
        }

        .button {
            display: inline-block;
            background-color: #007bff;
            color: #fff;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 10px;
        }

        .button:hover {
            background-color: #0056b3;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin: 10px 0;
            width: 100%;
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
            color: rgb(250,250,250);
        }
    </style>
</head>
<body>
<nav class="navbar">
    <div class="welcome">
        <a href="login.php" class="logo"><i class="fas fa-home"></i></a>
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
    <h2>Empleos Disponibles</h2>
    <?php if (isset($success)) { echo "<p class='success'>$success</p>"; } ?>
    <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>

    <?php if ($isAuthenticated && $user_role == 'empleador'): ?>
        <div class="form-container">
            <a href="agregar_empleo.php" class="button"><i class="fas fa-plus"></i> Nuevo Empleo</a>
        </div>
    <?php endif; ?>

    <div class="jobs">
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                // Convertir el logo de BLOB a una imagen en línea
                $logo = base64_encode($row['logo']);
                $logo_src = 'data:image/jpeg;base64,' . $logo;

                echo "<a href='empleo_detalle.php?id=" . $row['id'] . "' class='job'>";
                echo "<img src='" . $logo_src . "' alt='Logo de la empresa' class='job-logo'>";
                echo "<div class='job-details'>";
                echo "<h3>" . $row['title'] . "</h3>";
                echo "<p>" . $row['description'] . "</p>";
                echo "<small><i class='fas fa-building'></i> Publicado por: " . $row['company'] . " (" . $row['email'] . ") el " . $row['created_at'] . "</small>";
                echo "<small><i class='fas fa-map-marker-alt'></i> Ubicado en: " . $row['provincia'] . " (" . $row['canton'] . ")</small>";
                echo "</div>";
                echo "</a>";
            }
        } else {
            echo "<p>No hay empleos disponibles.</p>";
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
