<?php defined('ABSPATH') || exit; ?>

<?php
/**
 * Off-Canvas Dropdown Option Template
 *
 * Renders a sophisticated dropdown/off-canvas selection interface for options
 * with many choices. On desktop, shows as a dropdown menu; on mobile, displays
 * as a full-screen off-canvas panel. Includes search functionality for large
 * option lists and supports images for visual option selection.
 *
 * Features:
 * - Responsive design: dropdown on desktop, off-canvas on mobile
 * - Search functionality for large option lists (>15 options)
 * - Image support for visual option selection
 * - Auto-loading of saved configuration values
 * - Required field handling with default selection
 * - Optional "none" selection for non-required fields
 * - Price display for options with additional costs
 * - Accessibility support with proper ARIA labels
 *
 * Template Variables Available:
 * - $option_type: Offdrops type (offdrops or offdrops-child)
 * - $option_key: Sanitized option identifier
 * - $option_label: Display label for the option
 * - $option_order: Numeric order for sorting
 * - $option_group: Group identifier
 * - $option_name: Form field name attribute
 * - $option_required: Boolean indicating if field is required
 * - $option_values: Array of selectable options with labels, prices, images
 * - $option_placeholder: Placeholder text for no selection
 * - $option_placeholder_image: Image for placeholder option
 * - $option_description: Description text for help
 * - $posted_value: Current/submitted value for auto-loading
 * - $modal_link: Link for description modal
 *
 * @version 2.6.0
 * @package configurator
 *
 * @todo Imrove description icons, image zoom icons, etc.
 * @todo Imrove Offcanvas header
 */

/**
 * Generate Unique IDs for Components
 * Create unique IDs for off-canvas and dropdown elements to avoid conflicts
 */
$offcanvas_id = 'off-' . uniqid();
$dropdown_id = 'drop-' . uniqid();

/**
 * Determine if Search Field is Needed
 * Show search functionality for option lists with more than 15 items
 */
$search_field_treshold = count($option_values) > 15;

/**
 * Auto-Load Configuration Integration
 * Handle loading of saved configuration values for radio button selection
 */
$auto_load_selected_value = '';
if (!empty($posted_value)) {
    $auto_load_selected_value = $posted_value;

    // Debug output for auto-load process
    product_configurator_debug("Offdrops Auto-Load", [
        'option' => $option_key,
        'value' => $auto_load_selected_value
    ], 'info', 'templates');
}
?>

