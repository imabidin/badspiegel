/**
 * Product Configurator - Variables and Constants Module
 *
 * This module centralizes all DOM element references, product type detection flags,
 * and configurator state variables used throughout the product configuration system.
 * It provides a single source of truth for element queries and product characteristics.
 *
 * Features:
 * - Product type detection (mirrors, cabinets, lighting types)
 * - Form detection (round, rectangular, special cuts)
 * - Lighting configuration detection (LED positions)
 * - DOM element caching for performance optimization
 * - Input field references for all configurator types
 * - Price matrix element identification
 * - Comprehensive product attribute flags
 * - Simple debug system for monitoring
 *
 * @version 2.3.0
 * @package Configurator
 */

// ====================== DEBUG CONFIGURATION ======================

/**
 * Simple debug flag - set to false for production
 * @type {boolean}
 */
export const DEBUG_MODE = true;

// ====================== PRODUCT TYPE DETECTION ======================

/**
 * Base product container element
 * @type {HTMLElement|null}
 */
export const product = document.querySelector(".product");

if (DEBUG_MODE && product) console.log("✅ const product:", product.className);

// ====================== PRODUCT CATEGORY DETECTION ======================

/**
 * Product category elements
 * @type {boolean}
 */
export const isBadspiegel = product?.classList.contains("product_cat-badspiegel");
export const isSpiegelschrank = product?.classList.contains("product_cat-spiegelschraenke");
export const isUnterschrank = product?.classList.contains("product_cat-unterschraenke");
export const isHochschrank = product?.classList.contains("product_cat-hochschraenke");
export const isBoard =
  product?.classList.contains("product_cat-sideboards") || product?.classList.contains("product_cat-lowboards");

if (DEBUG_MODE) {
  if (isBadspiegel) console.log("✅ Badspiegel detected");
  if (isSpiegelschrank) console.log("✅ Spiegelschrank detected");
  if (isUnterschrank) console.log("✅ Unterschrank detected");
  if (isHochschrank) console.log("✅ Hochschrank detected");
  if (isBoard) console.log("✅ Sideboard/Lowboard detected");
}

// ====================== LIGHTING CONFIGURATION DETECTION ======================

/**
 * General LED lighting presence
 * @type {boolean}
 */
export const isLED = product?.classList.contains("product_attr-beleuchtung_ja");

if (DEBUG_MODE && isLED) console.log("✅ const isLED: ", isLED);

/**
 * LED lighting position
 * @type {boolean}
 */
export const isLORU = product?.classList.contains("product_attr-lichtposition_rundherum");
export const isLOR = product?.classList.contains("product_attr-lichtposition_links-oben-rechts");
export const isLR = product?.classList.contains("product_attr-lichtposition_links-rechts");
export const isOU = product?.classList.contains("product_attr-lichtposition_oben-unten");
export const isO = product?.classList.contains("product_attr-lichtposition_oben");

if (DEBUG_MODE) {
  if (isLORU) console.log("✅ const isLORU: ", isLORU);
  if (isLOR) console.log("✅ const isLOR: ", isLOR);
  if (isLR) console.log("✅ const isLR: ", isLR);
  if (isOU) console.log("✅ const isOU: ", isOU);
  if (isO) console.log("✅ const isO: ", isO);
}

// ====================== PRODUCT SHAPE DETECTION ======================

/**
 * Product shape and form factor detection
 * @type {boolean}
 */
export const isRechteckig =
  product?.classList.contains("product_attr-form_rechteckig") ||
  product?.classList.contains("product_attr-form_quadrat");
export const isRund = product?.classList.contains("product_attr-form_rund");
export const isSK =
  product?.classList.contains("product_attr-form_halbmond") ||
  product?.classList.contains("product_attr-form_rund-angeschnitten");
export const isSK1 =
  product?.classList.contains("product_attr-schnittkanten_1") ||
  product?.classList.contains("product_attr-schnittkante_unten") ||
  product?.classList.contains("product_attr-schnittkante_seite");
export const isSK2 =
  product?.classList.contains("product_attr-schnittkanten_2") ||
  product?.classList.contains("product_attr-schnittkante_seite-und-unten");

