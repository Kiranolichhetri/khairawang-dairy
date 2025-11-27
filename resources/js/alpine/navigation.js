/**
 * Navigation Alpine.js Component
 * Handles mobile navigation and scroll behavior
 * @module alpine/navigation
 */

/**
 * Initialize the navigation store and component
 */
export function initNavigation() {
  document.addEventListener('alpine:init', () => {
    // Navigation store for shared state
    Alpine.store('nav', {
      isMobileMenuOpen: false,
      isScrolled: false,
      activeSection: 'home',

      /**
       * Toggle mobile menu
       */
      toggleMobile() {
        this.isMobileMenuOpen = !this.isMobileMenuOpen;
        document.body.classList.toggle('overflow-hidden', this.isMobileMenuOpen);
      },

      /**
       * Close mobile menu
       */
      closeMobile() {
        this.isMobileMenuOpen = false;
        document.body.classList.remove('overflow-hidden');
      },

      /**
       * Update scroll state
       * @param {boolean} scrolled - Whether page is scrolled
       */
      setScrolled(scrolled) {
        this.isScrolled = scrolled;
      },

      /**
       * Set active navigation section
       * @param {string} section - Section ID
       */
      setActiveSection(section) {
        this.activeSection = section;
      }
    });

    // Navigation component
    Alpine.data('navigation', () => ({
      init() {
        // Listen for scroll events
        window.addEventListener('scroll', this.handleScroll.bind(this));
        this.handleScroll();

        // Set up intersection observer for sections
        this.setupSectionObserver();
      },

      /**
       * Handle scroll event
       */
      handleScroll() {
        Alpine.store('nav').setScrolled(window.scrollY > 50);
      },

      /**
       * Set up section observer for active link highlighting
       */
      setupSectionObserver() {
        const sections = document.querySelectorAll('section[id]');
        const observer = new IntersectionObserver(
          entries => {
            entries.forEach(entry => {
              if (entry.isIntersecting) {
                Alpine.store('nav').setActiveSection(entry.target.id);
              }
            });
          },
          {
            threshold: 0.3,
            rootMargin: '-100px 0px -50% 0px'
          }
        );

        sections.forEach(section => observer.observe(section));
      },

      /**
       * Smooth scroll to section
       * @param {Event} e - Click event
       * @param {string} targetId - Target section ID
       */
      scrollToSection(e, targetId) {
        e.preventDefault();
        const target = document.getElementById(targetId);
        if (target) {
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
          Alpine.store('nav').closeMobile();
        }
      },

      /**
       * Check if link is active
       * @param {string} section - Section ID
       * @returns {boolean} Active status
       */
      isActive(section) {
        return Alpine.store('nav').activeSection === section;
      }
    }));
  });
}

export default initNavigation;
