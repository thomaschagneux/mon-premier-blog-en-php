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
     * @throws Exception
     */
    public function emailExists(string $email): bool
    {
        if (!$this->conn instanceof PDO) {
            throw new Exception("Database connection not established.");
        }

        try {
            $query = "SELECT id FROM user WHERE email = :email LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':email' => $email]);

            // Si une ligne est trouvée, l'email existe déjà
            return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la vérification de l\'email : ' . $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function save(): int
    {
        // Vérification de la connexion à la base de données
        if (!$this->conn instanceof PDO) {
            throw new Exception("Database connection not established.");
        }

        // Déterminer si l'on fait une mise à jour ou une insertion
        $isUpdate = isset($this->id) && $this->id > 0;

        // Construire la requête dynamiquement en fonction du type d'opération
        if ($isUpdate) {
            $query = "UPDATE user SET 
                        first_name = :first_name,
                        last_name = :last_name,
                        email = :email,
                        password = :password,
                        role = :role,
                        picture_id = :picture_id,
                        updated_at = :updated_at
                      WHERE id = :id";
        } else {
            $query = "INSERT INTO user (first_name, last_name, email, password, role, picture_id, created_at) 
                      VALUES (:first_name, :last_name, :email, :password, :role, :picture_id, :created_at)";
        }

        try {
            $stmt = $this->conn->prepare($query);

            // Si c'est une mise à jour, on définit la date de mise à jour
            if ($isUpdate) {
                $this->updatedAt = new DateTime();
            } else {
                // Pour une insertion, on définit la date de création
                $this->createdAt = new DateTime();
            }

            // Préparer les paramètres communs
            $params = [
                ':first_name' => $this->getFirstName(),
                ':last_name' => $this->getLastName(),
                ':email' => $this->getEmail(),
                ':password' => $this->getPassword(),
                ':role' => $this->getRole(),
                ':picture_id' => $this->getPictureId(),
            ];

            // Ajouter la date et l'ID si c'est une mise à jour
            if ($isUpdate) {
                $params[':updated_at'] = $this->getUpdatedAt()->format('Y-m-d H:i:s');
                $params[':id'] = $this->getId();
            } else {
                // Ajouter la date de création si c'est une insertion
                $params[':created_at'] = $this->getCreatedAt()->format('Y-m-d H:i:s');
            }

            // Exécuter la requête
            $stmt->execute($params);

            // Si c'est une insertion, récupérer l'ID généré
            if (!$isUpdate) {
                $this->id = (int) $this->conn->lastInsertId();
            }

            return $this->id; // Retourner l'ID de l'utilisateur
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la sauvegarde de l\'utilisateur : ' . $e->getMessage());
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
