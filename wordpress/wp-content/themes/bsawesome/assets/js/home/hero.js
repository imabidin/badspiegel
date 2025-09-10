/**
 * Homepage Hero Section - Image Loader and Switcher Module
 *
 * This module handles the dynamic hero image display system on the homepage.
 * It provides smooth loading animations and interactive switching between different
 * lighting modes (day, dawn, night) to showcase mirror products in various ambient
 * conditions. The system includes accessibility features and responsive behavior.
 *
 * Features:
 * - Smooth fade-in animation on page load with delayed reveal
 * - Interactive lighting mode switcher (day/dawn/night)
 * - Automatic time-based mode selection for contextual experience
 * - Special day mode that combines multiple lighting states
 * - ARIA accessibility support for screen readers
 * - Button state management with visual feedback
 * - Performance-optimized DOM queries with element caching
 * - Event delegation for button interactions
 * - Responsive image visibility control
 * - Development testing utilities for quality assurance
 *
 * Lighting Modes:
 * - Dawn: Soft ambient lighting (06:00-08:59 & 17:00-21:59)
 * - Day: Bright lighting, shows both day and dawn images combined (09:00-16:59)
 * - Night: Dark ambient lighting, single image display (22:00-05:59)
 *
 * @version 2.3.0
 */

// PRODUCTION MODE: Set to false to disable debug logging
const HERO_DEBUG_ENABLED = false;

/**
 * Debug logging function (disabled in production)
 */
function heroDebugLog(...args) {
  if (HERO_DEBUG_ENABLED) {
    heroDebugLog(...args);
  }
}

// ====================== PAGE LOAD ANIMATION SYSTEM ======================

/**
 * Hero image reveal animation on page load
 * Provides smooth entrance effect for appropriate time-based images after page resources load
 * Uses delayed execution to ensure all assets are ready before animation
 * Now works in coordination with automatic time-based mode selection
 */
window.addEventListener("load", function () {
  /**
   * DELAYED REVEAL ANIMATION
   * =====================
   * 250ms delay ensures:
   * - All page resources (images, CSS) are fully loaded
   * - Smooth transition without layout shifts
   * - Better perceived performance for users
   * - Allows time-based mode selection to complete first
   */
  setTimeout(function () {
    // Determine current time-based mode and reveal appropriate images
    const currentHour = new Date().getHours();
    let targetImageClass;

    if (currentHour >= 22 || currentHour < 6) {
      // Night mode: 22:00 - 05:59
      targetImageClass = ".hero-img-night";
    } else if ((currentHour >= 6 && currentHour < 9) || (currentHour >= 17 && currentHour < 22)) {
      // Dawn mode: 06:00 - 08:59 & 17:00 - 21:59
      targetImageClass = ".hero-img-dawn";
    } else {
      // Day mode: 09:00 - 16:59 (shows both day and dawn)
      document.querySelectorAll(".hero-img-day").forEach(function (el) {
        el.classList.remove("opacity-0");
      });
      document.querySelectorAll(".hero-img-dawn").forEach(function (el) {
        el.classList.remove("opacity-0");
      });
      return; // Exit early for day mode
    }

    // Remove opacity-0 class from target images for fade-in effect
    document.querySelectorAll(targetImageClass).forEach(function (el) {
      el.classList.remove("opacity-0");
    });
  }, 250); // Optimal delay for smooth reveal without perceived lag
});

// ====================== INTERACTIVE SWITCHER SYSTEM ======================

/**
 * Hero image switcher initialization and event management
 * Sets up the interactive lighting mode system when DOM is ready
 */
