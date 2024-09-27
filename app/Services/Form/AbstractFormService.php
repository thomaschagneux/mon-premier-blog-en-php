<?php

namespace App\Services\Form;

use App\Components\FormRowComponent;
use Twig\Environment;

abstract class AbstractFormService
{
    protected Environment $twig;
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
     * @param \App\Components\FormRowComponent $formRowComponent
     */
    protected function addFormRow(string $name, \App\Components\FormRowComponent $formRowComponent): void
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
     * @return array
     */
    public function getFormRows(): array
    {
        return $this->formRows;
    }


}