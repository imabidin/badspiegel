/**
 * Product Configurator - Price Labels Module
 *
 * This module handles the dynamic updating of price display labels that appear
 * next to price matrix select elements. It automatically populates and updates
 * label information including dimension types, option labels, and formatted prices
 * based on user selections in price calculation dropdowns.
 *
 * Features:
 * - Automatic price label updates on selection changes
 * - Dynamic dimension type detection and labeling
 * - Real-time price and option label synchronization
 * - Event delegation for performance optimization
 * - Support for multiple price matrix types (dimensions, depth, cuts, frames)
 * - Initial state population on page load
 * - Robust DOM structure validation
 *
 * @version 2.2.0
 * @package Configurator
 * @subpackage PriceCalculations
 */

// ====================== PRICE LABEL UPDATE SYSTEM ======================

/**
 * Updates the price display label element that follows a price matrix select
 * Populates dimension type, option label, and formatted price information
 * based on the currently selected option's data attributes
 *
 * @param {HTMLSelectElement} select - The price matrix select element to update labels for
 *
 * @example
 * // HTML structure expected:
 * // <select class="option-pricematrix" name="pxbh_spiegel">
 * //   <option data-label="80x60 cm" data-price="199,00 €">80x60</option>
 * // </select>
 * // <p>
 * //   <span class="sub_name"></span> -
 * //   <span class="sub_label"></span> -
 * //   <span class="sub_price"></span>
 * // </p>
 */
function updatePriceLabel(select) {
  // Get the currently selected option and its data
  const opt = select.options[select.selectedIndex];

  // Find the adjacent paragraph element that contains the price labels
  const p = select.nextElementSibling;

  // Validate DOM structure - ensure we have a paragraph element
  if (!p || p.tagName !== "P") return;

  // Query for the specific label span elements within the paragraph
  const nameSpan = p.querySelector(".sub_name"); // Dimension type label
  const labelSpan = p.querySelector(".sub_label"); // Option description label
  const priceSpan = p.querySelector(".sub_price"); // Formatted price label

  /**
   * DIMENSION TYPE DETECTION AND LABELING
   * =====================================
   * Determines the appropriate German dimension label based on the select element's
   * name attribute prefix. Each prefix corresponds to a specific calculation type.
   */
  if (nameSpan) {
    nameSpan.textContent =
      select.name && select.name.startsWith("pxbh")
        ? "Maße" // Width x Height dimensions
        : select.name && select.name.startsWith("pxd")
        ? "Durchmesser" // Diameter for round mirrors
        : select.name && select.name.startsWith("pxt")
        ? "Tiefe" // Depth/thickness measurements
        : ""; // No label for unrecognized prefixes
  }

  /**
   * OPTION LABEL AND PRICE POPULATION
   * =================================
   * Extracts and displays the human-readable label and formatted price
   * from the selected option's data attributes
   */

  // Update the descriptive label (e.g., "80x60 cm", "Ø 50 cm")
  if (labelSpan) labelSpan.textContent = opt.dataset.label || "";

  // Update the formatted price display (e.g., "199,00 €", "+ 50,00 €")
  if (priceSpan) priceSpan.textContent = opt.dataset.price || "";
}

// ====================== INITIALIZATION SYSTEM ======================

/**
 * Initial population of all price labels on page load
 * Ensures that pre-selected options display their labels immediately
 * without requiring user interaction
 */
document
  .querySelectorAll("select.option-pricematrix, select.option-price")
  .forEach((sel) => updatePriceLabel(sel));

// ====================== EVENT DELEGATION SYSTEM ======================

/**
 * Global change event listener with event delegation
 * Provides efficient event handling for all price matrix selects
 * without individual event listener attachment
 *
 * Uses event delegation pattern for:
 * - Better performance with many select elements
 * - Automatic handling of dynamically added selects
 * - Centralized event management
 */
document.addEventListener("change", (event) => {
  const sel = event.target;

  // Check if the changed element is a price matrix select
  if (sel.matches("select.option-pricematrix, select.option-price")) {
    updatePriceLabel(sel);
  }
});

/**
 * Future Enhancement Ideas:
 *
 * 1. Animation support for label transitions:
 *    function animateLabel(element, newText) {
 *      element.style.opacity = '0';
 *      setTimeout(() => {
 *        element.textContent = newText;
 *        element.style.opacity = '1';
 *      }, 150);
 *    }
 *
 * 2. Label template system for internationalization:
 *    const labelTemplates = {
 *      'de': { pxbh: 'Maße', pxd: 'Durchmesser' },
 *      'en': { pxbh: 'Dimensions', pxd: 'Diameter' }
 *    };
 *
 * 3. Custom label formatting with currency conversion:
 *    function formatPrice(price, currency = 'EUR') {
 *      return new Intl.NumberFormat('de-DE', {
 *        style: 'currency',
 *        currency: currency
 *      }).format(parseFloat(price));
 *    }
 *
 * 4. Label validation and error handling:
 *    function validateLabelStructure(select) {
 *      const p = select.nextElementSibling;
 *      const requiredSpans = ['.sub_name', '.sub_label', '.sub_price'];
 *      return requiredSpans.every(sel => p?.querySelector(sel));
 *    }
 *
 * 5. Accessibility enhancements:
 *    function announceChange(select, newLabel) {
 *      const announcement = `Price updated: ${newLabel}`;
 *      const sr = document.createElement('div');
 *      sr.setAttribute('aria-live', 'polite');
 *      sr.setAttribute('aria-atomic', 'true');
 *      sr.className = 'sr-only';
 *      sr.textContent = announcement;
 *      document.body.appendChild(sr);
 *      setTimeout(() => sr.remove(), 1000);
 *    }
 */
