<?php
class ReviousMicrodataSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin', 
            'Revious Microdata Settings', 
            'manage_options', 
            'revious-microdata-setting-admin', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'revious_microdata_option_name' );
        ?>
        <div class="wrap">
            <h1>Revious Microdata Settings</h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'revious_microdata_option_group' );
                do_settings_sections( 'revious-microdata-setting-admin' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'revious_microdata_option_group', // Option group
            'revious_microdata_option_name', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'Revious Microdata Custom Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'revious-microdata-setting-admin' // Page
        );  

        // add_settings_field(
        //     'id_number', // ID
        //     'ID Number', // Title 
        //     array( $this, 'id_number_callback' ), // Callback
        //     'revious-microdata-setting-admin', // Page
        //     'setting_section_id' // Section           
        // );      

        // add_settings_field(
        //     'title', 
        //     'Title', 
        //     array( $this, 'title_callback' ), 
        //     'revious-microdata-setting-admin', 
        //     'setting_section_id'
        // );      
        add_settings_field(
            'shortcode_names', 
            'Enabled Shortcodes Names (comma separated)', 
            array( $this, 'shortcode_names_callback' ), 
            'revious-microdata-setting-admin', 
            'setting_section_id'
        );  

        add_settings_field(
            'wnd_default_image_settings_enabled', 
            'Enable Default Image Settings (for inserting images in post editor)', 
            array( $this, 'wnd_default_image_settings_enabled_callback' ), 
            'revious-microdata-setting-admin', 
            'setting_section_id'
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['id_number'] ) )
            $new_input['id_number'] = absint( $input['id_number'] );

        if( isset( $input['title'] ) )
            $new_input['title'] = sanitize_text_field( $input['title'] );

        if( isset( $input['shortcode_names'] ) )
            $new_input['shortcode_names'] = sanitize_text_field( $input['shortcode_names'] );

        if( isset( $input['wnd_default_image_settings_enabled'] ) )
            $new_input['wnd_default_image_settings_enabled'] = sanitize_text_field( $input['wnd_default_image_settings_enabled'] );

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    // public function id_number_callback()
    // {
    //     printf(
    //         '<input type="text" id="id_number" name="revious_microdata_option_name[id_number]" value="%s" />',
    //         isset( $this->options['id_number'] ) ? esc_attr( $this->options['id_number']) : ''
    //     );
    // }

    /** 
     * Get the settings option array and print one of its values
     */
    // public function title_callback()
    // {
    //     printf(
    //         '<input type="text" id="title" name="revious_microdata_option_name[title]" value="%s" />',
    //         isset( $this->options['title'] ) ? esc_attr( $this->options['title']) : ''
    //     );
    // }

    public function shortcode_names_callback()
    {
        printf(
            '<input type="text" id="shortcode_names" name="revious_microdata_option_name[shortcode_names]" value="%s" />',
            isset( $this->options['shortcode_names'] ) ? esc_attr( $this->options['shortcode_names']) : ''
        );
    }

    public function wnd_default_image_settings_enabled_callback() {
        //var_dump($this->options['wnd_default_image_settings_enabled']);
        $option_checked = '';
        if(isset($this->options['wnd_default_image_settings_enabled']) && $this->options['wnd_default_image_settings_enabled'] == 'on') {
            $option_checked = ' checked="checked" ';
            $this->wnd_default_image_settings();
            //$this->revious_microdata_after_setup_theme();
        }
        // else {
        //     $this->wnd_custom_image_settings();
        // }
        printf(
            '<input type="checkbox" id="wnd_default_image_settings_enabled" name="revious_microdata_option_name[wnd_default_image_settings_enabled]"' . $option_checked . ' />',
            isset( $this->options['wnd_default_image_settings_enabled'] ) ? esc_attr( $this->options['wnd_default_image_settings_enabled']) : ''
        );
    }

    // public function revious_microdata_after_setup_theme() {
    //     add_action('after_setup_theme', array($this, 'wnd_default_image_settings'));
    // }

    public function wnd_default_image_settings() {
        update_option('image_default_align', 'left');
        update_option('image_default_link_type', 'none');
        update_option('image_default_size', 'full-size');
    }

    // public function wnd_custom_image_settings() {
    //     update_option('image_default_align', 'right');
    //     update_option('image_default_link_type', 'file');
    //     update_option('image_default_size', 'small');
    // }

}

if( is_admin() )
    $revious_microdata_settings_page = new ReviousMicrodataSettingsPage();