/**
 * Enhanced WooCommerce quantity input controls with UX optimizations
 *
 * Features:
 * - Real-time validation with visual feedback
 * - Smart empty field handling (no auto-fill)
 * - Seamless cart integration
 * - Plus/minus button controls
 * - Min/max/step constraint enforcement
 * - Respects HTML5 input constraints (min="0" support)
 *
 * @version 2.5.0
 *
 * @todo Add a modal confirmation if user tries to set quantity to zero "0"
 */

document.addEventListener("DOMContentLoaded", function () {
  /**
   * Utility functions - centralized to avoid code duplication
   * @namespace Utils
   */

  /**
   * Extracts and parses input attributes with proper defaults
   * @param {HTMLInputElement} input - The input element
   * @returns {Object} Parsed constraints object
   */
  const getInputConstraints = (input) => {
    const min = parseFloat(input.getAttribute("min"));
    const max = parseFloat(input.getAttribute("max"));
    const step = parseFloat(input.getAttribute("step"));

    return {
      min: isNaN(min) ? 0 : min, // Respect actual min="0" from HTML
      max: isNaN(max) ? Infinity : max,
      step: isNaN(step) ? 1 : step,
    };
  };

  /**
   * Gets current input value with intelligent fallback chain
   * @param {HTMLInputElement} input - The input element
   * @returns {number} Current or fallback value
   */
  const getCurrentValue = (input) => {
    const current = parseFloat(input.value);
    if (!isNaN(current)) return current;

    const prev = parseFloat(input.dataset.prevValidValue);
    if (!isNaN(prev)) return prev;

    const original = parseFloat(input.dataset.originalValue);
    if (!isNaN(original)) return original;

    return getInputConstraints(input).min;
  };

  /**
   * Validates and normalizes quantity value
   * @param {HTMLInputElement} input - The input element
   * @param {boolean} allowEmpty - Whether empty values are acceptable
   * @returns {number|null} Normalized value or null if empty and allowed
   */
  const normalizeQuantity = (input, allowEmpty = false) => {
    if (!input?.getAttribute) return null;

    const isEmpty = !input.value || input.value.trim() === "";
    if (isEmpty && allowEmpty) return null;

    const { min, max, step } = getInputConstraints(input);
    const value = isEmpty ? min : parseFloat(input.value);

    if (isNaN(value)) return min;

    // Clamp to boundaries and round to step
    const clamped = Math.max(min, Math.min(max, value));
    return Math.round(clamped / step) * step;
  };

  /**
   * Centralized input state management - handles UI + events
   * @param {HTMLInputElement} input - Target input
   * @param {boolean} isValid - Validation state
   * @param {boolean} triggerChange - Whether to trigger change event
   */
  const updateInputState = (input, isValid, triggerChange = false) => {
    // Update validation UI with better accessibility
    input.classList.toggle("is-invalid", !isValid);
    input.classList.toggle("border-danger", !isValid);
    input.setAttribute("aria-invalid", !isValid);

    if (triggerChange) {
      input.dispatchEvent(new Event("change", { bubbles: true }));

      // Enhanced cart update with error handling
      const form = input.closest("form.woocommerce-cart-form");
      const updateBtn = form?.querySelector('button[name="update_cart"]');
      if (updateBtn && !updateBtn.disabled) {
        updateBtn.disabled = false;
        // Debounced update to prevent rapid-fire submissions
        clearTimeout(updateBtn.updateTimeout);
        updateBtn.updateTimeout = setTimeout(() => {
          try {
            updateBtn.click();
          } catch (error) {
            console.warn("Cart update failed:", error);
          }
        }, 150); // Slightly longer delay for better UX
      }
    }
  };

  /**
   * Checks if input is empty and handles UX accordingly
   * @param {HTMLInputElement} input - The input element
   * @returns {boolean} True if handled as empty, false if processing should continue
   */
  const handleEmptyInput = (input) => {
    const isEmpty = input.value.trim() === "";
    if (isEmpty) {
      updateInputState(input, false, false); // Mark invalid but don't trigger updates
      return true;
    }
    return false;
  };

  /**
   * Event management - simplified and DRY
   * @namespace EventHandlers
   */

  /**
   * Focus management - stores values for comparison
   */
  document.body.addEventListener("focusin", function (event) {
    if (!event.target.matches(".quantity .qty")) return;

    const input = event.target;
    input.dataset.originalValue = input.value;
    input.dataset.prevValidValue = input.value;
    updateInputState(input, true, false); // Reset validation state
  });

  /**
   * Validation on focus out with UX-optimized empty handling
   */
  document.body.addEventListener("focusout", function (event) {
    if (!event.target.matches(".quantity .qty")) return;

    const input = event.target;

    // Handle empty inputs with UX optimization
    if (handleEmptyInput(input)) return;

    const newValue = normalizeQuantity(input);
    const isValid = !isNaN(newValue) && newValue === parseFloat(input.value);
    const hasChanged = newValue !== parseFloat(input.dataset.prevValidValue);

    updateInputState(input, isValid, false);

    if (hasChanged && isValid) {
      input.value = newValue;
      input.dataset.prevValidValue = newValue;
      updateInputState(input, true, true); // Trigger change event
    }
  });

  /**
   * Plus/minus button controls
   */
  document.body.addEventListener("click", function (event) {
    if (!event.target.matches("button.plus, button.minus")) return;

    const button = event.target;
    const input = button.closest(".quantity")?.querySelector(".qty");
    if (!input) return;

    const { min, step } = getInputConstraints(input);
    const currentValue = getCurrentValue(input);

    const newValue = button.classList.contains("plus")
      ? currentValue + step
      : Math.max(min, currentValue - step);

    // Create mock input for normalization
    const mockInput = {
      value: newValue.toString(),
      getAttribute: (attr) => input.getAttribute(attr),
    };

    input.value = normalizeQuantity(mockInput);
    input.dataset.prevValidValue = input.value;
    updateInputState(input, true, true);
  });

  /**
   * Real-time input filtering (integers only for whole products)
   * Note: Strict integer validation since we only sell whole items
   */
  document.body.addEventListener("input", function (event) {
    if (!event.target.matches(".quantity .qty")) return;

    const input = event.target;

    // Remove non-numeric characters (integers only)
    const cleanValue = input.value.replace(/[^0-9]/g, "");
    if (input.value !== cleanValue) {
      input.value = cleanValue;
    }

    // Immediate validation feedback for integers
    const isValid =
      input.value.trim() !== "" && !isNaN(parseInt(input.value, 10));
    updateInputState(input, isValid, false);
  });
});
