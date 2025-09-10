# JavaScript Documentation Guide for BadSpiegel Theme

**INSTRUCTION FOR LLMs: This is a comprehensive documentation standard guide. When documenting JavaScript files in this project, follow these exact patterns and templates. Always document in ENGLISH only.**

## LLM Instructions Summary

**CRITICAL RULES:**
1. **ALWAYS document in English** - never German
2. **Follow the exact templates** provided below
3. **Use appropriate template** based on file type (module/class/utility/namespace)
4. **Include all required sections** as shown in examples
5. **Preserve important inline comments** that explain business logic
6. **Remove redundant/obvious comments**
7. **Follow JSDoc standards** consistently

---

## 1. File Header Documentation

### Complete File Header Template
Every JavaScript file should have a comprehensive header following this exact structure:

```javascript
/**
 * [Product/System Name] - [Module Purpose] Module
 *
 * [Brief description of what this module does - 2-3 lines maximum]
 * [Secondary description line if needed for complex modules]
 *
 * Features:
 * - [Key feature 1]
 * - [Key feature 2]
 * - [Key feature 3]
 * - [Key feature 4] (list 4-8 main features)
 * - [Integration features if applicable]
 * - [Debug/development features]
 *
 * @version 2.5.0
 * @package [Package/System Name]
 *
 * @todo [Optional - specific technical debt or improvements needed]
 * @todo [Optional - another item if needed]
 */
```

### Examples from Existing Codebase

**Module File Header (load.js style):**
```javascript
/**
 * Product Configurator - Configuration Loading Module
 *
 * This module handles loading saved product configurations from codes.
 * It provides functionality for validating, applying, and displaying
 * saved configurations with modal dialogs and error handling.
 *
 * Features:
 * - Configuration code validation and loading
 * - Product mismatch detection and handling
 * - Modal-based and inline form interfaces
 * - Visual feedback and error states
 * - Auto-redirect functionality for code URLs
 * - Comprehensive debug logging system
 *
 * @version 2.5.0
 * @package Configurator
 */
```

**Class-Based File Header (carousel.js style):**
```javascript
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
 * @version 2.5.0
 * @package Configurator
 *
 * @todo Check if the carousel functions need to be refactored for better reusability
 * @todo Check if it is possible just to scroll to add to cart button after pressing "Fertig"
 */
```

---

## 2. Configuration Constants Documentation

### Configuration Section Header
For files with configuration constants, use clear section separators:

```javascript
// ====================== CONFIGURATION CONSTANTS ======================

/**
 * [System] behavior configuration
 * - 'option1': Description of what this option does
 * - 'option2': Description of what this option does
 * - 'option3': Description of what this option does
 */
const CONFIG_MODE = "default-value";

/**
 * [Feature] configuration
 */
const FEATURE_DURATION = 1.5; // Seconds (0.4 = fast, 0.8 = normal, 1.2 = slow)

/**
 * [System] configuration
 */
const SCROLL_AMOUNT = 0.8; // Fraction of container width to scroll
const VISIBLE_CLASS = "buttons-visible"; // CSS class for button visibility
const TIMEOUT = 1500; // Auto-hide timeout for buttons (ms)
const TOLERANCE = 2; // Pixel tolerance for boundary detection
```

### Configuration Object Pattern (spinner.js style)
For comprehensive configuration objects:

```javascript
// Configuration constants
const CONFIG = {
  TIMEOUT_DURATION: 10000, // 10 seconds timeout (consistent with comment)
  NAVIGATION_DELAY: 1000, // Delay before navigation (ms)
  LOADING_TEXT: "Loading…", // Loading text with localization
  DEBUG: false, // Enable debug logging
};
```

---

## 3. Namespace Documentation

### Namespace Pattern (Utilities)
For utility objects and namespace-like structures:

```javascript
/**
 * [Feature Name] Utilities
 *
 * [Brief description of what this namespace handles - 2 lines maximum]
 * [Secondary description if needed]
 *
 * @namespace [NamespaceName]
 */
const UtilityName = {
  /**
   * [Brief description of what this method does]
   * [Secondary description with technical details if needed]
   *
   * @param {type} paramName - Description of parameter
   * @param {type} [optionalParam=defaultValue] - Description of optional parameter
   * @returns {type} Description of return value
   *
   * @example
   * UtilityName.methodName(value)  // Returns "formatted result"
   * UtilityName.methodName(0)     // Returns ""
   */
  methodName(param, optionalParam = false) {
    // Implementation
  },
};
```

