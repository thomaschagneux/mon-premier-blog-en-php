<?php

namespace App\Models;

use DateTime;
use Exception;
use PDO;

/**
 * Class Post
 * Represents a blog post, including metadata, content, and associations with a user and featured image.
 */
class Post extends AbstractModel
{
    /**
     * @var int The unique identifier of the post.
     */
    private int $id;

    /**
     * @var string The title of the post.
     */
    private string $title;

    /**
     * @var string The lede (short introduction) of the post.
     */
    private string $lede;

    /**
     * @var int|null The ID of the featured image associated with the post.
     */
    private ?int $featured_image_id = null;

    /**
     * @var Picture|null The featured image associated with the post.
     */
    private ?Picture $featured_image = null;

    /**
     * @var string The main content of the post.
     */
    private string $content;

    /**
     * @var int|null The ID of the user who created the post.
     */
    private ?int $user_id = null;

    /**
     * @var User|null The user who created the post.
     */
    private ?User $user = null;

    /**
     * @var int The number of views the post has received.
     */
    private int $views = 0;

    /**
     * Post constructor.
     * Initializes the parent AbstractModel constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Populates a Post object from an array of data.
     *
     * @param array<string, int|string|null> $data The data to populate the post object.
     *
     * @throws Exception If the data contains invalid values.
     *
     * @return self The populated Post object.
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
     * Retrieves all posts from the database.
     *
     * @throws Exception If a date string is malformed.
     *
     * @return array<int, Post> An array of Post objects.
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
     * Retrieves posts by the user ID.
     *
     * @param int $userId The ID of the user.
     *
     * @return array<int, Post> An array of Post objects associated with the user.
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

    /**
     * Finds a post by its ID.
     *
     * @param int $id The ID of the post.
     *
     * @return Post|null The Post object if found, or null otherwise.
     */
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

    /**
     * Increments the view count for the post.
     *
     * @return void
     */
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

    /**
     * Saves the post to the database (insert or update).
     *
     * @throws Exception If the database connection is unavailable or the query fails.
     *
     * @return int The ID of the saved post.
     */
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
     * Deletes the post and its associated comments from the database.
     *
     * @throws Exception If the database connection is unavailable or the deletion fails.
     *
     * @return bool True if the post and comments were successfully deleted, false otherwise.
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

    /**
     * Gets the ID of the post.
     *
     * @return int The post ID.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Sets the ID of the post.
     *
     * @param int $id The post ID.
     *
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Gets the title of the post.
     *
     * @return string The title of the post.
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Sets the title of the post.
     *
     * @param string $title The title to set.
     *
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * Gets the lede (short introduction) of the post.
     *
     * @return string The lede of the post.
     */
    public function getLede(): string
    {
        return $this->lede;
    }

    /**
     * Sets the lede of the post.
     *
     * @param string $lede The lede to set.
     *
     * @return void
     */
    public function setLede(string $lede): void
    {
        $this->lede = $lede;
    }

    /**
     * Gets the ID of the featured image.
     *
     * @return int|null The ID of the featured image, or null if not set.
     */
    public function getFeaturedImageId(): ?int
    {
        return $this->featured_image_id;
    }

    /**
     * Sets the ID of the featured image.
     *
     * @param int $featured_image_id The ID of the featured image to set.
     *
     * @return void
     */
    public function setFeaturedImageId(int $featured_image_id): void
    {
        $this->featured_image_id = $featured_image_id;
    }

    /**
     * Gets the featured image object.
     *
     * @return Picture|null The featured image object, or null if not set.
     */
    public function getFeaturedImage(): ?Picture
    {
        return $this->featured_image;
    }

    /**
     * Sets the featured image object.
     *
     * @param Picture|null $featured_image The featured image object to set.
     *
     * @return void
     */
    public function setFeaturedImage(?Picture $featured_image): void
    {
        $this->featured_image = $featured_image;
    }

    /**
     * Gets the content of the post.
     *
     * @return string The content of the post.
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Sets the content of the post.
     *
     * @param string $content The content to set.
     *
     * @return void
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * Gets the ID of the user who created the post.
     *
     * @return int|null The ID of the user, or null if not set.
     */
    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    /**
     * Sets the ID of the user who created the post.
     *
     * @param int|null $user_id The ID of the user to set.
     *
     * @return void
     */
    public function setUserId(?int $user_id): void
    {
        $this->user_id = $user_id;
    }

    /**
     * Gets the user object associated with the post.
     *
     * @return User|null The user object, or null if not set.
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Sets the user object associated with the post.
     *
     * @param User|null $user The user object to set.
     *
     * @return void
     */
    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    /**
     * Gets the number of views for the post.
     *
     * @return int The number of views.
     */
    public function getViews(): int
    {
        return $this->views;
    }

    /**
     * Sets the number of views for the post.
     *
     * @param int $views The number of views to set.
     *
     * @return void
     */
    public function setViews(int $views): void
    {
        $this->views = $views;
    }
}
