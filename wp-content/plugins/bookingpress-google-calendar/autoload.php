<?php

if (is_ssl()) {
    define('BOOKINGPRESS_GOOGLE_CALENDAR_URL', str_replace('http://', 'https://', WP_PLUGIN_URL . '/' . BOOKINGPRESS_GOOGLE_CALENDAR_DIR_NAME));
} else {
    define('BOOKINGPRESS_GOOGLE_CALENDAR_URL', WP_PLUGIN_URL . '/' . BOOKINGPRESS_GOOGLE_CALENDAR_DIR_NAME);
}

define('BOOKINGPRESS_GOOGLE_CALENDAR_LIBRARY_DIR', BOOKINGPRESS_GOOGLE_CALENDAR_DIR . '/lib');
define('BOOKINGPRESS_GOOGLE_CALENDAR_LIBRARY_URL', BOOKINGPRESS_GOOGLE_CALENDAR_URL . '/lib');

if(file_exists(BOOKINGPRESS_GOOGLE_CALENDAR_DIR . "/core/classes/class.bookingpress-google-calendar.php") ){
	require_once BOOKINGPRESS_GOOGLE_CALENDAR_DIR . "/core/classes/class.bookingpress-google-calendar.php";
}

global $bookingpress_google_calendar_version;
$bookingpress_google_calendar_version = '2.6';
define('BOOKINGPRESS_GOOGLE_CALENDAR_VERSION', $bookingpress_google_calendar_version);

load_plugin_textdomain( 'bookingpress-google-calendar', false, 'bookingpress-google-calendar/languages/' );

define( 'BOOKINGPRESS_GOOGLE_CALENDAR_STORE_URL', 'https://www.bookingpressplugin.com/' );

if ( ! class_exists( 'bookingpress_pro_updater' ) ) {
	require_once BOOKINGPRESS_GOOGLE_CALENDAR_DIR . '/core/classes/class.bookingpress_pro_plugin_updater.php';
}

function bookingpress_google_calendar_plugin_updater() {
	
	$plugin_slug_for_update = 'bookingpress-google-calendar/bookingpress-google-calendar.php';
	// To support auto-updates, this needs to run during the wp_version_check cron job for privileged users.
	$doing_cron = defined( 'DOING_CRON' ) && DOING_CRON;
	if ( ! current_user_can( 'manage_options' ) && ! $doing_cron ) {
		return;
	}

	// retrieve our license key from the DB
	$license_key = trim( get_option( 'bkp_google_calendar_license_key' ) );
	$package = trim( get_option( 'bkp_google_calendar_license_package' ) );

	// setup the updater
	$edd_updater = new bookingpress_pro_updater(
		BOOKINGPRESS_GOOGLE_CALENDAR_STORE_URL,
		$plugin_slug_for_update,
		array(
			'version' => BOOKINGPRESS_GOOGLE_CALENDAR_VERSION,  // current version number
			'license' => $license_key,             // license key (used get_option above to retrieve from DB)
			'item_id' => $package,       // ID of the product
			'author'  => 'Repute Infosystems', // author of this plugin
			'beta'    => false,
		)
	);

}
add_action( 'init', 'bookingpress_google_calendar_plugin_updater' );

?>