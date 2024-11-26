<?php
if (!class_exists('bookingpress_google_calendar') && class_exists('BookingPress_Core') ) {
	class bookingpress_google_calendar Extends BookingPress_Core {
        var $bookingpress_global_data;
		function __construct() {         
            add_action( 'admin_notices', array( $this, 'bookingpress_google_calendar_admin_notices') );
            if( !function_exists('is_plugin_active') ){
                include_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            if(is_plugin_active('bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php')){

                global $bookingpress_global_options;
                $this->bookingpress_global_data = $bookingpress_global_options->bookingpress_global_options();

                add_action( 'bookingpress_staff_members_dynamic_vue_methods', array( $this, 'bookingpress_google_calendar_authentication' ) );

                add_action( 'bookingpress_myprofile_dynamic_add_vue_methods_func', array( $this, 'bookingpress_google_calendar_authentication' ) );

                add_action( 'wp',array($this,'bookingpress_add_google_calender_event'),10);

                add_action( 'bookingpress_add_setting_dynamic_vue_methods', array( $this, 'bookingpress_google_calendar_vue_methods' ) );
                
                add_action( 'bookingpress_myprofile_dynamic_add_vue_methods_func', array( $this, 'bookingpress_google_calendar_vue_methods' ) );

                add_action( 'bookingpress_staff_member_view', array( $this, 'bookingpress_staff_member_google_calendar_integration' ) );

                add_filter( 'bookingpress_staff_member_vue_dynamic_data_fields', array( $this, 'bookingpress_staff_member_google_calendar_dynamic_field_data' ) );

                add_filter( 'bookingpress_modify_edit_profile_fields', array($this, 'bookingpress_edit_profile_google_calendar_dynamic_field_data_func'));

                add_filter( 'bookingpress_staff_member_external_details', array( $this, 'bookingpress_generate_staff_member_gcalendar_auth_link') );

                add_action( 'bookingpress_staff_member_edit_details_response', array( $this, 'bookingpress_assign_gcalendar_auth_link' ) );

                add_action( 'bookingpress_staff_member_external_onload_methods', array( $this, 'bookingpress_edit_staff_members' ) );
                
                add_filter( 'bookingpress_staff_members_save_external_details', array( $this, 'bookingpress_staff_members_save_gcalendar' ) );

                add_action( 'bookingpress_save_staff_member', array( $this, 'bookingpress_save_staff_member_func' ) );

                add_action( 'bookingpress_after_add_appointment_from_backend', array( $this, 'bookingpress_assign_appointment_to_staff_member_from_admin' ), 10, 3 );

                add_action( 'bookingpress_after_book_appointment', array( $this, 'bookingpress_schedule_gc_event' ), 10, 3 );

                add_action( 'bookingpress_after_add_group_appointment', array( $this, 'bookingpress_schedule_group_appointments') );

                add_action( 'bookingpress_schedule_staffmember_gc_event', array( $this, 'bookingpress_assign_appointment_to_staff_member_with_cron'), 10, 3 );

                add_action( 'bookingpress_force_send_scheduled_notifications', array( $this, 'bookingpress_force_assign_appointment_to_staff') );

                add_action( 'bookingpress_after_change_appointment_status', array( $this, 'bookingpress_update_google_calendar_event'), 10, 2 );

                add_action( 'bookingpress_after_cancel_appointment', array( $this, 'bookingpress_update_google_calendar_event'), 10 );                

                add_action( 'bookingpress_before_delete_appointment', array( $this, 'bookingpress_remove_google_calendar_event') );

                add_action( 'bookingpress_after_update_appointment', array( $this, 'bookingpress_calendar_event_reschedule') );

                add_action( 'bookingpress_after_rescheduled_appointment', array( $this, 'bookingpress_calendar_event_reschedule') );

                add_action( 'bookingpress_after_module_activate', array( $this, 'bookingpress_hide_notice_after_activate_module') );
                
                add_action( 'bookingpress_after_deactivating_module', array( $this, 'bookingpress_show_notice_after_deactivate_module') );

                add_filter( 'bookingpress_modify_booked_appointment_data', array( $this, 'bookingpress_modify_service_time_with_calendar_events'), 10, 4 );

                add_filter( 'bookingpress_add_integration_debug_logs', array( $this, 'bookingpress_add_google_calendar_integration_logs' ) );

                add_action( 'bookingpress_add_integration_settings_section',array($this,'bookingpress_add_integration_settings_section_func'));

                add_filter( 'bookingpress_add_setting_dynamic_data_fields', array( $this, 'bookingpress_add_setting_dynamic_data_fields_func' ), 11);

                add_filter( 'bookingpress_available_integration_addon_list',array($this,'bookingpress_available_integration_addon_list_func'));

                add_action( 'bookingpress_load_integration_settings_data', array( $this,'bookingpress_load_integration_settings_data_func' ));

                add_filter( 'bookingpress_addon_list_data_filter',array($this,'bookingpress_addon_list_data_filter_func'));

                add_action( 'wp_ajax_bookingpress_signout_google_calendar', array( $this, 'bookingpress_signout_google_calendar' ) );

                add_filter( 'bookingpress_modify_disable_dates_with_staffmember', array( $this, 'bookingpress_modify_disable_dates_google_calendar' ), 11, 3 );

                add_filter( 'bookingpress_modify_save_setting_data', array( $this, 'bookingpress_save_google_calendar_settings'), 10, 2 );

                add_action( 'bookingpress_modify_readmore_link', array($this, 'bookingpress_modify_readmore_link_func'), 15);

                add_filter( 'bookingpress_modify_capability_data', array($this, 'bookingpress_modify_capability_data_func'), 11, 1);

                add_action( 'init', array( $this, 'bookingpress_google_calendar_cron' ) );

                add_filter( 'cron_schedules', array( $this, 'bookingpress_gc_cron_schedules' ) );

                add_action( 'bookingpress_validate_staffmember_token', array( $this, 'bookingpress_validate_staffmember_token_callback' ) );
		
                add_filter( 'bookingpress_add_global_option_data', array( $this, 'bookingpress_add_global_option_data_func' ), 11 );

                add_filter( 'bookingpress_modify_email_notification_content', array( $this, 'bookingpress_modify_email_content_func' ), 11, 3 );

                add_action( 'bookingpress_page_admin_notices', array( $this, 'bookingpress_display_gc_token_validity_notices') );
                
                add_action( 'bookingpress_admin_vue_on_load_script', array( $this, 'bookingpress_reauth_google_calendar') );

                add_action( 'bookingpress_staff_member_edit_details_response', array( $this, 'bookingpress_move_control_to_gcalendar') );

                add_action( 'bookingpress_myprofile_dynamic_on_load_methods', array( $this, 'bookingpress_reauth_google_calendar_myprofile' ) );

                add_action( 'admin_enqueue_scripts', array( $this, 'bookingpress_admin_enqueue_scripts' ), 11 );

                add_filter( 'bookingpress_modify_my_appointments_data_externally',array($this,'bookingpress_modify_my_appointments_data_externally_fun'),20,1);
                                            
                add_action( 'bookingpress_integration_connect_extra_link',array($this,'bookingpress_integration_connect_extra_link_func'));

                add_filter( 'bookingpress_generate_my_booking_customize_css',array($this,'bookingpress_generate_my_booking_customize_css_func'),10,2);

                add_action( 'bookingpress_add_frontend_css',array($this,'bookingpress_add_frontend_css_func'));

                add_action( 'bpa_add_extra_tab_outside_func', array( $this,'bpa_add_extra_tab_outside_func_arr'));

                add_action( 'bookingpress_google_calendar_after_save_staff_settings', array( $this, 'bookingpress_gc_synchronize_staff_calendar') );

                add_action( 'wp', array( $this, 'bookingpress_gc_receive_synchronization') );

                add_filter( 'bookingpress_disabled_features_with_cron', array( $this, 'bookingpress_disabled_features_with_cron_func') );

                /*Backend add Google Meet URL Link Button*/
                add_action('bookingpress_add_dynamic_buttons_for_view_appointments', array($this, 'bookingpress_add_dynamic_buttons_for_view_appointments_func'), 12 );
                add_filter('bookingpress_modify_appointment_data', array($this, 'bookingpress_modify_appointment_data_func'), 10, 1);
                add_action('bookingpress_appointment_add_dynamic_vue_methods', array($this, 'bookingpress_appointment_add_dynamic_vue_methods_func'));
                add_action('bookingpress_dashboard_add_dynamic_vue_methods', array($this, 'bookingpress_appointment_add_dynamic_vue_methods_func'));
                /*For Calendar Page -Back end */
                add_action('bookingpress_additional_action_buttons', array($this, 'bookingpress_additional_action_buttons_gc_func'), 12);             
                add_filter('bookingpress_modify_popover_appointment_data', array($this, 'bookingpress_modify_popover_appointment_data_gc_func'), 12); 
                add_filter('bookingpress_modify_popover_appointment_data_query', array($this, 'bookingpress_modify_popover_appointment_data_query_gc_func'), 12, 2);    

                /* check staff member unavailable dateandtime data */
                add_filter('bookingpress_check_unavailable_time_outside', array($this, 'bookingpress_check_outside_unavailable_time_func'), 10,3);

                add_action( 'wp_ajax_bookingpress_refresh_google_calendar_list', array( $this, 'bookingpress_refresh_google_calendar_list_func' ) );
                /*Hook for front end add verion variable*/
                add_filter('bookingpress_frontend_apointment_form_add_dynamic_data',array($this,'bookingpress_frontend_apointment_form_add_dynamic_data_func_gc'));

                add_filter( 'bookingpress_add_dynamic_notification_data_fields', array( $this, 'bookingpress_add_google_dynamic_notification_data_fields' ) );

                add_action( 'bookingpress_notification_external_message_plachoders', array( $this, 'bookingpress_notification_google_meet_placeholder') );
				
				if( is_plugin_active( 'bookingpress-waiting-list/bookingpress-waiting-list.php' ) ){
                    add_filter( 'bookingpress_check_available_timings_with_staffmember', array( $this, 'bookingpress_set_flag_for_waiting_list'), 10, 4 );
					add_filter( 'bookingpress_modify_single_time_slot_data', array( $this, 'bookingpress_prevent_waiting_list_check_for_timeslot'), 12, 3 );	
				}
            }
            add_action( 'admin_init', array( $this, 'bookingpress_upgrade_google_calendar_data' ) );
            
            add_action('activated_plugin',array($this,'bookingpress_is_google_calendar_addon_activated'),11,2);
        }

        		
		function bookingpress_prevent_waiting_list_check_for_timeslot( $service_time_arr, $selected_service_id, $selected_date ){
			
			if( !empty( $service_time_arr['is_waiting_slot'] ) && $service_time_arr['is_waiting_slot'] == true && !empty( $service_time_arr['skip_waiting_list_with_gc'] ) && true == $service_time_arr['skip_waiting_list_with_gc'] ) {
				$service_time_arr['is_waiting_slot'] = false;
			}
			
			return $service_time_arr;
		}
		
		function bookingpress_set_flag_for_waiting_list( $service_timings, $selected_service_id, $selected_date, $total_booked_appiontments ){

            if( !empty( $total_booked_appiontments ) ){
				foreach( $total_booked_appiontments as $booked_appointment_data ){
					
					if( !empty( $booked_appointment_data['bookingpress_gc_blocked'] ) && 1 == $booked_appointment_data['bookingpress_gc_blocked'] ){
						$booked_appointment_start_time = $booked_appointment_data['bookingpress_appointment_time'];
						$booked_appointment_end_time = $booked_appointment_data['bookingpress_appointment_end_time'];

						if( '00:00:00' == $booked_appointment_end_time ){
							$booked_appointment_end_time = '24:00:00';
						}
						
						foreach( $service_timings as $sk => $time_slot_data ){
							$current_time_start = $time_slot_data['store_start_time'].':00';
							$current_time_end = $time_slot_data['store_end_time'].':00';
							if( ( $booked_appointment_start_time >= $current_time_start && $booked_appointment_end_time <= $current_time_end ) || ( $booked_appointment_start_time < $current_time_end && $booked_appointment_end_time > $current_time_start) ){
								$service_timings[ $sk ]['skip_waiting_list_with_gc'] = true;
							}
						}
					}
				}
			}

            return $service_timings;
        }

        function bookingpress_add_google_dynamic_notification_data_fields( $bookingpress_notification_vue_methods_data ){
            $bookingpress_notification_vue_methods_data['google_meet_placeholder'] = array(
                array(
                    'value' => '%google_meet_url%',
                    'name' => '%google_meet_url%'
                ),
            );

            return $bookingpress_notification_vue_methods_data;
        }

        function bookingpress_notification_google_meet_placeholder(){
            ?>
            <div class="bpa-gs__cb--item-tags-body">
                <div>
                    <span class="bpa-tags--item-sub-heading"><?php esc_html_e('Google Calendar', 'bookingpress-google-calendar'); ?></span>
                    <span class="bpa-tags--item-body" v-for="item in google_meet_placeholder" @click="bookingpress_insert_placeholder(item.value); bookingpress_insert_sms_placeholder(item.value);">{{ item.name }}</span>
                </div>
            </div>
            <?php
        }

        function bookingpress_frontend_apointment_form_add_dynamic_data_func_gc($bookingpress_front_vue_data_fields) {
            global $bookingpress_google_calendar_version;
            $bookingpress_front_vue_data_fields['bookingpress_google_calendar_version'] = $bookingpress_google_calendar_version;
            return $bookingpress_front_vue_data_fields;
        }

        function bookingpress_refresh_google_calendar_list_func(){

            global $wpdb, $tbl_bookingpress_staffmembers, $tbl_bookingpress_staffmembers_meta, $bookingpress_pro_staff_members, $bookingpress_debug_integration_log_id;
            $response              = array();
            $response['variant'] = 'error';
            $response['title']   = esc_html__( 'Error', 'bookingpress-google-calendar' );
            $response['msg']     = esc_html__( 'Calendar List Not Found', 'bookingpress-google-calendar' );
            
            $bpa_check_authorization = $this->bpa_check_authentication( 'google_calendar_refresh_list', true, 'bpa_wp_nonce' );
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-google-calendar');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-google-calendar');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }
            $staffmember_id = !empty( $_POST['staffmember'] ) ? intval( $_POST['staffmember'] ) : '';
            if(!empty( $staffmember_id ) ){
                $bookingpress_gcalendar_service = $this->bookingpress_get_staff_google_calendar_service( $staffmember_id );
                if( empty( $bookingpress_gcalendar_service ) || null == $bookingpress_gcalendar_service ){
                    wp_send_json( $response );
                    die;
                }
                
                $calendarId = !empty( $_POST['selected_calendar'] ) ? sanitize_text_field( $_POST['selected_calendar'] ) : '';

                $lists = $bookingpress_gcalendar_service->calendarList->listCalendarList();
                $calendar_data = array();
                $is_calendar_exists = false;
                foreach( $lists->getItems() as $calendarEntity ){
                    $calendar_data[] = array(
                        'id' => $calendarEntity->id,
                        'name' => $calendarEntity->summary,
                        'primary' => !empty($calendarEntity->primary) ? (int)$calendarEntity->primary : 0
                    );
                    if( false == $is_calendar_exists ){
                        $is_calendar_exists = ( !empty( $calendarId ) &&  $calendarEntity->id == $calendarId );
                    }
                }
                if( version_compare( PHP_VERSION_ID, '7.0.0', '>=' ) ){
                    usort($calendar_data, function ($a, $b) {return $b['primary'] <=> $a['primary'];});
                } else {
                    usort($calendar_data, function ($a, $b) {return $a['primary'] < $b['primary'];});
                }
                $calendar_arr = array();
                foreach( $calendar_data as $calendar ){
                    $calendar_arr[] = array(
                        'value' => $calendar['id'],
                        'name' => $calendar['name']
                    );
                }
                $response['is_reset_list'] = ( false == $is_calendar_exists );
                $response['variant'] = 'success';
                $response['title']   = esc_html__( 'Success', 'bookingpress-google-calendar' );
                $response['msg']     = esc_html__( 'List has been refreshed successfully.', 'bookingpress-google-calendar' );
                $response['google_calendars'] = $calendar_arr;

                if( !empty( $calendar_arr ) ){                
                    $bookingpress_pro_staff_members->update_bookingpress_staffmembersmeta( $staffmember_id,'bookingpress_staff_gcalendar_list',json_encode( $calendar_arr));
                }
            }
            echo wp_json_encode( $response );
            die;
        }

        function bookingpress_check_outside_unavailable_time_func( $staff_unavailable_times, $selected_date, $available_staff_ids ){

            global $bookingpress_pro_staff_members, $tbl_bookingpress_appointment_bookings, $wpdb;

            if( !empty( $available_staff_ids ) ){
                foreach( $available_staff_ids as $bpa_staff_id ){
                    
                    $is_google_calendar_enabled = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bpa_staff_id, 'bookingpress_staff_gcalendar_enable' );
                    if( "true" != $is_google_calendar_enabled ){
                        continue;
                    }
                    
                    $is_invalid_token = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bpa_staff_id, 'bookingpress_display_invalid_token_notice');
                    if( true == $is_invalid_token ){
                        continue;
                    }
                    
                    $calendarId = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bpa_staff_id, 'bookingpress_selected_gcalendar' );
                    if( empty( $calendarId ) ){
                        continue;
                    }

                    $staff_gcalendar_events = $this->bookingpress_retrieve_google_calendar_events( $bpa_staff_id, $calendarId, $selected_date, true );

            
                    if( empty( $staff_gcalendar_events ) ){
                        continue;
                    } 
                    
                    foreach( $staff_gcalendar_events as $event_id => $event_times ){

                        if( !empty( $event_id ) ){
                            /** check if Event is Registered With BookingPress */                    
                            $db_service_id = $wpdb->get_var( $wpdb->prepare( "SELECT bookingpress_service_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_google_calendar_event_id = %s", $event_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
                            if( !empty( $db_service_id ) && $db_service_id == $service_id ){
                                continue;
                            }
                        }
        
                        $event_start_datetime = $event_times['start_date'];
                        $event_end_datetime = $event_times['end_date'];
        
                        $evt_start_date = date('Y-m-d', strtotime( $event_start_datetime ) );
                        $evt_end_date = date('Y-m-d', strtotime( $event_end_datetime) );

                        $staff_unavailable_times[ $bpa_staff_id ][] = array(
                            'start_time' => date('H:i', strtotime($event_start_datetime) ),
                            'end_time' => date('H:i', strtotime( $event_end_datetime ) ),
                            'quantity' => 1,
                            'max_capacity'  => 1,
                        );
                    }

                }
            } 
            return $staff_unavailable_times;
        }

        function bookingpress_modify_popover_appointment_data_query_gc_func( $appointment_query_dynamic_arr, $posted_data)
        {   
            $appointment_columns_gc=',appointment.bookingpress_google_calendar_event_link';
            $appointment_query_dynamic_arr['appointment_columns'] .= $appointment_columns_gc; 
            return $appointment_query_dynamic_arr;
        }

        function bookingpress_modify_popover_appointment_data_gc_func($appointment_data)
        {
            if(!empty($appointment_data)) {
                if(!empty($appointment_data['bookingpress_popover_appointemnt_data'])) {
                    foreach ($appointment_data['bookingpress_popover_appointemnt_data'] as $key => $appointment_data_val) {
                        $bookingpress_google_calendar_event_link = isset($appointment_data_val['bookingpress_google_calendar_event_link']) ? $appointment_data_val['bookingpress_google_calendar_event_link'] : '';
                        $appointment_data['bookingpress_popover_appointemnt_data'][$key]['bookingpress_google_calendar_event_link'] = $bookingpress_google_calendar_event_link;
                    }
                } 
            } 
            return $appointment_data;
        }

        function bookingpress_additional_action_buttons_gc_func()
        { ?>
           <el-tooltip popper-class="bookingpress-gc-cal-popover" effect="dark" content="<?php esc_html_e('Google Meet', 'bookingpress-google-calendar'); ?>" placement="top"  open-delay="300">
                <a target="_blank" class="bpa-cal-google-meet-link" :href="item.bookingpress_google_calendar_event_link" v-if="item.bookingpress_appointment_status == 1 && item.bookingpress_google_calendar_event_link != undefined && item.bookingpress_google_calendar_event_link != '' && item.bookingpress_app_is_past==false">
                <svg width="20" height="18" viewBox="0 0 20 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <mask id="mask0_49_120" style="mask-type:luminance" maskUnits="userSpaceOnUse" x="1" y="3" width="17" height="10">
                    <path d="M17.1727 3.55566H1V12.4446H17.1727V3.55566Z" fill="white"/>
                    </mask>
                    <g mask="url(#mask0_49_120)">
                    <path d="M12.9715 10.2496C12.9729 10.7245 12.9851 11.1999 12.9722 11.6747C12.9608 12.1053 12.4851 12.4425 11.9048 12.4452C11.1 12.4485 10.2961 12.4463 9.49139 12.4463C8.05435 12.4463 6.61731 12.4463 5.18026 12.4463C5.11404 12.4463 5.04709 12.4436 4.98087 12.4425C4.98016 11.638 4.97874 10.8335 4.97803 10.0295L4.98728 10.0225C6.52829 10.023 8.0693 10.0214 9.61032 10.0263C9.74989 10.0268 9.78336 9.99928 9.78193 9.89514C9.77694 9.2568 9.78123 8.61847 9.78336 7.98014C9.79261 7.9796 9.80187 7.97906 9.81042 7.97852C9.81255 8.0368 9.87379 8.06539 9.92151 8.09831C10.4542 8.46955 10.9832 8.84348 11.5252 9.20662C11.6569 9.29512 11.7886 9.38523 11.9183 9.4748C12.2395 9.69819 12.5791 9.90593 12.8774 10.1482C12.8889 10.1568 12.8995 10.1655 12.911 10.1746C12.9103 10.2081 12.9053 10.2448 12.9715 10.2496Z" fill="#727E95"/>
                    <path d="M9.80887 7.97444C9.79962 7.97498 9.79036 7.97552 9.78182 7.97606C9.78039 7.35013 9.77612 6.72367 9.78039 6.09775C9.7811 6.0044 9.75119 5.97688 9.62515 5.9785C9.15871 5.98443 8.69299 5.98066 8.22727 5.9812C7.14557 5.98227 6.06387 5.98389 4.98289 5.98498L4.97363 5.97742C4.97435 5.17451 4.97435 4.37214 4.97506 3.56923C5.03345 3.54819 5.09541 3.5579 5.15523 3.5579C7.37986 3.55736 9.60378 3.55736 11.8284 3.55736C12.5249 3.55736 12.9735 3.89676 12.9756 4.42232C12.9771 4.87612 12.9785 5.32991 12.9799 5.78371C12.9777 5.79072 12.9756 5.79828 12.9728 5.80529C12.9507 5.8177 12.9272 5.82849 12.9073 5.84198C12.3725 6.20675 11.8363 6.57097 11.3036 6.93735C10.803 7.28215 10.3066 7.6291 9.80887 7.97444Z" fill="#727E95"/>
                    <path d="M12.9706 5.80506C12.9727 5.79805 12.9749 5.79049 12.9777 5.78348C13.2675 5.58815 13.5588 5.3939 13.8465 5.19748C14.1306 5.00323 14.414 4.8079 14.6946 4.61095C14.8769 4.48307 15.0784 4.42533 15.317 4.50573C15.5526 4.58505 15.6395 4.73236 15.6395 4.92283C15.6382 6.97489 15.6382 9.02641 15.6395 11.0785C15.6395 11.269 15.5506 11.4157 15.3141 11.4945C15.0755 11.5738 14.8748 11.5139 14.6917 11.3871C14.1647 11.0202 13.635 10.6554 13.103 10.2923C13.0681 10.2685 13.0454 10.2027 12.967 10.2464C12.9008 10.2416 12.9065 10.2049 12.9071 10.1698C13.0254 10.1919 12.9699 10.1191 12.9699 10.0932C12.9727 8.6638 12.9713 7.23443 12.9706 5.80506Z" fill="#727E95"/>
                    <path d="M1.78656 10.0218C1.78442 9.43361 1.78229 8.84546 1.78158 8.25731C1.78015 7.54235 1.78086 6.82793 1.78158 6.11352C1.78158 6.06819 1.76591 6.02125 1.79867 5.97754C2.85686 5.97754 3.91506 5.97754 4.97326 5.97754L4.98252 5.98509C4.9818 6.94718 4.97967 7.90981 4.97967 8.87189C4.97967 9.25446 4.98323 9.63649 4.98537 10.0191L4.97612 10.0261C3.91293 10.025 2.84975 10.0234 1.78656 10.0218Z" fill="#727E95"/>
                    <path d="M1.78688 10.0215C2.85007 10.0231 3.91324 10.0247 4.97643 10.0263C4.97714 10.8309 4.97857 11.6354 4.97928 12.4394C4.24723 12.4404 3.51589 12.4507 2.78455 12.4394C2.24548 12.4307 1.79329 12.08 1.78546 11.6721C1.77478 11.1217 1.78546 10.5713 1.78688 10.0215Z" fill="#727E95"/>
                    <path d="M4.97147 5.97752C3.91327 5.97752 2.85507 5.97752 1.79688 5.97752C1.81753 5.9581 1.83676 5.93705 1.85954 5.91924C2.88 5.14601 3.90045 4.37278 4.92091 3.59955C4.93658 3.58822 4.95581 3.57959 4.97289 3.56934C4.97218 4.37224 4.97218 5.17461 4.97147 5.97752Z" fill="#727E95"/>
                    <path d="M12.9708 5.80566C12.9715 7.23503 12.973 8.6644 12.9708 10.0938C12.9708 10.1191 13.0264 10.192 12.9082 10.1704C12.8967 10.1618 12.8861 10.1531 12.8747 10.144C12.5763 9.90222 12.2367 9.69394 11.9155 9.47055C11.7859 9.38044 11.6541 9.29033 11.5223 9.20238C10.9804 8.83923 10.4514 8.46529 9.91871 8.09406C9.87171 8.06115 9.80976 8.03255 9.80762 7.97427C10.3054 7.6284 10.8018 7.28144 11.3016 6.93772C11.8342 6.5708 12.3705 6.20712 12.9053 5.84235C12.9245 5.82886 12.9487 5.81807 12.9708 5.80566Z" fill="#727E95"/>
                    </g>
                    </svg>
                </a>
            </el-tooltip>	  
            <?php
        }

        /**
         * bookingpress_appointment_add_dynamic_vue_methods_func
         *
         * @return void
         */
        function bookingpress_appointment_add_dynamic_vue_methods_func(){
            ?>
            bookingpress_redirect_to_google_meet_url(bookingpress_google_meet_url){
                window.open(bookingpress_google_meet_url, '_blank').focus();
            },
            <?php
        }
        
        /**
         * bookingpress_modify_appointment_data_func
         *
         * @param  mixed $appointment_data
         * @return void
         */
        function bookingpress_modify_appointment_data_func($appointment_data){
            if(!empty($appointment_data)){
                global $wpdb, $tbl_bookingpress_appointment_bookings;
                $bookingpress_from_time = current_time('timestamp');
                foreach($appointment_data as $k => $v){
                    $bookingpress_payment_log_id = $v['payment_id'];
                    $bookingpress_appointment_id = $v['appointment_id'];
                    $bookingpress_appointment_details = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_appointment_status,bookingpress_appointment_date, bookingpress_appointment_time, bookingpress_google_calendar_event_link FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $bookingpress_appointment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
                    $appointment_data[$k]['bookingpress_show_google_meet_url'] = false;
                    $appointment_data[$k]['bookingpress_google_calendar_event_link'] = '';
                    if(!empty($bookingpress_appointment_details)) {
                        $bookingpress_to_time = strtotime($bookingpress_appointment_details['bookingpress_appointment_date'] .' '. $bookingpress_appointment_details['bookingpress_appointment_time']);  
                        if(isset($bookingpress_appointment_details['bookingpress_google_calendar_event_link']) && !empty($bookingpress_appointment_details['bookingpress_google_calendar_event_link'])) {
                            $bookingpress_appointment_status = !empty( $bookingpress_appointment_details['bookingpress_appointment_status']) ? intval($bookingpress_appointment_details['bookingpress_appointment_status']) : '';
                            if($bookingpress_appointment_status == 1 ) {
                                $appointment_data[$k]['bookingpress_show_google_meet_url'] = true;
                            }
                            $bookingpress_google_calendar_event_link = $bookingpress_appointment_details['bookingpress_google_calendar_event_link'];
                            $appointment_data[$k]['bookingpress_google_calendar_event_link'] = $bookingpress_google_calendar_event_link;
                        }
                        if( $bookingpress_from_time > $bookingpress_to_time ){
                            $appointment_data[$k]['bookingpress_show_google_meet_url'] = false;
                        }   
                    }
                } 
            }
            return $appointment_data;
        }

        function bookingpress_add_dynamic_buttons_for_view_appointments_func() {
            global $BookingPressPro;
            if ( $BookingPressPro->bookingpress_check_capability( 'bookingpress_payments' ) ) {            
            ?>
                <el-button class="bpa-btn bookingpress_google_meet_url_btn" v-if="scope.row.bookingpress_show_google_meet_url == true && scope.row.bookingpress_google_calendar_event_link != ''" @click="bookingpress_redirect_to_google_meet_url(scope.row.bookingpress_google_calendar_event_link)">
                <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_1071_6958)">
                    <path d="M12.4512 9.0338C12.4527 9.67483 12.4651 10.3166 12.4519 10.9576C12.4403 11.5389 11.9536 11.9942 11.3599 11.9978C10.5366 12.0022 9.71411 11.9993 8.89087 11.9993C7.4207 11.9993 5.95052 11.9993 4.48034 11.9993C4.41259 11.9993 4.3441 11.9957 4.27635 11.9942C4.27562 10.9081 4.27417 9.82198 4.27344 8.73659L4.28291 8.72712C5.85945 8.72785 7.43599 8.72567 9.01254 8.73222C9.15533 8.73295 9.18957 8.6958 9.18811 8.55521C9.18301 7.69346 9.18739 6.83171 9.18957 5.96996C9.19904 5.96923 9.20851 5.9685 9.21726 5.96777C9.21944 6.04645 9.28209 6.08505 9.33091 6.12949C9.87585 6.63066 10.4171 7.13547 10.9716 7.62571C11.1063 7.74518 11.2411 7.86683 11.3737 7.98775C11.7023 8.28933 12.0498 8.56978 12.355 8.89685C12.3667 8.90851 12.3776 8.92016 12.3893 8.93254C12.3886 8.97771 12.3835 9.02724 12.4512 9.0338Z" fill="#727E95"/>
                    <path d="M9.21626 5.96534C9.20679 5.96607 9.19732 5.9668 9.18858 5.96753C9.18712 5.12253 9.18275 4.27681 9.18712 3.43181C9.18785 3.30579 9.15725 3.26864 9.0283 3.27083C8.55111 3.27884 8.07465 3.27374 7.59819 3.27447C6.49155 3.27592 5.38491 3.27811 4.279 3.27957L4.26953 3.26937C4.27026 2.18544 4.27026 1.10224 4.27099 0.0183184C4.33073 -0.010091 4.39411 0.00302101 4.45531 0.00302101C6.73124 0.00229256 9.00644 0.00229256 11.2824 0.00229256C11.9949 0.00229256 12.4539 0.460485 12.456 1.16999C12.4575 1.78261 12.459 2.39523 12.4604 3.00786C12.4582 3.01733 12.456 3.02753 12.4531 3.037C12.4305 3.05375 12.4065 3.06832 12.3861 3.08653C11.839 3.57896 11.2904 4.07066 10.7455 4.56527C10.2333 5.03075 9.72551 5.49914 9.21626 5.96534Z" fill="#727E95"/>
                    <path d="M12.452 3.03742C12.4542 3.02795 12.4564 3.01775 12.4593 3.00828C12.7558 2.74459 13.0538 2.48235 13.3481 2.21719C13.6388 1.95495 13.9287 1.69125 14.2158 1.42537C14.4023 1.25273 14.6084 1.17479 14.8525 1.28333C15.0936 1.39041 15.1825 1.58927 15.1825 1.84641C15.1811 4.61669 15.1811 7.38624 15.1825 10.1565C15.1825 10.4137 15.0915 10.6118 14.8496 10.7181C14.6055 10.8252 14.4001 10.7444 14.2128 10.5732C13.6737 10.0778 13.1317 9.58541 12.5875 9.09517C12.5518 9.06312 12.5285 8.97425 12.4483 9.03325C12.3806 9.0267 12.3864 8.97716 12.3871 8.92981C12.5081 8.95968 12.4513 8.86134 12.4513 8.82637C12.4542 6.89672 12.4527 4.96707 12.452 3.03742Z" fill="#727E95"/>
                    <path d="M1.00893 8.72825C1.00674 7.93425 1.00456 7.14024 1.00383 6.34624C1.00237 5.38105 1.0031 4.41658 1.00383 3.45212C1.00383 3.39093 0.987803 3.32756 1.02132 3.26855C2.10391 3.26855 3.18651 3.26855 4.26911 3.26855L4.27858 3.27875C4.27785 4.57757 4.27567 5.87712 4.27567 7.17593C4.27567 7.6924 4.27931 8.20814 4.2815 8.72461L4.27203 8.73408C3.18433 8.73262 2.09663 8.73044 1.00893 8.72825Z" fill="#727E95"/>
                    <path d="M1.01017 8.72852C2.09787 8.7307 3.18556 8.73289 4.27326 8.73507C4.27399 9.82118 4.27545 10.9073 4.27618 11.9927C3.52725 11.9941 2.77904 12.008 2.03084 11.9927C1.47934 11.981 1.01672 11.5075 1.00871 10.9568C0.997783 10.2138 1.00871 9.4708 1.01017 8.72852Z" fill="#727E95"/>
                    <path d="M4.26733 3.26961C3.18473 3.26961 2.10213 3.26961 1.01953 3.26961C1.04066 3.24338 1.06033 3.21497 1.08364 3.19093C2.12763 2.14707 3.17161 1.10321 4.2156 0.0593476C4.23163 0.0440503 4.2513 0.0323951 4.26878 0.0185547C4.26805 1.10248 4.26805 2.18568 4.26733 3.26961Z" fill="#727E95"/>
                    <path d="M12.451 3.0376C12.4517 4.96725 12.4532 6.8969 12.451 8.82655C12.451 8.86079 12.5078 8.95913 12.3869 8.92999C12.3752 8.91833 12.3643 8.90668 12.3526 8.8943C12.0474 8.56795 11.6999 8.28677 11.3713 7.9852C11.2387 7.86355 11.1039 7.7419 10.9691 7.62316C10.4147 7.13292 9.87344 6.6281 9.32849 6.12693C9.28041 6.0825 9.21703 6.04389 9.21484 5.96522C9.72409 5.49829 10.2319 5.0299 10.7433 4.56588C11.2882 4.07053 11.8368 3.57956 12.384 3.08713C12.4036 3.06892 12.4284 3.05435 12.451 3.0376Z" fill="#727E95"/>
                    </g>
                    <defs>
                    <clipPath id="clip0_1071_6958">
                    <rect width="17" height="12" fill="white"/>
                    </clipPath>
                    </defs>
                </svg>
                <?php esc_html_e( 'Google Meet', 'bookingpress-google-calendar' ); ?>
                </el-button>
            <?php
            }
        }

        function bookingpress_disabled_features_with_cron_func( $disabled_feature_lists ){

            $disabled_feature_lists[] = esc_html__( 'Appointment Synchronization with Google Calendar', 'bookingpress-google-calendar' );

            return $disabled_feature_lists;
        }

        function bookingpress_gc_receive_synchronization(){

            if( !empty( $_GET['bpa_action'] ) && 'bpa_gc_event_sync' == $_GET['bpa_action'] ){
                $bpa_x_google_resource_token = !empty( $_SERVER['HTTP_X_GOOG_CHANNEL_TOKEN'] ) ? $_SERVER['HTTP_X_GOOG_CHANNEL_TOKEN'] : ''; //phpcs:ignore
                $bpa_x_google_resource_id = !empty( $_SERVER['HTTP_X_GOOG_RESOURCE_ID'] ) ? $_SERVER['HTTP_X_GOOG_RESOURCE_ID'] : ''; //phpcs:ignore
                $bpa_x_google_resource_state = !empty( $_SERVER['HTTP_X_GOOG_RESOURCE_STATE'] ) ? $_SERVER['HTTP_X_GOOG_RESOURCE_STATE'] : ''; //phpcs:ignore
                $bpa_x_google_channel_id = !empty( $_SERVER['HTTP_X_GOOG_CHANNEL_ID'] ) ? $_SERVER['HTTP_X_GOOG_CHANNEL_ID'] : ''; //phpcs:ignore

                if( empty( $bpa_x_google_channel_id ) || empty( $bpa_x_google_resource_id) || empty( $bpa_x_google_resource_state ) || empty( $bpa_x_google_resource_token) ){
                    return;
                }

                /** stop processing request if the new watch/channel created */
                if( 'exists' != $bpa_x_google_resource_state ){
                    return;
                }

                /** stop processing synchronization if the token doesn't match */
                if( !preg_match( '/bookingpress_gc_watch\-([\d+])/', $bpa_x_google_resource_token ) ){
                    return;
                }

                /** Extract the staffmember ID from the token */
                $bpa_staffmember_id = preg_replace( '/bookingpress_gc_watch\-/', '', $bpa_x_google_resource_token );
                

                if( empty( $bpa_staffmember_id ) ){
                    return;
                }

                /** Check HTTP_X_GOOG_CHANNEL_ID & HTTP_X_GOOG_RESOURCE_ID with the staff details */
                global $bookingpress_pro_staff_members;
                $bpa_staff_watch_details = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bpa_staffmember_id, 'bookingpress_gc_watch_details' );

                /** if the watch details is empty then return without performing action */
                if( empty( $bpa_staff_watch_details ) ){
                    return;
                }

                /** if the watch details is not empty, then check if the received channel id & resource id is same with the staff details */
                $bpa_staff_watch_data = json_decode( $bpa_staff_watch_details, true );
                $bpa_staff_watch_channel_id = $bpa_staff_watch_data['id'];
                $bpa_staff_watch_resource_id = $bpa_staff_watch_data['resourceId'];

                if( $bpa_staff_watch_channel_id == $bpa_x_google_channel_id && $bpa_staff_watch_resource_id == $bpa_x_google_resource_id ){
                    $this->bookingpress_gc_synchronize_staff_calendar( $bpa_staffmember_id );
                }
            }
        }

        function bookingpress_gc_synchronize_staff_calendar( $bookingpress_staffmember_id ){
            global $wpdb, $BookingPress, $bookingpress_pro_staff_members, $bookingpress_pro_appointment_bookings;

            if( empty( $bookingpress_staffmember_id ) ){
                return;
            }

            $bookingpress_enable_google_calendar_tmp = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_staff_gcalendar_enable' );
            $bookingpress_enable_google_calendar = ($bookingpress_enable_google_calendar_tmp == "true") ? true : false;

            if( true == $bookingpress_enable_google_calendar ){
                
                $bookingpress_google_service = $this->bookingpress_get_staff_google_calendar_service( $bookingpress_staffmember_id );

                if( empty( $bookingpress_google_service ) || null == $bookingpress_google_service ){
                    return;
                }

                $calendarId = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_selected_gcalendar' );
                
                $staffmember_cal_oauth_data = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_staff_gcalendar_auth' );

                $bookingpress_max_days_for_booking          = $BookingPress->bookingpress_get_settings( 'period_available_for_booking', 'general_setting' );

                $staff_member_oauth_data = json_decode( $staffmember_cal_oauth_data, true );
                
                $current_date = date('Y-m-d', current_time('timestamp'));
                $end_date = date( 'Y-m-d', strtotime('+' . $bookingpress_max_days_for_booking . ' days') );

                $start_time = '00:00:00';
                $end_time = '23:59:59';

                $timeMin = $bookingpress_pro_appointment_bookings->bookingpress_convert_date_time_to_utc( $current_date, $start_time, true );
                $timeMax = $bookingpress_pro_appointment_bookings->bookingpress_convert_date_time_to_utc( $end_date, $end_time, true );

                $service_arr = array(
                    'timeMin' => $timeMin,
                    'timeMax' => $timeMax,
                    'singleEvents' => true,
                    'maxResults' => 1000 /** retrieving first 1000 results from the current date */
                );

                $staff_gcalendar_events = $bookingpress_google_service->events->listEvents( $calendarId, $service_arr );
                $staff_gcalendar_event_list = $staff_gcalendar_events->getItems();
                

                $staff_gc_event_data = array(
                    $calendarId => array()
                );

                $event_dates = array();
                foreach( $staff_gcalendar_event_list as $event_data ){

                    $event_id = $event_data->id;

                    /**Check if slot is free then don't include it */
                    $event_transparency = $event_data->transparency;

                    if( !empty( $event_transparency ) && 'transparent' == $event_transparency){
                        continue;
                    }
                    
                    if( !isset( $event_data->getStart()->dateTime ) ){
                        
                        $event_timezone = $event_data->getStart()->timeZone;
    
                        if( empty( $event_timezone ) ){
                            $event_timezone = wp_timezone_string();
                        }

                        $start_date = $event_data->getStart()->date;
                        $end_date = $event_data->getEnd()->date;

                        $date_diff = strtotime( $end_date ) - strtotime( $start_date );

                        $diff_days = abs( round( $date_diff / 86400 ) );

                        if( 1 == $diff_days ){
                            $event_dates[ $event_data->id ] = array(
                                'timezone' => $event_timezone,
                                'start_date' => $start_date . ' 00:00:00',
                                'end_date' => $start_date .' 23:59:59'
                            );
                        } else {
                            $event_dates[ $event_data->id ] = array(
                                'timezone' => $event_timezone,
                                'start_date' => $start_date . ' 00:00:00',
                                'end_date' => date('Y-m-d', strtotime($end_date . '-1 day') ) .' 23:59:59'
                            );
                        }


                    } else {
    
                        $start_datetime = $event_data->getStart()->dateTime;
                        $end_datetime = $event_data->getEnd()->dateTime;
                        $timeZone = $event_data->getStart()->timeZone;
                        $current_timezone = wp_timezone_string();
                        
                        $start_dt = new DateTime( $start_datetime, new DateTimeZone( $timeZone ) );
                        $start_dt->setTimeZone( new DateTimeZone( $current_timezone ) );
                        $start_time = $start_dt->format( 'Y-m-d H:i:s');   
                        
                        $end_dt = new DateTime( $end_datetime, new DateTimeZone( $timeZone ) );
                        $end_dt->setTimeZone( new DateTimeZone( $current_timezone ) );
                        $end_time = $end_dt->format( 'Y-m-d H:i:s' );
                        
                        /** Add one second in start time to prevent blocking the previous time slot  */
                        $start_time_str = strtotime( $start_time );
                        
                        /** Substract one second in end time to prevent blocking the next time slot  */
                        $end_time_str = strtotime( $end_time );                    
                        
                        $event_dates[ $event_data->id ] = array(
                            'timezone' => $current_timezone,
                            'start_date' => date('Y-m-d H:i:s', $start_time_str ),
                            'end_date' => date('Y-m-d H:i:s', $end_time_str ),
                        );
                    }
                }

                $staff_gc_event_data[ $calendarId ] = $event_dates;
                
                $bookingpress_pro_staff_members->update_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_staff_gc_events', json_encode( $staff_gc_event_data ) );
                $bpa_fetch_expiration = current_time( 'timestamp' ) + ( MINUTE_IN_SECONDS * 5 );
                $bookingpress_pro_staff_members->update_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_staff_gc_event_fetch_expiration_time', $bpa_fetch_expiration );

                /** Check if the Google Watch is created & check the expiration time */

                $staff_gc_watch = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_gc_watch_details' );
                $staff_gc_expiration = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_gc_watch_expiry' );

                if( empty( $staff_gc_watch ) || ( !empty( $staff_gc_expiration ) && ( current_time('timestamp') * 1000 ) > $staff_gc_expiration ) ){

                    $gc_push_url = 'https://www.googleapis.com/calendar/v3/calendars/'.$calendarId.'/events/watch';

                    $access_token = $staff_member_oauth_data['access_token'];

                    /** Setting up one month expiry date for the watch */
                    $gc_watch_expiry = current_time( 'timestamp' ) + ( 60 * 60 * 24 * 31 );
                    $gc_watch_expiry_timestamp = strtotime( date('Y-m-d', $gc_watch_expiry ) . ' 00:00:00') * 1000; // convert it into ms 

                    $webhook_address = get_home_url() . '/?bpa_action=bpa_gc_event_sync';

                    $gc_watch_params = array(
                        'id' => $this->bookingpress_generate_unique_channel_id(),
                        'type' => 'web_hook',
                        'address' => $webhook_address,
                        'token' => 'bookingpress_gc_watch-'.$bookingpress_staffmember_id,
                        'expiration' => $gc_watch_expiry_timestamp
                    );

                    $arguments = array(
                        'timeout' => 4500,
                        'headers' => array(
                            'Authorization' => 'Bearer ' . $access_token,
                            'Content-Type' => 'application/json'
                        ),
                        'body' => json_encode( $gc_watch_params )
                    );

                    $api_call = wp_remote_post( $gc_push_url, $arguments );

                    if( is_wp_error( $api_call ) ){
                        /** Place log here for api call error */
                    } else {
                        $api_body = wp_remote_retrieve_body( $api_call );
                        $api_body_data = json_decode( $api_body, true );

                        if( !empty( $api_body_data['id'] ) && !empty( $api_body_data['kind'] ) ){
                            $staff_watch_data = json_encode( $api_body_data );

                            $staff_watch_expiry = $api_body_data['expiration'];
                            
                            $bookingpress_pro_staff_members->update_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_gc_watch_details', $staff_watch_data );
                            $bookingpress_pro_staff_members->update_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_gc_watch_expiry', $staff_watch_expiry );
                        } else {
                            /** Place log here for api call error */
                        }
                    }
                }
                
            }
        }

        function bookingpress_generate_unique_channel_id( $data = null ){
            // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
            $data = $data ?? random_bytes(16);
            assert(strlen($data) == 16);

            // Set version to 0100
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
            // Set bits 6-7 to 10
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

            // Output the 36 character UUID.
            return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        }

        function bookingpress_add_frontend_css_func(){                        
            global $bookingpress_appointment_bookings;
            if(isset($bookingpress_appointment_bookings->bookingpress_mybooking_login_user_id) && $bookingpress_appointment_bookings->bookingpress_mybooking_login_user_id){
                $bookingpress_custom_css = '
                .bpa-connect-upper{
                    display:flex;
                }
                .bpa-connect-upper .el-tag{
                    margin-left:15px;
                }                
                .bpa-connect-upper-mobile{
                    display:flex;
                    align-items: end;
                    justify-content: space-between;
                    width: 100%;                    
                }
                .bpa-connect-action-btn{
                    display:flex;
                }
                .bpa-connect-action-btn .bpa-zoom-gogole-link{
                    margin-right: 0px;
                    margin-left: 10px;
                }
                .bpa-connect-upper-mobile .bpa-left__service-detail{
                    width: 65%;
                }
                .bpa-zoom-gogole-link{
                    padding: 4px 10px;
                    border: 1px solid;
                    border-color: var(--bpa-gt-gray-400);
                    margin-right: 10px;
                    border-radius: 6px;                    
                    padding-top: 8px;
                } 
                .bpa-connect-upper-mobile .bpa-zoom-gogole-link{
                    padding-top: 8px;
                }
                .bpa-zoom-gogole-link svg{
                    fill:var(--bpa-dt-black-300);
                    width:20px;
                    height:15px;
                }
                .bpa-zoom-gogole-link:hover{
                    background-color:var(--wp--preset--color--vivid-green-cyan);                
                }
                .bpa-zoom-gogole-link:hover > svg *{
                    fill:#ffffff;                    
                }

                ';
                wp_add_inline_style( 'bookingpress_front_mybookings_custom_css', $bookingpress_custom_css, 'after' );    
            }
        }

        function bookingpress_generate_my_booking_customize_css_func($bookingpress_customize_css_content,$bookingpress_custom_data_arr) {

            $border_color  = $bookingpress_custom_data_arr['my_booking_form']['border_color'];
            $primary_color              = $bookingpress_custom_data_arr['my_booking_form']['primary_color'];
            $content_color              = $bookingpress_custom_data_arr['my_booking_form']['content_color'];

            $bookingpress_customize_css_content.='
                .bpa-zoom-gogole-link > svg *{
                    fill:'.$content_color.';
                }            
                .bpa-zoom-gogole-link{                    
                    border-color: '.$border_color.' !important;
                } 
                .bpa-zoom-gogole-link:hover{
                    background-color: '.$primary_color.' !important;               
                }                               
            ';
            return $bookingpress_customize_css_content;
            
        }

        function bookingpress_integration_connect_extra_link_func(){
        ?>
            <el-tooltip effect="dark" content="<?php esc_html_e('Google Meet', 'bookingpress-google-calendar'); ?>" placement="top"  open-delay="300">
                <a target="_blank" class="bpa-zoom-gogole-link" v-if="('undefined' != typeof scope.row.bookingpress_show_google_meet_url && scope.row.bookingpress_show_google_meet_url == true)" :href="scope.row.bookingpress_google_calendar_event_link">
                <svg width="17" height="12" viewBox="0 0 17 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_1071_6958)">
                    <path d="M12.4512 9.0338C12.4527 9.67483 12.4651 10.3166 12.4519 10.9576C12.4403 11.5389 11.9536 11.9942 11.3599 11.9978C10.5366 12.0022 9.71411 11.9993 8.89087 11.9993C7.4207 11.9993 5.95052 11.9993 4.48034 11.9993C4.41259 11.9993 4.3441 11.9957 4.27635 11.9942C4.27562 10.9081 4.27417 9.82198 4.27344 8.73659L4.28291 8.72712C5.85945 8.72785 7.43599 8.72567 9.01254 8.73222C9.15533 8.73295 9.18957 8.6958 9.18811 8.55521C9.18301 7.69346 9.18739 6.83171 9.18957 5.96996C9.19904 5.96923 9.20851 5.9685 9.21726 5.96777C9.21944 6.04645 9.28209 6.08505 9.33091 6.12949C9.87585 6.63066 10.4171 7.13547 10.9716 7.62571C11.1063 7.74518 11.2411 7.86683 11.3737 7.98775C11.7023 8.28933 12.0498 8.56978 12.355 8.89685C12.3667 8.90851 12.3776 8.92016 12.3893 8.93254C12.3886 8.97771 12.3835 9.02724 12.4512 9.0338Z" fill="#727E95"/>
                    <path d="M9.21626 5.96534C9.20679 5.96607 9.19732 5.9668 9.18858 5.96753C9.18712 5.12253 9.18275 4.27681 9.18712 3.43181C9.18785 3.30579 9.15725 3.26864 9.0283 3.27083C8.55111 3.27884 8.07465 3.27374 7.59819 3.27447C6.49155 3.27592 5.38491 3.27811 4.279 3.27957L4.26953 3.26937C4.27026 2.18544 4.27026 1.10224 4.27099 0.0183184C4.33073 -0.010091 4.39411 0.00302101 4.45531 0.00302101C6.73124 0.00229256 9.00644 0.00229256 11.2824 0.00229256C11.9949 0.00229256 12.4539 0.460485 12.456 1.16999C12.4575 1.78261 12.459 2.39523 12.4604 3.00786C12.4582 3.01733 12.456 3.02753 12.4531 3.037C12.4305 3.05375 12.4065 3.06832 12.3861 3.08653C11.839 3.57896 11.2904 4.07066 10.7455 4.56527C10.2333 5.03075 9.72551 5.49914 9.21626 5.96534Z" fill="#727E95"/>
                    <path d="M12.452 3.03742C12.4542 3.02795 12.4564 3.01775 12.4593 3.00828C12.7558 2.74459 13.0538 2.48235 13.3481 2.21719C13.6388 1.95495 13.9287 1.69125 14.2158 1.42537C14.4023 1.25273 14.6084 1.17479 14.8525 1.28333C15.0936 1.39041 15.1825 1.58927 15.1825 1.84641C15.1811 4.61669 15.1811 7.38624 15.1825 10.1565C15.1825 10.4137 15.0915 10.6118 14.8496 10.7181C14.6055 10.8252 14.4001 10.7444 14.2128 10.5732C13.6737 10.0778 13.1317 9.58541 12.5875 9.09517C12.5518 9.06312 12.5285 8.97425 12.4483 9.03325C12.3806 9.0267 12.3864 8.97716 12.3871 8.92981C12.5081 8.95968 12.4513 8.86134 12.4513 8.82637C12.4542 6.89672 12.4527 4.96707 12.452 3.03742Z" fill="#727E95"/>
                    <path d="M1.00893 8.72825C1.00674 7.93425 1.00456 7.14024 1.00383 6.34624C1.00237 5.38105 1.0031 4.41658 1.00383 3.45212C1.00383 3.39093 0.987803 3.32756 1.02132 3.26855C2.10391 3.26855 3.18651 3.26855 4.26911 3.26855L4.27858 3.27875C4.27785 4.57757 4.27567 5.87712 4.27567 7.17593C4.27567 7.6924 4.27931 8.20814 4.2815 8.72461L4.27203 8.73408C3.18433 8.73262 2.09663 8.73044 1.00893 8.72825Z" fill="#727E95"/>
                    <path d="M1.01017 8.72852C2.09787 8.7307 3.18556 8.73289 4.27326 8.73507C4.27399 9.82118 4.27545 10.9073 4.27618 11.9927C3.52725 11.9941 2.77904 12.008 2.03084 11.9927C1.47934 11.981 1.01672 11.5075 1.00871 10.9568C0.997783 10.2138 1.00871 9.4708 1.01017 8.72852Z" fill="#727E95"/>
                    <path d="M4.26733 3.26961C3.18473 3.26961 2.10213 3.26961 1.01953 3.26961C1.04066 3.24338 1.06033 3.21497 1.08364 3.19093C2.12763 2.14707 3.17161 1.10321 4.2156 0.0593476C4.23163 0.0440503 4.2513 0.0323951 4.26878 0.0185547C4.26805 1.10248 4.26805 2.18568 4.26733 3.26961Z" fill="#727E95"/>
                    <path d="M12.451 3.0376C12.4517 4.96725 12.4532 6.8969 12.451 8.82655C12.451 8.86079 12.5078 8.95913 12.3869 8.92999C12.3752 8.91833 12.3643 8.90668 12.3526 8.8943C12.0474 8.56795 11.6999 8.28677 11.3713 7.9852C11.2387 7.86355 11.1039 7.7419 10.9691 7.62316C10.4147 7.13292 9.87344 6.6281 9.32849 6.12693C9.28041 6.0825 9.21703 6.04389 9.21484 5.96522C9.72409 5.49829 10.2319 5.0299 10.7433 4.56588C11.2882 4.07053 11.8368 3.57956 12.384 3.08713C12.4036 3.06892 12.4284 3.05435 12.451 3.0376Z" fill="#727E95"/>
                    </g>
                    <defs>
                    <clipPath id="clip0_1071_6958">
                    <rect width="17" height="12" fill="white"/>
                    </clipPath>
                    </defs>
                </svg>
                </a>
            </el-tooltip>	        
        <?php 
        }

        /**
         * Function for show google meet url in my appointment page
         *
         * @param  mixed $bookingpress_appointments_data
         * @return void
         */
        function bookingpress_modify_my_appointments_data_externally_fun($bookingpress_appointments_data){           
            $bookingpress_from_time = current_time('timestamp');
            $bookingpress_to_time = strtotime($bookingpress_appointments_data['bookingpress_appointment_date'] .' '. $bookingpress_appointments_data['bookingpress_appointment_time']);
            $show_google_meet_url = true;
            if( $bookingpress_from_time > $bookingpress_to_time ){
                $show_google_meet_url = false;
            }            
            if(is_null($bookingpress_appointments_data['bookingpress_google_calendar_event_link']) || $bookingpress_appointments_data['bookingpress_google_calendar_event_link'] == ''){
                $show_google_meet_url = false;
            }            
            if($bookingpress_appointments_data['bookingpress_appointment_status'] != 1){
                $show_google_meet_url = false;
            }
            $bookingpress_appointments_data['bookingpress_show_google_meet_url'] = $show_google_meet_url;
            return $bookingpress_appointments_data;
        }
        function bpa_add_extra_tab_outside_func_arr() { ?>

            var bpa_get_setting_tab = bpa_get_url_param.get('setting_tab');
            if( bpa_get_page == 'bookingpress_settings'){
                
                if( selected_tab_name == 'integration_settings' && vm.bpa_integration_active_tab == 'google_calendar'){
                    vm.openNeedHelper("list_google_calendar_settings", "google_calendar_settings", "Google Calendar Settings");
                    vm.bpa_fab_floating_btn = 0;

                } else if ( null == selected_tab_name && 'integration_settings' == bpa_get_setting_page && 'google_calendar' == bpa_get_setting_tab ){
                    vm.openNeedHelper("list_google_calendar_settings", "google_calendar_settings", "Google Calendar Settings");
                    vm.bpa_fab_floating_btn = 0;
                }
            }
        <?php }

        function bookingpress_admin_enqueue_scripts(){
            
            if( !empty( $_GET['page'] ) && ('bookingpress_staff_members' == $_GET['page'] || 'bookingpress_myprofile' == $_GET['page']) ){
                wp_add_inline_style( 'bookingpress_pro_admin_css', '.bpa-dialog--fullscreen .bpa-dialog-body{ padding-bottom: 140px; } .google_calendar_staff_member_module_refresh.el-tooltip__popper { z-index: 9997 !important;} .bookingpress-staff-gc-refresh-col{ padding: 0 !important; @keyframes rotate { from { transform: rotate(0deg); } to { transform: rotate(-180deg);} } .gc-refresh-rotate { animation: rotate 2s linear infinite; animation-direction: reverse; } }  ' );
            }
            if( !empty( $_GET['page'] ) && ('bookingpress' == $_GET['page'] || 'bookingpress_appointments' == $_GET['page']) ){
                wp_add_inline_style( 'bookingpress_pro_admin_css', '.bpa-vac--head__right .bpa-btn.bookingpress_google_meet_url_btn { margin-left: 16px;} body.rtl .bpa-vac--head__right .bpa-btn.bookingpress_google_meet_url_btn { margin-left: 0px; margin-right: 16px;} .bpa-manage-appointment-items .bpa-hw-right-btn-group.bpa-vac--head__right { align-items: end; }' );
            }
            if( !empty( $_GET['page'] ) && 'bookingpress_calendar' == $_GET['page'] ){
                wp_add_inline_style( 'bookingpress_pro_admin_css', '.bpa-iec-body__customer-detail-external-btns .bpa-cal-google-meet-link {display: inline-block; width: 36px; height: 26px; border: 1px solid #CFD6E5; border-radius: 4px; } .bpa-cal-zoom-link + .bpa-cal-google-meet-link {margin-left: 10px;} .bpa-cal-google-meet-link svg { padding: 5px 8px 5px 8px;} .bookingpress-gc-cal-popover.el-tooltip__popper { z-index: 9997 !important;} body.rtl .bpa-cal-zoom-link + .bpa-cal-google-meet-link {margin-left: 0px; margin-right: 10px; }' );
            }
        }

        function bookingpress_display_gc_token_validity_notices(){
            if( !is_admin() ){
                return;
            }
            global $wpdb, $tbl_bookingpress_staffmembers, $tbl_bookingpress_staffmembers_meta;
            $current_user_id = get_current_user_id();
            $user_obj = new WP_User( $current_user_id );
            $user_roles = $user_obj->roles;
            if( !empty( $user_roles ) && in_array( 'bookingpress-staffmember', $user_roles) ){
                $staffmember_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_staffmember_id FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_wpuser_id = %d AND bookingpress_staffmember_status = %d", $current_user_id, 1 ) ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name. false alarm
                if( !empty( $staffmember_data ) ){
                    $staffmember_id = $staffmember_data->bookingpress_staffmember_id;
                    global $bookingpress_pro_staff_members;
                    $display_notice = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staffmember_id, 'bookingpress_display_invalid_token_notice');
                    $is_google_calendar_enable = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staffmember_id, 'bookingpress_staff_gcalendar_enable' );
                    if( true == $display_notice && true == $is_google_calendar_enable ){

                        $staffmember_redirect_link = "";
                        if( in_array( 'administrator', $user_roles ) ){
                            $staffmember_redirect_link = esc_url(admin_url( 'admin.php?page=bookingpress_staff_members&reauthgc=true&staffid=' . $staffmember_id ));
                        } else {
                            $staffmember_redirect_link = esc_url(admin_url( 'admin.php?page=bookingpress_myprofile&reauthgc=true&bpa_page=profile&staffid=' . $staffmember_id  ));
                        }

                        ?>
                            <div class="bpa-pg-warning-belt-box">
                                <p class="bpa-wbb__desc">
                                    <span class="material-icons-round">warning</span>
                                    <?php /* translators: 1. Staff member redirection link */ ?>
                                    <?php echo sprintf( esc_html__('Your access token for Google Calendar has been either expired or revoked. You need to re-authenticate from %s.', 'bookingpress-google-calendar'), '<a href="'.$staffmember_redirect_link.'">here</a>' ); //phpcs:ignore ?> 
                                </p>
                            </div>
                        <?php
                    }
                }
            }
            
        }

        function bookingpress_reauth_google_calendar(){
            if( !empty( $_GET['reauthgc'] ) && !empty( $_GET['staffid'] ) && true == $_GET['reauthgc'] && is_admin() ){
                $staff_id = intval( $_GET['staffid'] );
                if( !isset( $_GET['bpa_page'] ) ){
                    ?>
                    const vm = this;
                    vm.editStaffMember( <?php echo $staff_id; //phpcs:ignore ?> );
                    <?php
                }
            }
        }

        function bookingpress_reauth_google_calendar_myprofile(){
            if( !empty( $_GET['reauthgc'] ) && !empty( $_GET['staffid'] ) && true == $_GET['reauthgc'] && is_admin() && isset( $_GET['bpa_page'] ) ){
                ?>
                (function($this){

                    setTimeout(function(){
                        let gc_module = document.getElementById('google_calendar_staff_member_module');
                        setTimeout( function(){
                            gc_module.style.backgroundColor = "#DEFFF2";
                            gc_module.style.transition = "background-color 0.3s ease-in-out";
                        },400);
                        setTimeout( function(){
                            gc_module.style.backgroundColor = "";
                            gc_module.style.transition = "background-color 0.3s ease-in-out";
                        },800);
                        console.log( $this );
                        let url_string = $this.bookingpress_gc_remove_params_from_url( document.URL, "reauthgc" );
                        url_string = $this.bookingpress_gc_remove_params_from_url( url_string, "staffid" );
                        url_string = $this.bookingpress_gc_remove_params_from_url( url_string, "bpa_page" );
                        window.history.pushState({path: url_string}, '', url_string);
                    },2500)
                })(this);
                <?php
            }
        }

        function bookingpress_move_control_to_gcalendar(){
            if( !empty( $_GET['reauthgc'] ) && !empty( $_GET['staffid'] ) && true == $_GET['reauthgc'] && is_admin() ){
                if( !isset( $_GET['bpa_page'] ) ){
                    ?>
                    let dialog_wrapper = document.querySelector('.el-dialog__wrapper');
                    let gc_module = dialog_wrapper.querySelector('#google_calendar_staff_member_module');                    
                    let dialog_container = dialog_wrapper.querySelector('.bpa-dialog--staff-modal');
                    if( null != dialog_container  && null != gc_module ){
                        let gc_module_top = gc_module.getBoundingClientRect().top + 100;
                        gc_module_top = Math.round( gc_module_top );
                        dialog_container.scrollTo({
                            top: gc_module_top,
                        });
                        setTimeout( function(){
                            gc_module.style.backgroundColor = "#DEFFF2";
                            gc_module.style.transition = "background-color 0.3s ease-in-out";
                        },400);
                        setTimeout( function(){
                            gc_module.style.backgroundColor = "";
                            gc_module.style.transition = "background-color 0.3s ease-in-out";
                        },800);

                    }
                    let url_string = vm2.bookingpress_gc_remove_params_from_url( document.URL, "reauthgc" );
                    url_string = vm2.bookingpress_gc_remove_params_from_url( url_string, "staffid" );
                    window.history.pushState({path: url_string}, '', url_string);
                    <?php
                }
            }
        }

        function bookingpress_modify_email_content_func($template_content, $bookingpress_appointment_data,$notification_name = ''){
            global $BookingPress;
            if(!empty($bookingpress_appointment_data)){
                $bookingpress_google_meet_url = !empty($bookingpress_appointment_data['bookingpress_google_calendar_event_link']) ? $bookingpress_appointment_data['bookingpress_google_calendar_event_link'] : '';
                $template_content = str_replace('%google_meet_url%', $bookingpress_google_meet_url, $template_content);
            }
            return $template_content;
        }

        function bookingpress_add_global_option_data_func($global_data){
            $bookingpress_email_appointment_placeholders = json_decode($global_data['appointment_placeholders'], TRUE);
            if(is_plugin_active('bookingpress-zoom/bookingpress-zoom.php')) {
                $bookingpress_email_appointment_placeholders[] = array('value' => '%zoom_host_url%','name' => '%zoom_host_url%',);
                $bookingpress_email_appointment_placeholders[] = array('value' => '%zoom_join_url%','name' => '%zoom_join_url%',);
            } 
            $global_data['appointment_placeholders'] = wp_json_encode($bookingpress_email_appointment_placeholders);
            return $global_data;
        }

        function bookingpress_modify_capability_data_func($bpa_caps){
            $bpa_caps['bookingpress_staff_members'][] = 'google_calendar_signout';
            $bpa_caps['bookingpress_settings'][] = 'google_calendar_signout';
            $bpa_caps['bookingpress_myprofile'][] = 'google_calendar_signout';

            $bpa_caps['bookingpress_staff_members'][] = 'google_calendar_refresh_list';
            $bpa_caps['bookingpress_settings'][] = 'google_calendar_refresh_list';
            $bpa_caps['bookingpress_myprofile'][] = 'google_calendar_refresh_list';
            return $bpa_caps;
        }

        function bookingpress_gc_cron_schedules( $schedules ){
            if ( ! isset( $schedules['15min'] ) ) {
				$schedules['15min'] = array(
					'interval' => 900,
					'display'  => __( 'Every 15 minutes', 'bookingpress-google-calendar' ),
				);
			}
			return $schedules;
        }
        
        /**
         * bookingpress_google_calendar_cron
         *
         * @return void
         */
        function bookingpress_google_calendar_cron(){
            //global $wpdb, $tbl_bookingpress_staffmembers, $tbl_bookingpress_staffmembers_meta;

            if( !wp_next_scheduled( 'bookingpress_validate_staffmember_token' ) ) {
                $data = wp_schedule_event( current_time('timestamp'), '15min', 'bookingpress_validate_staffmember_token' );
            }

        }

        function bookingpress_validate_staffmember_token_callback(){
            global $wpdb, $tbl_bookingpress_staffmembers, $tbl_bookingpress_staffmembers_meta;

            $get_staffwith_gc = $wpdb->get_results( $wpdb->prepare( "SELECT bps.bookingpress_staffmember_id FROM {$tbl_bookingpress_staffmembers} bps LEFT JOIN {$tbl_bookingpress_staffmembers_meta} bpsm ON bps.bookingpress_staffmember_id=bpsm.bookingpress_staffmember_id WHERE bps.bookingpress_staffmember_status = %d AND bpsm.bookingpress_staffmembermeta_key = %s AND bpsm.bookingpress_staffmembermeta_value = %s", 1, 'bookingpress_staff_gcalendar_enable', 'true' ) ); // phpcs:ignore

            if( !empty( $get_staffwith_gc ) ){
                foreach( $get_staffwith_gc as $staffmember_data ){
                    $staffmember_id = $staffmember_data->bookingpress_staffmember_id;
                    $this->bookingpress_get_staff_google_calendar_service( $staffmember_id, true );
                }
            }
        }

        function bookingpress_modify_readmore_link_func(){
            ?>
                var selected_tab = sessionStorage.getItem("current_tabname");
                if(selected_tab == "integration_settings"){
                    if(vm.bpa_integration_active_tab == ""){
                        read_more_link = "https://www.bookingpressplugin.com/documents/google-calendar-integration/";
                    }
                    if(vm.bpa_integration_active_tab == "google_calendar"){
                        read_more_link = "https://www.bookingpressplugin.com/documents/google-calendar-integration/";
                    }
                }
            <?php
        }

        function bookingpress_save_google_calendar_settings( $bookingpress_save_settings_data, $posted_data ){

            global $BookingPress, $bookingpress_global_options, $wpdb, $tbl_bookingpress_settings, $bookingpress_pro_staff_members;

            $bookingpress_global_options_data = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_allow_tag = json_decode($bookingpress_global_options_data['allowed_html'], true);

            $bookingpress_db_gc_client_id = $wpdb->get_row( $wpdb->prepare( "SELECT setting_value FROM {$tbl_bookingpress_settings} WHERE setting_name = %s", 'google_calendar_client_id') ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_settings is table name defined globally.
            $bookingpress_db_gc_client_sec = $wpdb->get_row( $wpdb->prepare( "SELECT setting_value FROM {$tbl_bookingpress_settings} WHERE setting_name = %s", 'google_calendar_client_secret' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_settings is table name defined globally.
            
            $reset_auth_flag = false;

            if( !empty( $posted_data['google_calendar_client_id'] ) && !empty( $bookingpress_db_gc_client_id ) ){
                $google_calendar_client_id = $bookingpress_db_gc_client_id->setting_value;
                
                if( $google_calendar_client_id != $posted_data['google_calendar_client_id'] ){
                    $reset_auth_flag = true;
                }
            }

            if( false == $reset_auth_flag && !empty( $posted_data['google_calendar_client_secret'] ) && !empty( $bookingpress_db_gc_client_sec ) ){
                $google_calendar_client_secret = $bookingpress_db_gc_client_sec->setting_value;
                if( $google_calendar_client_secret != $posted_data['google_calendar_client_secret'] ){
                    $reset_auth_flag = true;
                }
            }

            if( true == $reset_auth_flag ){
                global $tbl_bookingpress_staffmembers, $tbl_bookingpress_staffmembers_meta;
                $allStaffmember = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_staffmember_id FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_staffmember_status = %d ", 1 ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_staffmembers is table name defined globally.

                if( !empty( $allStaffmember ) ){
                    foreach( $allStaffmember as $staff_data ){
                        $staffmember_id = $staff_data->bookingpress_staffmember_id;
                        $is_gc_enable = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staffmember_id, 'bookingpress_staff_gcalendar_enable' );

                        if( true == $is_gc_enable ){
                            $bookingpress_pro_staff_members->update_bookingpress_staffmembersmeta( $staffmember_id, 'bookingpress_display_invalid_token_notice', true );
                        }
                    }
                }
            }
            
            $google_calendar_event_title = !empty( $bookingpress_save_settings_data['google_calendar_event_title'] ) ? wp_kses( $bookingpress_save_settings_data['google_calendar_event_title'], $bookingpress_allow_tag ) : '';
            if( !empty( $google_calendar_event_title ) ){
                
                $bookingpress_check_record_existance = $wpdb->get_var($wpdb->prepare("SELECT COUNT(setting_id) FROM `{$tbl_bookingpress_settings}` WHERE setting_name = %s AND setting_type = %s", 'google_calendar_event_title', 'google_calendar_setting')); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_settings is table name defined globally.
                
                if ($bookingpress_check_record_existance > 0 ) {
                    $bookingpress_update_data = array(
                        'setting_value' => wp_kses($google_calendar_event_title, $bookingpress_allow_tag),
                        'setting_type'  => 'google_calendar_setting',
                        'updated_at'    => current_time('mysql'),
                    );

                    $bpa_update_where_condition = array(
                        'setting_name' => 'google_calendar_event_title',
                        'setting_type' => 'google_calendar_setting',
                    );

                    $bpa_update_affected_rows = $wpdb->update($tbl_bookingpress_settings, $bookingpress_update_data, $bpa_update_where_condition);
                    if ($bpa_update_affected_rows > 0 ) {
                        wp_cache_delete('google_calendar_event_title');
                        wp_cache_set('google_calendar_event_title', $google_calendar_event_title);
                    }
                } else {
                    $bookingpress_insert_data = array(
                        'setting_name'  => 'google_calendar_event_title',
                        'setting_value' => wp_kses($google_calendar_event_title, $bookingpress_allow_tag),
                        'setting_type'  => 'google_calendar_setting',
                        'updated_at'    => current_time('mysql'),
                    );

                    $bookingpress_inserted_id = $wpdb->insert($tbl_bookingpress_settings, $bookingpress_insert_data);
                    if ($bookingpress_inserted_id > 0 ) {
                        wp_cache_delete('google_calendar_event_title');
                        wp_cache_set('google_calendar_event_title', $google_calendar_event_title);
                    }
                }

                unset( $bookingpress_save_settings_data['google_calendar_event_title'] );
            }

            $google_calendar_event_description = !empty( $bookingpress_save_settings_data['google_calendar_event_description'] ) ? wp_kses( $bookingpress_save_settings_data['google_calendar_event_description'], $bookingpress_allow_tag ) : '';
            if( !empty( $google_calendar_event_description ) ){

                $bookingpress_check_record_existance = $wpdb->get_var($wpdb->prepare("SELECT COUNT(setting_id) FROM `{$tbl_bookingpress_settings}` WHERE setting_name = %s AND setting_type = %s", 'google_calendar_event_description', 'google_calendar_setting')); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_settings is table name defined globally.
                
                if ($bookingpress_check_record_existance > 0 ) {
                    $bookingpress_update_data = array(
                        'setting_value' => wp_kses($google_calendar_event_description, $bookingpress_allow_tag),
                        'setting_type'  => 'google_calendar_setting',
                        'updated_at'    => current_time('mysql'),
                    );

                    $bpa_update_where_condition = array(
                        'setting_name' => 'google_calendar_event_description',
                        'setting_type' => 'google_calendar_setting',
                    );

                    $bpa_update_affected_rows = $wpdb->update($tbl_bookingpress_settings, $bookingpress_update_data, $bpa_update_where_condition);
                    if ($bpa_update_affected_rows > 0 ) {
                        wp_cache_delete('google_calendar_event_description');
                        wp_cache_set('google_calendar_event_description', $google_calendar_event_description);
                    }
                } else {
                    $bookingpress_insert_data = array(
                        'setting_name'  => 'google_calendar_event_description',
                        'setting_value' => wp_kses($google_calendar_event_description, $bookingpress_allow_tag),
                        'setting_type'  => 'google_calendar_setting',
                        'updated_at'    => current_time('mysql'),
                    );

                    $bookingpress_inserted_id = $wpdb->insert($tbl_bookingpress_settings, $bookingpress_insert_data);
                    if ($bookingpress_inserted_id > 0 ) {
                        wp_cache_delete('google_calendar_event_description');
                        wp_cache_set('google_calendar_event_description', $google_calendar_event_description);
                    }
                }

                unset( $bookingpress_save_settings_data['google_calendar_event_description'] );
            }


            return $bookingpress_save_settings_data;
        }
        function bookingpress_upgrade_google_calendar_data(){
            global $BookingPress, $bookingpress_google_calendar_version;
            $bookingpress_db_gc_version = get_option( 'bookingpress_google_calendar_version' );

            if( version_compare( $bookingpress_db_gc_version, '2.6', '<' ) ){
                $bookingpress_load_gc_update_file = BOOKINGPRESS_GOOGLE_CALENDAR_DIR . '/core/views/upgrade_latest_google_calendar_data.php';
                include $bookingpress_load_gc_update_file;
                $BookingPress->bookingpress_send_anonymous_data_cron();
            }
        }

        function bookingpress_signout_google_calendar(){

            $response              = array();
			$bpa_check_authorization = $this->bpa_check_authentication( 'google_calendar_signout', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-google-calendar');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-google-calendar');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $staffmember_id = !empty( $_POST['staffmember'] ) ? intval( $_POST['staffmember'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

            global $wpdb, $tbl_bookingpress_staffmembers, $tbl_bookingpress_staffmembers_meta, $bookingpress_pro_staff_members;

            $staff_auth_data = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_staff_gcalendar_auth' );

            $staff_auth_data = json_decode( $staff_auth_data, true );

            $staff_watch_details = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_gc_watch_details' );
            $wpdb->delete(
                $tbl_bookingpress_staffmembers_meta,
                array(
                    'bookingpress_staffmembermeta_key' => 'bookingpress_selected_gcalendar',
                    'bookingpress_staffmember_id' => $staffmember_id
                )
            );

            $wpdb->delete(
                $tbl_bookingpress_staffmembers_meta,
                array(
                    'bookingpress_staffmembermeta_key' => 'bookingpress_staff_gcalendar_list',
                    'bookingpress_staffmember_id' => $staffmember_id
                )
            );

            $wpdb->delete(
                $tbl_bookingpress_staffmembers_meta,
                array(
                    'bookingpress_staffmembermeta_key' => 'bookingpress_staff_gcalendar_auth',
                    'bookingpress_staffmember_id' => $staffmember_id
                )
            );

            if( !empty( $staff_watch_details ) && !empty( $staff_auth_data ) ){
                $auth_token = $staff_auth_data['access_token'];

                $params = json_encode(
                    array(
                        'id' => $staff_watch['id'],
                        'resourceId' => $staff_watch['resourceId']
                    )
                );

                $stop_watch_url = 'https://www.googleapis.com/calendar/v3/channels/stop';

                $arguments = array(
                    'timeout' => 4500,
                    'headers' => array(
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer '.$auth_token
                    ),
                    'body' => json_encode( $params )
                );

                $stop_watch_response = wp_remote_post( $stop_watch_url, $arguments );

                update_option( 'bookingpress_gc_stop_watch_signout_param_' . $staff_id, '['.json_encode( $stop_watch_response ).','. json_encode( $params ).']' );
            }

            $wpdb->delete(
                $tbl_bookingpress_staffmembers_meta,
                array(
                    'bookingpress_staffmembermeta_key' => 'bookingpress_staff_gc_events',
                    'bookingpress_staffmember_id' => $staffmember_id
                )
            );

            $wpdb->delete(
                $tbl_bookingpress_staffmembers_meta,
                array(
                    'bookingpress_staffmembermeta_key' => 'bookingpress_gc_watch_details',
                    'bookingpress_staffmember_id' => $staffmember_id
                )
            );

            $wpdb->delete(
                $tbl_bookingpress_staffmembers_meta,
                array(
                    'bookingpress_staffmembermeta_key' => 'bookingpress_gc_watch_expiry',
                    'bookingpress_staffmember_id' => $staffmember_id
                )
            );

            $response['variant'] = 'success';
            $response['title']   = esc_html__( 'Success', 'bookingpress-google-calendar' );
            $response['msg']     = esc_html__( 'Sign out successfully.', 'bookingpress-google-calendar' );

            echo wp_json_encode( $response );
            die;
        }


        function bookingpress_is_google_calendar_addon_activated($plugin,$network_activation)
        {  
            $myaddon_name = "bookingpress-google-calendar/bookingpress-google-calendar.php";

            if($plugin == $myaddon_name)
            {
                if(!(is_plugin_active('bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php')))
                {
                    deactivate_plugins($myaddon_name, FALSE);
                    $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name);
                    $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Google Calendar Add-on', 'bookingpress-google-calendar');
					$bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-google-calendar'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
					wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                    die;
                }

                $license = trim( get_option( 'bkp_license_key' ) );
                $package = trim( get_option( 'bkp_license_package' ) );

                if( '' === $license || false === $license ) 
                {
                    deactivate_plugins($myaddon_name, FALSE);
                    $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name);
                    $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Google Calendar Add-on', 'bookingpress-google-calendar');
					$bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-google-calendar'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
					wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                    die;
                }
                else
                {
                    $store_url = BOOKINGPRESS_GOOGLE_CALENDAR_STORE_URL;
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
                            $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Google Calendar Add-on', 'bookingpress-google-calendar');
                            $bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-google-calendar'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
                            wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                            die;
                        }

                    }
                    else
                    {
                        deactivate_plugins($myaddon_name, FALSE);
                        $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name);
                        $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Google Calendar Add-on', 'bookingpress-google-calendar');
                        $bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-google-calendar'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
                        wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                        die;
                    }
                }
            }

        }

        function bookingpress_addon_list_data_filter_func($bookingpress_body_res){
            global $bookingpress_slugs;
            if(!empty($bookingpress_body_res)) {
                foreach($bookingpress_body_res as $bookingpress_body_res_key =>$bookingpress_body_res_val) {
                    $bookingpress_setting_page_url = add_query_arg('page', $bookingpress_slugs->bookingpress_settings, esc_url( admin_url() . 'admin.php?page=bookingpress' ));
                    $bookingpress_config_url = add_query_arg('setting_page', 'integration_settings', $bookingpress_setting_page_url);
                    $bookingpress_config_url = add_query_arg('setting_tab', 'google_calendar', $bookingpress_config_url);
                    if($bookingpress_body_res_val['addon_key'] == 'bookingpress_google_calendar') {
                        $bookingpress_body_res[$bookingpress_body_res_key]['addon_configure_url'] = $bookingpress_config_url;
                    }
                }
            }
            return $bookingpress_body_res;
        }

        function bookingpress_load_integration_settings_data_func(){
            ?>
                vm.getSettingsData('google_calendar_setting','bookingpress_google_calendar')
                setTimeout(function(){
                    if(vm.$refs.bookingpress_google_calendar != undefined){
                        vm.$refs.bookingpress_google_calendar.clearValidate();
                    }
                }, 2000);
            <?php            
        }

        function bookingpress_available_integration_addon_list_func($bookingpress_integration_addon_list) {
            $bookingpress_integration_addon_list[] = 'google-calendar';
            return  $bookingpress_integration_addon_list;
        }

        function bookingpress_add_setting_dynamic_data_fields_func($bookingpress_dynamic_setting_data_fields) {
            
            global $BookingPress, $bookingpress_pro_staff_members,$bookingpress_notification_duration, $wpdb, $tbl_bookingpress_form_fields, $bookingpress_global_options;
            $this->bookingpress_global_data = $bookingpress_global_options->bookingpress_global_options();

            $bookingpress_dynamic_setting_data_fields['bookingpress_tab_list'][] = array(
                'tab_value' => 'google_calendar',
                'tab_name' => esc_html__('Google Calendar', 'bookingpress-google-calendar'),
            );        

            $bookingpress_dynamic_setting_data_fields['bookingpress_google_calendar']['google_calendar_client_id'] = '';
            $bookingpress_dynamic_setting_data_fields['bookingpress_google_calendar']['google_calendar_client_secret'] = '';
            $bookingpress_dynamic_setting_data_fields['bookingpress_google_calendar']['google_calendar_redirect_url'] = '';
            $bookingpress_dynamic_setting_data_fields['bookingpress_google_calendar']['google_calendar_event_title'] = '';
            $bookingpress_dynamic_setting_data_fields['bookingpress_google_calendar']['google_calendar_event_description'] = '';
            $bookingpress_dynamic_setting_data_fields['bookingpress_google_calendar']['google_calendar_event_location'] = '';
            $bookingpress_dynamic_setting_data_fields['bookingpress_google_calendar']['google_calendar_meet'] = '';
            $bookingpress_dynamic_setting_data_fields['bookingpress_google_calendar']['google_calendar_max_event'] = '';

            $is_staffmember_activated = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();
			$bookingpress_dynamic_setting_data_fields['is_staffmember_activated'] = $is_staffmember_activated;

            $bookingpress_dynamic_setting_data_fields['bookingpress_gcalendar_customer_placeholder'] = json_decode($this->bookingpress_global_data['customer_placeholders'],true);
            $bookingpress_dynamic_setting_data_fields['bookingpress_gcalendar_service_placeholder'] = json_decode($this->bookingpress_global_data['service_placeholders'],true);
            $bookingpress_dynamic_setting_data_fields['bookingpress_gcalendar_company_placeholder'] = json_decode($this->bookingpress_global_data['company_placeholders'],true);

            $bpa_form_fields = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_field_label,bookingpress_form_field_id FROM {$tbl_bookingpress_form_fields} WHERE ( bookingpress_field_type = %s OR bookingpress_field_type = %s OR bookingpress_field_type = %s ) AND bookingpress_is_customer_field = %d ORDER BY bookingpress_field_position ASC", 'text', 'textarea', 'dropdown', 0 ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally.
            
            $bpa_form_fields_arr = array(
                array(
                    'value' => '',
                    'label' => esc_html__( 'Select Field', 'bookingpress-google-calendar' )
                )
            );
            if( !empty( $bpa_form_fields ) ){
                foreach( $bpa_form_fields as $form_field_data ) {
                    $form_field_label = $form_field_data->bookingpress_field_label;
                    $form_field_key = $form_field_data->bookingpress_form_field_id;

                    $bpa_form_fields_arr[] = array(
                        'value' => $form_field_key,
                        'label' => $form_field_label
                    );
                }
            }
            $bookingpress_dynamic_setting_data_fields['bookingpress_gcalendar_form_fields'] = $bpa_form_fields_arr;
                        
            $bookingpress_appointment_placeholders = json_decode($this->bookingpress_global_data['appointment_placeholders'],true);       
            $bookingpress_appointment_custom_field_placeholder = json_decode($this->bookingpress_global_data['custom_fields_placeholders'],true);            

            $bookingpress_appointment_field_list[] = array(
                'field_group_name' => esc_html__('Basic fields', 'bookingpress-google-calendar'),
                'field_list' => $bookingpress_appointment_placeholders
            );  
            if(!empty($bookingpress_appointment_custom_field_placeholder)) {
                $bookingpress_appointment_field_list[] = array(
                    'field_group_name'  => esc_html__('Advanced fields', 'bookingpress-google-calendar'),
                    'field_list' => $bookingpress_appointment_custom_field_placeholder
                );
            }                                     
            $bookingpress_dynamic_setting_data_fields['bookingpress_gcalendar_appointment_placeholder'] = $bookingpress_appointment_field_list;
            $bookingpress_dynamic_setting_data_fields['bookingpress_gcalendar_staff_placeholder'] = json_decode($this->bookingpress_global_data['staff_member_placeholders'],true);

            $bookingpress_dynamic_setting_data_fields['bookingpress_google_calendar_rules'] = array(                               
                'google_calendar_client_id'  => array(
                    array(
                        'required' => true,
                        'message'  => __( 'Please Enter the Client ID', 'bookingpress-google-calendar' ),
                        'trigger'  => 'change',
                    ),
                ),
                'google_calendar_client_secret' => array(
                    array(
                        'required' => true,
                        'message'  => __( 'Please Enter the Client Secret', 'bookingpress-google-calendar' ),
                        'trigger'  => 'change',
                    ),
                ),
            );
            $bookingpress_dynamic_setting_data_fields['debug_log_setting_form']['google_calendar_debug_logs'] = false;
            return $bookingpress_dynamic_setting_data_fields;
        }

        function bookingpress_add_integration_settings_section_func() {
            ?>
            <el-row type="flex" class="bpa-mlc-head-wrap-settings bpa-gs-tabs--pb__heading __bpa-is-groupping" v-if="bpa_integration_active_tab == 'google_calendar'">
                <el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-gs-tabs--pb__heading--left">
                    <h1 class="bpa-page-heading"><?php esc_html_e( 'Google Calendar', 'bookingpress-google-calendar' ); ?></h1>
                </el-col>
                <el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
                    <div class="bpa-hw-right-btn-group bpa-gs-tabs--pb__btn-group">												
                        <el-button class="bpa-btn bpa-btn--primary" @click="saveSettingsData('bookingpress_google_calendar','google_calendar_setting')" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''"  :disabled="is_disabled" >
                            <span class="bpa-btn__label"><?php esc_html_e( 'Save', 'bookingpress-google-calendar' ); ?></span>
                            <div class="bpa-btn--loader__circles">				    
                                <div></div>
                                <div></div>
                                <div></div>
                            </div>
                        </el-button>
                    </div>
                </el-col>
            </el-row>
            <el-form id="bookingpress_google_calendar" ref="bookingpress_google_calendar" :rules="bookingpress_google_calendar_rules" :model="bookingpress_google_calendar" label-position="top" @submit.native.prevent v-if="bpa_integration_active_tab == 'google_calendar'">                                    
                <div class="bpa-gs__cb--item">                
                    <div class="bpa-gs__cb--item-body bpa-gs__integration-cb--item-body">
                        <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" v-if="is_staffmember_activated != 1">
                            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                <div class="bpa-toast-notification --bpa-warning">
                                    <div class="bpa-front-tn-body">
                                        <span class="material-icons-round">info</span>
                                        <p><?php esc_html_e('Google Calendar Integration requires Staff Member Module to be activated.', 'bookingpress-google-calendar') ?></p>
                                    </div>
                                </div>
                            </el-col>
                        </el-row>       
                        <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                            <el-col :xs="12" :sm="12" :md="12" :lg="08" :xl="08" class="bpa-gs__cb-item-left">
                                <h4><?php esc_html_e( 'Client Id', 'bookingpress-google-calendar' ); ?></h4>
                            </el-col>                            
                            <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                                <el-form-item prop="google_calendar_client_id">
                                    <el-input class="bpa-form-control" v-model="bookingpress_google_calendar.google_calendar_client_id" placeholder="<?php esc_html_e( 'Enter client id', 'bookingpress-google-calendar' ); ?>"></el-input>
                                </el-form-item>
                            </el-col>                            
                        </el-row>
                        <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                            <el-col :xs="12" :sm="12" :md="12" :lg="08" :xl="08" class="bpa-gs__cb-item-left">
                                <h4><?php esc_html_e( 'Client Secret', 'bookingpress-google-calendar' ); ?></h4>
                            </el-col>                            
                            <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                                <el-form-item prop="google_calendar_client_secret">
                                    <el-input class="bpa-form-control" v-model="bookingpress_google_calendar.google_calendar_client_secret" placeholder="<?php esc_html_e( 'Enter client secret', 'bookingpress-google-calendar' ); ?>"></el-input>  
                                </el-form-item>
                            </el-col>                            
                        </el-row>
                        <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                            <el-col :xs="12" :sm="12" :md="12" :lg="08" :xl="08" class="bpa-gs__cb-item-left">
                                <h4><?php esc_html_e( 'Redirect URL', 'bookingpress-google-calendar' ); ?></h4>
                            </el-col>                            
                            <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                                <div class="bpa-gs__redirect-url-val">
                                    <p><?php echo esc_url(get_home_url()) . '?page=bookingpress_gcalendar' ?></p>
                                    <span class="material-icons-round" @click="bookingpress_gclaendar_insert_placeholder('<?php echo esc_url(get_home_url()) . '?page=bookingpress_gcalendar' ?>','text')">content_copy</span>
                                </div>
                            </el-col>                            
                        </el-row>
                        <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                <div class="bpa-toast-notification --bpa-warning">
                                    <div class="bpa-front-tn-body">
                                        <span class="material-icons-round">info</span>
                                        <?php /* translators: the plugin name. */ ?>
                                        <p><?php echo sprintf( esc_html__( "Appointment synchronization with Google Calendar will depend on WordPress' cron mechanism and sometimes, it may not synchronize immediately due to WordPress' Cron limitations. If you want more accurate synchronization with Google Calendar, please follow the steps described %s here %s.", 'bookingpress-google-calendar' ), '<a href="https://www.bookingpressplugin.com/documents/set-schedule-notifications-cronjob/" target="_blank">', '</a>' ); // phpcs:ignore ?></p>
                                    </div>
                                </div>
                            </el-col>                            
                        </el-row>                            
                        <div class="bpa-gs__cb--item-heading">
                            <h4 class="bpa-sec--sub-heading"><?php esc_html_e('Event Settings', 'bookingpress-google-calendar'); ?></h4> 
                        </div>                                     
                        <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                            <el-col :xs="12" :sm="12" :md="12" :lg="08" :xl="08" class="bpa-gs__cb-item-left">
                                <h4><?php esc_html_e( 'Event Title', 'bookingpress-google-calendar' ); ?></h4>
                            </el-col>                            
                            <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                                <el-form-item prop="google_calendar_event_title">
                                    <el-input class="bpa-form-control" v-model="bookingpress_google_calendar.google_calendar_event_title" placeholder="<?php esc_html_e( 'Enter event title', 'bookingpress-google-calendar' ); ?>"></el-input>
                                </el-form-item>
                            </el-col>                            
                        </el-row>                                    
                        <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                            <el-col :xs="12" :sm="12" :md="12" :lg="08" :xl="08" class="bpa-gs__cb-item-left">
                                <h4><?php esc_html_e( 'Event Description', 'bookingpress-google-calendar' ); ?></h4>
                            </el-col>
                            <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                                <el-form-item prop="google_calendar_event_description">
                                <el-input type="textarea" :rows="5" class="bpa-form-control" v-model="bookingpress_google_calendar.google_calendar_event_description" placeholder="<?php esc_html_e( 'Enter event description', 'bookingpress-google-calendar' ); ?>"></el-input>
                                </el-form-item>
                            </el-col>                            
                        </el-row>
                        <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                            <el-col :xs="12" :sm="12" :md="12" :lg="08" :xl="08" class="bpa-gs__cb-item-left">
                                <h4><?php esc_html_e( 'Event Location', 'bookingpress-google-calendar' ); ?></h4>
                            </el-col>
                            <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                                <el-form-item prop="google_calendar_event_location">
                                    <el-select v-model="bookingpress_google_calendar.google_calendar_event_location" class="bpa-form-control" placeholder="<?php esc_html_e( 'Select Field', 'bookingpress-google-calendar' ); ?>">
                                        <el-option v-for="item in bookingpress_gcalendar_form_fields" :key="item.value" :label="item.label" :value="item.value"></el-option>
                                    </el-select>
                                </el-form>
                            </el-col>
                        </el-row>
                        <el-row class="bpa-gs--tabs-pb__cb-item-row">
                            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                <div class="bpa-gs__cb--item-heading">
                                    <h4 class="bpa-sec--sub-heading __bpa-is-gs-heading-mb-0"><?php esc_html_e('Insert Placeholders', 'bookingpress-google-calendar'); ?></h4>
                                </div>
                            </el-col>
                        </el-row>
                        <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="24">							
                            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                                <el-form-item>
                                    <el-select class="bpa-form-control" placeholder="<?php esc_html_e( 'Customer', 'bookingpress-google-calendar' ); ?>" @change="bookingpress_gclaendar_insert_placeholder($event)">
                                        <el-option v-for="item in bookingpress_gcalendar_customer_placeholder" :key="item.value" :label="item.name" :value="item.value"></el-option>
                                    </el-select>
                                </el-form-item>    
                            </el-col>
                            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                                <el-form-item>
                                    <el-select class="bpa-form-control" placeholder="<?php esc_html_e( 'Service', 'bookingpress-google-calendar' ); ?>" @change="bookingpress_gclaendar_insert_placeholder($event)">
                                        <el-option v-for="item in bookingpress_gcalendar_service_placeholder" :key="item.value" :label="item.name" :value="item.value"></el-option>
                                    </el-select>
                                </el-form-item>    
                            </el-col>																	
                        </el-row>    
                        <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="24">							
                            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                                <el-form-item>
                                    <el-select class="bpa-form-control" placeholder="<?php esc_html_e( 'Company', 'bookingpress-google-calendar' ); ?>" @change="bookingpress_gclaendar_insert_placeholder($event)" >
                                        <el-option v-for="item in bookingpress_gcalendar_company_placeholder" :key="item.value" :label="item.name" :value="item.value">
                                        </el-option>
                                    </el-select>
                                </el-form-item>        
                            </el-col>
                            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                                <el-form-item>
                                    <el-select class="bpa-form-control" placeholder="<?php esc_html_e( 'Staff Member', 'bookingpress-google-calendar' ); ?>" @change="bookingpress_gclaendar_insert_placeholder($event)" >
                                        <el-option v-for="item in bookingpress_gcalendar_staff_placeholder" :key="item.value" :label="item.name" :value="item.value">
                                        </el-option>
                                    </el-select>
                                </el-form-item>
                            </el-col>																	
                        </el-row>
                        <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="24">							
                            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                                <el-form-item>
                                    <el-select class="bpa-form-control" placeholder="<?php esc_html_e( 'Appointment', 'bookingpress-google-calendar' ); ?>" 
                                    @change="bookingpress_gclaendar_insert_placeholder($event)"
										popper-class="bpa-el-select--is-with-navbar">
                                        <el-option-group v-for="item in bookingpress_gcalendar_appointment_placeholder" :label="item.field_group_name">
                                             <el-option v-for="field_data in item.field_list" :key="field_data.value" :label="field_data.name" :value="field_data.value"></el-option>
                                        </el-option-group>
                                    </el-select>
                                </el-form-item>    
                            </el-col>
                            <?php do_action('bookingpress_add_outside_notification_placeholders'); ?>
                        </el-row>                     
                        <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                            <el-col :xs="12" :sm="12" :md="12" :lg="08" :xl="08" class="bpa-gs__cb-item-left">
                                <h4><?php esc_html_e( 'Enable Google Meet', 'bookingpress-google-calendar' ); ?></h4>
                            </el-col>                            
                            <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                                <el-form-item prop="google_calendar_meet">
                                    <el-switch class="bpa-swtich-control" v-model="bookingpress_google_calendar.google_calendar_meet"></el-switch>
                                </el-form-item>
                            </el-col>                            
                        </el-row>
                        <div class="bpa-gs__cb--item-heading">
                            <h4 class="bpa-sec--sub-heading"><?php esc_html_e('Staff Member Related Settings', 'bookingpress-google-calendar'); ?></h4>
                        </div>
                        <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                            <el-col :xs="12" :sm="12" :md="12" :lg="08" :xl="08" class="bpa-gs__cb-item-left">
                                <h4><?php esc_html_e( 'Maximum Number Of Events Returned', 'bookingpress-google-calendar' ); ?></h4>
                            </el-col>                            
                            <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                                <el-form-item prop="google_calendar_max_event">
                                    <el-input class="bpa-form-control" v-model="bookingpress_google_calendar.google_calendar_max_event" placeholder="<?php esc_html_e( 'Enter maximum number of event returned', 'bookingpress-google-calendar' ); ?>"></el-input>
                                </el-form-item>
                            </el-col>                            
                        </el-row> 
                    </div>    
                </div>
            </el-form>                   
            <?php
        }
    
        function bookingpress_add_google_calender_event(){
            if( isset( $_GET['page'] ) && 'bookingpress_gcalendar' == $_GET['page'] ){
                
                /** extract hash tag from the URL and re-build query with hash tag query string */
                if( empty( $_GET['state'] ) ){
                    /** re-build query with js */

                    echo "<script type='text/javascript' data-cfasync='false'>";
                    echo "let url = document.URL;";
                    echo "if( /\#state/.test( url ) ){";
                        echo "url = url.replace( /\#state/, '&state' );";
                        echo "window.location.href= url;";
                    echo "} else {";
                        echo "window.location.href='" . esc_url(get_home_url()) . "';";
                    echo "}";
                    echo "</script>";
                } else {
                    global $wpdb, $tbl_bookingpress_entries, $tbl_bookingpress_appointment_bookings,$BookingPress, $bookingpress_pro_staff_members;
                    $state = base64_decode( sanitize_text_field($_GET['state']) );
                    if( preg_match( '/(staff_oauth)/', $state ) ){
                        require_once BOOKINGPRESS_GOOGLE_CALENDAR_LIBRARY_DIR . "/vendor/autoload.php";
                        $code = !empty( $_GET['code'] ) ? urldecode( $_GET['code'] ) : ''; //phpcs:ignore
                        

                        $bookingpress_addon_popup_field_form = $this->bookingpress_get_google_calendar_credentials();
                        $bookingpress_client_secret = $bookingpress_addon_popup_field_form['google_calendar_client_secret'];
    
                        $bookingpress_client_id =  $bookingpress_addon_popup_field_form['google_calendar_client_id'];
                        $bookingpress_redirect_url = $bookingpress_addon_popup_field_form['google_calendar_redirect_url'];

                        $client = new Google_Client();
                        $client->setClientId($bookingpress_client_id);
                        $client->setClientSecret( $bookingpress_client_secret );
                        $client->setRedirectUri( $bookingpress_redirect_url);
                        $client->setAccessType( 'offline' );
                        
                        $response_data  = $client->authenticate( $code );

                        $client->setAccessToken( $response_data );

                        $service = new Google_Service_Calendar( $client );

                        $lists = $service->calendarList->listCalendarList();

                        $calendar_data = array();
                        foreach( $lists->getItems() as $calendarEntity ){
                            $calendar_data[] = array(
                                'id' => $calendarEntity->id,
                                'name' => $calendarEntity->summary,
                                'primary' => !empty($calendarEntity->primary) ? (int)$calendarEntity->primary : 0
                            );
                        }
                        if( version_compare( PHP_VERSION_ID, '7.0.0', '>=' ) ){
                            usort($calendar_data, function ($a, $b) {return $b['primary'] <=> $a['primary'];});
                        } else {
                            usort($calendar_data, function ($a, $b) {return $a['primary'] < $b['primary'];});
                        }

                        $calendar_arr = array();
                        foreach( $calendar_data as $calendar ){
                            $calendar_arr[] = array(
                                'value' => $calendar['id'],
                                'name' => $calendar['name']
                            );
                        }
                        
                        ?>
                        <script>
                            window.opener.app.bookingpress_gcalendar_list = <?php echo json_encode( $calendar_arr ); ?>;
                            window.opener.app.bookingpress_staff_gcalendar_auth = '<?php echo json_encode( $response_data ); ?>';
                            window.close();
                        </script>
                        <?php
                    }
                }
                die;
            }

        }

		function bookingpress_get_google_calendar_credentials() {
			global $BookingPress;
			$bookingpress_addon_popup_field_form = array();
            $bookingpress_google_calnder_redirect_url = '';	            
            $bookingpress_get_settings_data = array('google_calendar_client_id','google_calendar_client_secret', 'google_calendar_event_title', 'google_calendar_event_description', 'google_calendar_event_location', 'google_calendar_meet', 'google_calendar_pending_appointments', 'google_calendar_max_event');
            foreach ( $bookingpress_get_settings_data as $bookingpress_setting_key ) {                              
                $bookingpress_setting_val  = $BookingPress->bookingpress_get_settings( $bookingpress_setting_key, 'google_calendar_setting');                                
                $bookingpress_addon_popup_field_form[$bookingpress_setting_key] = $bookingpress_setting_val; 
                
            }
            $bookingpress_addon_popup_field_form['google_calendar_redirect_url'] = get_home_url() . '?page=bookingpress_gcalendar';
            
            return $bookingpress_addon_popup_field_form;
		}

        function bookingpress_google_calendar_admin_notices(){
            if( !function_exists('is_plugin_active') ){
                include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            }

            if( !is_plugin_active('bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php') ){
                echo "<div class='notice notice-warning'><p>" . esc_html__('BookingPress - Google Calendar Integration plugin requires BookingPress Premium Plugin installed and active.', 'bookingpress-google-calendar') . "</p></div>";
            }

            global $bookingpress_version;
            if( version_compare( $bookingpress_version, '1.0.41', '<') ){
                echo "<div class='notice notice-warning'><p>" . esc_html__('BookingPress - Google Calendar Integration plugin requires BookingPress Appointment Booking plugin installed with version 1.0.41 or higher', 'bookingpress-google-calendar') . "</p></div>";
            }

            if( file_exists( WP_PLUGIN_DIR . '/bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php' ) ){
                $bpa_pro_plugin_info = get_plugin_data( WP_PLUGIN_DIR . '/bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php' );
                $bpa_pro_plugin_version = $bpa_pro_plugin_info['Version'];

                if( version_compare( $bpa_pro_plugin_version, '1.2', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("it's highly recommended to update the BookingPress Premium Plugin to version 1.2 or higher in order to use the BookingPress Google Calendar plugin", "bookingpress-google-calendar")."</p></div>";
                }
            }

            if( current_user_can( 'administrator' ) ){
                global $wpdb, $tbl_bookingpress_staffmembers, $tbl_bookingpress_staffmembers_meta;
            }
        }

        function bookingpress_google_calendar_vue_methods(){
            
            global $bookingpress_notification_duration;
            $bookingpress_placeholder_success_msg = __( 'Placeholder copied', 'bookingpress-google-calendar' );
            $bookingpress_redirect_url_success_msg = __( 'URL copied successfully', 'bookingpress-google-calendar' );
            ?>
            bookingpress_gclaendar_insert_placeholder(event,type='placeholder')
            {
                const vm = this
                var bookingpress_selected_placholder = event

                var bookingpress_dummy_elem = document.createElement("textarea");
                document.body.appendChild(bookingpress_dummy_elem);
                bookingpress_dummy_elem.value = bookingpress_selected_placholder;
                bookingpress_dummy_elem.select();
                document.execCommand("copy");
                document.body.removeChild(bookingpress_dummy_elem);

                if(type == "placeholder") {
                    vm.$notify({ title: '<?php esc_html_e( 'Success', 'bookingpress-google-calendar' ); ?>', message: '<?php echo esc_html( $bookingpress_placeholder_success_msg ); ?>', type: 'success', customClass: 'success_notification',duration:<?php echo intval($bookingpress_notification_duration); ?>})
                } else {
                    vm.$notify({ title: '<?php esc_html_e( 'Success', 'bookingpress-google-calendar' ); ?>', message: '<?php echo esc_html( $bookingpress_redirect_url_success_msg ); ?>', type: 'success', customClass: 'success_notification',duration:<?php echo intval($bookingpress_notification_duration); ?>})
                }
            },
            <?php
        }

        function bookingpress_retrieve_location_field_data( $bookingpress_location_field_id, $appointment_data ){

            $bookingpress_location_data = '';
            if( empty( $bookingpress_location_field_id ) || empty( $appointment_data ) ){
                return $bookingpress_location_data;
            }

            global $wpdb, $tbl_bookingpress_form_fields, $bookingpress_pro_appointment;
            
            $bookingpress_location_field_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_form_field_name,bookingpress_field_meta_key FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_form_field_id = %d", $bookingpress_location_field_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally.
            
            if( !empty( $bookingpress_location_field_data ) ){
                $form_field_name = $bookingpress_location_field_data->bookingpress_form_field_name;
                $form_field_key = $bookingpress_location_field_data->bookingpress_field_meta_key;
                
                if( 'fullname' == $form_field_name ) {                    
                    $bookingpress_location_data = stripslashes_deep( $appointment_data['bookingpress_customer_name'] );
                } else if( 'firstname' == $form_field_name ){
                    $bookingpress_location_data = stripslashes_deep( $appointment_data['bookingpress_customer_firstname'] );
                } else if( 'lastname' == $form_field_name ){
                    $bookingpress_location_data = stripslashes_deep( $appointment_data['bookingpress_customer_lastname'] );
                } else if( 'email_address' == $form_field_name ){
                    $bookingpress_location_data = stripslashes_deep( $appointment_data['bookingpress_customer_email'] );
                } else if( 'phone_number' == $form_field_name ){
                    $bookingpress_location_data = stripslashes_deep( $appointment_data['bookingpress_customer_phone'] );
                } else if( 'note' == $form_field_name ){
                    $bookingpress_location_data = stripslashes_deep( $appointment_data['bookingpress_appointment_internal_note'] );
                } else {
                    
                    $bookingpress_appointment_id = $appointment_data['bookingpress_appointment_booking_id'];
                    
                    $bookingpress_appointment_custom_fields_meta_values = $bookingpress_pro_appointment->bookingpress_get_appointment_form_field_data($bookingpress_appointment_id);
                    
                    if( !empty( $bookingpress_appointment_custom_fields_meta_values ) && !empty( $bookingpress_appointment_custom_fields_meta_values[ $form_field_key ] ) ){
                        $bookingpress_location_data = stripslashes_deep( $bookingpress_appointment_custom_fields_meta_values[ $form_field_key ] );
                    }
                }
            }
            
            return stripslashes_deep( $bookingpress_location_data );

        }

        function bookingpress_google_calendar_replace_shortcode( $bookingpress_content, $bookingpress_appointment_data ,$event_from = 'insert') {
            
            global $BookingPress,$BookingPressPro,$tbl_bookingpress_appointment_bookings,$wpdb;

            $bookingpress_appointment_id       = !empty( $bookingpress_appointment_data['bookingpress_appointment_booking_id'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_appointment_booking_id'] ) : '';
			$bookingpress_appointment_date       = !empty( $bookingpress_appointment_data['bookingpress_appointment_date'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_appointment_date'] ) : '';
			$bookingpress_appointment_start_time = !empty( $bookingpress_appointment_data['bookingpress_appointment_time'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_appointment_time'] ) : '';
			$bookingpress_appointment_end_time   = !empty( $bookingpress_appointment_data['bookingpress_appointment_end_time'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_appointment_end_time'] ) : '';
			$bookingpress_appointment_service_id   = !empty( $bookingpress_appointment_data['bookingpress_service_id'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_service_id'] ) : '';
			$bookingpress_staff_member_id = !empty( $bookingpress_appointment_data['bookingpress_staff_member_id'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_staff_member_id'] ) : '';

            if($event_from == 'insert') {
                $bpa_other_bookings = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_time = %s AND bookingpress_appointment_end_time = %s AND bookingpress_appointment_date = %s AND bookingpress_service_id = %d AND bookingpress_staff_member_id != '' AND bookingpress_staff_member_id = %d AND bookingpress_appointment_status != %d AND bookingpress_appointment_status != %d AND ((bookingpress_google_calendar_event_id != '') OR bookingpress_appointment_booking_id = %d ) ORDER BY bookingpress_appointment_booking_id ",$bookingpress_appointment_start_time,$bookingpress_appointment_end_time,$bookingpress_appointment_date,$bookingpress_appointment_service_id,$bookingpress_staff_member_id,3, 4,$bookingpress_appointment_id),ARRAY_A  ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally.

            } else {
                $bpa_other_bookings = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_time = %s AND bookingpress_appointment_end_time = %s AND bookingpress_appointment_date = %s AND bookingpress_appointment_booking_id != %d AND bookingpress_service_id = %d AND bookingpress_staff_member_id != '' AND bookingpress_staff_member_id = %d AND bookingpress_appointment_status != %d AND bookingpress_appointment_status != %d AND ((bookingpress_google_calendar_event_id != '') OR bookingpress_appointment_booking_id = %d )  ORDER BY bookingpress_appointment_booking_id ",$bookingpress_appointment_start_time,$bookingpress_appointment_end_time,$bookingpress_appointment_date,$bookingpress_appointment_id,$bookingpress_appointment_service_id,$bookingpress_staff_member_id,3, 4,$bookingpress_appointment_id),ARRAY_A  ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally.
            }
            if(method_exists( $BookingPressPro, 'bookingpress_replace_calendar_appointment_data' ) && !empty($bpa_other_bookings) ) {
                $bookingpress_content = $BookingPressPro->bookingpress_replace_calendar_appointment_data($bookingpress_content,$bookingpress_appointment_data,$bpa_other_bookings);
            } elseif(method_exists( $BookingPress, 'bookingpress_replace_appointment_data' )) {
                $bookingpress_content = $BookingPress->bookingpress_replace_appointment_data($bookingpress_content,$bookingpress_appointment_data);
            }
            return $bookingpress_content;
        }

        function bookingpress_staff_members_save_gcalendar( $response ){
            global $bookingpress_pro_staff_members, $BookingPress;
            $staffmember_id = !empty( $response['staffmember_id'] ) ? intval( $response['staffmember_id'] ) : 0;

            if( empty( $staffmember_id ) ){
                return $response;
            }

            if( !empty( $_REQUEST['bookingpress_action'] ) && 'bookingpress_edit_staffmember' != $_REQUEST['bookingpress_action'] ){
                return $response;
            }

            if( !empty( $_REQUEST['bookingpress_selected_gcalendar'] ) ){
                $bookingpress_pro_staff_members->update_bookingpress_staffmembersmeta( $staffmember_id, 'bookingpress_selected_gcalendar', sanitize_text_field($_REQUEST['bookingpress_selected_gcalendar']) );  // phpcs:ignore
            }
            if( !empty( $_REQUEST['bookingpress_gcalendar_list'] ) ){
                $bookingpress_gcalendar_list = array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['bookingpress_gcalendar_list']); // phpcs:ignore
                $bookingpress_pro_staff_members->update_bookingpress_staffmembersmeta( $staffmember_id,'bookingpress_staff_gcalendar_list',json_encode($bookingpress_gcalendar_list ) ); // phpcs:ignore
            }
            if( !empty( $_REQUEST['bookingpress_staff_gcalendar_auth'] ) ){
                $bookingpress_pro_staff_members->update_bookingpress_staffmembersmeta( $staffmember_id, 'bookingpress_staff_gcalendar_auth', stripslashes_deep( $_REQUEST['bookingpress_staff_gcalendar_auth'] ) ); // phpcs:ignore
            }           
            if(!empty($_REQUEST['bookingpress_enable_google_calendar'])){
                $bookingpress_pro_staff_members->update_bookingpress_staffmembersmeta( $staffmember_id, 'bookingpress_staff_gcalendar_enable', stripslashes_deep( $_REQUEST['bookingpress_enable_google_calendar'])  ); // phpcs:ignore
            }

            $bookingpress_pro_staff_members->update_bookingpress_staffmembersmeta( $staffmember_id, 'bookingpress_display_invalid_token_notice', false );

            do_action( 'bookingpress_google_calendar_after_save_staff_settings', $staffmember_id );
            
            return $response;
        }

        function bookingpress_edit_profile_google_calendar_dynamic_field_data_func($bookingpress_myprofile_data_fields_arr) {    
            global $wpdb,$bookingpress_pro_staff_members,$tbl_bookingpress_staffmembers;
            $bookingpress_gcalendar_list = array();
            $bookingpress_selected_gcalendar = '';
            $bookingpress_staff_gcalendar_auth = '';
            $bookingpress_enable_google_calendar = false;            
            $bookingpress_current_user_id = get_current_user_id();
            if(!empty($bookingpress_current_user_id)){
                $bookingpress_staffmember_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_wpuser_id = %d", $bookingpress_current_user_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_staffmembers is table name defined globally.
                $bookingpress_staffmember_id = !empty($bookingpress_staffmember_data['bookingpress_staffmember_id']) ? intval($bookingpress_staffmember_data['bookingpress_staffmember_id']) : 0;                
                if(!empty($bookingpress_staffmember_id)){
                    $bookingpress_gcalendar_list = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_staff_gcalendar_list' );
                    $bookingpress_gcalendar_list = json_decode( $bookingpress_gcalendar_list );
                    $bookingpress_selected_gcalendar = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_selected_gcalendar' );
                    $bookingpress_staff_gcalendar_auth = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_staff_gcalendar_auth' );
                    $bookingpress_enable_google_calendar_tmp = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_staff_gcalendar_enable' );
                    $bookingpress_enable_google_calendar = ($bookingpress_enable_google_calendar_tmp == "true") ? true : false;
                }
            }            
            $bookingpress_myprofile_data_fields_arr['bookingpress_gcalendar_list'] = $bookingpress_gcalendar_list;
            $bookingpress_myprofile_data_fields_arr['bookingpress_selected_gcalendar'] = $bookingpress_selected_gcalendar;
            $bookingpress_myprofile_data_fields_arr['bookingpress_gcalendar_signin_image'] = BOOKINGPRESS_GOOGLE_CALENDAR_URL . '/images/signin-with-google.png';
            $bookingpress_myprofile_data_fields_arr['bookingpress_staff_gcalendar_auth'] = $bookingpress_staff_gcalendar_auth;
            $bookingpress_myprofile_data_fields_arr['bookingpress_enable_google_calendar'] = $bookingpress_enable_google_calendar;
            $bookingpress_myprofile_data_fields_arr['bookingpress_staff_gcalendar_auth_staffmeta'] = $bookingpress_staff_gcalendar_auth;
            $bookingpress_myprofile_data_fields_arr['bookingpress_disable_gc_refresh_link'] = false;
            $get_current_logged_id = wp_get_current_user();

            if( in_array( 'bookingpress-staffmember', $get_current_logged_id->roles ) ){
                global $wpdb, $tbl_bookingpress_staffmembers;
                $bookingpress_current_user_id = $get_current_logged_id->ID;
                $bookingpress_staffmember_data = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_staffmember_id FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_wpuser_id = %d", $bookingpress_current_user_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_staffmembers is table name defined globally.
                if( !empty( $bookingpress_staffmember_data['bookingpress_staffmember_id'] ) ){
                    $bookingpress_myprofile_data_fields_arr['staff_members'] = array(
                        'update_id' => $bookingpress_staffmember_data['bookingpress_staffmember_id']
                    );
                }
            }

            return $bookingpress_myprofile_data_fields_arr;

        }

        function bookingpress_staff_member_google_calendar_dynamic_field_data( $bookingpress_staff_member_vue_data_fields ){

            $bookingpress_staff_member_vue_data_fields['bookingpress_gcalendar_list'] = array();
            $bookingpress_staff_member_vue_data_fields['bookingpress_selected_gcalendar'] = '';
            $bookingpress_staff_member_vue_data_fields['bookingpress_staff_gcalendar_auth'] = '';
            $bookingpress_staff_member_vue_data_fields['bookingpress_enable_google_calendar'] = false;
            $bookingpress_staff_member_vue_data_fields['bookingpress_staff_gcalendar_auth_staffmeta'] = '';
            $bookingpress_staff_member_vue_data_fields['bookingpress_disable_gc_refresh_link'] = false;
            return $bookingpress_staff_member_vue_data_fields;
        }

        function bookingpress_staff_member_gcalendar_dynamic_field_data(){

            
            $bookingpress_myprofile_vue_data_fields = array();

            if( in_array( 'bookingpress-staffmember', $get_current_logged_id->roles ) ){
                global $wpdb, $tbl_bookingpress_staffmembers;
                $bookingpress_current_user_id = $get_current_logged_id->ID;
                $bookingpress_staffmember_data = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_staffmember_id FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_wpuser_id = %d", $bookingpress_current_user_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_staffmembers is table name defined globally.
                if( !empty( $bookingpress_staffmember_data['bookingpress_staffmember_id'] ) ){
                    $bookingpress_myprofile_vue_data_fields['staff_members'] = array(
                        'update_id' => $bookingpress_staffmember_data['bookingpress_staffmember_id']
                    );
                }
            }

            echo wp_json_encode( $bookingpress_myprofile_vue_data_fields );
        }

        function bookingpress_google_calendar_authentication(){

            $bookingpress_addon_popup_field_form = $this->bookingpress_get_google_calendar_credentials();
            
            $bookingpress_client_id =  $bookingpress_addon_popup_field_form['google_calendar_client_id'];
            $bookingpress_redirect_url = $bookingpress_addon_popup_field_form['google_calendar_redirect_url'];
            $bookingpress_client_secret = $bookingpress_addon_popup_field_form['google_calendar_client_secret'];

            $state = base64_encode( 'action:staff_oauth' );

            ?>
            bookingpress_google_calendar_auth(){
                let oauth_url = 'https://accounts.google.com/o/oauth2/v2/auth?scope=' + encodeURI( 'https://www.googleapis.com/auth/calendar.calendarlist.readonly') + ' '  + encodeURI( 'https://www.googleapis.com/auth/calendar.events') + '&response_type=code&prompt=consent&redirect_uri=' + encodeURI( '<?php echo $bookingpress_redirect_url; // phpcs:ignore ?>' ) + '&access_type=offline&client_id=<?php echo $bookingpress_client_id; // phpcs:ignore ?>&state=<?php echo $state; // phpcs:ignore ?>';
                
                window.open( oauth_url, 'BookingPress Google Calendar Authentication', 'height=500, width=500');
            },
            bookingpress_google_calendar_signout( staffmember_id ){
                const vm = this;
                if( 1 > staffmember_id ){
                    return false;
                }
                let postData = {
                    action: "bookingpress_signout_google_calendar",
                    staffmember: staffmember_id,
                    _wpnonce: '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'
                };
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then( function (response) {
                    if( response.data.variant == "success" ){
                        vm.bookingpress_gcalendar_list = [];
                        vm.bookingpress_selected_gcalendar = '';
                        vm.bookingpress_staff_gcalendar_auth_staffmeta = '';
                    }
                }.bind(this) )
				.catch( function (error) {
                    console.log( error );
                });
            },
            bookingpress_gc_remove_params_from_url( url_string, variable_name ){
                let URL = String(url_string);
                let regex = new RegExp("\\?" + variable_name + "=[^&]*&?", "gi");
                URL = URL.replace(regex, '?');
                regex = new RegExp("\\&" + variable_name + "=[^&]*&?", "gi");
                URL = URL.replace(regex, '&');
                URL = URL.replace(/(\?|&)$/, '');
                regex = null;
                return URL;
            },
            bookingpress_refresh_google_calendar_list( staffmember_id ){
                const vm = this;
                if( 1 > staffmember_id ){
                    return false;
                }
                let svg = document.getElementById('bookingpress-gc-svg');
                svg.classList.add('gc-refresh-rotate');                
                vm.bookingpress_disable_gc_refresh_link = true;
                let postData = {
                    action: "bookingpress_refresh_google_calendar_list",
                    staffmember: staffmember_id,
                    selected_calendar: vm.bookingpress_selected_gcalendar,
                    _wpnonce: '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'
                };
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then( function (response) {
                    svg.classList.remove('gc-refresh-rotate');
                    vm.bookingpress_disable_gc_refresh_link = false;
                    if( response.data.variant == "success" && undefined != response.data.google_calendars){
                        vm.bookingpress_gcalendar_list = response.data.google_calendars;
                    }
                    if( "undefined" != typeof response.data.is_reset_list && true == response.data.is_reset_list ){
                        vm.bookingpress_selected_gcalendar = '';
                    }
                    vm.$notify({
                        title: response.data.title,
                        message: response.data.msg,
                        type: response.data.variant,
                        customClass: response.data.variant+'_notification',
                    });   
                }.bind(this) )
				.catch( function (error) {
                    console.log( error );
                });
            },
            <?php
        }

        function bookingpress_generate_staff_member_gcalendar_auth_link( $bookingpress_edit_staff_members_details ){

            global $bookingpress_pro_staff_members;

            $bookingpress_addon_popup_field_form = $this->bookingpress_get_google_calendar_credentials();
            
            $bookingpress_client_id =  $bookingpress_addon_popup_field_form['google_calendar_client_id'];
            $bookingpress_redirect_url = $bookingpress_addon_popup_field_form['google_calendar_redirect_url'];
            $bookingpress_client_secret = $bookingpress_addon_popup_field_form['google_calendar_client_secret'];
            
            $redirect_url = admin_url('admin.php?page=bookingpress_staff_members');
            $edit_staff_id = !empty($_POST['edit_id']) ? intval($_POST['edit_id']) : ''; // phpcs:ignore

            $state = base64_encode( 'action:staff_oauth&user_id='.$edit_staff_id );

            $bookingpress_staff_gcalendar_list = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $edit_staff_id, 'bookingpress_staff_gcalendar_list' );

            $bookingpress_staff_gcalendar_list = json_decode( $bookingpress_staff_gcalendar_list );

            if( !empty( $bookingpress_staff_gcalendar_list ) ){
                $bookingpress_edit_staff_members_details['bookingpress_gcalendar_list'] = $bookingpress_staff_gcalendar_list;
            } else {
                $bookingpress_edit_staff_members_details['bookingpress_gcalendar_list'] = array();
            }

            $bookingpress_staff_gcalendar_selected = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $edit_staff_id, 'bookingpress_selected_gcalendar' );
            if( !empty( $bookingpress_staff_gcalendar_selected ) ){
                $bookingpress_edit_staff_members_details['bookingpress_gcalendar'] = $bookingpress_staff_gcalendar_selected;
            } else {
                $bookingpress_edit_staff_members_details['bookingpress_gcalendar'] = '';
            }

            $bookingpress_staff_gcalendar_enabled = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta($edit_staff_id, 'bookingpress_staff_gcalendar_enable');            
            $bookingpress_edit_staff_members_details['bookingpress_enable_google_calendar'] = $bookingpress_staff_gcalendar_enabled == 'true' ? true : false;

            $staff_auth_data = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $edit_staff_id, 'bookingpress_staff_gcalendar_auth' );
            if( !empty( $staff_auth_data ) ){
                $bookingpress_edit_staff_members_details['bookingpress_staff_gcalendar_auth_staffmeta'] = $staff_auth_data;
            } else {
                $bookingpress_edit_staff_members_details['bookingpress_staff_gcalendar_auth_staffmeta'] = array();
            }
            return $bookingpress_edit_staff_members_details;
        }

        function bookingpress_save_staff_member_func(){
            global $bookingpress_notification_duration;
            ?> 
               if(vm2.bookingpress_enable_google_calendar == true && vm2.bookingpress_selected_gcalendar == '') {
                    vm2.$notify({
                        title: '<?php esc_html_e( 'Error', 'bookingpress-google-calendar' ); ?>',
                        message: '<?php esc_html_e( 'Please select the google calendar', 'bookingpress-google-calendar' ); ?>',
                        type: 'error',
                        customClass: 'error_notification',
                        duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
                    });
                    vm2.is_disabled = false
                    vm2.is_display_save_loader = '0'							
                    return false;
               }
               postdata.bookingpress_selected_gcalendar = vm2.bookingpress_selected_gcalendar;
               postdata.bookingpress_gcalendar_list = vm2.bookingpress_gcalendar_list;
               postdata.bookingpress_staff_gcalendar_auth = vm2.bookingpress_staff_gcalendar_auth;
               postdata.bookingpress_enable_google_calendar = vm2.bookingpress_enable_google_calendar
            <?php
        }

        function bookingpress_assign_gcalendar_auth_link(){
            ?>
            vm2.bookingpress_staff_gcalendar_auth = edit_staff_members_details.bookingpress_staff_gcalendar_auth;
            vm2.bookingpress_gcalendar_list = edit_staff_members_details.bookingpress_gcalendar_list;
            vm2.bookingpress_selected_gcalendar = edit_staff_members_details.bookingpress_gcalendar;
            vm2.bookingpress_enable_google_calendar = edit_staff_members_details.bookingpress_enable_google_calendar;
            vm2.bookingpress_staff_gcalendar_auth_staffmeta = edit_staff_members_details.bookingpress_staff_gcalendar_auth_staffmeta;
            
            <?php
        }

        function bookingpress_staff_member_google_calendar_integration(){
            ?>
            <div class="bpa-staff-integration-item" id="google_calendar_staff_member_module">
                <el-row>
                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <div class="bpa-en-status--swtich-row">
                            <label class="bpa-form-label"><?php esc_html_e( 'Google Calendar Integration', 'bookingpress-google-calendar' ); ?></label>
                            <el-switch class="bpa-swtich-control" v-model="bookingpress_enable_google_calendar"></el-switch>
                        </div>
                    </el-col>
                </el-row>
                <div class="bpa-sii__body" v-if="bookingpress_enable_google_calendar == true || bookingpress_enable_google_calendar == 'true'">
                    <el-row type="flex" :gutter="32">
                        <el-col :xs="16" :sm="16" :md="16" :lg="18" :xl="20">
                            <el-select v-model="bookingpress_selected_gcalendar" class="bpa-form-control bpa-form-control__left-icon" placeholder="<?php esc_html_e( 'Select Google Calendar', 'bookingpress-google-calendar' ); ?>">
                                <el-option v-for="item in bookingpress_gcalendar_list" :key="item.value" :label="item.name" :value="item.value"></el-option>
                            </el-select>
                        </el-col>
                        <el-col :xs="8" :sm="8" :md="8" :lg="6" :xl="1" class="bookingpress-staff-gc-refresh-col" v-if="bookingpress_gcalendar_list != null && bookingpress_gcalendar_list.length > 0 && bookingpress_staff_gcalendar_auth_staffmeta.length > 0">
                            <el-tooltip popper-class="google_calendar_staff_member_module_refresh" effect="dark" content="" placement="top" open-delay="300">
                                <div slot="content">
                                    <span><?php esc_html_e( 'Refresh Calendar List', 'bookingpress-google-calendar' ); ?></span>
                                </div>
                                <el-button :disabled="bookingpress_disable_gc_refresh_link" class="bpa-btn bpa-btn__medium bpa-btn--full-width" @click="bookingpress_refresh_google_calendar_list(staff_members.update_id)">
                                <svg width="18" height="18" viewBox="0.96 1.88 18 18" fill="none" xmlns="http://www.w3.org/2000/svg" id="bookingpress-gc-svg">
                                <g clip-path="url(#clip0_30_1275)">
                                <path d="M5.24736 7.17508C5.06799 7.12776 4.91891 7.00321 4.84043 6.83512C4.76196 6.66703 4.7622 6.47277 4.84109 6.30487L6.22519 3.35924C6.32834 3.13973 6.54915 2.9997 6.79169 3C7.03423 3.0003 7.25469 3.14088 7.3573 3.36064L7.96788 4.66847C7.98971 4.6599 8.01225 4.65245 8.03545 4.64624C11.6586 3.67541 15.3828 5.82556 16.3536 9.44873C17.3244 13.0719 15.1743 16.7961 11.5511 17.7669C7.92795 18.7377 4.20378 16.5876 3.23295 12.9644C2.92424 11.8122 2.93118 10.6481 3.20157 9.56566C3.28524 9.23074 3.62457 9.02706 3.95949 9.11073C4.29441 9.19439 4.4981 9.53372 4.41443 9.86865C4.19416 10.7504 4.18809 11.6989 4.44049 12.6408C5.23262 15.5971 8.27129 17.3515 11.2276 16.5594C14.1838 15.7672 15.9382 12.7286 15.1461 9.77229C14.3669 6.86436 11.414 5.11933 8.50407 5.81695L9.12013 7.13651C9.22273 7.35627 9.18894 7.61555 9.03344 7.80168C8.87794 7.98781 8.62881 8.06719 8.39429 8.00532L5.24736 7.17508Z" fill="#727E95" stroke="#727E95" stroke-width="0.55" stroke-linejoin="round"/>
                                </g>
                                <defs>
                                <clipPath id="clip0_30_1275">
                                <rect width="20" height="20" fill="white"/>
                                </clipPath>
                                </defs>
                                </svg>
                                </el-button>
                            </el-tooltip>
                        </el-col> 
                        <el-col :xs="8" :sm="8" :md="8" :lg="6" :xl="4">
                            <el-button class="bpa-btn bpa-btn__medium bpa-btn--full-width" @click="(bookingpress_gcalendar_list != null && bookingpress_gcalendar_list.length > 0 ) ? bookingpress_google_calendar_signout( staff_members.update_id ) : bookingpress_google_calendar_auth();">
                                <svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#google-symbol)">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M16.1444 8.17812C16.1444 7.64479 16.0556 7.02257 15.9667 6.57812H8.5V9.68924H12.7667C12.5889 10.667 12.0556 11.467 11.1667 12.0892V14.1337H13.8333C15.3444 12.7115 16.1444 10.5781 16.1444 8.17812Z" fill="#4285F4"/>
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M8.49978 15.9996C10.6331 15.9996 12.4998 15.2885 13.8331 14.0441L11.1664 12.0885C10.4553 12.5329 9.56645 12.8885 8.49978 12.8885C6.45534 12.8885 4.67756 11.4663 4.05534 9.59961H1.38867V11.5552C2.63312 14.2218 5.38867 15.9996 8.49978 15.9996Z" fill="#34A853"/>
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M4.05556 9.511C3.87778 9.06656 3.78889 8.53322 3.78889 7.99989C3.78889 7.46656 3.87778 6.93322 4.05556 6.48878V4.44434H1.38889C0.855556 5.511 0.5 6.75545 0.5 7.99989C0.5 9.24434 0.766667 10.4888 1.38889 11.5554L4.05556 9.511Z" fill="#FBBC05"/>
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M8.49978 3.2C9.65534 3.2 10.722 3.64444 11.522 4.35556L13.8331 2.04444C12.4998 0.8 10.6331 0 8.49978 0C5.38867 0 2.63312 1.77778 1.38867 4.44444L4.05534 6.48889C4.67756 4.62222 6.45534 3.2 8.49978 3.2Z" fill="#EA4335"/>
                                    </g>
                                    <defs>
                                        <clipPath id="google-symbol">
                                        <rect width="16" height="16" fill="white" transform="translate(0.5)"/>
                                        </clipPath>
                                    </defs>
                                </svg>
                                <span v-if="bookingpress_gcalendar_list != null && bookingpress_gcalendar_list.length > 0"><?php esc_html_e('Sign Out from Google', 'bookingpress-google-calendar'); ?></span>
                                <span v-else><?php esc_html_e('Sign In With Google', 'bookingpress-google-calendar'); ?></span>
                            </el-button>
                        </el-col>
                    </el-row>
                </div>
            </div>
            <?php
        }
        
        function bookingpress_edit_staff_members(){
            if( !empty( $_GET['baction'] ) && 'edit_staff' == $_GET['baction'] && !empty( $_GET['edit_staff_id'] ) ){
                $staff_id = intval( $_GET['edit_staff_id']); 
                ?>
                this.editStaffMember(<?php echo esc_html($staff_id); // phpcs:ignore ?>)
                <?php
            }
        }

        function bookingpress_modify_disable_dates_google_calendar( $bookingpress_disable_date, $bookingpress_selected_service, $month_check = '' ){

            $bookingpress_staffmember_id = !empty( $_POST['appointment_data_obj']['bookingpress_selected_staff_member_details']['selected_staff_member_id'] ) ? intval( $_POST['appointment_data_obj']['bookingpress_selected_staff_member_details']['selected_staff_member_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.

			if( empty( $bookingpress_staffmember_id ) && !empty( $_POST['bookingpress_selected_staffmember']['selected_staff_member_id'] ) ){ // phpcs:ignore
				$bookingpress_staffmember_id = intval( $_POST['bookingpress_selected_staffmember']['selected_staff_member_id'] ); // phpcs:ignore
			}

			if( empty( $bookingpress_staffmember_id ) ){
				return $bookingpress_disable_date;
			}

            global $tbl_bookingpress_appointment_bookings, $wpdb, $bookingpress_pro_staff_members, $bookingpress_pro_appointment_bookings, $tbl_bookingpress_services;

            $calendarId = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_selected_gcalendar' );
            if( empty( $calendarId ) ){
                return $bookingpress_disable_date;
            }

            if( empty( $month_check ) && !empty( $_POST['action'] ) && 'bookingpress_get_whole_day_appointments_multiple_days' == $_POST['action'] ){
                $month_check = $_POST['next_year'] .'-'. $_POST['next_month'] .'-01';
            }

            if( !empty( $month_check ) ){				
				$booking_start_date = date('Y-m-d', strtotime( $month_check ) );
				$booking_end_date = date( 'Y-m-d', strtotime( 'last day of this month', strtotime( $booking_start_date ) ) );
			} else {
				$booking_start_date = date('Y-m-d', current_time('timestamp') );
				$booking_end_date = date( 'Y-m-d', strtotime( 'last day of this month', current_time( 'timestamp' ) ) );
			}

            $start_date = new DateTime( $booking_start_date );
            $end_date = new DateTime( date('Y-m-d', strtotime( $booking_end_date . '+1 day') ) );

            $interval = DateInterval::createFromDateString('1 day');
            $period = new DatePeriod( $start_date, $interval, $end_date );

            $disable_included_date = array();

            $appointment_data = $_POST['appointment_data_obj'];
            $selected_service_duration = $appointment_data['selected_service_duration'];
            $selected_service_duration_unit = $appointment_data['selected_service_duration_unit'];

            $first_occurrence = array();
            foreach( $period as $dt ){
                $selected_date = $dt->format('Y-m-d');

                if( !empty( $disable_included_date ) && in_array( $selected_date, $disable_included_date ) ){
                    continue;
                }

                $get_calendar_data = $this->bookingpress_retrieve_google_calendar_events( $bookingpress_staffmember_id, $calendarId, $selected_date, true );
                
                if( empty( $get_calendar_data ) ){
                    continue;
                }
                
                $fcount = 0;
                if( $fcount < 1 ){
                    $first_occurrence[] = date('Y-m-d', strtotime( $selected_date ) );
                }
				
				$skip_check = false;
                
                foreach( $get_calendar_data as $event_id => $event_data ){
                    $db_service_id = $wpdb->get_var( $wpdb->prepare( "SELECT bookingpress_service_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_google_calendar_event_id = %s", $event_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
                
                    if( !empty( $db_service_id ) && $bookingpress_selected_service == $db_service_id ){
						$skip_check = true;
                        continue;
                    }
                    if( 'd' == $selected_service_duration_unit ){
                        array_push( $bookingpress_disable_date, $dt->format('c') );
                    } else {
       
                        $event_start_date = $event_data['start_date'];
                        $event_end_date = $event_data['end_date'];
                        
                        $date_diff = strtotime( $event_end_date ) - strtotime( $event_start_date );
                        
                        $diff = abs( round( $date_diff / 86400 ) );
                        
                        if( 0 < $diff ){
                            array_push( $bookingpress_disable_date, $dt->format('c') );
                        }
                        $fcount++;
                    }
                }

            }

            if( 'd' == $selected_service_duration_unit && false == $skip_check && !empty( $first_occurrence ) ){
                foreach( $first_occurrence as $first_date ){
                    for( $dm = $selected_service_duration - 1; $dm > 0; $dm-- ){
                        $booked_day_minus = date( 'Y-m-d', strtotime( $first_date . '-' . $dm . ' days' ));    
                        $bookingpress_disable_date[] = date('c', strtotime( $booked_day_minus ) );
                    }
                }
            }


            return $bookingpress_disable_date;
        }

        function bookignpress_is_google_event_exist($calendarId,$bookingpress_google_calendar_event_id,$staffMember_id) {
            $bookingpress_gcalendar_service = $this->bookingpress_get_staff_google_calendar_service( $staffMember_id );
            $eventData = $bookingpress_gcalendar_service->events->get( $calendarId, $bookingpress_google_calendar_event_id );
            if(!empty($eventData) && isset($eventData->status) && $eventData->status ==  'confirmed') {
                return 1;
            }
            return 0;           
        }        

        function bookingpress_schedule_group_appointments( $order_id ){

            if( empty( $order_id  ) ){
                return;
            }

            global $wpdb, $tbl_bookingpress_appointment_bookings;

            $bpa_fetch_group_booking_ids = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_appointment_booking_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_order_id = %d", $order_id ) );

            if( empty( $bpa_fetch_group_booking_ids ) ){
                return;
            }

            foreach( $bpa_fetch_group_booking_ids as $booking_ids ){
                $appointment_id = $booking_ids->bookingpress_appointment_booking_id;

                $this->bookingpress_assign_appointment_to_staff_member( $appointment_id, $order_id, array(), 0 );
            }

        }

        function bookingpress_assign_appointment_to_staff_member_from_admin( $appointment_id, $entry_id = '', $payment_gateway_data = array() ){

            $backtrace = wp_debug_backtrace_summary( null, 0, true );

            if( preg_match( '/bookingpress_book_front_appointment_func/', $backtrace ) ){
                return;
            } else {
                $this->bookingpress_assign_appointment_to_staff_member( $appointment_id, $entry_id, $payment_gateway_data, 0 );
            }

        }

        function bookingpress_assign_appointment_to_staff_member_with_cron( $appointment_id, $entry_id = '', $payment_gateway_data = array() ){
            global $BookingPress, $wpdb, $tbl_bookingpress_settings;

            if( empty( $appointment_id ) ){
                return;
            }

            $counter = get_option( 'bpa_gc_cron_counter_'. $appointment_id );

            if( !isset( $counter ) || empty( $counter ) ){
                $counter = 1;
            }

            if( $counter > 3 ){
                $debug_log_data = json_encode( func_get_args() );
                do_action( 'bookingpress_integration_log_entry', 'google_calendar_debug_logs', 'Google Calendar Event Creation', 'Google Calendar Appointment Syncrhonization attempt 3 times', 'Google Calendar Stopped attempting Google Calendar Appointment Synchronization after 3 attempt.', $debug_log_data, $bookingpress_debug_integration_log_id );
                return;
            }

            $get_flag = $wpdb->get_var( $wpdb->prepare( "SELECT setting_value FROM {$tbl_bookingpress_settings} WHERE setting_name = %s AND setting_type = %s", 'bpa_gc_cron_app_id_' . $appointment_id, 'bpa_gc_cron' )  ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_settings is a table name. false alarm

            if( empty( $get_flag ) ){ //blank or 0
                if( 0 === $get_flag || '0' === $get_flag){
                    $BookingPress->bookingpress_update_settings( 'bpa_gc_cron_app_id_' . $appointment_id, 'bpa_gc_cron', 1 );
                } else {
                    $wpdb->insert(
                        $tbl_bookingpress_settings,
                        array(
                            'setting_name' => 'bpa_gc_cron_app_id_' . $appointment_id,
                            'setting_value' => 1,
                            'setting_type' => 'bpa_gc_cron',
                            'updated_at' => date('Y-m-d H:i:s', current_time('timestamp') )
                        )
                    );
                }

                $this->bookingpress_assign_appointment_to_staff_member( $appointment_id, $entry_id, $payment_gateway_data, $counter );
            }
        }

        function bookingpress_force_assign_appointment_to_staff(){
            global $BookingPress, $wpdb, $tbl_bookingpress_settings;

            $get_cron_data = $wpdb->get_results( $wpdb->prepare( "SELECT setting_name,setting_value FROM {$tbl_bookingpress_settings} WHERE setting_name LIKE %s AND setting_type = %s AND setting_value = %d", 'bpa_gc_cron_app_id_%', 'bpa_gc_cron', 0 )  ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_settings is a table name. false alarm
            if(!empty($get_cron_data)) {
                foreach( $get_cron_data  as $cron_data ){
                    if(!empty($cron_data)) {
                        $setting_name = $cron_data->setting_name;
                        $setting_value = $cron_data->setting_value;
                        $appointment_id = str_replace( 'bpa_gc_cron_app_id_', '', $setting_name );
                        $app_data = $BookingPress->bookingpress_get_settings( 'bpa_gc_cron_app_data_' . $appointment_id, 'bpa_gc_cron' );
                        $appointment_data = json_decode( $app_data, true );
                        $entry_id = isset($appointment_data['entry_id']) ? $appointment_data['entry_id']: '';
                        $payment_gateway_data = isset($appointment_data['payment_gateway_data']) ? $appointment_data['payment_gateway_data'] : '';
                        $BookingPress->bookingpress_update_settings( 'bpa_gc_cron_app_id_' . $appointment_id, 'bpa_gc_cron', 1 );
                        $this->bookingpress_assign_appointment_to_staff_member( $appointment_id, $entry_id, $payment_gateway_data );
                    }
                }
            }
        }

        function bookingpress_force_assign_appointment_to_staff_legacy(){
            global $BookingPress, $wpdb, $tbl_bookingpress_settings;
            $bookingpress_gc_scheduler_data = get_option( 'bookingpress_gc_scheduler_data' );
            
            if( !empty( $bookingpress_gc_scheduler_data ) ){


                $bookingpress_gc_scheduler_data = json_decode( $bookingpress_gc_scheduler_data, true );
                if( !empty( $bookingpress_gc_scheduler_data ) ){

                    foreach( $bookingpress_gc_scheduler_data as $appointment_id => $appointment_data ){

                        $entry_id = $appointment_data['entry_id'];
                        
                        $payment_gateway_data = $appointment_data['payment_gateway_data'];

                        $counter = isset( $appointment_data['counter'] ) ? $appointment_data['counter'] : 1;
                        
                        $get_cron_flag = $wpdb->get_var( $wpdb->prepare( "SELECT setting_value FROM {$tbl_bookingpress_settings} WHERE setting_name = %s AND setting_type = %s", 'bpa_gc_cron_app_id_' . $appointment_id, 'bpa_gc_cron' )  ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_settings is a table name. false alarm

                        if( empty( $get_cron_flag ) ){ //blank or 0
                            if( 0 === $get_cron_flag || '0' === $get_cron_flag ){
                                $BookingPress->bookingpress_update_settings( 'bpa_gc_cron_app_id_' . $appointment_id, 'bpa_gc_cron', 1 );
                            } else {
                                $wpdb->insert(
                                    $tbl_bookingpress_settings,
                                    array(
                                        'setting_name' => 'bpa_gc_cron_app_id_' . $appointment_id,
                                        'setting_value' => 1,
                                        'setting_type' => 'bpa_gc_cron',
                                        'updated_at' => date('Y-m-d H:i:s', current_time('timestamp') )
                                    )
                                );
                            }
                            
                            $this->bookingpress_assign_appointment_to_staff_member( $appointment_id, $entry_id, $payment_gateway_data, $counter );
                        } else {
                            /** Check if the email notification has been sent or not */
                            $get_email_notification_data = $wpdb->get_results( $wpdb->prepare( "SELECT option_name,option_value FROM " . $wpdb->options . " WHERE option_name LIKE %s", 'bookingpress_gc_send_notification_' . $appointment_id .'_'. $entry_id.'%'), ARRAY_A );
                            if( !empty( $get_email_notification_data ) ){
                                global $bookingpress_email_notifications;
                                foreach( $get_email_notification_data as $opt_val  ){
                                    $notification_data = $opt_val['option_value'];
                                    $opt_name = $opt_val['option_name'];

                                    $is_sent = get_option( $opt_name .'_is_sent' );

                                    if( !empty( $is_sent ) && 1 == $is_sent ){
                                        delete_option( $opt_name );
                                        continue;
                                    }

                                    if( preg_match( '/_is_sent$/', $opt_name ) ){
                                        continue;
                                    }

                                    $args = json_decode( $notification_data, true );
                                    $template_type = !empty( $args[0] ) ? $args[0] : '';
                                    $notification_name = !empty( $args[1] ) ? $args[1] : '';
                                    $appointment_id = !empty( $args[2] ) ? $args[2] : '';
                                    $receiver_email_id = !empty( $args[3] ) ? $args[3] : '';
                                    $cc_emails = !empty( $args[4] ) ? $args[4] : '';
                                    $force = true;
                                    delete_option( $opt_name );
                                    $bookingpress_email_notifications->bookingpress_send_email_notification( $template_type, $notification_name, $appointment_id, $receiver_email_id, $cc_emails, $force );
                                    update_option( 'bookingpress_gc_send_notification_' . $appointment_id . '_' . $entry_id .'_'. $template_type .'_'.$notification_name .'_is_sent', 1 );
                                }
                            }
                        }
                    }
                }

            }
        }

        function bookingpress_schedule_gc_event( $appointment_id, $entry_id = '', $payment_gateway_data = array() ){

            global $BookingPress;
            $bookingpress_check_status = apply_filters( 'bookingpress_check_status_for_appointment_integration', false, $appointment_id, '', ''); 
            if($bookingpress_check_status){
                return;
            }
            $backtrace = wp_debug_backtrace_summary( null, 0, true );
            if( !preg_match( '/bookingpress_book_front_appointment_func/', $backtrace ) ){
                return;
            }
            add_filter( 'bookingpress_check_email_notiication_processing', function( $flag, $args ) use( $appointment_id, $entry_id){
                global $BookingPress, $bookingpress_pro_staff_members, $tbl_bookingpress_appointment_bookings, $wpdb, $tbl_bookingpress_notifications;
                /** Check if Google Calendar is configured with staff member. */
                $is_staffmember_activated = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();
                if( !$is_staffmember_activated ){
                    return $flag;
                }

                $appointment_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_staff_member_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $appointment_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally.

                $staff_member_id = !empty($appointment_data['bookingpress_staff_member_id']) ? intval($appointment_data['bookingpress_staff_member_id']) : 0 ;
                if( empty( $staff_member_id ) ){
                    return $flag;
                }

                $calendarId = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_selected_gcalendar' );
                if( empty( $calendarId ) ){
                    return $flag;
                }

                $bookingpress_staff_gcalendar_enable = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_staff_gcalendar_enable');
                if(empty($bookingpress_staff_gcalendar_enable) || $bookingpress_staff_gcalendar_enable == 'false' ) {
                    return $flag; 
                }

                $args = json_decode( $args, true );
                $template_type = !empty( $args[0] ) ? $args[0] : '';
                $notification_name = !empty( $args[1] ) ? $args[1] : '';

                $bookingpress_addon_popup_field_form = $this->bookingpress_get_google_calendar_credentials();
                $bookingpress_event_enable_meet = $bookingpress_addon_popup_field_form['google_calendar_meet']; 
                
                if('true' == $bookingpress_event_enable_meet) {
                    /* To check Google Meet Placeholder added in the Email notification or not */ 
                    $bookingpress_email_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_notification_subject, bookingpress_notification_message FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_name = %s AND bookingpress_notification_receiver_type = %s AND bookingpress_notification_status = 1", $notification_name, $template_type ), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_notifications is table name defined globally. False Positive alarm

                    $email_subject = (isset($bookingpress_email_data['bookingpress_notification_subject']) && !empty($bookingpress_email_data['bookingpress_notification_subject'])) ? $bookingpress_email_data['bookingpress_notification_subject'] : '';

                    $email_message = (isset($bookingpress_email_data['bookingpress_notification_message']) && !empty($bookingpress_email_data['bookingpress_notification_message'])) ? $bookingpress_email_data['bookingpress_notification_message'] : '';
                    
                    $bookingpress_found_google_meet_placeholder = false;
                    if(!empty($email_subject) && preg_match( '/%google_meet_url%/', $email_subject ))  {
                        $bookingpress_found_google_meet_placeholder = true;
                    }
                    if($bookingpress_found_google_meet_placeholder == false && !empty($email_message) && preg_match( '/%google_meet_url%/', $email_message )) {
                        $bookingpress_found_google_meet_placeholder = true;
                    }

                    if(true == $bookingpress_found_google_meet_placeholder )
                    {
                        update_option( 'bookingpress_gc_send_notification_' . $appointment_id . '_' . $entry_id . '_' . $template_type .'_'.$notification_name, json_encode( $args ) );
                        update_option( 'bookingpress_gc_send_notification_' . $appointment_id . '_' . $entry_id . '_' . $template_type .'_'.$notification_name. '_is_sent', 0 );
                        return false;
                    }
                }
                return $flag;
            }, 10, 2 );

            $BookingPress->bookingpress_update_settings( 'bpa_gc_cron_app_id_' . $appointment_id, 'bpa_gc_cron', 0 );
            
            $arguments = array(
                'entry_id' => $entry_id,
                'payment_gateway_data' => $payment_gateway_data
            );
            $BookingPress->bookingpress_update_settings( 'bpa_gc_cron_app_data_' . $appointment_id, 'bpa_gc_cron', json_encode( $arguments ) );

            wp_schedule_single_event( strtotime('+5 seconds'), 'bookingpress_schedule_staffmember_gc_event', array( $appointment_id, $entry_id, $payment_gateway_data ) );
        }

        function bookingpress_assign_appointment_to_staff_member( $appointment_id, $entry_id = '', $payment_gateway_data = array(), $counter = 0 ){

            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $bookingpress_pro_staff_members, $tbl_bookingpress_entries,$bookingpress_debug_integration_log_id,$tbl_bookingpress_staffmembers_services, $tbl_bookingpress_form_fields;
            
            if( empty( $appointment_id ) ){
                return;
            }
            
            $is_staffmember_activated = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();
            if( !$is_staffmember_activated ){
                $debug_log_data = json_encode( array( 'staff_module_status' => $is_staffmember_activated ) );
                do_action( 'bookingpress_integration_log_entry', 'google_calendar_debug_logs', 'Google Calendar Event Creation', 'BookingPress GC - Staffmember Module deactivated', 'Staff Member Module is deactivated while processing event', $debug_log_data, $bookingpress_debug_integration_log_id );
                $BookingPress->bookingpress_update_settings( 'bpa_gc_cron_app_id_' . $appointment_id, 'bpa_gc_cron', 0 );
                return;
            }

            $bookingpress_check_status = apply_filters( 'bookingpress_check_status_for_appointment_integration', false, $appointment_id, '', ''); 
            if($bookingpress_check_status){
                $debug_log_data = json_encode( array( 'status_from_filter' => $bookingpress_check_status, 'filter_name' => 'bookingpress_check_status_for_appointment_integration' ) );
                do_action( 'bookingpress_integration_log_entry', 'google_calendar_debug_logs', 'Google Calendar Event Creation', 'BookingPress GC - Waiting List', 'Appointment is in waiting list', $debug_log_data, $bookingpress_debug_integration_log_id );
                $BookingPress->bookingpress_update_settings( 'bpa_gc_cron_app_id_' . $appointment_id, 'bpa_gc_cron', 0 );
                return;
            }

            $appointment_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $appointment_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally.

            $staff_member_id = !empty($appointment_data['bookingpress_staff_member_id']) ? intval($appointment_data['bookingpress_staff_member_id']) : 0 ;
            $service_id = !empty($appointment_data['bookingpress_service_id']) ? intval($appointment_data['bookingpress_service_id']) : 0 ;
            
            if( empty( $appointment_data ) || empty( $staff_member_id ) || empty( $appointment_data['bookingpress_entry_id'] ) || empty($service_id) ){
                $debug_log_data = json_encode( array( 'appointment_data' => $appointment_data, 'staff_id' => $staff_member_id, 'service_id' => $service_id  ) );
                do_action( 'bookingpress_integration_log_entry', 'google_calendar_debug_logs', 'Google Calendar Event Creation', 'BookingPress GC - Empty data passed', 'Some of the data is empty', $debug_log_data, $bookingpress_debug_integration_log_id );
                $BookingPress->bookingpress_update_settings( 'bpa_gc_cron_app_id_' . $appointment_id, 'bpa_gc_cron', 0 );
                return;
            }

            $calendarId = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_selected_gcalendar' );
            if( empty( $calendarId ) ){
                $debug_log_data = json_encode( array( 'calendarID' => $calendarId, 'staff_id' => $staff_member_id ) );
                do_action( 'bookingpress_integration_log_entry', 'google_calendar_debug_logs', 'Google Calendar Event Creation', 'BookingPress GC - Empty Calendar ID', 'Calendar not selected', $debug_log_data, $bookingpress_debug_integration_log_id );
                $BookingPress->bookingpress_update_settings( 'bpa_gc_cron_app_id_' . $appointment_id, 'bpa_gc_cron', 0 );
                return;
            }

            $bookingpress_staff_gcalendar_enable = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_staff_gcalendar_enable');
            if(empty($bookingpress_staff_gcalendar_enable) || $bookingpress_staff_gcalendar_enable == 'false' ) {
                $debug_log_data = json_encode( array( 'calendar_status' => $bookingpress_staff_gcalendar_enable, 'staff_id' => $staff_member_id ) );
                do_action( 'bookingpress_integration_log_entry', 'google_calendar_debug_logs', 'Google Calendar Event Creation', 'BookingPress GC - Calendar Option is not activated', 'Calendar not Activated', $debug_log_data, $bookingpress_debug_integration_log_id );
                $BookingPress->bookingpress_update_settings( 'bpa_gc_cron_app_id_' . $appointment_id, 'bpa_gc_cron', 0 );
                return; 
            }

            $appointment_status = esc_html($appointment_data['bookingpress_appointment_status']);
            if($appointment_status == '3' || $appointment_status == '4' || $appointment_status == '5' || $appointment_status == '6' ) {
                $debug_log_data = json_encode( array( 'appointment_status' => $appointment_status, 'staff_id' => $staff_member_id ) );
                do_action( 'bookingpress_integration_log_entry', 'google_calendar_debug_logs', 'Google Calendar Event Creation', 'BookingPress GC - Appointment is neither pending nor approved', 'Appointment could not be synchronized due to status', $debug_log_data, $bookingpress_debug_integration_log_id );
                $BookingPress->bookingpress_update_settings( 'bpa_gc_cron_app_id_' . $appointment_id, 'bpa_gc_cron', 0 );
                return;
            }  

            require_once BOOKINGPRESS_GOOGLE_CALENDAR_LIBRARY_DIR . "/vendor/autoload.php";

            $bookingpress_addon_popup_field_form = $this->bookingpress_get_google_calendar_credentials();
            $bookingpress_start_time = esc_html($appointment_data['bookingpress_appointment_time']);
            $bookingpress_end_time = esc_html($appointment_data['bookingpress_appointment_end_time']);
            $bookingpress_appointment_date = esc_html($appointment_data['bookingpress_appointment_date']);
            $service_duration      = esc_html( $appointment_data['bookingpress_service_duration_val'] );
            $service_duration_unit = esc_html( $appointment_data['bookingpress_service_duration_unit'] );
            $bookingpress_service_name = esc_html($appointment_data['bookingpress_service_name']) ;

            $bookingpress_start_date_time = date('Y-m-d',strtotime($bookingpress_appointment_date)).'T'.date('H:i:s',strtotime($bookingpress_start_time));
            if( 'd' ==  $service_duration_unit  ){
                if( 1 < $service_duration ){
                    $bookingpress_end_date_time  = date('Y-m-d',strtotime($bookingpress_appointment_date . '+' . ( $service_duration - 1 ) . ' days')).'T23:59:59';
                } else {
                    $bookingpress_end_date_time  = date('Y-m-d',strtotime($bookingpress_appointment_date ) ).'T23:59:59';
                }
            } else {
                if($bookingpress_end_time == '24:00:00') {
                    $bookingpress_end_date_time = date('Y-m-d', strtotime($bookingpress_appointment_date . '+1 days')).'T00:00:00';
                } else {
                    $bookingpress_end_date_time  = date('Y-m-d',strtotime($bookingpress_appointment_date)).'T'.date('H:i:s',strtotime($bookingpress_end_time));
                }
            }

            $bookingpress_event_enable_meet = $bookingpress_addon_popup_field_form['google_calendar_meet'];            
            $bookingpress_gcalendar_service = $this->bookingpress_get_staff_google_calendar_service( $staff_member_id );
            if( null == $bookingpress_gcalendar_service ){
                $debug_log_data = json_encode( array( 'gc_service' => $bookingpress_gcalendar_service, 'staff_id' => $staff_member_id ) );
                do_action( 'bookingpress_integration_log_entry', 'google_calendar_debug_logs', 'Google Calendar Event Creation', 'BookingPress GC - Calendar API returns null value in service object', 'Appointment processing failes due to service object gets nulled value', $debug_log_data, $bookingpress_debug_integration_log_id );
                $BookingPress->bookingpress_update_settings( 'bpa_gc_cron_app_id_' . $appointment_id, 'bpa_gc_cron', 0 );
                return;
            }
            
            $already_booked_appointment = $wpdb->get_results( $wpdb->prepare( "SELECT bpa.* FROM {$tbl_bookingpress_appointment_bookings} bpa LEFT JOIN {$tbl_bookingpress_staffmembers_services} bps ON bpa.bookingpress_staff_member_id=bps.bookingpress_staffmember_id WHERE bpa.bookingpress_service_id = %d AND bpa.bookingpress_appointment_status != %d AND bpa.bookingpress_appointment_status != %d AND bpa.bookingpress_staff_member_id = %d AND bpa.bookingpress_appointment_date = %s AND bpa.bookingpress_appointment_time = %s AND bpa.bookingpress_appointment_booking_id != %d AND bps.bookingpress_service_capacity > %d AND bpa.bookingpress_google_calendar_event_id != '' ", $service_id, 3, 4, $staff_member_id, $bookingpress_appointment_date, $bookingpress_start_time, $appointment_id, 1),ARRAY_A); // phpcs:ignore

            $bookingpress_event_id = $bookingpress_event_link = '';
            if(!empty($already_booked_appointment)) {
                foreach($already_booked_appointment as $key => $val) {
                    $bookingpress_google_calendar_event_id = !empty($val['bookingpress_google_calendar_event_id']) ? esc_html($val['bookingpress_google_calendar_event_id']) : '';
                    $bookingpress_google_calendar_event_link = !empty($val['bookingpress_google_calendar_event_link']) ? esc_html($val['bookingpress_google_calendar_event_link']) : '';
                    $bookingpress_appointment_booking_id = !empty($val['bookingpress_appointment_booking_id']) ? intval($val['bookingpress_appointment_booking_id']) : '';

                    if(!empty($bookingpress_google_calendar_event_id)) {

                        $bookignpress_is_metting_exist=$this->bookignpress_is_google_event_exist($calendarId,$bookingpress_google_calendar_event_id,$staff_member_id);

                        if( !empty($bookignpress_is_metting_exist) && $bookignpress_is_metting_exist == 1 ){
                            $bookingpress_event_id = $bookingpress_google_calendar_event_id;
                            $bookingpress_event_link = $bookingpress_google_calendar_event_link;

                        } elseif($bookignpress_is_metting_exist == 0) {
                            $wpdb->update(
                                $tbl_bookingpress_appointment_bookings,
                                array(
                                    'bookingpress_google_calendar_event_id' => '',
                                    'bookingpress_google_calendar_event_link' =>  '',
                                ),
                                array(
                                    'bookingpress_appointment_booking_id' => $bookingpress_appointment_booking_id
                                )
                            );
                        }
                    }
                }
            }   

            $bookingpress_event_title = $this->bookingpress_google_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['google_calendar_event_title'], $appointment_data );
            $bookingpress_event_description = $this->bookingpress_google_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['google_calendar_event_description'], $appointment_data );

            $bookingpress_event_location = $this->bookingpress_retrieve_location_field_data( $bookingpress_addon_popup_field_form['google_calendar_event_location'], $appointment_data );
           
            $user_timezone = wp_timezone_string();
            $event_data = array(
                'summary' => $bookingpress_event_title,
                'location' => $bookingpress_event_location,
                'description' => $bookingpress_event_description,
                'start' => array(
                    'dateTime' => $bookingpress_start_date_time,
                    'timeZone' => $user_timezone    
                ),
                'end' => array(
                    'dateTime' => $bookingpress_end_date_time,
                    'timeZone' => $user_timezone
                )
            );

            if(!empty( $bookingpress_event_id )) {

                try{                    
                    $bookingpress_gevent_data = $bookingpress_gcalendar_service->events->get( $calendarId, $bookingpress_event_id );
                    $bookingpress_new_start_time = new Google_Service_Calendar_Event();
                    $bookingpress_gevent_data->setSummary( $bookingpress_event_title );
                    $bookingpress_gevent_data->setDescription( $bookingpress_event_description);
                    $bookingpress_gevent_data->setLocation( $bookingpress_event_location );                    
                    $bookingpress_gcalendar_service->events->update( $calendarId, $bookingpress_event_id, $bookingpress_gevent_data );

                } catch( Exception $e ){

                    $debug_log_data = array(
                        'google_calendar_exception_message' => $e->get_message(),
                        'google_calendar_exception_object' => $e,
                        'google_calendar_log_placement' => 'failed update google calendar event',
                        'google_calendar_sent_data' => array(
                            'calendar_id' => $calendarId,
                            'event_object' => $bookingpress_gcalendar_service,
                            'appointment_data' => $appointment_data
                        ),
                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                    );
                    do_action( 'bookingpress_integration_log_entry', 'google_calendar_debug_logs', 'Update Appointment Google Calendar Event', 'Google Calendar Integration', 'Update Google Calendar Event Summary and description', $debug_log_data, $bookingpress_debug_integration_log_id );
                }
                
                $wpdb->update(
                    $tbl_bookingpress_appointment_bookings,
                    array(
                        'bookingpress_google_calendar_event_id' => $bookingpress_event_id,
                        'bookingpress_google_calendar_event_link' => $bookingpress_event_link,
                    ),
                    array(
                        'bookingpress_appointment_booking_id' => $appointment_id
                    )
                );
            } else {

                /** Check if the appointment id has already linked with another Google Calendar ID */
                $get_event_id = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_google_calendar_event_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d" , $appointment_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally.

                if( !empty( $get_event_id ) && !empty( $get_event_id->bookingpress_google_calendar_event_id ) ){
                    /** If appointment contains the Google Calendar Event ID then remove the appointment id from the options and locked data */
                    $debug_log_data = wp_json_encode(
                        array(
                            'appointment_id' => $appointment_id,
                            'existing_event_id_in_db' => $get_event_id->bookingpress_google_calendar_event_id,
                            'backtrace' => wp_debug_backtrace_summary( null, 0, false )
                        )
                    );
                    do_action( 'bookingpress_integration_log_entry', 'google_calendar_debug_logs', 'Duplicate Event Execution', 'Duplicate Event Execution', 'Duplicate Event Execution', $debug_log_data, $bookingpress_debug_integration_log_id );

                    $bookingpress_gc_cron_app_data_data = $BookingPress->bookingpress_get_settings( 'bpa_gc_cron_app_data_'.$appointment_id );
                    
                    /** Remove Appointment ID & Data from the schedular details */
                    $bookingpress_gc_cron_app_data_data = json_decode( $bookingpress_gc_cron_app_data_data, true );
                    
                    if( !empty( $bookingpress_gc_cron_app_data_data ) ){
                        $bpa_schedular_data = json_encode( $bookingpress_gc_cron_app_data_data ) .' at ' . date('Y-m-d H:i:s', current_time('timestamp') ) . ' from duplicate execution';
                        do_action( 'bookingpress_integration_log_entry', 'google_calendar_debug_logs', 'Duplicate Event Execution Schedular Data', 'Duplicate Event Execution Schedular Data', 'Duplicate Event Execution Schedular Data - ' . $appointment_id, $bpa_schedular_data, $bookingpress_debug_integration_log_id );
                        //$BookingPress->bookingpress_update_settings( 'bpa_gc_cron_app_data_' . $appointment_id, 'bpa_gc_cron', '' );
                    }
                    /** Return from here and stop synchronization process */
                    return;
                }

                $event = new Google_Service_Calendar_Event( $event_data );
                $event_extra_vars = array();

                if( 'true' == $bookingpress_event_enable_meet ){
                    $solution_key = new Google_Service_Calendar_ConferenceSolutionKey();
                    $solution_key->setType('hangoutsMeet');
                    $confrequest = new Google_Service_Calendar_CreateConferenceRequest();
                    $confrequest->setRequestId("3whatisup3");
                    $confrequest->setConferenceSolutionKey($solution_key);
                    $confdata = new Google_Service_Calendar_ConferenceData();
                    $confdata->setCreateRequest($confrequest);
                    $event_extra_vars['conferenceDataVersion'] = 1;
                    $event->setConferenceData($confdata);
                }

                try{

                    $get_event_id = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_google_calendar_event_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d" , $appointment_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally.

                    if( !empty( $get_event_id ) && !empty( $get_event_id->bookingpress_google_calendar_event_id ) ){
                        /** Check if appointment ID has already connected with Events */
                        return;
                    }

                    $response = $bookingpress_gcalendar_service->events->insert($calendarId, $event, $event_extra_vars);
                    
                    $bookingpress_google_meet_event_url = !empty($response->hangoutLink) ? $response->hangoutLink : '';
                    if(!empty($bookingpress_google_meet_event_url)){
                        $wpdb->update($tbl_bookingpress_appointment_bookings, array( 'bookingpress_google_calendar_event_link' => $bookingpress_google_meet_event_url ), array( 'bookingpress_appointment_booking_id' => $appointment_data['bookingpress_appointment_booking_id'] ) );    
                    }
                    $appointment_event_id = $response->id;

                    $get_event_id = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_google_calendar_event_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d" , $appointment_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally.

                    if( !empty( $get_event_id ) && !empty( $get_event_id->bookingpress_google_calendar_event_id ) ){
                        /** Check if appointment ID has already connected with Events & delete the new one */
                        $bookingpress_delete_event_response = $bookingpress_gcalendar_service->events->delete( $calendarId, $appointment_event_id );
                        $debug_log_data = array(
                            'msg' => 'Deleting Duplicate Created Event for the same ID',
                            'appointment_id' => $appointment_id,
                            'existing_event_id' => $get_event_id->bookingpress_google_calendar_event_id,
                            'new_event_id' => $appointment_event_id,
                            'delete_event_response' => $bookingpress_delete_event_response
                        );
                        do_action( 'bookingpress_integration_log_entry', 'google_calendar_debug_logs', 'DELETING DUPLICATE CREATED EVENT FOR APPOINTMENT ID => '. $appointment_id, 'Google Calendar Integration', 'Creating Google Calendar Event', $debug_log_data, $bookingpress_debug_integration_log_id );

                        $this->bookingpress_google_calendar_send_remaining_notification( $appointment_id, $entry_id );

                        return;
                    }

                    $wpdb->update(
                        $tbl_bookingpress_appointment_bookings,
                        array(
                            'bookingpress_google_calendar_event_id' => $appointment_event_id
                        ),
                        array(
                            'bookingpress_appointment_booking_id' => $appointment_data['bookingpress_appointment_booking_id']
                        )
                    );

                    /** Update settings to value 2 as finished */
                    $BookingPress->bookingpress_update_settings( 'bpa_gc_cron_app_id_' . $appointment_id, 'bpa_gc_cron', 2 );

                    $this->bookingpress_google_calendar_send_remaining_notification( $appointment_id, $entry_id );


                } catch( Exception $e ){
                    $debug_log_data = array(
                        'google_calendar_exception_message' => $e->get_message(),
                        'google_calendar_exception_object' => $e,
                        'google_calendar_log_placement' => 'failed creating google calendar event',
                        'google_calendar_sent_data' => array(
                            'calendar_id' => $calendarId,
                            'event_object' => $event,
                            'event_extra_vars' => $event_extra_vars,
                            'appointment_data' => $appointment_data
                        ),
                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                    );
                    do_action( 'bookingpress_integration_log_entry', 'google_calendar_debug_logs', 'Assigning Appointment Google Calendar Event', 'Google Calendar Integration', 'Creating Google Calendar Event', $debug_log_data, $bookingpress_debug_integration_log_id );

                    if( !isset( $counter ) ){
                        $counter  = 1;
                    }
                    $counter++;

                    $counter = update_option( 'bpa_gc_cron_counter_'. $appointment_id, $counter );

                    $next_occurence_time = current_time( 'timestamp' ) + ( 10 * 60 );
                    wp_schedule_single_event( $next_occurence_time, 'bookingpress_schedule_staffmember_gc_event', array( $appointment_id, $entry_id, $payment_gateway_data, $counter ) );

                    $debug_log_data = array(
                        'appointment_id' => $appointment_id,
                        'entry_id' => $entry_id,
                        'counter' => $counter
                    );
                    do_action( 'bookingpress_integration_log_entry', 'google_calendar_debug_logs', 'Google Calendar Integration', 'Google Calendar - Appointment ID ' . $appointment_id . ' set for re-attempt('.$counter.') at '. date('Y-m-d H:i:s', $next_occurence_time ), 'Creating Google Calendar Event', $debug_log_data, $bookingpress_debug_integration_log_id );

                    $BookingPress->bookingpress_update_settings( 'bpa_gc_cron_app_id_' . $appointment_id, 'bpa_gc_cron', 0 );
                }
            }   

            /** assigning appointment to staff member Google Calendar */
        }

        function bookingpress_google_calendar_send_remaining_notification( $appointment_id, $entry_id ){
            global $wpdb;
            $get_email_notification_data = $wpdb->get_results( $wpdb->prepare( "SELECT option_name,option_value FROM " . $wpdb->options . " WHERE option_name LIKE %s", 'bookingpress_gc_send_notification_' . $appointment_id .'_'. $entry_id.'%'), ARRAY_A );
            if( '' != $get_email_notification_data ){
                global $bookingpress_email_notifications;
                foreach( $get_email_notification_data as $opt_val  ){
                    $notification_data = $opt_val['option_value'];
                    $opt_name = $opt_val['option_name'];

                    $is_sent = get_option( $opt_name .'_is_sent' );

                    if( !empty( $is_sent ) && 1 == $is_sent ){
                        delete_option( $opt_name );
                        continue;
                    }

                    if( preg_match( '/_is_sent$/', $opt_name ) ){
                        continue;
                    }

                    $args = json_decode( $notification_data, true );
                    $template_type = !empty( $args[0] ) ? $args[0] : '';
                    $notification_name = !empty( $args[1] ) ? $args[1] : '';
                    $appointment_id = !empty( $args[2] ) ? $args[2] : '';
                    $receiver_email_id = !empty( $args[3] ) ? $args[3] : '';
                    $cc_emails = !empty( $args[4] ) ? $args[4] : '';
                    $force = true;
                    delete_option( $opt_name );
                    $bookingpress_email_notifications->bookingpress_send_email_notification( $template_type, $notification_name, $appointment_id, $receiver_email_id, $cc_emails, $force );
                    update_option( 'bookingpress_gc_send_notification_' . $appointment_id . '_' . $entry_id .'_'. $template_type .'_'.$notification_name .'_is_sent', 1 );
                }
            }
        }
 
        function bookingpress_remove_google_calendar_event( $appointment_id ){

            global $bookingpress_pro_staff_members, $wpdb, $tbl_bookingpress_appointment_bookings;
            
            if( empty( $appointment_id ) ){
                return;
            }
            
            $is_staffmember_activated = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();
            if( !$is_staffmember_activated ){
                return;
            }
            
            $bookingpress_check_status = apply_filters( 'bookingpress_check_status_for_appointment_integration', false, $appointment_id, '', ''); 
            if($bookingpress_check_status){
                return;
            }

            $appointment_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $appointment_id ),ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally.

            $event_id = !empty( $appointment_data['bookingpress_google_calendar_event_id'] ) ? esc_html($appointment_data['bookingpress_google_calendar_event_id']) : '';  
            $staff_member_id = !empty($appointment_data['bookingpress_staff_member_id']) ? intval($appointment_data['bookingpress_staff_member_id']) : 0 ;
            $service_id = !empty($appointment_data['bookingpress_service_id']) ? intval($appointment_data['bookingpress_service_id']) : 0 ;            

            if( empty( $appointment_data ) || empty($event_id) || empty( $staff_member_id ) || empty($service_id) ){
                return;
            }

            $calendarId = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_selected_gcalendar' );
            if( empty( $calendarId ) ){
                return;
            }

            $bookingpress_staff_gcalendar_enable = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_staff_gcalendar_enable');
            if(empty($bookingpress_staff_gcalendar_enable) || $bookingpress_staff_gcalendar_enable == 'false' ) {
                return; 
            }
        

            $appointment_status = esc_html($appointment_data['bookingpress_appointment_status']);

            $bookingpress_gcalendar_service = $this->bookingpress_get_staff_google_calendar_service( $staff_member_id );
            $bookingpress_addon_popup_field_form = $this->bookingpress_get_google_calendar_credentials();


            $bpa_other_bookings = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id != %d AND bookingpress_google_calendar_event_id = %s AND bookingpress_appointment_status != %d AND bookingpress_appointment_status != %d", $appointment_id, $event_id, 3, 4 ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally.

            if($bpa_other_bookings > 0 ) {          

                $bookingpress_event_title = $this->bookingpress_google_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['google_calendar_event_title'], $appointment_data,'delete' );
                $bookingpress_event_description = $this->bookingpress_google_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['google_calendar_event_description'], $appointment_data ,'delete');
                $bookingpress_event_location = $this->bookingpress_retrieve_location_field_data( $bookingpress_addon_popup_field_form['google_calendar_event_location'], $appointment_data );

                try{

                    $bookingpress_gevent_data = $bookingpress_gcalendar_service->events->get( $calendarId, $event_id );
                    $bookingpress_new_start_time = new Google_Service_Calendar_Event();
                    $bookingpress_gevent_data->setSummary( $bookingpress_event_title );
                    $bookingpress_gevent_data->setDescription( $bookingpress_event_description);
                    $bookingpress_gevent_data->setLocation( $bookingpress_event_location);

                    $bookingpress_gcalendar_service->events->update( $calendarId, $event_id, $bookingpress_gevent_data );

                } catch( Exception $e ){

                    $debug_log_data = array(
                        'google_calendar_exception_message' => $e->get_message(),
                        'google_calendar_exception_object' => $e,
                        'google_calendar_log_placement' => 'failed update google calendar event',
                        'google_calendar_sent_data' => array(
                            'calendar_id' => $calendarId,
                            'event_object' => $bookingpress_gcalendar_service,
                            'appointment_data' => $appointment_data
                        ),
                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                    );
                    do_action( 'bookingpress_integration_log_entry', 'google_calendar_debug_logs', 'Update Appointment Google Calendar Event', 'Google Calendar Integration', 'Update Google Calendar Event Summary and description', $debug_log_data, $bookingpress_debug_integration_log_id );
                }
                
            } else {
            
                $is_exist_event = $this->bookignpress_is_google_event_exist($calendarId,$event_id,$staff_member_id);
                if( $is_exist_event == 0) {                                        
                    return;
                }

                try{    

                    $response = $bookingpress_gcalendar_service->events->delete( $calendarId, $event_id );

                } catch( Exception $e ){

                    $debug_log_data = array(
                        'google_calendar_exception_message' => $e->getMessage(),
                        'google_calendar_exception_object' => $e,
                        'google_calendar_log_placement' => 'failed removing google calendar event',
                        'google_calendar_sent_data' => array(
                            'calendar_id' => $calendarId,
                            'event_object' => $event_id,
                            'appointment_data' => $appointment_data
                        ),
                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                    );
                    do_action( 'bookingpress_integration_log_entry', 'google_calendar_debug_logs', 'Removing Google Calendar Event', 'Google Calendar Integration', 'Removing Google Calendar Event', $debug_log_data, $bookingpress_debug_integration_log_id );
                }                    
            }    
        }

        function bookingpress_update_google_calendar_event( $appointment_id, $appointment_status = '3') {

            global $bookingpress_pro_staff_members, $wpdb, $tbl_bookingpress_appointment_bookings;
            
            if( empty( $appointment_id ) || empty( $appointment_status ) ){
                return;
            }

            $bookingpress_check_status = apply_filters( 'bookingpress_check_status_for_appointment_integration', false, $appointment_id, $appointment_status, ''); 
            if($bookingpress_check_status){
                return;
            }

            $is_staffmember_activated = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();
            if( !$is_staffmember_activated ){
                return;
            }

            $appointment_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $appointment_id ),ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally.

            $event_id = !empty( $appointment_data['bookingpress_google_calendar_event_id'] ) ? esc_html($appointment_data['bookingpress_google_calendar_event_id']) : '';  
            $staff_member_id = !empty($appointment_data['bookingpress_staff_member_id']) ? intval($appointment_data['bookingpress_staff_member_id']) : 0 ;
            $service_id = !empty($appointment_data['bookingpress_service_id']) ? intval($appointment_data['bookingpress_service_id']) : 0 ;            
            if( empty( $appointment_data ) || empty( $staff_member_id ) || empty($service_id) ){
                return;
            }

            $calendarId = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_selected_gcalendar' );
            if( empty( $calendarId ) ){
                return;
            }

            $bookingpress_staff_gcalendar_enable = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_staff_gcalendar_enable');
            if(empty($bookingpress_staff_gcalendar_enable) || $bookingpress_staff_gcalendar_enable == 'false' ) {
                return; 
            }

            $appointment_status = esc_html($appointment_data['bookingpress_appointment_status']);
            $bookingpress_gcalendar_service = $this->bookingpress_get_staff_google_calendar_service( $staff_member_id );
            $bookingpress_addon_popup_field_form = $this->bookingpress_get_google_calendar_credentials();

            if(($appointment_status == '1' || $appointment_status == '2' ) && empty( $event_id ) ) {

                $this->bookingpress_assign_appointment_to_staff_member($appointment_id);

            } else {                                

                if( empty( $event_id ) ){
                    return;
                }

                $bpa_other_bookings = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id != %d AND bookingpress_google_calendar_event_id = %s AND bookingpress_appointment_status != %d AND bookingpress_appointment_status != %d", $appointment_id, $event_id, 3, 4 ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally.

                if($bpa_other_bookings > 0 ) {

                    if($appointment_status == '3' || $appointment_status == '4' ) {
                        $wpdb->update(
                            $tbl_bookingpress_appointment_bookings,
                            array(
                                'bookingpress_google_calendar_event_id' => '',
                                'bookingpress_google_calendar_event_link' => ''
                            ),
                            array(
                                'bookingpress_appointment_booking_id' => $appointment_id
                            )
                        );
                    }                    

                    $bookingpress_event_title = $this->bookingpress_google_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['google_calendar_event_title'], $appointment_data );
                    $bookingpress_event_description = $this->bookingpress_google_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['google_calendar_event_description'], $appointment_data );
                    $bookingpress_event_location = $this->bookingpress_retrieve_location_field_data( $bookingpress_addon_popup_field_form['google_calendar_event_location'], $appointment_data );

                    try{                    
                        $bookingpress_gevent_data = $bookingpress_gcalendar_service->events->get( $calendarId, $event_id );
                        $bookingpress_new_start_time = new Google_Service_Calendar_Event();
                        $bookingpress_gevent_data->setSummary( $bookingpress_event_title );
                        $bookingpress_gevent_data->setDescription( $bookingpress_event_description );
                        $bookingpress_gevent_data->setLocation( $bookingpress_event_location );
                        
                        $bookingpress_gcalendar_service->events->update( $calendarId, $event_id, $bookingpress_gevent_data );
    
                    } catch( Exception $e ){
    
                        $debug_log_data = array(
                            'google_calendar_exception_message' => $e->get_message(),
                            'google_calendar_exception_object' => $e,
                            'google_calendar_log_placement' => 'failed update google calendar event',
                            'google_calendar_sent_data' => array(
                                'calendar_id' => $calendarId,
                                'event_object' => $bookingpress_gcalendar_service,
                                'appointment_data' => $appointment_data
                            ),
                            'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                        );
                        do_action( 'bookingpress_integration_log_entry', 'google_calendar_debug_logs', 'Update Appointment Google Calendar Event', 'Google Calendar Integration', 'Update Google Calendar Event Summary and description', $debug_log_data, $bookingpress_debug_integration_log_id );
                    }               

                    return;              
                }
                
                $is_exist_event = $this->bookignpress_is_google_event_exist($calendarId,$event_id,$staff_member_id);

                if( '3' == $appointment_status || '4' == $appointment_status ){

                    $wpdb->update(
                        $tbl_bookingpress_appointment_bookings,
                        array(
                            'bookingpress_google_calendar_event_id' => '',
                            'bookingpress_google_calendar_event_link' => ''
                        ),
                        array(
                            'bookingpress_appointment_booking_id' => $appointment_id
                        )
                    );
                    
                    if($is_exist_event == 0) {
                        return;
                    }

                    try{    

                        $response = $bookingpress_gcalendar_service->events->delete( $calendarId, $event_id );

                    } catch( Exception $e ){

                        $debug_log_data = array(
                            'google_calendar_exception_message' => $e->getMessage(),
                            'google_calendar_exception_object' => $e,
                            'google_calendar_log_placement' => 'failed removing google calendar event',
                            'google_calendar_sent_data' => array(
                                'calendar_id' => $calendarId,
                                'event_object' => $event_id,
                                'appointment_data' => $appointment_data
                            ),
                            'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                        );
                        do_action( 'bookingpress_integration_log_entry', 'google_calendar_debug_logs', 'Removing Google Calendar Event', 'Google Calendar Integration', 'Removing Google Calendar Event', $debug_log_data, $bookingpress_debug_integration_log_id );
                    }
                    
                } else if( '1' == $appointment_status || '2' == $appointment_status ) {                                        
                    
                    if($is_exist_event == 0) {                 
                        $this->bookingpress_assign_appointment_to_staff_member( $appointment_id );
                    } else {

                        $bookingpress_event_title = $this->bookingpress_google_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['google_calendar_event_title'], $appointment_data );
                        $bookingpress_event_description = $this->bookingpress_google_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['google_calendar_event_description'], $appointment_data );
                        $bookingpress_event_location = $this->bookingpress_retrieve_location_field_data( $bookingpress_addon_popup_field_form['google_calendar_event_location'], $appointment_data );
    
                        try{                    
                            $bookingpress_gevent_data = $bookingpress_gcalendar_service->events->get( $calendarId, $event_id );
                            $bookingpress_new_start_time = new Google_Service_Calendar_Event();
                            $bookingpress_gevent_data->setSummary( $bookingpress_event_title );
                            $bookingpress_gevent_data->setDescription( $bookingpress_event_description);
                            $bookingpress_gevent_data->setLocation( $bookingpress_event_location);
                            
                            $bookingpress_gcalendar_service->events->update( $calendarId, $event_id, $bookingpress_gevent_data );
        
                        } catch( Exception $e ){
        
                            $debug_log_data = array(
                                'google_calendar_exception_message' => $e->get_message(),
                                'google_calendar_exception_object' => $e,
                                'google_calendar_log_placement' => 'failed update google calendar event',
                                'google_calendar_sent_data' => array(
                                    'calendar_id' => $calendarId,
                                    'event_object' => $bookingpress_gcalendar_service,
                                    'appointment_data' => $appointment_data
                                ),
                                'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                            );
                            do_action( 'bookingpress_integration_log_entry', 'google_calendar_debug_logs', 'Update Appointment Google Calendar Event', 'Google Calendar Integration', 'Update Google Calendar Event Summary and description', $debug_log_data, $bookingpress_debug_integration_log_id );
                        }     
                    }
                }
            }
        }

        function bookingpress_get_staff_google_calendar_service( $staff_member_id, $cron = false, $unique_id = '' ){
            global $bookingpress_pro_staff_members;
            $staffmember_cal_oauth_data = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_staff_gcalendar_auth' );
            if( empty( $staffmember_cal_oauth_data ) ){
                return false;
            }

            if( !empty( $unique_id ) ){
                $bkp_calendar_key = 'bkp_calendar_uniq_service_' . $unique_id;

                $bkp_service_obj = get_transient( $bkp_calendar_key );

                if( !empty( $bkp_service_obj ) ){
                    return json_decode( $bkp_service_obj );
                }
            }

            $bookingpress_addon_popup_field_form = $this->bookingpress_get_google_calendar_credentials();

            $bookingpress_client_secret = $bookingpress_addon_popup_field_form['google_calendar_client_secret'];        
            $bookingpress_client_id =  $bookingpress_addon_popup_field_form['google_calendar_client_id'];
            $bookingpress_redirect_url = $bookingpress_addon_popup_field_form['google_calendar_redirect_url'];
            $staff_member_oauth_data = json_decode( $staffmember_cal_oauth_data, true );

            require_once BOOKINGPRESS_GOOGLE_CALENDAR_LIBRARY_DIR . "/vendor/autoload.php";

            $client = new Google_Client();
            $client->setClientId($bookingpress_client_id);
            $client->setClientSecret( $bookingpress_client_secret );
            $client->setRedirectUri( $bookingpress_redirect_url);
            $client->setAccessToken( $staff_member_oauth_data );
            
            /** Refresh Google API Token */
            if( $client->isAccessTokenExpired() ){
                
                $is_refreshed = $client->refreshToken( $staff_member_oauth_data['refresh_token'] );
                if( !empty( $is_refreshed['error'] ) ){
                    global $bookingpress_debug_integration_log_id;
                    $debug_log_data = array(
                        'google_calendar_exception_object' => $is_refreshed,
                        'google_calendar_log_placement' => 'failed refreshing google access token',
                        'google_calendar_sent_data' => array(
                            'staff_member_oauth_data' => base64_encode( json_encode( $staff_member_oauth_data ) ),
                            'staff_member_id' => $staff_member_id
                        ),
                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                    );
                    do_action( 'bookingpress_integration_log_entry', 'google_calendar_debug_logs', 'Error while refreshing token', 'Google Calendar Refresh Token Error', 'Error while refreshing token', $debug_log_data, $bookingpress_debug_integration_log_id );
                    $bookingpress_pro_staff_members->update_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_display_invalid_token_notice', true );
                    return false;
                }
                $refresh_token = $staff_member_oauth_data['refresh_token'];
                $staff_member_oauth_data =  $client->getAccessToken();
                if( empty( $staff_member_oauth_data['refresh_token'] ) ){
                    $staff_member_oauth_data['refresh_token'] = $refresh_token;
                }
                $bookingpress_pro_staff_members->update_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_staff_gcalendar_auth', json_encode( $staff_member_oauth_data ) );
                $bookingpress_pro_staff_members->update_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_display_invalid_token_notice', false );
                $client->setAccessToken( $staff_member_oauth_data );
            } else {
                $verify_token_url = 'https://www.googleapis.com/oauth2/v3/tokeninfo';
                
                $args = array(
                    'timeout' => 5000,
                    'method' => 'GET',
                    'body' => array(
                        'access_token' => $staff_member_oauth_data['access_token']
                    )
                );
                $check_access_token = wp_remote_get( $verify_token_url, $args );
                
                
                if( is_wp_error( $check_access_token ) ){
                    global $bookingpress_debug_integration_log_id;
                    $debug_log_data = array(
                        'google_calendar_exception_object' => $check_access_token,
                        'google_calendar_log_placement' => 'failed refreshing google access token',
                        'google_calendar_sent_data' => array(
                            'staff_member_oauth_data' => base64_encode( json_encode( $staff_member_oauth_data ) ),
                            'staff_member_id' => $staff_member_id
                        ),
                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                    );
                    do_action( 'bookingpress_integration_log_entry', 'google_calendar_debug_logs', 'Error while retrieving services', 'Google Calendar Integration', 'Error while retrieving services', $debug_log_data, $bookingpress_debug_integration_log_id );
                    return false;
                }

                $valid_access_token_code = wp_remote_retrieve_response_code( $check_access_token );

                if( 200 != $valid_access_token_code ){
                    $validate_access_token = json_decode( wp_remote_retrieve_body( $check_access_token ), true );
                    
                    $bookingpress_pro_staff_members->update_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_display_invalid_token_notice', true );

                    global $bookingpress_debug_integration_log_id;
                    $debug_log_data = array(
                        'google_calendar_exception_object' => $check_access_token,
                        'google_calendar_log_placement' => 'invalid or revoked google access token',
                        'google_calendar_sent_data' => array(
                            'staff_member_oauth_data' => base64_encode( json_encode( $staff_member_oauth_data ) ),
                            'staff_member_id' => $staff_member_id
                        ),
                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                    );
                    do_action( 'bookingpress_integration_log_entry', 'google_calendar_debug_logs', 'Error on validating token', 'Google Calendar Integration', 'Error on validating token', $debug_log_data, $bookingpress_debug_integration_log_id );
                    return false;
                } else {
                    $bookingpress_pro_staff_members->update_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_display_invalid_token_notice', false );
                }
                
            }

        
            if( false == $cron  ){

                $bookingpress_gcalendar_service = new Google\Service\Calendar($client);

                if( !empty( $unique_id ) ){
                    $bkp_calendar_key = 'bkp_calendar_uniq_service_' . $unique_id;

                    set_transient( $bkp_calendar_key, json_encode( $bookingpress_gcalendar_service ), HOUR_IN_SECONDS );
                }
                
                return $bookingpress_gcalendar_service;
            }
        }

        function bookingpress_calendar_event_reschedule( $appointment_id ) {

            global $wpdb, $tbl_bookingpress_appointment_bookings, $bookingpress_pro_staff_members, $BookingPress, $tbl_bookingpress_appointment_meta,$bookingpress_debug_integration_log_id,$tbl_bookingpress_staffmembers_services;

            $allow_valid = wp_debug_backtrace_summary( null, 0, false );            
            if(!empty($allow_valid) && in_array("do_action('bookingpress_after_rescheduled_appointment')",$allow_valid) && in_array('bookingpress_calendar->bookingpress_save_appointment_booking_func',$allow_valid ) ){                
                return;
            }

            if( empty( $appointment_id ) ){
                return;
            }
            $is_staffmember_activated = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();
            if( !$is_staffmember_activated ){
                return;
            }

            $appointment_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $appointment_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally.

            $service_id = !empty($appointment_data['bookingpress_service_id']) ? intval($appointment_data['bookingpress_service_id']) : 0;
            $staff_member_id = !empty($appointment_data['bookingpress_staff_member_id']) ? intval($appointment_data['bookingpress_staff_member_id']) : 0;
            $bookingpress_entry_id = !empty($appointment_data['bookingpress_entry_id']) ? intval($appointment_data['bookingpress_entry_id']) : 0;            
            $bookingpress_event_id = !empty($appointment_data['bookingpress_google_calendar_event_id']) ? esc_html($appointment_data['bookingpress_google_calendar_event_id']) : '';            

            if( empty( $appointment_data ) || empty( $service_id ) || empty( $staff_member_id ) || empty( $bookingpress_entry_id ) ){
                return;
            }        

            $calendarId = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_selected_gcalendar' );
            if( empty( $calendarId ) ){
                return;
            }

            $bookingpress_staff_gcalendar_enable = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_staff_gcalendar_enable');
            if(empty($bookingpress_staff_gcalendar_enable) || $bookingpress_staff_gcalendar_enable == 'false' ) {
                return;
            }

            $appointment_status = esc_html($appointment_data['bookingpress_appointment_status']);
            $bookingpress_appointment_date = esc_html($appointment_data['bookingpress_appointment_date']);

            if($appointment_status == '5' || $appointment_status == '6' ) {
                return;
            }

            $bookingpress_gcalendar_service = $this->bookingpress_get_staff_google_calendar_service( $staff_member_id );
            $bookingpress_addon_popup_field_form = $this->bookingpress_get_google_calendar_credentials();
            $bookingpress_start_time = esc_html($appointment_data['bookingpress_appointment_time']);
            $bookingpress_end_time = esc_html($appointment_data['bookingpress_appointment_end_time']);
            $bookingpress_appointment_date = esc_html($appointment_data['bookingpress_appointment_date']);
            $service_duration      = esc_html( $appointment_data['bookingpress_service_duration_val'] );
            $service_duration_unit = esc_html( $appointment_data['bookingpress_service_duration_unit'] );
            $bookingpress_service_name = esc_html($appointment_data['bookingpress_service_name']) ;

            $bookingpress_start_date_time = date('Y-m-d',strtotime($bookingpress_appointment_date)).'T'.date('H:i:s',strtotime($bookingpress_start_time));
            if( 'd' ==  $service_duration_unit  ){
                if( 1 < $service_duration ){
                    $bookingpress_end_date_time  = date('Y-m-d',strtotime($bookingpress_appointment_date . '+' . ( $service_duration - 1 ) . ' days')).'T23:59:59';
                } else {
                    $bookingpress_end_date_time  = date('Y-m-d',strtotime($bookingpress_appointment_date ) ).'T23:59:59';
                }
            } else {
                if($bookingpress_end_time == '24:00:00') {
                    $bookingpress_end_date_time = date('Y-m-d', strtotime($bookingpress_appointment_date . '+1 days')).'T00:00:00';
                } else {
                    $bookingpress_end_date_time  = date('Y-m-d',strtotime($bookingpress_appointment_date)).'T'.date('H:i:s',strtotime($bookingpress_end_time));
                }
            }


            $user_timezone = wp_timezone_string();

            if(($appointment_status == '1' || $appointment_status == '2') && empty($bookingpress_event_id)) {
                                       
                $this->bookingpress_assign_appointment_to_staff_member( $appointment_id );

            } else {
                
                if(empty($bookingpress_event_id)) {
                    return;
                }                

                /** last appointment details */
                $last_appointment_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_appointment_meta_value FROM {$tbl_bookingpress_appointment_meta} WHERE bookingpress_appointment_meta_key = %s AND bookingpress_appointment_id = %d", '_bpa_last_appointment_data', $appointment_id ),ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_meta is table name defined globally.
                
                if( !empty( $last_appointment_data ) ) {

                    $last_appointment_data = json_decode( $last_appointment_data['bookingpress_appointment_meta_value'],true );

                    if( $last_appointment_data['bookingpress_staff_member_id'] != $staff_member_id || $bookingpress_start_time != $last_appointment_data['bookingpress_appointment_time'] || $bookingpress_appointment_date != $last_appointment_data ['bookingpress_appointment_date'] || $service_id != $last_appointment_data['bookingpress_service_id'] ) {                       

                        if($appointment_status == '3' && $appointment_status == '4' ) {

                            $bpa_other_bookings = $wpdb->get_row( $wpdb->prepare( "SELECT count(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id != %d AND bookingpress_google_calendar_event_id = %s AND bookingpress_appointment_status != %d AND bookingpress_appointment_status != %d", $appointment_id, $bookingpress_event_id, 3, 4 ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally.
                            $wpdb->update(
                                $tbl_bookingpress_appointment_bookings,
                                array(
                                    'bookingpress_google_calendar_event_id' => '',
                                    'bookingpress_google_calendar_event_link' => '',
                                ),
                                array(
                                    'bookingpress_appointment_booking_id' => $appointment_id
                                )
                            );

                            if($bpa_other_bookings > 0) {     

                                $bookingpress_event_title = $this->bookingpress_google_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['google_calendar_event_title'], $last_appointment_data );
                                $bookingpress_event_description = $this->bookingpress_google_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['google_calendar_event_description'], $last_appointment_data );
                                $bookingpress_event_location = $this->bookingpress_retrieve_location_field_data( $bookingpress_addon_popup_field_form['google_calendar_event_location'], $appointment_data );
                                
                                try{

                                    $bookingpress_gevent_data = $bookingpress_gcalendar_service->events->get( $calendarId, $bookingpress_event_id );
                                    $bookingpress_new_start_time = new Google_Service_Calendar_Event();
                                    $bookingpress_gevent_data->setSummary( $bookingpress_event_title );
                                    $bookingpress_gevent_data->setDescription( $bookingpress_event_description);                                    
                                    $bookingpress_gevent_data->setLocation( $bookingpress_event_location);                                    
                                    $bookingpress_gcalendar_service->events->update( $calendarId, $bookingpress_event_id, $bookingpress_gevent_data );

                                } catch( Exception $e ){
                                    $debug_log_data = array(
                                        'google_calendar_exception_message' => $e->get_message(),
                                        'google_calendar_exception_object' => $e,
                                        'google_calendar_log_placement' => 'failed update google calendar event',
                                        'google_calendar_sent_data' => array(
                                            'calendar_id' => $calendarId,
                                            'event_object' => $bookingpress_gcalendar_service,
                                            'appointment_data' => $appointment_data
                                        ),
                                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                                    );
                                    do_action( 'bookingpress_integration_log_entry', 'google_calendar_debug_logs', 'Update Appointment Google Calendar Event', 'Google Calendar Integration', 'Update Google Calendar Event Summary and description', $debug_log_data, $bookingpress_debug_integration_log_id );
                                }

                            } else {

                                /* check event exist or not if the event exist the delete the event*/

                                $is_exist_event = $this->bookignpress_is_google_event_exist($calendarId,$bookingpress_event_id,$staff_member_id);

                                if($is_exist_event == 0) {
                                    return;
                                }
                                try{    

                                    $response = $bookingpress_gcalendar_service->events->delete( $calendarId, $bookingpress_event_id );

                                } catch( Exception $e ){

                                    $debug_log_data = array(
                                        'google_calendar_exception_message' => $e->getMessage(),
                                        'google_calendar_exception_object' => $e,
                                        'google_calendar_log_placement' => 'failed removing google calendar event',
                                        'google_calendar_sent_data' => array(
                                            'calendar_id' => $calendarId,
                                            'event_object' => $bookingpress_event_id,
                                            'appointment_data' => $appointment_data
                                        ),
                                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                                    );
                                    do_action( 'bookingpress_integration_log_entry', 'google_calendar_debug_logs', 'Removing Google Calendar Event', 'Google Calendar Integration', 'Removing Google Calendar Event', $debug_log_data, $bookingpress_debug_integration_log_id );
                                }
                            }  

                        } else {

                            $bpa_other_bookings = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id != %d AND bookingpress_google_calendar_event_id = %s AND bookingpress_appointment_status != %d AND bookingpress_appointment_status != %d", $appointment_id, $bookingpress_event_id, 3, 4 ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally.

                            if($bpa_other_bookings > 0) {                                
                                $this->bookingpress_assign_appointment_to_staff_member( $appointment_id );

                                $bookingpress_event_title = $this->bookingpress_google_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['google_calendar_event_title'], $last_appointment_data );
                                $bookingpress_event_description = $this->bookingpress_google_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['google_calendar_event_description'], $last_appointment_data );
                                $bookingpress_event_location = $this->bookingpress_retrieve_location_field_data( $bookingpress_addon_popup_field_form['google_calendar_event_location'], $appointment_data );
                                try{

                                    $bookingpress_gevent_data = $bookingpress_gcalendar_service->events->get( $calendarId, $bookingpress_event_id );
                                    $bookingpress_new_start_time = new Google_Service_Calendar_Event();
                                    $bookingpress_gevent_data->setSummary( $bookingpress_event_title );
                                    $bookingpress_gevent_data->setDescription( $bookingpress_event_description);
                                    $bookingpress_gevent_data->setLocation( $bookingpress_event_location );
                                    $bookingpress_gcalendar_service->events->update( $calendarId, $bookingpress_event_id, $bookingpress_gevent_data );

                                } catch( Exception $e ){
                                    $debug_log_data = array(
                                        'google_calendar_exception_message' => $e->get_message(),
                                        'google_calendar_exception_object' => $e,
                                        'google_calendar_log_placement' => 'failed update google calendar event',
                                        'google_calendar_sent_data' => array(
                                            'calendar_id' => $calendarId,
                                            'event_object' => $bookingpress_gcalendar_service,
                                            'appointment_data' => $appointment_data
                                        ),
                                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                                    );
                                    do_action( 'bookingpress_integration_log_entry', 'google_calendar_debug_logs', 'Update Appointment Google Calendar Event', 'Google Calendar Integration', 'Update Google Calendar Event Summary and description', $debug_log_data, $bookingpress_debug_integration_log_id );
                                }

                            } else {

                                $already_booked_appointment = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bpa.bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} bpa LEFT JOIN {$tbl_bookingpress_staffmembers_services} bps ON bpa.bookingpress_staff_member_id=bps.bookingpress_staffmember_id WHERE bpa.bookingpress_service_id = %d AND bpa.bookingpress_appointment_status != %d AND bpa.bookingpress_appointment_status != %d AND bpa.bookingpress_staff_member_id = %d AND bpa.bookingpress_appointment_date = %s AND bpa.bookingpress_appointment_time = %s AND bpa.bookingpress_appointment_booking_id != %d AND bps.bookingpress_service_capacity > %d", $service_id, 3, 4, $staff_member_id, $bookingpress_appointment_date, $bookingpress_start_time, $appointment_id, 1)); // phpcs:ignore

                                if($already_booked_appointment > 0) {

                                    $this->bookingpress_assign_appointment_to_staff_member( $appointment_id );                                    
                                    $is_exist_event = $this->bookignpress_is_google_event_exist($calendarId,$bookingpress_event_id,$staff_member_id);

                                    if($is_exist_event == 0) {
                                        return;
                                    }

                                    try{    

                                        $response = $bookingpress_gcalendar_service->events->delete( $calendarId, $bookingpress_event_id );    

                                    } catch( Exception $e ){
    
                                        $debug_log_data = array(
                                            'google_calendar_exception_message' => $e->getMessage(),
                                            'google_calendar_exception_object' => $e,
                                            'google_calendar_log_placement' => 'failed removing google calendar event',
                                            'google_calendar_sent_data' => array(
                                                'calendar_id' => $calendarId,
                                                'event_object' => $bookingpress_event_id,
                                                'appointment_data' => $appointment_data
                                            ),
                                            'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                                        );
                                        do_action( 'bookingpress_integration_log_entry', 'google_calendar_debug_logs', 'Removing Google Calendar Event', 'Google Calendar Integration', 'Removing Google Calendar Event', $debug_log_data, $bookingpress_debug_integration_log_id );
                                    }
                                } else {
                                    /* update the event with the time */

                                    $bookingpress_event_title = $this->bookingpress_google_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['google_calendar_event_title'], $appointment_data );        
                                    $bookingpress_event_description = $this->bookingpress_google_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['google_calendar_event_description'], $appointment_data );
                                    $bookingpress_event_location = $this->bookingpress_retrieve_location_field_data( $bookingpress_addon_popup_field_form['google_calendar_event_location'], $appointment_data );
                                    try{

                                        $bookingpress_gevent_data = $bookingpress_gcalendar_service->events->get( $calendarId, $bookingpress_event_id );
                                        $bookingpress_new_start_time = new Google_Service_Calendar_Event();
                                        $bookingpress_gevent_data->setSummary( $bookingpress_event_title );
                                        $bookingpress_gevent_data->setDescription( $bookingpress_event_description);
                                        $bookingpress_gevent_data->setLocation( $bookingpress_event_location);
                                        
                                        $bookingpress_new_start_time = new Google_Service_Calendar_EventDateTime();
                                        $bookingpress_new_start_time->setDateTime( $bookingpress_start_date_time );
                                        $bookingpress_new_start_time->setTimeZone( $user_timezone );
                                        $bookingpress_gevent_data->setStart( $bookingpress_new_start_time );

                                        $bookingpress_new_end_time = new Google_Service_Calendar_EventDateTime();
                                        $bookingpress_new_end_time->setDateTime( $bookingpress_end_date_time );
                                        $bookingpress_new_end_time->setTimeZone( $user_timezone );
                                        $bookingpress_gevent_data->setEnd( $bookingpress_new_end_time );
                                        
                                        $bookingpress_gcalendar_service->events->update( $calendarId, $bookingpress_event_id, $bookingpress_gevent_data );
    
                                    } catch( Exception $e ){
                                        $debug_log_data = array(
                                            'google_calendar_exception_message' => $e->get_message(),
                                            'google_calendar_exception_object' => $e,
                                            'google_calendar_log_placement' => 'failed update google calendar event',
                                            'google_calendar_sent_data' => array(
                                                'calendar_id' => $calendarId,
                                                'event_object' => $bookingpress_gcalendar_service,
                                                'appointment_data' => $appointment_data
                                            ),
                                            'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                                        );
                                        do_action( 'bookingpress_integration_log_entry', 'google_calendar_debug_logs', 'Update Appointment Google Calendar Event', 'Google Calendar Integration', 'Update Google Calendar Event Summary and description', $debug_log_data, $bookingpress_debug_integration_log_id );
                                    }

                                }    

                            }

                        }
                    } else {

                        if($appointment_status == '3' || $appointment_status == '4' ) {

                            $bpa_other_bookings = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id != %d AND bookingpress_google_calendar_event_id = %s AND bookingpress_appointment_status != %d AND bookingpress_appointment_status != %d", $appointment_id, $bookingpress_event_id, 3, 4 ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally.

                            $wpdb->update(
                                $tbl_bookingpress_appointment_bookings,
                                array(
                                    'bookingpress_google_calendar_event_id' => '',
                                    'bookingpress_google_calendar_event_link' => '',
                                ),
                                array(
                                    'bookingpress_appointment_booking_id' => $appointment_id
                                )
                            );

                            if($bpa_other_bookings > 0) {
                                
                                $bookingpress_event_title = $this->bookingpress_google_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['google_calendar_event_title'], $appointment_data );
                                $bookingpress_event_description = $this->bookingpress_google_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['google_calendar_event_description'], $appointment_data );
                                $bookingpress_event_location = $this->bookingpress_retrieve_location_field_data( $bookingpress_addon_popup_field_form['google_calendar_event_location'], $appointment_data );
                                try{

                                    $bookingpress_gevent_data = $bookingpress_gcalendar_service->events->get( $calendarId, $bookingpress_event_id );
                                    $bookingpress_new_start_time = new Google_Service_Calendar_Event();
                                    $bookingpress_gevent_data->setSummary( $bookingpress_event_title );
                                    $bookingpress_gevent_data->setDescription( $bookingpress_event_description);
                                    $bookingpress_gevent_data->setLocation( $bookingpress_event_location);
                                    $bookingpress_gcalendar_service->events->update( $calendarId, $bookingpress_event_id, $bookingpress_gevent_data );

                                } catch( Exception $e ) {

                                    $debug_log_data = array(
                                        'google_calendar_exception_message' => $e->get_message(),
                                        'google_calendar_exception_object' => $e,
                                        'google_calendar_log_placement' => 'failed update google calendar event',
                                        'google_calendar_sent_data' => array(
                                            'calendar_id' => $calendarId,
                                            'event_object' => $bookingpress_gcalendar_service,
                                            'appointment_data' => $appointment_data
                                        ),
                                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                                    );
                                    do_action( 'bookingpress_integration_log_entry', 'google_calendar_debug_logs', 'Update Appointment Google Calendar Event', 'Google Calendar Integration', 'Update Google Calendar Event Summary and description', $debug_log_data, $bookingpress_debug_integration_log_id );                                    
                                }                                
                            } else {

                                $is_exist_event = $this->bookignpress_is_google_event_exist($calendarId,$bookingpress_event_id,$staff_member_id);

                                if($is_exist_event == 0) {
                                    return;
                                }

                                try{    
                                    $response = $bookingpress_gcalendar_service->events->delete( $calendarId, $bookingpress_event_id );    
                                } catch( Exception $e ){

                                    $debug_log_data = array(
                                        'google_calendar_exception_message' => $e->getMessage(),
                                        'google_calendar_exception_object' => $e,
                                        'google_calendar_log_placement' => 'failed removing google calendar event',
                                        'google_calendar_sent_data' => array(
                                            'calendar_id' => $calendarId,
                                            'event_object' => $bookingpress_event_id,
                                            'appointment_data' => $appointment_data
                                        ),
                                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                                    );
                                    do_action( 'bookingpress_integration_log_entry', 'google_calendar_debug_logs', 'Removing Google Calendar Event', 'Google Calendar Integration', 'Removing Google Calendar Event', $debug_log_data, $bookingpress_debug_integration_log_id );
                                }
                            }

                        } else {       
                            
                            $bookingpress_event_title = $this->bookingpress_google_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['google_calendar_event_title'], $appointment_data );
                            $bookingpress_event_description = $this->bookingpress_google_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['google_calendar_event_description'], $appointment_data );
                            $bookingpress_event_location = $this->bookingpress_retrieve_location_field_data( $bookingpress_addon_popup_field_form['google_calendar_event_location'], $appointment_data );
                            try{
                                $bookingpress_gevent_data = $bookingpress_gcalendar_service->events->get( $calendarId, $bookingpress_event_id );
                                $bookingpress_new_start_time = new Google_Service_Calendar_Event();
                                $bookingpress_gevent_data->setSummary( $bookingpress_event_title );
                                $bookingpress_gevent_data->setDescription( $bookingpress_event_description );
                                $bookingpress_gevent_data->setLocation( $bookingpress_event_location );
                                $bookingpress_gcalendar_service->events->update( $calendarId, $bookingpress_event_id, $bookingpress_gevent_data );

                            } catch( Exception $e ) {

                                $debug_log_data = array(
                                    'google_calendar_exception_message' => $e->get_message(),
                                    'google_calendar_exception_object' => $e,
                                    'google_calendar_log_placement' => 'failed update google calendar event',
                                    'google_calendar_sent_data' => array(
                                        'calendar_id' => $calendarId,
                                        'event_object' => $bookingpress_gcalendar_service,
                                        'appointment_data' => $appointment_data
                                    ),
                                    'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                                );
                                do_action( 'bookingpress_integration_log_entry', 'google_calendar_debug_logs', 'Update Appointment Google Calendar Event', 'Google Calendar Integration', 'Update Google Calendar Event Summary and description', $debug_log_data, $bookingpress_debug_integration_log_id );                                    
                            }
                        } 
                    }
                }
            }

        }

        function bookingpress_hide_notice_after_activate_module(){
            ?>
            if( 'bookingpress_staffmember_module' == activate_addon_key ){
                vm.bookingpress_addon_popup_field_form.is_staffmember_activated = 1;
            }
            <?php
        }

        function bookingpress_show_notice_after_deactivate_module(){
            ?>
            if( 'bookingpress_staffmember_module' == deactivate_addon_key ){
                vm.bookingpress_addon_popup_field_form.is_staffmember_activated = 0;
            }
            <?php
        }

        function bookingpress_modify_service_time_with_calendar_events( $total_booked_appointments, $selected_date, $service_timings, $service_id ){

            global $wpdb, $bookingpress_pro_appointment_bookings, $tbl_bookingpress_default_workhours, $bookingpress_pro_staff_members, $tbl_bookingpress_appointment_bookings;
            
            $is_staffmember_activated = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();
            if( !$is_staffmember_activated ){
                return $total_booked_appointments;
            }

            if( empty( $service_timings ) ){
                return $total_booked_appointments;
            }

            $staff_member_id = '';
            if( !empty( $_POST['selected_staffmember']) ){ // phpcs:ignore
                $staff_member_id = intval( $_POST['selected_staffmember'] ); // phpcs:ignore
            }

            if( empty( $staff_member_id ) && !empty( $_POST['bookingpress_selected_staffmember']['selected_staff_member_id'] ) ){ // phpcs:ignore
                $staff_member_id = intval( $_POST['bookingpress_selected_staffmember']['selected_staff_member_id'] ); // phpcs:ignore
            }

            if( empty( $staff_member_id ) && !empty( $_POST['appointment_data_obj']['bookingpress_selected_staff_member_details']['selected_staff_member_id'] ) ){ // phpcs:ignore
                $staff_member_id = intval($_POST['appointment_data_obj']['bookingpress_selected_staff_member_details']['selected_staff_member_id']); // phpcs:ignore
            }

            if( empty( $staff_member_id) ){
                return $total_booked_appointments;
            }
            
            $is_google_calendar_enabled = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_staff_gcalendar_enable' );
			if( "true" != $is_google_calendar_enabled ){
                return $total_booked_appointments;
			}
            
            $is_invalid_token = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_display_invalid_token_notice');
            if( true == $is_invalid_token ){
                return $total_booked_appointments;
            }
            
            $calendarId = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_selected_gcalendar' );
            if( empty( $calendarId ) ){
                return $total_booked_appointments;
            }

            $bpa_unique_id = $_POST['appointment_data_obj']['bookingpress_uniq_id'];

            $staff_gcalendar_events = $this->bookingpress_retrieve_google_calendar_events( $staff_member_id, $calendarId, $selected_date, true );
            
            if( empty( $staff_gcalendar_events ) ){
                return $total_booked_appointments;
            }

            $bookingpress_addon_popup_field_form = $this->bookingpress_get_google_calendar_credentials();
            $maxResults = $bookingpress_addon_popup_field_form['google_calendar_max_event'];

            $staffmember_capacity = get_transient( 'bkp_staff_capacity_for_service_' . $staff_member_id .'_' . $service_id .'_' . $bpa_unique_id  );

            if( empty( $staff_capacity ) ){
                global $tbl_bookingpress_staffmembers_services;
                $staffmember_capacity = $wpdb->get_var( $wpdb->prepare( "SELECT bookingpress_service_capacity FROM `{$tbl_bookingpress_staffmembers_services}` WHERE bookingpress_staffmember_id = %d AND bookingpress_service_id = %d", $staff_member_id, $service_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_services is table name.

                set_transient( 'bkp_staff_capacity_for_service_' . $staff_member_id .'_' . $service_id .'_' . $bpa_unique_id, $staffmember_capacity, HOUR_IN_SECONDS );
            }

            $event_counter = 0;
            foreach( $staff_gcalendar_events as $event_id => $event_times ){

                if( !empty( $maxResults ) && $event_counter >= $maxResults ){
                    break;
                }

                if( !empty( $event_id ) ){
                    /** check if Event is Registered With BookingPress */                    
                    $db_service_id = $wpdb->get_var( $wpdb->prepare( "SELECT bookingpress_service_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_google_calendar_event_id = %s", $event_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
                    if( !empty( $db_service_id ) && $db_service_id == $service_id ){
                        continue;
                    }
                }

                $event_start_datetime = $event_times['start_date'];
                $event_end_datetime = $event_times['end_date'];

                $evt_start_date = date('Y-m-d', strtotime( $event_start_datetime ) );
                $evt_end_date = date('Y-m-d', strtotime( $event_end_datetime) );

                if( $evt_start_date == $selected_date && $evt_end_date == $selected_date ){   
                    $total_booked_appointments[] = array(
                        'bookingpress_appointment_time' => date('H:i:s', strtotime($event_start_datetime) ),
                        'bookingpress_appointment_end_time' => date('H:i:s', strtotime( $event_end_datetime ) ),
                        'bookingpress_selected_extra_members' => ( $staffmember_capacity ),
                        'bookingpress_gc_blocked' => true,
                    );
                    
                } else if( $evt_start_date == $selected_date && $evt_end_date != $selected_date ){

                    $total_booked_appointments[] = array(
                        'bookingpress_appointment_time' => date('H:i:s', strtotime( $event_start_datetime ) ),
                        'bookingpress_appointment_end_time' => '23:59:59',
                        'bookingpress_selected_extra_members' => ( $staffmember_capacity ),
                        'bookingpress_gc_blocked' => true,
                    );
                
                } else if( $evt_start_date != $selected_date && $evt_end_date == $selected_date ){
                    $start_time_check = $evt_end_date .' 00:00:00';
                    if( !( $start_time_check == $event_end_datetime ) ){
                        $total_booked_appointments[] = array(
                            'bookingpress_appointment_time' => '00:00:00',
                            'bookingpress_appointment_end_time' => date('H:i:s', strtotime( $event_end_datetime ) ),
                            'bookingpress_selected_extra_members' => ( $staffmember_capacity ),
                            'bookingpress_gc_blocked' => true,
                        );  
                    }
                } else if( $evt_start_date != $selected_date && $evt_end_date != $selected_date ){

                    if( $selected_date > $evt_start_date && $selected_date < $evt_end_date ){
                        $total_booked_appointments[] = array(
                            'bookingpress_appointment_time' => date('H:i:s', strtotime( $event_start_datetime ) ),
                            'bookingpress_appointment_end_time' => date('H:i:s', strtotime( $event_end_datetime ) ),
                            'bookingpress_selected_extra_members' => ( $staffmember_capacity ),
                            'bookingpress_gc_blocked' => true,
                        );  
                    }

                }

                $event_counter++;
            }

            return $total_booked_appointments;
        }

        function bookingpress_retrieve_google_calendar_events( $staff_member_id, $calendarId, $selected_date, $check_for_time = false ){

            global $BookingPress, $bookingpress_pro_staff_members;

            $return_data = array();

            if( empty( $staff_member_id ) ){
                return $return_data;
            }

            $calendar_event_data = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_staff_gc_events' );
            $calendar_event_fetch_date = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_staff_gc_event_fetch_expiration_time' );

            $retrieve_gc_data = true;
            if( true == $check_for_time ){
                $current_datetime = current_time('timestamp');
                $fetched_datetime = !empty( $calendar_event_fetch_date ) ? $calendar_event_fetch_date : current_time('timestamp');

                if( $current_datetime < $fetched_datetime ){
                    $retrieve_gc_data = false;
                }
            }
            

            $calendar_event_data = json_decode( $calendar_event_data, true );

            if( empty( $calendar_event_data[ $calendarId ] ) && $retrieve_gc_data ){
                $this->bookingpress_gc_synchronize_staff_calendar( $staff_member_id );
                $calendar_event_data = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_staff_gc_events' );
                $calendar_event_data = json_decode( $calendar_event_data, true );
            }


            $staff_calendar_data = $calendar_event_data[ $calendarId ];
            
            if( empty( $staff_calendar_data ) ){
                return $return_data;
            }

            if( empty( $selected_date ) ){                
                $return_data = $staff_calendar_data;
            } else {
                foreach( $staff_calendar_data as $event_id => $event_times ){
                    $event_start_date = date('Y-m-d', strtotime( $event_times['start_date'] ) );
                    $event_end_date = date('Y-m-d', strtotime( $event_times['end_date'] ) );

                    if( $event_start_date == $event_end_date && $selected_date == $event_start_date ){
                        $return_data[ $event_id ] = $event_times;
                    } else {

                        if( $event_start_date == $event_end_date ){
                            continue;
                        }

                        $start_date = new DateTime( $event_start_date );
                        $end_date = new DateTime( date('Y-m-d', strtotime($event_end_date . '+1 day') ) );
                        
                        $interval = DateInterval::createFromDateString('1 day');
                        $period = new DatePeriod( $start_date, $interval, $end_date );

                        foreach( $period as $dt ){
                            $dt_formated = $dt->format( 'Y-m-d' );

                            if( $selected_date == $dt_formated ){
                                $return_data[ $event_id ] = $event_times;
                                break;
                            }
                        }
                    }
                }
            }

            return $return_data;
        }

        function bookingpress_add_google_calendar_integration_logs( $bookingpress_integration_debug_logs_arr ){

            $bookingpress_integration_debug_logs_arr[] = array(
                'integration_name' => __('Google Calendar Debug Logs', 'bookingpress-google-calendar'),
                'integration_key' => 'google_calendar_debug_logs'
            );

            return $bookingpress_integration_debug_logs_arr;
        }  
    }
    

    global $bookingpress_google_calendar;
	$bookingpress_google_calendar = new bookingpress_google_calendar;
}