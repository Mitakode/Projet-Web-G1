<?php

use PHPUnit\Framework\TestCase;
use App\Core\View;

/**
 * Unit tests for the Twig View renderer.
 */
final class ViewTest extends TestCase
{
    protected function setUp(): void
    {
        // View injects auth variables from $_SESSION unless explicitly set.
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION = [];
    }

    /**
     * When authenticated, View::render should pass isAuth=true by default.
     */
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

    /**
     * When caller explicitly passes isAuth, it must override session-derived state.
     */
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
