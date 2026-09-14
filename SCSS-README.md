# SCSS Setup & Customization Guide

This theme uses SCSS for styling, making it easy to customize colors, spacing, and components.

## File Structure

```
assets/
├── scss/
│   ├── _variables.scss    # Colors, spacing, typography variables
│   ├── _base.scss         # Base styles and CSS variables
│   ├── _components.scss    # Buttons, cards, navigation
│   ├── _sections.scss     # Homepage sections styling
│   ├── _forms.scss        # Contact Form 7 styling
│   ├── _responsive.scss   # Responsive breakpoints
│   └── main.scss          # Main file that imports all
└── css/
    └── style.css          # Compiled CSS (auto-generated)
```

## Quick Start

### Install Dependencies
```bash
npm install
```

### Compile SCSS
```bash
# One-time compilation
npm run sass

# Watch mode (auto-compile on changes)
npm run sass:watch

# Production (compressed)
npm run sass:compressed
```

## Customization

### Colors
Edit `assets/scss/_variables.scss`:
```scss
$color-primary: #0A4D68;
$color-accent: #94D2BD;
// ... etc
```

Or customize via `theme.json` - changes will be reflected in CSS variables.

### Spacing
Edit spacing values in `_variables.scss`:
```scss
$spacing-md: 1.5rem;
$spacing-lg: 2rem;
// ... etc
```

### Typography
Font family and sizes in `_variables.scss`:
```scss
$font-family-sans: 'Inter', ...;
$font-weight-semibold: 600;
```

### Components
- **Buttons**: `_components.scss` - `.wp-block-button__link`
- **Service Cards**: `_sections.scss` - `.wp-block-columns.alignwide`
- **Forms**: `_forms.scss` - `.wpcf7`
- **Hero Section**: `_sections.scss` - `.hero-section`

## Theme.json Integration

Colors defined in `theme.json` are automatically available as CSS variables:
- `var(--wp--preset--color--primary)`
- `var(--wp--preset--color--accent)`
- etc.

You can use these in SCSS or customize them via the WordPress Block Editor.

## Best Practices

1. **Use variables** - Don't hardcode colors/spacing
2. **Modular approach** - Keep related styles in the same file
3. **Mobile-first** - Styles in `_responsive.scss` override for smaller screens
4. **Test after changes** - Always compile and test in browser

## Troubleshooting

If styles don't update:
1. Make sure SCSS is compiled: `npm run sass`
2. Clear browser cache
3. Check `functions.php` is enqueuing `assets/css/style.css`
4. Verify file permissions

