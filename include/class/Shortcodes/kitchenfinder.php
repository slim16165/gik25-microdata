<?php
namespace gik25microdata\Shortcodes;

if (!defined('ABSPATH')) {
    exit;
}

class KitchenFinder extends ShortcodeBase
{
    public function __construct()
    {
        $this->shortcode = 'kitchen_finder';
        parent::__construct();
        
        // Registra endpoint AJAX per il calcolo del risultato
        add_action('wp_ajax_kitchen_finder_calculate', [$this, 'ajax_calculate']);
        add_action('wp_ajax_nopriv_kitchen_finder_calculate', [$this, 'ajax_calculate']);
        
        // Registra endpoint AJAX per il download PDF
        add_action('wp_ajax_kitchen_finder_pdf', [$this, 'ajax_generate_pdf']);
        add_action('wp_ajax_nopriv_kitchen_finder_pdf', [$this, 'ajax_generate_pdf']);
    }

    public function ShortcodeHandler($atts, $content = null)
    {
        $atts = shortcode_atts([
            'title' => 'Trova la cucina perfetta per te',
            'show_progress' => 'true'
        ], $atts);

        // Genera nonce per sicurezza
        $nonce = wp_create_nonce('kitchen_finder_nonce');
        
        // URL per AJAX
        $ajax_url = admin_url('admin-ajax.php');
        
        ob_start();
        ?>
        <div id="kitchen-finder-widget" class="kitchen-finder-container" data-nonce="<?php echo esc_attr($nonce); ?>" data-ajax-url="<?php echo esc_url($ajax_url); ?>">
            <div class="kf-header">
                <h2 class="kf-title"><?php echo esc_html($atts['title']); ?></h2>
                <?php if ($atts['show_progress'] === 'true'): ?>
                    <div class="kf-progress" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="4">
                        <div class="kf-progress-bar">
                            <span class="kf-progress-text">Passo <span id="kf-current-step">1</span> di 4</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Step 1: Spazio -->
            <div class="kf-step" id="kf-step-1" data-step="1" role="tabpanel" aria-labelledby="kf-step-1-label">
                <h3 id="kf-step-1-label" class="kf-step-title">Qual è lo spazio disponibile?</h3>
                <div class="kf-input-group">
                    <label for="kf-space-a">Lato A (cm)</label>
                    <input type="number" id="kf-space-a" class="kf-input" min="0" max="1000" placeholder="Es: 200" aria-required="true">
                </div>
                <div class="kf-input-group">
                    <label for="kf-space-b">Lato B (cm)</label>
                    <input type="number" id="kf-space-b" class="kf-input" min="0" max="1000" placeholder="Es: 150" aria-required="true">
                </div>
                <div class="kf-toggle-group">
                    <label class="kf-toggle">
                        <input type="checkbox" id="kf-small-kitchen" aria-describedby="kf-small-kitchen-desc">
                        <span class="kf-toggle-label">Cucina piccola (meno di 240 cm totali)</span>
                    </label>
                    <span id="kf-small-kitchen-desc" class="kf-help-text">Seleziona se lo spazio totale è inferiore a 240 cm</span>
                </div>
                <button type="button" class="kf-btn kf-btn-primary" onclick="kitchenFinder.nextStep()" aria-label="Continua al passo 2">Avanti</button>
            </div>

            <!-- Step 2: Layout -->
            <div class="kf-step kf-hidden" id="kf-step-2" data-step="2" role="tabpanel" aria-labelledby="kf-step-2-label">
                <h3 id="kf-step-2-label" class="kf-step-title">Quale layout preferisci?</h3>
                <div class="kf-option-grid">
                    <button type="button" class="kf-option-card" data-value="lineare" onclick="kitchenFinder.selectOption('layout', 'lineare')" aria-pressed="false">
                        <span class="kf-option-icon">━</span>
                        <span class="kf-option-label">Lineare</span>
                    </button>
                    <button type="button" class="kf-option-card" data-value="angolare" onclick="kitchenFinder.selectOption('layout', 'angolare')" aria-pressed="false">
                        <span class="kf-option-icon">└</span>
                        <span class="kf-option-label">Angolare</span>
                    </button>
                    <button type="button" class="kf-option-card" data-value="u" onclick="kitchenFinder.selectOption('layout', 'u')" aria-pressed="false">
                        <span class="kf-option-icon">⊔</span>
                        <span class="kf-option-label">A U</span>
                    </button>
                    <button type="button" class="kf-option-card" data-value="isola" onclick="kitchenFinder.selectOption('layout', 'isola')" aria-pressed="false">
                        <span class="kf-option-icon">▣</span>
                        <span class="kf-option-label">Isola</span>
                    </button>
                </div>
                <div class="kf-nav-buttons">
                    <button type="button" class="kf-btn kf-btn-secondary" onclick="kitchenFinder.prevStep()" aria-label="Torna al passo 1">Indietro</button>
                    <button type="button" class="kf-btn kf-btn-primary" onclick="kitchenFinder.nextStep()" aria-label="Continua al passo 3">Avanti</button>
                </div>
            </div>

            <!-- Step 3: Stile -->
            <div class="kf-step kf-hidden" id="kf-step-3" data-step="3" role="tabpanel" aria-labelledby="kf-step-3-label">
                <h3 id="kf-step-3-label" class="kf-step-title">Quale stile ti piace di più?</h3>
                <div class="kf-option-grid">
                    <button type="button" class="kf-option-card" data-value="moderno" onclick="kitchenFinder.selectOption('stile', 'moderno')" aria-pressed="false">
                        <span class="kf-option-label">Moderno</span>
                    </button>
                    <button type="button" class="kf-option-card" data-value="classico" onclick="kitchenFinder.selectOption('stile', 'classico')" aria-pressed="false">
                        <span class="kf-option-label">Classico</span>
                    </button>
                    <button type="button" class="kf-option-card" data-value="minimal" onclick="kitchenFinder.selectOption('stile', 'minimal')" aria-pressed="false">
                        <span class="kf-option-label">Minimal</span>
                    </button>
                    <button type="button" class="kf-option-card" data-value="industriale" onclick="kitchenFinder.selectOption('stile', 'industriale')" aria-pressed="false">
                        <span class="kf-option-label">Industriale</span>
                    </button>
                </div>
                <div class="kf-nav-buttons">
                    <button type="button" class="kf-btn kf-btn-secondary" onclick="kitchenFinder.prevStep()" aria-label="Torna al passo 2">Indietro</button>
                    <button type="button" class="kf-btn kf-btn-primary" onclick="kitchenFinder.nextStep()" aria-label="Continua al passo 4">Avanti</button>
                </div>
            </div>

            <!-- Step 4: Budget -->
            <div class="kf-step kf-hidden" id="kf-step-4" data-step="4" role="tabpanel" aria-labelledby="kf-step-4-label">
                <h3 id="kf-step-4-label" class="kf-step-title">Quale fascia di budget?</h3>
                <div class="kf-budget-options">
                    <button type="button" class="kf-budget-card" data-value="eco" onclick="kitchenFinder.selectOption('budget', 'eco')" aria-pressed="false">
                        <span class="kf-budget-label">Eco</span>
                        <span class="kf-budget-desc">Sistemi economici, essenziali</span>
                        <span class="kf-budget-price">Da €500</span>
                    </button>
                    <button type="button" class="kf-budget-card" data-value="medio" onclick="kitchenFinder.selectOption('budget', 'medio')" aria-pressed="false">
                        <span class="kf-budget-label">Medio</span>
                        <span class="kf-budget-desc">Buon rapporto qualità/prezzo</span>
                        <span class="kf-budget-price">€1.000 - €3.000</span>
                    </button>
                    <button type="button" class="kf-budget-card" data-value="premium" onclick="kitchenFinder.selectOption('budget', 'premium')" aria-pressed="false">
                        <span class="kf-budget-label">Premium</span>
                        <span class="kf-budget-desc">Alta qualità, design raffinato</span>
                        <span class="kf-budget-price">Da €3.000</span>
                    </button>
                </div>
                <div class="kf-nav-buttons">
                    <button type="button" class="kf-btn kf-btn-secondary" onclick="kitchenFinder.prevStep()" aria-label="Torna al passo 3">Indietro</button>
                    <button type="button" class="kf-btn kf-btn-primary" onclick="kitchenFinder.calculateResult()" aria-label="Vedi il risultato">Vedi risultato</button>
                </div>
            </div>

            <!-- Risultato -->
            <div class="kf-step kf-hidden" id="kf-result" data-step="result" role="tabpanel" aria-labelledby="kf-result-label">
                <div id="kf-result-content"></div>
                <!-- Schema JSON-LD verrà inserito dinamicamente -->
            </div>

            <!-- Loader -->
            <div class="kf-loader kf-hidden" id="kf-loader" role="status" aria-live="polite">
                <div class="kf-spinner"></div>
                <p>Sto calcolando il sistema perfetto per te...</p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function styles()
    {
        wp_register_style(
            'kitchen-finder-css',
            plugins_url('gik25-microdata/assets/css/kitchen-finder.css'),
            [],
            '1.0.0',
            'all'
        );
        wp_enqueue_style('kitchen-finder-css');
    }

    public function scripts()
    {
        wp_register_script(
            'kitchen-finder-js',
            plugins_url('gik25-microdata/assets/js/kitchen-finder.js'),
            [],
            '1.0.0',
            true
        );
        wp_enqueue_script('kitchen-finder-js');
        
        // Passa dati al JavaScript
        wp_localize_script('kitchen-finder-js', 'kitchenFinderData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('kitchen_finder_nonce'),
        ]);
    }

