<?php
// admin-core.php - Core admin logic
if (!defined('ABSPATH')) exit; // Security check

class UniqueAuctionAdmin {
    public function __construct() {
        $this->load_modules();
        add_action('init', array($this, 'register_user_types'));
        error_log('New admin-core.php loaded at ' . current_time('mysql') . ' in ' . __FILE__); // Debug file load
    }

    private function load_modules() {
        $user_management_path = __DIR__ . '/users/user-management.php';
        if (file_exists($user_management_path) && is_readable($user_management_path)) {
            require_once $user_management_path;
            error_log('user-management.php loaded successfully from ' . $user_management_path);
        } else {
            error_log('user-management.php not found or not readable at ' . $user_management_path . ' - Check permissions or path');
        }
    }

    public function register_user_types() {
        add_role('bidder', 'Bidder', array('read' => true));
        add_role('advertiser', 'Advertiser', array('read' => true));
        add_role('bidvertiser', 'Bidvertiser', array('read' => true));
    }
}
new UniqueAuctionAdmin();