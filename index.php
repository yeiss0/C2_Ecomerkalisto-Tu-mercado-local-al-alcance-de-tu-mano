<?php
require_once __DIR__ . '/core/Security.php';
Security::initSession();

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/core/Router.php';

// Carga de controladores básicos (esto se puede automatizar con un autoloader, pero por simplicidad lo incluimos directo)
require_once __DIR__ . '/controllers/HomeController.php';
require_once __DIR__ . '/controllers/AuthController.php';

$router = new Router();

// Rutas Públicas
$router->get('/', [HomeController::class, 'index']);
$router->get('/contacto', [HomeController::class, 'contacto']);
$router->get('/horario', [HomeController::class, 'horario']);
$router->get('/login', [AuthController::class, 'showLoginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);

// Rutas de Usuario (Carrito y Perfil)
require_once __DIR__ . '/controllers/CartController.php';
require_once __DIR__ . '/controllers/ProfileController.php';
$router->get('/carrito', [CartController::class, 'viewCart']);
$router->get('/perfil', [ProfileController::class, 'viewProfile']);

// Rutas de Administrador
require_once __DIR__ . '/controllers/AdminController.php';
$router->get('/admin', [AdminController::class, 'dashboard']);
$router->get('/panel', [AdminController::class, 'dashboard']);

$router->resolve();
?>
