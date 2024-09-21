<?php

namespace App\Models;

use DateTime;
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