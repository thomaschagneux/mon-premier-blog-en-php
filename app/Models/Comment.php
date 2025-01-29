<?php

namespace App\Models;

use DateTime;
use Exception;
use PDO;

/**
 * Class Comment
 * Represents a comment associated with a post and a user, providing methods for CRUD operations.
 */
class Comment extends AbstractModel
{
    /**
     * @var int The unique identifier of the comment.
     */
    private int $id;

    /**
     * @var string The content of the comment.
     */
    private string $content;

    /**
     * @var bool Whether the comment has been validated.
     */
    private bool $validated = false;

    /**
     * @var int|null The ID of the associated post.
     */
    private ?int $post_id = null;

    /**
     * @var int|null The ID of the user who created the comment.
     */
    private ?int $user_id = null;

    /**
     * @var User|null The user associated with the comment.
     */
    private ?User $user = null;

    /**
     * Comment constructor.
     * Initializes the parent AbstractModel constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Creates a Comment instance from an array of data.
     *
     * @param array<string, mixed> $data The data to initialize the comment.
     *
     * @throws Exception If any date string is malformed.
     *
     * @return self
     */
    public function fromArray(array $data): self
    {
        $comment = new self();

        $comment->setId(isset($data['id']) && is_int($data['id']) ? $data['id'] : 0);
        $comment->setContent(isset($data['content']) && is_string($data['content']) ? $data['content'] : '');
        $comment->setValidated(isset($data['validated']) ? (bool) $data['validated'] : false);
        $comment->setUserId(isset($data['user_id']) && is_int($data['user_id']) ? $data['user_id'] : null);
        if (isset($data['user_id']) && is_int($data['user_id'])) {
            $user = (new User())->findById($data['user_id']);
            if ($user) {
                $comment->setUser($user);
            }
        }
        $comment->setPostId(isset($data['post_id']) && is_int($data['post_id']) ? $data['post_id'] : null);
        $comment->setCreatedAt(isset($data['created_at']) && is_string($data['created_at']) ? new DateTime($data['created_at']) : new DateTime());
        $comment->setUpdatedAt(isset($data['updated_at']) && is_string($data['updated_at']) ? new DateTime($data['updated_at']) : null);

        return $comment;
    }

