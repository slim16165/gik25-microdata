<?php
namespace gik25microdata\Admin;

use gik25microdata\Shortcodes\ShortcodeRegistry;

if (!defined('ABSPATH')) {
    exit;
}

class ToolsPage
{
    private const CAPABILITY = 'manage_options';

    public static function init(): void
    {
        add_action('admin_post_gik25_export_shortcodes', [self::class, 'handleExport']);
    }

    public static function renderPage(): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            wp_die(__('Non hai i permessi per visualizzare questa pagina.', 'gik25-microdata'));
        }

        // Verifica se siamo in una pagina con tab (Dashboard unificata)
        $is_tabbed_page = isset($_GET['page']) && $_GET['page'] === 'revious-microdata' && isset($_GET['tab']) && $_GET['tab'] === 'tools';
        ?>
        <div class="wrap">
            <?php if (!$is_tabbed_page): ?>
            <h1><?php esc_html_e('Strumenti Revious Microdata', 'gik25-microdata'); ?></h1>
            <?php endif; ?>
            <p><?php esc_html_e('Esporta le impostazioni correnti degli shortcode per migrare o fare backup.', 'gik25-microdata'); ?></p>

            <h2><?php esc_html_e('Esporta configurazione shortcode', 'gik25-microdata'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('gik25_export_shortcodes'); ?>
                <input type="hidden" name="action" value="gik25_export_shortcodes">
                <p><?php esc_html_e('Scarica un file JSON con lo stato (abilitato/disabilitato) di ogni shortcode.', 'gik25-microdata'); ?></p>
                <button type="submit" class="button button-primary"><?php esc_html_e('Esporta JSON', 'gik25-microdata'); ?></button>
            </form>

            <hr>

            <h2><?php esc_html_e('Prossimi strumenti (roadmap)', 'gik25-microdata'); ?></h2>
            <p class="description">
                <?php esc_html_e('Qui in futuro puoi aggiungere import, reset e altri tool operativi. Al momento è presente solo l’esportazione JSON.', 'gik25-microdata'); ?>
            </p>
        </div>
        <?php
    }

    public static function handleExport(): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            wp_die(__('Operazione non consentita.', 'gik25-microdata'));
        }
        check_admin_referer('gik25_export_shortcodes');

        $payload = [
            'generated_at' => current_time('mysql'),
            'shortcodes' => ShortcodeRegistry::getItemsForAdmin(),
        ];

        nocache_headers();
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="gik25-shortcodes-' . gmdate('Ymd-His') . '.json"');
        echo wp_json_encode($payload, JSON_PRETTY_PRINT);
        exit;
    }
}
