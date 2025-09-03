/**
 * Product Configurator - Carousel Management Module
 *
 * This module handles the complete product configuration flow including carousel
 * navigation, step management, progress tracking, add to cart functionality,
 * and responsive indicator scrolling system.
 *
 * Features:
 * - Bootstrap carousel integration with custom navigation
 * - Dynamic progress tracking and visual feedback
 * - Add to cart validation with loading states
 * - Responsive indicator scrolling with smooth animations
 * - Summary display with completion effects
 * - Modal dialogs for incomplete configurations
 * - Mobile-optimized touch interactions
 *
 * @version 2.3.0
 * @package Configurator
 *
 * @todo Check if the carousel functions need to be refactored for better reusability (carouselheight)
 */

// ====================== CONFIGURATION CONSTANTS ======================

/**
 * Add to cart behavior configuration
 * - 'steps': Requires completion of all steps
 * - 'primary-button': Requires button state change to primary
 * - 'summary-visible': Requires summary to be visible (handles pre-loaded state)
 */
const ADD_TO_CART_MODE = "summary-visible";

/**
 * Shine effect configuration
 */
const SHINE_EFFECT_DURATION = 1.5; // Seconds (0.4 = fast, 0.8 = normal, 1.2 = slow)

/**
 * Indicator scrolling system configuration
 */
const INDICATOR_SCROLL_AMOUNT = 0.8; // Fraction of container width to scroll
const BUTTONS_VISIBLE_CLASS = "buttons-visible"; // CSS class for button visibility
const VISIBILITY_TIMEOUT = 1500; // Auto-hide timeout for scroll buttons (ms)
const SCROLL_TOLERANCE = 2; // Pixel tolerance for scroll boundary detection

// ====================== MAIN CONFIGURATOR CLASS ======================

/**
 * ProductConfigurator Class
 *
 * Main class that orchestrates the entire product configuration experience.
 * Manages carousel navigation, progress tracking, validation, and user interactions.
 */
class ProductConfigurator {
  /**
   * Constructor - Initialize all configurator components
   * Sets up DOM references, validates required elements, and initializes subsystems
   */
  constructor() {
    // Core form elements
    this.addtocartBtn = document.querySelector(".single_add_to_cart_button");
    this.formEl = this.addtocartBtn?.closest("form");

    // Main configurator container
    this.configuratorEl = document.getElementById("productConfigurator");

    // Bootstrap carousel elements
    this.carouselEl = document.getElementById("productConfiguratorCarousel");
    this.carouselInner = this.carouselEl?.querySelector(".carousel-inner");
    this.carouselPrevBtn = document.getElementById("productConfiguratorPrev");
    this.carouselNextBtn = document.getElementById("productConfiguratorNext");

    // Progress tracking elements
    this.progressEl = document.getElementById("productConfiguratorProgress");
    this.progressBar = this.progressEl?.querySelector(".progress-bar");

    // Step indicator elements
    this.indicatorsEl = document.getElementById("productConfiguratorIndicators");
    this.indicatorsRow = this.indicatorsEl?.querySelector(".row");
    this.indicators = this.indicatorsEl?.querySelectorAll(".indicator");
    this.indicatorsRightBtn = document.getElementById("scrollIndicatorsRight");
    this.indicatorsLeftBtn = document.getElementById("scrollIndicatorsLeft");
    this.indicatorsScrollBtnTimeoutHelper = null;

    // Summary display element
    this.summaryEl = document.getElementById("productConfiguratorSummary");

    // Configuration state management
    this.currentStep = 1;
    this.totalSteps = parseInt(this.configuratorEl?.dataset.totalSteps, 10) || 1;
    this.maxStepReached = 1;
    this.hasEverCompleted = false;
    this.isProgrammaticClick = false;
    this.isComplete = false;
    this.shineEffectTriggered = false; // Prevent multiple shine effects

    // Validate critical elements exist before initialization
    if (!this.carouselEl || !this.addtocartBtn) {
      console.error("ProductConfigurator: Missing required elements");
      return;
    }

    // Initialize all subsystems
    this.initCarousel();
    this.initResize();
    this.initAddtocart();
    this.initUIState();
    this.initIndicatorScrollSystem();
    // this.initIndicatorFocusHighlight(); // <-- Neue Methode hinzufügen
  }

  /**
   * CAROUSEL SYSTEM INITIALIZATION
   * ==============================
   */