    public function admin_scripts()
    {
        // TODO: Implement admin_scripts() method.
    }

    public function register_plugin($plugin_array)
    {
        // TODO: Implement register_plugin() method.
        return $plugin_array;
    }

    public function register_button($buttons)
    {
        // TODO: Implement register_button() method.
        return $buttons;
    }

    /**
     * Gestisce la richiesta AJAX per calcolare il risultato
     */
    public function ajax_calculate()
    {
        check_ajax_referer('kitchen_finder_nonce', 'nonce');
        
        $space_a = isset($_POST['space_a']) ? intval($_POST['space_a']) : 0;
        $space_b = isset($_POST['space_b']) ? intval($_POST['space_b']) : 0;
        $small_kitchen = isset($_POST['small_kitchen']) && $_POST['small_kitchen'] === 'true';
        $layout = isset($_POST['layout']) ? sanitize_text_field($_POST['layout']) : '';
        $stile = isset($_POST['stile']) ? sanitize_text_field($_POST['stile']) : '';
        $budget = isset($_POST['budget']) ? sanitize_text_field($_POST['budget']) : '';
        
        // Calcola il risultato
        $result = $this->calculateKitchenSystem($space_a, $space_b, $small_kitchen, $layout, $stile, $budget);
        
        // Tracking event (anonimo)
        $this->trackEvent('kitchen_finder_complete', [
            'space_a' => $space_a,
            'space_b' => $space_b,
            'small_kitchen' => $small_kitchen,
            'layout' => $layout,
            'stile' => $stile,
            'budget' => $budget,
            'primary_system' => $result['primary_system'],
        ]);
        
        wp_send_json_success($result);
    }

