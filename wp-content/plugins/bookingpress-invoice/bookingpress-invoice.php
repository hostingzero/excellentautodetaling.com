<?php
/*
Plugin Name: BookingPress - Invoice Addon
Description: Invoice Integration.
Version: 2.2
Requires at least: 5.0
Requires PHP:      5.6
Plugin URI: https://www.bookingpressplugin.com/
Author: Repute InfoSystems
Author URI: https://www.bookingpressplugin.com/
Text Domain: bookingpress-invoice
Domain Path: /languages
*/

define('BOOKINGPRESS_INVOICE_DIR_NAME', 'bookingpress-invoice');
define('BOOKINGPRESS_INVOICE_DIR', WP_PLUGIN_DIR . '/' . BOOKINGPRESS_INVOICE_DIR_NAME);

if (is_ssl()) {
    define('BOOKINGPRESS_INVOICE_URL', str_replace('http://', 'https://', WP_PLUGIN_URL . '/' . BOOKINGPRESS_INVOICE_DIR_NAME));
} else {
    define('BOOKINGPRESS_INVOICE_URL', WP_PLUGIN_URL . '/' . BOOKINGPRESS_INVOICE_DIR_NAME);
}

define('BOOKINGPRESS_INVOICE_LIBRARY_DIR', BOOKINGPRESS_INVOICE_DIR . '/lib');
define('BOOKINGPRESS_INVOICE_LIBRARY_URL', BOOKINGPRESS_INVOICE_URL . '/lib');

global $bookigpress_pdf_upload_dir_name,$bookingpress_invoice_pdf_upload_dir;
$bookigpress_pdf_upload_dir_name = 'bookingpress_invoice';
$wp_upload_dir = wp_upload_dir();
$bookingpress_invoice_pdf_upload_dir = $wp_upload_dir['basedir'] . '/bookingpress/'.$bookigpress_pdf_upload_dir_name;
$bookingpress_pdf_font_dir = $bookingpress_invoice_pdf_upload_dir.'/fonts';
define( 'BOOKINGPRESS_INVOICE_FONT_DIR',$bookingpress_pdf_font_dir);

$bpa_lite_plugin_version = get_option('bookingpress_version');

