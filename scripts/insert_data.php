<?php

require 'vendor/autoload.php'; // Remonter d'un dossier pour inclure autoload.php

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__)); // Remonter d'un dossier pour accéder à la racine
$dotenv->load();

try {
    $pdo = new PDO('mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'], $_ENV['DB_USER'], $_ENV['DB_PASS']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Chargement des données pour chaque table
    loadPictures($pdo);
    loadUsers($pdo);
    loadPosts($pdo);
    loadCommentaries($pdo);

    echo "Données insérées avec succès dans toutes les tables.\n";
} catch (Exception $e) {
    die("Erreur lors de l'insertion des données : " . $e->getMessage());
}

function loadUsers($pdo) {
    $csvFile = dirname(__DIR__) . '/data/User_Data.csv';
    insertDataFromCsv($pdo, $csvFile, 'user', [
        'id', 'first_name', 'last_name', 'email', 'password', 'role', 'picture_id', 'created_at', 'updated_at'
    ]);
}

function loadPictures($pdo) {
    $csvFile = dirname(__DIR__) . '/data/Picture_Data.csv';
    insertDataFromCsv($pdo, $csvFile, 'picture', [
        'id', 'file_name', 'path_name', 'mimeType', 'created_at', 'updated_at'
    ]);
}

function loadPosts($pdo) {
    $csvFile = dirname(__DIR__) . '/data/Post_Data.csv';
    insertDataFromCsv($pdo, $csvFile, 'post', [
        'id', 'title', 'lede', 'content', 'user_id', 'created_at', 'updated_at'
    ]);
}

function loadCommentaries($pdo) {
    $csvFile = dirname(__DIR__) . '/data/Commentary_Data.csv';
    insertDataFromCsv($pdo, $csvFile, 'commentary', [
        'id', 'content', 'post_id', 'user_id', 'created_at', 'updated_at'
    ]);
}

function insertDataFromCsv($pdo, $csvFile, $tableName, $columns) {
    if (!file_exists($csvFile)) {
        throw new Exception("Le fichier CSV spécifié est introuvable : " . $csvFile);
    }

    $file = fopen($csvFile, 'r');
    $headers = fgetcsv($file);

    $placeholders = ':' . implode(', :', $columns);
    $sql = "INSERT INTO $tableName (" . implode(', ', $columns) . ") VALUES ($placeholders)";
    $stmt = $pdo->prepare($sql);

    while ($row = fgetcsv($file)) {
        $data = array_combine($headers, $row);
        $params = [];
        foreach ($columns as $column) {
            // Si la colonne est 'updated_at' et la valeur est vide, on la remplace par NULL
            if ($column == 'updated_at' && empty($data[$column])) {
                $params[":$column"] = null;
            } else {
                $params[":$column"] = $data[$column] ?? null;
            }
        }
        $stmt->execute($params);
    }

    fclose($file);
}
