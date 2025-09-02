/**
 * Philips Hue Lighting Configuration Module
 *
 * This module handles the dynamic pricing and configuration logic for Philips Hue
 * lighting options in the product configurator. It manages both main lighting
 * and ambient lighting configurations, calculating appropriate pricing based on
 * the product category, dimensions, and selected lighting type.
 *
 * Features:
 * - Product category-specific lighting calculations
 * - Dynamic price calculation based on perimeter and lighting positions
 * - Automatic category selection for different perimeter ranges
 * - Price display updates for lightstrip variants (Plus vs Gradient)
 * - Reset functionality when switching away from Philips Hue
 * - Support for multiple product types (Badspiegel, Spiegelschrank, Cabinets)
 * - Incompatible option management (auto-hide Lichtstärke and Smart Home)
 * - Container visibility management for Philips Hue specific options
 *
 * Product Categories:
 * - Badspiegel: Main + Ambient lighting (uses position flags for main lighting)
 * - Spiegelschrank: Main + Ambient lighting (forced LORU for main lighting)
 * - Unterschrank/Hochschrank/Side-Lowboards: Ambient lighting only
 *
 * Main Lighting Logic:
 * - Badspiegel: Respects isLORU, isLOR, isLR, isOU, isO flags
 * - Spiegelschrank: Always calculates as LORU (all sides)
 * - Cabinets: No main lighting
 *
 * Ambient Lighting Logic:
 * - All categories: Based on selected ambient position options
 * - Calculates perimeter based on lighting position selections
 * - Supports various positions (waschbecken, decke, seiten, rundherum, etc.)
 *
 * Dependencies:
 * - variables.js: Dimension inputs, lighting position flags, and product category flags
 *
 * @version 2.2.0
 * @package Configurator
 */

import {
  durchmesserInput,
  breiteInput,
  hoeheInput,
  isLORU,
  isLOR,
  isLR,
  isOU,
  isO,
  isBadspiegel,
  isSpiegelschrank,
  isUnterschrank,
  isHochschrank,
  isBoard,
} from "../variables.js";

import {
  dependenciesValuesXcontainer,
  dependenciesValuesXvalues,
} from "../dependencies.js";

// ========================================
// CONFIGURATION & CONSTANTS
// ========================================

/**
 * DEBUG MODE CONFIGURATION
 * Set to true to enable debug logging, false to disable all debug output
 */
const DEBUG_MODE = false;

/**
 * Perimeter categories for pricing lookup
 */
const PERIMETER_CATEGORIES = {
  2: "bis_2m",
  3: "bis_3m",
  4: "bis_4m",
  5: "bis_5m",
  6: "bis_6m",
  8: "bis_8m",
  9: "bis_9m",
  10: "bis_10m",
};

/**
 * Lighting type constants
 */
const LIGHTING_TYPES = {
  MAIN: "lichtfarbe",
  AMBIENT: "ambientelicht",
};

/**
 * Lightstrip variant constants
 */
const LIGHTSTRIP_VARIANTS = {
  PLUS: "lightstrip_plus",
  GRADIENT: "lightstrip_gradient",
};

// ========================================
// GLOBAL VARIABLES & STATE
// ========================================

let mainLightingElements = {};
let ambientLightingElements = {};
let allLightingInputs = {};

// ========================================
// INITIALIZATION & EVENT LISTENERS
// ========================================

/**
 * Initialize Philips Hue configuration on DOM ready
 */
document.addEventListener("DOMContentLoaded", () => {
  // Prevent multiple initializations
  if (window.philipsHueInitialized) {
    debugLog("Philips Hue module already initialized, skipping");
    return;
  }

  debugLog("Initializing Philips Hue module");
  window.philipsHueInitialized = true;

  initializeDOMElements();
  setupEventListeners();
  performInitialUpdates();

  debugLog("Philips Hue module initialization completed");
});

/**
 * Initialize DOM element references
 */
