<?php

namespace App\Core;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

/**
 * Minimal Twig view renderer.
 *
 * Injects authentication variables (`isAuth`, `authUser`) into templates unless
 * explicitly provided by the caller.
 */
class View
{
    /**
     * Render a Twig template and echo the HTML.
     *
     * @param string $view Template filename, e.g. "home.html.twig".
     * @param array  $data Variables passed to Twig.
     */
    public static function render(string $view, array $data = []): void
    {
        // Twig template resolution is rooted at /templates.
        $loader = new FilesystemLoader(__DIR__ . "/../../templates");
        $twig = new Environment($loader);

        // Ensure the session is available to populate auth-related variables.
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Provide commonly used auth variables, while allowing controllers to override.
        if (!array_key_exists('isAuth', $data)) {
            $data['isAuth'] = isset($_SESSION['user']);
        }
        if (!array_key_exists('authUser', $data)) {
            $data['authUser'] = $_SESSION['user'] ?? null;
        }

        echo $twig->render($view, $data);
    }
}
