<?php
session_start();
include 'config.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoger datos del formulario
    $hobbies = $_POST['hobbies'];
    $time_spent = $_POST['time_spent'];
    $specialization = $_POST['specialization'];

    // Manejo del archivo PDF
    $resume_path = '';

    if (isset($_FILES['resume']) && $_FILES['resume']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['resume']['tmp_name'];
        $fileName = $_FILES['resume']['name'];
        $fileSize = $_FILES['resume']['size'];
        $fileType = $_FILES['resume']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        
        // Verificar que el archivo sea un PDF
        if ($fileExtension == 'pdf') {
            // Verifica y crea el directorio si no existe
            $uploadFileDir = './uploaded_files/';
            if (!file_exists($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }
            $dest_path = $uploadFileDir . $fileName;
            
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $resume_path = $dest_path;
            } else {
                echo "Error al mover el archivo subido.";
                exit;
            }
        } else {
            echo "Solo se permiten archivos PDF.";
            exit;
        }
    }

    // Actualizar los datos en la base de datos
    if ($resume_path) {
        $sql = "UPDATE users SET hobbies = ?, time_spent = ?, resume = ?, specialization = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $hobbies, $time_spent, $resume_path, $specialization, $user_id);
    } else {
        $sql = "UPDATE users SET hobbies = ?, time_spent = ?, specialization = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $hobbies, $time_spent, $specialization, $user_id);
    }

    if ($stmt->execute()) {
        echo "Información actualizada correctamente.";
    } else {
        echo "Error al actualizar la información.";
    }
}

// Obtener los datos actuales del usuario
$sql = "SELECT hobbies, time_spent, resume, specialization FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

$hobbies = $user_data['hobbies'];
$time_spent = $user_data['time_spent'];
$resume = $user_data['resume'];
$specialization = $user_data['specialization'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Información Adicional</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fff; /* Fondo oscuro */
            color: #e0e0e0; /* Texto claro */
            margin: 0;
            padding: 20px;
            background-image: url('img/fondo4.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .form-container {
            background-color: #fff; /* Fondo del contenedor */
            padding: 50px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3); /* Sombra más pronunciada */
            max-width: 600px;
            margin: auto;
        }
        .form-container h1 {
            margin-bottom: 20px;
            color: #000; /* Título claro */
        }
        .form-container label {
            display: block;
            margin: 10px 0 5px;
            color: #000; /* Etiquetas en color claro */
        }
        .form-container input, .form-container textarea, .form-container select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #555; /* Borde gris oscuro */
            border-radius: 5px;
            background-color: #fff; /* Fondo del input/textarea/select */
            color: #000; /* Texto claro dentro del input/textarea/select */
        }
        .form-container input[type="submit"] {
            background-color: rgb(189, 1, 2); /* Fondo del botón */
            color: #fff; /* Texto del botón */
            border: none;
            padding: 15px;
            cursor: pointer;
        }
        .form-container input[type="submit"]:hover {
            background-color: rgb(259, 20, 24); /* Fondo del botón al pasar el cursor */
        }
        .form-container .file-preview {
            margin-top: 10px;
        }
        .form-container .file-preview embed {
            width: 100%;
            height: 600px;
        }
        .form-container .btn-back {
            display: inline-block;
            background-color: #6c757d;
            color: #fff;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 10px;
            text-align: center;
        }
        .form-container .btn-back:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Editar Información Adicional</h1>
        <form action="" method="POST" enctype="multipart/form-data">
            <label for="hobbies">Hobbies:</label>
            <textarea id="hobbies" name="hobbies" rows="4"><?php echo htmlspecialchars($hobbies); ?></textarea>
            
            <label for="time_spent">¿Qué haces en tu tiempo libre?</label>
            <textarea id="time_spent" name="time_spent" rows="4"><?php echo htmlspecialchars($time_spent); ?></textarea>
            
            <label for="resume">Hoja de vida (PDF):</label>
            <input type="file" id="resume" name="resume" accept=".pdf">
            
            <?php if ($resume): ?>
                <div class="file-preview">
                    <h3>Vista previa de la Hoja de Vida:</h3>
                    <embed src="<?php echo htmlspecialchars($resume); ?>" type="application/pdf">
                </div>
            <?php endif; ?>
            
            <label for="specialization">Especialización:</label>
            <select id="specialization" name="specialization">
                <option value="Desarrollo de Software" <?php if ($specialization == 'Desarrollo de Software') echo 'selected'; ?>>Desarrollo de Software</option>
                <option value="Administración" <?php if ($specialization == 'Administración') echo 'selected'; ?>>Administración</option>
                <option value="Diseño" <?php if ($specialization == 'Diseño') echo 'selected'; ?>>Diseño</option>
            </select>
            
            <input type="submit" value="Actualizar Información">
        </form>
        <a href="perfil.php" class="btn-back">Volver al Perfil</a>
    </div>
</body>
</html>
