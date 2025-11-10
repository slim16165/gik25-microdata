<?php
namespace gik25microdata\Admin;

use gik25microdata\ListOfPosts\Cache\LinkCache;
use gik25microdata\ListOfPosts\Validation\UrlValidator;
use gik25microdata\ListOfPosts\Organization\LinkTagManager;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Dashboard admin con statistiche e analytics
 */
class LinksDashboard
{
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'addMenuPage']);
    }
    
    public static function addMenuPage(): void
    {
        add_submenu_page(
            'revious-microdata',
            __('Dashboard Link', 'revious-microdata'),
            __('Dashboard Link', 'revious-microdata'),
            'manage_options',
            'gik25-links-dashboard',
            [self::class, 'renderPage']
        );
    }
    
    public static function renderPage(): void
    {
        $cache_stats = LinkCache::getStats();
        $all_tags = LinkTagManager::getAllTags();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="gik25-dashboard-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
                
                <!-- Statistiche Cache -->
                <div class="postbox">
                    <h2 class="hndle"><?php _e('Statistiche Cache', 'revious-microdata'); ?></h2>
                    <div class="inside">
                        <ul>
                            <li><strong><?php _e('Elementi in cache:', 'revious-microdata'); ?></strong> <?php echo esc_html($cache_stats['total_cached_items']); ?></li>
                            <li><strong><?php _e('Prefisso cache:', 'revious-microdata'); ?></strong> <code><?php echo esc_html($cache_stats['cache_prefix']); ?></code></li>
                            <li><strong><?php _e('Scadenza default:', 'revious-microdata'); ?></strong> <?php echo esc_html($cache_stats['default_expiration']); ?>s</li>
                        </ul>
                        <p>
                            <button type="button" class="button" onclick="if(confirm('Sicuro di voler invalidare tutta la cache?')) { window.location.href='?page=gik25-links-dashboard&action=clear_cache&_wpnonce=<?php echo wp_create_nonce('clear_cache'); ?>'; }">
                                <?php _e('Pulisci Cache', 'revious-microdata'); ?>
                            </button>
                        </p>
                    </div>
                </div>
                
                <!-- Tag Disponibili -->
                <div class="postbox">
                    <h2 class="hndle"><?php _e('Tag Disponibili', 'revious-microdata'); ?></h2>
                    <div class="inside">
                        <?php if (!empty($all_tags)): ?>
                            <ul>
                                <?php foreach ($all_tags as $tag): ?>
                                    <li><code><?php echo esc_html($tag); ?></code></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p><?php _e('Nessun tag disponibile', 'revious-microdata'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Validazione URL -->
                <div class="postbox">
                    <h2 class="hndle"><?php _e('Validazione URL', 'revious-microdata'); ?></h2>
                    <div class="inside">
                        <form method="post" action="">
                            <?php wp_nonce_field('validate_url', 'validate_nonce'); ?>
                            <p>
                                <label for="url_to_validate"><?php _e('URL da validare:', 'revious-microdata'); ?></label>
                                <input type="url" id="url_to_validate" name="url_to_validate" class="regular-text" value="<?php echo esc_attr($_POST['url_to_validate'] ?? ''); ?>">
                            </p>
                            <p>
                                <button type="submit" class="button button-primary"><?php _e('Valida', 'revious-microdata'); ?></button>
                            </p>
                        </form>
                        
                        <?php
                        if (isset($_POST['url_to_validate']) && wp_verify_nonce($_POST['validate_nonce'] ?? '', 'validate_url')) {
                            $url = esc_url_raw($_POST['url_to_validate']);
                            $validation = UrlValidator::validate($url);
                            ?>
                            <div class="notice notice-<?php echo $validation['valid'] ? 'success' : 'error'; ?>">
                                <p><strong><?php _e('Risultato validazione:', 'revious-microdata'); ?></strong></p>
                                <ul>
                                    <?php foreach ($validation['errors'] as $error): ?>
                                        <li style="color: red;"><?php echo esc_html($error); ?></li>
                                    <?php endforeach; ?>
                                    <?php foreach ($validation['warnings'] as $warning): ?>
                                        <li style="color: orange;"><?php echo esc_html($warning); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                
            </div>
        </div>
        
        <style>
        .gik25-dashboard-grid .postbox {
            min-width: 0;
        }
        </style>
        <?php
        
        // Gestione azioni
        if (isset($_GET['action']) && $_GET['action'] === 'clear_cache' && wp_verify_nonce($_GET['_wpnonce'] ?? '', 'clear_cache')) {
            $cleared = LinkCache::invalidateAll();
            echo '<div class="notice notice-success"><p>' . sprintf(__('%d elementi rimossi dalla cache', 'revious-microdata'), $cleared) . '</p></div>';
        }
    }
}
