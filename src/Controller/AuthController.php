<?php

namespace App\Controller;

use App\Core\Auth;
use App\Core\InputValidator;
use App\Core\View;
use InvalidArgumentException;

class AuthController
{
    public function showLogin(): void
    {
        $error = $_SESSION['auth_error'] ?? null;
        unset($_SESSION['auth_error']);

        View::render('auth/login.html.twig', [
            'error' => $error,
        ]);
    }

    public function login()
    {
        try {
            $email = InputValidator::requireEmail($_POST, 'email');
            // Mot de passe login: blocage des caractères nuls et taille max raisonnable.
            $password = InputValidator::requireString(
                $_POST,
                'password',
                '/^[^\x00]{1,255}$/',
                255
            );
        } catch (InvalidArgumentException $e) {
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
            header('Location: /account');
            exit;
        }

        $_SESSION['auth_error'] = 'Identifiants invalides.';
        header('Location: /login');
        exit;
    }

    public function logout()
    {
        Auth::logout();
        header('Location: /login');
        exit;
    }

    public function register()
    {
        header('Location: /login');
        exit;
    }

    public function forbidden()
    {
        header('Location: /forbidden');
        exit;
    }

}
