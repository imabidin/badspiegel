/**
 * Complete Modal System for Bootstrap 5
 *
 * A comprehensive modal system combining dynamic content loading, Bootstrap modal creation,
 * caching, preloading, and seamless user experience.
 *
 * @version 2.3.2
 *
 * Features:
 * - Dynamic modal creation with flexible options
 * - Dynamic content loading via AJAX (HTML & Images)
 * - Intelligent caching system with TTL
 * - Preloading for frequently used modals
 * - Smooth transitions and loading states
 * - ARIA compliance and accessibility
 * - Automatic tooltip management
 * - Custom button configuration
 * - Memory leak prevention
 * - Comprehensive error handling
 * - Debug controls for development
 *
 * Usage Examples:
 *
 * // Manual modal creation
 * createModal({
 *   id: 'my-modal',
 *   title: 'Modal Title',
 *   body: '<p>Content here</p>',
 *   footer: [
 *     { text: 'Close', class: 'btn-secondary', dismiss: true },
 *     { text: 'Save', class: 'btn-primary', onClick: handleSave }
 *   ]
 * });
 *
 * // Automatic content loading
 * <button data-modal-link="path/to/content"
 *         data-modal-title="Modal Title"
 *         data-modal-preload="true">Open HTML Modal</button>
 *
 * // Automatic image loading
 * <button data-modal-image="1207"
 *         data-modal-title="Image Title">Open Image Modal</button>
 */

// =============================================================================
// CONFIGURATION
// =============================================================================

/**
 * Enable/disable debug logging
 * Set to false for production environment
 * @type {boolean}
 */
const MODAL_DEBUG_ENABLED = false;

/**
 * Enable/disable content caching
 * Set to false to always fetch fresh content
 * @type {boolean}
 */
const MODAL_CACHE_ENABLED = false;

/**
 * Default modal configuration
 * @type {Object}
 */
const MODAL_DEFAULTS = {
  size: "lg",
  backdrop: true,
  keyboard: true,
  focus: true,
  classes: "",
  footer: [
    { text: "Schließen", class: "btn-dark", dismiss: true, key: "dismiss" },
  ],
};

// =============================================================================
// INTERNAL STATE
// =============================================================================

/**
 * Cache storage for loaded modal content
 * @type {Map<string, string>}
 */
const modalCache = new Map();

/**
 * Track currently loading modals to prevent duplicates
 * @type {Map<string, boolean>}
 */
const loadingStates = new Map();

// =============================================================================
// UTILITY FUNCTIONS
// =============================================================================

/**
 * Debug logger (only when debug is enabled)
 * @param {...any} args - Console arguments
 */
function modalDebugLog(...args) {
  if (MODAL_DEBUG_ENABLED) {
    console.log("[Modal Debug]", ...args);
  }
}

/**
 * Warning logger (only when debug is enabled)
 * @param {...any} args - Console arguments
 */
function modalDebugWarn(...args) {
  if (MODAL_DEBUG_ENABLED) {
    console.warn("[Modal Warning]", ...args);
  }
}

/**
 * Error logger (always active)
 * @param {...any} args - Console arguments
 */
function modalErrorLog(...args) {
  console.error("[Modal Error]", ...args);
}

/**
 * Generate unique modal ID
 * @returns {string} Unique modal ID
 */
function generateModalId() {
  return `modal-${Date.now()}-${Math.random().toString(36).slice(2, 10)}`;
}

/**
 * Validate modal options
 * @param {Object} options - Modal options to validate
 * @returns {boolean} True if valid
 */
function validateModalOptions(options) {
  if (!options || typeof options !== "object") {
    modalErrorLog("Modal options must be an object");
    return false;
  }

  if (options.footer && !Array.isArray(options.footer)) {
    modalErrorLog("Modal footer must be an array of button configurations");
    return false;
  }

  return true;
}

/**
 * Sanitize HTML content to prevent XSS
 * @param {string} content - Content to sanitize
 * @returns {string} Sanitized content
 */
