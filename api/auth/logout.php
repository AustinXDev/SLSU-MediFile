<?php

require_once __DIR__ . '/../../config/init.php';

use App\Session\SessionManager;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode([
        'status' => 'error',
        'message' => 'Method Not Allowed. Use POST to sign out.',
    ]);
    exit;
}

(new SessionManager())->destroy();

echo json_encode([
    'status' => 'success',
    'redirect' => rtrim($_ENV['APP_URL'], '/') . '/login',
]);
