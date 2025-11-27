/**
 * Tabs Alpine.js Component
 * Handles tabbed content navigation
 * @module alpine/tabs
 */

/**
 * Initialize the tabs component
 */
export function initTabs() {
  document.addEventListener('alpine:init', () => {
    Alpine.data('tabs', (options = {}) => ({
      activeTab: options.defaultTab || 0,
      tabs: [],

      init() {
        // Initialize tabs from DOM
        this.tabs = Array.from(
          this.$el.querySelectorAll('[role="tab"]')
        ).map((tab, index) => ({
          id: tab.getAttribute('aria-controls') || `panel-${index}`,
          label: tab.textContent,
          disabled: tab.disabled
        }));

        // Keyboard navigation
        this.$el.addEventListener('keydown', this.handleKeydown.bind(this));
      },

      /**
       * Select a tab
       * @param {number} index - Tab index
       */
      selectTab(index) {
        if (this.tabs[index] && !this.tabs[index].disabled) {
          this.activeTab = index;
          this.$dispatch('tab-change', { index, tab: this.tabs[index] });
        }
      },

      /**
       * Check if tab is active
       * @param {number} index - Tab index
       * @returns {boolean} Active status
       */
      isActive(index) {
        return this.activeTab === index;
      },

      /**
       * Handle keyboard navigation
       * @param {KeyboardEvent} e - Keyboard event
       */
      handleKeydown(e) {
        const tabList = e.target.closest('[role="tablist"]');
        if (!tabList) return;

        let newIndex = this.activeTab;

        switch (e.key) {
          case 'ArrowLeft':
            e.preventDefault();
            newIndex = this.activeTab > 0 ? this.activeTab - 1 : this.tabs.length - 1;
            break;

          case 'ArrowRight':
            e.preventDefault();
            newIndex = this.activeTab < this.tabs.length - 1 ? this.activeTab + 1 : 0;
            break;

          case 'Home':
            e.preventDefault();
            newIndex = 0;
            break;

          case 'End':
            e.preventDefault();
            newIndex = this.tabs.length - 1;
            break;

          default:
            return;
        }

        // Skip disabled tabs
        while (this.tabs[newIndex] && this.tabs[newIndex].disabled) {
          newIndex =
            e.key === 'ArrowLeft' || e.key === 'Home'
              ? newIndex - 1
              : newIndex + 1;
          if (newIndex < 0) newIndex = this.tabs.length - 1;
          if (newIndex >= this.tabs.length) newIndex = 0;
        }

        this.selectTab(newIndex);

        // Focus the new tab
        const tabs = this.$el.querySelectorAll('[role="tab"]');
        if (tabs[newIndex]) tabs[newIndex].focus();
      },

      /**
       * Get tab button attributes
       * @param {number} index - Tab index
       * @returns {object} ARIA attributes
       */
      tabAttrs(index) {
        return {
          'role': 'tab',
          'aria-selected': this.isActive(index),
          'aria-controls': this.tabs[index]?.id,
          'tabindex': this.isActive(index) ? 0 : -1
        };
      },

      /**
       * Get tab panel attributes
       * @param {number} index - Panel index
       * @returns {object} ARIA attributes
       */
      panelAttrs(index) {
        return {
          'role': 'tabpanel',
          'id': this.tabs[index]?.id,
          'aria-labelledby': `tab-${index}`,
          'hidden': !this.isActive(index)
        };
      }
    }));
  });
}

export default initTabs;
