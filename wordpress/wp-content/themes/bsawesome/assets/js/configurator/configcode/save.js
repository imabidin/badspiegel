/**
 * Product Configurator - Configuration Saving Module
 *
 * This module handles saving product configurations and generating shareable codes.
 * It provides functionality for collecting form data, generating configuration codes,
 * and sharing configurations via multiple channels.
 *
 * Features:
 * - Complete form data collection and serialization
 * - Configuration code generation with server-side storage
 * - Success modal with sharing options (email, WhatsApp)
 * - Direct link generation for immediate access
 * - Clipboard functionality for easy sharing
 * - Debug logging for development support
 * - Automatic favourites integration
 *
 * @version 2.3.0
 * @package Configurator
 */

document.addEventListener("DOMContentLoaded", function () {
  /**
   * Data Collection Utilities
   *
   * Handles gathering and serialization of form data from various input types
   * including radio buttons, checkboxes, text inputs, and select elements.
   *
   * @namespace DataCollection
   */

  /**
   * Gathers all configuration data from form elements
   * Systematically collects data from all configurator form inputs and
   * structures it for server-side processing and storage.
   *
   * @param {boolean} [debug=false] - Enable debug logging for troubleshooting
   * @returns {Object} Collected configuration data with type information
   *
   * @example
   * const config = gatherConfigData(true); // Debug mode enabled
   * // Returns: { "size": { value: "large", type: "radio" }, "custom_text": { value: "Hello", type: "input" } }
   */
  function gatherConfigData(debug = false) {
    const configData = {};

    // Process radio button selections
    $("input.option-radio:checked").each(function () {
      const name = $(this).attr("name");
      const val = $(this).val();
      configData[name] = { value: val, type: "radio" };
    });

    // Process checkbox selections (support multiple values)
    $("input.option-check:checked").each(function () {
      const name = $(this).attr("name");
      if (!configData[name]) {
        configData[name] = { value: [], type: "checkbox" };
      }
      configData[name].value.push($(this).val());
    });

    // Process text and number inputs
    $("input.option-input").each(function () {
      const name = $(this).attr("name");
      const val = $(this).val();
      configData[name] = { value: val, type: "input" };
    });

    // Process select dropdown selections
    $("select.option-select").each(function () {
      const name = $(this).attr("name");
      const val = $(this).val();
      configData[name] = { value: val, type: "select" };
    });

    if (debug) {
      console.log("Configuration data collected:", configData);
    }

    return configData;
  }

  /**
   * Event Handlers
   *
   * Manages all user interactions including button clicks, sharing functionality,
   * and clipboard operations for the configuration saving system.
   *
   * @namespace EventHandlers
   */

  /**
   * Configures direct link functionality and clipboard copying
   * Sets up handlers for direct link input field and copy button interactions
   *
   * @param {string} directLink - The direct configuration access link
   *
   * @example
   * setupDirectLinkHandlers("https://example.com/code/ABC123");
   */
  function setupDirectLinkHandlers(directLink) {
    // Direct link copy button handler
    $(document)
      .off("click", "#copy-direct-link")
      .on("click", "#copy-direct-link", function () {
        const input = document.getElementById("direct-link-input");
        input.select();
        input.setSelectionRange(0, 99999); // For mobile device compatibility

        try {
          document.execCommand("copy");
          console.log("Direct link copied to clipboard");
        } catch (err) {
          console.error("Copy operation failed:", err);
        }
      });
  }

  /**
   * Sets up social sharing handlers with pre-formatted messages
   * Configures email and WhatsApp sharing with professional templates
   *
   * @param {string} code - The generated configuration code
   * @param {Object} productInfo - Product information object
   * @param {string} productInfo.title - Product title
   * @param {string} productInfo.price - Product price
   * @param {string} productInfo.deliveryTime - Delivery time information
   * @param {string} productInfo.directLink - Direct access link
   *
   * @example
   * setupShareHandlers("ABC123", {
   *   title: "Premium Mirror",
   *   price: "299,99 €",
   *   deliveryTime: "2-3 Wochen",
   *   directLink: "https://example.com/code/ABC123"
   * });
   */
  function setupShareHandlers(code, productInfo) {
    // Email sharing handler with professional template
    $(document)
      .off("click", "#configcode-share-mail")
      .on("click", "#configcode-share-mail", function () {
        const mailSubject = encodeURIComponent(
          `Badspiegel.de | Meine Konfiguration${productInfo.title ? " – " + productInfo.title : ""}`
        );

        // Comprehensive email body with all product details
        const mailBody = encodeURIComponent(
          `Meine persönliche Konfiguration für Badspiegel.de:\n\n` +
            `🪄 Direkter Link (klicken zum Öffnen):\n` +
            `${productInfo.directLink}\n\n` +
            `🔑 Oder Code eingeben: ${code}\n\n` +
            `📌 Produkt: ${productInfo.title || "Individueller Badspiegel oder Badmöbel"}\n\n` +
            (productInfo.price ? `💶 Preis: ${productInfo.price}\n` : "") +
            (productInfo.deliveryTime ? `⏱️ Lieferzeit: ${productInfo.deliveryTime}\n\n` : "") +
            `Mit diesem Code können Sie:\n` +
            `✓ Konfiguration sofort aufrufen\n` +
            `✓ Änderungen vornehmen\n` +
            `✓ Die Zusammenstellung optimieren\n\n` +
            `Viele Grüße\nIhr Badspiegel.de Team\n\n` +
            `🌟 Ihr Spezialist für Badspiegel & maßgefertigte Badmöbel\n\n` +
            `PS: Fragen? 📞 0231 / 550 33 204 | ✉️ service@badspiegel.de`
        );

        // Open email client with pre-filled content
        window.location.href = `mailto:?subject=${mailSubject}&body=${mailBody}`;
      });

    // WhatsApp sharing handler with concise message
    $(document)
      .off("click", "#configcode-share-whatsapp")
      .on("click", "#configcode-share-whatsapp", function () {
        const whatsappText = `Meine Konfiguration für Badspiegel.de:\n\n${productInfo.directLink}\n\n(Code: ${code})`;

        // Open WhatsApp with pre-filled message
        window.open(`https://wa.me/?text=${encodeURIComponent(whatsappText)}`, "_blank");
      });
  }

  /**
   * Generate configuration code via AJAX
   *
   * @param {Object} configData - Configuration data object
   * @returns {Promise} Promise that resolves with {code, directLink} or rejects with error
   */
  function generateConfigCode(configData) {
    return new Promise((resolve, reject) => {
      $.ajax({
        url: myAjaxData.ajaxUrl,
        method: "POST",
        data: {
          action: "save_config",
          security: myAjaxData.nonce,
          product_id: myAjaxData.productId,
          config_data: configData,
        },
        success(response) {
          if (response.success) {
            const code = response.data.generated_code;
            const directLink = `${window.location.origin}/code/${code}`;
            resolve({ code, directLink });
          } else {
            reject(new Error(response.data?.msg || "Konfiguration konnte nicht gespeichert werden"));
          }
        },
        error(xhr, status, error) {
          reject(new Error("Netzwerkfehler. Bitte versuchen Sie es erneut."));
        },
      });
    });
  }

  /**
   * Add configuration to favourites automatically
   *
   * @param {string} configCode - Generated configuration code
   * @param {number} productId - Product ID
   * @returns {Promise} Promise that resolves when favourite is added
   */
  function addConfigToFavourites(configCode, productId) {
    return new Promise((resolve, reject) => {
      $.ajax({
        url: myAjaxData.ajaxUrl,
        method: "POST",
        data: {
          action: "add_favourite_with_config",
          nonce: myAjaxData.favouriteNonce,
          product_id: productId,
          config_code: configCode,
        },
        success(response) {
          if (response.success) {
            console.log("Configuration added to favourites:", configCode);

            // Update favourites badge if function exists
            if (window.updateFavouritesBadgeDisplay) {
              window.updateFavouritesBadgeDisplay(response.data.count);
            }

            resolve(response.data);
          } else {
            console.warn("Could not add to favourites:", response.data?.message);
            // Don't reject - favourites addition is optional
            resolve({ added: false, message: response.data?.message });
          }
        },
        error(xhr, status, error) {
          console.warn("Favourites addition failed:", error);
          // Don't reject - favourites addition is optional
          resolve({ added: false, error: error });
        },
      });
    });
  }

  /**
   * Enhanced success modal with favourite confirmation
   *
   * @param {string} msg - Success message text to display
   * @param {string} code - Generated configuration code
   * @param {string} tooltip - Tooltip text for copy buttons
   * @param {boolean} addedToFavourites - Whether config was added to favourites
   */
  function showSuccessModal(msg, code, tooltip, addedToFavourites = false) {
    // Collect current product information for sharing
    const productInfo = {
      title: $(".product_title, .product-titel, .entry-title").first().text().trim(),
      price: $("#productConfiguroator .product-price").first().text().trim(),
      deliveryTime: $(".delivery-time-data").first().text().trim(),
      link: window.location.href,
      directLink: `${window.location.origin}/code/${code}`,
    };

    // Enhanced success message with favourite confirmation
    const successAlert = addedToFavourites
      ? `
      <div class="alert alert-success">
        <p class="mb-0">
          <span class="row g-4 align-items-center">
            <span class="col-auto"><i class="fa-sharp fa-light fa-check"></i></span>
            <span class="col border-start border-success-subtle">Konfiguration als Code gespeichert und zu Ihren Favoriten hinzugefügt!</span>
          </span>
        </p>
      </div>
    `
      : `
      <div class="alert alert-success">
        <p class="fw-medium mb-0">Ihre Konfiguration ist gespeichert!</p>
        <hr class="my-2">
        <p class="mb-0">Sichern Sie den Code damit Ihre Konfiguration nicht verloren geht.</p>
      </div>
    `;

    // Create comprehensive modal body with all sharing options
    const modalBody = `
      <!-- Success Alert -->
      ${successAlert}
      
      <!-- Configuration Code Section -->
      <div class="mb-3">
        <label class="form-label fw-medium mb-1">
          <i class="fa-sharp fa-light fa-key me-2"></i>Code
        </label>
        <button type="button"
                class="btn btn-outline-success d-block"
                style="--bs-btn-hover-bg: var(--bs-success-bg-subtle); --bs-btn-hover-color: var(--bs-success-text-emphasis); --bs-btn-color: var(--bs-success-text-emphasis); --bs-btn-border-color: var(--bs-success-border-subtle); --bs-btn-active-color: var(--bs-white); --bs-btn-active-border-color: var(--bs-success);"
                data-copy="clipboard"
                data-voucher="${code}"
                data-bs-tooltip="true"
                title="${tooltip}">
          <code class="fs-4">${code}</code>
          <i class="fa-sharp fa-light fa-copy fa-fw ms-2" aria-hidden="true"></i>
          <span class="visually-hidden">Code kopieren</span>
        </button>
      </div>
      
      <!-- Direct Link Section -->
      <div class="mb-3">
        <label class="form-label fw-medium mb-1">
          <i class="fa-sharp fa-light fa-link me-2"></i>Link
        </label>
        <div class="input-group">
          <input type="text" 
                 class="form-control border-success-subtle focus-ring focus-ring-success" 
                 value="${productInfo.directLink}" 
                 readonly 
                 id="direct-link-input">
            <button type="button"
                    class="btn btn-outline-success"
                    style="--bs-btn-hover-bg: var(--bs-success-bg-subtle); --bs-btn-hover-color: var(--bs-success-text-emphasis); --bs-btn-color: var(--bs-success-text-emphasis); --bs-btn-border-color: var(--bs-success-border-subtle); --bs-btn-active-color: var(--bs-white); --bs-btn-active-border-color: var(--bs-success);"
                    data-copy="clipboard"
                    data-voucher="${productInfo.directLink}"
                    data-bs-tooltip="true"
                    title="${tooltip}">
                <i class="fa-sharp fa-light fa-copy fa-fw" aria-hidden="true"></i>
                <span class="visually-hidden">Link kopieren</span>
            </button>
        </div>
      </div>

      <!-- Social Sharing Section -->
      <label class="form-label fw-medium mb-1">
        <i class="fa-sharp fa-light fa-share me-2"></i>Teilen
      </label>
      <div class="row g-2 mb-0" role="group">
        <div class="col col-md-auto">
          <button type="button"
                  class="btn btn-outline-success btn-sm py-2 text-md-start w-100"
                  id="configcode-share-mail"
                  style="--bs-btn-hover-bg: var(--bs-success-bg-subtle); --bs-btn-hover-color: var(--bs-success-text-emphasis); --bs-btn-color: var(--bs-success-text-emphasis); --bs-btn-border-color: var(--bs-success-border-subtle); --bs-btn-active-color: var(--bs-white); --bs-btn-active-border-color: var(--bs-success);">
            <i class="fa-sharp fa-light fa-envelope fa-fw me-1" aria-hidden="true"></i>E-Mail teilen
          </button>
        </div>
        <div class="col col-md-auto">
          <button type="button"
                  class="btn btn-outline-success btn-sm py-2 text-md-start w-100"
                  id="configcode-share-whatsapp"
                  style="--bs-btn-hover-bg: var(--bs-success-bg-subtle); --bs-btn-hover-color: var(--bs-success-text-emphasis); --bs-btn-color: var(--bs-success-text-emphasis); --bs-btn-border-color: var(--bs-success-border-subtle); --bs-btn-active-color: var(--bs-white); --bs-btn-active-border-color: var(--bs-success);">
            <i class="fab fa-whatsapp fa-fw me-1"></i>WhatsApp teilen
          </button>
        </div>
      </div>
    `;

    // Initialize modal with success configuration
    createModal({
      title: "Konfiguration gespeichert",
      body: modalBody,
      size: "md",
      footer: [{ text: "Schließen", class: "btn-dark", dismiss: true }],
    });

    // Initialize Bootstrap tooltips for copy buttons
    setTimeout(() => {
      $('[data-bs-tooltip="true"]').tooltip({ trigger: "hover" });
    }, 0);

    // Setup interactive functionality
    setupDirectLinkHandlers(productInfo.directLink);
    setupShareHandlers(code, productInfo);
  }

  /**
   * Loading States Utility
   *
   * Provides different loading animations for buttons
   *
   * @namespace LoadingStates
   */

  /**
   * Apply spinner loading state (for main save button)
   *
   * @param {jQuery} $button - The button element
   * @returns {Function} Cleanup function to reset button state
   */
  function applySpinnerLoading($button) {
    const originalContent = $button.html();
    const wasDisabled = $button.prop("disabled");

    $button
      .prop("disabled", true)
      .append(
        '<span class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true" data-id="saveSpinner"></span>'
      );

    // Return cleanup function
    return function cleanup() {
      $button.find('[data-id="saveSpinner"]').remove();
      $button.prop("disabled", wasDisabled);
    };
  }

  /**
   * Apply icon animation loading state (for summary save button)
   *
   * @param {HTMLElement|jQuery} button - The button element (can be native DOM or jQuery)
   * @returns {Function} Cleanup function to reset button state
   */
  function applyIconAnimationLoading(button) {
    // Handle both jQuery and native DOM elements
    const $button = button instanceof jQuery ? button : $(button);
    const nativeButton = $button[0];

    const originalContent = $button.html();
    const wasDisabled = $button.prop("disabled");

    // Apply icon animation loading (same as print button)
    $button
      .prop("disabled", true)
      .html(
        '<i class="fa-sharp fa-light fa-spinner fa-fw fa-spin"></i><span class="visually-hidden">Wird gespeichert...</span>'
      );

    // Return cleanup function
    return function cleanup() {
      $button.html(originalContent);
      $button.prop("disabled", wasDisabled);
    };
  }

  /**
   * Core save configuration function - reusable for multiple buttons
   *
   * @param {jQuery} $button - The button element that triggered the save
   * @param {string} loadingType - Type of loading animation ('spinner' or 'icon')
   * @returns {Promise} Promise that resolves when save is complete
   */
  async function saveConfiguration($button, loadingType = "spinner") {
    // Apply appropriate loading state based on type
    const cleanupLoading = loadingType === "icon" ? applyIconAnimationLoading($button) : applySpinnerLoading($button);

    try {
      // Collect all configuration data from form
      const configData = gatherConfigData(false);

      // Send configuration data to server for processing and storage
      const response = await new Promise((resolve, reject) => {
        $.ajax({
          url: myAjaxData.ajaxUrl,
          method: "POST",
          data: {
            action: "save_config",
            security: myAjaxData.nonce,
            product_id: myAjaxData.productId,
            config_data: configData,
          },
          success: resolve,
          error: reject,
        });
      });

      if (response.success) {
        const { generated_code: code, msg, tooltip } = response.data;

        // Automatically add configuration to favourites
        let addedToFavourites = false;
        try {
          const favouriteResult = await addConfigToFavourites(code, myAjaxData.productId);
          addedToFavourites = favouriteResult.added !== false;
        } catch (error) {
          console.warn("Could not add to favourites:", error);
        }

        // Display success modal with favourite confirmation
        showSuccessModal(msg, code, tooltip, addedToFavourites);

        return { success: true, code, addedToFavourites };
      } else {
        console.error("Save configuration failed:", response.data?.msg || "Unknown error");
        throw new Error(response.data?.msg || "Unknown error");
      }
    } catch (error) {
      console.error("Save configuration error:", error);
      throw error;
    } finally {
      // Reset button state regardless of success or failure
      cleanupLoading();
    }
  }

  /**
   * Main Configuration Save Handler
   * Enhanced to automatically add configuration to favourites
   */
  $("#product-configurator-configcode-save").on("click", function () {
    const $button = $(this);
    saveConfiguration($button, "spinner").catch(error => {
      console.error("Save failed:", error);
      // Optionally show error message to user
    });
  });

  // Export functions for use in other modules
  window.ConfiguratorSave = {
    gatherConfigData,
    generateConfigCode,
    saveConfiguration, // Export the updated function
    applySpinnerLoading, // Export loading utilities
    applyIconAnimationLoading,
  };

  // Make showSuccessModal globally available for summary module
  window.showSuccessModal = showSuccessModal;
});

