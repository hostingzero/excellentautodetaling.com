<?php

define( "BOOKINGPRESS_OUTLOOK_CALENDAR_URL", plugins_url( '', __FILE__ ) );

define( "BOOKINGPRESS_OUTLOOK_CALENDAR_LIBRARY_DIR", BOOKINGPRESS_OUTLOOK_CALENDAR_DIR . '/lib' );

global $bookingpress_outlook_calendar_version;
$bookingpress_outlook_calendar_version = '2.2';
define('BOOKINGPRESS_OUTLOOK_CALENDAR_VERSION', $bookingpress_outlook_calendar_version);

if(file_exists(BOOKINGPRESS_OUTLOOK_CALENDAR_DIR . "/core/classes/class.bookingpress-outlook-calendar.php") ){
	require_once BOOKINGPRESS_OUTLOOK_CALENDAR_DIR . "/core/classes/class.bookingpress-outlook-calendar.php";
}

load_plugin_textdomain( 'bookingpress-outlook-calendar', false, 'bookingpress-outlook-calendar/languages/' );

define( 'BOOKINGPRESS_OUTLOOK_CALENDAR_STORE_URL', 'https://www.bookingpressplugin.com/' );

if ( ! class_exists( 'bookingpress_pro_updater' ) ) {
	require_once BOOKINGPRESS_OUTLOOK_CALENDAR_DIR . '/core/classes/class.bookingpress_pro_plugin_updater.php';
}


function bookingpress_outlook_calendar_plugin_updater() {
	
	$plugin_slug_for_update = 'bookingpress-outlook-calendar/bookingpress-outlook-calendar.php';
	// To support auto-updates, this needs to run during the wp_version_check cron job for privileged users.
	$doing_cron = defined( 'DOING_CRON' ) && DOING_CRON;
	if ( ! current_user_can( 'manage_options' ) && ! $doing_cron ) {
		return;
	}

	// retrieve our license key from the DB
	$license_key = trim( get_option( 'bkp_outlook_calendar_license_key' ) );
	$package = trim( get_option( 'bkp_outlook_calendar_license_package' ) );

	// setup the updater
	$edd_updater = new bookingpress_pro_updater(
		BOOKINGPRESS_OUTLOOK_CALENDAR_STORE_URL,
		$plugin_slug_for_update,
		array(
			'version' => BOOKINGPRESS_OUTLOOK_CALENDAR_VERSION,  // current version number
			'license' => $license_key,             // license key (used get_option above to retrieve from DB)
			'item_id' => $package,       // ID of the product
			'author'  => 'Repute Infosystems', // author of this plugin
			'beta'    => false,
		)
	);

}
add_action( 'init', 'bookingpress_outlook_calendar_plugin_updater' );
