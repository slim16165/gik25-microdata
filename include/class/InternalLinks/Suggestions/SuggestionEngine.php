<?php
/**
 * Suggestion Engine - Generates link suggestions
 *
 * @package gik25microdata\InternalLinks\Suggestions
 */

namespace gik25microdata\InternalLinks\Suggestions;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Suggestion Engine class
 */
class SuggestionEngine
{
    /**
     * Generate suggestions for a post
     *
     * @param int $post_id Post ID
     * @param array $options Options
     * @return array Suggestions
     */
    public function generateSuggestions($post_id, $options = [])
    {
        $limit = isset($options['limit']) ? intval($options['limit']) : 10;

        // Check cache first
        $cached = $this->getCachedSuggestions($post_id);
        if ($cached !== false) {
            return array_slice($cached, 0, $limit);
        }

        // Extract phrases
        $phrase_extractor = new PhraseExtractor();
        $phrases = $phrase_extractor->extractPhrases(get_post_field('post_content', $post_id));

        // Generate suggestions for each phrase
        $suggestions = [];
        $semantic_analyzer = new SemanticAnalyzer();
        $ranker = new SuggestionRanker();

        foreach ($phrases as $phrase) {
            $candidates = $semantic_analyzer->findCandidates($phrase, $post_id);
            foreach ($candidates as $candidate) {
                $suggestions[] = [
                    'post_id' => $candidate['post_id'],
                    'phrase' => $phrase,
                    'similarity' => $candidate['similarity'],
                ];
            }
        }

        // Rank suggestions
        $suggestions = $ranker->rankSuggestions($suggestions, $post_id);

        // Cache results
        $this->cacheSuggestions($post_id, $suggestions);

        return array_slice($suggestions, 0, $limit);
    }

    /**
     * Batch process suggestions
     *
     * @param array $post_ids Post IDs
     * @return array Results
     */
    public function batchProcess($post_ids)
    {
        $results = [];
        foreach ($post_ids as $post_id) {
            $results[$post_id] = $this->generateSuggestions($post_id);
        }
        return $results;
    }

    /**
     * Cache suggestions
     *
     * @param int $post_id Post ID
     * @param array $suggestions Suggestions
     * @return void
     */
    public function cacheSuggestions($post_id, $suggestions)
    {
        global $wpdb;

        // Delete old suggestions
        $wpdb->delete(
            $wpdb->prefix . 'gik25_il_suggestions',
            ['post_id' => $post_id],
            ['%d']
        );

        // Insert new suggestions
        foreach ($suggestions as $suggestion) {
            $wpdb->insert(
                $wpdb->prefix . 'gik25_il_suggestions',
                [
                    'post_id' => $post_id,
                    'suggested_post_id' => $suggestion['post_id'],
                    'phrase' => $suggestion['phrase'],
                    'similarity_score' => $suggestion['similarity'],
                    'combined_score' => isset($suggestion['combined_score']) ? $suggestion['combined_score'] : $suggestion['similarity'],
                    'expires_at' => date('Y-m-d H:i:s', strtotime('+7 days')),
                ],
                ['%d', '%d', '%s', '%f', '%f', '%s']
            );
        }
    }

    /**
     * Get cached suggestions
     *
     * @param int $post_id Post ID
     * @return array|false Suggestions or false if not cached
     */
    private function getCachedSuggestions($post_id)
    {
        global $wpdb;

        $suggestions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}gik25_il_suggestions 
             WHERE post_id = %d AND expires_at > NOW() 
             ORDER BY combined_score DESC",
            $post_id
        ), ARRAY_A);

        return $suggestions ?: false;
    }
}

