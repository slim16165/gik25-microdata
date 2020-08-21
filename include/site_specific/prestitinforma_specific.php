<?php ////////////////////






add_shortcode('codicitributo', 'codicitributo_handler');


function codicitributo_handler($atts, $content = null)
{
    $result=<<<EOF
<ul class="nicelist">
 	<li><a href="https://www.prestitiinforma.it/tributi/codici-f24.htm"><strong>Codici e Modello F24</strong></a></li>
 	<li><strong><a href="https://www.prestitiinforma.it/tributi/codice-tributo-piu-usati.htm">Codici Tributo: i più utilizzati</a></strong></li>
 	<li><strong><a href="https://www.prestitiinforma.it/tributi/codice-tributo-6035.htm">Codice tributo 6035</a></strong></li>
 	<li><strong><a href="https://www.prestitiinforma.it/tributi/codice-tributo-6869.htm">Codice tributo 6869</a></strong></li>
 	<li><strong><a href="https://www.prestitiinforma.it/tributi/codice-tributo-2002.htm">Codice tributo 2002</a></strong></li>
 	<li><strong><a href="https://www.prestitiinforma.it/tributi/codice-tributo-109t.htm">Codice tributo 109t</a></strong></li>
 	<li><strong><a href="https://www.prestitiinforma.it/tributi/il-codice-tributo-3812.htm">Codice tributo 3812</a></strong></li>
 	<li><strong><a href="https://www.prestitiinforma.it/tributi/codice-tributo-4034.htm">Codice tributo 4034</a></strong></li>
 	<li><strong><a href="https://www.prestitiinforma.it/tributi/codice-tributo-3850.htm">Codice tributo 3850</a></strong></li>
 	<li><strong><a href="https://www.prestitiinforma.it/tributi/codice-tributo-4001-a-cosa-si-riferisce-e-dove-trova-impiego.htm">Codice tributo 4001</a></strong></li>
 	<li><strong><a href="https://www.prestitiinforma.it/tributi/codice-tributo-2003.htm">Codice tributo 2003</a></strong></li>
 	<li><strong><a href="https://www.prestitiinforma.it/leggi/codice-tributo-9001.htm">Codice tributo 9001</a></strong></li>
 	<li><strong><a href="https://www.prestitiinforma.it/tributi/codice-tributo-3813.htm">Codice tributo 3813</a></strong></li>
 	<li><strong><a href="https://www.prestitiinforma.it/tributi/codice-tributo-1668.htm">Codice tributo 1668</a></strong></li>
 	<li><strong><a href="https://www.prestitiinforma.it/tributi/codice-tributo-3844.htm">Codice tributo 3844</a></strong></li>
 	<li><strong><a href="https://www.prestitiinforma.it/tributi/codice-tributo-1040.htm">Codice tributo 1040</a></strong></li>
 	<li><strong><a href="https://www.prestitiinforma.it/tributi/codice-tributo-1668.htm">Codice tributo 1668</a></strong></li> 	 	
</ul>
EOF;

    return $result;

}

add_filter( 'xmlrpc_enabled', '__return_false' );