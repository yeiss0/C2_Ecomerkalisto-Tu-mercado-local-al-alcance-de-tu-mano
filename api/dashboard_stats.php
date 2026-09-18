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

$stats = [
    "top_vendidos" => [],
    "poco_stock"   => [],
    "resumen"      => []
];

// ── Top vendidos (todos los pedidos, sin filtro de estado para tener datos) ──
$sql_top = "SELECT 
                pr.id,
                pr.nombre,
                pr.marca,
                pr.url_imagen,
                SUM(pd.cantidad - IFNULL(pd.cantidad_devuelta, 0)) AS vendidos
            FROM pedido_detalles pd
            JOIN pedidos p   ON pd.pedido_id  = p.id
            JOIN productos pr ON pd.producto_id = pr.id
            WHERE p.estado IN ('Pendiente', 'Completado')
            GROUP BY pr.id, pr.nombre, pr.marca, pr.url_imagen
            ORDER BY vendidos DESC
            LIMIT 10";

$stmt = $conn->query($sql_top);
if ($stmt) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $stats["top_vendidos"][] = [
            "id"       => (int)$row["id"],
            "nombre"   => $row["nombre"],
            "marca"    => $row["marca"],
            "imagen"   => $row["url_imagen"],
            "vendidos" => (int)$row["vendidos"]
        ];
    }
}

// ── Productos con poco stock (< 10 unidades) ──
$sql_stock = "SELECT id, nombre, marca, url_imagen, stock
              FROM productos
              WHERE stock < 10
              ORDER BY stock ASC
              LIMIT 20";

$stmt2 = $conn->query($sql_stock);
if ($stmt2) {
    while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
        $stats["poco_stock"][] = [
            "id"     => (int)$row["id"],
            "nombre" => $row["nombre"],
            "marca"  => $row["marca"],
            "imagen" => $row["url_imagen"],
            "stock"  => (int)$row["stock"]
        ];
    }
}

// ── Resumen general ──
$resumen = $conn->query("SELECT 
    (SELECT COUNT(*) FROM pedidos WHERE estado = 'Pendiente')   AS pendientes,
    (SELECT COUNT(*) FROM pedidos WHERE estado = 'Completado')  AS completados,
    (SELECT COUNT(*) FROM pedidos WHERE estado = 'Cancelado')   AS cancelados,
    (SELECT COUNT(*) FROM pedidos WHERE estado = 'Devuelto')    AS devueltos,
    (SELECT IFNULL(SUM(total),0) FROM pedidos WHERE estado = 'Completado') AS ingresos_totales,
    (SELECT COUNT(*) FROM productos WHERE stock = 0)            AS agotados,
    (SELECT COUNT(*) FROM productos)                            AS total_productos
");

if ($resumen) {
    $stats["resumen"] = $resumen->fetch(PDO::FETCH_ASSOC);
}

echo json_encode($stats, JSON_UNESCAPED_UNICODE);
?>
