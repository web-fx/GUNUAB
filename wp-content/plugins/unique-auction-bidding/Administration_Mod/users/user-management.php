<?php
if (!defined('ABSPATH')) exit;

// Debug file load with unique identifier
error_log('user-management.php loaded at ' . current_time('mysql') . ' in ' . __FILE__ . ' - Version Check: 2025-06-14-1');

class UserManagement {
    public static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
            error_log('UserManagement instance created at ' . current_time('mysql') . ' in ' . __FILE__);
        }
        return self::$instance;
    }

    private function __construct() {
        // Debug class initialization with runtime check
        $runtime_check = file_put_contents(__FILE__ . '.runtime-' . time(), 'Runtime check at ' . current_time('mysql') . ' - PID: ' . getmypid());
        if ($runtime_check === false) {
            error_log('UserManagement runtime write test failed at ' . current_time('mysql') . ' in ' . __FILE__ . ' - Permissions: ' . substr(sprintf('%o', fileperms(__FILE__)), -4));
        } else {
            unlink(__FILE__ . '.runtime-' . time());
            error_log('UserManagement runtime write test succeeded at ' . current_time('mysql') . ' in ' . __FILE__ . ' - PID: ' . getmypid());
        }
        error_log('UserManagement class initialized at ' . current_time('mysql') . ' in ' . __FILE__ . ' - PID: ' . getmypid());

        // WordPress and WooCommerce hooks
        add_action('init', array($this, 'register_hooks'));
        add_action('user_register', array($this, 'save_user_type'), 10, 1);
        add_filter('registration_errors', array($this, 'validate_registration'), 10, 3);
    }

    public static function init() {
        self::get_instance();
    }

    public function register_hooks() {
        // No form display hooks needed here; handled by form-login.php
    }

    public function validate_registration($errors, $sanitized_user_login, $user_email) {
        if (!empty($_POST['user_type']) && !in_array($_POST['user_type'], ['bidder', 'advertiser', 'both'])) {
            $errors->add('user_type_error', __('Invalid membership type selected.', 'unique-auction-bidding'));
        }
        $is_promotion_active = get_option('uab_promotion_active', false);
        if ($is_promotion_active && in_array($_POST['user_type'], ['bidder', 'both'])) {
            if (empty($_POST['registration_code'])) {
                $errors->add('code_error', __('Award Code is required for this promotion.', 'unique-auction-bidding'));
            } elseif ($_POST['registration_code'] !== 'VALIDCODE123') {
                $errors->add('code_error', __('Invalid Award Code.', 'unique-auction-bidding'));
            }
        }
        return $errors;
    }

    public function save_user_type($user_id) {
        if (!empty($_POST['user_type']) && in_array($_POST['user_type'], ['bidder', 'advertiser', 'both'])) {
            update_user_meta($user_id, 'user_type', sanitize_text_field($_POST['user_type']));
            $role = $_POST['user_type'] === 'both' ? ['bidder', 'advertiser'] : [$_POST['user_type']];
            $user = new WP_User($user_id);
            $user->set_role($role[0]);
            if (count($role) > 1) $user->add_role($role[1]);
        }
        $is_promotion_active = get_option('uab_promotion_active', false);
        if ($is_promotion_active && !empty($_POST['registration_code']) && $_POST['registration_code'] === 'VALIDCODE123' && in_array($_POST['user_type'], ['bidder', 'both'])) {
            update_user_meta($user_id, 'wallet_balance', 5); // Default $5.00 deposit
            error_log('Wallet deposit of $5.00 added for user ' . $user_id);
        }
    }
}
UserManagement::init();