<?php

namespace App\Models;

use App\core\Database;
use DateTime;
use PDO;
use Exception;

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
     * @throws Exception
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
     * Creates a User instance from an associative array.
     *
     * @param array<string, mixed> $userData
     *
     * @throws Exception
     * @return self
     */
    public static function fromArray(array $userData): self
    {
        $user = new self();

        // Vérification des types avant de caster
        $user->setId(isset($userData['id']) && is_int($userData['id']) ? $userData['id'] : 0);
        $user->setFirstName(isset($userData['first_name']) && is_string($userData['first_name']) ? $userData['first_name'] : '');
        $user->setLastName(isset($userData['last_name']) && is_string($userData['last_name']) ? $userData['last_name'] : '');
        $user->setEmail(isset($userData['email']) && is_string($userData['email']) ? $userData['email'] : '');
        $user->setPassword(isset($userData['password']) && is_string($userData['password']) ? $userData['password'] : '');
        $user->setRole(isset($userData['role']) && is_string($userData['role']) ? $userData['role'] : '');
        $user->setPictureId(isset($userData['picture_id']) && is_int($userData['picture_id']) ? $userData['picture_id'] : null);
        $user->setCreatedAt(isset($userData['created_at']) && is_string($userData['created_at']) ? new \DateTime($userData['created_at']) : new \DateTime());
        $user->setUpdatedAt(isset($userData['updated_at']) && is_string($userData['updated_at']) ? new \DateTime($userData['updated_at']) : new \DateTime());

        return $user;
    }

    /**
     * Retrieves all users from the user table.
     *
     * @return array<int, array<string, mixed>> An associative array of all users
     */
    public function findAllUsers(): array
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
     * Retrieves all User objects from the user table.
     *
     * @throws Exception
     * @return array<int, self>
     */
    public function getAllUsers(): array
    {
        if ($this->conn instanceof PDO) {
            $query = "SELECT * FROM user";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $users = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (is_array($row)) {
                    $users[] = self::fromArray($row);
                }
            }

            return $users;
        }

        return [];
    }

    /**
     * Finds a user by email.
     *
     * @param string $mail
     * @throws Exception
     * @return self|null
     */
    public function findByUsermail(string $mail): ?self
    {
        if ($this->conn instanceof PDO) {
            $query = "SELECT * FROM user WHERE email = ?";
            $stmt = $this->conn->prepare($query);

            if (!$stmt) {
                throw new Exception('Failed to prepare the SQL statement.');
            }

            if (!$stmt->execute([$mail])) {
                throw new Exception('Failed to execute the SQL statement.');
            }

            $userData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($userData === false) {
                return null; // Aucun utilisateur trouvé
            }
            $userArray = [];
            if (is_array($userData)) {
                $userArray = $userData;
            }
            return self::fromArray($userArray);
        }

        throw new Exception("Server Error: Not connected to the database.");
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
