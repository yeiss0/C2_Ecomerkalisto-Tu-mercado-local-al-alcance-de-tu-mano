<?php

class Router {
    private $routes = [];

    public function get($uri, $callback) {
        $this->routes['GET'][$uri] = $callback;
    }

    public function post($uri, $callback) {
        $this->routes['POST'][$uri] = $callback;
    }

    public function resolve() {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Remove query string and trailing slash
        $uri = strtok($uri, '?');
        $uri = rtrim($uri, '/');
        
        // Dynamic base path support (both root and subdirectories)
        $basePath = defined('BASE_URL') ? BASE_URL : '';
        if ($basePath !== '' && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }
        if ($uri === '' || $uri === false) {
            $uri = '/';
        }

        // Legacy compatibility redirects
        if ($uri === '/carrito.php') {
            header("Location: " . $basePath . "/carrito");
            exit;
        }
        if ($uri === '/ecomerkalisto.php') {
            header("Location: " . ($basePath === '' ? '/' : $basePath . '/'));
            exit;
        }
        if ($uri === '/login.php') {
            header("Location: " . $basePath . "/login");
            exit;
        }
        if ($uri === '/admin.php') {
            header("Location: " . $basePath . "/admin");
            exit;
        }

        if (isset($this->routes[$method][$uri])) {
            $callback = $this->routes[$method][$uri];

            if (is_array($callback)) {
                $controller = new $callback[0]();
                $method = $callback[1];
                return call_user_func([$controller, $method]);
            }

            if (is_callable($callback)) {
                return call_user_func($callback);
            }
        }

        // 404 Not Found
        http_response_code(404);
        echo "404 Not Found";
    }
}
?>
