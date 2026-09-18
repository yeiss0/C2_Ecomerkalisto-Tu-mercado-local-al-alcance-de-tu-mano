<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

// Lista de emails autorizados como administradores
$admin_emails = ['inversionesmercalisto@gmail.com', 'rodrigueyeisson499@gmail.com'];

$email = $_SESSION['usuario_email'] ?? '';

$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$is_admin_email = in_array($email, $admin_emails);

if (!$is_admin && !$is_admin_email) {
    http_response_code(401);
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

// Si llegó por email admin pero no tenía la sesión marcada, la marcamos ahora
if ($is_admin_email && !$is_admin) {
    $_SESSION['admin_logged_in'] = true;
}

require_once '../config/Database.php';

$data = json_decode(file_get_contents("php://input"), true);

// array_key_exists permite detectar es_oferta=0 correctamente
if (!isset($data['id']) || !array_key_exists('es_oferta', $data)) {
    http_response_code(400);
    echo json_encode(["error" => "Datos incompletos"]);
    exit;
}

$id = (int)$data['id'];
$es_oferta = ($data['es_oferta'] == 1) ? 1 : 0;

try {
    $database = new Database();
    $conn = $database->getConnection();

    $stmt = $conn->prepare("UPDATE productos SET es_oferta = :es_oferta WHERE id = :id");
    $stmt->execute([':es_oferta' => $es_oferta, ':id' => $id]);

    echo json_encode([
        "success" => true,
        "msg"     => "Estado de oferta actualizado",
        "id"      => $id,
        "es_oferta" => $es_oferta
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error de base de datos: " . $e->getMessage()]);
}
?>
