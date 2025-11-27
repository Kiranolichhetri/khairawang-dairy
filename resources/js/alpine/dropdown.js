/**
 * Dropdown Alpine.js Component
 * Handles dropdown menus with keyboard navigation
 * @module alpine/dropdown
 */

/**
 * Initialize the dropdown component
 */
export function initDropdown() {
  document.addEventListener('alpine:init', () => {
    Alpine.data('dropdown', (options = {}) => ({
      isOpen: false,
      focusedIndex: -1,
      placement: options.placement || 'bottom-start',

      init() {
        // Close on outside click
        document.addEventListener('click', e => {
          if (this.isOpen && !this.$el.contains(e.target)) {
            this.close();
          }
        });

        // Keyboard navigation
        this.$el.addEventListener('keydown', this.handleKeydown.bind(this));
      },

      /**
       * Open dropdown
       */
      open() {
        this.isOpen = true;
        this.focusedIndex = -1;
      },

      /**
       * Close dropdown
       */
      close() {
        this.isOpen = false;
        this.focusedIndex = -1;
      },

      /**
       * Toggle dropdown
       */
      toggle() {
        this.isOpen ? this.close() : this.open();
      },

      /**
       * Handle keyboard navigation
       * @param {KeyboardEvent} e - Keyboard event
       */
      handleKeydown(e) {
        if (!this.isOpen) {
          if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
            e.preventDefault();
            this.open();
          }
          return;
        }

        const items = this.getItems();

        switch (e.key) {
          case 'Escape':
            e.preventDefault();
            this.close();
            break;

          case 'ArrowDown':
            e.preventDefault();
            this.focusedIndex = Math.min(
              this.focusedIndex + 1,
              items.length - 1
            );
            this.focusItem(items[this.focusedIndex]);
            break;

          case 'ArrowUp':
            e.preventDefault();
            this.focusedIndex = Math.max(this.focusedIndex - 1, 0);
            this.focusItem(items[this.focusedIndex]);
            break;

          case 'Enter':
          case ' ':
            e.preventDefault();
            if (this.focusedIndex >= 0 && items[this.focusedIndex]) {
              items[this.focusedIndex].click();
              this.close();
            }
            break;

          case 'Tab':
            this.close();
            break;
        }
      },

      /**
       * Get dropdown menu items
       * @returns {NodeList} Menu items
       */
      getItems() {
        return this.$el.querySelectorAll('[role="menuitem"]');
      },

      /**
       * Focus a specific item
       * @param {HTMLElement} item - Item to focus
       */
      focusItem(item) {
        if (item) {
          item.focus();
        }
      },

      /**
       * Select an item
       * @param {*} value - Item value
       */
      select(value) {
        this.$dispatch('select', value);
        this.close();
      }
    }));
  });
}

export default initDropdown;
