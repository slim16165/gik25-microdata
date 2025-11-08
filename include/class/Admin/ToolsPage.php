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
        add_action('admin_post_gik25_rebuild_usage_summary', [self::class, 'handleRebuildUsage']);
    }

    public static function renderPage(): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            wp_die(__('Non hai i permessi per visualizzare questa pagina.', 'gik25-microdata'));
        }

        $usage = ShortcodeRegistry::getUsageSummary();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Strumenti Revious Microdata', 'gik25-microdata'); ?></h1>
            <p><?php esc_html_e('Esporta le impostazioni correnti oppure rigenera il riepilogo di utilizzo degli shortcode.', 'gik25-microdata'); ?></p>

            <h2><?php esc_html_e('Esporta configurazione shortcode', 'gik25-microdata'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('gik25_export_shortcodes'); ?>
                <input type="hidden" name="action" value="gik25_export_shortcodes">
                <p><?php esc_html_e('Scarica un file JSON con lo stato (abilitato/disabilitato) di ogni shortcode.', 'gik25-microdata'); ?></p>
                <button type="submit" class="button button-primary"><?php esc_html_e('Esporta JSON', 'gik25-microdata'); ?></button>
            </form>

            <hr>

            <h2><?php esc_html_e('Riepilogo utilizzo shortcode', 'gik25-microdata'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-bottom:20px;">
                <?php wp_nonce_field('gik25_rebuild_usage_summary'); ?>
                <input type="hidden" name="action" value="gik25_rebuild_usage_summary">
                <p><?php esc_html_e('Esegue una scansione veloce di tutti i contenuti per calcolare quante volte appare ogni shortcode (max 500 post per tag).', 'gik25-microdata'); ?></p>
                <button type="submit" class="button"><?php esc_html_e('Rigenera riepilogo', 'gik25-microdata'); ?></button>
            </form>

            <?php if (!empty($usage['data'])) : ?>
                <p class="description">
                    <?php esc_html_e('Ultima scansione:', 'gik25-microdata'); ?>
                    <?php echo esc_html($usage['updated_at'] ? get_date_from_gmt($usage['updated_at'], 'Y-m-d H:i') : 'n/a'); ?>
                </p>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Shortcode', 'gik25-microdata'); ?></th>
                            <th><?php esc_html_e('Post trovati', 'gik25-microdata'); ?></th>
                            <th><?php esc_html_e('Occorrenze totali', 'gik25-microdata'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usage['data'] as $slug => $row) : ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($row['label'] ?? $slug); ?></strong><br>
                                    <code><?php echo esc_html($slug); ?></code>
                                </td>
                                <td><?php echo esc_html($row['posts']); ?></td>
                                <td><?php echo esc_html($row['occurrences']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php esc_html_e('Nessun riepilogo disponibile. Premi “Rigenera” per creare il primo report.', 'gik25-microdata'); ?></p>
            <?php endif; ?>
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

    public static function handleRebuildUsage(): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            wp_die(__('Operazione non consentita.', 'gik25-microdata'));
        }
        check_admin_referer('gik25_rebuild_usage_summary');

        ShortcodeRegistry::scanUsageSummary();

        wp_safe_redirect(add_query_arg([
            'page' => AdminMenu::MENU_SLUG . '-tools',
            'updated' => 'usage',
        ], admin_url('admin.php')));
        exit;
    }
}
