/**
 * Enhanced SimpleBar Implementation with Custom Scroll Controls
 *
 * Provides an enhanced scrollable container system using SimpleBar with custom
 * navigation buttons, intelligent visibility management, and performance optimizations.
 * Includes responsive button positioning and accessibility features.
 *
 * Features:
 * - Custom scroll buttons with smooth scrolling
 * - Intelligent button visibility based on scroll position
 * - Debounced scroll event handling for performance
 * - ResizeObserver integration for responsive updates
 * - Touch device support with passive event listeners
 * - WooCommerce specific auto-hide behavior
 * - ARIA accessibility labels
 * - FontAwesome icon integration
 * - CSS class-based configuration system
 *
 * @version 2.3.0
 * @package BSAwesome SimpleBar
 * @requires SimpleBar library, FontAwesome icons
 * @updated 22/04/2025 - V3 optimization update
 *
 * Technical Implementation:
 * - Uses ES6 modules and imports SimpleBar library
 * - Implements debounced scroll handling for performance
 * - Uses ResizeObserver for container size changes
 * - Applies fade/unfade CSS classes for smooth transitions
 * - Supports both click and touch events
 *
 * Usage:
 * Add class "simplebar" to any container element.
 * Initialization happens automatically on DOM load.
 *
 * @example
 * <div class="simplebar">
 *   <div class="content">
 *     <!-- Scrollable content -->
 *   </div>
 * </div>
 *
 * @todo V3 Optimization: Show buttons when hovering the container
 */

import SimpleBar from "simplebar";
window.SimpleBar = SimpleBar;

// =============================================================================
// CONFIGURATION
// =============================================================================

/**
 * Scroll behavior configuration object
 * Centralizes all scroll-related settings for easy maintenance
 */
const SCROLL_CONFIG = {
  step: 400, // Scroll step in pixels per button click
  behavior: "smooth", // Scroll behavior ('smooth' or 'auto')
  debounceDelay: 100, // Delay for scroll events in ms (performance optimization)
  autoHide: true, // Hide scrollbar by default (can be overridden per container)
};

/**
 * CSS class constants for consistent styling
 * Prevents typos and centralizes class name management
 */
const CLASSES = {
  BTN_GROUP: "simplebar-btn-group", // Container for scroll buttons
  BTN_LEFT: "btn-left", // Left scroll button identifier
  BTN_RIGHT: "btn-right", // Right scroll button identifier
  BTN_COLOR: "btn-dark", // Bootstrap button color class
  FADE: "fade", // CSS class for hidden state
  UNFADE: "unfade", // CSS class for visible state
};

// =============================================================================
// INITIALIZATION
// =============================================================================

/**
 * Initialize SimpleBar for all containers after DOM is ready
 * Automatically finds and processes all elements with "simplebar" class
 */
document.addEventListener("DOMContentLoaded", () => {
  initAllSimpleBars();
});

/**
 * Initialize SimpleBar for all containers (can be called multiple times)
 * Useful for dynamically loaded content like AJAX-loaded cross-selling sections
 */
function initAllSimpleBars() {
  document.querySelectorAll(".simplebar").forEach((container) => {
    initCustomScroll(container);
  });
}

/**
 * Observe for new SimplBar containers added dynamically
 * Uses MutationObserver to detect new .simplebar elements
 */
const observer = new MutationObserver((mutations) => {
  mutations.forEach((mutation) => {
    mutation.addedNodes.forEach((node) => {
      if (node.nodeType === Node.ELEMENT_NODE) {
        // Check if the added node itself has simplebar class
        if (node.classList && node.classList.contains('simplebar')) {
          initCustomScroll(node);
        }
        // Check for simplebar elements within the added node
        node.querySelectorAll && node.querySelectorAll('.simplebar').forEach((container) => {
          initCustomScroll(container);
        });
      }
    });
  });
});

// Start observing for dynamically added content
observer.observe(document.body, {
  childList: true,
  subtree: true
});

// Global function for manual initialization (useful for AJAX callbacks)
window.initSimpleBars = initAllSimpleBars;

// =============================================================================
// CORE FUNCTIONS
// =============================================================================

/**
 * Main initialization function for custom scrollbar with navigation buttons
 *
 * @param {HTMLElement} container - The container element to enhance with SimpleBar
 * @description
 * 1. Prevents duplicate initialization with data attribute check
 * 2. Creates SimpleBar instance with conditional auto-hide behavior
 * 3. Adds custom scroll navigation buttons
 * 4. Sets up event listeners and observers
 */
function initCustomScroll(container) {
  // Prevent duplicate initialization
  if (container.dataset.simplebarInitialized) return;
  container.dataset.simplebarInitialized = "true";

  // Create SimpleBar instance with conditional configuration
  const simpleBarInstance = new SimpleBar(container, {
    // WooCommerce containers: always show scrollbar
    // Other containers: use configurable auto-hide behavior
    autoHide: container.classList.contains("woocommerce")
      ? false
      : SCROLL_CONFIG.autoHide,
    tabIndex: -1, // Remove from tab order for better accessibility
  });

  // Enhance with custom scroll buttons
  addScrollButtons(container, simpleBarInstance);
}

