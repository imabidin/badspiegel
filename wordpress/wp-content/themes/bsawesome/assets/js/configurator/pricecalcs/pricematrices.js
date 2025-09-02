/**
 * Product Configurator - Price Matrices Module
 *
 * This module handles dynamic price calculations based on dimension inputs.
 * It provides intelligent rounding algorithms and automatic price matrix selection
 * for different product configurations including single dimensions (diameter) and
 * dual dimensions (width x height) with fallback rounding strategies.
 *
 * Features:
 * - Single dimension price calculation with 100-unit rounding
 * - Single dimension price calculation with 50-unit rounding
 * - Single dimension price calculation with 10-unit rounding
 * - Dual dimension price calculation with progressive rounding (100→50)
 * - Price matrix caching for performance optimization
 * - DOM fallback mechanisms for flexible option matching
 * - Automatic select element updates with event triggering
 * - Data attribute synchronization for price display
 * - Prevention of redundant calculations through value caching
 *
 * @version 2.2.0
 * @package Configurator
 * @subpackage PriceCalculations
 */

import { roundUp, updateSelectAndTrigger } from "./../functions";

// ====================== UTILITY FUNCTIONS ======================

/**
 * Searches for a matching option in the price matrix using cache and DOM fallback
 * Provides a flexible mechanism to find options either from pre-built cache
 * or by searching the DOM when cache misses occur
 *
 * @param {Object} pricematrixCache - Pre-built cache of option elements by key
 * @param {HTMLSelectElement} pricematrixSelect - The select element containing price options
 * @param {string} key - The key to search for (e.g., "400", "600x800")
 * @param {Function} [domFallbackFn] - Optional DOM search function for cache misses
 * @returns {HTMLOptionElement|null} The matching option element or null if not found
 *
 * @example
 * const option = findOption(cache, selectEl, "400", (select, key) => {
 *   return Array.from(select.options).find(opt => opt.value === key);
 * });
 */
function findOption(pricematrixCache, pricematrixSelect, key, domFallbackFn) {
  // First attempt: Check the pre-built cache for performance
  let optionToSelect = pricematrixCache[key];

  // Second attempt: Use DOM fallback function if cache miss occurs
  if (!optionToSelect && typeof domFallbackFn === "function") {
    optionToSelect = domFallbackFn(pricematrixSelect, key);
  }

  return optionToSelect;
}

// ====================== SINGLE DIMENSION CALCULATIONS ======================

/**
 * Calculates price for single dimension inputs (e.g., diameter for round mirrors)
 * Rounds input values to the next multiple of 100 and selects corresponding
 * price matrix option with automatic data attribute updates
 *
 * @param {HTMLInputElement} inputElement - The dimension input field
 * @param {HTMLSelectElement} pricematrixSelect - The price matrix select element
 * @param {Object} pricematrixCache - Cached option elements for performance
 * @param {Object} lastValueCache - Cache object to prevent redundant calculations
 * @param {number} lastValueCache.lastRoundedValue - Previously calculated rounded value
 *
 * @example
 * // Usage for diameter input
 * calcPrice1x100(durchmesserInput, matrixSelect, cache, { lastRoundedValue: null });
 *
 * // Input: 350 → Rounded: 400 → Selects option with value "400"
 */
