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
    "anschluesse",
    value => {
      return (
        value === "1x_1x_steckdosen" ||
        value === "2x_2x_steckdosen" ||
        value === "1x_usb_1x_steckdose" ||
        value === "1x_usb_2x_steckdosen"
      );
    },
    "anschluesse_position",
    value => {
      return value === "links_und_rechts";
    }
  );
  /**
   * Values X Container
   */
  dependenciesValuesXcontainer(
    "anschluesse",
    value => {
      return value !== "";
    },
    "anschluesse_position"
  );
});
