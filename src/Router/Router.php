<?php

use App\Controller\AuthController;
use App\Controller\HomeController;
use App\Controller\AccountController;
use App\Controller\PiloteController;
use App\Core\View;

$uri    = $_GET['uri'] ?? '/';
$method = $_SERVER['REQUEST_METHOD'];

$authController    = new AuthController();
$homeController    = new HomeController();
$accountController = new AccountController();
$pilotecontroller = new PiloteController();

switch ($uri) {
    case '/':
        $homeController->index();
        break;

    case '/login':
        if ($method == 'GET') {
            $authController->showLogin();
        } else {
            $authController->login();
        }
        break;

    case '/logout':
        $authController->logout();
        break;

    case '/account':
        $accountController->index();
        break;
    
    case '/account/student_list':
        $pilotecontroller->index();
        break;

    case '/mentions-legales':
        View::render('mentions_legales.html.twig');
        break;

    default:
        http_response_code(404);
        echo $uri;
}