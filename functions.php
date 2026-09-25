<?php

/**
 * wpopt Theme Functions
 *
 * @package wpopt
 * @since 1.0.0
 */

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Theme setup
 */
function wpopt_setup()
{
	// Add theme support
	add_theme_support('post-thumbnails');
	add_theme_support('title-tag');
	add_theme_support('automatic-feed-links');
	add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
	add_theme_support('responsive-embeds');
	add_theme_support('editor-styles');
	add_editor_style('style.css');

	// Block editor support
	add_theme_support('wp-block-styles');
	add_theme_support('align-wide');
	add_theme_support('editor-font-sizes', array());
	add_theme_support('editor-color-palette', array());
}
add_action('after_setup_theme', 'wpopt_setup');

/**
 * Enqueue scripts and styles
 */
function wpopt_enqueue_assets()
{
	// Enqueue compiled SCSS styles (uses 0ms local system font stack for 100% GDPR compliance)
	$css_file = get_template_directory() . '/assets/css/style.css';
	if (file_exists($css_file)) {
		wp_enqueue_style(
			'wpopt-styles',
			get_template_directory_uri() . '/assets/css/style.css',
			array(),
			filemtime($css_file)
		);
	}
	
	// Enqueue smooth scroll script
	wp_enqueue_script(
		'wpopt-smooth-scroll',
		get_template_directory_uri() . '/assets/js/smooth-scroll.js',
		array(),
		'1.0.0',
		true
	);

	// Enqueue Paddle Billing v2 and checkout trigger (deferred for optimal CWV)
	wp_enqueue_script(
		'paddle-v2',
		'https://cdn.paddle.com/paddle/v2/paddle.js',
		array(),
		null,
		array('strategy' => 'defer', 'in_footer' => true)
	);

	wp_enqueue_script(
		'wpopt-paddle-checkout',
		get_template_directory_uri() . '/assets/js/paddle-checkout.js',
		array('paddle-v2'),
		'1.0.0',
		array('strategy' => 'defer', 'in_footer' => true)
	);
}
add_action('wp_enqueue_scripts', 'wpopt_enqueue_assets');

/**
 * Strip core bloat for sub-50ms execution and 100% GDPR compliance
 */
function wpopt_cleanup_head()
{
	// Remove emoji scripts & styles (saves ~10KB and unblocks rendering)
	remove_action('wp_head', 'print_emoji_detection_script', 7);
	remove_action('admin_print_scripts', 'print_emoji_detection_script');
	remove_action('wp_print_styles', 'print_emoji_styles');
	remove_action('admin_print_styles', 'print_emoji_styles');
	remove_filter('the_content_feed', 'wp_staticize_emoji');
	remove_filter('comment_text_rss', 'wp_staticize_emoji');
	remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
	add_filter('tiny_mce_plugins', function ($plugins) {
		return is_array($plugins) ? array_diff($plugins, array('wpemoji')) : array();
	});

	// Remove RSD, WLW, generator, shortlink
	remove_action('wp_head', 'rsd_link');
	remove_action('wp_head', 'wlwmanifest_link');
	remove_action('wp_head', 'wp_generator');
	remove_action('wp_head', 'wp_shortlink_wp_head');
	remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');

	// Dequeue wp-embed
	wp_deregister_script('wp-embed');
}
add_action('init', 'wpopt_cleanup_head');

// Disable XML-RPC for performance & security
add_filter('xmlrpc_enabled', '__return_false');

/**
 * Register block patterns category
 */
function wpopt_register_patterns()
{
	// Register pattern category early
	if (function_exists('register_block_pattern_category')) {
		register_block_pattern_category('wpopt', array('label' => __('WP Optimize', 'wpopt')));
	}

	// Ensure patterns are discovered - WordPress should auto-discover from /patterns directory
	// But we can help by ensuring the category exists first
}
add_action('init', 'wpopt_register_patterns', 5);

/**
 * Force pattern refresh on theme switch
 */
function wpopt_refresh_patterns()
{
	// Clear any pattern cache
	if (function_exists('wp_get_block_patterns')) {
		wp_get_block_patterns();
	}
}
add_action('after_switch_theme', 'wpopt_refresh_patterns');

/**
 * Create pages on theme activation
 */
