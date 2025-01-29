<?php

namespace App\Services\Form;

use App\Components\FormRowComponent;
use Twig\Environment;

/**
 * Class AbstractFormService
 * Provides a base structure for form services to build and render form fields using Twig.
 */
abstract class AbstractFormService
{
    /**
     * @var Environment The Twig environment for rendering form rows.
     */
    protected Environment $twig;

    /**
     * @var array<string, string> An associative array of form rows where the key is the field name and the value is the rendered HTML.
     */
    protected array $formRows = [];

    /**
     * AbstractFormService constructor.
     *
     * @param Environment $twig The Twig environment for rendering templates.
     */
    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Method to be implemented by child classes to define form fields.
     *
     * @return void
     */
    abstract protected function buildForm(): void;

    /**
     * Adds a form row to the service and renders it using Twig.
     *
     * @param string           $name             The name of the form field.
     * @param FormRowComponent $formRowComponent The FormRowComponent instance representing the form field.
     *
     * @return void
     */
    protected function addFormRow(string $name, FormRowComponent $formRowComponent): void
    {
        $this->formRows[$name] = $formRowComponent->render($this->twig);
    }

    /**
     * Retrieves a rendered form row by its name.
     *
     * @param string $name The name of the form row to retrieve.
     *
     * @return string|null The rendered form row HTML, or null if not found.
     */
    public function getFormRow(string $name): ?string
    {
        return $this->formRows[$name] ?? null;
    }

    /**
     * Retrieves all rendered form rows.
     *
     * @return array<string, string> An associative array of all rendered form rows.
     */
    public function getFormRows(): array
    {
        return $this->formRows;
    }
}
