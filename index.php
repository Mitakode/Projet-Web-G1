<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();


if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

require_once __DIR__ . '/config/config.php';

// Récupération de l'URL
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];


$routes = require_once __DIR__ . '/src/Router/Router.php';

$route_found = false;

foreach ($routes as $pattern => $handler) {
    if ($uri === $pattern) {
        list($controller, $action) = explode('@', $handler);


        $controller_class = 'App\\Controller\\' . $controller;

        if (class_exists($controller_class)) {
            $ctrl = new $controller_class();
            $ctrl->$action();
            $route_found = true;
            break;
        } else {

            die("Erreur : Le contrôleur $controller_class n'existe pas ou n'a pas pu être chargé (problème de namespace ou d'autoloader).");
        }
    }
}

if (!$route_found){
    http_response_code(404);
    echo "Page non trouvée (L'URL $uri ne correspond à aucune route dans src/Router/Router.php)";
}
?>