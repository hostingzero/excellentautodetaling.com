<?php
/*
Plugin Name: BookingPress - Cart Addon
Description: Extension for BookingPress plugin to add cart feature for appointment booking
Version: 2.9
Requires at least: 5.0
Requires PHP:      5.6
Plugin URI: https://www.bookingpressplugin.com/
Author: Repute InfoSystems
Author URI: https://www.bookingpressplugin.com/
Text Domain: bookingpress-cart
Domain Path: /languages
*/

define('BOOKINGPRESS_CART_DIR_NAME', 'bookingpress-cart');
define('BOOKINGPRESS_CART_DIR', WP_PLUGIN_DIR . '/' . BOOKINGPRESS_CART_DIR_NAME);

if (file_exists( BOOKINGPRESS_CART_DIR . '/autoload.php')) {
    require_once BOOKINGPRESS_CART_DIR . '/autoload.php';
}