<?php
// Automaticky requirne potřebné kontrolery
spl_autoload_register(function (string $class): void {
    $dirs = [
        __DIR__ . '/app/src/Core/',
        __DIR__ . '/app/src/Controllers/',
        __DIR__ . '/app/src/Models/',
        __DIR__ . '/app/src/Services/',
    ];

    foreach ($dirs as $dir) {
        $file = $dir . $class . '.php';
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

$config = require __DIR__ . '/app/config/config.php';

if (($config['app']['env'] ?? 'production') === 'local') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

require __DIR__ . '/app/src/Core/Database.php';
$pdo = Database::getConnection($config);

$router = new Router();

$router->get('/', function() {
    echo 'Welcome to Meeple-Up';
});

// později: $router->get('/login', [AuthController::class, 'showLogin']);
//          $router->post('/login', [AuthController::class, 'login']);

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$base = dirname($_SERVER['SCRIPT_NAME']);
if($base !== '/' && str_starts_with($uri, $base)) {
    $uri = substr($uri, strlen($base));
}
if($uri === ''){
    $uri = '/';
}

$router->dispatch($method, $uri);