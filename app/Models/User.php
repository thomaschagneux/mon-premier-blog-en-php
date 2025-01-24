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
    /**
     * @var int The unique identifier of the user.
     */
    private int $id;

    /**
     * @var string The first name of the user.
     */
    private string $first_name;

    /**
     * @var string The last name of the user.
     */
    private string $last_name;

    /**
     * @var string The email address of the user.
     */
    private string $email;

    /**
     * @var string The hashed password of the user.
     */
    private string $password;

    /**
     * @var string The role of the user (e.g., ROLE_USER, ROLE_ADMIN).
     */
    private string $role;

    /**
     * @var int|null The ID of the user's profile picture.
     */
    private ?int $picture_id = null;

    /**
     * User constructor.
     * Initializes the parent AbstractModel and default property values.
     */
    public function __construct()
    {
        parent::__construct();
        $this->id         = 0;
        $this->first_name = '';
        $this->last_name  = '';
        $this->email      = '';
        $this->password   = '';
        $this->role       = 'ROLE_USER';
        $this->picture_id = 0;
    }

    /**
     * Creates a User instance from an associative array.
     *
     * @param array<string, mixed> $data The data to populate the user object.
     *
     * @throws Exception If any required data is invalid.
     *
     * @return self The populated User object.
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
     * Retrieves all User objects from the user table.
     *
     * @throws Exception If a database error occurs.
     *
     * @return array<int, self> An array of User objects.
     */
    public function getAllUsers(): array
    {
        if ($this->conn instanceof PDO) {
            $query = 'SELECT * FROM user';
            $stmt  = $this->conn->prepare($query);
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
     * Finds a user by their email address.
     *
     * @param string $mail The email address to search for.
     *
     * @throws Exception If a database error occurs.
     *
     * @return self|null The User object if found, or null if not.
     */
    public function findByUsermail(string $mail): ?self
    {
        if ($this->conn instanceof PDO) {
            $query = 'SELECT * FROM user WHERE email = ?';
            $stmt  = $this->conn->prepare($query);

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

        throw new Exception('Server Error: Not connected to the database.');
    }

    /**
     * Finds a user by their ID.
     *
     * @param int $id The ID of the user to find.
     *
     * @throws Exception If a database error occurs.
     *
     * @return self|null The User object if found, or null if no user exists with the given ID.
     */
    public function findById(int $id): ?self
    {
        if ($this->conn instanceof PDO) {
            $query = 'SELECT * FROM user WHERE id = ?';
            $stmt  = $this->conn->prepare($query);
            $stmt->execute([$id]);

            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            if (is_array($userData)) {
                return self::fromArray($userData);
            }
        }
        return null;
    }

    /**
     * Checks if an email address already exists in the user table.
     *
     * @param string $email The email address to check.
     *
     * @throws Exception If a database error occurs.
     *
     * @return bool True if the email exists, false otherwise.
     */
    public function emailExists(string $email): bool
    {
        if (!$this->conn instanceof PDO) {
            throw new Exception("La connexion à la base de données n'est pas disponible.");
        }

        try {
            $query = 'SELECT id FROM user WHERE email = :email LIMIT 1';
            $stmt  = $this->conn->prepare($query);
            $stmt->execute([':email' => $email]);

            // Si une ligne est trouvée, l'email existe déjà
            return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la vérification de l\'email : ' . $e->getMessage());
        }
    }

    /**
     * Saves the user to the database (insert or update).
     *
     * @throws Exception If a database error occurs.
     *
     * @return int The ID of the saved user.
     */
    public function save(): int
    {
        // Vérification de la connexion à la base de données
        if (!$this->conn instanceof PDO) {
            throw new Exception("La connexion à la base de données n'est pas disponible.");
        }

        $isUpdate = isset($this->id) && $this->id > 0;

        if ($isUpdate) {
            $query = 'UPDATE user SET 
                        first_name = :first_name,
                        last_name = :last_name,
                        email = :email,
                        password = :password,
                        role = :role,
                        picture_id = :picture_id,
                        updated_at = :updated_at
                      WHERE id = :id';
        } else {
            $query = 'INSERT INTO user (first_name, last_name, email, password, role, picture_id, created_at) 
                      VALUES (:first_name, :last_name, :email, :password, :role, :picture_id, :created_at)';
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
                ':last_name'  => $this->getLastName(),
                ':email'      => $this->getEmail(),
                ':password'   => $this->getPassword(),
                ':role'       => $this->getRole(),
                ':picture_id' => $this->getPictureId(),
            ];

            if ($isUpdate) {
                $params[':updated_at'] = $this->getUpdatedAt()?->format('Y-m-d H:i:s');
                $params[':id']         = $this->getId();
            } else {
                $params[':created_at'] = $this->getCreatedAt()->format('Y-m-d H:i:s');
            }

            $stmt->execute($params);

            if (!$isUpdate) {
                $this->id = (int) $this->conn->lastInsertId();
            }

            return $this->id;
        } catch (Exception) {
            throw new Exception('Erreur lors de la sauvegarde de l\'utilisateur');
        }
    }

    /**
     * @throws Exception
     * @return bool
     */
    public function remove(): bool
    {
        if (!$this->conn instanceof PDO) {
            throw new Exception("La connexion à la base de données n'est pas disponible.");
        }

        if (!isset($this->id) || $this->id <= 0) {
            throw new Exception("ID de l'utilisateur non valide.");
        }

        try {
            $query = 'DELETE FROM user WHERE id = :id';
            $stmt  = $this->conn->prepare($query);
            return $stmt->execute([':id' => $this->id]);
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la suppression de l\'utilisateur : ' . $e->getMessage());
        }

    }

    /**
     * GETTERS AND SETTERS
     */

    /**
     * Gets the user's ID.
     *
     * @return int The user's ID.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Sets the user's ID.
     *
     * @param int $id The ID to set for the user.
     *
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Gets the user's first name.
     *
     * @return string The user's first name.
     */
    public function getFirstName(): string
    {
        return $this->first_name;
    }

    /**
     * Sets the user's first name.
     *
     * @param string $first_name The first name to set for the user.
     *
     * @return void
     */
    public function setFirstName(string $first_name): void
    {
        $this->first_name = $first_name;
    }

    /**
     * Gets the user's last name.
     *
     * @return string The user's last name.
     */
    public function getLastName(): string
    {
        return $this->last_name;
    }

    /**
     * Sets the user's last name.
     *
     * @param string $last_name The last name to set for the user.
     *
     * @return void
     */
    public function setLastName(string $last_name): void
    {
        $this->last_name = $last_name;
    }

    /**
     * Gets the user's email address.
     *
     * @return string The user's email address.
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Sets the user's email address.
     *
     * @param string $email The email address to set for the user.
     *
     * @return void
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * Gets the user's password.
     *
     * @return string The user's hashed password.
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Sets the user's password.
     *
     * @param string $password The hashed password to set for the user.
     *
     * @return void
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * Gets the user's role.
     *
     * @return string The user's role (e.g., ROLE_USER, ROLE_ADMIN).
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * Sets the user's role.
     *
     * @param string $role The role to set for the user.
     *
     * @return void
     */
    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    /**
     * Gets the user's profile picture ID.
     *
     * @return int|null The ID of the user's profile picture, or null if not set.
     */
    public function getPictureId(): ?int
    {
        return $this->picture_id;
    }

    /**
     * Sets the user's profile picture ID.
     *
     * @param int|null $picture_id The ID of the profile picture to set for the user.
     *
     * @return void
     */
    public function setPictureId(?int $picture_id): void
    {
        $this->picture_id = $picture_id;
    }
}
