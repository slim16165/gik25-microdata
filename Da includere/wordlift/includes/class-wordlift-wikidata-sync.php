<?php
/**
 * Wikidata Entity Synchronization
 *
 * Synchronizes entity metadata from Wikidata to local entities.
 *
 * @package Wordlift
 * @since 3.40.0
 */

class Wordlift_Wikidata_Sync {

	/**
	 * Sync entity data from Wikidata.
	 *
	 * @param string $wikidata_id Wikidata entity ID (e.g., 'Q13166').
	 * @param int    $post_id WordPress post ID (entity post).
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public static function sync_entity( $wikidata_id, $post_id ) {
		require_once __DIR__ . '/class-wordlift-wikidata-cache.php';

		// Check cache first
		$cached = Wordlift_Wikidata_Cache::get( $wikidata_id );
		if ( $cached !== false ) {
			$entity_data = $cached;
		} else {
			// Fetch from Wikidata API
			$entity_data = self::fetch_wikidata_entity( $wikidata_id );
			if ( is_wp_error( $entity_data ) ) {
				return $entity_data;
			}
			// Cache the result
			Wordlift_Wikidata_Cache::set( $wikidata_id, $entity_data );
		}

		// Update post with Wikidata data
		return self::update_post_from_wikidata( $post_id, $entity_data );
	}

	/**
	 * Fetch entity data from Wikidata API.
	 *
	 * @param string $wikidata_id Wikidata entity ID.
	 * @return array|WP_Error Entity data or WP_Error.
	 */
	private static function fetch_wikidata_entity( $wikidata_id ) {
		// Clean ID (remove Q prefix if in URL format)
		$clean_id = preg_replace( '/^.*\/(Q\d+)$/', '$1', $wikidata_id );
		if ( empty( $clean_id ) ) {
			$clean_id = $wikidata_id;
		}

		$url = add_query_arg(
			array(
				'action' => 'wbgetentities',
				'ids' => $clean_id,
				'format' => 'json',
				'props' => 'labels|descriptions|claims|sitelinks',
			),
			'https://www.wikidata.org/w/api.php'
		);

		$response = wp_remote_get( $url, array( 'timeout' => 10 ) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! isset( $data['entities'][ $clean_id ] ) ) {
			return new WP_Error( 'entity_not_found', 'Entity not found in Wikidata.' );
		}

		$entity = $data['entities'][ $clean_id ];
		
		// Extract language-specific labels and descriptions
		$language = get_locale();
		$lang_code = substr( $language, 0, 2 );
		
		$label = '';
		$description = '';
		
		if ( isset( $entity['labels'][ $lang_code ]['value'] ) ) {
			$label = $entity['labels'][ $lang_code ]['value'];
		} elseif ( isset( $entity['labels']['en']['value'] ) ) {
			$label = $entity['labels']['en']['value'];
		}

		if ( isset( $entity['descriptions'][ $lang_code ]['value'] ) ) {
			$description = $entity['descriptions'][ $lang_code ]['value'];
		} elseif ( isset( $entity['descriptions']['en']['value'] ) ) {
			$description = $entity['descriptions']['en']['value'];
		}

		// Extract image URL if available
		$image_url = '';
		if ( isset( $entity['claims']['P18'] ) ) { // P18 = image property
			$image_claim = $entity['claims']['P18'][0];
			if ( isset( $image_claim['mainsnak']['datavalue']['value'] ) ) {
				$image_filename = $image_claim['mainsnak']['datavalue']['value'];
				$image_url = 'https://commons.wikimedia.org/wiki/Special:FilePath/' . urlencode( $image_filename );
			}
		}

		return array(
			'id' => $clean_id,
			'label' => $label,
			'description' => $description,
			'image_url' => $image_url,
			'raw_data' => $entity,
		);
	}

	/**
	 * Update WordPress post with Wikidata data.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $entity_data Entity data from Wikidata.
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	private static function update_post_from_wikidata( $post_id, $entity_data ) {
		// Update post title if empty or different
		$current_title = get_the_title( $post_id );
		if ( empty( $current_title ) && ! empty( $entity_data['label'] ) ) {
			wp_update_post( array(
				'ID' => $post_id,
				'post_title' => $entity_data['label'],
			) );
		}

		// Update post excerpt/description
		if ( ! empty( $entity_data['description'] ) ) {
			$current_excerpt = get_the_excerpt( $post_id );
			if ( empty( $current_excerpt ) ) {
				wp_update_post( array(
					'ID' => $post_id,
					'post_excerpt' => $entity_data['description'],
				) );
			}
		}

		// Store Wikidata ID in meta
		update_post_meta( $post_id, '_wl_wikidata_id', $entity_data['id'] );
		update_post_meta( $post_id, '_wl_wikidata_synced', current_time( 'mysql' ) );

		// Download and set featured image if available
		if ( ! empty( $entity_data['image_url'] ) && ! has_post_thumbnail( $post_id ) ) {
			require_once __DIR__ . '/class-wordlift-remote-image-service.php';
			$image_data = Wordlift_Remote_Image_Service::save_from_url( $entity_data['image_url'] );
			if ( ! is_wp_error( $image_data ) && isset( $image_data['path'] ) ) {
				$attachment_id = self::create_attachment_from_path( $image_data['path'], $post_id );
				if ( $attachment_id ) {
					set_post_thumbnail( $post_id, $attachment_id );
				}
			}
		}

		return true;
	}

	/**
	 * Create WordPress attachment from file path.
	 *
	 * @param string $file_path Path to image file.
	 * @param int    $post_id Parent post ID.
	 * @return int|false Attachment ID or false on failure.
	 */
	private static function create_attachment_from_path( $file_path, $post_id ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$filename = basename( $file_path );
		$upload_file = wp_upload_bits( $filename, null, file_get_contents( $file_path ) );

		if ( $upload_file['error'] ) {
			return false;
		}

		$attachment = array(
			'post_mime_type' => wp_check_filetype( $filename )['type'],
			'post_title' => sanitize_file_name( pathinfo( $filename, PATHINFO_FILENAME ) ),
			'post_content' => '',
			'post_status' => 'inherit',
		);

		$attach_id = wp_insert_attachment( $attachment, $upload_file['file'], $post_id );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $upload_file['file'] );
		wp_update_attachment_metadata( $attach_id, $attach_data );

		return $attach_id;
	}
}