function sanitizeContent(content) {
  if (typeof content !== "string") {
    return String(content);
  }

  // Basic XSS prevention - in production, consider using a proper sanitization library
  return content.replace(
    /<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi,
    ""
  );
}

/**
 * Retrieve content from cache
 * @param {string} key - Cache key
 * @returns {string|null} Cached content or null if not found/disabled
 */
function getCachedContent(key) {
  if (!MODAL_CACHE_ENABLED) {
    modalDebugLog(`Cache disabled - skipping cache check for: ${key}`);
    return null;
  }

  const cached = modalCache.get(key);
  if (cached) {
    modalDebugLog(`Cache Hit: ${key}`);
  }
  return cached || null;
}

/**
 * Store content in cache with automatic cleanup
 * @param {string} key - Cache key
 * @param {string} content - Content to cache
 * @param {number} ttl - Time to live in milliseconds (default: 5 minutes)
 */
function setCachedContent(key, content, ttl = 300000) {
  if (!MODAL_CACHE_ENABLED) {
    modalDebugLog(`Cache disabled - skipping caching for: ${key}`);
    return;
  }

  modalCache.set(key, content);
  modalDebugLog(`Cache Set: ${key} (TTL: ${ttl / 1000}s)`);

  // Auto-cleanup after TTL expires
  setTimeout(() => {
    if (modalCache.has(key)) {
      modalCache.delete(key);
      modalDebugLog(`Cache Expired: ${key}`);
    }
  }, ttl);
}

/**
 * Clear specific cache entry
 * @param {string} key - Cache key to clear
 */
function clearCachedContent(key) {
  if (modalCache.has(key)) {
    modalCache.delete(key);
    modalDebugLog(`Cache Cleared: ${key}`);
  }
}

/**
 * Clear entire cache
 */
function clearAllCache() {
  const cacheSize = modalCache.size;
  modalCache.clear();
  modalDebugLog(`Cache completely cleared (${cacheSize} entries)`);
}

/**
 * Fetch with timeout and abort capability
 * @param {string} url - URL to fetch
 * @param {Object} options - Fetch options
 * @param {number} timeout - Timeout in milliseconds
 * @returns {Promise<Response>} Fetch promise with timeout
 */
function fetchWithTimeout(url, options = {}, timeout = 10000) {
  const controller = new AbortController();
  const timeoutId = setTimeout(() => {
    modalDebugLog(`🕒 Request timeout after ${timeout}ms for: ${url}`);
    controller.abort();
  }, timeout);

  return fetch(url, { ...options, signal: controller.signal })
    .catch(error => {
      // Handle different abort scenarios
      if (error.name === 'AbortError') {
        throw new Error(`Request timeout after ${timeout/1000}s`);
      }
      // Re-throw other errors
      throw error;
    })
    .finally(() => {
      clearTimeout(timeoutId);
    });
}

/**
 * Check if AJAX dependencies are available
 * @returns {boolean} True if AJAX is available
 */
function isAjaxAvailable() {
  return !!(window.myAjaxData?.modalFileNonce && window.myAjaxData?.ajaxUrl);
}

/**
 * Create FormData for AJAX requests with common fields
 * @param {string} action - WordPress action name
 * @param {Object} data - Additional data fields
 * @returns {FormData} Prepared FormData object
 */
function createAjaxFormData(action, data = {}) {
  const formData = new FormData();
  formData.append("action", action);
  formData.append("nonce", window.myAjaxData.modalFileNonce);

  // Add additional data fields
  Object.entries(data).forEach(([key, value]) => {
    formData.append(key, value);
  });

  return formData;
}

/**
 * Perform AJAX request with standardized error handling
 * @param {string} action - WordPress action name
 * @param {Object} data - Request data
 * @param {number} timeout - Request timeout
 * @returns {Promise<any>} Promise resolving to response data
 */
