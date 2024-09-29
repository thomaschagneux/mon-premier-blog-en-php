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
        // Balises autorisées et leurs attributs permis
        $allowedTags = [
            'p'     => [],
            'h1'    => [],
            'h2'    => [],
            'h3'    => [],
            'h4'    => [],
            'h5'    => [],
            'h6'    => [],
            'strong'=> [],
            'em'    => [],
            'ul'    => [],
            'ol'    => [],
            'li'    => [],
            'br'    => [],
            'span'  => ['style'] // Autoriser span avec l'attribut style
        ];

        // Expression régulière pour identifier les balises HTML
        $pattern = '#<(/?)([a-zA-Z0-9]+)([^>]*)>#';

        // Fonction de rappel pour traiter chaque balise trouvée
        $sanitizedInput = preg_replace_callback($pattern, function ($matches) use ($allowedTags) {
            $closingSlash = $matches[1];
            $tag = strtolower($matches[2]);
            $attributes = $matches[3];

            // Vérifier si la balise est autorisée
            if (!array_key_exists($tag, $allowedTags)) {
                return ''; // Remplacer les balises non autorisées par une chaîne vide
            }

            // Si des attributs sont définis pour cette balise, les filtrer
            if (!empty($allowedTags[$tag])) {
                $filteredAttributes = '';
                preg_match_all('/([a-zA-Z]+)=("[^"]*"|\'[^\']*\')/', $attributes, $attrMatches, PREG_SET_ORDER);

                foreach ($attrMatches as $attr) {
                    $attrName = strtolower($attr[1]);
                    $attrValue = $attr[2];

                    // Vérifier si l'attribut est autorisé pour cette balise
                    if (in_array($attrName, $allowedTags[$tag])) {
                        // Si l'attribut est style, filtrer les styles inline
                        if ($attrName === 'style') {
                            $attrValue = $this->sanitizeStyles($attrValue);
                        } else {
                            // Nettoyer la valeur de l'attribut
                            $attrValue = htmlspecialchars($attrValue, ENT_QUOTES, 'UTF-8');
                        }
                        $filteredAttributes .= " $attrName=$attrValue";
                    }
                }

                $attributes = $filteredAttributes;
            } else {
                $attributes = ''; // Aucun attribut autorisé
            }

            // Reconstruire la balise sécurisée sans échapper les chevrons
            return "<$closingSlash$tag$attributes>";
        }, $input);

        // Note : NE PAS appliquer htmlspecialchars ici car nous avons déjà contrôlé les balises

        return $sanitizedInput;
    }

    /**
     * Sanitize inline styles to allow only safe styles.
     *
     * @param string $style The style attribute value.
     * @return string The sanitized style attribute value.
     */
    private function sanitizeStyles(string $style): string
    {
        // Liste des styles autorisés (exemples: color, font-size, background-color, etc.)
        $allowedStyles = ['color', 'font-family', 'font-size', 'background-color', 'text-decoration'];

        $sanitizedStyles = '';

        // Décomposer les styles en paires propriété:valeur
        preg_match_all('/([a-zA-Z\-]+)\s*:\s*([^;]+);?/', $style, $styleMatches, PREG_SET_ORDER);

        foreach ($styleMatches as $styleMatch) {
            $property = trim($styleMatch[1]);
            $value = trim($styleMatch[2]);

            // Si la propriété est autorisée, l'ajouter à la liste des styles filtrés
            if (in_array($property, $allowedStyles)) {
                $sanitizedStyles .= "$property: $value; ";
            }
        }

        // Nettoyer les espaces en trop
        return trim($sanitizedStyles);
    }
}