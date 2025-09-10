/**
 * @version 2.5.0
 *
 * @todo Optimize edge cases, i.e. refresh page, dachschraege position is default on "links", set "hoehe links" to 200 and then "hoehe rechts" to 100, but of them needs to be always above 400, actually specifically dependent on "dachschraege position"
 */

import { breiteUntenInput, breiteObenInput, hoeheLinksInput, hoeheRechtsInput } from "./../variables.js";

/**
 * Dachschräge position input reference
 */
const dachschraegePositionInputs = document.querySelectorAll('input[name="dachschraege_position"]');

/**
 * Dachschräge Validation Module
 *
 * This module provides specialized validation logic for sloped roof configurations.
 * It works as a plugin for the central input validation system (input.js).
 *
 * ARCHITECTURE:
 * - Pure business logic module (no direct DOM event listeners)
 * - Provides callback functions for input.js event system
 * - Exports validation functions used by central validation engine
 * - Only handles position radio button events (not covered by input.js)
 *
 * Handles validation for:
 * - breite_unten: bottom width (base reference)
 * - breite_oben: top width (must not exceed bottom width)
 * - hoehe_links: left height
 * - hoehe_rechts: right height
 * - dachschraege_position: controls which height can be reduced to 0
 */

/**
 * Gets the currently selected Dachschräge position
 * @returns {string|null} 'links', 'rechts', or null if none selected
 */
export function getDachschraegePosition() {
  const checkedInput = document.querySelector('input[name="dachschraege_position"]:checked');
  return checkedInput ? checkedInput.value : null;
}

/**
 * Updates min/max constraints based on Dachschräge position
 * - Position "links": hoehe_links can be 0, hoehe_rechts keeps original min
 * - Position "rechts": hoehe_rechts can be 0, hoehe_links keeps original min
 * - breite_oben: always min=0 regardless of position
 */
export function updateDachschraegeConstraints() {
  const position = getDachschraegePosition();

  // Store original min values if not already stored
  if (hoeheLinksInput && !hoeheLinksInput.dataset.originalMin) {
    hoeheLinksInput.dataset.originalMin = hoeheLinksInput.getAttribute("min") || "400";
  }
  if (hoeheRechtsInput && !hoeheRechtsInput.dataset.originalMin) {
    hoeheRechtsInput.dataset.originalMin = hoeheRechtsInput.getAttribute("min") || "400";
  }

  // Always set breite_oben min to 0
  if (breiteObenInput) {
    breiteObenInput.setAttribute("min", "0");
  }

  // Update height constraints based on position
  if (position === "links") {
    // Links: hoehe_links can be 0, hoehe_rechts keeps original min
    if (hoeheLinksInput) {
      hoeheLinksInput.setAttribute("min", "0");
    }
    if (hoeheRechtsInput) {
      hoeheRechtsInput.setAttribute("min", hoeheRechtsInput.dataset.originalMin);
    }
  } else if (position === "rechts") {
    // Rechts: hoehe_rechts can be 0, hoehe_links keeps original min
    if (hoeheLinksInput) {
      hoeheLinksInput.setAttribute("min", hoeheLinksInput.dataset.originalMin);
    }
    if (hoeheRechtsInput) {
      hoeheRechtsInput.setAttribute("min", "0");
    }
  } else {
    // No position selected: restore original min values for both heights
    if (hoeheLinksInput) {
      hoeheLinksInput.setAttribute("min", hoeheLinksInput.dataset.originalMin);
    }
    if (hoeheRechtsInput) {
      hoeheRechtsInput.setAttribute("min", hoeheRechtsInput.dataset.originalMin);
    }
  }
}

/**
 * Validates Dachschräge position constraints
 * Ensures that at least one height is above minimum when a position is selected
 * @returns {string|null} Error message or null if valid
 */
export function validateDachschraegePosition() {
  const position = getDachschraegePosition();
  if (!position) return null;

  const hoeheLinks = parseFloat(hoeheLinksInput?.value) || 0;
  const hoeheRechts = parseFloat(hoeheRechtsInput?.value) || 0;
  const originalMinHeight = parseFloat(hoeheLinksInput?.dataset.originalMin) || 400;

  if (position === "links") {
    // Wenn links gewählt, muss rechte Höhe mindestens originalMin sein
    if (hoeheRechts < originalMinHeight) {
      return `Bei Dachschräge Position "links" muss die rechte Höhe mindestens ${originalMinHeight} mm betragen.`;
    }
  } else if (position === "rechts") {
    // Wenn rechts gewählt, muss linke Höhe mindestens originalMin sein
    if (hoeheLinks < originalMinHeight) {
      return `Bei Dachschräge Position "rechts" muss die linke Höhe mindestens ${originalMinHeight} mm betragen.`;
    }
  }

  return null;
}

