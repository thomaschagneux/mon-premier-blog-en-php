<?php

namespace App\core;

use Exception;

class CookieManager
{
    private string $encryptionKey;

    public function __construct(string $encryptionKey = 'votre_clé_de_chiffrement')
    {
        $this->encryptionKey = $encryptionKey;
    }

    public function setCookie(string $name, string $value, int $expire = 0, string $path = "/", string $domain = "", bool $secure = false, bool $httpOnly = true, bool $encrypt = true): void
    {
        if ($encrypt) {
            $value = $this->encrypt($value);
        }
        setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
    }

    public function getCookie(string $name, bool $decrypt = true): ?string
    {
        $value = $_COOKIE[$name] ?? null;
        return $decrypt && $value ? $this->decrypt($value) : $value;
    }

    public function deleteCookie(string $name, string $path = "/", string $domain = ""): void
    {
        setcookie($name, '', time() - 3600, $path, $domain);
    }

    /**
     * @throws Exception
     */
    private function encrypt(string $data): string
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
    private function decrypt(string $data): string
    {
        $decryptData = openssl_decrypt($data, 'AES-128-ECB', $this->encryptionKey);

        if (false === $decryptData) {
            throw new Exception('Decryption failed.');
        }

        return $decryptData;
    }
}
