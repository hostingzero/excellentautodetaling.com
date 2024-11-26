<?php
/*
Plugin Name: BookingPress - Stripe Payment Gateway Addon
Description: Extension for BookingPress plugin to accept payments using Stripe Payment Gateway.
Version: 1.9
Requires at least: 5.0
Requires PHP:      5.6
Plugin URI: https://www.bookingpressplugin.com/
Author: Repute InfoSystems
Author URI: https://www.bookingpressplugin.com/
Text Domain: bookingpress-stripe
Domain Path: /languages
*/

define('BOOKINGPRESS_STRIPE_DIR_NAME', 'bookingpress-stripe');
define('BOOKINGPRESS_STRIPE_DIR', WP_PLUGIN_DIR . '/' . BOOKINGPRESS_STRIPE_DIR_NAME);

if (file_exists( BOOKINGPRESS_STRIPE_DIR . '/autoload.php')) {
    require_once BOOKINGPRESS_STRIPE_DIR . '/autoload.php';
}