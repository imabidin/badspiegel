/**
 * @version 2.5.0
 *
 * @note Seems to be final, no further changes if not needed
 */

import { dependenciesValuesXvalues, dependenciesValuesXcontainer } from "./../dependencies.js";

document.addEventListener("DOMContentLoaded", () => {
  /**
   * Values X Values
   */
  dependenciesValuesXvalues(
    "steckdose",
    value => {
      return value === "2x";
    },
    "steckdose_position",
    value => {
      return value === "links_und_rechts";
    }
  );
  /**
   * Values X Container
   */
  dependenciesValuesXcontainer(
    "steckdose",
    value => {
      return value !== "";
    },
    "steckdose_farbe"
  );
  dependenciesValuesXcontainer(
    "steckdose",
    value => {
      return value !== "";
    },
    "steckdose_position"
  );
});