async function performAjaxRequest(action, data = {}, timeout = 10000) {
  const formData = createAjaxFormData(action, data);

  modalDebugLog(`Fetching ${action} with data:`, data);

  try {
    const response = await fetchWithTimeout(
      window.myAjaxData.ajaxUrl,
      {
        method: "POST",
        body: formData,
        credentials: "same-origin",
      },
      timeout
    );

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const result = await response.json();

    if (!result.success) {
      throw new Error(result.data || "Request failed");
    }

    return result.data;

  } catch (error) {
    // Enhanced error handling for different scenarios
    if (error.name === 'AbortError') {
      throw new Error(`Request timeout after ${timeout/1000}s`);
    }

    if (error.name === 'TypeError' && error.message.includes('fetch')) {
      throw new Error('Network connection failed');
    }

    modalDebugLog(`❌ AJAX Error (${action}):`, error);
    throw error; // Re-throw with original or enhanced message
  }
}

// =============================================================================
// INITIALIZATION & EVENT HANDLING
// =============================================================================

/**
 * Initialize modal system when DOM is ready
 */
document.addEventListener("DOMContentLoaded", initModalSystem);

/**
 * Set up event listeners and initialize preloading
 */
function initModalSystem() {
  // Attach event listeners
  document.addEventListener("click", handleModalClick);
  document.addEventListener("keydown", handleModalKeydown);

  // Start intelligent preloading for marked modals
  preloadMarkedModals();

  modalDebugLog(
    `Modal system initialized - Cache: ${MODAL_CACHE_ENABLED ? "ON" : "OFF"}, Debug: ${
      MODAL_DEBUG_ENABLED ? "ON" : "OFF"
    }`
  );

  // Expose debug utilities (development only)
  if (MODAL_DEBUG_ENABLED && typeof window !== "undefined") {
    window.modalDebug = {
      clearCache: clearAllCache,
      clearItem: clearCachedContent,
      cacheStatus: () => {
        modalDebugLog(
          `Cache Status: ${modalCache.size} entries`,
          Array.from(modalCache.keys())
        );
      },
      toggleCache: () => {
        modalDebugLog(
          `Cache is ${
            MODAL_CACHE_ENABLED ? "enabled" : "disabled"
          }. Change MODAL_CACHE_ENABLED constant to modify.`
        );
      },
    };
    modalDebugLog("Debug utilities available: window.modalDebug");
  }
}

/**
 * Handle click events for modal trigger elements
 * @param {Event} event - The click event
 */
function handleModalClick(event) {
  const target = event.target.closest("[data-modal-link], [data-modal-image]");
  if (!target) return;

  event.preventDefault();

  // Check for image modal
  if (target.hasAttribute('data-modal-image')) {
    showImageModal(target);
  } else {
    showHtmlModal(target);
  }
}

/**
 * Handle keyboard events for modal trigger elements
 * @param {Event} event - The keydown event
 */
function handleModalKeydown(event) {
  if (event.key !== "Enter") return;

  const target = event.target.closest("[data-modal-link], [data-modal-image]");
  if (!target) return;

  event.preventDefault();

  // Check for image modal
  if (target.hasAttribute('data-modal-image')) {
    showImageModal(target);
  } else {
    showHtmlModal(target);
  }
}

// =============================================================================
// PRELOADING SYSTEM
// =============================================================================

/**
 * Find and preload modals marked with data-modal-preload="true"
 */
function preloadMarkedModals() {
  const preloadElements = document.querySelectorAll(
    '[data-modal-link][data-modal-preload="true"]'
  );

  if (preloadElements.length === 0) {
    modalDebugLog("No modals marked for preloading");
    return;
  }

  modalDebugLog(
    `Preloading ${preloadElements.length} marked modal(s)... (Cache: ${
      MODAL_CACHE_ENABLED ? "ON" : "OFF"
    })`
  );

  preloadElements.forEach((element, index) => {
    const baseName = element.getAttribute("data-modal-link");

    if (!baseName || !/^[a-z0-9\-_\/]+$/i.test(baseName)) {
      modalDebugWarn(`Invalid preload path: ${baseName}`);
      return;
    }

    // Staggered preloading (every 500ms)
    setTimeout(() => {
      preloadModal(baseName, element);
    }, index * 500);
  });
}

/**
 * Preload a single modal's content
 * @param {string} baseName - Modal content path
 * @param {HTMLElement} element - Original trigger element for context
 */
