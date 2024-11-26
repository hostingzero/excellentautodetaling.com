<?php
global $BookingPress, $bookingpress_invoice_version, $wpdb;

$bpa_lite_plugin_version = get_option('bookingpress_version');

if(empty($bpa_lite_plugin_version) || (!empty($bpa_lite_plugin_version) && version_compare( $bpa_lite_plugin_version, '1.0.42', '<' )) ){
    $myaddon_name = "bookingpress-invoice/bookingpress-invoice.php";
    deactivate_plugins($myaddon_name, FALSE);
    /*$redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name);
    $bpa_dact_message = __('BookingPress lite version 1.0.42 required to use BookingPress Invoice Add-on', 'bookingpress-invoice');
    $bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-invoice'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
    wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');*/
    //die;
}

$bookingpress_old_invoice_version = get_option('bookingpress_invoice_version', true);


$bookingpress_invoice_new_version = '2.2';
update_option('bookingpress_invoice_version', $bookingpress_invoice_new_version);
update_option('bookingpress_invoice_updated_date_' . $bookingpress_invoice_new_version, current_time('mysql'));