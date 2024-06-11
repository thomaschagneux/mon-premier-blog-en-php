<?php

namespace App\Controllers;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;

abstract class AbstractController
{
    /**
     * @var Environment
     */
    protected $twig;

    /**
     * AbstractController constructor.
     *
     * Initializes the Twig environment.
     */
    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__ . '/../Views');
        $this->twig = new Environment($loader, [
            'debug' => true, // Enable debug mode
        ]);
        $this->twig->addExtension(new DebugExtension()); // Add DebugExtension
    }

    /**
     * Render a Twig template.
     *
     * @param string $template The template file
     * @param array<string, mixed> $data The data to pass to the template
     * @return string The rendered template
     */
    protected function render($template, array $data = [])
    {
        return $this->twig->render($template, $data);
    }
}
