<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

// Conexión robusta verificando ambas rutas posibles
if (file_exists(__DIR__ . '/../config/Database.php')) {
    require_once __DIR__ . '/../config/Database.php';
    $database = new Database();
    $conn = $database->getConnection();
} elseif (file_exists(__DIR__ . '/../config/conexion.php')) {
    require_once __DIR__ . '/../config/conexion.php';
    $conn = $pdo ?? $conn ?? $db ?? null;
} else {
    echo json_encode(['success' => false, 'error' => 'Archivo de conexión no encontrado.']);
    exit;
}

$pdo = $conn;
$db = $conn;

if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos.']);
    exit;
}

$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);

if (!$data || !isset($data['cart']) || !is_array($data['cart']) || count($data['cart']) === 0) {
    echo json_encode(['success' => false, 'error' => 'El carrito está vacío o los datos son inválidos.']);
    exit;
}

$nombre = trim($data['nombre'] ?? '');
$email = trim($data['email'] ?? ($_SESSION['usuario_email'] ?? ''));
$telefono = trim($data['telefono'] ?? '');
$direccion = trim($data['direccion'] ?? '');
$metodoEntrega = trim($data['metodo_entrega'] ?? 'Domicilio');
$medioPago = trim($data['medio_pago'] ?? 'Efectivo');
$cart = $data['cart'];

// Soporte para usuarios en sesión o compras anónimas / invitados
$usuario_id = $_SESSION['usuario_id'] ?? null;

if (empty($nombre) || empty($email) || empty($telefono)) {
    echo json_encode(['success' => false, 'error' => 'Por favor completa tu nombre, correo y teléfono.']);
    exit;
}

if ($metodoEntrega === 'Domicilio' && empty($direccion)) {
    echo json_encode(['success' => false, 'error' => 'Por favor ingresa la dirección para la entrega a domicilio.']);
    exit;
}

// Iniciar transacción atómica obligatoria
$conn->beginTransaction();

