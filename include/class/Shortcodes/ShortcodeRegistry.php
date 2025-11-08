<?php
namespace gik25microdata\Shortcodes;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Central registry for the built-in shortcodes.
 *
 * It exposes metadata for the admin UI and stores enable/disable
 * preferences so that users can temporarily turn off specific tags.
 */
class ShortcodeRegistry
{
    private const OPTION = 'gik25_shortcodes_enabled';
    private const USAGE_OPTION = 'gik25_shortcodes_usage_summary';
    private const MAX_USAGE_SCAN_ROWS = 500;
    private static bool $bootstrapped = false;

    /**
     * Ensure hooks are registered.
     */
    public static function init(): void
    {
        if (self::$bootstrapped) {
            return;
        }

        self::$bootstrapped = true;
        add_filter('pre_do_shortcode_tag', [self::class, 'filterDisabledShortcodes'], 10, 4);
    }

    /**
     * Return registry metadata.
     *
     * @return array<string, array>
     */
    public static function getRegistry(): array
    {
        return [
            'md_quote' => [
                'label' => 'Quote',
                'description' => 'Mostra un blockquote evidenziato.',
                'aliases' => ['quote'],
                'example' => '[md_quote]Testo citazione[/md_quote]',
                'class' => Quote::class,
            ],
            'md_boxinfo' => [
                'label' => 'Box Informativo',
                'description' => 'Evidenzia una nota con titolo opzionale.',
                'aliases' => ['boxinfo', 'boxinformativo'],
                'example' => '[boxinfo title="Curiosità"]contenuto[/boxinfo]',
                'class' => Boxinfo::class,
            ],
            'md_blinkingbutton' => [
                'label' => 'Blinking Button',
                'description' => 'Crea un pulsante lampeggiante per call-to-action.',
                'aliases' => ['blinkingbutton'],
                'example' => '[blinkingbutton url="https://example.com"]Vai[/blinkingbutton]',
                'class' => BlinkingButton::class,
            ],
            'md_progressbar' => [
                'label' => 'Progress Bar',
                'description' => 'Visualizza una barra di avanzamento personalizzabile.',
                'aliases' => ['progressbar'],
                'example' => '[progressbar percent="60"]Avanzamento[/progressbar]',
                'class' => Progressbar::class,
            ],
            'md_slidingbox' => [
                'label' => 'Sliding Box',
                'description' => 'Mostra un box con effetto slide.',
                'aliases' => ['slidingbox'],
                'example' => '[slidingbox title="Titolo"]Testo[/slidingbox]',
                'class' => Slidingbox::class,
            ],
            'md_flipbox' => [
                'label' => 'Flip Box',
                'description' => 'Box fronte/retro per presentare informazioni in poco spazio.',
                'aliases' => ['flipbox'],
                'example' => '[flipbox front_title="Fronte" back_title="Retro"]Contenuto[/flipbox]',
                'class' => Flipbox::class,
            ],
            'md_perfectpullquote' => [
                'label' => 'Perfect Pullquote',
                'description' => 'Blocco evidenziato per riportare estratti.',
                'aliases' => ['perfectpullquote'],
                'example' => '[perfectpullquote]Citazione[/perfectpullquote]',
                'class' => PerfectPullquote::class,
            ],
            'md_prezzo' => [
                'label' => 'Prezzo',
                'description' => 'Markup per evidenziare un’offerta/prezzo.',
                'aliases' => ['prezzo'],
                'example' => '[prezzo amount="39"]Descrizione[/prezzo]',
                'class' => Prezzo::class,
            ],
            'md_flexlist' => [
                'label' => 'Flex List',
                'description' => 'Lista flessibile (thumbnail + testo).',
                'aliases' => ['flexlist'],
                'example' => '[flexlist]...[/flexlist]',
                'class' => Flexlist::class,
            ],
            'md_telefono' => [
                'label' => 'Telefono',
                'description' => 'Inserisce blocchi di contatto/telefono SEO friendly.',
                'aliases' => ['telefono', 'microdata_telefono'],
                'example' => '[telefono number="+3906123456" organizationname="Azienda"]',
                'class' => Telefono::class,
            ],
            'md_youtube' => [
                'label' => 'YouTube',
                'description' => 'Embed ottimizzato di video YouTube.',
                'aliases' => ['youtube'],
                'example' => '[youtube id="VIDEO_ID"]',
                'class' => Youtube::class,
            ],
            'carousel' => [
                'label' => 'Generic Carousel',
                'description' => 'Carosello/griglia basata su collezioni configurabili.',
                'aliases' => ['list', 'grid'],
                'example' => '[carousel collection="colori"]',
                'class' => GenericCarousel::class,
            ],
        ];
    }

    /**
     * Get registry enriched with enabled flag.
     *
     * @return array<string,array>
     */
    public static function getItemsForAdmin(): array
    {
        $settings = self::getSettings();
        $items = [];
        foreach (self::getRegistry() as $slug => $meta) {
            $items[$slug] = array_merge(
                $meta,
                [
                    'slug' => $slug,
                    'enabled' => self::isSlugEnabled($slug, $settings),
                ]
            );
        }

        return $items;
    }

