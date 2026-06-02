<?php
$config = require __DIR__ . '/app/config/config.php';

if (($config['app']['env'] ?? 'production') === 'local') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

require __DIR__ . '/app/src/Core/Database.php';
$pdo = Database::getConnection($config);
$result = $pdo->query('SELECT 1')->fetchColumn();
var_dump($result);