<?php
namespace gik25microdata\Widgets;

if (!defined('ABSPATH')) {
	exit;
}

class ContextualWidgets
{
	public static function init(): void
	{
		add_filter('the_content', [self::class, 'injectWidgets'], 90);
	}

	public static function injectWidgets($content)
	{
		if (!is_singular('post') || is_admin()) {
			return $content;
		}

		global $post;
		if (!$post || empty($post->post_content)) {
			return $content;
		}

		$haystack = strtolower($post->post_title . ' ' . wp_strip_all_tags($post->post_content));
		$widgetsHtml = '';

		// 1) Kitchen Finder su articoli cucina/IKEA cucina
		if (self::containsAny($haystack, ['cucina', 'cucine', 'ikea cucina', 'metod', 'enhet', 'sunnersta', 'knoxhult'])) {
			$widgetsHtml .= do_shortcode('[kitchen_finder title="Trova la cucina perfetta per te" show_progress="true"]');
		}

		// 2) Palette/Abbinamenti se si parla di colori
		if (self::containsAny($haystack, ['colori', 'colore', 'palette'])) {
			$widgetsHtml .= do_shortcode('[td_palette_correlate]');
		}

		if (empty($widgetsHtml)) {
			return $content;
		}

		// Inserisci alla fine del contenuto
		$content .= "\n\n" . $widgetsHtml;
		return $content;
	}

	private static function containsAny(string $text, array $needles): bool
	{
		foreach ($needles as $needle) {
			if (str_contains($text, strtolower($needle))) {
				return true;
			}
		}
		return false;
	}
}


