<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Ladda .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Hämta databasenamnet från .env
$dbPath = __DIR__ . '/' . $_ENV['DATABASE_NAME'];

// Skapa DSN
$dsn = 'sqlite:' . $dbPath;

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    throw new RuntimeException('Database connection failed: ' . $e->getMessage());
}
