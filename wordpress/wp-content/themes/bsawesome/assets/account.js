/**
 * Account Management - Modern Login/Registration System
 *
 * This module provides a modern, user-friendly authentication experience with
 * email-based user detection, progressive form disclosure, and enhanced UX features.
 * Replaces traditional WordPress login forms with a streamlined single-page flow.
 *
 * Features:
 * - Progressive email validation with user existence checking
 * - Dynamic form transitions between login and registration states
 * - Real-time input validation with visual feedback
 * - Login attempt rate limiting with countdown timers
 * - Automatic page title updates based on current state
 * - Enhanced accessibility with keyboard navigation support
 * - AJAX-powered user operations without page reloads
 * - Loading states and error handling for all operations
 * - Password visibility toggle functionality
 * - Registration with automatic login and welcome flow
 *
 * @version 2.6.0
 * @package Account
 * @requires ajax_object global object with nonce and URL
 */

document.addEventListener("DOMContentLoaded", function () {
  // ====================== STATE MANAGEMENT ======================

  /**
   * Global state variables for authentication flow management
   * These variables track the current state and prevent concurrent operations
   */
  let userExists = false; // Whether the entered email belongs to existing user
  let currentEmail = ""; // Currently validated email address
  let originalFormTextContent = ""; // Original help text for form reset functionality
  let isProcessing = false; // Prevents concurrent AJAX requests

  // ====================== CONFIGURATION CONSTANTS ======================

  /**
   * Email validation regex pattern (RFC 5322 compliant)
   * Validates standard email format with domain requirements
   */
  const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

  // ====================== INITIALIZATION ======================

  /**
   * Store original form-text content on page load
   * Preserves initial help text for proper form reset functionality
   */
  const formText = document.querySelector(
    "#username_email_check_wrapper .form-text"
  );
  if (formText) {
    originalFormTextContent = formText.textContent;
  }

  /**
   * Update page title for WooCommerce account page context
   * Only applies to non-logged-in users on the account page
   */
  if (
    document.body.classList.contains("woocommerce-account") &&
    !document.body.classList.contains("logged-in")
  ) {
    updatePageTitle("initial");
  }

  /**
   * Initialize all event listeners for interactive elements
   */
  setupEventListeners();

  // ====================== EVENT LISTENER SETUP ======================

  /**
   * Setup all event listeners for form interactions and keyboard navigation
   * Centralizes event binding for better organization and maintenance
   */
  function setupEventListeners() {
    /**
     * Email input validation and real-time feedback
     */
    const emailInput = document.getElementById("username_email_check");
    if (emailInput) {
      // Clear validation errors when user starts typing
      emailInput.addEventListener("input", function () {
        const input = this.value.trim();
        if (input.length > 0 && this.classList.contains("is-invalid")) {
          clearValidationMessage();
        }
      });

      // Handle Enter key press for improved UX
      emailInput.addEventListener("keypress", function (e) {
        if (e.key === "Enter" || e.keyCode === 13) {
          e.preventDefault();
          document.getElementById("continue-btn").click();
        }
      });
    }

    /**
     * Password input validation and keyboard handling
     */
    const passwordInput = document.getElementById("password");
    if (passwordInput) {
      // Clear password validation errors during typing
      passwordInput.addEventListener("input", function () {
        if (this.classList.contains("is-invalid")) {
          this.classList.remove("is-invalid");
          clearPasswordValidation();
        }
      });

      // Handle Enter key for login submission
      passwordInput.addEventListener("keypress", function (e) {
        if (e.key === "Enter" || e.keyCode === 13) {
          e.preventDefault();
          handleLogin();
        }
      });
    }

    /**
     * Main form submission handler
     * Routes to appropriate action based on current form state
     */
    const form = document.getElementById("modern-login-form");
    if (form) {
      form.addEventListener("submit", function (e) {
        e.preventDefault();

        // Determine current state and route accordingly
        const loginSection = document.getElementById("username_email_valid");
        const registerSection = document.getElementById(
          "username_email_invalid"
        );

        if (loginSection && loginSection.classList.contains("show")) {
          handleLogin();
        } else if (
          registerSection &&
          registerSection.classList.contains("show")
        ) {
          handleRegistration();
        } else {
          // Initial state - trigger email validation
          document.getElementById("continue-btn").click();
        }
      });
    }

    /**
     * Password visibility toggle functionality
     * Enhances UX by allowing users to verify password input
     */
    const showPasswordBtn = document.getElementById("show-password-btn");
    if (showPasswordBtn) {
      showPasswordBtn.addEventListener("click", function (e) {
        e.preventDefault();
        const passwordField = document.getElementById("password");

        if (passwordField.type === "password") {
          passwordField.type = "text";
          this.textContent = "Passwort verbergen";
        } else {
          passwordField.type = "password";
          this.textContent = "Passwort anzeigen";
        }
      });
    }

    /**
     * Continue button - Main action button handler
     * Handles email validation, login, and registration based on current state
     */
    const continueBtn = document.getElementById("continue-btn");
    if (continueBtn) {
      continueBtn.addEventListener("click", function (e) {
        e.preventDefault();

        // Prevent concurrent operations
        if (isProcessing) {
          return;
        }

        // Route to appropriate handler based on visible sections
        const loginSection = document.getElementById("username_email_valid");
        const registerSection = document.getElementById(
          "username_email_invalid"
        );

        if (loginSection && loginSection.classList.contains("show")) {
          handleLogin();
          return;
        }

        if (registerSection && registerSection.classList.contains("show")) {
          handleRegistration();
          return;
        }

        // Initial state: validate email and check user existence
        const email = document
          .getElementById("username_email_check")
          .value.trim();

        // Validate email presence
        if (!email) {
          showValidationMessage(
            "Bitte geben Sie eine E-Mail-Adresse ein.",
            "error"
          );
          document.getElementById("username_email_check").focus();
          return;
        }

        // Validate email format
        if (!isValidEmail(email)) {
          showValidationMessage(
            "Bitte geben Sie eine gültige E-Mail-Adresse ein (z.B. name@domain.de).",
            "error"
          );
          document.getElementById("username_email_check").focus();
          return;
        }

        // Check if we need to revalidate this email
        if (currentEmail !== email) {
          checkUserExists(email);
        } else {
          // Email already validated, proceed to next step
          if (userExists) {
            showLoginForm();
          } else {
            showRegistrationInfo();
          }
        }
      });
    }

    /**
     * Edit/Change button handler
     * Allows users to modify their email address and restart the flow
     */
    const changeBtn = document.getElementById("change-btn");
    if (changeBtn) {
      changeBtn.addEventListener("click", function (e) {
        e.preventDefault();
        resetForm();
        document.getElementById("username_email_check").focus();
      });
    }
  }

  // ====================== EMAIL VALIDATION SYSTEM ======================

  /**
   * Comprehensive email validation with multiple security checks
   * Validates format, length limits, and common security issues
   *
   * @param {string} email - Email address to validate
   * @returns {boolean} True if email passes all validation checks
   *
   * @example
   * isValidEmail('user@example.com') // Returns: true
   * isValidEmail('invalid.email')    // Returns: false
   */
  function isValidEmail(email) {
    // 1. Basic format validation using regex
    if (!emailPattern.test(email)) {
      return false;
    }

    // 2. RFC 5321 length limit check
    if (email.length > 254) {
      return false;
    }

    // 3. Split and validate email parts
    const parts = email.split("@");
    if (parts.length !== 2) {
      return false;
    }

    const [localPart, domain] = parts;

    // 4. Local part validation (before @)
    if (localPart.length === 0 || localPart.length > 64) {
      return false;
    }

    // 5. Domain part validation (after @)
    if (domain.length === 0 || domain.length > 253) {
      return false;
    }

    // 6. Check for consecutive dots (security issue)
    if (email.includes("..")) {
      return false;
    }

    // 7. Check for leading/trailing dots in local part
    if (localPart.startsWith(".") || localPart.endsWith(".")) {
      return false;
    }

    return true;
  }

  // ====================== VALIDATION FEEDBACK SYSTEM ======================

  /**
   * Display validation messages with appropriate styling and ARIA support
   * Provides visual feedback for form validation states
   *
   * @param {string} message - Message text to display
   * @param {string} [type='info'] - Message type: 'info', 'error', 'success'
   *
   * @example
   * showValidationMessage('Email ist erforderlich', 'error');
   * showValidationMessage('Email validiert', 'success');
   */
  function showValidationMessage(message, type = "info") {
    const formTextEl = document.querySelector(
      "#username_email_check_wrapper .form-text"
    );
    const emailInput = document.getElementById("username_email_check");

    if (formTextEl) {
      // Update message styling based on type
      const messageClass =
        type === "error"
          ? "text-danger"
          : type === "success"
          ? "text-success"
          : "text-muted";
      formTextEl.className = formTextEl.className.replace(
        /text-(muted|danger|success)/g,
        ""
      );
      formTextEl.classList.add(messageClass);
      formTextEl.textContent = message;
    }

    // Update input validation state
    if (type === "error") {
      emailInput.classList.add("is-invalid");
    } else {
      emailInput.classList.remove("is-invalid");
    }
  }

  /**
   * Clear validation messages and reset to original state
   * Removes error states and restores default help text
   */
  function clearValidationMessage() {
    const formTextEl = document.querySelector(
      "#username_email_check_wrapper .form-text"
    );
    const emailInput = document.getElementById("username_email_check");

    if (formTextEl) {
      // Reset to original styling and content
      formTextEl.className = formTextEl.className.replace(
        /text-(danger|success)/g,
        ""
      );
      formTextEl.classList.add("text-muted");
      formTextEl.textContent = originalFormTextContent;
    }

    // Clear validation classes
    emailInput.classList.remove("is-invalid", "is-valid");
  }

  // ====================== USER EXISTENCE CHECKING ======================

  /**
   * Check if user exists via AJAX and route to appropriate flow
   * Determines whether to show login or registration form
   *
   * @param {string} usernameOrEmail - Email address to check
   *
   * @example
   * checkUserExists('user@example.com');
   */
  function checkUserExists(usernameOrEmail) {
    const continueBtn = document.getElementById("continue-btn");

    // Execute AJAX request with loading state
    fetch(ajax_object.ajax_url, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams({
        action: "check_user_exists",
        username_email: usernameOrEmail,
        nonce: ajax_object.nonce,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // Handle invalid email response
          if (data.data.valid_email === false) {
            showValidationMessage(
              "Ungültige E-Mail-Adresse. Bitte überprüfen Sie die Eingabe.",
              "error"
            );
            continueBtn.disabled = false;
            continueBtn.textContent = "Weiter";
            return;
          }

          // Store results and proceed to next step
          userExists = data.data.exists;
          currentEmail = data.data.value;

          if (userExists) {
            showLoginForm();
          } else {
            showRegistrationInfo();
          }
        } else {
          // Handle server errors
          console.error("Error:", data.data.message);
          showValidationMessage(
            "Fehler bei der Überprüfung. Bitte versuchen Sie es erneut.",
            "error"
          );
          continueBtn.disabled = false;
          continueBtn.textContent = "Weiter";
        }
      })
      .catch((error) => {
        // Handle network errors
        console.error("AJAX request failed:", error);
        showValidationMessage(
          "Verbindungsfehler. Bitte versuchen Sie es erneut.",
          "error"
        );
        continueBtn.disabled = false;
        continueBtn.textContent = "Weiter";
      });

    // Set immediate loading state
    continueBtn.disabled = true;
    continueBtn.innerHTML =
      '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
    clearValidationMessage();
  }

  // ====================== PAGE TITLE MANAGEMENT ======================

  /**
   * Update page title based on current authentication state
   * Only applies to WooCommerce account page for non-logged-in users
   *
   * @param {string} state - Current state: 'initial', 'login', 'register'
   *
   * @example
   * updatePageTitle('login'); // Sets title to "Anmelden"
   */
  function updatePageTitle(state) {
    // Scope guard: only update on WooCommerce account page for non-logged-in users
    if (
      !document.body.classList.contains("woocommerce-account") ||
      document.body.classList.contains("logged-in")
    ) {
      return;
    }

    const h1 = document.querySelector("h1");
    if (!h1) {
      return;
    }

    // Update title based on current state
    switch (state) {
      case "initial":
        h1.textContent = "Anmelden oder registrieren";
        break;
      case "login":
        h1.textContent = "Anmelden";
        break;
      case "register":
        h1.textContent = "Registrieren";
        break;
    }
  }

  // ====================== PASSWORD VALIDATION SYSTEM ======================

  /**
   * Display password-specific validation messages
   * Provides context-aware feedback for password field
   *
   * @param {string} message - Validation message to display
   * @param {string} [type='info'] - Message type: 'info' or 'error'
   */
  function showPasswordValidation(message, type = "info") {
    const formTextEl = document.querySelector(
      "#username_email_valid .form-text"
    );

    if (formTextEl) {
      const messageClass = type === "error" ? "text-danger" : "text-muted";
      formTextEl.className = formTextEl.className.replace(
        /text-(muted|danger)/g,
        ""
      );
      formTextEl.classList.add(messageClass);
      formTextEl.textContent = message;
    }
  }

  /**
   * Clear password validation messages and reset to default
   * Restores welcome message for returning users
   */
  function clearPasswordValidation() {
    const formTextEl = document.querySelector(
      "#username_email_valid .form-text"
    );

    if (formTextEl) {
      formTextEl.className = formTextEl.className.replace(/text-danger/g, "");
      formTextEl.classList.add("text-muted");
      formTextEl.textContent =
        "Willkommen zurück! Bitte geben Sie Ihr Passwort ein.";
    }
  }

  // ====================== FORM STATE TRANSITIONS ======================

  /**
   * Show login form for existing users
   * Transitions to password input state with appropriate UI changes
   */
  function showLoginForm() {
    const emailInput = document.getElementById("username_email_check");
    const changeBtn = document.getElementById("change-btn");
    const loginSection = document.getElementById("username_email_valid");
    const passwordInput = document.getElementById("password");
    const continueBtn = document.getElementById("continue-btn");

    // Update UI elements for login state
    emailInput.disabled = true;
    changeBtn.classList.remove("fade");

    // Show login section using Bootstrap collapse
    loginSection.classList.add("show");

    // Set focus and update messaging
    passwordInput.focus();
    clearValidationMessage();
    clearPasswordValidation();
    continueBtn.disabled = false;
    continueBtn.textContent = "Anmelden";
    updatePageTitle("login");
  }

  /**
   * Show registration information for new users
   * Transitions to registration state with appropriate messaging
   */
  function showRegistrationInfo() {
    const emailInput = document.getElementById("username_email_check");
    const changeBtn = document.getElementById("change-btn");
    const registerSection = document.getElementById("username_email_invalid");
    const continueBtn = document.getElementById("continue-btn");

    // Update UI elements for registration state
    emailInput.disabled = true;
    changeBtn.classList.remove("fade");

    // Show registration section using Bootstrap collapse
    registerSection.classList.add("show");

    // Update messaging and button text
    clearValidationMessage();
    continueBtn.disabled = false;
    continueBtn.textContent = "Registrieren";
    updatePageTitle("register");
  }

  // ====================== LOGIN HANDLING ======================

  /**
   * Handle login form submission with validation
   * Validates inputs before sending to server
   */
  function handleLogin() {
    const email = document.getElementById("username_email_check").value.trim();
    const password = document.getElementById("password").value.trim();

    // Validate required fields
    if (!email || !password) {
      if (!password) {
        document.getElementById("password").classList.add("is-invalid");
        showPasswordValidation("Bitte geben Sie Ihr Passwort ein.", "error");
      }
      return;
    }

    // Validate minimum password length
    if (password.length < 6) {
      document.getElementById("password").classList.add("is-invalid");
      showPasswordValidation(
        "Das Passwort muss mindestens 6 Zeichen lang sein.",
        "error"
      );
      return;
    }

    // Proceed to server validation
    validateLogin(email, password);
  }

  /**
   * Validate login credentials via AJAX with rate limiting support
   * Handles successful login, errors, and account lockouts
   *
   * @param {string} email - User email address
   * @param {string} password - User password
   */
  function validateLogin(email, password) {
    // Prevent concurrent login attempts
    if (isProcessing) {
      return;
    }

    isProcessing = true;
    const continueBtn = document.getElementById("continue-btn");

    // Execute login validation request
    fetch(ajax_object.ajax_url, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams({
        action: "validate_user_login",
        username: email,
        password: password,
        remember_me: "true",
        perform_login: "true",
        nonce: ajax_object.nonce,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // Successful login - reload page
          window.location.reload();
        } else {
          // Handle login failure
          isProcessing = false;
          const passwordInput = document.getElementById("password");
          passwordInput.classList.add("is-invalid");

          if (data.data.lockout) {
            // Handle rate limiting lockout
            passwordInput.disabled = true;
            continueBtn.disabled = true;
            continueBtn.textContent = "Gesperrt";
            showPasswordValidation(data.data.message, "error");

            // Start countdown timer if provided
            if (data.data.remaining_time) {
              startLockoutCountdown(data.data.remaining_time);
            }
          } else {
            // Handle standard login error
            showPasswordValidation(
              data.data.message ||
                "Ungültige Anmeldedaten. Bitte überprüfen Sie Ihre Eingaben.",
              "error"
            );
            continueBtn.disabled = false;
            continueBtn.textContent = "Anmelden";
            passwordInput.focus();
            passwordInput.select();
          }
        }
      })
      .catch((error) => {
        // Handle network errors
        isProcessing = false;
        console.error("Login validation failed:", error);
        showPasswordValidation(
          "Verbindungsfehler. Bitte versuchen Sie es erneut.",
          "error"
        );
        continueBtn.disabled = false;
        continueBtn.textContent = "Anmelden";
      });

    // Set immediate loading state
    continueBtn.disabled = true;
    continueBtn.innerHTML =
      '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Anmelden...';
    clearPasswordValidation();
  }

  // ====================== REGISTRATION HANDLING ======================

  /**
   * Register new user account with automatic login
   * Creates account and immediately logs user in for seamless experience
   *
   * @param {string} email - User email address for registration
   */
  function registerUser(email) {
    // Prevent concurrent registration attempts
    if (isProcessing) {
      return;
    }

    isProcessing = true;
    const continueBtn = document.getElementById("continue-btn");

    // Execute registration request
    fetch(ajax_object.ajax_url, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams({
        action: "register_new_user",
        email: email,
        perform_login: "true",
        nonce: ajax_object.nonce,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          showRegistrationSuccess();
        } else {
          // Handle registration errors
          isProcessing = false;
          showRegistrationError(
            data.data.message ||
              "Registrierung fehlgeschlagen. Bitte versuchen Sie es erneut."
          );
        }
      })
      .catch((error) => {
        // Handle network errors
        isProcessing = false;
        console.error("Registration failed:", error);
        showRegistrationError(
          "Verbindungsfehler. Bitte versuchen Sie es erneut."
        );
      });

    // Set immediate loading state
    continueBtn.disabled = true;
    continueBtn.innerHTML =
      '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Registrieren...';
  }

  /**
   * Display registration success message and update UI
   * Shows confirmation and prepares for logged-in state
   */
  function showRegistrationSuccess() {
    const continueBtn = document.getElementById("continue-btn");
    const changeBtn = document.getElementById("change-btn");
    const registerSection = document.getElementById("username_email_invalid");

    // Hide action buttons
    continueBtn.style.display = "none";
    changeBtn.classList.add("fade");
    changeBtn.disabled = true;

    // Display success message
    const successHtml = `
            <div class="alert alert-success mt-3" id="registration-success">
                <h6 class="alert-heading">Konto erstellt & eingeloggt</h6>
                <p class="mb-0">Willkommen! Sie sind jetzt angemeldet. Ein Link zum Setzen eines neuen Passworts wurde an Ihre E-Mail-Adresse gesendet.</p>
            </div>
        `;

    registerSection.insertAdjacentHTML("beforeend", successHtml);

    updatePageTitle("register");
    updateLoggedInState();
  }

  /**
   * Update page elements to reflect logged-in state
   * Triggers custom events for other scripts to respond to authentication
   */
  function updateLoggedInState() {
    // Update account links in navigation
    const accountLinks = document.querySelectorAll('a[href*="my-account"]');
    accountLinks.forEach((link) => {
      link.textContent = "Mein Konto";
    });

    // Dispatch custom event for other modules
    const event = new CustomEvent("userLoggedIn", {
      detail: { via: "registration" },
    });
    document.dispatchEvent(event);
  }

  /**
   * Display registration error message with recovery options
   *
   * @param {string} message - Error message to display
   */
  function showRegistrationError(message) {
    const continueBtn = document.getElementById("continue-btn");
    const registerSection = document.getElementById("username_email_invalid");

    // Re-enable form controls
    continueBtn.disabled = false;
    continueBtn.textContent = "Registrieren";

    // Display error message
    const errorHtml = `
            <div class="alert alert-danger mt-3" id="registration-error">
                <p class="mb-0">${message}</p>
            </div>
        `;

    // Remove existing messages before adding new one
    const existingMessages = registerSection.querySelectorAll(
      "#registration-error, #registration-success"
    );
    existingMessages.forEach((msg) => msg.remove());

    registerSection.insertAdjacentHTML("beforeend", errorHtml);
  }

  // ====================== FORM RESET FUNCTIONALITY ======================

  /**
   * Reset form to initial state
   * Clears all progress and returns to email input step
   */
  function resetForm() {
    const emailInput = document.getElementById("username_email_check");
    const changeBtn = document.getElementById("change-btn");
    const loginSection = document.getElementById("username_email_valid");
    const registerSection = document.getElementById("username_email_invalid");
    const continueBtn = document.getElementById("continue-btn");

    // Reset form controls
    emailInput.disabled = false;
    changeBtn.classList.add("fade");

    // Hide all progress sections
    loginSection.classList.remove("show");
    registerSection.classList.remove("show");

    // Reset button states
    continueBtn.textContent = "Weiter";
    continueBtn.disabled = false;
    continueBtn.style.display = "block";

    // Clean up any displayed messages
    const messages = document.querySelectorAll(
      "#registration-error, #registration-success"
    );
    messages.forEach((msg) => msg.remove());

    // Reset state variables
    userExists = false;
    currentEmail = "";
    clearValidationMessage();
    updatePageTitle("initial");
  }

  // ====================== RATE LIMITING SYSTEM ======================

  /**
   * Start lockout countdown timer for rate-limited accounts
   * Displays remaining time and automatically re-enables form when expired
   *
   * @param {number} remainingSeconds - Seconds remaining in lockout period
   */
  function startLockoutCountdown(remainingSeconds) {
    const continueBtn = document.getElementById("continue-btn");
    const passwordInput = document.getElementById("password");

    /**
     * Recursive countdown function with automatic cleanup
     */
    const updateCountdown = () => {
      if (remainingSeconds <= 0) {
        // Lockout expired - re-enable form
        passwordInput.disabled = false;
        passwordInput.classList.remove("is-invalid");
        continueBtn.disabled = false;
        continueBtn.textContent = "Anmelden";
        clearPasswordValidation();
        return;
      }

      // Format remaining time for display
      const minutes = Math.floor(remainingSeconds / 60);
      const seconds = remainingSeconds % 60;
      const timeString =
        minutes > 0
          ? `${minutes}:${seconds.toString().padStart(2, "0")}`
          : `${seconds}`;

      // Update button and message
      continueBtn.textContent = `Gesperrt (${timeString})`;
      showPasswordValidation(
        `Account gesperrt. Verbleibende Zeit: ${timeString} Minuten`,
        "error"
      );

      // Continue countdown
      remainingSeconds--;
      setTimeout(updateCountdown, 1000);
    };

    updateCountdown();
  }

  /**
   * Handle registration form submission
   * Validates email and triggers registration process
   */
  function handleRegistration() {
    const email = document.getElementById("username_email_check").value.trim();

    if (!email) {
      showValidationMessage(
        "Bitte geben Sie eine E-Mail-Adresse ein.",
        "error"
      );
      return;
    }

    registerUser(email);
  }
});

/**
 * Future Enhancement Ideas:
 *
 * 1. Social login integration:
 *    - Google OAuth2 integration
 *    - Facebook Login support
 *    - Apple Sign In implementation
 *
 * 2. Enhanced security features:
 *    - Two-factor authentication support
 *    - CAPTCHA integration for suspicious activity
 *    - Device fingerprinting for security monitoring
 *
 * 3. Progressive Web App features:
 *    - Offline capability with service workers
 *    - Push notifications for account events
 *    - App-like installation prompts
 *
 * 4. Advanced UX improvements:
 *    - Password strength meter
 *    - Real-time username availability checking
 *    - Smart suggestions for email typos
 *    - Biometric authentication support
 *
 * 5. Analytics and monitoring:
 *    - Conversion funnel tracking
 *    - Error rate monitoring
 *    - Performance metrics collection
 *    - A/B testing framework integration
 */
