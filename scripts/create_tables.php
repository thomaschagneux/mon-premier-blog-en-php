<?php

require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

try {
    $pdo = new PDO('mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'], $_ENV['DB_USER'], $_ENV['DB_PASS']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Chemin vers le fichier SQL
    $sqlFile = dirname(__DIR__) . '/schema.sql';
    $sql = file_get_contents($sqlFile);

    // Exécution des commandes SQL
    $pdo->exec($sql);

    echo "Tables créées avec succès\n";
} catch (PDOException $e) {
    die("Erreur lors de la création des tables : " . $e->getMessage());
}