async function preloadModal(baseName, element) {
  // Skip if already cached or currently loading
  const cachedContent = getCachedContent(baseName);
  if (cachedContent || loadingStates.has(baseName)) {
    modalDebugLog(
      `Preload skipped for ${baseName}: already ${
        cachedContent ? "cached" : "loading"
      }`
    );
    return;
  }

  // Verify AJAX availability
  if (!isAjaxAvailable()) {
    modalDebugWarn(`Preload skipped for ${baseName}: AJAX not available`);
    return;
  }

  try {
    loadingStates.set(baseName, true);

    modalDebugLog(`🔄 Preloading: ${baseName}`);
    const content = await loadModalContent(baseName, true);

    // Cache for 10 minutes (longer than regular modals)
    setCachedContent(baseName, content, 600000);

    modalDebugLog(`✅ Preloaded: ${baseName}`);

    // Mark element as preloaded for visual indication
    element.setAttribute("data-modal-preloaded", "true");
    element.classList.add("modal-preloaded");
  } catch (error) {
    modalDebugWarn(`❌ Preload failed for ${baseName}:`, error.message);
    element.setAttribute("data-modal-preload-failed", "true");
  } finally {
    loadingStates.delete(baseName);
  }
}

// =============================================================================
// CONTENT LOADING FUNCTIONS
// =============================================================================

/**
 * Load and display image modal with WordPress shortcode
 * @param {HTMLElement} element - Element with data-modal-image attribute
 */
async function showImageModal(element) {
  const imageId = element.getAttribute("data-modal-image");
  const modalTitle = element.getAttribute("data-modal-title") || "Bild";
  const modalId = `image-modal-${imageId}-${Date.now()}`;

  // Validate image ID
  if (!imageId || !/^\d+$/.test(imageId)) {
    modalErrorLog("Invalid image ID:", imageId);
    showErrorModal(modalTitle, "Ungültige Bild-ID.");
    return;
  }

  try {
    // Verify AJAX dependencies
    if (!isAjaxAvailable()) {
      throw new Error("AJAX nicht verfügbar");
    }

    modalDebugLog(`Loading image modal for ID: ${imageId}`);

    // Create modal with loading content first
    const loadingContent = createImageLoadingContent();

    const modal = createModal({
      id: modalId,
      title: modalTitle,
      body: loadingContent,
      size: 'xl',
      footer: [{ text: "Schließen", class: "btn-dark", dismiss: true }],
    });

    try {
      // Load image via AJAX using WordPress shortcode
      const imageContent = await loadImageContent(imageId);

      // Update modal content with loaded image
      updateModalContent(modalId, imageContent);

      modalDebugLog(`Image modal loaded successfully: ${imageId}`);
    } catch (error) {
      modalErrorLog("Image load failed:", error);
      updateModalContent(
        modalId,
        `<div class="alert alert-danger mb-0">Fehler beim Laden des Bildes: ${error.message}</div>`
      );
    }
  } catch (error) {
    modalErrorLog("Image modal creation failed:", error);
    showErrorModal(modalTitle, `Fehler: ${error.message}`);
  }
}

/**
 * Load image content via AJAX using WordPress shortcode
 * @param {string} imageId - WordPress attachment ID
 * @returns {Promise<string>} Promise resolving to image HTML
 */
async function loadImageContent(imageId) {
  return performAjaxRequest("load_image_modal", { image_id: imageId }, 10000);
}

/**
 * Load and display modal content with seamless UX
 * @param {HTMLElement} element - Element with data-modal-link attribute
 */