export function calcPrice1x100(
  inputElement,
  pricematrixSelect,
  pricematrixCache,
  lastValueCache
) {
  // Parse and validate input value
  const inputValue = parseInt(inputElement.value, 10);
  if (isNaN(inputValue)) return;

  // Get available cache keys for fallback logic
  const cacheKeys = Object.keys(pricematrixCache)
    .map((k) => parseInt(k, 10))
    .sort((a, b) => a - b);

  let valueToUse = inputValue;

  // First: Check if exact input value exists in cache
  if (pricematrixCache[String(inputValue)]) {
    valueToUse = inputValue;
  } else {
    // Second: Try rounded value (next multiple of 100)
    const roundedValue = roundUp(inputValue);
    if (pricematrixCache[String(roundedValue)]) {
      valueToUse = roundedValue;
    } else {
      // Third: Find the next available size that's >= input value
      const nextAvailableSize = cacheKeys.find((size) => size >= inputValue);
      if (nextAvailableSize) {
        valueToUse = nextAvailableSize;
      } else {
        return; // No suitable option found
      }
    }
  }

  // Performance optimization: Skip if value hasn't changed
  if (lastValueCache.lastRoundedValue === valueToUse) return;
  lastValueCache.lastRoundedValue = valueToUse;

  /**
   * OPTION SEARCH WITH DOM FALLBACK
   * ===============================
   * Attempts to find matching option using cache first, then DOM search
   * The fallback function searches by data-label attribute for flexibility
   */
  const optionToSelect = findOption(
    pricematrixCache,
    pricematrixSelect,
    String(valueToUse),
    (pricematrixSelect, key) => {
      // DOM fallback: Search by data-label attribute
      return Array.from(pricematrixSelect.options).find((currentOption) => {
        const labelValue = parseInt(currentOption.dataset.label, 10);
        return !isNaN(labelValue) && labelValue === parseInt(key, 10);
      });
    }
  );

  // Exit if no matching option found
  if (!optionToSelect) return;

  // Update select element and trigger change events
  updateSelectAndTrigger(pricematrixSelect, optionToSelect);

  // Synchronize price data attribute for display systems
  inputElement.dataset.price = optionToSelect.dataset.price || "0";
}

/**
 * Calculates price for single dimension inputs with 50-unit rounding
 * Similar to calcPrice1x100 but rounds to the next multiple of 50 instead of 100
 * Ideal for configurations with smaller step sizes (e.g., 150, 200, 250, 300, 350, 400)
 *
 * @param {HTMLInputElement} inputElement - The dimension input field
 * @param {HTMLSelectElement} pricematrixSelect - The price matrix select element
 * @param {Object} pricematrixCache - Cached option elements for performance
 * @param {Object} lastValueCache - Cache object to prevent redundant calculations
 * @param {number} lastValueCache.lastRoundedValue - Previously calculated rounded value
 *
 * @example
 * // Usage for width input with 50-unit steps
 * calcPrice1x50(breiteInput, matrixSelect, cache, { lastRoundedValue: null });
 *
 * // Input: 175 → Rounded: 200 → Selects option with value "200"
 * // Input: 320 → Rounded: 350 → Selects option with value "350"
 */
export function calcPrice1x50(
  inputElement,
  pricematrixSelect,
  pricematrixCache,
  lastValueCache
) {
  // Parse and validate input value
  const inputValue = parseInt(inputElement.value, 10);
  if (isNaN(inputValue)) return;

  // Get available cache keys for fallback logic
  const cacheKeys = Object.keys(pricematrixCache)
    .map((k) => parseInt(k, 10))
    .sort((a, b) => a - b);

  let valueToUse = inputValue;

  // First: Check if exact input value exists in cache
  if (pricematrixCache[String(inputValue)]) {
    valueToUse = inputValue;
  } else {
    // Second: Try rounded value (next multiple of 50)
    const roundedValue = Math.ceil(inputValue / 50) * 50;
    if (pricematrixCache[String(roundedValue)]) {
      valueToUse = roundedValue;
    } else {
      // Third: Find the next available size that's >= input value
      const nextAvailableSize = cacheKeys.find((size) => size >= inputValue);
      if (nextAvailableSize) {
        valueToUse = nextAvailableSize;
      } else {
        return; // No suitable option found
      }
    }
  }

  // Performance optimization: Skip if value hasn't changed
  if (lastValueCache.lastRoundedValue === valueToUse) return;
  lastValueCache.lastRoundedValue = valueToUse;

  /**
   * OPTION SEARCH WITH DOM FALLBACK
   * ===============================
   * Attempts to find matching option using cache first, then DOM search
   * The fallback function searches by data-label attribute for flexibility
   */
  const optionToSelect = findOption(
    pricematrixCache,
    pricematrixSelect,
    String(valueToUse),
    (pricematrixSelect, key) => {
      // DOM fallback: Search by data-label attribute
      return Array.from(pricematrixSelect.options).find((currentOption) => {
        const labelValue = parseInt(currentOption.dataset.label, 10);
        return !isNaN(labelValue) && labelValue === parseInt(key, 10);
      });
    }
  );

  // Exit if no matching option found
  if (!optionToSelect) return;

  // Update select element and trigger change events
  updateSelectAndTrigger(pricematrixSelect, optionToSelect);

  // Synchronize price data attribute for display systems
  inputElement.dataset.price = optionToSelect.dataset.price || "0";
}

