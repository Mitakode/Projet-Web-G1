<?php

use PHPUnit\Framework\TestCase;
use App\Core\Auth;

/**
 * Unit tests for the Auth session helper.
 */
final class CoreAuthTest extends TestCase
{
    protected function setUp(): void
    {
        // Auth relies on $_SESSION; initialize a clean session state for each test.
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION = [];
    }

    /**
     * Auth::login() should create the session user, set auth_at,
        * drop the password, and normalize the role key.
     */
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
        $this->assertArrayNotHasKey('Password', $sessionUser);
        $this->assertArrayHasKey('auth_at', $_SESSION);
    }

    /**
     * Auth::logout() should clear the session and make Auth::check() false.
     */
    public function testLogoutClearsSession(): void
    {
        $_SESSION['user'] = ['Id_user' => 1];

        Auth::logout();

        $this->assertFalse(Auth::check());
    }

    /**
     * Auth::user() should return null when not authenticated.
     */
    public function testUserReturnsNullWhenNotAuthenticated(): void
    {
        $this->assertNull(Auth::user());
    }

    /**
     * If the user record contains a legacy role key, Auth::login() should still
     * store a normalized `Role` value in session.
     */
    public function testLoginNormalizesLegacyRoleKey(): void
    {
        $user = [
            'Id_user' => 456,
            'Rôle' => 99,
        ];

        Auth::login($user);

        $sessionUser = Auth::user();
        $this->assertSame(99, $sessionUser['Role']);
        $this->assertArrayNotHasKey('Rôle', $sessionUser);
    }
}
