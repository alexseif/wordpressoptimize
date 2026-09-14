<?php
/**
 * WPOPT Theme Setup Script
 * 
 * This script sets up the theme by:
 * - Installing Contact Form 7 (if needed)
 * - Creating the intake form
 * - Creating required pages
 * - Assigning templates
 * - Activating the theme
 * 
 * Run via WP-CLI: wp eval-file wp-content/themes/wpopt/setup.php
 * Or access via admin (add to functions.php temporarily)
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( __FILE__ ) . '/../../../wp-load.php';
}

// Check if Contact Form 7 is installed
function wpopt_check_cf7() {
	if ( ! function_exists( 'wpcf7_contact_form' ) ) {
		// Try to install Contact Form 7
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/misc.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		
		$upgrader = new Plugin_Upgrader();
		$result = $upgrader->install( 'https://downloads.wordpress.org/plugin/contact-form-7.latest-stable.zip' );
		
		if ( ! is_wp_error( $result ) ) {
			activate_plugin( 'contact-form-7/wp-contact-form-7.php' );
		}
	}
}

// Create intake form
function wpopt_create_intake_form() {
	if ( ! function_exists( 'wpcf7_save_contact_form' ) ) {
		return false;
	}
	
	// Check if form already exists
	$existing_forms = get_posts( array(
		'post_type' => 'wpcf7_contact_form',
		'posts_per_page' => 1,
		'meta_query' => array(
			array(
				'key' => '_wpopt_intake_form',
				'value' => '1',
			),
		),
	) );
	
	if ( ! empty( $existing_forms ) ) {
		return $existing_forms[0]->ID;
	}
	
	$form_content = '
<div class="wpopt-intake-form">
<p><label> Name (required)<br />
    [text* your-name] </label></p>

<p><label> Business / Clinic name<br />
    [text business-name] </label></p>

<p><label> Website URL OR Instagram link<br />
    [url website-url] </label></p>

<p><label> Phone<br />
    [tel phone] </label></p>

<p><label> Email (required)<br />
    [email* your-email] </label></p>

<p><label> Message<br />
    [textarea message] </label></p>

<p>[submit "Submit"]</p>
</div>
';
	
	$mail = array(
		'subject' => 'New Website Intake Form Submission',
		'sender' => '[your-name] <[your-email]>',
		'body' => 'From: [your-name] <[your-email]>
Business/Clinic: [business-name]
Website/Instagram: [website-url]
Phone: [phone]

Message:
[message]

--
This email was sent from the intake form on your WordPress site.',
		'recipient' => 'info@alexseif.com',
		'additional_headers' => '',
		'attachments' => '',
		'use_html' => 0,
		'exclude_blank' => 0,
	);
	
	$properties = array(
		'form' => $form_content,
		'mail' => $mail,
		'mail_2' => array( 'active' => false ),
		'messages' => array(),
		'additional_settings' => '',
	);
	
	$contact_form = wpcf7_save_contact_form( $properties );
	
	if ( $contact_form instanceof WPCF7_ContactForm ) {
		update_post_meta( $contact_form->id(), '_wpopt_intake_form', '1' );
		return $contact_form->id();
	}
	
	return false;
}

// Create pages
function wpopt_create_pages() {
	$pages = array(
		'home' => array(
			'title' => 'Home',
			'template' => 'front-page',
		),
		'services' => array(
			'title' => 'Services',
			'template' => 'page-services',
		),
		'pricing' => array(
			'title' => 'Pricing',
			'template' => 'page-pricing',
		),
		'case-studies' => array(
			'title' => 'Case Studies',
			'template' => 'page-case-studies',
		),
		'intake' => array(
			'title' => 'Website Intake',
			'template' => 'page-intake',
		),
		'contact' => array(
			'title' => 'Contact',
			'template' => 'page-contact',
		),
		'about' => array(
			'title' => 'About',
			'template' => 'page-about',
		),
	);
	
	$created_pages = array();
	
	foreach ( $pages as $slug => $page_data ) {
		$existing = get_page_by_path( $slug );
		
		if ( ! $existing ) {
			$page_id = wp_insert_post( array(
				'post_title' => $page_data['title'],
				'post_name' => $slug,
				'post_status' => 'publish',
				'post_type' => 'page',
				'post_content' => '',
			) );
			
			if ( $page_id && ! is_wp_error( $page_id ) ) {
				// Assign template
				if ( isset( $page_data['template'] ) ) {
					update_post_meta( $page_id, '_wp_page_template', 'templates/' . $page_data['template'] . '.html' );
				}
				
				$created_pages[ $slug ] = $page_id;
			}
		} else {
			$created_pages[ $slug ] = $existing->ID;
			
			// Update template if needed
			if ( isset( $page_data['template'] ) ) {
				update_post_meta( $existing->ID, '_wp_page_template', 'templates/' . $page_data['template'] . '.html' );
			}
		}
	}
	
	// Set homepage
	if ( isset( $created_pages['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $created_pages['home'] );
	}
	
	return $created_pages;
}

// Update intake form shortcode in template
function wpopt_update_intake_form_shortcode( $form_id ) {
	if ( ! $form_id ) {
		return;
	}
	
	$intake_page = get_page_by_path( 'intake' );
	if ( $intake_page ) {
		$content = '[contact-form-7 id="' . $form_id . '"]';
		wp_update_post( array(
			'ID' => $intake_page->ID,
			'post_content' => $content,
		) );
	}
}

// Activate theme
function wpopt_activate_theme() {
	switch_theme( 'wpopt' );
}

// Run setup
function wpopt_run_setup() {
	echo "Starting WPOPT theme setup...\n";
	
	// Check/install CF7
	echo "Checking Contact Form 7...\n";
	wpopt_check_cf7();
	
	// Create form
	echo "Creating intake form...\n";
	$form_id = wpopt_create_intake_form();
	if ( $form_id ) {
		echo "Intake form created with ID: $form_id\n";
	} else {
		echo "Warning: Could not create intake form. You may need to create it manually.\n";
	}
	
	// Create pages
	echo "Creating pages...\n";
	$pages = wpopt_create_pages();
	echo "Created/updated " . count( $pages ) . " pages.\n";
	
	// Update intake form shortcode
	if ( $form_id ) {
		wpopt_update_intake_form_shortcode( $form_id );
	}
	
	// Activate theme
	echo "Activating wpopt theme...\n";
	wpopt_activate_theme();
	
	echo "Setup complete!\n";
}

// Run if called directly
if ( php_sapi_name() === 'cli' || ( isset( $_GET['wpopt_setup'] ) && current_user_can( 'manage_options' ) ) ) {
	wpopt_run_setup();
}

