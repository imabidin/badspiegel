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
 * @version 2.3.0
 * @package Configurator
 */

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
    console.log(`[ConfigLoader][${timestamp}]`, ...args);
  }
}

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
    log(debug, "Formatting price", price);
    return price > 0
      ? ` (+${price.toLocaleString("de-DE", {
          style: "currency",
          currency: "EUR",
        })})`
      : "";
  },
};

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
    const result = id ? parseInt(id, 10) : null;
    log(debug, "Parsed product ID", { input: id, output: result });
    return result;
  },

  /**
   * Applies field value to appropriate form element based on type
   * Handles different input types with proper event triggering
   *
   * @param {string} fieldName - Name attribute of the target field
   * @param {string} fieldType - Type of field (radio/input/select)
   * @param {string} fieldValue - Value to apply to the field
   * @param {boolean} [debug=false] - Enable debug logging
   * @returns {Object} Result object with found status and field information
   *
   * @example
   * DataUtils.applyFieldValue("size", "radio", "large", true);
   * // Returns { found: true, fieldName: "size", fieldType: "radio", fieldValue: "large" }
   */
  applyFieldValue(fieldName, fieldType, fieldValue, debug = false) {
    let $element;
    let found = false;

    switch (fieldType) {
      case "radio":
        // Find and select specific radio button
        $element = $(`input[type="radio"][name="${fieldName}"][value="${fieldValue}"]`);
        if ($element.length) {
          $element.prop("checked", true).trigger("change");
          found = true;
        }
        log(debug, "Applied radio field", { fieldName, fieldValue, found });
        break;

      case "input":
        // Set value for text/number inputs
        $element = $(`input[name="${fieldName}"]`);
        if ($element.length) {
          $element.val(fieldValue);
          found = true;
        }
        log(debug, "Applied input field", { fieldName, fieldValue, found });
        break;

      case "select":
        // Set selected option for select elements
        $element = $(`select[name="${fieldName}"]`);
        if ($element.length) {
          $element.val(fieldValue);
          found = true;
        }
        log(debug, "Applied select field", { fieldName, fieldValue, found });
        break;

      default:
        log(debug, "Unknown field type encountered", {
          fieldName,
          fieldType,
          fieldValue,
        });
        console.warn("Unknown fieldType:", fieldType, fieldName, fieldValue);
    }

    return { found, fieldName, fieldType, fieldValue };
  },

  /**
   * Applies complete configuration to form with field reset
   * Resets all form fields before applying new configuration
   *
   * @param {Object} configData - Configuration data object with field definitions
   * @param {boolean} [debug=false] - Enable debug logging
   * @returns {Array} Array of fields that couldn't be found/applied
   *
   * @example
   * const config = { size: { type: "radio", value: "large" } };
   * const notFound = DataUtils.applyConfigData(config, true);
   */
  applyConfigData(configData, debug = false) {
    const notFound = [];

    log(debug, "Resetting all form fields before applying configuration");

    // Reset all form fields to default state
    $("input.option-radio, input.option-check").prop("checked", false);
    $("input.option-input").val("");
    $("select.option-select").prop("selectedIndex", 0);

    // Apply each configuration field
    $.each(configData, (fieldName, fieldObj) => {
      const result = this.applyFieldValue(fieldName, fieldObj.type, fieldObj.value, debug);

      if (!result.found) {
        log(debug, "Field not found in current form", { fieldName, fieldObj });
        notFound.push({
          fieldName,
          fieldType: fieldObj.type,
          fieldValue: fieldObj.value,
        });
      }
    });

    log(debug, "Configuration application completed. Fields not found:", notFound);
    return notFound;
  },
};

/**
 * UI Rendering Utilities
 *
 * Handles creation and rendering of configuration summaries and option lists.
 * Provides structured display of applied configurations with grouping and formatting.
 *
 * @namespace RenderUtils
 */
