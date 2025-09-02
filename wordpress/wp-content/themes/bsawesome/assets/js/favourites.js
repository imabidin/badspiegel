/**
 * Product Favourites Management System
 *
 * This module handles the complete favourites functionality for products and
 * product configurations. It provides real-time favourites management with
 * visual feedback, configuration code integration, and badge/counter updates.
 *
 * Features:
 * - Real-time favourites toggle with instant visual feedback
 * - Product configuration code support for specific configurations
 * - Animated badge updates with pulse effects
 * - Haptic feedback for mobile devices
 * - Debounced AJAX requests for performance optimization
 * - Comprehensive error handling with user-friendly messages
 * - State persistence across page reloads
 * - Integration with product configurator system
 * - Bulk operations (clear all favourites)
 * - Responsive UI animations and transitions
 *
 * @version 2.3.1
 * @package Favourites
 * @requires jQuery, myAjaxData global object
 *
 * 01/09/2023 - Added SMART NAVIGATION UTILITIES
 */

jQuery(document).ready(function ($) {
  // ====================== INITIALIZATION AND VALIDATION ======================

  /**
   * Validate required AJAX data is available
   * Exit early if WordPress AJAX data is not loaded
   */
  if (typeof myAjaxData === "undefined") {
    return;
  }

  // ====================== UTILITY FUNCTIONS ======================

  /**
   * Debounce function to prevent rapid successive AJAX calls
   * Improves performance and prevents server overload from rapid clicking
   *
   * @param {Function} func - Function to debounce
   * @param {number} wait - Delay in milliseconds
   * @returns {Function} Debounced function
   *
   * @example
   * const debouncedHandler = debounce(myFunction, 300);
   */
  function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

  /**
   * Extract and validate configuration code from button element
   * Supports multiple fallback methods for configuration code retrieval
   *
   * @param {jQuery} button - jQuery button element containing config data
   * @returns {string|null} Valid 6-character config code or null
   *
   * @example
   * const configCode = getConfigCode($('.btn-favourite'));
   * // Returns: "ABC123" or null
   */
  function getConfigCode(button) {
    let configCode =
      button.data("config-code") || button.attr("data-config-code");

    // 1. Primary validation and sanitization
    if (
      configCode === undefined ||
      configCode === null ||
      configCode === "" ||
      configCode === "undefined"
    ) {
      configCode = null;
    } else if (typeof configCode === "string") {
      configCode = configCode.trim();
      if (
        configCode === "" ||
        configCode === "undefined" ||
        configCode === "null"
      ) {
        configCode = null;
      }
    }

    // 2. Validate format (6-character alphanumeric code)
    if (
      configCode &&
      typeof configCode === "string" &&
      /^[A-Z0-9]{6}$/.test(configCode)
    ) {
      return configCode;
    }

    // 3. Fallback methods for configuration code retrieval
    if (!configCode) {
      // 3a. Global configurator function fallback
      if (typeof window.getConfiguratorCode === "function") {
        try {
          const globalConfig = window.getConfiguratorCode();
          if (
            globalConfig &&
            typeof globalConfig === "string" &&
            globalConfig.trim() !== ""
          ) {
            configCode = globalConfig.trim();
          }
        } catch (e) {
          // Silent error handling for production stability
        }
      }

      // 3b. URL parameter fallback
      if (!configCode) {
        const urlParams = new URLSearchParams(window.location.search);
        const urlConfig =
          urlParams.get("load_config") || urlParams.get("config_code");
        if (
          urlConfig &&
          typeof urlConfig === "string" &&
          urlConfig.trim() !== ""
        ) {
          configCode = urlConfig.trim();
        }
      }
    }

    // 4. Final validation of retrieved configuration code
    return configCode && /^[A-Z0-9]{6}$/.test(configCode) ? configCode : null;
  }

  /**
   * Create unique favourite identifier combining product ID and config code
   * Used for tracking specific product configurations as separate favourites
   *
   * @param {number|string} productId - WooCommerce product ID
   * @param {string|null} configCode - Optional configuration code
   * @returns {string} Unique favourite identifier
   *
   * @example
   * createFavouriteId(123, "ABC123") // Returns: "123_ABC123"
   * createFavouriteId(123, null)     // Returns: "123"
   */
  function createFavouriteId(productId, configCode) {
    return configCode ? `${productId}_${configCode}` : `${productId}`;
  }

  // ====================== VISUAL FEEDBACK SYSTEM ======================

  /**
   * Update favourites badge display with count and animations
   * Handles both numeric badge and icon-only displays with smooth transitions
   *
   * @param {number} count - Current favourites count
   *
   * @example
   * updateFavouritesBadgeDisplay(5); // Shows badge with "5"
   * updateFavouritesBadgeDisplay(0); // Hides badge
   */
  function updateFavouritesBadgeDisplay(count) {
    var badge = $("#favourites-badge");
    var heartIcon = $("#favourites-header-link i");

    count = parseInt(count) || 0;

    // Badge-based display (primary method)
    if (badge.length > 0) {
      if (count > 0) {
        badge.text(count).show();

        // Animate badge when count increases
        var lastCount = badge.data("last-count") || 0;
        if (count > lastCount) {
          badge.addClass("animate__animated animate__pulse");
          setTimeout(function () {
            badge.removeClass("animate__animated animate__pulse");
          }, 600);
        }

        badge.data("last-count", count);
      } else {
        badge.hide();
      }
    } else {
      // Icon-only display (fallback method)
      if (heartIcon.length > 0) {
        if (count > 0) {
          heartIcon.removeClass("fa-thin").addClass("fa-solid text-warning");
        } else {
          heartIcon.removeClass("fa-solid text-warning").addClass("fa-thin");
        }
      }
    }
  }

  // Expose badge update function globally for external use
  window.updateFavouritesBadgeDisplay = updateFavouritesBadgeDisplay;

  // ====================== FAVOURITE BUTTON HANDLER ======================

  /**
   * Main favourite button click handler with debouncing and comprehensive error handling
   * Manages the complete favourite toggle workflow including visual feedback,
   * AJAX communication, state management, and error recovery
   *
   * @param {jQuery} button - The favourite button element
   */
  var favouriteButtonHandler = debounce(function (button) {
    var heartIcon = button.find("i");

    // 1. Prevent concurrent requests
    if (button.data("processing")) {
      return;
    }

    button.data("processing", true);

    // 2. Determine current and target states
    var isCurrentlyFavourite = heartIcon.hasClass("text-warning");
    var willBeFavourite = !isCurrentlyFavourite;

    // 3. Apply immediate visual feedback for responsiveness
    if (willBeFavourite) {
      heartIcon
        .removeClass("fa-light")
        .addClass("fa-solid fa-heart text-warning");
      button.attr("title", "Aus Favoriten entfernen");
      button.attr("aria-pressed", "true");
    } else {
      heartIcon
        .removeClass("fa-solid text-warning")
        .addClass("fa-light fa-heart");
      button.attr("title", "Zu Favoriten hinzufügen");
      button.attr("aria-pressed", "false");
    }

    // 4. Extract and validate required data
    var productId = button.data("product-id");
    var configCode = getConfigCode(button);

    // 4a. Validate product ID
    if (!productId || productId <= 0 || isNaN(productId)) {
      rollbackToOriginalState();
      button.removeData("processing");
      return;
    }

    // 4b. Sanitize configuration code
    if (
      configCode === undefined ||
      configCode === "undefined" ||
      configCode === ""
    ) {
      configCode = null;
    }

    // 5. Provide haptic feedback for mobile devices
    if ("vibrate" in navigator && /Mobi|Android/i.test(navigator.userAgent)) {
      navigator.vibrate(willBeFavourite ? [50] : [30, 30, 30]);
    }

    // 6. Prepare AJAX request data
    var ajaxData = {
      action: "toggle_favourite",
      product_id: productId,
      nonce: myAjaxData.favouriteNonce,
    };

    // 6a. Include configuration code if available
    if (configCode !== null && configCode !== undefined && configCode !== "") {
      ajaxData.config_code = configCode;
    }

    // 7. Execute AJAX request with comprehensive error handling
    $.ajax({
      url: myAjaxData.ajaxUrl,
      type: "POST",
      data: ajaxData,
      success: function (response) {
        if (response.success) {
          // 7a. Update favourite ID tracking
          if (response.data.action === "added") {
            const favId = createFavouriteId(productId, configCode);
            button.attr("data-favourite-id", favId);
          } else if (response.data.action === "removed") {
            button.removeAttr("data-favourite-id");
          }

          // 7b. Update global badge counter
          if (typeof response.data.count !== "undefined") {
            updateFavouritesBadgeDisplay(response.data.count);
          }
        } else {
          // 7c. Handle server-side errors
          rollbackToOriginalState();
          alert(response.data?.message || "Ein Fehler ist aufgetreten.");
        }
      },
      error: function (xhr, status, error) {
        // 7d. Handle network and HTTP errors
        rollbackToOriginalState();

        var errorMessage =
          "Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut.";

        if (xhr.status === 0) {
          errorMessage = "Keine Verbindung zum Server.";
        } else if (xhr.status >= 500) {
          errorMessage = "Server-Fehler. Bitte versuchen Sie es später erneut.";
        } else if (xhr.status === 403) {
          errorMessage = "Sitzung abgelaufen. Bitte laden Sie die Seite neu.";
        }

        alert(errorMessage);
      },
      complete: function () {
        // 7e. Always clean up processing state
        button.removeData("processing");
      },
    });

    /**
     * Rollback visual state to original condition on error
     * Ensures UI consistency when AJAX requests fail
     */
    function rollbackToOriginalState() {
      if (isCurrentlyFavourite) {
        heartIcon
          .removeClass("fa-light")
          .addClass("fa-solid fa-heart text-warning");
        button.attr("title", "Aus Favoriten entfernen");
        button.attr("aria-pressed", "true");

        const favId = createFavouriteId(productId, configCode);
        button.attr("data-favourite-id", favId);
      } else {
        heartIcon
          .removeClass("fa-solid text-warning")
          .addClass("fa-light fa-heart");
        button.attr("title", "Zu Favoriten hinzufügen");
        button.attr("aria-pressed", "false");

        button.removeAttr("data-favourite-id");
      }
    }
  }, 300);

  // ====================== EVENT LISTENERS ======================

  /**
   * Primary favourite button click event handler
   * Uses event delegation for dynamically added buttons
   */
  $(document).on("click", ".btn-favourite-loop", function (e) {
    e.preventDefault();
    e.stopPropagation();

    var button = $(this);

    // Prevent processing if already handling a request
    if (button.data("processing")) {
      return false;
    }

    favouriteButtonHandler(button);
  });

  /**
   * BULK OPERATIONS
   * ===============
   */

  /**
   * Clear all favourites handler with confirmation dialog
   * Provides bulk deletion functionality with user confirmation
   */
  $(document).on("click", "#clear-all-favourites", function (e) {
    e.preventDefault();

    // User confirmation for destructive action
    if (
      !confirm(
        "Möchten Sie wirklich alle Favoriten löschen? Diese Aktion kann nicht rückgängig gemacht werden."
      )
    ) {
      return;
    }

    var button = $(this);
    var originalText = button.html();

    // Visual loading state
    button
      .prop("disabled", true)
      .html(
        '<i class="fa-light fa-sharp fa-spinner fa-spin me-1"></i>Lösche...'
      );

    $.ajax({
      url: myAjaxData.ajaxUrl,
      type: "POST",
      data: {
        action: "clear_all_favourites",
        nonce: myAjaxData.favouriteNonce,
      },
      success: function (response) {
        if (response.success) {
          updateFavouritesBadgeDisplay(0);
          // Delay reload for smooth user experience
          setTimeout(function () {
            location.reload();
          }, 500);
        } else {
          alert(response.data?.message || "Fehler beim Löschen der Favoriten.");
          button.prop("disabled", false).html(originalText);
        }
      },
      error: function (xhr, status, error) {
        alert("Fehler beim Löschen der Favoriten.");
        button.prop("disabled", false).html(originalText);
      },
    });
  });

  /**
   * FAVOURITES PAGE SPECIFIC HANDLING
   * =================================
   */

  /**
   * Remove favourite from favourites page with smooth animations
   * Handles context-specific removal with card animations
   */
  $(document).on("click", ".favourite-context", function (e) {
    var button = $(this);
    var productCard = button.closest(
      ".card, .favourite-product-card, .wc-block-grid__product, .product"
    );

    // Only process on favourites page
    if ($(".favourites-container").length === 0) {
      return;
    }

    // Set up one-time success handler for smooth removal animation
    button.one("ajaxSuccess", function () {
      setTimeout(function () {
        // Verify favourite was actually removed
        if (!button.find("i").hasClass("text-warning")) {
          productCard.addClass("removing");

          setTimeout(function () {
            productCard.fadeOut(300, function () {
              productCard.remove();

              // Check if any products remain
              var remainingProducts = $(
                ".card, .favourite-product-card, .wc-block-grid__product, .product"
              ).filter(":visible");
              if (remainingProducts.length === 0) {
                // Reload page when no favourites remain
                setTimeout(function () {
                  location.reload();
                }, 1000);
              }
            });
          }, 500);
        }
      }, 1500);
    });
  });

  // ====================== STATE INITIALIZATION ======================

  /**
   * Initialize favourite button states based on current user favourites
   * Queries server for current favourite state and updates UI accordingly
   */
  function initializeFavouriteStates() {
    $(".btn-favourite-loop").each(function () {
      var button = $(this);
      var productId = button.data("product-id");
      var configCode = getConfigCode(button);
      var heartIcon = button.find("i");

      // Skip invalid product IDs
      if (!productId || productId <= 0) {
        return;
      }

      // Special handling for favourites page (all items are favourites)
      if ($(".favourites-container").length > 0) {
        heartIcon.removeClass("fa-light").addClass("fa-solid text-warning");
        button.attr("title", "Aus Favoriten entfernen");
        button.attr("aria-pressed", "true");

        const favId = createFavouriteId(productId, configCode);
        button.attr("data-favourite-id", favId);
        return;
      }

      // AJAX state check for other pages
      var ajaxData = {
        action: "check_config_favourite_state",
        product_id: productId,
        nonce: myAjaxData.favouriteNonce,
      };

      // Include configuration code if available
      if (configCode !== null && configCode !== "") {
        ajaxData.config_code = configCode;
      }

      $.ajax({
        url: myAjaxData.ajaxUrl,
        type: "POST",
        data: ajaxData,
        success: function (response) {
          if (response.success && response.data.is_favourite) {
            heartIcon.removeClass("fa-light").addClass("fa-solid text-warning");

            button.attr("title", "Aus Favoriten entfernen");
            button.attr("aria-pressed", "true");

            const favId = createFavouriteId(productId, configCode);
            button.attr("data-favourite-id", favId);
          }
        },
        error: function (xhr, status, error) {
          // Silent error handling in production
        },
      });
    });
  }

  // ====================== CONFIGURATOR INTEGRATION ======================

  /**
   * Global function for configurator integration
   * Allows configurator system to add current configuration to favourites
   *
   * @param {number} productId - WooCommerce product ID
   * @param {string} configCode - Configuration code to save
   * @returns {Promise} Promise that resolves with server response
   *
   * @example
   * window.addCurrentConfigToFavourites(123, "ABC123")
   *   .then(data => console.log('Added to favourites'))
   *   .catch(error => console.error('Failed to add'));
   */
  window.addCurrentConfigToFavourites = function (productId, configCode) {
    if (!productId || !configCode) {
      return Promise.reject("Missing parameters");
    }

    return new Promise((resolve, reject) => {
      $.ajax({
        url: myAjaxData.ajaxUrl,
        type: "POST",
        data: {
          action: "add_favourite_with_config",
          product_id: productId,
          config_code: configCode,
          nonce: myAjaxData.favouriteNonce,
        },
        success: function (response) {
          if (response.success) {
            // Update global badge counter
            updateFavouritesBadgeDisplay(response.data.count);

            // Update all matching buttons on page
            $(`.btn-favourite-loop[data-product-id="${productId}"]`).each(
              function () {
                const button = $(this);
                const buttonConfigCode = getConfigCode(button);

                if (buttonConfigCode === configCode) {
                  const heartIcon = button.find("i");
                  heartIcon
                    .removeClass("fa-light")
                    .addClass("fa-solid text-warning");

                  button.attr("title", "Aus Favoriten entfernen");
                  button.attr("aria-pressed", "true");
                }
              }
            );

            resolve(response.data);
          } else {
            reject(response.data?.message || "Failed to add to favourites");
          }
        },
        error: function (xhr, status, error) {
          reject(error);
        },
      });
    });
  };

  // ====================== MODULE INITIALIZATION ======================

  /**
   * Initialize favourites badge count on page load
   * Fetches current count from server and updates display
   */
  $.ajax({
    url: myAjaxData.ajaxUrl,
    type: "POST",
    data: {
      action: "get_favourites_count",
      nonce: myAjaxData.favouriteNonce,
    },
    success: function (response) {
      if (response.success) {
        updateFavouritesBadgeDisplay(response.data.count);
      }
    },
  });

  /**
   * Initialize favourite states after short delay
   * Ensures DOM is fully rendered before state initialization
   */
  setTimeout(function () {
    initializeFavouriteStates();
  }, 100);

  /**
   * Re-initialize states on configurator events
   * Handles dynamic content loading and state changes
   */
  $(document).on(
    "configurator-ready configurator-loaded page-ready",
    function () {
      setTimeout(function () {
        initializeFavouriteStates();
      }, 500);
    }
  );
});

