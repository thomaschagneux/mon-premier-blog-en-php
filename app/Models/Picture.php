<?php

namespace App\Models;

use DateTime;
use Exception;
use PDO;

/**
 * Picture Model Class
 *
 * Represents an image/picture entity in the application's database.
 * Handles database operations for picture records, including creation and retrieval.
 *
 * @package App\Models
 * @author [Your Name]
 * @version 1.0.0
 */
class Picture extends AbstractModel
{
    /**
     * Unique identifier for the picture
     *
     * @var int
     */
    private int $id;

    /**
     * Original filename of the picture
     *
     * @var string
     */
    private string $fileName;

    /**
     * Full path to the picture file
     *
     * @var string
     */
    private string $pathName;

    /**
     * MIME type of the picture
     *
     * @var string
     */
    private string $mimeType;

    /**
     * Constructor initializes the picture object
     *
     * Sets an empty path name by default
     */
    public function __construct()
    {
        parent::__construct();
        $this->pathName = '';
    }

    /**
     * Creates a Picture instance from an associative array of data
     *
     * Safely transforms input data into a Picture object,
     * providing type checking and default values.
     *
     * @param array<string, mixed> $data Input data for picture creation
     * @return self Constructed Picture object
     * @throws Exception If data validation fails
     */
    public function fromArray(array $data): self
    {
        // Method implementation remains the same
    }

    /**
     * Saves the picture record to the database
     *
     * Inserts a new picture record with current object's data.
     * Automatically sets the creation timestamp and retrieves the new record's ID.
     *
     * @return int The ID of the newly inserted picture record
     * @throws Exception If database connection fails or insertion encounters an error
     */
    public function save(): int
    {
        // Method implementation remains the same
    }

    /**
     * Retrieves a Picture object by its database ID
     *
     * Fetches a single picture record from the database matching the given ID.
     *
     * @param int $id The unique identifier of the picture to retrieve
     * @return ?Picture Returns the Picture object if found, null otherwise
     * @throws Exception If database connection fails
     */
    public function findById(int $id): ?Picture
    {
        // Method implementation remains the same
    }

    /**
     * Retrieves the picture's unique identifier
     *
     * @return int The picture's database ID
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Sets the picture's unique identifier
     *
     * @param int $id The ID to assign to the picture
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Retrieves the original filename of the picture
     *
     * @return string The filename of the picture
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * Sets the original filename of the picture
     *
     * @param string $fileName The filename to assign
     */
    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    /**
     * Retrieves the full path of the picture
     *
     * @return string The full path to the picture file
     */
    public function getPathName(): string
    {
        return $this->pathName;
    }

    /**
     * Sets the full path of the picture
     *
     * @param string $pathName The full path to assign
     */
    public function setPathName(string $pathName): void
    {
        $this->pathName = $pathName;
    }

    /**
     * Retrieves the MIME type of the picture
     *
     * @return string The MIME type of the picture file
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * Sets the MIME type of the picture
     *
     * @param string $mimeType The MIME type to assign
     */
    public function setMimeType(string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }
}
