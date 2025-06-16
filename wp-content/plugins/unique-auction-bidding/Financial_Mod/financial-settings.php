<?php
if (!defined('ABSPATH')) exit;

// Include Stripe PHP library
require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';
use Stripe\Stripe;
use Stripe\Token;
use Stripe\Charge;

function uab_register_settings() {
    // Register settings for both test and live modes
    register_setting('uab_financial_settings_test', 'uab_stripe_test_options', array('sanitize_callback' => 'uab_sanitize_stripe_options'));
    register_setting('uab_financial_settings_live', 'uab_stripe_live_options', array('sanitize_callback' => 'uab_sanitize_stripe_options'));
    add_settings_section('uab_stripe_section', 'Stripe Payment Gateway', 'uab_stripe_section_callback', 'uab-financial');
    add_settings_field('uab_stripe_mode', 'Mode', 'uab_stripe_field_callback', 'uab-financial', 'uab_stripe_section', array('id' => 'uab_stripe_mode'));
    add_settings_field('uab_stripe_test_panel', 'Test Mode Settings', 'uab_stripe_field_callback', 'uab-financial', 'uab_stripe_section', array('id' => 'uab_stripe_test_panel'));
    add_settings_field('uab_stripe_live_panel', 'Live Mode Settings', 'uab_stripe_field_callback', 'uab-financial', 'uab_stripe_section', array('id' => 'uab_stripe_live_panel'));
    add_settings_field('uab_stripe_webhook_url', 'Webhook URL', 'uab_stripe_field_callback', 'uab-financial', 'uab_stripe_section', array('id' => 'uab_stripe_webhook_url'));
    add_settings_field('uab_stripe_enabled_events', 'Enabled Events', 'uab_stripe_field_callback', 'uab-financial', 'uab_stripe_section', array('id' => 'uab_stripe_enabled_events'));
    add_settings_field('uab_stripe_credit_card', 'Credit Card Test Form', 'uab_stripe_field_callback', 'uab-financial', 'uab_stripe_section', array('id' => 'uab_stripe_credit_card'));
    add_settings_field('uab_stripe_wallet_deposit', 'Virtual Wallet Deposit', 'uab_stripe_field_callback', 'uab-financial', 'uab_stripe_section', array('id' => 'uab_stripe_wallet_deposit'));
}

function uab_sanitize_stripe_options($input) {
    $output = array();
    $mode = isset($_POST['uab_stripe_mode']) ? sanitize_text_field($_POST['uab_stripe_mode']) : 'test';
    $option_name = ($mode === 'test') ? 'uab_stripe_test_options' : 'uab_stripe_live_options';
    $existing_options = get_option($option_name, array());

    // Filter input based on mode to prevent cross-contamination
    $mode_fields = ($mode === 'test') 
        ? ['uab_stripe_test_publishable_key', 'uab_stripe_test_secret_key', 'uab_stripe_test_webhook_signing_secret']
        : ['uab_stripe_live_publishable_key', 'uab_stripe_live_secret_key', 'uab_stripe_live_webhook_signing_secret'];
    foreach ($input as $key => $value) {
        if (in_array($key, $mode_fields) || in_array($key, ['uab_stripe_webhook_url', 'uab_stripe_enabled_events', 'uab_stripe_card_number', 'uab_stripe_card_expiry', 'uab_stripe_card_cvc', 'uab_stripe_card_name', 'uab_stripe_wallet_deposit'])) {
            if (in_array($key, ['uab_stripe_test_secret_key', 'uab_stripe_live_secret_key', 'uab_stripe_test_webhook_signing_secret', 'uab_stripe_live_webhook_signing_secret'])) {
                $output[$key] = !empty($value) ? sanitize_text_field($value) : $existing_options[$key] ?? '';
            } elseif ($key === 'uab_stripe_wallet_deposit') {
                $output[$key] = is_numeric($value) && $value > 0 ? floatval($value) : $existing_options[$key] ?? 0;
            } else {
                $output[$key] = sanitize_text_field($value);
            }
        }
    }
    // Handle multi-select array
    if (isset($input['uab_stripe_enabled_events']) && is_array($input['uab_stripe_enabled_events'])) {
        $output['uab_stripe_enabled_events'] = array_map('sanitize_text_field', $input['uab_stripe_enabled_events']);
    }
    return $output;
}

