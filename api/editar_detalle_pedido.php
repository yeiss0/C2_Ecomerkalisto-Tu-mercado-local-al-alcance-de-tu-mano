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

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['detalle_id']) || !isset($data['pedido_id']) || !isset($data['action'])) {
    http_response_code(400);
    echo json_encode(["error" => "Datos incompletos"]);
    exit;
}

$detalle_id = (int)$data['detalle_id'];
$pedido_id  = (int)$data['pedido_id'];
$action     = (int)$data['action']; // -1 (restar 1 unidad) o 0 (eliminar todo el registro)

$conn->beginTransaction();

try {
    // 1. Obtener info del detalle actual con bloqueo FOR UPDATE
    $stmt = $conn->prepare("SELECT producto_id, cantidad, precio_unitario FROM pedido_detalles WHERE id = :did AND pedido_id = :pid FOR UPDATE");
    $stmt->execute([':did' => $detalle_id, ':pid' => $pedido_id]);
    $detalle = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$detalle) {
        throw new Exception("Detalle no encontrado");
    }

    $producto_id     = (int)$detalle['producto_id'];
    $cantidad_actual = (int)$detalle['cantidad'];
    $precio          = (float)$detalle['precio_unitario'];
    $qty_a_restaurar = 0;

    // 2. Determinar acción
    if ($action === 0) {
        // Eliminar todo el registro
        $qty_a_restaurar = $cantidad_actual;
        $stmtDel = $conn->prepare("DELETE FROM pedido_detalles WHERE id = :did");
        $stmtDel->execute([':did' => $detalle_id]);
    } elseif ($action === -1) {
        if ($cantidad_actual > 1) {
            $qty_a_restaurar = 1;
            $stmtUpd = $conn->prepare("UPDATE pedido_detalles SET cantidad = cantidad - 1 WHERE id = :did");
            $stmtUpd->execute([':did' => $detalle_id]);
        } else {
            $qty_a_restaurar = 1;
            $stmtDel = $conn->prepare("DELETE FROM pedido_detalles WHERE id = :did");
            $stmtDel->execute([':did' => $detalle_id]);
        }
    } else {
        throw new Exception("Acción inválida");
    }

    // 3. Restaurar stock en productos
    if ($qty_a_restaurar > 0) {
        $stmtStock = $conn->prepare("UPDATE productos SET stock = stock + :qty WHERE id = :pid");
        $stmtStock->execute([':qty' => $qty_a_restaurar, ':pid' => $producto_id]);
    }

    // 4. Recalcular total del pedido
    $stmtSum = $conn->prepare("SELECT COALESCE(SUM(cantidad * precio_unitario), 0) FROM pedido_detalles WHERE pedido_id = :pid");
    $stmtSum->execute([':pid' => $pedido_id]);
    $nuevoTotal = (float)$stmtSum->fetchColumn();

    $stmtTotal = $conn->prepare("UPDATE pedidos SET total = :total WHERE id = :pid");
    $stmtTotal->execute([':total' => $nuevoTotal, ':pid' => $pedido_id]);

    $conn->commit();
    echo json_encode(["success" => true, "nuevo_total" => $nuevoTotal]);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(400);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
