<?php

require_once __DIR__ . '/../init.php';

/**
 * Database Configuration
 * 
 * this will serves as connection of the system to the database
 */

$host     = trim($_ENV['DB_HOST']) ?? '';
$port     = trim($_ENV['DB_PORT']) ?? '3306';
$db       = trim($_ENV['DB_NAME']) ?? '';
$user     = trim($_ENV['DB_USER']) ?? '';
$pass     = trim($_ENV['DB_PASS']) ?? '';
$charset  = 'utf8mb4';

$dsn = "mysql:host={$host},port={$port},dbname={$db},charset={$charset}";

//Security
$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];

try{

  $pdo = new PDO($dsn, $user, $pass, $options);
  echo 'Database connection successful';

} catch (\PDOException $e) {

  //Log the eeror message privately;
  error_log($e->getMessage());
  exit("A database error occured. Please try again.");

}


?>