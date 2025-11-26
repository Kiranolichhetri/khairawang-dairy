# KHAIRAWANG DAIRY Design System

A comprehensive design system for the KHAIRAWANG DAIRY e-commerce platform, built with Tailwind CSS 3.x.

## Overview

This design system provides a consistent, accessible, and visually appealing foundation for all UI components across the KHAIRAWANG DAIRY platform. It follows modern design principles with a focus on premium aesthetics, usability, and performance.

## Design Philosophy

- **Modern & Premium**: Clean, sophisticated design that reflects product quality
- **User-Centric**: Intuitive interfaces with clear visual hierarchy
- **Accessible**: WCAG 2.1 AA compliant components
- **Responsive**: Mobile-first approach with fluid layouts
- **Performant**: Optimized assets and 60fps animations

## Quick Start

```bash
# Install dependencies
npm install

# Start development server
npm run dev

# Build for production
npm run build
```

## Documentation Structure

| Document | Description |
|----------|-------------|
| [Colors](./colors.md) | Color palette and usage guidelines |
| [Typography](./typography.md) | Font families, scales, and weights |
| [Components](./components.md) | UI component specifications |

## Design Tokens

### Colors

| Token | Value | Usage |
|-------|-------|-------|
| `dark-brown` | `#201916` | Primary text, headers |
| `accent-orange` | `#FD7C44` | CTAs, highlights |
| `cream` | `#F7EFDF` | Backgrounds |
| `white` | `#FFFFFF` | Clean backgrounds |
| `light-gray` | `#F5F5F5` | Secondary backgrounds |
| `success-green` | `#22C55E` | Success states |
| `error-red` | `#EF4444` | Error states |

### Typography

- **Headings**: Poppins (300-700)
- **Body**: DM Sans (400-700)

### Spacing

Uses Tailwind's default spacing scale plus custom additions:
- `18`: 4.5rem
- `22`: 5.5rem
- `26`: 6.5rem
- `30`: 7.5rem

### Border Radius

| Token | Value |
|-------|-------|
| `xl` | 12px |
| `2xl` | 16px |
| `3xl` | 24px |
| `4xl` | 32px |

## File Structure

```
khairawang-dairy/
├── public/
│   └── index.html              # Complete homepage
├── resources/
│   ├── css/
│   │   ├── app.css             # Main entry with Tailwind directives
│   │   ├── components.css      # Component-specific styles
│   │   └── animations.css      # Keyframes and transitions
│   └── views/
│       ├── layouts/
│       │   └── base.html       # Base HTML layout
│       └── components/
│           ├── hero.html       # Hero section
│           ├── featured-products.html  # Product carousel
│           ├── about.html      # About section
│           └── footer.html     # Footer component
├── docs/
│   └── design-system/
│       ├── README.md           # This file
│       ├── colors.md           # Color documentation
│       ├── typography.md       # Typography guide
│       └── components.md       # Component specs
├── tailwind.config.js          # Tailwind configuration
├── postcss.config.js           # PostCSS configuration
├── vite.config.js              # Vite build configuration
└── package.json                # Dependencies
```

## Browser Support

- Chrome (last 2 versions)
- Firefox (last 2 versions)
- Safari (last 2 versions)
- Edge (last 2 versions)

## Accessibility

All components are built with accessibility in mind:

- Semantic HTML5 elements
- ARIA labels and roles where needed
- Keyboard navigation support
- Focus indicators
- Color contrast ratios meeting WCAG 2.1 AA
- Screen reader friendly

## Performance

- CSS minification in production
- Tree-shaking unused styles
- Lazy loading images
- Optimized animations (60fps)
- Minimal JavaScript dependencies

## Contributing

When adding new components or modifying existing ones:

1. Follow the established color palette
2. Use the typography scale
3. Ensure accessibility compliance
4. Add responsive styles
5. Document changes

## License

MIT License - See LICENSE file for details.
