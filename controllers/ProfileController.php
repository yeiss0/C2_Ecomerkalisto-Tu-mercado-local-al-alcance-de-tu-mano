<?php

class ProfileController {
    public function viewProfile() {
        require_once __DIR__ . '/../core/Security.php';
        Security::requireLogin();
        
        require_once __DIR__ . '/../views/home/profile.php';
    }
}
?>
