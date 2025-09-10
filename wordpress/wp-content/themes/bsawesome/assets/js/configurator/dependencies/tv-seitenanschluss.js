/**
 * @version 2.5.0
 *
 * @todo Check functionality on rounded mirrors
 */

import { dependenciesValuesXcontainer } from "./../dependencies.js";

document.addEventListener("DOMContentLoaded", () => {
  /**
   * Values X Container
   */
  dependenciesValuesXcontainer(
    "tv_seitenanschluss",
    value => {
      return value !== "";
    },
    "tv_seitenanschluss_position"
  );
});
