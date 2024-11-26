<?php

if (!class_exists('bookingpress_invoice') && class_exists('BookingPress_Core') ) {

	class bookingpress_invoice Extends BookingPress_Core {

		function __construct() {
            add_action( 'admin_notices', array( $this, 'bookingpress_admin_notices' ) );
            if( !function_exists('is_plugin_active') ){
                include_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            if(is_plugin_active('bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php')){

                add_action('init', array($this, 'bookingpress_load_mpdf_library'));
                add_action( 'admin_enqueue_scripts', array( $this, 'set_css' ), 11 );
                add_action( 'admin_enqueue_scripts', array( $this, 'set_js' ), 11 );

                add_action('bookingpress_settings_add_dynamic_on_load_method',array($this,'bookingpress_settings_add_dynamic_on_load_method_func'));            
                add_action('bookingpress_dynamic_get_settings_data',array($this,'bookingpress_dynamic_get_settings_data_func'));

                add_filter( 'bookingpress_add_setting_dynamic_data_fields', array( $this, 'bookingpress_add_setting_dynamic_data_fields_func' ), 11);
                add_filter('bookingpress_general_settings_add_tab_filter',array($this,'bookingpress_general_settings_add_tab_filter_func'));

                add_action('bookingpress_add_setting_dynamic_vue_methods',array($this,'bookingpress_add_setting_dynamic_vue_methods_func'),11);
                add_action ('wp_ajax_bookingpress_save_invoice_settings_data',array($this,'bookingpress_save_invoice_settings_data_func'));

                add_action ('wp_ajax_bookingpress_reset_invoice_counter',array($this,'bookingpress_reset_invoice_counter_func'));
                add_action('bookingpress_admin_vue_data_variables_script', array($this, 'bookingpress_admin_vue_data_variable_script_func'));

                add_filter('bookingpress_payment_add_view_field',array($this,'bookingpress_payment_add_view_field_func'),11,2);
                add_filter('bookingpress_modify_appointment_data', array($this, 'bookingpress_modify_appointment_data_func'), 10, 1);
                add_filter('bookingpress_addon_list_data_filter',array($this,'bookingpress_addon_list_data_filter_func'));            

                add_filter('bookingpress_front_appointment_add_dynamic_data',array($this,'bookingpress_front_appointment_add_dynamic_data_func'));
                add_filter('booingpress_front_get_customer_appointment_data_filter',array($this,'booingpress_front_get_customer_appointment_data_filter_func')); 

                add_filter('bookingpress_add_dynamic_notification_data_fields',array($this,'bookingpress_add_dynamic_notification_data_fields_func')); 

                add_filter('bookingpress_email_notification_attachment',array($this,'bookingpress_email_notification_attachment_func'),10,6);
                add_filter('bookingpress_get_email_template_details_filter',array($this,'bookingpress_get_email_template_details_filter'),10,2);

                add_action('bookingpress_add_email_notification_data',array($this,'bookingpress_add_email_notification_data_func'));            
                add_filter('bookingpress_save_email_notification_data_filter',array($this,'bookingpress_save_email_notification_data_filter_func'),10,2);

                add_filter('bookingpress_get_notifiacation_data_filter',array($this,'bookingpress_get_notifiacation_data_filter_func'));
                add_action('bookingpress_email_notification_get_data',array($this,'bookingpress_email_notification_get_data_func'));

                add_action('bookingpress_add_email_notification_section',array($this,'bookingpress_add_email_notification_section_func'),10);
                add_action('bookingpress_add_my_appointment_data_fields',array($this,'bookingpress_add_my_appointment_data_fields_func'));

                
                add_filter('bookingpress_payment_list_add_action_button',array($this,'bookingpress_payment_list_add_action_button_func'), 10, 1);
                add_action('bookingpress_add_dynamic_buttons_for_view_payments', array($this, 'bookingpress_add_dynamic_buttons_for_view_payments_func'));
                add_action('bookingpress_add_dynamic_buttons_for_view_appointments', array($this, 'bookingpress_add_dynamic_buttons_for_view_appointments_func'));
                add_action('bookingpress_pro_add_dynamic_vue_methods', array($this, 'bookingpress_pro_add_dynamic_vue_methods_func'));
                add_action('bookingpress_dashboard_add_dynamic_vue_methods', array($this, 'bookingpress_dashboard_add_dynamic_vue_methods_func'));
                add_action('bookingpress_appointment_add_dynamic_vue_methods', array($this, 'bookingpress_appointment_add_dynamic_vue_methods_func'));
                add_action('bookingpress_appointment_list_add_action_button', array($this, 'bookingpress_appointment_list_add_action_button_func'));

                //Modify My Appointment data
                add_filter('bookingpress_modify_my_appointments_data_externally', array($this, 'bookingpress_modify_my_appointments_data_externally_func'), 10, 1);
                add_action('bookingpress_add_invoice_btn_expand_details', array($this, 'bookingpress_add_invoice_btn_expand_details_func'));
                add_action('bookingpress_add_invoice_action_btns', array($this, 'bookingpress_add_invoice_action_btns_func'));
                add_action('bookingpress_pro_add_customer_panel_dynamic_methods', array($this, 'bookingpress_pro_add_customer_panel_dynamic_methods_func'));

                //Modify invoice id externally
                add_filter('bookingpress_modify_invoice_id_externally', array($this, 'bookingpress_modify_invoice_id_externally_func'));

                add_filter('bookingpress_customize_add_dynamic_data_fields',array($this,'bookingpress_customize_add_dynamic_data_fields_func'));
                add_filter('bookingpress_get_my_booking_customize_data_filter',array($this,'bookingpress_get_my_booking_customize_data_filter_func'));                


                //Change read more link
                add_action('bookingpress_modify_readmore_link', array($this, 'bookingpress_modify_readmore_link_func'), 11);

                add_filter('bookingpress_modify_capability_data', array($this, 'bookingpress_modify_capability_data_func'), 11, 1);
                if(is_plugin_active('bookingpress-multilanguage/bookingpress-multilanguage.php')) {
					add_filter('bookingpress_modified_language_translate_fields',array($this,'bookingpress_modified_language_translate_fields_func'),10);
                    add_filter('bookingpress_modified_customize_my_booking_language_translate_fields',array($this,'bookingpress_modified_customize_my_booking_language_translate_fields_func'),10);
                    add_filter('bookingpress_modified_language_translate_fields_section',array($this,'bookingpress_modified_language_translate_fields_section_func'),10);
				}
                //add action for documentation link
                add_action( 'bpa_add_extra_tab_outside_func', array( $this,'bpa_add_extra_tab_outside_func_arr'));
            }

            add_action('activated_plugin',array($this,'bookingpress_is_invoice_addon_activated'),11,2);

            add_action( 'admin_init', array( $this, 'bookingpress_invoice_upgrade_data' ) );

        }
	
        /*Multi language addon filter */
        function bookingpress_modified_language_translate_fields_func($bookingpress_all_language_translation_fields){
            $bookingpress_invoice_language_translation_fields = array(                
                'invoice_button_label' => array('field_type'=>'text','field_label'=>__('Download invoice button label', 'bookingpress-invoice'),'save_field_type'=>'booking_my_booking'),                 
			);  
			$bookingpress_all_language_translation_fields['customized_my_booking_field_labels'] = array_merge($bookingpress_all_language_translation_fields['customized_my_booking_field_labels'], $bookingpress_invoice_language_translation_fields);    		
            $bookingpress_invoice_html_language_translation_fields = array('invoice_setting' => 
            array('bookingpress_invoice_html_format' => array('field_type'=>'textarea','field_label'=>__('Invoice', 'bookingpress-invoice'),'save_field_type'=>'invoice_setting'))
			); 
            $bookingpress_all_language_translation_fields = array_merge($bookingpress_all_language_translation_fields, $bookingpress_invoice_html_language_translation_fields);
            return $bookingpress_all_language_translation_fields;
		}

        /**
         * Function for add language new section
         *
         * @param  mixed $bookingpress_all_language_translation_fields_section
         * @return void
         */
        function bookingpress_modified_language_translate_fields_section_func($bookingpress_all_language_translation_fields_section){
            $bookingpress_invoice_section_added = array('invoice_setting' => __('Invoice', 'bookingpress-invoice') );
            $bookingpress_all_language_translation_fields_section = array_merge($bookingpress_all_language_translation_fields_section,$bookingpress_invoice_section_added);
            return $bookingpress_all_language_translation_fields_section;
        }

        function bookingpress_modified_customize_my_booking_language_translate_fields_func($bookingpress_customize_my_booking_language_translate_fields)
        {
            $bookingpress_invoice_language_translation_fields = array(                
                'invoice_button_label' => array('field_type'=>'text','field_label'=>__('Download invoice button label', 'bookingpress-invoice'),'save_field_type'=>'booking_my_booking'),
			);  
            $bookingpress_customize_my_booking_language_translate_fields['customized_my_booking_field_labels'] = array_merge($bookingpress_customize_my_booking_language_translate_fields['customized_my_booking_field_labels'], $bookingpress_invoice_language_translation_fields);
            return $bookingpress_customize_my_booking_language_translate_fields;
        }  	
	
	function bpa_add_extra_tab_outside_func_arr(){ ?>

            if( bpa_get_page == 'bookingpress_settings'){ 
                if( selected_tab_name == 'invoice_settings'){
                    vm.openNeedHelper("list_invoice_settings", "invoice_settings", "Invoice Settings");
                    vm.bpa_fab_floating_btn = 0; 

                } else if(null == selected_tab_name && 'invoice_settings' == bpa_get_setting_page){

                    console.log( selected_tab_name );
                    console.log( bpa_get_setting_page );
                    
                    vm.openNeedHelper("list_invoice_settings", "invoice_settings", "Invoice Settings");
                    vm.bpa_fab_floating_btn = 0; 
                }
            }
        <?php }
	function bookingpress_modify_capability_data_func($bpa_caps){
            $bpa_caps['bookingpress_settings'][] = 'save_invoice_settings_data';
            $bpa_caps['bookingpress_settings'][] = 'reset_invoice_counter';
            return $bpa_caps;
        }

        function bookingpress_modify_readmore_link_func(){
            ?>
            var selected_tab = sessionStorage.getItem("current_tabname");
            if(selected_tab == "invoice_settings"){
                read_more_link = "https://www.bookingpressplugin.com/documents/invoice-addon/";
            }
            <?php
        }


        function bookingpress_is_invoice_addon_activated($plugin,$network_activation)
        {              
            $myaddon_name = "bookingpress-invoice/bookingpress-invoice.php";

            if($plugin == $myaddon_name)
            {
                if(!(is_plugin_active('bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php')))
                {
                    deactivate_plugins($myaddon_name, FALSE);
                    $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name);
                    $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Invoice Add-on', 'bookingpress-invoice');
					$bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-invoice'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
					wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                    die;
                }

                $license = trim( get_option( 'bkp_license_key' ) );
                $package = trim( get_option( 'bkp_license_package' ) );

                if( '' === $license || false === $license ) 
                {
                    deactivate_plugins($myaddon_name, FALSE);
                    $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name);
                    $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Invoice Add-on', 'bookingpress-invoice');
					$bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-invoice'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
					wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                    die;
                }
                else
                {
                    $store_url = BOOKINGPRESS_INVOICE_STORE_URL;
                    $api_params = array(
                        'edd_action' => 'check_license',
                        'license' => $license,
                        'item_id'  => $package,
                        //'item_name' => urlencode( $item_name ),
                        'url' => home_url()
                    );
                    $response = wp_remote_post( $store_url, array( 'body' => $api_params, 'timeout' => 15, 'sslverify' => false ) );
                    if ( is_wp_error( $response ) ) {
                        return false;
                    }
        
                    $license_data = json_decode( wp_remote_retrieve_body( $response ) );
                    $license_data_string =  wp_remote_retrieve_body( $response );
        
                    $message = '';

                    if ( true === $license_data->success ) 
                    {
                        if($license_data->license != "valid")
                        {
                            deactivate_plugins($myaddon_name, FALSE);
                            $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name);
                            $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Invoice Add-on', 'bookingpress-invoice');
                            $bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-invoice'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
                            wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                            die;
                        }

                    }
                    else
                    {
                        deactivate_plugins($myaddon_name, FALSE);
                        $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name);
                        $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Invoice Add-on', 'bookingpress-invoice');
                        $bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-invoice'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
                        wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                        die;
                    }
                }
            }
        }

        function bookingpress_invoice_upgrade_data(){
            global $BookingPress, $bookingpress_invoice_version;
            $bookingpress_db_invoice_version = get_option('bookingpress_invoice_version', true);

            if( version_compare( $bookingpress_db_invoice_version, '2.2', '<' ) ){
                $bookingpress_load_invoice_upgrade_file = BOOKINGPRESS_INVOICE_DIR . '/core/views/upgrade_latest_invoice_data.php';
                include $bookingpress_load_invoice_upgrade_file;
                $BookingPress->bookingpress_send_anonymous_data_cron();
            }
        }

        function bookingpress_customize_add_dynamic_data_fields_func($bookingpress_customize_vue_data_fields) {
            $bookingpress_customize_vue_data_fields['is_invoice_activated'] = $this->is_addon_activated();
            $bookingpress_customize_vue_data_fields['invoice_button_label'] = '';                        
            return $bookingpress_customize_vue_data_fields;
        }

        function bookingpress_get_my_booking_customize_data_filter_func($bookingpress_my_booking_field_settings){
            $bookingpress_my_booking_field_settings['invoice_button_label'] = '';
            return $bookingpress_my_booking_field_settings;
        }

        function bookingpress_modify_invoice_id_externally_func($bookingpress_invoice_id){
            global $BookingPress;
            if(!empty($bookingpress_invoice_id)){
                $bookingpress_invoice_suffix_prefix = $BookingPress->bookingpress_get_settings('bookingpress_invoice_suffix_prefix','invoice_setting'); 
                $bookingpress_invoice_suffix_prefix = !empty($bookingpress_invoice_suffix_prefix) ? $bookingpress_invoice_suffix_prefix == 'true' ? true : false : false;

                if( !empty($bookingpress_invoice_id) && $bookingpress_invoice_suffix_prefix == true) {
                    $bookingpress_invoice_prefix = $BookingPress->bookingpress_get_settings('bookingpress_invoice_prefix','invoice_setting'); 
                    $bookingpress_invoice_suffix = $BookingPress->bookingpress_get_settings('bookingpress_invoice_suffix','invoice_setting');
                    $bookingpress_minimum_invoice_length = $BookingPress->bookingpress_get_settings('bookingpress_minimum_invoice_length','invoice_setting');

                    if(!empty($bookingpress_minimum_invoice_length)){
                        $bookingpress_minimum_invoice_length = intval($bookingpress_minimum_invoice_length);
                        $bookingpress_invoice_id = str_pad($bookingpress_invoice_id, $bookingpress_minimum_invoice_length, "0", STR_PAD_LEFT);
                    }

                    $bookingpress_invoice_id = $bookingpress_invoice_prefix.$bookingpress_invoice_id.$bookingpress_invoice_suffix;
                }
            }
            return $bookingpress_invoice_id;
        }

        function bookingpress_pro_add_customer_panel_dynamic_methods_func(){
            ?>
                bookingpress_redirect_to_invoice(bookingpress_invoice_url){
                    window.open(bookingpress_invoice_url, '_blank').focus();
                },
            <?php
        }

        function bookingpress_add_invoice_action_btns_func(){
            ?>
                <el-tooltip v-if="scope.row.bpa_display_invoice_btn == true" effect="dark" content="<?php esc_html_e('Invoice', 'bookingpress-invoice'); ?>" placement="top"  open-delay="300">
                    <el-button class="bpa-front-btn bpa-front-btn--icon-without-box bpa-front-invoice-btn" @click="bookingpress_redirect_to_invoice(scope.row.invoice_pdf_url)">
                        <svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24"><path d="M0,0h24v24H0V0z" fill="none"/><g><path d="M14,9h-4C9.45,9,9,8.55,9,8v0c0-0.55,0.45-1,1-1h4c0.55,0,1,0.45,1,1v0C15,8.55,14.55,9,14,9z"/><path d="M14,12h-4c-0.55,0-1-0.45-1-1v0c0-0.55,0.45-1,1-1h4c0.55,0,1,0.45,1,1v0C15,11.55,14.55,12,14,12z"/><path d="M19.5,3.5L18,2l-1.5,1.5L15,2l-1.5,1.5L12,2l-1.5,1.5L9,2L7.5,3.5L6,2v14H4c-0.55,0-1,0.45-1,1v2c0,1.66,1.34,3,3,3h12 c1.66,0,3-1.34,3-3V2L19.5,3.5z M15,20H6c-0.55,0-1-0.45-1-1v-1h3h4h3V20z M19,19c0,0.55-0.45,1-1,1s-1-0.45-1-1v-2 c0-0.55-0.45-1-1-1h-2h-2H8V5h11V19z"/><circle cx="17" cy="8" r="1"/><circle cx="17" cy="11" r="1"/></g></svg>
                    </el-button>
                </el-tooltip>
            <?php
        }

        function bookingpress_add_invoice_btn_expand_details_func(){
            global $BookingPress;
            $invoice_button_label = $BookingPress->bookingpress_get_customize_settings('invoice_button_label', 'booking_my_booking');
            ?>
                <el-button v-if="scope.row.bpa_display_invoice_btn == true" class="bpa-front-btn bpa-front-btn__small bpa-front-invoice-btn" @click="bookingpress_redirect_to_invoice(scope.row.invoice_pdf_url)">
                    <svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24"><path d="M0,0h24v24H0V0z" fill="none"/><g><path d="M14,9h-4C9.45,9,9,8.55,9,8v0c0-0.55,0.45-1,1-1h4c0.55,0,1,0.45,1,1v0C15,8.55,14.55,9,14,9z"/><path d="M14,12h-4c-0.55,0-1-0.45-1-1v0c0-0.55,0.45-1,1-1h4c0.55,0,1,0.45,1,1v0C15,11.55,14.55,12,14,12z"/><path d="M19.5,3.5L18,2l-1.5,1.5L15,2l-1.5,1.5L12,2l-1.5,1.5L9,2L7.5,3.5L6,2v14H4c-0.55,0-1,0.45-1,1v2c0,1.66,1.34,3,3,3h12 c1.66,0,3-1.34,3-3V2L19.5,3.5z M15,20H6c-0.55,0-1-0.45-1-1v-1h3h4h3V20z M19,19c0,0.55-0.45,1-1,1s-1-0.45-1-1v-2 c0-0.55-0.45-1-1-1h-2h-2H8V5h11V19z"/><circle cx="17" cy="8" r="1"/><circle cx="17" cy="11" r="1"/></g></svg>
                    <?php echo esc_html($invoice_button_label); ?>
                </el-button>
            <?php
        }

        function bookingpress_modify_my_appointments_data_externally_func($bookingpress_appointments_data){
            if(!empty($bookingpress_appointments_data)){
                $bookingpress_payment_log_id = $bookingpress_appointments_data['bookingpress_payment_id'];
                $bookingpress_invoice_pdf_url = $this->bookingpress_generate_invoice_url($bookingpress_payment_log_id);
                $bookingpress_appointments_data['invoice_pdf_url'] = $bookingpress_invoice_pdf_url;
                $bookingpress_appointments_data['bpa_display_invoice_btn'] = true;
            }
            return $bookingpress_appointments_data;
        }

        function bookingpress_appointment_list_add_action_button_func(){
            global $BookingPressPro;
            if ( $BookingPressPro->bookingpress_check_capability( 'bookingpress_payments' ) ) {            
            ?>    
                <el-tooltip effect="dark" content="" placement="top" open-delay="300">
                    <div slot="content">
                        <span><?php esc_html_e( 'Invoice', 'bookingpress-invoice' ) ?></span>
                    </div>
		    <el-button @click="bookingpress_redirect_to_invoice(scope.row.bookingpress_invoice_pdf_url)" :class="(current_screen_size == 'mobile') ? 'bpa-btn bpa-btn bpa-btn__filled-light' : 'bpa-btn bpa-btn--icon-without-box'">
                        <span class="material-icons-round">receipt_long</span>
                    </el-button>
                </el-tooltip>
            <?php
            }
        }

        function bookingpress_appointment_add_dynamic_vue_methods_func(){
            ?>
                bookingpress_redirect_to_invoice(bookingpress_invoice_url){
                    window.open(bookingpress_invoice_url, '_blank').focus();
                },
            <?php
        }

        function bookingpress_pro_add_dynamic_vue_methods_func(){
            ?>
                bookingpress_redirect_to_invoice(bookingpress_invoice_url){
                    window.open(bookingpress_invoice_url, '_blank').focus();
                },
            <?php
        }

        function bookingpress_dashboard_add_dynamic_vue_methods_func(){
            ?>
                bookingpress_redirect_to_invoice(bookingpress_invoice_url){
                    window.open(bookingpress_invoice_url, '_blank').focus();
                },
            <?php
        }

        function bookingpress_add_dynamic_buttons_for_view_appointments_func(){
            global $BookingPressPro;
            if ( $BookingPressPro->bookingpress_check_capability( 'bookingpress_payments' ) ) {            
            ?>            
                <el-button class="bpa-btn" @click="bookingpress_redirect_to_invoice(scope.row.bookingpress_invoice_pdf_url)">
                    <span class="material-icons-round">receipt_long</span> 
                    <?php esc_html_e( 'Invoice', 'bookingpress-invoice' ); ?>
                </el-button>
            <?php
            }
        }

        function bookingpress_add_dynamic_buttons_for_view_payments_func(){
            ?>
            <el-button class="bpa-btn" @click="bookingpress_redirect_to_invoice(scope.row.bookingpress_invoice_pdf_url)">
                <span class="material-icons-round">receipt_long</span> 
                <?php esc_html_e( 'Invoice', 'bookingpress-invoice' ); ?>
            </el-button>
            <?php
        }

        public function is_addon_activated(){
            $bookingpress_invoice_version = get_option('bookingpress_invoice_version');
            return !empty($bookingpress_invoice_version) ? 1 : 0;
        }

        function bookingpress_import_basic_fonts_files(){

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
        }  

        function  bookingpress_install_default_invoice_settings_data() {                      
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
                    $this->bookingpress_invoice_update_settings( $bookingpress_default_data_val_key, $bookingpress_setting_type, $bookingpress_default_data_val2 );
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
        }

        function set_css() {
            global $bookingpress_slugs;
            wp_register_style( 'bookingpress_invoice_admin_css', BOOKINGPRESS_INVOICE_URL . '/css/bookingpress_invoice_admin.css', array(), 
                BOOKINGPRESS_INVOICE_VERSION );

            if ( isset( $_REQUEST['page'] ) && (sanitize_text_field( $_REQUEST['page'] ) == 'bookingpress_settings' || sanitize_text_field( $_REQUEST['page'] ) == 'bookingpress_payments' || sanitize_text_field( $_REQUEST['page'] ) == 'bookingpress_appointments' || sanitize_text_field( $_REQUEST['page'] ) == 'bookingpress') ) {
                wp_enqueue_style('bookingpress_invoice_admin_css');
            }
        }

        function set_js(){
            global $bookingpress_slugs;
            wp_register_script('bookingpress_invoice_admin_js', BOOKINGPRESS_INVOICE_URL . '/js/bookingpress_invoice_admin.js', array('bookingpress_text_editor_js'), BOOKINGPRESS_INVOICE_VERSION, true);
            if ( isset( $_REQUEST['page'] ) && (sanitize_text_field( $_REQUEST['page'] ) == 'bookingpress_settings')) {
                wp_enqueue_script('bookingpress_invoice_admin_js');
            }
        }

        function bookingpress_invoice_is_compatible() {            

            $bookingpress_get_php_version = (function_exists('phpversion')) ? phpversion() : 0; 

            if( version_compare($bookingpress_get_php_version, '7.1', '>=') ) {
                return true;
            } else {
                return false;
            }
        }

        function bookingpress_admin_notices() {            
            global $pagenow, $bookingpress_slugs,$bookingpress_invoice ;    
            if($pagenow == 'plugins.php' || (isset($_REQUEST['page']) && in_array($_REQUEST['page'], (array) $bookingpress_slugs))) {
                $bookingpress_get_php_version = (function_exists('phpversion')) ? phpversion() : 0;
                if(!$bookingpress_invoice->bookingpress_invoice_is_compatible()) {
                    echo '<div class="notice notice-warning" style="display:block;">';
                    echo '<p>'.esc_html__('mPDF Library for BookingPress invoice required Minimum PHP version 7.1 or greater.', 'bookingpress-invoice').'</p>';
                    echo '</div>';
                }
                if(!is_plugin_active('bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php')){
                    echo "<div class='notice notice-warning'><p>" . esc_html__('Bookingpress - Invoice plugin requires Bookingpress Premium Plugin installed and active.', 'bookingpress-invoice') . "</p></div>";
                }
                
                if( file_exists( WP_PLUGIN_DIR . '/bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php' ) ){
                    $bpa_pro_plugin_info = get_plugin_data( WP_PLUGIN_DIR . '/bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php' );
                    $bpa_pro_plugin_version = $bpa_pro_plugin_info['Version'];

                    if( version_compare( $bpa_pro_plugin_version, '1.2', '<' ) ){
                        echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("it's highly recommended to update the BookingPress Premium Plugin to version 1.2 or higher in order to use the BookingPress Invoice plugin", "bookingpress-invoice")."</p></div>";
                    }
                    if( version_compare( $bpa_pro_plugin_version, '1.6', '<' ) ){
                        echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("it's highly recommended to update the BookingPress Premium Plugin to version 1.6 or higher in order to use the BookingPress Invoice plugin", "bookingpress-invoice").".</p></div>";
                    }
                }
            }
        }

        function bookingpress_add_dynamic_notification_data_fields_func($bookingpress_notification_vue_methods_data) {            

            $bookingpress_notification_vue_methods_data['bookingpress_email_pdf_attachment_status'] = 0;
            return $bookingpress_notification_vue_methods_data;
        }

        function bookingpress_email_notification_get_data_func() {
            ?>
            if(bookingpress_return_notification_data.bookingpress_email_pdf_attachment_status != 'undefined') {
                vm.bookingpress_email_pdf_attachment_status = bookingpress_return_notification_data.bookingpress_email_pdf_attachment_status;
            }
            <?php
        }

        function bookingpress_get_notifiacation_data_filter_func($bookingpress_exist_record_data){

            if(isset($bookingpress_exist_record_data['bookingpress_email_pdf_attachment_status']) ) {
                $bookingpress_exist_record_data['bookingpress_email_pdf_attachment_status'] = $bookingpress_exist_record_data['bookingpress_email_pdf_attachment_status'] == '1' ? true : false;
            }
            return $bookingpress_exist_record_data;
        }

        function bookingpress_addon_list_data_filter_func($bookingpress_body_res){
            global $bookingpress_slugs;
            if(!empty($bookingpress_body_res)) {
                foreach($bookingpress_body_res as $bookingpress_body_res_key =>$bookingpress_body_res_val) {
                    $bookingpress_setting_page_url = add_query_arg('page', $bookingpress_slugs->bookingpress_settings, esc_url( admin_url() . 'admin.php?page=bookingpress' ));
                    $bookingpress_invoice_config_url = add_query_arg('setting_page', 'invoice_settings', $bookingpress_setting_page_url);
                    if($bookingpress_body_res_val['addon_key'] == 'bookingpress-invoice') {
                        $bookingpress_body_res[$bookingpress_body_res_key]['addon_configure_url'] = $bookingpress_invoice_config_url;
                    }
                }
            }
            return $bookingpress_body_res;
        }  

        function bookingpress_modify_appointment_data_func($appointment_data){
            if(!empty($appointment_data)){
                foreach($appointment_data as $k => $v){
                    $bookingpress_payment_log_id = $v['payment_id'];
                    $bookingpress_invoice_pdf_url = $this->bookingpress_generate_invoice_url($bookingpress_payment_log_id);
                    $appointment_data[$k]['bookingpress_invoice_pdf_url'] = $bookingpress_invoice_pdf_url;
                }
            }
            return $appointment_data;
        }

        function bookingpress_payment_add_view_field_func($payment,$payment_log_val) {            
            $payment_log_id = !empty($payment_log_val['bookingpress_payment_log_id']) ?  intval($payment_log_val['bookingpress_payment_log_id']) : 0;
            $bookingpress_invoice_id = ! empty( $payment_log_val['bookingpress_invoice_id'] ) ? intval( $payment_log_val['bookingpress_invoice_id'] ) : 0;
            $bookingpress_invoice_id = $this->bookingpress_manipulate_invoice_id($bookingpress_invoice_id);
            $payment['bookingpress_invoice_id'] = esc_html($bookingpress_invoice_id);       
            $bookingpress_invoice_pdf_url = $this->bookingpress_generate_invoice_url($payment_log_id);
            $payment['bookingpress_invoice_pdf_url'] = $bookingpress_invoice_pdf_url;

            $payment['invoice_addon'] = $this->is_addon_activated();    
            return $payment;
        }

        function bookingpress_front_appointment_add_dynamic_data_func($bookingpress_front_appointment_vue_data_fields) {

            $bookingpress_front_appointment_vue_data_fields['is_invoice_activated'] = $this->is_addon_activated();
            return $bookingpress_front_appointment_vue_data_fields;
        } 

        function booingpress_front_get_customer_appointment_data_filter_func($appointments) {
            global $wpdb,$tbl_bookingpress_payment_logs;

            if(!empty($appointments['appointment_id'])) {
                $appointment_id = intval($appointments['appointment_id']);
                $bookingpress_payment_log_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $tbl_bookingpress_payment_logs . " WHERE `bookingpress_appointment_booking_ref`=%d",$appointment_id), ARRAY_A); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm
                if(!empty($bookingpress_payment_log_data['bookingpress_payment_log_id']) && !empty($bookingpress_payment_log_data['bookingpress_invoice_id'])) {
                    $bookingpress_invoice_id = $this->bookingpress_manipulate_invoice_id($bookingpress_payment_log_data['bookingpress_invoice_id']);
                    $appointments['bookingpress_invoice_id'] = esc_html($bookingpress_invoice_id);
                    $bookingpress_invoice_pdf_url =$this->bookingpress_generate_invoice_url($bookingpress_payment_log_data['bookingpress_payment_log_id']);                   
                    $appointments['bookingpress_invoice_pdf_url'] = $bookingpress_invoice_pdf_url;
                }
            }
            return $appointments;            
        }

        function bookingpress_generate_invoice_url($bookingpress_payment_log_id) {
            //$bookingpress_invoice_pdf_url = add_query_arg('action','bpa_pdfcreates',site_url());
            $bookingpress_invoice_pdf_url = add_query_arg('action','bpa_pdfcreates',home_url('/'));            
            $bookingpress_invoice_pdf_url = add_query_arg('invoice_id',$bookingpress_payment_log_id, $bookingpress_invoice_pdf_url);
            $bookingpress_invoice_pdf_url = wp_nonce_url($bookingpress_invoice_pdf_url,'bpa_wp_nonce') ;
            $bookingpress_invoice_pdf_url = str_replace('&amp;', '&', $bookingpress_invoice_pdf_url);                 

            return $bookingpress_invoice_pdf_url;
        }

        function bookingpress_manipulate_invoice_id($org_invoice_id) {
            global $BookingPress;
            $invoice_id = !empty($org_invoice_id) ? $org_invoice_id : 0;
            $invc_prefix = '';
            $invc_suffix = "";           
            $bookingpress_invoice_suffix_prefix = $BookingPress->bookingpress_get_settings('bookingpress_invoice_suffix_prefix','invoice_setting'); 
            $bookingpress_invoice_suffix_prefix = !empty($bookingpress_invoice_suffix_prefix) ? $bookingpress_invoice_suffix_prefix == 'true' ? true : false : false;            

            if( !empty($invoice_id) && $bookingpress_invoice_suffix_prefix == true) {
                $bookingpress_invoice_prefix = $BookingPress->bookingpress_get_settings('bookingpress_invoice_prefix','invoice_setting'); 
                $bookingpress_invoice_suffix = $BookingPress->bookingpress_get_settings('bookingpress_invoice_suffix','invoice_setting'); 
                $bookingpress_minimum_invoice_length = $BookingPress->bookingpress_get_settings('bookingpress_minimum_invoice_length','invoice_setting'); 
                $invc_prefix = isset($bookingpress_invoice_prefix) ? $bookingpress_invoice_prefix : $invc_prefix;
                $invc_suffix = isset($bookingpress_invoice_suffix) ? $bookingpress_invoice_suffix : '';
                $invc_min_digit = isset($bookingpress_minimum_invoice_length) ? $bookingpress_minimum_invoice_length : 0;
                if($invc_min_digit > 0) {
                    $invoice_id = str_pad($invoice_id, $invc_min_digit, "0", STR_PAD_LEFT);
                }
            }
            $new_invoice_id = $invc_prefix . $invoice_id . $invc_suffix;
            return $new_invoice_id;
        }       

        function bookingpress_admin_vue_data_variable_script_func() {                       
            $is_invoice_addon_activated = $this->is_addon_activated();         
            ?>
                bookingpress_return_data['is_invoice_activated'] = '<?php echo esc_html( $is_invoice_addon_activated ); ?>';                
            <?php    
        }

        function bookingpress_reset_invoice_counter_func() {
            global $BookingPress;
            $response              = array();
            $bpa_check_authorization = $this->bpa_check_authentication( 'reset_invoice_counter', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-invoice');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-invoice');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            if ( ! empty( $_POST['action'] ) && ( sanitize_text_field( $_POST['action'] ) == 'bookingpress_reset_invoice_counter' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
                $is_update_counter = $BookingPress->bookingpress_update_settings( 'bookingpress_last_invoice_id', 'invoice_setting',0 );
                if($is_update_counter) { 
                    $response['variant'] = 'success';
                    $response['title']   = esc_html__( 'Success', 'bookingpress-invoice' );
                    $response['msg']     = esc_html__( 'Invoice Counter Update successfully.', 'bookingpress-invoice' );
                }
            }
            echo json_encode( $response );
            exit();
        }        

        function bookingpress_general_settings_add_tab_filter_func($bookingpress_file_url) {

            $bookingpress_file_url[] = BOOKINGPRESS_INVOICE_VIEWS_DIR.'/settings/invoice_setting_tab.php';
            return $bookingpress_file_url;
        }

        function bookingpress_save_invoice_settings_data_func() {
            global $BookingPress;
            $response              = array();
            $bpa_check_authorization = $this->bpa_check_authentication( 'save_invoice_settings_data', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-invoice');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-invoice');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }
            if ( ! empty( $_POST ) && ! empty( $_POST['action'] ) && ( sanitize_text_field( $_POST['action'] ) == 'bookingpress_save_invoice_settings_data' ) && ! empty( $_POST['settingType'] ) ) { //phpcs:ignore
                $bookingpress_save_settings_data = (array) $_POST; //phpcs:ignore
                $bookingpress_setting_type       = sanitize_text_field( $_POST['settingType'] ); //phpcs:ignore
                $bookingpress_setting_action     = sanitize_text_field( $_POST['action'] ); //phpcs:ignore
                unset( $bookingpress_save_settings_data['settingType'] );
                unset( $bookingpress_save_settings_data['action'] );
                unset( $bookingpress_save_settings_data['_wpnonce'] );

                
                $bookingpress_response_arr = array();
                foreach ( $bookingpress_save_settings_data as $bookingpress_setting_key => $bookingpress_setting_val ) {
                    $bookingpress_res = $this->bookingpress_invoice_update_settings( $bookingpress_setting_key, $bookingpress_setting_type, $bookingpress_setting_val );
                    array_push( $bookingpress_response_arr, $bookingpress_res );
                }

                $bookingpress_invoice_html_content = !empty($_POST['invoice_html_content']) ? $_POST['invoice_html_content'] : '<html><head><style>@import url(https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap);body{margin:0;padding:0;font-family:Poppins,sans-serif}.page{width:700px;min-height:600px;padding:20px;margin:0 auto;background:#fff}</style></head><body><div style="font-family:Poppins,sans-serif;padding:20px"><div><table width="800" style="border-spacing:0;border-collapse:collapse"><tr><td colspan="2" style="width:120px;padding-bottom:20px;font-size:16px;font-weight:400">{company_logo}</td><td colspan="2" style="font-size:16px;line-height:20px;color:#202c45" align="right"><b>INVOICE</b></td></tr><tr><td colspan="2" style="padding-bottom:16px;font-size:15px;line-height:24px;font-weight:400;color:#202c45"><div style="width:200px">{company_name}<br>{company_address}<br>{company_website}<br>{company_phone}</div></td><td colspan="2" align="right" style="padding-bottom:16px;font-size:15px;line-height:24px;font-weight:400;color:#202c45">Invoice: #{invoice_number}<br>Date: {invoice_date}<br>Due Date: {invoice_due_date}</td></tr><tr><td colspan="4"><hr></td></tr><tr><td colspan="2" style="padding-bottom:16px;font-size:15px;line-height:24px;font-weight:400;color:#202c45"><div style="width:200px"><b>BILL TO:</b><br><br>{customer_fullname}<br>{customer_firstname}<br>{customer_lastname}<br>{customer_email}<br>{customer_phone}</div></td></tr></table><table style="border-spacing:0;border:1px solid #dce4f5;border-collapse:collapse;margin-top:32px" width="800"><tr><td style="width:200px;border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:14px;color:#202c45"><b>Service</b></td><td style="width:200px;border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:14px;color:#202c45"><b>Date</b></td><td style="width:200px;border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:14px;color:#202c45"><b>Staff</b></td><td style="width:200px;border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:14px;color:#202c45"><b>Price</b></td></tr><tr><td style="width:200px;border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:14px;font-weight:400;color:#202c45">{service_name}</td><td style="width:200px;border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:14px;font-weight:400;color:#202c45">{appointment_date}</td><td style="width:200px;border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:14px;font-weight:400;color:#202c45">{staffmember_name}</td><td style="width:200px;border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:14px;font-weight:400;color:#202c45" align="right">{service_price}</td></tr><tr><td colspan="3" align="right" style="border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:14px;font-weight:400;color:#202c45">Subtotal</td><td colspan="2" style="border-bottom:1px solid #dce4f5;padding:6px 12px;font-size:14px;font-weight:400;color:#202c45" align="right">{subtotal_amt}</td></tr><tr><td colspan="3" align="right" style="border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:14px;font-weight:400;color:#202c45">Discount</td><td colspan="2" style="border-bottom:1px solid #dce4f5;padding:6px 12px;font-size:14px;font-weight:400;color:#202c45" align="right">{discount_amt}</td></tr><tr><td colspan="3" align="right" style="border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:14px;font-weight:400;color:#202c45">Tax</td><td colspan="2" style="border-bottom:1px solid #dce4f5;padding:6px 12px;font-size:14px;font-weight:400;color:#202c45" align="right">{tax_amt}</td></tr><tr><td colspan="3" align="right" style="border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:15px;color:#202c45"><b>Total</b></td><td colspan="2" align="right" style="border-bottom:1px solid #dce4f5;padding:6px 12px;font-size:15px;color:#202c45"><b>{total_amt}</b></td></tr></table><table width="800" style="margin-top:20px"><tr><td style="padding:6px 12px;font-size:15px;color:#202c45" align="center"><b>THANK YOU FOR VISIT</b></td></tr></table></div></div></body></html>'; //phpcs:ignore

                update_option('bookingpress_invoice_html_format', $bookingpress_invoice_html_content);

                if ( ! in_array( '0', $bookingpress_response_arr ) ) {
                    $response['variant'] = 'success';
                    $response['title']   = esc_html__( 'Success', 'bookingpress-invoice' );
                    $response['msg']     = esc_html__( 'Settings has been updated successfully.', 'bookingpress-invoice' );
                }

                do_action('boookingpress_after_save_invoice_settings_data',$_POST); //phpcs:ignore
            }

            echo json_encode( $response );
            exit();
        }

        function bookingpress_invoice_update_settings( $setting_name, $setting_type, $setting_value = '' ) {
             global $wpdb, $tbl_bookingpress_settings;
            if ( ! empty( $setting_name ) ) {
                $bookingpress_check_record_existance = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(setting_id) FROM `{$tbl_bookingpress_settings}` WHERE setting_name = %s AND setting_type = %s", $setting_name, $setting_type) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_settings is table name defined globally. False Positive alarm
                $allowed_html = array(
                    'br' => array(),
                );                
                $bookingpress_invoice_allowd_html_key = array('bookingpress_company_details_text','bookingpress_company_logo_text','bookingpress_invoice_heading_text','bookingpress_invoice_details_top','bookingpress_invoice_details_text','bookingpress_invoice_details_down','bookingpress_billing_details_text','bookingpress_billing_details_right','bookingpress_invoice_thankyou');

                if(in_array($setting_name,$bookingpress_invoice_allowd_html_key)) {
                    $bpa_Setting_value = wp_kses( $setting_value,$allowed_html);
                } else {
                    $bpa_Setting_value = (!empty( $setting_value ) && gettype( $setting_value ) === 'boolean' ) ? $setting_value : sanitize_text_field($setting_value);
                }

                if ( $bookingpress_check_record_existance > 0 ) {
                    // If record already exists then update data.

                    $bookingpress_update_data = array(
                        'setting_value' => $bpa_Setting_value,
                        'setting_type'  => $setting_type,
                        'updated_at'    => current_time( 'mysql' ),
                    );

                    $bookingpress_update_where_condition = array(
                        'setting_name' => $setting_name,
                        'setting_type' => $setting_type,
                    );

                    $bookingpress_update_affected_rows = $wpdb->update( $tbl_bookingpress_settings, $bookingpress_update_data, $bookingpress_update_where_condition );
                    if ( $bookingpress_update_affected_rows > 0 ) {
                        wp_cache_delete( $setting_name );
                        wp_cache_set( $setting_name, $setting_value );
                        return 1;
                    }
                } else {
                    // If record not exists hen insert data.
                        $bookingpress_insert_data = array(
                            'setting_name'  => $setting_name,
                            'setting_value' => $bpa_Setting_value,
                            'setting_type'  => $setting_type,
                            'updated_at'    => current_time( 'mysql' ),
                        );
                        $bookingpress_inserted_id = $wpdb->insert( $tbl_bookingpress_settings, $bookingpress_insert_data );
                        if ( $bookingpress_inserted_id > 0 ) {
                            wp_cache_delete( $setting_name );
                            wp_cache_set( $setting_name, $setting_value );
                            return 1;
                        }
                }
            }

            return 0;
        }

        function bookingpress_add_setting_dynamic_data_fields_func($bookingpress_dynamic_setting_data_fields) {                            
            global $bookingpress_global_options;
            $bookingpress_dynamic_setting_data_fields['invoice_setting_form'] = $this->bookingpress_get_invoice_settings_data();
            $bookingpress_global_data = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_custom_fields_placeholders = json_decode($bookingpress_global_data['custom_fields_placeholders'],true);

            //substr_replace($str, $replacement, $start, $length)
            
            $bookingpress_custom_field_data = array();
            if(!empty($bookingpress_custom_fields_placeholders)) {
                foreach($bookingpress_custom_fields_placeholders as $key => $value) {
                    $tag_value = substr_replace($value['value'],'{',0,1);                          
                    $tag_value = substr_replace($tag_value, '}', strlen($tag_value)-1,strlen($tag_value)-1);
                    $bookingpress_custom_field_data[] = array( 
                        'tag_name' => $value['name'],
                        'tag_value' => $tag_value,
                    );                        
                }
            }

            $bookingpress_dynamic_setting_data_fields['bookingpress_invoice_tag_list'] = array(                
                array( 'group_tag_name' =>  __('invoice','bookingpress-invoice'),
                    'tag_details' => array(
                        array( 'tag_name' =>  '{invoice_number}',
                        ),  
                        array( 'tag_name' =>  '{invoice_date}',
                        ),
                        array( 'tag_name' =>  '{invoice_due_date}',
                        ),
                    ),    
                ),                
                array( 'group_tag_name' =>  __('company','bookingpress-invoice'),
                    'tag_details' => array(
                        array( 'tag_name' =>  '{company_logo}',
                        ),
                        array( 'tag_name' =>  '{company_name}',
                        ),
                        array( 'tag_name' =>  '{company_address}',
                        ),
                        array( 'tag_name' =>  '{company_phone}',
                        ),
                        array( 'tag_name' =>  '{company_website}'
                        ),
                    ),   
                ), 
                array( 'group_tag_name' =>  'customer',
                    'tag_details' => array(      
                        array( 'tag_name' =>  '{customer_fullname}',
                        ),  
                        array( 'tag_name' =>  '{customer_firstname}',
                        ),
                        array( 'tag_name' =>  '{customer_lastname}',
                        ),             
                        array( 'tag_name' =>  '{customer_email}',
                        ),             
                        array( 'tag_name' =>  '{customer_phone}',
                        ),
                    ),
                 ),
                 array( 'group_tag_name' =>  'service',
                    'tag_details' => array(      
                        array( 'tag_name' =>  '{service_name}',
                        ),  
                        array( 'tag_name' =>  '{service_price}',
                        ),
                        array( 'tag_name' => '{service_gross_amount}')
                    ),
                 ),
                 array( 'group_tag_name' =>  'service_extras',
                    'tag_details' => array(      
                        array( 'tag_name' =>  '{service_extras}',
                        ),  
                    ),
                ),
                 array( 'group_tag_name' =>  'appointment details',
                    'tag_details' => array(      
                        array( 'tag_name' =>  '{appointment_date}',
                        ),  
                        array( 'tag_name' =>  '{appointment_time}',
                        ),  
                    ),
                 ),
                 array( 'group_tag_name' =>  'staffmember',
                    'tag_details' => array(      
                        array( 'tag_name' =>  '{staffmember_name}',
                        ),  
                    ),
                 ),
                 array( 'group_tag_name' =>  'cart',
                    'tag_details' => array(      
                        array( 'tag_name' =>  '[BOOKINGPRESS_CART_ITEMS] [/BOOKINGPRESS_CART_ITEMS]',
                        ),
                    ),
                 ),
                 array( 'group_tag_name' =>  'other',
                    'tag_details' => array(      
                        array( 'tag_name' =>  '{deposit_amt}',
                        ),
                        array( 'tag_name' =>  '{subtotal_amt}',
                        ),  
                        array( 'tag_name' =>  '{discount_amt}',
                        ),  
                        array( 'tag_name' =>  '{coupon_code}',
                        ),  
                        array( 'tag_name' =>  '{tax_amt}',
                        ),  
                        array( 'tag_name' =>  '{total_amt}',
                        ),
                        array( 'tag_name' =>  '{service_tax_amt}',
                        ),
                        array( 'tag_name' =>  '{paid_amt}',
                        ),
                        array( 'tag_name' =>  '{due_amt}',
                        ),
                        array( 'tag_name' =>  '{number_of_person}',
                        ),
                        array( 'tag_name' =>  '{payment_method}',
                        ),
                    ),
                 ),
                 array( 'group_tag_name' =>  'custom fields',
                    'tag_details' => $bookingpress_custom_field_data,
                ),
            );            
            $bookingpress_dynamic_setting_data_fields['bookingpress_invoice_style_list'] = array(
                array(
                    'style_name' =>  '[BOLD] Text Here [/BOLD]',
                ),
                array(
                    'style_name' =>  '[ITALIC] Text Here [/ITALIC]',
                ),
                array(
                    'style_name' =>  '[STRIKE-THROUGH] Text Here [/STRIKE-THROUGH]',
                ),
                array(
                    'style_name' =>  '[COLOR=#FF0000] Text Here [/COLOR]',
                ),
                array(
                    'style_name' =>  '[FONTSIZE=10] Text Here [/FONTSIZE]',
                ),
            );

            $bookingpress_dynamic_setting_data_fields['invoice_modal_pos'] = '80px';            
            $bookingpress_dynamic_setting_data_fields['open_add_invoice_modal'] = false;            
            $bookingpress_dynamic_setting_data_fields['invoice_details'] = array(
                'invoice_data' => '',
                'invoice_type' => '',
            );
            $bookingpress_dynamic_setting_data_fields['invoice_setting_popover'] = array(
                'is_edit_service_popup' => false,
                'is_edit_date_popup' => false,            
                'is_edit_provider_popup' => false,
                'is_edit_price_popup' => false,
                'is_edit_subtotal_popup' => false,
                'is_edit_discount_popup' => false,            
                'is_edit_tax_popup' => false,
                'is_edit_total_popup' => false
            );
            $bookingpress_invoice_get_fonts_arr =  $this->bookingpress_invoice_get_fonts_arr();
            $bookingpress_font_lang = $this->bookingpress_invoice_fonts_category();

            $bookingpress_font_options_list = array();
            foreach ( $bookingpress_invoice_get_fonts_arr as $bookingpress_invoice_get_fonts_key => $bookingpress_invoice_get_fonts_val ) {
                foreach ( $bookingpress_invoice_get_fonts_val as $key => $value ) {
                    $fcat_lang='';
                    if(isset($bookingpress_font_lang[ $bookingpress_invoice_get_fonts_key ])){
                        $fcat_lang=' ('.esc_html( $bookingpress_font_lang[ $bookingpress_invoice_get_fonts_key ] ).')';
                    }
                    $bookingpress_font_options_list[] = array( 'value'=> $bookingpress_invoice_get_fonts_key,'text' =>$key.$fcat_lang   );
                }    
            } 
            $bookingpress_dynamic_setting_data_fields['bookingpress_font_options_list'] = $bookingpress_font_options_list;
            $bookingpress_dynamic_setting_data_fields['rules_invoice'] = array();      
            
            $bookingpress_dynamic_setting_data_fields['bookingpress_invoice_preview_modal'] = false;
            $bookingpress_dynamic_setting_data_fields['bookingpress_invoice_preview_html'] = '';

            return $bookingpress_dynamic_setting_data_fields;
        }

        function bookingpress_get_invoice_settings_data() {
            global $BookingPress;
            $bookingpress_invoice_suffix_prefix = $BookingPress->bookingpress_get_settings( 'bookingpress_invoice_suffix_prefix', 'invoice_setting' );            
            $bookingpress_invoice_suffix = $BookingPress->bookingpress_get_settings( 'bookingpress_invoice_suffix', 'invoice_setting' );     
            $bookingpress_invoice_prefix = $BookingPress->bookingpress_get_settings( 'bookingpress_invoice_prefix', 'invoice_setting' ); 
            $bookingpress_minimum_invoice_length = $BookingPress->bookingpress_get_settings( 'bookingpress_minimum_invoice_length', 'invoice_setting' );
            $bookingpress_hide_discount_raw = $BookingPress->bookingpress_get_settings( 'bookingpress_hide_discount_raw', 'invoice_setting' );
            $bookingpress_hide_tax_raw = $BookingPress->bookingpress_get_settings( 'bookingpress_hide_tax_raw', 'invoice_setting' );
            $bookingpress_invoice_due_days = $BookingPress->bookingpress_get_settings( 'bookingpress_invoice_due_days', 'invoice_setting' );
            $bookingpress_selected_font = $BookingPress->bookingpress_get_settings( 'bookingpress_selected_font', 'invoice_setting' );
            $service_heading_text = $BookingPress->bookingpress_get_settings( 'service_heading_text', 'invoice_setting' );
            $date_heading_text = $BookingPress->bookingpress_get_settings( 'date_heading_text', 'invoice_setting' );
            $provider_heading_text = $BookingPress->bookingpress_get_settings( 'provider_heading_text', 'invoice_setting' );
            $price_heading_text = $BookingPress->bookingpress_get_settings( 'price_heading_text', 'invoice_setting' );
            $subtotal_heading_text = $BookingPress->bookingpress_get_settings( 'subtotal_heading_text', 'invoice_setting' );
            $discount_heading_text = $BookingPress->bookingpress_get_settings( 'discount_heading_text', 'invoice_setting' );
            $tax_heading_text = $BookingPress->bookingpress_get_settings( 'tax_heading_text', 'invoice_setting' );
            $total_heading_text = $BookingPress->bookingpress_get_settings( 'total_heading_text', 'invoice_setting' );
            $bookingpress_company_logo_text = $BookingPress->bookingpress_get_settings( 'bookingpress_company_logo_text', 'invoice_setting' );
            $bookingpress_invoice_heading_text = $BookingPress->bookingpress_get_settings( 'bookingpress_invoice_heading_text', 'invoice_setting' );
            $bookingpress_invoice_details_top = $BookingPress->bookingpress_get_settings( 'bookingpress_invoice_details_top', 'invoice_setting' );
            $bookingpress_company_details_text = $BookingPress->bookingpress_get_settings( 'bookingpress_company_details_text', 'invoice_setting' );
            $bookingpress_invoice_details_text = $BookingPress->bookingpress_get_settings( 'bookingpress_invoice_details_text', 'invoice_setting' );
            $bookingpress_invoice_details_down = $BookingPress->bookingpress_get_settings( 'bookingpress_invoice_details_down', 'invoice_setting' );
            $bookingpress_billing_details_text = $BookingPress->bookingpress_get_settings( 'bookingpress_billing_details_text', 'invoice_setting' );
            $bookingpress_billing_details_right =  $BookingPress->bookingpress_get_settings( 'bookingpress_billing_details_right', 'invoice_setting' );
            $bookingpress_invoice_thankyou = $BookingPress->bookingpress_get_settings( 'bookingpress_invoice_thankyou', 'invoice_setting' );

            $invoice_setting_data = array(
                'bookingpress_invoice_suffix_prefix' => !empty($bookingpress_invoice_suffix_prefix) ? $bookingpress_invoice_suffix_prefix == 'true' ? true : false : false,
                'bookingpress_invoice_suffix' => !empty($bookingpress_invoice_suffix) ? esc_html($bookingpress_invoice_suffix) : '',
                'bookingpress_invoice_prefix' => !empty($bookingpress_invoice_prefix) ? esc_html($bookingpress_invoice_prefix) : '',
                'bookingpress_minimum_invoice_length' => !empty($bookingpress_minimum_invoice_length) ? esc_html($bookingpress_minimum_invoice_length) : 0,
                'bookingpress_invoice_due_days' => !empty($bookingpress_invoice_due_days) ? esc_html($bookingpress_invoice_due_days) : 0,
                'bookingpress_hide_discount_raw' => !empty($bookingpress_hide_discount_raw) ? $bookingpress_hide_discount_raw == 'true' ? true : false : false,
                'bookingpress_hide_tax_raw' => !empty($bookingpress_hide_tax_raw) ? $bookingpress_hide_tax_raw == 'true' ? true : false : false,
                'bookingpress_selected_font' => !empty($bookingpress_selected_font) ? esc_html($bookingpress_selected_font) : '',
                'service_heading_text' => !empty($service_heading_text) ? $service_heading_text : '',
                'date_heading_text' => !empty($date_heading_text) ? $date_heading_text : '',
                'provider_heading_text' => !empty($provider_heading_text) ? $provider_heading_text : '',
                'price_heading_text' => !empty($price_heading_text) ? $price_heading_text : '',
                'subtotal_heading_text' => !empty($subtotal_heading_text) ? $subtotal_heading_text : '',
                'discount_heading_text' => !empty($discount_heading_text) ? $discount_heading_text : '',
                'tax_heading_text' => !empty($tax_heading_text) ? $tax_heading_text : '',
                'total_heading_text' => !empty($total_heading_text) ? $total_heading_text : '',
                'bookingpress_company_logo_text' => !empty($bookingpress_company_logo_text) ? $bookingpress_company_logo_text : '' ,
                'bookingpress_invoice_heading_text' => !empty($bookingpress_invoice_heading_text) ? $bookingpress_invoice_heading_text : '',
                'bookingpress_invoice_details_top'  => !empty($bookingpress_invoice_details_top) ? $bookingpress_invoice_details_top : '',                
                'bookingpress_company_details_text' => !empty($bookingpress_company_details_text) ? $bookingpress_company_details_text : '',
                'bookingpress_invoice_details_text' => !empty($bookingpress_invoice_details_text) ? $bookingpress_invoice_details_text : '',
                'bookingpress_invoice_details_down' => !empty($bookingpress_invoice_details_down) ? $bookingpress_invoice_details_down : '',
                'bookingpress_billing_details_text' => !empty($bookingpress_billing_details_text) ? $bookingpress_billing_details_text : '',
                'bookingpress_billing_details_right' => !empty($bookingpress_billing_details_right) ? $bookingpress_billing_details_right : '',
                'bookingpress_invoice_thankyou' => !empty($bookingpress_invoice_thankyou) ? $bookingpress_invoice_thankyou : '',
            );
            
            return $invoice_setting_data;

        }
        function bookingpress_br2nl($string) {
            return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
        }

        function bookingpress_settings_add_dynamic_on_load_method_func(){
            ?>
            else if( selected_tab_name == "invoice_settings" ) {                            
                vm.getInvoiceSettingsData()                 
            } 
            <?php            
        }

        function bookingpress_dynamic_get_settings_data_func(){
            ?>
            else if( current_tabname == "invoice_settings" ) {
                vm.getInvoiceSettingsData()                 
            } 
            <?php
        }

        function bookingpress_add_setting_dynamic_vue_methods_func(){            
            global $bookingpress_notification_duration;
            $bookingpress_tags_success_msg = __( 'Tag copied successfully', 'bookingpress-invoice' );
            $invoice_setting_data = $this->bookingpress_get_invoice_settings_data();            
            ?>          
            getInvoiceSettingsData() {                
                const vm = this;
                vm.invoice_setting_form = <?php echo json_encode($invoice_setting_data); ?>
            },
            saveInvoiceDetails(invoice_details) {
                const vm = this;
                var invoice_data = invoice_details.invoice_data

                invoice_data = vm.bookingpress_nl2br(invoice_data);
                vm.invoice_setting_form[invoice_details.invoice_type] = invoice_data ;
                vm.open_add_invoice_modal = false;
            },            
            bookingpress_nl2br (str, is_xhtml) {   
                var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br/>' : '<br>';    
                return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ breakTag +'$2');
            },
            bookingpress_br2nl(str, replaceMode) {       
              var replaceStr = (replaceMode) ? "\n" : '';
              return str.replace(/<\s*\/?br\s*[\/]?>/gi, replaceStr);
            },
            open_edit_invoice_modal(invoice_details,invoice_type) {
                const vm = this;
                vm.open_add_invoice_modal = true;
                vm.invoice_details.invoice_data =  vm.bookingpress_br2nl(invoice_details);
                vm.invoice_details.invoice_type =  invoice_type;
            },
            bookingpress_save_invoice_settings(form_name,setting_type){ 
                const vm = this
                var bookingpress_invoice_modified_html = document.getElementById("bookingpress_invoice_template_builder").value;
                vm.is_disabled = true
                vm.is_display_save_loader = '1'                    
                let saveFormData = vm[form_name]                                      
                saveFormData.action = 'bookingpress_save_invoice_settings_data'
                saveFormData.settingType = setting_type
                saveFormData.invoice_html_content = bookingpress_invoice_modified_html
                <?php do_action('bookingpress_add_invoice_settings_more_postdata'); ?>
                saveFormData._wpnonce = '<?php echo wp_create_nonce( 'bpa_wp_nonce' ); // phpcs:ignore ?>'
                axios.post(appoint_ajax_obj.ajax_url, Qs.stringify(saveFormData))
                .then(function(response){
                    vm.is_disabled = false
                    vm.is_display_save_loader = '0'
                    vm.$notify({                        
                        title: response.data.title,
                        message: response.data.msg,
                        type: response.data.variant,
                        customClass: response.data.variant+'_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
                    });
                    vm.isloading = false;
                    vm.toggleBusy()
                }).catch(function(error){
                    console.log(error)
                });
            },                   
            bookingpress_insert_tag(event){
                var bookingpress_textarea_element = document.getElementById("bookingpress_invoice_template_builder");
                var bookingpress_current_val = document.getElementById("bookingpress_invoice_template_builder").value;
                var bookingpress_start_pos = bookingpress_textarea_element.selectionStart;
                var bookingpress_end_pos = bookingpress_textarea_element.selectionEnd;

                var bookingpress_before_string = bookingpress_current_val.substring(0, bookingpress_start_pos);
                var bookingpress_after_string = bookingpress_current_val.substring(bookingpress_end_pos, bookingpress_current_val.length);

                var bookingpress_new_appended_string = bookingpress_before_string + event + bookingpress_after_string;
                document.getElementById("bookingpress_invoice_template_builder").value = bookingpress_new_appended_string;
            },
            bookingpress_invoice_reset_template() {
                const vm = this;     
                vm.invoice_setting_form = <?php echo json_encode($invoice_setting_data);  ?>                          
            },                        
            bookingpress_reset_invoice_counter() {
                const vm = this
                var invoice_data = [];
                invoice_data.action = 'bookingpress_reset_invoice_counter'
                invoice_data._wpnonce = '<?php echo wp_create_nonce( 'bpa_wp_nonce' ); // phpcs:ignore ?>'
                axios.post(appoint_ajax_obj.ajax_url, Qs.stringify(invoice_data))
                .then(function(response){
                    if(response.data.variant == 'success') {
                        vm.$notify({                        
                            title: response.data.title,
                            message: response.data.msg,
                            type: response.data.variant,
                            customClass: response.data.variant+'_notification',
                            duration:<?php echo intval($bookingpress_notification_duration); ?>,
                        });
                    }
                    vm.isloading = false;
                    vm.toggleBusy()
                }).catch(function(error){
                    console.log(error)
                });
            },
            bookingpress_close_invoice_settings_popover(){
                document.body.click();
            },
            bookingpress_open_preview_modal(){
                const vm = this;
                var bookingpress_preview_html = document.getElementById("bookingpress_invoice_template_builder").value;
                bookingpress_preview_html = bookingpress_preview_html.replace('[BOOKINGPRESS_CART_ITEMS]', '');
                bookingpress_preview_html = bookingpress_preview_html.replace('[/BOOKINGPRESS_CART_ITEMS]', '');                
                <?php do_action('bookingpress_before_open_invoice_preview'); ?>
                vm.bookingpress_invoice_preview_html = bookingpress_preview_html
                vm.bookingpress_invoice_preview_modal = true;
            },
            bookingpress_close_preview_modal(){
                const vm = this
                vm.bookingpress_invoice_preview_modal = false;
            },
            <?php 
        }
        function bookingpress_add_email_notification_data_func(){
            ?>
            if(vm.bookingpress_email_pdf_attachment_status != 'undefined') {
                bookingpress_save_notification_data.bookingpress_email_pdf_attachment_status = vm.bookingpress_email_pdf_attachment_status
            }
            <?php
        }
        
        function bookingpress_load_mpdf_library() {
            global $wpdb,$BookingPress,$BookingPressPro,$tbl_bookingpress_payment_logs,$bookingpress_pdfcreator_mpdf, $bookingpress_pdf_constructor,$tbl_bookingpress_customers, $tbl_bookingpress_appointment_bookings;

            $wpnonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';                        
            $bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );            

            //if(!$bpa_verify_nonce_flag || !$this->bookingpress_invoice_is_compatible()) {                
            if((!empty($_REQUEST['_wpnonce']) && !$bpa_verify_nonce_flag) || !$this->bookingpress_invoice_is_compatible()) {
                return;
            }

            if(!empty($_REQUEST['invoice_id']) && is_user_logged_in()) {
                $user_id = get_current_user_id();                
                $customer_data = $wpdb->get_results($wpdb->prepare("SELECT `bookingpress_customer_id` FROM " . $tbl_bookingpress_customers . " WHERE `bookingpress_wpuser_id`=%d",$user_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm 
                if(!empty($customer_data)) {
                    foreach($customer_data  as $customer_data_key => $customer_data_val) {
                        $customer_ids[] = intval($customer_data_val['bookingpress_customer_id']);
                    }
                }                                                
                $log_data = array();             
                if(!empty($customer_ids)) {
                    $bookingpress_search_query_placeholder = " AND bookingpress_customer_id IN (";
                    $bookingpress_search_query_placeholder .= rtrim( str_repeat( '%d,', count( $customer_ids ) ), ',' );
                    $bookingpress_search_query_placeholder .= ")";
                    array_unshift( $customer_ids , $bookingpress_search_query_placeholder );
                    $bookingpress_search_query_where = call_user_func_array( array( $wpdb, 'prepare' ), $customer_ids );
                    // phcs:ignore
                    $log_data = $wpdb->get_row($wpdb->prepare("SELECT 'bookingpress_payment_log_id' FROM " . $tbl_bookingpress_payment_logs . " WHERE `bookingpress_payment_log_id`=%d {$bookingpress_search_query_where} ",$_REQUEST['invoice_id']), ARRAY_A); //phpcs:ignore
                }
                $bpa_payments_cap     = $BookingPressPro->bookingpress_check_capability( 'bookingpress_payments' );
                if(empty($log_data) && !$bpa_payments_cap) {                    
                    return;
                }
            }
            $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];
            
            $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
            $fontData = $defaultFontConfig['fontdata'];
            

            $fontData = $fontData + [
                'arial' => [
                    'R' => 'arial.ttf'
                ],
                'courier_new' => [
                    'R' => 'cour_new.ttf'
                ],
                'courier' => [
                    'R' => 'Courier.ttf'
                ],
                'geneva' => [
                    'R' => 'Geneva.ttf'
                ],
                'helvetica' => [
                    'R' => 'Helvetica.ttf'
                ],
                'lucida_grande' => [
                    'R' => 'LucidaGrande.ttf',
                    'B' => 'LucidaGrandeBold.ttf'
                ],
                'lucida_sans_unicode' => [
                    'R' => 'lucida-sans-unicode.ttf'
                ],
                'monospace' => [
                    'R' => 'Monospace.ttf',
                ],
                'dejavuserif' => [
                    'R' => 'DejaVuSerif.ttf',
                    'B' => 'DejaVuSerif-Bold.ttf',
                    'I' => 'DejaVuSerif-Italic.ttf',
                    'BI'=> 'DejaVuSerif-BoldItalic.ttf',                     
                ],
                'tahoma' => [
                    'R' => 'Tahoma.ttf'
                ],
                'timesnewroman' => [
                    'R' => 'Timesr.ttf'
                ],
                'verdana' => [
                    'R' => 'Verdana.ttf'
                ]
            ];

            $bookingpress_invoice_get_fonts_arr=$this->bookingpress_invoice_get_fonts_arr();        
            $bookingpress_selected_font = $BookingPress->bookingpress_get_settings( 'bookingpress_selected_font', 'invoice_setting' );



            $fontDirs = array_merge( $fontDirs, array( BOOKINGPRESS_INVOICE_FONT_DIR ) );

            $bookingpress_pdf_constructor = array(
                'fontDir'           => $fontDirs,
                'fontdata' => $fontData,
                'default_font' => $bookingpress_selected_font,
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 5,
                'margin_right' => 5,
                'margin_top' => 20,
                'margin_bottom' => 10,
                'margin_header' => 5,
                'margin_footer' => 5,
                'orientation' => 'P',
                'debug' => false,
                'allow_output_buffering' => false,                    
            );
             
            $bookingpress_pdfcreator_mpdf = new Mpdf\Mpdf($bookingpress_pdf_constructor);
            $bookingpress_pdfcreator_mpdf->setAutoTopMargin = 'stretch';
            $bookingpress_pdfcreator_mpdf->setAutoBottomMargin = 'stretch';
            $bookingpress_pdfcreator_mpdf->autoScriptToLang = true;
            $bookingpress_pdfcreator_mpdf->baseScript = 1;
            $bookingpress_pdfcreator_mpdf->autoVietnamese = true;
            $bookingpress_pdfcreator_mpdf->autoArabic = true;
            $bookingpress_pdfcreator_mpdf->autoLangToFont = true;

            if(is_rtl())
            {
                $bookingpress_pdfcreator_mpdf->SetDirectionality('rtl');
            }
            if(is_user_logged_in() && isset($_REQUEST['action']) && $_REQUEST['action']=="bpa_pdfcreates"){                
                try {
                    if(!empty($_REQUEST['invoice_id'])){
                        $invoice_css='
                        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap");
                        body { 
                            margin: 0;
                            padding: 0;
                            font-family: "Poppins", sans-serif;
                        }
                        .page { 
                            width:700px;
                            min-height:600px;
                            padding: 20px;
                            margin: 0 auto;
                            background: white;
                        }';
                        $bookingpress_invoice_html_view='';
                        $log_id= intval($_REQUEST['invoice_id']);
                        if (!empty($log_id) && $log_id != 0) {
                            $log_detail = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $tbl_bookingpress_payment_logs . " WHERE `bookingpress_payment_log_id`=%d",intval($_REQUEST['invoice_id'])), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm 
                            $bookingpress_invoice_html_view .= $this->bookingpress_pdf_invoice_html_content($log_id);
                            
                            $bookingpress_pdf_title=__('Invoice','bookingpress-invoice').' #'.intval($log_detail['bookingpress_invoice_id']);
                            $pdf_template_name='invoice_'.intval($log_detail['bookingpress_invoice_id']);
                            
                            $bookingpress_invoice_html_doc = new DOMDocument();
                            $bookingpress_invoice_html_doc->loadHTML($bookingpress_invoice_html_view);
                            $style_output = $bookingpress_invoice_html_doc->getElementsByTagName("style");
                            if($style_output->length>0){
                                for ($i=0; $i < $style_output->length; $i++) { 
                                    $invoice_css .=$style_output->item($i)->nodeValue;;
                                }
                            }
                            
                            $bookingpress_invoice_html_view = preg_replace('/<style\b[^>]*>(.*?)<\/style>/i', '', $bookingpress_invoice_html_view);
                            $bookingpress_invoice_html_view = preg_replace('/<script\b[^>]*>(.*?)<\/script>/i', '', $bookingpress_invoice_html_view);
                            $bookingpress_pdfcreator_mpdf->WriteHTML($invoice_css,1);
                            $bookingpress_pdfcreator_mpdf->WriteHTML($bookingpress_invoice_html_view,2);
                            $bookingpress_pdfcreator_mpdf->SetTitle($bookingpress_pdf_title);
                            $pdffilename    = $pdf_template_name.'.pdf';
                            $bookingpress_pdfcreator_mpdf->Output($pdffilename, 'I');
                            exit;
                        }
                    }                     
                    
                } catch (Exception $e) {
                    echo $e->getmessage(); // phpcs:ignore
                }
            }        
        }

        function bookingpress_import_invoice_fonts( $font_name = array() ){
            
            $response = array(
                'variant' => 'error',
                'message' => esc_html__( 'Font not found', 'bookingpress-invoice' )
            );

            if( !function_exists('WP_Filesystem' ) ){
                require_once(ABSPATH . 'wp-admin/includes/file.php');
            }

            WP_Filesystem();
            global $wp_filesystem;

            $total_fonts = count( $font_name );
            $counter = 0;
            foreach( $font_name as $font ){
                $destination = BOOKINGPRESS_INVOICE_FONT_DIR . '/' . $font;
                $source      = 'https://www.arformsplugin.com/arf_pdfcreator_fonts/?action=arf_pdf_import_form&font=' . $font;

                $args = array(
                    'timeout' => 50,
                );

                $response = wp_remote_get( $source, $args );

                if ( isset( $response['body'] ) && $response['body'] != '' ) {
                    if( !file_exists( $destination ) && !$wp_filesystem->put_contents( $destination, $response['body'], 777 ) ){
                        $child_font = false;
                        $sub_font_err_cnt++;
                    }
                }

                $counter++;
            }

            if( $total_fonts == $counter ){
                $response = array(
                    'variant' => 'success',
                );
            }

            echo json_encode( $response );
        }

        function bookingpress_add_email_notification_section_func(){ ?>
            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-if="typeof is_invoice_activated !== 'undefined' && is_invoice_activated == 1 && bookingpress_active_email_notification != 'share_appointment'">
                <el-form-item>
                    <div class="bpa-en-status--swtich-row">
                        <label class="bpa-form-label"><?php esc_html_e( 'send a PDF', 'bookingpress-invoice' ); ?></label>
                        <el-switch class="bpa-swtich-control" v-model="bookingpress_email_pdf_attachment_status"></el-switch>
                    </div>
                </el-form-item>
            </el-col>
            <?php
        }

        function bookingpress_payment_list_add_action_button_func($bookingpress_add_dynamic_action_btn_content){
            $bookingpress_add_dynamic_action_btn_content .= '<el-tooltip effect="dark" content="" placement="top" open-delay="300">
                <div slot="content">
                    <span>'.esc_html__( 'Invoice', 'bookingpress-invoice' ).'</span>
                </div>
                <a :href="scope.row.bookingpress_invoice_pdf_url" target="_blank" class="bpa-btn bpa-btn--icon-without-box">
                    <span class="material-icons-round">receipt_long</span>
                </a>
            </el-tooltip>';
            return $bookingpress_add_dynamic_action_btn_content;
        }

        function bookingpress_add_my_appointment_data_fields_func() {
            ?>
            <div class="bpa-front-ma-list--item__ic-col" v-if="typeof is_invoice_activated !== 'undefined' && is_invoice_activated == 1 && items_list.bookingpress_invoice_id != '' && items_list.bookingpress_invoice_pdf_url != ''">
                <p><el-link :href="items_list.bookingpress_invoice_pdf_url" type="primary" target="_blank">{{items_list.bookingpress_invoice_id}}</el-link></p>
            </div>
            <?php
        }

        function bookingpress_get_single_transaction($log_id){
            global $wpdb,$tbl_bookingpress_payment_logs;
            $log_data = array();
            if (!empty($log_id) && $log_id != 0) {
                $log_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $tbl_bookingpress_payment_logs . " WHERE `bookingpress_payment_log_id`= %d",$log_id),ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm 
            }
            return $log_data;
        }

        function bookingpress_pdf_invoice_html_content($log_id) {

            global $wpdb,$BookingPress, $bookingpress_global_options, $bookingpress_pro_staff_members, $bookingpress_coupons, $bookingpress_pro_payment_gateways,$tbl_bookingpress_customers, $tbl_bookingpress_appointment_bookings,$bookingpress_pro_appointment,$tbl_bookingpress_form_fields, $BookingPressPro;

            $bookingpress_staffmember_module = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();

            if (!empty($log_id) && $log_id != 0) {                 
                    do_action('bookingpress_invoice_pdf_generate_before',$log_id);                    
                    $log_detail = $this->bookingpress_get_single_transaction($log_id);
                    $bookingpress_global_options_data = $bookingpress_global_options->bookingpress_global_options();
                    $bookingpress_global_date_format = $bookingpress_global_options_data['wp_default_date_format'];
                    $bookingpress_global_time_format = $bookingpress_global_options_data['wp_default_time_format'];
                    $bookingpress_date_time_format = $bookingpress_global_date_format.' '.$bookingpress_global_time_format;

                    $service_heading_text = $BookingPress->bookingpress_get_settings( 'service_heading_text', 'invoice_setting' );
                    $date_heading_text = $BookingPress->bookingpress_get_settings( 'date_heading_text', 'invoice_setting' );
                    $provider_heading_text = $BookingPress->bookingpress_get_settings( 'provider_heading_text', 'invoice_setting' );
                    $price_heading_text = $BookingPress->bookingpress_get_settings( 'price_heading_text', 'invoice_setting' );
                    $subtotal_heading_text = $BookingPress->bookingpress_get_settings( 'subtotal_heading_text', 'invoice_setting' );
                    $discount_heading_text = $BookingPress->bookingpress_get_settings( 'discount_heading_text', 'invoice_setting' );
                    $tax_heading_text = $BookingPress->bookingpress_get_settings( 'tax_heading_text', 'invoice_setting' );
                    $total_heading_text = $BookingPress->bookingpress_get_settings( 'total_heading_text', 'invoice_setting' );
                    $bookingpress_company_logo_text = $BookingPress->bookingpress_get_settings( 'bookingpress_company_logo_text', 'invoice_setting' );
                    $bookingpress_invoice_heading_text = $BookingPress->bookingpress_get_settings( 'bookingpress_invoice_heading_text', 'invoice_setting' );
                    $bookingpress_invoice_details_top = $BookingPress->bookingpress_get_settings( 'bookingpress_invoice_details_top', 'invoice_setting' );
                    $bookingpress_company_details_text = $BookingPress->bookingpress_get_settings( 'bookingpress_company_details_text', 'invoice_setting' );
                    $bookingpress_invoice_details_text = $BookingPress->bookingpress_get_settings( 'bookingpress_invoice_details_text', 'invoice_setting' );
                    $bookingpress_invoice_details_down = $BookingPress->bookingpress_get_settings( 'bookingpress_invoice_details_down', 'invoice_setting' );
                    $bookingpress_billing_details_text = $BookingPress->bookingpress_get_settings( 'bookingpress_billing_details_text', 'invoice_setting' );
                    $bookingpress_billing_details_right =  $BookingPress->bookingpress_get_settings( 'bookingpress_billing_details_right', 'invoice_setting' );
                    $bookingpress_invoice_thankyou = $BookingPress->bookingpress_get_settings( 'bookingpress_invoice_thankyou', 'invoice_setting' );      
                    $bookingpress_hide_discount_raw = $BookingPress->bookingpress_get_settings( 'bookingpress_hide_discount_raw', 'invoice_setting' );
                    $bookingpress_hide_tax_raw = $BookingPress->bookingpress_get_settings( 'bookingpress_hide_tax_raw', 'invoice_setting' );
                    $bookingpress_selected_font = $BookingPress->bookingpress_get_settings( 'bookingpress_selected_font', 'invoice_setting' );                    
                   
                    $bookingpress_hide_discount_raw = !empty($bookingpress_hide_discount_raw) ? $bookingpress_hide_discount_raw == 'true' ? true : false : false;
                    $bookingpress_hide_tax_raw = !empty($bookingpress_hide_tax_raw) ? $bookingpress_hide_tax_raw == 'true' ? true : false : false;

                    $bookingpress_currency_name   = !empty($log_detail['bookingpress_payment_currency']) ? esc_html($log_detail['bookingpress_payment_currency']) : '';
                    $bookingpress_currency_symbol = $BookingPress->bookingpress_get_currency_symbol( $bookingpress_currency_name );

                    $bookingpress_invoice_number = !empty($log_detail['bookingpress_invoice_id']) ? $log_detail['bookingpress_invoice_id'] : '';
                    $bookingpress_invoice_date = !empty($log_detail['bookingpress_created_at']) ? $log_detail['bookingpress_created_at']  : '';                    
                    $bookingpress_date_format = $bookingpress_global_options_data['wp_default_date_format'];
                    $bookingpress_invoice_due_date = $BookingPress->bookingpress_get_settings( 'bookingpress_invoice_due_days', 'invoice_setting' );
                    $bookingpress_company_name = $BookingPress->bookingpress_get_settings( 'company_name', 'company_setting' );
                    $bookingpress_company_address = $BookingPress->bookingpress_get_settings( 'company_address', 'company_setting' );
                    $bookingpress_company_website = $BookingPress->bookingpress_get_settings( 'company_website', 'company_setting' );
                    $bookingpress_company_phone = $BookingPress->bookingpress_get_settings( 'company_phone_number', 'company_setting' );
                    $bookingpress_company_avatar_url = $BookingPress->bookingpress_get_settings( 'company_avatar_url', 'company_setting' );
                     $bookingpress_company_avatar = '';

                    if (!empty($bookingpress_company_avatar_url)) {                        
                        $bookingpress_company_avatar_url = esc_url( $bookingpress_company_avatar_url );             ;                        
                        $bookingpress_company_avatar = "<img src='".$bookingpress_company_avatar_url."' height='150' width='170'>";
                    } 
                    $bookingpress_customer_fullname = !empty($log_detail['bookingpress_customer_name']) ? $log_detail['bookingpress_customer_name'] : '';
                    $bookingpress_customer_firstname = !empty($log_detail['bookingpress_customer_firstname']) ? esc_html( $log_detail['bookingpress_customer_firstname'] ) : '';
                    $bookingpress_customer_lastname = !empty($log_detail['bookingpress_customer_lastname']) ? esc_html( $log_detail['bookingpress_customer_lastname'] ) : '';
                    $bookingpress_customer_email = !empty($log_detail['bookingpress_customer_email']) ? ( $log_detail['bookingpress_customer_email'] ) : '';
                    $bookingpress_customer_id = !empty($log_detail['bookingpress_customer_id']) ? intval( $log_detail['bookingpress_customer_id'] ) : '';

                    $bookingpress_customer_phone_details = $wpdb->get_row( $wpdb->prepare('SELECT bookingpress_user_phone  FROM ' . $tbl_bookingpress_customers . ' WHERE bookingpress_customer_id = %d',$bookingpress_customer_id),ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm      
                    $bookingpress_customer_phone= !empty($bookingpress_customer_phone_details['bookingpress_user_phone']) ? esc_html($bookingpress_customer_phone_details['bookingpress_user_phone']) : '';
                    $bookingpress_invoice_dt = strtotime($bookingpress_invoice_date);
                    $bookingpress_invoice_due_date =  date($bookingpress_date_format,strtotime('+'.$bookingpress_invoice_due_date.' days',$bookingpress_invoice_dt));
                    $bookingpress_invoice_date =  date($bookingpress_date_format,strtotime($bookingpress_invoice_date));

                    $bookingpress_invoice_html_view = get_option('bookingpress_invoice_html_format');
                    $bookingpress_invoice_html_view = apply_filters( 'bookingpress_modified_bookingpress_invoice_html_format',$bookingpress_invoice_html_view);



                    $bookingpress_deposit_amt = $bookingpress_subtotal_amt = $bookingpress_tax_amount = $bookingpress_discount_amount = $bookingpress_total_amount = $bookingpress_paid_amt = $bookingpress_due_amt = 0;
                    $bookingpress_final_appointment_details = array();

                    //Deposit amount data
                    if($log_detail['bookingpress_payment_gateway'] != 'on-site'){
                        $bookingpress_deposit_amt = floatval($log_detail['bookingpress_deposit_amount']);
                    }
                    $bookingpress_deposit_amt_with_currency = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_deposit_amt, $bookingpress_currency_symbol);

                    //Fetch Tax Amount data
                    $bookingpress_tax_amount = !empty($log_detail['bookingpress_tax_amount']) ? esc_html( $log_detail['bookingpress_tax_amount'] ) : 0;               
                    $bookingpress_final_tax_amount  = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_tax_amount, $bookingpress_currency_symbol);                

                    //Fetch Coupon Amount data
                    $bookingpress_coupon_data = !empty($log_detail['bookingpress_coupon_details']) ? json_decode( $log_detail['bookingpress_coupon_details'],true ) : ''; 
                    $bookingpress_coupon_discount = $bookingpress_coupon_discount_amount = 0;
                    $bookingpress_coupon_code = '';
                    if(!empty($bookingpress_coupon_data)) {
                        $bookingpress_coupon_discount = $bookingpress_coupon_discount_amount = !empty($log_detail['bookingpress_coupon_discount_amount']) ? esc_html( $log_detail['bookingpress_coupon_discount_amount'] ) : '';                    
                        $bookingpress_coupon_code = !empty($bookingpress_coupon_data['coupon_data']['bookingpress_coupon_code'] ) ? esc_html( $bookingpress_coupon_data['coupon_data']['bookingpress_coupon_code'] ) : '';
                    }                                                  
                    $bookingpress_coupon_discount = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_coupon_discount, $bookingpress_currency_symbol);

                    //Calculate subtotal data
                    $bookingpress_appointment_details = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_payment_id = %d", $log_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
                    if(!empty($bookingpress_appointment_details)){
                        foreach($bookingpress_appointment_details as $appointment_key => $appointment_val){
                            $bookingpress_tmp_subtotal_amt = floatval($appointment_val['bookingpress_service_price']);

                            if(!empty($appointment_val['bookingpress_staff_member_id']) && !empty($appointment_val['bookingpress_staff_member_price']) ){
                                $bookingpress_tmp_subtotal_amt = floatval($appointment_val['bookingpress_staff_member_price']);
                            }

                            if(isset($appointment_val['bookingpress_enable_custom_duration'])  && $appointment_val['bookingpress_enable_custom_duration'] == 1) {
                               $bookingpress_tmp_subtotal_amt = floatval($appointment_val['bookingpress_service_price']);							
			    }

                            $bookingpress_bring_anyone_members_details = intval($appointment_val['bookingpress_selected_extra_members']) - 1;
                            if($bookingpress_bring_anyone_members_details > 0){
                                $bookingpress_tmp_subtotal_amt = $bookingpress_tmp_subtotal_amt + ( $bookingpress_tmp_subtotal_amt * $bookingpress_bring_anyone_members_details );
                            }                   
                            
                            
                                $bookingpress_extra_total = 0;
                                $bookingpress_extra_service_details = !empty($appointment_val['bookingpress_extra_service_details']) ? json_decode($appointment_val['bookingpress_extra_service_details'], TRUE) : array();
                                if(!empty($bookingpress_extra_service_details)){
                                    $bookingpress_service_extra_content = "<table align='left' width='100%'>";
                                    foreach($bookingpress_extra_service_details as $k3 => $v3){
                                        $bookingpress_extra_service_name = !empty($v3['bookingpress_extra_service_details']['bookingpress_extra_service_name']) ? esc_html($v3['bookingpress_extra_service_details']['bookingpress_extra_service_name']) : '';
                                        if(!empty($v3['bookingpress_extra_service_details']['bookingpress_extra_service_name']) && !empty($v3['bookingpress_extra_service_details']['bookingpress_extra_services_id'])){
                                            $bookingpress_extra_service_name =  $BookingPressPro->bookingpress_pro_front_language_translation_func($v3['bookingpress_extra_service_details']['bookingpress_extra_service_name'],'service_extra','bookingpress_extra_service_name',$v3['bookingpress_extra_service_details']['bookingpress_extra_services_id']);  
                                        }
                                        $bookingpress_extra_service_qty = !empty($v3['bookingpress_selected_qty']) ? intval($v3['bookingpress_selected_qty']) : '';
                                        $bookingpress_extra_service_price = !empty($v3['bookingpress_final_payable_price']) ? floatval($v3['bookingpress_final_payable_price']) : '';  
                                        $bookingpress_service_price_with_currency = ! empty($bookingpress_extra_service_price) ? $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_extra_service_price, $bookingpress_currency_symbol) : 0;	
                                        $bookingpress_service_extra_content .= "<tr>";
                                        $bookingpress_service_extra_content .= "<td valign='center' align='left' style='padding-bottom:10px;'>".$bookingpress_extra_service_name." * ".$bookingpress_extra_service_qty." </td>";
                                        $bookingpress_service_extra_content .= "<td valign='center' align='right'>".$bookingpress_service_price_with_currency."</td>";
                                        $bookingpress_service_extra_content .= "</tr>";
                                        $bookingpress_extra_total = $bookingpress_extra_total + $v3['bookingpress_final_payable_price'];
                                    }
                                    $bookingpress_service_extra_total_with_currency = ! empty($bookingpress_extra_total) ? $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_extra_total, $bookingpress_currency_symbol) : 0;
                                    $bookingpress_service_extra_content .= "</table>";
                                }
                                else {
                                    $bookingpress_service_extra_content = '';
                                }

                                if(isset($appointment_val['bookingpress_is_cart']) && $appointment_val['bookingpress_is_cart'] != 1) {
                                    $bookingpress_invoice_html_view = str_replace('{service_extras}', $bookingpress_service_extra_content, $bookingpress_invoice_html_view);
                                }
                            
                            
                            $bookingpress_tmp_subtotal_amt = $bookingpress_tmp_subtotal_amt + $bookingpress_extra_total;

                            $bookingpress_subtotal_amt = $bookingpress_subtotal_amt + $bookingpress_tmp_subtotal_amt;


                            $bookingpress_service_name = $appointment_val['bookingpress_service_name'];
                            $bookingpress_number_of_person = intval($appointment_val['bookingpress_selected_extra_members']);

                            $bookingpress_appointment_date = $appointment_val['bookingpress_appointment_date'];
                            $bookingpress_appointment_date = date($bookingpress_date_format, strtotime($bookingpress_appointment_date));

                            $bookingpress_staff_member_name = (!empty($appointment_val['bookingpress_staff_first_name']) || !empty($appointment_val['bookingpress_staff_last_name'])) ? $appointment_val['bookingpress_staff_first_name']." ".$appointment_val['bookingpress_staff_last_name'] : $appointment_val['bookingpress_staff_email_address'];

                            $service_gross_price = 0;
			                if($bookingpress_staffmember_module && !empty( $appointment_val['bookingpress_staff_member_price'] ) ) {

                                $bookingpress_service_price = floatval($appointment_val['bookingpress_staff_member_price']);        
                                $service_gross_price = $bookingpress_service_price;
                                $bookingpress_service_price = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_service_price, $bookingpress_currency_symbol, true);
                            } else {

                                $bookingpress_service_price = floatval($appointment_val['bookingpress_service_price']);
                                $service_gross_price = $bookingpress_service_price;
                                $bookingpress_service_price = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_service_price, $bookingpress_currency_symbol, true);    
                            }

                            $service_tax_price = $bookingpress_final_tax_amount;
                            if( !empty( $appointment_val['bookingpress_tax_amount'] ) && 'include_taxes' == $appointment_val['bookingpress_price_display_setting'] ){
                                $tax_percentage = $appointment_val['bookingpress_tax_percentage'];
                                $gross_amount = ( $service_gross_price * $tax_percentage ) / ( 100 + $tax_percentage );
                                $gross_amount = $BookingPress->bookingpress_price_formatter_with_currency_symbol( $gross_amount, $bookingpress_currency_symbol, false );
                                $service_tax_price = $BookingPress->bookingpress_price_formatter_with_currency_symbol( $gross_amount, $bookingpress_currency_symbol );
                                $service_gross_price = $service_gross_price - $gross_amount;
                                $service_gross_price = $BookingPress->bookingpress_price_formatter_with_currency_symbol( $service_gross_price, $bookingpress_currency_symbol );
                            } else if( !empty( $appointment_val['bookingpress_tax_amount'] ) && 'exclude_taxes' == $appointment_val['bookingpress_price_display_setting'] ){
                                $tax_percentage = $appointment_val['bookingpress_tax_percentage'];
                                $gross_amount = ( $service_gross_price * ( $tax_percentage / 100) );
                                $service_gross_price = $BookingPress->bookingpress_price_formatter_with_currency_symbol( $service_gross_price, $bookingpress_currency_symbol );
                                $service_tax_price = $BookingPress->bookingpress_price_formatter_with_currency_symbol( $gross_amount, $bookingpress_currency_symbol );
                                $gross_amount = $BookingPress->bookingpress_price_formatter_with_currency_symbol( $gross_amount, $bookingpress_currency_symbol, false );

                            }

                            $bookingpress_appointment_start_time = ! empty( $appointment_val['bookingpress_appointment_time'] ) ? esc_html( $appointment_val['bookingpress_appointment_time'] ) : '';
			    $bookingpress_appointment_end_time = ! empty( $appointment_val['bookingpress_appointment_end_time'] ) ? esc_html( $appointment_val['bookingpress_appointment_end_time'] ) : '';                            
                            $default_time_format = $bookingpress_global_options_data['wp_default_time_format'];
			    $bookingpress_appointment_start_time = date($default_time_format, strtotime($bookingpress_appointment_start_time));
                            $bookingpress_appointment_end_time   = date($default_time_format, strtotime($bookingpress_appointment_end_time));
                            
                            $bookingpress_final_appointment_details[] = array(
                                'service' => $bookingpress_service_name,
                                'appointment_date' => $bookingpress_appointment_date,
                                'appointment_time' =>  $bookingpress_appointment_start_time. ' - '.$bookingpress_appointment_end_time,
                                'staff_name' => $bookingpress_staff_member_name,
                                'service_price' => $bookingpress_service_price,
                                'service_gross_price' => $service_gross_price,
                                'service_tax_price' => $service_tax_price,
                                'number_of_person' => $bookingpress_number_of_person,
				                'service_extras' => $bookingpress_service_extra_content,
                                'all_fields' => $appointment_val,
                            );
                        }
                    }

                    if( !empty($log_detail['bookingpress_price_display_setting']) && $log_detail['bookingpress_price_display_setting'] == "include_taxes" ){
                        //$bookingpress_total_amount = ($bookingpress_subtotal_amt) - $bookingpress_coupon_discount_amount - $bookingpress_deposit_amt;    
                        $bookingpress_total_amount = ($bookingpress_subtotal_amt) - $bookingpress_coupon_discount_amount;
                    }else{
                        //$bookingpress_total_amount = ($bookingpress_subtotal_amt + $bookingpress_tax_amount) - $bookingpress_coupon_discount_amount - $bookingpress_deposit_amt;
                        $bookingpress_total_amount = ($bookingpress_subtotal_amt + $bookingpress_tax_amount) - floatval($bookingpress_coupon_discount_amount);
                    }
                    $bookingpress_payment_method = !empty($log_detail['bookingpress_payment_gateway']) ? $log_detail['bookingpress_payment_gateway'] : '';
                    if(!empty($bookingpress_payment_method) && $bookingpress_payment_method == 'on-site' ) {
                        $bookingpress_payment_method = $BookingPress->bookingpress_get_customize_settings('locally_text','booking_form');
                    } elseif(!empty($bookingpress_payment_method) && $bookingpress_payment_method != 'manual') {
                        $bookingpress_payment_method = $BookingPress->bookingpress_get_customize_settings($bookingpress_payment_method.'_text','booking_form');
                    }

                    //Paid Amount & Due Amount
                    $bookingpress_paid_amount = !empty($log_detail['bookingpress_paid_amount']) ? $log_detail['bookingpress_paid_amount'] : 0;
                    if($bookingpress_deposit_amt != 0){
                        $bookingpress_paid_amount = $bookingpress_deposit_amt;
                    }
                    
                    $bookingpress_total_amount = apply_filters('bookingpress_change_total_amount_before_calculate',$bookingpress_total_amount, $log_detail);

                    $bookingpress_due_amount = 0;
                    if($log_detail['bookingpress_payment_gateway'] != 'on-site'){

                        if($bookingpress_deposit_amt != 0){
                            $bookingpress_due_amount = $bookingpress_total_amount - $bookingpress_paid_amount;
                        }                        
                        if($log_detail['bookingpress_payment_status'] == 1 && $bookingpress_deposit_amt != 0) {
                            $bookingpress_due_amount = 0;
                            $bookingpress_paid_amount = $bookingpress_total_amount; 
                        }
                    }else{
                        $bookingpress_paid_amount = $bookingpress_total_amount;
                    }
                    $bookingpress_total_amount = apply_filters('bookingpress_change_total_amount_outside',$bookingpress_total_amount, $log_detail);                    
                    if(isset($log_detail['bookingpress_payment_status']) && $log_detail['bookingpress_payment_gateway'] == 'on-site' && $log_detail['bookingpress_payment_status'] != 1 && $log_detail['bookingpress_payment_status'] != 4){
                        $bookingpress_due_amount = $bookingpress_total_amount; 
                        $bookingpress_paid_amount = 0;                       
                    }
                     
                    if($bookingpress_due_amount < 0){
                        $bookingpress_due_amount = 0;
                    }

                    //$bookingpress_paid_amount = apply_filters('bookingpress_change_paid_amount_outside',$bookingpress_paid_amount, $log_detail);
                    
                    $bookingpress_total_amount_with_currency = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_total_amount, $bookingpress_currency_symbol);

                    $bookingpress_subtotal_val_with_currency = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_subtotal_amt, $bookingpress_currency_symbol);

                    $bookingpress_paid_amount_with_currency = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_paid_amount, $bookingpress_currency_symbol);

                    $bookingpress_due_amount_with_currency = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_due_amount, $bookingpress_currency_symbol);

                    $bookingpress_column_style = $bookingpress_staffmember_module ? 'colspan=2' : '';

                    $bookingpress_invoice_html_view = apply_filters( 'bookingpress_change_recurring_content_for_invoice', $bookingpress_invoice_html_view, $bookingpress_final_appointment_details,$log_id);

                    $bookingpress_tag_starting_pos = strpos($bookingpress_invoice_html_view, '[BOOKINGPRESS_CART_ITEMS]', 0);
                    $bookingpress_tag_ending_pos = strpos($bookingpress_invoice_html_view, '[/BOOKINGPRESS_CART_ITEMS]', $bookingpress_tag_starting_pos);
                    $bookingpress_tag_ending_pos = $bookingpress_tag_ending_pos + 27;

                    $bookingpress_cart_html_content = substr($bookingpress_invoice_html_view, $bookingpress_tag_starting_pos, $bookingpress_tag_ending_pos);

                    $bookingpress_cart_content = array();
                    $bookingpress_invoice_html_view = " ".$bookingpress_invoice_html_view;
                    $offset = 0;
                    while(true)
                    {
                        $ini = strpos($bookingpress_invoice_html_view, "[BOOKINGPRESS_CART_ITEMS]",$offset);
                        if ($ini == 0)
                            break;
                        $ini += strlen("[BOOKINGPRESS_CART_ITEMS]");
                        $len = strpos($bookingpress_invoice_html_view, "[/BOOKINGPRESS_CART_ITEMS]",$ini) - $ini;
                        $bookingpress_cart_content[] = substr($bookingpress_invoice_html_view, $ini,$len);
                        $offset = $ini+$len;
                    }

                    if(!empty($bookingpress_final_appointment_details)){
                        if(!empty($bookingpress_cart_content)){
                            $bookingpress_cart_invoice_html = !empty($bookingpress_cart_content[0]) ? $bookingpress_cart_content[0] : '';

                            if(count($bookingpress_final_appointment_details) > 1){
                                $bookingpress_tmp_cart_invoice_html = "";

                                foreach($bookingpress_final_appointment_details as $final_appointment_key => $final_appointment_val){
                                    $bookingpress_tmp_cart_invoice_html .= $bookingpress_cart_invoice_html;

                                    $bookingpress_tmp_cart_invoice_html = str_replace('{service_name}', $final_appointment_val['service'], $bookingpress_tmp_cart_invoice_html);
                                    $bookingpress_tmp_cart_invoice_html = str_replace('{appointment_date}', $final_appointment_val['appointment_date'], $bookingpress_tmp_cart_invoice_html);
                                    $bookingpress_tmp_cart_invoice_html = str_replace('{appointment_time}', $final_appointment_val['appointment_time'], $bookingpress_tmp_cart_invoice_html);
                                    $bookingpress_tmp_cart_invoice_html = str_replace('{staffmember_name}', $final_appointment_val['staff_name'], $bookingpress_tmp_cart_invoice_html);
                                    $bookingpress_tmp_cart_invoice_html = str_replace('{service_price}', $final_appointment_val['service_price'], $bookingpress_tmp_cart_invoice_html);
                                    $bookingpress_tmp_cart_invoice_html = str_replace('{service_gross_amount}', $final_appointment_val['service_gross_price'], $bookingpress_tmp_cart_invoice_html);
                                    $bookingpress_tmp_cart_invoice_html = str_replace('{service_tax_amt}', $final_appointment_val['service_tax_price'], $bookingpress_tmp_cart_invoice_html);
                                    $bookingpress_tmp_cart_invoice_html = str_replace('{number_of_person}', $final_appointment_val['number_of_person'], $bookingpress_tmp_cart_invoice_html);
                                    $bookingpress_tmp_cart_invoice_html = str_replace('{service_extras}', $final_appointment_val['service_extras'], $bookingpress_tmp_cart_invoice_html);
                                     
                                }

                                $bookingpress_cart_invoice_html = $bookingpress_tmp_cart_invoice_html;
                            }else{
                                foreach($bookingpress_final_appointment_details as $final_appointment_key => $final_appointment_val){
                                    $bookingpress_tmp_cart_invoice_html .= $bookingpress_cart_invoice_html;

                                    $bookingpress_cart_invoice_html = str_replace('{service_name}', $final_appointment_val['service'], $bookingpress_cart_invoice_html);
                                    $bookingpress_cart_invoice_html = str_replace('{appointment_date}', $final_appointment_val['appointment_date'], $bookingpress_cart_invoice_html);
                                    $bookingpress_cart_invoice_html = str_replace('{appointment_time}', $final_appointment_val['appointment_time'], $bookingpress_cart_invoice_html);
                                    $bookingpress_cart_invoice_html = str_replace('{staffmember_name}', $final_appointment_val['staff_name'], $bookingpress_cart_invoice_html);
                                    $bookingpress_cart_invoice_html = str_replace('{service_price}', $final_appointment_val['service_price'], $bookingpress_cart_invoice_html);
                                    $bookingpress_cart_invoice_html = str_replace('{service_gross_amount}', $final_appointment_val['service_gross_price'], $bookingpress_cart_invoice_html);
                                    $bookingpress_cart_invoice_html = str_replace('{service_tax_amt}', $final_appointment_val['service_tax_price'], $bookingpress_cart_invoice_html);
                                    $bookingpress_cart_invoice_html = str_replace('{number_of_person}', $final_appointment_val['number_of_person'], $bookingpress_cart_invoice_html); 
                                    $bookingpress_cart_invoice_html = str_replace('{service_extras}', $final_appointment_val['service_extras'], $bookingpress_cart_invoice_html);
                                }
                            }

                            $bookingpress_invoice_html_view = substr_replace($bookingpress_invoice_html_view, $bookingpress_cart_invoice_html, $bookingpress_tag_starting_pos, ($bookingpress_tag_ending_pos-$bookingpress_tag_starting_pos));
                        }else{
                            foreach($bookingpress_final_appointment_details as $final_appointment_key => $final_appointment_val){
                                $bookingpress_invoice_html_view = str_replace('{service_name}', $final_appointment_val['service'], $bookingpress_invoice_html_view);
                                $bookingpress_invoice_html_view = str_replace('{appointment_date}', $final_appointment_val['appointment_date'], $bookingpress_invoice_html_view);
                                $bookingpress_invoice_html_view = str_replace('{appointment_time}', $final_appointment_val['appointment_time'], $bookingpress_invoice_html_view);
                                $bookingpress_invoice_html_view = str_replace('{staffmember_name}', $final_appointment_val['staff_name'], $bookingpress_invoice_html_view);
                                $bookingpress_invoice_html_view = str_replace('{service_price}', $final_appointment_val['service_price'], $bookingpress_invoice_html_view);
                                $bookingpress_invoice_html_view = str_replace('{service_gross_amount}', $final_appointment_val['service_gross_price'], $bookingpress_invoice_html_view);
                                $bookingpress_invoice_html_view = str_replace('{service_tax_amt}', $final_appointment_val['service_tax_price'], $bookingpress_invoice_html_view);
                                $bookingpress_invoice_html_view = str_replace('{number_of_person}', $final_appointment_val['number_of_person'], $bookingpress_invoice_html_view); 
                                $bookingpress_invoice_html_view = str_replace('{service_extras}', $final_appointment_val['service_extras'], $bookingpress_invoice_html_view);
                            }
                        }
                    }

                    $bookingpress_invoice_html_view = apply_filters('bookingpress_change_label_value_for_invoice_using_appointment',$bookingpress_invoice_html_view, $log_detail, $bookingpress_final_appointment_details,$bookingpress_total_amount);

                    $bookingpress_invoice_html_view = str_replace('{invoice_number}', $bookingpress_invoice_number, $bookingpress_invoice_html_view);
                    $bookingpress_invoice_html_view = str_replace('{invoice_date}', $bookingpress_invoice_date, $bookingpress_invoice_html_view);
                    $bookingpress_invoice_html_view = str_replace('{invoice_due_date}', $bookingpress_invoice_due_date, $bookingpress_invoice_html_view);

                    $bookingpress_invoice_html_view = str_replace('{company_logo}',$bookingpress_company_avatar, $bookingpress_invoice_html_view);
                    $bookingpress_invoice_html_view = str_replace('{company_name}', $bookingpress_company_name, $bookingpress_invoice_html_view);
                    $bookingpress_invoice_html_view = str_replace('{company_website}', $bookingpress_company_website, $bookingpress_invoice_html_view);
                    $bookingpress_invoice_html_view = str_replace('{company_address}', $bookingpress_company_address, $bookingpress_invoice_html_view);
                    $bookingpress_invoice_html_view = str_replace('{company_phone}', $bookingpress_company_phone, $bookingpress_invoice_html_view);                                                          
                    $bookingpress_invoice_html_view = str_replace('{appointment_time}', $bookingpress_appointment_start_time. ' - '.$bookingpress_appointment_end_time, $bookingpress_invoice_html_view);

                    $bookingpress_invoice_html_view = str_replace('{customer_fullname}',$bookingpress_customer_fullname , $bookingpress_invoice_html_view);
                    $bookingpress_invoice_html_view = str_replace('{customer_firstname}',$bookingpress_customer_firstname , $bookingpress_invoice_html_view);
                    $bookingpress_invoice_html_view = str_replace('{customer_lastname}', $bookingpress_customer_lastname, $bookingpress_invoice_html_view);
                    $bookingpress_invoice_html_view = str_replace('{customer_email}', $bookingpress_customer_email, $bookingpress_invoice_html_view);
                    $bookingpress_invoice_html_view = str_replace('{customer_phone}', $bookingpress_customer_phone, $bookingpress_invoice_html_view);

                    $bookingpress_invoice_html_view = str_replace('{subtotal_amt}', $bookingpress_subtotal_val_with_currency, $bookingpress_invoice_html_view);
                    $bookingpress_invoice_html_view = str_replace('{deposit_amt}', $bookingpress_deposit_amt_with_currency, $bookingpress_invoice_html_view);
                    $bookingpress_invoice_html_view = str_replace('{discount_amt}', $bookingpress_coupon_discount, $bookingpress_invoice_html_view);
                    $bookingpress_invoice_html_view = str_replace('{coupon_code}', $bookingpress_coupon_code, $bookingpress_invoice_html_view);
                    $bookingpress_invoice_html_view = str_replace('{tax_amt}', $bookingpress_final_tax_amount, $bookingpress_invoice_html_view);
                    $bookingpress_invoice_html_view = str_replace('{total_amt}', $bookingpress_total_amount_with_currency, $bookingpress_invoice_html_view);
                    $bookingpress_invoice_html_view = str_replace('{paid_amt}', $bookingpress_paid_amount_with_currency, $bookingpress_invoice_html_view);
                    $bookingpress_invoice_html_view = str_replace('{due_amt}', $bookingpress_due_amount_with_currency, $bookingpress_invoice_html_view);
                    $bookingpress_invoice_html_view = str_replace('{payment_method}', $bookingpress_payment_method, $bookingpress_invoice_html_view);
                    $bookingpress_invoice_html_view = apply_filters('bookingpress_change_label_value_for_invoice',$bookingpress_invoice_html_view, $log_detail);

                    /* Bookingpress replace form custom field */                    
                    
                    if(!empty($bookingpress_appointment_details[0]['bookingpress_appointment_booking_id'])) {

                        $bookingpress_appointment_booking_id= $bookingpress_appointment_details[0]['bookingpress_appointment_booking_id'];

                        $bookingpress_appointment_custom_fields_meta_values = method_exists( $bookingpress_pro_appointment, 'bookingpress_get_appointment_form_field_data' ) ?  $bookingpress_pro_appointment->bookingpress_get_appointment_form_field_data($bookingpress_appointment_booking_id) : array();

                        if(!empty($bookingpress_appointment_custom_fields_meta_values)){

                            foreach($bookingpress_appointment_custom_fields_meta_values as $k2 => $v2) {
                                $bookingpress_field_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_field_type,bookingpress_field_options,bookingpress_field_values FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_field_meta_key = %s AND bookingpress_is_customer_field = %d", $k2,0) );// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm
                                //Replace all custom fields values
                                if(!empty($bookingpress_field_data) && $bookingpress_field_data->bookingpress_field_type == 'date' && !empty($bookingpress_field_data->bookingpress_field_options) && !empty($v2)) {
                                    $bookingpress_field_options = json_decode($bookingpress_field_data->bookingpress_field_options,true);
                                    if(!empty($bookingpress_field_options['enable_timepicker']) && $bookingpress_field_options['enable_timepicker'] == 'true') {                                        
                                        $v2 = date($bookingpress_date_time_format,strtotime($v2));
                                    } else {
                                        $v2 = date($bookingpress_global_date_format,strtotime($v2));
                                    }
                                }
                                if( is_array( $v2 ) ){
                                    $v2 = implode( ',', $v2 );
                                }                                
                                $bookingpress_invoice_html_view       = str_replace( '{'.$k2.'}', $v2, $bookingpress_invoice_html_view);
                            }        
                            $bookingpress_existing_custom_fields = $wpdb->get_results($wpdb->prepare("SELECT bookingpress_field_meta_key FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_field_is_default = %d",0), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm
                            if(!empty($bookingpress_existing_custom_fields)){
                                foreach($bookingpress_existing_custom_fields as $k3 => $v3){
                                    if(!array_key_exists($v3['bookingpress_field_meta_key'], $bookingpress_appointment_custom_fields_meta_values)){
                                        $bookingpress_invoice_html_view = str_replace( '{'.$v3['bookingpress_field_meta_key'].'}', '', $bookingpress_invoice_html_view);
                                    }
                                }
                            }
                        }else{
                            $bookingpress_existing_custom_fields = $wpdb->get_results($wpdb->prepare("SELECT bookingpress_field_meta_key FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_field_is_default =%d",0), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm
                            if(!empty($bookingpress_existing_custom_fields)){
                                foreach($bookingpress_existing_custom_fields as $k3 => $v3){
                                    $bookingpress_invoice_html_view       = str_replace( '{'.$v3['bookingpress_field_meta_key'].'}', '', $bookingpress_invoice_html_view);
                                }
                            }
                        }

                    }
                    $bookingpress_invoice_html_view = stripslashes($bookingpress_invoice_html_view);
            }
            return $bookingpress_invoice_html_view;
        }
        
        function bookingpress_email_notification_attachment_func($attachments,$email_temp_data,$appointment_id,$template_type, $notification_name, $appointment_data) {
            
            global $bookingpress_pdf_constructor,$wpdb,$bookigpress_pdf_upload_dir_name,$tbl_bookingpress_payment_logs,$bookingpress_invoice_pdf_upload_dir,$BookingPress;

            if(!empty($email_temp_data)){

                if(isset($email_temp_data['bookingpress_email_pdf_attachment_status']) && $email_temp_data['bookingpress_email_pdf_attachment_status'] == 1 && !empty($appointment_id)){

                    $wp_upload_dir  = wp_upload_dir();

                    $log_detail = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $tbl_bookingpress_payment_logs . " WHERE `bookingpress_appointment_booking_ref`=%d",$appointment_id), ARRAY_A); 

                    $bookingpress_payment_log_id = !empty($log_detail['bookingpress_payment_log_id']) ? intval($log_detail['bookingpress_payment_log_id']) : intval($appointment_data['bookingpress_payment_id']) ;  
                    $bookingpress_invoice_id = !empty($log_detail['bookingpress_invoice_id']) ? sanitize_text_field($log_detail['bookingpress_invoice_id']) : 0 ;  
                    
                    if(empty($log_detail['bookingpress_invoice_id']) && !empty($bookingpress_payment_log_id)) {
                        $log_detail = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $tbl_bookingpress_payment_logs . " WHERE `bookingpress_payment_log_id`=%d",$bookingpress_payment_log_id), ARRAY_A);  // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm 
                        $bookingpress_invoice_id = !empty($log_detail['bookingpress_invoice_id']) ? sanitize_text_field($log_detail['bookingpress_invoice_id']) : 0 ;  
                    }

                    $invoice_css='body {margin: 0;padding: 0;font: 12pt "Tahoma";}.page {width: 700px;min-height: 600px;padding: 20px;margin: 0 auto;background: white;}';
                    $bookingpress_invoice_html_view='';

                    if (!empty($bookingpress_payment_log_id) && !empty($bookingpress_invoice_id)) {

                        try {                                
                            $bookingpress_invoice_html_view .= $this->bookingpress_pdf_invoice_html_content($bookingpress_payment_log_id);      

                            $bookingpress_pdf_title=__('Invoice','bookingpress-invoice').' #'.intval($log_detail['bookingpress_invoice_id']);
                            $pdf_template_name='invoice_'.intval($log_detail['bookingpress_invoice_id']);
                                
                            $bookingpress_invoice_html_doc = new DOMDocument();
                            $bookingpress_invoice_html_doc->loadHTML($bookingpress_invoice_html_view);
                            $style_output = $bookingpress_invoice_html_doc->getElementsByTagName("style");
                            if($style_output->length>0){
                                for ($i=0; $i < $style_output->length; $i++) { 
                                    $invoice_css .=$style_output->item($i)->nodeValue;;
                                }
                            }
                            $bookingpress_invoice_html_view = preg_replace('/<style\b[^>]*>(.*?)<\/style>/i', '', $bookingpress_invoice_html_view);
                            $bookingpress_invoice_html_view = preg_replace('/<script\b[^>]*>(.*?)<\/script>/i', '', $bookingpress_invoice_html_view);
                            if( empty( $bookingpress_pdf_constructor ) || null == $bookingpress_pdf_constructor ){
                                $this->bookingpress_load_mpdf_library();
                                global $bookingpress_pdf_constructor;
                            }
                            $bookingpress_pdfcreator_mpdf1 = new Mpdf\Mpdf($bookingpress_pdf_constructor);
                            $bookingpress_pdfcreator_mpdf1->setAutoTopMargin = 'stretch';
                            $bookingpress_pdfcreator_mpdf1->setAutoBottomMargin = 'stretch';
                            $bookingpress_pdfcreator_mpdf1->autoScriptToLang = true;
                            $bookingpress_pdfcreator_mpdf1->baseScript = 1;
                            $bookingpress_pdfcreator_mpdf1->autoVietnamese = true;
                            $bookingpress_pdfcreator_mpdf1->autoArabic = true;
                            $bookingpress_pdfcreator_mpdf1->autoLangToFont = true;
                            if(is_rtl())
                            {
                                $bookingpress_pdfcreator_mpdf1->SetDirectionality('rtl');
                            }

                            $bookingpress_pdfcreator_mpdf1->WriteHTML($invoice_css,1);
                            $bookingpress_pdfcreator_mpdf1->WriteHTML($bookingpress_invoice_html_view,2);
                            $bookingpress_pdfcreator_mpdf1->SetTitle($bookingpress_pdf_title);
                            $pdffilename = $bookingpress_invoice_pdf_upload_dir.'/'.$pdf_template_name.'.pdf';
                            $bookingpress_pdfcreator_mpdf1->Output($pdffilename, 'F');
                            $attachments[]=$pdffilename;                                

                        } catch (\Mpdf\MpdfException $e) {                                    
                            $BookingPress->bookingpress_write_response('Exception in PDF create for Invoice Email notification=> '.addslashes($e->getMessage()).' in '.$e->getFile().' on line '.$e->getLine());
                         }    
                    }
                }                                                                      
                
            }

            return $attachments;
        }

        function bookingpress_get_email_template_details_filter($bookingpress_template_data,$bookingpress_email_data) {

            if(isset($bookingpress_email_data['bookingpress_email_pdf_attachment_status'])) {
                $bookingpress_template_data['bookingpress_email_pdf_attachment_status'] = esc_html($bookingpress_email_data['bookingpress_email_pdf_attachment_status']);
            }
            return $bookingpress_template_data; 
        }

        function bookingpress_save_email_notification_data_filter_func($bookingpress_database_modify_data,$notification_data){

            if(!empty($notification_data['bookingpress_email_pdf_attachment_status'])) {
                $bookingpress_database_modify_data['bookingpress_email_pdf_attachment_status'] = ($notification_data['bookingpress_email_pdf_attachment_status'] == 'true') ? 1 : 0 ;
            }
            return $bookingpress_database_modify_data;
        }

        function bookingpress_invoice_get_fonts_arr() {            
            $font_arr = array(
                'aboriginalsans'        => array( 'AboriginalSansREGULAR' => array('R'=>'AboriginalSansREGULAR.ttf' ) ),
                'abyssinicasil'         => array( 'Abyssinica_SIL' => array('R'=>'Abyssinica_SIL.ttf' ) ),
                'aegean'                => array( 'Aegean' => array('R'=>'Aegean.otf' ) ),
                'aegyptus'              => array( 'Aegyptus' => array('R'=>'Aegyptus.otf' ) ),
                'akkadian'              => array( 'Akkadian' => array('R'=>'Akkadian.otf' ) ),
                'ayar'                  => array( 'Ayar' => array('R'=>'ayar.ttf' ) ),
                'mph2bdamase'           => array( 'Damase' => array('R'=>'damase_v.2.ttf' ) ),
                'daibannasilbook'       => array( 'DBSILBR' => array('R'=>'DBSILBR.ttf' ) ),
                'dejavusans'            => array( 'DejaVuSans' => array('R'=>'DejaVuSans.ttf','B'=>'DejaVuSans-Bold.ttf','I'=>'DejaVuSans-Oblique.ttf','BI'=>'DejaVuSans-BoldOblique.ttf' ) ),
                'dejavusanscondensed'   => array('DejaVuSansCondensed' => array('R'=>'DejaVuSansCondensed.ttf','B'=>'DejaVuSansCondensed-Bold.ttf','I'=>'DejaVuSansCondensed-Oblique.ttf','BI'=>'DejaVuSansCondensed-BoldOblique.ttf' ) ),
                'dejavusansmono'        => array('DejaVuSansMono' => array('R'=>'DejaVuSansMono.ttf','B'=>'DejaVuSansMono-Bold.ttf','I'=>'DejaVuSansMono-Oblique.ttf','BI'=>'DejaVuSansMono-BoldOblique.ttf' ) ),
                'dejavuserifcondensed'  => array( 'DejaVuSerifCondensed' => array('R'=>'DejaVuSerifCondensed.ttf', 'B'=>'DejaVuSerifCondensed-Bold.ttf', 'I'=>'DejaVuSerifCondensed-Italic.ttf', 'BI'=>'DejaVuSerifCondensed-BoldItalic.ttf' ) ),
                'dhyana'                => array( 'Dhyana' => array('R'=>'Dhyana-Regular.ttf','B'=>'Dhyana-Bold.ttf' ) ),
                'freemono'              => array( 'FreeMono' => array('R'=>'FreeMono.ttf','B'=>'FreeMonoBold.ttf','I'=>'FreeMonoOblique.ttf','BI'=>'FreeMonoBoldOblique.ttf' ) ),
                'freesans'              => array( 'FreeSans' => array('R'=> 'FreeSans.ttf', 'B'=>'FreeSansBold.ttf','I'=> 'FreeSansOblique.ttf','BI'=> 'FreeSansBoldOblique.ttf' ) ),
                'freeserif'             => array( 'FreeSerif' => array('R'=> 'FreeSerif.ttf','B'=> 'FreeSerifBold.ttf','I'=> 'FreeSerifItalic.ttf', 'BI'=>'FreeSerifBoldItalic.ttf' ) ),
                'garuda'                => array( 'Garuda' => array('R'=> 'Garuda.ttf','B'=> 'Garuda-Bold.ttf', 'I'=>'Garuda-Oblique.ttf', 'BI'=>'Garuda-BoldOblique.ttf' ) ),
                'jomolhari'             => array( 'Jomolhari' => array('R'=> 'Jomolhari.ttf' ) ),
                'kaputaunicode'         => array( 'kaputaunicode' => array('R'=> 'kaputaunicode.ttf' ) ),
                'khmeros'               => array( 'KhmerOS' => array('R'=> 'KhmerOS.ttf' ) ),
                'lannaalif'             => array( 'lannaalif' => array( 'R'=>'lannaalif-v1-03.ttf' ) ),
                'lateef'                => array( 'LateefRegOT' => array( 'R'=>'LateefRegOT.ttf' ) ),
                'lohitkannada'          => array( 'Lohit-Kannada' => array( 'R'=>'Lohit-Kannada.ttf' ) ),
                'ocrb'                  => array( 'ocrb' => array('R'=> 'ocrb10.ttf' ) ),
                'padaukbook'            => array( 'Padauk-book' => array( 'R'=>'Padauk-book.ttf' ) ),
                'pothana2000'           => array( 'Pothana2000' => array('R'=> 'Pothana2000.ttf' ) ),
                'quivira'               => array( 'Quivira' => array('R'=> 'Quivira.otf' ) ),
                'sundaneseunicode'      => array( 'SundaneseUnicode' => array( 'R'=>'SundaneseUnicode-1.0.5.ttf' ) ),
                'sun-exta'              => array( 'Sun-ExtA' => array('R'=> 'Sun-ExtA.ttf' ) ),
                'sun-extb'              => array( 'Sun-ExtB' => array('R'=> 'Sun-ExtB.ttf' ) ),
                'estrangeloedessa'      => array( 'SyrCOMEdessa' => array('R'=> 'SyrCOMEdessa.otf' ) ),
                'taameydavidclm'        => array( 'TaameyDavidCLM-Medium' => array('R'=> 'TaameyDavidCLM-Medium.ttf' ) ),
                'taiheritagepro'        => array( 'TaiHeritagePro' => array( 'R'=>'TaiHeritagePro.ttf' ) ),
                'tharlon'               => array( 'Tharlon' => array('R'=> 'Tharlon-Regular.ttf' ) ),
                'unbatang'              => array( 'UnBatang_0613' => array('R'=> 'UnBatang_0613.ttf' ) ),
                'kfgqpcuthmantahanaskh' => array( 'Uthman' => array('R'=> 'Uthman.otf' ) ),
                'xbriyaz'               => array( 'XB Riyaz' => array('R'=> 'XB Riyaz.ttf','B'=> 'XB RiyazBd.ttf','I'=> 'XB RiyazIt.ttf','BI'=> 'XB RiyazBdIt.ttf' ) ),
                'zawgyi-one'            => array( 'ZawgyiOne' => array('R'=> 'ZawgyiOne.ttf' ) ),
            );
            return $font_arr;
        }
    
        function bookingpress_invoice_fonts_category(){
                $font_categories = array(
                    'aboriginalsans'        => 'Cree, Canadian, Aboriginal, Inuktuit',
                    'abyssinicasil'         => 'Ethiopic',
                    'aegean'                => 'Carian Lycian, Lydian, Phoenecian, Ugaritic, Linear B Old, Italic',
                    'aegyptus'              => 'Egyptian, Hieroglyphs',
                    'akkadian'              => 'Cuneiforn',
                    'ayar'                  => 'Myanmar',
                    'mph2bdamase'           => 'Glagolitic, Shavian, Osmanya, Kharoshti, Deserti',
                    'daibannasilbook'       => 'New Tai Lue',
                    'dejavusans'            => 'Generic',
                    'dejavusanscondensed'   => 'Generic',
                    'dejavusansmono'        => 'Generic',
                    'dejavuserif'           => 'Generic',
                    'dejavuserifcondensed'  => 'Generic',
                    'dhyana'                => 'Lao',
                    'freemono'              => 'Generic',
                    'freesans'              => 'Generic',
                    'freeserif'             => 'Generic',
                    'garuda'                => 'Thai',
                    'jomolhari'             => 'Tibetan',
                    'kaputaunicode'         => 'Sinhala',
                    'khmeros'               => 'Khmer',
                    'lannaalif'             => 'Tai Tham',
                    'lateef'                => 'Sindhi',
                    'lohitkannada'          => 'Kannada',
                    'ocrb'                  => 'Generic',
                    'padaukbook'            => 'Myanmar',
                    'pothana2000'           => 'Telugu',
                    'quivira'               => 'Coptic Buhid, Tagalog, Tagbanwa, Lisu',
                    'sundaneseunicode'      => 'Sundanese',
                    'sun-exta'              => 'Chinese, Japanese, Runic',
                    'sun-extb'              => 'Chinese, Japanese, Runic',
                    'estrangeloedessa'      => 'Syriac',
                    'taameydavidclm'        => 'Hebrew',
                    'taiheritagepro'        => 'Tai Viet',
                    'tharlon'               => 'Myanmar Tai Le',
                    'unbatang'              => 'Korean',
                    'kfgqpcuthmantahanaskh' => 'Arabic',
                    'xbriyaz'               => 'Arabic',
                    'zawgyi-one'            => 'Myanmar'
                );

                return $font_categories;
        }
    }    

    global $bookingpress_invoice;
	$bookingpress_invoice = new bookingpress_invoice;
}
