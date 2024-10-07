<?php

namespace App\Models;

use DateTime;
use Exception;
use PDO;

class Post extends AbstractModel
{
    private int $id;

    private string $title;

    private  string $lede;

    private string $content;

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
        $post = new self();

        $post->setId(isset($data['id']) && is_int($data['id'])? $data['id'] : 0);
        $post->setTitle(isset($data['title']) && is_string($data['title'])? $data['title'] : "");
        $post->setLede(isset($data['lede']) && is_string($data['lede'])? $data['lede'] : "");
        $post->setContent(isset($data['content']) && is_string($data['content'])? $data['content'] : "");
        $post->setUserId(isset($data['user_id']) && is_int($data['user_id'])? $data['user_id'] : null);
        $post->setCreatedAt(isset($data['created_at']) && is_string($data['created_at'])? new DateTime($data['created_at']) : new DateTime());
        $post->setUpdatedAt(isset($data['updated_at']) && is_string($data['updated_at'])? new DateTime($data['updated_at']) : null);

        return $post;
    }

    /**
     * @return array<int, Post>
     * @throws \DateMalformedStringException
     */
    public function getAllPosts(): array
    {
        if ($this->conn instanceof PDO) {
            $query = "SELECT * FROM post";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $posts = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (is_array($row)) {
                    $posts[] = self::fromArray($row);
                }
            }
            return $posts;
        }
        return [];
    }

    public function findById(int $id): ?self
    {
        if ($this->conn instanceof PDO) {
            $query = "SELECT * FROM post WHERE id = ?";
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
            $query = "UPDATE post SET 
                        title = :title,
                        lede = :lede,
                        content = :content,
                        user_id = :user_id,
                        updated_at = :updated_at
                      WHERE id = :id";
        } else {
            $query = "INSERT INTO post (title, lede, content, user_id, created_at) 
                      VALUES (:title, :lede, :content, :user_id, :created_at)";
        }

        try {
            $stmt = $this->conn->prepare($query);
            if ($isUpdate) {
                $this->updatedAt = new DateTime();
            } else {
                $this->createdAt = new DateTime();
            }

            $params = [
                ':title' => $this->getTitle(),
                ':lede' => $this->getLede(),
                ':content' => $this->getContent(),
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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getLede(): string
    {
        return $this->lede;
    }

    public function setLede(string $lede): void
    {
        $this->lede = $lede;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
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