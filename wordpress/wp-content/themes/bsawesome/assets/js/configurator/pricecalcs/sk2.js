/**
 * Product Configurator - SK2 Special Cut Calculations Module
 *
 * This module handles specialized mathematical calculations for SK2 (double cut edge)
 * mirror configurations. It provides bidirectional conversion between combined
 * width/height values and equivalent diameter measurements using empirically derived
 * linear transformation formulas for complex geometric relationships.
 *
 * Features:
 * - Bidirectional mathematical conversion (width/height ↔ diameter)
 * - Empirically calibrated transformation coefficients (a=1.077, b=0.08)
 * - Real-time input synchronization with debounced updates
 * - Immediate updates on field blur for instant feedback
 * - Performance optimization through change detection
 * - Event propagation for dependent calculation systems
 * - Robust input validation and error handling
 *
 * Mathematical Model:
 * - Diameter = a × (width/height) + b
 * - Width/Height = (diameter - b) ÷ a
 * - Coefficients derived from manufacturing specifications
 *
 * @version 2.6.0
 * @package Configurator
 * @subpackage PriceCalculations
 */

import { isSK, isSK2 } from "../variables";

// ====================== MODULE INITIALIZATION ======================

/**
 * Initialize SK2 calculations when DOM is ready
 * Validates product type and establishes bidirectional input relationships
 */
