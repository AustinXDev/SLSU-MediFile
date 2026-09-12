<?php 

namespace App\Controllers\Auth\LoginController;

use App\Services\Auth\Login\LoginService;

class LoginController
{

  public function __construct(
    private LoginService $loginService
  )
  {
  }

  public function login(
    string $username,
    string $password,
    string $ip
  ): array {

    return $this->loginService->Login(
      $username,
      $password,
      $ip
    );
  }

  public function verify(
    string $code,
    string $ip
  ): array {

    return $this->loginService->verify(
      $code,
      $ip
    );

  }

}

?>