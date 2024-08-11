<?php
session_start();
include 'config.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    $_SESSION['error_message'] = "Usuario no autenticado.";
    header("Location: login.php");
    exit();
}

// Obtener el email del usuario
$sql_get_user_email = "SELECT email, nombre FROM users WHERE id = ?";
$stmt_get_user_email = $conn->prepare($sql_get_user_email);
$stmt_get_user_email->bind_param("i", $user_id);
$stmt_get_user_email->execute();
$result_get_user_email = $stmt_get_user_email->get_result();
$user = $result_get_user_email->fetch_assoc();

if (!$user) {
    $_SESSION['error_message'] = "Usuario no encontrado.";
    header("Location: login.php");
    exit();
}

$user_email = $user['email'];
$user_nombre = $user['nombre'];

// Obtener la información actual de la empresa usando el email del usuario
$sql_get_empresa = "SELECT * FROM empresas WHERE email = ?";
$stmt_get_empresa = $conn->prepare($sql_get_empresa);
$stmt_get_empresa->bind_param("s", $user_email);
$stmt_get_empresa->execute();
$result_get_empresa = $stmt_get_empresa->get_result();
$empresa = $result_get_empresa->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Procesar el archivo de logo
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == UPLOAD_ERR_OK) {
        // Verificar el tamaño del archivo (máximo 2MB)
        if ($_FILES['logo']['size'] > 2 * 1024 * 1024) {
            $_SESSION['error_message'] = "El archivo es demasiado grande. Máximo 2MB.";
            header("Location: subir-logo.php");
            exit();
        }

        // Leer el archivo y escapar el contenido
        $logo = file_get_contents($_FILES['logo']['tmp_name']);
        if ($logo === false) {
            $_SESSION['error_message'] = "Error al leer el archivo.";
            header("Location: subir-logo.php");
            exit();
        }
        $logo = $conn->real_escape_string($logo);

        // Actualizar el logo en la base de datos
        $sql_update_logo = "UPDATE empresas SET logo = ? WHERE email = ?";
        $stmt_update_logo = $conn->prepare($sql_update_logo);
        if ($stmt_update_logo === false) {
            $_SESSION['error_message'] = "Error en la preparación de la consulta: " . $conn->error;
            header("Location: subir-logo.php");
            exit();
        }
        $stmt_update_logo->bind_param('bs', $logo, $user_email);

        if ($stmt_update_logo->execute()) {
            $_SESSION['success_message'] = "Logo subido exitosamente.";
        } else {
            $_SESSION['error_message'] = "Error al subir el logo: " . $stmt_update_logo->error;
        }
    } else {
        $_SESSION['error_message'] = "Error en la carga del archivo: " . $_FILES['logo']['error'];
    }

    header("Location: subir-logo.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Subir Logo</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: Arial, sans-serif;
            background-image: url('img/fondo3.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .container {
            display: flex;
            height: 100vh;
            align-items: center;
            justify-content: center;
        }
        .form-container {
            width: 100%;
            max-width: 600px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
            text-align: center;
        }
        .form-container form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .input-container {
            margin-bottom: 15px;
            width: 100%;
        }
        .input-container label {
            display: block;
            margin-bottom: 5px;
        }
        .input-container input[type="file"] {
            margin-bottom: 10px;
        }
        .input-container img {
            max-width: 100%;
            max-height: 150px;
            border-radius: 8px;
            display: block;
            margin-top: 10px;
        }
        .success-message, .error-message {
            color: green;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .error-message {
            color: red;
        }
        input[type="submit"], button {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        input[type="submit"] {
            background-color: #28a745;
            color: white;
        }
        button {
            background-color: #dc3545;
            color: white;
        }
        button:hover, input[type="submit"]:hover {
            opacity: 0.9;
        }
        .back-button {
            background-color: #007bff;
            color: white;
            margin-top: 10px;
        }
        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h3>Bienvenido, <?php echo htmlspecialchars($user_nombre); ?>!</h3>
            <h4>Subir Logo</h4>
            <?php if (isset($_SESSION['success_message'])): ?>
                <p class="success-message"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></p>
            <?php elseif (isset($_SESSION['error_message'])): ?>
                <p class="error-message"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
            <?php endif; ?>
            <form method="post" action="subir-logo.php" enctype="multipart/form-data">
                <div class="input-container">
                    <label for="logo"><i class="fas fa-image"></i> Subir Logo (opcional):</label>
                    <input type="file" id="logo" name="logo" accept="image/*" onchange="previewLogo(event)">
                    <?php if (!empty($empresa['logo'])): ?>
                        <img id="logo-preview" src="data:image/jpeg;base64,<?php echo base64_encode($empresa['logo']); ?>" alt="Logo de la empresa">
                    <?php else: ?>
                        <img id="logo-preview" style="display: none;" alt="Vista previa del logo">
                    <?php endif; ?>
                </div>
                <input type="submit" value="Subir Logo">
                <button type="button" onclick="window.history.back()"><i class="fas fa-times"></i> Cancelar</button>
                <button type="button" class="back-button" onclick="window.location.href='perfil.php'"><i class="fas fa-arrow-left"></i> Regresar al Perfil</button>
            </form>
        </div>
    </div>

    <script>
        function previewLogo(event) {
            var file = event.target.files[0];
            var reader = new FileReader();

            reader.onload = function(e) {
                var preview = document.getElementById('logo-preview');
                preview.src = e.target.result;
                preview.style.display = 'block';
            };

            if (file) {
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>
