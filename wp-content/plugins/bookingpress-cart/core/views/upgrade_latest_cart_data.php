<?php

global $BookingPress, $bookingpress_cart_version, $wpdb;
$bookingpress_old_cart_version = get_option('bookingpress_cart_module', true);

if (version_compare($bookingpress_old_cart_version, '1.4', '<') ) {
    $tbl_bookingpress_customize_settings = $wpdb->prefix . 'bookingpress_customize_settings';

    $booking_form = array(
        'cart_service_extra_title' => __('extras', 'bookingpress-cart'),
        'cart_deposit_title' => '('.__('Deposit', 'bookingpress-cart').')',
    );
    foreach($booking_form as $key => $value) {
        $bookingpress_customize_settings_db_fields = array(
            'bookingpress_setting_name'  => $key,
            'bookingpress_setting_value' => $value,
            'bookingpress_setting_type'  => 'booking_form',
        );
        $wpdb->insert( $tbl_bookingpress_customize_settings, $bookingpress_customize_settings_db_fields );
    }	
}

if (version_compare($bookingpress_old_cart_version, '1.5', '<') ) {
    $tbl_bookingpress_customize_settings = $wpdb->prefix . 'bookingpress_customize_settings';

    $booking_form = array(
        'cart_number_person_title' => __('No. Of Person', 'bookingpress-cart'),
    );
    foreach($booking_form as $key => $value) {
        $bookingpress_customize_settings_db_fields = array(
            'bookingpress_setting_name'  => $key,
            'bookingpress_setting_value' => $value,
            'bookingpress_setting_type'  => 'booking_form',
        );
        $wpdb->insert( $tbl_bookingpress_customize_settings, $bookingpress_customize_settings_db_fields );
    }	
}

if (version_compare($bookingpress_old_cart_version, '1.8', '<') ) {
    $tbl_bookingpress_customize_settings = $wpdb->prefix . 'bookingpress_customize_settings';
    $booking_form = array(
        'cart_edit_item_title' => __('Edit', 'bookingpress-cart'),
        'cart_remove_item_title' => __('Remove', 'bookingpress-cart'),
        'cart_service_duration_title' => __('Duration', 'bookingpress-cart'),
        'cart_staff_title' => __('Staff', 'bookingpress-cart'),
        'cart_service_extra_quantity_title' => __('Qty', 'bookingpress-cart'),
    );
    foreach($booking_form as $key => $value) {
        $bookingpress_customize_settings_db_fields = array(
            'bookingpress_setting_name'  => $key,
            'bookingpress_setting_value' => $value,
            'bookingpress_setting_type'  => 'booking_form',
        );
        $wpdb->insert( $tbl_bookingpress_customize_settings, $bookingpress_customize_settings_db_fields );
    }	
}

$bookingpress_cart_new_version = '2.9';
update_option('bookingpress_cart_module', $bookingpress_cart_new_version);
update_option('bookingpress_cart_updated_date_' . $bookingpress_cart_new_version, current_time('mysql'));