const RenderUtils = {
  /**
   * Creates structured option objects from configuration data
   * Extracts metadata from DOM elements to create rich option objects
   *
   * @param {Object} configData - Raw configuration data
   * @param {boolean} [debug=false] - Enable debug logging
   * @returns {Array} Array of structured option objects
   *
   * @example
   * const options = RenderUtils.createOptionObjects(configData, true);
   * // Returns array with objects containing label, value, price, grouping info
   */
  createOptionObjects(configData, debug = false) {
    const options = [];

    $.each(configData, (fieldName, fieldObj) => {
      // Find the form element to extract metadata
      const $input = $(`[name="${fieldName}"]`).first();
      if (!$input.length) return;

      // Extract grouping and container information
      const $group = $input.closest(".option-group");
      const isChild = $group.hasClass("option-group-child");
      const groupKey = $group.data("group") || "Sonstiges";
      const groupOrder = parseInt($group.data("order"), 10) || 999;

      // Get carousel step information if available
      const $carousel = $group.closest(".carousel-item[data-label]");
      const groupLabel = $carousel.length ? $carousel.data("label") : groupKey;
      const groupStep = $carousel.length ? parseInt($carousel.data("step"), 10) || 999 : 999;

      // Extract display values and pricing
      const label = $group.data("label") || fieldName;
      let value = fieldObj.value !== undefined ? fieldObj.value : "";
      let price = null;

      // Handle radio/checkbox specific processing
      if ($input.is(":radio") || $input.is(":checkbox")) {
        const $selected = $(`[name="${fieldName}"][value="${fieldObj.value}"]`);
        if ($selected.length) {
          const selectedLabel = $selected.data("label");

          // Skip empty child options (typically "none" selections)
          if (isChild && (value === "" || selectedLabel?.trim().toLowerCase() === "keins")) {
            return;
          }

          value = selectedLabel || value;
          price = $selected.data("price");
        }
      } else {
        // Skip empty child input values
        if (isChild && value === "") return;
        price = $input.data("price");
      }

      // Create structured option object
      options.push({
        label,
        value,
        price,
        isChild,
        groupKey,
        groupLabel,
        groupOrder,
        groupStep,
      });
    });

    log(debug, "Created structured option objects", options);
    return options;
  },

  /**
   * Renders options list with grouped structure and styling
   * Generates HTML for displaying configuration summary with Bootstrap styling
   *
   * @param {Object} configData - Configuration data to render
   * @param {boolean} [debug=false] - Enable debug logging
   * @returns {string} Generated HTML string for options display
   *
   * @example
   * const html = RenderUtils.renderOptionsList(configData, true);
   * $("#summary-container").html(html);
   */
  renderOptionsList(configData, debug = false) {
    log(debug, "Rendering options list from configuration", configData);
    const options = this.createOptionObjects(configData, debug);

    // Group options by their logical groups
    const groups = {};
    options.forEach(opt => {
      if (!groups[opt.groupKey]) {
        groups[opt.groupKey] = {
          order: opt.groupOrder,
          step: opt.groupStep,
          label: opt.groupLabel,
          options: [],
        };
      }
      groups[opt.groupKey].options.push(opt);
    });

    // Sort groups by step (primary) and order (secondary)
    const sortedGroups = Object.entries(groups)
      .sort((a, b) => {
        const stepDiff = a[1].step - b[1].step;
        return stepDiff !== 0 ? stepDiff : a[1].order - b[1].order;
      })
      .map(([_, group]) => group);

    let html = "";

    // Generate grouped HTML structure
    sortedGroups.forEach(group => {
      log(debug, "Rendering group", group.label, group.options);
      if (!group.options.length) return;

      // Group header
      html += `
        <div class="col-12 px-0">
          <h6>${group.label}</h6>
          <ul class="list-group list-group-flush">
      `;

      // Group options
      group.options.forEach(opt => {
        const priceDisplay = opt.price ? PriceUtils.formatPrice(opt.price, debug) : "";
        const itemClass = opt.isChild
          ? "list-group-item list-group-item-child text-muted small mt-n2"
          : "list-group-item";

        html += `
          <li class="${itemClass}">
            <span class="row g-1">
              <span class="col-auto fw-medium">${opt.label}:</span>
              <span class="col text-truncate">${opt.value}${priceDisplay}</span>
            </span>
          </li>
        `;
      });

      html += `</ul></div>`;
    });

    log(debug, "Generated complete options list HTML", html);
    return html;
  },
};

/**
 * Modal Management System
 *
 * Handles creation and management of modal dialogs for various states
 * including success, error, validation, and informational modals.
 *
 * @namespace ModalSystem
 */
