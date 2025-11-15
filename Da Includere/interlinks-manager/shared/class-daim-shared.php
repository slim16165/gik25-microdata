<?php
/**
 * This class should be used to stores properties and methods shared by the
 * admin and public side of WordPress.
 *
 * @package interlinks-manager
 */

use Daextteam\PluginUpdateChecker\PluginUpdateChecker;

/**
 * This class should be used to stores properties and methods shared by the
 * admin and public side of WordPress.
 */
class Daim_Shared {


	// Properties used in add_autolinks().

	/**
	 * The ID of the autolink.
	 *
	 * @var int
	 */
	private $ail_id;

	/**
	 * The autolink array.
	 *
	 * @var array
	 */
	private $ail_a;

	/**
	 * The object of the parsed autolink.
	 *
	 * @var object
	 */
	private $parsed_autolink;

	/**
	 * The parsed post type.
	 *
	 * @var string
	 */
	private $parsed_post_type = null;

	/**
	 * The max number of autolinks allowed per post.
	 *
	 * @var int
	 */
	private $max_number_autolinks_per_post;

	/**
	 * The same URL limit.
	 *
	 * @var int
	 */
	private $same_url_limit = null;

	/**
	 * An array with included the data of the autolinks used for performance reasons.
	 *
	 * @var array
	 */
	private $autolinks_ca = null;

	/**
	 * The ID of the protected block.
	 *
	 * @var null
	 */
	private $pb_id = null;

	/**
	 * The protected block array.
	 *
	 * @var null
	 */
	private $pb_a = null;

	/**
	 * The post ID of the protected block.
	 *
	 * @var null
	 */
	private $post_id = null;

	/**
	 * Regex used to validate a list of Gutenberg blocks.
	 *
	 * @var string
	 */
	public $regex_list_of_gutenberg_blocks = '/^(\s*([A-Za-z0-9-\/]+\s*,\s*)+[A-Za-z0-9-\/]+\s*|\s*[A-Za-z0-9-\/]+\s*)$/';

	/**
	 * Regex used to validate a list of post types.
	 *
	 * @var string
	 */
	public $regex_list_of_post_types = '/^(\s*([A-Za-z0-9_-]+\s*,\s*)+[A-Za-z0-9_-]+\s*|\s*[A-Za-z0-9_-]+\s*)$/';

	/**
	 * Regex used to validate a number with a maximum of 10 digits.
	 *
	 * @var string
	 */
	public $regex_number_ten_digits = '/^\s*\d{1,10}\s*$/';

	/**
	 * Rregex used to validate the user capability.
	 *
	 * @var string
	 */
	public $regex_capability = '/^\s*[A-Za-z0-9_]+\s*$/';

	/**
	 * The singleton instance of the class.
	 *
	 * @var Daim_Shared
	 */
	protected static $instance = null;

	/**
	 * The data of the plugin.
	 *
	 * @var array
	 */
	private $data = array();

	/**
	 * Constructor.
	 */
	private function __construct() {

		add_action( 'init', array( $this, 'cron_schedules_init' ) );

		add_action( 'daextdaim_cron_hook', array( $this, 'daextdaim_cron_exec' ) );

		$this->data['slug'] = 'daim';
		$this->data['ver']  = '1.41';
		$this->data['dir']  = substr( plugin_dir_path( __FILE__ ), 0, -7 );
		$this->data['url']  = substr( plugin_dir_url( __FILE__ ), 0, -7 );

		add_action( 'delete_term', array( $this, 'delete_term_action' ), 10, 3 );

		// Here are stored the plugin option with the related default values.
		$this->data['options'] = array(

			// Database version. (not available in the options UI).
			$this->get( 'slug' ) . '_database_version'     => '0',

			// Options version. (not available in the options UI).
			$this->get( 'slug' ) . '_options_version'     => '0',

			// Option used to save the dismissible notices of all the users.
			$this->get( 'slug' ) . '_dismissible_notice_a' => array(),

			// Used internally to verify the status of the last update of the broken link check. (not listed).
			$this->get( 'slug' ) . '_broken_list_check_last_update' => '',

			// Automatic Links ----------------------------------------------------------------------------------------.

			// Options.
			$this->get( 'slug' ) . '_default_enable_ail_on_post' => '1',
			$this->get( 'slug' ) . '_filter_priority'      => '2147483646',
			$this->get( 'slug' ) . '_ail_test_mode'        => '0',
			$this->get( 'slug' ) . '_random_prioritization' => '0',
			$this->get( 'slug' ) . '_ignore_self_ail'      => '1',
			$this->get( 'slug' ) . '_categories_and_tags_verification' => 'post',
			$this->get( 'slug' ) . '_general_limit_mode'   => '1',
			$this->get( 'slug' ) . '_characters_per_autolink' => '200',
			$this->get( 'slug' ) . '_max_number_autolinks_per_post' => '100',
			$this->get( 'slug' ) . '_general_limit_subtract_mil' => '0',
			$this->get( 'slug' ) . '_same_url_limit'       => '100',

			// Protected Elements.
			// By default, the following HTML tags are protected.
			$this->get( 'slug' ) . '_protected_tags'       => array(
				'h1',
				'h2',
				'h3',
				'h4',
				'h5',
				'h6',
				'a',
				'img',
				'ul',
				'ol',
				'span',
				'pre',
				'code',
				'table',
				'iframe',
				'script',
			),

			/**
			 * By default all the Gutenberg Blocks except the following are protected:
			 *
			 * - Paragraph
			 * - List
			 * - Text Columns
			 */
			$this->get( 'slug' ) . '_protected_gutenberg_blocks' => array(
				// 'paragraph',
				'image',
				'heading',
				'gallery',
				// 'list',
				'quote',
				'audio',
				'cover-image',
				'subhead',
				'video',
				'code',
				'html',
				'preformatted',
				'pullquote',
				'table',
				'verse',
				'button',
				'columns',
				'more',
				'nextpage',
				'separator',
				'spacer',
				// 'text-columns',
				'shortcode',
				'categories',
				'latest-posts',
				'embed',
				'core-embed/twitter',
				'core-embed/youtube',
				'core-embed/facebook',
				'core-embed/instagram',
				'core-embed/wordpress',
				'core-embed/soundcloud',
				'core-embed/spotify',
				'core-embed/flickr',
				'core-embed/vimeo',
				'core-embed/animoto',
				'core-embed/cloudup',
				'core-embed/collegehumor',
				'core-embed/dailymotion',
				'core-embed/funnyordie',
				'core-embed/hulu',
				'core-embed/imgur',
				'core-embed/issuu',
				'core-embed/kickstarter',
				'core-embed/meetup-com',
				'core-embed/mixcloud',
				'core-embed/photobucket',
				'core-embed/polldaddy',
				'core-embed/reddit',
				'core-embed/reverbnation',
				'core-embed/screencast',
				'core-embed/scribd',
				'core-embed/slideshare',
				'core-embed/smugmug',
				'core-embed/speaker',
				'core-embed/ted',
				'core-embed/tumblr',
				'core-embed/videopress',
				'core-embed/wordpress-tv',
			),

			$this->get( 'slug' ) . '_protected_gutenberg_custom_blocks' => '',
			$this->get( 'slug' ) . '_protected_gutenberg_custom_void_blocks' => '',

			// Defaults.
			$this->get( 'slug' ) . '_default_category_id'  => '0',
			$this->get( 'slug' ) . '_default_title'        => '',
			$this->get( 'slug' ) . '_default_open_new_tab' => '0',
			$this->get( 'slug' ) . '_default_use_nofollow' => '0',
			$this->get( 'slug' ) . '_default_activate_post_types' => array( 'post', 'page' ),
			$this->get( 'slug' ) . '_default_categories'   => '',
			$this->get( 'slug' ) . '_default_tags'         => '',
			$this->get( 'slug' ) . '_default_term_group_id' => '',
			$this->get( 'slug' ) . '_default_case_insensitive_search' => '0',
			$this->get( 'slug' ) . '_default_string_before' => '1',
			$this->get( 'slug' ) . '_default_string_after' => '1',
			$this->get( 'slug' ) . '_default_keyword_before' => '',
			$this->get( 'slug' ) . '_default_keyword_after' => '',
			$this->get( 'slug' ) . '_default_max_number_autolinks_per_keyword' => '100',
			$this->get( 'slug' ) . '_default_priority'     => '0',

			// Suggestions --------------------------------------------------------------------------------------------.

			// Options.
			$this->get( 'slug' ) . '_suggestions_pool_post_types' => array( 'post', 'page' ),
			$this->get( 'slug' ) . '_suggestions_pool_size' => 50,
			$this->get( 'slug' ) . '_suggestions_titles'   => 'consider',
			$this->get( 'slug' ) . '_suggestions_categories' => 'consider',
			$this->get( 'slug' ) . '_suggestions_tags'     => 'consider',
			$this->get( 'slug' ) . '_suggestions_post_type' => 'consider',

			// Link Analysis ------------------------------------------------------------------------------------------.

			// Juice.
			$this->get( 'slug' ) . '_default_seo_power'    => 1000,
			$this->get( 'slug' ) . '_penality_per_position_percentage' => '1',
			$this->get( 'slug' ) . '_remove_link_to_anchor' => '1',
			$this->get( 'slug' ) . '_remove_url_parameters' => '0',

			// Technical Options.
			$this->get( 'slug' ) . '_set_max_execution_time' => '1',
			$this->get( 'slug' ) . '_max_execution_time_value' => '300',
			$this->get( 'slug' ) . '_set_memory_limit'     => '0',
			$this->get( 'slug' ) . '_memory_limit_value'   => '512',
			$this->get( 'slug' ) . '_limit_posts_analysis' => '1000',
			$this->get( 'slug' ) . '_dashboard_post_types' => array( 'post', 'page' ),
			$this->get( 'slug' ) . '_juice_post_types'     => array( 'post', 'page' ),
			$this->get( 'slug' ) . '_http_status_post_types' => array( 'post', 'page' ),

			// Advanced -----------------------------------------------------------------------------------------------.

			// Click Tracking.
			$this->get( 'slug' ) . '_track_internal_links' => '1',

			// Optimization Parameters.
			$this->get( 'slug' ) . '_optimization_num_of_characters' => 1000,
			$this->get( 'slug' ) . '_optimization_delta'   => 2,

			// Meta boxes.
			$this->get( 'slug' ) . '_interlinks_options_post_types' => array( 'post', 'page' ),
			$this->get( 'slug' ) . '_interlinks_optimization_post_types' => array( 'post', 'page' ),
			$this->get( 'slug' ) . '_interlinks_suggestions_post_types' => array( 'post', 'page' ),

			// Capabilities.
			$this->get( 'slug' ) . '_dashboard_menu_required_capability' => 'edit_others_posts',
			$this->get( 'slug' ) . '_juice_menu_required_capability' => 'edit_others_posts',
			$this->get( 'slug' ) . '_hits_menu_required_capability' => 'edit_others_posts',
			$this->get( 'slug' ) . '_http_status_menu_required_capability' => 'edit_others_posts',
			$this->get( 'slug' ) . '_wizard_menu_required_capability' => 'edit_others_posts',
			$this->get( 'slug' ) . '_ail_menu_required_capability' => 'edit_others_posts',
			$this->get( 'slug' ) . '_categories_menu_required_capability' => 'edit_others_posts',
			$this->get( 'slug' ) . '_term_groups_menu_required_capability' => 'edit_others_posts',
			$this->get( 'slug' ) . '_tools_menu_required_capability' => 'edit_others_posts',
			$this->get( 'slug' ) . '_maintenance_menu_required_capability' => 'edit_others_posts',
			$this->get( 'slug' ) . '_interlinks_options_mb_required_capability' => 'edit_others_posts',
			$this->get( 'slug' ) . '_interlinks_optimization_mb_required_capability' => 'edit_posts',
			$this->get( 'slug' ) . '_interlinks_suggestions_mb_required_capability' => 'edit_posts',

			// HTTP Status.
			$this->get( 'slug' ) . '_http_status_checks_per_iteration' => '2',
			$this->get( 'slug' ) . '_http_status_cron_schedule_interval' => '60',
			$this->get( 'slug' ) . '_http_status_request_timeout' => '10',

			// Misc.
			$this->get( 'slug' ) . '_wizard_rows'          => '500',
			$this->get( 'slug' ) . '_supported_terms'      => '10',
			$this->get( 'slug' ) . '_protect_attributes'   => '0',

			// Pagination.
			$this->get( 'slug' ) . '_pagination_dashboard_menu' => '10',
			$this->get( 'slug' ) . '_pagination_juice_menu' => '10',
			$this->get( 'slug' ) . '_pagination_http_status_menu' => '10',
			$this->get( 'slug' ) . '_pagination_hits_menu' => '10',
			$this->get( 'slug' ) . '_pagination_ail_menu'  => '10',
			$this->get( 'slug' ) . '_pagination_categories_menu' => '10',
			$this->get( 'slug' ) . '_pagination_term_groups_menu' => '10',

			// License ------------------------------------------------------------------------------------------------.

			// License Management.
			$this->get( 'slug' ) . '_license_provider' => 'daext_com',
			$this->get( 'slug' ) . '_license_key' => '',

		);
	}

	/**
	 * Get the singleton instance of the class.
	 *
	 * @return Daextrevo_Shared|self|null
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Filters the non-default cron schedules.
	 *
	 * @return void
	 */
	public function cron_schedules_init() {
		add_filter( 'cron_schedules', array( $this, 'custom_cron_schedule' ) );
	}

	/**
	 * Adds a custom cron schedule for every 5 minutes.
	 *
	 * @param array $schedules An array of non-default cron schedules.
	 * @return array Filtered array of non-default cron schedules.
	 */
	public function custom_cron_schedule( $schedules ) {

		// Add a custom schedule named 'daim_custom_schedule' to the existing set.
		$schedules['daim_custom_schedule'] = array(
			'interval' => intval( get_option( $this->get( 'slug' ) . '_http_status_cron_schedule_interval' ), 10 ),
			'display'  => __( 'Custom schedule based on the Interlinks Manager options.', 'interlinks-manager'),
		);
		return $schedules;
	}

	/**
	 * Retrieve data.
	 *
	 * @param string $index The index of the data to retrieve.
	 *
	 * @return mixed
	 */
	public function get( $index ) {
		return $this->data[ $index ];
	}

	/**
	 * Get the number of manual interlinks in a given string
	 *
	 * @param string $text The string in which the search should be performed.
	 * @return int The number of internal links in the string
	 */
	public function get_manual_interlinks( $text ) {

		// Remove the HTML comments.
		$text = $this->remove_html_comments( $text );

		// Remove script tags.
		$text = $this->remove_script_tags( $text );

		// Working regex.
		$num_matches = preg_match_all(
			$this->manual_and_auto_internal_links_regex(),
			$text,
			$matches
		);

		return $num_matches;
	}

	/**
	 * Count the number of auto interlinks in the string.
	 *
	 * @param string $string The string in which the search should be performed.
	 * @return int The number of autolinks
	 */
	public function get_autolinks_number( $string ) {

		// Remove the HTML comments.
		$string = $this->remove_html_comments( $string );

		// Remove script tags.
		$string = $this->remove_script_tags( $string );

		/**
		 * Get the website url and quote and escape the regex character. # and
		 * whitespace ( used with the 'x' modifier ) are not escaped, thus
		 * should not be included in the $site_url string.
		 */
		$site_url = preg_quote( get_home_url() );

		$num_matches = preg_match_all(
			'{
            <a\s+                   #1 The element a start-tag followed by one or more whitespace character
            data-ail="[\d]+"\s+     #2 The data-ail attribute followed by one or more whitespace character
            target="_[\w]+"\s+      #3 The target attribute followed by one or more whitespace character
            (?:rel="nofollow"\s+)?  #4 The rel="nofollow" attribute followed by one or more whitespace character, all is made optional by the trailing ? that works on the non-captured group ?:
            href\s*=\s*             #5 Equal may have whitespaces on both sides
            ([\'"]?)                #6 Match double quotes, single quote or no quote ( captured for the backreference \1 )
            ' . $site_url . '       #7 The site URL ( Scheme and Domain )
            [^\'">\s]*              #8 The rest of the URL ( Path and/or File )
            (\1)                    #9 Backreference that matches the href value delimiter matched at line 5
            [^>]*                   #10 Any character except > zero or more times
            >                       #11 End of the start-tag
            .+?                     #12 Any character one or more time with the quantifier lazy
            <\/a\s*>                #13 Element a end-tag with optional white-spaces characters before the >
            }ix',
			$string,
			$matches
		);

		return $num_matches;
	}

	/**
	 * Get the raw post_content of the specified post.
	 *
	 * @param int $post_id The ID of the post.
	 * @return string The raw post content.
	 */
	public function get_raw_post_content( $post_id ) {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$post_obj = $wpdb->get_row(
			$wpdb->prepare( "SELECT post_content FROM {$wpdb->prefix}posts WHERE ID = %d", $post_id )
		);

		return $post_obj->post_content;
	}

	/**
	 * The optimization is calculated based on:
	 * - the "Optimization Delta" option
	 * - the number of interlinks
	 * - the content length
	 * True is returned if the content is optimized, False if it's not optimized.
	 *
	 * @param int $number_of_interlinks The overall number of interlinks ( manual interlinks + auto interlinks ).
	 * @param int $content_length The content length.
	 * @return bool True if is optimized, False if is not optimized
	 */
	public function calculate_optimization( $number_of_interlinks, $content_length ) {

		// get the values of the options.
		$optimization_num_of_characters = (int) get_option( $this->get( 'slug' ) . '_optimization_num_of_characters' );
		$optimization_delta             = (int) get_option( $this->get( 'slug' ) . '_optimization_delta' );

		// Determines if this post is optimized.
		$optimal_number_of_interlinks = (int) $content_length / $optimization_num_of_characters;
		if (
			( $number_of_interlinks >= ( $optimal_number_of_interlinks - $optimization_delta ) ) &&
			( $number_of_interlinks <= ( $optimal_number_of_interlinks + $optimization_delta ) )
		) {
			$is_optimized = true;
		} else {
			$is_optimized = false;
		}

		return $is_optimized;
	}

	/**
	 * The optimal number of interlinks is calculated by dividing the content
	 * length for the value in the "Characters per Interlink" option and
	 * converting the result to an integer.
	 *
	 * @param int $number_of_interlinks The overall number of interlinks ( manual interlinks + auto interlinks ).
	 * @param int $content_length The content length.
	 * @return int The number of recommended interlinks
	 */
	public function calculate_recommended_interlinks( $number_of_interlinks, $content_length ) {

		// Get the values of the options.
		$optimization_num_of_characters = get_option( $this->get( 'slug' ) . '_optimization_num_of_characters' );
		$optimization_delta             = get_option( $this->get( 'slug' ) . '_optimization_delta' );

		// Determines the optimal number of interlinks.
		$optimal_number_of_interlinks = $content_length / $optimization_num_of_characters;

		return intval( $optimal_number_of_interlinks, 10 );
	}

	/**
	 * The minimum number of interlinks suggestion is calculated by subtracting
	 * half of the optimization delta from the optimal number of interlinks.
	 *
	 * @param int $post_id The post id.
	 * @return int The minimum number of interlinks suggestion
	 */
	public function get_suggested_min_number_of_interlinks( $post_id ) {

		// Get the content length of the raw post.
		$content_length = mb_strlen( $this->get_raw_post_content( $post_id ) );

		// Get the values of the options.
		$optimization_num_of_characters = intval( get_option( $this->get( 'slug' ) . '_optimization_num_of_characters' ), 10 );
		$optimization_delta             = intval( get_option( $this->get( 'slug' ) . '_optimization_delta' ), 10 );

		// Determines the optimal number of interlinks.
		$optimal_number_of_interlinks = $content_length / $optimization_num_of_characters;

		// Get the minimum number of interlinks.
		$min_number_of_interlinks = intval( ( $optimal_number_of_interlinks - ( $optimization_delta / 2 ) ), 10 );

		// Set to zero negative values.
		if ( $min_number_of_interlinks < 0 ) {
			$min_number_of_interlinks = 0; }

		return $min_number_of_interlinks;
	}

	/**
	 * The maximum number of interlinks suggestion is calculated by adding
	 * half of the optimization delta to the optimal number of interlinks.
	 *
	 * @param int $post_id The post id.
	 * @return int The maximum number of interlinks suggestion.
	 */
	public function get_suggested_max_number_of_interlinks( $post_id ) {

		// Get the content length of the raw post.
		$content_length = mb_strlen( $this->get_raw_post_content( $post_id ) );

		// Get the values of the options.
		$optimization_num_of_characters = get_option( $this->get( 'slug' ) . '_optimization_num_of_characters' );
		$optimization_delta             = get_option( $this->get( 'slug' ) . '_optimization_delta' );

		// Determines the optimal number of interlinks.
		$optimal_number_of_interlinks = $content_length / $optimization_num_of_characters;

		return intval( ( $optimal_number_of_interlinks + ( $optimization_delta / 2 ) ), 10 );
	}

	/**
	 * Get the number of hits related to a specific post.
	 *
	 * @param int $post_id The post_id for which the hits should be counted
	 * @return int The number of hits
	 */
	public function get_number_of_hits( $post_id ) {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$number_of_hits = $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}daim_hits WHERE source_post_id = %d", $post_id )
		);

		return $number_of_hits;
	}