  /**
   * Initialize Bootstrap carousel with custom event handlers
   * Sets up slide transition events for dynamic content management
   */
  initCarousel() {
    if (!this.carouselEl) return;

    // Note: Bootstrap carousel instance is auto-created when needed
    // this.carouselInstance = bootstrap.Carousel.getOrCreateInstance(this.carouselEl);

    // Handle slide start events (before transition)
    this.carouselEl.addEventListener("slide.bs.carousel", event => {
      this.handleSlideStart(event);
    });

    // Handle slide completion events (after transition)
    this.carouselEl.addEventListener("slid.bs.carousel", () => {
      this.handleSlideComplete();
    });
  }

  /**
   * Initialize responsive behavior for window resize events
   */
  initResize() {
    window.addEventListener("resize", this.handleResize.bind(this));
  }

  /**
   * Initialize add to cart button with validation and loading states
   */
  initAddtocart() {
    this.addtocartBtn.addEventListener("click", this.handleAddtocartClick.bind(this));
  }

  /**
   * Set initial UI states and layout
   * Establishes baseline progress, heights, and overflow settings
   */
  initUIState() {
    this.updateProgressBar(1);
    this.setCarouselHeight();
    this.addCarouselOverflow();

    // Check if summary is already visible on load and sync button state
    this.syncButtonStateWithSummary();
  }

  /**
   * INDICATOR SCROLLING SYSTEM
   * ==========================
   */

  /**
   * Initialize complete indicator scrolling system
   * Sets up horizontal scrolling for step indicators on mobile devices
   */
  initIndicatorScrollSystem() {
    this.setupButtonVisibilityEvents();
    this.setupScrollButtons();
    this.setupScrollListeners();
  }

  /**
   * Setup mouse and touch events for scroll button visibility
   * Implements auto-hide behavior for cleaner mobile interface
   */
  setupButtonVisibilityEvents() {
    this.indicatorsEl?.addEventListener("mouseenter", this.showButtonsTemporarily.bind(this));
    this.indicatorsEl?.addEventListener("touchstart", this.showButtonsTemporarily.bind(this), { passive: true });
    this.indicatorsEl?.addEventListener("mouseleave", this.hideButtonsIfNotInteracted.bind(this));

    // Optional: Focus/blur events for keyboard navigation
    // [this.indicatorsLeftBtn, this.indicatorsRightBtn].forEach((button) => {
    //     button?.addEventListener("focus", () => {
    //         this.indicatorsEl.classList.add(BUTTONS_VISIBLE_CLASS);
    //     });
    //     button?.addEventListener("blur", () => {
    //         if (!this.indicatorsEl.matches(":hover")) {
    //             this.indicatorsEl.classList.remove(BUTTONS_VISIBLE_CLASS);
    //         }
    //     });
    // });
  }

  /**
   * Setup left/right scroll button click handlers
   * Implements smooth scrolling with calculated scroll distances
   */
  setupScrollButtons() {
    if (this.indicatorsRightBtn) {
      this.indicatorsRightBtn.addEventListener("click", e => {
        e.preventDefault();
        this.scrollContainer(this.indicatorsRow.clientWidth * INDICATOR_SCROLL_AMOUNT);
      });
    }

    if (this.indicatorsLeftBtn) {
      this.indicatorsLeftBtn.addEventListener("click", e => {
        e.preventDefault();
        this.scrollContainer(-this.indicatorsRow.clientWidth * INDICATOR_SCROLL_AMOUNT);
      });
    }
  }

  /**
   * Scroll the indicator container by specified amount
   * Handles boundary detection and smooth scrolling animation
   *
   * @param {number} amount - Pixels to scroll (positive = right, negative = left)
   */
  scrollContainer(amount) {
    if (!this.indicatorsRow) return;

    this.showButtonsTemporarily();
    const newPosition = this.indicatorsRow.scrollLeft + amount;
    const maxScroll = this.indicatorsRow.scrollWidth - this.indicatorsRow.clientWidth;
    const finalPosition = Math.max(0, Math.min(newPosition, maxScroll));

    this.indicatorsRow.scrollTo({
      left: finalPosition,
      behavior: "smooth",
    });
  }

  /**
   * Show scroll buttons temporarily with auto-hide timer
   * Provides visual feedback during user interaction
   */
  showButtonsTemporarily() {
    this.indicatorsEl?.classList.add(BUTTONS_VISIBLE_CLASS);
    clearTimeout(this.indicatorsScrollBtnTimeoutHelper);

    this.indicatorsScrollBtnTimeoutHelper = setTimeout(() => {
      this.hideButtonsIfNotInteracted();
    }, VISIBILITY_TIMEOUT);
  }

