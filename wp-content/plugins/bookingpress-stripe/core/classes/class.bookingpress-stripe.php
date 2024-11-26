<?php

if (!class_exists('bookingpress_stripe')) {
	class bookingpress_stripe {
		function __construct() {
            register_activation_hook(BOOKINGPRESS_STRIPE_DIR.'/bookingpress-stripe.php', array('bookingpress_stripe', 'install'));
            register_uninstall_hook(BOOKINGPRESS_STRIPE_DIR.'/bookingpress-stripe.php', array('bookingpress_stripe', 'uninstall'));

            //Admiin notices
            add_action('admin_notices', array($this, 'bookingpress_admin_notices'));
            if( !function_exists('is_plugin_active') ){
                include_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            if(is_plugin_active('bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php')){

                //add front side stripe option
                add_action('bpa_front_add_payment_gateway', array($this, 'bookingpress_add_frontend_payment_gateway'), 10);
                add_filter('bookingpress_frontend_apointment_form_add_dynamic_data', array($this, 'bookingpress_frontend_data_fields_for_stripe'), 10);

                //validation message at front side while book appointment
                add_action('bookingpress_validate_booking_form', array($this, 'bookingpress_add_validation_code_before_book_appointment_func'), 10, 1);
                add_filter('bookingpress_after_selecting_payment_method', array($this, 'bookingpress_after_selecting_payment_method_func'), 10, 1);

                //Load SCA JS
                add_action( 'bookingpress_add_frontend_js', array( $this, 'set_front_js' ), 1 );

                //Add debug log section
    			add_filter('bookingpress_add_debug_logs_section', array($this, 'bookingpress_add_debug_logs_func'));

                //Validate stripe currency
                add_filter('bookingpress_currency_support', array($this, 'bookingpress_currency_support_func'), 10, 2);
                add_filter('bookingpress_pro_validate_currency_before_book_appointment', array($this, 'bookingpress_pro_validate_currency_before_book_appointment_func'), 10, 3);

                //Filter for add payment gateway to revenue filter list
			    add_filter('bookingpress_revenue_filter_payment_gateway_list_add', array($this, 'bookingpress_revenue_filter_payment_gateway_list_add_func'));

                add_action('bookingpress_gateway_listing_field',array($this,'bookingpress_gateway_listing_field_func'));
                add_filter('bookingpress_add_setting_dynamic_data_fields',array($this,'bookingpress_add_setting_dynamic_data_fields_func'));
                add_filter('bookingpress_addon_list_data_filter',array($this,'bookingpress_addon_list_data_filter_func'));

                add_filter('bookingpress_modify_customize_data_fields',array($this,'bookingpress_modify_customize_data_fields_func'));
                add_action('bookingpress_add_booking_form_summary_label_data',array($this,'bookingpress_add_booking_form_summary_label_data_func'));

                add_filter('bookingpress_get_booking_form_customize_data_filter',array($this,'bookingpress_get_booking_form_customize_data_filter_func'));

                add_filter('bookingpress_allowed_payment_gateway_for_refund',array($this,'bookingpress_allowed_payment_gateway_for_refund_func'));

                if(is_plugin_active('bookingpress-multilanguage/bookingpress-multilanguage.php')) {
					add_filter('bookingpress_modified_language_translate_fields',array($this,'bookingpress_modified_language_translate_fields_func'),10);
                	add_filter('bookingpress_modified_customize_form_language_translate_fields',array($this,'bookingpress_modified_language_translate_fields_func'),10);
				}

                /* Package Addon Payment GateWay Added Start */

                add_action('bpa_front_package_order_add_payment_gateway', array($this, 'bpa_front_package_order_add_payment_gateway_func'), 10);
                add_filter('bookingpress_frontend_package_order_form_add_dynamic_data', array($this, 'bookingpress_frontend_package_order_form_add_dynamic_data_func'), 10);
                add_filter('bookingpress_after_selecting_payment_method_for_package_order', array($this, 'bookingpress_after_selecting_payment_method_for_package_order_func'), 10, 1);

                add_filter('bookingpress_modified_package_customization_fields', array($this, 'bookingpress_modified_package_customization_fields_func'), 10,1);
                add_filter('bookingpress_customized_package_booking_summary_step_labels_translate', array($this, 'bookingpress_customized_package_booking_summary_step_labels_translate'), 10,1);
                add_action('bookingpress_add_package_label_settings_dynamically',array($this,'bookingpress_add_package_label_settings_dynamically_func'));
                

                /* Package Addon Payment GateWay Added Over */

                
                /* Gift Card Addon Payment GateWay Added Start */

                add_action('bpa_front_gift_card_order_add_payment_gateway', array($this, 'bpa_front_gift_card_order_add_payment_gateway_func'), 10);
                add_filter('bookingpress_frontend_gift_card_order_form_add_dynamic_data', array($this, 'bookingpress_frontend_gift_card_order_form_add_dynamic_data_func'), 10);
                add_filter('bookingpress_after_selecting_payment_method_for_gift_card', array($this, 'bookingpress_after_selecting_payment_method_for_gift_card_func'), 10, 1);

                add_filter('bookingpress_get_gift_card_customize_data_filter', array($this, 'bookingpress_get_gift_card_customize_data_filter_func'), 10,1);
                add_filter('bookingpress_customized_gift_card_booking_summary_step_labels_translate', array($this, 'bookingpress_customized_gift_card_booking_summary_step_labels_translate'), 10,1);
                add_action('bookingpress_add_gift_card_label_settings_dynamically',array($this,'bookingpress_add_gift_card_label_settings_dynamically_func'));
                /* Gift Card Addon Payment GateWay Added Over */
			}
            
            add_action('activated_plugin',array($this,'bookingpress_is_addon_activated'),11,2);
            add_action( 'admin_init', array( $this, 'bookingpress_stripe_upgrade_data' ) );
		}
	function bookingpress_customized_gift_card_booking_summary_step_labels_translate($bookingpress_customized_gift_card_booking_summary_step_labels){
            $bookingpress_customized_gift_card_booking_summary_step_labels['stripe_text'] = array('field_type'=>'text','field_label'=>__('Stripe payment title', 'bookingpress-stripe'),'save_field_type'=>'gift_card_form');            
            return $bookingpress_customized_gift_card_booking_summary_step_labels;
        }

        function bookingpress_add_gift_card_label_settings_dynamically_func() {            
            ?>
            <div class="bpa-sm--item">
                <label class="bpa-form-label"><?php esc_html_e('Stripe payment title', 'bookingpress-stripe'); ?></label>
                <el-input v-model="gift_card_form_settings.stripe_text" class="bpa-form-control"></el-input>
            </div>                 
            <?php            
        }

        function bookingpress_get_gift_card_customize_data_filter_func($bookingpress_gift_card_field_settings) {
            global $BookingPress;
            $stripe_text = $BookingPress->bookingpress_get_customize_settings('stripe_text', 'gift_card_form');
            $bookingpress_gift_card_field_settings['stripe_text'] = $stripe_text;
            return $bookingpress_gift_card_field_settings;
        }

        /**
         * Function for after select payment method
         *
         * @param  mixed $bookingpress_after_selecting_payment_method_data
         * @return void
         */
        function bookingpress_after_selecting_payment_method_for_gift_card_func($bookingpress_after_selecting_payment_method_data){
            $bookingpress_after_selecting_payment_method_data .= '
            if(vm.gift_card_step_form_data.selected_payment_method == "stripe" && vm.stripe_payment_method == "built_in_form_fields"){
                bookingpress_allowed_payment_gateways_for_card_fields.push("stripe");
            }';
            return $bookingpress_after_selecting_payment_method_data;
		}

        /**
         * Function for payment method Gift Card data add
         *
         * @param  mixed $bookingpress_front_vue_data_fields
         * @return void
         */
        function bookingpress_frontend_gift_card_order_form_add_dynamic_data_func($bookingpress_front_vue_data_fields){
            global $BookingPress;
            $bookingpress_stripe_payment_method = $BookingPress->bookingpress_get_settings('stripe_payment_method', 'payment_setting');
            $bookingpress_stripe_payment_method = !empty($bookingpress_stripe_payment_method) ? $bookingpress_stripe_payment_method : 'sca_popup';

			$bookingpress_front_vue_data_fields['stripe_payment'] = $this->is_addon_activated();
            $bookingpress_front_vue_data_fields['stripe_payment_method'] = $bookingpress_stripe_payment_method;            
            $bookingpress_is_gateway_enable = $BookingPress->bookingpress_get_settings('stripe_payment', 'payment_setting');
            if($bookingpress_is_gateway_enable == 'true'){
                $bookingpress_front_vue_data_fields['is_only_onsite_enabled'] = 0;
                $bookingpress_front_vue_data_fields['bookingpress_activate_gift_card_payment_gateway_counter'] = $bookingpress_front_vue_data_fields['bookingpress_activate_gift_card_payment_gateway_counter'] + 1;
            }
            $bookingpress_front_vue_data_fields['stripe_text'] = $BookingPress->bookingpress_get_customize_settings('stripe_text', 'gift_card_form');
            return $bookingpress_front_vue_data_fields;
        }


        /**
         * Function for add front Gift Card payment gateway
         *
         * @return void
         */
        function bpa_front_gift_card_order_add_payment_gateway_func(){
            global $BookingPress;
            $bookingpress_is_gateway_enable = $BookingPress->bookingpress_get_settings('stripe_payment', 'payment_setting');
            if($bookingpress_is_gateway_enable == 'true'){
            ?>
            <div class="bpgc-front-module--pm-body__item" :class="(gift_card_step_form_data.selected_payment_method == 'stripe') ? '__bpgc-is-selected' : ''" @click="select_payment_method('stripe')" v-if="stripe_payment != 'false' && stripe_payment != ''">
                <svg class="bpgc-front-pm-pay-local-icon" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm-1 14H5c-.55 0-1-.45-1-1v-5h16v5c0 .55-.45 1-1 1zm1-10H4V6h16v2z"/></svg>
                    <p>{{stripe_text}}</p>
                    <div class="bpgc-front-si-card--checkmark-icon" v-if="gift_card_step_form_data.selected_payment_method == 'stripe'">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zM9.29 16.29 5.7 12.7c-.39-.39-.39-1.02 0-1.41.39-.39 1.02-.39 1.41 0L10 14.17l6.88-6.88c.39-.39 1.02-.39 1.41 0 .39.39.39 1.02 0 1.41l-7.59 7.59c-.38.39-1.02.39-1.41 0z"/></svg>
                    </div>
                </div>
            <?php
            }
        }
        function bookingpress_add_package_label_settings_dynamically_func() {            
            ?>
            <div class="bpa-sm--item">
                <label class="bpa-form-label"><?php esc_html_e('Stripe payment title', 'bookingpress-stripe'); ?></label>
                <el-input v-model="package_booking_form_settings.stripe_text" class="bpa-form-control"></el-input>
            </div>                 
            <?php            
        }

        function bookingpress_customized_package_booking_summary_step_labels_translate($bookingpress_customized_package_booking_summary_step_labels){
            $bookingpress_customized_package_booking_summary_step_labels['stripe_text'] = array('field_type'=>'text','field_label'=>__('Stripe payment title', 'bookingpress-stripe'),'save_field_type'=>'package_booking_form');            
            return $bookingpress_customized_package_booking_summary_step_labels;
        }


        function bookingpress_modified_package_customization_fields_func($bookingpress_modified_package_customization_fields){            
            global $BookingPress;
            $stripe_text = $BookingPress->bookingpress_get_customize_settings('stripe_text', 'package_booking_form');
            $bookingpress_modified_package_customization_fields['stripe_text'] = $stripe_text;
            return $bookingpress_modified_package_customization_fields;
        }
                        
        /**
         * Function for after select payment method
         *
         * @param  mixed $bookingpress_after_selecting_payment_method_data
         * @return void
         */
        function bookingpress_after_selecting_payment_method_for_package_order_func($bookingpress_after_selecting_payment_method_data){
            $bookingpress_after_selecting_payment_method_data .= '
            if(vm.package_step_form_data.selected_payment_method == "stripe" && vm.stripe_payment_method == "built_in_form_fields"){
                bookingpress_allowed_payment_gateways_for_card_fields.push("stripe");
            }';
            return $bookingpress_after_selecting_payment_method_data;
		}

        /**
         * Function for add front package payment gateway
         *
         * @return void
         */
        function bpa_front_package_order_add_payment_gateway_func(){
            global $BookingPress;
            $bookingpress_is_gateway_enable = $BookingPress->bookingpress_get_settings('stripe_payment', 'payment_setting');
            if($bookingpress_is_gateway_enable == 'true'){
            ?>
                <div class="bpp-front-module--pm-body__item" :class="(package_step_form_data.selected_payment_method == 'stripe') ? '__bpp-is-selected' : ''" @click="select_payment_method('stripe')" v-if="stripe_payment != 'false' && stripe_payment != ''">
                <svg class="bpp-front-pm-pay-local-icon" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm-1 14H5c-.55 0-1-.45-1-1v-5h16v5c0 .55-.45 1-1 1zm1-10H4V6h16v2z"/></svg>
                    <p>{{stripe_text}}</p>
                    <div class="bpp-front-si-card--checkmark-icon" v-if="package_step_form_data.selected_payment_method == 'stripe'">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zM9.29 16.29 5.7 12.7c-.39-.39-.39-1.02 0-1.41.39-.39 1.02-.39 1.41 0L10 14.17l6.88-6.88c.39-.39 1.02-.39 1.41 0 .39.39.39 1.02 0 1.41l-7.59 7.59c-.38.39-1.02.39-1.41 0z"/></svg>
                    </div>
                </div>
            <?php
            }
        }
        
        /**
         * Function for payment method package data add
         *
         * @param  mixed $bookingpress_front_vue_data_fields
         * @return void
         */
        function bookingpress_frontend_package_order_form_add_dynamic_data_func($bookingpress_front_vue_data_fields){
            global $BookingPress;
            $bookingpress_stripe_payment_method = $BookingPress->bookingpress_get_settings('stripe_payment_method', 'payment_setting');
            $bookingpress_stripe_payment_method = !empty($bookingpress_stripe_payment_method) ? $bookingpress_stripe_payment_method : 'sca_popup';

			$bookingpress_front_vue_data_fields['stripe_payment'] = $this->is_addon_activated();
            $bookingpress_front_vue_data_fields['stripe_payment_method'] = $bookingpress_stripe_payment_method;            
            $bookingpress_is_gateway_enable = $BookingPress->bookingpress_get_settings('stripe_payment', 'payment_setting');
            if($bookingpress_is_gateway_enable == 'true'){
                $bookingpress_front_vue_data_fields['is_only_onsite_enabled'] = 0;
                $bookingpress_front_vue_data_fields['bookingpress_activate_package_payment_gateway_counter'] = $bookingpress_front_vue_data_fields['bookingpress_activate_package_payment_gateway_counter'] + 1;
            }
            $bookingpress_front_vue_data_fields['stripe_text'] = $BookingPress->bookingpress_get_customize_settings('stripe_text', 'package_booking_form');

			return $bookingpress_front_vue_data_fields;
        }

        function bookingpress_modified_language_translate_fields_func($bookingpress_all_language_translation_fields){

			$bookingpress_stripe_language_translation_fields = array(                
				'stripe_text' => array('field_type'=>'text','field_label'=>__('Stripe payment title', 'bookingpress-stripe'),'save_field_type'=>'booking_form'),                 
			);  
			$bookingpress_all_language_translation_fields['customized_form_summary_step_labels'] = array_merge($bookingpress_all_language_translation_fields['customized_form_summary_step_labels'], $bookingpress_stripe_language_translation_fields);
			return $bookingpress_all_language_translation_fields;
		}		
		
		
	function bookingpress_allowed_payment_gateway_for_refund_func($payment_gateway_data) {
            
            $payment_gateway_data['stripe'] = array(
                'full_status' => 1,
                'partial_status' => 1,
                'allow_days' => 0,
                'is_refund_support' => 1,
            );
            return $payment_gateway_data;
        }

        function bookingpress_stripe_upgrade_data(){
            global $BookingPress;
            $bookingpress_stripe_version = get_option('bookingpress_stripe_payment_gateway', true);

            if( version_compare( $bookingpress_stripe_version, '1.9', '<' ) ){
                $bookingpress_load_stripe_update_file = BOOKINGPRESS_STRIPE_DIR . '/core/views/upgrade_latest_data.php';
                include $bookingpress_load_stripe_update_file;
                $BookingPress->bookingpress_send_anonymous_data_cron();
            }
        }
        
        function bookingpress_is_addon_activated($plugin,$network_activation)
        {  
            $myaddon_name = "bookingpress-stripe/bookingpress-stripe.php";

            if($plugin == $myaddon_name)
            {

                if(!(is_plugin_active('bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php')))
                {
                    deactivate_plugins($myaddon_name, FALSE);
                    $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name);
                    $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Stripe Add-on', 'bookingpress-stripe');
					$bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-stripe'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
					wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                    die;
                }

                $license = trim( get_option( 'bkp_license_key' ) );
                $package = trim( get_option( 'bkp_license_package' ) );

                if( '' === $license || false === $license ) 
                {
                    deactivate_plugins($myaddon_name, FALSE);
                    $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name);
                    $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Stripe Add-on', 'bookingpress-stripe');
					$bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-stripe'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
					wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                    die;
                }
                else
                {
                    $store_url = BOOKINGPRESS_STRIPE_STORE_URL;
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
                            $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Stripe Add-on', 'bookingpress-stripe');
                            $bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-stripe'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
                            wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                            die;
                        }

                    }
                    else
                    {
                        deactivate_plugins($myaddon_name, FALSE);
                        $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name);
                        $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Stripe Add-on', 'bookingpress-stripe');
                        $bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-stripe'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
                        wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                        die;
                    }
                }
            }

        }

		function bookingpress_get_booking_form_customize_data_filter_func($booking_form_settings) {
			$booking_form_settings['front_label_edit_data']['stripe_text'] = '';
            return $booking_form_settings;
        }	

        function bookingpress_modify_customize_data_fields_func($bookingpress_customize_vue_data_fields) {
            $bookingpress_customize_vue_data_fields['front_label_edit_data']['stripe_text'] = __('Stripe', 'bookingpress-stripe');
            return $bookingpress_customize_vue_data_fields;            
        }

        function bookingpress_add_booking_form_summary_label_data_func() {            
            ?>
            <div class="bpa-sm--item">
                <label class="bpa-form-label"><?php esc_html_e('Stripe payment title', 'bookingpress-stripe'); ?></label>
                <el-input v-model="front_label_edit_data.stripe_text" class="bpa-form-control"></el-input>
            </div>                 
            <?php            
        }		

        function bookingpress_addon_list_data_filter_func($bookingpress_body_res){
            global $bookingpress_slugs;
            if(!empty($bookingpress_body_res)) {
                foreach($bookingpress_body_res as $bookingpress_body_res_key =>$bookingpress_body_res_val) {
                    $bookingpress_setting_page_url = add_query_arg('page', $bookingpress_slugs->bookingpress_settings, esc_url( admin_url() . 'admin.php?page=bookingpress' ));
                    $bookingpress_config_url = add_query_arg('setting_page', 'payment_settings', $bookingpress_setting_page_url);
                    if($bookingpress_body_res_val['addon_key'] == 'bookingpress_stripe_payment_gateway') {
                        $bookingpress_body_res[$bookingpress_body_res_key]['addon_configure_url'] = $bookingpress_config_url;
                    }
                }
            }
            return $bookingpress_body_res;
        }  		

        function bookingpress_add_setting_dynamic_data_fields_func($bookingpress_dynamic_setting_data_fields) {

            $bookingpress_dynamic_setting_data_fields['payment_setting_form']['stripe_payment'] = false;
            $bookingpress_dynamic_setting_data_fields['payment_setting_form']['stripe_payment_mode'] = 'sandbox';
            $bookingpress_dynamic_setting_data_fields['payment_setting_form']['stripe_secret_key'] = '';
            $bookingpress_dynamic_setting_data_fields['payment_setting_form']['stripe_payment_method'] = 'built_in_form_fields';
            $bookingpress_dynamic_setting_data_fields['payment_setting_form']['sca_popup_title'] = '';
            $bookingpress_dynamic_setting_data_fields['payment_setting_form']['stripe_publishable_key'] = '';
            $bookingpress_dynamic_setting_data_fields['payment_setting_form']['sca_payment_button_label'] = '';
            $bookingpress_dynamic_setting_data_fields['payment_setting_form']['stripe_custom_field'] = false;
            $bookingpress_dynamic_setting_data_fields['payment_setting_form']['bpa_stripe_country'] = '';
            
            $bookingpress_dynamic_setting_data_fields['stripe_country_fields'] = array(
                array(
                    'text' => 'Please select country',
                    'value' => ''
                ),
                array(
                    'text'  => 'Australia',
                    'value' => 'AU',
                ),
                array(
                    'text'  => 'Austria',
                    'value' => 'AT',
                ),
                array(
                    'text'  => 'Belgium',
                    'value' => 'BE',
                ),
                array(
                    'text'  => 'Brazil',
                    'value' => 'BR',
                ),
                array(
                    'text'  => 'Bulgaria',
                    'value' => 'BG',
                ),
                array(
                    'text'  => 'Bulgaria',
                    'value' => 'BG',
                ),
                array(
                    'text'  => 'Canada',
                    'value' => 'CA',
                ),
                array(
                    'text'  => 'Croatia',
                    'value' => 'HR',
                ),
                array(
                    'text'  => 'Cyprus',
                    'value' => 'CY',
                ),
                array(
                    'text'  => 'Czech Republic',
                    'value' => 'CZ',
                ),
                array(
                    'text'  => 'Denmark',
                    'value' => 'DK',
                ),
                array(
                    'text'  => 'Estonia',
                    'value' => 'EE',
                ),
                array(
                    'text'  => 'Finland',
                    'value' => 'FI',
                ),
                array(
                    'text'  => 'France',
                    'value' => 'FR',
                ),
                array(
                    'text'  => 'Germany',
                    'value' => 'DE',
                ),
                array(
                    'text'  => 'Ghana',
                    'value' => 'GH',
                ),
                array(
                    'text'  => 'Gibraltar',
                    'value' => 'GL',
                ),
                array(
                    'text'  => 'Greece',
                    'value' => 'GR',
                ),
                array(
                    'text'  => 'Hong Kong',
                    'value' => 'HK',
                ),
                array(
                    'text'  => 'Hungary',
                    'value' => 'HU',
                ),
                array(
                    'text'  => 'India',
                    'value' => 'IN',
                ),
                array(
                    'text'  => 'Indonesia',
                    'value' => 'ID',
                ),
                array(
                    'text'  => 'Ireland',
                    'value' => 'IE',
                ),
                array(
                    'text'  => 'Italy',
                    'value' => 'IT',
                ),
                array(
                    'text'  => 'Japan',
                    'value' => 'JP',
                ),
                array(
                    'text'  => 'Kenya',
                    'value' => 'KE',
                ),
                array(
                    'text'  => 'Latvia',
                    'value' => 'LV',
                ),
                array(
                    'text'  => 'Liechtenstein',
                    'value' => 'LI',
                ),
                array(
                    'text'  => 'Lithuania',
                    'value' => 'LT',
                ),
                array(
                    'text'  => 'Luxembourg',
                    'value' => 'LU',
                ),
                array(
                    'text'  => 'Malaysia',
                    'value' => 'MY',
                ),
                array(
                    'text'  => 'Malta',
                    'value' => 'MT',
                ),
                array(
                    'text'  => 'Mexico',
                    'value' => 'MX',
                ),
                array(
                    'text'  => 'Netherlands',
                    'value' => 'NL',
                ),
                array(
                    'text'  => 'New Zealand',
                    'value' => 'NZ',
                ),
                array(
                    'text'  => 'Nigeria',
                    'value' => 'NG',
                ),
                array(
                    'text'  => 'Norway',
                    'value' => 'NO',
                ),
                array(
                    'text'  => 'Poland',
                    'value' => 'PL',
                ),
                array(
                    'text'  => 'Poland',
                    'value' => 'PL',
                ),
                array(
                    'text'  => 'Portugal',
                    'value' => 'PT',
                ),
                array(
                    'text'  => 'Romania',
                    'value' => 'RO',
                ),
                array(
                    'text'  => 'Singapore',
                    'value' => 'SG',
                ),
                array(
                    'text'  => 'Slovakia',
                    'value' => 'SK',
                ),
                array(
                    'text'  => 'Slovenia',
                    'value' => 'SI',
                ),
                array(
                    'text'  => 'South Africa',
                    'value' => 'SA',
                ),
                array(
                    'text'  => 'Spain',
                    'value' => 'ES',
                ),
                array(
                    'text'  => 'Sweden',
                    'value' => 'SE',
                ),
                array(
                    'text'  => 'Switzerland',
                    'value' => 'CH',
                ),
                array(
                    'text'  => 'Thailand',
                    'value' => 'TH',
                ),
                array(
                    'text'  => 'United Arab Emirates',
                    'value' => 'AE',
                ),
                array(
                    'text'  => 'United Kingdom',
                    'value' => 'GB',
                ),
                array(
                    'text'  => 'United States',
                    'value' => 'US',
                ),

            );

            $stripe_rules =  array(
                'stripe_secret_key' => array(
                    array(
                        'required' => true,
                        'message'  => __( 'Please enter api key', 'bookingpress-stripe' ).'.',
                        'trigger'  => 'change',
                    ),
                ),
                'stripe_publishable_key' => array(
                    array(
                        'required' => true,
                        'message'  => __( 'Please enter  publishable key', 'bookingpress-stripe' ).'.',
                        'trigger'  => 'change',
                    ),
                ),
                'sca_popup_title' => array(
                    array(
                        'required' => true,
                        'message'  => __( 'Please enter sca popup title', 'bookingpress-stripe' ).'.',
                        'trigger'  => 'change',
                    ),
                ),
                'sca_payment_button_label' => array(
                    array(
                        'required' => true,
                        'message'  => __( 'Please enter payment button label', 'bookingpress-stripe' ).'.',
                        'trigger'  => 'change',
                    ),
                ),
            );
            $bookingpress_dynamic_setting_data_fields['rules_payment'] = array_merge($bookingpress_dynamic_setting_data_fields['rules_payment'],$stripe_rules);
            return $bookingpress_dynamic_setting_data_fields;            
        }

        function bookingpress_gateway_listing_field_func(){
            ?>
            <div class="bpa-pst-is-single-payment-box">
                <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                    <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
                        <h4> <?php esc_html_e('Stripe', 'bookingpress-stripe'); ?></h4>
                    </el-col>
                    <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                        <el-form-item prop="stripe_payment">
                            <el-switch class="bpa-swtich-control" v-model="payment_setting_form.stripe_payment"></el-switch>
                        </el-form-item>
                    </el-col>
                </el-row>
                <div class="bpa-ns--sub-module__card" v-if="payment_setting_form.stripe_payment == true">                               
                    <el-row type="flex" class="bpa-ns--sub-module__card--row">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Payment Mode', 'bookingpress-stripe'); ?></h4>
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">
                            <el-radio v-model="payment_setting_form.stripe_payment_mode" label="sandbox">Sandbox</el-radio>
                            <el-radio v-model="payment_setting_form.stripe_payment_mode" label="live">Live</el-radio>
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-ns--sub-module__card--row">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Secret Key', 'bookingpress-stripe'); ?></h4>
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">
                            <el-form-item prop="stripe_secret_key">
                                <el-input class="bpa-form-control" v-model="payment_setting_form.stripe_secret_key" placeholder="<?php esc_html_e('Enter secret key', 'bookingpress-stripe'); ?>"></el-input>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-ns--sub-module__card--row">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Publishable Key', 'bookingpress-stripe'); ?></h4>
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                            <el-form-item prop="stripe_publishable_key">
                                <el-input class="bpa-form-control" v-model="payment_setting_form.stripe_publishable_key" placeholder="<?php esc_html_e('Enter publishable key', 'bookingpress-stripe'); ?>"></el-input>
                            </el-form-item>    
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-ns--sub-module__card--row">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Payment Method', 'bookingpress-stripe'); ?></h4>
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">
                            <el-radio v-model="payment_setting_form.stripe_payment_method" label="sca_popup"><?php esc_html_e('SCA Compliant (Recommended)', 'bookingpress-stripe'); ?></el-radio>
                            <el-radio v-model="payment_setting_form.stripe_payment_method" label="built_in_form_fields"><?php esc_html_e('Built-In Form Fields', 'bookingpress-stripe'); ?></el-radio>
                        </el-col>
                    </el-row>     
                    <el-row type="flex" class="bpa-ns--sub-module__card--row bpa-ns--smc__align-unset" v-if="payment_setting_form.stripe_payment_method == 'sca_popup'">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
                            <h4> <?php esc_html_e('Enter popup title', 'bookingpress-stripe'); ?></h4>
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                            <el-form-item prop="sca_popup_title">
                                <el-input class="bpa-form-control" v-model="payment_setting_form.sca_popup_title" placeholder="<?php esc_html_e('Enter popup title', 'bookingpress-stripe'); ?>"></el-input>
                                <span class="bpa-sm__field-helper-label">{appointment_service} - <?php esc_html_e('This shortcode will display booked appointment service name', 'bookingpress-stripe'); ?></span>
                            </el-form-item>    
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-ns--sub-module__card--row bpa-ns--smc__align-unset" v-if="payment_setting_form.stripe_payment_method == 'sca_popup'">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
                            <h4> <?php esc_html_e('Payment Button Label', 'bookingpress-stripe'); ?></h4>
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                            <el-form-item prop="sca_payment_button_label">
                                <el-input class="bpa-form-control" v-model="payment_setting_form.sca_payment_button_label" placeholder="<?php esc_html_e('Enter payment button label', 'bookingpress-stripe'); ?>"></el-input>
                                <span class="bpa-sm__field-helper-label">{total_payable_amount} - <?php esc_html_e('This shortcode will display total payable amount', 'bookingpress-stripe'); ?></span>
                            </el-form-item>    
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-ns--sub-module__card--row bpa-rp-sub-module__card--webook-url-row">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Webhook URL', 'bookingpress-stripe'); ?></h4>
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                            <span class="bpa-rp-wu__item-val"><?php esc_html_e(add_query_arg('bookingpress-listener', 'bpa_pro_stripe_url', BOOKINGPRESS_HOME_URL. "/")); //phpcs:ignore ?></span>                        
                        </el-col>
                    </el-row>
                    <div class="bpa-gs__cb--item-heading">
                        <h4 class="bpa-sec--sub-heading" style="font-size:16px;font-weight:bold;"><?php esc_html_e('Country Specific Settings (optional)', 'bookingpress-stripe'); ?></h4>
                    </div>
                    
                    <el-row type="flex" class="bpa-ns--sub-module__card--row bpa-rp-sub-module__card--webook-url-row">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Select your country', 'bookingpress-stripe'); ?></h4>
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
                            <el-form-item prop="bpa_stripe_country">
                                <el-select class="bpa-form-control" v-model="payment_setting_form.bpa_stripe_country" popper-class="bpa-el-select--is-with-navbar" placeholder="<?php esc_html_e("Please select country", "bookingpress-stripe") ?>" filterable>
                                    <el-option v-for="bpa_country_data in stripe_country_fields" :value="bpa_country_data.value" :label="bpa_country_data.text">{{ bpa_country_data.text }}</el-option>
                                </el-select>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    
                    <el-row type="flex" class="bpa-ns--sub-module__card--row bpa-rp-sub-module__card--webook-url-row" v-if="payment_setting_form.bpa_stripe_country == 'IN'">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Accept International Payments', 'bookingpress-stripe'); ?></h4>
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
                            <el-form-item prop="stripe_custom_field">
                                <el-switch class="bpa-swtich-control" v-model="payment_setting_form.stripe_custom_field"></el-switch>
                            </el-form-item>
                            <label class="bpa-cb-il__desc"><?php echo sprintf( esc_html__('Enabling this option will requires additional steps to follow. Please refer our documentation %s', 'bookingpress-stripe'), '<a href="https://www.bookingpressplugin.com/documents/stripe#accept-international-payments-for-indian-account" target="_blank">'.esc_html__( 'here', 'bookingpress-stripe') .'</a>' ); //phpcs:ignore ?></label>	
                        </el-col>
                    </el-row>
                </div>
            </div>    
            <?php
        }

        function bookingpress_revenue_filter_payment_gateway_list_add_func($bookingpress_revenue_filter_payment_gateway_list){		
            $bookingpress_revenue_filter_payment_gateway_list[] = array(
                'value' => 'stripe',
                'text' => 'Stripe'
            );

			return $bookingpress_revenue_filter_payment_gateway_list;
		}

        function bookingpress_add_debug_logs_func($bookingpress_debug_log_gateways){
            $bookingpress_debug_log_gateways['stripe'] = 'Stripe';
			return $bookingpress_debug_log_gateways;
		}

        function bookingpress_admin_notices(){
            if(!is_plugin_active('bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php')){
                echo "<div class='notice notice-warning'><p>" . esc_html__('Bookingpress - Stripe plugin requires Bookingpress Premium Plugin installed and active.', 'bookingpress-stripe') . "</p></div>";
            }
        }

        public static function install(){
			global $wpdb, $tbl_bookingpress_customize_settings, $bookingpress_stripe_version, $BookingPress;
            $bookingpress_stripe_addon_version = get_option('bookingpress_stripe_payment_gateway');
            if (!isset($bookingpress_stripe_addon_version) || $bookingpress_stripe_addon_version == '') {

                $myaddon_name = "bookingpress-stripe/bookingpress-stripe.php";
                
                // activate license for this addon
                $posted_license_key = trim( get_option( 'bkp_license_key' ) );
			    $posted_license_package = '4641';

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
                    $message =  ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) ) ? $response->get_error_message() : __( 'An error occurred, please try again.','bookingpress-stripe' );
                } else {
                    $license_data = json_decode( wp_remote_retrieve_body( $response ) );
                    $license_data_string = wp_remote_retrieve_body( $response );
                    if ( false === $license_data->success ) {
                        switch( $license_data->error ) {
                            case 'expired' :
                                $message = sprintf(
                                    __( 'Your license key expired on %s.','bookingpress-stripe' ),
                                    date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
                                );
                                break;
                            case 'revoked' :
                                $message = __( 'Your license key has been disabled.','bookingpress-stripe' );
                                break;
                            case 'missing' :
                                $message = __( 'Invalid license.','bookingpress-stripe' );
                                break;
                            case 'invalid' :
                            case 'site_inactive' :
                                $message = __( 'Your license is not active for this URL.','bookingpress-stripe' );
                                break;
                            case 'item_name_mismatch' :
                                $message = __('This appears to be an invalid license key for your selected package.','bookingpress-stripe');
                                break;
                            case 'invalid_item_id' :
                                    $message = __('This appears to be an invalid license key for your selected package.','bookingpress-stripe');
                                    break;
                            case 'no_activations_left':
                                $message = __( 'Your license key has reached its activation limit.','bookingpress-stripe' );
                                break;
                            default :
                                $message = __( 'An error occurred, please try again.','bookingpress-stripe' );
                                break;
                        }

                    }

                }

                if ( ! empty( $message ) ) {
                    update_option( 'bkp_stripe_license_data_activate_response', $license_data_string );
                    update_option( 'bkp_stripe_license_status', $license_data->license );
                    deactivate_plugins($myaddon_name, FALSE);
                    $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name);
                    $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Stripe Add-on', 'bookingpress-stripe');
					$bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-stripe'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
					wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                    die;
                }
                
                if($license_data->license === "valid")
                {
                    update_option( 'bkp_stripe_license_key', $posted_license_key );
                    update_option( 'bkp_stripe_license_package', $posted_license_package );
                    update_option( 'bkp_stripe_license_status', $license_data->license );
                    update_option( 'bkp_stripe_license_data_activate_response', $license_data_string );
                }            
                

                update_option('bookingpress_stripe_payment_gateway', $bookingpress_stripe_version);

                $bookingpress_stripe_customize_text = $BookingPress->bookingpress_get_customize_settings('stripe_text', 'booking_form');
                if(empty($bookingpress_stripe_customize_text)){
                    $bookingpress_customize_settings_db_fields = array(
                        'bookingpress_setting_name'  => 'stripe_text',
                        'bookingpress_setting_value' => __('Credit Card', 'bookingpress-stripe'),
                        'bookingpress_setting_type'  => 'booking_form',
                    );

                    $wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_customize_settings_db_fields);
                }

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

                    $bookingpress_customize_settings_db_fields = array(
                        'bookingpress_setting_name'  => $key,
                        'bookingpress_setting_value' => $value,
                        'bookingpress_setting_type'  => 'gift_card_form',
                    );
                    $wpdb->insert( $tbl_bookingpress_customize_settings, $bookingpress_customize_settings_db_fields );
                }

            }
		}

        public static function uninstall(){
            delete_option('bookingpress_stripe_payment_gateway');

            delete_option('bkp_stripe_license_key');
            delete_option('bkp_stripe_license_package');
            delete_option('bkp_stripe_license_status');
            delete_option('bkp_stripe_license_data_activate_response');

        }

        public function is_addon_activated(){
            $bookingpress_stripe_module_version = get_option('bookingpress_stripe_payment_gateway');
            return !empty($bookingpress_stripe_module_version) ? 1 : 0;
        }

        function set_front_js(){
            global $BookingPress, $bookingpress_stripe_version;
            $bookingpress_stripe_payment_method = $BookingPress->bookingpress_get_settings('stripe_payment_method', 'payment_setting');
            $bookingpress_stripe_payment_method = !empty($bookingpress_stripe_payment_method) ? $bookingpress_stripe_payment_method : 'sca_popup';

            if($bookingpress_stripe_payment_method == "sca_popup"){
                wp_register_script( 'bookingpress_pro_stripe_sca_js', 'https://js.stripe.com/v3/', array(), BOOKINGPRESS_PRO_VERSION );
                wp_enqueue_script('bookingpress_pro_stripe_sca_js');
            }
        }
       
        function bookingpress_add_frontend_payment_gateway(){
	    global $BookingPress;

            $bookingpress_is_gateway_enable = $BookingPress->bookingpress_get_settings('stripe_payment', 'payment_setting');
            if($bookingpress_is_gateway_enable == 'true'){
            ?>
                <div class="bpa-front-module--pm-body__item" :class="(appointment_step_form_data.selected_payment_method == 'stripe') ? '__bpa-is-selected' : ''" @click="select_payment_method('stripe')" v-if="stripe_payment != 'false' && stripe_payment != ''">
                    <svg class="bpa-front-pm-pay-local-icon" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm-1 14H5c-.55 0-1-.45-1-1v-5h16v5c0 .55-.45 1-1 1zm1-10H4V6h16v2z"/></svg>
					<p>{{stripe_text}}</p>
					<div class="bpa-front-si-card--checkmark-icon" v-if="appointment_step_form_data.selected_payment_method == 'stripe'">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zM9.29 16.29 5.7 12.7c-.39-.39-.39-1.02 0-1.41.39-.39 1.02-.39 1.41 0L10 14.17l6.88-6.88c.39-.39 1.02-.39 1.41 0 .39.39.39 1.02 0 1.41l-7.59 7.59c-.38.39-1.02.39-1.41 0z"/></svg>
					</div>
				</div>
            <?php
	    }
        }

        function bookingpress_frontend_data_fields_for_stripe($bookingpress_front_vue_data_fields){
            global $BookingPress;
            $bookingpress_stripe_payment_method = $BookingPress->bookingpress_get_settings('stripe_payment_method', 'payment_setting');
            $bookingpress_stripe_payment_method = !empty($bookingpress_stripe_payment_method) ? $bookingpress_stripe_payment_method : 'sca_popup';

			$bookingpress_front_vue_data_fields['stripe_payment'] = $this->is_addon_activated();
            $bookingpress_front_vue_data_fields['stripe_payment_method'] = $bookingpress_stripe_payment_method;            
            $bookingpress_is_gateway_enable = $BookingPress->bookingpress_get_settings('stripe_payment', 'payment_setting');
            if($bookingpress_is_gateway_enable == 'true'){
                $bookingpress_front_vue_data_fields['is_only_onsite_enabled'] = 0;
                $bookingpress_front_vue_data_fields['bookingpress_activate_payment_gateway_counter'] = $bookingpress_front_vue_data_fields['bookingpress_activate_payment_gateway_counter'] + 1;
            }
            $bookingpress_front_vue_data_fields['stripe_text'] = $BookingPress->bookingpress_get_customize_settings('stripe_text', 'booking_form');

			return $bookingpress_front_vue_data_fields;
        }

        function bookingpress_add_validation_code_before_book_appointment_func($posted_data){
            global $wpdb, $BookingPress;
            if(!empty($posted_data) && !empty($posted_data['appointment_data']['selected_payment_method']) && ($posted_data['appointment_data']['selected_payment_method'] == "stripe") ){
                $bookingpress_stripe_payment_method = $BookingPress->bookingpress_get_settings('stripe_payment_method', 'payment_setting');
                $bookingpress_stripe_payment_method = !empty($bookingpress_stripe_payment_method) ? $bookingpress_stripe_payment_method : 'sca_popup';

                $bookingpress_validation_msg = $BookingPress->bookingpress_get_settings('bookingpress_card_details_error_msg','message_setting');
                $bookingpress_validation_msg = !empty($bookingpress_validation_msg) ? stripslashes_deep($bookingpress_validation_msg) : __('Please fill all fields value of card details', 'bookingpress-stripe');

                
                if($bookingpress_stripe_payment_method == "built_in_form_fields" && $posted_data['appointment_data']['total_payable_amount'] > 0){
                    $bookingpress_card_number = !empty($posted_data['appointment_data']['card_number']) ? $posted_data['appointment_data']['card_number'] : '';
                    $bookingpress_expire_month = !empty($posted_data['appointment_data']['expire_month']) ? $posted_data['appointment_data']['expire_month'] : '';
                    $bookingpress_expire_year = !empty($posted_data['appointment_data']['expire_year']) ? $posted_data['appointment_data']['expire_year'] : '';
                    $bookingpress_expire_cvv = !empty($posted_data['appointment_data']['cvv']) ? $posted_data['appointment_data']['cvv'] : '';

                    if(empty($bookingpress_card_number) || empty($bookingpress_expire_month) || empty($bookingpress_expire_year) || empty($bookingpress_expire_cvv)){
                        $response['variant'] = 'error';
                        $response['title']   = esc_html__( 'Error', 'bookingpress-stripe' );
                        $response['msg'] = $bookingpress_validation_msg;
                        echo json_encode( $response );
                        exit();
                    }
                }
            }
        }

        function bookingpress_after_selecting_payment_method_func($bookingpress_after_selecting_payment_method_data){
            $bookingpress_after_selecting_payment_method_data .= '
            if(vm.appointment_step_form_data.selected_payment_method == "stripe" && vm.stripe_payment_method == "built_in_form_fields"){
                bookingpress_allowed_payment_gateways_for_card_fields.push("stripe");
            }';
            return $bookingpress_after_selecting_payment_method_data;
		}
      
        function bookingpress_currency_support_func($notAllow, $bookingpress_currency){            
            $bookingpress_stripe_currency = $this->bookingpress_stripe_supported_currency_list(); 
            if (!in_array($bookingpress_currency, $bookingpress_stripe_currency)) {
                $notAllow[] = 'stripe';
            }
            return $notAllow;
        }

        function bookingpress_pro_validate_currency_before_book_appointment_func($bookingpress_is_support,$bookingpress_selected_payment_method,$bookingpress_currency_name){
            $bookingpress_stripe_currency = $this->bookingpress_stripe_supported_currency_list(); 
            if ($bookingpress_selected_payment_method == 'stripe' && !in_array($bookingpress_currency_name,$bookingpress_stripe_currency ) ) {
                $bookingpress_is_support = 0;
            }
            return $bookingpress_is_support;
        }
        
        function bookingpress_stripe_supported_currency_list() {            
            /* 135 currency */
            $bookingpress_currency_list = array(
                'USD','AED','AFN','ALL','AMD','ANG','AOA','ARS','AUD','AWG','AZN','BAM','BBD','BDT','BGN','BIF','BMD','BND','BOB','BRL','BSD','BWP','BYN','BZD','CAD','CDF','CHF','CLP','CNY','COP','CRC','CVE','CZK','DJF','DKK','DOP','DZD','EGP','ETB','EUR','FJD','FKP','GBP','GEL','GIP','GMD','GNF','GTQ','GYD','HKD','HNL','HRK','HTG','HUF','IDR','ILS','INR','ISK','JMD','JPY','KES','KGS','KHR','KMF','KRW','KYD','KZT','LAK','LBP','LKR','LRD','LSL','MAD','MDL','MGA','MKD','MMK','MNT','MOP','MRO','MUR','MVR','MWK','MXN','MYR','MZN','NAD','NGN','NIO','NOK','NPR','NZD','PAB','PEN','PGK','PHP','PKR','PLN','PYG','QAR','RON','RSD','RUB','RWF','SAR','SBD','SCR','SEK','SGD','SHP','SLL','SOS','SRD','STD','SZL','THB','TJS','TOP','TRY','TTD','TWD','TZS','UAH','UGX','UYU','UZS','VND','VUV','WST','XAF','XCD','XOF','XPF','YER','ZAR','ZMW');
            return $bookingpress_currency_list;
        }
       
    }

    global $bookingpress_stripe;
	$bookingpress_stripe = new bookingpress_stripe;
}