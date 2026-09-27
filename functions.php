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

	// Paddle Billing scripts paused until merchant verification is completed
	/*
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
		filemtime(get_template_directory() . '/assets/js/paddle-checkout.js'),
		array('strategy' => 'defer', 'in_footer' => true)
	);
	*/
}
add_action('wp_enqueue_scripts', 'wpopt_enqueue_assets');

// Disable external Gravatars for 100% True Zero-Cookie Shield compliance
add_filter('pre_get_avatar', '__return_empty_string');

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
		'woocommerce-speed-optimization' => array(
			'title' => 'WooCommerce Speed Optimization',
			'template' => 'page-woocommerce',
		),
		'case-studies/coaching-businesses' => array(
			'title' => 'Coaching Businesses Case Study',
			'template' => 'page-case-study-coaching',
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
	$description = 'WordPress speed optimization and high-concurrency WooCommerce performance engineering. Eliminate bloat, optimize LCP, and follow the proven pathway to 100/100 Core Web Vitals. Serving EU, Egypt, and GCC enterprises.';
	$keywords = 'WordPress speed optimization, WooCommerce speed optimization, Core Web Vitals, improve LCP WordPress, Redis Object Cache, WordPress performance, EU, Egypt, GCC';

	if (is_page('services')) {
		$description = 'Comprehensive WordPress and WooCommerce speed services: Core Web Vitals remediation, Redis Object Cache indexing, database query tuning, and bespoke block theme engineering.';
		$keywords = 'WordPress speed services, WooCommerce speed optimization, Redis cache WordPress, Core Web Vitals audit, database query tuning';
	} elseif (is_page('pricing')) {
		$description = 'Predictable, harmonized pricing for WordPress speed optimization: €120 Performance Diagnostic (100% credited), €240/mo Care Retainer, and bespoke WooCommerce overhauls.';
		$keywords = 'WordPress optimization pricing, WooCommerce speed pricing, WordPress retainer, Core Web Vitals cost, performance diagnostic';
	} elseif (is_page('case-studies')) {
		$description = 'Empirical WordPress and WooCommerce speed results across Medical Clinics, Coaching Businesses, High-Concurrency Stores, and Global NGO portals.';
		$keywords = 'WordPress case studies, WooCommerce speed results, NGO website speed, clinic website optimization, coaching business website';
	} elseif (is_page('contact')) {
		$description = 'Consult with Principal Systems Engineer Alex Seif. Inquire about bespoke WordPress speed engineering, performance diagnostics, and monthly retainers.';
		$keywords = 'contact WordPress engineer, WordPress speed consultation, WooCommerce performance architect';
	} elseif (is_page('diagnostic-intake')) {
		$description = 'Submit your target URL and technical parameters to initiate your WordPress Performance Diagnostic audit.';
		$keywords = 'WordPress diagnostic kickoff, performance intake, speed audit submission';
	} elseif (is_page('privacy-policy')) {
		$description = 'WordPress Optimize Privacy Policy. Strict True Zero-Cookie Shield standard, zero third-party tracking cookies, and Paddle Merchant of Record compliance.';
		$keywords = 'privacy policy, zero cookie WordPress, GDPR compliant speed, Paddle merchant of record';
	} elseif (is_page('terms')) {
		$description = 'WordPress Optimize Terms and Conditions. Technical deliverables, 48-hour diagnostic SLA, Paddle buyer terms, and client code ownership.';
		$keywords = 'terms and conditions, WordPress service agreement, performance diagnostic SLA';
	} elseif (is_page('refund-policy')) {
		$description = 'WordPress Optimize Refund & Cancellation Policy. 100% Diagnostic fee credit toward full builds, pre-analysis refunds, and cancel-anytime retainers.';
		$keywords = 'refund policy, cancellation terms, diagnostic fee credit, WordPress retainer cancellation';
	} elseif (is_page('woocommerce-speed-optimization')) {
		$description = 'Turnkey WooCommerce speed optimization. Eliminate cart fragment lag, resolve high-concurrency checkout freezes, and configure Redis Object Cache for sub-second checkouts.';
		$keywords = 'woocommerce speed optimization, woocommerce performance optimization, woocommerce cart fragments, high concurrency woocommerce, redis object cache woocommerce, woocommerce slow checkout';
	} elseif (is_page('coaching-businesses')) {
		$description = 'How we achieved sub-second Core Web Vitals for an executive coaching & membership platform: -89% TTFB, zero CLS video embeds, and +41% consultation booking conversion.';
		$keywords = 'coaching business website, coach business website speed, learndash speed optimization, membership site speed wordpress, wordpress video funnels speed';
	}

	$title = wp_get_document_title();
	$canonical = is_singular() ? get_permalink() : home_url('/');

	echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
	echo '<meta name="keywords" content="' . esc_attr($keywords) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
	echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
	echo '<meta property="og:url" content="' . esc_url($canonical) . '">' . "\n";
	echo '<meta property="og:site_name" content="WordPress Optimize">' . "\n";
	echo '<meta property="og:type" content="website">' . "\n";
	echo '<meta property="og:locale" content="en_US">' . "\n";
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
	echo '<meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
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
			'description' => 'Elite WordPress speed optimization, Core Web Vitals remediation, Redis Object Caching, and high-concurrency WooCommerce performance engineering.',
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
				'name' => 'WordPress Speed & Performance Optimization Services',
				'itemListElement' => array(
					array(
						'@type' => 'Offer',
						'name' => 'WordPress Speed Optimization & Performance Diagnostic',
						'price' => '120',
						'priceCurrency' => 'EUR',
						'description' => 'Full SQL profiler audit and Core Web Vitals diagnostic, 100% credited toward build.',
					),
					array(
						'@type' => 'Offer',
						'name' => 'WordPress Performance Care Retainer',
						'price' => '240',
						'priceCurrency' => 'EUR',
						'description' => 'Monthly 24/7 speed, uptime, and security management with priority SLA.',
					),
					array(
						'@type' => 'Offer',
						'name' => 'Bespoke WordPress Performance Build',
						'price' => '1400',
						'priceCurrency' => 'EUR',
						'description' => 'Bespoke zero-bloat FSE block theme with WCAG 2.2 AA accessibility and GDPR compliance.',
					),
					array(
						'@type' => 'Offer',
						'name' => 'WooCommerce Speed Optimization & Concurrency Suite',
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
		return 'WordPress Speed Optimization: The Pathway to 100/100 Web Vitals | WordPress Optimize';
	} elseif (is_page('services')) {
		return 'WordPress Speed & WooCommerce Performance Services | WordPress Optimize';
	} elseif (is_page('pricing')) {
		return 'Transparent Pricing: WordPress Speed & Performance Plans | WordPress Optimize';
	} elseif (is_page('case-studies')) {
		return 'WordPress & WooCommerce Speed Case Studies | WordPress Optimize';
	} elseif (is_page('contact')) {
		return 'Consult a Senior WordPress Performance Architect | WordPress Optimize';
	} elseif (is_page('diagnostic-intake')) {
		return 'Diagnostic Kickoff & Intake | WordPress Optimize';
	} elseif (is_page('privacy-policy')) {
		return 'Privacy Policy: True Zero-Cookie Architecture | WordPress Optimize';
	} elseif (is_page('terms')) {
		return 'Terms & Conditions | WordPress Optimize';
	} elseif (is_page('refund-policy')) {
		return 'Refund & Cancellation Policy | WordPress Optimize';
	} elseif (is_page('woocommerce-speed-optimization')) {
		return 'WooCommerce Speed Optimization: High-Concurrency Engineering | WordPress Optimize';
	} elseif (is_page('coaching-businesses')) {
		return 'Case Study: High-Traffic Coaching Platform Performance | WordPress Optimize';
	}
	return $title;
}
add_filter('document_title', 'wpopt_document_title');

