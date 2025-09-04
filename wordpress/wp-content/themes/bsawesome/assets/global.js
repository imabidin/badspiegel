// global.js

// PRODUCTION MODE: Debug logging disabled
if (typeof window !== 'undefined') {
  window.addEventListener('DOMContentLoaded', function() {
    // Check if myAjaxData is loaded (silent in production)
    if (typeof myAjaxData === 'undefined') {
      // AJAX functionality may fail - debug in development only
      return;
    }

    // PRODUCTION: AJAX monitoring disabled for performance
    // Debug code removed for production deployment
  });
}

// Import Scss
import "./scss/style.scss";

// Import Js
import "./js/simplebar";
import "./js/tooltip";
import "./js/modal";
import "./js/countdown";
import "./js/clipboard";
import "./js/favourites";
import "./js/navigation";
import "./js/filter";
import "./js/loop";

import "./js/configurator/configcode/load";
