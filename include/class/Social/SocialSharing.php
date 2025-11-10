<?php
namespace gik25microdata\Social;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Social Sharing Avanzato
 * 
 * Share buttons con analytics e tracking
 */
class SocialSharing
{
    /**
     * Inizializza social sharing
     */
    public static function init(): void
    {
        add_action('wp_footer', [self::class, 'enqueueScripts']);
        add_action('wp_ajax_revious_share', [self::class, 'handleShare']);
        add_action('wp_ajax_nopriv_revious_share', [self::class, 'handleShare']);
    }
    
    /**
     * Renderizza share buttons
     */
    public static function renderButtons(array $args = []): string
    {
        $defaults = [
            'platforms' => ['facebook', 'twitter', 'linkedin', 'whatsapp'],
            'style' => 'icons', // icons, text, both
            'position' => 'inline', // inline, floating
        ];
        
        $args = wp_parse_args($args, $defaults);
        $post_id = get_the_ID();
        $url = urlencode(get_permalink($post_id));
        $title = urlencode(get_the_title($post_id));
        $description = urlencode(wp_trim_words(get_the_excerpt(), 20));
        
        $html = '<div class="revious-social-share" data-post-id="' . esc_attr($post_id) . '">';
        
        foreach ($args['platforms'] as $platform) {
            $html .= self::renderButton($platform, $url, $title, $description, $args['style']);
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Renderizza singolo button
     */
    private static function renderButton(string $platform, string $url, string $title, string $description, string $style): string
    {
        $classes = ['revious-share-btn', 'revious-share-' . $platform];
        
        $share_url = '';
        $icon = '';
        $label = '';
        
        switch ($platform) {
            case 'facebook':
                $share_url = "https://www.facebook.com/sharer/sharer.php?u={$url}";
                $icon = '📘';
                $label = 'Facebook';
                break;
            case 'twitter':
                $share_url = "https://twitter.com/intent/tweet?url={$url}&text={$title}";
                $icon = '🐦';
                $label = 'Twitter';
                break;
            case 'linkedin':
                $share_url = "https://www.linkedin.com/sharing/share-offsite/?url={$url}";
                $icon = '💼';
                $label = 'LinkedIn';
                break;
            case 'whatsapp':
                $share_url = "https://wa.me/?text={$title}%20{$url}";
                $icon = '💬';
                $label = 'WhatsApp';
                break;
            case 'pinterest':
                $share_url = "https://pinterest.com/pin/create/button/?url={$url}&description={$title}";
                $icon = '📌';
                $label = 'Pinterest';
                break;
        }
        
        if (empty($share_url)) {
            return '';
        }
        
        $content = '';
        if ($style === 'icons' || $style === 'both') {
            $content .= '<span class="share-icon">' . $icon . '</span>';
        }
        if ($style === 'text' || $style === 'both') {
            $content .= '<span class="share-label">' . esc_html($label) . '</span>';
        }
        
        return sprintf(
            '<a href="%s" class="%s" data-platform="%s" target="_blank" rel="noopener noreferrer">%s</a>',
            esc_url($share_url),
            esc_attr(implode(' ', $classes)),
            esc_attr($platform),
            $content
        );
    }
    
    /**
     * Handler AJAX per tracking share
     */
    public static function handleShare(): void
    {
        check_ajax_referer('revious_social', 'nonce');
        
        $post_id = intval($_POST['post_id'] ?? 0);
        $platform = sanitize_text_field($_POST['platform'] ?? '');
        
        if ($post_id && $platform) {
            // Incrementa counter
            $shares = get_post_meta($post_id, '_revious_social_shares', true) ?: 0;
            update_post_meta($post_id, '_revious_social_shares', $shares + 1);
            
            // Traccia evento analytics se disponibile
            if (class_exists('\gik25microdata\Analytics\AnalyticsTracker')) {
                \gik25microdata\Analytics\AnalyticsTracker::track('social', 'share', [
                    'platform' => $platform,
                    'post_id' => $post_id,
                ]);
            }
        }
        
        wp_send_json_success();
    }
    
    /**
     * Enqueue scripts
     */
    public static function enqueueScripts(): void
    {
        if (!is_singular()) {
            return;
        }
        
        $nonce = wp_create_nonce('revious_social');
        ?>
        <style>
        .revious-social-share {
            display: flex;
            gap: 10px;
            margin: 20px 0;
        }
        .revious-share-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            transition: transform 0.2s;
        }
        .revious-share-btn:hover {
            transform: translateY(-2px);
        }
        </style>
        <script>
        (function() {
            document.querySelectorAll('.revious-share-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const postId = this.closest('.revious-social-share').dataset.postId;
                    const platform = this.dataset.platform;
                    
                    fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: new URLSearchParams({
                            action: 'revious_share',
                            nonce: '<?php echo esc_js($nonce); ?>',
                            post_id: postId,
                            platform: platform
                        })
                    }).catch(() => {});
                });
            });
        })();
        </script>
        <?php
    }
}
