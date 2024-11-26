<?php
/*
Plugin Name: BookingPress - Outlook Calendar Integration Addon
Description: Extension for BookingPress plugin to add appointment in Outlook Calendar.
Version: 2.2
Requires at least: 5.0
Requires PHP:      5.6
Plugin URI: https://www.bookingpressplugin.com/
Author: Repute InfoSystems
Author URI: https://www.bookingpressplugin.com/
Text Domain: bookingpress-outlook-calendar
Domain Path: /languages
*/

define( 'BOOKINGPRESS_OUTLOOK_CALENDAR_DIR_NAME', 'bookingpress-outlook-calendar' );
define( 'BOOKINGPRESS_OUTLOOK_CALENDAR_DIR', WP_PLUGIN_DIR . '/' . BOOKINGPRESS_OUTLOOK_CALENDAR_DIR_NAME );

global $BookingPress;

$bpa_lite_plugin_version = get_option( 'bookingpress_version' );

function bookingpress_install_outlook_calendar() {

	$bpa_lite_plugin_version = get_option( 'bookingpress_version' );

	if ( empty( $bpa_lite_plugin_version ) || ( ! empty( $bpa_lite_plugin_version ) && version_compare( $bpa_lite_plugin_version, '1.0.42', '<' ) ) ) {

		$myaddon_name = 'bookingpress-outlook-calendar/bookingpress-outlook-calendar.php';
		deactivate_plugins( $myaddon_name, false );
		$redirect_url     = network_admin_url( 'plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin=' . $myaddon_name );
		$bpa_dact_message = __( 'BookingPress lite version 1.0.42 required to use BookingPress Outlook Calendar Add-on', 'bookingpress-outlook-calendar' );
		$bpa_link         = sprintf( __( 'Please %1$s Click Here %2$s to Continue', 'bookingpress-outlook-calendar' ), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>' );
		wp_die( '<p>' . $bpa_dact_message . '<br/>' . $bpa_link . '</p>' );
		die;

	} else {
		$bookingpress_outlook_calendar_db_version = get_option( 'bookingpress_outlook_calendar_version' );
		if ( ! isset( $bookingpress_outlook_calendar_db_version ) || $bookingpress_outlook_calendar_db_version == '' ) {
            $myaddon_name = "bookingpress-outlook-calendar/bookingpress-outlook-calendar.php";
		
            // activate license for this addon
            $posted_license_key = trim( get_option( 'bkp_license_key' ) );
            $posted_license_package = '4865';

            $api_params = array(
                'edd_action' => 'activate_license',
                'license'    => $posted_license_key,
                'item_id'  => $posted_license_package,
                //'item_name'  => urlencode( BOOKINGPRESS_ITEM_NAME ), // the name of our product in EDD
                'url'        => home_url()
            );

            // Call the custom API.
            $response = wp_remote_post( BOOKINGPRESS_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

            //echo "<pre>";print_r($response); echo "</pre>"; exit;

            // make sure the response came back okay
            $message = "";
            if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
                $message =  ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) ) ? $response->get_error_message() : __( 'An error occurred, please try again.','bookingpress-outlook-calendar' );
            } else {
                $license_data = json_decode( wp_remote_retrieve_body( $response ) );
                $license_data_string = wp_remote_retrieve_body( $response );
                if ( false === $license_data->success ) {
                    switch( $license_data->error ) {
                        case 'expired' :
                            $message = sprintf(
                                __( 'Your license key expired on %s.','bookingpress-outlook-calendar' ),
                                date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
                            );
                            break;
                        case 'revoked' :
                            $message = __( 'Your license key has been disabled.','bookingpress-outlook-calendar' );
                            break;
                        case 'missing' :
                            $message = __( 'Invalid license.','bookingpress-outlook-calendar' );
                            break;
                        case 'invalid' :
                        case 'site_inactive' :
                            $message = __( 'Your license is not active for this URL.','bookingpress-outlook-calendar' );
                            break;
                        case 'item_name_mismatch' :
                            $message = __('This appears to be an invalid license key for your selected package.','bookingpress-outlook-calendar');
                            break;
                        case 'invalid_item_id' :
                                $message = __('This appears to be an invalid license key for your selected package.','bookingpress-outlook-calendar');
                                break;
                        case 'no_activations_left':
                            $message = __( 'Your license key has reached its activation limit.','bookingpress-outlook-calendar' );
                            break;
                        default :
                            $message = __( 'An error occurred, please try again.','bookingpress-outlook-calendar');
                            break;
                    }

                }

            }

            if ( ! empty( $message ) ) {
                update_option( 'bkp_outlook_calendar_license_data_activate_response', $license_data_string );
                update_option( 'bkp_outlook_calendar_license_status', $license_data->license );
                deactivate_plugins($myaddon_name, FALSE);
                $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name);
                    $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Outlook Calendar Add-on', 'bookingpress-outlook-calendar');
					$bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-outlook-calendar'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
					wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                    die;
            }
            
            if($license_data->license === "valid")
            {
                update_option( 'bkp_outlook_calendar_license_key', $posted_license_key );
                update_option( 'bkp_outlook_calendar_license_package', $posted_license_package );
                update_option( 'bkp_outlook_calendar_license_status', $license_data->license );
                update_option( 'bkp_outlook_calendar_license_data_activate_response', $license_data_string );
            }

			$bookingpress_outlook_calendar_version = '2.0';
			update_option( 'bookingpress_outlook_calendar_version', $bookingpress_outlook_calendar_version );
			global $wpdb, $tbl_bookingpress_appointment_bookings;
			if ( empty( $tbl_bookingpress_appointment_bookings ) ) {
				$tbl_bookingpress_appointment_bookings = $wpdb->prefix . 'bookingpress_appointment_bookings';
			}
			$wpdb->query( "ALTER TABLE `{$tbl_bookingpress_appointment_bookings}` ADD COLUMN `bookingpress_outlook_calendar_event_id` VARCHAR(200) NULL DEFAULT NULL;" );
		}
	}
}
register_activation_hook( __FILE__, 'bookingpress_install_outlook_calendar' );

