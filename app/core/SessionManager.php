<?php

namespace App\core;

class SessionManager
{
    public function startSession(): void
    {
        if ($this->getSessionStatus() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function isSessionStarted(): bool
    {
        return $this->getSessionStatus() === PHP_SESSION_ACTIVE;
    }

    public function destroySession(): void
    {
        if ($this->isSessionStarted()) {
            session_destroy();
        }
    }

    /**
     * Get a value from the session
     *
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return isset($_SESSION[$key]) ? $this->sanitize($_SESSION[$key]) : null;
    }

    /**
     * Set a value in the session
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $this->sanitize($value);
    }

    /**
     * Remove a value from the session
     *
     * @param string $key
     * @return void
     */
    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Check if a session key is set
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Get the session status
     *
     * @return int
     */
    public function getSessionStatus(): int
    {
        return session_status();
    }

    /**
     * Sanitize the data before storing or retrieving
     *
     * @param mixed $data
     * @return mixed
     */
    private function sanitize($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->sanitize($value);
            }
        } else {
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        }
        return $data;
    }
}
