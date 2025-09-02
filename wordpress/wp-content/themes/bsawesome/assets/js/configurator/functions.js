/**
 * Product Configurator - Utility Functions Module
 *
 * This module provides essential utility functions used throughout the product
 * configurator system. It includes number parsing, price formatting, DOM
 * manipulation helpers, and performance optimization utilities.
 *
 * @version 2.2.0
 * @package Configurator
 */

/**
 * Creates a debounced version of a function that delays execution
 * until after the specified delay has elapsed since the last invocation
 *
 * @param {Function} fn - The function to debounce
 * @param {number} delay - The delay in milliseconds (default: 200)
 * @returns {Function} The debounced function
 *
 * @example
 * const debouncedSearch = debounce(performSearch, 300);
 * input.addEventListener('input', debouncedSearch);
 */
export function debounce(fn, delay = 200) {
  let timeout;
  return (...args) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => fn(...args), delay);
  };
}

/**
 * Safely parses strings into numbers with German locale support
 * Handles German decimal notation (comma) and currency symbols
 *
 * @param {string|number} value - The value to convert to number
 * @returns {number} Parsed number or 0 if invalid/NaN
 *
 * @example
 * toNumber("12,50 €")    // Returns 12.5
 * toNumber("1.234,56")   // Returns 1234.56
 * toNumber("invalid")    // Returns 0
 */
export function toNumber(value) {
  // Replace comma with dot and remove all non-digit or non-dot characters
  const sanitized = value?.replace(",", ".").replace(/[^\d.]/g, "") || "";
  const number = parseFloat(sanitized);
  return isNaN(number) ? 0 : number;
}

/**
 * Rounds a number up to the next multiple of the specified step
 * Useful for price calculations and quantity adjustments
 *
 * @param {number} value - The value to round up
 * @param {number} step - The step to round up to (default: 100)
 * @returns {number} The rounded up value
 *
 * @example
 * roundUp(150, 100)  // Returns 200
 * roundUp(99, 50)    // Returns 100
 * roundUp(200, 100)  // Returns 200
 */
export function roundUp(value, step = 100) {
  return Math.ceil(value / step) * step;
}

/**
 * Formats a numeric price value to German currency format
 * Returns empty string for zero values to handle optional pricing
 *
 * @param {string|number} price - The price value to format
 * @returns {string} Formatted price string or empty string for zero values
 *
 * @example
 * formatPrice(12.5)     // Returns "12,50 €"
 * formatPrice("15.99")  // Returns "15,99 €"
 * formatPrice(0)        // Returns ""
 * formatPrice("0,00")   // Returns ""
 */
export function formatPrice(price) {
  const numericPrice = toNumber(String(price));
  return numericPrice === 0
    ? ""
    : `${numericPrice.toFixed(2).replace(".", ",")} €`;
}

/**
 * Retrieves the product title from the current page
 * Searches for common product title selectors used in the theme
 *
 * @returns {string} Product title text or empty string if not found
 *
 * @example
 * const title = getProductTitle(); // "Badezimmerspiegel Premium"
 */
export function getProductTitle() {
  const titleEl =
    document.querySelector(".product-titel") ||
    document.querySelector(".product_title");
  return titleEl ? titleEl.textContent.trim() : "";
}

/**
 * Retrieves the base product price from the current page
 * Searches for common price selectors and parses the numeric value
 *
 * @returns {number} Product price as number or 0 if not found
 *
 * @example
 * const price = getProductPrice(); // 199.99
 */
export function getProductPrice() {
  const priceEl =
    document.querySelector(".product-price") ||
    document.querySelector(".price");
  return priceEl ? toNumber(priceEl.textContent.trim()) : 0;
}

/**
 * Updates a select element value and triggers a change event
 * Ensures proper event propagation for form validation and listeners
 *
 * @param {HTMLSelectElement} select - The select element to update
 * @param {HTMLOptionElement} option - The option to select
 *
 * @example
 * const select = document.querySelector('#size-select');
 * const option = select.querySelector('option[value="large"]');
 * updateSelectAndTrigger(select, option);
 */
export function updateSelectAndTrigger(select, option) {
  select.value = option.value;

  // Synchronize data-price attribute for display systems
  select.setAttribute("data-price", option.getAttribute("data-price") || "0");

  // Trigger change event with bubbling for dependent systems (summary updates, etc.)
  const event = new Event("change", { bubbles: true });
  select.dispatchEvent(event);
}

/**
 * Updates the value label and button state based on current selection
 * Handles special cases like "none" selections and price display logic
 *
 * @param {HTMLInputElement} input - The selected input element
 * @param {HTMLElement} valueLabel - The label element to update with text
 * @param {HTMLElement} button - The toggle button to update classes on
 * @param {Object} mgr - Manager object containing component state
 * @param {boolean} mgr.valueChanged - Flag to track if value has changed
 *
 * @example
 * updateValueLabel(radioInput, labelElement, toggleButton, managerObject);
 */
export function updateValueLabel(input, valueLabel, button, mgr) {
  const selLabel = input.closest("label");
  const text = input.dataset.label || "";
  let price = input.dataset.price || "0";

  // Determine selection state
  const isNone = input.value === "";
  const isOverride =
    isNone && selLabel.classList.contains("use-first-image-for-none");
  const treatAsValue = !isNone || isOverride;

  if (treatAsValue) {
    // For none-override cases, hide the price display
    const displayPrice = isOverride ? "" : price;
    valueLabel.textContent =
      displayPrice && displayPrice !== "0"
        ? `${text} (+${displayPrice} €)`
        : text;
    valueLabel.classList.add("fade", "show");
  } else {
    // Clear label for empty selections
    valueLabel.textContent = "";
    valueLabel.classList.remove("show");
  }

  // Update button visual state classes
  button.classList.remove("no-selection", "on-selection", "yes-selection");
  button.classList.add(treatAsValue ? "yes-selection" : "no-selection");

  // Mark that value has been changed for manager tracking
  mgr.valueChanged = true;
}

/**
 * Additional utility functions for future use
 * These functions can be uncommented and used as needed
 */

/**
 * Throttles function execution to improve performance
 * Unlike debounce, throttle ensures function runs at regular intervals
 *
 * @param {Function} fn - Function to throttle
 * @param {number} limit - Time limit in milliseconds
 * @returns {Function} Throttled function
 */
/*
export function throttle(fn, limit = 100) {
  let inThrottle;
  return function(...args) {
    if (!inThrottle) {
      fn.apply(this, args);
      inThrottle = true;
      setTimeout(() => inThrottle = false, limit);
    }
  };
}
*/

/**
 * Deep clones an object (simple implementation)
 *
 * @param {Object} obj - Object to clone
 * @returns {Object} Cloned object
 */
/*
export function deepClone(obj) {
  return JSON.parse(JSON.stringify(obj));
}
*/

/**
 * Validates email format using regex
 *
 * @param {string} email - Email address to validate
 * @returns {boolean} True if valid email format
 */
/*
export function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}
*/

/**
 * Formats file size in human readable format
 *
 * @param {number} bytes - File size in bytes
 * @returns {string} Formatted file size (e.g., "1.2 MB")
 */
/*
export function formatFileSize(bytes) {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
*/