    /**
     * Logica di decisione per raccomandare il sistema cucina
     */
    private function calculateKitchenSystem($space_a, $space_b, $small_kitchen, $layout, $stile, $budget): array
    {
        $total_space = $space_a + $space_b;
        $primary_system = '';
        $alternative_system = '';
        $price_range = '';
        $checklist = [];
        $internal_links = [];
        
        // Regole base per la selezione
        if ($small_kitchen || $total_space < 240) {
            // Cucine piccole → SUNNERSTA o KNOXHULT
            if ($budget === 'eco') {
                $primary_system = 'SUNNERSTA';
                $alternative_system = 'KNOXHULT';
                $price_range = '€500 - €1.500';
            } else {
                $primary_system = 'KNOXHULT';
                $alternative_system = 'SUNNERSTA';
                $price_range = '€800 - €2.000';
            }
        } elseif ($layout === 'angolare' && $budget === 'medio') {
            $primary_system = 'ENHET';
            $alternative_system = 'METOD';
            $price_range = '€1.500 - €3.500';
        } elseif (($layout === 'u' || $layout === 'isola') && $budget === 'premium') {
            $primary_system = 'METOD';
            $alternative_system = 'ENHET';
            $price_range = '€3.000 - €8.000+';
        } elseif ($stile === 'industriale') {
            $primary_system = 'METOD';
            $alternative_system = 'ENHET';
            $price_range = $budget === 'premium' ? '€3.000 - €8.000+' : '€1.500 - €3.500';
        } elseif ($budget === 'eco') {
            $primary_system = 'SUNNERSTA';
            $alternative_system = 'KNOXHULT';
            $price_range = '€500 - €1.500';
        } else {
            // Default: ENHET per buon rapporto qualità/prezzo
            $primary_system = 'ENHET';
            $alternative_system = 'METOD';
            $price_range = '€1.500 - €3.500';
        }
        
        // Genera checklist misure
        $checklist = $this->generateChecklist($layout, $total_space);
        
        // Genera link interni correlati
        $internal_links = $this->getInternalLinks($layout, $small_kitchen, $budget);
        
        // Genera FAQ dinamiche
        $faqs = $this->generateFAQs($primary_system, $layout);
        
        return [
            'primary_system' => $primary_system,
            'alternative_system' => $alternative_system,
            'price_range' => $price_range,
            'checklist' => $checklist,
            'internal_links' => $internal_links,
            'faqs' => $faqs,
            'total_space' => $total_space,
            'layout' => $layout,
            'stile' => $stile,
            'budget' => $budget,
        ];
    }

