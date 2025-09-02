/**
 * @version 2.2.0
 *
 * TODO: Check functionality on rounded mirrors.
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
    "tv_seitenanschluss",
    (value) => {
      return value !== "";
    },
    "tv_seitenanschluss_position"
  );
});
