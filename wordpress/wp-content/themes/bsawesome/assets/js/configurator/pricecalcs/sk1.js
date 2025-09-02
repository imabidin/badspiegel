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
 * @version 2.2.0
 * @package Configurator
 * @subpackage PriceCalculations
 */

import { isSK, isSK1, durchmesserInput } from "../variables";

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
  const schnittkanteInput = document.querySelector(
    '.option-input[name="schnittkante"]'
  );

  /**
   * INPUT VALIDATION AND FILTERING
   * ==============================
   * Create array of available inputs, filtering out null references
   * Enables graceful handling of partial input configurations
   */
  const inputs = [durchmesserInput, hoeheInput, schnittkanteInput].filter(
    Boolean
  );

  // Early exit if no valid inputs found
  if (inputs.length === 0) {
    return;
  }

  // ====================== DEBOUNCING SETUP ======================

  /**
   * Debouncing timeout for input handling
   * Prevents calculation spam while user is typing
   */
  let debounceTimeout = null;

  /**
   * Creates a debounced version of the calculation function
   * Delays execution until user stops typing for specified delay
   *
   * @param {Function} func - Function to debounce
   * @param {number} delay - Delay in milliseconds
   * @returns {Function} Debounced function
   */
  function debounce(func, delay) {
    return function (...args) {
      // Clear existing timeout
      if (debounceTimeout) {
        clearTimeout(debounceTimeout);
      }

      // Set new timeout
      debounceTimeout = setTimeout(() => {
        func.apply(this, args);
      }, delay);
    };
  }

  // ====================== EVENT LISTENER SETUP ======================

  /**
   * Attach input event listeners to all available input fields
   * Uses debounced handler to prevent calculation interference while typing
   */

  // Create debounced version of the input handler (500ms delay)
  const debouncedInputHandler = debounce(handleInputChange, 500);

  inputs.forEach((el) => {
    // Use debounced handler for 'input' events (while typing)
    el.addEventListener("input", debouncedInputHandler);

    // Use immediate handler for 'blur' events (when leaving field)
    el.addEventListener("blur", handleInputChange);
  });

  // ====================== INITIAL CALCULATION ======================

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
    switch (changed) {
      case "durchmesser":
        /**
         * DIAMETER CHANGED SCENARIO
         * Diameter (d) changed → Keep cut width (t) → Calculate new height (h)
         * Mathematical relationship: h = f(d, t)
         */
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
        if (!Number.isNaN(dVal) && !Number.isNaN(tVal)) {
          updateHeightFromDT(dVal, tVal);
        }
        break;
    }

    // Clear calculation flag after processing
    setTimeout(() => {
      isCalculating = false;
    }, 100);
  }

  // ====================== CALCULATION UPDATE FUNCTIONS ======================

  /**
   * Updates the segment height input based on diameter and cut width
   * Performs geometric calculation and updates DOM element with validation
   *
   * @param {number} d - Circle diameter
   * @param {number} t - Cut width (chord length)
   */
  function updateHeightFromDT(d, t) {
    const newH = calcHeightGivenDT(d, t);

    if (isFinite(newH) && hoeheInput) {
      const oldValue = hoeheInput.value;
      const newValue = round(newH);

      // Only update if value actually changed to prevent infinite loops
      if (String(newValue) !== oldValue) {
        // Set flag to prevent triggering input events during programmatic update
        const wasCalculating = isCalculating;
        isCalculating = true;

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
   * Updates the diameter input based on segment height and cut width
   * Performs geometric calculation and updates DOM element with validation
   *
   * @param {number} h - Segment height
   * @param {number} t - Cut width (chord length)
   */
  function updateDiameterFromHT(h, t) {
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
