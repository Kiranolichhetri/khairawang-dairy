# Typography Guide

The KHAIRAWANG DAIRY typography system uses two complementary font families to create visual hierarchy and readability.

## Font Families

### Poppins (Headings & Buttons)
- **Usage**: Headings, buttons, navigation, important UI elements
- **Weights**: 300 (Light), 400 (Regular), 500 (Medium), 600 (Semi-bold), 700 (Bold)
- **Tailwind Class**: `font-poppins` or `font-heading`
- **Source**: Google Fonts

### DM Sans (Body Text)
- **Usage**: Body text, paragraphs, form labels, descriptions
- **Weights**: 400 (Regular), 500 (Medium), 700 (Bold)
- **Tailwind Class**: `font-dm-sans` or `font-body`
- **Source**: Google Fonts

## Font Loading

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
```

## Type Scale

| Name | Size | Line Height | Tailwind Class |
|------|------|-------------|----------------|
| xs | 12px | 16px | `text-xs` |
| sm | 14px | 20px | `text-sm` |
| base | 16px | 24px | `text-base` |
| lg | 18px | 28px | `text-lg` |
| xl | 20px | 28px | `text-xl` |
| 2xl | 24px | 32px | `text-2xl` |
| 3xl | 30px | 36px | `text-3xl` |
| 4xl | 36px | 40px | `text-4xl` |
| 5xl | 48px | 52px | `text-5xl` |
| 6xl | 60px | 64px | `text-6xl` |

## Font Weights

| Weight | Value | Tailwind Class | Usage |
|--------|-------|----------------|-------|
| Light | 300 | `font-light` | Decorative text |
| Regular | 400 | `font-normal` | Body text |
| Medium | 500 | `font-medium` | Emphasized text, navigation |
| Semi-bold | 600 | `font-semibold` | Subheadings, buttons |
| Bold | 700 | `font-bold` | Headings, important text |

## Heading Styles

### H1 - Page Title
```html
<h1 class="font-heading font-bold text-4xl md:text-5xl lg:text-6xl text-dark-brown">
  Fresh From Our Farm To Your Table
</h1>
```
- **Desktop**: 60px / 64px line-height
- **Tablet**: 48px / 52px line-height
- **Mobile**: 36px / 40px line-height

### H2 - Section Title
```html
<h2 class="font-heading font-bold text-3xl md:text-4xl lg:text-5xl text-dark-brown">
  Featured Products
</h2>
```
- **Desktop**: 48px / 52px line-height
- **Tablet**: 36px / 40px line-height
- **Mobile**: 30px / 36px line-height

### H3 - Subsection Title
```html
<h3 class="font-heading font-semibold text-2xl md:text-3xl text-dark-brown">
  Our Story
</h3>
```
- **Desktop**: 30px / 36px line-height
- **Mobile**: 24px / 32px line-height

### H4 - Card Title
```html
<h4 class="font-heading font-semibold text-xl md:text-2xl text-dark-brown">
  Fresh Farm Milk
</h4>
```
- **Desktop**: 24px / 32px line-height
- **Mobile**: 20px / 28px line-height

### H5 - Small Title
```html
<h5 class="font-heading font-semibold text-lg md:text-xl text-dark-brown">
  Quick Links
</h5>
```

### H6 - Smallest Title
```html
<h6 class="font-heading font-medium text-base md:text-lg text-dark-brown">
  Contact Info
</h6>
```

## Body Text Styles

### Paragraph - Large
```html
<p class="font-body text-lg text-gray-600 leading-relaxed">
  Experience the pure taste of premium dairy products.
</p>
```

### Paragraph - Regular
```html
<p class="font-body text-base text-dark-brown leading-relaxed">
  Our family-owned farm follows traditional methods.
</p>
```

### Paragraph - Small
```html
<p class="font-body text-sm text-gray-500">
  No preservatives or additives
</p>
```

## Button Typography

```html
<!-- Primary Button -->
<button class="font-poppins font-medium text-base">
  Shop Now
</button>

<!-- Small Button -->
<button class="font-poppins font-medium text-sm">
  Add to Cart
</button>

<!-- Large Button -->
<button class="font-poppins font-medium text-lg">
  Get Started
</button>
```

## Link Styles

```html
<!-- Navigation Link -->
<a class="font-body font-medium text-base text-dark-brown hover:text-accent-orange">
  Products
</a>

<!-- Inline Link -->
<a class="font-body text-base text-accent-orange hover:text-dark-brown underline">
  Learn more
</a>
```

## Badge Typography

```html
<span class="font-medium text-sm">
  Fresh
</span>
```

## Price Typography

```html
<p class="font-heading font-bold text-xl text-accent-orange">
  NPR 120
  <span class="text-sm text-gray-400 line-through ml-2 font-normal">NPR 150</span>
</p>
```

## Text Utilities

### Line Clamp
```html
<!-- Single line -->
<p class="line-clamp-1">Long text truncated to one line...</p>

<!-- Two lines -->
<p class="line-clamp-2">Text truncated to two lines...</p>

<!-- Three lines -->
<p class="line-clamp-3">Text truncated to three lines...</p>
```

### Text Balance
```html
<h1 class="text-balance">
  Balanced heading text for better readability
</h1>
```

### Text Gradient
```html
<span class="text-gradient">
  Gradient text effect
</span>
```

## Responsive Typography

The design system uses a mobile-first approach:

```css
/* Mobile (default) */
h1 { font-size: 36px; }

/* Tablet (md: 768px) */
@media (min-width: 768px) {
  h1 { font-size: 48px; }
}

/* Desktop (lg: 1024px) */
@media (min-width: 1024px) {
  h1 { font-size: 60px; }
}
```

## Usage Guidelines

### Do's ✅
- Use Poppins for headings and CTAs
- Use DM Sans for body text and descriptions
- Maintain consistent hierarchy
- Use appropriate line heights for readability

### Don'ts ❌
- Don't mix more than 2-3 weights in a single view
- Don't use light weight (300) for small text
- Don't skip heading levels (h1 → h3)
- Don't use all caps for long text
