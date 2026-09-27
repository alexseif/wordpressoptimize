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

// 2. Ensure Coaching Business Case Study page exists
$case_studies_parent = get_page_by_path('case-studies');
$parent_id = $case_studies_parent ? $case_studies_parent->ID : 0;
$coaching_page = get_page_by_path('case-studies/coaching-businesses');
if (!$coaching_page) {
    $coaching_page_id = wp_insert_post(array(
        'post_title'     => 'High-Traffic Coaching Platform Performance Case Study',
        'post_name'      => 'coaching-businesses',
        'post_status'    => 'publish',
        'post_type'      => 'page',
        'post_parent'    => $parent_id,
        'comment_status' => 'closed',
        'ping_status'    => 'closed',
    ));
    if ($coaching_page_id && !is_wp_error($coaching_page_id)) {
        update_post_meta($coaching_page_id, '_wp_page_template', 'page-case-study-coaching');
        WP_CLI::line("Created 'coaching-businesses' case study page (ID: {$coaching_page_id})");
    }
} else {
    update_post_meta($coaching_page->ID, '_wp_page_template', 'page-case-study-coaching');
    WP_CLI::line("Updated 'coaching-businesses' case study page template (ID: {$coaching_page->ID})");
}

