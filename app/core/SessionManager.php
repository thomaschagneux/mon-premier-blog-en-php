<?php

namespace App\core;

class SessionManager
{
    public function startSession(): void
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function isSessionStarted(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
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
        return $_SESSION[$key] ?? null;
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
        $_SESSION[$key] = $value;
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
}
