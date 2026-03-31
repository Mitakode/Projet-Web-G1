<?php

namespace App\Controller;

use App\Core\Auth;
use App\Core\InputValidator;
use App\Core\View;
use InvalidArgumentException;

/**
 * Authentication controller.
 *
 * Handles rendering the login page and performing login/logout actions.
 */
class AuthController
{
    /**
     * Display the login page.
     *
     * Reads and clears a one-time error message stored in session.
     */
    public function showLogin(): void
    {
        // Flash-style error message (persisted through redirect).
        $error = $_SESSION['auth_error'] ?? null;
        unset($_SESSION['auth_error']);

        View::render('auth/login.html.twig', [
            'error' => $error,
        ]);
    }

    /**
     * Process the login form submission.
     *
     * Validates inputs, attempts authentication, then redirects.
     */
    public function login()
    {
        try {
            $email = InputValidator::requireEmail($_POST, 'email');
            // Login password: disallow null bytes and keep a reasonable max length.
            $password = InputValidator::requireString(
                $_POST,
                'password',
                '/^[^\x00]{1,255}$/',
                255
            );
        } catch (InvalidArgumentException $e) {
            // Keep the error generic on purpose (avoid leaking which field failed).
            $_SESSION['auth_error'] = 'Email et mot de passe requis.';
            header('Location: /login');
            exit;
        }

        if ($email === '' || $password === '') {
            $_SESSION['auth_error'] = 'Email et mot de passe requis.';
            header('Location: /login');
            exit;
        }

        if (Auth::attempt($email, $password)) {
            // Success: redirect to the authenticated area.
            header('Location: /account');
            exit;
        }

        // Failure: return to login with a generic message.
        $_SESSION['auth_error'] = 'Identifiants invalides.';
        header('Location: /login');
        exit;
    }

    /**
     * Log out and redirect to login.
     */
    public function logout()
    {
        Auth::logout();
        header('Location: /login');
        exit;
    }

    /**
     * Registration is not implemented in this controller (route redirects to /login).
     */
    public function register()
    {
        header('Location: /login');
        exit;
    }

    /**
     * Convenience redirect to the forbidden page.
     */
    public function forbidden()
    {
        header('Location: /forbidden');
        exit;
    }

}
