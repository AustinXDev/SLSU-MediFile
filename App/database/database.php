<?php

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

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";

//Security
$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];

try{

  $pdo = new PDO($dsn, $user, $pass, $options);

} catch (\PDOException $e) {

  throw new RuntimeException(
        'Database connection failed: ' . $e->getMessage(),
        0,
        $e
    );

}


?>