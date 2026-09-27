<?php
/**
 * Post-Deployment Script for WordPress Optimize
 * Executed via `wp eval-file bin/post-deploy.php` in deploy.sh
 * Ensures all forms, legal pages, and critical meta are synchronized and idempotent.
 */

if (!defined('ABSPATH')) {
    exit;
}

echo "--> [Post-Deploy] Synchronizing WordPress Optimize configuration...\n";

// ==============================================================================
// 1. Synchronize Privacy Policy (/privacy-policy)
// ==============================================================================
$privacy_content = <<<HTML
<!-- wp:group {"layout":{"type":"constrained","contentSize":"820px"}} -->
<div class="wp-block-group" style="padding-top:2rem;padding-bottom:4rem">
	<!-- wp:heading {"level":1} -->
	<h1 class="wp-block-heading" style="font-size:2.5rem;font-weight:800;letter-spacing:-0.02em;margin-bottom:1.5rem">Privacy Policy</h1>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"style":{"typography":{"fontSize":"0.9rem"},"color":{"text":"var:preset|color|foreground-light"}}} -->
	<p style="color:var(--wp--preset--color--foreground-light);font-size:0.9rem;margin-bottom:2.5rem">Last Updated: September 27, 2026</p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"level":2} -->
	<h2 class="wp-block-heading">1. Commitment to Privacy &amp; Zero-Cookie Shield</h2>
	<!-- /wp:heading -->
	<!-- wp:paragraph -->
	<p>At WordPress Optimize (accessible from https://wordpressoptimize.com), we believe performance and privacy are inseparable. We operate under a strict <strong>True Zero-Cookie Shield</strong> standard. We do not load marketing trackers, Google Analytics, social widgets, or intrusive advertising beacons. We do not set non-essential cookies on your browser, and no cookie consent banner is required to browse our website.</p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"level":2} -->
	<h2 class="wp-block-heading">2. Merchant of Record &amp; Payment Processing</h2>
	<!-- /wp:heading -->
	<!-- wp:paragraph -->
	<p>Our order process is conducted by our online reseller <strong>Paddle.com</strong>. Paddle.com is the Merchant of Record for all our orders. Paddle provides all customer service inquiries and handles returns. Paddle processes payment data in compliance with PCI-DSS Level 1 standards and international tax/VAT regulations. When you purchase an engineering service through our website, Paddle collects your billing address, email, and payment credentials according to <a href="https://www.paddle.com/legal/privacy" target="_blank" rel="noopener">Paddle's Privacy Policy</a>.</p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"level":2} -->
	<h2 class="wp-block-heading">3. Information We Collect Directly</h2>
	<!-- /wp:heading -->
	<!-- wp:paragraph -->
	<p>When you voluntarily submit an inquiry or diagnostic kickoff through our forms, we collect:</p>
	<!-- /wp:paragraph -->
	<!-- wp:list -->
	<ul>
		<li>Your Name and Business/Entity Name</li>
		<li>Work Email Address and Telephone / WhatsApp number</li>
		<li>Target Website URL or social links for performance benchmarking</li>
		<li>Audit parameters (traffic volume, performance objectives, known speed bottlenecks)</li>
		<li>Paddle Transaction ID (for paid order association)</li>
	</ul>
	<!-- /wp:list -->
	<!-- wp:paragraph -->
	<p>This data is collected solely to evaluate your technical infrastructure, deliver your diagnostic roadmap, communicate project milestones, and fulfill our contractual obligations.</p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"level":2} -->
	<h2 class="wp-block-heading">4. Data Storage &amp; Security</h2>
	<!-- /wp:heading -->
	<!-- wp:paragraph -->
	<p>Your technical inquiries and kickoff parameters are stored securely in our private WordPress database running on an isolated, hardened Linux droplet in Frankfurt, Germany. Access is restricted to authorized engineering personnel via SSH public-key authentication.</p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"level":2} -->
	<h2 class="wp-block-heading">5. Your GDPR Rights</h2>
	<!-- /wp:heading -->
	<!-- wp:paragraph -->
	<p>Under the General Data Protection Regulation (GDPR) and applicable data protection laws, you have the right to request access to your personal data, request rectification of inaccurate records, or request complete erasure of your contact history. To exercise any of these rights, contact us directly at <strong>alex.seif@gmail.com</strong>.</p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
HTML;

