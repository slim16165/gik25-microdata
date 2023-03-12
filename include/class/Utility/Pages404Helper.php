<?php
namespace gik25microdata\Utility;

if (!defined('ABSPATH'))
{
    exit; // Exit if accessed directly.
}

class Pages404Helper
{
    public function __construct()
    {
        if(is_admin() || wp_doing_ajax())
            return;
        
        add_filter('pre_handle_404', array(__CLASS__, 'change_404_headers'), 2, 99);
        add_filter('wp_redirect_status', array(__CLASS__, 'cache_control_handle_redirects'), 10, 2);
    }

    /**
     * Force cache headers on 404 pages and prevent WordPress from handling 404s.
     *
     * @param bool $preempt determines who handles 404s.
     * @param obj $wp_query global query object.
     */
    static function change_404_headers($preempt, $wp_query)
    {
        //se non è nel backend e non è una query finalizzata al robots.txt
        if (!is_admin() && !is_robots() && count($wp_query->posts) < 1)
        {
//            echo "is 404? → ";
//            var_dump( is_404() );
//            echo "PLUGIN ATTIVO";
            header('Cache-Control: max-age=300000');
            header('Cache-Control: immutable');
            header('Expires: ' . gmdate('D, d M Y H:i:s', strtotime('+50000 hours')) . ' GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', strtotime('-5000 hours')) . ' GMT');
            //$wp_query->set_404();
            //status_header( 404 );
            // prevents the default 404 from firing.
            //exit;
            return true;
        }
        return $preempt;
    }

    static function cache_control_handle_redirects($status, $location)
    {
        if ($status == 301 || $status == 308)
        {
            session_cache_limiter('public'); //Aim at 'public'
            //header("Vary: User-Agent, Accept-Encoding"); 
            //header ( "Cache-Control: no-cache, no-store, must-revalidate" );
        }

        return $status;
    }


}

new Pages404Helper();