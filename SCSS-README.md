# SCSS Styling Architecture & Guide

The `wpopt` theme uses a modular, zero-bloat SCSS architecture engineered for 100/100 Core Web Vitals, zero layout shift (CLS), and seamless integration with WordPress Full Site Editing (`theme.json`).

---

## Directory Structure

All styles originate in `assets/scss/` and compile into `assets/css/style.css`:

```text
assets/scss/
├── main.scss                  # Primary entry point importing all partials
├── base/
│   ├── _variables.scss        # SCSS tokens, spacing units, and container widths
│   ├── _animations.scss       # Keyframes (fadeInUp, pulseGlow, architecture flows)
│   └── _reset.scss            # Baseline resets and box-sizing normalization
├── components/
│   ├── _buttons.scss          # High-converting CTA buttons & hover transitions
│   ├── _cards.scss            # Service and telemetry benchmark cards
│   ├── _pricing.scss          # Harmonized pricing table styling & tier highlights
│   ├── _steps.scss            # "How It Works" step-by-step process indicators
│   ├── _case-studies.scss     # Enterprise case study cards & architecture badge
│   └── _forms.scss            # Contact Form 7 intake form layout & state styling
├── layout/
│   ├── _header.scss           # Header navigation, branding, and WCAG skip link
│   └── _responsive.scss       # Global media queries and layout adaptations
└── pages/
    ├── _homepage.scss         # Homepage hero, metrics counters, and trust badges
    └── _sections.scss         # Section wrapper spacing and background alternates
```

---

## Tooling & Compilation

Dependencies are defined in `package.json` utilizing `sass` (Dart Sass).

```bash
# 1. Install dev dependencies (first time only)
npm install

# 2. Watch mode (auto-compilation with expanded output during active CSS editing)
npm run sass:watch

# 3. Production build (compresses and minifies to ~18 KB for commit)
npm run sass:compressed
```

> [!IMPORTANT]
> Always run `npm run sass:compressed` before committing changes to Git. Production droplets pull compiled assets directly and require zero Node.js/npm tooling.

---

## Synergy with `theme.json`

The theme bridges WordPress Block Editor controls with SCSS via standard CSS Custom Properties:

| Theme Token | `theme.json` Variable | SCSS Variable Fallback | Usage |
| :--- | :--- | :--- | :--- |
| **Deep Carbon** | `var(--wp--preset--color--primary)` | `$color-primary: #090D16` | Main text, dark headers |
| **Electric Sapphire**| `var(--wp--preset--color--accent)` | `$color-accent: #2563EB` | Primary CTAs, active states |
| **Speed Emerald** | `var(--wp--preset--color--speed-emerald)` | `$color-success: #10B981` | CWV 100/100 badges, checkmarks |
| **Border Subtle** | `var(--wp--preset--color--border-subtle)` | `$color-border: #E2E8F0` | Card borders, dividers |
| **Background Alt** | `var(--wp--preset--color--background-alt)` | `$color-background-alt: #F8FAFC` | Alternating section bands |

---

## Core Web Vitals & GDPR Standards

1. **0ms Local System Font Stack**:
   * Zero external Google Fonts requests.
   * Leverages high-performance native system typography:
     ```scss
     $font-family-sans: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;
     ```
2. **Zero Layout Shift (CLS 0.00)**:
   * Fixed aspect ratios and explicit dimensions for images and SVG assets.
   * Autonomous CSS animations run strictly on composite layers (`transform` and `opacity`).
3. **WCAG 2.2 AA Focus Styling**:
   * Clear focus rings on buttons, inputs, and the screen-reader skip-to-content anchor.
