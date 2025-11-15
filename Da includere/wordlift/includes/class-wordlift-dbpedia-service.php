<?php
/**
 * DBpedia Service
 *
 * Provides entity lookup using DBpedia SPARQL endpoint.
 *
 * @package Wordlift
 * @since 3.40.0
 */

class Wordlift_DBpedia_Service {

	/**
	 * Search for entities in DBpedia.
	 *
	 * @param string $search_term Search term.
	 * @param string $language Language code.
	 * @param int    $limit Maximum results.
	 * @return array Array of entities.
	 */
	public static function search( $search_term, $language = 'en', $limit = 10 ) {
		// DBpedia SPARQL endpoint
		$sparql_endpoint = 'https://dbpedia.org/sparql';
		
		// Build SPARQL query
		$query = sprintf(
			'SELECT DISTINCT ?entity ?label ?abstract ?type WHERE {
				?entity rdfs:label ?label .
				FILTER (lang(?label) = "%s" || lang(?label) = "en") .
				FILTER (regex(?label, "%s", "i")) .
				OPTIONAL { ?entity dbo:abstract ?abstract . FILTER (lang(?abstract) = "%s" || lang(?abstract) = "en") } .
				OPTIONAL { ?entity rdf:type ?type . FILTER (strstarts(str(?type), "http://dbpedia.org/ontology/")) } .
			} LIMIT %d',
			$language,
			addslashes( $search_term ),
			$language,
			$limit
		);

		$url = add_query_arg(
			array(
				'query' => $query,
				'format' => 'json',
			),
			$sparql_endpoint
		);

		$response = wp_remote_get( $url, array( 'timeout' => 10 ) );

		if ( is_wp_error( $response ) ) {
			return array();
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! isset( $data['results']['bindings'] ) ) {
			return array();
		}

		$entities = array();
		foreach ( $data['results']['bindings'] as $binding ) {
			$entity_uri = $binding['entity']['value'];
			$label = isset( $binding['label']['value'] ) ? $binding['label']['value'] : '';
			$abstract = isset( $binding['abstract']['value'] ) ? $binding['abstract']['value'] : '';
			$type = isset( $binding['type']['value'] ) ? $binding['type']['value'] : '';

			$entities[] = array(
				'id' => $entity_uri,
				'label' => $label,
				'description' => $abstract,
				'type' => $type,
				'source' => 'dbpedia',
			);
		}

		return $entities;
	}
}

