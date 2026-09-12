<?php 
require_once __DIR__ . '/../../config/init.php';

use App\Repositories\AdminRepository;
use App\Services\Auth\Login\LoginRateLimiter;
use App\Repositories\LoginAttemptRepository;
use App\Repositories\TwoFactorRepository;
use App\Services\Auth\TwoFactor\TwoFactorService;

use App\Services\Auth\Login\LoginService;

use App\Controllers\Auth\LoginController\LoginController;

use App\Provider\Mailer;

header('Content-type: application/json');

try {

  $input = json_decode(
    file_get_contents('php://input'),
    true
  ) ?? [];

  $code = trim($input['code'] ?? '');

  $ip = $_SERVER['REMOTE_ADDR'] ?? '';

  require_once __DIR__ . '/../../App/database/database.php';

  /**
   * Repositories
   */
  $adminRepo = new AdminRepository($pdo);
  $attemptsRepo = new LoginAttemptRepository($pdo);
  $twoFactorRepo = new TwoFactorRepository($pdo);

  $rateLimiter = new LoginRateLimiter($attemptsRepo);

  $mailer = new Mailer();

  /**
   * Services
   */
  $twoFactorService = new TwoFactorService($adminRepo, $twoFactorRepo, $mailer);

  $service = new LoginService($adminRepo, $rateLimiter, $twoFactorService);

  /**
   * Controllers
   */
  $controller = new LoginController($service);

  $reponse = $controller->verify($code, $ip);

  /**
   * Success
   */
  http_response_code(200);

  echo json_encode($reponse);

} catch (Throwable $e) {

  http_response_code(400);

  echo json_encode([
    'status'  => 'error',
    'message' =>  $e->getMessage()
  ]);

}


?>