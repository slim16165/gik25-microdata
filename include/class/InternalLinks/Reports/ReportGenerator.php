<?php
/**
 * Report Generator - Generates various reports
 *
 * @package gik25microdata\InternalLinks\Reports
 */

namespace gik25microdata\InternalLinks\Reports;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Report Generator class
 */
class ReportGenerator
{
    /**
     * Generate link report
     *
     * @param array $filters Filters
     * @return array Report data
     */
    public function generateLinkReport($filters = [])
    {
        global $wpdb;

        $where = ['1=1'];
        $params = [];

        if (!empty($filters['post_type'])) {
            $where[] = "p.post_type = %s";
            $params[] = $filters['post_type'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = "p.post_date >= %s";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "p.post_date <= %s";
            $params[] = $filters['date_to'];
        }

        $where_clause = implode(' AND ', $where);

        $query = "SELECT 
                    l.id,
                    l.source_post_id,
                    l.target_post_id,
                    l.target_url,
                    l.anchor_text,
                    l.link_type,
                    l.click_count,
                    p.post_title as source_title
                  FROM {$wpdb->prefix}gik25_il_links l
                  LEFT JOIN {$wpdb->prefix}posts p ON l.source_post_id = p.ID
                  WHERE {$where_clause}
                  ORDER BY l.created_at DESC
                  LIMIT 1000";

        if (!empty($params)) {
            $query = $wpdb->prepare($query, $params);
        }

        return $wpdb->get_results($query, ARRAY_A);
    }

    /**
     * Generate juice report
     *
     * @param array $filters Filters
     * @return array Report data
     */
    public function generateJuiceReport($filters = [])
    {
        global $wpdb;

        $query = "SELECT 
                    j.id,
                    j.post_id,
                    j.url,
                    j.juice_absolute,
                    j.juice_relative,
                    j.inbound_links,
                    j.outbound_links,
                    p.post_title
                  FROM {$wpdb->prefix}gik25_il_juice j
                  LEFT JOIN {$wpdb->prefix}posts p ON j.post_id = p.ID
                  ORDER BY j.juice_absolute DESC
                  LIMIT 1000";

        return $wpdb->get_results($query, ARRAY_A);
    }

    /**
     * Generate click report
     *
     * @param array $filters Filters
     * @return array Report data
     */
    public function generateClickReport($filters = [])
    {
        global $wpdb;

        $where = ['1=1'];
        $params = [];

        if (!empty($filters['date_from'])) {
            $where[] = "c.clicked_at >= %s";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "c.clicked_at <= %s";
            $params[] = $filters['date_to'];
        }

        $where_clause = implode(' AND ', $where);

        $query = "SELECT 
                    c.id,
                    c.link_id,
                    c.post_id,
                    c.clicked_at,
                    l.target_url,
                    l.anchor_text,
                    p.post_title
                  FROM {$wpdb->prefix}gik25_il_clicks c
                  LEFT JOIN {$wpdb->prefix}gik25_il_links l ON c.link_id = l.id
                  LEFT JOIN {$wpdb->prefix}posts p ON c.post_id = p.ID
                  WHERE {$where_clause}
                  ORDER BY c.clicked_at DESC
                  LIMIT 1000";

        if (!empty($params)) {
            $query = $wpdb->prepare($query, $params);
        }

        return $wpdb->get_results($query, ARRAY_A);
    }
}

