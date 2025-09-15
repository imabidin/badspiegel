/**
 * @version 2.6.0
 */

import { isNotLED } from '../variables.js';

document.addEventListener('DOMContentLoaded', function () {
    // Configuration
    const POWER_REQUIRING_OPTIONS = [
        'steckdose',
        'spiegelheizung',
        'schminkspiegel_lichtfarbe',
        'digital_uhr',
        'lautsprecher'
    ];

    const stromanschlussOptionGroup = document.getElementById('option_stromanschluss');

    if (!stromanschlussOptionGroup) return;

    /**
     * Reset Stromanschluss selection to default (no selection)
     */
    function resetStromanschluss() {
        const noneRadio = stromanschlussOptionGroup.querySelector('.option-none[type="radio"]');
        if (noneRadio) {
            noneRadio.checked = true;
            noneRadio.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    /**
     * Show Stromanschluss option group
     */
    function showStromanschluss() {
        stromanschlussOptionGroup.classList.remove('d-none');
    }

    /**
     * Hide Stromanschluss option group and reset selection
     */
    function hideStromanschluss() {
        stromanschlussOptionGroup.classList.add('d-none');
        resetStromanschluss();
    }

    /**
     * Check if any power-requiring option has a value selected
     */
    function hasPowerRequiringSelection() {
        return POWER_REQUIRING_OPTIONS.some(optionId => {
            const optionGroup = document.getElementById(`option_${optionId}`);
            if (!optionGroup) return false;

            // Check radio buttons
            const selectedRadio = optionGroup.querySelector('input[type="radio"]:checked');
            if (selectedRadio && selectedRadio.value !== '') return true;

            // Check checkboxes
            const selectedCheckbox = optionGroup.querySelector('input[type="checkbox"]:checked');
            if (selectedCheckbox && selectedCheckbox.value !== '') return true;

            // Check number/text inputs
            const numberInput = optionGroup.querySelector('input[type="number"], input[type="text"]');
            if (numberInput && numberInput.value && numberInput.value !== '') return true;

            return false;
        });
    }

    /**
     * Update Stromanschluss visibility based on current selections
     */
    function updateStromanschlussVisibility() {
        if (isNotLED) {
            if (hasPowerRequiringSelection()) {
                showStromanschluss();
            } else {
                hideStromanschluss();
            }
        }
    }

    // Initial setup - hide if no LED and no power-requiring selections
    if (isNotLED) {
        if (!hasPowerRequiringSelection()) {
            hideStromanschluss();
        }
    }

    // Add event listeners to all power-requiring options
    POWER_REQUIRING_OPTIONS.forEach(optionId => {
        const optionGroup = document.getElementById(`option_${optionId}`);
        if (optionGroup) {
            // Listen for changes on all input types within the option group
            optionGroup.addEventListener('change', updateStromanschlussVisibility);
            optionGroup.addEventListener('input', updateStromanschlussVisibility);
        }
    });
});

