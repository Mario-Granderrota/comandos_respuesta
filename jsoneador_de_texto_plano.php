<?php
// jsoneador_de_texto_plano.php

function convertirTextoAJson($archivoTexto, $archivoJson) {
    try {
        $handle = fopen($archivoTexto, "r");
        if (!$handle) {
            throw new Exception("No se pudo abrir el archivo: {$archivoTexto}");
        }

        $comandosRespuestas = [];
        $comandoActual = '';
        $respuestaActual = '';

        while (($linea = fgets($handle)) !== false) {
            $linea = trim($linea);

            if (empty($linea)) {
                // Al encontrar una línea vacía, almacenar el par comando-respuesta y resetear para el siguiente par
                if (!empty($comandoActual) && !empty($respuestaActual)) {
                    $comandosRespuestas[$comandoActual] = $respuestaActual;
                    $comandoActual = '';
                    $respuestaActual = '';
                }
                continue;
            }

            if (strpos($linea, 'Comando:') === 0) {
                $comandoActual = substr($linea, strlen('Comando: '));
            } elseif (strpos($linea, 'Respuesta:') === 0) {
                $respuestaActual = substr($linea, strlen('Respuesta: '));
            }
        }

        fclose($handle);

        // Asegurarse de agregar el último par comando-respuesta si existe
        if (!empty($comandoActual) && !empty($respuestaActual)) {
            $comandosRespuestas[$comandoActual] = $respuestaActual;
        }

        $json = json_encode($comandosRespuestas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT);

        if ($json === false) {
            throw new Exception("Error al codificar JSON");
        }

        if (file_put_contents($archivoJson, $json) === false) {
            throw new Exception("Error al escribir el archivo JSON: {$archivoJson}");
        }

        echo "El archivo JSON ha sido creado con éxito: {$archivoJson}";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Llamada a la función
$archivoTexto = 'comandos.txt';
$archivoJson = 'comandos.json';
convertirTextoAJson($archivoTexto, $archivoJson);
?>
