
    protected DateTime $createdAt;

    protected DateTime $updatedAt;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $database = new Database();
        $dbInstance = $database::getInstance();
        if ($dbInstance === null) {
            throw new Exception('Failed to get a valid Database instance.');
        }

        $this->conn = $dbInstance->getConnection();
        if ($this->conn === null) {
            throw new Exception('Failed to connect to the database.');
        }
    }

    /**
     * GETTERS AND SETTERS
     */

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}