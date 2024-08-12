<?php

namespace App\core;

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
        $value = isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
        return $decrypt && $value ? $this->decrypt($value) : $value;
    }

    public function deleteCookie(string $name, string $path = "/", string $domain = ""): void
    {
        setcookie($name, '', time() - 3600, $path, $domain);
    }

    private function encrypt(string $data): string
    {
        return openssl_encrypt($data, 'AES-128-ECB', $this->encryptionKey);
    }

    private function decrypt(string $data): ?string
    {
        return openssl_decrypt($data, 'AES-128-ECB', $this->encryptionKey);
    }
}