### Examples from Existing Codebase

**Price Utilities (load.js style):**
```javascript
/**
 * Price Formatting Utilities
 *
 * Handles German locale price formatting with currency symbols
 * and positive price indicators for configuration displays.
 *
 * @namespace PriceUtils
 */
const PriceUtils = {
  /**
   * Formats a price value as localized German currency string
   * Returns formatted string with '+' prefix for positive values
   *
   * @param {number} price - Price value to format
   * @param {boolean} [debug=false] - Enable debug logging
   * @returns {string} Formatted price string with + prefix if positive, empty if zero
   *
   * @example
   * PriceUtils.formatPrice(12.5)  // Returns " (+12,50 €)"
   * PriceUtils.formatPrice(0)     // Returns ""
   */
  formatPrice(price, debug = false) {
    // Implementation
  },
};
```

**Data Processing Utilities:**
```javascript
/**
 * Data Processing Utilities
 *
 * Handles configuration data processing, validation, and form manipulation.
 * Provides safe parsing and application of configuration values to form elements.
 *
 * @namespace DataUtils
 */
const DataUtils = {
  /**
   * Normalizes product ID to integer with validation
   * Safely converts string or number IDs to integers
   *
   * @param {string|number} id - Product ID to normalize
   * @param {boolean} [debug=false] - Enable debug logging
   * @returns {number|null} Parsed integer or null if invalid
   *
   * @example
   * DataUtils.getProductId("123")   // Returns 123
   * DataUtils.getProductId("abc")   // Returns null
   */
  getProductId(id, debug = false) {
    // Implementation
  },
};
```

---

## 4. Class Documentation

### Class Declaration with Constructor
For ES6 classes, use comprehensive documentation:

```javascript
/**
 * [ClassName] Class
 *
 * [Brief description of what this class orchestrates/manages]
 * [Secondary description with key responsibilities]
 */
class ClassName {
  /**
   * Constructor - Initialize all [system] components
   * Sets up DOM references, validates required elements, and initializes subsystems
   */
  constructor() {
    // Core elements
    this.element = document.querySelector(".selector");
    this.container = this.element?.closest("container");

    // Configuration state management
    this.currentState = 1;
    this.isComplete = false;
    this.hasEverCompleted = false;

    // Validate critical elements exist before initialization
    if (!this.element) {
      console.error("ClassName: Missing required elements");
      return;
    }

    // Initialize all subsystems
    this.initSystem();
    this.initEvents();
    this.initState();
  }
```

### Method Section Headers
Use clear section separators for method groups:

```javascript
  /**
   * [SYSTEM NAME] INITIALIZATION
   * ============================
   */

  /**
   * Initialize [system component] with [specific functionality]
   * Sets up [specific behavior] for [specific purpose]
   */
  initSystem() {
    // Implementation
  }

  /**
   * [SYSTEM NAME] EVENT HANDLING
   * ============================
   */

  /**
   * Handle [specific event] events
   * Manages [specific behavior] and [specific outcomes]
   *
   * @param {Event} event - [Event type] event object
   */
  handleEvent(event) {
    // Implementation
  }
```

---

## 5. Function Documentation

### Standard Function Documentation
For standalone functions and methods:

```javascript
/**
 * [Brief description of what the function does]
 * [Extended description with technical details if complex]
 *
 * @param {type} paramName - Description of parameter
 * @param {type|type} [optionalParam=defaultValue] - Description with type alternatives
 * @param {boolean} [debug=false] - Enable debug logging
 * @returns {type} Description of return value with possible states
 *
 * @example
 * functionName(param1, param2); // Brief example
 * // More detailed example if needed:
 * const result = functionName("value", true);
 */
function functionName(paramName, optionalParam = defaultValue, debug = false) {
  // Implementation
}
```

### Debug Logging Functions
For utility functions that support debugging:

```javascript
/**
 * Debug logging utility with timestamp formatting
 * Provides consistent logging format across the module
 *
 * @param {boolean} debug - Whether debug mode is enabled
 * @param {...any} args - Arguments to log
 */
function log(debug, ...args) {
  if (debug) {
    const timestamp = new Date().toISOString().substring(11, 23);
    console.log(`[ModuleName][${timestamp}]`, ...args);
  }
}
```

