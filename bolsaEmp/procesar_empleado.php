<?php
session_start();

include 'config.php'; // Archivo de configuración para la conexión a la base de datos

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos del formulario
    $nombre = $_POST['nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $cedula = $_POST['cedula'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'] ?? '';
    $ruc = $_POST['ruc'] ?? '';

    // Procesar el archivo de imagen (foto de perfil)
    $uploadOk = 1;
    $fotoPerfil = null;
    if (isset($_FILES["foto_perfil"]) && $_FILES["foto_perfil"]["error"] == 0) {
        $imageFileType = strtolower(pathinfo($_FILES["foto_perfil"]["name"], PATHINFO_EXTENSION));

        // Verificar si el archivo de imagen es válido
        $check = getimagesize($_FILES["foto_perfil"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            $_SESSION['error_message'] = "El archivo no es una imagen válida.";
            $uploadOk = 0;
        }

       // Verificar el tamaño máximo del archivo (en bytes)
       if ($_FILES["foto_perfil"]["size"] > 2000000) { // 2 MB = 2,000,000 bytes
        $_SESSION['error_message'] = "El tamaño del archivo es demasiado grande. El tamaño máximo permitido es 2 MB.";
        $uploadOk = 0;
    }
        // Permitir ciertos formatos de archivo
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            $_SESSION['error_message'] = "Solo se permiten archivos JPG, JPEG, PNG y GIF.";
            $uploadOk = 0;
        }

        // Si $uploadOk está configurado como 1, procesar la imagen
        if ($uploadOk == 1) {
            $fotoPerfil = file_get_contents($_FILES["foto_perfil"]["tmp_name"]);
        }
    }

    // Si hubo errores en la subida del archivo, redirigir a la página de error
    if ($uploadOk == 0) {
        $_SESSION['error_message'] = "El archivo no se ha subido. Razón: " . $_SESSION['error_message'];
        header("Location: registro_error.php");
        exit();
    }

    // Verificar si el email o la cédula ya existen en la base de datos
    $checkSql = "SELECT * FROM users WHERE email = ? OR cedula = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ss", $email, $cedula);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $_SESSION['error_message'] = "El email o la cédula ya están registrados.";
        header("Location: registro_error.php");
        exit();
    }

    // Guardar los datos en la tabla `users`
    $sql = "INSERT INTO users (nombre, apellido, cedula, email, password, role, foto_perfil) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        $_SESSION['error_message'] = "Error en la preparación de la consulta: " . $conn->error;
        header("Location: registro_error.php");
        exit();
    }

    // Vincular parámetros
    $stmt->bind_param("ssssssb", $nombre, $apellido, $cedula, $email, $password, $role, $fotoPerfil);

    // Enviar el contenido del blob
    if ($fotoPerfil !== null) {
        $stmt->send_long_data(6, $fotoPerfil);
    }

    if ($stmt->execute()) {
        // Guardar el RUC en la tabla `empresas` si el rol es 'empleador'
        if ($role === 'empleador' && !empty($ruc)) {
            // Insertar o actualizar la información de la empresa
            $empresaSql = "INSERT INTO empresas (ruc, email) VALUES (?, ?) ON DUPLICATE KEY UPDATE email = ?";
            $empresaStmt = $conn->prepare($empresaSql);
            $empresaStmt->bind_param("sss", $ruc, $email, $email);
            if (!$empresaStmt->execute()) {
                $_SESSION['error_message'] = "Error al registrar el RUC y el email en la tabla de empresas: " . $empresaStmt->error;
                header("Location: registro_error.php");
                exit();
            }
        }

        $_SESSION['success_message'] = "¡Registrado exitosamente como $role!";
        header("Location: index.php"); // Redirigir al área protegida
        exit();
    } else {
        $_SESSION['error_message'] = "Error al registrar como $role: " . $stmt->error;
        header("Location: registro_error.php"); // Redirigir a una página de error de registro
        exit();
    }
}
?>
