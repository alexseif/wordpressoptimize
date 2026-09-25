<?php
/**
 * Post-Deployment Script for WordPress Optimize
 * Executed via `wp eval-file bin/post-deploy.php` in deploy.sh
 * Ensures all forms, pages, and critical meta are synchronized and idempotent.
 */

if (!defined('ABSPATH')) {
    exit;
}

echo "--> [Post-Deploy] Synchronizing Contact Form 7 configurations...\n";

// 1. Synchronize Client Intake Form (Form 13)
$client_form_content = <<<FORM
<div class="wpopt-intake-form">
<p><label> Name (required)<br />
    [text* your-name] </label></p>

<p><label> Business / Entity name<br />
    [text business-name] </label></p>

<p><label> Website URL OR Instagram link<br />
    [url website-url] </label></p>

<p><label> Phone<br />
    [tel phone] </label></p>

<p><label> Email (required)<br />
    [email* your-email] </label></p>

<p><label> Message<br />
    [textarea message] </label></p>

<p>[submit "Submit Inquiry"]</p>
</div>
FORM;

$client_mail = array(
    'active' => true,
    'subject' => '[WordPress Optimize] Client Inquiry from [your-name]',
    'sender' => 'WordPress Optimize <wordpress@wordpressoptimize.com>',
    'recipient' => 'alex.seif@gmail.com',
    'body' => "New Client Inquiry from WordPress Optimize:\n\nClient Name: [your-name]\nEmail: [your-email]\nPhone: [phone]\nBusiness / Entity: [business-name]\nWebsite / Instagram: [website-url]\n\nMessage / Inquiry:\n[message]\n\n--\nSubmitted via https://wordpressoptimize.com",
    'additional_headers' => "Reply-To: [your-email]",
    'attachments' => '',
    'use_html' => false,
    'exclude_blank' => true
);

$form_13 = get_post(13);
if ($form_13 && $form_13->post_type === 'wpcf7_contact_form') {
    wp_update_post(array(
        'ID' => 13,
        'post_title' => 'Client Intake Form',
        'post_content' => $client_form_content
    ));
    update_post_meta(13, '_form', $client_form_content);
    update_post_meta(13, '_mail', $client_mail);
    echo "  [OK] Form 13 (Client Intake Form) verified & synchronized.\n";
} else {
    echo "  [!] Form 13 not found as wpcf7_contact_form.\n";
}

// 2. Synchronize Diagnostic Kickoff Form (Post-Payment Form)
$diag_form_content = <<<FORM
<div class="wpopt-intake-form">
<p><label> Your Name (required)<br />
    [text* your-name] </label></p>

<p><label> Delivery Email (required)<br />
    [email* your-email] </label></p>

<p><label> Phone / WhatsApp (optional)<br />
    [tel phone] </label></p>

<p><label> Target Website URL to Audit (required)<br />
    [url* website-url placeholder "https://yourwebsite.com"] </label></p>

<p><label> Estimated Monthly Traffic (required)<br />
    [select* traffic-volume "Under 50,000 visits / month" "50,000 – 250,000 visits / month" "250,000 – 1,000,000 visits / month" "1,000,000+ visits / month" "Pre-launch / Staging Site"] </label></p>

<p><label> Primary Performance Objective (required)<br />
    [select* performance-objective "100/100 Core Web Vitals (sub-200ms TTFB / INP)" "WooCommerce Checkout & Cart Performance" "Server Bottlenecks & Redis Caching" "Full Architectural Speed Audit" "Other / Unsure"] </label></p>

<p><label> Known Bottlenecks, Hosting Stack, or Symptoms (optional)<br />
    [textarea message placeholder "e.g. Hosting on Kinsta/DigitalOcean, checkout is sluggish, mobile LCP is high..."] </label></p>

[hidden p_txn default:get]

<p>[submit "Initiate Diagnostic Audit →"]</p>
</div>
FORM;

$diag_mail = array(
    'active' => true,
    'subject' => '[WordPress Optimize] Paid Diagnostic Intake: [website-url] from [your-name]',
    'sender' => 'WordPress Optimize <wordpress@wordpressoptimize.com>',
    'recipient' => 'alex.seif@gmail.com',
    'body' => "Paid Performance Diagnostic Kickoff Parameters:\n\nClient Name: [your-name]\nDelivery Email: [your-email]\nPhone / WhatsApp: [phone]\nTarget Website to Audit: [website-url]\nEstimated Monthly Traffic: [traffic-volume]\nPrimary Objective: [performance-objective]\nPaddle Transaction ID: [p_txn]\n\nKnown Bottlenecks & Context:\n[message]\n\n--\nSubmitted via https://wordpressoptimize.com/diagnostic-intake/",
    'additional_headers' => "Reply-To: [your-email]",
    'attachments' => '',
    'use_html' => false,
    'exclude_blank' => true
);

$diag_messages = array(
    'mail_sent_ok' => 'Thank you! Your diagnostic parameters have been received. Profiling will begin shortly.',
    'mail_sent_ng' => 'There was an error submitting your diagnostic parameters. Please try again or email alex.seif@gmail.com directly.',
    'validation_error' => 'Please review the highlighted fields and try again.'
);

$matching_forms = get_posts(array(
    'post_type' => 'wpcf7_contact_form',
    'title' => 'Diagnostic Kickoff Form',
    'post_status' => 'any',
    'numberposts' => 1
));
$existing_diag_form = !empty($matching_forms) ? $matching_forms[0] : null;
if ($existing_diag_form) {
    $diag_form_id = $existing_diag_form->ID;
    wp_update_post(array(
        'ID' => $diag_form_id,
        'post_content' => $diag_form_content
    ));
} else {
    $diag_form_id = wp_insert_post(array(
        'post_title' => 'Diagnostic Kickoff Form',
        'post_type' => 'wpcf7_contact_form',
        'post_status' => 'publish',
        'post_content' => $diag_form_content
    ));
}

if ($diag_form_id && !is_wp_error($diag_form_id)) {
    update_post_meta($diag_form_id, '_form', $diag_form_content);
    update_post_meta($diag_form_id, '_mail', $diag_mail);
    update_post_meta($diag_form_id, '_messages', $diag_messages);
    echo "  [OK] Diagnostic Kickoff Form (ID {$diag_form_id}) synchronized.\n";
} else {
    echo "  [!] Error provisioning Diagnostic Kickoff Form.\n";
}

// 3. Synchronize /diagnostic-intake page
$diag_page = get_page_by_path('diagnostic-intake');
if ($diag_page && $diag_form_id) {
    update_post_meta($diag_page->ID, '_wp_page_template', 'default');
    
    // Replace any CF7 shortcode with the dedicated Diagnostic Form ID
    $updated_content = preg_replace(
        '/\[contact-form-7 id="[0-9]+"[^\]]*\]/',
        '[contact-form-7 id="' . $diag_form_id . '" title="Diagnostic Kickoff Form"]',
        $diag_page->post_content
    );
    
    if ($updated_content !== $diag_page->post_content) {
        wp_update_post(array(
            'ID' => $diag_page->ID,
            'post_content' => $updated_content
        ));
        echo "  [OK] /diagnostic-intake page updated to embed Form ID {$diag_form_id}.\n";
    } else {
        echo "  [OK] /diagnostic-intake page already references Form ID {$diag_form_id}.\n";
    }
}

echo "--> [Post-Deploy] Tasks completed successfully.\n";
