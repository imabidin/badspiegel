/**
 * Initializes Bootstrap tooltips for attributes: [data-bs-tooltip="true"] and [data-bs-tooltip-md="true"]
 * Tooltips with [data-bs-tooltip-md] will initialize only if viewport is md or larger.
 *
 * @version Final
 */
let tooltipInstances = [];

function destroyTooltips() {
  tooltipInstances.forEach((instance) => {
    // Null-Check hinzufügen und prüfen ob dispose verfügbar ist
    if (instance && typeof instance.dispose === "function") {
      try {
        // Prüfen ob das zugehörige Element noch im DOM existiert
        const element = instance._element || instance.element;
        if (element && element.isConnected) {
          instance.dispose();
        } else {
          // Element ist nicht mehr im DOM, Instanz manuell "bereinigen"
          instance._element = null;
        }
      } catch (error) {
        console.warn("Error disposing tooltip:", error);
      }
    }
  });
  tooltipInstances = [];
}

function initTooltips() {
  if (!window.bootstrap?.Tooltip) return;

  destroyTooltips();

  const viewportMd = window.matchMedia("(min-width: 768px)").matches;

  // Elemente auswählen
  const triggers = Array.from(
    document.querySelectorAll(
      '[data-bs-tooltip="true"], [data-bs-tooltip-md="true"]'
    )
  );

  triggers.forEach((el) => {
    // Element-Check hinzufügen
    if (!el || !el.isConnected) return;

    // Überspringen, falls data-bs-tooltip-md und Viewport kleiner als md
    if (el.hasAttribute("data-bs-tooltip-md") && !viewportMd) return;

    try {
      const tooltip = new bootstrap.Tooltip(el, {
        container: "body",
      });
      tooltipInstances.push(tooltip);

      // Tooltip bei Klick auf Elemente mit data-modal-* ausblenden
      if (
        [...el.attributes].some((attr) => attr.name.startsWith("data-modal"))
      ) {
        el.addEventListener("click", () => {
          if (tooltip && typeof tooltip.hide === "function") {
            tooltip.hide();
          }
        });
      }
    } catch (error) {
      console.warn("Error creating tooltip for element:", el, error);
    }
  });
}

// Global verfügbar machen
window.initTooltips = initTooltips;

document.addEventListener("DOMContentLoaded", initTooltips);

// Reagiert performant auf Breakpoint-Wechsel
const mdMedia = window.matchMedia("(min-width: 768px)");
mdMedia.addEventListener("change", () => {
  initTooltips();
});
