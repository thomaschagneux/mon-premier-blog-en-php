<?php

namespace App\Controllers;

use App\core\HttpHeaders;
use App\core\HttpHeadersInterface;
use App\core\HttpResponse;
use App\core\RedirectResponse;
use App\core\Router;
use App\Manager\CookieManager;
use App\Manager\FileManager;
use App\Manager\PostManager;
use App\Manager\ServerManager;
use App\Models\User;
use App\Services\Sanitizer;
use App\Twig\AppExtension;
use App\Twig\UrlExtension;
use Exception;
use Respect\Validation\Validatable;
use Respect\Validation\Validator as v;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

abstract class AbstractController
{
    protected Environment $twig;

    protected Router $router;

    protected HttpHeadersInterface $headers;

    protected HttpResponse $response;

    protected CookieManager $cookieManager;

    protected PostManager $postManager;

    protected ServerManager $serverManager;

    protected FileManager $fileManager;

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
            [
                __DIR__ . '/../Views',
                __DIR__ . '/../Views/components',
                __DIR__ . '/../Views/components/base',
                __DIR__ . '/../Views/admin',
                __DIR__ . '/../Views/user',
                __DIR__ . '/../Views/components/tables',

            ]
        );

        $this->twig = new Environment($loader, [
            'debug' => true, // Enable debug mode
        ]);

        $this->twig->addExtension(new DebugExtension()); // Add DebugExtension

        $this->twig->addExtension(new UrlExtension($router)); // Add UrlExtension

        $this->twig->addExtension(new AppExtension());

        $this->cookieManager = new CookieManager();

        $this->postManager = new PostManager();

        $this->serverManager = new ServerManager();

        $this->fileManager = new FileManager();

        $this->addGlobalVariables();

        $this->getConnectedUser();
    }

    /**
     * Render a twig template
     *
     * @param  string               $template
     * @param  array<string, mixed> $data
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     *
     * @return string
     */
    protected function render(string $template, array $data = []): string
    {
        return $this->twig->render($template, $data);
    }

    /**
     * Validates a given value against an array of rules.
     *
     * @param mixed         $value The value to be validated.
     * @param Validatable[] $rules An array of Respect\Validation\Validatable rules to apply.
     *
     * @return bool Returns true if the value passes all the rules, false otherwise.
     */
    protected function validate(mixed $value, array $rules): bool
    {
        $validator = v::create();
        foreach ($rules as $rule) {
            $validator->addRule($rule);
        }
        return $validator->validate($value);
    }

    /**
    * Gets validation messages for a given value against an array of rules.
    *
     * @param mixed         $value The value to be validated.
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
            return 'Validation passed!';
        }
        return 'Validation failed!';

    }

    /**
     * Get a redirection to the named route with optional parameters
     *
     * @param  array<int|string, array<mixed>|string> $params
     * @throws Exception
     */
    protected function redirectToRoute(string $routeName, array $params = []): RedirectResponse
    {
        $url = $this->router->getRouteUrl($routeName, $params);
        return new RedirectResponse($url, $this->headers, $this->response);
    }

    /**
     * @throws Exception
     */
    protected function redirectToUrl(string $url): RedirectResponse
    {
        $url = Sanitizer::sanitizeString($url);
        return new RedirectResponse($url, $this->headers, $this->response);
    }

    /**
     * @param  string                                 $routeName
     * @param  array<int|string, array<mixed>|string> $params
     * @return string
     */
    public function generateUrl(string $routeName, array $params = []): string
    {
        return $this->router->getRouteUrl($routeName, $params);
    }

    public function getReferer(): string
    {
        return  $this->serverManager->getServerParams('HTTP_REFERER') ?? $this->router->getRouteUrl('index');
    }

    /**
     * @throws Exception
     */
    protected function redirectToReferer(): RedirectResponse
    {
        return new RedirectResponse($this->getReferer(), $this->headers, $this->response);
    }

    /**
     * @throws Exception
     */
    public function getUserData(): ?User
    {
        $cookieData = $this->cookieManager->getCookie('user_data');

        if (null === $cookieData) {
            return null;
        }

        $decodedData = html_entity_decode($cookieData);

        $user = json_decode($decodedData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {

            return null;
        }

        if (!is_array($user)) {
            return null;
        }

        $currentUser = new User();
        $currentUser->setId(isset($user['id']) && is_int($user['id']) ? $user['id'] : null);
        $currentUser->setEmail(isset($user['email']) && is_string($user['email']) ? $user['email'] : '');
        $currentUser->setFirstName(isset($user['first_name']) && is_string($user['first_name']) ? $user['first_name'] : '');
        $currentUser->setLastName(isset($user['last_name']) && is_string($user['last_name']) ? $user['last_name'] : '');
        $currentUser->setRole(isset($user['role']) && is_string($user['role']) ? $user['role'] : '');


        return $currentUser;
    }


    /**
     * @throws Exception
     */
    public function getConnectedUser(): User
    {
        $user = $this->getUserData();


        return ($user instanceof User) ? $user : throw new Exception('User is not logged in');
    }

    /**
     * @throws Exception
     */
    public function isAdmin(): bool
    {
        $user = $this->getConnectedUser();

        return $user->getRole() === 'ROLE_ADMIN';
    }

    protected function isPostRequest(): bool
    {
        return $this->serverManager->getServerParams('REQUEST_METHOD') === 'POST';
    }

    /**
     * @throws Exception
     */
    protected function addGlobalVariables(): void
    {
        $userArray = [
            'connected' => $this->getConnectedUser() instanceof User,
            'admin'     => $this->isAdmin(),
        ];
        $this->twig->addGlobal('app_user', $userArray); // Add user to Twig globals
    }
}