const initializeDOMElements = () => {
  // Main lighting elements
  mainLightingElements = {
    radioHue: document.querySelector(
      "input[name='lichtfarbe'][value='philips_hue']"
    ),
    radioOptions: document.querySelectorAll(
      "input[name='lichtfarbe_philips_hue']"
    ),
    selectPlus: document.querySelector(
      "select[name='lichtfarbe_philips_hue_lightstrip_aufpreis']"
    ),
    selectGradient: document.querySelector(
      "select[name='lichtfarbe_philips_hue_gradient_aufpreis']"
    ),
  };

  // Ambient lighting elements
  ambientLightingElements = {
    radioHue: document.querySelector(
      "input[value='philips_hue'][name='ambientelicht_lichtfarbe']"
    ),
    radioOptions: document.querySelectorAll(
      "input[name='ambientelicht_philips_hue']"
    ),
    selectPlus: document.querySelector(
      "select[name='ambientelicht_philips_hue_lightstrip_aufpreis']"
    ),
    selectGradient: document.querySelector(
      "select[name='ambientelicht_philips_hue_gradient_aufpreis']"
    ),
  };

  // All lighting color inputs for reset functionality
  allLightingInputs = {
    main: document.querySelectorAll("input[name='lichtfarbe']"),
    ambient: document.querySelectorAll(
      "input[name='ambientelicht_lichtfarbe']"
    ),
  };
};

/**
 * Set up all event listeners
 */
const setupEventListeners = () => {
  setupDimensionInputListeners();
  setupMainLightingListeners();
  setupAmbientLightingListeners();
  setupResetListeners();
  setupPhilipsHueDependencies();
};

/**
 * Set up dimension input listeners for real-time price updates
 */
const setupDimensionInputListeners = () => {
  const dimensionInputs = [durchmesserInput, breiteInput, hoeheInput].filter(
    Boolean
  );

  dimensionInputs.forEach((input) => {
    if (!input) return;

    ["input", "change", "blur"].forEach((eventType) => {
      input.addEventListener(eventType, () => {
        if (isValidInput(input)) {
          debugLog(
            `Dimension ${eventType} event triggered, updating Philips Hue configurations`
          );
          updateHueConfigurations();
        } else {
          debugLog(`Invalid input for ${eventType} event:`, input.value);
        }
      });
    });
  });
};

/**
 * Set up main lighting Philips Hue option listeners
 */
const setupMainLightingListeners = () => {
  if (!hasMainLighting() || !mainLightingElements.radioOptions?.length) {
    debugLog(
      "Main lighting not available or no radio options found for this product category"
    );
    return;
  }

  debugLog("Setting up main lighting Philips Hue listeners");
  mainLightingElements.radioOptions.forEach((radio) => {
    if (radio) {
      radio.addEventListener("change", () => {
        debugLog("Main lighting Philips Hue option changed:", radio.value);
        updateMainLightingHueConfiguration();
      });
    }
  });
};

/**
 * Set up ambient lighting Philips Hue option listeners
 */
const setupAmbientLightingListeners = () => {
  if (!hasAmbientLighting() || !ambientLightingElements.radioOptions?.length) {
    debugLog(
      "Ambient lighting not available or no radio options found for this product category"
    );
    return;
  }

  debugLog("Setting up ambient lighting Philips Hue listeners");
  ambientLightingElements.radioOptions.forEach((radio) => {
    if (radio) {
      radio.addEventListener("change", () => {
        debugLog("Ambient lighting Philips Hue option changed:", radio.value);
        updateAmbientLightingHueConfiguration();
      });
    }
  });
};

/**
 * Set up reset listeners for lighting color changes
 */
const setupResetListeners = () => {
  // Main lighting reset listeners
  if (hasMainLighting() && allLightingInputs.main?.length) {
    allLightingInputs.main.forEach((radio) => {
      if (!radio) return;

      radio.addEventListener("change", (event) => {
        debugLog("Main lighting color changed:", event.target.value);
        if (event.target.value !== "philips_hue") {
          resetHueSelects(LIGHTING_TYPES.MAIN);
        }
      });
    });
  }

  // Ambient lighting reset listeners
  if (hasAmbientLighting() && allLightingInputs.ambient?.length) {
    allLightingInputs.ambient.forEach((radio) => {
      if (!radio) return;

      radio.addEventListener("change", (event) => {
        debugLog("Ambient lighting color changed:", event.target.value);
        if (event.target.value !== "philips_hue") {
          resetHueSelects(LIGHTING_TYPES.AMBIENT);
        }
      });
    });
  }
};

/**
 * Set up Philips Hue dependencies using the existing dependenciesValuesXcontainer function
 * This is much cleaner than duplicating the logic
 */
