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
        // Liste des balises autorisées et leurs attributs
        $allowedTags = [
            'p' => [],
            'b' => [],
            'strong' => [],
            'i' => [],
            'em' => [],
            'ul' => [],
            'ol' => [],
            'li' => [],
            'a' => ['href'],
            'img' => ['src', 'alt', 'title', 'width', 'height'],
            'blockquote' => [],
            'code' => [],
            'pre' => []
        ];

        // Supprimer tous les scripts et iframes
        $input = preg_replace('#<(script|iframe|embed|object)[^>]*>.*?</\1>#si', '', $input);

        // Définir les balises autorisées et les attributs autorisés
        $allowed = '';
        foreach ($allowedTags as $tag => $attributes) {
            $allowed .= '<' . $tag;
            if (!empty($attributes)) {
                $allowed .= ' ' . implode(' ', $attributes);
            }
            $allowed .= '>';
        }

        // Nettoyage du contenu en gardant uniquement les balises autorisées
        $input = strip_tags($input, $allowed);

        // Nettoyage des attributs non autorisés
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $input, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $this->cleanAttributes($dom, $allowedTags);

        // Retourner le contenu nettoyé
        return $dom->saveHTML($dom->documentElement);
    }

    /**
     * Supprime les attributs non autorisés d'un DOMDocument.
     *
     * @param DOMDocument $dom Le document DOM à nettoyer.
     * @param array $allowedTags Tableau des balises autorisées et de leurs attributs.
     */
    private function cleanAttributes(DOMDocument $dom, array $allowedTags)
    {
        foreach ($dom->getElementsByTagName('*') as $element) {
            $tagName = $element->tagName;
            if (!isset($allowedTags[$tagName])) {
                // Supprime les éléments non autorisés (si trouvés)
                $element->parentNode->removeChild($element);
                continue;
            }

            // Parcourt les attributs pour vérifier s'ils sont autorisés
            for ($i = $element->attributes->length - 1; $i >= 0; $i--) {
                $attribute = $element->attributes->item($i);
                if (!in_array($attribute->name, $allowedTags[$tagName])) {
                    // Supprime les attributs non autorisés
                    $element->removeAttribute($attribute->name);
                }
            }
        }
    }
}