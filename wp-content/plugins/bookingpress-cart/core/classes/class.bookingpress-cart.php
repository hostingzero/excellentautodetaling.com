<?php

if (!class_exists('bookingpress_cart') && class_exists( 'BookingPress_Core' ) ) {
	class bookingpress_cart extends BookingPress_Core {
		function __construct() {

            global $BookingPress;

            register_activation_hook(BOOKINGPRESS_CART_DIR.'/bookingpress-cart.php', array('bookingpress_cart', 'install'));
            register_uninstall_hook(BOOKINGPRESS_CART_DIR.'/bookingpress-cart.php', array('bookingpress_cart', 'uninstall'));

            add_action( 'admin_notices', array( $this, 'bookingpress_cart_admin_notices' ) );
            if( !function_exists('is_plugin_active') ){
                include_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            
            if(is_plugin_active('bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php')){

                //add front side step html dynamically
                add_filter('bookingpress_frontend_apointment_form_add_dynamic_data', array($this, 'bookingpress_modify_front_booking_form_data_vars_func'), 10, 1);
                add_action('bookingpress_add_front_side_sidebar_step_content', array($this, 'bookingpress_add_front_side_sidebar_step_content_func'), 10, 3);
                add_action('bookingpress_add_frontend_css',array($this,'bookingpress_add_front_css'));

                //Add data variables at front shortcode
                add_filter('bookingpress_filter_frontend_vue_data_fields', array($this, 'bookingpress_add_frontend_vue_data_fields_func'), 10, 1);

                //Hook for change next selecting tab value
                add_filter('bookingpress_dynamic_next_page_request_filter', array($this, 'bookingpress_dynamic_next_page_request_filter_func'), 10, 1);

                //Modify data after service add to cart
                add_action('wp_ajax_bookingpress_modify_cart_data_after_add_to_cart', array($this, 'bookingpress_modify_cart_data_after_add_to_cart_func'));
                add_action('wp_ajax_nopriv_bookingpress_modify_cart_data_after_add_to_cart', array($this, 'bookingpress_modify_cart_data_after_add_to_cart_func'));

                //Add Pro version dynamic methods
                add_filter('bookingpress_add_pro_booking_form_methods', array($this, 'bookingpress_add_pro_booking_form_methods_func'), 10, 1);

                //Modify final calculated taxable amount
                add_filter('bookingpress_modify_tax_calculated_amount', array($this, 'bookingpress_modify_tax_calculated_amount_func'), 10, 4);
                add_filter('bookingpress_modify_tax_calculated_appointment_details', array($this, 'bookingpress_modify_tax_calculated_appointment_details_func'), 10, 3);

                //Modify recalcualted appointment details
                add_filter('bookingpress_modify_calculated_appointment_details', array($this, 'bookingpress_modify_calculated_appointment_details_func'), 10, 1);

                //Modify appointment common function data
                add_filter('bookingpress_modify_appointment_return_data', array($this, 'bookingpress_modify_appointment_data_func'), 10, 3);

                //Check applied coupon is valid or not
                add_filter('bookingpress_check_coupon_validity_from_outside', array($this, 'bookingpress_check_coupon_validity_from_outside_func'), 10, 2);

                add_action( 'bookingpress_calendar_integration_events', array( $this, 'bookingpress_calendar_integration_urls') );
                add_action( 'init', array( $this, 'bookingpress_generate_ics_with_cart_items') );

                add_action('bookingpress_add_bookingform_label_data',array($this,'bookingpress_add_bookingform_label_data_func'));
                add_action('bookingpress_add_booking_form_customize_data',array($this,'bookingpress_add_booking_form_customize_data_func'));
                
                add_filter('bookingpress_frontend_apointment_form_add_dynamic_data',array($this ,'bookingpress_frontend_apointment_form_add_dynamic_data_func'));
                add_filter('bookingpress_customize_add_dynamic_data_fields',array($this,'bookingpress_customize_add_dynamic_data_fields_func'),10);
                add_filter('bookingpress_get_booking_form_customize_data_filter',array($this, 'bookingpress_get_booking_form_customize_data_filter_func'),10,1);
                add_filter('bookingpress_get_booking_form_customize_data_filter',array($this, 'bookingpress_get_booking_form_customize_data_filter_func'),10,1);

                add_filter('bookingpress_before_save_customize_booking_form',array($this, 'bookingpress_before_save_customize_booking_form_func'));

                add_action('bookingpress_before_save_customize_form_settings',array($this,'bookingpress_before_save_customize_form_settings_func'));                
                
                add_filter( 'bookingpress_dynamic_add_params_for_timeslot_request', array( $this, 'bookingpress_send_cart_items_to_front_timings') );

                add_filter( 'bookingpress_modify_booked_appointment_data', array( $this, 'bookingpress_modify_timeslot_for_cart' ), 10, 2 );

                add_filter( 'bookingpress_after_change_service_extras', array( $this,'bookingpress_reset_selected_time_data_after_service_changed'), 10, 2 );
                add_filter( 'bookingpress_after_change_service_quantity', array( $this,'bookingpress_reset_selected_time_data_after_service_changed'), 10, 2 );

                //Hook for reset datepicker dates
                add_filter( 'bookingpress_before_selecting_booking_service', array( $this, 'bookingpress_before_selecting_booking_service_func' ), 9, 1 );

                add_filter( 'bookingpress_check_available_timings_with_staffmember', array( $this, 'bookingpress_check_available_timings_with_staffmember_func' ), 11, 3 );

                if( $BookingPress->bpa_is_pro_exists() && $BookingPress->bpa_is_pro_active() ){
                    if( !empty( $BookingPress->bpa_pro_plugin_version() ) && version_compare( $BookingPress->bpa_pro_plugin_version(), '1.6', '>=' ) ) {
                        add_action('bookingpress_add_customize_booking_form_tab',array($this,'bookingpress_add_customize_booking_form_tab_pro_func'));
                    } else {
                        add_action('bookingpress_add_customize_booking_form_tab',array($this,'bookingpress_add_customize_booking_form_tab_func'));
                    }
                } else {
                    add_action('bookingpress_add_customize_booking_form_tab',array($this,'bookingpress_add_customize_booking_form_tab_func'));
                }

                add_action( 'bookingpress_modify_form_sequence_for_rearrange', array( $this, 'bookingpress_add_cart_index_after_datetime_step') );


                if(is_plugin_active('bookingpress-multilanguage/bookingpress-multilanguage.php')) {
					add_filter('bookingpress_modified_language_translate_fields',array($this,'bookingpress_modified_cart_language_translate_fields_func'),10);
                	add_filter('bookingpress_modified_customize_form_language_translate_fields',array($this,'bookingpress_modified_customize_form_cart_language_translate_fields_func'),10);
					add_filter('bookingpress_modified_language_translate_fields_section',array($this,'bookingpress_modified_cart_language_translate_fields_section_func'),10);
				}

                add_action( 'bookingpress_dynamic_next_page_request_filter', array( $this, 'bookingpress_display_conditional_fields_based_on_services'), 20, 1 );                
                add_filter('bookingpress_modify_total_booked_appointment',array($this,'bookingpress_modify_total_booked_appointment_func'),15,2);
                

                /* Add Cart Dynamic Notification Data */            
                add_filter( 'bookingpress_add_dynamic_notification_data_fields', array( $this, 'bookingpress_add_cart_dynamic_notification_data_fields' ) );            
                add_action( 'bookingpress_notification_external_message_plachoders', array( $this, 'bookingpress_add_cart_appointment_placeholder_list'));

                /* Send list of cart appointment in email notification */
                add_filter( 'bookingpress_modify_email_content_filter', array( $this, 'bookingpress_cart_modify_email_content_filter_func' ), 10, 2 );

                /* Function for send single email notification */
                add_filter('bookingpress_send_only_first_appointment_notification',array($this,'bookingpress_send_only_first_appointment_notification_func'),10,3);

                /* Add a filter for send single notification after book appointment */    
                add_filter('bookingpress_send_single_whatsapp_notification_after_booking', array($this, 'bookingpress_send_single_whatsapp_notification_after_booking_func'), 12, 2);
                add_filter('bookingpress_send_single_sms_notification_after_booking', array($this, 'bookingpress_send_single_sms_notification_after_booking_func'), 12, 2);


                /* My Booking Appointment Cancel Notification Send  */
                add_action( 'bookingpress_after_cancel_appointment_without_check_payment', array( $this, 'bookingpress_after_cancel_appointment_fun'), 15 );

                /* Cart Notice Added */
                add_action( 'bookingpress_page_admin_notices', array( $this, 'bookingpress_admin_license_notices_func') );
                add_action( 'bookingpress_admin_panel_vue_methods', array( $this, 'bookingpress_admin_common_vue_methods_func') );
                add_action( 'wp_ajax_bookingpress_dismiss_cart_notice', array( $this, 'bookingpress_dismiss_cart_notice_func') );     
                add_action( 'bookingpress_admin_vue_data_variables_script', array( $this, 'bookingpress_admin_vue_data_variable_script_func' ) );           

            }

            add_action('activated_plugin',array($this,'bookingpress_is_cart_addon_activated'),11,2);
            add_action('admin_init', array( $this, 'bookingpress_update_cart_data') );
        }


		/**
		 * bpa function for modify cart data
		 *
		 * @param  mixed $user_detail
		 * @return void
		*/
		function bookingpress_bpa_modify_cart_data_after_add_to_cart_func($user_detail=array()){
			global $BookingPress,$wpdb,$BookingPressPro;	
			$result = array();            
			$response = array('status' => 0, 'message' => '', 'response' => array('result' => $result));
			if(class_exists('BookingPressPro') && method_exists( $BookingPressPro, 'bookingpress_bpa_check_valid_connection_callback_func') && $BookingPressPro->bookingpress_bpa_check_valid_connection_callback_func()){

				$user_detail = !empty($user_detail)?array_map(array( $BookingPress,'appointment_sanatize_field'),$user_detail):array();
                $user_id = isset($user_detail['user_id']) ? $user_detail['user_id'] : '';
                $deleted_item_index = isset($user_detail['deleted_item_index']) ? $user_detail['deleted_item_index'] : -1;
                $delete_cart_item = isset($user_detail['delete_cart_item']) ? $user_detail['delete_cart_item'] : 'false';
                $empty_cart = isset($user_detail['empty_cart']) ? $user_detail['empty_cart'] : 'false';
                $appointment_details = isset($user_detail['appointment_details']) ? $user_detail['appointment_details'] : '';
                $bookingpress_nonce = isset($user_detail['bookingpress_nonce']) ? $user_detail['bookingpress_nonce'] : '';
                
				if(!empty($bookingpress_nonce)){
					$_REQUEST['_wpnonce'] = $bookingpress_nonce;
				}else{
					$bookingpress_nonce = wp_create_nonce('bpa_wp_nonce');
					$_REQUEST['_wpnonce'] = $bookingpress_nonce;					
				}

                $_REQUEST['deleted_item_index'] = $deleted_item_index;
                $_REQUEST['delete_cart_item'] = $delete_cart_item;
                $_REQUEST['empty_cart'] = $empty_cart;
				if(!empty($appointment_details)){
					$_REQUEST['bookingpress_appointment_data'] = $appointment_details;					
				}
                $_POST = $_REQUEST;
                
				$bookingpress_response =  $this->bookingpress_modify_cart_data_after_add_to_cart_func(true);
				$bookingpress_check_response = (isset($bookingpress_response['variant']))?$bookingpress_response['variant']:'';
				if($bookingpress_check_response == 'error'){					
					$message = (isset($bookingpress_response['msg']))?$bookingpress_response['msg']:'';
					$response = array('status' => 0, 'message' => $message, 'response' => array('result' => $result));					
				}else{
					$result = $bookingpress_response;
					$response = array('status' => 1, 'message' => '', 'response' => array('result' => $result));					
				}

			}
			return $response;
		}        


        function bookingpress_admin_vue_data_variable_script_func() {

            $is_display_cart_notice = !($this->bpa_check_cart_notic_status());
        ?>

            bookingpress_return_data['is_display_cart_notice'] = '<?php echo $is_display_cart_notice; //phpcs:ignore ?>';

        <?php 
        }

		function bookingpress_dismiss_cart_notice_func(){

			$cron_dismiss_type = !empty( $_REQUEST['type'] ) ? sanitize_text_field( $_REQUEST['type'] ) : 'later';
			update_option( 'bookingpress_dismiss_cart_addon_notice_type', $cron_dismiss_type );
			if( 'later' == $cron_dismiss_type ){
				$bookingpress_dismiss_date = strtotime('+2 days',current_time( 'timestamp'));                
				update_option( 'bookingpress_dismiss_cart_addon_notice', $bookingpress_dismiss_date );
			}
            
		}

        function bpa_check_cart_notic_status(){

            $default_return = false;
            $bpa_cron_notice_type = get_option( 'bookingpress_dismiss_cart_addon_notice_type' );

            if( 'forever' == $bpa_cron_notice_type ){
                return true;
            }

            $cron_notice_time = get_option( 'bookingpress_dismiss_cart_addon_notice' );

            if( !empty( $cron_notice_time ) ){
                if( ( current_time('timestamp') < $cron_notice_time ) ){
                    return true;
                }
            }

            return $default_return;

        }

		function bookingpress_admin_common_vue_methods_func(){
			global $bookingpress_notification_duration;
		?>
			bookingpress_hide_cart_notice_for_later(){
				const vm = this;
				var bookingpress_request_data = {};
				bookingpress_request_data.action = "bookingpress_dismiss_cart_notice";
				bookingpress_request_data.type = 'later';
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( bookingpress_request_data ) )
				.then( function( response ) {
					vm.is_display_cart_notice = false;
				}.bind( this ) )
				.catch( function(error){
					console.log( error );
				});
			},
			bookingpress_hide_cart_notice_forever(){
				const vm = this;
				var bookingpress_request_data = {};
				bookingpress_request_data.action = "bookingpress_dismiss_cart_notice";
				bookingpress_request_data.type = 'forever';
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( bookingpress_request_data ) )
				.then( function( response ) {
					vm.is_display_cart_notice = false;
				}.bind( this ) )
				.catch( function(error){
					console.log( error );
				});
			},
        <?php 
        }


		function bookingpress_admin_license_notices_func(){

			global $BookingPress, $bookingpress_pro_settings;
			if( !current_user_can( 'administrator' ) ){
				return;
			}
            $bpa_check_cart_notic_status = $this->bpa_check_cart_notic_status();
            if(false == $bpa_check_cart_notic_status){

                $bpa_cart_notification_msg = sprintf( esc_html__( 'From BookingPress Cart Addon Version 2.7,  a single-cart appointment notification will be sent out when multiple appointments get booked. so please change email notification accordingly. Please find more information about to send all appointment details altogether %s.', 'bookingpress-cart'), '<a href="https://www.bookingpressplugin.com/documents/cart-addon/" target="_blank">'.esc_html__('here', 'bookingpress-cart').'</a>');

            ?>
				<div class="bpa-pg-warning-belt-box bpa-error" v-if="is_display_cart_notice == true">
					<p class="bpa-wbb__desc">
						<span class="material-icons-round bpa-wbb__desc-icon">warning</span>
						<span class="bpa-wbb__desc-content"><?php echo $bpa_cart_notification_msg; //phpcs:ignore ?></span>
					</p>
					<span class="bpa-pg-warning-belt-footer">
						<span class="bpa-pg-warning-belt-footer-link" @click="bookingpress_hide_cart_notice_for_later"><?php esc_html_e('Show me later', 'bookingpress-cart'); ?></span>
						<span class="bpa-pg-warning-belt-footer-link" @click="bookingpress_hide_cart_notice_forever"><?php esc_html_e('Dismiss notice', 'bookingpress-cart'); ?></span>
					</span>
				</div>
            <?php 
            }

        }

        /**
         * Function for after cancel appointment.
         *
         * @param  mixed $appointment_id
         * @param  mixed $appointment_status
         * @return void
        */
        public function bookingpress_after_cancel_appointment_fun($appointment_id){

            if($appointment_id){

                global $wpdb,$tbl_bookingpress_payment_logs,$tbl_bookingpress_appointment_bookings,$bookingpress_email_notifications,$BookingPress;
                $bookingpress_get_appointment_record = $wpdb->get_row($wpdb->prepare( "SELECT bookingpress_order_id,bookingpress_is_cart FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d",$appointment_id), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
                if(!empty($bookingpress_get_appointment_record)){
                    $bookingpress_is_cart = (isset($bookingpress_get_appointment_record['bookingpress_is_cart']))?$bookingpress_get_appointment_record['bookingpress_is_cart']:'';
                    if($bookingpress_is_cart == 1 || $bookingpress_is_cart == '1'){
                        
                        $bookingpress_order_id = (isset($bookingpress_get_appointment_record['bookingpress_order_id']))?$bookingpress_get_appointment_record['bookingpress_order_id']:'';                        
                        $payment_log_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_payment_logs} WHERE bookingpress_order_id = %d", $bookingpress_order_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm                
                        if (! empty($payment_log_data) ) {

                            $bookingress_customer_email = $payment_log_data['bookingpress_customer_email'];                        
                            $bookingpress_email_notifications->bookingpress_send_after_payment_log_entry_email_notification('Appointment Canceled', $appointment_id, $bookingress_customer_email);

                        }                        

                    }

                }

            }
        }

        /**
         * Function for send single message for cart appointment after booking
         *
         * @param  mixed $bookingpress_send_single_integration_notification_after_booking
         * @param  mixed $bookingpress_appointment_data
         * @return void
        */
        public function bookingpress_send_single_whatsapp_notification_after_booking_func($bookingpress_send_single_integration_notification_after_booking,$bookingpress_appointment_data){
            if(!empty($bookingpress_appointment_data)){                
                global $wpdb,$tbl_bookingpress_appointment_meta;
                $bookingpress_is_cart = (isset($bookingpress_appointment_data['bookingpress_is_cart']))?$bookingpress_appointment_data['bookingpress_is_cart']:0;
                $bookingpress_order_id = (isset($bookingpress_appointment_data['bookingpress_order_id']))?$bookingpress_appointment_data['bookingpress_order_id']:0;
                if($bookingpress_is_cart == 1 || $bookingpress_is_cart == '1' && $bookingpress_order_id != 0){                    
                    $bookingpress_is_cart_records = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_appointment_meta_id FROM {$tbl_bookingpress_appointment_meta} WHERE bookingpress_order_id = %d AND bookingpress_appointment_meta_key = 'send_cart_whatsapp_notification'", $bookingpress_order_id ), ARRAY_A );// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_meta is table name defined globally. False Positive alarm
                    if(!empty($bookingpress_is_cart_records)){
                        $bookingpress_send_single_integration_notification_after_booking = false;     
                    }else{
                        $bookingpress_db_fields = array(
                            'bookingpress_entry_id' => 0,
                            'bookingpress_appointment_id' => 0,
                            'bookingpress_order_id' => $bookingpress_order_id,
                            'bookingpress_appointment_meta_key' => 'send_cart_whatsapp_notification',
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
         * Function for send single message for cart appointment after booking
         *
         * @param  mixed $bookingpress_send_single_integration_notification_after_booking
         * @param  mixed $bookingpress_appointment_data
         * @return void
        */
        function bookingpress_send_single_sms_notification_after_booking_func($bookingpress_send_single_integration_notification_after_booking,$bookingpress_appointment_data){
            if(!empty($bookingpress_appointment_data)){

                global $wpdb,$tbl_bookingpress_appointment_meta;
                $bookingpress_is_cart = (isset($bookingpress_appointment_data['bookingpress_is_cart']))?$bookingpress_appointment_data['bookingpress_is_cart']:0;
                $bookingpress_order_id = (isset($bookingpress_appointment_data['bookingpress_order_id']))?$bookingpress_appointment_data['bookingpress_order_id']:0;
                $meta_key = 'send_cart_sms_notification';
                if(($bookingpress_is_cart == 1 || $bookingpress_is_cart == '1') && $bookingpress_order_id != 0){
                    $bookingpress_is_cart_records = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_appointment_meta_id FROM {$tbl_bookingpress_appointment_meta} WHERE bookingpress_order_id = %d AND bookingpress_appointment_meta_key = %s", $bookingpress_order_id,$meta_key ), ARRAY_A );// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_meta is table name defined globally. False Positive alarm
                    if(!empty($bookingpress_is_cart_records)){
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
         * Function for send only first appointment notification
         *
         * @param  mixed $bookingpress_send_only_first_appointment_notification
         * @param  mixed $appointment_id
         * @param  mixed $payment_log_data
         * @return void
        */
        function bookingpress_send_only_first_appointment_notification_func($bookingpress_send_only_first_appointment_notification,$appointment_id,$payment_log_data){
            if(isset($payment_log_data['bookingpress_is_cart'])){
                if($payment_log_data['bookingpress_is_cart'] == '1'){
                    $bookingpress_send_only_first_appointment_notification = 'yes';
                }
            }           
            return $bookingpress_send_only_first_appointment_notification;
        }        

        /**
         * bookingpress_cart_modify_email_content_filter_func- Send list of cart appointment in email notification
         *
         * @param  mixed $template_content
         * @param  mixed $bookingpress_appointment_data
         * @return void
        */
        function bookingpress_cart_modify_email_content_filter_func($template_content, $bookingpress_appointment_data)
        {
            global $tbl_bookingpress_appointment_bookings, $wpdb, $bookingpress_global_options,$BookingPress;
            
            $bookingpress_order_id = isset($bookingpress_appointment_data['bookingpress_order_id']) ? $bookingpress_appointment_data['bookingpress_order_id'] : $bookingpress_appointment_data['bookingpress_order_id'];
            $bookingpress_is_cart = isset($bookingpress_appointment_data['bookingpress_is_cart']) ? $bookingpress_appointment_data['bookingpress_is_cart'] : $bookingpress_appointment_data['bookingpress_is_cart'];
            $bookingpress_cart_appointment_list = '';
            if($bookingpress_is_cart == 1){
                $bookingpress_rec_appointments = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_order_id =%d",$bookingpress_order_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm                
                $bookingpress_global_options_data = $bookingpress_global_options->bookingpress_global_options();
                $default_time_format = $bookingpress_global_options_data['wp_default_time_format'];
                $bookingpress_date_format = $bookingpress_global_options_data['wp_default_date_format'];
                if(!empty($bookingpress_rec_appointments) && $bookingpress_is_cart == 1) {
                    foreach($bookingpress_rec_appointments as $bookingpress_rec_appointments_key => $bookingpress_rec_appointments_data){
                        $bookingpress_is_cart = isset($bookingpress_rec_appointments_data['bookingpress_is_cart']) ? $bookingpress_rec_appointments_data['bookingpress_is_cart'] : '';
                        if($bookingpress_is_cart == 1) {

                            $bookingpress_service_name = isset($bookingpress_rec_appointments_data['bookingpress_service_name']) && !empty($bookingpress_rec_appointments_data['bookingpress_service_name']) ? $bookingpress_rec_appointments_data['bookingpress_service_name'] : '';
                            $bookingpress_appointment_date = isset($bookingpress_rec_appointments_data['bookingpress_appointment_date']) ? $bookingpress_rec_appointments_data['bookingpress_appointment_date'] : '';
                            $bookingpress_appointment_start_time = isset($bookingpress_rec_appointments_data['bookingpress_appointment_time']) && !empty($bookingpress_rec_appointments_data['bookingpress_appointment_time']) ? $bookingpress_rec_appointments_data['bookingpress_appointment_time'] : '';
                            $bookingpress_appointment_end_time = isset($bookingpress_rec_appointments_data['bookingpress_appointment_end_time']) && !empty($bookingpress_rec_appointments_data['bookingpress_appointment_end_time']) ? $bookingpress_rec_appointments_data['bookingpress_appointment_end_time'] : '';
                            $bookingpress_staff_first_name = isset($bookingpress_rec_appointments_data['bookingpress_staff_first_name']) && !empty($bookingpress_rec_appointments_data['bookingpress_staff_first_name']) ? $bookingpress_rec_appointments_data['bookingpress_staff_first_name'] : '';
                            $bookingpress_staff_last_name = isset($bookingpress_rec_appointments_data['bookingpress_staff_last_name']) && !empty($bookingpress_rec_appointments_data['bookingpress_staff_last_name']) ? $bookingpress_rec_appointments_data['bookingpress_staff_last_name'] : '';
                            $bookingpress_appointment_start_time = date($default_time_format, strtotime($bookingpress_appointment_start_time));
                            $bookingpress_appointment_end_time   = date($default_time_format, strtotime($bookingpress_appointment_end_time));
                            $bookingpress_appointment_date =  date($bookingpress_date_format,strtotime($bookingpress_appointment_date));
    
                            $at =  esc_html__( 'at', 'bookingpress-cart' );
                            $on =  esc_html__( 'on', 'bookingpress-cart' );
                            $by =  esc_html__( 'by', 'bookingpress-cart' );
                            $to =  esc_html__( 'to', 'bookingpress-cart' );

                            $bookingpress_service_duration_unit = isset($bookingpress_rec_appointments_data['bookingpress_service_duration_unit']) && !empty($bookingpress_rec_appointments_data['bookingpress_service_duration_unit']) ? $bookingpress_rec_appointments_data['bookingpress_service_duration_unit'] : '';

                            if(!empty($bookingpress_staff_first_name) || !empty($bookingpress_staff_last_name)){
                                $bookingpress_cart_appointment_list .= $bookingpress_service_name.' ';
                                $bookingpress_cart_appointment_list .= $by.' '.$bookingpress_staff_first_name.' '.$bookingpress_staff_last_name.' ';
                                if($bookingpress_service_duration_unit != 'd'){
                                    $bookingpress_cart_appointment_list .= $at.' '.$bookingpress_appointment_start_time.' '.$to.' '.$bookingpress_appointment_end_time.' ';
                                }
                                $bookingpress_cart_appointment_list .= $on.' '.$bookingpress_appointment_date.'<br>';
                            } else {
                                $bookingpress_cart_appointment_list .= $bookingpress_service_name.' ';                                
                                if($bookingpress_service_duration_unit != 'd'){
                                    $bookingpress_cart_appointment_list .= $at.' '.$bookingpress_appointment_start_time.' '.$to.' '.$bookingpress_appointment_end_time.' ';
                                }
                                $bookingpress_cart_appointment_list .= $on.' '.$bookingpress_appointment_date.'<br>';
                            }

                        }
                    }
                }
            }else{
                $bookingpress_is_recurring = isset($bookingpress_appointment_data['bookingpress_is_recurring']) ? $bookingpress_appointment_data['bookingpress_is_recurring'] : 0;
                if($bookingpress_is_recurring != 1){
                    
                    $bookingpress_global_options_data = $bookingpress_global_options->bookingpress_global_options();
                    $default_time_format = $bookingpress_global_options_data['wp_default_time_format'];
                    $bookingpress_date_format = $bookingpress_global_options_data['wp_default_date_format'];

                    $bookingpress_service_name = isset($bookingpress_appointment_data['bookingpress_service_name']) && !empty($bookingpress_appointment_data['bookingpress_service_name']) ? $bookingpress_appointment_data['bookingpress_service_name'] : '';
                    $bookingpress_appointment_date = isset($bookingpress_appointment_data['bookingpress_appointment_date']) ? $bookingpress_appointment_data['bookingpress_appointment_date'] : '';
                    $bookingpress_appointment_start_time = isset($bookingpress_appointment_data['bookingpress_appointment_time']) && !empty($bookingpress_appointment_data['bookingpress_appointment_time']) ? $bookingpress_appointment_data['bookingpress_appointment_time'] : '';
                    $bookingpress_appointment_end_time = isset($bookingpress_appointment_data['bookingpress_appointment_end_time']) && !empty($bookingpress_appointment_data['bookingpress_appointment_end_time']) ? $bookingpress_appointment_data['bookingpress_appointment_end_time'] : '';
                    $bookingpress_staff_first_name = isset($bookingpress_appointment_data['bookingpress_staff_first_name']) && !empty($bookingpress_appointment_data['bookingpress_staff_first_name']) ? $bookingpress_appointment_data['bookingpress_staff_first_name'] : '';
                    $bookingpress_staff_last_name = isset($bookingpress_appointment_data['bookingpress_staff_last_name']) && !empty($bookingpress_appointment_data['bookingpress_staff_last_name']) ? $bookingpress_appointment_data['bookingpress_staff_last_name'] : '';

                    $bookingpress_service_duration_unit = isset($bookingpress_appointment_data['bookingpress_service_duration_unit']) && !empty($bookingpress_appointment_data['bookingpress_service_duration_unit']) ? $bookingpress_appointment_data['bookingpress_service_duration_unit'] : '';


                    $bookingpress_appointment_start_time = date($default_time_format, strtotime($bookingpress_appointment_start_time));
                    $bookingpress_appointment_end_time   = date($default_time_format, strtotime($bookingpress_appointment_end_time));
                    $bookingpress_appointment_date =  date($bookingpress_date_format,strtotime($bookingpress_appointment_date));

                    $at =  esc_html__( 'at', 'bookingpress-cart' );
                    $on =  esc_html__( 'on', 'bookingpress-cart' );
                    $by =  esc_html__( 'by', 'bookingpress-cart' );
                    $to =  esc_html__( 'to', 'bookingpress-cart' );

                    if(!empty($bookingpress_staff_first_name) || !empty($bookingpress_staff_last_name)){
                        $bookingpress_cart_appointment_list .= $bookingpress_service_name.' ';
                        $bookingpress_cart_appointment_list .= $by.' '.$bookingpress_staff_first_name.' '.$bookingpress_staff_last_name.' ';
                        if($bookingpress_service_duration_unit != 'd'){
                            $bookingpress_cart_appointment_list .= $at.' '.$bookingpress_appointment_start_time.' '.$to.' '.$bookingpress_appointment_end_time.' ';
                        }
                        $bookingpress_cart_appointment_list .= $on.' '.$bookingpress_appointment_date.'<br>';
                    } else {

                        $bookingpress_cart_appointment_list .= $bookingpress_service_name.' ';
                        if($bookingpress_service_duration_unit != 'd'){
                            $bookingpress_cart_appointment_list .= $at.' '.$bookingpress_appointment_start_time.' '.$to.' '.$bookingpress_appointment_end_time.' ';
                        }                        
                        $bookingpress_cart_appointment_list .= $on.' '.$bookingpress_appointment_date.'<br>';

                    }                    


                }

            }
            $template_content  = str_replace( '%cart_appointment_list%', $bookingpress_cart_appointment_list, $template_content ); 
            return $template_content;
        }	   

        function bookingpress_add_cart_appointment_placeholder_list(){
            ?>
            <div class="bpa-gs__cb--item-tags-body" v-if="bookingpress_active_email_notification != 'package_order'">
                <div>
                    <span class="bpa-tags--item-sub-heading"><?php esc_html_e('Cart Appointment', 'bookingpress-cart'); ?></span>
                    <span class="bpa-tags--item-body" v-for="item in cart_appointment_list" @click="bookingpress_insert_placeholder(item.value); bookingpress_insert_sms_placeholder(item.value); bookingpress_insert_whatsapp_placeholder(item.value);">{{ item.name }}</span>
                </div>
            </div>
            <?php            
        }

        /**
         * Array for Notification placeholder bookingpress_add_cart_dynamic_notification_data_fields
         *
         * @param  mixed $bookingpress_notification_vue_methods_data
         * @return void
        */
        function bookingpress_add_cart_dynamic_notification_data_fields( $bookingpress_notification_vue_methods_data ) {

            $bookingpress_notification_vue_methods_data['cart_appointment_list'] = array(
                array(
                    'value' => '%cart_appointment_list%',
                    'name' => '%cart_appointment_list%'
                ),
            );
            
            return $bookingpress_notification_vue_methods_data;

        } 

        function bookingpress_modify_total_booked_appointment_func($bookingpress_total_appointment,$bookingpress_appointment_data){        
            global $BookingPress,$bookingpress_pro_staff_members,$BookingPressPro;
            if( !empty( $bookingpress_appointment_data['cart_items'] ) && count( $bookingpress_appointment_data['cart_items'] ) > 0 ){
                                
                if(empty($bookingpress_total_appointment)){
                    $bookingpress_total_appointment = array();
                }
                $cart_items = $bookingpress_appointment_data['cart_items']; //phpcs:ignore 
                $selected_service_id = !empty( $bookingpress_appointment_data['selected_service'] ) ? intval( $bookingpress_appointment_data['selected_service'] ) : '';
                $selected_staff_member_id = (isset($bookingpress_appointment_data['selected_staff_member_id']))?$bookingpress_appointment_data['selected_staff_member_id']:''; 
                
                $bookingpress_shared_service_timeslot = $BookingPress->bookingpress_get_settings('share_timeslot_between_services', 'general_setting');
                foreach( $cart_items as $cart_index => $cart_data ){                    
                    $bookingpress_service_duration_unit = (isset($cart_data['bookingpress_service_duration_unit']))?$cart_data['bookingpress_service_duration_unit']:'';
                    $bookingpress_service_duration_val = (isset($cart_data['bookingpress_service_duration_val']))?$cart_data['bookingpress_service_duration_val']:0;
                    $bookingpress_selected_date = (isset($cart_data['bookingpress_selected_date']))?$cart_data['bookingpress_selected_date']:'';
                    $cart_item_edit_index = (isset($bookingpress_appointment_data['cart_item_edit_index']))?$bookingpress_appointment_data['cart_item_edit_index']:'';                                                            
                    if($bookingpress_service_duration_unit == 'd'){
        
                        $bookingpress_appointment_date = $cart_data['bookingpress_selected_date'];
                        $bookingpress_service_duration_val = $cart_data['bookingpress_service_duration_val'];
                        $bookingpress_service_duration_unit = $cart_data['bookingpress_service_duration_unit'];
                        $bookingpress_total_person = $cart_data['bookingpress_bring_anyone_selected_members'];

                        if( $bookingpress_shared_service_timeslot == 'true'){
        
                            $bookingpress_share_timeslot_between_services_type = $BookingPress->bookingpress_get_settings('share_timeslot_between_services_type', 'general_setting');                            
                            if($bookingpress_share_timeslot_between_services_type == 'service_category'){
        
                                $bookingpress_related_services = array('none');
                                $bookingpress_selected_category = (isset($bookingpress_appointment_data['selected_category']))?$bookingpress_appointment_data['selected_category']:'';
                                $bookingpress_all_related_category_service = (isset($bookingpress_appointment_data['related_category_service']))?$bookingpress_appointment_data['related_category_service']:'';					
                                if((!empty($bookingpress_selected_category) || $bookingpress_selected_category == 0) && !empty($bookingpress_all_related_category_service)){						
                                    /* get selected services category start */
                                    $bookingpress_selected_service_category = 0;                                    
                                    foreach($bookingpress_all_related_category_service as $key=>$servval){
                                        foreach($servval as $val){
                                            if($val == $selected_service_id){
                                                $bookingpress_selected_service_category = $key;
                                            }    
                                        }
                                    }
                                    /* get selected services category over */
                                    $bookingpress_related_services = isset($bookingpress_all_related_category_service[$bookingpress_selected_service_category])?$bookingpress_all_related_category_service[$bookingpress_selected_service_category]:array();
                                }
                                $bookingpress_service_id = (isset($cart_data['bookingpress_service_id']))?$cart_data['bookingpress_service_id']:'';
                                if(!empty($bookingpress_related_services)){                                    
                                    if(in_array($bookingpress_service_id,$bookingpress_related_services)){
                                        if( $cart_item_edit_index == $cart_index ){
                                            continue;
                                        }     
                                        $bookingpress_total_appointment[] = array(
                                            'bookingpress_appointment_date' => $bookingpress_appointment_date,
                                            'bookingpress_service_duration_val' => $bookingpress_service_duration_val,
                                            'bookingpress_service_duration_unit' => $bookingpress_service_duration_unit,
                                            'bookingpress_total_person' => $bookingpress_total_person,                                        
                                        );                                        

                                    }                                
        
                                }
                            }else{
                                if( $cart_item_edit_index == $cart_index ){
                                    continue;
                                }                          
                                $bookingpress_total_appointment[] = array(
                                    'bookingpress_appointment_date' => $bookingpress_appointment_date,
                                    'bookingpress_service_duration_val' => $bookingpress_service_duration_val,
                                    'bookingpress_service_duration_unit' => $bookingpress_service_duration_unit,
                                    'bookingpress_total_person' => $bookingpress_total_person,
                                
                                );                                                               
                            }
                        }else{
                            $bookingpress_service_id = (isset($cart_data['bookingpress_service_id']))?$cart_data['bookingpress_service_id']:'';
                            $bookingpress_selected_staffmember = (isset($cart_data['bookingpress_selected_staffmember']))?$cart_data['bookingpress_selected_staffmember']:'';                            
                            if($bookingpress_pro_staff_members-> bookingpress_check_staffmember_module_activation()){
                                if($bookingpress_selected_staffmember == $selected_staff_member_id){
                                    if( $cart_item_edit_index == $cart_index ){
                                        continue;
                                    }        
                                    $bookingpress_total_appointment[] = array(
                                        'bookingpress_appointment_date' => $bookingpress_appointment_date,
                                        'bookingpress_service_duration_val' => $bookingpress_service_duration_val,
                                        'bookingpress_service_duration_unit' => $bookingpress_service_duration_unit,
                                        'bookingpress_total_person' => $bookingpress_total_person,
                                    
                                    );                                               
                                }
                            }else{
                                if($bookingpress_service_id == $selected_service_id){
                                    if( $cart_item_edit_index == $cart_index ){
                                        continue;
                                    }  
                                    $has_date = false;                     
                                    $bookingpress_total_appointment[] = array(
                                        'bookingpress_appointment_date' => $bookingpress_appointment_date,
                                        'bookingpress_service_duration_val' => $bookingpress_service_duration_val,
                                        'bookingpress_service_duration_unit' => $bookingpress_service_duration_unit,
                                        'bookingpress_total_person' => $bookingpress_total_person,
                                    
                                    );                                       
                                }
                            }        
                        }        
                    }
                }  
                             
            }
        
            return $bookingpress_total_appointment;
        }



        function bookingpress_modified_cart_language_translate_fields_func($bookingpress_all_language_translation_fields){
			
			$bookingpress_cart_language_translation_fields = array(                
				'customized_form_cart_member_step' => array(
					'cart_title' => array('field_type'=>'text','field_label'=>__('Step Cart', 'bookingpress-cart'),'save_field_type'=>'booking_form'),
                    'cart_heading_title' => array('field_type'=>'text','field_label'=>__('Cart title', 'bookingpress-cart'),'save_field_type'=>'booking_form'),
                    'cart_item_title' => array('field_type'=>'text','field_label'=>__('Cart item title', 'bookingpress-cart'),'save_field_type'=>'booking_form'),
                    'cart_add_service_button_label' => array('field_type'=>'text','field_label'=>__('Add service button label', 'bookingpress-cart'),'save_field_type'=>'booking_form'),
                    'cart_service_duration_title' => array('field_type'=>'text','field_label'=>__('Cart Duration title', 'bookingpress-cart'),'save_field_type'=>'booking_form'),
                    'cart_staff_title' => array('field_type'=>'text','field_label'=>__('Cart Staff title', 'bookingpress-cart'),'save_field_type'=>'booking_form'),
                    'cart_total_amount_title' => array('field_type'=>'text','field_label'=>__('Total title', 'bookingpress-cart'),'save_field_type'=>'booking_form'),
                    'cart_number_person_title' => array('field_type'=>'text','field_label'=>__('Cart No. of person title', 'bookingpress-cart'),'save_field_type'=>'booking_form'),
                    'cart_service_extra_title' => array('field_type'=>'text','field_label'=>__('Cart service extra title', 'bookingpress-cart'),'save_field_type'=>'booking_form'),
                    'cart_service_extra_quantity_title' => array('field_type'=>'text','field_label'=>__('Cart service extra quantity title', 'bookingpress-cart'),'save_field_type'=>'booking_form'),
                    'cart_deposit_title' => array('field_type'=>'text','field_label'=>__('Cart deposit title', 'bookingpress-cart'),'save_field_type'=>'booking_form'),
                    'cart_edit_item_title' => array('field_type'=>'text','field_label'=>__('Cart edit item title', 'bookingpress-cart'),'save_field_type'=>'booking_form'),
                    'cart_remove_item_title' => array('field_type'=>'text','field_label'=>__('Cart remove item title', 'bookingpress-cart'),'save_field_type'=>'booking_form'),
				)   
			);  
			
            $bookingpress_all_language_translation_fields = array_merge($bookingpress_all_language_translation_fields,$bookingpress_cart_language_translation_fields);
            return $bookingpress_all_language_translation_fields;
		}

		function bookingpress_modified_customize_form_cart_language_translate_fields_func($bookingpress_all_language_translation_fields){
			$bookingpress_cart_language_translation_fields = array(                
				'customized_form_cart_member_step' => array(
					'cart_title' => array('field_type'=>'text','field_label'=>__('Step Cart', 'bookingpress-cart'),'save_field_type'=>'booking_form'),
                    'cart_heading_title' => array('field_type'=>'text','field_label'=>__('Cart title', 'bookingpress-cart'),'save_field_type'=>'booking_form'),
                    'cart_item_title' => array('field_type'=>'text','field_label'=>__('Cart item title', 'bookingpress-cart'),'save_field_type'=>'booking_form'),
                    'cart_add_service_button_label' => array('field_type'=>'text','field_label'=>__('Add service button label', 'bookingpress-cart'),'save_field_type'=>'booking_form'),
                    'cart_service_duration_title' => array('field_type'=>'text','field_label'=>__('Cart Duration title', 'bookingpress-cart'),'save_field_type'=>'booking_form'),
                    'cart_staff_title' => array('field_type'=>'text','field_label'=>__('Cart Staff title', 'bookingpress-cart'),'save_field_type'=>'booking_form'),
                    'cart_total_amount_title' => array('field_type'=>'text','field_label'=>__('Total title', 'bookingpress-cart'),'save_field_type'=>'booking_form'),
                    'cart_number_person_title' => array('field_type'=>'text','field_label'=>__('Cart No. of person title', 'bookingpress-cart'),'save_field_type'=>'booking_form'),
                    'cart_service_extra_title' => array('field_type'=>'text','field_label'=>__('Cart service extra title', 'bookingpress-cart'),'save_field_type'=>'booking_form'),
                    'cart_service_extra_quantity_title' => array('field_type'=>'text','field_label'=>__('Cart service extra quantity title', 'bookingpress-cart'),'save_field_type'=>'booking_form'),
                    'cart_deposit_title' => array('field_type'=>'text','field_label'=>__('Cart deposit title', 'bookingpress-cart'),'save_field_type'=>'booking_form'),
                    'cart_edit_item_title' => array('field_type'=>'text','field_label'=>__('Cart edit item title', 'bookingpress-cart'),'save_field_type'=>'booking_form'),
                    'cart_remove_item_title' => array('field_type'=>'text','field_label'=>__('Cart remove item title', 'bookingpress-cart'),'save_field_type'=>'booking_form'),
				)   
			);  
			$pos = 4;
            if(is_plugin_active('bookingpress-location/bookingpress-location.php')) {
                $bookingpress_cart_language_translation_fields['customized_form_cart_member_step']['cart_location_title'] = array('field_type'=>'text','field_label'=>__('Cart location title', 'bookingpress-cart'),'save_field_type'=>'booking_form');                
            }
			$bookingpress_all_language_translation_fields = array_slice($bookingpress_all_language_translation_fields, 0, $pos)+$bookingpress_cart_language_translation_fields + array_slice($bookingpress_all_language_translation_fields, $pos);
			return $bookingpress_all_language_translation_fields;
		}

        function bookingpress_modified_cart_language_translate_fields_section_func($bookingpress_all_language_translation_fields_section){
			/* Function to add cart step heading */
            $bookingpress_cart_step_section_added = array('customized_form_cart_member_step' => __('Cart step labels', 'bookingpress-cart') );
			$bookingpress_all_language_translation_fields_section = array_merge($bookingpress_all_language_translation_fields_section,$bookingpress_cart_step_section_added);
			return $bookingpress_all_language_translation_fields_section;
		}  

        function bookingpress_display_conditional_fields_based_on_services( $bookingpress_dynamic_next_page_request_filter ){

            $bookingpress_dynamic_next_page_request_filter .= '
                
                if( "basic_details" == vm.bookingpress_current_tab && "basic_details" == next_tab ){
                    let cart_items = vm.appointment_step_form_data.cart_items;
                    let cart_service_ids = [];
                    if( "undefined" != typeof cart_items && 0 < cart_items.length ){
                        vm.appointment_step_form_data.cart_items.forEach( function(currentValue,i,arr) {
                            let cart_service_id = currentValue.bookingpress_service_id;
                            cart_service_ids.push( parseInt( cart_service_id ) );
                        });

                        let visible_fields = [];
                        let inner_visible_fields = {};
                        vm.customer_form_fields.forEach( (element,index) => {
                            if( typeof element.field_options != "undefined" && element.field_options.visibility == "services" ){
                                let field_services = element.field_options.selected_services;
                                vm.customer_form_fields[index].is_hide = 1;
                                for(let s_id of field_services ){
                                    let sid = parseInt( s_id );
                                    if( cart_service_ids.indexOf( sid ) > -1 ){
                                        visible_fields.push( index );
                                    }
                                }
                            } else if( element.field_type == "2_col" || element.field_type == "3_col" || element.field_type == "4_col" ){ 
                                let total_inner_fields = element.field_options.inner_fields.length;
                                let total_hidden_fields = 0;
                                if( total_inner_fields > 0 ){
                                    element.field_options.inner_fields.forEach( (ielement,iindex) =>{
                                        let field_visibility = ielement.field_options.visibility || "always";
                                        if( "services" == field_visibility ){
                                            vm.customer_form_fields[index].is_hide = 1;
                                            vm.customer_form_fields[index].field_options.inner_fields[iindex].is_hide = 1;
                                            let field_services = ielement.field_options.selected_services;
                                            for( let s_id of field_services ){
                                                let sid = parseInt( s_id );
                                                if( cart_service_ids.indexOf( sid ) > -1 ){
                                                    visible_fields.push( index );
                                                    /* visible_fields.push( iindex ); */
                                                    if( "undefined" == typeof inner_visible_fields[ index ] ){
                                                        inner_visible_fields[ index ] = [];
                                                    }
                                                    inner_visible_fields[ index ].push( iindex );
                                                } else {
                                                    total_hidden_fields++;
                                                }
                                            }
                                        }
                                    });
                                }
                            }
                        });

                        if( visible_fields.length > 0 ){
                            for( let cf_index of visible_fields ) {
                                vm.customer_form_fields[cf_index].is_hide = 0;
                                if( "undefined" != typeof inner_visible_fields[cf_index] ){
                                    if(  inner_visible_fields[cf_index].length > 0 ){
                                        for( let if_index of inner_visible_fields[cf_index] ){
                                            vm.customer_form_fields[ cf_index ].field_options.inner_fields[ if_index ].is_hide = 0;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            ';

            return $bookingpress_dynamic_next_page_request_filter;
        }

        function bookingpress_add_cart_index_after_datetime_step( $external_keys ){

            $datetime_pos = array_search( 'datetime_selection', $external_keys );

            array_splice( $external_keys, ($datetime_pos + 1), 0, 'cart_selection' );

            return $external_keys;
        }

        function bookingpress_before_selecting_booking_service_func($bookingpress_before_selecting_booking_service_data){
            $bookingpress_site_date = date('Y-m-d H:i:s', current_time( 'timestamp') );
            $bookingpress_site_date = apply_filters( 'bookingpress_modify_current_date', $bookingpress_site_date );

			$bookingpress_before_selecting_booking_service_data .= '
				if(typeof vm.appointment_step_form_data.cart_items != "undefined" && selected_service_id != ""){
					if(vm.appointment_step_form_data.cart_items.length == 0){
						/* When cart is empty and service changed then this condition will execute */
						var bookingpress_selected_date = vm.appointment_step_form_data.selected_date;
						let newDate = new Date('.( !empty( $bookingpress_site_date ) ? '"' . $bookingpress_site_date . '"' : '' ).');
						let pattern = /(\d{4}\-\d{2}\-\d{2})/;
						if( !pattern.test( newDate ) ){

							let sel_month = newDate.getMonth() + 1;
							let sel_year = newDate.getFullYear();
							let sel_date = newDate.getDate();

							if( sel_month < 10 ){
								sel_month = "0" + sel_month;
							}

							if( sel_date < 10 ){
								sel_date = "0" + sel_date;
							}
							
							newDate = sel_year + "-" + sel_month + "-" + sel_date;
						}
						
						vm.appointment_step_form_data.selected_date = newDate;
						vm.appointment_step_form_data.selected_start_time = "";
						vm.appointment_step_form_data.selected_end_time = "";
					}else if(vm.appointment_step_form_data.cart_items.length > 0){
                        if( typeof vm.appointment_step_form_data.cart_items[vm.appointment_step_form_data.cart_item_edit_index] != "undefined") {
                            var bookingpress_is_cart_item_edit = vm.appointment_step_form_data.cart_items[vm.appointment_step_form_data.cart_item_edit_index].bookingpress_is_edit;
                            var bookingpress_cart_edited_service_id = vm.appointment_step_form_data.cart_items[vm.appointment_step_form_data.cart_item_edit_index].bookingpress_service_id;
                        
                            if((bookingpress_is_cart_item_edit == 1 && bookingpress_cart_edited_service_id != selected_service_id) || (bookingpress_is_cart_item_edit == 0) ){
                                var bookingpress_selected_date = vm.appointment_step_form_data.selected_date;
                                let newDate = new Date('.( !empty( $bookingpress_site_date ) ? '"' . $bookingpress_site_date . '"' : '' ).');
                                let pattern = /(\d{4}\-\d{2}\-\d{2})/;
                                if( !pattern.test( newDate ) ){

                                    let sel_month = newDate.getMonth() + 1;
                                    let sel_year = newDate.getFullYear();
                                    let sel_date = newDate.getDate();

                                    if( sel_month < 10 ){
                                        sel_month = "0" + sel_month;
                                    }

                                    if( sel_date < 10 ){
                                        sel_date = "0" + sel_date;
                                    }
                                    
                                    newDate = sel_year + "-" + sel_month + "-" + sel_date;
                                }
                                
                                vm.appointment_step_form_data.selected_date = newDate;
                                vm.appointment_step_form_data.selected_start_time = "";
                                vm.appointment_step_form_data.selected_end_time = "";
                            }
                        }
					}
				}
			';
            
            return $bookingpress_before_selecting_booking_service_data;
        }
	
        function bookingpress_is_cart_addon_activated($plugin,$network_activation)
        { 
            $myaddon_name = "bookingpress-cart/bookingpress-cart.php";

            if($plugin == $myaddon_name)
            { 
                if(!(is_plugin_active('bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php')))
                {
                    deactivate_plugins($plugin, FALSE);
                    $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$plugin);
                    $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Cart Add-on', 'bookingpress-cart');
					$bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-cart'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
					wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                    die;
                }

                $license = trim( get_option( 'bkp_license_key' ) );
                $package = trim( get_option( 'bkp_license_package' ) );

                if( '' === $license || false === $license ) 
                {
                    deactivate_plugins($plugin, FALSE);
                    $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$plugin);
                    $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Cart Add-on', 'bookingpress-cart');
					$bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-cart'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
					wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                    die;
                }
                else
                {
                    $store_url = BOOKINGPRESS_CART_STORE_URL;
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
                            deactivate_plugins($plugin, FALSE);
                            $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$plugin);
                            $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Cart Add-on', 'bookingpress-cart');
                            $bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-cart'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
                            wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                            die;
                        }

                    }
                    else
                    {
                        deactivate_plugins($plugin, FALSE);
                        $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$plugin);
                        $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Cart Add-on', 'bookingpress-cart');
                        $bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-cart'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
                        wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                        die;
                    }
                }
            }
        }

        function bookingpress_update_cart_data(){
            global $BookingPress,$bookingpress_cart_version;
            $bookingpress_db_cart_version = get_option('bookingpress_cart_module', true);

            if( version_compare( $bookingpress_db_cart_version, '2.9', '<' ) ){
                $bookingpress_load_cart_update_file = BOOKINGPRESS_CART_DIR . '/core/views/upgrade_latest_cart_data.php';
                include $bookingpress_load_cart_update_file;
                $BookingPress->bookingpress_send_anonymous_data_cron();
            }
        }

        function bookingpress_add_customize_booking_form_tab_func() {
            global $BookingPress,$bookingpress_global_options;            
            $bookingpress_global_options_arr       = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_price = $BookingPress->bookingpress_price_formatter_with_currency_symbol(25);
            $bookingpress_default_date_format = $bookingpress_global_options_arr['wp_default_date_format'];
            ?>
            <el-tab-pane name="6">                                         
                <template #label>
                    <a :class="formActiveTab == '6' ? 'bpa_center_container_tab_title' : ''" :style="[ formActiveTab == '6' ? { 'color': selected_colorpicker_values.primary_color,'font-family': selected_font_values.title_font_family} : {'color': selected_colorpicker_values.sub_title_color,'font-size': selected_font_values.content_font_size+'px','font-family': selected_font_values.title_font_family} ]">
                        <span class="material-icons-round" :style="[ formActiveTab == '6' ? { 'background': selected_colorpicker_values.primary_color, 'border-color': selected_colorpicker_values.primary_color  } : {'color': selected_colorpicker_values.content_color,'font-size': selected_font_values.color_font_size+'px'} ]">shopping_cart</span>
                        {{ tab_container_data.cart_title }}
                    </a>
                </template>
                <div class="bpa-cbf--preview-step" :style="{ 'background': selected_colorpicker_values.background_color }">
                    <div class="bpa-cbf--preview-step__body-content">
                        <div class="bpa-cbf--preview--module-container">
                            <div class="bpa-front-module-container bpa-front-module--cart">
                                <el-row type="flex" class="bpa-fmc--head">                                
                                    <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" class="bpa-fmc--left-heading">
                                        <div class="bpa-front-module-heading" :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.title_font_size+'px', 'font-family': selected_font_values.title_font_family}" >{{cart_container_data.cart_heading_title}}<span class="bpa-fmc--head-counter" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '13px', 'font-family': selected_font_values.title_font_family}" >05 {{cart_container_data.cart_item_title}}</span></div>
                                    </el-col>
                                    <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" class="bpa-fmc--right-btn">
                                        <el-button class="bpa-btn bpa-btn__medium" :style="{'color': selected_colorpicker_values.sub_title_color+' !important', 'font-size': '14px', 'font-family': selected_font_values.title_font_family}" >
                                            <span class="material-icons-round" :style="{'color': selected_colorpicker_values.content_color}">add</span>{{cart_container_data.cart_add_service_button_label}}                                            
                                        </el-button>                                        
                                    </el-col>        
                                </el-row>
                                <el-row>
                                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                        <div class="bpa-fmc--cart-items-wrap">
                                            <div class="bpa-cart__item">
                                                <div class="bpa-ci__service-brief">  
                                                    <div class="bpa-sb--left"> 
                                                        <img :src="cart_default_img" alt="img">
                                                    </div>                      
                                                    <div class="bpa-sb--right">                                                        
                                                        <div class="bpa-sbr__title"  :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size':'16px','font-family': selected_font_values.title_font_family}" ><?php esc_html_e('Sample Service 01', 'bookingpress-cart'); ?></div>
                                                        <div class="bpa-sb__options">
                                                            <div class="bpa-sbo__item" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}"><?php esc_html_e('45 Mins', 'bookingpress-cart'); ?></div>
                                                            <div class="bpa-sbo__item" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}" ><?php esc_html_e('Guests', 'bookingpress-cart'); ?>:<strong>+1</strong></div>
                                                        </div>        
                                                    </div>
                                                </div>
                                                <div class="bpa-ci__service-stfm-wrap">
                                                    <div class="bpa-ci__service-stfm-col-val" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}" >John Doe</div>
                                                </div>
                                                <div class="bpa-ci__service-dt-wrap">
                                                    <div class="bpa-ci__service-date">
                                                        <div class="bpa-ci__service-col-val" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}"><?php echo date($bookingpress_default_date_format, current_time( 'timestamp' ) ); // phpcs:ignore ?></div>
                                                    </div>
                                                    <div class="bpa-ci__service-time">
                                                        <div class="bpa-ci__service-col-val" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}">
                                                            <span v-if="booking_form_settings.bookigpress_time_format_for_booking_form == '1' || booking_form_settings.bookigpress_time_format_for_booking_form == '2'">
                                                                {{ '09:00' | bookingpress_customize_format_time(booking_form_settings.bookigpress_time_format_for_booking_form) }} to {{ '09:30' | bookingpress_customize_format_time(booking_form_settings.bookigpress_time_format_for_booking_form) }}
                                                            </span>
                                                            <span v-if="booking_form_settings.bookigpress_time_format_for_booking_form == '5' || booking_form_settings.bookigpress_time_format_for_booking_form == '6'">
                                                                {{ '09:00' | bookingpress_customize_format_time(booking_form_settings.bookigpress_time_format_for_booking_form) }} - {{ '09:30' | bookingpress_customize_format_time(booking_form_settings.bookigpress_time_format_for_booking_form) }}
                                                            </span>
                                                            <span v-if="booking_form_settings.bookigpress_time_format_for_booking_form == '3' || booking_form_settings.bookigpress_time_format_for_booking_form == '4'">
                                                                {{ '09:00' | bookingpress_customize_format_time(booking_form_settings.bookigpress_time_format_for_booking_form) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="bpa-ci__service-price">
                                                    <div class="bpa-ci__service-col-val" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}"><?php echo esc_html($bookingpress_price);  ?></div>
                                                </div>
                                                <div class="bpa-ci__service-actions">
                                                    <el-button class="bpa-btn bpa-btn--icon-without-box" :style="{ 'color': selected_colorpicker_values.sub_title_color }">
                                                        <span class="material-icons-round">mode</span>
                                                    </el-button>
                                                    <el-button class="bpa-btn bpa-btn--icon-without-box" :style="{ 'color': selected_colorpicker_values.sub_title_color }">
                                                        <span class="material-icons-round">delete</span>
                                                    </el-button>
                                                </div>
                                            </div>
                                            <div class="bpa-cart__item">
                                                <div class="bpa-ci__service-brief">  
                                                    <div class="bpa-sb--left"> 
                                                        <img :src="cart_default_img" alt="img">
                                                    </div>                      
                                                    <div class="bpa-sb--right">
                                                        <div class="bpa-sbr__title" :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': '16px', 'font-family': selected_font_values.title_font_family}"><?php esc_html_e('Sample Service 01', 'bookingpress-cart'); ?></div>
                                                        <div class="bpa-sb__options">
                                                            <div class="bpa-sbo__item" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}"><?php esc_html_e('45 Mins', 'bookingpress-cart'); ?></div>
                                                            <div class="bpa-sbo__item" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}"><?php esc_html_e('Guests', 'bookingpress-cart'); ?>:<strong>+1</strong></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="bpa-ci__service-stfm-wrap">
                                                    <div class="bpa-ci__service-stfm-col-val" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}">John Doe</div>
                                                </div>
                                                <div class="bpa-ci__service-dt-wrap">
                                                    <div class="bpa-ci__service-date">
                                                        <div class="bpa-ci__service-col-val" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}"><?php echo date($bookingpress_default_date_format, current_time( 'timestamp' ) ); // phpcs:ignore ?></div>
                                                    </div>
                                                    <div class="bpa-ci__service-time">
                                                        <div class="bpa-ci__service-col-val" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}">
                                                            <span v-if="booking_form_settings.bookigpress_time_format_for_booking_form == '1' || booking_form_settings.bookigpress_time_format_for_booking_form == '2'">
                                                                {{ '09:00' | bookingpress_customize_format_time(booking_form_settings.bookigpress_time_format_for_booking_form) }} to {{ '09:30' | bookingpress_customize_format_time(booking_form_settings.bookigpress_time_format_for_booking_form) }}
                                                            </span>
                                                            <span v-if="booking_form_settings.bookigpress_time_format_for_booking_form == '5' || booking_form_settings.bookigpress_time_format_for_booking_form == '6'">
                                                                {{ '09:00' | bookingpress_customize_format_time(booking_form_settings.bookigpress_time_format_for_booking_form) }} - {{ '09:30' | bookingpress_customize_format_time(booking_form_settings.bookigpress_time_format_for_booking_form) }}
                                                            </span>
                                                            <span v-if="booking_form_settings.bookigpress_time_format_for_booking_form == '3' || booking_form_settings.bookigpress_time_format_for_booking_form == '4'">
                                                                {{ '09:00' | bookingpress_customize_format_time(booking_form_settings.bookigpress_time_format_for_booking_form) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="bpa-ci__service-price">
                                                    <div class="bpa-ci__service-col-val" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}"><?php echo esc_html($bookingpress_price); ?></div>
                                                </div>
                                                <div class="bpa-ci__service-actions">
                                                    <el-button class="bpa-btn bpa-btn--icon-without-box" :style="{ 'color': selected_colorpicker_values.sub_title_color }">
                                                        <span class="material-icons-round">mode</span>
                                                    </el-button>
                                                    <el-button class="bpa-btn bpa-btn--icon-without-box" :style="{ 'color': selected_colorpicker_values.sub_title_color }">
                                                        <span class="material-icons-round">delete</span>
                                                    </el-button>
                                                </div>
                                            </div>
                                            <div class="bpa-cart__item">
                                                <div class="bpa-ci__service-brief">  
                                                    <div class="bpa-sb--left"> 
                                                        <img :src="cart_default_img" alt="img">
                                                    </div>                      
                                                    <div class="bpa-sb--right">
                                                        <div class="bpa-sbr__title" :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}"><?php esc_html_e('Sample Service 01', 'bookingpress-cart'); ?></div>
                                                        <div class="bpa-sb__options">
                                                            <div class="bpa-sbo__item" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}"><?php esc_html_e('45 Mins', 'bookingpress-cart'); ?></div>
                                                            <div class="bpa-sbo__item" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}"><?php esc_html_e('Guests', 'bookingpress-cart'); ?>:<strong>+1</strong></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="bpa-ci__service-stfm-wrap">
                                                    <div class="bpa-ci__service-stfm-col-val" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}">John Doe</div>
                                                </div>
                                                <div class="bpa-ci__service-dt-wrap">
                                                    <div class="bpa-ci__service-date">
                                                        <div class="bpa-ci__service-col-val" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}"><?php echo date($bookingpress_default_date_format, current_time( 'timestamp' ) ); // phpcs:ignore ?></div>
                                                    </div>
                                                    <div class="bpa-ci__service-time">
                                                        <div class="bpa-ci__service-col-val" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}">
                                                            <span v-if="booking_form_settings.bookigpress_time_format_for_booking_form == '1' || booking_form_settings.bookigpress_time_format_for_booking_form == '2'">
                                                                {{ '09:00' | bookingpress_customize_format_time(booking_form_settings.bookigpress_time_format_for_booking_form) }} to {{ '09:30' | bookingpress_customize_format_time(booking_form_settings.bookigpress_time_format_for_booking_form) }}
                                                            </span>
                                                            <span v-if="booking_form_settings.bookigpress_time_format_for_booking_form == '5' || booking_form_settings.bookigpress_time_format_for_booking_form == '6'">
                                                                {{ '09:00' | bookingpress_customize_format_time(booking_form_settings.bookigpress_time_format_for_booking_form) }} - {{ '09:30' | bookingpress_customize_format_time(booking_form_settings.bookigpress_time_format_for_booking_form) }}
                                                            </span>
                                                            <span v-if="booking_form_settings.bookigpress_time_format_for_booking_form == '3' || booking_form_settings.bookigpress_time_format_for_booking_form == '4'">
                                                                {{ '09:00' | bookingpress_customize_format_time(booking_form_settings.bookigpress_time_format_for_booking_form) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="bpa-ci__service-price">
                                                    <div class="bpa-ci__service-col-val" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}"><?php echo esc_html($bookingpress_price);  ?></div>
                                                </div>
                                                <div class="bpa-ci__service-actions">
                                                    <el-button class="bpa-btn bpa-btn--icon-without-box" :style="{ 'color': selected_colorpicker_values.sub_title_color }">
                                                        <span class="material-icons-round">mode</span>
                                                    </el-button>
                                                    <el-button class="bpa-btn bpa-btn--icon-without-box" :style="{ 'color': selected_colorpicker_values.sub_title_color }">
                                                        <span class="material-icons-round">delete</span>
                                                    </el-button>
                                                </div>
                                            </div>
                                        </div>
                                    </el-col>
                                </el-row>
                            </div>
                        </div>
                    </div>
                    <div class="bpa-front-tabs--foot" :style="{ 'background': selected_colorpicker_values.footer_background_color }">
                        <el-button class="bpa-btn bpa-btn--primary bpa-btn--front-preview" :style="{ 'background': selected_colorpicker_values.primary_color, 'border-color': selected_colorpicker_values.primary_color, color: selected_colorpicker_values.price_button_text_color,'font-size': selected_font_values.sub_title_font_size+'px','font-family': selected_font_values.title_font_family,'font-size': selected_font_values.sub_title_font_size+'px'}">
                            <span class="bpa--text-ellipsis">{{ booking_form_settings.next_button_text}} <strong>{{tab_container_data.datetime_title }}</strong></span>
                            <span class="material-icons-round">east</span>
                        </el-button>
                    </div>
                </div>
            </el-tab-pane>
            <?php
        }

        function bookingpress_add_customize_booking_form_tab_pro_func() {
            global $BookingPress,$bookingpress_global_options;            
            $bookingpress_global_options_arr       = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_price = $BookingPress->bookingpress_price_formatter_with_currency_symbol(25);
            $bookingpress_default_date_format = $bookingpress_global_options_arr['wp_default_date_format'];
            ?>
            <div class="bpa-cbf--preview-step" :style="{ 'background': selected_colorpicker_values.background_color,'border-color':selected_colorpicker_values.border_color }" v-if="current_element.name == 6">
                <div class="bpa-cbf--preview-step__body-content">
                    <div class="bpa-cbf--preview--module-container">
                        <div class="bpa-front-module-container bpa-front-module--cart">
                            <el-row type="flex" class="bpa-fmc--head">                                
                                <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" class="bpa-fmc--left-heading">
                                    <div class="bpa-front-module-heading" :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.title_font_size+'px', 'font-family': selected_font_values.title_font_family}" >{{cart_container_data.cart_heading_title}}<span class="bpa-fmc--head-counter" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '13px', 'font-family': selected_font_values.title_font_family,'border-color':selected_colorpicker_values.border_color,'background-color':selected_colorpicker_values.footer_background_color+' !important'}" >05 {{cart_container_data.cart_item_title}}</span></div>
                                </el-col>
                                <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" class="bpa-fmc--right-btn">
                                    <el-button class="bpa-btn bpa-btn__medium" :style="{'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family,'border-color':selected_colorpicker_values.border_color}" >
                                        <span class="material-icons-round" :style="{'color': selected_colorpicker_values.content_color}">add</span>{{cart_container_data.cart_add_service_button_label}}                                            
                                    </el-button>                                        
                                </el-col>        
                            </el-row>
                            <el-row>
                                <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                    <div class="bpa-fmc--cart-items-wrap">
                                        <div class="bpa-cart__item" :style="{'border-color': selected_colorpicker_values.border_color}">
                                            <div class="bpa-ci__service-brief">
                                                <svg class="bpa-ci__expand-icon" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" >
                                                    <g clip-path="url(#clip0_3161_6717)">
                                                        <path d="M10.0013 5.83268C9.54297 5.83268 9.16797 6.20768 9.16797 6.66602V9.16602H6.66797C6.20964 9.16602 5.83464 9.54102 5.83464 9.99935C5.83464 10.4577 6.20964 10.8327 6.66797 10.8327H9.16797V13.3327C9.16797 13.791 9.54297 14.166 10.0013 14.166C10.4596 14.166 10.8346 13.791 10.8346 13.3327V10.8327H13.3346C13.793 10.8327 14.168 10.4577 14.168 9.99935C14.168 9.54102 13.793 9.16602 13.3346 9.16602H10.8346V6.66602C10.8346 6.20768 10.4596 5.83268 10.0013 5.83268ZM10.0013 1.66602C5.4013 1.66602 1.66797 5.39935 1.66797 9.99935C1.66797 14.5993 5.4013 18.3327 10.0013 18.3327C14.6013 18.3327 18.3346 14.5993 18.3346 9.99935C18.3346 5.39935 14.6013 1.66602 10.0013 1.66602ZM10.0013 16.666C6.3263 16.666 3.33464 13.6743 3.33464 9.99935C3.33464 6.32435 6.3263 3.33268 10.0013 3.33268C13.6763 3.33268 16.668 6.32435 16.668 9.99935C16.668 13.6743 13.6763 16.666 10.0013 16.666Z" />
                                                    </g>
                                                    <defs>
                                                        <clipPath id="clip0_3161_6717">
                                                            <rect width="20" height="20" fill="white"/>
                                                        </clipPath>
                                                    </defs>
                                                </svg>  
                                                <div class="bpa-sb--left">
                                                    <div class="bpa-front-si__default-img" :style="{'border-color': selected_colorpicker_values.border_color}">
                                                        <svg :style="{'fill':selected_colorpicker_values.content_color}"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M13.2 7.07L10.25 11l2.25 3c.33.44.24 1.07-.2 1.4-.44.33-1.07.25-1.4-.2-1.05-1.4-2.31-3.07-3.1-4.14-.4-.53-1.2-.53-1.6 0l-4 5.33c-.49.67-.02 1.61.8 1.61h18c.82 0 1.29-.94.8-1.6l-7-9.33c-.4-.54-1.2-.54-1.6 0z"/></svg>
                                                    </div>
                                                </div>                      
                                                <div class="bpa-sb--right">                                                        
                                                    <div class="bpa-sbr__title"  :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size':'16px','font-family': selected_font_values.title_font_family}" ><?php esc_html_e('Sample Service 01', 'bookingpress-cart'); ?></div>
                                                    <div class="bpa-sb__options">
                                                        <div class="bpa-sbo__item" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}"><?php esc_html_e('45 Mins', 'bookingpress-cart'); ?></div>
                                                        <div class="bpa-sbo__item" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}" ><?php esc_html_e('Guests', 'bookingpress-cart'); ?>:<strong>+1</strong></div>
                                                    </div>        
                                                </div>
                                            </div>
                                            <div class="bpa-ci__service-stfm-wrap">
                                                <div class="bpa-ci__service-stfm-col-val" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}" >John Doe</div>
                                            </div>
                                            <div class="bpa-ci__service-dt-wrap">
                                                <div class="bpa-ci__service-date">
                                                    <div class="bpa-ci__service-col-val" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}"><?php echo date($bookingpress_default_date_format, current_time( 'timestamp' ) ); // phpcs:ignore ?></div>
                                                </div>
                                                <div class="bpa-ci__service-time">
                                                    <div class="bpa-ci__service-col-val" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}">
                                                        <span v-if="booking_form_settings.bookigpress_time_format_for_booking_form == '1' || booking_form_settings.bookigpress_time_format_for_booking_form == '2'">
                                                            {{ '09:00' | bookingpress_customize_format_time(booking_form_settings.bookigpress_time_format_for_booking_form) }} to {{ '09:30' | bookingpress_customize_format_time(booking_form_settings.bookigpress_time_format_for_booking_form) }}
                                                        </span>
                                                        <span v-if="booking_form_settings.bookigpress_time_format_for_booking_form == '5' || booking_form_settings.bookigpress_time_format_for_booking_form == '6'">
                                                            {{ '09:00' | bookingpress_customize_format_time(booking_form_settings.bookigpress_time_format_for_booking_form) }} - {{ '09:30' | bookingpress_customize_format_time(booking_form_settings.bookigpress_time_format_for_booking_form) }}
                                                        </span>
                                                        <span v-if="booking_form_settings.bookigpress_time_format_for_booking_form == '3' || booking_form_settings.bookigpress_time_format_for_booking_form == '4'">
                                                            {{ '09:00' | bookingpress_customize_format_time(booking_form_settings.bookigpress_time_format_for_booking_form) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="bpa-ci__service-price">
                                                <div class="bpa-ci__service-col-val" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}"><?php echo esc_html($bookingpress_price);  ?></div>
                                            </div>
                                            <div class="bpa-ci__service-actions">
                                                <div class="bpa-ci__sa-wrap">
                                                    <el-button class="bpa-btn bpa-btn--icon-without-box" >
                                                        <span class="material-icons-round" :style="{ 'color': selected_colorpicker_values.content_color }">mode</span>
                                                    </el-button>
                                                    <el-button class="bpa-btn bpa-btn--icon-without-box" >
                                                        <span class="material-icons-round" :style="{ 'color': selected_colorpicker_values.content_color }">delete</span>
                                                    </el-button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bpa-cart__item" :style="{'border-color': selected_colorpicker_values.border_color}">
                                            <div class="bpa-ci__service-brief"> 
                                                <svg class="bpa-ci__expand-icon" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                    <g clip-path="url(#clip0_3161_6717)">
                                                        <path d="M10.0013 5.83268C9.54297 5.83268 9.16797 6.20768 9.16797 6.66602V9.16602H6.66797C6.20964 9.16602 5.83464 9.54102 5.83464 9.99935C5.83464 10.4577 6.20964 10.8327 6.66797 10.8327H9.16797V13.3327C9.16797 13.791 9.54297 14.166 10.0013 14.166C10.4596 14.166 10.8346 13.791 10.8346 13.3327V10.8327H13.3346C13.793 10.8327 14.168 10.4577 14.168 9.99935C14.168 9.54102 13.793 9.16602 13.3346 9.16602H10.8346V6.66602C10.8346 6.20768 10.4596 5.83268 10.0013 5.83268ZM10.0013 1.66602C5.4013 1.66602 1.66797 5.39935 1.66797 9.99935C1.66797 14.5993 5.4013 18.3327 10.0013 18.3327C14.6013 18.3327 18.3346 14.5993 18.3346 9.99935C18.3346 5.39935 14.6013 1.66602 10.0013 1.66602ZM10.0013 16.666C6.3263 16.666 3.33464 13.6743 3.33464 9.99935C3.33464 6.32435 6.3263 3.33268 10.0013 3.33268C13.6763 3.33268 16.668 6.32435 16.668 9.99935C16.668 13.6743 13.6763 16.666 10.0013 16.666Z" />
                                                    </g>
                                                    <defs>
                                                        <clipPath id="clip0_3161_6717">
                                                            <rect width="20" height="20" fill="white"/>
                                                        </clipPath>
                                                    </defs>
                                                </svg> 
                                                <div class="bpa-sb--left"> 
                                                    <div class="bpa-front-si__default-img" :style="{'border-color': selected_colorpicker_values.border_color}">
                                                        <svg :style="{'fill':selected_colorpicker_values.content_color}"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M13.2 7.07L10.25 11l2.25 3c.33.44.24 1.07-.2 1.4-.44.33-1.07.25-1.4-.2-1.05-1.4-2.31-3.07-3.1-4.14-.4-.53-1.2-.53-1.6 0l-4 5.33c-.49.67-.02 1.61.8 1.61h18c.82 0 1.29-.94.8-1.6l-7-9.33c-.4-.54-1.2-.54-1.6 0z"/></svg>
                                                    </div>
                                                </div>                      
                                                <div class="bpa-sb--right">
                                                    <div class="bpa-sbr__title" :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': '16px', 'font-family': selected_font_values.title_font_family}"><?php esc_html_e('Sample Service 01', 'bookingpress-cart'); ?></div>
                                                    <div class="bpa-sb__options">
                                                        <div class="bpa-sbo__item" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}"><?php esc_html_e('45 Mins', 'bookingpress-cart'); ?></div>
                                                        <div class="bpa-sbo__item" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}"><?php esc_html_e('Guests', 'bookingpress-cart'); ?>:<strong>+1</strong></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="bpa-ci__service-stfm-wrap">
                                                <div class="bpa-ci__service-stfm-col-val" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}">John Doe</div>
                                            </div>
                                            <div class="bpa-ci__service-dt-wrap">
                                                <div class="bpa-ci__service-date">
                                                    <div class="bpa-ci__service-col-val" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}"><?php echo date($bookingpress_default_date_format, current_time( 'timestamp' ) ); // phpcs:ignore ?></div>
                                                </div>
                                                <div class="bpa-ci__service-time">
                                                    <div class="bpa-ci__service-col-val" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}">
                                                        <span v-if="booking_form_settings.bookigpress_time_format_for_booking_form == '1' || booking_form_settings.bookigpress_time_format_for_booking_form == '2'">
                                                            {{ '09:00' | bookingpress_customize_format_time(booking_form_settings.bookigpress_time_format_for_booking_form) }} to {{ '09:30' | bookingpress_customize_format_time(booking_form_settings.bookigpress_time_format_for_booking_form) }}
                                                        </span>
                                                        <span v-if="booking_form_settings.bookigpress_time_format_for_booking_form == '5' || booking_form_settings.bookigpress_time_format_for_booking_form == '6'">
                                                            {{ '09:00' | bookingpress_customize_format_time(booking_form_settings.bookigpress_time_format_for_booking_form) }} - {{ '09:30' | bookingpress_customize_format_time(booking_form_settings.bookigpress_time_format_for_booking_form) }}
                                                        </span>
                                                        <span v-if="booking_form_settings.bookigpress_time_format_for_booking_form == '3' || booking_form_settings.bookigpress_time_format_for_booking_form == '4'">
                                                            {{ '09:00' | bookingpress_customize_format_time(booking_form_settings.bookigpress_time_format_for_booking_form) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="bpa-ci__service-price">
                                                <div class="bpa-ci__service-col-val" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}"><?php echo esc_html($bookingpress_price); ?></div>
                                            </div>
                                            <div class="bpa-ci__service-actions">
                                                <div class="bpa-ci__sa-wrap">
                                                    <el-button class="bpa-btn bpa-btn--icon-without-box">
                                                        <span class="material-icons-round" :style="{ 'color': selected_colorpicker_values.content_color }">mode</span>
                                                    </el-button>
                                                    <el-button class="bpa-btn bpa-btn--icon-without-box">
                                                        <span class="material-icons-round" :style="{ 'color': selected_colorpicker_values.content_color }">delete</span>
                                                    </el-button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bpa-cart__item" :style="{'border-color': selected_colorpicker_values.border_color}">
                                            <div class="bpa-ci__service-brief">
                                                <svg class="bpa-ci__expand-icon" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                    <g clip-path="url(#clip0_3161_6717)">
                                                        <path d="M10.0013 5.83268C9.54297 5.83268 9.16797 6.20768 9.16797 6.66602V9.16602H6.66797C6.20964 9.16602 5.83464 9.54102 5.83464 9.99935C5.83464 10.4577 6.20964 10.8327 6.66797 10.8327H9.16797V13.3327C9.16797 13.791 9.54297 14.166 10.0013 14.166C10.4596 14.166 10.8346 13.791 10.8346 13.3327V10.8327H13.3346C13.793 10.8327 14.168 10.4577 14.168 9.99935C14.168 9.54102 13.793 9.16602 13.3346 9.16602H10.8346V6.66602C10.8346 6.20768 10.4596 5.83268 10.0013 5.83268ZM10.0013 1.66602C5.4013 1.66602 1.66797 5.39935 1.66797 9.99935C1.66797 14.5993 5.4013 18.3327 10.0013 18.3327C14.6013 18.3327 18.3346 14.5993 18.3346 9.99935C18.3346 5.39935 14.6013 1.66602 10.0013 1.66602ZM10.0013 16.666C6.3263 16.666 3.33464 13.6743 3.33464 9.99935C3.33464 6.32435 6.3263 3.33268 10.0013 3.33268C13.6763 3.33268 16.668 6.32435 16.668 9.99935C16.668 13.6743 13.6763 16.666 10.0013 16.666Z" />
                                                    </g>
                                                    <defs>
                                                        <clipPath id="clip0_3161_6717">
                                                            <rect width="20" height="20" fill="white"/>
                                                        </clipPath>
                                                    </defs>
                                                </svg>  
                                                <div class="bpa-sb--left"> 
                                                    <div class="bpa-front-si__default-img" :style="{'border-color': selected_colorpicker_values.border_color}">
                                                        <svg :style="{'fill':selected_colorpicker_values.content_color}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M13.2 7.07L10.25 11l2.25 3c.33.44.24 1.07-.2 1.4-.44.33-1.07.25-1.4-.2-1.05-1.4-2.31-3.07-3.1-4.14-.4-.53-1.2-.53-1.6 0l-4 5.33c-.49.67-.02 1.61.8 1.61h18c.82 0 1.29-.94.8-1.6l-7-9.33c-.4-.54-1.2-.54-1.6 0z"/></svg>
                                                    </div>
                                                </div>                      
                                                <div class="bpa-sb--right">
                                                    <div class="bpa-sbr__title" :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}"><?php esc_html_e('Sample Service 01', 'bookingpress-cart'); ?></div>
                                                    <div class="bpa-sb__options">
                                                        <div class="bpa-sbo__item" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}"><?php esc_html_e('45 Mins', 'bookingpress-cart'); ?></div>
                                                        <div class="bpa-sbo__item" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}"><?php esc_html_e('Guests', 'bookingpress-cart'); ?>:<strong>+1</strong></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="bpa-ci__service-stfm-wrap">
                                                <div class="bpa-ci__service-stfm-col-val" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}">John Doe</div>
                                            </div>
                                            <div class="bpa-ci__service-dt-wrap">
                                                <div class="bpa-ci__service-date">
                                                    <div class="bpa-ci__service-col-val" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}"><?php echo date($bookingpress_default_date_format, current_time( 'timestamp' ) ); // phpcs:ignore ?></div>
                                                </div>
                                                <div class="bpa-ci__service-time">
                                                    <div class="bpa-ci__service-col-val" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}">
                                                        <span v-if="booking_form_settings.bookigpress_time_format_for_booking_form == '1' || booking_form_settings.bookigpress_time_format_for_booking_form == '2'">
                                                            {{ '09:00' | bookingpress_customize_format_time(booking_form_settings.bookigpress_time_format_for_booking_form) }} to {{ '09:30' | bookingpress_customize_format_time(booking_form_settings.bookigpress_time_format_for_booking_form) }}
                                                        </span>
                                                        <span v-if="booking_form_settings.bookigpress_time_format_for_booking_form == '5' || booking_form_settings.bookigpress_time_format_for_booking_form == '6'">
                                                            {{ '09:00' | bookingpress_customize_format_time(booking_form_settings.bookigpress_time_format_for_booking_form) }} - {{ '09:30' | bookingpress_customize_format_time(booking_form_settings.bookigpress_time_format_for_booking_form) }}
                                                        </span>
                                                        <span v-if="booking_form_settings.bookigpress_time_format_for_booking_form == '3' || booking_form_settings.bookigpress_time_format_for_booking_form == '4'">
                                                            {{ '09:00' | bookingpress_customize_format_time(booking_form_settings.bookigpress_time_format_for_booking_form) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="bpa-ci__service-price">
                                                <div class="bpa-ci__service-col-val" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': '14px', 'font-family': selected_font_values.title_font_family}"><?php echo esc_html($bookingpress_price);  ?></div>
                                            </div>
                                            <div class="bpa-ci__service-actions">
                                                <div class="bpa-ci__sa-wrap">
                                                    <el-button class="bpa-btn bpa-btn--icon-without-box">
                                                        <span class="material-icons-round" :style="{ 'color': selected_colorpicker_values.content_color }">mode</span>
                                                    </el-button>
                                                    <el-button class="bpa-btn bpa-btn--icon-without-box">
                                                        <span class="material-icons-round"  :style="{ 'color': selected_colorpicker_values.content_color }">delete</span>
                                                    </el-button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bpa-cart__item-total">
                                            <div class="bpa-cit__item" :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': '16px', 'font-family': selected_font_values.title_font_family}"><?php esc_html_e('Cart Total', 'bookingpress-cart'); ?></div>
                                            <div class="bpa-cit__item --bpa-is-item-amt" :style="{ 'color': selected_colorpicker_values.primary_color, 'font-size': '16px', 'font-family': selected_font_values.title_font_family}"><?php esc_html_e('$75.00', 'bookingpress-cart'); ?></div>                                    
                                        </div>
                                    </div>
                                </el-col>
                            </el-row>
                        </div>
                    </div>
                </div>
                <div class="bpa-front-tabs--foot" :style="{ 'background': selected_colorpicker_values.background_color,'border-color': selected_colorpicker_values.border_color}">
                    <el-button class="bpa-btn bpa-btn--borderless" :style="{'color': selected_colorpicker_values.sub_title_color,'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.sub_title_font_size+'px'}" v-if="current_element.previous_tab != ''">
                        <span class="material-icons-round">west</span>
                        {{ booking_form_settings.goback_button_text }}
                    </el-button>
                    <el-button class="bpa-btn bpa-btn--primary bpa-btn--front-preview" :style="{ 'background': selected_colorpicker_values.primary_color, 'border-color': selected_colorpicker_values.primary_color, color: selected_colorpicker_values.price_button_text_color,'font-size': selected_font_values.sub_title_font_size+'px','font-family': selected_font_values.title_font_family,'font-size': selected_font_values.sub_title_font_size+'px'}">
                        <span class="bpa--text-ellipsis">{{ booking_form_settings.next_button_text}} <strong>{{tab_container_data[current_element.next_tab] }}</strong></span>
                        <span class="material-icons-round">east</span>
                    </el-button>
                </div>
            </div>
            <?php
        }
        
        function bookingpress_before_save_customize_form_settings_func(){
            ?>
            postData.cart_container_data = vm2.cart_container_data;
            <?php
        }

        function bookingpress_add_booking_form_customize_data_func() {
            ?>
            vm2.cart_container_data = response.data.formdata.cart_container_data;
            <?php            
        }

        function bookingpress_before_save_customize_booking_form_func($booking_form_settings_data){
            global $BookingPress;
            $booking_form_settings_data['cart_container_data'] = !empty($_POST['cart_container_data']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['cart_container_data']) : array(); // phpcs:ignore

            return $booking_form_settings_data;
        }

        function bookingpress_customize_add_dynamic_data_fields_func($bookingpress_customize_vue_data_fields) {
            $bookingpress_customize_vue_data_fields['tab_container_data']['cart_title'] = '';
            $bookingpress_customize_vue_data_fields['cart_container_data']['cart_heading_title'] = '';
            $bookingpress_customize_vue_data_fields['cart_container_data']['cart_item_title'] = '';
            $bookingpress_customize_vue_data_fields['cart_container_data']['cart_add_service_button_label'] = '';
            $bookingpress_customize_vue_data_fields['cart_container_data']['cart_total_amount_title'] = '';
            $bookingpress_customize_vue_data_fields['cart_default_img'] = BOOKINGPRESS_IMAGES_URL.'/placeholder-img.jpg';
            $bookingpress_customize_vue_data_fields['cart_container_data']['cart_service_extra_title'] = '';
            $bookingpress_customize_vue_data_fields['cart_container_data']['cart_service_extra_quantity_title'] = '';
            $bookingpress_customize_vue_data_fields['cart_container_data']['cart_deposit_title'] = '';
            $bookingpress_customize_vue_data_fields['cart_container_data']['cart_number_person_title'] = '';
            $bookingpress_customize_vue_data_fields['cart_container_data']['cart_edit_item_title'] = '';
            $bookingpress_customize_vue_data_fields['cart_container_data']['cart_remove_item_title'] = '';
            $bookingpress_customize_vue_data_fields['cart_container_data']['cart_service_duration_title'] = '';
            $bookingpress_customize_vue_data_fields['cart_container_data']['cart_staff_title'] = '';
            $bookingpress_sidebar_step_data = $bookingpress_customize_vue_data_fields['bookingpress_form_sequance_arr'];

            if(!empty($bookingpress_sidebar_step_data)) {
                $bookingpress_new_item_key = 'datetime';                
                $bookingpress_arr_keys = array_keys($bookingpress_sidebar_step_data);
                $bookingpress_arr_vals = array_values($bookingpress_sidebar_step_data);
                $bookingpress_insertafter = array_search($bookingpress_new_item_key, $bookingpress_arr_keys) + 1;                
                $bookingpress_tmp_keys2 = array_splice($bookingpress_arr_keys, $bookingpress_insertafter);
                $bookingpress_tmp_vals2 = array_splice($bookingpress_arr_vals, $bookingpress_insertafter);
                $bookingpress_arr_keys[] = "cart";
                $bookingpress_arr_vals[] = array(
                    'title' => 'cart_title',
                    'next_tab' => 'basic_details_title',
                    'previous_tab' => '1',
                    'name' => '6',
                    'icon' => 'shopping_cart',
                    'is_visible' => '1',
                    'tab_name' => '',
                    'sorting_key' => 'cart_selection'
                );
                $bookingpress_new_modified_arr = array_merge(array_combine($bookingpress_arr_keys, $bookingpress_arr_vals), array_combine($bookingpress_tmp_keys2, $bookingpress_tmp_vals2));
                if(!empty($bookingpress_new_modified_arr['datetime'])){
                    $bookingpress_new_modified_arr['datetime']['next_tab'] = 'cart_title';
                }
                $bookingpress_customize_vue_data_fields['bookingpress_form_sequance_arr'] = $bookingpress_new_modified_arr;
            }
			return $bookingpress_customize_vue_data_fields;
		}

		function bookingpress_get_booking_form_customize_data_filter_func($booking_form_settings){
			$booking_form_settings['tab_container_data']['cart_title'] = __('Cart Items', 'bookingpress-cart');		
            $booking_form_settings['cart_container_data']['cart_heading_title'] = __('My Cart Items', 'bookingpress-cart');
            $booking_form_settings['cart_container_data']['cart_item_title'] = __('Items', 'bookingpress-cart');
            $booking_form_settings['cart_container_data']['cart_add_service_button_label'] = __('Add Services', 'bookingpress-cart');
            $booking_form_settings['cart_container_data']['cart_total_amount_title'] = __('Cart Total', 'bookingpress-cart');
            $booking_form_settings['cart_container_data']['cart_service_extra_title'] = __('Extras', 'bookingpress-cart');
            $booking_form_settings['cart_container_data']['cart_service_extra_quantity_title'] = __('Qty', 'bookingpress-cart');            
            $booking_form_settings['cart_container_data']['cart_deposit_title'] = '('.__('Deposit', 'bookingpress-cart').')';
            $booking_form_settings['cart_container_data']['cart_number_person_title'] = '('.__('No. Of Person', 'bookingpress-cart').')';            
            $booking_form_settings['cart_container_data']['cart_edit_item_title'] = __('Edit', 'bookingpress-cart');
            $booking_form_settings['cart_container_data']['cart_remove_item_title'] = __('Remove', 'bookingpress-cart');
            $booking_form_settings['cart_container_data']['cart_service_duration_title'] = __('Duration', 'bookingpress-cart');
            $booking_form_settings['cart_container_data']['cart_staff_title'] = __('Staff', 'bookingpress-cart');

			return $booking_form_settings;
		}

		function bookingpress_frontend_apointment_form_add_dynamic_data_func($bookingpress_front_vue_data_fields){
			global $BookingPress;
            $cart_heading_title = $BookingPress->bookingpress_get_customize_settings('cart_heading_title', 'booking_form');
            $cart_item_title = $BookingPress->bookingpress_get_customize_settings('cart_item_title', 'booking_form');
            $cart_add_service_button_label = $BookingPress->bookingpress_get_customize_settings('cart_add_service_button_label', 'booking_form');
            $cart_total_amount_title = $BookingPress->bookingpress_get_customize_settings('cart_total_amount_title', 'booking_form');
            $cart_service_extra_title = $BookingPress->bookingpress_get_customize_settings('cart_service_extra_title', 'booking_form');
            $cart_service_extra_quantity_title = $BookingPress->bookingpress_get_customize_settings('cart_service_extra_quantity_title', 'booking_form');            
            $cart_deposit_title = $BookingPress->bookingpress_get_customize_settings('cart_deposit_title', 'booking_form');
            $cart_number_person_title = $BookingPress->bookingpress_get_customize_settings('cart_number_person_title', 'booking_form');
            $cart_edit_item_title = $BookingPress->bookingpress_get_customize_settings('cart_edit_item_title', 'booking_form');
            $cart_remove_item_title = $BookingPress->bookingpress_get_customize_settings('cart_remove_item_title', 'booking_form');
            $cart_service_duration_title = $BookingPress->bookingpress_get_customize_settings('cart_service_duration_title', 'booking_form');
            $cart_staff_title = $BookingPress->bookingpress_get_customize_settings('cart_staff_title', 'booking_form');

            $bookingpress_front_vue_data_fields['cart_heading_title'] = !empty($cart_heading_title) ? stripslashes_deep($cart_heading_title) : '';
            $bookingpress_front_vue_data_fields['cart_item_title'] = !empty($cart_item_title) ? stripslashes_deep($cart_item_title) : '';
            $bookingpress_front_vue_data_fields['cart_add_service_button_label'] = !empty($cart_add_service_button_label) ? stripslashes_deep($cart_add_service_button_label) : '';
            $bookingpress_front_vue_data_fields['cart_total_amount_title'] = !empty($cart_total_amount_title) ? stripslashes_deep($cart_total_amount_title) : '';
            $bookingpress_front_vue_data_fields['cart_service_extra_title'] = !empty($cart_service_extra_title) ? stripslashes_deep($cart_service_extra_title) : '';
            $bookingpress_front_vue_data_fields['cart_service_extra_quantity_title'] = !empty($cart_service_extra_quantity_title) ? stripslashes_deep($cart_service_extra_quantity_title) : '';            
            $bookingpress_front_vue_data_fields['cart_deposit_title'] = !empty($cart_deposit_title) ? stripslashes_deep($cart_deposit_title) : '';
            $bookingpress_front_vue_data_fields['cart_number_person_title'] = !empty($cart_number_person_title) ? stripslashes_deep($cart_number_person_title) : '';
            $bookingpress_front_vue_data_fields['cart_edit_item_title'] = !empty($cart_edit_item_title) ? stripslashes_deep($cart_edit_item_title) : '';
            $bookingpress_front_vue_data_fields['cart_remove_item_title'] = !empty($cart_remove_item_title) ? stripslashes_deep($cart_remove_item_title) : '';
            $bookingpress_front_vue_data_fields['cart_service_duration_title'] = !empty($cart_service_duration_title) ? stripslashes_deep($cart_service_duration_title) : '';
            $bookingpress_front_vue_data_fields['cart_staff_title'] = !empty($cart_staff_title) ? stripslashes_deep($cart_staff_title) : '';

			return $bookingpress_front_vue_data_fields;
		}
    
        function bookingpress_add_bookingform_label_data_func() {
            ?>
             <h5><?php esc_html_e('Cart step labels', 'bookingpress-cart'); ?></h5>                                                    
            <div class="bpa-sm--item">
                <label class="bpa-form-label"><?php esc_html_e('Step Cart', 'bookingpress-cart'); ?></label>
                <el-input v-model="tab_container_data.cart_title" class="bpa-form-control"></el-input>
            </div>      
            <div class="bpa-sm--item">
                <label class="bpa-form-label"><?php esc_html_e('Cart title', 'bookingpress-cart'); ?></label>
                <el-input v-model="cart_container_data.cart_heading_title" class="bpa-form-control"></el-input>
            </div>   
            <div class="bpa-sm--item">
                <label class="bpa-form-label"><?php esc_html_e('Cart item title', 'bookingpress-cart'); ?></label>
                <el-input v-model="cart_container_data.cart_item_title" class="bpa-form-control"></el-input>
            </div>   
            <div class="bpa-sm--item">
                <label class="bpa-form-label"><?php esc_html_e('Add service button label', 'bookingpress-cart'); ?></label>
                <el-input v-model="cart_container_data.cart_add_service_button_label" class="bpa-form-control"></el-input>
            </div>
            <div class="bpa-sm--item">
                <label class="bpa-form-label"><?php esc_html_e('Cart Duration title', 'bookingpress-cart'); ?></label>
                <el-input v-model="cart_container_data.cart_service_duration_title" class="bpa-form-control"></el-input>
            </div>
            <div class="bpa-sm--item">
                <label class="bpa-form-label"><?php esc_html_e('Cart Staff title', 'bookingpress-cart'); ?></label>
                <el-input v-model="cart_container_data.cart_staff_title" class="bpa-form-control"></el-input>
            </div>            
            <div class="bpa-sm--item">
                <label class="bpa-form-label"><?php esc_html_e('Total title', 'bookingpress-cart'); ?></label>
                <el-input v-model="cart_container_data.cart_total_amount_title" class="bpa-form-control"></el-input>
            </div> 
            <div class="bpa-sm--item">
                <label class="bpa-form-label"><?php esc_html_e('Cart No. of person title', 'bookingpress-cart'); ?></label>
                <el-input v-model="cart_container_data.cart_number_person_title" class="bpa-form-control"></el-input>
            </div>
            <div class="bpa-sm--item">
                <label class="bpa-form-label"><?php esc_html_e('Cart service extra title', 'bookingpress-cart'); ?></label>
                <el-input v-model="cart_container_data.cart_service_extra_title" class="bpa-form-control"></el-input>
            </div>
            <div class="bpa-sm--item">
                <label class="bpa-form-label"><?php esc_html_e('Cart service extra quantity title', 'bookingpress-cart'); ?></label>
                <el-input v-model="cart_container_data.cart_service_extra_quantity_title" class="bpa-form-control"></el-input>
            </div>
            <div class="bpa-sm--item">
                <label class="bpa-form-label"><?php esc_html_e('Cart deposit title', 'bookingpress-cart'); ?></label>
                <el-input v-model="cart_container_data.cart_deposit_title" class="bpa-form-control"></el-input>
            </div>
            <div class="bpa-sm--item">
                <label class="bpa-form-label"><?php esc_html_e('Cart edit item title', 'bookingpress-cart'); ?></label>
                <el-input v-model="cart_container_data.cart_edit_item_title" class="bpa-form-control"></el-input>
            </div>
            <div class="bpa-sm--item">
                <label class="bpa-form-label"><?php esc_html_e('Cart remove item title', 'bookingpress-cart'); ?></label>
                <el-input v-model="cart_container_data.cart_remove_item_title" class="bpa-form-control"></el-input>
            </div>
        
            <?php
            do_action('bookingpress_add_cart_label_outside');
        }

        function bookingpress_send_cart_items_to_front_timings( $bookingpress_dynamic_add_params_for_timeslot_request ){

            $bookingpress_dynamic_add_params_for_timeslot_request .= '
                postData.appointment_data_obj = {
                    "cart_items": vm.appointment_step_form_data.cart_items,
                    "cart_item_edit_index" : vm.appointment_step_form_data.cart_item_edit_index
                };
            ';

            return $bookingpress_dynamic_add_params_for_timeslot_request;
        }

        function bookingpress_check_available_timings_with_staffmember_func( $service_timings, $selected_service_id, $selected_date ){
            
            $bookingpress_staffmember_id = !empty( $_POST['appointment_data_obj']['bookingpress_selected_staff_member_details']['selected_staff_member_id'] ) ? intval( $_POST['appointment_data_obj']['bookingpress_selected_staff_member_details']['selected_staff_member_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.

			if( empty( $bookingpress_staffmember_id ) && !empty( $_POST['bookingpress_selected_staffmember']['selected_staff_member_id'] ) ){ // phpcs:ignore
				$bookingpress_staffmember_id = intval( $_POST['bookingpress_selected_staffmember']['selected_staff_member_id'] ); // phpcs:ignore
			}

			if( empty( $bookingpress_staffmember_id ) ){
				return $service_timings;
			}
            
            if( !empty( $_POST['appointment_data_obj']['cart_items'] ) && count( $_POST['appointment_data_obj']['cart_items'] ) > 0 ){ // phpcs:ignore
                global $tbl_bookingpress_appointment_bookings, $wpdb, $bookingpress_services, $BookingPress;

                $current_cart_item = isset( $_POST['appointment_data_obj']['cart_item_edit_index'] ) ? intval($_POST['appointment_data_obj']['cart_item_edit_index']) : -1; // phpcs:ignore
                
                $get_appointments = array_map( array($BookingPress, 'appointment_sanatize_field'), $_POST['appointment_data_obj']['cart_items'] ); //phpcs:ignore 

                foreach( $service_timings as $k => $service_timing_data ){
                    $service_start_time = $service_timing_data['start_time'].':00';
                    $service_end_time = $service_timing_data['end_time'].':00';
                    
                    foreach( $get_appointments as $cart_index => $get_appointment ){

                        if( $current_cart_item == $cart_index ){
                            continue;
                        }
                        
                        $appointment_selected_date = $get_appointment['bookingpress_selected_date'];
                        $appointment_start_time = $get_appointment['bookingpress_selected_start_time'];
                        $appointment_end_time = $get_appointment['bookingpress_selected_end_time'];

                        $booking_service_id = $get_appointment['bookingpress_service_id'];
                        if( $booking_service_id == $selected_service_id && $selected_date == $appointment_selected_date ){
                            continue;
                        }
                        
						$bookingpress_service_buffertime_before = $bookingpress_services->bookingpress_get_service_meta( $booking_service_id, 'before_buffer_time' );
						$bookingpress_service_buffertime_before_unit = $bookingpress_services->bookingpress_get_service_meta( $booking_service_id, 'before_buffer_time_unit' );

                        if( 0 < $bookingpress_service_buffertime_before ){
							if( 'h' == $bookingpress_service_buffertime_before_unit ){
								$bookingpress_service_buffertime_before = $bookingpress_service_buffertime_before * 60;
							}

							$appointment_start_time = date('H:i:s',strtotime( $appointment_start_time . '-' . $bookingpress_service_buffertime_before.' minutes' ) );
						}

                        $bookingpress_service_buffertime_after = $bookingpress_services->bookingpress_get_service_meta( $booking_service_id, 'after_buffer_time' );
						$bookingpress_service_buffertime_after_unit = $bookingpress_services->bookingpress_get_service_meta( $booking_service_id, 'after_buffer_time_unit' );

                        $selected_staffmember_id = !empty( $get_appointment['bookingpress_selected_staffmember'] ) ? $get_appointment['bookingpress_selected_staffmember'] : 0;
                        if( !empty( $selected_staffmember_id ) && $selected_staffmember_id != $bookingpress_staffmember_id ){
                            continue;
                        }
                        
                        
                        if( 0 < $bookingpress_service_buffertime_after ){
							if( 'h' == $bookingpress_service_buffertime_after_unit ){
								$bookingpress_service_buffertime_after = $bookingpress_service_buffertime_after * 60;
							}

							$appointment_end_date = date('H:i:s', strtotime( $appointment_end_date . '+' . $bookingpress_service_buffertime_after.' minutes') );
						}
                        
						if( $selected_date == $appointment_selected_date && (($appointment_start_time >= $service_start_time && $appointment_end_time <= $service_end_time) || ( $appointment_start_time < $service_end_time && $appointment_end_time > $service_start_time )) ){
							unset( $service_timings[$k] );
						}
                    }
                }
            }
            
            return $service_timings;
        }

        function bookingpress_reset_selected_time_data_after_service_changed( $bookingpress_after_change_service_extras ){
            $bookingpress_after_change_service_extras .= '
                let cart_edit_index = vm.appointment_step_form_data.cart_item_edit_index;
                if( -1 < cart_edit_index ){
                    vm.appointment_step_form_data.selected_start_time = "";
                    vm.appointment_step_form_data.selected_end_time = "";
                }
            ';
            return $bookingpress_after_change_service_extras;
        }

        function bookingpress_modify_timeslot_for_cart( $total_booked_appointments, $selected_date ){
            
            if( !empty( $_POST['appointment_data_obj']['cart_items'] ) && count( $_POST['appointment_data_obj']['cart_items'] ) > 0 ){ // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
                global $BookingPress;
                $cart_items = array_map( array($BookingPress, 'appointment_sanatize_field'), $_POST['appointment_data_obj']['cart_items'] ); //phpcs:ignore 

                $selected_service_id = !empty( $_POST['service_id'] ) ? intval( $_POST['service_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.

                if( empty( $selected_service_id ) ){
                    return $total_booked_appointments;
                }

                $bookingpress_shared_service_timeslot = $BookingPress->bookingpress_get_settings('share_timeslot_between_services', 'general_setting');

                foreach( $cart_items as $cart_index => $cart_data ){

                    $cart_item_service_id = $cart_data['bookingpress_service_id'];
                    
                    if( isset($_POST['appointment_data_obj']['cart_item_edit_index']) && $_POST['appointment_data_obj']['cart_item_edit_index'] == $cart_index ){ // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
                        continue;
                    }
                    
                    if( !empty( $cart_data['bookingpress_selected_staffmember'] ) ){
                        $selected_staffmember = !empty( $_POST['appointment_data_obj']['bookingpress_selected_staff_member_details']['selected_staff_member_id'] ) ? intval( $_POST['appointment_data_obj']['bookingpress_selected_staff_member_details']['selected_staff_member_id'] ) : ''; // phpcs:ignore
                        
                        if( empty( $selected_staffmember ) && !empty( $_POST['bookingpress_selected_staffmember']['selected_staff_member_id'] ) ){ // phpcs:ignore
                            $selected_staffmember = intval( $_POST['bookingpress_selected_staffmember']['selected_staff_member_id'] ); // phpcs:ignore
                        }

                        if( $selected_staffmember != $cart_data['bookingpress_selected_staffmember'] ){
                            continue;
                        }
                    }
			        
                    $bookingpress_share_timeslot_between_services_type = $BookingPress->bookingpress_get_settings('share_timeslot_between_services_type', 'general_setting');			
                    $bookingpress_category_id = isset($cart_data['bookingpress_category_id']) ? $cart_data['bookingpress_category_id'] : '';
                    $bookingpress_selected_category = (isset($_POST['appointment_data_obj']['selected_category']))?$_POST['appointment_data_obj']['selected_category']:'';

                    if($bookingpress_selected_category == 0){
                        $bookingpress_all_related_category_service = (isset($_POST['appointment_data_obj']['related_category_service']))?$_POST['appointment_data_obj']['related_category_service']:''; //phpcs:ignore
						$selected_service = (isset($_POST['appointment_data_obj']['selected_service']))? intval($_POST['appointment_data_obj']['selected_service']):''; // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
						if(!empty($bookingpress_all_related_category_service) && !empty($selected_service)){
							foreach ($bookingpress_all_related_category_service as $category_id => $category_wise_services) {
                                if(!empty($category_wise_services) && is_array($category_wise_services)){
                                    $found = array_search($selected_service, $category_wise_services);
                                    if ($found !== false) {
                                        $bookingpress_selected_category = $category_id;
                                        break; // Stop searching after finding the value
                                    } 
                                }
							}
						}
					}

                    if($bookingpress_shared_service_timeslot == 'true' && $bookingpress_share_timeslot_between_services_type == 'service_category' && $cart_data['bookingpress_selected_date'] == $selected_date){
                        if($bookingpress_category_id == $bookingpress_selected_category) {
                            $cart_data['bookingpress_appointment_time'] = $cart_data['bookingpress_store_start_time'].':00';
                            $cart_data['bookingpress_appointment_end_time'] = $cart_data['bookingpress_store_end_time'].':00';
                            $cart_data['bookingpress_selected_extra_members'] = ('number_of_guests' == $cart_data['bookingpress_bring_anyone_selected_members'] ) ? 0 : $cart_data['bookingpress_bring_anyone_selected_members'];   
                        
                            $total_booked_appointments[] = $cart_data;
                        }
                    }
                    else if( $selected_service_id == $cart_item_service_id && $cart_data['bookingpress_selected_date'] == $selected_date){
                        $cart_data['bookingpress_appointment_time'] = $cart_data['bookingpress_store_start_time'].':00';
                        $cart_data['bookingpress_appointment_end_time'] = $cart_data['bookingpress_store_end_time'].':00';
                        $cart_data['bookingpress_selected_extra_members'] = ('number_of_guests' == $cart_data['bookingpress_bring_anyone_selected_members'] ) ? 0 : $cart_data['bookingpress_bring_anyone_selected_members'];   
                        
                        $total_booked_appointments[] = $cart_data;

                    } else if( !empty($selected_staffmember) && $selected_staffmember == $cart_data['bookingpress_selected_staffmember'] && $selected_service_id != $cart_item_service_id && $cart_data['bookingpress_selected_date'] == $selected_date  ){
                        $cart_data['bookingpress_appointment_time'] = $cart_data['bookingpress_store_start_time'].':00';
                        $cart_data['bookingpress_appointment_end_time'] = $cart_data['bookingpress_store_end_time'].':00';
                        $cart_data['bookingpress_selected_extra_members'] = ('number_of_guests' == $cart_data['bookingpress_bring_anyone_selected_members'] ) ? 0 : $cart_data['bookingpress_bring_anyone_selected_members'];
                        
                        $total_booked_appointments[] = $cart_data;
                    } else if( $bookingpress_shared_service_timeslot == 'true' && $cart_data['bookingpress_selected_date'] == $selected_date) {
                        $cart_data['bookingpress_appointment_time'] = $cart_data['bookingpress_store_start_time'].':00';
                        $cart_data['bookingpress_appointment_end_time'] = $cart_data['bookingpress_store_end_time'].':00';
                        $cart_data['bookingpress_selected_extra_members'] = ('number_of_guests' == $cart_data['bookingpress_bring_anyone_selected_members'] ) ? 0 : $cart_data['bookingpress_bring_anyone_selected_members'];
                        
                        $total_booked_appointments[] = $cart_data;
                    }
                }
            }

            return $total_booked_appointments;
        }

        function bookingpress_calendar_integration_urls(){  
        ?>
                wp.hooks.addFilter('bookingpress_change_calendar_url', 'bookingpress-cart-addon', function( calendar_link, selected_calendar, bookingpress_appointment_id ){                    
                    calendar_link = '<?php echo esc_url( get_home_url() ) . '?page=bookingpress_download&action=generate_ics_with_cart&state=' . esc_html( wp_create_nonce( 'bookingpress_calendar_ics' ) ) . '&order_id='; ?>' + bookingpress_appointment_id + '&selectedCalendar=' + selected_calendar;
                    return calendar_link;
                }, 10);
                if( 'undefined' != typeof vm.bookingpress_google_calendar_link && '' != vm.bookingpress_google_calendar_link ){
                    let calendar_link = '<?php echo esc_url( get_home_url() ) . '?page=bookingpress_download&action=generate_ics_with_cart&state=' . esc_html( wp_create_nonce( 'bookingpress_calendar_ics' ) ) . '&order_id='; ?>' + bookingpress_appointment_id + '&selectedCalendar=google_calendar';
                    vm.bookingpress_google_calendar_link = calendar_link;
                }
                if( 'undefined' != typeof vm.bookingpress_yahoo_calendar_link && '' != vm.bookingpress_yahoo_calendar_link ){
                    let calendar_link = '<?php echo esc_url( get_home_url() ) . '?page=bookingpress_download&action=generate_ics_with_cart&state=' . esc_html( wp_create_nonce( 'bookingpress_calendar_ics' ) ) . '&order_id='; ?>' + bookingpress_appointment_id + '&selectedCalendar=yahoo_calendar';
                    vm.bookingpress_yahoo_calendar_link = calendar_link;
                }
        <?php
        }

        function bookingpress_generate_ics_with_cart_items(){
            if ( ! empty( $_GET['page'] ) && 'bookingpress_download' == $_GET['page'] && ! empty( $_GET['action'] ) && 'generate_ics_with_cart' == $_GET['action'] ) {
                $nonce = ! empty( $_GET['state'] ) ? sanitize_text_field( $_GET['state'] ) : '';
                
                if ( ! wp_verify_nonce( $nonce, 'bookingpress_calendar_ics' ) ) {
					return false;
				}

				if ( empty( $_GET['order_id'] ) ) {
					return false;
				}

                $bookingpress_single_booking_id = (isset($_COOKIE['bookingpress_single_booking_id']))?$_COOKIE['bookingpress_single_booking_id']:'';

				$order_id = intval( $_GET['order_id'] );

                global $wpdb,$tbl_bookingpress_entries, $tbl_bookingpress_appointment_bookings, $BookingPress, $bookingpress_appointment_bookings;

                $get_all_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$tbl_bookingpress_appointment_bookings}` WHERE bookingpress_order_id = %d", $order_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally.

                if(!empty($bookingpress_single_booking_id)){
                    $bookingpress_entry_id = base64_decode($bookingpress_single_booking_id); 
                    $get_all_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$tbl_bookingpress_appointment_bookings}` WHERE bookingpress_entry_id = %d", $bookingpress_entry_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally.
                }

                if( !empty( $get_all_appointments ) ){

                    $calendar_vevent = '';
                    
                    foreach( $get_all_appointments as $appointment_data ){
                        
                        $service_id              = intval( $appointment_data['bookingpress_service_id'] );
						$bookingpress_start_time = $service_start_time = sanitize_text_field( $appointment_data['bookingpress_appointment_time'] );
						$bookingpress_end_time   = sanitize_text_field( $appointment_data['bookingpress_appointment_end_time'] );	
						
						$bookingpress_appointment_date_temp = $appointment_data['bookingpress_appointment_date'];
						if ($bookingpress_end_time === '24:00:00') {
							$bookingpress_appointment_date_temp = date('Y-m-d', strtotime($appointment_data['bookingpress_appointment_date'] . ' +1 day'));
							$bookingpress_end_time = '00:00:00';
						}

                        
						$bookingpress_start_time = date( 'Ymd', strtotime( $appointment_data['bookingpress_appointment_date'] ) ) . 'T' . date( 'His', strtotime( $bookingpress_start_time ) );
                        
						//$bookingpress_end_time = date( 'Ymd', strtotime( $appointment_data['bookingpress_appointment_date'] ) ) . 'T' . date( 'His', strtotime( $bookingpress_end_time ) );
                        
						$bookingpress_end_time = date( 'Ymd', strtotime( $bookingpress_appointment_date_temp ) ) . 'T' . date( 'His', strtotime( $bookingpress_end_time ) );
                        
                        //echo $bookingpress_start_time .' ---- ' . $bookingpress_end_time .' - --<br/>';



						$user_timezone             = wp_timezone_string();
						$bookingpress_service_name = ! empty( $appointment_data['bookingpress_service_name'] ) ? sanitize_text_field( $appointment_data['bookingpress_service_name'] ) : '';
     
                        $booking_stime = $bookingpress_appointment_bookings->bookingpress_convert_date_time_to_utc( $appointment_data['bookingpress_appointment_date'], $bookingpress_start_time );
                        //$booking_etime = $bookingpress_appointment_bookings->bookingpress_convert_date_time_to_utc( $appointment_data['bookingpress_appointment_date] ,  $bookingpress_end_time);
                        $booking_etime = $bookingpress_appointment_bookings->bookingpress_convert_date_time_to_utc( $bookingpress_appointment_date_temp,  $bookingpress_end_time);
                        $current_dtime = $bookingpress_appointment_bookings->bookingpress_convert_date_time_to_utc( date( 'm/d/Y' ), 'g:i A' );

                        /* For Day Service Fixed Issue */
                        $bookingpress_service_duration_unit = (isset($appointment_data['bookingpress_service_duration_unit']))?$appointment_data['bookingpress_service_duration_unit']:'';
                        if($bookingpress_service_duration_unit == 'd'){                            
                            $bookingpress_service_duration_val = (isset($appointment_data['bookingpress_service_duration_val']))?$appointment_data['bookingpress_service_duration_val']:'';
                            if($bookingpress_service_duration_val > 1){
                                $booking_stime = $bookingpress_appointment_bookings->bookingpress_convert_date_time_to_utc( $appointment_data['bookingpress_appointment_date'], '00:00:00' );
                                $bookingpress_appointment_date_temp = date('Y-m-d',strtotime($bookingpress_appointment_date_temp. ' + '.$bookingpress_service_duration_val.' days'));
                                $booking_etime = $bookingpress_appointment_bookings->bookingpress_convert_date_time_to_utc( $bookingpress_appointment_date_temp,  '24:00:00');
                            }
                        }

                        $calendar_vevent .= "BEGIN:VEVENT\r\n";
                        $calendar_vevent .= 'UID:' . md5( $service_start_time ) . "\r\n";
                        $calendar_vevent .= 'DTSTART:' . $booking_stime . "\r\n";
                        $calendar_vevent .= "SEQUENCE:0\r\n";
                        $calendar_vevent .= "TRANSP:OPAQUE\r\n";
                        $calendar_vevent .= "DTEND:{$booking_etime}\r\n";
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

        function bookingpress_check_coupon_validity_from_outside_func($response, $bookingpress_appointment_details){
            global $wpdb, $tbl_bookingpress_coupons, $BookingPress, $bookingpress_deposit_payment, $bookingpress_coupons;
            if($bookingpress_coupons->bookingpress_check_coupon_module_activation()){
                if(!empty($bookingpress_appointment_details) && !empty($bookingpress_appointment_details['cart_items']) ){
                    $bookingpress_cart_items = $bookingpress_appointment_details['cart_items'];
                    $bookingpress_is_coupon_applied = 0;
                    $bookingpress_coupon_applied_msg = "";
                    $bookingpress_applied_coupon_data = array();

                    $bookingpress_coupon_code = !empty($bookingpress_appointment_details['coupon_code']) ? $bookingpress_appointment_details['coupon_code'] : '';
                    $bookingpress_payable_amount = !empty($bookingpress_appointment_details['bookingpress_cart_total']) ? floatval($bookingpress_appointment_details['bookingpress_cart_total']) : 0;
                    foreach($bookingpress_cart_items as $k => $v){
                        $bookingpress_service_id = !empty($v['bookingpress_service_id']) ? intval($v['bookingpress_service_id']) : 0;
                        $bookingpress_applied_coupon_response = $bookingpress_coupons->bookingpress_apply_coupon_code( $bookingpress_coupon_code, $bookingpress_service_id );
                        $bookingpress_coupon_applied_msg = $bookingpress_applied_coupon_response['msg'];
                        if ( is_array( $bookingpress_applied_coupon_response ) && ! empty( $bookingpress_applied_coupon_response ) && !empty($bookingpress_applied_coupon_response['coupon_status']) && ($bookingpress_applied_coupon_response['coupon_status'] == "success") ) {
                            $bookingpress_is_coupon_applied = 1;
                            $bookingpress_applied_coupon_data = $bookingpress_applied_coupon_response['coupon_data'];
                        }else{                                                        
                            $bookingpress_is_coupon_applied = 0;
                            $bookingpress_applied_coupon_data = array();                            
                        }
                    }
                    if($bookingpress_is_coupon_applied == 1) {                                                
                        $bookingpress_after_discount_amounts = $bookingpress_coupons->bookingpress_calculate_bookingpress_coupon_amount( $bookingpress_coupon_code, $bookingpress_payable_amount );
                        if($bookingpress_after_discount_amounts['discounted_amount'] <= $bookingpress_payable_amount ) {
                            $response['final_payable_amount'] = ! empty( $bookingpress_after_discount_amounts['final_payable_amount'] ) ? floatval( $bookingpress_after_discount_amounts['final_payable_amount'] ) : 0;
                            
                            $response['discounted_amount']    = ! empty( $bookingpress_after_discount_amounts['discounted_amount'] ) ? $BookingPress->bookingpress_price_formatter_with_currency_symbol( floatval( $bookingpress_after_discount_amounts['discounted_amount'] ) ) : 0;
                            
                            $response['coupon_discount_amount'] = ! empty( $bookingpress_after_discount_amounts['discounted_amount'] ) ? $bookingpress_after_discount_amounts['discounted_amount'] : 0;
                            $response['coupon_discount_amount_with_currecny'] = $response['discounted_amount'];

                            $response['total_payable_amount'] = $response['final_payable_amount'];
                            $response['total_payable_amount_with_currency'] = !empty( $response['final_payable_amount'] ) ? $BookingPress->bookingpress_price_formatter_with_currency_symbol( $response['final_payable_amount'] ) : 0;;

                            $response['variant']     = "success";
                            $response['title']       = esc_html__( 'Success', 'bookingpress-cart' );
                            $response['msg']         = $bookingpress_coupon_applied_msg;
                            $response['coupon_data'] = $bookingpress_applied_coupon_data;
                        } else {
                            $response['variant']     = "error";
                            $response['title']       = esc_html__( 'Error', 'bookingpress-cart' );
                            $response['msg']         = __( 'Coupon code not applied on this service', 'bookingpress-cart' );
                        }
                    }else{
                        $response['variant']     = "error";
                        $response['title']       = esc_html__( 'Error', 'bookingpress-cart' );
                        $response['msg']         = $bookingpress_coupon_applied_msg;
                        $response['coupon_data'] = $bookingpress_applied_coupon_data;
                    }
                }
            }

            return $response;
        }

        function bookingpress_modify_appointment_data_func($bookingpress_appointment_data, $payment_gateway, $posted_data){
            global $BookingPress, $wpdb, $tbl_bookingpress_entries, $bookingpress_debug_payment_log_id, $bookingpress_coupons, $tbl_bookingpress_appointment_meta, $tbl_bookingpress_extra_services, $bookingpress_pro_staff_members, $tbl_bookingpress_staffmembers, $bookingpress_deposit_payment, $tbl_bookingpress_staffmembers_services, $bookingpress_other_debug_log_id;

            $return_data = array(
				'service_data'     => array(),
				'payable_amount'   => 0,
				'customer_details' => array(),
				'currency'         => '',
			);
            
            if(!empty($bookingpress_appointment_data) && !empty($bookingpress_appointment_data) && !empty($posted_data) && !empty($bookingpress_appointment_data['cart_items']) ){
                $bookingpress_cart_items = $bookingpress_appointment_data['cart_items'];                
                $bookingpress_currency_name   = $BookingPress->bookingpress_get_settings( 'payment_default_currency', 'payment_setting' );
				$return_data['currency']      = $bookingpress_currency_name;
				$return_data['currency_code'] = $BookingPress->bookingpress_get_currency_code( $bookingpress_currency_name );                
                $__payable_amount   = $bookingpress_appointment_data['total_payable_amount'];
                $payment_gateway    = $__payable_amount == 0 ? ' - ' : $payment_gateway;                

                $bookingpress_internal_note           = ! empty( $bookingpress_appointment_data['appointment_note'] ) ? sanitize_textarea_field( $bookingpress_appointment_data['appointment_note'] ) : $bookingpress_appointment_data['form_fields']['appointment_note'];
                $customer_email     = !empty($bookingpress_appointment_data['form_fields']['customer_email']) ? $bookingpress_appointment_data['form_fields']['customer_email'] : $bookingpress_appointment_data['customer_email'];
				$customer_username  = !empty( $bookingpress_appointment_data['form_fields']['customer_name'] ) ? sanitize_text_field( $bookingpress_appointment_data['form_fields']['customer_name'] ) : (!empty( $bookingpress_appointment_data['customer_name'] ) ? sanitize_text_field($bookingpress_appointment_data['customer_name'] ) : '');
				$customer_firstname = !empty( $bookingpress_appointment_data['form_fields']['customer_firstname'] ) ? sanitize_text_field( $bookingpress_appointment_data['form_fields']['customer_firstname'] ) : (!empty($bookingpress_appointment_data['customer_firstname']) ? sanitize_text_field($bookingpress_appointment_data['customer_firstname'] ) : '');
				$customer_lastname  = !empty( $bookingpress_appointment_data['form_fields']['customer_lastname'] ) ? sanitize_text_field( $bookingpress_appointment_data['form_fields']['customer_lastname'] ) : (!empty($bookingpress_appointment_data['customer_lastname']) ? sanitize_text_field($bookingpress_appointment_data['customer_lastname'] ) : '');
				$customer_phone     = !empty( $bookingpress_appointment_data['form_fields']['customer_phone'] ) ? sanitize_text_field( $bookingpress_appointment_data['form_fields']['customer_phone'] ) : ( !empty($bookingpress_appointment_data['customer_phone']) ? sanitize_text_field($bookingpress_appointment_data['customer_phone'] ) : '' );
				$customer_country   = !empty( $bookingpress_appointment_data['form_fields']['customer_phone_country'] ) ? sanitize_text_field( $bookingpress_appointment_data['form_fields']['customer_phone_country'] ) : ( !empty($bookingpress_appointment_data['customer_phone_country']) ? sanitize_text_field($bookingpress_appointment_data['customer_phone_country'] ) : '');
				$customer_phone_dial_code = !empty($bookingpress_appointment_data['customer_phone_dial_code']) ? $bookingpress_appointment_data['customer_phone_dial_code'] : '';
				$customer_timezone = !empty($bookingpress_appointment_data['bookingpress_customer_timezone']) ? $bookingpress_appointment_data['bookingpress_customer_timezone'] : wp_timezone_string();

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
					'customer_username'  => $customer_username,
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

                if($payment_gateway != "on-site" && $payment_gateway != " - " && $bookingpress_deposit_payment->bookingpress_check_deposit_payment_module_activation() && !empty($bookingpress_appointment_data['bookingpress_deposit_payment_method']) && ($bookingpress_appointment_data['bookingpress_deposit_payment_method'] == "deposit_or_full_price" ) ){
                    if( 'percentage' != $bookingpress_appointment_data['deposit_payment_type'] || ( 'percentage' != $bookingpress_appointment_data['deposit_payment_type'] && 100 > $bookingpress_appointment_data['deposit_payment_amount'] ) ){
                        $__payable_amount = $bookingpress_appointment_data['bookingpress_deposit_total'];
                        $bookingpress_due_amount = $bookingpress_appointment_data['bookingpress_deposit_due_amount_total'];
                    }
                }

                $return_data['payable_amount'] = (float) $__payable_amount;

                $bookingpress_cart_total_items = count($bookingpress_cart_items);

                //Get cart order id
                $bookingpress_cart_order_id = 0;                
                if($bookingpress_cart_total_items > 1){
                    $bookingpress_cart_order_id = get_option('bookingpress_cart_order_id', true);
                    if(empty($bookingpress_cart_order_id)){
                        $bookingpress_cart_order_id = 1;
                    }else{
                        $bookingpress_cart_order_id = floatval($bookingpress_cart_order_id) + 1;
                    }
                    update_option('bookingpress_cart_order_id', $bookingpress_cart_order_id);
                }


                $bookingpress_timeslot_display_in_client_timezone = $BookingPress->bookingpress_get_settings( 'show_bookingslots_in_client_timezone', 'general_setting' );

                foreach($bookingpress_cart_items as $k => $v){
                    $__payable_amount_tmp = $v['bookingpress_service_original_price'];
                    $bookingpress_due_amount_tmp = 0;

                    $bookingpress_selected_service_id     = sanitize_text_field( $v['bookingpress_service_id'] );
                    $bookingpress_appointment_booked_date = sanitize_text_field( $v['bookingpress_selected_date'] );
                    $bookingpress_selected_start_time     = sanitize_text_field( $v['bookingpress_selected_start_time'] );
                    $bookingpress_selected_end_time       = sanitize_text_field($v['bookingpress_selected_end_time']);

                    if( !empty( $bookingpress_timeslot_display_in_client_timezone ) && 'true' == $bookingpress_timeslot_display_in_client_timezone ){
                        $bookingpress_appointment_booked_date = !empty( $v['store_selected_date'] ) ? sanitize_text_field( $v['store_selected_date'] ) : $bookingpress_appointment_booked_date;

                        $bookingpress_selected_start_time = !empty( $v['bookingpress_store_start_time'] ) ? sanitize_text_field( $v['bookingpress_store_start_time'] ) : $bookingpress_selected_start_time;

                        $bookingpress_selected_end_time = !empty( $v['bookingpress_store_end_time'] ) ? sanitize_text_field( $v['bookingpress_store_end_time'] ) : $bookingpress_selected_end_time;
                    }

                    $service_data                         = $BookingPress->get_service_by_id( $bookingpress_selected_service_id );
				    $bookingpress_service_price = $service_data['bookingpress_service_price'];
                    $service_duration_vals                = $BookingPress->bookingpress_get_service_end_time( $bookingpress_selected_service_id, $bookingpress_selected_start_time );
                    $service_data['service_start_time']   = sanitize_text_field( $service_duration_vals['service_start_time'] );
                    $service_data['service_end_time']     = sanitize_text_field( $service_duration_vals['service_end_time'] );

                    if($bookingpress_cart_total_items > 1){
                        array_push($return_data['service_data'], $service_data);
                    }else{
                        $return_data['service_data'] = $service_data;
                    }                    

                    $bookingpress_deposit_selected_type = "";
                    $bookingpress_deposit_selected_amount = 0;
                    $bookingpress_deposit_details = array();
                    if($bookingpress_deposit_payment->bookingpress_check_deposit_payment_module_activation() && (!empty($bookingpress_appointment_data['bookingpress_deposit_payment_method']) && ($bookingpress_appointment_data['bookingpress_deposit_payment_method'] == "deposit_or_full_price") && !empty($bookingpress_appointment_data['selected_payment_method'] && $bookingpress_appointment_data['selected_payment_method'] != 'on-site' ) ) ){
                        $bookingpress_deposit_selected_type = !empty($bookingpress_appointment_data['deposit_payment_type']) ? $bookingpress_appointment_data['deposit_payment_type'] : '';

                        $bookingpress_deposit_selected_amount = isset($v['bookingpress_deposit_price']) ? floatval($v['bookingpress_deposit_price']) : $__payable_amount;
                        $bookingpress_due_amount_tmp = isset($v['bookingpress_deposit_due_amount']) ? floatval($v['bookingpress_deposit_due_amount']) : $__payable_amount;
                        //$__payable_amount = $bookingpress_deposit_selected_amount;
                        $bookingpress_deposit_details = array(
                            'deposit_selected_type' => $bookingpress_deposit_selected_type,
                            'deposit_amount' => $bookingpress_deposit_selected_amount,
                            'deposit_due_amount' => $bookingpress_due_amount_tmp,
                        );

                    }

                    $bookingpress_selected_extra_members = (!empty($v['bookingpress_bring_anyone_selected_members']) && ($v['bookingpress_bring_anyone_selected_members'] != 'number_of_guests') ) ? $v['bookingpress_bring_anyone_selected_members'] : 0;

                    $bookingpress_extra_services = !empty($v['bookingpress_selected_extras_ids']) ? $v['bookingpress_selected_extras_ids'] : array();
                    $bookingpress_extra_services_qty = !empty($v['bookingpress_selected_extras_qty']) ? $v['bookingpress_selected_extras_qty'] : array();

                    $bookingpress_extra_services_db_details = array();
                    if(!empty($bookingpress_extra_services)){
                        foreach($bookingpress_extra_services as $k2 => $v2){
                            $bookingpress_tmp_extra_details = array();
                            $bookingpress_extra_service_id = intval($v2);
                            $bookingpress_extra_service_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_extra_services} WHERE bookingpress_extra_services_id = %d", $bookingpress_extra_service_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_extra_services is a table name. false alarm

                            if(!empty($bookingpress_extra_service_details)){
                                $bookingpress_extra_service_price = ! empty( $bookingpress_extra_service_details['bookingpress_extra_service_price'] ) ? floatval( $bookingpress_extra_service_details['bookingpress_extra_service_price'] ) : 0;

                                $bookingpress_selected_qty = !empty($bookingpress_extra_services_qty[$bookingpress_extra_service_id]) ? intval($bookingpress_extra_services_qty[$bookingpress_extra_service_id]) : 1;
                                $bookingpress_tmp_extra_details['bookingpress_selected_qty'] = $bookingpress_selected_qty;
                                $bookingpress_tmp_extra_details['bookingpress_is_selected'] = true;                                
                                if(!empty($bookingpress_selected_qty)){
                                    $bookingpress_final_price = $bookingpress_extra_service_price * $bookingpress_selected_qty;
                                    $bookingpress_tmp_extra_details['bookingpress_final_payable_price'] = $bookingpress_final_price;
                                    $bookingpress_tmp_extra_details['bookingpress_extra_service_details'] = $bookingpress_extra_service_details;
                                    array_push($bookingpress_extra_services_db_details, $bookingpress_tmp_extra_details);
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
                        $bookingpress_selected_staffmember = !empty($v['bookingpress_selected_staffmember']) ? $v['bookingpress_selected_staffmember'] : 0;
                        if(!empty($bookingpress_selected_staffmember)){
                            $bookingpress_staffmember_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_staffmember_id = %d", $bookingpress_selected_staffmember), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_staffmembers is table name defined globally.
                            $bookingpress_staff_member_firstname = !empty($bookingpress_staffmember_details['bookingpress_staffmember_firstname']) ? $bookingpress_staffmember_details['bookingpress_staffmember_firstname'] : '';
                            $bookingpress_staff_member_lastname = !empty($bookingpress_staffmember_details['bookingpress_staffmember_lastname']) ? $bookingpress_staffmember_details['bookingpress_staffmember_lastname'] : '';
                            $bookingpress_staff_member_email_address = !empty($bookingpress_staffmember_details['bookingpress_staffmember_email']) ? $bookingpress_staffmember_details['bookingpress_staffmember_email'] : '';

                            //Fetch staff member price
                            $bookingpress_staffmember_price_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_staffmembers_services} WHERE bookingpress_staffmember_id = %d AND bookingpress_service_id = %d", $bookingpress_selected_staffmember, $bookingpress_selected_service_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_staffmembers_services is table name defined globally.
                            $bookingpress_staffmember_price = !empty($bookingpress_staffmember_price_details['bookingpress_service_price']) ? floatval($bookingpress_staffmember_price_details['bookingpress_service_price']) : 0;
                        }
                    }
                    $bookingpress_total_amount = $v['bookingpress_service_original_price'];

                    $bookingpress_is_cart = 0;
                    if($bookingpress_cart_total_items > 1){
                        $bookingpress_is_cart = 1;
                    }

                    $bookingpress_entry_details = array(
                        'bookingpress_customer_id'                    => $bookingpress_customer_id,
                        'bookingpress_order_id'                       => $bookingpress_cart_order_id,
                        'bookingpress_is_cart'                        => $bookingpress_is_cart,
                        'bookingpress_customer_name'                  => $customer_username,
                        'bookingpress_customer_phone'                 => $customer_phone,
                        'bookingpress_customer_firstname'             => $customer_firstname,
                        'bookingpress_customer_lastname'              => $customer_lastname,
                        'bookingpress_customer_country'               => $customer_country,
                        'bookingpress_customer_phone_dial_code'       => $customer_phone_dial_code,
                        'bookingpress_customer_email'                 => $customer_email,
                        'bookingpress_customer_timezone'              => $customer_timezone,
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
                        'bookingpress_due_amount'                     => $bookingpress_due_amount_tmp,
                        'bookingpress_total_amount'                   => (float) $bookingpress_appointment_data['total_payable_amount'],
                        'bookingpress_created_at'                     => current_time( 'mysql' ),
                    );

                    $bookingpress_entry_details = apply_filters( 'bookingpress_modify_cart_entry_data_before_insert', $bookingpress_entry_details, $posted_data,$v);

                    $bookingpress_entry_details = apply_filters( 'bookingpress_modify_entry_data_before_insert', $bookingpress_entry_details, $posted_data );
    
                    do_action( 'bookingpress_payment_log_entry', $payment_gateway, 'submit appointment form front', 'bookingpress pro', $bookingpress_entry_details, $bookingpress_debug_payment_log_id );
    
                    $wpdb->insert( $tbl_bookingpress_entries, $bookingpress_entry_details );
                    $entry_id = $wpdb->insert_id;


                    do_action( 'bookingpress_after_entry_data_insert', $entry_id,$v);

                }

                if($bookingpress_cart_total_items > 1){
                    $return_data['entry_id'] = $bookingpress_cart_order_id;
                    $return_data['is_cart'] = 1;    
                }else{
                    $return_data['entry_id'] = $entry_id;
                    $return_data['is_cart'] = 0;    
                }
                $return_data['booking_form_redirection_mode'] = $bookingpress_appointment_data['booking_form_redirection_mode'];

                $bookingpress_uniq_id = $bookingpress_appointment_data['bookingpress_uniq_id'];
                
                setcookie("bookingpress_last_request_id", "", time()-3600, "/");
                setcookie("bookingpress_referer_url", "", time() - 3600, "/");
                if($bookingpress_cart_total_items > 1){
                    setcookie("bookingpress_cart_id","", time()+(86400), "/");
                }

                if(session_id() == '' OR session_status() === PHP_SESSION_NONE) {
                    session_start();
                }                
                $_SESSION['bookingpress_cart_'.$bookingpress_uniq_id.'_data'] = json_encode($bookingpress_cart_items);
                

                $bookingpress_referer_url = (wp_get_referer()) ? wp_get_referer() : BOOKINGPRESS_HOME_URL;
				setcookie("bookingpress_last_request_id", $bookingpress_uniq_id, time()+(86400), "/");
				setcookie("bookingpress_referer_url", $bookingpress_referer_url, time()+(86400), "/");
                if($bookingpress_cart_total_items > 1){
                    setcookie("bookingpress_cart_id", base64_encode($bookingpress_cart_order_id), time()+(86400), "/");
                    setcookie('bookingpress_single_booking_id',"", time()-(86400), "/");
                }else{
                    $bookingpress_uniq_id = $posted_data['bookingpress_uniq_id'];
                    $bookingpress_cookie_name = $bookingpress_uniq_id."_appointment_data";
                    setcookie($bookingpress_cookie_name, base64_encode($entry_id), time()+(86400), "/");                    
                    setcookie('bookingpress_single_booking_id', base64_encode($entry_id), time()+(86400), "/");
                    setcookie("bookingpress_cart_id", "", time()-(86400), "/");
                }

                $bookingpress_after_approved_payment_page_id = $BookingPress->bookingpress_get_customize_settings( 'after_booking_redirection', 'booking_form' );
				$bookingpress_after_approved_payment_url     = get_permalink( $bookingpress_after_approved_payment_page_id );

				$bookingpress_after_canceled_payment_page_id = $BookingPress->bookingpress_get_customize_settings( 'after_failed_payment_redirection', 'booking_form' );
				$bookingpress_after_canceled_payment_url     = get_permalink( $bookingpress_after_canceled_payment_page_id );

                if($bookingpress_cart_total_items > 1){
                    $bookingpress_entry_hash = md5($bookingpress_cart_order_id);
                }else{
                    $bookingpress_cart_order_id = $entry_id;
                    $bookingpress_entry_hash = md5($entry_id);
                }    
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

                if($bookingpress_cart_total_items > 1){
                    $return_data['approved_appointment_url'] = add_query_arg('is_cart', 1, $return_data['approved_appointment_url']);
                    $return_data['pending_appointment_url'] = add_query_arg('is_cart', 1, $return_data['pending_appointment_url']);
                    $return_data['canceled_appointment_url'] = add_query_arg('is_cart', 1, $return_data['canceled_appointment_url']);
                }

                $bookingpress_notify_url   = BOOKINGPRESS_HOME_URL . '/?bookingpress-listener=bpa_pro_' . $payment_gateway . '_url';
				$return_data['notify_url'] = $bookingpress_notify_url;

                $return_data = apply_filters( 'bookingpress_add_modify_validate_submit_form_data', $return_data, $payment_gateway, $posted_data );

                //Enter data in appointment meta table
				//------------------------------
                if($bookingpress_cart_total_items > 1){

                    $bookingpress_appointment_meta_details = $bookingpress_appointment_data;
                    $bookingpress_db_fields = array(
                        'bookingpress_order_id' => $bookingpress_cart_order_id,
                        'bookingpress_appointment_id' => 0,
                        'bookingpress_appointment_meta_key' => 'appointment_details',
                        'bookingpress_appointment_meta_value' => wp_json_encode($bookingpress_appointment_meta_details),
                    );
                    $wpdb->insert($tbl_bookingpress_appointment_meta, $bookingpress_db_fields);

                }else{

                    $bookingpress_appointment_form_fields_data = array(
                        'form_fields' => !empty($posted_data['form_fields']) ? $posted_data['form_fields'] : array(),
                        'bookingpress_front_field_data' => !empty($posted_data['bookingpress_front_field_data']) ? $posted_data['bookingpress_front_field_data'] : array(),
                    );    
                    $bookingpress_db_fields = array(
                        'bookingpress_entry_id' => $entry_id,
                        'bookingpress_appointment_id' => 0,
                        'bookingpress_appointment_meta_key' => 'appointment_form_fields_data',
                        'bookingpress_appointment_meta_value' => wp_json_encode($bookingpress_appointment_form_fields_data),
                    );
                    $wpdb->insert($tbl_bookingpress_appointment_meta, $bookingpress_db_fields);                    

                }
                do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Cart appointment meta data', 'bookingpress_cart_submit_booking_request', $bookingpress_db_fields, $bookingpress_other_debug_log_id );
            }

            return $return_data;
        }

        function bookingpress_modify_calculated_appointment_details_func($bookingpress_appointment_details){
            global $wpdb, $tbl_bookingpress_coupons, $BookingPress, $bookingpress_coupons, $tbl_bookingpress_services, $bookingpress_deposit_payment, $bookingpress_services, $tbl_bookingpress_staffmembers_services, $bookingpress_pro_staff_members, $tbl_bookingpress_extra_services;

            if(!empty($bookingpress_appointment_details) && !empty($bookingpress_appointment_details['cart_items']) ){
                $total_payable_amount = $final_payable_amount = !empty($bookingpress_appointment_details['bookingpress_cart_total']) ? $bookingpress_appointment_details['bookingpress_cart_total'] : 0;
                if($bookingpress_deposit_payment->bookingpress_check_deposit_payment_module_activation() && (!empty($bookingpress_appointment_details['selected_payment_method']) && $bookingpress_appointment_details['selected_payment_method'] != 'on-site' && ($bookingpress_appointment_details['selected_payment_method'] == "deposit_or_full_price" )) ){
                    $total_payable_amount = $final_payable_amount = $bookingpress_appointment_details['bookingpress_deposit_total'];
                }
				$coupon_code          = ! empty( $bookingpress_appointment_details['coupon_code'] ) ? sanitize_text_field( $bookingpress_appointment_details['coupon_code'] ) : '';
                
				$bookingpress_appointment_details = apply_filters( 'bookingpress_modify_recalculate_appointment_details', $bookingpress_appointment_details, $final_payable_amount );
				$final_payable_amount = apply_filters( 'bookingpress_modify_recalculate_amount', $final_payable_amount, $bookingpress_appointment_details );

                if ( $bookingpress_coupons->bookingpress_check_coupon_module_activation() && !empty( $coupon_code ) ) {
                    $bookingpress_cart_items = $bookingpress_appointment_details['cart_items'];

                    $payment_gateway = !empty($bookingpress_appointment_details['selected_payment_method']) ? $bookingpress_appointment_details['selected_payment_method'] : '';

                    $bookingpress_is_coupon_applied = 0;
                    $bookingpress_coupon_applied_msg = "";
                    $bookingpress_applied_coupon_data = array();

                    foreach($bookingpress_cart_items as $k => $v){
                        $bookingpress_service_id = !empty($v['bookingpress_service_id']) ? intval($v['bookingpress_service_id']) : 0;
                        $bookingpress_applied_coupon_response = $bookingpress_coupons->bookingpress_apply_coupon_code( $coupon_code, $bookingpress_service_id );
                        $bookingpress_coupon_applied_msg = $bookingpress_applied_coupon_response['msg'];
                        if ( is_array( $bookingpress_applied_coupon_response ) && ! empty( $bookingpress_applied_coupon_response ) && !empty($bookingpress_applied_coupon_response['coupon_status']) && ($bookingpress_applied_coupon_response['coupon_status'] == "success") ) {
                            $bookingpress_is_coupon_applied = 1;
                            $bookingpress_applied_coupon_data = $bookingpress_applied_coupon_response['coupon_data'];
                        }else{
                            $bookingpress_is_coupon_applied = 0;
                            $bookingpress_applied_coupon_data = array();
                        }
                    }

                    if($bookingpress_is_coupon_applied == 1){
                        $bookingpress_after_discount_amounts = $bookingpress_coupons->bookingpress_calculate_bookingpress_coupon_amount( $coupon_code, $final_payable_amount );

                        $final_payable_amount = ! empty( $bookingpress_after_discount_amounts['final_payable_amount'] ) ? floatval( $bookingpress_after_discount_amounts['final_payable_amount'] ) : 0;
                        
                        if(!empty($payment_gateway) && $payment_gateway != "on-site" && $payment_gateway != " - " && $bookingpress_deposit_payment->bookingpress_check_deposit_payment_module_activation() && !empty($bookingpress_appointment_details['bookingpress_deposit_payment_method']) && ($bookingpress_appointment_details['bookingpress_deposit_payment_method'] == "deposit_or_full_price" ) ){
                            $bookingpress_deposit_amt = $bookingpress_appointment_details['bookingpress_deposit_total'];
                            $bookingpress_deposit_due_amt = $bookingpress_appointment_details['bookingpress_deposit_due_amount_total'];

                            $bookingpress_deposit_due_amt = $final_payable_amount = $final_payable_amount - $bookingpress_deposit_amt;
                            $bookingpress_appointment_details['bookingpress_deposit_due_amount_total'] = $bookingpress_deposit_due_amt;
                            $bookingpress_appointment_details['bookingpress_deposit_due_amount_total_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_deposit_due_amt);
                        }

                        $discounted_amount = ! empty( $bookingpress_after_discount_amounts['discounted_amount'] ) ? floatval( $bookingpress_after_discount_amounts['discounted_amount'] ) : 0;
                        $bookingpress_appointment_details['coupon_discount_amount'] = $discounted_amount;
                        $bookingpress_appointment_details['coupon_discount_amount_with_currecny'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($discounted_amount);
                    }

                    $bookingpress_appointment_details['applied_coupon_res'] = $bookingpress_applied_coupon_data;
				}

                $bookingpress_appointment_details['total_payable_amount_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol( $final_payable_amount );
				$bookingpress_appointment_details['total_payable_amount'] = $final_payable_amount;
            }
            return $bookingpress_appointment_details;
        }

        function bookingpress_modify_tax_calculated_appointment_details_func($bookingpress_appointment_details, $final_payable_amount, $bookingpress_tax_percentage){
            global $BookingPress, $bookingpress_deposit_payment;
            if(!empty($bookingpress_appointment_details)){
                $final_payable_amount_tmp = $bookingpress_appointment_details['bookingpress_cart_total'];
                $bookingpress_tax_amount = $final_payable_amount_tmp * ($bookingpress_tax_percentage / 100);

                $bookingpress_appointment_details['tax_amount'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_tax_amount);
                $bookingpress_appointment_details['total_payable_amount'] = $final_payable_amount;
                $bookingpress_appointment_details['total_payable_amount_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol( $final_payable_amount );

                $coupon_code          = ! empty( $bookingpress_appointment_details['coupon_code'] ) ? sanitize_text_field( $bookingpress_appointment_details['coupon_code'] ) : '';
                $payment_gateway = !empty($bookingpress_appointment_details['selected_payment_method']) ? $bookingpress_appointment_details['selected_payment_method'] : '';

                if(!empty($payment_gateway) && $payment_gateway != "on-site" && $payment_gateway != " - " && $bookingpress_deposit_payment->bookingpress_check_deposit_payment_module_activation() && !empty($bookingpress_appointment_details['bookingpress_deposit_payment_method']) && ($bookingpress_appointment_details['bookingpress_deposit_payment_method'] == "deposit_or_full_price") && empty($coupon_code) ){
                    $final_payable_amount = $final_payable_amount - floatval($bookingpress_appointment_details['bookingpress_deposit_total']);

                    $bookingpress_appointment_details['total_payable_amount'] = $bookingpress_appointment_details['bookingpress_deposit_total'];
                    $bookingpress_appointment_details['total_payable_amount_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_appointment_details['bookingpress_deposit_total']);

                    $bookingpress_appointment_details['bookingpress_deposit_due_amt_without_currency'] = $final_payable_amount;
                    $bookingpress_appointment_details['bookingpress_deposit_due_amt'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($final_payable_amount);

                    $bookingpress_appointment_details['bookingpress_deposit_due_amount_total'] = $final_payable_amount;
                    $bookingpress_appointment_details['bookingpress_deposit_due_amount_total_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($final_payable_amount);
                }
            }
            return $bookingpress_appointment_details;
        }

        function bookingpress_modify_tax_calculated_amount_func($final_payable_amount, $bookingpress_appointment_details, $bookingpress_tax_percentage, $bookingpress_tax_amount){
            global $bookingpress_deposit_payment;
            if(!empty($bookingpress_appointment_details['cart_items']) && $bookingpress_tax_percentage > 0 ){
                $payment_gateway = !empty($bookingpress_appointment_details['selected_payment_method']) ? $bookingpress_appointment_details['selected_payment_method'] : '';

                $final_payable_amount = $bookingpress_appointment_details['bookingpress_cart_total'];

                $bookingpress_tax_amount = $final_payable_amount * ($bookingpress_tax_percentage / 100);
                $final_payable_amount = $final_payable_amount + $bookingpress_tax_amount;

            }
            return $final_payable_amount;
        }

        function bookingpress_cart_admin_notices() {

            if( !function_exists('is_plugin_active') ){
                include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            }
            if( !is_plugin_active('bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php') ){
                echo "<div class='notice notice-warning'><p>" . esc_html__('BookingPress - Cart plugin requires BookingPress Premium Plugin installed and active.', 'bookingpress-cart') . "</p></div>";
            }
            if( file_exists( WP_PLUGIN_DIR . '/bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php' ) ){
                $bpa_pro_plugin_info = get_plugin_data( WP_PLUGIN_DIR . '/bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php' );
                $bpa_pro_plugin_version = $bpa_pro_plugin_info['Version'];
                // if( version_compare( $bpa_pro_plugin_version, '1.6', '<' ) ){
                //     echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("it's highly recommended to update the BookingPress Premium Plugin to version 1.6 or higher in order to use the BookingPress Cart plugin", "bookingpress-cart").".</p></div>";
                // }
                // if( version_compare( $bpa_pro_plugin_version, '2.0', '<' ) ){
                //     echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("It's highly recommended to update the BookingPress Premium Plugin to version 2.0 or higher in order to use the BookingPress Cart plugin", "bookingpress-cart").".</p></div>";
                // }
                if( version_compare( $bpa_pro_plugin_version, '2.6', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("It's Required to update the BookingPress Premium Plugin to version 2.6 or higher in order to use the BookingPress Cart plugin", "bookingpress-cart").".</p></div>";
                }
            }
        }

        function bookingpress_add_pro_booking_form_methods_func($bookingpress_vue_methods_data){

            global $bookingpress_version,$BookingPress;

            $bookingpress_site_date = date('Y-m-d H:i:s', current_time( 'timestamp') );
            $bookingpress_site_date = apply_filters( 'bookingpress_modify_current_date', $bookingpress_site_date );

            $custom_service_duration_data = ''; 
            $custom_service_duration_data = apply_filters('bookingpress_add_custom_service_duration_data',$custom_service_duration_data);

            $bookingpress_before_add_more_service_to_cart = '';
            $bookingpress_before_add_more_service_to_cart = apply_filters( 'bookingpress_modify_data_before_add_more_services_to_cart', $bookingpress_before_add_more_service_to_cart );

            $bookingpress_after_empty_cart = '';
            $bookingpress_after_empty_cart = apply_filters( 'bookingpress_modify_data_after_empty_cart', $bookingpress_after_empty_cart );

			$bookingpress_default_select_all_category_data = '';
			$bookingpress_default_select_all_category = $BookingPress->bookingpress_get_customize_settings('default_select_all_category','booking_form');
			if(!empty($bookingpress_default_select_all_category ) && $bookingpress_default_select_all_category == 'true') {                
				$bookingpress_default_select_all_category_data = ' vm.bpa_select_category( "" );  ';
			}            
            

            $bookingpress_before_remove_cart_item = '';
            $bookingpress_before_remove_cart_item = apply_filters( 'bookingpress_before_remove_cart_item', $bookingpress_before_remove_cart_item );
            
            /* Recurring appointment filter */
            $bookingpress_before_add_to_cart_item = '';
            $bookingpress_before_add_to_cart_item = apply_filters( 'bookingpress_before_add_to_cart_item', $bookingpress_before_add_to_cart_item );


            if( version_compare( $bookingpress_version, '1.0.59', '<' ) ){ //reputelog - please update the condition after lite release.
                $bookingpress_vue_methods_data .= '
                bookingpress_cart_item_calculations( is_delete_extra = false, cart_index = -1, service_extra_id = -1 ){

                    const vm5 = this;
                    var bookingpress_cart_total = 0;
                    var bookingpress_cart_deposit_total = 0;
                    var bookingpress_service_original_amount_total = 0;
                    var is_service_added_to_cart = 0;
                    
                    if(vm5.appointment_step_form_data.cart_items.length > 0){
                        
                        if( vm5.appointment_step_form_data.cart_item_edit_index > -1 ){
                            is_service_added_to_cart = 1;
                            let cart_item_index = vm5.appointment_step_form_data.cart_item_edit_index;
                            let currentValue = vm5.appointment_step_form_data.cart_items[ cart_item_index ];
    
                            currentValue.bookingpress_service_id = vm5.appointment_step_form_data.selected_service;
                            currentValue.bookingpress_service_name = vm5.appointment_step_form_data.selected_service_name;
    
                            currentValue.bookingpress_service_duration_val = vm5.appointment_step_form_data.selected_service_duration;
                            currentValue.bookingpress_service_duration_unit = vm5.appointment_step_form_data.selected_service_duration_unit;
    
                            currentValue.bookingpress_category_id = vm5.appointment_step_form_data.selected_category;                        
    
                            let bookingpress_service_price = parseFloat( vm5.appointment_step_form_data.service_price_without_currency );
                            let bookingpress_service_original_price = parseFloat(vm5.appointment_step_form_data.service_price_without_currency);
    
                            vm5.services_data.forEach(function(currentValue1, index1, arr1){
                                if(currentValue.bookingpress_service_id == currentValue1.bookingpress_service_id){
                                    currentValue.img_url = currentValue1.img_url;
                                    currentValue.use_placeholder = currentValue1.use_placeholder;
                                }
                            });
                            
                            if(vm5.is_staffmember_activated == "1" && vm5.appointment_step_form_data.is_staff_exists == "1"){
                                var bookingpress_selected_staffmember = vm5.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id;
                                let bookingpress_staffmember_name = "";
                                if(bookingpress_selected_staffmember != ""){
                                    currentValue.bookingpress_selected_staffmember = bookingpress_selected_staffmember;
                                    var bookingpress_staffmember_price = 0;
                                    vm5.bookingpress_staffmembers_details.forEach(function(currentValue4, index4, arr4){
                                        if(currentValue4.bookingpress_staffmember_id == bookingpress_selected_staffmember && currentValue4.assigned_service_price_details[currentValue.bookingpress_service_id] != "undefined"){
                                            bookingpress_staffmember_price = currentValue4.assigned_service_price_details[currentValue.bookingpress_service_id].assigned_service_price;
                                            bookingpress_staffmember_name = currentValue4.bookingpress_staffmember_firstname + " " + currentValue4.bookingpress_staffmember_lastname;   
                                        }
                                    });
    
                                    bookingpress_service_original_price = bookingpress_service_price = parseFloat(bookingpress_staffmember_price);
                                    currentValue.bookingpress_staffmember_name = bookingpress_staffmember_name;
                                }
                            }
                            
                            '.$custom_service_duration_data.'

                            var bookingpress_bring_anyone_members = parseInt(vm5.appointment_step_form_data.bookingpress_selected_bring_members) - 1;
                            if( "number_of_guests" == vm5.appointment_step_form_data.bookingpress_selected_bring_members ){
                                bookingpress_bring_anyone_members = 0;
                            }
                            if(bookingpress_bring_anyone_members > 0){
                                bookingpress_service_original_price = bookingpress_service_price = bookingpress_service_price + (bookingpress_service_price * bookingpress_bring_anyone_members);
                            }
                            bookingpress_bring_anyone_members++;
    
                            var bookingpress_service_extra_total = 0;
                            var bookingpress_selected_extras_counter = 0;
                            var bookingpress_selected_extras_ids = [];
                            var bookingpress_selected_extras_qty = [];
                            let bookingpress_selected_extras_details = [];
                            if( true == is_delete_extra && vm5.appointment_step_form_data.cart_item_edit_index == cart_index ){
                                bookingpress_selected_extras_details = vm5.appointment_step_form_data.cart_items[cart_index].bookingpress_selected_extras_details;
                                let remaining_service_extras = vm5.appointment_step_form_data.cart_items[cart_index].bookingpress_selected_extras_ids;
                                if( "undefined" != typeof remaining_service_extras && remaining_service_extras.length > 0 ){
                                    vm5.bookingpress_service_extras.forEach(function(currentValue3, index3, arr3){
                                        let service_extra_id = currentValue3.bookingpress_extra_services_id;
                                        if( remaining_service_extras.indexOf( service_extra_id ) > -1 ){
                                            let bookingpress_selected_extra_service_qty = parseFloat( vm5.appointment_step_form_data.cart_items[cart_index].bookingpress_selected_extras_qty[ service_extra_id ] );
                                            let bookingpress_selected_extra_service_price = parseFloat( currentValue3.bookingpress_extra_service_price );
                                            bookingpress_service_extra_total = bookingpress_service_extra_total + (bookingpress_selected_extra_service_price * bookingpress_selected_extra_service_qty);
                                            bookingpress_selected_extras_counter = bookingpress_selected_extras_counter + 1;
                                        }
                                    });
                                }
                            } else {
                                vm5.bookingpress_service_extras.forEach(function(currentValue3, index3, arr3){
                                
                                    if(currentValue3.bookingpress_service_id == currentValue.bookingpress_service_id && (vm5.appointment_step_form_data.bookingpress_selected_extra_details[currentValue3.bookingpress_extra_services_id].bookingpress_is_selected == "true" || vm5.appointment_step_form_data.bookingpress_selected_extra_details[currentValue3.bookingpress_extra_services_id].bookingpress_is_selected == true)){
                                        var bookingpress_selected_extra_service_qty = parseFloat(vm5.appointment_step_form_data.bookingpress_selected_extra_details[currentValue3.bookingpress_extra_services_id].bookingpress_selected_qty);
                                        var bookingpress_selected_extra_service_price = parseFloat(currentValue3.bookingpress_extra_service_price);
                                        bookingpress_service_extra_total = bookingpress_service_extra_total + (bookingpress_selected_extra_service_price * bookingpress_selected_extra_service_qty);
                                        bookingpress_selected_extras_counter = bookingpress_selected_extras_counter + 1;
        
                                        bookingpress_selected_extras_ids.push(currentValue3.bookingpress_extra_services_id);
                                        bookingpress_selected_extras_qty[currentValue3.bookingpress_extra_services_id] = bookingpress_selected_extra_service_qty;
        
                                        let service_extra_duration;
                                        if( "h" == currentValue3.bookingpress_extra_service_duration_unit ){
                                            service_extra_duration = currentValue3.bookingpress_extra_service_duration + " Hrs";
                                        } else {
                                            service_extra_duration = currentValue3.bookingpress_extra_service_duration + " Mins";
                                        }
        
                                        let service_extras_data = {
                                            "extra_service_id": currentValue3.bookingpress_extra_services_id,
                                            "extra_service_name": currentValue3.bookingpress_extra_service_name,
                                            "extra_service_duration": service_extra_duration,
                                            "extra_service_duration_val": parseInt(currentValue3.bookingpress_extra_service_duration),
                                            "extra_service_price_formatted": currentValue3.bookingpress_extra_formatted_price,
                                            "extra_service_price_qty": bookingpress_selected_extra_service_qty
                                        };
        
                                        bookingpress_selected_extras_details.push( service_extras_data );
                                    }
                                });
                            }
                            
    
                            bookingpress_service_original_price = bookingpress_service_price = bookingpress_service_price + bookingpress_service_extra_total;
    
                            var bookingpress_deposit_price = 0;
                            var bookingpress_deposit_due_amount = 0;
                            if( vm5.bookingpress_is_deposit_payment_activate == 1 && ( vm5.appointment_step_form_data.bookingpress_deposit_payment_method == "deposit_or_full_price" || vm5.appointment_step_form_data.bookingpress_deposit_payment_method == "allow_customer_to_pay_full_amount" ) )
                            {
                                var bookingpress_deposit_amount = parseFloat(vm5.appointment_step_form_data.deposit_payment_amount);
                                if(vm5.appointment_step_form_data.deposit_payment_type == "percentage"){
                                    bookingpress_deposit_price = bookingpress_service_price * (bookingpress_deposit_amount / 100);
                                }else{
                                    bookingpress_deposit_price = bookingpress_deposit_amount;
                                }
                                bookingpress_service_price = bookingpress_deposit_due_amount = bookingpress_service_price - bookingpress_deposit_price;
                            }
    
                            currentValue.bookingpress_service_original_price = bookingpress_service_original_price;
                            currentValue.bookingpress_service_final_price = bookingpress_service_price;
                            currentValue.bookingpress_service_final_price_without_currency = bookingpress_service_price;
                            currentValue.bookingpress_selected_extras_counter = bookingpress_selected_extras_counter;
                            currentValue.bookingpress_selected_extras_ids = bookingpress_selected_extras_ids;
                            currentValue.bookingpress_selected_extras_qty = bookingpress_selected_extras_qty;
                            currentValue.bookingpress_selected_extras_details = bookingpress_selected_extras_details;
                            currentValue.bookingpress_bring_anyone_selected_members = parseInt(bookingpress_bring_anyone_members);
                            currentValue.bookingpress_selected_date = vm5.appointment_step_form_data.selected_date;
                            currentValue.bookingpress_selected_start_time = vm5.appointment_step_form_data.selected_start_time;
                            currentValue.bookingpress_selected_end_time = vm5.appointment_step_form_data.selected_end_time;
                            currentValue.bookingpress_is_expand = 0;
                            currentValue.bookingpress_is_extra_expand = 0;
                            currentValue.formatted_start_time = vm5.appointment_step_form_data.selected_formatted_start_time;
                            currentValue.formatted_end_time = vm5.appointment_step_form_data.selected_formatted_end_time;
                
                            currentValue.bookingpress_store_selected_date = vm5.appointment_step_form_data.store_selected_date;
                            currentValue.bookingpress_store_start_time = vm5.appointment_step_form_data.store_start_time;
                            currentValue.bookingpress_store_end_time = vm5.appointment_step_form_data.store_end_time;
                            
                            currentValue.bookingpress_deposit_price = bookingpress_deposit_price;
                            currentValue.bookingpress_deposit_due_amount = bookingpress_deposit_due_amount;
    
                            bookingpress_service_original_amount_total = parseFloat(bookingpress_service_original_amount_total) + bookingpress_service_original_price;
                            bookingpress_cart_deposit_total = parseFloat(bookingpress_cart_deposit_total) + bookingpress_deposit_price;
                            bookingpress_cart_total = parseFloat(bookingpress_cart_total) + bookingpress_service_original_price;
    
                            vm5.appointment_step_form_data.cart_items[ cart_item_index ] = currentValue;
                        } else {
                            vm5.appointment_step_form_data.cart_items.forEach(function(currentValue, index, arr){
                                var bookingpress_service_price = parseFloat(currentValue.service_price_without_currency);
                                var bookingpress_service_original_price = parseFloat(currentValue.service_price_without_currency);
    
                                if(vm5.is_staffmember_activated == "1" && vm5.appointment_step_form_data.is_staff_exists == "1"){
                                    var bookingpress_selected_staffmember = currentValue.bookingpress_selected_staffmember;
                                    if(bookingpress_selected_staffmember != ""){
                                        currentValue.bookingpress_selected_staffmember = bookingpress_selected_staffmember;
                                        var bookingpress_staffmember_price = 0;
                                        vm5.bookingpress_staffmembers_details.forEach(function(currentValue4, index4, arr4){
                                            if(currentValue4.bookingpress_staffmember_id == bookingpress_selected_staffmember && currentValue4.assigned_service_price_details[currentValue.bookingpress_service_id] != "undefined"){
                                                bookingpress_staffmember_price = currentValue4.assigned_service_price_details[currentValue.bookingpress_service_id].assigned_service_price;
                                            }
                                        });
    
                                        bookingpress_service_original_price = bookingpress_service_price = parseFloat(bookingpress_staffmember_price);
                                    }
                                }
    
                                var bookingpress_bring_anyone_members = parseInt(vm5.appointment_step_form_data.bookingpress_selected_bring_members) - 1;
                                if(bookingpress_bring_anyone_members > 0){
                                    bookingpress_service_original_price = bookingpress_service_price = bookingpress_service_price + (bookingpress_service_price * bookingpress_bring_anyone_members);
                                }
                                bookingpress_bring_anyone_members++;
    
                                var bookingpress_service_extra_total = 0;
                                var bookingpress_selected_extras_counter = 0;
                                var bookingpress_selected_extras_ids = [];
                                var bookingpress_selected_extras_qty = [];
                                vm5.bookingpress_service_extras.forEach(function(currentValue3, index3, arr3){
                                    if(currentValue3.bookingpress_service_id == currentValue.bookingpress_service_id && (vm5.appointment_step_form_data.bookingpress_selected_extra_details[currentValue3.bookingpress_extra_services_id].bookingpress_is_selected == "true" || vm5.appointment_step_form_data.bookingpress_selected_extra_details[currentValue3.bookingpress_extra_services_id].bookingpress_is_selected == true)){
                                        var bookingpress_selected_extra_service_qty = parseFloat(vm5.appointment_step_form_data.bookingpress_selected_extra_details[currentValue3.bookingpress_extra_services_id].bookingpress_selected_qty);
                                        var bookingpress_selected_extra_service_price = parseFloat(currentValue3.bookingpress_extra_service_price);
                                        bookingpress_service_extra_total = bookingpress_service_extra_total + (bookingpress_selected_extra_service_price * bookingpress_selected_extra_service_qty);
                                        bookingpress_selected_extras_counter = bookingpress_selected_extras_counter + 1;
    
                                        bookingpress_selected_extras_ids.push(currentValue3.bookingpress_extra_services_id);
                                        bookingpress_selected_extras_qty[currentValue3.bookingpress_extra_services_id] = bookingpress_selected_extra_service_qty;
                                    }
                                });
    
                                bookingpress_service_original_price = bookingpress_service_price = bookingpress_service_price + bookingpress_service_extra_total;
    
                                var bookingpress_deposit_price = 0;
                                var bookingpress_deposit_due_amount = 0;
                                if( vm5.bookingpress_is_deposit_payment_activate == 1 && ( vm5.appointment_step_form_data.bookingpress_deposit_payment_method == "deposit_or_full_price" || vm5.appointment_step_form_data.bookingpress_deposit_payment_method == "allow_customer_to_pay_full_amount" ) ){
    
                                    var bookingpress_deposit_amount = parseFloat(vm5.appointment_step_form_data.deposit_payment_amount);
                                    if(vm5.appointment_step_form_data.deposit_payment_type == "percentage"){
                                        bookingpress_deposit_price = bookingpress_service_price * (bookingpress_deposit_amount / 100);
                                    }else{
                                        bookingpress_deposit_price = bookingpress_deposit_amount;
                                    }
                                    bookingpress_service_price = bookingpress_deposit_due_amount = bookingpress_service_price - bookingpress_deposit_price;
                                }
                                bookingpress_service_original_amount_total = parseFloat(bookingpress_service_original_amount_total) + bookingpress_service_original_price;
                                bookingpress_cart_deposit_total = parseFloat(bookingpress_cart_deposit_total) + bookingpress_deposit_price;
                                bookingpress_cart_total = parseFloat(bookingpress_cart_total) + bookingpress_service_original_price;
    
                            });
                        }
                    }
                    if(is_service_added_to_cart == 0){
                        vm5.services_data.forEach(function(currentValue, index, arr){
                            if(currentValue.bookingpress_service_id == vm5.appointment_step_form_data.selected_service){
                                var bookingpress_service_price = parseFloat(currentValue.service_price_without_currency);
                                var bookingpress_service_original_price = parseFloat(currentValue.service_price_without_currency);
    
                                currentValue.bookingpress_selected_staffmember = "";
                                if(vm5.is_staffmember_activated == "1" && vm5.appointment_step_form_data.is_staff_exists == "1"){
                                    var bookingpress_selected_staffmember = vm5.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id;
                                    let bookingpress_staffmember_name = "";
                                    if(bookingpress_selected_staffmember != ""){
                                        currentValue.bookingpress_selected_staffmember = bookingpress_selected_staffmember;
                                        var bookingpress_staffmember_price = 0;
                                        
                                        vm5.bookingpress_staffmembers_details.forEach(function(currentValue4, index4, arr4){
                                            if(currentValue4.bookingpress_staffmember_id == bookingpress_selected_staffmember && currentValue4.assigned_service_price_details[currentValue.bookingpress_service_id] != "undefined"){
                                                bookingpress_staffmember_price = currentValue4.assigned_service_price_details[currentValue.bookingpress_service_id].assigned_service_price;
                                                bookingpress_staffmember_name = currentValue4.bookingpress_staffmember_firstname + " " + currentValue4.bookingpress_staffmember_lastname;
                                            }
                                        });
    
                                        bookingpress_service_original_price = bookingpress_service_price = parseFloat(bookingpress_staffmember_price);
                                    }
                                    currentValue.bookingpress_staffmember_name = bookingpress_staffmember_name;
    
                                }
                                '.$custom_service_duration_data.'
                                var bookingpress_bring_anyone_members = parseInt(vm5.appointment_step_form_data.bookingpress_selected_bring_members) - 1;
                                if( "number_of_guests" == vm5.appointment_step_form_data.bookingpress_selected_bring_members ){
                                    bookingpress_bring_anyone_members = 0;
                                }
                                if(bookingpress_bring_anyone_members > 0){
                                    bookingpress_service_original_price = bookingpress_service_price = bookingpress_service_price + (bookingpress_service_price * bookingpress_bring_anyone_members);
                                }
                                bookingpress_bring_anyone_members++;
    
                                var bookingpress_service_extra_total = 0;
                                var bookingpress_selected_extras_counter = 0;
                                var bookingpress_selected_extras_ids = [];
                                var bookingpress_selected_extras_qty = [];
                                let bookingpress_selected_extras_details = [];
                                vm5.bookingpress_service_extras.forEach(function(currentValue3, index3, arr3){
                                    if(currentValue3.bookingpress_service_id == currentValue.bookingpress_service_id && vm5.appointment_step_form_data.bookingpress_selected_extra_details[currentValue3.bookingpress_extra_services_id].bookingpress_is_selected == true){
                                        var bookingpress_selected_extra_service_qty = parseFloat(vm5.appointment_step_form_data.bookingpress_selected_extra_details[currentValue3.bookingpress_extra_services_id].bookingpress_selected_qty);
                                        var bookingpress_selected_extra_service_price = parseFloat(currentValue3.bookingpress_extra_service_price);
                                        bookingpress_service_extra_total = bookingpress_service_extra_total + (bookingpress_selected_extra_service_price * bookingpress_selected_extra_service_qty);
                                        bookingpress_selected_extras_counter = bookingpress_selected_extras_counter + 1;
    
                                        bookingpress_selected_extras_ids.push(currentValue3.bookingpress_extra_services_id);
                                        bookingpress_selected_extras_qty[currentValue3.bookingpress_extra_services_id] = bookingpress_selected_extra_service_qty;
                                        
                                        let service_extra_duration;
                                        if( "h" == currentValue3.bookingpress_extra_service_duration_unit ){
                                            service_extra_duration = currentValue3.bookingpress_extra_service_duration + " Hrs";
                                        } else {
                                            service_extra_duration = currentValue3.bookingpress_extra_service_duration + " Mins";
                                        }
    
                                        let service_extras_data = {
                                            "extra_service_id": currentValue3.bookingpress_extra_services_id,
                                            "extra_service_name": currentValue3.bookingpress_extra_service_name,
                                            "extra_service_duration": service_extra_duration,
                                            "extra_service_duration_val": parseInt(currentValue3.bookingpress_extra_service_duration),
                                            "extra_service_price_formatted": currentValue3.bookingpress_extra_formatted_price,
                                            "extra_service_price_qty": bookingpress_selected_extra_service_qty
                                        };
    
                                        bookingpress_selected_extras_details.push( service_extras_data );
                                    }
                                });
                                bookingpress_service_price = parseFloat(bookingpress_service_price) + bookingpress_service_extra_total;
                                bookingpress_service_original_price = parseFloat(bookingpress_service_price);
                                var bookingpress_deposit_price = 0;
                                var bookingpress_deposit_due_amount = 0;
                                if( vm5.bookingpress_is_deposit_payment_activate == 1 && ( vm5.appointment_step_form_data.bookingpress_deposit_payment_method == "deposit_or_full_price" || vm5.appointment_step_form_data.bookingpress_deposit_payment_method == "allow_customer_to_pay_full_amount" ) ){
                                    var bookingpress_deposit_amount = parseFloat(vm5.appointment_step_form_data.deposit_payment_amount);
                                    if(vm5.appointment_step_form_data.deposit_payment_type == "percentage"){
                                        bookingpress_deposit_price = bookingpress_service_price * (bookingpress_deposit_amount / 100);
                                    }else{
                                        bookingpress_deposit_price = bookingpress_deposit_amount;
                                    }
                                    bookingpress_service_price = bookingpress_deposit_due_amount = bookingpress_service_price - bookingpress_deposit_price;
                                }
    
                                currentValue.bookingpress_service_original_price = bookingpress_service_original_price;
                                currentValue.bookingpress_service_final_price = bookingpress_service_price;
                                currentValue.bookingpress_service_final_price_without_currency = bookingpress_service_price;
                                currentValue.bookingpress_selected_extras_counter = bookingpress_selected_extras_counter;
                                currentValue.bookingpress_selected_extras_ids = bookingpress_selected_extras_ids;
                                currentValue.bookingpress_selected_extras_qty = bookingpress_selected_extras_qty;
                                currentValue.bookingpress_selected_extras_details = bookingpress_selected_extras_details;
                                currentValue.bookingpress_bring_anyone_selected_members = parseInt(bookingpress_bring_anyone_members);
                                currentValue.bookingpress_selected_date = vm5.appointment_step_form_data.selected_date;
                                currentValue.bookingpress_selected_start_time = vm5.appointment_step_form_data.selected_start_time;
                                currentValue.bookingpress_selected_end_time = vm5.appointment_step_form_data.selected_end_time;
    
                                currentValue.formatted_start_time = vm5.appointment_step_form_data.selected_formatted_start_time;
                                currentValue.formatted_end_time = vm5.appointment_step_form_data.selected_formatted_end_time;
    
                                currentValue.bookingpress_store_selected_date = vm5.appointment_step_form_data.store_selected_date;
                                currentValue.bookingpress_store_start_time = vm5.appointment_step_form_data.store_start_time;
                                currentValue.bookingpress_store_end_time = vm5.appointment_step_form_data.store_end_time;
                                currentValue.bookingpress_deposit_price = bookingpress_deposit_price;
                                currentValue.bookingpress_deposit_due_amount = bookingpress_deposit_due_amount;
                                currentValue.bookingpress_is_edit = 0;                            
                                currentValue.bookingpress_is_expand = 0;
                                currentValue.bookingpress_is_extra_expand = 0;
                                vm5.appointment_step_form_data.cart_items.push(currentValue);
                                
                                let cart_item_index = vm5.appointment_step_form_data.cart_items.length - 1;
                                vm5.appointment_step_form_data.cart_item_edit_index = cart_item_index;
                                
                                bookingpress_service_original_amount_total = parseFloat(bookingpress_service_original_amount_total) + bookingpress_service_original_price;
                                bookingpress_cart_deposit_total = parseFloat(bookingpress_cart_deposit_total) + bookingpress_deposit_price;
                                bookingpress_cart_total = parseFloat(bookingpress_cart_total) + bookingpress_service_original_price;
                            }
                        });
                    }
    
                    vm5.appointment_step_form_data.bookingpress_service_original_amount_total = bookingpress_service_original_amount_total;
                    vm5.appointment_step_form_data.bookingpress_cart_deposit_total = bookingpress_cart_deposit_total;
                    vm5.appointment_step_form_data.bookingpress_cart_total = bookingpress_cart_total;
                },
                bookingpress_add_more_service_to_cart(){
                    const vm = this;
                    
                    if( "undefined" != typeof vm.service_categories && "undefined" != typeof vm.service_categories[0] ){
                        vm.appointment_step_form_data.selected_category = vm.service_categories[0].bookingpress_category_id;
                        vm.selectStepCategory( vm.service_categories[0].bookingpress_category_id, vm.service_categories[0].bookingpress_category_name, vm.appointment_step_form_data.total_services );
                    }
                    
                    vm.appointment_step_form_data.selected_cat_name = "";
                    
                    vm.appointment_step_form_data.selected_service = "";
                    vm.appointment_step_form_data.selected_service_name = "";
                    
                    vm.appointment_step_form_data.selected_service_price = "";
                    vm.appointment_step_form_data.service_price_without_currency = 0;                
                    
                    vm.appointment_step_form_data.selected_start_time = "";
                    
                    vm.appointment_step_form_data.selected_end_time = "";
                    
                    vm.appointment_step_form_data.total_payable_amount = "";
                    
                    vm.appointment_step_form_data.bookingpress_selected_bring_members = 1;
                    
                    vm.appointment_step_form_data.service_max_capacity = 1;
    
                    if( true == vm.bookingpress_cart_reset_staff ){
                        vm.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id = 0;
                        vm.appointment_step_form_data.bookingpress_selected_staff_member_details.staff_member_id = "";
                        vm.appointment_step_form_data.selected_staff_member_id = 0;
                    }
                    
                    vm.appointment_step_form_data.is_extra_service_exists = 0;
                    
                    vm.appointment_step_form_data.is_staff_exists = 0;
    
                    vm.appointment_step_form_data.cart_item_edit_index = -1;
    
                    let newDate = new Date('. ( !empty( $bookingpress_site_date ) ? '"'.$bookingpress_site_date.'"' : '' ) .');
    
                    let pattern = /(\d{4}\-\d{2}\-\d{2})/;
                    if( !pattern.test( newDate ) ){
    
                        let sel_month = newDate.getMonth() + 1;
                        let sel_year = newDate.getFullYear();
                        let sel_date = newDate.getDate();
    
                        if( sel_month < 10 ){
                            sel_month = "0" + sel_month;
                        }
    
                        if( sel_date < 10 ){
                            sel_date = "0" + sel_date;
                        }
                        
                        newDate = sel_year + "-" + sel_month + "-" + sel_date;
                    }
    
                    vm.appointment_step_form_data.selected_date = newDate;
    
                    vm.bookingpress_service_extras.forEach(function(currentValue, index, arr){
                        var bookingpress_extra_service_id = parseInt(currentValue.bookingpress_extra_services_id);
                        if(vm.appointment_step_form_data.bookingpress_selected_extra_details[bookingpress_extra_service_id].bookingpress_is_selected == "true"){
                            vm.appointment_step_form_data.bookingpress_selected_extra_details[bookingpress_extra_service_id].bookingpress_is_selected = true;
                        }else{
                            vm.appointment_step_form_data.bookingpress_selected_extra_details[bookingpress_extra_service_id].bookingpress_is_selected = false;
                        }
                        vm.appointment_step_form_data.bookingpress_selected_extra_details[bookingpress_extra_service_id].bookingpress_selected_qty = 1;
                    });
    
                    let sidebar_step_data = [];
                    for( let x in vm.bookingpress_sidebar_step_data ){
                        let step_data = vm.bookingpress_sidebar_step_data[x];
                        if( step_data.is_first_step == 1 ){
                            sidebar_step_data = vm.bookingpress_sidebar_step_data[x];
                            break;
                        }
                    }
    
                    if( sidebar_step_data.length == 0 ){
                        if(typeof vm.bookingpress_sidebar_step_data["staffmembers"] != "undefined"){
                            vm.bookingpress_step_navigation("staffmembers", vm.bookingpress_sidebar_step_data["staffmembers"].next_tab_name, vm.bookingpress_sidebar_step_data["staffmembers"].previous_tab_name)
                        }else{
                            vm.bookingpress_step_navigation("service", vm.bookingpress_sidebar_step_data["service"].next_tab_name, vm.bookingpress_sidebar_step_data["service"].previous_tab_name)
                        }
                    } else {
                        vm.bookingpress_step_navigation( sidebar_step_data.tab_value, sidebar_step_data.next_tab_name, sidebar_step_data.previous_tab_name );
                    }
                    
                    vm.bookingpress_sidebar_step_data.cart.is_allow_navigate = 0;
                },
                bookingpress_edit_cart_item(edit_id, move_to_service = true){
                    const vm = this;
    
                    vm.appointment_step_form_data.cart_item_edit_index  = edit_id;
    
                    var bookingpress_edit_data = vm.appointment_step_form_data.cart_items[edit_id];
                    
                    var bookingpress_selected_staffmember = parseInt(bookingpress_edit_data.bookingpress_selected_staffmember);
                    var bookingpress_selected_bring_members = parseInt(bookingpress_edit_data.bookingpress_bring_anyone_selected_members);
                    
                    if( "" != bookingpress_edit_data.bookingpress_category_id && 0 != bookingpress_edit_data.bookingpress_category_id ){
                        let sm_categories = vm.service_categories;
                        
                        for( let c in sm_categories ){
                            let selected_category = bookingpress_edit_data.bookingpress_category_id;
                            let cat_data = sm_categories[c];
                            if( cat_data.bookingpress_category_id == selected_category ){
                                vm.selectStepCategory(cat_data.bookingpress_category_id, cat_data.bookingpress_category_name,vm.appointment_step_form_data.total_services);
                                break;
                            }
                        }
                    } else if( 0 == bookingpress_edit_data.bookingpress_category_id ){
                        vm.selectStepCategory(0, "all",vm.appointment_step_form_data.total_services);
                    }
                    
                    vm.selectDate(bookingpress_edit_data.bookingpress_service_id, bookingpress_edit_data.bookingpress_service_name, bookingpress_edit_data.bookingpress_service_price, bookingpress_edit_data.service_price_without_currency);
    
                    if( isNaN( bookingpress_selected_staffmember ) ){
                        bookingpress_selected_staffmember = "";
                    }
                    
                    vm.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id = bookingpress_selected_staffmember.toString();
                    
                    vm.appointment_step_form_data.selected_date = bookingpress_edit_data.bookingpress_selected_date;
                    vm.appointment_step_form_data.selected_start_time = bookingpress_edit_data.bookingpress_selected_start_time;
                    vm.appointment_step_form_data.selected_end_time = bookingpress_edit_data.bookingpress_selected_end_time;
                    vm.appointment_step_form_data.selected_service_duration = bookingpress_edit_data.bookingpress_service_duration_val;
                    vm.appointment_step_form_data.selected_service_duration_unit = bookingpress_edit_data.bookingpress_service_duration_unit;
                    
    
                    if( 0 == bookingpress_selected_bring_members ){
                        bookingpress_selected_bring_members = "number_of_guests";
                    }
                    vm.appointment_step_form_data.bookingpress_selected_bring_members = bookingpress_selected_bring_members;
    
                    vm.bookingpress_service_extras.forEach(function(currentValue, index, arr){
                        var bookingpress_extra_service_id = currentValue.bookingpress_extra_services_id;
                        
                        if( "undefined" != typeof bookingpress_edit_data.bookingpress_selected_extras_ids && bookingpress_edit_data.bookingpress_selected_extras_ids.includes( bookingpress_extra_service_id ) ){
                            vm.appointment_step_form_data.bookingpress_selected_extra_details[bookingpress_extra_service_id].bookingpress_is_selected = true;
                            let bookingpress_selected_extra_qty = bookingpress_edit_data.bookingpress_selected_extras_qty[bookingpress_extra_service_id];
                            vm.appointment_step_form_data.bookingpress_selected_extra_details[bookingpress_extra_service_id].bookingpress_selected_qty = bookingpress_selected_extra_qty;
                        } else {
                            vm.appointment_step_form_data.bookingpress_selected_extra_details[bookingpress_extra_service_id].bookingpress_is_selected = false;
                            vm.appointment_step_form_data.bookingpress_selected_extra_details[bookingpress_extra_service_id].bookingpress_selected_qty = 1;
                        }
                    });
                    vm.appointment_step_form_data.cart_items[edit_id].bookingpress_is_edit = 1;
                    if( move_to_service ){
                        let sidebar_step_data = [];
                        for( let x in vm.bookingpress_sidebar_step_data ){
                            let step_data = vm.bookingpress_sidebar_step_data[x];
                            if( step_data.is_first_step == 1 ){
                                sidebar_step_data = vm.bookingpress_sidebar_step_data[x];
                                break;
                            }
                        }
    
                        if( sidebar_step_data.length == 0 ){
                            if(typeof vm.bookingpress_sidebar_step_data["staffmembers"] != "undefined"){
                                vm.bookingpress_step_navigation("staffmembers", vm.bookingpress_sidebar_step_data["staffmembers"].next_tab_name, vm.bookingpress_sidebar_step_data["staffmembers"].previous_tab_name)
                            }else{
                                vm.bookingpress_step_navigation("service", vm.bookingpress_sidebar_step_data["service"].next_tab_name, vm.bookingpress_sidebar_step_data["service"].previous_tab_name)
                            }
                        } else {
                            vm.bookingpress_step_navigation( sidebar_step_data.tab_value, sidebar_step_data.next_tab_name, sidebar_step_data.previous_tab_name );
                        }
                    }
                },
                bookingpress_delete_cart_item(delete_id){
                    const vm = this;
                    
                    vm.appointment_step_form_data.cart_items.splice(delete_id, 1);
                    if(vm.appointment_step_form_data.cart_items.length == 0){

                        '.$bookingpress_default_select_all_category_data.'

                        if( "undefined" != typeof vm.service_categories && "undefined" != typeof vm.service_categories[0] ){
                            vm.appointment_step_form_data.selected_category = vm.service_categories[0].bookingpress_category_id;
                            vm.selectStepCategory( vm.service_categories[0].bookingpress_category_id, vm.service_categories[0].bookingpress_category_name, vm.appointment_step_form_data.total_services );
                        }
                        
                        vm.appointment_step_form_data.selected_cat_name = "";
                        
                        vm.appointment_step_form_data.selected_service_name = "";
                        
                        vm.appointment_step_form_data.selected_service_price = "";
    
                        vm.appointment_step_form_data.service_price_without_currency = 0;
                        
                        vm.appointment_step_form_data.selected_start_time = "";
                        
                        vm.appointment_step_form_data.selected_end_time = "";
                        
                        vm.appointment_step_form_data.total_payable_amount = "";
                        
                        vm.appointment_step_form_data.bookingpress_selected_bring_members = 1;
                        
                        vm.appointment_step_form_data.service_max_capacity = 1;
                        
                        if( true == vm.bookingpress_cart_reset_staff ){
                            vm.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id = 0;
                            vm.appointment_step_form_data.bookingpress_selected_staff_member_details.staff_member_id = "";
                            vm.appointment_step_form_data.selected_staff_member_id = "";
                        }
                        
                        vm.appointment_step_form_data.is_extra_service_exists = 0;
                            
                        vm.appointment_step_form_data.cart_item_edit_index = -1;
    
                        vm.appointment_step_form_data.bookingpress_cart_total = 0;
    
                        let newDate = new Date('. ( !empty( $bookingpress_site_date ) ? '"'.$bookingpress_site_date.'"' : '' ) .');
    
                        let pattern = /(\d{4}\-\d{2}\-\d{2})/;
                        if( !pattern.test( newDate ) ){
    
                            let sel_month = newDate.getMonth() + 1;
                            let sel_year = newDate.getFullYear();
                            let sel_date = newDate.getDate();
    
                            if( sel_month < 10 ){
                                sel_month = "0" + sel_month;
                            }
    
                            if( sel_date < 10 ){
                                sel_date = "0" + sel_date;
                            }
                            
                            newDate = sel_year + "-" + sel_month + "-" + sel_date;
                        }
    
                        vm.appointment_step_form_data.selected_date = newDate;
    
                        vm.appointment_step_form_data.selected_service = "";
    
                        vm.bookingpress_refresh_cart_details( true, true, delete_id );
    
                        let sidebar_step_data = [];
                        for( let x in vm.bookingpress_sidebar_step_data ){
                            let step_data = vm.bookingpress_sidebar_step_data[x];
                            if( step_data.is_first_step == 1 ){
                                sidebar_step_data = vm.bookingpress_sidebar_step_data[x];
                                break;
                            }
                        }
                        
                        

                        if( sidebar_step_data.length == 0 ){
                            if(typeof vm.bookingpress_sidebar_step_data["staffmembers"] != "undefined"){                                
                                vm.bookingpress_step_navigation("staffmembers", vm.bookingpress_sidebar_step_data["staffmembers"].next_tab_name, vm.bookingpress_sidebar_step_data["staffmembers"].previous_tab_name)
                            }else{
                                vm.bookingpress_step_navigation("service", vm.bookingpress_sidebar_step_data["service"].next_tab_name, vm.bookingpress_sidebar_step_data["service"].previous_tab_name);                                                                
                            }
                        } else {                            
                            vm.bookingpress_step_navigation( sidebar_step_data.tab_value, sidebar_step_data.next_tab_name, sidebar_step_data.previous_tab_name );
                        }
    
                    } else {
                        let cart_item_index = vm.appointment_step_form_data.cart_items.length - 1;
                        vm.appointment_step_form_data.cart_item_edit_index = cart_item_index;
                        vm.bookingpress_refresh_cart_details( false, true, delete_id )
                    }
                    
                },';
            } else {
                $bookingpress_vue_methods_data .= '
                bookingpress_cart_item_calculations( is_delete_extra = false, cart_index = -1, service_extra_id = -1 ){

                    const vm5 = this;
                    var bookingpress_cart_total = 0;
                    var bookingpress_cart_deposit_total = 0;
                    var bookingpress_service_original_amount_total = 0;
                    var is_service_added_to_cart = 0;
                    
                    if(vm5.appointment_step_form_data.cart_items.length > 0){
                        
                        if( vm5.appointment_step_form_data.cart_item_edit_index > -1 ){
                            is_service_added_to_cart = 1;
                            let cart_item_index = vm5.appointment_step_form_data.cart_item_edit_index;
                            let currentValue = vm5.appointment_step_form_data.cart_items[ cart_item_index ];
    
                            currentValue.bookingpress_service_id = vm5.appointment_step_form_data.selected_service;
                            currentValue.bookingpress_service_name = vm5.appointment_step_form_data.selected_service_name;
    
                            currentValue.bookingpress_service_duration_val = vm5.appointment_step_form_data.selected_service_duration;
                            currentValue.bookingpress_service_duration_unit = vm5.appointment_step_form_data.selected_service_duration_unit;
    
                            currentValue.bookingpress_category_id = vm5.appointment_step_form_data.selected_category;                    
                            
                            /*
                            let bookingpress_service_price = parseFloat( vm5.appointment_step_form_data.service_price_without_currency );                            
                            let bookingpress_service_original_price = parseFloat(vm5.appointment_step_form_data.service_price_without_currency);
                            */                            
                            let bookingpress_service_price = parseFloat( vm5.appointment_step_form_data.base_price_without_currency );                            
                            let bookingpress_service_original_price = parseFloat(vm5.appointment_step_form_data.base_price_without_currency);
                            

                            vm5.services_data.forEach(function(currentValue1, index1, arr1){
                                if(currentValue.bookingpress_service_id == currentValue1.bookingpress_service_id){
                                    currentValue.img_url = currentValue1.img_url;
                                    currentValue.use_placeholder = currentValue1.use_placeholder;
                                }
                            });
                            
                            if(vm5.is_staffmember_activated == "1" && vm5.appointment_step_form_data.is_staff_exists == "1"){
                                var bookingpress_selected_staffmember = vm5.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id;
                                let bookingpress_staffmember_name = "";
                                if(bookingpress_selected_staffmember != ""){
                                    currentValue.bookingpress_selected_staffmember = bookingpress_selected_staffmember;
                                    var bookingpress_staffmember_price = 0;
                                    vm5.bookingpress_staffmembers_details.forEach(function(currentValue4, index4, arr4){
                                        if(currentValue4.bookingpress_staffmember_id == bookingpress_selected_staffmember && currentValue4.assigned_service_price_details[currentValue.bookingpress_service_id] != "undefined"){
                                            bookingpress_staffmember_price = currentValue4.assigned_service_price_details[currentValue.bookingpress_service_id].assigned_service_price;
                                            bookingpress_staffmember_name = currentValue4.bookingpress_staffmember_firstname + " " + currentValue4.bookingpress_staffmember_lastname;   
                                        }
                                    });
    
                                    bookingpress_service_original_price = bookingpress_service_price = parseFloat(bookingpress_staffmember_price);
                                    currentValue.bookingpress_staffmember_name = bookingpress_staffmember_name;
                                }
                            }
                            
                            '.$custom_service_duration_data.'
    
                            var bookingpress_bring_anyone_members = parseInt(vm5.appointment_step_form_data.bookingpress_selected_bring_members) - 1;
                            if( "number_of_guests" == vm5.appointment_step_form_data.bookingpress_selected_bring_members ){
                                bookingpress_bring_anyone_members = 0;
                            }
                            if(bookingpress_bring_anyone_members > 0){
                                bookingpress_service_original_price = bookingpress_service_price = bookingpress_service_price + (bookingpress_service_price * bookingpress_bring_anyone_members);
                            }
                            bookingpress_bring_anyone_members++;
    
                            var bookingpress_service_extra_total = 0;
                            var bookingpress_selected_extras_counter = 0;
                            var bookingpress_selected_extras_ids = [];
                            var bookingpress_selected_extras_qty = [];
                            let bookingpress_selected_extras_details = [];
                            if( true == is_delete_extra && vm5.appointment_step_form_data.cart_item_edit_index == cart_index ){
                                bookingpress_selected_extras_details = vm5.appointment_step_form_data.cart_items[cart_index].bookingpress_selected_extras_details;
                                let remaining_service_extras = vm5.appointment_step_form_data.cart_items[cart_index].bookingpress_selected_extras_ids;
                                if( "undefined" != typeof remaining_service_extras && remaining_service_extras.length > 0 ){
                                    vm5.bookingpress_service_extras.forEach(function(currentValue3, index3, arr3){
                                        let service_extra_id = currentValue3.bookingpress_extra_services_id;
                                        if( remaining_service_extras.indexOf( service_extra_id ) > -1 ){
                                            let bookingpress_selected_extra_service_qty = parseFloat( vm5.appointment_step_form_data.cart_items[cart_index].bookingpress_selected_extras_qty[ service_extra_id ] );
                                            let bookingpress_selected_extra_service_price = parseFloat( currentValue3.bookingpress_extra_service_price );
                                            bookingpress_service_extra_total = bookingpress_service_extra_total + (bookingpress_selected_extra_service_price * bookingpress_selected_extra_service_qty);
                                            bookingpress_selected_extras_counter = bookingpress_selected_extras_counter + 1;
                                        }
                                    });
                                }
                            } else {
                                vm5.bookingpress_service_extras.forEach(function(currentValue3, index3, arr3){
                                
                                    if(currentValue3.bookingpress_service_id == currentValue.bookingpress_service_id && (vm5.appointment_step_form_data.bookingpress_selected_extra_details[currentValue3.bookingpress_extra_services_id].bookingpress_is_selected == "true" || vm5.appointment_step_form_data.bookingpress_selected_extra_details[currentValue3.bookingpress_extra_services_id].bookingpress_is_selected == true)){
                                        var bookingpress_selected_extra_service_qty = parseFloat(vm5.appointment_step_form_data.bookingpress_selected_extra_details[currentValue3.bookingpress_extra_services_id].bookingpress_selected_qty);
                                        var bookingpress_selected_extra_service_price = parseFloat(currentValue3.bookingpress_extra_service_price);
                                        bookingpress_service_extra_total = bookingpress_service_extra_total + (bookingpress_selected_extra_service_price * bookingpress_selected_extra_service_qty);
                                        bookingpress_selected_extras_counter = bookingpress_selected_extras_counter + 1;
        
                                        bookingpress_selected_extras_ids.push(currentValue3.bookingpress_extra_services_id);
                                        bookingpress_selected_extras_qty[currentValue3.bookingpress_extra_services_id] = bookingpress_selected_extra_service_qty;
        
                                        let service_extra_duration;
                                        if( "h" == currentValue3.bookingpress_extra_service_duration_unit ){
                                            service_extra_duration = currentValue3.bookingpress_extra_service_duration + " Hrs";
                                        } else {
                                            service_extra_duration = currentValue3.bookingpress_extra_service_duration + " Mins";
                                        }
        
                                        let service_extras_data = {
                                            "extra_service_id": currentValue3.bookingpress_extra_services_id,
                                            "extra_service_name": currentValue3.bookingpress_extra_service_name,
                                            "extra_service_duration": service_extra_duration,
                                            "extra_service_duration_val": parseInt(currentValue3.bookingpress_extra_service_duration),
                                            "extra_service_price_formatted": currentValue3.bookingpress_extra_formatted_price,
                                            "extra_service_price_qty": bookingpress_selected_extra_service_qty
                                        };
        
                                        bookingpress_selected_extras_details.push( service_extras_data );
                                    }
                                });
                            }
                            
    
                            bookingpress_service_original_price = bookingpress_service_price = bookingpress_service_price + bookingpress_service_extra_total;
    
                            var bookingpress_deposit_price = 0;
                            var bookingpress_deposit_due_amount = 0;
                            if( vm5.bookingpress_is_deposit_payment_activate == 1 && ( vm5.appointment_step_form_data.bookingpress_deposit_payment_method == "deposit_or_full_price" || vm5.appointment_step_form_data.bookingpress_deposit_payment_method == "allow_customer_to_pay_full_amount" ) )
                            {
                                var bookingpress_deposit_amount = parseFloat(vm5.appointment_step_form_data.deposit_payment_amount);
                                if(vm5.appointment_step_form_data.deposit_payment_type == "percentage"){
                                    bookingpress_deposit_price = bookingpress_service_price * (bookingpress_deposit_amount / 100);
                                }else{
                                    bookingpress_deposit_price = bookingpress_deposit_amount;
                                }
                                bookingpress_service_price = bookingpress_deposit_due_amount = bookingpress_service_price - bookingpress_deposit_price;
                            }
    
                            currentValue.bookingpress_service_original_price = bookingpress_service_original_price;
                            currentValue.bookingpress_service_final_price = bookingpress_service_price;
                            currentValue.bookingpress_service_final_price_without_currency = bookingpress_service_price;
                            currentValue.bookingpress_selected_extras_counter = bookingpress_selected_extras_counter;
                            currentValue.bookingpress_selected_extras_ids = bookingpress_selected_extras_ids;
                            currentValue.bookingpress_selected_extras_qty = bookingpress_selected_extras_qty;
                            currentValue.bookingpress_selected_extras_details = bookingpress_selected_extras_details;
                            currentValue.bookingpress_bring_anyone_selected_members = parseInt(bookingpress_bring_anyone_members);
                            currentValue.bookingpress_selected_date = vm5.appointment_step_form_data.selected_date;
                            currentValue.bookingpress_selected_start_time = vm5.appointment_step_form_data.selected_start_time;
                            currentValue.bookingpress_selected_end_time = vm5.appointment_step_form_data.selected_end_time;
                            currentValue.bookingpress_is_expand = 0;
                            currentValue.bookingpress_is_extra_expand = 0;
                            currentValue.formatted_start_time = vm5.appointment_step_form_data.selected_formatted_start_time;
                            currentValue.formatted_end_time = vm5.appointment_step_form_data.selected_formatted_end_time;
                            currentValue.formatted_start_end_time = vm5.appointment_step_form_data.selected_formatted_start_end_time;

                            currentValue.bookingpress_store_selected_date = vm5.appointment_step_form_data.store_selected_date;
                            currentValue.bookingpress_store_start_time = vm5.appointment_step_form_data.store_start_time;
                            currentValue.bookingpress_store_end_time = vm5.appointment_step_form_data.store_end_time;
                            
                            currentValue.bookingpress_deposit_price = bookingpress_deposit_price;
                            currentValue.bookingpress_deposit_due_amount = bookingpress_deposit_due_amount;
    
                            bookingpress_service_original_amount_total = parseFloat(bookingpress_service_original_amount_total) + bookingpress_service_original_price;
                            bookingpress_cart_deposit_total = parseFloat(bookingpress_cart_deposit_total) + bookingpress_deposit_price;
                            bookingpress_cart_total = parseFloat(bookingpress_cart_total) + bookingpress_service_original_price;
    
                            vm5.appointment_step_form_data.cart_items[ cart_item_index ] = currentValue;
                        } else {
                            vm5.appointment_step_form_data.cart_items.forEach(function(currentValue, index, arr){
                                var bookingpress_service_price = parseFloat(currentValue.service_price_without_currency);
                                var bookingpress_service_original_price = parseFloat(currentValue.service_price_without_currency);
    
                                if(vm5.is_staffmember_activated == "1" && vm5.appointment_step_form_data.is_staff_exists == "1"){
                                    var bookingpress_selected_staffmember = currentValue.bookingpress_selected_staffmember;
                                    if(bookingpress_selected_staffmember != ""){
                                        currentValue.bookingpress_selected_staffmember = bookingpress_selected_staffmember;
                                        var bookingpress_staffmember_price = 0;
                                        vm5.bookingpress_staffmembers_details.forEach(function(currentValue4, index4, arr4){
                                            if(currentValue4.bookingpress_staffmember_id == bookingpress_selected_staffmember && currentValue4.assigned_service_price_details[currentValue.bookingpress_service_id] != "undefined"){
                                                bookingpress_staffmember_price = currentValue4.assigned_service_price_details[currentValue.bookingpress_service_id].assigned_service_price;
                                            }
                                        });
    
                                        bookingpress_service_original_price = bookingpress_service_price = parseFloat(bookingpress_staffmember_price);
                                    }
                                }
    
                                var bookingpress_bring_anyone_members = parseInt(vm5.appointment_step_form_data.bookingpress_selected_bring_members) - 1;
                                if(bookingpress_bring_anyone_members > 0){
                                    bookingpress_service_original_price = bookingpress_service_price = bookingpress_service_price + (bookingpress_service_price * bookingpress_bring_anyone_members);
                                }
                                bookingpress_bring_anyone_members++;
    
                                var bookingpress_service_extra_total = 0;
                                var bookingpress_selected_extras_counter = 0;
                                var bookingpress_selected_extras_ids = [];
                                var bookingpress_selected_extras_qty = [];
                                vm5.bookingpress_service_extras.forEach(function(currentValue3, index3, arr3){
                                    if(currentValue3.bookingpress_service_id == currentValue.bookingpress_service_id && (vm5.appointment_step_form_data.bookingpress_selected_extra_details[currentValue3.bookingpress_extra_services_id].bookingpress_is_selected == "true" || vm5.appointment_step_form_data.bookingpress_selected_extra_details[currentValue3.bookingpress_extra_services_id].bookingpress_is_selected == true)){
                                        var bookingpress_selected_extra_service_qty = parseFloat(vm5.appointment_step_form_data.bookingpress_selected_extra_details[currentValue3.bookingpress_extra_services_id].bookingpress_selected_qty);
                                        var bookingpress_selected_extra_service_price = parseFloat(currentValue3.bookingpress_extra_service_price);
                                        bookingpress_service_extra_total = bookingpress_service_extra_total + (bookingpress_selected_extra_service_price * bookingpress_selected_extra_service_qty);
                                        bookingpress_selected_extras_counter = bookingpress_selected_extras_counter + 1;
    
                                        bookingpress_selected_extras_ids.push(currentValue3.bookingpress_extra_services_id);
                                        bookingpress_selected_extras_qty[currentValue3.bookingpress_extra_services_id] = bookingpress_selected_extra_service_qty;
                                    }
                                });
    
                                bookingpress_service_original_price = bookingpress_service_price = bookingpress_service_price + bookingpress_service_extra_total;
    
                                var bookingpress_deposit_price = 0;
                                var bookingpress_deposit_due_amount = 0;
                                if( vm5.bookingpress_is_deposit_payment_activate == 1 && ( vm5.appointment_step_form_data.bookingpress_deposit_payment_method == "deposit_or_full_price" || vm5.appointment_step_form_data.bookingpress_deposit_payment_method == "allow_customer_to_pay_full_amount" ) ){
    
                                    var bookingpress_deposit_amount = parseFloat(vm5.appointment_step_form_data.deposit_payment_amount);
                                    if(vm5.appointment_step_form_data.deposit_payment_type == "percentage"){
                                        bookingpress_deposit_price = bookingpress_service_price * (bookingpress_deposit_amount / 100);
                                    }else{
                                        bookingpress_deposit_price = bookingpress_deposit_amount;
                                    }
                                    bookingpress_service_price = bookingpress_deposit_due_amount = bookingpress_service_price - bookingpress_deposit_price;
                                }
                                bookingpress_service_original_amount_total = parseFloat(bookingpress_service_original_amount_total) + bookingpress_service_original_price;
                                bookingpress_cart_deposit_total = parseFloat(bookingpress_cart_deposit_total) + bookingpress_deposit_price;
                                bookingpress_cart_total = parseFloat(bookingpress_cart_total) + bookingpress_service_original_price;
    
                            });
                        }
                    }
                    '.$bookingpress_before_add_to_cart_item.'
                    if(is_service_added_to_cart == 0){
                        vm5.bpasortedServices.forEach(function(currentValue, index, arr){
                            if(currentValue.bookingpress_service_id == vm5.appointment_step_form_data.selected_service){
                                var bookingpress_service_price = parseFloat(currentValue.service_price_without_currency);
                                var bookingpress_service_original_price = parseFloat(currentValue.service_price_without_currency);
    
                                currentValue.bookingpress_selected_staffmember = "";
                                if(vm5.is_staffmember_activated == "1" && vm5.appointment_step_form_data.is_staff_exists == "1"){
                                    var bookingpress_selected_staffmember = vm5.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id;
                                    let bookingpress_staffmember_name = "";
                                    if(bookingpress_selected_staffmember != ""){
                                        currentValue.bookingpress_selected_staffmember = bookingpress_selected_staffmember;
                                        var bookingpress_staffmember_price = 0;
                                        
                                        vm5.bookingpress_staffmembers_details.forEach(function(currentValue4, index4, arr4){
                                            if(currentValue4.bookingpress_staffmember_id == bookingpress_selected_staffmember && currentValue4.assigned_service_price_details[currentValue.bookingpress_service_id] != "undefined"){
                                                bookingpress_staffmember_price = currentValue4.assigned_service_price_details[currentValue.bookingpress_service_id].assigned_service_price;
                                                bookingpress_staffmember_name = currentValue4.bookingpress_staffmember_firstname + " " + currentValue4.bookingpress_staffmember_lastname;
                                            }
                                        });
    
                                        bookingpress_service_original_price = bookingpress_service_price = parseFloat(bookingpress_staffmember_price);
                                    }
                                    currentValue.bookingpress_staffmember_name = bookingpress_staffmember_name;
    
                                }
                                '.$custom_service_duration_data.'
    
                                var bookingpress_bring_anyone_members = parseInt(vm5.appointment_step_form_data.bookingpress_selected_bring_members) - 1;
                                if( "number_of_guests" == vm5.appointment_step_form_data.bookingpress_selected_bring_members ){
                                    bookingpress_bring_anyone_members = 0;
                                }
                                if(bookingpress_bring_anyone_members > 0){
                                    bookingpress_service_original_price = bookingpress_service_price = bookingpress_service_price + (bookingpress_service_price * bookingpress_bring_anyone_members);
                                }
                                bookingpress_bring_anyone_members++;
    
                                var bookingpress_service_extra_total = 0;
                                var bookingpress_selected_extras_counter = 0;
                                var bookingpress_selected_extras_ids = [];
                                var bookingpress_selected_extras_qty = [];
                                let bookingpress_selected_extras_details = [];
                                vm5.bookingpress_service_extras.forEach(function(currentValue3, index3, arr3){
                                    if(currentValue3.bookingpress_service_id == currentValue.bookingpress_service_id && vm5.appointment_step_form_data.bookingpress_selected_extra_details[currentValue3.bookingpress_extra_services_id].bookingpress_is_selected == true){
                                        var bookingpress_selected_extra_service_qty = parseFloat(vm5.appointment_step_form_data.bookingpress_selected_extra_details[currentValue3.bookingpress_extra_services_id].bookingpress_selected_qty);
                                        var bookingpress_selected_extra_service_price = parseFloat(currentValue3.bookingpress_extra_service_price);
                                        bookingpress_service_extra_total = bookingpress_service_extra_total + (bookingpress_selected_extra_service_price * bookingpress_selected_extra_service_qty);
                                        bookingpress_selected_extras_counter = bookingpress_selected_extras_counter + 1;
    
                                        bookingpress_selected_extras_ids.push(currentValue3.bookingpress_extra_services_id);
                                        bookingpress_selected_extras_qty[currentValue3.bookingpress_extra_services_id] = bookingpress_selected_extra_service_qty;
                                        
                                        let service_extra_duration;
                                        if( "h" == currentValue3.bookingpress_extra_service_duration_unit ){
                                            service_extra_duration = currentValue3.bookingpress_extra_service_duration + " Hrs";
                                        } else {
                                            service_extra_duration = currentValue3.bookingpress_extra_service_duration + " Mins";
                                        }
    
                                        let service_extras_data = {
                                            "extra_service_id": currentValue3.bookingpress_extra_services_id,
                                            "extra_service_name": currentValue3.bookingpress_extra_service_name,
                                            "extra_service_duration": service_extra_duration,
                                            "extra_service_duration_val": parseInt(currentValue3.bookingpress_extra_service_duration),
                                            "extra_service_price_formatted": currentValue3.bookingpress_extra_formatted_price,
                                            "extra_service_price_qty": bookingpress_selected_extra_service_qty
                                        };
    
                                        bookingpress_selected_extras_details.push( service_extras_data );
                                    }
                                });
                                bookingpress_service_price = parseFloat(bookingpress_service_price) + bookingpress_service_extra_total;
                                bookingpress_service_original_price = parseFloat(bookingpress_service_price);
                                var bookingpress_deposit_price = 0;
                                var bookingpress_deposit_due_amount = 0;
                                if( vm5.bookingpress_is_deposit_payment_activate == 1 && ( vm5.appointment_step_form_data.bookingpress_deposit_payment_method == "deposit_or_full_price" || vm5.appointment_step_form_data.bookingpress_deposit_payment_method == "allow_customer_to_pay_full_amount" ) ){
                                    var bookingpress_deposit_amount = parseFloat(vm5.appointment_step_form_data.deposit_payment_amount);
                                    if(vm5.appointment_step_form_data.deposit_payment_type == "percentage"){
                                        bookingpress_deposit_price = bookingpress_service_price * (bookingpress_deposit_amount / 100);
                                    }else{
                                        bookingpress_deposit_price = bookingpress_deposit_amount;
                                    }
                                    bookingpress_service_price = bookingpress_deposit_due_amount = bookingpress_service_price - bookingpress_deposit_price;
                                }
    
                                currentValue.bookingpress_service_original_price = bookingpress_service_original_price;
                                currentValue.bookingpress_service_final_price = bookingpress_service_price;
                                currentValue.bookingpress_service_final_price_without_currency = bookingpress_service_price;
                                currentValue.bookingpress_selected_extras_counter = bookingpress_selected_extras_counter;
                                currentValue.bookingpress_selected_extras_ids = bookingpress_selected_extras_ids;
                                currentValue.bookingpress_selected_extras_qty = bookingpress_selected_extras_qty;
                                currentValue.bookingpress_selected_extras_details = bookingpress_selected_extras_details;
                                currentValue.bookingpress_bring_anyone_selected_members = parseInt(bookingpress_bring_anyone_members);
                                currentValue.bookingpress_selected_date = vm5.appointment_step_form_data.selected_date;
                                currentValue.bookingpress_selected_start_time = vm5.appointment_step_form_data.selected_start_time;
                                currentValue.bookingpress_selected_end_time = vm5.appointment_step_form_data.selected_end_time;
    
                                currentValue.formatted_start_time = vm5.appointment_step_form_data.selected_formatted_start_time;
                                currentValue.formatted_end_time = vm5.appointment_step_form_data.selected_formatted_end_time;
                                currentValue.formatted_start_end_time = vm5.appointment_step_form_data.selected_formatted_start_end_time;
    
                                currentValue.bookingpress_store_selected_date = vm5.appointment_step_form_data.store_selected_date;
                                currentValue.bookingpress_store_start_time = vm5.appointment_step_form_data.store_start_time;
                                currentValue.bookingpress_store_end_time = vm5.appointment_step_form_data.store_end_time;
                                currentValue.bookingpress_deposit_price = bookingpress_deposit_price;
                                currentValue.bookingpress_deposit_due_amount = bookingpress_deposit_due_amount;
                                currentValue.bookingpress_is_edit = 0;                            
                                currentValue.bookingpress_is_expand = 0;
                                currentValue.bookingpress_is_extra_expand = 0;
                                vm5.appointment_step_form_data.cart_items.push(currentValue);
                                
                                let cart_item_index = vm5.appointment_step_form_data.cart_items.length - 1;
                                vm5.appointment_step_form_data.cart_item_edit_index = cart_item_index;
                                
                                bookingpress_service_original_amount_total = parseFloat(bookingpress_service_original_amount_total) + bookingpress_service_original_price;
                                bookingpress_cart_deposit_total = parseFloat(bookingpress_cart_deposit_total) + bookingpress_deposit_price;
                                bookingpress_cart_total = parseFloat(bookingpress_cart_total) + bookingpress_service_original_price;
                            }
                        });
                    }
    
                    vm5.appointment_step_form_data.bookingpress_service_original_amount_total = bookingpress_service_original_amount_total;
                    vm5.appointment_step_form_data.bookingpress_cart_deposit_total = bookingpress_cart_deposit_total;
                    vm5.appointment_step_form_data.bookingpress_cart_total = bookingpress_cart_total;
                    vm5.appointment_step_form_data.bookingpress_cart_original_total = bookingpress_cart_total;
                },
                bookingpress_add_more_service_to_cart(){
                    const vm = this;

                    let all_categories = vm.bookingpress_all_categories;
                    if( all_categories.length > 0 ){
                        let n = 0;
                        for( let c in all_categories ) {
                            if( n == 0 && c == 0 ){
                                n++;
                                continue;
                            }
                            if( n < 2 ){
                                vm.bpa_select_category( all_categories[c].category_id, all_categories[c].category_name, all_categories[c].total_services );
                                break;
                            }
                            n++;
                        }
                    }

                    vm.appointment_step_form_data.selected_cat_name = "";
                    vm.appointment_step_form_data.selected_service = "";
                    vm.appointment_step_form_data.selected_service_name = "";
                    vm.appointment_step_form_data.selected_service_price = "";
                    vm.appointment_step_form_data.service_price_without_currency = 0;
                    vm.appointment_step_form_data.selected_start_time = "";
                    vm.appointment_step_form_data.selected_end_time = "";
                    vm.appointment_step_form_data.total_payable_amount = "";
                    vm.appointment_step_form_data.bookingpress_selected_bring_members = 1;
                    vm.appointment_step_form_data.service_max_capacity = 1;
                    if( true == vm.bookingpress_cart_reset_staff ){
                        vm.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id = 0;
                        vm.appointment_step_form_data.bookingpress_selected_staff_member_details.staff_member_id = "";
                        vm.appointment_step_form_data.selected_staff_member_id = 0;
                    }
                    vm.appointment_step_form_data.is_extra_service_exists = 0;
                    vm.appointment_step_form_data.is_staff_exists = 0;
                    vm.appointment_step_form_data.cart_item_edit_index = -1;

                    vm.bookingpress_cart_total_with_currency = "";
                    vm.bookingpress_deposit_due_amount_total = 0;
                    vm.bookingpress_deposit_due_amount_total_with_currency = "";
                    vm.bookingpress_deposit_total = 0;
                    vm.bookingpress_deposit_total_with_currency = "";
                    vm.deposit_payment_amount = "";
                    vm.deposit_payment_amount_percentage = "";
                    vm.deposit_payment_formatted_amount = "";
                    vm.total_payable_amount = 0;
                    vm.total_payable_amount_with_currency = "";

                    '.$bookingpress_before_add_more_service_to_cart.'
                    
                    let newDate = new Date('. ( !empty( $bookingpress_site_date ) ? '"'.$bookingpress_site_date.'"' : '' ) .');
    
                    let pattern = /(\d{4}\-\d{2}\-\d{2})/;
                    if( !pattern.test( newDate ) ){
    
                        let sel_month = newDate.getMonth() + 1;
                        let sel_year = newDate.getFullYear();
                        let sel_date = newDate.getDate();
    
                        if( sel_month < 10 ){
                            sel_month = "0" + sel_month;
                        }
    
                        if( sel_date < 10 ){
                            sel_date = "0" + sel_date;
                        }
                        
                        newDate = sel_year + "-" + sel_month + "-" + sel_date;
                    }
    
                    vm.appointment_step_form_data.selected_date = newDate;
    
                    vm.bookingpress_service_extras.forEach(function(currentValue, index, arr){
                        var bookingpress_extra_service_id = parseInt(currentValue.bookingpress_extra_services_id);
                        vm.appointment_step_form_data.bookingpress_selected_extra_details[bookingpress_extra_service_id].bookingpress_is_selected = false;
                        vm.appointment_step_form_data.bookingpress_selected_extra_details[bookingpress_extra_service_id].bookingpress_selected_qty = 1;
                    });
    
                    let sidebar_step_data = [];
                    for( let x in vm.bookingpress_sidebar_step_data ){
                        let step_data = vm.bookingpress_sidebar_step_data[x];
                        if( step_data.is_first_step == 1 ){
                            sidebar_step_data = vm.bookingpress_sidebar_step_data[x];
                            break;
                        }
                    }
    
                    if( sidebar_step_data.length == 0 ){
                        if(typeof vm.bookingpress_sidebar_step_data["staffmembers"] != "undefined"){
                            vm.bookingpress_step_navigation("staffmembers", vm.bookingpress_sidebar_step_data["staffmembers"].next_tab_name, vm.bookingpress_sidebar_step_data["staffmembers"].previous_tab_name)
                        }else{
                            vm.bookingpress_step_navigation("service", vm.bookingpress_sidebar_step_data["service"].next_tab_name, vm.bookingpress_sidebar_step_data["service"].previous_tab_name)
                        }
                    } else {
                        vm.bookingpress_step_navigation( sidebar_step_data.tab_value, sidebar_step_data.next_tab_name, sidebar_step_data.previous_tab_name );
                    }
                    
                    vm.bookingpress_sidebar_step_data.cart.is_allow_navigate = 0;
                },
                bookingpress_edit_cart_item(edit_id, move_to_service = true){
                    const vm = this;
    
                    vm.appointment_step_form_data.cart_item_edit_index  = edit_id;
    
                    var bookingpress_edit_data = vm.appointment_step_form_data.cart_items[edit_id];
                    
                    var bookingpress_selected_staffmember = parseInt(bookingpress_edit_data.bookingpress_selected_staffmember);
                    var bookingpress_selected_bring_members = parseInt(bookingpress_edit_data.bookingpress_bring_anyone_selected_members);
                    
                    if( "" !== bookingpress_edit_data.bookingpress_category_id ){
                        let selected_category = bookingpress_edit_data.bookingpress_category_id;
                        for( let x in vm.bookingpress_all_categories ){
                            let cat_data = vm.bookingpress_all_categories[x];
                            if( cat_data.category_id == selected_category ){
                                vm.bpa_select_category( cat_data.category_id, cat_data.category_name, cat_data.total_services );
                                break;
                            }
                        }
                    }

                    vm.selectDate(bookingpress_edit_data.bookingpress_service_id, bookingpress_edit_data.bookingpress_service_name, bookingpress_edit_data.bookingpress_service_price, bookingpress_edit_data.service_price_without_currency);
                    
                    if( isNaN( bookingpress_selected_staffmember ) ){
                        bookingpress_selected_staffmember = "";
                    }
                    
                    vm.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id = bookingpress_selected_staffmember.toString();
                    vm.appointment_step_form_data.selected_staff_member_id = bookingpress_selected_staffmember.toString();

                    vm.appointment_step_form_data.selected_date = bookingpress_edit_data.bookingpress_selected_date;
                    vm.appointment_step_form_data.selected_start_time = bookingpress_edit_data.bookingpress_selected_start_time;
                    vm.appointment_step_form_data.selected_end_time = bookingpress_edit_data.bookingpress_selected_end_time;
                    vm.appointment_step_form_data.selected_service_duration = bookingpress_edit_data.bookingpress_service_duration_val;
                    vm.appointment_step_form_data.selected_service_duration_unit = bookingpress_edit_data.bookingpress_service_duration_unit;
                    vm.appointment_step_form_data.selected_service_price = bookingpress_edit_data.bookingpress_service_original_price_with_currency;
                    vm.appointment_step_form_data.service_price_without_currency = bookingpress_edit_data.bookingpress_service_original_price;                    

                    vm.appointment_step_form_data.selected_formatted_start_time = bookingpress_edit_data.formatted_start_time;
                    vm.appointment_step_form_data.selected_formatted_end_time = bookingpress_edit_data.formatted_end_time;

                    vm.appointment_step_form_data.store_start_time = bookingpress_edit_data.bookingpress_store_start_time;
                    vm.appointment_step_form_data.store_end_time = bookingpress_edit_data.bookingpress_store_end_time;

                    if( 0 == bookingpress_selected_bring_members ){
                        bookingpress_selected_bring_members = "number_of_guests";
                    }
                    vm.appointment_step_form_data.bookingpress_selected_bring_members = bookingpress_selected_bring_members;
    
                    vm.bookingpress_service_extras.forEach(function(currentValue, index, arr){
                        var bookingpress_extra_service_id = currentValue.bookingpress_extra_services_id;
                        
                        if( "undefined" != typeof bookingpress_edit_data.bookingpress_selected_extras_ids && bookingpress_edit_data.bookingpress_selected_extras_ids.includes( bookingpress_extra_service_id ) ){
                            vm.appointment_step_form_data.bookingpress_selected_extra_details[bookingpress_extra_service_id].bookingpress_is_selected = true;
                            let bookingpress_selected_extra_qty = bookingpress_edit_data.bookingpress_selected_extras_qty[bookingpress_extra_service_id];
                            vm.appointment_step_form_data.bookingpress_selected_extra_details[bookingpress_extra_service_id].bookingpress_selected_qty = bookingpress_selected_extra_qty;
                        } else {
                            vm.appointment_step_form_data.bookingpress_selected_extra_details[bookingpress_extra_service_id].bookingpress_is_selected = false;
                            vm.appointment_step_form_data.bookingpress_selected_extra_details[bookingpress_extra_service_id].bookingpress_selected_qty = 1;                            
                        }
                    });
                    vm.appointment_step_form_data.cart_items[edit_id].bookingpress_is_edit = 1;
                    if( move_to_service ){
                        let sidebar_step_data = [];
                        for( let x in vm.bookingpress_sidebar_step_data ){
                            let step_data = vm.bookingpress_sidebar_step_data[x];
                            if( step_data.is_first_step == 1 ){
                                sidebar_step_data = vm.bookingpress_sidebar_step_data[x];
                                break;
                            }
                        }
    
                        if( sidebar_step_data.length == 0 ){
                            if(typeof vm.bookingpress_sidebar_step_data["staffmembers"] != "undefined"){
                                vm.bookingpress_step_navigation("staffmembers", vm.bookingpress_sidebar_step_data["staffmembers"].next_tab_name, vm.bookingpress_sidebar_step_data["staffmembers"].previous_tab_name)
                            }else{
                                vm.bookingpress_step_navigation("service", vm.bookingpress_sidebar_step_data["service"].next_tab_name, vm.bookingpress_sidebar_step_data["service"].previous_tab_name)
                            }
                        } else {
                            vm.bookingpress_step_navigation( sidebar_step_data.tab_value, sidebar_step_data.next_tab_name, sidebar_step_data.previous_tab_name );
                        }
                    }
                },
                bookingpress_delete_cart_item(delete_id){
                    const vm = this;

                    '.$bookingpress_before_remove_cart_item.'

                    vm.appointment_step_form_data.cart_items.splice(delete_id, 1);
                    
                    if(vm.appointment_step_form_data.cart_items.length == 0){
                        let all_categories = vm.bookingpress_all_categories;
                        if( all_categories.length > 0 ){
                            let n = 0;
                            for( let c in all_categories ) {
                                if( n == 0 && c == 0 ){
                                    n++;
                                    continue;
                                }
                                if( n < 2 ){
                                    vm.bpa_select_category( all_categories[c].category_id, all_categories[c].category_name, all_categories[c].total_services );
                                    break;
                                }
                                n++;
                            }
                        }
                        '.$bookingpress_default_select_all_category_data.'
                        vm.appointment_step_form_data.selected_cat_name = "";
                        
                        vm.appointment_step_form_data.selected_service_name = "";
                        
                        vm.appointment_step_form_data.selected_service_price = "";

                        vm.appointment_step_form_data.service_price_without_currency = 0;
                        
                        vm.appointment_step_form_data.selected_start_time = "";
                        
                        vm.appointment_step_form_data.selected_end_time = "";
                        
                        vm.appointment_step_form_data.total_payable_amount = "";
                        
                        vm.appointment_step_form_data.bookingpress_selected_bring_members = 1;
                        
                        vm.appointment_step_form_data.service_max_capacity = 1;
                        
                        if( true == vm.bookingpress_cart_reset_staff ){
                            vm.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id = 0;
                            vm.appointment_step_form_data.bookingpress_selected_staff_member_details.staff_member_id = "";
                            vm.appointment_step_form_data.selected_staff_member_id = "";
                        } 
                        
                        vm.appointment_step_form_data.is_extra_service_exists = 0;
                            
                        vm.appointment_step_form_data.cart_item_edit_index = -1;

                        vm.appointment_step_form_data.bookingpress_cart_total = 0;
                        vm.appointment_step_form_data.bookingpress_cart_original_total = 0;

                        let newDate = new Date('. ( !empty( $bookingpress_site_date ) ? '"'.$bookingpress_site_date.'"' : '' ) .');

                        let pattern = /(\d{4}\-\d{2}\-\d{2})/;
                        if( !pattern.test( newDate ) ){

                            let sel_month = newDate.getMonth() + 1;
                            let sel_year = newDate.getFullYear();
                            let sel_date = newDate.getDate();

                            if( sel_month < 10 ){
                                sel_month = "0" + sel_month;
                            }

                            if( sel_date < 10 ){
                                sel_date = "0" + sel_date;
                            }
                            
                            newDate = sel_year + "-" + sel_month + "-" + sel_date;
                        }

                        vm.appointment_step_form_data.selected_date = newDate;

                        vm.appointment_step_form_data.selected_service = "";

                        '.$bookingpress_after_empty_cart.'

                        vm.bookingpress_refresh_cart_details( true, true, delete_id );

                        let sidebar_step_data = [];
                        for( let x in vm.bookingpress_sidebar_step_data ){
                            let step_data = vm.bookingpress_sidebar_step_data[x];
                            if( step_data.is_first_step == 1 ){
                                sidebar_step_data = vm.bookingpress_sidebar_step_data[x];
                                break;
                            }
                        }

                        if( sidebar_step_data.length == 0 ){
                            if(typeof vm.bookingpress_sidebar_step_data["staffmembers"] != "undefined"){
                                vm.bookingpress_step_navigation("staffmembers", vm.bookingpress_sidebar_step_data["staffmembers"].next_tab_name, vm.bookingpress_sidebar_step_data["staffmembers"].previous_tab_name)
                            }else{
                                vm.bookingpress_step_navigation("service", vm.bookingpress_sidebar_step_data["service"].next_tab_name, vm.bookingpress_sidebar_step_data["service"].previous_tab_name)
                            }
                        } else {
                            vm.bookingpress_step_navigation( sidebar_step_data.tab_value, sidebar_step_data.next_tab_name, sidebar_step_data.previous_tab_name );
                        }

                    } else {
                        let cart_item_index = vm.appointment_step_form_data.cart_items.length - 1;
                        vm.appointment_step_form_data.cart_item_edit_index = cart_item_index;
                        vm.bookingpress_refresh_cart_details( false, true, delete_id )
                    }
                    
                },';
            }

            $bookingpress_modify_cart_xhr_response_data = '';
            $bookingpress_modify_cart_xhr_response_data = apply_filters( 'bookingpress_modify_cart_xhr_response_data', $bookingpress_modify_cart_xhr_response_data );

            $bookingpress_vue_methods_data .= '
            recalculate_cart_amount_total_data(){
                const vm = this;
                if(typeof vm.appointment_step_form_data.cart_items != "undefined"){                    
                    if(vm.appointment_step_form_data.cart_items.length > 0){
                        var cart_items = vm.appointment_step_form_data.cart_items;
                        var total = vm.appointment_step_form_data.cart_items.length;
                        var bookingpress_cart_total = 0,bookingpress_deposit_total = 0 ,bookingpress_deposit_due_amount_total=0,bookingpress_service_original_amount_total = 0;
                        for(let i=0;i<total;i++){                            
                            if(typeof cart_items[i] != "undefined") {

                               cart_items[i]["bookingpress_bring_anyone_selected_members"] =  parseInt(cart_items[i]["bookingpress_bring_anyone_selected_members"]);
                               cart_items[i]["bookingpress_service_final_price"] =  vm.bookingpress_price_with_currency_symbol(cart_items[i]["bookingpress_service_final_price_without_currency"]);
                               cart_items[i]["bookingpress_service_original_price_with_currency"] = vm.bookingpress_price_with_currency_symbol(cart_items[i]["bookingpress_service_original_price"]);
                               bookingpress_cart_total = parseFloat(bookingpress_cart_total) + parseFloat(cart_items[i]["bookingpress_service_original_price"]);                               
                               if(typeof cart_items[i]["bookingpress_deposit_price"] != "undefined" && cart_items[i]["bookingpress_deposit_price"] != ""){ 
                                  bookingpress_deposit_total = parseFloat(bookingpress_deposit_total) + parseFloat(cart_items[i]["bookingpress_deposit_price"]);
                                  bookingpress_deposit_due_amount_total = parseFloat(bookingpress_deposit_due_amount_total) + parseFloat(cart_items[i]["bookingpress_deposit_due_amount"]);
                                  cart_items[i]["bookingpress_deposit_price_with_currency"] = vm.bookingpress_price_with_currency_symbol(cart_items[i]["bookingpress_deposit_price"]);
                                  cart_items[i]["bookingpress_deposit_due_amount_with_currency"] = vm.bookingpress_price_with_currency_symbol(cart_items[i]["bookingpress_deposit_due_amount"]);
                                  bookingpress_service_original_amount_total = parseFloat(bookingpress_service_original_amount_total) + parseFloat(cart_items[i]["bookingpress_service_original_price"]);                                                                      
                               }
                            }
                        }                        
                        vm.appointment_step_form_data.cart_items = cart_items;
                        vm.appointment_step_form_data.bookingpress_cart_total = bookingpress_cart_total;
                        vm.appointment_step_form_data.bookingpress_cart_total_with_currency = vm.bookingpress_price_with_currency_symbol(bookingpress_cart_total);
                        vm.appointment_step_form_data.bookingpress_cart_original_total = bookingpress_cart_total;
                        vm.appointment_step_form_data.bookingpress_cart_original_total_with_currency = vm.bookingpress_price_with_currency_symbol(bookingpress_cart_total);
                        vm.appointment_step_form_data.bookingpress_deposit_total = bookingpress_deposit_total;
                        vm.appointment_step_form_data.bookingpress_deposit_total_with_currency = vm.bookingpress_price_with_currency_symbol(bookingpress_deposit_total);
                        vm.appointment_step_form_data.total_payable_amount = bookingpress_cart_total + bookingpress_deposit_total;
                        vm.appointment_step_form_data.total_payable_amount_with_currency = vm.bookingpress_price_with_currency_symbol( bookingpress_cart_total + bookingpress_deposit_total);
                        vm.appointment_step_form_data.bookingpress_deposit_due_amount_total = bookingpress_deposit_due_amount_total;
                        vm.appointment_step_form_data.bookingpress_deposit_due_amount_total_with_currency = vm.bookingpress_price_with_currency_symbol( bookingpress_deposit_due_amount_total );
                    }
                }
            },            
            bookingpress_refresh_cart_details( is_empty_cart = false, is_delete_item = false, deleted_cart_item_index = -1, use_loader = true ){
                const vm = this;
                vm.isLoadCartLoader = true;
                vm.recalculate_cart_amount_total_data();

                var bkp_wpnonce_pre = "'.esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ).'";
                var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
                if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null){
                    bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
                } else {
                    bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
                }
                
                var bookingpress_cart_post_data = {
                    action: "bookingpress_modify_cart_data_after_add_to_cart", 
                    empty_cart: is_empty_cart,
                    delete_cart_item: is_delete_item,
                    deleted_item_index: deleted_cart_item_index,
                    _wpnonce: bkp_wpnonce_pre_fetch
                };

                bookingpress_cart_post_data.bookingpress_appointment_data = JSON.stringify( vm.appointment_step_form_data );

                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( bookingpress_cart_post_data ) )
                .then( function (response) 
                {
                    if(response.data.variant == "success"){
                        vm.appointment_step_form_data = response.data.appointment_step_form_data;
                    }
                    if( true == use_loader ){
                        vm.isLoadCartLoader = false;
                    }
                    '.$bookingpress_modify_cart_xhr_response_data.'
                }.bind(this) )
                .catch( function (error) {
                    vm.bookingpress_set_error_msg(error)
                });
            },
            bookingpress_navigate_to_cart(){
                const vm = this;
                let sidebar_steps = vm.bookingpress_sidebar_step_data;
                for( let step in sidebar_steps ){
                    let step_data = sidebar_steps[ step ];
                    
                    let next_tab_name = step_data.next_tab_name;
                    let prev_tab_name = step_data.previous_tab_name;

                    vm.bookingpress_step_navigation( next_tab_name, next_tab_name, prev_tab_name );

                    if( step_data.next_tab_name == "cart" ){
                        break;
                    }
                }
            },
            bookingpress_add_more_service_to_cart_old(){
                const vm = this;

                vm.appointment_step_form_data.cart_item_edit_index = -1; /** resetting cart editing index to insert new item */
                
                vm.bookingpress_step_navigation("service", vm.bookingpress_sidebar_step_data["service"].next_tab_name, vm.bookingpress_sidebar_step_data["service"].previous_tab_name);
            },

            bookingpress_show_cart_extras(){
                const vm = this;
                vm.cart_extra_items_modal = true;
            },
            bookingpress_expand_cart_item(index) {
                const vm = this;
                if(typeof vm.appointment_step_form_data.cart_items != "undefined" && vm.appointment_step_form_data.cart_items != "") {
                    vm.appointment_step_form_data.cart_items.forEach( function(currentValue,i,arr) {
                        if(index != i) 
                        {   
                            vm.appointment_step_form_data.cart_items[i].bookingpress_is_expand = 0;
                        }
                    });
                    if(typeof vm.appointment_step_form_data.cart_items[index] != "undefined" ) {
                        vm.appointment_step_form_data.cart_items[index].bookingpress_is_expand = vm.appointment_step_form_data.cart_items[index].bookingpress_is_expand == 0 ? 1 : 0;
                    }
                }
            },
            bookingpress_expand_service_extras(index) {
                const vm = this;
                vm.appointment_step_form_data.cart_items[index].bookingpress_is_extra_expand = vm.appointment_step_form_data.cart_items[index].bookingpress_is_extra_expand == 0 ? 1 : 0;
            },
            ';
            return $bookingpress_vue_methods_data;
        }

        function bookingpress_modify_cart_data_after_add_to_cart_func($return_data = false){
            global $wpdb, $BookingPress, $tbl_bookingpress_form_fields;
            $response              = array();
			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

            
            $bookingpress_pro_version = get_option( 'bookingpress_pro_version');
            if(empty($bookingpress_pro_version)){
                $bookingpress_pro_version = 0;
            }

            $is_empty_cart = !empty( $_POST['empty_cart'] ) ? sanitize_text_field( $_POST['empty_cart'] ) : false;
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-cart' );
				$response['msg']     = esc_html__( 'Sorry, Your request can not process due to security reason.', 'bookingpress-cart' );
                if($return_data){
                    return $response;
                }
				echo wp_json_encode( $response );
				die();
			}
			$response['variant']       = 'success';
			$response['title']         = esc_html__( 'Success', 'bookingpress-cart' );
			$response['msg']           = esc_html__( 'Data modified successfully', 'bookingpress-cart' );
            $response['appointment_step_form_data'] = array();
            $bookingpress_version = get_option( 'bookingpress_version' );

            if( ( version_compare( $bookingpress_pro_version, '2.6', '<' ) ) || (!empty( $bookingpress_version ) && version_compare( $bookingpress_version, '1.0.66', '<' ) && session_id() == '' OR session_status() === PHP_SESSION_NONE)) {
				session_start();
			}

            if( !empty( $_POST['bookingpress_appointment_data'] ) && !is_array( $_POST['bookingpress_appointment_data'] ) ){
                $_POST['bookingpress_appointment_data'] = json_decode( stripslashes_deep( $_POST['bookingpress_appointment_data'] ), true ); //phpcs:ignore
                $_POST['bookingpress_appointment_data'] =  !empty($_POST['bookingpress_appointment_data']) ? array_map(array($this,'bookingpress_boolean_type_cast'), $_POST['bookingpress_appointment_data'] ) : array(); // phpcs:ignore
            }

			$bookingpress_uniq_id = !empty($_POST['bookingpress_appointment_data']['bookingpress_uniq_id']) ? sanitize_text_field( $_POST['bookingpress_appointment_data']['bookingpress_uniq_id'] ) : '';
            $bookingpress_form_token = !empty( $_POST['bookingpress_appointment_data']['bookingpress_form_token'] ) ? sanitize_text_field( $_POST['bookingpress_appointment_data']['bookingpress_form_token'] ) : $bookingpress_uniq_id;

            $is_deleted = !empty( $_POST['delete_cart_item'] ) ? sanitize_text_field( $_POST['delete_cart_item'] ) : '';
            $use_transient = false;
            if( version_compare( $bookingpress_version, '1.0.82', '<') && version_compare( $bookingpress_pro_version, '3.3.1', '<' ) ){
                $get_cart_timings = get_transient( 'bpa_cart_front_timings_'.$bookingpress_form_token );
                //$get_front_timings = get_transient( 'bpa_front_timings_'. $bookingpress_form_token.'_'.$appointment_selected_date );
                $use_transient = true;
            } else {
                $get_cart_timings = $this->bookingpress_get_transient( 'bpa_cart_front_timings_'.$bookingpress_form_token );
                //$get_front_timings = $this->bookingpress_get_transient( 'bpa_front_timings_'. $bookingpress_form_token.'_'.$appointment_selected_date );
            }
            if( 'true' == $is_deleted ){

                $deleted_item_index = (!empty( $_POST['deleted_item_index'] ) || $_POST['deleted_item_index'] == 0) ? intval( $_POST['deleted_item_index'] ) : -1;
                $response['before_delete'] = $get_cart_timings;
                if( -1 < $deleted_item_index ){

                    if( ( version_compare( $bookingpress_pro_version, '2.6', '<' ) ) || (!empty( $bookingpress_version ) && version_compare( $bookingpress_version, '1.0.66', '<' )) ){
                        if( !empty( $_SESSION['cart_timings'][ $deleted_item_index ] ) ){
                            unset( $_SESSION['cart_timings'][ $deleted_item_index ] );

                            $_SESSION['cart_timings'] = array_values( $_SESSION['cart_timings'] );
                        }
                    } else {
                        if( !empty( $get_cart_timings[ $deleted_item_index ] ) ){
                            unset( $get_cart_timings[ $deleted_item_index ] );                            
                            $get_cart_timings_temp = array();
                            if(!empty($get_cart_timings)){
                                foreach($get_cart_timings as $key=>$val){
                                    $get_cart_timings_temp[] = $get_cart_timings[$key];
                                }
                            }
                            if( false == $use_transient ){
                                $this->bookingpress_update_transient( 'bpa_cart_front_timings_' . $bookingpress_form_token, $get_cart_timings_temp, HOUR_IN_SECONDS  );
                            } else {
                                set_transient( 'bpa_cart_front_timings_'. $bookingpress_form_token, $get_cart_timings_temp, HOUR_IN_SECONDS );
                            }
                        }
                    }
                }
                $response['after_delete'] = $get_cart_timings;
            } else {
                
                if( isset( $_POST['bookingpress_appointment_data']['cart_item_edit_index'] ) ){
                    $cart_item_index = intval($_POST['bookingpress_appointment_data']['cart_item_edit_index']);
                    if(( version_compare( $bookingpress_pro_version, '2.6', '<' ) ) ||  (!empty( $bookingpress_version ) && version_compare( $bookingpress_version, '1.0.66', '<' )) ){
                        if( empty( $_SESSION['cart_timings'] ) ){    
                            $_SESSION['cart_timings']  = array();
                        }
                        $_SESSION['cart_timings'][ $cart_item_index ] = $_SESSION['front_timings'];
                    } else {
                        if( empty( $get_cart_timings ) ){
                            $get_cart_timings = array();
                        }
                        $transient_key = 'bpa_front_timings_' . $bookingpress_form_token.'_' . $_POST['bookingpress_appointment_data']['cart_items'][$cart_item_index]['bookingpress_selected_date'];
                        $get_front_timings = get_transient( $transient_key );
                        $get_cart_timings[ $cart_item_index ] = $get_front_timings;
                    }
                    if( true == $use_transient ){
                        set_transient( 'bpa_cart_front_timings_'. $bookingpress_form_token, $get_cart_timings, HOUR_IN_SECONDS );
                    } else {
                        $this->bookingpress_update_transient( 'bpa_cart_front_timings_' . $bookingpress_form_token, $get_cart_timings, HOUR_IN_SECONDS  );
                    }
                }
            }

            $bookingpress_appointment_details = !empty($_POST['bookingpress_appointment_data']) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['bookingpress_appointment_data'] ) : array(); // phpcs:ignore
            
            $cart_item_index = intval($_POST['bookingpress_appointment_data']['cart_item_edit_index']);
            if( !empty( $bookingpress_appointment_details['multiple_quantity_token'] ) ){
                $bookingpress_appointment_details['cart_items'][ $cart_item_index ]['multiple_quantity_token'] = $bookingpress_appointment_details['multiple_quantity_token'];
            }

            $bookingpress_cart_total = $bookingpress_deposit_total = $bookingpress_deposit_due_amount_total = $bookingpress_service_original_amount_total = 0;
            
            /*
            if( !empty( $bookingpress_appointment_details['cart_items'] ) ){   
                foreach($bookingpress_appointment_details['cart_items'] as $k => $v){
                    $bookingpress_appointment_details['cart_items'][$k]['bookingpress_bring_anyone_selected_members'] = intval($v['bookingpress_bring_anyone_selected_members']);

                    $bookingpress_appointment_details['cart_items'][$k]['bookingpress_service_final_price'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($v['bookingpress_service_final_price_without_currency']);

                    $bookingpress_appointment_details['cart_items'][$k]['bookingpress_service_original_price_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($v['bookingpress_service_original_price']);
                    
                    $bookingpress_cart_total = $bookingpress_cart_total + floatval($v['bookingpress_service_original_price']);
                    if(!empty($v['bookingpress_deposit_price'])){
                        $bookingpress_deposit_total = $bookingpress_deposit_total + floatval($v['bookingpress_deposit_price']);
                        $bookingpress_deposit_due_amount_total = $bookingpress_deposit_due_amount_total + floatval($v['bookingpress_deposit_due_amount']);

                        $bookingpress_appointment_details['cart_items'][$k]['bookingpress_deposit_price_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($v['bookingpress_deposit_price']);
                        $bookingpress_appointment_details['cart_items'][$k]['bookingpress_deposit_due_amount_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($v['bookingpress_deposit_due_amount']);
                    }
                    $bookingpress_service_original_amount_total = $bookingpress_service_original_amount_total + floatval($v['bookingpress_service_original_price']);
                }
            } else {
                $bookingpress_appointment_details['cart_items'] = array();
            }
                        
            $bookingpress_appointment_details['bookingpress_cart_total'] = $bookingpress_cart_total;
            $bookingpress_appointment_details['bookingpress_cart_total_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_cart_total);
            $bookingpress_appointment_details['bookingpress_cart_original_total'] = $bookingpress_cart_total;
            $bookingpress_appointment_details['bookingpress_cart_original_total_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_cart_total);
            $bookingpress_appointment_details['bookingpress_deposit_total'] = $bookingpress_deposit_total;
            $bookingpress_appointment_details['bookingpress_deposit_total_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_deposit_total);

            // Added on 14th April,2023
            $bookingpress_appointment_details['total_payable_amount'] = $bookingpress_cart_total + $bookingpress_deposit_total;
            $bookingpress_appointment_details['total_payable_amount_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_cart_total + $bookingpress_deposit_total);
            $bookingpress_appointment_details['bookingpress_deposit_due_amount_total'] = $bookingpress_deposit_due_amount_total;
            $bookingpress_appointment_details['bookingpress_deposit_due_amount_total_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_deposit_due_amount_total);
            */

            /** Get All Checkboxes */
            $bookingpress_all_checkbox_fields = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_field_meta_key FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_field_type = %s AND bookingpress_is_customer_field = %d", 'checkbox', 0 ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is table name.
            if( !empty( $bookingpress_all_checkbox_fields ) ){
                foreach( $bookingpress_all_checkbox_fields as $checkbox_field_val ){
                    $checkbox_meta_key = $checkbox_field_val->bookingpress_field_meta_key;
                    if( empty( $bookingpress_appointment_details['form_fields'][ $checkbox_meta_key ] ) || !isset( $bookingpress_appointment_details['form_fields'][ $checkbox_meta_key ] ) || !is_array( $bookingpress_appointment_details['form_fields'][ $checkbox_meta_key ] ) ){
                        $bookingpress_appointment_details['form_fields'][ $checkbox_meta_key ] = array();
                    }
                }
            }


            //Reset staff value after added to cart
            if( !empty($bookingpress_appointment_details['bookingpress_selected_staff_member_details']['selected_staff_member_id']) || (!empty($bookingpress_appointment_details['bookingpress_selected_staff_member_details']['is_any_staff_option_selected'])) ){
               // $bookingpress_appointment_details['bookingpress_selected_staff_member_details']['selected_staff_member_id'] = '';
               // $bookingpress_appointment_details['bookingpress_selected_staff_member_details']['is_any_staff_option_selected'] = '';
            }

            $bookingpress_appointment_details = apply_filters('bookingpress_front_modify_cart_data_filter',$bookingpress_appointment_details);

            $bookingpress_appointment_details['service_max_capacity'] = intval( $bookingpress_appointment_details['service_max_capacity'] );

           /*  print_r( $bookingpress_appointment_details ); */

            $response['appointment_step_form_data'] = $bookingpress_appointment_details;
            if($return_data){
                return $response;
            }
            echo wp_json_encode($response);
            exit;
        }

        function bookingpress_add_front_side_sidebar_step_content_func($bookingpress_goback_btn_text='', $bookingpress_next_btn_text='', $bookingpress_third_tab_name=''){
            global $bookingpress_cart_version,$BookingPress;            
            $hide_category_service = $BookingPress->bookingpress_get_customize_settings('hide_category_service_selection','booking_form');         
            $bpa_not_allow_add_more_cart_service = ( !empty($hide_category_service) && $hide_category_service == 'true' ) ? 1 : 0;
            if(( isset($_GET['allow_modify'])) ) {
                if($_GET['allow_modify'] == 1) {
                    $bpa_not_allow_add_more_cart_service = 0;
                } else {
                    $bpa_not_allow_add_more_cart_service = 1;
                }
            }            
            wp_register_style( 'bookingpress-pro-cart-front', BOOKINGPRESS_CART_URL . '/css/bookingpress_pro_cart_front.css', array(), $bookingpress_cart_version );
            wp_enqueue_style( 'bookingpress-pro-cart-front' );

            wp_register_style( 'bookingpress-pro-cart-front-rtl', BOOKINGPRESS_CART_URL . '/css/bookingpress_pro_cart_front_rtl.css', array(), $bookingpress_cart_version );
            if (is_rtl() ) {
                wp_enqueue_style( 'bookingpress-pro-cart-front-rtl' );
            }

            include BOOKINGPRESS_CART_DIR.'/core/views/bookingpress_step_cart_content.php';
        }
        
        function bookingpress_add_front_css() {
            global $bookingpress_cart_version,$BookingPress;
            wp_register_style( 'bookingpress-pro-cart-front', BOOKINGPRESS_CART_URL . '/css/bookingpress_pro_cart_front.css', array(), $bookingpress_cart_version );
            wp_enqueue_style( 'bookingpress-pro-cart-front' );
            wp_register_style( 'bookingpress-pro-cart-front-rtl', BOOKINGPRESS_CART_URL . '/css/bookingpress_pro_cart_front_rtl.css', array(), $bookingpress_cart_version );
            if (is_rtl() ) {
                wp_enqueue_style( 'bookingpress-pro-cart-front-rtl' );
            }            
        }

        function bookingpress_dynamic_next_page_request_filter_func($bookingpress_dynamic_next_page_request_filter){
            $bookingpress_dynamic_next_page_request_filter .= 'const vm5 = this;';
            
            $bookingpress_dynamic_next_page_request_filter .= '
                
                if(vm5.bookingpress_current_tab == "cart" && "cart" == next_tab){
                    
                    vm5.bookingpress_cart_item_calculations();

                    vm5.bookingpress_refresh_cart_details();
                }

                if( typeof vm5.appointment_step_form_data.cart_item_edit_index != "undefined" && typeof vm5.appointment_step_form_data.cart_items[vm5.appointment_step_form_data.cart_item_edit_index] != "undefined" ){
                    vm5.appointment_step_form_data.selected_date = vm5.appointment_step_form_data.cart_items[vm5.appointment_step_form_data.cart_item_edit_index].bookingpress_selected_date;
                }

            ';
            return $bookingpress_dynamic_next_page_request_filter;
        }

        function bookingpress_add_frontend_vue_data_fields_func($bookingpress_front_vue_data_fields){
            $bookingpress_front_vue_data_fields['bookingpress_cart_addon'] = 1;
            $bookingpress_front_vue_data_fields['appointment_step_form_data']['cart_items'] = array();
            $bookingpress_front_vue_data_fields['appointment_step_form_data']['cart_item_edit_index'] = -1;
            $bookingpress_front_vue_data_fields['appointment_step_form_data']['bookingpress_cart_total'] = 0;
            $bookingpress_front_vue_data_fields['appointment_step_form_data']['is_enable_validations'] = 0;

            $bookingpress_front_vue_data_fields['bookingpress_cart_reset_staff'] = true;

            $bookingpress_front_vue_data_fields['isLoadCartLoader'] = 0;

            $bookingpress_front_vue_data_fields['cart_extra_items_modal'] = false;
            return $bookingpress_front_vue_data_fields;
        }

        function bookingpress_modify_front_booking_form_data_vars_func($bookingpress_front_vue_data_fields){
            global $BookingPress;
            if(!empty($bookingpress_front_vue_data_fields)){
                $bookingpress_sidebar_step_data = !empty($bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data']) ? $bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data'] : array();

                if(!empty($bookingpress_sidebar_step_data)){
                    $bookingpress_new_item_key = 'datetime';
                    $bookingpress_arr_keys = array_keys($bookingpress_sidebar_step_data);
                    $bookingpress_arr_vals = array_values($bookingpress_sidebar_step_data);

                    $bookingpress_insertafter = array_search($bookingpress_new_item_key, $bookingpress_arr_keys) + 1;
                    
                    $bookingpress_tmp_keys2 = array_splice($bookingpress_arr_keys, $bookingpress_insertafter);
                    $bookingpress_tmp_vals2 = array_splice($bookingpress_arr_vals, $bookingpress_insertafter);

                    $cart_title = $BookingPress->bookingpress_get_customize_settings('cart_title','booking_form');
                    $cart_title = !empty($cart_title) ? stripslashes_deep($cart_title) : '';

                    $bookingpress_arr_keys[] = "cart";
                    $bookingpress_arr_vals[] = array(
                        'tab_name' => $cart_title,
                        'tab_value' => 'cart',
                        'tab_icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 3c0 .55.45 1 1 1h1l3.6 7.59-1.35 2.44C4.52 15.37 5.48 17 7 17h11c.55 0 1-.45 1-1s-.45-1-1-1H7l1.1-2h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.37-.66-.11-1.48-.87-1.48H5.21l-.67-1.43c-.16-.35-.52-.57-.9-.57H2c-.55 0-1 .45-1 1zm16 15c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/></svg>',
                        'next_tab_name' => 'basic_details',
                        'previous_tab_name' => 'datetime',
                        'validate_fields' => array(),
                        'validation_msg' => array(),
                        'is_allow_navigate' => 0,
                        'is_display_step' => 1,
                        'sorting_key' => 'cart_selection',
                        'is_first_step' => 0
                    );

                    $bookingpress_new_modified_arr = array_merge(array_combine($bookingpress_arr_keys, $bookingpress_arr_vals), array_combine($bookingpress_tmp_keys2, $bookingpress_tmp_vals2));

                    if(!empty($bookingpress_new_modified_arr['datetime'])){
                        $bookingpress_new_modified_arr['datetime']['next_tab_name'] = 'cart';
                    }

                    if(!empty($bookingpress_new_modified_arr['basic_details'])){
                        $bookingpress_new_modified_arr['basic_details']['previous_tab_name'] = 'cart';
                    }

                    $bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data'] = $bookingpress_new_modified_arr;
                }
            }

            return $bookingpress_front_vue_data_fields;
        }

        public static function install(){
			global $bookingpress_cart_version, $wpdb, $BookingPress;

			$bookingpress_cart_addon_version = get_option('bookingpress_cart_module');
			if (!isset($bookingpress_cart_addon_version) || $bookingpress_cart_addon_version == '') {
		
		        $myaddon_name = "bookingpress-cart/bookingpress-cart.php";
		
                // activate license for this addon
                $posted_license_key = trim( get_option( 'bkp_license_key' ) );
			    $posted_license_package = '4860';

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
                    $message =  ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) ) ? $response->get_error_message() : __( 'An error occurred, please try again.','bookingpress-cart' );
                } else {
                    $license_data = json_decode( wp_remote_retrieve_body( $response ) );
                    $license_data_string = wp_remote_retrieve_body( $response );
                    if ( false === $license_data->success ) {
                        switch( $license_data->error ) {
                            case 'expired' :
                                $message = sprintf(
                                    __( 'Your license key expired on %s.','bookingpress-cart' ),
                                    date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
                                );
                                break;
                            case 'revoked' :
                                $message = __( 'Your license key has been disabled.','bookingpress-cart' );
                                break;
                            case 'missing' :
                                $message = __( 'Invalid license.','bookingpress-cart' );
                                break;
                            case 'invalid' :
                            case 'site_inactive' :
                                $message = __( 'Your license is not active for this URL.','bookingpress-cart' );
                                break;
                            case 'item_name_mismatch' :
                                $message = __('This appears to be an invalid license key for your selected package.','bookingpress-cart');
                                break;
                            case 'invalid_item_id' :
                                    $message = __('This appears to be an invalid license key for your selected package.','bookingpress-cart');
                                    break;
                            case 'no_activations_left':
                                $message = __( 'Your license key has reached its activation limit.','bookingpress-cart' );
                                break;
                            default :
                                $message = __( 'An error occurred, please try again.','bookingpress-cart' );
                                break;
                        }

                    }

                }

                if ( ! empty( $message ) ) {
                    update_option( 'bkp_cart_license_data_activate_response', $license_data_string );
                    update_option( 'bkp_cart_license_status', $license_data->license );
                    deactivate_plugins($myaddon_name, FALSE);
                    $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name);
                    $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Cart Add-on', 'bookingpress-cart');
					$bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-cart'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
					wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                    die;
                }
                
                if($license_data->license === "valid")
                {
                    update_option( 'bkp_cart_license_key', $posted_license_key );
                    update_option( 'bkp_cart_license_package', $posted_license_package );
                    update_option( 'bkp_cart_license_status', $license_data->license );
                    update_option( 'bkp_cart_license_data_activate_response', $license_data_string );
                }

				update_option('bookingpress_cart_module', $bookingpress_cart_version);
				$tbl_bookingpress_customize_settings = $wpdb->prefix . 'bookingpress_customize_settings';

                $booking_form = array(
                    'cart_title' => __('Cart Items','bookingpress-cart'),
                    'cart_heading_title' => __('My Cart Items','bookingpress-cart'),
                    'cart_item_title' => __('Items','bookingpress-cart'),
                    'cart_add_service_button_label' => __('Add Services','bookingpress-cart'),
                    'cart_total_amount_title' => __('Cart Total','bookingpress-cart'),
                    'cart_service_extra_title' => __('Extras', 'bookingpress-cart'),
                    'cart_service_extra_quantity_title' => __('Qty', 'bookingpress-cart'),
                    'cart_deposit_title' => '('.__('Deposit', 'bookingpress-cart').')',
                    'cart_number_person_title' => __('No. Of Person', 'bookingpress-cart'),
                    'cart_edit_item_title' => __('Edit', 'bookingpress-cart'),
                    'cart_remove_item_title' => __('Remove', 'bookingpress-cart'),
                    'cart_service_duration_title' => __('Duration', 'bookingpress-cart'),
                    'cart_staff_title' => __('Staff', 'bookingpress-cart'),                    
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
            }
		}

        public static function uninstall(){
            delete_option('bookingpress_cart_module');

            delete_option('bkp_cart_license_key');
            delete_option('bkp_cart_license_package');
            delete_option('bkp_cart_license_status');
            delete_option('bkp_cart_license_data_activate_response');

        }

        public function is_addon_activated(){
            $bookingpress_cart_addon_version = get_option('bookingpress_cart_module');
            return !empty($bookingpress_cart_addon_version) ? 1 : 0;
        }
    }

    global $bookingpress_cart;
	$bookingpress_cart = new bookingpress_cart;
}