const setupPhilipsHueDependencies = () => {
  // Hide incompatible containers when Philips Hue is selected (main lighting)
  dependenciesValuesXcontainer(
    "lichtfarbe",
    (value) => value !== "philips_hue", // Show when NOT Philips Hue
    "lichtstaerke",
    DEBUG_MODE
  );

  dependenciesValuesXcontainer(
    "lichtfarbe",
    (value) => value !== "philips_hue", // Show when NOT Philips Hue
    "smart_home",
    DEBUG_MODE
  );

  // Hide incompatible containers when Philips Hue is selected (ambient lighting)
  dependenciesValuesXcontainer(
    "ambientelicht_lichtfarbe",
    (value) => value !== "philips_hue", // Show when NOT Philips Hue
    "lichtstaerke",
    DEBUG_MODE
  );

  dependenciesValuesXcontainer(
    "ambientelicht_lichtfarbe",
    (value) => value !== "philips_hue", // Show when NOT Philips Hue
    "smart_home",
    DEBUG_MODE
  );

  // Show Philips Hue containers when Philips Hue is selected
  dependenciesValuesXcontainer(
    "lichtfarbe",
    (value) => value === "philips_hue", // Show when Philips Hue
    "lichtfarbe_philips_hue",
    DEBUG_MODE
  );

  dependenciesValuesXcontainer(
    "ambientelicht_lichtfarbe",
    (value) => value === "philips_hue", // Show when Philips Hue
    "ambientelicht_philips_hue",
    DEBUG_MODE
  );

  // Show Philips Hue App option only when lichtfarbe contains "hue"
  dependenciesValuesXvalues(
    "lichtfarbe",
    (value) => {
      return value.includes("hue");
    },
    "bedienung",
    (value) => {
      return value === "philips_hue_app";
    },
    DEBUG_MODE
  );

  debugLog(
    "Philips Hue dependencies set up using dependenciesValuesXcontainer"
  );
};

/**
 * Perform initial updates on page load
 */
const performInitialUpdates = () => {
  setTimeout(() => {
    debugLog("Performing initial updates");
    updateHueConfigurations();
  }, 100);
};

// ========================================
// MAIN UPDATE FUNCTIONS
// ========================================

/**
 * Updates both main and ambient lighting Hue configurations
 */
const updateHueConfigurations = () => {
  updateMainLightingHueConfiguration();
  updateAmbientLightingHueConfiguration();
};

/**
 * Updates pricing and UI for main lighting Philips Hue options
 */
const updateMainLightingHueConfiguration = () => {
  if (!hasMainLighting()) {
    debugLog(
      "Main lighting not available for this product category, skipping update"
    );
    return;
  }

  if (!areElementsValid(mainLightingElements)) {
    debugLog("Main lighting Hue elements not available, skipping update");
    return;
  }

  updateLightingConfiguration(LIGHTING_TYPES.MAIN, mainLightingElements);
};

/**
 * Updates pricing and UI for ambient lighting Philips Hue options
 */
const updateAmbientLightingHueConfiguration = () => {
  if (!hasAmbientLighting()) {
    debugLog(
      "Ambient lighting not available for this product category, skipping update"
    );
    return;
  }

  if (!areElementsValid(ambientLightingElements)) {
    debugLog("Ambient lighting Hue elements not available, skipping update");
    return;
  }

  updateLightingConfiguration(LIGHTING_TYPES.AMBIENT, ambientLightingElements);
};

/**
 * Generic function to update lighting configuration for both main and ambient lighting
 */
const updateLightingConfiguration = (lightingType, elements) => {
  try {
    const perimeter = calculatePerimeter(lightingType);
    if (perimeter === 0) {
      debugLog(`No valid perimeter calculated for ${lightingType}`);
      return;
    }

    const category = mapPerimeterToCategory(perimeter);
    if (!category) {
      debugLog("No category found for perimeter:", perimeter);
      return;
    }

    const selectedType = getSelectedLightingType(lightingType);
    const { plusPrice, gradientPrice } = getPricesForCategory(
      elements,
      category
    );

    debugLog(`${lightingType} update:`, {
      perimeter,
      category,
      selectedType,
      plusPrice,
      gradientPrice,
    });

    // Update UI elements
    updateBasePriceDisplay(elements.radioHue, plusPrice);
    updatePriceDisplay(
      elements.radioOptions,
      LIGHTSTRIP_VARIANTS.PLUS,
      plusPrice
    );
    updatePriceDisplay(
      elements.radioOptions,
      LIGHTSTRIP_VARIANTS.GRADIENT,
      gradientPrice
    );
    updateSelectValues(
      elements,
      category,
      selectedType,
      plusPrice,
      gradientPrice
    );
  } catch (error) {
    debugWarn(`Error updating ${lightingType} Hue configuration:`, error);
  }
};

