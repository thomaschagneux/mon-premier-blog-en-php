<?php

namespace App\core;

class SessionManager
{

    /**
     * Put a value in the session
     *
     * @param string $key
     * @param mixed $value
     */
    public static function put($key, $value): void
    {
        $_SESSION[$key] = self::sanitize($value);
    }

    /**
     * Get a value from the session
     *
     * @param string $key
     * @return mixed
     */
    public static function get($key)
    {
        return isset($_SESSION[$key]) ? self::sanitize($_SESSION[$key]) : null;
    }

    /**
     * Get all values from the session
     *
     * @return array<string, mixed>
     */
    public static function getAll(): array
    {
        return self::sanitizeArray($_SESSION);
    }

    /**
     * Destroy the session
     */
    public static function destroy(): void
    {
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
    }

     /**
     * Start the session if not already started
     */
    public static function start(): void
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Forget a value from the session
     *
     * @param string $key
     */
    public static function forget($key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Sanitize the data before storing or retrieving
     *
     * @param mixed $data
     * @return mixed
     */
    private static function sanitize($data)
    {
        if (is_array($data)) {
            return self::sanitizeArray($data);
        } elseif (is_string($data)) {
            return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        } else {
           
            return $data;
        }
    }

    /**
     * Sanitize an array
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private static function sanitizeArray(array $data): array
    {
        foreach ($data as $key => $value) {
            $data[$key] = self::sanitize($value);
        }
        return $data;
    }

}
