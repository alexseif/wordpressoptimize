<?php
/**
 * Post-Deployment Script for WordPress Optimize
 * Idempotently provisions new pages, syncs templates, and updates content on deploy.
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

// 3. Ensure site icon files exist in uploads/2026/09 to prevent 404s
$uploads_dir = WP_CONTENT_DIR . '/uploads/2026/09';
if (!file_exists($uploads_dir)) {
    wp_mkdir_p($uploads_dir);
}
$source_logo = get_template_directory() . '/assets/img/logo-transparent.png';
if (file_exists($source_logo)) {
    $icons = array(
        'logo-transparent.png',
        'logo-transparent-150x150.png',
        'logo-transparent-300x300.png'
    );
    foreach ($icons as $icon_name) {
        $target_file = $uploads_dir . '/' . $icon_name;
        if (!file_exists($target_file)) {
            copy($source_logo, $target_file);
            WP_CLI::line("Provisioned site icon {$icon_name} in uploads/2026/09");
        }
    }
}

// 4. Update Diagnostic Intake Page (Post ID 55) to friction-free booking form
$diag_page = get_page_by_path('diagnostic-intake');
if ($diag_page) {
    $diag_content = '<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"5rem","bottom":"5rem"}}},"backgroundColor":"primary","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-primary-background-color has-background" style="background-color:var(--wp--preset--color--primary);color:#ffffff;padding-top:5rem;padding-bottom:5rem;text-align:center">
	<div class="wp-block-group" style="max-width:850px;margin:0 auto">
		<p class="hero-pill" style="color:var(--wp--preset--color--speed-emerald);font-size:0.75rem;font-weight:700;letter-spacing:0.08em;margin-bottom:1.25rem;text-transform:uppercase;background:rgba(16,185,129,0.12);padding:0.4rem 1rem;border-radius:9999px;display:inline-block;border:1px solid rgba(16,185,129,0.3)">
			⚡ PERFORMANCE DIAGNOSTIC • 48-HOUR DELIVERY SLA
		</p>
		<h1 class="wp-block-heading" style="color:#ffffff;font-size:clamp(2rem, 5vw, 3rem);font-weight:800;letter-spacing:-0.03em;margin:0 0 1.25rem 0">
			Book Your Performance Diagnostic (€120)
		</h1>
		<p style="color:#94A3B8;font-size:1.15rem;line-height:1.7;margin:0 auto;max-width:700px">
			Submit your site parameters below. We profile your database queries, TTFB latency, and Core Web Vitals bottlenecks within 48 hours. <strong>100% of the €120 fee is credited toward your subsequent build or retainer.</strong>
		</p>
	</div>
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"4rem","bottom":"4rem"}}},"backgroundColor":"background","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-background-background-color has-background" style="padding-top:4rem;padding-bottom:4rem">
	<div class="wp-block-group" style="max-width:850px;margin:0 auto">
		<div class="wp-block-group has-background-alt-background-color has-background" style="border:1px solid var(--wp--preset--color--border-subtle);border-radius:0.85rem;padding:2rem;margin-bottom:3rem">
			<h3 class="wp-block-heading" style="margin-bottom:1.5rem;font-size:1.15rem;font-weight:700">⏱️ What Happens Next:</h3>
			<div style="display:flex;flex-direction:column;gap:1.25rem">
				<div style="display:flex;gap:1rem;align-items:flex-start">
					<span style="background:#2563EB;color:#ffffff;font-weight:800;font-size:0.85rem;width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">1</span>
					<div>
						<p style="font-weight:700;color:var(--wp--preset--color--foreground);font-size:1rem;margin:0 0 0.2rem 0">Enter Your Site Parameters Below</p>
						<p style="color:var(--wp--preset--color--foreground-light);font-size:0.9rem;line-height:1.6;margin:0">Provide your target website URL, monthly traffic, and known speed pain points.</p>
					</div>
				</div>
				<div style="display:flex;gap:1rem;align-items:flex-start">
					<span style="background:#2563EB;color:#ffffff;font-weight:800;font-size:0.85rem;width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">2</span>
					<div>
						<p style="font-weight:700;color:var(--wp--preset--color--foreground);font-size:1rem;margin:0 0 0.2rem 0">Telemetry & SQL Profiling Begins (Within 2 Hours)</p>
						<p style="color:var(--wp--preset--color--foreground-light);font-size:0.9rem;line-height:1.6;margin:0">Alex Seif personally queues your stack for synthetic Core Web Vitals profiling, TTFB probes, and slow SQL query inspection.</p>
					</div>
				</div>
				<div style="display:flex;gap:1rem;align-items:flex-start">
					<span style="background:#2563EB;color:#ffffff;font-weight:800;font-size:0.85rem;width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">3</span>
					<div>
						<p style="font-weight:700;color:var(--wp--preset--color--foreground);font-size:1rem;margin:0 0 0.2rem 0">Actionable Remediation Blueprint & Video Walkthrough (Within 48 Hours)</p>
						<p style="color:var(--wp--preset--color--foreground-light);font-size:0.9rem;line-height:1.6;margin:0">You receive a breakdown of your top bottlenecks, architectural remediation plan, and video explanation.</p>
					</div>
				</div>
			</div>
		</div>

		<div class="wp-block-group has-background-background-color has-background intake-form-container" style="border:1px solid var(--wp--preset--color--border-subtle);border-radius:1rem;padding:2.5rem;box-shadow:0 4px 6px -1px rgba(0,0,0,0.05)">
			<h2 class="wp-block-heading has-text-align-center" style="font-size:1.5rem;font-weight:800;margin-bottom:0.5rem">Enter Your Site Details</h2>
			<p class="has-text-align-center" style="color:var(--wp--preset--color--foreground-light);font-size:0.95rem;margin-bottom:2rem">Complete the brief form below to initiate your diagnostic.</p>
			<!-- wp:shortcode -->[contact-form-7 id="61" title="Diagnostic Kickoff Form"]<!-- /wp:shortcode -->
		</div>
	</div>
</div>
<!-- /wp:group -->';

    wp_update_post(array(
        'ID'           => $diag_page->ID,
        'post_title'   => 'Book Your Performance Diagnostic',
        'post_content' => $diag_content,
    ));
    WP_CLI::line("Updated 'diagnostic-intake' page content (ID: {$diag_page->ID})");
}
