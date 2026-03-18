<?php

use App\Controller\AuthController;
use App\Controller\HomeController;
use App\Controller\AdminController;   // A supprimer après migration
use App\Controller\AccountController;
use App\Core\View;

$uri = $_GET['uri'] ?? '/';
$method = $_SERVER['REQUEST_METHOD'];

$authController = new AuthController();
$homeController = new HomeController();
$adminController = new AdminController(); // A supprimer après migration
$accountController = new AccountController();

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

    case '/mentions-legales':
        View::render('mentions_legales.html.twig');
        break;

    case '/admin': // A supprimer après migration
        $adminController->index();
    case '/account':
        $accountController->index();
        break;

    default:
        http_response_code(404);
        echo $uri;
}