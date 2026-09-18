<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../core/Security.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);

    $title = "Enlace enviado";
    $message = "Si el correo está registrado, recibirás las instrucciones en breve.";
    $type = "success";

    if ($email) {
        $database = new Database();
        $conn = $database->getConnection();

        $stmt = $conn->prepare("SELECT id, username FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Nota: Integración con PHPMailer / SMTP en producción
            // mail($email, "Recuperación de Contraseña - EcoMerkaListo", ...);
        }
    }

    $loginUrl = BASE_URL . '/login';

    // Mostrar confirmación y redirigir al login
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <title>Recuperando...</title>
    </head>
    <body style='background-color: #f4f6f9;'>
        <script>
            Swal.fire({
                title: " . json_encode($title) . ",
                text: " . json_encode($message) . ",
                icon: " . json_encode($type) . ",
                confirmButtonColor: '#2E7D32'
            }).then(() => {
                window.location.href = " . json_encode($loginUrl) . ";
            });
        </script>
    </body>
    </html>";
    exit;
} else {
    header("Location: " . BASE_URL . "/login");
    exit;
}
?>
