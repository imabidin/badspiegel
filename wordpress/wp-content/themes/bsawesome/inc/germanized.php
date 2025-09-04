<?php defined('ABSPATH') || exit;

/**
 * Germanized mods
 *
 * @package BSAwesome
 * @subpackage Germanized
 * @since 1.0.0
 * @author BS Awesome Team
 * @version 2.4.0
 */

/**
 * Overriding order-tax html to add "text-end" class for matching theme, specifically "woocommerce-checkout-review-order-table".
 */
function woocommerce_gzd_template_cart_total_tax()
{

    foreach (wc_gzd_get_cart_total_taxes() as $tax) :
        $label = wc_gzd_get_tax_rate_label($tax['tax']->rate);
?>
        <tr class="order-tax">
            <th><?php echo wp_kses_post($label); ?></th>
            <td class="text-end" data-title="<?php echo esc_attr($label); ?>"><?php echo wc_price($tax['amount']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                                                                ?></td>
        </tr>
<?php
    endforeach;
}
