<?php
session_start();


require_once __DIR__ . '/../config/config.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$method = $_SERVER['REQUEST_METHOD'];

$routes = require_once __DIR__ . '/../Router/Router.php';

$route_found = false;

foreach ($routes as $pattern => $handler) {

    if ($uri === $pattern) {

    list($controller, $action) = explode('@', $handler);

    $controller_class = 'controllers\\' . $controller;

    $ctrl = new $controller_class();

    $ctrl->$action();

    $route_found = true;

    break;

    }

}

    if (!$route_found){

    http_response_code(404);

    echo "Page non trouvée";

    }


?>