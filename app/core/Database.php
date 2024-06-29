<?php

namespace App\core;

require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
use PDO;
use PDOException;

/**
 * Class Database
 *
 * This class provides a singleton instance for database connection using PDO.
 */
class Database
{

    /**
     * @var string $host The hostname for the database connection
     */
    private string $host;

     /**
     * @var string $db_name The name of the database
     */
    private string $db_name;

    /**
     * @var string $username The username for the database connection
     */
    private string $username;

    /**
     * @var string $password The password for the database connection
     */
    private string $password;

    /**
     * @var PDO|null $conn The PDO connection instance
     */
    private ?PDO $conn;

    /**
     * @var Database|null $instance The singleton instance of the Database class
     */
    private static ?Database $instance = null;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $this->host = $_ENV['DB_HOST'];
        $this->db_name = $_ENV['DB_NAME'];
        $this->username = $_ENV['DB_USER'];
        $this->password = $_ENV['DB_PASS'];
    }

    /**
     * Gets the singleton instance of the Database class.
     *
     * @return Database The singleton instance
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Database();
        }

        return self::$instance;
    }

    /**
     * Gets the PDO connection instance and catch error if it fails.
     *
     * @return PDO|null The PDO connection instance
     */
    public function getConnection()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage() . '\r\n';
        }

        return $this->conn;
    }
}