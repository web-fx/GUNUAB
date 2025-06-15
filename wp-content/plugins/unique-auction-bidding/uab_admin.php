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
    $admin_core_path = plugin_dir_path(__FILE__) . 'Administration_Mod/admin-core.php';
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
        error_log('admin-core.php not found at ' . $admin_core_path . ' - Directory structure: ' . print_r(scandir(plugin_dir_path(__FILE__) . 'Administration_Mod/'), true));
    }

    // Include the admin module for WooCommerce settings with explicit path
    $admin_module_path = plugin_dir_path(__FILE__) . 'Administration_Mod/Control_Mod/admin-module.php';
    error_log('Attempting to load admin-module.php from: ' . $admin_module_path);
    if (file_exists($admin_module_path)) {
        if (is_readable($admin_module_path)) {
            require_once $admin_module_path;
            error_log('admin-module.php loaded successfully from ' . $admin_module_path);
        } else {
            error_log('admin-module.php at ' . $admin_module_path . ' is not readable - Permissions: ' . substr(sprintf('%o', fileperms($admin_module_path)), -4));
        }
    } else {
        error_log('admin-module.php not found at ' . $admin_module_path . ' - Directory structure: ' . print_r(scandir(plugin_dir_path(__FILE__) . 'Administration_Mod/Control_Mod/'), true));
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

// Add admin action to generate structure files
add_action('admin_action_generate_structure', 'uab_generate_structure');
add_action('admin_action_generate_structure_with_contents', 'uab_generate_structure_with_contents');

function uab_generate_structure() {
    require_once plugin_dir_path(__FILE__) . 'Administration_Mod/Control_Mod/scripts/generate_structure.php';
    wp_die('Structure generated. Check ' . plugin_dir_path(__FILE__) . 'structure.txt');
}

function uab_generate_structure_with_contents() {
    require_once plugin_dir_path(__FILE__) . 'Administration_Mod/Control_Mod/scripts/generate_structure_with_contents.php';
    wp_die('Structure with contents generated. Check ' . plugin_dir_path(__FILE__) . 'structure_with_contents.txt');
}

// Add admin menu item for generation
function uab_add_admin_menu() {
    add_management_page('Generate UAB Structure', 'Generate UAB Structure', 'manage_options', 'uab-generate-structure', 'uab_render_generate_page');
}
add_action('admin_menu', 'uab_add_admin_menu');

function uab_render_generate_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    echo '<div class="wrap"><h1>Generate UAB Structure</h1>';
    echo '<a href="' . wp_nonce_url(admin_url('admin.php?action=generate_structure'), 'generate_structure') . '" class="button">Generate Structure</a> ';
    echo '<a href="' . wp_nonce_url(admin_url('admin.php?action=generate_structure_with_contents'), 'generate_structure_with_contents') . '" class="button">Generate Structure with Contents</a>';
    echo '</div>';
}