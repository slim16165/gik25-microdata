<?php
namespace gik25microdata;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ReviousMicrodataSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;
    
    /**
     * Istanza singleton (per accesso da AdminMenu)
     */
    private static $instance = null;

    /**
     * Start up
     */
    public function __construct()
    {   
        self::$instance = $this;
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ), 15 );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }
    
    /**
     * Ottieni istanza corrente (per uso da AdminMenu)
     */
    public static function get_instance(): ?self
    {
        return self::$instance;
    }
    
    /**
     * Renderizza la pagina (metodo statico per uso da AdminMenu)
     */
    public static function render_page(): void
    {
        if (self::$instance) {
            self::$instance->create_admin_page();
        } else {
            // Se non c'è istanza, creane una temporanea
            $temp_instance = new self();
            $temp_instance->create_admin_page();
        }
    }

    /**
     * Add options page
     * 
     * Nota: La pagina viene ora registrata come sottovocce del menu principale "Revious Microdata"
     * Se il menu principale non esiste, viene comunque aggiunta sotto "Impostazioni" come fallback
     */
    public function add_plugin_page(): void
    {
        // Verifica se il menu principale esiste (registrato da AdminMenu)
        global $submenu;
        $menu_exists = isset($submenu['revious-microdata']);
        
        if ($menu_exists) {
            // Menu principale esiste, la sottovocce viene aggiunta automaticamente da AdminMenu
            // Qui registriamo solo la callback se necessario
            // La pagina viene renderizzata quando si accede al link
        } else {
            // Fallback: aggiungi sotto "Impostazioni" se il menu principale non esiste
            add_options_page(
                'Settings Admin', 
                'Revious Microdata Settings', 
                'manage_options', 
                'revious-microdata-setting-admin', 
                array( $this, 'create_admin_page' )
            );
        }
    }

    /**
     * Options page callback
     * Metodo pubblico per renderizzare la pagina (può essere chiamato da AdminMenu)
     */
    public function create_admin_page(): void
    {
        // Set class property
        $this->options = get_option( 'revious_microdata_option_name' );
        ?>
        <div class="wrap">
            <h1>Revious Microdata - Impostazioni</h1>
            <p>Configura le impostazioni del plugin. Le modifiche verranno applicate immediatamente.</p>
            
            <form method="post" action="options.php" class="revious-microdata-settings-form">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'revious_microdata_option_group' );
                do_settings_sections( 'revious-microdata-setting-admin' );
                submit_button('Salva Impostazioni', 'primary', 'submit', true);
            ?>
            </form>
        </div>
        
        <style>
            .revious-microdata-settings-form {
                background: #fff;
                padding: 20px;
                border: 1px solid #c3c4c7;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
                margin-top: 20px;
                max-width: 1200px;
            }
            .revious-microdata-settings-form .form-table {
                margin-top: 20px;
            }
            .revious-microdata-settings-form .form-table th {
                padding: 20px 10px 20px 0;
                width: 200px;
                font-weight: 600;
            }
            .revious-microdata-settings-form .form-table td {
                padding: 15px 10px;
            }
            .revious-microdata-settings-form .description {
                color: #646970;
                font-style: italic;
                margin-top: 5px;
            }
            .shortcodes-list {
                border: 1px solid #c3c4c7;
                padding: 15px;
                background: #f6f7f7;
                border-radius: 4px;
                max-height: 500px;
                overflow-y: auto;
            }
            .shortcode-item {
                padding: 12px;
                margin-bottom: 12px;
                background: #fff;
                border: 1px solid #dcdcde;
                border-radius: 4px;
                transition: all 0.2s ease;
            }
            .shortcode-item:hover {
                border-color: #2271b1;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .shortcode-checkbox {
                display: flex;
                align-items: center;
                cursor: pointer;
                margin-bottom: 8px;
            }
            .shortcode-checkbox input[type="checkbox"] {
                margin-right: 10px;
                width: 18px;
                height: 18px;
                cursor: pointer;
            }
            .shortcode-name {
                font-weight: 600;
                font-size: 14px;
            }
            .shortcode-name code {
                background: #f0f0f1;
                padding: 2px 6px;
                border-radius: 3px;
                font-size: 13px;
                color: #2271b1;
            }
            .shortcode-doc {
                margin-top: 8px;
                padding-left: 28px;
                font-size: 13px;
                color: #646970;
            }
            .shortcode-description {
                margin: 0 0 8px 0;
                line-height: 1.5;
            }
            .shortcode-usage,
            .shortcode-aliases {
                margin: 4px 0;
                font-size: 12px;
            }
            .shortcode-usage code,
            .shortcode-aliases code {
                background: #f0f0f1;
                padding: 2px 6px;
                border-radius: 3px;
                font-size: 12px;
                color: #1d2327;
            }
            .shortcode-usage strong,
            .shortcode-aliases strong {
                color: #1d2327;
                font-weight: 600;
            }
        </style>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init(): void
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
            'Carica CSS/JS per questi shortcode', 
            array( $this, 'shortcode_names_callback' ), 
            'revious-microdata-setting-admin', 
            'setting_section_id'
        );  

        add_settings_field(
            'wnd_default_image_settings_enabled', 
            'Abilita impostazioni immagine predefinite (per inserimento immagini nell\'editor)', 
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
    public function sanitize( $input ): array
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
    public function print_section_info(): void
    {
        print '<p>Seleziona gli shortcode per i quali vuoi caricare automaticamente CSS e JS. Gli shortcode selezionati avranno i loro stili e script caricati in tutte le pagine.</p>';
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

    public function shortcode_names_callback(): void
    {
        global $shortcode_tags;
        $plugin_shortcodes = array();
        $shortcode_docs = $this->get_shortcode_documentation();

        foreach($shortcode_tags as $k => $v) {
            $needle_found = strpos($k, PLUGIN_NAME_PREFIX);
            if(is_int($needle_found) &&  $needle_found == 0) {
                $plugin_shortcodes[] = $k;
            }
        }

        // Costruisci HTML con documentazione
        $plugin_shortcodes_html = '<div id="plugin_shortcodes_wrap" class="shortcodes-list">';
        
        foreach($plugin_shortcodes as $plugin_shortcode) {
            $doc = $shortcode_docs[$plugin_shortcode] ?? null;
            $aliases = $this->get_shortcode_aliases($plugin_shortcode);
            
            $plugin_shortcodes_html .= '<div class="shortcode-item">';
            $plugin_shortcodes_html .= '<label class="shortcode-checkbox">';
            $plugin_shortcodes_html .= '<input id="' . esc_attr($plugin_shortcode) . '" type="checkbox" name="plugin_shortcodes[]" value="' . esc_attr($plugin_shortcode) . '">';
            $plugin_shortcodes_html .= '<span class="shortcode-name"><code>' . esc_html($plugin_shortcode) . '</code></span>';
            $plugin_shortcodes_html .= '</label>';
            
            if ($doc) {
                $plugin_shortcodes_html .= '<div class="shortcode-doc">';
                $plugin_shortcodes_html .= '<p class="shortcode-description">' . esc_html($doc['description']) . '</p>';
                if (!empty($doc['usage'])) {
                    $plugin_shortcodes_html .= '<p class="shortcode-usage"><strong>Uso:</strong> <code>' . esc_html($doc['usage']) . '</code></p>';
                }
                if (!empty($aliases)) {
                    $plugin_shortcodes_html .= '<p class="shortcode-aliases"><strong>Alias:</strong> <code>' . implode('</code>, <code>', array_map('esc_html', $aliases)) . '</code></p>';
                }
                $plugin_shortcodes_html .= '</div>';
            } elseif (!empty($aliases)) {
                $plugin_shortcodes_html .= '<div class="shortcode-doc">';
                $plugin_shortcodes_html .= '<p class="shortcode-aliases"><strong>Alias:</strong> <code>' . implode('</code>, <code>', array_map('esc_html', $aliases)) . '</code></p>';
                $plugin_shortcodes_html .= '</div>';
            }
            
            $plugin_shortcodes_html .= '</div>';
        }
        
        $plugin_shortcodes_html .= '</div>';

        $js_script = <<<BBB
            <script>
                window.addEventListener('load', function() {
                    const btnSubmit = document.getElementById('submit');
                    const shortcodeNames = document.getElementById('shortcode_names');
                    const shortcodeNamesStr = shortcodeNames.value;
                    const shortcodeNamesArr = shortcodeNamesStr ? shortcodeNamesStr.split(',') : [];
                    const pluginShortcodes = document.getElementsByName('plugin_shortcodes[]');

                    for(var i = 0; i < pluginShortcodes.length; i++) {
                        if(shortcodeNamesArr.includes(pluginShortcodes[i].value)) {
                            pluginShortcodes[i].checked = 'checked';
                        }
                    }

                    btnSubmit.addEventListener('click', function(e) {
                        let enabledShortcodes = '';
                        for(let i = 0; i < pluginShortcodes.length; i++) {
                            if(pluginShortcodes[i].checked) {
                                enabledShortcodes += pluginShortcodes[i].value + ',';
                            }
                        }
                        enabledShortcodes = enabledShortcodes.slice(0, -1);
                        shortcodeNames.value = enabledShortcodes;
                    });
                });
            </script>
BBB;

        echo $plugin_shortcodes_html . $js_script;

        printf(
            '<input size="500" type="hidden" id="shortcode_names" name="revious_microdata_option_name[shortcode_names]" value="%s" />',
            isset( $this->options['shortcode_names'] ) ? esc_attr( $this->options['shortcode_names']) : ''
        );
    }

    /**
     * Ottieni documentazione shortcode
     */
    private function get_shortcode_documentation(): array
    {
        return [
            'md_quote' => [
                'description' => 'Mostra una citazione in formato blockquote. Utile per evidenziare frasi importanti o citazioni.',
                'usage' => '[md_quote]Testo della citazione[/md_quote]',
            ],
            'md_boxinfo' => [
                'description' => 'Box informativo con titolo opzionale. Perfetto per curiosità, note o informazioni aggiuntive.',
                'usage' => '[md_boxinfo title="Titolo"]Contenuto del box[/md_boxinfo]',
            ],
            'md_progressbar' => [
                'description' => 'Barra di progresso animata. Mostra visivamente lo stato di completamento di un processo.',
                'usage' => '[md_progressbar]',
            ],
            'md_slidingbox' => [
                'description' => 'Box con contenuto che scorre. Include un\'immagine di sfondo e testo scorrevole.',
                'usage' => '[md_slidingbox bg_img="url"]Contenuto[/md_slidingbox]',
            ],
            'md_flipbox' => [
                'description' => 'Box con effetto flip. Mostra un lato con immagine e l\'altro con testo quando si passa il mouse.',
                'usage' => '[md_flipbox]Contenuto[/md_flipbox]',
            ],
            'md_blinkingbutton' => [
                'description' => 'Pulsante con effetto lampeggiante. Attira l\'attenzione su call-to-action importanti.',
                'usage' => '[md_blinkingbutton]Testo pulsante[/md_blinkingbutton]',
            ],
            'md_perfectpullquote' => [
                'description' => 'Pullquote perfetto per citazioni. Stile elegante per evidenziare estratti di testo.',
                'usage' => '[md_perfectpullquote]Citazione[/md_perfectpullquote]',
            ],
            'md_youtube' => [
                'description' => 'Incorpora video YouTube. Carica automaticamente il video tramite oEmbed di WordPress.',
                'usage' => '[md_youtube url="https://youtube.com/watch?v=..."]',
            ],
            'md_telefono' => [
                'description' => 'Mostra un numero di telefono cliccabile. Aggiunge microdata per SEO e migliora l\'accessibilità.',
                'usage' => '[md_telefono]+39 123 456 7890[/md_telefono]',
            ],
            'md_prezzo' => [
                'description' => 'Mostra un prezzo formattato. Include microdata per dati strutturati e migliora la SEO.',
                'usage' => '[md_prezzo]29.99[/md_prezzo]',
            ],
            'md_flexlist' => [
                'description' => 'Lista flessibile e personalizzabile. Utile per elenchi stilizzati e responsive.',
                'usage' => '[md_flexlist]Item 1|Item 2|Item 3[/md_flexlist]',
            ],
        ];
    }

    /**
     * Ottieni alias di uno shortcode
     */
    private function get_shortcode_aliases(string $shortcode): array
    {
        $aliases_map = [
            'md_quote' => ['quote'],
            'md_boxinfo' => ['boxinfo', 'boxinformativo'],
            'md_progressbar' => ['progressbar'],
            'md_slidingbox' => ['slidingbox'],
            'md_flipbox' => ['flipbox'],
            'md_blinkingbutton' => ['blinkingbutton'],
            'md_youtube' => ['youtube'],
            'md_telefono' => ['telefono', 'microdata_telefono'],
            'md_prezzo' => ['prezzo'],
            'md_flexlist' => ['flexlist'],
        ];
        
        return $aliases_map[$shortcode] ?? [];
    }

    public function wnd_default_image_settings_enabled_callback(): void
    {
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

    public function wnd_default_image_settings(): void
    {
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

// Nota: L'istanziazione di ReviousMicrodataSettingsPage è ora gestita centralmente
// nel file bootstrap revious-microdata.php per mantenere la logica di inizializzazione centralizzata
