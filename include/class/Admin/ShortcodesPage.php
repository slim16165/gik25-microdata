<?php
namespace gik25microdata\Admin;

use gik25microdata\Shortcodes\ShortcodeRegistry;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin page that lists every shortcode with enable/disable toggles.
 */
class ShortcodesPage
{
    private const CAPABILITY = 'manage_options';

    public static function init(): void
    {
        add_action('admin_post_gik25_toggle_shortcode', [self::class, 'handleToggle']);
    }

    /**
     * Render the admin page.
     */
    public static function renderPage(): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            wp_die(__('Non hai i permessi per visualizzare questa pagina.', 'gik25-microdata'));
        }

        $items = ShortcodeRegistry::getItemsForAdmin();
        $action_url = admin_url('admin-post.php');
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Shortcode Revious Microdata', 'gik25-microdata'); ?></h1>

            <?php if (isset($_GET['updated'])) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e('Impostazioni salvate.', 'gik25-microdata'); ?></p>
                </div>
            <?php endif; ?>

            <p><?php esc_html_e('Qui puoi abilitare o disabilitare i singoli shortcode ed avere una descrizione rapida del loro utilizzo.', 'gik25-microdata'); ?></p>

            <table class="widefat striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Shortcode', 'gik25-microdata'); ?></th>
                        <th><?php esc_html_e('Descrizione', 'gik25-microdata'); ?></th>
                        <th><?php esc_html_e('Esempio', 'gik25-microdata'); ?></th>
                        <th><?php esc_html_e('Stato', 'gik25-microdata'); ?></th>
                        <th><?php esc_html_e('Azioni', 'gik25-microdata'); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $slug => $item) : ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($item['label'] ?? $slug); ?></strong><br>
                            <code><?php echo esc_html($slug); ?></code>
                            <?php
                            if (!empty($item['aliases'])) {
                                printf('<div class="description">%s</div>', esc_html(sprintf(__('Alias: %s', 'gik25-microdata'), implode(', ', $item['aliases']))));
                            }
                            ?>
                        </td>
                        <td><?php echo esc_html($item['description'] ?? ''); ?></td>
                        <td><code><?php echo esc_html($item['example'] ?? ''); ?></code></td>
                        <td>
                            <?php if (!empty($item['enabled'])) : ?>
                                <span class="dashicons dashicons-yes" style="color:#008a20;"></span>
                                <?php esc_html_e('Abilitato', 'gik25-microdata'); ?>
                            <?php else : ?>
                                <span class="dashicons dashicons-no" style="color:#b32d2e;"></span>
                                <?php esc_html_e('Disabilitato', 'gik25-microdata'); ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="post" action="<?php echo esc_url($action_url); ?>">
                                <?php wp_nonce_field('gik25_toggle_shortcode'); ?>
                                <input type="hidden" name="action" value="gik25_toggle_shortcode">
                                <input type="hidden" name="slug" value="<?php echo esc_attr($slug); ?>">
                                <input type="hidden" name="enable" value="<?php echo $item['enabled'] ? '0' : '1'; ?>">
                                <button type="submit" class="button button-secondary">
                                    <?php echo $item['enabled']
                                        ? esc_html__('Disabilita', 'gik25-microdata')
                                        : esc_html__('Abilita', 'gik25-microdata'); ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Handle enable/disable request.
     */
    public static function handleToggle(): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            wp_die(__('Operazione non consentita.', 'gik25-microdata'));
        }

        check_admin_referer('gik25_toggle_shortcode');

        $slug = isset($_POST['slug']) ? sanitize_text_field(wp_unslash($_POST['slug'])) : '';
        $enable = isset($_POST['enable']) ? (bool) intval($_POST['enable']) : false;

        if ($slug) {
            ShortcodeRegistry::setSlugEnabled($slug, $enable);
        }

        wp_safe_redirect(add_query_arg([
            'page' => AdminMenu::MENU_SLUG . '-shortcodes',
            'updated' => $slug,
        ], admin_url('admin.php')));
        exit;
    }
}
