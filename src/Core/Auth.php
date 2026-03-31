<?php

namespace App\Core;

use App\Model\AuthModel;

/**
 * Authentication helper.
 *
 * Centralizes login/logout logic and password verification.
 * This class also supports a legacy password storage format and will upgrade it
 * to a modern password hash on successful login.
 */
class Auth
{
    /**
     * Attempt to authenticate a user with email + password.
     *
     * @return bool True when credentials are valid and the session is created.
     */
    public static function attempt(string $email, string $password): bool
    {
        $model = new AuthModel();
        $user = $model->getByEmail($email);

        if (!$user) {
            return false;
        }

        $hash = $user['Password'] ?? null;

        // Detect whether the stored value looks like a password_hash() output.
        // If not, treat it as a legacy “raw” password value (historical data).
        $info = is_string($hash) ? password_get_info($hash) : ['algo' => 0];
        if ($info['algo'] === 0) {
            // Legacy path: compare in constant-time and then upgrade to a real hash.
            if (!is_string($hash) || !hash_equals($hash, $password)) {
                return false;
            }

            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $model->updatePassword((int) $user['Id_user'], $newHash);
            $user['Password'] = $newHash;
        } else {
            // Normal path: verify and rehash when the default algorithm settings changed.
            if (!password_verify($password, $hash)) {
                return false;
            }

            if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $model->updatePassword((int) $user['Id_user'], $newHash);
                $user['Password'] = $newHash;
            }
        }

        self::login($user);
        return true;
    }

    /**
     * Create the authenticated session for a given user record.
     *
     * The password field is removed before storing in session.
     */
    public static function login(array $user): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Mitigate session fixation after authentication.
        session_regenerate_id(true);

        // Never store password hashes in session.
        unset($user['Password']);

        // Normalize the role key to a single, consistent name.
        if (!isset($user['Role']) && isset($user['Rôle'])) {
            $user['Role'] = $user['Rôle'];
        }

        unset($user['Rôle']);

        $_SESSION['user'] = $user;
        // Timestamp can be used for inactivity expiration or audit.
        $_SESSION['auth_at'] = time();
    }

    /**
     * Register a user and automatically log them in.
     *
     * @return array|false The created user record, or false on validation/DB failure.
     */
    public static function register(array $data): array|false
    {
        $model = new AuthModel();

        // Prevent duplicate accounts.
        if ($model->emailExists($data['Email'])) {
            return false;
        }

        // Always store a one-way hash.
        $data['Password'] = password_hash($data['Password'], PASSWORD_DEFAULT);

        $user = $model->createUser($data);
        if (!$user) {
            return false;
        }

        self::login($user);
        return $user;
    }

    /**
     * Log out the current user and destroy the session.
     */
    public static function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Clear all session data.
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            // Invalidate the session cookie on the client.
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
    }

    /**
     * @return bool True if a user record is present in session.
     */
    public static function check(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        return isset($_SESSION['user']);
    }

    /**
     * @return array|null The authenticated user record (without password), if any.
     */
    public static function user(): array|null
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        return $_SESSION['user'] ?? null;
    }

    /**
     * Enforce authentication for the current request.
     * Redirects to /login when not authenticated.
     */
    public static function requireAuth(): void
    {
        if (!self::check()) {
            header('Location: /login');
            exit;
        }
    }


}
