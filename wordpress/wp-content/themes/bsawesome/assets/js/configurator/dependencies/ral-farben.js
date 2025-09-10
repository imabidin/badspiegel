/**
 * @version 2.5.0
 *
 * @todo Its not inside build, add to configurator.js and do feature checks, if everything works fine
 */

document.addEventListener("DOMContentLoaded", () => {
  // Cache all RAL color input elements for better performance
  const ralInputs = document.querySelectorAll(
    ['input[placeholder="RAL Farbton eingeben"]', 'input[name="ablage_farbton"]', 'input[name*="farbton"]'].join(", ")
  );

  // Apply event listeners to all RAL input fields
  ralInputs.forEach(ralInput => {
    if (!ralInput) return;

    /**
     * Clear input when focused if it contains placeholder-like text
     */
    ralInput.addEventListener("focus", function () {
      const value = this.value.trim().toLowerCase();
      const placeholder = this.placeholder.trim().toLowerCase();

      // Clear if value matches placeholder or contains auto-filled text
      if (value === placeholder || value === "ral farbton auswählen" || value === "ral farbton eingeben") {
        this.value = "";
      }
    });

    /**
     * Validate input when leaving the field (on blur)
     */
    ralInput.addEventListener("blur", function () {
      const value = this.value.trim();

      // Empty input is allowed
      if (value === "") return;

      const validation = validateRAL(value);
      if (!validation.isValid) {
        showRALModal(value, validation.errorType);
      }
    });
  });

  /**
   * Validates RAL color codes (both Classic and Design systems)
   * @param {string} value - Input value to validate
   * @returns {Object} Validation result with status and error details
   */
  function validateRAL(value) {
    // Normalize input: remove spaces and convert to uppercase
    const cleanValue = value.replace(/\s/g, "").toUpperCase();

    // Validate RAL Classic format (RAL + 4-digit number, 1000-9999)
    if (/^RAL[1-9]\d{3}$/.test(cleanValue)) {
      return { isValid: true };
    }

    // Validate RAL Design format (RAL + 3 groups of 3 digits)
    if (/^RALD\d{3}-\d{3}-\d{3}$/.test(cleanValue)) {
      return { isValid: true };
    }

    // Return format error if no valid pattern matched
    return {
      isValid: false,
      errorType: "invalid_format",
      details: "Invalid RAL color code format",
    };
  }

  /**
   * Displays validation error in a Bootstrap modal
   * @param {string} invalidValue - The invalid input value
   * @param {string} errorType - Type of validation error
   */
  function showRALModal(invalidValue, errorType) {
    // Base error message
    let message = `<p class="mb-3"><strong>"${invalidValue}"</strong> is not a valid RAL color code.</p>`;
    let modalTitle = "Validation Error";

    // Customize message based on error type
    switch (errorType) {
      case "invalid_format":
        message += `
                    <div class="alert alert-warning">
                        <p class="mb-1"><strong>Valid formats:</strong></p>
                        <ul class="mb-0">
                            <li>RAL Classic: RAL 1234 or RAL1234 (1000-9999)</li>
                            <li>RAL Design: RAL 000-000-000 or RAL000-000-000</li>
                        </ul>
                    </div>`;
        break;

      case "invalid_range":
        message += `
                    <div class="alert alert-danger">
                        RAL Classic numbers must be between 1000 and 9999.
                        <strong>"${invalidValue}"</strong> is outside this range.
                    </div>`;
        break;

      default:
        message += `
                    <div class="alert alert-info">
                        Please enter a valid RAL color code.
                    </div>`;
    }

    // Create modal with error details
    createModal({
      title: modalTitle,
      body: message,
      size: "md",
      classes: "ral-validation-modal",
      footer: [
        { text: "OK", class: "btn-dark", dismiss: true }, // Standardmäßig btn-primary, kann angepasst werden
      ],
    });
  }
});
