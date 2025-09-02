/**
 * @version 2.2.0
 */

import {
  dependenciesValuesXvalues,
  dependenciesValuesXcontainer,
} from "./../dependencies.js";

import {} from "./../variables.js";

document.addEventListener("DOMContentLoaded", () => {
  //*********************************/
  // Dependencies: Values X Container
  //*********************************/
  if (!isRund) {
    dependenciesValuesXcontainer(
      "verblendung",
      (value) => {
        return value !== "";
      },
      "verblendung_ausfuehrung"
    );
    dependenciesValuesXcontainer(
      "verblendung",
      (value) => {
        return value === "seitenverblendung";
      },
      "verblendung_ausfuehrung_seiten"
    );
    dependenciesValuesXcontainer(
      "verblendung",
      (value) => {
        return value === "vollverblendung";
      },
      "verblendung_ausfuehrung_voll"
    );
  }
});
