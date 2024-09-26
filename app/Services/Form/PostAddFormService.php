<?php

namespace App\Services\Form;

use App\Components\FormComponent;
use Twig\Environment;

class PostAddFormService
{
    private FormComponent $formComponent;

    /**
     * @param string $route
     * @param string $action
     * @param string $enctype
     */
    public function __construct(string $route, string $action = 'POST', string $enctype = 'multipart/form-data')
    {
        $this->formComponent = new FormComponent($route, $action, $enctype);
        $this->initializeFields();
    }

    /**
     * Initialize form fields.
     */
    private function initializeFields(): void
    {
        // Define the fields for the form
        $this->formComponent->addField('text', 'title', '', ['label' => 'Title']);
        $this->formComponent->addField('textarea', 'content', '', ['label' => 'Content']);
        $this->formComponent->addField('file', 'image', '', ['label' => 'Image']);
        // Add more fields as necessary
    }

    /**
     * Render the form with the Twig environment.
     *
     * @param Environment $twig
     * @return string
     */
    public function render(Environment $twig): string
    {
        return $this->formComponent->render($twig);
    }
}
