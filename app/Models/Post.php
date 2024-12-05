<?php

namespace App\Models;

use DateTime;
use Exception;
use PDO;

class Post extends AbstractModel
{
    private int $id;

    private string $title;

    private string $lede;

    private ?int $featured_image_id = null;

    private ?Picture $featured_image = null;

    private string $content;

    private ?int $user_id = null;

    private ?User $user = null;

    private int $views = 0;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param  array<string, int|string|null> $data
     * @return self
     * @throws \DateMalformedStringException
     * @throws Exception
     */
    public function fromArray(array $data): self
    {
        $post = new self();

        $post->setId(isset($data['id']) && is_int($data['id']) ? $data['id'] : 0);
        $post->setTitle(isset($data['title']) && is_string($data['title']) ? $data['title'] : '');
        $post->setLede(isset($data['lede']) && is_string($data['lede']) ? $data['lede'] : '');
        if (isset($data['featured_image_id']) && is_int($data['featured_image_id'])) {
            $picture = (new Picture())->findById($data['featured_image_id']);
            if ($picture) {
                $post->setFeaturedImage($picture);
                $post->setFeaturedImageId($picture->getId());
            }
        }
        $post->setContent(isset($data['content']) && is_string($data['content']) ? $data['content'] : '');
        $post->setUserId(isset($data['user_id']) && is_int($data['user_id']) ? $data['user_id'] : null);
        if (isset($data['user_id']) && is_int($data['user_id'])) {
            $user = (new User())->findById($data['user_id']);
            if ($user) {
                $post->setUser($user);
            }
        }
        $post->setViews((isset($data['views']) && is_int($data['views']) ? $data['views'] : 0));
        $post->setCreatedAt(isset($data['created_at']) && is_string($data['created_at']) ? new DateTime($data['created_at']) : new DateTime());
        $post->setUpdatedAt(isset($data['updated_at']) && is_string($data['updated_at']) ? new DateTime($data['updated_at']) : null);

        return $post;
    }

    /**
     * @return array<int, Post>
     * @throws \DateMalformedStringException
     */
    public function getAllPosts(): array
    {
        if ($this->conn instanceof PDO) {
            $query = 'SELECT * FROM post';

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

    /**
     * @param  int              $userId
     * @return array<int, Post>
     */
    public function findPostsByUserId(int $userId): array
    {
        if ($this->conn instanceof PDO) {
            $query = 'SELECT * FROM post WHERE user_id = :user_id ORDER BY created_at ASC';
            $stmt  = $this->conn->prepare($query);
            $stmt->execute([':user_id' => $userId]);

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
            $query = 'SELECT * FROM post WHERE id = ?';
            $stmt  = $this->conn->prepare($query);
            $stmt->execute([$id]);

            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if (is_array($data)) {
                $post = self::fromArray($data);

                // Vérifier si un cookie pour cet article existe
                if (!$this->cookieManager->getCookie('viewed_post_' . $id)) {
                    // Incrémenter les vues
                    $post->incrementViews();

                    // Définir un cookie pour éviter de recompter les vues immédiatement
                    $this->cookieManager->setCookie('viewed_post_' . $id, 'true', time() + (3600 * 24));
                }

                return $post;
            }
        }
        return null;
    }

    public function incrementViews(): void
    {
        // Incrémenter les vues dans la base de données
        $query = 'UPDATE post SET views = views + 1 WHERE id = :id';

        if ($this->conn instanceof PDO) {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $this->getId()]);

            // Mettre à jour l'attribut views de l'objet
            $this->views++;
        }
    }

    public function save(): int
    {
        // Vérification de la connexion à la base de données
        if (!$this->conn instanceof PDO) {
            throw new Exception("La connexion à la base de données n'est pas disponible.");
        }

        $isUpdate = isset($this->id) && $this->id > 0;

        if ($isUpdate) {
            $query = 'UPDATE post SET 
                        title = :title,
                        featured_image_id = :featured_image_id,
                        lede = :lede,
                        content = :content,
                        user_id = :user_id,
                        updated_at = :updated_at
                      WHERE id = :id';
        } else {
            $query = 'INSERT INTO post (title, featured_image_id, lede, content, user_id, created_at) 
                      VALUES (:title, :featured_image_id, :lede, :content, :user_id, :created_at)';
        }

        try {
            $stmt = $this->conn->prepare($query);
            if ($isUpdate) {
                $this->updatedAt = new DateTime();
            } else {
                $this->createdAt = new DateTime();
            }

            $params = [
                ':title'             => $this->getTitle(),
                ':featured_image_id' => $this->getFeaturedImageId(),
                ':lede'              => $this->getLede(),
                ':content'           => $this->getContent(),
                ':user_id'           => $this->getUserId(),
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
            throw new Exception('Erreur lors de la sauvegarde du post');
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
            throw new Exception('ID du post non valide.');
        }

        try {
            // Démarrer la transaction pour garantir l'intégrité des suppressions
            $this->conn->beginTransaction();

            // Supprimer les commentaires associés au post
            $queryCommentary = 'DELETE FROM commentary WHERE post_id = :id';
            $stmtCommentary  = $this->conn->prepare($queryCommentary);
            $stmtCommentary->execute([':id' => $this->id]);

            // Supprimer le post
            $queryPost = 'DELETE FROM post WHERE id = :id';
            $stmtPost  = $this->conn->prepare($queryPost);
            $stmtPost->execute([':id' => $this->id]);

            // Valider la transaction si tout s'est bien passé
            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->conn->rollBack();
            throw new Exception('Erreur lors de la suppression du post et de ses commentaires : ' . $e->getMessage());
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

    public function getFeaturedImageId(): ?int
    {
        return $this->featured_image_id;
    }

    public function setFeaturedImageId(int $featured_image_id): void
    {
        $this->featured_image_id = $featured_image_id;
    }

    public function getFeaturedImage(): ?Picture
    {
        return $this->featured_image;
    }

    public function setFeaturedImage(?Picture $featured_image): void
    {
        $this->featured_image = $featured_image;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getViews(): int
    {
        return $this->views;
    }

    public function setViews(int $views): void
    {
        $this->views = $views;
    }

}
