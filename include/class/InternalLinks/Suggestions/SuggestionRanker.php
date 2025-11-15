<?php
/**
 * Suggestion Ranker - Ranks suggestions
 *
 * @package gik25microdata\InternalLinks\Suggestions
 */

namespace gik25microdata\InternalLinks\Suggestions;

use gik25microdata\InternalLinks\Reports\JuiceCalculator;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Suggestion Ranker class
 */
class SuggestionRanker
{
    /**
     * Rank suggestions
     *
     * @param array $suggestions Suggestions
     * @param int $post_id Post ID
     * @return array Ranked suggestions
     */
    public function rankSuggestions($suggestions, $post_id)
    {
        $juice_calculator = new JuiceCalculator();

        foreach ($suggestions as &$suggestion) {
            // Get juice score for suggested post
            $juice = $juice_calculator->calculateJuice($suggestion['post_id'], 0);
            $suggestion['juice'] = $juice['absolute'];

            // Calculate combined score (70% similarity, 30% juice)
            $similarity_weight = 0.7;
            $juice_weight = 0.3;
            $normalized_juice = min($juice['absolute'] / 100, 1.0); // Normalize juice to 0-1

            $suggestion['combined_score'] = 
                ($suggestion['similarity'] * $similarity_weight) + 
                ($normalized_juice * $juice_weight);
        }

        // Sort by combined score
        usort($suggestions, function($a, $b) {
            return $b['combined_score'] <=> $a['combined_score'];
        });

        return $suggestions;
    }

    /**
     * Calculate combined score
     *
     * @param float $similarity Similarity score
     * @param float $juice Juice score
     * @return float Combined score
     */
    public function calculateCombinedScore($similarity, $juice)
    {
        $similarity_weight = 0.7;
        $juice_weight = 0.3;
        $normalized_juice = min($juice / 100, 1.0);

        return ($similarity * $similarity_weight) + ($normalized_juice * $juice_weight);
    }
}

