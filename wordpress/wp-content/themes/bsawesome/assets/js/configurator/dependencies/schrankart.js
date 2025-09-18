/**
 * @version 2.7.0
 *
 * @note Seems to be final, no more changes needed, unless order bugs arise
 */

import { dependenciesValuesXcontainer } from "./../dependencies.js";

document.addEventListener("DOMContentLoaded", () => {
  /**
   * Bei Halbeinbau automatisch Einbaurahmen auf "ja" setzen
   * (Halbeinbau ohne Einbaurahmen ist nicht möglich)
   *
   * TODO FIX THIS
   */
  dependenciesValuesXcontainer(
    "schrankart",
    value => {
      return value === "einbau_unterputz";
    },
    "einbaurahmen"
  );
  /**
   * Einbaurahmen Tiefe Container - nur bei Halbeinbau sichtbar
   */
  dependenciesValuesXcontainer(
    "schrankart",
    value => {
      return value === "halbeinbau_teilunterputz";
    },
    "einbaurahmen_tiefe"
  );
  /**
   * Einbaurahmen Vorab Container - kombinierte Abhängigkeit
   * Wird nur gezeigt wenn BEIDE Bedingungen erfüllt sind:
   * 1. schrankart = "halbeinbau_teilunterputz" UND
   * 2. einbaurahmen = "ja"
   *
   * Löst das Problem der doppelten Event-Handler die zu Zurücksetzen/Neusetzen führen
   */
  function handleEinbaurahmenVorabVisibility() {
    const container = document.getElementById('option_einbaurahmen_vorab');
    if (!container) return;

    const schrankartInput = document.querySelector('input[name="schrankart"]:checked');
    const einbaurahmenInput = document.querySelector('input[name="einbaurahmen"]:checked');

    const isHalbeinbau = schrankartInput?.value === "halbeinbau_teilunterputz";
    const isEinbaurahmenJa = einbaurahmenInput?.value === "ja";

    const shouldShow = isHalbeinbau || isEinbaurahmenJa;

    if (shouldShow) {
      container.classList.remove("d-none");

      // Auto-select first valid option if none selected
      const inputs = container.querySelectorAll("input");
      const hasSelection = Array.from(inputs).some(input => input.checked);

      if (!hasSelection) {
        const firstInput = inputs[0];
        if (firstInput) {
          firstInput.checked = true;
          firstInput.dispatchEvent(new Event("change", { bubbles: true }));
        }
      }
    } else {
      container.classList.add("d-none");

      // Clear selections
      const inputs = container.querySelectorAll("input");
      inputs.forEach(input => {
        input.checked = false;
      });

      // Select empty option if available
      const emptyOption = container.querySelector('input[value=""]');
      if (emptyOption) {
        emptyOption.checked = true;
        emptyOption.dispatchEvent(new Event("change", { bubbles: true }));
      }
    }
  }

  // Event Listener für beide Trigger
  document.body.addEventListener("change", event => {
    const target = event.target;
    if (target.matches('input[name="schrankart"]') || target.matches('input[name="einbaurahmen"]')) {
      // Kleine Verzögerung um sicherzustellen dass alle Werte aktualisiert sind
      setTimeout(handleEinbaurahmenVorabVisibility, 10);
    }
  });

  // Initial run
  handleEinbaurahmenVorabVisibility();
  /**
   * Einbaurahmen Tiefe Min/Max von Breite-Input ableiten
   */
  function updateEinbaurahmenTiefeConstraints() {
    const breiteInput = document.querySelector('input[name="breite"]');
    const einbaurahmenTiefeInput = document.querySelector('input[name="einbaurahmen_tiefe"]');

    if (!breiteInput || !einbaurahmenTiefeInput) return;

    const breiteValue = parseInt(breiteInput.value) || 0;

    // Einbaurahmen-Tiefe: mindestens 80mm, maximum von der angegebenen Breite
    const einbaurahmenMinimum = 80;

    einbaurahmenTiefeInput.min = einbaurahmenMinimum;
    einbaurahmenTiefeInput.max = breiteValue;

    // Placeholder aktualisieren
    einbaurahmenTiefeInput.placeholder = `Geben Sie einen Wert ein (min: ${einbaurahmenMinimum}, max: ${breiteValue})`;

    // Falls der aktuelle Wert außerhalb der neuen Grenzen liegt, anpassen
    const currentValue = parseInt(einbaurahmenTiefeInput.value) || 0;
    if (currentValue > einbaurahmenTiefeInput.max) {
      einbaurahmenTiefeInput.value = einbaurahmenTiefeInput.max;
    } else if (currentValue < einbaurahmenTiefeInput.min) {
      einbaurahmenTiefeInput.value = einbaurahmenTiefeInput.min;
    }
  }

  // Event Listener für Breite-Änderungen
  const breiteInput = document.querySelector('input[name="breite"]');
  if (breiteInput) {
    breiteInput.addEventListener("input", updateEinbaurahmenTiefeConstraints);
    breiteInput.addEventListener("change", updateEinbaurahmenTiefeConstraints);

    // Initiale Anpassung
    updateEinbaurahmenTiefeConstraints();
  }
});
