# Component Specifications

This document details all UI components in the KHAIRAWANG DAIRY design system.

## Table of Contents

1. [Buttons](#buttons)
2. [Cards](#cards)
3. [Badges](#badges)
4. [Form Elements](#form-elements)
5. [Hero Section](#hero-section)
6. [Product Carousel](#product-carousel)
7. [About Section](#about-section)
8. [Footer](#footer)
9. [Navigation](#navigation)

---

## Buttons

### Primary Button
The main call-to-action button with accent orange background.

```html
<button class="btn btn-primary">
  Shop Now
</button>
```

**Specifications:**
- Background: `#FD7C44` (accent-orange)
- Text: `#FFFFFF` (white)
- Font: Poppins Medium, 16px
- Padding: 12px 24px
- Border Radius: 12px
- Shadow: `0 4px 12px rgba(253, 124, 68, 0.25)`
- Hover: Scale up slightly, increased shadow

### Secondary Button
Outline button for secondary actions.

```html
<button class="btn btn-secondary">
  Learn More
</button>
```

**Specifications:**
- Background: Transparent
- Border: 2px solid `#201916`
- Text: `#201916` (dark-brown)
- Hover: Filled background, white text

### Button Sizes

```html
<!-- Small -->
<button class="btn btn-primary btn-sm">Small</button>

<!-- Default -->
<button class="btn btn-primary">Default</button>

<!-- Large -->
<button class="btn btn-primary btn-lg">Large</button>
```

| Size | Padding | Font Size |
|------|---------|-----------|
| Small (btn-sm) | 8px 16px | 14px |
| Default | 12px 24px | 16px |
| Large (btn-lg) | 16px 32px | 18px |

---

## Cards

### Product Card
Display products in a grid or carousel.

```html
<article class="product-card">
  <div class="product-card-image">
    <img src="..." alt="Product name">
    <div class="product-card-badge">
      <span class="badge badge-success">Fresh</span>
    </div>
  </div>
  <div class="product-card-body">
    <h3 class="product-card-name">Fresh Farm Milk</h3>
    <p class="product-card-price">NPR 120</p>
    <button class="btn btn-primary product-card-action">
      Add to Cart
    </button>
  </div>
</article>
```

**Specifications:**
- Background: `#FFFFFF`
- Border Radius: 16px
- Shadow: `0 4px 12px rgba(32, 25, 22, 0.08)`
- Image Aspect Ratio: 4:5
- Hover: Lift 8px, increased shadow

### Base Card
Generic card component.

```html
<div class="card">
  <div class="card-body">
    Content goes here
  </div>
</div>
```

---

## Badges

Visual indicators for product status or categories.

```html
<!-- Primary -->
<span class="badge badge-primary">Popular</span>

<!-- Success -->
<span class="badge badge-success">Fresh</span>

<!-- Error -->
<span class="badge badge-error">Sold Out</span>
```

**Specifications:**
- Padding: 4px 12px
- Border Radius: Full (pill shape)
- Font: Medium, 14px
- Background: 10% opacity of color
- Text: Full color

---

## Form Elements

### Text Input
Standard text input field.

```html
<input type="text" class="input" placeholder="Your email">
```

**Specifications:**
- Background: `#FFFFFF`
- Border: 1px solid `#E5E7EB`
- Border Radius: 12px
- Padding: 12px 16px
- Font: DM Sans, 16px
- Focus: Border `#FD7C44`, ring with 20% opacity

### Input with Error
```html
<input type="text" class="input input-error" placeholder="Your email">
```

---

## Hero Section

Full-width hero with gradient overlay and animated content.

```html
<section class="hero">
  <div class="hero-background" style="background-image: url(...)"></div>
  <div class="hero-overlay"></div>
  <div class="hero-content">
    <h1 class="hero-title">Fresh From Our Farm<br>To Your Table</h1>
    <p class="hero-subtitle">Premium dairy products...</p>
    <div class="hero-actions">
      <a href="#" class="btn btn-primary btn-lg">Shop Now</a>
      <a href="#" class="btn btn-secondary btn-lg">Learn More</a>
    </div>
  </div>
</section>
```

**Specifications:**
- Height: 100vh minimum
- Overlay: Linear gradient from 85% to 60% opacity dark brown
- Content: Centered vertically and horizontally
- Animations: Fade-in-up with staggered delays

---

## Product Carousel

Horizontal scrollable product showcase.

```html
<div class="products-carousel">
  <div class="products-carousel-nav">
    <button class="products-carousel-btn" id="carousel-prev">←</button>
    <button class="products-carousel-btn" id="carousel-next">→</button>
  </div>
  <div class="products-carousel-container">
    <!-- Product cards here -->
  </div>
  <div class="products-carousel-dots">
    <button class="products-carousel-dot active"></button>
    <button class="products-carousel-dot"></button>
  </div>
</div>
```

**Specifications:**
- Container: Horizontal scroll, snap-x mandatory
- Item Width: 288px (flex-shrink-0)
- Gap: 24px
- Navigation Buttons: 48px circle, white background
- Dots: 8px circles, active dot 32px wide

**Functionality:**
- Click arrows to scroll
- Click dots to jump to position
- Touch/swipe support on mobile
- Auto-update dots on scroll

---

## About Section

Two-column layout showcasing company information.

```html
<section class="section about-section">
  <div class="container-dairy">
    <div class="about-grid">
      <div class="about-image">
        <img src="..." alt="Farm">
        <div class="about-image-badge">25+ Years</div>
      </div>
      <div class="about-content">
        <h2 class="about-title">Our Story</h2>
        <p class="about-description">...</p>
        <div class="about-highlights">
          <!-- Highlight items -->
        </div>
      </div>
    </div>
  </div>
</section>
```

**Specifications:**
- Layout: 2-column grid on desktop, stacked on mobile
- Gap: 64px
- Image Badge: Positioned bottom-right, overflow visible
- Highlights: 2-column grid of feature cards

### Highlight Item
```html
<div class="about-highlight">
  <div class="about-highlight-icon">
    <svg>...</svg>
  </div>
  <div class="about-highlight-content">
    <h4>100% Fresh & Natural</h4>
    <p>No preservatives or additives</p>
  </div>
</div>
```

---

## Footer

Full-width footer with multiple columns.

```html
<footer class="footer">
  <div class="footer-main">
    <div class="container-dairy">
      <div class="footer-grid">
        <div class="footer-brand">...</div>
        <div class="footer-column">...</div>
        <div class="footer-column">...</div>
        <div class="footer-column">...</div>
      </div>
    </div>
  </div>
  <div class="footer-bottom">
    <div class="container-dairy">
      <div class="footer-bottom-content">
        <p class="footer-copyright">© 2024 KHAIRAWANG DAIRY</p>
        <nav class="footer-bottom-links">...</nav>
      </div>
    </div>
  </div>
</footer>
```

**Specifications:**
- Background: `#201916` (dark-brown)
- Text: White with gray-400 for secondary
- Grid: 4 columns on desktop, responsive
- Link Hover: `#FD7C44` (accent-orange)
- Bottom: Border-top with 10% white opacity

### Newsletter Form
```html
<form class="footer-newsletter-form">
  <input type="email" class="footer-newsletter-input" placeholder="Your email">
  <button type="submit" class="footer-newsletter-btn">→</button>
</form>
```

---

## Navigation

Fixed top navigation with scroll effects.

```html
<nav class="navbar" id="navbar">
  <div class="navbar-container">
    <a href="/" class="navbar-logo">🥛 KHAIRAWANG DAIRY</a>
    <div class="navbar-menu">
      <a href="#home" class="navbar-link">Home</a>
      <a href="#products" class="navbar-link">Products</a>
      <a href="#about" class="navbar-link">About</a>
      <a href="#contact" class="navbar-link">Contact</a>
    </div>
    <div class="navbar-actions">
      <a href="#" class="btn btn-primary btn-sm">Shop Now</a>
    </div>
  </div>
</nav>
```

**Specifications:**
- Position: Fixed, top 0
- Z-index: 50
- Transition: 300ms for all properties
- Default: Transparent background, white text
- Scrolled: White background, dark text, shadow

**Scroll Behavior:**
```javascript
window.addEventListener('scroll', () => {
  if (window.scrollY > 50) {
    navbar.classList.add('scrolled');
  } else {
    navbar.classList.remove('scrolled');
  }
});
```

---

## Animation Classes

### Fade Animations
```html
<div class="animate-fade-in">Fades in</div>
<div class="animate-fade-in-up">Fades in from below</div>
<div class="animate-fade-in-down">Fades in from above</div>
```

### Continuous Animations
```html
<div class="animate-bounce-soft">Gentle bounce</div>
<div class="animate-pulse-soft">Gentle pulse</div>
<div class="animate-float">Floating effect</div>
```

### Animation Delays
```html
<div class="animate-fade-in-up delay-100">100ms delay</div>
<div class="animate-fade-in-up delay-200">200ms delay</div>
<div class="animate-fade-in-up delay-300">300ms delay</div>
```

### Scroll Animations
```html
<div data-animate="fade-up">Animates when scrolled into view</div>
```

---

## Accessibility Checklist

All components meet these requirements:

- [ ] Keyboard navigable
- [ ] Focus indicators visible
- [ ] ARIA labels on interactive elements
- [ ] Color contrast AA compliant
- [ ] Screen reader friendly
- [ ] Reduced motion support
- [ ] Semantic HTML structure
