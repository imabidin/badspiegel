/**
 * Enhanced checkout button loading state management
 *
 * Features:
 * - Prevents multiple clicks during loading
 * - Timeout reset with fallback
 * - Error handling and recovery
 * - Memory leak prevention
 * - WooCommerce AJAX integration
 *
 * @version 2.5.0
 *
 * @note Seems to be final, no further changes if not needed
 */

// Configuration constants
const CONFIG = {
  TIMEOUT_DURATION: 10000, // 10 seconds timeout (consistent with comment)
  NAVIGATION_DELAY: 1000, // Delay before navigation (ms)
  LOADING_TEXT: "Kasse wird vorbereitet…", // Loading text (German)
  DEBUG: false, // Enable debug logging
};

// Debug helper
const debug = (...args) => {
  if (CONFIG.DEBUG) {
    console.log("[CHECKOUT-SPINNER]", ...args);
  }
};

// Global state management
let isInitialized = false;
let timeoutIds = new Set(); // Track all timeouts for cleanup

/**
 * Initialize checkout spinner functionality on DOM ready
 */
document.addEventListener("DOMContentLoaded", function () {
  debug("DOM loaded, initializing checkout spinner");
  initCheckoutSpinner();
});

/**
 * Cleanup timeouts on page unload to prevent memory leaks
 */
window.addEventListener("beforeunload", function () {
  debug("Page unloading, cleaning up timeouts");
  timeoutIds.forEach(id => clearTimeout(id));
  timeoutIds.clear();
});

/**
 * WooCommerce AJAX event integration (consolidated)
 */
document.addEventListener("DOMContentLoaded", function () {
  const wooEvents = ["updated_wc_div", "updated_cart_totals", "wc_fragments_refreshed"];

  wooEvents.forEach(eventName => {
    document.body.addEventListener(eventName, function () {
      debug(`WooCommerce ${eventName} detected, re-initializing`);
      initCheckoutSpinner();
    });
  });
});

/**
 * Initialize checkout spinner functionality with event delegation
 */
function initCheckoutSpinner() {
  debug("Initializing checkout spinner");

  // Prevent duplicate initialization
  if (isInitialized) {
    debug("Already initialized, skipping");
    return;
  }

  // Use event delegation to handle dynamically added buttons
  document.addEventListener("click", handleCheckoutClick, true);
  isInitialized = true;
}

/**
 * Enhanced checkout button click handler with error handling
 *
 * 1. Click → Set loading state
 * 2. Navigate after delay
 * 3. Reset after timeout with error recovery
 */
function handleCheckoutClick(event) {
  // Early return if not a checkout button
  if (!event.target.matches("a.checkout-button")) return;

  const checkoutButton = event.target;
  debug("Checkout button clicked");

  // Prevent multiple clicks during loading state
  if (checkoutButton.hasAttribute("data-loading")) {
    debug("Button already in loading state, ignoring click");
    return;
  }

  // Validate button state
  if (!checkoutButton.href) {
    debug("No href found, aborting");
    return;
  }

  // Store original state
  const originalContent = checkoutButton.innerHTML;
  const originalHref = checkoutButton.href;

  debug("Setting loading state");
  setLoadingState(checkoutButton, originalContent);

  // Navigate after delay with error handling
  const navigationTimeout = setTimeout(() => {
    try {
      debug("Navigating to:", originalHref);
      window.location.href = originalHref;
    } catch (error) {
      debug("Navigation error:", error);
      resetButton(checkoutButton, originalContent);
    }
  }, CONFIG.NAVIGATION_DELAY);

  timeoutIds.add(navigationTimeout);

  // Timeout reset with error recovery
  const resetTimeout = setTimeout(() => {
    debug("Timeout reached, resetting button");
    resetButton(checkoutButton, originalContent);
    timeoutIds.delete(navigationTimeout);
    timeoutIds.delete(resetTimeout);
  }, CONFIG.TIMEOUT_DURATION);

  timeoutIds.add(resetTimeout);
}

/**
 * Set button to loading state
 */
function setLoadingState(button, originalContent) {
  button.setAttribute("data-original-content", originalContent);

  const spinnerHTML = `
    <span role="status" aria-atomic="true">
      <span class="spinner-border me-2"
            style="--bs-spinner-width:1.25rem;
                   --bs-spinner-height:1.25rem;
                   --bs-spinner-border-width:0.225rem"
            aria-hidden="true">
      </span>
      <span>${CONFIG.LOADING_TEXT}</span>
    </span>
  `;

  button.innerHTML = spinnerHTML;
  button.setAttribute("aria-busy", "true");
  button.setAttribute("data-loading", "true");
  button.setAttribute("tabindex", "-1");
}

/**
 * Reset button to original state
 */
function resetButton(button, originalContent) {
  button.innerHTML = originalContent;
  button.setAttribute("aria-busy", "false");
  button.removeAttribute("data-loading");
  button.removeAttribute("data-original-content");
  button.removeAttribute("tabindex");
}
