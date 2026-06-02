<?php

class Auth
{
    private static ?array $cachedUser = null;

    public static function login(array $user) : void {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        self::$cachedUser = null;
    }

    public static function logout() : void {
        $_SESSION = [];
        session_destroy();
        self::$cachedUser = null;
    }

    public static function check() : bool {
        return isset($_SESSION['user_id']);
    }

    public static function id() : ?int {
        return $_SESSION['user_id'] ?? null;
    }

    public static function user() : ?array {
        if (!self::check()) {
            return null;
        }

        // ještě nenačtený? načíst do cache
        if (self::$cachedUser === null) {
            self::$cachedUser = (new User())->findById(self::id());
        }

        return self::$cachedUser;
    }
}