function uab_stripe_section_callback() {
    echo '<p>Configure your Stripe payment gateway settings. Switch between Test and Live modes to manage respective credentials.</p>';
    // Enqueue JavaScript for dynamic field toggling and credit card form
    wp_enqueue_script('uab-stripe-settings', plugin_dir_url(__FILE__) . '../Administration_Mod/Control_Mod/scripts/stripe-settings.js', array('jquery'), '1.0', true);
    wp_localize_script('uab-stripe-settings', 'uab_stripe', array(
        'nonce' => wp_create_nonce('uab_stripe_settings')
    ));
}

function uab_stripe_field_callback($args) {
    $test_options = get_option('uab_stripe_test_options', array());
    $live_options = get_option('uab_stripe_live_options', array());
    $mode = isset($_POST['uab_stripe_mode']) ? sanitize_text_field($_POST['uab_stripe_mode']) : (isset($test_options['uab_stripe_mode']) ? $test_options['uab_stripe_mode'] : (isset($live_options['uab_stripe_mode']) ? $live_options['uab_stripe_mode'] : 'test'));
    $options = ($mode === 'test') ? $test_options : $live_options;
    $saved_card = isset($options['uab_stripe_saved_card']) ? $options['uab_stripe_saved_card'] : null;
    $wallet_balance = isset($options['uab_stripe_wallet_balance']) ? $options['uab_stripe_wallet_balance'] : 0;
    $id = $args['id'];
    $value = isset($options[$id]) ? $options[$id] : '';
    switch ($id) {
        case 'uab_stripe_mode':
            echo '<input type="radio" name="uab_stripe_mode" value="test" ' . checked('test', $mode, false) . ' id="uab_stripe_mode_test"> <label for="uab_stripe_mode_test">Test Mode</label>';
            echo '<input type="radio" name="uab_stripe_mode" value="live" ' . checked('live', $mode, false) . ' id="uab_stripe_mode_live"> <label for="uab_stripe_mode_live">Live Mode</label>';
            echo '<p class="description">Select the mode to configure and use for Stripe API calls.</p>';
            break;
        case 'uab_stripe_test_panel':
            echo '<div id="uab_stripe_test_panel" ' . ($mode === 'live' ? 'style="display:none;"' : '') . '>';
            echo '<label for="uab_stripe_test_publishable_key">Test Publishable Key</label>';
            echo '<input type="text" name="uab_stripe_test_options[uab_stripe_test_publishable_key]" id="uab_stripe_test_publishable_key" value="' . esc_attr($test_options['uab_stripe_test_publishable_key'] ?? '') . '" class="regular-text" />';
            echo '<p class="description">Enter your Stripe Test Publishable Key (keep secure).</p>';
            echo '<label for="uab_stripe_test_secret_key">Test Secret Key</label>';
            echo '<input type="password" name="uab_stripe_test_options[uab_stripe_test_secret_key]" id="uab_stripe_test_secret_key" value="" class="regular-text" autocomplete="new-password" />';
            echo '<p class="description">Enter your Stripe Test Secret Key (will be stored as plain text for testing, leave blank to keep existing).</p>';
            echo '<label for="uab_stripe_test_webhook_signing_secret">Test Webhook Signing Secret</label>';
            echo '<input type="password" name="uab_stripe_test_options[uab_stripe_test_webhook_signing_secret]" id="uab_stripe_test_webhook_signing_secret" value="" class="regular-text" autocomplete="new-password" />';
            echo '<p class="description">Enter your Stripe Test Webhook Signing Secret (will be stored as plain text for testing, leave blank to keep existing).</p>';
            echo '</div>';
            break;
        case 'uab_stripe_live_panel':
            echo '<div id="uab_stripe_live_panel" ' . ($mode === 'test' ? 'style="display:none;"' : '') . '>';
            echo '<label for="uab_stripe_live_publishable_key">Live Publishable Key</label>';
            echo '<input type="text" name="uab_stripe_live_options[uab_stripe_live_publishable_key]" id="uab_stripe_live_publishable_key" value="' . esc_attr($live_options['uab_stripe_live_publishable_key'] ?? '') . '" class="regular-text" />';
            echo '<p class="description">Enter your Stripe Live Publishable Key (keep secure).</p>';
            echo '<label for="uab_stripe_live_secret_key">Live Secret Key</label>';
            echo '<input type="password" name="uab_stripe_live_options[uab_stripe_live_secret_key]" id="uab_stripe_live_secret_key" value="" class="regular-text" autocomplete="new-password" />';
            echo '<p class="description">Enter your Stripe Live Secret Key (will be stored as plain text for testing, leave blank to keep existing).</p>';
            echo '<label for="uab_stripe_live_webhook_signing_secret">Live Webhook Signing Secret</label>';
            echo '<input type="password" name="uab_stripe_live_options[uab_stripe_live_webhook_signing_secret]" id="uab_stripe_live_webhook_signing_secret" value="" class="regular-text" autocomplete="new-password" />';
            echo '<p class="description">Enter your Stripe Live Webhook Signing Secret (will be stored as plain text for testing, leave blank to keep existing).</p>';
            echo '</div>';
            break;
        case 'uab_stripe_webhook_url':
            echo '<input type="text" name="uab_stripe_' . $mode . '_options[uab_stripe_webhook_url]" id="uab_stripe_webhook_url" value="' . esc_attr($value) . '" class="regular-text" />';
            echo '<p class="description">Enter your Stripe Webhook URL (shared for both modes, e.g., https://yourdomain.com/wp-json/uab/v1/webhook).</p>';
            break;
        case 'uab_stripe_enabled_events':
            $events = array(
                'charge.failed' => 'Charge Failed',
                'charge.succeeded' => 'Charge Succeeded',
                'customer.created' => 'Customer Created',
                'customer.updated' => 'Customer Updated',
                'payment_intent.canceled' => 'Payment Intent Canceled',
                'payment_intent.created' => 'Payment Intent Created',
                'payment_intent.payment_failed' => 'Payment Intent Payment Failed',
                'payment_intent.processing' => 'Payment Intent Processing',
                'payment_intent.requires_action' => 'Payment Intent Requires Action',
                'payment_intent.succeeded' => 'Payment Intent Succeeded',
                'payment_link.created' => 'Payment Link Created',
                'payment_link.updated' => 'Payment Link Updated',
                'payment_method.attached' => 'Payment Method Attached',
                'payment_method.automatically_updated' => 'Payment Method Automatically Updated',
                'payment_method.card_automatically_updated' => 'Payment Method Card Automatically Updated',
                'payment_method.detached' => 'Payment Method Detached',
                'payment_method.updated' => 'Payment Method Updated'
            );
            $selected_events = isset($value) ? (array)$value : array();
            echo '<select multiple name="uab_stripe_' . $mode . '_options[uab_stripe_enabled_events][]" id="uab_stripe_enabled_events" style="width: 100%;">';
            foreach ($events as $event_key => $event_label) {
                $selected = in_array($event_key, $selected_events) ? 'selected' : '';
                echo '<option value="' . esc_attr($event_key) . '" ' . $selected . '>' . esc_html($event_label) . '</option>';
            }
            echo '</select>';
            echo '<p class="description">Select the Stripe events to enable for webhooks (saved selections persist).</p>';
            break;
        case 'uab_stripe_credit_card':
            echo '<div>';
            echo '<label for="uab_stripe_card_number">Card Number</label>';
            echo '<input type="text" name="uab_stripe_' . $mode . '_options[uab_stripe_card_number]" id="uab_stripe_card_number" value="" class="regular-text" placeholder="e.g., 4242 4242 4242 4242 (test card)" />';
            echo '<p class="description">Enter a test card number (e.g., 4242 4242 4242 4242 for successful test).</p>';
            echo '<label for="uab_stripe_card_expiry">Expiry Date</label>';
            echo '<input type="text" name="uab_stripe_' . $mode . '_options[uab_stripe_card_expiry]" id="uab_stripe_card_expiry" value="" class="regular-text" placeholder="MM/YY e.g., 12/25" />';
            echo '<p class="description">Enter expiry date (e.g., 12/25).</p>';
            echo '<label for="uab_stripe_card_cvc">CVC</label>';
            echo '<input type="text" name="uab_stripe_' . $mode . '_options[uab_stripe_card_cvc]" id="uab_stripe_card_cvc" value="" class="regular-text" placeholder="e.g., 123" />';
            echo '<p class="description">Enter CVC (e.g., 123).</p>';
            echo '<label for="uab_stripe_card_name">Cardholder Name</label>';
            echo '<input type="text" name="uab_stripe_' . $mode . '_options[uab_stripe_card_name]" id="uab_stripe_card_name" value="" class="regular-text" placeholder="e.g., John Doe" />';
            echo '<p class="description">Enter cardholder name for testing.</p>';
            echo '<button type="button" id="uab_stripe_save_card">Save Card</button>';
            echo '<p id="uab_stripe_card_status" style="color: green;"></p>';
            if ($saved_card) {
                echo '<p>Saved Card: ' . esc_html($saved_card['number']) . ', Exp: ' . esc_html($saved_card['expiry']) . ', Name: ' . esc_html($saved_card['name']) . '</p>';
            }
            echo '</div>';
            break;
        case 'uab_stripe_wallet_deposit':
            echo '<div>';
            echo '<label for="uab_stripe_wallet_deposit_amount">Deposit Amount</label>';
            echo '<input type="number" name="uab_stripe_' . $mode . '_options[uab_stripe_wallet_deposit]" id="uab_stripe_wallet_deposit_amount" value="" class="regular-text" min="0" step="0.01" />';
            echo '<p class="description">Enter amount to deposit into virtual wallet (e.g., 100.00).</p>';
            echo '<button type="button" id="uab_stripe_deposit_funds">Deposit Funds</button>';
            echo '<p>Current Virtual Wallet Balance: <strong>$' . number_format($wallet_balance, 2) . '</strong></p>';
            echo '<p id="uab_stripe_deposit_status" style="color: green;"></p>';
            echo '</div>';
            break;
    }
}

