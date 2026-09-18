<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

$admin_emails = ['inversionesmercalisto@gmail.com', 'rodrigueyeisson499@gmail.com'];
$email = $_SESSION['usuario_email'] ?? '';
$is_admin = (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true)
            || in_array($email, $admin_emails, true);

if (!$is_admin) {
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "No autorizado. Solo administradores pueden modificar la configuración."]);
    exit;
}
$_SESSION['admin_logged_in'] = true;

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !is_array($data)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Datos inválidos"]);
    exit;
}

$config_file = __DIR__ . '/../config.json';
$current_config = [];
if (file_exists($config_file)) {
    $current_config = json_decode(file_get_contents($config_file), true);
    if (!is_array($current_config)) $current_config = [];
}

// Sanitizar campos recibidos
$bannerTitle = isset($data['banner_title']) ? strip_tags(trim($data['banner_title']), '<span><b><strong><i><em>') : ($current_config['banner_title'] ?? "Todo lo que necesitas, <span>a un clic.</span>");
$bannerSubtitle = isset($data['banner_subtitle']) ? strip_tags(trim($data['banner_subtitle'])) : ($current_config['banner_subtitle'] ?? "");
$bannerImage = isset($data['banner_image']) ? strip_tags(trim($data['banner_image'])) : ($current_config['banner_image'] ?? "imagen_entradacora.jpeg");
$horario = isset($data['horario']) ? strip_tags(trim($data['horario'])) : ($current_config['horario'] ?? "9:00 AM — 9:00 PM");
$telefono = isset($data['contacto_telefono']) ? strip_tags(trim($data['contacto_telefono'])) : ($current_config['contacto_telefono'] ?? "+57 313 253 1923");
$direccion = isset($data['contacto_direccion']) ? strip_tags(trim($data['contacto_direccion'])) : ($current_config['contacto_direccion'] ?? "Cl. 14 #4a-84, Ubaté, Cundinamarca");
$contactEmail = isset($data['contacto_email']) ? filter_var(trim($data['contacto_email']), FILTER_SANITIZE_EMAIL) : ($current_config['contacto_email'] ?? "inversionesmercalisto@gmail.com");

$current_config['banner_title'] = $bannerTitle;
$current_config['banner_subtitle'] = $bannerSubtitle;
$current_config['banner_image'] = $bannerImage;
$current_config['horario'] = $horario;
$current_config['contacto_telefono'] = $telefono;
$current_config['contacto_direccion'] = $direccion;
$current_config['contacto_email'] = $contactEmail;

if (file_put_contents($config_file, json_encode($current_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo json_encode(["success" => true, "msg" => "Configuración guardada correctamente."]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "No se pudo guardar la configuración."]);
}
?>