/**
 * Creates and configures scroll navigation buttons for a SimpleBar container
 *
 * @param {HTMLElement} container - The container element
 * @param {SimpleBar} simpleBarInstance - The SimpleBar instance
 * @description
 * 1. Injects HTML for left/right scroll buttons with accessibility
 * 2. Configures click and touch event handlers for scrolling
 * 3. Sets up visibility management based on scroll position
 * 4. Implements ResizeObserver for responsive button updates
 * 5. Applies debounced scroll event handling for performance
 */
function addScrollButtons(container, simpleBarInstance) {
  const scrollElement = simpleBarInstance.getScrollElement();

  // =============================================================================
  // BUTTON HTML INJECTION
  // =============================================================================

  // Insert scroll button HTML with accessibility attributes
  container.insertAdjacentHTML(
    "beforeend",
    `
        <div class="${CLASSES.BTN_GROUP}">
            <button class="btn ${CLASSES.BTN_COLOR} simplebar-btn ${CLASSES.BTN_LEFT} ${CLASSES.FADE}"
                    aria-label="Nach links scrollen">
                <i class="fa-sharp fa-light fa-chevron-left"></i>
            </button>
            <button class="btn ${CLASSES.BTN_COLOR} simplebar-btn ${CLASSES.BTN_RIGHT} ${CLASSES.FADE}"
                    aria-label="Nach rechts scrollen">
                <i class="fa-sharp fa-light fa-chevron-right"></i>
            </button>
        </div>
    `
  );

  // =============================================================================
  // BUTTON REFERENCES AND EVENT HANDLERS
  // =============================================================================

  // Get button references for event binding
  const scrollLeftBtn = container.querySelector(`.${CLASSES.BTN_LEFT}`);
  const scrollRightBtn = container.querySelector(`.${CLASSES.BTN_RIGHT}`);

  // Mouse click handlers for desktop interaction
  scrollLeftBtn.addEventListener("click", () => {
    scrollElement.scrollBy({
      left: -SCROLL_CONFIG.step,
      behavior: SCROLL_CONFIG.behavior,
    });
  });

  scrollRightBtn.addEventListener("click", () => {
    scrollElement.scrollBy({
      left: SCROLL_CONFIG.step,
      behavior: SCROLL_CONFIG.behavior,
    });
  });

  // =============================================================================
  // TOUCH SUPPORT FOR MOBILE DEVICES
  // =============================================================================

  // Touch event handlers with passive listeners for better performance
  scrollLeftBtn.addEventListener(
    "touchstart",
    () => {
      scrollElement.scrollBy({
        left: -SCROLL_CONFIG.step,
        behavior: SCROLL_CONFIG.behavior,
      });
    },
    { passive: true } // Improves scroll performance on touch devices
  );

  scrollRightBtn.addEventListener(
    "touchstart",
    () => {
      scrollElement.scrollBy({
        left: SCROLL_CONFIG.step,
        behavior: SCROLL_CONFIG.behavior,
      });
    },
    { passive: true } // Improves scroll performance on touch devices
  );

  // =============================================================================
  // VISIBILITY MANAGEMENT AND OBSERVERS
  // =============================================================================

  // Create debounced visibility update function for performance optimization
  const updateVisibility = debounce(() => {
    updateButtonVisibility(container, scrollElement);
  }, SCROLL_CONFIG.debounceDelay);

  // Bind scroll event with debounced handler
  scrollElement.addEventListener("scroll", updateVisibility);

  // Observe container size changes for responsive updates
  new ResizeObserver(updateVisibility).observe(container);

  // Perform initial visibility check
  updateVisibility();
}

// =============================================================================
// UTILITY FUNCTIONS
// =============================================================================

/**
 * Updates scroll button visibility based on current scroll position
 *
 * @param {HTMLElement} container - The container element
 * @param {HTMLElement} scrollElement - The scrollable element from SimpleBar
 * @description
 * Calculates scroll boundaries and toggles button visibility:
 * - Left button: visible when scrolled away from left edge
 * - Right button: visible when more content available to the right
 * Uses CSS classes for smooth fade transitions
 */
function updateButtonVisibility(container, scrollElement) {
  const scrollLeftBtn = container.querySelector(`.${CLASSES.BTN_LEFT}`);
  const scrollRightBtn = container.querySelector(`.${CLASSES.BTN_RIGHT}`);

  // Calculate scroll position boundaries
  const canScrollLeft = scrollElement.scrollLeft > 0;
  const canScrollRight =
    scrollElement.scrollLeft <
    scrollElement.scrollWidth - scrollElement.clientWidth;

  // Toggle visibility classes for smooth transitions
  scrollLeftBtn.classList.toggle(CLASSES.UNFADE, canScrollLeft);
  scrollRightBtn.classList.toggle(CLASSES.UNFADE, canScrollRight);
}

/**
 * Creates a debounced version of a function for performance optimization
 *
 * @param {Function} func - The function to debounce
 * @param {number} delay - Delay in milliseconds (default: 100ms)
 * @returns {Function} Debounced function that delays execution
 * @description
 * Prevents excessive function calls during rapid events (like scroll/resize).
 * Only executes the function after the specified delay has passed since
 * the last call, improving performance and preventing UI jank.
 *
 * @example
 * const debouncedScroll = debounce(handleScroll, 100);
 * element.addEventListener('scroll', debouncedScroll);
 */
function debounce(func, delay = 100) {
  let timeout;
  return (...args) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => func.apply(this, args), delay);
  };
}
