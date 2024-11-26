<?php
/*
Plugin Name: BookingPress - Google Calendar Integration Addon
Description: Extension for BookingPress plugin to add appointment in google Calendar.
Version: 2.6
Requires at least: 5.0
Requires PHP:      5.6
Plugin URI: https://www.bookingpressplugin.com/
Author: Repute InfoSystems
Author URI: https://www.bookingpressplugin.com/
Text Domain: bookingpress-google-calendar
Domain Path: /languages
*/

define('BOOKINGPRESS_GOOGLE_CALENDAR_DIR_NAME', 'bookingpress-google-calendar');
define('BOOKINGPRESS_GOOGLE_CALENDAR_DIR', WP_PLUGIN_DIR . '/' . BOOKINGPRESS_GOOGLE_CALENDAR_DIR_NAME);

global $BookingPress;

$bpa_lite_plugin_version = get_option('bookingpress_version');
function bpa_check_google_calendar_lite_version(){
    $bpa_lite_plugin_version = get_option('bookingpress_version');
    if(empty($bpa_lite_plugin_version) || (!empty($bpa_lite_plugin_version) && version_compare( $bpa_lite_plugin_version, '1.0.42', '<' )) ){
        $myaddon_name = "bookingpress-google-calendar/bookingpress-google-calendar.php";
        deactivate_plugins($myaddon_name, FALSE);
        $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name);
        $bpa_dact_message = __('BookingPress lite version 1.0.42 required to use BookingPress Google Calendar Add-on', 'bookingpress-google-calendar');
        /* translators: 1. Plugin deactivate url */
        $bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-google-calendar'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>'); //phpcs:ignore
        wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason: data is escaped properly
        die;
    }else{
        global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_notifiction;
        $bookingpress_g_c_version = get_option('bookingpress_google_calendar_version');
        if (!isset($bookingpress_g_c_version) || $bookingpress_g_c_version == '') {

            $myaddon_name = "bookingpress-google-calendar/bookingpress-google-calendar.php";

            // activate license for this addon
            $posted_license_key = trim( get_option( 'bkp_license_key' ) );
            $posted_license_package = '4864';

            $api_params = array(
                'edd_action' => 'activate_license',
                'license'    => $posted_license_key,
                'item_id'  => $posted_license_package,
                //'item_name'  => urlencode( BOOKINGPRESS_ITEM_NAME ), // the name of our product in EDD
                'url'        => home_url()
            );

            // Call the custom API.
            $response = wp_remote_post( BOOKINGPRESS_STORE_URL, array( 'timeout' => 15, 'body' => $api_params ) );

            //echo "<pre>";print_r($response); echo "</pre>"; exit;

            // make sure the response came back okay
            $message = "";
            if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
                $message =  ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) ) ? $response->get_error_message() : __( 'An error occurred, please try again.','bookingpress-google-calendar');
            } else {
                $license_data = json_decode( wp_remote_retrieve_body( $response ) );
                $license_data_string = wp_remote_retrieve_body( $response );
                if ( false === $license_data->success ) {
                    switch( $license_data->error ) {
                        case 'expired' :
                            $message = sprintf(
                                __( 'Your license key expired on %s.','bookingpress-google-calendar' ),
                                date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
                            );
                            break;
                        case 'revoked' :
                            $message = __( 'Your license key has been disabled.','bookingpress-google-calendar' );
                            break;
                        case 'missing' :
                            $message = __( 'Invalid license.','bookingpress-google-calendar' );
                            break;
                        case 'invalid' :
                        case 'site_inactive' :
                            $message = __( 'Your license is not active for this URL.','bookingpress-google-calendar' );
                            break;
                        case 'item_name_mismatch' :
                            $message = __('This appears to be an invalid license key for your selected package.','bookingpress-google-calendar');
                            break;
                        case 'invalid_item_id' :
                                $message = __('This appears to be an invalid license key for your selected package.','bookingpress-google-calendar');
                                break;
                        case 'no_activations_left':
                            $message = __( 'Your license key has reached its activation limit.','bookingpress-google-calendar');
                            break;
                        default :
                            $message = __( 'An error occurred, please try again.','bookingpress-google-calendar' );
                            break;
                    }

                }

            }

            if ( ! empty( $message ) ) {
                update_option( 'bkp_google_calendar_license_data_activate_response', $license_data_string );
                update_option( 'bkp_google_calendar_license_status', $license_data->license );
                deactivate_plugins($myaddon_name, FALSE);
                $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name);
                    $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Google Calendar Add-on', 'bookingpress-google-calendar');
					$bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-google-calendar'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
					wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                    die;

            }
            
            if($license_data->license === "valid")
            {
                update_option( 'bkp_google_calendar_license_key', $posted_license_key );
                update_option( 'bkp_google_calendar_license_package', $posted_license_package );
                update_option( 'bkp_google_calendar_license_status', $license_data->license );
                update_option( 'bkp_google_calendar_license_data_activate_response', $license_data_string );
            }

            $bookingpress_google_calendar_version = 1.3;
            update_option('bookingpress_google_calendar_version', $bookingpress_google_calendar_version);
            if( empty( $tbl_bookingpress_appointment_bookings ) ){
                $tbl_bookingpress_appointment_bookings = $wpdb->prefix . 'bookingpress_appointment_bookings';
            }
            $wpdb->query( "ALTER TABLE `{$tbl_bookingpress_appointment_bookings}` ADD COLUMN `bookingpress_google_calendar_event_id` VARCHAR(100) NULL DEFAULT NULL;" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
            $wpdb->query( "ALTER TABLE `{$tbl_bookingpress_appointment_bookings}` ADD COLUMN `bookingpress_google_calendar_event_link` VARCHAR(100) NULL DEFAULT NULL;" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
        }
    }
}
register_activation_hook( __FILE__, 'bpa_check_google_calendar_lite_version' );

