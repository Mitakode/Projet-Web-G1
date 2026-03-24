<?php

namespace App\Core;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

class View
{
    public static function render(string $view, array $data = []): void
    {
        $loader = new FilesystemLoader(__DIR__ . "/../../templates");
        $twig = new Environment($loader);

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!array_key_exists('isAuth', $data)) {
            $data['isAuth'] = isset($_SESSION['user']);
        }
        if (!array_key_exists('authUser', $data)) {
            $data['authUser'] = $_SESSION['user'] ?? null;
        }

        echo $twig->render($view, $data);
    }
}
