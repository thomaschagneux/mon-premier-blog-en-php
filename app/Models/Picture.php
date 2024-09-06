<?php

namespace App\Models;

use DateTime;
use Exception;
use PDO;

class Picture extends AbstractModel
{
    private int $id;
    private string $fileName;
    private string $pathName;
    private string $mimeType;

    public function __construct()
    {
        parent::__construct();
        $this->pathName = '';
    }

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        $picture = new self();

        $picture->setId(isset($data['id']) && is_int($data['id']) ? $data['id'] : 0);
        $picture->setFileName(isset($data['file_name']) && is_string($data['file_name']) ? $data['file_name'] : '');
        $picture->setPathName(isset($data['path_name']) && is_string($data['path_name']) ? $data['path_name'] : '');
        $picture->setMimeType(isset($data['mime_type']) && is_string($data['mime_type']) ? $data['mime_type'] : '');

        return $picture;
    }

    /**
     * @throws Exception
     */
    public function save(): int
    {
        // Vérification de la connexion
        if (!$this->conn instanceof PDO) {
            throw new Exception('Failed to save picture: no database connection.');
        }

        try {
            // Préparer la requête d'insertion
            $query = "INSERT INTO picture (file_name, path_name, mime_type, created_at)
                      VALUES (:file_name, :path_name, :mime_type, :created_at)";
            $stmt = $this->conn->prepare($query);

            // Définir la date de création
            $this->createdAt = new DateTime();

            // Exécution de la requête avec les valeurs du modèle
            $stmt->execute([
                ':file_name' => $this->getFileName(),
                ':path_name' => $this->getPathName(),
                ':mime_type' => $this->getMimeType(),
                ':created_at' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
            ]);

            // Récupérer l'ID généré par la base de données
            $this->id = (int) $this->conn->lastInsertId(); // Convertir en int

            return $this->id; // Retourner l'ID de l'image insérée

        } catch (Exception $e) {
            throw new Exception('Erreur lors de la sauvegarde de l\'image : ' . $e->getMessage());
        }
    }

    /**
     * GETTERS AND SETTERS
     */

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    public function getPathName(): string
    {
        return $this->pathName;
    }

    public function setPathName(string $pathName): void
    {
        $this->pathName = $pathName;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }
}