<?php 

require_once __DIR__ . '/../config/init.php';

define('BASE_URL', rtrim($_ENV['APP_URL'], '/') . '/');

$request = isset($_GET['url']) ? rtrim($_GET['url'], '/') : "";

$request = strtolower(rtrim($request, '/'));

switch($request) {

  case '':
    header("Location: " . BASE_URL . "Login");
    exit();
    
  case 'login':
    require_once __DIR__ . '/../templates/auth/Login.php';
    break;

  case 'dashboard':
    require_once __DIR__ . '/../templates/Dashboard.php';
    break;

  case 'csmdashboard':
    require_once __DIR__ . '/../templates/CsmDashboard.php';
    break;

  case 'patients':
    require_once __DIR__ . '/../templates/Patients.php';
    break;

  case 'users':
    require_once __DIR__ . '/../templates/Users.php';
    break;

  case 'activitylogs':
    require_once __DIR__ . '/../templates/ActivityLogs.php';
    break;

  default:
    http_response_code(404);
    echo "<h1>404 - Page Not Found</h1>";
    break;
}


?>