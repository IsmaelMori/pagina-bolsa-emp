<?php
session_start();
include 'config.php'; // Archivo de configuración para la conexión a la base de datos

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos del formulario
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $cedula = $_POST['cedula'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $user_id = $_SESSION['user_id'];

    // Procesar el archivo de imagen (foto de perfil)
    $fotoPerfil = null;
    if (isset($_FILES["foto_perfil"]) && $_FILES["foto_perfil"]["error"] == 0) {
        $imageFileType = strtolower(pathinfo($_FILES["foto_perfil"]["name"], PATHINFO_EXTENSION));

        // Verificar si el archivo de imagen es válido
        $check = getimagesize($_FILES["foto_perfil"]["tmp_name"]);
        if ($check !== false) {
            // Verificar el tamaño máximo del archivo (en bytes)
            if ($_FILES["foto_perfil"]["size"] <= 900000) {
                // Permitir ciertos formatos de archivo
                if ($imageFileType == "jpg" || $imageFileType == "jpeg" || $imageFileType == "png" || $imageFileType == "gif") {
                    $fotoPerfil = file_get_contents($_FILES["foto_perfil"]["tmp_name"]);
                } else {
                    $_SESSION['error_message'] = "Solo se permiten archivos JPG, JPEG, PNG y GIF.";
                    header("Location: editar-perfil.php");
                    exit();
                }
            } else {
                $_SESSION['error_message'] = "El tamaño del archivo es demasiado grande.";
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
}

$conn->close();
?>