const ModalSystem = {
  /**
   * Centralized modal text messages
   * Provides consistent messaging across the application
   */
  texts: {
    info: '<span class="text-muted"><i class="fa-sharp fa-light fa-info-circle me-2"></i>Es handelt sich um Ihre Konfiguration als Code aus einem vorherigen Besuch.</span>',
    empty:
      '<span class="text-danger"><i class="fa-sharp fa-light fa-exclamation-circle me-2"></i>Bitte geben Sie einen Code ein.</span>',
    invalid:
      '<span class="text-danger"><i class="fa-sharp fa-light fa-exclamation-circle me-2"></i>Der Code muss genau 6 Zeichen haben.</span>',
    notFound:
      '<span class="text-danger"><i class="fa-sharp fa-light fa-exclamation-circle me-2"></i>Keine Konfiguration gefunden. Haben Sie den Code richtig eingegeben?</span>',
    success: '<span class="text-success"><i class="fa-sharp fa-light fa-sparkles me-2"></i>Erfolg!</span>',
  },

  /**
   * Generates context-sensitive text messages based on parent element styling
   * Adjusts colors for dark backgrounds (text-bg-dark class)
   *
   * @param {string} type - Message type (info/empty/invalid/notFound/success)
   * @param {jQuery|string} $context - Context element to check for dark background
   * @param {boolean} [debug=false] - Enable debug logging
   * @returns {string} HTML string with appropriate styling
   *
   * @example
   * ModalSystem.getContextualText('info', $('#homepage-form'));
   * // Returns white text if parent has text-bg-dark class
   */
  getContextualText(type, $context, debug = false) {
    $context = $($context);
    const isDarkBackground = $context.closest(".text-bg-dark").length > 0;

    log(debug, "Generating contextual text", {
      type,
      isDarkBackground,
      contextSelector: $context.selector,
    });

    const messages = {
      info: {
        light:
          '<span class="text-muted"><i class="fa-sharp fa-light fa-info-circle me-2"></i>Es handelt sich um Ihre Konfiguration als Code aus einem vorherigen Besuch.</span>',
        dark: '<span class="text-light"><i class="fa-sharp fa-light fa-info-circle me-2"></i>Es handelt sich um Ihre Konfiguration als Code aus einem vorherigen Besuch.</span>',
      },
      empty: {
        light:
          '<span class="text-danger"><i class="fa-sharp fa-light fa-exclamation-circle me-2"></i>Bitte geben Sie einen Code ein.</span>',
        dark: '<span class="text-warning"><i class="fa-sharp fa-light fa-exclamation-circle me-2"></i>Bitte geben Sie einen Code ein.</span>',
      },
      invalid: {
        light:
          '<span class="text-danger"><i class="fa-sharp fa-light fa-exclamation-circle me-2"></i>Der Code muss genau 6 Zeichen haben.</span>',
        dark: '<span class="text-warning"><i class="fa-sharp fa-light fa-exclamation-circle me-2"></i>Der Code muss genau 6 Zeichen haben.</span>',
      },
      notFound: {
        light:
          '<span class="text-danger"><i class="fa-sharp fa-light fa-exclamation-circle me-2"></i>Keine Konfiguration gefunden. Haben Sie den Code richtig eingegeben?</span>',
        dark: '<span class="text-warning"><i class="fa-sharp fa-light fa-exclamation-circle me-2"></i>Keine Konfiguration gefunden. Haben Sie den Code richtig eingegeben?</span>',
      },
      success: {
        light: '<span class="text-success"><i class="fa-sharp fa-light fa-sparkles me-2"></i>Erfolg!</span>',
        dark: '<span class="text-success"><i class="fa-sharp fa-light fa-sparkles me-2"></i>Erfolg!</span>',
      },
    };

    const messageSet = messages[type] || messages.info;
    const selectedMessage = isDarkBackground ? messageSet.dark : messageSet.light;

    log(debug, "Selected contextual message", {
      type,
      isDarkBackground,
      message: selectedMessage,
    });
    return selectedMessage;
  },

  /**
   * Creates base modal with common configuration options
   * Provides default settings that can be overridden for specific use cases
   *
   * @param {Object} options - Modal configuration options, compatible with createModal
   * @param {string} options.title - Modal title
   * @param {string} options.body - Modal body HTML
   * @param {Array<Object>} [options.footer] - Footer button configuration array (new structure)
   * @param {string} [options.size="md"] - Modal size (sm/md/lg/xl)
   * @param {boolean} [debug=false] - Enable debug logging
   * @returns {Object} Created modal instance
   *
   * @example
   * ModalSystem.createBaseModal({
   *   title: "Confirmation",
   *   body: "<p>Are you sure?</p>",
   *   footer: [
   *     { text: "No", class: "btn-secondary", dismiss: true },
   *     { text: "Yes", class: "btn-primary", onClick: () => console.log("Confirmed") }
   *   ]
   * });
   */
  createBaseModal(options, debug = false) {
    log(debug, "Creating modal with title:", options.title, "Full options:", options);
    const defaults = {
      size: "md",
      // createModal will use its own default footer if options.footer is not provided.
      // createModal's default footer is:
      // footer = [
      //   { text: "Schließen", class: "btn-dark", dismiss: true, key: "dismiss" },
      // ]
    };

    // Merge provided options with defaults.
    // The 'options' parameter should already be structured correctly for createModal,
    // especially the 'footer' array.
    const modalSettings = { ...defaults, ...options };

    // Warn if an old 'onConfirm' was passed directly and not integrated into the new 'footer' array.
    if (options.onConfirm && typeof options.onConfirm === "function") {
      const confirmButtonWithHandler = modalSettings.footer?.find(btn => btn.onClick === options.onConfirm);
      if (!confirmButtonWithHandler) {
        const warningMsg =
          "[ConfigLoader] createBaseModal: 'onConfirm' was provided but no corresponding button in 'footer' array uses it. This 'onConfirm' might be ignored. Please update the calling function to use the new footer structure correctly.";
        log(debug, warningMsg);
        console.warn(warningMsg);
      }
    }

    // Remove onConfirm from the options passed to createModal, as it's now handled within the footer array.
    const { onConfirm, ...finalOptionsForCreateModal } = modalSettings;

    return createModal(finalOptionsForCreateModal);
  },

  /**
   * Shows validation modal for product mismatches
   * Handles cases where loaded configuration belongs to different product
   *
   * @param {Function} onConfirmCallback - Callback for confirmation action
   * @param {string} [productUrl=null] - URL of the correct product
   * @param {string} [productTitle=null] - Title of the correct product
   * @param {boolean} [debug=false] - Enable debug logging
   *
   * @example
   * ModalSystem.showValidationModal(
   *   () => applyConfig(),
   *   "/product/correct-mirror",
   *   "Correct Mirror Product"
   * );
   */
  showValidationModal(onConfirmCallback, productUrl = null, productTitle = null, debug = false) {
    log(debug, "Product mismatch detected", { productUrl, productTitle });

    const modalBody = `
      <div class="alert alert-warning bg-body text-body mb-0" role="alert">
        <p>Der geladene Code gehört zu einem anderen Produkt. Möchten Sie die Konfiguration trotzdem laden?</p>
        ${
          productUrl && productTitle
            ? `
              <hr class="my-3" />
              <p class="mb-0">
                <span class="d-block mb-2">Oder zum korrekten Produkt wechseln:</span>
                <a href="${productUrl}" target="_blank" rel="noopener" class="alert-link fw-semibold">
                  <i class="fa-xs fa-sharp fa-up-right-from-square me-2"></i>${productTitle}
                </a>
              </p>
            `
            : ""
        }
      </div>
    `;

    this.createBaseModal(
      {
        title: "Abweichendes Produkt",
        body: modalBody,
        footer: [
          { text: "Abbrechen", class: "btn-secondary", dismiss: true },
          {
            text: "Ja, trotzdem laden",
            class: "btn-primary",
            onClick: onConfirmCallback,
            dismiss: true,
          },
        ],
        size: "md",
      },
      debug
    );
  },

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
    log(debug, "Displaying success modal", {
      configData,
      successMsg,
      forcedLoad,
      showSummaryAccordion,
    });

    // Apply configuration data to form
    DataUtils.applyConfigData(configData, debug);

    let modalBody = `
      <div class="alert alert-success d-flex align-items-center mb-0" role="alert">
        <span class="row g-4">
          <span class="col-auto"><i class="fa-sharp fa-light fa-check"></i></span>
          <span class="col border-start border-success-subtle">${successMsg}</span>
        </span>
      </div>
    `;

    // Add optional configuration summary accordion
    if (showSummaryAccordion) {
      modalBody += `
        <div class="accordion" id="loadedOptionsAccordion">
          <div class="accordion-item">
            <h5 class="accordion-header" id="headingOptions">
              <button class="accordion-button collapsed bg-secondary-subtle" type="button" 
                  data-bs-toggle="collapse" data-bs-target="#collapseOptions" 
                  aria-expanded="false" aria-controls="collapseOptions">
                Zusammenfassung anzeigen
              </button>
            </h5>
            <div id="collapseOptions" class="accordion-collapse collapse" 
                aria-labelledby="headingOptions" data-bs-parent="#loadedOptionsAccordion">
              <div class="accordion-body bg-secondary-subtle row g-3 p-3 mx-0">
                ${RenderUtils.renderOptionsList(configData, debug)}
              </div>
            </div>
          </div>
        </div>
      `;
    }

    this.createBaseModal(
      {
        title: "Konfiguration angewendet",
        body: modalBody,
        footer: [
          // Using a single "Schließen" button, similar to createModal's default
          { text: "Schließen", class: "btn-dark", dismiss: true },
        ],
        size: "md",
      },
      debug
    );
  },

  /**
   * Shows error modal with customizable message and title
   * Provides consistent error display across the application
   *
   * @param {string} errorMsg - Error message to display
   * @param {string} [customTitle='Fehler'] - Custom modal title
   * @param {boolean} [debug=false] - Enable debug logging
   *
   * @example
   * ModalSystem.showErrorModal("Network connection failed", "Connection Error");
   */
  showErrorModal(errorMsg, customTitle = "Fehler", debug = false) {
    log(debug, "Displaying error modal", { errorMsg, customTitle });

    const modalBody = `
      <div class="alert alert-danger d-flex align-items-center mb-0" role="alert">
        <i class="fa-sharp fa-light fa-exclamation-triangle me-2"></i>
        <div>${errorMsg}</div>
      </div>
    `;

    this.createBaseModal(
      {
        title: customTitle,
        body: modalBody,
        // No footer explicitly passed, so createModal's default footer will be used:
        // [{ text: "Schließen", class: "btn-dark", dismiss: true }]
        size: "md",
      },
      debug
    );
  },

  /**
   * Sets form validation text based on message type
   * Updates form help text with appropriate styling and icons
   *
   * @param {string} [type='info'] - Message type (info/empty/invalid/notFound/success)
   * @param {boolean} [debug=false] - Enable debug logging
   *
   * @example
   * ModalSystem.setConfigCodeFormText('success');
   * ModalSystem.setConfigCodeFormText('invalid');
   */
  setConfigCodeFormText(type = "info", debug = false) {
    log(debug, "Setting form validation text", type);
    $(".form-text").html(this.texts[type] || this.texts.info);
  },
};

