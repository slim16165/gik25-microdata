<?php
namespace gik25microdata\Utility;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
	class TagHelper
	{
		public function __construct()
        {
        }

        public static function add_filter_DisableTagWith1Post(): void
        {
            add_filter('tag_link', array(__CLASS__, 'removeLinkFromTags'), 10, 2);
            //add_action('template_redirect', array(__CLASS__, 'tagWithOnePostRedirect'), 5);
            //add_filter('wpseo_exclude_from_sitemap_by_term_ids', array(__CLASS__, 'wpseo_exclude_from_sitemap_1postTags'), 10000);
        }

        public static function wpseo_exclude_from_sitemap_1postTags($alreadyExcluded): array
        {
            $excludeTagId = array_merge($alreadyExcluded, self::find_tags_with_only_one_post());
            return $excludeTagId;
        }

        /** @noinspection PhpUnused */
        public static function removeLinkFromOnePostTag($tag_link, $tag_id)
        {
            $tag = get_tag($tag_id);

            if ($tag->count == 1)
            {
                return '';
            } else return $tag_link;
        }

        /** @noinspection PhpUnused */
        public static function removeLinkFromTags(): string
        {
            return '';
        }

        /**
         * Redirects visitors to the homepage for Tags with
         * less than 10 posts associated to them.
         */
        /** @noinspection PhpUnused */
        public static function tagWithOnePostRedirect(): void
        {
            // We're viewing a Tag archive page
            if (is_tag())
			{
				// Get Tag object
				$tag = get_tag(get_queried_object_id());

				// Tag's post count
				$post_count = $tag->count;

				// This tag has less than 10 posts, redirect visitor
				if ($post_count == 1)
				{
                    // La funzione si aspetta il nome del tag, non l'ID
                    $post_ids = self::find_post_id_from_taxonomy($tag->name, 'post_tag');
                    if (!empty($post_ids)) {
                        $post_id = $post_ids[0]; // Prendi il primo (e unico) post
                        wp_redirect(
                            get_permalink($post_id),
                            301 // The HTTP status, 301 = Moved
                        );
                        exit;
                    }
                }
            }
        }


        public static function find_tags_with_only_one_post(): array
        {
            global $wpdb;

            // Usa il prefisso dinamico delle tabelle WordPress
            $prefix = $wpdb->prefix;
            $posts_table = $prefix . 'posts';
            $terms_table = $prefix . 'terms';
            $term_taxonomy_table = $prefix . 'term_taxonomy';
            $term_relationships_table = $prefix . 'term_relationships';

            $sql = $wpdb->prepare(
                "SELECT {$terms_table}.term_id, count(DISTINCT {$posts_table}.ID)
                FROM {$posts_table}
                INNER JOIN {$term_relationships_table}
                  ON {$term_relationships_table}.object_id = {$posts_table}.ID
                INNER JOIN {$term_taxonomy_table}
                  ON {$term_taxonomy_table}.term_taxonomy_id = {$term_relationships_table}.term_taxonomy_id
                    AND {$term_taxonomy_table}.taxonomy = %s 
                INNER JOIN {$terms_table}
                  ON {$terms_table}.term_id = {$term_taxonomy_table}.term_id    
                WHERE {$posts_table}.post_type = 'post'
                  AND {$posts_table}.post_status = 'publish'
                  AND {$posts_table}.post_parent = 0
                GROUP by {$terms_table}.term_id
                HAVING count(DISTINCT {$posts_table}.ID) = 1",
                'post_tag'
            );

			$result = $wpdb->get_results($sql);


            $getTermId = function ($value) {
                return intval($value->term_id);
            };
            $ids = array_map($getTermId, $result);

            return $ids;
        }


        /**
         * @param string $term_name The taxonomy value (tag or category name)
         * @param string $taxonomy_type tag or category ('post_tag' or 'post_category')
         * @return array Returns all the id of posts from a given tag or category
         */
        public static function find_post_id_from_taxonomy(string $term_name, string $taxonomy_type): array
        {
            #region Check errors

            if ($taxonomy_type != 'post_tag' && $taxonomy_type != 'category')
            {
                // Log errore invece di fare exit
                error_log("gik25microdata\Utility\TagHelper::find_post_id_from_taxonomy: taxonomy_type deve essere 'post_tag' o 'category', ricevuto: " . $taxonomy_type);
                return [];
            }

            global $wpdb;

			#endregion

            // Usa il prefisso dinamico delle tabelle WordPress
            $prefix = $wpdb->prefix;
            $posts_table = $prefix . 'posts';
            $terms_table = $prefix . 'terms';
            $term_taxonomy_table = $prefix . 'term_taxonomy';
            $term_relationships_table = $prefix . 'term_relationships';

            // Prepara la query SQL con prepared statement per sicurezza (previene SQL injection)
            $sql = $wpdb->prepare(
                "SELECT {$posts_table}.ID
                FROM {$posts_table}
                INNER JOIN {$term_relationships_table}
                  ON {$term_relationships_table}.object_id = {$posts_table}.ID
                INNER JOIN {$term_taxonomy_table}
                  ON {$term_taxonomy_table}.term_taxonomy_id = {$term_relationships_table}.term_taxonomy_id
                    AND {$term_taxonomy_table}.taxonomy = %s
                INNER JOIN {$terms_table}
                  ON {$terms_table}.term_id = {$term_taxonomy_table}.term_id
                    AND {$terms_table}.name = %s
                WHERE {$posts_table}.post_type = 'post'
                  AND {$posts_table}.post_status = 'publish'
                  AND {$posts_table}.post_parent = 0",
                $taxonomy_type,
                $term_name
            );

			$result = $wpdb->get_results($sql);

			#region Imparare PHP

			//[ array_column() ] Return the values from a single column in the input array
			//Easy
			//		$ids = [];
			//		foreach ($values as $value)     {
			//			$ids[] = $value->ID    ;
			//		}

			#endregion

			$fn = function ($value)
			{
				return $value->ID;
			};
			$ids = array_map($fn, $result);

			return $ids;
		}

	}