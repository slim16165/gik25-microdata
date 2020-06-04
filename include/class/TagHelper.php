<?php


	class TagHelper
	{
		public function __construct()
		{
		}

		public static function add_filter_DisableTagWith1Post()
		{
			#region Imparare Wordpress
			//			add_filter( 'term_links-post_tag', array(__CLASS__, 'modify_term_link_url') );
			//			add_filter( 'wp_get_object_terms', array(__CLASS__, 'filter_wp_get_object_terms'), 10, 4 );
			#endregion

			add_filter( 'tag_link'  		 , array(__CLASS__, 'removeLinkFromOnePostTag'), 10, 2 );
			add_action( 'template_redirect'	 , array(__CLASS__, 'tagWithOnePostRedirect'), 5);
		}
		/**
		 * Returns a link to a tag. Instead of /tag/tag-name/ returns /tag-name.html
		 */
		public static function removeLinkFromOnePostTag($tag_link, $tag_id)
		{
			$tag = get_tag($tag_id);

			if ($tag->count == 1)
			{
				return '';
			}

			else return $tag_link;
		}

		/**
		 * Redirects visitors to the homepage for Tags with
		 * less than 10 posts associated to them.
		 */
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
					$post_id = find_post_id_from_taxonomy($tag->term_id, 'post_tag');
					wp_redirect(
						get_permalink($post_id),
						'301' // The HTTP status, 301 = Moved
					);
				}
			}
		}
	}

