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

session_start();

$base = dirname($_SERVER['SCRIPT_NAME']);
define('BASE_PATH', $base === '/' ? '' : $base);

$config = require __DIR__ . '/app/config/config.php';

if (($config['app']['env'] ?? 'production') === 'local') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

define('PEPPER', $config['app']['pepper'] ?? '');

define('BGG_SOURCE', $config['app']['bgg_source'] ?? 'catalog');

define('BGG_TOKEN', $config['app']['bgg_token'] ?? '');

require __DIR__ . '/app/src/Core/Database.php';
$pdo = Database::getConnection($config);

$router = new Router();

// --- auth routy ---
$router->get('/register',  [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/login',  [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);

// --- home routy ---
$router->get('/', [HomeController::class, 'index']);

// --- sessions routy ---
$router->get('/sessions',        [SessionController::class, 'index']);
$router->get('/sessions/create',  [SessionController::class, 'create']);
$router->post('/sessions',        [SessionController::class, 'store']);
$router->get('/sessions/{id}',    [SessionController::class, 'show']);
$router->post('/sessions/{id}/delete', [SessionController::class, 'destroy']);
$router->get('/sessions/{id}/edit',    [SessionController::class, 'edit']);
$router->post('/sessions/{id}/cancel', [SessionController::class, 'cancel']);
$router->post('/sessions/{id}/reopen', [SessionController::class, 'reopen']);
$router->post('/sessions/{id}/update', [SessionController::class, 'update']);
$router->get('/sessions/{id}/calendar', [SessionController::class, 'calendar']);

// --- participační routy ---
$router->post('/sessions/{id}/join',  [ParticipationController::class, 'join']);
$router->post('/sessions/{id}/leave', [ParticipationController::class, 'leave']);
$router->post('/sessions/{id}/approve', [ParticipationController::class, 'approve']);
$router->post('/sessions/{id}/reject',  [ParticipationController::class, 'reject']);

// --- comment routy ---
$router->post('/sessions/{id}/comments', [CommentController::class, 'store']);
$router->post('/comments/{id}/delete',   [CommentController::class, 'destroy']);

// --- game routy ---
$router->get('/games/search', [GameController::class, 'search']);

// --- favorite routy ---
$router->get('/games',                 [FavoriteController::class, 'index']);
$router->post('/games/{id}/favorite',  [FavoriteController::class, 'toggle']);

// --- account routy ---
$router->get('/account',           [AccountController::class, 'index']);
$router->post('/account/profile',  [AccountController::class, 'updateProfile']);
$router->post('/account/password', [AccountController::class, 'updatePassword']);

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);


if($base !== '/' && str_starts_with($uri, $base)) {
    $uri = substr($uri, strlen($base));
}
if($uri === ''){
    $uri = '/';
}

$router->dispatch($method, $uri);