try {
    $total = 0;
    $detalles = "";
    $itemsToProcess = [];
    
    // 1. Validar productos y calcular totales
    $stmtCheck = $conn->prepare("SELECT id, nombre, marca, precio, stock FROM productos WHERE id = :id FOR UPDATE");
    
    foreach ($cart as $item) {
        $id = (int)($item['id'] ?? 0);
        $qty = (int)($item['qty'] ?? 0);
        
        if ($id <= 0 || $qty <= 0) {
            continue;
        }
        
        $stmtCheck->execute([':id' => $id]);
        $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            throw new Exception("Producto #$id no encontrado o descontinuado.");
        }
        
        $currentStock = (int)$row['stock'];
        if ($currentStock < $qty) {
            throw new Exception("Stock insuficiente para: " . $row['nombre'] . " (Disponible: " . $currentStock . ")");
        }
        
        $precioUnitario = (float)$row['precio'];
        $subtotal = $precioUnitario * $qty;
        $total += $subtotal;
        
        $itemsToProcess[] = [
            'producto_id' => $id,
            'cantidad' => $qty,
            'precio_unitario' => $precioUnitario,
            'new_stock' => $currentStock - $qty
        ];
        
        $detalles .= "*Cant:* {$qty} | *Prod:* {$row['nombre']} {$row['marca']}\n";
        $detalles .= "   *Sub:* $" . number_format($subtotal, 0, ',', '.') . "\n\n";
    }
    
    if (empty($itemsToProcess)) {
        throw new Exception("No hay productos válidos en el pedido.");
    }
    
    if ($metodoEntrega === 'Domicilio' && $total < 50000) {
        throw new Exception("El monto mínimo de compra para envíos a domicilio es de $50.000 COP.");
    }
    
    // Detectar si la tabla pedidos tiene la columna usuario_id en producción
    $hasUsuarioIdCol = false;
    try {
        $colCheck = $conn->query("SHOW COLUMNS FROM pedidos LIKE 'usuario_id'");
        if ($colCheck && $colCheck->rowCount() > 0) {
            $hasUsuarioIdCol = true;
        }
    } catch (Throwable $ignore) {
        $hasUsuarioIdCol = false;
    }

    // 2. INSERT INTO pedidos
    if ($hasUsuarioIdCol) {
        $stmtPedido = $conn->prepare("
            INSERT INTO pedidos (usuario_id, nombre, email, telefono, direccion, metodo_entrega, medio_pago, total, estado, fecha) 
            VALUES (:usuario_id, :nombre, :email, :telefono, :direccion, :metodo_entrega, :medio_pago, :total, 'Pendiente', NOW())
        ");
        $stmtPedido->execute([
            ':usuario_id' => $usuario_id,
            ':nombre' => $nombre,
            ':email' => $email,
            ':telefono' => $telefono,
            ':direccion' => ($metodoEntrega === 'Domicilio' ? $direccion : 'Recogida en tienda'),
            ':metodo_entrega' => $metodoEntrega,
            ':medio_pago' => $medioPago,
            ':total' => $total
        ]);
    } else {
        $stmtPedido = $conn->prepare("
            INSERT INTO pedidos (nombre, email, telefono, direccion, metodo_entrega, medio_pago, total, estado, fecha) 
            VALUES (:nombre, :email, :telefono, :direccion, :metodo_entrega, :medio_pago, :total, 'Pendiente', NOW())
        ");
        $stmtPedido->execute([
            ':nombre' => $nombre,
            ':email' => $email,
            ':telefono' => $telefono,
            ':direccion' => ($metodoEntrega === 'Domicilio' ? $direccion : 'Recogida en tienda'),
            ':metodo_entrega' => $metodoEntrega,
            ':medio_pago' => $medioPago,
            ':total' => $total
        ]);
    }
    
    $pedidoId = (int)$conn->lastInsertId();
    if ($pedidoId <= 0) {
        throw new Exception("No se pudo generar el ID del pedido en la base de datos.");
    }
    
    // 3. INSERT INTO pedido_detalles
    $stmtDetalle = $conn->prepare("
        INSERT INTO pedido_detalles (pedido_id, producto_id, cantidad, precio_unitario, cantidad_devuelta) 
        VALUES (:pedido_id, :producto_id, :cantidad, :precio_unitario, 0)
    ");
    
    foreach ($itemsToProcess as $pItem) {
        $stmtDetalle->execute([
            ':pedido_id' => $pedidoId,
            ':producto_id' => $pItem['producto_id'],
            ':cantidad' => $pItem['cantidad'],
            ':precio_unitario' => $pItem['precio_unitario']
        ]);
    }
    
    // 4. Descuento de stock
    $stmtUpdateStock = $conn->prepare("UPDATE productos SET stock = :new_stock WHERE id = :id");
    foreach ($itemsToProcess as $pItem) {
        $stmtUpdateStock->execute([
            ':new_stock' => $pItem['new_stock'],
            ':id' => $pItem['producto_id']
        ]);
    }
    
    // Confirmar transacción
    $conn->commit();
    
    // Generar mensaje de WhatsApp
    $pedidoTexto = "*¡NUEVO PEDIDO DE ECOMERKALISTO!* (Orden #$pedidoId)\n\n";
    $pedidoTexto .= "*Cliente:* $nombre\n";
    $pedidoTexto .= "*Correo:* $email\n";
    $pedidoTexto .= "*Teléfono:* $telefono\n";
    $pedidoTexto .= "*Método de Entrega:* $metodoEntrega\n";
    if ($metodoEntrega === 'Domicilio') {
        $pedidoTexto .= "*Dirección:* $direccion\n";
    } else {
        $pedidoTexto .= "*Dirección:* (Pasará a recoger el pedido)\n";
    }
    $pedidoTexto .= "*Medio de Pago:* $medioPago\n\n";
    $pedidoTexto .= "*DETALLE DEL PEDIDO:*\n";
    $pedidoTexto .= "--------------------------------------\n";
    $pedidoTexto .= $detalles;
    $pedidoTexto .= "--------------------------------------\n";
    $pedidoTexto .= "*TOTAL A PAGAR:* $" . number_format($total, 0, ',', '.');
    
    echo json_encode([
        "success" => true,
        "pedido_id" => $pedidoId,
        "whatsapp_msg" => $pedidoTexto
    ]);
    exit;
    
} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
} catch (Throwable $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}
?>