function uab_financial_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    // Set Stripe API key based on mode
    $test_options = get_option('uab_stripe_test_options', array());
    $live_options = get_option('uab_stripe_live_options', array());
    $mode = isset($_POST['uab_stripe_mode']) ? sanitize_text_field($_POST['uab_stripe_mode']) : (isset($test_options['uab_stripe_mode']) ? $test_options['uab_stripe_mode'] : (isset($live_options['uab_stripe_mode']) ? $live_options['uab_stripe_mode'] : 'test'));
    $secret_key = ($mode === 'test') ? $test_options['uab_stripe_test_secret_key'] : $live_options['uab_stripe_live_secret_key'];
    if (!empty($secret_key)) {
        Stripe::setApiKey($secret_key); // Use plain text secret key
    }
    echo '<div class="wrap"><h1>Financial</h1>';
    echo '<form method="post" action="options.php" id="uab_stripe_form">';
    echo '<input type="hidden" name="uab_stripe_mode" id="uab_stripe_mode_hidden" value="' . esc_attr($mode) . '">';
    settings_fields('uab_financial_settings_' . $mode); // Dynamic settings group
    do_settings_sections('uab-financial');
    submit_button();
    echo '</form>';

    // Enqueue JavaScript for card saving and deposit
    wp_enqueue_script('uab-stripe-settings', plugin_dir_url(__FILE__) . '../Administration_Mod/Control_Mod/scripts/stripe-settings.js', array('jquery'), '1.0', true);
    wp_localize_script('uab-stripe-settings', 'uab_stripe', array(
        'nonce' => wp_create_nonce('uab_stripe_settings')
    ));
    wp_enqueue_script('uab-stripe-test', plugin_dir_url(__FILE__) . '../Administration_Mod/Control_Mod/scripts/stripe-test.js', array('jquery'), '1.0', true);
    wp_localize_script('uab-stripe-test', 'uab_stripe_test', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('uab_stripe_test'),
        'mode' => $mode
    ));

    // Verify script loading
    if (wp_script_is('uab-stripe-test', 'enqueued')) {
        error_log('uab-stripe-test script enqueued successfully with data: ' . print_r(array('ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('uab_stripe_test'), 'mode' => $mode), true));
    } else {
        error_log('uab-stripe-test script failed to enqueue');
    }
}

// Add AJAX handlers for testing
add_action('wp_ajax_uab_stripe_save_card', 'uab_stripe_save_card');
add_action('wp_ajax_uab_stripe_deposit_funds', 'uab_stripe_deposit_funds');

function uab_stripe_save_card() {
    check_ajax_referer('uab_stripe_test', 'nonce');
    $mode = $_POST['mode'];
    $options = ($mode === 'test') ? get_option('uab_stripe_test_options', array()) : get_option('uab_stripe_live_options', array());
    $secret_key = ($mode === 'test') ? $options['uab_stripe_test_secret_key'] : $options['uab_stripe_live_secret_key'];
    Stripe::setApiKey($secret_key); // Use plain text secret key

    $card_number = sanitize_text_field($_POST['card_number']);
    $expiry = sanitize_text_field($_POST['expiry']);
    $cvc = sanitize_text_field($_POST['cvc']);
    $name = sanitize_text_field($_POST['name']);

    error_log('Save Card Attempt - Mode: ' . $mode . ', Secret Key: ' . $secret_key . ', Card Data: ' . $card_number . ', ' . $expiry . ', ' . $cvc . ', ' . $name); // Debug
    if (empty($secret_key)) {
        wp_send_json_error('No secret key configured for ' . $mode . ' mode.');
        return;
    }

    // Validate card data
    if (!preg_match('/^\d{16}$/', $card_number) || !preg_match('/^\d{2}\/\d{2}$/', $expiry) || !preg_match('/^\d{3,4}$/', $cvc) || empty($name)) {
        wp_send_json_error('Invalid card details. Ensure correct format (e.g., 4242424242424242, 12/25, 123).');
        return;
    }

    // Create a Stripe token
    try {
        $token = Token::create(array(
            'card' => array(
                'number' => $card_number,
                'exp_month' => substr($expiry, 0, 2),
                'exp_year' => '20' . substr($expiry, -2),
                'cvc' => $cvc,
                'name' => $name
            )
        ));
        error_log('Stripe Token Created: ' . $token->id); // Debug log
        if ($token->id) {
            $options['uab_stripe_saved_card'] = array(
                'token' => $token->id,
                'number' => '****-****-****-' . substr($card_number, -4), // Mask for security
                'expiry' => $expiry,
                'cvc' => '***',
                'name' => $name
            );
            $option_name = ($mode === 'test') ? 'uab_stripe_test_options' : 'uab_stripe_live_options';
            update_option($option_name, $options);
            wp_send_json_success('Card tokenized and saved successfully with Stripe in ' . $mode . ' mode.');
        }
    } catch (\Stripe\Exception\CardException $e) {
        error_log('Stripe Card Error: ' . $e->getError()->message); // Debug log
        wp_send_json_error('Card error: ' . $e->getError()->message);
    } catch (\Exception $e) {
        error_log('Stripe General Error: ' . $e->getMessage()); // Debug log
        wp_send_json_error('Error: ' . $e->getMessage());
    }
}

function uab_stripe_deposit_funds() {
    check_ajax_referer('uab_stripe_test', 'nonce');
    $mode = $_POST['mode'];
    $options = ($mode === 'test') ? get_option('uab_stripe_test_options', array()) : get_option('uab_stripe_live_options', array());
    $secret_key = ($mode === 'test') ? $options['uab_stripe_test_secret_key'] : $options['uab_stripe_live_secret_key'];
    Stripe::setApiKey($secret_key); // Use plain text secret key

    $deposit_amount = floatval($_POST['deposit_amount']);
    $saved_card = $options['uab_stripe_saved_card'] ?? null;

    error_log('Deposit Attempt - Mode: ' . $mode . ', Secret Key: ' . $secret_key . ', Amount: ' . $deposit_amount . ', Saved Card: ' . print_r($saved_card, true)); // Debug
    if (empty($secret_key)) {
        wp_send_json_error('No secret key configured for ' . $mode . ' mode.');
        return;
    }
    if ($deposit_amount <= 0) {
        wp_send_json_error('Invalid deposit amount.');
        return;
    }
    if (!$saved_card || !isset($saved_card['token'])) {
        wp_send_json_error('No saved card available or token missing.');
        return;
    }

    try {
        // Create a charge using the saved token
        $charge = Charge::create(array(
            'amount' => $deposit_amount * 100, // Amount in cents
            'currency' => 'usd',
            'source' => $saved_card['token'],
            'description' => 'Test deposit to virtual wallet'
        ));
        error_log('Stripe Charge Response: ' . print_r($charge, true)); // Debug log
        if ($charge->status === 'succeeded') {
            $current_balance = floatval($options['uab_stripe_wallet_balance'] ?? 0);
            $options['uab_stripe_wallet_balance'] = $current_balance + $deposit_amount;
            $option_name = ($mode === 'test') ? 'uab_stripe_test_options' : 'uab_stripe_live_options';
            update_option($option_name, $options);
            wp_send_json_success('Funds deposited via Stripe. New balance: $' . number_format($options['uab_stripe_wallet_balance'], 2));
        } else {
            wp_send_json_error('Charge failed: ' . $charge->status);
        }
    } catch (\Stripe\Exception\CardException $e) {
        error_log('Stripe Card Error: ' . $e->getError()->message); // Debug log
        wp_send_json_error('Card error: ' . $e->getError()->message);
    } catch (\Exception $e) {
        error_log('Stripe General Error: ' . $e->getMessage()); // Debug log
        wp_send_json_error('Error: ' . $e->getMessage());
    }
}

// Add JavaScript for dynamic field toggling and test features
file_put_contents(plugin_dir_path(__FILE__) . '../Administration_Mod/Control_Mod/scripts/stripe-settings.js', 
    "jQuery(document).ready(function($) {
        function toggleStripeFields() {
            var mode = $('input[name=\"uab_stripe_mode\"]:checked').val() || 'test';
            if (mode === 'test') {
                $('#uab_stripe_test_panel').show();
                $('#uab_stripe_live_panel').hide();
                $('#uab_stripe_form').attr('action', 'options.php');
                $('#uab_stripe_form').attr('name', 'uab_financial_settings_test');
                $('#uab_stripe_mode_hidden').val('test');
            } else {
                $('#uab_stripe_test_panel').hide();
                $('#uab_stripe_live_panel').show();
                $('#uab_stripe_form').attr('action', 'options.php');
                $('#uab_stripe_form').attr('name', 'uab_financial_settings_live');
                $('#uab_stripe_mode_hidden').val('live');
            }
        }

        // Initial toggle based on saved or default mode
        toggleStripeFields();

        // Toggle on mode change
        $('input[name=\"uab_stripe_mode\"]').on('change', function() {
            toggleStripeFields();
            $('#uab_stripe_form').submit(); // Submit form to save the mode change
        });
    });"
);