if (DEBUG_MODE) {
  if (isRechteckig) console.log("✅ Rechteckig detected");
  if (isRund) console.log("✅ Rund detected");
  if (isSK) console.log("✅ SK detected");
  if (isSK1) console.log("✅ SK1 detected");
  if (isSK2) console.log("✅ SK2 detected");
}

// ====================== SPECIFIC DIMENSION INPUTS ======================

/**
 * Specific dimension input elements
 * @type {HTMLElement|null}
 */
export const durchmesserInput = document.querySelector('.option-input[name="durchmesser"]');
export const breiteHoheInput = document.querySelector('.option-input[name="breite_hoehe"]');
export const breiteInput = document.querySelector('.option-input[name="breite"]');
export const breiteUntenInput = document.querySelector('.option-input[name="breite_unten"]');
export const breiteObenInput = document.querySelector('.option-input[name="breite_oben"]');
export const hoeheInput = document.querySelector('.option-input[name="hoehe"]');
export const hoeheLinksInput = document.querySelector('.option-input[name="hoehe_links"]');
export const hoeheRechtsInput = document.querySelector('.option-input[name="hoehe_rechts"]');
export const tiefeInput = document.querySelector('.option-input[name="tiefe"]');

if (DEBUG_MODE) {
  if (durchmesserInput) console.log("✅ Durchmesser input found");
  if (breiteHoheInput) console.log("✅ BreiteHoehe input found");
  if (breiteInput) console.log("✅ Breite input found");
  if (breiteUntenInput) console.log("✅ BreiteUnten input found");
  if (breiteObenInput) console.log("✅ BreiteOben input found");
  if (hoeheInput) console.log("✅ Hoehe input found");
  if (hoeheLinksInput) console.log("✅ HoeheLinks input found");
  if (hoeheRechtsInput) console.log("✅ HoeheRechts input found");
  if (tiefeInput) console.log("✅ Tiefe input found");
}

/////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////

// ====================== CART FUNCTIONALITY ======================

/**
 * WooCommerce add to cart button reference
 * Used for cart integration and validation state management
 * @type {HTMLElement|null}
 */
export const addToCartButton = document.querySelector(".single_add_to_cart_button");

// ====================== CONFIGURATOR CORE ELEMENTS ======================

/**
 * Main configurator container element
 * Central hub for all configuration interactions
 * @type {HTMLElement|null}
 */
export const configurator = document.getElementById("productConfigurator");

/**
 * RESPONSIVE OFFCANVAS SYSTEM
 * ===========================
 */

/**
 * Bootstrap offcanvas element for mobile configuration display
 * @type {HTMLElement|null}
 */
// export const offcanvas = document.getElementById("productConfiguratorOffcanvas"); // not in use

/**
 * All option elements that use offcanvas display mode
 * @type {NodeList}
 */
// export const offcanvasElements = document.querySelectorAll(".option-offcanvas"); // is it in use?

// ====================== OPTION GROUP ELEMENTS ======================

/**
 * GLOBAL OPTION GROUP REFERENCES
 * ==============================
 */

/**
 * Single option group element (first found)
 * @type {HTMLElement|null}
 */
export const optionGroup = document.querySelector(".option-group");

/**
 * All option group elements in the configurator
 * Used for iteration and batch operations
 * @type {NodeList}
 */
export const optionGroups = document.querySelectorAll(".option-group");

// ====================== INPUT TYPE REFERENCES ======================

/**
 * SINGLE INPUT ELEMENT REFERENCES
 * ===============================
 * These references point to the first found element of each type
 * Useful for singular operations and validation checks
 */

/**
 * First number input field
 */
export const numberInput = document.querySelector('.option-input[type="number"]');

/**
 * First text input field
 */
export const textInput = document.querySelector('.option-input[type="text"]');

/**
 * First radio button input
 */
export const radioInput = document.querySelector('.option-radio[type="radio"]');

/**
 * First checkbox input
 */
export const checkInput = document.querySelector('.option-check[type="checkbox"]');

/**
 * MULTIPLE INPUT ELEMENT COLLECTIONS
 * ==================================
 * These NodeLists contain all elements of each type for iteration
 */

/**
 * All number input fields in the configurator
 * @type {NodeList}
 */
export const numberInputs = document.querySelectorAll('.option-input[type="number"]');