function bpa_check_invoice_lite_version(){
    $bpa_lite_plugin_version = get_option('bookingpress_version');
    if(empty($bpa_lite_plugin_version) || (!empty($bpa_lite_plugin_version) && version_compare( $bpa_lite_plugin_version, '1.0.42', '<' )) ){
        $myaddon_name = "bookingpress-invoice/bookingpress-invoice.php";
        deactivate_plugins($myaddon_name, FALSE);
        $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name);
        $bpa_dact_message = __('BookingPress lite version 1.0.42 required to use BookingPress Invoice Add-on', 'bookingpress-invoice');
        $bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-invoice'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
        wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
        die;
    }else{
        global $wpdb,$tbl_bookingpress_notifications, $tbl_bookingpress_settings;

        $bookingpress_invoice_addon_version = get_option('bookingpress_invoice_version');
        if (!isset($bookingpress_invoice_addon_version) || $bookingpress_invoice_addon_version == '') { 

            $myaddon_name = "bookingpress-invoice/bookingpress-invoice.php";

            // activate license for this addon
            $posted_license_key = trim( get_option( 'bkp_license_key' ) );
            $posted_license_package = '4856';

            $api_params = array(
                'edd_action' => 'activate_license',
                'license'    => $posted_license_key,
                'item_id'  => $posted_license_package,
                //'item_name'  => urlencode( BOOKINGPRESS_ITEM_NAME ), // the name of our product in EDD
                'url'        => home_url()
            );

            // Call the custom API.
            $response = wp_remote_post( BOOKINGPRESS_INVOICE_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

            //echo "<pre>";print_r($response); echo "</pre>"; exit;

            // make sure the response came back okay
            $message = "";
            if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
                $message =  ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) ) ? $response->get_error_message() : __( 'An error occurred, please try again.','bookingpress-invoice' );
            } else {
                $license_data = json_decode( wp_remote_retrieve_body( $response ) );
                $license_data_string = wp_remote_retrieve_body( $response );
                if ( false === $license_data->success ) {
                    switch( $license_data->error ) {
                        case 'expired' :
                            $message = sprintf(
                                __( 'Your license key expired on %s.','bookingpress-invoice' ),
                                date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
                            );
                            break;
                        case 'revoked' :
                            $message = __( 'Your license key has been disabled.','bookingpress-invoice' );
                            break;
                        case 'missing' :
                            $message = __( 'Invalid license.','bookingpress-invoice' );
                            break;
                        case 'invalid' :
                        case 'site_inactive' :
                            $message = __( 'Your license is not active for this URL.','bookingpress-invoice' );
                            break;
                        case 'item_name_mismatch' :
                            $message = __('This appears to be an invalid license key for your selected package.','bookingpress-invoice');
                            break;
                        case 'invalid_item_id' :
                                $message = __('This appears to be an invalid license key for your selected package.','bookingpress-invoice');
                                break;
                        case 'no_activations_left':
                            $message = __( 'Your license key has reached its activation limit.','bookingpress-invoice' );
                            break;
                        default :
                            $message = __( 'An error occurred, please try again.','bookingpress-invoice' );
                            break;
                    }

                }

            }

            if ( ! empty( $message ) ) {
                update_option( 'bkp_invoice_license_data_activate_response', $license_data_string );
                update_option( 'bkp_invoice_license_status', $license_data->license );
                deactivate_plugins($myaddon_name, FALSE);
                header('Location: ' . network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name));
                die;
            }
            
            if($license_data->license === "valid")
            {
                update_option( 'bkp_invoice_license_key', $posted_license_key );
                update_option( 'bkp_invoice_license_package', $posted_license_package );
                update_option( 'bkp_invoice_license_status', $license_data->license );
                update_option( 'bkp_invoice_license_data_activate_response', $license_data_string );
            }

            $bookingpress_invoice_version = 2.1;
            update_option('bookingpress_invoice_version', $bookingpress_invoice_version);           
            wp_mkdir_p(BOOKINGPRESS_INVOICE_FONT_DIR);

            if(empty($tbl_bookingpress_settings)){
                $tbl_bookingpress_settings = $wpdb->prefix . 'bookingpress_settings';
            }

            if(empty($tbl_bookingpress_notifications)){
                $tbl_bookingpress_notifications = $wpdb->prefix."bookingpress_notifications";
            }

            //Install default plugin data
            global $BookingPress;            
            $bookingpress_install_default_invoice_settings_data  = array(
                'bookingpress_invoice_suffix_prefix' =>  'false',
                'bookingpress_invoice_prefix' => '',
                'bookingpress_invoice_suffix' => '',
                'bookingpress_minimum_invoice_length' => 5,
                'bookingpress_hide_discount_raw' => 'false',
                'bookingpress_hide_tax_raw' => 'false',
                'bookingpress_invoice_due_days' => 30,
                'service_heading_text' => 'Service',
                'date_heading_text' => 'Date',
                'provider_heading_text' => 'Provider',
                'price_heading_text' => 'Price',
                'subtotal_heading_text' => 'SubTotal',
                'discount_heading_text' => 'Discount',
                'tax_heading_text' => 'Tax',
                'total_heading_text' => 'Total',
                'bookingpress_company_logo_text' => '{company_logo}',
                'bookingpress_invoice_heading_text' => 'INVOICE',
                'bookingpress_invoice_details_top'  => '',                
                'bookingpress_company_details_text' => '{company_name}<br/>{company_address}<br/>{company_website}<br/>{company_phone}',
                'bookingpress_invoice_details_text' => 'Invoice# {invoice_number}<br/> Date: {invoice_date} <br/> Due date: {invoice_due_date}', 
                'bookingpress_invoice_details_down' => '', 
                'bookingpress_billing_details_text' =>  'BILL TO:<br/>{customer_fullname}<br/>{customer_firstname}<br/>{customer_lastname}<br>{customer_email}<br/>{customer_phone}',
                'bookingpress_billing_details_right' => '',
                'bookingpress_invoice_thankyou' => 'Thank you for your visit',
            );     
            $bookingpress_install_default_data                   = array(
                'invoice_setting'      => $bookingpress_install_default_invoice_settings_data,
            );      
            foreach ( $bookingpress_install_default_data as $bookingpress_default_data_key => $bookingpress_default_data_val ) {
                $bookingpress_setting_type = $bookingpress_default_data_key;
                foreach ( $bookingpress_default_data_val as $bookingpress_default_data_val_key => $bookingpress_default_data_val2 ) {
                    $bookingpress_insert_data = array(
                        'setting_name'  => $bookingpress_default_data_val_key,
                        'setting_value' => $bookingpress_default_data_val2,
                        'setting_type'  => $bookingpress_setting_type,
                        'updated_at'    => current_time( 'mysql' ),
                    );
                    $bookingpress_inserted_id = $wpdb->insert( $tbl_bookingpress_settings, $bookingpress_insert_data );
                }
            }
            $bookingpress_invoice_html = '
            <html>
                <head>
                    <style>@import url(https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap);body{margin:0;padding:0;font-family:Poppins,sans-serif}.page{width:700px;min-height:600px;padding:20px;margin:0 auto;background:#fff}</style>
                </head>
                <body>
                    <div style="font-family:Poppins,sans-serif;padding:20px">
                        <div>
                            <table width="800" style="border-spacing:0;border-collapse:collapse">
                                <tr>
                                    <td colspan="2" style="width:120px;padding-bottom:20px;font-size:16px;font-weight:400">{company_logo}</td>
                                    <td colspan="2" style="font-size:16px;line-height:20px;color:#202c45" align="right"><b>INVOICE</b></td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="padding-bottom:16px;font-size:15px;line-height:24px;font-weight:400;color:#202c45">
                                        <div style="width:200px">{company_name}<br>{company_address}<br>{company_website}<br>{company_phone}</div>
                                    </td>
                                    <td colspan="2" align="right" style="padding-bottom:16px;font-size:15px;line-height:24px;font-weight:400;color:#202c45">Invoice: #{invoice_number}<br>Date: {invoice_date}<br>Due Date: {invoice_due_date}</td>
                                </tr>
                                <tr>
                                    <td colspan="4">
                                        <hr>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="padding-bottom:16px;padding-top:16px;font-size:15px;line-height:24px;font-weight:400;color:#202c45">
                                        <div style="width:200px"><b>BILL TO:</b><br><br>{customer_fullname}<br>{customer_firstname}<br>{customer_lastname}<br>{customer_email}<br>{customer_phone}</div>
                                    </td>
                                </tr>
                            </table>
                            <table style="border-spacing:0;border:1px solid #dce4f5;border-collapse:collapse;margin-top:32px" width="800">
                                <tr>
                                    <td style="width:200px;border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:14px;color:#202c45"><b>Service</b></td>
                                    <td style="width:200px;border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:14px;color:#202c45"><b>Date</b></td>
                                    <td style="width:200px;border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:14px;color:#202c45"><b>Staff</b></td>
                                    <td style="width:200px;border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:14px;color:#202c45"><b>Price</b></td>
                                </tr>
                                <tr>
                                    <td style="width:200px;border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:14px;font-weight:400;color:#202c45">{service_name}</td>
                                    <td style="width:200px;border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:14px;font-weight:400;color:#202c45">{appointment_date}</td>
                                    <td style="width:200px;border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:14px;font-weight:400;color:#202c45">{staffmember_name}</td>
                                    <td style="width:200px;border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:14px;font-weight:400;color:#202c45" align="right">{service_price}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" align="right" style="border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:14px;font-weight:400;color:#202c45">Subtotal</td>
                                    <td colspan="2" style="border-bottom:1px solid #dce4f5;padding:6px 12px;font-size:14px;font-weight:400;color:#202c45" align="right">{subtotal_amt}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" align="right" style="border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:14px;font-weight:400;color:#202c45">Discount</td>
                                    <td colspan="2" style="border-bottom:1px solid #dce4f5;padding:6px 12px;font-size:14px;font-weight:400;color:#202c45" align="right">{discount_amt}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" align="right" style="border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:14px;font-weight:400;color:#202c45">Tax</td>
                                    <td colspan="2" style="border-bottom:1px solid #dce4f5;padding:6px 12px;font-size:14px;font-weight:400;color:#202c45" align="right">{tax_amt}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" align="right" style="border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:15px;color:#202c45"><b>Total</b></td>
                                    <td colspan="2" align="right" style="border-bottom:1px solid #dce4f5;padding:6px 12px;font-size:15px;color:#202c45"><b>{total_amt}</b></td>
                                </tr>
                                <tr>
                                    <td colspan="3" align="right" style="border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:15px;color:#202c45"><b>Paid</b></td>
                                    <td colspan="2" align="right" style="border-bottom:1px solid #dce4f5;padding:6px 12px;font-size:15px;color:#202c45"><b>{paid_amt}</b></td>
                                </tr>
                                <tr>
                                    <td colspan="3" align="right" style="border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:15px;color:#202c45"><b>Due</b></td>
                                    <td colspan="2" align="right" style="border-bottom:1px solid #dce4f5;padding:6px 12px;font-size:15px;color:#202c45"><b>{due_amt}</b></td>
                                </tr>
                            </table>
                            <table width="800" style="margin-top:20px">
                                <tr>
                                    <td style="padding:6px 12px;font-size:15px;color:#202c45" align="center"><b>THANK YOU FOR VISIT</b></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </body>
            </html>';
            update_option('bookingpress_invoice_html_format',$bookingpress_invoice_html);

            // Import basic fonts

            $source_dir = BOOKINGPRESS_INVOICE_LIBRARY_DIR . '/mpdf/vendor/mpdf/mpdf/ttfonts';
            $destination_dir = BOOKINGPRESS_INVOICE_FONT_DIR;

            if( !is_dir( $destination_dir ) ){
                wp_mkdir_p( $destination_dir );
            }

            if( is_dir( $source_dir ) ){
                if( $dh = opendir( $source_dir ) ){
                    while( ($file = readdir($dh)) !== false ){
                        if( '.' != $file && '..' != $file ){
                            $source = $source_dir.'/'.$file;
                            $target = $destination_dir . '/' .$file;
                            try{
                                copy( $source, $target );
                            } catch(Exception $e){
                                //destination directory does not have permission
                            }
                        }
                    }
                }
            }

            // phpcs:ignore
            $wpdb->query("ALTER TABLE {$tbl_bookingpress_notifications} ADD bookingpress_email_pdf_attachment_status INT(1) DEFAULT 0 AFTER bookingpress_notification_duration_unit");
        }
    }
}
register_activation_hook( __FILE__, 'bpa_check_invoice_lite_version' );


