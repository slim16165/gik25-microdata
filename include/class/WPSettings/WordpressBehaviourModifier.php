<?php

namespace gik25microdata\WPSettings;

use function gik25microdata\add_action;

if (!defined('ABSPATH'))
{
    exit; // Exit if accessed directly.
}

//require_once "class/ExcludePostFrom.php";

class WordpressBehaviourModifier{

    public function __construct()
    {
        add_action('admin_init', array(__CLASS__, "admin_init_scripts_styles"));
        add_action('save_post', array(__CLASS__, "add_permalink_to_posts_table"), 100, 2);
    }

    public static function admin_init_scripts_styles(): void
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

    public static function add_permalink_to_posts_table($id, $post): void
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
}