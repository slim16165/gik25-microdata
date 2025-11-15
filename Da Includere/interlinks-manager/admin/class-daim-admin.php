<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package interlinks-manager
 */

use Daextteam\PluginUpdateChecker\PluginUpdateChecker;

/**
 * This class should be used to work with the administrative side of WordPress.
 */
class Daim_Admin {

	protected static $instance = null;
	private $shared            = null;

	/**
	 * The screen id of the "Dashboard" menu.
	 *
	 * @var null
	 */
	private $screen_id_dashboard = null;

	/**
	 * The screen id of the "Juice" menu.
	 *
	 * @var null
	 */
	private $screen_id_juice = null;

	/**
	 * The screen id of the "Anchors" menu.
	 *
	 * @var null
	 */
	private $screen_id_anchors = null;

	/**
	 * The screen id of the "HTTP Status" menu.
	 *
	 * @var null
	 */
	private $screen_id_http_status = null;

	/**
	 * The screen id of the "Hits" menu.
	 *
	 * @var null
	 */
	private $screen_id_hits = null;

	/**
	 * The screen id of the "Wizard" menu.
	 *
	 * @var null
	 */
	private $screen_id_wizard = null;

	/**
	 * The screen id of the "Autolinks" menu.
	 *
	 * @var null
	 */
	private $screen_id_autolinks = null;

	/**
	 * The screen id of the "Categories" menu.
	 *
	 * @var null
	 */
	private $screen_id_categories = null;

	/**
	 * The screen id of the "Term Groups" menu.
	 *
	 * @var null
	 */
	private $screen_id_term_groups = null;

	/**
	 * The screen id of the "Tools" menu.
	 *
	 * @var null
	 */
	private $screen_id_tools = null;

	/**
	 * The screen id of the "Maintenance" menu.
	 *
	 * @var null
	 */
	private $screen_id_maintenance = null;

	/**
	 * The screen id of the "Options" menu.
	 *
	 * @var null
	 */
	private $screen_id_options = null;

	/**
	 * Instance of the Daim_Menu_Options class.
	 *
	 * @var null
	 */
	private $menu_options = null;

	/**
	 * Instance of the class used to generate the back-end menus.
	 *
	 * @var null
	 */
	private $menu_elements = null;

	/**
	 * Constructor.
	 */
	private function __construct() {

		// assign an instance of the plugin info.
		$this->shared = Daim_Shared::get_instance();

		// Load admin stylesheets and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the admin menu.
		add_action( 'admin_menu', array( $this, 'me_add_admin_menu' ) );

		// Add the meta box.
		add_action( 'add_meta_boxes', array( $this, 'create_meta_box' ) );

		// Save the meta box.
		add_action( 'save_post', array( $this, 'daim_save_meta_interlinks_options' ) );

		// this hook is triggered during the creation of a new blog.
		add_action( 'wpmu_new_blog', array( $this, 'new_blog_create_options_and_tables' ), 10, 6 );

		// This hook is triggered during the deletion of a blog.
		add_action( 'delete_blog', array( $this, 'delete_blog_delete_options_and_tables' ), 10, 1 );

		// Perform a manual license verification when the user click the provided link to verify the license.
		add_action( 'admin_init', array( $this, 'manual_license_verification' ) );

		// Require and instantiate the related classes used to handle the menus.
		add_action( 'init', array( $this, 'handle_menus' ) );

	}

	/**
	 * Return an istance of this class.
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * If we are in one of the plugin back-end menus require and instantiate the related class used to handle the menu.
	 *
	 * @return void
	 */
	public function handle_menus() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce non-necessary for menu selection.
		$page_query_param = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : null;

