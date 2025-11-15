<?php
/**
 * Local Analysis Service
 *
 * This service provides local analysis using Wikidata and pattern matching
 * instead of the WordLift cloud service.
 *
 * @package  Wordlift\Analysis
 */

namespace Wordlift\Analysis;

use Wordlift\Common\Singleton;

/**
 * Local_Analysis_Service provides local entity extraction and analysis.
 */
class Local_Analysis_Service extends Singleton implements Analysis_Service {

	/**
	 * Get analysis response using local methods (Wikidata, pattern matching).
	 *
	 * @param array  $data The analysis data.
	 * @param String $content_type Content type for the request.
	 * @param int    $post_id Post id.
	 *
	 * @return string|object|\WP_Error A {@link WP_Error} instance or the actual response content.
	 */
	public function get_analysis_response( $data, $content_type, $post_id ) {
		$request_body = json_decode( $data, true );
		
		if ( ! $request_body || ! isset( $request_body['content'] ) ) {
			return new \WP_Error( 'invalid_data', 'Invalid request data' );
		}

		$content = $request_body['content'];
		$language = isset( $request_body['contentLanguage'] ) ? $request_body['contentLanguage'] : 'en';

		// Extract text from HTML if needed
		$text = $this->extract_text_from_html( $content );

		// Perform local analysis
		$entities = $this->extract_entities_local( $text, $language );
		$annotations = $this->create_annotations( $text, $entities );
		$topics = $this->extract_topics( $text );

		// Format response to match WordLift format (as JSON string)
		$response = array(
			'entities' => $entities,
			'annotations' => $annotations,
			'topics' => $topics,
		);

		// Return as JSON string to match expected format
		return wp_json_encode( $response );
	}

	/**
	 * Extract text from HTML content.
	 *
	 * @param string $html HTML content.
	 * @return string Plain text.
	 */
	private function extract_text_from_html( $html ) {
		// Remove script and style tags
		$html = preg_replace( '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi', '', $html );
		$html = preg_replace( '/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/gi', '', $html );
		
		// Convert HTML entities
		$text = html_entity_decode( $html, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		
		// Remove HTML tags
		$text = wp_strip_all_tags( $text );
		
		// Clean up whitespace
		$text = preg_replace( '/\s+/', ' ', $text );
		$text = trim( $text );

		return $text;
	}

	/**
	 * Extract entities using local methods (Wikidata search, pattern matching).
	 *
	 * @param string $text Text to analyze.
	 * @param string $language Language code.
	 * @return array Entities array.
	 */
	private function extract_entities_local( $text, $language ) {
		$entities = array();

		// Extract potential entity names (capitalized words, proper nouns)
		$potential_entities = $this->extract_potential_entities( $text );

		foreach ( $potential_entities as $entity_name ) {
			// Try to find entity in Wikidata
			$wikidata_entity = $this->search_wikidata( $entity_name, $language );
			
			if ( $wikidata_entity ) {
				$entity_id = 'https://www.wikidata.org/entity/' . $wikidata_entity['id'];
				
				$entities[ $entity_id ] = array(
					'id' => $entity_id,
					'label' => $wikidata_entity['label'],
					'description' => isset( $wikidata_entity['description'] ) ? $wikidata_entity['description'] : '',
					'mainType' => $this->determine_entity_type( $wikidata_entity ),
					'types' => isset( $wikidata_entity['types'] ) ? $wikidata_entity['types'] : array(),
					'sameAs' => array( $entity_id ),
					'confidence' => 0.7, // Default confidence for local analysis
				);
			} else {
				// Create local entity if not found in Wikidata
				$local_id = 'local-entity-' . md5( strtolower( $entity_name ) );
				$entities[ $local_id ] = array(
					'id' => $local_id,
					'label' => $entity_name,
					'description' => '',
					'mainType' => 'Thing',
					'types' => array( 'Thing' ),
					'sameAs' => array(),
					'confidence' => 0.5,
				);
			}
		}

		return $entities;
	}

	/**
	 * Extract potential entity names from text.
	 *
	 * @param string $text Text to analyze.
	 * @return array Array of potential entity names.
	 */
	private function extract_potential_entities( $text ) {
		$entities = array();

		// Extract capitalized words/phrases (potential proper nouns)
		preg_match_all( '/\b[A-Z][a-z]+(?:\s+[A-Z][a-z]+)*\b/', $text, $matches );
		
		if ( ! empty( $matches[0] ) ) {
			$entities = array_unique( $matches[0] );
			// Filter out common words and short phrases
			$entities = array_filter( $entities, function( $entity ) {
				$common_words = array( 'The', 'This', 'That', 'These', 'Those', 'A', 'An' );
				return strlen( $entity ) > 2 && ! in_array( $entity, $common_words, true );
			} );
		}

		// Limit to top 20 entities
		return array_slice( array_values( $entities ), 0, 20 );
	}

	/**
	 * Search Wikidata for an entity.
	 *
	 * @param string $search_term Term to search.
	 * @param string $language Language code.
	 * @return array|false Entity data or false if not found.
	 */
	private function search_wikidata( $search_term, $language = 'en' ) {
		// Use Wikidata Search API
		$search_url = add_query_arg(
			array(
				'action' => 'wbsearchentities',
				'search' => $search_term,
				'language' => $language,
				'format' => 'json',
				'limit' => 1,
			),
			'https://www.wikidata.org/w/api.php'
		);

		$response = wp_remote_get( $search_url, array( 'timeout' => 5 ) );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! empty( $data['search'] ) && ! empty( $data['search'][0] ) ) {
			$result = $data['search'][0];
			return array(
				'id' => $result['id'],
				'label' => isset( $result['label'] ) ? $result['label'] : $search_term,
				'description' => isset( $result['description'] ) ? $result['description'] : '',
				'types' => isset( $result['types'] ) ? $result['types'] : array(),
			);
		}

		return false;
	}

