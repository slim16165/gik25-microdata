<?php
/**
 * Used to generate the data used in the options menu powered by React.
 *
 * @package interlinks-manager
 */

/**
 * This menu_options_configuration() method of this class is used to generate the data used in the options menu powered
 * by React.
 */
class Daim_Menu_Options {

	/**
	 * The singleton instance of the class.
	 *
	 * @var null
	 */
	protected static $instance = null;

	/**
	 * Get the singleton instance of the class.
	 *
	 * @return self|null
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Returns an array with the data used by the React based options menu to initialize the options.
	 *
	 * @return array[]
	 */
	public function menu_options_configuration() {

		// Get the public post types that have a UI.
		$args               = array(
			'public'  => true,
			'show_ui' => true,
		);
		$post_types_with_ui = get_post_types( $args );
		unset( $post_types_with_ui['attachment'] );
		$post_types_select_options = array();
		foreach ( $post_types_with_ui as $post_type ) {
			$post_types_select_options[] = array(
				'value' => $post_type,
				'text'  => $post_type,
			);
		}

		// The select multiple options of the "Protected Tags" option.
		$protected_tags_html_tags = array(
			'a',
			'abbr',
			'acronym',
			'address',
			'applet',
			'area',
			'article',
			'aside',
			'audio',
			'b',
			'base',
			'basefont',
			'bdi',
			'bdo',
			'big',
			'blockquote',
			'body',
			'br',
			'button',
			'canvas',
			'caption',
			'center',
			'cite',
			'code',
			'col',
			'colgroup',
			'datalist',
			'dd',
			'del',
			'details',
			'dfn',
			'dir',
			'div',
			'dl',
			'dt',
			'em',
			'embed',
			'fieldset',
			'figcaption',
			'figure',
			'font',
			'footer',
			'form',
			'frame',
			'frameset',
			'h1',
			'h2',
			'h3',
			'h4',
			'h5',
			'h6',
			'head',
			'header',
			'hgroup',
			'hr',
			'html',
			'i',
			'iframe',
			'img',
			'input',
			'ins',
			'kbd',
			'keygen',
			'label',
			'legend',
			'li',
			'link',
			'map',
			'mark',
			'menu',
			'meta',
			'meter',
			'nav',
			'noframes',
			'noscript',
			'object',
			'ol',
			'optgroup',
			'option',
			'output',
			'p',
			'param',
			'pre',
			'progress',
			'q',
			'rp',
			'rt',
			'ruby',
			's',
			'samp',
			'script',
			'section',
			'select',
			'small',
			'source',
			'span',
			'strike',
			'strong',
			'style',
			'sub',
			'summary',
			'sup',
			'table',
			'tbody',
			'td',
			'textarea',
			'tfoot',
			'th',
			'thead',
			'time',
			'title',
			'tr',
			'tt',
			'u',
			'ul',
			'var',
			'video',
			'wbr',
		);

		$protected_tags_html_tags_select_options = array();
		foreach ( $protected_tags_html_tags as $protected_tags_html_tag ) {
			$protected_tags_html_tags_select_options[] = array(
				'value' => $protected_tags_html_tag,
				'text'  => $protected_tags_html_tag,
			);
		}

		$protected_gutenberg_blocks_select_options = array(
			array(
				'value' => 'Paragraph',
				'text'  => 'paragraph',
			),
			array(
				'value' => 'image',
				'text'  => 'Image',
			),
			array(
				'value' => 'heading',
				'text'  => 'Heading',
			),
			array(
				'value' => 'gallery',
				'text'  => 'Gallery',
			),
			array(
				'value' => 'list',
				'text'  => 'List',
			),
			array(
				'value' => 'audio',
				'text'  => 'Audio',
			),
			array(
				'value' => 'cover-image',
				'text'  => 'Cover Image',
			),
			array(
				'value' => 'subhead',
				'text'  => 'Subhead',
			),
			array(
				'value' => 'video',
				'text'  => 'Video',
			),
			array(
				'value' => 'preformatted',
				'text'  => 'Preformatted',
			),
			array(
				'value' => 'pullquote',
				'text'  => 'Pullquote',
			),
			array(
				'value' => 'table',
				'text'  => 'Table',
			),
			array(
				'value' => 'button',
				'text'  => 'Button',
			),
			array(
				'value' => 'columns',
				'text'  => 'Columns',
			),
			array(
				'value' => 'more',
				'text'  => 'More',
			),
			array(
				'value' => 'nextpage',
				'text'  => 'Page Break',
			),
			array(
				'value' => 'separator',
				'text'  => 'Separator',
			),
			array(
				'value' => 'spacer',
				'text'  => 'Spacer',
			),
			array(
				'value' => 'shortcode',
				'text'  => 'Shortcode',
			),
			array(
				'value' => 'categories',
				'text'  => 'Categories',
			),
			array(
				'value' => 'latest-posts',
				'text'  => 'Latest Posts',
			),
			array(
				'value' => 'embed',
				'text'  => 'Embed',
			),
			array(
				'value' => 'core-embed/twitter',
				'text'  => 'Twitter',
			),
			array(
				'value' => 'core-embed/facebook',
				'text'  => 'Facebook',
			),
			array(
				'value' => 'core-embed/instagram',
				'text'  => 'Instagram',
			),
			array(
				'value' => 'core-embed/wordpress',
				'text'  => 'WordPress',
			),
			array(
				'value' => 'core-embed/soundcloud',
				'text'  => 'SoundCloud',
			),
			array(
				'value' => 'core-embed/spotify',
				'text'  => 'Spotify',
			),
			array(
				'value' => 'core-embed/flickr',
				'text'  => 'Flickr',
			),
			array(
				'value' => 'core-embed/vimeo',
				'text'  => 'Vimeo',
			),
			array(
				'value' => 'core-embed/animoto',
				'text'  => 'Animoto',
			),
			array(
				'value' => 'core-embed/cloudup',
				'text'  => 'Cloudup',
			),
			array(
				'value' => 'core-embed/collegehumor',
				'text'  => 'CollegeHumor',
			),
			array(
				'value' => 'core-embed/dailymotion',
				'text'  => 'DailyMotion',
			),
			array(
				'value' => 'core-embed/funnyordie',
				'text'  => 'Funny or Die',
			),
			array(
				'value' => 'core-embed/hulu',
				'text'  => 'Imgur',
			),
			array(
				'value' => 'core-embed/issuu',
				'text'  => 'Issuu',
			),
			array(
				'value' => 'core-embed/kickstarter',
				'text'  => 'Kickstarter',
			),
			array(
				'value' => 'core-embed/meetup-com',
				'text'  => 'Meetup.com',
			),
			array(
				'value' => 'core-embed/mixcloud',
				'text'  => 'Mixcloud',
			),
			array(
				'value' => 'core-embed/photobucket',
				'text'  => 'Photobucket',
			),
			array(
				'value' => 'core-embed/polldaddy',
				'text'  => 'Polldaddy',
			),
			array(
				'value' => 'core-embed/reddit',
				'text'  => 'Reddit',
			),
			array(
				'value' => 'core-embed/reverbnation',
				'text'  => 'ReverbNation',
			),
			array(
				'value' => 'core-embed/screencast',
				'text'  => 'Screencast',
			),
			array(
				'value' => 'core-embed/smugmug',
				'text'  => 'SmugMug',
			),
			array(
				'value' => 'core-embed/ted',
				'text'  => 'Ted',
			),
			array(
				'value' => 'core-embed/tumblr',
				'text'  => 'Tumblr',
			),
			array(
				'value' => 'core-embed/videopress',
				'text'  => 'VideoPress',
			),
			array(
				'value' => 'core-embed/wordpress-tv',
				'text'  => 'WordPress.tv',
			),
		);

		$default_category_id_select_options = array(
			array(
				'value' => '0',
				'text'  => 'None',
			),
		);

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$category_a = $wpdb->get_results(
			"SELECT category_id, name FROM {$wpdb->prefix}daim_category ORDER BY category_id DESC"
			, ARRAY_A );

		foreach ( $category_a as $key => $category ) {
			$default_category_id_select_options[] = array(
				'value' => $category['category_id'],
				'text'  => $category['name'],
			);
		}

		// Get the public post types that have a UI.
		$args               = array(
			'public'  => true,
			'show_ui' => true,
		);
		$post_types_with_ui = get_post_types( $args );
		unset( $post_types_with_ui['attachment'] );
		$post_types_select_options = array();
		foreach ( $post_types_with_ui as $post_type ) {
			$post_type_obj               = get_post_type_object( $post_type );
			$post_types_select_options[] = array(
				'value' => $post_type,
				'text'  => $post_type_obj->label,
			);
		}

		// Categories select options.
		$categories                = get_categories(
			array(
				'hide_empty' => 0,
				'orderby'    => 'term_id',
				'order'      => 'DESC',
			)
		);
		$categories_select_options = array();
		foreach ( $categories as $category ) {
			$categories_select_options[] = array(
				'value' => (string) $category->term_id,
				'text'  => $category->name,
			);
		}

		// Tags select options.
		$tags                = get_categories(
			array(
				'hide_empty' => 0,
				'orderby'    => 'term_id',
				'order'      => 'DESC',
				'taxonomy'   => 'post_tag',
			)
		);
		$tags_select_options = array();
		foreach ( $tags as $tag ) {
			$tags_select_options[] = array(
				'value' => (string) $tag->term_id,
				'text'  => $tag->name,
			);
		}

		// Term groups select options.
		$tags_select_options[] = array(
			'value' => 0,
			'text'  => __( 'None', 'interlinks-manager'),
		);

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$term_group_a              = $wpdb->get_results(
			"SELECT term_group_id, name FROM {$wpdb->prefix}daim_term_group ORDER BY term_group_id DESC"
			, ARRAY_A );
		$term_group_select_options = array();
		foreach ( $term_group_a as $key => $term_group ) {
			$term_group_select_options[] = array(
				'value' => $term_group['term_group_id'],
				'text'  => stripslashes( $term_group['name'] ),
			);
		}

		// Pagination select options.
		$pagination_select_options = array(
			array(
				'value' => '10',
				'text'  => '10',
			),
			array(
				'value' => '20',
				'text'  => '20',
			),
			array(
				'value' => '30',
				'text'  => '30',
			),
			array(
				'value' => '40',
				'text'  => '40',
			),
			array(
				'value' => '50',
				'text'  => '50',
			),
			array(
				'value' => '60',
				'text'  => '60',
			),
			array(
				'value' => '70',
				'text'  => '70',
			),
			array(
				'value' => '80',
				'text'  => '80',
			),
			array(
				'value' => '90',
				'text'  => '90',
			),
			array(
				'value' => '100',
				'text'  => '100',
			),
		);

		// This variable includes all the data used by the configuration options.
		$configuration = array(

			array(
				'title'       => __( 'Automatic Links', 'interlinks-manager' ),
				'description' => __( 'Configure the application of the automatic internal links.', 'interlinks-manager' ),
				'cards'       => array(
					array(
						'title'   => __( 'Options', 'interlinks-manager' ),
						'options' => array(
							array(
								'name'    => 'daim_default_enable_ail_on_post',
								'label'   => __( 'Enable AIL', 'interlinks-manager' ),
								'type'    => 'toggle',
								'tooltip' => __( 'This option determines the default status of the &quot;Enable AIL&quot; option available in the &quot;Interlinks Options&quot; editor tool.', 'interlinks-manager' ),
								'help'    => __(
									'Enable the application of the automatic links.',
									'interlinks-manager'
								),
							),
							array(
								'name'      => 'daim_filter_priority',
								'label'     => __( 'Filter Priority', 'interlinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'This option determines the priority of the filter used to apply the AIL. A lower number corresponds with an earlier execution.',
									'interlinks-manager'
								),
								'help'      => __( 'Set the priority of the filter used to apply the automatic links.', 'interlinks-manager' ),
								'rangeMin'  => - 2147483648,
								'rangeMax'  => 2147483646,
								'rangeStep' => 1,
							),
							array(
								'name'    => 'daim_ail_test_mode',
								'label'   => __( 'Test Mode', 'interlinks-manager' ),
								'type'    => 'toggle',
								'tooltip' => __( 'With the test mode enabled the AIL will be applied to your posts, pages or custom post types only if the user that is requesting the posts, pages or custom post types has the capability defined with the &quot;AIL Menu&quot; option.', 'interlinks-manager' ),
								'help'    => __(
									'Apply the automatic links only when the site is viewed by privileged users.',
									'interlinks-manager'
								),
							),
							array(
								'name'    => 'daim_random_prioritization',
								'label'   => __( 'Random Prioritization', 'interlinks-manager' ),
								'type'    => 'toggle',
								'tooltip' => __( "With this option enabled the order used to apply the AIL with the same priority is randomized on a per-post basis. With this option disabled the order used to apply the AIL with the same priority is the order used to add them in the back-end. It's recommended to enable this option for a better distribution of the AIL.", 'interlinks-manager' ),
								'help'    => __(
									'Improve the distribution of the automatic links.',
									'interlinks-manager'
								),
							),
							array(
								'name'    => 'daim_ignore_self_ail',
								'label'   => __( 'Ignore Self AIL', 'interlinks-manager' ),
								'type'    => 'toggle',
								'tooltip' => __( 'With this option enabled, the AIL, which have as a target the post where they should be applied, will be ignored.', 'interlinks-manager' ),
								'help'    => __(
									'Prevent the application of automatic links that targets the post where they should be applied.',
									'interlinks-manager'
								),
							),
							array(
								'name'          => 'daim_categories_and_tags_verification',
								'label'         => __( 'Categories & Tags Verification', 'interlinks-manager' ),
								'type'          => 'select',
								'tooltip'       => __( 'If &quot;Post&quot; is selected categories and tags will be verified only in the &quot;post&quot; post type, if &quot;Any&quot; is selected categories and tags will be verified in any post type.', 'interlinks-manager' ),
								'selectOptions' => array(
									array(
										'value' => 'post',
										'text'  => 'Post',
									),
									array(
										'value' => 'any',
										'text'  => 'Any',
									),
								),
								'help'          => __(
									'Select how to verify the categories and tags.',
									'interlinks-manager'
								),
							),
							array(
								'name'    => 'daim_general_limit_mode',
								'label'   => __( 'General Limit Mode', 'interlinks-manager' ),
								'type'    => 'select',
								'tooltip' => __( 'If &quot;Auto&quot; is selected the maximum number of AIL per post is automatically generated based on the length of the post, in this case the &quot;General Limit (Characters per AIL)&quot; option is used. If &quot;Manual&quot; is selected the maximum number of AIL per post is equal to the value of the &quot;General Limit (Amount)&quot; option.', 'interlinks-manager' ),
								'selectOptions' => array(
									array(
										'value' => '0',
										'text'  => __('Auto', 'interlinks-manager'),
									),
									array(
										'value' => '1',
										'text'  => __('Manual', 'interlinks-manager'),
									),
								),
								'help'    => __(
									'Select how the general limit of automatic links per post should be determined.',
									'interlinks-manager'
								),
							),
							array(
								'name'      => 'daim_characters_per_autolink',
								'label'     => __( 'General Limit (Characters per AIL)', 'interlinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __( 'Set the ideal number of characters per internal links.', 'interlinks-manager' ),
								'help'      => __(
									'Set maximum number of automatic links per post.',
									'interlinks-manager'
								),
								'rangeMin'  => 1,
								'rangeMax'  => 50000,
								'rangeStep' => 1,
							),
							array(
								'name'      => 'daim_max_number_autolinks_per_post',
								'label'     => __( 'General Limit (Amount)', 'interlinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __( 'This value determines the maximum number of AIL per post when the &quot;General Limit Mode&quot; option is set to &quot;Manual&quot;.', 'interlinks-manager' ),
								'help'      => __(
									'Set maximum number of automatic links per post.',
									'interlinks-manager'
								),
								'rangeMin'  => 1,
								'rangeMax'  => 500,
								'rangeStep' => 1,
							),
							array(
								'name'    => 'daim_general_limit_subtract_mil',
								'label'   => __( 'General Limit (Subtract MIL)', 'interlinks-manager' ),
								'type'    => 'toggle',
								'tooltip' => __( 'With this option enabled the number of MIL included in the post will be subtracted from the maximum number of AIL allowed in the post.', 'interlinks-manager' ),
								'help'    => __(
									'Subtract the number of manual internal links from the general limit.',
									'interlinks-manager'
								),
							),
							array(
								'name'      => 'daim_same_url_limit',
								'label'     => __( 'Same URL Limit', 'interlinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __( 'This option limits the number of AIL with the same URL to a specific value.', 'interlinks-manager' ),
								'help'      => __(
									'Set the maximum number of automatic links with the same URL.',
									'interlinks-manager'
								),
								'rangeMin'  => 1,
								'rangeMax'  => 500,
								'rangeStep' => 1,
							),
						),
					),
					array(
						'title'   => __( 'Protected Elements', 'interlinks-manager' ),
						'options' => array(
							array(
								'name'          => 'daim_protected_tags',
								'label'         => __( 'Tags', 'interlinks-manager' ),
								'type'          => 'select-multiple',
								'tooltip'       => __(
									'With this option you are able to determine in which HTML tags the AIL should not be applied.',
									'interlinks-manager'
								),
								'selectOptions' => $protected_tags_html_tags_select_options,
								'help'          => __( 'Select the tags where the automatic links should not be applied.', 'interlinks-manager' ),
							),
							array(
								'name'          => 'daim_protected_gutenberg_blocks',
								'label'         => __( 'Gutenberg Blocks', 'interlinks-manager' ),
								'type'          => 'select-multiple',
								'tooltip'       => __(
									'With this option you are able to determine in which Gutenberg blocks the AIL should not be applied.',
									'interlinks-manager'
								),
								'selectOptions' => $protected_gutenberg_blocks_select_options,
								'help'          => __( 'Select the Gutenberg blocks where the automatic links should not be applied.', 'interlinks-manager' ),
							),
							array(
								'name'    => 'daim_protected_gutenberg_custom_blocks',
								'label'   => __( 'Gutenberg Custom Blocks', 'interlinks-manager' ),
								'type'    => 'text',
								'tooltip' => __(
									'Enter a list of Gutenberg custom blocks, separated by a comma.',
									'interlinks-manager'
								),
								'help'    => __( 'Add the Gutenberg custom blocks where the automatic links should not be applied.', 'interlinks-manager' ),
							),
							array(
								'name'    => 'daim_protected_gutenberg_custom_void_blocks',
								'label'   => __( 'Gutenberg Custom Void Blocks', 'interlinks-manager' ),
								'type'    => 'text',
								'tooltip' => __(
									'Enter a list of Gutenberg custom void blocks, separated by a comma.',
									'interlinks-manager'
								),
								'help'    => __( 'Add the Gutenberg custom void blocks where the automatic links should not be applied.', 'interlinks-manager' ),
							),
						),
					),
					array(
						'title'   => __( 'Defaults', 'interlinks-manager' ),
						'options' => array(
							array(
								'name'          => 'daim_default_category_id',
								'label'         => __( 'Category', 'interlinks-manager' ),
								'type'          => 'select',
								'tooltip'       => __(
									'The category of the AIL. This option determines the default value of the "Category" field available in the "AIL" menu and in the "Wizard" menu.',
									'interlinks-manager'
								),
								'selectOptions' => $default_category_id_select_options,
								'help'          => __( 'Select the category of the automatic link.', 'interlinks-manager' ),
							),
							array(
								'name'    => 'daim_default_title',
								'label'   => __( 'Title', 'interlinks-manager' ),
								'type'    => 'text',
								'tooltip' => __(
									'The title attribute of the link automatically generated on the keyword. This option determines the default value of the &quot;Title&quot; field available in the &quot;AIL&quot; menu and is also used for the AIL generated with the &quot;Wizard&quot; menu.',
									'interlinks-manager'
								),
								'help'    => __( 'Enter the title of the automatic link.', 'interlinks-manager' ),
							),
							array(
								'name'    => 'daim_default_open_new_tab',
								'label'   => __( 'Open New Tab', 'interlinks-manager' ),
								'type'    => 'toggle',
								'tooltip' => __( 'If you select &quot;Yes&quot; the link generated on the defined keyword opens the linked document in a new tab. This option determines the default value of the &quot;Open New Tab&quot; field available in the &quot;AIL&quot; menu and is also used for the AIL generated with the &quot;Wizard&quot; menu.', 'interlinks-manager' ),
								'help'    => __(
									'Open the linked document in a new tab.',
									'interlinks-manager'
								),
							),
							array(
								'name'    => 'daim_default_use_nofollow',
								'label'   => __( 'Use Nofollow', 'interlinks-manager' ),
								'type'    => 'toggle',
								'tooltip' => __( 'If you select &quot;Yes&quot; the link generated on the defined keyword will include the rel=&quot;nofollow&quot; attribute. This option determines the default value of the &quot;Use Nofollow&quot; field available in the &quot;AIL&quot; menu and is also used for the AIL generated with the &quot;Wizard&quot; menu.', 'interlinks-manager' ),
								'help'    => __(
									'Add the rel="nofollow" attribute to the link.',
									'interlinks-manager'
								),
							),
							array(
								'name'          => 'daim_default_activate_post_types',
								'label'         => __( 'Post Types', 'interlinks-manager' ),
								'type'          => 'select-multiple',
								'tooltip'       => __(
									'With this option you are able to determine in which post types the defined keywords will be automatically converted to a link. This option determines the default value of the &quot;Post Types&quot; field available in the &quot;AIL&quot; menu and is also used for the AIL generated with the &quot;Wizard&quot; menu.',
									'interlinks-manager'
								),
								'selectOptions' => $post_types_select_options,
								'help'          => __( 'Select the post types where the automatic links should be added.', 'interlinks-manager' ),
							),
							array(
								'name'          => 'daim_default_categories',
								'label'         => __( 'Categories', 'interlinks-manager' ),
								'type'          => 'select-multiple',
								'tooltip'       => __(
									'With this option you are able to determine in which categories the defined keywords will be automatically converted to a link. Leave this field empty to convert the keyword in any category. This option determines the default value of the &quot;Categories&quot; field available in the &quot;AIL&quot; menu and is also used for the AIL generated with the &quot;Wizard&quot; menu.',
									'interlinks-manager'
								),
								'selectOptions' => $categories_select_options,
								'help'          => __( 'Select the categories where the automatic links should be added.', 'interlinks-manager' ),
							),
							array(
								'name'          => 'daim_default_tags',
								'label'         => __( 'Tags', 'interlinks-manager' ),
								'type'          => 'select-multiple',
								'tooltip'       => __(
									'With this option you are able to determine in which tags the defined keywords will be automatically converted to a link. Leave this field empty to convert the keyword in any tag. This option determines the default value of the &quot;Tags&quot; field available in the &quot;AIL&quot; menu and is also used for the AIL generated with the &quot;Wizard&quot; menu.',
									'interlinks-manager'
								),
								'selectOptions' => $tags_select_options,
								'help'          => __( 'todo', 'interlinks-manager' ),
							),
							array(
								'name'          => 'daim_default_term_group_id',
								'label'         => __( 'Term Group', 'interlinks-manager' ),
								'type'          => 'select',
								'tooltip'       => __(
									'The terms that will be compared with the ones available on the posts where the AIL are applied. Please note that when a term group is selected the &quot;Categories&quot; and &quot;Tags&quot; options will be ignored. This option determines the default value of the &quot;Term Group&quot; field available in the &quot;AIL&quot; menu and is also used for the AIL generated with the &quot;Wizard&quot; menu.',
									'interlinks-manager'
								),
								'selectOptions' => $term_group_select_options,
								'help'          => __( 'Select the tags where the automatic links should be added.', 'interlinks-manager' ),
							),
							array(
								'name'    => 'daim_default_case_insensitive_search',
								'label'   => __( 'Case Insensitive Search', 'interlinks-manager' ),
								'type'    => 'toggle',
								'tooltip' => __( 'If you select "Yes" your keyword will match both lowercase and uppercase variations. This option determines the default value of the "Case Insensitive Search" field available in the "AIL" menu and is also used for the AIL generated with the "Wizard" menu.', 'interlinks-manager' ),
								'help'    => __(
									'Enable the case insensitive search.',
									'interlinks-manager'
								),
							),
							array(
								'name'          => 'daim_default_string_before',
								'label'         => __( 'Left Boundary', 'interlinks-manager' ),
								'type'          => 'select',
								'tooltip'       => __(
									'Use this option to match keywords preceded by a generic boundary or by a specific character. This option determines the default value of the "Left Boundary" field available in the "AIL" menu and is also used for the AIL generated with the "Wizard" menu.',
									'interlinks-manager'
								),
								'selectOptions' => array(
									array(
										'value' => '1',
										'text'  => __( 'Generic', 'interlinks-manager' ),
									),
									array(
										'value' => '2',
										'text'  => __( 'White Space', 'interlinks-manager' ),
									),
									array(
										'value' => '3',
										'text'  => __( 'Comma', 'interlinks-manager' ),
									),
									array(
										'value' => '4',
										'text'  => __( 'Point', 'interlinks-manager' ),
									),
									array(
										'value' => '5',
										'text'  => __( 'None', 'interlinks-manager' ),
									),
								),
								'help'          => __( 'Select the boundary or character that should precede the keyword.', 'interlinks-manager' ),
							),
							array(
								'name'          => 'daim_default_string_after',
								'label'         => __( 'Right Boundary', 'interlinks-manager' ),
								'type'          => 'select',
								'tooltip'       => __(
									'Use this option to match keywords followed by a generic boundary or by a specific character. This option determines the default value of the "Right Boundary" field available in the "AIL" menu and is also used for the AIL generated with the "Wizard" menu.',
									'interlinks-manager'
								),
								'selectOptions' => array(
									array(
										'value' => '1',
										'text'  => __( 'Generic', 'interlinks-manager' ),
									),
									array(
										'value' => '2',
										'text'  => __( 'White Space', 'interlinks-manager' ),
									),
									array(
										'value' => '3',
										'text'  => __( 'Comma', 'interlinks-manager' ),
									),
									array(
										'value' => '4',
										'text'  => __( 'Point', 'interlinks-manager' ),
									),
									array(
										'value' => '5',
										'text'  => __( 'None', 'interlinks-manager' ),
									),
								),
								'help'          => __( 'Select the boundary or character that should follow the keyword.', 'interlinks-manager' ),
							),
							array(
								'name'    => 'daim_default_keyword_before',
								'label'   => __( 'Keyword Before', 'interlinks-manager' ),
								'type'    => 'text',
								'tooltip' => __(
									'Use this option to match occurences preceded by a specific string. This option determines the default value of the "Keyword Before" field available in the "AIL" menu and is also used for the AIL generated with the "Wizard" menu.',
									'interlinks-manager'
								),
								'help'    => __( 'Set the string that should precede the keyword.', 'interlinks-manager' ),
							),
							array(
								'name'    => 'daim_default_keyword_after',
								'label'   => __( 'Keyword After', 'interlinks-manager' ),
								'type'    => 'text',
								'tooltip' => __(
									'Use this option to match occurrences followed by a specific string. This option determines the default value of the "Keyword After" field available in the "AIL" menu and is also used for the AIL generated with the "Wizard" menu.',
									'interlinks-manager'
								),
								'help'    => __( 'Set the string that should follow the keyword.', 'interlinks-manager' ),
							),
							array(
								'name'      => 'daim_default_max_number_autolinks_per_keyword',
								'label'     => __( 'Limit', 'interlinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'With this option you can determine the maximum number of matches of the defined keyword automatically converted to a link. This option determines the default value of the "Limit" field available in the "AIL" menu and is also used for the AIL generated with the "Wizard" menu.',
									'interlinks-manager'
								),
								'help'      => __( 'Set the maximum number of keywords automatically converted to links.', 'interlinks-manager' ),
								'rangeMin'  => 1,
								'rangeMax'  => 500,
								'rangeStep' => 1,
							),
							array(
								'name'      => 'daim_default_priority',
								'label'     => __( 'Priority', 'interlinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'The priority value determines the order used to apply the AIL on the post. This option determines the default value of the "Priority" field available in the "AIL" menu and is also used for the AIL generated with the "Wizard" menu.',
									'interlinks-manager'
								),
								'help'      => __( 'Set the priority of the keyword.', 'interlinks-manager' ),
								'rangeMin'  => 0,
								'rangeMax'  => 100,
								'rangeStep' => 1,
							),
						),
					),
				),
			),

			array(
				'title'       => __( 'Suggestions', 'interlinks-manager' ),
				'description' => __( 'Customize the algorithm used to generate the internal links suggestions.', 'interlinks-manager' ),
				'cards'       => array(
					array(
						'title'   => __( 'Options', 'interlinks-manager' ),
						'options' => array(
							array(
								'name'          => 'daim_suggestions_pool_post_types',
								'label'         => __( 'Post Types', 'interlinks-manager' ),
								'type'          => 'select-multiple',
								'tooltip'       => __(
									'With this option you are able to determine in which post types the algorithm available in the "Interlinks Suggestions" editor tool should look for suggestions.',
									'interlinks-manager'
								),
								'selectOptions' => $post_types_select_options,
								'help'          => __( 'Select the post types considered for the suggestions.', 'interlinks-manager' ),
							),
							array(
								'name'      => 'daim_suggestions_pool_size',
								'label'     => __( 'Results Pool Size', 'interlinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'This option determines the maximum number of results returned by the algorithm available in the "Interlinks Suggestions" editor tool. (The five results shown for each iteration are retrieved from a pool of results which has, as a maximum size, the value defined with this option.)',
									'interlinks-manager'
								),
								'help'      => __( 'Set the pool size of the results.', 'interlinks-manager' ),
								'rangeMin'  => 1,
								'rangeMax'  => 1000,
								'rangeStep' => 1,
							),
							array(
								'name'          => 'daim_suggestions_titles',
								'label'         => __( 'Titles', 'interlinks-manager' ),
								'type'          => 'select',
								'tooltip'       => __( 'Select if the algorithm available in the "Interlinks Suggestions" editor tool should consider the posts, pages and custom post types titles.', 'interlinks-manager' ),
								'selectOptions' => array(
									array(
										'value' => 'consider',
										'text'  => __( 'Consider', 'interlinks-manager' ),
									),
									array(
										'value' => 'ignore',
										'text'  => __( 'Ignore', 'interlinks-manager' ),
									),
								),
								'help'          => __(
									'Select if the titles of the posts should be used by the algorithm.',
									'interlinks-manager'
								),
							),
							array(
								'name'          => 'daim_suggestions_categories',
								'label'         => __( 'Categories', 'interlinks-manager' ),
								'type'          => 'select',
								'tooltip'       => __( 'Select if the algorithm available in the "Interlinks Suggestions" editor tool should consider the post categories. If "Required" is selected the algorithm will return only posts that have at least one category in common with the edited post.', 'interlinks-manager' ),
								'selectOptions' => array(
									array(
										'value' => 'require',
										'text'  => __( 'Require', 'interlinks-manager' ),
									),
									array(
										'value' => 'consider',
										'text'  => __( 'Consider', 'interlinks-manager' ),
									),
									array(
										'value' => 'ignore',
										'text'  => __( 'Ignore', 'interlinks-manager' ),
									),
								),
								'help'          => __(
									'Select if the categories of the posts should be used by the algorithm.',
									'interlinks-manager'
								),
							),
							array(
								'name'          => 'daim_suggestions_tags',
								'label'         => __( 'Tags', 'interlinks-manager' ),
								'type'          => 'select',
								'tooltip'       => __( 'Select if the algorithm available in the "Interlinks Suggestions" editor tool should consider the post tags. If "Required" is selected the algorithm will return only posts that have at least one tag in common with the edited post.', 'interlinks-manager' ),
								'selectOptions' => array(
									array(
										'value' => 'require',
										'text'  => __( 'Require', 'interlinks-manager' ),
									),
									array(
										'value' => 'consider',
										'text'  => __( 'Consider', 'interlinks-manager' ),
									),
									array(
										'value' => 'ignore',
										'text'  => __( 'Ignore', 'interlinks-manager' ),
									),
								),
								'help'          => __(
									'Select if the tags of the posts should be used by the algorithm.',
									'interlinks-manager'
								),
							),
							array(
								'name'          => 'daim_suggestions_post_type',
								'label'         => __( 'Post Type', 'interlinks-manager' ),
								'type'          => 'select',
								'tooltip'       => __( 'Select if the algorithm available in the "Interlinks Suggestions" editor tool should consider the post type. If "Required" is selected the algorithm will return only posts that belong to the same post type of the edited post.', 'interlinks-manager' ),
								'selectOptions' => array(
									array(
										'value' => 'require',
										'text'  => __( 'Require', 'interlinks-manager' ),
									),
									array(
										'value' => 'consider',
										'text'  => __( 'Consider', 'interlinks-manager' ),
									),
									array(
										'value' => 'ignore',
										'text'  => __( 'Ignore', 'interlinks-manager' ),
									),
								),
								'help'          => __(
									'Select if the post types of the posts should be used by the algorithm.',
									'interlinks-manager'
								),
							),
						),
					),
				),
			),

			array(
				'title'       => __( 'Link Analysis', 'interlinks-manager' ),
				'description' => __( 'Configure options and parameters used for the link analysis.', 'interlinks-manager' ),
				'cards'       => array(
					array(
						'title'   => __( 'Juice', 'interlinks-manager' ),
						'options' => array(
							array(
								'name'      => 'daim_default_seo_power',
								'label'     => __( 'SEO Power (Default)', 'interlinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'Please enter a number from 100 to 1000000 in the "SEO Power (Default)" option.',
									'interlinks-manager'
								),
								'help'      => __( 'Set the default SEO power of the posts.', 'interlinks-manager' ),
								'rangeMin'  => 100,
								'rangeMax'  => 1000000,
								'rangeStep' => 1,
							),
							array(
								'name'      => 'daim_penality_per_position_percentage',
								'label'     => __( 'Penality per Position (%)', 'interlinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'With multiple links in an article, the algorithm that calculates the "Link Juice" passed by each link removes a percentage of the passed "Link Juice" based on the position of a link compared to the other links.',
									'interlinks-manager'
								),
								'help'      => __( 'Set the penality per position percentage.', 'interlinks-manager' ),
								'rangeMin'  => 1,
								'rangeMax'  => 100,
								'rangeStep' => 1,
							),
							array(
								'name'    => 'daim_remove_link_to_anchor',
								'label'   => __( 'Remove Fragment Identifier', 'interlinks-manager' ),
								'type'    => 'toggle',
								'tooltip' => __( 'Select "Yes" to automatically remove links to anchors from every URL used to calculate the link juice. With this option enabled "http://example.com" and "http://example.com#myanchor" will both contribute to generate link juice only for a single URL, that is "http://example.com".', 'interlinks-manager' ),
								'help'    => __(
									'Remove the fragment identifier from the URL.',
									'interlinks-manager'
								),
							),
							array(
								'name'    => 'daim_remove_url_parameters',
								'label'   => __( 'Remove URL Parameters', 'interlinks-manager' ),
								'type'    => 'toggle',
								'tooltip' => __( 'Select "Yes" to automatically remove the URL parameters from every URL used to calculate the link juice. With this option enabled "http://example.com" and "http://example.com?param=1" will both contribute to generate link juice only for a single URL, that is "http://example.com". Please note that this option should not be enabled if your website is using URL parameters to actually identify specific pages. (for example with pretty permalinks not enabled)', 'interlinks-manager' ),
								'help'    => __(
									'Remove the parameters from the URL.',
									'interlinks-manager'
								),
							),
						),
					),
					array(
						'title'   => __( 'Technical Options', 'interlinks-manager' ),
						'options' => array(
							array(
								'name'    => 'daim_set_max_execution_time',
								'label'   => __( 'Set Max Execution Time', 'interlinks-manager' ),
								'type'    => 'toggle',
								'tooltip' => __( 'Select "Yes" to enable your custom "Max Execution Time Value" on long running scripts.', 'interlinks-manager' ),
								'help'    => __(
									'Enable a custom max execution time value.',
									'interlinks-manager'
								),
							),
							array(
								'name'      => 'daim_max_execution_time_value',
								'label'     => __( 'Max Execution Time Value', 'interlinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'This value determines the maximum number of seconds allowed to execute long running scripts.',
									'interlinks-manager'
								),
								'help'      => __( 'Set the max execution time value.', 'interlinks-manager' ),
								'rangeMin'  => 1,
								'rangeMax'  => 3600,
								'rangeStep' => 1,
							),
							array(
								'name'    => 'daim_set_memory_limit',
								'label'   => __( 'Set Memory Limit', 'interlinks-manager' ),
								'type'    => 'toggle',
								'tooltip' => __( 'Select "Yes" to enable your custom "Memory Limit Value" on long running scripts.', 'interlinks-manager' ),
								'help'    => __(
									'Enable a custom memory limit.',
									'interlinks-manager'
								),
							),
							array(
								'name'      => 'daim_memory_limit_value',
								'label'     => __( 'Memory Limit Value', 'interlinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'This value determines the PHP memory limit in megabytes allowed to execute long running scripts.',
									'interlinks-manager'
								),
								'help'      => __( 'Set the memory limit value.', 'interlinks-manager' ),
								'rangeMin'  => 1,
								'rangeMax'  => 16384,
								'rangeStep' => 1,
							),
							array(
								'name'      => 'daim_limit_posts_analysis',
								'label'     => __( 'Limit Posts Analysis	', 'interlinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'With this options you can determine the maximum number of posts analyzed to get information about your internal links, to get information about the internal links juice and to get suggestions in the "Interlinks Suggestions" editor tool. If you select for example "1000", the analysis performed by the plugin will use your latest "1000" posts.',
									'interlinks-manager'
								),
								'help'      => __( 'Limit the maximum number of analyzed posts.', 'interlinks-manager' ),
								'rangeMin'  => 1,
								'rangeMax'  => 100000,
								'rangeStep' => 1,
							),
							array(
								'name'          => 'daim_dashboard_post_types',
								'label'         => __( 'Dashboard Post Types', 'interlinks-manager' ),
								'type'          => 'select-multiple',
								'tooltip'       => __(
									'With this option you are able to determine the post types analyzed in the Dashboard menu. Leave this field empty to perform the analysis in any post type.',
									'interlinks-manager'
								),
								'selectOptions' => $post_types_select_options,
								'help'          => __( 'Select the post types analyzed in the Dashboard menu.', 'interlinks-manager' ),
							),
							array(
								'name'          => 'daim_juice_post_types',
								'label'         => __( 'Juice Post Types', 'interlinks-manager' ),
								'type'          => 'select-multiple',
								'tooltip'       => __(
									'With this option you are able to determine the post types analyzed in the Juice menu. Leave this field empty to perform the analysis in any post type.',
									'interlinks-manager'
								),
								'selectOptions' => $post_types_select_options,
								'help'          => __( 'Select the post types analyzed in the Juice menu.', 'interlinks-manager' ),
							),
							array(
								'name'          => 'daim_http_status_post_types',
								'label'         => __( 'HTTP Status Post Types', 'interlinks-manager' ),
								'type'          => 'select-multiple',
								'tooltip'       => __(
									'With this option you are able to determine the post types analyzed in the HTTP Status menu. Leave this field empty to perform the analysis in any post type.',
									'interlinks-manager'
								),
								'selectOptions' => $post_types_select_options,
								'help'          => __( 'Select the post types analyzed in the HTTP Status menu.', 'interlinks-manager' ),
							),
						),
					),
				),
			),

			array(
				'title'       => __( 'Advanced', 'interlinks-manager' ),
				'description' => __( 'Manage advanced plugin settings.', 'interlinks-manager' ),
				'cards'       => array(
					array(
						'title'   => __( 'Click Tracking', 'interlinks-manager' ),
						'options' => array(
							array(
								'name'    => 'daim_track_internal_links',
								'label'   => __( 'Track Internal Links', 'interlinks-manager' ),
								'type'    => 'toggle',
								'tooltip' => __( 'With this option enabled every click on the manual and auto internal links will be tracked. The collected data will be available in the "Hits" menu.', 'interlinks-manager' ),
								'help'    => __(
									'Track the clicks on the internal links.',
									'interlinks-manager'
								),
							),
						),
					),
					array(
						'title'   => __( 'Optimization Parameters', 'interlinks-manager' ),
						'options' => array(
							array(
								'name'      => 'daim_optimization_num_of_characters',
								'label'     => __( 'Characters per Interlink', 'interlinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'The "Recommended Interlinks" value available in the "Dashboard" menu and in the "Interlinks Optimization" editor tool is based on the defined "Characters per Interlink" and on the content length of the post. For example if you define 500 "Characters per Interlink", in the "Dashboard" menu, with a post that has a content length of 2000 characters you will get 4 as the value for the "Recommended Interlinks".',
									'interlinks-manager'
								),
								'help'      => __( 'Set the optimal number of characters per internal link.', 'interlinks-manager' ),
								'rangeMin'  => 1,
								'rangeMax'  => 1000000,
								'rangeStep' => 1,
							),
							array(
								'name'      => 'daim_optimization_delta',
								'label'     => __( 'Optimization Delta	', 'interlinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'The "Optimization Delta" is used to generate the "Optimization Flag" available in the "Dashboard" menu and the text message diplayed in the "Interlinks Optimization" editor tool. This option determines how different can be the actual number of interlinks in a post from the calculated "Recommended Interlinks". This option defines a range, so for example in a post with 10 "Recommended Interlinks" and this option value equal to 4, the post will be considered optimized when it includes from 8 to 12 interlinks.',
									'interlinks-manager'
								),
								'help'      => __( 'Set how different can the number of internal links of a post from the optimal value.', 'interlinks-manager' ),
								'rangeMin'  => 1,
								'rangeMax'  => 1000000,
								'rangeStep' => 1,
							),
						),
					),
					array(
						'title'   => __( 'Editor Tools', 'interlinks-manager' ),
						'options' => array(
							array(
								'name'          => 'daim_interlinks_options_post_types',
								'label'         => __( 'Interlinks Options Post Types', 'interlinks-manager' ),
								'type'          => 'select-multiple',
								'tooltip'       => __(
									'With this option you are able to determine in which post types the "Interlinks Options" editor tool should be loaded.',
									'interlinks-manager'
								),
								'selectOptions' => $post_types_select_options,
								'help'          => __( 'Select the post types where the "Interlinks Options" editor tool should be loaded.', 'interlinks-manager' ),
							),
							array(
								'name'          => 'daim_interlinks_optimization_post_types',
								'label'         => __( 'Interlinks Optimization Post Types', 'interlinks-manager' ),
								'type'          => 'select-multiple',
								'tooltip'       => __(
									'With this option you are able to determine in which post types the "Interlinks Optimization" editor tool should be loaded.',
									'interlinks-manager'
								),
								'selectOptions' => $post_types_select_options,
								'help'          => __( 'Select the post types where the "Interlinks Options" editor tool should be loaded.', 'interlinks-manager' ),
							),
							array(
								'name'          => 'daim_interlinks_suggestions_post_types',
								'label'         => __( 'Interlinks Suggestions Post Types', 'interlinks-manager' ),
								'type'          => 'select-multiple',
								'tooltip'       => __(
									'With this option you are able to determine in which post types the "Interlinks Suggestions" editor tool should be loaded.',
									'interlinks-manager'
								),
								'selectOptions' => $post_types_select_options,
								'help'          => __( 'Select the post types where the "Interlinks Suggestions" editor tool should be loaded.', 'interlinks-manager' ),
							),
						),
					),
					array(
						'title'   => __( 'Capabilities', 'interlinks-manager' ),
						'options' => array(
							array(
								'name'    => 'daim_dashboard_menu_required_capability',
								'label'   => __( 'Dashboard Menu', 'interlinks-manager' ),
								'type'    => 'text',
								'tooltip' => __(
									'The capability required to get access on the "Dashboard" Menu.',
									'interlinks-manager'
								),
								'help'    => __( 'The capability required to access the "Dashboard" menu.', 'interlinks-manager' ),
							),
							array(
								'name'    => 'daim_juice_menu_required_capability',
								'label'   => __( 'Juice Menu', 'interlinks-manager' ),
								'type'    => 'text',
								'tooltip' => __(
									'The capability required to get access on the "Juice" Menu.',
									'interlinks-manager'
								),
								'help'    => __( 'The capability required to access the "Juice" menu.', 'interlinks-manager' ),
							),
							array(
								'name'    => 'daim_hits_menu_required_capability',
								'label'   => __( 'Hits Menu', 'interlinks-manager' ),
								'type'    => 'text',
								'tooltip' => __(
									'The capability required to get access on the "Hits" Menu.',
									'interlinks-manager'
								),
								'help'    => __( 'The capability required to access the "Hits" menu.', 'interlinks-manager' ),
							),
							array(
								'name'    => 'daim_http_status_menu_required_capability',
								'label'   => __( 'HTTP Status Menu', 'interlinks-manager' ),
								'type'    => 'text',
								'tooltip' => __(
									'The capability required to get access on the "http_status" Menu.',
									'interlinks-manager'
								),
								'help'    => __( 'The capability required to access the "HTTP Status" menu.', 'interlinks-manager' ),
							),
							array(
								'name'    => 'daim_wizard_menu_required_capability',
								'label'   => __( 'Wizard Menu	', 'interlinks-manager' ),
								'type'    => 'text',
								'tooltip' => __(
									'The capability required to get access on the "Wizard" Menu.',
									'interlinks-manager'
								),
								'help'    => __( 'The capability required to access the "Wizard" menu.', 'interlinks-manager' ),
							),
							array(
								'name'    => 'daim_ail_menu_required_capability',
								'label'   => __( 'AIL Menu', 'interlinks-manager' ),
								'type'    => 'text',
								'tooltip' => __(
									'The capability required to get access on the "AIL" Menu.',
									'interlinks-manager'
								),
								'help'    => __( 'The capability required to access the "AIL" menu.', 'interlinks-manager' ),
							),
							array(
								'name'    => 'daim_categories_menu_required_capability',
								'label'   => __( 'Categories Menu', 'interlinks-manager' ),
								'type'    => 'text',
								'tooltip' => __(
									'The capability required to get access on the "Categories" Menu.',
									'interlinks-manager'
								),
								'help'    => __( 'The capability required to access the "Categories" menu.', 'interlinks-manager' ),
							),
							array(
								'name'    => 'daim_term_groups_menu_required_capability',
								'label'   => __( 'Term Groups Menu', 'interlinks-manager' ),
								'type'    => 'text',
								'tooltip' => __(
									'The capability required to get access on the "Term Groups" Menu.',
									'interlinks-manager'
								),
								'help'    => __( 'The capability required to access the "Term Groups" menu.', 'interlinks-manager' ),
							),
							array(
								'name'    => 'daim_tools_menu_required_capability',
								'label'   => __( 'Tools Menu', 'interlinks-manager' ),
								'type'    => 'text',
								'tooltip' => __(
									'The capability required to get access on the "Tools" Menu.',
									'interlinks-manager'
								),
								'help'    => __( 'The capability required to access the "Tools" menu.', 'interlinks-manager' ),
							),
							array(
								'name'    => 'daim_maintenance_menu_required_capability',
								'label'   => __( 'Maintenance Menu', 'interlinks-manager' ),
								'type'    => 'text',
								'tooltip' => __(
									'The capability required to get access on the "Maintenance" Menu.',
									'interlinks-manager'
								),
								'help'    => __( 'The capability required to access the "Maintenance" menu.', 'interlinks-manager' ),
							),
							array(
								'name'    => 'daim_interlinks_options_mb_required_capability',
								'label'   => __( 'Interlinks Options Editor Tool', 'interlinks-manager' ),
								'type'    => 'text',
								'tooltip' => __(
									'The capability required to get access on the "Interlinks Options" Editor Tool.',
									'interlinks-manager'
								),
								'help'    => __( 'The capability required to access the "Interlinks Options" editor tool.', 'interlinks-manager' ),
							),
							array(
								'name'    => 'daim_interlinks_optimization_mb_required_capability',
								'label'   => __( 'Interlinks Optimization Editor Tool', 'interlinks-manager' ),
								'type'    => 'text',
								'tooltip' => __(
									'The capability required to get access on the "Interlinks Optimization" Editor Tool.',
									'interlinks-manager'
								),
								'help'    => __( 'The capability required to access the "Interlinks Optimization" editor tool.', 'interlinks-manager' ),
							),
							array(
								'name'    => 'daim_interlinks_suggestions_mb_required_capability',
								'label'   => __( 'Interlinks Suggestions Editor Tool', 'interlinks-manager' ),
								'type'    => 'text',
								'tooltip' => __(
									'The capability required to get access on the "Interlinks Suggestions" Editor Tool.',
									'interlinks-manager'
								),
								'help'    => __( 'The capability required to access the "Interlinks Suggestions" editor tool.', 'interlinks-manager' ),
							),
						),
					),
					array(
						'title'   => __( 'HTTP Status', 'interlinks-manager' ),
						'options' => array(
							array(
								'name'      => 'daim_http_status_checks_per_iteration',
								'label'     => __( 'WP-Cron Checks Per Run', 'interlinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'This value determines the number of HTTP requests sent at each run of the WP-Cron event. This parameter is used in the WP-Cron event used to verify the HTTP response status codes of the URLs used in the internal links.',
									'interlinks-manager'
								),
								'help'      => __( 'Set the number of HTTP requests sent at each run of the WP-Cron event.', 'interlinks-manager' ),
								'rangeMin'  => 1,
								'rangeMax'  => 10,
								'rangeStep' => 1,
							),
							array(
								'name'      => 'daim_http_status_cron_schedule_interval',
								'label'     => __( 'WP-Cron Event Interval', 'interlinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'This value determines the interval in seconds between the custom WP-Cron events used to verify the HTTP response status codes of the URLs used in the internal links.',
									'interlinks-manager'
								),
								'help'      => __( 'Set the interval in seconds between the WP-Cron events.', 'interlinks-manager' ),
								'rangeMin'  => 1,
								'rangeMax'  => 3600,
								'rangeStep' => 1,
							),
							array(
								'name'      => 'daim_http_status_request_timeout',
								'label'     => __( 'Request Timeout', 'interlinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'This value determines how long the connection should stay open in seconds for the HTTP requests used to verify the HTTP response status codes of the URLs used in the internal links.',
									'interlinks-manager'
								),
								'help'      => __( 'Set the HTTP request timeout in seconds.', 'interlinks-manager' ),
								'rangeMin'  => 1,
								'rangeMax'  => 3600,
								'rangeStep' => 1,
							),
						),
					),
					array(
						'title'   => __( 'Misc', 'interlinks-manager' ),
						'options' => array(
							array(
								'name'      => 'daim_wizard_rows',
								'label'     => __( 'Wizard Rows', 'interlinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'This option determines the number of rows available in the table of the Wizard menu.',
									'interlinks-manager'
								),
								'help'      => __( 'Set the number of rows available in the "Wizard" menu.', 'interlinks-manager' ),
								'rangeMin'  => 1,
								'rangeMax'  => 2000,
								'rangeStep' => 1,
							),
							array(
								'name'    => 'daim_supported_terms',
								'label'   => __( 'Supported Terms', 'interlinks-manager' ),
								'type'    => 'range',
								'tooltip' => __(
									'This option determines the maximum number of terms supported in a single term group.',
									'interlinks-manager'
								),
								'help'    => __( 'Set the maximum number of terms supported in a single term group.', 'interlinks-manager' ),
								'rangeMin'  => 1,
								'rangeMax'  => 50,
								'rangeStep' => 1,
							),
							array(
								'name'      => 'daim_protect_attributes',
								'label'     => __( 'Protect Attributes', 'interlinks-manager' ),
								'type'      => 'toggle',
								'tooltip'   => __(
									'With this option enabled, the AIL will not be applied to HTML attributes.',
									'interlinks-manager'
								),
								'help'      => __( 'Protect the HTML attributes from the application of the automatic links.', 'interlinks-manager' ),
								'rangeMin'  => 1,
								'rangeMax'  => 50,
								'rangeStep' => 1,
							),
						),
					),
					array(
						'title'   => __( 'Pagination', 'interlinks-manager' ),
						'options' => array(
							array(
								'name'      => 'daim_pagination_dashboard_menu',
								'label'     => __( 'Pagination Dashboard Menu', 'interlinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'This options determines the number of elements per page displayed in the "Dashboard" menu.',
									'interlinks-manager'
								),
								'help'      => __( 'Set the number of elements per page displayed in the "Dashboard" menu.', 'interlinks-manager' ),
								'rangeMin'  => 10,
								'rangeMax'  => 100,
								'rangeStep' => 10,
							),
							array(
								'name'      => 'daim_pagination_juice_menu',
								'label'     => __( 'Pagination Juice Menu', 'interlinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'This options determines the number of elements per page displayed in the "Juice" menu.',
									'interlinks-manager'
								),
								'help'      => __( 'Set the number of elements per page displayed in the "Juice" menu.', 'interlinks-manager' ),
								'rangeMin'  => 10,
								'rangeMax'  => 100,
								'rangeStep' => 10,
							),
							array(
								'name'      => 'daim_pagination_http_status_menu',
								'label'     => __( 'Pagination HTTP Status Menu', 'interlinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'This options determines the number of elements per page displayed in the "HTTP Status" menu.',
									'interlinks-manager'
								),
								'help'      => __( 'Set the number of elements per page displayed in the "HTTP Status" menu.', 'interlinks-manager' ),
								'rangeMin'  => 10,
								'rangeMax'  => 100,
								'rangeStep' => 10,
							),
							array(
								'name'      => 'daim_pagination_hits_menu',
								'label'     => __( 'Pagination Hits Menu', 'interlinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'This options determines the number of elements per page displayed in the "Hits" menu.',
									'interlinks-manager'
								),
								'help'      => __( 'Set the number of elements per page displayed in the "Hits" menu.', 'interlinks-manager' ),
								'rangeMin'  => 10,
								'rangeMax'  => 100,
								'rangeStep' => 10,
							),
							array(
								'name'      => 'daim_pagination_ail_menu',
								'label'     => __( 'Pagination AIL Menu', 'interlinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'This options determines the number of elements per page displayed in the "AIL" menu.',
									'interlinks-manager'
								),
								'help'      => __( 'Set the number of elements per page displayed in the "AIL" menu.', 'interlinks-manager' ),
								'rangeMin'  => 10,
								'rangeMax'  => 100,
								'rangeStep' => 10,
							),
							array(
								'name'      => 'daim_pagination_categories_menu',
								'label'     => __( 'Pagination Categories Menu', 'interlinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'This options determines the number of elements per page displayed in the "Categories" menu.',
									'interlinks-manager'
								),
								'help'      => __( 'Set the number of elements per page displayed in the "Categories" menu.', 'interlinks-manager' ),
								'rangeMin'  => 10,
								'rangeMax'  => 100,
								'rangeStep' => 10,
							),
							array(
								'name'      => 'daim_pagination_term_groups_menu',
								'label'     => __( 'Pagination Term Groups Menu', 'interlinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'This options determines the number of elements per page displayed in the "Term Groups" menu.',
									'interlinks-manager'
								),
								'help'      => __( 'Set the number of elements per page displayed in the "Term Groups" menu.', 'interlinks-manager' ),
								'rangeMin'  => 10,
								'rangeMax'  => 100,
								'rangeStep' => 10,
							),
						),
					),
				),
			),
			array(
				'title'       => __( 'License', 'interlinks-manager' ),
				'description' => __( 'Configure your license for seamless updates and support.', 'interlinks-manager' ),
				'cards'       => array(
					array(
						'title'   => __( 'License Management', 'interlinks-manager' ),
						'options' => array(
							array(
								'name'          => 'daim_license_provider',
								'label'         => __( 'Provider', 'interlinks-manager' ),
								'type'          => 'select',
								'tooltip'       => __(
									'Use this option to indicate where you purchased your license. Choosing the correct license provider ensures that your license is properly verified on our system.',
									'interlinks-manager'
								),
								'help'          => __( 'Select your license provider.', 'interlinks-manager' ),
								'selectOptions' => array(
									array(
										'value' => 'daext_com',
										'text'  => __( 'DAEXT.COM', 'interlinks-manager' ),
									),
									array(
										'value' => 'envato_market',
										'text'  => __( 'Envato Market', 'interlinks-manager' ),
									),
								),
							),
							array(
								'name'    => 'daim_license_key',
								'label'   => __( 'Key', 'interlinks-manager' ),
								'type'    => 'text',
								'tooltip' => __(
									'For licenses purchased on daext.com, find and manage your keys at https://daext.com/account/. For Envato Market licenses, use the item purchase code available in your downloads area.',
									'interlinks-manager'
								),
								'help'    => __( 'Enter your license key.', 'interlinks-manager' ),
							),
						),
					),
				),
			),

		);

		return $configuration;
	}
}
