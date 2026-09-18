<?php

class CartController {
    public function viewCart() {
        require_once __DIR__ . '/../core/Security.php';
        Security::requireLogin();
        
        require_once __DIR__ . '/../views/home/cart.php';
    }
}
?>
