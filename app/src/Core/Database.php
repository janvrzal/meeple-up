<?php

class Database
{

    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getConnection(?array $config = null) : PDO{

        if (self::$instance === null) {
            if($config === null){
                throw new RuntimeException('Database not initialized, missing config');
            }
            $db = $config['database'];

            $dsn = "mysql:host={$db['host']};dbname={$db['name']};charset={$db['charset']}";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, $db['user'], $db['password'], $options);
            } catch (PDOException $e) {
                // NEvypisuj $e->getMessage() uživateli!
                error_log($e->getMessage());
                die('Database connection failed.');
            }
        }

        return self::$instance;
    }
}