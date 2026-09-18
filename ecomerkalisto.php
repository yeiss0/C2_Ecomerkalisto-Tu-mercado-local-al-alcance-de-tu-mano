<?php
require_once __DIR__ . '/core/Security.php';
header("Location: " . (BASE_URL === '' ? '/' : BASE_URL . '/'));
exit;
?>
