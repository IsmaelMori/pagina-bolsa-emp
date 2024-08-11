<?php
session_start();
include 'config.php';

$user_id = $_SESSION['user_id'] ?? null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($user_id)) {
    $experience = $_POST['experience'];
    $experience_id = $_POST['experience_id'] ?? null;

    if (isset($_POST['delete'])) {
        // Eliminar experiencia existente
        $sql = "DELETE FROM experiences WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $experience_id, $user_id);
        $message = "Experiencia eliminada exitosamente.";
    } elseif ($experience_id) {
        // Editar experiencia existente
        $sql = "UPDATE experiences SET experience = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $experience, $experience_id, $user_id);
        $message = "Experiencia actualizada exitosamente.";
    } else {
        // Añadir nueva experiencia
        $sql = "INSERT INTO experiences (user_id, experience) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $user_id, $experience);
        $message = "Experiencia añadida exitosamente.";
    }

    if ($stmt->execute()) {
        $_SESSION['success_message'] = $message;
        header("Location: add_experience.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error: " . $stmt->error;
    }
}

$experiences = [];
if (isset($user_id)) {
    // Consultar experiencias existentes
    $sql = "SELECT id, experience FROM experiences WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $experiences[] = $row;
    }
}

// Pre-cargar experiencia para edición
$edit_experience_id = $_GET['edit'] ?? null;
$edit_experience = '';
if ($edit_experience_id) {
    $sql = "SELECT experience FROM experiences WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $edit_experience_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $edit_experience = $row['experience'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir/Editar Experiencia</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            background-image: url('img/fondo4.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        .container a.back-link {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 16px;
            color: rgb(189,1,2);
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        .container a.back-link:hover {
            text-decoration: underline;
        }
        .container a.back-link .fa {
            margin-right: 8px;
        }
        h1, h2 {
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
        }
        textarea {
            margin-bottom: 15px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            resize: vertical;
        }
        input[type="hidden"] {
            display: none;
        }
        button {
            padding: 10px 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            font-size: 16px;
            transition: background-color 0.3s ease;
            background-color:rgb(129,1,2);;
            color: #ffffff;
        }
        button:hover {
            background-color: rgb(189,1,2);;
        }
        button .fa {
            margin-right: 8px;
        }
        .experience-list {
            margin-top: 20px;
        }
        .experience-list ul {
            list-style-type: none;
            padding: 0;
        }
        .experience-list li {
            margin-bottom: 15px;
            padding: 15px;
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .experience-list .action-buttons {
            display: flex;
            gap: 10px;
        }
        .experience-list .edit-button, .experience-list .delete-button {
            padding: 5px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            transition: background-color 0.3s ease;
        }
        .experience-list .edit-button {
            background-color: #28a745;
            color: #ffffff;
        }
        .experience-list .edit-button:hover {
            background-color: #218838;
        }
        .experience-list .delete-button {
            background-color: #dc3545;
            color: #ffffff;
        }
        .experience-list .delete-button:hover {
            background-color: #c82333;
        }
        .experience-list .fa {
            margin-right: 6px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="perfil.php" class="back-link"><i class="fa fa-arrow-left"></i> Volver al Perfil</a>
        <h1>Añadir/Editar Experiencia</h1>
        <?php if (isset($_SESSION['success_message'])): ?>
            <p style="color: green;"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></p>
        <?php elseif (isset($_SESSION['error_message'])): ?>
            <p style="color: red;"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
        <?php endif; ?>
        <form action="add_experience.php" method="post">
            <textarea name="experience" rows="6" placeholder="Descripción de la experiencia" required><?php echo htmlspecialchars($edit_experience); ?></textarea>
            <input type="hidden" name="experience_id" value="<?php echo htmlspecialchars($edit_experience_id); ?>">
            <button type="submit"><?php echo $edit_experience_id ? '<i class="fa fa-save"></i> Actualizar Experiencia' : '<i class="fa fa-plus"></i> Guardar Experiencia'; ?></button>
        </form>
        <div class="experience-list">
            <h2>Experiencias Registradas</h2>
            <?php if (!empty($experiences)): ?>
                <ul>
                    <?php foreach ($experiences as $exp): ?>
                        <li>
                            <?php echo htmlspecialchars($exp['experience']); ?>
                            <div class="action-buttons">
                                <a href="add_experience.php?edit=<?php echo $exp['id']; ?>" class="edit-button"><i class="fa fa-edit"></i> Editar</a>
                                <form action="add_experience.php" method="post" style="display:inline;">
                                    <input type="hidden" name="experience_id" value="<?php echo $exp['id']; ?>">
                                    <input type="hidden" name="experience" value="<?php echo htmlspecialchars($exp['experience']); ?>">
                                    <input type="hidden" name="delete" value="1">
                                    <button type="submit" class="delete-button" onclick="return confirm('¿Estás seguro de que deseas eliminar esta experiencia?');"><i class="fa fa-trash"></i> Eliminar</button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No hay experiencias registradas.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
