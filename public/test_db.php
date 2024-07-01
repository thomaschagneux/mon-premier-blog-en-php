<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

use App\core\Database;

$database = new Database();
$conn = $database->getConnection();

if ($conn) {
    echo "Connexion réussie à la base de données.\n";
} else {
    echo "Échec de la connexion à la base de données.\n";
}