/**
 * Calculates price for single dimension inputs with 10-unit rounding
 * Similar to calcPrice1x100 and calcPrice1x50 but rounds to the next multiple of 10
 * Ideal for configurations with very fine step sizes (e.g., 150, 160, 170, 180, ... 400)
 *
 * @param {HTMLInputElement} inputElement - The dimension input field
 * @param {HTMLSelectElement} pricematrixSelect - The price matrix select element
 * @param {Object} pricematrixCache - Cached option elements for performance
 * @param {Object} lastValueCache - Cache object to prevent redundant calculations
 * @param {number} lastValueCache.lastRoundedValue - Previously calculated rounded value
 *
 * @example
 * // Usage for depth input with 10-unit steps
 * calcPrice1x10(tiefeInput, matrixSelect, cache, { lastRoundedValue: null });
 *
 * // Input: 165 → Rounded: 170 → Selects option with value "170"
 * // Input: 235 → Rounded: 240 → Selects option with value "240"
 */
export function calcPrice1x10(
  inputElement,
  pricematrixSelect,
  pricematrixCache,
  lastValueCache
) {
  // Parse and validate input value
  const inputValue = parseInt(inputElement.value, 10);
  if (isNaN(inputValue)) return;

  // Get available cache keys for fallback logic
  const cacheKeys = Object.keys(pricematrixCache)
    .map((k) => parseInt(k, 10))
    .sort((a, b) => a - b);

  let valueToUse = inputValue;

  // First: Check if exact input value exists in cache
  if (pricematrixCache[String(inputValue)]) {
    valueToUse = inputValue;
  } else {
    // Second: Try rounded value (next multiple of 10)
    const roundedValue = Math.ceil(inputValue / 10) * 10;
    if (pricematrixCache[String(roundedValue)]) {
      valueToUse = roundedValue;
    } else {
      // Third: Find the next available size that's >= input value
      const nextAvailableSize = cacheKeys.find((size) => size >= inputValue);
      if (nextAvailableSize) {
        valueToUse = nextAvailableSize;
      } else {
        return; // No suitable option found
      }
    }
  }

  // Performance optimization: Skip if value hasn't changed
  if (lastValueCache.lastRoundedValue === valueToUse) return;
  lastValueCache.lastRoundedValue = valueToUse;

  /**
   * OPTION SEARCH WITH DOM FALLBACK
   * ===============================
   * Attempts to find matching option using cache first, then DOM search
   * The fallback function searches by data-label attribute for flexibility
   */
  const optionToSelect = findOption(
    pricematrixCache,
    pricematrixSelect,
    String(valueToUse),
    (pricematrixSelect, key) => {
      // DOM fallback: Search by data-label attribute
      return Array.from(pricematrixSelect.options).find((currentOption) => {
        const labelValue = parseInt(currentOption.dataset.label, 10);
        return !isNaN(labelValue) && labelValue === parseInt(key, 10);
      });
    }
  );

  // Exit if no matching option found
  if (!optionToSelect) return;

  // Update select element and trigger change events
  updateSelectAndTrigger(pricematrixSelect, optionToSelect);

  // Synchronize price data attribute for display systems
  inputElement.dataset.price = optionToSelect.dataset.price || "0";
}

// ====================== DUAL DIMENSION CALCULATIONS ======================

/**
 * Calculates price for dual dimension inputs (width x height for rectangular mirrors)
 * Uses progressive rounding strategy: attempts 100-unit rounding first, falls back
 * to 50-unit rounding if no exact match found in price matrix
 *
 * @param {HTMLInputElement} inputElement1 - First dimension input (usually width)
 * @param {HTMLInputElement} inputElement2 - Second dimension input (usually height)
 * @param {HTMLSelectElement} pricematrixSelect - The price matrix select element
 * @param {Object} pricematrixCache - Cached option elements (currently unused but preserved for consistency)
 * @param {Object} lastValueCache - Cache object to prevent redundant calculations
 * @param {string} lastValueCache.lastRoundedValue - Previously calculated dimension string
 *
 * @example
 * // Usage for width x height inputs
 * calcPrice2x100(breiteInput, hoeheInput, matrixSelect, cache, { lastRoundedValue: null });
 *
 * // Input: 350x780 → Try: 400x800 → Found: Select option
 * // Input: 120x340 → Try: 200x400 → Not found → Try: 150x350 → Found: Select option
 */
