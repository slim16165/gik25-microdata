<?php
/**
 * Semantic Analyzer - Analyzes content semantically
 *
 * @package gik25microdata\InternalLinks\Suggestions
 */

namespace gik25microdata\InternalLinks\Suggestions;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Semantic Analyzer class
 */
class SemanticAnalyzer
{
    /**
     * Calculate similarity between two texts using improved algorithm
     *
     * @param string $text1 First text
     * @param string $text2 Second text
     * @param string $language Language code
     * @return float Similarity score (0-1)
     */
    public function calculateSimilarity($text1, $text2, $language = 'it')
    {
        // Tokenize and stem words
        $words1 = $this->tokenizeAndStem($text1, $language);
        $words2 = $this->tokenizeAndStem($text2, $language);

        if (empty($words1) || empty($words2)) {
            return 0.0;
        }

        // Calculate Jaccard similarity (intersection over union)
        $intersection = array_intersect($words1, $words2);
        $union = array_unique(array_merge($words1, $words2));

        if (count($union) === 0) {
            return 0.0;
        }

        $jaccard = count($intersection) / count($union);

        // Calculate cosine similarity for better results
        $cosine = $this->cosineSimilarity($words1, $words2);

        // Combine both metrics (weighted average)
        return ($jaccard * 0.4) + ($cosine * 0.6);
    }

    /**
     * Calculate cosine similarity
     *
     * @param array $words1 First word array
     * @param array $words2 Second word array
     * @return float Cosine similarity
     */
    private function cosineSimilarity($words1, $words2)
    {
        // Count word frequencies
        $freq1 = array_count_values($words1);
        $freq2 = array_count_values($words2);

        // Get all unique words
        $all_words = array_unique(array_merge($words1, $words2));

        // Calculate dot product and magnitudes
        $dot_product = 0;
        $magnitude1 = 0;
        $magnitude2 = 0;

        foreach ($all_words as $word) {
            $count1 = isset($freq1[$word]) ? $freq1[$word] : 0;
            $count2 = isset($freq2[$word]) ? $freq2[$word] : 0;

            $dot_product += $count1 * $count2;
            $magnitude1 += $count1 * $count1;
            $magnitude2 += $count2 * $count2;
        }

        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0.0;
        }

        return $dot_product / (sqrt($magnitude1) * sqrt($magnitude2));
    }

    /**
     * Tokenize and stem text
     *
     * @param string $text Text to process
     * @param string $language Language code
     * @return array Stemmed words
     */
    private function tokenizeAndStem($text, $language = 'it')
    {
        // Remove HTML
        $text = wp_strip_all_tags($text);
        
        // Tokenize
        $words = $this->tokenize($text);
        
        // Stem words
        $stemmer = new \gik25microdata\InternalLinks\Utils\Stemmer();
        $stemmed = [];
        
        foreach ($words as $word) {
            $stem = $stemmer->stem($word, $language);
            if (strlen($stem) > 2) { // Filter very short stems
                $stemmed[] = $stem;
            }
        }
        
        return $stemmed;
    }

    /**
     * Analyze content
     *
     * @param string $content Content
     * @return array Analysis results
     */
    public function analyzeContent($content)
    {
        return [
            'word_count' => str_word_count($content),
            'sentences' => $this->extractSentences($content),
        ];
    }

    /**
     * Find candidate posts for a phrase
     *
     * @param string $phrase Phrase
     * @param int $exclude_post_id Post ID to exclude
     * @return array Candidates
     */
    public function findCandidates($phrase, $exclude_post_id = 0)
    {
        global $wpdb;

        $candidates = [];

        // Get all published posts
        $posts = $wpdb->get_results($wpdb->prepare(
            "SELECT ID, post_content, post_title 
             FROM {$wpdb->prefix}posts 
             WHERE post_status = 'publish' 
             AND post_type = 'post'
             AND ID != %d
             LIMIT 100",
            $exclude_post_id
        ), ARRAY_A);

        // Detect language from phrase
        $language_support = new \gik25microdata\InternalLinks\Utils\LanguageSupport();
        $language = $language_support->detectLanguage($phrase);

        foreach ($posts as $post) {
            $content = $post['post_content'] . ' ' . $post['post_title'];
            $similarity = $this->calculateSimilarity($phrase, $content, $language);

            if ($similarity > 0.1) { // Minimum threshold
                $candidates[] = [
                    'post_id' => $post['ID'],
                    'similarity' => $similarity,
                ];
            }
        }

        // Sort by similarity
        usort($candidates, function($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });

        return array_slice($candidates, 0, 20); // Top 20
    }

    /**
     * Tokenize text
     *
     * @param string $text Text
     * @return array Tokens
     */
    private function tokenize($text)
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s]/', ' ', $text);
        $words = preg_split('/\s+/', $text);
        return array_filter($words, function($word) {
            return strlen($word) > 2; // Filter short words
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
        return array_filter(array_map('trim', $sentences));
    }
}