    /**
     * Genera checklist misure in base al layout
     */
    private function generateChecklist($layout, $total_space): array
    {
        $checklist = [];
        
        // Misure base comuni
        $checklist[] = 'Profondità moduli: minimo 60 cm (65 cm consigliato)';
        $checklist[] = 'Altezza pensili: standard 60 cm, oppure personalizzati';
        $checklist[] = 'Passaggio libero: minimo 90 cm tra cucina e parete opposta';
        
        // Aggiunge misure specifiche per layout
        if ($layout === 'angolare') {
            $checklist[] = 'Verifica spazio angolo: minimo 90x90 cm per moduli angolari';
            $checklist[] = 'Lato corto: almeno 120 cm per l\'installazione';
        } elseif ($layout === 'u') {
            $checklist[] = 'Larghezza totale: minimo 240 cm per configurazione a U confortevole';
            $checklist[] = 'Area centrale: almeno 120x120 cm per movimento agevole';
        } elseif ($layout === 'isola') {
            $checklist[] = 'Spazio per isola: minimo 90x90 cm per l\'isola stessa';
            $checklist[] = 'Passaggi laterali: minimo 90 cm su entrambi i lati dell\'isola';
        }
        
        if ($total_space < 240) {
            $checklist[] = '⚠️ Spazio limitato: considera moduli a profondità ridotta (45 cm)';
        }
        
        return $checklist;
    }

    /**
     * Restituisce link interni correlati
     */
    private function getInternalLinks($layout, $small_kitchen, $budget): array
    {
        $links = [];
        
        // URL IKEA disponibili (basati sui tuoi articoli reali)
        $ikea_urls = [
            'base' => 'cucine-ikea/',
            'complementi' => [
                'soggiorni-ikea/',        // Zona giorno
                'tavolo-da-pranzo-ikea/', // Complemento cucina
                'credenza-ikea/',         // Arredamento
                'mobili-ingresso-ikea/',  // Vicino cucina
                'ikea-online/',           // Info generale
                'divani-ikea/',           // Spazi connessi
            ]
        ];
        
        // Sempre aggiungi articolo base cucine
        $links[] = [
            'url' => 'https://www.totaldesign.it/cucine-ikea/',
            'title' => 'Cucine IKEA: guida completa',
            'description' => 'Tutto sui sistemi cucina IKEA METOD, ENHET, SUNNERSTA'
        ];
        
        // Aggiungi 2 complementi casuali
        $complementi = $ikea_urls['complementi'];
        shuffle($complementi); // Mescola per varietà
        
        foreach (array_slice($complementi, 0, 2) as $slug) {
            $post_id = url_to_postid("https://www.totaldesign.it/{$slug}");
            
            if ($post_id > 0) {
                $post = get_post($post_id);
                if ($post && $post->post_status === 'publish') {
                    $links[] = [
                        'url' => get_permalink($post),
                        'title' => get_the_title($post),
                        'description' => wp_trim_words(get_the_excerpt($post) ?: 'Idee arredamento IKEA', 12)
                    ];
                }
            }
        }
        
        return array_slice($links, 0, 3); // Max 3 link
    }

