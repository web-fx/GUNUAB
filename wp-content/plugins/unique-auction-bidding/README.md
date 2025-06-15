# Unique Auction Bidding Plugin

## Overview
This is a custom WordPress plugin for the GunBid platform, rebuilt from scratch to manage user registrations, wallet deposits, and future auction features.

## Current Features
- **Registration Form**:
    - Located in `wp-content/themes/hello-child/woocommerce/myaccount/form-login.php`.
    - Two-column layout for "Email Address" and "Password".
    - Horizontal "Membership Type" radio buttons (Bidder, Advertiser, Advertiser & Bidder).
    - Dynamic "Award Code" section for "Bidder" or "Advertiser & Bidder" with `testMode` active.
    - Informational text placeholder for admin control.
- **User Management**:
    - Located in `wp-content/plugins/unique-auction-bidding/Administration_Mod/users/user-management.php`.
    - Validates user type and award code, sets wallet balance ($5.00) on successful "VALIDCODE123" entry.
- **Theme Functions**:
    - Located in `wp-content/themes/hello-child/functions.php`.
    - Enqueues styles, extends nonce lifetime, and manages product edit capabilities.
- **Payment Gateway**:
    - Located in `wp-content/plugins/unique-auction-bidding/Financial_Mod/payment-gateway.php`.
    - Skeleton for Stripe integration, not yet active.

## Pending Features
- **Wallet Deposit Debug**: Resolve why `wallet_balance` isn’t updating.
- **Admin Toggle**: Build settings page in `Administration_Mod` to control `uab_promotion_active` and deposit/info text.
- **Dashboard**: Add "Credit Card" and "Deposit" fields via a custom endpoint or `my-account.php` override.

## Installation
1. Clone this repository to `wp-content/plugins/unique-auction-bidding/`.
2. Activate the plugin in WordPress admin.
3. Ensure the `hello-child` theme is active and updated.

## File Structure
- `Administration_Mod/users/user-management.php`: User registration and validation logic.
- `Financial_Mod/payment-gateway.php`: Stripe payment gateway skeleton.
- `wp-content/themes/hello-child/functions.php`: Theme-specific functions.
- `wp-content/themes/hello-child/woocommerce/myaccount/form-login.php`: Customized registration form.

## Development Notes
- Use `WP_DEBUG` and `debug.log` for troubleshooting.
- Temporary `testMode = true` in `form-login.php` for development; replace with admin toggle.
- Install Stripe PHP library via Composer (`composer require stripe/stripe-php`).

## Contributors
- [Your Name]

## License
[Specify license, e.g., GPL-2.0]