	/**
	 * Determine entity type from Wikidata data.
	 *
	 * @param array $wikidata_entity Wikidata entity data.
	 * @return string Schema.org type.
	 */
	private function determine_entity_type( $wikidata_entity ) {
		// Map common Wikidata types to Schema.org types
		$type_mapping = array(
			'Q5' => 'Person', // human
			'Q43229' => 'Organization', // organization
			'Q515' => 'City', // city
			'Q6256' => 'Country', // country
			'Q571' => 'Book', // book
			'Q11424' => 'Movie', // film
			'Q482994' => 'Album', // album
		);

		if ( isset( $wikidata_entity['types'] ) ) {
			foreach ( $wikidata_entity['types'] as $type ) {
				if ( isset( $type_mapping[ $type ] ) ) {
					return $type_mapping[ $type ];
				}
			}
		}

		return 'Thing';
	}

	/**
	 * Create annotations from entities found in text.
	 *
	 * @param string $text Text content.
	 * @param array  $entities Entities array.
	 * @return array Annotations array.
	 */
	private function create_annotations( $text, $entities ) {
		$annotations = array();

		foreach ( $entities as $entity_id => $entity ) {
			$label = $entity['label'];
			
			// Find all occurrences of the entity label in text
			$positions = $this->find_text_positions( $text, $label );
			
			foreach ( $positions as $pos ) {
				$annotation_id = 'annotation-' . md5( $entity_id . $pos['start'] );
				$annotations[ $annotation_id ] = array(
					'id' => $annotation_id,
					'entityMatches' => array(
						array(
							'entityId' => $entity_id,
							'confidence' => $entity['confidence'],
						),
					),
					'text' => $label,
					'start' => $pos['start'],
					'end' => $pos['end'],
				);
			}
		}

		return $annotations;
	}

	/**
	 * Find all positions of a text in content.
	 *
	 * @param string $text Full text.
	 * @param string $search Search term.
	 * @return array Array of positions.
	 */
	private function find_text_positions( $text, $search ) {
		$positions = array();
		$offset = 0;
		$search_lower = strtolower( $search );
		$text_lower = strtolower( $text );

		while ( ( $pos = strpos( $text_lower, $search_lower, $offset ) ) !== false ) {
			$positions[] = array(
				'start' => $pos,
				'end' => $pos + strlen( $search ),
			);
			$offset = $pos + 1;
		}

		return $positions;
	}

	/**
	 * Extract topics from text (simple keyword extraction).
	 *
	 * @param string $text Text content.
	 * @return array Topics array.
	 */
	private function extract_topics( $text ) {
		// Simple topic extraction based on frequent words
		$words = str_word_count( strtolower( $text ), 1 );
		
		// Remove common stop words
		$stop_words = array( 'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should' );
		$words = array_filter( $words, function( $word ) use ( $stop_words ) {
			return strlen( $word ) > 3 && ! in_array( $word, $stop_words, true );
		} );

		// Count word frequency
		$word_counts = array_count_values( $words );
		arsort( $word_counts );

		// Get top 5 topics
		$topics = array();
		$top_words = array_slice( array_keys( $word_counts ), 0, 5 );
		
		foreach ( $top_words as $word ) {
			$topics[ 'topic-' . md5( $word ) ] = array(
				'id' => 'topic-' . md5( $word ),
				'label' => ucfirst( $word ),
				'confidence' => $word_counts[ $word ] / count( $words ),
			);
		}

		return $topics;
	}
}

