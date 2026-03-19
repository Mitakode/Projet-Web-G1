<?php

use App\Controller\AuthController;
use App\Controller\HomeController;
use App\Controller\FicheController;

$uri = $_GET['uri'] ?? '/';
$method = $_SERVER['REQUEST_METHOD'];

$authController = new AuthController();
$homeController = new HomeController();
$ficheController = new FicheController();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

switch ($uri) {
    case '/':
        $homeController->index();
        break;

    case '/fiche-personne':
        $ficheController->show();
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
