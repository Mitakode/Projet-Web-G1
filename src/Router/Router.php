<?php

use App\Controller\AuthController;
use App\Controller\HomeController;
use App\Controller\AccountController;
use App\Controller\DashboardController;
use App\Controller\CandidatureController;
use App\Controller\EntreprisePublicController;
use App\Controller\DocumentController;
use App\Core\View;
use App\Core\Auth;
use App\Core\InputValidator;

$uri    = (string) ($_GET['uri'] ?? '/');
$uri = trim($uri);
// URI de route: autorise uniquement /, lettres, chiffres, _ et -.
if (!InputValidator::regex($uri, '#^/[A-Za-z0-9_\-/]*$#')) {
    $uri = '/';
}
$method = $_SERVER['REQUEST_METHOD'];

$authController    = new AuthController();
$homeController    = new HomeController();
$accountController = new AccountController();
$candidatureController = new CandidatureController();
$entreprisePublicController = new EntreprisePublicController();
$documentController = new DocumentController();

switch ($uri) {
    case '/':
        $homeController->index();
        break;

    case '/login':
        if (Auth::check()) {
            header('Location: /');
        } elseif ($method == 'GET') {
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
    
    case '/addWishlist':
        $homeController->addWishlist();
        break;

    case '/deleteWishlist':
        $homeController->deleteWishlist();
        break;

    case '/companies':
        $entreprisePublicController->index();
        break;

    case '/company':
        $entreprisePublicController->fiche();
        break;

    case '/student_list':
    case '/eleve':
    case '/student_creation':
    case '/enterprise_list':
    case '/offer_list':
    case '/offres_de_stages':
    case '/entreprise':
    case '/add_entreprise':
    case '/pilotes_list':
    case '/pilote':
    case '/add_pilote':
    case '/dashboard':
        $dashboardController = new DashboardController();
        match ($uri) {
            '/student_list'    => $dashboardController->index(),
            '/eleve'           => $dashboardController->index(),
            '/student_creation'=> $dashboardController->addEleve(),
            '/enterprise_list' => $dashboardController->listEntreprise(),
            '/offer_list'      => $dashboardController->listOffre(),
            '/offres_de_stages'=> $dashboardController->Offre(),
            '/entreprise'      => $dashboardController->Entreprise(),
            '/add_entreprise'  => $dashboardController->addEntreprise(),
            '/pilotes_list'    => $dashboardController->listPilotes(),
            '/pilote'          => $dashboardController->Pilote(),
            '/add_pilote'      => $dashboardController->addPilote(),
            '/dashboard'       => $dashboardController->dashboard(),
        };
        break;
        
    case '/candidater':
        if ($method === 'POST') {
            $candidatureController->submit();
        } else {
            $candidatureController->index();
        }
        break;

    case '/document':
        if ($method !== 'GET') {
            http_response_code(405);
            break;
        }

        $documentController->download();
        break;

    case '/forbidden':
        http_response_code(403);
        View::render('Forbidden.html.twig');
        break;

    default:
        http_response_code(404);
        echo $uri;
}
