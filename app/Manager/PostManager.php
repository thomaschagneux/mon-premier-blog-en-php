<?php

namespace App\Manager;

class PostManager
{
    // Constantes pour les balises et attributs autorisés
    private const ALLOWED_TAGS = [
        'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'strong', 'em', 'ul', 'ol',
        'li', 'br', 'span', 'details', 's', 'img', 'blockquote', 'sub', 'sup',
        'table', 'thead', 'tbody', 'colgroup', 'col', 'tr', 'th', 'td', 'a', 'iframe',
    ];

    private const ALLOWED_ATTRIBUTES = [
        'class', 'style', 'id', 'src', 'href', 'open', 'title', 'target', 'rel', 'alt', 'width', 'height',
    ];

    private const ALLOWED_IFRAME_ATTRIBUTES = ['src', 'width', 'height'];

    private const TRUSTED_DOMAINS = [
        'youtube.com', 'www.youtube.com', 'vimeo.com', 'player.vimeo.com',
    ];

    /**
     * Validate and sanitize a POST parameter.
     *
     * @param  string      $key The key of the POST parameter.
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
     * @param  string $input The input to sanitize.
     * @return string The sanitized input.
     */
    private function sanitizeInput(string $input): string
    {
        $pattern = '#<(/?)([a-zA-Z0-9]+)([^>]*)>#';

        $sanitizedInput = preg_replace_callback($pattern, function ($matches) {
            $closingSlash = $matches[1];
            $tag          = strtolower($matches[2]);
            $attributes   = $matches[3];

            if (!in_array($tag, self::ALLOWED_TAGS, true)) {
                return ''; // Supprimer les balises non autorisées
            }

            $filteredAttributes = '';
            preg_match_all('/([a-zA-Z]+)=("[^"]*"|\'[^\']*\')/', $attributes, $attrMatches, PREG_SET_ORDER);

            foreach ($attrMatches as $attr) {
                $attrName  = strtolower($attr[1]);
                $attrValue = $attr[2];

                // Vérifier les iframes spécifiquement
                if ($tag === 'iframe' && !in_array($attrName, self::ALLOWED_IFRAME_ATTRIBUTES, true)) {
                    continue; // Ignorer les attributs non autorisés pour les iframes
                }

                // Si on est dans un iframe, valider l'URL source
                if ($tag === 'iframe' && $attrName === 'src') {
                    $srcUrl = trim($attrValue, '"\''); // Enlever les guillemets
                    if (!$this->isTrustedIframeSource($srcUrl)) {
                        return ''; // Si l'URL n'est pas de confiance, on ne permet pas l'iframe
                    }
                }

                if (in_array($attrName, self::ALLOWED_ATTRIBUTES, true)) {
                    // Si l'attribut est autorisé, on le conserve
                    $filteredAttributes .= " $attrName=$attrValue";
                } else {
                    // Sinon, on le nettoie avec htmlspecialchars
                    $filteredAttributes .= " $attrName=" . htmlspecialchars($attrValue, ENT_QUOTES, 'UTF-8');
                }
            }

            return "<$closingSlash$tag$filteredAttributes>";
        }, $input);

        return $sanitizedInput ?? '';
    }

    /**
     * Vérifie si l'URL src d'un iframe est dans la liste des sources autorisées.
     *
     * @param  string $url L'URL src de l'iframe.
     * @return bool   True si l'URL est de confiance, sinon false.
     */
    private function isTrustedIframeSource(string $url): bool
    {
        $parsedUrl = parse_url($url);

        // Vérifier si le domaine de l'URL est dans la liste des domaines de confiance
        if (isset($parsedUrl['host']) && in_array($parsedUrl['host'], self::TRUSTED_DOMAINS, true)) {
            return true;
        }

        return false;
    }
}
