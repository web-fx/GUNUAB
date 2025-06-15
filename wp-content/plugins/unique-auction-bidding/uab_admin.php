<?php
/*
 * Plugin Name: Unique Auction Bidding
 * Description: A custom auction bidding plugin with user management.
 * Version: 1.0
 * Author: WEB FX LLC - web-fx.com
 * File uab_admin.php
 */

if (!defined('ABSPATH')) exit;

register_activation_hook(__FILE__, 'unique_auction_activate');

// Force activation check on every load for testing
add_action('plugins_loaded', 'unique_auction_activation_check');

function unique_auction_activate() {
    error_log('unique_auction_activate called at ' . current_time('mysql') . ' in ' . __FILE__); // Debug activation
    $admin_core_path = __DIR__ . '/Administration_Mod/admin-core.php';
    if (file_exists($admin_core_path)) {
        if (is_readable($admin_core_path)) {
            require_once $admin_core_path;
            error_log('admin-core.php loaded successfully from ' . $admin_core_path);
            if (class_exists('UniqueAuctionAdmin')) {
                new UniqueAuctionAdmin();
                error_log('UniqueAuctionAdmin class instantiated');
            } else {
                error_log('UniqueAuctionAdmin class not found in ' . $admin_core_path);
            }
        } else {
            error_log('admin-core.php at ' . $admin_core_path . ' is not readable - Permissions: ' . substr(sprintf('%o', fileperms($admin_core_path)), -4));
        }
    } else {
        error_log('admin-core.php not found at ' . $admin_core_path . ' - Directory structure: ' . print_r(scandir(__DIR__), true));
    }

    // Include the admin module for WooCommerce settings
    $admin_module_path = __DIR__ . '/Administration_Mod/Control_Mod/admin-module.php';
    if (file_exists($admin_module_path)) {
        if (is_readable($admin_module_path)) {
            require_once $admin_module_path;
            error_log('admin-module.php loaded successfully from ' . $admin_module_path);
        } else {
            error_log('admin-module.php at ' . $admin_module_path . ' is not readable - Permissions: ' . substr(sprintf('%o', fileperms($admin_module_path)), -4));
        }
    } else {
        error_log('admin-module.php not found at ' . $admin_module_path . ' - Directory structure: ' . print_r(scandir(__DIR__ . '/Administration_Mod/Control_Mod/'), true));
    }
}

function unique_auction_activation_check() {
    if (get_option('unique_auction_activated') !== '1.0') {
        unique_auction_activate();
        update_option('unique_auction_activated', '1.0');
        error_log('unique_auction_activation_check triggered activation at ' . current_time('mysql'));
    }
}

function unique_auction_admin_init() {
    error_log('unique_auction_admin_init called at ' . current_time('mysql') . ' in ' . __FILE__); // Debug init
    // Placeholder for admin setup
}
add_action('admin_init', 'unique_auction_admin_init');