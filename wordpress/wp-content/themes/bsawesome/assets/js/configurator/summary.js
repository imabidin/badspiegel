/**
 * Product Configurator - Summary Module
 *
 * This module handles the dynamic summary display for the product configurator.
 * It collects selected options from various input types (text inputs, radio buttons,
 * price matrices), calculates total prices, and provides interactive navigation
 * back to specific configuration steps.
 *
 * Features:
 * - Real-time summary updates with DOM update optimization (caching)
 * - Grouped and sorted option display by logical groups and steps
 * - Interactive navigation: click summary items to jump to configurator steps
 * - Print and save configuration as code or direct link
 * - Price calculation and formatted display for all options and total
 * - Support for nested/child option groups and price matrices
 * - Accessible focus and keyboard navigation for summary and controls
 * - Tooltip initialization for summary actions
 * - Graceful error handling for print/save actions
 *
 * @version 2.3.0
 * @package Configurator
 */

import { optionGroups } from "./variables";
import { toNumber, formatPrice, getProductTitle, getProductPrice } from "./functions";

/**
 * Configuration flags for summary module behavior
 * @type {Object}
 */
const summaryConfig = {
  enableClickNavigation: false, // Set to true to enable clicking summary items to navigate to steps
};

/**
 * Performance cache to prevent unnecessary DOM updates
 * @type {Object}
 */
let summaryCache = {
  lastHash: null,
  lastHtml: null,
};

/**
 * Hide tooltip for a specific element
 * @param {HTMLElement} element - The element with tooltip to hide
 */
function hideTooltip(element) {
  if (window.bootstrap?.Tooltip) {
    const tooltip = bootstrap.Tooltip.getInstance(element);
    if (tooltip) {
      tooltip.hide();
    }
  }
}

/**
 * Set up click interactions for summary items
 */
function setupSummaryInteractions() {
  const summary = document.getElementById("productConfiguratorSummary");
  if (!summary) return;

  // Only add click navigation if enabled in config
  if (summaryConfig.enableClickNavigation) {
    summary.addEventListener("click", e => {
      const item = e.target.closest(".list-group-item[data-step]");
      if (!item) return;

      const step = item.dataset.step;
      const key = item.dataset.key;
      scrollToCarouselStep(step, key);
    });
  }

  // Set up print button
  const printBtn = summary.querySelector(".btn-print-summary");
  if (printBtn) {
    printBtn.addEventListener("click", e => {
      e.preventDefault();
      e.stopPropagation();
      hideTooltip(printBtn);
      printSummary();
    });
  }
  // Set up save button
  const saveBtn = summary.querySelector(".btn-save-summary");
  if (saveBtn) {
    saveBtn.addEventListener("click", e => {
      e.preventDefault();
      e.stopPropagation();
      hideTooltip(saveBtn);
      saveSummaryConfig();
    });
  }

  // Set up edit buttons
  const editBtns = summary.querySelectorAll(".btn-edit-group");
  editBtns.forEach(editBtn => {
    editBtn.addEventListener("click", e => {
      e.preventDefault();
      e.stopPropagation();
      hideTooltip(editBtn);
      const step = editBtn.dataset.step;
      navigateToCarouselStep(step);
    });
  });

  // Initialize tooltips using the global function
  if (window.initTooltips) {
    window.initTooltips();
  }
}

/**
 * Apply focus and tabindex handling to form control elements
 *
 * @param {HTMLElement} element - The element to focus
 * @param {string} cleanupEventName - Unique identifier for cleanup function
 */
function applyFocusWithTabindex(element, cleanupEventName = "cleanup") {
  element.setAttribute("tabindex", "0");

  const cleanup = () => {
    element.removeAttribute("tabindex");
  };

  element.addEventListener("blur", cleanup, { once: true });
  element.focus();
}

/**
 * Navigate to a specific carousel step and focus the related form control
 *
 * @param {string} step - The carousel step number to navigate to
 * @param {string} key - The option group key to focus within the step
 */
