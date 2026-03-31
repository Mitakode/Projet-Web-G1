<?php

use App\Controller\AuthController;
use App\Controller\HomeController;
use App\Controller\AccountController;
use App\Controller\DashboardController;
use App\Controller\CandidatureController;
use App\Controller\EntreprisePublicController;
use App\Core\View;
use App\Core\Auth;
use App\Core\InputValidator;

// Single entry router.
// It dispatches requests based on the `uri` query string parameter (front-controller style).
// NOTE: This is a simple switch-based router; for larger apps, consider a proper routing table.
$uri    = (string) ($_GET['uri'] ?? '/');
$uri = trim($uri);
// Route URI: only allow /, letters, digits, underscore, dash and nested segments.
// This prevents unexpected characters from being reflected or used for routing.
if (!InputValidator::regex($uri, '#^/[A-Za-z0-9_\-/]*$#')) {
    $uri = '/';
}
$method = $_SERVER['REQUEST_METHOD'];

// Instantiate controllers once; some routes will reuse the same instance.
$authController    = new AuthController();
$homeController    = new HomeController();
$accountController = new AccountController();
$candidatureController = new CandidatureController();
$entreprisePublicController = new EntreprisePublicController();

switch ($uri) {
    case '/':
        $homeController->index();
        break;

    case '/login':
        // If already logged in, avoid showing the login form.
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
        // Registration is currently redirected to login (feature not exposed here).
        header('Location: /login');
        break;

    case '/account':
        $accountController->index();
        break;

    case '/noter-entreprise':
        // Only accept POST to rate a company; otherwise redirect back.
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
        // Adds an offer/company to the wishlist.
        $homeController->addWishlist();
        break;

    case '/deleteWishlist':
        // Removes an item from the wishlist.
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
        // Admin dashboard routes are grouped here and dispatched using match().
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
        // Candidate page: GET shows the page, POST submits a candidature.
        if ($method === 'POST') {
            $candidatureController->submit();
        } else {
            $candidatureController->index();
        }
        break;

    case '/forbidden':
        // Explicit 403 page.
        http_response_code(403);
        View::render('Forbidden.html.twig');
        break;

    default:
        http_response_code(404);
        echo $uri;
}
