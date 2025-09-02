/**
 * SimpleBar implementation
 * with scroll buttons, debouncing and ResizeObserver
 *
 * Updated 22/04/2025 - optimize in V3 update
 *
 * Optimization: Show buttons when you hover the container
 */

import SimpleBar from "simplebar";
window.SimpleBar = SimpleBar;

// Configuration
const SCROLL_CONFIG = {
  step: 400, // Scroll step in pixels
  behavior: "smooth", // Scroll behavior ('smooth' or 'auto')
  debounceDelay: 100, // Delay for scroll events in ms
  autoHide: true, // Hide scrollbar by default
};

// CSS classes as constants
const CLASSES = {
  BTN_GROUP: "simplebar-btn-group",
  BTN_LEFT: "btn-left",
  BTN_RIGHT: "btn-right",
  BTN_COLOR: "btn-dark",
  FADE: "fade",
  UNFADE: "unfade",
};

// Initialize after DOM load
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".simplebar").forEach((container) => {
    initCustomScroll(container);
  });
});

/**
 * Main function to initialize custom scrollbar
 * @param {HTMLElement} container - Container element
 */
function initCustomScroll(container) {
  // Check if already initialized
  if (container.dataset.simplebarInitialized) return;
  container.dataset.simplebarInitialized = "true";

  // Create SimpleBar instance
  const simpleBarInstance = new SimpleBar(container, {
    autoHide: container.classList.contains("woocommerce")
      ? false
      : SCROLL_CONFIG.autoHide,
    tabIndex: -1,
  });

  // Add scroll buttons
  addScrollButtons(container, simpleBarInstance);
}

/**
 * Adds scroll buttons and binds events
 * @param {HTMLElement} container - Container element
 * @param {SimpleBar} simpleBarInstance - SimpleBar instance
 */
function addScrollButtons(container, simpleBarInstance) {
  const scrollElement = simpleBarInstance.getScrollElement();

  // Insert button HTML
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

  // Button references
  const scrollLeftBtn = container.querySelector(`.${CLASSES.BTN_LEFT}`);
  const scrollRightBtn = container.querySelector(`.${CLASSES.BTN_RIGHT}`);

  // Click handlers
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

  // Touch support
  scrollLeftBtn.addEventListener(
    "touchstart",
    () => {
      scrollElement.scrollBy({
        left: -SCROLL_CONFIG.step,
        behavior: SCROLL_CONFIG.behavior,
      });
    },
    { passive: true }
  );

  scrollRightBtn.addEventListener(
    "touchstart",
    () => {
      scrollElement.scrollBy({
        left: SCROLL_CONFIG.step,
        behavior: SCROLL_CONFIG.behavior,
      });
    },
    { passive: true }
  );

  // Initial visibility check
  const updateVisibility = debounce(() => {
    updateButtonVisibility(container, scrollElement);
  }, SCROLL_CONFIG.debounceDelay);

  // Bind events
  scrollElement.addEventListener("scroll", updateVisibility);
  new ResizeObserver(updateVisibility).observe(container);

  // Initial check
  updateVisibility();
}

/**
 * Updates scroll button visibility
 * @param {HTMLElement} container - Container element
 * @param {HTMLElement} scrollElement - Scrollable element
 */
function updateButtonVisibility(container, scrollElement) {
  const scrollLeftBtn = container.querySelector(`.${CLASSES.BTN_LEFT}`);
  const scrollRightBtn = container.querySelector(`.${CLASSES.BTN_RIGHT}`);

  const canScrollLeft = scrollElement.scrollLeft > 0;
  const canScrollRight =
    scrollElement.scrollLeft <
    scrollElement.scrollWidth - scrollElement.clientWidth;

  scrollLeftBtn.classList.toggle(CLASSES.UNFADE, canScrollLeft);
  scrollRightBtn.classList.toggle(CLASSES.UNFADE, canScrollRight);
}

/**
 * Debounce function for performance optimization
 * @param {Function} func - Function to execute
 * @param {number} delay - Delay in ms
 * @returns {Function} Debounced function
 */
function debounce(func, delay = 100) {
  let timeout;
  return (...args) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => func.apply(this, args), delay);
  };
}
