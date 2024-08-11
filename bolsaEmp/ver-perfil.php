<?php
session_start();
include 'config.php';

// Verificar si el usuario está autenticado
if (isset($_SESSION['user_id'])) {
    $current_user_id = $_SESSION['user_id'];
    
    // Verificar si se ha pasado un ID de usuario en la URL
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $user_id = (int)$_GET['id'];

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
            $hobbies = explode(',', $row['hobbies']); // Asumir que hobbies están separados por comas
            $time_spent = explode(',', $row['time_spent']); // Asumir que time_spent está separado por comas
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

            // Mostrar la información del perfil
            echo <<<HTML
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
                <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css">
                <title>Perfil de Usuario</title>
                <style>
                    body {
                        background-color: #fff;
                        font-family: Arial, sans-serif;
                        margin: 0;
                        color: #fff;
                        display: flex;
                        flex-direction: column;
                        height: 100vh;
                        align-items: center;
                        padding-top: 80px; /* Espacio para el navbar fijo */
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
                        padding: 10px 20px;
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
                    .navbar .back-button {
                        background-color: #444;
                        border: none;
                        color: white;
                        padding: 10px 15px;
                        border-radius: 5px;
                        cursor: pointer;
                        font-size: 16px;
                        display: flex;
                        align-items: center;
                        margin-right: 20px;
                    }
                    .navbar .back-button i {
                        margin-right: 5px;
                    }
                    .profile-content {
                        background-color: #fff;
                        padding: 30px;
                        border-radius: 15px;
                        box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
                        color: #000;
                        text-align: center;
                        width: 90%;
                        max-width: 800px;
                        margin-top: 20px; /* Separar del navbar */
                    }
                    .profile-content img {
                        border-radius: 50%;
                        width: 150px;
                        height: 150px;
                        object-fit: cover;
                        cursor: pointer;
                        transition: transform 0.3s ease;
                        margin-bottom: 20px;
                    }
                    .profile-content img:hover {
                        transform: scale(1.1);
                    }
                    .profile-content .additional-info {
                        border-top: 2px solid red;
                        padding-top: 20px;
                        margin-top: 20px;
                    }
                    .profile-content .additional-info h2 {
                        margin-top: 20px;
                        font-size: 20px;
                    }
                    .profile-content ul {
                        list-style-type: disc;
                        padding-left: 20px;
                        text-align: left;
                        display: inline-block;
                        margin: 10px 0;
                    }
                    .profile-content ul li {
                        font-size: 18px;
                        margin: 5px 0;
                    }
                    .modal {
                        display: none;
                        position: fixed;
                        z-index: 1000;
                        left: 0;
                        top: 0;
                        width: 100%;
                        height: 100%;
                        overflow: auto;
                        background-color: rgba(0,0,0,0.8);
                    }
                    .modal-content {
                        margin: 15% auto;
                        padding: 20px;
                        width: 80%;
                        max-width: 700px;
                        background-color: #fff;
                    }
                    .modal-content img {
                        width: 100%;
                        height: auto;
                    }
                    .close {
                        color: #aaa;
                        float: right;
                        font-size: 28px;
                        font-weight: bold;
                    }
                    .close:hover,
                    .close:focus {
                        color: #000;
                        text-decoration: none;
                        cursor: pointer;
                    }
                    .resume-button {
        display: inline-block;
        background-color: rgb(189, 1, 2);
        color: #fff;
        padding: 10px 20px;
        border-radius: 5px;
        text-decoration: none;
        font-size: 16px;
        font-weight: bold;
        margin-top: 20px;
    }
    .resume-button:hover {
        background-color: rgb(150, 0, 0);
    }
                </style>
            </head>
            <body>
                <nav class="navbar">
                    <button class="back-button" onclick="history.back()"><i class="fas fa-arrow-left"></i> Regresar</button>
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
                <div class="profile-content">
                    <img src="data:image/jpeg;base64,{$fotoPerfil}" alt="Foto de perfil" id="profileImg">
                    <h1>Perfil de Usuario</h1>
                    <p><i class="fas fa-user"></i> Nombre: $nombre $apellido</p>
                    <p><i class="fas fa-id-card"></i> Cédula: $cedula</p>
                    <p><i class="fas fa-envelope"></i> Email: $email</p>
                    <p><i class="fas fa-user-tag"></i> Rol: $role</p>
                    <h2>Experiencias</h2>
                    <ul>
HTML;

            foreach ($experiences as $experience) {
                echo "<li>$experience</li>";
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
                    <h2>Hobbies</h2>
                    <ul>
HTML;

            foreach ($hobbies as $hobby) {
                echo "<li>$hobby</li>";
            }

            echo <<<HTML
                    </ul>
                    <h2>Tiempo Libre</h2>
                    <ul>
HTML;

            foreach ($time_spent as $time) {
                echo "<li>$time</li>";
            }

            echo <<<HTML
                    </ul>
                    <h2>Información Adicional</h2>
                    <p><strong>Currículo:</strong> <a href="$resume" target="_blank" class="resume-button">Ver Currículo</a></p>

                    <p><strong>Especialización:</strong> $specialization</p>
                </div>
                <!-- The Modal -->
                <div id="myModal" class="modal">
                    <span class="close">&times;</span>
                    <div class="modal-content">
                        <img src="data:image/jpeg;base64,{$fotoPerfil}" alt="Imagen de Perfil">
                    </div>
                </div>
                <script>
                    // Get the modal
                    var modal = document.getElementById("myModal");

                    // Get the image and insert it inside the modal
                    var img = document.getElementById("profileImg");
                    var modalImg = document.querySelector(".modal-content img");

                    img.onclick = function(){
                        modal.style.display = "block";
                        modalImg.src = this.src;
                    }

                    // Get the <span> element that closes the modal
                    var span = document.getElementsByClassName("close")[0];

                    // When the user clicks on <span> (x), close the modal
                    span.onclick = function() {
                        modal.style.display = "none";
                    }

                    // When the user clicks anywhere outside of the modal, close it
                    window.onclick = function(event) {
                        if (event.target == modal) {
                            modal.style.display = "none";
                        }
                    }
                </script>
            </body>
            </html>
HTML;
        } else {
            echo "No se encontró información del perfil.";
        }
    } else {
        echo "ID de usuario no válido.";
    }
} else {
    header("Location: login.php");
    exit;
}

$conn->close();
?>
