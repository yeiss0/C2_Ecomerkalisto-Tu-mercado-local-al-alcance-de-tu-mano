<?php

require_once __DIR__ . '/../models/Producto.php';

class HomeController {
    public function index() {
        require_once __DIR__ . '/../core/Security.php';
        Security::requireLogin();
        
        require_once __DIR__ . '/../views/home/index.php';
    }

    public function contacto() {
        require_once __DIR__ . '/../core/Security.php';
        Security::requireLogin();
        
        require_once __DIR__ . '/../views/home/contacto.php';
    }

    public function horario() {
        require_once __DIR__ . '/../core/Security.php';
        Security::requireLogin();
        
        require_once __DIR__ . '/../views/home/horario.php';
    }
}
?>
