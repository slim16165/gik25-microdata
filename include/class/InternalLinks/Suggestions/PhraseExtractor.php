<?php
/**
 * Phrase Extractor - Extracts phrases from content
 *
 * @package gik25microdata\InternalLinks\Suggestions
 */

namespace gik25microdata\InternalLinks\Suggestions;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Phrase Extractor class
 */
class PhraseExtractor
{
    /**
     * Extract phrases from content
     *
     * @param string $content Content
     * @return array Phrases
     */
    public function extractPhrases($content)
    {
        // Remove HTML tags
        $content = wp_strip_all_tags($content);

        // Extract sentences
        $sentences = $this->extractSentences($content);

        // Extract key phrases (2-4 words)
        $phrases = [];
        foreach ($sentences as $sentence) {
            $words = $this->tokenize($sentence);
            for ($i = 0; $i < count($words) - 1; $i++) {
                // 2-word phrases
                if ($i + 1 < count($words)) {
                    $phrases[] = $words[$i] . ' ' . $words[$i + 1];
                }
                // 3-word phrases
                if ($i + 2 < count($words)) {
                    $phrases[] = $words[$i] . ' ' . $words[$i + 1] . ' ' . $words[$i + 2];
                }
            }
        }

        // Remove duplicates and filter
        $phrases = array_unique($phrases);
        $phrases = array_filter($phrases, function($phrase) {
            return strlen($phrase) > 5 && strlen($phrase) < 50;
        });

        return array_slice($phrases, 0, 50); // Limit to 50 phrases
    }

    /**
     * Tokenize text
     *
     * @param string $text Text
     * @return array Tokens
     */
    public function tokenize($text)
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s]/', ' ', $text);
        $words = preg_split('/\s+/', $text);
        return array_filter($words, function($word) {
            return strlen($word) > 2;
        });
    }

    /**
     * Extract sentences
     *
     * @param string $content Content
     * @return array Sentences
     */
    private function extractSentences($content)
    {
        $sentences = preg_split('/[.!?]+/', $content);
        return array_filter(array_map('trim', $sentences), function($sentence) {
            return strlen($sentence) > 10;
        });
    }
}

