<?php

use App\Controller\AuthController;
use App\Controller\HomeController;

$uri = $_GET['uri'] ?? '/';
$method = $_SERVER['REQUEST_METHOD'];

$authController = new AuthController();
$homeController = new HomeController();

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

    default:
        http_response_code(404);
        echo $uri;
}