/**
 * Simple checkout button loading state management
 *
 * Features:
 * - Prevents multiple clicks during loading
 * - 20-second timeout reset
 * - Simple and reliable
 *
 * @version 2.3.0
 */

// Configuration constants
const CONFIG = {
  TIMEOUT_DURATION: 10000, // 10 seconds timeout
  NAVIGATION_DELAY: 150, // Delay before navigation (ms)
  LOADING_TEXT: "Kasse wird vorbereitet…",
  DEBUG: false, // Enable debug logging
};

// Debug helper
const debug = (...args) => {
  if (CONFIG.DEBUG) {
    console.log("[CHECKOUT-SPINNER]", ...args);
  }
};

/**
 * Initialize checkout spinner functionality on DOM ready
 */
document.addEventListener("DOMContentLoaded", function () {
  debug("DOM loaded, initializing checkout spinner");
  initCheckoutSpinner();
});

/**
 * Re-initialize after WooCommerce AJAX updates using vanilla JS
 */
document.addEventListener("DOMContentLoaded", function () {
  // Listen for WooCommerce AJAX events on document body
  document.body.addEventListener("updated_wc_div", function () {
    debug("WooCommerce updated_wc_div detected, re-initializing");
    initCheckoutSpinner();
  });

  document.body.addEventListener("updated_cart_totals", function () {
    debug("WooCommerce updated_cart_totals detected, re-initializing");
    initCheckoutSpinner();
  });

  document.body.addEventListener("wc_fragments_refreshed", function () {
    debug("WooCommerce wc_fragments_refreshed detected, re-initializing");
    initCheckoutSpinner();
  });
});

/**
 * Initialize checkout spinner functionality with event delegation
 */
function initCheckoutSpinner() {
  debug("Initializing checkout spinner");

  // Remove existing event listeners to prevent duplicates
  document.removeEventListener("click", handleCheckoutClick, true);

  // Use event delegation to handle dynamically added buttons
  document.addEventListener("click", handleCheckoutClick, true);
}

/**
 * Simple checkout button click handler
 *
 * 1. Click → Set loading state
 * 2. Navigate after delay
 * 3. Reset after 20 seconds (simple timeout)
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

  // Store original state
  const originalContent = checkoutButton.innerHTML;
  const originalHref = checkoutButton.href;

  debug("Setting loading state");

  // Store original content
  checkoutButton.setAttribute("data-original-content", originalContent);

  // Create spinner HTML
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

  // Set loading state
  checkoutButton.innerHTML = spinnerHTML;
  checkoutButton.setAttribute("aria-busy", "true");
  checkoutButton.setAttribute("data-loading", "true");
  // checkoutButton.style.pointerEvents = "none";
  checkoutButton.setAttribute("tabindex", "-1");

  // Navigate after delay
  setTimeout(() => {
    debug("Navigating to:", originalHref);
    window.location.href = originalHref;
  }, CONFIG.NAVIGATION_DELAY);

  // Simple timeout reset after 20 seconds
  setTimeout(() => {
    debug("20 second timeout reached, resetting button");

    // Reset button to original state
    checkoutButton.innerHTML = originalContent;
    checkoutButton.setAttribute("aria-busy", "false");
    checkoutButton.removeAttribute("data-loading");
    checkoutButton.removeAttribute("data-original-content");
    // checkoutButton.style.pointerEvents = "";
    checkoutButton.removeAttribute("tabindex");
  }, CONFIG.TIMEOUT_DURATION);
}
