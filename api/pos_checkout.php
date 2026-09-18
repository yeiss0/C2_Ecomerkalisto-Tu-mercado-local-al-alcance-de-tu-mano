<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

$admin_emails = ['inversionesmercalisto@gmail.com', 'rodrigueyeisson499@gmail.com'];
$email = $_SESSION['usuario_email'] ?? '';
$is_admin = (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true)
            || in_array($email, $admin_emails);

if (!$is_admin) {
    http_response_code(401);
    echo json_encode(["error" => "No autorizado. Inicie sesión en el panel."]);
    exit;
}

$_SESSION['admin_logged_in'] = true;

require_once __DIR__ . '/../config/Database.php';

$database = new Database();
$conn = $database->getConnection();

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['cart']) || !is_array($data['cart']) || count($data['cart']) === 0) {
    http_response_code(400);
    echo json_encode(["error" => "El carrito POS está vacío."]);
    exit;
}

$cart = $data['cart'];

// Iniciar transacción atómica para consistencia de stock
$conn->beginTransaction();

try {
    $total = 0;
    
    // Preparar declaraciones
    $stmtCheck = $conn->prepare("SELECT id, nombre, precio, stock FROM productos WHERE id = :id FOR UPDATE");
    $stmtUpdateStock = $conn->prepare("UPDATE productos SET stock = :new_stock WHERE id = :id");
    
    $processedItems = [];

    foreach ($cart as $item) {
        $id = (int)($item['id'] ?? 0);
        $qty = (int)($item['qty'] ?? 0);
        
        if ($id <= 0 || $qty <= 0) {
            continue;
        }
        
        $stmtCheck->execute([':id' => $id]);
        $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            throw new Exception("Producto #$id no encontrado.");
        }
        
        $currentStock = (int)$row['stock'];
        if ($currentStock < $qty) {
            throw new Exception("Stock insuficiente para '{$row['nombre']}'. Disponible: $currentStock, solicitado: $qty");
        }
        
        // Descontar stock automáticamente
        $newStock = $currentStock - $qty;
        $stmtUpdateStock->execute([
            ':new_stock' => $newStock,
            ':id' => $id
        ]);
        
        $itemTotal = ((float)$row['precio'] * $qty);
        $total += $itemTotal;
        
        $processedItems[] = [
            'id' => $id,
            'nombre' => $row['nombre'],
            'precio' => (float)$row['precio'],
            'qty' => $qty,
            'new_stock' => $newStock
        ];
    }
    
    if (empty($processedItems)) {
        throw new Exception("No hay productos válidos para procesar.");
    }
    
    // Registrar pedido en tabla pedidos
    $stmtPedido = $conn->prepare("
        INSERT INTO pedidos (nombre, email, telefono, direccion, metodo_entrega, medio_pago, total, estado, fecha) 
        VALUES (:nombre, :email, :telefono, :direccion, :metodo_entrega, :medio_pago, :total, 'Completado', NOW())
    ");
    
    $stmtPedido->execute([
        ':nombre' => 'Venta Física POS',
        ':email' => 'pos@mercalisto.com',
        ':telefono' => 'N/A',
        ':direccion' => 'Local Físico',
        ':metodo_entrega' => 'Recogida',
        ':medio_pago' => 'Efectivo/POS',
        ':total' => $total
    ]);
    
    $pedidoId = (int)$conn->lastInsertId();
    
    // Registrar detalles del pedido
    $stmtDetalle = $conn->prepare("
        INSERT INTO pedido_detalles (pedido_id, producto_id, cantidad, precio_unitario, cantidad_devuelta) 
        VALUES (:pedido_id, :producto_id, :cantidad, :precio_unitario, 0)
    ");
    
    foreach ($processedItems as $pItem) {
        $stmtDetalle->execute([
            ':pedido_id' => $pedidoId,
            ':producto_id' => $pItem['id'],
            ':cantidad' => $pItem['qty'],
            ':precio_unitario' => $pItem['precio']
        ]);
    }
    
    // Confirmar transacción
    $conn->commit();
    
    echo json_encode([
        "success" => true,
        "pedido_id" => $pedidoId,
        "total" => $total,
        "items" => $processedItems
    ]);
    
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(400);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
