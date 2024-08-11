
<?php
session_start();
include 'config.php';

if (isset($_GET['id'])) {
    $notification_id = (int)$_GET['id'];

    // Actualizar el estado de lectura
    $sql_update_notification = "UPDATE notificaciones SET leida = 1 WHERE id = ?";
    $stmt_update_notification = $conn->prepare($sql_update_notification);
    $stmt_update_notification->bind_param("i", $notification_id);
    $stmt_update_notification->execute();

    // Redirigir al empleador
    header("Location: buzon.php");
    exit();
}

$conn->close();
?>
