<?php

if (is_ssl()) {
    define('RECURRING_APPOINTMENTS_LIST_URL', str_replace('http://', 'https://', WP_PLUGIN_URL . '/' . RECURRING_APPOINTMENTS_LIST_DIR_NAME));
} else {
    define('RECURRING_APPOINTMENTS_LIST_URL', WP_PLUGIN_URL . '/' . RECURRING_APPOINTMENTS_LIST_DIR_NAME);
}
if(file_exists(RECURRING_APPOINTMENTS_LIST_DIR . "/core/classes/class.bookingpress-recurring-appointments.php") ){
	require_once RECURRING_APPOINTMENTS_LIST_DIR . "/core/classes/class.bookingpress-recurring-appointments.php";
}
global $recurring_appointments_list_version;
$recurring_appointments_list_version = '1.4';
define('RECURRING_APPOINTMENTS_LIST_VERSION', $recurring_appointments_list_version);
load_plugin_textdomain( 'bookingpress-recurring-appointments', false, 'bookingpress-recurring-appointments/languages/' );


define( 'BOOKINGPRESS_RECURRING_APPOINTMENTS_STORE_URL', 'https://www.bookingpressplugin.com/' );

if ( ! class_exists( 'bookingpress_pro_updater' ) ) {
	require_once RECURRING_APPOINTMENTS_LIST_DIR . '/core/classes/class.bookingpress_pro_plugin_updater.php';
}

function bookingpress_recurring_appointments_plugin_updater() {

	$plugin_slug_for_update = 'bookingpress-recurring-appointments/bookingpress-recurring-appointments.php';

	// To support auto-updates, this needs to run during the wp_version_check cron job for privileged users.
	$doing_cron = defined( 'DOING_CRON' ) && DOING_CRON;
	if ( ! current_user_can( 'manage_options' ) && ! $doing_cron ) {
		return;
	}

	// retrieve our license key from the DB
	$license_key = trim( get_option( 'bkp_recurring_appointments_license_key' ) );
	$package = trim( get_option( 'bkp_recurring_appointments_license_package' ) );

	// setup the updater
	$edd_updater = new bookingpress_pro_updater(
		BOOKINGPRESS_RECURRING_APPOINTMENTS_STORE_URL,
		$plugin_slug_for_update,
		array(
			'version' => RECURRING_APPOINTMENTS_LIST_VERSION,  // current version number
			'license' => $license_key,             // license key (used get_option above to retrieve from DB)
			'item_id' => $package,       // ID of the product
			'author'  => 'Repute Infosystems', // author of this plugin
			'beta'    => false,
		)
	);

}
add_action( 'init', 'bookingpress_recurring_appointments_plugin_updater' );

?>