function scrollToCarouselStep(step, key) {
  if (!step) return;

  const carouselEl = document.getElementById("productConfiguratorCarousel");
  const carouselItem = carouselEl?.querySelector(`.carousel-item[data-step="${step}"]`);
  if (!carouselEl || !carouselItem) return;

  const carousel = bootstrap.Carousel.getOrCreateInstance(carouselEl);
  const items = Array.from(carouselEl.querySelectorAll(".carousel-item"));
  const targetIdx = items.indexOf(carouselItem);
  if (targetIdx === -1) return;

  /**
   * Focus the appropriate form control within the target carousel item
   */
  const focusControlElement = () => {
    const targetGroup = carouselItem.querySelector(`.option-group[data-key="${key}"]`);
    if (!targetGroup) return;

    let controlEl = null;
    let controlType = null;

    // Priority-based control element detection
    const detectionRules = [
      {
        type: "input",
        selector: "input.form-control",
        getElement: input => input.closest(".form-floating"),
      },
      {
        type: "button",
        selector: "button.form-select",
        getElement: button => button.closest(".form-floating"),
      },
      {
        type: "btngroup",
        selector: ".btn-group[data-key]",
        getElement: btnGroup => btnGroup,
      },
    ];

    // Find first matching control element
    for (const rule of detectionRules) {
      const foundElement = targetGroup.querySelector(rule.selector);
      if (foundElement) {
        controlEl = rule.getElement(foundElement);
        if (controlEl) {
          controlType = rule.type;
          break;
        }
      }
    }

    // Apply focus based on control type
    if (controlEl && controlType) {
      applyFocusWithTabindex(controlEl, `cleanup${controlType.charAt(0).toUpperCase() + controlType.slice(1)}`);
    } else {
      console.warn("[Summary] No focusable control element found for key:", key);
    }
  };

  // Handle carousel navigation
  const activeItem = carouselEl.querySelector(".carousel-item.active");
  const activeIdx = items.indexOf(activeItem);

  if (activeIdx === targetIdx) {
    focusControlElement();
  } else {
    carouselEl.addEventListener("slid.bs.carousel", focusControlElement, {
      once: true,
    });
    carousel.to(targetIdx);
  }
}

/**
 * Navigate to a specific carousel step for editing (used by edit buttons)
 * Adds focus-ring styling and focuses the corresponding indicator button after scrolling
 *
 * @param {string} step - The carousel step number to navigate to
 */
function navigateToCarouselStep(step) {
  if (!step) return;

  const carouselEl = document.getElementById("productConfiguratorCarousel");
  const carouselItem = carouselEl?.querySelector(`.carousel-item[data-step="${step}"]`);
  if (!carouselEl || !carouselItem) return;

  const carousel = bootstrap.Carousel.getOrCreateInstance(carouselEl);
  const items = Array.from(carouselEl.querySelectorAll(".carousel-item"));
  const targetIdx = items.indexOf(carouselItem);
  if (targetIdx === -1) return;

  /**
   * Add focus with ring styling to the corresponding indicator button
   */
  const highlightIndicatorButton = () => {
    // Try multiple selectors to find the indicator button
    let indicatorButton = document.querySelector(`button.indicator[data-bs-slide-to="${targetIdx}"]`);

    if (!indicatorButton) {
      indicatorButton = document.querySelector(`button[data-bs-slide-to="${targetIdx}"]`);
    }

    if (!indicatorButton) {
      indicatorButton = document.querySelector(
        `button[data-bs-target="#productConfiguratorCarousel"][data-bs-slide-to="${targetIdx}"]`
      );
    }

    if (indicatorButton) {
      // Check if the indicator is visible and scroll to it if needed
      const rect = indicatorButton.getBoundingClientRect();
      const isVisible =
        rect.left >= 0 && rect.right <= window.innerWidth && rect.top >= 0 && rect.bottom <= window.innerHeight;

      if (!isVisible) {
        indicatorButton.scrollIntoView({
          behavior: "smooth",
          block: "nearest",
          inline: "center",
        });
      }

      // Add focus ring classes and focus after scrolling
      setTimeout(
        () => {
          indicatorButton.classList.remove("border-secondary-subtle");
          indicatorButton.classList.add("border-primary-subtle", "focus-ring", "focus-ring-primary");
          indicatorButton.focus();

          // Remove classes after blur
          indicatorButton.addEventListener(
            "blur",
            () => {
              indicatorButton.classList.remove("border-primary", "focus-ring", "focus-ring-primary");
              indicatorButton.classList.add("border-secondary-subtle");
            },
            { once: true }
          );
        },
        isVisible ? 100 : 800
      );
    }
  };

  // Handle carousel navigation
  const activeItem = carouselEl.querySelector(".carousel-item.active");
  const activeIdx = items.indexOf(activeItem);

  if (activeIdx === targetIdx) {
    highlightIndicatorButton();
  } else {
    carouselEl.addEventListener("slid.bs.carousel", highlightIndicatorButton, {
      once: true,
    });
    carousel.to(targetIdx);
  }
}