/**
 * Configuration Application Logic
 *
 * Handles the application of loaded configurations with product validation
 * and user confirmation for mismatched products.
 *
 * @namespace ConfigApplication
 */
const ConfigApplication = {
  /**
   * Applies configuration with product mismatch handling
   * Validates product compatibility and handles user decisions
   *
   * @param {Object} configData - Configuration data to apply
   * @param {string} successMsg - Success message for successful application
   * @param {number} remoteProductId - Product ID from loaded configuration
   * @param {number} currentProductId - Current page product ID
   * @param {string} [productUrl=null] - URL of correct product
   * @param {string} [productTitle=null] - Title of correct product
   * @param {boolean} [debug=false] - Enable debug logging
   *
   * @example
   * ConfigApplication.doApply(
   *   configData,
   *   "Configuration applied!",
   *   123,
   *   456,
   *   "/product/correct",
   *   "Correct Product"
   * );
   */
  doApply(
    configData,
    successMsg,
    remoteProductId,
    currentProductId,
    productUrl = null,
    productTitle = null,
    debug = false
  ) {
    log(debug, "Applying configuration with product validation", {
      configData,
      successMsg,
      remoteProductId,
      currentProductId,
      productUrl,
      productTitle,
    });

    // Check for product mismatch
    if (currentProductId && remoteProductId && remoteProductId !== currentProductId) {
      // Show validation modal for product mismatch
      ModalSystem.showValidationModal(
        () => {
          // User confirmed to load despite mismatch
          const $modal = $(".modal:visible");
          $modal.one("hidden.bs.modal", () => {
            ModalSystem.showSuccessModal(configData, successMsg, true, false, debug);
          });
          $modal.modal("hide");
        },
        productUrl,
        productTitle,
        debug
      );
    } else {
      // Products match or no validation needed - apply directly
      ModalSystem.showSuccessModal(configData, successMsg, false, false, debug);
    }
  },
};

