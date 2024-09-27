<?php

namespace App\Components;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class FormRowComponent
{
    private string $type;
    private string $name;
    private mixed $value;
    private array $options;
    private array $attributes;

    public function __construct(
        string $type,
        string $name,
        mixed $value = null,
        array $attributes = [],
        array $options = [],
    )
    {
        $this->type = $type;
        $this->name = $name;
        $this->value = $value;
        $this->options = $options;
        $this->attributes = $attributes;
    }

    public function render(Environment $twig): string
    {
        return $twig->render('components/form/form_fields.html.twig', [
            'type' => $this->type,
            'name' => $this->name,
            'value' => $this->value,
            'options' => $this->options,
            'attributes' => $this->attributes,
        ]);
    }
}