register_uninstall_hook( __FILE__, 'bookingpress_uninstall_outlook_calendar' );

function bookingpress_uninstall_outlook_calendar() {

	global $wpdb;
	if ( is_multisite() ) {
		$blogs = $wpdb->get_results( "SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A );
		if ( $blogs ) {
			foreach ( $blogs as $blog ) {
				switch_to_blog( $blog['blog_id'] );

				$bookingpress_tbl_name = $wpdb->prefix . 'bookingpress_appointment_bookings';
				$wpdb->query( "ALTER TABLE `{$bookingpress_tbl_name}` DROP COLUMN IF EXISTS `bookingpress_outlook_calendar_event_id`" );

				delete_option( 'bookingpress_outlook_calendar_version' );
                
                delete_option('bkp_outlook_calendar_license_key');
                delete_option('bkp_outlook_calendar_license_package');
                delete_option('bkp_outlook_calendar_license_status');
                delete_option('bkp_outlook_calendar_license_data_activate_response');
			}
			restore_current_blog();
		}
	} else {
		delete_option( 'bookingpress_outlook_calendar_version' );

        delete_option('bkp_outlook_calendar_license_key');
        delete_option('bkp_outlook_calendar_license_package');
        delete_option('bkp_outlook_calendar_license_status');
        delete_option('bkp_outlook_calendar_license_data_activate_response');

		$bookingpress_tbl_name = $wpdb->prefix . 'bookingpress_appointment_bookings';
		$wpdb->query( "ALTER TABLE `{$bookingpress_tbl_name}` DROP COLUMN IF EXISTS `bookingpress_outlook_calendar_event_id`" );
	}
}

if ( ! empty( $bpa_lite_plugin_version ) && version_compare( $bpa_lite_plugin_version, '1.0.42', '>=' ) && file_exists( BOOKINGPRESS_OUTLOOK_CALENDAR_DIR . '/autoload.php' ) ) {
	require_once BOOKINGPRESS_OUTLOOK_CALENDAR_DIR . '/autoload.php';
}