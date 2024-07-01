<?php

namespace App\Models;

use App\core\Database;
use PDO;

/**
 * Class User
 *
 * This class provides methods to interact with the user table in the database.
 */
class User
{
    /**
     * @var PDO|null The PDO connection instance
     */
    private ?PDO $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database::getInstance()->getConnection();
    }

    /**
     * Retrieves all users from the user table.
     *
     * @return array<int, array<string, mixed>> An associative array of all users
     */
    public function getAllUsers()
    {
        if($this->conn instanceof PDO){
            $query = "SELECT * FROM user";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
    
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return [];        
    }
}
