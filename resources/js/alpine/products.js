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
      sampleProducts: [
        { id: 12, product_id: 12, name: 'Fresh Farm Milk', price: 120, oldPrice: 150, category: 'Milk', badge: 'Fresh', image: 'https://images.unsplash.com/photo-1563636619-e9143da7973b?w=600' },
        { id: 13, product_id: 13, name: 'Organic Yogurt', price: 85, category: 'Yogurt', badge: 'Popular', image: 'https://images.unsplash.com/photo-1488477181946-6428a0291777?w=600' },
        { id: 14, product_id: 14, name: 'Brown Eggs', price: 350, category: 'Eggs', badge: 'New', image: 'https://images.unsplash.com/photo-1486297678162-eb2a19b0a32d?w=600' },
        { id: 1, product_id: 1, name: 'Fresh Milk', price: 180, category: 'Milk', image: 'https://images.unsplash.com/photo-1589985270826-4b7bb135bc9d?w=600' },
        { id: 2, product_id: 2, name: 'EGG', price: 220, category: 'Eggs', badge: 'Bestseller', image: 'https://images.unsplash.com/photo-1631452180519-c014fe946bc9d?w=600' },
        { id: 12, product_id: 12, name: 'Fresh Milk 500ml', price: 95, category: 'Milk', image: 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=600' }
      ],
      stockMap: {},

      init() {
        // Use sample products if none provided
        if (this.products.length === 0) {
          this.products = this.sampleProducts;
        }

        // Fetch stock status for all products
        this.fetchStockStatus();

        // Extract unique categories
        this.categories = [
          'all',
          ...new Set(this.products.map(p => p.category))
        ];

        // Initial filter
        this.applyFilters();
      },

      async fetchStockStatus() {
        try {
          // Fetch stock for all products from API (adjust endpoint as needed)
          const response = await fetch('/api/v1/products/stock', {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
          });
          const data = await response.json();
          if (data.success && data.stock) {
            // stock: { product_id: available_qty }
            this.stockMap = data.stock;
          }
        } catch (e) {
          // fallback: mark all as available
          this.products.forEach(p => { this.stockMap[p.product_id || p.id] = 99; });
        }
      },

      getStock(product) {
        const pid = product.product_id || product.id;
        return typeof this.stockMap[pid] === 'number' ? this.stockMap[pid] : 0;
      },
      isInStock(product) {
        return this.getStock(product) > 0;
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
