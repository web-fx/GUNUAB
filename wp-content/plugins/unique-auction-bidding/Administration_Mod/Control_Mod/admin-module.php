<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Ensure WooCommerce is active
if (!class_exists('WooCommerce')) {
    return;
}

// Define plugin constants
define('UAB_WC_TAB_ID', 'uab_settings');

class UAB_Admin_Module {
    public function __construct() {
        add_filter('woocommerce_settings_tabs_array', array($this, 'add_uab_settings_tab'), 50);
        add_action('woocommerce_settings_tabs_' . UAB_WC_TAB_ID, array($this, 'render_uab_settings_tab'));
        add_action('woocommerce_update_options_' . UAB_WC_TAB_ID, array($this, 'save_uab_settings'));
    }

    public function add_uab_settings_tab($tabs) {
        $tabs[UAB_WC_TAB_ID] = __('UAB Settings', 'unique-auction-bidding');
        return $tabs;
    }

    public function render_uab_settings_tab() {
        woocommerce_admin_fields($this->get_uab_settings());
    }

    public function save_uab_settings() {
        woocommerce_update_options($this->get_uab_settings());
    }

    private function get_uab_settings() {
        $settings = array(
            'section_title' => array(
                'name' => __('UAB Settings', 'unique-auction-bidding'),
                'type' => 'title',
                'desc' => __('Configure settings for the Unique Auction Bidding plugin. Add new options as modules are developed.', 'unique-auction-bidding'),
                'id'   => 'uab_settings_section'
            ),
            'section_end' => array(
                'type' => 'sectionend',
                'id'   => 'uab_settings_section_end'
            )
        );
        return apply_filters('uab_settings', $settings);
    }
}

// Initialize the admin module
new UAB_Admin_Module();

// Load text domain for translations (optional, add if you plan to localize)
function uab_load_textdomain() {
    load_plugin_textdomain('unique-auction-bidding', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'uab_load_textdomain');