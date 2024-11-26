<?php 

if (!class_exists('bookingpress_recurring_appointments') && class_exists('BookingPress_Core')) {

    class bookingpress_recurring_appointments Extends BookingPress_Core {

        var $max_no_of_recurring = 60;

        function __construct(){

            global $bookingpress_pro_version,$default_recurring_appointments_max_no_of_times,$is_cart_addon_active,$all_recurring_frequencies, $bookingpress_uniq_id_recurring_thankyou,$bookingpress_max_no_of_recurring;
            $bookingpress_pro_version = get_option( 'bookingpress_pro_version');
            $bookingpress_pro_version = (!empty($bookingpress_pro_version))?$bookingpress_pro_version:0;

            $bookingpress_max_no_of_recurring = $this->max_no_of_recurring;

            $default_recurring_appointments_max_no_of_times = 0;
            $all_recurring_frequencies = $this->bookingpress_get_recurring_frequencies();
            $is_cart_addon_active = $this->is_cart_addon_active();

            register_activation_hook(RECURRING_APPOINTMENTS_LIST_DIR.'/bookingpress-recurring-appointments.php', array('bookingpress_recurring_appointments', 'install'));
            register_uninstall_hook(RECURRING_APPOINTMENTS_LIST_DIR.'/bookingpress-recurring-appointments.php', array('bookingpress_recurring_appointments', 'uninstall'));              
            
            $recurring_appointment_working = $this->bookingpress_check_recurring_addon_requirement();            
            
            add_action('admin_notices', array($this, 'bookingpress_admin_notices'));
            if(is_plugin_active('bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php') && version_compare($bookingpress_pro_version, '3.0.1', '>=') && $recurring_appointment_working) {

                /* Add a payment settings */          
                add_filter('bookingpress_add_setting_dynamic_data_fields',array($this,'bookingpress_add_setting_dynamic_data_fields_func'),15);

                /* Add Service New Fields Settings Start */
                add_action('bookingpress_add_service_field_outside',array($this,'bookingpress_add_service_field_outside_fun'));
                add_filter( 'bookingpress_modify_service_data_fields', array( $this, 'bookingpress_modify_service_data_fields_func' ),15); 
                add_action( 'bookingpress_add_service_dynamic_vue_methods', array( $this, 'bookingpress_add_service_dynamic_vue_methods_func' ), 10 );
                add_filter('bookingpress_modify_edit_service_data', array($this, 'bookingpress_modify_edit_service_data_func'), 10, 2);	
                add_action( 'bookingpress_edit_service_more_vue_data', array( $this, 'bookingpress_edit_service_more_vue_data_func' ), 10 );
                add_filter( 'bookingpress_after_add_update_service', array( $this, 'bookingpress_save_service_details' ), 10, 3 ); 
                add_action( 'bookingpress_after_open_add_service_model', array( $this, 'bookingpress_after_open_add_service_model_func' ), 10 );
                /* Add Service New Fields Settings Over */

                /* Add a customization form settings Start */
                add_filter('bookingpress_customize_add_dynamic_data_fields',array($this,'bookingpress_customize_add_dynamic_data_fields_func'),10);
                add_action('bookingpress_add_bookingform_label_data',array($this,'bookingpress_add_bookingform_label_data_func'));            
                add_filter('bookingpress_get_booking_form_customize_data_filter',array($this, 'bookingpress_get_booking_form_customize_data_filter_func'),10,1);
                add_filter('bookingpress_before_save_customize_booking_form',array($this, 'bookingpress_before_save_customize_booking_form_func'));
                add_action('bookingpress_before_save_customize_form_settings',array($this,'bookingpress_before_save_customize_form_settings_func'));             
                /* Add a customization form settings Over */

                /* Added Recurring Tab Front Side Configuration */
                add_filter('bookingpress_frontend_apointment_form_add_dynamic_data', array($this, 'bookingpress_modify_front_booking_form_data_vars_func'), 15, 1);
                add_filter( 'bookingpress_before_selecting_booking_service', array( $this, 'bookingpress_before_selecing_booking_service_for_recurring'), 10 );
                
                add_action('bookingpress_add_service_extra_drawer',array($this,'bookingpress_add_service_extra_drawer_func'),10);

                /* Add Recurring Appointment HTML in Front Side */
                add_action('bookingpress_add_dateandtime_detail_section_before_front_side',array( $this,'bookingpress_add_recurring_appointment_front_side_func' ), 11);
                add_action('bookingpress_add_dateandtime_detail_section_before_front_side_mobile_view',array( $this,'bookingpress_add_dateandtime_detail_section_before_front_side_mobile_view_func' ), 11);

                /* BookingPress get recurring appointment list */
                add_action("wp_ajax_bookingpress_get_recurring_appointments", array($this,'bookingpress_get_recurring_appointments_func'));
                add_action("wp_ajax_nopriv_bookingpress_get_recurring_appointments", array($this,'bookingpress_get_recurring_appointments_func'));            

                /* Load CSS & JS in Front Side */
                add_action('wp_head', array( $this, 'set_front_css' ),11 );
                /* Add Adimin CSS file. */
                add_action( 'admin_enqueue_scripts', array( $this, 'set_admin_css' ), 12 ); 

                /* Front Side Vue Method Added */
                add_filter('bookingpress_add_pro_booking_form_methods', array($this, 'bookingpress_add_pro_booking_form_methods_func'), 15, 1);
                add_filter('bookingpress_dynamic_next_page_request_filter', array($this, 'bookingpress_dynamic_next_page_request_filter_func'), 10, 1);

                /* Add Front Side disable date filter added */
                add_filter('bookingpress_disable_date_vue_data_modify',array($this,'bookingpress_disable_date_vue_data_modify_func'),15,1);
                add_filter('bookingpress_disable_multiple_days_event_xhr_resp_after', array( $this, 'bookingpress_disable_multiple_days_event_xhr_resp_after_func'),15,1 );
                add_filter('bookingpress_after_selecting_booking_service',array($this,'bookingpress_after_selecting_booking_service_func'),15,1);
                add_filter( 'bookingpress_dynamic_validation_for_step_change', array( $this, 'bookingpress_dynamic_validation_for_step_change_recurring_appointment'));

                /* Multi-Language Translation */
                if(is_plugin_active('bookingpress-recurring-appointments/bookingpress-recurring-appointments.php')){
                    add_filter('bookingpress_modified_language_translate_fields_section',array($this,'bookingpress_modified_language_translate_fields_section_func'),5,1);
                    add_filter('bookingpress_modified_language_translate_fields',array($this,'bookingpress_modified_language_translate_fields_func'),10);
                    add_filter('bookingpress_modified_customize_form_language_translate_fields',array($this,'bookingpress_modified_language_translate_fields_customize_func'),10);                
                }

                add_filter( 'bookingpress_modify_booked_appointment_data', array( $this, 'bookingpress_modify_timeslot_for_recurring' ), 10, 2 );
                add_filter('bookingpress_modify_happy_hours_offer_message',array($this,'bookingpress_modify_happy_hours_offer_message_func'),10,1);

                /* Final Step Amount Change  */
                add_filter( 'bookingpress_sub_total_amount_payable_modify_outside', array( $this, 'bookingpress_sub_total_amount_payable_modify_outside_func'));
                add_filter( 'bookingpress_calculate_total_after_apply_diposit_outside', array( $this, 'bookingpress_calculate_total_after_apply_diposit_outside_func'));

                add_action('bookingpress_add_summary_total_block',array($this,'bookingpress_add_summary_total_block_func'),10);
                /* Desktop View Appointment list for recurring appointment */
                add_action('bookingpress_booking_form_summary_appointment_list',array($this,'bookingpress_booking_form_summary_appointment_list_func'),10,1);


                /* Add Front Booking Validation for recurring appointments */
                if(version_compare($bookingpress_pro_version, '3.2', '>')){
                    add_filter('bookingpress_validate_only_booking_form',array($this,'bookingpress_validate_only_booking_form_func'),10,2);                    
                }else{
                    add_action('bookingpress_validate_booking_form',array($this,'bookingpress_front_booking_recurring_validate_booking_form_func'));
                }                                                

                /* BookingPress Add Single Appointment Data */
                add_filter('bookingpress_add_single_appointment_data',array($this,'bookingpress_add_single_appointment_data_func'),10,2);

                /* BookingPress Add recurring entry data here... */
                add_filter('bookingpress_modify_appointment_return_data', array($this, 'bookingpress_add_recurring_entries_data_func'), 10, 3);

                /* After edit appointment set timeslot data */
                add_action("wp_ajax_bookingpress_after_edit_recurring_appointments", array($this,'bookingpress_after_edit_recurring_appointments_func'));
                add_action("wp_ajax_nopriv_bookingpress_after_edit_recurring_appointments", array($this,'bookingpress_after_edit_recurring_appointments_func')); 

                /*Customize color for the suggested and blocked timeslots*/
                add_action('bookingpress_customize_color_setting_after',array($this,'bookingpress_customize_recurring_color_setting_after_fun'), 11);
                
                /* Add hook for reset color option. */
                add_action('bookingpress_reset_color_option_after',array($this,'bookingpress_reset_color_option_after_fun'),10);

                /* Apply Customize CSS */
                add_filter('bookingpress_generate_booking_form_customize_css',array($this,'bookingpress_recurring_customize_css_content_modify_fun'),12,2);

                /* Add recurring data in bookingpress booking table */
                add_filter('bookingpress_modify_appointment_booking_fields_before_insert',array($this,'bookingpress_modify_appointment_booking_fields_before_insert_func'),11,2);

                /* Add recurring data in payment table */
                add_filter('bookingpress_modify_payment_log_fields_before_insert', array($this, 'bookingpress_modify_payment_log_fields_before_insert_func'), 12, 2);

                /* Disable Happy Hours Data In Time Slot */
                add_filter('bookingpress_disable_happy_hours_data_for_timeslot',array($this,'bookingpress_disable_happy_hours_data_for_timeslot_func'),10,2);

                /* backend function start for add recurring icon */
                add_action('bookingpress_backend_appointment_list_type_icons',array($this,'bookingpress_backend_appointment_list_type_icons_func'));
                add_filter('bookingpress_appointment_add_view_field', array($this, 'bookingpress_appointment_add_view_field_func'), 10, 2);
                add_filter('bookingpress_payment_add_view_field', array($this, 'bookingpress_appointment_add_view_field_func'), 10, 2);            
                add_action('bookingpress_backend_payment_list_type_icons',array($this,'bookingpress_backend_payment_list_type_icons_func'));
                /* backend function for add recurring placeholders */
                add_action( 'bookingpress_notification_external_message_plachoders', array( $this, 'bookingpress_add_recurring_appointment_placeholder_list'));
                            
                /* Add Recurring Dynamic Notification Data */            
                add_filter( 'bookingpress_add_dynamic_notification_data_fields', array( $this, 'bookingpress_add_recurring_dynamic_notification_data_fields' ) );            
                add_filter('bookingpress_multiple_appointment_payment_order_detail',array($this,'bookingpress_multiple_appointment_payment_order_detail_func'),10,2);

                /* Before Add To Cart Addon */
                add_filter('bookingpress_before_add_to_cart_item', array($this, 'bookingpress_before_add_to_cart_item_func'), 10, 1); 
                add_filter('bookingpress_modified_coupon_total_payable_amount',array($this,'bookingpress_modified_coupon_total_payable_amount_func'),10,2);    

                /* Add Functionality For Backend Recurring Appointment Add */
                add_filter('bookingpress_modify_appointment_data_fields',array($this,'bookingpress_modify_backend_appointment_data_fields_func'),15);            
                add_filter( 'bookingpress_modify_calendar_data_fields', array( $this, 'bookingpress_modify_backend_appointment_data_fields_func' ),15);
                add_filter( 'bookingpress_modify_dashboard_data_fields', array( $this, 'bookingpress_modify_backend_appointment_data_fields_func' ),15 );

                //add_action('bookingpress_add_appointment_field_section',array($this,'bookingpress_add_appointment_field_section_func'),10);

                add_action('bookingpress_change_backend_service',array($this,'bookingpress_change_backend_service_func'));
                /* action for backend after select time call... */
                add_action('bookingpress_admin_add_appointment_after_select_timeslot',array($this,'bookingpress_admin_add_appointment_after_select_timeslot_fun'),10);
                /* Backend add appointment HTML */
                add_action('bookingpress_add_appointment_new_row_section',array($this,'bookingpress_add_appointment_new_row_section_func'),10);

                /* Backend date ajax data pass */
                add_action('bookingpress_set_additional_appointment_xhr_data',array($this,'bookingpress_set_additional_appointment_xhr_data_func'),20);
                add_action('bookingpress_additional_disable_dates',array($this,'bookingpress_additional_disable_dates_func'),20);
                //add_action('bookingpress_get_front_timing_set_additional_appointment_xhr_data',array($this,'bookingpress_get_front_timing_set_additional_appointment_xhr_data_func'),20);
                add_action('bookingpress_backend_after_get_timeslot_response',array($this,'bookingpress_backend_after_get_timeslot_response_func'),20);
                /* Apply recurring appointment price in backend */            
                add_action('bookingpress_admin_calculate_subtotal_price',array($this,'bookingpress_admin_calculate_total_after_add_recurring_appointment_price_fun'),20);

                /* Backend admin vue method added */
                //add_action( 'bookingpress_appointment_add_dynamic_vue_methods', array( $this, 'bookingpress_appointment_add_dynamic_vue_methods_func' ), 15 );
                add_action('bookingpress_admin_panel_vue_methods', array($this, 'bookingpress_appointment_add_dynamic_vue_methods_func'), 10);
                
                
                /* Send list of recurring appointment in email notification */
                add_filter( 'bookingpress_modify_email_content_filter', array( $this, 'bookingpress_recurring_modify_email_content_filter_func' ), 10, 2 );

                add_filter( 'bookingpress_modify_payments_listing_data', array( $this, 'bookingpress_modify_payments_listing_data' ), 15, 1 );

                /* Modify Datetime for the datetime shortcpde*/
                add_filter( 'bookingpress_modify_datetime_shortcode_data', array( $this, 'bookingpress_modify_recurring_datetime_shortcode_data' ), 11, 2 );
                /* Modify Datetime for the service shortcpde*/
                add_filter('bookingpress_modify_service_shortcode_details', array($this, 'bookingpress_modify_recurring_service_shortcode_details'), 11, 2);
                /* Modify Datetime for the customer details shortcpde*/
                add_filter('bookingpress_modify_customer_details_shortcode_data', array($this, 'bookingpress_modify_rec_customer_details_shortcode_data_func'), 10, 2);
                
                /* Add Backend Recurring Appointment Data */
                add_filter( 'bookingpress_add_backend_recurring_appointment', array( $this, 'bookingpress_add_backend_recurring_appointment_func' ), 15, 3);

                /* BookingPress Add appointment clear data */
                add_action('bookingpress_add_appointment_model_reset',array($this,'bookingpress_add_appointment_model_reset_func'),11);            
                add_action('bookingpress_calendar_add_appointment_model_reset', array( $this, 'bookingpress_add_appointment_model_reset_func' ),11);
                
                /* BookingPress Add MyBooking Icons */                            
                add_filter('bookingpress_modify_my_appointments_data_externally',array($this,'bookingpress_modify_my_appointments_data_for_recurring'),20,1);

                /* Add Recurring icon in my booking */
                add_action('bookingpress_my_booking_extra_icons',array($this,'bookingpress_my_booking_extra_icons_func'),10);

                /* Disable refund in recurring appointment */
                add_filter('bookingpress_is_not_allow_to_refund_check',array($this,'bookingpress_is_not_allow_to_refund_check_func'),10,2);

                /* Start Date not change when waiting list active */
                add_filter( 'bookingpress_add_single_disable_date_when_no_timeslot', array( $this, 'bookingpress_add_single_disable_date_when_no_timeslot_fun'),15,3);
                add_filter( 'bookingpress_allow_to_disable_booked_date', array( $this, 'bookingpress_allow_to_disable_booked_date_fun'),10,2);

                /* Complete Payment Calculate Due Amount add hook */
                add_filter('bookingpress_check_is_group_order_for_complete_payment',array($this,'bookingpress_check_is_group_order_for_complete_payment_func'),10,2);
                
                /* BookingPress Complete Payment Daynamic Data */
                add_filter('modify_complate_payment_data_after_entry_create', array($this, 'modify_complate_payment_data_after_entry_create_func'), 15, 2);            
                add_action('bookingpress_recurring_appointment_complete_payment_summary',array($this,'bookingpress_recurring_appointment_complete_payment_summary_func'),10,1);

                add_filter('bookingpress_check_is_group_order_for_complete_payment_update',array($this,'bookingpress_check_is_group_order_for_complete_payment_update_func'),10,3);

                /*Backend get list of recurring <appointments></appointments*/
                add_action("wp_ajax_bookingpress_get_recurring_appointment_list", array($this,'bookingpress_get_recurring_appointment_list_func'));
                
                /* BookingPress Date & Time popover add for thankyou page */
                add_filter("bookingpress_modify_datetime_shortcode_content", array($this,'bookingpress_modify_datetime_shortcode_content_func'), 10, 2);
                /* function to set common messages for the recurring appointment  */
                add_action("bookingpress_add_setting_msg_outside", array($this,'bookingpress_add_setting_msg_outside'));

                add_action('bookingpress_appointment_full_row_clickable',array($this,'bookingpress_appointment_full_row_clickable_func'),10);

                /* Add filter for backend complete payment URL send */  
                add_action('bookingpress_after_add_group_appointment', array($this, 'bookingpress_send_complete_payment_url_notification'), 11, 3);

                /* Add New Invoice Filter */
                add_action('bookingpress_before_open_invoice_preview',array($this,'bookingpress_before_open_invoice_preview_func'),10);
                add_filter("bookingpress_change_recurring_content_for_invoice", array($this,'bookingpress_change_recurring_content_for_invoice_func'), 10, 3);

                /* Have added hook for send aweber & mailchamp data */
                add_filter('bookingpress_check_waiting_after_front_book_for_integration', array($this, 'bookingpress_check_waiting_after_front_book_for_integration_func'), 20, 2);

                /* Calendar Recurring Appointment List */
                add_action( 'bookingpress_calendar_integration_events', array( $this, 'bookingpress_calendar_integration_urls_for_recurring'),10);
                add_action( 'init', array( $this, 'bookingpress_generate_ics_with_cart_items') );
                
                /* Add a filter for send single notification after book appointment */    
                add_filter('bookingpress_send_single_whatsapp_notification_after_booking', array($this, 'bookingpress_send_single_whatsapp_notification_after_booking_func'), 12, 2);
                add_filter('bookingpress_send_single_sms_notification_after_booking', array($this, 'bookingpress_send_single_sms_notification_after_booking_func'), 12, 2);

                /* Add a filter for send recurring notification in whatsapp */
                add_filter('bookingpress_check_group_order_for_whatsapp', array($this, 'bookingpress_check_group_order_for_whatsapp_func'), 12, 2);            
                
                add_filter('bookingpress_check_group_order_for_thankyou_datetime', array($this, 'bookingpress_check_group_order_for_thankyou_datetime_func'), 12, 2);


                add_filter('bookingpress_modify_check_duplidate_appointment_time_slot',array($this,'bookingpress_modify_check_duplidate_appointment_time_slot_func'),15,2);

                add_action('bookingpress_manage_appointment_view_bottom',array($this,'bookingpress_manage_appointment_view_bottom_func'),10);
                add_action('bookingpress_manage_dashboard_view_bottom',array($this,'bookingpress_manage_appointment_view_bottom_func'),10);
                add_action('bookingpress_manage_calendar_view_bottom',array($this,'bookingpress_manage_appointment_view_bottom_func'),10);

                /* Delete Payment record after delete all recurring appointment */
                add_action('bookingpress_before_delete_appointment',array($this,'bookingpress_before_delete_appointment_func'),12);

                add_filter('bookingpress_disable_date_send_data_before',array($this,'bookingpress_disable_date_send_data_before_func'),15);

                /* Function for send single email notification */
                add_filter('bookingpress_send_only_first_appointment_notification',array($this,'bookingpress_send_only_first_appointment_notification_func'),10,3);
            }
	    
	    		add_action('admin_init', array( $this, 'bookingpress_update_recurring_appointment_data') );
			
			add_action('activated_plugin',array($this,'bookingpress_is_recurring_appointments_addon_activated'),11,2);
        }
	
		/**
		 * bpa function for get recurring appointment list
		 *
		 * @param  mixed $user_detail
		 * @return void
		*/
		function bookingpress_bpa_get_recurring_appointments_func($user_detail=array()){
			
			global $BookingPress,$wpdb,$BookingPressPro,$bookingpress_pro_appointment_bookings;
			$result = array();						
			//$result["customer_form_fields"] = array();
			$response = array('status' => 0, 'message' => '', 'response' => array('result' => $result));

			if(class_exists('BookingPressPro') && method_exists( $BookingPressPro, 'bookingpress_bpa_check_valid_connection_callback_func') && $BookingPressPro->bookingpress_bpa_check_valid_connection_callback_func()){
				
				$user_detail = ! empty($user_detail) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $user_detail) : array();	
				$user_id = isset($user_detail['user_id']) ? intval($user_detail['user_id']) : '';
                $recurring_detail = isset($user_detail['recurring_detail']) ? $user_detail['recurring_detail'] : '';  
                $enable_recurring_appointments = isset($user_detail['enable_recurring_appointments']) ? $user_detail['enable_recurring_appointments'] : '';	              
                $bookingpress_pro_appointment_bookings->bookingpress_bpa_set_disable_date_data_func($user_detail);
                $bookingpress_get_recurring_appointments_response = array();	
                if(!empty($recurring_detail)){

                    $_REQUEST['recurring_form_data'] = (is_array($recurring_detail))?json_encode($recurring_detail,true):'';
                    $_POST = $_REQUEST;

                    $bookingpress_get_recurring_appointments_response = $this->bookingpress_get_recurring_appointments_func(true);
                    $bookingpress_check_response = (isset($bookingpress_get_recurring_appointments_response['variant']))?$bookingpress_get_recurring_appointments_response['variant']:'';
                    if($bookingpress_check_response == 'error'){					
                        $message = (isset($bookingpress_get_recurring_appointments_response['msg']))?$bookingpress_get_recurring_appointments_response['msg']:'';
                        $response = array('status' => 0, 'message' => $message, 'response' => array('result' => $result));					
                    }else{
                        $result = $bookingpress_get_recurring_appointments_response;
                        $response = array('status' => 1, 'message' => '', 'response' => array('result' => $result));					
                    }

                }

			}

			return $response;
		}     		

        /**
         * Function for send only first appointment notification
         *
         * @param  mixed $bookingpress_send_only_first_appointment_notification
         * @param  mixed $appointment_id
         * @param  mixed $payment_log_data
         * @return void
         */
        function bookingpress_send_only_first_appointment_notification_func($bookingpress_send_only_first_appointment_notification,$appointment_id,$payment_log_data){
            global $BookingPress;
            if(isset($payment_log_data['bookingpress_is_recurring'])){
                if($payment_log_data['bookingpress_is_recurring'] == '1'){
                    $bookingpress_send_only_first_appointment_notification = 'yes';
                }
            }            
            return $bookingpress_send_only_first_appointment_notification;
        }

        /**
         * Function for update recurring addon
         *
         * @return void
        */
        function bookingpress_update_recurring_appointment_data(){
            global $BookingPress, $recurring_appointments_list_version;
            $bookingpress_db_recurring_appointments_version = get_option('recurring_appointments_list_version', true);
            if( version_compare( $bookingpress_db_recurring_appointments_version, '1.4', '<' ) ){
                $bookingpress_load_recurring_appointments_update_file = RECURRING_APPOINTMENTS_LIST_DIR . '/core/views/upgrade_latest_recurring_data.php';
                include $bookingpress_load_recurring_appointments_update_file;
                $BookingPress->bookingpress_send_anonymous_data_cron();             
            }
        }

          
		function bookingpress_is_recurring_appointments_addon_activated($plugin,$network_activation)
        {  
            $myaddon_name = "bookingpress-recurring-appointments/bookingpress-recurring-appointments.php";

            if($plugin == $myaddon_name)
            {

                if(!(is_plugin_active('bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php')))
                {
                    deactivate_plugins($myaddon_name, FALSE);
                    $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name);
                    $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Recurring Appointments Add-on', 'bookingpress-recurring-appointments');
					$bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-recurring-appointments'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
					wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                    die;
                }

                $license = trim( get_option( 'bkp_license_key' ) );
                $package = trim( get_option( 'bkp_license_package' ) );

                if( '' === $license || false === $license ) 
                {
                    deactivate_plugins($myaddon_name, FALSE);
                    $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name);
                    $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Recurring Appointments Add-on', 'bookingpress-recurring-appointments');
					$bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-recurring-appointments'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
					wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                    die;
                }
                else
                {
                    $store_url = BOOKINGPRESS_RECURRING_APPOINTMENTS_STORE_URL;
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
                            $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Recurring Appointments Add-on', 'bookingpress-recurring-appointments');
                            $bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-recurring-appointments'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
                            wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                            die;
                        }

                    }
                    else
                    {
                        deactivate_plugins($myaddon_name, FALSE);
                        $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name);
                        $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Recurring Appointments Add-on', 'bookingpress-recurring-appointments');
                        $bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-recurring-appointments'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
                        wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                        die;
                    }
                }
            }

        }

        /**
         * bookingpress_before_delete_appointment_func
         *
         * @param  mixed $appointment_id
         * @return void
         */
        function bookingpress_before_delete_appointment_func($appointment_id ) { 
            global $wpdb,$tbl_bookingpress_payment_logs,$tbl_bookingpress_appointment_bookings;
            $bookingperss_appointments_data = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_payment_id,bookingpress_is_recurring,bookingpress_order_id FROM {$tbl_bookingpress_appointment_bookings}  WHERE bookingpress_appointment_booking_id = %d",$appointment_id),ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
            if(!empty($bookingperss_appointments_data['bookingpress_is_recurring']) && $bookingperss_appointments_data['bookingpress_is_recurring'] == 1) {
                $bookingpress_order_id = !empty($bookingperss_appointments_data['bookingpress_order_id']) ? intval($bookingperss_appointments_data['bookingpress_order_id']) : 0;
				$bookingpress_payment_id = !empty($bookingperss_appointments_data['bookingpress_order_id']) ? intval($bookingperss_appointments_data['bookingpress_payment_id']) : 0;
                $bookingperss_cart_appointemnt_data = $wpdb->get_var($wpdb->prepare("SELECT bookingpress_appointment_booking_id FROM {$tbl_bookingpress_appointment_bookings}  WHERE bookingpress_order_id = %d AND bookingpress_appointment_booking_id != %d ",$bookingpress_order_id,$appointment_id)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
                if($bookingperss_cart_appointemnt_data == 0) {
					$wpdb->delete($tbl_bookingpress_payment_logs, array( 'bookingpress_payment_log_id' => $bookingpress_payment_id ), array( '%d' ));
                }
            }
        }
                
        /**
         * Function for remove old disable date when edit recurring appointment get
         *
         * @return void
         */
        function bookingpress_disable_date_send_data_before_func($bookingpress_disable_date_send_data){
            $bookingpress_disable_date_send_data.='                
                if(vm.recurring_open_edit_popup == true || (vm.appointment_step_form_data.recurring_edit_index != "" || vm.recurring_appointment_device == "mobile")){
                    if(typeof postData.disabled_dates !== "undefined"){	
                        postData.disabled_dates = [];                                               
                    }
                }                                                
            ';
            return $bookingpress_disable_date_send_data;
        }

        /**
         * Function for add recurring appointments list popup in backend
         *
         * @return void
         */
        function bookingpress_manage_appointment_view_bottom_func(){
        ?>
            <el-dialog custom-class="bpa-dialog bpa-dialog--rec-list" id="recurring_appointment_data" title="" :visible.sync="bookingpress_is_recurring_appointment_list_model" :modal="is_mask_display" @open="bookingpress_enable_modal" @close="bookingpress_disable_modal" lock-scroll="false" top="0">
                <div class="bpa-dialog-heading">
                    <el-row type="flex">
                        <el-col :xs="12" :sm="12" :md="8" :lg="8" :xl="8">
                            <div><span class="bpa-rec-transaction-heading"><?php esc_html_e( 'Recurring Transaction', 'bookingpress-recurring-appointments' ); ?></span></div>
                        </el-col>
                        <el-col v-if="bookingpress_recurring_appointment_list_popup_loader == false" :xs="12" :sm="12" :md="16" :lg="16" :xl="16" class="bpa-dh__btn-group-col">
                            <div class="rec-list-appointment-title-container">
                                <div class="rec-list-appointment-title-head-id">
                                    <span class="rec-list-appointment-title-head"><?php esc_html_e( 'ID: ', 'bookingpress-recurring-appointments' ); ?></span>
                                    <span class="rec-list-appointment-title">#{{bookingpress_recurring_appointment_list_popup.booking_id}}</span>
                                </div>
                                <div class="rec-list-appointment-title-head">
                                    <span class="rec-list-appointment-title-head"><?php esc_html_e( 'Service: ', 'bookingpress-recurring-appointments' ); ?></span>
                                    <span class="rec-list-appointment-title">{{bookingpress_recurring_appointment_list_popup.service_name}}</span>
                                </div>
                            </div>
                        </el-col>
                    </el-row>
                </div>                
                <div class="bpa-dialog-body bpa-grid-list-rec-appint-container">
		            <el-container class="bpa-grid-list-container">
                        <div v-if="bookingpress_recurring_appointment_list_popup_loader" class="bpa-recurring-appointment-listing-loader">
                            <div class="bpa-back-loader-container">
                                <div class="bpa-back-loader"></div>
                            </div>                                       
                        </div>                         
                        <div v-if="bookingpress_recurring_appointment_list_popup_loader == false" class="bpa-form-row">	
                           <template>
                                <el-row class="recurring_appointment_data-row" :gutter="20" v-for="(rec_data,rec_ind) in bookingpress_recurring_appointment_list_popup.rec_appointment_data">                                    
                                    <el-col><div class="recurring_appointment_data-col"><b v-html="bookingpress_recurring_appointment_count(rec_ind)"></b> {{rec_data.date}}, {{rec_data.time}}</div></el-col>                                                                                                            
                                </el-row>
                            </template> 
                        </div>
                    </el-container> 
                </div>
                <div class="bpa-dialog-footer">
                    <div class="bpa-hw-right-btn-group">
			            <el-button class="bpa-btn bpa-btn__medium" @click="close_rec_list_appointment_model" ><?php esc_html_e( 'Close', 'bookingpress-recurring-appointments' ); ?></el-button>
                    </div>
                </div>
            </el-dialog>        
        <?php 
        }

        /**
         * Function for thankyou page recurring datetime
         *
         * @param  mixed $is_group_datetime
         * @param  mixed $appointment_data
         * @return void
         */
        function bookingpress_check_group_order_for_thankyou_datetime_func($is_group_datetime,$appointment_data){            
            $is_group_datetime = isset($appointment_data[0]['bookingpress_is_recurring']) ? $appointment_data[0]['bookingpress_is_recurring'] : 0;
            return $is_group_datetime;
        }
                

        /**
         * Function for recurring requirement
         *
         * @return void
         */
        function bookingpress_check_recurring_addon_requirement(){
            global $bookingpress_pro_version;
            $recurring_working = true;
            if( version_compare( $bookingpress_pro_version, '3.0.1', '<' ) ){
                $recurring_working = false;
            }
            if(is_plugin_active('bookingpress-cart/bookingpress-cart.php')){
                $bookingpress_cart_version = get_option( 'bookingpress_cart_module' );
                if( version_compare( $bookingpress_cart_version, '2.5', '<' ) ){
                    $recurring_working = false;
                }
            }             
            if(is_plugin_active('bookingpress-tax/bookingpress-tax.php')){ 
                $bookingpress_tax_module = get_option('bookingpress_tax_module');
                if( version_compare( $bookingpress_tax_module, '1.5', '<' ) ){
                    $recurring_working = false;
                }   
            }
            if(is_plugin_active('bookingpress-waiting-list/bookingpress-waiting-list.php')){ 
                $bookingpress_waiting_list_version = get_option('bookingpress_waiting_list_version');
                if( version_compare( $bookingpress_waiting_list_version, '1.3', '<' ) ){
                    $recurring_working = false;
                }                 
            }
            if(is_plugin_active('bookingpress-whatsapp/bookingpress-whatsapp.php')){ 
                $bookingpress_whatsapp_gateway = get_option('bookingpress_whatsapp_gateway');
                if( version_compare( $bookingpress_whatsapp_gateway, '1.7', '<' ) ){
                    $recurring_working = false;
                }                
            } 
            if(is_plugin_active('bookingpress-sms/bookingpress-sms.php')){ 
                $bookingpress_sms_gateway = get_option('bookingpress_sms_gateway');
                if( version_compare( $bookingpress_sms_gateway, '1.8', '<' ) ){
                    $recurring_working = false;
                }                  
            }  
            if(is_plugin_active('bookingpress-happy-hours/bookingpress-happy-hours.php')){
                $happy_hours_version = get_option('happy_hours_version');
                if( version_compare( $happy_hours_version, '1.3', '<' ) ){
                    $recurring_working = false;
                }
            }
            if(is_plugin_active('bookingpress-invoice/bookingpress-invoice.php')){
                $bookingpress_invoice_version = get_option('bookingpress_invoice_version');
                if( version_compare( $bookingpress_invoice_version, '1.9', '<' ) ){
                    $recurring_working = false;
                }                
            }                       
            return $recurring_working;
        }

        /**
         * Function for display notice in admin side
         *
         * @return void
        */
        function bookingpress_admin_notices(){

            global $bookingpress_pro_version;
            if( version_compare( $bookingpress_pro_version, '3.0.1', '<' ) ){
                echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Recurring Appointments Plugin Requires to update the BookingPress Pro Plugin to version 3.0.1 or higher.", "bookingpress-recurring-appointments")."</p></div>";
            }
            if(is_plugin_active('bookingpress-cart/bookingpress-cart.php')){
                $bookingpress_cart_version = get_option( 'bookingpress_cart_module' );
                if( version_compare( $bookingpress_cart_version, '2.5', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Recurring Appointments Plugin Requires to update the BookingPress Cart Plugin to version 2.5 or higher.", "bookingpress-recurring-appointments")."</p></div>";
                }
            }  
            if(is_plugin_active('bookingpress-happy-hours/bookingpress-happy-hours.php')){
                $happy_hours_version = get_option('happy_hours_version');
                if( version_compare( $happy_hours_version, '1.3', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Recurring Appointments Plugin Requires to update the BookingPress - Happy Hours Pricing Plugin to version 1.3 or higher.", "bookingpress-recurring-appointments")."</p></div>";
                }
            }
            if(is_plugin_active('bookingpress-invoice/bookingpress-invoice.php')){
                $bookingpress_invoice_version = get_option('bookingpress_invoice_version');
                if( version_compare( $bookingpress_invoice_version, '1.9', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Recurring Appointments Plugin Requires to update the BookingPress - Invoice Plugin to version 1.9 or higher.", "bookingpress-recurring-appointments")."</p></div>";
                }                
            }            
            if(is_plugin_active('bookingpress-sms/bookingpress-sms.php')){ 
                $bookingpress_sms_gateway = get_option('bookingpress_sms_gateway');
                if( version_compare( $bookingpress_sms_gateway, '1.8', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Recurring Appointments Plugin Requires to update the BookingPress - SMS Notification Plugin to version 1.8 or higher.", "bookingpress-recurring-appointments")."</p></div>";
                }                  
            }
            if(is_plugin_active('bookingpress-tax/bookingpress-tax.php')){ 
                $bookingpress_tax_module = get_option('bookingpress_tax_module');
                if( version_compare( $bookingpress_tax_module, '1.5', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Recurring Appointments Plugin Requires to update the BookingPress - Tax Plugin to version 1.5 or higher.", "bookingpress-recurring-appointments")."</p></div>";
                }   
            }
            if(is_plugin_active('bookingpress-waiting-list/bookingpress-waiting-list.php')){ 
                $bookingpress_waiting_list_version = get_option('bookingpress_waiting_list_version');
                if( version_compare( $bookingpress_waiting_list_version, '1.3', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Recurring Appointments Plugin Requires to update the BookingPress - Waiting List Plugin to version 1.3 or higher.", "bookingpress-recurring-appointments")."</p></div>";
                }                 
            }
            if(is_plugin_active('bookingpress-whatsapp/bookingpress-whatsapp.php')){ 
                $bookingpress_whatsapp_gateway = get_option('bookingpress_whatsapp_gateway');
                if( version_compare( $bookingpress_whatsapp_gateway, '1.7', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Recurring Appointments Plugin Requires to update the BookingPress - WhatsApp Notification Plugin to version 1.7 or higher.", "bookingpress-recurring-appointments")."</p></div>";
                }                
            }
            if(is_plugin_active('bookingpress-multilanguage/bookingpress-multilanguage.php')){ 
                $bookingpress_multilanguage_version = get_option('bookingpress_multilanguage_version');
                if( version_compare( $bookingpress_multilanguage_version, '1.1', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Recurring Appointments Plugin Requires to update the BookingPress - Multi-Language Plugin to version 1.1 or higher.", "bookingpress-recurring-appointments")."</p></div>";
                }
            }
        }
        
        /**
         * Function for send single message for recurring appointment after booking
         *
         * @param  mixed $bookingpress_check_group_order_for_whatsapp
         * @param  mixed $bookingpress_appointment_data
         * @return void
         */
        function bookingpress_check_group_order_for_whatsapp_func($bookingpress_check_group_order_for_whatsapp,$bookingpress_appointment_data){
            if(!empty($bookingpress_appointment_data)){
                $bookingpress_is_recurring = (isset($bookingpress_appointment_data['bookingpress_is_recurring']))?$bookingpress_appointment_data['bookingpress_is_recurring']:0;
                if($bookingpress_is_recurring == 1){
                    $bookingpress_check_group_order_for_whatsapp = true; 
                }
            }
            return $bookingpress_check_group_order_for_whatsapp;
        }
        
        function bookingpress_modify_check_duplidate_appointment_time_slot_func($bpa_check_duplidate_appointment_time_slot,$posted_data){
            $bpa_is_recurring_appointments = (isset($posted_data['appointment_data']['is_recurring_appointments']))?$posted_data['appointment_data']['is_recurring_appointments']:'';
            if($bpa_is_recurring_appointments == true || $bpa_is_recurring_appointments == "true"){
                $bpa_check_duplidate_appointment_time_slot = false;
            }            
            return $bpa_check_duplidate_appointment_time_slot;
        }

        /**
         * Function for send single message for recurring appointment after booking
         *
         * @param  mixed $bookingpress_send_single_integration_notification_after_booking
         * @param  mixed $bookingpress_appointment_data
         * @return void
         */
        function bookingpress_send_single_sms_notification_after_booking_func($bookingpress_send_single_integration_notification_after_booking,$bookingpress_appointment_data){
            if(!empty($bookingpress_appointment_data)){

                global $wpdb,$tbl_bookingpress_appointment_meta;
                $bookingpress_is_recurring = (isset($bookingpress_appointment_data['bookingpress_is_recurring']))?$bookingpress_appointment_data['bookingpress_is_recurring']:0;
                $bookingpress_order_id = (isset($bookingpress_appointment_data['bookingpress_order_id']))?$bookingpress_appointment_data['bookingpress_order_id']:0;
                $meta_key = 'send_recurring_sms_notification';
                if(($bookingpress_is_recurring == 1 || $bookingpress_is_recurring == '1') && $bookingpress_order_id != 0){
                    $bookingpress_is_recurring_records = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_appointment_meta_id FROM {$tbl_bookingpress_appointment_meta} WHERE bookingpress_order_id = %d AND bookingpress_appointment_meta_key = %s", $bookingpress_order_id,$meta_key ), ARRAY_A );// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_meta is table name defined globally. False Positive alarm
                    if(!empty($bookingpress_is_recurring_records)){
                        $bookingpress_send_single_integration_notification_after_booking = false;     
                    }else{
                        $bookingpress_db_fields = array(
                            'bookingpress_entry_id' => 0,
                            'bookingpress_appointment_id' => 0,
                            'bookingpress_order_id' => $bookingpress_order_id,
                            'bookingpress_appointment_meta_key' => $meta_key,
                            'bookingpress_appointment_meta_value' => 'yes',
                        );
                        $wpdb->insert($tbl_bookingpress_appointment_meta, $bookingpress_db_fields);
                        $bookingpress_send_single_integration_notification_after_booking = true;     
                    }
                }
                
            }

            return $bookingpress_send_single_integration_notification_after_booking;
        }

        
        /**
         * Function for send single message for recurring appointment after booking
         *
         * @param  mixed $bookingpress_send_single_integration_notification_after_booking
         * @param  mixed $bookingpress_appointment_data
         * @return void
         */
        public function bookingpress_send_single_whatsapp_notification_after_booking_func($bookingpress_send_single_integration_notification_after_booking,$bookingpress_appointment_data){
            if(!empty($bookingpress_appointment_data)){

                global $wpdb,$tbl_bookingpress_appointment_meta;
                $bookingpress_is_recurring = (isset($bookingpress_appointment_data['bookingpress_is_recurring']))?$bookingpress_appointment_data['bookingpress_is_recurring']:0;
                $bookingpress_order_id = (isset($bookingpress_appointment_data['bookingpress_order_id']))?$bookingpress_appointment_data['bookingpress_order_id']:0;
                
                if($bookingpress_is_recurring == 1 || $bookingpress_is_recurring == '1' && $bookingpress_order_id != 0){
                    $bookingpress_is_recurring_records = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_appointment_meta_id FROM {$tbl_bookingpress_appointment_meta} WHERE bookingpress_order_id = %d AND bookingpress_appointment_meta_key = 'send_recurring_whatsapp_notification'", $bookingpress_order_id ), ARRAY_A );// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_meta is table name defined globally. False Positive alarm
                    if(!empty($bookingpress_is_recurring_records)){
                        $bookingpress_send_single_integration_notification_after_booking = false;     
                    }else{
                        $bookingpress_db_fields = array(
                            'bookingpress_entry_id' => 0,
                            'bookingpress_appointment_id' => 0,
                            'bookingpress_order_id' => $bookingpress_order_id,
                            'bookingpress_appointment_meta_key' => 'send_recurring_whatsapp_notification',
                            'bookingpress_appointment_meta_value' => 'yes',
                        );
                        $wpdb->insert($tbl_bookingpress_appointment_meta, $bookingpress_db_fields);
                        $bookingpress_send_single_integration_notification_after_booking = true; 
                    }
                }
                
            }

            return $bookingpress_send_single_integration_notification_after_booking;
        }

        /**
         * Function for add calendar recurring appointment.
         *
         * @return void
         */
        public function bookingpress_calendar_integration_urls_for_recurring(){
            if(!is_plugin_active('bookingpress-cart/bookingpress-cart.php')){
            ?>                   
                let has_data = document.getElementById("bookingpress_thankyou_recurring_list");
                if(has_data != null){
                    wp.hooks.addFilter('bookingpress_change_calendar_url', 'bookingpress-cart-addon', function( calendar_link, selected_calendar, bookingpress_appointment_id ){
                        calendar_link = '<?php echo esc_url( get_home_url() ) . '?page=bookingpress_download&action=generate_ics_with_cart&state=' . esc_html( wp_create_nonce( 'bookingpress_calendar_ics' ) ) . '&order_id='; ?>' + bookingpress_appointment_id + '&selectedCalendar=' + selected_calendar;                    
                        return calendar_link;
                    }, 10);
                }                
            <?php                
            }
            ?>
                let has_data_new = document.getElementById("bookingpress_thankyou_recurring_list");
                if(has_data_new != null){                             
                    var app_thankyou = new Vue({
                        el: '#bookingpress_thankyou_recurring_list',
                        data() {
                            var bookingpress_return_data = {
                                open_bookingpress_shortcode_modal: false
                            };
                            return bookingpress_return_data;			
                        },
                        mounted(){
                        },
                        methods: {							
                            
                        },
                    });
                }
            <?php            
        }
        
        /**
         * Function for recurring appointment thankyou page calendar
         *
         * @return void
         */
        function bookingpress_generate_ics_with_cart_items(){
            if(!is_plugin_active('bookingpress-cart/bookingpress-cart.php')){
                if ( ! empty( $_GET['page'] ) && 'bookingpress_download' == $_GET['page'] && ! empty( $_GET['action'] ) && 'generate_ics_with_cart' == $_GET['action'] ) {
                    $nonce = ! empty( $_GET['state'] ) ? sanitize_text_field( $_GET['state'] ) : '';                    
                    if ( ! wp_verify_nonce( $nonce, 'bookingpress_calendar_ics' ) ) {
                        return false;
                    }
                    if ( empty( $_GET['order_id'] ) ) {
                        return false;
                    }
                    $order_id = intval( $_GET['order_id'] );
                    global $wpdb,$tbl_bookingpress_entries, $tbl_bookingpress_appointment_bookings, $BookingPress, $bookingpress_appointment_bookings;
                    $get_all_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$tbl_bookingpress_appointment_bookings}` WHERE bookingpress_order_id = %d", $order_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally.
                    if( !empty( $get_all_appointments ) ){
                        $calendar_vevent = '';
                        foreach( $get_all_appointments as $appointment_data ){                            
                            $service_id              = intval( $appointment_data['bookingpress_service_id'] );
                            $bookingpress_start_time = $service_start_time    = sanitize_text_field( $appointment_data['bookingpress_appointment_time'] );
                            $service_duration        = sanitize_text_field( $appointment_data['bookingpress_service_duration_val'] );
                            $service_duration_unit   = sanitize_text_field( $appointment_data['bookingpress_service_duration_unit'] );
                            $service_end_time        = $BookingPress->bookingpress_get_service_end_time( $service_id, $service_start_time, $service_duration, $service_duration_unit );
                            $bookingpress_start_time = date( 'Ymd', strtotime( $appointment_data['bookingpress_appointment_date'] ) ) . 'T' . date( 'His', strtotime( $bookingpress_start_time ) );
                            $bookingpress_start_time_for_yahoo = date( 'Ymd', strtotime( $appointment_data['bookingpress_appointment_date'] ) ) . 'T' . date( 'His', strtotime( $bookingpress_start_time ) ) . 'Z';

                            $bookingpress_end_time     = date( 'Ymd', strtotime( $appointment_data['bookingpress_appointment_date'] ) ) . 'T' . date( 'His', strtotime( $service_end_time['service_end_time'] ) );
                            $bookingpress_end_time_for_yahoo = date( 'Ymd', strtotime( $appointment_data['bookingpress_appointment_date'] ) ) . 'T' . date( 'His', strtotime( $bookingpress_end_time ) ) . 'Z';
                            
                            $user_timezone             = wp_timezone_string();
                            $bookingpress_service_name = ! empty( $appointment_data['bookingpress_service_name'] ) ? sanitize_text_field( $appointment_data['bookingpress_service_name'] ) : '';

                            $booking_stime = $bookingpress_appointment_bookings->bookingpress_convert_date_time_to_utc( $appointment_data['bookingpress_appointment_date'], $bookingpress_start_time );
                            $booking_etime = $bookingpress_appointment_bookings->bookingpress_convert_date_time_to_utc( $appointment_data['bookingpress_appointment_date'], $service_end_time['service_end_time'] );
                            $current_dtime = $bookingpress_appointment_bookings->bookingpress_convert_date_time_to_utc( date( 'm/d/Y' ), 'g:i A' );

                            $calendar_vevent .= "BEGIN:VEVENT\r\n";
                            $calendar_vevent .= 'UID:' . md5( $service_start_time ) . "\r\n";
                            if( isset($_REQUEST['selectedCalendar']) && 'yahoo_calendar' == $_REQUEST['selectedCalendar'] ){
                                $calendar_vevent .= 'DTSTART:' . $bookingpress_start_time_for_yahoo . "\r\n";
                            } else {
                                $calendar_vevent .= 'DTSTART:' . $booking_stime . "\r\n";
                            }
                            $calendar_vevent .= "SEQUENCE:0\r\n";
                            $calendar_vevent .= "TRANSP:OPAQUE\r\n";
                            if( isset($_REQUEST['selectedCalendar']) && 'yahoo_calendar' == $_REQUEST['selectedCalendar'] ){
                                $calendar_vevent .= "DTEND:{$bookingpress_end_time_for_yahoo}\r\n";
                            } else {
                                $calendar_vevent .= "DTEND:{$booking_etime}\r\n";
                            }
                            $calendar_vevent .= "SUMMARY:{$bookingpress_service_name}\r\n";
                            $calendar_vevent .= "CLASS:PUBLIC\r\n";
                            $calendar_vevent .= "DTSTAMP:{$current_dtime}\r\n";
                            $calendar_vevent .= "END:VEVENT\r\n";
                        }
                        $string  = "BEGIN:VCALENDAR\r\n";
                        $string .= "VERSION:2.0\r\n";
                        $string .= 'PRODID:BOOKINGPRESS APPOINTMENT BOOKING\\\\' . get_bloginfo('title') . "\r\n";
                        $string .= "X-PUBLISHED-TTL:P1W\r\n";
                        $string .= $calendar_vevent;
                        $string .= "END:VCALENDAR\r\n";
                        
                        header( 'Content-Type: text/calendar; charset=utf-8' );
                        header( 'Content-Disposition: attachment; filename="cal.ics"' );
                        
                        echo $string; //phpcs:ignore
                    }
                    die;
                }
            }
        }

        /**
         * Function for Mailchamp & Aweber Integration
         *
         * @param  mixed $bookingpress_waiting_ap
         * @param  mixed $appointment_data
         * @return void
        */
        public function bookingpress_check_waiting_after_front_book_for_integration_func($bookingpress_waiting_ap,$appointment_data){            
            $is_recurring_appointments = (isset($appointment_data['is_recurring_appointments']))?$appointment_data['is_recurring_appointments']:''; //phpcs:ignore
            if($is_recurring_appointments == 'true'){
                $bookingpress_waiting_ap = false;
            }
            return $bookingpress_waiting_ap;
        }
        
        /**
         * Function for change invoice HTML
         *
         * @param  mixed $bookingpress_invoice_html_view
         * @param  mixed $bookingpress_final_appointment_details
         * @param  mixed $log_id
         * @return void
         */
        function bookingpress_change_recurring_content_for_invoice_func($bookingpress_invoice_html_view, $bookingpress_final_appointment_details,$log_id){

            $bookingpress_recurring_content = array();
            $bookingpress_invoice_html_view = "      ".$bookingpress_invoice_html_view;
            $has_recurring_shortcode = strpos($bookingpress_invoice_html_view,"BOOKINGPRESS_RECURRING_ITEMS");
            if($has_recurring_shortcode){
                
                $bookingpress_is_recurring = (isset($bookingpress_final_appointment_details[0]['all_fields']['bookingpress_is_recurring']))?$bookingpress_final_appointment_details[0]['all_fields']['bookingpress_is_recurring']:0;                
                $has_cart_shortcode = strpos($bookingpress_invoice_html_view,"BOOKINGPRESS_CART_ITEMS");
                $bookingpress_apply_recurring = true;
                if(($bookingpress_is_recurring == 1 || $bookingpress_is_recurring == '1') || !$has_cart_shortcode){                    

                }else{
                    $bookingpress_apply_recurring = false;
                }                
                $bookingpress_apply_recurring = true;

                $bookingpress_recurring_tag_starting_pos = strpos($bookingpress_invoice_html_view, '[BOOKINGPRESS_RECURRING_ITEMS]', 0);
                $bookingpress_recurring_tag_ending_pos = strpos($bookingpress_invoice_html_view, '[/BOOKINGPRESS_RECURRING_ITEMS]', $bookingpress_recurring_tag_starting_pos);
                $bookingpress_recurring_tag_ending_pos = $bookingpress_recurring_tag_ending_pos + 32;
                $bookingpress_recurring_html_content = substr($bookingpress_invoice_html_view, $bookingpress_recurring_tag_starting_pos, $bookingpress_recurring_tag_ending_pos);
                
                $bookingpress_invoice_html_view = " ".$bookingpress_invoice_html_view;
                $offset = 0;
                while(true){
                    $ini = strpos($bookingpress_invoice_html_view, "[BOOKINGPRESS_RECURRING_ITEMS]",$offset);
                    if ($ini == 0)
                        break;
                    $ini += strlen("[BOOKINGPRESS_RECURRING_ITEMS]");
                    $len = strpos($bookingpress_invoice_html_view, "[/BOOKINGPRESS_RECURRING_ITEMS]",$ini) - $ini;
                    $bookingpress_recurring_content[] = substr($bookingpress_invoice_html_view, $ini,$len);
                    $offset = $ini+$len;
                }                  
                if(!$bookingpress_apply_recurring){
                    $bookingpress_invoice_html_view = substr_replace($bookingpress_invoice_html_view, "", $bookingpress_recurring_tag_starting_pos, ($bookingpress_recurring_tag_ending_pos-$bookingpress_recurring_tag_starting_pos));                                       
                }
                if(!empty($bookingpress_recurring_content) && $bookingpress_apply_recurring){
                    if(!empty($bookingpress_final_appointment_details)){
                        if(!empty($bookingpress_recurring_content)){
                            $bookingpress_tmp_recurring_invoice_html = "";
                            $bookingpress_recur_invoice_html = !empty($bookingpress_recurring_content[0]) ? $bookingpress_recurring_content[0] : '';
                            if(count($bookingpress_final_appointment_details) > 1){
                                $bookingpress_tmp_recurring_invoice_html = "";
                                foreach($bookingpress_final_appointment_details as $final_appointment_key => $final_appointment_val){
                                    $bookingpress_tmp_recurring_invoice_html .= $bookingpress_recur_invoice_html;                                    
                                    $bookingpress_tmp_recurring_invoice_html = str_replace('{service_name}', $final_appointment_val['service'], $bookingpress_tmp_recurring_invoice_html);
                                    $bookingpress_tmp_recurring_invoice_html = str_replace('{appointment_date}', $final_appointment_val['appointment_date'], $bookingpress_tmp_recurring_invoice_html);
                                    $staff_name = (isset($final_appointment_val['staff_name']))?$final_appointment_val['staff_name']:'';
                                    $bookingpress_tmp_recurring_invoice_html = str_replace('{staffmember_name}', $staff_name, $bookingpress_tmp_recurring_invoice_html);
                                    $bookingpress_tmp_recurring_invoice_html = str_replace('{service_price}', $final_appointment_val['service_price'], $bookingpress_tmp_recurring_invoice_html);
                                    $bookingpress_tmp_recurring_invoice_html = str_replace('{number_of_person}', $final_appointment_val['number_of_person'], $bookingpress_tmp_recurring_invoice_html);
                                    $bookingpress_tmp_recurring_invoice_html = str_replace('{appointment_time}', $final_appointment_val['appointment_time'], $bookingpress_tmp_recurring_invoice_html);
                                    $bookingpress_tmp_recurring_invoice_html = str_replace('{service_extras}', $final_appointment_val['service_extras'], $bookingpress_tmp_recurring_invoice_html);

                                }
                                $bookingpress_cart_invoice_html = $bookingpress_tmp_recurring_invoice_html;
                            }else{
                                foreach($bookingpress_final_appointment_details as $final_appointment_key => $final_appointment_val){

                                    $bookingpress_tmp_recurring_invoice_html .= $bookingpress_recur_invoice_html;
                                    $bookingpress_tmp_recurring_invoice_html = str_replace('{service_name}', $final_appointment_val['service'], $bookingpress_tmp_recurring_invoice_html);
                                    $bookingpress_tmp_recurring_invoice_html = str_replace('{appointment_date}', $final_appointment_val['appointment_date'], $bookingpress_tmp_recurring_invoice_html);
                                    $staff_name = (isset($final_appointment_val['staff_name']))?$final_appointment_val['staff_name']:'';
                                    $bookingpress_tmp_recurring_invoice_html = str_replace('{staffmember_name}', $staff_name, $bookingpress_tmp_recurring_invoice_html);
                                    $bookingpress_tmp_recurring_invoice_html = str_replace('{service_price}', $final_appointment_val['service_price'], $bookingpress_tmp_recurring_invoice_html);
                                    $bookingpress_tmp_recurring_invoice_html = str_replace('{number_of_person}', $final_appointment_val['number_of_person'], $bookingpress_tmp_recurring_invoice_html);                                     
                                    $bookingpress_tmp_recurring_invoice_html = str_replace('{appointment_time}', $final_appointment_val['appointment_time'], $bookingpress_tmp_recurring_invoice_html);
                                    $bookingpress_tmp_recurring_invoice_html = str_replace('{service_extras}', $final_appointment_val['service_extras'], $bookingpress_tmp_recurring_invoice_html);


                                }
                            }                            
                            $bookingpress_invoice_html_view = substr_replace($bookingpress_invoice_html_view, $bookingpress_tmp_recurring_invoice_html, $bookingpress_recurring_tag_starting_pos, ($bookingpress_recurring_tag_ending_pos-$bookingpress_recurring_tag_starting_pos));                            
                        }
                    }    
                }
                if($bookingpress_apply_recurring){
                    $bookingpress_invoice_html_view = " ".$bookingpress_invoice_html_view;
                    $bookingpress_tag_starting_pos = strpos($bookingpress_invoice_html_view, '[BOOKINGPRESS_CART_ITEMS]', 0);
                    $bookingpress_tag_ending_pos = strpos($bookingpress_invoice_html_view, '[/BOOKINGPRESS_CART_ITEMS]', $bookingpress_tag_starting_pos);
                    $bookingpress_tag_ending_pos = $bookingpress_tag_ending_pos + 27;                                        
                    $bookingpress_invoice_html_view = substr_replace($bookingpress_invoice_html_view, "", $bookingpress_tag_starting_pos, ($bookingpress_tag_ending_pos-$bookingpress_tag_starting_pos));
                }
            }
            return $bookingpress_invoice_html_view;
        }
        
        /**
         * Invoice Preview
         *
         * @return void
         */
        function bookingpress_before_open_invoice_preview_func(){
        ?>
            bookingpress_preview_html = bookingpress_preview_html.replace('[BOOKINGPRESS_RECURRING_ITEMS]', '');
            bookingpress_preview_html = bookingpress_preview_html.replace('[/BOOKINGPRESS_RECURRING_ITEMS]', '');        
        <?php 
        }
		
		/**
		 * Function for send complete payment URL from backend
		 *
		 * @param  mixed $bookingpress_order_id
		 * @return void
		 */
		function bookingpress_send_complete_payment_url_notification($bookingpress_order_id = 0){
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $bookingpress_email_notifications;                        
			$bookingpress_appointment_details = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_appointment_booking_id,bookingpress_customer_email FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_order_id = %d", $bookingpress_order_id), ARRAY_A);//phpcs:ignore
			if(!empty($bookingpress_appointment_details['bookingpress_customer_email']) && !empty($_POST['appointment_data']['complete_payment_url_selection']) && $_POST['appointment_data']['complete_payment_url_selection'] == 'send_payment_link' && !empty($_POST['appointment_data']['complete_payment_url_selected_method'] && in_array('email',$_POST['appointment_data']['complete_payment_url_selected_method']))){ //phpcs:ignore
				$bookingpress_email_notifications->bookingpress_send_after_payment_log_entry_email_notification('Complete Payment URL', $bookingpress_appointment_details['bookingpress_appointment_booking_id'], $bookingpress_appointment_details['bookingpress_customer_email']);
			}
		}

        /**
         * Function for full row clickable.
         *
         * @return void
         */
        function bookingpress_appointment_full_row_clickable_func(){
        ?>
            let source = events.target;
            if( null != source ){
                let parents = vm.BPAGetParents( source, '.bpa-apc__recurring-icon' );
                if( source.classList.contains('bpa-apc__recurring-icon') || parents.length > 0 ){
                    return false;
                }
            }            
        <?php 
        }


        /**
         * bookingpress_add_setting_msg_outside
         * 
         * @return void
         */
        function bookingpress_add_setting_msg_outside()
        { ?>
            <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64" >
                <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                    <h4> <?php esc_html_e('Please select recurring start date', 'bookingpress-recurring-appointments'); ?></h4>                    
                </el-col>
                <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                    <el-form-item prop="recurring_start_date_validation_message">
                    <el-input class="bpa-form-control" v-model="message_setting_form.recurring_start_date_validation_message"></el-input>        
                </el-col>
            </el-row>
            <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64" >
                <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                    <h4> <?php esc_html_e('Please select recurring timeslot', 'bookingpress-recurring-appointments'); ?></h4>                    
                </el-col>
                <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                    <el-form-item prop="recurring_timeslot_validation_message">
                    <el-input class="bpa-form-control" v-model="message_setting_form.recurring_timeslot_validation_message"></el-input>        
                </el-col>
            </el-row>
            <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64" >
                <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                    <h4> <?php esc_html_e('Please select recurring no of session', 'bookingpress-recurring-appointments'); ?></h4>                    
                </el-col>
                <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                    <el-form-item prop="recurring_no_of_session_validation_message">
                    <el-input class="bpa-form-control" v-model="message_setting_form.recurring_no_of_session_validation_message"></el-input>        
                </el-col>
            </el-row>
            <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64" >
                <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                    <h4> <?php esc_html_e('Please select recurring frequency', 'bookingpress-recurring-appointments'); ?></h4>                    
                </el-col>
                <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                    <el-form-item prop="recurring_frequency_validation_message">
                    <el-input class="bpa-form-control" v-model="message_setting_form.recurring_frequency_validation_message"></el-input>        
                </el-col>
            </el-row>

            <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64" >
                <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                    <h4> <?php esc_html_e('Please add recurring appointment', 'bookingpress-recurring-appointments'); ?></h4>                    
                </el-col>
                <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                    <el-form-item prop="recurring_appointment_add_validation_message">
                    <el-input class="bpa-form-control" v-model="message_setting_form.recurring_appointment_add_validation_message"></el-input>        
                </el-col>
            </el-row>
            <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64" >
                <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                    <h4> <?php esc_html_e('Please select time for not available appointment', 'bookingpress-recurring-appointments'); ?></h4>                    
                </el-col>
                <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                    <el-form-item prop="recurring_not_avaliable_appointment_validation_message">
                    <el-input class="bpa-form-control" v-model="message_setting_form.recurring_not_avaliable_appointment_validation_message"></el-input>        
                </el-col>
            </el-row>
            <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64" >
                <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                    <h4> <?php esc_html_e('Selected date or time slot is suggested.', 'bookingpress-recurring-appointments'); ?></h4>                    
                </el-col>
                <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                    <el-form-item prop="recurring_suggested_message">
                    <el-input class="bpa-form-control" v-model="message_setting_form.recurring_suggested_message"></el-input>        
                </el-col>
            </el-row>
            <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64" >
                <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                    <h4> <?php esc_html_e('Selected or suggested time slot not available.', 'bookingpress-recurring-appointments'); ?></h4>                    
                </el-col>
                <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                    <el-form-item prop="recurring_not_avaliable_message">
                    <el-input class="bpa-form-control" v-model="message_setting_form.recurring_not_avaliable_message"></el-input>        
                </el-col>
            </el-row>
            <?php
        }

        
        /**
         * Function for thankyou page datetime data
         *
         * @param  mixed $content
         * @param  mixed $appointment_data
         * @return void
         */
        function bookingpress_modify_datetime_shortcode_content_func($content, $appointment_data)
        {
            global $BookingPress,$bookingpress_global_options, $wpdb, $tbl_bookingpress_entries;            
            $bookingpress_is_recurring = (isset($appointment_data[0]['bookingpress_is_recurring']))?$appointment_data[0]['bookingpress_is_recurring']:'';
            if($bookingpress_is_recurring == '1' && !empty($appointment_data)){

                $BookingPress->set_front_css( 1 );
                $BookingPress->set_front_js( 1 );
                $BookingPress->bookingpress_load_booking_form_custom_css();                

                $bookingpress_global_options_arr       = $bookingpress_global_options->bookingpress_global_options();
                $bookingpress_default_date_time_format = $bookingpress_global_options_arr['wp_default_date_format'] . ' ' . $bookingpress_global_options_arr['wp_default_time_format'];
                
                $bookingpress_recurring_appointments_date_time = array();
                $bookingpress_recurring_appointments_date_time_first = '';

                if(empty($appointment_data['bookingpress_appointment_date'])){

                    foreach($appointment_data as $appointment_data_key => $appointment_data_val){
                        $booked_appointment_datetime = esc_html($appointment_data_val['bookingpress_appointment_date']) . ' ' . esc_html($appointment_data_val['bookingpress_appointment_time']);
                        if(empty($bookingpress_entry_details['bookingpress_customer_timezone'])){
                            $bookingpress_entry_id = !empty($appointment_data_val['bookingpress_entry_id']) ? intval($appointment_data_val['bookingpress_entry_id']) : 0;
                            if(!empty($bookingpress_entry_id)){

                                //Get entries details
                                $bookingpress_entry_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_entries} WHERE bookingpress_entry_id = %d",$bookingpress_entry_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm
                            }
                        }
                        $booked_appointment_datetime = apply_filters( 'bookingpress_appointment_change_to_client_timezone', $booked_appointment_datetime, $bookingpress_entry_details['bookingpress_customer_timezone'], $bookingpress_entry_details );                    
                        $booked_appointment_date = date($bookingpress_default_date_time_format, strtotime($booked_appointment_datetime));                    
                        if(empty($bookingpress_recurring_appointments_date_time_first)){
                            $bookingpress_recurring_appointments_date_time_first = $booked_appointment_date;
                            $bookingpress_recurring_appointments_date_time[] = $booked_appointment_date;
                        }else{
                            $bookingpress_recurring_appointments_date_time[] = $booked_appointment_date;
                        } 
                    }

                }else{

                    $booked_appointment_datetime = esc_html($appointment_data['bookingpress_appointment_date']) . ' ' . esc_html($appointment_data['bookingpress_appointment_time']);
                    if(empty($bookingpress_entry_details['bookingpress_customer_timezone'])){
                        $bookingpress_entry_id = !empty($appointment_data['bookingpress_entry_id']) ? intval($appointment_data['bookingpress_entry_id']) : 0;
                        if(!empty($bookingpress_entry_id)){
                            $bookingpress_entry_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_entries} WHERE bookingpress_entry_id = %d",$bookingpress_entry_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm
                        }
                    }                    
                    $booked_appointment_datetime = apply_filters( 'bookingpress_appointment_change_to_client_timezone', $booked_appointment_datetime, $bookingpress_entry_details['bookingpress_customer_timezone'], $bookingpress_entry_details );                    
                    $booked_appointment_date = date_i18n($bookingpress_default_date_time_format, strtotime($booked_appointment_datetime));                    
                    $bookingpress_recurring_appointments_date_time_first = $booked_appointment_date;

                }

                $content .= "<div class='bookingpress_appointment_datetime_div'>";
                $content .= "<span class='bookingpress_appointment_datetime'>" . $bookingpress_recurring_appointments_date_time_first . '</span>';
                $content .= '</div><br/>';                

                if(!empty($bookingpress_recurring_appointments_date_time)){                    

                    $recurring_more_datetime_label = $BookingPress->bookingpress_get_customize_settings('recurring_more_datetime_label','booking_form');                    
                    $appointment_id = (isset( $_REQUEST['appointment_id'] ) ? intval( base64_decode( $_REQUEST['appointment_id'] ) ) : '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized                                                                                   
                    $content.= '
                    <div class="bookingpress-thankyou-recurring-appointment-list" id="bookingpress_thankyou_recurring_list">
                    <el-popover placement="bottom" width="295" trigger="hover" popper-class="bpa--summary_front_popover bpa--summary_front_recurring-popup-thankyou bpa--summary-recurring_front_popover">
                        <div class="bpa-front-tabs"> 
                        <div class="bpa-front-module--booking-summary bpa-front-sm-module--booking-service-wrapper">                        
                        ';
                        foreach($bookingpress_recurring_appointments_date_time as $recurring_appointment){
                            $content.= '<div class="bpa-aaf-recurring__item bpa_summary_service_datetime_block bpa-front-module--bs-summary-content"><div class="bpa_summary_rec_datetime_body_inner bpa_rec_popover_datetime_item">'.$recurring_appointment.'</div></div>';
                        }                        
                    $content.= '
                        </div></div>
                    <span slot="reference" class="bpa--summary_extra_count_name bpa-thank-you-datetime-count">'.(count($bookingpress_recurring_appointments_date_time)-1).' '.esc_html(stripslashes_deep($recurring_more_datetime_label)).' </span>
                    </el-popover>
                    </div>';
                    
                }
            }

            return $content;
        }



        
        /**
         * Function for get recurring appointment list for front & backend
         *
         * @return void
         */
        function bookingpress_get_recurring_appointment_list_func()
        {
            global $wpdb, $tbl_bookingpress_appointment_bookings, $bookingpress_global_options;
            $response = array();

            $wpnonce               = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';            
            $bpa_verify_nonce_flag = wp_verify_nonce($wpnonce, 'bpa_wp_nonce');
			if (!$bpa_verify_nonce_flag){
				$response                = array();
				$response['variant']     = 'error';
				$response['title']       = esc_html__( 'Error', 'bookingpress-recurring-appointments' );
				$response['msg']         = esc_html__( 'Sorry, Your request can not be processed due to security reason.', 'bookingpress-recurring-appointments' );
				$response['rec_appointment_data'] = array();
				echo wp_json_encode( $response );
				die();
			}

            $bookingpress_order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : ''; // phpcs:ignore WordPress.Security.NonceVerification
            $bookingpress_global_options_arr       = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_default_date_format = $bookingpress_global_options_arr['wp_default_date_format'];
            $bookingpress_default_time_format = $bookingpress_global_options_arr['wp_default_time_format'];
            if(!empty($bookingpress_order_id)){
                $bpa_rec_appointment_data = $wpdb->get_results($wpdb->prepare("SELECT bookingpress_booking_id,bookingpress_appointment_date,bookingpress_appointment_time,bookingpress_appointment_end_time,bookingpress_service_name FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_order_id = %d AND bookingpress_is_recurring = %d", $bookingpress_order_id,1), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
                if(!empty($bpa_rec_appointment_data)) {
                    $rec_appointment_list = array();
                    $booking_id = '';
                    $service_name ='';
                    foreach($bpa_rec_appointment_data as $appointment_data_key => $appointment_data_val){

                        $booked_appointment_date = esc_html($appointment_data_val['bookingpress_appointment_date']);
                        $booked_appointment_start_time = esc_html($appointment_data_val['bookingpress_appointment_time']);
                        $booked_appointment_end_time = esc_html($appointment_data_val['bookingpress_appointment_end_time']);
                        $bookingpress_appointment_start_time = date($bookingpress_default_time_format, strtotime($booked_appointment_start_time));
                        $bookingpress_appointment_end_time   = date($bookingpress_default_time_format, strtotime($booked_appointment_end_time));
                        $bookingpress_appointment_date =  date($bookingpress_default_date_format,strtotime($booked_appointment_date));
                        $appointment_data = array();
                        $appointment_data['booking_id'] = esc_html($appointment_data_val['bookingpress_booking_id']);
                        $appointment_data['date'] = $bookingpress_appointment_date;
                        $appointment_data['time'] = $bookingpress_appointment_start_time." - ".$bookingpress_appointment_end_time;
                        $appointment_data['service_name'] = esc_html($appointment_data_val['bookingpress_service_name']);

                        $booking_id = $appointment_data_val['bookingpress_booking_id'];
                        if(empty($service_name)){
                            $service_name = esc_html($appointment_data_val['bookingpress_service_name']);
                        }                        
                        $rec_appointment_list[] = $appointment_data;

                    }                    
                    $response['variant']     = 'success';
                    $response['booking_id']     = $booking_id;
                    $response['service_name']     = $service_name;
                    $response['rec_appointment_data'] = $rec_appointment_list;
                }
            }
            echo wp_json_encode($response);
			exit;
        }

                
        /**
         * Function for recurring appointment complete payment status update.
         *
         * @param  mixed $bookingpress_check_is_group_order
         * @param  mixed $entry_id
         * @param  mixed $bookingpress_order_id
         * @return void
         */
        function bookingpress_check_is_group_order_for_complete_payment_update_func($bookingpress_check_is_group_order, $entry_id,$bookingpress_order_id){
            if($bookingpress_order_id != 0){
                $bookingpress_check_is_group_order = true;
            }                
            return $bookingpress_check_is_group_order;
        }

        /**
         * Function for add complete payment summary when recurring appointment added
         *
         * @return void
         */
        function bookingpress_recurring_appointment_complete_payment_summary_func($bookingpress_pass_label){
            global $BookingPress;
            $bookingpress_service_text = (isset($bookingpress_pass_label['bookingpress_service_text']))?$bookingpress_pass_label['bookingpress_service_text']:'';
            $bookingpress_date_time_text = (isset($bookingpress_pass_label['bookingpress_date_time_text']))?$bookingpress_pass_label['bookingpress_date_time_text']:'';
            $bookingpress_appointment_details_title_text = (isset($bookingpress_pass_label['bookingpress_appointment_details_title_text']))?$bookingpress_pass_label['bookingpress_appointment_details_title_text']:'';
            $recurring_more_datetime_label = $BookingPress->bookingpress_get_customize_settings('recurring_more_datetime_label','booking_form');
        ?>            
            <div v-if="bookingpress_is_recurring == '1'" class="bpa-front-module--bs-summary-content bpa-front-summary-content__lg">
                <div class="bpa-front-module--bs-summary-content-item">
                    <span><?php echo esc_html( $bookingpress_service_text ); ?></span>
                    <div class="bpa-front-bs-sm__item-val">{{ appointment_step_form_data.selected_service_name}}</div>
                </div>									
                <div class="bpa-front-module--bs-summary-content-item">

                    <span><?php echo esc_html( $bookingpress_date_time_text ); ?></span>
                    <div class="bpa-front-bs-sm__item-val" v-if="(bookigpress_time_format_for_booking_form == '1' || bookigpress_time_format_for_booking_form == '2') && (is_cart_enabled == '1')" v-for="(cart_details, key) in cart_items.slice(0,1)">
                        {{ cart_details.bookingpress_selected_date | bookingpress_format_date }}, {{ cart_details.bookingpress_selected_start_time }} to {{ cart_details.bookingpress_selected_end_time }}
                    </div>
                    <div class="bpa-front-bs-sm__item-val" v-if="(bookigpress_time_format_for_booking_form == '5' || bookigpress_time_format_for_booking_form == '6') && (is_cart_enabled == '1')" v-for="(cart_details, key) in cart_items.slice(0,1)">
                        {{ cart_details.bookingpress_selected_date | bookingpress_format_date }}, {{ cart_details.bookingpress_selected_start_time }} - {{ cart_details.bookingpress_selected_end_time }}
                    </div>
                    <div class="bpa-front-bs-sm__item-val" v-if="(bookigpress_time_format_for_booking_form == '3' || bookigpress_time_format_for_booking_form == '4') && (is_cart_enabled == '1')" v-for="(cart_details, key) in cart_items.slice(0,1)">
                        {{ cart_details.bookingpress_selected_date | bookingpress_format_date }}, {{ cart_details.bookingpress_selected_start_time }}
                    </div>
                    <el-popover v-if="recurring_more_appointment != 0" placement="right" width="335" trigger="hover" popper-class="bpa--summary_front_popover bpa--summary-recurring_front_popover">
                        <div class="bpa-front-tabs">
                                <div class="bpa-front-module--booking-summary bpa-front-sm-module--booking-service-wrapper">                                    
                                    <div class="bpa-front-bs-sm__item-val bpa_rec_popover_datetime_item" v-if="(bookigpress_time_format_for_booking_form == '1' || bookigpress_time_format_for_booking_form == '2') && (is_cart_enabled == '1')" v-for="(cart_details, key) in cart_items">
                                        {{ cart_details.bookingpress_selected_date | bookingpress_format_date }}, {{ cart_details.bookingpress_selected_start_time }} to {{ cart_details.bookingpress_selected_end_time }}
                                    </div>
                                    <div class="bpa-front-bs-sm__item-val bpa_rec_popover_datetime_item" v-if="(bookigpress_time_format_for_booking_form == '5' || bookigpress_time_format_for_booking_form == '6') && (is_cart_enabled == '1')" v-for="(cart_details, key) in cart_items">
                                        {{ cart_details.bookingpress_selected_date | bookingpress_format_date }}, {{ cart_details.bookingpress_selected_start_time }} - {{ cart_details.bookingpress_selected_end_time }}
                                    </div>
                                    <div class="bpa-front-bs-sm__item-val bpa_rec_popover_datetime_item" v-if="(bookigpress_time_format_for_booking_form == '3' || bookigpress_time_format_for_booking_form == '4') && (is_cart_enabled == '1')" v-for="(cart_details, key) in cart_items">
                                        {{ cart_details.bookingpress_selected_date | bookingpress_format_date }}, {{ cart_details.bookingpress_selected_start_time }}
                                    </div>                                                            
                                </div>
                        </div>
                        <span slot="reference"  class="bpa--summary_service_datetime_count_name"> {{recurring_more_appointment}}<?php echo ' '.esc_html(stripslashes_deep($recurring_more_datetime_label)); ?></span>
                    </el-popover>

                </div>
            </div>     
            <div v-if="bookingpress_is_recurring == '1'" class="bpa-front-module--bs-summary-content bpa-front-summary-content__sm">
                <div class="bpa-front-module--bs-summary-content-item">
                    <span><?php echo esc_html($bookingpress_appointment_details_title_text); ?></span>
                    <div class="bpa-front-bs-sm__item-vals"  v-for="(cart_details, key) in cart_items.slice(0,1)">
                        <div class="bpa-front-bs-sm__item-val">{{ cart_details.bookingpress_service_name}}</div>
                        <div class="bpa-front-bs-sm__item-val" v-if="(bookigpress_time_format_for_booking_form == '1' || bookigpress_time_format_for_booking_form == '2')">{{ cart_details.bookingpress_selected_date | bookingpress_format_date }}, {{ cart_details.bookingpress_selected_start_time }} to {{ cart_details.bookingpress_selected_end_time }}
                        </div>

                        <div class="bpa-front-bs-sm__item-val" v-if="(bookigpress_time_format_for_booking_form == '5' || bookigpress_time_format_for_booking_form == '6')">{{ cart_details.bookingpress_selected_date | bookingpress_format_date }}, {{ cart_details.bookingpress_selected_start_time }} - {{ cart_details.bookingpress_selected_end_time }}
                        </div>
                        
                        <div class="bpa-front-bs-sm__item-val" v-if="(bookigpress_time_format_for_booking_form == '3' || bookigpress_time_format_for_booking_form == '4')">{{ cart_details.bookingpress_selected_date | bookingpress_format_date }}, {{ cart_details.bookingpress_selected_start_time }}
                        </div>
                    </div>
                    <el-popover v-if="recurring_more_appointment != 0" placement="bottom" width="335" trigger="hover" popper-class="bpa--summary_front_popover">
                        <div class="bpa-front-tabs">
                                <div class="bpa-front-module--booking-summary bpa-front-sm-module--booking-service-wrapper">                                    
                                    <div class="bpa-front-bs-sm__item-val bpa_rec_popover_datetime_item" v-if="(bookigpress_time_format_for_booking_form == '1' || bookigpress_time_format_for_booking_form == '2') && (is_cart_enabled == '1')" v-for="(cart_details, key) in cart_items">
                                        {{ cart_details.bookingpress_selected_date | bookingpress_format_date }}, {{ cart_details.bookingpress_selected_start_time }} to {{ cart_details.bookingpress_selected_end_time }}
                                    </div>
                                    <div class="bpa-front-bs-sm__item-val bpa_rec_popover_datetime_item" v-if="(bookigpress_time_format_for_booking_form == '5' || bookigpress_time_format_for_booking_form == '6') && (is_cart_enabled == '1')" v-for="(cart_details, key) in cart_items">
                                        {{ cart_details.bookingpress_selected_date | bookingpress_format_date }}, {{ cart_details.bookingpress_selected_start_time }} - {{ cart_details.bookingpress_selected_end_time }}
                                    </div>
                                    <div class="bpa-front-bs-sm__item-val bpa_rec_popover_datetime_item" v-if="(bookigpress_time_format_for_booking_form == '3' || bookigpress_time_format_for_booking_form == '4') && (is_cart_enabled == '1')" v-for="(cart_details, key) in cart_items">
                                        {{ cart_details.bookingpress_selected_date | bookingpress_format_date }}, {{ cart_details.bookingpress_selected_start_time }}
                                    </div>                                                            
                                </div>
                        </div>
                        <span slot="reference"  class="bpa--summary_service_datetime_count_name"> {{recurring_more_appointment}}<?php echo ' '.esc_html(stripslashes_deep($recurring_more_datetime_label)); ?></span>
                    </el-popover>                    
                </div>
            </div>               
        <?php 
        }

                
        /**
         * Complete Payment Data Modified
         *
         * @param  mixed $bookingpress_complete_payment_data_vars
         * @param  mixed $bookingpress_appointment_details
         * @return void
         */
        function modify_complate_payment_data_after_entry_create_func($bookingpress_complete_payment_data_vars, $bookingpress_appointment_details){
            $bookingpress_is_recurring = (isset($bookingpress_appointment_details['bookingpress_is_recurring'])) ? intval($bookingpress_appointment_details['bookingpress_is_recurring']) : 0;
            $bookingpress_complete_payment_data_vars['bookingpress_is_recurring'] = $bookingpress_is_recurring;
            $cart_items = (isset($bookingpress_complete_payment_data_vars['cart_items']))?$bookingpress_complete_payment_data_vars['cart_items']:array();
            $recurring_more_appointment = 0;
            if($bookingpress_is_recurring){
                $recurring_more_appointment = count($cart_items) - 1;
            }
            $bookingpress_complete_payment_data_vars['recurring_more_appointment'] = $recurring_more_appointment;
            if($bookingpress_appointment_details['bookingpress_complete_payment_url_selection'] == 'send_payment_link'){
                $deposit_payment_amount = $bookingpress_complete_payment_data_vars['appointment_step_form_data']['deposit_payment_amount'];
                if($deposit_payment_amount == 0 || $deposit_payment_amount == '0'){
                    $bookingpress_complete_payment_data_vars['appointment_step_form_data']['deposit_payment_amount'] = '';
                }                
            }
            return $bookingpress_complete_payment_data_vars;
        }

        /**
         * Function for check complete payment group order
         *
         * @param  mixed $bookingpress_is_group_order
         * @param  mixed $bookingpress_appointment_details
         * @return void
        */
        function bookingpress_check_is_group_order_for_complete_payment_func($bookingpress_is_group_order, $bookingpress_appointment_details){                        
            $bookingpress_is_recurring = (isset($bookingpress_appointment_details['bookingpress_is_recurring'])) ? intval($bookingpress_appointment_details['bookingpress_is_recurring']) : 0;
            if($bookingpress_is_recurring == 1){
                $bookingpress_is_group_order = true;
            }
            return $bookingpress_is_group_order;
        }

        /**
         * Function for remove disable date when timeslot in waiting
         *
         * @param  mixed $bookingpress_allow_to_disable_booked_date
         * @param  mixed $bookingpress_selected_service
         * @param  mixed $appointment_data_obj
         * @return void
         */
        function bookingpress_allow_to_disable_booked_date_fun($bookingpress_allow_to_disable_booked_date,$bookingpress_selected_service){
            global $bookingpress_services;                        
            $is_recurring_appointments = (isset($_POST['appointment_data_obj']['is_recurring_appointments']))?$_POST['appointment_data_obj']['is_recurring_appointments']:''; //phpcs:ignore
            if($is_recurring_appointments){
                $is_recurring_appointments = true;
            }            
            return $bookingpress_allow_to_disable_booked_date;
        }    

        /**
         * Add start day when recurring appointment add
         *
         * @param  mixed $bookingpress_add_single_disable_date
         * @param  mixed $bookingpress_selected_service
         * @param  mixed $front_timings
         * @return void
         */
        function bookingpress_add_single_disable_date_when_no_timeslot_fun($bookingpress_add_single_disable_date,$bookingpress_selected_service,$front_timings){
            $is_recurring_appointments = (isset($_POST['appointment_data_obj']['is_recurring_appointments']))?$_POST['appointment_data_obj']['is_recurring_appointments']:''; //phpcs:ignore
            if($is_recurring_appointments == 'true'){                
                $bookingpress_add_single_disable_date = true;
            }
            return $bookingpress_add_single_disable_date;
        }

        /**
         * Function for not allow refund for recurring appointment
         *
         * @param  mixed $bookingpress_is_not_allow_to_refund_check
         * @param  mixed $appointment_data
         * @return void
         */
        function bookingpress_is_not_allow_to_refund_check_func($bookingpress_is_not_allow_to_refund_check, $appointment_data){
			if(isset($appointment_data['bookingpress_is_recurring']) && $appointment_data['bookingpress_is_recurring'] == 1 ) {
				$bookingpress_is_not_allow_to_refund_check = true;
			}
            return $bookingpress_is_not_allow_to_refund_check;
        }
        
        /**
         * Function for add icon in my booking
         *
         * @return void
         */
        function bookingpress_my_booking_extra_icons_func(){
        ?>
            <el-tooltip content="<?php esc_html_e('Recurring Transaction', 'bookingpress-recurring-appointments'); ?>" placement="top" v-if="scope.row.bookingpress_is_recurring == '1'">											
                <svg width="15" height="15" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M0 8.53745C0 8.82364 0.113692 9.09812 0.316064 9.30049C0.518437 9.50287 0.792913 9.61656 1.07911 9.61656C1.3578 9.6082 1.62245 9.49231 1.81758 9.29315C2.01271 9.094 2.12318 8.82704 2.12585 8.54824C2.14747 7.32275 2.5165 6.12857 3.19003 5.10453C3.86355 4.0805 4.81391 3.26867 5.93062 2.76344C7.04733 2.25821 8.2845 2.08033 9.49831 2.25048C10.7121 2.42063 11.8527 2.93182 12.7875 3.72461H12.4421C12.3004 3.72461 12.1601 3.75252 12.0292 3.80675C11.8983 3.86098 11.7793 3.94047 11.6791 4.04068C11.5789 4.14088 11.4994 4.25984 11.4452 4.39076C11.3909 4.52169 11.363 4.66201 11.363 4.80372C11.363 4.94543 11.3909 5.08576 11.4452 5.21668C11.4994 5.3476 11.5789 5.46657 11.6791 5.56677C11.7793 5.66697 11.8983 5.74646 12.0292 5.80069C12.1601 5.85492 12.3004 5.88283 12.4421 5.88283H15.496C15.7822 5.88283 16.0567 5.76914 16.2591 5.56677C16.4614 5.3644 16.5751 5.08992 16.5751 4.80372V1.74984C16.5751 1.46364 16.4614 1.18917 16.2591 0.986793C16.0567 0.78442 15.7822 0.670729 15.496 0.670729C15.2098 0.670729 14.9354 0.78442 14.733 0.986793C14.5306 1.18917 14.4169 1.46364 14.4169 1.74984V2.22465C13.1834 1.11118 11.6547 0.377381 10.0144 0.11135C8.3741 -0.154681 6.69186 0.0583593 5.16965 0.724896C3.64744 1.39143 2.34995 2.48314 1.43294 3.86896C0.515929 5.25477 0.0183721 6.8758 0 8.53745ZM15.8521 9.30361C15.8521 9.01742 15.9658 8.74294 16.1682 8.54057C16.3706 8.3382 16.6451 8.2245 16.9312 8.2245C17.2137 8.22731 17.4838 8.34077 17.6836 8.54051C17.8833 8.74026 17.9968 9.01036 17.9996 9.29282C18.0174 10.4421 17.8029 11.5831 17.369 12.6475C16.9351 13.7119 16.2907 14.6777 15.4744 15.4869C13.9087 17.0448 11.8054 17.9432 9.59738 17.9974C7.38934 18.0516 5.24451 17.2574 3.60423 15.7783V16.2531C3.60423 16.5393 3.49054 16.8138 3.28817 17.0161C3.08579 17.2185 2.81132 17.3322 2.52512 17.3322C2.23892 17.3322 1.96444 17.2185 1.76207 17.0161C1.5597 16.8138 1.44601 16.5393 1.44601 16.2531V13.1992C1.44601 12.913 1.5597 12.6385 1.76207 12.4362C1.96444 12.2338 2.23892 12.1201 2.52512 12.1201H5.61138C5.75309 12.1201 5.89341 12.148 6.02433 12.2022C6.15526 12.2565 6.27422 12.336 6.37442 12.4362C6.47463 12.5364 6.55411 12.6553 6.60834 12.7862C6.66257 12.9172 6.69049 13.0575 6.69049 13.1992C6.69049 13.3409 6.66257 13.4812 6.60834 13.6122C6.55411 13.7431 6.47463 13.862 6.37442 13.9623C6.27422 14.0625 6.15526 14.1419 6.02433 14.1962C5.89341 14.2504 5.75309 14.2783 5.61138 14.2783H5.23369C6.17914 15.0658 7.32942 15.5676 8.54979 15.7249C9.77015 15.8822 11.0101 15.6885 12.1243 15.1665C13.2386 14.6444 14.181 13.8157 14.8412 12.7774C15.5014 11.739 15.8521 10.5341 15.8521 9.30361Z" fill="#727E95"/>
                </svg>                 
            </el-tooltip>            
        <?php 
        }

        /**
         * Function for modified recurring my booking data
         *
         * @param  mixed $bookingpress_appointments_data
         * @return void
         */
        function bookingpress_modify_my_appointments_data_for_recurring($bookingpress_appointments_data){            
            $bookingpress_is_recurring = (isset($bookingpress_appointments_data['bookingpress_is_recurring']))?$bookingpress_appointments_data['bookingpress_is_recurring']:'';
            if($bookingpress_is_recurring == 1){
                $bookingpress_appointments_data['appointment_refund_status'] = 0; 
            }                       
            return $bookingpress_appointments_data;
        }

        function bookingpress_add_appointment_model_reset_func(){
        ?>
			if(typeof vm2.appointment_formdata.selected_staffmember != "undefined"){
				vm2.appointment_formdata.selected_staffmember = '';
			}        
            if(typeof vm2.appointment_formdata.is_recurring_appointments !== 'undefined'){					                               
                vm2.appointment_formdata.is_recurring_appointments = false;
            }
            if(typeof vm2.recurring_form_data !== 'undefined'){					                   
                vm2.recurring_form_data.start_time = "";
                vm2.recurring_form_data.end_time = "";
                vm2.recurring_form_data.no_of_session = 0;
                vm2.recurring_form_data.start_date = "";
                vm2.recurring_form_data.formatted_start_time = "";
                vm2.recurring_form_data.formatted_end_time = "";                    
            }         
            if(typeof vm2.is_service_recurring_appointments_enable !== 'undefined'){
                vm2.is_service_recurring_appointments_enable = "0";
            }
            if(typeof vm2.recurring_appointments_max_no_of_times !== 'undefined'){
                vm2.recurring_appointments_max_no_of_times = 0;
            }                        
            if(typeof vm2.appointment_formdata.recurring_appointments !== 'undefined'){
                vm2.appointment_formdata.recurring_appointments = [];                    
            }                                                                     
        <?php 
        }

        /**
         * Function for add recurring backend appointment data
         *
         * @param  mixed $response
         * @param  mixed $bookingpress_appointment_data
         * @param  mixed $appointment_id
         * @return void
         */
        function bookingpress_add_backend_recurring_appointment_func($response,$bookingpress_appointment_data, $bookingpress_entry_details){

            global $wpdb,$tbl_bookingpress_entries, $bookingpress_other_debug_log_id, $bookingpress_debug_payment_log_id,$bookingpress_pro_payment_gateways;
            $is_recurring_appointments = (isset($bookingpress_appointment_data['is_recurring_appointments']))?$bookingpress_appointment_data['is_recurring_appointments']:'';
            if($is_recurring_appointments == 'true'){                
                
                $recurring_appointments = (isset($bookingpress_appointment_data['recurring_appointments']))?$bookingpress_appointment_data['recurring_appointments']:array();
                if(empty($recurring_appointments)){
                    $response['msg'] = esc_html__('Please add recurring appointment data', 'bookingpress-recurring-appointments');
                    return $response;
                }else{
                    $is_not_avaliable_appointment = false;
                    foreach($recurring_appointments as $rec_appointment){
                        if($rec_appointment['is_not_avaliable'] == 1 || $rec_appointment['is_not_avaliable'] == '1'){
                            $is_not_avaliable_appointment = true;
                        }
                    }
                    if($is_not_avaliable_appointment){
                        $response['msg'] = esc_html__('Please select time slot for not available time slot.', 'bookingpress-recurring-appointments');
                        return $response;                        
                    }
                }

                if(!empty($bookingpress_entry_details)){

                    $bookingpress_cart_order_id = get_option('bookingpress_cart_order_id', true);
                    if(empty($bookingpress_cart_order_id)){
                        $bookingpress_cart_order_id = 1;
                    }else{
                        $bookingpress_cart_order_id = floatval($bookingpress_cart_order_id) + 1;
                    }
                    update_option('bookingpress_cart_order_id', $bookingpress_cart_order_id);

                    $bookingpress_entry_details['bookingpress_is_recurring'] = 1;
                    $bookingpress_entry_details['bookingpress_order_id'] = $bookingpress_cart_order_id;

                    foreach($recurring_appointments as $rec_index=>$recurring_app){

                        $bookingpress_entry_details['bookingpress_appointment_time'] = date('H:i:s', strtotime($recurring_app['selected_start_time']));
                        $bookingpress_entry_details['bookingpress_appointment_end_time'] = date('H:i:s', strtotime($recurring_app['selected_end_time'])); 
                        if($bookingpress_entry_details['bookingpress_service_duration_val'] == 24 && $bookingpress_entry_details['bookingpress_service_duration_unit'] == 'h'){
                            $bookingpress_entry_details['bookingpress_appointment_end_time'] = '24:00:00';    
                        }
                        $bookingpress_entry_details['bookingpress_appointment_date'] =  $recurring_app['selected_date'];                                                                        
                        $bookingpress_entry_details = apply_filters('bookingpress_modify_backend_add_appointment_entry_data', $bookingpress_entry_details, $bookingpress_appointment_data);

                        do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Backend add appointment data', 'bookingpress_admin_add_update_appointment', $bookingpress_entry_details, $bookingpress_other_debug_log_id );
                        do_action('bookingpress_payment_log_entry', 'manual', 'submit appointment form backend', 'bookingpress', $bookingpress_entry_details, $bookingpress_debug_payment_log_id);                        
                        $wpdb->insert( $tbl_bookingpress_entries, $bookingpress_entry_details );
                        $entry_id = $wpdb->insert_id;

                        do_action('bookingpress_after_insert_entry_data_from_backend', $entry_id, $bookingpress_appointment_data);

                    }                    

                    $payment_log_id = $bookingpress_pro_payment_gateways->bookingpress_confirm_booking($bookingpress_cart_order_id, array(), '1', '', '', 2,1);

                    $response['variant'] = 'success';
                    $response['title']   = esc_html__('Success', 'bookingpress-recurring-appointments');
                    $response['msg']     = esc_html__('Appointment has been booked successfully.', 'bookingpress-recurring-appointments');                    

                }
            }
            return $response;
        }


        /**
         * function for modified date & time recurring data
         *
         * @param  mixed $appointment_data
         * @param  mixed $appointment_id
         * @return void
         */
        function bookingpress_modify_recurring_datetime_shortcode_data($appointment_data, $appointment_id)
        {
            if(!empty($appointment_id) ){

                global $wpdb, $tbl_bookingpress_appointment_bookings;
                $bpa_rec_appointment_data = wp_cache_get( 'bookingpress_record_count_rec_order_'.$appointment_id );
                if( false == $bpa_rec_appointment_data ){
                    $bpa_rec_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT COUNT(bookingpress_appointment_booking_id) as total_rec FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_order_id = %d AND bookingpress_is_recurring = %d", $appointment_id,1), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
                    wp_cache_set( 'bookingpress_record_count_rec_order_'.$appointment_id , $bpa_rec_appointment_data);
                }
                if($bpa_rec_appointment_data['total_rec'] > 1){
                    $bpa_rec_appointment_data = wp_cache_get( 'bookingpress_appointments_records_rec_order_'.$appointment_id );
					if( false == $bpa_rec_appointment_data ){
						$bpa_rec_appointment_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_order_id = %d AND bookingpress_is_recurring = %d", $appointment_id,1), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
						wp_cache_set( 'bookingpress_appointments_records_rec_order_'.$appointment_id , $bpa_rec_appointment_data);
					}
                }
                else{
                    $bpa_rec_appointment_data = wp_cache_get( 'bookingpress_records_rec_order_'.$appointment_id );
                    if( false == $bpa_rec_appointment_data ){
                        $bpa_rec_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_order_id = %d AND bookingpress_is_recurring = %d", $appointment_id,1), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
                        wp_cache_set( 'bookingpress_records_rec_order_'.$appointment_id , $bpa_rec_appointment_data);
                    }
                }
                $appointment_data = !empty($bpa_rec_appointment_data) ? $bpa_rec_appointment_data : $appointment_data;
            }
            return $appointment_data;
        }        
        /**
         * thankyou service shortcode data modified
         *
         * @param  mixed $appointment_data
         * @param  mixed $appointment_id
         * @return void
         */
        function bookingpress_modify_recurring_service_shortcode_details($appointment_data, $appointment_id){
            if(!empty($appointment_id) ){

                global $wpdb, $tbl_bookingpress_appointment_bookings;
                $bpa_rec_appointment_data = wp_cache_get( 'bookingpress_record_count_rec_order_'.$appointment_id );
                if( false == $bpa_rec_appointment_data ){
                    $bpa_rec_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT COUNT(bookingpress_appointment_booking_id) as total_rec FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_order_id = %d AND bookingpress_is_recurring = %d", $appointment_id,1), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
                    wp_cache_set( 'bookingpress_record_count_rec_order_'.$appointment_id , $bpa_rec_appointment_data);
                }
                if($bpa_rec_appointment_data['total_rec'] > 1){
                    $bpa_rec_appointment_data = wp_cache_get( 'bookingpress_appointments_records_rec_order_'.$appointment_id );
					if( false == $bpa_rec_appointment_data ){
						$bpa_rec_appointment_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_order_id = %d AND bookingpress_is_recurring = %d", $appointment_id,1), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
						wp_cache_set( 'bookingpress_appointments_records_rec_order_'.$appointment_id , $bpa_rec_appointment_data);
					}
                }
                else{
                    $bpa_rec_appointment_data = wp_cache_get( 'bookingpress_records_rec_order_'.$appointment_id );
                    if( false == $bpa_rec_appointment_data ){
                        $bpa_rec_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_order_id = %d AND bookingpress_is_recurring = %d", $appointment_id,1), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
                        wp_cache_set( 'bookingpress_records_rec_order_'.$appointment_id , $bpa_rec_appointment_data);
                    }
                }
                if(isset($bpa_rec_appointment_data[0])) { 
                    $appointment_data = !empty($bpa_rec_appointment_data) ? $bpa_rec_appointment_data[0] : $appointment_data;
                }
            }
            return $appointment_data;
        }        
        /**
         * modified customer detail shortcode
         *
         * @param  mixed $appointment_data
         * @param  mixed $appointment_id
         * @return void
         */
        function bookingpress_modify_rec_customer_details_shortcode_data_func($appointment_data, $appointment_id)
        {
            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings;
            if(!empty($appointment_id)){
				
                $bpa_rec_appointment_data = wp_cache_get( 'bookingpress_records_rec_order_'.$appointment_id );
					if( false == $bpa_rec_appointment_data ){
						$bpa_rec_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_order_id = %d AND bookingpress_is_recurring = %d", $appointment_id,1), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
						wp_cache_set( 'bookingpress_records_rec_order_'.$appointment_id , $bpa_rec_appointment_data);
					}

				$appointment_data = !empty($bpa_rec_appointment_data) ? $bpa_rec_appointment_data : $appointment_data;
			} 
            return $appointment_data;
        }

        /**
         * bookingpress_modify_payments_listing_data -function for the manage payment page appointment date
         *
         * @param  mixed $payment_logs_data
         * @return void
         */
        function bookingpress_modify_payments_listing_data($payment_logs_data)
        {   
            if(!empty($payment_logs_data) && is_array($payment_logs_data) ){
                global $bookingpress_pro_payment;
                foreach($payment_logs_data as $k => $v){
					$payment_log_id = $v['payment_log_id'];
                    $payment_log_details = $bookingpress_pro_payment->bookingpress_calculate_payment_details($payment_log_id);
                    if($v['bookingpress_is_recurring'] == '1'){
						$payment_logs_data[$k]['appointment_date'] = ' - ';
					}
                }
            }
            return $payment_logs_data;
        } 

       
        /**
	    * Function for add recurring total amount in payment record
	    *
	    * @param  mixed $bookingpress_multiple_appointment_payment_order_detail
	    * @param  mixed $entry_details
	    * @return void
	    */
	   function bookingpress_multiple_appointment_payment_order_detail_func($bookingpress_multiple_appointment_payment_order_detail, $entry_details){
            $bookingpress_is_recurring = (isset($entry_details['bookingpress_is_recurring']))?$entry_details['bookingpress_is_recurring']:'';
            if($bookingpress_is_recurring == 1){
                $bookingpress_multiple_appointment_payment_order_detail = true;
            }
            return $bookingpress_multiple_appointment_payment_order_detail;
       }
	   
        /**
         * bookingpress_recurring_modify_email_content_filter_func- Send list of recurring appointment in email notification
         *
         * @param  mixed $template_content
         * @param  mixed $bookingpress_appointment_data
         * @return void
         */
        function bookingpress_recurring_modify_email_content_filter_func($template_content, $bookingpress_appointment_data)
        {
            global $tbl_bookingpress_appointment_bookings, $wpdb, $bookingpress_global_options;
            
            $bookingpress_order_id = isset($bookingpress_appointment_data['bookingpress_order_id']) ? $bookingpress_appointment_data['bookingpress_order_id'] : $bookingpress_appointment_data['bookingpress_order_id'];
            $bookingpress_is_recurring = isset($bookingpress_appointment_data['bookingpress_is_recurring']) ? $bookingpress_appointment_data['bookingpress_is_recurring'] : $bookingpress_appointment_data['bookingpress_is_recurring'];
            $bookingpress_rec_appointments = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_order_id =%d",$bookingpress_order_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
            $bookingpress_recurring_appointment_list = '';
            $bookingpress_global_options_data = $bookingpress_global_options->bookingpress_global_options();
            $default_time_format = $bookingpress_global_options_data['wp_default_time_format'];
            $bookingpress_date_format = $bookingpress_global_options_data['wp_default_date_format'];
            if(!empty($bookingpress_rec_appointments) && $bookingpress_is_recurring==1) {
                foreach($bookingpress_rec_appointments as $bookingpress_rec_appointments_key => $bookingpress_rec_appointments_data){
                    $is_recurring_appointment = isset($bookingpress_rec_appointments_data['bookingpress_is_recurring']) ? $bookingpress_rec_appointments_data['bookingpress_is_recurring'] : '';
                    if($is_recurring_appointment==1) {
                        $bookingpress_service_name = isset($bookingpress_rec_appointments_data['bookingpress_service_name']) && !empty($bookingpress_rec_appointments_data['bookingpress_service_name']) ? $bookingpress_rec_appointments_data['bookingpress_service_name'] : '';
                        $bookingpress_appointment_date = isset($bookingpress_rec_appointments_data['bookingpress_appointment_date']) ? $bookingpress_rec_appointments_data['bookingpress_appointment_date'] : '';
                        $bookingpress_appointment_start_time = isset($bookingpress_rec_appointments_data['bookingpress_appointment_time']) && !empty($bookingpress_rec_appointments_data['bookingpress_appointment_time']) ? $bookingpress_rec_appointments_data['bookingpress_appointment_time'] : '';
                        $bookingpress_appointment_end_time = isset($bookingpress_rec_appointments_data['bookingpress_appointment_end_time']) && !empty($bookingpress_rec_appointments_data['bookingpress_appointment_end_time']) ? $bookingpress_rec_appointments_data['bookingpress_appointment_end_time'] : '';
                        $bookingpress_staff_first_name = isset($bookingpress_rec_appointments_data['bookingpress_staff_first_name']) && !empty($bookingpress_rec_appointments_data['bookingpress_staff_first_name']) ? $bookingpress_rec_appointments_data['bookingpress_staff_first_name'] : '';
                        $bookingpress_staff_last_name = isset($bookingpress_rec_appointments_data['bookingpress_staff_last_name']) && !empty($bookingpress_rec_appointments_data['bookingpress_staff_last_name']) ? $bookingpress_rec_appointments_data['bookingpress_staff_last_name'] : '';
                        $bookingpress_appointment_start_time = date($default_time_format, strtotime($bookingpress_appointment_start_time));
                        $bookingpress_appointment_end_time   = date($default_time_format, strtotime($bookingpress_appointment_end_time));
                        $bookingpress_appointment_date =  date($bookingpress_date_format,strtotime($bookingpress_appointment_date));

                        $at =  esc_html__( 'at', 'bookingpress-recurring-appointments' );
                        $on =  esc_html__( 'on', 'bookingpress-recurring-appointments' );
                        $by =  esc_html__( 'by', 'bookingpress-recurring-appointments' );
                        $to =  esc_html__( 'to', 'bookingpress-recurring-appointments' );
                        if(!empty($bookingpress_staff_first_name) || !empty($bookingpress_staff_last_name)){
                            $bookingpress_recurring_appointment_list .= $bookingpress_service_name.' ';
                            $bookingpress_recurring_appointment_list .= $by.' '.$bookingpress_staff_first_name.' '.$bookingpress_staff_last_name.' ';
                            $bookingpress_recurring_appointment_list .= $at.' '.$bookingpress_appointment_start_time.' '.$bookingpress_appointment_end_time.' ';
                            $bookingpress_recurring_appointment_list .= $on.' '.$bookingpress_appointment_date.'<br>';
                        }
                        else {
                            $bookingpress_recurring_appointment_list .= $bookingpress_service_name.' '.$at.' '.$bookingpress_appointment_start_time.' '.$to.' '.$bookingpress_appointment_end_time.' '.$on.' '.$bookingpress_appointment_date.'<br>';
                        }
                    }
                }
            }
            $template_content  = str_replace( '%recurring_appointment_list%', $bookingpress_recurring_appointment_list, $template_content ); 
            return $template_content;
        }	   
	   
                
        /**
         * Add Appointment after select service
         *
         * @return void
         */
        function bookingpress_change_backend_service_func(){
        ?>
            vm.appointment_formdata.recurring_appointments = [];
            vm.appointment_formdata.is_recurring_appointments = false;            
            vm.appointment_services_list.forEach(function(currentValue, index, arr){
                if(currentValue.category_services.length > 0){
                    currentValue.category_services.forEach(function(currentValue2, index2, arr2){
                      if(vm.appointment_formdata.appointment_update_id == 0){                        
                        if(currentValue2.service_id == vm.appointment_formdata.appointment_selected_service && currentValue2.service_duration_unit != 'd'){

                            if(currentValue2.enable_recurring_appointments == true){
                                vm.is_service_recurring_appointments_enable = "1";
                                vm.bookingpress_recurring_frequencies = currentValue2.recurring_frequencies;
                                vm.recurring_appointments_max_no_of_times = parseInt(currentValue2.recurring_appointments_max_no_of_times);
                                vm.recurring_form_data.recurring_frequency = currentValue2.default_recurring_frequencies;
                            }else{
                                vm.is_service_recurring_appointments_enable = "0";
                                vm.recurring_appointments_max_no_of_times = 0;
                                vm.appointment_formdata.is_recurring_appointments = false;
                                vm.recurring_form_data.recurring_frequency = "";
                            }

                        }else{
                            
                            if(currentValue2.service_id == vm.appointment_formdata.appointment_selected_service && currentValue2.service_duration_unit == 'd'){
                                vm.is_service_recurring_appointments_enable = "0";
                                vm.recurring_appointments_max_no_of_times = 0;
                                vm.appointment_formdata.is_recurring_appointments = false;
                                vm.recurring_form_data.recurring_frequency = "";
                            }

                        }

                      }  
                    });
                }
            });
        <?php 
        }

        /**
         * Function for add happy hour price in admin side when timeslot select
         *
         * @return void
         */
        function bookingpress_admin_add_appointment_after_select_timeslot_fun(){
        ?>                      
            vm.recurring_form_data.formatted_start_time = data_arr.formatted_start_time;
            vm.recurring_form_data.formatted_end_time = data_arr.formatted_end_time;
            vm.bookingpress_check_for_recurring_appointment();
        <?php
        }
                
        /**
         * Function for add appointment vue method
         *
         * @return void
         */
        function bookingpress_appointment_add_dynamic_vue_methods_func(){
            $bookingpress_nonce = wp_create_nonce('bpa_wp_nonce');
        ?>
        bookingpress_backend_recurring_appointment_form_change(){
            var vm = this;
            vm.bookingpress_check_for_recurring_appointment();
        },
        bookingpress_backend_change_no_of_recurring(){
            var vm = this;
            if(vm.recurring_form_data.no_of_session > 0){
                vm.appointment_formdata.is_recurring_appointments = true;
            }else{
                vm.appointment_formdata.is_recurring_appointments = false;
                vm.appointment_formdata.recurring_appointments = [];
            }
        },
        bookingpress_check_for_recurring_appointment(){
            var vm = this;
                                               
            if(vm.appointment_formdata.is_recurring_appointments != false){                
                if(vm.appointment_formdata.appointment_booked_date != ""){
                    vm.recurring_form_data.start_date = vm.appointment_formdata.appointment_booked_date;
                }
                if(vm.appointment_formdata.appointment_booked_time != ""){                    
                    vm.recurring_form_data.start_time = vm.appointment_formdata.appointment_booked_time;
                    vm.recurring_form_data.end_time = vm.appointment_formdata.appointment_booked_end_time;
                }else{
                    vm.recurring_form_data.start_time = "";
                    vm.recurring_form_data.end_time = "";
                }
                if(vm.recurring_form_data.no_of_session > 0 && vm.recurring_form_data.recurring_frequency != ""){
                    vm.bookingpress_backend_recurring_appointment_get();                    
                }else{
                    vm.appointment_formdata.recurring_appointments = [];
                }
            }
            vm.bookingpress_admin_get_final_step_amount();
        },
        bookingpress_backend_recurring_appointment_get(){
            
            const vm = this;                
            const CustformData = new FormData();
            var selected_service_id = vm.appointment_formdata.appointment_selected_service;
            var selected_date = vm.recurring_form_data.start_date;
            /* vm.close_recurring_modal(); */

            let bookingpress_appointment_form_data = vm.appointment_formdata;
            var bookingpress_service_expiration_date = "";
            
            var postData = { action:"bookingpress_get_recurring_appointments", service_id: selected_service_id, selected_date: selected_date, _wpnonce:"<?php echo  esc_html($bookingpress_nonce); ?>", };
            <?php //do_action( 'bookingpress_set_additional_appointment_xhr_data' ) ?>
            vm.appointment_formdata.recurring_edit_index = "";            
            postData.bookingpress_service_expiration_date = bookingpress_service_expiration_date; 
            postData.appointment_data_obj = JSON.stringify(vm.appointment_formdata);
            postData.recurring_form_data = JSON.stringify(vm.recurring_form_data);                                    
            axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
            .then( function (response) {
                if(response.data.variant == "success"){
                    vm.appointment_formdata.recurring_appointments = response.data.recurring_appointments;                                                           
                    if(response.data.bookingpress_recurring_form_token){
                        //vm.appointment_formdata.bookingpress_recurring_form_token = response.data.bookingpress_recurring_form_token;
                    }                                        
                    if("undefined" != typeof vm.appointment_formdata.happy_hour_data){
                        vm.appointment_formdata.happy_hour_data = [];
                    }
                    vm.bookingpress_admin_get_final_step_amount();                            
                }else{                                        
                    //vm.bookingpress_set_error_msg(response.data.msg);
                    vm.appointment_formdata.recurring_appointments = [];
                    vm.bookingpress_admin_get_final_step_amount();
                }
                vm.recurring_appointment_loader = false;
            }.bind(this) )
            .catch( function (error) {
                console.log(error);
                vm.recurring_appointment_loader = false;
                vm.appointment_formdata.recurring_appointments = [];
                vm.bookingpress_admin_get_final_step_amount();
            });

        },
        open_recurring_modal_backend(currentElement,recurringEditIndex,device=""){

            const vm = this;
            vm.close_recurring_modal_backend();
            vm.recurring_edit_appointment_time_slot = [];
            vm.appointment_formdata.recurring_edit_index = recurringEditIndex;
            var dialog_pos = currentElement.target.getBoundingClientRect();
            vm.extra_service_modal_pos = (dialog_pos.top - 90)+"px";
            vm.extra_service_modal_pos_right = "-"+(dialog_pos.right - 430)+"px";

            vm.recurring_open_edit_popup = true;
            vm.recurring_appointment_loader_edit = true;

            if(vm.appointment_formdata.recurring_appointments[recurringEditIndex].is_not_avaliable == 0){
                vm.recurring_edit_time = vm.appointment_formdata.recurring_appointments[recurringEditIndex].selected_start_time;
                vm.recurring_edit_date = vm.appointment_formdata.recurring_appointments[recurringEditIndex].selected_date;
            }else{
                vm.recurring_edit_date = "";
                vm.recurring_edit_time = "";
            }
            /* disable not popup start */
            vm.bookingpress_appointment_get_disable_dates();
            if( typeof vm.bpa_adjust_popup_position != "undefined" ){
                vm.bpa_adjust_popup_position( currentElement, "div#recurring_front_modal .el-dialog.bpa-dialog--add-recurring-edit");
            }
            /* disable not popup over */
            vm.bookingpress_reset_default_date_time_data();

        }, 
        bookingpress_reset_default_date_time_data(){
            const vm = this;            
            vm.appointment_formdata.appointment_booked_date = vm.recurring_form_data.start_date;                       
            vm.appointment_formdata.appointment_booked_time = vm.recurring_form_data.start_time;
            vm.appointment_time_slot = vm.appointment_time_slot_old;
        },
        close_recurring_modal_backend(){
            const vm = this;
            vm.recurring_open_edit_popup = false;
            vm.appointment_formdata.recurring_edit_index = "";
            vm.recurring_edit_appointment_time_slot = [];
            vm.bookingpress_reset_default_date_time_data();
            vm.bookingpress_admin_get_final_step_amount();
        },
        change_recurring_start_date_backend(selected_value,type){            
            const vm = this;
            vm.recurring_edit_time = "";
            vm.recurring_edit_appointment_time_slot = [];
            vm.recurring_appointment_time_loader_edit = true;
            vm.select_appointment_booking_date( selected_value );
            vm.bookingpress_reset_default_date_time_data();  

        },
        bookingpress_set_recurring_start_time(){
            const vm = this;

        },
        save_edit_recurring_data_backend(){
            const vm = this;
            if(vm.appointment_formdata.recurring_edit_index != "" || vm.appointment_formdata.recurring_edit_index == 0){                
                var recurringEditIndex = vm.appointment_formdata.recurring_edit_index;
                var single_appointment_data = vm.appointment_formdata.recurring_appointments[recurringEditIndex];

                for (let x in vm.recurring_edit_appointment_time_slot) {                    
                    if(vm.recurring_edit_date != "" && typeof vm.recurring_edit_appointment_time_slot[x] != "undefined"){

                        var slot_data_arr = vm.recurring_edit_appointment_time_slot[x];                       
                        for(let y in slot_data_arr) {
                            var time_slot_data_arr = slot_data_arr[y];
                            for(let m in time_slot_data_arr) {                            
                                var data_arr  = time_slot_data_arr[m];
                                if(data_arr.store_start_time != undefined && data_arr.store_end_time != undefined && data_arr.store_start_time == vm.recurring_edit_time){
                                    vm.appointment_formdata.recurring_appointments[recurringEditIndex].formated_end_time = data_arr.formatted_end_time;
                                    vm.appointment_formdata.recurring_appointments[recurringEditIndex].formated_select_date = data_arr.store_service_date;
                                    vm.appointment_formdata.recurring_appointments[recurringEditIndex].formated_start_time = data_arr.formatted_start_time;
                                    vm.appointment_formdata.recurring_appointments[recurringEditIndex].selected_date = data_arr.store_service_date;
                                    vm.appointment_formdata.recurring_appointments[recurringEditIndex].selected_end_time = data_arr.store_end_time;
                                    vm.appointment_formdata.recurring_appointments[recurringEditIndex].selected_start_time = data_arr.store_start_time;
                                    if(vm.bookigpress_time_format_for_booking_form == "1" || vm.bookigpress_time_format_for_booking_form == "2")
                                    {
                                        vm.appointment_formdata.recurring_appointments[recurringEditIndex].display_formated_date_and_time = data_arr.formatted_start_time+" "+to_text_data+" "+data_arr.formatted_end_time;
                                    }    
                                    else if(vm.bookigpress_time_format_for_booking_form == "5" || vm.bookigpress_time_format_for_booking_form == "6"){
                                        vm.appointment_formdata.recurring_appointments[recurringEditIndex].display_formated_date_and_time = data_arr.formatted_start_time+" "+"-"+" "+data_arr.formatted_end_time;
                                    }
                                    else if(vm.bookigpress_time_format_for_booking_form == "3" || vm.bookigpress_time_format_for_booking_form == "4"){
                                        vm.appointment_formdata.recurring_appointments[recurringEditIndex].display_formated_date_and_time = data_arr.formatted_start_time;
                                    }
                                    else if(vm.bookigpress_time_format_for_booking_form == "5" || vm.bookigpress_time_format_for_booking_form == "6"){
                                        vm.appointment_formdata.recurring_appointments[recurringEditIndex].display_formated_date_and_time = data_arr.formatted_start_time+" "+"-"+" "+data_arr.formatted_end_time;
                                    }
                                    vm.appointment_formdata.recurring_appointments[recurringEditIndex].is_suggested = 0;
                                    vm.appointment_formdata.recurring_appointments[recurringEditIndex].is_not_avaliable = 0;
                                }
                            }                                                    
                        }

                    }

                }
            }
            vm.close_recurring_modal_backend();
            vm.bookingpress_admin_get_final_step_amount();
        },                 
        bookingpress_backend_calculate_recurring_appointment_total(){
            const vm = this;
        },
        bookingpress_recurring_appointment_count(rindex){
            return parseInt(rindex)+1+") ";
        },
        bookingpress_recurring_appointment_list_model(currentElement,bookingpress_order_id){
            const vm = this;
            vm.bookingpress_recurring_appointment_list_popup_loader = true;
            vm.bookingpress_recurring_appointment_list_popup = '';
            vm.bookingpress_is_recurring_appointment_list_model = true;          
            bpa_order_id = bookingpress_order_id;
            var appointment_generate_url_details = {
                action:'bookingpress_get_recurring_appointment_list',
                order_id: bpa_order_id,
                _wpnonce: '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>'
            }				
            axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( appointment_generate_url_details ) )
            .then(function(response) {  
                vm.bookingpress_recurring_appointment_list_popup_loader = false;	                	
                vm.bookingpress_recurring_appointment_list_popup = response.data;
            }).catch(function(error){
                vm.bookingpress_recurring_appointment_list_popup_loader = false;
                console.log(error);
                vm.$notify({
                    title: '<?php esc_html_e( 'Error', 'bookingpress-recurring-appointments' ); ?>',
                    message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-recurring-appointments' ); ?>',
                    type: 'error',
                    customClass: 'error_notification',
                });
            });
        },
        close_rec_list_appointment_model(){
            const vm=this;
            vm.bookingpress_is_recurring_appointment_list_model= false;
        },
        <?php 
        }
        
        /**
         * Backend calculate total for recurring appointment
         *
         * @return void
         */
        function bookingpress_admin_calculate_total_after_add_recurring_appointment_price_fun(){
        ?>
            const vm8 = this;
            var bookingpress_recurring_total = 0;
            if(vm.appointment_formdata.is_recurring_appointments == true){
                var all_recurring_appointments = vm8.appointment_formdata.recurring_appointments;
                if(all_recurring_appointments != '' && all_recurring_appointments.length != 0){
                    var total_recurring_appointment = 0;
                    if(all_recurring_appointments.length > 0){
                        all_recurring_appointments.forEach(function(item, index, arr){					
                            if(item.is_not_avaliable == 0){						
                                total_recurring_appointment = total_recurring_appointment+1; 
                            }					
                        });
                    }                      
                    if(vm.is_extras_enable){
                        if(parseInt(vm.appointment_formdata.extras_total) > 0){
                            total_amount = total_amount - parseFloat(vm.appointment_formdata.extras_total);
                            var recurring_service_total_amount = total_amount * total_recurring_appointment;
                            var recurring_service_extra_total_amount = parseFloat(vm.appointment_formdata.extras_total) * total_recurring_appointment;
                            
                            total_amount = recurring_service_total_amount + recurring_service_extra_total_amount;
                            subtotal_price = recurring_service_total_amount;
                            vm.appointment_formdata.extras_total = recurring_service_extra_total_amount;
					        vm.appointment_formdata.extras_total_with_currency = vm.bookingpress_price_with_currency_symbol(recurring_service_extra_total_amount);

                        }else{
                            total_amount = total_amount * total_recurring_appointment;
                            subtotal_price = total_amount;  
                        }
                    }else{
                        total_amount = total_amount * total_recurring_appointment;                        
                        subtotal_price = total_amount;                        
                    }
                    vm.appointment_formdata.subtotal_with_currency = vm.bookingpress_price_with_currency_symbol( subtotal_price );
        		    vm.appointment_formdata.subtotal = subtotal_price;

                    vm.appointment_formdata.bookingpress_recurring_total = total_amount;
                    vm.appointment_formdata.bookingpress_recurring_total_with_currency = vm.bookingpress_price_with_currency_symbol(total_amount);

                }else{

                }
            }
        <?php 
        }

        /*
        function bookingpress_add_appointment_field_section_func(){
            
        }
        */

        /**
         * Function for add recurring appointment html whrn add appointment in backend
         *
         * @return void
         */
        function bookingpress_add_appointment_new_row_section_func(){
            global $bookingpress_common_date_format;
        ?>
        <div class="bpa-form-row" v-if="appointment_formdata.appointment_selected_service != '' && is_service_recurring_appointments_enable == '1' && is_service_recurring_appointments_enable != ''">
			<el-row>
                <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
					<div class="bpa-db-sec-heading">
						<el-row type="flex" align="middle">
							<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
								<div class="db-sec-left">
									<h2 class="bpa-page-heading"><?php esc_html_e( 'Repeat Appointment', 'bookingpress-recurring-appointments' ); ?></h2>
								</div>
							</el-col>
						</el-row>
					</div>
					<div class="bpa-default-card bpa-db-card">
                        <el-form label-position="top" :model="recurring_form_data" ref="appointment_formdata_custom_recurring" @submit.native.prevent>
							<template>						                                
                                <div class="bpa-form-body-row">
                                    <el-row :gutter="34" class="bpa-repeat-appointment">                                        
                                        <el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8" class="bpa-repeat-appointment-col">
                                            <el-form-item prop="no_of_session">
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e( 'No Of Sessions', 'bookingpress-recurring-appointments' ); ?></span>
                                                </template>
                                                <el-select :disabled="(appointment_formdata.appointment_booked_time == '')?true:false" @change="[bookingpress_backend_change_no_of_recurring(),bookingpress_backend_recurring_appointment_form_change()]" v-model="recurring_form_data.no_of_session"  class="bpa-form-control" popper-class="bpa-el-select--is-with-modal">
                                                    <el-option v-for="(nper, keys) in bookingpress_no_of_recurring" v-if="nper.value <= recurring_appointments_max_no_of_times" :label="nper.label" :value="nper.value">
                                                        <span>{{nper.label}}</span>
                                                    </el-option>
                                                </el-select>
                                            </el-form-item>
                                        </el-col>
                                        <el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8" class="bpa-repeat-appointment-col">
                                            <el-form-item>
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e( 'Frequency', 'bookingpress-recurring-appointments' ); ?></span>
                                                </template>
                                                <el-select :disabled="(appointment_formdata.appointment_booked_time == '')?true:false" @change="bookingpress_backend_recurring_appointment_form_change" v-model="recurring_form_data.recurring_frequency" class="bpa-form-control" popper-class="bpa-el-select--is-with-modal">
                                                    <el-option v-for="(recval, reckey) in all_recurring_frequencies" v-if="bookingpress_recurring_frequencies.includes(recval.value)" :label="recval.text" :value="recval.value">
                                                        <span>{{recval.text}}</span>
                                                    </el-option>
                                                </el-select>                                                
                                            </el-form-item>                                              
                                        </el-col>
                                    </el-row>
                                    <div class="bpa-recurring-appointment-data bpa-recurring-appointment-body">
                                        <div v-if="recurring_appointment_loader == true" class="bpa-recurring-appointment-body-loader">
                                            <div class="bpa-recurring-appointment-loader">
                                                <div class="bpa-back-loader-container">
                                                    <div class="bpa-back-loader"></div>
                                                </div>                                        
                                            </div>                                    
                                        </div>                                        
                                        <div v-if="(recurring_appointment_loader == false && appointment_formdata.recurring_appointments != '' && appointment_formdata.recurring_appointments.length != 0)">
                                            <label class="bpa-upcoming-appointments-label bpa-front-module-heading"><?php esc_html_e( 'Upcoming Appointments', 'bookingpress-recurring-appointments' ); ?></label>
                                            <el-row :gutter="16" class="bpa-recurring-appointment-body-content">
                                                <el-col v-for="(recurring_item, rkey) in appointment_formdata.recurring_appointments" :class="[((recurring_item.is_suggested == 1)?'bpa-upcomming-suggested':''),((recurring_item.is_not_avaliable == 1)?'bpa-upcomming-notavaliable':'')]" class="bpa-upcomming-appointments" :xs="24" :sm="12" :md="8" :lg="8" :xl="8">
                                                    <div class="bpa-lspd__item">
                                                        <div class="bpa-lspd__item-val">
                                                            <div class="bpa-hh-item__date-col bpa-hh-item__date-col-date">
                                                                <span class="bpa-front-tm--item-icon material-icons-round"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"></path><path d="M19 4h-1V3c0-.55-.45-1-1-1s-1 .45-1 1v1H8V3c0-.55-.45-1-1-1s-1 .45-1 1v1H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 15c0 .55-.45 1-1 1H6c-.55 0-1-.45-1-1V9h14v10zM7 11h2v2H7zm4 0h2v2h-2zm4 0h2v2h-2z"></path></svg></span>
                                                                <span>{{recurring_item.selected_date | bookingpress_format_date}}</span>
                                                            </div>                                    
                                                            <div class="bpa-hh-item-info-col">                                       
                                                                <p>{{recurring_item.display_formated_date_and_time}}</p>
                                                            </div>                                     
                                                        </div>
                                                        <div class="bpa-card__item">                                       
                                                            <el-tooltip effect="dark" content="" placement="top" open-delay="300">
                                                                <div slot="content">
                                                                    <span><?php esc_html_e( 'Edit', 'bookingpress-recurring-appointments' ); ?></span>
                                                                </div>
                                                                <el-button @click="open_recurring_modal_backend(event,rkey,'')" class="bpa-btn bpa-btn--icon-without-box">
                                                                    <span class="material-icons-round">mode_edit</span>
                                                                </el-button>
                                                            </el-tooltip>	
                                                        </div>
                                                        <div v-if="(recurring_item.is_suggested == 1 || recurring_item.is_not_avaliable == 1)" :class="[((recurring_item.is_suggested == 1)?'bpa-recurring-msg-suggested':''),((recurring_item.is_not_avaliable == 1)?'bpa-recurring-msg-notavaliable':'')]" class="bpa-recurring-msg">
                                                            <span><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 7c.55 0 1 .45 1 1v4c0 .55-.45 1-1 1s-1-.45-1-1V8c0-.55.45-1 1-1zm-.01-5C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm1-3h-2v-2h2v2z"></path></svg></span><span v-if="(recurring_item.is_not_avaliable == 1)"><?php esc_html_e('Selected or suggested time slot not available.', 'bookingpress-recurring-appointments'); ?></span> <span v-if="(recurring_item.is_suggested == 1)"><?php esc_html_e('Suggested time slot due to unavailability.', 'bookingpress-recurring-appointments'); ?></span>
                                                        </div>      
                                                    </div>
                                                </el-col>
                                            </el-row>                                                                                          
                                        </div>
                                    </div>
                                </div>
                            </template>
						</el-form>
  
					</div>
				</el-col>
			</el-row>            
            <el-dialog id="recurring_front_modal" custom-class="bpa-dialog bpa-dailog__small bpa-dialog--add-recurring-edit" :visible.sync="recurring_open_edit_popup" close-on-press-escape="false">
                <div class="bpa-dialog-heading">
                    <el-row type="flex">
                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">                            
                            <h1 class="bpa-page-heading"><?php esc_html_e( 'Edit Appointment', 'bookingpress-recurring-appointments' ); ?></h1>
                        </el-col>
                    </el-row>
                </div>
                <div class="bpa-dialog-body">
                    <el-container class="bpa-grid-list-container bpa-add-categpry-container">
                        <div v-if="recurring_appointment_loader_edit" class="bpa-recurring-appointment-loader bpa-recurring-appointment-loader-edit-cls">
                            <div class="bpa-back-loader-container">
                                <div class="bpa-back-loader"></div>
                            </div>                                       
                        </div>                          
                        <div v-if="recurring_appointment_loader_edit == false" class="bpa-form-row">
                            <el-row>
                                <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                    <el-form ref="recurring_edit_form" :model="recurring_edit_form" label-position="top" @submit.native.prevent>
                                        <div class="bpa-form-body-row">
                                            <el-row>
                                                <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                    <el-form-item prop="recurring_edit_date">
                                                        <template #label>
                                                            <span class="bpa-form-label"><?php esc_html_e( 'Date', 'bookingpress-recurring-appointments' ); ?></span>
                                                        </template>   
                                                        <el-date-picker :disabled="recurring_appointment_loader_edit" @change="change_recurring_start_date_backend($event,'edit')" class="bpa-form-control bpa-form-control--date-picker" type="date" format="<?php echo esc_html($bookingpress_common_date_format); ?>" placeholder="<?php echo esc_html($bookingpress_common_date_format); ?>" v-model="recurring_edit_date" name="appointment_booked_date" popper-class="bpa-custom-datepicker" type="date" :clearable="false" :picker-options="recurring_edit_pickerOptions" value-format="yyyy-MM-dd"></el-date-picker>                                                                                                             
                                                    </el-form-item> 
                                                </el-col>	
                                                <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                    <el-form-item prop="recurring_edit_time">
                                                        <template #label>
                                                            <span class="bpa-form-label"><?php esc_html_e( 'Time', 'bookingpress-recurring-appointments' ); ?></span>
                                                        </template>   
                                                        <div class="field bpa-recurring-appointment-head-row">
                                                            <el-select :loading="recurring_appointment_time_loader_edit" class="bpa-form-control" v-model="recurring_edit_time" filterable popper-class="bpa-fm--service__advance-options-popper bpa-el-select--is-with-modal" @Change="bookingpress_set_recurring_start_time($event,recurring_edit_appointment_time_slot,'edit')">                                                                
                                                                <el-option-group v-for="appointment_time_slot_data in recurring_edit_appointment_time_slot" :key="appointment_time_slot_data.timeslot_label" :label="appointment_time_slot_data.timeslot_label" >
                                                                    <el-option v-for="appointment_time in appointment_time_slot_data.timeslots" :label="(appointment_time.formatted_start_time)+' to '+(appointment_time.formatted_end_time)" :value="appointment_time.store_start_time" :disabled="( appointment_time.is_disabled || appointment_time.max_capacity == 0 || appointment_time.is_booked == 1 )">
                                                                        <span>{{ appointment_time.formatted_start_time  }} to {{appointment_time.formatted_end_time}}</span>
                                                                    </el-option>	
                                                                </el-option-group>                                                                
                                                            </el-select>
                                                        </div>                                                        
                                                    </el-form-item> 
                                                </el-col>	                                                								
                                            </el-row>
                                        </div>
                                    </el-form>
                                </el-col>
                            </el-row>
                        </div>
                    </el-container>
                </div>
                <div class="bpa-dialog-footer">
                    <div class="bpa-hw-right-btn-group">
                        <el-button @click="save_edit_recurring_data_backend()" class="bpa-front-btn bpa-btn bpa-btn__small bpa-btn--primary bpa-front-btn--primary"><?php esc_html_e( 'Done', 'bookingpress-recurring-appointments' ); ?></el-button>
                        <el-button @click="close_recurring_modal_backend()" class="bpa-front-btn bpa-btn bpa-btn__small bpa-front-btn--borderless"><?php esc_html_e( 'Cancel', 'bookingpress-recurring-appointments' ); ?></el-button>
                    </div>
                </div>
            </el-dialog>
            

		</div>
        <?php 
        }
        
        /**
         * Function for edit recurring date
         *
         * @return void
        */
        function bookingpress_set_additional_appointment_xhr_data_func(){
        ?>
            if(vm.recurring_open_edit_popup == true){
                let recurringEditIndex = vm.appointment_formdata.recurring_edit_index;
                if(vm.appointment_formdata.recurring_appointments != '' && typeof vm.appointment_formdata.recurring_appointments[recurringEditIndex] != "undefined"){                    
                    var recurring_appointment_selected_date = vm.appointment_formdata.recurring_appointments[recurringEditIndex].selected_date;
                    postData.selected_date = recurring_appointment_selected_date;
                }
            }
        <?php 
        }
        
        /**
         * Function for set disable date for edit appointment backend
         *
         * @return void
         */
        function bookingpress_additional_disable_dates_func(){
        ?>                                                       
            if(vm.recurring_open_edit_popup == true){
                let disableDatesRecurring = response.data.days_off_disabled_dates;

                if(typeof disableDatesRecurring !== "undefined") {

                    let disableDatesRecurring_arr = disableDatesRecurring.split(",");                                        
                    let disableDatesRecurring_formatted = [];
                    vm.recurring_edit_disable_dates = [];
                    disableDatesRecurring_arr.forEach(function( date ){                                            
                        let formatted_date = vm.get_formatted_date( date );
                        vm.recurring_edit_disable_dates.push( formatted_date );
                    });
                    vm.recurring_edit_pickerOptions.disabledDate = function(Time){     
                        if(vm.recurring_max_available_date != "") {                                                     
                            var max_avaliable_time = Date.parse(""+vm.recurring_max_available_date);                            
                            if(Time.getTime() > max_avaliable_time){
                                return true;
                            }                                
                        }                                               
                        let currentDate = new Date( Time );
                        currentDate = vm.get_formatted_date( currentDate );
                        var date = new Date();                    
                        date.setDate(date.getDate()-1);                    
                        var disable_past_date = Time.getTime() < date.getTime();
                        if( vm.recurring_edit_disable_dates.indexOf( currentDate ) > -1 ){
                            return true;
                        } else {
                            return disable_past_date;
                        }
                    };
                    if(typeof response.data.front_timings !== "undefined"){
                        let timeslot_response_data = response.data.front_timings;
                        let morning_times = timeslot_response_data.morning_time;
                        let afternoon_times = timeslot_response_data.afternoon_time;
                        let evening_times = timeslot_response_data.evening_time;
                        let night_times = timeslot_response_data.night_time;                    
                        let timeslot_data = {
                            morning_time: {
                                timeslot_label: "<?php esc_html_e( 'Morning', 'bookingpress-recurring-appointments' ); ?>",
                                timeslots: morning_times
                            },
                            afternoon_time: {
                                timeslot_label: "<?php esc_html_e( 'Afternoon', 'bookingpress-recurring-appointments' ); ?>",
                                timeslots: afternoon_times
                            },
                            evening_time: {
                                timeslot_label: "<?php esc_html_e( 'Evening', 'bookingpress-recurring-appointments' ); ?>",
                                timeslots: evening_times
                            },
                            night_time: {
                                timeslot_label: "<?php esc_html_e( 'Night', 'bookingpress-recurring-appointments' ); ?>",
                                timeslots: night_times
                            }
                        };                    
                        vm.recurring_edit_appointment_time_slot = timeslot_data;
                        vm.bookingpress_reset_default_date_time_data();
                    } 
                    vm.recurring_appointment_time_loader_edit = false;
                    vm.recurring_appointment_loader_edit = false;   

                }

            }else{
                vm.appointment_time_slot_old = vm.appointment_time_slot;
            }      
            vm.recurring_appointment_time_loader = false;     
        <?php 
        }
        
        /**
         * Function for get edit appointment disable date
         *
         * @return void
         */
        function bookingpress_get_front_timing_set_additional_appointment_xhr_data_func(){
        ?>
            if(vm.recurring_open_edit_popup == true){
                let recurringEditIndex = vm.appointment_formdata.recurring_edit_index;
                if(vm.appointment_formdata.recurring_appointments != '' && typeof vm.appointment_formdata.recurring_appointments[recurringEditIndex] != "undefined"){                    
                    var recurring_appointment_selected_date = vm.appointment_formdata.recurring_appointments[recurringEditIndex].selected_date;                    
                }
            }
        <?php 
        }
        
        /**
         * Function for after get timeslot response in backend
         *
         * @return void
         */
        function bookingpress_backend_after_get_timeslot_response_func(){
        ?>                      
            vm.recurring_appointment_time_loader = false;            
            if(vm.recurring_open_edit_popup == true){
                vm.recurring_appointment_loader_edit = false;
                vm.recurring_appointment_time_loader_edit = false;

                if(typeof response.data !== "undefined") {
                    let timeslot_response_data = response.data;
                    let morning_times = timeslot_response_data.morning_time;
                    let afternoon_times = timeslot_response_data.afternoon_time;
                    let evening_times = timeslot_response_data.evening_time;
                    let night_times = timeslot_response_data.night_time;                    
                    let timeslot_data = {
                        morning_time: {
                            timeslot_label: "<?php esc_html_e( 'Morning', 'bookingpress-recurring-appointments' ); ?>",
                            timeslots: morning_times
                        },
                        afternoon_time: {
                            timeslot_label: "<?php esc_html_e( 'Afternoon', 'bookingpress-recurring-appointments' ); ?>",
                            timeslots: afternoon_times
                        },
                        evening_time: {
                            timeslot_label: "<?php esc_html_e( 'Evening', 'bookingpress-recurring-appointments' ); ?>",
                            timeslots: evening_times
                        },
                        night_time: {
                            timeslot_label: "<?php esc_html_e( 'Night', 'bookingpress-recurring-appointments' ); ?>",
                            timeslots: night_times
                        }
                    };                    
                    vm.recurring_edit_appointment_time_slot = timeslot_data;
                    vm.bookingpress_reset_default_date_time_data();
                }
            }else{
                vm.appointment_time_slot_old = vm.appointment_time_slot;
            }             
        <?php
        }
        
        /**
         * Function for add backend appointment recurring vue data
         *
         * @param  mixed $bookingpress_appointment_vue_data_fields
         * @return void
         */
        function bookingpress_modify_backend_appointment_data_fields_func($bookingpress_appointment_vue_data_fields) {

            global $bookingpress_services, $BookingPress, $all_recurring_frequencies,$bookingpress_global_options;
            $bookingpress_global_details     = $bookingpress_global_options->bookingpress_global_options();
            if(!empty($bookingpress_appointment_vue_data_fields['appointment_services_list']) ) {                

                foreach($bookingpress_appointment_vue_data_fields['appointment_services_list'] as $key => $value ) {
                    if(!empty($value['category_services'])) {
                        foreach($value['category_services'] as $key2 => $value2 ) {

                            $bookingpress_service_id = !empty($value2['service_id']) ? intval($value2['service_id']) : 0;
                            $enable_custom_service_duration = $bookingpress_services->bookingpress_get_service_meta($bookingpress_service_id,'enable_custom_service_duration');
                            $enable_custom_service_duration = !empty($enable_custom_service_duration ) && $enable_custom_service_duration == 'true' ? true : false;
                            $recurring_frequencies = $bookingpress_services->bookingpress_get_service_meta($bookingpress_service_id, 'recurring_frequencies');
                            $enable_recurring_appointments = $bookingpress_services->bookingpress_get_service_meta($bookingpress_service_id, 'enable_recurring_appointments');                                                              
                            $has_custom_service_duration = false;
                            if(isset($value2['enable_custom_service_duration'])){
                                $enable_custom_service_duration = $value2['enable_custom_service_duration'];
                                if(!empty($enable_custom_service_duration) && $enable_custom_service_duration == 'true'){
                                    $has_custom_service_duration = true;
                                }
                            }
                            $bookingpress_service_duration_unit = (isset($value2['bookingpress_service_duration_unit']))?$value2['bookingpress_service_duration_unit']:'';
                            
                            $default_recurring_frequencies = $bookingpress_services->bookingpress_get_service_meta($bookingpress_service_id, 'default_recurring_frequencies');                   
                            $recurring_appointments_max_no_of_times = $bookingpress_services->bookingpress_get_service_meta($bookingpress_service_id, 'recurring_appointments_max_no_of_times');

                            if(empty($recurring_frequencies) || $bookingpress_service_duration_unit == 'd' || $has_custom_service_duration || $recurring_appointments_max_no_of_times == 0){
                                $enable_recurring_appointments = 0;
                            }
                            if(!empty($recurring_frequencies)){
                                $recurring_frequencies = explode(",",$recurring_frequencies);
                            }

                            $bookingpress_appointment_vue_data_fields['appointment_services_list'][$key]['category_services'][$key2]['enable_recurring_appointments'] = $enable_recurring_appointments;
                            $bookingpress_appointment_vue_data_fields['appointment_services_list'][$key]['category_services'][$key2]['recurring_frequencies'] = $recurring_frequencies;                            
                            
                            $bookingpress_appointment_vue_data_fields['appointment_services_list'][$key]['category_services'][$key2]['default_recurring_frequencies'] = $default_recurring_frequencies;
                            $bookingpress_appointment_vue_data_fields['appointment_services_list'][$key]['category_services'][$key2]['recurring_appointments_max_no_of_times'] = $recurring_appointments_max_no_of_times;

                        }                            
                    }
                }                
            }

            $max_no_of_recurring = $this->max_no_of_recurring;
            $bookingpress_no_of_recurring = array();
            for($i=0;$i<=$max_no_of_recurring;$i++){
                if($i == 0){
                    $label = 0;
                }else{
                    $label = str_pad($i,2,0,STR_PAD_LEFT);
                }                 
                $bookingpress_no_of_recurring[] = array('label' => $label,'value' => $i);
            }
            $bookingpress_no_of_recurring_first_label = array();
            for($i=0;$i<=$max_no_of_recurring;$i++){
                if($i != 1){
                    if($i == 0){
                        $label = esc_html__('Select Sessions', 'bookingpress-recurring-appointments');
                    }else{
                        $label = $i.' '.esc_html__('Sessions', 'bookingpress-recurring-appointments');
                    }                                                    
                    $bookingpress_no_of_recurring_first_label[] = array('label' => $label,'value' => $i);
                }
            }

            $bookingpress_appointment_vue_data_fields['bookingpress_no_of_recurring'] = $bookingpress_no_of_recurring_first_label;
            $bookingpress_appointment_vue_data_fields['bookingpress_recurring_frequencies'] = array();           
            $bookingpress_appointment_vue_data_fields['all_recurring_frequencies'] = $all_recurring_frequencies;
            $bookingpress_appointment_vue_data_fields['recurring_pickerOptions'] = array('firstDayOfWeek'=>intval(esc_html($bookingpress_global_details['start_of_week'])));
            $bookingpress_appointment_vue_data_fields['recurring_disable_dates'] = array();           
            $bookingpress_appointment_vue_data_fields['recurring_appointment_time_slot'] = array(); 
            
            $bookingpress_appointment_vue_data_fields['recurring_open_edit_popup'] = false;           
            $bookingpress_appointment_vue_data_fields['recurring_edit_date'] = '';
            $bookingpress_appointment_vue_data_fields['recurring_edit_time'] = '';

            $bookingpress_appointment_vue_data_fields['recurring_edit_form']=array(
                'recurring_edit_date' => '',
                'recurring_edit_time' => '',
            );


            $bookingpress_appointment_vue_data_fields['recurring_appointment_loader'] = false;
            //recurring_appointment_loader

            $bookingpress_appointment_vue_data_fields['recurring_edit_pickerOptions'] = array('firstDayOfWeek'=>intval(esc_html($bookingpress_global_details['start_of_week'])));
            $bookingpress_appointment_vue_data_fields['recurring_edit_disable_dates'] = array();           
            $bookingpress_appointment_vue_data_fields['recurring_edit_appointment_time_slot'] = array();

            $bookingpress_appointment_vue_data_fields['recurring_max_available_date'] = "";
            

            $bookingpress_appointment_vue_data_fields['recurring_form_data'] = array(
                'start_date' => '',
                'start_time' => '',
                'end_time' => '',
                'formatted_start_time' => '',
                'formatted_end_time' => '',
                'recurring_frequency' => '',
                'no_of_session' => 0,
            );

            $bookingpress_appointment_vue_data_fields['appointment_formdata']['recurring_edit_index'] = '';           
            $bookingpress_appointment_vue_data_fields['appointment_formdata']['bookingpress_recurring_total'] = 0;
            $bookingpress_appointment_vue_data_fields['appointment_formdata']['bookingpress_recurring_total_with_currency'] = '';
            $bookingpress_appointment_vue_data_fields['appointment_formdata']['bookingpress_recurring_original_total'] = 0;
            $bookingpress_appointment_vue_data_fields['appointment_formdata']['bookingpress_recurring_original_total_with_currency'] = '';            

            $bookingpress_appointment_vue_data_fields['is_service_recurring_appointments_enable'] = "0";

            $bookingpress_appointment_vue_data_fields['appointment_formdata']['is_recurring_appointments'] = false;
            $bookingpress_appointment_vue_data_fields['appointment_formdata']['recurring_appointments'] = array();

            $bookingpress_appointment_vue_data_fields['recurring_appointments_max_no_of_times'] = 0;


            $recurring_suggested_message = $BookingPress->bookingpress_get_customize_settings('recurring_suggested_message','booking_form');
            $recurring_not_avaliable_message = $BookingPress->bookingpress_get_customize_settings('recurring_not_avaliable_message','booking_form');

            $bookingpress_appointment_vue_data_fields['recurring_suggested_message'] = $recurring_suggested_message;
            $bookingpress_appointment_vue_data_fields['recurring_not_avaliable_message'] = $recurring_not_avaliable_message;
            $bookingpress_appointment_vue_data_fields['appointment_time_slot_old'] = array(); 

            $bookigpress_time_format_for_booking_form =  $BookingPress->bookingpress_get_customize_settings('bookigpress_time_format_for_booking_form','booking_form');
			$bookigpress_time_format_for_booking_form =  !empty($bookigpress_time_format_for_booking_form) ? $bookigpress_time_format_for_booking_form : '2';
            
            $bookingpress_appointment_vue_data_fields['bookigpress_time_format_for_booking_form'] = $bookigpress_time_format_for_booking_form;

            $bookingpress_appointment_vue_data_fields['bookingpress_is_recurring_appointment_list_model'] = false;
            $bookingpress_appointment_vue_data_fields['is_mask_display'] = false;
            
            $bookingpress_appointment_vue_data_fields['bookingpress_recurring_appointment_list_popup'] = '';
            $bookingpress_appointment_vue_data_fields['bookingpress_recurring_appointment_list_popup_loader'] = true;

            $bookingpress_appointment_vue_data_fields['recurring_appointment_loader_edit'] = false;
            $bookingpress_appointment_vue_data_fields['recurring_appointment_time_loader'] = false;
            $bookingpress_appointment_vue_data_fields['recurring_appointment_time_loader_edit'] = false;



            return $bookingpress_appointment_vue_data_fields;
        }


        /* Coupon code start */        
        function bookingpress_modified_coupon_total_payable_amount_func($bookingpress_payable_amount, $bookingpress_appointment_details){

            $is_recurring_appointments = (isset($bookingpress_appointment_details['is_recurring_appointments']))?$bookingpress_appointment_details['is_recurring_appointments']:'';
            $bookingpress_recurring_total = (isset($bookingpress_appointment_details['bookingpress_recurring_total']))?$bookingpress_appointment_details['bookingpress_recurring_total']:'';
            if($is_recurring_appointments == 'true' && !empty($bookingpress_recurring_total)){
                $bookingpress_payable_amount = (float)$bookingpress_recurring_total;
            }

            return $bookingpress_payable_amount;
        }

        /**
         * Function for call coupon code outside calculation function.
         *
         * @param  mixed $bookingpress_need_check_coupon_validity_from_outside
         * @return void
         */
        function bookingpress_need_check_coupon_validity_from_outside_func($bookingpress_need_check_coupon_validity_from_outside,$bookingpress_appointment_details){

            $is_recurring_appointments = (isset($bookingpress_appointment_details['is_recurring_appointments']))?sanitize_text_field($bookingpress_appointment_details['is_recurring_appointments']):'';
            if($is_recurring_appointments == 'true'){
                $bookingpress_need_check_coupon_validity_from_outside = true;
            }                        
            
            return $bookingpress_need_check_coupon_validity_from_outside;

        }

        /**
         * Add Placeholder for Email Notification
         *
         * @return void
         */
        function bookingpress_add_recurring_appointment_placeholder_list() {
            ?>
            <div class="bpa-gs__cb--item-tags-body" v-if="bookingpress_active_email_notification != 'package_order'">
                <div>
                    <span class="bpa-tags--item-sub-heading"><?php esc_html_e('Recurring Appointment', 'bookingpress-recurring-appointments'); ?></span>
                    <span class="bpa-tags--item-body" v-for="item in recurring_appointment_list" @click="bookingpress_insert_placeholder(item.value); bookingpress_insert_sms_placeholder(item.value); bookingpress_insert_whatsapp_placeholder(item.value);">{{ item.name }}</span>
                </div>
            </div>
            <?php
        }
        
        /**
         * Array for Notification placeholder bookingpress_add_recurring_dynamic_notification_data_fields
         *
         * @param  mixed $bookingpress_notification_vue_methods_data
         * @return void
         */
        function bookingpress_add_recurring_dynamic_notification_data_fields( $bookingpress_notification_vue_methods_data ) {
            $bookingpress_notification_vue_methods_data['recurring_appointment_list'] = array(
                array(
                    'value' => '%recurring_appointment_list%',
                    'name' => '%recurring_appointment_list%'
                ),
            );
            return $bookingpress_notification_vue_methods_data;
        }        
                
        /**
         * function for remove recurring appointment in cart
         *
         * @param  mixed $bookingpress_before_add_to_cart_item
         * @return void
         */
        function bookingpress_before_add_to_cart_item_func($bookingpress_before_add_to_cart_item){
            $bookingpress_before_add_to_cart_item.='
                if(typeof vm5.appointment_step_form_data.is_recurring_appointments != "undefined"){                    
                    if(vm5.appointment_step_form_data.is_recurring_appointments == true || vm5.appointment_step_form_data.is_recurring_appointments == "true"){
                        is_service_added_to_cart = 1;
                        if(vm5.appointment_step_form_data.cart_items.length > 0){
                            vm5.appointment_step_form_data.cart_items = [];
                        }
                    }
                }                
            ';
            return $bookingpress_before_add_to_cart_item;
        }

        /**
         * Disable happy hour data in timeslot
         *
         * @return void
         */
        function bookingpress_disable_happy_hours_data_for_timeslot_func($is_disable,$posted_data){
            $is_recurring_appointments = (isset($posted_data['appointment_data_obj']['is_recurring_appointments']))?sanitize_text_field($posted_data['appointment_data_obj']['is_recurring_appointments']):'';
            if($is_recurring_appointments == 'true'){
                $is_disable = true;
            }
            return $is_disable;
        }

		/**
		 * Function for modify appointment listing details
		 *
		 * @param  mixed $bookingpress_appointment_data
		 * @return void
		*/
		function bookingpress_appointment_add_view_field_func($bookingpress_appointment_data, $get_appointment){
            if(isset($get_appointment['bookingpress_is_recurring'])){
                $bookingpress_appointment_data['bookingpress_is_recurring'] = $get_appointment['bookingpress_is_recurring'];          
            }
            if(isset($get_appointment['bookingpress_order_id'])){
                $bookingpress_appointment_data['bookingpress_order_id'] = $get_appointment['bookingpress_order_id'];          
            }
            return $bookingpress_appointment_data;
        }
        
        /**
         * Function for add backend icon for appointment list & payment list
         *
         * @return void
         */
        function bookingpress_backend_appointment_list_type_icons_func(){
        ?>
        <div class="bookingpress-recurring-appointment-list-data">        
            <el-tooltip content="<?php esc_html_e('Recurring Transaction', 'bookingpress-recurring-appointments'); ?>" placement="top" v-if="scope.row.bookingpress_is_recurring == 1">
                <span class="material-icons-round bpa-apc__recurring-icon" @click="bookingpress_recurring_appointment_list_model(event,scope.row.bookingpress_order_id)"> 
                    <svg width="15" height="15" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M0 8.53745C0 8.82364 0.113692 9.09812 0.316064 9.30049C0.518437 9.50287 0.792913 9.61656 1.07911 9.61656C1.3578 9.6082 1.62245 9.49231 1.81758 9.29315C2.01271 9.094 2.12318 8.82704 2.12585 8.54824C2.14747 7.32275 2.5165 6.12857 3.19003 5.10453C3.86355 4.0805 4.81391 3.26867 5.93062 2.76344C7.04733 2.25821 8.2845 2.08033 9.49831 2.25048C10.7121 2.42063 11.8527 2.93182 12.7875 3.72461H12.4421C12.3004 3.72461 12.1601 3.75252 12.0292 3.80675C11.8983 3.86098 11.7793 3.94047 11.6791 4.04068C11.5789 4.14088 11.4994 4.25984 11.4452 4.39076C11.3909 4.52169 11.363 4.66201 11.363 4.80372C11.363 4.94543 11.3909 5.08576 11.4452 5.21668C11.4994 5.3476 11.5789 5.46657 11.6791 5.56677C11.7793 5.66697 11.8983 5.74646 12.0292 5.80069C12.1601 5.85492 12.3004 5.88283 12.4421 5.88283H15.496C15.7822 5.88283 16.0567 5.76914 16.2591 5.56677C16.4614 5.3644 16.5751 5.08992 16.5751 4.80372V1.74984C16.5751 1.46364 16.4614 1.18917 16.2591 0.986793C16.0567 0.78442 15.7822 0.670729 15.496 0.670729C15.2098 0.670729 14.9354 0.78442 14.733 0.986793C14.5306 1.18917 14.4169 1.46364 14.4169 1.74984V2.22465C13.1834 1.11118 11.6547 0.377381 10.0144 0.11135C8.3741 -0.154681 6.69186 0.0583593 5.16965 0.724896C3.64744 1.39143 2.34995 2.48314 1.43294 3.86896C0.515929 5.25477 0.0183721 6.8758 0 8.53745ZM15.8521 9.30361C15.8521 9.01742 15.9658 8.74294 16.1682 8.54057C16.3706 8.3382 16.6451 8.2245 16.9312 8.2245C17.2137 8.22731 17.4838 8.34077 17.6836 8.54051C17.8833 8.74026 17.9968 9.01036 17.9996 9.29282C18.0174 10.4421 17.8029 11.5831 17.369 12.6475C16.9351 13.7119 16.2907 14.6777 15.4744 15.4869C13.9087 17.0448 11.8054 17.9432 9.59738 17.9974C7.38934 18.0516 5.24451 17.2574 3.60423 15.7783V16.2531C3.60423 16.5393 3.49054 16.8138 3.28817 17.0161C3.08579 17.2185 2.81132 17.3322 2.52512 17.3322C2.23892 17.3322 1.96444 17.2185 1.76207 17.0161C1.5597 16.8138 1.44601 16.5393 1.44601 16.2531V13.1992C1.44601 12.913 1.5597 12.6385 1.76207 12.4362C1.96444 12.2338 2.23892 12.1201 2.52512 12.1201H5.61138C5.75309 12.1201 5.89341 12.148 6.02433 12.2022C6.15526 12.2565 6.27422 12.336 6.37442 12.4362C6.47463 12.5364 6.55411 12.6553 6.60834 12.7862C6.66257 12.9172 6.69049 13.0575 6.69049 13.1992C6.69049 13.3409 6.66257 13.4812 6.60834 13.6122C6.55411 13.7431 6.47463 13.862 6.37442 13.9623C6.27422 14.0625 6.15526 14.1419 6.02433 14.1962C5.89341 14.2504 5.75309 14.2783 5.61138 14.2783H5.23369C6.17914 15.0658 7.32942 15.5676 8.54979 15.7249C9.77015 15.8822 11.0101 15.6885 12.1243 15.1665C13.2386 14.6444 14.181 13.8157 14.8412 12.7774C15.5014 11.739 15.8521 10.5341 15.8521 9.30361Z" fill="#727E95"/>
                    </svg>                                         
                </span>
            </el-tooltip>
        </div>     
        <?php 
        }
                
        /**
         * Function for add recurring payment icon
         *
         * @return void
         */
        function bookingpress_backend_payment_list_type_icons_func(){
        ?>        
            <el-tooltip content="<?php esc_html_e('Recurring Transaction', 'bookingpress-recurring-appointments'); ?>" placement="top" v-if="scope.row.bookingpress_is_recurring == 1">
                <span class="material-icons-round bpa-apc__recurring-icon"> 
                    <svg width="15" height="15" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M0 8.53745C0 8.82364 0.113692 9.09812 0.316064 9.30049C0.518437 9.50287 0.792913 9.61656 1.07911 9.61656C1.3578 9.6082 1.62245 9.49231 1.81758 9.29315C2.01271 9.094 2.12318 8.82704 2.12585 8.54824C2.14747 7.32275 2.5165 6.12857 3.19003 5.10453C3.86355 4.0805 4.81391 3.26867 5.93062 2.76344C7.04733 2.25821 8.2845 2.08033 9.49831 2.25048C10.7121 2.42063 11.8527 2.93182 12.7875 3.72461H12.4421C12.3004 3.72461 12.1601 3.75252 12.0292 3.80675C11.8983 3.86098 11.7793 3.94047 11.6791 4.04068C11.5789 4.14088 11.4994 4.25984 11.4452 4.39076C11.3909 4.52169 11.363 4.66201 11.363 4.80372C11.363 4.94543 11.3909 5.08576 11.4452 5.21668C11.4994 5.3476 11.5789 5.46657 11.6791 5.56677C11.7793 5.66697 11.8983 5.74646 12.0292 5.80069C12.1601 5.85492 12.3004 5.88283 12.4421 5.88283H15.496C15.7822 5.88283 16.0567 5.76914 16.2591 5.56677C16.4614 5.3644 16.5751 5.08992 16.5751 4.80372V1.74984C16.5751 1.46364 16.4614 1.18917 16.2591 0.986793C16.0567 0.78442 15.7822 0.670729 15.496 0.670729C15.2098 0.670729 14.9354 0.78442 14.733 0.986793C14.5306 1.18917 14.4169 1.46364 14.4169 1.74984V2.22465C13.1834 1.11118 11.6547 0.377381 10.0144 0.11135C8.3741 -0.154681 6.69186 0.0583593 5.16965 0.724896C3.64744 1.39143 2.34995 2.48314 1.43294 3.86896C0.515929 5.25477 0.0183721 6.8758 0 8.53745ZM15.8521 9.30361C15.8521 9.01742 15.9658 8.74294 16.1682 8.54057C16.3706 8.3382 16.6451 8.2245 16.9312 8.2245C17.2137 8.22731 17.4838 8.34077 17.6836 8.54051C17.8833 8.74026 17.9968 9.01036 17.9996 9.29282C18.0174 10.4421 17.8029 11.5831 17.369 12.6475C16.9351 13.7119 16.2907 14.6777 15.4744 15.4869C13.9087 17.0448 11.8054 17.9432 9.59738 17.9974C7.38934 18.0516 5.24451 17.2574 3.60423 15.7783V16.2531C3.60423 16.5393 3.49054 16.8138 3.28817 17.0161C3.08579 17.2185 2.81132 17.3322 2.52512 17.3322C2.23892 17.3322 1.96444 17.2185 1.76207 17.0161C1.5597 16.8138 1.44601 16.5393 1.44601 16.2531V13.1992C1.44601 12.913 1.5597 12.6385 1.76207 12.4362C1.96444 12.2338 2.23892 12.1201 2.52512 12.1201H5.61138C5.75309 12.1201 5.89341 12.148 6.02433 12.2022C6.15526 12.2565 6.27422 12.336 6.37442 12.4362C6.47463 12.5364 6.55411 12.6553 6.60834 12.7862C6.66257 12.9172 6.69049 13.0575 6.69049 13.1992C6.69049 13.3409 6.66257 13.4812 6.60834 13.6122C6.55411 13.7431 6.47463 13.862 6.37442 13.9623C6.27422 14.0625 6.15526 14.1419 6.02433 14.1962C5.89341 14.2504 5.75309 14.2783 5.61138 14.2783H5.23369C6.17914 15.0658 7.32942 15.5676 8.54979 15.7249C9.77015 15.8822 11.0101 15.6885 12.1243 15.1665C13.2386 14.6444 14.181 13.8157 14.8412 12.7774C15.5014 11.739 15.8521 10.5341 15.8521 9.30361Z" fill="#727E95"/>
                    </svg>                     
                </span>
            </el-tooltip>            
        <?php             
        }

        /**
         * Function for add recurring data in payment table
         *
         * @param  mixed $payment_log_data
         * @param  mixed $entry_data
         * @return void
         */
        function bookingpress_modify_payment_log_fields_before_insert_func($payment_log_data, $entry_data){
            if(isset($entry_data['bookingpress_is_recurring'])) {
                if($entry_data['bookingpress_is_recurring'] == 1){
                    $payment_log_data['bookingpress_is_recurring'] = 1;
                    $payment_log_data['bookingpress_is_cart'] = 0;
                }  

                if(isset($entry_data['bookingpress_complete_payment_url_selection']) && isset($entry_data['bookingpress_complete_payment_url_selection_method'])){                
                    $bookingpress_complete_payment_url_selection         = $entry_data['bookingpress_complete_payment_url_selection'];
                    $bookingpress_complete_payment_url_selection_method  = $entry_data['bookingpress_complete_payment_url_selection_method'];
                    $payment_log_data['bookingpress_complete_payment_url_selection'] = $bookingpress_complete_payment_url_selection;
                    $payment_log_data['bookingpress_complete_payment_url_selection_method'] = $bookingpress_complete_payment_url_selection_method;
                    
                    $payment_log_data['bookingpress_complete_payment_token'] = $entry_data['bookingpress_complete_payment_token'];

                }                

                $payment_log_data['bookingpress_staff_member_id'] = isset($entry_data['bookingpress_staff_member_id']) ? $entry_data['bookingpress_staff_member_id'] : '';
                $payment_log_data['bookingpress_staff_member_price'] = isset($entry_data['bookingpress_staff_member_price']) ? $entry_data['bookingpress_staff_member_price'] : '';
                $payment_log_data['bookingpress_staff_first_name'] = isset($entry_data['bookingpress_staff_first_name']) ? $entry_data['bookingpress_staff_first_name'] : '';
                $payment_log_data['bookingpress_staff_last_name'] = isset($entry_data['bookingpress_staff_last_name']) ? $entry_data['bookingpress_staff_last_name'] : '';
                $payment_log_data['bookingpress_staff_email_address'] = isset($entry_data['bookingpress_staff_email_address']) ? $entry_data['bookingpress_staff_email_address'] : '';
                $payment_log_data['bookingpress_staff_member_details'] = isset($entry_data['bookingpress_staff_member_details']) ? $entry_data['bookingpress_staff_member_details'] : '';  
                $payment_log_data['bookingpress_service_id'] = isset($entry_data['bookingpress_service_id']) ? $entry_data['bookingpress_service_id'] : '';  
                $payment_log_data['bookingpress_service_name'] = isset($entry_data['bookingpress_service_name']) ? $entry_data['bookingpress_service_name'] : '';  

            }
            return $payment_log_data;
        }        

        /**
         * Function for add recurring appointment data
         *
         * @param  mixed $appointment_booking_fields
         * @param  mixed $entry_data
         * @return void
         */
        function bookingpress_modify_appointment_booking_fields_before_insert_func($appointment_booking_fields, $entry_data ) {                       
            if(isset($entry_data['bookingpress_is_recurring'])) {
                if($entry_data['bookingpress_is_recurring'] == 1){
                    $appointment_booking_fields['bookingpress_is_recurring'] = 1;
                    $appointment_booking_fields['bookingpress_is_cart'] = 0;

                    if(isset($entry_data['bookingpress_complete_payment_url_selection']) && isset($entry_data['bookingpress_complete_payment_url_selection_method'])){
                
                        $bookingpress_complete_payment_url_selection         = $entry_data['bookingpress_complete_payment_url_selection'];
                        $bookingpress_complete_payment_url_selection_method  = $entry_data['bookingpress_complete_payment_url_selection_method'];
                        $appointment_booking_fields['bookingpress_complete_payment_url_selection'] = $bookingpress_complete_payment_url_selection;
                        $appointment_booking_fields['bookingpress_complete_payment_url_selection_method'] = $bookingpress_complete_payment_url_selection_method;
        
                        $appointment_booking_fields['bookingpress_complete_payment_token'] = $entry_data['bookingpress_complete_payment_token'];
                    }

                }                
            }
            return $appointment_booking_fields;
        }

        /**
         * bookingpress_recurring_customize_css_content_modify_fun
         *
         * @param  mixed $bookingpress_customize_css_content
         * @param  mixed $bookingpress_custom_data_arr
         * @return void
         */
        function bookingpress_recurring_customize_css_content_modify_fun($bookingpress_customize_css_content,$bookingpress_custom_data_arr)
        {
            global $BookingPress;            
            /*suggested color setting - start */
            $recurring_appointment_suggested_timeslot_color = $BookingPress->bookingpress_get_customize_settings('recurring_appointment_suggested_timeslot_color','booking_form');                            
            if(empty($recurring_appointment_suggested_timeslot_color)){
                $recurring_appointment_suggested_timeslot_color = '#F5AE41';         
            }

            $hex               = $recurring_appointment_suggested_timeslot_color;
			list($r, $g, $b)   = sscanf($hex, '#%02x%02x%02x');
            $recurring_appointment_suggested_background_color = "rgba($r,$g,$b,0.16)";
            /*suggested color setting - over */
			
            /* Not avilable color setting - start */
            $recurring_appointment_booked_timelost_color = $BookingPress->bookingpress_get_customize_settings('recurring_appointment_booked_timelost_color','booking_form');            
            if(empty($recurring_appointment_booked_timelost_color)){
                $recurring_appointment_booked_timelost_color = '#ff0000';         
            }
            $hex               = $recurring_appointment_booked_timelost_color;
			list($r, $g, $b)   = sscanf($hex, '#%02x%02x%02x');
            $recurring_appointment_notavailable_background_color = "rgba($r,$g,$b,0.16)";
            /* Not avilable color setting - over */

            /*other customize settings -start */
            $recurring_title_label_color = $bookingpress_custom_data_arr['booking_form']['label_title_color'];
            $recurring_sub_title_color   = $bookingpress_custom_data_arr['booking_form']['sub_title_color'];
            $recurring_content_color     = $bookingpress_custom_data_arr['booking_form']['content_color'];
            $recurring_border_color      = $bookingpress_custom_data_arr['booking_form']['border_color'];
            $recurring_title_font_family = $bookingpress_custom_data_arr['booking_form']['title_font_family'];
            $recurring_title_font_family =  $recurring_title_font_family == 'Inherit Fonts' ? 'inherit' : $recurring_title_font_family;
            $recurring_background_color  = $bookingpress_custom_data_arr['booking_form']['background_color'];
            $shortcode_footer_background_color = $bookingpress_custom_data_arr['booking_form']['footer_background_color'];

            $primary_color                     = $bookingpress_custom_data_arr['booking_form']['primary_color'];

			list($r, $g, $b)   = sscanf($primary_color, '#%02x%02x%02x');
            $recurring_appointment_primary_background_color = "rgba($r,$g,$b,0.20)";

			list($r, $g, $b)   = sscanf($recurring_title_label_color, '#%02x%02x%02x');
            $recurring_title_label_color_color = "rgba($r,$g,$b,0.60)";

            /*other customize settings -over */
            
            $bookingpress_customize_css_content.='

                .bpa-custom-recurring-datepicker .el-picker-panel__content .el-date-table td.prev-month span, .bpa-custom-recurring-datepicker .el-picker-panel__content .el-date-table td.next-month span{
                    color: ' . $recurring_title_label_color_color . ' !important;
                }
                .bpa_rec_popover_datetime_item{
                    color: ' . $recurring_content_color . ' !important;
                }                
                .bpa-thank-you-datetime-count{
                    color: ' . $recurring_sub_title_color . ' !important;
                    border-color:' . $recurring_sub_title_color . ' !important;
                }                
                .bpa-recurring-msg-suggested span { color: '.$recurring_appointment_suggested_timeslot_color.' !important; }
                .bpa-recurring-msg-suggested svg { fill:  '.$recurring_appointment_suggested_timeslot_color.' !important; }
                .bpa-dialog--add-recurring-edit .bpa-dialog-heading, .bpa-dialog--add-recurring-edit .bpa-dialog-footer{
                    border-color: '.$recurring_border_color.' !important; 
                }
                .bpa-recurring-msg-notavaliable span{
                    color: '.$recurring_appointment_booked_timelost_color.' !important;
                }
                .bpa-recurring-msg-notavaliable svg{
                    fill:  '.$recurring_appointment_booked_timelost_color.' !important;
                }
                .bookingpress-is-recurring-heading,.bpa-dialog--add-recurring-edit .bpa-page-heading{ color: ' . $recurring_title_label_color . ' !important; }
                .bpa-recurring-head-col label { color: ' . $recurring_sub_title_color . ' !important; font-family: ' . $recurring_title_font_family . ' !important; } 
                .bpa-upcomming-appointments .bpa-hh-item__date-col , .bpa-upcomming-appointments .bpa-hh-item-info-col { color: ' . $recurring_sub_title_color . ' !important; } 
                .bpa-hh-item__date-col-date .bpa-front-tm--item-icon svg, .bpa-card__item .bpa-front-btn--icon-without-box span svg{  fill:  '.$recurring_content_color.' !important; }
                .bpa-recurring-appointment-body .bpa-lspd__item, .bpa-card-item-mobile-edit-appointment .bpa-edit-appointment-body { border-color: ' . $recurring_border_color . ' !important; }
                .bpa-lspd__item .bpa-hh-item__date-col span, .bpa-lspd__item .bpa-hh-item-info-col p, .bpa-recurring-msg span, 
                .bpa-dialog--add-recurring-edit .el-form-item span, .bpa-dialog--add-recurring-edit .bpa-dialog-heading h1 {  font-family: ' . $recurring_title_font_family . ' !important; } 
                .bpa-dialog--add-recurring-edit {background-color: '.$recurring_background_color.' !important; }
                .bpa-card-item-mobile-edit-appointment { background-color: ' . $shortcode_footer_background_color . ' !important; }
                .bpa-fm--service__advance-options-popper .el-select-group__title { color:  '.$recurring_content_color.' !important; }
                .bpa-upcomming-notavaliable .bpa-lspd__item{
                    border-color: '.$recurring_appointment_booked_timelost_color.' !important; 
                    background-color: '.$recurring_appointment_notavailable_background_color.' !important; 
                }  
                .bpa-upcomming-suggested .bpa-lspd__item {
                    border-color: '.$recurring_appointment_suggested_timeslot_color.' !important; 
                    background-color: '.$recurring_appointment_suggested_background_color.' !important; 
                }                              
            ';  
            return $bookingpress_customize_css_content;
        }

        /**
         * Function for recurring color for customize
         *
         * @return void
         */
        function bookingpress_customize_recurring_color_setting_after_fun()
        {
            ?>    
            <div class="bpa-tp__body-item __bpa-is-style-type">
                <div class="bpa-bi__title">
                    <label class="bpa-form-label"><?php esc_html_e('Recurring Appointment color:', 'bookingpress-recurring-appointments'); ?></label>
                </div>
                <div class="bpa-bi__body">
                    <el-tooltip effect="dark" content="<?php esc_html_e('Suggested Timeslot Color', 'bookingpress-recurring-appointments'); ?>" placement="top" open-delay="300">
                        <el-color-picker class="bpa-customize-tp__color-picker" v-model="recurring_appointment_container_data.recurring_appointment_suggested_timeslot_color" ></el-color-picker>
                    </el-tooltip>
                    <el-tooltip effect="dark" content="<?php esc_html_e('Not Available Timeslot Color', 'bookingpress-recurring-appointments'); ?>" placement="top" open-delay="300">
                        <el-color-picker class="bpa-customize-tp__color-picker" v-model="recurring_appointment_container_data.recurring_appointment_booked_timelost_color"></el-color-picker>
                    </el-tooltip>
                </div>
            </div>  
            <?php 
        }
                
        /**
         * Reset color for backend
         *
         * @return void
         */
        function bookingpress_reset_color_option_after_fun(){
        ?>
            vm.recurring_appointment_container_data.recurring_appointment_suggested_timeslot_color = '#F5AE41';
            vm.recurring_appointment_container_data.recurring_appointment_booked_timelost_color = '#ff0000';
        <?php
        }
        
        /**
         * Function for add bookingpress_entries table data add
         *
         * @param  mixed $bookingpress_appointment_data
         * @param  mixed $payment_gateway
         * @param  mixed $posted_data
         * @return void
         */
        function bookingpress_add_recurring_entries_data_func($bookingpress_appointment_data, $payment_gateway, $posted_data){

            global $BookingPress, $wpdb, $tbl_bookingpress_entries, $bookingpress_debug_payment_log_id, $bookingpress_coupons, $tbl_bookingpress_appointment_meta, $tbl_bookingpress_extra_services, $bookingpress_pro_staff_members, $tbl_bookingpress_staffmembers, $bookingpress_deposit_payment, $tbl_bookingpress_staffmembers_services, $bookingpress_other_debug_log_id;
            $is_recurring_appointments = (isset($posted_data['is_recurring_appointments']))?sanitize_text_field($posted_data['is_recurring_appointments']):'';            
            $recurring_appointments = (isset($posted_data['recurring_appointments']))?$posted_data['recurring_appointments']:'';
            

            $return_data = array(
				'service_data'     => array(),
				'payable_amount'   => 0,
				'customer_details' => array(),
				'currency'         => '',
			);

            if($is_recurring_appointments == 'true'){
                if(!isset($bookingpress_appointment_data['recurring_appointments'])){
                    $bookingpress_appointment_data =  $posted_data;   
                }
                if(!empty($bookingpress_appointment_data) && !empty($bookingpress_appointment_data) && !empty($posted_data) && !empty($recurring_appointments) ){
                    
                    $bookingpress_selected_service_id     = sanitize_text_field( $bookingpress_appointment_data['selected_service'] );
                    $bookingpress_appointment_booked_date = sanitize_text_field( $bookingpress_appointment_data['selected_date'] );
                    $bookingpress_selected_start_time     = sanitize_text_field( $bookingpress_appointment_data['selected_start_time'] );
                    $bookingpress_selected_end_time       = sanitize_text_field($bookingpress_appointment_data['selected_end_time']);
                    if( !empty( $bookingpress_timeslot_display_in_client_timezone ) && 'true' == $bookingpress_timeslot_display_in_client_timezone ){
                        $bookingpress_appointment_booked_date = !empty( $bookingpress_appointment_data['store_selected_date'] ) ? sanitize_text_field( $bookingpress_appointment_data['store_selected_date'] ) : $bookingpress_appointment_booked_date;
                        $bookingpress_selected_start_time = !empty( $bookingpress_appointment_data['store_start_time'] ) ? sanitize_text_field( $bookingpress_appointment_data['store_start_time'] ) : $bookingpress_selected_start_time;
                        $bookingpress_selected_end_time = !empty( $bookingpress_appointment_data['store_end_time'] ) ? sanitize_text_field( $bookingpress_appointment_data['store_end_time'] ) : $bookingpress_selected_end_time;                            
                    }
                    $bookingpress_internal_note = '';
                    if( isset ( $bookingpress_appointment_data['appointment_note'] ) ){
    
                        $bookingpress_internal_note           = !empty( $bookingpress_appointment_data['appointment_note'] ) ? sanitize_textarea_field( $bookingpress_appointment_data['appointment_note'] ) : $bookingpress_appointment_data['form_fields']['appointment_note'];
                    }
    
                    $bookingpress_check_deposit_payment_module_activation = $bookingpress_deposit_payment->bookingpress_check_deposit_payment_module_activation();
                    $bookingpress_check_coupon_module_activation = $bookingpress_coupons->bookingpress_check_coupon_module_activation();

                    $service_data                         = $BookingPress->get_service_by_id( $bookingpress_selected_service_id );
                    $bookingpress_service_price = $service_data['bookingpress_service_price'];
                    $service_duration_vals                = $this->bookingpress_get_service_end_time( $bookingpress_selected_service_id, $bookingpress_selected_start_time,$service_data);
                    $service_data['service_start_time']   = sanitize_text_field( $service_duration_vals['service_start_time'] );
                    $service_data['service_end_time']     = sanitize_text_field( $service_duration_vals['service_end_time'] );
                    $return_data['service_data']          = $service_data;
    
                    $bookingpress_currency_name   = $BookingPress->bookingpress_get_settings( 'payment_default_currency', 'payment_setting' );
                    $return_data['currency']      = $bookingpress_currency_name;
                    $return_data['currency_code'] = $BookingPress->bookingpress_get_currency_code( $bookingpress_currency_name );
    
                    $__payable_amount              = $bookingpress_appointment_data['total_payable_amount'];
                    $bookingpress_due_amount = 0;
    
                    if ( $__payable_amount == 0 ) {
                        $payment_gateway = ' - ';
                    }
    
                    
                    $customer_email     = !empty($bookingpress_appointment_data['form_fields']['customer_email']) ? $bookingpress_appointment_data['form_fields']['customer_email'] : $bookingpress_appointment_data['customer_email'];
                    $customer_full_name  = !empty( $bookingpress_appointment_data['form_fields']['customer_name'] ) ? sanitize_text_field( $bookingpress_appointment_data['form_fields']['customer_name'] ) : (!empty( $bookingpress_appointment_data['customer_name'] ) ? sanitize_text_field($bookingpress_appointment_data['customer_name'] ) : '');
                    $customer_username  = !empty( $bookingpress_appointment_data['form_fields']['customer_username'] ) ? sanitize_text_field( $bookingpress_appointment_data['form_fields']['customer_username'] ) : (!empty( $bookingpress_appointment_data['customer_username'] ) ? sanitize_text_field($bookingpress_appointment_data['customer_username'] ) : '');
                    $customer_firstname = !empty( $bookingpress_appointment_data['form_fields']['customer_firstname'] ) ? sanitize_text_field( $bookingpress_appointment_data['form_fields']['customer_firstname'] ) : (!empty($bookingpress_appointment_data['customer_firstname']) ? sanitize_text_field($bookingpress_appointment_data['customer_firstname'] ) : '');
                    $customer_lastname  = !empty( $bookingpress_appointment_data['form_fields']['customer_lastname'] ) ? sanitize_text_field( $bookingpress_appointment_data['form_fields']['customer_lastname'] ) : (!empty($bookingpress_appointment_data['customer_lastname']) ? sanitize_text_field($bookingpress_appointment_data['customer_lastname'] ) : '');
                    $customer_phone     = !empty( $bookingpress_appointment_data['form_fields']['customer_phone'] ) ? sanitize_text_field( $bookingpress_appointment_data['form_fields']['customer_phone'] ) : ( !empty($bookingpress_appointment_data['customer_phone']) ? sanitize_text_field($bookingpress_appointment_data['customer_phone'] ) : '' );
                    $customer_country   = !empty( $bookingpress_appointment_data['form_fields']['customer_phone_country'] ) ? sanitize_text_field( $bookingpress_appointment_data['form_fields']['customer_phone_country'] ) : ( !empty($bookingpress_appointment_data['customer_phone_country']) ? sanitize_text_field($bookingpress_appointment_data['customer_phone_country'] ) : '');
                    $customer_phone_dial_code = !empty($bookingpress_appointment_data['customer_phone_dial_code']) ? $bookingpress_appointment_data['customer_phone_dial_code'] : '';
                    $customer_timezone = !empty($bookingpress_appointment_data['bookingpress_customer_timezone']) ? $bookingpress_appointment_data['bookingpress_customer_timezone'] : wp_timezone_string();
    
                    $customer_dst_timezone = !empty( $bookingpress_appointment_data['client_dst_timezone'] ) ? intval( $bookingpress_appointment_data['client_dst_timezone'] ) : 0;
    
                    if( !empty($customer_phone) && !empty( $customer_phone_dial_code) ){
    
                        $customer_phone_pattern = '/(^\+'.$customer_phone_dial_code.')/';
                        if( preg_match($customer_phone_pattern, $customer_phone) ){
                            $customer_phone = preg_replace( $customer_phone_pattern, '', $customer_phone) ;
                        }
                    }
    
                    $return_data['customer_details'] = array(
                        'customer_firstname' => $customer_firstname,
                        'customer_lastname'  => $customer_lastname,
                        'customer_email'     => $customer_email,
                        'customer_username'  => !empty($customer_username) ? $customer_username : $customer_full_name,
                        'customer_phone'     => $customer_phone,
                    );
    
                    $return_data['card_details'] = array(
                        'card_holder_name' => $bookingpress_appointment_data['card_holder_name'],
                        'card_number'      => $bookingpress_appointment_data['card_number'],
                        'expire_month'     => $bookingpress_appointment_data['expire_month'],
                        'expire_year'      => $bookingpress_appointment_data['expire_year'],
                        'cvv'              => $bookingpress_appointment_data['cvv'],
                    );
    
                    $bookingpress_appointment_status = $BookingPress->bookingpress_get_settings( 'appointment_status', 'general_setting' );
    
                    if ( $payment_gateway == 'on-site' ) {
                        $bookingpress_appointment_status = $BookingPress->bookingpress_get_settings('onsite_appointment_status', 'general_setting');
                    }
    
                    $bookingpress_customer_id = get_current_user_id();
    
                    $bookingpress_deposit_selected_type = "";
                    $bookingpress_deposit_selected_amount = 0;
                    $bookingpress_deposit_details = array();

                    if($payment_gateway != "on-site" && $payment_gateway != " - " && $bookingpress_check_deposit_payment_module_activation && !empty($bookingpress_appointment_data['bookingpress_deposit_payment_method']) && ($bookingpress_appointment_data['bookingpress_deposit_payment_method'] == "deposit_or_full_price") ){
                        
                        $bookingpress_deposit_selected_type = !empty($bookingpress_appointment_data['deposit_payment_type']) ? $bookingpress_appointment_data['deposit_payment_type'] : '';
                        $bookingpress_deposit_selected_amount = !empty($bookingpress_appointment_data['bookingpress_single_deposit_price']) ? floatval($bookingpress_appointment_data['bookingpress_single_deposit_price']) : 0;
                        $bookingpress_due_amount = !empty($bookingpress_appointment_data['bookingpress_single_deposit_due_amount']) ? floatval($bookingpress_appointment_data['bookingpress_single_deposit_due_amount']) : 0;
                        if(!empty($bookingpress_deposit_selected_amount)){
                            $__payable_amount = $bookingpress_deposit_selected_amount;
                        }                        
                        $bookingpress_deposit_details = array(
                            'deposit_selected_type' => $bookingpress_deposit_selected_type,
                            'deposit_amount' => $bookingpress_deposit_selected_amount,
                            'deposit_due_amount' => $bookingpress_due_amount,
                        );

                    }
                    
                    

                    // Apply coupon if coupon module enabled
                    $bookingpress_coupon_code         = ! empty( $bookingpress_appointment_data['coupon_code'] ) ? $bookingpress_appointment_data['coupon_code'] : '';
                    $discounted_amount                = !empty($bookingpress_appointment_data['coupon_discount_amount']) ? floatval($bookingpress_appointment_data['coupon_discount_amount']) : 0;
                    $bookingpress_is_coupon_applied   = 0;
                    $bookingpress_applied_coupon_data = array();

                    if ( $bookingpress_check_coupon_module_activation && ! empty( $bookingpress_coupon_code )) {
                        $bookingpress_applied_coupon_data = ! empty( $bookingpress_appointment_data['applied_coupon_res'] ) ? $bookingpress_appointment_data['applied_coupon_res'] : array();
                        $bookingpress_applied_coupon_data['coupon_discount_amount'] = $discounted_amount;
                        $bookingpress_is_coupon_applied = 1;
                    }

                    if($payment_gateway != "on-site" && $payment_gateway != " - " && $bookingpress_check_deposit_payment_module_activation && !empty($bookingpress_appointment_data['bookingpress_deposit_payment_method']) && ($bookingpress_appointment_data['bookingpress_deposit_payment_method'] == "deposit_or_full_price" ) ){
                        $__payable_amount = $bookingpress_appointment_data['bookingpress_deposit_total'];
                        $bookingpress_deposit_due_amount_total = $bookingpress_appointment_data['bookingpress_deposit_due_amount_total'];                        
                    }

                    $return_data['payable_amount'] = (float) $__payable_amount;
    
                    
    
                    // Apply coupon if coupon module enabled
                    $bookingpress_coupon_code         = ! empty( $bookingpress_appointment_data['coupon_code'] ) ? $bookingpress_appointment_data['coupon_code'] : '';
                    $discounted_amount                = !empty($bookingpress_appointment_data['coupon_discount_amount']) ? floatval($bookingpress_appointment_data['coupon_discount_amount']) : 0;
                    $bookingpress_is_coupon_applied   = 0;
                    $bookingpress_applied_coupon_data = array();    
                    if ( $bookingpress_check_coupon_module_activation && ! empty( $bookingpress_coupon_code )) {
                        $bookingpress_applied_coupon_data = ! empty( $bookingpress_appointment_data['applied_coupon_res'] ) ? $bookingpress_appointment_data['applied_coupon_res'] : array();
                        $bookingpress_applied_coupon_data['coupon_discount_amount'] = $discounted_amount;
                        $bookingpress_is_coupon_applied = 1;
                    }    
                    $bookingpress_selected_extra_members = !empty($bookingpress_appointment_data['bookingpress_selected_bring_members']) ? $bookingpress_appointment_data['bookingpress_selected_bring_members'] : 1;    
                    $bookingpress_extra_services = !empty($bookingpress_appointment_data['bookingpress_selected_extra_details']) ? $bookingpress_appointment_data['bookingpress_selected_extra_details'] : array();
                    $bookingpress_extra_services_db_details = array();

                    if(!empty($bookingpress_extra_services)){
                        foreach($bookingpress_extra_services as $k => $v){
                            if($v['bookingpress_is_selected'] == "true"){
                                $bookingpress_extra_service_id = intval($k);
                                $bookingpress_extra_service_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_extra_services} WHERE bookingpress_extra_services_id = %d", $bookingpress_extra_service_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_extra_services is a table name. false alarm
    
                                if(!empty($bookingpress_extra_service_details)){
                                    $bookingpress_extra_service_price = ! empty( $bookingpress_extra_service_details['bookingpress_extra_service_price'] ) ? floatval( $bookingpress_extra_service_details['bookingpress_extra_service_price'] ) : 0;
    
                                    $bookingpress_selected_qty = !empty($v['bookingpress_selected_qty']) ? intval($v['bookingpress_selected_qty']) : 1;
    
                                    if(!empty($bookingpress_selected_qty)){
                                        $bookingpress_final_price = $bookingpress_extra_service_price * $bookingpress_selected_qty;
                                        $v['bookingpress_final_payable_price'] = $bookingpress_final_price;
                                        $v['bookingpress_extra_service_details'] = $bookingpress_extra_service_details;
                                        array_push($bookingpress_extra_services_db_details, $v);
                                    }
                                }
                            }
                        }
                    }        
                    $bookingpress_selected_staffmember = 0;
                    $bookingpress_is_any_staff_selected = 0;
                    $bookingpress_staff_member_firstname = "";
                    $bookingpress_staff_member_lastname = "";
                    $bookingpress_staff_member_email_address = "";
                    $bookingpress_staffmember_price = 0;
                    $bookingpress_staffmember_details = array();
                    if($bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation()){
                        $bookingpress_selected_staffmember = !empty($bookingpress_appointment_data['bookingpress_selected_staff_member_details']['selected_staff_member_id']) ? $bookingpress_appointment_data['bookingpress_selected_staff_member_details']['selected_staff_member_id'] : 0;
                        $bookingpress_is_any_staff_selected = !empty($bookingpress_appointment_data['bookingpress_selected_staff_member_details']['is_any_staff_option_selected']) ? 1 : 0;
                        if(!empty($bookingpress_selected_staffmember)){
                            $bookingpress_staffmember_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_staffmember_id = %d", $bookingpress_selected_staffmember), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is table name.
                            $bookingpress_staff_member_firstname = !empty($bookingpress_staffmember_details['bookingpress_staffmember_firstname']) ? $bookingpress_staffmember_details['bookingpress_staffmember_firstname'] : '';
                            $bookingpress_staff_member_lastname = !empty($bookingpress_staffmember_details['bookingpress_staffmember_lastname']) ? $bookingpress_staffmember_details['bookingpress_staffmember_lastname'] : '';
                            $bookingpress_staff_member_email_address = !empty($bookingpress_staffmember_details['bookingpress_staffmember_email']) ? $bookingpress_staffmember_details['bookingpress_staffmember_email'] : '';
                            $bookingpress_staffmember_details['is_any_staff_selected'] = $bookingpress_is_any_staff_selected;    
                            /* Fetch staff member price */
                            $bookingpress_staffmember_price_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_staffmembers_services} WHERE bookingpress_staffmember_id = %d AND bookingpress_service_id = %d", $bookingpress_selected_staffmember, $bookingpress_selected_service_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_services is table name.
                            $bookingpress_staffmember_price = !empty($bookingpress_staffmember_price_details['bookingpress_service_price']) ? floatval($bookingpress_staffmember_price_details['bookingpress_service_price']) : 0;
                        }
                    }
                    $bookingpress_total_amount = $bookingpress_appointment_data['total_payable_amount'];   
                    
                
                        
                    /* Get cart order id */
                    $bookingpress_cart_order_id = get_option('bookingpress_cart_order_id', true);
                    if(empty($bookingpress_cart_order_id)){
                        $bookingpress_cart_order_id = 1;
                    }else{
                        $bookingpress_cart_order_id = floatval($bookingpress_cart_order_id) + 1;
                    }
                    update_option('bookingpress_cart_order_id', $bookingpress_cart_order_id);                    
                    $bookingpress_timeslot_display_in_client_timezone = $BookingPress->bookingpress_get_settings( 'show_bookingslots_in_client_timezone', 'general_setting' );

                    $bookingpress_entry_details = array(
                        'bookingpress_customer_id'                    => $bookingpress_customer_id,
                        'bookingpress_order_id'                       => $bookingpress_cart_order_id,
                        'bookingpress_is_cart'                        => 0,
                        'bookingpress_is_recurring'                   => 1,
                        'bookingpress_customer_name'                  => $customer_full_name,
                        'bookingpress_username'                       => $customer_username,
                        'bookingpress_customer_phone'                 => $customer_phone,
                        'bookingpress_customer_firstname'             => $customer_firstname,
                        'bookingpress_customer_lastname'              => $customer_lastname,
                        'bookingpress_customer_country'               => $customer_country,
                        'bookingpress_customer_phone_dial_code'       => $customer_phone_dial_code,
                        'bookingpress_customer_email'                 => $customer_email,
                        'bookingpress_customer_timezone'              => $customer_timezone,
                        'bookingpress_dst_timezone'					  => $customer_dst_timezone,
                        'bookingpress_service_id'                     => $bookingpress_selected_service_id,
                        'bookingpress_service_name'                   => $service_data['bookingpress_service_name'],
                        'bookingpress_service_price'                  => $bookingpress_service_price,
                        'bookingpress_service_currency'               => $bookingpress_currency_name,
                        'bookingpress_service_duration_val'           => $service_data['bookingpress_service_duration_val'],
                        'bookingpress_service_duration_unit'          => isset($service_data['bookingpress_service_duration_unit']) ? $service_data['bookingpress_service_duration_unit'] :'',
                        'bookingpress_payment_gateway'                => $payment_gateway,
                        'bookingpress_appointment_date'               => $bookingpress_appointment_booked_date,
                        'bookingpress_appointment_time'               => $bookingpress_selected_start_time,
                        'bookingpress_appointment_end_time'  		  => $bookingpress_selected_end_time,
                        'bookingpress_appointment_internal_note'      => $bookingpress_internal_note,
                        'bookingpress_appointment_send_notifications' => 1,
                        'bookingpress_appointment_status'             => $bookingpress_appointment_status,
                        'bookingpress_coupon_details'                 => wp_json_encode( $bookingpress_applied_coupon_data ),
                        'bookingpress_coupon_discount_amount'         => $discounted_amount,
                        'bookingpress_deposit_payment_details'        => wp_json_encode( $bookingpress_deposit_details ),
                        'bookingpress_deposit_amount'                 => $bookingpress_deposit_selected_amount,
                        'bookingpress_selected_extra_members'         => $bookingpress_selected_extra_members,
                        'bookingpress_extra_service_details'          => wp_json_encode( $bookingpress_extra_services_db_details ),
                        'bookingpress_staff_member_id'                => $bookingpress_selected_staffmember,
                        'bookingpress_staff_member_price'             => $bookingpress_staffmember_price,
                        'bookingpress_staff_first_name'               => $bookingpress_staff_member_firstname,
                        'bookingpress_staff_last_name'                => $bookingpress_staff_member_lastname,
                        'bookingpress_staff_email_address'            => $bookingpress_staff_member_email_address,
                        'bookingpress_staff_member_details'           => wp_json_encode($bookingpress_staffmember_details),
                        'bookingpress_paid_amount'                    => $__payable_amount,
                        'bookingpress_due_amount'                     => $bookingpress_due_amount,
                        'bookingpress_total_amount'                   => $bookingpress_total_amount,
                        'bookingpress_created_at'                     => current_time( 'mysql' ),
                    );

                    $bookingpress_recurring_object = array();
                    foreach($recurring_appointments as $rec_index=>$recurring_app){

                        //$service_data                         = $BookingPress->get_service_by_id( $bookingpress_selected_service_id );
                        $bookingpress_service_price = $service_data['bookingpress_service_price'];
                        $bookingpress_selected_start_time = date('H:i:s', strtotime($recurring_app['selected_start_time']));
                        $service_duration_vals                = $this->bookingpress_get_service_end_time( $bookingpress_selected_service_id, $bookingpress_selected_start_time,$service_data);
                        $service_data['service_start_time']   = sanitize_text_field( $service_duration_vals['service_start_time'] );
                        $service_data['service_end_time']     = sanitize_text_field( $service_duration_vals['service_end_time'] );
                        array_push($return_data['service_data'], $service_data);

                        $selected_end_time = date('H:i:s', strtotime($recurring_app['selected_end_time']));
                        if($selected_end_time == "00:00:00"){
                            $selected_end_time = "24:00:00";
                        }
                        $bookingpress_entry_details['bookingpress_appointment_time'] = date('H:i:s', strtotime($recurring_app['selected_start_time']));
                        $bookingpress_entry_details['bookingpress_appointment_end_time'] = $selected_end_time; 
                        $bookingpress_entry_details['bookingpress_appointment_date'] =  $recurring_app['selected_date'];                        
                        $bookingpress_entry_details = apply_filters( 'bookingpress_modify_entry_data_before_insert', $bookingpress_entry_details, $posted_data );

                        do_action( 'bookingpress_payment_log_entry', $payment_gateway, 'submit appointment form front', 'bookingpress pro', $bookingpress_entry_details, $bookingpress_debug_payment_log_id );

                        $wpdb->insert( $tbl_bookingpress_entries, $bookingpress_entry_details );
                        $entry_id = $wpdb->insert_id;

                        do_action( 'bookingpress_after_entry_data_insert', $entry_id, $bookingpress_appointment_data);                                                
                    }
                    
                    $return_data['entry_id'] = $bookingpress_cart_order_id;
                    $return_data['is_cart'] = 1;
                    $return_data['booking_form_redirection_mode'] = $bookingpress_appointment_data['booking_form_redirection_mode'];
                    $bookingpress_uniq_id = $bookingpress_appointment_data['bookingpress_uniq_id'];                    

                    setcookie("bookingpress_last_request_id", "", time()-3600, "/");
                    setcookie("bookingpress_referer_url", "", time() - 3600, "/");
                    setcookie("bookingpress_cart_id","", time()+(86400), "/");
                    if(session_id() == '' OR session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    $_SESSION['bookingpress_cart_'.$bookingpress_uniq_id.'_data'] = json_encode($recurring_appointments); 
                    
                    $bookingpress_referer_url = (wp_get_referer()) ? wp_get_referer() : BOOKINGPRESS_HOME_URL;
                    setcookie("bookingpress_last_request_id", $bookingpress_uniq_id, time()+(86400), "/");
                    setcookie("bookingpress_referer_url", $bookingpress_referer_url, time()+(86400), "/");
                    setcookie("bookingpress_cart_id", base64_encode($bookingpress_cart_order_id), time()+(86400), "/");
                    setcookie('bookingpress_single_booking_id',"", time()-(86400), "/");
                    
                    $bookingpress_after_approved_payment_page_id = $BookingPress->bookingpress_get_customize_settings( 'after_booking_redirection', 'booking_form' );
                    $bookingpress_after_approved_payment_url     = get_permalink( $bookingpress_after_approved_payment_page_id );
    
                    $bookingpress_after_canceled_payment_page_id = $BookingPress->bookingpress_get_customize_settings( 'after_failed_payment_redirection', 'booking_form' );
                    $bookingpress_after_canceled_payment_url     = get_permalink( $bookingpress_after_canceled_payment_page_id );
    
                    $bookingpress_entry_hash = md5($bookingpress_cart_order_id);
    
                    if( !empty($bookingpress_appointment_data['booking_form_redirection_mode']) && $bookingpress_appointment_data['booking_form_redirection_mode'] == "in-built" ){
                        $bookingpress_approved_appointment_url = $bookingpress_canceled_appointment_url = $bookingpress_referer_url;
                        $bookingpress_approved_appointment_url = add_query_arg('is_success', 1, $bookingpress_after_approved_payment_url);
                        $bookingpress_approved_appointment_url = add_query_arg('appointment_id', base64_encode($bookingpress_cart_order_id), $bookingpress_approved_appointment_url);
                        $bookingpress_approved_appointment_url = add_query_arg( 'bp_tp_nonce', wp_create_nonce( 'bpa_nonce_url-'.$bookingpress_entry_hash ), $bookingpress_approved_appointment_url );
    
                        $bookingpress_canceled_appointment_url = add_query_arg('is_success', 2, $bookingpress_canceled_appointment_url);
                        $bookingpress_canceled_appointment_url = add_query_arg('appointment_id', base64_encode($bookingpress_cart_order_id), $bookingpress_canceled_appointment_url);
                        $bookingpress_canceled_appointment_url = add_query_arg( 'bp_tp_nonce', wp_create_nonce( 'bpa_nonce_url-'.$bookingpress_entry_hash ), $bookingpress_canceled_appointment_url );
    
                        $return_data['approved_appointment_url'] = $bookingpress_approved_appointment_url;
                        $return_data['pending_appointment_url'] = $return_data['approved_appointment_url'];
                        $return_data['canceled_appointment_url'] = $bookingpress_canceled_appointment_url;
                    }else{
                        $bookingpress_after_approved_payment_url = ! empty( $bookingpress_after_approved_payment_url ) ? $bookingpress_after_approved_payment_url : BOOKINGPRESS_HOME_URL;
                        $bookingpress_after_approved_payment_url = add_query_arg('appointment_id', base64_encode($bookingpress_cart_order_id), $bookingpress_after_approved_payment_url);
                        $bookingpress_after_approved_payment_url = add_query_arg( 'bp_tp_nonce', wp_create_nonce( 'bpa_nonce_url-'.$bookingpress_entry_hash ), $bookingpress_after_approved_payment_url );
                        $return_data['approved_appointment_url'] = $bookingpress_after_approved_payment_url;
    
                        $bookingpress_after_canceled_payment_url = ! empty( $bookingpress_after_canceled_payment_url ) ? $bookingpress_after_canceled_payment_url : BOOKINGPRESS_HOME_URL;
                        $bookingpress_after_canceled_payment_url = add_query_arg('appointment_id', base64_encode($bookingpress_cart_order_id), $bookingpress_after_canceled_payment_url);
                        $return_data['canceled_appointment_url'] = $bookingpress_after_canceled_payment_url;
                        
                        $return_data['pending_appointment_url'] = $return_data['approved_appointment_url'];
                    }
    
                    $return_data['approved_appointment_url'] = add_query_arg('is_cart', 1, $return_data['approved_appointment_url']);
                    $return_data['pending_appointment_url'] = add_query_arg('is_cart', 1, $return_data['pending_appointment_url']);
                    $return_data['canceled_appointment_url'] = add_query_arg('is_cart', 1, $return_data['canceled_appointment_url']);
    
                    $bookingpress_notify_url   = BOOKINGPRESS_HOME_URL . '/?bookingpress-listener=bpa_pro_' . $payment_gateway . '_url';
                    $return_data['notify_url'] = $bookingpress_notify_url;
    
                    $return_data = apply_filters( 'bookingpress_add_modify_validate_submit_form_data', $return_data, $payment_gateway, $posted_data );
    
                    //Enter data in appointment meta table
                    //------------------------------
                    $bookingpress_appointment_meta_details = $bookingpress_appointment_data;
                    $bookingpress_db_fields = array(
                        'bookingpress_order_id' => $bookingpress_cart_order_id,
                        'bookingpress_appointment_id' => 0,
                        'bookingpress_appointment_meta_key' => 'appointment_details',
                        'bookingpress_appointment_meta_value' => wp_json_encode($bookingpress_appointment_meta_details),
                    );
                    $wpdb->insert($tbl_bookingpress_appointment_meta, $bookingpress_db_fields);

                    do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Cart appointment meta data', 'bookingpress_cart_submit_booking_request', $bookingpress_db_fields, $bookingpress_other_debug_log_id );


                }  
                
                return $return_data;
            }else{
                return $bookingpress_appointment_data;
            }            

            return $bookingpress_appointment_data;
        }

        /**
         * Get service end time based start time
         *
         * @param  mixed $service_id
         * @param  mixed $service_start_time
         * @param  mixed $service_duration_val
         * @param  mixed $service_duration_unit
         * @return void
         */
        public function bookingpress_get_service_end_time( $service_id, $service_start_time, $service_data = array() )
        {
            global $wpdb, $tbl_bookingpress_services;
            if (! empty($service_id) && !empty($service_data) ) {
                
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_services is table name defined globally. False Positive alarm
                //$service_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = %d", $service_id), ARRAY_A);
                
                $service_duration      = $service_data['bookingpress_service_duration_val'];
                $service_unit_duration = $service_data['bookingpress_service_duration_unit'];                
                $service_mins = $service_duration;
                if ($service_unit_duration == 'h' ) {
                    $service_mins = $service_duration * 60;
                }

                $service_end_time_obj = new DateTime($service_start_time);
                $service_end_time_obj->add(new DateInterval('PT' . $service_mins . 'M'));
                $service_end_time = $service_end_time_obj->format('H:i');

                return array(
                'service_start_time' => $service_start_time,
                'service_end_time'   => $service_end_time,
                );

            }

            return array();
        }        

        /**
         * Function for add bookingpress_entries table data add
         *
         * @param  mixed $bookingpress_appointment_data
         * @param  mixed $payment_gateway
         * @param  mixed $posted_data
         * @return void
         */
        function bookingpress_add_recurring_entries_data_func_old_backup_28_08_23($bookingpress_appointment_data, $payment_gateway, $posted_data){

            global $BookingPress, $wpdb, $tbl_bookingpress_entries, $bookingpress_debug_payment_log_id, $bookingpress_coupons, $tbl_bookingpress_appointment_meta, $tbl_bookingpress_extra_services, $bookingpress_pro_staff_members, $tbl_bookingpress_staffmembers, $bookingpress_deposit_payment, $tbl_bookingpress_staffmembers_services, $bookingpress_other_debug_log_id;
            $is_recurring_appointments = (isset($posted_data['is_recurring_appointments']))?sanitize_text_field($posted_data['is_recurring_appointments']):'';            
            $recurring_appointments = (isset($bookingpress_appointment_data['recurring_appointments']))?$bookingpress_appointment_data['recurring_appointments']:'';
                        
            

            $return_data = array(
				'service_data'     => array(),
				'payable_amount'   => 0,
				'customer_details' => array(),
				'currency'         => '',
			);

            if($is_recurring_appointments == 'true'){

                if(!empty($bookingpress_appointment_data) && !empty($bookingpress_appointment_data) && !empty($posted_data) && !empty($recurring_appointments) ){
                    
                    $bookingpress_selected_service_id     = sanitize_text_field( $bookingpress_appointment_data['selected_service'] );
                    $bookingpress_appointment_booked_date = sanitize_text_field( $bookingpress_appointment_data['selected_date'] );
                    $bookingpress_selected_start_time     = sanitize_text_field( $bookingpress_appointment_data['selected_start_time'] );
                    $bookingpress_selected_end_time       = sanitize_text_field($bookingpress_appointment_data['selected_end_time']);
                    if( !empty( $bookingpress_timeslot_display_in_client_timezone ) && 'true' == $bookingpress_timeslot_display_in_client_timezone ){
                        $bookingpress_appointment_booked_date = !empty( $bookingpress_appointment_data['store_selected_date'] ) ? sanitize_text_field( $bookingpress_appointment_data['store_selected_date'] ) : $bookingpress_appointment_booked_date;
                        $bookingpress_selected_start_time = !empty( $bookingpress_appointment_data['store_start_time'] ) ? sanitize_text_field( $bookingpress_appointment_data['store_start_time'] ) : $bookingpress_selected_start_time;
                        $bookingpress_selected_end_time = !empty( $bookingpress_appointment_data['store_end_time'] ) ? sanitize_text_field( $bookingpress_appointment_data['store_end_time'] ) : $bookingpress_selected_end_time;                            
                    }
                    $bookingpress_internal_note = '';
                    if( isset ( $bookingpress_appointment_data['appointment_note'] ) ){
    
                        $bookingpress_internal_note           = !empty( $bookingpress_appointment_data['appointment_note'] ) ? sanitize_textarea_field( $bookingpress_appointment_data['appointment_note'] ) : $bookingpress_appointment_data['form_fields']['appointment_note'];
                    }
    
                    $service_data                         = $BookingPress->get_service_by_id( $bookingpress_selected_service_id );
                    $bookingpress_service_price = $service_data['bookingpress_service_price'];
                    $service_duration_vals                = $BookingPress->bookingpress_get_service_end_time( $bookingpress_selected_service_id, $bookingpress_selected_start_time );
                    $service_data['service_start_time']   = sanitize_text_field( $service_duration_vals['service_start_time'] );
                    $service_data['service_end_time']     = sanitize_text_field( $service_duration_vals['service_end_time'] );
                    $return_data['service_data']          = $service_data;
    
                    $bookingpress_currency_name   = $BookingPress->bookingpress_get_settings( 'payment_default_currency', 'payment_setting' );
                    $return_data['currency']      = $bookingpress_currency_name;
                    $return_data['currency_code'] = $BookingPress->bookingpress_get_currency_code( $bookingpress_currency_name );
    
                    $__payable_amount              = $bookingpress_appointment_data['total_payable_amount'];
                    $bookingpress_due_amount = 0;
    
                    if ( $__payable_amount == 0 ) {
                        $payment_gateway = ' - ';
                    }
    
                    //echo "Payable amount ===>".$__payable_amount ;
                    $customer_email     = !empty($bookingpress_appointment_data['form_fields']['customer_email']) ? $bookingpress_appointment_data['form_fields']['customer_email'] : $bookingpress_appointment_data['customer_email'];
                    $customer_full_name  = !empty( $bookingpress_appointment_data['form_fields']['customer_name'] ) ? sanitize_text_field( $bookingpress_appointment_data['form_fields']['customer_name'] ) : (!empty( $bookingpress_appointment_data['customer_name'] ) ? sanitize_text_field($bookingpress_appointment_data['customer_name'] ) : '');
                    $customer_username  = !empty( $bookingpress_appointment_data['form_fields']['customer_username'] ) ? sanitize_text_field( $bookingpress_appointment_data['form_fields']['customer_username'] ) : (!empty( $bookingpress_appointment_data['customer_username'] ) ? sanitize_text_field($bookingpress_appointment_data['customer_username'] ) : '');
                    $customer_firstname = !empty( $bookingpress_appointment_data['form_fields']['customer_firstname'] ) ? sanitize_text_field( $bookingpress_appointment_data['form_fields']['customer_firstname'] ) : (!empty($bookingpress_appointment_data['customer_firstname']) ? sanitize_text_field($bookingpress_appointment_data['customer_firstname'] ) : '');
                    $customer_lastname  = !empty( $bookingpress_appointment_data['form_fields']['customer_lastname'] ) ? sanitize_text_field( $bookingpress_appointment_data['form_fields']['customer_lastname'] ) : (!empty($bookingpress_appointment_data['customer_lastname']) ? sanitize_text_field($bookingpress_appointment_data['customer_lastname'] ) : '');
                    $customer_phone     = !empty( $bookingpress_appointment_data['form_fields']['customer_phone'] ) ? sanitize_text_field( $bookingpress_appointment_data['form_fields']['customer_phone'] ) : ( !empty($bookingpress_appointment_data['customer_phone']) ? sanitize_text_field($bookingpress_appointment_data['customer_phone'] ) : '' );
                    $customer_country   = !empty( $bookingpress_appointment_data['form_fields']['customer_phone_country'] ) ? sanitize_text_field( $bookingpress_appointment_data['form_fields']['customer_phone_country'] ) : ( !empty($bookingpress_appointment_data['customer_phone_country']) ? sanitize_text_field($bookingpress_appointment_data['customer_phone_country'] ) : '');
                    $customer_phone_dial_code = !empty($bookingpress_appointment_data['customer_phone_dial_code']) ? $bookingpress_appointment_data['customer_phone_dial_code'] : '';
                    $customer_timezone = !empty($bookingpress_appointment_data['bookingpress_customer_timezone']) ? $bookingpress_appointment_data['bookingpress_customer_timezone'] : wp_timezone_string();
    
                    $customer_dst_timezone = !empty( $bookingpress_appointment_data['client_dst_timezone'] ) ? intval( $bookingpress_appointment_data['client_dst_timezone'] ) : 0;
    
                    if( !empty($customer_phone) && !empty( $customer_phone_dial_code) ){
    
                        $customer_phone_pattern = '/(^\+'.$customer_phone_dial_code.')/';
                        if( preg_match($customer_phone_pattern, $customer_phone) ){
                            $customer_phone = preg_replace( $customer_phone_pattern, '', $customer_phone) ;
                        }
                    }
    
                    $return_data['customer_details'] = array(
                        'customer_firstname' => $customer_firstname,
                        'customer_lastname'  => $customer_lastname,
                        'customer_email'     => $customer_email,
                        'customer_username'  => !empty($customer_username) ? $customer_username : $customer_full_name,
                        'customer_phone'     => $customer_phone,
                    );
    
                    $return_data['card_details'] = array(
                        'card_holder_name' => $bookingpress_appointment_data['card_holder_name'],
                        'card_number'      => $bookingpress_appointment_data['card_number'],
                        'expire_month'     => $bookingpress_appointment_data['expire_month'],
                        'expire_year'      => $bookingpress_appointment_data['expire_year'],
                        'cvv'              => $bookingpress_appointment_data['cvv'],
                    );
    
                    $bookingpress_appointment_status = $BookingPress->bookingpress_get_settings( 'appointment_status', 'general_setting' );
    
                    if ( $payment_gateway == 'on-site' ) {
                        $bookingpress_appointment_status = $BookingPress->bookingpress_get_settings('onsite_appointment_status', 'general_setting');
                    }
    
                    $bookingpress_customer_id = get_current_user_id();
    
                    $bookingpress_deposit_selected_type = "";
                    $bookingpress_deposit_selected_amount = 0;
                    $bookingpress_deposit_details = array();
                    if($payment_gateway != "on-site" && $payment_gateway != " - " && $bookingpress_deposit_payment->bookingpress_check_deposit_payment_module_activation() && !empty($bookingpress_appointment_data['bookingpress_deposit_payment_method']) && ($bookingpress_appointment_data['bookingpress_deposit_payment_method'] == "deposit_or_full_price") ){
                        $bookingpress_deposit_selected_type = !empty($bookingpress_appointment_data['deposit_payment_type']) ? $bookingpress_appointment_data['deposit_payment_type'] : '';
                        $bookingpress_deposit_selected_amount = !empty($bookingpress_appointment_data['bookingpress_deposit_amt_without_currency']) ? floatval($bookingpress_appointment_data['bookingpress_deposit_amt_without_currency']) : 0;
                        $bookingpress_due_amount = !empty($bookingpress_appointment_data['bookingpress_deposit_due_amt_without_currency']) ? floatval($bookingpress_appointment_data['bookingpress_deposit_due_amt_without_currency']) : 0;
    
                        if(!empty($bookingpress_deposit_selected_amount)){
                            $__payable_amount = $bookingpress_deposit_selected_amount;
                        }
                        
                        $bookingpress_deposit_details = array(
                            'deposit_selected_type' => $bookingpress_deposit_selected_type,
                            'deposit_amount' => $bookingpress_deposit_selected_amount,
                            'deposit_due_amount' => $bookingpress_due_amount,
                        );
                    }
    
                    $return_data['payable_amount'] = (float) $__payable_amount;
    
                    
    
                    // Apply coupon if coupon module enabled
                    $bookingpress_coupon_code         = ! empty( $bookingpress_appointment_data['coupon_code'] ) ? $bookingpress_appointment_data['coupon_code'] : '';
                    $discounted_amount                = !empty($bookingpress_appointment_data['coupon_discount_amount']) ? floatval($bookingpress_appointment_data['coupon_discount_amount']) : 0;
                    $bookingpress_is_coupon_applied   = 0;
                    $bookingpress_applied_coupon_data = array();    
                    if ( $bookingpress_coupons->bookingpress_check_coupon_module_activation() && ! empty( $bookingpress_coupon_code )) {
                        $bookingpress_applied_coupon_data = ! empty( $bookingpress_appointment_data['applied_coupon_res'] ) ? $bookingpress_appointment_data['applied_coupon_res'] : array();
                        $bookingpress_applied_coupon_data['coupon_discount_amount'] = $discounted_amount;
                        $bookingpress_is_coupon_applied = 1;
                    }    
                    $bookingpress_selected_extra_members = !empty($bookingpress_appointment_data['bookingpress_selected_bring_members']) ? $bookingpress_appointment_data['bookingpress_selected_bring_members'] : 1;    
                    $bookingpress_extra_services = !empty($bookingpress_appointment_data['bookingpress_selected_extra_details']) ? $bookingpress_appointment_data['bookingpress_selected_extra_details'] : array();
                    $bookingpress_extra_services_db_details = array();

                    if(!empty($bookingpress_extra_services)){
                        foreach($bookingpress_extra_services as $k => $v){
                            if($v['bookingpress_is_selected'] == "true"){
                                $bookingpress_extra_service_id = intval($k);
                                $bookingpress_extra_service_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_extra_services} WHERE bookingpress_extra_services_id = %d", $bookingpress_extra_service_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_extra_services is a table name. false alarm
    
                                if(!empty($bookingpress_extra_service_details)){
                                    $bookingpress_extra_service_price = ! empty( $bookingpress_extra_service_details['bookingpress_extra_service_price'] ) ? floatval( $bookingpress_extra_service_details['bookingpress_extra_service_price'] ) : 0;
    
                                    $bookingpress_selected_qty = !empty($v['bookingpress_selected_qty']) ? intval($v['bookingpress_selected_qty']) : 1;
    
                                    if(!empty($bookingpress_selected_qty)){
                                        $bookingpress_final_price = $bookingpress_extra_service_price * $bookingpress_selected_qty;
                                        $v['bookingpress_final_payable_price'] = $bookingpress_final_price;
                                        $v['bookingpress_extra_service_details'] = $bookingpress_extra_service_details;
                                        array_push($bookingpress_extra_services_db_details, $v);
                                    }
                                }
                            }
                        }
                    }        
                    $bookingpress_selected_staffmember = 0;
                    $bookingpress_is_any_staff_selected = 0;
                    $bookingpress_staff_member_firstname = "";
                    $bookingpress_staff_member_lastname = "";
                    $bookingpress_staff_member_email_address = "";
                    $bookingpress_staffmember_price = 0;
                    $bookingpress_staffmember_details = array();
                    if($bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation()){
                        $bookingpress_selected_staffmember = !empty($bookingpress_appointment_data['bookingpress_selected_staff_member_details']['selected_staff_member_id']) ? $bookingpress_appointment_data['bookingpress_selected_staff_member_details']['selected_staff_member_id'] : 0;
                        $bookingpress_is_any_staff_selected = !empty($bookingpress_appointment_data['bookingpress_selected_staff_member_details']['is_any_staff_option_selected']) ? 1 : 0;
                        if(!empty($bookingpress_selected_staffmember)){
                            $bookingpress_staffmember_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_staffmember_id = %d", $bookingpress_selected_staffmember), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is table name.
                            $bookingpress_staff_member_firstname = !empty($bookingpress_staffmember_details['bookingpress_staffmember_firstname']) ? $bookingpress_staffmember_details['bookingpress_staffmember_firstname'] : '';
                            $bookingpress_staff_member_lastname = !empty($bookingpress_staffmember_details['bookingpress_staffmember_lastname']) ? $bookingpress_staffmember_details['bookingpress_staffmember_lastname'] : '';
                            $bookingpress_staff_member_email_address = !empty($bookingpress_staffmember_details['bookingpress_staffmember_email']) ? $bookingpress_staffmember_details['bookingpress_staffmember_email'] : '';
                            $bookingpress_staffmember_details['is_any_staff_selected'] = $bookingpress_is_any_staff_selected;    
                            /* Fetch staff member price */
                            $bookingpress_staffmember_price_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_staffmembers_services} WHERE bookingpress_staffmember_id = %d AND bookingpress_service_id = %d", $bookingpress_selected_staffmember, $bookingpress_selected_service_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_services is table name.
                            $bookingpress_staffmember_price = !empty($bookingpress_staffmember_price_details['bookingpress_service_price']) ? floatval($bookingpress_staffmember_price_details['bookingpress_service_price']) : 0;
                        }
                    }
                    $bookingpress_total_amount = $bookingpress_appointment_data['total_payable_amount'];                    
                    /* Get cart order id */
                    $bookingpress_cart_order_id = get_option('bookingpress_cart_order_id', true);
                    if(empty($bookingpress_cart_order_id)){
                        $bookingpress_cart_order_id = 1;
                    }else{
                        $bookingpress_cart_order_id = floatval($bookingpress_cart_order_id) + 1;
                    }
                    update_option('bookingpress_cart_order_id', $bookingpress_cart_order_id);                    
                    $bookingpress_timeslot_display_in_client_timezone = $BookingPress->bookingpress_get_settings( 'show_bookingslots_in_client_timezone', 'general_setting' );

                    $bookingpress_entry_details = array(
                        'bookingpress_customer_id'                    => $bookingpress_customer_id,
                        'bookingpress_order_id'                       => $bookingpress_cart_order_id,
                        'bookingpress_is_cart'                        => 0,
                        'bookingpress_is_recurring'                   => 1,
                        'bookingpress_customer_name'                  => $customer_full_name,
                        'bookingpress_username'                       => $customer_username,
                        'bookingpress_customer_phone'                 => $customer_phone,
                        'bookingpress_customer_firstname'             => $customer_firstname,
                        'bookingpress_customer_lastname'              => $customer_lastname,
                        'bookingpress_customer_country'               => $customer_country,
                        'bookingpress_customer_phone_dial_code'       => $customer_phone_dial_code,
                        'bookingpress_customer_email'                 => $customer_email,
                        'bookingpress_customer_timezone'              => $customer_timezone,
                        'bookingpress_dst_timezone'					  => $customer_dst_timezone,
                        'bookingpress_service_id'                     => $bookingpress_selected_service_id,
                        'bookingpress_service_name'                   => $service_data['bookingpress_service_name'],
                        'bookingpress_service_price'                  => $bookingpress_service_price,
                        'bookingpress_service_currency'               => $bookingpress_currency_name,
                        'bookingpress_service_duration_val'           => $service_data['bookingpress_service_duration_val'],
                        'bookingpress_service_duration_unit'          => $service_data['bookingpress_service_duration_unit'],
                        'bookingpress_payment_gateway'                => $payment_gateway,
                        'bookingpress_appointment_date'               => $bookingpress_appointment_booked_date,
                        'bookingpress_appointment_time'               => $bookingpress_selected_start_time,
                        'bookingpress_appointment_end_time'  		  => $bookingpress_selected_end_time,
                        'bookingpress_appointment_internal_note'      => $bookingpress_internal_note,
                        'bookingpress_appointment_send_notifications' => 1,
                        'bookingpress_appointment_status'             => $bookingpress_appointment_status,
                        'bookingpress_coupon_details'                 => wp_json_encode( $bookingpress_applied_coupon_data ),
                        'bookingpress_coupon_discount_amount'         => $discounted_amount,
                        'bookingpress_deposit_payment_details'        => wp_json_encode( $bookingpress_deposit_details ),
                        'bookingpress_deposit_amount'                 => $bookingpress_deposit_selected_amount,
                        'bookingpress_selected_extra_members'         => $bookingpress_selected_extra_members,
                        'bookingpress_extra_service_details'          => wp_json_encode( $bookingpress_extra_services_db_details ),
                        'bookingpress_staff_member_id'                => $bookingpress_selected_staffmember,
                        'bookingpress_staff_member_price'             => $bookingpress_staffmember_price,
                        'bookingpress_staff_first_name'               => $bookingpress_staff_member_firstname,
                        'bookingpress_staff_last_name'                => $bookingpress_staff_member_lastname,
                        'bookingpress_staff_email_address'            => $bookingpress_staff_member_email_address,
                        'bookingpress_staff_member_details'           => wp_json_encode($bookingpress_staffmember_details),
                        'bookingpress_paid_amount'                    => $__payable_amount,
                        'bookingpress_due_amount'                     => $bookingpress_due_amount,
                        'bookingpress_total_amount'                   => $bookingpress_total_amount,
                        'bookingpress_created_at'                     => current_time( 'mysql' ),
                    );

                    $bookingpress_recurring_object = array();
                    foreach($recurring_appointments as $rec_index=>$recurring_app){

                        $service_data                         = $BookingPress->get_service_by_id( $bookingpress_selected_service_id );
                        $bookingpress_service_price = $service_data['bookingpress_service_price'];
                        $bookingpress_selected_start_time = date('H:i:s', strtotime($recurring_app['selected_start_time']));
                        $service_duration_vals                = $BookingPress->bookingpress_get_service_end_time( $bookingpress_selected_service_id, $bookingpress_selected_start_time );
                        $service_data['service_start_time']   = sanitize_text_field( $service_duration_vals['service_start_time'] );
                        $service_data['service_end_time']     = sanitize_text_field( $service_duration_vals['service_end_time'] );
                        array_push($return_data['service_data'], $service_data);

                        $bookingpress_entry_details['bookingpress_appointment_time'] = date('H:i:s', strtotime($recurring_app['selected_start_time']));
                        $bookingpress_entry_details['bookingpress_appointment_end_time'] = date('H:i:s', strtotime($recurring_app['selected_end_time'])); 
                        $bookingpress_entry_details['bookingpress_appointment_date'] =  $recurring_app['selected_date'];                        
                        $bookingpress_entry_details = apply_filters( 'bookingpress_modify_entry_data_before_insert', $bookingpress_entry_details, $posted_data );

                        do_action( 'bookingpress_payment_log_entry', $payment_gateway, 'submit appointment form front', 'bookingpress pro', $bookingpress_entry_details, $bookingpress_debug_payment_log_id );

                        $wpdb->insert( $tbl_bookingpress_entries, $bookingpress_entry_details );
                        $entry_id = $wpdb->insert_id;

                        do_action( 'bookingpress_after_entry_data_insert', $entry_id,$recurring_app);                                                
                    }
                    
                    $return_data['entry_id'] = $bookingpress_cart_order_id;
                    $return_data['is_cart'] = 1;
                    $return_data['booking_form_redirection_mode'] = $bookingpress_appointment_data['booking_form_redirection_mode'];
                    $bookingpress_uniq_id = $bookingpress_appointment_data['bookingpress_uniq_id'];                    

                    setcookie("bookingpress_last_request_id", "", time()-3600, "/");
                    setcookie("bookingpress_referer_url", "", time() - 3600, "/");
                    setcookie("bookingpress_cart_id","", time()+(86400), "/");
                    if(session_id() == '' OR session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    $_SESSION['bookingpress_cart_'.$bookingpress_uniq_id.'_data'] = json_encode($recurring_appointments); 
                    
                    $bookingpress_referer_url = (wp_get_referer()) ? wp_get_referer() : BOOKINGPRESS_HOME_URL;
                    setcookie("bookingpress_last_request_id", $bookingpress_uniq_id, time()+(86400), "/");
                    setcookie("bookingpress_referer_url", $bookingpress_referer_url, time()+(86400), "/");
                    setcookie("bookingpress_cart_id", base64_encode($bookingpress_cart_order_id), time()+(86400), "/");
                    
                    $bookingpress_after_approved_payment_page_id = $BookingPress->bookingpress_get_customize_settings( 'after_booking_redirection', 'booking_form' );
                    $bookingpress_after_approved_payment_url     = get_permalink( $bookingpress_after_approved_payment_page_id );
    
                    $bookingpress_after_canceled_payment_page_id = $BookingPress->bookingpress_get_customize_settings( 'after_failed_payment_redirection', 'booking_form' );
                    $bookingpress_after_canceled_payment_url     = get_permalink( $bookingpress_after_canceled_payment_page_id );
    
                    $bookingpress_entry_hash = md5($bookingpress_cart_order_id);
    
                    if( !empty($bookingpress_appointment_data['booking_form_redirection_mode']) && $bookingpress_appointment_data['booking_form_redirection_mode'] == "in-built" ){
                        $bookingpress_approved_appointment_url = $bookingpress_canceled_appointment_url = $bookingpress_referer_url;
                        $bookingpress_approved_appointment_url = add_query_arg('is_success', 1, $bookingpress_after_approved_payment_url);
                        $bookingpress_approved_appointment_url = add_query_arg('appointment_id', base64_encode($bookingpress_cart_order_id), $bookingpress_approved_appointment_url);
                        $bookingpress_approved_appointment_url = add_query_arg( 'bp_tp_nonce', wp_create_nonce( 'bpa_nonce_url-'.$bookingpress_entry_hash ), $bookingpress_approved_appointment_url );
    
                        $bookingpress_canceled_appointment_url = add_query_arg('is_success', 2, $bookingpress_canceled_appointment_url);
                        $bookingpress_canceled_appointment_url = add_query_arg('appointment_id', base64_encode($bookingpress_cart_order_id), $bookingpress_canceled_appointment_url);
                        $bookingpress_canceled_appointment_url = add_query_arg( 'bp_tp_nonce', wp_create_nonce( 'bpa_nonce_url-'.$bookingpress_entry_hash ), $bookingpress_canceled_appointment_url );
    
                        $return_data['approved_appointment_url'] = $bookingpress_approved_appointment_url;
                        $return_data['pending_appointment_url'] = $return_data['approved_appointment_url'];
                        $return_data['canceled_appointment_url'] = $bookingpress_canceled_appointment_url;
                    }else{
                        $bookingpress_after_approved_payment_url = ! empty( $bookingpress_after_approved_payment_url ) ? $bookingpress_after_approved_payment_url : BOOKINGPRESS_HOME_URL;
                        $bookingpress_after_approved_payment_url = add_query_arg('appointment_id', base64_encode($bookingpress_cart_order_id), $bookingpress_after_approved_payment_url);
                        $bookingpress_after_approved_payment_url = add_query_arg( 'bp_tp_nonce', wp_create_nonce( 'bpa_nonce_url-'.$bookingpress_entry_hash ), $bookingpress_after_approved_payment_url );
                        $return_data['approved_appointment_url'] = $bookingpress_after_approved_payment_url;
    
                        $bookingpress_after_canceled_payment_url = ! empty( $bookingpress_after_canceled_payment_url ) ? $bookingpress_after_canceled_payment_url : BOOKINGPRESS_HOME_URL;
                        $bookingpress_after_canceled_payment_url = add_query_arg('appointment_id', base64_encode($bookingpress_cart_order_id), $bookingpress_after_canceled_payment_url);
                        $return_data['canceled_appointment_url'] = $bookingpress_after_canceled_payment_url;
                        
                        $return_data['pending_appointment_url'] = $return_data['approved_appointment_url'];
                    }
    
                    $return_data['approved_appointment_url'] = add_query_arg('is_cart', 1, $return_data['approved_appointment_url']);
                    $return_data['pending_appointment_url'] = add_query_arg('is_cart', 1, $return_data['pending_appointment_url']);
                    $return_data['canceled_appointment_url'] = add_query_arg('is_cart', 1, $return_data['canceled_appointment_url']);
    
                    $bookingpress_notify_url   = BOOKINGPRESS_HOME_URL . '/?bookingpress-listener=bpa_pro_' . $payment_gateway . '_url';
                    $return_data['notify_url'] = $bookingpress_notify_url;
    
                    $return_data = apply_filters( 'bookingpress_add_modify_validate_submit_form_data', $return_data, $payment_gateway, $posted_data );
    
                    //Enter data in appointment meta table
                    //------------------------------
                    $bookingpress_appointment_meta_details = $bookingpress_appointment_data;
                    $bookingpress_db_fields = array(
                        'bookingpress_order_id' => $bookingpress_cart_order_id,
                        'bookingpress_appointment_id' => 0,
                        'bookingpress_appointment_meta_key' => 'appointment_details',
                        'bookingpress_appointment_meta_value' => wp_json_encode($bookingpress_appointment_meta_details),
                    );
                    $wpdb->insert($tbl_bookingpress_appointment_meta, $bookingpress_db_fields);

                    do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Cart appointment meta data', 'bookingpress_cart_submit_booking_request', $bookingpress_db_fields, $bookingpress_other_debug_log_id );
                }                  
                return $return_data;
            }

        }

        /**
         * Function for remove single entry for bookingpress_entries table
         *
         * @param  mixed $bookingpress_add_single_entry
         * @param  mixed $posted_data
         * @return void
         */
        function bookingpress_add_single_appointment_data_func($bookingpress_add_single_entry,$posted_data){
            $is_recurring_appointments = (isset($posted_data['is_recurring_appointments']))?sanitize_text_field($posted_data['is_recurring_appointments']):'';
            if($is_recurring_appointments == 'true'){
                $bookingpress_add_single_entry = false;                
            }
            return $bookingpress_add_single_entry;
        }

        /**
         * Function for add recurring validation for front
         *
         * @return void
         */
        function bookingpress_validate_booking_front_form_func($return_data,$posted_data,$service_visibility_field_arr,$field_error_msg=''){

            global $BookingPress;
            $is_recurring_appointments = (isset($posted_data['appointment_data']['is_recurring_appointments']))?sanitize_text_field($posted_data['appointment_data']['is_recurring_appointments']):'';
            if($is_recurring_appointments == 'true'){
                
                $response = array();
                $response['variant']    = 'success';
                $response['title']      = '';
                $response['msg']        = '';
                $response['error_type'] = '';                
                $bookingpress_form_token = !empty( $posted_data['appointment_data']['bookingpress_form_token'] ) ? sanitize_text_field($posted_data['appointment_data']['bookingpress_form_token']) : sanitize_text_field($posted_data['appointment_data']['bookingpress_uniq_id']);

                /* serivce extra validation start */                                    
                if( !empty( $posted_data['appointment_data']['selected_service']) && $posted_data['appointment_data']['is_extra_service_exists'] == 1 && !empty($posted_data['appointment_data']['bookingpress_selected_extra_details']) ){                    
                    foreach( $posted_data['appointment_data']['bookingpress_selected_extra_details'] as $key=>$val ){
                        if($val['bookingpress_is_selected'] == 'true'){
                            if($val['bookingpress_service_id'] != $posted_data['appointment_data']['selected_service']){
                                $response['variant'] = 'error';
                                $response['title']   = esc_html__('Error', 'bookingpress-recurring-appointments');
                                $response['msg']     = "selected extras not associated with selected service";
                                return wp_json_encode($response);
                            }
                        }
                    }
                }                                
                $bookingpress_appointment_data = !empty($_POST['appointment_data']) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['appointment_data'] ) : array(); // phpcs:ignore

                /* serivce extra validation end */
                $appointment_service_id    = intval($posted_data['appointment_data']['selected_service']);               
                $bookingpress_selected_staffmember_id = 0;
                $bookingpress_selected_staffmember_id = sanitize_text_field($posted_data['appointment_data']['bookingpress_selected_staff_member_details']['selected_staff_member_id']);
                $recurring_appointments = (isset($posted_data['appointment_data']['recurring_appointments']))?$posted_data['appointment_data']['recurring_appointments']:'';
                $recurring_appointment_add_validation_message = $BookingPress->bookingpress_get_customize_settings('recurring_appointment_add_validation_message','booking_form');                

                if(empty($recurring_appointments)){
                    $response['variant'] = 'error';
                    $response['title']   = esc_html__('Error', 'bookingpress-recurring-appointments');
                    $response['msg']     = $recurring_appointment_add_validation_message;
                    return wp_json_encode($response);
                }

                if( !empty( $service_visibility_field_arr ) ){
                    $is_visible_field_validation = false;
                    foreach( $service_visibility_field_arr as $visible_field_key => $visible_field_value ){
                        if( empty( $posted_data['appointment_data']['form_fields'][ $visible_field_key ] ) && in_array( $appointment_service_id, $visible_field_value ) ){
                            $is_visible_field_validation = true;
                            $field_validation_message[] = $field_error_msg;
                        }
                    }

                    if( true == $is_visible_field_validation ){
                        $response['variant'] = 'error';
                        $response['title']   = esc_html__('Error', 'bookingpress-recurring-appointments');
                        $response['msg']     = !empty($field_validation_message) ? implode(',', $field_validation_message) : array();
                        return wp_json_encode($response);
                    }
                }

                foreach($recurring_appointments as $rec_index=>$recurring_app){

                    $appointment_selected_date = $recurring_app['selected_date'];
                    $appointment_start_time    = date('H:i:s', strtotime($recurring_app['selected_start_time']));
                    $appointment_end_time      = date('H:i:s', strtotime($recurring_app['selected_end_time'])); 
                    $bookingpress_search_query              = preg_quote($appointment_selected_date, '~');
                    $bookingpress_get_default_daysoff_dates = $BookingPress->bookingpress_get_default_dayoff_dates();

                    /* Wrong Cart Items */
                    /*
                    $bookingpress_get_default_daysoff_dates = apply_filters('bookingpress_modify_disable_dates', $bookingpress_get_default_daysoff_dates, $appointment_service_id, date( 'Y-m-d', current_time('timestamp') ), $cart_item);                    
                    $bookingpress_get_default_daysoff_dates = apply_filters( 'bookingpress_modify_disable_dates_with_staffmember', $bookingpress_get_default_daysoff_dates, $appointment_service_id);                                
                    */

    
                    $bookingpress_appointment_data = !empty($_POST['appointment_data']) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['appointment_data'] ) : array(); // phpcs:ignore
                    $bookingpress_appointment_data['selected_date'] = (isset($recurring_app['selected_date']))?$recurring_app['selected_date']:'';
                    $bookingpress_appointment_data['selected_end_time'] = (isset($recurring_app['selected_end_time']))?$recurring_app['selected_end_time']:'';
                    $bookingpress_appointment_data['selected_start_time'] = (isset($recurring_app['selected_start_time']))?$recurring_app['selected_start_time']:'';

                    $bookingpress_get_default_daysoff_dates = apply_filters('bookingpress_modify_disable_dates', $bookingpress_get_default_daysoff_dates, $appointment_service_id, date( 'Y-m-d', current_time('timestamp') ), $bookingpress_appointment_data);
                    $bookingpress_get_default_daysoff_dates = apply_filters( 'bookingpress_modify_disable_dates_with_staffmember', $bookingpress_get_default_daysoff_dates, $appointment_service_id);                    
                    //$bookingpress_get_default_daysoff_dates = $_SESSION['disable_dates']; // Dimple changes 26jul2022 Need to check with Azhar why selected added to the array
                    $bookingpress_search_date  = preg_grep('~' . $bookingpress_search_query . '~', $bookingpress_get_default_daysoff_dates);

                    if (! empty($bookingpress_search_date) ) {
                        $booking_dayoff_msg     = $appointment_selected_date . ' ' .esc_html__('is off day', 'bookingpress-recurring-appointments');
                        $booking_dayoff_msg    .= '. ' . esc_html__('So please select new date', 'bookingpress-recurring-appointments') . '.';
                        $response['error_type'] = 'dayoff';
                        $response['variant']    = 'error';
                        $response['title']      = esc_html__('Error', 'bookingpress-recurring-appointments');
                        $response['msg']        = $booking_dayoff_msg;
                        return wp_json_encode($response);
                    }

                    $is_booked_appointment = $BookingPress->bookingpress_is_appointment_booked($appointment_service_id, $appointment_selected_date, $appointment_start_time, $appointment_end_time, 0, false, $posted_data );
                    if( !empty( $is_booked_appointment ) ){
                        $response = json_decode( $is_booked_appointment );            
                        return wp_json_encode($response);
                    }                    

                    /* Minimum Time Required Before Booked */
                    $minimum_time_required = ''; 
                    $minimum_time_required = 'disabled';
                    $minimum_time_required = apply_filters( 'bookingpress_retrieve_minimum_required_time', $minimum_time_required, $appointment_service_id );            
                    if( 'disabled' != $minimum_time_required ){
                        $bookingpress_slot_start_datetime       = $appointment_selected_date . ' ' . $appointment_start_time;
                        $bookingpress_slot_start_time_timestamp = strtotime( $bookingpress_slot_start_datetime );
                        $bookingpress_time_diff = round( abs( current_time('timestamp') - $bookingpress_slot_start_time_timestamp ) / 60, 2 );					
                        //echo $bookingpress_time_diff; exit;
                        /*  Add Day Service Validation For minimum time required */
                        $bookingpress_service_duration_unit = isset($bookingpress_appointment_data['selected_service_duration_unit'])?$bookingpress_appointment_data['selected_service_duration_unit']:'';
                        if($bookingpress_service_duration_unit == 'd'){	
                            if($minimum_time_required >= 1440){
                                $bookingpress_time_diff = round( abs( strtotime(date('Y-m-d')) - $bookingpress_slot_start_time_timestamp ) / 60, 2 )+5;							
                            }
                        }            
                        if( $bookingpress_time_diff <= $minimum_time_required ){
                            $response['variant']              = 'error';
                            $response['title']                = 'Error';
                            $response['msg']                  = esc_html__("Sorry, Booking can not be done as minimum required time before booking is already passed", "bookingpress-recurring-appointments");
                            return wp_json_encode($response);
                        }
                    }
                    $get_recurring_timings = get_transient( 'bpa_recurring_front_timings_'. $bookingpress_form_token );
                    if(isset($get_recurring_timings[ $rec_index ]) && is_array($get_recurring_timings[ $rec_index ])){
                        $timings = array_values( $get_recurring_timings[ $rec_index ] );
                    }else{
                        $timings = array();
                    }                                       

                    $appointment_end_time = ($appointment_end_time == '00:00:00' || $appointment_end_time == '00:00') ? '24:00' : date('H:i',strtotime($appointment_end_time));                    
                    $time_slot_start_key = array_search( date('H:i',strtotime($appointment_start_time)), array_column( $timings, 'store_start_time' ) );
                    $time_slot_end_key = array_search($appointment_end_time, array_column( $timings, 'store_end_time' ) ); 

                    if((trim($time_slot_start_key) === '' || trim($time_slot_end_key) === '') && 'd' != $bookingpress_service_duration_unit){
                        $response['variant']              = 'error';
                        $response['title']                = 'Error';
                        $response['msg']                  = esc_html__("Sorry, Booking can not be done as booking time is different than selected timeslot", "bookingpress-recurring-appointments");                         
                        return wp_json_encode($response);
                    }  

                }
                return wp_json_encode($response);
            }            
            return '';
        }


        function bookingpress_validate_only_booking_form_func($return_data,$posted_data){
            global $BookingPress, $wpdb, $tbl_bookingpress_form_fields;
            $is_recurring_appointments = (isset($posted_data['appointment_data']['is_recurring_appointments']))?sanitize_text_field($posted_data['appointment_data']['is_recurring_appointments']):'';
            if($is_recurring_appointments == 'true'){
                
                $response = array();
                $response['variant']    = 'success';
                $response['title']      = '';
                $response['msg']        = '';
                $response['error_type'] = '';                
                $bookingpress_form_token = !empty( $posted_data['appointment_data']['bookingpress_form_token'] ) ? sanitize_text_field($posted_data['appointment_data']['bookingpress_form_token']) : sanitize_text_field($posted_data['appointment_data']['bookingpress_uniq_id']);

                /* serivce extra validation start */                                    
                if( !empty( $posted_data['appointment_data']['selected_service']) && $posted_data['appointment_data']['is_extra_service_exists'] == 1 && !empty($posted_data['appointment_data']['bookingpress_selected_extra_details']) ){                    
                    foreach( $posted_data['appointment_data']['bookingpress_selected_extra_details'] as $key=>$val ){
                        if($val['bookingpress_is_selected'] == 'true'){
                            if($val['bookingpress_service_id'] != $posted_data['appointment_data']['selected_service']){
                                $response['variant'] = 'error';
                                $response['title']   = esc_html__('Error', 'bookingpress-recurring-appointments');
                                $response['msg']     = "selected extras not associated with selected service";
                                return wp_json_encode($response);
                                exit();
                            }
                        }
                    }
                }                                
                $bookingpress_appointment_data = !empty($_POST['appointment_data']) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['appointment_data'] ) : array(); // phpcs:ignore

                /* serivce extra validation end */
                $appointment_service_id    = intval($posted_data['appointment_data']['selected_service']);               
                $bookingpress_selected_staffmember_id = 0;
                $bookingpress_selected_staffmember_id = sanitize_text_field($posted_data['appointment_data']['bookingpress_selected_staff_member_details']['selected_staff_member_id']);
                $recurring_appointments = (isset($posted_data['appointment_data']['recurring_appointments']))?$posted_data['appointment_data']['recurring_appointments']:'';
                $recurring_appointment_add_validation_message = $BookingPress->bookingpress_get_customize_settings('recurring_appointment_add_validation_message','booking_form');                

                if(empty($recurring_appointments)){
                    $response['variant'] = 'error';
                    $response['title']   = esc_html__('Error', 'bookingpress-recurring-appointments');
                    $response['msg']     = $recurring_appointment_add_validation_message;
                    return wp_json_encode($response);
                    exit();
                }

                $bookingpress_uniq_id = (isset($posted_data['appointment_data']['bookingpress_uniq_id']))?sanitize_text_field( $posted_data['appointment_data']['bookingpress_uniq_id'] ):'';
                $bookingpress_form_token = (isset($posted_data['appointment_data']['bookingpress_form_token']) && !empty($posted_data['appointment_data']['bookingpress_form_token'])) ? sanitize_text_field( $posted_data['appointment_data']['bookingpress_form_token'] ) : $bookingpress_uniq_id ;

                $all_fields = wp_cache_get( 'bookingpress_validate_form_fields_data_' . $bookingpress_form_token );
                if( false == $all_fields ){
                    $all_fields = $wpdb->get_results( $wpdb->prepare("SELECT bookingpress_field_meta_key,bookingpress_field_error_message,bookingpress_form_field_name,bookingpress_field_is_default,bookingpress_field_options,bookingpress_field_type FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_field_required = %d", 1) );// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
                }
                $service_visibility_field_arr = array();
                $field_validation_message = array();
                if ( ! empty( $all_fields ) ) {
                    $is_required_validation = false;
                    foreach ( $all_fields as $field_data ) {
                        
                        if( 'password' == $field_data->bookingpress_field_type && is_user_logged_in() ){
                            continue;
                        }
    
                        $field_error_msg = $field_data->bookingpress_field_error_message;
    
                        $field_options = json_decode($field_data->bookingpress_field_options, true);
                        $bookingpress_selected_service = $field_options['selected_services'];   
                        if( $field_options['visibility'] == 'always' &&  $field_data->bookingpress_form_field_name != '2 Col' && $field_data->bookingpress_form_field_name != '3 Col' && $field_data->bookingpress_form_field_name != '4 Col' ){
                            $bpa_visible_field_key = '';
                            if( $field_data->bookingpress_field_is_default == 1 ){
                                if( $field_data->bookingpress_form_field_name == 'firstname'){
                                    $bpa_visible_field_key = 'customer_firstname';		
                                }
                                if( $field_data->bookingpress_form_field_name == 'lastname'){
                                    $bpa_visible_field_key = 'customer_lastname';		
                                }
                                if( $field_data->bookingpress_form_field_name == 'email_address'){
                                    $bpa_visible_field_key = 'customer_email';		
                                }
                                if( $field_data->bookingpress_form_field_name == 'note'){
                                    $bpa_visible_field_key = 'appointment_note';		
                                }
                                if( $field_data->bookingpress_form_field_name == 'phone_number'){
                                    $bpa_visible_field_key = 'customer_phone';		
                                }
                                if( $field_data->bookingpress_form_field_name == 'fullname'){
                                    $bpa_visible_field_key = 'customer_name';		
                                }
                                if( $field_data->bookingpress_form_field_name == 'username'){
                                    $bpa_visible_field_key = 'customer_username';		
                                }
                                if( $field_data->bookingpress_form_field_name == 'terms_and_conditions'){
                                    $bpa_visible_field_key = 'appointment_terms_conditions';		
                                }
                            } else {
                                $bpa_visible_field_key = $field_data->bookingpress_field_meta_key;
                            }
    
                            if( 'password' == $field_data->bookingpress_field_type && empty( $posted_data['appointment_data']['form_fields'][$bpa_visible_field_key] ) ){
                                continue;
                            }
    
                            $val = $posted_data['appointment_data']['form_fields'][ $bpa_visible_field_key ];
    
                            if( $bpa_visible_field_key == 'appointment_terms_conditions'){
    
                                if( empty($val[0])){
                                    $is_required_validation = true;
                                    $field_validation_message[] = $field_error_msg;
                                }
                            } else {
                                if( '' === $val ){
                                    $is_required_validation = true;
                                    $field_validation_message[] = $field_error_msg;
                                }
                            }
                        }
    
                        if( $field_options['visibility'] == 'services' &&  $field_data->bookingpress_form_field_name != '2 Col' && $field_data->bookingpress_form_field_name != '3 Col' && $field_data->bookingpress_form_field_name != '4 Col' ){
                            $bookingpress_field_meta_key_val = $field_data->bookingpress_field_meta_key;
    
                            if( $field_data->bookingpress_field_is_default == 1 ){
                                if( $field_data->bookingpress_form_field_name == 'firstname'){
                                    $bookingpress_field_meta_key_val = 'customer_firstname';		
                                }
                                if( $field_data->bookingpress_form_field_name == 'lastname'){
                                    $bookingpress_field_meta_key_val = 'customer_lastname';		
                                }
                                if( $field_data->bookingpress_form_field_name == 'email_address'){
                                    $bookingpress_field_meta_key_val = 'customer_email';		
                                }
                                if( $field_data->bookingpress_form_field_name == 'note'){
                                    $bookingpress_field_meta_key_val = 'appointment_note';		
                                }
                                if( $field_data->bookingpress_form_field_name == 'phone_number'){
                                    $bookingpress_field_meta_key_val = 'customer_phone';		
                                }
                                if( $field_data->bookingpress_form_field_name == 'fullname'){
                                    $bookingpress_field_meta_key_val = 'customer_name';		
                                }
                                if( $field_data->bookingpress_form_field_name == 'username'){
                                    $bookingpress_field_meta_key_val = 'customer_username';		
                                }
                                if( $field_data->bookingpress_form_field_name == 'terms_and_conditions'){
                                    $bookingpress_field_meta_key_val = 'appointment_terms_conditions';		
                                }
                            } else {
                                $bookingpress_field_meta_key_val = $field_data->bookingpress_field_meta_key;
                            }
                            $service_visibility_field_arr[$bookingpress_field_meta_key_val] = $bookingpress_selected_service;
                        }
                    }
    
                }                                
                if( !empty( $service_visibility_field_arr ) ){
                    $is_visible_field_validation = false;
                    foreach( $service_visibility_field_arr as $visible_field_key => $visible_field_value ){
                        if( empty( $posted_data['appointment_data']['form_fields'][ $visible_field_key ] ) && in_array( $appointment_service_id, $visible_field_value ) ){
                            $is_visible_field_validation = true;
                            $field_validation_message[] = $field_error_msg;
                        }
                    }
                    if( true == $is_visible_field_validation ){
                        $response['variant'] = 'error';
                        $response['title']   = esc_html__('Error', 'bookingpress-recurring-appointments');
                        $response['msg']     = !empty($field_validation_message) ? implode(',', $field_validation_message) : array();
                        return wp_json_encode($response);
                        exit();
                    }
                }                                

                /* Minimum Time Required Before Booked */
                $minimum_time_required = ''; 
                $minimum_time_required = 'disabled';
                $minimum_time_required = apply_filters( 'bookingpress_retrieve_minimum_required_time', $minimum_time_required, $appointment_service_id );                

                $get_recurring_timings = get_transient( 'bpa_recurring_front_timings_'. $bookingpress_form_token );

                $bookingpress_get_default_daysoff_dates = $BookingPress->bookingpress_get_default_dayoff_dates();
                $bookingpress_get_default_daysoff_dates = apply_filters('bookingpress_modify_disable_dates', $bookingpress_get_default_daysoff_dates, $appointment_service_id, date( 'Y-m-d', current_time('timestamp') ), $bookingpress_appointment_data);
                $bookingpress_get_default_daysoff_dates = apply_filters( 'bookingpress_modify_disable_dates_with_staffmember', $bookingpress_get_default_daysoff_dates, $appointment_service_id);                

                foreach($recurring_appointments as $rec_index=>$recurring_app){

                    $appointment_selected_date = $recurring_app['selected_date'];
                    $appointment_start_time    = date('H:i:s', strtotime($recurring_app['selected_start_time']));
                    $appointment_end_time      = date('H:i:s', strtotime($recurring_app['selected_end_time'])); 
                    $bookingpress_search_query              = preg_quote($appointment_selected_date, '~');
                    

                    /* Wrong Cart Items */
                    /*
                    $bookingpress_get_default_daysoff_dates = apply_filters('bookingpress_modify_disable_dates', $bookingpress_get_default_daysoff_dates, $appointment_service_id, date( 'Y-m-d', current_time('timestamp') ), $cart_item);                    
                    $bookingpress_get_default_daysoff_dates = apply_filters( 'bookingpress_modify_disable_dates_with_staffmember', $bookingpress_get_default_daysoff_dates, $appointment_service_id);                                
                    */

    
                    $bookingpress_appointment_data = !empty($_POST['appointment_data']) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['appointment_data'] ) : array(); // phpcs:ignore
                    $bookingpress_appointment_data['selected_date'] = (isset($recurring_app['selected_date']))?$recurring_app['selected_date']:'';
                    $bookingpress_appointment_data['selected_end_time'] = (isset($recurring_app['selected_end_time']))?$recurring_app['selected_end_time']:'';
                    $bookingpress_appointment_data['selected_start_time'] = (isset($recurring_app['selected_start_time']))?$recurring_app['selected_start_time']:'';

                   
                    //$bookingpress_get_default_daysoff_dates = $_SESSION['disable_dates']; // Dimple changes 26jul2022 Need to check with Azhar why selected added to the array
                    $bookingpress_search_date  = preg_grep('~' . $bookingpress_search_query . '~', $bookingpress_get_default_daysoff_dates);

                    if (! empty($bookingpress_search_date) ) {
                        $booking_dayoff_msg     = $appointment_selected_date . ' ' .esc_html__('is off day', 'bookingpress-recurring-appointments');
                        $booking_dayoff_msg    .= '. ' . esc_html__('So please select new date', 'bookingpress-recurring-appointments') . '.';
                        $response['error_type'] = 'dayoff';
                        $response['variant']    = 'error';
                        $response['title']      = esc_html__('Error', 'bookingpress-recurring-appointments');
                        $response['msg']        = $booking_dayoff_msg;
                        return wp_json_encode($response);
                        exit();
                    }

                    $is_booked_appointment = $BookingPress->bookingpress_is_appointment_booked($appointment_service_id, $appointment_selected_date, $appointment_start_time, $appointment_end_time, 0, false, $posted_data );
                    if( !empty( $is_booked_appointment ) ){
                        $response = json_decode( $is_booked_appointment );            
                        return wp_json_encode($response);
                        exit();
                    }                    


                               
                    if( 'disabled' != $minimum_time_required ){
                        $bookingpress_slot_start_datetime       = $appointment_selected_date . ' ' . $appointment_start_time;
                        $bookingpress_slot_start_time_timestamp = strtotime( $bookingpress_slot_start_datetime );
                        $bookingpress_time_diff = round( abs( current_time('timestamp') - $bookingpress_slot_start_time_timestamp ) / 60, 2 );					
                        //echo $bookingpress_time_diff; exit;
                        /*  Add Day Service Validation For minimum time required */
                        $bookingpress_service_duration_unit = isset($bookingpress_appointment_data['selected_service_duration_unit'])?$bookingpress_appointment_data['selected_service_duration_unit']:'';
                        if($bookingpress_service_duration_unit == 'd'){	
                            if($minimum_time_required >= 1440){
                                $bookingpress_time_diff = round( abs( strtotime(date('Y-m-d')) - $bookingpress_slot_start_time_timestamp ) / 60, 2 )+5;							
                            }
                        }            
                        if( $bookingpress_time_diff <= $minimum_time_required ){
                            $response['variant']              = 'error';
                            $response['title']                = 'Error';
                            $response['msg']                  = esc_html__("Sorry, Booking can not be done as minimum required time before booking is already passed", "bookingpress-recurring-appointments");
                            return wp_json_encode($response);
                            exit;
                        }
                    }
                    
                    
                    if(isset($get_recurring_timings[ $rec_index ]) && is_array($get_recurring_timings[ $rec_index ])){
                        $timings = array_values( $get_recurring_timings[ $rec_index ] );
                    }else{
                        $timings = array();
                    }                                       

                    $appointment_end_time = ($appointment_end_time == '00:00:00' || $appointment_end_time == '00:00') ? '24:00' : date('H:i',strtotime($appointment_end_time));                    
                    $time_slot_start_key = array_search( date('H:i',strtotime($appointment_start_time)), array_column( $timings, 'store_start_time' ) );
                    $time_slot_end_key = array_search($appointment_end_time, array_column( $timings, 'store_end_time' ) ); 

                    if((trim($time_slot_start_key) === '' || trim($time_slot_end_key) === '') && 'd' != $bookingpress_service_duration_unit){
                        $response['variant']              = 'error';
                        $response['title']                = 'Error';
                        $response['msg']                  = esc_html__("Sorry, Booking can not be done as booking time is different than selected timeslot", "bookingpress-recurring-appointments");                         
                        return wp_json_encode($response);
                        exit();
                    }  
                }                

                return wp_json_encode($response);
                exit();
            }else{
                return '';
            }            
            
        }

        /*  Action not working for validation... */
        function bookingpress_front_booking_recurring_validate_booking_form_func($posted_data){

            global $BookingPress, $wpdb, $tbl_bookingpress_form_fields;
            $is_recurring_appointments = (isset($posted_data['appointment_data']['is_recurring_appointments']))?sanitize_text_field($posted_data['appointment_data']['is_recurring_appointments']):'';
            if($is_recurring_appointments == 'true'){
                
                $response = array();
                $response['variant']    = 'success';
                $response['title']      = '';
                $response['msg']        = '';
                $response['error_type'] = '';                
                $bookingpress_form_token = !empty( $posted_data['appointment_data']['bookingpress_form_token'] ) ? sanitize_text_field($posted_data['appointment_data']['bookingpress_form_token']) : sanitize_text_field($posted_data['appointment_data']['bookingpress_uniq_id']);

                /* serivce extra validation start */                                    
                if( !empty( $posted_data['appointment_data']['selected_service']) && $posted_data['appointment_data']['is_extra_service_exists'] == 1 && !empty($posted_data['appointment_data']['bookingpress_selected_extra_details']) ){                    
                    foreach( $posted_data['appointment_data']['bookingpress_selected_extra_details'] as $key=>$val ){
                        if($val['bookingpress_is_selected'] == 'true'){
                            if($val['bookingpress_service_id'] != $posted_data['appointment_data']['selected_service']){
                                $response['variant'] = 'error';
                                $response['title']   = esc_html__('Error', 'bookingpress-recurring-appointments');
                                $response['msg']     = "selected extras not associated with selected service";
                                return wp_json_encode($response);
                                exit();
                            }
                        }
                    }
                }                                
                $bookingpress_appointment_data = !empty($_POST['appointment_data']) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['appointment_data'] ) : array(); // phpcs:ignore

                /* serivce extra validation end */
                $appointment_service_id    = intval($posted_data['appointment_data']['selected_service']);               
                $bookingpress_selected_staffmember_id = 0;
                $bookingpress_selected_staffmember_id = sanitize_text_field($posted_data['appointment_data']['bookingpress_selected_staff_member_details']['selected_staff_member_id']);
                $recurring_appointments = (isset($posted_data['appointment_data']['recurring_appointments']))?$posted_data['appointment_data']['recurring_appointments']:'';
                $recurring_appointment_add_validation_message = $BookingPress->bookingpress_get_customize_settings('recurring_appointment_add_validation_message','booking_form');                

                if(empty($recurring_appointments)){
                    $response['variant'] = 'error';
                    $response['title']   = esc_html__('Error', 'bookingpress-recurring-appointments');
                    $response['msg']     = $recurring_appointment_add_validation_message;
                    return wp_json_encode($response);
                    exit();
                }

                $bookingpress_uniq_id = (isset($posted_data['appointment_data']['bookingpress_uniq_id']))?sanitize_text_field( $posted_data['appointment_data']['bookingpress_uniq_id'] ):'';
                $bookingpress_form_token = (isset($posted_data['appointment_data']['bookingpress_form_token']) && !empty($posted_data['appointment_data']['bookingpress_form_token'])) ? sanitize_text_field( $posted_data['appointment_data']['bookingpress_form_token'] ) : $bookingpress_uniq_id ;

                $all_fields = wp_cache_get( 'bookingpress_validate_form_fields_data_' . $bookingpress_form_token );
                if( false == $all_fields ){
                    $all_fields = $wpdb->get_results( $wpdb->prepare("SELECT bookingpress_field_meta_key,bookingpress_field_error_message,bookingpress_form_field_name,bookingpress_field_is_default,bookingpress_field_options,bookingpress_field_type FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_field_required = %d", 1) );// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
                }
                $service_visibility_field_arr = array();
                $field_validation_message = array();
                if ( ! empty( $all_fields ) ) {
                    $is_required_validation = false;
                    foreach ( $all_fields as $field_data ) {
                        
                        if( 'password' == $field_data->bookingpress_field_type && is_user_logged_in() ){
                            continue;
                        }
    
                        $field_error_msg = $field_data->bookingpress_field_error_message;
    
                        $field_options = json_decode($field_data->bookingpress_field_options, true);
                        $bookingpress_selected_service = $field_options['selected_services'];   
                        if( $field_options['visibility'] == 'always' &&  $field_data->bookingpress_form_field_name != '2 Col' && $field_data->bookingpress_form_field_name != '3 Col' && $field_data->bookingpress_form_field_name != '4 Col' ){
                            $bpa_visible_field_key = '';
                            if( $field_data->bookingpress_field_is_default == 1 ){
                                if( $field_data->bookingpress_form_field_name == 'firstname'){
                                    $bpa_visible_field_key = 'customer_firstname';		
                                }
                                if( $field_data->bookingpress_form_field_name == 'lastname'){
                                    $bpa_visible_field_key = 'customer_lastname';		
                                }
                                if( $field_data->bookingpress_form_field_name == 'email_address'){
                                    $bpa_visible_field_key = 'customer_email';		
                                }
                                if( $field_data->bookingpress_form_field_name == 'note'){
                                    $bpa_visible_field_key = 'appointment_note';		
                                }
                                if( $field_data->bookingpress_form_field_name == 'phone_number'){
                                    $bpa_visible_field_key = 'customer_phone';		
                                }
                                if( $field_data->bookingpress_form_field_name == 'fullname'){
                                    $bpa_visible_field_key = 'customer_name';		
                                }
                                if( $field_data->bookingpress_form_field_name == 'username'){
                                    $bpa_visible_field_key = 'customer_username';		
                                }
                                if( $field_data->bookingpress_form_field_name == 'terms_and_conditions'){
                                    $bpa_visible_field_key = 'appointment_terms_conditions';		
                                }
                            } else {
                                $bpa_visible_field_key = $field_data->bookingpress_field_meta_key;
                            }
    
                            if( 'password' == $field_data->bookingpress_field_type && empty( $posted_data['appointment_data']['form_fields'][$bpa_visible_field_key] ) ){
                                continue;
                            }
    
                            $val = $posted_data['appointment_data']['form_fields'][ $bpa_visible_field_key ];
    
                            if( $bpa_visible_field_key == 'appointment_terms_conditions'){
    
                                if( empty($val[0])){
                                    $is_required_validation = true;
                                    $field_validation_message[] = $field_error_msg;
                                }
                            } else {
                                if( '' === $val ){
                                    $is_required_validation = true;
                                    $field_validation_message[] = $field_error_msg;
                                }
                            }
                        }
    
                        if( $field_options['visibility'] == 'services' &&  $field_data->bookingpress_form_field_name != '2 Col' && $field_data->bookingpress_form_field_name != '3 Col' && $field_data->bookingpress_form_field_name != '4 Col' ){
                            $bookingpress_field_meta_key_val = $field_data->bookingpress_field_meta_key;
    
                            if( $field_data->bookingpress_field_is_default == 1 ){
                                if( $field_data->bookingpress_form_field_name == 'firstname'){
                                    $bookingpress_field_meta_key_val = 'customer_firstname';		
                                }
                                if( $field_data->bookingpress_form_field_name == 'lastname'){
                                    $bookingpress_field_meta_key_val = 'customer_lastname';		
                                }
                                if( $field_data->bookingpress_form_field_name == 'email_address'){
                                    $bookingpress_field_meta_key_val = 'customer_email';		
                                }
                                if( $field_data->bookingpress_form_field_name == 'note'){
                                    $bookingpress_field_meta_key_val = 'appointment_note';		
                                }
                                if( $field_data->bookingpress_form_field_name == 'phone_number'){
                                    $bookingpress_field_meta_key_val = 'customer_phone';		
                                }
                                if( $field_data->bookingpress_form_field_name == 'fullname'){
                                    $bookingpress_field_meta_key_val = 'customer_name';		
                                }
                                if( $field_data->bookingpress_form_field_name == 'username'){
                                    $bookingpress_field_meta_key_val = 'customer_username';		
                                }
                                if( $field_data->bookingpress_form_field_name == 'terms_and_conditions'){
                                    $bookingpress_field_meta_key_val = 'appointment_terms_conditions';		
                                }
                            } else {
                                $bookingpress_field_meta_key_val = $field_data->bookingpress_field_meta_key;
                            }
                            $service_visibility_field_arr[$bookingpress_field_meta_key_val] = $bookingpress_selected_service;
                        }
                    }
    
                }                                
                if( !empty( $service_visibility_field_arr ) ){
                    $is_visible_field_validation = false;
                    foreach( $service_visibility_field_arr as $visible_field_key => $visible_field_value ){
                        if( empty( $posted_data['appointment_data']['form_fields'][ $visible_field_key ] ) && in_array( $appointment_service_id, $visible_field_value ) ){
                            $is_visible_field_validation = true;
                            $field_validation_message[] = $field_error_msg;
                        }
                    }
                    if( true == $is_visible_field_validation ){
                        $response['variant'] = 'error';
                        $response['title']   = esc_html__('Error', 'bookingpress-recurring-appointments');
                        $response['msg']     = !empty($field_validation_message) ? implode(',', $field_validation_message) : array();
                        return wp_json_encode($response);
                        exit();
                    }
                }                                

                /* Minimum Time Required Before Booked */
                $minimum_time_required = ''; 
                $minimum_time_required = 'disabled';
                $minimum_time_required = apply_filters( 'bookingpress_retrieve_minimum_required_time', $minimum_time_required, $appointment_service_id );                

                $get_recurring_timings = get_transient( 'bpa_recurring_front_timings_'. $bookingpress_form_token );

                $bookingpress_get_default_daysoff_dates = $BookingPress->bookingpress_get_default_dayoff_dates();
                $bookingpress_get_default_daysoff_dates = apply_filters('bookingpress_modify_disable_dates', $bookingpress_get_default_daysoff_dates, $appointment_service_id, date( 'Y-m-d', current_time('timestamp') ), $bookingpress_appointment_data);
                $bookingpress_get_default_daysoff_dates = apply_filters( 'bookingpress_modify_disable_dates_with_staffmember', $bookingpress_get_default_daysoff_dates, $appointment_service_id);                

                foreach($recurring_appointments as $rec_index=>$recurring_app){

                    $appointment_selected_date = $recurring_app['selected_date'];
                    $appointment_start_time    = date('H:i:s', strtotime($recurring_app['selected_start_time']));
                    $appointment_end_time      = date('H:i:s', strtotime($recurring_app['selected_end_time'])); 
                    $bookingpress_search_query              = preg_quote($appointment_selected_date, '~');
                    

                    /* Wrong Cart Items */
                    /*
                    $bookingpress_get_default_daysoff_dates = apply_filters('bookingpress_modify_disable_dates', $bookingpress_get_default_daysoff_dates, $appointment_service_id, date( 'Y-m-d', current_time('timestamp') ), $cart_item);                    
                    $bookingpress_get_default_daysoff_dates = apply_filters( 'bookingpress_modify_disable_dates_with_staffmember', $bookingpress_get_default_daysoff_dates, $appointment_service_id);                                
                    */

    
                    $bookingpress_appointment_data = !empty($_POST['appointment_data']) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['appointment_data'] ) : array(); // phpcs:ignore
                    $bookingpress_appointment_data['selected_date'] = (isset($recurring_app['selected_date']))?$recurring_app['selected_date']:'';
                    $bookingpress_appointment_data['selected_end_time'] = (isset($recurring_app['selected_end_time']))?$recurring_app['selected_end_time']:'';
                    $bookingpress_appointment_data['selected_start_time'] = (isset($recurring_app['selected_start_time']))?$recurring_app['selected_start_time']:'';

                   
                    //$bookingpress_get_default_daysoff_dates = $_SESSION['disable_dates']; // Dimple changes 26jul2022 Need to check with Azhar why selected added to the array
                    $bookingpress_search_date  = preg_grep('~' . $bookingpress_search_query . '~', $bookingpress_get_default_daysoff_dates);

                    if (! empty($bookingpress_search_date) ) {
                        $booking_dayoff_msg     = $appointment_selected_date . ' ' .esc_html__('is off day', 'bookingpress-recurring-appointments');
                        $booking_dayoff_msg    .= '. ' . esc_html__('So please select new date', 'bookingpress-recurring-appointments') . '.';
                        $response['error_type'] = 'dayoff';
                        $response['variant']    = 'error';
                        $response['title']      = esc_html__('Error', 'bookingpress-recurring-appointments');
                        $response['msg']        = $booking_dayoff_msg;
                        return wp_json_encode($response);
                        exit();
                    }

                    $is_booked_appointment = $BookingPress->bookingpress_is_appointment_booked($appointment_service_id, $appointment_selected_date, $appointment_start_time, $appointment_end_time, 0, false, $posted_data );
                    if( !empty( $is_booked_appointment ) ){
                        $response = json_decode( $is_booked_appointment );            
                        return wp_json_encode($response);
                        exit();
                    }                    


                               
                    if( 'disabled' != $minimum_time_required ){
                        $bookingpress_slot_start_datetime       = $appointment_selected_date . ' ' . $appointment_start_time;
                        $bookingpress_slot_start_time_timestamp = strtotime( $bookingpress_slot_start_datetime );
                        $bookingpress_time_diff = round( abs( current_time('timestamp') - $bookingpress_slot_start_time_timestamp ) / 60, 2 );					
                        //echo $bookingpress_time_diff; exit;
                        /*  Add Day Service Validation For minimum time required */
                        $bookingpress_service_duration_unit = isset($bookingpress_appointment_data['selected_service_duration_unit'])?$bookingpress_appointment_data['selected_service_duration_unit']:'';
                        if($bookingpress_service_duration_unit == 'd'){	
                            if($minimum_time_required >= 1440){
                                $bookingpress_time_diff = round( abs( strtotime(date('Y-m-d')) - $bookingpress_slot_start_time_timestamp ) / 60, 2 )+5;							
                            }
                        }            
                        if( $bookingpress_time_diff <= $minimum_time_required ){
                            $response['variant']              = 'error';
                            $response['title']                = 'Error';
                            $response['msg']                  = esc_html__("Sorry, Booking can not be done as minimum required time before booking is already passed", "bookingpress-recurring-appointments");
                            return wp_json_encode($response);
                            exit;
                        }
                    }
                    
                    
                    if(isset($get_recurring_timings[ $rec_index ]) && is_array($get_recurring_timings[ $rec_index ])){
                        $timings = array_values( $get_recurring_timings[ $rec_index ] );
                    }else{
                        $timings = array();
                    }                                       

                    $appointment_end_time = ($appointment_end_time == '00:00:00' || $appointment_end_time == '00:00') ? '24:00' : date('H:i',strtotime($appointment_end_time));                    
                    $time_slot_start_key = array_search( date('H:i',strtotime($appointment_start_time)), array_column( $timings, 'store_start_time' ) );
                    $time_slot_end_key = array_search($appointment_end_time, array_column( $timings, 'store_end_time' ) ); 

                    if((trim($time_slot_start_key) === '' || trim($time_slot_end_key) === '') && 'd' != $bookingpress_service_duration_unit){
                        $response['variant']              = 'error';
                        $response['title']                = 'Error';
                        $response['msg']                  = esc_html__("Sorry, Booking can not be done as booking time is different than selected timeslot", "bookingpress-recurring-appointments");                         
                        return wp_json_encode($response);
                        exit();
                    }  

                }
                return wp_json_encode($response);
                exit();
            }  

        }

        
        /**
         * Function for add appointment list for recurring appointment
         *
         * @return void
         */
        function bookingpress_booking_form_summary_appointment_list_func($bookingpress_summary_titles){

            global $BookingPress;
            $bookingpress_service_text = (isset($bookingpress_summary_titles['bookingpress_service_text']))?$bookingpress_summary_titles['bookingpress_service_text']:'';
            $bookingpress_date_time_text = (isset($bookingpress_summary_titles['bookingpress_date_time_text']))?$bookingpress_summary_titles['bookingpress_date_time_text']:'';
            $bookingpress_appointment_details_title_text = (isset($bookingpress_summary_titles['bookingpress_appointment_details_title_text']))?$bookingpress_summary_titles['bookingpress_appointment_details_title_text']:'';
            $bookingpress_summary_step_service_extras_label = (isset($bookingpress_summary_titles['bookingpress_summary_step_service_extras_label']))?$bookingpress_summary_titles['bookingpress_summary_step_service_extras_label']:'';
            $recurring_more_datetime_label = $BookingPress->bookingpress_get_customize_settings('recurring_more_datetime_label','booking_form');
        ?>
           <div v-if="(typeof appointment_step_form_data.is_recurring_appointments != 'undefined' && (appointment_step_form_data.is_recurring_appointments == 'true' || appointment_step_form_data.is_recurring_appointments == true))"> 
                <div v-if="(typeof appointment_step_form_data.is_recurring_appointments != 'undefined' && (appointment_step_form_data.is_recurring_appointments == 'true' || appointment_step_form_data.is_recurring_appointments == true))" class="bpa-front-module--bs-summary-content bpa-front-summary-content__lg">
                    <div class="bpa-front-module--bs-summary-content-item">
                        <span><?php echo esc_html( $bookingpress_service_text ); ?></span>
                        
                        <div class="bpa-front-bs-sm__item-val" :class="appointment_step_form_data.is_extra_service_exists == 1 ? 'bpa_extra_service_summary': ''">{{ appointment_step_form_data.selected_service_name}}</div>                    
                        <div class="bpa-front-bs-sm__extra-wrapper" v-if="appointment_step_form_data.is_extra_service_exists == 1 && appointment_step_form_data.bookingpress_selected_extra_service_count != 0">
                                <el-popover placement="bottom-end" width="280" trigger="hover" popper-class="bpa--summary_front_popover">
                                    <div class="bpa-front-tabs">
                                        <div class="bpa-front-module--booking-summary bpa-front-sm-module--booking-service-wrapper">
                                            <div class="bpa-aaf-extra__item bpa_summary_extras_block bpa-front-module--bs-summary-content" v-for="(extras_details, index) in appointment_step_form_data.bookingpress_selected_extra_details" v-if="extras_details.bookingpress_is_selected == true">
                                                <div class="bpa_summary_extra_body_inner bpa-front-module--bs-summary-content-item">
                                                    <div class="bpa-front-bs-sm__extra-wrapper-container">
                                                        <div class="bpa-front-bs-sm__item-val"> {{extras_details.bookingpress_extra_name }} </div>
                                                        <div class="bpa_summary_extra_inner bpa-front-module--bs-summary-content-item"> 
                                                            <span> {{extras_details.bookingpress_extra_price}} </span> 
                                                            <span class="bpa-summary_service_inner_extra"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"></path><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm-.22-13h-.06c-.4 0-.72.32-.72.72v4.72c0 .35.18.68.49.86l4.15 2.49c.34.2.78.1.98-.24.21-.34.1-.79-.25-.99l-3.87-2.3V7.72c0-.4-.32-.72-.72-.72z"></path></svg> {{extras_details.bookingpress_extra_duration}} </span> 
                                                        </div>
                                                    </div>
                                                    <div class="bpa_summary_extra_inner_cls bpa-front-module--bs-summary-content-item"> <span> {{extras_details.bookingpress_selected_qty}} </span></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <span slot="reference" class="bpa--summary_extra_count_name">{{appointment_step_form_data.bookingpress_selected_extra_service_count}} <?php echo esc_html($bookingpress_summary_step_service_extras_label); ?></span>
                                </el-popover>
                        </div>
                    </div>                        
                    <div class="bpa-front-module--bs-summary-content-item">
                        <span><?php echo esc_html( $bookingpress_date_time_text ); ?></span>
                        <div class="bpa-front-bs-sm__item-val" v-if="((bookigpress_time_format_for_booking_form == '1' || bookigpress_time_format_for_booking_form == '2') && appointment_step_form_data.selected_service_duration_unit != 'd')" v-for="(recurring_details, key) in appointment_step_form_data.recurring_appointments.slice(0,1)">
                            <template>
                                {{ recurring_details.selected_date | bookingpress_format_date }}, {{ recurring_details.formated_start_time }} to {{ recurring_details.formated_end_time }}
                            </template>
                        </div>
                        <div class="bpa-front-bs-sm__item-val" v-if="((bookigpress_time_format_for_booking_form == '1' || bookigpress_time_format_for_booking_form == '2') && appointment_step_form_data.selected_service_duration_unit == 'd')" v-for="(recurring_details, key) in appointment_step_form_data.recurring_appointments.slice(0,1)">
                            <template>
                                {{ recurring_details.selected_date | bookingpress_format_date }}
                            </template>
                        </div>
                        <div class="bpa-front-bs-sm__item-val" v-if="((bookigpress_time_format_for_booking_form == '5' || bookigpress_time_format_for_booking_form == '6')  && appointment_step_form_data.selected_service_duration_unit != 'd')" v-for="(recurring_details, key) in appointment_step_form_data.recurring_appointments.slice(0,1)">
                            <template>
                                {{ recurring_details.selected_date | bookingpress_format_date }}, {{ recurring_details.formated_start_time }} - {{ recurring_details.formated_end_time }}
                            </template>
                        </div>
                        <div class="bpa-front-bs-sm__item-val" v-if="((bookigpress_time_format_for_booking_form == '5' || bookigpress_time_format_for_booking_form == '6')  && appointment_step_form_data.selected_service_duration_unit == 'd')" v-for="(recurring_details, key) in appointment_step_form_data.recurring_appointments.slice(0,1)">
                            <template>
                                {{ recurring_details.selected_date | bookingpress_format_date }}
                            </template>
                        </div>
                        <div class="bpa-front-bs-sm__item-val" v-if="((bookigpress_time_format_for_booking_form == '3' || bookigpress_time_format_for_booking_form == '4')  && appointment_step_form_data.selected_service_duration_unit != 'd')" v-for="(recurring_details, key) in appointment_step_form_data.recurring_appointments.slice(0,1)">
                            <template>
                                {{ recurring_details.selected_date | bookingpress_format_date }}, {{ recurring_details.formated_start_time }}
                            </template>
                        </div>
                        <div class="bpa-front-bs-sm__item-val" v-if="((bookigpress_time_format_for_booking_form == '3' || bookigpress_time_format_for_booking_form == '4')  && appointment_step_form_data.selected_service_duration_unit == 'd')" v-for="(recurring_details, key) in appointment_step_form_data.recurring_appointments">
                            <template>
                                {{ recurring_details.selected_date | bookingpress_format_date }}
                            </template>
                        </div>
                        <div class="bpa-front-bs-sm__item-val" v-if="((bookigpress_time_format_for_booking_form == 'bookingpress-wp-inherit-time-format')  && appointment_step_form_data.selected_service_duration_unit != 'd')" v-for="(recurring_details, key) in appointment_step_form_data.recurring_appointments.slice(0,1)">
                            <template>
                                {{ recurring_details.selected_date | bookingpress_format_date }}, {{ recurring_details.formated_start_time }} - {{ recurring_details.formated_end_time }}
                            </template>
                        </div>
                        <div class="bpa-front-bs-sm__item-val" v-if="((bookigpress_time_format_for_booking_form == 'bookingpress-wp-inherit-time-format')  && appointment_step_form_data.selected_service_duration_unit == 'd')" v-for="(recurring_details, key) in appointment_step_form_data.recurring_appointments.slice(0,1)">
                            <template>
                                {{ recurring_details.selected_date | bookingpress_format_date }}
                            </template>
                        </div>
                        <div class="bpa-front-bs-sm__recurring_datetime-wrapper" v-if="appointment_step_form_data.recurring_appointments.length != 0">
                            <el-popover placement="right" width="335" trigger="hover" popper-class="bpa--summary_front_popover bpa--summary-recurring_front_popover">
                                <div class="bpa-front-tabs">
                                    <div class="bpa-front-module--booking-summary bpa-front-sm-module--booking-service-wrapper">
                                        <div class="bpa-aaf-recurring__item bpa_summary_service_datetime_block bpa-front-module--bs-summary-content" v-for="(recurring_details, key) in appointment_step_form_data.recurring_appointments" >
                                            <div class="bpa_summary_rec_datetime_body_inner bpa-front-module--bs-summary-content-item bpa_rec_popover_datetime_item" v-if="((bookigpress_time_format_for_booking_form == '1' || bookigpress_time_format_for_booking_form == '2') && appointment_step_form_data.selected_service_duration_unit != 'd')">
                                                {{ recurring_details.selected_date | bookingpress_format_date }}, {{ recurring_details.formated_start_time }} to {{ recurring_details.formated_end_time }}
                                            </div>

                                            <div class="bpa_summary_rec_datetime_body_inner bpa-front-module--bs-summary-content-item bpa_rec_popover_datetime_item" v-if="((bookigpress_time_format_for_booking_form == '1' || bookigpress_time_format_for_booking_form == '2') && appointment_step_form_data.selected_service_duration_unit == 'd')">
                                                {{ recurring_details.selected_date | bookingpress_format_date }}
                                            </div>

                                            <div class="bpa_summary_rec_datetime_body_inner bpa-front-module--bs-summary-content-item bpa_rec_popover_datetime_item" v-if="((bookigpress_time_format_for_booking_form == '5' || bookigpress_time_format_for_booking_form == '6')  && appointment_step_form_data.selected_service_duration_unit != 'd')">
                                                {{ recurring_details.selected_date | bookingpress_format_date }}, {{ recurring_details.formated_start_time }} - {{ recurring_details.formated_end_time }}
                                            </div>

                                            <div class="bpa_summary_rec_datetime_body_inner bpa-front-module--bs-summary-content-item bpa_rec_popover_datetime_item" v-if="((bookigpress_time_format_for_booking_form == '5' || bookigpress_time_format_for_booking_form == '6')  && appointment_step_form_data.selected_service_duration_unit == 'd')">
                                                {{ recurring_details.selected_date | bookingpress_format_date }}
                                            </div>

                                            <div class="bpa_summary_rec_datetime_body_inner bpa-front-module--bs-summary-content-item bpa_rec_popover_datetime_item" v-if="((bookigpress_time_format_for_booking_form == '3' || bookigpress_time_format_for_booking_form == '4')  && appointment_step_form_data.selected_service_duration_unit != 'd')">
                                            {{ recurring_details.selected_date | bookingpress_format_date }}, {{ recurring_details.formated_start_time }}
                                            </div>

                                            <div class="bpa_summary_rec_datetime_body_inner bpa-front-module--bs-summary-content-item bpa_rec_popover_datetime_item" v-if="((bookigpress_time_format_for_booking_form == '3' || bookigpress_time_format_for_booking_form == '4')  && appointment_step_form_data.selected_service_duration_unit == 'd')">
                                            {{ recurring_details.selected_date | bookingpress_format_date }}
                                            </div>

                                            <div class="bpa_summary_rec_datetime_body_inner bpa-front-module--bs-summary-content-item bpa_rec_popover_datetime_item" v-if="((bookigpress_time_format_for_booking_form == 'bookingpress-wp-inherit-time-format')  && appointment_step_form_data.selected_service_duration_unit != 'd')">
                                            {{ recurring_details.selected_date | bookingpress_format_date }}, {{ recurring_details.formated_start_time }} - {{ recurring_details.formated_end_time }}
                                            </div>    

                                            <div class="bpa_summary_rec_datetime_body_inner bpa-front-module--bs-summary-content-item bpa_rec_popover_datetime_item" v-if="((bookigpress_time_format_for_booking_form == 'bookingpress-wp-inherit-time-format')  && appointment_step_form_data.selected_service_duration_unit == 'd')">
                                            {{ recurring_details.selected_date | bookingpress_format_date }}
                                            </div>   
                                        </div>
                                    </div>
                                </div>
                            <span slot="reference" class="bpa--summary_service_datetime_count_name">{{appointment_step_form_data.recurring_appointments.length-1}} <?php echo esc_html(stripslashes_deep($recurring_more_datetime_label)); ?></span>
                            </el-popover>    
                        </div>
                        <?php
                            do_action('bookingpress_add_content_after_date_time_summary');
                        ?>
                    </div>                        
                </div>  
                <div v-if="(appointment_step_form_data.recurring_appointments.length != 0 && typeof appointment_step_form_data.is_recurring_appointments != 'undefined' && (appointment_step_form_data.is_recurring_appointments == 'true' || appointment_step_form_data.is_recurring_appointments == true))" class="bpa-front-module--bs-summary-content bpa-front-summary-content__sm">
                    <div class="bpa-front-module--bs-summary-content-item">
                        <span><?php echo esc_html($bookingpress_appointment_details_title_text); ?></span>
                        <div class="bpa-front-bs-sm__item-val">{{ appointment_step_form_data.selected_service_name}}</div>  
                        <div class="bpa-front-bs-sm__extra-wrapper" v-if="appointment_step_form_data.is_extra_service_exists == 1 && appointment_step_form_data.bookingpress_selected_extra_service_count != 0 && typeof(bookingpress_cart_addon) == 'undefined'">
                            <el-popover placement="bottom" width="280" trigger="hover" popper-class="bpa--summary_front_popover">
                                <div class="bpa-front-tabs">
                                    <div class="bpa-front-module--booking-summary bpa-front-sm-module--booking-service-wrapper">
                                        <div class="bpa-aaf-extra__item bpa_summary_extras_block bpa-front-module--bs-summary-content" v-for="(extras_details, index) in appointment_step_form_data.bookingpress_selected_extra_details" v-if="extras_details.bookingpress_is_selected == true">
                                            <div class="bpa_summary_extra_body_inner bpa-front-module--bs-summary-content-item">
                                                <div class="bpa-front-bs-sm__extra-wrapper-container">
                                                    <div class="bpa-front-bs-sm__item-val"> {{extras_details.bookingpress_extra_name }} </div>
                                                    <div class="bpa_summary_extra_inner bpa-front-module--bs-summary-content-item"> 
                                                        <span> {{extras_details.bookingpress_extra_price}} </span> 
                                                        <span class="bpa-summary_service_inner_extra"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"></path><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm-.22-13h-.06c-.4 0-.72.32-.72.72v4.72c0 .35.18.68.49.86l4.15 2.49c.34.2.78.1.98-.24.21-.34.1-.79-.25-.99l-3.87-2.3V7.72c0-.4-.32-.72-.72-.72z"></path></svg> {{extras_details.bookingpress_extra_duration}} </span> 
                                                    </div>
                                                </div>
                                                <div class="bpa_summary_extra_inner_cls bpa-front-module--bs-summary-content-item"> <span> {{extras_details.bookingpress_selected_qty}} </span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <span slot="reference" class="bpa--summary_extra_count_name">{{appointment_step_form_data.bookingpress_selected_extra_service_count}} <?php echo esc_html(stripslashes_deep($bookingpress_summary_step_service_extras_label)); ?></span>
                            </el-popover>
                        </div>

                        <div class="bpa-front-bs-sm__item-vals" v-for="(recurring_details, key) in appointment_step_form_data.recurring_appointments.slice(0,1)">
                            <div class="bpa-front-bs-sm__item-val" v-if="(bookigpress_time_format_for_booking_form == '1' || bookigpress_time_format_for_booking_form == '2') && appointment_step_form_data.selected_service_duration_unit != 'd'">{{ recurring_details.selected_date | bookingpress_format_date }}, {{ recurring_details.formated_start_time }} to {{ recurring_details.formated_end_time }}
                            </div>
                            <div class="bpa-front-bs-sm__item-val" v-if="(bookigpress_time_format_for_booking_form == '1' || bookigpress_time_format_for_booking_form == '2') && appointment_step_form_data.selected_service_duration_unit == 'd'">{{ recurring_details.selected_date | bookingpress_format_date }}
                            </div>	

                            <div class="bpa-front-bs-sm__item-val" v-if="(bookigpress_time_format_for_booking_form == '5' || bookigpress_time_format_for_booking_form == '6') && appointment_step_form_data.selected_service_duration_unit != 'd'">{{ recurring_details.selected_date | bookingpress_format_date }}, {{ recurring_details.formated_start_time }} - {{ recurring_details.formated_end_time }}
                            </div>
                            <div class="bpa-front-bs-sm__item-val" v-if="(bookigpress_time_format_for_booking_form == '5' || bookigpress_time_format_for_booking_form == '6') && appointment_step_form_data.selected_service_duration_unit == 'd'">{{ recurring_details.selected_date | bookingpress_format_date }}
                            </div>
                            
                            <div class="bpa-front-bs-sm__item-val" v-if="(bookigpress_time_format_for_booking_form == '3' || bookigpress_time_format_for_booking_form == '4') && appointment_step_form_data.selected_service_duration_unit != 'd'">{{ recurring_details.selected_date | bookingpress_format_date }}, {{ recurring_details.formated_start_time }}
                            </div>
                            <div class="bpa-front-bs-sm__item-val" v-if="(bookigpress_time_format_for_booking_form == '3' || bookigpress_time_format_for_booking_form == '4') && appointment_step_form_data.selected_service_duration_unit == 'd'">{{ recurring_details.selected_date | bookingpress_format_date }}
                            </div>
                        </div>
                        <div class="bpa-front-bs-sm__recurring_datetime-wrapper" v-if="appointment_step_form_data.recurring_appointments.length != 1 && appointment_step_form_data.recurring_appointments.length != 0">
                            <el-popover placement="bottom-end" width="335" trigger="hover" popper-class="bpa--summary_front_popover">
                                <div class="bpa-front-tabs">
                                    <div class="bpa-front-module--booking-summary bpa-front-sm-module--booking-service-wrapper">
                                        <div class="bpa-aaf-recurring__item bpa_summary_service_datetime_block bpa-front-module--bs-summary-content" v-for="(recurring_details, key) in appointment_step_form_data.recurring_appointments" >
                                            <div class="bpa_summary_rec_datetime_body_inner bpa-front-module--bs-summary-content-item bpa-front-module--bs-summary-content-item-rec bpa_rec_popover_datetime_item" v-if="(bookigpress_time_format_for_booking_form == '1' || bookigpress_time_format_for_booking_form == '2') && appointment_step_form_data.selected_service_duration_unit != 'd'">
                                                {{ recurring_details.selected_date | bookingpress_format_date }}, {{ recurring_details.formated_start_time }} to {{ recurring_details.formated_end_time }}
                                            </div>
                                            <div class="bpa_summary_rec_datetime_body_inner bpa-front-module--bs-summary-content-item bpa-front-module--bs-summary-content-item-rec bpa_rec_popover_datetime_item" v-else-if="(bookigpress_time_format_for_booking_form == '1' || bookigpress_time_format_for_booking_form == '2') && appointment_step_form_data.selected_service_duration_unit == 'd'">
                                                {{ recurring_details.selected_date | bookingpress_format_date }}
                                            </div>
                                            <div class="bpa_summary_rec_datetime_body_inner bpa-front-module--bs-summary-content-item bpa-front-module--bs-summary-content-item-rec bpa_rec_popover_datetime_item" v-else-if="(bookigpress_time_format_for_booking_form == '5' || bookigpress_time_format_for_booking_form == '6') && appointment_step_form_data.selected_service_duration_unit != 'd'">
                                            {{ recurring_details.selected_date | bookingpress_format_date }}, {{ recurring_details.formated_start_time }} - {{ recurring_details.formated_end_time }}
                                            </div>
                                            <div class="bpa_summary_rec_datetime_body_inner bpa-front-module--bs-summary-content-item bpa-front-module--bs-summary-content-item-rec bpa_rec_popover_datetime_item" v-else-if="(bookigpress_time_format_for_booking_form == '5' || bookigpress_time_format_for_booking_form == '6') && appointment_step_form_data.selected_service_duration_unit == 'd'">
                                            {{ recurring_details.selected_date | bookingpress_format_date }}
                                            </div>
                                            <div class="bpa_summary_rec_datetime_body_inner bpa-front-module--bs-summary-content-item bpa-front-module--bs-summary-content-item-rec bpa_rec_popover_datetime_item" v-else-if="(bookigpress_time_format_for_booking_form == '3' || bookigpress_time_format_for_booking_form == '4') && appointment_step_form_data.selected_service_duration_unit != 'd'">
                                            {{ recurring_details.selected_date | bookingpress_format_date }}, {{ recurring_details.formated_start_time }}
                                            </div>
                                            <div class="bpa_summary_rec_datetime_body_inner bpa-front-module--bs-summary-content-item bpa-front-module--bs-summary-content-item-rec bpa_rec_popover_datetime_item" v-else-if="(bookigpress_time_format_for_booking_form == '3' || bookigpress_time_format_for_booking_form == '4') && appointment_step_form_data.selected_service_duration_unit == 'd'">
                                            {{ recurring_details.selected_date | bookingpress_format_date }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <span slot="reference" class="bpa--summary_service_datetime_count_name">{{appointment_step_form_data.recurring_appointments.length-1}} <?php echo esc_html(stripslashes_deep($recurring_more_datetime_label)); ?></span>
                            </el-popover>
                        </div>
                        <?php
                            do_action('bookingpress_add_content_after_date_time_summary');
                        ?>

                    </div>
                </div>
            </div>  
        <?php 
        }

        /**
         * Function for add final step summary when recurring appointment added
         *
         * @return void
         */
        function bookingpress_add_summary_total_block_func(){
        ?>             
            <div class="bpa-front-module-container bpa-front-module--booking-summary" v-if="(typeof appointment_step_form_data.is_recurring_appointments != 'undefined' && (appointment_step_form_data.is_recurring_appointments == true || appointment_step_form_data.is_recurring_appointments == 'true'))">
                <div class="bpa-front-module--bs-amount-details" :class="[(is_coupon_activated == '1') ? 'bpa-is-coupon-module-enable' : '', (bookingpress_is_deposit_payment_activate == '1') ? 'bpa-is-deposit-module-enable' : '', (is_tax_activated == '1') ? 'bpa-is-tax-module-enable' : '']">
                    <div class="bpa-fm--bs-amount-item"> <!-- v-if="is_tax_activated == '1' || is_coupon_activated == '1'" -->
                        <div class="bpa-bs-ai__item">{{subtotal_text}}</div>
                        <div class="bpa-bs-ai__item">{{ appointment_step_form_data.bookingpress_recurring_total_with_currency }}</div>
                    </div>
                    <?php do_action('bookingpress_after_subtotal_extra_calculation_data'); ?>
                    <div class="bpa-fm--bs-amount-item" :class="(is_tax_activated == '1') ? 'bpa-fm--bs-amount-item--tax-module' : ''" v-if="is_tax_activated == '1' && (appointment_step_form_data.tax_percentage != '0' || appointment_step_form_data.tax_percentage != '') && ( appointment_step_form_data.tax_price_display_options == '' || appointment_step_form_data.tax_price_display_options == undefined ||  appointment_step_form_data.tax_price_display_options == 'exclude_taxes' || ( appointment_step_form_data.tax_price_display_options != 'exclude_taxes' && appointment_step_form_data.display_tax_order_summary == 'true' ))">
                        <div class="bpa-bs-ai__item" v-if="typeof tax_title != 'undefined'">{{tax_title}}</div>
                        <div class="bpa-bs-ai__item" v-else><?php esc_html_e( 'Tax', 'bookingpress-recurring-appointments' ); ?></div>
                        <div class="bpa-bs-ai__item">+{{ appointment_step_form_data.tax_amount }}</div>
                    </div>
                    <div class="bpa-fm--bs-amount-item bpa-is-coupon-applied" v-if="is_coupon_activated == '1' && coupon_applied_status == 'success'">
                        <div class="bpa-bs-ai__item">
                            {{couon_applied_title}}											
                            <span>{{ appointment_step_form_data.coupon_code }}<svg @click="bookingpress_remove_coupon_code" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M18.3 5.71c-.39-.39-1.02-.39-1.41 0L12 10.59 7.11 5.7c-.39-.39-1.02-.39-1.41 0-.39.39-.39 1.02 0 1.41L10.59 12 5.7 16.89c-.39.39-.39 1.02 0 1.41.39.39 1.02.39 1.41 0L12 13.41l4.89 4.89c.39.39 1.02.39 1.41 0 .39-.39.39-1.02 0-1.41L13.41 12l4.89-4.89c.38-.38.38-1.02 0-1.4z"/></svg></span>
                        </div>
                        <div class="bpa-bs-ai__item bpa-is-ca__price">-{{ appointment_step_form_data.coupon_discount_amount_with_currecny }}</div>
                    </div>								
                    <div class="bpa-fm--bs__coupon-module-textbox" v-if="is_coupon_activated == '1' && coupon_applied_status == 'error' && ('undefined' == typeof appointment_step_form_data.is_waiting_list || appointment_step_form_data.is_waiting_list == false)">
                        <div class="bpa-cmt__left">
                            <span class="bpa-front-form-label">{{coupon_code_title}}</span>
                        </div>
                        <div class="bpa-cmt__right">
                            <el-input class="bpa-front-form-control" v-model="appointment_step_form_data.coupon_code" :placeholder="coupon_code_field_title" :disabled="bpa_coupon_apply_disabled"></el-input>
                            <div class="bpa-bs__coupon-validation --is-error" v-if="coupon_applied_status == 'error' && coupon_code_msg != ''">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 7c.55 0 1 .45 1 1v4c0 .55-.45 1-1 1s-1-.45-1-1V8c0-.55.45-1 1-1zm-.01-5C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm1-3h-2v-2h2v2z"/></svg>
                                <p>{{ coupon_code_msg }}</p>
                            </div>
                            <div class="bpa-bs__coupon-validation --is-success" v-if="coupon_applied_status == 'success' && coupon_code_msg != ''">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zM9.29 16.29 5.7 12.7c-.39-.39-.39-1.02 0-1.41.39-.39 1.02-.39 1.41 0L10 14.17l6.88-6.88c.39-.39 1.02-.39 1.41 0 .39.39.39 1.02 0 1.41l-7.59 7.59c-.38.39-1.02.39-1.41 0z"/></svg>
                                <p>{{ coupon_code_msg }}</p>
                            </div>
                            <el-button class="bpa-front-btn bpa-front-btn--primary" :class="(coupon_apply_loader == '1') ? 'bpa-front-btn--is-loader' : ''" @click="bookingpress_apply_coupon_code" :disabled="bpa_coupon_apply_disabled">
                                <span class="bpa-btn__label" v-if="bpa_coupon_apply_disabled == 0">{{coupon_apply_button_label}}</span>
                                <span class="bpa-btn__label" v-else><?php esc_html_e( 'Applied', 'bookingpress-recurring-appointments' ); ?></span>
                                <div class="bpa-front-btn--loader__circles">
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                </div>
                            </el-button>
                        </div>
                    </div>
                    <!-- add action for tip -->
                    <?php do_action('bookingpress_add_content_after_subtotal_data_frontend'); ?>
                    <div class="bpa-fm--bs-amount-item bpa-is-total-row" v-if="bookingpress_is_deposit_payment_activate != '1' || (bookingpress_is_deposit_payment_activate == '1' && (appointment_step_form_data.selected_payment_method == '' || appointment_step_form_data.selected_payment_method == 'on-site'))">
                        <div class="bpa-bs-ai__item"><span v-html="bookingpress_total_amount_text"></span> <span class="bpa-fm-tr__tax-included-label" v-if="appointment_step_form_data.tax_price_display_options != 'exclude_taxes'">{{ appointment_step_form_data.included_tax_label }}</span></div>
                        <div class="bpa-bs-ai__item --bpa-is-total-price">{{ appointment_step_form_data.total_payable_amount_with_currency }}</div>
                    </div>
                    <div class="bpa-fm--bs__deposit-payment-module" v-if="bookingpress_is_deposit_payment_activate == '1' && appointment_step_form_data.selected_payment_method != '' && appointment_step_form_data.selected_payment_method != 'on-site'" >
                        <div class="bpa-bs__dpm-title" v-if="bookingpress_deposit_payment_method == 'allow_customer_to_pay_full_amount'">{{deposit_heading_title}}</div>
                        <div class="bpa-dpm__type-selection" v-if="bookingpress_deposit_payment_method == 'allow_customer_to_pay_full_amount'">
                            <el-radio class="bpa-front-form-control--radio" v-model="appointment_step_form_data.bookingpress_deposit_payment_method" label="deposit_or_full_price" @change="bookingpress_get_final_step_amount()">{{deposit_title}}</el-radio>
                            <el-radio class="bpa-front-form-control--radio" v-model="appointment_step_form_data.bookingpress_deposit_payment_method" label="allow_customer_to_pay_full_amount" @change="bookingpress_get_final_step_amount()">{{full_payment_title}}</el-radio>
                        </div>
                        <div class="bpa-dpm__item" v-if="appointment_step_form_data.bookingpress_deposit_payment_method == 'deposit_or_full_price'">
                            <div class="bpa-dpm-item__label --bpa-is-label-inline">{{deposit_paying_amount_title}}</div>
                            <div class="bpa-dpm-item__label" v-if="appointment_step_form_data.deposit_payment_type != 'percentage'">{{ appointment_step_form_data.bookingpress_deposit_total_with_currency }}</div>
                            <div class="bpa-dpm-item__label" v-else> {{ appointment_step_form_data.deposit_payment_amount_percentage }}% ( {{ appointment_step_form_data.bookingpress_deposit_total_with_currency }} )</div>                            
                        </div>
                        <div class="bpa-dpm__item --bpa-is-dpm-total-item">
                            <div class="bpa-dpm-item__total-label" v-if="appointment_step_form_data.bookingpress_deposit_payment_method == 'deposit_or_full_price'">{{deposit_remaining_amount_title}} <span class="bpa-fm-tr__tax-included-label" v-if="appointment_step_form_data.tax_price_display_options != 'exclude_taxes'">{{ appointment_step_form_data.included_tax_label }}</span></div>
                            <div class="bpa-dpm-item__total-label" v-else><span v-html="bookingpress_total_amount_text"></span> <span class="bpa-fm-tr__tax-included-label" v-if="appointment_step_form_data.tax_price_display_options != 'exclude_taxes'">{{ appointment_step_form_data.included_tax_label }}</span></div>
                            <div class="bpa-dpm-item__total-label --bpa-is-total-price" v-if="appointment_step_form_data.bookingpress_deposit_payment_method == 'deposit_or_full_price'">{{ appointment_step_form_data.bookingpress_deposit_due_amount_total_with_currency }}</div>
                            <div class="bpa-dpm-item__total-label --bpa-is-total-price" v-else>{{ appointment_step_form_data.total_payable_amount_with_currency }}</div>
                        </div>
                    </div>

                </div>
            </div>                       
        <?php 
        }

        function bookingpress_sub_total_amount_payable_modify_outside_func($bookingpress_sub_total_amount_payable_modify_outside){

            $bookingpress_sub_total_amount_payable_modify_outside.='            
                if(typeof vm.appointment_step_form_data.recurring_appointments != "undefined"){
                    if(vm.appointment_step_form_data.is_recurring_appointments == true || vm.appointment_step_form_data.is_recurring_appointments == "true"){
                        
                        total_payable_amount = vm.appointment_step_form_data.bookingpress_recurring_total; 
                        total_payable_amount_without_tax = parseFloat(total_payable_amount);

                        if( typeof tax_amount != "undefined" ){
                            total_payable_amount_without_tax = parseFloat( total_payable_amount ) - parseFloat( tax_amount );
                        }                        
                        //total_payable_amount_without_tax = total_payable_amount;                        
                        is_cart_addon = false;								

                    }                    
                }
            ';
            return $bookingpress_sub_total_amount_payable_modify_outside;

        }

        function bookingpress_calculate_total_after_apply_diposit_outside_func($bookingpress_calculate_total_after_apply_diposit_outside){
            $bookingpress_calculate_total_after_apply_diposit_outside.='
            
            if(typeof vm.appointment_step_form_data.recurring_appointments != "undefined"){
                if(vm.appointment_step_form_data.is_recurring_appointments == true || vm.appointment_step_form_data.is_recurring_appointments == "true"){
                    
                    var bpa_deposit_due_amount_total = bookingpress_deposit_due_amt;
                    if( "allow_customer_to_pay_full_amount" == deposit_method ){
                        vm.appointment_step_form_data.bookingpress_deposit_due_amount_total = bookingpress_deposit_due_amt + tax_amount;
                        vm.appointment_step_form_data.bookingpress_deposit_due_amount_total_with_currency = vm.bookingpress_price_with_currency_symbol( bookingpress_deposit_due_amt + tax_amount );
                    } else {

                        if( 1 == vm.is_tax_activated ){
                                                          
                                
                                let bpa_deposit_due_amount_total = ( parseFloat( total_payable_amount ) - parseFloat( vm.appointment_step_form_data.bookingpress_deposit_total ) );	                                
                                if( 1 == vm.is_coupon_activated){
                                    
                                    

                                    let coupon_discount = vm.appointment_step_form_data.coupon_discount_amount;
                                    vm.appointment_step_form_data.bookingpress_deposit_due_amount_total = bpa_deposit_due_amount_total - coupon_discount;
                                    vm.appointment_step_form_data.bookingpress_deposit_due_amount_total_with_currency = vm.bookingpress_price_with_currency_symbol( vm.appointment_step_form_data.bookingpress_deposit_due_amount_total );
                                    
                                } else {
                                    vm.appointment_step_form_data.bookingpress_deposit_due_amount_total = bpa_deposit_due_amount_total;
                                    vm.appointment_step_form_data.bookingpress_deposit_due_amount_total_with_currency = vm.bookingpress_price_with_currency_symbol( bpa_deposit_due_amount_total );
                                }
                                
                                
                        } else {	

                            if( 1 == vm.is_coupon_activated){
                                let coupon_discount = vm.appointment_step_form_data.coupon_discount_amount;
                                let bpa_deposit_due_amount_total = ( parseFloat( total_payable_amount ) - parseFloat( vm.appointment_step_form_data.bookingpress_deposit_total ) );
                                vm.appointment_step_form_data.bookingpress_deposit_due_amount_total = bpa_deposit_due_amount_total - coupon_discount;
                                vm.appointment_step_form_data.bookingpress_deposit_due_amount_total_with_currency = vm.bookingpress_price_with_currency_symbol( vm.appointment_step_form_data.bookingpress_deposit_due_amount_total  );
                            }else{
                                let bpa_deposit_due_amount_total = ( parseFloat( total_payable_amount ) - parseFloat( vm.appointment_step_form_data.bookingpress_deposit_total ) );
                                vm.appointment_step_form_data.bookingpress_deposit_due_amount_total = bpa_deposit_due_amount_total;
                                vm.appointment_step_form_data.bookingpress_deposit_due_amount_total_with_currency = vm.bookingpress_price_with_currency_symbol( bpa_deposit_due_amount_total );
                            }
                        }
                    }                    

                }
            }    

            ';
            return $bookingpress_calculate_total_after_apply_diposit_outside;
        }

        function bookingpress_after_edit_recurring_appointments_func(){

            global $BookingPress,$bookingpress_global_options,$bookingpress_appointment_bookings;
            $response  = array();
            $appointment_step_form_data = isset($_POST['appointment_data_obj'])?$_POST['appointment_data_obj']:''; //phpcs:ignore 
            $recurring_form_data = isset($_POST['recurring_form_data'])?$_POST['recurring_form_data']:''; //phpcs:ignore 
			$bookingpress_global_data = $bookingpress_global_options->bookingpress_global_options();
			$default_date_format = $bookingpress_global_data['wp_default_date_format'];           
			$default_time_format = $bookingpress_global_data['wp_default_time_format'];
            if(!empty($recurring_form_data)){
                $_POST['recurring_form_data'] = json_decode( stripslashes_deep( $_POST['recurring_form_data'] ), true ); //phpcs:ignore
                $recurring_form_data = !empty( $_POST['recurring_form_data'] ) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['recurring_form_data']) : array(); //phpcs:ignore  
            }            
            $appointment_data_obj =  json_decode( stripslashes_deep( $_POST['appointment_data_obj'] ), true ); //phpcs:ignore
            $appointment_data_obj['selected_date'] = $recurring_form_data['start_date'];
            $appointment_data_obj['selected_start_time'] = $recurring_form_data['start_time'];            
            $appointment_data_obj['selected_end_time'] = $recurring_form_data['end_time'];
            

            $_POST['appointment_data_obj'] = json_encode($appointment_data_obj);

            if(!empty($appointment_step_form_data)){
                $_POST['appointment_step_form_data'] = json_decode( stripslashes_deep( $_POST['appointment_data_obj'] ), true ); //phpcs:ignore
                $appointment_step_form_data = !empty( $_POST['appointment_step_form_data'] ) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['appointment_step_form_data']) : array(); //phpcs:ignore   
            }
            $wpnonce               = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';            
            $bpa_verify_nonce_flag = wp_verify_nonce($wpnonce, 'bpa_wp_nonce');
			if (!$bpa_verify_nonce_flag){
				$response                = array();
				$response['variant']     = 'error';
				$response['title']       = esc_html__( 'Error', 'bookingpress-recurring-appointments' );
				$response['msg']         = esc_html__( 'Sorry, Your request can not be processed due to security reason.', 'bookingpress-recurring-appointments' );
				$response['coupon_data'] = array();
				echo wp_json_encode( $response );
				die();
			}
                        
            $recurring_edit_index = (isset($appointment_step_form_data['recurring_edit_index']))?$appointment_step_form_data['recurring_edit_index']:'';
            $recurring_appointments = (isset($appointment_step_form_data['recurring_appointments']))?$appointment_step_form_data['recurring_appointments']:'';
            $bookingpress_form_token = !empty( $appointment_step_form_data['bookingpress_form_token'] ) ? sanitize_text_field($appointment_step_form_data['bookingpress_form_token']):sanitize_text_field($appointment_step_form_data['bookingpress_uniq_id']);            
            $bookingpress_recurring_form_token = !empty( $appointment_step_form_data['bookingpress_recurring_form_token'] ) ? sanitize_text_field($appointment_step_form_data['bookingpress_recurring_form_token']):'';
            if(!empty($bookingpress_recurring_form_token)){
                $bookingpress_form_token = $bookingpress_recurring_form_token;
            }

            if($recurring_edit_index != "" || $recurring_edit_index == 0){

                $get_front_timings = get_transient( 'bpa_front_timings_'. $bookingpress_form_token);
                $get_recurring_timings = get_transient( 'bpa_recurring_front_timings_'. $bookingpress_form_token );                
                if(!empty($recurring_appointments)){
                    foreach($recurring_appointments as $key=>$recappoint){
                        if($key == $recurring_edit_index){
                            if(isset($get_recurring_timings[$key])){
                                $get_recurring_timings[$key] = $get_front_timings;
                            }
                        }
                    }
                }
                set_transient( 'bpa_recurring_front_timings_'. $bookingpress_form_token, $get_recurring_timings, (240 * MINUTE_IN_SECONDS) );

            }
			$response                = array();
			$response['variant']     = 'success';
			$response['title']       = __( 'success', 'bookingpress-recurring-appointments' );
			$response['msg']         = __( 'success', 'bookingpress-recurring-appointments' );
            $response['bookingpress_recurring_form_token']  = $bookingpress_recurring_form_token;

            echo wp_json_encode( $response );
            exit();
        }

        /**
         * Function for remove happy hours message when recurring appointment
         *
         * @param  mixed $happy_hours_offer_message
         * @return void
         */
        function bookingpress_modify_happy_hours_offer_message_func($happy_hours_offer_message){
            $bookingpress_service_id = !empty($_REQUEST['selected_service']) ? intval($_REQUEST['selected_service']) : '';            
            $is_recurring_appointments = (isset($_REQUEST['appointment_data_obj']['is_recurring_appointments']))?$_REQUEST['appointment_data_obj']['is_recurring_appointments']:''; //phpcs:ignore
            if($is_recurring_appointments == 'true'){
                $happy_hours_offer_message = '';    
            }
            return $happy_hours_offer_message;
        }
        
        /**
         * Function for block recurring already added appointment when edit 
         *
         * @param  mixed $total_booked_appointments
         * @param  mixed $selected_date
         * @return void
         */
        function bookingpress_modify_timeslot_for_recurring($total_booked_appointments, $selected_date){

            if( !empty( $_POST['appointment_data_obj']['recurring_appointments'] ) && count( $_POST['appointment_data_obj']['recurring_appointments'] ) > 0 ){ // phpcs:ignore WordPress.Security.NonceVerification
                global $BookingPress;
                $recurring_appointments = array_map( array($BookingPress, 'appointment_sanatize_field'), $_POST['appointment_data_obj']['recurring_appointments'] ); //phpcs:ignore 
                $selected_service_id = !empty( $_POST['service_id'] ) ? intval( $_POST['service_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.                
                if( empty( $selected_service_id ) ){
                    return $total_booked_appointments;
                }          
                $recurring_edit_index = (isset($_POST['appointment_data_obj']['recurring_edit_index']))?$_POST['appointment_data_obj']['recurring_edit_index']:'';   //phpcs:ignore                                                               
                if(!empty($recurring_appointments) && (!empty($recurring_edit_index) || $recurring_edit_index == 0)){
                    $service_step_duration_val = (isset($_POST['appointment_data_obj']['selected_service_duration']))?intval($_POST['appointment_data_obj']['selected_service_duration']):'';   // phpcs:ignore
                    $service_time_duration_unit = (isset($_POST['appointment_data_obj']['selected_service_duration_unit']))?sanitize_text_field($_POST['appointment_data_obj']['selected_service_duration_unit']):''; // phpcs:ignore
                    $service_step_duration_val = apply_filters( 'bookingpress_modify_service_timeslot', $service_step_duration_val, $selected_service_id, $service_time_duration_unit, false);
                    $bookingpress_total_person = (isset($_POST['appointment_data_obj']['bookingpress_selected_bring_members']))?$_POST['appointment_data_obj']['bookingpress_selected_bring_members']:'';  // phpcs:ignore                  
                    if(!is_array($total_booked_appointments)){ $total_booked_appointments = array(); }
                    foreach( $recurring_appointments as $recurring_index => $recurring_data ){
                        if($recurring_edit_index != $recurring_index){
                            if($recurring_data['is_not_avaliable'] == 0 && $recurring_data['selected_date'] == $selected_date){                                
                                
                                $total_booked_appointments[] = array(                                    
                                    'bookingpress_appointment_date' => $recurring_data['selected_date'],
                                    'bookingpress_service_duration_val' => $service_step_duration_val,
                                    'bookingpress_service_duration_unit' => $service_time_duration_unit,
                                    'bookingpress_total_person' => $bookingpress_total_person,
                                    'bookingpress_selected_extra_members' => $bookingpress_total_person,
                                    'bookingpress_appointment_time' => $recurring_data['selected_start_time'].':00',  
                                    'bookingpress_appointment_end_time' => $recurring_data['selected_end_time'].':00',                                        
                                );
                                
                            }                            
                        }
                    }
                }
            }            
            return $total_booked_appointments;
        }
                        
        /**
         * Function for add recurring dynamic validation step
         *
         * @param  mixed $bookingpress_dynamic_validation_for_step_change
         * @return void
         */
        function bookingpress_dynamic_validation_for_step_change_recurring_appointment($bookingpress_dynamic_validation_for_step_change ){

            $bookingpress_dynamic_validation_for_step_change.='
                let current_step_for_recurring = vm.bookingpress_current_tab;
                let is_recurring_appointments = vm.appointment_step_form_data.is_recurring_appointments;                  
                if(next_tab == "datetime" && vm.bookingpress_current_tab != next_tab){
                    vm.appointment_step_form_data.recurring_appointments = [];
                    if( true == is_recurring_appointments || "true" == is_recurring_appointments){
                        vm.appointment_step_form_data.recurring_form_data.start_time = "";                       
                    }
                }
                if( "datetime" == current_step_for_recurring && current_step_for_recurring != next_tab && current_tab != previous_tab){                                        
                    if( true == is_recurring_appointments || "true" == is_recurring_appointments){

                        if(vm.appointment_step_form_data.recurring_appointments.length == 0){
                            vm.bookingpress_set_error_msg( vm.recurring_appointment_add_validation_message );
                            bookingpress_is_validate = 1;
                        }else{
                            var has_not_avaliable_appointment = false;
                            var all_recurring_appointments = vm.appointment_step_form_data.recurring_appointments;
                            all_recurring_appointments.forEach(function(item, index, arr){					
                                if(item.is_not_avaliable == 1){						
                                    has_not_avaliable_appointment = true;
                                }else{
                                       
                                }
                            });                            
                        }
                        if(has_not_avaliable_appointment){
                            vm.bookingpress_set_error_msg( vm.recurring_not_avaliable_appointment_validation_message );
                            bookingpress_is_validate = 1;                                                        
                        }

                    }
                } 
                vm.appointment_step_form_data.recurring_edit_index = "";               
            ';            
            return $bookingpress_dynamic_validation_for_step_change;

        }

        
        /**
         * Function for add recurring language field.
         *
         * @param  mixed $bookingpress_all_language_translation_fields
         * @return void
         */
        function bookingpress_modified_language_translate_fields_func($bookingpress_all_language_translation_fields){
            $bookingpress_recurring_appointment_language_translation_fields = array(                
				'recurring_appointment_labels' => array(
					'recurring_appointment_checkbox' => array('field_type'=>'text','field_label'=>__('Repeat Appointment Label', 'bookingpress-recurring-appointments'),'save_field_type'=>'booking_form'),
                    'recurring_appointment_session_title' => array('field_type'=>'text','field_label'=>__('Sessions Title', 'bookingpress-recurring-appointments'),'save_field_type'=>'booking_form'),
                    'recurring_appointment_start_date_title' => array('field_type'=>'text','field_label'=>__('Start Date', 'bookingpress-recurring-appointments'),'save_field_type'=>'booking_form'),
                    'recurring_appointment_time_title' => array('field_type'=>'text','field_label'=>__('Time', 'bookingpress-recurring-appointments'),'save_field_type'=>'booking_form'),
                    'recurring_appointment_no_of_session_title' => array('field_type'=>'text','field_label'=>__('No of Sessions', 'bookingpress-recurring-appointments'),'save_field_type'=>'booking_form'),
                    'recurring_appointment_frequency_title' => array('field_type'=>'text','field_label'=>__('Frequency', 'bookingpress-recurring-appointments'),'save_field_type'=>'booking_form'),
                    'recurring_appointment_apply_btn_title' => array('field_type'=>'text','field_label'=>__('Apply', 'bookingpress-recurring-appointments'),'save_field_type'=>'booking_form'),
                    'recurring_appointment_upcoming_appointment_title' => array('field_type'=>'text','field_label'=>__('Upcoming Appointments', 'bookingpress-recurring-appointments'),'save_field_type'=>'booking_form'),
                    'recurring_edit_appointment_title' => array('field_type'=>'text','field_label'=>__('Edit Appointment', 'bookingpress-recurring-appointments'),'save_field_type'=>'booking_form'),
                    'recurring_edit_appointment_date_label' => array('field_type'=>'text','field_label'=>__('Date', 'bookingpress-recurring-appointments'),'save_field_type'=>'booking_form'),
                    'recurring_more_datetime_label' => array('field_type'=>'text','field_label'=>__('More Service Date Time Label', 'bookingpress-recurring-appointments'),'save_field_type'=>'booking_form'),
                )   
            );  
            $bookingpress_all_language_translation_fields = array_merge($bookingpress_all_language_translation_fields,$bookingpress_recurring_appointment_language_translation_fields);

            $bookingpress_rec_message_translation_fields = array(                
				'recurring_start_date_validation_message' => array('field_type'=>'text','field_label'=>__('Please select recurring start date.', 'bookingpress-recurring-appointments'),'save_field_type'=>'message_setting'),
                'recurring_timeslot_validation_message' => array('field_type'=>'text','field_label'=>__('Please select recurring timeslot.', 'bookingpress-recurring-appointments'),'save_field_type'=>'message_setting'),
                'recurring_no_of_session_validation_message' => array('field_type'=>'text','field_label'=>__('Please select recurring no of session.', 'bookingpress-recurring-appointments'),'save_field_type'=>'message_setting'),
                'recurring_frequency_validation_message' => array('field_type'=>'text','field_label'=>__('Please select recurring frequency.', 'bookingpress-recurring-appointments'),'save_field_type'=>'message_setting'),
                'recurring_appointment_add_validation_message' => array('field_type'=>'text','field_label'=>__('Please add recurring appointment.', 'bookingpress-recurring-appointments'),'save_field_type'=>'message_setting'),
                'recurring_not_avaliable_appointment_validation_message' => array('field_type'=>'text','field_label'=>__('Please select date & time for not available appointment.', 'bookingpress-recurring-appointments'),'save_field_type'=>'message_setting'),
                'recurring_suggested_message' => array('field_type'=>'text','field_label'=>__('Suggested time slot due to unavailability.', 'bookingpress-recurring-appointments'),'save_field_type'=>'message_setting'),
                'recurring_not_avaliable_message' => array('field_type'=>'text','field_label'=>__('Selected or suggested time slot not available.', 'bookingpress-recurring-appointments'),'save_field_type'=>'message_setting'),
			);						
			$bookingpress_all_language_translation_fields['message_setting'] = array_merge($bookingpress_all_language_translation_fields['message_setting'], $bookingpress_rec_message_translation_fields);

            return $bookingpress_all_language_translation_fields;
		}
        function bookingpress_modified_language_translate_fields_customize_func($bookingpress_all_language_translation_fields){
            $bookingpress_recurring_appointment_language_translation_fields = array(                
				'recurring_appointment_labels' => array(
					'recurring_appointment_checkbox' => array('field_type'=>'text','field_label'=>__('Repeat Appointment Label', 'bookingpress-recurring-appointments'),'save_field_type'=>'booking_form'),
                    'recurring_appointment_session_title' => array('field_type'=>'text','field_label'=>__('Sessions Title', 'bookingpress-recurring-appointments'),'save_field_type'=>'booking_form'),
                    'recurring_appointment_start_date_title' => array('field_type'=>'text','field_label'=>__('Start Date', 'bookingpress-recurring-appointments'),'save_field_type'=>'booking_form'),
                    'recurring_appointment_time_title' => array('field_type'=>'text','field_label'=>__('Time', 'bookingpress-recurring-appointments'),'save_field_type'=>'booking_form'),
                    'recurring_appointment_no_of_session_title' => array('field_type'=>'text','field_label'=>__('No of Sessions', 'bookingpress-recurring-appointments'),'save_field_type'=>'booking_form'),
                    'recurring_appointment_frequency_title' => array('field_type'=>'text','field_label'=>__('Frequency', 'bookingpress-recurring-appointments'),'save_field_type'=>'booking_form'),
                    'recurring_appointment_apply_btn_title' => array('field_type'=>'text','field_label'=>__('Apply', 'bookingpress-recurring-appointments'),'save_field_type'=>'booking_form'),
                    'recurring_appointment_upcoming_appointment_title' => array('field_type'=>'text','field_label'=>__('Upcoming Appointments', 'bookingpress-recurring-appointments'),'save_field_type'=>'booking_form'),
                    'recurring_edit_appointment_title' => array('field_type'=>'text','field_label'=>__('Edit Appointment', 'bookingpress-recurring-appointments'),'save_field_type'=>'booking_form'),
                    'recurring_edit_appointment_date_label' => array('field_type'=>'text','field_label'=>__('Date', 'bookingpress-recurring-appointments'),'save_field_type'=>'booking_form'),
                    'recurring_more_datetime_label' => array('field_type'=>'text','field_label'=>__('More Service Date Time Label', 'bookingpress-recurring-appointments'),'save_field_type'=>'booking_form'),
                    'recurring_daily_label' => array('field_type'=>'text','field_label'=>__('Daily Label', 'bookingpress-recurring-appointments'),'save_field_type'=>'booking_form'),
                    'recurring_weekly_label' => array('field_type'=>'text','field_label'=>__('Weekly Label', 'bookingpress-recurring-appointments'),'save_field_type'=>'booking_form'),
                    'recurring_biweekly_label' => array('field_type'=>'text','field_label'=>__('Biweekly Label', 'bookingpress-recurring-appointments'),'save_field_type'=>'booking_form'),
                    'recurring_monthly_label' => array('field_type'=>'text','field_label'=>__('Monthly Label', 'bookingpress-recurring-appointments'),'save_field_type'=>'booking_form'),                    
                )   
			);  
            $bookingpress_all_language_translation_fields = array_merge($bookingpress_all_language_translation_fields,$bookingpress_recurring_appointment_language_translation_fields);

            return $bookingpress_all_language_translation_fields;
		}
        
        /**
         * Function for add recurring language section.
         *
         * @param  mixed $bookingpress_all_language_translation_fields_section
         * @return void
         */
        function bookingpress_modified_language_translate_fields_section_func($bookingpress_all_language_translation_fields_section){
            $bookingpress_all_language_translation_fields_section['recurring_appointment_labels'] =  __('Recurring Labels', 'bookingpress-recurring-appointments');
			return $bookingpress_all_language_translation_fields_section;
        }
     
        /* Disable Date In Calendar Start */                
        /**
         * Get recurring date
         *
         * @param  mixed $bookingpress_disable_date_xhr_data
         * @return void
         */
        function bookingpress_disable_multiple_days_event_xhr_resp_after_func($bookingpress_disable_date_xhr_data){
            
            $bookingpress_disable_date_xhr_data.='

            if(typeof vm.appointment_step_form_data.is_recurring_appointments != "undefined"){  
                if(vm.appointment_step_form_data.is_recurring_appointments == true || vm.appointment_step_form_data.is_recurring_appointments == "true"){
                    var recurring_appointment_start_counter = parseInt(vm.recurring_appointment_start_counter);
                    recurring_appointment_start_counter = recurring_appointment_start_counter + 1;
                    vm.recurring_appointment_start_counter = recurring_appointment_start_counter;
                    if(recurring_appointment_start_counter > 2 && recurring_appointment_start_counter < 6){                        
                        postData.days_off_disabled_dates = vm.days_off_disabled_dates;
                        postData.next_month = response.data.next_month;
                        postData.next_year = response.data.next_year;
                        postData.counter++;
                        vm.isHoldBookingRequest = true;
                        vm.bookingpress_retrieve_daysoff_for_booked_appointment( postData );                                                    
                    }
                }
            }            
            if(vm.recurring_open_edit_popup == true || (vm.appointment_step_form_data.recurring_edit_index != ""  || vm.recurring_appointment_device == "mobile") ){                      

                let disableDatesRecurring = response.data.days_off_disabled_dates;
                if(typeof disableDatesRecurring != "undefined"){ 

                    let disableDatesRecurring_arr = disableDatesRecurring?.split(",");
                    disableDatesRecurring_arr.forEach(function( date ){                                            
                        let formatted_date = vm.get_formatted_date( date );
                        vm.recurring_edit_disable_dates.push( formatted_date );
                    });
                    vm.recurring_edit_pickerOptions.disabledDate = function(Time){ 
                        if(vm.recurring_max_available_date != "") {                                                     
                            var max_avaliable_time = Date.parse(""+vm.recurring_max_available_date);                            
                            if(Time.getTime() > max_avaliable_time){
                                return true;
                            }                                
                        }  
                        let currentDate = new Date( Time );
                        currentDate = vm.get_formatted_date( currentDate );
                        var date = new Date();                    
                        date.setDate(date.getDate()-1);                    
                        var disable_past_date = Time.getTime() < date.getTime();
                        if( vm.recurring_edit_disable_dates.indexOf( currentDate ) > -1 ){
                            return true;
                        } else {
                            return disable_past_date;
                        }
                    };                

                }

            }else{
                
                let disableDatesRecurring = response.data.days_off_disabled_dates;
                if(typeof disableDatesRecurring !== "undefined") { 
                    
                    let disableDatesRecurring_arr = disableDatesRecurring?.split(",");
                    disableDatesRecurring_arr.forEach(function( date ){                                            
                        let formatted_date = vm.get_formatted_date( date );
                        vm.recurring_disable_dates.push( formatted_date );
                    });
                    vm.recurring_pickerOptions.disabledDate = function(Time){ 
                        if(vm.recurring_max_available_date != "") {                                                     
                            var max_avaliable_time = Date.parse(""+vm.recurring_max_available_date);                            
                            if(Time.getTime() > max_avaliable_time){
                                return true;
                            }                                
                        }  
                        let currentDate = new Date( Time );
                        currentDate = vm.get_formatted_date( currentDate );
                        var date = new Date();                    
                        date.setDate(date.getDate()-1);                    
                        var disable_past_date = Time.getTime() < date.getTime();
                        if( vm.recurring_disable_dates.indexOf( currentDate ) > -1 ){
                            return true;
                        } else {
                            return disable_past_date;
                        }
                    };
                    
                    if(vm.appointment_step_form_data.recurring_form_data.start_date == "" && vm.appointment_step_form_data.selected_date != ""){
                        vm.appointment_step_form_data.recurring_form_data.start_date = vm.appointment_step_form_data.selected_date;
                    }

                }



            }    


            vm.recurring_appointment_loader_edit = false;
            vm.recurring_appointment_time_loader = false;
            vm.recurring_appointment_time_loader_edit = false;

            ';
            return $bookingpress_disable_date_xhr_data;
        }
        /* Set Recurring Time  */        
        /**
         * Function for set recurring time
         *
         * @param  mixed $bookingpress_after_selecting_booking_service
         * @return void
         */
        function bookingpress_after_selecting_booking_service_func($bookingpress_after_selecting_booking_service){
            $bookingpress_after_selecting_booking_service.='


                if(typeof response !== "undefined" && typeof response.data.morning_time  !== "undefined"){
                    let timeslot_response_data = response.data;
                    let morning_times = timeslot_response_data.morning_time;
                    let afternoon_times = timeslot_response_data.afternoon_time;
                    let evening_times = timeslot_response_data.evening_time;
                    let night_times = timeslot_response_data.night_time;                    
                    let timeslot_data = {
                        morning_time: {
                            timeslot_label: vm.hide_time_slot_grouping?"":vm.morning_text,
                            timeslots: morning_times
                        },
                        afternoon_time: {
                            timeslot_label: vm.hide_time_slot_grouping?"":vm.afternoon_text,
                            timeslots: afternoon_times
                        },
                        evening_time: {
                            timeslot_label: vm.hide_time_slot_grouping?"":vm.evening_text,
                            timeslots: evening_times
                        },
                        night_time: {
                            timeslot_label: vm.hide_time_slot_grouping?"":vm.night_text,
                            timeslots: night_times
                        }
                    };                    
                    if(vm.recurring_open_edit_popup == true || (vm.appointment_step_form_data.recurring_edit_index != "" || vm.recurring_appointment_device == "mobile")){                              
                        vm.recurring_edit_appointment_time_slot = timeslot_data;
                    }else{
                        vm.recurring_appointment_time_slot = timeslot_data;
                    }
                }

                vm.recurring_appointment_loader_edit = false;
                vm.recurring_appointment_time_loader = false;
                vm.recurring_appointment_time_loader_edit = false;                
                
            ';
            return $bookingpress_after_selecting_booking_service;
        }
        
        /**
         * function for set recurring time
         *
         * @param  mixed $bookingpress_disable_date_vue_data
         * @return void
         */
        function bookingpress_disable_date_vue_data_modify_func($bookingpress_disable_date_vue_data){
            $bookingpress_disable_date_vue_data.='  
                
                vm.recurring_appointment_start_counter = 0;
                var recurring_max_available_date = "";
                if(typeof response.data.max_available_date !== "undefined") {
                    vm.recurring_max_available_date = response.data.max_available_date;
                }
                /* Service Max Avaliable Date set */
                let recurring_selected_service_id = vm.appointment_step_form_data.selected_service;                                
                if(recurring_selected_service_id != "" && "undefined" != typeof vm.bookingpress_all_services_data[recurring_selected_service_id] ){
                    if( "undefined" != typeof vm.bookingpress_all_services_data[ recurring_selected_service_id ].bookingpress_service_expiration_date && "" != vm.bookingpress_all_services_data[ recurring_selected_service_id ].bookingpress_service_expiration_date && null != vm.bookingpress_all_services_data[ recurring_selected_service_id ].bookingpress_service_expiration_date ) {                        
                        vm.recurring_max_available_date = vm.bookingpress_all_services_data[ recurring_selected_service_id ].bookingpress_service_expiration_date;
                    }                    
                }
                /* Service Max Avaliable Date set */                
                if(vm.recurring_open_edit_popup == true || (vm.appointment_step_form_data.recurring_edit_index != "" || vm.recurring_appointment_device == "mobile")  ){                                          
                    if(vm.appointment_step_form_data.bookingpress_recurring_form_token != ""){
                        vm.appointment_step_form_data.bookingpress_form_token = vm.appointment_step_form_data.bookingpress_recurring_form_token;
                    }                    
                    let disableDatesRecurring = response.data.days_off_disabled_dates;

                    if(typeof disableDatesRecurring !== "undefined") {

                        let disableDatesRecurring_arr = disableDatesRecurring.split(",");                                      
                        let disableDatesRecurring_formatted = [];
                        vm.recurring_edit_disable_dates = [];
                        disableDatesRecurring_arr.forEach(function( date ){                                            
                            let formatted_date = vm.get_formatted_date( date );
                            vm.recurring_edit_disable_dates.push( formatted_date );
                        });
                        vm.recurring_edit_pickerOptions.disabledDate = function(Time){     
                            if(vm.recurring_max_available_date != "") {                                                     
                                var max_avaliable_time = Date.parse(""+vm.recurring_max_available_date);                            
                                if(Time.getTime() > max_avaliable_time){
                                    return true;
                                }                                
                            }                                               
                            let currentDate = new Date( Time );
                            currentDate = vm.get_formatted_date( currentDate );
                            var date = new Date();                    
                            date.setDate(date.getDate()-1);                    
                            var disable_past_date = Time.getTime() < date.getTime();
                            if( vm.recurring_edit_disable_dates.indexOf( currentDate ) > -1 ){
                                return true;
                            } else {
                                return disable_past_date;
                            }
                        };
                        if(typeof response.data.front_timings !== "undefined") {
        
                            let timeslot_response_data = response.data.front_timings;
                            let morning_times = timeslot_response_data.morning_time;
                            let afternoon_times = timeslot_response_data.afternoon_time;
                            let evening_times = timeslot_response_data.evening_time;
                            let night_times = timeslot_response_data.night_time;                    
                            let timeslot_data = {
                                morning_time: {
                                    timeslot_label: vm.hide_time_slot_grouping?"":vm.morning_text,
                                    timeslots: morning_times
                                },
                                afternoon_time: {
                                    timeslot_label: vm.hide_time_slot_grouping?"":vm.afternoon_text,
                                    timeslots: afternoon_times
                                },
                                evening_time: {
                                    timeslot_label: vm.hide_time_slot_grouping?"":vm.evening_text,
                                    timeslots: evening_times
                                },
                                night_time: {
                                    timeslot_label: vm.hide_time_slot_grouping?"":vm.night_text,
                                    timeslots: night_times
                                }
                            };                    
                            vm.recurring_edit_appointment_time_slot = timeslot_data;
                        } 
                    }


                }else{  

                    let disableDatesRecurring = response.data.days_off_disabled_dates;
                    if(typeof disableDatesRecurring !== "undefined") {
                        let disableDatesRecurring_arr = disableDatesRecurring.split(",");                                        
                        let disableDatesRecurring_formatted = [];
                        vm.recurring_disable_dates = [];
                        disableDatesRecurring_arr.forEach(function( date ){                                            
                            let formatted_date = vm.get_formatted_date( date );
                            vm.recurring_disable_dates.push( formatted_date );
                        });
                        vm.recurring_pickerOptions.disabledDate = function(Time){  
                            if(vm.recurring_max_available_date != "") {                                                     
                                var max_avaliable_time = Date.parse(""+vm.recurring_max_available_date);                            
                                if(Time.getTime() > max_avaliable_time){
                                    return true;
                                }                                
                            }                                                                                         
                            let currentDate = new Date( Time );
                            currentDate = vm.get_formatted_date( currentDate );
                            var date = new Date();                    
                            date.setDate(date.getDate()-1);                    
                            var disable_past_date = Time.getTime() < date.getTime();
                            if( vm.recurring_disable_dates.indexOf( currentDate ) > -1 ){
                                return true;
                            } else {
                                return disable_past_date;
                            }
                        };
                        if(typeof response.data.front_timings !== "undefined") {
                            let timeslot_response_data = response.data.front_timings;
                            let morning_times = timeslot_response_data.morning_time;
                            let afternoon_times = timeslot_response_data.afternoon_time;
                            let evening_times = timeslot_response_data.evening_time;
                            let night_times = timeslot_response_data.night_time;                    
                            let timeslot_data = {
                                morning_time: {
                                    timeslot_label: vm.hide_time_slot_grouping?"":vm.morning_text,
                                    timeslots: morning_times
                                },
                                afternoon_time: {
                                    timeslot_label: vm.hide_time_slot_grouping?"":vm.afternoon_text,
                                    timeslots: afternoon_times
                                },
                                evening_time: {
                                    timeslot_label: vm.hide_time_slot_grouping?"":vm.evening_text,
                                    timeslots: evening_times
                                },
                                night_time: {
                                    timeslot_label: vm.hide_time_slot_grouping?"":vm.night_text,
                                    timeslots: night_times
                                }
                            };                    
                            vm.recurring_appointment_time_slot = timeslot_data;
                        } 

                    } 
                } 
                setTimeout(function(){
                    vm.recurring_appointment_loader_edit = false;
                    vm.recurring_appointment_time_loader = false;
                    vm.recurring_appointment_time_loader_edit = false;                    
                },800);
            ';
            return $bookingpress_disable_date_vue_data;
        }
        
        /* Disable Date In Calendar Over */
        /**
         * Function for get recurring appointment
         *
         * @return void
         */
        function bookingpress_get_recurring_appointments_func($return_data = false){

            global $BookingPress,$bookingpress_global_options,$bookingpress_appointment_bookings,$wpdb;
            $response  = array();
            $appointment_step_form_data = isset($_POST['appointment_data_obj'])?$_POST['appointment_data_obj']:''; //phpcs:ignore 
            $recurring_form_data = isset($_POST['recurring_form_data'])?$_POST['recurring_form_data']:''; //phpcs:ignore 
            $bookingpress_service_expiration_date = isset($_POST['bookingpress_service_expiration_date'])?$_POST['bookingpress_service_expiration_date']:''; //phpcs:ignore 
			$bookingpress_global_data = $bookingpress_global_options->bookingpress_global_options();
			$default_date_format = $bookingpress_global_data['wp_default_date_format'];           
			$default_time_format = $bookingpress_global_data['wp_default_time_format'];
            if(!empty($recurring_form_data)){
                $_POST['recurring_form_data'] = json_decode( stripslashes_deep( $_POST['recurring_form_data'] ), true ); //phpcs:ignore
                $recurring_form_data = !empty( $_POST['recurring_form_data'] ) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['recurring_form_data']) : array(); //phpcs:ignore  
            }

            $recurring_start_date_validation_message = $BookingPress->bookingpress_get_settings('recurring_start_date_validation_message','message_setting');
            $recurring_start_date_validation_message = (!empty($recurring_start_date_validation_message))?stripslashes_deep($recurring_start_date_validation_message):'';

            $recurring_timeslot_validation_message = $BookingPress->bookingpress_get_settings('recurring_timeslot_validation_message','message_setting');
            $recurring_timeslot_validation_message = (!empty($recurring_timeslot_validation_message))?stripslashes_deep($recurring_timeslot_validation_message):'';
            
            $recurring_no_of_session_validation_message = $BookingPress->bookingpress_get_settings('recurring_no_of_session_validation_message','message_setting');
            $recurring_no_of_session_validation_message = (!empty($recurring_no_of_session_validation_message))?stripslashes_deep($recurring_no_of_session_validation_message):'';

            $recurring_frequency_validation_message = $BookingPress->bookingpress_get_settings('recurring_frequency_validation_message','message_setting');
            $recurring_frequency_validation_message = (!empty($recurring_frequency_validation_message))?stripslashes_deep($recurring_frequency_validation_message):'';

            $bookigpress_time_format_for_booking_form =  $BookingPress->bookingpress_get_customize_settings('bookigpress_time_format_for_booking_form','booking_form');
			$bookigpress_time_format_for_booking_form =  !empty($bookigpress_time_format_for_booking_form) ? $bookigpress_time_format_for_booking_form : '2';

            if(!isset($recurring_form_data['start_date']) || empty($recurring_form_data['start_date'])){
				$response                = array();
				$response['variant']     = 'error';
				$response['title']       = esc_html__( 'Error', 'bookingpress-recurring-appointments' );
				$response['msg']         =  'recurring_start_date_validation_message';
				$response['coupon_data'] = array();
                if($return_data){
                    return $response;
                }
				echo wp_json_encode( $response );
				die();                
            }
            if(!isset($recurring_form_data['start_time']) || empty($recurring_form_data['start_time'])){
				$response                = array();
				$response['variant']     = 'error';
				$response['title']       = esc_html__( 'Error', 'bookingpress-recurring-appointments' );
				$response['msg']         =  'recurring_timeslot_validation_message';
				$response['coupon_data'] = array();
                if($return_data){
                    return $response;
                }                
				echo wp_json_encode( $response );
				die();                
            }        
            if(!isset($recurring_form_data['no_of_session']) || empty($recurring_form_data['no_of_session'])){
				$response                = array();
				$response['variant']     = 'error';
				$response['title']       = esc_html__( 'Error', 'bookingpress-recurring-appointments' );
				$response['msg']         =  'recurring_no_of_session_validation_message';
				$response['coupon_data'] = array();
                if($return_data){
                    return $response;
                }                
				echo wp_json_encode( $response );
				die();                
            }          
            if(!isset($recurring_form_data['recurring_frequency']) || empty($recurring_form_data['recurring_frequency'])){
				$response                = array();
				$response['variant']     = 'error';
				$response['title']       = esc_html__( 'Error', 'bookingpress-recurring-appointments' );
				$response['msg']         =  'recurring_frequency_validation_message';
				$response['coupon_data'] = array();
                if($return_data){
                    return $response;
                }                
				echo wp_json_encode( $response );
				die();                
            }
            
            $appointment_data_obj =  (!is_array($_POST['appointment_data_obj']))?json_decode( stripslashes_deep( $_POST['appointment_data_obj'] ), true ):$_POST['appointment_data_obj']; // phpcs:ignore
            $appointment_data_obj['selected_date'] = $recurring_form_data['start_date'];
            $appointment_data_obj['selected_start_time'] = $recurring_form_data['start_time'];            
            $appointment_data_obj['selected_end_time'] = $recurring_form_data['end_time'];
            $appointment_data_obj['selected_formatted_start_time'] = $recurring_form_data['formatted_start_time'];
            $appointment_data_obj['selected_formatted_end_time'] = $recurring_form_data['formatted_end_time'];

            $bookingpress_selected_bring_members = (isset($appointment_data_obj['bookingpress_selected_bring_members']))?$appointment_data_obj['bookingpress_selected_bring_members']:1;

            $_POST['appointment_data_obj'] = json_encode($appointment_data_obj,JSON_UNESCAPED_UNICODE);

            if(!empty($appointment_step_form_data)){
                $_POST['appointment_step_form_data'] = json_decode( stripslashes_deep( $_POST['appointment_data_obj'] ), true ); //phpcs:ignore
                $appointment_step_form_data = !empty( $_POST['appointment_step_form_data'] ) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['appointment_step_form_data']) : array(); //phpcs:ignore   
            }
            $wpnonce               = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';            
            $bpa_verify_nonce_flag = wp_verify_nonce($wpnonce, 'bpa_wp_nonce');
			if (!$bpa_verify_nonce_flag){
				$response                = array();
				$response['variant']     = 'error';
				$response['title']       = esc_html__( 'Error', 'bookingpress-recurring-appointments' );
				$response['msg']         = esc_html__( 'Sorry, Your request can not be processed due to security reason.', 'bookingpress-recurring-appointments' );
				$response['coupon_data'] = array();
                if($return_data){
                    return $response;
                }                
				echo wp_json_encode( $response );
				die();
			}
			$response                           = array();
			$response['variant']                = 'error';
			$response['title']                  = __( 'Error', 'bookingpress-recurring-appointments' );
			$response['msg']                    = __( 'Something went wrong..', 'bookingpress-recurring-appointments' );
			$response['recurring_appointments'] = array();            
            $selected_date = (isset($appointment_step_form_data['selected_date']))?sanitize_text_field($appointment_step_form_data['selected_date']):'';
            $recurring_frequency = (isset($recurring_form_data['recurring_frequency']))?$recurring_form_data['recurring_frequency']:'';
            $no_of_session = (isset($recurring_form_data['no_of_session']))?$recurring_form_data['no_of_session']:'';
            $selected_start_time = (isset($appointment_step_form_data['selected_start_time']))?sanitize_text_field($appointment_step_form_data['selected_start_time']):'';            
            $selected_end_time = (isset($appointment_step_form_data['selected_end_time']))?sanitize_text_field($appointment_step_form_data['selected_end_time']):'';
            
            $selected_formatted_start_time = (isset($appointment_step_form_data['selected_formatted_start_time']))?$appointment_step_form_data['selected_formatted_start_time']:'';
            $selected_formatted_end_time = (isset($appointment_step_form_data['selected_formatted_end_time']))?$appointment_step_form_data['selected_formatted_end_time']:'';            

            if(isset($_POST['selected_formatted_start_time'])){
                $selected_formatted_start_time = sanitize_text_field($_POST['selected_formatted_start_time']);
            }
            if(isset($_POST['selected_formatted_end_time'])){
                $selected_formatted_end_time = sanitize_text_field($_POST['selected_formatted_end_time']);
            }

            $get_possible_date_and_times = array();
            $bpa_unique_id = isset($appointment_step_form_data['bookingpress_uniq_id']) ? sanitize_text_field($appointment_step_form_data['bookingpress_uniq_id']) : '';
            $bookingpress_form_token = !empty( $appointment_step_form_data['bookingpress_form_token'] ) ? sanitize_text_field($appointment_step_form_data['bookingpress_form_token']): $bpa_unique_id;

            if(!empty($recurring_frequency)){                
                
                $start_date_timestamp = strtotime($selected_date);
                $next_date = $selected_date;
                if($recurring_frequency == 'daily'){
                    for($i=0;$i<$no_of_session;$i++){                                                                      
                        $get_possible_date_and_times[] = array('is_not_avaliable'=>0,'is_suggested'=>0,'formated_end_time'=>$selected_formatted_end_time,'formated_start_time'=>$selected_formatted_start_time,'selected_date' => $next_date,'selected_start_time' => $selected_start_time,'selected_end_time' => $selected_end_time); 
                        $next_date = date('Y-m-d', strtotime($next_date . ' +1 days'));                         
                    }
                }else if($recurring_frequency == 'weekly'){
                    for($i=0;$i<$no_of_session;$i++){                                                                       
                        $get_possible_date_and_times[] = array('is_not_avaliable'=>0,'is_suggested'=>0,'formated_end_time'=>$selected_formatted_end_time,'formated_start_time'=>$selected_formatted_start_time,'selected_date'=>$next_date,'selected_start_time' => $selected_start_time,'selected_end_time' => $selected_end_time);          
                        $next_date = date('Y-m-d', strtotime($next_date . ' +7 days'));               
                    }
                }else if($recurring_frequency == 'biweekly'){
                    for($i=0;$i<$no_of_session;$i++){                         
                        $get_possible_date_and_times[] = array('is_not_avaliable'=>0,'is_suggested'=>0,'formated_end_time'=>$selected_formatted_end_time,'formated_start_time'=>$selected_formatted_start_time,'selected_date'=>$next_date,'selected_start_time' => $selected_start_time,'selected_end_time' => $selected_end_time);
                        $next_date = date('Y-m-d', strtotime($next_date . ' +14 days'));                        
                    }
                }else if($recurring_frequency == 'monthly'){
                    for($i=0;$i<$no_of_session;$i++){                         
                        $get_possible_date_and_times[] = array('is_not_avaliable'=>0,'is_suggested'=>0,'formated_end_time'=>$selected_formatted_end_time,'formated_start_time'=>$selected_formatted_start_time,'selected_date'=>$next_date,'selected_start_time' => $selected_start_time,'selected_end_time' => $selected_end_time);
                        $next_date = date('Y-m-d', strtotime($next_date . ' +1 month'));                       
                    }
                }
            }                        
            if(!empty($get_possible_date_and_times)){                                
                /* Check Possible Date Is Avaliable Or Not Start */                
                //$get_recurring_timings = get_transient( 'bpa_recurring_front_timings_'.$bookingpress_form_token);                
                if(empty($bookingpress_service_expiration_date)){
                    $bookingpress_period_available_for_booking  = $BookingPress->bookingpress_get_settings( 'period_available_for_booking', 'general_setting' );
                    $bookingpress_service_expiration_date = date('Y-m-d', strtotime( '+' . $bookingpress_period_available_for_booking . ' days' ) );
                }
                $max_avaialble_date_time = strtotime($bookingpress_service_expiration_date);

                /** Holiday Date New Logic Start Here */
                
                    $get_period_available_for_booking = $BookingPress->bookingpress_get_settings('period_available_for_booking', 'general_setting');
                    if(empty( $get_period_available_for_booking )){
                        $get_period_available_for_booking = 365;
                    }
                    $bookingpress_selected_staffmember_id = !empty($appointment_data_obj['bookingpress_selected_staff_member_details']['selected_staff_member_id']) ? intval($appointment_data_obj['bookingpress_selected_staff_member_details']['selected_staff_member_id']) : '';
                    $bookingpress_selected_service= !empty($_REQUEST['selected_service']) ? intval($_REQUEST['selected_service']) : '';        
                    if(empty($bookingpress_selected_service)){
                        $bookingpress_selected_service = !empty( $appointment_data_obj['selected_service'] ) ? $appointment_data_obj['selected_service'] : ( !empty( $appointment_data_obj['appointment_selected_service'] ) ? $appointment_data_obj['appointment_selected_service'] : 0 );
                    }
                    $selected_service_duration_unit = $appointment_data_obj['selected_service_duration_unit'];
                    $selected_service_duration = $appointment_data_obj['selected_service_duration'];        
                    $selected_service_duration_in_min = '';
                    if( 'm' == $selected_service_duration_unit ){
                        $selected_service_duration_in_min = $selected_service_duration;
                    } else {
                        if( 'h' == $selected_service_duration_unit ){
                            $selected_service_duration_in_min = ( MINUTE_IN_SECONDS * $selected_service_duration );
                        }
                    }    
                    
                    $bpa_retrieves_default_disabled_dates = $BookingPress->bookingpress_retrieve_off_days( $selected_date, ( $get_period_available_for_booking + 1 ), $bookingpress_selected_service, $selected_service_duration_in_min, $bookingpress_selected_staffmember_id );                                        
                    if(!empty($bpa_retrieves_default_disabled_dates)){
                        $bpa_retrieves_default_disabled_dates = array_map(function($value){
                            return date('Y-m-d', strtotime($value));
                        }, $bpa_retrieves_default_disabled_dates);     
                    }
                    
                /** Holiday Date New Logic over Here */

                $get_recurring_timings = array();
                foreach($get_possible_date_and_times as $key=>$pdata){

                    $current_selected_date = $pdata['selected_date'];
                    $selected_date_time = strtotime($pdata['selected_date']);                    
                    $selected_start_time = $pdata['selected_start_time'];
                    $all_avaliable_time_slot_available = $bookingpress_appointment_bookings->bookingpress_retrieve_timeslots($current_selected_date,true,false);
                    
                    $get_front_timings = get_transient( 'bpa_front_timings_'. $bookingpress_form_token);                  

                    /* New Holiday Disable For Recurring Appointment Start */
                    $is_not_holiday_date = true;                       
                    if(!empty($bpa_retrieves_default_disabled_dates)){
                        if(in_array($current_selected_date,$bpa_retrieves_default_disabled_dates)){                                
                            $all_avaliable_time_slot_available = array();                                
                        }                            
                    }                          
                    /* New Holiday Disable For Recurring Appointment Over */
                    if(!empty($all_avaliable_time_slot_available)){
                    
                        $bookingpress_selected_bring_members = (int)$bookingpress_selected_bring_members;

                        $single_timeslot_arr = array();
                        $is_time_avaliable = false;
                        foreach($all_avaliable_time_slot_available as $timecheckdata){                            
                          foreach($timecheckdata as $timecheck){                                                         
                                                        
                            $max_total_capacity = $timecheck['max_total_capacity'];
                            $total_booked = $timecheck['total_booked'];
                            $max_capacity = $timecheck['max_capacity'];
                            
                            if($timecheck['store_start_time'] == $selected_start_time && $total_booked < $max_total_capacity && $max_capacity >=  $bookingpress_selected_bring_members){
                                $is_time_avaliable = true;
                                break;
                            }
                            if($total_booked < $max_total_capacity && $max_capacity >=  $bookingpress_selected_bring_members){
                                $single_timeslot_data = $timecheck;
                                $single_timeslot_data['store_start_time_compare'] = strtotime($timecheck['store_start_time']);
                                $single_timeslot_arr[] = $single_timeslot_data;  
                            }
                          }                            
                        }
                        if($selected_date_time > $max_avaialble_date_time){
                            $is_time_avaliable = false;
                        }
                        /* Suggested Logic Start here */
                        if(!$is_time_avaliable){
                            $is_time_slot_available = $bookingpress_appointment_bookings->bookingpress_retrieve_timeslots( $current_selected_date, true, true );
                            if($selected_date_time > $max_avaialble_date_time){
                                $is_time_slot_available = '';
                            }                            
                            if($is_time_slot_available){                               
                                $get_possible_date_and_times[$key]['is_suggested'] = 1;
                                if(!empty($single_timeslot_arr)){
                                    usort($single_timeslot_arr, function($a, $b) {
                                        if ($a['store_start_time_compare'] > $b['store_start_time_compare']) {
                                            return 1;
                                        } elseif ($a['store_start_time_compare'] < $b['store_start_time_compare']) {
                                            return -1;
                                        }
                                        return 0;
                                    });
                                    $final_comp_start_time =  strtotime($selected_start_time); 
                                    $possible_times = array_filter($single_timeslot_arr, function ($v) use ($final_comp_start_time) { 
                                        return $v['store_start_time_compare'] > $final_comp_start_time; 
                                    });
                                    /* Get before time here... */                                                                       
                                    if(empty($possible_times)){
                                        usort($single_timeslot_arr, function($a, $b) {
                                            if ($a['store_start_time_compare'] < $b['store_start_time_compare']) {
                                                return 1;
                                            } elseif ($a['store_start_time_compare'] > $b['store_start_time_compare']) {
                                                return -1;
                                            }
                                            return 0;
                                        });                                              
                                        $final_comp_start_time =  strtotime($selected_start_time);                                 
                                        $possible_times = array_filter($single_timeslot_arr, function ($v) use ($final_comp_start_time) { 
                                            return $v['store_start_time_compare'] < $final_comp_start_time; 
                                        });                                        
                                    }
                                    if(!empty($possible_times)){                                            
                                        $possible_times_keys = array_keys($possible_times);
                                        $first_key = (isset($possible_times_keys[0]))?$possible_times_keys[0]:'';                                        
                                        $new_suggested_time = (isset($possible_times[$first_key]))?$possible_times[$first_key]:'';                                        
                                        if(!empty($new_suggested_time)){
                                            $get_possible_date_and_times[$key]['selected_date'] = $new_suggested_time['store_service_date'];
                                            $get_possible_date_and_times[$key]['formated_end_time'] = $new_suggested_time['formatted_end_time'];
                                            $get_possible_date_and_times[$key]['formated_start_time'] = $new_suggested_time['formatted_start_time'];
                                            $get_possible_date_and_times[$key]['selected_start_time'] = $new_suggested_time['store_start_time'];
                                            $get_possible_date_and_times[$key]['selected_end_time'] = $new_suggested_time['store_end_time'];
                                        }                                                                                
                                    }
                                }                                
                            }else{
                                $get_possible_date_and_times[$key]['is_not_avaliable'] = 1;
                            }                            
                        }
                        /* Suggested Logic Over here */
                    }else{
                        $get_possible_date_and_times[$key]['is_not_avaliable'] = 1;
                    }                        
                    $get_recurring_timings[$key] = $get_front_timings;
                }
                /* Check Possible Date Is Avaliable Or Not Over */
                foreach($get_possible_date_and_times as $pkey=>$date_time){
                    $formated_start_time = date($default_time_format,strtotime($date_time['selected_start_time']));
                    $formated_end_time = date($default_time_format,strtotime($date_time['selected_end_time'])); 
                                                           
                    $formated_start_time = $get_possible_date_and_times[$pkey]['formated_start_time']; 
                    $formated_end_time =  $get_possible_date_and_times[$pkey]['formated_end_time'];

                    //$formated_start_time = date_i18n($default_time_format, strtotime($date_time['selected_start_time']));
                    //$formated_end_time   = date_i18n($default_time_format, strtotime($date_time['selected_end_time']));

                    $get_possible_date_and_times[$pkey]['formated_select_date'] = date($default_date_format,strtotime($date_time['selected_date']));

                    if($bookigpress_time_format_for_booking_form=='1' || $bookigpress_time_format_for_booking_form=='2') {
                        $get_possible_date_and_times[$pkey]['display_formated_date_and_time'] = $formated_start_time.' '.__('to', 'bookingpress-recurring-appointments').' '.$formated_end_time;
                    }
                    else if($bookigpress_time_format_for_booking_form=='5' || $bookigpress_time_format_for_booking_form=='6') {
                        $get_possible_date_and_times[$pkey]['display_formated_date_and_time'] = $formated_start_time.' '.'-'.' '.$formated_end_time;
                    }
                    else if($bookigpress_time_format_for_booking_form=='3' || $bookigpress_time_format_for_booking_form=='4') {
                        $get_possible_date_and_times[$pkey]['display_formated_date_and_time'] = $formated_start_time;
                    }
                    else if($bookigpress_time_format_for_booking_form == 'bookingpress-wp-inherit-time-format') {
                        $get_possible_date_and_times[$pkey]['display_formated_date_and_time'] = $formated_start_time.' '.'-'.' '.$formated_end_time;
                    }
                    else {
                        $get_possible_date_and_times[$pkey]['display_formated_date_and_time'] = $formated_start_time.' '.__('to', 'bookingpress-recurring-appointments').' '.$formated_end_time;
                    }
                }
            }
            if(!empty($get_recurring_timings)){
                set_transient( 'bpa_recurring_front_timings_'. $bookingpress_form_token, $get_recurring_timings, (800 * MINUTE_IN_SECONDS) );
            }            
			$response['variant']                = 'success';
			$response['title']                  = __( 'Success', 'bookingpress-recurring-appointments' );
			$response['msg']                    = __( 'Success', 'bookingpress-recurring-appointments' );
			$response['recurring_appointments'] = $get_possible_date_and_times;
            $response['bookingpress_recurring_form_token'] = $bookingpress_form_token;

			//echo wp_json_encode( $response );
            if($return_data){
                return $response;
            }            
            echo json_encode($response);
			exit();
        }

        /**
         * Function for add front css
         * set_front_css
         *
         * @return void
        */
        function set_front_css(){         
            global $BookingPress;
            wp_register_style( 'bookingpress_recurring_appointment_front_css', RECURRING_APPOINTMENTS_LIST_URL . '/css/bookingpress_recurring_appointments_front.css', array(), RECURRING_APPOINTMENTS_LIST_VERSION );

            wp_register_style('bookingpress_recurring_appointment_front_rtl_css', RECURRING_APPOINTMENTS_LIST_URL . '/css/bookingpress_recurring_appointments_front_rtl.css', array(), RECURRING_APPOINTMENTS_LIST_VERSION);

            if ( $BookingPress->bookingpress_is_front_page() ) {
                wp_enqueue_style( 'bookingpress_recurring_appointment_front_css' );
                if (is_rtl() ) {
                    wp_enqueue_style('bookingpress_recurring_appointment_front_rtl_css');
                }
            }
        }

        /**
         * Function for set css in admin side
         *
         * @return void
         */
        function set_admin_css(){
            global $bookingpress_slugs;
			wp_register_style( 'bookingpress_recurring_appointment_admin_css', RECURRING_APPOINTMENTS_LIST_URL . '/css/bookingpress_recurring_appointments_admin.css', array(), RECURRING_APPOINTMENTS_LIST_VERSION );
            wp_register_style('bookingpress_recurring_appointment_admin_rtl_css', RECURRING_APPOINTMENTS_LIST_URL . '/css/bookingpress_recurring_appointments_admin_rtl.css', array(), RECURRING_APPOINTMENTS_LIST_VERSION);

            if ( isset( $_REQUEST['page'] ) && in_array( sanitize_text_field( $_REQUEST['page'] ), (array) $bookingpress_slugs ) ) {
	    wp_enqueue_style( 'bookingpress_recurring_appointment_admin_css' );

                if (is_rtl() ) {
                    wp_enqueue_style('bookingpress_recurring_appointment_admin_rtl_css');
                }
			}
        }        

        /**
         * Function for set default recurring frequencies data
         *
         * @return void
         */
        function bookingpress_get_recurring_frequencies(){
            global $BookingPress;
            $all_recurring_frequencies_arr = array();
            $recurring_daily_label = $BookingPress->bookingpress_get_customize_settings('recurring_daily_label','booking_form');
            $recurring_weekly_label = $BookingPress->bookingpress_get_customize_settings('recurring_weekly_label','booking_form');
            $recurring_biweekly_label = $BookingPress->bookingpress_get_customize_settings('recurring_biweekly_label','booking_form');
            $recurring_monthly_label = $BookingPress->bookingpress_get_customize_settings('recurring_monthly_label','booking_form');
            
            $recurring_daily_label = (!empty($recurring_daily_label))?stripslashes_deep($recurring_daily_label):'';
            $recurring_weekly_label = (!empty($recurring_weekly_label))?stripslashes_deep($recurring_weekly_label):'';
            $recurring_biweekly_label = (!empty($recurring_biweekly_label))?stripslashes_deep($recurring_biweekly_label):'';
            $recurring_monthly_label = (!empty($recurring_monthly_label))?stripslashes_deep($recurring_monthly_label):'';

            $all_recurring_frequencies_arr[] = array('text'=>$recurring_daily_label,'value'=>'daily');
            $all_recurring_frequencies_arr[] = array('text'=>$recurring_weekly_label,'value'=>'weekly');
            $all_recurring_frequencies_arr[] = array('text'=>$recurring_biweekly_label,'value'=>'biweekly');
            $all_recurring_frequencies_arr[] = array('text'=>$recurring_monthly_label,'value'=>'monthly');
            return $all_recurring_frequencies_arr;
        }

        
        /**
         * Function for check addon active or not
         *
         * @return void
         */
        public function is_addon_activated(){
            $bookingpress_c_d_version = get_option('recurring_appointments_list_version');
            return !empty($bookingpress_c_d_version) ? 1 : 0;
        }        

        public function is_recurring_in_service($service_id){
            global $bookingpress_services;            
            $recurring_frequencies = $bookingpress_services->bookingpress_get_service_meta($service_id, 'recurring_frequencies');
            $enable_recurring_appointments = $bookingpress_services->bookingpress_get_service_meta($service_id, 'enable_recurring_appointments');
        }

         /* Front Side Implement Start */
        /**
         * Function for add feaquncy data
         *
         * @param  mixed $bookingpress_dynamic_next_page_request_filter
         * @return void
         */
        function bookingpress_dynamic_next_page_request_filter_func($bookingpress_dynamic_next_page_request_filter){

            $bookingpress_dynamic_next_page_request_filter .= 'const vm8 = this;';            
            $bookingpress_dynamic_next_page_request_filter .= '                              
            var service_next_tab = "";
            if( typeof vm8.bookingpress_sidebar_step_data["service"].next_tab_name != "undefined"){
                service_next_tab = vm8.bookingpress_sidebar_step_data["service"].next_tab_name;                
            }            
            if(vm8.bookingpress_current_tab == service_next_tab && service_next_tab == next_tab){
                if(vm8.is_cart_addon_active == 1){                    
                    if(vm8.appointment_step_form_data.is_recurring_appointments == true || vm8.appointment_step_form_data.is_recurring_appointments == "true"){
                        vm8.bookingpress_sidebar_step_data.cart.is_display_step = 0;
                        let cart_next_tab = vm8.bookingpress_sidebar_step_data.cart.next_tab_name;
                        let cart_prev_tab = vm8.bookingpress_sidebar_step_data.cart.previous_tab_name;                        
                        if(vm8.recurring_cart_next_tab == ""){
                            vm8.recurring_cart_next_tab = cart_next_tab;                            
                        }
                        if(vm8.recurring_cart_prev_tab == ""){
                            vm8.recurring_cart_prev_tab = cart_prev_tab;                            
                        }
                        vm8.bookingpress_sidebar_step_data[ cart_prev_tab ].next_tab_name = cart_next_tab;
                        vm8.bookingpress_sidebar_step_data[ cart_next_tab ].previous_tab_name = cart_prev_tab;
                        vm8.bookingpress_sidebar_step_data[ cart_prev_tab ].cart_next_tab = true;
                        vm8.bookingpress_sidebar_step_data[ cart_next_tab ].cart_prev_tab = true;
                    }else{

                        if(vm8.recurring_cart_next_tab == ""){
                            let cart_next_tab = vm8.bookingpress_sidebar_step_data.cart.next_tab_name;                                                          
                            vm8.recurring_cart_next_tab = cart_next_tab;                            
                        }
                        if(vm8.recurring_cart_prev_tab == ""){
                            let cart_prev_tab = vm8.bookingpress_sidebar_step_data.cart.previous_tab_name;
                            vm8.recurring_cart_prev_tab = cart_prev_tab;                            
                        }                        
                        vm8.bookingpress_sidebar_step_data.cart.next_tab_name = vm8.recurring_cart_next_tab;
                        let recurring_cart_prev_tab_org = vm8.recurring_cart_prev_tab;
                        vm8.bookingpress_sidebar_step_data[recurring_cart_prev_tab_org].next_tab_name = "cart";
                        vm8.bookingpress_sidebar_step_data[vm8.recurring_cart_next_tab].previous_tab_name = "cart";
                        vm8.bookingpress_sidebar_step_data.cart.is_display_step = 1;
                    }
                }
            }            
            if(vm8.bookingpress_current_tab == "basic_details" && "basic_details" == next_tab){
                
            }
            if(vm8.bookingpress_current_tab == "datetime" && "datetime" == next_tab){
                    let current_selected_service_for_recurring_tab = vm8.appointment_step_form_data.selected_service;
                    if(current_selected_service_for_recurring_tab != "" &&  "undefined" != vm8.bookingpress_all_services_data[ current_selected_service_for_recurring_tab ] && "undefined" != vm8.bookingpress_all_services_data[ current_selected_service_for_recurring_tab ].enable_recurring_appointments && false != vm8.bookingpress_all_services_data[ current_selected_service_for_recurring_tab ].enable_recurring_appointments ){
                        vm8.bookingpress_recurring_frequencies = vm8.bookingpress_all_services_data[current_selected_service_for_recurring_tab].recurring_frequencies;
                    }
                }
            ';
            return $bookingpress_dynamic_next_page_request_filter;

        }

		/**
		 * Open drawer for service extras after selecting service ( after new service array changes );
		 *
		 * @param  mixed $bookingpress_before_selecting_booking_service_data
		 * @return void
		 */
		function bookingpress_before_selecing_booking_service_for_recurring( $bookingpress_before_selecting_booking_service_data ){
			$bookingpress_before_selecting_booking_service_data .= '
				let current_selected_service_for_recurring = vm.appointment_step_form_data.selected_service;
				if( "undefined" != vm.bookingpress_all_services_data[ selected_service_id ].enable_recurring_appointments && false != vm.bookingpress_all_services_data[ selected_service_id ].enable_recurring_appointments ){
					let bookingpress_has_cart_item = false;
                    if(vm.is_cart_addon_active == 1){
                        if(vm.appointment_step_form_data.cart_items.length > 0){
                            bookingpress_has_cart_item = true;     
                        }
                    }                    
                    if(bookingpress_has_cart_item == false){
                        vm.appointment_step_form_data.recurring_form_data.no_of_session = 0;
                        vm.appointment_step_form_data.is_recurring_appointments = false;
                        vm.appointment_step_form_data.recurring_form_data.start_date = "";
                        vm.bookingpress_open_extras_drawer = "true";
                        vm.isServiceLoadTimeLoader = "0";                                                
                        vm.appointment_step_form_data.recurring_appointments_max_no_of_times = parseInt(vm.bookingpress_all_services_data[ selected_service_id ].recurring_appointments_max_no_of_times);
                        if(vm.appointment_step_form_data.recurring_appointments_max_no_of_times > 0){
                            vm.appointment_step_form_data.is_service_recurring_appointments_enable = "1";
                        }
                        is_drawer_opened = "true";
                        is_move_to_next = false;
                    }else{
                        vm.appointment_step_form_data.is_service_recurring_appointments_enable = "0";
                    }
				} else {
					vm.appointment_step_form_data.is_service_recurring_appointments_enable = "0";
                    vm.appointment_step_form_data.is_recurring_appointments = false;
                    vm.appointment_step_form_data.recurring_form_data.no_of_session = 0;
                    vm.appointment_step_form_data.is_recurring_appointments = false;
                    vm.appointment_step_form_data.recurring_form_data.start_date = "";
				}
			';
			return $bookingpress_before_selecting_booking_service_data;
		}        

       
        /**
         * Function for add front side recurring tab
         *
         * @param  mixed $bookingpress_front_vue_data_fields
         * @return void
         */
        function bookingpress_modify_front_booking_form_data_vars_func($bookingpress_front_vue_data_fields){

            global $BookingPress,$is_cart_addon_active,$bookingpress_services,$all_recurring_frequencies,$bookingpress_global_options;
            $bookingpress_global_details     = $bookingpress_global_options->bookingpress_global_options();

            $all_recurring_frequencies = $this->bookingpress_get_recurring_frequencies();
                        
            $bookingpress_front_vue_data_fields['is_recurring_appointment_addon_active'] = $this->is_addon_activated();
            //$bookingpress_front_vue_data_fields['is_recurring_appointment_addon_active'] = 1;
            $bookingpress_all_service_data = $bookingpress_front_vue_data_fields['all_services_data'];
            if(!empty($bookingpress_front_vue_data_fields['bookingpress_all_services_data'])){
                foreach( $bookingpress_front_vue_data_fields['bookingpress_all_services_data'] as $key => $value ) {                  
                   $bookingpress_service_id = !empty($value['bookingpress_service_id']) ?  intval($value['bookingpress_service_id']) : 0;                   
                   $recurring_frequencies = $bookingpress_services->bookingpress_get_service_meta($bookingpress_service_id, 'recurring_frequencies');
                   $enable_recurring_appointments = $bookingpress_services->bookingpress_get_service_meta($bookingpress_service_id, 'enable_recurring_appointments');                                                              
                   $default_recurring_frequencies = $bookingpress_services->bookingpress_get_service_meta($bookingpress_service_id, 'default_recurring_frequencies');                   
                   $recurring_appointments_max_no_of_times = $bookingpress_services->bookingpress_get_service_meta($bookingpress_service_id, 'recurring_appointments_max_no_of_times'); 

                   $has_custom_service_duration = false;
                   if(isset($value['enable_custom_service_duration'])){
                        $enable_custom_service_duration = $value['enable_custom_service_duration'];
                        if(!empty($enable_custom_service_duration) && $enable_custom_service_duration == 'true'){
                            $has_custom_service_duration = true;
                        }
                   } 
                   if(empty($recurring_frequencies) || $value['bookingpress_service_duration_unit'] == 'd' || $has_custom_service_duration || $recurring_appointments_max_no_of_times == 0){
                       $enable_recurring_appointments = 0;
                   }
                   if(!empty($recurring_frequencies)){                                               
                       $recurring_frequencies = explode(",",$recurring_frequencies);                       
                   }                                                                    
                   $bookingpress_front_vue_data_fields['bookingpress_all_services_data'][$key]['enable_recurring_appointments'] = $enable_recurring_appointments;
                   $bookingpress_front_vue_data_fields['bookingpress_all_services_data'][$key]['recurring_frequencies'] = $recurring_frequencies;
                   $bookingpress_front_vue_data_fields['bookingpress_all_services_data'][$key]['default_recurring_frequencies'] = $default_recurring_frequencies;                   
                   $bookingpress_front_vue_data_fields['bookingpress_all_services_data'][$key]['recurring_appointments_max_no_of_times'] = $recurring_appointments_max_no_of_times;

               } 
           }                                  
           $bookingpress_front_vue_data_fields['appointment_step_form_data']['is_service_recurring_appointments_enable'] = "0";  
           $bookingpress_front_vue_data_fields['appointment_step_form_data']['is_recurring_appointments'] = false;
           $bookingpress_front_vue_data_fields['appointment_step_form_data']['recurring_appointments_max_no_of_times'] = 0;

           $bookingpress_front_vue_data_fields['appointment_step_form_data']['recurring_appointments'] = array();
           $bookingpress_front_vue_data_fields['appointment_step_form_data']['bookingpress_recurring_form_token'] = '';
           $bookingpress_front_vue_data_fields['appointment_step_form_data']['bookingpress_single_deposit_price'] = 0;
           $bookingpress_front_vue_data_fields['appointment_step_form_data']['bookingpress_single_deposit_due_amount'] = 0;
           
           $bookingpress_front_vue_data_fields['is_cart_addon_active'] = $is_cart_addon_active;

           $recurring_appointment_checkbox = $BookingPress->bookingpress_get_customize_settings('recurring_appointment_checkbox','booking_form');
           $recurring_appointment_session_title = $BookingPress->bookingpress_get_customize_settings('recurring_appointment_session_title','booking_form');
           $recurring_appointment_start_date_title = $BookingPress->bookingpress_get_customize_settings('recurring_appointment_start_date_title','booking_form');
           $recurring_appointment_time_title = $BookingPress->bookingpress_get_customize_settings('recurring_appointment_time_title','booking_form');
           $recurring_appointment_no_of_session_title = $BookingPress->bookingpress_get_customize_settings('recurring_appointment_no_of_session_title','booking_form');
           $recurring_appointment_frequency_title = $BookingPress->bookingpress_get_customize_settings('recurring_appointment_frequency_title','booking_form');
           $recurring_appointment_apply_btn_title = $BookingPress->bookingpress_get_customize_settings('recurring_appointment_apply_btn_title','booking_form');
           $recurring_appointment_upcoming_appointment_title = $BookingPress->bookingpress_get_customize_settings('recurring_appointment_upcoming_appointment_title','booking_form');
           
           /* Validation Message for get recurring appointment */
           $recurring_start_date_validation_message = $BookingPress->bookingpress_get_settings('recurring_start_date_validation_message','message_setting');
           $recurring_timeslot_validation_message = $BookingPress->bookingpress_get_settings('recurring_timeslot_validation_message','message_setting');
           $recurring_no_of_session_validation_message = $BookingPress->bookingpress_get_settings('recurring_no_of_session_validation_message','message_setting');
           $recurring_frequency_validation_message = $BookingPress->bookingpress_get_settings('recurring_frequency_validation_message','message_setting');
           $recurring_appointment_add_validation_message = $BookingPress->bookingpress_get_settings('recurring_appointment_add_validation_message','message_setting');
           $recurring_not_avaliable_appointment_validation_message = $BookingPress->bookingpress_get_settings('recurring_not_avaliable_appointment_validation_message','message_setting');

           /* Translate both new messages.... */
           $recurring_suggested_message = $BookingPress->bookingpress_get_settings('recurring_suggested_message','message_setting');
           $recurring_not_avaliable_message = $BookingPress->bookingpress_get_settings('recurring_not_avaliable_message','message_setting');

           $recurring_edit_appointment_title = $BookingPress->bookingpress_get_customize_settings('recurring_edit_appointment_title','booking_form');
           $recurring_edit_appointment_date_label = $BookingPress->bookingpress_get_customize_settings('recurring_edit_appointment_date_label','booking_form');
           $recurring_more_datetime_label = $BookingPress->bookingpress_get_customize_settings('recurring_more_datetime_label','booking_form');
           
           $bookingpress_front_vue_data_fields['recurring_appointments_repeat_title'] = !empty($recurring_appointment_checkbox) ? stripslashes_deep($recurring_appointment_checkbox) : '';
           $bookingpress_front_vue_data_fields['recurring_start_date_label'] = !empty($recurring_appointment_start_date_title) ? stripslashes_deep($recurring_appointment_start_date_title) : '';  
           $bookingpress_front_vue_data_fields['recurring_no_of_session_label'] = !empty($recurring_appointment_no_of_session_title) ? stripslashes_deep($recurring_appointment_no_of_session_title) : '';

           $bookingpress_front_vue_data_fields['recurring_frequency_label'] = !empty($recurring_appointment_frequency_title) ? stripslashes_deep($recurring_appointment_frequency_title) : '';
           $bookingpress_front_vue_data_fields['recurring_apply_button_label'] = !empty($recurring_appointment_apply_btn_title) ? stripslashes_deep($recurring_appointment_apply_btn_title) : '';
           $bookingpress_front_vue_data_fields['recurring_upcoming_appointments_label'] = !empty($recurring_appointment_upcoming_appointment_title) ? stripslashes_deep($recurring_appointment_upcoming_appointment_title) : '';
           $bookingpress_front_vue_data_fields['recurring_start_time_label'] = !empty($recurring_appointment_time_title) ? stripslashes_deep($recurring_appointment_time_title) : '';

           $bookingpress_front_vue_data_fields['recurring_suggested_message'] = !empty($recurring_suggested_message) ? stripslashes_deep($recurring_suggested_message) : '';
           $bookingpress_front_vue_data_fields['recurring_not_avaliable_message'] = !empty($recurring_not_avaliable_message) ? stripslashes_deep($recurring_not_avaliable_message) : '';
           $bookingpress_front_vue_data_fields['recurring_edit_appointment_title'] = !empty($recurring_edit_appointment_title) ? stripslashes_deep($recurring_edit_appointment_title) : '';
           $bookingpress_front_vue_data_fields['recurring_edit_appointment_date_label'] = !empty($recurring_edit_appointment_date_label) ? stripslashes_deep($recurring_edit_appointment_date_label) : '';

           $bookingpress_front_vue_data_fields['recurring_more_datetime_label'] = !empty($recurring_more_datetime_label) ? stripslashes_deep($recurring_more_datetime_label) : '';
           
           $bookingpress_front_vue_data_fields['recurring_start_date_validation_message'] = !empty($recurring_start_date_validation_message) ? stripslashes_deep($recurring_start_date_validation_message) : '';
           $bookingpress_front_vue_data_fields['recurring_timeslot_validation_message'] = !empty($recurring_timeslot_validation_message) ? stripslashes_deep($recurring_timeslot_validation_message): '';
           $bookingpress_front_vue_data_fields['recurring_no_of_session_validation_message'] = !empty($recurring_no_of_session_validation_message) ? stripslashes_deep($recurring_no_of_session_validation_message) : '';
           $bookingpress_front_vue_data_fields['recurring_frequency_validation_message'] = !empty($recurring_frequency_validation_message) ? stripslashes_deep($recurring_frequency_validation_message) : '';
           $bookingpress_front_vue_data_fields['recurring_appointment_add_validation_message'] = !empty($recurring_appointment_add_validation_message) ? stripslashes_deep($recurring_appointment_add_validation_message): '';
           $bookingpress_front_vue_data_fields['recurring_not_avaliable_appointment_validation_message'] = !empty($recurring_not_avaliable_appointment_validation_message) ? stripslashes_deep($recurring_not_avaliable_appointment_validation_message) : '';
           $bookingpress_front_vue_data_fields['recurring_appointment_loader'] = false;

           $bookingpress_front_vue_data_fields['recurring_appointment_device'] = "";

           $bookingpress_front_vue_data_fields['recurring_max_available_date'] = "";
           $bookingpress_front_vue_data_fields['appointment_step_form_data']['recurring_form_data'] = array(
                'start_date' => '',
                'start_time' => '',
                'end_time' => '',
                'formatted_start_time' => '',
                'formatted_end_time' => '',
                'recurring_frequency' => '',
                'no_of_session' => 0,
           );

           $bookingpress_front_vue_data_fields['appointment_step_form_data']['recurring_edit_index'] = '';           
           $bookingpress_front_vue_data_fields['appointment_step_form_data']['bookingpress_recurring_total'] = 0;
           $bookingpress_front_vue_data_fields['appointment_step_form_data']['bookingpress_recurring_total_with_currency'] = '';
           $bookingpress_front_vue_data_fields['appointment_step_form_data']['bookingpress_recurring_original_total'] = 0;
           $bookingpress_front_vue_data_fields['appointment_step_form_data']['bookingpress_recurring_original_total_with_currency'] = '';

           $max_no_of_recurring = $this->max_no_of_recurring;           
           $bookingpress_no_of_recurring = array();
           for($i=2;$i<=$max_no_of_recurring;$i++){
                $label = str_pad($i,2,0,STR_PAD_LEFT);                                
                $bookingpress_no_of_recurring[] = array('label' => $label,'value' => $i);
           }
           $bookingpress_front_vue_data_fields['bookingpress_no_of_recurring'] = $bookingpress_no_of_recurring;

            for($i=0;$i<=$max_no_of_recurring;$i++){
                $label = $i.' '.stripslashes_deep($recurring_appointment_session_title);  
                if($i != 1){
                    $bookingpress_no_of_recurring_first_label[] = array('label' => $label,'value' => $i);
                }                                              
            }
            $bookingpress_front_vue_data_fields['bookingpress_no_of_recurring_first_label'] = $bookingpress_no_of_recurring_first_label;

           $bookingpress_front_vue_data_fields['bookingpress_recurring_frequencies'] = array();           
           $bookingpress_front_vue_data_fields['all_recurring_frequencies'] = $all_recurring_frequencies;

           
           $bookingpress_front_vue_data_fields['recurring_pickerOptions'] = array('firstDayOfWeek'=>intval(esc_html($bookingpress_global_details['start_of_week'])));

           $bookingpress_front_vue_data_fields['recurring_disable_dates'] = array();           
           $bookingpress_front_vue_data_fields['recurring_appointment_time_slot'] = array();

           /* Edit Recurring Appointment Here.... */
           $bookingpress_front_vue_data_fields['recurring_open_edit_popup'] = false;           
           $bookingpress_front_vue_data_fields['recurring_edit_date'] = '';
           $bookingpress_front_vue_data_fields['recurring_edit_time'] = '';
           $bookingpress_front_vue_data_fields['recurring_edit_pickerOptions'] = array('firstDayOfWeek'=>intval(esc_html($bookingpress_global_details['start_of_week'])));
           $bookingpress_front_vue_data_fields['recurring_edit_disable_dates'] = array();           
           $bookingpress_front_vue_data_fields['recurring_edit_appointment_time_slot'] = array();
           
           $bookingpress_front_vue_data_fields['recurring_cart_next_tab'] = "";
           $bookingpress_front_vue_data_fields['recurring_cart_prev_tab'] = "";


           /* Customize get timeslot grouping */
           $bookingpress_morning_text = $BookingPress->bookingpress_get_customize_settings('morning_text', 'booking_form');
            if (empty($bookingpress_morning_text) ) {
                $bookingpress_morning_text = esc_html__('Morning', 'bookingpress-recurring-appointments');
            }

            $bookingpress_afternoon_text = $BookingPress->bookingpress_get_customize_settings('afternoon_text', 'booking_form');
            if (empty($bookingpress_afternoon_text) ) {
                $bookingpress_afternoon_text = esc_html__('Afternoon', 'bookingpress-recurring-appointments');
            }

            $bookingpress_evening_text = $BookingPress->bookingpress_get_customize_settings('evening_text', 'booking_form');
            if (empty($bookingpress_evening_text) ) {
                $bookingpress_evening_text = esc_html__('Evening', 'bookingpress-recurring-appointments');
            }

            $bookingpress_night_text = $BookingPress->bookingpress_get_customize_settings('night_text', 'booking_form');
            if (empty($bookingpress_night_text) ) {
                $bookingpress_night_text = esc_html__('Night', 'bookingpress-recurring-appointments');
            }   
            $bookingpress_morning_text =stripslashes_deep($bookingpress_morning_text);
            $bookingpress_afternoon_text =stripslashes_deep($bookingpress_afternoon_text);
            $bookingpress_evening_text =stripslashes_deep($bookingpress_evening_text);
            $bookingpress_night_text =stripslashes_deep($bookingpress_night_text);
            $bookingpress_front_vue_data_fields['morning_text'] = $bookingpress_morning_text;
            $bookingpress_front_vue_data_fields['afternoon_text'] = $bookingpress_afternoon_text;
            $bookingpress_front_vue_data_fields['evening_text'] = $bookingpress_evening_text;
            $bookingpress_front_vue_data_fields['night_text'] = $bookingpress_night_text;


            $bookingpress_front_vue_data_fields['recurring_appointment_loader_edit'] = false;
            $bookingpress_front_vue_data_fields['recurring_appointment_time_loader'] = false;
            $bookingpress_front_vue_data_fields['recurring_appointment_time_loader_edit'] = false;

            $bookingpress_front_vue_data_fields['recurring_appointment_start_counter'] = 0;

            /* Customize get timeslot grouping */

            return $bookingpress_front_vue_data_fields;
        }        
                

        /**
         * Function for add service extra drawer repeat option
         *
         * @return void
         */
        function bookingpress_add_service_extra_drawer_func(){
        ?>       
        <div class="bpa-sao__module-row --bpa-sao-guest-module" v-if="is_recurring_appointment_addon_active == '1' && appointment_step_form_data.is_service_recurring_appointments_enable != '0'">
            <div class="bpa-front-sec--sub-heading">{{recurring_appointments_repeat_title}}</div>
            <el-select @change="bookingpress_select_repeat_recurring_appointment()" v-model="appointment_step_form_data.recurring_form_data.no_of_session" popper-class="bpa-fm--service__advance-options-popper bpa-fm--service__advance-rec-no-popover" class="bpa-front-form-control">
                <el-option v-for="(nper, keys) in bookingpress_no_of_recurring_first_label" v-if="nper.value <= appointment_step_form_data.recurring_appointments_max_no_of_times" :label="(nper.value == 0)?recurring_no_of_session_label:nper.label" :value="nper.value"></el-option>
            </el-select>
        </div>           
        <?php 
        }

        
        /**
         * Function for add recurring tab in mobile view
         *
         * @return void
         */
        function bookingpress_add_dateandtime_detail_section_before_front_side_mobile_view_func(){
        ?>
        <div class="bpa-reacurring-date-time-mobile-step">
            <?php 
                do_action('bookingpress_add_dateandtime_detail_section_before_front_side','mobile');    
            ?>
        </div>
        <?php    
        }
                
        /**
         * Function for add recurring tab in desktop
         *
         * @return void
         */
        function bookingpress_add_recurring_appointment_front_side_func($device_view=''){
            global $bookingpress_global_options,$BookingPress, $bookingpress_common_date_format;
            $device_class = (!empty($device_view))?'bpa-dialog--add-recurring-edit-mobile':'bpa-dialog--add-recurring-edit-desktop';
        ?>
        <div v-if="typeof appointment_step_form_data.is_recurring_appointments !== 'undefined' && (appointment_step_form_data.is_recurring_appointments == true || appointment_step_form_data.is_recurring_appointments == 'true')" class="bpa-front--dt__wrapper">        
            <div class="bpa-front--dt__col bpa-front-dt__timeslot-col" :class="(isLoadDateTimeCalendarLoad == 0 ) ? 'bpa-front-dt-col__is-visible' : ''">    
                <div class="bpa-recurring-appointment-head">
                    <el-row type="flex" :gutter="16" class="bpa-front-recurring--filter-wrapper">
                        <el-col class="bpa-recurring-head-col bpa-recurring-m-right-m" :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
                            <el-row type="flex" :gutter="12">
                                <el-col class="bpa-recurring-head-col bpa-recurring-m-right" :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
                                    <label v-html="recurring_start_date_label"></label>
                                    <div class="field bpa-recurring-appointment-head-row">
                                        <el-date-picker @change="change_recurring_start_date($event,'add')" class="bpa-front-form-control bpa-front-form-control--date-picker" type="date" format="<?php echo esc_html($bookingpress_common_date_format); ?>" placeholder="<?php echo esc_html($bookingpress_common_date_format); ?>" v-model="appointment_step_form_data.recurring_form_data.start_date" name="appointment_booked_date" popper-class="bpa-custom-datepicker bpa-custom-recurring-datepicker" type="date" :clearable="false" :picker-options="recurring_pickerOptions" value-format="yyyy-MM-dd"></el-date-picker>
                                    </div>
                                </el-col>
                                <el-col class="bpa-recurring-head-col bpa-recurring-m-right" :xs="12" :sm="12" :md="12" :lg="12" :xl="12">                                                                        
                                    <label v-html="recurring_start_time_label"></label>
                                    <div class="field bpa-recurring-appointment-head-row">
                                        <el-select :disabled="recurring_appointment_time_loader" class="bpa-front-form-control" popper-class="bpa-fm--service__advance-options-popper bpa-fm--service__advance-options-recurring-popper" Placeholder="<?php esc_html_e( 'Select Time', 'bookingpress-recurring-appointments' ); ?>" v-model="appointment_step_form_data.recurring_form_data.start_time" filterable popper-class="bpa-el-select--is-with-modal" @Change="bookingpress_set_recurring_start_time($event,recurring_appointment_time_slot)">
                                            <el-option-group :class="hide_time_slot_grouping == true ? 'bpa-do-not-group-timing' : ''" v-for="appointment_time_slot_data in recurring_appointment_time_slot" :key="appointment_time_slot_data.timeslot_label" :label="appointment_time_slot_data.timeslot_label" >
                                                <el-option v-for="appointment_time in appointment_time_slot_data.timeslots" :label="bookingpress_set_recurring_appointment_timeslot_formate(appointment_time)" :value="appointment_time.store_start_time" :disabled="( appointment_time.is_disabled || appointment_time.max_capacity == 0 || appointment_time.is_booked == 1 )">
                                                    <span>{{ bookingpress_set_recurring_appointment_timeslot_formate(appointment_time) }}</span>
                                                </el-option>	
                                            </el-option-group>
                                        </el-select>
                                    </div>
                                </el-col>
                            </el-row>
                        </el-col>
                        <el-col class="bpa-recurring-head-col bpa-recurring-head-col-other-filter" :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
                            <el-row type="flex" :gutter="12">
                                <el-col class="bpa-recurring-head-col bpa-recurring-m-right" :xs="12" :sm="9" :md="9" :lg="9" :xl="9">
                                    <label v-html="recurring_no_of_session_label"></label>
                                    <div class="field bpa-recurring-appointment-head-row">
                                        <el-select v-model="appointment_step_form_data.recurring_form_data.no_of_session" popper-class="bpa-fm--service__advance-options-popper" class="bpa-front-form-control">
                                            <el-option v-for="(nper, keys) in bookingpress_no_of_recurring" v-if="nper.value <= appointment_step_form_data.recurring_appointments_max_no_of_times" :label="nper.label" :value="nper.value">
                                                <span>{{nper.label}}</span>
                                            </el-option>
                                        </el-select>
                                    </div>                        
                                </el-col>
                                <el-col class="bpa-recurring-head-col" :xs="12" :sm="9" :md="9" :lg="9" :xl="9">
                                    <label v-html="recurring_frequency_label"></label>
                                    <div class="field bpa-recurring-appointment-head-row">
                                        <el-select v-model="appointment_step_form_data.recurring_form_data.recurring_frequency" popper-class="bpa-fm--service__advance-options-popper" class="bpa-front-form-control">
                                            <el-option v-for="(recval, reckey) in all_recurring_frequencies" v-if="bookingpress_recurring_frequencies.includes(recval.value)" :label="recval.text" :value="recval.value">
                                                <span>{{recval.text}}</span>
                                            </el-option>
                                        </el-select>
                                    </div>                         
                                </el-col>
                                <el-col class="bpa-recurring-head-col bpa-recurring-m-left" :xs="12" :sm="6" :md="6" :lg="6" :xl="6">
                                    <label> &nbsp; </label>
                                    <div class="field bpa-recurring-appointment-head-row">
                                        <el-button class="bpa-front-btn bpa-front-btn__medium bpa-front-btn--full-width bpa-front-btn--primary" @click="bookingpress_recurring_appointment_get()" v-if="1 == 1">	                            
                                            {{recurring_apply_button_label}}
                                        </el-button>  
                                    </div>
                                </el-col>
                            </el-row>
                        </el-col>                                                
                    </el-row>
                </div>
                <div class="bpa-recurring-appointment-body">
                    <div v-if="recurring_appointment_loader == true" class="bpa-recurring-appointment-loader">
                        <div class="bpa-front-loader-container">
                            <div class="bpa-front-loader">
                                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid meet" width="256" height="256" viewBox="0 0 256 256" style="width:100%;height:100%">
                                    <defs>
                                        <animate repeatCount="indefinite" dur="2.2166667s" begin="0s" xlink:href="#_R_G_L_1_C_0_P_0" fill="freeze" attributeName="d" attributeType="XML" from="M294.33 386.7 C294.33,386.7 291.96,386.7 291.96,386.7 C291.96,386.7 291.67,391.89 291.67,391.89 C291.67,391.89 292.41,396.34 292.41,396.34 C292.41,396.34 292.11,401.09 292.11,401.09 C292.11,401.09 295.67,401.09 295.67,401.09 C295.67,401.09 295.82,396.05 295.82,396.05 C295.82,396.05 295.97,391.75 295.97,391.75 C295.97,391.75 294.33,386.7 294.33,386.7z " to="M330.93 386.68 C330.93,386.68 263.6,386.68 263.6,386.68 C263.6,386.68 265.82,453.13 265.82,453.13 C265.82,453.13 295.78,456.98 295.78,456.98 C295.78,456.98 295.89,452.83 295.89,452.83 C295.89,452.83 296.26,452.98 296.26,452.98 C296.26,452.98 295.78,457.13 295.78,457.13 C295.78,457.13 329.59,454.91 329.59,454.91 C329.59,454.91 330.93,386.68 330.93,386.68z " keyTimes="0;0.5037594;0.5263158;0.5789474;0.6691729;0.6992481;0.7593985;0.7669173;1" values="M294.33 386.7 C294.33,386.7 291.96,386.7 291.96,386.7 C291.96,386.7 291.67,391.89 291.67,391.89 C291.67,391.89 292.41,396.34 292.41,396.34 C292.41,396.34 292.11,401.09 292.11,401.09 C292.11,401.09 295.67,401.09 295.67,401.09 C295.67,401.09 295.82,396.05 295.82,396.05 C295.82,396.05 295.97,391.75 295.97,391.75 C295.97,391.75 294.33,386.7 294.33,386.7z ;M294.33 386.7 C294.33,386.7 291.96,386.7 291.96,386.7 C291.96,386.7 291.67,391.89 291.67,391.89 C291.67,391.89 292.41,396.34 292.41,396.34 C292.41,396.34 292.11,401.09 292.11,401.09 C292.11,401.09 295.67,401.09 295.67,401.09 C295.67,401.09 295.82,396.05 295.82,396.05 C295.82,396.05 295.97,391.75 295.97,391.75 C295.97,391.75 294.33,386.7 294.33,386.7z ;M303.49 386.7 C303.49,386.7 284.88,386.7 284.88,386.7 C284.88,386.7 284.88,402.72 284.88,402.72 C284.88,402.72 293.41,402.87 293.41,402.87 C293.41,402.87 293.07,405.24 293.07,405.24 C293.07,405.24 296.63,405.24 296.63,405.24 C296.63,405.24 296.82,402.57 296.82,402.57 C296.82,402.57 304.49,401.98 304.49,401.98 C304.49,401.98 303.49,386.7 303.49,386.7z ;M330.97 386.7 C330.97,386.7 263.64,386.7 263.64,386.7 C263.64,386.7 265.56,398.12 265.56,398.12 C265.56,398.12 266.75,407.02 266.75,407.02 C266.75,407.02 294.78,405.83 294.78,405.83 C294.78,405.83 298.34,405.83 298.34,405.83 C298.34,405.83 332.75,406.72 332.75,406.72 C332.75,406.72 332.45,399.46 332.45,399.46 C332.45,399.46 330.97,386.7 330.97,386.7z ;M330.97 386.7 C330.97,386.7 263.64,386.7 263.64,386.7 C263.64,386.7 265.56,442.32 265.56,442.32 C265.56,442.32 266.75,448.4 266.75,448.4 C266.75,448.4 283.8,447.51 283.8,447.51 C283.8,447.51 312.06,447.21 312.06,447.21 C312.06,447.21 332.75,448.1 332.75,448.1 C332.75,448.1 332.45,443.65 332.45,443.65 C332.45,443.65 330.97,386.7 330.97,386.7z ;M330.97 386.7 C330.97,386.7 263.64,386.7 263.64,386.7 C263.64,386.7 265.86,453.14 265.86,453.14 C265.86,453.14 276.98,456.11 276.98,456.11 C276.98,456.11 277.28,447.51 277.28,447.51 C277.28,447.51 319.47,447.81 319.47,447.81 C319.47,447.81 318.81,456.11 318.81,456.11 C318.81,456.11 329.63,454.92 329.63,454.92 C329.63,454.92 330.97,386.7 330.97,386.7z ;M330.93 386.68 C330.93,386.68 263.6,386.68 263.6,386.68 C263.6,386.68 265.82,453.13 265.82,453.13 C265.82,453.13 295.78,456.98 295.78,456.98 C295.78,456.98 295.63,448.83 295.63,448.83 C295.63,448.83 295.71,448.75 295.71,448.75 C295.71,448.75 295.78,457.13 295.78,457.13 C295.78,457.13 329.59,454.91 329.59,454.91 C329.59,454.91 330.93,386.68 330.93,386.68z ;M330.93 386.68 C330.93,386.68 263.6,386.68 263.6,386.68 C263.6,386.68 265.82,453.13 265.82,453.13 C265.82,453.13 295.78,456.98 295.78,456.98 C295.78,456.98 295.89,452.83 295.89,452.83 C295.89,452.83 296.26,452.98 296.26,452.98 C296.26,452.98 295.78,457.13 295.78,457.13 C295.78,457.13 329.59,454.91 329.59,454.91 C329.59,454.91 330.93,386.68 330.93,386.68z ;M330.93 386.68 C330.93,386.68 263.6,386.68 263.6,386.68 C263.6,386.68 265.82,453.13 265.82,453.13 C265.82,453.13 295.78,456.98 295.78,456.98 C295.78,456.98 295.89,452.83 295.89,452.83 C295.89,452.83 296.26,452.98 296.26,452.98 C296.26,452.98 295.78,457.13 295.78,457.13 C295.78,457.13 329.59,454.91 329.59,454.91 C329.59,454.91 330.93,386.68 330.93,386.68z " keySplines="0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0 0 0 0" calcMode="spline"/>
                                        <clipPath id="_R_G_L_1_C_0">
                                            <path id="_R_G_L_1_C_0_P_0" fill-rule="nonzero"/>
                                        </clipPath>
                                        <animate repeatCount="indefinite" dur="2.2166667s" begin="0s" xlink:href="#_R_G_L_0_C_0_P_0" fill="freeze" attributeName="d" attributeType="XML" from="M306.79 419.97 C306.79,419.97 306.83,419.98 306.83,419.98 C306.83,419.98 306.8,419.97 306.8,419.97 C306.8,419.97 306.78,420 306.78,420 C306.78,420 306.8,420 306.8,420 C306.8,420 306.86,420 306.86,420 C306.86,420 306.95,419.93 306.95,419.93 C306.95,419.93 306.86,419.96 306.86,419.96 C306.86,419.96 306.84,420.21 306.84,420.21 C306.84,420.21 306.89,420.1 306.89,420.1 C306.89,420.1 306.83,420.1 306.83,420.1 C306.83,420.1 306.5,420.99 306.83,420.17 C307.17,419.36 306.69,420.75 306.69,419.9 C306.69,419.04 306.89,420.14 306.89,420.14 C306.89,420.14 306.93,420.01 306.93,420.01 C306.93,420.01 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 302.88,423.24 302.88,423.24 C302.88,423.24 302.6,423.2 302.6,423.2 C302.6,423.2 302.79,423.22 302.79,423.22 C302.79,423.22 302.47,423.18 302.47,423.18 C302.47,423.18 302.62,423.19 302.62,423.19 C302.62,423.19 302.53,423.17 302.53,423.17 C302.53,423.17 302.43,423.36 302.43,423.18 C302.43,422.99 302.57,423.16 302.57,423.16 C302.57,423.16 302.4,423.16 302.4,423.16 C302.4,423.16 302.71,423.1 302.71,423.1 C302.71,423.1 302.68,423.07 302.68,423.07 C302.68,423.07 302.76,423.09 302.76,423.09 C302.76,423.09 302.66,423.2 302.66,423.2 C302.66,423.2 302.71,423.14 302.71,423.14 C302.71,423.14 302.75,423.12 302.75,423.12 C302.75,423.12 302.75,423.18 302.75,423.18 C302.75,423.18 302.53,423.22 302.53,423.22 C302.53,423.22 306.79,419.98 306.79,419.98 C306.79,419.98 306.77,419.98 306.77,419.98 C306.77,419.98 306.8,419.98 306.8,419.98 C306.8,419.98 306.77,419.98 306.77,419.98 C306.77,419.98 306.79,419.98 306.79,419.98 C306.79,419.98 306.79,419.97 306.79,419.97z " to="M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 293.25,442.25 293.25,442.25 C293.25,442.25 298.5,442.3 298.5,442.3 C298.5,442.3 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 306.45,419.99 306.45,419.99 C306.45,419.99 301.77,423.53 301.77,423.53 C301.77,423.53 298.75,430.25 298.75,430.25 C298.75,430.25 293.3,430.28 293.3,430.28 C293.3,430.28 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 301.74,416.82 301.74,416.82 C301.74,416.82 292.58,424.16 292.58,424.16 C292.58,424.16 293.3,430.28 293.3,430.28 C293.3,430.28 298.75,430.25 298.75,430.25 C298.75,430.25 301.74,423.57 301.74,423.57 C301.74,423.57 306.45,419.97 306.45,419.97 C306.45,419.97 308.08,414.37 308.08,414.37 C308.08,414.37 310.3,409.7 310.3,409.7 C310.3,409.7 301.92,404.95 301.92,404.95z " keyTimes="0;0.1804511;0.2180451;0.2481203;0.2631579;0.2706767;0.2781955;0.2857143;0.3157895;0.3308271;0.3533835;0.3834586;0.406015;0.4135338;0.4210526;0.4511278;0.4736842;0.4887218;0.4962406;1" values="M306.79 419.97 C306.79,419.97 306.83,419.98 306.83,419.98 C306.83,419.98 306.8,419.97 306.8,419.97 C306.8,419.97 306.78,420 306.78,420 C306.78,420 306.8,420 306.8,420 C306.8,420 306.86,420 306.86,420 C306.86,420 306.95,419.93 306.95,419.93 C306.95,419.93 306.86,419.96 306.86,419.96 C306.86,419.96 306.84,420.21 306.84,420.21 C306.84,420.21 306.89,420.1 306.89,420.1 C306.89,420.1 306.83,420.1 306.83,420.1 C306.83,420.1 306.5,420.99 306.83,420.17 C307.17,419.36 306.69,420.75 306.69,419.9 C306.69,419.04 306.89,420.14 306.89,420.14 C306.89,420.14 306.93,420.01 306.93,420.01 C306.93,420.01 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 302.88,423.24 302.88,423.24 C302.88,423.24 302.6,423.2 302.6,423.2 C302.6,423.2 302.79,423.22 302.79,423.22 C302.79,423.22 302.47,423.18 302.47,423.18 C302.47,423.18 302.62,423.19 302.62,423.19 C302.62,423.19 302.53,423.17 302.53,423.17 C302.53,423.17 302.43,423.36 302.43,423.18 C302.43,422.99 302.57,423.16 302.57,423.16 C302.57,423.16 302.4,423.16 302.4,423.16 C302.4,423.16 302.71,423.1 302.71,423.1 C302.71,423.1 302.68,423.07 302.68,423.07 C302.68,423.07 302.76,423.09 302.76,423.09 C302.76,423.09 302.66,423.2 302.66,423.2 C302.66,423.2 302.71,423.14 302.71,423.14 C302.71,423.14 302.75,423.12 302.75,423.12 C302.75,423.12 302.75,423.18 302.75,423.18 C302.75,423.18 302.53,423.22 302.53,423.22 C302.53,423.22 306.79,419.98 306.79,419.98 C306.79,419.98 306.77,419.98 306.77,419.98 C306.77,419.98 306.8,419.98 306.8,419.98 C306.8,419.98 306.77,419.98 306.77,419.98 C306.77,419.98 306.79,419.98 306.79,419.98 C306.79,419.98 306.79,419.97 306.79,419.97z ;M306.79 419.97 C306.79,419.97 306.83,419.98 306.83,419.98 C306.83,419.98 306.8,419.97 306.8,419.97 C306.8,419.97 306.78,420 306.78,420 C306.78,420 306.8,420 306.8,420 C306.8,420 306.86,420 306.86,420 C306.86,420 306.95,419.93 306.95,419.93 C306.95,419.93 306.86,419.96 306.86,419.96 C306.86,419.96 306.84,420.21 306.84,420.21 C306.84,420.21 306.89,420.1 306.89,420.1 C306.89,420.1 306.83,420.1 306.83,420.1 C306.83,420.1 306.5,420.99 306.83,420.17 C307.17,419.36 306.69,420.75 306.69,419.9 C306.69,419.04 306.89,420.14 306.89,420.14 C306.89,420.14 306.93,420.01 306.93,420.01 C306.93,420.01 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 302.88,423.24 302.88,423.24 C302.88,423.24 302.6,423.2 302.6,423.2 C302.6,423.2 302.79,423.22 302.79,423.22 C302.79,423.22 302.47,423.18 302.47,423.18 C302.47,423.18 302.62,423.19 302.62,423.19 C302.62,423.19 302.53,423.17 302.53,423.17 C302.53,423.17 302.43,423.36 302.43,423.18 C302.43,422.99 302.57,423.16 302.57,423.16 C302.57,423.16 302.4,423.16 302.4,423.16 C302.4,423.16 302.71,423.1 302.71,423.1 C302.71,423.1 302.68,423.07 302.68,423.07 C302.68,423.07 302.76,423.09 302.76,423.09 C302.76,423.09 302.66,423.2 302.66,423.2 C302.66,423.2 302.71,423.14 302.71,423.14 C302.71,423.14 302.75,423.12 302.75,423.12 C302.75,423.12 302.75,423.18 302.75,423.18 C302.75,423.18 302.53,423.22 302.53,423.22 C302.53,423.22 306.79,419.98 306.79,419.98 C306.79,419.98 306.77,419.98 306.77,419.98 C306.77,419.98 306.8,419.98 306.8,419.98 C306.8,419.98 306.77,419.98 306.77,419.98 C306.77,419.98 306.79,419.98 306.79,419.98 C306.79,419.98 306.79,419.97 306.79,419.97z ;M310.92 429.74 C310.92,429.74 310.97,429.75 310.97,429.75 C310.97,429.75 310.93,429.74 310.93,429.74 C310.93,429.74 310.91,429.77 310.91,429.77 C310.91,429.77 310.94,429.77 310.94,429.77 C310.94,429.77 310.99,429.77 310.99,429.77 C310.99,429.77 311.09,429.7 311.09,429.7 C311.09,429.7 310.99,429.73 310.99,429.73 C310.99,429.73 310.9,434.91 310.9,434.91 C310.9,434.91 312.25,433.8 312.25,433.8 C312.25,433.8 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 303.93,428.18 303.93,428.18 C303.93,428.18 303.66,428.14 303.66,428.14 C303.66,428.14 303.84,428.16 303.84,428.16 C303.84,428.16 303.52,428.11 303.52,428.11 C303.52,428.11 303.67,428.12 303.67,428.12 C303.67,428.12 303.58,428.1 303.58,428.1 C303.58,428.1 303.49,428.3 303.49,428.11 C303.49,427.93 303.63,428.09 303.63,428.09 C303.63,428.09 303.45,428.1 303.45,428.1 C303.45,428.1 303.76,428.04 303.76,428.04 C303.76,428.04 303.73,428 303.73,428 C303.73,428 303.69,427.98 303.69,427.98 C303.69,427.98 303.71,428.13 303.71,428.13 C303.71,428.13 303.76,428.08 303.76,428.08 C303.76,428.08 303.8,428.06 303.8,428.06 C303.8,428.06 303.8,428.11 303.8,428.11 C303.8,428.11 303.58,428.16 303.58,428.16 C303.58,428.16 310.92,429.75 310.92,429.75 C310.92,429.75 310.91,429.75 310.91,429.75 C310.91,429.75 310.93,429.75 310.93,429.75 C310.93,429.75 310.9,429.75 310.9,429.75 C310.9,429.75 310.93,429.75 310.93,429.75 C310.93,429.75 310.92,429.74 310.92,429.74z ;M299.65 434.12 C299.65,434.12 299.7,434.13 299.7,434.13 C299.7,434.13 299.66,434.11 299.66,434.11 C299.66,434.11 299.64,434.14 299.64,434.14 C299.64,434.14 299.66,434.14 299.66,434.14 C299.66,434.14 299.72,434.15 299.72,434.15 C299.72,434.15 299.81,434.08 299.81,434.08 C299.81,434.08 299.72,434.11 299.72,434.11 C299.72,434.11 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 300.06,430.31 300.06,430.31 C300.06,430.31 299.78,430.27 299.78,430.27 C299.78,430.27 299.96,430.29 299.96,430.29 C299.96,430.29 299.65,430.25 299.65,430.25 C299.65,430.25 299.8,430.25 299.8,430.25 C299.8,430.25 299.7,430.24 299.7,430.24 C299.7,430.24 299.61,430.43 299.61,430.25 C299.61,430.06 299.75,430.22 299.75,430.22 C299.75,430.22 299.57,430.23 299.57,430.23 C299.57,430.23 299.89,430.17 299.89,430.17 C299.89,430.17 299.85,430.13 299.85,430.13 C299.85,430.13 299.82,430.12 299.82,430.12 C299.82,430.12 299.83,430.26 299.83,430.26 C299.83,430.26 299.89,430.21 299.89,430.21 C299.89,430.21 299.93,430.19 299.93,430.19 C299.93,430.19 299.93,430.25 299.93,430.25 C299.93,430.25 299.7,430.29 299.7,430.29 C299.7,430.29 299.65,434.13 299.65,434.13 C299.65,434.13 299.64,434.13 299.64,434.13 C299.64,434.13 299.66,434.13 299.66,434.13 C299.66,434.13 299.63,434.13 299.63,434.13 C299.63,434.13 299.65,434.13 299.65,434.13 C299.65,434.13 299.65,434.12 299.65,434.12z ;M292.83 434.12 C292.83,434.12 292.81,434.11 292.81,434.11 C292.81,434.11 292.84,434.12 292.84,434.12 C292.84,434.12 292.82,434.15 292.82,434.15 C292.82,434.15 292.85,434.15 292.85,434.15 C292.85,434.15 294.61,434.08 294.61,434.08 C294.61,434.08 298.37,434.07 298.37,434.07 C298.37,434.07 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 298.47,430.31 298.47,430.31 C298.47,430.31 294.44,430.33 294.44,430.33 C294.44,430.33 292.89,430.31 292.89,430.31 C292.89,430.31 292.69,430.25 292.69,430.25 C292.69,430.25 292.72,430.28 292.72,430.28 C292.72,430.28 292.63,430.26 292.63,430.26 C292.63,430.26 292.65,430.43 292.65,430.25 C292.65,430.06 292.56,430.15 292.56,430.15 C292.56,430.15 292.61,430.23 292.61,430.23 C292.61,430.23 292.93,430.17 292.93,430.17 C292.93,430.17 292.89,430.13 292.89,430.13 C292.89,430.13 292.85,430.12 292.85,430.12 C292.85,430.12 292.87,430.26 292.87,430.26 C292.87,430.26 292.93,430.21 292.93,430.21 C292.93,430.21 292.96,430.19 292.96,430.19 C292.96,430.19 292.96,430.25 292.96,430.25 C292.96,430.25 292.77,430.22 292.77,430.22 C292.77,430.22 292.83,434.13 292.83,434.13 C292.83,434.13 292.82,434.13 292.82,434.13 C292.82,434.13 292.84,434.13 292.84,434.13 C292.84,434.13 292.81,434.13 292.81,434.13 C292.81,434.13 292.83,434.13 292.83,434.13 C292.83,434.13 292.83,434.12 292.83,434.12z ;M286.91 434.04 C286.91,434.04 286.89,434.02 286.89,434.02 C286.89,434.02 286.92,434.03 286.92,434.03 C286.92,434.03 286.9,434.06 286.9,434.06 C286.9,434.06 286.92,434.06 286.92,434.06 C286.92,434.06 294.61,434.08 294.61,434.08 C294.61,434.08 298.39,434.03 298.39,434.03 C298.39,434.03 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 298.47,430.31 298.47,430.31 C298.47,430.31 294.44,430.33 294.44,430.33 C294.44,430.33 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 291.91,428.68 291.91,428.68 C291.91,428.68 291.82,428.67 291.82,428.67 C291.82,428.67 291.88,428.65 291.88,428.46 C291.88,428.28 291.78,428.37 291.78,428.37 C291.78,428.37 291.84,428.44 291.84,428.44 C291.84,428.44 292.15,428.39 292.15,428.39 C292.15,428.39 292.12,428.35 292.12,428.35 C292.12,428.35 292.08,428.33 292.08,428.33 C292.08,428.33 292.1,428.48 292.1,428.48 C292.1,428.48 292.15,428.42 292.15,428.42 C292.15,428.42 292.19,428.41 292.19,428.41 C292.19,428.41 292.19,428.46 292.19,428.46 C292.19,428.46 291.97,428.51 291.97,428.51 C291.97,428.51 287.14,434.07 287.14,434.07 C287.14,434.07 286.89,434.05 286.89,434.05 C286.89,434.05 286.92,434.05 286.92,434.05 C286.92,434.05 286.89,434.05 286.89,434.05 C286.89,434.05 286.91,434.05 286.91,434.05 C286.91,434.05 286.91,434.04 286.91,434.04z ;M286.7 429.47 C286.7,429.47 286.88,429.37 286.88,429.37 C286.88,429.37 286.52,429.45 286.52,429.45 C286.52,429.45 286.83,429.85 286.83,429.85 C286.83,429.85 286.14,434.18 286.14,434.18 C286.14,434.18 294.61,434.08 294.61,434.08 C294.61,434.08 298.37,434.08 298.37,434.08 C298.37,434.08 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 298.38,430.31 298.38,430.31 C298.38,430.31 294.56,430.33 294.56,430.33 C294.56,430.33 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 291.99,426.42 291.99,426.42 C291.99,426.42 291.87,426.34 291.87,426.34 C291.87,426.34 292.01,426.25 292.01,426.07 C292.01,425.88 292.05,425.99 292.05,425.99 C292.05,425.99 291.97,425.95 291.97,425.95 C291.97,425.95 292.39,425.98 292.39,425.98 C292.39,425.98 292.27,426.05 292.27,426.05 C292.27,426.05 292.35,425.99 292.35,425.99 C292.35,425.99 292.32,426 292.32,426 C292.32,426 292.4,426 292.4,426 C292.4,426 292.4,426.06 292.4,426.06 C292.4,426.06 292.39,426.05 292.39,426.05 C292.39,426.05 292.62,426.45 292.62,426.45 C292.62,426.45 286.78,429.41 286.78,429.41 C286.78,429.41 286.55,429.2 286.55,429.2 C286.55,429.2 286.62,429.38 286.62,429.38 C286.62,429.38 286.51,429.44 286.51,429.44 C286.51,429.44 286.46,429.37 286.46,429.37 C286.46,429.37 286.7,429.47 286.7,429.47z ;M286.5 424.9 C286.5,424.9 286.87,424.72 286.87,424.72 C286.87,424.72 286.13,424.87 286.13,424.87 C286.13,424.87 286.76,425.64 286.76,425.64 C286.76,425.64 285.37,434.3 285.37,434.3 C285.37,434.3 294.63,434.09 294.63,434.09 C294.63,434.09 298.37,434.09 298.37,434.09 C298.37,434.09 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 298.36,430.31 298.36,430.31 C298.36,430.31 294.59,430.33 294.59,430.33 C294.59,430.33 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.06,424.16 292.06,424.16 C292.06,424.16 291.91,424.01 291.91,424.01 C291.91,424.01 292.13,423.86 292.13,423.68 C292.13,423.49 292.32,423.6 292.32,423.6 C292.32,423.6 292.1,423.46 292.1,423.46 C292.1,423.46 292.62,423.57 292.62,423.57 C292.62,423.57 292.43,423.75 292.43,423.75 C292.43,423.75 292.62,423.64 292.62,423.64 C292.62,423.64 292.54,423.53 292.54,423.53 C292.54,423.53 292.65,423.57 292.65,423.57 C292.65,423.57 292.62,423.72 292.62,423.72 C292.62,423.72 292.58,423.64 292.58,423.64 C292.58,423.64 293.27,424.39 293.27,424.39 C293.27,424.39 286.43,424.75 286.43,424.75 C286.43,424.75 286.2,424.35 286.2,424.35 C286.2,424.35 286.31,424.72 286.31,424.72 C286.31,424.72 286.13,424.83 286.13,424.83 C286.13,424.83 286.02,424.68 286.02,424.68 C286.02,424.68 286.5,424.9 286.5,424.9z ;M285.53 417.93 C285.53,417.93 285.61,418.01 285.61,418.01 C285.61,418.01 285.39,417.97 285.39,417.97 C285.39,417.97 285.68,418.12 285.68,418.12 C285.68,418.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.61,434.08 294.61,434.08 C294.61,434.08 298.38,434.11 298.38,434.11 C298.38,434.11 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 298.71,430.31 298.71,430.31 C298.71,430.31 293.3,430.31 293.3,430.31 C293.3,430.31 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.26,417.75 291.26,417.56 C291.26,417.38 291.34,417.38 291.34,417.38 C291.34,417.38 291.45,417.54 291.45,417.54 C291.45,417.54 291.21,417.5 291.21,417.5 C291.21,417.5 291.32,417.45 291.32,417.45 C291.32,417.45 291.28,417.51 291.28,417.51 C291.28,417.51 291.5,417.56 291.5,417.56 C291.5,417.56 291.52,417.54 291.52,417.54 C291.52,417.54 291.45,417.6 291.45,417.6 C291.45,417.6 291.43,417.67 291.43,417.67 C291.43,417.67 291.41,417.89 291.41,417.89 C291.41,417.89 291.24,417.95 291.24,417.95 C291.24,417.95 285.98,417.86 285.98,417.86 C285.98,417.86 286.02,417.69 286.02,417.69 C286.02,417.69 285.92,417.77 285.92,417.77 C285.92,417.77 285.81,417.62 285.81,417.62 C285.81,417.62 285.53,417.93 285.53,417.93z ;M284.93 404.18 C284.93,404.18 281.14,411.97 281.14,411.97 C281.14,411.97 273.88,412.04 273.88,412.04 C273.88,412.04 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.61,434.08 294.61,434.08 C294.61,434.08 298.36,434.08 298.36,434.08 C298.36,434.08 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 298.35,430.31 298.35,430.31 C298.35,430.31 294.59,430.32 294.59,430.32 C294.59,430.32 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 291.91,415.81 291.91,415.81 C291.91,415.81 291.8,415.82 291.8,415.82 C291.8,415.82 291.88,415.73 291.88,415.73 C291.88,415.73 291.9,415.66 291.9,415.66 C291.9,415.66 291.8,415.65 291.8,415.65 C291.8,415.65 291.73,415.73 291.73,415.73 C291.73,415.73 291.87,415.58 291.87,415.58 C291.87,415.58 291.87,415.71 291.87,415.71 C291.87,415.71 291.83,415.72 291.83,415.72 C291.83,415.72 291.82,415.71 291.82,415.71 C291.82,415.71 291.66,414.92 291.66,414.92 C291.66,414.92 291.45,413.38 291.45,413.38 C291.45,413.38 291.09,411.81 291.09,411.81 C291.09,411.81 291.05,411.77 291.05,411.77 C291.05,411.77 289.08,410.26 289.08,410.26 C289.08,410.26 284.93,404.18 284.93,404.18z ;M298.66 404.21 C298.66,404.21 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.61,434.09 294.61,434.09 C294.61,434.09 298.35,434.08 298.35,434.08 C298.35,434.08 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 298.76,430.32 298.76,430.32 C298.76,430.32 294.62,430.33 294.62,430.33 C294.62,430.33 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 300.75,413.19 300.75,413.19 C300.75,413.19 300.74,413.2 300.74,413.2 C300.74,413.2 300.68,413.28 300.68,413.28 C300.68,413.28 300.74,413.15 300.74,413.15 C300.74,413.15 300.76,413.19 300.76,413.19 C300.76,413.19 300.77,413.17 300.77,413.17 C300.77,413.17 303.55,406.44 303.55,406.44 C303.55,406.44 302.85,404.47 302.85,404.47 C302.85,404.47 301.29,403.47 301.29,403.47 C301.29,403.47 301.18,403.32 301.18,403.32 C301.18,403.32 298.66,404.21 298.66,404.21z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.61,434.07 294.61,434.07 C294.61,434.07 298.36,434.07 298.36,434.07 C298.36,434.07 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 298.73,430.31 298.73,430.31 C298.73,430.31 293.3,430.33 293.3,430.33 C293.3,430.33 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 302.59,416.02 302.59,416.02 C302.59,416.02 302.55,415.98 302.55,415.98 C302.55,415.98 302.63,415.99 302.63,415.99 C302.63,415.99 306.67,409.55 306.67,409.55 C306.67,409.55 306.65,409.61 306.65,409.61 C306.65,409.61 306.59,409.55 306.59,409.55 C306.59,409.55 306.69,409.72 306.69,409.72 C306.69,409.72 306.58,409.57 306.58,409.57 C306.58,409.57 301.92,404.95 301.92,404.95z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.61,434.09 294.61,434.09 C294.61,434.09 298.36,434.09 298.36,434.09 C298.36,434.09 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 306.29,420.1 306.29,420.1 C306.29,420.1 301.7,423.39 301.7,423.39 C301.7,423.39 298.38,430.31 298.38,430.31 C298.38,430.31 293.4,430.32 293.4,430.32 C293.4,430.32 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 302.63,417.02 302.63,417.02 C302.63,417.02 302.61,416.97 302.61,416.97 C302.61,416.97 302.63,416.9 302.63,416.9 C302.63,416.9 307.12,415.55 307.12,415.55 C307.12,415.55 307.51,415.47 307.51,415.47 C307.51,415.47 307.52,415.47 307.52,415.47 C307.52,415.47 309.01,412.56 309.01,412.56 C309.01,412.56 310.3,409.7 310.3,409.7 C310.3,409.7 301.92,404.95 301.92,404.95z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.6,434.08 294.6,434.08 C294.6,434.08 298.37,434.07 298.37,434.07 C298.37,434.07 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 306.05,420.2 306.05,420.2 C306.05,420.2 301.63,423.29 301.63,423.29 C301.63,423.29 298.57,430.33 298.57,430.33 C298.57,430.33 293.35,430.32 293.35,430.32 C293.35,430.32 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 301.74,416.82 301.74,416.82 C301.74,416.82 297.55,418.67 297.55,418.67 C297.55,418.67 300.2,418.16 300.2,418.16 C300.2,418.16 306.72,417.16 306.72,417.16 C306.72,417.16 307.56,417.29 307.56,417.29 C307.56,417.29 307.59,417.33 307.59,417.33 C307.59,417.33 308.54,413.47 308.54,413.47 C308.54,413.47 306.71,408.22 306.71,408.22 C306.71,408.22 301.92,404.95 301.92,404.95z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.62,434.09 294.62,434.09 C294.62,434.09 298.35,434.08 298.35,434.08 C298.35,434.08 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 305.8,420.3 305.8,420.3 C305.8,420.3 301.55,423.2 301.55,423.2 C301.55,423.2 298.74,430.31 298.74,430.31 C298.74,430.31 293.34,430.32 293.34,430.32 C293.34,430.32 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 301.74,416.82 301.74,416.82 C301.74,416.82 297.55,418.67 297.55,418.67 C297.55,418.67 300.2,418.16 300.2,418.16 C300.2,418.16 306.32,418.77 306.32,418.77 C306.32,418.77 307.34,417.78 307.34,417.78 C307.34,417.78 307.74,418.52 307.74,418.52 C307.74,418.52 308.08,414.37 308.08,414.37 C308.08,414.37 310.3,409.7 310.3,409.7 C310.3,409.7 301.92,404.95 301.92,404.95z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.6,434.09 294.6,434.09 C294.6,434.09 298.35,434.08 298.35,434.08 C298.35,434.08 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 305.98,420.3 305.98,420.3 C305.98,420.3 301.72,423.59 301.72,423.59 C301.72,423.59 298.75,430.25 298.75,430.25 C298.75,430.25 293.3,430.28 293.3,430.28 C293.3,430.28 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 301.74,416.82 301.74,416.82 C301.74,416.82 297.55,418.67 297.55,418.67 C297.55,418.67 296.68,421.72 296.68,421.72 C296.68,421.72 300.57,423.18 300.57,423.18 C300.57,423.18 301.74,423.57 301.74,423.57 C301.74,423.57 306.45,419.97 306.45,419.97 C306.45,419.97 308.08,414.37 308.08,414.37 C308.08,414.37 310.3,409.7 310.3,409.7 C310.3,409.7 301.92,404.95 301.92,404.95z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.61,434.08 294.61,434.08 C294.61,434.08 298.36,434.09 298.36,434.09 C298.36,434.09 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 306.41,419.97 306.41,419.97 C306.41,419.97 301.7,423.64 301.7,423.64 C301.7,423.64 298.69,430.31 298.69,430.31 C298.69,430.31 294.56,430.33 294.56,430.33 C294.56,430.33 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 301.74,416.82 301.74,416.82 C301.74,416.82 292.58,424.16 292.58,424.16 C292.58,424.16 294.58,430.33 294.58,430.33 C294.58,430.33 298.38,430.31 298.38,430.31 C298.38,430.31 301.74,423.57 301.74,423.57 C301.74,423.57 306.45,419.97 306.45,419.97 C306.45,419.97 308.08,414.37 308.08,414.37 C308.08,414.37 310.3,409.7 310.3,409.7 C310.3,409.7 301.92,404.95 301.92,404.95z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 293.73,439.55 293.73,439.55 C293.73,439.55 298.46,439.54 298.46,439.54 C298.46,439.54 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 306.43,419.98 306.43,419.98 C306.43,419.98 301.75,423.57 301.75,423.57 C301.75,423.57 298.73,430.27 298.73,430.27 C298.73,430.27 293.72,430.3 293.72,430.3 C293.72,430.3 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 301.74,416.82 301.74,416.82 C301.74,416.82 292.58,424.16 292.58,424.16 C292.58,424.16 293.7,430.31 293.7,430.31 C293.7,430.31 298.74,430.26 298.74,430.26 C298.74,430.26 301.74,423.57 301.74,423.57 C301.74,423.57 306.45,419.97 306.45,419.97 C306.45,419.97 308.08,414.37 308.08,414.37 C308.08,414.37 310.3,409.7 310.3,409.7 C310.3,409.7 301.92,404.95 301.92,404.95z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 293.25,442.25 293.25,442.25 C293.25,442.25 298.5,442.3 298.5,442.3 C298.5,442.3 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 306.45,419.99 306.45,419.99 C306.45,419.99 301.77,423.53 301.77,423.53 C301.77,423.53 298.75,430.25 298.75,430.25 C298.75,430.25 293.3,430.28 293.3,430.28 C293.3,430.28 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 301.74,416.82 301.74,416.82 C301.74,416.82 292.58,424.16 292.58,424.16 C292.58,424.16 293.3,430.28 293.3,430.28 C293.3,430.28 298.75,430.25 298.75,430.25 C298.75,430.25 301.74,423.57 301.74,423.57 C301.74,423.57 306.45,419.97 306.45,419.97 C306.45,419.97 308.08,414.37 308.08,414.37 C308.08,414.37 310.3,409.7 310.3,409.7 C310.3,409.7 301.92,404.95 301.92,404.95z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 293.25,442.25 293.25,442.25 C293.25,442.25 298.5,442.3 298.5,442.3 C298.5,442.3 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 306.45,419.99 306.45,419.99 C306.45,419.99 301.77,423.53 301.77,423.53 C301.77,423.53 298.75,430.25 298.75,430.25 C298.75,430.25 293.3,430.28 293.3,430.28 C293.3,430.28 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 301.74,416.82 301.74,416.82 C301.74,416.82 292.58,424.16 292.58,424.16 C292.58,424.16 293.3,430.28 293.3,430.28 C293.3,430.28 298.75,430.25 298.75,430.25 C298.75,430.25 301.74,423.57 301.74,423.57 C301.74,423.57 306.45,419.97 306.45,419.97 C306.45,419.97 308.08,414.37 308.08,414.37 C308.08,414.37 310.3,409.7 310.3,409.7 C310.3,409.7 301.92,404.95 301.92,404.95z " keySplines="0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0 0 0 0" calcMode="spline"/>
                                        <clipPath id="_R_G_L_0_C_0">
                                            <path id="_R_G_L_0_C_0_P_0" fill-rule="nonzero"/>
                                        </clipPath>
                                        <animate attributeType="XML" attributeName="opacity" dur="2s" from="0" to="1" xlink:href="#time_group"/>
                                    </defs>
                                    <g id="_R_G">
                                        <g id="_R_G_L_1_G" transform=" translate(127.638, 127.945) scale(3.37139, 3.37139) translate(-297.638, -420.945)">
                                            <g clip-path="url(#_R_G_L_1_C_0)">
                                                <path id="_R_G_L_1_G_G_0_D_0_P_0" class="bpa-front-loader-cl-primary" fill-opacity="1" fill-rule="nonzero" d=" M328 398.61 C328,398.61 328,446.23 328,446.23 C328,449.7 325.2,452.5 321.75,452.5 C321.75,452.5 274.25,452.5 274.25,452.5 C270.8,452.5 268,449.7 268,446.23 C268,446.23 268,398.61 268,398.61 C268,395.15 270.8,392.35 274.25,392.35 C274.25,392.35 283.46,392.26 283.46,392.26 C283.46,392.26 283.46,390.38 283.46,390.38 C283.46,389.76 284.08,388.5 285.33,388.5 C286.58,388.5 287.21,389.75 287.21,390.38 C287.21,390.38 287.21,397.89 287.21,397.89 C287.21,398.53 286.59,399.78 285.33,399.78 C284.08,399.78 283.46,398.53 283.46,397.9 C283.46,397.9 283.46,396.02 283.46,396.02 C283.46,396.02 275.5,396.1 275.5,396.1 C273.43,396.1 271.75,397.79 271.75,399.86 C271.75,399.86 271.75,444.98 271.75,444.98 C271.75,447.06 273.43,448.74 275.5,448.74 C275.5,448.74 320.5,448.74 320.5,448.74 C322.57,448.74 324.25,447.06 324.25,444.98 C324.25,444.98 324.25,399.86 324.25,399.86 C324.25,397.79 322.57,396.1 320.5,396.1 C320.5,396.1 312.62,396.1 312.62,396.1 C312.62,396.1 312.63,397.06 312.63,397.99 C312.63,398.61 312,399.86 310.75,399.86 C309.5,399.86 308.88,398.61 308.88,397.98 C308.88,397.98 308.87,396.1 308.87,396.1 C308.87,396.1 301.88,396.1 301.88,396.1 C300.84,396.1 300,395.26 300,394.23 C300,393.19 300.84,392.35 301.88,392.35 C301.88,392.35 308.87,392.35 308.87,392.35 C308.87,392.35 308.87,390.47 308.87,390.47 C308.87,389.83 309.5,388.5 310.75,388.5 C312,388.5 312.62,389.84 312.62,390.47 C312.62,390.47 312.62,392.35 312.62,392.35 C312.62,392.35 321.75,392.35 321.75,392.35 C325.2,392.35 328,395.15 328,398.61z "/>
                                            </g>
                                        </g>
                                        <g id="_R_G_L_0_G" transform=" translate(125.555, 126.412) scale(3.37139, 3.37139) translate(-297.638, -420.945)">
                                            <g clip-path="url(#_R_G_L_0_C_0)">
                                                <path id="_R_G_L_0_G_G_0_D_0_P_0" class="bpa-front-loader-cl-primary" fill-opacity="1" fill-rule="nonzero" d=" M305.86 420.29 C305.86,420.29 307.11,419.04 307.11,415.28 C307.11,409.01 303.36,407.76 298.36,407.76 C298.36,407.76 287.11,407.76 287.11,407.76 C287.11,407.76 287.11,434.08 287.11,434.08 C287.11,434.08 294.61,434.08 294.61,434.08 C294.61,434.08 294.61,441.6 294.61,441.6 C294.61,441.6 298.36,441.6 298.36,441.6 C298.36,441.6 298.36,434.08 298.36,434.08 C302.71,434.08 305.73,434.08 307.98,431.3 C309.07,429.95 309.62,428.24 309.61,426.5 C309.61,425.58 309.51,424.67 309.3,424.05 C308.73,422.65 308.36,421.55 305.86,420.29z  M302.11 430.32 C302.11,430.32 298.36,430.32 298.36,430.32 C298.36,430.32 298.36,426.56 298.36,426.56 C298.36,424.48 300.03,422.8 302.11,422.8 C304.13,422.8 305.86,424.43 305.86,426.56 C305.86,428.78 304.03,430.32 302.11,430.32z  M299.07 419.95 C298.43,420.26 297.82,420.63 297.26,421.05 C295.87,422.1 294.61,423.58 294.61,426.56 C294.61,426.56 294.61,430.32 294.61,430.32 C294.61,430.32 290.86,430.32 290.86,430.32 C290.86,430.32 290.86,411.52 290.86,411.52 C290.86,411.52 298.36,411.52 298.36,411.52 C301.35,411.52 303.36,412.77 303.36,415.28 C303.36,417.58 301.65,418.68 299.07,419.95z "/>
                                            </g>
                                        </g>
                                    </g>
                                    <g id="time_group"/>
                                </svg>
                            </div>
                        </div> 
                    </div>
                    <div v-if="(recurring_appointment_loader == false && appointment_step_form_data.recurring_appointments.length != 0)" class="bpa-recurring-appointment-data">
                        <label class="bpa-upcoming-appointments-label bpa-front-module-heading" v-html="recurring_upcoming_appointments_label"></label>
                        <el-row :gutter="16" class="bpa-recurring-appointment-body-content">

                            <el-col v-for="(recurring_item, rkey) in appointment_step_form_data.recurring_appointments" :class="[((recurring_item.is_suggested == 1)?'bpa-upcomming-suggested':''),((recurring_item.is_not_avaliable == 1)?'bpa-upcomming-notavaliable':'')]" class="bpa-upcomming-appointments" :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
                                <div class="bpa-lspd__item">
                                    <div class="bpa-lspd__item-val">
                                        <div class="bpa-hh-item__date-col bpa-hh-item__date-col-date">
                                            <span class="bpa-front-tm--item-icon material-icons-round"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"></path><path d="M19 4h-1V3c0-.55-.45-1-1-1s-1 .45-1 1v1H8V3c0-.55-.45-1-1-1s-1 .45-1 1v1H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 15c0 .55-.45 1-1 1H6c-.55 0-1-.45-1-1V9h14v10zM7 11h2v2H7zm4 0h2v2h-2zm4 0h2v2h-2z"></path></svg></span>
                                            <span>{{recurring_item.selected_date | bookingpress_format_date}}</span>
                                        </div>                                    
                                        <div class="bpa-hh-item-info-col">                                       
                                            <p>{{recurring_item.display_formated_date_and_time}}</p>
                                        </div>                                     
                                    </div>
                                    <div class="bpa-card__item">                                       
                                        <el-button @click="open_recurring_modal(event,rkey,'<?php echo esc_html($device_view); ?>')" class="bpa-front-btn bpa-front-btn--icon-without-box bpa-edit-appointment-btn" >                                                
                                            <svg viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                <g clip-path="url(#clip0_3168_6275)">
                                                    <path d="M2.5 14.5501V17.0835C2.5 17.3168 2.68333 17.5001 2.91667 17.5001H5.45C5.55833 17.5001 5.66667 17.4585 5.74167 17.3751L14.8417 8.28346L11.7167 5.15846L2.625 14.2501C2.54167 14.3335 2.5 14.4335 2.5 14.5501ZM17.2583 5.8668C17.5833 5.5418 17.5833 5.0168 17.2583 4.6918L15.3083 2.7418C14.9833 2.4168 14.4583 2.4168 14.1333 2.7418L12.6083 4.2668L15.7333 7.3918L17.2583 5.8668Z" />
                                                </g>
                                                <defs>
                                                    <clipPath id="clip0_3168_6275">
                                                        <rect width="20" height="20" fill="white"/>
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                        </el-button>
                                    </div>
                                    <div v-if="(recurring_item.is_suggested == 1 || recurring_item.is_not_avaliable == 1)" :class="[((recurring_item.is_suggested == 1)?'bpa-recurring-msg-suggested':''),((recurring_item.is_not_avaliable == 1)?'bpa-recurring-msg-notavaliable':'')]" class="bpa-recurring-msg">
                                        <span><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 7c.55 0 1 .45 1 1v4c0 .55-.45 1-1 1s-1-.45-1-1V8c0-.55.45-1 1-1zm-.01-5C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm1-3h-2v-2h2v2z"></path></svg></span><span v-html="(recurring_item.is_suggested == 1)?recurring_suggested_message:recurring_not_avaliable_message"></span>
                                    </div>      
                                    <?php if($device_view != 'mobile'){ ?>
                                        <div v-if="(recurring_item.is_suggested != 1 && recurring_item.is_not_avaliable != 1 && bookingpress_check_previous_appointment(rkey))" class="bpa-recurring-msg">&nbsp;</div>
                                    <?php } ?>    
                                    <?php if($device_view=='mobile'){ ?>
                                    <div v-if="recurring_appointment_device == 'mobile' && appointment_step_form_data.recurring_edit_index == rkey" class="bpa-card-item-mobile-edit-appointment" >
                                        <div class="bpa-edit-appointment-heading">
                                            <el-row type="flex">
                                                <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">                            
                                                    <h1 class="bpa-page-heading bpa-front-module-heading">{{recurring_edit_appointment_title}}</h1>
                                                </el-col>
                                            </el-row>
                                        </div>
                                        <div class="bpa-edit-appointment-body bpa-edit-appointment-item">
                                            <el-container class="bpa-grid-list-container bpa-add-categpry-container">
                                            <div v-if="recurring_appointment_loader_edit == true" class="bpa-recurring-appointment-loader-edit">
                                                    <div class="bpa-front-loader-container">
                                                        <div class="bpa-front-loader">
                                                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid meet" width="256" height="256" viewBox="0 0 256 256" style="width:100%;height:100%">
                                                                <defs>
                                                                    <animate repeatCount="indefinite" dur="2.2166667s" begin="0s" xlink:href="#_R_G_L_1_C_0_P_0" fill="freeze" attributeName="d" attributeType="XML" from="M294.33 386.7 C294.33,386.7 291.96,386.7 291.96,386.7 C291.96,386.7 291.67,391.89 291.67,391.89 C291.67,391.89 292.41,396.34 292.41,396.34 C292.41,396.34 292.11,401.09 292.11,401.09 C292.11,401.09 295.67,401.09 295.67,401.09 C295.67,401.09 295.82,396.05 295.82,396.05 C295.82,396.05 295.97,391.75 295.97,391.75 C295.97,391.75 294.33,386.7 294.33,386.7z " to="M330.93 386.68 C330.93,386.68 263.6,386.68 263.6,386.68 C263.6,386.68 265.82,453.13 265.82,453.13 C265.82,453.13 295.78,456.98 295.78,456.98 C295.78,456.98 295.89,452.83 295.89,452.83 C295.89,452.83 296.26,452.98 296.26,452.98 C296.26,452.98 295.78,457.13 295.78,457.13 C295.78,457.13 329.59,454.91 329.59,454.91 C329.59,454.91 330.93,386.68 330.93,386.68z " keyTimes="0;0.5037594;0.5263158;0.5789474;0.6691729;0.6992481;0.7593985;0.7669173;1" values="M294.33 386.7 C294.33,386.7 291.96,386.7 291.96,386.7 C291.96,386.7 291.67,391.89 291.67,391.89 C291.67,391.89 292.41,396.34 292.41,396.34 C292.41,396.34 292.11,401.09 292.11,401.09 C292.11,401.09 295.67,401.09 295.67,401.09 C295.67,401.09 295.82,396.05 295.82,396.05 C295.82,396.05 295.97,391.75 295.97,391.75 C295.97,391.75 294.33,386.7 294.33,386.7z ;M294.33 386.7 C294.33,386.7 291.96,386.7 291.96,386.7 C291.96,386.7 291.67,391.89 291.67,391.89 C291.67,391.89 292.41,396.34 292.41,396.34 C292.41,396.34 292.11,401.09 292.11,401.09 C292.11,401.09 295.67,401.09 295.67,401.09 C295.67,401.09 295.82,396.05 295.82,396.05 C295.82,396.05 295.97,391.75 295.97,391.75 C295.97,391.75 294.33,386.7 294.33,386.7z ;M303.49 386.7 C303.49,386.7 284.88,386.7 284.88,386.7 C284.88,386.7 284.88,402.72 284.88,402.72 C284.88,402.72 293.41,402.87 293.41,402.87 C293.41,402.87 293.07,405.24 293.07,405.24 C293.07,405.24 296.63,405.24 296.63,405.24 C296.63,405.24 296.82,402.57 296.82,402.57 C296.82,402.57 304.49,401.98 304.49,401.98 C304.49,401.98 303.49,386.7 303.49,386.7z ;M330.97 386.7 C330.97,386.7 263.64,386.7 263.64,386.7 C263.64,386.7 265.56,398.12 265.56,398.12 C265.56,398.12 266.75,407.02 266.75,407.02 C266.75,407.02 294.78,405.83 294.78,405.83 C294.78,405.83 298.34,405.83 298.34,405.83 C298.34,405.83 332.75,406.72 332.75,406.72 C332.75,406.72 332.45,399.46 332.45,399.46 C332.45,399.46 330.97,386.7 330.97,386.7z ;M330.97 386.7 C330.97,386.7 263.64,386.7 263.64,386.7 C263.64,386.7 265.56,442.32 265.56,442.32 C265.56,442.32 266.75,448.4 266.75,448.4 C266.75,448.4 283.8,447.51 283.8,447.51 C283.8,447.51 312.06,447.21 312.06,447.21 C312.06,447.21 332.75,448.1 332.75,448.1 C332.75,448.1 332.45,443.65 332.45,443.65 C332.45,443.65 330.97,386.7 330.97,386.7z ;M330.97 386.7 C330.97,386.7 263.64,386.7 263.64,386.7 C263.64,386.7 265.86,453.14 265.86,453.14 C265.86,453.14 276.98,456.11 276.98,456.11 C276.98,456.11 277.28,447.51 277.28,447.51 C277.28,447.51 319.47,447.81 319.47,447.81 C319.47,447.81 318.81,456.11 318.81,456.11 C318.81,456.11 329.63,454.92 329.63,454.92 C329.63,454.92 330.97,386.7 330.97,386.7z ;M330.93 386.68 C330.93,386.68 263.6,386.68 263.6,386.68 C263.6,386.68 265.82,453.13 265.82,453.13 C265.82,453.13 295.78,456.98 295.78,456.98 C295.78,456.98 295.63,448.83 295.63,448.83 C295.63,448.83 295.71,448.75 295.71,448.75 C295.71,448.75 295.78,457.13 295.78,457.13 C295.78,457.13 329.59,454.91 329.59,454.91 C329.59,454.91 330.93,386.68 330.93,386.68z ;M330.93 386.68 C330.93,386.68 263.6,386.68 263.6,386.68 C263.6,386.68 265.82,453.13 265.82,453.13 C265.82,453.13 295.78,456.98 295.78,456.98 C295.78,456.98 295.89,452.83 295.89,452.83 C295.89,452.83 296.26,452.98 296.26,452.98 C296.26,452.98 295.78,457.13 295.78,457.13 C295.78,457.13 329.59,454.91 329.59,454.91 C329.59,454.91 330.93,386.68 330.93,386.68z ;M330.93 386.68 C330.93,386.68 263.6,386.68 263.6,386.68 C263.6,386.68 265.82,453.13 265.82,453.13 C265.82,453.13 295.78,456.98 295.78,456.98 C295.78,456.98 295.89,452.83 295.89,452.83 C295.89,452.83 296.26,452.98 296.26,452.98 C296.26,452.98 295.78,457.13 295.78,457.13 C295.78,457.13 329.59,454.91 329.59,454.91 C329.59,454.91 330.93,386.68 330.93,386.68z " keySplines="0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0 0 0 0" calcMode="spline"/>
                                                                    <clipPath id="_R_G_L_1_C_0">
                                                                        <path id="_R_G_L_1_C_0_P_0" fill-rule="nonzero"/>
                                                                    </clipPath>
                                                                    <animate repeatCount="indefinite" dur="2.2166667s" begin="0s" xlink:href="#_R_G_L_0_C_0_P_0" fill="freeze" attributeName="d" attributeType="XML" from="M306.79 419.97 C306.79,419.97 306.83,419.98 306.83,419.98 C306.83,419.98 306.8,419.97 306.8,419.97 C306.8,419.97 306.78,420 306.78,420 C306.78,420 306.8,420 306.8,420 C306.8,420 306.86,420 306.86,420 C306.86,420 306.95,419.93 306.95,419.93 C306.95,419.93 306.86,419.96 306.86,419.96 C306.86,419.96 306.84,420.21 306.84,420.21 C306.84,420.21 306.89,420.1 306.89,420.1 C306.89,420.1 306.83,420.1 306.83,420.1 C306.83,420.1 306.5,420.99 306.83,420.17 C307.17,419.36 306.69,420.75 306.69,419.9 C306.69,419.04 306.89,420.14 306.89,420.14 C306.89,420.14 306.93,420.01 306.93,420.01 C306.93,420.01 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 302.88,423.24 302.88,423.24 C302.88,423.24 302.6,423.2 302.6,423.2 C302.6,423.2 302.79,423.22 302.79,423.22 C302.79,423.22 302.47,423.18 302.47,423.18 C302.47,423.18 302.62,423.19 302.62,423.19 C302.62,423.19 302.53,423.17 302.53,423.17 C302.53,423.17 302.43,423.36 302.43,423.18 C302.43,422.99 302.57,423.16 302.57,423.16 C302.57,423.16 302.4,423.16 302.4,423.16 C302.4,423.16 302.71,423.1 302.71,423.1 C302.71,423.1 302.68,423.07 302.68,423.07 C302.68,423.07 302.76,423.09 302.76,423.09 C302.76,423.09 302.66,423.2 302.66,423.2 C302.66,423.2 302.71,423.14 302.71,423.14 C302.71,423.14 302.75,423.12 302.75,423.12 C302.75,423.12 302.75,423.18 302.75,423.18 C302.75,423.18 302.53,423.22 302.53,423.22 C302.53,423.22 306.79,419.98 306.79,419.98 C306.79,419.98 306.77,419.98 306.77,419.98 C306.77,419.98 306.8,419.98 306.8,419.98 C306.8,419.98 306.77,419.98 306.77,419.98 C306.77,419.98 306.79,419.98 306.79,419.98 C306.79,419.98 306.79,419.97 306.79,419.97z " to="M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 293.25,442.25 293.25,442.25 C293.25,442.25 298.5,442.3 298.5,442.3 C298.5,442.3 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 306.45,419.99 306.45,419.99 C306.45,419.99 301.77,423.53 301.77,423.53 C301.77,423.53 298.75,430.25 298.75,430.25 C298.75,430.25 293.3,430.28 293.3,430.28 C293.3,430.28 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 301.74,416.82 301.74,416.82 C301.74,416.82 292.58,424.16 292.58,424.16 C292.58,424.16 293.3,430.28 293.3,430.28 C293.3,430.28 298.75,430.25 298.75,430.25 C298.75,430.25 301.74,423.57 301.74,423.57 C301.74,423.57 306.45,419.97 306.45,419.97 C306.45,419.97 308.08,414.37 308.08,414.37 C308.08,414.37 310.3,409.7 310.3,409.7 C310.3,409.7 301.92,404.95 301.92,404.95z " keyTimes="0;0.1804511;0.2180451;0.2481203;0.2631579;0.2706767;0.2781955;0.2857143;0.3157895;0.3308271;0.3533835;0.3834586;0.406015;0.4135338;0.4210526;0.4511278;0.4736842;0.4887218;0.4962406;1" values="M306.79 419.97 C306.79,419.97 306.83,419.98 306.83,419.98 C306.83,419.98 306.8,419.97 306.8,419.97 C306.8,419.97 306.78,420 306.78,420 C306.78,420 306.8,420 306.8,420 C306.8,420 306.86,420 306.86,420 C306.86,420 306.95,419.93 306.95,419.93 C306.95,419.93 306.86,419.96 306.86,419.96 C306.86,419.96 306.84,420.21 306.84,420.21 C306.84,420.21 306.89,420.1 306.89,420.1 C306.89,420.1 306.83,420.1 306.83,420.1 C306.83,420.1 306.5,420.99 306.83,420.17 C307.17,419.36 306.69,420.75 306.69,419.9 C306.69,419.04 306.89,420.14 306.89,420.14 C306.89,420.14 306.93,420.01 306.93,420.01 C306.93,420.01 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 302.88,423.24 302.88,423.24 C302.88,423.24 302.6,423.2 302.6,423.2 C302.6,423.2 302.79,423.22 302.79,423.22 C302.79,423.22 302.47,423.18 302.47,423.18 C302.47,423.18 302.62,423.19 302.62,423.19 C302.62,423.19 302.53,423.17 302.53,423.17 C302.53,423.17 302.43,423.36 302.43,423.18 C302.43,422.99 302.57,423.16 302.57,423.16 C302.57,423.16 302.4,423.16 302.4,423.16 C302.4,423.16 302.71,423.1 302.71,423.1 C302.71,423.1 302.68,423.07 302.68,423.07 C302.68,423.07 302.76,423.09 302.76,423.09 C302.76,423.09 302.66,423.2 302.66,423.2 C302.66,423.2 302.71,423.14 302.71,423.14 C302.71,423.14 302.75,423.12 302.75,423.12 C302.75,423.12 302.75,423.18 302.75,423.18 C302.75,423.18 302.53,423.22 302.53,423.22 C302.53,423.22 306.79,419.98 306.79,419.98 C306.79,419.98 306.77,419.98 306.77,419.98 C306.77,419.98 306.8,419.98 306.8,419.98 C306.8,419.98 306.77,419.98 306.77,419.98 C306.77,419.98 306.79,419.98 306.79,419.98 C306.79,419.98 306.79,419.97 306.79,419.97z ;M306.79 419.97 C306.79,419.97 306.83,419.98 306.83,419.98 C306.83,419.98 306.8,419.97 306.8,419.97 C306.8,419.97 306.78,420 306.78,420 C306.78,420 306.8,420 306.8,420 C306.8,420 306.86,420 306.86,420 C306.86,420 306.95,419.93 306.95,419.93 C306.95,419.93 306.86,419.96 306.86,419.96 C306.86,419.96 306.84,420.21 306.84,420.21 C306.84,420.21 306.89,420.1 306.89,420.1 C306.89,420.1 306.83,420.1 306.83,420.1 C306.83,420.1 306.5,420.99 306.83,420.17 C307.17,419.36 306.69,420.75 306.69,419.9 C306.69,419.04 306.89,420.14 306.89,420.14 C306.89,420.14 306.93,420.01 306.93,420.01 C306.93,420.01 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 302.88,423.24 302.88,423.24 C302.88,423.24 302.6,423.2 302.6,423.2 C302.6,423.2 302.79,423.22 302.79,423.22 C302.79,423.22 302.47,423.18 302.47,423.18 C302.47,423.18 302.62,423.19 302.62,423.19 C302.62,423.19 302.53,423.17 302.53,423.17 C302.53,423.17 302.43,423.36 302.43,423.18 C302.43,422.99 302.57,423.16 302.57,423.16 C302.57,423.16 302.4,423.16 302.4,423.16 C302.4,423.16 302.71,423.1 302.71,423.1 C302.71,423.1 302.68,423.07 302.68,423.07 C302.68,423.07 302.76,423.09 302.76,423.09 C302.76,423.09 302.66,423.2 302.66,423.2 C302.66,423.2 302.71,423.14 302.71,423.14 C302.71,423.14 302.75,423.12 302.75,423.12 C302.75,423.12 302.75,423.18 302.75,423.18 C302.75,423.18 302.53,423.22 302.53,423.22 C302.53,423.22 306.79,419.98 306.79,419.98 C306.79,419.98 306.77,419.98 306.77,419.98 C306.77,419.98 306.8,419.98 306.8,419.98 C306.8,419.98 306.77,419.98 306.77,419.98 C306.77,419.98 306.79,419.98 306.79,419.98 C306.79,419.98 306.79,419.97 306.79,419.97z ;M310.92 429.74 C310.92,429.74 310.97,429.75 310.97,429.75 C310.97,429.75 310.93,429.74 310.93,429.74 C310.93,429.74 310.91,429.77 310.91,429.77 C310.91,429.77 310.94,429.77 310.94,429.77 C310.94,429.77 310.99,429.77 310.99,429.77 C310.99,429.77 311.09,429.7 311.09,429.7 C311.09,429.7 310.99,429.73 310.99,429.73 C310.99,429.73 310.9,434.91 310.9,434.91 C310.9,434.91 312.25,433.8 312.25,433.8 C312.25,433.8 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 303.93,428.18 303.93,428.18 C303.93,428.18 303.66,428.14 303.66,428.14 C303.66,428.14 303.84,428.16 303.84,428.16 C303.84,428.16 303.52,428.11 303.52,428.11 C303.52,428.11 303.67,428.12 303.67,428.12 C303.67,428.12 303.58,428.1 303.58,428.1 C303.58,428.1 303.49,428.3 303.49,428.11 C303.49,427.93 303.63,428.09 303.63,428.09 C303.63,428.09 303.45,428.1 303.45,428.1 C303.45,428.1 303.76,428.04 303.76,428.04 C303.76,428.04 303.73,428 303.73,428 C303.73,428 303.69,427.98 303.69,427.98 C303.69,427.98 303.71,428.13 303.71,428.13 C303.71,428.13 303.76,428.08 303.76,428.08 C303.76,428.08 303.8,428.06 303.8,428.06 C303.8,428.06 303.8,428.11 303.8,428.11 C303.8,428.11 303.58,428.16 303.58,428.16 C303.58,428.16 310.92,429.75 310.92,429.75 C310.92,429.75 310.91,429.75 310.91,429.75 C310.91,429.75 310.93,429.75 310.93,429.75 C310.93,429.75 310.9,429.75 310.9,429.75 C310.9,429.75 310.93,429.75 310.93,429.75 C310.93,429.75 310.92,429.74 310.92,429.74z ;M299.65 434.12 C299.65,434.12 299.7,434.13 299.7,434.13 C299.7,434.13 299.66,434.11 299.66,434.11 C299.66,434.11 299.64,434.14 299.64,434.14 C299.64,434.14 299.66,434.14 299.66,434.14 C299.66,434.14 299.72,434.15 299.72,434.15 C299.72,434.15 299.81,434.08 299.81,434.08 C299.81,434.08 299.72,434.11 299.72,434.11 C299.72,434.11 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 300.06,430.31 300.06,430.31 C300.06,430.31 299.78,430.27 299.78,430.27 C299.78,430.27 299.96,430.29 299.96,430.29 C299.96,430.29 299.65,430.25 299.65,430.25 C299.65,430.25 299.8,430.25 299.8,430.25 C299.8,430.25 299.7,430.24 299.7,430.24 C299.7,430.24 299.61,430.43 299.61,430.25 C299.61,430.06 299.75,430.22 299.75,430.22 C299.75,430.22 299.57,430.23 299.57,430.23 C299.57,430.23 299.89,430.17 299.89,430.17 C299.89,430.17 299.85,430.13 299.85,430.13 C299.85,430.13 299.82,430.12 299.82,430.12 C299.82,430.12 299.83,430.26 299.83,430.26 C299.83,430.26 299.89,430.21 299.89,430.21 C299.89,430.21 299.93,430.19 299.93,430.19 C299.93,430.19 299.93,430.25 299.93,430.25 C299.93,430.25 299.7,430.29 299.7,430.29 C299.7,430.29 299.65,434.13 299.65,434.13 C299.65,434.13 299.64,434.13 299.64,434.13 C299.64,434.13 299.66,434.13 299.66,434.13 C299.66,434.13 299.63,434.13 299.63,434.13 C299.63,434.13 299.65,434.13 299.65,434.13 C299.65,434.13 299.65,434.12 299.65,434.12z ;M292.83 434.12 C292.83,434.12 292.81,434.11 292.81,434.11 C292.81,434.11 292.84,434.12 292.84,434.12 C292.84,434.12 292.82,434.15 292.82,434.15 C292.82,434.15 292.85,434.15 292.85,434.15 C292.85,434.15 294.61,434.08 294.61,434.08 C294.61,434.08 298.37,434.07 298.37,434.07 C298.37,434.07 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 298.47,430.31 298.47,430.31 C298.47,430.31 294.44,430.33 294.44,430.33 C294.44,430.33 292.89,430.31 292.89,430.31 C292.89,430.31 292.69,430.25 292.69,430.25 C292.69,430.25 292.72,430.28 292.72,430.28 C292.72,430.28 292.63,430.26 292.63,430.26 C292.63,430.26 292.65,430.43 292.65,430.25 C292.65,430.06 292.56,430.15 292.56,430.15 C292.56,430.15 292.61,430.23 292.61,430.23 C292.61,430.23 292.93,430.17 292.93,430.17 C292.93,430.17 292.89,430.13 292.89,430.13 C292.89,430.13 292.85,430.12 292.85,430.12 C292.85,430.12 292.87,430.26 292.87,430.26 C292.87,430.26 292.93,430.21 292.93,430.21 C292.93,430.21 292.96,430.19 292.96,430.19 C292.96,430.19 292.96,430.25 292.96,430.25 C292.96,430.25 292.77,430.22 292.77,430.22 C292.77,430.22 292.83,434.13 292.83,434.13 C292.83,434.13 292.82,434.13 292.82,434.13 C292.82,434.13 292.84,434.13 292.84,434.13 C292.84,434.13 292.81,434.13 292.81,434.13 C292.81,434.13 292.83,434.13 292.83,434.13 C292.83,434.13 292.83,434.12 292.83,434.12z ;M286.91 434.04 C286.91,434.04 286.89,434.02 286.89,434.02 C286.89,434.02 286.92,434.03 286.92,434.03 C286.92,434.03 286.9,434.06 286.9,434.06 C286.9,434.06 286.92,434.06 286.92,434.06 C286.92,434.06 294.61,434.08 294.61,434.08 C294.61,434.08 298.39,434.03 298.39,434.03 C298.39,434.03 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 298.47,430.31 298.47,430.31 C298.47,430.31 294.44,430.33 294.44,430.33 C294.44,430.33 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 291.91,428.68 291.91,428.68 C291.91,428.68 291.82,428.67 291.82,428.67 C291.82,428.67 291.88,428.65 291.88,428.46 C291.88,428.28 291.78,428.37 291.78,428.37 C291.78,428.37 291.84,428.44 291.84,428.44 C291.84,428.44 292.15,428.39 292.15,428.39 C292.15,428.39 292.12,428.35 292.12,428.35 C292.12,428.35 292.08,428.33 292.08,428.33 C292.08,428.33 292.1,428.48 292.1,428.48 C292.1,428.48 292.15,428.42 292.15,428.42 C292.15,428.42 292.19,428.41 292.19,428.41 C292.19,428.41 292.19,428.46 292.19,428.46 C292.19,428.46 291.97,428.51 291.97,428.51 C291.97,428.51 287.14,434.07 287.14,434.07 C287.14,434.07 286.89,434.05 286.89,434.05 C286.89,434.05 286.92,434.05 286.92,434.05 C286.92,434.05 286.89,434.05 286.89,434.05 C286.89,434.05 286.91,434.05 286.91,434.05 C286.91,434.05 286.91,434.04 286.91,434.04z ;M286.7 429.47 C286.7,429.47 286.88,429.37 286.88,429.37 C286.88,429.37 286.52,429.45 286.52,429.45 C286.52,429.45 286.83,429.85 286.83,429.85 C286.83,429.85 286.14,434.18 286.14,434.18 C286.14,434.18 294.61,434.08 294.61,434.08 C294.61,434.08 298.37,434.08 298.37,434.08 C298.37,434.08 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 298.38,430.31 298.38,430.31 C298.38,430.31 294.56,430.33 294.56,430.33 C294.56,430.33 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 291.99,426.42 291.99,426.42 C291.99,426.42 291.87,426.34 291.87,426.34 C291.87,426.34 292.01,426.25 292.01,426.07 C292.01,425.88 292.05,425.99 292.05,425.99 C292.05,425.99 291.97,425.95 291.97,425.95 C291.97,425.95 292.39,425.98 292.39,425.98 C292.39,425.98 292.27,426.05 292.27,426.05 C292.27,426.05 292.35,425.99 292.35,425.99 C292.35,425.99 292.32,426 292.32,426 C292.32,426 292.4,426 292.4,426 C292.4,426 292.4,426.06 292.4,426.06 C292.4,426.06 292.39,426.05 292.39,426.05 C292.39,426.05 292.62,426.45 292.62,426.45 C292.62,426.45 286.78,429.41 286.78,429.41 C286.78,429.41 286.55,429.2 286.55,429.2 C286.55,429.2 286.62,429.38 286.62,429.38 C286.62,429.38 286.51,429.44 286.51,429.44 C286.51,429.44 286.46,429.37 286.46,429.37 C286.46,429.37 286.7,429.47 286.7,429.47z ;M286.5 424.9 C286.5,424.9 286.87,424.72 286.87,424.72 C286.87,424.72 286.13,424.87 286.13,424.87 C286.13,424.87 286.76,425.64 286.76,425.64 C286.76,425.64 285.37,434.3 285.37,434.3 C285.37,434.3 294.63,434.09 294.63,434.09 C294.63,434.09 298.37,434.09 298.37,434.09 C298.37,434.09 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 298.36,430.31 298.36,430.31 C298.36,430.31 294.59,430.33 294.59,430.33 C294.59,430.33 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.06,424.16 292.06,424.16 C292.06,424.16 291.91,424.01 291.91,424.01 C291.91,424.01 292.13,423.86 292.13,423.68 C292.13,423.49 292.32,423.6 292.32,423.6 C292.32,423.6 292.1,423.46 292.1,423.46 C292.1,423.46 292.62,423.57 292.62,423.57 C292.62,423.57 292.43,423.75 292.43,423.75 C292.43,423.75 292.62,423.64 292.62,423.64 C292.62,423.64 292.54,423.53 292.54,423.53 C292.54,423.53 292.65,423.57 292.65,423.57 C292.65,423.57 292.62,423.72 292.62,423.72 C292.62,423.72 292.58,423.64 292.58,423.64 C292.58,423.64 293.27,424.39 293.27,424.39 C293.27,424.39 286.43,424.75 286.43,424.75 C286.43,424.75 286.2,424.35 286.2,424.35 C286.2,424.35 286.31,424.72 286.31,424.72 C286.31,424.72 286.13,424.83 286.13,424.83 C286.13,424.83 286.02,424.68 286.02,424.68 C286.02,424.68 286.5,424.9 286.5,424.9z ;M285.53 417.93 C285.53,417.93 285.61,418.01 285.61,418.01 C285.61,418.01 285.39,417.97 285.39,417.97 C285.39,417.97 285.68,418.12 285.68,418.12 C285.68,418.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.61,434.08 294.61,434.08 C294.61,434.08 298.38,434.11 298.38,434.11 C298.38,434.11 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 298.71,430.31 298.71,430.31 C298.71,430.31 293.3,430.31 293.3,430.31 C293.3,430.31 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.26,417.75 291.26,417.56 C291.26,417.38 291.34,417.38 291.34,417.38 C291.34,417.38 291.45,417.54 291.45,417.54 C291.45,417.54 291.21,417.5 291.21,417.5 C291.21,417.5 291.32,417.45 291.32,417.45 C291.32,417.45 291.28,417.51 291.28,417.51 C291.28,417.51 291.5,417.56 291.5,417.56 C291.5,417.56 291.52,417.54 291.52,417.54 C291.52,417.54 291.45,417.6 291.45,417.6 C291.45,417.6 291.43,417.67 291.43,417.67 C291.43,417.67 291.41,417.89 291.41,417.89 C291.41,417.89 291.24,417.95 291.24,417.95 C291.24,417.95 285.98,417.86 285.98,417.86 C285.98,417.86 286.02,417.69 286.02,417.69 C286.02,417.69 285.92,417.77 285.92,417.77 C285.92,417.77 285.81,417.62 285.81,417.62 C285.81,417.62 285.53,417.93 285.53,417.93z ;M284.93 404.18 C284.93,404.18 281.14,411.97 281.14,411.97 C281.14,411.97 273.88,412.04 273.88,412.04 C273.88,412.04 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.61,434.08 294.61,434.08 C294.61,434.08 298.36,434.08 298.36,434.08 C298.36,434.08 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 298.35,430.31 298.35,430.31 C298.35,430.31 294.59,430.32 294.59,430.32 C294.59,430.32 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 291.91,415.81 291.91,415.81 C291.91,415.81 291.8,415.82 291.8,415.82 C291.8,415.82 291.88,415.73 291.88,415.73 C291.88,415.73 291.9,415.66 291.9,415.66 C291.9,415.66 291.8,415.65 291.8,415.65 C291.8,415.65 291.73,415.73 291.73,415.73 C291.73,415.73 291.87,415.58 291.87,415.58 C291.87,415.58 291.87,415.71 291.87,415.71 C291.87,415.71 291.83,415.72 291.83,415.72 C291.83,415.72 291.82,415.71 291.82,415.71 C291.82,415.71 291.66,414.92 291.66,414.92 C291.66,414.92 291.45,413.38 291.45,413.38 C291.45,413.38 291.09,411.81 291.09,411.81 C291.09,411.81 291.05,411.77 291.05,411.77 C291.05,411.77 289.08,410.26 289.08,410.26 C289.08,410.26 284.93,404.18 284.93,404.18z ;M298.66 404.21 C298.66,404.21 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.61,434.09 294.61,434.09 C294.61,434.09 298.35,434.08 298.35,434.08 C298.35,434.08 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 298.76,430.32 298.76,430.32 C298.76,430.32 294.62,430.33 294.62,430.33 C294.62,430.33 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 300.75,413.19 300.75,413.19 C300.75,413.19 300.74,413.2 300.74,413.2 C300.74,413.2 300.68,413.28 300.68,413.28 C300.68,413.28 300.74,413.15 300.74,413.15 C300.74,413.15 300.76,413.19 300.76,413.19 C300.76,413.19 300.77,413.17 300.77,413.17 C300.77,413.17 303.55,406.44 303.55,406.44 C303.55,406.44 302.85,404.47 302.85,404.47 C302.85,404.47 301.29,403.47 301.29,403.47 C301.29,403.47 301.18,403.32 301.18,403.32 C301.18,403.32 298.66,404.21 298.66,404.21z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.61,434.07 294.61,434.07 C294.61,434.07 298.36,434.07 298.36,434.07 C298.36,434.07 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 298.73,430.31 298.73,430.31 C298.73,430.31 293.3,430.33 293.3,430.33 C293.3,430.33 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 302.59,416.02 302.59,416.02 C302.59,416.02 302.55,415.98 302.55,415.98 C302.55,415.98 302.63,415.99 302.63,415.99 C302.63,415.99 306.67,409.55 306.67,409.55 C306.67,409.55 306.65,409.61 306.65,409.61 C306.65,409.61 306.59,409.55 306.59,409.55 C306.59,409.55 306.69,409.72 306.69,409.72 C306.69,409.72 306.58,409.57 306.58,409.57 C306.58,409.57 301.92,404.95 301.92,404.95z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.61,434.09 294.61,434.09 C294.61,434.09 298.36,434.09 298.36,434.09 C298.36,434.09 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 306.29,420.1 306.29,420.1 C306.29,420.1 301.7,423.39 301.7,423.39 C301.7,423.39 298.38,430.31 298.38,430.31 C298.38,430.31 293.4,430.32 293.4,430.32 C293.4,430.32 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 302.63,417.02 302.63,417.02 C302.63,417.02 302.61,416.97 302.61,416.97 C302.61,416.97 302.63,416.9 302.63,416.9 C302.63,416.9 307.12,415.55 307.12,415.55 C307.12,415.55 307.51,415.47 307.51,415.47 C307.51,415.47 307.52,415.47 307.52,415.47 C307.52,415.47 309.01,412.56 309.01,412.56 C309.01,412.56 310.3,409.7 310.3,409.7 C310.3,409.7 301.92,404.95 301.92,404.95z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.6,434.08 294.6,434.08 C294.6,434.08 298.37,434.07 298.37,434.07 C298.37,434.07 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 306.05,420.2 306.05,420.2 C306.05,420.2 301.63,423.29 301.63,423.29 C301.63,423.29 298.57,430.33 298.57,430.33 C298.57,430.33 293.35,430.32 293.35,430.32 C293.35,430.32 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 301.74,416.82 301.74,416.82 C301.74,416.82 297.55,418.67 297.55,418.67 C297.55,418.67 300.2,418.16 300.2,418.16 C300.2,418.16 306.72,417.16 306.72,417.16 C306.72,417.16 307.56,417.29 307.56,417.29 C307.56,417.29 307.59,417.33 307.59,417.33 C307.59,417.33 308.54,413.47 308.54,413.47 C308.54,413.47 306.71,408.22 306.71,408.22 C306.71,408.22 301.92,404.95 301.92,404.95z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.62,434.09 294.62,434.09 C294.62,434.09 298.35,434.08 298.35,434.08 C298.35,434.08 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 305.8,420.3 305.8,420.3 C305.8,420.3 301.55,423.2 301.55,423.2 C301.55,423.2 298.74,430.31 298.74,430.31 C298.74,430.31 293.34,430.32 293.34,430.32 C293.34,430.32 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 301.74,416.82 301.74,416.82 C301.74,416.82 297.55,418.67 297.55,418.67 C297.55,418.67 300.2,418.16 300.2,418.16 C300.2,418.16 306.32,418.77 306.32,418.77 C306.32,418.77 307.34,417.78 307.34,417.78 C307.34,417.78 307.74,418.52 307.74,418.52 C307.74,418.52 308.08,414.37 308.08,414.37 C308.08,414.37 310.3,409.7 310.3,409.7 C310.3,409.7 301.92,404.95 301.92,404.95z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.6,434.09 294.6,434.09 C294.6,434.09 298.35,434.08 298.35,434.08 C298.35,434.08 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 305.98,420.3 305.98,420.3 C305.98,420.3 301.72,423.59 301.72,423.59 C301.72,423.59 298.75,430.25 298.75,430.25 C298.75,430.25 293.3,430.28 293.3,430.28 C293.3,430.28 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 301.74,416.82 301.74,416.82 C301.74,416.82 297.55,418.67 297.55,418.67 C297.55,418.67 296.68,421.72 296.68,421.72 C296.68,421.72 300.57,423.18 300.57,423.18 C300.57,423.18 301.74,423.57 301.74,423.57 C301.74,423.57 306.45,419.97 306.45,419.97 C306.45,419.97 308.08,414.37 308.08,414.37 C308.08,414.37 310.3,409.7 310.3,409.7 C310.3,409.7 301.92,404.95 301.92,404.95z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.61,434.08 294.61,434.08 C294.61,434.08 298.36,434.09 298.36,434.09 C298.36,434.09 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 306.41,419.97 306.41,419.97 C306.41,419.97 301.7,423.64 301.7,423.64 C301.7,423.64 298.69,430.31 298.69,430.31 C298.69,430.31 294.56,430.33 294.56,430.33 C294.56,430.33 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 301.74,416.82 301.74,416.82 C301.74,416.82 292.58,424.16 292.58,424.16 C292.58,424.16 294.58,430.33 294.58,430.33 C294.58,430.33 298.38,430.31 298.38,430.31 C298.38,430.31 301.74,423.57 301.74,423.57 C301.74,423.57 306.45,419.97 306.45,419.97 C306.45,419.97 308.08,414.37 308.08,414.37 C308.08,414.37 310.3,409.7 310.3,409.7 C310.3,409.7 301.92,404.95 301.92,404.95z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 293.73,439.55 293.73,439.55 C293.73,439.55 298.46,439.54 298.46,439.54 C298.46,439.54 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 306.43,419.98 306.43,419.98 C306.43,419.98 301.75,423.57 301.75,423.57 C301.75,423.57 298.73,430.27 298.73,430.27 C298.73,430.27 293.72,430.3 293.72,430.3 C293.72,430.3 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 301.74,416.82 301.74,416.82 C301.74,416.82 292.58,424.16 292.58,424.16 C292.58,424.16 293.7,430.31 293.7,430.31 C293.7,430.31 298.74,430.26 298.74,430.26 C298.74,430.26 301.74,423.57 301.74,423.57 C301.74,423.57 306.45,419.97 306.45,419.97 C306.45,419.97 308.08,414.37 308.08,414.37 C308.08,414.37 310.3,409.7 310.3,409.7 C310.3,409.7 301.92,404.95 301.92,404.95z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 293.25,442.25 293.25,442.25 C293.25,442.25 298.5,442.3 298.5,442.3 C298.5,442.3 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 306.45,419.99 306.45,419.99 C306.45,419.99 301.77,423.53 301.77,423.53 C301.77,423.53 298.75,430.25 298.75,430.25 C298.75,430.25 293.3,430.28 293.3,430.28 C293.3,430.28 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 301.74,416.82 301.74,416.82 C301.74,416.82 292.58,424.16 292.58,424.16 C292.58,424.16 293.3,430.28 293.3,430.28 C293.3,430.28 298.75,430.25 298.75,430.25 C298.75,430.25 301.74,423.57 301.74,423.57 C301.74,423.57 306.45,419.97 306.45,419.97 C306.45,419.97 308.08,414.37 308.08,414.37 C308.08,414.37 310.3,409.7 310.3,409.7 C310.3,409.7 301.92,404.95 301.92,404.95z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 293.25,442.25 293.25,442.25 C293.25,442.25 298.5,442.3 298.5,442.3 C298.5,442.3 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 306.45,419.99 306.45,419.99 C306.45,419.99 301.77,423.53 301.77,423.53 C301.77,423.53 298.75,430.25 298.75,430.25 C298.75,430.25 293.3,430.28 293.3,430.28 C293.3,430.28 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 301.74,416.82 301.74,416.82 C301.74,416.82 292.58,424.16 292.58,424.16 C292.58,424.16 293.3,430.28 293.3,430.28 C293.3,430.28 298.75,430.25 298.75,430.25 C298.75,430.25 301.74,423.57 301.74,423.57 C301.74,423.57 306.45,419.97 306.45,419.97 C306.45,419.97 308.08,414.37 308.08,414.37 C308.08,414.37 310.3,409.7 310.3,409.7 C310.3,409.7 301.92,404.95 301.92,404.95z " keySplines="0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0 0 0 0" calcMode="spline"/>
                                                                    <clipPath id="_R_G_L_0_C_0">
                                                                        <path id="_R_G_L_0_C_0_P_0" fill-rule="nonzero"/>
                                                                    </clipPath>
                                                                    <animate attributeType="XML" attributeName="opacity" dur="2s" from="0" to="1" xlink:href="#time_group_edit_appointment_mobile"/>
                                                                </defs>
                                                                <g id="_R_G">
                                                                    <g id="_R_G_L_1_G" transform=" translate(127.638, 127.945) scale(3.37139, 3.37139) translate(-297.638, -420.945)">
                                                                        <g clip-path="url(#_R_G_L_1_C_0)">
                                                                            <path id="_R_G_L_1_G_G_0_D_0_P_0" class="bpa-front-loader-cl-primary" fill-opacity="1" fill-rule="nonzero" d=" M328 398.61 C328,398.61 328,446.23 328,446.23 C328,449.7 325.2,452.5 321.75,452.5 C321.75,452.5 274.25,452.5 274.25,452.5 C270.8,452.5 268,449.7 268,446.23 C268,446.23 268,398.61 268,398.61 C268,395.15 270.8,392.35 274.25,392.35 C274.25,392.35 283.46,392.26 283.46,392.26 C283.46,392.26 283.46,390.38 283.46,390.38 C283.46,389.76 284.08,388.5 285.33,388.5 C286.58,388.5 287.21,389.75 287.21,390.38 C287.21,390.38 287.21,397.89 287.21,397.89 C287.21,398.53 286.59,399.78 285.33,399.78 C284.08,399.78 283.46,398.53 283.46,397.9 C283.46,397.9 283.46,396.02 283.46,396.02 C283.46,396.02 275.5,396.1 275.5,396.1 C273.43,396.1 271.75,397.79 271.75,399.86 C271.75,399.86 271.75,444.98 271.75,444.98 C271.75,447.06 273.43,448.74 275.5,448.74 C275.5,448.74 320.5,448.74 320.5,448.74 C322.57,448.74 324.25,447.06 324.25,444.98 C324.25,444.98 324.25,399.86 324.25,399.86 C324.25,397.79 322.57,396.1 320.5,396.1 C320.5,396.1 312.62,396.1 312.62,396.1 C312.62,396.1 312.63,397.06 312.63,397.99 C312.63,398.61 312,399.86 310.75,399.86 C309.5,399.86 308.88,398.61 308.88,397.98 C308.88,397.98 308.87,396.1 308.87,396.1 C308.87,396.1 301.88,396.1 301.88,396.1 C300.84,396.1 300,395.26 300,394.23 C300,393.19 300.84,392.35 301.88,392.35 C301.88,392.35 308.87,392.35 308.87,392.35 C308.87,392.35 308.87,390.47 308.87,390.47 C308.87,389.83 309.5,388.5 310.75,388.5 C312,388.5 312.62,389.84 312.62,390.47 C312.62,390.47 312.62,392.35 312.62,392.35 C312.62,392.35 321.75,392.35 321.75,392.35 C325.2,392.35 328,395.15 328,398.61z "/>
                                                                        </g>
                                                                    </g>
                                                                    <g id="_R_G_L_0_G" transform=" translate(125.555, 126.412) scale(3.37139, 3.37139) translate(-297.638, -420.945)">
                                                                        <g clip-path="url(#_R_G_L_0_C_0)">
                                                                            <path id="_R_G_L_0_G_G_0_D_0_P_0" class="bpa-front-loader-cl-primary" fill-opacity="1" fill-rule="nonzero" d=" M305.86 420.29 C305.86,420.29 307.11,419.04 307.11,415.28 C307.11,409.01 303.36,407.76 298.36,407.76 C298.36,407.76 287.11,407.76 287.11,407.76 C287.11,407.76 287.11,434.08 287.11,434.08 C287.11,434.08 294.61,434.08 294.61,434.08 C294.61,434.08 294.61,441.6 294.61,441.6 C294.61,441.6 298.36,441.6 298.36,441.6 C298.36,441.6 298.36,434.08 298.36,434.08 C302.71,434.08 305.73,434.08 307.98,431.3 C309.07,429.95 309.62,428.24 309.61,426.5 C309.61,425.58 309.51,424.67 309.3,424.05 C308.73,422.65 308.36,421.55 305.86,420.29z  M302.11 430.32 C302.11,430.32 298.36,430.32 298.36,430.32 C298.36,430.32 298.36,426.56 298.36,426.56 C298.36,424.48 300.03,422.8 302.11,422.8 C304.13,422.8 305.86,424.43 305.86,426.56 C305.86,428.78 304.03,430.32 302.11,430.32z  M299.07 419.95 C298.43,420.26 297.82,420.63 297.26,421.05 C295.87,422.1 294.61,423.58 294.61,426.56 C294.61,426.56 294.61,430.32 294.61,430.32 C294.61,430.32 290.86,430.32 290.86,430.32 C290.86,430.32 290.86,411.52 290.86,411.52 C290.86,411.52 298.36,411.52 298.36,411.52 C301.35,411.52 303.36,412.77 303.36,415.28 C303.36,417.58 301.65,418.68 299.07,419.95z "/>
                                                                        </g>
                                                                    </g>
                                                                </g>
                                                                <g id="time_group_edit_appointment_mobile"/>
                                                            </svg>
                                                        </div>
                                                    </div> 
                                                </div>                                                 
                                                <div v-if="recurring_appointment_loader_edit != true" class="bpa-form-row">
                                                    <el-row>
                                                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                            <el-form ref="recurring_edit_form"  label-position="top" @submit.native.prevent>
                                                                <div class="bpa-form-body-row">
                                                                    <el-row>
                                                                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-form-body-row-item">
                                                                            <el-form-item prop="recurring_edit_date">
                                                                                <template #label>
                                                                                    <span class="bpa-form-label">{{recurring_edit_appointment_date_label}}</span>
                                                                                </template>   
                                                                                <el-date-picker @change="change_recurring_start_date($event,'edit')" class="bpa-front-form-control bpa-front-form-control--date-picker" type="date" format="<?php echo esc_html($bookingpress_common_date_format); ?>" placeholder="<?php echo esc_html($bookingpress_common_date_format); ?>" v-model="recurring_edit_date" name="appointment_booked_date" popper-class="bpa-custom-datepicker bpa-custom-recurring-datepicker" type="date" :clearable="false" :picker-options="recurring_edit_pickerOptions" value-format="yyyy-MM-dd"></el-date-picker>                                                                                                             
                                                                            </el-form-item> 
                                                                        </el-col>	
                                                                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-form-body-row-item">
                                                                            <el-form-item prop="happy_hour_label">
                                                                                <template #label>
                                                                                    <span class="bpa-form-label">{{recurring_start_time_label}}</span>
                                                                                </template>   
                                                                                <div class="field bpa-recurring-appointment-head-row">
                                                                                    <el-select :disabled="recurring_appointment_time_loader_edit" :loading="recurring_appointment_time_loader_edit" class="bpa-front-form-control" :placeholder="recurring_start_time_label" v-model="recurring_edit_time" filterable popper-class="bpa-fm--service__advance-options-popper bpa-fm--service__advance-options-recurring-popper bpa-el-select--is-with-modal" @Change="bookingpress_set_recurring_start_time($event,recurring_edit_appointment_time_slot,'edit')">                                                                
                                                                                        <el-option-group :class="hide_time_slot_grouping == true ? 'bpa-do-not-group-timing' : ''" v-for="appointment_time_slot_data in recurring_edit_appointment_time_slot" :key="appointment_time_slot_data.timeslot_label" :label="appointment_time_slot_data.timeslot_label" >
                                                                                            <el-option v-for="appointment_time in appointment_time_slot_data.timeslots" :label="bookingpress_set_recurring_appointment_timeslot_formate(appointment_time)" :value="appointment_time.store_start_time" :disabled="( appointment_time.is_disabled || appointment_time.max_capacity == 0 || appointment_time.is_booked == 1 )">
                                                                                                <span>{{ bookingpress_set_recurring_appointment_timeslot_formate(appointment_time) }}</span>
                                                                                            </el-option>	
                                                                                        </el-option-group>                                                                
                                                                                    </el-select>
                                                                                </div>                                                        
                                                                            </el-form-item> 
                                                                        </el-col>	                                                								
                                                                    </el-row>
                                                                </div>
                                                            </el-form>
                                                        </el-col>
                                                    </el-row>
                                                </div>
                                            </el-container>
                                        </div>
                                        <div class="bpa-edit-appointment-footer bpa-edit-appointment-item">
                                            <div class="bpa-hw-right-btn-group">
                                                <el-button @click="close_recurring_modal()" class="bpa-front-btn bpa-btn bpa-btn__small"><?php esc_html_e( 'Cancel', 'bookingpress-recurring-appointments' ); ?></el-button>
                                                <el-button @click="save_edit_recurring_data()" class="bpa-front-btn bpa-btn bpa-btn__small bpa-btn--primary bpa-front-btn--primary"><?php esc_html_e( 'Done', 'bookingpress-recurring-appointments' ); ?></el-button>
                                            </div>
                                        </div>
                                    </div>  
                                    <?php } ?>
                                </div>
                            </el-col>  
                        </el-row>
                    </div>
                </div>
            </div>
            <?php if($device_view!='mobile'){ ?>
                <el-dialog id="recurring_front_modal" modal-append-to-body="false" close-on-click-modal="false" custom-class="bpa-dialog bpa-dailog__small bpa-dialog--add-recurring-edit" :visible.sync="recurring_open_edit_popup" close-on-press-escape="false">
                    <div class="bpa-dialog-heading">
                        <el-row type="flex">
                            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">                            
                                <h1 class="bpa-page-heading">{{recurring_edit_appointment_title}}</h1>
                            </el-col>
                        </el-row>
                    </div>
                    <div class="bpa-dialog-body">
                        <el-container class="bpa-grid-list-container bpa-add-categpry-container">                            
                            <div v-if="recurring_appointment_loader_edit == true" class="bpa-recurring-appointment-loader-edit">
                                <div class="bpa-front-loader-container">
                                    <div class="bpa-front-loader">
                                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid meet" width="256" height="256" viewBox="0 0 256 256" style="width:100%;height:100%">
                                            <defs>
                                                <animate repeatCount="indefinite" dur="2.2166667s" begin="0s" xlink:href="#_R_G_L_1_C_0_P_0" fill="freeze" attributeName="d" attributeType="XML" from="M294.33 386.7 C294.33,386.7 291.96,386.7 291.96,386.7 C291.96,386.7 291.67,391.89 291.67,391.89 C291.67,391.89 292.41,396.34 292.41,396.34 C292.41,396.34 292.11,401.09 292.11,401.09 C292.11,401.09 295.67,401.09 295.67,401.09 C295.67,401.09 295.82,396.05 295.82,396.05 C295.82,396.05 295.97,391.75 295.97,391.75 C295.97,391.75 294.33,386.7 294.33,386.7z " to="M330.93 386.68 C330.93,386.68 263.6,386.68 263.6,386.68 C263.6,386.68 265.82,453.13 265.82,453.13 C265.82,453.13 295.78,456.98 295.78,456.98 C295.78,456.98 295.89,452.83 295.89,452.83 C295.89,452.83 296.26,452.98 296.26,452.98 C296.26,452.98 295.78,457.13 295.78,457.13 C295.78,457.13 329.59,454.91 329.59,454.91 C329.59,454.91 330.93,386.68 330.93,386.68z " keyTimes="0;0.5037594;0.5263158;0.5789474;0.6691729;0.6992481;0.7593985;0.7669173;1" values="M294.33 386.7 C294.33,386.7 291.96,386.7 291.96,386.7 C291.96,386.7 291.67,391.89 291.67,391.89 C291.67,391.89 292.41,396.34 292.41,396.34 C292.41,396.34 292.11,401.09 292.11,401.09 C292.11,401.09 295.67,401.09 295.67,401.09 C295.67,401.09 295.82,396.05 295.82,396.05 C295.82,396.05 295.97,391.75 295.97,391.75 C295.97,391.75 294.33,386.7 294.33,386.7z ;M294.33 386.7 C294.33,386.7 291.96,386.7 291.96,386.7 C291.96,386.7 291.67,391.89 291.67,391.89 C291.67,391.89 292.41,396.34 292.41,396.34 C292.41,396.34 292.11,401.09 292.11,401.09 C292.11,401.09 295.67,401.09 295.67,401.09 C295.67,401.09 295.82,396.05 295.82,396.05 C295.82,396.05 295.97,391.75 295.97,391.75 C295.97,391.75 294.33,386.7 294.33,386.7z ;M303.49 386.7 C303.49,386.7 284.88,386.7 284.88,386.7 C284.88,386.7 284.88,402.72 284.88,402.72 C284.88,402.72 293.41,402.87 293.41,402.87 C293.41,402.87 293.07,405.24 293.07,405.24 C293.07,405.24 296.63,405.24 296.63,405.24 C296.63,405.24 296.82,402.57 296.82,402.57 C296.82,402.57 304.49,401.98 304.49,401.98 C304.49,401.98 303.49,386.7 303.49,386.7z ;M330.97 386.7 C330.97,386.7 263.64,386.7 263.64,386.7 C263.64,386.7 265.56,398.12 265.56,398.12 C265.56,398.12 266.75,407.02 266.75,407.02 C266.75,407.02 294.78,405.83 294.78,405.83 C294.78,405.83 298.34,405.83 298.34,405.83 C298.34,405.83 332.75,406.72 332.75,406.72 C332.75,406.72 332.45,399.46 332.45,399.46 C332.45,399.46 330.97,386.7 330.97,386.7z ;M330.97 386.7 C330.97,386.7 263.64,386.7 263.64,386.7 C263.64,386.7 265.56,442.32 265.56,442.32 C265.56,442.32 266.75,448.4 266.75,448.4 C266.75,448.4 283.8,447.51 283.8,447.51 C283.8,447.51 312.06,447.21 312.06,447.21 C312.06,447.21 332.75,448.1 332.75,448.1 C332.75,448.1 332.45,443.65 332.45,443.65 C332.45,443.65 330.97,386.7 330.97,386.7z ;M330.97 386.7 C330.97,386.7 263.64,386.7 263.64,386.7 C263.64,386.7 265.86,453.14 265.86,453.14 C265.86,453.14 276.98,456.11 276.98,456.11 C276.98,456.11 277.28,447.51 277.28,447.51 C277.28,447.51 319.47,447.81 319.47,447.81 C319.47,447.81 318.81,456.11 318.81,456.11 C318.81,456.11 329.63,454.92 329.63,454.92 C329.63,454.92 330.97,386.7 330.97,386.7z ;M330.93 386.68 C330.93,386.68 263.6,386.68 263.6,386.68 C263.6,386.68 265.82,453.13 265.82,453.13 C265.82,453.13 295.78,456.98 295.78,456.98 C295.78,456.98 295.63,448.83 295.63,448.83 C295.63,448.83 295.71,448.75 295.71,448.75 C295.71,448.75 295.78,457.13 295.78,457.13 C295.78,457.13 329.59,454.91 329.59,454.91 C329.59,454.91 330.93,386.68 330.93,386.68z ;M330.93 386.68 C330.93,386.68 263.6,386.68 263.6,386.68 C263.6,386.68 265.82,453.13 265.82,453.13 C265.82,453.13 295.78,456.98 295.78,456.98 C295.78,456.98 295.89,452.83 295.89,452.83 C295.89,452.83 296.26,452.98 296.26,452.98 C296.26,452.98 295.78,457.13 295.78,457.13 C295.78,457.13 329.59,454.91 329.59,454.91 C329.59,454.91 330.93,386.68 330.93,386.68z ;M330.93 386.68 C330.93,386.68 263.6,386.68 263.6,386.68 C263.6,386.68 265.82,453.13 265.82,453.13 C265.82,453.13 295.78,456.98 295.78,456.98 C295.78,456.98 295.89,452.83 295.89,452.83 C295.89,452.83 296.26,452.98 296.26,452.98 C296.26,452.98 295.78,457.13 295.78,457.13 C295.78,457.13 329.59,454.91 329.59,454.91 C329.59,454.91 330.93,386.68 330.93,386.68z " keySplines="0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0 0 0 0" calcMode="spline"/>
                                                <clipPath id="_R_G_L_1_C_0">
                                                    <path id="_R_G_L_1_C_0_P_0" fill-rule="nonzero"/>
                                                </clipPath>
                                                <animate repeatCount="indefinite" dur="2.2166667s" begin="0s" xlink:href="#_R_G_L_0_C_0_P_0" fill="freeze" attributeName="d" attributeType="XML" from="M306.79 419.97 C306.79,419.97 306.83,419.98 306.83,419.98 C306.83,419.98 306.8,419.97 306.8,419.97 C306.8,419.97 306.78,420 306.78,420 C306.78,420 306.8,420 306.8,420 C306.8,420 306.86,420 306.86,420 C306.86,420 306.95,419.93 306.95,419.93 C306.95,419.93 306.86,419.96 306.86,419.96 C306.86,419.96 306.84,420.21 306.84,420.21 C306.84,420.21 306.89,420.1 306.89,420.1 C306.89,420.1 306.83,420.1 306.83,420.1 C306.83,420.1 306.5,420.99 306.83,420.17 C307.17,419.36 306.69,420.75 306.69,419.9 C306.69,419.04 306.89,420.14 306.89,420.14 C306.89,420.14 306.93,420.01 306.93,420.01 C306.93,420.01 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 302.88,423.24 302.88,423.24 C302.88,423.24 302.6,423.2 302.6,423.2 C302.6,423.2 302.79,423.22 302.79,423.22 C302.79,423.22 302.47,423.18 302.47,423.18 C302.47,423.18 302.62,423.19 302.62,423.19 C302.62,423.19 302.53,423.17 302.53,423.17 C302.53,423.17 302.43,423.36 302.43,423.18 C302.43,422.99 302.57,423.16 302.57,423.16 C302.57,423.16 302.4,423.16 302.4,423.16 C302.4,423.16 302.71,423.1 302.71,423.1 C302.71,423.1 302.68,423.07 302.68,423.07 C302.68,423.07 302.76,423.09 302.76,423.09 C302.76,423.09 302.66,423.2 302.66,423.2 C302.66,423.2 302.71,423.14 302.71,423.14 C302.71,423.14 302.75,423.12 302.75,423.12 C302.75,423.12 302.75,423.18 302.75,423.18 C302.75,423.18 302.53,423.22 302.53,423.22 C302.53,423.22 306.79,419.98 306.79,419.98 C306.79,419.98 306.77,419.98 306.77,419.98 C306.77,419.98 306.8,419.98 306.8,419.98 C306.8,419.98 306.77,419.98 306.77,419.98 C306.77,419.98 306.79,419.98 306.79,419.98 C306.79,419.98 306.79,419.97 306.79,419.97z " to="M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 293.25,442.25 293.25,442.25 C293.25,442.25 298.5,442.3 298.5,442.3 C298.5,442.3 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 306.45,419.99 306.45,419.99 C306.45,419.99 301.77,423.53 301.77,423.53 C301.77,423.53 298.75,430.25 298.75,430.25 C298.75,430.25 293.3,430.28 293.3,430.28 C293.3,430.28 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 301.74,416.82 301.74,416.82 C301.74,416.82 292.58,424.16 292.58,424.16 C292.58,424.16 293.3,430.28 293.3,430.28 C293.3,430.28 298.75,430.25 298.75,430.25 C298.75,430.25 301.74,423.57 301.74,423.57 C301.74,423.57 306.45,419.97 306.45,419.97 C306.45,419.97 308.08,414.37 308.08,414.37 C308.08,414.37 310.3,409.7 310.3,409.7 C310.3,409.7 301.92,404.95 301.92,404.95z " keyTimes="0;0.1804511;0.2180451;0.2481203;0.2631579;0.2706767;0.2781955;0.2857143;0.3157895;0.3308271;0.3533835;0.3834586;0.406015;0.4135338;0.4210526;0.4511278;0.4736842;0.4887218;0.4962406;1" values="M306.79 419.97 C306.79,419.97 306.83,419.98 306.83,419.98 C306.83,419.98 306.8,419.97 306.8,419.97 C306.8,419.97 306.78,420 306.78,420 C306.78,420 306.8,420 306.8,420 C306.8,420 306.86,420 306.86,420 C306.86,420 306.95,419.93 306.95,419.93 C306.95,419.93 306.86,419.96 306.86,419.96 C306.86,419.96 306.84,420.21 306.84,420.21 C306.84,420.21 306.89,420.1 306.89,420.1 C306.89,420.1 306.83,420.1 306.83,420.1 C306.83,420.1 306.5,420.99 306.83,420.17 C307.17,419.36 306.69,420.75 306.69,419.9 C306.69,419.04 306.89,420.14 306.89,420.14 C306.89,420.14 306.93,420.01 306.93,420.01 C306.93,420.01 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 302.88,423.24 302.88,423.24 C302.88,423.24 302.6,423.2 302.6,423.2 C302.6,423.2 302.79,423.22 302.79,423.22 C302.79,423.22 302.47,423.18 302.47,423.18 C302.47,423.18 302.62,423.19 302.62,423.19 C302.62,423.19 302.53,423.17 302.53,423.17 C302.53,423.17 302.43,423.36 302.43,423.18 C302.43,422.99 302.57,423.16 302.57,423.16 C302.57,423.16 302.4,423.16 302.4,423.16 C302.4,423.16 302.71,423.1 302.71,423.1 C302.71,423.1 302.68,423.07 302.68,423.07 C302.68,423.07 302.76,423.09 302.76,423.09 C302.76,423.09 302.66,423.2 302.66,423.2 C302.66,423.2 302.71,423.14 302.71,423.14 C302.71,423.14 302.75,423.12 302.75,423.12 C302.75,423.12 302.75,423.18 302.75,423.18 C302.75,423.18 302.53,423.22 302.53,423.22 C302.53,423.22 306.79,419.98 306.79,419.98 C306.79,419.98 306.77,419.98 306.77,419.98 C306.77,419.98 306.8,419.98 306.8,419.98 C306.8,419.98 306.77,419.98 306.77,419.98 C306.77,419.98 306.79,419.98 306.79,419.98 C306.79,419.98 306.79,419.97 306.79,419.97z ;M306.79 419.97 C306.79,419.97 306.83,419.98 306.83,419.98 C306.83,419.98 306.8,419.97 306.8,419.97 C306.8,419.97 306.78,420 306.78,420 C306.78,420 306.8,420 306.8,420 C306.8,420 306.86,420 306.86,420 C306.86,420 306.95,419.93 306.95,419.93 C306.95,419.93 306.86,419.96 306.86,419.96 C306.86,419.96 306.84,420.21 306.84,420.21 C306.84,420.21 306.89,420.1 306.89,420.1 C306.89,420.1 306.83,420.1 306.83,420.1 C306.83,420.1 306.5,420.99 306.83,420.17 C307.17,419.36 306.69,420.75 306.69,419.9 C306.69,419.04 306.89,420.14 306.89,420.14 C306.89,420.14 306.93,420.01 306.93,420.01 C306.93,420.01 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 302.88,423.24 302.88,423.24 C302.88,423.24 302.6,423.2 302.6,423.2 C302.6,423.2 302.79,423.22 302.79,423.22 C302.79,423.22 302.47,423.18 302.47,423.18 C302.47,423.18 302.62,423.19 302.62,423.19 C302.62,423.19 302.53,423.17 302.53,423.17 C302.53,423.17 302.43,423.36 302.43,423.18 C302.43,422.99 302.57,423.16 302.57,423.16 C302.57,423.16 302.4,423.16 302.4,423.16 C302.4,423.16 302.71,423.1 302.71,423.1 C302.71,423.1 302.68,423.07 302.68,423.07 C302.68,423.07 302.76,423.09 302.76,423.09 C302.76,423.09 302.66,423.2 302.66,423.2 C302.66,423.2 302.71,423.14 302.71,423.14 C302.71,423.14 302.75,423.12 302.75,423.12 C302.75,423.12 302.75,423.18 302.75,423.18 C302.75,423.18 302.53,423.22 302.53,423.22 C302.53,423.22 306.79,419.98 306.79,419.98 C306.79,419.98 306.77,419.98 306.77,419.98 C306.77,419.98 306.8,419.98 306.8,419.98 C306.8,419.98 306.77,419.98 306.77,419.98 C306.77,419.98 306.79,419.98 306.79,419.98 C306.79,419.98 306.79,419.97 306.79,419.97z ;M310.92 429.74 C310.92,429.74 310.97,429.75 310.97,429.75 C310.97,429.75 310.93,429.74 310.93,429.74 C310.93,429.74 310.91,429.77 310.91,429.77 C310.91,429.77 310.94,429.77 310.94,429.77 C310.94,429.77 310.99,429.77 310.99,429.77 C310.99,429.77 311.09,429.7 311.09,429.7 C311.09,429.7 310.99,429.73 310.99,429.73 C310.99,429.73 310.9,434.91 310.9,434.91 C310.9,434.91 312.25,433.8 312.25,433.8 C312.25,433.8 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 303.93,428.18 303.93,428.18 C303.93,428.18 303.66,428.14 303.66,428.14 C303.66,428.14 303.84,428.16 303.84,428.16 C303.84,428.16 303.52,428.11 303.52,428.11 C303.52,428.11 303.67,428.12 303.67,428.12 C303.67,428.12 303.58,428.1 303.58,428.1 C303.58,428.1 303.49,428.3 303.49,428.11 C303.49,427.93 303.63,428.09 303.63,428.09 C303.63,428.09 303.45,428.1 303.45,428.1 C303.45,428.1 303.76,428.04 303.76,428.04 C303.76,428.04 303.73,428 303.73,428 C303.73,428 303.69,427.98 303.69,427.98 C303.69,427.98 303.71,428.13 303.71,428.13 C303.71,428.13 303.76,428.08 303.76,428.08 C303.76,428.08 303.8,428.06 303.8,428.06 C303.8,428.06 303.8,428.11 303.8,428.11 C303.8,428.11 303.58,428.16 303.58,428.16 C303.58,428.16 310.92,429.75 310.92,429.75 C310.92,429.75 310.91,429.75 310.91,429.75 C310.91,429.75 310.93,429.75 310.93,429.75 C310.93,429.75 310.9,429.75 310.9,429.75 C310.9,429.75 310.93,429.75 310.93,429.75 C310.93,429.75 310.92,429.74 310.92,429.74z ;M299.65 434.12 C299.65,434.12 299.7,434.13 299.7,434.13 C299.7,434.13 299.66,434.11 299.66,434.11 C299.66,434.11 299.64,434.14 299.64,434.14 C299.64,434.14 299.66,434.14 299.66,434.14 C299.66,434.14 299.72,434.15 299.72,434.15 C299.72,434.15 299.81,434.08 299.81,434.08 C299.81,434.08 299.72,434.11 299.72,434.11 C299.72,434.11 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 300.06,430.31 300.06,430.31 C300.06,430.31 299.78,430.27 299.78,430.27 C299.78,430.27 299.96,430.29 299.96,430.29 C299.96,430.29 299.65,430.25 299.65,430.25 C299.65,430.25 299.8,430.25 299.8,430.25 C299.8,430.25 299.7,430.24 299.7,430.24 C299.7,430.24 299.61,430.43 299.61,430.25 C299.61,430.06 299.75,430.22 299.75,430.22 C299.75,430.22 299.57,430.23 299.57,430.23 C299.57,430.23 299.89,430.17 299.89,430.17 C299.89,430.17 299.85,430.13 299.85,430.13 C299.85,430.13 299.82,430.12 299.82,430.12 C299.82,430.12 299.83,430.26 299.83,430.26 C299.83,430.26 299.89,430.21 299.89,430.21 C299.89,430.21 299.93,430.19 299.93,430.19 C299.93,430.19 299.93,430.25 299.93,430.25 C299.93,430.25 299.7,430.29 299.7,430.29 C299.7,430.29 299.65,434.13 299.65,434.13 C299.65,434.13 299.64,434.13 299.64,434.13 C299.64,434.13 299.66,434.13 299.66,434.13 C299.66,434.13 299.63,434.13 299.63,434.13 C299.63,434.13 299.65,434.13 299.65,434.13 C299.65,434.13 299.65,434.12 299.65,434.12z ;M292.83 434.12 C292.83,434.12 292.81,434.11 292.81,434.11 C292.81,434.11 292.84,434.12 292.84,434.12 C292.84,434.12 292.82,434.15 292.82,434.15 C292.82,434.15 292.85,434.15 292.85,434.15 C292.85,434.15 294.61,434.08 294.61,434.08 C294.61,434.08 298.37,434.07 298.37,434.07 C298.37,434.07 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 298.47,430.31 298.47,430.31 C298.47,430.31 294.44,430.33 294.44,430.33 C294.44,430.33 292.89,430.31 292.89,430.31 C292.89,430.31 292.69,430.25 292.69,430.25 C292.69,430.25 292.72,430.28 292.72,430.28 C292.72,430.28 292.63,430.26 292.63,430.26 C292.63,430.26 292.65,430.43 292.65,430.25 C292.65,430.06 292.56,430.15 292.56,430.15 C292.56,430.15 292.61,430.23 292.61,430.23 C292.61,430.23 292.93,430.17 292.93,430.17 C292.93,430.17 292.89,430.13 292.89,430.13 C292.89,430.13 292.85,430.12 292.85,430.12 C292.85,430.12 292.87,430.26 292.87,430.26 C292.87,430.26 292.93,430.21 292.93,430.21 C292.93,430.21 292.96,430.19 292.96,430.19 C292.96,430.19 292.96,430.25 292.96,430.25 C292.96,430.25 292.77,430.22 292.77,430.22 C292.77,430.22 292.83,434.13 292.83,434.13 C292.83,434.13 292.82,434.13 292.82,434.13 C292.82,434.13 292.84,434.13 292.84,434.13 C292.84,434.13 292.81,434.13 292.81,434.13 C292.81,434.13 292.83,434.13 292.83,434.13 C292.83,434.13 292.83,434.12 292.83,434.12z ;M286.91 434.04 C286.91,434.04 286.89,434.02 286.89,434.02 C286.89,434.02 286.92,434.03 286.92,434.03 C286.92,434.03 286.9,434.06 286.9,434.06 C286.9,434.06 286.92,434.06 286.92,434.06 C286.92,434.06 294.61,434.08 294.61,434.08 C294.61,434.08 298.39,434.03 298.39,434.03 C298.39,434.03 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 298.47,430.31 298.47,430.31 C298.47,430.31 294.44,430.33 294.44,430.33 C294.44,430.33 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 291.91,428.68 291.91,428.68 C291.91,428.68 291.82,428.67 291.82,428.67 C291.82,428.67 291.88,428.65 291.88,428.46 C291.88,428.28 291.78,428.37 291.78,428.37 C291.78,428.37 291.84,428.44 291.84,428.44 C291.84,428.44 292.15,428.39 292.15,428.39 C292.15,428.39 292.12,428.35 292.12,428.35 C292.12,428.35 292.08,428.33 292.08,428.33 C292.08,428.33 292.1,428.48 292.1,428.48 C292.1,428.48 292.15,428.42 292.15,428.42 C292.15,428.42 292.19,428.41 292.19,428.41 C292.19,428.41 292.19,428.46 292.19,428.46 C292.19,428.46 291.97,428.51 291.97,428.51 C291.97,428.51 287.14,434.07 287.14,434.07 C287.14,434.07 286.89,434.05 286.89,434.05 C286.89,434.05 286.92,434.05 286.92,434.05 C286.92,434.05 286.89,434.05 286.89,434.05 C286.89,434.05 286.91,434.05 286.91,434.05 C286.91,434.05 286.91,434.04 286.91,434.04z ;M286.7 429.47 C286.7,429.47 286.88,429.37 286.88,429.37 C286.88,429.37 286.52,429.45 286.52,429.45 C286.52,429.45 286.83,429.85 286.83,429.85 C286.83,429.85 286.14,434.18 286.14,434.18 C286.14,434.18 294.61,434.08 294.61,434.08 C294.61,434.08 298.37,434.08 298.37,434.08 C298.37,434.08 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 298.38,430.31 298.38,430.31 C298.38,430.31 294.56,430.33 294.56,430.33 C294.56,430.33 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 291.99,426.42 291.99,426.42 C291.99,426.42 291.87,426.34 291.87,426.34 C291.87,426.34 292.01,426.25 292.01,426.07 C292.01,425.88 292.05,425.99 292.05,425.99 C292.05,425.99 291.97,425.95 291.97,425.95 C291.97,425.95 292.39,425.98 292.39,425.98 C292.39,425.98 292.27,426.05 292.27,426.05 C292.27,426.05 292.35,425.99 292.35,425.99 C292.35,425.99 292.32,426 292.32,426 C292.32,426 292.4,426 292.4,426 C292.4,426 292.4,426.06 292.4,426.06 C292.4,426.06 292.39,426.05 292.39,426.05 C292.39,426.05 292.62,426.45 292.62,426.45 C292.62,426.45 286.78,429.41 286.78,429.41 C286.78,429.41 286.55,429.2 286.55,429.2 C286.55,429.2 286.62,429.38 286.62,429.38 C286.62,429.38 286.51,429.44 286.51,429.44 C286.51,429.44 286.46,429.37 286.46,429.37 C286.46,429.37 286.7,429.47 286.7,429.47z ;M286.5 424.9 C286.5,424.9 286.87,424.72 286.87,424.72 C286.87,424.72 286.13,424.87 286.13,424.87 C286.13,424.87 286.76,425.64 286.76,425.64 C286.76,425.64 285.37,434.3 285.37,434.3 C285.37,434.3 294.63,434.09 294.63,434.09 C294.63,434.09 298.37,434.09 298.37,434.09 C298.37,434.09 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 298.36,430.31 298.36,430.31 C298.36,430.31 294.59,430.33 294.59,430.33 C294.59,430.33 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.06,424.16 292.06,424.16 C292.06,424.16 291.91,424.01 291.91,424.01 C291.91,424.01 292.13,423.86 292.13,423.68 C292.13,423.49 292.32,423.6 292.32,423.6 C292.32,423.6 292.1,423.46 292.1,423.46 C292.1,423.46 292.62,423.57 292.62,423.57 C292.62,423.57 292.43,423.75 292.43,423.75 C292.43,423.75 292.62,423.64 292.62,423.64 C292.62,423.64 292.54,423.53 292.54,423.53 C292.54,423.53 292.65,423.57 292.65,423.57 C292.65,423.57 292.62,423.72 292.62,423.72 C292.62,423.72 292.58,423.64 292.58,423.64 C292.58,423.64 293.27,424.39 293.27,424.39 C293.27,424.39 286.43,424.75 286.43,424.75 C286.43,424.75 286.2,424.35 286.2,424.35 C286.2,424.35 286.31,424.72 286.31,424.72 C286.31,424.72 286.13,424.83 286.13,424.83 C286.13,424.83 286.02,424.68 286.02,424.68 C286.02,424.68 286.5,424.9 286.5,424.9z ;M285.53 417.93 C285.53,417.93 285.61,418.01 285.61,418.01 C285.61,418.01 285.39,417.97 285.39,417.97 C285.39,417.97 285.68,418.12 285.68,418.12 C285.68,418.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.61,434.08 294.61,434.08 C294.61,434.08 298.38,434.11 298.38,434.11 C298.38,434.11 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 298.71,430.31 298.71,430.31 C298.71,430.31 293.3,430.31 293.3,430.31 C293.3,430.31 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.26,417.75 291.26,417.56 C291.26,417.38 291.34,417.38 291.34,417.38 C291.34,417.38 291.45,417.54 291.45,417.54 C291.45,417.54 291.21,417.5 291.21,417.5 C291.21,417.5 291.32,417.45 291.32,417.45 C291.32,417.45 291.28,417.51 291.28,417.51 C291.28,417.51 291.5,417.56 291.5,417.56 C291.5,417.56 291.52,417.54 291.52,417.54 C291.52,417.54 291.45,417.6 291.45,417.6 C291.45,417.6 291.43,417.67 291.43,417.67 C291.43,417.67 291.41,417.89 291.41,417.89 C291.41,417.89 291.24,417.95 291.24,417.95 C291.24,417.95 285.98,417.86 285.98,417.86 C285.98,417.86 286.02,417.69 286.02,417.69 C286.02,417.69 285.92,417.77 285.92,417.77 C285.92,417.77 285.81,417.62 285.81,417.62 C285.81,417.62 285.53,417.93 285.53,417.93z ;M284.93 404.18 C284.93,404.18 281.14,411.97 281.14,411.97 C281.14,411.97 273.88,412.04 273.88,412.04 C273.88,412.04 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.61,434.08 294.61,434.08 C294.61,434.08 298.36,434.08 298.36,434.08 C298.36,434.08 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 298.35,430.31 298.35,430.31 C298.35,430.31 294.59,430.32 294.59,430.32 C294.59,430.32 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 291.91,415.81 291.91,415.81 C291.91,415.81 291.8,415.82 291.8,415.82 C291.8,415.82 291.88,415.73 291.88,415.73 C291.88,415.73 291.9,415.66 291.9,415.66 C291.9,415.66 291.8,415.65 291.8,415.65 C291.8,415.65 291.73,415.73 291.73,415.73 C291.73,415.73 291.87,415.58 291.87,415.58 C291.87,415.58 291.87,415.71 291.87,415.71 C291.87,415.71 291.83,415.72 291.83,415.72 C291.83,415.72 291.82,415.71 291.82,415.71 C291.82,415.71 291.66,414.92 291.66,414.92 C291.66,414.92 291.45,413.38 291.45,413.38 C291.45,413.38 291.09,411.81 291.09,411.81 C291.09,411.81 291.05,411.77 291.05,411.77 C291.05,411.77 289.08,410.26 289.08,410.26 C289.08,410.26 284.93,404.18 284.93,404.18z ;M298.66 404.21 C298.66,404.21 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.61,434.09 294.61,434.09 C294.61,434.09 298.35,434.08 298.35,434.08 C298.35,434.08 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 298.76,430.32 298.76,430.32 C298.76,430.32 294.62,430.33 294.62,430.33 C294.62,430.33 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 300.75,413.19 300.75,413.19 C300.75,413.19 300.74,413.2 300.74,413.2 C300.74,413.2 300.68,413.28 300.68,413.28 C300.68,413.28 300.74,413.15 300.74,413.15 C300.74,413.15 300.76,413.19 300.76,413.19 C300.76,413.19 300.77,413.17 300.77,413.17 C300.77,413.17 303.55,406.44 303.55,406.44 C303.55,406.44 302.85,404.47 302.85,404.47 C302.85,404.47 301.29,403.47 301.29,403.47 C301.29,403.47 301.18,403.32 301.18,403.32 C301.18,403.32 298.66,404.21 298.66,404.21z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.61,434.07 294.61,434.07 C294.61,434.07 298.36,434.07 298.36,434.07 C298.36,434.07 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 298.73,430.31 298.73,430.31 C298.73,430.31 293.3,430.33 293.3,430.33 C293.3,430.33 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 302.59,416.02 302.59,416.02 C302.59,416.02 302.55,415.98 302.55,415.98 C302.55,415.98 302.63,415.99 302.63,415.99 C302.63,415.99 306.67,409.55 306.67,409.55 C306.67,409.55 306.65,409.61 306.65,409.61 C306.65,409.61 306.59,409.55 306.59,409.55 C306.59,409.55 306.69,409.72 306.69,409.72 C306.69,409.72 306.58,409.57 306.58,409.57 C306.58,409.57 301.92,404.95 301.92,404.95z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.61,434.09 294.61,434.09 C294.61,434.09 298.36,434.09 298.36,434.09 C298.36,434.09 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 306.29,420.1 306.29,420.1 C306.29,420.1 301.7,423.39 301.7,423.39 C301.7,423.39 298.38,430.31 298.38,430.31 C298.38,430.31 293.4,430.32 293.4,430.32 C293.4,430.32 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 302.63,417.02 302.63,417.02 C302.63,417.02 302.61,416.97 302.61,416.97 C302.61,416.97 302.63,416.9 302.63,416.9 C302.63,416.9 307.12,415.55 307.12,415.55 C307.12,415.55 307.51,415.47 307.51,415.47 C307.51,415.47 307.52,415.47 307.52,415.47 C307.52,415.47 309.01,412.56 309.01,412.56 C309.01,412.56 310.3,409.7 310.3,409.7 C310.3,409.7 301.92,404.95 301.92,404.95z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.6,434.08 294.6,434.08 C294.6,434.08 298.37,434.07 298.37,434.07 C298.37,434.07 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 306.05,420.2 306.05,420.2 C306.05,420.2 301.63,423.29 301.63,423.29 C301.63,423.29 298.57,430.33 298.57,430.33 C298.57,430.33 293.35,430.32 293.35,430.32 C293.35,430.32 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 301.74,416.82 301.74,416.82 C301.74,416.82 297.55,418.67 297.55,418.67 C297.55,418.67 300.2,418.16 300.2,418.16 C300.2,418.16 306.72,417.16 306.72,417.16 C306.72,417.16 307.56,417.29 307.56,417.29 C307.56,417.29 307.59,417.33 307.59,417.33 C307.59,417.33 308.54,413.47 308.54,413.47 C308.54,413.47 306.71,408.22 306.71,408.22 C306.71,408.22 301.92,404.95 301.92,404.95z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.62,434.09 294.62,434.09 C294.62,434.09 298.35,434.08 298.35,434.08 C298.35,434.08 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 305.8,420.3 305.8,420.3 C305.8,420.3 301.55,423.2 301.55,423.2 C301.55,423.2 298.74,430.31 298.74,430.31 C298.74,430.31 293.34,430.32 293.34,430.32 C293.34,430.32 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 301.74,416.82 301.74,416.82 C301.74,416.82 297.55,418.67 297.55,418.67 C297.55,418.67 300.2,418.16 300.2,418.16 C300.2,418.16 306.32,418.77 306.32,418.77 C306.32,418.77 307.34,417.78 307.34,417.78 C307.34,417.78 307.74,418.52 307.74,418.52 C307.74,418.52 308.08,414.37 308.08,414.37 C308.08,414.37 310.3,409.7 310.3,409.7 C310.3,409.7 301.92,404.95 301.92,404.95z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.6,434.09 294.6,434.09 C294.6,434.09 298.35,434.08 298.35,434.08 C298.35,434.08 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 305.98,420.3 305.98,420.3 C305.98,420.3 301.72,423.59 301.72,423.59 C301.72,423.59 298.75,430.25 298.75,430.25 C298.75,430.25 293.3,430.28 293.3,430.28 C293.3,430.28 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 301.74,416.82 301.74,416.82 C301.74,416.82 297.55,418.67 297.55,418.67 C297.55,418.67 296.68,421.72 296.68,421.72 C296.68,421.72 300.57,423.18 300.57,423.18 C300.57,423.18 301.74,423.57 301.74,423.57 C301.74,423.57 306.45,419.97 306.45,419.97 C306.45,419.97 308.08,414.37 308.08,414.37 C308.08,414.37 310.3,409.7 310.3,409.7 C310.3,409.7 301.92,404.95 301.92,404.95z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.61,434.08 294.61,434.08 C294.61,434.08 298.36,434.09 298.36,434.09 C298.36,434.09 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 306.41,419.97 306.41,419.97 C306.41,419.97 301.7,423.64 301.7,423.64 C301.7,423.64 298.69,430.31 298.69,430.31 C298.69,430.31 294.56,430.33 294.56,430.33 C294.56,430.33 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 301.74,416.82 301.74,416.82 C301.74,416.82 292.58,424.16 292.58,424.16 C292.58,424.16 294.58,430.33 294.58,430.33 C294.58,430.33 298.38,430.31 298.38,430.31 C298.38,430.31 301.74,423.57 301.74,423.57 C301.74,423.57 306.45,419.97 306.45,419.97 C306.45,419.97 308.08,414.37 308.08,414.37 C308.08,414.37 310.3,409.7 310.3,409.7 C310.3,409.7 301.92,404.95 301.92,404.95z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 293.73,439.55 293.73,439.55 C293.73,439.55 298.46,439.54 298.46,439.54 C298.46,439.54 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 306.43,419.98 306.43,419.98 C306.43,419.98 301.75,423.57 301.75,423.57 C301.75,423.57 298.73,430.27 298.73,430.27 C298.73,430.27 293.72,430.3 293.72,430.3 C293.72,430.3 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 301.74,416.82 301.74,416.82 C301.74,416.82 292.58,424.16 292.58,424.16 C292.58,424.16 293.7,430.31 293.7,430.31 C293.7,430.31 298.74,430.26 298.74,430.26 C298.74,430.26 301.74,423.57 301.74,423.57 C301.74,423.57 306.45,419.97 306.45,419.97 C306.45,419.97 308.08,414.37 308.08,414.37 C308.08,414.37 310.3,409.7 310.3,409.7 C310.3,409.7 301.92,404.95 301.92,404.95z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 293.25,442.25 293.25,442.25 C293.25,442.25 298.5,442.3 298.5,442.3 C298.5,442.3 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 306.45,419.99 306.45,419.99 C306.45,419.99 301.77,423.53 301.77,423.53 C301.77,423.53 298.75,430.25 298.75,430.25 C298.75,430.25 293.3,430.28 293.3,430.28 C293.3,430.28 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 301.74,416.82 301.74,416.82 C301.74,416.82 292.58,424.16 292.58,424.16 C292.58,424.16 293.3,430.28 293.3,430.28 C293.3,430.28 298.75,430.25 298.75,430.25 C298.75,430.25 301.74,423.57 301.74,423.57 C301.74,423.57 306.45,419.97 306.45,419.97 C306.45,419.97 308.08,414.37 308.08,414.37 C308.08,414.37 310.3,409.7 310.3,409.7 C310.3,409.7 301.92,404.95 301.92,404.95z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 293.25,442.25 293.25,442.25 C293.25,442.25 298.5,442.3 298.5,442.3 C298.5,442.3 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 306.45,419.99 306.45,419.99 C306.45,419.99 301.77,423.53 301.77,423.53 C301.77,423.53 298.75,430.25 298.75,430.25 C298.75,430.25 293.3,430.28 293.3,430.28 C293.3,430.28 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 301.74,416.82 301.74,416.82 C301.74,416.82 292.58,424.16 292.58,424.16 C292.58,424.16 293.3,430.28 293.3,430.28 C293.3,430.28 298.75,430.25 298.75,430.25 C298.75,430.25 301.74,423.57 301.74,423.57 C301.74,423.57 306.45,419.97 306.45,419.97 C306.45,419.97 308.08,414.37 308.08,414.37 C308.08,414.37 310.3,409.7 310.3,409.7 C310.3,409.7 301.92,404.95 301.92,404.95z " keySplines="0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0 0 0 0" calcMode="spline"/>
                                                <clipPath id="_R_G_L_0_C_0">
                                                    <path id="_R_G_L_0_C_0_P_0" fill-rule="nonzero"/>
                                                </clipPath>
                                                <animate attributeType="XML" attributeName="opacity" dur="2s" from="0" to="1" xlink:href="#time_group_edit_appointment"/>
                                            </defs>
                                            <g id="_R_G">
                                                <g id="_R_G_L_1_G" transform=" translate(127.638, 127.945) scale(3.37139, 3.37139) translate(-297.638, -420.945)">
                                                    <g clip-path="url(#_R_G_L_1_C_0)">
                                                        <path id="_R_G_L_1_G_G_0_D_0_P_0" class="bpa-front-loader-cl-primary" fill-opacity="1" fill-rule="nonzero" d=" M328 398.61 C328,398.61 328,446.23 328,446.23 C328,449.7 325.2,452.5 321.75,452.5 C321.75,452.5 274.25,452.5 274.25,452.5 C270.8,452.5 268,449.7 268,446.23 C268,446.23 268,398.61 268,398.61 C268,395.15 270.8,392.35 274.25,392.35 C274.25,392.35 283.46,392.26 283.46,392.26 C283.46,392.26 283.46,390.38 283.46,390.38 C283.46,389.76 284.08,388.5 285.33,388.5 C286.58,388.5 287.21,389.75 287.21,390.38 C287.21,390.38 287.21,397.89 287.21,397.89 C287.21,398.53 286.59,399.78 285.33,399.78 C284.08,399.78 283.46,398.53 283.46,397.9 C283.46,397.9 283.46,396.02 283.46,396.02 C283.46,396.02 275.5,396.1 275.5,396.1 C273.43,396.1 271.75,397.79 271.75,399.86 C271.75,399.86 271.75,444.98 271.75,444.98 C271.75,447.06 273.43,448.74 275.5,448.74 C275.5,448.74 320.5,448.74 320.5,448.74 C322.57,448.74 324.25,447.06 324.25,444.98 C324.25,444.98 324.25,399.86 324.25,399.86 C324.25,397.79 322.57,396.1 320.5,396.1 C320.5,396.1 312.62,396.1 312.62,396.1 C312.62,396.1 312.63,397.06 312.63,397.99 C312.63,398.61 312,399.86 310.75,399.86 C309.5,399.86 308.88,398.61 308.88,397.98 C308.88,397.98 308.87,396.1 308.87,396.1 C308.87,396.1 301.88,396.1 301.88,396.1 C300.84,396.1 300,395.26 300,394.23 C300,393.19 300.84,392.35 301.88,392.35 C301.88,392.35 308.87,392.35 308.87,392.35 C308.87,392.35 308.87,390.47 308.87,390.47 C308.87,389.83 309.5,388.5 310.75,388.5 C312,388.5 312.62,389.84 312.62,390.47 C312.62,390.47 312.62,392.35 312.62,392.35 C312.62,392.35 321.75,392.35 321.75,392.35 C325.2,392.35 328,395.15 328,398.61z "/>
                                                    </g>
                                                </g>
                                                <g id="_R_G_L_0_G" transform=" translate(125.555, 126.412) scale(3.37139, 3.37139) translate(-297.638, -420.945)">
                                                    <g clip-path="url(#_R_G_L_0_C_0)">
                                                        <path id="_R_G_L_0_G_G_0_D_0_P_0" class="bpa-front-loader-cl-primary" fill-opacity="1" fill-rule="nonzero" d=" M305.86 420.29 C305.86,420.29 307.11,419.04 307.11,415.28 C307.11,409.01 303.36,407.76 298.36,407.76 C298.36,407.76 287.11,407.76 287.11,407.76 C287.11,407.76 287.11,434.08 287.11,434.08 C287.11,434.08 294.61,434.08 294.61,434.08 C294.61,434.08 294.61,441.6 294.61,441.6 C294.61,441.6 298.36,441.6 298.36,441.6 C298.36,441.6 298.36,434.08 298.36,434.08 C302.71,434.08 305.73,434.08 307.98,431.3 C309.07,429.95 309.62,428.24 309.61,426.5 C309.61,425.58 309.51,424.67 309.3,424.05 C308.73,422.65 308.36,421.55 305.86,420.29z  M302.11 430.32 C302.11,430.32 298.36,430.32 298.36,430.32 C298.36,430.32 298.36,426.56 298.36,426.56 C298.36,424.48 300.03,422.8 302.11,422.8 C304.13,422.8 305.86,424.43 305.86,426.56 C305.86,428.78 304.03,430.32 302.11,430.32z  M299.07 419.95 C298.43,420.26 297.82,420.63 297.26,421.05 C295.87,422.1 294.61,423.58 294.61,426.56 C294.61,426.56 294.61,430.32 294.61,430.32 C294.61,430.32 290.86,430.32 290.86,430.32 C290.86,430.32 290.86,411.52 290.86,411.52 C290.86,411.52 298.36,411.52 298.36,411.52 C301.35,411.52 303.36,412.77 303.36,415.28 C303.36,417.58 301.65,418.68 299.07,419.95z "/>
                                                    </g>
                                                </g>
                                            </g>
                                            <g id="time_group_edit_appointment"/>
                                        </svg>
                                    </div>
                                </div> 
                            </div>                    
                            <div v-if="recurring_appointment_loader_edit != true" class="bpa-form-row">
                                <el-row>
                                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                        <el-form ref="recurring_edit_form"  label-position="top" @submit.native.prevent>
                                            <div class="bpa-form-body-row">
                                                <el-row>
                                                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                        <el-form-item prop="recurring_edit_date">
                                                            <template #label>
                                                                <span class="bpa-form-label">{{recurring_edit_appointment_date_label}}</span>
                                                            </template>   
                                                            <el-date-picker :disabled="recurring_appointment_loader_edit" @change="change_recurring_start_date($event,'edit')" class="bpa-front-form-control bpa-front-form-control--date-picker" type="date" format="<?php echo esc_html($bookingpress_common_date_format); ?>" placeholder="<?php echo esc_html($bookingpress_common_date_format); ?>" v-model="recurring_edit_date" name="appointment_booked_date" popper-class="bpa-custom-datepicker bpa-custom-recurring-datepicker" type="date" :clearable="false" :picker-options="recurring_edit_pickerOptions" value-format="yyyy-MM-dd"></el-date-picker>                                                                                                             
                                                        </el-form-item> 
                                                    </el-col>	
                                                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                        <el-form-item prop="happy_hour_label">
                                                            <template #label>
                                                                <span class="bpa-form-label">{{recurring_start_time_label}}</span>
                                                            </template>   
                                                            <div class="field bpa-recurring-appointment-head-row">
                                                                <el-select :disabled="recurring_appointment_time_loader_edit" :loading="recurring_appointment_time_loader_edit" class="bpa-front-form-control" :placeholder="recurring_start_time_label" v-model="recurring_edit_time" filterable popper-class="bpa-fm--service__advance-options-popper bpa-fm--service__advance-options-recurring-popper bpa-el-select--is-with-modal" @Change="bookingpress_set_recurring_start_time($event,recurring_edit_appointment_time_slot,'edit')">                                                                
                                                                    <el-option-group :class="hide_time_slot_grouping == true ? 'bpa-do-not-group-timing' : ''" v-for="appointment_time_slot_data in recurring_edit_appointment_time_slot" :key="appointment_time_slot_data.timeslot_label" :label="appointment_time_slot_data.timeslot_label" >
                                                                        <el-option v-for="appointment_time in appointment_time_slot_data.timeslots" :label="bookingpress_set_recurring_appointment_timeslot_formate(appointment_time)" :value="appointment_time.store_start_time" :disabled="( appointment_time.is_disabled || appointment_time.max_capacity == 0 || appointment_time.is_booked == 1 )">
                                                                            <span>{{ bookingpress_set_recurring_appointment_timeslot_formate(appointment_time) }}</span>
                                                                        </el-option>	
                                                                    </el-option-group>                                                                
                                                                </el-select>
                                                            </div>                                                        
                                                        </el-form-item> 
                                                    </el-col>	                                                								
                                                </el-row>
                                            </div>
                                        </el-form>
                                    </el-col>
                                </el-row>
                            </div>
                            
                        </el-container>
                    </div>
                    <div class="bpa-dialog-footer">
                        <div class="bpa-hw-right-btn-group">
                            <el-button @click="close_recurring_modal()" class="bpa-front-btn bpa-btn bpa-btn__small bpa-front-btn--borderless"><?php esc_html_e( 'Cancel', 'bookingpress-recurring-appointments' ); ?></el-button>
                            <el-button @click="save_edit_recurring_data()" class="bpa-front-btn bpa-btn bpa-btn__small bpa-btn--primary bpa-front-btn--primary"><?php esc_html_e( 'Done', 'bookingpress-recurring-appointments' ); ?></el-button>
                        </div>
                    </div>
                </el-dialog>          
            <?php } ?>
        </div>
        <?php 
        }

        /**
         * Function for add front booking method
         *
         * @param  mixed $bookingpress_vue_methods_data
         * @return void
        */
        function bookingpress_add_pro_booking_form_methods_func($bookingpress_vue_methods_data){
            global $bookingpress_ajaxurl;
            $bookingpress_nonce = wp_create_nonce('bpa_wp_nonce');            
            $bookingpress_dynamic_add_params_for_timeslot_request = '';
            $bookingpress_dynamic_add_params_for_timeslot_request = apply_filters('bookingpress_dynamic_add_params_for_timeslot_request', $bookingpress_dynamic_add_params_for_timeslot_request);
            $to_text_data = __('to', 'bookingpress-recurring-appointments');

            $bookingpress_vue_methods_data.= '
            bookingpress_select_repeat_recurring_appointment(){
                const vm = this;
                var no_of_session = parseInt(vm.appointment_step_form_data.recurring_form_data.no_of_session);
                if(no_of_session > 0){
                    vm.appointment_step_form_data.is_recurring_appointments = true;
                    let current_selected_service_for_recurring = vm.appointment_step_form_data.selected_service;
                    if(typeof vm.bookingpress_all_services_data[ current_selected_service_for_recurring ].default_recurring_frequencies != "undefined"){
                        vm.appointment_step_form_data.recurring_form_data.recurring_frequency = vm.bookingpress_all_services_data[ current_selected_service_for_recurring ].default_recurring_frequencies;
                    }
                }else{
                    vm.appointment_step_form_data.is_recurring_appointments = false;
                }                
                vm.appointment_step_form_data.recurring_appointments = [];
            },
            calculate_recurring_appointment_total(){

                const vm = this;
                var bookingpress_recurring_total = 0;
                var all_recurring_appointments = vm.appointment_step_form_data.recurring_appointments;
                var base_price_without_currency = parseFloat(vm.appointment_step_form_data.base_price_without_currency);
                var bookingpress_selected_bring_members = parseInt(vm.appointment_step_form_data.bookingpress_selected_bring_members);
                var single_appointment_price = base_price_without_currency * bookingpress_selected_bring_members;                
                var total_recurring_appointment = 0;
                if(all_recurring_appointments.length > 0){
                    all_recurring_appointments.forEach(function(item, index, arr){					
                        if(item.is_not_avaliable == 0){						
                            total_recurring_appointment = total_recurring_appointment+1; 
                        }					
                    }); 
                }
                
                /* BookingPress Add Service Extra Price To Single Appointment Total Start */    

                var bookingpress_service_extra_total = 0;
                var bookingpress_selected_extras_counter = 0;
                var bookingpress_selected_extras_ids = [];
                var bookingpress_selected_extras_qty = [];
                vm.bookingpress_service_extras.forEach(function(currentValue3, index3, arr3){
                    if(currentValue3.bookingpress_service_id == vm.appointment_step_form_data.selected_service && (vm.appointment_step_form_data.bookingpress_selected_extra_details[currentValue3.bookingpress_extra_services_id].bookingpress_is_selected == "true" || vm.appointment_step_form_data.bookingpress_selected_extra_details[currentValue3.bookingpress_extra_services_id].bookingpress_is_selected == true)){
                        var bookingpress_selected_extra_service_qty = parseFloat(vm.appointment_step_form_data.bookingpress_selected_extra_details[currentValue3.bookingpress_extra_services_id].bookingpress_selected_qty);
                        var bookingpress_selected_extra_service_price = parseFloat(currentValue3.bookingpress_extra_service_price);
                        bookingpress_service_extra_total = bookingpress_service_extra_total + (bookingpress_selected_extra_service_price * bookingpress_selected_extra_service_qty);
                        bookingpress_selected_extras_counter = bookingpress_selected_extras_counter + 1;
                
                        bookingpress_selected_extras_ids.push(currentValue3.bookingpress_extra_services_id);
                        bookingpress_selected_extras_qty[currentValue3.bookingpress_extra_services_id] = bookingpress_selected_extra_service_qty;
                    }
                });                
                
                single_appointment_price = single_appointment_price + bookingpress_service_extra_total;

                /* BookingPress Add Service Extra Price To Single Appointment Total Over */

                var bookingpress_service_price = single_appointment_price;

                var final_recurring_appointment_total_amt = single_appointment_price * total_recurring_appointment;
                vm.appointment_step_form_data.bookingpress_recurring_total = final_recurring_appointment_total_amt;
                vm.appointment_step_form_data.bookingpress_recurring_total_with_currency = vm.bookingpress_price_with_currency_symbol(final_recurring_appointment_total_amt);

                vm.appointment_step_form_data.bookingpress_recurring_original_total = final_recurring_appointment_total_amt;
                vm.appointment_step_form_data.bookingpress_recurring_original_total_with_currency = vm.bookingpress_price_with_currency_symbol(final_recurring_appointment_total_amt);

                /*  New Code Added Start */
                var bookingpress_deposit_price = 0;
                var bookingpress_deposit_due_amount = 0;
                if( vm.bookingpress_is_deposit_payment_activate == 1 && ( vm.appointment_step_form_data.bookingpress_deposit_payment_method == "deposit_or_full_price" || vm.appointment_step_form_data.bookingpress_deposit_payment_method == "allow_customer_to_pay_full_amount" ) )
                {
                    var bookingpress_deposit_amount = parseFloat(vm.appointment_step_form_data.deposit_payment_amount);
                    if(vm.appointment_step_form_data.deposit_payment_type == "percentage"){
                        bookingpress_deposit_price = bookingpress_service_price * (bookingpress_deposit_amount / 100);
                    }else{
                        bookingpress_deposit_price = bookingpress_deposit_amount;
                    }
                    bookingpress_service_price = bookingpress_deposit_due_amount = bookingpress_service_price - bookingpress_deposit_price;
                }
                /*  New Code Added Over */

                vm.appointment_step_form_data.bookingpress_single_deposit_price = bookingpress_deposit_price;
                vm.appointment_step_form_data.bookingpress_single_deposit_due_amount = bookingpress_deposit_due_amount;

                var bookingpress_deposit_total_amount = parseFloat(bookingpress_deposit_price) * total_recurring_appointment;
                vm.appointment_step_form_data.bookingpress_deposit_total = bookingpress_deposit_total_amount;
                vm.appointment_step_form_data.bookingpress_deposit_total_with_currency = vm.bookingpress_price_with_currency_symbol(bookingpress_deposit_total_amount);

                vm.appointment_step_form_data.total_payable_amount = final_recurring_appointment_total_amt + bookingpress_deposit_total_amount;
                vm.appointment_step_form_data.total_payable_amount_with_currency = vm.bookingpress_price_with_currency_symbol( final_recurring_appointment_total_amt + bookingpress_deposit_total_amount );
                
                var bookingpress_deposit_due_amount_total = 0;
                if( vm.bookingpress_is_deposit_payment_activate == 1 && ( vm.appointment_step_form_data.bookingpress_deposit_payment_method == "deposit_or_full_price" || vm.appointment_step_form_data.bookingpress_deposit_payment_method == "allow_customer_to_pay_full_amount" ) ){
                    if(vm.appointment_step_form_data.deposit_payment_type == "percentage"){
                        bookingpress_deposit_price = final_recurring_appointment_total_amt * (bookingpress_deposit_total_amount / 100);
                    }else{
                        bookingpress_deposit_price = bookingpress_deposit_total_amount;
                    }
                    bookingpress_deposit_due_amount_total = final_recurring_appointment_total_amt - bookingpress_deposit_price;                    
                }                
                
                vm.appointment_step_form_data.bookingpress_deposit_due_amount_total = bookingpress_deposit_due_amount_total;
                vm.appointment_step_form_data.bookingpress_deposit_due_amount_total_with_currency = vm.bookingpress_price_with_currency_symbol( bookingpress_deposit_due_amount_total );                

            },
            save_edit_recurring_data(){
                const vm = this;
                var to_text_data = "'.$to_text_data.'";
                vm.recurring_appointment_start_counter = 15;
                if(vm.appointment_step_form_data.recurring_edit_index != "" || vm.appointment_step_form_data.recurring_edit_index == 0){

                    var recurringEditIndex = vm.appointment_step_form_data.recurring_edit_index;
                    var single_appointment_data = vm.appointment_step_form_data.recurring_appointments[recurringEditIndex];
                    for (let x in vm.recurring_edit_appointment_time_slot) {
                        if(typeof vm.recurring_edit_appointment_time_slot[x] != "undefined" && vm.recurring_edit_date != ""){
                            var slot_data_arr = vm.recurring_edit_appointment_time_slot[x];                        
                            for(let y in slot_data_arr) {
                                var time_slot_data_arr = slot_data_arr[y];
                                for(let m in time_slot_data_arr) {                            
                                    var data_arr  = time_slot_data_arr[m];
                                    if(data_arr.store_start_time != undefined && data_arr.store_end_time != undefined && data_arr.store_start_time == vm.recurring_edit_time){
                                        vm.appointment_step_form_data.recurring_appointments[recurringEditIndex].formated_end_time = data_arr.formatted_end_time;
                                        vm.appointment_step_form_data.recurring_appointments[recurringEditIndex].formated_select_date = data_arr.store_service_date;
                                        vm.appointment_step_form_data.recurring_appointments[recurringEditIndex].formated_start_time = data_arr.formatted_start_time;
                                        vm.appointment_step_form_data.recurring_appointments[recurringEditIndex].selected_date = data_arr.store_service_date;
                                        vm.appointment_step_form_data.recurring_appointments[recurringEditIndex].selected_end_time = data_arr.store_end_time;
                                        vm.appointment_step_form_data.recurring_appointments[recurringEditIndex].selected_start_time = data_arr.store_start_time;
                                        if(vm.bookigpress_time_format_for_booking_form == "1" || vm.bookigpress_time_format_for_booking_form == "2")
                                        {
                                            vm.appointment_step_form_data.recurring_appointments[recurringEditIndex].display_formated_date_and_time = data_arr.formatted_start_time+" "+to_text_data+" "+data_arr.formatted_end_time;
                                        }    
                                        else if(vm.bookigpress_time_format_for_booking_form == "5" || vm.bookigpress_time_format_for_booking_form == "6"){
                                            vm.appointment_step_form_data.recurring_appointments[recurringEditIndex].display_formated_date_and_time = data_arr.formatted_start_time+" "+"-"+" "+data_arr.formatted_end_time;
                                        }
                                        else if(vm.bookigpress_time_format_for_booking_form == "3" || vm.bookigpress_time_format_for_booking_form == "4"){
                                            vm.appointment_step_form_data.recurring_appointments[recurringEditIndex].display_formated_date_and_time = data_arr.formatted_start_time;
                                        }
                                        else if(vm.bookigpress_time_format_for_booking_form == "5" || vm.bookigpress_time_format_for_booking_form == "6"){
                                            vm.appointment_step_form_data.recurring_appointments[recurringEditIndex].display_formated_date_and_time = data_arr.formatted_start_time+" "+"-"+" "+data_arr.formatted_end_time;
                                        }
                                        vm.appointment_step_form_data.recurring_appointments[recurringEditIndex].is_suggested = 0;
                                        vm.appointment_step_form_data.recurring_appointments[recurringEditIndex].is_not_avaliable = 0;
                                    }
                                }                                                    
                            }
                        }
                    }
                }
                               
                const CustformData = new FormData();
                var selected_service_id = vm.appointment_step_form_data.selected_service;
                var selected_date = vm.appointment_step_form_data.selected_date;
                var postData = { action:"bookingpress_after_edit_recurring_appointments", service_id: selected_service_id, selected_date: selected_date, _wpnonce:"'.$bookingpress_nonce.'", };                
                postData.appointment_data_obj = JSON.stringify(vm.appointment_step_form_data);
                postData.recurring_form_data = JSON.stringify(vm.appointment_step_form_data.recurring_form_data);                                                    
                postData.bpa_change_store_date = false;
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then( function (response) {
                     if(typeof response.data.bookingpress_recurring_form_token != "undefined"){
                        if(response.data.bookingpress_recurring_form_token != ""){
                            vm.appointment_step_form_data.bookingpress_form_token = response.data.bookingpress_recurring_form_token;
                        }
                     }
                }.bind(this) )
                .catch( function (error) {
                    console.log(error);                    
                });

                vm.calculate_recurring_appointment_total();
                vm.close_recurring_modal();
                /*vm.recurring_open_edit_popup = false;
                vm.appointment_step_form_data.recurring_edit_index = "";
                vm.bookingpress_set_default_date_and_time_for_validation();
                vm.recurring_edit_appointment_time_slot = [];
                vm.recurring_appointment_device = "";*/
		
                /*
		vm.recurring_open_edit_popup = false;
                vm.appointment_step_form_data.recurring_edit_index = "";
                vm.bookingpress_set_default_date_and_time_for_validation();
                vm.calculate_recurring_appointment_total();
		*/

            },
            close_recurring_modal(){
                const vm = this;
                vm.recurring_open_edit_popup = false;
                vm.appointment_step_form_data.recurring_edit_index = "";
                vm.recurring_edit_appointment_time_slot = [];
                vm.bookingpress_set_default_date_and_time_for_validation();
                vm.recurring_appointment_device = "";
                vm.recurring_appointment_start_counter = 15;
            },               
            open_recurring_modal(currentElement,recurringEditIndex,device=""){

                const vm = this;               
                vm.close_recurring_modal();                
                vm.recurring_edit_appointment_time_slot = [];
                vm.appointment_step_form_data.recurring_edit_index = recurringEditIndex;

                vm.recurring_appointment_device = device;
                document.body.classList.add("bpa-front-booking-popup");

                /* disable not popup start */
                var dialog_pos = currentElement.target.getBoundingClientRect();
                vm.extra_service_modal_pos = (dialog_pos.top - 90)+"px";
                vm.extra_service_modal_pos_right = "-"+(dialog_pos.right - 430)+"px";                                    
                vm.recurring_open_edit_popup = true;
                if(device == "mobile"){
                    vm.recurring_open_edit_popup = false;
                }
                vm.recurring_appointment_start_counter = 15;
                vm.recurring_appointment_loader_edit = true;

                /* disable not popup over */
                if(vm.appointment_step_form_data.bookingpress_recurring_form_token != ""){
                    vm.appointment_step_form_data.bookingpress_form_token = vm.appointment_step_form_data.bookingpress_recurring_form_token;
                }
                if(vm.appointment_step_form_data.recurring_appointments[recurringEditIndex].is_not_avaliable == 0){
                    vm.recurring_edit_time = vm.appointment_step_form_data.recurring_appointments[recurringEditIndex].selected_start_time;
                    vm.recurring_edit_date = vm.appointment_step_form_data.recurring_appointments[recurringEditIndex].selected_date;
                }else{
                    vm.recurring_edit_date = "";
                    vm.recurring_edit_time = "";
                }

                vm.bookingpress_disable_date_xhr(vm.appointment_step_form_data.selected_service,vm.appointment_step_form_data.recurring_appointments[recurringEditIndex].selected_date,false);    
                
                /* disable not popup start */
                if( typeof vm.bpa_adjust_popup_position != "undefined" ){
                    vm.bpa_adjust_popup_position( currentElement, "div#recurring_front_modal .el-dialog.bpa-dialog--add-recurring-edit");
                }
                /* disable not popup over */

                vm.bookingpress_set_default_date_and_time_for_validation();
            },              
            bpa_adjust_popup_position( currentElement, selector, sourceCls = "", position ){
                    let paths = currentElement.path;
                    let buttonElm = null;                    
                    if( typeof paths != "undefined" ){
                        for( let x in paths ){
                            let currentPath = paths[x];
                            let currentPathNodeName = currentPath.nodeName;
                            if( "BUTTON" == currentPathNodeName || ( "undefined" != typeof sourceCls && "" != sourceCls && currentPath.classList.contains(sourceCls)) ){
                                buttonElm = currentPath;
                                break;
                            }
                        }
                    } else {                
                        if( "BUTTON" == currentElement.target.nodeName || ( "undefined" != typeof sourceCls && "" != sourceCls && currentElement.target.classList.contains(sourceCls) ) ){
                            buttonElm = currentElement.target;
                        } else {
                            let par = this.bpa_get_target_parent( currentElement.target, "button" );                            
                            if( par.length > 0 ){
                                buttonElm = par[0];
                            } else {
                                let par = this.bpa_get_target_parent( currentElement.target, "." + sourceCls );
                                if( par.length > 0 ){
                                    buttonElm = par[0];
                                }
                            }
                        }
                    }                    
                    if( null !== buttonElm ){
                        let pos_x = buttonElm.getBoundingClientRect().left;
                        let pos_y = buttonElm.getBoundingClientRect().top;                        
                        pos_x = Math.ceil( pos_x );
                        pos_y = Math.ceil( pos_y );                
                        let btn_height = buttonElm.offsetHeight;
                        let pos_top = pos_y + btn_height + 20;                
                        let dialog__wrapper = document.querySelector( selector );
                        if( null !== dialog__wrapper ){                                                        
                            (function(pos_x, buttonElm, dialog__wrapper){
                                setTimeout(function(){                
                                    dialog__wrapper.style.position = "";
                                    dialog__wrapper.style.margin = "";
                                    dialog__wrapper.style.top = "";
                                    dialog__wrapper.style.left = "0";                
                                    dialog__wrapper.style.position = "absolute";
                                    dialog__wrapper.style.margin = "0";
                                    dialog__wrapper.style.top = parseInt(pos_top) + "px";                
                                    let pos_to_place = pos_x + ( buttonElm.offsetWidth * 0.5 );
                                    let dialog_pos_right = dialog__wrapper.offsetWidth + dialog__wrapper.getBoundingClientRect().left;                                                                           
                                    var dir = "";                                      
                                    if(typeof document.documentElement.dir !== "undefined"){
                                        dir = document.documentElement.dir;
                                    }                                    
                                    if( ("" != position && "right" == position) || dir === "rtl" ){
                                        dialog_pos_right = dialog_pos_right;
                                        dialog__wrapper.style.top = (parseInt(pos_top) - 5) + "px"; 
                                        dialog__wrapper.style.left = (pos_to_place -  ( dialog__wrapper.getBoundingClientRect().left + 50 ) ) + "px";                                        
                                    } else {
                                        dialog_pos_right = dialog_pos_right - 50; 
                                        if (dir === "rtl") {
                                            dialog__wrapper.style.left = ((pos_to_place - dialog_pos_right) + 216) + "px";
                                        }else{
                                            dialog__wrapper.style.left = pos_to_place - dialog_pos_right + "px";
                                        }                                        
                                    }
                                },10)
                            })( pos_x, buttonElm, dialog__wrapper );
                        }
                    }                    
                },   
                bpa_get_target_parent( elem, selector ){
                    if (!Element.prototype.matches) {
                        Element.prototype.matches = Element.prototype.matchesSelector ||
                            Element.prototype.mozMatchesSelector ||
                            Element.prototype.msMatchesSelector ||
                            Element.prototype.oMatchesSelector ||
                            Element.prototype.webkitMatchesSelector ||
                            function(s) {
                                var matches = (this.document || this.ownerDocument).querySelectorAll(s),
                                    i = matches.length;
                                while (--i >= 0 && matches.item(i) !== this) {}
                                return i > -1;
                            };
                    }
                    var parents = [];
                    for (; elem && elem !== document; elem = elem.parentNode) {
                        if (selector) {
                            if (elem.matches(selector)) {
                                parents.push(elem);
                            }
                            continue;
                        }
                        parents.push(elem);
                    }
                    return parents;
                },                                        
                change_recurring_start_date(selected_value,type){
                    const vm = this;                   
                    vm.get_date_timings(selected_value);
                    if(type == "add"){
                        vm.recurring_appointment_time_loader = true;
                        vm.appointment_step_form_data.recurring_edit_index = "";
                        vm.appointment_step_form_data.recurring_form_data.start_time = "";
                        vm.recurring_appointment_device = "";
                    }else{
                        vm.recurring_appointment_time_loader_edit = true;
                        vm.recurring_edit_time = "";
                        vm.recurring_edit_appointment_time_slot = [];
                    }
                    vm.bookingpress_set_default_date_and_time_for_validation();
                },
                bookingpress_set_recurring_appointment_timeslot_formate(appointment_time){
                    const vm = this;
                    if(vm.bookigpress_time_format_for_booking_form == "1" || vm.bookigpress_time_format_for_booking_form == "2"){
                        return appointment_time.formatted_start_time+" to "+appointment_time.formatted_end_time;
                    }
                    if(vm.bookigpress_time_format_for_booking_form == "5" || vm.bookigpress_time_format_for_booking_form == "6"){
                        return appointment_time.formatted_start_time+" - "+appointment_time.formatted_end_time;
                    }
                    if(vm.bookigpress_time_format_for_booking_form == "3" || vm.bookigpress_time_format_for_booking_form == "4"){
                        return appointment_time.formatted_start_time;
                    }
                    return appointment_time.formatted_start_time+" - "+appointment_time.formatted_end_time;
                },
                bookingpress_set_recurring_start_time(event,time_slot_data,type=""){
                    const vm = this;
                    if(event != "" && time_slot_data != "") {
                        for (let x in time_slot_data) {                      
                            var slot_data_arr = time_slot_data[x];                        
                            for(let y in slot_data_arr) {
                                var time_slot_data_arr = slot_data_arr[y];
                                for(let m in time_slot_data_arr) {                                                                
                                    var data_arr  = time_slot_data_arr[m];
                                    if(data_arr.store_start_time != undefined && data_arr.store_end_time != undefined && data_arr.store_start_time == event){                                                                                 
                                        if(type != "edit"){
                                            vm.appointment_step_form_data.selected_start_time = data_arr.store_start_time;
                                            vm.appointment_step_form_data.selected_end_time = data_arr.store_end_time; 
                                            vm.appointment_step_form_data.selected_formatted_start_time = data_arr.formatted_start_time;
                                            vm.appointment_step_form_data.selected_formatted_end_time = data_arr.formatted_end_time; 
                                            vm.appointment_step_form_data.recurring_form_data.end_time = data_arr.store_end_time;
                                            vm.appointment_step_form_data.recurring_form_data.formatted_start_time = data_arr.formatted_start_time;
                                            vm.appointment_step_form_data.recurring_form_data.formatted_end_time = data_arr.formatted_end_time;                                            
                                        }                                     
                                    }
                                }                                                    
                            }                      
                        }                    
                    }
                    vm.bookingpress_set_default_date_and_time_for_validation();
                },
                bookingpress_check_previous_appointment(rkey){
                    const vm = this;
                    var all_recurring_appointments = vm.appointment_step_form_data.recurring_appointments;
                    var all_recurring_appointments_length = all_recurring_appointments.length;
                    if(rkey > 1){                        
                        if(rkey % 2 != 0) {
                            var newkey = rkey-1;
                            if(typeof all_recurring_appointments[newkey] != "undefined"){
                                var check_rec_data = all_recurring_appointments[newkey];
                                if(check_rec_data.is_suggested == 1 || check_rec_data.is_not_avaliable == 1){
                                    return true;
                                }
                            }
                        }else{
                            var newkey = rkey+1;
                            if(typeof all_recurring_appointments[newkey] != "undefined"){
                                var check_rec_data = all_recurring_appointments[newkey];
                                if(check_rec_data.is_suggested == 1 || check_rec_data.is_not_avaliable == 1){
                                    return true;
                                }
                            }                            
                        }
                    }else{
                        if(rkey == 0) {
                            var newkey = 1;
                            if(typeof all_recurring_appointments[newkey] != "undefined"){
                                var check_rec_data = all_recurring_appointments[newkey];
                                if(check_rec_data.is_suggested == 1 || check_rec_data.is_not_avaliable == 1){
                                    return true;
                                }                            
                            }
                        }                        
                        if(rkey == 1) {
                            var newkey = 0;
                            if(typeof all_recurring_appointments[newkey] != "undefined"){
                                var check_rec_data = all_recurring_appointments[newkey];
                                if(check_rec_data.is_suggested == 1 || check_rec_data.is_not_avaliable == 1){
                                    return true;
                                }
                            }
                        }                                               
                    }
                    return false;
                },
                bookingpress_recurring_appointment_get(){
                    
                    const vm = this;                
                    const CustformData = new FormData();
                    var selected_service_id = vm.appointment_step_form_data.selected_service;
                    var selected_date = vm.appointment_step_form_data.selected_date;                                        
                    vm.close_recurring_modal();
                    var bookingpress_service_expiration_date = "";
                    let recurring_selected_service_id = vm.appointment_step_form_data.selected_service;
                    if(recurring_selected_service_id != "" && "undefined" != typeof this.bookingpress_all_services_data[recurring_selected_service_id]){
                        if( "undefined" != typeof this.bookingpress_all_services_data[ recurring_selected_service_id ].bookingpress_service_expiration_date && "" != this.bookingpress_all_services_data[ recurring_selected_service_id ].bookingpress_service_expiration_date && null != this.bookingpress_all_services_data[ recurring_selected_service_id ].bookingpress_service_expiration_date ) {
                            bookingpress_service_expiration_date = this.bookingpress_all_services_data[ recurring_selected_service_id ].bookingpress_service_expiration_date;
                        }                    
                    }
                    var postData = { action:"bookingpress_get_recurring_appointments", service_id: selected_service_id, selected_date: selected_date, _wpnonce:"'.$bookingpress_nonce.'", };
                    ' . $bookingpress_dynamic_add_params_for_timeslot_request . ' 
                    vm.appointment_step_form_data.recurring_edit_index = "";
                    vm.appointment_step_form_data.recurring_appointments = [];
                    postData.bookingpress_service_expiration_date = bookingpress_service_expiration_date; 
                    postData.appointment_data_obj = JSON.stringify(vm.appointment_step_form_data);
                    postData.recurring_form_data = JSON.stringify(vm.appointment_step_form_data.recurring_form_data);
                    postData.selected_formatted_start_time = vm.appointment_step_form_data.selected_formatted_start_time;
                    postData.selected_formatted_end_time = vm.appointment_step_form_data.selected_formatted_end_time;                    
                    postData.bpa_change_store_date = false;
                    if( "undefined" != typeof vm.bookingpress_timezone_offset ){
                        postData.client_timezone_offset = vm.bookingpress_timezone_offset;
                        postData.bpa_change_store_date = true;                
                    }
                    if( "undefined" != typeof vm.bookingpress_dst_timezone ){
                        postData.client_dst_timezone = vm.bookingpress_dst_timezone;
                    }
                    vm.recurring_appointment_loader = true;
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                    .then( function (response) {
                        if(response.data.variant == "success"){
                            vm.appointment_step_form_data.recurring_appointments = response.data.recurring_appointments;                                                           
                            if(response.data.bookingpress_recurring_form_token){
                                vm.appointment_step_form_data.bookingpress_recurring_form_token = response.data.bookingpress_recurring_form_token;
                            }
                            vm.bookingpress_set_default_date_and_time_for_validation();
                            vm.calculate_recurring_appointment_total();                            
                        }else{                            
                            if(response.data.msg == "recurring_start_date_validation_message"){
                                vm.bookingpress_set_error_msg(vm.recurring_start_date_validation_message);
                            }else if(response.data.msg == "recurring_timeslot_validation_message"){
                                vm.bookingpress_set_error_msg(vm.recurring_timeslot_validation_message);
                            }else if(response.data.msg == "recurring_no_of_session_validation_message"){
                                vm.bookingpress_set_error_msg(vm.recurring_no_of_session_validation_message);
                            }else if(response.data.msg == "recurring_frequency_validation_message"){
                                vm.bookingpress_set_error_msg(vm.recurring_frequency_validation_message);
                            }else{
                                vm.bookingpress_set_error_msg(response.data.msg);
                            }
                            vm.appointment_step_form_data.recurring_appointments = [];
                        }
                        vm.recurring_appointment_loader = false;
                    }.bind(this) )
                    .catch( function (error) {
                        console.log(error);
                        vm.recurring_appointment_loader = false;
                    });                
                },
                bookingpress_set_default_date_and_time_for_validation(){
                    const vm = this;
                    if(vm.appointment_step_form_data.selected_start_time == ""){
                        vm.appointment_step_form_data.selected_start_time = vm.appointment_step_form_data.recurring_form_data.start_time;
                        vm.appointment_step_form_data.selected_end_time = vm.appointment_step_form_data.recurring_form_data.end_time;
                        vm.appointment_step_form_data.selected_formatted_start_time = vm.appointment_step_form_data.recurring_form_data.formatted_start_time;
                        vm.appointment_step_form_data.selected_formatted_end_time = vm.appointment_step_form_data.recurring_form_data.formatted_end_time;
                    }
                },
                bookingpress_navigate_to_recurring(){
                    const vm = this;
                    let sidebar_steps = vm.bookingpress_sidebar_step_data;
                    for( let step in sidebar_steps ){
                        let step_data = sidebar_steps[ step ];                    
                        let next_tab_name = step_data.next_tab_name;
                        let prev_tab_name = step_data.previous_tab_name;
                        vm.bookingpress_step_navigation( next_tab_name, next_tab_name, prev_tab_name );
                        if(step_data.next_tab_name == "recurring" ){
                            break;
                        }
                    }
                },

            ';
            return $bookingpress_vue_methods_data;
        }        

        /**
         * Function for check cart addon active or not
         *
         * @return void
         */
        function is_cart_addon_active(){
            $bookingpress_cart_addon  = 0;
            if(is_plugin_active('bookingpress-cart/bookingpress-cart.php')){
                $bookingpress_cart_addon = 1;
            }
            return $bookingpress_cart_addon;          
        }        

		/**
		 * Function for add customize form settings data to save request before data save
         * 
		 * @return void
		 */
        function bookingpress_before_save_customize_form_settings_func(){
        ?>
            postData.recurring_appointment_container_data = vm2.recurring_appointment_container_data;
        <?php
        }

		/**
		 * Function for execute code brfore save customize booking form data
		 *
		 * @param  mixed $booking_form_settings
		 * @return void
		 */
        function bookingpress_before_save_customize_booking_form_func($booking_form_settings_data){
            
            global $BookingPress;
            $booking_form_settings_data['recurring_appointment_container_data'] = !empty($_POST['recurring_appointment_container_data']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['recurring_appointment_container_data']) : array();  //phpcs:ignore
            return $booking_form_settings_data;

        }

		/**
		 * Function for add dynamic field to customize page
		 *
		 * @param  mixed $bookingpress_customize_vue_data_fields
		 * @return void
		 */
        function bookingpress_customize_add_dynamic_data_fields_func($bookingpress_customize_vue_data_fields) {

            global $BookingPress,$is_cart_addon_active;
            $recurring_appointment_step_label = $BookingPress->bookingpress_get_customize_settings('recurring_appointment_step_label','booking_form');
            $recurring_appointment_checkbox = $BookingPress->bookingpress_get_customize_settings('recurring_appointment_checkbox','booking_form');
            $recurring_appointment_start_date_title = $BookingPress->bookingpress_get_customize_settings('recurring_appointment_start_date_title','booking_form');
            $recurring_appointment_time_title = $BookingPress->bookingpress_get_customize_settings('recurring_appointment_time_title','booking_form');
            $recurring_appointment_no_of_session_title = $BookingPress->bookingpress_get_customize_settings('recurring_appointment_no_of_session_title','booking_form');
            $recurring_appointment_frequency_title = $BookingPress->bookingpress_get_customize_settings('recurring_appointment_frequency_title','booking_form');
            $recurring_appointment_apply_btn_title = $BookingPress->bookingpress_get_customize_settings('recurring_appointment_apply_btn_title','booking_form');
            $recurring_appointment_upcoming_appointment_title = $BookingPress->bookingpress_get_customize_settings('recurring_appointment_upcoming_appointment_title','booking_form');

            $recurring_start_date_validation_message = $BookingPress->bookingpress_get_settings('recurring_start_date_validation_message','message_setting');
            $recurring_timeslot_validation_message = $BookingPress->bookingpress_get_settings('recurring_timeslot_validation_message','message_setting');
            $recurring_no_of_session_validation_message = $BookingPress->bookingpress_get_settings('recurring_no_of_session_validation_message','message_setting');
            $recurring_frequency_validation_message = $BookingPress->bookingpress_get_settings('recurring_frequency_validation_message','message_setting');
            $recurring_appointment_add_validation_message = $BookingPress->bookingpress_get_settings('recurring_appointment_add_validation_message','message_setting');
            $recurring_not_avaliable_appointment_validation_message = $BookingPress->bookingpress_get_settings('recurring_not_avaliable_appointment_validation_message','message_setting');

            $recurring_suggested_message = $BookingPress->bookingpress_get_settings('recurring_suggested_message','message_setting');
            $recurring_not_avaliable_message = $BookingPress->bookingpress_get_settings('recurring_not_avaliable_message','message_setting');
            $recurring_edit_appointment_title = $BookingPress->bookingpress_get_customize_settings('recurring_edit_appointment_title','booking_form');
            $recurring_edit_appointment_date_label = $BookingPress->bookingpress_get_customize_settings('recurring_edit_appointment_date_label','booking_form');

            $recurring_appointment_session_title = $BookingPress->bookingpress_get_customize_settings('recurring_appointment_session_title','booking_form');
            $recurring_more_datetime_label = $BookingPress->bookingpress_get_customize_settings('recurring_more_datetime_label','booking_form');

            $recurring_daily_label = $BookingPress->bookingpress_get_customize_settings('recurring_daily_label','booking_form');
            $recurring_weekly_label = $BookingPress->bookingpress_get_customize_settings('recurring_weekly_label','booking_form');
            $recurring_biweekly_label = $BookingPress->bookingpress_get_customize_settings('recurring_biweekly_label','booking_form');
            $recurring_monthly_label = $BookingPress->bookingpress_get_customize_settings('recurring_monthly_label','booking_form');


            $bookingpress_customize_vue_data_fields['recurring_appointment_container_data']['recurring_daily_label'] = (!empty($recurring_daily_label))?stripslashes_deep($recurring_daily_label):'';
            $bookingpress_customize_vue_data_fields['recurring_appointment_container_data']['recurring_weekly_label'] = (!empty($recurring_weekly_label))?stripslashes_deep($recurring_weekly_label):'';
            $bookingpress_customize_vue_data_fields['recurring_appointment_container_data']['recurring_biweekly_label'] = (!empty($recurring_biweekly_label))?stripslashes_deep($recurring_biweekly_label):'';
            $bookingpress_customize_vue_data_fields['recurring_appointment_container_data']['recurring_monthly_label'] = (!empty($recurring_monthly_label))?stripslashes_deep($recurring_monthly_label):'';

            $bookingpress_customize_vue_data_fields['recurring_appointment_container_data']['recurring_appointment_checkbox'] = (!empty($recurring_appointment_checkbox))?stripslashes_deep($recurring_appointment_checkbox):'';
            $bookingpress_customize_vue_data_fields['recurring_appointment_container_data']['recurring_appointment_start_date_title'] = (!empty
            ($recurring_appointment_start_date_title))?stripslashes_deep($recurring_appointment_start_date_title):'';
            $bookingpress_customize_vue_data_fields['recurring_appointment_container_data']['recurring_appointment_time_title'] = (!empty($recurring_appointment_time_title))?stripslashes_deep($recurring_appointment_time_title):'';
            $bookingpress_customize_vue_data_fields['recurring_appointment_container_data']['recurring_appointment_no_of_session_title'] = (!empty($recurring_appointment_no_of_session_title))?stripslashes_deep($recurring_appointment_no_of_session_title):'';
            $bookingpress_customize_vue_data_fields['recurring_appointment_container_data']['recurring_appointment_frequency_title'] = (!empty($recurring_appointment_frequency_title))?stripslashes_deep($recurring_appointment_frequency_title):'';
            $bookingpress_customize_vue_data_fields['recurring_appointment_container_data']['recurring_appointment_apply_btn_title'] = (!empty($recurring_appointment_apply_btn_title))?stripslashes_deep($recurring_appointment_apply_btn_title):'';
            $bookingpress_customize_vue_data_fields['recurring_appointment_container_data']['recurring_appointment_upcoming_appointment_title'] = (!empty($recurring_appointment_upcoming_appointment_title))?stripslashes_deep($recurring_appointment_upcoming_appointment_title):'';
            $bookingpress_sidebar_step_data = $bookingpress_customize_vue_data_fields['bookingpress_form_sequance_arr'];

            $bookingpress_customize_vue_data_fields['recurring_appointment_container_data']['recurring_start_date_validation_message'] = (!empty($recurring_start_date_validation_message))?stripslashes_deep($recurring_start_date_validation_message):'';
            $bookingpress_customize_vue_data_fields['recurring_appointment_container_data']['recurring_timeslot_validation_message'] = (!empty($recurring_timeslot_validation_message))?stripslashes_deep($recurring_timeslot_validation_message):'';
            $bookingpress_customize_vue_data_fields['recurring_appointment_container_data']['recurring_no_of_session_validation_message'] = (!empty($recurring_no_of_session_validation_message))?stripslashes_deep($recurring_no_of_session_validation_message):'';
            $bookingpress_customize_vue_data_fields['recurring_appointment_container_data']['recurring_frequency_validation_message'] = (!empty($recurring_frequency_validation_message))?stripslashes_deep($recurring_frequency_validation_message):'';
            $bookingpress_customize_vue_data_fields['recurring_appointment_container_data']['recurring_appointment_add_validation_message'] = (!empty($recurring_appointment_add_validation_message))?stripslashes_deep($recurring_appointment_add_validation_message):'';
            $bookingpress_customize_vue_data_fields['recurring_appointment_container_data']['recurring_not_avaliable_appointment_validation_message'] = (!empty($recurring_not_avaliable_appointment_validation_message))?stripslashes_deep($recurring_not_avaliable_appointment_validation_message):'';

            $bookingpress_customize_vue_data_fields['recurring_appointment_container_data']['recurring_suggested_message'] = (!empty($recurring_suggested_message))?stripslashes_deep($recurring_suggested_message):'';
            $bookingpress_customize_vue_data_fields['recurring_appointment_container_data']['recurring_not_avaliable_message'] = (!empty($recurring_not_avaliable_message))?stripslashes_deep($recurring_not_avaliable_message):'';
            $bookingpress_customize_vue_data_fields['recurring_appointment_container_data']['recurring_edit_appointment_title'] = (!empty($recurring_edit_appointment_title))?stripslashes_deep($recurring_edit_appointment_title):'';
            $bookingpress_customize_vue_data_fields['recurring_appointment_container_data']['recurring_edit_appointment_date_label'] = (!empty($recurring_edit_appointment_date_label))?stripslashes_deep($recurring_edit_appointment_date_label):'';

            $recurring_appointment_suggested_timeslot_color = $BookingPress->bookingpress_get_customize_settings('recurring_appointment_suggested_timeslot_color','booking_form');
            $bookingpress_customize_vue_data_fields['recurring_appointment_container_data']['recurring_appointment_suggested_timeslot_color'] = (!empty($recurring_appointment_suggested_timeslot_color))?stripslashes_deep($recurring_appointment_suggested_timeslot_color):'';

            $recurring_appointment_booked_timelost_color = $BookingPress->bookingpress_get_customize_settings('recurring_appointment_booked_timelost_color','booking_form');
            $bookingpress_customize_vue_data_fields['recurring_appointment_container_data']['recurring_appointment_booked_timelost_color'] = (!empty($recurring_appointment_booked_timelost_color))?stripslashes_deep($recurring_appointment_booked_timelost_color):'';

            $bookingpress_customize_vue_data_fields['recurring_appointment_container_data']['recurring_appointment_session_title'] = (!empty($recurring_appointment_session_title))?stripslashes_deep($recurring_appointment_session_title):'';
            
            $bookingpress_customize_vue_data_fields['recurring_appointment_container_data']['recurring_more_datetime_label'] = (!empty($recurring_more_datetime_label))?stripslashes_deep($recurring_more_datetime_label):'';

            return $bookingpress_customize_vue_data_fields;

		}          

		/**
		 * Function for add data variables for customize page
		 *
		 * @param  mixed $booking_form_settings
		 * @return void
		*/        
		function bookingpress_get_booking_form_customize_data_filter_func($booking_form_settings){
			$booking_form_settings['recurring_appointment_container_data']['recurring_appointment_checkbox'] = __('Repeat appointment label', 'bookingpress-recurring-appointments');
            $booking_form_settings['recurring_appointment_container_data']['recurring_appointment_session_title'] = __('Sessions title', 'bookingpress-recurring-appointments');
            $booking_form_settings['recurring_appointment_container_data']['recurring_appointment_start_date_title'] = __('Start Date', 'bookingpress-recurring-appointments');
            $booking_form_settings['recurring_appointment_container_data']['recurring_appointment_time_title'] = __('Time', 'bookingpress-recurring-appointments');
            $booking_form_settings['recurring_appointment_container_data']['recurring_appointment_no_of_session_title'] = __('No of Sessions', 'bookingpress-recurring-appointments');
            $booking_form_settings['recurring_appointment_container_data']['recurring_appointment_frequency_title'] = __('Frequency', 'bookingpress-recurring-appointments');
            $booking_form_settings['recurring_appointment_container_data']['recurring_appointment_apply_btn_title'] = __('Apply', 'bookingpress-recurring-appointments');
            $booking_form_settings['recurring_appointment_container_data']['recurring_appointment_upcoming_appointment_title'] = __('Upcoming Appointments', 'bookingpress-recurring-appointments');

            $booking_form_settings['recurring_appointment_container_data']['recurring_edit_appointment_title'] = __('Edit Appointment', 'bookingpress-recurring-appointments');
            $booking_form_settings['recurring_appointment_container_data']['recurring_edit_appointment_date_label'] = __('Date', 'bookingpress-recurring-appointments');
            $booking_form_settings['recurring_appointment_container_data']['recurring_more_datetime_label'] = __('More', 'bookingpress-recurring-appointments');
            
            $booking_form_settings['recurring_appointment_container_data']['recurring_daily_label'] = __('Daily', 'bookingpress-recurring-appointments');
            $booking_form_settings['recurring_appointment_container_data']['recurring_weekly_label'] = __('Weekly', 'bookingpress-recurring-appointments');
            $booking_form_settings['recurring_appointment_container_data']['recurring_biweekly_label'] = __('Biweekly', 'bookingpress-recurring-appointments');
            $booking_form_settings['recurring_appointment_container_data']['recurring_monthly_label'] = __('Monthly', 'bookingpress-recurring-appointments');

            return $booking_form_settings;
		}

        /**
         * Function For add field HTML to customize page
         *
         * @return void
         */
        public function bookingpress_add_bookingform_label_data_func(){
        ?>
            <h5><?php esc_html_e('Recurring Labels', 'bookingpress-recurring-appointments'); ?></h5>                                                    
            <div class="bpa-sm--item">
                <label class="bpa-form-label"><?php esc_html_e('Repeat Appointment Label', 'bookingpress-recurring-appointments'); ?></label>
                <el-input v-model="recurring_appointment_container_data.recurring_appointment_checkbox" class="bpa-form-control"></el-input>
            </div>    
            <div class="bpa-sm--item">
                <label class="bpa-form-label"><?php esc_html_e('Sessions Title', 'bookingpress-recurring-appointments'); ?></label>
                <el-input v-model="recurring_appointment_container_data.recurring_appointment_session_title" class="bpa-form-control"></el-input>
            </div> 
            <div class="bpa-sm--item">
                <label class="bpa-form-label"><?php esc_html_e('Start Date', 'bookingpress-recurring-appointments'); ?></label>
                <el-input v-model="recurring_appointment_container_data.recurring_appointment_start_date_title" class="bpa-form-control"></el-input>
            </div> 
            <div class="bpa-sm--item">
                <label class="bpa-form-label"><?php esc_html_e('Time', 'bookingpress-recurring-appointments'); ?></label>
                <el-input v-model="recurring_appointment_container_data.recurring_appointment_time_title" class="bpa-form-control"></el-input>
            </div> 
            <div class="bpa-sm--item">
                <label class="bpa-form-label"><?php esc_html_e('No of Sessions', 'bookingpress-recurring-appointments'); ?></label>
                <el-input v-model="recurring_appointment_container_data.recurring_appointment_no_of_session_title" class="bpa-form-control"></el-input>
            </div> 
            <div class="bpa-sm--item">
                <label class="bpa-form-label"><?php esc_html_e('Frequency', 'bookingpress-recurring-appointments'); ?></label>
                <el-input v-model="recurring_appointment_container_data.recurring_appointment_frequency_title" class="bpa-form-control"></el-input>
            </div> 
            <div class="bpa-sm--item">
                <label class="bpa-form-label"><?php esc_html_e('Apply', 'bookingpress-recurring-appointments'); ?></label>
                <el-input v-model="recurring_appointment_container_data.recurring_appointment_apply_btn_title" class="bpa-form-control"></el-input>
            </div> 
            <div class="bpa-sm--item">
                <label class="bpa-form-label"><?php esc_html_e('Upcoming Appointments', 'bookingpress-recurring-appointments'); ?></label>
                <el-input v-model="recurring_appointment_container_data.recurring_appointment_upcoming_appointment_title" class="bpa-form-control"></el-input>
            </div>      
            <div class="bpa-sm--item">
                <label class="bpa-form-label"><?php esc_html_e('Edit Appointment', 'bookingpress-recurring-appointments'); ?></label>
                <el-input v-model="recurring_appointment_container_data.recurring_edit_appointment_title" class="bpa-form-control"></el-input>
            </div> 
            <div class="bpa-sm--item">
                <label class="bpa-form-label"><?php esc_html_e('Date', 'bookingpress-recurring-appointments'); ?></label>
                <el-input v-model="recurring_appointment_container_data.recurring_edit_appointment_date_label" class="bpa-form-control"></el-input>
            </div> 
            <div class="bpa-sm--item">
                <label class="bpa-form-label"><?php esc_html_e('More Service Date Time Label', 'bookingpress-recurring-appointments'); ?></label>
                <el-input v-model="recurring_appointment_container_data.recurring_more_datetime_label" class="bpa-form-control"></el-input>
            </div> 
            <div class="bpa-sm--item">
                <label class="bpa-form-label"><?php esc_html_e('Daily Label', 'bookingpress-recurring-appointments'); ?></label>
                <el-input v-model="recurring_appointment_container_data.recurring_daily_label" class="bpa-form-control"></el-input>
            </div>
            <div class="bpa-sm--item">
                <label class="bpa-form-label"><?php esc_html_e('Weekly Label', 'bookingpress-recurring-appointments'); ?></label>
                <el-input v-model="recurring_appointment_container_data.recurring_weekly_label" class="bpa-form-control"></el-input>
            </div>
            <div class="bpa-sm--item">
                <label class="bpa-form-label"><?php esc_html_e('Biweekly Label', 'bookingpress-recurring-appointments'); ?></label>
                <el-input v-model="recurring_appointment_container_data.recurring_biweekly_label" class="bpa-form-control"></el-input>
            </div>
            <div class="bpa-sm--item">
                <label class="bpa-form-label"><?php esc_html_e('Monthly Label', 'bookingpress-recurring-appointments'); ?></label>
                <el-input v-model="recurring_appointment_container_data.recurring_monthly_label" class="bpa-form-control"></el-input>
            </div>            
        <?php
        }

        /**
         * Function for edit service more vue data
         *
         * @return void
         */
        function bookingpress_edit_service_more_vue_data_func(){
        ?>                        
            vm2.service.recurring_frequencies = (response.data.recurring_frequencies !== undefined) ? response.data.recurring_frequencies : [];
            vm2.service.enable_recurring_appointments = (response.data.enable_recurring_appointments == 1)?true:false;
            vm2.service.default_recurring_frequencies = (response.data.default_recurring_frequencies !== undefined) ? response.data.default_recurring_frequencies : '';
            vm2.service.recurring_appointments_max_no_of_times = (response.data.recurring_appointments_max_no_of_times !== undefined) ? response.data.recurring_appointments_max_no_of_times : 0;
        <?php
        }
		
		/**
		 * Function for reset form data when new service added
		 *
		 * @return void
		 */
		function bookingpress_after_open_add_service_model_func() {
		?>			
            vm.service.recurring_frequencies = [];
            vm.service.enable_recurring_appointments = false;
            vm.service.default_recurring_frequencies = '';
            vm.service.recurring_appointments_max_no_of_times = 0;            
		<?php
		}   

        /**
         * Function for save service details
         *
         * @param  mixed $response
         * @param  mixed $service_id
         * @param  mixed $posted_data
         * @return void
         */
        function bookingpress_save_service_details( $response, $service_id, $posted_data ){ 
            global $BookingPress, $bookingpress_services;
            if ( ! empty( $service_id ) && ! empty( $posted_data ) ) {
				$enable_recurring_appointments = (isset( $posted_data['enable_recurring_appointments'] )) ? $posted_data['enable_recurring_appointments'] : 'false';
                if($enable_recurring_appointments == 'true'){
                    $enable_recurring_appointments = '1';
                }else{
                    $enable_recurring_appointments = '0';
                }
                $default_recurring_frequencies = (isset( $posted_data['default_recurring_frequencies'] )) ? $posted_data['default_recurring_frequencies'] : '';
                $recurring_appointments_max_no_of_times = (isset( $posted_data['recurring_appointments_max_no_of_times'] )) ? $posted_data['recurring_appointments_max_no_of_times'] : 0;

                $bookingpress_services->bookingpress_add_service_meta( $service_id, 'default_recurring_frequencies', $default_recurring_frequencies );
                $bookingpress_services->bookingpress_add_service_meta( $service_id, 'recurring_appointments_max_no_of_times', $recurring_appointments_max_no_of_times );

                $bookingpress_services->bookingpress_add_service_meta( $service_id, 'enable_recurring_appointments', $enable_recurring_appointments );
                $recurring_frequencies = (isset( $posted_data['recurring_frequencies'] )) ? $posted_data['recurring_frequencies'] : '';                                               
                if(is_array($recurring_frequencies)){
                    $recurring_frequencies = implode(',',$recurring_frequencies);
                }else{
                    $recurring_frequencies = '';
                }                
				$bookingpress_services->bookingpress_add_service_meta( $service_id, 'recurring_frequencies', $recurring_frequencies );
				                	
            }
            return $response;
        }

        /**
         * Function for modify edit service data
         *
         * @param  mixed $response
         * @param  mixed $service_id
         * @return void
         */        
        function bookingpress_modify_edit_service_data_func( $response,$service_id ) {
            global $bookingpress_services, $default_recurring_appointments_max_no_of_times,$all_recurring_frequencies;
            
            $recurring_frequencies = $bookingpress_services->bookingpress_get_service_meta($service_id, 'recurring_frequencies');
            $enable_recurring_appointments = $bookingpress_services->bookingpress_get_service_meta($service_id, 'enable_recurring_appointments');

            $default_recurring_frequencies = $bookingpress_services->bookingpress_get_service_meta($service_id, 'default_recurring_frequencies');
            $recurring_appointments_max_no_of_times = $bookingpress_services->bookingpress_get_service_meta($service_id, 'recurring_appointments_max_no_of_times');
            
            if(!empty($recurring_frequencies)){
                $recurring_frequencies = explode(',',$recurring_frequencies);
            }
            $response['enable_recurring_appointments'] = $enable_recurring_appointments;
            $response['recurring_frequencies'] = $recurring_frequencies;

            $response['default_recurring_frequencies'] = $default_recurring_frequencies;
            $response['recurring_appointments_max_no_of_times'] = $recurring_appointments_max_no_of_times;
                       
            $response['all_recurring_frequencies'] = $all_recurring_frequencies;
            return $response;
        }

        /**
         * Function for modify service data fields
         *
         * @param  mixed $bookingpress_services_vue_data_fields
         * @return void
        */
        public function bookingpress_modify_service_data_fields_func($bookingpress_services_vue_data_fields){            
            global $bookingpress_services, $default_recurring_appointments_max_no_of_times,$all_recurring_frequencies;                    
            
            $bookingpress_services_vue_data_fields['service']['recurring_frequencies'] = [];
            $bookingpress_services_vue_data_fields['service']['enable_recurring_appointments'] = false;
            $bookingpress_services_vue_data_fields['all_recurring_frequencies'] = $all_recurring_frequencies;
            $bookingpress_services_vue_data_fields['service']['recurring_appointments_max_no_of_times'] = 0;            
            $bookingpress_services_vue_data_fields['service']['default_recurring_frequencies'] = '';
            $bookingpress_services_vue_data_fields['rules']['recurring_frequencies'] = array(
                'required' => true,
                'message'  => esc_html__('Please Select Recurring Frequency', 'bookingpress-recurring-appointments'),
                'trigger'  => 'blur',
            );
            $bookingpress_services_vue_data_fields['rules']['default_recurring_frequencies'] = array(
                'required' => true,
                'message'  => esc_html__('Please Select Default Recurring Frequency', 'bookingpress-recurring-appointments'),
                'trigger'  => 'blur',
            );

            return $bookingpress_services_vue_data_fields;
        }
        
        
        /**
         * Function for add dynamic vue service method
         *
         * @return void
         */
        function bookingpress_add_service_dynamic_vue_methods_func(){
        ?>
			bookingpress_change_recurring_feaquncy(){
				const vm = this
				vm.service.default_recurring_frequencies = '';
			},        
        <?php 
        }

        /**
         * Function for add new fields with service deposit in service
         *
         * @return void
        */
        public function bookingpress_add_service_field_outside_fun(){
            global $bookingpress_max_no_of_recurring;
        ?>
        <div>
            <div class="bpa-form-body-row bpa-form-body-row-recurring bpa-deposit-payment__heading"></div>									
            <div class="bpa-form-body-row bpa-form-body-row-recurring">

                <div class="bpa-rec-sec-heading">
                    <el-row :gutter="32" type="flex" align="middle">
                        <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                            <div class="db-sec-left">
                                <span class="bpa-recurring-serv__heading"><?php esc_html_e('Enable Recurring Appointments', 'bookingpress-recurring-appointments' ); ?></span>
                            </div>
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                            <div class="bpa-hw-right-btn-group">
                                <el-form-item>  
                                    <el-switch class="bpa-swtich-control" v-model="service.enable_recurring_appointments" ></el-switch>
                                </el-form-item>  
                            </div>
                        </el-col>                            
                    </el-row>
                </div>            	
                <el-row :gutter="32">                     
                    <el-col v-if="service.enable_recurring_appointments" :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
                        <el-form-item>
                            <template #label>
                                <span class="bpa-form-label"><?php esc_html_e( 'Max no of occurance', 'bookingpress-recurring-appointments' ); ?></span>            
                            </template>
                            <el-input-number class="bpa-form-control bpa-form-control--number" :min="2" :max="<?php echo intval($bookingpress_max_no_of_recurring); ?>" v-model="service.recurring_appointments_max_no_of_times" id="recurring_appointments_max_no_of_times" name="recurring_appointments_max_no_of_times" step-strictly></el-input-number>                            
                        </el-form-item>
                    </el-col>                   	
                    <el-col v-if="service.enable_recurring_appointments" :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
                        <el-form-item prop="recurring_frequencies">
                            <template #label>
                                    <span class="bpa-form-label"><?php esc_html_e('Recurring Frequencies (You can select multiple options)', 'bookingpress-recurring-appointments' ); ?></span>            
                            </template>                                                
                            <el-select @change="bookingpress_change_recurring_feaquncy()" class="bpa-form-control" id="recurring_frequencies" multiple name="recurring_frequencies" v-model="service.recurring_frequencies" 
                                placeholder="<?php esc_html_e( 'Recurring Frequencies', 'bookingpress-recurring-appointments' ); ?>" >								
                                <el-option v-for="item in all_recurring_frequencies" :selected="service.recurring_frequencies.lenght > 0" :key="item.text" :label="item.text" :value="item.value"></el-option>	
                            </el-select> 
                        </el-form-item>                       
                    </el-col>
                    <el-col v-if="service.enable_recurring_appointments && service.recurring_frequencies.lenght != 0" :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
                        <el-form-item prop="default_recurring_frequencies">
                            <template #label>
                                    <span class="bpa-form-label"><?php esc_html_e('Default Frequency', 'bookingpress-recurring-appointments' ); ?></span>            
                            </template>                                                
                            <el-select class="bpa-form-control" id="default_recurring_frequencies" name="default_recurring_frequencies" v-model="service.default_recurring_frequencies" 
                                placeholder="<?php esc_html_e( 'Default Recurring Frequencies', 'bookingpress-recurring-appointments' ); ?>" >								
                                <el-option v-for="item in all_recurring_frequencies" v-if="service.recurring_frequencies.includes(item.value)" :key="item.text" :label="item.text" :value="item.value"></el-option>	
                            </el-select> 
                        </el-form-item>                       
                    </el-col>
                </el-row>
            </div>
        </div>        
        <?php
        }

        /**
         * Payment Settings Vue Variable Data added
         *
         * @param  mixed $bookingpress_dynamic_setting_data_fields
         * @return void
         */
        function bookingpress_add_setting_dynamic_data_fields_func($bookingpress_dynamic_setting_data_fields) {            
            $bookingpress_dynamic_setting_data_fields['payment_setting_form']['bookingpress_recurring_appointments_pay'] = 0;

            $bookingpress_dynamic_setting_data_fields['message_setting_form']['recurring_start_date_validation_message'] = '';
            $bookingpress_dynamic_setting_data_fields['message_setting_form']['recurring_timeslot_validation_message'] = '';
            $bookingpress_dynamic_setting_data_fields['message_setting_form']['recurring_no_of_session_validation_message'] = '';
            $bookingpress_dynamic_setting_data_fields['message_setting_form']['recurring_frequency_validation_message'] = '';
            $bookingpress_dynamic_setting_data_fields['message_setting_form']['recurring_appointment_add_validation_message'] = '';
            $bookingpress_dynamic_setting_data_fields['message_setting_form']['recurring_not_avaliable_appointment_validation_message'] = '';
            $bookingpress_dynamic_setting_data_fields['message_setting_form']['recurring_suggested_message'] = '';
            $bookingpress_dynamic_setting_data_fields['message_setting_form']['recurring_not_avaliable_message'] = '';

			$bookingpress_dynamic_setting_data_fields['rules_message']['recurring_start_date_validation_message']= array(
				array(
					'required' => true,
					'message'  => esc_html__('Please enter message', 'bookingpress-recurring-appointments'),
					'trigger'  => 'blur',
				),
			);	
            $bookingpress_dynamic_setting_data_fields['rules_message']['recurring_timeslot_validation_message']= array(
				array(
					'required' => true,
					'message'  => esc_html__('Please enter message', 'bookingpress-recurring-appointments'),
					'trigger'  => 'blur',
				),
			);	
            $bookingpress_dynamic_setting_data_fields['rules_message']['recurring_no_of_session_validation_message']= array(
				array(
					'required' => true,
					'message'  => esc_html__('Please enter message', 'bookingpress-recurring-appointments'),
					'trigger'  => 'blur',
				),
			);	
            $bookingpress_dynamic_setting_data_fields['rules_message']['recurring_frequency_validation_message']= array(
				array(
					'required' => true,
					'message'  => esc_html__('Please enter message', 'bookingpress-recurring-appointments'),
					'trigger'  => 'blur',
				),
			);	
            $bookingpress_dynamic_setting_data_fields['rules_message']['recurring_appointment_add_validation_message']= array(
				array(
					'required' => true,
					'message'  => esc_html__('Please enter message', 'bookingpress-recurring-appointments'),
					'trigger'  => 'blur',
				),
			);	
            $bookingpress_dynamic_setting_data_fields['rules_message']['recurring_not_avaliable_appointment_validation_message']= array(
				array(
					'required' => true,
					'message'  => esc_html__('Please enter message', 'bookingpress-recurring-appointments'),
					'trigger'  => 'blur',
				),
			);	
            $bookingpress_dynamic_setting_data_fields['rules_message']['recurring_suggested_message']= array(
				array(
					'required' => true,
					'message'  => esc_html__('Please enter message', 'bookingpress-recurring-appointments'),
					'trigger'  => 'blur',
				),
			);	
            $bookingpress_dynamic_setting_data_fields['rules_message']['recurring_not_avaliable_message']= array(
				array(
					'required' => true,
					'message'  => esc_html__('Please enter message', 'bookingpress-recurring-appointments'),
					'trigger'  => 'blur',
				),
			);	
            /* Invoice Tag added */
            
            if(is_plugin_active('bookingpress-invoice/bookingpress-invoice.php')){
                if(isset($bookingpress_dynamic_setting_data_fields['bookingpress_invoice_tag_list'])){
                    $bookingpress_invoice_tag_list = (isset($bookingpress_dynamic_setting_data_fields['bookingpress_invoice_tag_list']))?$bookingpress_dynamic_setting_data_fields['bookingpress_invoice_tag_list']:array();
                    $bookingpress_invoice_tag_list[] =  
                    array( 
                        'group_tag_name' =>  'recurring',
                        'tag_details' => array(      
                            array( 'tag_name' =>  '[BOOKINGPRESS_RECURRING_ITEMS] [/BOOKINGPRESS_RECURRING_ITEMS]'),
                        ),
                    );
                    $bookingpress_dynamic_setting_data_fields['bookingpress_invoice_tag_list'] = $bookingpress_invoice_tag_list; 
                }                
            }
            

            return $bookingpress_dynamic_setting_data_fields;            
        }
        
        /**
         * Payment Settings HTML Added
         *
         * @return void
         */
        function bookingpress_add_payment_settings_section_func() {
        ?>
            <div class="bpa-gs__cb--item">
                <div class="bpa-gs__cb--item-heading">
                    <h4 class="bpa-sec--sub-heading"><?php esc_html_e('Recurring Appointments', 'bookingpress-recurring-appointments'); ?></h4>
                </div>
                <div class="bpa-gs__cb--item-body">
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">                        
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Choose one of the payment options', 'bookingpress-recurring-appointments'); ?></h4>
                        </el-col>
						<el-col :xs="16" :sm="16" :md="16" :lg="16" :xl="16" class="bpa-gs__cb-item-right bpa-modal-radio-controls">
							<el-radio v-model="payment_setting_form.bookingpress_recurring_appointments_pay" label="pay_first"><?php esc_html_e( 'Pay for the first appointment', 'bookingpress-recurring-appointments' ); ?></el-radio>
							<el-radio v-model="payment_setting_form.bookingpress_recurring_appointments_pay" label="pay_all"><?php esc_html_e( 'Pay for all appointments', 'bookingpress-recurring-appointments' ); ?></el-radio>
						</el-col>                      
                    </el-row>
                </div>                                                            
            </div>    
        <?php
        }

        public static function install(){              
            global  $recurring_appointments_list_version,$wpdb,$tbl_bookingpress_customize_settings,$tbl_bookingpress_entries,$bookingpress_waiting_list_version,$BookingPress,$tbl_bookingpress_appointment_bookings;
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';                                                            
            $recurring_appointments_list_version_db = get_option('recurring_appointments_list_version');  
            if (!isset($recurring_appointments_list_version_db) || $recurring_appointments_list_version_db == ''){
				
				
				$myaddon_name = "bookingpress-recurring-appointments/bookingpress-recurring-appointments.php";
                
                // activate license for this addon
                $posted_license_key = trim( get_option( 'bkp_license_key' ) );
			    $posted_license_package = '26562';

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
                    $message =  ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) ) ? $response->get_error_message() : __( 'An error occurred, please try again.','bookingpress-recurring-appointments' );
                } else {
                    $license_data = json_decode( wp_remote_retrieve_body( $response ) );
                    $license_data_string = wp_remote_retrieve_body( $response );
                    if ( false === $license_data->success ) {
                        switch( $license_data->error ) {
                            case 'expired' :
                                $message = sprintf(
                                    __( 'Your license key expired on %s.','bookingpress-recurring-appointments' ),
                                    date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
                                );
                                break;
                            case 'revoked' :
                                $message = __( 'Your license key has been disabled.','bookingpress-recurring-appointments' );
                                break;
                            case 'missing' :
                                $message = __( 'Invalid license.','bookingpress-recurring-appointments' );
                                break;
                            case 'invalid' :
                            case 'site_inactive' :
                                $message = __( 'Your license is not active for this URL.','bookingpress-recurring-appointments' );
                                break;
                            case 'item_name_mismatch' :
                                $message = __('This appears to be an invalid license key for your selected package.','bookingpress-recurring-appointments');
                                break;
                            case 'invalid_item_id' :
                                    $message = __('This appears to be an invalid license key for your selected package.','bookingpress-recurring-appointments');
                                    break;
                            case 'no_activations_left':
                                $message = __( 'Your license key has reached its activation limit.','bookingpress-recurring-appointments' );
                                break;
                            default :
                                $message = __( 'An error occurred, please try again.','bookingpress-recurring-appointments' );
                                break;
                        }

                    }

                }

                if ( ! empty( $message ) ) {
                    update_option( 'bkp_recurring_appointments_license_data_activate_response', $license_data_string );
                    update_option( 'bkp_recurring_appointments_license_status', $license_data->license );
                    deactivate_plugins($myaddon_name, FALSE);
                    $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name);
                    $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Recurring Appointments Add-on', 'bookingpress-recurring-appointments');
					$bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-recurring-appointments'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
					wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                    die;
                }
                
                if($license_data->license === "valid")
                {
                    update_option( 'bkp_recurring_appointments_license_key', $posted_license_key );
                    update_option( 'bkp_recurring_appointments_license_package', $posted_license_package );
                    update_option( 'bkp_recurring_appointments_license_status', $license_data->license );
                    update_option( 'bkp_recurring_appointments_license_data_activate_response', $license_data_string );
                } 	
				
				
				
				
                update_option('recurring_appointments_list_version',$recurring_appointments_list_version);
                $BookingPress->bookingpress_update_settings('bookingpress_recurring_appointments_pay', 'payment_setting', 'pay_all');                
                $booking_form = array(
                    'recurring_appointment_checkbox' => __('Repeat appointment', 'bookingpress-recurring-appointments'),
                    'recurring_appointment_session_title' => __('Sessions', 'bookingpress-recurring-appointments'),
                    'recurring_appointment_start_date_title' => __('Start Date', 'bookingpress-recurring-appointments'),
                    'recurring_appointment_time_title' => __('Time', 'bookingpress-recurring-appointments'),
                    'recurring_appointment_no_of_session_title' => __('No of Sessions', 'bookingpress-recurring-appointments'),
                    'recurring_appointment_frequency_title' => __('Frequency', 'bookingpress-recurring-appointments'),
                    'recurring_appointment_apply_btn_title' => __('Apply', 'bookingpress-recurring-appointments'),
                    'recurring_appointment_upcoming_appointment_title' => __('Upcoming Appointments', 'bookingpress-recurring-appointments'),
                    'recurring_edit_appointment_title' => __('Edit Appointment', 'bookingpress-recurring-appointments'),
                    'recurring_edit_appointment_date_label' => __('Date', 'bookingpress-recurring-appointments'),
                    'recurring_appointment_suggested_timeslot_color' => '#F5AE41',
                    'recurring_appointment_booked_timelost_color' => '#ff0000',
                    'recurring_more_datetime_label' => __('More', 'bookingpress-recurring-appointments'),
                    'recurring_daily_label' => __('Daily', 'bookingpress-recurring-appointments'),
                    'recurring_weekly_label' => __('Weekly', 'bookingpress-recurring-appointments'),
                    'recurring_biweekly_label' => __('Biweekly', 'bookingpress-recurring-appointments'),
                    'recurring_monthly_label' => __('Monthly', 'bookingpress-recurring-appointments'),                    
                );
                foreach($booking_form as $key => $value) {
                    $bookingpress_get_customize_text = $BookingPress->bookingpress_get_customize_settings($key, 'booking_form');
                    if(empty($bookingpress_get_customize_text)){
                        $bookingpress_customize_settings_db_fields = array(
                            'bookingpress_setting_name'  => $key,
                            'bookingpress_setting_value' => $value,
                            'bookingpress_setting_type'  => 'booking_form',
                        );
                        $wpdb->insert( $tbl_bookingpress_customize_settings, $bookingpress_customize_settings_db_fields );
                    }
                }
                
                $tbl_bookingpress_entries = $wpdb->prefix . 'bookingpress_entries';
                    $bookingpress_is_recurring_entry_exists = $wpdb->get_row("SHOW COLUMNS FROM {$tbl_bookingpress_entries} LIKE 'bookingpress_is_recurring'");// phpcs:ignore 
                    if(empty($bookingpress_is_recurring_entry_exists)){
                        $wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_is_recurring int(11) DEFAULT 0 AFTER bookingpress_complete_payment_token" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
                    }
                    //Add columns to appointment booking table for store appointment related location details
                    $tbl_bookingpress_appointment_bookings = $wpdb->prefix . 'bookingpress_appointment_bookings';
                    $bookingpress_is_recurring_booking_exists = $wpdb->get_row("SHOW COLUMNS FROM {$tbl_bookingpress_appointment_bookings} LIKE 'bookingpress_is_recurring'");// phpcs:ignore 
                    if(empty($bookingpress_is_recurring_booking_exists)){
                        $wpdb->query( "ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_is_recurring int(11) DEFAULT 0 AFTER bookingpress_complete_payment_token" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
                    }
                    //Add columns to payment table for store payment related location details                    
                    $tbl_bookingpress_payment_logs  = $wpdb->prefix . 'bookingpress_payment_transactions';
                    $bookingpress_is_recurring_payment_exists = $wpdb->get_row("SHOW COLUMNS FROM {$tbl_bookingpress_payment_logs} LIKE 'bookingpress_is_recurring'");// phpcs:ignore 
                    if(empty($bookingpress_is_recurring_payment_exists)){
                        $wpdb->query( "ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_is_recurring int(11) DEFAULT 0 AFTER bookingpress_complete_payment_token" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm
                    }    
                $bookingpress_rec_message_settings = array(
                    'recurring_start_date_validation_message' => __('Please select recurring start date.', 'bookingpress-recurring-appointments'),
                    'recurring_timeslot_validation_message' => __('Please select recurring timeslot.', 'bookingpress-recurring-appointments'),
                    'recurring_no_of_session_validation_message' => __('Please select recurring no of session.', 'bookingpress-recurring-appointments'),
                    'recurring_frequency_validation_message' => __('Please select recurring frequency.', 'bookingpress-recurring-appointments'),
                    'recurring_appointment_add_validation_message' => __('Please add recurring appointment.', 'bookingpress-recurring-appointments'),
                    'recurring_not_avaliable_appointment_validation_message' => __('Please select date & time for not available appointment.', 'bookingpress-recurring-appointments'),
                    'recurring_suggested_message' => __('Suggested time slot due to unavailability.', 'bookingpress-recurring-appointments'),
                    'recurring_not_avaliable_message' => __('Selected or suggested time slot not available.', 'bookingpress-recurring-appointments'),
                );
                foreach($bookingpress_rec_message_settings as $bookingpress_rec_message_settings_ley => $bookingpress_rec_message_settings_val) {
                    $BookingPress->bookingpress_update_settings($bookingpress_rec_message_settings_ley,'message_setting', $bookingpress_rec_message_settings_val );
                }


                    //Regenerate Bookingpress CSS Start                    
                    $bookingpress_custom_data_arr = array();
                    $bookingpress_background_color = $BookingPress->bookingpress_get_customize_settings('background_color', 'booking_form');
                    $bookingpress_footer_background_color = $BookingPress->bookingpress_get_customize_settings('footer_background_color', 'booking_form');
                    $bookingpress_primary_color = $BookingPress->bookingpress_get_customize_settings('primary_color', 'booking_form');
                    $bookingpress_content_color = $BookingPress->bookingpress_get_customize_settings('content_color', 'booking_form');
                    $bookingpress_label_title_color = $BookingPress->bookingpress_get_customize_settings('label_title_color', 'booking_form');
                    $bookingpress_title_font_family = $BookingPress->bookingpress_get_customize_settings('title_font_family', 'booking_form');        
                    $bookingpress_sub_title_color = $BookingPress->bookingpress_get_customize_settings('sub_title_color', 'booking_form');
                    $bookingpress_price_button_text_color = $BookingPress->bookingpress_get_customize_settings('price_button_text_color', 'booking_form');    
                    $bookingpress_primary_background_color = $BookingPress->bookingpress_get_customize_settings('primary_background_color', 'booking_form');
                    $bookingpress_border_color= $BookingPress->bookingpress_get_customize_settings('border_color', 'booking_form');
                    
                    $bookingpress_background_color = !empty($bookingpress_background_color) ? $bookingpress_background_color : '#fff';
                    $bookingpress_footer_background_color = !empty($bookingpress_footer_background_color) ? $bookingpress_footer_background_color : '#f4f7fb';
                    $bookingpress_primary_color = !empty($bookingpress_primary_color) ? $bookingpress_primary_color : '#12D488';
                    $bookingpress_content_color = !empty($bookingpress_content_color) ? $bookingpress_content_color : '#727E95';
                    $bookingpress_label_title_color = !empty($bookingpress_label_title_color) ? $bookingpress_label_title_color : '#202C45';
                    $bookingpress_title_font_family = !empty($bookingpress_title_font_family) ? $bookingpress_title_font_family : '';    
                    $bookingpress_sub_title_color = !empty($bookingpress_sub_title_color) ? $bookingpress_sub_title_color : '#535D71';
                    $bookingpress_price_button_text_color = !empty($bookingpress_price_button_text_color) ? $bookingpress_price_button_text_color : '#fff';    
                    $bookingpress_primary_background_color = !empty($bookingpress_primary_background_color) ? $bookingpress_primary_background_color : '#e2faf1';
                    $bookingpress_border_color = !empty($bookingpress_border_color) ? $bookingpress_border_color : '#CFD6E5';
                    
                    $bookingpress_custom_data_arr['action'][] = 'bookingpress_save_my_booking_settings';
                    $bookingpress_custom_data_arr['action'][] = 'bookingpress_save_booking_form_settings';

                    $my_booking_form = array(
                        'background_color' => $bookingpress_background_color,
                        'row_background_color' => $bookingpress_footer_background_color,
                        'primary_color' => $bookingpress_primary_color,
                        'content_color' => $bookingpress_content_color,
                        'label_title_color' => $bookingpress_label_title_color,
                        'title_font_family' => $bookingpress_title_font_family,        
                        'sub_title_color'   => $bookingpress_sub_title_color,
                        'price_button_text_color' => $bookingpress_price_button_text_color,        
                        'border_color'         => $bookingpress_border_color,
                    );
                    $booking_form = array(
                        'background_color' => $bookingpress_background_color,
                        'footer_background_color' => $bookingpress_footer_background_color,
                        'primary_color' => $bookingpress_primary_color,
                        'primary_background_color'=> $bookingpress_primary_background_color,
                        'label_title_color' => $bookingpress_label_title_color,
                        'title_font_family' => $bookingpress_title_font_family,                
                        'content_color' => $bookingpress_content_color,                
                        'price_button_text_color' => $bookingpress_price_button_text_color,
                        'sub_title_color' => $bookingpress_sub_title_color,
                        'border_color'         => $bookingpress_border_color,
                    );
                    $bookingpress_custom_data_arr['booking_form'] = $booking_form;
                    $bookingpress_custom_data_arr['my_booking_form'] = $my_booking_form;
                    $BookingPress->bookingpress_generate_customize_css_func($bookingpress_custom_data_arr);
                    //Regenerate Bookingpress CSS Over

            }
        }

        public static function uninstall(){
            delete_option('recurring_appointments_list_version');
			
			delete_option('bkp_recurring_appointments_license_key');
            delete_option('bkp_recurring_appointments_license_package');
            delete_option('bkp_recurring_appointments_license_status');
            delete_option('bkp_recurring_appointments_license_data_activate_response');
			
        }

    }

	global $bookingpress_recurring_appointments;
	$bookingpress_recurring_appointments = new bookingpress_recurring_appointments();    

}
