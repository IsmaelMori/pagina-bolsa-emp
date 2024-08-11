<?php
session_start();
include 'config.php';

// Verificar si el usuario está autenticado
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Consulta para obtener los datos del usuario
    $sql = "SELECT nombre, apellido, cedula, email, role, foto_perfil, hobbies, time_spent, resume, specialization FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nombre = $row['nombre'];
        $apellido = $row['apellido'];
        $cedula = $row['cedula'];
        $email = $row['email'];
        $role = $row['role'];
        $fotoPerfil = base64_encode($row['foto_perfil']);
        $hobbies = $row['hobbies'];
        $time_spent = $row['time_spent'];
        $resume = $row['resume'];
        $specialization = $row['specialization'];

        // Consultas para obtener experiencias y habilidades
        $sql_experiences = "SELECT experience FROM experiences WHERE user_id = ?";
        $stmt_experiences = $conn->prepare($sql_experiences);
        $stmt_experiences->bind_param("i", $user_id);
        $stmt_experiences->execute();
        $result_experiences = $stmt_experiences->get_result();
        $experiences = [];
        while ($exp = $result_experiences->fetch_assoc()) {
            $experiences[] = $exp['experience'];
        }

        $sql_skills = "SELECT skill FROM skills WHERE user_id = ?";
        $stmt_skills = $conn->prepare($sql_skills);
        $stmt_skills->bind_param("i", $user_id);
        $stmt_skills->execute();
        $result_skills = $stmt_skills->get_result();
        $skills = [];
        while ($skill = $result_skills->fetch_assoc()) {
            $skills[] = $skill['skill'];
        }

        // Verificar si el nombre de la empresa está registrado
        $sql_company = "SELECT COUNT(*) as count FROM empresas WHERE nombre_empresa IS NULL OR nombre_empresa = ''";
        $stmt_company = $conn->prepare($sql_company);
        $stmt_company->execute();
        $result_company = $stmt_company->get_result();
        $company = $result_company->fetch_assoc();
        $isCompanyRegistered = $company['count'] > 0;

        // Mostrar la información del perfil
        echo <<<HTML
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
            <link rel="stylesheet" href="css/perfil.css">
            <title>Perfil de Usuario</title>
            <style>
                body {
                    background-color: #fff;
                    font-family: Arial, sans-serif;
                    margin: 0;
                    display: flex;
                    flex-direction: column; /* Alinea el contenido verticalmente */
                    height: 100vh;
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
                .navbar .nav-links .start {
                    margin-right: auto; /* Ajusta el enlace "Inicio" a la izquierda */
                }
                .container {
                    display: flex;
                    flex: 1; /* Ocupa todo el espacio restante */
                    margin-top: 60px; /* Espacio para la barra de navegación */
                    padding: 20px;
                    gap: 20px; /* Espacio entre contenedores */
                    overflow: auto; /* Asegura que el contenido no se desborde */
                }
                .perfil-container, .actions-container, .additional-info {
                    background-color: #fff;
                    padding: 20px;
                    border-radius: 10px;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                }
                .perfil-container {
                    flex: 1; /* Ocupa un espacio flexible */
                    max-width: 500px; /* Ancho máximo */
                }
                .actions-container {
                    flex: 1; /* Ocupa un espacio flexible */
                    margin-left: 20px; /* Espacio entre las secciones */
                }
                .additional-info {
                    flex: 2; /* Ocupa el doble de espacio que el perfil */
                    margin-top: 20px; /* Espacio superior */
                }
                .perfil {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                }
                .perfil img {
                    border-radius: 50%;
                    width: 150px; /* Tamaño ajustado */
                    height: 150px; /* Tamaño ajustado */
                    object-fit: cover;
                }
                .perfil .datos p {
                    font-size: 18px; /* Tamaño ajustado */
                    margin: 10px 0;
                }
                .perfil .datos p i {
                    margin-right: 10px;
                }
                .buttons a, .actions-container a {
                    display: block;
                    margin: 10px 0;
                    padding: 10px 20px;
                    border-radius: 5px;
                    text-decoration: none;
                    color: #fff;
                    background-color: rgb(129, 1, 2);
                    transition: background-color 0.3s ease;
                }
                .buttons a:hover, .actions-container a:hover {
                    background-color: rgb(189, 1, 2);
                }
                .buttons a.logout {
                    background-color: rgb(255, 20, 20);
                }
                .buttons a.logout:hover {
                    background-color: rgb(255, 20, 20);
                }
                .actions-container a {
                    background-color: rgb(129, 1, 2);
                    text-align: center;
                }
                .actions-container a:hover {
                    background-color: rgb(189, 1, 2);
                }
                .updated-info, .additional-info {
                    margin-top: 20px;
                }
            </style>
        </head>
        <body>
            <nav class="navbar">
                <a href="jobs.php" class="logo"><i class="fas fa-user-circle"></i></a>
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
            <div class="container animated">
                <div class="perfil-container">
                    <h1>Perfil de Usuario</h1>
                    <div class="perfil">
                        <img src="data:image/jpeg;base64,{$fotoPerfil}" alt="Foto de perfil">
                        <div class="datos">
                            <p><i class="fas fa-user"></i><strong>Nombre:</strong> $nombre $apellido</p>
                            <p><i class="fas fa-id-card"></i><strong>Cédula:</strong> $cedula</p>
                            <p><i class="fas fa-envelope"></i><strong>Correo Electrónico:</strong> $email</p>
                            <p><i class="fas fa-user-tag"></i><strong>Rol:</strong> $role</p>
                        </div>
                    </div>
                    <div class="buttons">
                        <a href="editar-perfil.php">Editar Perfil</a>
                        <a href="editar-Contraseña.php">Cambiar Contraseña</a>
HTML;

        // Mostrar los enlaces de edición de empresa y creación de empresa si el rol es 'empleador'
        if ($role === 'empleador') {
            echo <<<HTML
                        <a href="editar-empresa.php">Editar Empresa</a>
HTML;

            // Mostrar el botón "Crear Empresa" solo si no hay ningún registro con nombre_empresa nulo o vacío
            if ($isCompanyRegistered) {
                echo '<a href="formulario_empleador.php">Crear Empresa</a>';
            }

            echo <<<HTML
                        <a href="buzon.php">Buzón de Solicitudes</a>
HTML;
        }
                    
        echo <<<HTML
                <a href="logout.php" class="logout">Cerrar Sesión</a>
                    </div>
                </div>
                <div class="actions-container">
                    <a href="add_experience.php">Añadir Experiencia</a>
                    <a href="add_skills.php">Añadir Habilidades</a>
                    <a href="edit_personal_data.php">Editar Datos Personales</a>
                    
                    <!-- Información de Experiencia -->
                    <div class="additional-info">
                        <h2>Experiencias</h2>
                        <ul>
HTML;
        foreach ($experiences as $exp) {
            echo "<li>$exp</li>";
        }
        echo <<<HTML
                        </ul>
                        <h2>Habilidades</h2>
                        <ul>
HTML;
        foreach ($skills as $skill) {
            echo "<li>$skill</li>";
        }
        echo <<<HTML
                        </ul>
                        <h2>Información Adicional</h2>
                        <p><strong>Hobbies:</strong> $hobbies</p>
                        <p><strong>Tiempo Libre:</strong> $time_spent</p>
                        <p><strong>Currículo:</strong> <a href="$resume" target="_blank">Ver Currículo</a></p>
                        <p><strong>Especialización:</strong> $specialization</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
HTML;
    } else {
        echo "No se encontró información del perfil.";
    }
} else {
    header("Location: login.php");
    exit;
}

$conn->close();
?>
