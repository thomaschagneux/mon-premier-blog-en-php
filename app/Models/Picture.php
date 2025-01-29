<?php

namespace App\Models;

use DateTime;
use Exception;
use PDO;

/**
 * Class Picture
 * Represents a picture object, including its metadata such as filename, path, and MIME type.
 */
class Picture extends AbstractModel
{
    /**
     * @var int The unique identifier of the picture.
     */
    private int $id;

    /**
     * @var string The name of the file.
     */
    private string $fileName;

    /**
     * @var string The path to the file on the server.
     */
    private string $pathName;

    /**
     * @var string The MIME type of the file (e.g., image/jpeg, image/png).
     */
    private string $mimeType;

    /**
     * Picture constructor.
     * Initializes the parent AbstractModel constructor and sets default values.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pathName = '';
    }

    /**
     * Populates a Picture object from an array of data.
     *
     * @param array<string, mixed> $data The data to populate the Picture object.
     *
     * @throws Exception If any required data is invalid.
     *
     * @return self The populated Picture object.
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
     * Saves the picture to the database.
     *
     * @throws Exception If the database connection is unavailable or the query fails.
     *
     * @return int The ID of the saved picture.
     */
    public function save(): int
    {
        if (!$this->conn instanceof PDO) {
            throw new Exception('Failed to save picture: no database connection.');
        }

        try {
            $query = 'INSERT INTO picture (file_name, path_name, mime_type, created_at)
                      VALUES (:file_name, :path_name, :mime_type, :created_at)';
            $stmt = $this->conn->prepare($query);

            $this->createdAt = new DateTime();

            $stmt->execute([
                ':file_name'  => $this->getFileName(),
                ':path_name'  => $this->getPathName(),
                ':mime_type'  => $this->getMimeType(),
                ':created_at' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
            ]);

            $this->id = (int) $this->conn->lastInsertId();

            return $this->id;
        } catch (Exception $e) {
            throw new Exception('Failed to save picture: ' . $e->getMessage());
        }
    }

    /**
     * Finds a picture by its ID.
     *
     * @param int $id The ID of the picture to find.
     *
     * @throws Exception If the database connection is unavailable.
     *
     * @return Picture|null The Picture object if found, or null if no picture exists with the given ID.
     */
    public function findById(int $id): ?Picture
    {
        if (!$this->conn instanceof PDO) {
            throw new Exception('Failed to fetch picture by ID: ' . $id);
        }

        try {
            $query = 'SELECT * FROM picture WHERE id = :id';
            $stmt  = $this->conn->prepare($query);
            $stmt->execute([':id' => $id]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (is_array($result)) {
                return $this->fromArray($result);
            }

            return null;
        } catch (Exception $e) {
            error_log('Failed to fetch picture by ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Gets the ID of the picture.
     *
     * @return int The picture ID.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Sets the ID of the picture.
     *
     * @param int $id The ID to set.
     *
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Gets the file name of the picture.
     *
     * @return string The file name.
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * Sets the file name of the picture.
     *
     * @param string $fileName The file name to set.
     *
     * @return void
     */
    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    /**
     * Gets the path name of the picture.
     *
     * @return string The path name.
     */
    public function getPathName(): string
    {
        return $this->pathName;
    }

    /**
     * Sets the path name of the picture.
     *
     * @param string $pathName The path name to set.
     *
     * @return void
     */
    public function setPathName(string $pathName): void
    {
        $this->pathName = $pathName;
    }

    /**
     * Gets the MIME type of the picture.
     *
     * @return string The MIME type.
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * Sets the MIME type of the picture.
     *
     * @param string $mimeType The MIME type to set.
     *
     * @return void
     */
    public function setMimeType(string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }
}
