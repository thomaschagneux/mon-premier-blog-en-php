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
        'id', 'file_name', 'path_name', 'mime_type', 'created_at', 'updated_at'
    ]);
}

function loadPosts($pdo) {
    $csvFile = dirname(__DIR__) . '/data/Post_Data.csv';
    $baseTxtPath = dirname(__DIR__) . '/data/posts/'; // Dossier où se trouvent les fichiers .txt

    insertDataFromCsvWithTxtContent($pdo, $csvFile, $baseTxtPath, 'post', [
        'id', 'title', 'lede', 'featured_image_id', 'content', 'user_id', 'created_at', 'updated_at'
    ]);
}

function loadCommentaries($pdo) {
    $csvFile = dirname(__DIR__) . '/data/Commentary_Data.csv';
    insertDataFromCsv($pdo, $csvFile, 'commentary', [
        'id', 'content', 'validated', 'post_id', 'user_id', 'created_at', 'updated_at'
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

function insertDataFromCsvWithTxtContent($pdo, $csvFile, $baseTxtPath, $tableName, $columns) {
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
            if ($column == 'content') {
                // Construire le chemin vers le fichier .txt basé sur l'ID pour le contenu
                $txtFile = $baseTxtPath . 'post_' . $data['id'] . '.txt';
                if (file_exists($txtFile)) {
                    $params[":$column"] = file_get_contents($txtFile);
                } else {
                    throw new Exception("Le fichier texte pour le contenu du post ID " . $data['id'] . " est introuvable : " . $txtFile);
                }
            } elseif ($column == 'lede') {
                // Construire le chemin vers le fichier .txt basé sur l'ID pour le lede
                $txtFile = $baseTxtPath . 'lede_' . $data['id'] . '.txt';
                if (file_exists($txtFile)) {
                    $params[":$column"] = file_get_contents($txtFile);
                } else {
                    throw new Exception("Le fichier texte pour le lede du post ID " . $data['id'] . " est introuvable : " . $txtFile);
                }
            } elseif ($column == 'updated_at' && empty($data[$column])) {
                $params[":$column"] = null;
            } else {
                $params[":$column"] = $data[$column] ?? null;
            }
        }
        $stmt->execute($params);
    }

    fclose($file);
}