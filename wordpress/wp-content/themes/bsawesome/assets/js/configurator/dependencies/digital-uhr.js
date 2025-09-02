/**
 * @version 2.2.0
 */

import {
  dependenciesValuesXvalues,
  dependenciesValuesXcontainer,
} from "./../dependencies.js";

import { isRund, isLOR, isLR, isOU } from "./../variables.js";

document.addEventListener("DOMContentLoaded", () => {
  /**
   * Values X Container
   */
  dependenciesValuesXcontainer(
    "digital_uhr",
    (value) => {
      return value === "ja";
    },
    "digital_uhr_position"
  );
});
