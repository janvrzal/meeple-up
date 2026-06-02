<?php
$envPath = __DIR__.'/../.env';

if (!file_exists($envPath)) {
    die("Critical error: Environment file '$envPath' does not exist.");
}

$env = parse_ini_file($envPath, true);

if ($env === FALSE) {
    die("Critical error: Failed to parse environment file '$envPath'.");
}

return [
    'app' => [
        'env' => $env['APP_ENV'] ?? 'production',
    ],
    'database' => [
        'host'     => $env['DB_HOST'],
        'name'     => $env['DB_NAME'],
        'user'     => $env['DB_USER'],
        'password' => $env['DB_PASS'],
        'charset'  => $env['DB_CHARSET'] ?? 'utf8mb4',
    ],
];