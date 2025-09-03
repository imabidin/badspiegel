/**
 * Product Configurator - Dependencies Management Module
 *
 * This module handles conditional dependencies between form elements in the product
 * configurator. It provides functions for managing visibility, auto-selection, and
 * visual feedback based on user selections and business logic dependencies.
 *
 * Features:
 * - Values to Values dependencies (conditional option visibility)
 * - Values to Container dependencies (conditional container visibility)
 * - Auto-selection of valid options when dependencies change
 * - Visual focus effects for user guidance
 * - Performance-optimized event delegation
 * - Debug logging capabilities
 *
 * @version 2.3.0
 * @package Configurator
 *
 * @todo Check if these functions can be used, for a better DRY concept
 */

/**
 * Applies a visual focus effect to a container element
 * Provides user feedback when automatic changes occur due to dependencies
 *
 * @param {HTMLElement} container - The container element to apply focus effect to
 * @param {string} focusClass - CSS class for the specific focus ring (e.g., 'focus-ring-secondary')
 *
 * @example
 * applyFocusEffect(optionContainer, 'focus-ring-primary');
 */
export function applyFocusEffect(container, focusClass) {
  const toggle = container.querySelector("[data-target='offdrop']");

  // Apply focus classes with delay for smooth visual transition
  setTimeout(() => {
    toggle?.classList.add("focus", "focus-ring", focusClass);
    toggle && (toggle.style.borderColor = "var(--bs-secondary)");
  }, 300);

  // Remove focus classes after duration for clean UI
  setTimeout(() => {
    toggle?.classList.remove("focus", "focus-ring", focusClass);
    toggle && (toggle.style.borderColor = "");
  }, 3300);
}

/**
 * Automatically selects the first visible, valid input from a set of inputs
 * Used when dependencies change to maintain a valid selection state
 *
 * @param {HTMLInputElement[]} inputs - Array of target input elements
 * @param {function(HTMLElement): void} [focusCallback] - Optional callback for focus effect on container
 * @param {boolean} [debug=false] - Optional flag to enable debug logging
 *
 * @example
 * const sizeInputs = document.querySelectorAll('input[name="size"]');
 * autoSelect(sizeInputs, (container) => applyFocusEffect(container, 'focus-ring-info'));
 */
export const autoSelect = (inputs, focusCallback, debug = false) => {
  const log = (...args) => debug && console.log("[autoSelect]", ...args);
  const warn = (...args) => debug && console.warn("[autoSelect]", ...args);

  log("autoSelect inputs:", inputs);

  // 1. Filter for visible and non-empty inputs only
  const candidates = inputs.filter(input => {
    const group = input.closest(".btn, .btn-group, .values-group");
    const visible = group && !group.classList.contains("d-none");
    const nonEmpty = input.value.trim() !== "";
    return visible && nonEmpty;
  });
  log("Auto-select candidates:", candidates);

  // 2. Skip if a selection already exists
  if (candidates.some(input => input.checked)) {
    log("Already selected, aborting.");
    return;
  }

  // 3. Select first candidate and trigger change event
  const first = candidates[0];
  if (first) {
    first.checked = true;
    first.dispatchEvent(new Event("change", { bubbles: true }));
    log("Auto-selected:", first.value);

    // Apply focus effect if callback provided
    if (focusCallback) {
      const container = first.closest(".option-group, .values-group");
      if (container) focusCallback(container);
    }
  } else {
    warn("No valid candidates found for auto-select.");
  }
};

/**
 * Dependencies: Values × Values
 *
 * Controls target input visibility and selection based on trigger input values.
 * This function manages conditional relationships between different option groups
 * where the selection in one group determines which options are available in another.
 *
 * @param {string} triggerName - Name attribute of the trigger input group
 * @param {function(string): boolean} condition - Function that evaluates trigger value for dependency
 * @param {string} targetOptionName - Name attribute of the target input group
 * @param {function(string): boolean} targetValueCondition - Filter function for valid target values
 * @param {boolean} [debug=false] - Enable debug logging for troubleshooting
 *
 * @example
 * // Show premium finishes only when premium material is selected
 * dependenciesValuesXvalues(
 *   'material',
 *   (value) => value === 'premium',
 *   'finish',
 *   (value) => ['gold', 'platinum', 'titanium'].includes(value),
 *   false
 * );
 */