document.addEventListener("DOMContentLoaded", function () {
  // ====================== DOM ELEMENT CACHING ======================

  /**
   * Hero image element collections organized by lighting mode
   * Object structure provides efficient lookup and iteration
   * @type {Object<string, NodeList>}
   */
  const heroImages = {
    "hero-img-day": document.querySelectorAll(".hero-img-day"), // Bright daylight images
    "hero-img-dawn": document.querySelectorAll(".hero-img-dawn"), // Soft dawn/dusk images
    "hero-img-night": document.querySelectorAll(".hero-img-night"), // Dark ambient images
  };

  /**
   * Interactive control buttons for mode switching
   * Cached for performance optimization during frequent interactions
   * @type {NodeList}
   */
  const buttons = document.querySelectorAll(".hero-switch .btn");

  // ====================== CORE SWITCHING LOGIC ======================

  /**
   * Switches between different hero image lighting modes
   * Manages button states, accessibility attributes, and image visibility
   * with special handling for combined lighting effects
   *
   * @param {string} mode - The target lighting mode ("hero-img-day", "hero-img-dawn", "hero-img-night")
   *
   * @example
   * switchMode("hero-img-day");    // Shows day + dawn images
   * switchMode("hero-img-dawn");   // Shows only dawn images
   * switchMode("hero-img-night");  // Shows only night images
   */
  function switchMode(mode) {
    /**
     * BUTTON STATE RESET
     * ==================
     * Clear all active states and reset ARIA attributes for accessibility
     */
    buttons.forEach(btn => {
      btn.classList.remove("active"); // Remove visual active state
      btn.setAttribute("aria-pressed", "false"); // Reset screen reader state
    });

    /**
     * ACTIVE BUTTON IDENTIFICATION AND STATE UPDATE
     * ============================================
     * Find and activate the button corresponding to selected mode
     */
    const activeButton = document.querySelector(`.hero-switch .btn[data-hero="${mode}"]`);
    if (activeButton) {
      activeButton.classList.add("active"); // Apply visual active state
      activeButton.setAttribute("aria-pressed", "true"); // Update screen reader state
    }

    /**
     * IMAGE VISIBILITY CONTROL
     * ========================
     * Manages opacity classes to show/hide images based on selected mode
     * Special logic for day mode which combines multiple lighting states
     */
    Object.keys(heroImages).forEach(key => {
      heroImages[key].forEach(img => {
        if (mode === "hero-img-day") {
          /**
           * DAY MODE SPECIAL BEHAVIOR
           * ========================
           * Day mode shows both day AND dawn images simultaneously
           * This creates a layered lighting effect showcasing mirrors
           * in bright conditions while maintaining ambient warmth
           */
          const shouldShow = key === "hero-img-day" || key === "hero-img-dawn";
          img.classList.toggle("opacity-0", !shouldShow);
        } else {
          /**
           * STANDARD MODE BEHAVIOR
           * =====================
           * Dawn and night modes show only their respective images
           * Provides clean, focused lighting demonstration
           */
          img.classList.toggle("opacity-0", key !== mode);
        }
      });
    });
  }

  // ====================================================================
  // ====================== AUTOMATIC TIME-BASED MODE ======================
  // ====================================================================

  /**
   * Automatically selects display mode based on current time of day
   * Provides dynamic and contextual user experience that matches ambient lighting
   *
   * Time Windows (customizable):
   * - Night:   22:00 - 05:59 (dark ambient lighting)
   * - Dawn:    06:00 - 08:59 & 19:00 - 21:59 (soft twilight lighting)
   * - Day:     09:00 - 18:59 (bright daylight)
   */
  function setInitialModeByTime() {
    const currentHour = new Date().getHours(); // Get current hour (0-23)
    let modeToSet;

    /**
     * TIME-BASED MODE SELECTION LOGIC
     * ===============================
     * Determines appropriate lighting mode based on time of day
     * to create immersive mirror showcasing experience
     */
    if (currentHour >= 22 || currentHour < 6) {
      // Night mode: 22:00 - 05:59
      modeToSet = "hero-img-night";
    } else if ((currentHour >= 6 && currentHour < 9) || (currentHour >= 17 && currentHour < 22)) {
      // Dawn mode: 06:00 - 08:59 & 17:00 - 21:59
      modeToSet = "hero-img-dawn";
    } else {
      // Day mode: 09:00 - 16:59
      modeToSet = "hero-img-day";
    }

    // PRODUCTION: Debug logging removed

    // Apply the determined mode using existing switchMode function
    switchMode(modeToSet);
  }

  // Execute automatic mode selection on page load
  setInitialModeByTime();

  // ====================================================================
  // ====================== DEVELOPMENT TESTING UTILITIES ======================
  // ====================================================================

  /**
   * Development helper functions for testing time-based mode selection
   * Available in browser console for manual testing and debugging
   *
   * PRODUCTION NOTE: These utilities are designed for development and testing.
   * They can safely remain in production as they don't affect normal operation
   * and are only accessible via browser console for debugging purposes.
   *
   * Usage in browser console:
   * - testHeroMode.night()     // Test night mode (22:00-05:59)
   * - testHeroMode.dawn()      // Test dawn mode (06:00-08:59 & 17:00-21:59)
   * - testHeroMode.day()       // Test day mode (09:00-16:59)
   * - testHeroMode.time(14)    // Test specific hour (0-23)
   * - testHeroMode.reset()     // Reset to current real time
   * - testHeroMode.info()      // Show current mode and time info
   */
  window.testHeroMode = {
    /**
     * Test night mode (simulates 2 AM)
     */
    night: function () {
      heroDebugLog("🌙 Testing NIGHT mode (simulating 02:00)");
      this._simulateTime(2);
    },

    /**
     * Test dawn mode (simulates 7 AM)
     */
    dawn: function () {
      heroDebugLog("🌅 Testing DAWN mode (simulating 07:00)");
      this._simulateTime(7);
    },

    /**
     * Test day mode (simulates 2 PM)
     */
    day: function () {
      heroDebugLog("☀️ Testing DAY mode (simulating 14:00)");
      this._simulateTime(14);
    },

    /**
     * Test specific time
     * @param {number} hour - Hour to simulate (0-23)
     */
    time: function (hour) {
      if (hour < 0 || hour > 23) {
        console.error("❌ Invalid hour! Please use 0-23");
        return;
      }
      heroDebugLog(`🕐 Testing specific time: ${hour}:00`);
      this._simulateTime(hour);
    },

    /**
     * Reset to current real time
     */
    reset: function () {
      const realHour = new Date().getHours();
      heroDebugLog(`🔄 Resetting to real time: ${realHour}:00`);
      this._simulateTime(realHour);
    },

    /**
     * Show current mode information
     */
    info: function () {
      const currentHour = new Date().getHours();
      let mode, timeRange;

      if (currentHour >= 22 || currentHour < 6) {
        mode = "NIGHT";
        timeRange = "22:00 - 05:59";
      } else if ((currentHour >= 6 && currentHour < 9) || (currentHour >= 17 && currentHour < 22)) {
        mode = "DAWN";
        timeRange = "06:00 - 08:59 & 17:00 - 21:59";
      } else {
        mode = "DAY";
        timeRange = "09:00 - 16:59";
      }

      const activeButton = document.querySelector(".hero-switch .btn.active");
      const activeMode = activeButton ? activeButton.dataset.hero : "none";

      heroDebugLog(`📊 Hero Mode Info:
🕐 Current time: ${currentHour}:00
🎯 Expected mode: ${mode} (${timeRange})
✅ Active button: ${activeMode}
🖼️ Visible images: ${this._getVisibleImages()}`);
    },

    /**
     * Internal helper to simulate specific time
     * @private
     */
    _simulateTime: function (hour) {
      let modeToSet;

      if (hour >= 22 || hour < 6) {
        modeToSet = "hero-img-night";
        heroDebugLog(`   → Should activate NIGHT mode (hour ${hour})`);
      } else if ((hour >= 6 && hour < 9) || (hour >= 17 && hour < 22)) {
        modeToSet = "hero-img-dawn";
        heroDebugLog(`   → Should activate DAWN mode (hour ${hour})`);
      } else {
        modeToSet = "hero-img-day";
        heroDebugLog(`   → Should activate DAY mode (hour ${hour})`);
      }

      // Apply the mode
      switchMode(modeToSet);

      // Show result
      setTimeout(() => {
        heroDebugLog(`   ✅ Active mode: ${modeToSet}`);
        heroDebugLog(`   🖼️ Visible images: ${this._getVisibleImages()}`);
      }, 100);
    },

    /**
     * Get list of currently visible hero images
     * @private
     */
    _getVisibleImages: function () {
      const visibleImages = [];
      document.querySelectorAll(".hero-img").forEach(img => {
        if (!img.classList.contains("opacity-0")) {
          if (img.classList.contains("hero-img-night")) visibleImages.push("night");
          if (img.classList.contains("hero-img-dawn")) visibleImages.push("dawn");
          if (img.classList.contains("hero-img-day")) visibleImages.push("day");
        }
      });
      return visibleImages.join(", ") || "none";
    },
  };

  // Development mode detection and conditional logging
  if (window.location.hostname === "localhost" || window.location.hostname.includes("dev") || window.console) {
    heroDebugLog(`
🧪 Hero Mode Testing Commands Available:
🌙 testHeroMode.night()    - Test night mode
🌅 testHeroMode.dawn()     - Test dawn mode
☀️ testHeroMode.day()      - Test day mode
🕐 testHeroMode.time(14)   - Test specific hour
🔄 testHeroMode.reset()    - Reset to real time
📊 testHeroMode.info()     - Show current info`);
  }

  // ====================== EVENT LISTENER REGISTRATION ======================

  /**
   * Button click event delegation for mode switching
   * Attaches event listeners to all switcher buttons for interactive control
   */
  buttons.forEach(button => {
    button.addEventListener("click", function () {
      // Extract target mode from button's data attribute
      const targetMode = button.dataset.hero;

      // Execute mode switch with extracted target
      switchMode(targetMode);
    });
  });

  /**
   * OPTIONAL: Initial state setup
   * ============================
   * Uncomment to set specific initial mode on page load
   * Currently uses CSS defaults (dawn mode visible)
   */

  // Set initial dawn mode as active button state
  // switchMode("hero-img-dawn");
});

