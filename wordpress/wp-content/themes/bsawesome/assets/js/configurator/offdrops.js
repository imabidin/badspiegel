/**
 * Offdrop Component - Responsive Toggle between Dropdown and Offcanvas
 *
 * This module provides a responsive solution that toggles between a dropdown
 * (on desktop) and an offcanvas (on mobile) based on Bootstrap 5 components.
 * Includes MutationObserver to track dynamic attribute changes and search
 * functionality for option groups.
 *
 * @version 2.3.0
 * @package Configurator
 *
 * @todo Offdrops desktop, dropdowns are not navigable through keyboard
 */

/**
 * Configuration constants for responsive behavior
 */
const BREAKPOINT = 768; // Bootstrap md breakpoint in pixels
const MD_BREAKPOINT = 768; // Bootstrap md breakpoint for dropdown width calculations
const managers = []; // Stores all component instances for lifecycle management
let skipNextClick = false; // Flag to prevent immediate reopening after modal close

/**
 * Initialize all offdrop components when DOM is ready
 */
document.addEventListener("DOMContentLoaded", () => {
  /**
   * 1. Handle modal hide events to prevent dropdown closing after modal close
   * This prevents unwanted interaction between Bootstrap modals and dropdowns
   */
  document.addEventListener("hide.bs.modal", () => {
    skipNextClick = true;
  });

  /**
   * 2. Initialize and manage all offdrop button components
   * Creates responsive dropdown/offcanvas pairs for each button
   */
  document
    .querySelectorAll('button[data-target="offdrop"]')
    .forEach((button) => {
      const offcanvasId = button.dataset.offcanvasId;
      const dropdownId = button.dataset.dropdownId;
      const group = button.closest(".option-offdrop");
      if (!group) return;

      // Get required DOM elements
      const source = group.querySelector(".offdrop-body");
      const offcanvasEl = document.getElementById(offcanvasId);
      const dropdownEl = document.getElementById(dropdownId);
      if (!source || !offcanvasEl || !dropdownEl) return;

      const offcanvasBody = offcanvasEl.querySelector(".offcanvas-body");
      const dropdownBody = dropdownEl.querySelector(".dropdown-body");
      if (!offcanvasBody || !dropdownBody) return;

      const valueLabel = group.querySelector(".option-value-label");

      // Extract and clear source nodes for dynamic placement
      const nodes = Array.from(source.children);
      source.innerHTML = "";

      // Initialize Bootstrap component instances
      const offcanvasInst =
        bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
      const dropdownInst = bootstrap.Dropdown.getOrCreateInstance(button);
      dropdownInst._menu = dropdownEl; // Workaround for separate menu element

      /**
       * Manager instance containing all component state and methods
       */
      const mgr = {
        group,
        nodes,
        offcanvasBody,
        dropdownBody,
        dropdownEl,
        offcanvasInst,
        dropdownInst,
        isDesktop: null,
        originalValue: null,
        valueChanged: false,
        updateValueLabel,
        observer: null,
        setupObserver,
      };

      /**
       * Updates the value label and button state based on current selection
       *
       * @param {HTMLInputElement} input - The selected input element
       */
      function updateValueLabel(input) {
        const selLabel = input.closest("label");
        const text = input.dataset.label || "";
        let price = input.dataset.price || "0";

        const isNone = input.value === "";
        const isOverride =
          isNone && selLabel.classList.contains("use-first-image-for-none");
        const treatAsValue = !isNone || isOverride;

        if (treatAsValue) {
          // For none-override cases, hide the price display
          const displayPrice = isOverride ? "" : price;
          valueLabel.textContent =
            displayPrice && displayPrice !== "0"
              ? `${text} (+${displayPrice} €)`
              : text;
          valueLabel.classList.add("fade", "show");
        } else {
          valueLabel.textContent = "";
          valueLabel.classList.remove("show");
        }

        // Update button visual state
        button.classList.remove(
          "no-selection",
          "on-selection",
          "yes-selection"
        );
        button.classList.add(treatAsValue ? "yes-selection" : "no-selection");

        mgr.valueChanged = true;
      }

      /**
       * Sets up MutationObserver to track dynamic changes to data attributes
       * Monitors changes to data-price, data-label, and value attributes
       */
      function setupObserver() {
        const config = {
          attributes: true,
          attributeFilter: ["data-price", "data-label", "value"],
          subtree: true,
        };

        const callback = (mutations) => {
          mutations.forEach((mutation) => {
            if (
              mutation.type === "attributes" &&
              mutation.target.matches(
                'input[type="radio"], input[type="checkbox"]'
              )
            ) {
              const input = mutation.target;
              if (input.checked) {
                mgr.updateValueLabel(input);
              }
            }
          });
        };

        // Cleanup existing observer before creating new one
        if (mgr.observer) mgr.observer.disconnect();
        mgr.observer = new MutationObserver(callback);
        mgr.observer.observe(mgr.dropdownBody, config);
        mgr.observer.observe(mgr.offcanvasBody, config);
      }

      // Initialize the mutation observer
      mgr.setupObserver();

      /**
       * Handle input change events for radio buttons and checkboxes
       */
      mgr.group.addEventListener("change", (e) => {
        if (e.target.matches('input[type="radio"], input[type="checkbox"]')) {
          // Update active state on label buttons
          mgr.group
            .querySelectorAll("label.btn")
            .forEach((lbl) => lbl.classList.remove("active"));
          const selLbl = e.target.closest("label");
          if (selLbl) selLbl.classList.add("active");

          // Update value label with slight delay for smooth UX
          setTimeout(() => mgr.updateValueLabel(e.target), 150);
        }
      });

      /**
       * Offcanvas event handlers
       */

      // Offcanvas show event
      offcanvasEl.addEventListener("show.bs.offcanvas", () => {
        button.classList.add("on-selection");
        const checked = mgr.offcanvasBody.querySelector("input:checked");
        mgr.originalValue = checked ? checked.value : "";
        mgr.valueChanged = false;
      });

      // Offcanvas hide event
      offcanvasEl.addEventListener("hide.bs.offcanvas", () => {
        const checked = mgr.offcanvasBody.querySelector("input:checked");
        const newValue = checked ? checked.value : "";
        if (!mgr.valueChanged) {
          button.classList.remove("on-selection");
          button.classList.add(
            newValue !== "" ? "yes-selection" : "no-selection"
          );
        }
        mgr.valueChanged = false;
      });

      /**
       * Dropdown event handlers
       */
      // Dropdown show event
      button.addEventListener("show.bs.dropdown", () => {
        button.classList.add("on-selection");
        const checked = mgr.dropdownBody.querySelector("input:checked");
        mgr.originalValue = checked ? checked.value : "";
        mgr.valueChanged = false;

        // Set responsive max-height and overflow for dropdown
        mgr.dropdownBody.style.maxHeight = "69vh";
        mgr.dropdownBody.style.overflowY = "auto";
        mgr.dropdownBody.style.overflowX = "hidden";

        // Constrain dropdown width to trigger button width
        mgr.updateDropdownWidth();
      });

      /**
       * Update dropdown width to match trigger button width
       */
      mgr.updateDropdownWidth = () => {
        const triggerWidth = button.offsetWidth;
        mgr.dropdownEl.style.minWidth = `${triggerWidth}px`;
        mgr.dropdownEl.style.maxWidth = `${triggerWidth}px`;
        mgr.dropdownEl.style.width = `${triggerWidth}px`;
      };

      // Dropdown hide event
      button.addEventListener("hide.bs.dropdown", () => {
        const checked = mgr.dropdownBody.querySelector("input:checked");
        const newValue = checked ? checked.value : "";
        if (!mgr.valueChanged) {
          button.classList.remove("on-selection");
          button.classList.add(
            newValue !== "" ? "yes-selection" : "no-selection"
          );
        }
        mgr.valueChanged = false;
      });

      /**
       * Main button click handler for responsive toggle
       */
      button.addEventListener("click", (e) => {
        e.preventDefault();
        if (mgr.isDesktop) {
          mgr.dropdownInst.toggle();
        } else {
          mgr.offcanvasInst.toggle();
        }
      });

      /**
       * Dropdown input change events with auto-close and focus management
       */
      mgr.dropdownBody.addEventListener("change", (e) => {
        if (e.target.matches('input[type="radio"], input[type="checkbox"]')) {
          mgr.dropdownInst.hide();

          // Only for trusted user events (not programmatic)
          if (e.isTrusted) {
            // Set visual feedback and focus after transition
            setTimeout(() => {
              button.classList.add("just-selected");
              button.focus();
            }, 150);

            // Remove visual feedback on blur
            button.addEventListener(
              "blur",
              () => button.classList.remove("just-selected"),
              { once: true }
            );
          }
        }
      });

      /**
       * Offcanvas input change events with auto-close and focus management
       */
      mgr.offcanvasBody.addEventListener("change", (e) => {
        if (e.target.matches('input[type="radio"], input[type="checkbox"]')) {
          mgr.offcanvasInst.hide();

          // Focus management after offcanvas is completely hidden
          offcanvasEl.addEventListener(
            "hidden.bs.offcanvas",
            () => {
              button.classList.add("just-selected");
              button.focus();
            },
            { once: true }
          );

          // Remove visual feedback on blur
          button.addEventListener(
            "blur",
            () => button.classList.remove("just-selected"),
            { once: true }
          );
        }
      });

      // Add manager to global array for lifecycle management
      managers.push(mgr);

      // Remove d-none class after initialization to prevent layout shift
      source.classList.remove("d-none");
    });

  /**
   * 3. Global click handler to close dropdowns (preserves Bootstrap behavior)
   * Handles edge cases with modals and prevents unwanted closures
   */
  document.addEventListener("click", (e) => {
    // Skip if modal was just closed to prevent dropdown interference
    if (skipNextClick) {
      skipNextClick = false;
      return;
    }

    // Don't close dropdowns when clicking inside modal
    if (e.target.closest(".modal.show")) return;

    // Close open dropdowns when clicking outside
    managers.forEach((mgr) => {
      if (!mgr.isDesktop) return;
      const menu = mgr.dropdownInst._menu;
      const toggleEl = mgr.dropdownInst._element;
      if (
        menu.classList.contains("show") &&
        !menu.contains(e.target) &&
        !toggleEl.contains(e.target)
      ) {
        mgr.dropdownInst.hide();
      }
    });
  });

  /**
   * 4. Responsive behavior management
   * Handles transitions between desktop/mobile layouts
   */
  const mql = window.matchMedia(`(min-width: ${BREAKPOINT}px)`);

  /**
   * Handle responsive layout changes
   *
   * @param {MediaQueryListEvent} e - Media query change event
   */
  const handleResponsiveBehaviour = (e) => {
    const matchesDesktop = e.matches;

    managers.forEach((mgr) => {
      // Skip if already in correct state
      if (mgr.isDesktop === matchesDesktop) return;

      // Close active component before switching layouts
      if (mgr.isDesktop) mgr.dropdownInst.hide();
      else mgr.offcanvasInst.hide();

      // Update responsive state
      mgr.isDesktop = matchesDesktop;

      // Update CSS classes for layout
      mgr.group.classList.toggle("offdrop-dropdown", matchesDesktop);
      mgr.group.classList.toggle("offdrop-offcanvas", !matchesDesktop);

      // Disconnect observer before DOM manipulation
      if (mgr.observer) mgr.observer.disconnect();

      // Move nodes to appropriate container
      const target = matchesDesktop ? mgr.dropdownBody : mgr.offcanvasBody;
      target.innerHTML = "";
      mgr.nodes.forEach((node) => target.appendChild(node));

      // Reconnect observer after DOM changes
      mgr.setupObserver();

      // Adjust button styling for desktop layout
      target.querySelectorAll(".btn-group-vertical .btn").forEach((btn) => {
        btn.classList.toggle("border-0", matchesDesktop);
      });

      // Maintain active states after layout switch
      target.querySelectorAll("input:checked").forEach((input) => {
        const lbl = input.closest("label");
        if (lbl && !lbl.classList.contains("active"))
          lbl.classList.add("active");
      });
    });
  };

  /**
   * 5. Register responsive behavior listener
   * Uses modern addEventListener with fallback for older browsers
   */
  const addListener = mql.addEventListener || mql.addListener;
  addListener.call(mql, "change", handleResponsiveBehaviour);

  /**
   * 6. Initialize responsive state and value labels
   * Set initial state based on current viewport and update displays
   */
  handleResponsiveBehaviour(mql);
  // Update value labels for all existing selections
  managers.forEach((mgr) => {
    const target = mgr.isDesktop ? mgr.dropdownBody : mgr.offcanvasBody;
    const checkedInput = target.querySelector("input:checked");
    if (checkedInput) mgr.updateValueLabel(checkedInput);
  });

  /**
   * 7. Handle viewport changes at Bootstrap breakpoints for dropdown width recalculation
   * Recalculates dropdown widths when crossing lg (992px), xl (1200px), and xxl (1400px) breakpoints
   */
  const breakpoints = [992, 1200, 1400]; // Bootstrap lg, xl, xxl breakpoints
  let currentBreakpoint = null;

  const updateBreakpointState = () => {
    const width = window.innerWidth;
    let newBreakpoint = null;

    if (width >= 1400) newBreakpoint = "xxl";
    else if (width >= 1200) newBreakpoint = "xl";
    else if (width >= 992) newBreakpoint = "lg";
    else if (width >= 768) newBreakpoint = "md";
    else newBreakpoint = "sm";

    // Only recalculate if breakpoint actually changed
    if (currentBreakpoint !== newBreakpoint) {
      currentBreakpoint = newBreakpoint;

      // Recalculate dropdown widths for all desktop managers
      managers.forEach((mgr) => {
        if (mgr.isDesktop && mgr.dropdownEl.classList.contains("show")) {
          // Small delay to ensure layout is settled
          setTimeout(() => mgr.updateDropdownWidth(), 1);
        }
      });
    }
  };

  // Initialize current breakpoint state
  updateBreakpointState();

  // Listen for resize events with debouncing
  let resizeTimer;
  window.addEventListener("resize", () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(updateBreakpointState, 1);
  });
});

