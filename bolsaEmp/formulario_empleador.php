<?php
session_start();
include 'config.php';

// Verificar si el usuario está autenticado
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Consultar el rol del usuario
    $sql_role = "SELECT email, role FROM users WHERE id = ?";
    $stmt_role = $conn->prepare($sql_role);
    $stmt_role->bind_param("i", $user_id);
    $stmt_role->execute();
    $result_role = $stmt_role->get_result();

    if ($result_role->num_rows > 0) {
        $user = $result_role->fetch_assoc();
        $user_email = $user['email'];
        $user_role = $user['role'];
    } else {
        echo "Error: No se pudo obtener información del usuario.";
        exit();
    }

    // Consultar el RUC asociado al email del usuario, si existe
    $sql_ruc = "SELECT ruc FROM empresas WHERE email = ?";
    $stmt_ruc = $conn->prepare($sql_ruc);
    $stmt_ruc->bind_param("s", $user_email);
    $stmt_ruc->execute();
    $result_ruc = $stmt_ruc->get_result();

    if ($result_ruc->num_rows > 0) {
        $empresa = $result_ruc->fetch_assoc();
        $empresa_ruc = $empresa['ruc'];
    } else {
        $empresa_ruc = ''; // RUC por defecto si no se encuentra la empresa
    }

    // Determinar si los campos deben ser editables
    $disabled = ($user_role !== 'admin') ? 'readonly' : '';

    // Procesar la carga del archivo
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $uploadDir = 'uploads/';
        
        // Verificar si el directorio existe, si no, crearlo
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $uploadFile = $uploadDir . basename($_FILES['logo']['name']);
        $imageFileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));

        // Validar el tipo de archivo
        $validImageTypes = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
        if (in_array($imageFileType, $validImageTypes)) {
            if ($_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadFile)) {
                    echo "El archivo ". htmlspecialchars(basename($_FILES['logo']['name'])). " ha sido cargado.";

                    // Obtener otros datos del formulario
                    $nombre_empresa = $_POST['nombre_empresa'];
                    $descripcion_empresa = $_POST['descripcion_empresa'];
                    $ubicacion_empresa = $_POST['ubicacion_empresa'];
                    $ruc = $_POST['ruc'];

                    // Insertar los datos en la base de datos
                    $sql_insert = "INSERT INTO empresas (nombre_empresa, descripcion_empresa, ubicacion_empresa, email, ruc, logo) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt_insert = $conn->prepare($sql_insert);
                    $stmt_insert->bind_param("ssssss", $nombre_empresa, $descripcion_empresa, $ubicacion_empresa, $user_email, $ruc, $_FILES['logo']['name']);

                    if ($stmt_insert->execute()) {
                        echo "Datos insertados correctamente.";
                    } else {
                        echo "Error al insertar los datos: " . $stmt_insert->error;
                    }

                    $stmt_insert->close();
                } else {
                    echo "Error al mover el archivo.";
                }
            } else {
                echo "Error al cargar el archivo: " . $_FILES['logo']['error'];
            }
        } else {
            echo "Solo se permiten archivos JPEG, JPG, PNG, GIF y WEBP.";
        }
    }

    // Mostrar el formulario HTML con el email del usuario y el RUC
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <style>
            body {
                font-family: 'Arial', sans-serif;
                background-color: #ffffff;
                margin: 0;
                padding: 0;
                position: relative;
            }
            .container {
                max-width: 800px;
                margin: 40px auto;
                padding: 20px;
                background-color: #f8f9fa;
                border-radius: 10px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                position: relative;
                overflow: hidden;
                z-index: 1;
            }
            h3 {
                color: #dc3545;
                text-align: center;
            }
            label {
                display: block;
                margin: 10px 0 5px;
                font-weight: bold;
                color: #000; /* Cambiado a negro */
            }
            input[type="text"],
            input[type="file"] {
                width: 100%;
                padding: 10px;
                border: 1px solid #dc3545;
                border-radius: 5px;
                font-size: 16px;
                margin-bottom: 15px;
                transition: border-color 0.3s ease, box-shadow 0.3s ease;
                color: #000; /* Cambiado a negro */
            }
            input[type="text"]:focus {
                border-color: #007bff;
                box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
                outline: none;
            }
            input[readonly] {
                background-color: #e9ecef;
            }
            input[type="submit"],
            button {
                background-color: #dc3545;
                color: #ffffff;
                border: none;
                padding: 10px 20px;
                border-radius: 5px;
                cursor: pointer;
                font-size: 16px;
                transition: background-color 0.3s ease, transform 0.3s ease;
                margin-right: 10px;
            }
            input[type="submit"]:hover,
            button:hover {
                background-color: #c82333;
                transform: scale(1.05);
            }
            .message {
                display: none;
                position: fixed;
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                padding: 15px;
                background-color: #28a745;
                color: #ffffff;
                border-radius: 5px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                font-size: 18px;
                z-index: 999;
                animation: explode 0.5s forwards;
            }
            .message.show {
                display: block;
            }
            @keyframes explode {
                0% { transform: scale(1); opacity: 1; }
                50% { transform: scale(1.1); opacity: 0.7; }
                100% { transform: scale(0); opacity: 0; }
            }
            .back-button {
                background-color: #007bff;
                color: #ffffff;
            }
            .back-button:hover {
                background-color: #0056b3;
            }
            .preview-container {
                text-align: center;
                margin-top: 20px;
            }
            .preview-container img {
                max-width: 200px;
                max-height: 200px;
                border: 1px solid #dc3545;
                border-radius: 5px;
                margin-top: 10px;
            }
            .error-message {
                color: #dc3545;
                font-weight: bold;
                display: none;
            }
        </style>
        <script>
            function showSuccessMessage() {
                var message = document.getElementById('success-message');
                message.classList.add('show');
                setTimeout(function() {
                    message.classList.remove('show');
                }, 3000); // Mensaje se oculta después de 3 segundos
            }

            function previewImage() {
                const fileInput = document.getElementById('logo');
                const previewContainer = document.getElementById('preview-container');
                const previewImage = document.getElementById('preview-image');
                const errorMessage = document.getElementById('error-message');

                const file = fileInput.files[0];
                const validImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg'];

                if (file) {
                    if (validImageTypes.includes(file.type)) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewImage.src = e.target.result;
                            previewImage.style.display = 'block';
                            errorMessage.style.display = 'none';
                        }
                        reader.readAsDataURL(file);
                    } else {
                        previewImage.style.display = 'none';
                        errorMessage.style.display = 'block';
                    }
                }
            }
        </script>
    </head>
    <body>
        <div id="success-message" class="message">Registro Exitoso!</div>
        <div class="container">
            <h3>Registro como Empleador</h3>
            <form method="post" action="formulario_empleador.php" enctype="multipart/form-data" onsubmit="showSuccessMessage()">
                <label for="nombre_empresa">Nombre de la Empresa:</label>
                <input type="text" name="nombre_empresa" id="nombre_empresa" required><br>
                <label for="descripcion_empresa">Descripción de la Empresa:</label>
                <input type="text" name="descripcion_empresa" id="descripcion_empresa" required><br>
                <label for="ubicacion_empresa">Ubicación de la Empresa:</label>
                <input type="text" name="ubicacion_empresa" id="ubicacion_empresa" required><br>
                <label for="email">Correo Electrónico:</label>
                <input type="text" id="email" name="email" value="{$user_email}" {$disabled}><br><br>
                <label for="ruc">RUC de la Empresa:</label>
                <input type="text" name="ruc" id="ruc" value="{$empresa_ruc}" {$disabled} required><br>
               
                <label for="logo">Logo de la Empresa:</label>
                <input type="file" name="logo" id="logo" accept="image/*" onchange="previewImage()" required><br>
                <div class="preview-container" id="preview-container">
                    <img id="preview-image" src="#" alt="Previsualización de la imagen" style="display:none;">
                    <div id="error-message" class="error-message">Imagen no válida</div>
                </div>
                <input type="submit" value="Registrar">
                <button type="button" class="back-button" onclick="window.history.back()">Regresar</button>
            </form>
        </div>
    </body>
    </html>
HTML;

} else {
    header("Location: login.php");
    exit;
}

$conn->close();
?>
