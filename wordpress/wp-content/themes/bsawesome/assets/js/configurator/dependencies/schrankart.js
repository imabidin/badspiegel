/**
 * @version 2.5.0
 *
 * @note Seems to be final, no further changes if not needed
 */

import { dependenciesValuesXcontainer } from "./../dependencies.js";

document.addEventListener("DOMContentLoaded", () => {
  /**
   * Bei Halbeinbau automatisch Einbaurahmen auf "ja" setzen
   * (Halbeinbau ohne Einbaurahmen ist nicht möglich)
   *
   * TODO FIX THIS
   */
  //   dependenciesValuesXvalues(
  //     "schrankart",
  //     (value) => {
  //       return (
  //         value === "halbeinbau-teilunterputz"
  //       );
  //     },
  //     "einbaurahmen",
  //     (value) => {
  //       return (
  //         value === ""
  //       );
  //     }
  //   );

  /**
   * Einbaurahmen Tiefe Container - nur bei Halbeinbau sichtbar
   */
  dependenciesValuesXcontainer(
    "einbaurahmen",
    value => {
      return value === "ja";
    },
    "einbaurahmen_tiefe"
  );

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
