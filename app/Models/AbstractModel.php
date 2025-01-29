<?php

namespace App\Models;

use App\core\Database;
use App\Manager\CookieManager;
use DateTime;
use PDO;
use RuntimeException;

/**
 * Class AbstractModel
 * Provides a base model class with database connection management and common properties like creation and update timestamps.
 */
class AbstractModel
{
    /**
     * @var PDO|null The PDO connection instance.
     */
    protected ?PDO $conn;

    /**
     * @var DateTime The creation timestamp of the model.
     */
    protected DateTime $createdAt;

    /**
     * @var DateTime|null The last update timestamp of the model, or null if not updated.
     */
    protected ?DateTime $updatedAt = null;

    /**
     * @var CookieManager Handles cookie-related operations.
     */
    protected CookieManager $cookieManager;

    /**
     * AbstractModel constructor.
     * Initializes the database connection and the CookieManager.
     *
     * @throws RuntimeException If the database connection cannot be established.
     */
    public function __construct()
    {
        $database   = new Database();
        $dbInstance = $database::getInstance();
        if ($dbInstance === null) {
            throw new RuntimeException('Failed to get a valid Database instance.');
        }

        $this->conn = $dbInstance->getConnection();
        if ($this->conn === null) {
            throw new RuntimeException('Failed to connect to the database.');
        }

        $this->cookieManager = new CookieManager();
    }

    /**
     * Gets the creation timestamp.
     *
     * @return DateTime The creation timestamp.
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * Sets the creation timestamp.
     *
     * @param DateTime $createdAt The creation timestamp to set.
     *
     * @return void
     */
    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Gets the last update timestamp.
     *
     * @return DateTime|null The last update timestamp, or null if not updated.
     */
    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    /**
     * Sets the last update timestamp.
     *
     * @param DateTime|null $updatedAt The last update timestamp to set.
     *
     * @return void
     */
    public function setUpdatedAt(?DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