### Debug Helper Pattern (spinner.js style)
For simple debug helpers with module prefixes:

```javascript
// Debug helper
const debug = (...args) => {
  if (CONFIG.DEBUG) {
    console.log("[MODULE-NAME]", ...args);
  }
};
```

### Global State Management
For modules that need state tracking:

```javascript
// Global state management
let isInitialized = false;
let timeoutIds = new Set(); // Track all timeouts for cleanup
```

### Event Handler Functions
For event handling functions:

```javascript
/**
 * Handle [specific action] click events
 * Validates [conditions] before allowing [action]
 *
 * @param {Event} event - Click event object
 */
handleActionClick(event) {
  // Implementation
}
```

### Event Delegation Pattern (spinner.js style)
For modern event delegation with early returns:

```javascript
/**
 * Enhanced [action] click handler with error handling
 *
 * 1. Click → [Step 1 description]
 * 2. [Step 2] → [Step 2 description]
 * 3. [Step 3] → [Step 3 description]
 */
function handleActionClick(event) {
  // Early return if not target element
  if (!event.target.matches("selector")) return;

  const element = event.target;
  // Processing logic
}
```

### Memory Management Pattern
For cleanup and memory leak prevention:

```javascript
/**
 * Cleanup timeouts on page unload to prevent memory leaks
 */
window.addEventListener("beforeunload", function () {
  debug("Page unloading, cleaning up timeouts");
  timeoutIds.forEach(id => clearTimeout(id));
  timeoutIds.clear();
});
```

---

## 6. Advanced Documentation Patterns

### Promise-Based Functions
For functions returning Promises:

```javascript
/**
 * Generate configuration code via AJAX
 *
 * @param {Object} configData - Configuration data object
 * @returns {Promise} Promise that resolves with {code, directLink} or rejects with error
 */
function generateConfigCode(configData) {
  return new Promise((resolve, reject) => {
    // Implementation
  });
}
```

### Modal and UI Functions
For UI-related functions:

```javascript
/**
 * Shows success modal with configuration summary
 * Displays successful configuration loading with optional summary accordion
 *
 * @param {Object} configData - Applied configuration data
 * @param {string} successMsg - Success message to display
 * @param {boolean} [forcedLoad=false] - Whether this was a forced load despite mismatch
 * @param {boolean} [showSummaryAccordion=false] - Whether to show expandable summary
 * @param {boolean} [debug=false] - Enable debug logging
 *
 * @example
 * ModalSystem.showSuccessModal(
 *   configData,
 *   "Configuration loaded successfully!",
 *   false,
 *   true
 * );
 */
showSuccessModal(configData, successMsg, forcedLoad = false, showSummaryAccordion = false, debug = false) {
  // Implementation
}
```

### Loading State Functions
For UI state management:

```javascript
/**
 * Apply spinner loading state (for main save button)
 *
 * @param {jQuery} $button - The button element
 * @returns {Function} Cleanup function to reset button state
 */
function applySpinnerLoading($button) {
  // Implementation
  return function cleanup() {
    // Cleanup logic
  };
}
```

### UI State Management Pattern (spinner.js style)
For comprehensive UI state management:

```javascript
/**
 * Set button to loading state
 */
function setLoadingState(button, originalContent) {
  button.setAttribute("data-original-content", originalContent);

  const spinnerHTML = `
    <span role="status" aria-atomic="true">
      <span class="spinner-border me-2"
            style="--bs-spinner-width:1.25rem;
                   --bs-spinner-height:1.25rem;
                   --bs-spinner-border-width:0.225rem"
            aria-hidden="true">
      </span>
      <span>${CONFIG.LOADING_TEXT}</span>
    </span>
  `;

  button.innerHTML = spinnerHTML;
  button.setAttribute("aria-busy", "true");
  button.setAttribute("data-loading", "true");
  button.setAttribute("tabindex", "-1");
}

/**
 * Reset button to original state
 */
function resetButton(button, originalContent) {
  button.innerHTML = originalContent;
  button.setAttribute("aria-busy", "false");
  button.removeAttribute("data-loading");
  button.removeAttribute("data-original-content");
  button.removeAttribute("tabindex");
}
```