$privacy_page = get_page_by_path('privacy-policy');
if ($privacy_page) {
    wp_update_post(array(
        'ID' => $privacy_page->ID,
        'post_title' => 'Privacy Policy',
        'post_content' => $privacy_content,
        'post_status' => 'publish'
    ));
    update_post_meta($privacy_page->ID, '_wp_page_template', 'default');
    echo "  [OK] /privacy-policy page updated & published.\n";
} else {
    $privacy_id = wp_insert_post(array(
        'post_title' => 'Privacy Policy',
        'post_name' => 'privacy-policy',
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_content' => $privacy_content
    ));
    update_post_meta($privacy_id, '_wp_page_template', 'default');
    echo "  [OK] /privacy-policy page created & published (ID {$privacy_id}).\n";
}

// ==============================================================================
// 2. Synchronize Terms & Conditions (/terms)
// ==============================================================================
$terms_content = <<<HTML
<!-- wp:group {"layout":{"type":"constrained","contentSize":"820px"}} -->
<div class="wp-block-group" style="padding-top:2rem;padding-bottom:4rem">
	<!-- wp:heading {"level":1} -->
	<h1 class="wp-block-heading" style="font-size:2.5rem;font-weight:800;letter-spacing:-0.02em;margin-bottom:1.5rem">Terms &amp; Conditions</h1>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"style":{"typography":{"fontSize":"0.9rem"},"color":{"text":"var:preset|color|foreground-light"}}} -->
	<p style="color:var(--wp--preset--color--foreground-light);font-size:0.9rem;margin-bottom:2.5rem">Last Updated: September 27, 2026</p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"level":2} -->
	<h2 class="wp-block-heading">1. Overview &amp; Agreement</h2>
	<!-- /wp:heading -->
	<!-- wp:paragraph -->
	<p>These Terms &amp; Conditions govern all specialized WordPress performance engineering, Core Web Vitals remediation, and architectural retainer services provided by WordPress Optimize ("we", "our", "us") via https://wordpressoptimize.com. By booking a diagnostic or ordering an engineering package, you agree to be bound by these terms.</p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"level":2} -->
	<h2 class="wp-block-heading">2. Service Packages &amp; Deliverables</h2>
	<!-- /wp:heading -->
	<!-- wp:paragraph -->
	<p>We provide four harmonized technical service tiers:</p>
	<!-- /wp:paragraph -->
	<!-- wp:list -->
	<ul>
		<li><strong>Performance Diagnostic (€120)</strong>: Full-stack synthetic and Real User Monitoring (RUM) audit, profiling TTFB, query bottlenecks, and Core Web Vitals. Delivers an actionable architectural remediation roadmap and a 10-minute async video walkthrough within 48 business hours. 100% of this fee is credited toward a subsequent build or retainer booked within 14 days.</li>
		<li><strong>Performance Care Retainer (€240 / month)</strong>: Continuous Core Web Vitals maintenance, 24/7 uptime monitoring, weekly staging regression checks, database bloat defense, and SLA response time.</li>
		<li><strong>Performance Build (from €1,400)</strong>: Bespoke zero-bloat Full Site Editing (FSE) block theme build with sub-200ms TTFB, 100/100 Core Web Vitals target, zero Google Fonts, and WCAG 2.2 AA accessibility.</li>
		<li><strong>E-Commerce Speed Suite (from €2,100)</strong>: High-concurrency WooCommerce overhaul, Redis object cache optimization, non-blocking checkout, and cart fragment remediation.</li>
	</ul>
	<!-- /wp:list -->

	<!-- wp:heading {"level":2} -->
	<h2 class="wp-block-heading">3. Merchant of Record &amp; Billing Terms</h2>
	<!-- /wp:heading -->
	<!-- wp:paragraph -->
	<p>All transactions are processed by <strong>Paddle.com</strong> as Merchant of Record. By placing an order, you agree to <a href="https://www.paddle.com/legal/checkout-buyer-terms" target="_blank" rel="noopener">Paddle's Buyer Terms</a>. Paddle is responsible for handling payment processing, billing disputes, VAT/sales tax calculations, and official invoicing.</p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"level":2} -->
	<h2 class="wp-block-heading">4. Intellectual Property &amp; Code Ownership</h2>
	<!-- /wp:heading -->
	<!-- wp:paragraph -->
	<p>Upon receipt of full payment, you own 100% of all custom theme code, stylesheets, templates, and configuration scripts developed specifically for your project. WordPress Optimize retains no proprietary lock-in over your production code.</p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"level":2} -->
	<h2 class="wp-block-heading">5. Client Cooperation &amp; Staging Access</h2>
	<!-- /wp:heading -->
	<!-- wp:paragraph -->
	<p>To perform architectural profiling or code remediation, you agree to provide timely technical access (e.g., staging credentials, read-only analytics, or hosting panels). We perform all code-level optimizations on staging environments before deploying to production.</p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
HTML;

