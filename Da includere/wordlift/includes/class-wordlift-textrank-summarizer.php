<?php
/**
 * TextRank Summarizer
 *
 * Implements TextRank algorithm for text summarization.
 *
 * @package Wordlift
 * @since 3.40.0
 */

class Wordlift_TextRank_Summarizer {

	/**
	 * Generate summary using TextRank algorithm.
	 *
	 * @param string $text Full text.
	 * @param int    $max_sentences Maximum sentences in summary.
	 * @return string Summary text.
	 */
	public static function summarize( $text, $max_sentences = 3 ) {
		// Clean and split into sentences
		$sentences = self::split_sentences( $text );
		
		if ( count( $sentences ) <= $max_sentences ) {
			return implode( ' ', $sentences );
		}

		// Calculate sentence scores using TextRank
		$scores = self::calculate_textrank_scores( $sentences );

		// Sort sentences by score
		arsort( $scores );

		// Get top sentences (preserving original order)
		$top_sentences = array_slice( array_keys( $scores ), 0, $max_sentences );
		sort( $top_sentences ); // Preserve original order

		// Build summary
		$summary = array();
		foreach ( $top_sentences as $index ) {
			$summary[] = $sentences[ $index ];
		}

		return implode( ' ', $summary );
	}

	/**
	 * Split text into sentences.
	 *
	 * @param string $text Full text.
	 * @return array Array of sentences.
	 */
	private static function split_sentences( $text ) {
		// Clean text
		$text = wp_strip_all_tags( $text );
		$text = preg_replace( '/\s+/', ' ', $text );
		$text = trim( $text );

		// Split by sentence endings
		$sentences = preg_split( '/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY );
		
		// Filter out very short sentences
		$sentences = array_filter( $sentences, function( $sentence ) {
			return strlen( trim( $sentence ) ) > 20;
		} );

		return array_values( $sentences );
	}

	/**
	 * Calculate TextRank scores for sentences.
	 *
	 * @param array $sentences Array of sentences.
	 * @return array Sentence scores.
	 */
	private static function calculate_textrank_scores( $sentences ) {
		$num_sentences = count( $sentences );
		$scores = array_fill( 0, $num_sentences, 1.0 );
		$similarity_matrix = array();

		// Build similarity matrix
		for ( $i = 0; $i < $num_sentences; $i++ ) {
			$similarity_matrix[ $i ] = array();
			for ( $j = 0; $j < $num_sentences; $j++ ) {
				if ( $i === $j ) {
					$similarity_matrix[ $i ][ $j ] = 0;
				} else {
					$similarity_matrix[ $i ][ $j ] = self::sentence_similarity( $sentences[ $i ], $sentences[ $j ] );
				}
			}
		}

		// Iterate TextRank algorithm (simplified version)
		$damping = 0.85;
		$max_iterations = 20;
		$convergence_threshold = 0.0001;

		for ( $iter = 0; $iter < $max_iterations; $iter++ ) {
			$new_scores = array();
			$max_diff = 0;

			for ( $i = 0; $i < $num_sentences; $i++ ) {
				$sum = 0;
				$out_degree = array_sum( $similarity_matrix[ $i ] );

				if ( $out_degree > 0 ) {
					for ( $j = 0; $j < $num_sentences; $j++ ) {
						if ( $similarity_matrix[ $j ][ $i ] > 0 ) {
							$in_degree = array_sum( $similarity_matrix[ $j ] );
							if ( $in_degree > 0 ) {
								$sum += $similarity_matrix[ $j ][ $i ] / $in_degree * $scores[ $j ];
							}
						}
					}
				}

				$new_scores[ $i ] = ( 1 - $damping ) + $damping * $sum;
				$max_diff = max( $max_diff, abs( $new_scores[ $i ] - $scores[ $i ] ) );
			}

			$scores = $new_scores;

			if ( $max_diff < $convergence_threshold ) {
				break;
			}
		}

		return $scores;
	}

	/**
	 * Calculate similarity between two sentences (word overlap).
	 *
	 * @param string $sentence1 First sentence.
	 * @param string $sentence2 Second sentence.
	 * @return float Similarity score (0-1).
	 */
	private static function sentence_similarity( $sentence1, $sentence2 ) {
		$words1 = self::extract_words( $sentence1 );
		$words2 = self::extract_words( $sentence2 );

		if ( empty( $words1 ) || empty( $words2 ) ) {
			return 0;
		}

		$intersection = count( array_intersect( $words1, $words2 ) );
		$union = count( array_unique( array_merge( $words1, $words2 ) ) );

		return $union > 0 ? $intersection / $union : 0;
	}

	/**
	 * Extract words from sentence.
	 *
	 * @param string $sentence Sentence text.
	 * @return array Array of words.
	 */
	private static function extract_words( $sentence ) {
		$words = str_word_count( strtolower( $sentence ), 1 );
		
		// Remove stop words
		$stop_words = array( 'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'this', 'that', 'these', 'those' );
		$words = array_filter( $words, function( $word ) use ( $stop_words ) {
			return strlen( $word ) > 2 && ! in_array( $word, $stop_words, true );
		} );

		return array_values( $words );
	}
}

