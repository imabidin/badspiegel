/**
 * Marketing countdown timer
 * 
 * @version Final
 * 
  <div class="countdown"
    data-countdown-date="2025-12-31"
    data-countdown-time="23:59:59"
    data-countdown-timezone="Europe/Berlin">
        <span class="days"></span>
        <span class="hours"></span>
        <span class="minutes"></span>
        <span class="seconds"></span>
  </div>
 */

/**
 * Initializes all countdowns on the page
 */
function initCountdowns() {
  document
    .querySelectorAll("[data-countdown-date]")
    .forEach((countdownElement) => {
      try {
        startCountdown(countdownElement);
      } catch (error) {
        console.error("Countdown initialization failed:", error);
        displayError(countdownElement);
      }
    });
}

/**
 * Starts a single countdown
 * @param {HTMLElement} countdownElement - The countdown container
 */
function startCountdown(countdownElement) {
  // Validate required elements
  const daysElement = countdownElement.querySelector(".days");
  const hoursElement = countdownElement.querySelector(".hours");
  const minutesElement = countdownElement.querySelector(".minutes");
  const secondsElement = countdownElement.querySelector(".seconds");

  if (!daysElement || !hoursElement || !minutesElement || !secondsElement) {
    throw new Error("Missing countdown elements");
  }

  // Get configuration from data attributes
  const targetDate = countdownElement.dataset.countdownDate;
  const targetTime = countdownElement.dataset.countdownTime || "23:59:59";
  const timeZone = countdownElement.dataset.countdownTimezone || null;

  // Calculate target timestamp (with timezone support)
  let targetDateTime;
  try {
    if (timeZone) {
      const timeZoneDate = new Date(
        `${targetDate}T${targetTime}`
      ).toLocaleString("en-US", { timeZone });
      targetDateTime = new Date(timeZoneDate).getTime();
    } else {
      targetDateTime = new Date(`${targetDate} ${targetTime}`).getTime();
    }

    if (isNaN(targetDateTime)) throw new Error("Invalid date format");
  } catch (e) {
    throw new Error(`Invalid date configuration: ${e.message}`);
  }

  // Initial render before starting interval
  updateCountdown();
  countdownElement.classList.add("show");

  // Start timer
  const interval = setInterval(updateCountdown, 1000);

  /**
   * Updates the countdown display
   */
  function updateCountdown() {
    const now = new Date().getTime();
    const distance = targetDateTime - now;

    // Calculate time units
    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor(
      (distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)
    );
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

    if (distance < 0) {
      clearInterval(interval);
      countdownElement.innerHTML =
        '<span class="text-danger">Aktion abgelaufen!</span>';
      return;
    }

    // Update display with auto-hiding logic
    daysElement.classList.toggle("hidden", days <= 0);
    daysElement.textContent = `${days.toString().padStart(2, "0")} ${
      days === 1 ? "Tag : " : "Tage : "
    }`;

    hoursElement.classList.toggle("hidden", days <= 0 && hours <= 0);
    hoursElement.textContent = `${hours.toString().padStart(2, "0")}h : `;

    minutesElement.classList.toggle(
      "hidden",
      days <= 0 && hours <= 0 && minutes <= 0
    );
    minutesElement.textContent = `${minutes.toString().padStart(2, "0")}m : `;

    // Seconds always visible
    secondsElement.textContent = `${seconds.toString().padStart(2, "0")}s`;
  }
}

/**
 * Displays an error state
 * @param {HTMLElement} element - The countdown container
 */
function displayError(element) {
  element.innerHTML = '<span class="error">Countdown nicht verfügbar</span>';
  element.classList.add("show");
}

// Initialize when DOM is ready
if (document.readyState !== "loading") {
  initCountdowns();
} else {
  document.addEventListener("DOMContentLoaded", initCountdowns);
}
