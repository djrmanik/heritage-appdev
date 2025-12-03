<?php
/**
 * Front Controller - Heritage Family Tree Application
 * Handles all HTTP requests and routes them to appropriate controllers
 */

// Start session
session_start();

// Set timezone
date_default_timezone_set('UTC');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Load helpers
require_once __DIR__ . '/../app/helpers/functions.php';

// Get request method and URI
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove base path if application is in subdirectory
$basePath = '/heritage/public';
if (strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}

// Remove trailing slash
$requestUri = rtrim($requestUri, '/');
if (empty($requestUri)) {
    $requestUri = '/';
}

// Route the request
try {
    route($requestMethod, $requestUri);
} catch (Exception $e) {
    error_log("Routing Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal Server Error']);
}

/**
 * Router function
 */
function route(string $method, string $uri): void {
    // API Routes
    if (strpos($uri, '/api/') === 0) {
        handleApiRoute($method, $uri);
        return;
    }
    
    // Web Routes
    handleWebRoute($method, $uri);
}

/**
 * Handle API routes
 */
function handleApiRoute(string $method, string $uri): void {
    $uri = substr($uri, 4); // Remove '/api' prefix
    
    // Auth routes
    if ($uri === '/register' && $method === 'POST') {
        $controller = new \App\Controllers\AuthController();
        $controller->register();
        return;
    }
    
    if ($uri === '/login' && $method === 'POST') {
        $controller = new \App\Controllers\AuthController();
        $controller->login();
        return;
    }
    
    if ($uri === '/logout' && $method === 'POST') {
        $controller = new \App\Controllers\AuthController();
        $controller->logout();
        return;
    }
    
    if ($uri === '/me' && $method === 'GET') {
        $controller = new \App\Controllers\AuthController();
        $controller->getCurrentUser();
        return;
    }
    
    // Family routes
    if (preg_match('#^/families$#', $uri) && $method === 'GET') {
        $controller = new \App\Controllers\FamilyController();
        $controller->index();
        return;
    }
    
    if (preg_match('#^/families$#', $uri) && $method === 'POST') {
        $controller = new \App\Controllers\FamilyController();
        $controller->create();
        return;
    }
    
    if (preg_match('#^/families/([a-f0-9\-]{36})$#', $uri, $matches) && $method === 'GET') {
        $controller = new \App\Controllers\FamilyController();
        $controller->show($matches[1]);
        return;
    }
    
    if (preg_match('#^/families/([a-f0-9\-]{36})$#', $uri, $matches) && $method === 'PUT') {
        $controller = new \App\Controllers\FamilyController();
        $controller->update($matches[1]);
        return;
    }
    
    if (preg_match('#^/families/([a-f0-9\-]{36})$#', $uri, $matches) && $method === 'DELETE') {
        $controller = new \App\Controllers\FamilyController();
        $controller->delete($matches[1]);
        return;
    }
    
    if (preg_match('#^/families/([a-f0-9\-]{36})/members$#', $uri, $matches) && $method === 'POST') {
        $controller = new \App\Controllers\FamilyController();
        $controller->addMember($matches[1]);
        return;
    }
    
    if (preg_match('#^/families/([a-f0-9\-]{36})/members/([a-f0-9\-]{36})$#', $uri, $matches) && $method === 'DELETE') {
        $controller = new \App\Controllers\FamilyController();
        $controller->removeMember($matches[1], $matches[2]);
        return;
    }
    
    // Person routes
    if (preg_match('#^/persons$#', $uri) && $method === 'GET') {
        $controller = new \App\Controllers\PersonController();
        $controller->index();
        return;
    }
    
    if (preg_match('#^/persons$#', $uri) && $method === 'POST') {
        $controller = new \App\Controllers\PersonController();
        $controller->create();
        return;
    }
    
    if (preg_match('#^/persons/search$#', $uri) && $method === 'GET') {
        $controller = new \App\Controllers\PersonController();
        $controller->search();
        return;
    }
    
    if (preg_match('#^/persons/([a-f0-9\-]{36})$#', $uri, $matches) && $method === 'GET') {
        $controller = new \App\Controllers\PersonController();
        $controller->show($matches[1]);
        return;
    }
    
    if (preg_match('#^/persons/([a-f0-9\-]{36})$#', $uri, $matches) && $method === 'PUT') {
        $controller = new \App\Controllers\PersonController();
        $controller->update($matches[1]);
        return;
    }
    
    if (preg_match('#^/persons/([a-f0-9\-]{36})$#', $uri, $matches) && $method === 'DELETE') {
        $controller = new \App\Controllers\PersonController();
        $controller->delete($matches[1]);
        return;
    }
    
    // Relationship routes
    if (preg_match('#^/relationships$#', $uri) && $method === 'GET') {
        $controller = new \App\Controllers\RelationshipController();
        $controller->index();
        return;
    }
    
    if (preg_match('#^/relationships$#', $uri) && $method === 'POST') {
        $controller = new \App\Controllers\RelationshipController();
        $controller->create();
        return;
    }
    
    if (preg_match('#^/relationships/([a-f0-9\-]{36})$#', $uri, $matches) && $method === 'GET') {
        $controller = new \App\Controllers\RelationshipController();
        $controller->show($matches[1]);
        return;
    }
    
    if (preg_match('#^/relationships/([a-f0-9\-]{36})$#', $uri, $matches) && $method === 'PUT') {
        $controller = new \App\Controllers\RelationshipController();
        $controller->update($matches[1]);
        return;
    }
    
    if (preg_match('#^/relationships/([a-f0-9\-]{36})$#', $uri, $matches) && $method === 'DELETE') {
        $controller = new \App\Controllers\RelationshipController();
        $controller->delete($matches[1]);
        return;
    }
    
    // Tree routes
    if (preg_match('#^/tree/([a-f0-9\-]{36})$#', $uri, $matches) && $method === 'GET') {
        $controller = new \App\Controllers\TreeController();
        $controller->getTreeData($matches[1]);
        return;
    }
    
    if (preg_match('#^/tree/([a-f0-9\-]{36})/simple$#', $uri, $matches) && $method === 'GET') {
        $controller = new \App\Controllers\TreeController();
        $controller->getSimpleTree($matches[1]);
        return;
    }
    
    // Not found
    jsonResponse(['error' => 'API endpoint not found'], 404);
}

/**
 * Handle web routes (views)
 */
function handleWebRoute(string $method, string $uri): void {
    $viewsPath = __DIR__ . '/../app/views';
    
    // Home/Login
    if ($uri === '/' || $uri === '/login.php') {
        if (isAuthenticated()) {
            header('Location: dashboard.php');
            exit;
        }
        require $viewsPath . '/auth/login.php';
        return;
    }
    
    if ($uri === '/register.php') {
        if (isAuthenticated()) {
            header('Location: dashboard.php');
            exit;
        }
        require $viewsPath . '/auth/register.php';
        return;
    }
    
    // Logout
    if ($uri === '/logout.php') {
        session_destroy();
        header('Location: login.php');
        exit;
    }
    
    // Protected routes - require authentication
    \App\Middleware\AuthMiddleware::requireAuth();
    
    if ($uri === '/dashboard.php') {
        require $viewsPath . '/dashboard/index.php';
        return;
    }
    
    if ($uri === '/families.php') {
        require $viewsPath . '/families/index.php';
        return;
    }
    
    if ($uri === '/families/create.php') {
        require $viewsPath . '/families/create.php';
        return;
    }
    
    if (preg_match('#^/families/show\.php$#', $uri)) {
        require $viewsPath . '/families/show.php';
        return;
    }
    
    if ($uri === '/persons.php') {
        require $viewsPath . '/persons/index.php';
        return;
    }
    
    if ($uri === '/persons/create.php') {
        require $viewsPath . '/persons/create.php';
        return;
    }
    
    if (preg_match('#^/persons/show\.php$#', $uri)) {
        require $viewsPath . '/persons/show.php';
        return;
    }
    
    if (preg_match('#^/tree\.php$#', $uri)) {
        require $viewsPath . '/tree/show.php';
        return;
    }
    
    // 404 Not Found
    http_response_code(404);
    echo '<h1>404 - Page Not Found</h1>';
}