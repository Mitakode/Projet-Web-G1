<?php

use App\Controller\AuthController;
use App\Controller\HomeController;
use App\Controller\AccountController;
use App\Controller\DashboardController;
use App\Core\View;
use App\Core\Auth;

$uri    = $_GET['uri'] ?? '/';
$method = $_SERVER['REQUEST_METHOD'];

$authController    = new AuthController();
$homeController    = new HomeController();
$accountController = new AccountController();
$dashboardController = new DashboardController();

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
        $dashboardController->index();
        break;
    
    case '/enterprise_list':
        $dashboardController->listEntreprise();
        break;
    
    case '/offer_list':
        $dashboardController->listOffre();
        break;

    case '/mentions-legales':
        View::render('mentions_legales.html.twig');
        break;
    
    case '/eleve':
        $dashboardController->index();
        break;
    
    case '/offres_de_stages':
        $dashboardController->Offre();
        break;
    
    case '/student_creation':
        $dashboardController->addEleve();
        break;
    
    case '/entreprise':
    $dashboardController->Entreprise();
    break;

    case '/enterprise_list':
    $dashboardController->listEntreprise();
    break;

    case '/add_entreprise':
    $dashboardController->addEntreprise();
    break;

    case '/pilotes_list':
    $dashboardController->listPilotes();
    break;

    case '/pilote':
    $dashboardController->Pilote();
    break;

    case '/add_pilote':
    $dashboardController->addPilote();
    break;

    default:
        http_response_code(404);
        echo $uri;
}
