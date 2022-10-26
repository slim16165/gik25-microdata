<?php

if (!defined('ABSPATH'))
{
    exit; // Exit if accessed directly.
}

const PLUGIN_NAME_PREFIX = 'md_';

require_once "class/ExcludePostFrom.php";


function IsNullOrEmptyString($str): bool
{
    return (!isset($str) || trim($str) === '');
}

function CheckJsonError(string $json): string
{
    $json_last_error = json_last_error();

    switch ($json_last_error)
    {
        //Nessun errore
        case JSON_ERROR_NONE:
            return "";
            break;

        //varie casistiche di errori
        case JSON_ERROR_DEPTH:
            $errormessage = "Maximum stack depth exceeded";
            break;
        case JSON_ERROR_STATE_MISMATCH:
            $errormessage = "Underflow or the modes mismatch";
            break;
        case JSON_ERROR_CTRL_CHAR:
            $errormessage = "Unexpected control character found";
            break;
        case JSON_ERROR_SYNTAX:
            $errormessage = "Syntax error, malformed JSON";
            break;
        case JSON_ERROR_UTF8:
            $errormessage = "Malformed UTF-8 characters, possibly incorrectly encoded";
            break;
        default:
            $errormessage = "Unknown error";
            break;
    }

//		https://github.com/scrivo/highlight.php


    $errormessage = "<pre>$errormessage<br/>
		[$json]
		<a href='https://codebeautify.org/jsonviewer?input=[$json]'>Validator</a>
		</pre>";
    // Instantiate the Highlighter.
    //$hl = new \Highlight\Highlighter();

//		try {
//			// Highlight some code.
//			$highlighted = $hl->highlight('json', $code);
//
//			echo "<pre><code class=\"hljs {$highlighted->language}\">";
//			echo $highlighted->value;
//			echo "</code></pre>";
//		}
//		catch (DomainException $e) {
//			// This is thrown if the specified language does not exist
//
//			echo "<pre><code>";
//			echo $code;
//			echo "</code></pre>";
//		}

    return $errormessage;
}

function timer()
{
    $starttime = microtime(true);
    /* do stuff here */

    $endtime = microtime(true);
    $timediff = $endtime - $starttime;

    var_dump($timediff); //in seconds
}



//Limit the visibility of some post for specific users
//add_filter('parse_query', 'md_hide_others_roles_posts');
function md_hide_others_roles_posts($query)
{
    global $pagenow;

    //if user is not logged exit
    if (!is_user_logged_in())
        return;

    $limited_users = array(/*'indexo3', */'Gerardatt', 'GiuseppeAmbrosio', 'SaraMarchiano');
    $authors_post_to_hide = array(/*4,*/ 7 /* 'Gianluigi Salvi'*/); //TODO: finish to implement the array version

    $current_user = wp_get_current_user();

    if (!in_array($current_user->nickname, $limited_users))
        return;

    list($users, $author__in) = GetAllUsersButExcluded($authors_post_to_hide);

    if (count($users))
    {
        if ($pagenow == 'edit.php')
        {
            $query->query_vars['author__in'] = $author__in;
        }
    }
}

function GetAllUsersButExcluded(array $authors_post_to_hide): array
{
    $user_args = [
        'fields ' => 'ID',
        'exclude' => $authors_post_to_hide
    ];
    $users = get_users($user_args);

    $author__in = [];
    foreach ($users as $user)
    {
        $author__in[] = $user->ID;
    }
    return array($users, $author__in);
}

function md_scripts_styles()
{
    // wp_register_style('md-admin-fa-styles', plugins_url('/gik25-microdata/assets/css/fontawesome.min.css'), array(), '', 'all');
    // //fontawesome.min
    //wp_register_style('md-admin-fa-styles', plugins_url('/gik25-microdata/assets/css/fontawesome.min.css'), array(), '', 'all');
    //fontawesome.min
    //https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css
    wp_register_style('md-admin-fa-styles', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css');
    wp_enqueue_style('md-admin-fa-styles');

    //wp_register_style('md-admin-fa-styles', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css');
}

add_action('admin_init', 'md_scripts_styles');

add_action('save_post', 'add_permalink_to_posts_table', 100, 2);

function add_permalink_to_posts_table($id, $post)
{
    global $wpdb;
    $permalink_col_exists = false;
    $table = $wpdb->prefix . 'posts';
    // $q = 'DESCRIBE ' . $wpdb->prefix . 'posts';
    $q = 'DESCRIBE ' . $table;
    //$res = $wpdb->query($q);
    $res = $wpdb->get_results($q, 'OBJECT');
    //var_dump($res);exit;
    foreach ($res as $tbl_col)
    {
        if ($tbl_col->Field == 'permalink')
        {
            //var_dump($tbl_col->Field);exit;
            $permalink_col_exists = true;
        }
    }
    // var_dump($wpdb);exit;
    if ($permalink_col_exists)
    {
        //update 'permalink' col
        $post_permalink = get_permalink($post->ID);
        //$table = $wpdb->prefix;
        $data = array(
            'permalink' => $post_permalink
        );
        $where = array(
            'ID' => $post->ID
        );
        // $wpdb->update($table, $data, $where, $format = null, $where_format = null);
        $wpdb->update($table, $data, $where);
    }
}

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