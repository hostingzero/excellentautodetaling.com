<?php

if (!class_exists('bookingpress_stripe_payment')) {
	class bookingpress_stripe_payment {
        var $bookingpress_stripe_payment_mode;
        var $bookingpress_stripe_secret_key;
        var $bookingpress_stripe_publishable_key;
        var $bookingpress_stripe_payment_method;
        var $bookingpress_stripe_api_version;
        var $bookingpress_stripe_obj;
        var $bookingpress_stripe_popup_title;
        var $bookingpress_stripe_popup_payment_btn_label;

		function __construct() {
            if(is_plugin_active('bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php')){
                add_filter('bookingpress_stripe_submit_form_data', array($this, 'bookingpress_stripe_submit_form_data_func'), 10, 2);
                add_action('wp_ajax_bookingpress_confirm_sca_booking', array($this, 'bookingpress_confirm_sca_booking_func'), 10);
                add_action('wp_ajax_nopriv_bookingpress_confirm_sca_booking', array($this, 'bookingpress_confirm_sca_booking_func'), 10);
                add_filter('bookingpress_stripe_apply_refund', array($this, 'bookingpress_stripe_apply_refund_func'),10,2);

                /* Stripe Payment GateWay Submit Data */
                add_filter('bookingpress_package_order_stripe_submit_form_data', array($this, 'bookingpress_package_order_stripe_submit_form_data_func'), 10, 2);
				
				add_action('wp', array($this, 'bookingpress_StripeEventListener'), 10);
				/*Webhook related */
                add_action('wp_ajax_bookingpress_sca_booking_payment_intent_log', array($this, 'bookingpress_sca_booking_payment_intent_log_func'), 10);
                add_action('wp_ajax_nopriv_bookingpress_sca_booking_payment_intent_log', array($this, 'bookingpress_sca_booking_payment_intent_log_func'), 10);

                /* Stripe Payment GateWay Submit Data - Gift Card*/
                add_filter('bookingpress_gift_card_order_stripe_submit_form_data', array($this, 'bookingpress_gift_card_order_stripe_submit_form_data_func'), 10, 2);

            }    
        }

        /**
         * Function for stripe payment gateway gift card payment
         *
         * @param  mixed $response
         * @param  mixed $bookingpress_return_data
         * @return void
         */
		function bookingpress_gift_card_order_stripe_submit_form_data_func($response, $bookingpress_return_data){
            global $wpdb, $BookingPress, $bookingpress_pro_payment_gateways, $bookingpress_debug_payment_log_id;

            $this->bookingpress_init_stripe();

            if(!empty($bookingpress_return_data)){

                $entry_id                          = $bookingpress_return_data['entry_id'];
                $bookingpress_is_cart = !empty($bookingpress_return_data['is_cart']) ? 1 : 0;
                $currency_code                     = strtolower($bookingpress_return_data['currency_code']);
                $bookingpress_final_payable_amount = isset( $bookingpress_return_data['payable_amount'] ) ? $bookingpress_return_data['payable_amount'] : 0;
                $bookingpress_final_payable_amount = ((float)$bookingpress_final_payable_amount) * 100;
                $customer_details                  = $bookingpress_return_data['customer_details'];
                $customer_email                    = ! empty( $customer_details['customer_email'] ) ? $customer_details['customer_email'] : '';

                $bookingpress_service_name = ! empty( $bookingpress_return_data['selected_gift_card_details']['bookingpress_gift_card_title'] ) ? $bookingpress_return_data['selected_gift_card_details']['bookingpress_gift_card_title'] : __( 'Gift Card Purchase', 'bookingpress-stripe' );

                $custom_var = $entry_id;
                $bookingpress_notify_url = $bookingpress_return_data['notify_url'];
                $redirect_url = $bookingpress_return_data['approved_appointment_url'];
                
                $bookingpress_appointment_status = $BookingPress->bookingpress_get_settings( 'appointment_status', 'general_setting' );
                if ( $bookingpress_appointment_status == '2' ) {
                    $redirect_url = $bookingpress_return_data['pending_appointment_url'];
                }

                $booking_form_redirection_mode = !empty($bookingpress_return_data['booking_form_redirection_mode']) ? $bookingpress_return_data['booking_form_redirection_mode'] : 'external_redirection';

                if($this->bookingpress_stripe_payment_method == "built_in_form_fields"){

                    $bookingpress_card_number = !empty($bookingpress_return_data['card_details']['card_number']) ? $bookingpress_return_data['card_details']['card_number'] : '';
                    $bookingpress_expire_month = !empty($bookingpress_return_data['card_details']['expire_month']) ? $bookingpress_return_data['card_details']['expire_month'] : '';
                    $bookingpress_expire_year = !empty($bookingpress_return_data['card_details']['expire_year']) ? $bookingpress_return_data['card_details']['expire_year'] : '';
                    $bookingpress_cvv = !empty($bookingpress_return_data['card_details']['cvv']) ? $bookingpress_return_data['card_details']['cvv'] : '';

                    if(empty($bookingpress_card_number) || empty($bookingpress_expire_month) || empty($bookingpress_expire_year) || empty($bookingpress_cvv)){
                        $response['variant']       = 'error';
                        $response['title']         = esc_html__( 'Error', 'bookingpress-stripe' );
                        $response['msg']           = esc_html__( 'Please fill all card fields value', 'bookingpress-stripe' );
                        $response['is_redirect']   = 0;
                        $response['redirect_data'] = '';
                        $response['is_spam']       = 0;
                    }else{
                        try{
                            $bookingpress_stripe_charge_details = array();
                            //Create card token
                            $bookingpress_card_token_res = $this->bookingpress_stripe_obj->tokens->create(
                                array(
                                    'card' => array(
                                        'number' => $bookingpress_card_number,
                                        'exp_month' => $bookingpress_expire_month,
                                        'exp_year' => $bookingpress_expire_year,
                                        'cvc' => $bookingpress_cvv
                                    ),
                                )
                            );

                            do_action( 'bookingpress_payment_log_entry', 'stripe', 'Stripe card token res', 'bookingpress pro', $bookingpress_card_token_res, $bookingpress_debug_payment_log_id );

                            $bookingpress_created_card_token = !empty($bookingpress_card_token_res->id) ? $bookingpress_card_token_res->id : '';

                            do_action( 'bookingpress_payment_log_entry', 'stripe', 'Stripe card created token', 'bookingpress pro', $bookingpress_created_card_token, $bookingpress_debug_payment_log_id );

                            if(!empty($bookingpress_created_card_token)){
                                $bookingpress_stripe_charge_details['card_token'] = $bookingpress_created_card_token;

                                $bookingpress_created_customer_id = $this->bookingpress_stripe_create_customer($customer_email, $bookingpress_created_card_token);

                                do_action( 'bookingpress_payment_log_entry', 'stripe', 'Stripe card token customer id', 'bookingpress pro', $bookingpress_created_customer_id, $bookingpress_debug_payment_log_id );
                                
                                if(!empty($bookingpress_created_customer_id)){
                                    $bookingpress_stripe_charge_details['customer'] = $bookingpress_created_customer_id;
                                }else{
                                    $bookingpress_customer_err_msg = esc_html__('Stripe Error', 'bookingpress-stripe').": ";
                                    $bookingpress_customer_err_msg .= esc_html__('Something went wrong while creating customer with stripe.', 'bookingpress-stripe')." ";

                                    $response['variant']       = 'error';
                                    $response['title']         = esc_html__( 'Error', 'bookingpress-stripe' );
                                    $response['msg']           = $bookingpress_customer_err_msg;
                                    $response['is_redirect']   = 0;
                                    $response['redirect_data'] = '';
                                    $response['is_spam']       = 0;    
                                }
                            }else{
                                $bookingpress_card_err_msg = esc_html__('Stripe Error', 'bookingpress-stripe').": ";
                                $bookingpress_card_err_msg .= esc_html__('Something went wrong while pay with stripe using provided card details.', 'bookingpress-stripe')." ";

                                $response['variant']       = 'error';
                                $response['title']         = esc_html__( 'Error', 'bookingpress-stripe' );
                                $response['msg']           = $bookingpress_card_err_msg;
                                $response['is_redirect']   = 0;
                                $response['redirect_data'] = '';
                                $response['is_spam']       = 0;
                            }

                            if(!empty($bookingpress_stripe_charge_details)){
                                $bookingpress_stripe_charge_res = $this->bookingpress_stripe_obj->charges->create(
                                    array(
                                        'amount' => $bookingpress_final_payable_amount,
                                        'currency' => $currency_code,
                                        'customer' => $bookingpress_created_customer_id,
                                        'description' => $bookingpress_service_name,
                                        'metadata' => array('custom' => $custom_var),
                                    )
                                );

                                do_action( 'bookingpress_payment_log_entry', 'stripe', 'Stripe card charge response', 'bookingpress pro', $bookingpress_stripe_charge_res, $bookingpress_debug_payment_log_id );

                                if(!empty($bookingpress_stripe_charge_res->status) && ($bookingpress_stripe_charge_res->status == "succeeded")){
                                    $bookingpress_transaction_id = $bookingpress_stripe_charge_res->id;
                                    $bookingpress_payment_data = json_decode(json_encode($bookingpress_stripe_charge_res), TRUE);
                                    $bookingpress_pro_payment_gateways->bookingpress_confirm_booking( $entry_id, $bookingpress_payment_data, '1', 'id', 'amount',1, $bookingpress_is_cart, 'currency' );

                                    $response['variant'] = 'redirect_url';
                                    $response['title']         = '';
                                    $response['msg']           = '';
                                    $response['is_redirect']   = 1;
                                    $response['redirect_data'] = $redirect_url;
                                    if($booking_form_redirection_mode == "in-built"){
                                        $response['is_transaction_completed'] = 1;
                                    }
                                    $response['entry_id'] = $entry_id;
                                }
                            }                           
                        }
                        catch(Exception $e){
                            $bookingpress_stripe_err_msg_obj = $e->getJsonBody();
                            $bookingpress_stripe_err_msg = $bookingpress_stripe_err_msg_obj['error']['message'];
                            $response['variant']       = 'error';
                            $response['title']         = esc_html__( 'Error', 'bookingpress-stripe' );
                            $response['msg']           = $bookingpress_stripe_err_msg;
                            $response['is_redirect']   = 0;
                            $response['redirect_data'] = '';
                            $response['is_spam']       = 0;
                        }
                    }


                }
                else if($this->bookingpress_stripe_payment_method == "sca_popup"){

                    $bookingpress_created_customer_id = $this->bookingpress_stripe_create_customer($customer_email);

                    do_action( 'bookingpress_payment_log_entry', 'stripe', 'Stripe sca customer id', 'bookingpress pro', $bookingpress_created_customer_id, $bookingpress_debug_payment_log_id );

                    if(empty($bookingpress_created_customer_id)){
                        $bookingpress_customer_err_msg = esc_html__('Stripe Error', 'bookingpress-stripe').": ";
                        $bookingpress_customer_err_msg .= esc_html__('Something went wrong while creating customer with stripe.', 'bookingpress-stripe')." ";

                        $response['variant']       = 'error';
                        $response['title']         = esc_html__( 'Error', 'bookingpress-stripe' );
                        $response['msg']           = $bookingpress_customer_err_msg;
                        $response['is_redirect']   = 0;
                        $response['redirect_data'] = '';
                        $response['is_spam']       = 0;    
                    }

                    else{
                        //Create a payment intent at stripe.
                        $bookingpress_stripe_payment_intent_arr = array(
                            'amount' => $bookingpress_final_payable_amount,
                            'currency' => $currency_code,
                            'customer' => $bookingpress_created_customer_id,
                            'description' => $bookingpress_service_name,
                            'automatic_payment_methods' => array(
                                'enabled' => true,
                                'allow_redirects' => 'never',
                            ),   
							'metadata' => array('custom' => $custom_var),
                        );

                        $bookingpress_created_payment_intent_res = $this->bookingpress_stripe_obj->paymentIntents->create($bookingpress_stripe_payment_intent_arr);
                        do_action( 'bookingpress_payment_log_entry', 'stripe', 'Stripe sca created payment res', 'bookingpress pro', $bookingpress_created_payment_intent_res, $bookingpress_debug_payment_log_id );

                        $bookingpress_payment_intent_client_secret = !empty($bookingpress_created_payment_intent_res->client_secret) ? $bookingpress_created_payment_intent_res->client_secret : '';
                        if(empty($bookingpress_payment_intent_client_secret)){
                            $response['variant']       = 'error';
                            $response['title']         = esc_html__( 'Error', 'bookingpress-stripe' );
                            $response['msg']           = esc_html__( 'Something went wrong while creating payment intent', 'bookingpress-stripe' );
                            $response['is_redirect']   = 0;
                            $response['redirect_data'] = '';
                            $response['is_spam']       = 0;        
                        }
                        else {
                            
                            $bookingpress_stripe_form = $this->bookingpress_get_stripe_form($bookingpress_final_payable_amount, $bookingpress_return_data,$bookingpress_service_name);

                            $bookingpress_redirect_data = $bookingpress_stripe_form;
                            $bookingpress_redirect_data .= '<script type="text/javascript" id="bookingpress_stripe_js">';
                            $bookingpress_redirect_data .= 'var stripe = Stripe("' . $this->bookingpress_stripe_publishable_key .'");';
                            $bookingpress_redirect_data .= 'var elements = stripe.elements({fonts: [{cssSrc: "https://fonts.googleapis.com/css?family=Source+Code+Pro"}],locale: window.__exampleLocale});';

                            $bookingpress_redirect_data .= "var elementStyles = { base: { color: '#32325D', fontWeight: 500, fontFamily: 'Source Code Pro, Consolas, Menlo, monospace', fontSize: '16px', fontSmoothing: 'antialiased', '::placeholder': { color: '#CFD7DF', }, ':-webkit-autofill': { color: '#e39f48',},},invalid: {color: '#E25950','::placeholder': {color: '#FFCCA5',},},};";

                            $bookingpress_redirect_data .= "var elementClasses = { focus: 'focused', empty: 'empty', invalid: 'invalid', };";

                            $bookingpress_redirect_data .= " var cardNumber = elements.create('cardNumber', { style: elementStyles, classes: elementClasses, }); cardNumber.mount('#card-number');";
                            $bookingpress_redirect_data .= " var cardExpiry = elements.create('cardExpiry', { style: elementStyles, classes: elementClasses, }); cardExpiry.mount('#card-expiry');";
                            $bookingpress_redirect_data .= " var cardCvc = elements.create('cardCvc', { style: elementStyles, classes: elementClasses, }); cardCvc.mount('#card-cvc');";

                            $bookingpress_redirect_data .= 'var cardButton = document.getElementById("card-button"); var clientSecret = cardButton.dataset.secret;';

                            $bookingpress_redirect_data .= 'var closeIcon = document.getElementById("stripe_wrapper_close_icon");';

                            $bookingpress_redirect_data .= 'cardButton.addEventListener("click", function(e) {
                                cardButton.setAttribute("disabled","disabled");
                                cardButton.style.cursor = "not-allowed";
                                stripe.confirmCardPayment(
                                    "'.$bookingpress_payment_intent_client_secret.'",
                                    {
                                        payment_method:{ card: cardNumber },
                                        setup_future_usage: "off_session"
                                    }
                                ).then(function(result) {
                                    if (result.error) {
                                        cardButton.removeAttribute("disabled");
                                        cardButton.style.cursor = "";
                                        var errorElement = document.getElementById("card-errors");
                                        errorElement.textContent = result.error.message;
                                    } else {
                                        var errorElement = document.getElementById("card-errors");
                                        errorElement.textContent = "";
                                        if(result.paymentIntent.status == "succeeded"){
                                            var sca_confirm_booking_data = { action: "bookingpress_confirm_sca_booking", bookingpress_payment_res: result, _wpnonce: "'.wp_create_nonce( 'bpa_wp_nonce' ).'", bookingpress_entry_id: "'.$entry_id.'", is_cart_payment: "'.$bookingpress_is_cart.'" }
                                            axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( sca_confirm_booking_data ) )
				                            .then(function(response) {
                                                if(response.data.variant != "error") {
                                                    var bookingpress_redirection_mode = "'.$booking_form_redirection_mode.'";
                                                    if(bookingpress_redirection_mode == "in-built"){
                                                        console.log("inside if..")
                                                        var bookingpress_uniq_id = window.app.gift_card_step_form_data.bookingpress_uniq_id;
                                                        //document.getElementById("bookingpress_booking_form_"+bookingpress_uniq_id).style.display = "none";
                                                        document.getElementById("stripe_element_wrapper").remove();
                                                        if(response.data.variant != "error"){
                                                            window.app.bookingpress_render_gift_card_thankyou_content();
                                                        }else{
                                                            window.app.bookingpress_render_gift_card_payment_error_content();
                                                        }

                                                    }else{
                                                        console.log("inside else..")
                                                        window.location.href = "'.$redirect_url.'";
                                                    }
                                                } else {
                                                    window.app.bookingpress_set_error_msg(response.data.msg);
                                                }
                                            }).catch(function(error){
                                                console.log(error);
                                            });
                                        }
										else {
                                            var sca_confirm_booking_data = { action: "bookingpress_sca_booking_payment_intent_log", bookingpress_payment_res: result, _wpnonce: "'.wp_create_nonce( 'bpa_wp_nonce' ).'", bookingpress_entry_id: "'.$entry_id.'", is_cart_payment: "'.$bookingpress_is_cart.'" }
                                            axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( sca_confirm_booking_data ) )
				                            .then(function(response) {
                                                window.app.bookingpress_set_error_msg(response.data.msg);
                                            }).catch(function(error){
                                                console.log(error);
                                            });
                                        }
                                    }
                                });
                            });';

                            $bookingpress_redirect_data .= '</script>';

                            $bookingpress_return_data = $bookingpress_redirect_data;

                            $response['variant']       = 'redirect';
                            $response['title']         = '';
                            $response['msg']           = '';
                            $response['is_redirect']   = 1;
                            $response['redirect_data'] = $bookingpress_return_data;
                            $response['entry_id'] = $entry_id;                           
                        }
                    }

                }
                else{
                    $response['variant']       = 'error';
                    $response['title']         = esc_html__( 'Error', 'bookingpress-stripe' );
                    $response['msg']           = esc_html__( 'Something went wrong while payment with stripe', 'bookingpress-stripe' );
                    $response['is_redirect']   = 0;
                    $response['redirect_data'] = '';
                    $response['is_spam']       = 0;
                }

            }
            return $response;
        }

        function bookingpress_sca_booking_payment_intent_log_func(){

            global $bookingpress_debug_payment_log_id;
            $wpnonce               = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			$response              = array();
            
            do_action( 'bookingpress_payment_log_entry', 'stripe', 'Stripe sca Payment Intent Not successed', 'bookingpress pro', $_POST, $bookingpress_debug_payment_log_id );
            if ( ! $bpa_verify_nonce_flag ) {
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-stripe' );
				$response['msg']     = esc_html__( 'Sorry, Your request can not process due to security reason.', 'bookingpress-stripe' );
				wp_send_json( $response );
				die();
			}else{
                $response['variant'] = 'error';
                $response['title']   = esc_html__( 'Error', 'bookingpress-stripe' );
                $response['msg']     = esc_html__( 'Sorry, payment is not successed with the stripe.', 'bookingpress-stripe' );
            }
            echo wp_json_encode($response);
            exit;
        }

		function bookingpress_StripeEventListener() {
			  if (isset($_REQUEST['bookingpress-listener']) && in_array($_REQUEST['bookingpress-listener'], array('bpa_pro_stripe_url'))) {
				  
				  global $bookingpress_debug_payment_log_id, $bookingpress_pro_payment_gateways, $tbl_bookingpress_appointment_bookings, $wpdb, $tbl_bookingpress_payment_logs, $BookingPress;
				  
				  do_action( 'bookingpress_payment_log_entry', 'stripe', 'Stripe Webhook data', 'bookingpress pro', $_REQUEST, $bookingpress_debug_payment_log_id );
				  $body = @file_get_contents('php://input');
					/* Grab the event information */
				  $event_json = json_decode($body);

				  do_action('bookingpress_payment_log_entry', 'stripe', 'webhook requested data', 'bookingpress pro', $event_json, $bookingpress_debug_payment_log_id);
				  
				  $event_id = !empty($event_json->id) ? $event_json->id : '';
				  do_action('bookingpress_payment_log_entry', 'stripe', 'Event id ', 'bookingpress pro', $event_id, $bookingpress_debug_payment_log_id);
				  if (!empty($event_id)) {
					   try {
						   	$this->bookingpress_init_stripe();
						   	$event = $this->bookingpress_stripe_obj->events->retrieve($event_id);
                        	$invoice = $event->data->object;
                        	do_action('bookingpress_payment_log_entry', 'stripe', 'webhook generated invoice data', 'bookingpress pro', $invoice, $bookingpress_debug_payment_log_id);
                        	$customs = !empty($invoice->metadata->custom) ? explode('|', $invoice->metadata->custom) : array();
                        	$entry_id = isset($customs[0]) ? $customs[0] : '' ;
							$bp_is_cart = isset($customs[1]) ? $customs[1] : '0' ;
                        	//$invoice = array_map( array( $BookingPress, 'appointment_sanatize_field' ), $invoice );
						    $payment_intent_id = !empty($invoice->payment_intent) ? $invoice->payment_intent : '';
						    $log_array = array(
                                'entry_id' => $entry_id,
                                'bp_is_cart' => $bp_is_cart,
                                'payment_intent_id' => $payment_intent_id,
                                'event_type' => $event->type
                            );
						    do_action('bookingpress_payment_log_entry', 'stripe', 'Webhook Log array ', 'bookingpress pro', $log_array , $bookingpress_debug_payment_log_id);
						   	if(!empty($entry_id) && !empty($payment_intent_id)){
                                $payment_log_details = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_transaction_id FROM {$tbl_bookingpress_payment_logs} WHERE  bookingpress_transaction_id = %s", $payment_intent_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm

                                if(empty($payment_log_details)){						   
                                    switch ($event->type) {
                                        case 'charge.succeeded':
                                            $bookingpress_pro_payment_gateways->bookingpress_confirm_booking( $entry_id, $invoice, '1', 'payment_intent', 'amount', 1, $bp_is_cart, 'currency' );
                                            do_action('bookingpress_payment_log_entry', 'stripe', 'Payment Successed - Webhook ', 'bookingpress pro', 'Entry id :'.$entry_id  , $bookingpress_debug_payment_log_id);                                            
                                        break;
                                    }
                                }
                            }
					   }
					  catch (Exception $e) {
                     	  do_action('bookingpress_payment_log_entry', 'stripe', 'error in webhook data verification', 'bookingpress pro', $e->getMessage(), $bookingpress_debug_payment_log_id);
                     }   
				  }
				  
			  }
		 }
        
        /**
         * Function for stripe payment gateway package payment
         *
         * @param  mixed $response
         * @param  mixed $bookingpress_return_data
         * @return void
         */
        function bookingpress_package_order_stripe_submit_form_data_func($response, $bookingpress_return_data){
            global $wpdb, $BookingPress, $bookingpress_pro_payment_gateways, $bookingpress_debug_payment_log_id;

            $this->bookingpress_init_stripe();

            if(!empty($bookingpress_return_data)){
                $entry_id                          = $bookingpress_return_data['entry_id'];
                $bookingpress_is_cart = !empty($bookingpress_return_data['is_cart']) ? 1 : 0;
                $currency_code                     = strtolower($bookingpress_return_data['currency_code']);
                $bookingpress_final_payable_amount = isset( $bookingpress_return_data['payable_amount'] ) ? $bookingpress_return_data['payable_amount'] : 0;
                $bookingpress_final_payable_amount = ((float)$bookingpress_final_payable_amount) * 100;
                $customer_details                  = $bookingpress_return_data['customer_details'];
                $customer_email                    = ! empty( $customer_details['customer_email'] ) ? $customer_details['customer_email'] : '';

                $bookingpress_service_name = ! empty( $bookingpress_return_data['selected_package_details']['bookingpress_package_name'] ) ? $bookingpress_return_data['selected_package_details']['bookingpress_package_name'] : __( 'Package Booking', 'bookingpress-stripe' );

                $custom_var = $entry_id;
                $bookingpress_notify_url = $bookingpress_return_data['notify_url'];
                $redirect_url = $bookingpress_return_data['approved_appointment_url'];
                
                $bookingpress_appointment_status = $BookingPress->bookingpress_get_settings( 'appointment_status', 'general_setting' );
                if ( $bookingpress_appointment_status == '2' ) {
                    $redirect_url = $bookingpress_return_data['pending_appointment_url'];
                }

                $booking_form_redirection_mode = !empty($bookingpress_return_data['booking_form_redirection_mode']) ? $bookingpress_return_data['booking_form_redirection_mode'] : 'external_redirection';

                if($this->bookingpress_stripe_payment_method == "built_in_form_fields"){

                    $bookingpress_card_number = !empty($bookingpress_return_data['card_details']['card_number']) ? $bookingpress_return_data['card_details']['card_number'] : '';
                    $bookingpress_expire_month = !empty($bookingpress_return_data['card_details']['expire_month']) ? $bookingpress_return_data['card_details']['expire_month'] : '';
                    $bookingpress_expire_year = !empty($bookingpress_return_data['card_details']['expire_year']) ? $bookingpress_return_data['card_details']['expire_year'] : '';
                    $bookingpress_cvv = !empty($bookingpress_return_data['card_details']['cvv']) ? $bookingpress_return_data['card_details']['cvv'] : '';

                    if(empty($bookingpress_card_number) || empty($bookingpress_expire_month) || empty($bookingpress_expire_year) || empty($bookingpress_cvv)){
                        $response['variant']       = 'error';
                        $response['title']         = esc_html__( 'Error', 'bookingpress-stripe' );
                        $response['msg']           = esc_html__( 'Please fill all card fields value', 'bookingpress-stripe' );
                        $response['is_redirect']   = 0;
                        $response['redirect_data'] = '';
                        $response['is_spam']       = 0;
                    }else{
                        try{
                            $bookingpress_stripe_charge_details = array();

                            //Create card token
                            $bookingpress_card_token_res = $this->bookingpress_stripe_obj->tokens->create(
                                array(
                                    'card' => array(
                                        'number' => $bookingpress_card_number,
                                        'exp_month' => $bookingpress_expire_month,
                                        'exp_year' => $bookingpress_expire_year,
                                        'cvc' => $bookingpress_cvv
                                    ),
                                )
                            );

                            do_action( 'bookingpress_payment_log_entry', 'stripe', 'Stripe card token res', 'bookingpress pro', $bookingpress_card_token_res, $bookingpress_debug_payment_log_id );

                            $bookingpress_created_card_token = !empty($bookingpress_card_token_res->id) ? $bookingpress_card_token_res->id : '';

                            do_action( 'bookingpress_payment_log_entry', 'stripe', 'Stripe card created token', 'bookingpress pro', $bookingpress_created_card_token, $bookingpress_debug_payment_log_id );

                            if(!empty($bookingpress_created_card_token)){
                                $bookingpress_stripe_charge_details['card_token'] = $bookingpress_created_card_token;

                                $bookingpress_created_customer_id = $this->bookingpress_stripe_create_customer($customer_email, $bookingpress_created_card_token, $entry_id, true);

                                do_action( 'bookingpress_payment_log_entry', 'stripe', 'Stripe card token customer id', 'bookingpress pro', $bookingpress_created_customer_id, $bookingpress_debug_payment_log_id );
                                
                                if(!empty($bookingpress_created_customer_id)){
                                    $bookingpress_stripe_charge_details['customer'] = $bookingpress_created_customer_id;
                                }else{
                                    $bookingpress_customer_err_msg = esc_html__('Stripe Error', 'bookingpress-stripe').": ";
                                    $bookingpress_customer_err_msg .= esc_html__('Something went wrong while creating customer with stripe.', 'bookingpress-stripe')." ";

                                    $response['variant']       = 'error';
                                    $response['title']         = esc_html__( 'Error', 'bookingpress-stripe' );
                                    $response['msg']           = $bookingpress_customer_err_msg;
                                    $response['is_redirect']   = 0;
                                    $response['redirect_data'] = '';
                                    $response['is_spam']       = 0;    
                                }
                            }else{
                                $bookingpress_card_err_msg = esc_html__('Stripe Error', 'bookingpress-stripe').": ";
                                $bookingpress_card_err_msg .= esc_html__('Something went wrong while pay with stripe using provided card details.', 'bookingpress-stripe')." ";

                                $response['variant']       = 'error';
                                $response['title']         = esc_html__( 'Error', 'bookingpress-stripe' );
                                $response['msg']           = $bookingpress_card_err_msg;
                                $response['is_redirect']   = 0;
                                $response['redirect_data'] = '';
                                $response['is_spam']       = 0;
                            }

                            if(!empty($bookingpress_stripe_charge_details)){
                                $bookingpress_stripe_charge_res = $this->bookingpress_stripe_obj->charges->create(
                                    array(
                                        'amount' => $bookingpress_final_payable_amount,
                                        'currency' => $currency_code,
                                        'customer' => $bookingpress_created_customer_id,
                                        'description' => $bookingpress_service_name,
                                        'metadata' => array('custom' => $custom_var),
                                    )
                                );

                                do_action( 'bookingpress_payment_log_entry', 'stripe', 'Stripe card charge response', 'bookingpress pro', $bookingpress_stripe_charge_res, $bookingpress_debug_payment_log_id );

                                if(!empty($bookingpress_stripe_charge_res->status) && ($bookingpress_stripe_charge_res->status == "succeeded")){
                                    $bookingpress_transaction_id = $bookingpress_stripe_charge_res->id;
                                    $bookingpress_payment_data = json_decode(json_encode($bookingpress_stripe_charge_res), TRUE);
                                    $bookingpress_pro_payment_gateways->bookingpress_confirm_booking( $entry_id, $bookingpress_payment_data, '1', 'id', 'amount',1, $bookingpress_is_cart, 'currency' );

                                    $response['variant'] = 'redirect_url';
                                    $response['title']         = '';
                                    $response['msg']           = '';
                                    $response['is_redirect']   = 1;
                                    $response['redirect_data'] = $redirect_url;
                                    if($booking_form_redirection_mode == "in-built"){
                                        $response['is_transaction_completed'] = 1;
                                    }
                                    $response['entry_id'] = $entry_id;
                                }
                            }
                        }catch(Exception $e){
                            $bookingpress_stripe_err_msg_obj = $e->getJsonBody();
                            $bookingpress_stripe_err_msg = $bookingpress_stripe_err_msg_obj['error']['message'];
                            $response['variant']       = 'error';
                            $response['title']         = esc_html__( 'Error', 'bookingpress-stripe' );
                            $response['msg']           = $bookingpress_stripe_err_msg;
                            $response['is_redirect']   = 0;
                            $response['redirect_data'] = '';
                            $response['is_spam']       = 0;
                        }
                    }
                }else if($this->bookingpress_stripe_payment_method == "sca_popup"){
                    //Create SCA customer
                    $bookingpress_created_customer_id = $this->bookingpress_stripe_create_customer($customer_email,'',$entry_id, true);

                    do_action( 'bookingpress_payment_log_entry', 'stripe', 'Stripe sca customer id', 'bookingpress pro', $bookingpress_created_customer_id, $bookingpress_debug_payment_log_id );

                    if(empty($bookingpress_created_customer_id)){
                        $bookingpress_customer_err_msg = esc_html__('Stripe Error', 'bookingpress-stripe').": ";
                        $bookingpress_customer_err_msg .= esc_html__('Something went wrong while creating customer with stripe.', 'bookingpress-stripe')." ";

                        $response['variant']       = 'error';
                        $response['title']         = esc_html__( 'Error', 'bookingpress-stripe' );
                        $response['msg']           = $bookingpress_customer_err_msg;
                        $response['is_redirect']   = 0;
                        $response['redirect_data'] = '';
                        $response['is_spam']       = 0;    
                    }else{
                        //Create a payment intent at stripe.

                        $bookingpress_stripe_payment_intent_arr = array(
                            'amount' => $bookingpress_final_payable_amount,
                            'currency' => $currency_code,
                            'customer' => $bookingpress_created_customer_id,
                            'description' => $bookingpress_service_name,
                            'automatic_payment_methods' => array(
                                'enabled' => true,
                                'allow_redirects' => 'never',
                            ),   
							'metadata' => array('custom' => $custom_var),
                        );
                        $bookingpress_created_payment_intent_res = $this->bookingpress_stripe_obj->paymentIntents->create($bookingpress_stripe_payment_intent_arr);
                        do_action( 'bookingpress_payment_log_entry', 'stripe', 'Stripe sca created payment res', 'bookingpress pro', $bookingpress_created_payment_intent_res, $bookingpress_debug_payment_log_id );

                        $bookingpress_payment_intent_client_secret = !empty($bookingpress_created_payment_intent_res->client_secret) ? $bookingpress_created_payment_intent_res->client_secret : '';
                        if(empty($bookingpress_payment_intent_client_secret)){
                            $response['variant']       = 'error';
                            $response['title']         = esc_html__( 'Error', 'bookingpress-stripe' );
                            $response['msg']           = esc_html__( 'Something went wrong while creating payment intent', 'bookingpress-stripe' );
                            $response['is_redirect']   = 0;
                            $response['redirect_data'] = '';
                            $response['is_spam']       = 0;        
                        }else{
                            $bookingpress_stripe_form = $this->bookingpress_get_stripe_form($bookingpress_final_payable_amount, $bookingpress_return_data,$bookingpress_service_name);

                            $bookingpress_redirect_data = $bookingpress_stripe_form;
                            $bookingpress_redirect_data .= '<script type="text/javascript" id="bookingpress_stripe_js">';
                            $bookingpress_redirect_data .= 'var stripe = Stripe("' . $this->bookingpress_stripe_publishable_key .'");';
                            $bookingpress_redirect_data .= 'var elements = stripe.elements({fonts: [{cssSrc: "https://fonts.googleapis.com/css?family=Source+Code+Pro"}],locale: window.__exampleLocale});';

                            $bookingpress_redirect_data .= "var elementStyles = { base: { color: '#32325D', fontWeight: 500, fontFamily: 'Source Code Pro, Consolas, Menlo, monospace', fontSize: '16px', fontSmoothing: 'antialiased', '::placeholder': { color: '#CFD7DF', }, ':-webkit-autofill': { color: '#e39f48',},},invalid: {color: '#E25950','::placeholder': {color: '#FFCCA5',},},};";

                            $bookingpress_redirect_data .= "var elementClasses = { focus: 'focused', empty: 'empty', invalid: 'invalid', };";

                            $bookingpress_redirect_data .= " var cardNumber = elements.create('cardNumber', { style: elementStyles, classes: elementClasses, }); cardNumber.mount('#card-number');";
                            $bookingpress_redirect_data .= " var cardExpiry = elements.create('cardExpiry', { style: elementStyles, classes: elementClasses, }); cardExpiry.mount('#card-expiry');";
                            $bookingpress_redirect_data .= " var cardCvc = elements.create('cardCvc', { style: elementStyles, classes: elementClasses, }); cardCvc.mount('#card-cvc');";

                            $bookingpress_redirect_data .= 'var cardButton = document.getElementById("card-button"); var clientSecret = cardButton.dataset.secret;';

                            $bookingpress_redirect_data .= 'var closeIcon = document.getElementById("stripe_wrapper_close_icon");';

                            /*$bookingpress_redirect_data .= 'closeIcon.addEventListener("click", function(e){
                                document.getElementById("stripe_element_wrapper").remove();
                                document.getElementById("bookingpress_stripe_js").remove();
                                document.getElementById("bookingpress_stripe_css").remove();
                            });';*/

                            $bookingpress_redirect_data .= 'cardButton.addEventListener("click", function(e) {
                                cardButton.setAttribute("disabled","disabled");
                                cardButton.style.cursor = "not-allowed";
                                stripe.confirmCardPayment(
                                    "'.$bookingpress_payment_intent_client_secret.'",
                                    {
                                        payment_method:{ card: cardNumber },
                                        setup_future_usage: "off_session"
                                    }
                                ).then(function(result) {
                                    if (result.error) {
                                        cardButton.removeAttribute("disabled");
                                        cardButton.style.cursor = "";
                                        var errorElement = document.getElementById("card-errors");
                                        errorElement.textContent = result.error.message;
                                    } else {
                                        var errorElement = document.getElementById("card-errors");
                                        errorElement.textContent = "";
                                        if(result.paymentIntent.status == "succeeded"){
                                            var sca_confirm_booking_data = { action: "bookingpress_confirm_sca_booking", bookingpress_payment_res: result, _wpnonce: "'.wp_create_nonce( 'bpa_wp_nonce' ).'", bookingpress_entry_id: "'.$entry_id.'", is_cart_payment: "'.$bookingpress_is_cart.'" }
                                            axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( sca_confirm_booking_data ) )
				                            .then(function(response) {
                                                if(response.data.variant != "error") {
                                                    var bookingpress_redirection_mode = "'.$booking_form_redirection_mode.'";
                                                    if(bookingpress_redirection_mode == "in-built"){

                                                        var bookingpress_uniq_id = window.app.package_step_form_data.bookingpress_uniq_id;
                                                        //document.getElementById("bookingpress_booking_form_"+bookingpress_uniq_id).style.display = "none";
                                                        document.getElementById("stripe_element_wrapper").remove();
                                                        if(response.data.variant != "error"){
                                                            window.app.bookingpress_render_package_thankyou_content();
                                                        }else{
                                                            window.app.bookingpress_render_package_payment_error_content();
                                                        }

                                                    }else{
                                                        window.location.href = "'.$redirect_url.'";
                                                    }
                                                } else {
                                                    window.app.bookingpress_set_error_msg(response.data.msg);
                                                }
                                            }).catch(function(error){
                                                console.log(error);
                                            });
                                        }
										else {
                                            var sca_confirm_booking_data = { action: "bookingpress_sca_booking_payment_intent_log", bookingpress_payment_res: result, _wpnonce: "'.wp_create_nonce( 'bpa_wp_nonce' ).'", bookingpress_entry_id: "'.$entry_id.'", is_cart_payment: "'.$bookingpress_is_cart.'" }
                                            axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( sca_confirm_booking_data ) )
				                            .then(function(response) {
                                                window.app.bookingpress_set_error_msg(response.data.msg);
                                            }).catch(function(error){
                                                console.log(error);
                                            });
                                        }
                                    }
                                });
                            });';

                            $bookingpress_redirect_data .= '</script>';

                            $bookingpress_return_data = $bookingpress_redirect_data;

                            $response['variant']       = 'redirect';
                            $response['title']         = '';
                            $response['msg']           = '';
                            $response['is_redirect']   = 1;
                            $response['redirect_data'] = $bookingpress_return_data;
                            $response['entry_id'] = $entry_id;
                        }
                    }
                }else{
                    $response['variant']       = 'error';
                    $response['title']         = esc_html__( 'Error', 'bookingpress-stripe' );
                    $response['msg']           = esc_html__( 'Something went wrong while payment with stripe', 'bookingpress-stripe' );
                    $response['is_redirect']   = 0;
                    $response['redirect_data'] = '';
                    $response['is_spam']       = 0;
                }
            }
            return $response;
        }


        function bookingpress_confirm_sca_booking_func(){
            global $wpdb, $BookingPress, $bookingpress_pro_payment_gateways, $bookingpress_debug_payment_log_id;
            $wpnonce               = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			$response              = array();
            
            do_action( 'bookingpress_payment_log_entry', 'stripe', 'Stripe sca confirm booking posted data', 'bookingpress pro', $_POST, $bookingpress_debug_payment_log_id );

			if ( ! $bpa_verify_nonce_flag ) {
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-stripe' );
				$response['msg']     = esc_html__( 'Sorry, Your request can not process due to security reason.', 'bookingpress-stripe' );
				wp_send_json( $response );
				die();
			}else{
                if(!empty($_POST['bookingpress_payment_res']['paymentIntent']) && !empty($_POST['bookingpress_payment_res']['paymentIntent']['status']) && ($_POST['bookingpress_payment_res']['paymentIntent']['status'] == "succeeded")){
                    $entry_id = !empty($_POST['bookingpress_entry_id']) ? intval($_POST['bookingpress_entry_id']) : 0;
                    $bookingpress_is_cart = !empty($_POST['is_cart_payment']) ? intval($_POST['is_cart_payment']) : 0;
                    $bookingpress_payment_data = $_POST['bookingpress_payment_res']['paymentIntent']; //phpcs:ignore
                    $bookingpress_pro_payment_gateways->bookingpress_confirm_booking( $entry_id, $bookingpress_payment_data, '1', 'id', 'amount', 1, $bookingpress_is_cart, 'currency' );
                }else{
                    $response['variant'] = 'error';
                    $response['title']   = esc_html__( 'Error', 'bookingpress-stripe' );
                    $response['msg']     = esc_html__( 'Sorry, your appointment caanot book due to payment not verified with stripe.', 'bookingpress-stripe' );
                }
            }

            echo wp_json_encode($response);
            exit;
        }

        function bookingpress_init_stripe(){
            global $BookingPress;
            $bookingpress_stripe_payment_mode = $BookingPress->bookingpress_get_settings('stripe_payment_mode', 'payment_setting');
            $this->bookingpress_stripe_payment_mode = !empty($bookingpress_stripe_payment_mode) ? $bookingpress_stripe_payment_mode : 'sandbox';

            $this->bookingpress_stripe_secret_key = $BookingPress->bookingpress_get_settings('stripe_secret_key', 'payment_setting');
            $this->bookingpress_stripe_publishable_key = $BookingPress->bookingpress_get_settings('stripe_publishable_key', 'payment_setting');

            $bookingpress_stripe_payment_method = $BookingPress->bookingpress_get_settings('stripe_payment_method', 'payment_setting');
            $this->bookingpress_stripe_payment_method = !empty($bookingpress_stripe_payment_method) ? $bookingpress_stripe_payment_method : 'sca_popup';

            $this->bookingpress_stripe_api_version = "2020-08-27";

            if (file_exists(BOOKINGPRESS_STRIPE_LIB_DIR . "vendor/autoload.php")) {
                require_once (BOOKINGPRESS_STRIPE_LIB_DIR . "vendor/autoload.php");
            }

            Stripe\Stripe::setApiKey($this->bookingpress_stripe_secret_key);
            Stripe\Stripe::setApiVersion($this->bookingpress_stripe_api_version);

            $this->bookingpress_stripe_obj = new \Stripe\StripeClient($this->bookingpress_stripe_secret_key);

            $bookigpress_stripe_popup_title_var = $BookingPress->bookingpress_get_settings('sca_popup_title', 'payment_setting');
            $this->bookingpress_stripe_popup_title = !empty($bookigpress_stripe_popup_title_var) ? $bookigpress_stripe_popup_title_var : get_bloginfo('name');

            $bookingpress_stripe_popup_payment_btn_label_var = $BookingPress->bookingpress_get_settings('sca_payment_button_label', 'payment_setting');
            $this->bookingpress_stripe_popup_payment_btn_label = !empty($bookingpress_stripe_popup_payment_btn_label_var) ? $bookingpress_stripe_popup_payment_btn_label_var : __('Pay Now', 'bookingpress-stripe');
        }

        function bookingpress_get_stripe_form($total_payable_amount, $bookingpress_return_data,$bookingpress_service_name = ""){
            global $BookingPress;

            $bookingpress_is_cart = !empty($bookingpress_return_data['is_cart']) ? 1 : 0;
            $bookingpress_booked_service_name = '';
            if(!$bookingpress_is_cart){
                $bookingpress_booked_service_name = !empty($bookingpress_return_data['service_data']['bookingpress_service_name']) ? $bookingpress_return_data['service_data']['bookingpress_service_name'] : '';
            }
            if(!empty($bookingpress_service_name)){
                $bookingpress_booked_service_name = $bookingpress_service_name; 
            }

            $this->bookingpress_stripe_popup_title = str_replace('{appointment_service}', $bookingpress_booked_service_name, $this->bookingpress_stripe_popup_title);

            $bookingpress_company_icon = $BookingPress->bookingpress_get_settings('company_icon_url', 'company_setting');

            $total_payable_amount = $total_payable_amount / 100;
            $final_payable_amount_with_currency = $BookingPress->bookingpress_price_formatter_with_currency_symbol($total_payable_amount);
            $this->bookingpress_stripe_popup_payment_btn_label = str_replace('{total_payable_amount}', $final_payable_amount_with_currency, $this->bookingpress_stripe_popup_payment_btn_label);

            $bookingpress_stripe_form_html  = '<div class="stripe_element_wrapper" id="stripe_element_wrapper">';
            
                $bookingpress_stripe_form_html .= '<div class="form-inner-row" data-locale-reversible>';

                    $bookingpress_stripe_form_html .= "<div class='site_info_row'>";
                        $bookingpress_stripe_form_html .= "<div class='site_info'>";

                        if(!empty($bookingpress_company_icon)){
                            $bookingpress_stripe_form_html .= "<div class='bpa_stripe_popup_logo'>";
                                $bookingpress_stripe_form_html .= "<div class='bpa_stripe_popup_logo_wrap'>";
                                    $bookingpress_stripe_form_html .= "<div class='bpa_stripe_popup_logo_bevel'></div>";
                                    $bookingpress_stripe_form_html .= "<div class='bpa_stripe_popup_logo_border'></div>";
                                    $bookingpress_stripe_form_html .= "<div class='bpa_stripe_popup_logo_image' style='background-image:url(".$bookingpress_company_icon.")'></div>";
                                $bookingpress_stripe_form_html .= "</div>";
                            $bookingpress_stripe_form_html .= "</div>";
                        }

                            $bookingpress_stripe_form_html .= "<div class='site_title'>".$this->bookingpress_stripe_popup_title."</div>";
                            
                            //$bookingpress_stripe_form_html .= "<div class='close_icon' id='stripe_wrapper_close_icon'></div>";
                        $bookingpress_stripe_form_html .= "</div>";
                    $bookingpress_stripe_form_html .= "</div>";

                    $bookingpress_stripe_form_html .= '<div class="field_wrapper">';

                        $bookingpress_stripe_form_html .= '<div class="bookingpress_stripe_field_row">';
                            $bookingpress_stripe_form_html .= '<div class="field">';
                                $bookingpress_stripe_form_html .= '<div id="card-number" class="input empty"></div>';
                                $bookingpress_stripe_form_html .= '<div class="baseline"></div>';
                            $bookingpress_stripe_form_html .= '</div>';
                        $bookingpress_stripe_form_html .= '</div>';

                        $bookingpress_stripe_form_html .= '<div class="bookingpress_stripe_field_row">';
                            $bookingpress_stripe_form_html .= '<div class="field half-width">';
                                $bookingpress_stripe_form_html .= '<div id="card-expiry" class="input empty"></div>';
                                $bookingpress_stripe_form_html .= '<div class="baseline"></div>';
                            $bookingpress_stripe_form_html .= '</div>';
                        $bookingpress_stripe_form_html .= '</div>';

                        $bookingpress_stripe_form_html .= '<div class="bookingpress_stripe_field_row">';
                            $bookingpress_stripe_form_html .= '<div class="field half-width">';
                                $bookingpress_stripe_form_html .= '<div id="card-cvc" class="input empty"></div>';
                                $bookingpress_stripe_form_html .= '<div class="baseline"></div>';
                            $bookingpress_stripe_form_html .= '</div>';
                        $bookingpress_stripe_form_html .= '</div>';

                        $bookingpress_stripe_form_html .= '<div class="card-errors" id="card-errors" role="alert"></div>';
                        $bookingpress_stripe_form_html .= '<button id="card-button" type="button" data-secret="'.$this->bookingpress_stripe_secret_key.'"><span class="bookingpress_stripe_loader"></span>'.$this->bookingpress_stripe_popup_payment_btn_label.'</button>';
                        
                    $bookingpress_stripe_form_html .= '</div>';
                $bookingpress_stripe_form_html .= '</div>';


            $bookingpress_stripe_form_html .= '</div>';

            $bookingpress_stripe_form_html .= '<style type="text/css" id="bookingpress_stripe_css">.stripe_element_wrapper{position:fixed;top:0;left:0;width:100%;height:100%;text-align:center;background:rgba(0,0,0,0.6);z-index:999999;}.stripe_element_wrapper .form-inner-row{ float: left; width: 300px; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #F5F5F7;text-align:left;border-radius:5px;overflow:hidden;}.stripe_element_wrapper #card-button,#update-card-button{ background:linear-gradient(#43B0E9,#3299DE); padding:0 !important; font-weight:normal; border:none; color: #fff; display: inline-block; margin-top: 25px; margin-bottom:15px; height: 40px; line-height: normal; float: left; border-radius:4px;width:100%;font-size:20px;}.stripe_element_wrapper .form-row{ float:left; width: 70%;}.stripe_element_wrapper iframe{position:relative;left:0}.StripeElement {box-sizing: border-box;height: 40px;padding: 10px 12px;border: 1px solid transparent;border-radius: 4px;background-color: white;box-shadow: 0 1px 3px 0 #e6ebf1;-webkit-transition: box-shadow 150ms ease;transition: box-shadow 150ms ease;}.card-errors{font-size: 14px;color: #ff0000;}.site_info_row {float: left;width: 100%;height: 95px;background: #E8E9EB;border-bottom: 1px solid #DBDBDD;box-sizing: border-box;text-align: center;padding: 25px 10px;}.field_wrapper{float:left;padding:30px;width:100%;box-sizing:border-box;}.form-inner-row .field_wrapper .bookingpress_stripe_field_row{float:left;width:100%;margin-bottom:10px;}.site_title,.site_tag{float:left;width:100%;text-align:center;font-size:16px;} .site_title{font-weight:bold;}.site_info_row .close_icon{position: absolute;width: 20px;height: 20px;background: #cecccc;right: 10px;top: 10px;border-radius: 20px;cursor:pointer;}.site_info_row .close_icon::before{content: "";width: 12px;height: 2px;background: #fff;display: block;top: 50%;left: 50%;transform: translate(-50%,-50%) rotate(45deg);position: absolute;}.site_info_row .close_icon::after{content: "";width: 12px;height: 2px;background: #fff;display: block;top: 50%;left: 50%;transform: translate(-50%,-50%) rotate(-45deg);position: absolute;}.StripeElement--focus { box-shadow: 0 1px 3px 0 #cfd7df; }.StripeElement--invalid {border-color: #fa755a;}.StripeElement--webkit-autofill {background-color: #fefde5 !important;}.bookingpress_stripe_loader{float:none;display:inline-block;width:15px;height:15px;border:3px solid #fff;border-radius:15px;border-top:3px solid transparent;margin-right:5px;position:relative;top:3px;display:none;animation:spin infinite 1.5s}@keyframes spin{0%{transform:rotate(0deg);}100%{transform:rotate(360deg)}} #card-button[disabled],#update-card-button[disabled]{opacity:0.7;} #card-button[disabled] .bookingpress_stripe_loader,#update-card-button[disabled] .bookingpress_stripe_loader{display:inline-block;}';

            if(!empty($bookingpress_company_icon)){
                $bookingpress_stripe_form_html .= '.bpa_stripe_popup_logo{float:left;width:100%;position:relative;height:35px;margin-bottom:6px;box-sizing:border-box;}.bpa_stripe_popup_logo *{box-sizing:border-box;}.bpa_stripe_popup_logo_wrap{position:absolute;top:-38px;right:0;left:0;width:70px;height:70px;margin:0 auto;}.bpa_stripe_popup_logo_bevel{border:1px solid rgba(0,0,0,0.2);width:64px;height:64px;border-radius:100%;box-shadow:inset 0 1px 0 0 hsla(0,0%,100%,.1);position:absolute;top:3px;left:3px;}.bpa_stripe_popup_logo_border{border:3px solid #fff;width:70px;height:70px;border-radius:100%;box-shadow:0 0 0 1px rgba(0,0,0,.18), 0 2px 2px 0 rgba(0,0,0,0.08);position:absolute;top:0;left:0;}.bpa_stripe_popup_logo_image{width:64px;height:64px;margin:3px;border-radius:100%;background:#fff;background-position:50% 50%; background-size:cover;display:inline-block;background-repeat:no-repeat;}.form-inner-row{overflow:visible !important;}.site_info_row{border-radius:5px 5px 0 0;height:auto; padding: 15px 10px;}';
            }

            $bookingpress_stripe_form_html .='</style>';

            return $bookingpress_stripe_form_html;
        }

        function bookingpress_stripe_create_customer($customer_email, $customer_card_token = '', $entry_id='', $is_package = false, $bookingpress_is_cart = false){

            global $BookingPress, $bookingpress_debug_payment_log_id, $tbl_bookingpress_appointment_meta, $wpdb, $tbl_bookingpress_package_bookings_meta;

            $get_bpa_stripe_address = '';
            $get_bpa_stripe_country_code = '';

            $bookingpress_stripe_field_enable = $BookingPress->bookingpress_get_settings('stripe_custom_field', 'payment_setting');

            if( !empty( $entry_id ) && ('true' == $bookingpress_stripe_field_enable ) && (false == $is_package) ){

                if( $bookingpress_is_cart == 1 ){
                    $get_form_fields_meta = $wpdb->get_var( $wpdb->prepare( "SELECT bookingpress_appointment_meta_value FROM {$tbl_bookingpress_appointment_meta} WHERE bookingpress_appointment_meta_key = %s AND bookingpress_order_id = %d", 'appointment_details', $entry_id ) ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_meta is a table name. false alarm

                } else {
                    $get_form_fields_meta = $wpdb->get_var( $wpdb->prepare( "SELECT bookingpress_appointment_meta_value FROM {$tbl_bookingpress_appointment_meta} WHERE bookingpress_appointment_meta_key = %s AND bookingpress_entry_id = %d", 'appointment_form_fields_data', $entry_id ) ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_meta is a table name. false alarm
                }
    
                if( !empty( $get_form_fields_meta )){

                    $get_form_fields_meta = !empty( $get_form_fields_meta ) ? json_decode( $get_form_fields_meta, true ) : array();
                    $get_bpa_stripe_country_code = !empty( $get_form_fields_meta['form_fields']['country']) ? sanitize_text_field($get_form_fields_meta['form_fields']['country']) : '';
                    $get_bpa_stripe_address = !empty( $get_form_fields_meta['form_fields']['address']) ? sanitize_text_field($get_form_fields_meta['form_fields']['address']) : '';
                }
            } elseif( !empty( $entry_id ) && ('true' == $bookingpress_stripe_field_enable ) && (true == $is_package) ){

                $get_form_fields_meta = $wpdb->get_var( $wpdb->prepare( "SELECT bookingpress_package_meta_value FROM {$tbl_bookingpress_package_bookings_meta} WHERE bookingpress_package_meta_key = %s AND bookingpress_entry_id = %d", 'package_form_fields_data', $entry_id ) ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_package_bookings_meta is a table name. false alarm
                
                if( !empty( $get_form_fields_meta )){

                    $get_form_fields_meta = !empty( $get_form_fields_meta ) ? json_decode( $get_form_fields_meta, true ) : array();
                    $get_bpa_stripe_country_code = !empty( $get_form_fields_meta['form_fields']['country']) ? sanitize_text_field($get_form_fields_meta['form_fields']['country']) : '';
                    $get_bpa_stripe_address = !empty( $get_form_fields_meta['form_fields']['address']) ? sanitize_text_field($get_form_fields_meta['form_fields']['address']) : '';
    
                }
            }

            $bookingpress_created_customer_id = '';
            $bookingpress_customer_create_arr = array(
                'email' => $customer_email,
                'description' => 'BookingPress customer',
            );

            if( 'true' == $bookingpress_stripe_field_enable ){
                $bookingpress_customer_create_arr['name'] = $customer_email;
                $bookingpress_customer_create_arr['address'] = array(
                    'line1' => $get_bpa_stripe_address,
                    'postal_code' => '00',
                    'city' => '-',
                    'state' => '-',
                    'country' => $get_bpa_stripe_country_code,
                );
            }

            if(!empty($customer_card_token)){
                $bookingpress_customer_create_arr['card'] = $customer_card_token;
            }

            $bookingpress_created_customer_res = $this->bookingpress_stripe_obj->customers->create($bookingpress_customer_create_arr);
            do_action( 'bookingpress_payment_log_entry', 'stripe', 'Stripe created customer res', 'bookingpress pro', $bookingpress_created_customer_res, $bookingpress_debug_payment_log_id );

            $bookingpress_created_customer_id = !empty($bookingpress_created_customer_res->id) ? $bookingpress_created_customer_res->id : '';

            return $bookingpress_created_customer_id;
        }

        function bookingpress_stripe_submit_form_data_func($response, $bookingpress_return_data){
            global $wpdb, $BookingPress, $bookingpress_pro_payment_gateways, $bookingpress_debug_payment_log_id;
            $this->bookingpress_init_stripe();

            if(!empty($bookingpress_return_data)){
                $entry_id                          = $bookingpress_return_data['entry_id'];
                $bookingpress_is_cart = !empty($bookingpress_return_data['is_cart']) ? 1 : 0;
                $currency_code                     = strtolower($bookingpress_return_data['currency_code']);
                $bookingpress_final_payable_amount = isset( $bookingpress_return_data['payable_amount'] ) ? $bookingpress_return_data['payable_amount'] : 0;
                $bookingpress_final_payable_amount = (float)$bookingpress_final_payable_amount * 100;
                $customer_details                  = $bookingpress_return_data['customer_details'];
                $customer_email                    = ! empty( $customer_details['customer_email'] ) ? $customer_details['customer_email'] : '';

                $bookingpress_service_name = ! empty( $bookingpress_return_data['service_data']['bookingpress_service_name'] ) ? $bookingpress_return_data['service_data']['bookingpress_service_name'] : __( 'Appointment Booking', 'bookingpress-stripe' );

                $custom_var = $entry_id . '|' . $bookingpress_is_cart;
                $bookingpress_notify_url = $bookingpress_return_data['notify_url'];
                $redirect_url = $bookingpress_return_data['approved_appointment_url'];
                
                $bookingpress_appointment_status = $BookingPress->bookingpress_get_settings( 'appointment_status', 'general_setting' );
                if ( $bookingpress_appointment_status == '2' ) {
                    $redirect_url = $bookingpress_return_data['pending_appointment_url'];
                }

                $booking_form_redirection_mode = !empty($bookingpress_return_data['booking_form_redirection_mode']) ? $bookingpress_return_data['booking_form_redirection_mode'] : 'external_redirection';

                if($this->bookingpress_stripe_payment_method == "built_in_form_fields"){
                    $bookingpress_card_number = !empty($bookingpress_return_data['card_details']['card_number']) ? $bookingpress_return_data['card_details']['card_number'] : '';
                    $bookingpress_expire_month = !empty($bookingpress_return_data['card_details']['expire_month']) ? $bookingpress_return_data['card_details']['expire_month'] : '';
                    $bookingpress_expire_year = !empty($bookingpress_return_data['card_details']['expire_year']) ? $bookingpress_return_data['card_details']['expire_year'] : '';
                    $bookingpress_cvv = !empty($bookingpress_return_data['card_details']['cvv']) ? $bookingpress_return_data['card_details']['cvv'] : '';

                    if(empty($bookingpress_card_number) || empty($bookingpress_expire_month) || empty($bookingpress_expire_year) || empty($bookingpress_cvv)){
                        $response['variant']       = 'error';
                        $response['title']         = esc_html__( 'Error', 'bookingpress-stripe' );
                        $response['msg']           = esc_html__( 'Please fill all card fields value', 'bookingpress-stripe' );
                        $response['is_redirect']   = 0;
                        $response['redirect_data'] = '';
                        $response['is_spam']       = 0;
                    }else{
                        try{
                            $bookingpress_stripe_charge_details = array();

                            //Create card token
                            $bookingpress_card_token_res = $this->bookingpress_stripe_obj->tokens->create(
                                array(
                                    'card' => array(
                                        'number' => $bookingpress_card_number,
                                        'exp_month' => $bookingpress_expire_month,
                                        'exp_year' => $bookingpress_expire_year,
                                        'cvc' => $bookingpress_cvv
                                    ),
                                )
                            );

                            do_action( 'bookingpress_payment_log_entry', 'stripe', 'Stripe card token res', 'bookingpress pro', $bookingpress_card_token_res, $bookingpress_debug_payment_log_id );

                            $bookingpress_created_card_token = !empty($bookingpress_card_token_res->id) ? $bookingpress_card_token_res->id : '';

                            do_action( 'bookingpress_payment_log_entry', 'stripe', 'Stripe card created token', 'bookingpress pro', $bookingpress_created_card_token, $bookingpress_debug_payment_log_id );

                            if(!empty($bookingpress_created_card_token)){
                                $bookingpress_stripe_charge_details['card_token'] = $bookingpress_created_card_token;

                                $bookingpress_created_customer_id = $this->bookingpress_stripe_create_customer($customer_email, $bookingpress_created_card_token, $entry_id, false, $bookingpress_is_cart);

                                do_action( 'bookingpress_payment_log_entry', 'stripe', 'Stripe card token customer id', 'bookingpress pro', $bookingpress_created_customer_id, $bookingpress_debug_payment_log_id );
                                
                                if(!empty($bookingpress_created_customer_id)){
                                    $bookingpress_stripe_charge_details['customer'] = $bookingpress_created_customer_id;
                                }else{
                                    $bookingpress_customer_err_msg = esc_html__('Stripe Error', 'bookingpress-stripe').": ";
                                    $bookingpress_customer_err_msg .= esc_html__('Something went wrong while creating customer with stripe.', 'bookingpress-stripe')." ";

                                    $response['variant']       = 'error';
                                    $response['title']         = esc_html__( 'Error', 'bookingpress-stripe' );
                                    $response['msg']           = $bookingpress_customer_err_msg;
                                    $response['is_redirect']   = 0;
                                    $response['redirect_data'] = '';
                                    $response['is_spam']       = 0;    
                                }
                            }else{
                                $bookingpress_card_err_msg = esc_html__('Stripe Error', 'bookingpress-stripe').": ";
                                $bookingpress_card_err_msg .= esc_html__('Something went wrong while pay with stripe using provided card details.', 'bookingpress-stripe')." ";

                                $response['variant']       = 'error';
                                $response['title']         = esc_html__( 'Error', 'bookingpress-stripe' );
                                $response['msg']           = $bookingpress_card_err_msg;
                                $response['is_redirect']   = 0;
                                $response['redirect_data'] = '';
                                $response['is_spam']       = 0;
                            }

                            if(!empty($bookingpress_stripe_charge_details)){
                                $bookingpress_stripe_charge_res = $this->bookingpress_stripe_obj->charges->create(
                                    array(
                                        'amount' => $bookingpress_final_payable_amount,
                                        'currency' => $currency_code,
                                        'customer' => $bookingpress_created_customer_id,
                                        'description' => $bookingpress_service_name,
										'metadata' => array('custom' => $custom_var),
                                    )
                                );

                                do_action( 'bookingpress_payment_log_entry', 'stripe', 'Stripe card charge response', 'bookingpress pro', $bookingpress_stripe_charge_res, $bookingpress_debug_payment_log_id );

                                if(!empty($bookingpress_stripe_charge_res->status) && ($bookingpress_stripe_charge_res->status == "succeeded")){
                                    $bookingpress_transaction_id = $bookingpress_stripe_charge_res->id;
                                    $bookingpress_payment_data = json_decode(json_encode($bookingpress_stripe_charge_res), TRUE);
                                    $bookingpress_pro_payment_gateways->bookingpress_confirm_booking( $entry_id, $bookingpress_payment_data, '1', 'id', '',1, $bookingpress_is_cart );

                                    $response['variant'] = 'redirect_url';
                                    $response['title']         = '';
                                    $response['msg']           = '';
                                    $response['is_redirect']   = 1;
                                    $response['redirect_data'] = $redirect_url;
                                    if($booking_form_redirection_mode == "in-built"){
                                        $response['is_transaction_completed'] = 1;
                                    }
                                    $response['entry_id'] = $entry_id;
                                }
                            }
                        }catch(Exception $e){
                            $bookingpress_stripe_err_msg_obj = $e->getJsonBody();
                            $bookingpress_stripe_err_msg = $bookingpress_stripe_err_msg_obj['error']['message'];
                            $response['variant']       = 'error';
                            $response['title']         = esc_html__( 'Error', 'bookingpress-stripe' );
                            $response['msg']           = $bookingpress_stripe_err_msg;
                            $response['is_redirect']   = 0;
                            $response['redirect_data'] = '';
                            $response['is_spam']       = 0;
                        }
                    }
                }else if($this->bookingpress_stripe_payment_method == "sca_popup"){
                    //Create SCA customer
                    $bookingpress_created_customer_id = $this->bookingpress_stripe_create_customer($customer_email,'', $entry_id, false, $bookingpress_is_cart);

                    do_action( 'bookingpress_payment_log_entry', 'stripe', 'Stripe sca customer id', 'bookingpress pro', $bookingpress_created_customer_id, $bookingpress_debug_payment_log_id );

                    if(empty($bookingpress_created_customer_id)){
                        $bookingpress_customer_err_msg = esc_html__('Stripe Error', 'bookingpress-stripe').": ";
                        $bookingpress_customer_err_msg .= esc_html__('Something went wrong while creating customer with stripe.', 'bookingpress-stripe')." ";

                        $response['variant']       = 'error';
                        $response['title']         = esc_html__( 'Error', 'bookingpress-stripe' );
                        $response['msg']           = $bookingpress_customer_err_msg;
                        $response['is_redirect']   = 0;
                        $response['redirect_data'] = '';
                        $response['is_spam']       = 0;    
                    }else{
                        //Create a payment intent at stripe.

                        $bookingpress_stripe_payment_intent_arr = array(
                            'amount' => $bookingpress_final_payable_amount,
                            'currency' => $currency_code,
                            'customer' => $bookingpress_created_customer_id,
                            'description' => $bookingpress_service_name,
                            'automatic_payment_methods' => array(
                                'enabled' => true,
                                'allow_redirects' => 'never',
                            ),	
							'metadata' => array('custom' => $custom_var),
                        );
                        $bookingpress_created_payment_intent_res = $this->bookingpress_stripe_obj->paymentIntents->create($bookingpress_stripe_payment_intent_arr);
                        do_action( 'bookingpress_payment_log_entry', 'stripe', 'Stripe sca created payment res', 'bookingpress pro', $bookingpress_created_payment_intent_res, $bookingpress_debug_payment_log_id );

                        $bookingpress_payment_intent_client_secret = !empty($bookingpress_created_payment_intent_res->client_secret) ? $bookingpress_created_payment_intent_res->client_secret : '';
                        if(empty($bookingpress_payment_intent_client_secret)){
                            $response['variant']       = 'error';
                            $response['title']         = esc_html__( 'Error', 'bookingpress-stripe' );
                            $response['msg']           = esc_html__( 'Something went wrong while creating payment intent', 'bookingpress-stripe' );
                            $response['is_redirect']   = 0;
                            $response['redirect_data'] = '';
                            $response['is_spam']       = 0;        
                        }else{
                            $bookingpress_stripe_form = $this->bookingpress_get_stripe_form($bookingpress_final_payable_amount, $bookingpress_return_data);

                            $bookingpress_redirect_data = $bookingpress_stripe_form;
                            $bookingpress_redirect_data .= '<script type="text/javascript" id="bookingpress_stripe_js">';
                            $bookingpress_redirect_data .= 'var stripe = Stripe("' . $this->bookingpress_stripe_publishable_key .'");';
                            $bookingpress_redirect_data .= 'var elements = stripe.elements({fonts: [{cssSrc: "https://fonts.googleapis.com/css?family=Source+Code+Pro"}],locale: window.__exampleLocale});';

                            $bookingpress_redirect_data .= "var elementStyles = { base: { color: '#32325D', fontWeight: 500, fontFamily: 'Source Code Pro, Consolas, Menlo, monospace', fontSize: '16px', fontSmoothing: 'antialiased', '::placeholder': { color: '#CFD7DF', }, ':-webkit-autofill': { color: '#e39f48',},},invalid: {color: '#E25950','::placeholder': {color: '#FFCCA5',},},};";

                            $bookingpress_redirect_data .= "var elementClasses = { focus: 'focused', empty: 'empty', invalid: 'invalid', };";

                            $bookingpress_redirect_data .= " var cardNumber = elements.create('cardNumber', { style: elementStyles, classes: elementClasses, }); cardNumber.mount('#card-number');";
                            $bookingpress_redirect_data .= " var cardExpiry = elements.create('cardExpiry', { style: elementStyles, classes: elementClasses, }); cardExpiry.mount('#card-expiry');";
                            $bookingpress_redirect_data .= " var cardCvc = elements.create('cardCvc', { style: elementStyles, classes: elementClasses, }); cardCvc.mount('#card-cvc');";

                            $bookingpress_redirect_data .= 'var cardButton = document.getElementById("card-button"); var clientSecret = cardButton.dataset.secret;';

                            $bookingpress_redirect_data .= 'var closeIcon = document.getElementById("stripe_wrapper_close_icon");';

                            /*$bookingpress_redirect_data .= 'closeIcon.addEventListener("click", function(e){
                                document.getElementById("stripe_element_wrapper").remove();
                                document.getElementById("bookingpress_stripe_js").remove();
                                document.getElementById("bookingpress_stripe_css").remove();
                            });';*/

                            $bookingpress_redirect_data .= 'cardButton.addEventListener("click", function(e) {
                                cardButton.setAttribute("disabled","disabled");
                                cardButton.style.cursor = "not-allowed";
                                stripe.confirmCardPayment(
                                    "'.$bookingpress_payment_intent_client_secret.'",
                                    {
                                        payment_method:{ card: cardNumber },
                                        setup_future_usage: "off_session"
                                    }
                                ).then(function(result) {
                                    if (result.error) {
                                        cardButton.removeAttribute("disabled");
                                        cardButton.style.cursor = "";
                                        var errorElement = document.getElementById("card-errors");
                                        errorElement.textContent = result.error.message;
                                    } else {
                                        var errorElement = document.getElementById("card-errors");
                                        errorElement.textContent = "";
                                        if(result.paymentIntent.status == "succeeded"){
                                            var sca_confirm_booking_data = { action: "bookingpress_confirm_sca_booking", bookingpress_payment_res: result, _wpnonce: "'.wp_create_nonce( 'bpa_wp_nonce' ).'", bookingpress_entry_id: "'.$entry_id.'", is_cart_payment: "'.$bookingpress_is_cart.'" }
                                            axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( sca_confirm_booking_data ) )
				                            .then(function(response) {
                                                if(response.data.variant != "error") {
                                                    var bookingpress_redirection_mode = "'.$booking_form_redirection_mode.'";
                                                    if(bookingpress_redirection_mode == "in-built"){
                                                        window.app.bookingpress_render_thankyou_content();
                                                        var bookingpress_uniq_id = window.app.appointment_step_form_data.bookingpress_uniq_id;
                                                        document.getElementById("bookingpress_booking_form_"+bookingpress_uniq_id).style.display = "none";
                                                        document.getElementById("stripe_element_wrapper").remove();
                                                        if(response.data.variant != "error"){
                                                            document.getElementById("bpa-failed-screen-div").style.display = "none";
                                                            document.getElementById("bpa-thankyou-screen-div").style.display = "block";
                                                        }else{
                                                            document.getElementById("bpa-failed-screen-div").style.display = "block";
                                                            document.getElementById("bpa-thankyou-screen-div").style.display = "none";
                                                        }
                                                    }else{
                                                        window.location.href = "'.$redirect_url.'";
                                                    }
                                                } else {
                                                    window.app.bookingpress_set_error_msg(response.data.msg);
                                                }
                                            }).catch(function(error){
                                                console.log(error);
                                            });
                                        }
                                        else {
                                            var sca_confirm_booking_data = { action: "bookingpress_sca_booking_payment_intent_log", bookingpress_payment_res: result, _wpnonce: "'.wp_create_nonce( 'bpa_wp_nonce' ).'", bookingpress_entry_id: "'.$entry_id.'", is_cart_payment: "'.$bookingpress_is_cart.'" }
                                            axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( sca_confirm_booking_data ) )
				                            .then(function(response) {
                                                window.app.bookingpress_set_error_msg(response.data.msg);
                                            }).catch(function(error){
                                                console.log(error);
                                            });
                                        }
                                    }
                                });
                            });';

                            $bookingpress_redirect_data .= '</script>';

                            $bookingpress_return_data = $bookingpress_redirect_data;

                            $response['variant']       = 'redirect';
                            $response['title']         = '';
                            $response['msg']           = '';
                            $response['is_redirect']   = 1;
                            $response['redirect_data'] = $bookingpress_return_data;
                            $response['entry_id'] = $entry_id;
                        }
                    }
                }else{
                    $response['variant']       = 'error';
                    $response['title']         = esc_html__( 'Error', 'bookingpress-stripe' );
                    $response['msg']           = esc_html__( 'Something went wrong while payment with stripe', 'bookingpress-stripe' );
                    $response['is_redirect']   = 0;
                    $response['redirect_data'] = '';
                    $response['is_spam']       = 0;
                }
            }
            return $response;
        }

        		
		/**
		 * bookingpress_stipe_apply_refund_func
		 *
		 * @param  mixed $response
		 * @param  mixed $bookingpres_refund_data
		 * @return void
		 */
		function bookingpress_stripe_apply_refund_func($response,$bookingpress_refund_data) {
            global $bookingpress_debug_payment_log_id;

            $bookingpress_transaction_id = !empty($bookingpress_refund_data['bookingpress_transaction_id']) ? $bookingpress_refund_data['bookingpress_transaction_id'] :'';
            if(!empty($bookingpress_transaction_id ) && !empty($bookingpress_refund_data['refund_type'])) {   
                if('pi' == substr($bookingpress_transaction_id, 0, 2) ) {
                    $bookingpress_send_refund_data = array('payment_intent' => $bookingpress_transaction_id);
                 } else {
                    $bookingpress_send_refund_data = array('charge' => $bookingpress_transaction_id);
                }                

                $bookingpres_refund_type = $bookingpress_refund_data['refund_type'] ? $bookingpress_refund_data['refund_type'] : '';
                if($bookingpres_refund_type != 'full') {
                    $bookingpres_refund_amount = $bookingpress_refund_data['refund_amount'] ? $bookingpress_refund_data['refund_amount'] : 0;
                    $bookingpress_send_refund_data['amount'] = ((float)$bookingpres_refund_amount) * 100;
                }
                if(!empty($bookingpress_refund_data['refund_reason'])) {
					$bookingpress_send_refund_data['metadata'] = array(
                        'refund_reason_description' => $bookingpress_refund_data['refund_reason']
                    );
				}
                if( empty( $bookingpress_send_refund_data['reason'] ) || !in_array( $bookingpress_send_refund_data['reason'], array( 'duplicate', 'requested_by_customer', 'fraudulent', 'expired_uncaptured_charge')) ){
                    $bookingpress_send_refund_data['reason'] = 'requested_by_customer';
                }

                do_action( 'bookingpress_payment_log_entry', 'stripe', 'Stripe submited refund data', 'bookingpress pro', $bookingpress_send_refund_data, $bookingpress_debug_payment_log_id );

                try{
                    $this->bookingpress_init_stripe();

                    $bookingpress_create_refund_response = $this->bookingpress_stripe_obj->refunds->create($bookingpress_send_refund_data);
                    do_action( 'bookingpress_payment_log_entry', 'stripe', 'Stripe response of the refund', 'bookingpress pro', $bookingpress_create_refund_response, $bookingpress_debug_payment_log_id);

                    if(!empty($bookingpress_create_refund_response->id)) {
                        $response['title']   = esc_html__( 'Success', 'bookingpress-stripe' );
                        $response['variant'] = 'success';
                        $response['bookingpress_refund_response'] = !empty($bookingpress_create_refund_response) ? $bookingpress_create_refund_response : array();
                    } else {
						$response['variant'] = 'error';
						$response['title']  = esc_html__( 'Error', 'bookingpress-stripe' );
						$response['msg'] =  esc_html__('Sorry! refund could not be processed', 'bookingpress-stripe');
					}

               } catch (Exception $e){
                    $error_message = '';
                    $error_message = esc_html__('Error Code', 'bookingpress-stripe').':'.$e->getCode().' '. $e->getMessage();
                    do_action( 'bookingpress_payment_log_entry', 'stripe', 'Stripe refund resoponse with error', 'bookingpress pro', $error_message, $bookingpress_debug_payment_log_id);

                    $response['title']   = esc_html__( 'Error', 'bookingpress-stripe' );
                    $response['variant'] = 'error';
                    $response['msg'] = $error_message;
               }
            }
            return 	$response;
		}
    }

    global $bookingpress_stripe_payment;
	$bookingpress_stripe_payment = new bookingpress_stripe_payment;
}

?>