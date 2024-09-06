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
class User extends AbstractModel
{
    private int $id;
    private string $first_name;
    private string $last_name;
    private string $email;
    private string $password;
    private string $role;
    private ?int $picture_id = null;

    public function __construct()
    {
        parent::__construct();
        $this->id = 0;
        $this->first_name = "";
        $this->last_name = "";
        $this->email = "";
        $this->password = "";
        $this->role = 'ROLE_USER';
        $this->picture_id = 0;
    }

    /**
     * Creates a User instance from an associative array.
     *
     * @param array<string, mixed> $data
     * @throws Exception
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $user = new self();

        $user->setId(isset($data['id']) && is_int($data['id']) ? $data['id'] : 0);
        $user->setFirstName(isset($data['first_name']) && is_string($data['first_name']) ? $data['first_name'] : '');
        $user->setLastName(isset($data['last_name']) && is_string($data['last_name']) ? $data['last_name'] : '');
        $user->setEmail(isset($data['email']) && is_string($data['email']) ? $data['email'] : '');
        $user->setPassword(isset($data['password']) && is_string($data['password']) ? $data['password'] : '');
        $user->setRole(isset($data['role']) && is_string($data['role']) ? $data['role'] : '');
        $user->setPictureId(isset($data['picture_id']) && is_int($data['picture_id']) ? $data['picture_id'] : null);
        $user->setCreatedAt(isset($data['created_at']) && is_string($data['created_at']) ? new \DateTime($data['created_at']) : new \DateTime());
        $user->setUpdatedAt(isset($data['updated_at']) && is_string($data['updated_at']) ? new \DateTime($data['updated_at']) : new \DateTime());

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

            if (is_array($userData)) {
                return self::fromArray($userData);
            }

            return null;
        }

        throw new Exception("Server Error: Not connected to the database.");
    }


    /**
     * @throws Exception
     */
    public function emailExists(string $email): bool
    {
        if (!$this->conn instanceof PDO) {
            throw new Exception("La connexion à la base de données n'est pas disponible.");
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
            throw new Exception("La connexion à la base de données n'est pas disponible.");
        }

        $isUpdate = isset($this->id) && $this->id > 0;

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

            if ($isUpdate) {
                $this->updatedAt = new DateTime();
            } else {
                $this->createdAt = new DateTime();
            }

            $params = [
                ':first_name' => $this->getFirstName(),
                ':last_name' => $this->getLastName(),
                ':email' => $this->getEmail(),
                ':password' => $this->getPassword(),
                ':role' => $this->getRole(),
                ':picture_id' => $this->getPictureId(),
            ];

            if ($isUpdate) {
                $params[':updated_at'] = $this->getUpdatedAt()->format('Y-m-d H:i:s');
                $params[':id'] = $this->getId();
            } else {
                $params[':created_at'] = $this->getCreatedAt()->format('Y-m-d H:i:s');
            }

            $stmt->execute($params);

            if (!$isUpdate) {
                $this->id = (int) $this->conn->lastInsertId();
            }

            return $this->id;
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la sauvegarde de l\'utilisateur : ' . $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function remove()
    {
        if (!$this->conn instanceof PDO) {
            throw new Exception("La connexion à la base de données n'est pas disponible.");
        }

        if (!isset($this->id) || $this->id <= 0) {
            throw new Exception("ID de l'utilisateur non valide.");
        }

        try {
            $query = "DELETE FROM user WHERE id = :id";
            $stmt = $this->conn->prepare($query);
           return $stmt->execute([':id' => $this->id]);
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la suppression de l\'utilisateur : ' . $e->getMessage());
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
}