/**
 * Auto-adjusts Dachschräge position based on height values
 * This provides intelligent suggestions when user enters asymmetric heights
 * @deprecated Use suggestOptimalPosition() instead for better UX
 */
export function autoDetectDachschraegePosition() {
  suggestOptimalPosition();
}

/**
 * Sets the Dachschräge position programmatically
 * @param {string} position - 'links' or 'rechts'
 */
export function setDachschraegePosition(position) {
  const targetInput = document.querySelector(`input[name="dachschraege_position"][value="${position}"]`);
  if (targetInput) {
    targetInput.checked = true;
    targetInput.dispatchEvent(new Event("change", { bubbles: true }));
  }
}

/**
 * Validates that top width does not exceed bottom width
 * @param {HTMLElement} breiteObenField - Top width input field
 * @param {HTMLElement} breiteUntenField - Bottom width input field
 * @returns {string|null} Error message or null if valid
 */
export function validateBreiteOben(breiteObenField, breiteUntenField) {
  const breiteOben = parseFloat(breiteObenField?.value) || 0;
  const breiteUnten = parseFloat(breiteUntenField?.value) || 0;

  if (breiteOben > breiteUnten) {
    return 'Die "Breite oben" darf nicht größer sein als die "Breite unten".';
  }
  return null;
}

/**
 * Validates height relationship with Dachschräge position constraints
 * @param {HTMLElement} hoeheLinksField - Left height input field
 * @param {HTMLElement} hoeheRechtsField - Right height input field
 * @returns {string|null} Error message or null if valid
 */
export function validateHoeheRelation(hoeheLinksField, hoeheRechtsField) {
  // First check Dachschräge position constraints
  const positionError = validateDachschraegePosition();
  if (positionError) {
    return positionError;
  }

  // Additional height relationship validation can be added here
  return null;
}

/**
 * Gets validation message for a specific field based on Dachschräge rules
 * Only validates input fields, not radio buttons (position validation happens internally)
 * @param {HTMLElement} field - The field to validate
 * @returns {string|null} Error message or null if valid
 */
export function getDachschraegeValidationMessage(field) {
  if (!field) return null;

  switch (field.name) {
    case "breite_oben":
      // Only validate breite_oben against breite_unten
      return validateBreiteOben(field, breiteUntenInput);

    case "hoehe_links":
      // Only validate left height constraints - don't trigger right height validation here
      const position = getDachschraegePosition();
      if (position === "links") {
        // Bei Position "links" kann das linke Höhe-Feld unter dem ursprünglichen Minimum sein
        // da hier die Dachschräge ist - keine zusätzliche Validierung nötig
      } else if (position === "rechts") {
        // Bei Position "rechts" muss das linke Höhe-Feld mindestens das ursprüngliche Minimum haben
        const hoeheLinks = parseFloat(field.value) || 0;
        const originalMinHeight = parseFloat(field.dataset.originalMin) || 400;
        if (hoeheLinks < originalMinHeight) {
          return `Bei Dachschräge Position "rechts" muss die linke Höhe mindestens ${originalMinHeight} mm betragen.`;
        }
      }

      // Basic min/max validation (this is handled by input.js, but we include it for completeness)
      const minValue = parseFloat(field.getAttribute("min"));
      const currentValue = parseFloat(field.value) || 0;
      if (!isNaN(minValue) && currentValue < minValue) {
        return `Der Wert muss mindestens ${minValue} mm betragen.`;
      }

      return null;

    case "hoehe_rechts":
      // Only validate right height constraints - don't trigger left height validation here
      const positionRight = getDachschraegePosition();
      if (positionRight === "rechts") {
        // Bei Position "rechts" kann das rechte Höhe-Feld unter dem ursprünglichen Minimum sein
        // da hier die Dachschräge ist - keine zusätzliche Validierung nötig
      } else if (positionRight === "links") {
        // Bei Position "links" muss das rechte Höhe-Feld mindestens das ursprüngliche Minimum haben
        const hoeheRechts = parseFloat(field.value) || 0;
        const originalMinHeight = parseFloat(hoeheRechtsInput?.dataset.originalMin) || 400;
        if (hoeheRechts < originalMinHeight) {
          return `Bei Dachschräge Position "links" muss die rechte Höhe mindestens ${originalMinHeight} mm betragen.`;
        }
      }

      // Basic min/max validation (this is handled by input.js, but we include it for completeness)
      const minValueRight = parseFloat(field.getAttribute("min"));
      const currentValueRight = parseFloat(field.value) || 0;
      if (!isNaN(minValueRight) && currentValueRight < minValueRight) {
        return `Der Wert muss mindestens ${minValueRight} mm betragen.`;
      }

      return null;

    // Radio button validation removed - position validation happens internally
    // and doesn't need visual feedback containers
    default:
      return null;
  }
}

