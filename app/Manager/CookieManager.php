<?php

namespace App\Manager;

use App\Services\Sanitizer;
use Exception;

/**
 * Class CookieManager
 * Manages setting, retrieving, deleting, encrypting, and decrypting cookies.
 */
class CookieManager
{
    /**
     * @var string The encryption key used for encrypting and decrypting cookie values.
     */
    private string $encryptionKey;

    /**
     * CookieManager constructor.
     *
     * @param string $encryptionKey The encryption key for secure cookie management. Defaults to an empty string.
     */
    public function __construct(string $encryptionKey = '')
    {
        $this->encryptionKey = $encryptionKey;
    }

    /**
     * Sets a cookie with optional encryption and sanitization.
     *
     * @param string $name     The name of the cookie.
     * @param string $value    The value of the cookie.
     * @param int    $expire   The expiration time in seconds. Defaults to 0 (session cookie).
     * @param string $path     The path on the server where the cookie is available. Defaults to '/'.
     * @param string $domain   The domain that the cookie is available to. Defaults to an empty string.
     * @param bool   $secure   Whether the cookie should only be transmitted over a secure HTTPS connection. Defaults to false.
     * @param bool   $httpOnly Whether the cookie is accessible only through the HTTP protocol. Defaults to true.
     * @param bool   $encrypt  Whether the cookie value should be encrypted. Defaults to true.
     *
     * @throws Exception If the cookie fails to set or encryption fails.
     *
     * @return void
     */
    public function setCookie(
        string $name,
        string $value,
        int $expire = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httpOnly = true,
        bool $encrypt = true
    ): void {
        if ($encrypt) {
            $encryptedValue = $this->encrypt($value);
            $value          = Sanitizer::sanitizeString($encryptedValue);
        } else {
            $value = Sanitizer::sanitizeString($value);
        }

        if (!setcookie($name, $value, time() + $expire, $path, $domain, $secure, $httpOnly)) {
            throw new Exception('Failed to set the cookie.');
        }
    }

    /**
     * Retrieves a cookie value with optional decryption.
     *
     * @param string $name    The name of the cookie.
     * @param bool   $decrypt Whether the cookie value should be decrypted. Defaults to true.
     *
     * @throws Exception If decryption fails.
     *
     * @return string|null The cookie value, or null if the cookie does not exist.
     */
    public function getCookie(string $name, bool $decrypt = true): ?string
    {
        $value = $_COOKIE[$name] ?? null;

        if ($value === null) {
            return null;
        }

        if ($decrypt) {
            $value = $this->decrypt($value);
        }

        return Sanitizer::sanitizeString($value);
    }

    /**
     * Deletes a cookie by setting its expiration date in the past.
     *
     * @param string $name   The name of the cookie.
     * @param string $path   The path on the server where the cookie is available. Defaults to '/'.
     * @param string $domain The domain that the cookie is available to. Defaults to an empty string.
     *
     * @return void
     */
    public function deleteCookie(string $name, string $path = '/', string $domain = ''): void
    {
        setcookie($name, '', time() - 3600, $path, $domain);
    }

    /**
     * Encrypts a string using AES-128-ECB.
     *
     * @param string $data The data to encrypt.
     *
     * @throws Exception If encryption fails.
     *
     * @return string The encrypted data.
     */
    public function encrypt(string $data): string
    {
        $encryptedData = openssl_encrypt($data, 'AES-128-ECB', $this->encryptionKey);

        if (false === $encryptedData) {
            throw new Exception('Encryption failed.');
        }

        return $encryptedData;
    }

    /**
     * Decrypts a string using AES-128-ECB.
     *
     * @param string $data The data to decrypt.
     *
     * @throws Exception If decryption fails.
     *
     * @return string The decrypted data.
     */
    public function decrypt(string $data): string
    {
        $decryptData = openssl_decrypt($data, 'AES-128-ECB', $this->encryptionKey);

        if (false === $decryptData) {
            throw new Exception('Decryption failed.');
        }

        return $decryptData;
    }
}
