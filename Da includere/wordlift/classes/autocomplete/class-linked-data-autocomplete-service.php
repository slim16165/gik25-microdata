<?php
/**
 * This file provides the Linked Data autocomplete service.
 *
 * @author David Riccitelli <david@wordlift.io>
 * @since 3.24.2
 * @package Wordlift\Autocomplete
 */

namespace Wordlift\Autocomplete;

use Wordlift\Api\Default_Api_Service;
use Wordlift\Entity\Entity_Helper;
use Wordlift_Configuration_Service;
use Wordlift_Log_Service;
use Wordlift_Post_Excerpt_Helper;
use Wordlift_Schema_Service;

class Linked_Data_Autocomplete_Service implements Autocomplete_Service {

	/**
	 * A {@link Wordlift_Log_Service} instance.
	 *
	 * @since  3.15.0
	 * @access private
	 * @var \Wordlift_Log_Service $log A {@link Wordlift_Log_Service} instance.
	 */
	private $log;
	private $entity_helper;
	private $entity_uri_service;
	/**
	 * @var \Wordlift_Entity_Service
	 */
	private $entity_service;

	/**
	 * The {@link Class_Wordlift_Autocomplete_Service} instance.
	 *
	 * @param Entity_Helper                $entity_helper
	 * @param \Wordlift_Entity_Uri_Service $entity_uri_service
	 * @param \Wordlift_Entity_Service     $entity_service
	 *
	 * @since 3.15.0
	 */
	public function __construct( $entity_helper, $entity_uri_service, $entity_service ) {

		$this->log = Wordlift_Log_Service::get_logger( 'Wordlift_Autocomplete_Service' );

		$this->entity_helper      = $entity_helper;
		$this->entity_uri_service = $entity_uri_service;
		$this->entity_service     = $entity_service;
	}

	/**
	 * Make request to external API and return the response.
	 *
	 * @param string       $query The search string.
	 * @param string       $scope The search scope: "local" will search only in the local dataset; "cloud" will search also
	 *                            in Wikipedia. By default is "cloud".
	 * @param array|string $excludes The exclude parameter string.
	 *
	 * @return array $response The API response.
	 * @since 3.15.0
	 */
	public function query( $query, $scope = 'cloud', $excludes = array() ) {

		$results = $this->do_query( $query, $scope, $excludes );

		$uris = array_reduce(
			$results,
			function ( $carry, $result ) {

				$carry[] = $result['id'];

				return array_merge( $carry, $result['sameAss'] );
			},
			array()
		);

		$mappings = $this->entity_helper->map_many_to_local( $uris );

		$that           = $this;
		$mapped_results = array_map(
			function ( $result ) use ( $that, $mappings ) {

				if ( $that->entity_uri_service->is_internal( $result['id'] ) ) {
						return $result;
				}

				$uris = array_merge( (array) $result['id'], $result['sameAss'] );

				foreach ( $uris as $uri ) {
					if ( isset( $mappings[ $uri ] ) ) {
						$local_entity = $that->entity_uri_service->get_entity( $mappings[ $uri ] );

						return $that->post_to_autocomplete_result( $mappings[ $uri ], $local_entity );
					}
				}

				return $result;
			},
			$results
		);

		return $mapped_results;
	}

	private function do_query( $query, $scope = 'cloud', $exclude = '' ) {
		// MODIFIED: Use Wikidata Search API with caching and advanced search
		if ( 'local' === $scope ) {
			// For local scope, return empty (local entities are handled by Local_Autocomplete_Service)
			return array();
		}

		$configuration_service = Wordlift_Configuration_Service::get_instance();
		$language = $configuration_service->get_language_code();

		require_once __DIR__ . '/../../includes/class-wordlift-wikidata-cache.php';

		// Check cache first
		$cached_results = Wordlift_Wikidata_Cache::get_search( $query, $language );
		if ( $cached_results !== false && is_array( $cached_results ) ) {
			return $cached_results;
		}

		// Use Wikidata Search API with fuzzy matching
		$wikidata_url = add_query_arg(
			array(
				'action'   => 'wbsearchentities',
				'search'   => $query,
				'language' => $language,
				'format'   => 'json',
				'limit'    => 15, // Get more results for better filtering
			),
			'https://www.wikidata.org/w/api.php'
		);

		$this->log->debug( "Local mode: Querying Wikidata for autocomplete: $query" );

		$response = wp_remote_get( $wikidata_url, array( 'timeout' => 5 ) );

		if ( is_wp_error( $response ) ) {
			$this->log->error( 'Wikidata autocomplete error: ' . $response->get_error_message() );
			return array();
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			$this->log->error( "Wikidata autocomplete returned code: $response_code" );
			return array();
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! isset( $body['search'] ) || ! is_array( $body['search'] ) ) {
			return array();
		}

		// Filter and rank results using fuzzy matching
		$filtered_results = $this->filter_and_rank_results( $query, $body['search'], $exclude );

		// Cache the results
		Wordlift_Wikidata_Cache::set_search( $query, $language, $filtered_results );

		return $filtered_results;
	}