file_put_contents(plugin_dir_path(__FILE__) . '../Administration_Mod/Control_Mod/scripts/stripe-test.js', 
    "jQuery(document).ready(function($) {
        console.log('Stripe Test JS Loaded'); // Debug
        if (typeof uab_stripe_test === 'undefined') {
            console.error('uab_stripe_test object not defined. Check wp_localize_script.');
            return;
        }
        console.log('uab_stripe_test Data: ', uab_stripe_test); // Debug the localized data
        if ($('#uab_stripe_save_card').length) {
            console.log('Save Card button found'); // Debug
            $('#uab_stripe_save_card').on('click', function() {
                console.log('Save Card Clicked'); // Debug
                var card_number = $('#uab_stripe_card_number').val();
                var expiry = $('#uab_stripe_card_expiry').val();
                var cvc = $('#uab_stripe_card_cvc').val();
                var name = $('#uab_stripe_card_name').val();
                var mode = $('input[name=\"uab_stripe_mode\"]:checked').val() || 'test';

                console.log('Card Data: ' + card_number + ', ' + expiry + ', ' + cvc + ', ' + name + ', Mode: ' + mode); // Debug
                if (!card_number || !expiry || !cvc || !name) {
                    $('#uab_stripe_card_status').text('All fields are required.').css('color', 'red').show();
                    return;
                }

                $.ajax({
                    url: uab_stripe_test.ajax_url,
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'uab_stripe_save_card',
                        nonce: uab_stripe_test.nonce,
                        mode: mode,
                        card_number: card_number,
                        expiry: expiry,
                        cvc: cvc,
                        name: name
                    },
                    beforeSend: function(xhr) {
                        console.log('AJAX Request Starting with Nonce: ' + uab_stripe_test.nonce); // Debug
                        xhr.setRequestHeader('X-WP-Nonce', uab_stripe_test.nonce); // Explicitly set nonce
                    },
                    success: function(response) {
                        console.log('AJAX Success: ' + JSON.stringify(response)); // Debug
                        if (response.success) {
                            $('#uab_stripe_card_status').text(response.data).show();
                        } else {
                            $('#uab_stripe_card_status').text(response.data).css('color', 'red').show();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('AJAX Error: ' + status + ' - ' + error + ', Response: ' + xhr.responseText); // Debug
                        $('#uab_stripe_card_status').text('Error saving card: ' + status + ' - ' + error + (xhr.responseText ? ' - ' + xhr.responseText : '')).css('color', 'red').show();
                    }
                });
            });
        } else {
            console.log('Save Card button not found in DOM'); // Debug
        }

        $('#uab_stripe_deposit_funds').on('click', function() {
            console.log('Deposit Funds Clicked'); // Debug
            var deposit_amount = $('#uab_stripe_wallet_deposit_amount').val();
            var mode = $('input[name=\"uab_stripe_mode\"]:checked').val() || 'test';

            console.log('Deposit Data: ' + deposit_amount + ', Mode: ' + mode); // Debug
            if (!deposit_amount || deposit_amount <= 0) {
                $('#uab_stripe_deposit_status').text('Valid deposit amount required.').css('color', 'red').show();
                return;
            }

            $.ajax({
                url: uab_stripe_test.ajax_url,
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'uab_stripe_deposit_funds',
                    nonce: uab_stripe_test.nonce,
                    mode: mode,
                    deposit_amount: deposit_amount
                },
                beforeSend: function(xhr) {
                    console.log('AJAX Request Starting with Nonce: ' + uab_stripe_test.nonce); // Debug
                    xhr.setRequestHeader('X-WP-Nonce', uab_stripe_test.nonce); // Explicitly set nonce
                },
                success: function(response) {
                    console.log('AJAX Success: ' + JSON.stringify(response)); // Debug
                    if (response.success) {
                        $('#uab_stripe_deposit_status').text(response.data).show();
                        // Refresh the page to update wallet balance display
                        location.reload();
                    } else {
                        $('#uab_stripe_deposit_status').text(response.data).css('color', 'red').show();
                    }
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error: ' + status + ' - ' + error + ', Response: ' + xhr.responseText); // Debug
                    $('#uab_stripe_deposit_status').text('Error depositing funds: ' + status + ' - ' + error + (xhr.responseText ? ' - ' + xhr.responseText : '')).css('color', 'red').show();
                }
            });
        });
    });"
);