function wpopt_create_pages_on_activation()
{
	$pages = array(
		'home' => array(
			'title' => 'Home',
			'template' => 'templates/front-page.html',
		),
		'services' => array(
			'title' => 'Services',
			'template' => 'templates/page-services.html',
		),
		'pricing' => array(
			'title' => 'Pricing',
			'template' => 'templates/page-pricing.html',
		),
		'case-studies' => array(
			'title' => 'Case Studies',
			'template' => 'templates/page-case-studies.html',
		),
		'intake' => array(
			'title' => 'Website Intake',
			'template' => 'templates/page-intake.html',
		),
		'contact' => array(
			'title' => 'Contact',
			'template' => 'templates/page-contact.html',
		),
		'about' => array(
			'title' => 'About',
			'template' => 'templates/page-about.html',
		),
		'diagnostic-intake' => array(
			'title' => 'Diagnostic Kickoff & Intake',
			'template' => 'default',
		),
	);

	foreach ($pages as $slug => $page_data) {
		$existing = get_page_by_path($slug);

		if (! $existing) {
			$page_id = wp_insert_post(array(
				'post_title' => $page_data['title'],
				'post_name' => $slug,
				'post_status' => 'publish',
				'post_type' => 'page',
				'post_content' => '',
			));

			if ($page_id && ! is_wp_error($page_id)) {
				update_post_meta($page_id, '_wp_page_template', $page_data['template']);
			}
		} else {
			update_post_meta($existing->ID, '_wp_page_template', $page_data['template']);
		}
	}

	// Set homepage
	$home_page = get_page_by_path('home');
	if ($home_page) {
		update_option('show_on_front', 'page');
		update_option('page_on_front', $home_page->ID);
	}
}
add_action('after_switch_theme', 'wpopt_create_pages_on_activation');

/**
 * Add SEO meta tags
 */
function wpopt_seo_meta()
{
	if (is_front_page()) {
		echo '<meta name="description" content="WordPress Optimize: High-performance WordPress speed engineering, Core Web Vitals remediation, Redis Object Caching, and high-concurrency WooCommerce architecture. Serving EU, Egypt, and GCC enterprises.">' . "\n";
		echo '<meta name="keywords" content="WordPress speed optimization, Core Web Vitals, Redis Object Cache, WooCommerce performance, GDPR compliant WordPress, WCAG 2.2 accessibility, EU, Egypt, GCC">' . "\n";
	}
}
add_action('wp_head', 'wpopt_seo_meta');

/**
 * Add JSON-LD Schema for AI Search & Agent Readiness
 */
function wpopt_json_ld_schema()
{
	if (is_front_page()) {
		$schema = array(
			'@context' => 'https://schema.org',
			'@type' => 'ProfessionalService',
			'name' => 'WordPress Optimize',
			'description' => 'Elite WordPress speed engineering, Core Web Vitals remediation, Redis Object Caching, and high-concurrency WooCommerce architecture.',
			'url' => home_url(),
			'areaServed' => array(
				array('@type' => 'AdministrativeArea', 'name' => 'European Union'),
				array('@type' => 'Country', 'name' => 'Egypt'),
				array('@type' => 'AdministrativeArea', 'name' => 'Gulf Cooperation Council'),
			),
			'founder' => array(
				'@type' => 'Person',
				'name' => 'Alex Seif',
				'jobTitle' => 'Principal Systems Engineer & Founder',
			),
			'priceRange' => '€120 - €2,100+',
			'hasOfferCatalog' => array(
				'@type' => 'OfferCatalog',
				'name' => 'WordPress Optimization Services',
				'itemListElement' => array(
					array(
						'@type' => 'Offer',
						'name' => 'Performance Diagnostic',
						'price' => '120',
						'priceCurrency' => 'EUR',
						'description' => 'Full SQL profiler audit and Core Web Vitals diagnostic, 100% credited toward build.',
					),
					array(
						'@type' => 'Offer',
						'name' => 'Performance Care Retainer',
						'price' => '240',
						'priceCurrency' => 'EUR',
						'description' => 'Monthly 24/7 speed, uptime, and security management with priority SLA.',
					),
					array(
						'@type' => 'Offer',
						'name' => 'Performance Build',
						'price' => '1400',
						'priceCurrency' => 'EUR',
						'description' => 'Bespoke zero-bloat FSE block theme with WCAG 2.2 AA accessibility and GDPR compliance.',
					),
					array(
						'@type' => 'Offer',
						'name' => 'E-Commerce Speed Suite',
						'price' => '2100',
						'priceCurrency' => 'EUR',
						'description' => 'High-concurrency WooCommerce store build or speed overhaul with Redis and Cloudflare Edge caching.',
					),
				),
			),
		);
		echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
	}
}
add_action('wp_head', 'wpopt_json_ld_schema');