export function dependenciesValuesXvalues(
  triggerName,
  condition,
  targetOptionName,
  targetValueCondition,
  debug = false
) {
  const log = (...args) => debug && console.log("[ValuesXvalues]", ...args);
  const warn = (...args) => debug && console.warn("[ValuesXvalues]", ...args);

  // 1. Get all target group inputs for auto-selection functionality
  const allGroupInputs = Array.from(document.querySelectorAll('.values-group input[name="' + targetOptionName + '"]'));

  // 2. Pre-cache all target inputs with their container buttons for performance
  const allTargetEntries = allGroupInputs
    .filter(input => targetValueCondition(input.value))
    .map(input => {
      const btn = input.closest("label.btn");
      if (!btn) {
        warn("No container found for input:", input.value);
      }
      return { input, btn };
    });

  // 3. Validate that target inputs exist before setting up dependencies
  if (allTargetEntries.length === 0) {
    warn("No valid target inputs found for:", targetOptionName);
    return;
  }

  // 4. Change handler for trigger input events
  function handleChange() {
    const value = this.value;
    const match = condition(value);
    log("Trigger value:", value, "-> condition result:", match);

    // Toggle visibility of target options based on condition
    allTargetEntries.forEach(({ input, btn }) => {
      if (!btn) return;
      btn.classList.toggle("d-none", !match);

      // Clear selection if option becomes hidden
      if (!match) input.checked = false;
    });

    // Auto-select first valid option after dependency change with focus effect
    const targetContainer = allGroupInputs[0]?.closest(".option-group, .values-group");
    autoSelect(allGroupInputs, targetContainer ? () => applyFocusEffect(targetContainer, "focus-ring-info") : null);
  }

  // 5. Event delegation: single listener for all trigger inputs (performance optimization)
  document.body.addEventListener("change", event => {
    const target = event.target;
    if (target.name === triggerName) {
      handleChange.call(target, event);
    }
  });

  // 6. Initial run for already checked trigger inputs (page load state)
  // Note: Commented out to prevent conflicts with other initialization logic
  // document
  //     .querySelectorAll(`input[name="${triggerName}"]:checked`)
  //     .forEach(input => handleChange.call(input));
}

/**
 * Dependencies: Values × Container
 *
 * Controls entire container visibility based on trigger input values.
 * This function manages dependencies where selecting certain values shows or hides
 * complete option groups, including automatic value initialization and cleanup.
 *
 * @param {string} triggerName - Name attribute of the trigger input group
 * @param {function(string): boolean} condition - Function to evaluate dependency condition
 * @param {string} targetIdSuffix - Suffix for target container ID (#option_{suffix})
 * @param {boolean} [debug=false] - Enable debug logging for troubleshooting
 *
 * @example
 * // Show custom sizing options only when "custom" size is selected
 * dependenciesValuesXcontainer(
 *   'size_type',
 *   (value) => value === 'custom',
 *   'custom_dimensions',
 *   false
 * );
 */
export function dependenciesValuesXcontainer(triggerName, condition, targetIdSuffix, debug = false) {
  const log = (...args) => debug && console.log("[ValuesXcontainer]", ...args);
  const warn = (...args) => debug && console.warn("[ValuesXcontainer]", ...args);

  log("Trigger name:", triggerName);

  // Locate target container element
  const container = document.getElementById(`option_${targetIdSuffix}`);
  if (!container) {
    warn("Container not found:", `#option_${targetIdSuffix}`);
    return;
  }

  // Main trigger change handler
  const handleChange = function (event) {
    const match = condition(this.value);
    log("Trigger value:", this.value, "-> condition result:", match);
    log("Condition function:", condition);

    if (match) {
      // Show container and initialize default values
      container.classList.remove("d-none");

      // Set default values for empty number and text inputs with focus effect
      ["number", "text"].forEach(type => {
        const input = container.querySelector(`input[type="${type}"]`);
        if (input && input.value.trim() === "") {
          // Use min attribute for numbers, placeholder for text as fallback
          const fallback = input.getAttribute(type === "number" ? "min" : "placeholder") || "";
          input.value = fallback;
          input.classList.replace("no-input", "yes-input");

          // Apply visual focus effect using consistent method
          applyFocusEffect(container, "focus-ring-warning");
        }
      });

      // Auto-select first valid option within the container (only for child option groups)
      if (container.classList.contains("option-group-child")) {
        const allInputs = Array.from(container.querySelectorAll("input"));
        autoSelect(allInputs, () => applyFocusEffect(container, "focus-ring-secondary"));
      }
    } else {
      // Hide container and reset all values
      container.classList.add("d-none");

      // Clear number and text input values
      container.querySelectorAll("input").forEach(input => {
        if (["number", "text"].includes(input.type)) {
          input.value = "";
          input.classList.replace("yes-input", "no-input");
        }
      });

      // Select empty fallback option if available
      const emptyOption = container.querySelector('input[value=""]');
      if (emptyOption) {
        emptyOption.checked = true;
        emptyOption.dispatchEvent(new Event("change", { bubbles: true }));
      }
    }
  };

  // Event delegation for performance optimization
  document.body.addEventListener("change", event => {
    const target = event.target;
    if (target.matches(`input[name="${triggerName}"]`)) {
      handleChange.call(target, event);
    }
  });

  // Initial run for already checked trigger inputs (page load state)
  document.querySelectorAll(`input[name="${triggerName}"]:checked`).forEach(input => handleChange.call(input, null));
}

