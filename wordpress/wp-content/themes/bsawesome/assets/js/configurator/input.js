/**
 * Product Configurator - Input Validation Module
 *
 * This module serves as the central validation engine for the product configurator.
 * It provides real-time validation feedback, custom error messages,
 * relational field validation, and Bootstrap form state management.
 *
 * ARCHITECTURE (DRY Principle):
 * - input.js: Central validation engine with generic event listeners
 * - Specialized modules (e.g., dachschraege.js): Domain-specific validation rules
 * - Callback system: Specialized modules provide callback functions for input.js
 *
 * Features:
 * - Real-time validation with visual feedback
 * - Custom error message positioning
 * - Relational field constraints (e.g., width dependencies)
 * - Bootstrap form state classes (is-valid, is-invalid)
 * - Dynamic min/max attribute updates
 * - Modular validation rules via callback system
 *
 * @version 2.6.0
 * @package Configurator
 */

import {
  getDachschraegeValidationMessage,
  getDependentFields,
  isDachschraegeField,
  triggerCarouselHeightUpdate,
  validateAndUpdateAllPositionFields,
  onDachschraegeFieldInput,
  onDachschraegeFieldChange,
} from "./dependencies/dachschraege.js";

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
   */
  const durchmesserInput = document.querySelector('.option-input[name="durchmesser"]');
  const schnittkanteInput = document.querySelector('.option-input[name="schnittkante"]');
  const hoeheInput = document.querySelector('.option-input[name="hoehe_schnittkante"]') ||
                    document.querySelector('.option-input[name="breite_schnittkante"]');

  /**
   * SK1 geometric calculation functions
   * Based on circular segment formulas
   */

  /**
   * Calculates segment height from diameter and cut width
   * @param {number} diameter - Circle diameter
   * @param {number} cutWidth - Cut width (chord length)
   * @returns {number|null} Calculated height or null if invalid
   */
  function calculateHeightFromDiameterAndCut(diameter, cutWidth) {
    if (diameter <= 0 || cutWidth <= 0 || cutWidth > diameter) return null;

    const r = diameter / 2;
    const discriminant = r * r - (cutWidth * cutWidth) / 4;

    if (discriminant < 0) return null;

    const distanceToChord = r - Math.sqrt(discriminant);
    const segmentHeight = 2 * r - distanceToChord;

    return Math.round(segmentHeight); // Round to whole numbers
  }

  /**
   * Calculates cut width from diameter and height
   * @param {number} diameter - Circle diameter
   * @param {number} height - Segment height
   * @returns {number|null} Calculated cut width or null if invalid
   */
  function calculateCutFromDiameterAndHeight(diameter, height) {
    if (diameter <= 0 || height <= 0 || height > diameter) return null;

    const r = diameter / 2;
    const distanceToChord = 2 * r - height;
    const discriminant = r * r - distanceToChord * distanceToChord;

    if (discriminant < 0) return null;

    const cutWidth = 2 * Math.sqrt(discriminant);

    return Math.round(cutWidth); // Round to whole numbers
  }

  /**
   * Updates calculated field without triggering recursive calculations
   * @param {HTMLElement} field - Target field to update
   * @param {number} value - New calculated value
   */
  function updateCalculatedField(field, value) {
    if (!field || value === null || value <= 0) return;

    // Temporarily mark field as "calculating" to prevent recursive updates
    field.dataset.calculating = 'true';
    field.value = value;

    // Trigger validation after update
    if (window.validateField) {
      validateField(field);
    }

    // Remove calculating flag after a short delay
    setTimeout(() => {
      delete field.dataset.calculating;
    }, 100);
  }

  /**
   * Performs SK1 calculations based on field changes
   * @param {HTMLElement} changedField - The field that was changed
   */
  function performSK1Calculations(changedField) {
    if (!changedField || changedField.dataset.calculating === 'true') return;

    const diameter = parseFloat(durchmesserInput?.value) || 0;
    const cutWidth = parseFloat(schnittkanteInput?.value) || 0;
    const height = parseFloat(hoeheInput?.value) || 0;

    console.log(`[SK1 Calc] ${changedField.name} changed: D=${diameter}, C=${cutWidth}, H=${height}`);

    switch (changedField.name) {
      case "durchmesser":
        // Priority 1: Durchmesser changed -> calculate height based on schnittkante
        if (diameter > 0 && cutWidth > 0) {
          const calculatedHeight = calculateHeightFromDiameterAndCut(diameter, cutWidth);
          if (calculatedHeight !== null && hoeheInput) {
            console.log(`[SK1 Calc] Diameter change: calculating height = ${calculatedHeight}`);
            updateCalculatedField(hoeheInput, calculatedHeight);
          }
        }
        break;

      case "hoehe_schnittkante":
      case "breite_schnittkante":
        // Priority 2: Height changed -> calculate schnittkante based on durchmesser
        if (diameter > 0 && height > 0) {
          const calculatedCut = calculateCutFromDiameterAndHeight(diameter, height);
          if (calculatedCut !== null && schnittkanteInput) {
            console.log(`[SK1 Calc] Height change: calculating cut width = ${calculatedCut}`);
            updateCalculatedField(schnittkanteInput, calculatedCut);
          }
        }
        break;

      case "schnittkante":
        // Priority 3: Schnittkante changed -> calculate height based on durchmesser
        if (diameter > 0 && cutWidth > 0) {
          const calculatedHeight = calculateHeightFromDiameterAndCut(diameter, cutWidth);
          if (calculatedHeight !== null && hoeheInput) {
            console.log(`[SK1 Calc] Cut width change: calculating height = ${calculatedHeight}`);
            updateCalculatedField(hoeheInput, calculatedHeight);
          }
        }
        break;
    }
  }

  /**
   * Updates the max attribute of schnittkante field based on durchmesser value
   * Ensures schnittkante cannot exceed durchmesser and corrects values if needed
   */
  function updateSchnittkanteMax() {
    if (!durchmesserInput || !schnittkanteInput) return;

    const durchmesserValue = parseFloat(durchmesserInput.value) || 0;

    if (durchmesserValue > 0) {
      // Set max attribute to durchmesser value
      schnittkanteInput.setAttribute("max", durchmesserValue);

      // No auto-correction - let validation handle it
    } else {
      // Remove max constraint if no durchmesser value
      schnittkanteInput.removeAttribute("max");
    }
  }

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

    // Trigger carousel height update when feedback visibility changes
    triggerCarouselHeightUpdate();
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

    // Dachschräge validation (outsourced to dedicated module)
    if (isDachschraegeField(field)) {
      const dachschraegeMessage = getDachschraegeValidationMessage(field);
      if (dachschraegeMessage) {
        messages.push(dachschraegeMessage);
      }
    }

    // 4. Apply validation state based on collected messages
    if (messages.length > 0) {
      field.classList.add("is-invalid");
      setInvalidFeedback(field, messages.join(" "));
    } else {
      field.classList.remove("is-invalid");
      setInvalidFeedback(field, "");
    }
  }

  // Make validateField globally available for other modules (e.g., dachschraege.js)
  window.validateField = validateField;

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
   * Updates the max attribute of shelf width field based on bottom width
   * Ensures shelf width cannot exceed bottom width and corrects values if needed
   * This provides dynamic constraint updates for related fields
   */
  function updateAblageBreiteMax() {
    const breiteUntenField = document.querySelector('input[name="breite_unten"]');
    const ablageBreiteField = document.querySelector('input[name="ablage_breite"]');
    if (breiteUntenField && ablageBreiteField) {
      const breiteUntenValue = parseFloat(breiteUntenField.value) || 0;
      ablageBreiteField.setAttribute("max", breiteUntenValue);

      // Auto-correct shelf width if it exceeds bottom width
      const ablageBreiteValue = parseFloat(ablageBreiteField.value) || 0;
      if (ablageBreiteValue > breiteUntenValue) {
        ablageBreiteField.value = breiteUntenValue;
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
      if (field.name === "breite_unten") {
        updateAblageBreiteMax();
      }

      // Handle durchmesser field changes for schnittkante max validation
      if (field.name === "durchmesser") {
        updateSchnittkanteMax();
      }

      // Handle SK1 geometric calculations
      if (field.name === "durchmesser" ||
          field.name === "schnittkante" ||
          field.name === "hoehe_schnittkante" ||
          field.name === "breite_schnittkante") {
        performSK1Calculations(field);
      }

      // Handle Dachschräge field dependencies using centralized logic
      if (isDachschraegeField(field)) {
        onDachschraegeFieldInput(field);
      }

      // Show 'is-valid' state during typing when no errors exist
      // This provides positive feedback during data entry
      if (document.activeElement === field && !field.classList.contains("is-invalid")) {
        field.classList.add("is-valid");
      } else {
        field.classList.remove("is-valid");
      }
    });

    /**
     * Change event handler
     * Handles cross-field validation and position suggestions when user completes input
     */
    field.addEventListener("change", function () {
      // Handle Dachschräge field dependencies and position suggestions
      if (isDachschraegeField(field)) {
        onDachschraegeFieldChange(field);
      }

      // Handle SK1 geometric calculations on field completion
      if (field.name === "durchmesser" ||
          field.name === "schnittkante" ||
          field.name === "hoehe_schnittkante" ||
          field.name === "breite_schnittkante") {
        performSK1Calculations(field);
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
   */
  updateAblageBreiteMax();

  /**
   * Initialize schnittkante constraints on page load
   * Sets up initial max attribute if durchmesser field has a value
   */
  updateSchnittkanteMax();
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