document.addEventListener("DOMContentLoaded", () => {
  // ====================== PRODUCT TYPE VALIDATION ======================

  /**
   * Product compatibility check
   * Ensures this module only runs on SK2 (double cut edge) products
   * Early return prevents unnecessary processing on incompatible products
   */
  if (!isSK || !isSK2) {
    // console.warn('[SK2] Nicht die richtige Produktseite.');
    return;
  }

  // ====================== MATHEMATICAL CONSTANTS ======================

  /**
   * Empirically derived transformation coefficients
   * These values are calibrated based on manufacturing specifications
   * and geometric relationships specific to SK2 cut patterns
   */

  /**
   * Linear coefficient (slope) for diameter calculation
   * Represents the proportional relationship between dimensions
   * @type {number}
   */
  const a = 1.077;

  /**
   * Offset coefficient (y-intercept) for diameter calculation
   * Accounts for geometric constants in the cutting process
   * @type {number}
   */
  const b = 0.08;

  // ====================== MATHEMATICAL TRANSFORMATION FUNCTIONS ======================

  /**
   * Calculates equivalent diameter from combined width/height value
   * Uses linear transformation: d = a × bh + b
   *
   * @param {number} bh - Combined width/height measurement
   * @returns {number} Calculated equivalent diameter
   *
   * @example
   * computeDiameterFromBH(100) // Returns ~107.8 for 100mm width/height
   */
  function computeDiameterFromBH(bh) {
    return a * bh + b;
  }

  /**
   * Calculates combined width/height from diameter value
   * Uses inverse linear transformation: bh = (d - b) ÷ a
   *
   * @param {number} d - Diameter measurement
   * @returns {number} Calculated combined width/height value
   *
   * @example
   * computeBHFromDiameter(107.8) // Returns ~100 for 107.8mm diameter
   */
  function computeBHFromDiameter(d) {
    return (d - b) / a;
  }

  // ====================== DOM ELEMENT DISCOVERY ======================

  /**
   * Input field references for bidirectional calculations
   * Both fields must be present for proper functionality
   */

  /**
   * Combined width/height input field
   * Represents the rectangular dimension equivalent
   * @type {HTMLInputElement|null}
   */
  const bhInput = document.querySelector('.option-input[name="breite_hoehe"]');

  /**
   * Diameter input field
   * Represents the circular dimension equivalent
   * @type {HTMLInputElement|null}
   */
  const dInput = document.querySelector('.option-input[name="durchmesser"]');

  /**
   * INPUT VALIDATION
   * ===============
   * Ensure both required input fields are available in DOM
   */
  if (!bhInput || !dInput) {
    return;
  }

  // ====================== PERFORMANCE OPTIMIZATION UTILITIES ======================

  /**
   * Creates a debounced version of a function for performance optimization
   * Delays execution until a specified period of inactivity has passed
   *
   * @param {Function} func - The function to debounce
   * @param {number} delay - Delay in milliseconds before execution
   * @returns {Function} Debounced function wrapper
   *
   * @example
   * const debouncedCalc = debounce(calculateValues, 500);
   * input.addEventListener('input', debouncedCalc);
   */
  function debounce(func, delay) {
    let timeoutId;
    return function (...args) {
      // Clear any existing timeout to reset the delay
      if (timeoutId) clearTimeout(timeoutId);

      // Set new timeout for delayed execution
      timeoutId = setTimeout(() => {
        func.apply(this, args);
      }, delay);
    };
  }

  // ====================== BIDIRECTIONAL UPDATE FUNCTIONS ======================

  /**
   * Updates diameter field based on width/height input changes
   * Performs validation, calculation, and change detection for efficiency
   */
  function updateDiameterFromBH() {
    // Parse and validate input value
    const valBH = parseFloat(bhInput.value);
    if (isNaN(valBH)) return; // Skip invalid inputs

    // Calculate new diameter and round to whole number
    const newD = Math.round(computeDiameterFromBH(valBH));

    /**
     * CHANGE DETECTION OPTIMIZATION
     * ============================
     * Only update if value actually changed to prevent unnecessary events
     */
    if (String(newD) !== dInput.value) {
      dInput.value = newD;

      // Dispatch input event for dependent systems (price calculations, etc.)
      dInput.dispatchEvent(new Event("input", { bubbles: true }));
    }
  }

  /**
   * Updates width/height field based on diameter input changes
   * Performs validation, calculation, and change detection for efficiency
   */
  function updateBHFromDiameter() {
    // Parse and validate input value
    const valD = parseFloat(dInput.value);
    if (isNaN(valD)) return; // Skip invalid inputs

    // Calculate new width/height and round to whole number
    const newBH = Math.round(computeBHFromDiameter(valD));

    /**
     * CHANGE DETECTION OPTIMIZATION
     * ============================
     * Only update if value actually changed to prevent unnecessary events
     */
    if (String(newBH) !== bhInput.value) {
      bhInput.value = newBH;

      // Dispatch input event for dependent systems
      bhInput.dispatchEvent(new Event("input", { bubbles: true }));
    }
  }

  // ====================== EVENT HANDLER OPTIMIZATION ======================

  /**
   * Debounced event handlers for input fields
   * Reduces calculation frequency during rapid typing while maintaining responsiveness
   */

  /**
   * Debounced handler for width/height input changes
   * 500ms delay prevents excessive calculations during user typing
   */
  const debouncedBHHandler = debounce(updateDiameterFromBH, 500);

  /**
   * Debounced handler for diameter input changes
   * 500ms delay prevents excessive calculations during user typing
   */
  const debouncedDHandler = debounce(updateBHFromDiameter, 500);

  // ====================== EVENT LISTENER REGISTRATION ======================

  /**
   * PRIMARY INPUT EVENT LISTENERS
   * ============================
   * Debounced handlers for real-time updates during typing
   * Balances responsiveness with performance
   */

  // Width/height input changes trigger diameter calculation
  bhInput.addEventListener("input", debouncedBHHandler);

  // Diameter input changes trigger width/height calculation
  dInput.addEventListener("input", debouncedDHandler);

  /**
   * IMMEDIATE BLUR EVENT LISTENERS
   * =============================
   * Instant updates when user leaves field for immediate feedback
   * Ensures calculations complete when user moves to next field
   */

  // Immediate diameter update when leaving width/height field
  bhInput.addEventListener("blur", updateDiameterFromBH);

  // Immediate width/height update when leaving diameter field
  dInput.addEventListener("blur", updateBHFromDiameter);

  /**
   * INITIAL SYNCHRONIZATION ON PAGE LOAD
   * ====================================
   * Automatically calculate diameter from breite_hoehe value on page start
   * This ensures consistent values when the page loads with pre-filled data
   */

  // Check if breite_hoehe has a value and calculate diameter
  if (bhInput.value && bhInput.value.trim() !== "") {
    updateDiameterFromBH();
  }
  // Alternatively, if diameter has a value but breite_hoehe doesn't, calculate breite_hoehe
  else if (dInput.value && dInput.value.trim() !== "" && (!bhInput.value || bhInput.value.trim() === "")) {
    updateBHFromDiameter();
  }
});

/**
 * Future Enhancement Ideas:
 *
 * 1. Configurable transformation coefficients:
 *    const transformationProfiles = {
 *      standard: { a: 1.077, b: 0.08 },
 *      premium: { a: 1.085, b: 0.05 },
 *      custom: { a: 1.070, b: 0.10 }
 *    };
 *
 * 2. Non-linear transformation support:
 *    function polynomialTransform(bh, coefficients) {
 *      return coefficients.reduce((sum, coeff, index) => {
 *        return sum + coeff * Math.pow(bh, index);
 *      }, 0);
 *    }
 *
 * 3. Validation ranges and manufacturing limits:
 *    const validationRules = {
 *      minDimension: 50,
 *      maxDimension: 2000,
 *      maxDiameterRatio: 1.2,
 *      warningThreshold: 1500
 *    };
 *
 * 4. Unit conversion support:
 *    const unitConverters = {
 *      mmToCm: (val) => val / 10,
 *      cmToInch: (val) => val / 2.54,
 *      inchToMm: (val) => val * 25.4
 *    };
 *
 * 5. Precision control for different manufacturing tolerances:
 *    function roundToPrecision(value, precision = 1) {
 *      const factor = Math.pow(10, precision);
 *      return Math.round(value * factor) / factor;
 *    }
 */
