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
    "ablagen",
    value => {
      return value !== "";
    },
    "ablagen_typ"
  );
  dependenciesValuesXcontainer(
    "ablagen_typ",
    value => {
      return value === "farbiges_glas";
    },
    "ablagen_farbe"
  );
  dependenciesValuesXcontainer(
    "ablagen_typ",
    value => {
      return value === "getoentes_glas";
    },
    "ablagen_toenung"
  );
  dependenciesValuesXcontainer(
    "ablagen_farbe",
    value => {
      return value === "individuell";
    },
    "ablagen_farbton"
  );
});