/**
 * Print the configuration summary with generated code and link
 * Note: The HTML content for the print view, including CSS, is generated on-demand
 * when this function is called (i.e., when the print button is clicked).
 * The data (`summaryHtml`, `totalHtml`) is fetched from the current DOM state.
 * Additionally, a configuration code is generated and included in the print.
 */
async function printSummary() {
  const summaryContent = document.getElementById("productConfiguratorSummary");
  const totalContent = document.getElementById("productConfiguratorTotal");

  if (!summaryContent) return;

  const summaryHtml = summaryContent.innerHTML;
  const totalHtml = totalContent ? totalContent.innerHTML : "";

  // Show loading state
  const printBtn = summaryContent.querySelector(".btn-print-summary");
  const originalBtnContent = printBtn?.innerHTML;
  if (printBtn) {
    printBtn.innerHTML =
      '<i class="fa-sharp fa-light fa-spinner fa-fw fa-spin"></i><span class="visually-hidden">Wird gedruckt...</span>';
    printBtn.disabled = true;
  }

  let codeSection = "";

  try {
    // Generate configuration code for the print
    if (
      window.ConfiguratorSave &&
      window.ConfiguratorSave.gatherConfigData &&
      window.ConfiguratorSave.generateConfigCode
    ) {
      const configData = window.ConfiguratorSave.gatherConfigData(false);
      const { code, directLink } = await window.ConfiguratorSave.generateConfigCode(configData);

      codeSection = `
        <div class="print-code-section">
          <h3>Konfiguration als Code</h3>
          <p>Code: <code style="font-size: 1.2em; font-weight: bold; background: #f8f9fa; padding: 4px 8px; border: 1px solid #dee2e6;">${code}</code></p>
          <p>Direkter Link: <a href="${directLink}" style="word-break: break-all; color: #0066cc;">${directLink}</a></p>
          <p class="small text-muted">Mit diesem Code können Sie Ihre Konfiguration jederzeit wieder aufrufen.</p>
        </div>
      `;
    }
  } catch (error) {
    console.warn("Code generation failed for print:", error);
    codeSection = `
      <div class="print-code-section">
        <h3>Konfiguration als Code</h3>
        <p class="text-muted">Code konnte nicht generiert werden. Bitte speichern Sie Ihre Konfiguration separat.</p>
      </div>
    `;
  } finally {
    // Reset button state
    if (printBtn && originalBtnContent) {
      printBtn.innerHTML = originalBtnContent;
      printBtn.disabled = false;
    }
  }

  const printWindow = window.open("", "_blank");

  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>Badspiegel.de</title>
      <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.5; font-size: 14px; }
        .print-code-section, 
        .print-header { margin-bottom: 16px; border-bottom: 2px solid #dee2e6; }
        .print-header p { margin: 0; margin-bottom: 16px; }
        
        /* Bootstrap-like grid system - modified for print */
        .row { display: block; margin: 0; }
        .list-group-item .row { display: flex; align-items: center; margin: 0; }
        .row.g-1 { display: flex; align-items: center; margin: 0; }
        .col { flex: 1; padding: 0 4px; }
        .col-auto { flex: 0 0 auto; padding: 0 4px; }
        .col-12 { width: 100%; display: block; margin-bottom: 15px; }
        
        /* Container layouts */
        .list-group-body { display: block !important; }
        .list-group-body > .row { display: block; }
        
        /* List styles */
        .list-group { list-style: none; margin: 0; padding: 0; }
        .list-group-item { padding: 12px 16px; border: 1px solid #ddd; margin-bottom: 1px; background: #fff; display: block; }
        .list-group-flush .list-group-item { border-left: 0; border-right: 0; border-top: 0; }
        .list-group-flush .list-group-item:first-child { border-top: 0; }
        .list-group-flush .list-group-item:last-child { border-bottom: 0; }
        
        /* Typography */
        .fw-medium { font-weight: 500; }
        .text-end { text-align: right; }
        .fs-5 { font-size: 1.25rem; }
        .small { font-size: 0.875rem; }
        .text-muted { color: #6c757d; }
        .text-truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        
        /* Headers */
        h5, h6 { font-weight: 500; }
        h2 { font-size: 1.5rem; margin: 16px 0 16px 0; }
        h3 { font-size: 1.3rem; margin: 16px 0 8px 0; font-weight: 500; }
        h5 { font-size: 1.25rem; margin: 16px 0 16px 0; }
        h6 { font-size: 1.1rem; margin: 16px 0 8px 0; }

        /* Spacing */
        .mb-0 { margin-bottom: 0; }
        .px-3 { padding-left: 1rem; padding-right: 1rem; }
        .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
        .pt-3 { padding-top: 1rem; }
        .p-3 { padding: 1rem; }
        
        /* Background and borders */
        .border-secondary-subtle { border-color: #dee2e6; }
        .border-top { border-top: 1px solid #dee2e6; }
        
        /* Layout improvements */
        .d-flex { display: flex; }
        .justify-content-between { justify-content: space-between; }
        .align-items-center { align-items: center; }
        
        /* Hide print button in print */
        .btn-print-summary,
        .btn-save-summary,
        .btn-edit-group,
        div.vr.border-secondary-subtle { display: none; }

        /* Price styling */
        .product-price { font-weight: 600; }
        
        /* Page breaks */
        @media print {
          .print-header { page-break-after: avoid; }
          .print-code-section { page-break-inside: avoid; }
          .list-group-item { page-break-inside: avoid; }
        }

        /* Overwrites */
        .d-flex.justify-content-between.align-items-center.bg-secondary-subtle.px-3.pt-3.mb-0,
        .list-group-body.bg-secondary-subtle.row.g-3.p-3.pt-0.mx-0.mt-0 {
          padding: 0
        }
        .list-group-item.border-secondary-subtle {
          padding: 0.25rem;
        }
        .bg-secondary-subtle.border-top.border-secondary-subtle.text-end.px-3.py-2.mb-0.fs-5 {
          padding: 0.5rem 1rem;
          border-top: 2px double #dee2e6;
        }

      </style>
    </head>
    <body>
      <div class="print-header">
        <h2>Badspiegel.de Konfiguration</h2>
        <p>Gedruckt am: ${new Date().toLocaleDateString("de-DE")} um ${new Date().toLocaleTimeString("de-DE")}</p>
      </div>
      ${codeSection}
      ${summaryHtml}
      ${totalHtml}
    </body>
    </html>
  `);

  printWindow.document.close();
  printWindow.focus();
  printWindow.print();
  printWindow.close();
}

/**
 * Lock the heart button and wait for summary changes
 * @param {HTMLElement} heartButton - The heart button element to lock
 */
function lockHeartButton(heartButton) {
  if (!heartButton) return;

  const heartIcon = heartButton.querySelector("i");
  if (!heartIcon) return;

  // Store original classes and tooltip title for restoration
  const originalClasses = heartIcon.className;
  const originalTitle = heartButton.getAttribute("title");

  // Lock the button: change icon to solid and disable interaction
  heartIcon.classList.remove("fa-light");
  heartIcon.classList.add("fa-solid");
  heartButton.disabled = true;
  heartButton.setAttribute("aria-pressed", "true");

  // Dispose any existing tooltip on the button FIRST
  if (window.bootstrap?.Tooltip) {
    const existingTooltip = bootstrap.Tooltip.getInstance(heartButton);
    if (existingTooltip) {
      try {
        existingTooltip.dispose();
      } catch (error) {
        console.warn("Error disposing existing tooltip:", error);
      }
    }
  }

  // Create a wrapper span for the tooltip (Bootstrap disabled element workaround)
  const wrapper = document.createElement("span");
  // wrapper.style.cssText = 'display: inline-block;';
  wrapper.setAttribute("title", "Konfiguration in Favoriten gespeichert");

  // Wrap the button
  heartButton.parentNode.insertBefore(wrapper, heartButton);
  wrapper.appendChild(heartButton);

  // Remove tooltip attributes from the disabled button
  heartButton.removeAttribute("title");
  heartButton.removeAttribute("data-bs-tooltip-md");
  heartButton.removeAttribute("data-bs-toggle");
  heartButton.removeAttribute("data-bs-placement");

  // Initialize tooltip on the wrapper with error handling
  let wrapperTooltip = null;
  if (window.bootstrap?.Tooltip) {
    try {
      wrapperTooltip = new bootstrap.Tooltip(wrapper, {
        container: "body",
        title: "Konfiguration wurde schon gespeichert",
        trigger: "hover focus",
        placement: "top",
      });
    } catch (error) {
      console.warn("Error creating wrapper tooltip:", error);
    }
  }

  // Store original hash to detect changes
  const currentHash = summaryCache.lastHash;

  // Create a function to check for changes and unlock
  const checkForChanges = () => {
    // Check if summary has changed
    if (summaryCache.lastHash !== currentHash) {
      // Summary has changed, unlock the button
      heartIcon.className = originalClasses;
      heartButton.disabled = false;
      heartButton.setAttribute("aria-pressed", "false");
      heartButton.setAttribute("title", originalTitle || "Konfiguration speichern");
      heartButton.setAttribute("data-bs-tooltip-md", "true");

      // Remove wrapper and restore original structure
      if (wrapper && wrapper.parentNode) {
        // Dispose wrapper tooltip safely
        if (wrapperTooltip) {
          try {
            wrapperTooltip.dispose();
          } catch (error) {
            console.warn("Error disposing wrapper tooltip:", error);
          }
        }

        // Move button back to original position
        wrapper.parentNode.insertBefore(heartButton, wrapper);
        wrapper.remove();

        // Reinitialize tooltip on the button with delay
        setTimeout(() => {
          if (window.bootstrap?.Tooltip) {
            try {
              new bootstrap.Tooltip(heartButton, {
                container: "body",
                title: originalTitle || "Konfiguration speichern",
              });
            } catch (error) {
              console.warn("Error recreating button tooltip:", error);
            }
          }
        }, 100);
      }

      // Stop checking
      return true;
    }
    return false;
  };

  // Use a MutationObserver to watch for DOM changes in the summary
  const summaryElement = document.getElementById("productConfiguratorSummary");
  if (summaryElement) {
    const observer = new MutationObserver(() => {
      if (checkForChanges()) {
        observer.disconnect();
      }
    });

    observer.observe(summaryElement, {
      childList: true,
      subtree: true,
      attributes: false,
      characterData: true,
    });
  }
}

/**
 * Save the configuration using the same functionality as the main save button
 */
async function saveSummaryConfig() {
  const saveBtn = document.querySelector(".btn-save-summary");
  if (!saveBtn) return;

  // Check if ConfiguratorSave module is available
  if (!window.ConfiguratorSave || !window.ConfiguratorSave.saveConfiguration) {
    console.error("ConfiguratorSave module not available");
    alert("Speichern-Funktion nicht verfügbar. Bitte verwenden Sie den Hauptspeichern-Button.");
    return;
  }

  try {
    // // Get current config code if available
    // const configData = window.ConfiguratorSave.gatherConfigData(false);
    // const configCode = configData && configData.code ? configData.code : null;

    // // Add config_code as data attribute to the button for the AJAX call
    // if (configCode) {
    //     saveBtn.setAttribute('data-config-code', configCode);
    // }

    // Lock the heart button IMMEDIATELY after successful save (when modal opens)
    const result = await window.ConfiguratorSave.saveConfiguration($(saveBtn), "icon");

    // Lock immediately after save is successful (modal is opening)
    lockHeartButton(saveBtn);
  } catch (error) {
    console.error("Error saving configuration from summary:", error);

    // User-friendly error handling
    let errorMessage = "Fehler beim Speichern der Konfiguration.";

    if (error.message) {
      errorMessage += " " + error.message;
    } else {
      errorMessage += " Bitte versuchen Sie es erneut.";
    }

    alert(errorMessage);
  }
}

/**
 * Group options by their logical groups for better organization
 *
 * @param {Array} optionsSummary - Array of selected option objects
 * @returns {Array} Array of grouped and sorted option objects
 */
function createGroupedOptions(optionsSummary) {
  const groups = {};

  optionsSummary.forEach(item => {
    const { groupKey, groupLabel, groupStep, groupOrder } = item.groupData;

    if (!groups[groupKey]) {
      groups[groupKey] = {
        label: groupLabel,
        step: groupStep,
        order: groupOrder,
        options: [],
      };
    }

    groups[groupKey].options.push(item);
  });

  return Object.values(groups).sort((a, b) => {
    if (a.step !== b.step) return a.step - b.step;
    return a.order - b.order;
  });
}

/**
 * Build the HTML structure for the configuration summary
 *
 * @param {Object} params - Configuration parameters
 * @param {string} params.productTitle - The base product title
 * @param {number} params.productPrice - Base product price in euros
 * @param {Array} params.optionsSummary - Array of selected option objects
 * @returns {string} Complete HTML string for the summary display
 */
export function buildSummary({ productTitle, productPrice, optionsSummary }) {
  let htmlOutput = `
    <div class="d-flex justify-content-between align-items-center bg-secondary-subtle px-3 pt-3 pe-2 mb-0">
      <h5 class="lh-base me-auto mb-0">Zusammenfassung</h5>
      <div class="d-none vr border-secondary-subtle"></div>
      <button type="button" class="btn-save-summary btn btn-sm btn-link link-body-emphasis" style="--bs-btn-disabled-opacity: 1;--bs-btn-disabled-color: var(--bs-dark)" title="Konfiguration speichern" data-bs-tooltip-md="true">
        <i class="fa-sharp fa-fw fa-light fa-heart"></i>
        <span class="visually-hidden">Speichern</span>
      </button>
      <div class="vr border-secondary-subtle"></div>
      <button type="button" class="btn-print-summary btn btn-sm btn-link link-body-emphasis" title="Konfiguration drucken" data-bs-tooltip-md="true">
        <i class="fa-sharp fa-fw fa-light fa-print"></i>
        <span class="visually-hidden">Drucken</span>
      </button>
    </div>
    <div class="list-group-body bg-secondary-subtle row g-3 p-3 pt-0 mx-0 mt-0">
      <div class="col-12 px-0">
        <ul class="list-group list-group-flush">
          <li class="list-group-item border-secondary-subtle">
            <span class="row g-1">
              <span class="col text-truncate fw-medium">${productTitle}</span>
              <span class="col-auto">${formatPrice(productPrice)}</span>
            </span>
          </li>
        </ul>
      </div>
  `;

  if (optionsSummary.length === 0) {
    htmlOutput += `
        <ul class="list-group list-group-flush">
          <li class="list-group-item text-danger">Keine Optionen ausgewählt</li>
        </ul>
    `;
  } else {
    const groupedOptions = createGroupedOptions(optionsSummary);

    groupedOptions.forEach(group => {
      if (group.options.length === 0) return;

      const showGroupLabel = group.options.some(opt => !opt.isPricematrix);
      htmlOutput += `<div class="col-12 px-0">`;

      if (showGroupLabel) {
        htmlOutput += `
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">${group.label}</h6>
            <button type="button" class="btn-edit-group btn btn-sm btn-link link-body-emphasis" data-step="${group.step}" title="Bearbeiten" data-bs-tooltip-md="true">
              <i class="fa-sharp fa-fw fa-light fa-edit"></i>
              <span class="visually-hidden">Bearbeiten</span>
            </button>
          </div>
        `;
      }

      htmlOutput += `<ul class="list-group list-group-flush">`;

      group.options.forEach(item => {
        const {
          key,
          label,
          pricematrixLabel,
          pricematrixPrice,
          inputValue,
          inputPrice,
          radioLabel,
          radioPrice,
          isChild,
          stepNumber,
          isPricematrix,
        } = item;

        const value = radioLabel || inputValue || pricematrixLabel || "";
        const price = radioPrice || inputPrice || pricematrixPrice || 0;
        const formattedPrice = formatPrice(price) || "-";
        const childClass = isChild ? " text-muted small" : ""; // Conditional classes and attributes for pricematrix items
        const isClickable = !isPricematrix && summaryConfig.enableClickNavigation;
        const actionClass = isClickable ? " list-group-item-action" : "";
        const interactiveAttrs = isClickable
          ? `
            role="button"
            data-step="${stepNumber}"
            data-key="${key}"
            tabindex="0"
            aria-label="Zur Option ${label} springen"
        `
          : "";

        htmlOutput += `
          <li
            class="list-group-item${actionClass} list-group-item-secondary bg-body text-body border-secondary-subtle${childClass}"
            ${interactiveAttrs}
          >
            <span class="row g-1">
              <span class="col-auto fw-medium">${label}:</span>
              <span class="col text-truncate">${value}</span>
              <span class="col-auto">${formattedPrice}</span>
            </span>
          </li>
        `;
      });

      htmlOutput += `</ul></div>`;
    });
  }

  htmlOutput += `</div>`;
  return htmlOutput;
}

/**
 * Main function to update the product configuration summary
 */
export function updateSummary() {
  const productPrice = getProductPrice();
  const productTitle = getProductTitle();
  const optionsSummary = [];

  optionGroups.forEach(group => {
    const label = group.dataset.label || "Unbekannte Option";
    const isChild = group.classList.contains("option-group-child");
    const key = group.dataset.key;

    const carouselItem = group.closest(".carousel-item");
    const groupDataForSummary = {
      groupKey: group.dataset.group || "Sonstiges",
      groupLabel: carouselItem?.dataset.label || group.dataset.group || "Sonstiges",
      groupStep: parseInt(carouselItem?.dataset.step, 10) || 999,
      groupOrder: parseInt(group.dataset.order, 10) || 999,
    };

    // Check for pricematrix selections (pxbh = price x by height matrix)
    const pricematrixEl = group.querySelector('.option-price[class*="pxbh"]');
    let pricematrixLabel = null;
    let pricematrixPrice = null;
    let isPricematrix = false;

    if (pricematrixEl && pricematrixEl.value.trim() !== "") {
      const selected = pricematrixEl.selectedOptions[0];
      pricematrixLabel = selected?.dataset?.label?.trim() || "";
      pricematrixPrice = selected?.dataset?.price || null;
      isPricematrix = true;
    }

    // Check for text input values
    const inputEl = group.querySelector(".option-input");
    let inputValue = null;
    let inputPrice = null;

    if (inputEl && inputEl.value.trim() !== "") {
      inputValue = inputEl.value.trim();
      inputPrice = inputEl.dataset.price || null;
    }

    // Check for radio button selections
    const radioEl = group.querySelector(".option-radio:checked");
    let radioLabel = null;
    let radioPrice = null;

    if (radioEl && radioEl.value.trim() !== "") {
      radioLabel = radioEl.dataset.label || radioEl.value.trim();
      radioPrice = radioEl.dataset.price || null;
    }

    const stepNumber = group.closest(".carousel-item")?.dataset?.step || null;

    if (inputValue || radioLabel || pricematrixLabel) {
      optionsSummary.push({
        key,
        label,
        pricematrixLabel,
        pricematrixPrice,
        inputValue,
        inputPrice,
        radioLabel,
        radioPrice,
        isChild,
        stepNumber,
        isPricematrix,
        groupData: groupDataForSummary,
      });
    }
  });

  // Performance optimization: skip update if nothing changed
  const currentHash = JSON.stringify(optionsSummary);
  if (summaryCache.lastHash === currentHash && summaryCache.lastHtml) {
    return;
  }

  // Generate and inject summary HTML
  const summaryHtml = buildSummary({
    productTitle,
    productPrice,
    optionsSummary,
  });

  const configuratorSummary = document.getElementById("productConfiguratorSummary");
  if (configuratorSummary) {
    configuratorSummary.innerHTML = summaryHtml;
    setupSummaryInteractions(); // Re-establishes click handlers and initializes tooltips
  }

  // Calculate and display total price
  const extraPrice = optionsSummary.reduce(
    (acc, item) => acc + toNumber(item.inputPrice) + toNumber(item.radioPrice) + toNumber(item.pricematrixPrice),
    0
  );
  const totalPrice = productPrice + extraPrice;
  const totalPriceFormatted = formatPrice(totalPrice.toFixed(2));

  const totalHtml = `
    <p class="bg-secondary-subtle border-top border-secondary-subtle text-end px-3 py-2 mb-0 fs-5">
      <span class="product-price-total-text small">Gesamtpreis:</span>
      <span class="product-price price fw-medium">${totalPriceFormatted || "0,00 €"}</span>
    </p>
  `;

  const configuratorTotal = document.getElementById("productConfiguratorTotal");
  if (configuratorTotal) {
    configuratorTotal.innerHTML = totalHtml;
  }

  // Update cache
  summaryCache.lastHash = currentHash;
  summaryCache.lastHtml = summaryHtml;
}

/**
 * Initialize the summary module
 */
document.addEventListener("DOMContentLoaded", () => {
  setupSummaryInteractions();
});