// ========================================
// CALCULATION FUNCTIONS
// ========================================

/**
 * Calculates the perimeter based on mirror dimensions and lighting type
 */
const calculatePerimeter = (lightingType = LIGHTING_TYPES.AMBIENT) => {
  try {
    // Handle circular mirrors (diameter input)
    if (durchmesserInput?.value && parseFloat(durchmesserInput.value) > 0) {
      const diameter = parseInputValue(durchmesserInput.value);
      if (diameter <= 0) {
        debugLog("Invalid diameter value:", durchmesserInput.value);
        return 0;
      }
      return Math.ceil(Math.PI * diameter * 100) / 100;
    }

    // Handle rectangular mirrors (width and height inputs)
    const hasWidth = breiteInput?.value && parseFloat(breiteInput.value) > 0;
    const hasHeight = hoeheInput?.value && parseFloat(hoeheInput.value) > 0;

    if (hasWidth || hasHeight) {
      const width = parseInputValue(breiteInput?.value || "0");
      const height = parseInputValue(hoeheInput?.value || "0");

      if (width <= 0 || height <= 0) {
        debugLog("Invalid rectangular dimensions:", {
          width,
          height,
          hasWidth,
          hasHeight,
        });
        return 0;
      }

      return lightingType === LIGHTING_TYPES.AMBIENT
        ? calculateAmbientPerimeter(width, height)
        : calculateMainLightingPerimeter(width, height);
    }

    debugLog("No valid dimensions found for perimeter calculation");
    return 0;
  } catch (error) {
    debugWarn("Error calculating perimeter:", error);
    return 0;
  }
};

/**
 * Calculates perimeter for ambient lighting based on product category and selected position
 */
const calculateAmbientPerimeter = (width, height) => {
  if (!validateDimensions(width, height)) return 0;

  const selectedOption = getSelectedAmbientOption();
  if (!selectedOption) {
    debugLog("No ambient lighting option selected");
    return 0;
  }

  debugLog("Product categories for ambient lighting:", {
    isBadspiegel,
    isSpiegelschrank,
    isUnterschrank,
    isHochschrank,
    isBoard,
  });
  debugLog("Selected ambient option:", selectedOption);

  // Product category specific logic
  if (isBadspiegel || isSpiegelschrank) {
    return calculateMirrorAmbientLighting(width, height, selectedOption);
  }

  if (isUnterschrank || isHochschrank || isBoard) {
    return calculateCabinetAmbientLighting(width, height, selectedOption);
  }

  // Fallback: Use mirror logic for unknown categories
  debugWarn(
    "Unknown product category for ambient lighting, using mirror logic"
  );
  return calculateMirrorAmbientLighting(width, height, selectedOption);
};

/**
 * Calculates perimeter for main lighting based on product category and lighting position flags
 */
const calculateMainLightingPerimeter = (width, height) => {
  if (!validateDimensions(width, height)) return 0;

  debugLog("Product categories:", {
    isBadspiegel,
    isSpiegelschrank,
    isUnterschrank,
    isHochschrank,
    isBoard,
  });
  debugLog("Main lighting flags:", { isLORU, isLOR, isLR, isOU, isO });

  // Product category specific logic
  if (isBadspiegel) {
    return calculateBadspiegelMainLighting(width, height);
  }

  if (isSpiegelschrank) {
    // Spiegelschrank: Always LORU (all sides), regardless of detected flags
    const perimeter = Math.ceil(2 * (width + height) * 100) / 100;
    debugLog("Spiegelschrank main lighting (forced LORU):", {
      width,
      height,
      perimeter,
    });
    return perimeter;
  }

  // Cabinets don't have main lighting
  if (isUnterschrank || isHochschrank || isBoard) {
    debugLog("Product category has no main lighting:", {
      isUnterschrank,
      isHochschrank,
      isBoard,
    });
    return 0;
  }

  // Fallback: Use Badspiegel logic for unknown product categories
  debugWarn("Unknown product category, using fallback logic");
  return calculateBadspiegelMainLighting(width, height);
};