/**
 * All text input fields in the configurator
 * @type {NodeList}
 */
export const textInputs = document.querySelectorAll('.option-input[type="text"]');

/**
 * All radio button inputs in the configurator
 * @type {NodeList}
 */
export const radioInputs = document.querySelectorAll('.option-radio[type="radio"]');

/**
 * All checkbox inputs in the configurator
 * @type {NodeList}
 */
export const checkInputs = document.querySelectorAll('.option-check[type="checkbox"]');

// ====================== EMPTY VALUE INPUT DETECTION ======================

/**
 * SINGLE "NO SELECTION" INPUT REFERENCES
 * ======================================
 * These inputs represent "none" or "no selection" options
 */

/**
 * First radio button with empty value (represents "no selection")
 */
export const radioNoInput = document.querySelector('.option-input[type="radio"][value=""]');

/**
 * First checkbox with empty value (represents "no selection")
 */
export const checkNoInput = document.querySelector('.option-input[type="checkbox"][value=""]');

/**
 * MULTIPLE "NO SELECTION" INPUT COLLECTIONS
 * =========================================
 */

/**
 * All radio buttons with empty values
 * @type {NodeList}
 */
export const radioNoInputs = document.querySelectorAll('.option-input[type="radio"][value=""]');

/**
 * All checkboxes with empty values
 * @type {NodeList}
 */
export const checkNoInputs = document.querySelectorAll('.option-input[type="checkbox"][value=""]');

// ====================== SELECTED INPUT DETECTION ======================

/**
 * SINGLE SELECTED INPUT REFERENCES
 * ================================
 * Currently selected/checked input elements
 */

/**
 * First currently checked radio button
 */
export const radioCheckedInput = document.querySelector('.option-radio[type="radio"]:checked');

/**
 * First currently checked checkbox
 */
export const checkCheckedInput = document.querySelector('.option-check[type="checkbox"]:checked');

/**
 * MULTIPLE SELECTED INPUT COLLECTIONS
 * ===================================
 */

/**
 * All currently checked radio buttons
 * @type {NodeList}
 */
export const radioCheckedInputs = document.querySelectorAll('.option-radio[type="radio"]:checked');

/**
 * All currently checked checkboxes
 * @type {NodeList}
 */
export const checkCheckedInputs = document.querySelectorAll('.option-check[type="checkbox"]:checked');

// ====================== PRICE MATRIX ELEMENTS ======================

/**
 * DYNAMIC PRICING CALCULATION ELEMENTS
 * ====================================
 * These select elements contain price matrices for dimension-based pricing
 */

/**
 * Diameter-based price matrix selector
 * Contains options with diameter values and corresponding prices
 * @type {HTMLSelectElement|null}
 */
export const matrixDurchmesser = document.querySelector('.option-price[name*="pxd"]');

/**
 * Width x Height price matrix selector
 * Contains options with combined dimension values and corresponding prices
 * @type {HTMLSelectElement|null}
 */
export const matrixBreiteHoehe = document.querySelector('.option-price[name*="pxbh"]');

// Debug price matrix elements
if (DEBUG_MODE) {
  if (matrixDurchmesser) console.log("✅ Price matrix Durchmesser found:", matrixDurchmesser.name);
  if (matrixBreiteHoehe) console.log("✅ Price matrix BreiteHoehe found:", matrixBreiteHoehe.name);
}

// ====================== ARCHIVED DETECTION FLAGS ======================

/**
 * LEGACY PRODUCT DETECTION (COMMENTED OUT)
 * ========================================
 * These flags are preserved for potential future use or reference
 * They represent more granular product categorization that may be needed
 */

