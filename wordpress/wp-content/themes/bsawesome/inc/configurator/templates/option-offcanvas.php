<?php defined('ABSPATH') || exit; ?>

<?php
// Generate Offcanvas and Dropdown IDs
$offcanvas_id = 'off-' . uniqid();
$dropdown_id = 'drop-' . uniqid();
$has_many_options = count($option_values) > 15;
// 
// <div class="option-group col-12 mt-0<?php echo ($option_type === 'offcanvas-child') ? ' collapse fade' : ' collapse show';
//
// Question: Why using mt-0 for option-group and adding mt-3 for form-floating?
//
?>

<div class="option-group<?php echo ($option_type === 'offcanvas-child') ? ' option-group-child d-none' : ''; ?> option-offdrop col-12 mt-0"
    id="option_<?php echo esc_attr($option_key); ?>"
    data-key="<?php echo esc_attr($option_key); ?>"
    data-label="<?php echo esc_attr($option_label); ?>">

    <?php
    // Button
    ?>
    <div class="form-floating mt-3">
        <button class="form-select focus-ring <?php echo $option_required ? 'yes-selection' : 'no-selection'; ?> offdrop-trigger"
            type="button"
            data-target="offdrop"
            data-offcanvas-id="<?= esc_attr($offcanvas_id); ?>"
            data-dropdown-id="<?= esc_attr($dropdown_id); ?>">
        </button>
        <label class="text-hind pe-none">
            <?php echo $option_label; ?>
        </label>
        <span class="option-value-label text-truncate position-absolute lh-sm pe-none fade" style="left: 0; top: 0; padding: 1.625rem 2.25rem 0.625rem 0.75rem"></span>
    </div>

    <?php
    // Options
    ?>
    <div class="offdrop-body">
        <?php
        // Description
        ?>
        <div class="row g-3">
            <?php if (!empty($option_description)): ?>
                <div class="col-12">
                    <button type="button"
                        class="option-desc-btn btn btn-sm btn-dark"
                        data-modal-link="<?= esc_attr($modal_link); ?>">
                        <span class="option-desc-label text-truncate"><?= esc_attr($option_description); ?></span><i class="fa-sharp fa-light fa-circle-question ms-2" aria-hidden="true"></i>
                    </button>
                </div>
            <?php endif; ?>
            <?php /* Suchfeld nur bei vielen Optionen */ ?>
            <?php if ($has_many_options): ?>
                <div class="col-12 mx-md-3 pe-md-5 d-none">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-sharp fa-light fa-search"></i></span>
                        <input type="text"
                            class="form-control option-search-input"
                            placeholder="<?= esc_attr__('Search options...', 'my-product-configurator'); ?>"
                            data-target="#<?= esc_attr($offcanvas_id); ?> .values-group, #<?= esc_attr($dropdown_id); ?> .values-group">
                    </div>
                </div>
            <?php endif; ?>
            <?php
            // Option values
            ?>
            <div class="col-12">
                <div class="values-group btn-group-vertical w-100" role="group">
                    <?php if (!$option_required): ?>
                        <?php
                        // None value
                        //
                        // "option-none" class is important for carousel.js
                        //
                        // Automatically select the first option if required and $index=0
                        $first_option       = reset($option_values);
                        $first_option_label = $first_option['label'] ?? '';
                        $first_option_image = $first_option['image'] ?? '';

                        // 2. Flag setzen: Placeholder == erstes Label?
                        $use_first_image_for_none =
                            ! empty($option_placeholder)
                            && $option_placeholder === $first_option_label;
                        ?>
                        <label class="btn btn-outline-secondary link-body-emphasis bg-body text-hind fw-normal <?php echo !empty($use_first_image_for_none) ? 'use-first-image-for-none ps-3 p-2 pe-4' : '  ps-4 py-3 pe-3'; ?><?php echo ($option_type === 'offcanvas-child') ? ' d-none' : ''; ?>"
                            for="<?= esc_attr($value_none_id); ?>">
                            <span class="row g-3 align-items-center">
                                <span class="col-auto<?php echo !empty($use_first_image_for_none) ? ' d-none' : ''; ?>">
                                    <input type="radio"
                                        class="option-radio option-none form-check-input<?php echo ($option_type === 'offcanvas-child') ? ' d-none' : ''; ?>"
                                        name="<?= esc_attr($option_name); ?>"
                                        id="<?= esc_attr($value_none_id); ?>"
                                        value=""
                                        data-value=""
                                        data-label="<?= !empty($option_placeholder) ? esc_attr($option_placeholder) : esc_attr__('Keins', 'my-product-configurator'); ?>"
                                        data-price="0"
                                        checked>
                                </span>
                                <?php if (!empty($use_first_image_for_none)): ?>
                                    <span class="col-3">
                                        <?php
                                        echo do_shortcode(
                                            '[img id="' . esc_attr($first_option_image) . '" class="pe-none border" size="thumbnail"]'
                                        );
                                        ?>
                                    </span>
                                <?php endif; ?>
                                <!-- <span class="col-auto"><i class="fa-sharp fa-light fa-ban" aria-hidden="true"></i></span> -->
                                <span class="col text-truncate text-start">
                                    <?= !empty($option_placeholder) ? esc_html($option_placeholder) : esc_html__('Keins', 'my-product-configurator'); ?>
                                </span>
                            </span>
                        </label>
                    <?php endif; ?>
                    <?php
                    // Values
                    ?>
                    <?php
                    $index = 0;
                    foreach ($option_values as $option_key => $sub_option):
                        if (! empty($use_first_image_for_none) && $index === 0) {
                            $index++;
                            continue;
                        }

                        $sub_key       = $sub_option['key'] ?? '';
                        $sub_label     = $sub_option['label'] ?? '';
                        $sub_name      = sanitize_title($sub_option['key'] ?? '');
                        $sub_image     = $sub_option['image'] ?? '';
                        $sub_price     = $sub_option['price'] ?? '';
                        $sub_option_id = uniqid();

                        // Automatically select the first option if required and $index=0
                        $is_selected = false;
                        if ($option_required && $index === 0) {
                            $is_selected = true;
                        }
                    ?>
                        <label class="btn btn-outline-secondary bg-body text-body text-hind fw-normal<?php echo !empty($sub_image) ? ' ps-3 p-2 pe-4' : '  ps-4 py-3 pe-3'; ?>"
                            for="<?= esc_attr($sub_option_id); ?>">
                            <span class="row g-3 align-items-center">
                                <span class="col-auto<?php echo !empty($sub_image) ? ' d-none' : ''; ?>">
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
                                <?php if (!empty($sub_image)): ?>
                                    <span class="col-3">
                                        <?php
                                        echo do_shortcode(
                                            '[img id="' . esc_attr($sub_image) . '" class="pe-none border" size="thumbnail"]'
                                        );
                                        ?>
                                    </span>
                                <?php endif; ?>
                                <span class="col w-100 text-truncate text-start"><?= esc_html($sub_label); ?></span>
                                <span class="col-auto">
                                    <?php if (!empty($sub_price) && $sub_price != '0'): ?>
                                        (+<?= esc_html(str_replace('.', ',', $sub_price)); ?> €)
                                    <?php endif; ?>
                                </span>
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
    // Offcanvas
    //
    // option-offcanvas class is important to initialize the offcanvas.js
    ?>
    <div class="offcanvas-md offcanvas-bottom bg-secondary-subtle"
        id="<?= esc_attr($offcanvas_id); ?>"
        aria-labelledby="<?= esc_attr($offcanvas_id); ?>_label"
        tabindex="-1">
        <?php
        // Offcanvas header
        ?>
        <div class="offcanvas-header p-3">
            <?php
            // Offcanvas title
            ?>
            <h5 class="offcanvas-title" id="<?= esc_attr($offcanvas_id); ?>_label">
                <?= esc_html($option_label); ?>
            </h5>
            <?php
            // Offcanvas close button
            ?>
            <button type="button"
                class="btn-close"
                data-bs-dismiss="offcanvas"
                data-bs-target="#<?= esc_attr($offcanvas_id); ?>"
                aria-label="<?= esc_attr__('Close', 'woocommerce'); ?>">
            </button>
        </div>
        <?php
        // Offcanvas body
        ?>
        <div class="offcanvas-body"></div>
    </div>

    <?php
    // Dropdown
    ?>
    <div class="dropdown-menu w-100 pt-3 pb-0 z-5"
        style="--bs-dropdown-border-color: var(--bs-secondary);"
        id="<?= esc_attr($dropdown_id); ?>">
        <?php
        // Dropdown body
        ?>
        <div class="dropdown-body"></div>
    </div>
</div>