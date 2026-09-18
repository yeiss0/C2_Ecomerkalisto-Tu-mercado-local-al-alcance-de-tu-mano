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

require_once '../config/Database.php';
$database = new Database();
$conn = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Listar todos los pedidos (sin borrar automáticamente)
    $sql = "SELECT 
                p.id,
                p.nombre       AS nombre_cliente,
                p.email        AS correo,
                p.telefono,
                p.direccion,
                p.metodo_entrega,
                p.medio_pago,
                p.total,
                p.estado,
                p.fecha
            FROM pedidos p
            ORDER BY p.id DESC";

    $stmt = $conn->query($sql);
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($pedidos);

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['id']) || !isset($data['accion'])) {
        http_response_code(400);
        echo json_encode(["error" => "Datos incompletos"]);
        exit;
    }

    $id     = (int)$data['id'];
    $accion = $data['accion'];

    try {
        if ($accion === 'completar') {
            // Marcar como completado (NO borrar)
            $stmt = $conn->prepare("UPDATE pedidos SET estado = 'Completado' WHERE id = :id");
            $stmt->execute([':id' => $id]);
            echo json_encode(["success" => true, "msg" => "Pedido marcado como Completado"]);

        } elseif ($accion === 'cancelar') {
            // Solo cancelar si está Pendiente
            $stmt = $conn->prepare("SELECT estado FROM pedidos WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pedido) {
                echo json_encode(["error" => "Pedido no encontrado"]);
                exit;
            }
            if ($pedido['estado'] !== 'Pendiente') {
                echo json_encode(["error" => "Solo se pueden cancelar pedidos en estado Pendiente"]);
                exit;
            }

            $conn->beginTransaction();
            // Restaurar stock de los productos que NO han sido devueltos aún
            $stmt2 = $conn->prepare("SELECT producto_id, cantidad, IFNULL(cantidad_devuelta,0) AS devueltos FROM pedido_detalles WHERE pedido_id = :pid");
            $stmt2->execute([':pid' => $id]);
            $detalles = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            foreach ($detalles as $det) {
                $qty_restante = $det['cantidad'] - $det['devueltos'];
                if ($qty_restante > 0) {
                    $stmt3 = $conn->prepare("UPDATE productos SET stock = stock + :qty WHERE id = :pid");
                    $stmt3->execute([':qty' => $qty_restante, ':pid' => $det['producto_id']]);
                }
            }

            $stmt4 = $conn->prepare("UPDATE pedidos SET estado = 'Cancelado' WHERE id = :id");
            $stmt4->execute([':id' => $id]);
            $conn->commit();
            echo json_encode(["success" => true, "msg" => "Pedido cancelado y stock restaurado"]);

        } elseif ($accion === 'devolver') {
            // Devolución parcial: { id, accion:'devolver', producto_id, cantidad_devolver }
            if (!isset($data['detalle_id']) || !isset($data['cantidad_devolver'])) {
                echo json_encode(["error" => "Faltan datos de devolución"]);
                exit;
            }

            $detalle_id      = (int)$data['detalle_id'];
            $cant_devolver   = (int)$data['cantidad_devolver'];

            if ($cant_devolver <= 0) {
                echo json_encode(["error" => "La cantidad a devolver debe ser mayor a 0"]);
                exit;
            }

            $conn->beginTransaction();

            // Obtener detalle con bloqueo
            $stmt = $conn->prepare("SELECT producto_id, cantidad, IFNULL(cantidad_devuelta,0) AS ya_devueltos, precio_unitario FROM pedido_detalles WHERE id = :did AND pedido_id = :pid FOR UPDATE");
            $stmt->execute([':did' => $detalle_id, ':pid' => $id]);
            $det = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$det) {
                $conn->rollBack();
                echo json_encode(["error" => "Detalle no encontrado"]);
                exit;
            }

            $pendiente_devolver = $det['cantidad'] - $det['ya_devueltos'];
            if ($cant_devolver > $pendiente_devolver) {
                $conn->rollBack();
                echo json_encode(["error" => "No puedes devolver más de lo pendiente ($pendiente_devolver unidades)"]);
                exit;
            }

            // Registrar devolución
            $stmt2 = $conn->prepare("UPDATE pedido_detalles SET cantidad_devuelta = cantidad_devuelta + :cant WHERE id = :did");
            $stmt2->execute([':cant' => $cant_devolver, ':did' => $detalle_id]);

            // Restaurar stock
            $stmt3 = $conn->prepare("UPDATE productos SET stock = stock + :cant WHERE id = :prod_id");
            $stmt3->execute([':cant' => $cant_devolver, ':prod_id' => $det['producto_id']]);

            // Reducir total del pedido
            $monto = $cant_devolver * $det['precio_unitario'];
            $stmt4 = $conn->prepare("UPDATE pedidos SET total = GREATEST(0, total - :monto) WHERE id = :pid");
            $stmt4->execute([':monto' => $monto, ':pid' => $id]);

            // Si todo fue devuelto, marcar como Devuelto
            $stmt5 = $conn->query("SELECT SUM(cantidad) as total_cant, SUM(IFNULL(cantidad_devuelta,0)) as total_dev FROM pedido_detalles WHERE pedido_id = $id");
            $resumen = $stmt5->fetch(PDO::FETCH_ASSOC);
            if ($resumen['total_cant'] == $resumen['total_dev']) {
                $conn->prepare("UPDATE pedidos SET estado = 'Devuelto' WHERE id = :id")->execute([':id' => $id]);
            } else {
                $conn->prepare("UPDATE pedidos SET estado = 'Devuelto' WHERE id = :id")->execute([':id' => $id]);
            }

            $conn->commit();
            echo json_encode(["success" => true, "msg" => "Devolución registrada: $cant_devolver unidades devueltas al inventario"]);

        } elseif ($accion === 'eliminar') {
            // Eliminar pedido permanentemente de la base de datos sin afectar el inventario
            $conn->beginTransaction();
            $stmtDelDet = $conn->prepare("DELETE FROM pedido_detalles WHERE pedido_id = :id");
            $stmtDelDet->execute([':id' => $id]);
            $stmtDel = $conn->prepare("DELETE FROM pedidos WHERE id = :id");
            $stmtDel->execute([':id' => $id]);
            $conn->commit();
            echo json_encode(["success" => true, "msg" => "Pedido #$id eliminado correctamente"]);

        } else {
            http_response_code(400);
            echo json_encode(["error" => "Acción no válida"]);
        }

    } catch (Exception $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        http_response_code(500);
        echo json_encode(["error" => "Error interno: " . $e->getMessage()]);
    }
}
?>
