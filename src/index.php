<?php
session_start();

$debugMode = filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN);
error_reporting(E_ALL);
ini_set('display_errors', $debugMode ? '1' : '0');

$timezone = getenv('APP_TIMEZONE') ?: 'Asia/Jakarta';
if (!in_array($timezone, timezone_identifiers_list(), true)) {
    $timezone = 'Asia/Jakarta';
}
date_default_timezone_set($timezone);

// Redirect root path to login page
if ($_SERVER['REQUEST_URI'] == '/' || $_SERVER['REQUEST_URI'] == '') {
    header('Location: /login');
    exit();
}

// Define base path
define('BASE_PATH', __DIR__);
define('PUBLIC_PATH', BASE_PATH . '/public');
define('CONTROLLER_PATH', BASE_PATH . '/controllers');
define('MODEL_PATH', BASE_PATH . '/models');
define('VIEW_PATH', BASE_PATH . '/views');
define('CONFIG_PATH', BASE_PATH . '/config');
define('HELPER_PATH', BASE_PATH . '/helpers');
define('MIDDLEWARE_PATH', BASE_PATH . '/middleware');
define('STORAGE_PATH', BASE_PATH . '/storage');

// Autoload classes
spl_autoload_register(function ($class) {
    $paths = [
        MODEL_PATH,
        CONTROLLER_PATH,
        HELPER_PATH,
        MIDDLEWARE_PATH
    ];
    
    foreach ($paths as $path) {
        $file = $path . '/' . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Load configuration
require_once CONFIG_PATH . '/database.php';
$routeConfig = require_once CONFIG_PATH . '/routes.php';
require_once HELPER_PATH . '/Csrf.php';

$routes = isset($routeConfig['routes']) ? $routeConfig['routes'] : [];
$postRoutes = isset($routeConfig['postRoutes']) ? $routeConfig['postRoutes'] : [];

// Parse URL to determine route
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove base path if exists (in case app is not running from root)
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
if ($basePath !== '/') {
    $uri = preg_replace('#^' . preg_quote($basePath) . '#', '', $uri);
}

// Remove any trailing slash and query string
$uri = rtrim($uri, '/');
$uri = strtok($uri, '?'); // Remove query string if exists

// Validate CSRF token for POST requests (skip if explicitly disabled)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? null;
    if (!Csrf::validate($csrfToken)) {
        http_response_code(419);
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        $_SESSION['flash']['error'] = 'Sesi formulir berakhir. Silakan muat ulang halaman dan coba lagi.';
        $redirectTarget = '/login';
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $refererPath = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
            if (is_string($refererPath) && $refererPath !== '') {
                $redirectTarget = $refererPath;
            }
        }
        header('Location: ' . $redirectTarget);
        exit;
    }
}

$paramKeyMap = ['id', 'stage', 'event_id'];

/**
 * Dispatch a controller method with appropriate parameters.
 */
function dispatchRoute($controllerName, $method, $matches = [], $paramKeyMap = []) {
    try {
        $controller = new $controllerName();

        $reflection = new ReflectionMethod($controller, $method);
        $paramCount = $reflection->getNumberOfParameters();

        if ($paramCount === 0) {
            $controller->$method();
            return;
        }

        if ($paramCount === 1) {
            $params = [];
            foreach ($matches as $index => $value) {
                $key = $paramKeyMap[$index] ?? $index;
                $params[$key] = $value;
            }
            $controller->$method($params);
            return;
        }

        if ($paramCount > 1) {
            $controller->$method(...$matches);
            return;
        }
    } catch (Throwable $e) {
        throw $e;
    }
}

// Match route
$routeFound = false;

// Check if request method is POST and try to match POST routes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check for exact match first
    if (array_key_exists($uri, $postRoutes)) {
        list($controllerName, $method) = explode('@', $postRoutes[$uri]);
        dispatchRoute($controllerName, $method, [], $paramKeyMap);
        $routeFound = true;
    } else {
        // Check for pattern match
        foreach ($postRoutes as $pattern => $handler) {
            // Convert pattern to regex
            $regex = '#^' . preg_replace('/\(\[\^\/\]\+\)/', '([^/]+)', $pattern) . '$#';
            if (preg_match($regex, $uri, $matches)) {
                // Remove full match and keep only captured groups
                array_shift($matches);
                
                list($controllerName, $method) = explode('@', $handler);
                dispatchRoute($controllerName, $method, $matches, $paramKeyMap);
                $routeFound = true;
                break;
            }
        }
    }
} else {
    // Check for exact match first (for GET routes)
    if (array_key_exists($uri, $routes)) {
        list($controllerName, $method) = explode('@', $routes[$uri]);
        dispatchRoute($controllerName, $method, [], $paramKeyMap);
        $routeFound = true;
    } else {
        // Check for pattern match
        foreach ($routes as $pattern => $handler) {
            // Convert pattern to regex
            $regex = '#^' . preg_replace('/\(\[\^\/\]\+\)/', '([^/]+)', $pattern) . '$#';
            if (preg_match($regex, $uri, $matches)) {
                // Remove full match and keep only captured groups
                array_shift($matches);
                
                list($controllerName, $method) = explode('@', $handler);
                dispatchRoute($controllerName, $method, $matches, $paramKeyMap);
                $routeFound = true;
                break;
            }
        }
    }
}

// Special handling for root and login
if (!$routeFound) {
    if ($uri === '/' || $uri === '/login') {
        $controller = new AuthController();
        $controller->login();
        $routeFound = true;
    }
}

// If no route found, show 404
if (!$routeFound) {
    http_response_code(404);
    echo "404 Not Found";
    exit;
}
