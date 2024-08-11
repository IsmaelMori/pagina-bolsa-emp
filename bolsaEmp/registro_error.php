<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error de Registro</title>
</head>
<body>
    <h1>Error de Registro</h1>
    <?php
    session_start();
    if(isset($_SESSION['error_message'])) {
        echo "<p>{$_SESSION['error_message']}</p>";
        unset($_SESSION['error_message']); // Eliminar el mensaje de error para evitar que se muestre en futuras visitas
    } else {
        echo "<p>Ocurrió un error durante el proceso de registro.</p>";
    }
    
    // Mostrar el mensaje de error específico de subida de archivo, si está disponible
    if(isset($_SESSION['upload_error_message'])) {
        echo "<p>{$_SESSION['upload_error_message']}</p>";
        unset($_SESSION['upload_error_message']); // Eliminar el mensaje de error de subida de archivo
    }
    ?>
    <p>Por favor, inténtalo de nuevo más tarde.</p>
    
</body>
</html>
