/**
 * Clipboard script for copying voucher codes to the clipboard
 */

// Wait until the DOM is fully loaded
$(function () {
  // 1) Initialize all buttons with data-copy="clipboard"
  //    (Tooltip is initially empty, we add the text on click)
  $('[data-copy="clipboard"]').tooltip({
    // Optional: tooltip settings. With "title: ''" they remain empty for now.
    title: "",
    placement: "top",
    trigger: "manual", // We control the display manually
  });

  // 2) Click handler for all buttons with data-copy="clipboard"
  $(document).on("click", '[data-copy="clipboard"]', function (event) {
    event.preventDefault();

    const $button = $(this);
    const voucherCode = $button.data("voucher");

    // Clipboard API check
    if (navigator.clipboard && voucherCode) {
      navigator.clipboard
        .writeText(voucherCode)
        .then(() => {
          // a) Change icon
          $button
            .find("i")
            .removeClass("fa-light fa-copy")
            .addClass("fa-light fa-check text-success");

          // b) Set tooltip to "Copied!" nur wenn data-bs-tooltip="true"
          if ($button.attr("data-bs-tooltip") === "true") {
            $button
              .tooltip("dispose") // remove old tooltip
              .tooltip({ title: "Kopiert!" }) // new tooltip with new text
              .tooltip("show"); // show tooltip immediately
          }
        })
        .catch((err) => {
          console.error("Error copying: ", err);
        });
    }
  });

  // 3) Click outside -> Reset everything
  $(document).on("click", function (event) {
    // If NOT clicked on a button with data-copy="clipboard" ...
    if (!$(event.target).closest('[data-copy="clipboard"]').length) {
      const $buttons = $('[data-copy="clipboard"]');

      // a) Reset icon to "fa-copy"
      $buttons
        .find("i")
        .removeClass("fa-light fa-check text-success")
        .addClass("fa-light fa-copy");

      // b) Empty tooltip again
      $buttons.each(function () {
        const $btn = $(this);
        if ($btn.attr("data-bs-tooltip") === "true") {
          $btn.tooltip("dispose").tooltip({ title: "" });
        }
      });
    }
  });
});
