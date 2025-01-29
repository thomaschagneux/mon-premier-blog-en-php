<?php

namespace App\Services\Form;

use App\Components\FormRowComponent;

/**
 * Class ContactFormService
 * Provides a form service for building and managing a contact form, extending the AbstractFormService.
 */
class ContactFormService extends AbstractFormService
{
    /**
     * Builds the form components for the contact form and stores them in the form rows array.
     * The form includes fields for first name, last name, email, and a message.
     *
     * @return void
     */
    public function buildForm(): void
    {
        $this
            ->addFormRow('firstName', new FormRowComponent(
                'text',
                'firstName',
                '',
                ['class' => 'form-control', 'placeholder' => 'Entrez votre nom', 'required' => true],
                [],
                'Votre prénom: '
            ));

        $this
            ->addFormRow('lastName', new FormRowComponent(
                'text',
                'lastName',
                '',
                ['class' => 'form-control', 'placeholder' => 'Entrez votre prénom', 'required' => true],
                [],
                'Votre nom:'
            ));

        $this
            ->addFormRow('email', new FormRowComponent(
                'email',
                'email',
                '',
                ['class' => 'form-control', 'placeholder' => 'Entrez votre mail', 'required' => true],
                [],
                'Votre email: '
            ));
        $this->addFormRow('message', new FormRowComponent(
            'textarea',
            'content',
            '',
            ['class' => 'form-control', 'rows' => 5],
            [],
            'Votre message: ',
        ));
    }


}
