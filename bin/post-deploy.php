<?php
/**
 * Post-Deployment Script for WordPress Optimize
 * Idempotently provisions new pages and syncs templates on deploy.
 */

if (!defined('ABSPATH')) {
    exit;
}

// 1. Ensure WooCommerce Speed Optimization page exists
$woo_page = get_page_by_path('woocommerce-speed-optimization');
if (!$woo_page) {
    $woo_page_id = wp_insert_post(array(
        'post_title'     => 'WooCommerce Speed Optimization',
        'post_name'      => 'woocommerce-speed-optimization',
        'post_status'    => 'publish',
        'post_type'      => 'page',
        'comment_status' => 'closed',
        'ping_status'    => 'closed',
    ));
    if ($woo_page_id && !is_wp_error($woo_page_id)) {
        update_post_meta($woo_page_id, '_wp_page_template', 'page-woocommerce');
        WP_CLI::line("Created 'woocommerce-speed-optimization' page (ID: {$woo_page_id})");
    }
} else {
    update_post_meta($woo_page->ID, '_wp_page_template', 'page-woocommerce');
    WP_CLI::line("Updated 'woocommerce-speed-optimization' page template (ID: {$woo_page->ID})");
}
