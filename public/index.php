<?php
// Informations de connexion à la base de données
$host = 'localhost'; // ou l'adresse de votre serveur de base de données
$db   = 'first_blog';
$user = 'first_blog';
$pass = 'first_blog';
$charset = 'utf8mb4';

// DSN (Data Source Name) pour la connexion PDO
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Connexion à la base de données réussie.";
} catch (PDOException $e) {
    echo "Échec de la connexion à la base de données : " . $e->getMessage();
}
?>