/**
 * Configuration Code Input Utilities
 *
 * Provides shared utilities for configuration code input validation,
 * feedback management, and user interface updates.
 *
 * @namespace ConfigCodeInputUtils
 */
const ConfigCodeInputUtils = {
  /**
   * Validates configuration code format and requirements
   * Checks for empty codes and minimum length requirements
   *
   * @param {string} code - Code to validate
   * @param {boolean} [debug=false] - Enable debug logging
   * @returns {string|null} Error type string or null if valid
   *
   * @example
   * const error = ConfigCodeInputUtils.validate("ABC123");
   * if (error) {
   *   console.log("Validation failed:", error);
   * }
   */
  validate(code, debug = false) {
    log(debug, "Validating configuration code", code);
    if (!code) return "empty";
    if (code.length < 6) return "invalid";
    return null;
  },

  /**
   * Sets input field UI state and feedback text
   * Manages input validation styling and help text for both modal and main forms
   *
   * @param {string|jQuery} $input - Input selector or jQuery object
   * @param {string} errorType - Error type ("empty", "invalid", "notFound") or null for success
   * @param {boolean} [isModal=false] - Whether this is a modal input field
   * @param {boolean} [debug=false] - Enable debug logging
   *
   * @example
   * ConfigCodeInputUtils.setFeedback($("#code-input"), "invalid", false, true);
   * ConfigCodeInputUtils.setFeedback($("#modal-input"), null, true); // Success state
   */ setFeedback($input, errorType, isModal = false, debug = false) {
    log(debug, "Setting input feedback", {
      input: $input && $input.selector,
      errorType,
      isModal,
    });
    $input = $($input);

    // Check if we're in a dark background context
    const isDarkBackground = $input.closest(".text-bg-dark").length > 0;

    // Update input validation styling
    if (errorType) {
      $input.addClass("is-invalid");

      // Apply dark mode invalid styling if needed
      if (isDarkBackground) {
        $input.css({
          "--bs-form-invalid-border-color": "var(--bs-warning)",
          "--bs-danger-rgb": "var(--bs-warning-rgb)",
        });
        log(debug, "Applied dark mode invalid styling", {
          isDarkBackground: true,
        });
      }
    } else {
      $input.removeClass("is-invalid");

      // Reset CSS custom properties when valid
      if (isDarkBackground) {
        $input.css({
          "--bs-form-invalid-border-color": "",
          "--bs-danger-rgb": "",
        });
        log(debug, "Reset dark mode invalid styling", {
          isDarkBackground: true,
        });
      }
    }

    // Generate contextual text content based on background
    const textContent = ModalSystem.getContextualText(errorType || "info", $input, debug);

    // Handle feedback text display based on context
    if (isModal) {
      // Modal form - target the modal's .form-text specifically
      const $modalFormText = $input.closest(".modal-body").find(".form-text");
      if ($modalFormText.length) {
        $modalFormText.html(textContent);
        log(debug, "Updated modal form feedback text", {
          errorType,
          textContent,
          found: true,
        });
      } else {
        log(debug, "Modal form text element not found", {
          selector: $input.closest(".modal-body").find(".form-text"),
        });
      }
    } else {
      // Main form - try multiple selectors to find the form text element
      let $mainFormText = $input.closest(".input-group").siblings(".form-text");

      // Fallback strategies for finding form text element
      if (!$mainFormText.length) {
        $mainFormText = $input.closest(".input-group").parent().find(".form-text");
      }

      if (!$mainFormText.length) {
        $mainFormText = $input.parent().siblings(".form-text");
      }

      if (!$mainFormText.length) {
        $mainFormText = $input.closest("form, .form-container, .configurator-section").find(".form-text");
      }

      if ($mainFormText.length) {
        $mainFormText.html(textContent);
        log(debug, "Updated main form feedback text", {
          errorType,
          textContent,
          found: true,
          selector: $mainFormText.selector,
        });
      } else {
        log(debug, "Main form text element not found - attempting fallback approach", {
          inputId: $input.attr("id"),
          parentClasses: $input.parent().attr("class"),
          containerClasses: $input.closest(".input-group").attr("class"),
        });

        // Last resort: find form text by ID relationship
        let $formText = $(`#${$input.attr("id")}`)
          .closest(".input-group")
          .next(".form-text");
        if (!$formText.length) {
          $formText = $(`#${$input.attr("id")}`)
            .parent()
            .next(".form-text");
        }
        if ($formText.length) {
          $formText.html(textContent);
          log(debug, "Successfully found form text with fallback method", {
            found: true,
          });
        } else {
          log(debug, "Could not find any form text element for main form");
        }
      }
    }
  },
};

