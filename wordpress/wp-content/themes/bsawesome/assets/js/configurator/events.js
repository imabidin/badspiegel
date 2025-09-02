/**
 * Product Configurator - Event Management Module
 *
 * This module handles all event listeners and interactions for the product
 * configurator system. It manages form input events, price calculations,
 * summary updates, and initialization of pre-selected options.
 *
 * Features:
 * - Automatic initialization of pre-selected options
 * - Real-time price matrix calculations
 * - Summary updates on input changes
 * - Dynamic event binding for configurator elements
 * - Performance optimizations with caching and debouncing
 *
 * @version 2.2.0
 * @package Configurator
 */

import {
  durchmesserInput,
  breiteInput,
  hoeheInput,
  tiefeInput,
  isSK,
  isSK1,
  isSK2,
} from "./variables";

import { updateSummary } from "./summary";

import {
  calcPrice1x100,
  calcPrice1x50,
  calcPrice1x10,
  calcPrice2x100,
} from "./pricecalcs/pricematrices";

/**
 * Initialize configurator events when DOM is ready
 * Triggers change events for pre-selected radio inputs and performs
 * initial price calculations for all configurator elements
 */
document.addEventListener("DOMContentLoaded", () => {
  /**
   * Find and trigger events for pre-selected radio inputs
   * This ensures that any default selections are properly processed
   * and reflected in the summary and price calculations
   */
  const optionRadios = document.querySelectorAll("input.option-radio:checked");
  if (optionRadios.length > 0) {
    optionRadios.forEach((input) => {
      // Dispatch synthetic change event with bubbling enabled
      // This triggers all associated event handlers and calculations
      input.dispatchEvent(new Event("change", { bubbles: true }));
    });
  }

  /**
   * Trigger initial input events for all option inputs with values
   * This ensures that price calculations are performed on page load
   * for inputs that have default or pre-filled values
   */
  const optionInputs = document.querySelectorAll(".option-input");
  optionInputs.forEach((input) => {
    if (input.value && input.value.trim() !== "") {
      // Dispatch synthetic input event to trigger price calculations
      input.dispatchEvent(new Event("input", { bubbles: true }));
    }
  });

  // Small delay to ensure all calculations are completed before summary update
  setTimeout(updateSummary, 10);
});

/**
 * Global event listeners for configurator form elements
 * These listeners handle real-time updates to the summary display
 * when users interact with configuration options
 */

/**
 * Radio button change event handler
 * Updates summary when radio button selections change
 * Small delay ensures price matrix calculations complete first
 */
document.querySelectorAll(".option-radio").forEach((radioEl) => {
  radioEl.addEventListener("change", () => {
    // 1ms delay allows price matrix calculations to complete
    // before summary update, preventing display inconsistencies
    setTimeout(updateSummary, 1);
  });
});

/**
 * Input field change event handler
 * Updates summary when text/number inputs change
 * Provides real-time feedback during user typing
 */
document.querySelectorAll(".option-input").forEach((inputEl) => {
  inputEl.addEventListener("input", () => {
    // 1ms delay ensures price calculations are completed
    // before summary display is updated
    setTimeout(updateSummary, 1);
  });
});

/**
 * Centralized Price Calculation Event System
 *
 * Handles all price calculations in a consistent manner with automatic initialization.
 * This ensures all price matrices are calculated on page load and updated on user input.
 */
