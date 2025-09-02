<?php defined('ABSPATH') || exit; ?>

<?php
// Generate Offcanvas and Dropdown IDs
$offcanvas_id = 'off-' . uniqid();
$dropdown_id = 'drop-' . uniqid();
// 
// <div class="option-group col-12 mt-0<?php echo ($option_type === 'offcanvas-child') ? ' collapse fade' : ' collapse show';
//
// Question: Why using mt-0 for option-group and adding mt-3 for form-floating?
//
?>

<div class="option-group<?php echo ($option_type === 'offcanvas-child') ? ' option-group-child d-none' : ''; ?> col-12 mt-0"
    id="option_<?php echo esc_attr($option_key); ?>"
    data-key="<?php echo esc_attr($option_key); ?>"
    data-label="<?php echo esc_attr($option_label); ?>">

    <?php
    // Button
    //
    // offdrop-toggler class is important for JS
    ?>
    <div class="form-floating mt-3">
        <button class="form-select focus-ring focus-ring-primary no-selection offdrop-toggler"
            type="button"
            data-offcanvas-id="<?= esc_attr($offcanvas_id); ?>"
            data-dropdown-id="<?= esc_attr($dropdown_id); ?>">
        </button>
        <label class="fw-medium text-hind pe-none">
            <?php echo esc_html($option_label); ?>
            <?php if ($option_required): ?>
                <abbr class="required text-danger" title="erforderlich">*</abbr>
            <?php endif; ?>
        </label>
        <!-- Purpose not sure: <span class="option-value-label position-absolute lh-sm pe-none fade" style="left: 0; top: 0; padding: 1.625rem 2.25rem 0.625rem 0.75rem"></span> -->
    </div>

    <?php
    // Offcanvas Dropdown
    //
    // option-offcanvas class is important to initialize the offcanvas.js
    ?>
    <div class="option-offdrop option-offcanvas offcanvas-md offcanvas-bottom bg-secondary-subtle"
        tabindex="-1"
        id="<?= esc_attr($offcanvas_id); ?>"
        aria-labelledby="<?= esc_attr($offcanvas_id); ?>_label">

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
        <div class="offcanvas-body py-1">
            <div id="<?= esc_attr($dropdown_id); ?>" class="dropdown-body">

                <?php
                // Description
                if (!empty($option_description)): ?>
                    <div class="col">
                        <button type="button"
                            class="btn btn-sm btn-dark mb-3"
                            data-description-file="<?= esc_attr($option_description_file); ?>">
                            <span class=""><?= esc_attr($option_description); ?></span><i class="fa-sharp fa-light fa-circle-question ms-2" aria-hidden="true"></i>
                        </button>
                    </div>
                <?php endif; ?>

                <div class="btn-group-vertical w-100" role="group">
                    <?php if (!$option_required): ?>
                        <?php
                        // None value
                        //
                        // "option-none" class is important for carousel.js
                        //
                        // Automatically select the first option if required and $index=0
                        ?>
                        <div class="bg-body w-100" role="group">
                            <label class="btn btn-outline-secondary link-body-emphasis text-hind fw-normal w-100 px-4 py-3<?php echo ($option_type === 'offcanvas-child') ? ' d-none' : ''; ?>"
                                for="<?= esc_attr($value_none_id); ?>">
                                <span class="row g-3 align-items-center">
                                    <span class="col-auto">
                                        <input type="radio"
                                            class="option-radio option-none form-check-input<?php echo ($option_type === 'offcanvas-child') ? ' d-none' : ''; ?>"
                                            name="<?= esc_attr($option_name); ?>"
                                            id="<?= esc_attr($value_none_id); ?>"
                                            value=""
                                            data-value=""
                                            data-label="<?= esc_html__('Keins', 'my-product-configurator'); ?>"
                                            data-price="0"
                                            checked>
                                    </span>
                                    <!-- <span class="col-auto"><i class="fa-sharp fa-light fa-ban" aria-hidden="true"></i></span> -->
                                    <span class="col text-truncate text-start"><?= esc_html__('Keins', 'my-product-configurator'); ?></span>
                                </span>
                            </label>
                        </div>
                    <?php endif; ?>

                    <?php
                    // Option values
                    ?>
                    <?php
                    $index = 0;
                    foreach ($option_values as $option_key => $sub_option):
                        $sub_key       = $sub_option['key'] ?? '';
                        $sub_label     = $sub_option['label'] ?? '';
                        $sub_name      = sanitize_title($sub_option['key'] ?? '');
                        $sub_image     = $sub_option['image'] ?? '';
                        $sub_price     = $sub_option['price'] ?? '';
                        $sub_option_id = uniqid();

                        // Prepare image
                        // $display_image = (!empty($sub_image))
                        //     ? esc_url($image_base . ltrim($sub_image, '/'))
                        //     : ''; // Kein Platzhalter, wenn kein Bild vorhanden

                        // Use image
                        /* <?php if ($display_image): ?>
                        <img class="col-auto"
                            src="<?php echo esc_attr($display_image); ?>"
                            alt="<?php echo esc_attr($sub_label); ?>"
                            style="width:64px;height:50px;object-fit:cover;">
                        <?php endif; ?> */

                        // Automatically select the first option if required and $index=0
                        $is_selected = false;
                        if ($option_required && $index === 0) {
                            $is_selected = true;
                        }

                    ?>

                        <div class="bg-body w-100" role="group">
                            <label class="btn btn-outline-secondary link-body-emphasis text-hind fw-normal<?php echo !empty($sub_image) ? ' p-2' : '  px-4 py-3'; ?>"
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
                                    <span class="col text-truncate text-start"><?= esc_html($sub_label); ?></span>
                                    <?php if (!empty($sub_price) && $sub_price != '0'): ?>
                                        <span class="col-auto">(+<?= esc_html(str_replace('.', ',', $sub_price)); ?> €)</span>
                                    <?php endif; ?>
                                </span>
                            </label>
                        </div>

                    <?php
                        $index++;
                    endforeach; ?>
                </div>

            </div>
        </div>

    </div>
</div>