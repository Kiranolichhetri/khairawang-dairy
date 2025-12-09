/**
 * Shopping Cart Alpine.js Store
 * Handles cart functionality with localStorage persistence
 * @module alpine/cart
 */

import { getItem, setItem } from '../utils/storage.js';
import { formatPrice } from '../utils/formatters.js';

const CART_STORAGE_KEY = 'khairawang_cart';
const FREE_SHIPPING_THRESHOLD = 1000;
const SHIPPING_COST = 100;

/**
 * Initialize the cart store
 */
export function initCartStore() {
  document.addEventListener('alpine:init', () => {
    // Load items from localStorage before creating the store
    const savedItems = getItem(CART_STORAGE_KEY, []);
    
    Alpine.store('cart', {
      items: savedItems,
      isOpen: false,
      isLoading: false,

      /**
       * Add a product to the cart
       * @param {object} product - Product to add
       * @param {number} qty - Quantity to add
       */
      add(product, qty = 1) {
        const existingIndex = this.items.findIndex(
          item => item.id === product.id
        );

        if (existingIndex > -1) {
          this.items[existingIndex].quantity += qty;
        } else {
          this.items.push({
            id: product.id,
            name: product.name,
            price: product.price,
            image: product.image,
            quantity: qty
          });
        }

        this.save();
        this.showNotification(`${product.name} added to cart`);
      },

      /**
       * Remove an item from the cart by index
       * @param {number} index - Index of item to remove
       */
      remove(index) {
        if (index >= 0 && index < this.items.length) {
          const item = this.items[index];
          this.items.splice(index, 1);
          this.save();
          this.showNotification(`${item.name} removed from cart`);
        }
      },

      /**
       * Update item quantity
       * @param {number} index - Index of item
       * @param {number} qty - New quantity
       */
      updateQuantity(index, qty) {
        if (index >= 0 && index < this.items.length) {
          if (qty <= 0) {
            this.remove(index);
          } else {
            this.items[index].quantity = qty;
            this.save();
          }
        }
      },

      /**
       * Increment item quantity
       * @param {number} index - Index of item
       */
      increment(index) {
        if (index >= 0 && index < this.items.length) {
          this.items[index].quantity++;
          this.save();
        }
      },

      /**
       * Decrement item quantity
       * @param {number} index - Index of item
       */
      decrement(index) {
        if (index >= 0 && index < this.items.length) {
          if (this.items[index].quantity > 1) {
            this.items[index].quantity--;
            this.save();
          } else {
            this.remove(index);
          }
        }
      },

      /**
       * Clear all items from cart
       */
      clear() {
        this.items = [];
        this.save();
        this.showNotification('Cart cleared');
      },

      /**
       * Get total item count
       * @returns {number} Total count of items
       */
      get count() {
        return this.items.reduce((sum, item) => sum + item.quantity, 0);
      },

      /**
       * Get cart subtotal
       * @returns {number} Subtotal amount
       */
      get subtotal() {
        return this.items.reduce(
          (sum, item) => sum + item.price * item.quantity,
          0
        );
      },

      /**
       * Get shipping cost (free above threshold)
       * @returns {number} Shipping cost
       */
      get shippingCost() {
        return this.subtotal >= FREE_SHIPPING_THRESHOLD ? 0 : SHIPPING_COST;
      },

      /**
       * Get cart total
       * @returns {number} Total amount
       */
      get total() {
        return this.subtotal + this.shippingCost;
      },

      /**
       * Check if cart is empty
       * @returns {boolean} Empty status
       */
      get isEmpty() {
        return this.items.length === 0;
      },

      /**
       * Get formatted subtotal
       * @returns {string} Formatted subtotal
       */
      get formattedSubtotal() {
        return formatPrice(this.subtotal);
      },

      /**
       * Get formatted shipping
       * @returns {string} Formatted shipping cost
       */
      get formattedShipping() {
        return this.shippingCost === 0 ? 'Free' : formatPrice(this.shippingCost);
      },

      /**
       * Get formatted total
       * @returns {string} Formatted total
       */
      get formattedTotal() {
        return formatPrice(this.total);
      },

      /**
       * Toggle cart drawer
       */
      toggle() {
        this.isOpen = !this.isOpen;
        document.body.classList.toggle('overflow-hidden', this.isOpen);
      },

      /**
       * Open cart drawer
       */
      open() {
        this.isOpen = true;
        document.body.classList.add('overflow-hidden');
      },

      /**
       * Close cart drawer
       */
      close() {
        this.isOpen = false;
        document.body.classList.remove('overflow-hidden');
      },

      /**
       * Save cart to localStorage
       */
      save() {
        setItem(CART_STORAGE_KEY, this.items);
      },

      /**
       * Show notification via toast store
       * @param {string} message - Notification message
       */
      showNotification(message) {
        if (Alpine.store('toast')) {
          Alpine.store('toast').show(message, 'success');
        }
      },

      /**
       * Sync cart with server and refresh
       */
      async refresh() {
        try {
          const response = await fetch('/api/v1/cart', {
            method: 'GET',
            headers: {
              'Accept': 'application/json'
            }
          });
          
          if (!response.ok) {
            console.error('Failed to fetch cart from server');
            if (Alpine.store('toast')) {
              Alpine.store('toast').show('Failed to refresh cart', 'error');
            }
            return;
          }
          
          const data = await response.json();
          
          if (data.success && data.data && data.data.items) {
            // Update items from server
            this.items = data.data.items.map(item => ({
              id: item.product_id,
              name: item.name,
              price: item.price,
              image: item.image,
              quantity: item.quantity
            }));
            this.save();
          }
        } catch (error) {
          console.error('Error refreshing cart:', error);
          if (Alpine.store('toast')) {
            Alpine.store('toast').show('Failed to refresh cart', 'error');
          }
        }
      },

      /**
       * Get CSRF token from meta tag
       * @returns {string} CSRF token or empty string
       */
      getCsrfToken() {
        const token = document.querySelector('meta[name="csrf-token"]');
        return token ? token.getAttribute('content') : '';
      },

      /**
       * Add product to cart via API (server-side)
       * @param {string|number} productId - Product ID
       * @param {number} quantity - Quantity to add
       * @param {string|number|null} variantId - Variant ID (optional)
       * @returns {Promise<object>} Response data
       */
      async addViaApi(productId, quantity = 1, variantId = null) {
        this.isLoading = true;
        
        try {
          const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          };
          
          // Add CSRF token if available
          const csrfToken = this.getCsrfToken();
          if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken;
          }
          
          const response = await fetch('/api/v1/cart/items', {
            method: 'POST',
            headers: headers,
            body: JSON.stringify({
              product_id: productId,
              quantity: quantity,
              variant_id: variantId
            })
          });
          
          const data = await response.json();
          
          if (data.success) {
            // Refresh cart from server
            await this.refresh();
            this.showNotification(data.message || 'Item added to cart');
          } else {
            if (Alpine.store('toast')) {
              Alpine.store('toast').show(data.message || 'Failed to add item', 'error');
            }
          }
          
          return data;
        } catch (error) {
          console.error('Error adding to cart:', error);
          if (Alpine.store('toast')) {
            Alpine.store('toast').show('Failed to add item to cart', 'error');
          }
          return { success: false, message: 'Network error' };
        } finally {
          this.isLoading = false;
        }
      }
    });
  });
}

export default initCartStore;
