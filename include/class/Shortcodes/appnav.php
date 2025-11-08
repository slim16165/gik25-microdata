<?php
namespace gik25microdata\Shortcodes;

if (!defined('ABSPATH')) {
	exit;
}

class AppNav extends ShortcodeBase
{
	public function __construct()
	{
		$this->shortcode = 'app_nav';
		parent::__construct();
	}

	public function ShortcodeHandler($atts, $content = null)
	{
		$atts = shortcode_atts([
			'title' => 'Esplora il sito come un\'app',
			'variant' => 'auto', // 'auto', 'mobile', 'desktop'
		], $atts);

		// Struttura multilivello
		$nav_structure = $this->getNavigationStructure();

		ob_start();
		?>
		<div class="td-appnav" data-appnav data-variant="<?php echo esc_attr($atts['variant']); ?>">
			<header class="td-appnav__header">
				<h2 class="td-appnav__title"><?php echo esc_html($atts['title']); ?></h2>
				<nav class="td-appnav__tabs" role="tablist" aria-label="Sezioni principali">
					<?php foreach ($nav_structure as $key => $section): ?>
						<button class="td-appnav__tab <?php echo $key === 'scopri' ? 'is-active' : ''; ?>" 
								role="tab" 
								aria-selected="<?php echo $key === 'scopri' ? 'true' : 'false'; ?>" 
								data-section="<?php echo esc_attr($key); ?>"
								data-has-submenu="<?php echo !empty($section['subsections']) ? 'true' : 'false'; ?>">
							<?php echo esc_html($section['label']); ?>
							<?php if (!empty($section['subsections'])): ?>
								<svg class="td-appnav__tab-icon" viewBox="0 0 24 24" aria-hidden="true">
									<path d="M7 10l5 5 5-5" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/>
								</svg>
							<?php endif; ?>
						</button>
					<?php endforeach; ?>
				</nav>
			</header>

			<?php foreach ($nav_structure as $key => $section): ?>
				<section class="td-appnav__section <?php echo $key === 'scopri' ? 'is-active' : ''; ?>" 
						 id="td-section-<?php echo esc_attr($key); ?>" 
						 role="tabpanel" 
						 aria-labelledby="<?php echo esc_attr($key); ?>">
					
					<?php if (!empty($section['subsections'])): ?>
						<!-- Desktop: mostra tutte le sottosezioni in griglia -->
						<div class="td-appnav__subsections-desktop">
							<?php foreach ($section['subsections'] as $subkey => $subsection): ?>
								<div class="td-appnav__subsection">
									<h3 class="td-appnav__subsection-title"><?php echo esc_html($subsection['label']); ?></h3>
									<div class="td-card-grid">
										<?php foreach ($subsection['items'] as $item): ?>
											<a class="td-card <?php echo !empty($item['cta']) ? 'td-card--cta' : ''; ?>" 
											   href="<?php echo esc_url($item['url']); ?>">
												<?php if (!empty($item['icon'])): ?>
													<span class="td-card__icon"><?php echo $item['icon']; ?></span>
												<?php endif; ?>
												<h4><?php echo esc_html($item['title']); ?></h4>
												<?php if (!empty($item['description'])): ?>
													<p><?php echo esc_html($item['description']); ?></p>
												<?php endif; ?>
											</a>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</div>

						<!-- Mobile: menu a livelli con breadcrumb -->
						<div class="td-appnav__subsections-mobile">
							<div class="td-appnav__level" data-level="1">
								<div class="td-appnav__breadcrumb">
									<button class="td-appnav__back" aria-label="Torna indietro" style="display:none;">
										<svg viewBox="0 0 24 24" aria-hidden="true">
											<path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/>
										</svg>
										<span class="td-appnav__back-label"></span>
									</button>
								</div>
								<div class="td-appnav__submenu">
									<?php foreach ($section['subsections'] as $subkey => $subsection): ?>
										<button class="td-appnav__submenu-item" 
												data-subsection="<?php echo esc_attr($subkey); ?>"
												data-parent="<?php echo esc_attr($key); ?>">
											<span class="td-appnav__submenu-label"><?php echo esc_html($subsection['label']); ?></span>
											<svg class="td-appnav__submenu-arrow" viewBox="0 0 24 24" aria-hidden="true">
												<path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/>
											</svg>
											<span class="td-appnav__submenu-count"><?php echo count($subsection['items']); ?></span>
										</button>
									<?php endforeach; ?>
								</div>
							</div>

							<?php foreach ($section['subsections'] as $subkey => $subsection): ?>
								<div class="td-appnav__level" data-level="2" data-subsection="<?php echo esc_attr($subkey); ?>" style="display:none;">
									<div class="td-card-grid td-card-grid--mobile">
										<?php foreach ($subsection['items'] as $item): ?>
											<a class="td-card td-card--mobile <?php echo !empty($item['cta']) ? 'td-card--cta' : ''; ?>" 
											   href="<?php echo esc_url($item['url']); ?>">
												<?php if (!empty($item['icon'])): ?>
													<span class="td-card__icon"><?php echo $item['icon']; ?></span>
												<?php endif; ?>
												<h4><?php echo esc_html($item['title']); ?></h4>
												<?php if (!empty($item['description'])): ?>
													<p><?php echo esc_html($item['description']); ?></p>
												<?php endif; ?>
											</a>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php else: ?>
						<!-- Sezione senza sottosezioni -->
						<div class="td-card-grid">
							<?php foreach ($section['items'] as $item): ?>
								<a class="td-card <?php echo !empty($item['cta']) ? 'td-card--cta' : ''; ?>" 
								   href="<?php echo esc_url($item['url']); ?>">
									<?php if (!empty($item['icon'])): ?>
										<span class="td-card__icon"><?php echo $item['icon']; ?></span>
									<?php endif; ?>
									<h3><?php echo esc_html($item['title']); ?></h3>
									<?php if (!empty($item['description'])): ?>
										<p><?php echo esc_html($item['description']); ?></p>
									<?php endif; ?>
								</a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</section>
			<?php endforeach; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Genera la struttura multilivello della navigazione
	 */
	private function getNavigationStructure(): array
	{
		$home = home_url();
		
		return [
			'scopri' => [
				'label' => 'Scopri',
				'items' => [
					[
						'title' => 'Colori &amp; Palette',
						'description' => 'Palette pronte, abbinamenti e materiali',
						'url' => $home . '/colori/',
						'cta' => true,
						'icon' => '🎨'
					],
					[
						'title' => 'IKEA Guide &amp; Hack',
						'description' => 'Compatibilità, idee salvaspazio e progetti',
						'url' => $home . '/ikea/',
						'cta' => true,
						'icon' => '🏠'
					],
					[
						'title' => 'Tendenze',
						'description' => 'Colori e idee di stagione verificate',
						'url' => $home . '/?s=trend%20colori%20arredamento',
						'cta' => true,
						'icon' => '✨'
					],
				]
			],
			'colori' => [
				'label' => 'Colori',
				'subsections' => [
					'biblioteca' => [
						'label' => 'Biblioteca Colori',
						'items' => [
							['title' => 'Bianco', 'description' => 'Palette e abbinamenti', 'url' => $home . '/colore-bianco/', 'icon' => '⚪'],
							['title' => 'Nero', 'description' => 'Stile e materiali', 'url' => $home . '/colore-nero/', 'icon' => '⚫'],
							['title' => 'Grigio', 'description' => 'Tonalità e accostamenti', 'url' => $home . '/grigio-chiaro/', 'icon' => '🔘'],
							['title' => 'Beige', 'description' => 'Palette calde', 'url' => $home . '/colore-beige/', 'icon' => '🟤'],
							['title' => 'Tortora', 'description' => 'Neutro elegante', 'url' => $home . '/color-tortora-colore-neutro-tendenza/', 'icon' => '🟫'],
							['title' => 'Verde Salvia', 'description' => 'Tendenza naturale', 'url' => $home . '/colore-verde-salvia/', 'icon' => '🌿'],
						]
					],
					'pantone' => [
						'label' => 'Pantone',
						'items' => [
							['title' => 'Very Peri 2022', 'description' => 'Colore dell\'anno', 'url' => $home . '/il-very-peri-e-il-colore-dellanno-2022-secondo-pantone/', 'icon' => '💜'],
							['title' => 'Classic Blue 2020', 'description' => 'Pantone 2020', 'url' => $home . '/classic-blue-pantone/', 'icon' => '💙'],
							['title' => 'Ultra Violet 2018', 'description' => 'Pantone 2018', 'url' => $home . '/ultra-violet-inspiration-scopri-come-arredare-la-casa-con-il-colore-pantone-2018/', 'icon' => '💜'],
							['title' => 'Tutti i Pantone', 'description' => 'Palette complete', 'url' => $home . '/colori-pantone/', 'icon' => '🎨'],
						]
					],
					'guide' => [
						'label' => 'Guide',
						'items' => [
							['title' => 'Abbinamenti', 'description' => 'Come accostare i colori', 'url' => $home . '/abbinamento-colori/', 'icon' => '🎯'],
							['title' => 'Colori complementari', 'description' => 'Teoria e pratica', 'url' => $home . '/colori-complementari/', 'icon' => '🔄'],
							['title' => 'Colori caldi e freddi', 'description' => 'Guida completa', 'url' => $home . '/colori-caldi-freddi-e-neutri/', 'icon' => '🌡️'],
							['title' => 'Catalogo pareti', 'description' => 'Tutti i colori per arredare', 'url' => $home . '/catalogo-colori-pareti/', 'icon' => '📚'],
						]
					],
				]
			],
			'ikea' => [
				'label' => 'IKEA',
				'subsections' => [
					'sistemi' => [
						'label' => 'Sistemi Cucina',
						'items' => [
							['title' => 'METOD', 'description' => 'Sistema premium modulare', 'url' => $home . '/?s=metod%20cucina', 'icon' => '🍳'],
							['title' => 'ENHET', 'description' => 'Qualità/prezzo ottimale', 'url' => $home . '/?s=enhet%20cucina', 'icon' => '🏡'],
							['title' => 'SUNNERSTA', 'description' => 'Economico essenziale', 'url' => $home . '/?s=sunnersta', 'icon' => '💰'],
							['title' => 'KNOXHULT', 'description' => 'Cucine piccole', 'url' => $home . '/?s=knoxhult', 'icon' => '📏'],
							['title' => 'Kitchen Finder', 'description' => 'Trova la cucina perfetta', 'url' => $home . '/?s=kitchen%20finder', 'icon' => '🔍'],
						]
					],
					'linee' => [
						'label' => 'Linee Popolari',
						'items' => [
							['title' => 'BILLY', 'description' => 'Librerie e vetrine', 'url' => $home . '/?s=billy%20ikea', 'icon' => '📚'],
							['title' => 'KALLAX', 'description' => 'Moduli cubi versatili', 'url' => $home . '/?s=kallax', 'icon' => '📦'],
							['title' => 'BESTA', 'description' => 'Parete attrezzata', 'url' => $home . '/?s=besta', 'icon' => '🪑'],
							['title' => 'PAX', 'description' => 'Guardaroba modulare', 'url' => $home . '/?s=pax%20ikea', 'icon' => '👔'],
						]
					],
					'hack' => [
						'label' => 'Hack &amp; Guide',
						'items' => [
							['title' => 'Hack verificati', 'description' => 'Idee smart', 'url' => $home . '/?s=hack%20ikea', 'icon' => '🔧'],
							['title' => 'Compatibilità', 'description' => 'Accessori e alternative', 'url' => $home . '/?s=compatibilita%20ikea', 'icon' => '🔗'],
							['title' => 'Guida completa', 'description' => 'Tutti gli articoli IKEA', 'url' => $home . '/ikea/', 'icon' => '📖'],
						]
					],
				]
			],
			'stanze' => [
				'label' => 'Stanze',
				'subsections' => [
					'soggiorno' => [
						'label' => 'Soggiorno',
						'items' => [
							['title' => 'Colori pareti', 'description' => 'Palette zona giorno', 'url' => $home . '/colori-pareti-soggiorno/', 'icon' => '🎨'],
							['title' => 'Arredamento', 'description' => 'Idee e complementi', 'url' => $home . '/?s=soggiorno%20arredamento', 'icon' => '🛋️'],
							['title' => 'IKEA soggiorno', 'description' => 'Hack e soluzioni', 'url' => $home . '/?s=soggiorno%20ikea', 'icon' => '🏠'],
						]
					],
					'cucina' => [
						'label' => 'Cucina',
						'items' => [
							['title' => 'Colori pareti', 'description' => 'Palette pratiche', 'url' => $home . '/colori-pareti-cucina/', 'icon' => '🎨'],
							['title' => 'Sistemi IKEA', 'description' => 'METOD, ENHET, SUNNERSTA', 'url' => $home . '/?s=cucina%20ikea', 'icon' => '🍳'],
							['title' => 'Kitchen Finder', 'description' => 'Trova la cucina perfetta', 'url' => $home . '/?s=kitchen%20finder', 'icon' => '🔍'],
						]
					],
					'camera' => [
						'label' => 'Camera',
						'items' => [
							['title' => 'Colori pareti', 'description' => 'Palette rilassanti', 'url' => $home . '/colori-pareti-camera-da-letto/', 'icon' => '🎨'],
							['title' => 'Guardaroba', 'description' => 'PAX e soluzioni', 'url' => $home . '/?s=guardaroba%20ikea', 'icon' => '👔'],
						]
					],
					'bagno' => [
						'label' => 'Bagno',
						'items' => [
							['title' => 'Colori pareti', 'description' => 'Antimuffa e materiali', 'url' => $home . '/colori-pareti-bagno/', 'icon' => '🎨'],
							['title' => 'IKEA bagno', 'description' => 'GODMORGON e accessori', 'url' => $home . '/?s=bagno%20ikea', 'icon' => '🚿'],
						]
					],
				]
			],
			'trend' => [
				'label' => 'Trend',
				'items' => [
					['title' => 'Trend colori 2025', 'description' => 'Le palette più richieste', 'url' => $home . '/?s=trend%202025%20colori', 'icon' => '🎨'],
					['title' => 'Novità IKEA', 'description' => 'Linee e accessori', 'url' => $home . '/?s=ikea%20novita', 'icon' => '🆕'],
					['title' => 'Prodotti consigliati', 'description' => 'Vernici e tessili', 'url' => $home . '/?s=vernice%20pareti', 'icon' => '🛒'],
				]
			],
		];
	}

	public function styles()
	{
		$css_abs = dirname(__DIR__, 3) . '/assets/css/app-nav.css';
		$css_ver = file_exists($css_abs) ? (string) filemtime($css_abs) : '1.0.0';
		wp_register_style(
			'app-nav-css',
			plugins_url('gik25-microdata/assets/css/app-nav.css'),
			[],
			$css_ver,
			'all'
		);
		wp_enqueue_style('app-nav-css');
	}

	public function scripts()
	{
		$js_abs = dirname(__DIR__, 3) . '/assets/js/app-nav.js';
		$js_ver = file_exists($js_abs) ? (string) filemtime($js_abs) : '1.0.0';
		wp_register_script(
			'app-nav-js',
			plugins_url('gik25-microdata/assets/js/app-nav.js'),
			[],
			$js_ver,
			true
		);
		wp_enqueue_script('app-nav-js');
	}

	public function admin_scripts()
	{
		// not used
	}

	public function register_plugin($plugin_array)
	{
		return $plugin_array;
	}

	public function register_button($buttons)
	{
		return $buttons;
	}
}

$td_appnav = new AppNav();