/**
 * Event Handlers
 *
 * Manages all event listeners and user interactions for configuration loading.
 * Handles both main form and modal form interactions with proper validation.
 *
 * @namespace EventHandlers
 */
const EventHandlers = {
  /**
   * Initializes all event handlers for configuration loading
   * Sets up input validation, button clicks, and keyboard interactions
   *
   * @param {boolean} [debug=false] - Enable debug logging
   *
   * @example
   * EventHandlers.init(true); // Initialize with debug logging
   */
  init(debug = false) {
    log(debug, "Initializing event handlers");

    // Initialize main form with informational text
    const $mainInput = $("#product-configurator-configcode-input");
    if ($mainInput.length) {
      ConfigCodeInputUtils.setFeedback($mainInput, null, false, debug);
      log(debug, "Initialized main form with info text");
    }

    // Initialize homepage form with informational text
    const $homepageInput = $("#homepage-configcode-input");
    if ($homepageInput.length) {
      ConfigCodeInputUtils.setFeedback($homepageInput, null, false, debug);
      log(debug, "Initialized homepage form with info text");
    }

    // Main form input validation event
    $("#product-configurator-configcode-input")
      .off("input.configcode")
      .on("input.configcode", function () {
        ConfigCodeInputUtils.setFeedback(this, null, false, debug);
      });

    // Homepage form input validation event
    $("#homepage-configcode-input")
      .off("input.configcode")
      .on("input.configcode", function () {
        ConfigCodeInputUtils.setFeedback(this, null, false, debug);
      });

    // Modal form input validation event
    $("#modal-configcode-input")
      .off("input.configcode")
      .on("input.configcode", function () {
        ConfigCodeInputUtils.setFeedback(this, null, true, debug);
      });

    // Enter key handling for main form
    $("#product-configurator-configcode-input")
      .off("keydown.configcode")
      .on("keydown.configcode", function (event) {
        if (event.key === "Enter" || event.keyCode === 13) {
          event.preventDefault();
          $("#product-configurator-configcode-load").trigger("click");
        }
      });

    // Enter key handling for homepage form
    $("#homepage-configcode-input")
      .off("keydown.configcode")
      .on("keydown.configcode", function (event) {
        if (event.key === "Enter" || event.keyCode === 13) {
          event.preventDefault();
          $("#homepage-configcode-load").trigger("click");
        }
      });

    // Enter key handling for modal form
    $("#modal-configcode-input")
      .off("keydown.configcode")
      .on("keydown.configcode", function (event) {
        if (event.key === "Enter" || event.keyCode === 13) {
          event.preventDefault();
          EventHandlers.handleModalConfigLoad(debug);
        }
      });

    // Load configuration button click handler
    $("#product-configurator-configcode-load")
      .off("click.configcode")
      .on("click.configcode", function () {
        EventHandlers.handleMainConfigLoad(debug);
      });

    // Homepage load configuration button click handler
    $("#homepage-configcode-load")
      .off("click.configcode")
      .on("click.configcode", function () {
        EventHandlers.handleHomepageConfigLoad(debug);
      });

    // Site-wide configuration code button
    $(document)
      .off("click.configcode", "#site-conficode-btn")
      .on("click.configcode", "#site-conficode-btn", function () {
        EventHandlers.handleSiteConfigCode(debug);
      });
  },

  /**
   * Handles main (non-modal) configuration code loading
   * Validates input, sends AJAX request, and processes response
   *
   * @param {boolean} [debug=false] - Enable debug logging
   *
   * @example
   * EventHandlers.handleMainConfigLoad(true);
   */
  handleMainConfigLoad(debug = false) {
    const $input = $("#product-configurator-configcode-input");
    const code = $input.val().trim();
    log(debug, "Main configuration load triggered", code);

    // Validate input code
    const errorType = ConfigCodeInputUtils.validate(code, debug);
    if (errorType) {
      log(debug, "Validation error detected", errorType);
      ConfigCodeInputUtils.setFeedback($input, errorType, false, debug);
      return;
    }
    ConfigCodeInputUtils.setFeedback($input, null, false, debug);

    // Get and prepare load button
    const $button = $("#product-configurator-configcode-load");
    if ($button.length === 0) {
      log(debug, "Load button not found");
      return;
    }

    // Add loading state to button
    try {
      $button
        .append(
          `<span class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true" id="loadSpinner"></span>`
        )
        .prop("disabled", true);
    } catch (error) {
      log(debug, "Error adding loading spinner", error);
      $button.prop("disabled", true);
    }

    // Send AJAX request to load configuration
    log(debug, "Sending AJAX request for configuration", code);
    $.ajax({
      url: myAjaxData.ajaxUrl,
      method: "POST",
      data: {
        action: "load_config",
        security: myAjaxData.nonce,
        config_code: code,
      },
      success(response) {
        log(debug, "AJAX request successful", response);
        if (response.success) {
          const { msg, product_id, config_data, product_url, product_title } = response.data;
          const remoteProductId = DataUtils.getProductId(product_id, debug);
          const currentProductId = DataUtils.getProductId(myAjaxData.productId, debug);
          ConfigApplication.doApply(
            config_data,
            msg,
            remoteProductId,
            currentProductId,
            product_url,
            product_title,
            debug
          );
        } else {
          log(debug, "AJAX logical error", response.data?.msg);
          ConfigCodeInputUtils.setFeedback($input, "notFound", false, debug);
        }
      },
      error(xhr) {
        log(debug, "AJAX request failed", xhr);
        ConfigCodeInputUtils.setFeedback($input, "invalid", false, debug);
      },
      complete() {
        log(debug, "AJAX request completed");
        $("#loadSpinner").remove();
        $button.prop("disabled", false);
      },
    });
  },
  /**
   * Handles homepage configuration code loading
   * Similar to main config load but redirects to code URL
   *
   * @param {boolean} [debug=false] - Enable debug logging
   *
   * @example
   * EventHandlers.handleHomepageConfigLoad(true);
   */ handleHomepageConfigLoad(debug = false) {
    const $input = $("#homepage-configcode-input");
    const code = $input.val().trim();
    log(debug, "Homepage configuration load triggered", code);

    // Validate input code
    const errorType = ConfigCodeInputUtils.validate(code, debug);
    if (errorType) {
      log(debug, "Homepage validation error detected", errorType);
      ConfigCodeInputUtils.setFeedback($input, errorType, false, debug);
      return;
    }
    ConfigCodeInputUtils.setFeedback($input, null, false, debug);

    // Get and prepare load button
    const $button = $("#homepage-configcode-load");
    if ($button.length === 0) {
      log(debug, "Homepage load button not found");
      return;
    }

    // Store original button text and set loading state
    const originalText = $button.text();
    $button
      .html(
        '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Wird geprüft...'
      )
      .prop("disabled", true);

    // Send AJAX request to validate configuration exists
    log(debug, "Sending homepage AJAX request for configuration", code);
    $.ajax({
      url: myAjaxData.ajaxUrl,
      method: "POST",
      data: {
        action: "load_config",
        security: myAjaxData.nonce,
        config_code: code,
      },
      success(response) {
        log(debug, "Homepage AJAX request successful", response);
        if (response.success) {
          // Configuration exists - show success state then redirect
          $button
            .html(
              '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Produkt wird geladen...'
            )
            .removeClass("btn-primary")
            .addClass("btn-success");

          // Show brief success feedback before redirect
          setTimeout(() => {
            const codeUrl = `${window.location.origin}/code/${code}`;
            log(debug, "Redirecting to configuration URL from homepage", codeUrl);
            window.location.href = codeUrl;
          }, 800);
        } else {
          log(debug, "Homepage AJAX logical error", response.data?.msg);
          ConfigCodeInputUtils.setFeedback($input, "notFound", false, debug);
          // Reset button to original state
          $button.html(originalText).prop("disabled", false);
        }
      },
      error(xhr) {
        log(debug, "Homepage AJAX request failed", xhr);
        ConfigCodeInputUtils.setFeedback($input, "invalid", false, debug);
        // Reset button to original state
        $button.html(originalText).prop("disabled", false);
      },
    });
  },

  /**
   * Handles site-wide configuration code modal creation and setup
   * Creates modal dialog for loading configurations from any page
   *
   * @param {boolean} [debug=false] - Enable debug logging
   *
   * @example
   * EventHandlers.handleSiteConfigCode(true);
   */
  handleSiteConfigCode(debug = false) {
    log(debug, "Site-wide configuration code modal triggered");

    const modalBody = `
      <p>Geben Sie Ihren 6-stelligen Konfigurationscode ein:</p>
      <div class="position-relative">
        <label for="modal-configcode-input" class="visually-hidden">
          Konfiguration als Code
        </label>
        <input type="text"
            class="form-control form-control-lg border-primary-subtle"
            style="padding-left: 2.75rem"
            id="modal-configcode-input"
            placeholder="z.B. ABC123"
            maxlength="6"
            autocomplete="off"/>
        <i class="fa-sharp fa-light fa-heart position-absolute start-0 top-50 translate-middle-y ms-3" style="z-index: 2;"></i>
      </div>
      <div class="form-text mt-2"></div>
    `;

    // Create modal with configuration
    const modal = ModalSystem.createBaseModal(
      {
        title: "Konfiguration laden",
        body: modalBody,
        footer: [
          { text: "Abbrechen", class: "btn-secondary", dismiss: true },
          {
            text: "Konfiguration laden",
            class: "btn-primary", // confirmClass from old structure is now part of 'class'
            onClick: () => EventHandlers.handleModalConfigLoad(debug),
            dismiss: false, // Keep modal open during AJAX load in handleModalConfigLoad
          },
        ],
        size: "md",
        // onConfirm is now handled by the onClick in the footer array
      },
      debug
    );

    // Set initial form state
    ModalSystem.setConfigCodeFormText("info", debug);

    // Auto-focus input when modal opens
    $(document).one("shown.bs.modal", ".modal", () => {
      setTimeout(() => $("#modal-configcode-input").trigger("focus"), 150);
    });

    // Input validation handler
    $("#modal-configcode-input").on("input", function () {
      $(this).removeClass("is-invalid");
      ModalSystem.setConfigCodeFormText("info", debug);
    });
  },

  /**
   * Handles modal configuration loading with redirect functionality
   * Processes configuration codes from modal and redirects to code URL
   *
   * @param {boolean} [debug=false] - Enable debug logging
   *
   * @example
   * EventHandlers.handleModalConfigLoad(true);
   */
  handleModalConfigLoad(debug = false) {
    const $input = $("#modal-configcode-input");
    const code = $input.val().trim();
    log(debug, "Modal configuration load triggered", code);

    // Validate input code
    const errorType = ConfigCodeInputUtils.validate(code, debug);
    if (errorType) {
      log(debug, "Modal validation error", errorType);
      ConfigCodeInputUtils.setFeedback($input, errorType, true, debug);
      return;
    }
    ConfigCodeInputUtils.setFeedback($input, null, true, debug);

    // Set modal loading state
    const $modal = $(".modal:visible");
    $modal
      .find(".modal-footer .btn-primary")
      .html(
        '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Wird geladen...'
      )
      .prop("disabled", true);

    // Send AJAX request for modal configuration loading
    log(debug, "Sending AJAX request from modal", code);
    $.ajax({
      url: myAjaxData.ajaxUrl,
      method: "POST",
      data: {
        action: "load_config",
        security: myAjaxData.nonce,
        config_code: code,
      },
      success(response) {
        log(debug, "Modal AJAX request successful", response);
        if (response.success) {
          const { config_data } = response.data;

          // Always redirect to code URL for modal loads
          const codeUrl = `${window.location.origin}/code/${code}`;
          log(debug, "Redirecting to configuration URL", codeUrl);
          window.location.href = codeUrl;
        } else {
          // Handle error response
          const errorMsg = response.data?.msg || "Ein unbekannter Fehler ist aufgetreten.";
          log(debug, "Modal AJAX logical error", errorMsg);

          $modal.find(".modal-footer .btn-primary").html("Konfiguration laden").prop("disabled", false);
          ConfigCodeInputUtils.setFeedback($input, "notFound", true, debug);
        }
      },
      error(xhr) {
        log(debug, "Modal AJAX request failed", xhr);
        ConfigCodeInputUtils.setFeedback($input, "invalid", true, debug);
        $modal.find(".modal-footer .btn-primary").html("Konfiguration laden").prop("disabled", false);
      },
    });
  },
};

