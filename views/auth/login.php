<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso Admin - EcoMerkaListo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body style="background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; height: 100vh;">
    <div class="card shadow p-4" style="width: 350px; border-radius: 12px; border-top: 5px solid #D5FF6A;">
        <div class="text-center mb-4">
            <h3 style="color: #2c3e50; font-weight: bold;"><i class="fas fa-shopping-basket" style="color: #D5FF6A;"></i> Ecomer<span style="color: #D5FF6A;">Kalisto</span></h3>
            <p class="text-muted">Iniciar sesión</p>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/login">
            <?php 
                require_once __DIR__ . '/../../core/Security.php';
                echo Security::csrfField(); 
            ?>
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Correo Electrónico" required autofocus>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
            </div>
            <button type="submit" class="btn w-100 mb-3" style="background-color: #D5FF6A; color: #2c3e50; border: none; font-weight: bold;">Ingresar</button>
            
            <hr>
            <div class="text-center mt-3">
                <p class="text-muted">O ingresa con</p>
                <div id="g_id_onload"
                     data-client_id="887193264399-vr557h5pugckjdds1pptcrkfdjeg12vf.apps.googleusercontent.com"
                     data-context="signin"
                     data-ux_mode="popup"
                     data-login_uri="<?= SITE_URL ?>/google_login.php"
                     data-auto_prompt="false">
                </div>

                <div class="g_id_signin"
                     data-type="standard"
                     data-shape="rectangular"
                     data-theme="outline"
                     data-text="signin_with"
                     data-size="large"
                     data-logo_alignment="left">
                </div>
            </div>
        </form>
    </div>
</body>
</html>
