<?php

use App\Controller\AuthController;
use App\Controller\HomeController;
use App\Controller\AccountController;
use App\Controller\PiloteController;
use App\Controller\UserController;
use App\Core\View;
use App\Core\Auth;

$uri    = $_GET['uri'] ?? '/';
$method = $_SERVER['REQUEST_METHOD'];

$authController    = new AuthController();
$homeController    = new HomeController();
$accountController = new AccountController();
$pilotecontroller = new PiloteController();
$userController = new UserController();

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
    
    case '/student_list':
        $pilotecontroller->index();
        break;
    
    case '/enterprise_list':
        $pilotecontroller->listEntreprise();
        break;
    
    case '/offer_list':
        $pilotecontroller->listOffre();
        break;

    case '/mentions-legales':
        View::render('mentions_legales.html.twig');
        break;
    
    case '/user':
        $userController->index();
        break;

    default:
        http_response_code(404);
        echo $uri;
}
