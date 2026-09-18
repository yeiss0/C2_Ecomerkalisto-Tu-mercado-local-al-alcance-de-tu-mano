<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['usuario_email'])) {
    http_response_code(401);
    echo json_encode(["error" => "Sesión expirada"]);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
require_once __DIR__ . '/../config/Database.php';

$database = new Database();
$conn = $database->getConnection();

$sql = "SELECT id, nombre, marca, categoria, precio, contenido, descripcion, url_imagen, stock, es_oferta FROM productos ORDER BY id ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$productos = array();
foreach ($result as $row) {
    $productos[] = array(
        "id" => (int)$row["id"],
        "nombre" => htmlspecialchars($row["nombre"], ENT_QUOTES, 'UTF-8'),
        "marca" => htmlspecialchars($row["marca"] ?? '', ENT_QUOTES, 'UTF-8'),
        "categoria" => htmlspecialchars($row["categoria"] ?? '', ENT_QUOTES, 'UTF-8'),
        "precio" => (float)$row["precio"],
        "contenido" => htmlspecialchars($row["contenido"] ?? '', ENT_QUOTES, 'UTF-8'),
        "descripcion" => htmlspecialchars($row["descripcion"] ?? '', ENT_QUOTES, 'UTF-8'),
        "url_imagen" => htmlspecialchars($row["url_imagen"] ?? '', ENT_QUOTES, 'UTF-8'),
        "stock" => (int)$row["stock"],
        "es_oferta" => (int)($row["es_oferta"] ?? 0)
    );
}

$jsonOutput = json_encode($productos, JSON_UNESCAPED_UNICODE);

if (!headers_sent() && extension_loaded('zlib') && isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
    header('Content-Encoding: gzip');
    echo gzencode($jsonOutput, 6);
} else {
    echo $jsonOutput;
}
?>
