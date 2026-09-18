<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../core/Security.php';

class AuthController {
    public function showLoginForm() {
        require_once __DIR__ . '/../views/auth/login.php';
    }

    public function login() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                $loginUrl = BASE_URL . '/login';
                echo "<script>alert('Token de seguridad inválido.'); window.location.href='{$loginUrl}';</script>";
                exit;
            }
            $email = $_POST['email'];
            $pass = $_POST['password'];

            $database = new Database();
            $conn = $database->getConnection();

            $query = "SELECT id, username, password FROM usuarios WHERE email = :email";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (password_verify($pass, $row['password']) || $row['password'] === $pass) {
                    session_regenerate_id(true); // Prevents Session Fixation
                    $_SESSION['usuario_id'] = $row['id'];
                    $_SESSION['usuario_nombre'] = $row['username'];
                    $_SESSION['usuario_email'] = $email;
                    
                    if ($email === 'inversionesmercalisto@gmail.com' || $email === 'rodrigueyeisson499@gmail.com') {
                        $_SESSION['admin_logged_in'] = true;
                        header("Location: " . BASE_URL . "/panel");
                    } else {
                        header("Location: " . (BASE_URL === '' ? '/' : BASE_URL . '/'));
                    }
                    exit();
                } else {
                    $loginUrl = BASE_URL . '/login';
                    echo "<script>alert('Correo o contraseña incorrectos.'); window.location.href='{$loginUrl}';</script>";
                }
            } else {
                $loginUrl = BASE_URL . '/login';
                echo "<script>alert('Correo o contraseña incorrectos.'); window.location.href='{$loginUrl}';</script>";
            }
        }
    }

    public function logout() {
        session_destroy();
        header("Location: " . BASE_URL . "/login");
        exit();
    }
}
?>
