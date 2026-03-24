<?php

namespace App\Controller;

use App\Core\Auth;
use App\Core\View;

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
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

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
        View::render('auth/forbiden.html.twig');
    }

}
