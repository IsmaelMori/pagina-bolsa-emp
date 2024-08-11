<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redirigir si el usuario ya está conectado
}

include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT id, email, password, role FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = $row['role'];
            header("Location: jobs.php"); // Redirigir al usuario al área protegida
        } else {
            $error = "Correo electrónico o contraseña incorrectos";
        }
    } else {
        $error = "Correo electrónico o contraseña incorrectos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Iniciar Sesión</title>
    <style>
        body {
            background-color: #ffffff;
            color: #ffffff;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-image: url('img/fondo.png');
            background-size: cover;
            background-position: center;
        }

        .container {
            background-color: rgba(189, 1, 2); /* Blanco con opacidad */
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            width: 80%;
            max-width: 900px; /* Asegura que el contenedor no sea demasiado ancho */
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .login-section, .welcome-section {
            width: 50%;
        }

        .welcome-section {
            text-align: left;
            color: #545454;
        }

        .login-form-container {
            background-color: rgba(250, 250, 250); /* Blanco con opacidad */
            padding: 15px;
            border-radius: 10px;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .user-icon {
            font-size: 50px;
            margin-bottom: 10px;
            color: rgba(189, 1, 2, 0.8);
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        .input-container {
            position: relative;
            width: 75%; /* Ajustar ancho */
            margin-bottom: 15px;
        }

        .input-container i {
            position: absolute;
            top: 50%;
            left: 10px;
            transform: translateY(-50%);
            color: #888;
        }

        input[type="text"], input[type="password"] {
            padding: 10px 10px 10px 30px; /* Espacio para el icono */
            border: none; /* Elimina el borde por defecto */
            border-bottom: 2px solid #8B0000; /* Línea roja en la parte inferior */
            background-color: transparent; /* Fondo transparente */
            color: rgba(189, 1, 2, 0.8);; /* Color del texto */
            width: 97%;
            box-shadow: none; /* Sin sombra */
            outline: none; /* Elimina el contorno por defecto al hacer clic */
            font-size: 15px; /* Tamaño de fuente para mejor legibilidad */
        }

        input[type="submit"] {
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #8B0000;
            color: #ffffff;
            font-size: 16px;
            cursor: pointer;
            width: 80%;
        }

        input[type="submit"]:hover {
            background-color: #545454;
        }

        .error {
            color: #ff5555;
            margin-bottom: 20px;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #333;
            padding: 10px;
            width: 99%;
            position: absolute;
            top: 0;
        }

        .navbar .logo {
            color: white;
            font-size: 24px;
            text-decoration: none;
        }

        .navbar .nav-links {
            list-style: none;
            display: flex;
            align-items: center;
        }

        .navbar .nav-links li {
            margin: 0 10px;
        }

        .navbar .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 18px;
        }

        .navbar .nav-links .fa {
            margin-right: 6px;
        }

        h2 {
            color: #000;
        }

        p {
            color: #000;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="welcome-section">
            <h2>¡Bienvenido!</h2>
            <p>Accede a tu cuenta o regístrate para comenzar.</p>
        </div>
        <div class="login-section">
            <div class="login-form-container">
                <i class="fas fa-user user-icon"></i>
                <h2>Iniciar Sesión</h2>
                <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                    <div class="input-container">
                        <i class="fas fa-envelope"></i>
                        <input type="text" id="email" name="email" placeholder="Correo Electrónico" required>
                    </div>
                    <div class="input-container">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Contraseña" required>
                    </div>
                    <input type="submit" value="Iniciar Sesión">
                </form>
                <p>¿No tienes una cuenta? <a href="formulario_empleado.php">Regístrate aquí</a>.</p>
            </div>
        </div>
    </div>
</body>
</html>