/**
 * Future Enhancement Ideas (V2 Optimization):
 *
 * 1. Smooth transition animations between modes:
 *    const transitionConfig = {
 *      duration: 500,
 *      easing: 'ease-in-out',
 *      stagger: 100
 *    };
 *
 * 2. Preload optimization for better performance:
 *    function preloadHeroImages() {
 *      Object.values(heroImages).flat().forEach(img => {
 *        const imageUrl = getComputedStyle(img).backgroundImage;
 *        const preloadImg = new Image();
 *        preloadImg.src = imageUrl.slice(5, -2);
 *      });
 *    }
 *
 * 3. Automatic mode cycling for demo purposes:
 *    const autoCycle = {
 *      enabled: false,
 *      interval: 5000,
 *      modes: ['hero-img-dawn', 'hero-img-day', 'hero-img-night']
 *    };
 *
 * 4. Intersection Observer for lazy loading:
 *    const heroObserver = new IntersectionObserver((entries) => {
 *      entries.forEach(entry => {
 *        if (entry.isIntersecting) startHeroAnimations();
 *      });
 *    });
 *
 * 5. Touch gesture support for mobile:
 *    let startX = 0;
 *    hero.addEventListener('touchstart', (e) => {
 *      startX = e.touches[0].clientX;
 *    });
 *    hero.addEventListener('touchend', (e) => {
 *      const endX = e.changedTouches[0].clientX;
 *      const diff = startX - endX;
 *      if (Math.abs(diff) > 50) cycleModes(diff > 0 ? 1 : -1);
 *    });
 */
