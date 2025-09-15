/**
 * @version 2.6.0
 *
 * @note Seems to be final, no further changes if not needed
 */

import { dependenciesValuesXcontainer } from "./../dependencies.js";

document.addEventListener("DOMContentLoaded", () => {
  /**
   * Values X Container
   */
  dependenciesValuesXcontainer(
    "ausschnitte",
    value => {
      return value === "1x";
    },
    "ausschnitt_durchmesser"
  );
  dependenciesValuesXcontainer(
    "ausschnitte",
    value => {
      return value === "1x";
    },
    "ausschnitt_abstand_links"
  );
  dependenciesValuesXcontainer(
    "ausschnitte",
    value => {
      return value === "1x";
    },
    "ausschnitt_abstand_rechts"
  );
  dependenciesValuesXcontainer(
    "ausschnitte",
    value => {
      return value === "2x";
    },
    "ausschnitt_1_durchmesser"
  );
  dependenciesValuesXcontainer(
    "ausschnitte",
    value => {
      return value === "2x";
    },
    "ausschnitt_1_abstand_links"
  );
  dependenciesValuesXcontainer(
    "ausschnitte",
    value => {
      return value === "2x";
    },
    "ausschnitt_1_abstand_rechts"
  );
  dependenciesValuesXcontainer(
    "ausschnitte",
    value => {
      return value === "2x";
    },
    "ausschnitt_2_durchmesser"
  );
  dependenciesValuesXcontainer(
    "ausschnitte",
    value => {
      return value === "2x";
    },
    "ausschnitt_2_abstand_links"
  );
  dependenciesValuesXcontainer(
    "ausschnitte",
    value => {
      return value === "2x";
    },
    "ausschnitt_2_abstand_rechts"
  );
});
