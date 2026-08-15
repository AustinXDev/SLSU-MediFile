<?php 

namespace App\Repositories;

use App\Models\Admin;
use PDO;

class AdminRepository
{

  public function __construct(
    private PDO $pdo
  )
  {
  }

  //Find the Admin based on username
  public function findByUsername(
    string $username
  ): ?Admin {

    $stmt = $this->pdo->prepare("
      SELECT * 
      FROM admin
      WHERE username = ?
      LIMIT 1
    ");

    $stmt->execute([$username]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row
    ? Admin::fromArray($row)
    :null;
  }


  //Find Admin based on Id
  public function findById(
    int $adminId
  ): ?Admin {

    $stmt = $this->pdo->prepare("
      SELECT * 
      FROM admin
      WHERE admin_id = ?
      LIMIT 1
    ");

    $stmt->execute([$adminId]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row 
    ? Admin::fromArray($row)
    : null;

  }


}

?>