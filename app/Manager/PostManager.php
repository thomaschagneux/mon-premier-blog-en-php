<?php

namespace App\Manager;

/**
 * Class PostManager
 * Manages sanitization and validation of POST parameters, including filtering HTML tags and attributes.
 */
class PostManager
{
    /**
     * @var array<string> The list of allowed HTML tags.
     */
    private const ALLOWED_TAGS = [
        'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'strong', 'em', 'ul', 'ol',
        'li', 'br', 'span', 'details', 's', 'img', 'blockquote', 'sub', 'sup',
        'table', 'thead', 'tbody', 'colgroup', 'col', 'tr', 'th', 'td', 'a', 'iframe',
    ];

    /**
     * @var array<string> The list of allowed HTML attributes.
     */
    private const ALLOWED_ATTRIBUTES = [
        'class', 'style', 'id', 'src', 'href', 'open', 'title', 'target', 'rel', 'alt', 'width', 'height',
    ];

    /**
     * @var array<string> The list of allowed attributes specifically for iframe tags.
     */
    private const ALLOWED_IFRAME_ATTRIBUTES = ['src', 'width', 'height'];

    /**
     * @var array<string> The list of trusted domains for iframe sources.
     */
    private const TRUSTED_DOMAINS = [
        'youtube.com', 'www.youtube.com', 'vimeo.com', 'player.vimeo.com',
    ];

    /**
     * Validate and sanitize a POST parameter.
     *
     * @param string $key The key of the POST parameter.
     *
     * @return string|null The sanitized value or null if the key does not exist or the input is invalid.
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
     * Sanitize a given input manually by filtering allowed HTML tags and attributes.
     *
     * @param string $input The input string to sanitize.
     *
     * @return string The sanitized input string.
     */
    private function sanitizeInput(string $input): string
    {
        $pattern = '#<(/?)([a-zA-Z0-9]+)([^>]*)>#';

        $sanitizedInput = preg_replace_callback($pattern, function ($matches) {
            $closingSlash = $matches[1];
            $tag = strtolower($matches[2]);
            $attributes = $matches[3];

            // Remove disallowed tags
            if (!in_array($tag, self::ALLOWED_TAGS, true)) {
                return '';
            }

            $filteredAttributes = '';
            preg_match_all('/([a-zA-Z]+)=("[^"]*"|\'[^\']*\')/', $attributes, $attrMatches, PREG_SET_ORDER);

            foreach ($attrMatches as $attr) {
                $attrName = strtolower($attr[1]);
                $attrValue = $attr[2];

                // Specific checks for iframe attributes
                if ($tag === 'iframe' && !in_array($attrName, self::ALLOWED_IFRAME_ATTRIBUTES, true)) {
                    continue;
                }

                if ($tag === 'iframe' && $attrName === 'src') {
                    $srcUrl = trim($attrValue, '"\'');
                    if (!$this->isTrustedIframeSource($srcUrl)) {
                        return '';
                    }
                }

                // Allow valid attributes or sanitize disallowed ones
                if (in_array($attrName, self::ALLOWED_ATTRIBUTES, true)) {
                    $filteredAttributes .= " $attrName=$attrValue";
                } else {
                    $filteredAttributes .= " $attrName=" . htmlspecialchars($attrValue, ENT_QUOTES, 'UTF-8');
                }
            }

            return "<$closingSlash$tag$filteredAttributes>";
        }, $input);

        return $sanitizedInput ?? '';
    }

    /**
     * Check if the iframe's src URL belongs to a trusted domain.
     *
     * @param string $url The src URL of the iframe.
     *
     * @return bool True if the domain is trusted, false otherwise.
     */
    private function isTrustedIframeSource(string $url): bool
    {
        $parsedUrl = parse_url($url);

        if (isset($parsedUrl['host']) && in_array($parsedUrl['host'], self::TRUSTED_DOMAINS, true)) {
            return true;
        }

        return false;
    }
}
