<?php

namespace App\Services\Form;

use App\Components\FormComponent;
use Twig\Environment;

abstract class AbstractFormService
{
    protected FormComponent $formComponent;

    public function __construct(string $route, string $action = 'POST', string $enctype = 'multipart/form-data')
    {
        $this->formComponent = new FormComponent($route, $action, $enctype);

        $this->buildForm();
    }

    abstract protected function buildForm(): void;

    public function render(Environment $twig): string
    {
        return $this->formComponent->render($twig);
    }
}