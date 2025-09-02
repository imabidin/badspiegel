/**
 * @version 2.2.0
 */

import {
  dependenciesValuesXvalues,
  dependenciesValuesXcontainer,
} from "./../dependencies.js";

import {} from "./../variables.js";

document.addEventListener("DOMContentLoaded", () => {
  /**
   * Values X Values
   */
  dependenciesValuesXvalues(
    "schminkspiegel_lichtfarbe",
    (value) => {
      return (
        value === "warmweiss" ||
        value === "neutralweiss" ||
        value === "kaltweiss"
      );
    },
    "schminkspiegel_bedienung",
    (value) => {
      return (
        value === "ohne_extra_schalter" ||
        value === "extra_kippschalter" ||
        value === "extra_touch_sensor"
      );
    }
  );
  dependenciesValuesXvalues(
    "schminkspiegel_lichtfarbe",
    (value) => {
      return value === "alle_drei_lichtfarben";
    },
    "schminkspiegel_bedienung",
    (value) => {
      return value === "extra_touch_sensor";
    }
  );
  dependenciesValuesXvalues(
    "schminkspiegel_bedienung",
    (value) => {
      return !value.includes("touch_sensor");
    },
    "schminkspiegel_bedienung_position",
    (value) => {
      return value === "linke_seite" || value === "rechte_seite";
    }
  );
  /**
   * Values X Container
   */
  dependenciesValuesXcontainer(
    "schminkspiegel",
    (value) => {
      return value !== "";
    },
    "schminkspiegel_position"
  );
  dependenciesValuesXcontainer(
    "schminkspiegel",
    (value) => {
      return value === "beleuchtet";
    },
    "schminkspiegel_lichtfarbe"
  );
  dependenciesValuesXcontainer(
    "schminkspiegel",
    (value) => {
      return value === "beleuchtet";
    },
    "schminkspiegel_bedienung"
  );
  dependenciesValuesXcontainer(
    "schminkspiegel_bedienung",
    (value) => {
      return value !== "" && value !== "ohne_extra_schalter";
    },
    "schminkspiegel_bedienung_position"
  );
});
