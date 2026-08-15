<?php 

namespace App\Services\Auth\Login;

use App\Repositories\AdminRepository;
use RuntimeException;

class LoginService 
{

  public function __construct(
    private AdminRepository $admins
  )
  {
  }

  
  public function Login(
    string $username,
    string $password
  ): array {

    if($username === '' || $password === ''){
      throw new RuntimeException(
        "Username and password are required."
      );
    }

    /**
     * Find admin
     */
    $admin = $this->admins->findByUsername(
      $username
    );

    if(!$admin) {

      throw new \RuntimeException(
        "Incorrect Username or password."
      );

    }

    if(!password_verify(
      $password,
      $admin->passwordHash
    )) {

      throw new RuntimeException(
        "Incorrect username or password."
      );

    }

    return[
      'status'  =>  'success',
      'message' =>  'Successfully Login'
    ];


  }

}

?>