<?php /* Off-Canvas Dropdown Option Container */ ?>
<div class="option-group<?php echo ($option_type === 'offdrops-child') ? ' option-group-child d-none' : ''; ?> option-offdrop col-12 mt-0"
    id="option_<?php echo esc_attr($option_key); ?>"
    data-key="<?php echo esc_attr($option_key); ?>"
    data-label="<?php echo esc_attr($option_label); ?>"
    data-order="<?php echo esc_attr($option_order); ?>"
    data-group="<?php echo esc_attr($option_group); ?>">

    <?php
    /**
     * TRIGGER BUTTON
     * Main button that opens the dropdown/off-canvas selection interface
     */
    ?>
    <div class="form-floating focus-ring mt-3">
        <button class="form-select focus-ring <?php echo $option_required ? 'yes-selection' : 'no-selection'; ?> offdrop-trigger"
            type="button"
            data-target="offdrop"
            data-offcanvas-id="<?= esc_attr($offcanvas_id); ?>"
            data-dropdown-id="<?= esc_attr($dropdown_id); ?>">
        </button>
        <label class="text-hind pe-none">
            <?php echo $option_label; ?>
        </label>
        <span class="option-value-label text-truncate position-absolute start-0 top-0 lh-sm pe-none fade"></span>
    </div>

    <?php
    /**
     * OPTIONS CONTENT BODY
     * Contains all selectable options, search functionality, and descriptions
     */
    ?>
    <div class="offdrop-body d-none">
        <div class="row m-0">
            <?php
            /**
             * DESCRIPTION SECTION
             * Optional help button with description modal trigger
             */
            if (!empty($option_description) && !empty($option_description_file)): ?>
                <div class="col-12 p-0 ps-4 pe-md-3 mt-3">
                    <button type="button"
                        class="option-desc-btn btn btn-sm btn-link text-wrap text-start mw-100 p-0<?php echo $search_field_treshold ? '' : ' mb-3'; ?>"
                        data-modal-link="<?= esc_attr($modal_link); ?>"
                        data-modal-title="<?= esc_attr($option_label); ?>">
                        <div class="row g-3 align-items-start">
                            <div class="col-auto ps-1">
                                <i class="fa-sharp fa-light fa-circle-question fa-lg pt-2" aria-hidden="true"></i>
                            </div>
                            <div class="col">
                                <span class="option-desc-label"><?= esc_attr($option_description); ?></span>
                            </div>
                        </div>
                    </button>
                </div>
            <?php endif; ?>

            <?php
            /**
             * SEARCH FIELD SECTION
             * Conditional search input for large option lists
             */
            if ($search_field_treshold): ?>
                <div class="col-12 sticky-top p-0">
                    <div class="option-search-wrapper d-flex position-relative py-3 px-md-3">
                        <?php /* Search Icon */ ?>
                        <span class="btn btn-link pe-none text-muted position-absolute ms-1">
                            <i class="fa-sharp fa-light fa-search"></i>
                        </span>
                        <?php /* Search Input */ ?>
                        <input type="search"
                            enterkeyhint="search"
                            class="option-search-input form-control ps-5"
                            placeholder="<?= esc_attr__('Optionen suchen...', 'bsawesome'); ?>"
                            data-target="#<?= esc_attr($offcanvas_id); ?> .values-group, #<?= esc_attr($dropdown_id); ?> .values-group">
                        <?php /* Clear Search Button */ ?>
                        <button class="option-search-input-reset btn btn-link position-absolute end-0 top-50 translate-middle-y me-md-3" type="button">
                            <i class="fa-sharp fa-light fa-xmark"></i>
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <?php
            /**
             * OPTION VALUES SECTION
             * Contains all selectable radio button options
             * .w-100 is important for proper width handling, especially for search result alerts
             */
            ?>
            <div class="col-12 p-0">
                <div class="values-group btn-group-vertical w-100" role="group">

                    <?php
                    /**
                     * NONE/PLACEHOLDER OPTION
                     * For non-required fields, provide option to have no selection
                     */
                    if (!$option_required): ?>
                        <?php
                        /**
                         * Auto-Load None Value Selection Logic
                         * Select "none" option only when no auto-load value is present
                         */
                        $none_is_selected = false;
                        if (empty($auto_load_selected_value)) {
                            $none_is_selected = true;
                        }
                        ?>
                        <label style="--bs-btn-disabled-color: var(--bs-body-color);" class="btn btn-outline-secondary link-body-emphasis bg-body text-hind fw-normal <?php echo !empty($option_placeholder_image) ? 'ps-3 p-2 pe-4' : 'ps-4 py-3 pe-3'; ?><?php echo ($option_type === 'offdrops-child') ? ' d-none' : ''; ?>"
                            for="<?= esc_attr($value_none_id); ?>">
                            <span class="row g-0 align-items-center">
                                <!-- Radio Button -->
                                <span class="col-auto<?php echo !empty($option_placeholder_image) ? ' d-none' : ''; ?>">
                                    <input type="radio"
                                        class="option-radio option-none form-check-input<?php echo ($option_type === 'offdrops-child') ? ' d-none' : ' me-3'; ?>"
                                        name="<?= esc_attr($option_name); ?>"
                                        id="<?= esc_attr($value_none_id); ?>"
                                        value=""
                                        data-value=""
                                        data-label="<?= !empty($option_placeholder) ? esc_attr($option_placeholder) : esc_attr__('Keins', 'bsawesome'); ?>"
                                        data-price="0"
                                        <?php echo $none_is_selected ? 'checked' : ''; ?>>
                                </span>
                                <?php /* Placeholder Image */ ?>
                                <?php if (!empty($option_placeholder_image)): ?>
                                    <span class="col-3 col-xl-2 me-3">
                                        <?php
                                        echo do_shortcode(
                                            '[img id="' . esc_attr($option_placeholder_image) . '" class="pe-none border" size="thumbnail"]'
                                        );
                                        ?>
                                    </span>
                                <?php endif; ?>
                                <?php /* Label Text */ ?>
                                <span class="col text-truncate text-start">
                                    <?= !empty($option_placeholder) ? esc_html($option_placeholder) : esc_html__('Keins', 'bsawesome'); ?>
                                </span>
                                <?php /* Placeholder Description Button */ ?>
                                <?php if (!empty($option_placeholder_description_file)):
                                    // Generate modal link for placeholder description file
                                    // Preserve subfolder structure by removing only .html extension
                                    $placeholder_filename_without_ext = preg_replace('/\.html$/', '', $option_placeholder_description_file);
                                    $placeholder_modal_link = 'configurator/' . $placeholder_filename_without_ext;
                                ?>
                                    <span class="col-auto">
                                        <button type="button"
                                            data-bs-tooltip="true"
                                            title="<?= esc_attr($option_placeholder_description); ?>"
                                            class="btn btn-link text-hind p-2 ms-1"
                                            style="--bs-bt-color: var(--bs-body-color);--bs-btn-hover-bg: transparent; --bs-btn-active-bg: transparent;"
                                            data-modal-link="<?= esc_attr($placeholder_modal_link); ?>"
                                            data-modal-title="<?= !empty($option_placeholder) ? esc_attr($option_placeholder) : esc_attr__('Keins', 'bsawesome'); ?>">
                                            <i class="fa-sharp fa-lg mt-1 fa-light fa-circle-question" aria-hidden="true"></i>
                                        </button>
                                    </span>
                                <?php elseif (!empty($option_placeholder_description)): ?>
                                    <?php /* Fallback: Show tooltip only if no description file but description exists */ ?>
                                    <span class="col-auto">
                                        <span class="btn btn btn-link text-hind p-2 ms-1"
                                            style="--bs-bt-color: var(--bs-body-color); --bs-btn-hover-color: var(--bs-body-color); --bs-btn-active-color: var(--bs-body-color); --bs-btn-hover-bg: transparent; --bs-btn-active-bg: transparent;"
                                            data-bs-tooltip="true"
                                            title="<?= esc_attr($option_placeholder_description); ?>">
                                            <i class="fa-sharp fa-lg mt-1 fa-light fa-circle-info" aria-hidden="true"></i>
                                        </span>
                                    </span>
                                <?php elseif (!empty($option_placeholder_image)): ?>
                                    <?php /* Image zoom modal fallback for placeholder when no description but image exists */ ?>
                                    <span class="col-auto">
                                        <button type="button"
                                            data-bs-tooltip="true"
                                            title="<?= esc_attr__('Bild vergrößern', 'bsawesome'); ?>"
                                            class="btn btn-link text-hind p-2 ms-1"
                                            style="--bs-bt-color: var(--bs-body-color);--bs-btn-hover-bg: transparent; --bs-btn-active-bg: transparent;"
                                            data-modal-image="<?= esc_attr($option_placeholder_image); ?>"
                                            data-modal-title="<?= !empty($option_placeholder) ? esc_attr($option_placeholder) : esc_attr__('Keins', 'bsawesome'); ?>">
                                            <i class="fa-sharp fa-lg mt-1 fa-light fa-image" aria-hidden="true"></i>
                                        </button>
                                    </span>
                                <?php endif; ?>
                            </span>
                        </label>
                    <?php endif; ?>

                    <?php
                    /**
                     * SELECTABLE OPTION VALUES
                     * Loop through all available options and create radio buttons
                     */
                    $index = 0;
                    foreach ($option_values as $option_key => $sub_option):
                        $sub_key       = $sub_option['key'] ?? '';
                        $sub_label     = $sub_option['label'] ?? '';
                        $sub_name      = sanitize_title($sub_option['key'] ?? '');
                        $sub_image     = $sub_option['image'] ?? '';
                        $sub_price     = $sub_option['price'] ?? '';
                        $sub_description = $sub_option['description'] ?? '';
                        $sub_description_file = $sub_option['description_file'] ?? '';
                        $sub_order     = $sub_option['order'] ?? 0; // Added order support
                        $sub_option_id = uniqid();

                        /**
                         * Auto-Load Radio Button Selection Logic
                         * Determine if this option should be selected based on auto-loaded value
                         */
                        $is_selected = false;

                        if (!empty($auto_load_selected_value)) {
                            // Auto-Load: Check if this value should be selected
                            if ($auto_load_selected_value === $sub_name) {
                                $is_selected = true;
                            }
                        } elseif ($option_required && $index === 0) {
                            // Fallback: Select first element for required fields
                            $is_selected = true;
                        }
                    ?>
                        <label style="--bs-btn-disabled-color: var(--bs-body-color);" class="btn btn-outline-secondary bg-body text-body text-hind fw-normal<?php echo !empty($sub_image) ? ' ps-3 p-2 pe-4' : ' ps-4 py-3 pe-4'; ?>"
                            for="<?= esc_attr($sub_option_id); ?>">
                            <span class="row g-0 align-items-center">
                                <!-- Radio Button -->
                                <span class="col-auto<?php echo !empty($sub_image) ? ' d-none' : ' me-3'; ?>">
                                    <input type="radio"
                                        class="option-radio form-check-input"
                                        name="<?= esc_attr($option_name); ?>"
                                        id="<?= esc_attr($sub_option_id); ?>"
                                        value="<?= esc_html($sub_name); ?>"
                                        data-value="<?= esc_attr($sub_name); ?>"
                                        data-label="<?= esc_attr($sub_label); ?>"
                                        data-price="<?= esc_attr($sub_price); ?>"
                                        <?php echo $is_selected ? 'checked' : ''; ?>>
                                </span>
                                <?php /* Option Image */ ?>
                                <?php if (!empty($sub_image)): ?>
                                    <span class="col-3 col-xl-2 me-3">
                                        <?php
                                        echo do_shortcode(
                                            '[img id="' . esc_attr($sub_image) . '" class="pe-none border" size="thumbnail"]'
                                        );
                                        ?>
                                    </span>
                                <?php endif; ?>
                                <?php /* Option Label */ ?>
                                <span class="col w-100 text-truncate text-start"><?= esc_html($sub_label); ?></span>
                                <?php /* Price Display */ ?>
                                <span class="value-price col-auto">
                                    <?php if (!empty($sub_price) && $sub_price != '0'): ?>
                                        (+<?= esc_html(str_replace('.', ',', $sub_price)); ?> €)
                                    <?php endif; ?>
                                </span>
                                <?php /* Value Description Button */ ?>
                                <?php if (!empty($sub_description_file)): ?>
                                    <?php
                                    // Generate modal link for value description file using same pattern as option descriptions
                                    // Preserve subfolder structure by removing only .html extension
                                    $filename_without_ext = preg_replace('/\.html$/', '', $sub_description_file);
                                    $value_modal_link = 'configurator/' . $filename_without_ext;
                                    ?>
                                    <span class="col-auto">
                                        <button type="button"
                                            data-bs-tooltip="true"
                                            title="<?= esc_attr($sub_description); ?>"
                                            class="btn btn-link text-hind p-2 ms-1"
                                            style="--bs-bt-color: var(--bs-body-color);--bs-btn-hover-bg: transparent; --bs-btn-active-bg: transparent;"
                                            data-modal-link="<?= esc_attr($value_modal_link); ?>"
                                            data-modal-title="<?= esc_attr($sub_label); ?>">
                                            <i class="fa-sharp fa-lg mt-1 fa-light fa-circle-question" aria-hidden="true"></i>
                                        </button>
                                    </span>
                                <?php elseif (!empty($sub_description)): ?>
                                    <?php /* Fallback: Show tooltip only if no description file but description exists */ ?>
                                    <span class="col-auto">
                                        <span class="btn btn btn-link text-hind p-2 ms-1"
                                            style="--bs-bt-color: var(--bs-body-color); --bs-btn-hover-color: var(--bs-body-color); --bs-btn-active-color: var(--bs-body-color); --bs-btn-hover-bg: transparent; --bs-btn-active-bg: transparent;"
                                            data-bs-tooltip="true"
                                            title="<?= esc_attr($sub_description); ?>">
                                            <i class="fa-sharp fa-lg mt-1 fa-light fa-circle-info" aria-hidden="true"></i>
                                        </span>
                                    </span>
                                <?php elseif (!empty($sub_image)): ?>
                                    <?php /* Image zoom modal fallback when no description but image exists */ ?>
                                    <span class="col-auto">
                                        <button type="button"
                                            data-bs-tooltip="true"
                                            title="<?= esc_attr__('Bild vergrößern', 'bsawesome'); ?>"
                                            class="btn btn-link text-hind p-2 ms-1"
                                            style="--bs-bt-color: var(--bs-body-color);--bs-btn-hover-bg: transparent; --bs-btn-active-bg: transparent;"
                                            data-modal-image="<?= esc_attr($sub_image); ?>"
                                            data-modal-title="<?= esc_attr($sub_label); ?>">
                                            <i class="fa-sharp fa-lg mt-1 fa-light fa-image" aria-hidden="true"></i>
                                        </button>
                                    </span>
                                <?php else: ?>
                                    <?php /* Fallback: Show empty span when neither description file nor description exists */ ?>
                                    <span class="col-auto">
                                        <span class="btn btn btn-link text-hind p-2 ms-1"
                                            style="--bs-btn-bg: transparent;--bs-btn-hover-bg: transparent; --bs-btn-active-bg: transparent;">
                                            <i class="fa-sharp fa-lg mt-1 fa-light fa-circle-info opacity-0 pe-none" aria-hidden="true"></i>
                                        </span>
                                    </span>
                                <?php endif; ?>
                            </span>
                        </label>
                    <?php
                        $index++;
                    endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <?php
    /**
     * OFFCANVAS COMPONENT (Mobile View)
     * Full-screen overlay for mobile selection interface
     * .option-offcanvas class is important for JavaScript initialization
     */
    ?>
    <div class="offcanvas-md offcanvas-bottom bg-secondary-subtle"
        id="<?= esc_attr($offcanvas_id); ?>"
        aria-labelledby="<?= esc_attr($offcanvas_id); ?>_label"
        tabindex="-1">

        <?php
        /**
         * Offcanvas Header
         * Contains title and close button
         */
        ?>
        <div class="offcanvas-header p-3">
            <?php /* Offcanvas Title */ ?>
            <h5 class="offcanvas-title" id="<?= esc_attr($offcanvas_id); ?>_label">
                <?= esc_html($option_label); ?>
            </h5>
            <?php /* Offcanvas Close Button */ ?>
            <button type="button"
                class="btn-close"
                data-bs-dismiss="offcanvas"
                data-bs-target="#<?= esc_attr($offcanvas_id); ?>"
                aria-label="<?= esc_attr__('Close', 'woocommerce'); ?>">
            </button>
        </div>

        <?php
        /**
         * Offcanvas Body
         * Content area for options (populated by JavaScript)
         * margin-top: -16px fixes sticky top issue on iOS Chrome
         */
        ?>
        <div class="offcanvas-body pt-0 mt-n3 mt-md-0"></div>
    </div>

    <?php
    /**
     * DROPDOWN COMPONENT (Desktop View)
     * Standard dropdown menu for desktop selection interface
     */
    ?>
    <div class="dropdown-menu w-100 p-0 z-5"
        style="--bs-dropdown-border-color: var(--bs-secondary);"
        id="<?= esc_attr($dropdown_id); ?>">
        <?php
        /**
         * Dropdown Body
         * Content area for options (populated by JavaScript)
         */
        ?>
        <div class="dropdown-body"></div>
    </div>
</div>