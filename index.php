<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);


    session_start();

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

require_once __DIR__ . '/config/config.php';

// Récupération de l'URL "propre" (sans les dossiers d'installation si tu n'es pas en VirtualHost)
$uri = $_SERVER['REQUEST_URI'];

// Si tu es dans un sous-dossier (ex: localhost/Projet-Web-G1/), on enlève "Projet-Web-G1" de l'URL
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
if ($scriptName !== '/') {
    $uri = str_replace($scriptName, '', $uri);
}

// On enlève la partie "?page=2" de l'URL pour le routeur
$uri = explode('?', $uri)[0];

// On s'assure que si c'est vide, ça correspond bien à '/'
if (empty($uri)) {
    $uri = '/';
}

$method = $_SERVER['REQUEST_METHOD'];

$routes = require_once __DIR__ . '/src/Router/Router.php';

$route_found = false;

foreach ($routes as $pattern => $handler) {
    
    // On construit la signature attendue par ton routeur (ex: "GET /connexion")
    // Note: Pour "/", ton routeur n'a pas de "GET" devant, donc on le gère à part.
    $signature = ($pattern === '/') ? '/' : $method . ' ' . $uri;

    if ($signature === $pattern || $uri === $pattern) {
        list($controller, $action) = explode('@', $handler);

        $controller_class = 'App\\Controller\\' . $controller;

        if (class_exists($controller_class)) {
            $ctrl = new $controller_class();
            $ctrl->$action();
            $route_found = true;
            break;
        } else {
            die("Erreur : Le contrôleur <b>$controller_class</b> n'existe pas. N'oublie pas de faire 'composer dump-autoload' dans ton terminal.");
        }
    }
}

if (!$route_found){
    http_response_code(404);
    echo "<h1>Page non trouvée (404)</h1>";
    echo "L'URL demandée : <b>" . htmlspecialchars($uri) . "</b> n'existe pas dans le routeur.";
}
?>