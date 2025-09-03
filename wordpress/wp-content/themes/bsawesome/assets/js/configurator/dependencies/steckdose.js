/**
 * @version 2.3.0
 */

import { dependenciesValuesXvalues, dependenciesValuesXcontainer } from "./../dependencies.js";

import {} from "./../variables.js";

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
