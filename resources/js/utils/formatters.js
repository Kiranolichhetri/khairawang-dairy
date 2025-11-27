/**
 * Formatting utilities for price, date, and text
 * @module utils/formatters
 */

/**
 * Format price in Nepali Rupees
 * @param {number} amount - The amount to format
 * @param {string} currency - Currency code (default: NPR)
 * @returns {string} Formatted price string
 */
export function formatPrice(amount, currency = 'NPR') {
  if (typeof amount !== 'number' || isNaN(amount)) {
    return `${currency} 0`;
  }
  
  // Format with thousand separators
  const formatted = amount.toLocaleString('en-NP', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2
  });
  
  return `${currency} ${formatted}`;
}

/**
 * Format date in a readable format
 * @param {Date|string} date - The date to format
 * @param {string} format - Format type: 'short', 'long', 'relative'
 * @returns {string} Formatted date string
 */
export function formatDate(date, format = 'short') {
  const dateObj = date instanceof Date ? date : new Date(date);
  
  if (isNaN(dateObj.getTime())) {
    return 'Invalid date';
  }
  
  switch (format) {
    case 'long':
      return dateObj.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    
    case 'relative':
      return getRelativeTime(dateObj);
    
    case 'short':
    default:
      return dateObj.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
  }
}

/**
 * Get relative time string (e.g., "2 hours ago")
 * @param {Date} date - The date to compare
 * @returns {string} Relative time string
 */
export function getRelativeTime(date) {
  const now = new Date();
  const diffMs = now - date;
  const diffSec = Math.floor(diffMs / 1000);
  const diffMin = Math.floor(diffSec / 60);
  const diffHour = Math.floor(diffMin / 60);
  const diffDay = Math.floor(diffHour / 24);
  
  if (diffSec < 60) return 'Just now';
  if (diffMin < 60) return `${diffMin} minute${diffMin > 1 ? 's' : ''} ago`;
  if (diffHour < 24) return `${diffHour} hour${diffHour > 1 ? 's' : ''} ago`;
  if (diffDay < 7) return `${diffDay} day${diffDay > 1 ? 's' : ''} ago`;
  
  return formatDate(date, 'short');
}

/**
 * Truncate text with ellipsis
 * @param {string} text - The text to truncate
 * @param {number} maxLength - Maximum length
 * @returns {string} Truncated text
 */
export function truncateText(text, maxLength = 100) {
  if (!text || text.length <= maxLength) return text;
  return text.slice(0, maxLength).trim() + '...';
}

/**
 * Slugify a string for URLs
 * @param {string} text - The text to slugify
 * @returns {string} URL-safe slug
 */
export function slugify(text) {
  return text
    .toLowerCase()
    .trim()
    .replace(/[^\w\s-]/g, '')
    .replace(/[\s_-]+/g, '-')
    .replace(/^-+|-+$/g, '');
}

/**
 * Format phone number
 * @param {string} phone - The phone number
 * @returns {string} Formatted phone number
 */
export function formatPhone(phone) {
  if (!phone) return '';
  // Remove all non-digit characters
  const digits = phone.replace(/\D/g, '');
  // Format for Nepal numbers
  if (digits.length === 10) {
    return `${digits.slice(0, 3)} ${digits.slice(3, 6)} ${digits.slice(6)}`;
  }
  return phone;
}

export default {
  formatPrice,
  formatDate,
  getRelativeTime,
  truncateText,
  slugify,
  formatPhone
};
