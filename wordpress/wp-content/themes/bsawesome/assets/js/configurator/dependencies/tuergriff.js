/**
 * @version 2.3.0
 */

import { dependenciesValuesXvalues, dependenciesValuesXcontainer } from "./../dependencies.js";

import {} from "./../variables.js";

document.addEventListener("DOMContentLoaded", () => {
  /**
   * Values X Container
   */
  dependenciesValuesXcontainer(
    "tuergriff",
    value => {
      return value === "tuerueberstand";
    },
    "tuerueberstand_position"
  );
  dependenciesValuesXcontainer(
    "tuerueberstand_position",
    value => {
      return value.includes("oben");
    },
    "tuerueberstand_oben_in_mm"
  );
  dependenciesValuesXcontainer(
    "tuerueberstand_position",
    value => {
      return value.includes("unten");
    },
    "tuerueberstand_unten_in_mm"
  );
});
