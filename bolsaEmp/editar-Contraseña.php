<?php
// Incluir archivo de configuración de base de datos
require_once 'config.php';

session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirigir al login si no está autenticado
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Verificar que las contraseñas nuevas coincidan
    if ($new_password !== $confirm_password) {
        $message = 'Las contraseñas nuevas no coinciden.';
    } else {
        // Consultar la contraseña actual del usuario
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar la contraseña actual
        if (password_verify($current_password, $user['password'])) {
            // Actualizar la contraseña
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$new_password_hash, $user_id]);
            $message = 'Contraseña actualizada con éxito.';
        } else {
            $message = 'La contraseña actual es incorrecta.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña</title>
    <link rel="stylesheet" href="styles.css"> <!-- Agregar el CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome -->
    <style>
        /* Estilos generales para el cuerpo de la página */
        body {
            font-family: Arial, sans-serif;
            background: url('img/fondo4.png') no-repeat center center fixed;
            background-size: cover;
            color: #333; /* Color de texto general */
            margin: 0;
            padding: 0;
        }

        /* Estilos para el contenedor del formulario */
        form {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background-color: #ffffff; /* Fondo blanco para el formulario */
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        /* Estilos para los elementos del formulario */
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }

        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        /* Estilos para el botón de envío */
        button {
            background-color: #e74c3c; /* Rojo fuerte para el botón */
            color: #ffffff; /* Texto blanco en el botón */
            border: none;
            padding: 15px 20px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        /* Cambio de color del botón al pasar el ratón */
        button:hover {
            background-color: #c0392b; /* Rojo más oscuro para el hover */
        }

        /* Estilos para los mensajes de error o éxito */
        p {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        p.success {
            background-color: #d4edda; /* Verde claro para el éxito */
            color: #155724; /* Texto verde oscuro */
        }

        p.error {
            background-color: #f8d7da; /* Rojo claro para el error */
            color: #721c24; /* Texto rojo oscuro */
        }

        /* Estilos para el botón de volver al perfil */
        .button-back {
            position: absolute;
            top: 20px;
            left: 20px;
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            background-color: rgb(189,1,2); /* Azul para el botón */
            color: #ffffff; /* Texto blanco */
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .button-back i {
            margin-right: 8px; /* Espacio entre el icono y el texto */
        }

        /* Cambio de color del botón al pasar el ratón */
        .button-back:hover {
            background-color: rgb(129,1,2); /* Azul más oscuro para el hover */
        }
    </style>
</head>
<body>
    <!-- Botón para volver al perfil -->
    <a href="perfil.php" class="button-back"><i class="fas fa-arrow-left"></i>Volver al Perfil</a>
    
    
    <?php if ($message): ?>
        <p class="<?php echo strpos($message, 'éxito') !== false ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>
    <form action="" method="post">
        <label for="current_password">Contraseña Actual:</label>
        <input type="password" id="current_password" name="current_password" required>
        
        <label for="new_password">Nueva Contraseña:</label>
        <input type="password" id="new_password" name="new_password" required>
        
        <label for="confirm_password">Confirmar Nueva Contraseña:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
        
        <button type="submit">Actualizar Contraseña</button>
    </form>
</body>
</html>
