<?php
session_start();

$admin_emails = ['inversionesmercalisto@gmail.com', 'rodrigueyeisson499@gmail.com'];
$email = $_SESSION['usuario_email'] ?? '';
$is_admin = (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true)
            || in_array($email, $admin_emails);

if (!$is_admin) {
    http_response_code(403);
    echo json_encode(["error" => "No autorizado. Inicie sesión en el panel."]);
    exit;
}

// Asegurar la marca de admin en sesión
$_SESSION['admin_logged_in'] = true;

header('Content-Type: application/json; charset=utf-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once '../config/Database.php';

$database = new Database();
$conn = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $sql = "SELECT * FROM productos WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                echo json_encode($row);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "No encontrado"]);
            }
        } else {
            $sql = "SELECT * FROM productos";
            $stmt = $conn->query($sql);
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($productos);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        if ($data) {
            $nombre = trim($data['nombre'] ?? '');
            $marca = trim($data['marca'] ?? '');
            $categoria = trim($data['categoria'] ?? '');
            $contenido = trim($data['contenido'] ?? '');
            $url_imagen = trim($data['url_imagen'] ?? '');
            $precio = (float)($data['precio'] ?? 0);
            
            if (empty($nombre) || empty($marca) || empty($categoria) || empty($contenido) || empty($url_imagen) || $precio <= 0) {
                http_response_code(400);
                echo json_encode(["error" => "Campos inválidos."]);
                exit;
            }

            $descripcion = trim($data['descripcion'] ?? '');
            $stock = isset($data['stock']) ? (int)$data['stock'] : 50;

            $sql = "INSERT INTO productos (nombre, marca, categoria, precio, contenido, descripcion, url_imagen, stock) 
                    VALUES (:nombre, :marca, :categoria, :precio, :contenido, :descripcion, :url_imagen, :stock)";
            
            $stmt = $conn->prepare($sql);
            try {
                $stmt->execute([
                    ':nombre' => $nombre,
                    ':marca' => $marca,
                    ':categoria' => $categoria,
                    ':precio' => $precio,
                    ':contenido' => $contenido,
                    ':descripcion' => $descripcion,
                    ':url_imagen' => $url_imagen,
                    ':stock' => $stock
                ]);
                echo json_encode(["success" => true, "id" => $conn->lastInsertId()]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(["error" => $e->getMessage()]);
            }
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        if ($data && isset($data['id'])) {
            $id = (int)$data['id'];
            $nombre = trim($data['nombre'] ?? '');
            $marca = trim($data['marca'] ?? '');
            $categoria = trim($data['categoria'] ?? '');
            $contenido = trim($data['contenido'] ?? '');
            $url_imagen = trim($data['url_imagen'] ?? '');
            $precio = (float)($data['precio'] ?? 0);
            $descripcion = trim($data['descripcion'] ?? '');
            $stock = isset($data['stock']) ? (int)$data['stock'] : 50;

            $sql = "UPDATE productos SET 
                    nombre=:nombre, marca=:marca, categoria=:categoria, precio=:precio, 
                    contenido=:contenido, descripcion=:descripcion, url_imagen=:url_imagen, stock=:stock
                    WHERE id=:id";
            
            $stmt = $conn->prepare($sql);
            try {
                $stmt->execute([
                    ':nombre' => $nombre,
                    ':marca' => $marca,
                    ':categoria' => $categoria,
                    ':precio' => $precio,
                    ':contenido' => $contenido,
                    ':descripcion' => $descripcion,
                    ':url_imagen' => $url_imagen,
                    ':stock' => $stock,
                    ':id' => $id
                ]);
                echo json_encode(["success" => true]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(["error" => $e->getMessage()]);
            }
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"), true);
        if ($data && isset($data['id'])) {
            $id = (int)$data['id'];
            $sql = "DELETE FROM productos WHERE id = :id";
            $stmt = $conn->prepare($sql);
            try {
                $stmt->execute([':id' => $id]);
                echo json_encode(["success" => true]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(["error" => $e->getMessage()]);
            }
        }
        break;
}
?>
