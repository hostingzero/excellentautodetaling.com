<?php

global $BookingPress, $recurring_appointments_list_version, $wpdb;
$bookingpress_db_recurring_appointments_version = get_option('recurring_appointments_list_version', true);


$bookingpress_recurring_appointments_new_version = '1.4';
update_option('recurring_appointments_list_version', $bookingpress_recurring_appointments_new_version);
update_option('recurring_appointments_list_version_updated_date_' . $bookingpress_recurring_appointments_new_version, current_time('mysql'));