/**
 * Dependencies: Values Starting Price × Container
 *
 * Sets starting prices ("ab X €") for a trigger option based on the cheapest option
 * in the related container. This shows users the minimum price they can expect
 * when selecting an option that opens up additional choices.
 *
 * @param {string} triggerName - Name attribute of the trigger input group
 * @param {function(string): boolean} condition - Function to evaluate which trigger value gets the starting price
 * @param {string} targetIdSuffix - Suffix for target container ID (#option_{suffix}) to get prices from
 * @param {boolean} [debug=false] - Enable debug logging for troubleshooting
 *
 * @example
 * // Set starting price for "facettenschliff" option based on cheapest facette option
 * dependenciesValueStartingPriceXcontainer(
 *   'kantenpolierung',
 *   (value) => value === 'facettenschliff',
 *   'facette'
 * );
 */
export function dependenciesValueStartingPriceXcontainer(triggerName, condition, targetIdSuffix, debug = false) {
  const log = (...args) => debug && console.log("[ValueStartingPriceXcontainer]", ...args);
  const warn = (...args) => debug && console.warn("[ValueStartingPriceXcontainer]", ...args);

  log("Trigger name:", triggerName, "Target container:", targetIdSuffix);

  // Locate target container element to get prices from
  const targetContainer = document.getElementById(`option_${targetIdSuffix}`);
  if (!targetContainer) {
    warn("Target container not found:", `#option_${targetIdSuffix}`);
    return;
  }

  /**
   * Gets the cheapest price from all options in the target container
   * @returns {string|null} The cheapest price or null if no valid prices found
   */
  const getCheapestPrice = () => {
    const radioInputs = targetContainer.querySelectorAll('input[type="radio"]:not(.option-none)');
    let cheapestPrice = null;

    radioInputs.forEach(radio => {
      const price = parseFloat(radio.dataset.price || radio.getAttribute("data-price"));
      if (!isNaN(price) && price > 0) {
        if (cheapestPrice === null || price < cheapestPrice) {
          cheapestPrice = price;
        }
      }
    });

    log("Cheapest price found:", cheapestPrice);
    return cheapestPrice;
  };

  /**
   * Updates the starting price display for the trigger option
   * @param {HTMLInputElement} triggerInput - The trigger radio input element
   * @param {string|null} price - The price to display
   */
  const updateTriggerStartingPrice = (triggerInput, price) => {
    if (!triggerInput) return;

    const label = document.querySelector(`label.btn[for="${triggerInput.id}"]`);
    const priceContainer = label?.querySelector(".col-auto:last-child");

    if (priceContainer) {
      priceContainer.textContent = price ? `(ab ${price} €)` : "";
      log("Updated trigger price display for", triggerInput.value, ":", price);
    }
  };

  /**
   * Main function to update starting prices
   */
  const updateStartingPrices = () => {
    const cheapestPrice = getCheapestPrice();

    // Find all trigger inputs that match the condition
    const triggerInputs = document.querySelectorAll(`input[name="${triggerName}"]`);

    triggerInputs.forEach(input => {
      if (condition(input.value)) {
        updateTriggerStartingPrice(input, cheapestPrice);
      }
    });
  };

  // Event handler for trigger changes
  const handleTriggerChange = function (event) {
    // Update starting prices whenever trigger changes
    setTimeout(updateStartingPrices, 10);
  };

  // Event handler for target container changes (when prices might change)
  const handleTargetChange = function (event) {
    // Update starting prices when target options change
    updateStartingPrices();
  };

  // Event delegation for trigger inputs
  document.body.addEventListener("change", event => {
    const target = event.target;
    if (target.matches(`input[name="${triggerName}"]`)) {
      handleTriggerChange.call(target, event);
    }
  });

  // Event delegation for target container inputs (in case prices change dynamically)
  document.body.addEventListener("change", event => {
    const target = event.target;
    if (target.matches(`#option_${targetIdSuffix} input[type="radio"]`)) {
      handleTargetChange.call(target, event);
    }
  });

  // Initial run on page load
  setTimeout(() => {
    updateStartingPrices();
  }, 100);

  // Also run when DOM is fully loaded
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", updateStartingPrices);
  } else {
    updateStartingPrices();
  }
}

/**
 * Future Enhancement Ideas:
 *
 * 1. Dependency chain validation:
 *    function validateDependencyChain(dependencies) {
 *      // Check for circular dependencies
 *      // Validate dependency order
 *    }
 *
 * 2. Dynamic dependency registration:
 *    const dependencyManager = {
 *      register(config) { ... },
 *      unregister(id) { ... },
 *      getActive() { ... }
 *    };
 *
 * 3. Animation support for show/hide transitions:
 *    function animateVisibility(element, show, options = {}) {
 *      // CSS transition or animation support
 *    }
 *
 * 4. Dependency state persistence:
 *    function saveDependencyState() {
 *      // Save current state to localStorage/sessionStorage
 *    }
 *
 * 5. Advanced condition operators:
 *    const CONDITIONS = {
 *      equals: (value, target) => value === target,
 *      includes: (value, array) => array.includes(value),
 *      range: (value, min, max) => value >= min && value <= max,
 *      regex: (value, pattern) => new RegExp(pattern).test(value)
 *    };
 */
