<?php

namespace include\class\Utility;

class GenericHelper
{
    public static function timer(): void
    {
        $starttime = microtime(true);
        /* do stuff here */

        $endtime = microtime(true);
        $timediff = $endtime - $starttime;

        var_dump($timediff); //in seconds
    }

    function inizializza_console_debug(): void
    {
//// PHP Console autoload
//$dir = dirname( __FILE__ );
//if (preg_match('%^.+?/plugins/%imx', $dir, $regs)) {
//    $pluginsfolder = $regs[0];
//} else {
//    $pluginsfolder = "";
//}
//
//require_once $pluginsfolder . 'wp-php-console/vendor/autoload.php';
//
//// make PC class available in global PHP scope
//if ( ! class_exists( 'PC', false ) ) PhpConsole\Helper::register();
//
//// send $my_var with tag 'my_tag' to the JavaScript console through PHP Console Server Library and PHP Console Chrome Plugin
////PhpConsole\Helper::debug( get_term_link( $term ) , 'tag');
    }
}