/**
 * Calculates ambient lighting perimeter for mirrors (Badspiegel and Spiegelschrank)
 */
const calculateMirrorAmbientLighting = (width, height, selectedOption) => {
  const lightingCalculations = {
    waschbeckenbeleuchtung: () => Math.ceil(width * 100) / 100,
    deckenbeleuchtung: () => Math.ceil(width * 100) / 100,
    unten: () => Math.ceil(width * 100) / 100,
    oben: () => Math.ceil(width * 100) / 100,
    seitenbeleuchtung: () => Math.ceil(2 * height * 100) / 100,
    wasch_und_deckenbeleuchtung: () => Math.ceil(2 * width * 100) / 100,
    oben_und_unten: () => Math.ceil(2 * width * 100) / 100,
    wasch_und_seitenbeleuchtung: () =>
      Math.ceil((width + 2 * height) * 100) / 100,
    decken_und_seitenbeleuchtung: () =>
      Math.ceil((width + 2 * height) * 100) / 100,
    rundherumbeleuchtung: () => Math.ceil(2 * (width + height) * 100) / 100,
  };

  const calculation = lightingCalculations[selectedOption];
  if (calculation) {
    const perimeter = calculation();
    debugLog("Mirror ambient lighting calculation:", {
      selectedOption,
      width,
      height,
      perimeter,
    });
    return perimeter;
  }

  debugWarn("Unknown mirror ambient lighting option:", selectedOption);
  return 0;
};

/**
 * Calculates ambient lighting perimeter for cabinets
 */
const calculateCabinetAmbientLighting = (width, height, selectedOption) => {
  const productType = getProductTypeName();

  const lightingCalculations = {
    waschbeckenbeleuchtung: () => Math.ceil(width * 100) / 100,
    deckenbeleuchtung: () => Math.ceil(width * 100) / 100,
    unten: () => Math.ceil(width * 100) / 100,
    oben: () => Math.ceil(width * 100) / 100,
    seitenbeleuchtung: () => Math.ceil(2 * height * 100) / 100,
    wasch_und_deckenbeleuchtung: () => Math.ceil(2 * width * 100) / 100,
    oben_und_unten: () => Math.ceil(2 * width * 100) / 100,
    wasch_und_seitenbeleuchtung: () =>
      Math.ceil((width + 2 * height) * 100) / 100,
    decken_und_seitenbeleuchtung: () =>
      Math.ceil((width + 2 * height) * 100) / 100,
    rundherumbeleuchtung: () => Math.ceil(2 * (width + height) * 100) / 100,
    sockelbeleuchtung: () => Math.ceil(width * 100) / 100,
    innenbeleuchtung: () => Math.ceil(2 * (width + height) * 100) / 100,
  };

  const calculation = lightingCalculations[selectedOption];
  if (calculation) {
    const perimeter = calculation();
    debugLog(`${productType} ambient lighting calculation:`, {
      selectedOption,
      width,
      height,
      perimeter,
    });
    return perimeter;
  }

  debugWarn(`Unknown ${productType} ambient lighting option:`, selectedOption);
  return 0;
};

/**
 * Calculates main lighting perimeter for Badspiegel based on lighting position flags
 */
const calculateBadspiegelMainLighting = (width, height) => {
  const lightingFlags = [
    {
      flag: isLORU,
      calculation: () => Math.ceil(2 * (width + height) * 100) / 100,
      name: "LORU",
    },
    {
      flag: isLOR,
      calculation: () => Math.ceil((width + 2 * height) * 100) / 100,
      name: "LOR",
    },
    {
      flag: isLR,
      calculation: () => Math.ceil(2 * height * 100) / 100,
      name: "LR",
    },
    {
      flag: isOU,
      calculation: () => Math.ceil(2 * width * 100) / 100,
      name: "OU",
    },
    { flag: isO, calculation: () => Math.ceil(width * 100) / 100, name: "O" },
  ];

  for (const { flag, calculation, name } of lightingFlags) {
    if (flag) {
      const perimeter = calculation();
      debugLog(`Badspiegel ${name} calculation:`, { width, height, perimeter });
      return perimeter;
    }
  }

  debugWarn("No main lighting position flag is active for Badspiegel!", {
    isLORU,
    isLOR,
    isLR,
    isOU,
    isO,
  });
  return 0;
};

// ========================================
// RESET FUNCTIONS
// ========================================

