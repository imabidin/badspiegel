/**
 * @version 2.5.0
 *
 * @todo Intensive testing in various scenarios
 * @todo Check different mirror shapes (e.g. round, oval)
 * @todo Check different product types
 */

import { dependenciesValuesXvalues, dependenciesValuesXcontainer } from "./../dependencies.js";

document.addEventListener("DOMContentLoaded", () => {
  /**
   * Values X Values
   */
  dependenciesValuesXvalues(
    "ambientelicht_lichtfarbe",
    value => {
      return value === "warmweiss" || value === "neutralweiss" || value === "kaltweiss";
    },
    "ambientelicht_bedienung",
    value => {
      return (
        value === "ohne_extra_schalter" ||
        value === "extra_schalter" ||
        value.includes("drehdimmer") ||
        value.includes("kippschalter") ||
        value.includes("gestensensor") ||
        value.includes("touch_sensor") ||
        value.includes("gestensteuerung")
      );
    }
  );
  dependenciesValuesXvalues(
    "ambientelicht_lichtfarbe",
    value => {
      return value.includes("warm_bis_kalt");
    },
    "ambientelicht_bedienung",
    value => {
      return value === "extra_gestensteuerung_plus" || value === "extra_fernbedienung";
    }
  );
  dependenciesValuesXvalues(
    "ambientelicht_lichtfarbe",
    value => {
      return value.includes("rgb");
    },
    "ambientelicht_bedienung",
    value => {
      return value === "extra_rgb_fernbedienung";
    }
  );
  dependenciesValuesXvalues(
    "ambientelicht_lichtfarbe",
    value => {
      return value.includes("hue");
    },
    "ambientelicht_bedienung",
    value => {
      return value === "philips_hue_app";
    }
  );
  dependenciesValuesXvalues(
    "ambientelicht_bedienung",
    value => {
      return !(value.includes("touch_sensor") || value.includes("gestensteuerung"));
    },
    "ambientelicht_bedienung_position",
    value => {
      return value === "linke_seite" || value === "rechte_seite";
    }
  );
  /**
   * Values X Container
   */
  dependenciesValuesXcontainer(
    "ambientelicht",
    value => {
      return value !== "";
    },
    "ambientelicht_lichtfarbe"
  );
  dependenciesValuesXcontainer(
    "ambientelicht_lichtfarbe",
    value => {
      return value !== "" && value !== "philips_hue";
    },
    "ambientelicht_bedienung"
  );
  dependenciesValuesXcontainer(
    "ambientelicht_bedienung",
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
    "ambientelicht_bedienung_position"
  );
  dependenciesValuesXcontainer(
    "ambientelicht_lichtfarbe",
    value => {
      return value.includes("hue");
    },
    "ambientelicht_philips_hue"
  );
});
