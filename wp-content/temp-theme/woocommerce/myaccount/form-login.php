<?php
/**
 * Login Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-login.php.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

do_action( 'woocommerce_before_customer_login_form' ); ?>

<?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>

<div class="u-columns col2-set" id="customer_login">

    <div class="u-column1 col-1">

        <?php endif; ?>

        <h2><?php esc_html_e( 'Login', 'woocommerce' ); ?></h2>

        <form class="woocommerce-form woocommerce-form-login login" method="post" novalidate>

            <?php do_action( 'woocommerce_login_form_start' ); ?>

            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label for="username"><?php esc_html_e( 'Username or email address', 'woocommerce' ); ?> <span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
                <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) && is_string( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required aria-required="true" /><?php // @codingStandardsIgnoreLine ?>
            </p>
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label for="password"><?php esc_html_e( 'Password', 'woocommerce' ); ?> <span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
                <input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" required aria-required="true" />
            </p>

            <?php do_action( 'woocommerce_login_form' ); ?>

            <p class="form-row">
                <label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
                    <input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <span><?php esc_html_e( 'Remember me', 'woocommerce' ); ?></span>
                </label>
                <?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
                <button type="submit" class="woocommerce-button button woocommerce-form-login__submit<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>"><?php esc_html_e( 'Log in', 'woocommerce' ); ?></button>
            </p>
            <p class="woocommerce-LostPassword lost_password">
                <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Lost your password?', 'woocommerce' ); ?></a>
            </p>

            <?php do_action( 'woocommerce_login_form_end' ); ?>

        </form>

        <?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>

    </div>

    <div class="u-column2 col-2">

        <h2><?php esc_html_e( 'Register', 'woocommerce' ); ?></h2>

        <form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?> >

            <?php do_action( 'woocommerce_register_form_start' ); ?>

            <!-- Two-column layout for Email and Password -->
            <div style="display: flex; gap: 20px;">
                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide" style="flex: 1;">
                    <label for="reg_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?> <span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
                    <input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" required aria-required="true" /><?php // @codingStandardsIgnoreLine ?>
                </p>
                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide" style="flex: 1;">
                    <?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
                        <label for="reg_password"><?php esc_html_e( 'Password', 'woocommerce' ); ?> <span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
                        <input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" required aria-required="true" />
                    <?php else : ?>
                <p><?php esc_html_e( 'A link to set a new password will be sent to your email address.', 'woocommerce' ); ?></p>
                <?php endif; ?>
                </p>
            </div>

            <!-- Membership Type selection on same row -->
            <p style="margin-top: 10px;">
                <label style="margin-right: 10px;"><?php _e('Membership Type:', 'unique-auction-bidding'); ?></label>
                <input type="radio" name="user_type" value="bidder" id="user_type_bidder" <?php checked( isset($_POST['user_type']) && $_POST['user_type'] === 'bidder' ); ?> required style="margin-right: 5px;">
                <label for="user_type_bidder" style="margin-right: 15px;"><?php _e('Bidder', 'unique-auction-bidding'); ?></label>
                <input type="radio" name="user_type" value="advertiser" id="user_type_advertiser" <?php checked( isset($_POST['user_type']) && $_POST['user_type'] === 'advertiser' ); ?> style="margin-right: 5px;">
                <label for="user_type_advertiser" style="margin-right: 15px;"><?php _e('Advertiser', 'unique-auction-bidding'); ?></label>
                <input type="radio" name="user_type" value="both" id="user_type_both" <?php checked( isset($_POST['user_type']) && $_POST['user_type'] === 'both' ); ?> style="margin-right: 5px;">
                <label for="user_type_both"><?php _e('Advertiser & Bidder', 'unique-auction-bidding'); ?></label>
            </p>

            <!-- Dynamic Award Code section on same row -->
            <?php $is_promotion_active = get_option('uab_promotion_active', false); ?>
            <div id="award-code-section" style="display: none; margin-top: 10px;">
                <p style="display: inline-block; margin-right: 20px; color: green;"><?php _e('Joining Award: Available.', 'unique-auction-bidding'); ?></p>
                <p style="display: inline-block; border: 1px solid orange; padding: 5px;">
                    <label for="registration_code" style="margin-right: 5px;"><?php _e('Award Code:', 'unique-auction-bidding'); ?> <span class="required" aria-hidden="true">*</span></label>
                    <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="registration_code" id="registration_code" required aria-required="true" <?php echo !$is_promotion_active ? 'disabled' : ''; ?> style="display: inline; width: 150px;" />
                </p>
            </div>

            <!-- Admin-controlled informational field -->
            <?php $info_text = get_option('uab_registration_info', 'Special events or promotions may be announced here.'); ?>
            <p style="font-style: italic; color: #666; margin-top: 10px;">
                <?php echo esc_html($info_text); ?>
            </p>

            <?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>

                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                    <label for="reg_username"><?php esc_html_e( 'Username', 'woocommerce' ); ?> <span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
                    <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required aria-required="true" /><?php // @codingStandardsIgnoreLine ?>
                </p>

            <?php endif; ?>

            <?php do_action( 'woocommerce_register_form' ); ?>

            <p class="woocommerce-form-row form-row">
                <?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
                <button type="submit" class="woocommerce-Button woocommerce-button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?> woocommerce-form-register__submit" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>"><?php esc_html_e( 'Register', 'woocommerce' ); ?></button>
            </p>

            <?php do_action( 'woocommerce_register_form_end' ); ?>

        </form>

    </div>

</div>
<?php endif; ?>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        var radios = document.querySelectorAll('input[name="user_type"]');
        var awardCodeSection = document.getElementById('award-code-section');
        var codeInput = document.getElementById('registration_code');
        var isPromotionActive = <?php echo json_encode(get_option('uab_promotion_active', false)); ?>;

        // Temporary override for testing (remove when admin toggle is built)
        var testMode = true; // Set to false when admin toggle is implemented

        function updateAwardCode() {
            var selectedValue = document.querySelector('input[name="user_type"]:checked') ? document.querySelector('input[name="user_type"]:checked').value : '';
            console.log('Selected value: ' + selectedValue + ', Promotion active: ' + (testMode ? 'true (test mode)' : isPromotionActive)); // Debug
            if (awardCodeSection && codeInput) {
                if ((testMode || isPromotionActive) && (selectedValue === 'bidder' || selectedValue === 'both')) {
                    awardCodeSection.style.display = 'block';
                    codeInput.removeAttribute('disabled'); // Enable input in test mode or when promotion is active
                    console.log('Award Code section displayed and input enabled');
                } else {
                    awardCodeSection.style.display = 'none';
                    codeInput.setAttribute('disabled', 'disabled'); // Disable input when hidden
                    console.log('Award Code section hidden and input disabled');
                }
            } else {
                console.log('Award Code section or input not found');
            }
        }

        radios.forEach(function(radio) {
            radio.addEventListener('change', updateAwardCode);
        });

        // Initial check
        updateAwardCode();
    });
</script>