### Input Validation Pattern (quantity.js style)
For form input validation and normalization:

```javascript
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
```

---

## 7. Inline Comments Within Functions

### What to KEEP:

**✅ Important inline comments that explain:**
- **Complex Logic**: Why certain decisions were made
- **Business Logic**: Specific rules or calculations
- **Browser Compatibility**: Workarounds for specific browsers
- **Performance Optimizations**: Cache strategies, DOM optimizations
- **Integration Points**: Third-party library interactions
- **State Management**: Complex state transitions
- **Timing and Async**: Race condition prevention
- **Memory Management**: Cleanup and leak prevention
- **UX Patterns**: User experience optimizations

```javascript
function complexProcessing() {
  // Prevent multiple simultaneous operations to avoid race conditions
  if (this.isProcessing) return;

  // Use requestAnimationFrame for smooth visual transitions
  requestAnimationFrame(() => {
    // Implementation
  });

  // Cache DOM queries for performance in loops
  const elements = document.querySelectorAll('.selector');

  // Business rule: Only process premium configurations differently
  if (config.isPremium) {
    // Implementation
  }

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
```### What to REMOVE:

**❌ Redundant comments that can be removed:**
- Obvious variable assignments
- Standard DOM manipulations without special logic
- Repetition of function name in comment
- Comments that only restate the code

```javascript
// ❌ REMOVE - obvious
const button = document.getElementById('button'); // Get button element

// ❌ REMOVE - restates code
element.style.display = 'none'; // Hide the element

// ❌ REMOVE - function name repetition
function saveData() {
  // Save data function implementation
}
```

### Translation Rule:
**German Comments → English Comments:**
```javascript
// ❌ GERMAN (convert)
// Prüfe, ob alle Schritte abgeschlossen sind

// ✅ ENGLISH (new version)
// Check if all steps are completed
```

---

## 8. DOM Ready and Initialization

### Module Initialization Pattern
For modules that initialize on DOM ready:

```javascript
/**
 * Module Initialization
 *
 * Initialize all [system] functionality when DOM is ready with configurable debug mode.
 */
$(document).ready(() => {
  const systemInstance = new SystemClass();

  // Make essential functions globally available if needed
  window.SystemAPI = {
    method1: systemInstance.method1.bind(systemInstance),
    method2: systemInstance.method2.bind(systemInstance)
  };
});
```

### DOMContentLoaded Pattern
For vanilla JavaScript modules:

```javascript
document.addEventListener("DOMContentLoaded", function () {
  // Module initialization code

  // Export functions for use in other modules
  window.ModuleName = {
    publicMethod1,
    publicMethod2,
    getState: () => internalState
  };
});
```

### Multiple Event Listener Pattern (spinner.js style)
For modules that need multiple initialization points:

```javascript
/**
 * Initialize checkout spinner functionality on DOM ready
 */
document.addEventListener("DOMContentLoaded", function () {
  debug("DOM loaded, initializing checkout spinner");
  initCheckoutSpinner();
});

/**
 * WooCommerce AJAX event integration (consolidated)
 */
document.addEventListener("DOMContentLoaded", function () {
  const wooEvents = ["updated_wc_div", "updated_cart_totals", "wc_fragments_refreshed"];

  wooEvents.forEach(eventName => {
    document.body.addEventListener(eventName, function () {
      debug(`WooCommerce ${eventName} detected, re-initializing`);
      initCheckoutSpinner();
    });
  });
});
```

### Initialization with Duplicate Prevention
For modules that need re-initialization:

```javascript
/**
 * Initialize checkout spinner functionality with event delegation
 */
function initCheckoutSpinner() {
  debug("Initializing checkout spinner");

  // Prevent duplicate initialization
  if (isInitialized) {
    debug("Already initialized, skipping");
    return;
  }

  // Use event delegation to handle dynamically added buttons
  document.addEventListener("click", handleCheckoutClick, true);
  isInitialized = true;
}
```---

## 9. Error Handling and Validation

### Error Handling Pattern
For robust error handling:

```javascript
/**
 * Process configuration with comprehensive error handling
 *
 * @param {Object} config - Configuration object to process
 * @returns {Object|null} Processed result or null if failed
 */
function processConfig(config) {
  try {
    // Validate input
    if (!config || typeof config !== 'object') {
      console.error('Invalid configuration provided');
      return null;
    }

    // Process with error boundaries
    const result = complexProcessing(config);
    return result;

  } catch (error) {
    console.error('Configuration processing failed:', error);
    return null;
  }
}
```

