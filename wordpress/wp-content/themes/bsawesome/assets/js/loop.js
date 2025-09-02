/**
 * Product Hover Image JavaScript
 *
 * Enhances the product hover image functionality with preloading,
 * lazy loading optimization, and intelligent image mode toggling.
 *
 * @package BSAwesome
 * @subpackage Assets
 * @since 1.0.0
 * @version 3.0.0 - DRY Refactor
 */

/**
 * Product Hover Image Handler
 */
const ProductHoverImage = {
  // Configuration constants
  CONSTANTS: {
    DOUBLE_TAP_TIMEOUT: 500,
    AUTO_HIDE_TIMEOUT: 3000,
    TRANSITION_DELAY: 50,
    STORAGE_KEY: 'bsawesome_image_mode',
    MODES: {
      MAIN: 'main',
      HOVER: 'hover'
    },
    SELECTORS: {
      PRODUCT_IMAGE: '.product-image-main',
      PRODUCT_CONTAINER: '.card-img',
      PRODUCT_HOVER: '.product-image-hover',
      IMAGE_TOGGLE: '[data-js="image-mode-toggle"]',
      TOGGLE_BUTTON: '[data-js="image-mode-toggle"] button'
    },
    CLASSES: {
      HOVER: 'hover',
      TOUCH_ACTIVE: 'touch-active',
      HOVER_TEMP_REMOVED: 'hover-temp-removed',
      IMAGE_MODE_MAIN: 'image-mode-main',
      IMAGE_MODE_HOVER: 'image-mode-hover',
      HOVER_IMAGE_LOADED: 'hover-image-loaded',
      DEVICE_TOUCH_ONLY: 'touch-only-device',
      DEVICE_HOVER_CAPABLE: 'hover-capable-device',
      DEVICE_HYBRID: 'hybrid-device'
    }
  },

  /**
   * Initialize the hover image functionality
   */
  init: function () {
    this.detectTouchDevice();
    this.restoreImageModeFromStorage();
    this.bindEvents();
    this.preloadHoverImages();
  },

  /**
   * Detect if we're on a touch device and set appropriate behavior
   */
  detectTouchDevice: function () {
    // Modern way to detect touch capability
    this.isTouchDevice = 
      ('ontouchstart' in window) || 
      (navigator.maxTouchPoints > 0) || 
      (navigator.msMaxTouchPoints > 0);
    
    // Check for hover capability
    this.hasHoverCapability = window.matchMedia('(hover: hover)').matches;
    this.hasCoarsePointer = window.matchMedia('(pointer: coarse)').matches;
    
    // Set device class on body for CSS targeting
    this.setDeviceClass();
  },

  /**
   * Set appropriate device class on body
   */
  setDeviceClass: function () {
    const { CLASSES } = this.CONSTANTS;
    
    if (this.isTouchDevice && !this.hasHoverCapability) {
      $('body').addClass(CLASSES.DEVICE_TOUCH_ONLY);
    } else if (this.hasHoverCapability && !this.hasCoarsePointer) {
      $('body').addClass(CLASSES.DEVICE_HOVER_CAPABLE);
    } else {
      $('body').addClass(CLASSES.DEVICE_HYBRID);
    }
  },

  /**
   * Restore image mode from localStorage after page navigation
   */
  restoreImageModeFromStorage: function () {
    const { STORAGE_KEY, MODES } = this.CONSTANTS;
    
    try {
      const savedMode = localStorage.getItem(STORAGE_KEY);
      const isValidMode = Object.values(MODES).includes(savedMode);
      
      if (savedMode && isValidMode) {
        this.setImageMode(savedMode);
        this.updateToggleButtons(savedMode);
      } else {
        this.setImageMode(MODES.MAIN);
      }
    } catch (e) {
      this.setImageMode(MODES.MAIN);
    }
  },

  /**
   * Save current mode to localStorage
   */
  saveImageModeToStorage: function (mode) {
    try {
      localStorage.setItem(this.CONSTANTS.STORAGE_KEY, mode);
    } catch (e) {
      // localStorage not available, continue without saving
    }
  },

  /**
   * Update toggle button states to match the current mode
   */
  updateToggleButtons: function (mode) {
    const $toggleGroup = $(this.CONSTANTS.SELECTORS.IMAGE_TOGGLE);
    
    // Update button states
    $toggleGroup
      .find("button")
      .removeClass("active")
      .attr("aria-pressed", "false");

    $toggleGroup
      .find(`button[data-mode="${mode}"]`)
      .addClass("active")
      .attr("aria-pressed", "true");
  },

  /**
   * Bind event handlers
   */
  bindEvents: function () {
    // Handle image mode toggle buttons
    $(document).on("click", this.CONSTANTS.SELECTORS.TOGGLE_BUTTON, (e) => {
      e.preventDefault();
      this.handleImageModeToggle($(e.currentTarget));
    });

    // Device-specific event binding
    if (this.isTouchDevice && !this.hasHoverCapability) {
      this.bindTouchEvents();
    } else {
      this.bindHoverEvents();
    }
  },

  /**
   * Bind touch-specific events for touch-only devices
   */
  bindTouchEvents: function () {
    const { SELECTORS, CLASSES, MODES } = this.CONSTANTS;

    // Only prevent hover classes in "Seite" mode, not in "Front" mode
    $(document).on("touchstart touchend", SELECTORS.PRODUCT_IMAGE, (e) => {
      const $this = $(e.currentTarget);
      
      // Only remove hover class if we're in "Seite" mode
      if (this.getCurrentImageMode() === MODES.MAIN) {
        $this.removeClass(CLASSES.HOVER);
      }
      // In "Front" mode, keep the hover class to maintain the front image display
    });

    // Simple touch/click handler
    $(document).on("touchstart click", SELECTORS.PRODUCT_IMAGE, (e) => {
      const $this = $(e.currentTarget);
      
      // In "Front" mode, always allow immediate navigation
      if (this.getCurrentImageMode() === MODES.HOVER) {
        return; // Let the browser handle the link normally
      }
      
      // In "Seite" mode, allow immediate navigation (no hover preview required)
      // The hover preview is only for desktop hover, not mobile touch
      return; // Let the browser handle the link normally
    });

    // Optional: Long press could show hover preview in "Seite" mode
    let longPressTimer;
    
    $(document).on("touchstart", SELECTORS.PRODUCT_IMAGE, (e) => {
      const $this = $(e.currentTarget);
      
      // Only in "Seite" mode, allow long press for preview
      if (this.getCurrentImageMode() === MODES.MAIN) {
        longPressTimer = setTimeout(() => {
          // Show hover preview on long press
          $(SELECTORS.PRODUCT_IMAGE).removeClass(CLASSES.TOUCH_ACTIVE);
          $this.addClass(CLASSES.TOUCH_ACTIVE);
          
          // Auto-hide after shorter timeout
          setTimeout(() => {
            $this.removeClass(CLASSES.TOUCH_ACTIVE);
          }, 2000);
        }, 500); // 500ms long press
      }
    });
    
    $(document).on("touchend touchcancel", SELECTORS.PRODUCT_IMAGE, (e) => {
      // Clear long press timer
      if (longPressTimer) {
        clearTimeout(longPressTimer);
        longPressTimer = null;
      }
    });

    // Remove touch-active when touching outside
    $(document).on("touchstart", (e) => {
      if (!$(e.target).closest(SELECTORS.PRODUCT_IMAGE).length) {
        $(SELECTORS.PRODUCT_IMAGE).removeClass(CLASSES.TOUCH_ACTIVE);
      }
    });
  },

  /**
   * Bind hover events for hover-capable devices
   */
  bindHoverEvents: function () {
    const { SELECTORS, CLASSES } = this.CONSTANTS;

    // Handle hover with temporary removal for JS-controlled states
    $(document).on("mouseenter", `${SELECTORS.PRODUCT_IMAGE}.${CLASSES.HOVER}`, (e) => {
      const $this = $(e.currentTarget);
      $this.removeClass(CLASSES.HOVER).addClass(CLASSES.HOVER_TEMP_REMOVED);
    });

    $(document).on("mouseleave", `${SELECTORS.PRODUCT_IMAGE}.${CLASSES.HOVER_TEMP_REMOVED}`, (e) => {
      const $this = $(e.currentTarget);
      
      if (this.isImageModeToggled()) {
        $this.removeClass(CLASSES.HOVER_TEMP_REMOVED).addClass(CLASSES.HOVER);
      } else {
        $this.removeClass(CLASSES.HOVER_TEMP_REMOVED);
      }
    });

    // Handle natural hover for non-forced states
    $(document).on("mouseenter", `${SELECTORS.PRODUCT_IMAGE}:not(.${CLASSES.HOVER}):not(.${CLASSES.HOVER_TEMP_REMOVED})`, (e) => {
      const $this = $(e.currentTarget);
      
      if (!this.isImageModeToggled()) {
        $this.addClass(CLASSES.HOVER);
      }
    });

    $(document).on("mouseleave", `${SELECTORS.PRODUCT_IMAGE}:not(.${CLASSES.HOVER_TEMP_REMOVED})`, (e) => {
      const $this = $(e.currentTarget);
      
      if (!this.isImageModeToggled() && $this.hasClass(CLASSES.HOVER)) {
        $this.removeClass(CLASSES.HOVER);
      }
    });
  },

  /**
   * Preload hover images for products in viewport
   */
  preloadHoverImages: function () {
    if (!("IntersectionObserver" in window)) {
      return;
    }

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            this.preloadHoverImage($(entry.target));
            observer.unobserve(entry.target);
          }
        });
      },
      {
        rootMargin: "50px",
      }
    );

    $(".card-img").each(function () {
      const $cardImg = $(this);
      if ($cardImg.find(".product-image-hover").length) {
        observer.observe($cardImg[0]);
      }
    });
  },

  /**
   * Preload a specific hover image
   *
   * @param {jQuery} $container The hover container element
   */
  preloadHoverImage: function ($container) {
    const $hoverImg = $container.find(".product-image-hover img");

    if ($hoverImg.length && !$hoverImg.data("preloaded")) {
      const imgSrc = $hoverImg.attr("src");

      if (imgSrc) {
        const preloadImg = new Image();
        preloadImg.onload = function () {
          $hoverImg.data("preloaded", true);
          $container.addClass("hover-image-loaded");
        };
        preloadImg.src = imgSrc;
      }
    }
  },

  /**
   * Handle image mode toggle button clicks
   */
  handleImageModeToggle: function ($button) {
    const mode = $button.data("mode");
    
    this.updateToggleButtons(mode);
    this.setImageMode(mode);
    this.saveImageModeToStorage(mode);
  },

  /**
   * Set image mode for all products
   */
  setImageMode: function (mode) {
    const { SELECTORS, CLASSES, MODES, TRANSITION_DELAY } = this.CONSTANTS;
    const $productImages = $(SELECTORS.PRODUCT_IMAGE);
    const $productContainers = $(SELECTORS.PRODUCT_CONTAINER);

    // Clear all existing states
    this.clearAllStates($productImages, $productContainers);

    if (mode === MODES.HOVER) {
      this.activateHoverMode($productImages, $productContainers);
    } else {
      this.activateMainMode($productImages, $productContainers);
    }

    // Store current mode and add transitions
    this.currentImageMode = mode;
    setTimeout(() => this.addTransitions(), TRANSITION_DELAY);
  },

  /**
   * Clear all existing states
   */
  clearAllStates: function ($productImages, $productContainers) {
    const { CLASSES } = this.CONSTANTS;
    
    $productContainers.removeClass(`${CLASSES.IMAGE_MODE_MAIN} ${CLASSES.IMAGE_MODE_HOVER}`);
    $productImages.removeClass(`${CLASSES.HOVER} ${CLASSES.TOUCH_ACTIVE}`);
  },

  /**
   * Activate hover mode
   */
  activateHoverMode: function ($productImages, $productContainers) {
    const { CLASSES } = this.CONSTANTS;
    
    $productContainers.addClass(CLASSES.IMAGE_MODE_HOVER);
    $productImages.addClass(CLASSES.HOVER);
    
    // Preload hover images
    this.preloadHoverImagesForContainers($productContainers);
  },

  /**
   * Activate main mode
   */
  activateMainMode: function ($productImages, $productContainers) {
    const { CLASSES } = this.CONSTANTS;
    
    $productContainers.addClass(CLASSES.IMAGE_MODE_MAIN);
  },

  /**
   * Preload hover images for given containers
   */
  preloadHoverImagesForContainers: function ($productContainers) {
    const { SELECTORS } = this.CONSTANTS;
    
    $productContainers.each((index, element) => {
      const $container = $(element);
      if ($container.find(SELECTORS.PRODUCT_HOVER).length) {
        this.preloadHoverImage($container);
      }
    });
  },

  /**
   * Get current image mode
   */
  getCurrentImageMode: function () {
    return this.currentImageMode || this.CONSTANTS.MODES.MAIN;
  },

  /**
   * Check if image mode toggle is currently active
   */
  isImageModeToggled: function () {
    return this.getCurrentImageMode() === this.CONSTANTS.MODES.HOVER;
  },

  /**
   * Add transition styles dynamically to prevent initial page load flicker
   */
  addTransitions: function () {
    if ($("#bsawesome-transitions").length) return;

    const transitionCSS = `
      <style id="bsawesome-transitions">
        .product-image-main,
        .product-image-hover {
          transition: opacity 0.3s ease-in-out !important;
        }
        .card-img.image-mode-hover .product-image-main,
        .card-img.image-mode-main .product-image-hover {
          transition: opacity 0.3s ease-in-out !important;
        }
      </style>
    `;

    $("head").append(transitionCSS);
  },

  /**
   * Remove transition styles (for debugging or cleanup)
   */
  removeTransitions: function () {
    $("#bsawesome-transitions").remove();
  }
};

/**
 * Initialize when DOM is ready
 */
$(document).ready(function () {
  ProductHoverImage.init();
});

/**
 * Re-initialize for AJAX-loaded content
 */
$(document).on("woocommerce_ajax_products_loaded", function () {
  ProductHoverImage.init();
});

// Make it globally accessible for debugging
window.ProductHoverImage = ProductHoverImage;
