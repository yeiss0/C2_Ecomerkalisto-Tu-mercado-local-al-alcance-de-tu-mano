<?php

class AdminController {

    private $admin_emails = ['inversionesmercalisto@gmail.com', 'rodrigueyeisson499@gmail.com'];

    public function dashboard() {
        require_once __DIR__ . '/../core/Security.php';
        Security::requireLogin();

        // Verificar si el email de sesión es un admin autorizado
        $email = $_SESSION['usuario_email'] ?? '';
        if (!in_array($email, $this->admin_emails)) {
            header("Location: " . (BASE_URL === '' ? '/' : BASE_URL . '/'));
            exit();
        }

        // Marcar siempre como admin en la sesión para que los APIs funcionen
        $_SESSION['admin_logged_in'] = true;

        // Evitar caché de navegador para el panel de administración
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

        require_once __DIR__ . '/../views/admin/dashboard.php';
    }
}
?>
