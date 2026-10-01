<?php

namespace App\Middleware;

use App\Session\SessionManager;

final class AdminMiddleware
{
    private SessionManager $session;

    public function __construct(
        ?SessionManager $session = null
    ) {

        $this->session = $session ?? new SessionManager();

    }

    public static function handle(): void
    {
        $middleware = new self();

        $middleware->requireAuth();
    }


    public function requireAuth(): void
    {
        if (!$this->session->get('admin_authenticated')) {
            header('Location: login');
            exit;
        }

    }

}
