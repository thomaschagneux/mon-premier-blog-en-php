<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    /**
     * Returns a list of custom filters.
     *
     * @return array
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('attributes', [$this, 'attributesFilter']),
        ];
    }

    /**
     * Convert an associative array to HTML attributes string.
     *
     * @param array $attributes
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
}
