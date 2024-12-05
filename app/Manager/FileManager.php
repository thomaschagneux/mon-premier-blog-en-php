<?php

namespace App\Manager;

use App\Dto\FileDto;
use App\Services\Sanitizer;
use Exception;

class FileManager
{
    private string $destination;


    /**
     * Retrieve and validate a file from the $_FILES array.
     * @param  array<string> $allowedTypes List of allowed MIME types.     $maxSize      Maximum allowed file size in bytes.
     * @throws Exception
     */
    public function getFile(string $key, array $allowedTypes = ['image/jpeg', 'image/png'], int $maxSize = 2000000): ?FileDto
    {
        $file = $this->sanitizedFiles($key);
        // Sanitize the file name (removes harmful characters)
        if (!is_string($file['name'])) {
            return null;
        }
        $filteredName = Sanitizer::sanitizeString($file['name']);

        // Validate file size
        if (!is_int($file['size']) || $file['size'] > $maxSize) {
            throw new Exception('File size exceeds the maximum allowed size.');
        }

        // Validate that tmp_name is a valid string
        if (!is_string($file['tmp_name']) || !file_exists($file['tmp_name'])) {
            throw new Exception('Le chemin temporaire du fichier est invalide.');
        }

        // Validate MIME type using mime_content_type on the temp file
        $mimeType = mime_content_type($file['tmp_name']);

        if ($mimeType === false) {
            throw new Exception('Unable to determine file MIME type.');
        }

        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception('Invalid file type.');
        }

        // If everything is valid, return the file data
        return new FileDto(
            name     : $filteredName,
            tmp_name : $file['tmp_name'],
            size     : $file['size'],
            type     : $mimeType,
        );
    }


    /**
     * Move an uploaded file to a target directory.
     *
     * @param  string    $tmpPath The temporary path of the file.
     * @return string    The final path where the file is moved.
     * @throws Exception If the file could not be moved.
     */
    public function moveFile(string $tmpPath, string $finalName): string
    {
        // Assurez-vous que $this->destination a bien été défini avec setDestination()
        if (empty($this->destination)) {
            throw new \Exception('Le répertoire de destination n\'est pas défini.');
        }

        // Chemin complet du fichier de destination
        $filePath = $this->destination . $finalName;

        // Déplacement du fichier temporaire vers le chemin final
        if (!move_uploaded_file($tmpPath, $filePath)) {
            throw new \Exception('Échec du déplacement du fichier.');
        }

        return $filePath;
    }


    /**
     * Set the destination directory where files will be moved.
     *
     * @param  string    $destination The path to the directory where files should be moved.
     * @return self
     * @throws Exception If the directory does not exist or is not writable.
     */
    public function setDestination(string $destination): self
    {
        // Vérification que le répertoire existe et est accessible en écriture
        if (!is_dir($destination) || !is_writable($destination)) {
            throw new Exception("Le répertoire $destination n'existe pas ou n'est pas accessible en écriture.");
        }

        // Ajoute un slash à la fin du chemin si nécessaire
        $this->destination = rtrim($destination, '/') . '/';

        return $this;
    }

    /**
     * Vérifie si un fichier a été envoyé et est valide.
     *
     * @param  string $key La clé spécifique dans $_FILES.
     * @return bool   Retourne true si le fichier a été envoyé correctement, false sinon.
     */
    public function isPostFiles(string $key): bool
    {
        $file = $this->sanitizedFiles($key);

        // Vérifie si le fichier est vide ou si une erreur d'upload indique qu'aucun fichier n'a été envoyé
        if (empty($file['name']) || empty($file['tmp_name']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return false;
        }

        return true;
    }

    /**
     * @param  string|null  $key
     * @return array<mixed>
     */
    public function sanitizedFiles(string $key = null): array
    {
        if (null === $key) {
            return Sanitizer::sanitizeArray($_FILES);
        }
        return Sanitizer::sanitizeArray($_FILES[$key]);
    }
}
