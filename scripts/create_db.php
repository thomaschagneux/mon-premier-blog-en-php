<?php
require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

try {
    $pdo = new PDO('mysql:host=' . $_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = 'CREATE DATABASE IF NOT EXISTS ' . $_ENV['DB_NAME'];
    $pdo->exec($sql);
    echo "Base de données créée avec succès.\n";
} catch (PDOException $e) {
    die("Erreur lors de la création de la base de données : " . $e->getMessage());
}
