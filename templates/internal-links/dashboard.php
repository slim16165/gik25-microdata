<?php
/**
 * Dashboard Template
 *
 * @var array $stats Statistics
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap gik25-il-admin">
    <h1><?php esc_html_e('Internal Links Dashboard', 'gik25-microdata'); ?></h1>

    <div class="dashboard-stats">
        <div class="stat-card">
            <h3><?php esc_html_e('Total Links', 'gik25-microdata'); ?></h3>
            <div class="stat-value"><?php echo esc_html($stats['total_links'] ?? 0); ?></div>
        </div>
        <div class="stat-card">
            <h3><?php esc_html_e('Autolinks', 'gik25-microdata'); ?></h3>
            <div class="stat-value"><?php echo esc_html($stats['total_autolinks'] ?? 0); ?></div>
        </div>
        <div class="stat-card">
            <h3><?php esc_html_e('Posts with Links', 'gik25-microdata'); ?></h3>
            <div class="stat-value"><?php echo esc_html($stats['total_posts'] ?? 0); ?></div>
        </div>
        <div class="stat-card">
            <h3><?php esc_html_e('Autolink Rules', 'gik25-microdata'); ?></h3>
            <div class="stat-value"><?php echo esc_html($stats['total_autolink_rules'] ?? 0); ?></div>
        </div>
    </div>
</div>