async function showHtmlModal(element) {
  const baseName = element.getAttribute("data-modal-link");
  const modalTitle = element.getAttribute("data-modal-title") || "Information";
  const modalId = `modal-${Date.now()}`;

  // Validate modal path
  if (!baseName || !/^[a-z0-9\-_\/]+$/i.test(baseName)) {
    modalErrorLog("Invalid path format:", baseName);
    showErrorModal(modalTitle, "Invalid path format.");
    return;
  }

  try {
    // Verify AJAX dependencies
    if (!isAjaxAvailable()) {
      throw new Error("AJAX configuration not available.");
    }

    // Prevent duplicate loading
    if (loadingStates.has(baseName)) {
      modalDebugLog(`Modal already loading: ${baseName}`);
      return;
    }

    // Check cache for instant display
    const cachedContent = getCachedContent(baseName);
    if (cachedContent) {
      showModal(modalId, modalTitle, cachedContent);

      if (element.hasAttribute("data-modal-preloaded")) {
        modalDebugLog(`⚡ Instant load (preloaded): ${baseName}`);
      } else {
        modalDebugLog(`⚡ Instant load (cached): ${baseName}`);
      }
      return;
    }

    loadingStates.set(baseName, true);

    // Create modal with loading content
    const loadingContent = createLoadingContent();

    const modal = createModal({
      id: modalId,
      title: modalTitle,
      body: loadingContent,
      footer: [{ text: "Schließen", class: "btn-dark", dismiss: true }],
    });

    try {
      modalDebugLog(`🔄 Loading: ${baseName}`);

      // Fetch content via AJAX
      const content = await loadModalContent(baseName);

      // Cache for 5 minutes
      setCachedContent(baseName, content, 300000);

      // Replace content with smooth transition
      updateModalContent(modalId, content);

      modalDebugLog(`✅ Loaded: ${baseName}`);
    } catch (error) {
      // Display error in same modal
      const errorContent = `<div class="alert alert-danger mb-0">Loading error: ${error.message}</div>`;
      updateModalContent(modalId, errorContent);
      modalErrorLog(`❌ Load failed: ${baseName}`, error);
    } finally {
      loadingStates.delete(baseName);
    }
  } catch (error) {
    modalErrorLog("Modal load failed:", error);
    showErrorModal(modalTitle, `Loading error: ${error.message}`);
    loadingStates.delete(baseName);
  }
}

/**
 * Load modal content via AJAX
 * @param {string} baseName - Content path identifier
 * @param {boolean} isPreload - Whether this is a preload request
 * @returns {Promise<string>} Promise resolving to modal content
 */
async function loadModalContent(baseName, isPreload = false) {
  const timeout = isPreload ? 5000 : 15000; // Increased timeout for main requests

  modalDebugLog(`🔄 Starting AJAX request for: ${baseName} (timeout: ${timeout}ms)`);

  // Check AJAX availability first
  if (!isAjaxAvailable()) {
    throw new Error('AJAX system not initialized');
  }

  // Minimal context data - only what's really needed
  const contextData = {
    file_name: baseName,
    current_url: window.location.href,
    product_id: null
  };

  // Try to get product ID - prioritize the most reliable method
  if (typeof wc_single_product_params !== 'undefined' && wc_single_product_params.post_id) {
    // Method 1: WooCommerce JavaScript params (most reliable)
    contextData.product_id = parseInt(wc_single_product_params.post_id);
  } else {
    // Fallback methods only if WooCommerce params not available

    // Method 2: Add-to-cart button (very reliable)
    const addToCartBtn = document.querySelector('button[name="add-to-cart"], input[name="add-to-cart"]');
    if (addToCartBtn && addToCartBtn.value) {
      contextData.product_id = parseInt(addToCartBtn.value);
    } else {
      // Method 3: Body class (reliable for single products)
      if (document.body.classList.contains('single-product')) {
        const bodyClasses = document.body.className.split(' ');
        const postIdClass = bodyClasses.find(cls => cls.startsWith('postid-'));
        if (postIdClass) {
          contextData.product_id = parseInt(postIdClass.replace('postid-', ''));
        }
      }
    }
  }

  return performAjaxRequest("load_modal_file", contextData, timeout);
}

// =============================================================================
// UI HELPER FUNCTIONS
// =============================================================================

/**
 * Create loading spinner content with customizable parameters
 * @param {Object} options - Loading content options
 * @param {string} [options.spinnerText='Loading...'] - Spinner aria-label text
 * @param {string} [options.description='Inhalt wird geladen...'] - Loading description text
 * @returns {string} Loading HTML content
 */
