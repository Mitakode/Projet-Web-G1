<?php

use PHPUnit\Framework\TestCase;
use App\Core\View;

final class ViewTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION = [];
    }

    public function testRenderUsesSessionAuthByDefault(): void
    {
        $_SESSION['user'] = [
            'Id_user' => 1,
            'Role' => 0,
        ];

        ob_start();
        View::render('Forbidden.html.twig');
        $html = ob_get_clean();

        $this->assertStringContainsString('href="/logout"', $html);
        $this->assertStringNotContainsString('href="/login"', $html);
    }

    public function testRenderRespectsExplicitIsAuthValue(): void
    {
        $_SESSION['user'] = [
            'Id_user' => 1,
            'Role' => 0,
        ];

        ob_start();
        View::render('Forbidden.html.twig', [
            'isAuth' => false,
        ]);
        $html = ob_get_clean();

        $this->assertStringContainsString('href="/login"', $html);
        $this->assertStringNotContainsString('href="/logout"', $html);
    }
}
