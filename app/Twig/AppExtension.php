<?php

namespace App\Twig;

use App\Services\HelperServices;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private HelperServices $helperServices;

    public function __construct()
    {
        $this->helperServices = new HelperServices();
    }

    /**
     * Returns a list of custom filters.
     *
     * @return array<TwigFilter>
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('attributes', [$this, 'attributesFilter']),
        ];
    }

    /**
     * @return array<TwigFunction>
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('dumper', [$this, 'dumper']),
        ];
    }

    /**
     * Convert an associative array to HTML attributes string.
     *
     * @param array<string, string> $attributes
     * @return string
     */
    public function attributesFilter(array $attributes): string
    {
        $html = '';
        foreach ($attributes as $key => $value) {
            $html .= sprintf('%s=%s ', htmlspecialchars($key), htmlspecialchars($value));
        }
        return trim($html);
    }

    public function dumper(mixed $var): void
    {
        $this->helperServices->dump($var);
    }
}