function createLoadingContent(options = {}) {
  const {
    spinnerText = 'Lädt...',
    description = 'Inhalt wird geladen...'
  } = options;

  return `
        <div class="d-flex flex-column justify-content-center align-items-center" style="min-height: 200px;">
            <div class="spinner-border text-info mb-3" role="status" style="width: 2rem; height: 2rem;">
                <span class="visually-hidden">${spinnerText}</span>
            </div>
            <p class="text-muted mb-0">${description}</p>
        </div>
    `;
}

/**
 * Create image loading spinner content
 * @returns {string} Image loading HTML content
 */
function createImageLoadingContent() {
  return createLoadingContent({
    spinnerText: 'Bild lädt...',
    description: 'Bild wird geladen...'
  });
}

/**
 * Update modal content with smooth transition
 * @param {string} modalId - Target modal ID
 * @param {string} newContent - New content to display
 */
function updateModalContent(modalId, newContent) {
  const modalBody = document.querySelector(`#${modalId} .modal-body`);

  if (!modalBody) {
    modalDebugWarn(`Modal body not found for: ${modalId}`);
    return;
  }

  // Smooth fade-out/fade-in transition
  modalBody.style.transition = "opacity 0.3s ease-in-out";
  modalBody.style.opacity = "0";

  setTimeout(() => {
    modalBody.innerHTML = newContent;
    modalBody.style.opacity = "1";
    modalDebugLog(`Content updated for modal: ${modalId}`);
  }, 300);
}

/**
 * Display modal using createModal function
 * @param {string} id - Modal ID
 * @param {string} title - Modal title
 * @param {string} content - Modal content
 */
function showModal(id, title, content) {
  createModal({
    id: id,
    title: title,
    body: content,
    footer: [{ text: "Schließen", class: "btn-dark", dismiss: true }],
  });
}

/**
 * Display error modal with consistent styling
 * @param {string} title - Modal title
 * @param {string} message - Error message
 */
function showErrorModal(title, message) {
  showModal(
    `error-${Date.now()}`,
    title,
    `<div class="alert alert-danger mb-0">${message}</div>`
  );
}

/**
 * Create and display a Bootstrap modal with advanced configuration
 *
 * @param {Object} options - Modal configuration options
 * @param {string} [options.id] - Modal ID (auto-generated if not provided)
 * @param {string} [options.title='Modal Title'] - Modal title
 * @param {string} [options.body='<p>Modal content</p>'] - Modal body content
 * @param {Array} [options.footer] - Array of button configurations
 * @param {string} [options.size='lg'] - Modal size (sm, lg, xl)
 * @param {string} [options.classes=''] - Additional CSS classes
 * @param {boolean|string} [options.backdrop=true] - Backdrop behavior
 * @param {boolean} [options.keyboard=true] - Keyboard interaction
 * @param {boolean} [options.focus=true] - Auto-focus behavior
 * @returns {HTMLElement} Modal DOM element
 *
 * @example
 * // Basic modal
 * createModal({
 *   title: 'Confirmation',
 *   body: '<p>Are you sure?</p>'
 * });
 *
 * @example
 * // Advanced modal with custom buttons
 * createModal({
 *   title: 'Delete Item',
 *   body: '<p>This action cannot be undone.</p>',
 *   footer: [
 *     { text: 'Cancel', class: 'btn-secondary', dismiss: true },
 *     { text: 'Delete', class: 'btn-danger', onClick: handleDelete }
 *   ]
 * });
 */

// =============================================================================
// BOOTSTRAP MODAL CREATION
// =============================================================================

