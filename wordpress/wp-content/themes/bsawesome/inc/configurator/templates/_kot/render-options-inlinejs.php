<script>
    document.addEventListener('DOMContentLoaded', function() {
        const optionGroups = document.querySelectorAll('.option-group');
        optionGroups.forEach(group => {
            const options = group.querySelectorAll('.option-item');
            options.forEach(opt => {
                opt.addEventListener('click', () => {
                    // Aktive Klasse von allen entfernen
                    options.forEach(o => o.classList.remove('active'));
                    opt.classList.add('active');

                    const value = opt.getAttribute('data-value');

                    // Den übergeordneten Container suchen
                    const container = group.closest('.product-configurator-option');
                    if (!container) return;

                    // Passenden Radio für diesen Wert suchen
                    const radio = container.querySelector('input[type="radio"][value="' + value + '"]');
                    if (radio) {
                        radio.checked = true;
                    }
                });
            });
        });
    });
</script>