<?php
session_start();
include 'config.php';

if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'empleador') {
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $empresa = $_POST['empresa'];
    $ubicacion = $_POST['ubicacion'];
    $user_id = $_SESSION['user_id'];

    $sql = "INSERT INTO empleos (titulo, descripcion, empresa, ubicacion, user_id) VALUES ('$titulo', '$descripcion', '$empresa', '$ubicacion', '$user_id')";

    if ($conn->query($sql) === TRUE) {
        echo "Nuevo empleo publicado con éxito";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
} else {
    echo "No tiene permisos para publicar empleos.";
}

$conn->close();
?>
