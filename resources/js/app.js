/**
 * KHAIRAWANG DAIRY - Main JavaScript Entry Point
 * Initializes Alpine.js and all components
 */

// Import Alpine.js
import Alpine from 'alpinejs';

// Import Alpine components
import { initCartStore } from './alpine/cart.js';
import { initNavigation } from './alpine/navigation.js';
import { initModal } from './alpine/modal.js';
import { initDropdown } from './alpine/dropdown.js';
import { initTabs } from './alpine/tabs.js';
import { initToast } from './alpine/toast.js';
import { initGallery } from './alpine/gallery.js';
import { initSearch } from './alpine/search.js';
import { initProducts } from './alpine/products.js';

// Import utilities for global access
import * as storage from './utils/storage.js';
import * as formatters from './utils/formatters.js';
import * as api from './utils/api.js';

// Make Alpine available globally
window.Alpine = Alpine;

// Make utilities available globally
window.KhairawangDairy = {
  storage,
  formatters,
  api
};

// Initialize all Alpine components before starting
initToast();        // Toast should be first for notifications
initCartStore();    // Cart store
initNavigation();   // Navigation component
initModal();        // Modal component
initDropdown();     // Dropdown component
initTabs();         // Tabs component
initGallery();      // Gallery component
initSearch();       // Search component
initProducts();     // Products component

// Start Alpine.js
Alpine.start();

// Log initialization in development
if (import.meta.env.DEV) {
  console.log('🥛 KHAIRAWANG DAIRY - Alpine.js initialized');
}
