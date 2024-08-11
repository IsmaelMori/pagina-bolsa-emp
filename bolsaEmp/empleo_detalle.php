<?php
session_start();
include 'config.php';

// Verificar si el usuario ha iniciado sesión
$isLoggedIn = isset($_SESSION['user_id']);
$user_id = $isLoggedIn ? $_SESSION['user_id'] : null;

if (!isset($_GET['id'])) {
    header("Location: empleo.php");
    exit();
}

$job_id = $_GET['id'];

// Obtener los detalles del empleo, incluyendo el logo de la empresa
$sql = "SELECT j.id, j.title, j.description, j.company, j.created_at, j.employment_type, j.job_type, j.salary, j.requirements, j.benefits, e.logo, e.email , e.nombre_empresa, e.provincia, e.canton
        FROM jobs j
        JOIN users u ON j.user_id = u.id
        JOIN empresas e ON u.email = e.email
        WHERE j.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();
$job = $result->fetch_assoc();

if (!$job) {
    header("Location: empleo.php");
    exit();
}

// Manejar la solicitud de empleo y la adición a favoritos
if ($_SERVER["REQUEST_METHOD"] == "POST" && $isLoggedIn) {
    if (isset($_POST['apply'])) {
        // Lógica para manejar la solicitud de empleo
        $success = "Has solicitado este empleo";
    } elseif (isset($_POST['add_favorite'])) {
        // Verificar si el empleo ya está en favoritos
        $checkSql = "SELECT * FROM favorites WHERE user_id = ? AND job_id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ii", $user_id, $job_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $error = "Ya has solicitado este empleo.";
        } else {
            // Si no está en favoritos, agregarlo
            $insertSql = "INSERT INTO favorites (user_id, job_id) VALUES (?, ?)";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("ii", $user_id, $job_id);
            if ($insertStmt->execute()) {
                $success = "Empleo solicitado";
            } else {
                $error = "Error al guardar el empleo en favoritos: " . $conn->error;
            }
            $insertStmt->close();
        }
        $checkStmt->close();
    }
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
    <title>Detalle del Empleo</title>
    <style>
        body {
            background-color: #fff;
            color: #000000;
            font-family: Arial, sans-serif;
            margin: 0;
            background-image: url('img/fondo4.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            padding-top: 60px; /* Para evitar que el contenido quede oculto detrás del navbar */
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
            width: 80%;
            max-width: 900px;
            padding: 20px;
            background-color: #ffffff;
            color: #000000;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            margin-left: auto;
            margin-right: auto;
        }

        .job-detail {
            padding: 20px;
            background-color: #ffffff;
            color: #000000;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }

        .job-detail .company-logo {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .job-detail .company-logo img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
            margin-right: 20px;
        }

        .job-detail .company-logo h3 {
            margin: 0;
            font-size: 28px;
            color: #000000;
        }

        .job-detail .company-logo .company-name {
            font-size: 20px;
            color: #555555;
            margin-top: 5px;
        }

        .job-detail .description {
            margin: 20px 0;
            border-top: 2px solid #dddddd;
            padding-top: 20px;
        }

        .job-detail .details {
            margin-top: 20px;
            color: #000;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
        }

        .job-detail .details p {
            margin: 5px 0;
            display: flex;
            align-items: center;
        }

        .job-detail .details i {
            color: rgb(189,1,2);
            margin-right: 10px;
        }

        .job-detail form {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .job-detail input[type="submit"] {
            background-color: rgb(189,1,2);
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s ease;
            margin-left: auto;
        }

        .job-detail input[type="submit"]:hover {
            background-color: rgb(189,1,2);
        }

        .requirements-benefits {
            margin-top: 20px;
            padding: 20px;
            width: 80%;
            max-width: 900px;
            background-color: #ffffff;
            color: #000000;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            margin-left: auto;
            margin-right: auto;
        }

        .requirements-benefits h4 {
            margin: 10px 0;
            color: #000000;
        }

        .requirements-benefits p {
            color: #555555;
            white-space: pre-wrap;
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
    <h2>Detalle del Empleo</h2>
    <?php if (isset($success)) { echo "<p class='success'>$success</p>"; } ?>
    <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>

    <div class="job-detail">
        <div class="company-logo">
            <?php if ($job['logo']): ?>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($job['logo']); ?>" alt="Logo de la Empresa">
            <?php else: ?>
                <img src="img/default-logo.png" alt="Logo de la Empresa">
            <?php endif; ?>
            <div>
                <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                <p class="company-name"><?php echo htmlspecialchars($job['nombre_empresa']); ?></p>
            </div>
        </div>
        
        <div class="description">
            <p><?php echo nl2br(htmlspecialchars($job['description'])); ?></p>
            <div class="details">
                <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($job['company']); ?>: <?php echo htmlspecialchars($job['email']); ?></p>
                <p><i class="fas fa-calendar-day"></i> <?php echo htmlspecialchars($job['created_at']); ?></p>
                <p><i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($job['employment_type']); ?></p>
                <p><i class="fas fa-tags"></i> <?php echo htmlspecialchars($job['job_type']); ?></p>
                <p><i class="fas fa-dollar-sign"></i> <?php echo htmlspecialchars($job['salary']); ?></p>
                <?php echo "<p><i class='fas fa-map-marker-alt'></i> Ubicado en: " . $job['provincia'] . " (" . $job['canton'] . ")</p>";?>
            </div>
        </div>
        
        <?php if ($isLoggedIn): ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?id=' . $job_id);?>">
            <input type="hidden" name="job_id" value="<?php echo htmlspecialchars($job_id); ?>">
            <input type="submit" name="add_favorite" value="Solicitar Empleo">
        </form>
        <?php endif; ?>
    </div>
</div>

<div class="requirements-benefits">
    <h4>Requisitos:</h4>
    <p><?php echo nl2br(htmlspecialchars($job['requirements'])); ?></p>
    <h4>Beneficios:</h4>
    <p><?php echo nl2br(htmlspecialchars($job['benefits'])); ?></p>
</div>

<script>
    let lastScrollTop = 0;
    const navbar = document.querySelector('.navbar');

    window.addEventListener('scroll', () => {
        let currentScroll = window.pageYOffset || document.documentElement.scrollTop;
        if (currentScroll > lastScrollTop) {
            navbar.classList.add('hidden');
        } else {
            navbar.classList.remove('hidden');
        }
        lastScrollTop = currentScroll <= 0 ? 0 : currentScroll; // For Mobile or negative scrolling
    });
</script>
</body>
</html>