register_uninstall_hook(__FILE__, 'invoice_uninstall');
function invoice_uninstall(){
    global $wpdb, $tbl_bookingpress_notifications;
    $get_column = $wpdb->get_var( $wpdb->prepare( "SHOW COLUMNS FROM {$tbl_bookingpress_notifications} LIKE %s", 'bookingpress_email_pdf_attachment_status' ) ); // phpcs:ignore
    if( 'bookingpress_email_pdf_attachment_status' == $get_column ){
        $wpdb->query( "ALTER TABLE {$tbl_bookingpress_notifications} DROP COLUMN bookingpress_email_pdf_attachment_status" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm
    }
    delete_option('bookingpress_invoice_version');
    delete_option('bkp_invoice_license_key');
    delete_option('bkp_invoice_license_package');
    delete_option('bkp_invoice_license_status');
    delete_option('bkp_invoice_license_data_activate_response');
}

$bookingpress_db_invoice_version = get_option('bookingpress_invoice_version');
if( !empty($bookingpress_db_invoice_version) && version_compare( $bookingpress_db_invoice_version, '2.2', '<' ) ){
    $bookingpress_load_invoice_upgrade_file = BOOKINGPRESS_INVOICE_DIR . '/core/views/upgrade_latest_invoice_data.php';
    include $bookingpress_load_invoice_upgrade_file;
}

if (!empty($bpa_lite_plugin_version) && version_compare( $bpa_lite_plugin_version, '1.0.42', '>=' ) && file_exists( BOOKINGPRESS_INVOICE_DIR . '/autoload.php')) {
    require_once BOOKINGPRESS_INVOICE_DIR . '/autoload.php';
}