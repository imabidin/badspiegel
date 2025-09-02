/**
 * Updated 22/04/2025
 */

// Optimized click delegation for dynamically loaded note items
document.addEventListener("click", function (event) {
  // Only process primary mouse button clicks
  if (event.button !== 0) return;

  // Find the closest enabled note item ancestor
  const noteItem = event.target.closest(".wcpf-note-item:not(.disabled)");
  if (!noteItem) return; // Click outside note items

  // Skip if click originated from or inside remove button
  if (event.target.closest(".remove-filter")) return;

  // Find and trigger the remove button if it exists
  noteItem.querySelector(".remove-filter")?.click();
});
