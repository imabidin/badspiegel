/**
 * @version 2.2.0
 */

import {
  dependenciesValuesXvalues,
  dependenciesValuesXcontainer,
} from "./../dependencies.js";

import {} from "../variables.js";

document.addEventListener("DOMContentLoaded", () => {
  /**
   * Values X Container
   */
  dependenciesValuesXcontainer(
    "auflage",
    (value) => {
      return value === "farbiges_glas";
    },
    "auflage_farbe"
  );
  dependenciesValuesXcontainer(
    "auflage",
    (value) => {
      return value === "getoentes_glas";
    },
    "auflage_toenung"
  );
  dependenciesValuesXcontainer(
    "auflage",
    (value) => {
      return value === "keramik";
    },
    "auflage_keramik"
  );
  dependenciesValuesXcontainer(
    "auflage_farbe",
    (value) => {
      return value === "individuell";
    },
    "auflage_farbton"
  );
});
