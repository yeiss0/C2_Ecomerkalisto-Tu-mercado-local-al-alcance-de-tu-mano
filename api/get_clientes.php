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
    echo json_encode(["error" => "No autorizado. Inicie sesión como administrador."]);
    exit;
}
$_SESSION['admin_logged_in'] = true;

require_once __DIR__ . '/../config/Database.php';
$database = new Database();
$conn = $database->getConnection();

try {
    // Obtener usuarios y sumar el total de sus pedidos completados
    $sql = "SELECT u.id, u.username, u.email,
            COUNT(p.id) as total_pedidos,
            COALESCE(SUM(p.total), 0) as total_gastado
            FROM usuarios u
            LEFT JOIN pedidos p ON u.email = p.email AND p.estado = 'Completado'
            GROUP BY u.id, u.username, u.email
            ORDER BY total_gastado DESC";

    $stmt = $conn->query($sql);
    $clientes = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $clientes[] = [
            "id" => (int)$row["id"],
            "nombre" => htmlspecialchars($row["username"], ENT_QUOTES, 'UTF-8'),
            "email" => htmlspecialchars($row["email"], ENT_QUOTES, 'UTF-8'),
            "total_pedidos" => (int)$row["total_pedidos"],
            "total_gastado" => (float)$row["total_gastado"]
        ];
    }

    echo json_encode($clientes, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error al obtener clientes"]);
}
?>