  /**
   * Hide scroll buttons if user is not actively interacting
   * Checks for hover, focus, and active states before hiding
   */
  hideButtonsIfNotInteracted() {
    if (!this.isInteracted(this.indicatorsLeftBtn) && !this.isInteracted(this.indicatorsRightBtn)) {
      this.indicatorsEl?.classList.remove(BUTTONS_VISIBLE_CLASS);
    }
  }

  /**
   * Check if user is currently interacting with scroll buttons
   * Utility function for interaction state detection
   *
   * @param {HTMLElement} element - Button element to check
   * @returns {boolean} True if element or container is being interacted with
   */
  isInteracted(element) {
    return this.indicatorsEl?.matches(":hover") || element?.matches(":focus") || this.indicatorsEl?.matches(":active");
  }

  /**
   * Setup scroll event listeners and resize handling
   * Manages scroll button state based on scroll position
   */
  setupScrollListeners() {
    if (this.indicatorsRow) {
      this.checkScrollButtons();
      this.indicatorsRow.addEventListener("scroll", this.checkScrollButtons.bind(this));
    }
    window.addEventListener("resize", this.checkScrollButtons.bind(this));
  }

  /**
   * Update scroll button states based on current scroll position
   * Disables buttons at scroll boundaries and provides visual feedback
   */
  checkScrollButtons() {
    if (!this.indicatorsRow) return;

    const currentScroll = this.indicatorsRow.scrollLeft;
    const maxScroll = this.indicatorsRow.scrollWidth - this.indicatorsRow.clientWidth;

    // Update right scroll button state
    if (this.indicatorsRightBtn) {
      const rightDisabled = currentScroll >= maxScroll - SCROLL_TOLERANCE;
      this.indicatorsRightBtn.style.opacity = rightDisabled ? "0" : "";
      this.indicatorsRightBtn.style.pointerEvents = rightDisabled ? "none" : "";
      this.indicatorsRightBtn.disabled = rightDisabled;
    }

    // Update left scroll button state
    if (this.indicatorsLeftBtn) {
      const leftDisabled = currentScroll <= SCROLL_TOLERANCE;
      this.indicatorsLeftBtn.style.opacity = leftDisabled ? "0" : "";
      this.indicatorsLeftBtn.style.pointerEvents = leftDisabled ? "none" : "";
      this.indicatorsLeftBtn.disabled = leftDisabled;
    }
  }

  /**
   * CAROUSEL EVENT HANDLING
   * =======================
   */

  /**
   * Handle carousel slide start events
   * Manages height transitions, scrolling, and progress updates
   *
   * @param {Event} event - Bootstrap carousel slide event
   */
  handleSlideStart(event) {
    // Set carousel height for smooth transitions
    this.setCarouselHeight();
    requestAnimationFrame(() => {
      this.setCarouselHeight(event.relatedTarget);
    });

    // Ensure configurator is visible during navigation
    this.scrollToConfiguratorTop();

    // Temporarily remove overflow for transition
    this.removeCarouselOverflow();

    // Update progress tracking
    this.handleSlideProgress(event);
  }

  /**
   * Handle carousel slide completion events
   * Resets layout and re-enables overflow for dropdowns
   */
  handleSlideComplete() {
    this.resetCarouselHeight();
    this.addCarouselOverflow();
  }

  /**
   * Handle window resize events
   * Maintains proper carousel height and scroll button states
   */
  handleResize() {
    if (this.carouselInner) {
      this.setCarouselHeight();
      this.resetCarouselHeight();
    }
    this.checkScrollButtons();
  }

  /**
   * CAROUSEL LAYOUT HELPERS
   * =======================
   */

  /**
   * Set carousel container height based on active slide
   * Prevents layout jump during slide transitions
   *
   * @param {HTMLElement} [slide] - Target slide element (defaults to active slide)
   */
  setCarouselHeight(slide) {
    const targetSlide = slide || this.carouselInner?.querySelector(".carousel-item.active");
    if (targetSlide) {
      // Use requestAnimationFrame to batch DOM reads/writes and prevent forced reflow
      requestAnimationFrame(() => {
        const height = targetSlide.offsetHeight;
        requestAnimationFrame(() => {
          this.carouselInner.style.height = `${height}px`;
        });
      });
    }
  }

  /**
   * Reset carousel height to auto for natural content flow
   */
  resetCarouselHeight() {
    if (this.carouselInner) {
      this.carouselInner.style.height = "";
    }
  }