		// Require and instantiate the class used to register the menu options.
		if ( null !== $page_query_param ) {

			$config = array(
				'admin_toolbar' => array(
					'items'      => array(
						array(
							'link_text' => __('Dashboard', 'interlinks-manager'),
							'link_url'  => admin_url( 'admin.php?page=daim-dashboard' ),
							'icon'      => 'line-chart-up-03',
							'menu_slug' => 'daim-dashboard',
						),
						array(
							'link_text' => __('Juice', 'interlinks-manager'),
							'link_url'  => admin_url( 'admin.php?page=daim-juice' ),
							'icon'      => 'link-03',
							'menu_slug' => 'daim-juice',
						),
						array(
							'link_text' => __('HTTP Status', 'interlinks-manager'),
							'link_url'  => admin_url( 'admin.php?page=daim-http-status' ),
							'icon'      => 'check-verified-01',
							'menu_slug' => 'daim-http-status',
						),
					),
					'more_items' => array(
						array(
							'link_text' => __('Hits', 'interlinks-manager'),
							'link_url'  => admin_url( 'admin.php?page=daim-hits' ),
							'pro_badge' => false,
						),
						array(
							'link_text' => __('Wizard', 'interlinks-manager'),
							'link_url'  => admin_url( 'admin.php?page=daim-wizard' ),
							'pro_badge' => false,
						),
						array(
							'link_text' => __('AIL', 'interlinks-manager'),
							'link_url'  => admin_url( 'admin.php?page=daim-autolinks' ),
							'pro_badge' => false,
						),
						array(
							'link_text' => __('Categories', 'interlinks-manager'),
							'link_url'  => admin_url( 'admin.php?page=daim-categories' ),
							'pro_badge' => false,
						),
						array(
							'link_text' => __('Term Groups', 'interlinks-manager'),
							'link_url'  => admin_url( 'admin.php?page=daim-term-groups' ),
							'pro_badge' => false,
						),
						array(
							'link_text' => __('Tools', 'interlinks-manager'),
							'link_url'  => admin_url( 'admin.php?page=daim-tools' ),
							'pro_badge' => false,
						),
						array(
							'link_text' => __('Maintenance', 'interlinks-manager'),
							'link_url'  => admin_url( 'admin.php?page=daim-maintenance' ),
							'pro_badge' => false,
						),
						array(
							'link_text' => __('Options', 'interlinks-manager'),
							'link_url'  => admin_url( 'admin.php?page=daim-options' ),
							'pro_badge' => false,
						),
					),
				),
			);

			// Parent class.
			require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/class-daim-menu-elements.php';

			// Use the correct child class based on the page query parameter.
			if ( 'daim-categories' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daim-category-menu-elements.php';
				$this->menu_elements = new Daim_Category_Menu_Elements( $this->shared, $page_query_param, $config );
			}
			if ( 'daim-autolinks' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daim-autolink-menu-elements.php';
				$this->menu_elements = new Daim_Autolink_Menu_Elements( $this->shared, $page_query_param, $config );
			}
			if ( 'daim-term-groups' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daim-term-groups-menu-elements.php';
				$this->menu_elements = new Daim_Term_Groups_Menu_Elements( $this->shared, $page_query_param, $config );
			}
			if ( 'daim-wizard' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daim-wizard-menu-elements.php';
				$this->menu_elements = new Daim_Wizard_Menu_Elements( $this->shared, $page_query_param, $config );
			}
			if ( 'daim-tools' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daim-tools-menu-elements.php';
				$this->menu_elements = new Daim_Tools_Menu_Elements( $this->shared, $page_query_param, $config );
			}
			if ( 'daim-maintenance' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daim-maintenance-menu-elements.php';
				$this->menu_elements = new Daim_Maintenance_Menu_Elements( $this->shared, $page_query_param, $config );
			}
			if ( 'daim-dashboard' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daim-dashboard-menu-elements.php';
				$this->menu_elements = new Daim_Dashboard_Menu_Elements( $this->shared, $page_query_param, $config );
			}
			if ( 'daim-juice' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daim-juice-menu-elements.php';
				$this->menu_elements = new Daim_Juice_Menu_Elements( $this->shared, $page_query_param, $config );
			}
			if ( 'daim-http-status' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daim-http-status-menu-elements.php';
				$this->menu_elements = new Daim_Http_Status_Menu_Elements( $this->shared, $page_query_param, $config );
			}
			if ( 'daim-hits' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daim-hits-menu-elements.php';
				$this->menu_elements = new Daim_Hits_Menu_Elements( $this->shared, $page_query_param, $config );
			}
			if ( 'daim-options' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daim-options-menu-elements.php';
				$this->menu_elements = new Daim_Options_Menu_Elements( $this->shared, $page_query_param, $config );
			}
		}

	}

	public function enqueue_admin_styles() {

		$screen = get_current_screen();

		// Menu dashboard.
		if ( $screen->id === $this->screen_id_dashboard ) {

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array(), $this->shared->get( 'ver' ) );

		}

		// Menu dashboard.
		if ( $screen->id === $this->screen_id_juice ) {

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array(), $this->shared->get( 'ver' ) );

		}

		// Menu HTTP Status.
		if ( $screen->id === $this->screen_id_http_status ) {

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array(), $this->shared->get( 'ver' ) );

		}

		// Menu Hits.
		if ( $screen->id === $this->screen_id_hits ) {

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array(), $this->shared->get( 'ver' ) );

		}

		// Menu wizard.
		if ( $screen->id === $this->screen_id_wizard ) {

			// Handsontable.
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-handsontable-full',
				$this->shared->get( 'url' ) . 'admin/assets/inc/handsontable/handsontable.full.min.css',
				array(),
				$this->shared->get( 'ver' )
			);

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array(), $this->shared->get( 'ver' ) );

			// Select2.
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/css/select2.min.css',
				array(),
				$this->shared->get( 'ver' )
			);

		}

		// Menu autolinks.
		if ( $screen->id === $this->screen_id_autolinks ) {

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array(), $this->shared->get( 'ver' ) );

			//jQuery UI Dialog
			wp_enqueue_style($this->shared->get('slug') . '-jquery-ui-dialog',
				$this->shared->get('url') . 'admin/assets/css/jquery-ui-dialog.css', array(),
				$this->shared->get('ver'));

			// Select2.
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/css/select2.min.css',
				array(),
				$this->shared->get( 'ver' )
			);

		}

		// Menu categories.
		if ( $screen->id === $this->screen_id_categories ) {

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array(), $this->shared->get( 'ver' ) );

			//jQuery UI Dialog
			wp_enqueue_style($this->shared->get('slug') . '-jquery-ui-dialog',
				$this->shared->get('url') . 'admin/assets/css/jquery-ui-dialog.css', array(),
				$this->shared->get('ver'));

		}

		// Menu term groups.
		if ( $screen->id === $this->screen_id_term_groups ) {

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array(), $this->shared->get( 'ver' ) );

			// Select2.
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/css/select2.min.css',
				array(),
				$this->shared->get( 'ver' )
			);

			//jQuery UI Dialog
			wp_enqueue_style($this->shared->get('slug') . '-jquery-ui-dialog',
				$this->shared->get('url') . 'admin/assets/css/jquery-ui-dialog.css', array(),
				$this->shared->get('ver'));

		}

		// Menu tools.
		if ( $screen->id === $this->screen_id_tools ) {

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array(), $this->shared->get( 'ver' ) );

		}

		// Menu Maintenance.
		if ( $screen->id === $this->screen_id_maintenance ) {

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array(), $this->shared->get( 'ver' ) );

			// Select2.
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/css/select2.min.css',
				array(),
				$this->shared->get( 'ver' )
			);

			//jQuery UI Dialog
			wp_enqueue_style($this->shared->get('slug') . '-jquery-ui-dialog',
				$this->shared->get('url') . 'admin/assets/css/jquery-ui-dialog.css', array(),
				$this->shared->get('ver'));

		}

		// Menu options -----------------------------------------------------------------------------------------------.
		if ( $screen->id === $this->screen_id_options ) {

			// New Menu Framework (used also in the standard menus).
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array('wp-components'), $this->shared->get( 'ver' ) );

		}

		// Post editor style for the Classic Editor -------------------------------------------------------------------.

		/**
		 * Load the CSS files used in the classic editor only if the block editor js file with handler 'daim-editor-js'
		 * is not enqueued.
		 */
		if ( !wp_script_is( 'daim-editor-js', 'enqueued' ) ) {

			/**
			 * Load the post editor CSS if at least one of the three meta box is
			 * enabled with the current $screen->id.
			 */
			$load_post_editor_css = false;

			$interlinks_options_post_types_a = maybe_unserialize( get_option( $this->shared->get( 'slug' ) . '_interlinks_options_post_types' ) );
			if ( is_array( $interlinks_options_post_types_a ) && in_array( $screen->id, $interlinks_options_post_types_a, true ) ) {
				$load_post_editor_css = true;
			}

			$interlinks_optimization_post_types_a = maybe_unserialize( get_option( $this->shared->get( 'slug' ) . '_interlinks_optimization_post_types' ) );
			if ( is_array( $interlinks_optimization_post_types_a ) && in_array( $screen->id, $interlinks_optimization_post_types_a, true ) ) {
				$load_post_editor_css = true;
			}

			$interlinks_suggestions_post_types_a = maybe_unserialize( get_option( $this->shared->get( 'slug' ) . '_interlinks_suggestions_post_types' ) );
			if ( is_array( $interlinks_suggestions_post_types_a ) && in_array( $screen->id, $interlinks_suggestions_post_types_a, true ) ) {
				$load_post_editor_css = true;
			}

			if ( $load_post_editor_css ) {

				// JQuery UI Tooltips.
				wp_enqueue_style( $this->shared->get( 'slug' ) . '-jquery-ui-tooltip', $this->shared->get( 'url' ) . 'admin/assets/css/jquery-ui-tooltip.css', array(), $this->shared->get( 'ver' ) );

				// Post Editor CSS.
				wp_enqueue_style( $this->shared->get( 'slug' ) . '-post-editor', $this->shared->get( 'url' ) . 'admin/assets/css/post-editor.css', array(), $this->shared->get( 'ver' ) );

			}

		}

	}

	/**
	 * Enqueue admin-specific javascript.
	 */
	public function enqueue_admin_scripts() {

		$wp_localize_script_data = array(
			'deleteText'             => wp_strip_all_tags( __( 'Delete', 'interlinks-manager') ),
			'cancelText'             => wp_strip_all_tags( __( 'Cancel', 'interlinks-manager') ),
			'chooseAnOptionText'     => wp_strip_all_tags( __( 'Choose an Option ...', 'interlinks-manager') ),
			'wizardRows'             => intval( get_option( $this->shared->get( 'slug' ) . '_wizard_rows' ), 10 ),
			'closeText'              => wp_strip_all_tags( __( 'Close', 'interlinks-manager') ),
			'postText'               => wp_strip_all_tags( __( 'Post', 'interlinks-manager') ),
			'anchorTextText'         => wp_strip_all_tags( __( 'Anchor Text', 'interlinks-manager') ),
			'juiceText'              => wp_strip_all_tags( __( 'Juice (Value)', 'interlinks-manager') ),
			'juiceVisualText'        => wp_strip_all_tags( __( 'Juice (Visual)', 'interlinks-manager') ),
			'postTooltipText'        => wp_strip_all_tags( __( 'The post that includes the link.', 'interlinks-manager') ),
			'anchorTextTooltipText'  => wp_strip_all_tags( __( 'The anchor text of the link.', 'interlinks-manager') ),
			'juiceTooltipText'       => wp_strip_all_tags( __( 'The link juice generated by the link.', 'interlinks-manager') ),
			'juiceVisualTooltipText' => wp_strip_all_tags( __( 'The visual representation of the link juice generated by the link.', 'interlinks-manager') ),
			'juiceModalTitleText'    => wp_strip_all_tags( __( 'Internal Inbound Links for', 'interlinks-manager') ),
			'itemsText'              => wp_strip_all_tags( __( 'items', 'interlinks-manager') ),
		);

		$screen = get_current_screen();

		// General.
		wp_enqueue_script( $this->shared->get( 'slug' ) . '-general', $this->shared->get( 'url' ) . 'admin/assets/js/general.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

		// Menu dashboard.
		if ( $screen->id === $this->screen_id_dashboard ) {

			// Store the JavaScript parameters in the window.DAEXTREVOP_PARAMETERS object.
			$initialization_script  = 'window.DAIM_PARAMETERS = {';
			$initialization_script .= 'ajax_url: "' . admin_url( 'admin-ajax.php' ) . '",';
			$initialization_script .= 'read_requests_nonce: "' . wp_create_nonce( 'daextrevop_read_requests_nonce' ) . '",';
			$initialization_script .= 'admin_url: "' . get_admin_url() . '",';
			$initialization_script .= 'site_url: "' . get_site_url() . '",';
			$initialization_script .= 'plugin_url: "' . $this->shared->get( 'url' ) . '",';
			$initialization_script .= 'items_per_page: ' . intval(get_option($this->shared->get( 'slug' ) . '_pagination_dashboard_menu'), 10);
			$initialization_script .= '};';

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-dashboard-menu',
				$this->shared->get( 'url' ) . 'admin/react/dashboard-menu/build/index.js',
				array( 'wp-element', 'wp-api-fetch', 'wp-i18n' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_add_inline_script( $this->shared->get( 'slug' ) . '-dashboard-menu', $initialization_script, 'before' );

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-new/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

		}

		// Menu juice.
		if ( $screen->id === $this->screen_id_juice ) {

			// Store the JavaScript parameters in the window.DAEXTREVOP_PARAMETERS object.
			$initialization_script  = 'window.DAIM_PARAMETERS = {';
			$initialization_script .= 'ajax_url: "' . admin_url( 'admin-ajax.php' ) . '",';
			$initialization_script .= 'read_requests_nonce: "' . wp_create_nonce( 'daextrevop_read_requests_nonce' ) . '",';
			$initialization_script .= 'admin_url: "' . get_admin_url() . '",';
			$initialization_script .= 'site_url: "' . get_site_url() . '",';
			$initialization_script .= 'plugin_url: "' . $this->shared->get( 'url' ) . '",';
			$initialization_script .= 'items_per_page: ' . intval(get_option($this->shared->get( 'slug' ) . '_pagination_juice_menu'), 10);
			$initialization_script .= '};';

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-juice-menu',
				$this->shared->get( 'url' ) . 'admin/react/juice-menu/build/index.js',
				array( 'wp-element', 'wp-api-fetch', 'wp-i18n' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_add_inline_script( $this->shared->get( 'slug' ) . '-juice-menu', $initialization_script, 'before' );

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-new/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

		}

		// Menu HTTP Status.
		if ( $screen->id === $this->screen_id_http_status ) {

			// Store the JavaScript parameters in the window.DAEXTREVOP_PARAMETERS object.
			$initialization_script  = 'window.DAIM_PARAMETERS = {';
			$initialization_script .= 'ajax_url: "' . admin_url( 'admin-ajax.php' ) . '",';
			$initialization_script .= 'read_requests_nonce: "' . wp_create_nonce( 'daextrevop_read_requests_nonce' ) . '",';
			$initialization_script .= 'admin_url: "' . get_admin_url() . '",';
			$initialization_script .= 'site_url: "' . get_site_url() . '",';
			$initialization_script .= 'plugin_url: "' . $this->shared->get( 'url' ) . '",';
			$initialization_script .= 'items_per_page: ' . intval(get_option($this->shared->get( 'slug' ) . '_pagination_http_status_menu'), 10);
			$initialization_script .= '};';

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-http-status-menu',
				$this->shared->get( 'url' ) . 'admin/react/http-status-menu/build/index.js',
				array( 'wp-element', 'wp-api-fetch', 'wp-i18n' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_add_inline_script( $this->shared->get( 'slug' ) . '-http-status-menu', $initialization_script, 'before' );

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-new/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

		}

		// Menu Hits.
		if ( $screen->id === $this->screen_id_hits ) {

			// Store the JavaScript parameters in the window.DAEXTREVOP_PARAMETERS object.
			$initialization_script  = 'window.DAIM_PARAMETERS = {';
			$initialization_script .= 'ajax_url: "' . admin_url( 'admin-ajax.php' ) . '",';
			$initialization_script .= 'read_requests_nonce: "' . wp_create_nonce( 'daextrevop_read_requests_nonce' ) . '",';
			$initialization_script .= 'admin_url: "' . get_admin_url() . '",';
			$initialization_script .= 'site_url: "' . get_site_url() . '",';
			$initialization_script .= 'plugin_url: "' . $this->shared->get( 'url' ) . '",';
			$initialization_script .= 'items_per_page: ' . intval(get_option($this->shared->get( 'slug' ) . '_pagination_hits_menu'), 10);
			$initialization_script .= '};';

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-hits-menu',
				$this->shared->get( 'url' ) . 'admin/react/hits-menu/build/index.js',
				array( 'wp-element', 'wp-api-fetch', 'wp-i18n' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_add_inline_script( $this->shared->get( 'slug' ) . '-hits-menu', $initialization_script, 'before' );

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-new/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

		}

		// Menu wizard.
		if ( $screen->id === $this->screen_id_wizard ) {

			// Store the JavaScript parameters in the window.DAEXTREVOP_PARAMETERS object.
			$initialization_script  = 'window.DAIM_PARAMETERS = {';
			$initialization_script .= 'ajax_url: "' . admin_url( 'admin-ajax.php' ) . '",';
			$initialization_script .= 'admin_url: "' . get_admin_url() . '",';
			$initialization_script .= 'wizard_nonce: "' . wp_create_nonce( 'wizard_nonce' ) . '",';
			$initialization_script .= 'plugin_url: "' . $this->shared->get( 'url' ) . '"';
			$initialization_script .= '};';

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/js/select2.min.js',
				array( 'jquery' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu-wizard', $this->shared->get( 'url' ) . 'admin/assets/js/menu-wizard.js', array( 'jquery', $this->shared->get( 'slug' ) . '-select2' ), $this->shared->get( 'ver' ), true );
			wp_localize_script( $this->shared->get( 'slug' ) . '-menu-wizard', 'objectL10n', $wp_localize_script_data );

			// Handsontable.
			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-handsontable-full',
				$this->shared->get( 'url' ) . 'admin/assets/inc/handsontable/handsontable.full.min.js',
				array( 'jquery' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_add_inline_script( $this->shared->get( 'slug' ) . '-menu-wizard', $initialization_script, 'before' );


			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-new/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

		}

		// Menu autolinks.
		if ( $screen->id === $this->screen_id_autolinks ) {

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/js/select2.min.js',
				array( 'jquery' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-new/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu-autolinks', $this->shared->get( 'url' ) . 'admin/assets/js/menu-autolinks.js', array( 'jquery', $this->shared->get( 'slug' ) . '-select2', 'jquery-ui-dialog' ), $this->shared->get( 'ver' ), true );
			wp_localize_script( $this->shared->get( 'slug' ) . '-menu-autolinks', 'objectL10n', $wp_localize_script_data );

		}

		// Menu categories.
		if ( $screen->id === $this->screen_id_categories ) {

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-new/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu-categories', $this->shared->get( 'url' ) . 'admin/assets/js/menu-categories.js', array( 'jquery', 'jquery-ui-dialog' ), $this->shared->get( 'ver' ), true );
			wp_localize_script( $this->shared->get( 'slug' ) . '-menu-categories', 'objectL10n', $wp_localize_script_data );

		}

		// Menu term groups.
		if ( $screen->id === $this->screen_id_term_groups ) {

			// Store the JavaScript parameters in the window.DAEXTREVOP_PARAMETERS object.
			$initialization_script  = 'window.DAIM_PARAMETERS = {';
			$initialization_script .= 'ajax_url: "' . admin_url( 'admin-ajax.php' ) . '",';
			$initialization_script .= 'admin_url: "' . get_admin_url() . '",';
			$initialization_script .= 'daim_nonce: "' . wp_create_nonce( 'daim' ) . '",';
			$initialization_script .= '};';

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/js/select2.min.js',
				array( 'jquery' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu-term-groups', $this->shared->get( 'url' ) . 'admin/assets/js/menu-term-groups.js', array( 'jquery', 'jquery-ui-dialog', $this->shared->get( 'slug' ) . '-select2' ), $this->shared->get( 'ver' ), true );
			wp_localize_script( $this->shared->get( 'slug' ) . '-menu-term-groups', 'objectL10n', $wp_localize_script_data );

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-new/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

			wp_add_inline_script( $this->shared->get( 'slug' ) . '-menu-term-groups', $initialization_script, 'before' );

		}

		// Menu tools.
		if ( $screen->id === $this->screen_id_tools ) {

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-new/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

		}

		// Menu Maintenance.
		if ( $screen->id === $this->screen_id_maintenance ) {

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/js/select2.min.js',
				array( 'jquery' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-new/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

			//Maintenance Menu
			wp_enqueue_script($this->shared->get('slug') . '-menu-maintenance',
				$this->shared->get('url') . 'admin/assets/js/menu-maintenance.js', array('jquery', 'jquery-ui-dialog', $this->shared->get( 'slug' ) . '-select2'),
				$this->shared->get('ver'),
			true);
			wp_localize_script($this->shared->get('slug') . '-menu-maintenance', 'objectL10n',
				$wp_localize_script_data);

		}

		// Menu options.
		if ( $screen->id === $this->screen_id_options ) {

			// Store the JavaScript parameters in the window.DAEXTDAIM_PARAMETERS object.
			$initialization_script  = 'window.DAEXTDAIM_PARAMETERS = {';
			$initialization_script .= 'ajax_url: "' . admin_url( 'admin-ajax.php' ) . '",';
			$initialization_script .= 'admin_url: "' . get_admin_url() . '",';
			$initialization_script .= 'site_url: "' . get_site_url() . '",';
			$initialization_script .= 'plugin_url: "' . $this->shared->get( 'url' ) . '",';

			require_once $this->shared->get( 'dir' ) . '/admin/inc/class-daim-menu-options.php';
			$daextdaim_menu_options = new Daim_Menu_Options();
			$initialization_script .= 'options_configuration_pages: ' . wp_json_encode( $daextdaim_menu_options->menu_options_configuration() );

			$initialization_script .= '};';

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-menu-options-new',
				$this->shared->get( 'url' ) . 'admin/react/options-menu/build/index.js',
				array( 'wp-element', 'wp-api-fetch', 'wp-i18n', 'wp-components' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_add_inline_script( $this->shared->get( 'slug' ) . '-menu-options-new', $initialization_script, 'before' );

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-new/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

		}

		/**
		 * Load the js files used in the classic editor only if the block editor js file with handler 'daim-editor-js'
		 * is not enqueued.
		 */
		if ( !wp_script_is( 'daim-editor-js', 'enqueued' ) ) {

			/**
			 * Load the post editor JS if at least one of the three meta box is
			 * enabled with the current $screen->id.
			 */
			$load_post_editor_js = false;

			$interlinks_options_post_types_a = maybe_unserialize( get_option( $this->shared->get( 'slug' ) . '_interlinks_options_post_types' ) );
			if ( is_array( $interlinks_options_post_types_a ) && in_array( $screen->id, $interlinks_options_post_types_a, true ) ) {
				$load_post_editor_js = true;
			}

			$interlinks_optimization_post_types_a = maybe_unserialize( get_option( $this->shared->get( 'slug' ) . '_interlinks_optimization_post_types' ) );
			if ( is_array( $interlinks_optimization_post_types_a ) && in_array( $screen->id, $interlinks_optimization_post_types_a, true ) ) {
				$load_post_editor_js = true;
			}

			$interlinks_suggestions_post_types_a = maybe_unserialize( get_option( $this->shared->get( 'slug' ) . '_interlinks_suggestions_post_types' ) );
			if ( is_array( $interlinks_suggestions_post_types_a ) && in_array( $screen->id, $interlinks_suggestions_post_types_a, true ) ) {
				$load_post_editor_js = true;
			}

			if ( $load_post_editor_js ) {

				// Store the JavaScript parameters in the window.DAEXTREVOP_PARAMETERS object.
				$initialization_script  = 'window.DAIM_PARAMETERS = {';
				$initialization_script .= 'ajax_url: "' . admin_url( 'admin-ajax.php' ) . '",';
				$initialization_script .= 'admin_url: "' . get_admin_url() . '",';
				$initialization_script .= 'nonce: "' . wp_create_nonce( 'daim' ) . '"';
				$initialization_script .= '};';

				// JQuery UI Tooltips.
				wp_enqueue_script( 'jquery-ui-tooltip' );
				wp_enqueue_script( $this->shared->get( 'slug' ) . '-jquery-ui-tooltip-init', $this->shared->get( 'url' ) . 'admin/assets/js/jquery-ui-tooltip-init.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

				// Post Editor Js.
				wp_enqueue_script( $this->shared->get( 'slug' ) . '-post-editor', $this->shared->get( 'url' ) . 'admin/assets/js/post-editor.js', array( 'jquery', 'wp-api-fetch' ), $this->shared->get( 'ver' ), true );

				wp_add_inline_script( $this->shared->get( 'slug' ) . '-post-editor', $initialization_script, 'before' );

				wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-new/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

			}

		}

	}

	/**
	 * Plugin activation.
	 */
	static public function ac_activate( $networkwide ) {

		/**
		 * Delete options and tables for all the sites in the network.
		 */
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			/**
			 * If this is a "Network Activation" create the options and tables
			 * for each blog.
			 */
			if ( $networkwide ) {

				// Get the current blog id.
				global $wpdb;
				$current_blog = $wpdb->blogid;

				// Create an array with all the blog ids.
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

				// iterate through all the blogs.
				foreach ( $blogids as $blog_id ) {

					// swith to the iterated blog.
					switch_to_blog( $blog_id );

					// create options and tables for the iterated blog.
					self::ac_initialize_options();
					self::ac_create_database_tables();

				}

				// switch to the current blog.
				switch_to_blog( $current_blog );

			} else {

				/**
				 * If this is not a "Network Activation" create options and
				 * tables only for the current blog.
				 */
				self::ac_initialize_options();
				self::ac_create_database_tables();

			}
		} else {

			/**
			 * if this is not a multisite installation create options and
			 *  tables only for the current blog.
			 */
			self::ac_initialize_options();
			self::ac_create_database_tables();

		}
	}

	/**
	 * Create the options and tables for the newly created blog.
	 *
	 * @param int     $blog_id The id of the blog.
	 * @param $user_id
	 * @param $domain
	 * @param $path
	 * @param $site_id
	 * @param $meta
	 *
	 * @return void
	 */
	public function new_blog_create_options_and_tables( $blog_id ) {

		global $wpdb;

		/**
		 * If the plugin is "Network Active" create the options and tables for
		 *  this new blog.
		 */
		if ( is_plugin_active_for_network( 'interlinks-manager/init.php' ) ) {

			// get the id of the current blog.
			$current_blog = $wpdb->blogid;

			// switch to the blog that is being activated.
			switch_to_blog( $blog_id );

			// create options and database tables for the new blog.
			$this->ac_initialize_options();
			$this->ac_create_database_tables();

			// switch to the current blog.
			switch_to_blog( $current_blog );

		}
	}

	/**
	 * Delete options and tables for the deleted blog.
	 *
	 * @param int $blog_id The ID of the blog.
	 *
	 * @return void
	 */
	public function delete_blog_delete_options_and_tables( $blog_id ) {

		global $wpdb;

		// get the id of the current blog.
		$current_blog = $wpdb->blogid;

		// switch to the blog that is being activated.
		switch_to_blog( $blog_id );

		// create options and database tables for the new blog.
		$this->un_delete_options();
		$this->un_delete_database_tables();

		// switch to the current blog.
		switch_to_blog( $current_blog );
	}

	/**
	 * Initialize plugin options.
	 */
	static public function ac_initialize_options() {

		if ( intval( get_option( 'daim_options_version' ), 10 ) < 1 ) {

			// assign an instance of Daim_Shared.
			$shared = Daim_Shared::get_instance();

			foreach ( $shared->get( 'options' ) as $key => $value ) {
				add_option( $key, $value );
			}

			// Update options version.
			update_option( 'daim_options_version', '1' );

		}

	}

	/**
	 * Create the plugin database tables.
	 *
	 * @return void
	 */
	static public function ac_create_database_tables() {

		// assign an instance of Daim_Shared.
		$shared = Daim_Shared::get_instance();

		global $wpdb;

		// Get the database character collate that will be appended at the end of each query.
		$charset_collate = $wpdb->get_charset_collate();

		// check database version and create the database.
		if ( intval( get_option( $shared->get( 'slug' ) . '_database_version' ), 10 ) < 6 ) {

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			// create *prefix*_archive.
			$sql = "CREATE TABLE {$wpdb->prefix}daim_archive (
                id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                post_id bigint(20) NOT NULL DEFAULT '0',
                post_title text NOT NULL DEFAULT '',
                post_permalink text NOT NULL DEFAULT '',
                post_edit_link text NOT NULL DEFAULT '',
                post_type varchar(20) NOT NULL DEFAULT '',
                post_date datetime DEFAULT NULL,
                manual_interlinks bigint(20) NOT NULL DEFAULT '0',
                auto_interlinks bigint(20) NOT NULL DEFAULT '0',
                iil bigint(20) NOT NULL DEFAULT '0',
                content_length bigint(20) NOT NULL DEFAULT '0',
                recommended_interlinks bigint(20) NOT NULL DEFAULT '0',
                num_il_clicks bigint(20) NOT NULL DEFAULT '0',
                optimization tinyint(1) NOT NULL DEFAULT '0'
            ) $charset_collate";

			dbDelta( $sql );

			// create *prefix*_juice.
			global $wpdb;
			$sql = "CREATE TABLE {$wpdb->prefix}daim_juice (
                id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                url varchar(2083) NOT NULL DEFAULT '',
                iil bigint(20) NOT NULL DEFAULT '0',
                juice bigint(20) NOT NULL DEFAULT '0',
                juice_relative bigint(20) NOT NULL DEFAULT '0'
            ) $charset_collate";

			dbDelta( $sql );

			// create *prefix*_anchors.
			global $wpdb;
			$sql = "CREATE TABLE {$wpdb->prefix}daim_anchors (
                id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                url varchar(2083) NOT NULL DEFAULT '',
                anchor longtext NOT NULL DEFAULT '',
                post_id bigint(20) NOT NULL DEFAULT '0',
                post_title text NOT NULL DEFAULT '',
                post_permalink text NOT NULL DEFAULT '',
                post_edit_link text NOT NULL DEFAULT '',
                juice bigint(20) NOT NULL DEFAULT '0'
            ) $charset_collate";

			dbDelta( $sql );

			// create *prefix*_hits.
			global $wpdb;
			$sql = "CREATE TABLE {$wpdb->prefix}daim_hits (
                id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                source_post_id bigint(20) NOT NULL DEFAULT '0',
                post_title text NOT NULL DEFAULT '',
                post_permalink text NOT NULL DEFAULT '',
                post_edit_link text NOT NULL DEFAULT '',
                target_url varchar(2083) NOT NULL DEFAULT '',
                date datetime DEFAULT NULL,
                date_gmt datetime DEFAULT NULL,
                link_type tinyint(1) NOT NULL DEFAULT '0'
            ) $charset_collate";

			dbDelta( $sql );

			// create *prefix*_autolinks.
			global $wpdb;
			$sql = "CREATE TABLE {$wpdb->prefix}daim_autolinks (
                id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                name TEXT NOT NULL DEFAULT '',
                category_id BIGINT NOT NULL DEFAULT 0,
                keyword varchar(255) NOT NULL DEFAULT '',
                url varchar(2083) NOT NULL DEFAULT '',
                title varchar(1024) NOT NULL DEFAULT '',
                string_before int(11) NOT NULL DEFAULT '1',
                string_after int(11) NOT NULL DEFAULT '1',
                keyword_before VARCHAR(255) NOT NULL DEFAULT '',
                keyword_after VARCHAR(255) NOT NULL DEFAULT '',
                activate_post_types varchar(1000) NOT NULL DEFAULT '',
                categories TEXT NOT NULL DEFAULT '',
                tags TEXT NOT NULL DEFAULT '',
                term_group_id BIGINT NOT NULL DEFAULT 0,
                max_number_autolinks int(11) NOT NULL DEFAULT '0',
                case_insensitive_search tinyint(1) NOT NULL DEFAULT '0',
                open_new_tab tinyint(1) NOT NULL DEFAULT '0',
                use_nofollow tinyint(1) NOT NULL DEFAULT '0',
                priority int(11) NOT NULL DEFAULT '0'
            ) $charset_collate";

			dbDelta( $sql );

			// create *prefix*_category.
			$sql = "CREATE TABLE {$wpdb->prefix}daim_category (
                category_id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                name TEXT,
                description TEXT
            ) $charset_collate";

			dbDelta( $sql );

			// create *prefix*_term_group.
			$query_part = '';
			for ( $i = 1; $i <= 50; $i++ ) {
				$query_part .= 'post_type_' . $i . ' TEXT,
                ';
				$query_part .= 'taxonomy_' . $i . ' TEXT,
                ';
				$query_part .= 'term_' . $i . ' BIGINT';
				if ( $i !== 50 ) {
					$query_part .= ',
                    ';
				}
			}
			$sql = "CREATE TABLE {$wpdb->prefix}daim_term_group (
                term_group_id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100),
                $query_part
            ) $charset_collate";

			dbDelta( $sql );

			// create *prefix*_http_status.
			$sql = "CREATE TABLE {$wpdb->prefix}daim_http_status (
                id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                post_id bigint(20) NOT NULL DEFAULT '0',
                post_title text NOT NULL DEFAULT '',
                post_permalink text NOT NULL DEFAULT '',
                post_edit_link text NOT NULL DEFAULT '',
                url text NOT NULL DEFAULT '',
                anchor text NOT NULL DEFAULT '',
                checked tinyint(1) NOT NULL DEFAULT 0,
                last_check_date datetime DEFAULT NULL,
                last_check_date_gmt datetime DEFAULT NULL,
                code text NOT NULL DEFAULT '',
                code_description text NOT NULL DEFAULT ''
            ) $charset_collate";

			dbDelta( $sql );

			/**
			 * Delete the statistics. This is done to avoid the statistics with
			 * previous db fields to be displayed in latest UI.
			 */
			$shared->delete_statistics();

			// Update database version.
			update_option( $shared->get( 'slug' ) . '_database_version', '6' );

			// Make the database data compatible with the new plugin versions.
			$shared->convert_database_data();

			// Make the options compatible with the new plugin versions.
			$shared->convert_options_data();

		}

	}

	/**
	 * Plugin delete.
	 */
	public static function un_delete() {

		/**
		 * Delete options and tables for all the sites in the network.
		 */
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			// get the current blog id.
			global $wpdb;
			$current_blog = $wpdb->blogid;

			// create an array with all the blog ids.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

			// iterate through all the blogs.
			foreach ( $blogids as $blog_id ) {

				// swith to the iterated blog.
				switch_to_blog( $blog_id );

				// create options and tables for the iterated blog.
				self::un_delete_options();
				self::un_delete_database_tables();

			}

			// switch to the current blog.
			switch_to_blog( $current_blog );

		} else {

			/**
			 * If this is not a multisite installation delete options and
			 * tables only for the current blog.
			 */
			self::un_delete_options();
			self::un_delete_database_tables();

		}
	}

	/**
	 * Delete plugin options.
	 */
	public static function un_delete_options() {

		// assign an instance of Daim_Shared.
		$shared = Daim_Shared::get_instance();

		foreach ( $shared->get( 'options' ) as $key => $value ) {
			delete_option( $key );
		}
	}

	/**
	 * Delete plugin database tables.
	 */
	public static function un_delete_database_tables() {

		// Assign an instance of Daim_Shared.
		$shared = Daim_Shared::get_instance();

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( "DROP TABLE {$wpdb->prefix}daim_archive" );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( "DROP TABLE {$wpdb->prefix}daim_juice" );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( "DROP TABLE {$wpdb->prefix}daim_anchors" );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( "DROP TABLE {$wpdb->prefix}daim_hits" );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( "DROP TABLE {$wpdb->prefix}daim_autolinks" );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( "DROP TABLE {$wpdb->prefix}daim_category" );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( "DROP TABLE {$wpdb->prefix}daim_term_group" );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( "DROP TABLE {$wpdb->prefix}daim_http_status" );
	}

	/**
	 * Register the admin menu.
	 */
	public function me_add_admin_menu() {

		$icon_svg = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZlcnNpb249IjEuMSIgdmlld0JveD0iMCAwIDI1NiAyNTYiPgogIDxkZWZzPgogICAgPHN0eWxlPgogICAgICAuY2xzLTEgewogICAgICAgIGZpbGw6ICNmZmY7CiAgICAgICAgc3Ryb2tlLXdpZHRoOiAwcHg7CiAgICAgIH0KICAgIDwvc3R5bGU+CiAgPC9kZWZzPgogIDxnIGlkPSJMYXllcl8xIiBkYXRhLW5hbWU9IkxheWVyIDEiPgogICAgPHBhdGggY2xhc3M9ImNscy0xIiBkPSJNMTI4LDE2YzI5LjkyLDAsNTguMDQsMTEuNjUsNzkuMiwzMi44LDIxLjE1LDIxLjE1LDMyLjgsNDkuMjgsMzIuOCw3OS4ycy0xMS42NSw1OC4wNC0zMi44LDc5LjJjLTIxLjE1LDIxLjE1LTQ5LjI4LDMyLjgtNzkuMiwzMi44cy01OC4wNC0xMS42NS03OS4yLTMyLjhjLTIxLjE1LTIxLjE1LTMyLjgtNDkuMjgtMzIuOC03OS4yczExLjY1LTU4LjA0LDMyLjgtNzkuMmMyMS4xNS0yMS4xNSw0OS4yOC0zMi44LDc5LjItMzIuOE0xMjgsMEM1Ny4zMSwwLDAsNTcuMzEsMCwxMjhzNTcuMzEsMTI4LDEyOCwxMjgsMTI4LTU3LjMxLDEyOC0xMjhTMTk4LjY5LDAsMTI4LDBoMFoiLz4KICA8L2c+CiAgPGcgaWQ9IkxheWVyXzIiIGRhdGEtbmFtZT0iTGF5ZXIgMiI+CiAgICA8cGF0aCBjbGFzcz0iY2xzLTEiIGQ9Ik0xMjgsNTZjLTE3LjY3LDAtMzIsMTQuMzMtMzIsMzJ2OGgxNnYtOGMwLTguODIsNy4xOC0xNiwxNi0xNnMxNiw3LjE4LDE2LDE2djMyYzAsOC44Mi03LjE4LDE2LTE2LDE2djE2YzE3LjY3LDAsMzItMTQuMzMsMzItMzJ2LTMyYzAtMTcuNjctMTQuMzMtMzItMzItMzJaIi8+CiAgICA8cGF0aCBjbGFzcz0iY2xzLTEiIGQ9Ik0xNDQsMTYwdjhjMCw4LjgyLTcuMTgsMTYtMTYsMTZzLTE2LTcuMTgtMTYtMTZ2LTMyYzAtOC44Miw3LjE4LTE2LDE2LTE2di0xNmMtMTcuNjcsMC0zMiwxNC4zMy0zMiwzMnYzMmMwLDE3LjY3LDE0LjMzLDMyLDMyLDMyczMyLTE0LjMzLDMyLTMydi04aC0xNloiLz4KICA8L2c+Cjwvc3ZnPg==';

		add_menu_page(
			esc_html__( 'IM', 'interlinks-manager'),
			esc_html__( 'Interlinks', 'interlinks-manager'),
			get_option( $this->shared->get( 'slug' ) . '_dashboard_menu_required_capability' ),
			$this->shared->get( 'slug' ) . '-dashboard',
			array( $this, 'me_display_menu_dashboard' ),
			$icon_svg
		);

		$this->screen_id_dashboard = add_submenu_page(
			$this->shared->get( 'slug' ) . '-dashboard',
			esc_html__( 'IM - Dashboard', 'interlinks-manager'),
			esc_html__( 'Dashboard', 'interlinks-manager'),
			get_option( $this->shared->get( 'slug' ) . '_dashboard_menu_required_capability' ),
			$this->shared->get( 'slug' ) . '-dashboard',
			array( $this, 'me_display_menu_dashboard' )
		);

		$this->screen_id_juice = add_submenu_page(
			$this->shared->get( 'slug' ) . '-dashboard',
			esc_html__( 'IM - Juice', 'interlinks-manager'),
			esc_html__( 'Juice', 'interlinks-manager'),
			get_option( $this->shared->get( 'slug' ) . '_juice_menu_required_capability' ),
			$this->shared->get( 'slug' ) . '-juice',
			array( $this, 'me_display_menu_juice' )
		);

		$this->screen_id_http_status = add_submenu_page(
			$this->shared->get( 'slug' ) . '-dashboard',
			esc_html__( 'IM - HTTP Status', 'interlinks-manager'),
			esc_html__( 'HTTP Status', 'interlinks-manager'),
			get_option( $this->shared->get( 'slug' ) . '_http_status_menu_required_capability' ),
			$this->shared->get( 'slug' ) . '-http-status',
			array( $this, 'me_display_menu_http_status' )
		);

		$this->screen_id_hits = add_submenu_page(
			$this->shared->get( 'slug' ) . '-dashboard',
			esc_html__( 'IM - Hits', 'interlinks-manager'),
			esc_html__( 'Hits', 'interlinks-manager'),
			get_option( $this->shared->get( 'slug' ) . '_hits_menu_required_capability' ),
			$this->shared->get( 'slug' ) . '-hits',
			array( $this, 'me_display_menu_hits' )
		);

		$this->screen_id_wizard = add_submenu_page(
			$this->shared->get( 'slug' ) . '-dashboard',
			esc_html__( 'IM - Wizard', 'interlinks-manager'),
			esc_html__( 'Wizard', 'interlinks-manager'),
			get_option( $this->shared->get( 'slug' ) . '_wizard_menu_required_capability' ),
			$this->shared->get( 'slug' ) . '-wizard',
			array( $this, 'me_display_menu_wizard' )
		);

		$this->screen_id_autolinks = add_submenu_page(
			$this->shared->get( 'slug' ) . '-dashboard',
			esc_html__( 'IM - AIL', 'interlinks-manager'),
			esc_html__( 'AIL', 'interlinks-manager'),
			get_option( $this->shared->get( 'slug' ) . '_ail_menu_required_capability' ),
			$this->shared->get( 'slug' ) . '-autolinks',
			array( $this, 'me_display_menu_autolinks' )
		);

		$this->screen_id_categories = add_submenu_page(
			$this->shared->get( 'slug' ) . '-dashboard',
			esc_html__( 'IM - Categories', 'interlinks-manager'),
			esc_html__( 'Categories', 'interlinks-manager'),
			get_option( $this->shared->get( 'slug' ) . '_categories_menu_required_capability' ),
			$this->shared->get( 'slug' ) . '-categories',
			array( $this, 'me_display_menu_categories' )
		);

		$this->screen_id_term_groups = add_submenu_page(
			$this->shared->get( 'slug' ) . '-dashboard',
			esc_html__( 'IM - Term Groups', 'interlinks-manager'),
			esc_html__( 'Term Groups', 'interlinks-manager'),
			get_option( $this->shared->get( 'slug' ) . '_term_groups_menu_required_capability' ),
			$this->shared->get( 'slug' ) . '-term-groups',
			array( $this, 'me_display_menu_term_groups' )
		);

		$this->screen_id_tools = add_submenu_page(
			$this->shared->get( 'slug' ) . '-dashboard',
			esc_html__( 'IM - Tools', 'interlinks-manager'),
			esc_html__( 'Tools', 'interlinks-manager'),
			get_option( $this->shared->get( 'slug' ) . '_tools_menu_required_capability' ),
			$this->shared->get( 'slug' ) . '-tools',
			array( $this, 'me_display_menu_tools' )
		);

		$this->screen_id_maintenance = add_submenu_page(
			$this->shared->get( 'slug' ) . '-dashboard',
			esc_html__( 'IM - Maintenance', 'interlinks-manager'),
			esc_html__( 'Maintenance', 'interlinks-manager'),
			get_option( $this->shared->get( 'slug' ) . '_maintenance_menu_required_capability' ),
			$this->shared->get( 'slug' ) . '-maintenance',
			array( $this, 'me_display_menu_maintenance' )
		);

		$this->screen_id_options = add_submenu_page(
			$this->shared->get( 'slug' ) . '-dashboard',
			esc_html__( 'IM - Options', 'interlinks-manager'),
			esc_html__( 'Options', 'interlinks-manager'),
			'manage_options',
			$this->shared->get( 'slug' ) . '-options',
			array( $this, 'me_display_menu_options' )
		);

		add_submenu_page(
			$this->shared->get( 'slug' ) . '-dashboard',
			esc_html__( 'Help & Support', 'interlinks-manager'),
			esc_html__( 'Help & Support', 'interlinks-manager') . '<i class="dashicons dashicons-external" style="font-size:12px;vertical-align:-2px;height:10px;"></i>',
			'manage_options',
			'https://daext.com/kb-category/interlinks-manager/',
		);

	}

	/**
	 * Includes the dashboard view.
	 */
	public function me_display_menu_dashboard() {
		include_once 'view/dashboard.php';
	}

	/**
	 * Includes the juice view.
	 */
	public function me_display_menu_juice() {
		include_once 'view/juice.php';
	}

	/**
	 * Includes the http status view.
	 */
	public function me_display_menu_http_status() {
		include_once 'view/http_status.php';
	}

	/**
	 * Includes the hits view.
	 *
	 * @return void
	 */
	public function me_display_menu_hits() {
		include_once 'view/hits.php';
	}

	/**
	 * Includes the wizard view.
	 */
	public function me_display_menu_wizard() {
		include_once 'view/wizard.php';
	}

	/**
	 * Includes the autolinks view.
	 */
	public function me_display_menu_autolinks() {
		include_once 'view/autolinks.php';
	}

	/**
	 * Includes the categories view.
	 */
	public function me_display_menu_categories() {
		include_once 'view/categories.php';
	}

	/**
	 * Includes the term groups.
	 *
	 * @return void
	 */
	public function me_display_menu_term_groups() {
		include_once 'view/term_groups.php';
	}

	/**
	 * Includes the tools view.
	 *
	 * @return void
	 */
	public function me_display_menu_tools() {
		include_once 'view/tools.php';
	}

	/**
	 * Includes the maintenance view.
	 */
	public function me_display_menu_maintenance() {
		include_once 'view/maintenance.php';
	}

	/**
	 * Includes the options view.
	 */
	public function me_display_menu_options() {
		include_once 'view/options.php';
	}

	/**
	 * Add the meta boxes.
	 *
	 * @return void
	 */
	public function create_meta_box() {

		if ( current_user_can( get_option( $this->shared->get( 'slug' ) . '_interlinks_options_mb_required_capability' ) ) ) {

			/**
			 * Load the "Interlinks Options" meta box only in the post types defined
			 * with the "Interlinks Options Post Types" option.
			 */
			$interlinks_options_post_types_a = maybe_unserialize( get_option( $this->shared->get( 'slug' ) . '_interlinks_options_post_types' ) );
			if ( is_array( $interlinks_options_post_types_a ) ) {
				foreach ( $interlinks_options_post_types_a as $key => $post_type ) {
					add_meta_box(
							'daim-meta-options',
							esc_html__( 'Interlinks Options', 'interlinks-manager'),
							array( $this, 'create_options_meta_box_callback' ),
							$post_type,
							'normal',
							'high',
							// Ref: https://make.wordpress.org/core/2018/11/07/meta-box-compatibility-flags/ .
							array(

								/*
								 * It's not confirmed that this meta box works in the block editor.
								 */
								'__block_editor_compatible_meta_box' => false,

								/*
								 * This meta box should only be loaded in the classic editor interface, and the block editor
								 * should not display it.
								 */
								'__back_compat_meta_box' => true,
							)
							);
				}
			}
		}

		if ( current_user_can( get_option( $this->shared->get( 'slug' ) . '_interlinks_optimization_mb_required_capability' ) ) ) {

			/**
			 * Load the "Interlinks Optimization" meta box only in the post types
			 * defined with the "Interlinks Optimization Post Types" option.
			 */
			$interlinks_optimization_post_types_a = maybe_unserialize( get_option( $this->shared->get( 'slug' ) . '_interlinks_optimization_post_types' ) );
			if ( is_array( $interlinks_optimization_post_types_a ) ) {
				foreach ( $interlinks_optimization_post_types_a as $key => $post_type ) {
					add_meta_box(
							'daim-meta-optimization',
							esc_html__( 'Interlinks Optimization', 'interlinks-manager'),
							array( $this, 'create_optimization_meta_box_callback' ),
							$post_type,
							'side',
							'default',
							// Ref: https://make.wordpress.org/core/2018/11/07/meta-box-compatibility-flags/ .
							array(

								/*
								 * It's not confirmed that this meta box works in the block editor.
								 */
								'__block_editor_compatible_meta_box' => false,

								/*
								 * This meta box should only be loaded in the classic editor interface, and the block editor
								 * should not display it.
								 */
								'__back_compat_meta_box' => true,
							)
					);
				}
			}
		}

		if ( current_user_can( get_option( $this->shared->get( 'slug' ) . '_interlinks_suggestions_mb_required_capability' ) ) ) {

			/**
			 * Load the "Interlinks Suggestions" meta box only in the post types
			 * defined with the "Interlinks Suggestions Post Types" option.
			 */
			$interlinks_suggestions_post_types_a = maybe_unserialize( get_option( $this->shared->get( 'slug' ) . '_interlinks_suggestions_post_types' ) );
			if ( is_array( $interlinks_suggestions_post_types_a ) ) {
				foreach ( $interlinks_suggestions_post_types_a as $key => $post_type ) {
					add_meta_box(
							'daim-meta-suggestions',
							esc_html__( 'Interlinks Suggestions', 'interlinks-manager'),
							array( $this, 'create_suggestions_meta_box_callback' ),
							$post_type,
							'side',
							'default',
							// Ref: https://make.wordpress.org/core/2018/11/07/meta-box-compatibility-flags/ .
							array(

								/*
								 * It's not confirmed that this meta box works in the block editor.
								 */
								'__block_editor_compatible_meta_box' => false,

								/*
								 * This meta box should only be loaded in the classic editor interface, and the block editor
								 * should not display it.
								 */
								'__back_compat_meta_box' => true,
							)
					);
				}
			}
		}
	}

	/**
	 * Display the Interlinks Options meta box content.
	 *
	 * @param object $post The post object.
	 *
	 * @return void
	 */
	public function create_options_meta_box_callback( $post ) {

		// retrieve the Interlinks Manager data values.
		$seo_power = get_post_meta( $post->ID, '_daim_seo_power', true );
		if ( 0 === strlen( trim( $seo_power ) ) ) {
			$seo_power = (int) get_option( $this->shared->get( 'slug' ) . '_default_seo_power' );}
		$enable_ail = get_post_meta( $post->ID, '_daim_enable_ail', true );

		// if the $enable_ail is empty use the Enable AIL option as a default.
		if ( 0 === strlen( trim( $enable_ail ) ) ) {
			$enable_ail = get_option( $this->shared->get( 'slug' ) . '_default_enable_ail_on_post' );
		}

		?>

		<table class="form-table table-interlinks-options">
			<tbody>
				
				<tr>
					<th scope="row"><label><?php esc_html_e( 'SEO Power', 'interlinks-manager'); ?></label></th>
					<td>
						<input type="text" name="daim_seo_power" value="<?php echo intval( ( $seo_power ), 10 ); ?>" class="regular-text" maxlength="7">
						<div class="help-icon" title="<?php esc_attr_e( 'The SEO Power of this post.', 'interlinks-manager'); ?>"></div>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><label><?php esc_html_e( 'Enable AIL', 'interlinks-manager'); ?></label></th>
					<td>
						<select id="daim-enable-ail"name="daim_enable_ail">
							<option <?php selected( intval( $enable_ail, 10 ), 0 ); ?> value="0"><?php esc_html_e( 'No', 'interlinks-manager'); ?></option>
							<option <?php selected( intval( $enable_ail, 10 ), 1 ); ?>value="1"><?php esc_html_e( 'Yes', 'interlinks-manager'); ?></option>
						</select>
						<div class="help-icon" title="<?php esc_attr_e( 'Select "Yes" to enable the AIL in this post.', 'interlinks-manager'); ?>"></div>

					</td>
				</tr>
				
			</tbody>
		</table>     
		
		<?php

		// Use nonce for verification.
		wp_nonce_field( plugin_basename( __FILE__ ), 'daim_nonce' );
	}

	/**
	 * Display the Interlinks Optimization meta box content.
	 *
	 * @param object $post The post object.
	 *
	 * @return void
	 */
	public function create_optimization_meta_box_callback( $post ) {

		?>

		<div class="meta-box-body">
			<table class="form-table">
				<tbody>

					<tr>
						<td></td>
					</tr>

				</tbody>
			</table>
		</div>
		
		<?php
	}

	/**
	 * Display the Interlinks Suggestions meta box content.
	 *
	 * @param object $post The post object.
	 *
	 * @return void
	 */
	public function create_suggestions_meta_box_callback( $post ) {

		?>

		<div class="meta-box-body">
			<table class="form-table">
				<tbody>

					<tr>
						<td>
							<input id="daim-interlinks-suggestions-hidden-input" type="text"/>
							<p id="daim-interlinks-suggestions-introduction"><?php esc_html_e( 'Click the "Generate" button multiple times until you find posts suitable to be used as internal links.', 'interlinks-manager'); ?></p>
							<div id="daim-interlinks-suggestions-list"></div>
						</td>
					</tr>

				</tbody>
			</table>  
		</div>

		<div id="major-publishing-actions">

			<div id="publishing-action">
				<input id="ajax-request-status" type="hidden" value="inactive">
				<span class="spinner"></span>
				<input data-post-id="<?php echo esc_attr( $post->ID ); ?>" type="button" class="button button-primary button-large" id="generate-ideas" value="<?php esc_attr_e( 'Generate', 'interlinks-manager'); ?>">
			</div>
			<div class="clear"></div>

		</div>
		
		<?php
	}

	/**
	 * Save the Interlinks Options metadata.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return void
	 */
	public function daim_save_meta_interlinks_options( $post_id ) {

		// Security verifications -----------------------------------------------.

		// Verify if this is an auto save routine.
		// If it is our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		/**
		 * Verify this came from our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */
		if ( ! isset( $_POST['daim_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['daim_nonce'] ) ), plugin_basename( __FILE__ ) ) ) {
			return;
		}

		// verify the capability.
		if ( ! current_user_can( get_option( $this->shared->get( 'slug' ) . '_interlinks_options_mb_required_capability' ) ) ) {
			return;}

		// end security verifications -------------------------------------------.

		// Save the "SEO Power" only if it's included in the allowed values.
		if ( 0 !== intval( $_POST['daim_seo_power'], 10 ) && intval( $_POST['daim_seo_power'], 10 ) <= 1000000 ) {
			update_post_meta( $post_id, '_daim_seo_power', intval( $_POST['daim_seo_power'], 10 ) );
		}

		// save the "Enable AIL".
		update_post_meta( $post_id, '_daim_enable_ail', intval( $_POST['daim_enable_ail'], 10 ) );
	}

	/**
	 * Plugin deactivation.
	 */
	static public function dc_deactivate() {
		wp_clear_scheduled_hook( 'daextdaim_cron_hook' );
	}

	/**
	 * Echo all the dismissible notices based on the values of the $notices array.
	 *
	 * @param array $notices The array with the notices class and message.
	 */
	public function dismissible_notice( $notices ) {

		foreach ( $notices as $notice ) {
			echo '<div class="' . esc_attr( $notice['class'] ) . ' settings-error notice is-dismissible below-h2"><p>' . esc_html( $notice['message'] ) . '</p></div>';
		}
	}

	/**
	 * Perform a manual license verification when the user click the provided link to verify the license.
	 *
	 * @return void
	 */
	public function manual_license_verification() {

		if ( isset( $_GET['daim_verify_license'] ) ) {

			$verify_license_nonce = isset( $_GET['daim_verify_license_nonce'] ) ? sanitize_key( $_GET['daim_verify_license_nonce'] ) : null;

			if ( wp_verify_nonce( $verify_license_nonce, 'daim_verify_license' ) ) {

				require_once $this->shared->get( 'dir' ) . 'vendor/autoload.php';
				$plugin_update_checker = new PluginUpdateChecker(DAIM_PLUGIN_UPDATE_CHECKER_SETTINGS);

				// Delete the transient used to store the plugin info previously retrieved from the remote server.
				$plugin_update_checker->delete_transient();

				// Fetch the plugin information from the remote server and saved it in the transient.
				$plugin_update_checker->fetch_remote_plugin_info();

				if ( $plugin_update_checker->is_valid_license() ) {
					$this->shared->save_dismissible_notice(
						__( 'Your license is active, and all features are now enabled. Thank you!', 'interlinks-manager' ),
						'updated'
					);
				} else {
					$this->shared->save_dismissible_notice(
						__( 'The license key provided is either invalid or could not be verified at this time. Please check your key and try again, or contact support if the issue persists.', 'interlinks-manager' ),
						'error'
					);
				}

			}

		}

	}

}