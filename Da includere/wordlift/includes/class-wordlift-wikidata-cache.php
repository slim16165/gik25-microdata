<?php
/**
 * Wikidata Entity Cache
 *
 * Caches Wikidata entity data locally to reduce API calls.
 *
 * @package Wordlift
 * @since 3.40.0
 */

class Wordlift_Wikidata_Cache {

	const CACHE_GROUP = 'wl_wikidata';
	const CACHE_EXPIRY = WEEK_IN_SECONDS; // Cache for 1 week

	/**
	 * Get cached entity data.
	 *
	 * @param string $entity_id Wikidata entity ID (e.g., 'Q13166').
	 * @return array|false Entity data or false if not cached.
	 */
	public static function get( $entity_id ) {
		$cache_key = 'entity_' . $entity_id;
		return wp_cache_get( $cache_key, self::CACHE_GROUP );
	}

	/**
	 * Cache entity data.
	 *
	 * @param string $entity_id Wikidata entity ID.
	 * @param array  $entity_data Entity data to cache.
	 */
	public static function set( $entity_id, $entity_data ) {
		$cache_key = 'entity_' . $entity_id;
		wp_cache_set( $cache_key, $entity_data, self::CACHE_GROUP, self::CACHE_EXPIRY );
	}

	/**
	 * Get cached search results.
	 *
	 * @param string $search_term Search term.
	 * @param string $language Language code.
	 * @return array|false Search results or false if not cached.
	 */
	public static function get_search( $search_term, $language = 'en' ) {
		$cache_key = 'search_' . md5( $search_term . '_' . $language );
		return wp_cache_get( $cache_key, self::CACHE_GROUP );
	}

	/**
	 * Cache search results.
	 *
	 * @param string $search_term Search term.
	 * @param string $language Language code.
	 * @param array  $results Search results.
	 */
	public static function set_search( $search_term, $language, $results ) {
		$cache_key = 'search_' . md5( $search_term . '_' . $language );
		wp_cache_set( $cache_key, $results, self::CACHE_GROUP, DAY_IN_SECONDS ); // Cache searches for 1 day
	}

	/**
	 * Clear all cache.
	 */
	public static function clear() {
		wp_cache_flush_group( self::CACHE_GROUP );
	}
}

