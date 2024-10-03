<?php

namespace App\Manager;

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
            'target',
            'rel',
            'alt',
            'width',
            'height',
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
                'sub',
                'sup',
                'table',
                'thead',
                'tbody',
                'colgroup',
                'col',
                'tr',
                'th',
                'td',
                'a',
                'iframe', // Autorisation des iframes
            ],
            $allowedAttributes
        );

        // Restriction des attributs autorisés pour l'iframe (par exemple, seulement src, width, height)
        $allowedIframeAttributes = ['src', 'width', 'height'];

        $pattern = '#<(/?)([a-zA-Z0-9]+)([^>]*)>#';

        $sanitizedInput = preg_replace_callback($pattern, function ($matches) use ($allowedTags, $allowedIframeAttributes) {
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

                // Vérifier les iframes spécifiquement
                if ($tag === 'iframe' && !in_array($attrName, $allowedIframeAttributes)) {
                    continue; // Ignorer les attributs non autorisés pour les iframes
                }

                // Si on est dans un iframe, valider l'URL source
                if ($tag === 'iframe' && $attrName === 'src') {
                    $srcUrl = trim($attrValue, '"\''); // Enlever les guillemets
                    if (!$this->isTrustedIframeSource($srcUrl)) {
                        return ''; // Si l'URL n'est pas de confiance, on ne permet pas l'iframe
                    }
                }

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

    /**
     * Vérifie si l'URL src d'un iframe est dans la liste des sources autorisées.
     *
     * @param string $url L'URL src de l'iframe.
     * @return bool True si l'URL est de confiance, sinon false.
     */
    private function isTrustedIframeSource(string $url): bool
    {
        // Liste des domaines autorisés pour les iframes
        $trustedDomains = [
            'youtube.com',
            'www.youtube.com',
            'vimeo.com',
            'player.vimeo.com'
        ];

        $parsedUrl = parse_url($url);

        // Vérifier si le domaine de l'URL est dans la liste des domaines de confiance
        if (isset($parsedUrl['host']) && in_array($parsedUrl['host'], $trustedDomains, true)) {
            return true;
        }

        return false;
    }

}