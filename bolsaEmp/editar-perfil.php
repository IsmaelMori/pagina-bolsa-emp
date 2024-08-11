<?php
session_start();
include 'config.php'; // Archivo de configuración para la conexión a la base de datos

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    $_SESSION['error_message'] = "Usuario no autenticado.";
    header("Location: login.php");
    exit();
}

// Obtener la información actual del usuario para mostrar en el formulario
$sql = "SELECT nombre, apellido, cedula, email, role, foto_perfil, password FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $cedula = $_POST['cedula'];
    $email = $user['role'] === 'admin' ? $user['email'] : $_POST['email'];
    $role = $user['role']; // Mantener el rol actual a menos que el usuario sea admin
    $password = $_POST['password'];

    // Verificar la contraseña ingresada
    $sql = "SELECT password FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();

    if (password_verify($password, $user_data['password'])) {
        // Procesar el archivo de imagen (foto de perfil)
        $fotoPerfil = $user['foto_perfil']; // Mantener la foto de perfil actual por defecto
        if (isset($_FILES["foto_perfil"]) && $_FILES["foto_perfil"]["error"] == 0) {
            $imageFileType = strtolower(pathinfo($_FILES["foto_perfil"]["name"], PATHINFO_EXTENSION));

            // Verificar si el archivo de imagen es válido
            $check = getimagesize($_FILES["foto_perfil"]["tmp_name"]);
            if ($check !== false) {
                // Verificar el tamaño máximo del archivo (en bytes)
                if ($_FILES["foto_perfil"]["size"] <= 2 * 1024 * 1024) { // 2 MB
                    // Permitir ciertos formatos de archivo
                    if ($imageFileType == "jpg" || $imageFileType == "jpeg" || $imageFileType == "png" || $imageFileType == "gif") {
                        $fotoPerfil = file_get_contents($_FILES["foto_perfil"]["tmp_name"]);
                    } else {
                        $_SESSION['error_message'] = "Solo se permiten archivos JPG, JPEG, PNG y GIF.";
                        header("Location: editar-perfil.php");
                        exit();
                    }
                } else {
                    $_SESSION['error_message'] = "El tamaño del archivo es demasiado grande. El máximo permitido es 2 MB.";
                    header("Location: editar-perfil.php");
                    exit();
                }
            } else {
                $_SESSION['error_message'] = "El archivo no es una imagen válida.";
                header("Location: editar-perfil.php");
                exit();
            }
        }

        // Actualizar los datos en la base de datos
        if ($fotoPerfil) {
            $sql = "UPDATE users SET nombre = ?, apellido = ?, cedula = ?, email = ?, role = ?, foto_perfil = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssi", $nombre, $apellido, $cedula, $email, $role, $fotoPerfil, $user_id);
        } else {
            $sql = "UPDATE users SET nombre = ?, apellido = ?, cedula = ?, email = ?, role = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $nombre, $apellido, $cedula, $email, $role, $user_id);
        }

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Perfil actualizado exitosamente.";
            header("Location: perfil.php"); // Redirigir al perfil del usuario
            exit();
        } else {
            $_SESSION['error_message'] = "Error al actualizar el perfil: " . $stmt->error;
            header("Location: editar-perfil.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Contraseña incorrecta.";
        header("Location: editar-perfil.php");
        exit();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- FontAwesome CSS -->
    <title>Editar Perfil</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-image: url('img/img7.webp'); 
            background-size: cover;
            background-position: center;
        }

        .container {
            background-color: #ffffff;
            color: #000000;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 80%;
            max-width: 1000px;
            display: flex;
            align-items: flex-start;
            padding: 20px;
        }

        .image-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 20px;
        }

        .image-container img.profile-image {
            width: 150px; /* Tamaño del círculo */
            height: 150px; /* Tamaño del círculo */
            border-radius: 50%; /* Círculo */
            object-fit: cover;
            border: 3px solid #ddd; /* Borde de la imagen */
        }

        .form-container {
            flex: 2;
            display: flex;
            flex-direction: column;
        }

        h3 {
            margin-bottom: 20px;
            text-align: center;
            color: #333;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        .input-container {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .input-container label {
            width: 150px;
            text-align: right;
            margin-right: 20px;
            font-weight: bold;
            color: #333;
        }

        .input-container input,
        .input-container select,
        .input-container span {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #ffffff;
            color: #000000;
        }

        .input-container input[type="file"] {
            border: none;
            background-color: #ffffff;
            color: #000000;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        input[type="submit"],
        button {
            padding: 12px;
            border: none;
            border-radius: 5px;
            color: #ffffff;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
        }

        input[type="submit"] {
            background-color: #007bff;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        button {
            background-color: #dc3545;
            transition: background-color 0.3s;
            display: flex;
            align-items: center;
        }

        button:hover {
            background-color: #c82333;
        }

        button i {
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="image-container">
            <?php if ($user['foto_perfil']): ?>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($user['foto_perfil']); ?>" alt="Foto de Perfil" class="profile-image">
            <?php else: ?>
                <img src="path/to/default-image.jpg" alt="Foto de Perfil" class="profile-image"> <!-- Imagen predeterminada si no hay foto -->
            <?php endif; ?>
        </div>
        <div class="form-container">
            <h3>Editar Perfil</h3>
            <?php if (isset($_SESSION['success_message'])): ?>
                <p style="color: green;"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></p>
            <?php elseif (isset($_SESSION['error_message'])): ?>
                <p style="color: red;"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
            <?php endif; ?>
            <form method="post" action="editar-perfil.php" enctype="multipart/form-data">
                <div class="input-container">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($user['nombre']); ?>" required>
                </div>
                <div class="input-container">
                    <label for="apellido">Apellido:</label>
                    <input type="text" id="apellido" name="apellido" value="<?php echo htmlspecialchars($user['apellido']); ?>" required>
                </div>
                <div class="input-container">
                    <label for="cedula">Cédula:</label>
                    <input type="text" id="cedula" name="cedula" value="<?php echo htmlspecialchars($user['cedula']); ?>" required>
                </div>
                <?php if ($user['role'] != 'admin'): ?>
                <div class="input-container">
                    <label for="email">Correo Electrónico:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <?php endif; ?>
                <div class="input-container">
                    <label for="foto_perfil">Foto de Perfil:</label>
                    <input type="file" id="foto_perfil" name="foto_perfil">
                </div>
                <strong>Nota: si desea guardar los cambios introduce la contraseña</strong>
                <div class="input-container">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" placeholder="Introduce tu contraseña actual" required>
                </div>
                <input type="submit" value="Actualizar Perfil">
            </form>
            <form method="post" action="perfil.php" style="margin-top: 10px;">
                <button type="submit"><i class="fas fa-arrow-left"></i> Cancelar</button>
            </form>
        </div>
    </div>
</body>
</html>
