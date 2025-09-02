/**
 * PayPal Messaging - Dynamic Price Observer Module
 *
 * This module monitors price changes on WooCommerce product pages and automatically
 * updates PayPal messaging components to reflect current product pricing. It uses
 * MutationObserver for real-time price tracking and maintains synchronization
 * between WooCommerce price display and PayPal promotional messaging.
 *
 * Features:
 * - Real-time price change detection with MutationObserver
 * - Automatic PayPal messaging data attribute updates
 * - German locale price parsing (comma decimal separator)
 * - WooCommerce price structure compatibility
 * - Performance-optimized DOM observation
 * - Graceful handling of missing DOM elements
 * - Regex-based price extraction with validation
 *
 * Use Cases:
 * - Product configurator price updates
 * - Dynamic pricing changes from options/variants
 * - Real-time promotional messaging accuracy
 * - Currency conversion updates
 *
 * @version 2.2.0
 * @package Product
 */

/**
 * Initialize PayPal price observer when DOM is ready
 * Sets up monitoring system for price changes and PayPal messaging updates
 */
document.addEventListener("DOMContentLoaded", () => {
  // ====================== DOM ELEMENT DISCOVERY ======================

  /**
   * PayPal messaging container element
   * Contains the data-pp-message attribute that triggers PayPal SDK messaging
   * @type {HTMLElement|null}
   */
  const paypalContainer = document.querySelector("[data-pp-message]");

  /**
   * WooCommerce price container element
   * Main price display element containing formatted price with currency
   * @type {HTMLElement|null}
   */
  const priceContainer = document.querySelector(
    ".woocommerce-Price-amount.amount"
  );

  /**
   * ELEMENT VALIDATION
   * =================
   * Early exit if required elements are not found in DOM
   * Prevents errors on pages without PayPal messaging or price display
   */
  if (!paypalContainer || !priceContainer) return;

  /**
   * Price value element (BDI - Bidirectional Isolate)
   * Contains the actual numeric price value within the price container
   * BDI element ensures proper text direction for international currencies
   * @type {HTMLElement|null}
   */
  const price = priceContainer.querySelector("bdi");

  // Exit if price element structure is invalid
  if (!price) return;

  // ====================== PRICE PARSING AND UPDATE LOGIC ======================

  /**
   * Extracts and updates price information for PayPal messaging
   * Parses German locale pricing format and updates PayPal data attributes
   * for accurate promotional messaging display
   */
  const updatePrice = () => {
    /**
     * GERMAN LOCALE PRICE EXTRACTION
     * =============================
     * Extracts numeric price value from German format (e.g., "199,95 €")
     * Uses regex pattern to capture digits with comma decimal separator
     *
     * Parsing Logic:
     * 1. Extract text content from price element
     * 2. Apply regex pattern: /(\d+,\d+)/ to find "number,decimals" format
     * 3. Convert comma to dot for JavaScript parseFloat compatibility
     * 4. Convert to string for PayPal data attribute
     */
    const integer = ((m = price.textContent.trim().match(/(\d+,\d+)/)?.[1]) =>
      m ? parseFloat(m.replace(",", ".")).toString() : null)();

    // Exit if price extraction failed (invalid format or missing price)
    if (!integer) return;

    /**
     * PAYPAL DATA ATTRIBUTE UPDATE
     * ===========================
     * Updates the data-pp-amount attribute that PayPal SDK uses
     * for calculating and displaying promotional messaging
     *
     * Example: "199,95" → "199.95" → PayPal messaging for €199.95
     */
    paypalContainer.dataset.ppAmount = integer;
  };

  // ====================== INITIAL PRICE SETUP ======================

  /**
   * OPTIONAL: Initial price setup on page load
   * ==================================
   * Currently commented out - uncomment if immediate price setting needed
   * Useful for pre-populated forms or cached price displays
   */
  // updatePrice(); // Initial call to set the price on page load

  // ====================== MUTATION OBSERVER SETUP ======================

  /**
   * Real-time price change monitoring system
   * Uses MutationObserver to detect any changes in price display
   * and automatically updates PayPal messaging accordingly
   *
   * Observer Configuration:
   * - childList: true    → Detects added/removed child elements
   * - subtree: true      → Monitors all descendant elements
   * - characterData: true → Detects text content changes
   *
   * This comprehensive monitoring ensures price updates are captured
   * regardless of how WooCommerce or other plugins modify the price display
   */
  new MutationObserver(updatePrice).observe(price, {
    childList: true, // Monitor child element changes
    subtree: true, // Monitor all descendants
    characterData: true, // Monitor text content changes
  });
});

/**
 * Future Enhancement Ideas (V2 Optimization):
 *
 * 1. Multiple currency support:
 *    const currencyParsers = {
 *      EUR: (text) => text.match(/(\d+,\d+)/)?.[1]?.replace(',', '.'),
 *      USD: (text) => text.match(/(\d+\.\d+)/)?.[1],
 *      GBP: (text) => text.match(/(\d+\.\d+)/)?.[1]
 *    };
 *
 * 2. Error handling and logging:
 *    function logPriceUpdateError(error, priceText) {
 *      console.warn('[PayPal] Price update failed:', error, priceText);
 *    }
 *
 * 3. Debounced updates for rapid price changes:
 *    const debouncedUpdate = debounce(updatePrice, 300);
 *    new MutationObserver(debouncedUpdate).observe(price, config);
 *
 * 4. PayPal SDK integration validation:
 *    function validatePayPalIntegration() {
 *      return window.paypal && paypalContainer.dataset.ppMessage;
 *    }
 *
 * 5. Price format validation:
 *    function validatePriceFormat(priceText) {
 *      const validPatterns = [/\d+,\d{2}/, /\d+\.\d{2}/];
 *      return validPatterns.some(pattern => pattern.test(priceText));
 *    }
 */
