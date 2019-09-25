<?php
	/**
	 * Created by PhpStorm.
	 * User: g.salvi
	 * Date: 17/09/2019
	 * Time: 11:54
	 */

	require_once("ShortCodeHelper.php");
	require_once("functions.php");

	//add_action('wp_enqueue_scripts', 'revious_microdata_styles');

	function revious_microdata_styles()
	{
		// Register the style like this for a plugin:
		//wp_register_style('revious-quotes-styles', plugins_url('/revious_microdata.css', __FILE__), array(), '1.7.5', 'all');
		// For either a plugin or a theme, you can then enqueue the style:
		//wp_enqueue_style('revious-quotes-styles');
	}

	//EnableErrorLogging();


	add_shortcode('microdata_telefono', 'microdata_telefono');
	add_shortcode('microdata_prezzo', 'microdata_prezzo');
	add_shortcode('youtube', 'youtube_handler');
	add_shortcode('quote', 'quote_handler');
	add_shortcode('flexlist', 'flexlist_handler');


	function microdata_telefono($atts, $content = null)
	{
		$attrValue = shortcode_atts(array(
			'organizationname' => null // (Optional)
		), $atts);

		$organizationName = $atts['organizationname'];

		$telefonoPuro = wp_strip_all_tags( $content, true);

		if(substr( $telefonoPuro, 0, 1 ) === "+")
			$telefonoSchema = $telefonoPuro;
		else
			$telefonoSchema = "+39-$telefonoPuro";


		$result = "<a href=\"tel:$telefonoPuro\" style=\"color:green;\">$content</a>";

		if(!is_null($organizationName) && !empty($organizationName))
		{
			$result = <<<EOF
<span>
  <span>$organizationName</span>
  $result  
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "$organizationName",
      "contactPoint": {
        "@type": "ContactPoint",
        "telephone": "$telefonoSchema",
        "contactType": "customer support"
      }
    }
    </script>
  
</span>
EOF;
		}

		return $result;
	}

	function microdata_prezzo($atts, $content = null)
	{
		$result = <<<EOF
<span itemprop="offers" itemscope itemtype="http://schema.org/Offer">
    <span itemprop="priceCurrency" content="EUR">€</span>
    <span itemprop="price">
EOF
			.do_shortcode($content)
			."</span>" //Fine price span
			."</span>"; //Fine offer span


		return $result;
	}

	function youtube_handler($atts, $content = null)
	{
		$result = wp_oembed_get($atts["url"]);
		return $result;
	}

	function quote_handler($atts, $content = null)
	{
		$result = "<blockquote>$content</blockquote>";
		return $result;
	}

	function flexlist_handler($atts, $content = null)
	{
		$html = $content;
		$dom = new DOMDocument;
		$dom->loadHTML($html);
		$xpath = new DOMXPath($dom);
		$nodes = $xpath->query("//ul");
		foreach($nodes as $node) {
			$node->setAttribute('style', 'display: flex; flex-wrap: wrap;');
		}

		$nodes = $xpath->query("//li");
		foreach($nodes as $node) {
			$node->setAttribute('style', 'margin-right: 5px;');
		}

		return $dom->saveHTML();
	}

	function linkIfNotSelf_ND($url, $nome, $commento = "")
	{
		global $post;
		$permalink = get_permalink($post->ID);
		if ($permalink != $url)
		{
			if (IsNullOrEmptyString($commento))
				return "<li><a href=\"$url\">$nome</a></li>\n";
			else
				return "<li><a href=\"$url\">$nome</a> $commento</li>\n";
		}
		else
		{
			if (IsNullOrEmptyString($commento))
				return "<li>$nome (articolo corrente)</li>\n";
			else
				return "<li>$nome $commento (articolo corrente)</li>\n";
		}
	}

	function linkIfNotSelf($target_url, $nome, $removeIfSelf = true)
	{
		global $current_post; //il post corrente
		$current_permalink = get_permalink( $current_post->ID );
		$target_url = ReplaceTargetUrlIfStaging($target_url);

		if($current_permalink != $target_url)
		{

			$target_postid = url_to_postid( $target_url );

			if($target_postid == 0)
				return "";

			$target_post = get_post($target_postid);
			if($target_post->post_status === "publish")
			{
				$featured_img_url = get_the_post_thumbnail_url($target_post->ID, 'thumbnail');
				return <<<TAG
<li>
<a href="$target_url">			
<div class="li-img">
	<img src="$featured_img_url" alt="$nome" />		
</div>
<div class="li-text">$nome</div>
</a></li>\n
TAG;
			}
		}
		else if(!$removeIfSelf)
		{
			$target_postid = url_to_postid( $target_url );

			if($target_postid == 0)
				return "";

			$target_post = get_post($target_postid);
			if($target_post->post_status === "publish")
			{
				$featured_img_url = get_the_post_thumbnail_url($target_post->ID, 'thumbnail');
				return <<<TAG
<li>
<div class="li-img">
	<img src="$featured_img_url" alt="$nome" />		
</div>
<div class="li-text">$nome</div>
</li>\n
TAG;
			}
		}
	}




	function linkIfNotSelf2($url, $nome)
	{
		global $current_post;
		$permalink = get_permalink( $current_post->ID );

		if($permalink != $url)
		{
			return "<a href=\"$url\">$nome</a>";
		}
		else
		{
			return "$nome";
		}
	}

	function GetLinkWithImage(string $target_url, string $nome, string $commento = "", bool $removeIfSelf = false)
	{
		$target_url = ReplaceTargetUrlIfStaging($target_url);

		global $post, $MY_DEBUG; //il post corrente
		$current_post = $post;
		$result ="";

		if(!IsNullOrEmptyString($commento))
			$commento = " ($commento)";

		$current_permalink = get_permalink( $current_post->ID );

		//Check if the current post is the same of the target_url
		$sameFile2 = strcmp($current_permalink, $target_url);
		$sameFile = $sameFile2 == 0;

////DEBUG
//    $val = <<<TAG
//<p>current_permalink: $current_permalink<br/>
//target_url: $target_url<br/>
//sameFile2: $sameFile2<br/>
//sameFile: $sameFile<br/>
//</p>
//TAG;
//    return $val;

		if($sameFile && $removeIfSelf)
		{
			if( $MY_DEBUG )
				return "sameFile && removeIfSelf";
			else
				return "";
		}

		$target_postid = url_to_postid($target_url);

		if ($target_postid == 0)
		{
			if( $MY_DEBUG)
				return "target_postid == 0";
			else
				return "";
		}

		$target_post = get_post($target_postid);

		if( $MY_DEBUG )
			$result.="259-";

		if ($target_post->post_status === "publish")
		{

			if( $MY_DEBUG)
				$result.="266-";

			$featured_img_url = get_the_post_thumbnail_url($target_post->ID, 'thumbnail');

			if($MY_DEBUG)
				$result.="270-";

			if ($sameFile) {
				$result.= GetNoLinkTemplate("", $nome, $commento, $removeIfSelf);
			} else {
				$result.=GetLinkTemplate($target_url, $nome, $commento, $featured_img_url);
			}
		}
		else
		{
			if( $MY_DEBUG)
				$result.="NON PUBBLICATO: $target_url";
			else
				$result.="<!-- NON PUBBLICATO -->";
		}

		return $result;
	}

	function GetNoLinkTemplate(string $target_url, string $nome, string $commento, string  $featured_img_url): string
	{
		return <<<EOF
<li>
<div class="li-img">
	<img src="$featured_img_url" alt="$nome" />		
</div>
<div class="li-text">$nome ($commento)</div>
</li>\n
EOF;
	}

	function GetLinkTemplate($target_url, $nome, $commento, $featured_img_url): string
	{
		return <<<EOF
<li>
<a href="$target_url">			
<div class="li-img">
	<img src="$featured_img_url" alt="$nome" />		
</div>
<div class="li-text">$nome </div>
</a>$commento</li>\n
EOF;
	}


	add_action( 'init', 'revious_microdata_buttons' );

	function revious_microdata_buttons() {
		add_filter("mce_external_plugins", "revious_microdata_add_buttons");
		add_filter('mce_buttons', 'revious_microdata_register_buttons');
	}

	function revious_microdata_add_buttons($plugin_array) {
		$plugin_array['revious_microdata'] = plugins_url( '/revious-microdata.js', __FILE__ );
		return $plugin_array;
	}

	function revious_microdata_register_buttons($buttons) {
		array_push( $buttons, 'md_telefono_btn', 'boxinfo-menu' );
		array_push( $buttons, 'md_prezzo_btn', 'boxinfo-menu' );
		return $buttons;
	}

//

	function load_css_single_pages() {
		if(is_single())
		{
			$plugin_url = plugin_dir_url( __FILE__ );
			wp_enqueue_style( 'css_single_pages', trailingslashit( $plugin_url ) . 'revious-microdata.css', array(  ) );
		}
		//else if(is_category() || is_tag())
	}



add_action( 'wp_enqueue_scripts', 'load_css_single_pages', 1001 );
add_action('admin_head', 'add_LogRocket');

	//add_filter( 'xmlrpc_enabled', '__return_false' );

