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
    http_response_code(401);
    echo json_encode(["error" => "No autorizado. Inicie sesión en el panel."]);
    exit;
}
$_SESSION['admin_logged_in'] = true;

$config_file = __DIR__ . '/../config.json';
$current_config = [];
if (file_exists($config_file)) {
    $current_config = json_decode(file_get_contents($config_file), true);
    if (!is_array($current_config)) $current_config = [];
}

$title = isset($_POST['banner_title']) ? trim($_POST['banner_title']) : ($current_config['banner_title'] ?? '');
$subtitle = isset($_POST['banner_subtitle']) ? trim($_POST['banner_subtitle']) : ($current_config['banner_subtitle'] ?? '');
$image = $current_config['banner_image'] ?? 'imagen_entradacora.jpeg';

// Validar y procesar subida de banner si se envió un archivo
if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
    $tmp_name = $_FILES['banner_image']['tmp_name'];
    $orig_name = $_FILES['banner_image']['name'];
    $file_size = $_FILES['banner_image']['size'];

    // 1. Tamaño máximo: 5 MB
    if ($file_size > 5 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(["error" => "La imagen no debe exceder 5 MB."]);
        exit;
    }

    // 2. Validar extensión en lista blanca estricta
    $extension = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($extension, $allowed_extensions, true)) {
        http_response_code(400);
        echo json_encode(["error" => "Formato no permitido. Solo se admiten archivos JPG, PNG y WEBP."]);
        exit;
    }

    // 3. Validar contenido real con getimagesize()
    $image_info = @getimagesize($tmp_name);
    if ($image_info === false) {
        http_response_code(400);
        echo json_encode(["error" => "El archivo no es una imagen válida."]);
        exit;
    }

    // 4. Validar tipo MIME real
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $real_mime = finfo_file($finfo, $tmp_name);
    finfo_close($finfo);

    $allowed_mimes = ['image/jpeg', 'image/pjpeg', 'image/png', 'image/webp'];
    if (!in_array($real_mime, $allowed_mimes, true)) {
        http_response_code(400);
        echo json_encode(["error" => "Tipo MIME no válido ($real_mime)."]);
        exit;
    }

    // 5. Generar nombre de archivo seguro y aleatorio (sin caracteres controlables por el usuario)
    $upload_dir = __DIR__ . '/../imagenes/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $safe_filename = 'banner_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
    $target_file = $upload_dir . $safe_filename;

    if (move_uploaded_file($tmp_name, $target_file)) {
        $image = 'imagenes/' . $safe_filename;
    } else {
        http_response_code(500);
        echo json_encode(["error" => "No se pudo guardar la imagen en el servidor."]);
        exit;
    }
}

$new_config = array_merge($current_config, [
    "banner_title"    => $title,
    "banner_subtitle" => $subtitle,
    "banner_image"    => $image
]);

if (file_put_contents($config_file, json_encode($new_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo json_encode(["success" => true, "msg" => "Configuración guardada", "config" => $new_config]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "No se pudo guardar la configuración"]);
}
?>