### Validation Functions
For input validation:

```javascript
/**
 * Validate configuration data structure
 *
 * @param {Object} data - Data object to validate
 * @returns {Object} Validation result with success status and errors
 */
function validateConfig(data) {
  const errors = [];

  // Validation logic
  if (!data.required_field) {
    errors.push('Required field missing');
  }

  return {
    isValid: errors.length === 0,
    errors: errors
  };
}
```

### Input Constraint Validation (quantity.js style)
For HTML5 input constraint validation:

```javascript
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
```---

## 10. Global Exports and API

### Window Object Exports
For making functions globally available:

```javascript
// Export functions for use in other modules
window.ModuleName = {
  // Core functionality
  primaryFunction,
  secondaryFunction,

  // State management
  getState: () => moduleState,
  setState: (newState) => { moduleState = newState; },

  // Utility methods
  utils: {
    helper1,
    helper2
  }
};
```

### Global Function Pattern
For functions that need global access:

```javascript
// Make [functionality] globally available for [use case]
window.globalFunctionName = function(params) {
  // Implementation
};
```

---

## 11. Future Enhancement Documentation

### Future Enhancement Section
Every file should end with a comprehensive future enhancements section:

```javascript
/**
 * Future Enhancement Ideas:
 *
 * 1. [Category] improvements:
 *    - [Specific improvement 1]
 *    - [Specific improvement 2]
 *    - [Specific improvement 3]
 *
 * 2. [Category] enhancements:
 *    - [Specific enhancement 1]
 *    - [Specific enhancement 2]
 *
 * 3. Performance optimizations:
 *    - [Performance improvement 1]
 *    - [Performance improvement 2]
 *
 * 4. Security improvements (CRITICAL):
 *    - Escape user input in template literals to prevent XSS
 *    - Sanitize HTML before using .html() method
 *    - Validate all form inputs before processing
 *    - Add Content Security Policy headers
 *
 * 5. Modern API adoption (CRITICAL):
 *    - Replace deprecated APIs with modern alternatives
 *    - Add fallback for browsers without modern API support
 *    - Implement proper async/await error handling
 *
 * 6. Dependency management:
 *    - Add existence checks for external libraries
 *    - Implement graceful degradation for missing dependencies
 *    - Add feature detection for required browser APIs
 *
 * 7. Error handling robustness:
 *    - Add comprehensive error logging
 *    - Implement automatic retry mechanisms
 *    - Show user-friendly error messages
 *
 * 8. Code maintainability:
 *    - Break down large functions into smaller units
 *    - Add comprehensive unit tests
 *    - Implement JSDoc type definitions
 *
 * 9. Accessibility improvements:
 *    - Add ARIA labels to dynamic elements
 *    - Implement keyboard navigation
 *    - Add screen reader support
 *
 * 10. Memory management:
 *     - Clean up event listeners properly
 *     - Remove DOM elements after use
 *     - Implement proper garbage collection
 */
```

---

## 14. Advanced JavaScript Patterns (From Cart Module Analysis)

### UX-Optimized File Headers (quantity.js/spinner.js style)
For user-facing functionality modules:

```javascript
/**
 * Enhanced [Component] with UX optimizations
 *
 * Features:
 * - Real-time validation with visual feedback
 * - Smart empty field handling (no auto-fill)
 * - Seamless [system] integration
 * - [Control type] controls
 * - [Constraint] enforcement
 * - Respects HTML5 input constraints
 *
 * @version 2.5.0
 *
 * @todo Add a modal confirmation if user tries to [critical action]
 */
```

### Centralized Utility Functions Pattern
For avoiding code duplication:

```javascript
/**
 * Utility functions - centralized to avoid code duplication
 * @namespace Utils
 */

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
```

### Comprehensive UI State Management
For complex form interactions:

```javascript
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

    // Enhanced integration with error handling
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
          console.warn("Update failed:", error);
        }
      }, 150); // Slightly longer delay for better UX
    }
  }
};
```

### HTML5 Input Processing Pattern
For form inputs with proper constraint handling:

```javascript
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
```

### WooCommerce Integration Pattern
For WooCommerce-specific event handling:

