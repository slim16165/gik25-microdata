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
		], $atts);

		ob_start();
		?>
		<div class="td-appnav" data-appnav>
			<header class="td-appnav__header">
				<h2 class="td-appnav__title"><?php echo esc_html($atts['title']); ?></h2>
				<nav class="td-appnav__tabs" role="tablist" aria-label="Sezioni">
					<button class="td-appnav__tab is-active" role="tab" aria-selected="true" data-section="scopri">Scopri</button>
					<button class="td-appnav__tab" role="tab" aria-selected="false" data-section="colori">Colori</button>
					<button class="td-appnav__tab" role="tab" aria-selected="false" data-section="ikea">IKEA</button>
					<button class="td-appnav__tab" role="tab" aria-selected="false" data-section="stanze">Stanze</button>
					<button class="td-appnav__tab" role="tab" aria-selected="false" data-section="trend">Trend</button>
				</nav>
			</header>

			<section class="td-appnav__section is-active" id="td-section-scopri" role="tabpanel" aria-labelledby="scopri">
				<div class="td-card-grid">
					<a class="td-card td-card--cta" href="<?php echo esc_url(home_url('/colori/')); ?>">
						<h3>Colori &amp; Palette</h3>
						<p>Palette pronte, abbinamenti e materiali.</p>
					</a>
					<a class="td-card td-card--cta" href="<?php echo esc_url(home_url('/ikea/')); ?>">
						<h3>IKEA Guide &amp; Hack</h3>
						<p>Compatibilità, idee salvaspazio e progetti.</p>
					</a>
					<a class="td-card td-card--cta" href="<?php echo esc_url(home_url('/?s=trend%20colori%20arredamento')); ?>">
						<h3>Tendenze</h3>
						<p>Colori e idee di stagione verificate.</p>
					</a>
				</div>
			</section>

			<section class="td-appnav__section" id="td-section-colori" role="tabpanel" aria-labelledby="colori">
				<div class="td-card-grid">
					<a class="td-card" href="<?php echo esc_url(home_url('/colori/')); ?>"><h3>Biblioteca colori</h3><p>Palette pronte e materiali abbinati</p></a>
					<a class="td-card" href="<?php echo esc_url(home_url('/colori-pantone/')); ?>"><h3>Pantone</h3><p>I colori dell'anno e le palette</p></a>
					<a class="td-card" href="<?php echo esc_url(home_url('/abbinamento-colori/')); ?>"><h3>Abbinamenti</h3><p>Guide rapide agli accostamenti</p></a>
				</div>
			</section>

			<section class="td-appnav__section" id="td-section-ikea" role="tabpanel" aria-labelledby="ikea">
				<div class="td-card-grid">
					<a class="td-card" href="<?php echo esc_url(home_url('/ikea/')); ?>"><h3>Guida IKEA</h3><p>Hack, compatibilità e consigli</p></a>
					<a class="td-card" href="<?php echo esc_url(home_url('/?s=metod%20enhet%20sunnersta')); ?>"><h3>Sistemi cucina</h3><p>METOD, ENHET, SUNNERSTA</p></a>
					<a class="td-card" href="<?php echo esc_url(home_url('/?s=hack%20ikea')); ?>"><h3>Hack verificati</h3><p>Idee smart e salvaspazio</p></a>
				</div>
			</section>

			<section class="td-appnav__section" id="td-section-stanze" role="tabpanel" aria-labelledby="stanze">
				<div class="td-card-grid">
					<a class="td-card" href="<?php echo esc_url(home_url('/colori-pareti-soggiorno/')); ?>"><h3>Soggiorno</h3><p>Colori e palette per la zona giorno</p></a>
					<a class="td-card" href="<?php echo esc_url(home_url('/colori-pareti-cucina/')); ?>"><h3>Cucina</h3><p>Idee pratiche e materiali compatibili</p></a>
					<a class="td-card" href="<?php echo esc_url(home_url('/colori-pareti-bagno/')); ?>"><h3>Bagno</h3><p>Palette antimuffa e materiali</p></a>
				</div>
			</section>

			<section class="td-appnav__section" id="td-section-trend" role="tabpanel" aria-labelledby="trend">
				<div class="td-card-grid">
					<a class="td-card" href="<?php echo esc_url(home_url('/?s=trend%202025%20colori')); ?>"><h3>Trend colori</h3><p>Le palette più richieste</p></a>
					<a class="td-card" href="<?php echo esc_url(home_url('/?s=ikea%20novita')); ?>"><h3>Novità IKEA</h3><p>Linee e accessori in evidenza</p></a>
					<a class="td-card" href="<?php echo esc_url(home_url('/?s=vernice%20pareti')); ?>"><h3>Prodotti consigliati</h3><p>Vernici e tessili compatibili</p></a>
				</div>
			</section>
		</div>
		<?php
		return ob_get_clean();
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


