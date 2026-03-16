<?php

namespace App\Controller;

use App\Core\View;

class AuthController
{
    public function showLogin(): void
    {
        View::render('auth/login.html.twig');
    }

    public function login()
    {

    }

    public function logout()
    {

    }

    public function forbidden()
    {

    }

}