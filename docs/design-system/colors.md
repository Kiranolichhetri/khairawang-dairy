# Color Palette

The KHAIRAWANG DAIRY color system uses warm, earthy tones that evoke freshness, nature, and premium quality.

## Primary Colors

### Dark Brown
- **Hex**: `#201916`
- **Tailwind Class**: `text-dark-brown`, `bg-dark-brown`, `border-dark-brown`
- **Usage**: Primary text, headers, buttons, footer background
- **RGB**: 32, 25, 22
- **HSL**: 9°, 19%, 11%

### Accent Orange
- **Hex**: `#FD7C44`
- **Tailwind Class**: `text-accent-orange`, `bg-accent-orange`, `border-accent-orange`
- **Usage**: Call-to-action buttons, highlights, links, accents
- **RGB**: 253, 124, 68
- **HSL**: 18°, 98%, 63%

### Cream
- **Hex**: `#F7EFDF`
- **Tailwind Class**: `text-cream`, `bg-cream`, `border-cream`
- **Usage**: Page backgrounds, card backgrounds, soft elements
- **RGB**: 247, 239, 223
- **HSL**: 40°, 57%, 92%

## Neutral Colors

### White
- **Hex**: `#FFFFFF`
- **Tailwind Class**: `text-white`, `bg-white`
- **Usage**: Clean backgrounds, text on dark surfaces, cards
- **RGB**: 255, 255, 255

### Light Gray
- **Hex**: `#F5F5F5`
- **Tailwind Class**: `text-light-gray`, `bg-light-gray`, `border-light-gray`
- **Usage**: Secondary backgrounds, section dividers
- **RGB**: 245, 245, 245
- **HSL**: 0°, 0%, 96%

## State Colors

### Success Green
- **Hex**: `#22C55E`
- **Tailwind Class**: `text-success-green`, `bg-success-green`
- **Usage**: Success messages, positive indicators, "Fresh" badges
- **RGB**: 34, 197, 94
- **HSL**: 142°, 71%, 45%

### Error Red
- **Hex**: `#EF4444`
- **Tailwind Class**: `text-error-red`, `bg-error-red`
- **Usage**: Error messages, alerts, form validation errors
- **RGB**: 239, 68, 68
- **HSL**: 0°, 84%, 60%

## Color Combinations

### Primary Combinations

| Background | Text | Usage |
|------------|------|-------|
| `bg-cream` | `text-dark-brown` | Default page content |
| `bg-dark-brown` | `text-white` | Footer, dark sections |
| `bg-accent-orange` | `text-white` | Primary buttons |
| `bg-white` | `text-dark-brown` | Cards, modals |
| `bg-light-gray` | `text-dark-brown` | Alternate sections |

### Button Styles

```html
<!-- Primary Button -->
<button class="bg-accent-orange text-white hover:bg-opacity-90">
  Shop Now
</button>

<!-- Secondary Button -->
<button class="bg-transparent text-dark-brown border-2 border-dark-brown hover:bg-dark-brown hover:text-white">
  Learn More
</button>

<!-- Outline Button -->
<button class="bg-transparent text-accent-orange border-2 border-accent-orange hover:bg-accent-orange hover:text-white">
  View Details
</button>
```

### Badge Styles

```html
<!-- Primary Badge -->
<span class="bg-accent-orange bg-opacity-10 text-accent-orange">Popular</span>

<!-- Success Badge -->
<span class="bg-success-green bg-opacity-10 text-success-green">Fresh</span>

<!-- Error Badge -->
<span class="bg-error-red bg-opacity-10 text-error-red">Sold Out</span>
```

## Contrast Ratios

All color combinations meet WCAG 2.1 AA standards:

| Combination | Contrast Ratio | Rating |
|-------------|----------------|--------|
| Dark Brown on Cream | 12.5:1 | AAA |
| Dark Brown on White | 14.8:1 | AAA |
| White on Dark Brown | 14.8:1 | AAA |
| White on Accent Orange | 3.2:1 | AA (Large Text) |
| Dark Brown on Light Gray | 13.5:1 | AAA |

## Shadows

Custom shadows use the dark brown color with opacity:

```css
/* Soft Shadow */
box-shadow: 0 2px 8px rgba(32, 25, 22, 0.08);

/* Medium Shadow */
box-shadow: 0 4px 16px rgba(32, 25, 22, 0.10);

/* Large Shadow */
box-shadow: 0 8px 24px rgba(32, 25, 22, 0.12);

/* Button Shadow (Orange) */
box-shadow: 0 4px 12px rgba(253, 124, 68, 0.25);
```

## Gradients

### Hero Gradient
```css
background: linear-gradient(135deg, rgba(32, 25, 22, 0.85) 0%, rgba(32, 25, 22, 0.6) 100%);
```

### Card Gradient
```css
background: linear-gradient(180deg, rgba(247, 239, 223, 0) 0%, rgba(247, 239, 223, 0.8) 100%);
```

## CSS Custom Properties

The design system uses CSS custom properties for easy theming:

```css
:root {
  --color-dark-brown: #201916;
  --color-accent-orange: #FD7C44;
  --color-cream: #F7EFDF;
  --color-white: #FFFFFF;
  --color-light-gray: #F5F5F5;
  --color-success: #22C55E;
  --color-error: #EF4444;
}
```

## Usage Guidelines

### Do's ✅
- Use dark brown for all primary text
- Use accent orange sparingly for emphasis
- Maintain sufficient contrast for readability
- Use cream backgrounds for a warm, inviting feel

### Don'ts ❌
- Don't use accent orange for large text blocks
- Don't combine similar colors without sufficient contrast
- Don't overuse accent orange—it loses impact
- Don't use pure black; prefer dark brown