```javascript
/**
 * WooCommerce AJAX event integration (consolidated)
 */
document.addEventListener("DOMContentLoaded", function () {
  const wooEvents = ["updated_wc_div", "updated_cart_totals", "wc_fragments_refreshed"];

  wooEvents.forEach(eventName => {
    document.body.addEventListener(eventName, function () {
      debug(`WooCommerce ${eventName} detected, re-initializing`);
      initializeModule();
    });
  });
});
```

### Button State Management with Accessibility
For loading states and accessibility:

```javascript
/**
 * Set button to loading state
 */
function setLoadingState(button, originalContent) {
  button.setAttribute("data-original-content", originalContent);

  const spinnerHTML = `
    <span role="status" aria-atomic="true">
      <span class="spinner-border me-2"
            style="--bs-spinner-width:1.25rem;
                   --bs-spinner-height:1.25rem;
                   --bs-spinner-border-width:0.225rem"
            aria-hidden="true">
      </span>
      <span>${CONFIG.LOADING_TEXT}</span>
    </span>
  `;

  button.innerHTML = spinnerHTML;
  button.setAttribute("aria-busy", "true");
  button.setAttribute("data-loading", "true");
  button.setAttribute("tabindex", "-1");
}
```

---

## 15. Best Practices Checklist (Extended)

### For Every JavaScript File:
- [ ] **Complete file header** with features list and version
- [ ] **Appropriate section separators** for different functional areas
- [ ] **JSDoc comments** for all public functions and methods
- [ ] **Parameter types** clearly documented with alternatives where applicable
- [ ] **Return types** documented with possible states
- [ ] **@example blocks** for complex or public APIs
- [ ] **Error handling** documented and implemented
- [ ] **Debug logging** support where appropriate
- [ ] **Global exports** documented if functions are made globally available
- [ ] **Future enhancements** section with categorized improvements

### For Classes:
- [ ] **Class description** explaining its main orchestration role
- [ ] **Constructor documentation** explaining initialization process
- [ ] **Method grouping** with clear section headers
- [ ] **Property documentation** for complex state management
- [ ] **Event handling** clearly separated and documented

### For Utility Modules:
- [ ] **Namespace documentation** explaining the utility's purpose
- [ ] **Consistent parameter patterns** (debug parameter where applicable)
- [ ] **Example usage** for public methods
- [ ] **Input validation** and error handling

### For Integration Modules:
- [ ] **Third-party dependencies** clearly documented
- [ ] **Integration points** explained
- [ ] **Fallback strategies** for missing dependencies
- [ ] **Browser compatibility** considerations

### For UX-Focused Modules (NEW - from cart analysis):
- [ ] **UX optimization features** documented in header
- [ ] **Accessibility considerations** (ARIA labels, screen readers)
- [ ] **Real-time validation** patterns implemented
- [ ] **Empty state handling** with user-friendly feedback
- [ ] **Debounced actions** to prevent rapid-fire events
- [ ] **Loading states** with proper accessibility attributes
- [ ] **Memory leak prevention** with cleanup on page unload
- [ ] **Error boundaries** with graceful degradation

### For Form Input Modules (NEW):
- [ ] **HTML5 constraint validation** properly implemented
- [ ] **Input filtering** for data type enforcement
- [ ] **Fallback value chains** for robust defaults
- [ ] **Visual feedback** for validation states
- [ ] **Event delegation** for dynamic content
- [ ] **WooCommerce integration** events handled

### For Button/Action Modules (NEW):
- [ ] **Multiple click prevention** during processing
- [ ] **Timeout management** with cleanup tracking
- [ ] **Loading state management** with proper reset
- [ ] **Error recovery** mechanisms
- [ ] **Accessibility compliance** (aria-busy, role, etc.)
- [ ] **Navigation handling** with error boundaries

---

## 16. Common Patterns and Templates (Extended)

### AJAX Request Pattern
```javascript
/**
 * [Action description] via AJAX
 *
 * @param {Object} requestData - Data to send to server
 * @returns {Promise} Promise that resolves with server response or rejects with error
 */
function makeAjaxRequest(requestData) {
  return new Promise((resolve, reject) => {
    $.ajax({
      url: ajaxData.ajax_url,
      method: 'POST',
      data: {
        action: 'action_name',
        nonce: ajaxData.nonce,
        ...requestData
      },
      success: function(response) {
        if (response.success) {
          resolve(response.data);
        } else {
          reject(new Error(response.data || 'Server request failed'));
        }
      },
      error: function(xhr, status, error) {
        reject(new Error(`Network error: ${error}`));
      }
    });
  });
}
```

