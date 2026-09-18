<?php
if (session_status() === PHP_SESSION_NONE) {
    // Configurar cookies de sesión seguras
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

require_once __DIR__ . '/core/Security.php';
require_once __DIR__ . '/config/Database.php';

const GOOGLE_CLIENT_ID = '887193264399-vr557h5pugckjdds1pptcrkfdjeg12vf.apps.googleusercontent.com';

function verifyGoogleToken($jwt, $expectedClientId) {
    if (empty($jwt) || !is_string($jwt)) {
        return false;
    }

    $url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . urlencode($jwt);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        return false;
    }

    $payload = json_decode($response, true);
    if (!is_array($payload)) {
        return false;
    }

    // 1. Verificar que aud coincida exactamente con el CLIENT_ID de la aplicación
    if (!isset($payload['aud']) || $payload['aud'] !== $expectedClientId) {
        return false;
    }

    // 2. Verificar que el correo esté verificado por Google
    $emailVerified = $payload['email_verified'] ?? false;
    if ($emailVerified !== true && $emailVerified !== "true" && $emailVerified !== 1) {
        return false;
    }

    // 3. Verificar emisor oficial de Google
    $iss = $payload['iss'] ?? '';
    if ($iss !== 'accounts.google.com' && $iss !== 'https://accounts.google.com') {
        return false;
    }

    // 4. Verificar expiración
    if (isset($payload['exp']) && (int)$payload['exp'] < time()) {
        return false;
    }

    return $payload;
}

// Si recibimos un token de Google por POST
if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST['credential'])) {
    $jwt = trim($_POST['credential']);

    // Validar criptográficamente el token con Google
    $payload = verifyGoogleToken($jwt, GOOGLE_CLIENT_ID);

    if (!$payload || empty($payload['email'])) {
        $loginUrl = BASE_URL . '/login';
        echo "<script>alert('Acceso denegado: El token de Google no es válido o ha expirado.'); window.location.href='{$loginUrl}';</script>";
        exit;
    }

    $email = strtolower(trim($payload['email']));
    $name = trim($payload['name'] ?? 'Usuario Google');
    
    // Lista de administradores autorizados
    $admin_emails = ['inversionesmercalisto@gmail.com', 'rodrigueyeisson499@gmail.com'];

    $database = new Database();
    $conn = $database->getConnection();

    // Buscar si el usuario ya existe
    $query = "SELECT id, username FROM usuarios WHERE email = :email";
    $stmt = $conn->prepare($query);
    $stmt->execute([':email' => $email]);

    if ($stmt->rowCount() > 0) {
        // Usuario existente
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = $row['id'];
        $_SESSION['usuario_nombre'] = $row['username'];
        $_SESSION['usuario_email'] = $email;

        if (in_array($email, $admin_emails, true)) {
            $_SESSION['admin_logged_in'] = true;
            header("Location: " . BASE_URL . "/panel");
        } else {
            header("Location: " . (BASE_URL === '' ? '/' : BASE_URL . '/'));
        }
        exit();
    } else {
        // Registrar nuevo usuario desde Google con contraseña criptográficamente segura
        $query = "INSERT INTO usuarios (username, email, password) VALUES (:username, :email, :password)";
        $stmt = $conn->prepare($query);
        $random_pass = bin2hex(random_bytes(32));
        $hashed_pass = password_hash($random_pass, PASSWORD_DEFAULT);

        if ($stmt->execute([':username' => $name, ':email' => $email, ':password' => $hashed_pass])) {
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = $conn->lastInsertId();
            $_SESSION['usuario_nombre'] = $name;
            $_SESSION['usuario_email'] = $email;

            if (in_array($email, $admin_emails, true)) {
                $_SESSION['admin_logged_in'] = true;
                header("Location: " . BASE_URL . "/panel");
            } else {
                header("Location: " . (BASE_URL === '' ? '/' : BASE_URL . '/'));
            }
            exit();
        } else {
            $loginUrl = BASE_URL . '/login';
            echo "<script>alert('Error al registrar la cuenta de Google.'); window.location.href='{$loginUrl}';</script>";
            exit;
        }
    }
} else {
    // Redirigir accesos GET al login
    header("Location: " . BASE_URL . "/login");
    exit();
}
?>
