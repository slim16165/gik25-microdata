<?php


	class TagHelper
	{
		public function __construct()
		{
		}

		public static function add_filter_DisableTagWith1Post()
		{
			add_filter('tag_link', array(__CLASS__, 'removeLinkFromOnePostTag'), 10, 2);
			add_action('template_redirect', array(__CLASS__, 'tagWithOnePostRedirect'), 5);
			add_filter('wpseo_exclude_from_sitemap_by_term_ids', array(__CLASS__, 'wpseo_exclude_from_sitemap_1postTags'), 10000);
		}

		public static function wpseo_exclude_from_sitemap_1postTags($alreadyExcluded)
		{
			$excludeTagId = array_merge($alreadyExcluded, TagHelper::find_tags_with_only_one_post());
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

		/**
		 * Redirects visitors to the homepage for Tags with
		 * less than 10 posts associated to them.
		 */
		/** @noinspection PhpUnused */
		public static function tagWithOnePostRedirect()
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
					$post_id = TagHelper::find_post_id_from_taxonomy($tag->term_id, 'post_tag');
					wp_redirect(
						get_permalink($post_id),
						'301' // The HTTP status, 301 = Moved
					);
				}
			}
		}


		public static function find_tags_with_only_one_post()
		{
			global $wpdb;

			$sql = <<<TAG
SELECT wp_terms.term_id, count(DISTINCT wp_posts.ID)
FROM wp_posts
INNER JOIN wp_term_relationships
  ON wp_term_relationships.object_id = wp_posts.ID
INNER JOIN wp_term_taxonomy
  ON wp_term_taxonomy.term_taxonomy_id = wp_term_relationships.term_taxonomy_id
    AND wp_term_taxonomy.taxonomy = 'post_tag' 
INNER JOIN wp_terms
  ON wp_terms.term_id = wp_term_taxonomy.term_id    
WHERE wp_posts.post_type = 'post'
  AND wp_posts.post_status = 'publish'
  AND wp_posts.post_parent = 0
GROUP by wp_terms.term_id
HAVING count(DISTINCT wp_posts.ID) = 1
TAG;

			$result = $wpdb->get_results($sql);


			$getTermId = function ($value)
			{
				return intval($value->term_id);
			};
			$ids = array_map($getTermId, $result);

			return $ids;
		}


		public static function find_post_id_from_taxonomy($term_name, $taxonomy_type)
		{
			#region Check errors

			if ($taxonomy_type != 'post_tag' && $taxonomy_type = 'post_category')
			{
				echo "error: era atteso un tag o categoria";
				exit;
			}

			global $wpdb;

			#endregion

			$sql = <<<TAG
SELECT wp_posts.ID
FROM wp_posts
INNER JOIN wp_term_relationships
  ON wp_term_relationships.object_id = wp_posts.ID
INNER JOIN wp_term_taxonomy
  ON wp_term_taxonomy.term_taxonomy_id = wp_term_relationships.term_taxonomy_id
    AND wp_term_taxonomy.taxonomy = '{$taxonomy_type}'
INNER JOIN wp_terms
  ON wp_terms.term_id = wp_term_taxonomy.term_id
    AND wp_terms.name = '{$term_name}'
WHERE wp_posts.post_type = 'post'
  AND wp_posts.post_status = 'publish'
  AND wp_posts.post_parent = 0
TAG;

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



