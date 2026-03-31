<?php

use PHPUnit\Framework\TestCase;
use App\Core\Auth;

final class CoreAuthTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION = [];
    }

    public function testLoginSetsSession(): void
    {
        $user = [
            'Id_user' => 123,
            'Role' => 1,
            'Password' => 'fake-hash',
            'Email' => 'test@example.com',
        ];

        Auth::login($user);

        $this->assertTrue(Auth::check());

        $sessionUser = Auth::user();
        $this->assertSame(123, $sessionUser['Id_user']);
        $this->assertSame(1, $sessionUser['Role']);
        $this->assertSame(1, $sessionUser['Rôle']);
        $this->assertArrayNotHasKey('Password', $sessionUser);
        $this->assertArrayHasKey('auth_at', $_SESSION);
    }

    public function testLogoutClearsSession(): void
    {
        $_SESSION['user'] = ['Id_user' => 1];

        Auth::logout();

        $this->assertFalse(Auth::check());
    }

    public function testUserReturnsNullWhenNotAuthenticated(): void
    {
        $this->assertNull(Auth::user());
    }

    public function testLoginDoesNotOverrideExistingRoleLabel(): void
    {
        $user = [
            'Id_user' => 456,
            'Role' => 2,
            'Rôle' => 99,
        ];

        Auth::login($user);

        $sessionUser = Auth::user();
        $this->assertSame(99, $sessionUser['Rôle']);
    }
}