/**
 * Gets all fields that should be re-validated when a specific field changes
 * This is primarily used for cross-field validation after user completes input
 * @param {HTMLElement} field - The field that changed
 * @returns {HTMLElement[]} Array of fields to re-validate
 */
export function getDependentFields(field) {
  if (!field) return [];

  let dependentFields = [];

  switch (field.name) {
    case "breite_unten":
      // When bottom width changes, re-validate top width (for breite_oben > breite_unten check)
      if (breiteObenInput) dependentFields.push(breiteObenInput);
      break;

    case "breite_oben":
      // Top width validation is self-contained - no dependent fields needed
      break;

    case "hoehe_links":
      // For height fields, we don't immediately validate the other height field
      // This prevents double validation messages during input
      // Cross-validation happens in change event via onDachschraegeFieldChange
      break;

    case "hoehe_rechts":
      // For height fields, we don't immediately validate the other height field
      // This prevents double validation messages during input
      // Cross-validation happens in change event via onDachschraegeFieldChange
      break;

    case "dachschraege_position":
      // Position radio buttons are handled internally - no dependent fields for validation UI
      // Constraint updates and validation happen through position change event listeners
      updateDachschraegeConstraints();
      if (hoeheLinksInput) dependentFields.push(hoeheLinksInput);
      if (hoeheRechtsInput) dependentFields.push(hoeheRechtsInput);

      // Trigger comprehensive update for position changes
      setTimeout(() => {
        validateAndUpdateAllPositionFields();
      }, 10);
      break;
  }

  // Trigger carousel height update after dependent field validation
  if (dependentFields.length > 0) {
    triggerCarouselHeightUpdate();
  }

  return dependentFields;
}

/**
 * Checks if a field is part of Dachschräge configuration
 * @param {HTMLElement} field - The field to check
 * @returns {boolean} True if field is part of Dachschräge
 */
export function isDachschraegeField(field) {
  if (!field) return false;

  // Only handle input fields, not radio buttons (radio buttons are handled separately)
  const dachschraegeInputFields = ["breite_unten", "breite_oben", "hoehe_links", "hoehe_rechts"];
  return dachschraegeInputFields.includes(field.name);
}

/**
 * Triggers carousel height update after validation changes
 * Should be called whenever invalid feedback is shown/hidden
 */
export function triggerCarouselHeightUpdate() {
  // Small delay to ensure DOM changes (validation feedback) are rendered
  setTimeout(() => {
    if (window.updateCarouselHeight) {
      window.updateCarouselHeight();
    }
  }, 100);
}

/**
 * Validates and updates all Dachschräge-related fields
 * This ensures complete synchronization between all fields
 */
export function validateAndUpdateAllPositionFields() {
  // Update constraints first
  updateDachschraegeConstraints();

  // Validate all fields if validation function is available
  if (window.validateField) {
    // Validate height fields (these trigger position-specific validation)
    if (hoeheLinksInput) {
      window.validateField(hoeheLinksInput);
    }
    if (hoeheRechtsInput) {
      window.validateField(hoeheRechtsInput);
    }

    // Validate width fields
    if (breiteObenInput) {
      window.validateField(breiteObenInput);
    }
    if (breiteUntenInput) {
      window.validateField(breiteUntenInput);
    }

    // Note: Position radio buttons are not validated via validateField
    // to prevent invalid-feedback containers from being created
  }

  // Update carousel height after all validations
  triggerCarouselHeightUpdate();
}

/**
 * Provides smart suggestions for position based on current height values
 * More sophisticated than simple auto-detection
 */
