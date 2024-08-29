<?php

namespace App\Models;

use App\core\Database;
use DateTime;
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

    private int $id;

    private string $first_name;

    private string $last_name;

    private string $email;

    private string $password;

    private string $role;

    private ?int $picture_id = null;

    private DateTime $created_at;

    private DateTime $updated_at;


    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $database = new Database();
        $dbInstance = $database::getInstance();
        if ($dbInstance === null) {
            throw new \Exception('Failed to get a valid Database instance.');
        }

        $this->conn = $dbInstance->getConnection();
        if ($this->conn === null) {
            throw new \Exception('Failed to connect to the database.');
        }
    }

    /**
     * Retrieves all users from the user table.
     *
     * @return array<int, array<string, mixed>> An associative array of all users
     */
    public function getAllUsers(): array
    {
        if ($this->conn instanceof PDO) {
            $query = "SELECT * FROM user";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return [];
    }

    /**
     * @param string $mail
     * @throws \Exception
     * @return self|null
     */
    public function findByUsermail(string $mail): ?User
    {
        if ($this->conn instanceof PDO) {
            $query = "SELECT * FROM user WHERE email = ?";
            $stmt = $this->conn->prepare($query);

            if (!$stmt) {
                throw new \Exception('Failed to prepare the SQL statement.');
            }

            if (!$stmt->execute([$mail])) {
                throw new \Exception('Failed to execute the SQL statement.');
            }

            $userData = $stmt->fetch(PDO::FETCH_ASSOC);


            if ($userData === false) {
                return null; // Aucun utilisateur trouvé
            }

            if (is_array($userData)) {
                $user = new self();
                $user->setId((int) $userData['id']);
                $user->setFirstName((string) $userData['first_name']);
                $user->setLastName((string) $userData['last_name']);
                $user->setEmail((string) $userData['email']);
                $user->setPassword((string) $userData['password']);
                $user->setRole((string) $userData['role']);
                $user->setPictureId($userData['picture_id'] !== null ? (int) $userData['picture_id'] : null);
                $user->setCreatedAt(new DateTime((string) $userData['created_at']));
                $user->setUpdatedAt(new DateTime((string) $userData['updated_at']));

                return $user;
            } else {
                throw new \Exception("Failed to fetch user data.");

            }




        }

        throw new \Exception("Server Error: Not connected to the database.");
    }

    /**
     *  GETTTERS AND SETTERS
     */


    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getFirstName(): string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): void
    {
        $this->first_name = $first_name;
    }

    public function getLastName(): string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): void
    {
        $this->last_name = $last_name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function getPictureId(): ?int
    {
        return $this->picture_id;
    }

    public function setPictureId(?int $picture_id): void
    {
        $this->picture_id = $picture_id;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->created_at;
    }

    public function setCreatedAt(DateTime $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(DateTime $updated_at): void
    {
        $this->updated_at = $updated_at;
    }

}
