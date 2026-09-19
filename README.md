# WPOPT Theme

WordPress Optimize Theme (wpopt) is a bespoke, zero-bloat Full Site Editing (FSE) block theme engineered for WordPress speed optimization and Core Web Vitals excellence.

## Theme Highlights
- Full Site Editing (FSE): Native Block Editor templates and parts; zero heavy visual builder dependencies.
- 0ms Local Font Stack: Zero Google Fonts or external CDN calls for 100% GDPR compliance.
- True Zero-Cookie Architecture: No third-party tracking scripts or cookie banners needed.
- WCAG 2.2 AA Accessibility: High-contrast tokens, accessible skip links, and ARIA landmarks.
- Telemetry Benchmark Card: Live hero architecture telemetry metrics display.
- Animated Layered Architecture: Responsive SVG with autonomous CSS keyframes showcasing full-stack optimization.

## Harmonized Service Packages
- Performance Diagnostic: €120 (One-time, 100% credited toward build)
- Performance Care Retainer: €240 / month
- Performance Build: Starting at €1,400 (One-time)
- E-Commerce Speed Suite: Starting at €2,100 (One-time)

## Client Intake Form
- Engine: Contact Form 7 (Form ID 13).
- Fields: Name, Business / Entity name, Website URL or Instagram, Phone, Email, Message.
- Notification Recipient: Configured to deliver to `alex.seif@gmail.com` with `Reply-To: [customer-email]`.
- Database Backup: Submissions stored via Flamingo (`flamingo_inbound`).
- Zero Email Leaks: No public `mailto:` links on the frontend.

## Styling & SCSS Architecture
Styles are authored in modular SCSS under `assets/scss/` and compiled to `assets/css/style.css`:
* Production build: `npm run sass:compressed`
* Watch during dev: `npm run sass:watch`
* Complete styling architecture, design tokens, and CWV guidelines are documented in [SCSS-README.md](SCSS-README.md).

## Directory Layout
```
wpopt/
├── assets/
│   ├── css/style.css
│   ├── scss/
│   │   ├── base/
│   │   ├── components/
│   │   └── layout/
│   └── img/
│       ├── logo-transparent.png
│       └── layered-architecture.svg
├── parts/
│   ├── header.html
│   ├── hero-section.html
│   ├── services-section.html
│   ├── pricing-section.html
│   ├── case-studies-section.html
│   ├── intake-form-section.html
│   └── footer.html
├── templates/
│   ├── front-page.html
│   ├── page-services.html
│   ├── page-pricing.html
│   └── page-case-studies.html
├── deploy.sh
├── DEPLOYMENT.md
├── SCSS-README.md
├── functions.php
└── theme.json
```
