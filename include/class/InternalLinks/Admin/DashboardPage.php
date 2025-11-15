<?php
/**
 * Dashboard Page
 *
 * @package gik25microdata\InternalLinks\Admin
 */

namespace gik25microdata\InternalLinks\Admin;

use gik25microdata\InternalLinks\Reports\LinkStats;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Dashboard Page class
 */
class DashboardPage
{
    /**
     * Render dashboard
     *
     * @return void
     */
    public static function render()
    {
        $stats = new LinkStats();
        $site_stats = $stats->getSiteStats();

        include __DIR__ . '/../../../templates/internal-links/dashboard.php';
    }
}