	/**
	 * Filter and rank search results using fuzzy matching.
	 *
	 * @param string $query Original search query.
	 * @param array  $results Raw search results.
	 * @param string $exclude Excluded URIs.
	 * @return array Filtered and ranked results.
	 */
	private function filter_and_rank_results( $query, $results, $exclude ) {
		$ranked_results = array();
		$query_lower = strtolower( $query );

		foreach ( $results as $item ) {
			$entity_id = 'https://www.wikidata.org/wiki/' . $item['id'];
			
			// Skip excluded URIs
			if ( ! empty( $exclude ) ) {
				$exclude_array = is_array( $exclude ) ? $exclude : array( $exclude );
				if ( in_array( $entity_id, $exclude_array, true ) ) {
					continue;
				}
			}

			$label = isset( $item['label'] ) ? $item['label'] : '';
			$label_lower = strtolower( $label );

			// Calculate relevance score
			$score = 0;
			
			// Exact match gets highest score
			if ( $label_lower === $query_lower ) {
				$score = 100;
			} elseif ( strpos( $label_lower, $query_lower ) !== false ) {
				// Contains query gets high score
				$score = 80;
			} else {
				// Fuzzy match score
				$similarity = $this->calculate_similarity( $query_lower, $label_lower );
				$score = $similarity * 60;
			}

			// Boost score if has description
			if ( ! empty( $item['description'] ) ) {
				$score += 5;
			}

			// Build labels array (label + aliases)
			$labels = array( $label );
			if ( isset( $item['aliases'] ) && is_array( $item['aliases'] ) ) {
				$labels = array_merge( $labels, $item['aliases'] );
			}

			$result = array(
				'id'           => $entity_id,
				'label'        => array( $label ),
				'labels'       => $labels,
				'descriptions' => isset( $item['description'] ) && ! empty( $item['description'] ) ? array( $item['description'] ) : array(),
				'scope'        => 'cloud',
				'sameAss'      => array(),
				'types'        => isset( $item['concepturi'] ) ? array( $item['concepturi'] ) : array( 'http://schema.org/Thing' ),
				'urls'         => array(),
				'images'       => array(),
				'_score'       => $score, // Internal score for sorting
			);

			// Add displayTypes if available
			if ( isset( $item['match'] ) && isset( $item['match']['type'] ) ) {
				$result['displayTypes'] = array( $item['match']['type'] );
			} else {
				$result['displayTypes'] = array( 'Thing' );
			}

			$ranked_results[] = $result;
		}

		// Sort by score (descending)
		usort( $ranked_results, function( $a, $b ) {
			return ( $b['_score'] ?? 0 ) - ( $a['_score'] ?? 0 );
		} );

		// Remove score from final results and limit to top 10
		$final_results = array();
		foreach ( array_slice( $ranked_results, 0, 10 ) as $result ) {
			unset( $result['_score'] );
			$final_results[] = $result;
		}

		return $final_results;
	}

	/**
	 * Calculate similarity between two strings.
	 *
	 * @param string $str1 First string.
	 * @param string $str2 Second string.
	 * @return float Similarity score (0-1).
	 */
	private function calculate_similarity( $str1, $str2 ) {
		$max_len = max( strlen( $str1 ), strlen( $str2 ) );
		if ( $max_len === 0 ) {
			return 1.0;
		}

		$distance = levenshtein( $str1, $str2 );
		return 1 - ( $distance / $max_len );
	}

	/**
	 * Build the autocomplete url.
	 * 
	 * NOTE: This method is no longer used in local mode (replaced by Wikidata API),
	 * but kept for backward compatibility.
	 *
	 * @param string       $query The search string.
	 * @param array|string $exclude The exclude parameter.
	 * @param string       $scope The search scope: "local" will search only in the local dataset; "cloud" will search also
	 *                            in Wikipedia. By default is "cloud".
	 *
	 * @return string Built url.
	 * @since 3.15.0
	 */
	private function build_request_url( $query, $exclude, $scope ) {
		$configuration_service = Wordlift_Configuration_Service::get_instance();

		$args = array(
			'key'      => $configuration_service->get_key(),
			'language' => $configuration_service->get_language_code(),
			'query'    => $query,
			'scope'    => $scope,
			'limit'    => 10,
		);

		// Add args to URL.
		$request_url = add_query_arg( urlencode_deep( $args ), '/autocomplete' );

		// Add the exclude parameter.
		if ( ! empty( $exclude ) ) {
			$request_url .= '&exclude=' . implode( '&exclude=', array_map( 'urlencode', (array) $exclude ) );
		}

		// return the built url.
		return $request_url;
	}

	private function post_to_autocomplete_result( $uri, $post ) {

		return array(
			'id'           => $uri,
			'label'        => array( $post->post_title ),
			'labels'       => $this->entity_service->get_alternative_labels( $post->ID ),
			'descriptions' => array( Wordlift_Post_Excerpt_Helper::get_text_excerpt( $post ) ),
			'scope'        => 'local',
			'sameAss'      => get_post_meta( $post->ID, Wordlift_Schema_Service::FIELD_SAME_AS ),
			// The following properties are less relevant because we're linking entities that exist already in the
			// vocabulary. That's why we don't make an effort to load the real data.
			'types'        => array( 'http://schema.org/Thing' ),
			'urls'         => array(),
			'images'       => array(),
		);
	}
}
