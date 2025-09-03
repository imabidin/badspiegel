/**
 * @version 2.3.0
 */

import { dependenciesValuesXvalues, dependenciesValuesXcontainer } from "./../dependencies.js";

import { isRund, isLOR, isLR, isOU } from "./../variables.js";

document.addEventListener("DOMContentLoaded", () => {
  /**
   * Values X Values
   */
  dependenciesValuesXvalues(
    "bedienung",
    value => {
      return !(value.includes("touch_sensor") || value.includes("gestensteuerung"));
    },
    "bedienung_position",
    value => {
      return value === "linke_seite" || value === "rechte_seite";
    }
  );
  //
  //  isLOR
  //
  if (isLOR || isLR) {
    dependenciesValuesXvalues(
      "bedienung",
      value => {
        return value.includes("kippschalter") || value.includes("gestensensor");
      },
      "bedienung_position",
      value => {
        return value.includes("linke_seite") || value.includes("rechte_seite");
      }
    );
  }
  //
  //  isOU
  //
  if (isOU) {
    dependenciesValuesXvalues(
      "bedienung",
      value => {
        return value.includes("kippschalter") || value.includes("gestensensor");
      },
      "bedienung_position",
      value => {
        return value.includes("unten-mittig") || value.includes("unten-links") || value.includes("unten-rechts");
      }
    );
  }
  //
  //  isRund
  //
  if (isRund) {
    dependenciesValuesXvalues(
      "bedienung",
      value => {
        return value.includes("touch_sensor") || value.includes("gestensteuerung");
      },
      "bedienung_position",
      value => {
        return (
          value.includes("rechts_3_uhr") || value.includes("links_9_uhr")
          // || value.includes('oben_12_uhr')
        );
      }
    );
  }
  /**
   * Values X Container
   */
  dependenciesValuesXcontainer(
    "bedienung",
    value => {
      return (
        value.includes("kippschalter") ||
        value.includes("gestensensor") ||
        value.includes("touch_sensor") ||
        value.includes("gestensteuerung") ||
        value === "schalter" ||
        value === "drehdimmer"
      );
    },
    "bedienung_position"
  );
});
