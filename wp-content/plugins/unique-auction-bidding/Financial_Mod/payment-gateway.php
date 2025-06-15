<?php
if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php'; // Use relative path to vendor/autoload.php

class UniqueAuctionPaymentGateway {
    private $api_key;
    private $publishable_key;

    public function __construct() {
        $this->api_key = 'sk_test_your_key_here'; // Replace with your Stripe secret key
        $this->publishable_key = 'pk_test_your_key_here'; // Replace with your Stripe publishable key
        add_action('init', array($this, 'setup_gateway'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('user_register', array($this, 'process_registration_payment'), 10, 1);
    }

    public function setup_gateway() {
        error_log('Stripe gateway initialized at ' . current_time('mysql'));
    }

    public function enqueue_scripts() {
        wp_enqueue_script('stripe-js', 'https://js.stripe.com/v3/', array(), null, true);
        wp_enqueue_script('uab-stripe-custom', get_template_directory_uri() . '/js/stripe-custom.js', array('stripe-js'), null, true);
        wp_localize_script('uab-stripe-custom', 'uabStripeParams', array(
            'publishableKey' => $this->publishable_key,
        ));
        error_log('Stripe scripts enqueued at ' . current_time('mysql'));
    }

    public function process_registration_payment($user_id) {
        if (!empty($_POST['registration_code']) && $_POST['registration_code'] === 'VALIDCODE123') {
            error_log('Processing payment for user ' . $user_id);
            \Stripe\Stripe::setApiKey($this->api_key);

            try {
                $amount = 5 * 100; // $5.00 in cents
                if (!empty($_POST['stripeToken'])) {
                    $charge = \Stripe\Charge::create([
                        'amount' => $amount,
                        'currency' => 'usd',
                        'source' => $_POST['stripeToken'],
                        'description' => 'Registration Wallet Deposit for User #' . $user_id,
                    ]);
                    update_user_meta($user_id, 'wallet_balance', 5);
                    error_log('Stripe charge successful for user ' . $user_id . ': ' . $charge->id);
                } else {
                    // Fallback to manual deposit for testing
                    update_user_meta($user_id, 'wallet_balance', 5);
                    error_log('Manual wallet deposit of $5.00 for user ' . $user_id . ' (no Stripe token)');
                }
            } catch (\Stripe\Exception\ApiErrorException $e) {
                error_log('Stripe charge failed for user ' . $user_id . ': ' . $e->getMessage());
            }
        }
    }
}

new UniqueAuctionPaymentGateway();