/*
// Glass type detection flags
export const isGlasESG = document.querySelectorAll('.product-summary.product_cat-esg-glas').length > 0;
export const isGlasVSG = document.querySelectorAll('.product-summary.product_cat-vsg-glas').length > 0;
export const isGlasFloat = document.querySelectorAll('.product-summary.product_cat-floatglas').length > 0;

// Lighting status detection
export const isSpiegelBeleuchtet = document.querySelectorAll('.product-summary.product_cat-spiegel-mit-beleuchtung').length > 0;
export const isSpiegelUnbeleuchtet = document.querySelectorAll('.product-summary.product_cat-spiegel-ohne-beleuchtung').length > 0;

// Shape category detection
export const isRechteckigeSpiegel = document.querySelectorAll('.product-summary.product_cat-rechteckige-spiegel').length > 0;
export const isRundeSpiegel = document.querySelectorAll('.product-summary.product_cat-runde-spiegel').length > 0;
export const isOvaleSpiegel = document.querySelectorAll('.product-summary.product_cat-ovale-spiegel').length > 0;
export const isAbgerundeteSpiegel = document.querySelectorAll('.product-summary.product_cat-abgerundete-spiegel').length > 0;

// Special functionality detection
export const isKlappspiegel = document.querySelectorAll('.product-summary.product_cat-klappspiegel-bad').length > 0;
export const isFacettenspiegel = document.querySelectorAll('.product-summary.product_cat-facettenspiegel').length > 0;
export const isDachschraege = document.querySelectorAll('.product-summary.product_cat-spiegel-fuer-dachschraege').length > 0;

// Corner rounding detection (1-4 corners)
export const isAbgerundeteEcken = document.querySelectorAll('.product-summary.product_cat-spiegel-mit-abgerundeten-ecken').length > 0;
export const isAbgerundeteEcken1 = document.querySelectorAll('.product-summary.product_cat-spiegel-mit-einer-abgerundeten-ecke').length > 0;
export const isAbgerundeteEcken2 = document.querySelectorAll('.product-summary.product_cat-spiegel-mit-zwei-abgerundeten-ecken').length > 0;
export const isAbgerundeteEcken3 = document.querySelectorAll('.product-summary.product_cat-spiegel-mit-drei-abgerundeten-ecken').length > 0;
export const isAbgerundeteEcken4 = document.querySelectorAll('.product-summary.product_cat-spiegel-mit-vier-abgerundeten-ecken').length > 0;

// Detailed LED positioning (alternative detection method)
export const isLEDSpiegelRundherum = document.querySelectorAll('.product-summary.product_cat-led-spiegel-rundherum-beleuchtet').length > 0;
export const isLEDSpiegelLinksObenRechts = document.querySelectorAll('.product-summary.product_cat-led-spiegel-links-oben-rechts-beleuchtet').length > 0;
export const isLEDSpiegelLinksRechts = document.querySelectorAll('.product-summary.product_cat-led-spiegel-links-rechts-beleuchtet').length > 0;
export const isLEDSpiegelObenUnten = document.querySelectorAll('.product-summary.product_cat-led-spiegel-oben-unten-beleuchtet').length > 0;
export const isLEDSpiegelOben = document.querySelectorAll('.product-summary.product_cat-led-spiegel-oben-beleuchtet').length > 0;

// Special lighting effects
export const isAuraEffekt = document.querySelectorAll('.product-summary.product_cat-spiegel-mit-auraeffekt').length > 0;
export const isBlendEffekt = document.querySelectorAll('.product-summary.product_cat-spiegel-mit-blendeffekt').length > 0;
*/

/**
 * Future Enhancement Ideas:
 *
 * 1. Dynamic variable generation:
 *    function createProductFlags(selectors) {
 *      return Object.fromEntries(
 *        selectors.map(sel => [sel.name, document.querySelectorAll(sel.selector).length > 0])
 *      );
 *    }
 *
 * 2. Variable validation system:
 *    function validateVariables() {
 *      const required = ['product', 'configurator', 'addToCartButton'];
 *      return required.every(varName => window[varName] !== null);
 *    }
 *
 * 3. Performance monitoring:
 *    const variablePerformance = {
 *      queryCount: 0,
 *      totalTime: 0,
 *      logQuery: (selector, time) => { ... }
 *    };
 *
 * 4. Variable change detection:
 *    const variableObserver = new MutationObserver(mutations => {
 *      // Re-evaluate variables when DOM changes
 *    });
 *
 * 5. Conditional loading:
 *    const lazyVariables = {
 *      get expensiveVariable() {
 *        return this._cached || (this._cached = computeExpensive());
 *      }
 *    };
 */

// ====================== DEBUG COMPLETE ======================

// Simple debug summary
// if (DEBUG_MODE) {
//   console.log("🔧 Configurator variables loaded");
// }
