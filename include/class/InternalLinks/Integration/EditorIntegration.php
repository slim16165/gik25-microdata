<?php
/**
 * Editor Integration - Integrates with Gutenberg and Classic Editor
 *
 * @package gik25microdata\InternalLinks\Integration
 */

namespace gik25microdata\InternalLinks\Integration;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Editor Integration class
 */
class EditorIntegration
{
    /**
     * Initialize editor integration
     *
     * @return void
     */
    public static function init()
    {
        $instance = new self();
        add_action('add_meta_boxes', [$instance, 'registerMetaBoxes']);
        add_action('admin_enqueue_scripts', [$instance, 'enqueueEditorAssets']);
    }

    /**
     * Register meta boxes
     *
     * @return void
     */
    public function registerMetaBoxes()
    {
        $post_types = get_post_types(['public' => true]);
        
        foreach ($post_types as $post_type) {
            add_meta_box(
                'gik25_il_suggestions',
                __('Internal Links Suggestions', 'gik25-microdata'),
                [$this, 'renderSuggestionsBox'],
                $post_type,
                'side',
                'default'
            );
        }
    }

    /**
     * Enqueue editor assets
     *
     * @param string $hook Current page hook
     * @return void
     */
    public function enqueueEditorAssets($hook)
    {
        if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
            return;
        }

        wp_enqueue_script(
            'gik25-il-editor',
            plugins_url('../../../../assets/internal-links/js/admin.js', __FILE__),
            ['jquery'],
            '1.0.0',
            true
        );
    }

    /**
     * Render suggestions meta box
     *
     * @param \WP_Post $post Post object
     * @return void
     */
    public function renderSuggestionsBox($post)
    {
        $manager = \gik25microdata\InternalLinks\Core\InternalLinksManager::getInstance();
        $suggestions = $manager->getSuggestions($post->ID, 5);

        echo '<div class="gik25-il-suggestions-box">';
        if (empty($suggestions)) {
            echo '<p>' . esc_html__('No suggestions available.', 'gik25-microdata') . '</p>';
        } else {
            echo '<ul>';
            foreach ($suggestions as $suggestion) {
                echo '<li>';
                echo '<a href="' . esc_url(get_permalink($suggestion['post_id'])) . '" target="_blank">';
                echo esc_html(get_the_title($suggestion['post_id']));
                echo '</a>';
                echo '</li>';
            }
            echo '</ul>';
        }
        echo '</div>';
    }
}