/**
 * Resets Philips Hue select fields and pricing
 */
const resetHueSelects = (lightingType) => {
  try {
    const elements =
      lightingType === LIGHTING_TYPES.MAIN
        ? mainLightingElements
        : ambientLightingElements;
    debugLog(`Resetting ${lightingType} Hue selects`);

    // Reset select field values
    if (elements.selectPlus) elements.selectPlus.value = "";
    if (elements.selectGradient) elements.selectGradient.value = "";

    // Reset radio button prices and UI displays
    if (elements.radioOptions) {
      elements.radioOptions.forEach((radio) => {
        if (radio) {
          // Clear data attributes
          delete radio.dataset.price;
          radio.removeAttribute("data-price");

          // Clear price display in UI
          clearPriceDisplay(radio);
        }
      });
    }

    // Reset base price display
    updateBasePriceDisplay(elements.radioHue, "");

    debugLog(`${lightingType} Hue reset completed`);
  } catch (error) {
    debugWarn(`Error resetting ${lightingType} Hue selects:`, error);
  }
};

// ========================================
// UI UPDATE HELPER FUNCTIONS
// ========================================

/**
 * Updates the base price display for Philips Hue main option
 */
const updateBasePriceDisplay = (radioElement, price) => {
  if (!radioElement) return;

  const priceContainer = getPriceContainer(radioElement);
  if (priceContainer) {
    priceContainer.textContent = price ? `(ab ${price} €)` : "";
  }
};

/**
 * Updates the price display in the UI for a specific radio button
 */
const updatePriceDisplay = (radioElements, targetValue, price) => {
  if (!radioElements || !targetValue) return;

  const radio = Array.from(radioElements).find(
    (r) => r && r.value === targetValue
  );
  if (!radio) return;

  const priceContainer = getPriceContainer(radio);
  if (priceContainer) {
    priceContainer.textContent = price ? `(+${price} €)` : "";
  }
};

/**
 * Updates select values and radio prices based on selected type
 */
const updateSelectValues = (
  elements,
  category,
  selectedType,
  plusPrice,
  gradientPrice
) => {
  if (selectedType === LIGHTSTRIP_VARIANTS.GRADIENT) {
    elements.selectGradient.value = category;
    elements.selectPlus.value = "";
    setRadioPrice(
      elements.radioOptions,
      LIGHTSTRIP_VARIANTS.GRADIENT,
      gradientPrice
    );
  } else if (selectedType === LIGHTSTRIP_VARIANTS.PLUS) {
    elements.selectPlus.value = category;
    elements.selectGradient.value = "";
    setRadioPrice(elements.radioOptions, LIGHTSTRIP_VARIANTS.PLUS, plusPrice);
  } else {
    // No specific type selected - show both options
    elements.selectPlus.value = "";
    elements.selectGradient.value = "";
  }
};

/**
 * Sets the data-price attribute for a specific radio button option
 */
const setRadioPrice = (radioElements, targetValue, price) => {
  if (!radioElements || !targetValue) return;

  radioElements.forEach((radio) => {
    if (radio && radio.value === targetValue) {
      if (price) {
        radio.dataset.price = price;
        radio.setAttribute("data-price", price);
      } else {
        delete radio.dataset.price;
        radio.removeAttribute("data-price");
      }
    }
  });
};

/**
 * Clears price display for a radio button
 */
const clearPriceDisplay = (radio) => {
  const priceContainer = getPriceContainer(radio);
  if (priceContainer) {
    priceContainer.textContent = "";
  }
};

/**
 * Gets the price container element for a radio button
 */
const getPriceContainer = (radioElement) => {
  if (!radioElement) return null;

  const label = document.querySelector(`label.btn[for="${radioElement.id}"]`);
  return label?.querySelector(".value-price");
};

// ========================================
// UTILITY & HELPER FUNCTIONS
// ========================================

/**
 * Safely parses input value and converts from millimeters to meters
 */
const parseInputValue = (input) => {
  if (input === null || input === undefined || input === "") {
    return 0;
  }

  const parsed = parseFloat(input);
  return isNaN(parsed) || parsed < 0 ? 0 : parsed / 1000; // Convert mm to m, ensure non-negative
};

/**
 * Maps perimeter to appropriate pricing category
 */