function createModal(options = {}) {
  try {
    // Validate Bootstrap availability
    const ModalConstructor = window.bootstrap?.Modal;
    if (!ModalConstructor) {
      throw new Error(
        "Bootstrap Modal is not available on window.bootstrap.Modal"
      );
    }

    // Validate and merge options with defaults
    if (!validateModalOptions(options)) {
      throw new Error("Invalid modal options provided");
    }

    const config = { ...MODAL_DEFAULTS, ...options };
    const {
      id = generateModalId(),
      title = "Modal Title",
      body = "<p>Modal content</p>",
      footer,
      size,
      classes,
      backdrop,
      keyboard,
      focus,
    } = config;

    modalDebugLog("Creating modal with ID:", id);

    // Check for existing modal with same ID
    const existingModal = document.getElementById(id);
    if (existingModal) {
      modalErrorLog(`Modal with ID '${id}' already exists`);
      existingModal.remove();
    }

    // Sanitize content
    const sanitizedTitle = sanitizeContent(title);
    const sanitizedBody = sanitizeContent(body);

    // Create modal element
    const modal = createModalElement(
      id,
      sanitizedTitle,
      sanitizedBody,
      size,
      classes
    );

    // Find the best container for the modal
    const productContainer = document.querySelector(
      'main > div[id*="product-"]'
    );
    const targetContainer = productContainer || document.body;

    // Append to the determined container
    targetContainer.appendChild(modal);

    // Set up ARIA compliance observer
    const ariaObserver = setupAriaObserver(modal);

    // Create footer buttons
    createFooterButtons(modal, footer, id);

    // Initialize Bootstrap modal
    const bsModal = new ModalConstructor(modal, {
      backdrop,
      keyboard,
      focus,
    });

    // Set up event listeners
    setupModalEvents(modal, bsModal, ariaObserver);

    // Show modal
    bsModal.show();

    modalDebugLog("Modal created and shown:", id);
    return modal;
  } catch (error) {
    modalErrorLog("Failed to create modal:", error.message);
    throw error;
  }
}

/**
 * Create modal DOM element
 * @param {string} id - Modal ID
 * @param {string} title - Modal title
 * @param {string} body - Modal body content
 * @param {string} size - Modal size
 * @param {string} classes - Additional CSS classes
 * @returns {HTMLElement} Modal element
 */
function createModalElement(id, title, body, size, classes) {
  const modal = document.createElement("div");
  modal.id = id;
  modal.className = "modal fade";
  modal.tabIndex = -1;
  modal.setAttribute("role", "dialog");
  modal.setAttribute("aria-labelledby", `${id}-label`);
  modal.setAttribute("aria-hidden", "true");

  modal.innerHTML = `
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-${size} ${classes}" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="${id}-label">${title}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">${body}</div>
                <div class="modal-footer" id="${id}-footer"></div>
            </div>
        </div>
    `;

  return modal;
}

/**
 * Set up ARIA compliance observer
 * @param {HTMLElement} modal - Modal element
 * @returns {MutationObserver} Observer instance
 */
function setupAriaObserver(modal) {
  const observer = new MutationObserver((records) => {
    records.forEach((record) => {
      if (
        record.attributeName === "aria-hidden" &&
        modal.hasAttribute("aria-hidden")
      ) {
        modal.removeAttribute("aria-hidden");
        modalDebugLog("Removed aria-hidden attribute for accessibility");
      }
    });
  });

  // Observe only aria-hidden attribute to minimize overhead
  observer.observe(modal, {
    attributes: true,
    attributeFilter: ["aria-hidden"],
  });

  return observer;
}

/**
 * Create footer buttons based on configuration
 * @param {HTMLElement} modal - Modal element
 * @param {Array} footerConfig - Footer button configuration
 * @param {string} modalId - Modal ID
 */
function createFooterButtons(modal, footerConfig, modalId) {
  const footerEl = modal.querySelector(`#${modalId}-footer`);

  if (!Array.isArray(footerConfig) || footerConfig.length === 0) {
    footerEl.style.display = "none";
    return;
  }

  footerConfig.forEach((buttonConfig, index) => {
    if (!buttonConfig || typeof buttonConfig.text !== "string") {
      modalErrorLog(`Invalid button configuration at index ${index}`);
      return;
    }

    const button = createFooterButton(buttonConfig, modalId);
    footerEl.appendChild(button);
  });

  modalDebugLog(`Created ${footerConfig.length} footer buttons`);
}

/**
 * Create individual footer button
 * @param {Object} buttonConfig - Button configuration
 * @param {string} modalId - Modal ID
 * @returns {HTMLElement} Button element
 */
