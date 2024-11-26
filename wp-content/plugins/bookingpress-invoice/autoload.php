<?php
define( 'BOOKINGPRESS_INVOICE_VIEWS_DIR', BOOKINGPRESS_INVOICE_DIR . '/core/views' );
define( 'BOOKINGPRESS_INVOICE_VIEWS_URL', BOOKINGPRESS_INVOICE_URL . '/core/views' );

if(file_exists(BOOKINGPRESS_INVOICE_DIR . "/core/classes/class.bookingpress-invoice.php") ){
	require_once BOOKINGPRESS_INVOICE_DIR . "/core/classes/class.bookingpress-invoice.php";
}

$bookingpress_get_php_version = (function_exists('phpversion')) ? phpversion() : 0;

if (file_exists(BOOKINGPRESS_INVOICE_LIBRARY_DIR . '/mpdf/vendor/autoload.php') && version_compare( $bookingpress_get_php_version, '7.1.0', '>=') ) {
    require_once(BOOKINGPRESS_INVOICE_LIBRARY_DIR . '/mpdf/vendor/autoload.php');
}

global $bookingpress_invoice_version;
$bookingpress_invoice_version = '2.2';
define('BOOKINGPRESS_INVOICE_VERSION', $bookingpress_invoice_version);

load_plugin_textdomain( 'bookingpress-invoice', false, 'bookingpress-invoice/languages/' );

define( 'BOOKINGPRESS_INVOICE_STORE_URL', 'https://www.bookingpressplugin.com/' );

if ( ! class_exists( 'bookingpress_pro_updater' ) ) {
	require_once BOOKINGPRESS_INVOICE_DIR . '/core/classes/class.bookingpress_pro_plugin_updater.php';
}


function bookingpress_invoice_plugin_updater() {

	$plugin_slug_for_update = 'bookingpress-invoice/bookingpress-invoice.php';

	// To support auto-updates, this needs to run during the wp_version_check cron job for privileged users.
	$doing_cron = defined( 'DOING_CRON' ) && DOING_CRON;
	if ( ! current_user_can( 'manage_options' ) && ! $doing_cron ) {
		return;
	}

	// retrieve our license key from the DB
	$license_key = trim( get_option( 'bkp_invoice_license_key' ) );
	$package = trim( get_option( 'bkp_invoice_license_package' ) );

	// setup the updater
	$edd_updater = new bookingpress_pro_updater(
		BOOKINGPRESS_INVOICE_STORE_URL,
		$plugin_slug_for_update,
		array(
			'version' => BOOKINGPRESS_INVOICE_VERSION,  // current version number
			'license' => $license_key,             // license key (used get_option above to retrieve from DB)
			'item_id' => $package,       // ID of the product
			'author'  => 'Repute Infosystems', // author of this plugin
			'beta'    => false,
		)
	);

}
add_action( 'init', 'bookingpress_invoice_plugin_updater' );
?>