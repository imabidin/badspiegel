document.addEventListener("DOMContentLoaded", function () {
  const mdQuery = window.matchMedia("(min-width: 768px)");
  let desktopActive = false;

  // Handler-Funktionen müssen referenzierbar sein, damit sie entfernt werden können
  function showDropdownHandler(e) {
    const trigger = e.target;
    const dropdown = trigger.closest(".dropdown, .menu-item-has-children");
    if (!dropdown) return;
    const dropdownMenu = dropdown.querySelector(".dropdown-menu");
    if (!dropdownMenu) return;

    const simplebar = dropdown.closest("[data-simplebar]");
    if (!simplebar) return;

    const triggerRect = trigger.getBoundingClientRect();
    const simplebarRect = simplebar.getBoundingClientRect();

    simplebar.appendChild(dropdownMenu);

    dropdownMenu.style.position = "absolute";
    dropdownMenu.style.left = `${
      triggerRect.left - simplebarRect.left + simplebar.scrollLeft
    }px`;
    dropdownMenu.style.top = `${
      triggerRect.bottom - simplebarRect.top + simplebar.scrollTop
    }px`;
    dropdownMenu.style.zIndex = 9999;
    dropdownMenu.style.display = "block";

    dropdownMenu._originalParent = dropdown;
  }

  function hideDropdownHandler(e) {
    const trigger = e.target;
    const dropdown = trigger.closest(".dropdown, .menu-item-has-children");
    if (!dropdown) return;
    const dropdownMenu = document.querySelector(
      '.dropdown-menu[style*="position: absolute"]'
    );
    if (!dropdownMenu) return;

    if (dropdownMenu._originalParent) {
      dropdownMenu._originalParent.appendChild(dropdownMenu);
      delete dropdownMenu._originalParent;
    } else {
      dropdown.appendChild(dropdownMenu);
    }
    dropdownMenu.style.position = "";
    dropdownMenu.style.left = "";
    dropdownMenu.style.top = "";
    dropdownMenu.style.zIndex = "";
    dropdownMenu.style.display = "";
  }

  function enableDesktopDropdown() {
    if (desktopActive) return;
    document.addEventListener("show.bs.dropdown", showDropdownHandler);
    document.addEventListener("hide.bs.dropdown", hideDropdownHandler);
    desktopActive = true;
  }

  function disableDesktopDropdown() {
    if (!desktopActive) return;
    document.removeEventListener("show.bs.dropdown", showDropdownHandler);
    document.removeEventListener("hide.bs.dropdown", hideDropdownHandler);
    desktopActive = false;
  }

  // Initial prüfen und bei Änderung reagieren
  function checkBreakpoint(e) {
    if (mdQuery.matches) {
      enableDesktopDropdown();
    } else {
      disableDesktopDropdown();
    }
  }

  mdQuery.addEventListener("change", checkBreakpoint);
  checkBreakpoint();
});