### Modal Creation Pattern
```javascript
/**
 * Create and show modal with specified configuration
 *
 * @param {Object} options - Modal configuration options
 * @param {string} options.title - Modal title
 * @param {string} options.body - Modal body HTML
 * @param {Array<Object>} [options.footer] - Footer button configuration
 * @param {string} [options.size="md"] - Modal size (sm/md/lg/xl)
 * @returns {Object} Created modal instance
 */
function createModal(options) {
  // Implementation
}
```

### State Management Pattern
```javascript
/**
 * [System] State Management
 *
 * Handles [system] state tracking and persistence
 */
const StateManager = {
  state: {
    current: null,
    previous: null,
    isModified: false
  },

  /**
   * Update state with validation and change detection
   *
   * @param {Object} newState - New state object
   * @param {boolean} [silent=false] - Whether to suppress change events
   */
  setState(newState, silent = false) {
    // Implementation
  },

  /**
   * Get current state with optional deep cloning
   *
   * @param {boolean} [clone=false] - Whether to return a deep clone
   * @returns {Object} Current state object
   */
  getState(clone = false) {
    // Implementation
  }
};
```

### Event Delegation with Early Returns Pattern (quantity.js/spinner.js style)
```javascript
/**
 * Event management with event delegation
 * Modern pattern for handling dynamic content
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
 * Plus/minus button controls
 */
document.body.addEventListener("click", function (event) {
  if (!event.target.matches("button.plus, button.minus")) return;

  const button = event.target;
  const input = button.closest(".quantity")?.querySelector(".qty");
  if (!input) return;

  // Processing logic
});
```

### Timeout and Cleanup Management Pattern (spinner.js style)
```javascript
/**
 * Enhanced timeout management with cleanup
 * Prevents memory leaks and orphaned timeouts
 */

// Global state management
let timeoutIds = new Set(); // Track all timeouts for cleanup

function handleAction() {
  // Create timeout with cleanup tracking
  const actionTimeout = setTimeout(() => {
    try {
      // Action logic
    } catch (error) {
      debug("Action error:", error);
    }
  }, CONFIG.DELAY);

  timeoutIds.add(actionTimeout);

  // Cleanup timeout
  const cleanupTimeout = setTimeout(() => {
    timeoutIds.delete(actionTimeout);
    timeoutIds.delete(cleanupTimeout);
  }, CONFIG.TIMEOUT_DURATION);

  timeoutIds.add(cleanupTimeout);
}

/**
 * Cleanup timeouts on page unload to prevent memory leaks
 */
window.addEventListener("beforeunload", function () {
  timeoutIds.forEach(id => clearTimeout(id));
  timeoutIds.clear();
});
```

---

## Conclusion

This JavaScript documentation standard is derived from comprehensive analysis of BadSpiegel theme modules, including configurator modules (`load.js`, `save.js`, `carousel.js`) and cart interaction modules (`quantity.js`, `spinner.js`).

**Core Principles:**
1. **Comprehensive file headers** with feature lists and UX considerations
2. **Clear namespace organization** for utility functions
3. **Detailed JSDoc comments** with examples for complex APIs
4. **Consistent parameter patterns** especially for debug modes
5. **Future enhancement sections** with security and performance focus
6. **English-only documentation** with proper technical terminology
7. **Practical examples** that show real usage patterns
8. **Error handling documentation** for robust applications
9. **UX-focused patterns** with accessibility and user experience considerations
10. **Memory management** with proper cleanup and leak prevention

**New Patterns from Cart Module Analysis:**
- **Event Delegation with Early Returns** for modern event handling
- **UX-Optimized Input Validation** with real-time feedback
- **Comprehensive State Management** with accessibility
- **Timeout and Cleanup Management** for memory leak prevention
- **WooCommerce Integration** patterns for WordPress themes
- **HTML5 Constraint Validation** for form inputs
- **Button State Management** with loading states and accessibility

Follow these patterns to maintain consistency and quality across all JavaScript files in the BadSpiegel theme.
