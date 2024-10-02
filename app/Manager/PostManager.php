<?php

namespace App\Manager;

use DOMDocument;

class PostManager
{
    /**
     * Validate and sanitize a POST parameter.
     *
     * @param string $key The key of the POST parameter.
     * @return string|null The sanitized value or null if the key does not exist.
     */
    public function getPostParam(string $key): ?string
    {
        $value = filter_input(INPUT_POST, $key, FILTER_UNSAFE_RAW);

        if (null === $value || false === $value) {
            return null;
        }

        return $this->sanitizeInput($value);
    }

    /**
     * Sanitize a given input manually.
     *
     * @param string $input The input to sanitize.
     * @return string The sanitized input.
     */
    private function sanitizeInput(string $input): string
    {
        $allowedAttributes = [
            'class',
            'style',
            'id',
            'src',
            'href',
            'open',
            'title',
        ];

        $allowedTags = array_fill_keys(
            [
                'p',
                'h1',
                'h2',
                'h3',
                'h4',
                'h5',
                'h6',
                'strong',
                'em',
                'ul',
                'ol',
                'li',
                'br',
                'span',
                'details',
                's',
                'img',
                'blockquote',
                'sub'
            ],
            $allowedAttributes
        );

        $pattern = '#<(/?)([a-zA-Z0-9]+)([^>]*)>#';

        $sanitizedInput = preg_replace_callback($pattern, function ($matches) use ($allowedTags) {
            $closingSlash = $matches[1];
            $tag = strtolower($matches[2]);
            $attributes = $matches[3];

            if (!array_key_exists($tag, $allowedTags)) {
                return ''; // Si la balise n'est pas autorisée, la supprimer
            }

            $filteredAttributes = '';
            preg_match_all('/([a-zA-Z]+)=("[^"]*"|\'[^\']*\')/', $attributes, $attrMatches, PREG_SET_ORDER);

            foreach ($attrMatches as $attr) {
                $attrName = strtolower($attr[1]);
                $attrValue = $attr[2];

                if (in_array($attrName, $allowedTags[$tag])) {
                    // Si l'attribut est autorisé, on le conserve
                    $filteredAttributes .= " $attrName=$attrValue";
                } else {
                    // Sinon, on le nettoie avec htmlspecialchars
                    $filteredAttributes .= " $attrName=" . htmlspecialchars($attrValue, ENT_QUOTES, 'UTF-8');
                }
            }

            return "<$closingSlash$tag$filteredAttributes>";
        }, $input);

        return $sanitizedInput;
    }
}