/**
 * Future Enhancement Ideas:
 *
 * 1. Enhanced error handling:
 *    - User-friendly error modals
 *    - Retry mechanisms for failed saves
 *    - Offline storage for drafts
 *
 * 2. Advanced sharing options:
 *    - Social media integration (Facebook, Twitter)
 *    - QR code generation for mobile sharing
 *    - PDF export functionality
 *
 * 3. Configuration management:
 *    - Save multiple configurations per user
 *    - Configuration versioning
 *    - Configuration comparison tools
 *
 * 4. Analytics integration:
 *    - Track save success rates
 *    - Monitor sharing channel usage
 *    - Configuration popularity metrics
 *
 * 5. Performance optimizations:
 *    - Data compression for large configurations
 *    - Background saving
 *    - Progress indicators for large datasets
 *
 * 6. Security improvements (CRITICAL):
 *    - Escape user input in template literals to prevent XSS
 *    - Sanitize HTML content before modal insertion
 *    - Validate all form field names and values
 *    - Implement Content Security Policy headers
 *    - Add CSRF protection for save operations
 *
 * 7. Modern API adoption (CRITICAL):
 *    - Replace deprecated document.execCommand("copy") with modern Clipboard API
 *    - Add fallback for browsers without Clipboard API support
 *    - Implement proper async/await error handling
 *    - Use AbortController for cancellable requests
 *
 * 8. Dependency management:
 *    - Add existence checks for myAjaxData before usage
 *    - Verify createModal function availability
 *    - Implement graceful degradation for missing jQuery
 *    - Add feature detection for required browser APIs
 *
 * 9. Race condition prevention:
 *    - Prevent multiple simultaneous save operations
 *    - Cancel previous requests before new ones
 *    - Implement request queuing system
 *    - Add debouncing for rapid button clicks
 *
 * 10. Error handling robustness:
 *     - Show user-friendly error messages in modals
 *     - Distinguish between network and server errors
 *     - Add comprehensive error logging
 *     - Implement automatic retry with exponential backoff
 *
 * 11. DOM selector reliability:
 *     - Fix typo in selector: #productConfiguroator -> #productConfigurator
 *     - Cache frequently used selectors
 *     - Use data attributes instead of complex class selectors
 *     - Add fallback strategies for missing elements
 *
 * 12. Input validation enhancement:
 *     - Validate configuration data structure before save
 *     - Add client-side validation for required fields
 *     - Implement schema validation for form data
 *     - Sanitize all user inputs before processing
 *
 * 13. Memory management:
 *     - Properly clean up event listeners
 *     - Remove modal elements after closing
 *     - Clear timeouts and intervals
 *     - Implement proper garbage collection for large objects
 *
 * 14. User experience improvements:
 *     - Add save progress indicators
 *     - Implement auto-save functionality
 *     - Show confirmation before leaving page with unsaved changes
 *     - Add keyboard shortcuts for save operations
 *
 * 15. Code maintainability:
 *     - Extract modal HTML generation into templates
 *     - Reduce code duplication between similar functions
 *     - Add comprehensive JSDoc type definitions
 *     - Implement unit tests for core functions
 *
 * 16. Accessibility improvements:
 *     - Add proper ARIA labels to all interactive elements
 *     - Implement keyboard navigation for modals
 *     - Add screen reader announcements for save status
 *     - Ensure high contrast mode compatibility
 *
 * 17. Network resilience:
 *     - Add request timeout handling
 *     - Implement offline save capability
 *     - Add automatic retry for failed network requests
 *     - Show network status indicators
 */
