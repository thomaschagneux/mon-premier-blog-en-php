<?php

namespace App\Services\Form;

use App\Components\FormRowComponent;
use Twig\Environment;

abstract class AbstractFormService
{
    protected Environment $twig;

    /**
     * @var array<string, string>
     */
    protected array $formRows = [];

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Method to be implemented by child classes to build form fields.
     */
    abstract protected function buildForm(): void;

    /**
     * Render a form row and store it in the associative array.
     *
     * @param string $name
     * @param FormRowComponent $formRowComponent
     */
    protected function addFormRow(string $name, FormRowComponent $formRowComponent): void
    {
        $this->formRows[$name] = $formRowComponent->render($this->twig);
    }

    /**
     * Get the form rows by name.
     *
     * @param string $name
     * @return string|null
     */
    public function getFormRow(string $name): ?string
    {
        return $this->formRows[$name] ?? null;
    }

    /**
     * Get all form rows.
     *
     * @return array<string, string>
     */
    public function getFormRows(): array
    {
        return $this->formRows;
    }


}