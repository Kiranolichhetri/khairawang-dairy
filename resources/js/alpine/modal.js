/**
 * Modal Alpine.js Component
 * Handles modal dialogs with animations
 * @module alpine/modal
 */

/**
 * Initialize the modal component
 */
export function initModal() {
  document.addEventListener('alpine:init', () => {
    Alpine.data('modal', (options = {}) => ({
      isOpen: false,
      modalId: options.id || 'modal',

      init() {
        // Listen for open/close events
        window.addEventListener(`open-modal-${this.modalId}`, () => this.open());
        window.addEventListener(`close-modal-${this.modalId}`, () => this.close());

        // Close on escape key
        document.addEventListener('keydown', e => {
          if (e.key === 'Escape' && this.isOpen) {
            this.close();
          }
        });
      },

      /**
       * Open the modal
       */
      open() {
        this.isOpen = true;
        document.body.classList.add('overflow-hidden');
        this.$nextTick(() => {
          // Focus first focusable element
          const focusable = this.$el.querySelector(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
          );
          if (focusable) focusable.focus();
        });
      },

      /**
       * Close the modal
       */
      close() {
        this.isOpen = false;
        document.body.classList.remove('overflow-hidden');
      },

      /**
       * Toggle modal state
       */
      toggle() {
        this.isOpen ? this.close() : this.open();
      },

      /**
       * Close on backdrop click
       * @param {Event} e - Click event
       */
      backdropClick(e) {
        if (e.target === e.currentTarget) {
          this.close();
        }
      }
    }));

    // Global modal trigger function
    Alpine.magic('openModal', () => id => {
      window.dispatchEvent(new CustomEvent(`open-modal-${id}`));
    });

    Alpine.magic('closeModal', () => id => {
      window.dispatchEvent(new CustomEvent(`close-modal-${id}`));
    });
  });
}

export default initModal;
