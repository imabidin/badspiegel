/**
 * Product Configurator - SK1 Special Cut Calculations Module
 *
 * This module handles specialized geometric calculations for SK1 (single cut edge)
 * mirror configurations. It provides real-time mathematical relationships between
 * diameter, segment height, and cut width for circular mirrors with one straight
 * cut edge, creating a segment shape.
 *
 * Features:
 * - Real-time geometric calculations for circular segment mirrors
 * - Bidirectional input dependencies (change one dimension, auto-calculate others)
 * - Mathematical validation and error handling for impossible configurations
 * - Event-driven updates with input change detection
 * - Flexible input field naming conventions support
 * - Performance optimization through early validation checks
 * - Precise rounding to whole numbers for manufacturing requirements
 *
 * Mathematical Relationships:
 * - Segment Height: h = 2r - (r - √(r² - (t²/4)))
 * - Diameter from Height: d = (h² + t²/4) / h
 * - All calculations based on circle geometry and segment formulas
 *
 * @version 2.6.0
 * @package Configurator
 * @subpackage PriceCalculations
 */

import { isSK, isSK1, durchmesserInput } from "../variables";
import { updateSummary } from "../summary";

// ====================== DEBUG & OPTIMIZATION MODES ======================

/**
 * Optimized SK1 configuration - simplified approach
 */
const SK1_DEBUG_CONFIG = {
  // Debug mode - enables console logging for troubleshooting
  DEBUG_MODE: true,

  // Use change events instead of input events for final calculations
  USE_CHANGE_EVENTS: true,

  // Precision control to prevent rounding loops
  PRECISION_THRESHOLD: 1, // Don't update if difference is less than 1mm
};

/**
 * Debug logging function
 */
function debugLog(message, data = null) {
  if (SK1_DEBUG_CONFIG.DEBUG_MODE) {
    console.log(`[SK1 Optimized] ${message}`, data || '');
  }
}

// ====================== MODULE INITIALIZATION ======================

/**
 * Initialize SK1 calculations when DOM is ready
 * Validates product type and sets up input event listeners for geometric calculations
 */