$terms_page = get_page_by_path('terms');
if ($terms_page) {
    wp_update_post(array(
        'ID' => $terms_page->ID,
        'post_title' => 'Terms & Conditions',
        'post_content' => $terms_content,
        'post_status' => 'publish'
    ));
    update_post_meta($terms_page->ID, '_wp_page_template', 'default');
    echo "  [OK] /terms page updated & published.\n";
} else {
    $terms_id = wp_insert_post(array(
        'post_title' => 'Terms & Conditions',
        'post_name' => 'terms',
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_content' => $terms_content
    ));
    update_post_meta($terms_id, '_wp_page_template', 'default');
    echo "  [OK] /terms page created & published (ID {$terms_id}).\n";
}

// ==============================================================================
// 3. Synchronize Refund & Cancellation Policy (/refund-policy)
// ==============================================================================
$refund_content = <<<HTML
<!-- wp:group {"layout":{"type":"constrained","contentSize":"820px"}} -->
<div class="wp-block-group" style="padding-top:2rem;padding-bottom:4rem">
	<!-- wp:heading {"level":1} -->
	<h1 class="wp-block-heading" style="font-size:2.5rem;font-weight:800;letter-spacing:-0.02em;margin-bottom:1.5rem">Refund &amp; Cancellation Policy</h1>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"style":{"typography":{"fontSize":"0.9rem"},"color":{"text":"var:preset|color|foreground-light"}}} -->
	<p style="color:var(--wp--preset--color--foreground-light);font-size:0.9rem;margin-bottom:2.5rem">Last Updated: September 27, 2026</p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"level":2} -->
	<h2 class="wp-block-heading">1. Performance Diagnostic (€120 One-Time)</h2>
	<!-- /wp:heading -->
	<!-- wp:paragraph -->
	<p><strong>100% Fee Credit:</strong> 100% of your €120 diagnostic fee is credited directly toward any bespoke Performance Build or WooCommerce overhaul contracted within 14 calendar days of roadmap delivery.</p>
	<!-- /wp:paragraph -->
	<!-- wp:paragraph -->
	<p><strong>Refund Eligibility:</strong></p>
	<!-- /wp:paragraph -->
	<!-- wp:list -->
	<ul>
		<li><strong>Before Analysis Begins:</strong> If you request a cancellation before our engineers initiate synthetic profiling (within 2 hours of submitting your kickoff parameters), you are entitled to a 100% full refund with no questions asked.</li>
		<li><strong>After Profiling Commences:</strong> Because the diagnostic involves dedicated, bespoke engineering time and custom telemetry profiling, once analysis has begun, the fee is non-refundable. However, your 100% credit toward subsequent build sprints remains fully active.</li>
	</ul>
	<!-- /wp:list -->

	<!-- wp:heading {"level":2} -->
	<h2 class="wp-block-heading">2. Performance Care Retainers (€240 / Month)</h2>
	<!-- /wp:heading -->
	<!-- wp:paragraph -->
	<p><strong>Cancel Anytime:</strong> You may cancel your monthly Performance Care Retainer at any time with zero penalties or lock-in fees. You can cancel directly through Paddle's self-service customer portal (linked on your invoice receipt) or by emailing alex.seif@gmail.com. Your coverage will remain active through the end of your current prepaid billing period.</p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"level":2} -->
	<h2 class="wp-block-heading">3. Custom Builds &amp; E-Commerce Sprints</h2>
	<!-- /wp:heading -->
	<!-- wp:paragraph -->
	<p>Custom theme builds and WooCommerce speed overhauls are delivered under milestone agreements. Payments for completed and approved milestone deliverables are non-refundable.</p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"level":2} -->
	<h2 class="wp-block-heading">4. How Refunds Are Processed</h2>
	<!-- /wp:heading -->
	<!-- wp:paragraph -->
	<p>All refunds are processed directly by our Merchant of Record, <strong>Paddle.com</strong>, to your original payment method (Credit Card, PayPal, Apple Pay, etc.). Refunds typically appear in your account within 5–10 business days depending on your financial institution.</p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
HTML;

$refund_page = get_page_by_path('refund-policy');
if ($refund_page) {
    wp_update_post(array(
        'ID' => $refund_page->ID,
        'post_title' => 'Refund & Cancellation Policy',
        'post_content' => $refund_content,
        'post_status' => 'publish'
    ));
    update_post_meta($refund_page->ID, '_wp_page_template', 'default');
    echo "  [OK] /refund-policy page updated & published.\n";
} else {
    $refund_id = wp_insert_post(array(
        'post_title' => 'Refund & Cancellation Policy',
        'post_name' => 'refund-policy',
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_content' => $refund_content
    ));
    update_post_meta($refund_id, '_wp_page_template', 'default');
    echo "  [OK] /refund-policy page created & published (ID {$refund_id}).\n";
}

echo "--> [Post-Deploy] Tasks completed successfully.\n";
