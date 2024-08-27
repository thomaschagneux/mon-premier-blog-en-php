<?php

namespace App\core;

class Response
{
private string $content;
private int $statusCode;
private array $headers = [];

public function __construct(string $content = '', int $statusCode = 200, array $headers = [])
{
$this->content = $content;
$this->statusCode = $statusCode;
$this->headers = $headers;
}

public function setContent(string $content): void
{
$this->content = $content;
}

public function setStatusCode(int $statusCode): void
{
$this->statusCode = $statusCode;
}

public function addHeader(string $name, string $value): void
{
$this->headers[$name] = $value;
}

public function send(): void
{
// Définit le code de statut HTTP
http_response_code($this->statusCode);

// Envoie les en-têtes HTTP
foreach ($this->headers as $name => $value) {
header("$name: $value");
}

// Affiche le contenu
echo $this->content;
}
}
