<?php
namespace gik25microdata\Widgets;

use gik25microdata\ListOfPosts\LinkBuilder;
use WP_Widget;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Widget WordPress per liste di link configurabili
 */
class LinkListWidget extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'gik25_link_list',
            __('Lista Link Revious', 'revious-microdata'),
            [
                'description' => __('Mostra una lista di link configurabile', 'revious-microdata'),
            ]
        );
    }
    
    public function widget($args, $instance)
    {
        echo $args['before_widget'];
        
        $title = !empty($instance['title']) ? $instance['title'] : '';
        if (!empty($title)) {
            echo $args['before_title'] . apply_filters('widget_title', $title) . $args['after_title'];
        }
        
        // Parsing dei link dalla textarea
        $links_data = $this->parseLinks($instance['links'] ?? '');
        $style = $instance['style'] ?? 'standard';
        $nColumns = (int)($instance['columns'] ?? 1);
        $withImage = !empty($instance['with_image']);
        
        $builder = new LinkBuilder($style, [
            'removeIfSelf' => !empty($instance['remove_if_self']),
            'withImage' => $withImage,
            'nColumns' => $nColumns,
        ]);
        
        $html = $builder->createLinksFromArray($links_data, [
            'ulClass' => $instance['css_class'] ?? 'link-list-widget',
        ]);
        
        echo $html;
        echo $args['after_widget'];
    }
    
    public function form($instance)
    {
        $title = $instance['title'] ?? '';
        $links = $instance['links'] ?? '';
        $style = $instance['style'] ?? 'standard';
        $columns = $instance['columns'] ?? 1;
        $withImage = $instance['with_image'] ?? true;
        $removeIfSelf = $instance['remove_if_self'] ?? true;
        $cssClass = $instance['css_class'] ?? 'link-list-widget';
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php _e('Titolo:', 'revious-microdata'); ?>
            </label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text"
                   value="<?php echo esc_attr($title); ?>">
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('links')); ?>">
                <?php _e('Link (uno per riga, formato: URL|Titolo|Commento):', 'revious-microdata'); ?>
            </label>
            <textarea class="widefat" rows="10" id="<?php echo esc_attr($this->get_field_id('links')); ?>"
                      name="<?php echo esc_attr($this->get_field_name('links')); ?>"><?php echo esc_textarea($links); ?></textarea>
            <small><?php _e('Formato: https://example.com/page|Titolo Pagina|Commento opzionale', 'revious-microdata'); ?></small>
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('style')); ?>">
                <?php _e('Stile:', 'revious-microdata'); ?>
            </label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('style')); ?>"
                    name="<?php echo esc_attr($this->get_field_name('style')); ?>">
                <option value="standard" <?php selected($style, 'standard'); ?>><?php _e('Standard', 'revious-microdata'); ?></option>
                <option value="carousel" <?php selected($style, 'carousel'); ?>><?php _e('Carousel', 'revious-microdata'); ?></option>
                <option value="simple" <?php selected($style, 'simple'); ?>><?php _e('Semplice', 'revious-microdata'); ?></option>
            </select>
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('columns')); ?>">
                <?php _e('Colonne:', 'revious-microdata'); ?>
            </label>
            <input type="number" min="1" max="4" class="tiny-text"
                   id="<?php echo esc_attr($this->get_field_id('columns')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('columns')); ?>"
                   value="<?php echo esc_attr($columns); ?>">
        </p>
        
        <p>
            <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('with_image')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('with_image')); ?>"
                   <?php checked($withImage); ?>>
            <label for="<?php echo esc_attr($this->get_field_id('with_image')); ?>">
                <?php _e('Mostra immagini', 'revious-microdata'); ?>
            </label>
        </p>
        
        <p>
            <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('remove_if_self')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('remove_if_self')); ?>"
                   <?php checked($removeIfSelf); ?>>
            <label for="<?php echo esc_attr($this->get_field_id('remove_if_self')); ?>">
                <?php _e('Rimuovi link se punta alla pagina corrente', 'revious-microdata'); ?>
            </label>
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('css_class')); ?>">
                <?php _e('Classe CSS:', 'revious-microdata'); ?>
            </label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('css_class')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('css_class')); ?>" type="text"
                   value="<?php echo esc_attr($cssClass); ?>">
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance)
    {
        $instance = [];
        $instance['title'] = sanitize_text_field($new_instance['title'] ?? '');
        $instance['links'] = sanitize_textarea_field($new_instance['links'] ?? '');
        $instance['style'] = sanitize_text_field($new_instance['style'] ?? 'standard');
        $instance['columns'] = absint($new_instance['columns'] ?? 1);
        $instance['with_image'] = !empty($new_instance['with_image']);
        $instance['remove_if_self'] = !empty($new_instance['remove_if_self']);
        $instance['css_class'] = sanitize_html_class($new_instance['css_class'] ?? 'link-list-widget');
        return $instance;
    }
    
    /**
     * Parsing dei link dalla textarea
     * 
     * @param string $links_text Testo con i link
     * @return array Array di link
     */
    private function parseLinks(string $links_text): array
    {
        $links = [];
        $lines = explode("\n", $links_text);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            
            $parts = explode('|', $line, 3);
            $url = trim($parts[0] ?? '');
            $title = trim($parts[1] ?? '');
            $comment = trim($parts[2] ?? '');
            
            if (!empty($url) && !empty($title)) {
                $links[] = [
                    'target_url' => $url,
                    'nome' => $title,
                    'commento' => $comment,
                ];
            }
        }
        
        return $links;
    }
}
