<?php

namespace App\Services\Form;

use App\Components\FormComponent;
use Twig\Environment;

class PostAddFormService extends AbstractFormService
{

    protected function buildForm(): void
    {
        $this->formComponent->addField(
            'text',
            'title',
            'plop',
            ['class' => 'form-control'],
        );

        $this->formComponent->addField(
            'textarea',
            'content',
            '',
            ['class' => 'form-control', 'disabled' => true],
        );

        $this->formComponent->addField(
            'file',
            'image',
            '',
        );

        // Exemple de champ select avec une option sélectionnée par défaut et des attributs supplémentaires
        $options = [
            ['value' => '1', 'label' => 'Option 1'],
            ['value' => '2', 'label' => 'Option 2'],
            ['value' => '3', 'label' => 'Option 3'],
        ];
        $this->formComponent->addField(
            'select',
            'category',
            null,
            ['class' => 'form-select', 'data-label' => 'category-select'],
            $options,
            '2',
        );
    }
}
