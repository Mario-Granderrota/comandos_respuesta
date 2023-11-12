<?php
// index.php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
ob_start(); // Inicia el búfer de salida.
session_start(); // Inicia la sesión.

require_once 'config.php'; // Incluye la configuración.
require_once 'funcionesLenguaje.php'; // Incluye las funciones de procesamiento.

$respuesta = '';
$error = ''; // Variable para almacenar mensajes de error.
$mensaje = ''; // Variable para almacenar mensajes de éxito.

// Verifica si se ha presionado el botón de actualizar JSON.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_json'])) {
    require_once 'jsoneador_de_texto_plano.php'; // Incluye el archivo con la función de conversión.
    
    // Llama a la función que convierte el archivo de texto a JSON.
    convertirTextoAJson('comandos.txt', 'comandos.json');
    
    // Guarda un mensaje de éxito en la sesión.
    $_SESSION['mensaje'] = "El archivo JSON ha sido actualizado con éxito.";
    header('Location: index.php'); // Redirige a index.php para evitar reenvíos del formulario.
    exit();
}

// Verifica si hay un comando enviado a través del formulario.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['pregunta'])) {
    $pregunta = trim($_POST['pregunta']);
    
    // Comprobación de longitud mínima de la pregunta.
    if (mb_strlen($pregunta) < 3) {
        $_SESSION['error'] = "La pregunta debe tener al menos 3 caracteres.";
    } else {
        try {
            // Obtiene los comandos y sinónimos desde el archivo.
            $comandos = obtenerComandos();
            $sinonimos = obtenerSinonimos();
            $palabrasClave = extraerPalabrasClave($pregunta);

            // Obtener la lista de comandos coincidentes
            $comandosCoincidentes = buscarEnComandos($comandos, $palabrasClave, $sinonimos);

            // Elegir la mejor respuesta utilizando la pregunta y los comandos coincidentes
            $respuesta = elegirMejorRespuesta($pregunta, $comandosCoincidentes, $sinonimos);
            
            $_SESSION['respuesta'] = $respuesta; // Guarda la respuesta en la sesión.
            header('Location: index.php'); // Redirige a index.php para evitar reenvíos del formulario.
            exit();
        } catch (Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
        }
    }
}

// Verifica si hay un mensaje almacenado en la sesión y lo muestra.
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']); // Limpia el mensaje de la sesión.
}

// Verifica si hay una respuesta o un error almacenado en la sesión y los muestra.
if (isset($_SESSION['respuesta'])) {
    $respuesta = $_SESSION['respuesta'];
    unset($_SESSION['respuesta']); // Limpia la respuesta de la sesión.
} elseif (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']); // Limpia el error de la sesión.
}
ob_end_flush(); // Envía la salida del búfer al navegador y lo desactiva.
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formulario de Comandos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 800px; /* Aumentado de 600px a 800px */
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .pregunta, .respuesta, .actualizacion {
            margin-bottom: 20px;
        }
        .respuesta p, .actualizacion p {
            background-color: #e4f9f5;
            padding: 15px; /* Padding aumentado para más espacio */
            border-left: 5px solid #34c759;
            color: #333;
            font-size: 1.1em; /* Fuente más grande */
        }
        button {
            background-color: #34c759;
            color: #fff;
            border: none;
            padding: 15px 30px; /* Padding aumentado para botones más grandes */
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em; /* Fuente más grande */
        }
        button:hover {
            background-color: #2ca345;
        }
        .oculto {
            display: none;
        }
        .instrucciones {
            font-size: 1em; /* Fuente más grande */
            margin-bottom: 20px;
        }
        textarea {
            width: 100%;
            height: 200px; /* Altura aumentada */
            margin-bottom: 20px;
            font-size: 1em; /* Fuente más grande */
            padding: 10px; /* Padding para el texto dentro del textarea */
        }
        input[type="text"] {
            width: calc(100% - 22px);
            padding: 10px; /* Padding para más espacio */
            font-size: 1em; /* Fuente más grande */
            margin-bottom: 10px; /* Espacio extra antes del botón */
        }
    </style>
</head>
<body>

<div class="container">
    <div class="instrucciones" id="instrucciones">
        <p>Por favor, introduce tu comando o pregunta en el campo de texto y presiona "Enviar pregunta" para obtener una respuesta.</p>
    </div>

    <div class="pregunta" id="pregunta">
        <form action="index.php" method="post">
            <label for="preguntaInput">Escribe tu pregunta:</label>
            <input type="text" id="preguntaInput" name="pregunta" required>
            <button type="submit">Enviar pregunta</button>
        </form>
    </div>

    <div class="respuesta" id="respuesta">
        <?php if ($respuesta): ?>
            <p>Respuesta: <?= htmlspecialchars($respuesta) ?></p>
        <?php elseif ($error): ?>
            <p>Error: <?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
    </div>

    <div class="actualizacion oculto" id="seccionActualizacion">
        <p>Instrucciones: Si deseas subir tu propio archivo de comandos y respuestas, asegúrate de que el archivo de comandos esté formateado correctamente, tal cómo aparece en este cuadro (El cuadro muestra los comandos y respuestas actuales). Cada comando debe estar separado de su respectiva respuesta por dos puntos y entre cada par comando:respuesta separados por una línea en blanco.</p>
        <textarea id="contenidoComandos" readonly>
            <?php
            // Leer y mostrar el contenido de comandos.txt
            echo htmlspecialchars(file_get_contents('comandos.txt'));
            ?>
        </textarea>
          <form action="cargador_comandos.php" method="post" enctype="multipart/form-data">
              <input type="file" name="archivocomandos">
              <button type="submit">Subir Archivo</button>
          </form>

    </div>

    <div class="actualizar">
        <button id="botonActualizar">Actualizar Comandos</button>
    </div>
</div>

<script>
    document.getElementById('botonActualizar').addEventListener('click', function() {
        document.getElementById('instrucciones').classList.add('oculto');
        document.getElementById('pregunta').classList.add('oculto');
        document.getElementById('respuesta').classList.add('oculto');
        document.getElementById('seccionActualizacion').classList.remove('oculto');
    });
</script>

</body>
</html>