export function suggestOptimalPosition() {
  const hoeheLinks = parseFloat(hoeheLinksInput?.value) || 0;
  const hoeheRechts = parseFloat(hoeheRechtsInput?.value) || 0;
  const originalMinHeight = parseFloat(hoeheLinksInput?.dataset.originalMin) || 400;
  const currentPosition = getDachschraegePosition();

  // Determine optimal position based on values
  let suggestedPosition = null;

  // Case 1: Left height is below minimum, right height is acceptable
  if (hoeheLinks < originalMinHeight && hoeheRechts >= originalMinHeight) {
    suggestedPosition = "links";
  }
  // Case 2: Right height is below minimum, left height is acceptable
  else if (hoeheRechts < originalMinHeight && hoeheLinks >= originalMinHeight) {
    suggestedPosition = "rechts";
  }
  // Case 3: Both heights are acceptable - validate current position
  else if (hoeheLinks >= originalMinHeight && hoeheRechts >= originalMinHeight) {
    // Keep current position if it's valid, otherwise suggest based on smaller value
    if (currentPosition) {
      // Current position is fine, no change needed
      return;
    } else {
      // No position selected, suggest based on which side is smaller
      suggestedPosition = hoeheLinks <= hoeheRechts ? "links" : "rechts";
    }
  }
  // Case 4: Both heights are below minimum - this is an invalid state
  else if (hoeheLinks < originalMinHeight && hoeheRechts < originalMinHeight) {
    // Suggest position based on which height is closer to minimum
    const linksDistance = originalMinHeight - hoeheLinks;
    const rechtsDistance = originalMinHeight - hoeheRechts;
    suggestedPosition = linksDistance <= rechtsDistance ? "links" : "rechts";
  }

  // Only change if suggestion is different from current position
  if (suggestedPosition && suggestedPosition !== currentPosition) {
    setDachschraegePosition(suggestedPosition);

    // Visual feedback that position was auto-adjusted
    setTimeout(() => {
      const positionGroup = document.querySelector('[data-key="dachschraege_position"]');
      if (positionGroup) {
        positionGroup.classList.add("focus-ring-success");
        setTimeout(() => {
          positionGroup.classList.remove("focus-ring-success");
        }, 2000);
      }
    }, 100);
  }
}

/**
 * Callback function for input events on Dachschräge fields
 * Called by input.js when a Dachschräge field receives input
 * @param {HTMLElement} field - The field that received input
 */
export function onDachschraegeFieldInput(field) {
  // Handle dependent field validation for real-time feedback
  const dependentFields = getDependentFields(field);
  dependentFields.forEach(dependentField => {
    setTimeout(() => {
      if (window.validateField) {
        window.validateField(dependentField);
      }
    }, 0);
  });
}

/**
 * Callback function for change events on Dachschräge fields
 * Called by input.js when a Dachschräge field value changes (user completes input)
 * @param {HTMLElement} field - The field that changed
 */
export function onDachschraegeFieldChange(field) {
  // Handle position-specific logic for height fields
  if (field.name === "hoehe_links" || field.name === "hoehe_rechts") {
    // Smart position suggestion based on height values
    suggestOptimalPosition();

    // Re-validate the other height field after position might have changed
    const otherHeightField = field.name === "hoehe_links" ? hoeheRechtsInput : hoeheLinksInput;
    if (otherHeightField && window.validateField) {
      setTimeout(() => {
        window.validateField(otherHeightField);
      }, 100); // Small delay to ensure position change is processed
    }
  }

  // Validate dependent fields for all field types
  const dependentFields = getDependentFields(field);
  dependentFields.forEach(dependentField => {
    setTimeout(() => {
      if (window.validateField) {
        window.validateField(dependentField);
      }
    }, 0);
  });

  // Trigger position validation and suggestions for height fields
  if (field.name === "hoehe_links" || field.name === "hoehe_rechts") {
    setTimeout(() => {
      validateAndUpdateAllPositionFields();
    }, 50);
  }
}

/**
 * Initialize Dachschräge dependencies on DOM ready
 */
document.addEventListener("DOMContentLoaded", () => {
  // Initialize constraints based on current position
  updateDachschraegeConstraints();

  // Add event listeners ONLY for position radio buttons (not handled by input.js)
  dachschraegePositionInputs.forEach(input => {
    input.addEventListener("change", () => {
      // Immediate constraint update for better responsiveness
      updateDachschraegeConstraints();

      // Comprehensive validation of all related fields
      setTimeout(() => {
        validateAndUpdateAllPositionFields();
      }, 20);
    });
  });

  // Initial auto-detection with improved logic
  setTimeout(() => {
    if (
      (hoeheLinksInput?.value && hoeheLinksInput.value !== "0") ||
      (hoeheRechtsInput?.value && hoeheRechtsInput.value !== "0")
    ) {
      suggestOptimalPosition();
      validateAndUpdateAllPositionFields();
    }
  }, 200); // Increased delay for complete DOM readiness
});
