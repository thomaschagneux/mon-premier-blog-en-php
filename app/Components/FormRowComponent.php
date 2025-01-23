<?php

namespace App\Components;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class FormRowComponent
 * Represents a form field component with various options and attributes for rendering.
 */
class FormRowComponent
{
    /**
     * @var string The type of the form field (e.g., text, checkbox, select, etc.).
     */
    private string $type;

    /**
     * @var string The name attribute of the form field.
     */
    private string $name;

    /**
     * @var string|null The label text for the form field, if any.
     */
    private ?string $label;

    /**
     * @var mixed The value of the form field.
     */
    private mixed $value;

    /**
     * @var array<string, mixed> Additional options for the form field (e.g., for select options).
     */
    private array $options;

    /**
     * @var array<string, mixed> HTML attributes for the form field (e.g., class, id, data-*).
     */
    private array $attributes;

    /**
     * FormRowComponent constructor.
     *
     * @param string               $type       The type of the form field.
     * @param string               $name       The name attribute of the form field.
     * @param mixed|null           $value      The initial value of the form field.
     * @param array<string, mixed> $attributes HTML attributes for the form field.
     * @param array<string, mixed> $options    Additional options for the form field.
     * @param string|null          $label      The label text for the form field, if any.
     */
    public function __construct(
        string $type,
        string $name,
        mixed $value = null,
        array $attributes = [],
        array $options = [],
        string $label = null,
    ) {
        $this->type       = $type;
        $this->name       = $name;
        $this->label      = $label;
        $this->value      = $value;
        $this->options    = $options;
        $this->attributes = $attributes;
    }

    /**
     * Renders the form field using the provided Twig environment.
     *
     * @param Environment $twig The Twig environment instance.
     *
     * @return string The rendered HTML for the form field.
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function render(Environment $twig): string
    {
        return $twig->render('components/form/form_fields.html.twig', [
            'type'       => $this->type,
            'name'       => $this->name,
            'label'      => $this->label,
            'value'      => $this->value,
            'options'    => $this->options,
            'attributes' => $this->attributes,
        ]);
    }
}
