<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

$admin_emails = ['inversionesmercalisto@gmail.com', 'rodrigueyeisson499@gmail.com'];
$email_session = $_SESSION['usuario_email'] ?? '';
$is_admin = (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true)
            || in_array($email_session, $admin_emails);

if (!$is_admin) {
    http_response_code(401);
    echo json_encode(["error" => "No autorizado"]);
    exit;
}
$_SESSION['admin_logged_in'] = true;

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Falta ID"]);
    exit;
}

require_once '../config/Database.php';
$database = new Database();
$conn = $database->getConnection();

$id = (int)$_GET['id'];

$sql = "SELECT 
            pd.id,
            pd.pedido_id,
            pd.producto_id,
            pd.cantidad,
            IFNULL(pd.cantidad_devuelta, 0) AS cantidad_devuelta,
            (pd.cantidad - IFNULL(pd.cantidad_devuelta, 0)) AS cantidad_pendiente,
            pd.precio_unitario,
            pr.nombre,
            pr.marca
        FROM pedido_detalles pd
        JOIN productos pr ON pd.producto_id = pr.id
        WHERE pd.pedido_id = :id";

$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $id]);
$detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($detalles);
?>