    /**
     * Retrieves all comments from the database.
     *
     * @throws Exception If any date string is malformed.
     *
     * @return array<int, Comment> An array of Comment objects.
     */
    public function getAllComments(): array
    {
        if ($this->conn instanceof PDO) {
            $query = 'SELECT * FROM commentary';

            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $Comments = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (is_array($row)) {
                    $Comments[] = self::fromArray($row);
                }
            }
            return $Comments;
        }
        return [];
    }

    /**
     * Retrieves comments by post ID.
     *
     * @param int $id The ID of the post.
     *
     * @throws Exception If any date string is malformed.
     *
     * @return array<int, Comment> An array of Comment objects.
     */
    public function getCommentsByPostId(int $id): array
    {
        if ($this->conn instanceof PDO) {
            $query = 'SELECT * FROM commentary where post_id = ?';

            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);

            $Comments = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (is_array($row)) {
                    $Comments[] = self::fromArray($row);
                }
            }
            return $Comments;
        }
        return [];
    }

    /**
     * Retrieves comments by user ID.
     *
     * @param int $userId The ID of the user.
     *
     * @throws Exception If any date string is malformed.
     *
     * @return array<int, Comment> An array of Comment objects.
     */
    public function findcommentsByUserId(int $userId): array
    {
        if ($this->conn instanceof PDO) {
            $query = 'SELECT * FROM commentary WHERE user_id = :user_id';
            $stmt  = $this->conn->prepare($query);
            $stmt->execute([':user_id' => $userId]);

            $comments = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (is_array($row)) {
                    $comments[] = self::fromArray($row);
                }
            }
            return $comments;
        }
        return [];
    }

    /**
     * Retrieves comments by post ID and user ID.
     *
     * @param int $postId The ID of the post.
     * @param int $userId The ID of the user.
     *
     * @throws Exception If any date string is malformed or an error occurs while creating a `DateTime` object.
     *
     * @return array<int, Comment> An array of Comment objects that match the given post ID and user ID.
     */
    public function findCommentsByPostIdAndUserId(int $postId, int $userId): array
    {
        if ($this->conn instanceof PDO) {
            $query = 'SELECT * FROM commentary WHERE post_id = :post_id AND user_id = :user_id';
            $stmt  = $this->conn->prepare($query);
            $stmt->execute([':post_id' => $postId, ':user_id' => $userId]);
            $comments = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (is_array($row)) {
                    $comments[] = self::fromArray($row);
                }
            }

            return $comments;
        }

        return [];
    }

    /**
     * Finds a comment by its ID.
     *
     * @param int $id The ID of the comment to find.
     *
     * @return self|null The Comment object if found, or null if no comment exists with the given ID.
     */
    public function findById(int $id): ?self
    {
        if ($this->conn instanceof PDO) {
            $query = 'SELECT * FROM commentary WHERE id = ?';
            $stmt  = $this->conn->prepare($query);
            $stmt->execute([$id]);

            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if (is_array($data)) {
                return self::fromArray($data);
            }
        }
        return null;
    }

    /**
     * Saves the comment to the database (insert or update).
     *
     * @throws Exception If the database connection is unavailable or the query fails.
     *
     * @return int The ID of the saved comment.
     */
    public function save(): int
    {
        // Vérification de la connexion à la base de données
        if (!$this->conn instanceof PDO) {
            throw new Exception("La connexion à la base de données n'est pas disponible.");
        }

        $isUpdate = isset($this->id) && $this->id > 0;

        if ($isUpdate) {
            $query = 'UPDATE commentary SET 
                        content = :content,
                        user_id = :user_id,
                        post_id = :post_id,
                        updated_at = :updated_at
                      WHERE id = :id';
        } else {
            $query = 'INSERT INTO commentary (content, user_id, post_id, created_at) 
                      VALUES (:content, :user_id, :post_id, :created_at)';
        }

        try {
            $stmt = $this->conn->prepare($query);
            if ($isUpdate) {
                $this->updatedAt = new DateTime();
            } else {
                $this->createdAt = new DateTime();
            }

            $params = [
                ':content' => $this->getContent(),
                ':post_id' => $this->getPostId(),
                ':user_id' => $this->getUserId(),
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
     * Deletes the comment from the database.
     *
     * @throws Exception If the database connection is unavailable or the comment ID is invalid.
     *
     * @return bool True if the comment was successfully deleted, false otherwise.
     */
    public function remove(): bool
    {
        if (!$this->conn instanceof PDO) {
            throw new Exception("La connexion à la base de données n'est pas disponible.");
        }

        if (!isset($this->id) || $this->id <= 0) {
            throw new Exception('ID du commentaire non valide.');
        }

        try {
            $query = 'DELETE FROM commentary WHERE id = :id';
            $stmt  = $this->conn->prepare($query);
            return $stmt->execute([':id' => $this->id]);
        } catch (Exception) {
            throw new Exception('Erreur lors de la suppression du commentaire');
        }

    }

    /**
     * Marks the comment as validated.
     *
     * @throws Exception If the database connection is unavailable or the comment ID is invalid.
     *
     * @return bool True if the comment was successfully validated, false otherwise.
     */
    public function validate(): bool
    {
        // Vérifiez que la connexion PDO est bien initialisée
        if (!$this->conn instanceof PDO) {
            throw new Exception("La connexion à la base de données n'est pas disponible.");
        }

        // Vérifiez que l'ID du commentaire est défini
        if (!isset($this->id) || $this->id <= 0) {
            throw new Exception('ID du commentaire non valide.');
        }

        try {
            $query = 'UPDATE commentary SET validated = 1 WHERE id = :id';
            $stmt  = $this->conn->prepare($query);
            $stmt->execute([':id' => $this->id]);

            $this->validated = true;

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Gets the ID of the comment.
     *
     * @return int The comment ID.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Sets the ID of the comment.
     *
     * @param int $id The comment ID.
     *
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Gets the content of the comment.
     *
     * @return string The content of the comment.
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Sets the content of the comment.
     *
     * @param string $content The content to set for the comment.
     *
     * @return void
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * Checks if the comment is validated.
     *
     * @return bool True if the comment is validated, false otherwise.
     */
    public function isValidated(): bool
    {
        return $this->validated;
    }

    /**
     * Sets the validation status of the comment.
     *
     * @param bool $validated True to mark the comment as validated, false otherwise.
     *
     * @return void
     */
    public function setValidated(bool $validated): void
    {
        $this->validated = $validated;
    }

    /**
     * Gets the ID of the associated post.
     *
     * @return int|null The ID of the associated post, or null if not set.
     */
    public function getPostId(): ?int
    {
        return $this->post_id;
    }

    /**
     * Sets the ID of the associated post.
     *
     * @param int|null $post_id The ID of the post to associate with the comment.
     *
     * @return void
     */
    public function setPostId(?int $post_id): void
    {
        $this->post_id = $post_id;
    }

    /**
     * Gets the ID of the user who created the comment.
     *
     * @return int|null The ID of the user, or null if not set.
     */
    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    /**
     * Sets the ID of the user who created the comment.
     *
     * @param int|null $user_id The ID of the user to associate with the comment.
     *
     * @return void
     */
    public function setUserId(?int $user_id): void
    {
        $this->user_id = $user_id;
    }

    /**
     * Gets the User object associated with the comment.
     *
     * @return User|null The User object, or null if not set.
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Sets the User object associated with the comment.
     *
     * @param User|null $user The User object to associate with the comment.
     *
     * @return void
     */
    public function setUser(?User $user): void
    {
        $this->user = $user;
    }
}