/**
 * Option Search Module - Filter functionality for Option Groups
 *
 * Provides search functionality for option groups in the product configurator.
 * Features include debounced input, minimum character requirements, caching,
 * accessibility support, and dynamic content handling.
 *
 * @author BadSpiegel Team
 * @version 1.0.0
 */
document.addEventListener("DOMContentLoaded", () => {
  /**
   * Search configuration object
   */
  const config = {
    useCache: false, // Enable/disable text normalization caching
    minChars: 3, // Minimum characters required for search
    debounceMs: 300, // Input debounce delay in milliseconds
    noResultsText: "Keine Optionen gefunden", // Text displayed when no results found
  };

  /**
   * Normalize text for search comparison
   * Converts to lowercase and handles German umlauts
   *
   * @param {string} str - Text to normalize
   * @returns {string} Normalized text
   */
  const normalize = (str) =>
    str
      .toLowerCase()
      .replace(/ä/g, "ae")
      .replace(/ö/g, "oe")
      .replace(/ü/g, "ue")
      .replace(/ß/g, "ss");

  /**
   * Cache system for normalized text (performance optimization)
   */
  const cache = new WeakMap();

  /**
   * Get normalized text with optional caching
   *
   * @param {HTMLElement} label - Label element to normalize
   * @returns {string} Normalized text content
   */
  const getNormalizedText = (label) => {
    if (!config.useCache) {
      return normalize(label.textContent.trim());
    }
    if (!cache.has(label)) {
      cache.set(label, normalize(label.textContent.trim()));
    }
    return cache.get(label);
  };

  /**
   * MutationObserver for dynamic content (only when caching enabled)
   * Maintains cache consistency when new elements are added
   */
  if (config.useCache) {
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        mutation.addedNodes.forEach((node) => {
          if (node.nodeType === 1) {
            // Element node
            if (node.matches("label.btn")) {
              cache.set(node, normalize(node.textContent.trim()));
            }
            // Handle nested label elements
            node.querySelectorAll?.("label.btn").forEach((label) => {
              cache.set(label, normalize(label.textContent.trim()));
            });
          }
        });
      });
    });

    // Observe all values groups for dynamic changes
    document.querySelectorAll(".values-group").forEach((container) => {
      observer.observe(container, { childList: true, subtree: true });
    });
  }

  /**
   * Initialize search functionality for each search input
   */
  document.querySelectorAll(".option-search-input").forEach((input) => {
    let debounceTimer;
    const wrapper = input.closest(".option-search-wrapper");
    const resetBtn = wrapper.querySelector(".option-search-input-reset");

    // Parse search targets from data attribute
    const targets =
      input.dataset.target
        ?.split(",")
        .map((selector) => selector.trim())
        .filter(Boolean) || [];

    if (targets.length === 0) {
      console.warn("No search targets defined for input:", input);
      return;
    }

    /**
     * Setup accessibility attributes for search input
     */
    input.setAttribute("role", "searchbox");
    input.setAttribute("aria-autocomplete", "list");

    /**
     * Initialize reset button
     */
    resetBtn.setAttribute("aria-label", "Suche zurücksetzen");
    resetBtn.classList.add("invisible");
    resetBtn.setAttribute("aria-hidden", "true");
    resetBtn.addEventListener("click", () => {
      input.value = "";
      input.focus();
      performSearch();
    });

    /**
     * Handle Enter key press for mobile keyboard dismissal
     */
    input.addEventListener("keydown", (e) => {
      if (e.key === "Enter") {
        e.preventDefault();

        // Simple solution: blur input and perform search
        input.blur(); // Dismiss mobile keyboard
        setTimeout(performSearch, 100); // Small delay for smooth UX

        // Alternative advanced solution (commented out):
        /*
        // Visual feedback during search
        input.classList.add('searching');
        
        // Dismiss mobile keyboard
        input.blur();
        
        // Perform search with delay
        setTimeout(() => {
          performSearch();
          input.classList.remove('searching');
          
          // Restore focus for screen readers
          if (document.activeElement === document.body) {
            input.focus({ preventScroll: true });
          }
        }, 150);
        */
      }
    });

    /**
     * Main search function
     * Filters options based on normalized input text
     */
    function performSearch() {
      const rawInput = input.value.trim();
      const normalizedTerm = normalize(rawInput);
      const isSearchActive = rawInput.length >= config.minChars;

      // Prepare no-results message with icon
      const iconHTML =
        '<i class="fa-sharp fa-light fa-triangle-exclamation me-2" aria-hidden="true"></i>';
      const noResultsHTML = `${iconHTML} ${config.noResultsText}`;

      // Toggle reset button visibility
      const hasText = rawInput.length > 0;
      resetBtn.classList.toggle("invisible", !hasText);
      resetBtn.setAttribute("aria-hidden", String(!hasText));

      // Process each search target
      targets.forEach((selector) => {
        document.querySelectorAll(selector).forEach((container) => {
          // Setup aria-controls relationship
          if (!container.id) {
            container.id = `search-container-${Math.random()
              .toString(36)
              .slice(2, 8)}`;
          }
          input.setAttribute("aria-controls", container.id);

          // Filter option labels
          let hasVisibleOptions = false;
          container.querySelectorAll("label.btn").forEach((label) => {
            const normalizedText = getNormalizedText(label);
            const shouldShow =
              !isSearchActive || normalizedText.includes(normalizedTerm);

            label.classList.toggle("d-none", !shouldShow);
            label.setAttribute("aria-hidden", String(!shouldShow));

            if (shouldShow) hasVisibleOptions = true;
          });

          // Handle no-results message
          let noResultsElement = container.querySelector(".no-results");
          if (!noResultsElement) {
            // Create no-results element
            noResultsElement = document.createElement("div");
            noResultsElement.className = "no-results px-md-3";
            noResultsElement.setAttribute("role", "status");
            noResultsElement.setAttribute("aria-live", "polite");

            const alertParagraph = document.createElement("p");
            alertParagraph.className =
              "alert alert-danger text-truncate mt-1 mt-md-3 mb-3";
            alertParagraph.innerHTML = noResultsHTML;

            noResultsElement.appendChild(alertParagraph);
            container.appendChild(noResultsElement);
          } else {
            // Update existing no-results message
            const alertParagraph = noResultsElement.querySelector("p.alert");
            if (alertParagraph) alertParagraph.innerHTML = noResultsHTML;
          }

          // Show/hide no-results message
          noResultsElement.style.display = hasVisibleOptions ? "none" : "block";
        });
      });
    }

    /**
     * Debounced input handler
     * Delays search execution to improve performance
     */
    function handleInput() {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(performSearch, config.debounceMs);
    }

    input.addEventListener("input", handleInput);

    // Perform initial search to set up state
    performSearch();
  });
});

/**
 * CSS for advanced Enter key handling (if enabled)
 * Add this to your stylesheet when using the advanced solution:
 *
 * .option-search-input.searching {
 *   background-color: rgba(0,0,0,0.05);
 *   transition: background-color 0.2s ease;
 * }
 */
