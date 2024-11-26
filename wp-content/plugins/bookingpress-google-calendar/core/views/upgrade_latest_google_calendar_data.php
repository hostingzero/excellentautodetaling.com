<?php

global $BookingPress, $bookingpress_google_calendar_version, $wpdb;
$bookingpress_old_google_calendar_version = get_option('bookingpress_google_calendar_version', true);

if(version_compare($bookingpress_old_google_calendar_version, '1.2', '<')){
    $tbl_bookingpress_appointment_bookings = $wpdb->prefix . 'bookingpress_appointment_bookings';
    $wpdb->query( "ALTER TABLE `{$tbl_bookingpress_appointment_bookings}` ADD COLUMN `bookingpress_google_calendar_event_link` VARCHAR(100) NULL DEFAULT NULL;" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally.
}
if(version_compare($bookingpress_old_google_calendar_version, '2.4', '<')){
    $bookingpress_gc_scheduler_data = get_option( 'bookingpress_gc_scheduler_data' );
    $bookingpress_gc_scheduler_data = json_decode( $bookingpress_gc_scheduler_data, true );
    if( !empty( $bookingpress_gc_scheduler_data ) ){
        foreach( $bookingpress_gc_scheduler_data as $appointment_id => $appointment_data ){
            $entry_id = $appointment_data['entry_id'];
            $payment_gateway_data = $appointment_data['payment_gateway_data'];
            $arguments = array(
                'entry_id' => $entry_id,
                'payment_gateway_data' => $payment_gateway_data
            );
            $BookingPress->bookingpress_update_settings( 'bpa_gc_cron_app_data_' . $appointment_id, 'bpa_gc_cron', json_encode( $arguments ) );
        }
    }
}

$bookingpress_google_calendar_new_version = '2.6';
update_option('bookingpress_google_calendar_version', $bookingpress_google_calendar_new_version);
update_option('bookingpress_google_calendar_updated_date_' . $bookingpress_google_calendar_new_version, current_time('mysql'));