    /**
     * Enable/disable a shortcode.
     */
    public static function setSlugEnabled(string $slug, bool $enabled): void
    {
        $settings = self::getSettings();
        $settings[$slug] = $enabled;
        update_option(self::OPTION, $settings, false);
    }

    /**
     * Determine if tag (slug or alias) is enabled.
     */
    public static function isTagEnabled(string $tag): bool
    {
        $slug = self::resolveSlugFromTag($tag);
        if (!$slug) {
            return true;
        }

        return self::isSlugEnabled($slug, self::getSettings());
    }

    /**
     * Hook that prevents disabled shortcodes from rendering.
     *
     * @param mixed $output  Return value for shortcode (null by default)
     * @param string $tag    Shortcode currently being processed
     */
    public static function filterDisabledShortcodes($output, string $tag)
    {
        if (!self::isTagEnabled($tag)) {
            return '';
        }

        return $output;
    }

    /**
     * Return dropdown ready array slug=>label (includes alias hint)
     */
    public static function getOptionsForSelect(): array
    {
        $options = [];
        foreach (self::getRegistry() as $slug => $meta) {
            $label = $meta['label'] ?? $slug;
            if (!empty($meta['aliases'])) {
                $label .= ' (' . implode(', ', $meta['aliases']) . ')';
            }
            $options[$slug] = $label;
        }

        return $options;
    }

    /**
     * Count occurrences of a tag inside given content.
     */
    public static function countOccurrences(string $tag, ?string $content): int
    {
        if ($content === '' || $content === null) {
            return 0;
        }

        $pattern = '/\[' . preg_quote($tag, '/') . '\b/i';
        if (!preg_match_all($pattern, $content, $matches)) {
            return 0;
        }

        return count($matches[0]);
    }

    /**
     * Resolve alias to canonical slug.
     */
    public static function resolveSlugFromTag(string $tag): ?string
    {
        $tag = strtolower($tag);
        foreach (self::getRegistry() as $slug => $meta) {
            if ($slug === $tag) {
                return $slug;
            }
            foreach ($meta['aliases'] ?? [] as $alias) {
                if (strtolower($alias) === $tag) {
                    return $slug;
                }
            }
        }

        return null;
    }

    /**
     * Retrieve saved enable/disable settings.
     *
     * @return array<string,bool>
     */
    private static function getSettings(): array
    {
        $settings = get_option(self::OPTION, []);
        if (!is_array($settings)) {
            return [];
        }

        return $settings;
    }

    private static function isSlugEnabled(string $slug, array $settings): bool
    {
        if (!array_key_exists($slug, $settings)) {
            return true; // default ON
        }

        return (bool) $settings[$slug];
    }

    /**
     * Get cached usage summary data.
     *
     * @return array{updated_at:string|null,data:array<string,array>} 
     */
    public static function getUsageSummary(): array
    {
        $option = get_option(self::USAGE_OPTION, [
            'updated_at' => null,
            'data' => [],
        ]);

        if (!is_array($option)) {
            return ['updated_at' => null, 'data' => []];
        }

        $option['data'] = isset($option['data']) && is_array($option['data']) ? $option['data'] : [];

        return $option;
    }

    /**
     * Run LIKE queries for every shortcode and store the summary.
     *
     * @return array<string,array{posts:int,occurrences:int,label:string}>
     */
    public static function scanUsageSummary(): array
    {
        global $wpdb;

        $summary = [];
        foreach (self::getRegistry() as $slug => $meta) {
            $like = '%[' . $wpdb->esc_like($slug) . '%';
            $sql = $wpdb->prepare(
                "SELECT ID, post_content
                 FROM {$wpdb->posts}
                 WHERE post_status NOT IN ('trash','auto-draft','inherit')
                   AND post_content LIKE %s
                 LIMIT %d",
                $like,
                self::MAX_USAGE_SCAN_ROWS
            );
            $rows = $wpdb->get_results($sql, ARRAY_A);
            $posts = is_array($rows) ? count($rows) : 0;
            $occurrences = 0;
            if ($rows) {
                foreach ($rows as $row) {
                    $occurrences += self::countOccurrences($slug, $row['post_content'] ?? '');
                }
            }
            $summary[$slug] = [
                'posts' => $posts,
                'occurrences' => $occurrences,
                'label' => $meta['label'] ?? $slug,
            ];
        }

        update_option(self::USAGE_OPTION, [
            'updated_at' => current_time('mysql'),
            'data' => $summary,
        ], false);

        return $summary;
    }

    /**
     * Helper to return human readable label for slug.
     */
    public static function getLabel(string $slug): string
    {
        $registry = self::getRegistry();
        return $registry[$slug]['label'] ?? $slug;
    }
}
