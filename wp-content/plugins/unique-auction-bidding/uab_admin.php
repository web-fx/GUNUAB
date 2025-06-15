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

    // Include the admin module for WooCommerce settings with explicit instantiation
    $admin_module_path = plugin_dir_path(__FILE__) . 'Administration_Mod/Control_Mod/admin-module.php';
    error_log('Attempting to load admin-module.php from: ' . $admin_module_path);
    if (file_exists($admin_module_path)) {
        if (is_readable($admin_module_path)) {
            require_once $admin_module_path;
            if (class_exists('UAB_Admin_Module')) {
                new UAB_Admin_Module(); // Explicitly instantiate the class
                error_log('UAB_Admin_Module class instantiated from ' . $admin_module_path);
            } else {
                error_log('UAB_Admin_Module class not found in ' . $admin_module_path . ' - File contents: ' . file_get_contents($admin_module_path));
            }
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

// Add admin action to generate structure files via AJAX
add_action('wp_ajax_generate_structure', 'uab_generate_structure');
add_action('wp_ajax_generate_structure_with_contents', 'uab_generate_structure_with_contents');

function uab_generate_structure() {
    check_ajax_referer('generate_structure', 'nonce');
    $output_file = WP_CONTENT_DIR . '/uploads/wc-logs/structure.txt';
    $success = false;
    ob_start();
    require_once plugin_dir_path(__FILE__) . 'Administration_Mod/Control_Mod/scripts/generate_structure.php';
    ob_end_clean();
    $success = file_exists($output_file) && is_writable($output_file);
    wp_send_json_success('Structure generated. Check ' . $output_file);
}

function uab_generate_structure_with_contents() {
    check_ajax_referer('generate_structure_with_contents', 'nonce');
    $output_file = WP_CONTENT_DIR . '/uploads/wc-logs/structure_with_contents.txt';
    $success = false;
    ob_start();
    require_once plugin_dir_path(__FILE__) . 'Administration_Mod/Control_Mod/scripts/generate_structure_with_contents.php';
    ob_end_clean();
    $success = file_exists($output_file) && is_writable($output_file);
    wp_send_json_success('Structure with contents generated. Check ' . $output_file);
}

// Add admin menu item for generation with AJAX
function uab_add_admin_menu() {
    add_management_page('Generate UAB Structure', 'Generate UAB Structure', 'manage_options', 'uab-generate-structure', 'uab_render_generate_page');
}
add_action('admin_menu', 'uab_add_admin_menu');

function uab_render_generate_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    // Enqueue WordPress admin styles for dismissible notice
    wp_enqueue_style('wp-admin');
    // Enqueue script for AJAX and popup
    wp_enqueue_script('uab-generate-script', plugin_dir_url(__FILE__) . 'Administration_Mod/Control_Mod/scripts/generate-script.js', array('jquery'), '1.0', true);
    wp_localize_script('uab-generate-script', 'uab_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce_structure' => wp_create_nonce('generate_structure'),
        'nonce_contents' => wp_create_nonce('generate_structure_with_contents'),
    ));
    echo '<div class="wrap"><h1>Generate UAB Structure</h1>';
    echo '<div id="uab-alert" style="display:none; position: fixed; top: 120px; left: 50%; transform: translateX(-50%); max-width: 80%; padding: 15px; z-index: 1000; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" class="notice notice-success is-dismissible"></div>';
    echo '<a href="#" class="button" id="generate-structure">Generate Structure</a> ';
    echo '<a href="#" class="button" id="generate-structure-with-contents">Generate Structure with Contents</a>';
    echo '</div>';
}

// Add JavaScript file for popup with DOM readiness and error handling
file_put_contents(plugin_dir_path(__FILE__) . 'Administration_Mod/Control_Mod/scripts/generate-script.js',
    "jQuery(document).ready(function($) {
        // Ensure alert div exists
        if ($('#uab-alert').length) {
            $('#generate-structure').on('click', function(e) {
                e.preventDefault();
                $.ajax({
                    url: uab_ajax.ajax_url,
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'generate_structure',
                        nonce: uab_ajax.nonce_structure
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#uab-alert').html('<p>' + response.data + '</p>').show();
                        } else {
                            $('#uab-alert').html('<strong>Error!</strong> ' + response.data).show();
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#uab-alert').html('<strong>Error!</strong> ' + status + ': ' + error).show();
                    }
                });
            });

            $('#generate-structure-with-contents').on('click', function(e) {
                e.preventDefault();
                $.ajax({
                    url: uab_ajax.ajax_url,
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'generate_structure_with_contents',
                        nonce: uab_ajax.nonce_contents
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#uab-alert').html('<p>' + response.data + '</p>').show();
                        } else {
                            $('#uab-alert').html('<strong>Error!</strong> ' + response.data).show();
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#uab-alert').html('<strong>Error!</strong> ' + status + ': ' + error).show();
                    }
                });
            });

            // Handle dismissible notice (rely on WordPress default)
            // No need for custom click handler if is-dismissible works
        } else {
            console.error('uab-alert div not found in DOM');
        }
    });"
);

// Add debug log for WooCommerce settings tab filter
add_filter('woocommerce_settings_tabs_array', function ($tabs) {
    error_log('woocommerce_settings_tabs_array filter triggered in ' . __FILE__ . ' with tabs: ' . print_r($tabs, true));
    return $tabs;
}, 50);