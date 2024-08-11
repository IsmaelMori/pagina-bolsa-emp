<?php
session_start();
include 'config.php';

$user_id = $_SESSION['user_id'] ?? null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($user_id)) {
    $skill = $_POST['skill'];
    $skill_id = $_POST['skill_id'] ?? null;

    if (isset($_POST['delete'])) {
        // Eliminar habilidad existente
        $sql = "DELETE FROM skills WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $skill_id, $user_id);
        $message = "Habilidad eliminada exitosamente.";
    } elseif ($skill_id) {
        // Editar habilidad existente
        $sql = "UPDATE skills SET skill = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $skill, $skill_id, $user_id);
        $message = "Habilidad actualizada exitosamente.";
    } else {
        // Añadir nueva habilidad
        $sql = "INSERT INTO skills (user_id, skill) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $user_id, $skill);
        $message = "Habilidad añadida exitosamente.";
    }

    if ($stmt->execute()) {
        $_SESSION['success_message'] = $message;
        header("Location: add_skills.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error: " . $stmt->error;
    }
}

$skills = [];
if (isset($user_id)) {
    // Consultar habilidades existentes
    $sql = "SELECT id, skill FROM skills WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $skills[] = $row;
    }
}

// Pre-cargar habilidad para edición
$edit_skill_id = $_GET['edit'] ?? null;
$edit_skill = '';
if ($edit_skill_id) {
    $sql = "SELECT skill FROM skills WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $edit_skill_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $edit_skill = $row['skill'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir/Editar Habilidades</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
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
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            position: relative;
        }
        .container a.back-link {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 18px;
            color: rgb(189,1,2);
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: color 0.3s ease;
        }
        .container a.back-link:hover {
            color: rgb(129,1,2);
        }
        .container a.back-link .fa {
            margin-right: 10px;
        }
        h1, h2 {
            color: #333;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
        }
        input[type="text"] {
            margin-bottom: 15px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        input[type="text"]:focus {
            border-color: #007bff;
            outline: none;
        }
        button {
            padding: 12px 20px;
            background-color: rgb(129,1,2);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color:rgb(189,1,2);
        }
        .skill-list {
            margin-top: 20px;
        }
        .skill-list ul {
            list-style-type: none;
            padding: 0;
        }
        .skill-list li {
            margin-bottom: 15px;
            padding: 15px;
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease;
        }
        .skill-list li:hover {
            background-color: #f1f1f1;
        }
        .skill-list .edit-button, .skill-list .delete-button {
            padding: 8px 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            transition: background-color 0.3s ease;
        }
        .skill-list .edit-button {
            background-color: #28a745;
            color: #ffffff;
        }
        .skill-list .edit-button:hover {
            background-color: #218838;
        }
        .skill-list .delete-button {
            background-color: #dc3545;
            color: #ffffff;
        }
        .skill-list .delete-button:hover {
            background-color: #c82333;
        }
        .skill-list .fa {
            margin-right: 6px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="perfil.php" class="back-link"><i class="fa fa-arrow-left"></i> Volver al Perfil</a>
        <h1>Añadir/Editar Habilidad</h1>
        <?php if (isset($_SESSION['success_message'])): ?>
            <p style="color: green;"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></p>
        <?php elseif (isset($_SESSION['error_message'])): ?>
            <p style="color: red;"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
        <?php endif; ?>
        <form action="add_skills.php" method="post">
            <input type="text" name="skill" placeholder="Nombre de la habilidad" value="<?php echo htmlspecialchars($edit_skill); ?>" required>
            <input type="hidden" name="skill_id" value="<?php echo htmlspecialchars($edit_skill_id); ?>">
            <button type="submit"><?php echo $edit_skill_id ? 'Actualizar Habilidad' : 'Guardar Habilidad'; ?></button>
        </form>
        <div class="skill-list">
            <h2>Habilidades Registradas</h2>
            <?php if (!empty($skills)): ?>
                <ul>
                    <?php foreach ($skills as $skill): ?>
                        <li>
                            <?php echo htmlspecialchars($skill['skill']); ?>
                            <div>
                                <a href="add_skills.php?edit=<?php echo $skill['id']; ?>" class="edit-button"><i class="fa fa-edit"></i> Editar</a>
                                <form action="add_skills.php" method="post" style="display:inline;">
                                    <input type="hidden" name="skill_id" value="<?php echo $skill['id']; ?>">
                                    <input type="hidden" name="skill" value="<?php echo htmlspecialchars($skill['skill']); ?>">
                                    <input type="hidden" name="delete" value="1">
                                    <button type="submit" class="delete-button" onclick="return confirm('¿Estás seguro de que deseas eliminar esta habilidad?');"><i class="fa fa-trash"></i> Eliminar</button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No hay habilidades registradas.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
