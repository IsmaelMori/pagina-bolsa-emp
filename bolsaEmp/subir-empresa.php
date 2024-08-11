<?php
session_start();
include 'config.php';

if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'empleador') {
    $nombre_empresa = $_POST['nombre_empresa'];
    $descripcion_empresa = $_POST['descripcion_empresa'];
    $ubicacion_empresa = $_POST['ubicacion_empresa'];
    $user_id = $_SESSION['user_id'];

    $sql = "INSERT INTO empresas (nombre_empresa, descripcion_empresa, ubicacion_empresa, user_id) VALUES ('$nombre_empresa', '$descripcion_empresa', '$ubicacion_empresa', '$user_id')";

    if ($conn->query($sql) === TRUE) {
        echo "Nueva empresa agregada con éxito";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
} else {
    echo "No tiene permisos para agregar empresas.";
}

$conn->close();
?>