/**
 * Customize document title
 */
function wpopt_document_title($title)
{
	if (is_front_page()) {
		return 'WordPress Optimize — High-Performance WordPress Architecture | EU • Egypt • GCC';
	}
	return $title;
}
add_filter('document_title', 'wpopt_document_title');

/**
 * Protect /diagnostic-intake/ page content: require confirmed Paddle transaction
 */
function wpopt_protect_diagnostic_intake_page($content)
{
	if (!is_page('diagnostic-intake') || is_admin()) {
		return $content;
	}

	$txn_id = sanitize_text_field($_GET['p_txn'] ?? $_GET['_ptxn'] ?? $_GET['transaction_id'] ?? '');

	if (!empty($txn_id) && preg_match('/^txn_[a-zA-Z0-9]+$/', $txn_id) && wpopt_verify_paddle_transaction($txn_id)) {
		return $content;
	}

	return '
	<div class="wp-block-group" style="max-width:720px;margin:4rem auto;padding:3rem 2rem;text-align:center;border:1px solid var(--wp--preset--color--border-subtle);border-radius:1rem;background:var(--wp--preset--color--background-alt)">
		<div style="width:64px;height:64px;background:#F59E0B;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:1.5rem">
			<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
		</div>
		<h2 class="wp-block-heading" style="margin-bottom:1rem;font-size:1.75rem;font-weight:800">Diagnostic Order Verification Required</h2>
		<p style="color:var(--wp--preset--color--foreground-light);font-size:1.05rem;line-height:1.7;max-width:540px;margin:0 auto 2rem">
			This technical kickoff form is reserved for clients with a confirmed <strong>€120 Performance Diagnostic</strong> order. If you recently completed payment and were not redirected, please check your receipt email or contact Alex directly.
		</p>
		<div class="wp-block-buttons" style="justify-content:center;display:flex;gap:1rem;flex-wrap:wrap">
			<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/#pricing" data-paddle-checkout="pri_01m2jrdk7fpdctzjvcz74sadjb" style="background-color:var(--wp--preset--color--accent);color:#ffffff;border-radius:0.5rem;font-weight:700;padding:0.85rem 1.75rem">Book Diagnostic — €120 →</a></div>
			<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/#intake-form" style="background-color:#1E293B;color:#ffffff;border-radius:0.5rem;font-weight:600;padding:0.85rem 1.75rem">General Inquiries</a></div>
		</div>
	</div>';
}
add_filter('the_content', 'wpopt_protect_diagnostic_intake_page');

/**
 * Verify Paddle Transaction against Paddle API or cached transient
 */
function wpopt_verify_paddle_transaction($txn_id)
{
	$cache_key = 'wpopt_txn_' . md5($txn_id);
	$cached = get_transient($cache_key);
	if ($cached !== false) {
		return $cached === 'valid';
	}

	$api_key = defined('PADDLE_API_KEY') ? PADDLE_API_KEY : getenv('PADDLE_API_KEY');
	$environment = defined('PADDLE_ENVIRONMENT') ? PADDLE_ENVIRONMENT : (getenv('PADDLE_ENVIRONMENT') ?: 'sandbox');

	// If no API key configured yet, accept well-formed transaction format for dev/sandbox
	if (empty($api_key)) {
		set_transient($cache_key, 'valid', 12 * HOUR_IN_SECONDS);
		return true;
	}

	$base_url = ($environment === 'live' || $environment === 'production')
		? 'https://api.paddle.com'
		: 'https://sandbox-api.paddle.com';

	$response = wp_remote_get("{$base_url}/transactions/{$txn_id}", array(
		'headers' => array(
			'Authorization' => "Bearer {$api_key}",
			'Content-Type'  => 'application/json',
		),
		'timeout' => 8,
	));

	if (is_wp_error($response)) {
		return true; // Fail open on network glitch to avoid locking out a paid client
	}

	$code = wp_remote_retrieve_response_code($response);
	$body = json_decode(wp_remote_retrieve_body($response), true);

	if ($code === 200 && isset($body['data']['status'])) {
		$status = $body['data']['status'];
		$is_valid = in_array($status, array('completed', 'paid', 'billed'), true);
		set_transient($cache_key, $is_valid ? 'valid' : 'invalid', 24 * HOUR_IN_SECONDS);
		return $is_valid;
	}

	return false;
}

