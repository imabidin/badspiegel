/**
 * Navigation Dropdown System for Bootstrap 5
 *
 * Manages responsive dropdown behavior within SimpleBar scrollable containers.
 * Automatically handles positioning of dropdown menus to prevent clipping
 * when dropdowns are inside scrollable containers.
 *
 * Features:
 * - Responsive dropdown behavior (desktop only ≥768px)
 * - Dynamic positioning for SimpleBar containers
 * - Automatic menu repositioning to prevent clipping
 * - Proper cleanup and restoration of original DOM structure
 * - Media query responsive activation/deactivation
 * - Bootstrap 5 dropdown event integration
 *
 * @version 2.3.0
 * @package BSAwesome Navigation
 * @requires Bootstrap 5, SimpleBar (optional)
 *
 * Technical Implementation:
 * - Uses Bootstrap dropdown events (show.bs.dropdown, hide.bs.dropdown)
 * - Temporarily moves dropdown menus to SimpleBar container for positioning
 * - Calculates absolute positioning based on trigger element coordinates
 * - Restores original DOM structure when dropdown closes
 *
 * Usage:
 * Works automatically with any Bootstrap dropdown inside a SimpleBar container.
 * No manual initialization required - responds to window resize automatically.
 *
 * @example
 * <div data-simplebar>
 *   <div class="dropdown">
 *     <button class="dropdown-toggle" data-bs-toggle="dropdown">Menu</button>
 *     <ul class="dropdown-menu">
 *       <li><a href="#">Item 1</a></li>
 *     </ul>
 *   </div>
 * </div>
 */

document.addEventListener("DOMContentLoaded", function () {
  // Media query for desktop breakpoint (Bootstrap md: ≥768px)
  const mdQuery = window.matchMedia("(min-width: 768px)");
  let desktopActive = false;

  // =============================================================================
  // EVENT HANDLERS
  // =============================================================================

  /**
   * Handler for Bootstrap dropdown show event
   * Repositions dropdown menu within SimpleBar container to prevent clipping
   *
   * @param {Event} e - Bootstrap dropdown show event
   * @description
   * 1. Finds the dropdown trigger and corresponding menu
   * 2. Locates the SimpleBar container (if present)
   * 3. Temporarily moves dropdown menu to SimpleBar container
   * 4. Calculates absolute positioning based on trigger coordinates
   * 5. Stores reference to original parent for restoration
   */
  function showDropdownHandler(e) {
    const trigger = e.target;
    const dropdown = trigger.closest(".dropdown, .menu-item-has-children");
    if (!dropdown) return;
    
    const dropdownMenu = dropdown.querySelector(".dropdown-menu");
    if (!dropdownMenu) return;

    // Find SimpleBar container (scrollable parent)
    const simplebar = dropdown.closest("[data-simplebar]");
    if (!simplebar) return;

    // Calculate positioning relative to SimpleBar container
    const triggerRect = trigger.getBoundingClientRect();
    const simplebarRect = simplebar.getBoundingClientRect();

    // Temporarily move dropdown to SimpleBar container for proper positioning
    simplebar.appendChild(dropdownMenu);

    // Apply absolute positioning to prevent clipping
    dropdownMenu.style.position = "absolute";
    dropdownMenu.style.left = `${
      triggerRect.left - simplebarRect.left + simplebar.scrollLeft
    }px`;
    dropdownMenu.style.top = `${
      triggerRect.bottom - simplebarRect.top + simplebar.scrollTop
    }px`;
    dropdownMenu.style.zIndex = 9999;
    dropdownMenu.style.display = "block";

    // Store original parent for restoration when hiding
    dropdownMenu._originalParent = dropdown;
  }

  /**
   * Handler for Bootstrap dropdown hide event
   * Restores dropdown menu to its original position in the DOM
   *
   * @param {Event} e - Bootstrap dropdown hide event
   * @description
   * 1. Finds the dropdown trigger and temporarily positioned menu
   * 2. Restores dropdown menu to its original parent container
   * 3. Removes all positioning styles applied during show
   * 4. Cleans up temporary references
   */
  function hideDropdownHandler(e) {
    const trigger = e.target;
    const dropdown = trigger.closest(".dropdown, .menu-item-has-children");
    if (!dropdown) return;
    
    // Find the temporarily positioned dropdown menu
    const dropdownMenu = document.querySelector(
      '.dropdown-menu[style*="position: absolute"]'
    );
    if (!dropdownMenu) return;

    // Restore to original parent if reference exists, otherwise use current dropdown
    if (dropdownMenu._originalParent) {
      dropdownMenu._originalParent.appendChild(dropdownMenu);
      delete dropdownMenu._originalParent;
    } else {
      dropdown.appendChild(dropdownMenu);
    }
    
    // Remove all positioning styles to restore normal behavior
    dropdownMenu.style.position = "";
    dropdownMenu.style.left = "";
    dropdownMenu.style.top = "";
    dropdownMenu.style.zIndex = "";
    dropdownMenu.style.display = "";
  }

  // =============================================================================
  // RESPONSIVE CONTROL FUNCTIONS
  // =============================================================================

  /**
   * Enables desktop dropdown functionality
   * Attaches event listeners for Bootstrap dropdown events
   * Only activates if not already active to prevent duplicate listeners
   */
  function enableDesktopDropdown() {
    if (desktopActive) return;
    
    // Attach Bootstrap dropdown event listeners
    document.addEventListener("show.bs.dropdown", showDropdownHandler);
    document.addEventListener("hide.bs.dropdown", hideDropdownHandler);
    desktopActive = true;
  }

  /**
   * Disables desktop dropdown functionality
   * Removes event listeners for Bootstrap dropdown events
   * Only deactivates if currently active to prevent unnecessary operations
   */
  function disableDesktopDropdown() {
    if (!desktopActive) return;
    
    // Remove Bootstrap dropdown event listeners
    document.removeEventListener("show.bs.dropdown", showDropdownHandler);
    document.removeEventListener("hide.bs.dropdown", hideDropdownHandler);
    desktopActive = false;
  }

  // =============================================================================
  // MEDIA QUERY MANAGEMENT
  // =============================================================================

  /**
   * Checks current viewport size and enables/disables functionality accordingly
   * Responds to both initial load and viewport size changes
   *
   * @param {MediaQueryListEvent|MediaQueryList} e - Media query event or list
   * @description
   * - Enables desktop dropdown behavior for viewports ≥768px (Bootstrap md+)
   * - Disables functionality for smaller viewports (mobile behavior)
   * - Called both on initial load and when viewport size changes
   */
  function checkBreakpoint(e) {
    if (mdQuery.matches) {
      // Desktop: Enable advanced dropdown positioning
      enableDesktopDropdown();
    } else {
      // Mobile: Use standard Bootstrap behavior
      disableDesktopDropdown();
    }
  }

  // =============================================================================
  // INITIALIZATION
  // =============================================================================

  // Listen for viewport size changes
  mdQuery.addEventListener("change", checkBreakpoint);
  
  // Initialize based on current viewport size
  checkBreakpoint();
});