document.addEventListener("DOMContentLoaded", function () {
  // ====================== DIAMETER CALCULATIONS ======================
  const diameterPriceMatrix = document.querySelector(
    '.option-price[name*="pxd"]'
  );
  if (durchmesserInput && diameterPriceMatrix) {
    const diameterCache = {};
    const diameterLastValue = { lastRoundedValue: null };

    // Build cache
    diameterPriceMatrix
      .querySelectorAll("option[data-label]")
      .forEach((option) => {
        const labelValue = parseInt(option.dataset.label, 10);
        if (!isNaN(labelValue)) {
          diameterCache[String(labelValue)] = option;
        }
      });

    // Calculation function
    function calcDiameterPrice() {
      calcPrice1x100(
        durchmesserInput,
        diameterPriceMatrix,
        diameterCache,
        diameterLastValue
      );
    }

    // Event listener
    durchmesserInput.addEventListener("input", calcDiameterPrice);

    // Initial calculation
    calcDiameterPrice();
  }

  // ====================== SK2 MODE CALCULATIONS ======================
  const sk2Input = document.querySelector('.option-input[name="breite_hoehe"]');
  const sk2PriceMatrix = document.querySelector('.option-price[name*="pxd"]');
  if (isSK2 && sk2Input && sk2PriceMatrix) {
    const sk2Cache = {};
    const sk2LastValue = { lastRoundedValue: null };

    // Build cache
    sk2PriceMatrix.querySelectorAll("option[data-label]").forEach((option) => {
      const labelValue = parseInt(option.dataset.label, 10);
      if (!isNaN(labelValue)) {
        sk2Cache[String(labelValue)] = option;
      }
    });

    // Calculation function
    function calcSK2Price() {
      calcPrice1x100(sk2Input, sk2PriceMatrix, sk2Cache, sk2LastValue);
    }

    // Event listener
    sk2Input.addEventListener("input", calcSK2Price);

    // Initial calculation
    calcSK2Price();
  }

  // ====================== DEPTH CALCULATIONS (10-unit steps) ======================
  const depthPriceMatrix = document.querySelector('.option-price[name*="pxt"]');
  if (tiefeInput && depthPriceMatrix) {
    const depthCache = {};
    const depthLastValue = { lastRoundedValue: null };

    // Build cache
    depthPriceMatrix
      .querySelectorAll("option[data-label]")
      .forEach((option) => {
        const labelValue = parseInt(option.dataset.label, 10);
        if (!isNaN(labelValue)) {
          depthCache[String(labelValue)] = option;
        }
      });

    // Calculation function
    function calcDepthPrice() {
      calcPrice1x10(tiefeInput, depthPriceMatrix, depthCache, depthLastValue);
    }

    // Event listener
    tiefeInput.addEventListener("input", calcDepthPrice);

    // Initial calculation - force it even if input is empty by setting default value
    if (!tiefeInput.value || tiefeInput.value.trim() === "") {
      // Set minimum value if no value exists
      const minValue = tiefeInput.getAttribute("min") || "150";
      tiefeInput.value = minValue;
    }

    // Initial calculation
    setTimeout(() => {
      calcDepthPrice();
    }, 50); // Small delay to ensure DOM is fully ready
  }

  // ====================== WIDTH x HEIGHT CALCULATIONS ======================
  const whPriceMatrix = document.querySelector('.option-price[name*="pxbh"]');
  if (breiteInput && hoeheInput && whPriceMatrix) {
    const whCache = {};
    const whLastValue = { lastRoundedValue: null };

    // Build cache
    whPriceMatrix.querySelectorAll("option").forEach((option) => {
      if (option.value.includes("x")) {
        whCache[option.value] = option;
      }
    });

    // Calculation function
    function calcWidthHeightPrice() {
      calcPrice2x100(
        breiteInput,
        hoeheInput,
        whPriceMatrix,
        whCache,
        whLastValue
      );
    }

    // Event listeners
    breiteInput.addEventListener("input", calcWidthHeightPrice);
    hoeheInput.addEventListener("input", calcWidthHeightPrice);

    // Initial calculation
    calcWidthHeightPrice();
  }

  // ====================== KLAPPELEMENTE CALCULATIONS (50-unit steps) ======================
  const klappelementerBreiteInput = document.querySelector(
    '.option-input[name="klappelemente_breite"]'
  );
  const klappelementePriceMatrix = document.querySelector(
    '.option-price[name*="klappelemente_breite_aufpreis"]'
  );
  if (klappelementerBreiteInput && klappelementePriceMatrix) {
    const klappelementerCache = {};
    const klappelementerLastValue = { lastRoundedValue: null };

    // Build cache
    klappelementePriceMatrix
      .querySelectorAll("option[data-label]")
      .forEach((option) => {
        const labelValue = parseInt(option.dataset.label, 10);
        if (!isNaN(labelValue)) {
          klappelementerCache[String(labelValue)] = option;
        }
      });

    // Calculation function
    function calcKlappelementerPrice() {
      calcPrice1x50(
        klappelementerBreiteInput,
        klappelementePriceMatrix,
        klappelementerCache,
        klappelementerLastValue
      );
    }

    // Event listener
    klappelementerBreiteInput.addEventListener(
      "input",
      calcKlappelementerPrice
    );

    // Initial calculation - force it even if input is empty by setting default value
    if (
      !klappelementerBreiteInput.value ||
      klappelementerBreiteInput.value.trim() === ""
    ) {
      // Set minimum value if no value exists
      const minValue = klappelementerBreiteInput.getAttribute("min") || "150";
      klappelementerBreiteInput.value = minValue;
    }

    // Initial calculation
    setTimeout(() => {
      calcKlappelementerPrice();
    }, 50); // Small delay to ensure DOM is fully ready
  }

  // ====================== ADDITIONAL INPUT FIELDS ======================
  // Handle additional inputs that should trigger diameter recalculation
  const additionalInputs = [
    document.querySelector('.option-input[name="hoehe_schnittkante"]'),
    document.querySelector('.option-input[name="schnittkante"]'),
  ];

  additionalInputs.forEach((input) => {
    if (input && durchmesserInput && diameterPriceMatrix) {
      input.addEventListener("input", () => {
        // Trigger diameter recalculation when additional inputs change
        durchmesserInput.dispatchEvent(new Event("input", { bubbles: true }));
      });
    }
  });
});

/**
 * Future Enhancement Ideas:
 *
 * 1. Event delegation for dynamically added elements:
 *    document.addEventListener('change', (e) => {
 *      if (e.target.matches('.option-radio')) {
 *        // Handle radio changes
 *      }
 *    });
 *
 * 2. Custom events for better component communication:
 *    document.dispatchEvent(new CustomEvent('configurator:priceUpdated', {
 *      detail: { newPrice: calculatedPrice }
 *    });
 *
 * 3. Performance monitoring:
 *    const performanceObserver = new PerformanceObserver((list) => {
 *      // Monitor event handler performance
 *    });
 *
 * 4. Error boundary for event handlers:
 *    function safeEventHandler(handler) {
 *      return function(...args) {
 *        try {
 *          return handler.apply(this, args);
 *        } catch (error) {
 *          console.error('Event handler error:', error);
 *        }
 *      };
 *    }
 */
