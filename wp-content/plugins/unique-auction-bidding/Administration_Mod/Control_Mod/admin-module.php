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
        error_log('add_uab_settings_tab called in ' . __FILE__);
        $tabs[UAB_WC_TAB_ID] = __('UAB Settings', 'unique-auction-bidding');
        return $tabs;
    }

    public function render_uab_settings_tab() {
        error_log('render_uab_settings_tab called in ' . __FILE__);
        woocommerce_admin_fields($this->get_uab_settings());
    }

    public function save_uab_settings() {
        error_log('save_uab_settings called in ' . __FILE__);
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
            'user_types' => array(
                'name' => __('Enable User Types', 'unique-auction-bidding'),
                'type' => 'multicheck',
                'desc' => __('Select the user types to enable for the auction system.', 'unique-auction-bidding'),
                'id'   => 'uab_user_types',
                'options' => array(
                    'advertiser' => __('Advertiser', 'unique-auction-bidding'),
                    'bidder' => __('Bidder', 'unique-auction-bidding'),
                    'bidvertiser' => __('Bidvertiser', 'unique-auction-bidding')
                ),
                'default' => array('advertiser', 'bidder')
            ),
            'section_end' => array(
                'type' => 'sectionend',
                'id'   => 'uab_settings_section_end'
            )
        );
        return apply_filters('uab_settings', $settings);
    }
}