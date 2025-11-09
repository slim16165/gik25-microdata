<?php
namespace gik25microdata\Admin;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class SettingsPage
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
        
        // Verifica se siamo in una pagina con tab (Dashboard unificata)
        $is_tabbed_page = isset($_GET['page']) && $_GET['page'] === 'revious-microdata' && isset($_GET['tab']) && $_GET['tab'] === 'settings';
        ?>
        <div class="wrap">
            <?php if (!$is_tabbed_page): ?>
            <h1>Revious Microdata - Impostazioni</h1>
            <?php endif; ?>
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
            /* Shortcode Manager - Layout tipo Elementor */
            .shortcode-manager-wrapper {
                margin-top: 20px;
            }
            .shortcode-manager-filters {
                margin-bottom: 20px;
                padding: 20px;
                background: #fff;
                border: 1px solid #c3c4c7;
                border-radius: 4px;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
            }
            .shortcode-search {
                margin-bottom: 15px;
            }
            .shortcode-search-field {
                width: 100%;
                max-width: 400px;
                padding: 8px 12px;
                border: 1px solid #8c8f94;
                border-radius: 4px;
                font-size: 14px;
            }
            .shortcode-search-field:focus {
                border-color: #2271b1;
                outline: none;
                box-shadow: 0 0 0 1px #2271b1;
            }
            .shortcode-category-filters {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }
            .category-filter {
                padding: 8px 16px;
                background: #f6f7f7;
                border: 1px solid #dcdcde;
                border-radius: 4px;
                cursor: pointer;
                font-size: 14px;
                transition: all 0.2s ease;
            }
            .category-filter:hover {
                background: #f0f0f1;
                border-color: #8c8f94;
            }
            .category-filter.active {
                background: #2271b1;
                color: #fff;
                border-color: #2271b1;
            }
            
            /* Grid Shortcode Cards */
            .shortcode-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 20px;
                margin-top: 20px;
            }
            .shortcode-card {
                background: #fff;
                border: 1px solid #dcdcde;
                border-radius: 8px;
                overflow: hidden;
                transition: all 0.3s ease;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                display: flex;
                flex-direction: column;
            }
            .shortcode-card:hover {
                border-color: #2271b1;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                transform: translateY(-2px);
            }
            .shortcode-card-header {
                position: relative;
                padding: 20px;
                background: linear-gradient(135deg, #f6f7f7 0%, #fff 100%);
                border-bottom: 1px solid #e5e5e5;
            }
            .shortcode-card-icon {
                width: 64px;
                height: 64px;
                margin: 0 auto 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #fff;
                border-radius: 8px;
                border: 1px solid #e5e5e5;
                overflow: hidden;
            }
            .shortcode-icon-img {
                width: 100%;
                height: 100%;
                object-fit: contain;
                padding: 8px;
            }
            .shortcode-icon-placeholder,
            .shortcode-icon-dashicon {
                width: 100%;
                height: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #2271b1;
            }
            .shortcode-icon-dashicon .dashicons {
                font-size: 32px;
                width: 32px;
                height: 32px;
            }
            .shortcode-card-toggle {
                position: absolute;
                top: 15px;
                right: 15px;
            }
            .shortcode-toggle-switch {
                position: relative;
                display: inline-block;
                width: 44px;
                height: 24px;
            }
            .shortcode-toggle-switch input {
                opacity: 0;
                width: 0;
                height: 0;
            }
            .toggle-slider {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #ccc;
                transition: .4s;
                border-radius: 24px;
            }
            .toggle-slider:before {
                position: absolute;
                content: "";
                height: 18px;
                width: 18px;
                left: 3px;
                bottom: 3px;
                background-color: white;
                transition: .4s;
                border-radius: 50%;
            }
            .shortcode-toggle-switch input:checked + .toggle-slider {
                background-color: #2271b1;
            }
            .shortcode-toggle-switch input:checked + .toggle-slider:before {
                transform: translateX(20px);
            }
            
            .shortcode-card-body {
                padding: 15px 20px;
                flex-grow: 1;
            }
            .shortcode-card-title {
                margin: 0 0 10px 0;
                font-size: 16px;
                font-weight: 600;
                color: #1d2327;
            }
            .shortcode-card-description {
                margin: 0 0 12px 0;
                font-size: 13px;
                color: #646970;
                line-height: 1.5;
            }
            .shortcode-card-aliases {
                margin: 12px 0;
                font-size: 12px;
            }
            .shortcode-card-aliases strong {
                display: block;
                margin-bottom: 5px;
                color: #1d2327;
                font-weight: 600;
            }
            .shortcode-alias-tag {
                display: inline-block;
                margin: 2px 4px 2px 0;
                padding: 2px 6px;
                background: #f0f0f1;
                border-radius: 3px;
                font-size: 11px;
                color: #2271b1;
            }
            .shortcode-card-usage {
                margin-top: 12px;
                padding: 8px;
                background: #f6f7f7;
                border-radius: 4px;
            }
            .shortcode-usage-code {
                font-size: 11px;
                color: #1d2327;
                word-break: break-all;
            }
            
            .shortcode-card-footer {
                padding: 10px 20px;
                background: #f6f7f7;
                border-top: 1px solid #e5e5e5;
            }
            .shortcode-card-category {
                font-size: 11px;
                color: #646970;
                text-transform: uppercase;
                font-weight: 600;
                letter-spacing: 0.5px;
            }
            
            .shortcode-no-results {
                text-align: center;
                padding: 40px 20px;
                color: #646970;
            }
            
            /* Responsive */
            @media (max-width: 782px) {
                .shortcode-grid {
                    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
                    gap: 15px;
                }
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
        // Gestione shortcode spostata in ShortcodesManagerPage
        // add_settings_field(
        //     'shortcode_names', 
        //     'Carica CSS/JS per questi shortcode', 
        //     array( $this, 'shortcode_names_callback' ), 
        //     'revious-microdata-setting-admin', 
        //     'setting_section_id'
        // );  

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
        print '<p>Configura le impostazioni generali del plugin. Per gestire gli shortcode, visita la pagina <a href="' . esc_url(admin_url('admin.php?page=revious-microdata-shortcodes')) . '">Shortcodes</a>.</p>';
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
        $shortcode_categories = $this->get_shortcode_categories();

        foreach($shortcode_tags as $k => $v) {
            $needle_found = strpos($k, PLUGIN_NAME_PREFIX);
            if(is_int($needle_found) &&  $needle_found == 0) {
                $plugin_shortcodes[] = $k;
            }
        }

        // Organizza shortcode per categoria
        $shortcodes_by_category = [];
        foreach($plugin_shortcodes as $shortcode) {
            $category = $shortcode_categories[$shortcode] ?? 'other';
            if (!isset($shortcodes_by_category[$category])) {
                $shortcodes_by_category[$category] = [];
            }
            $shortcodes_by_category[$category][] = $shortcode;
        }

        // Genera HTML con layout a card tipo Elementor
        ?>
        <div class="shortcode-manager-wrapper">
            <!-- Filtri e Ricerca -->
            <div class="shortcode-manager-filters">
                <div class="shortcode-search">
                    <input type="text" id="shortcode-search-input" placeholder="Cerca shortcode..." class="shortcode-search-field">
                </div>
                <div class="shortcode-category-filters">
                    <button class="category-filter active" data-category="all">Tutti</button>
                    <button class="category-filter" data-category="visual">🎨 Widget Grafici</button>
                    <button class="category-filter" data-category="functional">⚙️ Shortcode Funzionali</button>
                    <button class="category-filter" data-category="totaldesign">🏠 Widget TotalDesign</button>
                </div>
            </div>

            <!-- Grid Shortcode Cards -->
            <div class="shortcode-grid" id="shortcode-grid">
                <?php
                foreach($shortcodes_by_category as $category => $shortcodes) {
                    foreach($shortcodes as $plugin_shortcode) {
                        $doc = $shortcode_docs[$plugin_shortcode] ?? null;
                        $aliases = $this->get_shortcode_aliases($plugin_shortcode);
                        $icon = $this->get_shortcode_icon($plugin_shortcode);
                        $category_label = $this->get_category_label($category);
                        
                        $this->render_shortcode_card($plugin_shortcode, $doc, $aliases, $icon, $category, $category_label);
                    }
                }
                ?>
            </div>

            <!-- Messaggio nessun risultato -->
            <div class="shortcode-no-results" id="shortcode-no-results" style="display: none;">
                <p>Nessuno shortcode trovato. Prova a modificare i filtri o la ricerca.</p>
            </div>
        </div>

        <?php
        $this->render_shortcode_manager_scripts();

        printf(
            '<input size="500" type="hidden" id="shortcode_names" name="revious_microdata_option_name[shortcode_names]" value="%s" />',
            isset( $this->options['shortcode_names'] ) ? esc_attr( $this->options['shortcode_names']) : ''
        );
    }

    /**
     * Renderizza una card shortcode
     */
    private function render_shortcode_card(string $shortcode, ?array $doc, array $aliases, string $icon, string $category, string $category_label): void
    {
        $enabled_shortcodes = isset($this->options['shortcode_names']) ? explode(',', $this->options['shortcode_names']) : [];
        $is_enabled = in_array($shortcode, $enabled_shortcodes);
        ?>
        <div class="shortcode-card" data-category="<?php echo esc_attr($category); ?>" data-shortcode="<?php echo esc_attr($shortcode); ?>">
            <div class="shortcode-card-header">
                <div class="shortcode-card-icon">
                    <?php if (strpos($icon, 'dashicon:') === 0): ?>
                        <?php 
                        $dashicon_class = str_replace('dashicon:', '', $icon);
                        ?>
                        <div class="shortcode-icon-dashicon">
                            <span class="dashicons <?php echo esc_attr($dashicon_class); ?>"></span>
                        </div>
                    <?php elseif ($icon): ?>
                        <img src="<?php echo esc_url($icon); ?>" alt="<?php echo esc_attr($shortcode); ?>" class="shortcode-icon-img">
                    <?php else: ?>
                        <div class="shortcode-icon-placeholder">
                            <span class="dashicons dashicons-shortcode"></span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="shortcode-card-toggle">
                    <label class="shortcode-toggle-switch">
                        <input type="checkbox" name="plugin_shortcodes[]" value="<?php echo esc_attr($shortcode); ?>" <?php checked($is_enabled); ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
            <div class="shortcode-card-body">
                <h3 class="shortcode-card-title"><?php echo esc_html($shortcode); ?></h3>
                <?php if ($doc && !empty($doc['description'])): ?>
                    <p class="shortcode-card-description"><?php echo esc_html($doc['description']); ?></p>
                <?php endif; ?>
                <?php if (!empty($aliases)): ?>
                    <div class="shortcode-card-aliases">
                        <strong>Alias:</strong>
                        <?php foreach($aliases as $alias): ?>
                            <code class="shortcode-alias-tag"><?php echo esc_html($alias); ?></code>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if ($doc && !empty($doc['usage'])): ?>
                    <div class="shortcode-card-usage">
                        <code class="shortcode-usage-code"><?php echo esc_html($doc['usage']); ?></code>
                    </div>
                <?php endif; ?>
            </div>
            <div class="shortcode-card-footer">
                <span class="shortcode-card-category"><?php echo esc_html($category_label); ?></span>
            </div>
        </div>
        <?php
    }

    /**
     * Renderizza script per gestione shortcode manager
     */
    private function render_shortcode_manager_scripts(): void
    {
        ?>
        <script>
        (function() {
            const searchInput = document.getElementById('shortcode-search-input');
            const categoryFilters = document.querySelectorAll('.category-filter');
            const shortcodeCards = document.querySelectorAll('.shortcode-card');
            const shortcodeGrid = document.getElementById('shortcode-grid');
            const noResults = document.getElementById('shortcode-no-results');
            const shortcodeNamesInput = document.getElementById('shortcode_names');

            // Funzione per filtrare shortcode
            function filterShortcodes() {
                const searchTerm = searchInput.value.toLowerCase();
                const activeCategory = document.querySelector('.category-filter.active')?.dataset.category || 'all';
                let visibleCount = 0;

                shortcodeCards.forEach(card => {
                    const shortcode = card.dataset.shortcode.toLowerCase();
                    const category = card.dataset.category;
                    const description = card.querySelector('.shortcode-card-description')?.textContent.toLowerCase() || '';
                    const aliases = Array.from(card.querySelectorAll('.shortcode-alias-tag')).map(tag => tag.textContent.toLowerCase()).join(' ');

                    const matchesSearch = !searchTerm || 
                        shortcode.includes(searchTerm) || 
                        description.includes(searchTerm) || 
                        aliases.includes(searchTerm);
                    
                    const matchesCategory = activeCategory === 'all' || category === activeCategory;

                    if (matchesSearch && matchesCategory) {
                        card.style.display = 'block';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Mostra/nascondi messaggio nessun risultato
                if (visibleCount === 0) {
                    noResults.style.display = 'block';
                    shortcodeGrid.style.display = 'none';
                } else {
                    noResults.style.display = 'none';
                    shortcodeGrid.style.display = 'grid';
                }
            }

            // Event listener per ricerca
            searchInput.addEventListener('input', filterShortcodes);

            // Event listener per filtri categoria
            categoryFilters.forEach(filter => {
                filter.addEventListener('click', function() {
                    categoryFilters.forEach(f => f.classList.remove('active'));
                    this.classList.add('active');
                    filterShortcodes();
                });
            });

            // Salva stato checkboxes al submit
            const submitButton = document.getElementById('submit');
            if (submitButton) {
                submitButton.addEventListener('click', function(e) {
                    const checkboxes = document.querySelectorAll('input[name="plugin_shortcodes[]"]:checked');
                    const enabledShortcodes = Array.from(checkboxes).map(cb => cb.value).join(',');
                    shortcodeNamesInput.value = enabledShortcodes;
                });
            }

            // Inizializza stato checkboxes
            const enabledShortcodes = shortcodeNamesInput.value ? shortcodeNamesInput.value.split(',') : [];
            enabledShortcodes.forEach(code => {
                const checkbox = document.querySelector(`input[name="plugin_shortcodes[]"][value="${code}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                }
            });
        })();
        </script>
        <?php
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

    /**
     * Ottieni categoria di uno shortcode
     */
    private function get_shortcode_categories(): array
    {
        return [
            // Widget Grafici
            'md_quote' => 'visual',
            'md_boxinfo' => 'visual',
            'md_progressbar' => 'visual',
            'md_slidingbox' => 'visual',
            'md_flipbox' => 'visual',
            'md_blinkingbutton' => 'visual',
            'md_perfectpullquote' => 'visual',
            'md_flexlist' => 'visual',
            
            // Shortcode Funzionali
            'md_youtube' => 'functional',
            'md_telefono' => 'functional',
            'md_prezzo' => 'functional',
            
            // Widget TotalDesign (se presenti)
            'kitchen_finder' => 'totaldesign',
            'app_nav' => 'totaldesign',
            'link_colori' => 'totaldesign',
            'grafica3d' => 'totaldesign',
            'archistar' => 'totaldesign',
        ];
    }

    /**
     * Ottieni icona di uno shortcode
     * Ritorna 'dashicon' per usare Dashicons, o URL immagine
     */
    private function get_shortcode_icon(string $shortcode): string
    {
        $plugin_url = plugins_url('gik25-microdata');
        $icons_map = [
            // Widget Grafici - usa immagini esistenti quando disponibili
            'md_quote' => $plugin_url . '/assets/images/quote-left.png',
            'md_boxinfo' => 'dashicon:dashicons-info',
            'md_progressbar' => 'dashicon:dashicons-performance',
            'md_slidingbox' => $plugin_url . '/assets/images/icon-sliding-box.png',
            'md_flipbox' => $plugin_url . '/assets/images/icon-flipbox.png',
            'md_blinkingbutton' => $plugin_url . '/assets/images/icon-blinking-button.png',
            'md_perfectpullquote' => $plugin_url . '/assets/images/quote-left.png',
            'md_flexlist' => 'dashicon:dashicons-list-view',
            
            // Shortcode Funzionali - usa dashicons
            'md_youtube' => 'dashicon:dashicons-video-alt3',
            'md_telefono' => 'dashicon:dashicons-phone',
            'md_prezzo' => 'dashicon:dashicons-money-alt',
            
            // Widget TotalDesign - usa dashicons appropriati
            'kitchen_finder' => 'dashicon:dashicons-building',
            'app_nav' => 'dashicon:dashicons-menu',
            'link_colori' => 'dashicon:dashicons-art',
            'grafica3d' => 'dashicon:dashicons-desktop',
            'archistar' => 'dashicon:dashicons-star-filled',
        ];
        
        return $icons_map[$shortcode] ?? 'dashicon:dashicons-shortcode';
    }

    /**
     * Ottieni etichetta categoria
     */
    private function get_category_label(string $category): string
    {
        $labels = [
            'visual' => 'Widget Grafico',
            'functional' => 'Shortcode Funzionale',
            'totaldesign' => 'Widget TotalDesign',
            'other' => 'Altro',
        ];
        
        return $labels[$category] ?? 'Altro';
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

// Nota: L'istanziazione di SettingsPage è ora gestita centralmente
// nel file bootstrap revious-microdata.php per mantenere la logica di inizializzazione centralizzata