  /**
   * Remove overflow visibility during transitions
   * Prevents dropdown/modal clipping during carousel animation
   */
  removeCarouselOverflow() {
    this.carouselInner?.classList.remove("overflow-visible");
  }

  /**
   * Add overflow visibility after transitions
   * Allows dropdowns and modals to display properly
   */
  addCarouselOverflow() {
    this.carouselInner?.classList.add("overflow-visible");
  }

  /**
   * Scroll to configurator top if not fully visible
   * Ensures progress bar and navigation remain accessible
   */
  scrollToConfiguratorTop() {
    if (!this.progressBar) return;

    // Use requestAnimationFrame to batch DOM reads and prevent forced reflow
    requestAnimationFrame(() => {
      const rect = this.progressBar.getBoundingClientRect();
      const isFullyVisible =
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth);

      if (!isFullyVisible) {
        this.configuratorEl?.scrollIntoView({
          behavior: "smooth",
          block: "start",
        });
      }
    });
  }

  /**
   * PROGRESS TRACKING SYSTEM
   * ========================
   */

  /**
   * Handle slide progress updates and state management
   * Updates step counters, progress bar, and navigation buttons
   *
   * @param {Event} event - Bootstrap carousel slide event
   */
  handleSlideProgress(event) {
    this.currentStep = event.to + 1;
    this.maxStepReached = Math.max(this.maxStepReached, this.currentStep);

    // Update progress bar based on maximum reached step
    this.updateProgressBar(this.maxStepReached);
    this.updateActiveIndicator(event.to);

    // Update navigation button states
    if (this.carouselPrevBtn && this.carouselNextBtn) {
      this.updateCarouselButtons();
    }

    // Track completion state
    if (this.currentStep === this.totalSteps) {
      this.hasEverCompleted = true;
    }
  }

  /**
   * Update progress bar visual state
   * Shows completion percentage with 90% max until final completion
   *
   * @param {number} currentStep - Current step number (1-based)
   */
  updateProgressBar(currentStep) {
    if (this.isComplete || !this.progressBar) return;

    let percentage;
    if (currentStep < this.totalSteps) {
      // Cap at 90% until final completion
      percentage = (currentStep / this.totalSteps) * 90;
    } else {
      percentage = 90;
    }

    this.progressBar.style.width = `${percentage}%`;
    this.progressBar.setAttribute("aria-valuenow", percentage.toString());
  }

  /**
   * Update active step indicator visual state
   * Highlights current step and ensures visibility
   *
   * @param {number} stepIndex - Zero-based step index
   */
  updateActiveIndicator(stepIndex) {
    if (!this.indicators || this.indicators.length === 0) return;

    this.indicators.forEach((indicator, index) => {
      const isActive = index === stepIndex;
      indicator.classList.toggle("bg-secondary-subtle", isActive);
      indicator.classList.toggle("text-muted", !isActive);
      indicator.setAttribute("aria-current", isActive ? "true" : "false");
    });

    this.scrollIndicatorIntoView(stepIndex);
  }

  /**
   * Scroll active indicator into view within container
   * Ensures current step is always visible on mobile
   *
   * @param {number} stepIndex - Zero-based step index
   */
  scrollIndicatorIntoView(stepIndex) {
    if (!this.indicatorsRow || !this.indicators) return;

    const activeIndicator = this.indicators[stepIndex];
    if (!activeIndicator) return;

    // Use requestAnimationFrame to batch DOM reads and prevent forced reflow
    requestAnimationFrame(() => {
      const containerRect = this.indicatorsRow.getBoundingClientRect();
      const indicatorRect = activeIndicator.getBoundingClientRect();

      // Scroll left if indicator is cut off on left side
      if (indicatorRect.left < containerRect.left) {
        this.indicatorsRow.scrollTo({
          left: this.indicatorsRow.scrollLeft + (indicatorRect.left - containerRect.left) - 10,
          behavior: "smooth",
        });
      }
      // Scroll right if indicator is cut off on right side
      else if (indicatorRect.right > containerRect.right) {
        this.indicatorsRow.scrollTo({
          left: this.indicatorsRow.scrollLeft + (indicatorRect.right - containerRect.right) + 10,
          behavior: "smooth",
        });
      }
    });
  }

  /**
   * NAVIGATION BUTTON MANAGEMENT
   * ============================
   */

  /**
   * Update both previous and next button states
   * Coordinates button visibility and functionality
   */
  updateCarouselButtons() {
    this.updatePrevButton();
    this.updateNextButton();
  }

  /**
   * Update previous button state and visibility
   * Hides button on first step with fade animation
   */
  updatePrevButton() {
    if (!this.carouselPrevBtn) return;

    const isFirstStep = this.currentStep === 1;
    this.carouselPrevBtn.disabled = isFirstStep;

    if (isFirstStep) {
      this.carouselPrevBtn.classList.add("fade");
      this.carouselPrevBtn.classList.remove("show");
      // Set tabindex to -1 to prevent focus on hidden/faded button
      this.carouselPrevBtn.setAttribute("tabindex", "-1");
    } else {
      this.carouselPrevBtn.classList.add("show");
      setTimeout(() => {
        this.carouselPrevBtn.classList.remove("fade");
        this.carouselPrevBtn.classList.remove("show");
        // Restore tabindex to 0 to make button focusable again
        this.carouselPrevBtn.setAttribute("tabindex", "0");
      }, 75);
    }
  }

  /**
   * Update next button state and functionality
   * Changes to "Finished" on last step and handles summary display
   */
  updateNextButton() {
    if (!this.carouselNextBtn) return;

    if (this.currentStep === this.totalSteps) {
      // Final step: Change to "Finished" and setup summary
      this.carouselNextBtn.innerHTML = "Fertig";
      this.handleSummary();
    } else {
      // Regular step: Reset to "Next" and carousel navigation
      this.carouselNextBtn.classList.remove("show");
      this.carouselNextBtn.innerHTML = "Weiter";

      setTimeout(() => {
        this.carouselNextBtn.removeAttribute("data-bs-toggle");
        this.carouselNextBtn.removeAttribute("aria-expanded");
        this.carouselNextBtn.removeAttribute("aria-controls");
        this.carouselNextBtn.setAttribute("data-bs-target", "#productConfiguratorCarousel");
      }, 300);
    }
  }

  /**
   * SUMMARY DISPLAY MANAGEMENT
   * ==========================
   */ /**
   * Handle summary display setup and button behavior
   * Configures collapse functionality for final step
   */
  handleSummary() {
    console.log("🔧 handleSummary() called");
    if (!this.summaryEl) {
      console.log("❌ No summaryEl found");
      return;
    }

    console.log("📊 Summary element:", this.summaryEl);
    console.log("📊 Summary has 'show' class:", this.summaryEl.classList.contains("show"));

    // Show button if summary is already visible
    if (this.summaryEl.classList.contains("show")) {
      this.carouselNextBtn.classList.add("show");
      console.log("✅ Summary already visible, added 'show' to next button");

      // Only apply completion styling automatically for non-summary-visible modes
      if (ADD_TO_CART_MODE !== "summary-visible") {
        console.log("🎯 Non-summary-visible mode, applying completion styling directly");
        this.applyCompletionStyling();
      } else {
        console.log("🎯 Summary-visible mode, styling will be applied on 'Fertig' click only");
      }
    }

    // Configure button for Bootstrap collapse
    setTimeout(() => {
      this.carouselNextBtn.setAttribute("data-bs-toggle", "collapse");
      this.carouselNextBtn.setAttribute("data-bs-target", "#productConfiguratorSummary");
      this.carouselNextBtn.setAttribute("aria-expanded", "false");
      this.carouselNextBtn.setAttribute("aria-controls", "productConfiguratorSummary");
      console.log("✅ Bootstrap collapse attributes set");
    }, 1);

    // Setup summary event listeners (once only)
    if (!this.summaryEl.dataset.listenerAdded) {
      console.log("🎯 Setting up event listeners for first time");
      this.lockSummaryOnShow();
      this.setupFinishedButtonScrollBehavior();
      this.summaryEl.dataset.listenerAdded = "true";
    } else {
      console.log("⚠️ Event listeners already added, skipping setup");
    }
  }

  /**
   * Apply completion styling to button and progress bar
   * Centralized method for consistent completion effects
   */
  applyCompletionStyling() {
    console.log("🎨 applyCompletionStyling() called");

    // Prevent multiple styling applications - check and set flag immediately
    if (this.isComplete) {
      console.log("⚠️ Already completed, skipping styling");
      return;
    }

    // Mark configuration as complete immediately to prevent race conditions
    this.isComplete = true;
    console.log("🔒 Configuration marked as complete (early)");

    console.log("📊 Add to cart button before:", this.addtocartBtn.className);

    // Transform add to cart button to primary state
    this.addtocartBtn.classList.add("btn-primary");
    this.addtocartBtn.classList.remove("text-start", "btn-light");

    console.log("📊 Add to cart button after:", this.addtocartBtn.className);

    // Complete progress bar
    if (this.progressBar) {
      this.progressBar.style.width = "100%";
      this.progressBar.setAttribute("aria-valuenow", "100");
      console.log("✅ Progress bar set to 100%");
    }

    console.log("✅ Configuration styling complete");

    // Trigger shine effect only once
    setTimeout(() => {
      this.triggerCompletionEffects();
    }, 300);
  }

  /**
   * Setup summary collapse event handlers
   * Handles completion effects and prevents summary hiding
   */
  lockSummaryOnShow() {
    console.log("🔒 lockSummaryOnShow() called");

    const onSummaryShow = () => {
      console.log("🎉 SUMMARY SHOW EVENT TRIGGERED!");

      // Update button state
      this.carouselNextBtn?.classList.add("show");

      // Apply completion styling
      this.applyCompletionStyling();

      // Scroll to summary
      setTimeout(() => {
        this.scrollToSummary();
      }, 1);
    };

    // Setup event listeners
    console.log("🎯 Adding show.bs.collapse event listener");
    this.summaryEl.addEventListener("show.bs.collapse", onSummaryShow);
    this.summaryEl.addEventListener("hide.bs.collapse", e => e.preventDefault());
  }  /**
   * Trigger completion visual effects (shine effect on add-to-cart button)
   * Centralized method to ensure consistent completion feedback
   */
  triggerCompletionEffects() {
    console.log("✨ triggerCompletionEffects() called");

    // Prevent multiple shine effects
    if (this.shineEffectTriggered) {
      console.log("⚠️ Shine effect already triggered, skipping");
      return;
    }

    this.shineEffectTriggered = true;
    console.log("🔒 Shine effect marked as triggered");

    // Add slight delay to ensure button styling is complete
    setTimeout(() => {
      this.addShineEffectToButton();
    }, 300);
  }

  /**
   * Setup "Fertig" button click behavior for summary scrolling
   * Ensures clicking "Fertig" always scrolls to summary if it's already visible
   */
  setupFinishedButtonScrollBehavior() {
    console.log("🎯 setupFinishedButtonScrollBehavior() called");
    if (!this.carouselNextBtn) {
      console.log("❌ No carouselNextBtn found");
      return;
    }

    this.carouselNextBtn.addEventListener("click", event => {
      console.log("🔘 Fertig button clicked!");
      console.log("📊 Current step:", this.currentStep, "Total steps:", this.totalSteps);
      console.log("📊 ADD_TO_CART_MODE:", ADD_TO_CART_MODE);

      // Only handle when button is in "Fertig" state (last step)
      if (this.currentStep !== this.totalSteps) {
        console.log("⚠️ Not on last step, ignoring click");
        return;
      }

      console.log("✅ On last step, checking summary visibility");
      console.log("📊 Summary element:", this.summaryEl);
      console.log("📊 Summary has 'show' class:", this.summaryEl?.classList.contains("show"));

      // For summary-visible mode: Apply styling on every "Fertig" click
      if (ADD_TO_CART_MODE === "summary-visible") {
        console.log("🎯 Summary-visible mode: Applying completion styling on Fertig click");

        // Reset shine effect flag to allow multiple triggers
        this.shineEffectTriggered = false;
        console.log("🔄 Shine effect flag reset for new trigger");

        this.applyCompletionStyling();
      }

      // Check if summary is already visible
      if (this.summaryEl && this.summaryEl.classList.contains("show")) {
        console.log("📋 Summary already visible, scrolling to it");
        // Small delay to ensure any collapse animation is complete
        setTimeout(() => {
          this.scrollToSummary();

          // Reset shine effect flag for re-trigger
          this.shineEffectTriggered = false;
          console.log("🔄 Shine effect flag reset for re-trigger");

          // Trigger shine effect when re-clicking "Fertig" on visible summary
          this.triggerCompletionEffects();
        }, 100);
      } else {
        console.log("📋 Summary not visible, Bootstrap collapse should handle it");
      }
    });
  }

  /**
   * Handle add to cart button click events
   * Validates configuration completeness before allowing purchase
   */
  handleAddtocartClick(event) {
    // Allow programmatic clicks to proceed (from modal confirmation)
    if (this.isProgrammaticClick) {
      // Reset the flag after use to prevent future clicks from bypassing validation
      this.isProgrammaticClick = false;
      this.addtocartLoadingState();
      return;
    }

    // Allow completed configurations to proceed
    if (this.configuratorCompleted()) {
      this.addtocartLoadingState();
      return;
    }

    // Prevent default and show incomplete configuration modal
    event.preventDefault();
    this.showIncompleteConfigModal();
  }

  /**
   * Apply loading state to add to cart button
   * Shows spinner and loading message during cart processing
   */
  addtocartLoadingState() {
    this.addtocartBtn.innerHTML = `
            <span class="spinner-border me-2"
                  style="--bs-spinner-width:1.25rem; --bs-spinner-height:1.25rem; --bs-spinner-border-width:0.225rem"
                  role="status">
            </span>
            <span aria-live="polite">Warenkorb wird vorbereitet...</span>
        `;
    this.addtocartBtn.setAttribute("aria-busy", "true");
  }

  /**
   * Check if configurator has been completed
   * Supports multiple completion detection modes
   *
   * @returns {boolean} True if configuration is complete
   */
  configuratorCompleted() {
    console.log("🔍 configuratorCompleted() check - Mode:", ADD_TO_CART_MODE);

    switch (ADD_TO_CART_MODE) {
      case "steps":
        const stepsResult = this.hasEverCompleted;
        console.log("📊 Steps mode result:", stepsResult, "(hasEverCompleted:", this.hasEverCompleted, ")");
        return stepsResult;
      case "primary-button":
        const primaryResult = this.addtocartBtn.classList.contains("btn-primary");
        console.log("📊 Primary-button mode result:", primaryResult, "(button classes:", this.addtocartBtn.className, ")");
        return primaryResult;
      case "summary-visible":
        const summaryResult = this.summaryEl && this.summaryEl.classList.contains("show");
        console.log("📊 Summary-visible mode result:", summaryResult, "(summary element exists:", !!this.summaryEl, ", has show class:", this.summaryEl?.classList.contains("show"), ")");
        return summaryResult;
      default:
        console.log("❌ Unknown mode, returning false");
        return false;
    }
  }

  /**
   * MODAL DIALOG MANAGEMENT
   * =======================
   */

  /**
   * Show modal dialog for incomplete configurations
   * Offers users choice to continue configuring or proceed to cart
   */
  showIncompleteConfigModal() {
    createModal({
      title: '<i class="fa-sharp fa-light fa-exclamation-triangle text-warning me-2"></i>Hinweis',
      body: `
                <p class="alert alert-warning mb-3">Konfiguration noch nicht abgeschlossen!</p>
                <p class="border border-warning-subtle p-3 text-warning-emphasis mb-0">
                    Sie können zurück zum Konfigurator, <strong>oder</strong> das Produkt direkt in den Warenkorb legen.
                </p>
                `,
      footer: [
        {
          text: "Zurück zum Konfigurator",
          class: "btn-secondary", // Beispielklasse, kann angepasst werden
          dismiss: true,
        },
        {
          text: "Weiter zum Warenkorb",
          class: "btn-primary", // Beispielklasse, kann angepasst werden
          dismiss: true, // Schließt das Modal nach dem Klick
          onClick: () => {
            this.triggerProgrammaticClick();
          },
        },
      ],
      size: "lg",
      // onConfirm ist nicht mehr nötig, da es im footer-Array gehandhabt wird
    });
  }

  /**
   * Trigger programmatic add to cart click
   * Bypasses validation for user-confirmed incomplete configurations
   */
  triggerProgrammaticClick() {
    this.isProgrammaticClick = true;
    this.addtocartLoadingState();
    this.addtocartBtn.click();
  }

  /**
   * Scroll to summary section smoothly
   * Ensures summary is visible after configuration completion
   */
  scrollToSummary() {
    if (!this.summaryEl) return;

    this.summaryEl.scrollIntoView({
      behavior: "smooth",
      block: "start",
    });
  }

  /**
   * Sync button state with summary visibility on initialization
   * Handles cases where summary is pre-loaded with "show" class
   */
  syncButtonStateWithSummary() {
    if (!this.summaryEl || !this.addtocartBtn) return;

    // Only auto-sync button styling for "primary-button" mode
    // "summary-visible" mode should only style button on active "Fertig" click
    if (ADD_TO_CART_MODE !== "primary-button") return;

    // If summary is already visible but button is not primary, sync the state
    if (this.summaryEl.classList.contains("show") && !this.addtocartBtn.classList.contains("btn-primary")) {
      // Transform add to cart button to primary state
      this.addtocartBtn.classList.add("btn-primary");
      this.addtocartBtn.classList.remove("text-start", "btn-light");

      // Complete progress bar
      if (this.progressBar) {
        this.progressBar.style.width = "100%";
        this.progressBar.setAttribute("aria-valuenow", "100");
      }

      // Mark configuration as complete
      this.isComplete = true;

      // Optional: Trigger shine effect after a short delay
      setTimeout(() => {
        this.triggerCompletionEffects();
      }, 500);
    }
  }

  /**
   * Add shine effect to add-to-cart button
   * Creates visual feedback for configuration completion
   */
  addShineEffectToButton() {
    if (!this.addtocartBtn) return;

    console.log("✨ Adding shine effect to button");

    // Remove any existing shine effects to prevent overlap
    const existingShines = this.addtocartBtn.querySelectorAll(".btn-shine-effect");
    existingShines.forEach(shine => {
      console.log("🧹 Removing existing shine effect");
      shine.remove();
    });

    const shineElement = document.createElement("div");
    shineElement.className = "btn-shine-effect";

    if (getComputedStyle(this.addtocartBtn).position === "static") {
      this.addtocartBtn.style.position = "relative";
    }

    this.addtocartBtn.appendChild(shineElement);

    requestAnimationFrame(() => {
      shineElement.style.animation = `shine ${SHINE_EFFECT_DURATION}s ease-out forwards`;
    });

    setTimeout(() => {
      if (shineElement.parentNode) {
        shineElement.remove();
      }
    }, SHINE_EFFECT_DURATION * 1000); // Convert to milliseconds
  }

  /**
   * Initialize indicator focus highlighting for carousel headers
   * Adds text-primary class to .carousel-header when corresponding indicator is focused
   */
  initIndicatorFocusHighlight() {
    if (!this.indicators) return;

    this.indicators.forEach((indicator, index) => {
      // Add focus event listener
      indicator.addEventListener("focus", () => {
        this.highlightCarouselHeader(index, true);
      });

      // Add blur event listener
      indicator.addEventListener("blur", () => {
        this.highlightCarouselHeader(index, false);
      });

      // Optional: Also handle mouse hover for consistent UX
      indicator.addEventListener("mouseenter", () => {
        this.highlightCarouselHeader(index, true);
      });

      indicator.addEventListener("mouseleave", () => {
        this.highlightCarouselHeader(index, false);
      });
    });
  }

  /**
   * Highlight or unhighlight carousel header based on indicator focus
   *
   * @param {number} stepIndex - Zero-based step index
   * @param {boolean} highlight - Whether to add or remove highlight
   */
  highlightCarouselHeader(stepIndex, highlight) {
    // Find the corresponding carousel item
    const carouselItem = this.carouselInner?.children[stepIndex];
    if (!carouselItem) return;

    // Find the header within that carousel item
    const carouselHeader = carouselItem.querySelector(".carousel-header");
    if (!carouselHeader) return;

    // Add smooth transition for color changes
    if (!carouselHeader.style.transition) {
      carouselHeader.style.transition = "color 0.3s ease";
    }

    // Toggle the text-primary class
    if (highlight) {
      carouselHeader.classList.add("text-primary");
    } else {
      carouselHeader.classList.remove("text-primary");
    }
  }
}