/**
 * Module Initialization
 *
 * Initialize all event handlers when DOM is ready with configurable debug mode.
 */
$(document).ready(() => {
  // Debug mode configuration - set to true for development
  const DEBUG_MODE = false;
  EventHandlers.init(DEBUG_MODE);
});

/**
 * Future Enhancement Ideas:
 *
 * 0. Optimization:
 *   - Activate summary (RenderUtils and PriceUtils), functions are not in use yet
 *
 * 1. Configuration validation:
 *    - Validate configuration data structure
 *    - Check for required fields
 *    - Verify data integrity
 *
 * 2. Offline support:
 *    - Cache configurations locally
 *    - Offline loading capabilities
 *    - Sync when online
 *
 * 3. Enhanced error handling:
 *    - Retry mechanisms for failed requests
 *    - Better error categorization
 *    - User-friendly error messages
 *
 * 4. Performance optimizations:
 *    - Request debouncing
 *    - Configuration data compression
 *    - Lazy loading of large configurations
 *
 * 5. Analytics integration:
 *    - Track configuration loading success rates
 *    - Monitor popular configurations
 *    - User behavior analysis
 *
 * 6. Security improvements (CRITICAL):
 *    - Escape user input in template literals to prevent XSS
 *    - Sanitize HTML before using .html() method
 *    - Validate fieldName and fieldValue before DOM queries
 *    - Add Content Security Policy headers
 *
 * 7. Dependency management:
 *    - Add existence checks for myAjaxData before usage
 *    - Verify createModal function availability
 *    - Implement graceful degradation for missing jQuery
 *    - Add feature detection for required browser APIs
 *
 * 8. Memory management:
 *    - Implement proper modal cleanup/destruction
 *    - Remove all event listeners on component destroy
 *    - Clear timers and intervals properly
 *    - Add WeakMap for DOM element references
 *
 * 9. Race condition prevention:
 *    - Cancel previous AJAX requests before new ones
 *    - Implement request queuing system
 *    - Add loading state management
 *    - Prevent multiple simultaneous form submissions
 *
 * 10. Error handling robustness:
 *     - Distinguish between network and server errors
 *     - Add try-catch blocks around DOM manipulations
 *     - Implement fallback strategies for failed operations
 *     - Add comprehensive error logging and reporting
 *
 * 11. DOM selector reliability:
 *     - Cache frequently used selectors
 *     - Use data attributes instead of complex selectors
 *     - Implement element existence validation
 *     - Add fallback element discovery methods
 *
 * 12. Input validation enhancement:
 *     - Add strict type checking for configData
 *     - Validate parseInt results against expected ranges
 *     - Implement schema validation for configuration objects
 *     - Add input sanitization before processing
 *
 * 13. Performance optimizations:
 *     - Implement DOM query caching
 *     - Add input debouncing for real-time validation
 *     - Use DocumentFragment for bulk DOM updates
 *     - Optimize frequent selector queries
 *
 * 14. Code maintainability:
 *     - Break down large functions into smaller units
 *     - Reduce code duplication between handlers
 *     - Make debug mode configurable via data attributes
 *     - Add comprehensive JSDoc type definitions
 *
 * 15. Accessibility improvements:
 *     - Enhance modal focus management and keyboard navigation
 *     - Add ARIA labels to dynamically created elements
 *     - Implement proper screen reader announcements
 *     - Add high contrast mode support
 */
