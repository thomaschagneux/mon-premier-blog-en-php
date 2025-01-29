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

/**
 * AbstractController
 *
 * This abstract class serves as a base controller for other controllers in the application.
 * It initializes various services and managers, and provides common methods for rendering views,
 * validating data, handling redirections, and managing user sessions.
 */
abstract class AbstractController
{
    /**
     * @var Environment The Twig environment for rendering templates.
     */
    protected Environment $twig;

    /**
     * @var Router The router for handling route generation and URLs.
     */
    protected Router $router;

    /**
     * @var HttpHeadersInterface The HTTP headers manager.
     */
    protected HttpHeadersInterface $headers;

    /**
     * @var HttpResponse The HTTP response manager.
     */
    protected HttpResponse $response;

    /**
     * @var CookieManager The cookie manager for handling cookies.
     */
    protected CookieManager $cookieManager;

    /**
     * @var PostManager The post manager for handling POST requests.
     */
    protected PostManager $postManager;

    /**
     * @var ServerManager The server manager for handling server parameters.
     */
    protected ServerManager $serverManager;

    /**
     * @var FileManager The file manager for handling file operations.
     */
    protected FileManager $fileManager;

    /**
     * AbstractController constructor.
     *
     * Initializes the Twig environment and other necessary services.
     *
     * @param Router $router The router instance.
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
     * Render a Twig template.
     *
     * @param string $template The template name to render.
     * @param array<string, mixed> $data The data to pass to the template.
     * @return string The rendered template.
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function render(string $template, array $data = []): string
    {
        return $this->twig->render($template, $data);
    }

    /**
     * Validates a given value against an array of rules.
     *
     * @param mixed $value The value to be validated.
     * @param Validatable[] $rules An array of Respect\Validation\Validatable rules to apply.
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
     * @param mixed $value The value to be validated.
     * @param Validatable[] $rules An array of Respect\Validation\Validatable rules to apply.
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
     * Get a redirection to the named route with optional parameters.
     *
     * @param string $routeName The name of the route.
     * @param array<int|string, array<mixed>|string> $params The parameters for the route.
     * @return RedirectResponse The redirect response.
     * @throws Exception
     */
    protected function redirectToRoute(string $routeName, array $params = []): RedirectResponse
    {
        $url = $this->router->getRouteUrl($routeName, $params);
        return new RedirectResponse($url, $this->headers, $this->response);
    }

    /**
     * Get a redirection to the specified URL.
     *
     * @param string $url The URL to redirect to.
     * @return RedirectResponse The redirect response.
     * @throws Exception
     */
    protected function redirectToUrl(string $url): RedirectResponse
    {
        $url = Sanitizer::sanitizeString($url);
        return new RedirectResponse($url, $this->headers, $this->response);
    }

    /**
     * Generate a URL for the named route with optional parameters.
     *
     * @param string $routeName The name of the route.
     * @param array<int|string, array<mixed>|string> $params The parameters for the route.
     * @return string The generated URL.
     */
    public function generateUrl(string $routeName, array $params = []): string
    {
        return $this->router->getRouteUrl($routeName, $params);
    }

    /**
     * Get the referer URL.
     *
     * @return string The referer URL.
     */
    public function getReferer(): string
    {
        return $this->serverManager->getServerParams('HTTP_REFERER') ?? $this->router->getRouteUrl('index');
    }

    /**
     * Get a redirection to the referer URL.
     *
     * @return RedirectResponse The redirect response.
     * @throws Exception
     */
    protected function redirectToReferer(): RedirectResponse
    {
        return new RedirectResponse($this->getReferer(), $this->headers, $this->response);
    }

    /**
     * Get the user data from the cookie.
     *
     * @return User|null The user data or null if not found.
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
        $currentUser->setId(isset($user['id']) && is_int($user['id']) ? $user['id'] : 0);
        $currentUser->setEmail(isset($user['email']) && is_string($user['email']) ? $user['email'] : '');
        $currentUser->setFirstName(isset($user['first_name']) && is_string($user['first_name']) ? $user['first_name'] : '');
        $currentUser->setLastName(isset($user['last_name']) && is_string($user['last_name']) ? $user['last_name'] : '');
        $currentUser->setRole(isset($user['role']) && is_string($user['role']) ? $user['role'] : '');

        return $currentUser;
    }

    /**
     * Get the connected user.
     *
     * @return User|null The connected user or null if not found.
     * @throws Exception
     */
    public function getConnectedUser(): ?User
    {
        $user = $this->getUserData();
        return ($user instanceof User) ? $user : null;
    }

    /**
     * Check if the connected user is an admin.
     *
     * @return bool True if the user is an admin, false otherwise.
     * @throws Exception
     */
    public function isAdmin(): bool
    {
        $user = $this->getConnectedUser();
        return $user?->getRole() === 'ROLE_ADMIN';
    }

    /**
     * Check if the request method is POST.
     *
     * @return bool True if the request method is POST, false otherwise.
     */
    protected function isPostRequest(): bool
    {
        return $this->serverManager->getServerParams('REQUEST_METHOD') === 'POST';
    }

    /**
     * Add global variables to the Twig environment.
     *
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
