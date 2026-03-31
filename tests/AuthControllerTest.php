<?php

use PHPUnit\Framework\TestCase;
use App\Controller\AuthController;

final class AuthControllerTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION = [];
    }

    public function testShowLoginRendersError(): void
    {
        $_SESSION['auth_error'] = 'Identifiants invalides.';

        $controller = new AuthController();

        ob_start();
        $controller->showLogin();
        $html = ob_get_clean();

        $this->assertStringContainsString('auth-error', $html);
        $this->assertStringContainsString('Identifiants invalides.', $html);
        $this->assertArrayNotHasKey('auth_error', $_SESSION);
    }

    public function testShowLoginWithoutErrorDoesNotRenderErrorBlock(): void
    {
        $controller = new AuthController();

        ob_start();
        $controller->showLogin();
        $html = ob_get_clean();

        $this->assertStringNotContainsString('auth-error', $html);
        $this->assertStringContainsString('<h1>Connexion</h1>', $html);
    }
}
