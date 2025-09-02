/**
 * @version 2.2.0
 */

import {
  dependenciesValuesXvalues,
  dependenciesValuesXcontainer,
} from "../dependencies.js";

import {} from "../variables.js";

// Mindestabmessungen für jede TV-Größe (Breite x Höhe in mm)
const TV_SIZE_REQUIREMENTS = {
  led_156_zoll_ca_40cm: { minWidth: 600, minHeight: 600 },
  led_19_zoll_ca_48cm: { minWidth: 700, minHeight: 650 },
  led_22_zoll_ca_56cm: { minWidth: 850, minHeight: 650 },
  qled_32_zoll_ca_81cm: { minWidth: 1250, minHeight: 650 },
  qled_43_zoll_ca_109cm: { minWidth: 1450, minHeight: 800 },
};

// Benutzerfreundliche Fehlermeldungen pro TV-Größe
const TV_SIZE_MESSAGES = {
  qled_43_zoll_ca_109cm:
    'Bei einem 43"-TV muss der Spiegel mind. 145 x 80 cm (Breite x Höhe) groß sein. Bitte ändern Sie Breite x Höhe oder wählen Sie einen kleineren Bildschirm.',
  qled_32_zoll_ca_81cm:
    'Bei einem 32"-TV muss der Spiegel mind. 125 x 65 cm (Breite x Höhe) groß sein. Bitte ändern Sie Breite x Höhe oder wählen Sie einen kleineren Bildschirm.',
  led_22_zoll_ca_56cm:
    'Bei einem 22"-TV muss der Spiegel mind. 85 x 65 cm (Breite x Höhe) groß sein. Bitte ändern Sie Breite x Höhe oder wählen Sie einen kleineren Bildschirm.',
  led_19_zoll_ca_48cm:
    'Bei einem 19"-TV sind die Maße zu klein. Bitte vergrößern Sie Breite x Höhe oder wählen Sie einen kleineren Bildschirm.',
  led_156_zoll_ca_40cm:
    "Für diese sehr kleine TV-Größe sollten minimale Maße beachtet werden (siehe Konfiguration).",
};

// Feedback mode: 'alert' or 'modal'
// Set to 'modal' to use the site's modal utility `createModal` (if available)
const FEEDBACK_MODE = "modal";

// DEBUG: set to true to enable console tracing
const DEBUG = false;

function debugLog(...args) {
  if (DEBUG && typeof console !== "undefined" && console.log) {
    console.log("[tv-geraet]", ...args);
  }
}

// runtime marker: always set a minimal global flag, verbose log only when DEBUG
try {
  if (typeof window !== "undefined") {
    window.tvGeraet = window.tvGeraet || {};
    window.tvGeraet.loaded = true;
    window.tvGeraet.loadedAt = new Date().toISOString();
  }
  debugLog("script executed (module evaluation)", {
    loadedAt: window?.tvGeraet?.loadedAt,
  });
} catch (e) {
  /* ignore */
}