export function calcPrice2x100(
  inputElement1,
  inputElement2,
  pricematrixSelect,
  pricematrixCache,
  lastValueCache
) {
  // Parse and validate both input values
  const inputValue1 = parseInt(inputElement1.value, 10);
  const inputValue2 = parseInt(inputElement2.value, 10);

  if (isNaN(inputValue1) || isNaN(inputValue2)) return;

  /**
   * PROGRESSIVE ROUNDING STRATEGY WITH INTELLIGENT FALLBACK
   * ======================================================
   * Step 1: Round to nearest 100 (primary strategy for common sizes)
   * Step 2: Round to nearest 50 (fallback for intermediate sizes)
   * Step 3: Smart fallback to next available size in matrix
   */

  // Step 1: Round both dimensions to next multiple of 100
  let roundedValue1 = Math.ceil(inputValue1 / 100) * 100;
  let roundedValue2 = Math.ceil(inputValue2 / 100) * 100;
  let potentialValue = `${roundedValue1}x${roundedValue2}`;

  // Performance optimization: Skip if dimension string hasn't changed
  if (lastValueCache.lastRoundedValue === potentialValue) return;

  /**
   * PRIMARY OPTION SEARCH (100-unit rounding)
   * ========================================
   * Search for exact match in price matrix using 100-unit rounded dimensions
   */
  let optionToSelect = Array.from(pricematrixSelect.options).find(
    (opt) => opt.value === potentialValue
  );

  /**
   * FALLBACK OPTION SEARCH (50-unit rounding)
   * ========================================
   * If 100-unit rounding doesn't yield results, try 50-unit rounding
   * This covers intermediate sizes not available in the primary matrix
   */
  if (!optionToSelect) {
    roundedValue1 = Math.ceil(inputValue1 / 50) * 50;
    roundedValue2 = Math.ceil(inputValue2 / 50) * 50;
    potentialValue = `${roundedValue1}x${roundedValue2}`;

    optionToSelect = Array.from(pricematrixSelect.options).find(
      (opt) => opt.value === potentialValue
    );
  }

  /**
   * INTELLIGENT SIZE FALLBACK (similar to calcPrice1x100)
   * ====================================================
   * If both 100 and 50 unit rounding fail, find next available size combination
   */
  if (!optionToSelect) {
    // Get all available dimension combinations from the select options
    const availableOptions = Array.from(pricematrixSelect.options)
      .map((opt) => opt.value)
      .filter((val) => val.includes("x"))
      .map((val) => {
        const [w, h] = val.split("x").map((v) => parseInt(v, 10));
        return {
          value: val,
          width: w,
          height: h,
          element: pricematrixSelect.querySelector(`option[value="${val}"]`),
        };
      })
      .filter((opt) => !isNaN(opt.width) && !isNaN(opt.height));

    // Find the smallest option that can accommodate both dimensions
    const suitableOption = availableOptions.find(
      (opt) => opt.width >= roundedValue1 && opt.height >= roundedValue2
    );

    if (suitableOption) {
      optionToSelect = suitableOption.element;
      potentialValue = suitableOption.value;
    }
  }

  // Exit if no option found even with intelligent fallback
  if (!optionToSelect) return;

  /**
   * MATRIX UPDATE AND EVENT PROPAGATION
   * ===================================
   * Update the price matrix selection and propagate changes to dependent systems
   * Now using the same pattern as calcPrice1x100 for consistency
   */

  // Update the cache with successful calculation result
  lastValueCache.lastRoundedValue = potentialValue;

  // Use the same updateSelectAndTrigger function as calcPrice1x100 for consistency
  updateSelectAndTrigger(pricematrixSelect, optionToSelect);

  // Synchronize data-price attributes for both input elements (same as calcPrice1x100)
  const price = optionToSelect.dataset.price || "0";
  inputElement1.dataset.price = price;
  inputElement2.dataset.price = price;
}
