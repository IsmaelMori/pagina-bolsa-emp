<?php
session_start();

include 'config.php'; // Archivo de configuración para la conexión a la base de datos

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos del formulario
    $nombreEmpresa = $_POST['nombre_empresa'];
    $descripcionEmpresa = $_POST['descripcion_empresa'];
    $ubicacionEmpresa = $_POST['ubicacion_empresa'];
    $email = $_POST['email'];

    // Procesar el archivo de imagen (logo)
    $uploadOkLogo = 1;
    $logo = null;
    if (isset($_FILES["logo"]) && $_FILES["logo"]["error"] == 0) {
        $imageFileType = strtolower(pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION));

        // Verificar si el archivo de imagen es válido
        $check = getimagesize($_FILES["logo"]["tmp_name"]);
        if ($check !== false) {
            $uploadOkLogo = 1;
        } else {
            $_SESSION['error_message'] = "El archivo de logo no es una imagen válida.";
            $uploadOkLogo = 0;
        }

        // Verificar el tamaño máximo del archivo (en bytes)
        if ($_FILES["logo"]["size"] > 900000) {
            $_SESSION['error_message'] = "El tamaño del archivo de logo es demasiado grande.";
            $uploadOkLogo = 0;
        }

        // Permitir ciertos formatos de archivo
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            $_SESSION['error_message'] = "Solo se permiten archivos JPG, JPEG, PNG y GIF para el logo.";
            $uploadOkLogo = 0;
        }

        // Si $uploadOkLogo está configurado como 1, procesar la imagen
        if ($uploadOkLogo == 1) {
            $logo = file_get_contents($_FILES["logo"]["tmp_name"]);
        }
    } else {
        $_SESSION['error_message'] = "Error al subir el archivo de logo.";
        $uploadOkLogo = 0;
    }

    // Si hubo errores en la subida del archivo de logo, redirigir a la página de error
    if ($uploadOkLogo == 0) {
        $_SESSION['error_message'] = "El archivo no se ha subido. Razón: " . $_SESSION['error_message'];
        header("Location: registro_error.php");
        exit();
    }

    // Guardar los datos en la base de datos
    $sql = "INSERT INTO empresas (nombre_empresa, descripcion_empresa, ubicacion_empresa, email, fecha_registro, logo) 
            VALUES (?, ?, ?, ?, NOW(), ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $nombreEmpresa, $descripcionEmpresa, $ubicacionEmpresa, $email, $logo);

    if ($stmt->execute() === TRUE) {
        $_SESSION['success_message'] = "¡Registrado exitosamente como empleador!";
        header("Location: jobs.php"); // Redirigir a una página de registro exitoso
        exit();
    } else {
        $_SESSION['error_message'] = "Error al registrar como empleador: " . $stmt->error;
        header("Location: registro_error.php"); // Redirigir a una página de error de registro
        exit();
    }
}
?>
