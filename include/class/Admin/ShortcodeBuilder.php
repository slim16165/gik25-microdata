<?php
namespace gik25microdata\Admin;

use gik25microdata\ListOfPosts\LinkBuilder;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcode builder avanzato con preview
 */
class ShortcodeBuilder
{
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'addMenuPage']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueueScripts']);
    }
    
    public static function addMenuPage(): void
    {
        add_submenu_page(
            'revious-microdata',
            __('Shortcode Builder', 'revious-microdata'),
            __('Shortcode Builder', 'revious-microdata'),
            'manage_options',
            'gik25-shortcode-builder',
            [self::class, 'renderPage']
        );
    }
    
    public static function enqueueScripts(string $hook): void
    {
        if ($hook !== 'revious-microdata_page_gik25-shortcode-builder') {
            return;
        }
        
        wp_enqueue_script('jquery');
        wp_add_inline_script('jquery', '
            jQuery(document).ready(function($) {
                function updatePreview() {
                    var links = [];
                    $("#link-list textarea").val().split("\\n").forEach(function(line) {
                        var parts = line.split("|");
                        if (parts.length >= 2) {
                            links.push({
                                target_url: parts[0].trim(),
                                nome: parts[1].trim(),
                                commento: parts[2] ? parts[2].trim() : ""
                            });
                        }
                    });
                    
                    var style = $("#style-select").val();
                    var columns = parseInt($("#columns-input").val()) || 1;
                    var withImage = $("#with-image").is(":checked");
                    
                    $.ajax({
                        url: ajaxurl,
                        type: "POST",
                        data: {
                            action: "gik25_preview_links",
                            links: links,
                            style: style,
                            columns: columns,
                            with_image: withImage,
                            nonce: "' . wp_create_nonce('gik25_preview') . '"
                        },
                        success: function(response) {
                            if (response.success) {
                                $("#preview-container").html(response.data.html);
                            }
                        }
                    });
                }
                
                $("#link-list textarea, #style-select, #columns-input, #with-image").on("change input", updatePreview);
                updatePreview();
            });
        ');
    }
    
    public static function renderPage(): void
    {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                <div>
                    <h2><?php _e('Configurazione', 'revious-microdata'); ?></h2>
                    
                    <form id="shortcode-builder-form">
                        <table class="form-table">
                            <tr>
                                <th><label for="style-select"><?php _e('Stile', 'revious-microdata'); ?></label></th>
                                <td>
                                    <select id="style-select" name="style" class="regular-text">
                                        <option value="standard"><?php _e('Standard', 'revious-microdata'); ?></option>
                                        <option value="carousel"><?php _e('Carousel', 'revious-microdata'); ?></option>
                                        <option value="simple"><?php _e('Semplice', 'revious-microdata'); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="columns-input"><?php _e('Colonne', 'revious-microdata'); ?></label></th>
                                <td>
                                    <input type="number" id="columns-input" name="columns" min="1" max="4" value="1" class="small-text">
                                </td>
                            </tr>
                            <tr>
                                <th><label for="with-image"><?php _e('Con Immagini', 'revious-microdata'); ?></label></th>
                                <td>
                                    <input type="checkbox" id="with-image" name="with_image" checked>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="link-list"><?php _e('Link', 'revious-microdata'); ?></label></th>
                                <td>
                                    <textarea id="link-list" name="links" rows="10" class="large-text" placeholder="https://example.com/page1|Titolo Pagina 1|Commento opzionale
https://example.com/page2|Titolo Pagina 2"></textarea>
                                    <p class="description"><?php _e('Un link per riga. Formato: URL|Titolo|Commento', 'revious-microdata'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </form>
                </div>
                
                <div>
                    <h2><?php _e('Anteprima', 'revious-microdata'); ?></h2>
                    <div id="preview-container" style="border: 1px solid #ccc; padding: 20px; min-height: 200px; background: #fff;">
                        <?php _e('Inserisci i link per vedere l\'anteprima', 'revious-microdata'); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

// Handler AJAX per preview
add_action('wp_ajax_gik25_preview_links', function() {
    check_ajax_referer('gik25_preview', 'nonce');
    
    $links = $_POST['links'] ?? [];
    $style = sanitize_text_field($_POST['style'] ?? 'standard');
    $columns = (int)($_POST['columns'] ?? 1);
    $withImage = !empty($_POST['with_image']);
    
    $builder = new LinkBuilder($style, [
        'nColumns' => $columns,
        'withImage' => $withImage,
    ]);
    
    $html = $builder->createLinksFromArray($links);
    
    wp_send_json_success(['html' => $html]);
});
