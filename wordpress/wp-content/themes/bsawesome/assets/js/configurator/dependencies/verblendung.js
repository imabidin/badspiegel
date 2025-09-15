/**
 * @version 2.6.0
 */

import { dependenciesValuesXcontainer } from "./../dependencies.js";

import { isRund } from "./../variables.js";

document.addEventListener("DOMContentLoaded", () => {
  /**
   * Values X Container
   */
  if (!isRund) {
    dependenciesValuesXcontainer(
      "verblendung",
      value => {
        return value !== "";
      },
      "verblendung_ausfuehrung"
    );
    dependenciesValuesXcontainer(
      "verblendung",
      value => {
        return value === "seitenverblendung";
      },
      "verblendung_ausfuehrung_seiten"
    );
    dependenciesValuesXcontainer(
      "verblendung",
      value => {
        return value === "vollverblendung";
      },
      "verblendung_ausfuehrung_voll"
    );
  }
});
