/**
 * Image Gallery Alpine.js Component
 * Handles product image gallery with thumbnails
 * @module alpine/gallery
 */

/**
 * Initialize the gallery component
 */
export function initGallery() {
  document.addEventListener('alpine:init', () => {
    Alpine.data('gallery', (options = {}) => ({
      images: options.images || [],
      activeIndex: 0,
      isZoomed: false,
      isLightboxOpen: false,

      init() {
        // Keyboard navigation
        document.addEventListener('keydown', e => {
          if (!this.isLightboxOpen) return;

          switch (e.key) {
            case 'ArrowLeft':
              this.prev();
              break;
            case 'ArrowRight':
              this.next();
              break;
            case 'Escape':
              this.closeLightbox();
              break;
          }
        });
      },

      /**
       * Select an image by index
       * @param {number} index - Image index
       */
      select(index) {
        if (index >= 0 && index < this.images.length) {
          this.activeIndex = index;
        }
      },

      /**
       * Go to next image
       */
      next() {
        this.activeIndex =
          this.activeIndex < this.images.length - 1 ? this.activeIndex + 1 : 0;
      },

      /**
       * Go to previous image
       */
      prev() {
        this.activeIndex =
          this.activeIndex > 0 ? this.activeIndex - 1 : this.images.length - 1;
      },

      /**
       * Get active image
       * @returns {object} Active image object
       */
      get activeImage() {
        return this.images[this.activeIndex] || {};
      },

      /**
       * Toggle zoom on main image
       */
      toggleZoom() {
        this.isZoomed = !this.isZoomed;
      },

      /**
       * Open lightbox
       */
      openLightbox() {
        this.isLightboxOpen = true;
        document.body.classList.add('overflow-hidden');
      },

      /**
       * Close lightbox
       */
      closeLightbox() {
        this.isLightboxOpen = false;
        this.isZoomed = false;
        document.body.classList.remove('overflow-hidden');
      },

      /**
       * Handle image zoom on mouse move
       * @param {MouseEvent} e - Mouse event
       */
      handleZoomMove(e) {
        if (!this.isZoomed) return;

        const rect = e.target.getBoundingClientRect();
        const x = ((e.clientX - rect.left) / rect.width) * 100;
        const y = ((e.clientY - rect.top) / rect.height) * 100;

        e.target.style.transformOrigin = `${x}% ${y}%`;
      },

      /**
       * Check if thumbnail is active
       * @param {number} index - Thumbnail index
       * @returns {boolean} Active status
       */
      isActive(index) {
        return this.activeIndex === index;
      }
    }));
  });
}

export default initGallery;
