<?php
/**
 * Hello Child Theme Functions
 *
 * This file contains the main functions for the Hello Child theme.
 * It is a child theme of Hello Elementor.
 */

// Include plugin.php to use is_plugin_active()
if (!function_exists('is_plugin_active')) {
    include_once ABSPATH . 'wp-admin/includes/plugin.php';
}

// Check if the Unique Auction Bidding plugin is active
$uab_plugin_active = is_plugin_active('unique-auction-bidding/unique-auction-bidding.php');

/**
 * Enqueue child theme styles and scripts.
 */
add_action('wp_enqueue_scripts', 'hello_child_enqueue_styles');
function hello_child_enqueue_styles() {
    // Enqueue the parent theme's style.css
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');

    // Enqueue the child theme's style.css
    wp_enqueue_style('child-style', get_stylesheet_uri(), array('parent-style'), '1.0.0');
}

/**
 * Increase nonce lifetime to 24 hours to prevent expiration issues
 */
add_filter('nonce_life', function() {
    return 24 * HOUR_IN_SECONDS;
});

/**
 * Ensure the current user has permissions to edit products
 */
add_action('admin_init', function() {
    $user = wp_get_current_user();
    if (in_array('shop_manager', (array) $user->roles) || in_array('administrator', (array) $user->roles)) {
        if (!current_user_can('edit_products')) {
            $user->add_cap('edit_products');
            $user->add_cap('edit_published_products');
            $user->add_cap('edit_others_products');
        }
    }
}, 20);

/**
 * Enqueue Stripe debug script on payment method page
 */
add_action('wp_enqueue_scripts', function () {
    if (is_account_page() && function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('add-payment-method')) {
        wp_enqueue_script(
            'stripe-debug-js',
            get_stylesheet_directory_uri() . '/stripe-debug.js',
            array('jquery', 'wc-stripe'),
            '1.0.0',
            true
        );
        error_log('Stripe Debug PHP: Enqueued stripe-debug.js on Add Payment Method page');
    }
});

// Commented Stripe Customer ID Debug (reference for future use)
// $user_id = get_current_user_id();
// $stripe_customer_id = get_user_meta($user_id, '_stripe_customer_id', true);
// error_log("Stripe Customer ID for User $user_id: " . ($stripe_customer_id ? $stripe_customer_id : 'Not found'));
?>