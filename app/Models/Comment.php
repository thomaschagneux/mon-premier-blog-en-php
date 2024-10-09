<?php

namespace App\Models;

use DateTime;
use Exception;
use PDO;

class Comment extends AbstractModel
{
    private int $id;

    private string $content;

    private ?int $post_id = null;

    private ?int $user_id = null;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param array<string, mixed> $data
     * @return self
     * @throws \DateMalformedStringException
     */
    public function fromArray(array $data): self
    {
        $comment = new self();

        $comment->setId(isset($data['id']) && is_int($data['id']) ? $data['id'] : 0);
        $comment->setContent(isset($data['content']) && is_string($data['content']) ? $data['content'] : '');
        $comment->setUserId(isset($data['user_id']) && is_int($data['user_id'])? $data['user_id'] : null);
        $comment->setPostId(isset($data['post_id']) && is_int($data['post_id'])? $data['post_id'] : null);
        $comment->setCreatedAt(isset($data['created_at']) && is_string($data['created_at'])? new DateTime($data['created_at']) : new DateTime());
        $comment->setUpdatedAt(isset($data['updated_at']) && is_string($data['updated_at'])? new DateTime($data['updated_at']) : null);

        return $comment;
    }

    /**
     * @return array<int, Comment>
     * @throws \DateMalformedStringException
     */
    public function getAllComments(): array
    {
        if ($this->conn instanceof PDO) {
            $query = "SELECT * FROM commentary";

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
     * @param int $id
     * @return array<int, Comment>
     * @throws \DateMalformedStringException
     */
    public function getCommentsByPostId(int $id): array
    {
        if ($this->conn instanceof PDO) {
            $query = "SELECT * FROM commentary where post_id = ?";

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


    public function findById(int $id): ?self
    {
        if ($this->conn instanceof PDO) {
            $query = "SELECT * FROM commentary WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);

            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if (is_array($data)) {
                return self::fromArray($data);
            }
        }
        return null;
    }

    public function save(): int
    {
        // Vérification de la connexion à la base de données
        if (!$this->conn instanceof PDO) {
            throw new Exception("La connexion à la base de données n'est pas disponible.");
        }

        $isUpdate = isset($this->id) && $this->id > 0;

        if ($isUpdate) {
            $query = "UPDATE commentary SET 
                        content = :content,
                        user_id = :user_id,
                        post_id = :post_id,
                        updated_at = :updated_at
                      WHERE id = :id";
        } else {
            $query = "INSERT INTO commentary (content, user_id, post_id, created_at) 
                      VALUES (:content, :user_id, :post_id, :created_at)";
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
                $params[':id'] = $this->getId();
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
            throw new Exception("ID du post non valide.");
        }

        try {
            $query = "DELETE FROM commentary WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([':id' => $this->id]);
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la suppression de l\'utilisateur : ' . $e->getMessage());
        }

    }

    /**
     *  GETTERS AND SETTERS
     */

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getPostId(): ?int
    {
        return $this->post_id;
    }

    public function setPostId(?int $post_id): void
    {
        $this->post_id = $post_id;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(?int $user_id): void
    {
        $this->user_id = $user_id;
    }
}