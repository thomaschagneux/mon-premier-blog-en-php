<?php

namespace App\Components;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class FormComponent
{ /**
 * @var array<array<string, mixed>>
 */
    private array $fields = [];

    /**
     * @var string
     */
    private string $action;

    /**
     * @param string $action
     */
    public function __construct(string $action)
    {
        $this->action = $action;
    }

    /**
     * @param string $type
     * @param string $name
     * @param mixed $value
     * @param array<string, mixed> $options
     * @return void
     */
    public function addField(string $type, string $name, mixed $value = null, array $options = []): void
    {
        $this->fields[] = [
            'type' => $type,
            'name' => $name,
            'value' => $value,
            'options' => $options,
        ];
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @param Environment $twig
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @return string
     */
    public function render(Environment $twig): string
    {
        return $twig->render('components/form/form.html.twig', [
            'fields' => $this->getFields(),
            'action' => $this->getAction(),
        ]);
    }
}
