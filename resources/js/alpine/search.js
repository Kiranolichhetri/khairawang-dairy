/**
 * Search Autocomplete Alpine.js Component
 * Handles product search with suggestions
 * @module alpine/search
 */

/**
 * Initialize the search component
 */
export function initSearch() {
  document.addEventListener('alpine:init', () => {
    // Search store for shared state
    Alpine.store('search', {
      isOpen: false,

      open() {
        this.isOpen = true;
        document.body.classList.add('overflow-hidden');
      },

      close() {
        this.isOpen = false;
        document.body.classList.remove('overflow-hidden');
      },

      toggle() {
        this.isOpen ? this.close() : this.open();
      }
    });

    // Search component
    Alpine.data('search', (options = {}) => ({
      query: '',
      results: [],
      isLoading: false,
      focusedIndex: -1,
      minQueryLength: options.minQueryLength || 2,
      debounceTime: options.debounceTime || 300,
      debounceTimer: null,

      // Sample products for demo (replace with API call in production)
      sampleProducts: [
        { id: 1, name: 'Fresh Farm Milk', price: 120, category: 'Milk', image: 'https://images.unsplash.com/photo-1563636619-e9143da7973b?w=100' },
        { id: 2, name: 'Organic Yogurt', price: 85, category: 'Yogurt', image: 'https://images.unsplash.com/photo-1488477181946-6428a0291777?w=100' },
        { id: 3, name: 'Artisan Cheese', price: 350, category: 'Cheese', image: 'https://images.unsplash.com/photo-1486297678162-eb2a19b0a32d?w=100' },
        { id: 4, name: 'Fresh Butter', price: 180, category: 'Butter', image: 'https://images.unsplash.com/photo-1589985270826-4b7bb135bc9d?w=100' },
        { id: 5, name: 'Fresh Paneer', price: 220, category: 'Paneer', image: 'https://images.unsplash.com/photo-1631452180519-c014fe946bc7?w=100' },
        { id: 6, name: 'Fresh Cream', price: 95, category: 'Cream', image: 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=100' }
      ],

      init() {
        // Focus input when search opens
        this.$watch('$store.search.isOpen', value => {
          if (value) {
            this.$nextTick(() => {
              this.$refs.searchInput?.focus();
            });
          } else {
            this.clear();
          }
        });
      },

      /**
       * Handle search input
       */
      handleInput() {
        clearTimeout(this.debounceTimer);

        if (this.query.length < this.minQueryLength) {
          this.results = [];
          return;
        }

        this.debounceTimer = setTimeout(() => {
          this.search();
        }, this.debounceTime);
      },

      /**
       * Perform search
       */
      async search() {
        this.isLoading = true;
        this.focusedIndex = -1;

        // Simulate API call with sample data
        // Replace with actual API call in production
        await new Promise(resolve => setTimeout(resolve, 200));

        const searchTerm = this.query.toLowerCase();
        this.results = this.sampleProducts.filter(
          product =>
            product.name.toLowerCase().includes(searchTerm) ||
            product.category.toLowerCase().includes(searchTerm)
        );

        this.isLoading = false;
      },

      /**
       * Handle keyboard navigation
       * @param {KeyboardEvent} e - Keyboard event
       */
      handleKeydown(e) {
        switch (e.key) {
          case 'ArrowDown':
            e.preventDefault();
            this.focusedIndex = Math.min(
              this.focusedIndex + 1,
              this.results.length - 1
            );
            break;

          case 'ArrowUp':
            e.preventDefault();
            this.focusedIndex = Math.max(this.focusedIndex - 1, -1);
            break;

          case 'Enter':
            e.preventDefault();
            if (this.focusedIndex >= 0 && this.results[this.focusedIndex]) {
              this.selectResult(this.results[this.focusedIndex]);
            }
            break;

          case 'Escape':
            Alpine.store('search').close();
            break;
        }
      },

      /**
       * Select a search result
       * @param {object} result - Selected result
       */
      selectResult(result) {
        // Navigate to product page
        window.location.href = `/products/${result.id}`;
        Alpine.store('search').close();
      },

      /**
       * Clear search
       */
      clear() {
        this.query = '';
        this.results = [];
        this.focusedIndex = -1;
      },

      /**
       * Check if result is focused
       * @param {number} index - Result index
       * @returns {boolean} Focused status
       */
      isFocused(index) {
        return this.focusedIndex === index;
      },

      /**
       * Highlight matching text
       * @param {string} text - Text to highlight
       * @returns {string} HTML with highlighted text
       */
      highlight(text) {
        if (!this.query) return text;
        const regex = new RegExp(`(${this.query})`, 'gi');
        return text.replace(regex, '<mark class="bg-accent-orange bg-opacity-20 text-dark-brown">$1</mark>');
      }
    }));
  });
}

export default initSearch;