function createFooterButton(buttonConfig, modalId) {
  const button = document.createElement("button");
  button.type = "button";
  button.className = `btn ${buttonConfig.class || "btn-secondary"}`;
  button.textContent = buttonConfig.text;

  // Set button attributes
  if (buttonConfig.id) {
    button.id = buttonConfig.id;
  }

  if (buttonConfig.disabled) {
    button.disabled = true;
  }

  if (buttonConfig.dismiss) {
    button.setAttribute("data-bs-dismiss", "modal");
  }

  // Add click handler
  if (typeof buttonConfig.onClick === "function") {
    button.addEventListener("click", (event) => {
      try {
        const modal = document.getElementById(modalId);
        const bsModal = window.bootstrap.Modal.getInstance(modal);
        buttonConfig.onClick(bsModal, event, modal);
      } catch (error) {
        modalErrorLog("Button click handler error:", error.message);
      }
    });
  }

  return button;
}

/**
 * Set up modal event listeners
 * @param {HTMLElement} modal - Modal element
 * @param {Object} bsModal - Bootstrap modal instance
 * @param {MutationObserver} ariaObserver - ARIA observer
 */
function setupModalEvents(modal, bsModal, ariaObserver) {
  // Handle tooltip cleanup when modal is shown
  modal.addEventListener("shown.bs.modal", () => {
    hideActiveTooltips();
    modalDebugLog("Modal shown, tooltips hidden");
  });

  // Handle modal cleanup when hidden
  modal.addEventListener("hidden.bs.modal", () => {
    cleanupModal(modal, ariaObserver);
    modalDebugLog("Modal hidden and cleaned up");
  });

  // Handle modal disposal
  modal.addEventListener("dispose.bs.modal", () => {
    modalDebugLog("Modal disposed");
  });
}

/**
 * Hide all active tooltips
 */
function hideActiveTooltips() {
  const Tooltip = window.bootstrap?.Tooltip;
  if (!Tooltip) return;

  // Find all potential tooltip triggers
  const tooltipSelectors = [
    '[data-bs-toggle="tooltip"]',
    '[data-toggle="tooltip"]',
    "[title]",
  ];

  tooltipSelectors.forEach((selector) => {
    document.querySelectorAll(selector).forEach((element) => {
      const tooltipInstance = Tooltip.getInstance(element);
      if (tooltipInstance) {
        tooltipInstance.hide();
      }
    });
  });
}

/**
 * Clean up modal resources
 * @param {HTMLElement} modal - Modal element
 * @param {MutationObserver} ariaObserver - ARIA observer
 */
function cleanupModal(modal, ariaObserver) {
  try {
    // Disconnect observer
    if (ariaObserver) {
      ariaObserver.disconnect();
    }

    // Remove modal from DOM
    if (modal && modal.parentNode) {
      modal.parentNode.removeChild(modal);
    }

    modalDebugLog("Modal cleanup completed");
  } catch (error) {
    modalErrorLog("Error during modal cleanup:", error.message);
  }
}

// =============================================================================
// GLOBAL EXPOSURE
// =============================================================================

/**
 * Expose modal functions globally
 */
if (typeof window !== "undefined") {
  // Main functions
  window.createModal = createModal;

  // Content loading functions
  window.showHtmlModal = showHtmlModal;
  window.showImageModal = showImageModal;

  // Cache management
  window.clearModalCache = clearAllCache;

  // Expose debug utilities in development
  if (MODAL_DEBUG_ENABLED) {
    window.modalDebug = {
      clearCache: clearAllCache,
      clearItem: clearCachedContent,
      cacheStatus: () => {
        modalDebugLog(
          `Cache Status: ${modalCache.size} entries`,
          Array.from(modalCache.keys())
        );
      },
      cleanup: () => {
        // Clean up all modals
        document.querySelectorAll(".modal").forEach((modal) => {
          const bsModal = window.bootstrap?.Modal?.getInstance(modal);
          if (bsModal) {
            bsModal.dispose();
          }
          modal.remove();
        });
        modalDebugLog("All modals cleaned up");
      },
    };
    modalDebugLog("Debug utilities available: window.modalDebug");
  }
}