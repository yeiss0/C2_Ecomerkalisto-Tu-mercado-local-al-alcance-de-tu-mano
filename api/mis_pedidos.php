<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

if (!isset($_SESSION['usuario_email']) || empty($_SESSION['usuario_email'])) {
    http_response_code(401);
    echo json_encode(["error" => "Debes iniciar sesión para ver tus pedidos."]);
    exit;
}

require_once '../config/Database.php';
$database = new Database();
$conn = $database->getConnection();

$email = $_SESSION['usuario_email'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        // Obtener pedidos del usuario ordenados por fecha descendente
        $stmt = $conn->prepare("SELECT id, nombre, email, telefono, direccion, metodo_entrega, medio_pago, total, estado, fecha 
                                FROM pedidos 
                                WHERE email = :email 
                                ORDER BY id DESC");
        $stmt->execute([':email' => $email]);
        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener detalles de cada pedido
        foreach ($pedidos as &$p) {
            $stmtDet = $conn->prepare("SELECT 
                                        pd.id,
                                        pd.producto_id,
                                        pd.cantidad,
                                        IFNULL(pd.cantidad_devuelta, 0) AS cantidad_devuelta,
                                        pd.precio_unitario,
                                        pr.nombre,
                                        pr.marca,
                                        pr.url_imagen
                                       FROM pedido_detalles pd
                                       JOIN productos pr ON pd.producto_id = pr.id
                                       WHERE pd.pedido_id = :pid");
            $stmtDet->execute([':pid' => $p['id']]);
            $p['detalles'] = $stmtDet->fetchAll(PDO::FETCH_ASSOC);
        }

        echo json_encode($pedidos, JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => "Error al obtener pedidos: " . $e->getMessage()]);
    }

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $accion = $data['accion'] ?? '';
    $id = isset($data['id']) ? (int)$data['id'] : 0;

    if ($accion === 'eliminar') {
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["error" => "ID de pedido no válido."]);
            exit;
        }

        try {
            // Verificar que el pedido pertenece al usuario en sesión
            $stmtCheck = $conn->prepare("SELECT id FROM pedidos WHERE id = :id AND email = :email");
            $stmtCheck->execute([':id' => $id, ':email' => $email]);
            $pedido = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if (!$pedido) {
                http_response_code(403);
                echo json_encode(["error" => "No tienes permisos para eliminar este pedido o no existe."]);
                exit;
            }

            // Eliminar pedido y detalles sin modificar inventario
            $conn->beginTransaction();
            $stmtDelDet = $conn->prepare("DELETE FROM pedido_detalles WHERE pedido_id = :id");
            $stmtDelDet->execute([':id' => $id]);

            $stmtDel = $conn->prepare("DELETE FROM pedidos WHERE id = :id");
            $stmtDel->execute([':id' => $id]);
            $conn->commit();

            echo json_encode(["success" => true, "msg" => "Pedido #$id eliminado de tu historial correctamente."]);
        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            http_response_code(500);
            echo json_encode(["error" => "Error al eliminar el pedido: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Acción no soportada."]);
    }
}
?>
