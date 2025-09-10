/**
 * @version 2.5.0
 *
 * @note Seems to be final, no further changes if not needed
 */

import { dependenciesValuesXcontainer } from "./../dependencies.js";

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
