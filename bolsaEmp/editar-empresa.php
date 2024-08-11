<?php
session_start();
include 'config.php'; // Archivo de configuración para la conexión a la base de datos

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    $_SESSION['error_message'] = "Usuario no autenticado.";
    header("Location: login.php");
    exit();
}

// Obtener la información del usuario
$sql_get_user = "SELECT email, role FROM users WHERE id = ?";
$stmt_get_user = $conn->prepare($sql_get_user);
$stmt_get_user->bind_param("i", $user_id);
$stmt_get_user->execute();
$result_get_user = $stmt_get_user->get_result();
$user = $result_get_user->fetch_assoc();

if (!$user) {
    $_SESSION['error_message'] = "Usuario no encontrado.";
    header("Location: login.php");
    exit();
}

$user_email = $user['email'];
$user_role = $user['role'];

// Obtener la información actual de la empresa usando el email del usuario
$sql_get_empresa = "SELECT * FROM empresas WHERE email = ?";
$stmt_get_empresa = $conn->prepare($sql_get_empresa);
$stmt_get_empresa->bind_param("s", $user_email);
$stmt_get_empresa->execute();
$result_get_empresa = $stmt_get_empresa->get_result();
$empresa = $result_get_empresa->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $razon_social = $_POST['razon_social'];
    $ruc = $user_role === 'admin' ? $_POST['ruc'] : $empresa['ruc'];
    $provincia = $_POST['provincia'];
    $canton = $_POST['canton'];
    $telefono = $_POST['telefono'];
    $email_empresa = $user_role === 'admin' ? $_POST['email_empresa'] : $empresa['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verificar si la contraseña es correcta si se proporciona
    if (!empty($password) && $password !== $confirm_password) {
        $_SESSION['error_message'] = "Las contraseñas no coinciden.";
    } else {
        // Actualizar la información de la empresa
        if ($empresa) {
            // La empresa ya existe, así que actualizamos
            $sql_update_empresa = "UPDATE empresas SET 
                                    razon_social = ?, 
                                    ruc = ?, 
                                    provincia = ?, 
                                    canton = ?, 
                                    telefono = ?, 
                                    email = ?, 
                                    fecha_registro = NOW() 
                                WHERE email = ?";
            $stmt_update_empresa = $conn->prepare($sql_update_empresa);
            $stmt_update_empresa->bind_param("sssssss", $razon_social, $ruc, $provincia, $canton, $telefono, $email_empresa, $email_empresa);

            if ($stmt_update_empresa->execute()) {
                $_SESSION['success_message'] = "Empresa actualizada exitosamente.";
            } else {
                $_SESSION['error_message'] = "Error al actualizar la empresa: " . $stmt_update_empresa->error;
            }
        } else {
            // La empresa no existe, la insertamos
            $sql_insert_empresa = "INSERT INTO empresas (razon_social, ruc, provincia, canton, telefono, email, fecha_registro) 
                                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt_insert_empresa = $conn->prepare($sql_insert_empresa);
            $stmt_insert_empresa->bind_param("ssssss", $razon_social, $ruc, $provincia, $canton, $telefono, $email_empresa);

            if ($stmt_insert_empresa->execute()) {
                $_SESSION['success_message'] = "Empresa registrada exitosamente.";
            } else {
                $_SESSION['error_message'] = "Error al registrar la empresa: " . $stmt_insert_empresa->error;
            }
        }

        // Actualizar la contraseña en la tabla users si se proporciona una nueva contraseña
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql_update_password = "UPDATE users SET password = ? WHERE id = ?";
            $stmt_update_password = $conn->prepare($sql_update_password);
            $stmt_update_password->bind_param("si", $hashed_password, $user_id);

            if ($stmt_update_password->execute()) {
                $_SESSION['success_message'] .= " Contraseña actualizada exitosamente.";
            } else {
                $_SESSION['error_message'] .= " Error al actualizar la contraseña: " . $stmt_update_password->error;
            }
        }

        header("Location: editar-empresa.php");
        exit();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Editar Empresa</title>
    <style>
        html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: Arial, sans-serif;
            background-image: url('img/fondo3.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .container {
            display: flex;
            height: 100vh;
            align-items: center;
            justify-content: center;
        }
        .form-container {
            width: 100%;
            max-width: 600px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .form-container form {
            display: flex;
            flex-direction: column;
        }
        .input-container {
            margin-bottom: 15px;
        }
        .input-container label {
            display: block;
            margin-bottom: 5px;
        }
        .input-container input, .input-container textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .input-container input[readonly] {
            background-color: #f9f9f9;
            cursor: not-allowed;
        }
        .success-message, .error-message {
            color: green;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .error-message {
            color: red;
        }
        input[type="submit"], button {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        input[type="submit"] {
            background-color: #28a745;
            color: white;
            margin-top: 10px;
        }
        button {
            background-color: #dc3545;
            color: white;
            margin-top: 10px;
        }
        button:hover, input[type="submit"]:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h3>Editar Empresa</h3>
            <?php if (isset($_SESSION['success_message'])): ?>
                <p class="success-message"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></p>
            <?php elseif (isset($_SESSION['error_message'])): ?>
                <p class="error-message"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
            <?php endif; ?>
            <form method="post" action="editar-empresa.php">
                <div class="input-container">
                    <label for="razon_social"><i class="fas fa-building"></i> Razón Social:</label>
                    <input type="text" id="razon_social" name="razon_social" value="<?php echo htmlspecialchars($empresa['razon_social'] ?? ''); ?>" required>
                </div>
                <div class="input-container">
                    <label for="ruc"><i class="fas fa-id-card"></i> RUC:</label>
                    <input type="text" id="ruc" name="ruc" value="<?php echo htmlspecialchars($empresa['ruc'] ?? ''); ?>" <?php echo $user_role === 'admin' ? '' : 'readonly'; ?>>
                </div>
                <div class="input-container">
                    <label for="provincia"><i class="fas fa-map-marker-alt"></i> Provincia:</label>
                    <input type="text" id="provincia" name="provincia" value="<?php echo htmlspecialchars($empresa['provincia'] ?? ''); ?>" required>
                </div>
                <div class="input-container">
                    <label for="canton"><i class="fas fa-map-marker-alt"></i> Cantón:</label>
                    <input type="text" id="canton" name="canton" value="<?php echo htmlspecialchars($empresa['canton'] ?? ''); ?>" required>
                </div>
                <div class="input-container">
                    <label for="telefono"><i class="fas fa-phone"></i> Teléfono Convencional:</label>
                    <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($empresa['telefono'] ?? ''); ?>" required>
                </div>
                <div class="input-container">
                    <label for="email_empresa"><i class="fas fa-envelope"></i> Email:</label>
                    <input type="email" id="email_empresa" name="email_empresa" value="<?php echo htmlspecialchars($empresa['email'] ?? ''); ?>" <?php echo $user_role === 'admin' ? '' : 'readonly'; ?>>
                </div>
                <div class="input-container">
                    <label for="password"><i class="fas fa-lock"></i> Contraseña:</label>
                    <input type="password" id="password" name="password">
                </div>
                <div class="input-container">
                    <label for="confirm_password"><i class="fas fa-lock"></i> Confirmar Contraseña:</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                </div>
                <p><strong>Nota:</strong> Solo debe ingresar la contraseña si desea cambiarla.</p>
                <input type="submit" value="Guardar Cambios">
               
               
                <button type="button" onclick="window.history.back()"><i class="fas fa-times"></i> Cancelar</button>
            </form>
        </div>
    </div>
</body>
</html>
