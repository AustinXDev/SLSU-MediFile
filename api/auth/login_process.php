<?php

require_once __DIR__ . '/../../config/init.php';

use App\Repositories\AdminRepository;
use App\Services\Auth\Login\LoginService;
use App\Controllers\Auth\LoginController\LoginController;

header('Content-Type: application/json');

try {

    $input = json_decode(
        file_get_contents('php://input'),
        true
    ) ?? [];

    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';

    if ($username === '' || $password === '') {

        http_response_code(400);

        echo json_encode([
            'status' => 'error',
            'message' => 'Username and password are required.'
        ]);

        exit;
    }

    require_once __DIR__ . '/../../App/database/database.php';

    $adminRepo = new AdminRepository($pdo);

    $auth = new LoginService($adminRepo);

    $controller = new LoginController($auth);

    $response = $controller->login(
        $username,
        $password
    );

    http_response_code(200);

    echo json_encode($response);

} catch (Throwable $e) {

    http_response_code(400);

    echo json_encode([
      'status' => 'error',
      'message' => $e->getMessage()
    ]);

    exit;
}