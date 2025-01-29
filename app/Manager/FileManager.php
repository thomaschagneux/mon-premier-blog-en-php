<?php

namespace App\Manager;

use App\Dto\FileDto;
use App\Services\Sanitizer;
use Exception;

/**
 * Class FileManager
 * Handles file uploads, validation, sanitization, and file movements.
 */
class FileManager
{
    /**
     * @var string The destination directory where files will be moved.
     */
    private string $destination;

    /**
     * Retrieve and validate a file from the $_FILES array.
     *
     * @param string        $key          The key in the $_FILES array.
     * @param array<string> $allowedTypes List of allowed MIME types. Defaults to ['image/jpeg', 'image/png'].
     * @param int           $maxSize      Maximum allowed file size in bytes. Defaults to 2MB (2000000).
     *
     * @throws Exception If the file is invalid or does not meet the validation criteria.
     *
     * @return FileDto|null A FileDto instance if the file is valid, or null if the file is not provided.
     */
    public function getFile(string $key, array $allowedTypes = ['image/jpeg', 'image/png'], int $maxSize = 2000000): ?FileDto
    {
        $file = $this->sanitizedFiles($key);

        // Sanitize the file name
        if (!is_string($file['name'])) {
            return null;
        }
        $filteredName = Sanitizer::sanitizeString($file['name']);

        // Validate file size
        if (!is_int($file['size']) || $file['size'] > $maxSize) {
            throw new Exception('File size exceeds the maximum allowed size.');
        }

        // Validate that tmp_name is a valid file path
        if (!is_string($file['tmp_name']) || !file_exists($file['tmp_name'])) {
            throw new Exception('Temporary file path is invalid.');
        }

        // Validate MIME type
        $mimeType = mime_content_type($file['tmp_name']);
        if ($mimeType === false) {
            throw new Exception('Unable to determine file MIME type.');
        }
        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception('Invalid file type.');
        }

        // Return valid FileDto
        return new FileDto(
            name: $filteredName,
            tmp_name: $file['tmp_name'],
            size: $file['size'],
            type: $mimeType,
        );
    }

    /**
     * Move an uploaded file to a target directory.
     *
     * @param string $tmpPath   The temporary path of the file.
     * @param string $finalName The final name of the file in the destination directory.
     *
     * @throws Exception If the destination directory is not set or the file cannot be moved.
     *
     * @return string The full path of the moved file.
     */
    public function moveFile(string $tmpPath, string $finalName): string
    {
        if (empty($this->destination)) {
            throw new Exception('Le répertoire de destination n\'est pas défini.');
        }

        $filePath = $this->destination . $finalName;

        if (!move_uploaded_file($tmpPath, $filePath)) {
            throw new Exception('Échec du déplacement du fichier.');
        }

        return $filePath;
    }

    /**
     * Set the destination directory where files will be moved.
     *
     * @param string $destination The directory path.
     *
     * @throws Exception If the directory does not exist or is not writable.
     *
     * @return self
     */
    public function setDestination(string $destination): self
    {
        if (!is_dir($destination) || !is_writable($destination)) {
            throw new Exception("Le répertoire $destination n'existe pas ou n'est pas accessible en écriture.");
        }

        $this->destination = rtrim($destination, '/') . '/';

        return $this;
    }

    /**
     * Check if a file has been uploaded correctly.
     *
     * @param string $key The key in the $_FILES array.
     *
     * @return bool True if a file is uploaded and valid, false otherwise.
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
     * Retrieve and sanitize the $_FILES array or a specific file within it.
     *
     * @param string|null $key The key in the $_FILES array, or null for the entire $_FILES array.
     *
     * @return array<mixed> The sanitized file data.
     */
    public function sanitizedFiles(string $key = null): array
    {
        if (null === $key) {
            return Sanitizer::sanitizeArray($_FILES);
        }
        return Sanitizer::sanitizeArray($_FILES[$key]);
    }
}
