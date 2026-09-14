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
	// Enqueue Inter font from Google Fonts
	wp_enqueue_style(
		'wpopt-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
		array(),
		'1.0.0'
	);

	// Enqueue compiled SCSS styles
	$css_file = get_template_directory() . '/assets/css/style.css';
	if (file_exists($css_file)) {
		wp_enqueue_style(
			'wpopt-styles',
			get_template_directory_uri() . '/assets/css/style.css',
			array('wpopt-fonts'),
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
}
add_action('wp_enqueue_scripts', 'wpopt_enqueue_assets');

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
		echo '<meta name="description" content="High-reliability WordPress care in Egypt. Fast, secure, maintained WordPress sites for clinics, coaches, and SMEs. 20+ years experience.">' . "\n";
		echo '<meta name="keywords" content="WordPress maintenance Egypt, WordPress care, WordPress support, website maintenance, WordPress developer Egypt">' . "\n";
	}
}
add_action('wp_head', 'wpopt_seo_meta');

/**
 * Add JSON-LD Schema
 */
function wpopt_json_ld_schema()
{
	if (is_front_page()) {
		$schema = array(
			'@context' => 'https://schema.org',
			'@type' => 'LocalBusiness',
			'name' => 'WordPress Optimize',
			'description' => 'High-reliability WordPress care and maintenance services in Egypt',
			'url' => home_url(),
			'telephone' => '',
			'address' => array(
				'@type' => 'PostalAddress',
				'addressCountry' => 'EG',
			),
			'founder' => array(
				'@type' => 'Person',
				'name' => 'Alex Seif',
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
		return 'WordPress Optimize – High-Reliability WordPress Care in Egypt';
	}
	return $title;
}
add_filter('document_title', 'wpopt_document_title');