// ====================== AUTHENTICATION FORM HANDLING ======================

/**
 * Handle authentication form toggles and smooth transitions
 * Provides seamless switching between login and registration forms
 */
$(document).ready(function () {
  // Handle toggle between login and register forms
  $('[data-bs-toggle="collapse"][data-bs-target="#favourites-login-form"]').on(
    "click",
    function (e) {
      e.preventDefault();

      const $loginBtn = $(this);
      const $registerBtn = $(
        '[data-bs-toggle="collapse"][data-bs-target="#favourites-register-form"]'
      );
      const $loginForm = $("#favourites-login-form");
      const $registerForm = $("#favourites-register-form");

      // Update button states
      $loginBtn.removeClass("btn-outline-primary").addClass("btn-primary");
      $registerBtn.removeClass("btn-primary").addClass("btn-outline-primary");

      // Show login form, hide register form
      $registerForm.collapse("hide");
      setTimeout(() => {
        $loginForm.collapse("show");
      }, 150);
    }
  );

  $(
    '[data-bs-toggle="collapse"][data-bs-target="#favourites-register-form"]'
  ).on("click", function (e) {
    e.preventDefault();

    const $registerBtn = $(this);
    const $loginBtn = $(
      '[data-bs-toggle="collapse"][data-bs-target="#favourites-login-form"]'
    );
    const $loginForm = $("#favourites-login-form");
    const $registerForm = $("#favourites-register-form");

    // Update button states
    $registerBtn.removeClass("btn-outline-primary").addClass("btn-primary");
    $loginBtn.removeClass("btn-primary").addClass("btn-outline-primary");

    // Show register form, hide login form
    $loginForm.collapse("hide");
    setTimeout(() => {
      $registerForm.collapse("show");
    }, 150);
  });

  // ====================== GUEST ACCOUNT COLLAPSE HANDLING ======================

  /**
   * Enhanced guest account collapse toggle behavior
   * Fades out and disables toggle button when account section is opened
   */

  // Handle guest account section toggle button
  $(".guest-account-toggle").on("click", function (e) {
    const $toggleButton = $(this);
    const $guestAccountSection = $("#guest-account-section");

    // Prevent default Bootstrap collapse behavior temporarily
    e.preventDefault();
    e.stopPropagation();

    // Check if section is currently collapsed
    if (!$guestAccountSection.hasClass("show")) {
      // About to show - disable button and show section
      $toggleButton.prop("disabled", true).addClass("pe-none");

      $guestAccountSection.collapse("show");
    }
  });

  // Handle collapse events for guest account section
  const $guestAccountSection = $("#guest-account-section");
  const $guestToggleButton = $(".guest-account-toggle");

  if ($guestAccountSection.length && $guestToggleButton.length) {
    // When collapse is fully shown
    $guestAccountSection.on("shown.bs.collapse", function () {
      // Auto-focus first input field
      const $firstInput = $(this)
        .find('input[type="text"], input[type="email"]')
        .first();
      if ($firstInput.length) {
        setTimeout(() => $firstInput.focus(), 100);
      }
    });

    // When collapse starts to hide
    $guestAccountSection.on("hide.bs.collapse", function () {
      // Re-enable the toggle button
      $guestToggleButton.removeClass("pe-none").prop("disabled", false);
    });

    // Optional: Handle programmatic closing (e.g., successful login)
    $guestAccountSection.on("hidden.bs.collapse", function () {
      // Ensure button is enabled
      if ($guestToggleButton.prop("disabled")) {
        $guestToggleButton.removeClass("pe-none").prop("disabled", false);
      }
    });
  }

  // ====================== FORM SUBMISSION HANDLING ======================

  // Handle form submission with loading states
  $(".woocommerce-form-login").on("submit", function () {
    const $form = $(this);
    const $submitBtn = $form.find('button[type="submit"]');
    const originalText = $submitBtn.html();

    $submitBtn
      .prop("disabled", true)
      .html(
        '<i class="fa-light fa-sharp fa-spinner fa-spin me-2"></i>Anmelden...'
      );

    // Store action for potential page reload handling
    localStorage.setItem("favourites_login_attempt", "true");

    // Reset button after 10 seconds (fallback)
    setTimeout(() => {
      $submitBtn.prop("disabled", false).html(originalText);
    }, 10000);
  });

  $(".woocommerce-form-register").on("submit", function () {
    const $form = $(this);
    const $submitBtn = $form.find('button[type="submit"]');
    const originalText = $submitBtn.html();

    $submitBtn
      .prop("disabled", true)
      .html(
        '<i class="fa-light fa-sharp fa-spinner fa-spin me-2"></i>Registrieren...'
      );

    // Store action for potential page reload handling
    localStorage.setItem("favourites_register_attempt", "true");

    // Reset button after 10 seconds (fallback)
    setTimeout(() => {
      $submitBtn.prop("disabled", false).html(originalText);
    }, 10000);
  });

  // ====================== POST-AUTHENTICATION HANDLING ======================

  /**
   * Handle successful login/registration scenarios
   * Automatically reload page if authentication was successful
   */
  $(document).ready(function () {
    // Check if we returned from a login/register attempt
    const loginAttempt = localStorage.getItem("favourites_login_attempt");
    const registerAttempt = localStorage.getItem("favourites_register_attempt");

    if (loginAttempt === "true" || registerAttempt === "true") {
      // Clear the flags
      localStorage.removeItem("favourites_login_attempt");
      localStorage.removeItem("favourites_register_attempt");

      // Check if user is now logged in (basic check)
      if (
        $("body").hasClass("logged-in") ||
        $(".woocommerce-MyAccount-navigation").length > 0
      ) {
        // User successfully logged in, reload to show authenticated view
        setTimeout(() => {
          window.location.reload();
        }, 1000);
      }
    }
  });

  // Auto-focus first input when forms are shown (original functionality preserved)
  $("#favourites-login-form").on("shown.bs.collapse", function () {
    $(this).find('input[name="username"]').focus();
  });

  $("#favourites-register-form").on("shown.bs.collapse", function () {
    $(this).find('input[name="username"], input[name="email"]').first().focus();
  });

  // ====================== UTILITY FUNCTIONS FOR GUEST HANDLING ======================

  /**
   * Programmatically close guest account section
   * Useful for external triggers (e.g., successful authentication)
   */
  window.closeGuestAccountSection = function () {
    const $guestAccountSection = $("#guest-account-section");
    if ($guestAccountSection.hasClass("show")) {
      $guestAccountSection.collapse("hide");
    }
  };

  /**
   * Programmatically open guest account section
   * Useful for external triggers or deep linking
   */
  window.openGuestAccountSection = function () {
    const $guestAccountSection = $("#guest-account-section");
    const $toggleButton = $(".guest-account-toggle");

    if (!$guestAccountSection.hasClass("show")) {
      // Trigger the same behavior as clicking the button
      $toggleButton.prop("disabled", true).addClass("pe-none");

      $guestAccountSection.collapse("show");
    }
  };
});

// ====================== SMART NAVIGATION UTILITIES ======================

/**
 * Intelligent back navigation handler with history detection
 * Handles cases where no browser history exists (direct URL entry, incognito mode)
 */
window.handleBackNavigation = function() {
  if (window.history.length > 1) {
    window.history.back();
  } else {
    // Fallback to home page when no history available
    window.location.href = "/";
  }
};

/**
 * Initialize smart back button display
 * Only shows back button if browser history is available
 */
function initializeSmartBackButton() {
  const backButton = document.getElementById("back-button");
  if (backButton && window.history.length > 1) {
    backButton.style.display = "inline-block";
  }
}

// Initialize back button on DOM ready
$(document).ready(function() {
  initializeSmartBackButton();
});