// ====================== MODULE INITIALIZATION ======================

/**
 * Initialize ProductConfigurator when DOM is ready
 * Creates single instance to manage entire configurator experience
 */
document.addEventListener("DOMContentLoaded", () => {
  const configuratorInstance = new ProductConfigurator();

  // Make carousel height update globally available for validation feedback
  window.updateCarouselHeight = function () {
    if (configuratorInstance && configuratorInstance.setCarouselHeight) {
      configuratorInstance.setCarouselHeight();
    }
  };
});

/**
 * Future Enhancement Ideas:
 *
 * 1. Keyboard navigation support:
 *    - Arrow key navigation between steps
 *    - Enter/Space for step selection
 *    - Tab order management
 *
 * 2. Progress persistence:
 *    - Save configuration state to localStorage
 *    - Restore progress on page reload
 *    - URL-based step navigation
 *
 * 3. Advanced validation:
 *    - Custom validation rules per step
 *    - Real-time validation feedback
 *    - Dependent field validation
 *
 * 4. Analytics integration:
 *    - Step completion tracking
 *    - Abandonment point analysis
 *    - Configuration choice analytics
 *
 * 5. Performance optimizations:
 *    - Lazy loading of step content
 *    - Virtual scrolling for large indicator lists
 *    - Image preloading for smoother transitions
 */
