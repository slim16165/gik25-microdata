<?php
namespace gik25microdata\Admin;

use gik25microdata\Shortcodes\ShortcodeRegistry;

if (!defined('ABSPATH')) {
    exit;
}

class ShortcodesUsagePage
{
    private const CAPABILITY = 'manage_options';
    private const MAX_RESULTS = 200;

    public static function renderPage(): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            wp_die(__('Non hai i permessi per visualizzare questa pagina.', 'gik25-microdata'));
        }

        $selected = isset($_GET['shortcode']) ? sanitize_text_field(wp_unslash($_GET['shortcode'])) : '';
        $results = [];
        $error = '';

        if ($selected !== '') {
            $results = self::queryUsage($selected);
            if ($results === null) {
                $error = __('Shortcode non riconosciuto.', 'gik25-microdata');
                $results = [];
            }
        }

        $options = ShortcodeRegistry::getOptionsForSelect();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Utilizzo Shortcode', 'gik25-microdata'); ?></h1>
            <p><?php esc_html_e('Seleziona uno shortcode per ottenere la lista dei contenuti che lo utilizzano.', 'gik25-microdata'); ?></p>
            <form method="get">
                <input type="hidden" name="page" value="<?php echo esc_attr(AdminMenu::MENU_SLUG . '-shortcodes-usage'); ?>">
                <select name="shortcode">
                    <option value=""><?php esc_html_e('— Seleziona shortcode —', 'gik25-microdata'); ?></option>
                    <?php foreach ($options as $slug => $label) : ?>
                        <option value="<?php echo esc_attr($slug); ?>" <?php selected($selected, $slug); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="button button-primary"><?php esc_html_e('Cerca', 'gik25-microdata'); ?></button>
            </form>

            <?php if ($error) : ?>
                <div class="notice notice-error"><p><?php echo esc_html($error); ?></p></div>
            <?php endif; ?>

            <?php if ($selected && !$error) : ?>
                <h2 style="margin-top: 30px;">
                    <?php printf(esc_html__('Risultati per [%s]', 'gik25-microdata'), esc_html($selected)); ?>
                </h2>
                <?php if (empty($results)) : ?>
                    <p><?php esc_html_e('Nessun contenuto trovato.', 'gik25-microdata'); ?></p>
                <?php else : ?>
                    <table class="widefat striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('ID', 'gik25-microdata'); ?></th>
                                <th><?php esc_html_e('Titolo', 'gik25-microdata'); ?></th>
                                <th><?php esc_html_e('Tipo', 'gik25-microdata'); ?></th>
                                <th><?php esc_html_e('Status', 'gik25-microdata'); ?></th>
                                <th><?php esc_html_e('Occorrenze', 'gik25-microdata'); ?></th>
                                <th><?php esc_html_e('Ultima modifica', 'gik25-microdata'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $row) : ?>
                                <tr>
                                    <td><a href="<?php echo esc_url(get_edit_post_link($row['ID'], '')); ?>">#<?php echo esc_html($row['ID']); ?></a></td>
                                    <td><?php echo esc_html($row['post_title']); ?></td>
                                    <td><?php echo esc_html($row['post_type']); ?></td>
                                    <td><?php echo esc_html($row['post_status']); ?></td>
                                    <td><?php echo esc_html($row['count']); ?></td>
                                    <td><?php echo esc_html(get_date_from_gmt($row['post_modified_gmt'], 'Y-m-d H:i')); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p class="description">
                        <?php esc_html_e('Nota: vengono mostrati al massimo 200 risultati. Utilizza i filtri di WordPress se ti servono ricerche più specifiche.', 'gik25-microdata'); ?>
                    </p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Execute the LIKE query and return results or null if slug invalid.
     *
     * @return array<int,array<string,mixed>>|null
     */
    private static function queryUsage(string $slug): ?array
    {
        $canonical = ShortcodeRegistry::resolveSlugFromTag($slug);
        if (!$canonical) {
            return null;
        }

        global $wpdb;
        $like = '%[' . $wpdb->esc_like($slug) . '%';
        $sql = $wpdb->prepare(
            "SELECT ID, post_title, post_type, post_status, post_modified_gmt, post_content
             FROM {$wpdb->posts}
             WHERE post_status NOT IN ('trash','auto-draft','inherit')
                AND post_content LIKE %s
             ORDER BY post_modified_gmt DESC
             LIMIT %d",
            $like,
            self::MAX_RESULTS
        );
        $rows = $wpdb->get_results($sql, ARRAY_A);
        if (!$rows) {
            return [];
        }

        foreach ($rows as &$row) {
            $row['count'] = ShortcodeRegistry::countOccurrences($slug, $row['post_content']);
            unset($row['post_content']);
        }

        return $rows;
    }
}
