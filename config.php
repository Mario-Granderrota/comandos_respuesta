<?php
// config.php

return [
    'max_tokens_por_respuesta' => 500,
    'max_tokens_diarios' => 1000,
    'max_tokens_para_contexto' => 500,
    'temperature' => 0.5, // Controla la aleatoriedad de la generación de texto
    'max_length_respuesta' => 200, // Longitud máxima en caracteres de la respuesta
    'min_percent_match' => 50, // Porcentaje mínimo de coincidencia para comandos
    'max_length_prompt' => 2000, // Longitud máxima del prompt en tokens
    'entorno' => [
        'zona_horaria' => 'Europe/Madrid',
        'debug_mode' => true,
    ],
    'cabeceras' => [
        'expires' => "Tue, 01 Jan 2000 00:00:00 GMT",
        'last_modified' => gmdate("D, d M Y H:i:s") . " GMT",
        'cache_control' => "no-store, no-cache, must-revalidate, max-age=0",
        'cache_control_post' => "post-check=0, pre-check=0",
        'pragma' => "no-cache",
    ],
];
?>