const mapPerimeterToCategory = (perimeter) => {
  if (!perimeter || perimeter <= 0) {
    debugLog("Invalid perimeter for category mapping:", perimeter);
    return "";
  }

  for (const [maxPerimeter, category] of Object.entries(PERIMETER_CATEGORIES)) {
    if (perimeter <= parseFloat(maxPerimeter)) {
      debugLog("Perimeter category mapping:", { perimeter, category });
      return category;
    }
  }

  debugWarn("Perimeter exceeds maximum category (10m):", perimeter);
  return "";
};

/**
 * Validates input value against min/max constraints
 */
const isValidInput = (input) => {
  try {
    if (!input || !input.value || input.value.trim() === "") {
      return false;
    }

    const value = parseFloat(input.value);
    if (isNaN(value) || value <= 0) {
      return false;
    }

    const min = input.min ? parseFloat(input.min) : -Infinity;
    const max = input.max ? parseFloat(input.max) : Infinity;

    return value >= min && value <= max;
  } catch (error) {
    debugWarn("Error validating input:", error);
    return false;
  }
};

/**
 * Validates dimensions
 */
const validateDimensions = (width, height) => {
  if (!width || !height || width <= 0 || height <= 0) {
    debugWarn("Invalid dimensions:", { width, height });
    return false;
  }
  return true;
};

/**
 * Checks if the current product category supports main lighting
 */
const hasMainLighting = () => {
  const hasMain = isBadspiegel || isSpiegelschrank;
  debugLog("Main lighting availability:", {
    isBadspiegel,
    isSpiegelschrank,
    isUnterschrank,
    isHochschrank,
    isBoard,
    hasMain,
  });
  return hasMain;
};

/**
 * Checks if the current product category supports ambient lighting
 */
const hasAmbientLighting = () => {
  const hasAmbient =
    isBadspiegel ||
    isSpiegelschrank ||
    isUnterschrank ||
    isHochschrank ||
    isBoard;
  debugLog("Ambient lighting availability:", {
    isBadspiegel,
    isSpiegelschrank,
    isUnterschrank,
    isHochschrank,
    isBoard,
    hasAmbient,
  });
  return hasAmbient;
};

/**
 * Checks if all required elements are valid
 */
const areElementsValid = (elements) => {
  return elements.radioHue && elements.selectPlus && elements.selectGradient;
};

/**
 * Gets the selected ambient lighting option
 */
const getSelectedAmbientOption = () => {
  const ambientElement = document.querySelector(
    "input[name='ambientelicht']:checked"
  );
  return ambientElement?.value;
};

/**
 * Gets the selected lighting type for main or ambient lighting
 */
const getSelectedLightingType = (lightingType) => {
  const selectorName =
    lightingType === LIGHTING_TYPES.MAIN
      ? "lichtfarbe_philips_hue"
      : "ambientelicht_philips_hue";
  const selectedTypeElement = document.querySelector(
    `input[name='${selectorName}']:checked`
  );
  return selectedTypeElement?.value;
};

/**
 * Gets prices for a category from select options
 */
const getPricesForCategory = (elements, category) => {
  const plusOption = elements.selectPlus.querySelector(
    `option[value="${category}"]`
  );
  const gradientOption = elements.selectGradient.querySelector(
    `option[value="${category}"]`
  );

  if (!plusOption || !gradientOption) {
    debugWarn("Missing select options for category:", {
      category,
      plusOption: !!plusOption,
      gradientOption: !!gradientOption,
    });
    return { plusPrice: "", gradientPrice: "" };
  }

  return {
    plusPrice: plusOption.dataset.price || "",
    gradientPrice: gradientOption.dataset.price || "",
  };
};

/**
 * Gets the product type name for logging
 */
const getProductTypeName = () => {
  return isUnterschrank
    ? "Unterschrank"
    : isHochschrank
    ? "Hochschrank"
    : isBoard
    ? "Side/Lowboard"
    : "Cabinet";
};

// ========================================
// DEBUG HELPER FUNCTIONS
// ========================================

/**
 * Debug helper functions that respect DEBUG_MODE setting
 */
const debugLog = (...args) => {
  if (DEBUG_MODE) {
    console.debug("[Philips Hue]", ...args);
  }
};

const debugWarn = (...args) => {
  if (DEBUG_MODE) {
    console.warn("[Philips Hue]", ...args);
  }
};

const debugInfo = (...args) => {
  if (DEBUG_MODE) {
    console.info("[Philips Hue]", ...args);
  }
};
