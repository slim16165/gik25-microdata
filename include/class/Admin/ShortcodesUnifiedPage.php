<?php
namespace gik25microdata\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Pagina unificata per gestione shortcode con tab
 * 
 * Tab:
 * - Gestione
 * - Utilizzo
 */
class ShortcodesUnifiedPage
{
    private const PAGE_SLUG = 'revious-microdata-shortcodes';
    private const CAPABILITY = 'manage_options';

    /**
     * Inizializza la pagina
     */
    public static function init(): void
    {
        // Le inizializzazioni delle pagine figlie sono gestite dalle rispettive classi
        // Qui non serve inizializzare nulla in più
    }

    /**
     * Renderizza la pagina con tab
     */
    public static function render_page(): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            wp_die(__('Non hai i permessi per accedere a questa pagina.', 'gik25-microdata'));
        }

        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'management';
        
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Shortcodes - Revious Microdata', 'gik25-microdata'); ?></h1>
            
            <nav class="nav-tab-wrapper" style="margin: 20px 0;">
                <a href="<?php echo esc_url(admin_url('admin.php?page=' . self::PAGE_SLUG . '&tab=management')); ?>" 
                   class="nav-tab <?php echo $active_tab === 'management' ? 'nav-tab-active' : ''; ?>">
                    🎨 Gestione
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=' . self::PAGE_SLUG . '&tab=usage')); ?>" 
                   class="nav-tab <?php echo $active_tab === 'usage' ? 'nav-tab-active' : ''; ?>">
                    📊 Utilizzo
                </a>
            </nav>

            <div class="tab-content" style="margin-top: 20px;">
                <?php
                switch ($active_tab) {
                    case 'usage':
                        self::render_usage_tab();
                        break;
                    case 'management':
                    default:
                        self::render_management_tab();
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render tab Gestione
     */
    private static function render_management_tab(): void
    {
        if (class_exists('\gik25microdata\Admin\ShortcodesManagerPage')) {
            \gik25microdata\Admin\ShortcodesManagerPage::renderPage();
        } else {
            echo '<div class="wrap"><h1>Errore</h1><p>Impossibile caricare la pagina gestione shortcode. Classe non trovata.</p></div>';
        }
    }

    /**
     * Render tab Utilizzo
     */
    private static function render_usage_tab(): void
    {
        if (class_exists('\gik25microdata\Admin\ShortcodesUsagePage')) {
            \gik25microdata\Admin\ShortcodesUsagePage::renderPage();
        } else {
            echo '<div class="wrap"><h1>Errore</h1><p>Impossibile caricare la pagina utilizzo shortcode. Classe non trovata.</p></div>';
        }
    }
}

