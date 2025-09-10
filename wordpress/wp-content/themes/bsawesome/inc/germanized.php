<?php defined('ABSPATH') || exit;

/**
 * WooCommerce Germanized Theme Integration
 *
 * Provides Bootstrap-compatible styling modifications for WooCommerce Germanized plugin
 * checkout tables. Ensures consistent theme styling across German e-commerce requirements.
 *
 * @package BSAwesome
 * @subpackage Germanized
 * @version 2.5.0
 *
 * @return void Outputs modified checkout tax table HTML with Bootstrap classes
 */

function woocommerce_gzd_template_cart_total_tax() {
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
