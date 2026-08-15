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
    string $password
  ): array {

    return $this->loginService->Login(
      $username,
      $password
    );
  }

}

?>