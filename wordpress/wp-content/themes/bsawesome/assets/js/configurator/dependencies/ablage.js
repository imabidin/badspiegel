/**
 * @version 2.6.0
 *
 * @note Seems to be final, no further changes if not needed
 */

import { dependenciesValuesXcontainer } from "./../dependencies.js";

document.addEventListener("DOMContentLoaded", () => {
  /**
   * Values X Container
   */
  dependenciesValuesXcontainer(
    "ablage",
    value => {
      return value !== "";
    },
    "ablage_breite"
  );
  dependenciesValuesXcontainer(
    "ablage",
    value => {
      return value === "farbiges_glas";
    },
    "ablage_farbe"
  );
  dependenciesValuesXcontainer(
    "ablage",
    value => {
      return value === "getoentes_glas";
    },
    "ablage_toenung"
  );
  dependenciesValuesXcontainer(
    "ablage_farbe",
    value => {
      return value === "individuell";
    },
    "ablage_farbton"
  );
});
