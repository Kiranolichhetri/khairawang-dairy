/**
 * Products Alpine.js Component
 * Handles product listing, filtering, and interactions
 * @module alpine/products
 */

import { formatPrice } from '../utils/formatters.js';

/**
 * Initialize the products component
 */
export function initProducts() {
  document.addEventListener('alpine:init', () => {
    Alpine.data('productGrid', (options = {}) => ({
      products: options.products || [],
      filteredProducts: [],
      categories: [],
      selectedCategory: 'all',
      sortBy: 'default',
      viewMode: 'grid', // 'grid' or 'list'
      isLoading: false,

      // Sample products for demo
      sampleProducts: [
        { id: 1, name: 'Fresh Farm Milk', price: 120, oldPrice: 150, category: 'Milk', badge: 'Fresh', image: 'https://images.unsplash.com/photo-1563636619-e9143da7973b?w=600' },
        { id: 2, name: 'Organic Yogurt', price: 85, category: 'Yogurt', badge: 'Popular', image: 'https://images.unsplash.com/photo-1488477181946-6428a0291777?w=600' },
        { id: 3, name: 'Artisan Cheese', price: 350, category: 'Cheese', badge: 'New', image: 'https://images.unsplash.com/photo-1486297678162-eb2a19b0a32d?w=600' },
        { id: 4, name: 'Fresh Butter', price: 180, category: 'Butter', image: 'https://images.unsplash.com/photo-1589985270826-4b7bb135bc9d?w=600' },
        { id: 5, name: 'Fresh Paneer', price: 220, category: 'Paneer', badge: 'Bestseller', image: 'https://images.unsplash.com/photo-1631452180519-c014fe946bc7?w=600' },
        { id: 6, name: 'Fresh Cream', price: 95, category: 'Cream', image: 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=600' }
      ],

      init() {
        // Use sample products if none provided
        if (this.products.length === 0) {
          this.products = this.sampleProducts;
        }

        // Extract unique categories
        this.categories = [
          'all',
          ...new Set(this.products.map(p => p.category))
        ];

        // Initial filter
        this.applyFilters();
      },

      /**
       * Apply filters and sorting
       */
      applyFilters() {
        let result = [...this.products];

        // Category filter
        if (this.selectedCategory !== 'all') {
          result = result.filter(p => p.category === this.selectedCategory);
        }

        // Sorting
        switch (this.sortBy) {
          case 'price-low':
            result.sort((a, b) => a.price - b.price);
            break;
          case 'price-high':
            result.sort((a, b) => b.price - a.price);
            break;
          case 'name':
            result.sort((a, b) => a.name.localeCompare(b.name));
            break;
          case 'newest':
            result.sort((a, b) => (b.id || 0) - (a.id || 0));
            break;
        }

        this.filteredProducts = result;
      },

      /**
       * Set category filter
       * @param {string} category - Category to filter
       */
      setCategory(category) {
        this.selectedCategory = category;
        this.applyFilters();
      },

      /**
       * Set sort option
       * @param {string} sort - Sort option
       */
      setSort(sort) {
        this.sortBy = sort;
        this.applyFilters();
      },

      /**
       * Toggle view mode
       */
      toggleView() {
        this.viewMode = this.viewMode === 'grid' ? 'list' : 'grid';
      },

      /**
       * Add product to cart
       * @param {object} product - Product to add
       */
      addToCart(product) {
        if (Alpine.store('cart')) {
          Alpine.store('cart').add(product);
        }
      },

      /**
       * Format price
       * @param {number} price - Price to format
       * @returns {string} Formatted price
       */
      formatPrice(price) {
        return formatPrice(price);
      },

      /**
       * Check if category is active
       * @param {string} category - Category to check
       * @returns {boolean} Active status
       */
      isActiveCategory(category) {
        return this.selectedCategory === category;
      },

      /**
       * Get grid columns class based on view mode
       * @returns {string} Tailwind grid classes
       */
      get gridClasses() {
        return this.viewMode === 'grid'
          ? 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6'
          : 'flex flex-col gap-4';
      }
    }));

    // Product card component
    Alpine.data('productCard', (product = {}) => ({
      product,
      isHovered: false,
      isQuickViewOpen: false,

      /**
       * Add to cart
       */
      addToCart() {
        if (Alpine.store('cart')) {
          Alpine.store('cart').add(this.product);
        }
      },

      /**
       * Open quick view modal
       */
      openQuickView() {
        this.isQuickViewOpen = true;
        this.$dispatch('open-quick-view', this.product);
      },

      /**
       * Format price
       * @param {number} price - Price to format
       * @returns {string} Formatted price
       */
      formatPrice(price) {
        return formatPrice(price);
      }
    }));
  });
}

export default initProducts;
