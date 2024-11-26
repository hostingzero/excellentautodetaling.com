<?php
/*
Plugin Name: BookingPress - Recurring Appointments Addon
Description: An extension to easily book a series of appointments in few clicks
Version: 1.4
Requires at least: 5.0
Requires PHP:      5.6
Plugin URI: https://www.bookingpressplugin.com/
Author: Repute InfoSystems
Author URI: https://www.bookingpressplugin.com/
Text Domain: bookingpress-recurring-appointments
Domain Path: /languages
*/

define('RECURRING_APPOINTMENTS_LIST_DIR_NAME', 'bookingpress-recurring-appointments');
define('RECURRING_APPOINTMENTS_LIST_DIR', WP_PLUGIN_DIR . '/' . RECURRING_APPOINTMENTS_LIST_DIR_NAME);
define('RECURRING_APPOINTMENTS_VIEW_DIR', WP_PLUGIN_DIR . '/' . RECURRING_APPOINTMENTS_LIST_DIR_NAME.'/core/views/');
 
global $wpdb;

if(file_exists(RECURRING_APPOINTMENTS_LIST_DIR . '/autoload.php')) {
    require_once RECURRING_APPOINTMENTS_LIST_DIR . '/autoload.php';
}