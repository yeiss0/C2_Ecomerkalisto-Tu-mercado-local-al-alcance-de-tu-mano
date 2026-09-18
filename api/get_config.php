<?php
header('Content-Type: application/json; charset=utf-8');
$config_file = '../config.json';
if (file_exists($config_file)) {
    echo file_get_contents($config_file);
} else {
    echo json_encode(["banner_title" => "Todo lo que necesitas, <span>a un clic.</span>", "banner_subtitle" => "Descubre la forma más rápida y segura de hacer tus compras...", "banner_image" => "imagen_entradacora.jpeg"]);
}
?>
