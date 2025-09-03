/**
 * @version 2.3.0
 */

import { dependenciesValuesXvalues, dependenciesValuesXcontainer } from "./../dependencies.js";

import {} from "./../variables.js";

document.addEventListener("DOMContentLoaded", () => {
  /**
   * Values X Values
   */
  dependenciesValuesXvalues(
    "lichtfarbe",
    value => {
      return value === "warmweiss" || value === "neutralweiss" || value === "kaltweiss";
    },
    "bedienung",
    value => {
      return (
        value === "ueber_eigenen_wandschalter" ||
        value === "schalter" ||
        value === "drehdimmer" ||
        value === "kippschalter" ||
        value === "gestensensor" ||
        value === "touch_sensor" ||
        value === "touch_sensor_inkl_dimmfunktion" ||
        value === "gestensteuerung_inkl_dimmfunktion"
      );
    }
  );
  dependenciesValuesXvalues(
    "lichtfarbe",
    value => {
      return value.includes("warm_und_kalt");
    },
    "bedienung",
    value => {
      return value.includes("doppel");
    }
  );
  dependenciesValuesXvalues(
    "lichtfarbe",
    value => {
      return value.includes("warm_bis_kalt");
    },
    "bedienung",
    value => {
      return value === "gestensteuerung_plus" || value === "fernbedienung";
    }
  );
  dependenciesValuesXvalues(
    "lichtfarbe",
    value => {
      return value.includes("rgb");
    },
    "bedienung",
    value => {
      return value === "rgb_fernbedienung";
    }
  );
  // dependenciesValuesXvalues(
  //     "bedienung",
  //     value => {
  //         return !(
  //             value.includes("touch_sensor") ||
  //             value.includes("gestensteuerung")
  //         );
  //     },
  //     "bedienung_position",
  //     value => {
  //         return (
  //             value === "linke_seite" ||
  //             value === "rechte_seite");
  //     }
  // );
  /**
   * Values X Container
   */
  // dependenciesValuesXcontainer(
  //     'lichtfarbe', value => {
  //         return (
  //             value === "warmweiss" ||
  //             value === "neutralweiss" ||
  //             value === "kaltweiss"
  //         );
  //     },
  //     'lichtstaerke'
  // );
});