register_uninstall_hook(__FILE__, 'bpa_google_cal_uninstall');
function bpa_google_cal_uninstall(){
    global $wpdb;
    if( is_multisite() ){
        $blogs = $wpdb->get_results( "SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A );
        if ( $blogs ) {
            foreach ( $blogs as $blog ) {
                switch_to_blog( $blog['blog_id'] );
                
                $bookingpress_tbl_name = $wpdb->prefix . 'bookingpress_appointment_bookings';
                
                $wpdb->query( "ALTER TABLE `{$bookingpress_tbl_name}` DROP COLUMN IF EXISTS `bookingpress_google_calendar_event_id`" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $bookingpress_tbl_name is a table name. false alarm
                $wpdb->query( "ALTER TABLE `{$bookingpress_tbl_name}` DROP COLUMN IF EXISTS `bookingpress_google_calendar_event_link`"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $bookingpress_tbl_name is a table name. false alarm

                delete_option('bookingpress_google_calendar_version');
                delete_option('bkp_google_calendar_license_key');
                delete_option('bkp_google_calendar_license_package');
                delete_option('bkp_google_calendar_license_status');
                delete_option('bkp_google_calendar_license_data_activate_response');
		$bookingpress_staff_tbl_data = $wpdb->prefix . 'bookingpress_staffmembers_meta';

                $fetch_watch_details = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_staffmember_id, bookingpress_staffmembermeta_value FROM {$bookingpress_staff_tbl_data} WHERE bookingpress_staffmembermeta_key = %s", 'bookingpress_gc_watch_details' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $bookingpress_staff_tbl_data is a table name. false alarm

                if( !empty( $fetch_watch_details ) ){
                    foreach( $fetch_watch_details as $watch_details ){
                        $staff_id = !empty( $watch_details->bookingpress_staffmember_id ) ? intval( $watch_details->bookingpress_staffmember_id ) : '';
                        $staff_watch = !empty( $watch_details->bookingpress_staffmembermeta_value ) ? json_decode( $watch_details->bookingpress_staffmembermeta_value, true ) : '';
                        $get_auth_token = $wpdb->get_var( $wpdb->prepare( "SELECT bookingpress_staffmembermeta_value FROM {$bookingpress_staff_tbl_data} WHERE bookingpress_staffmembermeta_key = %s AND bookingpress_staffmember_id = %d", 'bookingpress_staff_gcalendar_auth', $staff_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $bookingpress_staff_tbl_data is a table name. false alarm

                        if( !empty( $staff_watch ) && !empty( $get_auth_token ) ){
                            $params = json_encode(
                                array(
                                    'id' => $staff_watch['id'],
                                    'resourceId' => $staff_watch['resourceId']
                                )
                            );

                            $stop_watch_url = 'https://www.googleapis.com/calendar/v3/channels/stop';

                            $arguments = array(
                                'timeout' => 4500,
                                'headers' => array(
                                    'Content-Type' => 'application/json',
                                    'Authorization' => 'Bearer ' . $get_auth_token['access_token']
                                ),
                                'body' => json_encode( $params )
                            );

                            $stop_watch_response = wp_remote_post( $stop_watch_url, $arguments );

                            update_option( 'bookingpress_gc_stop_watch_param_' . $staff_id, json_encode( $stop_watch_response ) );
                        }
                    }
                }
            }
        }
    } else {
        delete_option('bookingpress_google_calendar_version');
        delete_option('bkp_google_calendar_license_key');
        delete_option('bkp_google_calendar_license_package');
        delete_option('bkp_google_calendar_license_status');
        delete_option('bkp_google_calendar_license_data_activate_response');

        $bookingpress_tbl_name = $wpdb->prefix . 'bookingpress_appointment_bookings';
        $wpdb->query( "ALTER TABLE `{$bookingpress_tbl_name}` DROP COLUMN IF EXISTS `bookingpress_google_calendar_event_id`"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $bookingpress_tbl_name is a table name. false alarm
        $wpdb->query( "ALTER TABLE `{$bookingpress_tbl_name}` DROP COLUMN IF EXISTS `bookingpress_google_calendar_event_link`"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $bookingpress_tbl_name is a table name. false alarm

        $bookingpress_staff_tbl_data = $wpdb->prefix . 'bookingpress_staffmembers_meta';

        $fetch_watch_details = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_staffmember_id, bookingpress_staffmembermeta_value FROM {$bookingpress_staff_tbl_data} WHERE bookingpress_staffmembermeta_key = %s", 'bookingpress_gc_watch_details' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $bookingpress_staff_tbl_data is a table name. false alarm

        if( !empty( $fetch_watch_details ) ){
            foreach( $fetch_watch_details as $watch_details ){
                $staff_id = !empty( $watch_details->bookingpress_staffmember_id ) ? intval( $watch_details->bookingpress_staffmember_id ) : '';
                $staff_watch = !empty( $watch_details->bookingpress_staffmembermeta_value ) ? json_decode( $watch_details->bookingpress_staffmembermeta_value, true ) : '';
                $get_auth_token = $wpdb->get_var( $wpdb->prepare( "SELECT bookingpress_staffmembermeta_value FROM {$bookingpress_staff_tbl_data} WHERE bookingpress_staffmembermeta_key = %s AND bookingpress_staffmember_id = %d", 'bookingpress_staff_gcalendar_auth', $staff_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $bookingpress_staff_tbl_data is a table name. false alarm

                if( !empty( $staff_watch ) && !empty( $get_auth_token ) ){
                    $params = json_encode(
                        array(
                            'id' => $staff_watch['id'],
                            'resourceId' => $staff_watch['resourceId']
                        )
                    );

                    $stop_watch_url = 'https://www.googleapis.com/calendar/v3/channels/stop';

                    $arguments = array(
                        'timeout' => 4500,
                        'headers' => array(
                            'Content-Type' => 'application/json',
                            'Authorization' => 'Bearer '.$get_auth_token['access_token']
                        ),
                        'body' => json_encode( $params )
                    );

                    $stop_watch_response = wp_remote_post( $stop_watch_url, $arguments );

                    update_option( 'bookingpress_gc_stop_watch_param_' . $staff_id, json_encode( $stop_watch_response ) );
                }
            }
        }
    }
}

$bookingpress_db_gcalendar_version = get_option('bookingpress_google_calendar_version');
if(!empty($bookingpress_db_gcalendar_version) && version_compare( $bookingpress_db_gcalendar_version, '1.3', '<' ) ){
    $bookingpress_load_gcalendar_upgrade_file = BOOKINGPRESS_GOOGLE_CALENDAR_DIR . '/core/views/upgrade_latest_google_calendar_data.php';
    include $bookingpress_load_gcalendar_upgrade_file;
    $BookingPress->bookingpress_send_anonymous_data_cron();   
}

if (!empty($bpa_lite_plugin_version) && version_compare( $bpa_lite_plugin_version, '1.0.42', '>=' ) && file_exists( BOOKINGPRESS_GOOGLE_CALENDAR_DIR . '/autoload.php')) {
    require_once BOOKINGPRESS_GOOGLE_CALENDAR_DIR . '/autoload.php';
}