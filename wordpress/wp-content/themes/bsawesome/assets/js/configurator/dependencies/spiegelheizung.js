/**
 * @version 2.6.0
 *
 * @note Seems to be final, no further changes if not needed
 */

import { dependenciesValuesXvalues, dependenciesValuesXcontainer } from "./../dependencies.js";

document.addEventListener("DOMContentLoaded", () => {
  /**
   * Values X Values
   */
  dependenciesValuesXvalues(
    "spiegelheizung_bedienung",
    value => {
      return !value.includes("touch_sensor");
    },
    "spiegelheizung_bedienung_position",
    value => {
      return value === "linke_seite" || value === "rechte_seite";
    }
  );
  /**
   * Values X Container
   */
  dependenciesValuesXcontainer(
    "spiegelheizung",
    value => {
      return value === "ja";
    },
    "spiegelheizung_bedienung"
  );
  dependenciesValuesXcontainer(
    "spiegelheizung_bedienung",
    value => {
      return value !== "" && value !== "ohne_extra_schalter";
    },
    "spiegelheizung_bedienung_position"
  );
});
