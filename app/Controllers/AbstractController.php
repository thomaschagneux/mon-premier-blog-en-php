<?php

namespace App\Controllers;

use App\core\HttpHeaders;
use App\core\HttpHeadersInterface;
use App\core\HttpResponse;
use App\core\PostManager;
use App\core\RedirectResponse;
use App\core\Router;
use App\core\SessionManager;
use App\Twig\UrlExtension;
use Respect\Validation\Validator as v;
use Respect\Validation\Validatable;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;

abstract class AbstractController
{
    protected Environment $twig;

    protected Router $router;

    protected HttpHeadersInterface $headers;

    protected HttpResponse $response;

    protected SessionManager $session;

    protected PostManager $postManager;

    /**
     * AbstractController constructor.
     *
     * Initializes the Twig environment.
     */
    public function __construct(Router $router)
    {
        $this->router = $router;

        $this->headers = new HttpHeaders();

        $this->response = new HttpResponse();

        $loader = new FilesystemLoader(
            [__DIR__ . '/../Views',
            __DIR__ . '/../Views/components',
            __DIR__ . '/../Views/components/base',]
        );

        $this->twig = new Environment($loader, [
            'debug' => true, // Enable debug mode
        ]);

        $this->twig->addExtension(new DebugExtension()); // Add DebugExtension

        $this->twig->addExtension(new UrlExtension($router)); // Add UrlExtension

        $this->session = new SessionManager();
        $this->session->start();
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

    /**
     * Validates a given value against an array of rules.
     * 
     * @param mixed $value The value to be validated.
     * @param Validatable[] $rules An array of Respect\Validation\Validatable rules to apply.
     * 
     * @return bool Returns true if the value passes all the rules, false otherwise.
     */
    protected function validate(mixed $value, array $rules): bool {
        $validator = v::create();
        foreach ($rules as $rule) {
            $validator->addRule($rule);
        }
        return $validator->validate($value);
    }

     /**
     * Gets validation messages for a given value against an array of rules.
     * 
     * @param mixed $value The value to be validated.
     * @param Validatable[] $rules An array of Respect\Validation\Validatable rules to apply.
     * 
     * @return string Returns a validation message indicating if the validation passed or failed.
     */
    protected function getValidationMessages(mixed $value, array $rules): string
    {
        $validator = v::create();
        foreach ($rules as $rule) {
            $validator->addRule($rule);
        }
        if ($validator->validate($value)) {
            return "Validation passed!";
        } else {
            return "Validation failed!";
        }
    }

    /**
     * Get a redirection to the named route with optional parameters
     *
     * @param array<int|string, array<mixed>|string> $params
     * @throws \Exception
     */
    protected function redirectToRoute(string $routeName, array $params = []): RedirectResponse
    {
        $url = $this->router->getRouteUrl($routeName, $params);
        return new RedirectResponse($url, $this->headers, $this->response);
    }

    public function isAdmin(): bool
    {
        $user = $this->session->get('user');
        return is_array($user) && isset($user['role']) && $user['role'] === 'ROLE_ADMIN';
    }

    protected function isPostRequest(): bool
    {
        return $this->getServerParam('REQUEST_METHOD') === 'POST';
    }


    /**
     * @throws \Exception
     */
    protected function getPostParam(string $key): ?string
    {
        if ($this->isPostRequest() && $this->postManager->isValidNonce()) {
            return $this->postManager->getPostParam($key);
        }
        throw new \Exception("Invalid CSRF token or not a POST request");
    }

    
    protected function getServerParam(string $key): ?string
    {
        // @codingStandardsIgnoreLine
        return isset($_SERVER[$key]) ? $this->sanitizeInput($_SERVER[$key]) : null;
    }

    private function sanitizeInput(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }

}