document.addEventListener("DOMContentLoaded", () => {
  // ====================== PRODUCT TYPE VALIDATION ======================

  /**
   * Product compatibility check
   * Ensures this module only runs on SK1 (single cut edge) products
   * Early return prevents unnecessary processing on incompatible products
   */
  if (!isSK || !isSK1) {
    return;
  }

  // ====================== DOM ELEMENT DISCOVERY ======================

  /**
   * Input field references with flexible naming convention support
   * Accommodates different naming patterns used across product variants
   */

  // Diameter input (imported from variables module)
  // const durchmesserInput = document.querySelector('.option-input[name="durchmesser"]');

  /**
   * Segment height input with fallback naming support
   * Can be named either "hoehe_schnittkante" or "breite_schnittkante"
   * depending on product orientation and configuration system setup
   * @type {HTMLInputElement|null}
   */
  const hoeheInput =
    document.querySelector('.option-input[name="hoehe_schnittkante"]') ||
    document.querySelector('.option-input[name="breite_schnittkante"]');

  /**
   * Cut width input field
   * Represents the width of the straight cut edge
   * @type {HTMLInputElement|null}
   */
  const schnittkanteInput = document.querySelector('.option-input[name="schnittkante"]');

  /**
   * INPUT VALIDATION AND FILTERING
   * ==============================
   * Create array of available inputs, filtering out null references
   * Enables graceful handling of partial input configurations
   */
  const inputs = [durchmesserInput, hoeheInput, schnittkanteInput].filter(Boolean);

  // Early exit if no valid inputs found
  if (inputs.length === 0) {
    return;
  }

  // ====================== OPTIMIZED EVENT LISTENER SETUP ======================

  /**
   * Simplified event handling - no debouncing, direct change events
   * Uses 'change' events for final calculations when user leaves field
   */
  inputs.forEach(el => {
    if (SK1_DEBUG_CONFIG.USE_CHANGE_EVENTS) {
      // Use 'change' event for clean, final calculations
      el.addEventListener("change", handleInputChange);
      debugLog(`Change event listener attached to: ${el.name}`);
    } else {
      // Fallback to 'blur' event
      el.addEventListener("blur", handleInputChange);
      debugLog(`Blur event listener attached to: ${el.name}`);
    }
  });  // ====================== INITIAL CALCULATION ======================

  /**
   * Perform initial calculation if values are already present
   * This handles cases where the form is pre-populated or page is refreshed
   * For SK1: Always calculate height from diameter and cut width when page loads
   */
  const performInitialCalculation = () => {
    const dVal = parseFloat(durchmesserInput?.value);
    const hVal = parseFloat(hoeheInput?.value);
    const tVal = parseFloat(schnittkanteInput?.value);

    // For SK1: If we have diameter and cut width, always calculate height
    // This is the primary relationship for SK1 mirrors
    if (!Number.isNaN(dVal) && !Number.isNaN(tVal)) {
      updateHeightFromDT(dVal, tVal);
    }
    // Fallback: If we only have height and cut width, calculate diameter
    else if (!Number.isNaN(hVal) && !Number.isNaN(tVal) && Number.isNaN(dVal)) {
      updateDiameterFromHT(hVal, tVal);
    }
  };

  // Run initial calculation after a short delay to ensure DOM is fully ready
  setTimeout(performInitialCalculation, 100);

  // ====================== CORE CALCULATION LOGIC ======================

  /**
   * Flag to prevent calculation loops when updating fields programmatically
   */
  let isCalculating = false;

  /**
   * Central input change event handler
   * Determines which input changed and triggers appropriate calculations
   * based on geometric relationships between diameter, height, and cut width
   *
   * @param {Event} e - The input change event
   */
  function handleInputChange(e) {
    // Prevent recursive calculations
    if (isCalculating) {
      return;
    }

    const changed = e.target.name; // "durchmesser", "hoehe_schnittkante" oder "schnittkante"

    /**
     * VALUE EXTRACTION AND VALIDATION
     * ===============================
     * Parse current values from all input fields with safety checks
     */
    const dVal = parseFloat(durchmesserInput?.value); // Durchmesser (diameter)
    const hVal = parseFloat(hoeheInput?.value); // Höhe (segment height)
    const tVal = parseFloat(schnittkanteInput?.value); // Schnittkante (cut width)

    // Early exit if all values are invalid (no calculations possible)
    if (Number.isNaN(dVal) && Number.isNaN(hVal) && Number.isNaN(tVal)) {
      return;
    }

    // Set calculation flag
    isCalculating = true;

    /**
     * CALCULATION DISPATCH LOGIC
     * =========================
     * Determine which calculation to perform based on the changed input
     * Each case maintains two known values and calculates the third
     */
    debugLog(`Input changed: ${changed}, values: D=${dVal}, H=${hVal}, T=${tVal}`);

    switch (changed) {
      case "durchmesser":
        /**
         * DIAMETER CHANGED SCENARIO
         * Diameter (d) changed → Keep cut width (t) → Calculate new height (h)
         * Mathematical relationship: h = f(d, t)
         */
        debugLog("Processing diameter change");
        if (!Number.isNaN(dVal) && !Number.isNaN(tVal)) {
          updateHeightFromDT(dVal, tVal);
        }
        break;

      case "hoehe_schnittkante":
      case "breite_schnittkante":
        /**
         * HEIGHT CHANGED SCENARIO
         * Height (h) changed → Keep cut width (t) → Calculate new diameter (d)
         * Mathematical relationship: d = f(h, t)
         */
        debugLog("Processing height change");
        if (!Number.isNaN(hVal) && !Number.isNaN(tVal)) {
          updateDiameterFromHT(hVal, tVal);
        }
        break;

      case "schnittkante":
        /**
         * CUT WIDTH CHANGED SCENARIO
         * Cut width (t) changed → Keep diameter (d) → Calculate new height (h)
         * Mathematical relationship: h = f(d, t)
         */
        debugLog("Processing cut width change");
        if (!Number.isNaN(dVal) && !Number.isNaN(tVal)) {
          updateHeightFromDT(dVal, tVal);
        }
        break;
    }

    // Clear calculation flag immediately - no delays needed
    isCalculating = false;
  }

  // ====================== OPTIMIZED UPDATE FUNCTIONS ======================

  /**
   * Optimized height update - no delays, precision checking
   * @param {number} d - Circle diameter
   * @param {number} t - Cut width (chord length)
   */
  function updateHeightFromDT(d, t) {
    const newH = calcHeightGivenDT(d, t);
    debugLog(`Calculating height: D=${d}, T=${t} → H=${newH}`);

    if (isFinite(newH) && hoeheInput) {
      const currentValue = parseFloat(hoeheInput.value) || 0;
      const newValue = round(newH);
      const difference = Math.abs(newValue - currentValue);

      // Only update if difference is significant (prevents rounding loops)
      if (difference >= SK1_DEBUG_CONFIG.PRECISION_THRESHOLD) {
        debugLog(`Updating height: ${currentValue} → ${newValue} (diff: ${difference}mm)`);

        // Prevent recursive updates
        isCalculating = true;
        hoeheInput.value = newValue;

        // Trigger summary update directly - no event chain needed
        if (typeof updateSummary === 'function') {
          updateSummary();
          debugLog("Summary updated directly");
        }

        isCalculating = false;
      } else {
        debugLog(`Height update skipped - difference too small: ${difference}mm`);
      }
    }
  }

  /**
   * Optimized diameter update - no delays, precision checking
   * @param {number} h - Segment height
   * @param {number} t - Cut width (chord length)
   */
  function updateDiameterFromHT(h, t) {
    const newD = calcDiameterGivenHT(h, t);
    debugLog(`Calculating diameter: H=${h}, T=${t} → D=${newD}`);

    if (isFinite(newD) && durchmesserInput) {
      const currentValue = parseFloat(durchmesserInput.value) || 0;
      const newValue = round(newD);
      const difference = Math.abs(newValue - currentValue);

      // Only update if difference is significant (prevents rounding loops)
      if (difference >= SK1_DEBUG_CONFIG.PRECISION_THRESHOLD) {
        debugLog(`Updating diameter: ${currentValue} → ${newValue} (diff: ${difference}mm)`);

        // Prevent recursive updates
        isCalculating = true;
        durchmesserInput.value = newValue;

        // Trigger price recalculation via input event
        durchmesserInput.dispatchEvent(new Event("input", { bubbles: true }));
        debugLog("Diameter price recalculation triggered");

        isCalculating = false;
      } else {
        debugLog(`Diameter update skipped - difference too small: ${difference}mm`);
      }
    }
  }

  // ====================== GEOMETRIC CALCULATION FUNCTIONS ======================

  /**
   * Calculates segment height from circle diameter and chord width
   * Uses circle geometry: segment height = radius - distance from center to chord
   *
   * Mathematical Formula:
   * h = 2r - (r - √(r² - (t²/4)))
   *
   * Where:
   * - h = segment height
   * - r = radius (d/2)
   * - t = chord width (cut width)
   *
   * @param {number} d - Circle diameter
   * @param {number} t - Chord width (cut width)
   * @returns {number} Calculated segment height or NaN if impossible geometry
   */
  function calcHeightGivenDT(d, t) {
    const r = d / 2; // Convert diameter to radius

    /**
     * GEOMETRIC VALIDATION
     * ===================
     * Check if the chord width is geometrically possible for given diameter
     * Discriminant must be non-negative: r² - (t²/4) ≥ 0
     * This ensures the chord doesn't exceed the circle diameter
     */
    const disc = r * r - (t * t) / 4; // r² - (t²/4)
    if (disc < 0) return NaN; // Impossible geometry

    /**
     * SEGMENT HEIGHT CALCULATION
     * =========================
     * Calculate distance from center to chord, then segment height
     */
    const distanceToChord = r - Math.sqrt(disc); // Distance from center to chord
    const segmentHeight = 2 * r - distanceToChord; // Full height minus distance

    return segmentHeight;
  }

  /**
   * Calculates circle diameter from segment height and chord width
   * Derived from the inverse of the segment height formula
   *
   * Mathematical Formula:
   * d = (h² + t²/4) / h
   *
   * Where:
   * - d = circle diameter
   * - h = segment height
   * - t = chord width (cut width)
   *
   * @param {number} h - Segment height
   * @param {number} t - Chord width (cut width)
   * @returns {number} Calculated diameter or NaN if invalid input
   */
  function calcDiameterGivenHT(h, t) {
    // Prevent division by zero
    if (h === 0) return NaN;

    /**
     * DIAMETER CALCULATION
     * ===================
     * Uses quadratic relationship between height, chord width, and diameter
     * Formula derived from circle geometry and segment relationships
     */
    const numerator = (t * t) / 4 + h * h; // t²/4 + h²
    return numerator / h; // (t²/4 + h²) / h
  }

  // ====================== UTILITY FUNCTIONS ======================

  /**
   * Rounds numeric values to whole numbers for manufacturing precision
   * Eliminates decimal places that are not practical for physical production
   *
   * @param {number} val - The value to round
   * @returns {number} Rounded integer value
   */
  function round(val) {
    return Math.round(val);
  }

        hoeheInput.value = newValue;

        // Trigger input event to notify other systems (price calculations, summary updates)
        // But do it after a delay to ensure our flag is respected
        setTimeout(() => {
          hoeheInput.dispatchEvent(new Event("input", { bubbles: true }));
          isCalculating = wasCalculating; // Restore previous state
        }, 50);
      }
    }
  }

  /**
   * ORIGINAL: Updates the diameter input based on segment height and cut width
   * Performs geometric calculation and updates DOM element with validation
   *
   * @param {number} h - Segment height
   * @param {number} t - Cut width (chord length)
   */
  function updateDiameterFromHT_Original(h, t) {
    const newD = calcDiameterGivenHT(h, t);

    if (isFinite(newD) && durchmesserInput) {
      const oldValue = durchmesserInput.value;
      const newValue = round(newD);

      // Only update if value actually changed to prevent infinite loops
      if (String(newValue) !== oldValue) {
        // Set flag to prevent triggering input events during programmatic update
        const wasCalculating = isCalculating;
        isCalculating = true;

        durchmesserInput.value = newValue;

        // Trigger input event to notify other systems (price calculations, summary updates)
        // But do it after a delay to ensure our flag is respected
        setTimeout(() => {
          durchmesserInput.dispatchEvent(new Event("input", { bubbles: true }));
          isCalculating = wasCalculating; // Restore previous state
        }, 50);
      }
    }
  }

  // ====================== GEOMETRIC CALCULATION FUNCTIONS ======================

  /**
   * Calculates segment height from circle diameter and chord width
   * Uses circle geometry: segment height = radius - distance from center to chord
   *
   * Mathematical Formula:
   * h = 2r - (r - √(r² - (t²/4)))
   *
   * Where:
   * - h = segment height
   * - r = radius (d/2)
   * - t = chord width (cut width)
   *
   * @param {number} d - Circle diameter
   * @param {number} t - Chord width (cut width)
   * @returns {number} Calculated segment height or NaN if impossible geometry
   *
   * @example
   * calcHeightGivenDT(100, 60) // Returns segment height for 100mm diameter, 60mm cut
   */
  function calcHeightGivenDT(d, t) {
    const r = d / 2; // Convert diameter to radius

    /**
     * GEOMETRIC VALIDATION
     * ===================
     * Check if the chord width is geometrically possible for given diameter
     * Discriminant must be non-negative: r² - (t²/4) ≥ 0
     * This ensures the chord doesn't exceed the circle diameter
     */
    const disc = r * r - (t * t) / 4; // r² - (t²/4)
    if (disc < 0) return NaN; // Impossible geometry

    /**
     * SEGMENT HEIGHT CALCULATION
     * =========================
     * Calculate distance from center to chord, then segment height
     */
    const distanceToChord = r - Math.sqrt(disc); // Distance from center to chord
    const segmentHeight = 2 * r - distanceToChord; // Full height minus distance

    return segmentHeight;
  }

  /**
   * Calculates circle diameter from segment height and chord width
   * Derived from the inverse of the segment height formula
   *
   * Mathematical Formula:
   * d = (h² + t²/4) / h
   *
   * Where:
   * - d = circle diameter
   * - h = segment height
   * - t = chord width (cut width)
   *
   * @param {number} h - Segment height
   * @param {number} t - Chord width (cut width)
   * @returns {number} Calculated diameter or NaN if invalid input
   *
   * @example
   * calcDiameterGivenHT(30, 60) // Returns diameter for 30mm height, 60mm cut
   */
  function calcDiameterGivenHT(h, t) {
    // Prevent division by zero
    if (h === 0) return NaN;

    /**
     * DIAMETER CALCULATION
     * ===================
     * Uses quadratic relationship between height, chord width, and diameter
     * Formula derived from circle geometry and segment relationships
     */
    const numerator = (t * t) / 4 + h * h; // t²/4 + h²
    return numerator / h; // (t²/4 + h²) / h
  }

  // ====================== UTILITY FUNCTIONS ======================

  /**
   * Rounds numeric values to whole numbers for manufacturing precision
   * Eliminates decimal places that are not practical for physical production
   *
   * @param {number} val - The value to round
   * @returns {number} Rounded integer value
   */
  function round(val) {
    return Math.round(val);
  }

  // ====================== TESTING/VERIFICATION ======================

  /**
   * Test calculation with expected values for verification
   * Expected: diameter=400, cutWidth=150 → height≈385
   */
  if (typeof window !== "undefined" && window.console) {
    // Make calculation functions available for debugging
    window.SK1_calcHeightGivenDT = calcHeightGivenDT;
    window.SK1_calcDiameterGivenHT = calcDiameterGivenHT;
    window.SK1_round = round;

    // Make debug configuration available for runtime changes
    window.SK1_DEBUG_CONFIG = SK1_DEBUG_CONFIG;

    // Debug helper functions
    window.SK1_switchMode = function(mode) {
      const validModes = ["SIMPLIFIED_EVENTS", "DIRECT_PRICE_CALC", "ORIGINAL"];
      if (validModes.includes(mode)) {
        SK1_DEBUG_CONFIG.OPTIMIZATION_MODE = mode;
        debugLog(`Switched to optimization mode: ${mode}`);
        console.log(`✅ SK1 optimization mode switched to: ${mode}`);
      } else {
        console.log(`❌ Invalid mode. Valid modes: ${validModes.join(', ')}`);
      }
    };

    window.SK1_toggleDebug = function() {
      SK1_DEBUG_CONFIG.DEBUG_MODE = !SK1_DEBUG_CONFIG.DEBUG_MODE;
      console.log(`✅ SK1 debug mode: ${SK1_DEBUG_CONFIG.DEBUG_MODE ? 'ON' : 'OFF'}`);
    };

    window.SK1_setTiming = function(debounce = 200, update = 10) {
      SK1_DEBUG_CONFIG.DEBOUNCE_DELAY = debounce;
      SK1_DEBUG_CONFIG.UPDATE_DELAY = update;
      console.log(`✅ SK1 timing updated - Debounce: ${debounce}ms, Update: ${update}ms`);
    };

    // Test function to demonstrate the difference between modes
    window.SK1_testHeightChange = function() {
      const hoeheInput = document.querySelector('.option-input[name="hoehe_schnittkante"]');
      if (hoeheInput) {
        console.log(`🧪 Testing height change with mode: ${SK1_DEBUG_CONFIG.OPTIMIZATION_MODE}`);
        console.log(`📊 Current values - Diameter: ${durchmesserInput?.value}, Height: ${hoeheInput.value}, Cut: ${document.querySelector('.option-input[name="schnittkante"]')?.value}`);

        // Simulate user changing height to 600
        const originalValue = hoeheInput.value;
        console.log(`🔄 Changing height from ${originalValue} to 600...`);

        hoeheInput.value = '600';
        hoeheInput.dispatchEvent(new Event('input', { bubbles: true }));

        setTimeout(() => {
          console.log(`📋 Result - Diameter updated to: ${durchmesserInput?.value}`);
        }, 1000);
      } else {
        console.log('❌ Height input not found');
      }
    };

    // Performance timing test
    window.SK1_performanceTest = function() {
      const hoeheInput = document.querySelector('.option-input[name="hoehe_schnittkante"]');
      if (!hoeheInput) return console.log('❌ Height input not found');

      console.log(`⏱️ Performance test for mode: ${SK1_DEBUG_CONFIG.OPTIMIZATION_MODE}`);

      const startTime = performance.now();
      let updateCount = 0;

      // Monitor for updates
      const observer = new MutationObserver(() => {
        updateCount++;
        if (updateCount === 1) {
          const endTime = performance.now();
          console.log(`📊 Update completed in: ${(endTime - startTime).toFixed(2)}ms`);
          observer.disconnect();
        }
      });

      observer.observe(durchmesserInput, { attributes: true, attributeFilter: ['value'] });

      // Trigger change
      hoeheInput.value = '500';
      hoeheInput.dispatchEvent(new Event('input', { bubbles: true }));
    };    // Log initial configuration
    if (SK1_DEBUG_CONFIG.DEBUG_MODE) {
      console.log('🔧 SK1 Debug Mode Active');
      console.log('📊 Available commands:');
      console.log('  SK1_switchMode("SIMPLIFIED_EVENTS") - Test simplified event approach');
      console.log('  SK1_switchMode("DIRECT_PRICE_CALC") - Test direct price calculation');
      console.log('  SK1_switchMode("ORIGINAL") - Use original approach');
      console.log('  SK1_toggleDebug() - Toggle debug logging');
      console.log('  SK1_setTiming(debounce, update) - Adjust timing');
      console.log('  SK1_testHeightChange() - Test height change behavior');
      console.log('  SK1_performanceTest() - Measure update performance');
      console.log(`📋 Current mode: ${SK1_DEBUG_CONFIG.OPTIMIZATION_MODE}`);
      console.log(`⏱️ Current timing - Debounce: ${SK1_DEBUG_CONFIG.DEBOUNCE_DELAY}ms, Update: ${SK1_DEBUG_CONFIG.UPDATE_DELAY}ms`);
      console.log('');
      console.log('🎯 TO TEST THE DIFFERENCE:');
      console.log('1. Change the HEIGHT field (hoehe_schnittkante) - NOT the diameter!');
      console.log('2. Watch how the diameter gets recalculated');
      console.log('3. Compare different modes for speed and price updates');
    }
  }
});

/**
 * Future Enhancement Ideas:
 *
 * 1. Visual feedback for impossible geometries:
 *    function showGeometryWarning(input, message) {
 *      input.classList.add('geometry-error');
 *      showTooltip(input, message);
 *    }
 *
 * 2. Advanced validation with tolerance ranges:
 *    const geometryLimits = {
 *      minDiameter: 100,
 *      maxDiameter: 2000,
 *      minCutRatio: 0.1,  // Minimum cut width as ratio of diameter
 *      maxCutRatio: 0.9   // Maximum cut width as ratio of diameter
 *    };
 *
 * 3. Real-time geometry preview:
 *    function drawSegmentPreview(diameter, height, cutWidth) {
 *      // Canvas or SVG visualization of the cut mirror shape
 *    }
 *
 * 4. Manufacturing constraints validation:
 *    function validateManufacturingLimits(dimensions) {
 *      // Check against production capabilities and material limits
 *    }
 *
 * 5. Unit conversion support:
 *    const unitConverter = {
 *      mmToCm: (val) => val / 10,
 *      cmToMm: (val) => val * 10,
 *      inchToMm: (val) => val * 25.4
 *    };
 */
