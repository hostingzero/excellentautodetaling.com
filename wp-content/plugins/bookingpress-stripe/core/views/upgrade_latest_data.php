<?php

global $BookingPress, $wpdb;

$bookingpress_old_stripe_version = get_option('bookingpress_stripe_payment_gateway', true);

if (version_compare($bookingpress_old_stripe_version, '1.7', '<') ) {

    $tbl_bookingpress_customize_settings = $wpdb->prefix . 'bookingpress_customize_settings';
    $booking_form = array(
        'stripe_text' => __('Credit Card', 'bookingpress-stripe'),
    );
    foreach($booking_form as $key => $value) {
        $bookingpress_customize_settings_db_fields = array(
            'bookingpress_setting_name'  => $key,
            'bookingpress_setting_value' => $value,
            'bookingpress_setting_type'  => 'package_booking_form',
        );
        $wpdb->insert( $tbl_bookingpress_customize_settings, $bookingpress_customize_settings_db_fields );
    }

}

if (version_compare($bookingpress_old_stripe_version, '1.9', '<') ) {

    $tbl_bookingpress_customize_settings = $wpdb->prefix . 'bookingpress_customize_settings';
    $booking_form = array(
        'stripe_text' => __('Credit Card', 'bookingpress-stripe'),
    );
    foreach($booking_form as $key => $value) {
        $bookingpress_customize_settings_db_fields = array(
            'bookingpress_setting_name'  => $key,
            'bookingpress_setting_value' => $value,
            'bookingpress_setting_type'  => 'gift_card_form',
        );
        $wpdb->insert( $tbl_bookingpress_customize_settings, $bookingpress_customize_settings_db_fields );
    }

}

$bookingpress_stripe_new_version = '1.9';
update_option('bookingpress_stripe_payment_gateway', $bookingpress_stripe_new_version);
update_option('bookingpress_stripe_updated_date_' . $bookingpress_stripe_new_version, current_time('mysql'));

?>