    /**
     * Genera FAQ dinamiche basate sul risultato
     */
    private function generateFAQs($primary_system, $layout): array
    {
        $faqs = [];
        
        $faqs[] = [
            'question' => "Quanto costa un sistema {$primary_system}?",
            'answer' => "Il prezzo di un sistema {$primary_system} varia in base alle dimensioni e alle finiture scelte. In genere parte da €800-€1.500 per cucine piccole e può arrivare a €5.000+ per configurazioni complete con elettrodomestici."
        ];
        
        if ($layout === 'angolare') {
            $faqs[] = [
                'question' => 'Come si installa una cucina angolare IKEA?',
                'answer' => "Le cucine angolari IKEA richiedono spazio minimo di 90x90 cm nell'angolo. I moduli angolari si collegano perfettamente con i moduli lineari. Assicurati di avere almeno 120 cm su entrambi i lati."
            ];
        }
        
        $faqs[] = [
            'question' => 'I mobili IKEA sono facili da montare?',
            'answer' => "I sistemi IKEA come {$primary_system} sono progettati per il fai-da-te con istruzioni chiare. Tuttavia, per installazioni complesse o se preferisci assistenza professionale, puoi richiedere il servizio di montaggio IKEA (a pagamento)."
        ];
        
        return $faqs;
    }

    /**
     * Gestisce la generazione del PDF
     */
    public function ajax_generate_pdf()
    {
        check_ajax_referer('kitchen_finder_nonce', 'nonce');
        
        // Per ora restituiamo un placeholder
        // In futuro si potrà integrare una libreria PDF (es. TCPDF, mPDF)
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $result_data = isset($_POST['result_data']) ? $_POST['result_data'] : [];
        
        if (!is_email($email)) {
            wp_send_json_error(['message' => 'Email non valida']);
            return;
        }
        
        // Salva lead (potresti integrarlo con un CRM o email marketing)
        $this->saveLead($email, $result_data);
        
        // Tracking
        $this->trackEvent('kitchen_finder_lead_submitted', [
            'email' => $email, // Non tracciare l'email nel tracking, solo confermare l'invio
        ]);
        
        wp_send_json_success([
            'message' => 'PDF inviato con successo! Controlla la tua email.',
            'redirect' => false
        ]);
    }

    /**
     * Salva il lead (placeholder - da integrare con sistema email/CRM)
     */
    private function saveLead($email, $result_data)
    {
        // Puoi integrare con:
        // - WordPress options API per storage temporaneo
        // - Custom post type per i lead
        // - Integrazione con MailChimp/SendGrid/etc
        // - Database esterno
        
        // Esempio: salva in transient (24 ore)
        $leads = get_transient('kitchen_finder_leads') ?: [];
        $leads[] = [
            'email' => $email,
            'data' => $result_data,
            'timestamp' => current_time('mysql'),
        ];
        set_transient('kitchen_finder_leads', $leads, DAY_IN_SECONDS);
    }

    /**
     * Tracking eventi (anonimo)
     */
    private function trackEvent($event_name, $data = [])
    {
        // Integra con Google Analytics, Facebook Pixel, o altro
        // Esempio base: log in debug
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Kitchen Finder Event: {$event_name} - " . json_encode($data));
        }
    }
}

$kitchen_finder = new KitchenFinder();

