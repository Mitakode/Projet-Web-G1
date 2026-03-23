<?php

namespace App\Core;

use App\Model\AuthModel;

class Auth
{
    public static function attempt(string $email, string $password): bool
    {
        $model = new AuthModel();
        $user = $model->getByEmail($email);

        if (!$user) {
            return false;
        }

        $hash = $user['Password'] ?? null;
        if (!self::verifyPassword($password, $hash)) {
            return false;
        }

        self::login($user);
        return true;
    }

    public static function login(array $user): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        session_regenerate_id(true);

        unset($user['Password']);
        if (isset($user['Role']) && !isset($user['Rôle'])) {
            $user['Rôle'] = $user['Role'];
        }

        $_SESSION['user'] = $user;
        $_SESSION['auth_at'] = time();
    }

    public static function register(array $data): array|false
    {
        $model = new AuthModel();

        if ($model->emailExists($data['Email'])) {
            return false;
        }

        $data['Password'] = password_hash($data['Password'], PASSWORD_DEFAULT);

        $user = $model->createUser($data);
        if (!$user) {
            return false;
        }

        self::login($user);
        return $user;
    }

    public static function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
    }

    public static function check(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        return isset($_SESSION['user']);
    }

    public static function user(): array|null
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        return $_SESSION['user'] ?? null;
    }

    public static function requireAuth(): void
    {
        if (!self::check()) {
            header('Location: /login');
            exit;
        }
    }

    private static function verifyPassword(string $password, ?string $hash): bool
    {
        if (!$hash) {
            return false;
        }

        if (password_verify($password, $hash)) {
            return true;
        }

        return hash_equals($hash, $password);
    }
}
