/**
 * Configurator
 *
 * @version 2.5.0
 */

// Import Scss
import "./scss/configurator.scss";

// Import Js
import "./js/configurator/configcode/save";
// import "./js/configurator/configcode/load"; // Included in global.js

import "./js/configurator/pricecalcs/pricematrices";
import "./js/configurator/pricecalcs/sk1";
import "./js/configurator/pricecalcs/sk2";

import "./js/configurator/carousel";
import "./js/configurator/input";
import "./js/configurator/offdrops";
import "./js/configurator/summary";

// Dependencies
// import "./js/configurator/dependencies";
import "./js/configurator/dependencies/ablage";
import "./js/configurator/dependencies/ablagen";
import "./js/configurator/dependencies/auflage";
import "./js/configurator/dependencies/ambientelicht";
import "./js/configurator/dependencies/ausschnitte";
import "./js/configurator/dependencies/bedienung";
import "./js/configurator/dependencies/digital-uhr";
// import "./js/configurator/dependencies/facette";
import "./js/configurator/dependencies/lichtfarbe";
import "./js/configurator/dependencies/philips-hue";
import "./js/configurator/dependencies/tv-geraet";
import "./js/configurator/dependencies/tv-seitenanschluss";
import "./js/configurator/dependencies/anschluesse";
import "./js/configurator/dependencies/verblendung";
import "./js/configurator/dependencies/schrankart";
import "./js/configurator/dependencies/schminkspiegel";
import "./js/configurator/dependencies/spiegelheizung";
import "./js/configurator/dependencies/steckdose";
import "./js/configurator/dependencies/tueren";
import "./js/configurator/dependencies/tuergriff";

// Events
import "./js/configurator/events";

document.addEventListener("DOMContentLoaded", () => {
  // Alle Radio-Inputs in dieser Option-Gruppe holen
  const radios = document.querySelectorAll(
    '#option_montageset input[type="radio"]'
  );

  function updateSelection() {
    radios.forEach((radio) => {
      const label = document.querySelector(`label[for="${radio.id}"]`);
      if (!label) return;

      if (radio.checked) {
        label.classList.add("yes-selection");
        label.classList.remove("no-selection");
      } else {
        label.classList.add("no-selection");
        label.classList.remove("yes-selection");
      }
    });
  }

  // Auf Änderungen reagieren
  radios.forEach((radio) => {
    radio.addEventListener("change", updateSelection);
  });

  // Initiale Markierung beim Laden
  updateSelection();
});
