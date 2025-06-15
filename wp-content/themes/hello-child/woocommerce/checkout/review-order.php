<?php
/**
 * Review order table
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/review-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woo.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 5.2.0
 */

defined('ABSPATH') || exit;

// Ensure WooCommerce is loaded
if (!function_exists('WC')) {
    return;
}

// Get the cart instance
$cart = WC()->cart;

// Exit if cart is not available or not properly initialized
if (!$cart || !is_object($cart) || !method_exists($cart, 'get_cart')) {
    return;
}
?>

<table class="shop_table woocommerce-checkout-review-order-table">
    <thead>
    <tr>
        <th class="product-name"><?php esc_html_e('Product', 'woocommerce'); ?></th>
        <th class="product-total"><?php esc_html_e('Subtotal', 'woocommerce'); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php
    do_action('woocommerce_review_order_before_cart_contents');

    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        $product = $cart_item['data'];
        $product_id = $cart_item['product_id'];
        ?>
        <tr class="cart_item">
            <td class="product-name">
                <?php echo wp_kses_post(apply_filters('woocommerce_cart_item_name', $product->get_name(), $cart_item, $cart_item_key)); ?>
                <?php echo apply_filters('woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . sprintf('× %s', $cart_item['quantity']) . '</strong>', $cart_item, $cart_item_key); ?>
                <?php echo wc_get_formatted_cart_item_data($cart_item); ?>
                <?php
                // Add the credit card processing fee notice for the Bid Token product (ID 1081)
                if ($product_id == 1081) {
                    $processing_rate = floatval(uab_get_setting('uab_credit_card_processing_rate', 2.9));
                    $plus_fee = floatval(uab_get_setting('uab_plus_fee', 0.30));
                    $notice = sprintf(
                        __('A credit card processing fee is charged for this transaction of %s%% + $%s.', 'unique-auction-bidding'),
                        number_format($processing_rate, 1),
                        number_format($plus_fee, 2)
                    );
                    echo '<div class="uab-checkout-fee-notice" style="margin-top: 5px; font-size: 14px; color: #333;">' . esc_html($notice) . '</div>';
                }
                ?>
                <?php
                // Display the short description
                $short_description = $product->get_short_description();
                if ($short_description) {
                    echo '<div class="product-short-description">' . wp_kses_post($short_description) . '</div>';
                }
                ?>
            </td>
            <td class="product-total">
                <?php echo apply_filters('woocommerce_cart_item_subtotal', $cart->get_product_subtotal($product, $cart_item['quantity']), $cart_item, $cart_item_key); ?>
            </td>
        </tr>
        <?php
    }

    do_action('woocommerce_review_order_after_cart_contents');
    ?>
    </tbody>
    <tfoot>
    <tr class="cart-subtotal">
        <th><?php esc_html_e('Subtotal', 'woocommerce'); ?></th>
        <td><?php wc_cart_totals_subtotal_html(); ?></td>
    </tr>

    <?php if ($cart->get_coupons()) : ?>
        <?php foreach ($cart->get_coupons() as $code => $coupon) : ?>
            <tr class="cart-discount coupon-<?php echo esc_attr(sanitize_title($code)); ?>">
                <th><?php wc_cart_totals_coupon_label($coupon); ?></th>
                <td><?php wc_cart_totals_coupon_html($coupon); ?></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($cart->needs_shipping() && $cart->show_shipping()) : ?>
        <?php do_action('woocommerce_review_order_before_shipping'); ?>
        <?php wc_cart_totals_shipping_html(); ?>
        <?php do_action('woocommerce_review_order_after_shipping'); ?>
    <?php endif; ?>

    <?php if ($cart->get_fees()) : ?>
        <?php foreach ($cart->get_fees() as $fee) : ?>
            <tr class="fee">
                <th><?php echo esc_html($fee->name); ?></th>
                <td><?php wc_cart_totals_fee_html($fee); ?></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (wc_tax_enabled() && !$cart->display_prices_including_tax()) : ?>
        <?php if ('itemized' === get_option('woocommerce_tax_total_display')) : ?>
            <?php foreach ($cart->get_tax_totals() as $code => $tax) : ?>
                <tr class="tax-rate tax-rate-<?php echo esc_attr(sanitize_title($code)); ?>">
                    <th><?php echo esc_html($tax->label); ?></th>
                    <td><?php echo wp_kses_post($tax->formatted_amount); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else : ?>
            <tr class="tax-total">
                <th><?php echo esc_html(WC()->countries->tax_or_vat()); ?></th>
                <td><?php wc_cart_totals_taxes_total_html(); ?></td>
            </tr>
        <?php endif; ?>
    <?php endif; ?>

    <?php do_action('woocommerce_review_order_before_order_total'); ?>

    <tr class="order-total">
        <th><?php esc_html_e('Total', 'woocommerce'); ?></th>
        <td><?php wc_cart_totals_order_total_html(); ?></td>
    </tr>

    <?php do_action('woocommerce_review_order_after_order_total'); ?>
    </tfoot>
</table>