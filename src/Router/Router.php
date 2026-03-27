<?php

use App\Controller\AuthController;
use App\Controller\HomeController;
use App\Controller\AccountController;
use App\Controller\CandidatureController;
use App\Core\View;
use App\Core\Auth;

$uri    = $_GET['uri'] ?? '/';
$method = $_SERVER['REQUEST_METHOD'];

$authController    = new AuthController();
$homeController    = new HomeController();
$accountController = new AccountController();
$candidatureController = new CandidatureController();

switch ($uri) {
    case '/':
        $homeController->index();
        break;

    case '/login':
        if (Auth::check()){
            header('Location: /');
        } else if ($method == 'GET') {
            $authController->showLogin();
        } else {
            $authController->login();
        }
        break;

    case '/logout':
        $authController->logout();
        break;

    case '/register':
        header('Location: /login');
        break;

    case '/account':
        $accountController->index();
        break;

    case '/noter-entreprise':
        if ($method === 'POST') {
            $accountController->noterEntreprise();
        } else {
            header('Location: /account');
            exit;
        }
        break;

    case '/mentions-legales':
        View::render('mentions_legales.html.twig');
        break;

    case '/candidater':
        if ($method === 'POST') {
            $candidatureController->submit();
        } else {
            $candidatureController->index();
        }
        break;

    case '/forbidden':
        http_response_code(403);
        View::render('Forbidden.html.twig');
        break;

    default:
        http_response_code(404);
        echo $uri;
}
