<?php

global $BookingPress, $bookingpress_outlook_calendar_version, $wpdb;
$bookingpress_old_outlook_calendar_version = get_option('bookingpress_outlook_calendar_version', true);

if(version_compare($bookingpress_old_outlook_calendar_version, '1.9', '<')){
    $bookingpress_oc_scheduler_data = get_option( 'bookingpress_outlook_scheduler_data' );
    $bookingpress_oc_scheduler_data = json_decode( $bookingpress_oc_scheduler_data, true );
    if( !empty( $bookingpress_oc_scheduler_data ) ){
        foreach( $bookingpress_oc_scheduler_data as $appointment_id => $appointment_data ){
            $entry_id = $appointment_data['entry_id'];
            $payment_gateway_data = $appointment_data['payment_gateway_data'];
            $arguments = array(
                'entry_id' => $entry_id,
                'payment_gateway_data' => $payment_gateway_data
            );
            $BookingPress->bookingpress_update_settings( 'bpa_outlookc_cron_app_data_' . $appointment_id, 'bpa_outlookc_cron', json_encode( $arguments ) );
        }
    }

    $bookingpress_oc_scheduler_data_other = get_option( 'bookingpress_oc_scheduler_data' );
    $bookingpress_oc_scheduler_data_other = json_decode( $bookingpress_oc_scheduler_data_other, true );
    if( !empty( $bookingpress_oc_scheduler_data_other ) ){
        foreach( $bookingpress_oc_scheduler_data_other as $appointment_id => $appointment_data ){
            $entry_id = $appointment_data['entry_id'];
            $payment_gateway_data = $appointment_data['payment_gateway_data'];
            $arguments = array(
                'entry_id' => $entry_id,
                'payment_gateway_data' => $payment_gateway_data
            );
            $BookingPress->bookingpress_update_settings( 'bpa_outlookc_cron_app_data_' . $appointment_id, 'bpa_outlookc_cron', json_encode( $arguments ) );
        }
    }    
}


$bookingpress_outlook_calendar_new_version = '2.2';
update_option('bookingpress_outlook_calendar_version', $bookingpress_outlook_calendar_new_version);
update_option('bookingpress_outlook_calendar_updated_date_' . $bookingpress_outlook_calendar_new_version, current_time('mysql'));