<?php

namespace App\Session;

class SessionManager
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Configure secure session options before starting
            session_start([
                'cookie_lifetime' => 0,
                'cookie_httponly' => true,
                'cookie_secure'   => true,
                'cookie_samesite' => 'Lax',
            ]);
        }
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $sessionName = session_name();
            $cookieParams = session_get_cookie_params();

            session_unset();

            if (ini_get('session.use_cookies')) {
                setcookie($sessionName, '', [
                    'expires' => time() - 42000,
                    'path' => $cookieParams['path'],
                    'domain' => $cookieParams['domain'],
                    'secure' => $cookieParams['secure'],
                    'httponly' => $cookieParams['httponly'],
                    'samesite' => $cookieParams['samesite'] ?? 'Lax',
                ]);
            }

            session_destroy();
        }
    }
}
