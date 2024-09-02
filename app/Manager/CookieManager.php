<?php

namespace App\Manager;

use App\Services\Sanitizer;
use Exception;

class CookieManager
{
    private string $encryptionKey;

    public function __construct(string $encryptionKey = '')
    {
        $this->encryptionKey = $encryptionKey;
    }

    /**
     * @throws Exception
     */
    public function setCookie(string $name, string $value, int $expire = 0, string $path = "/", string $domain = "", bool $secure = false, bool $httpOnly = true, bool $encrypt = true): void
    {
        if ($encrypt) {
            $encryptedValue = $this->encrypt($value);
            $value = Sanitizer::sanitizeString($encryptedValue);
        } else {
            $value = Sanitizer::sanitizeString($value);
        }

        if (!setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly)) {
            throw new Exception("Failed to set the cookie.");
        }
    }


    /**
     * @throws Exception
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


    public function deleteCookie(string $name, string $path = "/", string $domain = ""): void
    {
        setcookie($name, '', time() - 3600, $path, $domain);
    }

    /**
     * @throws Exception
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
     * @throws Exception
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
