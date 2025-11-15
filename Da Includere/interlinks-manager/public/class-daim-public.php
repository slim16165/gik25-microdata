<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @package interlinks-manager
 */

/**
 * This class should be used to work with the public side of WordPress.
 */
class Daim_Public {

	/**
	 * The singleton instance of the class.
	 *
	 * @var Daextrevo_Shared
	 */
	protected static $instance = null;

	/**
	 * An instance of the shared class.
	 *
	 * @var Daextrevo_Shared|null
	 */
	private $shared            = null;

	/**
	 * Constructor.
	 */
	private function __construct() {

		// Assign an instance of the plugin info.
		$this->shared = Daim_Shared::get_instance();

		// Load public js.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Add a data attribute identify the manual interlink.
		add_filter( 'the_content', array( $this, 'add_data_attribute' ), 0 );

		/**
		 * Add the autolink on the content if the test mode option is not
		 * activated or if the current user is the administrator.
		 */
		if (
			! (bool) get_option( $this->shared->get( 'slug' ) . '_ail_test_mode' ) ||
			current_user_can( get_option( $this->shared->get( 'slug' ) . '_ail_menu_required_capability' ) )
		) {
			add_filter(
				'the_content',
				array( $this->shared, 'add_autolinks' ),
				intval( get_option( $this->shared->get( 'slug' ) . '_filter_priority' ), 10 )
			);
		}
	}

	/**
	 * Create an instance of this class.
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Add public JavaScript.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {

		/**
		 * Enqueue the script used to track the interlinks if the tracking is
		 * enabled.
		 */
		if ( intval( get_option( $this->shared->get( 'slug' ) . '_track_internal_links' ), 10 ) === 1 ) {

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-track-internal-links', $this->shared->get( 'url' ) . 'public/assets/js/track-internal-links.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

			// Store the JavaScript parameters in the window.DAIM_PARAMETERS object.
			$script  = 'window.DAIM_PARAMETERS = {';
			$script .= 'ajax_url: "' . admin_url( 'admin-ajax.php' ) . '",';
			$script .= 'nonce: "' . wp_create_nonce( 'daim' ) . '"';
			$script .= '};';
			if ( false !== $script ) {
				wp_add_inline_script( $this->shared->get( 'slug' ) . '-track-internal-links', $script, 'before' );
			}
		}
	}

	/**
	 * The data-mil="[post-id]" is added to all the manual interlink.
	 * This filter is applied to the content with less priority than the
	 * add_autolinks() filter, so this data attribute is applied only to the
	 * manual links.
	 *
	 * @param string $content The post content.
	 * @return string The $content with added the data-mil="1" attribute on the
	 * manual interlinks
	 */
	public function add_data_attribute( $content ) {

		if ( ! is_singular() || is_attachment() || is_feed() ) {
			return $content;}

		/**
		 * Get the website url and quote and escape the regex character. # and
		 * whitespace ( used with the 'x' modifier ) are not escaped, thus
		 * should not be included in the $site_url string.
		 */
		$site_url = preg_quote( get_home_url(), '/' );

		$content = preg_replace_callback(
			'
            {<a                     #1 Begin the element a start-tag
            [^>]+                   #2 Any character except > at least one time
            href\s*=\s*             #3 Equal may have whitespaces on both sides
            ([\'"]?)                #4 Match double quotes, single quote or no quote ( captured for the backreference \1 )
            ' . $site_url . '       #5 The site URL ( Scheme and Domain )
            [^\'">\s]+              #6 The rest of the URL ( Path and/or File )
            (\1)                    #7 Backreference that matches the href value delimiter matched at line 4
            [^>]*                   #8 Any character except > zero or more times
            >                       #9 End of the start-tag
            .*?                     #10 Link text or nested tags. After the dot ( enclose in parenthesis ) negative lookbehinds can be applied to avoid specific stuff inside the link text or nested tags. Example with single negative lookbehind (.(?<!word1))*? Example with multiple negative lookbehind (.(?<!word1)(?<!word2)(?<!word3))*?
            <\/a\s*>                #11 Element a end-tag with optional white-spaces characters before the >
            }ixu', // Enable case insentive, extended and unicode modifiers.
			array( $this->shared, 'preg_replace_callback_6' ),
			$content
		);

		return $content;
	}
}
