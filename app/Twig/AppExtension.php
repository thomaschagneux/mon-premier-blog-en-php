<?php

namespace App\Twig;

use App\Services\HelperServices;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Class AppExtension
 * Provides custom Twig filters and functions for use in Twig templates.
 */
class AppExtension extends AbstractExtension
{
    /**
     * @var HelperServices The helper services instance for utility functions.
     */
    private readonly HelperServices $helperServices;

    /**
     * AppExtension constructor.
     * Initializes the extension with a HelperServices instance.
     */
    public function __construct()
    {
        $this->helperServices = new HelperServices();
    }

    /**
     * Returns a list of custom Twig filters.
     *
     * @return array<TwigFilter> An array of custom Twig filters.
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('attributes', [$this, 'attributesFilter']),
        ];
    }

    /**
     * Returns a list of custom Twig functions.
     *
     * @return array<TwigFunction> An array of custom Twig functions.
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('dumper', [$this, 'dumper']),
        ];
    }

    /**
     * Converts an associative array to an HTML attributes string.
     *
     * Example: ['class' => 'btn', 'id' => 'submit'] becomes 'class="btn" id="submit"'
     *
     * @param array<string, string> $attributes The associative array of attributes.
     *
     * @return string The HTML attributes as a string.
     */
    public function attributesFilter(array $attributes): string
    {
        $html = '';
        foreach ($attributes as $key => $value) {
            $html .= sprintf('%s=%s ', htmlspecialchars($key), htmlspecialchars($value));
        }
        return trim($html);
    }

    /**
     * Dumps a variable using the helper service in Twig templates.
     *
     * @param mixed $var The variable to dump.
     *
     * @return void
     */
    public function dumper(mixed $var): void
    {
        $this->helperServices->dump($var);
    }
}