	/**
	 * Add autolinks to the content based on the keyword created with the AIL
	 * menu:
	 *
	 * 1 - The protected blocks are applied with apply_protected_blocks()
	 * 2 - The words to be converted as a link are temporarely replaced with [ail]ID[/ail]
	 * 3 - The [ail]ID[/ail] identifiers are replaced with the actual links
	 * 4 - The protected block are removed with the remove_protected_blocks()
	 * 5 - The content with applied the autolinks is returned
	 *
	 * @param string $content The content on which the autolinks should be applied.
	 * @param bool   $check_query This parameter is set to True when the method is called inside the loop and is used to
	 *   verify if we are in a single post.
	 * @param string $post_type If the autolinks are added from the back-end this
	 * parameter is used to determine the post type of the content.
	 * @param int    $post_id This parameter is used if the method has been called outside the loop.
	 *
	 * @return string The content with applied the autolinks.
	 */
	public function add_autolinks( $content, $check_query = true, $post_type = '', $post_id = false ) {

		// Verify that we are inside a post, page or cpt.
		if ( $check_query ) {
			if ( ! is_singular() || is_attachment() || is_feed() ) {
				return $content;}
		}

		/**
		 * If the $post_id is not set means that we are in the loop and can be
		 * retrieved with get_the_ID().
		 */
		if ( false === $post_id ) {
			$post_id = get_the_ID(); }

		// Get the permalink.
		$post_permalink = get_permalink( $post_id );

		/**
		 * Verify with the "Enable AIL" post meta data or ( if the meta data is
		 * not present ) verify through the "Default Enable AIL" option if the
		 * autolinks should be applied to this post
		 */
		$enable_ail = get_post_meta( $post_id, '_daim_enable_ail', true );
		if ( 0 === strlen( trim( $enable_ail ) ) ) {
			$enable_ail = get_option( $this->get( 'slug' ) . '_default_enable_ail_on_post' );
		}
		if ( 0 === intval( $enable_ail, 10 ) ) {
			return $content;}

		// Initialize properties.
		$this->ail_id  = 0;
		$this->ail_a   = array();
		$this->post_id = $post_id;

		// get the max number of autolinks allowed per post.
		$this->max_number_autolinks_per_post = $this->get_max_number_autolinks_per_post( $this->post_id, $content );

		// Save the "Same URL Limit" as a class property.
		$this->same_url_limit = intval( get_option( $this->get( 'slug' ) . '_same_url_limit' ), 10 );

		// Protect the tags and the commented HTML with protected blocks.
		$content = $this->apply_protected_blocks( $content );

		// Initialize the counter of the autolinks applied.
		$total_autolink_applied = 0;

		// Get an array with the autolinks from the db table.
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$autolinks = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}daim_autolinks ORDER BY priority DESC",
			ARRAY_A
		);

		/*
		 * To avoid additional database requests for each autolink in preg_replace_callback_2() save the data of the
		 * autolink in an array that uses the "autolink_id" as its index.
		 */
		$this->autolinks_ca = $this->save_autolinks_in_custom_array( $autolinks );

		// Apply the Random Prioritization if enabled.
		if ( intval( get_option( $this->get( 'slug' ) . '_random_prioritization' ), 10 ) === 1 ) {
			$autolinks = $this->apply_random_prioritization( $autolinks, $post_id );
		}

		// Cycle through all the defined autolinks.
		foreach ( $autolinks as $key => $autolink ) {

			// Save this autolink as a class property.
			$this->parsed_autolink = $autolink;

			/**
			 * Self AIL
			 *
			 * If the "Ignore Self AIL" option is set to true, do not apply the autolinks that have, as a target, the
			 * post where they should be applied
			 *
			 * Compare $autolink['url'] with the permalink ( with the get_home_url() removed ),
			 * if the comparison returns true ( which means that the autolink url and the current url are the same ) do
			 * not apply the autolink
			 */
			if ( 1 === intval( get_option( $this->get( 'slug' ) . '_ignore_self_ail' ), 10 ) ) {
				$home_url_length = abs( strlen( get_home_url() ) );
				if ( substr( $post_permalink, $home_url_length ) === $autolink['url'] ) {
					continue;}
			}

			/**
			 * If $post_type is not empty means that we are adding the autolinks through the back-end, in this case set
			 * the $this->parsed_post_type property with the $post_type variable.
			 *
			 * If $post_type is empty means that we are in the loop and the post type can be retrieved with the
			 * get_post_type() function.
			 */
			if ( '' !== $post_type ) {
				$this->parsed_post_type = $post_type;
			} else {
				$this->parsed_post_type = get_post_type();
			}

			// Get the list of post types where the autolinks should be applied.
			$post_types_a = maybe_unserialize( $autolink['activate_post_types'] );

			// Verify the post type.
			if ( ! is_array( $post_types_a ) || false === in_array( $this->parsed_post_type, $post_types_a, true ) ) {
				continue;
			}

			/**
			 * If the term group is not set:
			 *
			 * - Check if the post is compliant by verifying categories and tags
			 *
			 * If the term group is set:
			 *
			 * - Check if the post is compliant by verifying the term group
			 */
			if ( intval( $autolink['term_group_id'], 10 ) === 0 ) {

				/**
				 * Verify categories and tags only in the "post" post type or in all the posts. This verification is based
				 * on the value of the $categories_and_tags_verification option.
				 *
				 * - If $categories_and_tags_verification is equal to "any" verify the presence of the selected categories
				 * and tags in any post type.
				 * - If $categories_and_tags_verification is equal to "post" verify the presence of the selected categories
				 * and tags only in the "post" post type.
				 */
				$categories_and_tags_verification = get_option( $this->get( 'slug' ) . '_categories_and_tags_verification' );
				if ( ( 'any' === $categories_and_tags_verification || 'post' === $this->parsed_post_type ) &&
					( ! $this->is_compliant_with_categories( $this->post_id, $autolink ) ||
						! $this->is_compliant_with_tags( $this->post_id, $autolink ) ) ) {
					continue;
				}
			} elseif ( ! $this->is_compliant_with_term_group( $this->post_id, $autolink ) ) {

				// Do not proceed with the application of the autolink if this post is not compliant with the term group.
				continue;

			}

			// Get the max number of autolinks per keyword.
			$max_number_autolinks_per_keyword = $autolink['max_number_autolinks'];

			// Apply a case insensitive search if the case_insensitive_flag is selected.
			if ( $autolink['case_insensitive_search'] ) {
				$modifier = 'iu';// Enable case sensitive and unicode modifier.
			} else {
				$modifier = 'u';// enable unicode modifier.
			}

			$ail_temp = array();

			// Find the left boundary.
			switch ( $autolink['string_before'] ) {
				case 1:
					$string_before = '\b';
					break;

				case 2:
					$string_before = ' ';
					break;

				case 3:
					$string_before = ',';
					break;

				case 4:
					$string_before = '\.';
					break;

				case 5:
					$string_before = '';
					break;
			}

			// Find the right boundary.
			switch ( $autolink['string_after'] ) {
				case 1:
					$string_after = '\b';
					break;

				case 2:
					$string_after = ' ';
					break;

				case 3:
					$string_after = ',';
					break;

				case 4:
					$string_after = '\.';
					break;

				case 5:
					$string_after = '';
					break;
			}

			// Escape regex characters and the '/' regex delimiter.
			$autolink_keyword        = preg_quote( stripslashes( $autolink['keyword'] ), '/' );
			$autolink_keyword_before = preg_quote( $autolink['keyword_before'], '/' );
			$autolink_keyword_after  = preg_quote( $autolink['keyword_after'], '/' );

			/**
			 * Step 1: "The creation of temporary identifiers of the sostitutions"
			 * Replace all the matches with the [ail]ID[/ail] string, where the
			 * ID is the identifier of the sostitution.
			 * The ID is also used as the index of the $this->ail_a temporary array
			 * used to store information about all the sostutions.
			 * This array will be later used in "Step 2" to replace the
			 * [ail]ID[/ail] string with the actual links
			 */
			$content = preg_replace_callback(
				'/(' . $autolink_keyword_before . ')(' . ( $string_before ) . ')(' . $autolink_keyword . ')(' . ( $string_after ) . ')(' . $autolink_keyword_after . ')/' . $modifier,
				array( $this, 'preg_replace_callback_1' ),
				$content,
				$max_number_autolinks_per_keyword
			);

		}

		/**
		 * Step 2: "The replacement of the temporary string [ail]ID[/ail]"
		 * Replaces the [ail]ID[/ail] matches found in the $content with the
		 * actual links by using the $this->ail_a array to find the identifier of the
		 * sostitutions and by retrieving in the db table "autolinks" ( with the
		 *  "autolink_id" ) additional information about the sostitution.
		 */
		$content = preg_replace_callback(
			'/\[ail\](\d+)\[\/ail\]/',
			array( $this, 'preg_replace_callback_2' ),
			$content
		);

		// Remove the protected blocks.
		$content = $this->remove_protected_blocks( $content );

		return $content;
	}

	/**
	 * Replace the following elements with [pr]ID[/pr]:
	 *
	 * - HTML attributes
	 * - Protected Gutenberg blocks
	 * - Commented HTML
	 * - Protected HTML tags
	 *
	 * The replaced content is saved in the property $pr_a, an array with the ID used in the [pr]ID[/pr] placeholder
	 * as the index.
	 *
	 * @param string $content The unprotected $content.
	 * @return string The $content with applied the protected block.
	 */
	private function apply_protected_blocks( $content ) {

		$this->pb_id = 0;
		$this->pb_a  = array();

		// Protect all the HTML attributes if the "Protect Attributes" option is enabled.
		if ( intval( get_option( $this->get( 'slug' ) . '_protect_attributes' ), 10 ) === 1 ) {

			// Match all the HTML attributes that use double quotes as the attribute value delimiter.
			$content = preg_replace_callback(
				'{
					<[a-z0-9]+    #1 The beginning of any HTML element
					\s+           #2 Optional whitespaces 
					(             #3 Begin a group
					(?:           #4 Begin a non-capturing group	
					\s*           #5 Optional whitespaces
					[a-z0-9-_]+   #6 Match the name of the attribute
					\s*=\s*       #7 Equal may have whitespaces on both sides
					"             #8 Match double quotes
					[^"]*         #9 Any character except double quotes zero or more times
					"             #10 Match double quotes
					\s*           #11 Optional whitespaces 
					|			  #12 Provide an alternative to match attributes without values like for example "itemscope"
					\s*           #13 Optional whitespaces
					[a-z0-9-_]    #14 Match the name of the attribute
					\s*           #15 Optional whitespaces 
					)*            #16 Close the group that matches the complete attribute (attribute name + equal sign + attribute value) and use the * to match multiple groups
					)             #17 Close the main capturing group
					\/?           #18 Match an optional / (used in void elements)
					>             #19 Match the end of any HTML element
                    }ixs',
				array( $this, 'apply_single_protected_block_attributes' ),
				$content
			);

			// Match all the HTML attributes that use single quotes as the attribute value delimiter.
			$content = preg_replace_callback(
				'{
					<[a-z0-9]+    #1 The beginning of any HTML element
					\s+           #2 Optional whitespaces 
					(             #3 Begin a group
					(?:           #4 Begin a non-capturing group
					\s*           #5 Optional whitespaces
					[a-z0-9-_]+   #6 Match the name of the attribute
					\s*=\s*       #7 Equal may have whitespaces on both sides
					\'            #8 Match single quote
					[^\']*        #9 Any character except single quote zero or more times
					\'            #10 Match single quote
					\s*           #11 Optional whitespaces 
					|			  #12 Provide an alternative to match attributes without values like for example "itemscope"
					\s*           #13 Optional whitespaces
					[a-z0-9-_]    #14 Match the name of the attribute
					\s*           #15 Optional whitespaces 
					)*            #16 Close the group that matches the complete attribute (attribute name + equal sign + attribute value) and use the * to match multiple groups
					)             #17 Close the main capturing group
					\/?           #18 Match an optional / (used in void elements)
					>             #19 Match the end of any HTML element
                    }ixs',
				array( $this, 'apply_single_protected_block_attributes' ),
				$content
			);

		}

		// Get the Gutenberg Protected Blocks.
		$protected_gutenberg_blocks   = get_option( $this->get( 'slug' ) . '_protected_gutenberg_blocks' );
		$protected_gutenberg_blocks_a = maybe_unserialize( $protected_gutenberg_blocks );
		if ( ! is_array( $protected_gutenberg_blocks_a ) ) {
			$protected_gutenberg_blocks_a = array();
		}

		// Get the Protected Gutenberg Custom Blocks.
		$protected_gutenberg_custom_blocks   = get_option( $this->get( 'slug' ) . '_protected_gutenberg_custom_blocks' );
		$protected_gutenberg_custom_blocks_a = array_filter(
			explode(
				',',
				str_replace( ' ', '', trim( $protected_gutenberg_custom_blocks ) )
			)
		);

		// Get the Protected Gutenberg Custom Void Blocks.
		$protected_gutenberg_custom_void_blocks   = get_option( $this->get( 'slug' ) . '_protected_gutenberg_custom_void_blocks' );
		$protected_gutenberg_custom_void_blocks_a = array_filter(
			explode(
				',',
				str_replace( ' ', '', trim( $protected_gutenberg_custom_void_blocks ) )
			)
		);

		$protected_gutenberg_blocks_comprehensive_list_a = array_merge(
			$protected_gutenberg_blocks_a,
			$protected_gutenberg_custom_blocks_a,
			$protected_gutenberg_custom_void_blocks_a
		);

		if ( is_array( $protected_gutenberg_blocks_comprehensive_list_a ) ) {

			foreach ( $protected_gutenberg_blocks_comprehensive_list_a as $key => $block ) {

				// Non-Void Blocks.
				if ( 'paragraph' === $block ||
					'image' === $block ||
					'heading' === $block ||
					'gallery' === $block ||
					'list' === $block ||
					'quote' === $block ||
					'audio' === $block ||
					'cover-image' === $block ||
					'subhead' === $block ||
					'video' === $block ||
					'code' === $block ||
					'preformatted' === $block ||
					'pullquote' === $block ||
					'table' === $block ||
					'verse' === $block ||
					'button' === $block ||
					'columns' === $block ||
					'more' === $block ||
					'nextpage' === $block ||
					'separator' === $block ||
					'spacer' === $block ||
					'text-columns' === $block ||
					'shortcode' === $block ||
					'embed' === $block ||
					'html' === $block ||
					'core-embed/twitter' === $block ||
					'core-embed/youtube' === $block ||
					'core-embed/facebook' === $block ||
					'core-embed/instagram' === $block ||
					'core-embed/wordpress' === $block ||
					'core-embed/soundcloud' === $block ||
					'core-embed/spotify' === $block ||
					'core-embed/flickr' === $block ||
					'core-embed/vimeo' === $block ||
					'core-embed/animoto' === $block ||
					'core-embed/cloudup' === $block ||
					'core-embed/collegehumor' === $block ||
					'core-embed/dailymotion' === $block ||
					'core-embed/funnyordie' === $block ||
					'core-embed/hulu' === $block ||
					'core-embed/imgur' === $block ||
					'core-embed/issuu' === $block ||
					'core-embed/kickstarter' === $block ||
					'core-embed/meetup-com' === $block ||
					'core-embed/mixcloud' === $block ||
					'core-embed/photobucket' === $block ||
					'core-embed/polldaddy' === $block ||
					'core-embed/reddit' === $block ||
					'core-embed/reverbnation' === $block ||
					'core-embed/screencast' === $block ||
					'core-embed/scribd' === $block ||
					'core-embed/slideshare' === $block ||
					'core-embed/smugmug' === $block ||
					'core-embed/speaker' === $block ||
					'core-embed/ted' === $block ||
					'core-embed/tumblr' === $block ||
					'core-embed/videopress' === $block ||
					'core-embed/wordpress-tv' === $block ||
					in_array( $block, $protected_gutenberg_custom_blocks_a, true )
				) {

					// Escape regex characters and the '/' regex delimiter.
					$block = preg_quote( $block, '/' );

					// Non-Void Blocks Regex.
					$content = preg_replace_callback(
						'/
                    <!--\s+(wp:' . $block . ').*?-->        #1 Gutenberg Block Start
                    .*?                                     #2 Gutenberg Content
                    <!--\s+\/\1\s+-->                       #3 Gutenberg Block End
                    /ixs',
						array( $this, 'apply_single_protected_block' ),
						$content
					);

					// Void Blocks.
				} elseif ( 'categories' === $block ||
							'latest-posts' === $block ||
							in_array( $block, $protected_gutenberg_custom_void_blocks_a, true )
				) {

					// Escape regex characters and the '/' regex delimiter.
					$block = preg_quote( $block, '/' );

					// Void Blocks Regex.
					$content = preg_replace_callback(
						'/
                    <!--\s+wp:' . $block . '.*?\/-->        #1 Void Block
                    /ix',
						array( $this, 'apply_single_protected_block' ),
						$content
					);

				}
			}
		}

		/**
		 * Protect the commented sections, enclosed between <!-- and -->
		 */
		$content = preg_replace_callback(
			'/
            <!--                                #1 Comment Start
            .*?                                 #2 Any character zero or more time with a lazy quantifier
            -->                                 #3 Comment End
            /ix',
			array( $this, 'apply_single_protected_block' ),
			$content
		);

		// Get the list of the protected tags from the "Protected Tags" option.
		$protected_tags_a = $this->get_protected_tags_option();
		foreach ( $protected_tags_a as $key => $single_protected_tag ) {

			/**
			 * Validate the tag. HTML elements all have names that only use
			 * characters in the range 0–9, a–z, and A–Z.
			 */
			if ( preg_match( '/^[0-9a-zA-Z]+$/', $single_protected_tag ) === 1 ) {

				// Make the tag lowercase.
				$single_protected_tag = strtolower( $single_protected_tag );

				/**
				 * Apply different treatment if the tag is a void tag or a
				 * non-void tag.
				 */
				if ( 'area' === $single_protected_tag ||
					'base' === $single_protected_tag ||
					'br' === $single_protected_tag ||
					'col' === $single_protected_tag ||
					'embed' === $single_protected_tag ||
					'hr' === $single_protected_tag ||
					'img' === $single_protected_tag ||
					'input' === $single_protected_tag ||
					'keygen' === $single_protected_tag ||
					'link' === $single_protected_tag ||
					'meta' === $single_protected_tag ||
					'param' === $single_protected_tag ||
					'source' === $single_protected_tag ||
					'track' === $single_protected_tag ||
					'wbr' === $single_protected_tag
				) {

					// Apply the protected block on void tags.
					$content = preg_replace_callback(
						'/                                  
                        <                                   #1 Begin the start-tag
                        (' . $single_protected_tag . ')     #2 The tag name ( captured for the backreference )
                        (\s+[^>]*)?                         #3 Match the rest of the start-tag
                        >                                   #4 End the start-tag
                        /ix',
						array( $this, 'apply_single_protected_block' ),
						$content
					);

				} else {

					// Apply the protected block on non-void tags.
					$content = preg_replace_callback(
						'/
                        <                                   #1 Begin the start-tag
                        (' . $single_protected_tag . ')     #2 The tag name ( captured for the backreference )
                        (\s+[^>]*)?                         #3 Match the rest of the start-tag
                        >                                   #4 End the start-tag
                        .*?                                 #5 The element content ( with the "s" modifier the dot matches also the new lines )
                        <\/\1\s*>                           #6 The end-tag with a backreference to the tag name ( \1 ) and optional white-spaces before the closing >
                        /ixs',
						array( $this, 'apply_single_protected_block' ),
						$content
					);

				}
			}
		}

		return $content;
	}

	/**
	 * This method is in multiple preg_replace_callback located in the
	 * apply_protected_blocks() method.
	 *
	 * What it does is:
	 * 1 - save the match in the $pb_a array
	 * 2 - return the protected block with the related identifier ( [pb]ID[/pb] )
	 *
	 * @param array $m An array with at index 0 the complete match and at index 1 the capture group.
	 * @return string
	 */
	private function apply_single_protected_block( $m ) {

		// Save the match in the $pb_a array.
		++$this->pb_id;
		$this->pb_a[ $this->pb_id ] = $m[0];

		/**
		 * Replace the tag/URL with the protected block and the
		 * index of the $pb_a array as the identifier.
		 */
		return '[pb]' . $this->pb_id . '[/pb]';
	}

	/**
	 * This method is used by a preg_replace_callback located in the apply_protected_blocks() method.
	 *
	 * Specifically, this method is used to apply a protected block on the matched HTML attributes.
	 *
	 * What it does:
	 *
	 * 1 - Saves the match in the $pb_a array
	 * 2 - Replaces the matched HTML attributes with a protected blocks
	 * 2 - Returns the modified HTML
	 *
	 * @param array $m An array with at index 0 the complete match and at index 1 the first capturing group (one or more
	 * HTML attributes).
	 * @return string
	 */
	private function apply_single_protected_block_attributes( $m ) {

		// Save the match in the $pb_a array.
		++$this->pb_id;
		$this->pb_a[ $this->pb_id ] = $m[1];

		// Replace the matched attribute with the protected block and return it.
		return str_replace( $m[1], '[pb]' . $this->pb_id . '[/pb]', $m[0] );
	}

	/**
	 * Replace the block [pr]ID[/pr] with the related tags found in the
	 * $pb_a property.
	 *
	 * @param $content string The $content with applied the protected block
	 * return string The unprotected content.
	 */
	private function remove_protected_blocks( $content ) {

		$content = preg_replace_callback(
			'/\[pb\](\d+)\[\/pb\]/',
			array( $this, 'preg_replace_callback_3' ),
			$content
		);

		return $content;
	}

	/**
	 * Calculate the link juice of a links based on the given parameters.
	 *
	 * @param string $post_content_with_autolinks The post content (with autolinks applied).
	 * @param int    $post_id The post id.
	 * @param int    $link_postition The position of the link in the string (the line where the link string starts).
	 * @return int The link juice of the link.
	 */
	public function calculate_link_juice( $post_content_with_autolinks, $post_id, $link_position ) {

		// Get the SEO power of the post.
		$seo_power = get_post_meta( $post_id, '_daim_seo_power', true );
		if ( 0 === strlen( trim( $seo_power ) ) ) {
			$seo_power = (int) get_option( $this->get( 'slug' ) . '_default_seo_power' );}

		/**
		 * Divide the SEO power for the total number of links ( all the links,
		 * external and internal are considered ).
		 */
		$juice_per_link = $seo_power / $this->get_number_of_links( $post_content_with_autolinks );

		/**
		 * Calculate the index of the link on the post ( example 1 for the first
		 * link or 3 for the third link )
		 * A regular expression that counts the links on a string that starts
		 * from the beginning of the post and ends at the $link_position is used
		 */
		$post_content_before_the_link = substr( $post_content_with_autolinks, 0, $link_position );
		$number_of_links_before       = $this->get_number_of_links( $post_content_before_the_link );

		/**
		 * Remove a percentage of the $juice_value based on the number of links
		 * before this one.
		 */
		$penality_per_position_percentage = (int) get_option( $this->get( 'slug' ) . '_penality_per_position_percentage' );
		$link_juice                       = $juice_per_link - ( ( $juice_per_link / 100 * $penality_per_position_percentage ) * $number_of_links_before );

		// Return the link juice or 0 if the calculated link juice is negative.
		if ( $link_juice < 0 ) {
			$link_juice = 0;}
		return $link_juice;
	}

	/**
	 * Get the total number of links ( any kind of link: internal, external,
	 * nofollow, dofollow ) available in the provided string.
	 *
	 * @param $s The string on which the number of links should be counted
	 * @return int The number of links found on the string
	 */
	public function get_number_of_links( $s ) {

		// Remove the HTML comments.
		$s = $this->remove_html_comments( $s );

		// Remove script tags.
		$s = $this->remove_script_tags( $s );

		$num_matches = preg_match_all(
			'{<a                                #1 Begin the element a start-tag
            [^>]+                               #2 Any character except > at least one time
            href\s*=\s*                         #3 Equal may have whitespaces on both sides
            ([\'"]?)                            #4 Match double quotes, single quote or no quote ( captured for the backreference \1 )
            [^\'">\s]+                          #5 The site URL
            \1                                  #6 Backreference that matches the href value delimiter matched at line 4     
            [^>]*                               #7 Any character except > zero or more times
            >                                   #8 End of the start-tag
            .*?                                 #9 Link text or nested tags. After the dot ( enclose in parenthesis ) negative lookbehinds can be applied to avoid specific stuff inside the link text or nested tags. Example with single negative lookbehind (.(?<!word1))*? Example with multiple negative lookbehind (.(?<!word1)(?<!word2)(?<!word3))*?
            <\/a\s*>                            #10 Element a end-tag with optional white-spaces characters before the >
            }ix',
			$s,
			$matches
		);

		return $num_matches;
	}

	/**
	 * Given a link returns it with the anchor link removed.
	 *
	 * @param string $s The link that should be analyzed.
	 * @return string The link with the link anchor removed.
	 */
	public function remove_link_to_anchor( $s ) {

		$s = preg_replace_callback(
			'/([^#]+)               #Everything except # one or more times ( captured )
            \#.*                    #The # with anything the follows zero or more times
            /ux',
			array( $this, 'preg_replace_callback_4' ),
			$s
		);

		return $s;
	}

	/**
	 * Given a URL the parameter part is removed.
	 *
	 * @param string $s The URL that should be analyzed.
	 * @return string $s The URL.
	 */
	public function remove_url_parameters( $s ) {

		$s = preg_replace_callback(
			'/([^?]+)               #Everything except ? one or more time ( captured )
            \?.*                    #The ? with anything the follows zero or more times
            /ux',
			array( $this, 'preg_replace_callback_5' ),
			$s
		);

		return $s;
	}

	/**
	 * Callback of the preg_replace_callback() function.
	 *
	 * This callback is used to avoid an anonimus function as a parameter of the
	 * preg_replace_callback() function for PHP backward compatibility
	 *
	 * Look for uses of preg_replace_callback_1 to find which
	 * preg_replace_callback() function is actually using this callback
	 *
	 * @param array $m Todo.
	 */
	public function preg_replace_callback_1( $m ) {

		/**
		 * Do not apply the replacement ( and return the matches string )
		 * if the max number of autolinks per post has been reached.
		 */
		if ( $this->max_number_autolinks_per_post == $this->ail_id ||
			$this->same_url_limit_reached() ) {
			/**
			 * Return the captured text with related left and right boundaries
			 * to not alter the content.
			 */
			return $m[1] . $m[2] . $m[3] . $m[4] . $m[5];
		} else {
			++$this->ail_id;
			$this->ail_a[ $this->ail_id ]['autolink_id']    = $this->parsed_autolink['id'];
			$this->ail_a[ $this->ail_id ]['url']            = $this->parsed_autolink['url'];
			$this->ail_a[ $this->ail_id ]['text']           = $m[3];
			$this->ail_a[ $this->ail_id ]['left_boundary']  = $m[2];
			$this->ail_a[ $this->ail_id ]['right_boundary'] = $m[4];
			$this->ail_a[ $this->ail_id ]['keyword_before'] = $m[1];
			$this->ail_a[ $this->ail_id ]['keyword_after']  = $m[5];

			return '[ail]' . $this->ail_id . '[/ail]';
		}
	}

	/**
	 * Callback of the preg_replace_callback() function.
	 *
	 * This callback is used to avoid an anonimus function as a parameter of the
	 * preg_replace_callback() function for PHP backward compatibility.
	 *
	 * Look for uses of preg_replace_callback_2 to find which
	 * preg_replace_callback() function is actually using this callback.
	 */
	public function preg_replace_callback_2( $m ) {

		/**
		 * Find the related text of the link from the $this->ail_a multidimensional
		 * array by using the match as the index.
		 */
		$link_text = $this->ail_a[ $m[1] ]['text'];

		/**
		 * Get the left and right boundaries.
		 */
		$left_boundary  = $this->ail_a[ $m[1] ]['left_boundary'];
		$right_boundary = $this->ail_a[ $m[1] ]['right_boundary'];

		// Get the keyword_before and keyword_after.
		$keyword_before = $this->ail_a[ $m[1] ]['keyword_before'];
		$keyword_after  = $this->ail_a[ $m[1] ]['keyword_after'];

		// Get the autolink_id.
		$autolink_id = $this->ail_a[ $m[1] ]['autolink_id'];

		// get the "url" value.
		$link_url = $this->autolinks_ca[ $autolink_id ]['url'];

		// Generate the title attribute HTML if the "title" field is not empty.
		if ( strlen( trim( $this->autolinks_ca[ $autolink_id ]['title'] ) ) > 0 ) {
			$title_attribute = 'title="' . esc_attr( stripslashes( $this->autolinks_ca[ $autolink_id ]['title'] ) ) . '"';
		} else {
			$title_attribute = '';
		}

		// Get the "open_new_tab" value.
		if ( 1 === intval( $this->autolinks_ca[ $autolink_id ]['open_new_tab'], 10 ) ) {
			$open_new_tab = 'target="_blank"';
		} else {
			$open_new_tab = 'target="_self"';}

		// Get the "use_nofollow" value.
		if ( 1 === intval( $this->autolinks_ca[ $autolink_id ]['use_nofollow'], 10 ) ) {
			$use_nofollow = 'rel="nofollow"';
		} else {
			$use_nofollow = '';}

		// Return the actual link.
		return $keyword_before . $left_boundary . '<a data-ail="' . $this->post_id . '" ' . $open_new_tab . ' ' . $use_nofollow . ' href="' . esc_url( get_home_url() . $link_url ) . '" ' . $title_attribute . '>' . $link_text . '</a>' . $right_boundary . $keyword_after;
	}

	/**
	 * Callback of the preg_replace_callback() function.
	 *
	 * This callback is used to avoid an anonimus function as a parameter of the
	 * preg_replace_callback() function for PHP backward compatibility.
	 *
	 * Look for uses of preg_replace_callback_3 to find which
	 * preg_replace_callback() function is actually using this callback.
	 */
	public function preg_replace_callback_3( $m ) {

		/**
		 * The presence of nested protected blocks is verified. If a protected
		 * block is inside the content of a protected block the
		 * remove_protected_block() method is applied recursively until there
		 * are no protected blocks
		 */
		$html           = $this->pb_a[ $m[1] ];
		$recursion_ends = false;

		do {

			/**
			 * If there are no protected blocks in content of the protected
			 * block end the recursion, otherwise apply remove_protected_block()
			 * again.
			 */
			if ( preg_match( '/\[pb\](\d+)\[\/pb\]/', $html ) == 0 ) {
				$recursion_ends = true;
			} else {
				$html = $this->remove_protected_blocks( $html );
			}
		} while ( false === $recursion_ends );

		return $html;
	}

	/**
	 * Callback of the preg_replace_callback() function
	 *
	 * This callback is used to avoid an anonimus function as a parameter of the
	 * preg_replace_callback() function for PHP backward compatibility
	 *
	 * Look for uses of preg_replace_callback_4 to find which
	 * preg_replace_callback() function is actually using this callback
	 *
	 * @param array $m Todo.
	 */
	public function preg_replace_callback_4( $m ) {

		return $m[1];
	}

	/**
	 * Callback of the preg_replace_callback() function
	 *
	 * This callback is used to avoid an anonimus function as a parameter of the
	 * preg_replace_callback() function for PHP backward compatibility
	 *
	 * Look for uses of preg_replace_callback_5 to find which
	 * preg_replace_callback() function is actually using this callback
	 *
	 * @param array $m Todo.
	 * @return mixed
	 */
	public function preg_replace_callback_5( $m ) {

		return $m[1];
	}

	/**
	 * Callback of the preg_replace_callback() function.
	 *
	 * This callback is used to avoid an anonimus function as a parameter of the
	 * preg_replace_callback() function for PHP backward compatibility.
	 *
	 * Look for uses of preg_replace_callback_6 to find which
	 * preg_replace_callback() function is actually using this callback.
	 */
	public function preg_replace_callback_6( $m ) {

		// replace '<a "' with '<a data-mil="[post-id]"' and return.
		return '<a data-mil="' . get_the_ID() . '" ' . mb_substr( $m[0], 3 );
	}

	/**
	 * Callback of the usort() function.
	 *
	 * This callback is used to avoid an anonimus function as a parameter of the
	 * usort() function for PHP backward compatibility.
	 *
	 * Look for uses of usort_callback_1 to find which usort() function is
	 * actually using this callback.
	 *
	 * @param array $a The first array to compare.
	 * @param array $b The second array to compare.
	 */
	public function usort_callback_1( $a, $b ) {

		return $b['score'] - $a['score'];
	}

	/**
	 * Remove the HTML comment ( comment enclosed between <!-- and --> )
	 *
	 * @param $content The HTML with the comments
	 * @return string The HTML without the comments
	 */
	public function remove_html_comments( $content ) {

		$content = preg_replace(
			'/
            <!--                                #1 Comment Start
            .*?                                 #2 Any character zero or more time with a lazy quantifier
            -->                                 #3 Comment End
            /ix',
			'',
			$content
		);

		return $content;
	}

	/**
	 * Remove the script tags
	 *
	 * @param string $content The HTML with the script tags.
	 * @return string The HTML without the script tags
	 */
	public function remove_script_tags( $content ) {

		$content = preg_replace(
			'/
            <                                   #1 Begin the start-tag
            script                              #2 The script tag name
            (\s+[^>]*)?                         #3 Match the rest of the start-tag
            >                                   #4 End the start-tag
            .*?                                 #5 The element content ( with the "s" modifier the dot matches also the new lines )
            <\/script\s*>                       #6 The script end-tag with optional white-spaces before the closing >
            /ixs',
			'',
			$content
		);

		return $content;
	}

	/**
	 * Get the number of records available in the "_archive" db table.
	 *
	 * @return int The number of records in the "_archive" db table
	 */
	public function number_of_records_in_archive() {

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}daim_archive" );

		return $total_items;
	}

	/**
	 * Get the number of records available in the "_juice" db table.
	 *
	 * @return int The number of records in the "_juice" db table
	 */
	public function number_of_records_in_juice() {

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}daim_juice" );

		return $total_items;
	}

	/**
	 * Check if the all the URLs in the "_http_status" db table have been checked and that there is at least one record.
	 *
	 * @return bool
	 */
	public function complete_http_status_data_exists() {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}daim_http_status" );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$checked_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}daim_http_status WHERE checked = 1" );

		if ( $total_items > 0 && $total_items === $checked_items ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get the number of records available in the "_hits" db table
	 *
	 * @return int The number of records in the "_hits" db table
	 */
	public function number_of_records_in_hits() {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}daim_hits" );

		return $total_items;
	}

	/**
	 * If $needle is present in the $haystack array echos 'selected="selected"'.
	 *
	 * @param array  $data_a The array in which the $needle should be searched.
	 * @param string $needle The string that should be searched in the $haystack array.
	 */
	public function selected_array( $data_a, $needle ) {

		if ( is_array( $data_a ) && in_array( $needle, $data_a, true ) ) {
			return 'selected="selected"';
		}
	}

	/**
	 * If the number of times that the parsed autolink URL ($this->parsed_autolink['url']) is present in the array that
	 * includes the data of the autolinks already applied as temporary identifiers ($this->ail_a) is equal or
	 * higher than the limit estabilished with the "Same URL Limit" option ($this->same_url_limit) True is returned,
	 * otherwise False is returned.
	 *
	 * @return Bool
	 */
	public function same_url_limit_reached() {

		$counter = 0;

		foreach ( $this->ail_a as $key => $value ) {
			if ( $value['url'] === $this->parsed_autolink['url'] ) {
				++$counter;
			}
		}

		if ( $counter >= $this->same_url_limit ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * With versions lower than 1.19 the list of protected block is stored in the "daim_protected_tags" option as a
	 * comma separated list of tags and not as a serialized array.
	 *
	 * This method:
	 *
	 * 1 - Retrieves the "daim_protected_tags" option value
	 * 2 - If the value is a string (pre 1.19) the protected tags are converted to an array
	 * 3 - Returns the array of protected tags
	 *
	 * @return Array
	 */
	public function get_protected_tags_option() {

		$protected_tags_a = array();
		$protected_tags   = get_option( 'daim_protected_tags' );
		if ( is_string( $protected_tags ) ) {
			$protected_tags = str_replace( ' ', '', $protected_tags );
			if ( strlen( $protected_tags ) > 0 ) {
				$protected_tags_a = explode( ',', str_replace( ' ', '', $protected_tags ) );
			}
		} else {
			$protected_tags_a = $protected_tags;
		}

		return $protected_tags_a;
	}

	/**
	 * Returns the maximum number of AIL allowed per post by using the method explained below.
	 *
	 * If the "General Limit Mode" option is set to "Auto":
	 *
	 * The maximum number of autolinks per post is calculated based on the content length of this post divided for the
	 * value of the "General Limit (Characters per AIL)" option.
	 *
	 * If the "General Limit Mode" option is set to "Manual":
	 *
	 * The maximum number of AIL per post is equal to the value of "General Limit (Max AIL per Post)".
	 *
	 * @param int $post_id The post ID for which the maximum number AIL per post should be calculated.
	 * @return int The maximum number of AIL allowed per post.
	 */
	private function get_max_number_autolinks_per_post( $post_id, $post_content ) {

		/**
		 * Calculate the maximumn umber of AIL that should be applied in the post based on the following options:
		 *
		 * - General Limit Mode
		 * - General Limit (Characters per AIL)
		 * - General Limit (Amount)
		 */
		if ( intval( get_option( $this->get( 'slug' ) . '_general_limit_mode' ), 10 ) === 0 ) {

			// Auto ---------------------------------------------------------------------------------------------------.
			$post_obj                = get_post( $post_id );
			$post_length             = mb_strlen( $post_obj->post_content );
			$characters_per_autolink = intval( get_option( $this->get( 'slug' ) . '_characters_per_autolink' ), 10 );
			$number_of_ail           = intval( $post_length / $characters_per_autolink, 10 );

		} else {

			// Manual -------------------------------------------------------------------------------------------------.
			$number_of_ail = intval( get_option( $this->get( 'slug' ) . '_max_number_autolinks_per_post' ), 10 );

		}

		/**
		 * If the "General Limit (Subtract MIL) option is enabled subtract the number of existing MIL of the post
		 * ($number_of_mil) from the maximum number of AIL that should be applied in the post ($number_of_ail).
		 * Otherwise return the maximum number of AIL that should be applied in the post without further calculations.
		 */
		if ( intval( get_option( 'daim_general_limit_subtract_mil' ), 10 ) === 1 ) {

			$number_of_mil = $this->get_manual_interlinks( $post_content );
			$result        = max( $number_of_ail - $number_of_mil, 0 );
			return intval( $result, 10 );

		} else {

			return $number_of_ail;

		}
	}

	/**
	 * To avoid additional database requests for each autolink in preg_replace_callback_2() save the data of the
	 * autolink in an array that uses the "autolink_id" as its index.
	 *
	 * @param array $autolinks Todo.
	 * @return Array
	 */
	public function save_autolinks_in_custom_array( $autolinks ) {

		$autolinks_ca = array();

		foreach ( $autolinks as $key => $autolink ) {

			$autolinks_ca[ $autolink['id'] ] = $autolink;

		}

		return $autolinks_ca;
	}

	/**
	 * Applies a random order (based on the hash of the post_id and autolink_id) to the autolinks that have the same
	 * priority. This ensures a better distribution of the autolinks.
	 *
	 * @param attay $autolink Todo.
	 * @param int   $post_id Todo.
	 * @return Array
	 */
	public function apply_random_prioritization( $autolinks, $post_id ) {

		// Initialize variables.
		$autolinks_rp1 = array();
		$autolinks_rp2 = array();

		// Move the autolinks array in the new $autolinks_rp1 array, which uses the priority value as its index.
		foreach ( $autolinks as $key => $autolink ) {

			$autolinks_rp1[ $autolink['priority'] ][] = $autolink;

		}

		/**
		 * Apply a random order (based on the hash of the post_id and autolink_id) to the autolinks that have the same
		 * priority.
		 */
		foreach ( $autolinks_rp1 as $key => $autolinks_a ) {

			/**
			 * In each autolink create the new "hash" field which include an hash value based on the post_id and on the
			 * autolink id.
			 */
			foreach ( $autolinks_a as $key2 => $autolink ) {

				/**
				 * Create the hased value. Note that the "-" character is used to avoid situations where the same input
				 * is provided to the md5() function.
				 *
				 * Without the "-" character for example with:
				 *
				 * $post_id = 12 and $autolink['id'] = 34
				 *
				 * We provide the same input of:
				 *
				 * $post_id = 123 and $autolink['id'] = 4
				 *
				 * etc.
				 */
				$hash = hexdec( md5( $post_id . '-' . $autolink['id'] ) );

				/**
				 * Convert all the non-digits to the character "1", this makes the comparison performed in the usort
				 * callback possible.
				 */
				$autolink['hash']     = preg_replace( '/\D/', '1', $hash, -1, $replacement_done );
				$autolinks_a[ $key2 ] = $autolink;

			}

			// Sort $autolinks_a based on the new value of the "hash" field.
			usort(
				$autolinks_a,
				function ( $a, $b ) {

					return $b['hash'] - $a['hash'];
				}
			);

			$autolinks_rp1[ $key ] = $autolinks_a;

		}

		/**
		 * Move the autolinks in the new $autolinks_rp2 array, which is structured like the original array, where the
		 * value of the priority field is stored in the autolink and it's not used as the index of the array that
		 * includes all the autolinks with the same priority.
		 */
		foreach ( $autolinks_rp1 as $key => $autolinks_a ) {

			$autolinks_a_num = count( $autolinks_a );
			for ( $t = 0; $t < $autolinks_a_num; $t++ ) {

				$autolink        = $autolinks_a[ $t ];
				$autolinks_rp2[] = $autolink;

			}
		}

		return $autolinks_rp2;
	}

	/**
	 * Returns true if one or more AIL are using the specified category.
	 *
	 * @param int $category_id The category ID.
	 * @return bool
	 */
	public function category_is_used( $category_id ) {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$total_items = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}daim_autolinks WHERE category_id = %d", $category_id ) );

		if ( $total_items > 0 ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Given the category ID the category name is returned.
	 *
	 * @param int $category_id Todo.
	 * @return String
	 */
	public function get_category_name( $category_id ) {

		if ( intval( $category_id, 10 ) === 0 ) {
			return __( 'None', 'interlinks-manager');
		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$category_obj = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}daim_category WHERE category_id = %d ", $category_id )
		);

		return $category_obj->name;
	}

	/**
	 * Returns true if the category with the specified $category_id exists.
	 *
	 * @param int $category_id Todo.
	 * @return bool
	 */
	public function category_exists( $category_id ) {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$total_items = $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}daim_category WHERE category_id = %d", $category_id )
		);

		if ( $total_items > 0 ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Returns the number of items in the "anchors" database table with the specified "url".
	 *
	 * @param string $url Todo.
	 * @return int
	 */
	public function get_anchors_with_url( $url ) {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$total_items = $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}daim_anchors WHERE url = %s ORDER BY id DESC", $url )
		);

		return intval( $total_items );
	}

	/**
	 * Get an array with the post types with UI except the attachment post type.
	 *
	 * @return Array
	 */
	public function get_post_types_with_ui() {

		// Get all the post types with UI.
		$args               = array(
			'public'  => true,
			'show_ui' => true,
		);
		$post_types_with_ui = get_post_types( $args );

		// Remove the attachment post type.
		unset( $post_types_with_ui['attachment'] );

		// Replace the associative index with a numeric index.
		$temp_array = array();
		foreach ( $post_types_with_ui as $key => $value ) {
			$temp_array[] = $value;
		}
		$post_types_with_ui = $temp_array;

		return $post_types_with_ui;
	}

	/**
	 * Returns True if the post has the categories required by the autolink or if the autolink doesn't require any
	 * specific category.
	 *
	 * @param int   $post_id The post ID.
	 * @param array $autolink The autolink data.
	 *
	 * @return Bool
	 */
	private function is_compliant_with_categories( $post_id, $autolink ) {

		$autolink_categories_a = maybe_unserialize( $autolink['categories'] );
		$post_categories       = get_the_terms( $post_id, 'category' );
		$category_found        = false;

		// If no categories are specified return true.
		if ( ! is_array( $autolink_categories_a ) ) {
			return true;
		}

		// If the post has no categories return false.
		if ( ! is_array( $post_categories ) ) {
			return false;
		}

		/**
		 * Do not proceed with the application of the autolink if in this post no categories included in
		 * $autolink_categories_a are available.
		 */
		foreach ( $post_categories as $key => $post_single_category ) {
			if ( in_array( $post_single_category->term_id, $autolink_categories_a ) ) {
				$category_found = true;
			}
		}

		if ( $category_found ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Returns True if the post has the tags required by the autolink or if the autolink doesn't require any specific
	 * tag.
	 *
	 * @param int   $post_id The post ID.
	 * @param array $autolink The autolink data.
	 *
	 * @return Bool
	 */
	private function is_compliant_with_tags( $post_id, $autolink ) {

		$autolink_tags_a = maybe_unserialize( $autolink['tags'] );
		$post_tags       = get_the_terms( $post_id, 'post_tag' );
		$tag_found       = false;

		// If no tags are specified return true.
		if ( ! is_array( $autolink_tags_a ) ) {
			return true;
		}

		if ( false !== $post_tags ) {

			/**
			 * Do not proceed with the application of the autolink if this post has at least one tag but no tags
			 * included in $autolink_tags_a are available.
			 */
			foreach ( $post_tags as $key => $post_single_tag ) {
				if ( in_array( $post_single_tag->term_id, $autolink_tags_a ) ) {
					$tag_found = true;
				}
			}
			if ( ! $tag_found ) {
				return false;
			}
		} else {

			// Do not proceed with the application of the autolink if this post has no tags associated.
			return false;

		}

		return true;
	}

	/**
	 * Verifies if the post includes at least one term included in the term group associated with the autolink.
	 *
	 * In the following conditions True is returned:
	 *
	 * - When a term group is not set
	 * - When the post has at least one term present in the term group
	 *
	 * @param int   $post_id The post ID.
	 * @param array $autolink The autolink data.
	 *
	 * @return Bool
	 */
	private function is_compliant_with_term_group( $post_id, $autolink ) {

		$supported_terms = intval( get_option( $this->get( 'slug' ) . '_supported_terms' ), 10 );

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$term_group_obj = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}daim_term_group WHERE term_group_id = %d ",
				$autolink['term_group_id']
			)
		);

		if ( null !== $term_group_obj ) {

			for ( $i = 1; $i <= $supported_terms; $i++ ) {

				$post_type = $term_group_obj->{'post_type_' . $i};
				$taxonomy  = $term_group_obj->{'taxonomy_' . $i};
				$term      = $term_group_obj->{'term_' . $i};

				// Verify post type, taxonomy and term as specified in the term group.
				if ( $post_type === $this->parsed_post_type && has_term( $term, $taxonomy, $post_id ) ) {
					return true;
				}
			}

			return false;

		}

		return true;
	}

	/**
	 * Fires after a term is deleted from the database and the cache is cleaned.
	 *
	 * The following tasks are performed:
	 *
	 * Part 1 - Deletes the $term_id found in the categories field of the autolinks
	 * Part 2 - Deletes the $term_id found in the tags field of the autolinks
	 * Part 3 - Deletes the $term_id found in the 50 term_[n] fields of the term groups
	 *
	 * @param int    $term_id The term ID.
	 * @param int    $term_taxonomy_id The term taxonomy ID.
	 * @param string $taxonomy_slug The taxonomy slug.
	 *
	 * @return void
	 */
	public function delete_term_action( $term_id, $term_taxonomy_id, $taxonomy_slug ) {

		// Part 1-2 ---------------------------------------------------------------------------------------------------.

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$autolink_a = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}daim_autolinks ORDER BY id ASC", ARRAY_A );

		if ( null !== $autolink_a && count( $autolink_a ) > 0 ) {

			foreach ( $autolink_a as $key1 => $autolink ) {

				// Delete the term in the categories field of the autolinks.
				$category_term_a = maybe_unserialize( $autolink['categories'] );
				if ( is_array( $category_term_a ) && count( $category_term_a ) > 0 ) {
					foreach ( $category_term_a as $key2 => $category_term ) {
						if ( intval( $category_term, 10 ) === $term_id ) {
							unset( $category_term_a[ $key2 ] );
						}
					}
				}
				$category_term_a_serialized = maybe_serialize( $category_term_a );

				// Delete the term in the tags field of the autolinks.
				$tag_term_a = maybe_unserialize( $autolink['tags'] );
				if ( is_array( $tag_term_a ) && count( $tag_term_a ) > 0 ) {
					foreach ( $tag_term_a as $key2 => $tag_term ) {
						if ( intval( $tag_term, 10 ) === $term_id ) {
							unset( $tag_term_a[ $key2 ] );
						}
					}
				}
				$tag_term_a_serialized = maybe_serialize( $tag_term_a );

				// Update the record of the database if $categories or $tags are changed.
				if ( $autolink['categories'] !== $category_term_a_serialized ||
					$autolink['tags'] !== $tag_term_a_serialized ) {

					// phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$wpdb->query(
						$wpdb->prepare(
							"UPDATE {$wpdb->prefix}daim_autolinks SET 
                        categories = %s,
                        tags = %s
                        WHERE id = %d",
							$category_term_a_serialized,
							$tag_term_a_serialized,
							$autolink['id']
						)
					);

				}
			}
		}

		// Part 3 -----------------------------------------------------------------------------------------------------.

		// Delete the term in all the 50 term_[n] field of the term groups.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$term_group_a = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}daim_term_group ORDER BY term_group_id ASC", ARRAY_A );

		if ( null !== $term_group_a && count( $term_group_a ) > 0 ) {

			foreach ( $term_group_a as $key => $term_group ) {

				$no_terms = true;
				for ( $i = 1; $i <= 50; $i++ ) {

					if ( intval( $term_group[ 'term_' . $i ], 10 ) === $term_id ) {
						$term_group[ 'post_type_' . $i ] = '';
						$term_group[ 'taxonomy_' . $i ]  = '';
						$term_group[ 'term_' . $i ]      = 0;
					}

					if ( intval( $term_group[ 'term_' . $i ], 10 ) !== 0 ) {
						$no_terms = false;
					}
				}

				/**
				 * If all the terms of the term group are empty delete the term group and reset the association between
				 * autolinks and this term group. If there are terms in the term group update the term group.
				 */
				if ( $no_terms ) {

					// Delete the term group.

					// phpcs:disable WordPress.DB.DirectDatabaseQuery
					$query_result = $wpdb->query(
						$wpdb->prepare(
							"DELETE FROM {$wpdb->prefix}daim_term_group WHERE term_group_id = %d ",
							$term_group['term_group_id']
						)
					);
					// phpcs:enable

					// If the term group is used reset the association between the autolinks and this term group.
					if ( $this->term_group_is_used( $term_group['term_group_id'] ) ) {

						// Reset the association between the autolinks and this term group.
						$safe_sql = $wpdb->prepare(
							"UPDATE {$wpdb->prefix}daim_term_group SET 
                                    term_group_id = 0,
                                    WHERE term_group_id = %d",
							$term_group['term_group_id']
						);

					}
				} else {

					// Update the term group.

					$query_part = '';
					for ( $i = 1; $i <= 50; $i++ ) {
						$query_part .= $wpdb->prepare('%i = %s,', 'post_type_' . $i, $term_group['post_type_'. $i] );
						$query_part .= $wpdb->prepare('%i = %s,', 'taxonomy_' . $i, $term_group['taxonomy_'. $i] );
						$query_part .= $wpdb->prepare('%i = %s', 'term_' . $i, $term_group['term_'. $i] );
						if ( 50 !== $i ) {
							$query_part .= ',';
						}
					}

					// Update the database.
					global $wpdb;

					// phpcs:disable WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $query_part is already prepared.
					$query_result = $wpdb->query(
						$wpdb->prepare(
							"UPDATE {$wpdb->prefix}daim_term_group SET
                        $query_part
                        WHERE term_group_id = %d",
							$term_group['term_group_id']
						)
					);
					// phpcs:enable

				}
			}
		}
	}

	/**
	 * Make the database data compatible with the new plugin versions.
	 *
	 * Only Task 1 is available at the moment. Use Task 2, Task 3, etc. for additional operation on the database.
	 */
	public function convert_database_data() {

		/**
		 * Task 1:
		 *
		 * Convert all the values of the category field of the autolinks saved as a comma separated list of values
		 * to an array.
		 *
		 * Note that the category field of the autolinks is saved serialized starting from version 1.26
		 */
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$autolink_a = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}daim_autolinks", ARRAY_A );

		// If there are data generate the csv header and content.
		if ( count( $autolink_a ) > 0 ) {

			foreach ( $autolink_a as $autolink ) {

				if ( strlen( $autolink['activate_post_types'] ) === 0 || is_serialized( $autolink['activate_post_types'] ) ) {
					continue;
				}

				$activate_post_types = preg_replace( '/\s+/', '', $autolink['activate_post_types'] );
				$post_type_a         = explode( ',', $activate_post_types );

				if ( is_array( $post_type_a ) ) {

					$post_type_serialized = maybe_serialize( $post_type_a );
					$autolink_id          = intval( $autolink['id'], 10 );

					// phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$wpdb->query(
						$wpdb->prepare(
							"UPDATE {$wpdb->prefix}daim_autolinks SET 
                        activate_post_types = %s
                        WHERE id = %d",
							$post_type_serialized,
							$autolink_id
						)
					);

				}
			}
		}
	}

	/**
	 * Make the database data compatible with the new plugin versions.
	 *
	 * Only Task 1 is available at the moment. Use Task 2, Task 3, etc. for additional operation on the database.
	 */
	public function convert_options_data() {

		/**
		 * Task 1:
		 *
		 * Convert all the options that include list of post types saved as a comma separated list of values to an
		 * array.
		 *
		 * Note that these options are saved serialized starting from version 1.26
		 */
		$option_name_a = array(
			'_default_activate_post_types',
			'_suggestions_pool_post_types',
			'_dashboard_post_types',
			'_juice_post_types',
			'_interlinks_options_post_types',
			'_interlinks_optimization_post_types',
			'_interlinks_suggestions_post_types',
		);

		foreach ( $option_name_a as $option_name ) {

			$option_value = get_option( $this->get( 'slug' ) . $option_name );

			if ( false === ($option_value) || is_array( $option_value ) || ( is_string( $option_value ) && strlen( $option_value ) === 0 ) ) {
				continue;
			}

			$activate_post_types = preg_replace( '/\s+/', '', $option_value );
			$post_type_a         = explode( ',', $activate_post_types );

			if ( is_array( $post_type_a ) ) {
				update_option( $this->get( 'slug' ) . $option_name, $post_type_a );
			}
		}
	}

	/**
	 * Returns true if the term group with the specified $term_group_id exists.
	 *
	 * @param int $term_group_id The term group ID.
	 * @return bool
	 */
	public function term_group_exists( $term_group_id ) {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$total_items = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}daim_term_group WHERE term_group_id = %d", $term_group_id ) );

		if ( $total_items > 0 ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Returns true if one or more autolinks are using the specified term group.
	 *
	 * @param int $term_group_id The ID of the term group.
	 *
	 * @return bool
	 */
	public function term_group_is_used( $term_group_id ) {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$total_items = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}daim_autolinks WHERE term_group_id = %d", $term_group_id ) );

		if ( $total_items > 0 ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Returns true if there are exportable data or false if here are no exportable data.
	 */
	public function exportable_data_exists() {

		$exportable_data = false;
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}daim_autolinks" );
		if ( $total_items > 0 ) {
			$exportable_data = true;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}daim_category" );
		if ( $total_items > 0 ) {
			$exportable_data = true;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}daim_term_group" );
		if ( $total_items > 0 ) {
			$exportable_data = true;
		}

		return $exportable_data;
	}

	/**
	 * Generates the XML version of the data of the table.
	 *
	 * @param string $db_table_name The name of the db table without the prefix.
	 * @param string $db_table_primary_key The name of the primary key of the table.
	 *
	 * @return String The XML version of the data of the db table
	 */
	public function convert_db_table_to_xml( $db_table_name, $db_table_primary_key ) {

		// Get the data from the db table.
		global $wpdb;
		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$data_a = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM %i ORDER BY %i ASC',
				$wpdb->prefix . 'daim_' . $db_table_name,
				$db_table_primary_key
			),
			ARRAY_A
		);
		// phpcs:enable

		// Generate the data of the db table.
		foreach ( $data_a as $record ) {

			echo '<' . esc_attr( $db_table_name ) . '>';

			// Get all the indexes of the $data array.
			$record_keys = array_keys( $record );

			// Cycle through all the indexes of the single record and create all the XML tags.
			foreach ( $record_keys as $key ) {
				echo '<' . esc_attr( $key ) . '>' . esc_attr( $record[ $key ] ) . '</' . esc_attr( $key ) . '>';
			}

			echo '</' . esc_attr( $db_table_name ) . '>';

		}
	}

	/**
	 * Objects as a value are set to empty strings. This prevents to generate notices with the methods of the wpdb class.
	 *
	 * @param array $data An array which includes objects that should be converted to an empty strings.
	 * @return string An array where the objects have been replaced with empty strings.
	 */
	public function replace_objects_with_empty_strings( $data ) {

		foreach ( $data as $key => $value ) {
			if ( gettype( $value ) === 'object' ) {
				$data[ $key ] = '';
			}
		}

		return $data;
	}

	/**
	 * Set the PHP "Max Execution Time" and "Memory Limit" based on the values defined in the options.
	 */
	public function set_met_and_ml() {

		/**
		 * Set the custom "Max Execution Time Value" defined in the options if
		 * the 'Set Max Execution Time' option is set to "Yes"
		 */
		if ( intval( get_option( $this->get( 'slug' ) . '_set_max_execution_time' ), 10 ) === 1 ) {
			ini_set( 'max_execution_time', intval( get_option( 'daim_max_execution_time_value' ), 10 ) );
		}

		/**
		 * Set the custom "Memory Limit Value" ( in megabytes ) defined in the
		 * options if the 'Set Memory Limit' option is set to "Yes"
		 */
		if ( intval( get_option( $this->get( 'slug' ) . '_set_memory_limit' ), 10 ) === 1 ) {
			ini_set( 'memory_limit', intval( get_option( 'daim_memory_limit_value' ), 10 ) . 'M' );
		}
	}

	/**
	 * Execute the cron jobs.
	 */
	public function daextdaim_cron_exec() {

		// Get the HTTP response status code of a limited number of URLs saved in the "http_status" db table.
		$this->check_http_status();

		/**
		 * Check in the "_http_status" db table if all the links have been checked. (if there are zero links to check)
		 * If all the links have been checked, clear the schedule of the cron hook.
		 */
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}daim_http_status WHERE checked = 0" );
		if ( intval( $count, 10 ) === 0 ) {
			wp_clear_scheduled_hook( 'daextdaim_cron_hook' );
		}
	}

	/**
	 * Create the list of URLs for which the HTTP status code should be checked. The records are saved in the
	 * "_http_status" db table.
	 */
	public function create_http_status_list() {

		// Set the PHP "Max Execution Time" and "Memory Limit" based on the values defined in the options.
		$this->set_met_and_ml();

		// Delete all the items in the "_http_status" db table.
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}daim_http_status" );

		/**
		 * Create a query used to consider in the analysis only the post types selected with the
		 * HTTP Status Post Types option.
		 */
		$http_status_post_types_a = maybe_unserialize( get_option( $this->get( 'slug' ) . '_http_status_post_types' ) );
		$post_types_query         = '';

		// If $analysis_post_types_a is not an array fill $analysis_post_types_a with the posts available in the website.
		if ( ! is_array( $http_status_post_types_a ) || 0 === count( $http_status_post_types_a ) ) {
			$http_status_post_types_a = $this->get_post_types_with_ui();
		}

		if ( is_array( $http_status_post_types_a ) ) {
			foreach ( $http_status_post_types_a as $key => $value ) {

				$post_types_query .= $wpdb->prepare('post_type = %s', $value);
				if ( ( count( $http_status_post_types_a ) - 1 ) !== $key ) {
					$post_types_query .= ' OR ';}
			}
		}

		// Get all the considered posts.
		global $wpdb;
		$limit_posts_analysis = intval( get_option( $this->get( 'slug' ) . '_limit_posts_analysis' ), 10 );

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $post_types_query is already sanitized.
		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$posts_a = $wpdb->get_results(
			$wpdb->prepare("SELECT ID, post_title, post_type, post_date, post_content FROM {$wpdb->prefix}posts WHERE ($post_types_query) AND post_status = 'publish' ORDER BY post_date DESC LIMIT %d", $limit_posts_analysis),
			ARRAY_A
		);
		// phpcs:enable

		foreach ( $posts_a as $key => $single_post ) {

			// Set the post content.
			$post_content = $single_post['post_content'];

			// Remove the HTML comments.
			$post_content = $this->remove_html_comments( $post_content );

			// Remove script tags.
			$post_content = $this->remove_script_tags( $post_content );

			// Apply the auto interlinks to the post content.
			$post_content_with_autolinks = $this->add_autolinks( $post_content, false, $single_post['post_type'], $single_post['ID'] );

			// Find all the manual and auto interlinks matches with a regex and add them in an array.
			preg_match_all(
				$this->manual_and_auto_internal_links_regex(),
				$post_content_with_autolinks,
				$matches,
				PREG_OFFSET_CAPTURE
			);

			// Save the URL and other data in the "_http_status" db table.
			$captures = $matches[2];
			foreach ( $captures as $key => $single_capture ) {

				// Get the current time.
				$date     = current_time( 'mysql' );
				$date_gmt = current_time( 'mysql', 1 );

				// Save the link in the "http_status" db table.
				global $wpdb;

				// phpcs:disable WordPress.DB.DirectDatabaseQuery
				$wpdb->query(
					$wpdb->prepare(
						"INSERT INTO {$wpdb->prefix}daim_http_status SET 
                    post_id = %d,
                    post_title = %s,
                    post_permalink = %s,
                    post_edit_link = %s,
                    url = %s,
                    anchor = %s,
                    checked = %d,
                    last_check_date = %s,
                    last_check_date_gmt = %s",
						$single_post['ID'],
						get_the_title( $single_post['ID'] ),
						get_the_permalink( $single_post['ID'] ),
						get_edit_post_link( $single_post['ID'], 'url' ),
						$this->relative_to_absolute_url($single_capture[0], $single_post['ID']),
						$matches[3][ $key ][0],
						0,
						$date,
						$date_gmt
					)
				);
				// phpcs:enable

			}
		}
	}

	/**
	 * Get the HTTP response status code of a limited number of URLs saved in the "http_status" db table.
	 *
	 * Note that:
	 *
	 * - The number of URLs checked per run of this function is set in the "http_status_checks_per_iteration" option.
	 * - The timeout of the HTTP request is set in the "http_status_request_timeout" option.
	 * - This function runs in a WP-Cron job.
	 */
	public function check_http_status() {

		$http_status_checks_per_iteration = intval( get_option( $this->get( 'slug' ) . '_http_status_checks_per_iteration' ), 10 );
		$http_status_request_timeout      = intval( get_option( $this->get( 'slug' ) . '_http_status_request_timeout' ), 10 );

		// Iterate through the url available in the http_status db table.
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$http_status_a = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}daim_http_status WHERE checked = 0 ORDER BY id ASC LIMIT %d",
				$http_status_checks_per_iteration
			)
				, ARRAY_A );

		// Iterate through $http_status_a.
		foreach ( $http_status_a as $key => $http_status ) {

			// Check the http response of the url.
			$response    = wp_remote_get( $http_status['url'], array( 'timeout' => $http_status_request_timeout ) );
			$status_code = wp_remote_retrieve_response_code( $response );

			// Get the current time.
			$date     = current_time( 'mysql' );
			$date_gmt = current_time( 'mysql', 1 );

			// Update the checked field to 1 in the "_http_status" db table for the iterated id.
			global $wpdb;

			// phpcs:disable WordPress.DB.DirectDatabaseQuery
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->prefix}daim_http_status SET
				checked = %d,
                last_check_date = %s,
                last_check_date_gmt = %s,
                code = %d,
                code_description = %s
				WHERE id = %d",
					1,
					$date,
					$date_gmt,
					$status_code,
					$this->get_status_code_description($status_code),
					$http_status['id']
				)
			);
			// phpcs:enable

		}
	}

	/**
	 * Schedule the cron event.
	 */
	public function schedule_cron_event() {
		if ( ! wp_next_scheduled( 'daextdaim_cron_hook' ) ) {
			wp_schedule_event( time(), 'daim_custom_schedule', 'daextdaim_cron_hook' );
		}
	}

	/**
	 * Given the value of the http response status code, return the status code description.
	 */
	public function get_status_code_description( $http_status_code ) {

		// Add the status code description.
		switch ( $http_status_code ) {

			// 1xx - Informational responses.
			case 100:
				$http_status_code_description = 'Continue';
				break;
			case 101:
				$http_status_code_description = 'Switching Protocols';
				break;
			case 102:
				$http_status_code_description = 'Processing';
				break;
			case 103:
				$http_status_code_description = 'Early Hints';
				break;

			// 2xx - Successful responses.
			case 200:
				$http_status_code_description = 'OK';
				break;
			case 201:
				$http_status_code_description = 'Created';
				break;
			case 202:
				$http_status_code_description = 'Accepted';
				break;
			case 203:
				$http_status_code_description = 'Non-Authoritative Information';
				break;
			case 204:
				$http_status_code_description = 'No Content';
				break;
			case 205:
				$http_status_code_description = 'Reset Content';
				break;
			case 206:
				$http_status_code_description = 'Partial Content';
				break;
			case 207:
				$http_status_code_description = 'Multi-Status';
				break;
			case 208:
				$http_status_code_description = 'Already Reported';
				break;
			case 226:
				$http_status_code_description = 'IM Used';
				break;

			// 3xx - Redirection messages.
			case 300:
				$http_status_code_description = 'Multiple Choices';
				break;
			case 301:
				$http_status_code_description = 'Moved Permanently';
				break;
			case 302:
				$http_status_code_description = 'Found';
				break;
			case 303:
				$http_status_code_description = 'See Other';
				break;
			case 304:
				$http_status_code_description = 'Not Modified';
				break;
			case 305:
				$http_status_code_description = 'Use Proxy';
				break;
			case 306:
				$http_status_code_description = 'unused';
				break;
			case 307:
				$http_status_code_description = 'Temporary Redirect';
				break;
			case 308:
				$http_status_code_description = 'Permanent Redirect';
				break;

			// 4xx - Client error responses.
			case 400:
				$http_status_code_description = 'Bad Request';
				break;
			case 401:
				$http_status_code_description = 'Unauthorized';
				break;
			case 402:
				$http_status_code_description = 'Payment Required';
				break;
			case 403:
				$http_status_code_description = 'Forbidden';
				break;
			case 404:
				$http_status_code_description = 'Not Found';
				break;
			case 405:
				$http_status_code_description = 'Method Not Allowed';
				break;
			case 406:
				$http_status_code_description = 'Not Acceptable';
				break;
			case 407:
				$http_status_code_description = 'Proxy Authentication Required';
				break;
			case 408:
				$http_status_code_description = 'Request Timeout';
				break;
			case 409:
				$http_status_code_description = 'Conflict';
				break;
			case 410:
				$http_status_code_description = 'Gone';
				break;
			case 411:
				$http_status_code_description = 'Length Required';
				break;
			case 412:
				$http_status_code_description = 'Precondition Failed';
				break;
			case 413:
				$http_status_code_description = 'Payload Too Large';
				break;
			case 414:
				$http_status_code_description = 'URI Too Long';
				break;
			case 415:
				$http_status_code_description = 'Unsupported Media Type';
				break;
			case 416:
				$http_status_code_description = 'Range Not Satisfiable';
				break;
			case 417:
				$http_status_code_description = 'Expectation Failed';
				break;
			case 418:
				$http_status_code_description = 'I\'m a teapot';
				break;
			case 421:
				$http_status_code_description = 'Misdirected Request';
				break;
			case 422:
				$http_status_code_description = 'Unprocessable Content';
				break;
			case 423:
				$http_status_code_description = 'Locked';
				break;
			case 424:
				$http_status_code_description = 'Failed Dependency';
				break;
			case 426:
				$http_status_code_description = 'Upgrade Required';
				break;
			case 428:
				$http_status_code_description = 'Precondition Required';
				break;
			case 429:
				$http_status_code_description = 'Too Many Requests';
				break;
			case 431:
				$http_status_code_description = 'Request Header Fields Too Large';
				break;
			case 451:
				$http_status_code_description = 'Unavailable For Legal Reasons';
				break;

			// 5xx - Server error responses.
			case 500:
				$http_status_code_description = 'Internal Server Error';
				break;
			case 501:
				$http_status_code_description = 'Not Implemented';
				break;
			case 502:
				$http_status_code_description = 'Bad Gateway';
				break;
			case 503:
				$http_status_code_description = 'Service Unavailable';
				break;
			case 504:
				$http_status_code_description = 'Gateway Timeout';
				break;
			case 505:
				$http_status_code_description = 'HTTP Version Not Supported';
				break;
			case 506:
				$http_status_code_description = 'Variant Also Negotiates';
				break;
			case 507:
				$http_status_code_description = 'Insufficient Storage';
				break;
			case 508:
				$http_status_code_description = 'Loop Detected';
				break;
			case 510:
				$http_status_code_description = 'Not Extended';
				break;
			case 511:
				$http_status_code_description = 'Network Authentication Required';
				break;

			default:
				$http_status_code_description = 'Unknown';
				break;

		}

		return $http_status_code_description;
	}

	/**
	 * Given the value of the HTTP response status code, return the corresponding status code group.
	 *
	 * @param int $http_status_code The HTTP response status code.
	 *
	 * @return string
	 */
	public function get_http_response_status_code_group( $http_status_code ) {

		if ( $http_status_code >= 100 && $http_status_code <= 199 ) {
			$group_name = '1xx-informational-responses';
		} elseif ( $http_status_code >= 200 && $http_status_code <= 299 ) {
			$group_name = '2xx-successful-responses';
		} elseif ( $http_status_code >= 300 && $http_status_code <= 399 ) {
			$group_name = '3xx-redirection-messages';
		} elseif ( $http_status_code >= 400 && $http_status_code <= 499 ) {
			$group_name = '4xx-client-error-responses';
		} elseif ( $http_status_code >= 500 && $http_status_code <= 599 ) {
			$group_name = '5xx-server-error-responses';
		} else {
			$group_name = 'unknown';
		}

		return $group_name;
	}

	public function echo_icon_svg( $icon_name ) {

		switch ( $icon_name ) {

			case 'dots-grid':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 6C12.5523 6 13 5.55228 13 5C13 4.44772 12.5523 4 12 4C11.4477 4 11 4.44772 11 5C11 5.55228 11.4477 6 12 6Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M12 13C12.5523 13 13 12.5523 13 12C13 11.4477 12.5523 11 12 11C11.4477 11 11 11.4477 11 12C11 12.5523 11.4477 13 12 13Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M12 20C12.5523 20 13 19.5523 13 19C13 18.4477 12.5523 18 12 18C11.4477 18 11 18.4477 11 19C11 19.5523 11.4477 20 12 20Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M19 6C19.5523 6 20 5.55228 20 5C20 4.44772 19.5523 4 19 4C18.4477 4 18 4.44772 18 5C18 5.55228 18.4477 6 19 6Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M19 13C19.5523 13 20 12.5523 20 12C20 11.4477 19.5523 11 19 11C18.4477 11 18 11.4477 18 12C18 12.5523 18.4477 13 19 13Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M19 20C19.5523 20 20 19.5523 20 19C20 18.4477 19.5523 18 19 18C18.4477 18 18 18.4477 18 19C18 19.5523 18.4477 20 19 20Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M5 6C5.55228 6 6 5.55228 6 5C6 4.44772 5.55228 4 5 4C4.44772 4 4 4.44772 4 5C4 5.55228 4.44772 6 5 6Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M5 13C5.55228 13 6 12.5523 6 12C6 11.4477 5.55228 11 5 11C4.44772 11 4 11.4477 4 12C4 12.5523 4.44772 13 5 13Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M5 20C5.55228 20 6 19.5523 6 19C6 18.4477 5.55228 18 5 18C4.44772 18 4 18.4477 4 19C4 19.5523 4.44772 20 5 20Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'code-browser':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M22 9H2M14 17.5L16.5 15L14 12.5M10 12.5L7.5 15L10 17.5M2 7.8L2 16.2C2 17.8802 2 18.7202 2.32698 19.362C2.6146 19.9265 3.07354 20.3854 3.63803 20.673C4.27976 21 5.11984 21 6.8 21H17.2C18.8802 21 19.7202 21 20.362 20.673C20.9265 20.3854 21.3854 19.9265 21.673 19.362C22 18.7202 22 17.8802 22 16.2V7.8C22 6.11984 22 5.27977 21.673 4.63803C21.3854 4.07354 20.9265 3.6146 20.362 3.32698C19.7202 3 18.8802 3 17.2 3L6.8 3C5.11984 3 4.27976 3 3.63803 3.32698C3.07354 3.6146 2.6146 4.07354 2.32698 4.63803C2 5.27976 2 6.11984 2 7.8Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'layout-alt-03':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M17.5 17H6.5M17.5 13H6.5M3 9H21M7.8 3H16.2C17.8802 3 18.7202 3 19.362 3.32698C19.9265 3.6146 20.3854 4.07354 20.673 4.63803C21 5.27976 21 6.11984 21 7.8V16.2C21 17.8802 21 18.7202 20.673 19.362C20.3854 19.9265 19.9265 20.3854 19.362 20.673C18.7202 21 17.8802 21 16.2 21H7.8C6.11984 21 5.27976 21 4.63803 20.673C4.07354 20.3854 3.6146 19.9265 3.32698 19.362C3 18.7202 3 17.8802 3 16.2V7.8C3 6.11984 3 5.27976 3.32698 4.63803C3.6146 4.07354 4.07354 3.6146 4.63803 3.32698C5.27976 3 6.11984 3 7.8 3Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'settings-01':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M18.7273 14.7273C18.6063 15.0015 18.5702 15.3056 18.6236 15.6005C18.6771 15.8954 18.8177 16.1676 19.0273 16.3818L19.0818 16.4364C19.2509 16.6052 19.385 16.8057 19.4765 17.0265C19.568 17.2472 19.6151 17.4838 19.6151 17.7227C19.6151 17.9617 19.568 18.1983 19.4765 18.419C19.385 18.6397 19.2509 18.8402 19.0818 19.0091C18.913 19.1781 18.7124 19.3122 18.4917 19.4037C18.271 19.4952 18.0344 19.5423 17.7955 19.5423C17.5565 19.5423 17.3199 19.4952 17.0992 19.4037C16.8785 19.3122 16.678 19.1781 16.5091 19.0091L16.4545 18.9545C16.2403 18.745 15.9682 18.6044 15.6733 18.5509C15.3784 18.4974 15.0742 18.5335 14.8 18.6545C14.5311 18.7698 14.3018 18.9611 14.1403 19.205C13.9788 19.4489 13.8921 19.7347 13.8909 20.0273V20.1818C13.8909 20.664 13.6994 21.1265 13.3584 21.4675C13.0174 21.8084 12.5549 22 12.0727 22C11.5905 22 11.1281 21.8084 10.7871 21.4675C10.4461 21.1265 10.2545 20.664 10.2545 20.1818V20.1C10.2475 19.7991 10.1501 19.5073 9.97501 19.2625C9.79991 19.0176 9.55521 18.8312 9.27273 18.7273C8.99853 18.6063 8.69437 18.5702 8.39947 18.6236C8.10456 18.6771 7.83244 18.8177 7.61818 19.0273L7.56364 19.0818C7.39478 19.2509 7.19425 19.385 6.97353 19.4765C6.7528 19.568 6.51621 19.6151 6.27727 19.6151C6.03834 19.6151 5.80174 19.568 5.58102 19.4765C5.36029 19.385 5.15977 19.2509 4.99091 19.0818C4.82186 18.913 4.68775 18.7124 4.59626 18.4917C4.50476 18.271 4.45766 18.0344 4.45766 17.7955C4.45766 17.5565 4.50476 17.3199 4.59626 17.0992C4.68775 16.8785 4.82186 16.678 4.99091 16.5091L5.04545 16.4545C5.25503 16.2403 5.39562 15.9682 5.4491 15.6733C5.50257 15.3784 5.46647 15.0742 5.34545 14.8C5.23022 14.5311 5.03887 14.3018 4.79497 14.1403C4.55107 13.9788 4.26526 13.8921 3.97273 13.8909H3.81818C3.33597 13.8909 2.87351 13.6994 2.53253 13.3584C2.19156 13.0174 2 12.5549 2 12.0727C2 11.5905 2.19156 11.1281 2.53253 10.7871C2.87351 10.4461 3.33597 10.2545 3.81818 10.2545H3.9C4.2009 10.2475 4.49273 10.1501 4.73754 9.97501C4.98236 9.79991 5.16883 9.55521 5.27273 9.27273C5.39374 8.99853 5.42984 8.69437 5.37637 8.39947C5.3229 8.10456 5.18231 7.83244 4.97273 7.61818L4.91818 7.56364C4.74913 7.39478 4.61503 7.19425 4.52353 6.97353C4.43203 6.7528 4.38493 6.51621 4.38493 6.27727C4.38493 6.03834 4.43203 5.80174 4.52353 5.58102C4.61503 5.36029 4.74913 5.15977 4.91818 4.99091C5.08704 4.82186 5.28757 4.68775 5.50829 4.59626C5.72901 4.50476 5.96561 4.45766 6.20455 4.45766C6.44348 4.45766 6.68008 4.50476 6.9008 4.59626C7.12152 4.68775 7.32205 4.82186 7.49091 4.99091L7.54545 5.04545C7.75971 5.25503 8.03183 5.39562 8.32674 5.4491C8.62164 5.50257 8.9258 5.46647 9.2 5.34545H9.27273C9.54161 5.23022 9.77093 5.03887 9.93245 4.79497C10.094 4.55107 10.1807 4.26526 10.1818 3.97273V3.81818C10.1818 3.33597 10.3734 2.87351 10.7144 2.53253C11.0553 2.19156 11.5178 2 12 2C12.4822 2 12.9447 2.19156 13.2856 2.53253C13.6266 2.87351 13.8182 3.33597 13.8182 3.81818V3.9C13.8193 4.19253 13.906 4.47834 14.0676 4.72224C14.2291 4.96614 14.4584 5.15749 14.7273 5.27273C15.0015 5.39374 15.3056 5.42984 15.6005 5.37637C15.8954 5.3229 16.1676 5.18231 16.3818 4.97273L16.4364 4.91818C16.6052 4.74913 16.8057 4.61503 17.0265 4.52353C17.2472 4.43203 17.4838 4.38493 17.7227 4.38493C17.9617 4.38493 18.1983 4.43203 18.419 4.52353C18.6397 4.61503 18.8402 4.74913 19.0091 4.91818C19.1781 5.08704 19.3122 5.28757 19.4037 5.50829C19.4952 5.72901 19.5423 5.96561 19.5423 6.20455C19.5423 6.44348 19.4952 6.68008 19.4037 6.9008C19.3122 7.12152 19.1781 7.32205 19.0091 7.49091L18.9545 7.54545C18.745 7.75971 18.6044 8.03183 18.5509 8.32674C18.4974 8.62164 18.5335 8.9258 18.6545 9.2V9.27273C18.7698 9.54161 18.9611 9.77093 19.205 9.93245C19.4489 10.094 19.7347 10.1807 20.0273 10.1818H20.1818C20.664 10.1818 21.1265 10.3734 21.4675 10.7144C21.8084 11.0553 22 11.5178 22 12C22 12.4822 21.8084 12.9447 21.4675 13.2856C21.1265 13.6266 20.664 13.8182 20.1818 13.8182H20.1C19.8075 13.8193 19.5217 13.906 19.2778 14.0676C19.0339 14.2291 18.8425 14.4584 18.7273 14.7273Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'grid-01':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M8.4 3H4.6C4.03995 3 3.75992 3 3.54601 3.10899C3.35785 3.20487 3.20487 3.35785 3.10899 3.54601C3 3.75992 3 4.03995 3 4.6V8.4C3 8.96005 3 9.24008 3.10899 9.45399C3.20487 9.64215 3.35785 9.79513 3.54601 9.89101C3.75992 10 4.03995 10 4.6 10H8.4C8.96005 10 9.24008 10 9.45399 9.89101C9.64215 9.79513 9.79513 9.64215 9.89101 9.45399C10 9.24008 10 8.96005 10 8.4V4.6C10 4.03995 10 3.75992 9.89101 3.54601C9.79513 3.35785 9.64215 3.20487 9.45399 3.10899C9.24008 3 8.96005 3 8.4 3Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M19.4 3H15.6C15.0399 3 14.7599 3 14.546 3.10899C14.3578 3.20487 14.2049 3.35785 14.109 3.54601C14 3.75992 14 4.03995 14 4.6V8.4C14 8.96005 14 9.24008 14.109 9.45399C14.2049 9.64215 14.3578 9.79513 14.546 9.89101C14.7599 10 15.0399 10 15.6 10H19.4C19.9601 10 20.2401 10 20.454 9.89101C20.6422 9.79513 20.7951 9.64215 20.891 9.45399C21 9.24008 21 8.96005 21 8.4V4.6C21 4.03995 21 3.75992 20.891 3.54601C20.7951 3.35785 20.6422 3.20487 20.454 3.10899C20.2401 3 19.9601 3 19.4 3Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M19.4 14H15.6C15.0399 14 14.7599 14 14.546 14.109C14.3578 14.2049 14.2049 14.3578 14.109 14.546C14 14.7599 14 15.0399 14 15.6V19.4C14 19.9601 14 20.2401 14.109 20.454C14.2049 20.6422 14.3578 20.7951 14.546 20.891C14.7599 21 15.0399 21 15.6 21H19.4C19.9601 21 20.2401 21 20.454 20.891C20.6422 20.7951 20.7951 20.6422 20.891 20.454C21 20.2401 21 19.9601 21 19.4V15.6C21 15.0399 21 14.7599 20.891 14.546C20.7951 14.3578 20.6422 14.2049 20.454 14.109C20.2401 14 19.9601 14 19.4 14Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M8.4 14H4.6C4.03995 14 3.75992 14 3.54601 14.109C3.35785 14.2049 3.20487 14.3578 3.10899 14.546C3 14.7599 3 15.0399 3 15.6V19.4C3 19.9601 3 20.2401 3.10899 20.454C3.20487 20.6422 3.35785 20.7951 3.54601 20.891C3.75992 21 4.03995 21 4.6 21H8.4C8.96005 21 9.24008 21 9.45399 20.891C9.64215 20.7951 9.79513 20.6422 9.89101 20.454C10 20.2401 10 19.9601 10 19.4V15.6C10 15.0399 10 14.7599 9.89101 14.546C9.79513 14.3578 9.64215 14.2049 9.45399 14.109C9.24008 14 8.96005 14 8.4 14Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'line-chart-up-03':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M17 9L11.5657 14.4343C11.3677 14.6323 11.2687 14.7313 11.1545 14.7684C11.0541 14.8011 10.9459 14.8011 10.8455 14.7684C10.7313 14.7313 10.6323 14.6323 10.4343 14.4343L8.56569 12.5657C8.36768 12.3677 8.26867 12.2687 8.15451 12.2316C8.05409 12.1989 7.94591 12.1989 7.84549 12.2316C7.73133 12.2687 7.63232 12.3677 7.43431 12.5657L3 17M17 9H13M17 9V13M7.8 21H16.2C17.8802 21 18.7202 21 19.362 20.673C19.9265 20.3854 20.3854 19.9265 20.673 19.362C21 18.7202 21 17.8802 21 16.2V7.8C21 6.11984 21 5.27976 20.673 4.63803C20.3854 4.07354 19.9265 3.6146 19.362 3.32698C18.7202 3 17.8802 3 16.2 3H7.8C6.11984 3 5.27976 3 4.63803 3.32698C4.07354 3.6146 3.6146 4.07354 3.32698 4.63803C3 5.27976 3 6.11984 3 7.8V16.2C3 17.8802 3 18.7202 3.32698 19.362C3.6146 19.9265 4.07354 20.3854 4.63803 20.673C5.27976 21 6.11984 21 7.8 21Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'link-03':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M9.99999 13C10.4294 13.5741 10.9773 14.0491 11.6065 14.3929C12.2357 14.7367 12.9315 14.9411 13.6466 14.9923C14.3618 15.0435 15.0796 14.9403 15.7513 14.6897C16.4231 14.4392 17.0331 14.047 17.54 13.54L20.54 10.54C21.4508 9.59695 21.9547 8.33394 21.9434 7.02296C21.932 5.71198 21.4061 4.45791 20.4791 3.53087C19.552 2.60383 18.298 2.07799 16.987 2.0666C15.676 2.0552 14.413 2.55918 13.47 3.46997L11.75 5.17997M14 11C13.5705 10.4258 13.0226 9.95078 12.3934 9.60703C11.7642 9.26327 11.0685 9.05885 10.3533 9.00763C9.63819 8.95641 8.9204 9.0596 8.24864 9.31018C7.57688 9.56077 6.96687 9.9529 6.45999 10.46L3.45999 13.46C2.5492 14.403 2.04522 15.666 2.05662 16.977C2.06801 18.288 2.59385 19.542 3.52089 20.4691C4.44793 21.3961 5.702 21.9219 7.01298 21.9333C8.32396 21.9447 9.58697 21.4408 10.53 20.53L12.24 18.82" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'check-verified-01':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M9 12L11 14L15.5 9.5M7.33377 3.8187C8.1376 3.75455 8.90071 3.43846 9.51447 2.91542C10.9467 1.69486 13.0533 1.69486 14.4855 2.91542C15.0993 3.43846 15.8624 3.75455 16.6662 3.8187C18.5421 3.96839 20.0316 5.45794 20.1813 7.33377C20.2455 8.1376 20.5615 8.90071 21.0846 9.51447C22.3051 10.9467 22.3051 13.0533 21.0846 14.4855C20.5615 15.0993 20.2455 15.8624 20.1813 16.6662C20.0316 18.5421 18.5421 20.0316 16.6662 20.1813C15.8624 20.2455 15.0993 20.5615 14.4855 21.0846C13.0533 22.3051 10.9467 22.3051 9.51447 21.0846C8.90071 20.5615 8.1376 20.2455 7.33377 20.1813C5.45794 20.0316 3.96839 18.5421 3.8187 16.6662C3.75455 15.8624 3.43846 15.0993 2.91542 14.4855C1.69486 13.0533 1.69486 10.9467 2.91542 9.51447C3.43846 8.90071 3.75455 8.1376 3.8187 7.33377C3.96839 5.45794 5.45794 3.96839 7.33377 3.8187Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-up':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M18 15L12 9L6 15" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-down':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M6 9L12 15L18 9" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-left':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M15 18L9 12L15 6" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-left-double':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M18 17L13 12L18 7M11 17L6 12L11 7" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-right':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M9 18L15 12L9 6" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-right-double':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M6 17L11 12L6 7M13 17L18 12L13 7" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'arrow-up-right':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M7 17L17 7M17 7H7M17 7V17" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'plus':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 5V19M5 12H19" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'bar-chart-07':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M21 21H6.2C5.07989 21 4.51984 21 4.09202 20.782C3.71569 20.5903 3.40973 20.2843 3.21799 19.908C3 19.4802 3 18.9201 3 17.8V3M7 10.5V17.5M11.5 5.5V17.5M16 10.5V17.5M20.5 5.5V17.5" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'lightbulb-05':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 2V3M3 12H2M5.5 5.5L4.8999 4.8999M18.5 5.5L19.1002 4.8999M22 12H21M10 13.5H14M12 13.5V18.5M15.5 16.874C17.0141 15.7848 18 14.0075 18 12C18 8.68629 15.3137 6 12 6C8.68629 6 6 8.68629 6 12C6 14.0075 6.98593 15.7848 8.5 16.874V18.8C8.5 19.9201 8.5 20.4802 8.71799 20.908C8.90973 21.2843 9.21569 21.5903 9.59202 21.782C10.0198 22 10.5799 22 11.7 22H12.3C13.4201 22 13.9802 22 14.408 21.782C14.7843 21.5903 15.0903 21.2843 15.282 20.908C15.5 20.4802 15.5 19.9201 15.5 18.8V16.874Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'share-05':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M21 6H17.8C16.1198 6 15.2798 6 14.638 6.32698C14.0735 6.6146 13.6146 7.07354 13.327 7.63803C13 8.27976 13 9.11984 13 10.8V12M21 6L18 3M21 6L18 9M10 3H7.8C6.11984 3 5.27976 3 4.63803 3.32698C4.07354 3.6146 3.6146 4.07354 3.32698 4.63803C3 5.27976 3 6.11984 3 7.8V16.2C3 17.8802 3 18.7202 3.32698 19.362C3.6146 19.9265 4.07354 20.3854 4.63803 20.673C5.27976 21 6.11984 21 7.8 21H16.2C17.8802 21 18.7202 21 19.362 20.673C19.9265 20.3854 20.3854 19.9265 20.673 19.362C21 18.7202 21 17.8802 21 16.2V14" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'check-circle-broken':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M22 11.0857V12.0057C21.9988 14.1621 21.3005 16.2604 20.0093 17.9875C18.7182 19.7147 16.9033 20.9782 14.8354 21.5896C12.7674 22.201 10.5573 22.1276 8.53447 21.3803C6.51168 20.633 4.78465 19.2518 3.61096 17.4428C2.43727 15.6338 1.87979 13.4938 2.02168 11.342C2.16356 9.19029 2.99721 7.14205 4.39828 5.5028C5.79935 3.86354 7.69279 2.72111 9.79619 2.24587C11.8996 1.77063 14.1003 1.98806 16.07 2.86572M22 4L12 14.01L9 11.01" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'cursor-click-02':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M9 3.5V2M5.06066 5.06066L4 4M5.06066 13L4 14.0607M13 5.06066L14.0607 4M3.5 9H2M8.5 8.5L12.6111 21.2778L15.5 18.3889L19.1111 22L22 19.1111L18.3889 15.5L21.2778 12.6111L8.5 8.5Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'log-in-04':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 8L16 12M16 12L12 16M16 12H3M3.33782 7C5.06687 4.01099 8.29859 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C8.29859 22 5.06687 19.989 3.33782 17" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'log-out-04':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M18 8L22 12M22 12L18 16M22 12H9M15 4.20404C13.7252 3.43827 12.2452 3 10.6667 3C5.8802 3 2 7.02944 2 12C2 16.9706 5.8802 21 10.6667 21C12.2452 21 13.7252 20.5617 15 19.796" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'order-asc':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M3 12H15M3 6H9M3 18H21" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'order-desc':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M3 12H15M3 6H21M3 18H9" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'clipboard-icon-svg':
				$xml = '<?xml version="1.0" encoding="utf-8"?>
				<svg version="1.1" id="Layer_3" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
					 viewBox="0 0 20 20" style="enable-background:new 0 0 20 20;" xml:space="preserve">
				<path d="M14,18H8c-1.1,0-2-0.9-2-2V7c0-1.1,0.9-2,2-2h6c1.1,0,2,0.9,2,2v9C16,17.1,15.1,18,14,18z M8,7v9h6V7H8z"/>
				<path d="M5,4h6V2H5C3.9,2,3,2.9,3,4v9h2V4z"/>
				</svg>';

				$allowed_html = array(
					'svg'  => array(
						'version' => array(),
						'id'      => array(),
						'xmlns'   => array(),
						'x'       => array(),
						'y'       => array(),
						'viewbox' => array(),
						'style'   => array(),
					),
					'path' => array(
						'd' => array(),
					),
				);

				break;

			case 'x':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M17 7L7 17M7 7L17 17" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'version' => array(),
						'id'      => array(),
						'xmlns'   => array(),
						'x'       => array(),
						'y'       => array(),
						'viewbox' => array(),
						'style'   => array(),
					),
					'path' => array(
						'd' => array(),
					),
				);

				break;

			case 'diamond-01':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M2.49954 9H21.4995M9.99954 3L7.99954 9L11.9995 20.5L15.9995 9L13.9995 3M12.6141 20.2625L21.5727 9.51215C21.7246 9.32995 21.8005 9.23885 21.8295 9.13717C21.8551 9.04751 21.8551 8.95249 21.8295 8.86283C21.8005 8.76114 21.7246 8.67005 21.5727 8.48785L17.2394 3.28785C17.1512 3.18204 17.1072 3.12914 17.0531 3.09111C17.0052 3.05741 16.9518 3.03238 16.8953 3.01717C16.8314 3 16.7626 3 16.6248 3H7.37424C7.2365 3 7.16764 3 7.10382 3.01717C7.04728 3.03238 6.99385 3.05741 6.94596 3.09111C6.89192 3.12914 6.84783 3.18204 6.75966 3.28785L2.42633 8.48785C2.2745 8.67004 2.19858 8.76114 2.16957 8.86283C2.144 8.95249 2.144 9.04751 2.16957 9.13716C2.19858 9.23885 2.2745 9.32995 2.42633 9.51215L11.385 20.2625C11.596 20.5158 11.7015 20.6424 11.8279 20.6886C11.9387 20.7291 12.0603 20.7291 12.1712 20.6886C12.2975 20.6424 12.4031 20.5158 12.6141 20.2625Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			default:
				$xml = '';

				break;

		}

		echo wp_kses( $xml, $allowed_html );
	}

	/**
	 * Ajax handler used to generate the interlinks archive in the "Dashboard"
	 * menu.
	 */
	public function update_interlinks_archive() {

		// Generate the link juice data (these data will be used to generate the value of the IIL column).
		$this->update_juice_archive();

		// Set the PHP "Max Execution Time" and "Memory Limit" based on the values defined in the options.
		$this->set_met_and_ml();

		/**
		 * Create a query used to consider in the analysis only the post types
		 * selected with the 'dashboard_post_types' option.
		 */
		$dashboard_post_types_a = maybe_unserialize( get_option( $this->get( 'slug' ) . '_dashboard_post_types' ) );
		$post_types_query       = '';
		global $wpdb;

		// If $dashboard_post_types_a is not an array fill $dashboard_post_types_a with the posts available in the website.
		if ( ! is_array( $dashboard_post_types_a ) || 0 === count( $dashboard_post_types_a ) ) {
			$dashboard_post_types_a = $this->get_post_types_with_ui();
		}

		if ( is_array( $dashboard_post_types_a ) ) {
			foreach ( $dashboard_post_types_a as $key => $value ) {

				$post_types_query .= $wpdb->prepare('post_type = %s', $value);
				if ( ( count( $dashboard_post_types_a ) - 1 ) !== $key ) {
					$post_types_query .= ' OR ';}
			}
		}

		/**
		 * Get all the manual internal links and save them in the archive db
		 * table.
		 */
		$limit_posts_analysis = intval( get_option( $this->get( 'slug' ) . '_limit_posts_analysis' ), 10 );
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $post_types_query is already sanitized.
		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$posts_a              = $wpdb->get_results(
			$wpdb->prepare("SELECT ID, post_title, post_type, post_date, post_content FROM {$wpdb->prefix}posts WHERE ($post_types_query) AND post_status = 'publish' ORDER BY post_date DESC LIMIT %d", $limit_posts_analysis)
				, ARRAY_A );
		// phpcs:enable

		// Delete the internal links archive database table content.
		$result = $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}daim_archive" );

		// Init $archive_a.
		$archive_a = array();

		foreach ( $posts_a as $key => $single_post ) {

			// Set the post id.
			$post_archive_post_id = $single_post['ID'];

			// get the post title.
			$post_archive_post_title = $single_post['post_title'];

			// Get the post permalink.
			$post_archive_post_permalink = get_the_permalink( $single_post['ID'] );

			// Get the post edit link.
			$post_archive_post_edit_link = get_edit_post_link( $single_post['ID'], 'url' );

			// Set the post type.
			$post_archive_post_type = $single_post['post_type'];

			// Set the post date.
			$post_archive_post_date = $single_post['post_date'];

			// Set the post content.
			$post_content = $single_post['post_content'];

			// Set the number of manual internal links.
			$post_archive_manual_interlinks = $this->get_manual_interlinks( $post_content );

			// Create a variable with the post content with autolinks included.
			$post_content_with_autolinks = $this->add_autolinks( $post_content, false, $post_archive_post_type, $post_archive_post_id );

			// Set the number of auto internal links.
			$post_archive_auto_interlinks = $this->get_autolinks_number( $post_content_with_autolinks );

			/**
			 * Get the IIL from the juice db table by comparing the permalink of this post with the URL field available
			 * in the juice db table.
			 */

			// Get the permalink of the post.
			$permalink = get_the_permalink( $single_post['ID'] );

			// Find this permalink in the url field of the "_juice" db table.
			// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
			// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
			$juice_obj = $wpdb->get_row(
				$wpdb->prepare( "SELECT iil FROM {$wpdb->prefix}daim_juice WHERE url = %s ", $permalink )
			);
			// phpcs:enable

			if ( null !== $juice_obj ) {
				$post_archive_iil = $juice_obj->iil;
			} else {
				$post_archive_iil = 0;
			}

			// Set the post content length.
			$post_archive_content_length = mb_strlen( trim( $post_content ) );

			// Set the recommended interlinks.
			$post_archive_recommended_interlinks = $this->calculate_recommended_interlinks( $post_archive_manual_interlinks + $post_archive_auto_interlinks, $post_archive_content_length );

			// Get the number of internal links clicks.
			$num_il_clicks = $this->get_number_of_hits($single_post['ID']);

			// Set the optimization flag.
			$optimization = $this->calculate_optimization( $post_archive_manual_interlinks + $post_archive_auto_interlinks, $post_archive_content_length );

			/**
			 * Save data in the $archive_a array ( data will be later saved into
			 * the archive db table ).
			 */
			$archive_a[] = array(
				'post_id'                => $post_archive_post_id,
				'post_title'             => $post_archive_post_title,
				'post_permalink'         => $post_archive_post_permalink,
				'post_edit_link'         => $post_archive_post_edit_link,
				'post_type'              => $post_archive_post_type,
				'post_date'              => $post_archive_post_date,
				'manual_interlinks'      => $post_archive_manual_interlinks,
				'auto_interlinks'        => $post_archive_auto_interlinks,
				'iil'                    => $post_archive_iil,
				'content_length'         => $post_archive_content_length,
				'recommended_interlinks' => $post_archive_recommended_interlinks,
				'num_il_clicks'          => $num_il_clicks,
				'optimization'           => $optimization,
			);

		}

		/**
		 * Save data into the archive db table with multiple queries of 100
		 * items each one.
		 * It's a compromise for the following two reasons:
		 * 1 - For performance, too many queries slow down the process
		 * 2 - To avoid problem with queries too long the number of inserted
		 * rows per query are limited to 100
		 */
		$archive_a_length = count( $archive_a );
		$query_groups     = array();
		$query_index      = 0;
		foreach ( $archive_a as $key => $single_archive ) {

			$query_index = intval( $key / 100, 10 );

			$query_groups[ $query_index ][] = $wpdb->prepare(
				'( %d, %s, %s, %s, %s, %s, %d, %d, %d, %d, %d, %d, %d )',
				$single_archive['post_id'],
				$single_archive['post_title'],
				$single_archive['post_permalink'],
				$single_archive['post_edit_link'],
				$single_archive['post_type'],
				$single_archive['post_date'],
				$single_archive['manual_interlinks'],
				$single_archive['auto_interlinks'],
				$single_archive['iil'],
				$single_archive['content_length'],
				$single_archive['recommended_interlinks'],
				$single_archive['num_il_clicks'],
				$single_archive['optimization']
			);

		}

		/**
		 * Each item in the $query_groups array includes a maximum of 100
		 * assigned records. Here each group creates a query and the query is
		 * executed.
		 */
		$query_start = "INSERT INTO {$wpdb->prefix}daim_archive (post_id, post_title, post_permalink, post_edit_link, post_type, post_date, manual_interlinks, auto_interlinks, iil, content_length, recommended_interlinks, num_il_clicks, optimization) VALUES ";
		$query_end   = '';

		foreach ( $query_groups as $key => $query_values ) {

			$query_body = '';

			foreach ( $query_values as $single_query_value ) {

				$query_body .= $single_query_value . ',';

			}

			$safe_sql = $query_start . substr( $query_body, 0, strlen( $query_body ) - 1 ) . $query_end;

			// Save data into the archive db table.
			// phpcs:disable WordPress.DB.DirectDatabaseQuery
			// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared -- $query_start, query_body, and $query_end are already prepared.
			$wpdb->query( $query_start . substr( $query_body, 0, strlen( $query_body ) - 1 ) . $query_end );
			// phpcs:enable

		}
	}

	/**
	 * Ajax handler used to generate the juice archive in "Juice" menu.
	 */
	public function update_juice_archive() {

		// Set the PHP "Max Execution Time" and "Memory Limit" based on the values defined in the options.
		$this->set_met_and_ml();

		// Delete the juice db table content.
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}daim_juice" );

		// Delete the anchors db table content.
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}daim_anchors" );

		// update the juice archive ---------------------------------------------.
		$juice_a  = array();
		$juice_id = 0;

		/**
		 * Create a query used to consider in the analysis only the post types
		 * selected with the 'juice_post_types' option.
		 */
		$juice_post_types_a = maybe_unserialize( get_option( $this->get( 'slug' ) . '_juice_post_types' ) );
		$post_types_query   = '';

		// If $analysis_post_types_a is not an array fill $analysis_post_types_a with the posts available in the website.
		if ( ! is_array( $juice_post_types_a ) || 0 === count( $juice_post_types_a ) ) {
			$juice_post_types_a = $this->get_post_types_with_ui();
		}

		if ( is_array( $juice_post_types_a ) ) {
			foreach ( $juice_post_types_a as $key => $value ) {

				$post_types_query .= $wpdb->prepare('post_type = %s', $value);
				if ( ( count( $juice_post_types_a ) - 1 ) !== $key ) {
					$post_types_query .= ' OR ';}
			}
		}

		/**
		 * Get all the manual and auto internal links and save them in an array.
		 */
		$limit_posts_analysis = intval( get_option( $this->get( 'slug' ) . '_limit_posts_analysis' ), 10 );

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $post_types_query is already prepared.
		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$posts_a = $wpdb->get_results(
			$wpdb->prepare("SELECT ID, post_title, post_type, post_date, post_content FROM {$wpdb->prefix}posts WHERE ($post_types_query) AND post_status = 'publish' ORDER BY post_date DESC LIMIT %d", $limit_posts_analysis),
			ARRAY_A
		);
		// phpcs:enable

		foreach ( $posts_a as $key => $single_post ) {

			// Set the post content.
			$post_content = $single_post['post_content'];

			// Remove the HTML comments.
			$post_content = $this->remove_html_comments( $post_content );

			// Remove script tags.
			$post_content = $this->remove_script_tags( $post_content );

			// Apply the auto interlinks to the post content.
			$post_content_with_autolinks = $this->add_autolinks( $post_content, false, $single_post['post_type'], $single_post['ID'] );

			/**
			 * Find all the manual and auto interlinks matches with a regular
			 * expression and add them in the $juice_a array.
			 */
			preg_match_all(
				$this->manual_and_auto_internal_links_regex(),
				$post_content_with_autolinks,
				$matches,
				PREG_OFFSET_CAPTURE
			);

			// Save the URLs, the juice value and other info in the array.
			$captures = $matches[2];
			foreach ( $captures as $key => $single_capture ) {

				// Get the link position.
				$link_position = $matches[0][ $key ][1];

				// save the captured URL.
				$url = $this->relative_to_absolute_url( $single_capture[0], $single_post['ID'] );

				/**
				 * Remove link to anchor from the URL ( if enabled through the
				 * options ).
				 */
				if ( intval( get_option( $this->get( 'slug' ) . '_remove_link_to_anchor' ), 10 ) === 1 ) {
					$url = $this->remove_link_to_anchor( $url );
				}

				/**
				 * Remove the URL parameters ( if enabled through the options ).
				 */
				if ( 1 === intval( get_option( $this->get( 'slug' ) . '_remove_url_parameters' ), 10 ) ) {
					$url = $this->remove_url_parameters( $url );
				}

				$juice_a[ $juice_id ]['url']        = $url;
				$juice_a[ $juice_id ]['juice']      = $this->calculate_link_juice( $post_content_with_autolinks, $single_post['ID'], $link_position );
				$juice_a[ $juice_id ]['anchor']     = $matches[3][ $key ][0];
				$juice_a[ $juice_id ]['post_id']    = $single_post['ID'];
				$juice_a[ $juice_id ]['post_title'] = $single_post['post_title'];
				$juice_a[ $juice_id ]['post_permalink'] = get_the_permalink( $single_post['ID'] );
				$juice_a[ $juice_id ]['post_edit_link'] = get_edit_post_link( $single_post['ID'], 'url' );

				++$juice_id;

			}
		}

		/**
		 * Save data into the anchors db table with multiple queries of 100
		 * items each one.
		 * It's a compromise for the following two reasons:
		 * 1 - For performance, too many queries slow down the process.
		 * 2 - To avoid problem with queries too long the number of inserted
		 * rows per query are limited to 100.
		 */
		$juice_a_length = count( $juice_a );
		$query_groups   = array();
		$query_index    = 0;
		foreach ( $juice_a as $key => $single_juice ) {

			$query_index = intval( $key / 100, 10 );

			$query_groups[ $query_index ][] = $wpdb->prepare(
				'( %s, %s, %d, %d, %s, %s, %s )',
				$single_juice['url'],
				$single_juice['anchor'],
				$single_juice['post_id'],
				$single_juice['juice'],
				$single_juice['post_title'],
				$single_juice['post_permalink'],
				$single_juice['post_edit_link']
			);

		}

		/**
		 * Each item in the $query_groups array includes a maximum of 100
		 * assigned records. Here each group creates a query and the query is
		 * executed.
		 */
		$query_start = "INSERT INTO {$wpdb->prefix}daim_anchors (url, anchor, post_id, juice, post_title, post_permalink, post_edit_link) VALUES ";
		$query_end   = '';

		foreach ( $query_groups as $key => $query_values ) {

			$query_body = '';

			foreach ( $query_values as $single_query_value ) {

				$query_body .= $single_query_value . ',';

			}

			// Save data into the archive db table.
			// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared -- $query_start, query_body, and $query_end are already prepared.
			// phpcs:disable WordPress.DB.DirectDatabaseQuery
			$wpdb->query(
				$query_start . substr( $query_body, 0, strlen( $query_body ) - 1 ) . $query_end
			);
			// phpcs:enable

		}

		// Prepare data that should be saved in the juice db table --------------.
		$juice_a_no_duplicates    = array();
		$juice_a_no_duplicates_id = 0;

		/*
		 * Reduce multiple array items with the same URL to a single array item
		 * with a sum of iil and juice
		 */
		foreach ( $juice_a as $key => $single_juice ) {

			$duplicate_found = false;

			// Verify if an item with this url already exist in the $juice_a_no_duplicates array.
			foreach ( $juice_a_no_duplicates as $key => $single_juice_a_no_duplicates ) {

				if ( $single_juice_a_no_duplicates['url'] === $single_juice['url'] ) {
					++$juice_a_no_duplicates[ $key ]['iil'];
					$juice_a_no_duplicates[ $key ]['juice'] = $juice_a_no_duplicates[ $key ]['juice'] + $single_juice['juice'];
					$duplicate_found                        = true;
				}
			}

			/*
			 * if this url doesn't already exist in the array save it in
			 * $juice_a_no_duplicates
			 */
			if ( ! $duplicate_found ) {

				$juice_a_no_duplicates[ $juice_a_no_duplicates_id ]['url']   = $single_juice['url'];
				$juice_a_no_duplicates[ $juice_a_no_duplicates_id ]['iil']   = 1;
				$juice_a_no_duplicates[ $juice_a_no_duplicates_id ]['juice'] = $single_juice['juice'];
				++$juice_a_no_duplicates_id;

			}
		}

		/**
		 * Calculate the relative link juice on a scale between 0 and 100,
		 * the maximum value found corresponds to the 100 value of the
		 * relative link juice.
		 */
		$max_value = 0;
		foreach ( $juice_a_no_duplicates as $key => $juice_a_no_duplicates_single ) {
			if ( $juice_a_no_duplicates_single['juice'] > $max_value ) {
				$max_value = $juice_a_no_duplicates_single['juice'];
			}
		}

		// Set the juice_relative index in the array.
		foreach ( $juice_a_no_duplicates as $key => $juice_a_no_duplicates_single ) {
			$juice_a_no_duplicates[ $key ]['juice_relative'] = ( 100 * $juice_a_no_duplicates_single['juice'] ) / $max_value;
		}

		/**
		 * Save data into the juice db table with multiple queries of 100
		 * items each one.
		 * It's a compromise for the following two reasons:
		 * 1 - For performance, too many queries slow down the process.
		 * 2 - To avoid problem with queries too long the number of inserted
		 * rows per query are limited to 100.
		 */
		$juice_a_no_duplicates_length = count( $juice_a_no_duplicates );
		$query_groups                 = array();
		$query_index                  = 0;
		foreach ( $juice_a_no_duplicates as $key => $value ) {

			$query_index = intval( $key / 100, 10 );

			$query_groups[ $query_index ][] = $wpdb->prepare(
				'( %s, %d, %d, %d )',
				$value['url'],
				$value['iil'],
				$value['juice'],
				$value['juice_relative']
			);

		}

		/**
		 * Each item in the $query_groups array includes a maximum of 100
		 * assigned records. Here each group creates a query and the query is
		 * executed.
		 */
		$query_start = "INSERT INTO {$wpdb->prefix}daim_juice (url, iil, juice, juice_relative) VALUES ";
		$query_end   = '';

		foreach ( $query_groups as $key => $query_values ) {

			$query_body = '';

			foreach ( $query_values as $single_query_value ) {

				$query_body .= $single_query_value . ',';

			}

			// Save data into the archive db table.
			// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared -- $query_start, query_body, and $query_end are already prepared.
			// phpcs:disable WordPress.DB.DirectDatabaseQuery
			$wpdb->query(
				$query_start . substr( $query_body, 0, strlen( $query_body ) - 1 ) . $query_end
			);
			// phpcs:enable

		}

		// Send output.
		return 'success';

	}

	/**
	 * Escape the double quotes of the $content string, so the returned string
	 * can be used in CSV fields enclosed by double quotes.
	 *
	 * @param string $content The unescape content ( Ex: She said "No!" )
	 * @return string The escaped content ( Ex: She said ""No!"" )
	 */
	public function esc_csv( $content ) {
		return str_replace( '"', '""', $content );
	}

	/**
	 * Save a dismissible notice in the "daim_dismissible_notice_a" WordPress.
	 *
	 * @param string $message The message of the dismissible notice.
	 * @param string $class The class of the dismissible notice.
	 *
	 * @return void
	 */
	public function save_dismissible_notice( $message, $element_class ) {

		$dismissible_notice = array(
			'user_id' => get_current_user_id(),
			'message' => $message,
			'class'   => $element_class,
		);

		// Get the current option value.
		$dismissible_notice_a = get_option( 'daim_dismissible_notice_a' );

		// If the option is not an array, initialize it as an array.
		if ( ! is_array( $dismissible_notice_a ) ) {
			$dismissible_notice_a = array();
		}

		// Add the dismissible notice to the array.
		$dismissible_notice_a[] = $dismissible_notice;

		// Save the dismissible notice in the "daim_dismissible_notice_a" WordPress option.
		update_option( 'daim_dismissible_notice_a', $dismissible_notice_a );
	}

	/**
	 * Display the dismissible notices stored in the "daim_dismissible_notice_a" option.
	 *
	 * Note that the dismissible notice will be displayed only once to the user.
	 *
	 * The dismissable notice is first displayed (only to the same user with which has been generated) and then it is
	 * removed from the "daim_dismissible_notice_a" option.
	 *
	 * @return void
	 */
	public function display_dismissible_notices() {

		$dismissible_notice_a = get_option( 'daim_dismissible_notice_a' );

		// Iterate over the dismissible notices with the user id of the same user.
		if ( is_array( $dismissible_notice_a ) ) {
			foreach ( $dismissible_notice_a as $key => $dismissible_notice ) {

				// If the user id of the dismissible notice is the same as the current user id, display the message.
				if ( get_current_user_id() === $dismissible_notice['user_id'] ) {

					$message = $dismissible_notice['message'];
					$class   = $dismissible_notice['class'];

					?>
					<div class="<?php echo esc_attr( $class ); ?> notice">
						<p><?php echo esc_html( $message ); ?></p>
						<div class="notice-dismiss-button"><?php $this->echo_icon_svg('x'); ?></div>
					</div>

					<?php

					// Remove the echoed dismissible notice from the "daim_dismissible_notice_a" WordPress option.
					unset( $dismissible_notice_a[ $key ] );

					update_option( 'daim_dismissible_notice_a', $dismissible_notice_a );

				}
			}
		}
	}

	/**
	 * Generate the http status archive displayed in the "Http status" menu.
	 *
	 * @return void
	 */
	public function update_http_status_archive() {

		// Clear the existing cron event.
		wp_clear_scheduled_hook( 'daextdaim_cron_hook' );

		// Create a new list of URLs to check.
		$this->create_http_status_list();

		// Schedule the cron event.
		$this->schedule_cron_event();
	}

	/**
	 * Iterate the $results array and find the average number of manual internal links. Note that the manual internal
	 * links value is stored in the 'manual_interlinks' key of the $results array.
	 *
	 * @param array $results The link statistics stored in the archive db table provided as an array.
	 *
	 * @return int The average number of manual internal links.
	 */
	public function get_average_mil( $results ) {

		// Init the $total_mil variable.
		$total_mil = 0;

		// Iterate the $results array and sum the manual internal links.
		foreach ( $results as $key => $result ) {
			$total_mil += $result->manual_interlinks;
		}

		// Calculate the average number of manual internal links.
		$average_mil = $total_mil / count( $results );

		// Round the average number of manual internal links (no decimals).
		$average_mil = round( $average_mil, 1 );

		return $average_mil;
	}

	/**
	 * Iterate the $results array and find the average number of automatic internal links. Note that the auto internal
	 * links value is stored in the 'auto_interlinks' key of the $results array.
	 *
	 * @param array $results The link statistics stored in the archive db table provided as an array.
	 *
	 * @return int The average number of automatic internal links.
	 */
	public function get_average_ail( $results ) {

		// Init the $total_mil variable.
		$total_ail = 0;

		// Iterate the $results array and sum the manual internal links.
		foreach ( $results as $key => $result ) {
			$total_ail += $result->auto_interlinks;
		}

		// Calculate the average number of manual internal links.
		$average_ail = $total_ail / count( $results );

		// Round the average number of manual internal links (no decimals).
		$average_ail = round( $average_ail, 1 );

		return $average_ail;
	}

	/**
	 * Iterate the $results array and find the average number of internal inbound links. Note that the internal inbound
	 * links value is stored in the 'iil' key of the $results array.
	 *
	 * @param array $results The link statistics stored in the archive db table provided as an array.
	 *
	 * @return int The average number of manual internal links.
	 */
	public function get_average_iil( $results ) {

		// Init the $total_iil variable.
		$total_iil = 0;

		// Iterate the $results array and sum the manual internal links.
		foreach ( $results as $key => $result ) {
			$total_iil += $result->iil;
		}

		// Calculate the average number of manual internal links.
		$average_iil = $total_iil / count( $results );

		// Round the average number of manual internal links (no decimals).
		$average_iil = round( $average_iil, 1 );

		return $average_iil;
	}

	/**
	 * Iterate the $results array and find the average juice value. Note that the juice
	 * value is stored in the 'juice' key of the $results array.
	 *
	 * @param array $results The link statistics stored in the archive db table provided as an array.
	 *
	 * @return int The average number of manual internal links.
	 */
	public function get_average_juice( $results ) {

		// Init the $total_juice variable.
		$total_juice = 0;

		// Iterate the $results array and sum the manual internal links.
		foreach ( $results as $key => $result ) {
			$total_juice += $result->juice;
		}

		// Calculate the average number of manual internal links.
		$average_juice = $total_juice / count( $results );

		// Round the average number of manual internal links (no decimals).
		$average_juice = round( $average_juice, 1 );

		return $average_juice;
	}

	/**
	 * Iterate the $results array and find the number of successful responses (200 status code).
	 *
	 * @param array $results The HTTP responses data stored in the archive db table provided as an array.
	 *
	 * @return int The number of successful responses. (200 status code)
	 */
	public function get_successful_responses( $results ) {

		// Init the $successful_responses variable.
		$successful_responses = 0;

		// Iterate the $results array and add to the $successful_responses 1 for each successful response.
		foreach ( $results as $key => $result ) {
			if ( intval( $result->code, 10 ) === 200 ) {
				++$successful_responses;
			}
		}

		return $successful_responses;
	}

	/**
	 * Iterate the $results array and find the percentage of click performed on the automatic internal links.
	 *
	 * @param array $results The hits statistics stored in the archive db table provided as an array.
	 *
	 * @return int The percentage of click performed on the automatic internal links.
	 */
	public function get_hits_autolinks_percentage( $results ) {

		// Init the $total_mil variable.
		$total_ail_clicks = 0;

		// Iterate the $results array and sum the manual internal links.
		foreach ( $results as $key => $result ) {
			if ( intval( $result->link_type, 10 ) === 0 ) {
				++$total_ail_clicks;
			}
		}

		// Calculate the average number of manual internal links.
		$average_ail_clicks = $total_ail_clicks / count( $results ) * 100;

		// Round the average number of manual internal links (no decimals).
		$average_ail_clicks = round( $average_ail_clicks, 1 );

		return $average_ail_clicks;
	}

	/**
	 * Sanitize the data of the table provided as an escaped json string.
	 *
	 * @param string $table_data The table data provided as an escaped json string.
	 *
	 * @return array|bool
	 */
	public function sanitize_table_data( $table_data ) {

		// Unescape and decode the table data provided in json format.
		$table_data = json_decode( stripslashes( $table_data ) );

		// Verify if data property of the returned object is an array.
		if ( ! isset( $table_data ) || ! is_array( $table_data ) ) {
			return false;
		}

		foreach ( $table_data as $row_index => $row_data ) {

			// Verify if the table row data are provided as an array.
			if ( ! is_array( $row_data ) ) {
				return false;
			}

			// Sanitize all the cells data in the $row_data array.
			$table_data[ $row_index ] = array_map( 'sanitize_text_field', $row_data );

		}

		return $table_data;
	}

	/**
	 * Sanitize the data of an uploaded file.
	 *
	 * @param array $file The data of the uploaded file.
	 *
	 * @return array
	 */
	public function sanitize_uploaded_file( $file ) {

		return array(
			'name'     => sanitize_file_name( $file['name'] ),
			'type'     => $file['type'],
			'tmp_name' => $file['tmp_name'],
			'error'    => intval( $file['error'], 10 ),
			'size'     => intval( $file['size'], 10 ),
		);
	}

	/**
	 * Delete the statistics available in the following db tables:
	 *
	 * - wp_daim_anchors
	 * - wp_daim_archive
	 * - wp_daim_hits
	 * - wp_daim_http_status
	 * - wp_daim_juice
	 *
	 * @return void
	 */
	public function delete_statistics(){

		global $wpdb;

		// Delete the anchors db table content.
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}daim_anchors" );

		// Delete the archive db table content.
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}daim_archive" );

		// Delete the hits db table content.
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}daim_hits" );

		// Delete the http status db table content.
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}daim_http_status" );

		// Delete the juice db table content.
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}daim_juice" );

	}

	/**
	 * Send a request to the remote server to get the plugin information.
	 *
	 * These plugin information are used:
	 *
	 * - To include the plugin information for our custom plugin using the callback of the "plugins_api" filter.
	 * - To report the plugin as out of date if needed by adding information to the transient.
	 * - As a verification of the license. Since a response with an error is returned if the license is not valid.
	 *
	 * @return false|object
	 */
	public function fetch_remote_plugin_info() {

		// The transient expiration is set to 24 hours.
		$transient_expiration = 86400;

		$remote = get_transient( DAIM_WP_PLUGIN_UPDATE_INFO_TRANSIENT );

		/**
		 * If the transient does not exist, does not have a value, or has expired, then fetch the plugin information
		 * from the remote server on daext.com.
		 */
		if ( false === $remote ) {

			$license_provider = get_option( 'daim_license_provider' );
			$license_key      = get_option( 'daim_license_key' );

			// Prepare the body of the request.
			$body = wp_json_encode(
				array(
					'license_provider' => $license_provider,
					'license_key'      => $license_key,
					'slug'             => 'interlinks-manager',
					'domain'           => site_url(),
				)
			);

			$remote = wp_remote_post(
				DAIM_WP_PLUGIN_UPDATE_INFO_API_URL,
				array(
					'method'  => 'POST',
					'timeout' => 10,
					'body'    => $body,
					'headers' => array(
						'Content-Type' => 'application/json',
					),
				)
			);

			$response_code = wp_remote_retrieve_response_code( $remote );

			if (
				is_wp_error( $remote )
				|| 200 !== $response_code
				|| empty( wp_remote_retrieve_body( $remote ) )
			) {

				/**
				 * For valid response where the license has been verified, and it's invalid, save a specific
				 * 'invalid_license' error in the transient.
				 */
				$remote_body = json_decode( wp_remote_retrieve_body( $remote ) );
				if ( 403 === $response_code && 'invalid_license' === $remote_body->error ) {

					$error_res = new WP_Error( 'invalid_license', 'Invalid License' );
					set_transient( DAIM_WP_PLUGIN_UPDATE_INFO_TRANSIENT, $error_res, $transient_expiration );

				}else{

					/**
					 * With other error response codes save a generic error response in the transient.
					 */
					$error_res = new WP_Error( 'generic_error', 'Generic Error' );
					set_transient( DAIM_WP_PLUGIN_UPDATE_INFO_TRANSIENT, $error_res, $transient_expiration );

				}

				return $error_res;

			} else {

				/**
				 * With a valid license, save the plugin information in the transient and return the plugin information.
				 * Otherwise, save the error in the transient and return the error.
				 */

				$remote = json_decode( wp_remote_retrieve_body( $remote ) );

				// Check if the fields of a valid response are also set.
				if ( isset( $remote->name ) &&
				     isset( $remote->slug ) ) {
					set_transient( DAIM_WP_PLUGIN_UPDATE_INFO_TRANSIENT, $remote, $transient_expiration );
					return $remote;
				} else {
					$error_res = new WP_Error( 'generic_error', 'Generic Error' );
					set_transient( DAIM_WP_PLUGIN_UPDATE_INFO_TRANSIENT, $error_res, $transient_expiration );
					return $error_res;
				}

			}

		}

		return $remote;
	}

	/**
	 * Verify the license key. If the license is not valid display a message and return false.
	 *
	 * @return bool
	 */
	public function is_valid_license() {

		$plugin_info = get_transient( DAIM_WP_PLUGIN_UPDATE_INFO_TRANSIENT );

		if ( false === $plugin_info || is_wp_error( $plugin_info ) ) {
			return false;
		} else {
			return true;
		}

	}

	/**
	 * Display a notice to the user to activate the license.
	 *
	 * @return void
	 */
	public function display_license_activation_notice() {

		require_once $this->get( 'dir' ) . 'vendor/autoload.php';
		$plugin_update_checker = new PluginUpdateChecker(DAIM_PLUGIN_UPDATE_CHECKER_SETTINGS);

		if ( ! $plugin_update_checker->is_valid_license() ) {
			?>
			<div class="daim-license-activation-notice">
				<h2><?php esc_html_e( 'Activate Your License', 'interlinks-manager' ); ?></h2>
				<p><?php esc_html_e( "Please activate your license to enable plugin updates and access technical support. It's important to note that without updates, the plugin may stop functioning correctly when new WordPress versions are released. Furthermore, not receiving updates could expose your site to security issues.", 'interlinks-manager' ) . '</p>'; ?>
				<h4><?php esc_html_e( 'License Activation Steps', 'interlinks-manager' ); ?></h4>
				<ol>
					<li>
						<?php esc_html_e( 'If you purchased the plugin from daext.com, you can find your license key in your account at', 'interlinks-manager' ); ?>&nbsp<a href="https://daext.com/account/" target="_blank"><?php esc_html_e( 'https://daext.com/account/', 'interlinks-manager' ); ?></a>.
						<?php esc_html_e( 'For Envato Market purchases, use the item purchase code available in your', 'interlinks-manager' ); ?>&nbsp<strong><?php esc_html_e( 'Downloads', 'interlinks-manager' ); ?></strong>&nbsp<?php esc_html_e( 'area.', 'interlinks-manager' ); ?>
					</li>
					<li>
						<?php esc_html_e( 'Navigate to', 'interlinks-manager' ); ?>&nbsp<strong><?php esc_html_e( 'Options → License → License Management', 'interlinks-manager' ); ?></strong>&nbsp<?php esc_html_e( 'to enter your license key.', 'interlinks-manager' ); ?>
					</li>
					<li>
						<?php esc_html_e( 'Once in the', 'interlinks-manager' ); ?>&nbsp<strong><?php esc_html_e( 'License Management', 'interlinks-manager' ); ?></strong>&nbsp<?php esc_html_e( 'section, choose the appropriate license provider from the dropdown menu.', 'interlinks-manager' ); ?>
					</li>
					<li>
						<?php esc_html_e( 'Enter your license key into the provided field.', 'interlinks-manager' ); ?></li>
					<li>
						<?php esc_html_e( 'Click the', 'interlinks-manager' ); ?>&nbsp<strong><?php esc_html_e( 'Save settings', 'interlinks-manager' ); ?></strong>&nbsp<?php esc_html_e( 'button to activate your license.', 'interlinks-manager' ); ?>
					</li>
				</ol>
				<h4><?php esc_html_e( 'Reasons for This Notice', 'interlinks-manager' ); ?></h4>
				<p><?php esc_html_e( 'Here are the common reasons for this notification:', 'interlinks-manager' ) . '</p>'; ?>
				<ul>
					<li>
						<?php esc_html_e( 'A license key has not been provided.', 'interlinks-manager' ); ?></li>
					<li>
						<?php esc_html_e( 'The configured license key is invalid or expired.', 'interlinks-manager' ); ?></li>
					<li><?php esc_html_e( "The configured license key is already linked to other sites, and the license tier related to this key doesn't allow additional activations.", 'interlinks-manager' ); ?></li>
					<li>
						<?php echo esc_html__( 'Our server is temporarily unable to validate your license. To manually verify your license, click ', 'interlinks-manager' ) . '<a href="' . esc_url( $this->get_current_admin_page_verify_license_url() ) . '">' . esc_html__( 'this link', 'interlinks-manager' ) . '</a>' . esc_html__( '. A successful validation will dismiss this notice.', 'interlinks-manager' ); ?></li>
				</ul>
				<h4><?php esc_html_e( 'Support and Assistance', 'interlinks-manager' ); ?></h4>
				<p><?php esc_html_e( "If you're having trouble activating your license or locating your key, visit our", 'interlinks-manager' ); ?>&nbsp<a href="https://daext.com/support/" target="_blank"><?php esc_html_e( 'support page', 'interlinks-manager' ); ?></a>&nbsp<?php esc_html_e( 'for help.', 'interlinks-manager' ); ?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Get the current page URL with the daim_verify_license=1 parameter and a nonce.
	 *
	 * @return string|null
	 */
	public function get_current_admin_page_verify_license_url() {

		// Generate a nonce for the action "daim_verify_license".
		$nonce = wp_create_nonce( 'daim_verify_license' );

		// Add query parameters 'daim_verify_license' and 'daim_verify_license_nonce'.
		return admin_url(
			'admin.php?page=' . $this->get( 'slug' ) . '-dashboard' . '&' . http_build_query(
				array(
					'daim_verify_license'       => 1,
					'daim_verify_license_nonce' => $nonce,
				)
			)
		);
	}

	/**
	 * Converts relative URLs to absolute URLs.
	 *
	 * The following type of URLs are supported:
	 *
	 * - Absolute URLs | E.g., "https://example.com/post/"
	 * - Protocol-relative URLs | E.g., "//localhost/image.jpg".
	 * - Root-relative URLs | E.g., "/post/".
	 * - Fragment-only URLs | E.g., "#section1".
	 * - Relative URLs with relative paths. | E.g., "./post/", "../post", "../../post".
	 * - Page-relative URLs | E.g., "post/".
	 *
	 * @param String $relative_url The relative URL that should be converted.
	 * @param Int $post_id The ID of the post.
	 *
	 * @return mixed|string
	 */
	public function relative_to_absolute_url( $relative_url, $post_id ) {

		$post_permalink = get_permalink( $post_id );

		/**
		 * If already an absolute URL, return as is.
		 *
		 * -------------------------------------------------------------------------------------------------------------
		 */
		if ( empty( $relative_url ) || wp_parse_url( $relative_url, PHP_URL_SCHEME ) ) {
			return $relative_url;
		}

		// Get the site URL. Ensure trailing slash for proper resolution.
		$base_url = home_url( '/' );

		// Parse base URL.
		$base_parts = wp_parse_url( $base_url );

		/**
		 * Protocol-relative URL | If it's a protocol-relative URL (e.g., //example.com/image.jpg), add "https:" as
		 * default.
		 *
		 * -------------------------------------------------------------------------------------------------------------
		 */
		if ( str_starts_with( $relative_url, '//' ) ) {
			if ( $this->is_site_using_https() ) {
				return 'https:' . $relative_url;
			} else {
				return 'http:' . $relative_url;
			}
		}

		/**
		 * Root-relative URLs | Handle root-relative URLs (e.g., "/some-page/").
		 *
		 * -------------------------------------------------------------------------------------------------------------
		 */
		if ( str_starts_with( $relative_url, '/' ) ) {
			return $base_parts['scheme'] . '://' . $base_parts['host'] . $relative_url;
		}

		/**
		 * Fragment identifier | Handle fragment-only URLs (e.g., "#section").
		 *
		 * -------------------------------------------------------------------------------------------------------------
		 */
		if ( str_starts_with( $relative_url, '#' ) ) {
			return $post_permalink . $relative_url;
		}

		/**
		 * Relative URLs with relative paths.
		 *
		 * Handles the relative URLs with relative paths like "./page", "../page", and "../../page'.
		 *
		 * Check if the relative URLs starts with "./", or "../", or subsequent levels like "../../".
		 * If it does, use the exact relative URL to retrieve and return the absolute URL.
		 *
		 * -------------------------------------------------------------------------------------------------------------
		 */

		// This conditional supports all the levels like '../../', etc.
		if ( str_starts_with( $relative_url, './' ) || str_starts_with( $relative_url, '../' ) ) {

			/**
			 * Here, based on the type of relative URL, we move up one or more levels in the directory tree
			 * to create the correct absolute URL.
			 *
			 * Note that the URL on which we should move levels is stored in the $current_url variable.
			 */
			$post_permalink_parts = wp_parse_url( $post_permalink );

			// Ensure we have a valid base URL.
			if ( ! isset( $post_permalink_parts['scheme'], $post_permalink_parts['host'], $post_permalink_parts['path'] ) ) {
				return $relative_url; // Return as-is if current URL is invalid.
			}

			// Get the directory of the current URL.
			$base_path = rtrim( $post_permalink_parts['path'], '/' );

			// Split the base path into segments.
			$base_parts = explode( '/', $base_path );

			// Split the relative URL into segments.
			$relative_parts = explode( '/', $relative_url );

			// Process the relative path.
			foreach ( $relative_parts as $part ) {
				if ( '..' === $part ) {
					// Move up one directory level.
					if ( count( $base_parts ) > 1 ) {
						array_pop( $base_parts );
					}
				} elseif ( '.' !== $part && '' !== $part ) {
					// Append valid segments.
					$base_parts[] = $part;
				}
			}

			// If there is a trailing slash in the permalink add it to the $trailing_slash string.
			$trailing_slash = str_ends_with( $relative_url, '/' ) ? '/' : '';

			// Construct the final absolute URL and return it.
			return $post_permalink_parts['scheme'] . '://' . $post_permalink_parts['host'] . implode( '/', $base_parts ) . $trailing_slash;

		}

		/**
		 * Page-relative URLs.
		 *
		 * Handle relative URLs without a leading slash (page-relative URLs like "example-post/").
		 */
		$base_parts = wp_parse_url( $post_permalink );
		return $base_parts['scheme'] . '://' . $base_parts['host'] . $base_parts['path'] . $relative_url;

	}

	/**
	 * A regex to match manual and auto internal links.
	 *
	 * Note that relative URLs are also supported.
	 *
	 * @return string The regex to match manual and auto internal links.
	 */
	public function manual_and_auto_internal_links_regex() {

		/**
		 * Get the website URL and escape the regex character. # and
		 * whitespace ( used with the 'x' modifier ) are not escaped, thus
		 * should not be included in the $site_url string
		 */
		$site_url = preg_quote( get_home_url(), '/' );

		// Get the website URL without the protocol part.
		$site_url_without_protocol_part = preg_quote( wp_parse_url( get_home_url(), PHP_URL_HOST ), '/' );

		return '{<a                                                     #1 Begin the element a start-tag
            [^>]+                                                       #2 Any character except > at least one time
            href\s*=\s*                                                 #3 Equal may have whitespaces on both sides
            ([\'"]?)                                                    #4 Match double quotes, single quote or no quote ( captured for the backreference \1 )
	        (                                                           #5 Capture group for both full and relative URLs
	            (?:' . $site_url . '[^\'">\s]*)                         #5a Match full URL starting with $site_url ( captured )
	            |                                                       # OR
	            (?!//)(?:\/|\.{1,2}\/)[^\'">\s]*                        #5b Match relative URLs (must start with /, ./, or ../) ( captured )
	                        |                                           # OR
                \#[^\'">\s]*                                            #5c Match fragment-only URLs (e.g., #section2) ( captured )
                            |                                           # OR
				(?!//)[^\'"\s<>:]+                                      #5d Match page-relative URLs (must not contain "://") (captured)
				|                                                       # OR
                (?://' . $site_url_without_protocol_part . '[^\'">\s]*) #5e Match protocol-relative URLs with $site_url (captured)
	        )    
            \1                                                          #6 Backreference that matches the href value delimiter matched at line 4
            [^>]*                                                       #7 Any character except > zero or more times
            >                                                           #8 End of the start-tag
            (.*?)                                                       #9 Link text or nested tags. After the dot ( enclose in parenthesis ) negative lookbehinds can be applied to avoid specific stuff inside the link text or nested tags. Example with single negative lookbehind (.(?<!word1))*? Example with multiple negative lookbehind (.(?<!word1)(?<!word2)(?<!word3))*?
            <\/a\s*>                                                    #10 Element a end-tag with optional white-spaces characters before the >
            }ix';
	}

	/**
	 * Checks if the WordPress site URL is using the HTTPS protocol.
	 *
	 * @return bool Returns true if the site URL starts with https:// (i.e., the site is using HTTPS). false if the site
	 * URL does not start with https:// (i.e., the site is using HTTP).
	 */
	public function is_site_using_https() {

		$site_url = get_option( 'siteurl' );

		return ( str_starts_with( $site_url, 'https://' ) );
	}

	/**
	 * Register the support of the 'custom-fields' to all the post type with UI.
	 *
	 * The 'custom-fields' support is required by the sidebar components that use meta data. Without the
	 * 'custom-fields' support associated with the posts, the following meta data can't be used by the sidebar
	 * components and a JavaScript error breaks the editor:
	 *
	 * - _daim_default_seo_power
	 * - _daim_enable_ail
	 *
	 * Note that the problem solved by this method occurs only when a post type is registered and the "supports" array
	 * doesn't include the 'custom-fields' value.
	 *
	 * See: https://developer.wordpress.org/reference/functions/add_post_type_support/
	 */
	public function register_support_on_post_types() {

		// Get the post types with UI.
		$available_post_types_a = get_post_types(
			array(
				'show_ui' => true,
			)
		);

		// Remove the 'attachment' post type.
		$available_post_types_a = array_diff( $available_post_types_a, array( 'attachment' ) );

		// Add the 'custom-fields' support to the post types with UI.
		foreach ( $available_post_types_a as $available_post_type ) {
			add_post_type_support( $available_post_type, 'custom-fields' );
		}
	}

}
