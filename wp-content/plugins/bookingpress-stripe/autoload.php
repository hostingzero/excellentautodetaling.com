<?php

if (is_ssl()) {
    define('BOOKINGPRESS_STRIPE_URL', str_replace('http://', 'https://', WP_PLUGIN_URL . '/' . BOOKINGPRESS_STRIPE_DIR_NAME));
} else {
    define('BOOKINGPRESS_STRIPE_URL', WP_PLUGIN_URL . '/' . BOOKINGPRESS_STRIPE_DIR_NAME);
}

define('BOOKINGPRESS_STRIPE_LIB_DIR', BOOKINGPRESS_STRIPE_DIR."/lib/Stripe/");

if(file_exists(BOOKINGPRESS_STRIPE_DIR . "/core/classes/class.bookingpress-stripe.php") ){
	require_once BOOKINGPRESS_STRIPE_DIR . "/core/classes/class.bookingpress-stripe.php";
}

if(file_exists(BOOKINGPRESS_STRIPE_DIR . "/core/classes/class.bookingpress-stripe-payment.php") ){
	require_once BOOKINGPRESS_STRIPE_DIR . "/core/classes/class.bookingpress-stripe-payment.php";
}

global $bookingpress_stripe_version;
$bookingpress_stripe_version = '1.9';
define('BOOKINGPRESS_STRIPE_VERSION', $bookingpress_stripe_version);

load_plugin_textdomain( 'bookingpress-stripe', false, 'bookingpress-stripe/languages/' );

define( 'BOOKINGPRESS_STRIPE_STORE_URL', 'https://www.bookingpressplugin.com/' );

if ( ! class_exists( 'bookingpress_pro_updater' ) ) {
	require_once BOOKINGPRESS_STRIPE_DIR . '/core/classes/class.bookingpress_pro_plugin_updater.php';
}

function bookingpress_stripe_plugin_updater() {

	$plugin_slug_for_update = 'bookingpress-stripe/bookingpress-stripe.php';

	// To support auto-updates, this needs to run during the wp_version_check cron job for privileged users.
	$doing_cron = defined( 'DOING_CRON' ) && DOING_CRON;
	if ( ! current_user_can( 'manage_options' ) && ! $doing_cron ) {
		return;
	}

	// retrieve our license key from the DB
	$license_key = trim( get_option( 'bkp_stripe_license_key' ) );
	$package = trim( get_option( 'bkp_stripe_license_package' ) );

	// setup the updater
	$edd_updater = new bookingpress_pro_updater(
		BOOKINGPRESS_STRIPE_STORE_URL,
		$plugin_slug_for_update,
		array(
			'version' => BOOKINGPRESS_STRIPE_VERSION,  // current version number
			'license' => $license_key,             // license key (used get_option above to retrieve from DB)
			'item_id' => $package,       // ID of the product
			'author'  => 'Repute Infosystems', // author of this plugin
			'beta'    => false,
		)
	);

}
add_action( 'init', 'bookingpress_stripe_plugin_updater' );
?>