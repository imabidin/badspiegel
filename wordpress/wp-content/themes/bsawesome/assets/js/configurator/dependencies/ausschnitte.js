/**
 * @version 2.2.0
 */

import {
  dependenciesValuesXvalues,
  dependenciesValuesXcontainer,
} from "./../dependencies.js";

import {} from "../variables.js";

document.addEventListener("DOMContentLoaded", () => {
  dependenciesValuesXcontainer(
    "ausschnitte",
    (value) => {
      return value === "1x";
    },
    "ausschnitt_durchmesser"
  );
  dependenciesValuesXcontainer(
    "ausschnitte",
    (value) => {
      return value === "1x";
    },
    "ausschnitt_abstand_links"
  );
  dependenciesValuesXcontainer(
    "ausschnitte",
    (value) => {
      return value === "1x";
    },
    "ausschnitt_abstand_rechts"
  );
  dependenciesValuesXcontainer(
    "ausschnitte",
    (value) => {
      return value === "2x";
    },
    "ausschnitt_1_durchmesser"
  );
  dependenciesValuesXcontainer(
    "ausschnitte",
    (value) => {
      return value === "2x";
    },
    "ausschnitt_1_abstand_links"
  );
  dependenciesValuesXcontainer(
    "ausschnitte",
    (value) => {
      return value === "2x";
    },
    "ausschnitt_1_abstand_rechts"
  );
  dependenciesValuesXcontainer(
    "ausschnitte",
    (value) => {
      return value === "2x";
    },
    "ausschnitt_2_durchmesser"
  );
  dependenciesValuesXcontainer(
    "ausschnitte",
    (value) => {
      return value === "2x";
    },
    "ausschnitt_2_abstand_links"
  );
  dependenciesValuesXcontainer(
    "ausschnitte",
    (value) => {
      return value === "2x";
    },
    "ausschnitt_2_abstand_rechts"
  );
});
