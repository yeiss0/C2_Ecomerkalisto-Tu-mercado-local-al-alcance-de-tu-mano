<?php
// Archivo de compatibilidad para inclusión de conexión
require_once __DIR__ . '/Database.php';

$database = new Database();
$conn = $database->getConnection();
$pdo = $conn;
$db = $conn;
