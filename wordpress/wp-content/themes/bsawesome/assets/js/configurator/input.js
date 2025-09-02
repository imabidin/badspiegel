/**
 * Product Configurator - Input Validation Module
 *
 * This module handles form input validation for the product configurator.
 * It provides real-time validation feedback, custom error messages,
 * relational field validation, and Bootstrap form state management.
 *
 * Features:
 * - Real-time validation with visual feedback
 * - Custom error message positioning
 * - Relational field constraints (e.g., width dependencies)
 * - Bootstrap form state classes (is-valid, is-invalid)
 * - Dynamic min/max attribute updates
 *
 * @version 2.2.0
 * @package Configurator
 */

document.addEventListener("DOMContentLoaded", function () {
  /**
   * Select all relevant input fields for validation
   * Includes number inputs, text inputs, and textareas with .option-input class
   */
  const fields = document.querySelectorAll(
    'input.option-input[type="number"], input.option-input[type="text"], textarea.option-input'
  );

  /**
   * Special field references for relational validation
   * These fields have interdependencies that require custom validation logic
   */
  const breiteField = document.querySelector('input[name="breite"]');
  const ablageBreiteField = document.querySelector(
    'input[name="ablage_breite"]'
  );

  /**
   * Ensures that an invalid feedback container exists for the given field
   * Creates the container if it doesn't exist and positions it correctly
   * relative to Bootstrap's form-floating structure
   *
   * @param {HTMLElement} field - The input field requiring validation feedback
   * @returns {HTMLElement} The invalid feedback container element
   */
  function ensureInvalidContainer(field) {
    let invalidFeedback;

    // Check if field is within a Bootstrap form-floating container
    const formFloating = field.closest(".form-floating");

    if (formFloating) {
      // For form-floating: place feedback after the floating container
      const nextSibling = formFloating.nextElementSibling;
      if (nextSibling && nextSibling.classList.contains("invalid-feedback")) {
        // Reuse existing feedback element
        invalidFeedback = nextSibling;
      } else {
        // Create new feedback element
        invalidFeedback = document.createElement("div");
        invalidFeedback.classList.add("invalid-feedback");
        invalidFeedback.style.display = "none"; // Initially hidden
        formFloating.insertAdjacentElement("afterend", invalidFeedback);
      }
    } else {
      // For standard inputs: place feedback within parent element
      invalidFeedback = field.parentNode.querySelector(".invalid-feedback");
      if (!invalidFeedback) {
        invalidFeedback = document.createElement("div");
        invalidFeedback.classList.add("invalid-feedback");
        invalidFeedback.style.display = "none"; // Initially hidden
        field.parentNode.appendChild(invalidFeedback);
      }
    }

    return invalidFeedback;
  }

  /**
   * Sets the validation error message for a field
   * Creates the feedback container if needed and manages visibility
   *
   * @param {HTMLElement} field - The input field to show feedback for
   * @param {string} message - The error message to display (empty string hides feedback)
   */
  function setInvalidFeedback(field, message) {
    const feedbackElement = ensureInvalidContainer(field);
    feedbackElement.textContent = message;

    // Show feedback if message exists, hide otherwise
    if (message) {
      feedbackElement.style.display = "block";
    } else {
      feedbackElement.style.display = "none";
    }
  }

  /**
   * Validates a field based on multiple criteria:
   * - Required field validation
   * - Min/max value validation for number inputs
   * - Custom relational validation rules
   * - Sets Bootstrap validation classes and error messages
   *
   * @param {HTMLElement} field - The input field to validate
   */
  function validateField(field) {
    let messages = [];

    // 1. Required field validation
    if (field.hasAttribute("required") && field.value.trim() === "") {
      messages.push("Dieses Feld ist erforderlich.");
    }

    // 2. Min/max validation for number inputs
    if (field.type === "number" && field.value.trim() !== "") {
      const val = parseFloat(field.value);
      const minAttr = field.getAttribute("min");
      const maxAttr = field.getAttribute("max");

      if (minAttr !== null && val < parseFloat(minAttr)) {
        messages.push(`Der Wert muss mindestens ${minAttr} sein.`);
      }
      if (maxAttr !== null && val > parseFloat(maxAttr)) {
        messages.push(`Der Wert darf höchstens ${maxAttr} sein.`);
      }
    }

    // 3. Custom relational validation rules

    // Validate upper width against main width
    if (field.name === "obere_breite") {
      const obereBreite = parseFloat(field.value) || 0;
      const breite =
        parseFloat(document.querySelector('input[name="breite"]')?.value) || 0;
      if (obereBreite > breite) {
        messages.push(
          'Die "obere Breite" darf nicht größer sein als die Breite.'
        );
      }
    }

    // Validate shorter height against main height
    if (field.name === "kuerze_hoehe") {
      const kuerzeHoehe = parseFloat(field.value) || 0;
      const hoehe =
        parseFloat(document.querySelector('input[name="hoehe"]')?.value) || 0;
      if (kuerzeHoehe > hoehe) {
        messages.push(
          'Die "kürzere Höhe" darf nicht größer sein als die Haupthöhe.'
        );
      }
    }

    // 4. Shelf width validation (currently disabled)
    // Uncomment to enable shelf width validation against main width
    /*
    if (field.name === "ablage_breite") {
      const ablageBreite = parseFloat(field.value) || 0;
      const breite = parseFloat(breiteField ? breiteField.value : 0) || 0;
      if (ablageBreite > breite) {
        messages.push("Die Ablage Breite darf nicht größer sein als die Breite.");
      }
    }
    */

    // 5. Apply validation state based on collected messages
    if (messages.length > 0) {
      field.classList.add("is-invalid");
      setInvalidFeedback(field, messages.join(" "));
    } else {
      field.classList.remove("is-invalid");
      setInvalidFeedback(field, "");
    }
  }

  /**
   * Updates field state classes based on focus and content:
   * - 'on-input': Field is currently focused
   * - 'yes-input': Field has content and is not focused
   * - 'no-input': Field is empty and not focused
   *
   * Also triggers field validation after updating classes
   *
   * @param {HTMLElement} field - The input field to update
   */
  function updateClasses(field) {
    // Handle focus state
    if (document.activeElement === field) {
      field.classList.add("on-input");
    } else {
      field.classList.remove("on-input");

      // Update content-based classes when not focused
      if (field.value.trim() === "") {
        field.classList.add("no-input");
        field.classList.remove("yes-input");
      } else {
        field.classList.add("yes-input");
        field.classList.remove("no-input");
      }
    }

    // Trigger validation after class updates
    validateField(field);
  }

  /**
   * Updates the max attribute of shelf width field based on main width
   * Ensures shelf width cannot exceed main width and corrects values if needed
   * This provides dynamic constraint updates for related fields
   */
  function updateAblageBreiteMax() {
    if (breiteField && ablageBreiteField) {
      const breiteValue = parseFloat(breiteField.value) || 0;
      ablageBreiteField.setAttribute("max", breiteValue);

      // Auto-correct shelf width if it exceeds main width
      const ablageBreiteValue = parseFloat(ablageBreiteField.value) || 0;
      if (ablageBreiteValue > breiteValue) {
        ablageBreiteField.value = breiteValue;
      }

      // Update classes and validation after correction
      updateClasses(ablageBreiteField);
    }
  }

  /**
   * Initialize event listeners for all input fields
   * Sets up real-time validation and visual feedback
   */
  fields.forEach(function (field) {
    /**
     * Focus event handler
     * Updates visual state when field gains focus
     */
    field.addEventListener("focus", function () {
      updateClasses(field);
    });

    /**
     * Input event handler (real-time validation)
     * Provides immediate feedback during typing and handles relational updates
     */
    field.addEventListener("input", function () {
      updateClasses(field);

      // Handle width field changes for relational validation
      if (field.name === "breite") {
        updateAblageBreiteMax();
      }

      // Show 'is-valid' state during typing when no errors exist
      // This provides positive feedback during data entry
      if (
        document.activeElement === field &&
        !field.classList.contains("is-invalid")
      ) {
        field.classList.add("is-valid");
      } else {
        field.classList.remove("is-valid");
      }
    });

    /**
     * Blur event handler
     * Performs final validation when field loses focus
     * Removes 'is-valid' state for cleaner final appearance
     */
    field.addEventListener("blur", function () {
      // Optional: Auto-round values to valid range
      // autoRoundToRange(field);

      // Final validation and class updates
      updateClasses(field);

      // Remove positive validation state on blur for cleaner UI
      field.classList.remove("is-valid");
    });
  });

  /**
   * Initialize shelf width constraints on page load
   * Sets up initial max attribute if main width field has a value
   * Currently disabled - uncomment to enable initial constraint setup
   */
  // updateAblageBreiteMax();
});

/**
 * Future Enhancement Ideas:
 *
 * 1. Auto-rounding function for numeric inputs:
 *    function autoRoundToRange(field) {
 *      if (field.type === "number" && field.value.trim() !== "") {
 *        const val = parseFloat(field.value);
 *        const min = parseFloat(field.getAttribute("min")) || -Infinity;
 *        const max = parseFloat(field.getAttribute("max")) || Infinity;
 *        const corrected = Math.min(Math.max(val, min), max);
 *        if (corrected !== val) {
 *          field.value = corrected;
 *        }
 *      }
 *    }
 *
 * 2. Debounced validation for better performance on rapid input
 * 3. Custom validation rules configuration object
 * 4. Internationalization support for error messages
 * 5. Accessibility enhancements with ARIA attributes
 */
