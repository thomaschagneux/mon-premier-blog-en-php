<?php

namespace App\Components;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class FormComponent
{
    private array $fields = [];
    private string $action;
    private string $method;
    private string $enctype;

    public function __construct(string $action, string $method = 'POST', string $enctype = 'multipart/form-data')
    {
        $this->action = $action;
        $this->method = $method;
        $this->enctype = $enctype;
    }

    public function addField(
        string $type,
        string $name,
        mixed $value = null,
        array $attributes = [],
        array $options = [],
        mixed $selectedOption = null
    ): void
    {
        $field = [
            'type' => $type,
            'name' => $name,
            'value' => $value,
            'attributes' => $attributes,
        ];
        if ('select' == $type) {
            $field['options'] = $this->formatOptions($options, $selectedOption);
        }

        $this->fields[] = $field;
    }

    private function formatOptions(array $options, mixed $selectedOption): array
    {
        $formattedOptions = [];
        foreach ($options as $key => $option) {
            if (is_array($option) && isset($option['value']) && isset($option['label'])) {
                $formattedOptions[] = [
                    'value' => $option['value'],
                    'label' => $option['label'],
                    'selected' => ($option['value'] === $selectedOption)
                ];
            } else {
                $formattedOptions[] = [
                    'value' => $key,
                    'label' => $option,
                    'selected' => ($key === $selectedOption)
                ];
            }
        }
        return $formattedOptions;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getEnctype(): string
    {
        return $this->enctype;
    }

    public function render(Environment $twig): string
    {
        return $twig->render('components/form/form.html.twig', [
            'fields' => $this->getFields(),
            'action' => $this->getAction(),
            'method' => $this->getMethod(),
            'enctype' => $this->getEnctype(),
        ]);
    }
}
