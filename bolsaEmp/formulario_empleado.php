<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <title>Registro como Empleado</title>
    <style>
body {
    background-color: #fff;
    color: #ffffff;
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background-image: url('img/fondo4.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
}

.container {
    display: flex;
    width: 80%;
    max-width: 1200px;
    background-color: rgb(250,250,250);
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(250, 250, 250);
}

.image-container {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-right: 20px;
    background-color: #fff;
    padding: 10px;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 0 10px rgba(30, 30, 30, 0.1);
}

.image-preview {
    width: 100%;
    height: auto;
    max-width: 300px;
    border-radius: 10px;
}

.form-container {
    flex: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
    color:rgb(250,250,250) /* Centra el formulario horizontalmente */
}

h3 {
    margin-bottom: 20px;
    text-align: center;
    color: #fff;
}

form {
    width: 100%;
    max-width: 600px; /* Limita el ancho del formulario */
}

label{
    color: #000
}

h3{
    color: #000
}

p{
    color: #000
}
.input-container {
    margin-bottom: 15px;
    position: relative;
    width: 100%;
    color:rgb(250,250,250) /* Asegura que el contenedor de entrada use el ancho completo */
}

.input-container input, 
.input-container select {
    width: calc(100% - 40px); /* Ajusta el ancho para dejar espacio para el icono */
    padding: 15px 15px 15px 40px; /* Ajusta el padding para el icono */
    border: 1px solid #333;
    border-radius: 5px;
    background-color: #fff;
    color: #000;
    margin-top: 5px;
    box-sizing: border-box; /* Incluye padding y border en el ancho total */
}

.input-container i {
    position: absolute;
    left: 10px;
    top: 75%;
    transform: translateY(-75%);
    color: #000;
    pointer-events: none; /* Evita que el icono interfiera con el clic en el campo */
}

.input-container select {
    padding-left: 40px; /* Ajusta el padding para el icono */
}

/* Ajuste específico para el icono del campo de rol */
.input-container i.fa-user-tag {
    top: 3%; /* Mueve el icono un poco más arriba */
    transform: translateY(-3%); /* Ajusta la posición vertical del icono */
}

input[type="file"] {
    border: none;
    background-color: transparent;
    color: #ffffff;
    margin-top: 10px;
    display: block;
}

input[type="submit"], 
button {
    padding: 10px;
    border: none;
    border-radius: 5px;
    background-color: rgb(189,1,2);
    color: #ffffff;
    font-size: 16px;
    cursor: pointer;
    width: 100%;
    margin-top: 10px;
}

input[type="submit"]:hover, 
button:hover {
    background-color: rgb(129,1,2);
}

button {
    background-color: #444444;
}

button:hover {
    background-color: #555555;
}
    </style>
</head>
<body>
    <div class="container">
        <div class="image-container">
            <img id="imagePreview" src="#" alt="Vista previa de la imagen" class="image-preview" style="display: none;">
        </div>
        <div class="form-container">
            <h3>Registro como Empleado</h3>
            <form method="post" action="procesar_empleado.php" enctype="multipart/form-data">
                <div class="input-container">
                    <label for="nombre">Nombre:</label>
                    <i class="fas fa-user"></i>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div class="input-container">
                    <label for="apellido">Apellido:</label>
                    <i class="fas fa-user"></i>
                    <input type="text" id="apellido" name="apellido" required>
                </div>
                <div class="input-container">
                    <label for="cedula">Cédula:</label>
                    <i class="fas fa-id-card"></i>
                    <input type="text" id="cedula" name="cedula" required>
                </div>
                <div class="input-container">
                    <label for="email">Correo Electrónico:</label>
                    <i class="fas fa-envelope"></i>
                    <input type="text" id="email" name="email" required>
                </div>
                <div class="input-container">
                    <label for="password">Contraseña:</label>
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="input-container">
                    <label for="foto_perfil">Foto de Perfil:</label>
                    <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*" onchange="previewImage(event)">
                </div>
                <div class="input-container">
                    <label for="role">Rol:</label>
                    <i class="fas fa-user-tag"></i>
                    <select id="role" name="role" required onchange="toggleRucField()">
                        <option value="empleado">Empleado</option>
                        <option value="empleador">Empleador</option>
                    </select>
                </div>
                <div id="ruc-container" class="input-container" style="display: none;">
                    <label for="ruc">RUC:</label>
                    <i class="fas fa-id-card"></i>
                    <input type="text" id="ruc" name="ruc" placeholder="Ingrese el RUC" maxlength="13" oninput="validateRuc(this)">
                </div>
                <input type="submit" value="Registrarse">
                <p><strong>Nota:</strong>Si ya tienes cuenta haz click en regresar.</p>
                <button type="button" onclick="window.history.back()">Regresar</button>
            </form>
        </div>
       
    </div>
    
   
    
    <script>
        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function() {
                var output = document.getElementById('imagePreview');
                output.src = reader.result;
                output.style.display = 'block'; // Muestra la imagen solo si se ha cargado
            }
            reader.readAsDataURL(event.target.files[0]); // Lee el archivo como una URL de datos
        }

        function toggleRucField() {
            var role = document.getElementById('role').value;
            var rucContainer = document.getElementById('ruc-container');
            if (role === 'empleador') {
                rucContainer.style.display = 'block';
            } else {
                rucContainer.style.display = 'none';
            }
        }

        function validateRuc(input) {
            // Solo permite caracteres numéricos y limita la longitud a 13 caracteres
            var value = input.value;
            if (value.length > 13) {
                input.value = value.slice(0, 13);
            }
            input.value = value.replace(/[^0-9]/g, ''); // Elimina caracteres no numéricos
        }
    </script>
</body>
</html>
