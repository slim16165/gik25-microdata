<?php
/**
 * Keyword Matcher - Matches keywords in content
 *
 * @package gik25microdata\InternalLinks\Autolinks
 */

namespace gik25microdata\InternalLinks\Autolinks;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Keyword Matcher class
 */
class KeywordMatcher
{
    /**
     * Match keyword in content
     *
     * @param string $keyword Keyword to match
     * @param string $content Content to search
     * @param array $options Matching options
     * @return array Array of matches with positions
     */
    public function matchKeyword($keyword, $content, $options = [])
    {
        $use_stemming = isset($options['use_stemming']) ? $options['use_stemming'] : false;
        $case_insensitive = isset($options['case_insensitive']) ? $options['case_insensitive'] : false;

        if ($use_stemming) {
            return $this->stemMatch($keyword, $content, $options);
        } else {
            return $this->exactMatch($keyword, $content, $case_insensitive);
        }
    }

    /**
     * Exact match keyword
     *
     * @param string $keyword Keyword to match
     * @param string $content Content to search
     * @param bool $case_insensitive Case insensitive
     * @return array Matches
     */
    public function exactMatch($keyword, $content, $case_insensitive = false)
    {
        $matches = [];
        $pattern = '/' . preg_quote($keyword, '/') . '/';
        
        if ($case_insensitive) {
            $pattern .= 'iu';
        } else {
            $pattern .= 'u';
        }

        if (preg_match_all($pattern, $content, $matches_array, PREG_OFFSET_CAPTURE)) {
            foreach ($matches_array[0] as $match) {
                $matches[] = [
                    'position' => $match[1],
                    'length' => strlen($match[0]),
                    'text' => $match[0],
                ];
            }
        }

        return $matches;
    }

    /**
     * Stem match keyword
     *
     * @param string $keyword Keyword to match
     * @param string $content Content to search
     * @param array $options Options
     * @return array Matches
     */
    public function stemMatch($keyword, $content, $options = [])
    {
        $language = isset($options['language']) ? $options['language'] : 'it';
        $case_insensitive = isset($options['case_insensitive']) ? $options['case_insensitive'] : false;

        $stemmer = new \gik25microdata\InternalLinks\Utils\Stemmer();
        $keyword_stem = $stemmer->stem($keyword, $language);

        // Tokenize content
        $words = $this->tokenize($content);
        $matches = [];

        foreach ($words as $word) {
            $word_stem = $stemmer->stem($word, $language);
            if ($word_stem === $keyword_stem) {
                // Find position in original content
                $position = stripos($content, $word);
                if ($position !== false) {
                    $matches[] = [
                        'position' => $position,
                        'length' => strlen($word),
                        'text' => $word,
                    ];
                }
            }
        }

        return $matches;
    }

    /**
     * Tokenize text into words
     *
     * @param string $text Text to tokenize
     * @return array Words
     */
    private function tokenize($text)
    {
        // Remove HTML tags
        $text = strip_tags($text);
        // Split by word boundaries
        preg_match_all('/\b\w+\b/u', $text, $matches);
        return $matches[0] ?? [];
    }
}