// Small HTML escape helper
function escapeHtml(str) {
  if (typeof str !== "string") return String(str);
  return str
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

/**
 * Highlight the most important parts of a message (dimensions) with fw-semibold.
 * @param {string} escapedMessage - message already escaped for HTML
 */
function highlightImportant(escapedMessage) {
  // Match patterns like '1450 x 800 mm' or '1250 x 650 mm' (space-insensitive)
  return escapedMessage.replace(
    /(\d+\s*[x×]\s*\d+\s*mm)/gi,
    '<span class="fw-semibold">$1</span>'
  );
}

/**
 * Show feedback to the user. Uses `alert` or `createModal` depending on FEEDBACK_MODE.
 * @param {string} message
 * @param {string} [title]
 */
function showFeedback(message, title) {
  debugLog("showFeedback called", { mode: FEEDBACK_MODE, title });
  if (FEEDBACK_MODE === "modal" && typeof window.createModal === "function") {
    try {
      // Build a Bootstrap-friendly body with FontAwesome icon
      const escaped = escapeHtml(message);
      const highlighted = highlightImportant(escaped);
      const bodyHtml = `
        <div class="d-flex align-items-start">
          <div class="me-3">
            <i class="fa-thin fa-circle-exclamation fa-2x text-warning" aria-hidden="true"></i>
          </div>
          <div class="fs-6">${highlighted}</div>
        </div>
      `;

      window.createModal({
        title: title || "Hinweis",
        body: bodyHtml,
        // createFooterButton prefixes with 'btn ' so pass only the modifier
        footer: [{ text: "OK", class: "btn-dark", dismiss: true }],
      });
      debugLog("createModal invoked successfully");
      return;
    } catch (e) {
      console.error("[tv-geraet] createModal threw", e);
      // fallback to alert if modal creation fails
    }
  }

  // If modal mode was requested but createModal is not available, warn in console
  if (FEEDBACK_MODE === "modal" && typeof window.createModal !== "function") {
    console.warn(
      'FEEDBACK_MODE is "modal" but window.createModal is not available. Falling back to alert(). Ensure assets/js/modal.js is loaded before tv-geraet.js.'
    );
  }

  // default fallback
  alert(message);
}

// --- Small DOM + state helpers (DRY) -------------------------------------
function getTvOptions() {
  return document.querySelectorAll('input[name="tv_geraet"]');
}

function getValuesGroup() {
  return document.querySelector(".values-group");
}

function getLabelForOption(option) {
  return option.closest("label");
}

function setLabelAvailable(label) {
  if (!label) return;
  label.classList.remove("disabled");
  // label.style.opacity = "1";
  label.style.pointerEvents = "auto";
  delete label.dataset.disabledReason;
}

function setLabelDisabled(label, reason) {
  if (!label) return;
  label.classList.add("disabled");
  // label.style.opacity = "0.5";
  label.style.pointerEvents = "auto"; // keep info button clickable
  if (reason) label.dataset.disabledReason = reason;
}

function getAvailableOptions() {
  return document.querySelectorAll('input[name="tv_geraet"]:not([disabled])');
}

function showNoAvailableFeedback() {
  showFeedback(
    "Für die angegebenen Maße sind keine TV-Geräte verfügbar. Bitte vergrößern Sie die Abmessungen.",
    "Keine TV-Geräte verfügbar"
  );
}

function showLabelReason(label) {
  if (!label) return;
  const reason = label.dataset.disabledReason;
  debugLog("showLabelReason", { reason, label });
  if (reason) showFeedback(reason, "Warum diese Option deaktiviert ist");
  else
    showFeedback(
      "Diese TV-Größe ist für die angegebenen Maße nicht verfügbar.",
      "Warum diese Option deaktiviert ist"
    );
}
// --------------------------------------------------------------------------

// Selected TV tracking (new behavior)
let selectedTvValue = null;
let selectedTvRequirements = null;

function setSelectedTv(tvValue) {
  selectedTvValue = tvValue || null;
  selectedTvRequirements = TV_SIZE_REQUIREMENTS[tvValue] || null;
  debugLog("setSelectedTv", { selectedTvValue, selectedTvRequirements });
}

/**
 * Check current dimensions against the selected TV (if any) and show a hint
 * only when a TV is selected and the current size is smaller than required.
 */
function checkSelectedTvSizing() {
  if (!selectedTvValue || !selectedTvRequirements) return;

  const { width, height } = getCurrentDimensions();
  debugLog("checkSelectedTvSizing", { width, height, selectedTvRequirements });

  const tooNarrow = width < selectedTvRequirements.minWidth;
  const tooShort = height < selectedTvRequirements.minHeight;

  if (tooNarrow || tooShort) {
    // Use the configured message if present, but append a short hint about reduction
    const baseMsg =
      TV_SIZE_MESSAGES[selectedTvValue] ||
      "Die gewählten Maße sind zu klein für das gewählte TV-Gerät.";
    const hint =
      " Das ausgewählte TV-Gerät wird in der Darstellung verkleinert.";
    showFeedback(baseMsg + hint, "Hinweis zur TV-Größe");
    return false;
  }

  return true;
}

// --- Event listeners / initialization (placed near top) ------------------
document.addEventListener("DOMContentLoaded", () => {
  /**
   * Values X Container
   */
  dependenciesValuesXcontainer(
    "tv_seitenanschluss",
    (value) => {
      return value !== "";
    },
    "tv_seitenanschluss_position"
  );

  // Event Listener für Breite- und Höhenänderungen
  const widthInput = document.querySelector('input[name="breite"]');
  const heightInput = document.querySelector('input[name="hoehe"]');

  // TV radio buttons
  const tvRadios = document.querySelectorAll('input[name="tv_geraet"]');
  if (tvRadios && tvRadios.length) {
    tvRadios.forEach((radio) => {
      radio.addEventListener("change", (ev) => {
        if (ev.target.checked) {
          setSelectedTv(ev.target.value);
          // immediate sizing check when a tv is selected
          checkSelectedTvSizing();
        }
      });
    });
    // initialize selectedTv if one is checked on load
    const initiallyChecked = document.querySelector(
      'input[name="tv_geraet"]:checked'
    );
    if (initiallyChecked) setSelectedTv(initiallyChecked.value);
  }

  if (widthInput) {
    widthInput.addEventListener("input", () => checkSelectedTvSizing());
    widthInput.addEventListener("change", () => checkSelectedTvSizing());
  }

  if (heightInput) {
    heightInput.addEventListener("input", () => checkSelectedTvSizing());
    heightInput.addEventListener("change", () => checkSelectedTvSizing());
  }

  // Initiale Prüfung der verfügbaren TV-Größen
  // do not run global validation on load — only act when a tv is selected or radios change
  // handleDimensionChange();

  // Delegierter Klick-Handler: wenn ein deaktiviertes Label angeklickt wird,
  // zeigen wir den Grund an. Info-Buttons (.btn-link mit data-modal-link) bleiben klickbar.
  const valuesGroup = getValuesGroup();
  if (valuesGroup) {
    valuesGroup.addEventListener(
      "click",
      (ev) => {
        const infoBtn = ev.target.closest("button[data-modal-link]");
        if (infoBtn) return; // allow modal button to work

        const label = ev.target.closest("label");
        if (!label) return;

        if (label.classList.contains("disabled")) {
          showLabelReason(label);
          ev.preventDefault();
          ev.stopPropagation();
        }
      },
      true
    );
  }
});

// --------------------------------------------------------------------------

/**
 * Prüft verfügbare TV-Größen basierend auf Breite und Höhe
 * @param {number} width - Breite in mm
 * @param {number} height - Höhe in mm
 */
function updateAvailableTVSizes(width, height) {
  // If there are no tv radio inputs on this page, don't run validation or show warnings.
  const allTvInputs = document.querySelectorAll('input[name="tv_geraet"]');
  if (!allTvInputs || allTvInputs.length === 0) {
    debugLog("updateAvailableTVSizes: no tv_geraet inputs found, skipping");
    return true;
  }
  const tvOptions = getTvOptions();

  tvOptions.forEach((option) => {
    const tvValue = option.value;
    const requirements = TV_SIZE_REQUIREMENTS[tvValue];
    const label = getLabelForOption(option);

    if (!requirements || !label) return;

    const isAvailable =
      width >= requirements.minWidth && height >= requirements.minHeight;

    if (isAvailable) {
      option.disabled = false;
      setLabelAvailable(label);
    } else {
      option.disabled = true;
      option.checked = false;
      setLabelDisabled(label, TV_SIZE_MESSAGES[tvValue]);
    }
  });

  // Prüfen ob mindestens eine Option verfügbar ist
  const availableOptions = getAvailableOptions();
  if (availableOptions.length === 0) {
    showNoAvailableFeedback();
    return false;
  }

  // Erste verfügbare Option auswählen, wenn keine ausgewählt ist
  const checkedOption = document.querySelector(
    'input[name="tv_geraet"]:checked:not([disabled])'
  );
  if (!checkedOption && availableOptions.length > 0) {
    availableOptions[0].checked = true;
    // Trigger change event
    availableOptions[0].dispatchEvent(new Event("change", { bubbles: true }));
  }

  return true;
}

/**
 * Holt die aktuellen Werte für Breite und Höhe
 */
function getCurrentDimensions() {
  const widthInput = document.querySelector('input[name="breite"]');
  const heightInput = document.querySelector('input[name="hoehe"]');

  return {
    width: widthInput ? parseInt(widthInput.value) || 0 : 0,
    height: heightInput ? parseInt(heightInput.value) || 0 : 0,
  };
}

/**
 * Event Handler für Dimensionsänderungen
 */
function handleDimensionChange() {
  const { width, height } = getCurrentDimensions();

  if (width > 0 && height > 0) {
    updateAvailableTVSizes(width, height);
  }
}
