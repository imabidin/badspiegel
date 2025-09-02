/**
 * @version 2.2.0
 */

import {
  dependenciesValuesXvalues,
  dependenciesValuesXcontainer,
} from "./../dependencies.js";

import {} from "./../variables.js";

document.addEventListener("DOMContentLoaded", () => {
  /**
   * Values X Container
   */
  dependenciesValuesXcontainer(
    "tueren",
    (value) => value === "1x",
    "tuer_anschlagrichtung"
  );
  dependenciesValuesXcontainer(
    "tueren",
    (value) =>
      value === "2x" ||
      value === "3x" ||
      value === "4x" ||
      value === "5x" ||
      value === "6x",
    "tuer_1_anschlagrichtung"
  );
  dependenciesValuesXcontainer(
    "tueren",
    (value) =>
      value === "2x" ||
      value === "3x" ||
      value === "4x" ||
      value === "5x" ||
      value === "6x",
    "tuer_2_anschlagrichtung"
  );
  dependenciesValuesXcontainer(
    "tueren",
    (value) =>
      value === "3x" || value === "4x" || value === "5x" || value === "6x",
    "tuer_3_anschlagrichtung"
  );
  dependenciesValuesXcontainer(
    "tueren",
    (value) => value === "4x" || value === "5x" || value === "6x",
    "tuer_4_anschlagrichtung"
  );
  dependenciesValuesXcontainer(
    "tueren",
    (value) => value === "5x" || value === "6x",
    "tuer_5_anschlagrichtung"
  );
  dependenciesValuesXcontainer(
    "tueren",
    (value) => value === "6x",
    "tuer_6_anschlagrichtung"
  );

  // // Allgemeine Fälle für "2x" bis "6x" mittels Schleifen
  // for (let i = 2; i <= 6; i++) { // Äußere Schleife für 2x, 3x, 4x, 5x, 6x
  //     const currentValue = `${i}x`; // z.B. "2x", "3x", ...
  //     for (let j = 1; j <= i; j++) { // Innere Schleife für die Suffixe _1, _2, ..., _i
  //         dependenciesValuesXcontainer(
  //             "tueren",
  //             value => value === currentValue,
  //             `tuer_${j}_anschlagrichtung`,
  //             true
  //         );
  //     }
  // }
});
