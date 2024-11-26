<?php
if ( !class_exists( 'bookingpress_outlook_calendar' ) && class_exists( 'BookingPress_Core' ) ) {

    class bookingpress_outlook_calendar Extends BookingPress_Core {

        var $bookingpress_global_data;
        function __construct(){        

            add_action( 'admin_notices', array( $this, 'bookingpress_outlook_calendar_admin_notices') );

            if( !function_exists('is_plugin_active') ){
                include_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            if(is_plugin_active('bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php')) {
                
                global $bookingpress_global_options;
                $this->bookingpress_global_data = $bookingpress_global_options->bookingpress_global_options();

                add_action('bookingpress_add_integration_settings_section', array($this, 'bookingpress_add_integration_settings_section_func'));

                add_action( 'bookingpress_add_setting_dynamic_vue_methods', array( $this, 'bookingpress_outlook_calendar_vue_methods' ) );

                add_action( 'bookingpress_staff_member_view', array( $this, 'bookingpress_staff_member_outlook_calendar_integration' ), 11 );

                add_filter( 'bookingpress_staff_member_vue_dynamic_data_fields', array( $this, 'bookingpress_staff_member_outlook_calendar_dynamic_field_data' ) );

                add_filter('bookingpress_modify_edit_profile_fields',array( $this, 'bookingpress_edit_profile_outlook_calendar_dynamic_field_data' ) );

                add_filter( 'bookingpress_staff_member_external_details', array( $this, 'bookingpress_generate_staff_member_outlook_auth_link' ) );

                add_action( 'bookingpress_staff_members_dynamic_vue_methods', array( $this, 'bookingpress_staff_member_outlook_oauth_methods' ) );

                add_action( 'bookingpress_myprofile_dynamic_add_vue_methods_func', array( $this, 'bookingpress_staff_member_outlook_oauth_methods' ) );

                add_filter( 'bookingpress_staff_members_save_external_details', array( $this, 'bookingpress_staff_member_save_outlook_data') );

                add_action( 'bookingpress_save_staff_member', array( $this, 'bookingpress_save_staff_member_func' ) );

                add_action( 'bookingpress_staff_member_edit_details_response', array( $this, 'bookingpress_assign_ocalendar_auth_link' ) );

                add_action( 'wp', array( $this, 'bookingpress_staff_member_authentication_callback' ) );

                add_action( 'bookingpress_after_add_appointment_from_backend', array( $this, 'bookingpress_assign_appointment_to_staff_member_from_admin' ), 10, 3 );

                /* changes start */

                add_action( 'bookingpress_after_book_appointment', array( $this, 'bookingpress_schedule_googleoutlook_event'), 10, 3);
                add_action( 'bookingpress_schedule_staffmember_google_outlook_event', array( $this, 'bookingpress_assign_appointment_to_staff_member'), 10, 3 );
                add_action( 'bookingpress_force_send_scheduled_notifications', array( $this, 'bookingpress_force_assign_appointment_to_staff') );

                /* changes end */

                add_action( 'bookingpress_after_add_group_appointment', array( $this, 'bookingpress_schedule_oc_group_appointments') );

                add_action( 'bookingpress_modify_booked_appointment_data', array( $this, 'bookingpress_modify_service_time_with_calendar_events' ), 10, 4 );

                add_action( 'bookingpress_after_cancel_appointment', array( $this, 'bookingpress_update_outlook_calendar_event'), 10 );                

                add_action( 'bookingpress_after_change_appointment_status', array( $this, 'bookingpress_update_outlook_calendar_event'), 10, 2 );

                add_action('bookingpress_before_delete_appointment',array($this,'bookingpress_before_delete_appointment_func'),11);                

                add_action( 'bookingpress_after_update_appointment', array( $this, 'bookingpress_calendar_event_reschedule') );

                add_action( 'bookingpress_after_rescheduled_appointment', array( $this, 'bookingpress_calendar_event_reschedule') );

                add_action( 'bookingpress_after_module_activate', array( $this, 'bookingpress_hide_notice_after_activate_module') );

                add_action( 'bookingpress_after_deactivating_module', array( $this, 'bookingpress_show_notice_after_deactivate_module') );

                add_filter( 'bookingpress_add_integration_debug_logs', array( $this, 'bookingpress_add_outlook_calendar_integration_logs' ) );

                add_filter( 'bookingpress_add_setting_dynamic_data_fields', array( $this, 'bookingpress_add_outlook_calendar_dynamic_data_fields' ),11 );

                add_filter('bookingpress_available_integration_addon_list',array($this,'bookingpress_available_integration_addon_list_func'));

                add_action( 'bookingpress_load_integration_settings_data', array( $this, 'bookingpress_load_integration_settings_data_func' ) );

                add_filter('bookingpress_addon_list_data_filter',array($this,'bookingpress_addon_list_data_filter_func'));

                add_action( 'wp_ajax_bookingpress_signout_outlook_calendar', array( $this, 'bookingpress_signout_outlook_calendar') );

                add_filter( 'bookingpress_modify_save_setting_data', array( $this, 'bookingpress_save_outlook_calendar_settings' ), 10, 2 );

                add_filter( 'bookingpress_modify_disable_dates_with_staffmember', array( $this, 'bookingpress_modify_disable_dates_outlook_calendar' ), 11, 3 );

                add_action('bookingpress_modify_readmore_link', array($this, 'bookingpress_modify_readmore_link_func'), 13);

                add_filter('bookingpress_modify_capability_data', array($this, 'bookingpress_modify_capability_data_func'), 11, 1);

                add_action( 'bookingpress_schedule_staffmember_oc_event', array( $this, 'bookingpress_assign_appointment_to_staff_member_with_cron_oc_cal'), 10, 3 );
                /*For syncing outlook cal events*/
                add_action( 'bookingpress_outlook_calendar_after_save_staff_settings', array( $this, 'bookingpress_oc_synchronize_staff_calendar'));

                add_action( 'wp', array( $this, 'bookingpress_sycnrhonize_outlook_events') );
                //add action for need help link
                add_action( 'bpa_add_extra_tab_outside_func', array( $this,'bpa_add_extra_tab_outside_func_arr'));

                /* check staff member unavailable dateandtime data */
                add_filter('bookingpress_check_unavailable_time_outside', array($this, 'bookingpress_check_outside_unavailable_time_func'), 10,3);

                add_filter( 'bookingpress_disabled_features_with_cron', array( $this, 'bookingpress_add_outlook_in_disabled_cron_func') );

                add_filter( 'wp_ajax_bookingpress_refresh_outlook_calendar_list', array( $this, 'bookingpress_refresh_outlook_calendar_list_func') );

                /*Hook for front end add verion variable*/
                add_filter('bookingpress_frontend_apointment_form_add_dynamic_data',array($this,'bookingpress_frontend_apointment_form_add_dynamic_data_func_oc'));

                add_action( 'admin_enqueue_scripts', array( $this, 'bookingpress_admin_enqueue_scripts_oc' ), 11 );

                if( is_plugin_active( 'bookingpress-waiting-list/bookingpress-waiting-list.php' ) ){
                    add_filter( 'bookingpress_check_available_timings_with_staffmember', array( $this, 'bookingpress_set_flag_for_waiting_list'), 10, 4 );
					add_filter( 'bookingpress_modify_single_time_slot_data', array( $this, 'bookingpress_prevent_waiting_list_check_for_timeslot'), 12, 3 );	
				}
                
			}
	        add_action( 'admin_init', array( $this, 'bookingpress_upgrade_outlook_calendar_data' ) );
	    
            add_action('activated_plugin',array($this,'bookingpress_is_outlook_calendar_addon_activated'),11,2);
		}

        function bookingpress_prevent_waiting_list_check_for_timeslot( $service_time_arr, $selected_service_id, $selected_date ){
			
			if( !empty( $service_time_arr['is_waiting_slot'] ) && $service_time_arr['is_waiting_slot'] == true && !empty( $service_time_arr['skip_waiting_list_with_oc'] ) && true == $service_time_arr['skip_waiting_list_with_oc'] ) {
				$service_time_arr['is_waiting_slot'] = false;
			}
			
			return $service_time_arr;
		}

        function bookingpress_set_flag_for_waiting_list( $service_timings, $selected_service_id, $selected_date, $total_booked_appiontments ){

            if( !empty( $total_booked_appiontments ) ){
				foreach( $total_booked_appiontments as $booked_appointment_data ){
					
					if( !empty( $booked_appointment_data['bookingpress_oc_blocked'] ) && 1 == $booked_appointment_data['bookingpress_oc_blocked'] ){
						$booked_appointment_start_time = $booked_appointment_data['bookingpress_appointment_time'];
						$booked_appointment_end_time = $booked_appointment_data['bookingpress_appointment_end_time'];

						if( '00:00:00' == $booked_appointment_end_time ){
							$booked_appointment_end_time = '24:00:00';
						}
						
						foreach( $service_timings as $sk => $time_slot_data ){
							$current_time_start = $time_slot_data['store_start_time'].':00';
							$current_time_end = $time_slot_data['store_end_time'].':00';
							if( ( $booked_appointment_start_time >= $current_time_start && $booked_appointment_end_time <= $current_time_end ) || ( $booked_appointment_start_time < $current_time_end && $booked_appointment_end_time > $current_time_start) ){
								$service_timings[ $sk ]['skip_waiting_list_with_oc'] = true;
							}
						}
					}
				}
			}

            return $service_timings;
        }

        function bookingpress_admin_enqueue_scripts_oc(){
            if( !empty( $_GET['page'] ) && ('bookingpress_staff_members' == $_GET['page'] || 'bookingpress_myprofile' == $_GET['page'])){
                wp_add_inline_style( 'bookingpress_pro_admin_css', '.outlook_calendar_staff_member_module_refresh.el-tooltip__popper { z-index: 9997 !important;} .bookingpress-staff-oc-refresh-col{ padding: 0 !important; @keyframes rotate { from { transform: rotate(0deg); } to { transform: rotate(360deg);} } .oc-refresh-rotate { animation: rotate 2s linear infinite; animation-direction: reverse; } }  ' );
            } 
        }

        function bookingpress_frontend_apointment_form_add_dynamic_data_func_oc($bookingpress_front_vue_data_fields) {
            global $bookingpress_outlook_calendar_version;
            $bookingpress_front_vue_data_fields['bookingpress_outlook_calendar_version'] = $bookingpress_outlook_calendar_version;
            return $bookingpress_front_vue_data_fields;
        }

        function bookingpress_refresh_outlook_calendar_list_func()
        {   
            global $wpdb, $tbl_bookingpress_staffmembers, $tbl_bookingpress_staffmembers_meta, $bookingpress_pro_staff_members, $bookingpress_debug_integration_log_id;
            $response              = array();
            $response['variant'] = 'error';
            $response['title']   = esc_html__( 'Error', 'bookingpress-outlook-calendar' );
            $response['msg']     = esc_html__( 'Calendar List Not Found', 'bookingpress-outlook-calendar' );

            $bpa_check_authorization = $this->bpa_check_authentication( 'outlook_calendar_refresh_list', true, 'bpa_wp_nonce' );
			
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-outlook-calendar');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-outlook-calendar');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }
            $staffmember_id = !empty( $_POST['staffmember'] ) ? intval( $_POST['staffmember'] ) : '';
            $staff_token_data = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staffmember_id, 'bookingpress_ocalendar_oauthdata');
            if( empty( $staff_token_data ) ){
                echo wp_json_encode( $response );
                die;
            }
            $staff_token_data = json_decode( $staff_token_data );
            $auth_access_token = $staff_token_data->access_token;
            if( current_time( 'timestamp' ) > ( $staff_token_data->created + $staff_token_data->expires_in ) ){
                $auth_access_token = $this->bookingpress_refresh_access_token( $staff_token_data, $staffmember_id );
            }
            $calendarId = !empty( $_POST['selected_calendar'] ) ? sanitize_text_field( $_POST['selected_calendar'] ) : '';
            if(!empty( $staff_token_data ) ){
                $list_records = 'https://graph.microsoft.com/v1.0/me/calendars';
                $args = array(
                    'timeout' => 5000,
                    'headers' => array(
                        'Authorization' => 'Bearer ' . $auth_access_token,
                        'Content-Type'  => 'application/json'
                    )
                );
                $resp_calendars = wp_remote_get( $list_records, $args );
                if( is_wp_error( $resp_calendars ) ){
                    $debug_log_data = array(
                        'outlook_calendar_message' => $resp_calendars->get_error_message(),
                        'outlook_calendar_log_placement' => 'failed retrieving outlook calendar list',
                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                    );
                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'Error while refreshinging calendar list from the outlook', $debug_log_data, $bookingpress_debug_integration_log_id );
                }
                
                if( 200 != $resp_calendars['response']['code'] ){
                    $debug_log_data = array(
                        'outlook_calendar_message' => $resp_calendars,
                        'outlook_calendar_log_placement' => 'outlook calendar retrieving list status not 200 OK',
                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                    );
                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'Status is not 200 OK while refreshinging calendar list from the outlook', $debug_log_data, $bookingpress_debug_integration_log_id );

                } else {
                    $debug_log_data = array(
                        'outlook_calendar_message' => $resp_calendars,
                        'outlook_calendar_log_placement' => 'outlook calendar retrieving list status 200 OK',
                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                    );
                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'Status is 200 OK while refreshing calendar list from the outlook', $debug_log_data, $bookingpress_debug_integration_log_id );                    
                }               
                $calendar_lists = json_decode( $resp_calendars['body'], true );
                $outlook_calendars = array();
                if( empty( $calendar_lists ) ){
                    $debug_log_data = array(
                        'outlook_calendar_message' => $resp_calendars,
                        'outlook_calendar_log_placement' => 'outlook calendar empty list',
                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                    );
                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'Outlook calendar List is empty - Refresh List ', $debug_log_data, $bookingpress_debug_integration_log_id );
                }
                else {
                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'Outlook calendar List - Refresh List ', $calendar_lists, $bookingpress_debug_integration_log_id );
                    if(isset($calendar_lists['value'])) {
                        $is_calendar_exists = false;
                        foreach( $calendar_lists['value'] as $outlook_calendar ){
                            $outlook_calendars[] = array(
                                'value' => $outlook_calendar['id'],
                                'name' => $outlook_calendar['name']
                            );
                            if( false == $is_calendar_exists ){
                                $is_calendar_exists = ( !empty( $calendarId ) &&  $outlook_calendar['id'] == $calendarId );
                            }
                        }
                        $response['is_oc_reset_list'] = ( false == $is_calendar_exists );
                        $response['variant'] = 'success';
                        $response['title']   = esc_html__( 'Success', 'bookingpress-outlook-calendar' );
                        $response['msg']     = esc_html__( 'List has been refreshed successfully.', 'bookingpress-outlook-calendar' );
                        $response['outlook_calendars'] = $outlook_calendars;

                        if( !empty( $outlook_calendars ) ){                
                            $bookingpress_pro_staff_members->update_bookingpress_staffmembersmeta( $staffmember_id, 'bookingpress_ocalendar_list', json_encode( $outlook_calendars ) );
                        }
                    }
                }
            }
            echo wp_json_encode( $response );
            die;
        }

        function bookingpress_add_outlook_in_disabled_cron_func( $disabled_feature_lists ){

            $disabled_feature_lists[] = esc_html__( 'Outlook Calendar', 'bookingpress-outlook-calendar' );

            return $disabled_feature_lists;
        }

        function bookingpress_check_outside_unavailable_time_func( $staff_unavailable_times, $selected_date, $available_staff_ids ){

            global $bookingpress_pro_staff_members, $tbl_bookingpress_appointment_bookings, $wpdb;

            if( !empty( $available_staff_ids ) ){
                foreach( $available_staff_ids as $bpa_staff_id ){

                    $bookingpress_enable_outlook_calendar = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bpa_staff_id, 'bookingpress_enable_outlook_calendar');
                    
                    if(empty($bookingpress_enable_outlook_calendar) || $bookingpress_enable_outlook_calendar == 'false' ) {
                        continue;
                    }

                    $calendarId = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bpa_staff_id, 'bookingpress_selected_ocalendar' );
                    if( empty( $calendarId ) ){
                        continue;
                    }
        
                    $staff_token_data = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bpa_staff_id, 'bookingpress_ocalendar_oauthdata');
                    if( empty( $staff_token_data ) ){
                        continue;
                    }

                    $staff_ocalendar_events = $this->bookingpress_retrieve_outlook_calendar_events( $bpa_staff_id, $calendarId, $selected_date, true );
                    if( empty( $staff_ocalendar_events ) ){
                        continue;
                    }
                    
                    foreach( $staff_ocalendar_events as $event_id => $event_times ){

                        if( !empty( $event_id ) ){
                            /** check if Event is Registered With BookingPress */                    
                            $db_service_id = $wpdb->get_var( $wpdb->prepare( "SELECT bookingpress_service_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_outlook_calendar_event_id = %s", $event_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
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
		
		function bpa_add_extra_tab_outside_func_arr(){ ?>

			var bpa_get_setting_tab = bpa_get_url_param.get('setting_tab');

		        if( bpa_get_page == 'bookingpress_settings'){

                if( selected_tab_name == 'integration_settings' && vm.bpa_integration_active_tab == 'outlook_calendar'){
                        vm.openNeedHelper("list_outlook_calendar_settings", "outlook_calendar_settings", "Outlook calendar settings");
                    vm.bpa_fab_floating_btn = 0;

                } else if ( null == selected_tab_name && 'integration_settings' == bpa_get_setting_page && 'outlook_calendar' == bpa_get_setting_tab ){
                    vm.openNeedHelper("list_outlook_calendar_settings", "outlook_calendar_settings", "Outlook calendar settings");
                    vm.bpa_fab_floating_btn = 0;
                }
            }

        <?php }
	function bookingpress_modify_capability_data_func($bpa_caps){
            $bpa_caps['bookingpress_staff_members'][] = 'outlook_calendar_signout';
            $bpa_caps['bookingpress_settings'][] = 'outlook_calendar_signout';
            $bpa_caps['bookingpress_myprofile'][] = 'outlook_calendar_signout';

            $bpa_caps['bookingpress_staff_members'][] = 'outlook_calendar_refresh_list';
            $bpa_caps['bookingpress_settings'][] = 'outlook_calendar_refresh_list';
            $bpa_caps['bookingpress_myprofile'][] = 'outlook_calendar_refresh_list';
            return $bpa_caps;
        }

        function bookingpress_modify_readmore_link_func(){
            ?>
                var selected_tab = sessionStorage.getItem("current_tabname");
                if(selected_tab == "integration_settings"){
                    if(vm.bpa_integration_active_tab == ""){
                        read_more_link = "https://www.bookingpressplugin.com/documents/outlook-calendar-integration/";
                    }
                    if(vm.bpa_integration_active_tab == "outlook_calendar"){
                        read_more_link = "https://www.bookingpressplugin.com/documents/outlook-calendar-integration/";
                    }
                }
            <?php
        }

        function bookingpress_save_outlook_calendar_settings( $bookingpress_save_settings_data, $posted_data ){

            global $BookingPress, $bookingpress_global_options, $wpdb, $tbl_bookingpress_settings;

            $bookingpress_global_options_data = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_allow_tag = json_decode($bookingpress_global_options_data['allowed_html'], true);

            $outlook_calendar_event_title = !empty( $bookingpress_save_settings_data['outlook_calendar_event_title'] ) ? wp_kses( $bookingpress_save_settings_data['outlook_calendar_event_title'], $bookingpress_allow_tag ) : '';

            if( !empty( $outlook_calendar_event_title ) ){
                $bookingpress_check_record_existance = $wpdb->get_var($wpdb->prepare("SELECT COUNT(setting_id) FROM `{$tbl_bookingpress_settings}` WHERE setting_name = %s AND setting_type = %s", 'outlook_calendar_event_title', 'outlook_calendar_setting'));

                if ( $bookingpress_check_record_existance > 0 ) {
                    $bookingpress_update_data = array(
                        'setting_value' => wp_kses($outlook_calendar_event_title, $bookingpress_allow_tag),
                        'setting_type'  => 'outlook_calendar_setting',
                        'updated_at'    => current_time('mysql'),
                    );

                    $bpa_update_where_condition = array(
                        'setting_name' => 'outlook_calendar_event_title',
                        'setting_type' => 'outlook_calendar_setting',
                    );

                    $bpa_update_affected_rows = $wpdb->update($tbl_bookingpress_settings, $bookingpress_update_data, $bpa_update_where_condition);
                    if ($bpa_update_affected_rows > 0 ) {
                        wp_cache_delete('outlook_calendar_event_title');
                        wp_cache_set('outlook_calendar_event_title', $outlook_calendar_event_title);
                    }
                } else {
                    $bookingpress_insert_data = array(
                        'setting_name'  => 'outlook_calendar_event_title',
                        'setting_value' => wp_kses($outlook_calendar_event_title, $bookingpress_allow_tag),
                        'setting_type'  => 'outlook_calendar_setting',
                        'updated_at'    => current_time('mysql'),
                    );

                    $bookingpress_inserted_id = $wpdb->insert($tbl_bookingpress_settings, $bookingpress_insert_data);
                    if ($bookingpress_inserted_id > 0 ) {
                        wp_cache_delete('outlook_calendar_event_title');
                        wp_cache_set('outlook_calendar_event_title', $outlook_calendar_event_title);
                    }
                }
                unset( $bookingpress_save_settings_data['outlook_calendar_event_title'] );
            }

            $outlook_calendar_event_description = !empty( $bookingpress_save_settings_data['outlook_calendar_event_description'] ) ? wp_kses( $bookingpress_save_settings_data['outlook_calendar_event_description'], $bookingpress_allow_tag ) : '';
            if( !empty( $outlook_calendar_event_description ) ){
                $bookingpress_check_record_existance = $wpdb->get_var($wpdb->prepare("SELECT COUNT(setting_id) FROM `{$tbl_bookingpress_settings}` WHERE setting_name = %s AND setting_type = %s", 'outlook_calendar_event_description', 'outlook_calendar_setting'));
                
                if ($bookingpress_check_record_existance > 0 ) {
                    $bookingpress_update_data = array(
                        'setting_value' => wp_kses($outlook_calendar_event_description, $bookingpress_allow_tag),
                        'setting_type'  => 'outlook_calendar_setting',
                        'updated_at'    => current_time('mysql'),
                    );

                    $bpa_update_where_condition = array(
                        'setting_name' => 'outlook_calendar_event_description',
                        'setting_type' => 'outlook_calendar_setting',
                    );

                    $bpa_update_affected_rows = $wpdb->update($tbl_bookingpress_settings, $bookingpress_update_data, $bpa_update_where_condition);
                    if ($bpa_update_affected_rows > 0 ) {
                        wp_cache_delete('outlook_calendar_event_description');
                        wp_cache_set('outlook_calendar_event_description', $outlook_calendar_event_description);
                    }
                } else {
                    $bookingpress_insert_data = array(
                        'setting_name'  => 'outlook_calendar_event_description',
                        'setting_value' => wp_kses($outlook_calendar_event_description, $bookingpress_allow_tag),
                        'setting_type'  => 'outlook_calendar_setting',
                        'updated_at'    => current_time('mysql'),
                    );

                    $bookingpress_inserted_id = $wpdb->insert($tbl_bookingpress_settings, $bookingpress_insert_data);
                    if ($bookingpress_inserted_id > 0 ) {
                        wp_cache_delete('outlook_calendar_event_description');
                        wp_cache_set('outlook_calendar_event_description', $outlook_calendar_event_description);
                    }
                }

                unset( $bookingpress_save_settings_data['outlook_calendar_event_description'] );
            }

            return $bookingpress_save_settings_data;
        }

        function bookingpress_upgrade_outlook_calendar_data(){
            global $BookingPress, $bookingpress_outlook_calendar_version;
            $bookingpress_db_ol_version = get_option( 'bookingpress_outlook_calendar_version' );

            if( version_compare( $bookingpress_db_ol_version, '2.2', '<' ) ){
                $bookingpress_load_ol_update_file = BOOKINGPRESS_OUTLOOK_CALENDAR_DIR . '/core/views/upgrade_latest_outlook_calendar_data.php';
                include $bookingpress_load_ol_update_file;
                $BookingPress->bookingpress_send_anonymous_data_cron();
            }
        }
        function bookingpress_signout_outlook_calendar(){
            $response              = array();
			$bpa_check_authorization = $this->bpa_check_authentication( 'outlook_calendar_signout', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-outlook-calendar');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-outlook-calendar');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $staffmember_id = !empty( $_POST['staffmember'] ) ? intval( $_POST['staffmember'] ) : '';

            global $wpdb, $tbl_bookingpress_staffmembers, $tbl_bookingpress_staffmembers_meta, $bookingpress_pro_staff_members, $bookingpress_debug_integration_log_id;

            $staff_ocal_auth_data = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staffmember_id, 'bookingpress_ocalendar_oauthdata' );
            $staff_ocal_auth_data = json_decode( $staff_ocal_auth_data, true );
			
            $staff_subscription_details = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staffmember_id, 'bookingpress_oc_subscription_details' );
			$staff_subscription_details = json_decode( $staff_subscription_details, true );
			if( !empty( $staff_subscription_details ) && !empty( $staff_ocal_auth_data ) ){
                $auth_access_token = $staff_ocal_auth_data['access_token'];
                $subscription_id = $staff_subscription_details['id'];
                $delete_subscription_url = "https://graph.microsoft.com/v1.0/subscriptions/$subscription_id";
				//echo $auth_access_token . ' ---<br/>' . $delete_subscription_url.' --- ';die;
                $del_sub_response = wp_remote_request($delete_subscription_url, array(
                    'method' => 'DELETE',
                    'headers' => array(
                        'Authorization' => 'Bearer ' . $auth_access_token,
                    ),
                ));
                $debug_log_data = array(
                    'subscription_id' => $subscription_id,
                    'del_sub_response' => $del_sub_response,
                    'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false ),
                    'staffmember_id' => $staffmember_id,
                );
                if (!is_wp_error($del_sub_response) && wp_remote_retrieve_response_code($del_sub_response) === 204) {
                    // Subscription deleted successfully
                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', 'Outlook Calendar Subscription Deletion', 'Outlook Calendar subscription deleted', 'Oulook calendar subscription deleted successfully.', $debug_log_data, $bookingpress_debug_integration_log_id );
                } else {
                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', 'Outlook Calendar Subscription Deletion Failed', 'Outlook Calendar subscription delete Failed', 'Oulook calendar subscription deletion Failed.', $debug_log_data, $bookingpress_debug_integration_log_id );                    
                }
            }

            $wpdb->delete(
                $tbl_bookingpress_staffmembers_meta,
                array(
                    'bookingpress_staffmembermeta_key' => 'bookingpress_selected_ocalendar',
                    'bookingpress_staffmember_id' => $staffmember_id
                )
            );

            $wpdb->delete(
                $tbl_bookingpress_staffmembers_meta,
                array(
                    'bookingpress_staffmembermeta_key' => 'bookingpress_ocalendar_list',
                    'bookingpress_staffmember_id' => $staffmember_id
                )
            );

            $wpdb->delete(
                $tbl_bookingpress_staffmembers_meta,
                array(
                    'bookingpress_staffmembermeta_key' => 'bookingpress_ocalendar_oauthdata',
                    'bookingpress_staffmember_id' => $staffmember_id
                )
            );

            
            $wpdb->delete(
                $tbl_bookingpress_staffmembers_meta,
                array(
                    'bookingpress_staffmembermeta_key' => 'bookingpress_staff_oc_events',
                    'bookingpress_staffmember_id' => $staffmember_id
                )
            );
            $wpdb->delete(
                $tbl_bookingpress_staffmembers_meta,
                array(
                    'bookingpress_staffmembermeta_key' => 'bookingpress_oc_subscription_details',
                    'bookingpress_staffmember_id' => $staffmember_id
                )
            );

            $wpdb->delete(
                $tbl_bookingpress_staffmembers_meta,
                array(
                    'bookingpress_staffmembermeta_key' => 'bookingpress_oc_subscription_expiry',
                    'bookingpress_staffmember_id' => $staffmember_id
                )
            );

            $response['variant'] = 'success';
            $response['title']   = esc_html__( 'Success', 'bookingpress-outlook-calendar' );
            $response['msg']     = esc_html__( 'Sign out successfully.', 'bookingpress-outlook-calendar' );

            echo wp_json_encode( $response );
            die;
        }

        function bookingpress_is_outlook_calendar_addon_activated($plugin,$network_activation)
        {              
            $myaddon_name = "bookingpress-outlook-calendar/bookingpress-outlook-calendar.php";

            if($plugin == $myaddon_name)
            {

                if(!(is_plugin_active('bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php')))
                {
                    deactivate_plugins($myaddon_name, FALSE);
                    $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name);
                    $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Outlook Calendar Add-on', 'bookingpress-outlook-calendar');
					$bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-outlook-calendar'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
					wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                    die;
                }

                $license = trim( get_option( 'bkp_license_key' ) );
                $package = trim( get_option( 'bkp_license_package' ) );

                if( '' === $license || false === $license ) 
                {
                    deactivate_plugins($myaddon_name, FALSE);
                    $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name);
                    $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Outlook Calendar Add-on', 'bookingpress-outlook-calendar');
					$bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-outlook-calendar'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
					wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                    die;
                }
                else
                {
                    $store_url = BOOKINGPRESS_OUTLOOK_CALENDAR_STORE_URL;
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
                            $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Outlook Calendar Add-on', 'bookingpress-outlook-calendar');
                            $bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-outlook-calendar'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
                            wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                            die;
                        }

                    }
                    else
                    {
                        deactivate_plugins($myaddon_name, FALSE);
                        $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name);
                        $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Outlook Calendar Add-on', 'bookingpress-outlook-calendar');
                        $bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-outlook-calendar'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
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
                    $bookingpress_config_url = add_query_arg('setting_tab', 'outlook_calendar', $bookingpress_config_url);
                    if($bookingpress_body_res_val['addon_key'] == 'bookingpress_outlook_calendar') {
                        $bookingpress_body_res[$bookingpress_body_res_key]['addon_configure_url'] = $bookingpress_config_url;
                    }
                }
            }
            return $bookingpress_body_res;
        }  

        function bookingpress_outlook_calendar_admin_notices(){
            if( !function_exists('is_plugin_active') ){
                include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            }

            if( !is_plugin_active('bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php') ){
                echo "<div class='notice notice-warning'><p>" . esc_html__('BookingPress - Outlook Calendar Integration plugin requires BookingPress Premium Plugin installed and active.', 'bookingpress-outlook-calendar') . "</p></div>";
            }

            global $bookingpress_version;
            if( version_compare( $bookingpress_version, '1.0.41', '<') ){
                echo "<div class='notice notice-warning'><p>" . esc_html__('BookingPress - Outlook Calendar Integration plugin requires BookingPress Appointment Booking plugin installed with version 1.0.41 or higher', 'bookingpress-outlook-calendar') . "</p></div>";
            }

            if( file_exists( WP_PLUGIN_DIR . '/bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php' ) ){
                $bpa_pro_plugin_info = get_plugin_data( WP_PLUGIN_DIR . '/bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php' );
                $bpa_pro_plugin_version = $bpa_pro_plugin_info['Version'];

                if( version_compare( $bpa_pro_plugin_version, '1.2', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("it's highly recommended to update the BookingPress Premium Plugin to version 1.2 or higher in order to use the BookingPress Outlook Calendar plugin", "bookingpress-outlook-calendar")."</p></div>";
                }
            }
        }

        function bookingpress_available_integration_addon_list_func($bookingpress_integration_addon_list) {
            $bookingpress_integration_addon_list[] = 'outlook-calendar';
            return  $bookingpress_integration_addon_list;
        }

        function bookingpress_load_integration_settings_data_func(){
            ?>
                vm.getSettingsData('outlook_calendar_setting','bookingpress_outlook_calendar')
                setTimeout(function(){
                    if(vm.$refs.bookingpress_outlook_calendar != undefined){
                        vm.$refs.bookingpress_outlook_calendar.clearValidate();
                    }
                }, 2000);
            <?php            
        }

        function bookingpress_add_integration_settings_section_func() {
        ?>
            <el-row type="flex" class="bpa-mlc-head-wrap-settings bpa-gs-tabs--pb__heading __bpa-is-groupping" v-if="bpa_integration_active_tab == 'outlook_calendar'">
                <el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-gs-tabs--pb__heading--left">
                    <h1 class="bpa-page-heading"><?php esc_html_e( 'Outlook Calendar', 'bookingpress-outlook-calendar' ); ?></h1>
                </el-col>
                <el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
                    <div class="bpa-hw-right-btn-group bpa-gs-tabs--pb__btn-group">												
                        <el-button class="bpa-btn bpa-btn--primary" @click="saveSettingsData('bookingpress_outlook_calendar','outlook_calendar_setting')" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''"  :disabled="is_disabled" >
                            <span class="bpa-btn__label"><?php esc_html_e( 'Save', 'bookingpress-outlook-calendar' ); ?></span>
                            <div class="bpa-btn--loader__circles">				    
                                <div></div>
                                <div></div>
                                <div></div>
                            </div>
                        </el-button>
                    </div>
                </el-col>
            </el-row>
            <el-form id="bookingpress_outlook_calendar" ref="bookingpress_outlook_calendar" :rules="bookingpress_outlook_rules" :model="bookingpress_outlook_calendar" label-position="top" @submit.native.prevent v-if="bpa_integration_active_tab == 'outlook_calendar'">                        
                <div class="bpa-gs__cb--item">                
                    <div class="bpa-gs__cb--item-body bpa-gs__integration-cb--item-body">
                        <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" v-if="is_staffmember_activated != 1">                    
                            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-gs__cb-item-left">
                                <div class="bpa-toast-notification --bpa-warning">
                                    <div class="bpa-front-tn-body">
                                        <span class="material-icons-round">info</span>
                                        <p><?php esc_html_e('Outlook Calendar Integration requires Staff Member Module to be activated.', 'bookingpress-outlook-calendar') ?></p>
                                    </div>
                                </div>
                            </el-col>
                        </el-row>
                        <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                            <el-col :xs="12" :sm="12" :md="12" :lg="08" :xl="08" class="bpa-gs__cb-item-left">
                                <h4><?php esc_html_e( 'Application (Client) Id', 'bookingpress-outlook-calendar' ); ?></h4>
                            </el-col>                            
                            <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                                <el-form-item prop="outlook_calendar_client_id">
                                <el-input class="bpa-form-control" v-model="bookingpress_outlook_calendar.outlook_calendar_client_id" placeholder="<?php esc_html_e( 'Enter client id', 'bookingpress-outlook-calendar' ); ?>"></el-input>        
                                </el-form-item>
                            </el-col>                            
                        </el-row>
                        <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                            <el-col :xs="12" :sm="12" :md="12" :lg="08" :xl="08" class="bpa-gs__cb-item-left">
                                <h4><?php esc_html_e( 'Client Secret', 'bookingpress-outlook-calendar' ); ?></h4>
                            </el-col>                            
                            <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                                <el-form-item prop="outlook_calendar_client_secret">
                                <el-input class="bpa-form-control" v-model="bookingpress_outlook_calendar.outlook_calendar_client_secret" placeholder="<?php esc_html_e( 'Enter client secret', 'bookingpress-outlook-calendar' ); ?>"></el-input>        
                                </el-form-item>
                            </el-col>                            
                        </el-row>
                        <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                            <el-col :xs="12" :sm="12" :md="12" :lg="08" :xl="08" class="bpa-gs__cb-item-left">
                                <h4><?php esc_html_e( 'Redirect URL', 'bookingpress-outlook-calendar' ); ?></h4>
                            </el-col>                            
                            <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                                <div class="bpa-gs__redirect-url-val">
                                    <p><?php echo get_home_url() ?></p>
                                    <span class="material-icons-round" @click="bookingpress_ocalendar_insert_placeholder('<?php echo get_home_url() ?>','text')">content_copy</span>
                                </div>
                            </el-col>                            
                        </el-row>
                        <div class="bpa-gs__cb--item-heading">
                            <h4 class="bpa-sec--sub-heading"><?php esc_html_e('Event Settings', 'bookingpress-outlook-calendar'); ?></h4> 
                        </div>   
                        <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                            <el-col :xs="12" :sm="12" :md="12" :lg="08" :xl="08" class="bpa-gs__cb-item-left">
                                <h4><?php esc_html_e( 'Event Title', 'bookingpress-outlook-calendar' ); ?></h4>
                            </el-col>                            
                            <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                                <el-form-item prop="outlook_calendar_event_title">
                                <el-input class="bpa-form-control" v-model="bookingpress_outlook_calendar.outlook_calendar_event_title" placeholder="<?php esc_html_e( 'Enter event title', 'bookingpress-outlook-calendar' ); ?>"></el-input>        
                                </el-form-item>
                            </el-col>                            
                        </el-row>
                        <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                            <el-col :xs="12" :sm="12" :md="12" :lg="08" :xl="08" class="bpa-gs__cb-item-left">
                                <h4><?php esc_html_e( 'Event Description', 'bookingpress-outlook-calendar' ); ?></h4>
                            </el-col>                            
                            <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                                <el-form-item prop="outlook_calendar_event_description">
                                    <el-input type="textarea" :rows="5" class="bpa-form-control" v-model="bookingpress_outlook_calendar.outlook_calendar_event_description" placeholder="<?php esc_html_e( 'Enter event description', 'bookingpress-outlook-calendar' ); ?>"></el-input>
                                </el-form-item>
                            </el-col>                            
                        </el-row>
                        <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                            <el-col :xs="12" :sm="12" :md="12" :lg="08" :xl="08" class="bpa-gs__cb-item-left">
                                <h4><?php esc_html_e( 'Event Location', 'bookingpress-outlook-calendar' ); ?></h4>
                            </el-col>
                            <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                                <el-form-item prop="outlook_calendar_event_location">
                                    <el-select v-model="bookingpress_outlook_calendar.outlook_calendar_event_location" class="bpa-form-control" placeholder="<?php esc_html_e( 'Select Field', 'bookingpress-outlook-calendar' ); ?>">
                                        <el-option v-for="item in bookingpress_ocalendar_form_fields" :key="item.value" :label="item.label" :value="item.value"></el-option>
                                    </el-select>
                                </el-form>
                            </el-col>
                        </el-row>
                        <el-row class="bpa-gs--tabs-pb__cb-item-row">
                            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                <div class="bpa-gs__cb--item-heading">
                                    <h4 class="bpa-sec--sub-heading __bpa-is-gs-heading-mb-0"><?php esc_html_e('Insert Placeholders', 'bookingpress-outlook-calendar'); ?></h4>							
                                </div>
                            </el-col>
                        </el-row>
                        <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="24">							
                            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                                <el-form-item>
                                    <el-select class="bpa-form-control" placeholder="<?php esc_html_e( 'Customer', 'bookingpress-outlook-calendar' ); ?>" @change="bookingpress_ocalendar_insert_placeholder($event)">
                                        <el-option v-for="item in bookingpress_outlook_calendar_customer_placeholder" :key="item.value" :label="item.name" :value="item.value">
                                        </el-option>
                                    </el-select>
                                </el-form-item>    
                            </el-col>
                            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                                <el-form-item>
                                    <el-select class="bpa-form-control" placeholder="<?php esc_html_e( 'Service', 'bookingpress-outlook-calendar' ); ?>" @change="bookingpress_ocalendar_insert_placeholder($event)">
                                        <el-option v-for="item in bookingpress_outlook_calendar_service_placeholder" :key="item.value" :label="item.name" :value="item.value">
                                        </el-option>
                                    </el-select>
                                </el-form-item>    
                            </el-col>																	
                        </el-row>   
                        <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="24">							
                            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                                <el-form-item>
                                    <el-select class="bpa-form-control" placeholder="<?php esc_html_e( 'Company', 'bookingpress-outlook-calendar' ); ?>" @change="bookingpress_ocalendar_insert_placeholder($event)" >
                                        <el-option v-for="item in bookingpress_outlook_calendar_company_placeholder" :key="item.value" :label="item.name" :value="item.value">
                                        </el-option>
                                    </el-select>
                                </el-form-item>    
                            </el-col>
                            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                                <el-form-item>
                                    <el-select class="bpa-form-control" placeholder="<?php esc_html_e( 'Staff Member', 'bookingpress-outlook-calendar' ); ?>" @change="bookingpress_ocalendar_insert_placeholder($event)" >
                                        <el-option v-for="item in bookingpress_outlook_calendar_staff_placeholder" :key="item.value" :label="item.name" :value="item.value"></el-option>
                                    </el-select>                                    
                                </el-form-item>    
                            </el-col>																	
                        </el-row>    
                        <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="24">							
                            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                                <el-form-item>
                                    <el-select class="bpa-form-control" placeholder="<?php esc_html_e( 'Appointment', 'bookingpress-outlook-calendar' ); ?>" 
                                    @change="bookingpress_ocalendar_insert_placeholder($event)"
										popper-class="bpa-el-select--is-with-navbar">
                                        <el-option-group v-for="item in bookingpress_outlook_calendar_appointment_placeholder" :label="item.field_group_name">
                                             <el-option v-for="field_data in item.field_list" :key="field_data.value" :label="field_data.name" :value="field_data.value"></el-option>
                                        </el-option-group>
                                    </el-select>                             
                                </el-form-item>    
                            </el-col>
                            <?php do_action('bookingpress_add_ocalendar_outside_notification_placeholders'); ?>
                        </el-row>                                                          
                        <div class="bpa-gs__cb--item-heading">
                            <h4 class="bpa-sec--sub-heading"><?php esc_html_e('Staff Member related Settings', 'bookingpress-outlook-calendar'); ?></h4> 
                        </div>
                        <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                            <el-col :xs="12" :sm="12" :md="12" :lg="08" :xl="08" class="bpa-gs__cb-item-left">
                                <h4><?php esc_html_e( 'Maximum Number Of Events Returned', 'bookingpress-outlook-calendar' ); ?></h4>
                            </el-col>                            
                            <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                                <el-form-item prop="outlook_calendar_max_event">
                                    <el-input class="bpa-form-control" v-model="bookingpress_outlook_calendar.outlook_calendar_max_event" placeholder="<?php esc_html_e( 'Enter maximum number of event returned', 'bookingpress-outlook-calendar' ); ?>"></el-input>
                                </el-form-item>
                            </el-col>                            
                        </el-row>      
                    </div>    
                </div>    
            </el-form>    
            <?php
        }

        function bookingpress_outlook_calendar_vue_methods(){
            global $bookingpress_notification_duration;
            $bookingpress_placeholder_success_msg = __( 'Placeholder copied', 'bookingpress-outlook-calendar' );
            $bookingpress_redirect_url_success_msg = __( 'URL copied successfully', 'bookingpress-outlook-calendar' );
            ?>
            bookingpress_ocalendar_insert_placeholder(event,type='placeholder')
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
                    vm.$notify({ title: '<?php esc_html_e( 'Success', 'bookingpress-outlook-calendar' ); ?>', message: '<?php echo esc_html( $bookingpress_placeholder_success_msg ); ?>', type: 'success', customClass: 'success_notification',duration:<?php echo intval($bookingpress_notification_duration); ?>,});
                } else {
                    vm.$notify({ title: '<?php esc_html_e( 'Success', 'bookingpress-outlook-calendar' ); ?>', message: '<?php echo esc_html( $bookingpress_redirect_url_success_msg ); ?>', type: 'success', customClass: 'success_notification',duration:<?php echo intval($bookingpress_notification_duration); ?>,});
                }
            },

            <?php
        }

        function bookingpress_staff_member_outlook_calendar_integration(){
            ?>
            <div class="bpa-staff-integration-item">
                <el-row>
                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <div class="bpa-en-status--swtich-row">
                            <label class="bpa-form-label"><?php esc_html_e( 'Outlook Calendar Integration', 'bookingpress-outlook-calendar' ); ?></label>
                            <el-switch class="bpa-swtich-control" v-model="bookingpress_enable_outlook_calendar"></el-switch>
                        </div>
                    </el-col>
                </el-row>
                <div class="bpa-sii__body" v-if="bookingpress_enable_outlook_calendar == true || bookingpress_enable_outlook_calendar == 'true'">
                    <el-row type="flex" :gutter="32">
                        <el-col :xs="16" :sm="16" :md="16" :lg="18" :xl="20">
                            <el-select v-model="bookingpress_selected_ocalendar" class="bpa-form-control bpa-form-control__left-icon" placeholder="<?php esc_html_e( 'Select Outlook Calendar', 'bookingpress-outlook-calendar' ); ?>">
                                <el-option v-for="item in bookingpress_ocalendar_list" :key="item.value" :label="item.name" :value="item.value"></el-option>
                            </el-select>
                        </el-col>
                        <el-col :xs="8" :sm="8" :md="8" :lg="6" :xl="1" class="bookingpress-staff-oc-refresh-col" v-if="bookingpress_ocalendar_list != null && bookingpress_ocalendar_list.length > 0  && bookingpress_outlook_calendar_oauth_staffmeta.length > 0">
                            <el-tooltip popper-class="outlook_calendar_staff_member_module_refresh" effect="dark" content="" placement="top" open-delay="300">
                                <div slot="content">
                                    <span><?php esc_html_e( 'Refresh Calendar List', 'bookingpress-outlook-calendar' ); ?></span>
                                </div>
                                <el-button :disabled="bookingpress_disable_oc_refresh_link" class="bpa-btn bpa-btn__medium bpa-btn--full-width" @click="bookingpress_refresh_outlook_calendar_list(staff_members.update_id)">
                                <svg width="18" height="18" viewBox="0.96 1.88 18 18" fill="none" xmlns="http://www.w3.org/2000/svg" id="bookingpress-oc-svg" >
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
                            <el-button class="bpa-btn bpa-btn__medium bpa-btn--full-width" @click="( bookingpress_ocalendar_list != null && bookingpress_ocalendar_list.length > 0 ) ? bookingpress_outlook_calendar_signout(staff_members.update_id) : bookingpress_outlook_calendar_auth()">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M7.57897 0H0V7.57897H7.57897V0Z" fill="#F25022"/>
                                    <path d="M7.57897 8.4209H0V15.9999H7.57897V8.4209Z" fill="#00A4EF"/>
                                    <path d="M16.0008 0H8.42188V7.57897H16.0008V0Z" fill="#7FBA00"/>
                                    <path d="M16.0008 8.4209H8.42188V15.9999H16.0008V8.4209Z" fill="#FFB900"/>
                                </svg>
                                <span v-if="bookingpress_ocalendar_list != null && bookingpress_ocalendar_list.length > 0"><?php esc_html_e('Sign Out from Outlook', 'bookingpress-outlook-calendar'); ?></span>
                                <span v-else><?php esc_html_e('Sign In With Outlook', 'bookingpress-outlook-calendar'); ?></span>
                            </el-button>
                        </el-col>
                    </el-row>
                </div>
                <input type="hidden" id="bookingpress_outlook_calendar_oauth_data" v-model="bookingpress_outlook_calendar_oauth_data" />                
            </div>
            <?php
        }

        function bookingpress_staff_member_outlook_oauth_methods(){
            $bookingpress_addon_popup_field_form = $this->bookingpress_get_outlook_calendar_credentials();

            $bookingpress_outlook_client_id =  $bookingpress_addon_popup_field_form['outlook_calendar_client_id'];
            $bookingpress_outlook_client_secret = $bookingpress_addon_popup_field_form['outlook_calendar_client_secret'];
            $bookingpress_outlook_redirect_uri = get_home_url();
            ?>
            bookingpress_outlook_calendar_auth(){
                let url = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize';

                let oauth_url = url + '?client_id=<?php echo $bookingpress_outlook_client_id; ?>&scope=calendars.readwrite offline_access calendars.read.shared&response_type=code&redirect_uri=<?php echo urlencode( $bookingpress_outlook_redirect_uri); ?>';

                window.open( oauth_url, 'BookingPress Outlook Calendar Authentication', 'height=500, width=500');
            },
            bookingpress_outlook_calendar_signout( staffmember_id ){
                const vm = this;
                if( 1 > staffmember_id ){
                    return false;
                }
                let postData = {
                    action: "bookingpress_signout_outlook_calendar",
                    staffmember: staffmember_id,
                    _wpnonce: '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'
                };
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then( function (response) {
                    if( response.data.variant == "success" ){
                        vm.bookingpress_ocalendar_list = [];
                        vm.bookingpress_selected_ocalendar = '';
                        vm.bookingpress_outlook_calendar_oauth_staffmeta = '';
                    }
                }.bind(this) )
				.catch( function (error) {
                    console.log( error );
                });
            },
            bookingpress_refresh_outlook_calendar_list( staffmember_id ) {
                const vm = this;
                if( 1 > staffmember_id ){
                    return false;
                }
                let oc_svg = document.getElementById('bookingpress-oc-svg');
                oc_svg.classList.add('oc-refresh-rotate');    
                vm.bookingpress_disable_oc_refresh_link = true;
                let postData = {
                    action: "bookingpress_refresh_outlook_calendar_list",
                    staffmember: staffmember_id,
                    selected_calendar: vm.bookingpress_selected_ocalendar,
                    _wpnonce: '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'
                };
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then( function (response) {
                    oc_svg.classList.remove('oc-refresh-rotate');
                    vm.bookingpress_disable_oc_refresh_link = false;
                    if( response.data.variant == "success" && undefined != response.data.outlook_calendars){
                        vm.bookingpress_ocalendar_list = response.data.outlook_calendars;
                    }
                    if( "undefined" != typeof response.data.is_oc_reset_list && true == response.data.is_oc_reset_list ){
                        vm.bookingpress_selected_ocalendar = '';
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

        function bookingpress_staff_member_outlook_calendar_dynamic_field_data( $bookingpress_staff_member_vue_data_fields ){            
            
            $bookingpress_staff_member_vue_data_fields['bookingpress_ocalendar_list'] = array();
            $bookingpress_staff_member_vue_data_fields['bookingpress_selected_ocalendar'] = '';
            $bookingpress_staff_member_vue_data_fields['bookingpress_ocalendar_signin_image'] = BOOKINGPRESS_OUTLOOK_CALENDAR_URL . '/images/signin-with-microsoft.png';
            $bookingpress_staff_member_vue_data_fields['bookingpress_outlook_calendar_oauth_data'] = '';
            $bookingpress_staff_member_vue_data_fields['bookingpress_enable_outlook_calendar'] = false;
            $bookingpress_staff_member_vue_data_fields['bookingpress_outlook_calendar_oauth_staffmeta'] = '';
            $bookingpress_staff_member_vue_data_fields['bookingpress_disable_oc_refresh_link'] = false;
            return $bookingpress_staff_member_vue_data_fields;
        }
        function bookingpress_edit_profile_outlook_calendar_dynamic_field_data($bookingpress_myprofile_data_fields_arr){
            global $tbl_bookingpress_staffmembers,$wpdb,$bookingpress_pro_staff_members;
            $bookingpress_staff_ocalendar_list = array();
            $bookingpress_staff_ocalendar_oauth = array();
            $bookingpress_selected_ocalendar = '';
            $bookingpress_enable_outlook_calendar = false;                                   
            $bookingpress_current_user_id = get_current_user_id();
            if(!empty($bookingpress_current_user_id)){
                $bookingpress_staffmember_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_wpuser_id = %d", $bookingpress_current_user_id), ARRAY_A);
                $bookingpress_staffmember_id = !empty($bookingpress_staffmember_data['bookingpress_staffmember_id']) ? intval($bookingpress_staffmember_data['bookingpress_staffmember_id']) : 0;                
                if(!empty($bookingpress_staffmember_id)){                    
                    $bookingpress_staff_ocalendar_list = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_ocalendar_list' );
                    $bookingpress_selected_ocalendar = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_selected_ocalendar' );                    
                   $bookingpress_enable_outlook_calendar = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta($bookingpress_staffmember_id, 'bookingpress_enable_outlook_calendar');                   
                   $bookingpress_enable_outlook_calendar = ($bookingpress_enable_outlook_calendar == "true") ? true : false;

                   $bookingpress_staff_ocalendar_oauth = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_ocalendar_oauthdata' );
                }   
            }

            $bookingpress_myprofile_data_fields_arr['bookingpress_ocalendar_list'] = json_decode( $bookingpress_staff_ocalendar_list, true );
            $bookingpress_myprofile_data_fields_arr['bookingpress_selected_ocalendar'] = $bookingpress_selected_ocalendar;
            $bookingpress_myprofile_data_fields_arr['bookingpress_ocalendar_signin_image'] = BOOKINGPRESS_OUTLOOK_CALENDAR_URL . '/images/signin-with-microsoft.png';
            $bookingpress_myprofile_data_fields_arr['bookingpress_enable_outlook_calendar'] = $bookingpress_enable_outlook_calendar;
            $bookingpress_myprofile_data_fields_arr['bookingpress_outlook_calendar_oauth_staffmeta'] = $bookingpress_staff_ocalendar_oauth;
            $bookingpress_myprofile_data_fields_arr['bookingpress_disable_oc_refresh_link'] = false;
            $get_current_logged_id = wp_get_current_user();

            if( in_array( 'bookingpress-staffmember', $get_current_logged_id->roles ) ){
                global $wpdb, $tbl_bookingpress_staffmembers;
                $bookingpress_current_user_id = $get_current_logged_id->ID;
                $bookingpress_staffmember_data = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_staffmember_id FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_wpuser_id = %d", $bookingpress_current_user_id), ARRAY_A);
                if( !empty( $bookingpress_staffmember_data['bookingpress_staffmember_id'] ) ){
                    $bookingpress_myprofile_data_fields_arr['staff_members'] = array(
                        'update_id' => $bookingpress_staffmember_data['bookingpress_staffmember_id']
                    );
                }
            }

            return $bookingpress_myprofile_data_fields_arr;
        }

        function bookingpress_get_outlook_calendar_credentials(){
            global $BookingPress;
            $bookingpress_addon_popup_field_form = array();
            $bookingpress_get_settings_data = array('outlook_calendar_client_id','outlook_calendar_client_secret','outlook_calendar_event_title', 'outlook_calendar_event_description', 'outlook_calendar_event_location', 'outlook_calendar_max_event');
            foreach ( $bookingpress_get_settings_data as $bookingpress_setting_key ) {
                $bookingpress_setting_val  = $BookingPress->bookingpress_get_settings( $bookingpress_setting_key, 'outlook_calendar_setting');                                
                $bookingpress_addon_popup_field_form[$bookingpress_setting_key] = $bookingpress_setting_val; 
            }

            return $bookingpress_addon_popup_field_form;
        }

        function bookingpress_assign_ocalendar_auth_link(){
            ?>
                vm2.bookingpress_ocalendar_list = edit_staff_members_details.bookingpress_ocalendar_list;
                vm2.bookingpress_selected_ocalendar = edit_staff_members_details.bookingpress_selected_ocalendar;
                vm2.bookingpress_enable_outlook_calendar = edit_staff_members_details.bookingpress_enable_outlook_calendar;
                vm2.bookingpress_outlook_calendar_oauth_staffmeta = edit_staff_members_details.bookingpress_outlook_calendar_oauth_staffmeta;
            <?php
        }

        function bookingpress_modify_disable_dates_outlook_calendar( $bookingpress_disable_date, $bookingpress_selected_service, $month_check = '' ){
            $bookingpress_staffmember_id = !empty( $_POST['appointment_data_obj']['bookingpress_selected_staff_member_details']['selected_staff_member_id'] ) ? intval( $_POST['appointment_data_obj']['bookingpress_selected_staff_member_details']['selected_staff_member_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.

            if( empty( $bookingpress_staffmember_id ) && !empty( $_POST['bookingpress_selected_staffmember']['selected_staff_member_id'] ) ){
				$bookingpress_staffmember_id = intval( $_POST['bookingpress_selected_staffmember']['selected_staff_member_id'] );
			}

			if( empty( $bookingpress_staffmember_id ) ){
				return $bookingpress_disable_date;
			}

            global $tbl_bookingpress_appointment_bookings, $wpdb, $bookingpress_pro_staff_members, $bookingpress_pro_appointment_bookings, $tbl_bookingpress_services;

            $bpa_day_events = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_service_duration_unit FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = %d", $bookingpress_selected_service ) );

            if( empty( $bpa_day_events ) || ( !empty( $bpa_day_events ) && $bpa_day_events->bookingpress_service_duration_unit != 'd' ) ){
                return $bookingpress_disable_date;
            }

            $calendarId = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_selected_ocalendar' );
            if( empty( $calendarId ) ){
                return $bookingpress_disable_date;
            }
            $staff_token_data = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_ocalendar_oauthdata');
            if( empty( $staff_token_data ) ){
                return $bookingpress_disable_date;
            }

            $staff_token_data = json_decode( $staff_token_data );

            $staff_access_token = $staff_token_data->access_token;
            if( current_time( 'timestamp' ) > ( $staff_token_data->created + $staff_token_data->expires_in ) ){
                $staff_access_token = $this->bookingpress_refresh_access_token( $staff_token_data, $bookingpress_disable_date );
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

            $current_timezone = wp_timezone_string();

            $appointment_data = $_POST['appointment_data_obj'];
            $selected_service_duration = $appointment_data['selected_service_duration'];
            $selected_service_duration_unit = $appointment_data['selected_service_duration_unit'];

            foreach( $period as $dt ){
                $selected_date = $dt->format('Y-m-d');

                if( !empty( $disable_included_date ) && in_array( $selected_date, $disable_included_date ) ){
                    continue;
                }
                $get_calendar_data = $this->bookingpress_retrieve_outlook_calendar_events( $bookingpress_staffmember_id, $calendarId, $selected_date, true );

                if( empty( $get_calendar_data ) ){
                    continue;
                }
                $fcount = 0;
                if( $fcount < 1 ){
                    $first_occurrence[] = date('Y-m-d', strtotime( $selected_date ) );
                }

                $skip_check = false;

                foreach( $get_calendar_data as $event_id => $event_data ){

                    $db_service_id = $wpdb->get_var( $wpdb->prepare( "SELECT bookingpress_service_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_outlook_calendar_event_id = %s", $event_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
                
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

        function bookingpress_generate_staff_member_outlook_auth_link( $bookingpress_edit_staff_members_details ){

            global $bookingpress_pro_staff_members;

            $bookingpress_addon_popup_field_form = $this->bookingpress_get_outlook_calendar_credentials();

            $bookingpress_client_id =  $bookingpress_addon_popup_field_form['outlook_calendar_client_id'];
            $bookingpress_client_secret = $bookingpress_addon_popup_field_form['outlook_calendar_client_secret'];

            $redirect_url = admin_url('admin.php?page=bookingpress_staff_members');
            $edit_staff_id = $_POST['edit_id'];
            
            $bookingpress_staff_ocalendar_list = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $edit_staff_id, 'bookingpress_ocalendar_list' );
            $bookingpress_ocalendar_list_arr = json_decode( $bookingpress_staff_ocalendar_list, true );
            if( !empty( $bookingpress_ocalendar_list_arr ) ){
                $bookingpress_edit_staff_members_details['bookingpress_ocalendar_list'] = $bookingpress_ocalendar_list_arr;
            } else {
                $bookingpress_edit_staff_members_details['bookingpress_ocalendar_list'] = array();
            }

            $bookingpress_selected_ocalendar = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $edit_staff_id, 'bookingpress_selected_ocalendar' );
            if( !empty( $bookingpress_selected_ocalendar ) ){    
                $bookingpress_edit_staff_members_details['bookingpress_selected_ocalendar'] = $bookingpress_selected_ocalendar;
            } else {
                $bookingpress_edit_staff_members_details['bookingpress_selected_ocalendar'] = '';
            }

            $bookingpress_enable_outlook_calendar = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta($edit_staff_id, 'bookingpress_enable_outlook_calendar');
            $bookingpress_enable_outlook_calendar = !empty($bookingpress_enable_outlook_calendar) && $bookingpress_enable_outlook_calendar == 'true' ? true : false;

            $bookingpress_edit_staff_members_details['bookingpress_enable_outlook_calendar'] = $bookingpress_enable_outlook_calendar;

            $staff_auth_data = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $edit_staff_id, 'bookingpress_ocalendar_oauthdata' );
            if( !empty( $staff_auth_data ) ){
                $bookingpress_edit_staff_members_details['bookingpress_outlook_calendar_oauth_staffmeta'] = $staff_auth_data;
            } else {
                $bookingpress_edit_staff_members_details['bookingpress_outlook_calendar_oauth_staffmeta'] = array();
            }
            
            return $bookingpress_edit_staff_members_details;
        }
        
        function bookingpress_staff_member_authentication_callback(){

            if( !empty( $_GET[ 'code' ] ) ){
                $auth_code = !empty( $_REQUEST['code'] ) ? $_REQUEST['code'] : '';

                $bookingpress_addon_popup_field_form = $this->bookingpress_get_outlook_calendar_credentials();

                $bookingpress_client_id =  $bookingpress_addon_popup_field_form['outlook_calendar_client_id'];
                $bookingpress_client_secret = $bookingpress_addon_popup_field_form['outlook_calendar_client_secret'];

                $url = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
                $redirect_url = get_home_url();


                $body = array(
                    'client_id' 	=> $bookingpress_client_id,
                    'redirect_uri' 	=> $redirect_url,
                    'client_secret' => $bookingpress_client_secret,
                    'code' 			=> $auth_code,
                    'scope' 		=> 'calendars.readwrite offline_access calendars.read.shared',
                    'grant_type' 	=> 'authorization_code'
                );
                

                $args = array(
                    'body' 			=> $body,
                    'timeout' 		=> 5000,
                    'headers' 		=> array(
                        'Content-Type' => 'application/x-www-form-urlencoded'
                    ),
                    'cookies' 		=> array()
                );
    
                $response = wp_remote_post($url,$args);

                global $bookingpress_debug_integration_log_id;
                if( is_wp_error( $response ) ){
                    $debug_log_data = array(
                        'outlook_calendar_error_message' => $response->get_error_message(),
                        'outlook_calendar_log_placement' => 'failed generating token',
                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                    );
                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'Authorizing Outlook to generate Token', $debug_log_data, $bookingpress_debug_integration_log_id );
                    ?>
                    <script> window.close();</script>
                    <?php
                    return;
                }
                
                if( 200 != $response['response']['code'] ){
                    $debug_log_data = array(
                        'outlook_calendar_message' => $response,
                        'outlook_calendar_log_placement' => 'failed generating token status not 200',
                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                    );
                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'Generate Token Response Not 200 OK', $debug_log_data, $bookingpress_debug_integration_log_id );
                    ?>
                    <script> window.close();</script>
                    <?php
                    return;
                } else {
                    $debug_log_data = array(
                        'outlook_calendar_message' => $response,
                        'outlook_calendar_log_placement' => 'generating token status 200',
                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                    );
                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'Generate Token Response 200 OK', $debug_log_data, $bookingpress_debug_integration_log_id );
                }

                $res_body = json_decode($response['body'],true);
                
                $access_token_data = array(
                    'access_token' => $res_body['access_token'],
                    'refresh_token' => $res_body['refresh_token'],
                    'expires_in' => $res_body['expires_in'],
                    'created' => current_time('timestamp')
                );
                
                $list_records = 'https://graph.microsoft.com/v1.0/me/calendars';

                $args = array(
                    'timeout' => 5000,
                    'headers' => array(
                        'Authorization' => 'Bearer ' . $res_body['access_token'],
                        'Content-Type'  => 'application/json'
                    )
                );

                $resp_calendars = wp_remote_get( $list_records, $args );

                if( is_wp_error( $resp_calendars ) ){
                    $debug_log_data = array(
                        'outlook_calendar_message' => $resp_calendars->get_error_message(),
                        'outlook_calendar_log_placement' => 'failed retrieving outlook calendar list',
                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                    );
                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'Error while fetching calendar list from the outlook', $debug_log_data, $bookingpress_debug_integration_log_id );
                    ?>
                    <script> window.close();</script>
                    <?php
                    return;
                }

                if( 200 != $resp_calendars['response']['code'] ){
                    $debug_log_data = array(
                        'outlook_calendar_message' => $resp_calendars,
                        'outlook_calendar_log_placement' => 'outlook calendar retrieving list status not 200 OK',
                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                    );
                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'Status is not 200 OK while fetching calendar list from the outlook', $debug_log_data, $bookingpress_debug_integration_log_id );
                    ?>
                    <script> window.close();</script>
                    <?php
                    return;
                } else {
                    $debug_log_data = array(
                        'outlook_calendar_message' => $resp_calendars,
                        'outlook_calendar_log_placement' => 'outlook calendar retrieving list status 200 OK',
                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                    );
                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'Status is 200 OK while fetching calendar list from the outlook', $debug_log_data, $bookingpress_debug_integration_log_id );                    
                }               

                $calendar_lists = json_decode( $resp_calendars['body'], true );

                $outlook_calendars = array();
                if( empty( $calendar_lists ) ){
                    $debug_log_data = array(
                        'outlook_calendar_message' => $resp_calendars,
                        'outlook_calendar_log_placement' => 'outlook calendar empty list',
                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                    );
                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'Outlook calendar List is empty ', $debug_log_data, $bookingpress_debug_integration_log_id );
                    ?>
                    <script>window.close();</script>
                    <?php
                }
                foreach( $calendar_lists['value'] as $outlook_calendar ){
                    $outlook_calendars[] = array(
                        'value' => $outlook_calendar['id'],
                        'name' => $outlook_calendar['name']
                    );
                }
                ?>
                <script>
                    window.opener.app.bookingpress_ocalendar_list = <?php echo json_encode( $outlook_calendars ); ?>;
                    window.opener.app.bookingpress_outlook_calendar_oauth_data = '<?php echo json_encode( $access_token_data ); ?>';
                    window.close();
                </script>
                <?php
            }

        }

        function bookingpress_save_staff_member_func(){
            global $bookingpress_notification_duration;
            ?>  
                if(vm2.bookingpress_enable_outlook_calendar == true && vm2.bookingpress_selected_ocalendar == '') {
                    vm2.$notify({
                        title: '<?php esc_html_e( 'Error', 'bookingpress-outlook-calendar' ); ?>',
                        message: '<?php esc_html_e( 'Please select the outlook calendar', 'bookingpress-outlook-calendar' ); ?>',
                        type: 'error',
                        customClass: 'error_notification',
                        duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
                    });
                    vm2.is_disabled = false
                    vm2.is_display_save_loader = '0'						
                    return false;
                }
                postdata.bookingpress_ocalendar_list = vm2.bookingpress_ocalendar_list;
                postdata.bookingpress_selected_ocalendar = vm2.bookingpress_selected_ocalendar;
                postdata.bookingpress_outlook_calendar_oauth_data = vm2.bookingpress_outlook_calendar_oauth_data;
                postdata.bookingpress_enable_outlook_calendar = vm2.bookingpress_enable_outlook_calendar

            <?php
        }

        function bookingpress_staff_member_save_outlook_data( $response ){            
            global $bookingpress_pro_staff_members;
            
            $bookingpress_selected_ocalendar = !empty( $_REQUEST['bookingpress_selected_ocalendar'] ) ? $_REQUEST['bookingpress_selected_ocalendar'] : '';
            $bookingpress_ocalendar_list = !empty( $_REQUEST['bookingpress_ocalendar_list'] ) ? $_REQUEST['bookingpress_ocalendar_list'] : '';
            $bookingpress_ocalendar_oauthdata = !empty( $_REQUEST['bookingpress_outlook_calendar_oauth_data'] ) ? stripslashes_deep( $_REQUEST['bookingpress_outlook_calendar_oauth_data'] ) : '';
            $bookingpress_enable_outlook_calendar = !empty($_REQUEST['bookingpress_enable_outlook_calendar']) ? $_REQUEST['bookingpress_enable_outlook_calendar'] : '';
            
            $staffmember_id = !empty( $response['staffmember_id'] ) ? intval( $response['staffmember_id'] ) : 0;

            if( empty( $staffmember_id ) ){
                return $response;
            }

            if( !empty( $_REQUEST['bookingpress_action'] ) && 'bookingpress_edit_staffmember' != $_REQUEST['bookingpress_action'] ){
                return $response;
            }
            
            if( !empty( $bookingpress_ocalendar_list ) ){                
                $bookingpress_pro_staff_members->update_bookingpress_staffmembersmeta( $staffmember_id, 'bookingpress_ocalendar_list', json_encode( $bookingpress_ocalendar_list ) );
            }
            if( !empty( $bookingpress_selected_ocalendar ) ){
                $bookingpress_pro_staff_members->update_bookingpress_staffmembersmeta( $staffmember_id, 'bookingpress_selected_ocalendar', $bookingpress_selected_ocalendar );
            }            
            if( !empty( $bookingpress_ocalendar_oauthdata ) ){
                $bookingpress_pro_staff_members->update_bookingpress_staffmembersmeta( $staffmember_id, 'bookingpress_ocalendar_oauthdata', $bookingpress_ocalendar_oauthdata );
            }
            if(!empty($bookingpress_enable_outlook_calendar)){
                $bookingpress_pro_staff_members->update_bookingpress_staffmembersmeta($staffmember_id, 'bookingpress_enable_outlook_calendar', $bookingpress_enable_outlook_calendar);
            }            

            do_action( 'bookingpress_outlook_calendar_after_save_staff_settings', $staffmember_id );

            return $response;
        }

        function bookingpress_refresh_access_token( $access_token_data, $staff_member_id ){

            global $bookingpress_debug_integration_log_id;
            $url = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';

            $bookingpress_addon_popup_field_form = $this->bookingpress_get_outlook_calendar_credentials();

            $bookingpress_client_id =  $bookingpress_addon_popup_field_form['outlook_calendar_client_id'];
            $bookingpress_client_secret = $bookingpress_addon_popup_field_form['outlook_calendar_client_secret'];

            $body = array(
                'client_id' => $bookingpress_client_id,
                'client_secret' => $bookingpress_client_secret,
                'scope' => 'calendars.readwrite offline_access calendars.read.shared',
                'grant_type' => 'refresh_token',
                'refresh_token' => $access_token_data->refresh_token
            );

            $args = array(
                'timeout' => 5000,
                'body' => $body,
                'headers' => array(
                    'Content-Type' => 'application/x-www-form-urlencoded'
                )
            );

            $response = wp_remote_post( $url, $args );
            
            if( is_wp_error( $response) ){
                $debug_log_data = array(
                    'outlook_calendar_message' => $response,
                    'outlook_calendar_log_placement' => 'outlook calendar refresh token error',
                    'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                );
                do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'Outlook calendar refreshing token error', $debug_log_data, $bookingpress_debug_integration_log_id );
                return false;
            }

            if( 200 != $response['response']['code'] ){
                $debug_log_data = array(
                    'outlook_calendar_message' => $response,
                    'outlook_calendar_log_placement' => 'outlook calendar refresh token status not 200 OK',
                    'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                );
                do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'Outlook Calendar refreshing token status not 200 OK', $debug_log_data, $bookingpress_debug_integration_log_id );
                return false;
            } else {
                $debug_log_data = array(
                    'outlook_calendar_message' => $response,
                    'outlook_calendar_log_placement' => 'outlook calendar refresh token status 200 OK',
                    'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                );
                do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'Outlook Calendar refreshing token status 200 OK', $debug_log_data, $bookingpress_debug_integration_log_id );
            }

            $res_body = json_decode( $response['body'] );
            $res_body->created = current_time( 'timestamp' );
            
            global $bookingpress_pro_staff_members;
            $bookingpress_pro_staff_members->update_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_ocalendar_oauthdata', json_encode( $res_body ) );

            return $res_body->access_token;
        }

        function bookignpress_is_outlook_event_exist($calendar_id,$bookingpress_event_id,$staff_access_token) {

            global $bookingpress_debug_integration_log_id;

            $outlook_event_url = 'https://graph.microsoft.com/v1.0/me/calendars/'.$calendar_id.'/events/'.$bookingpress_event_id;
            $args = array(
                'timeout' => 4500,
                'headers' => array(
                    'Authorization' => 'Bearer ' . $staff_access_token,
                )
            );

            $resp = wp_remote_get( $outlook_event_url, $args );
                
            if( is_wp_error( $resp )) {
                $debug_log_data = array(                    
                    'outlook_calendar_message' => $resp->get_error_message(),
                    'outlook_calendar_log_placement' => 'error while check outllok calenadr event exist or not',                    
                    'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                );
                do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'Outlook Calendar check event exist', $debug_log_data, $bookingpress_debug_integration_log_id );                
                return 0;
            }                        

            if(!isset($resp['response']['code']) && 200 != $resp['response']['code'] ) {
                $debug_log_data = array(
                    'outlook_calendar_message' => $resp,
                    'outlook_calendar_log_placement' => 'status not 200 OK while fetching events for check event exist or not',                    
                    'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                );
                do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'status not 200 OK while fetching Outlook Calendar event lists for check event exist or not', $debug_log_data, $bookingpress_debug_integration_log_id );
                return 0;

            } else {

                $debug_log_data = array(
                    'outlook_calendar_message' => $resp,
                    'outlook_calendar_log_placement' => 'status 200 OK while fetching events from Outlook calendar for status change',                    
                    'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                );
                do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'status 200 OK while fetching Outlook Calendar event lists for for check event exist or not', $debug_log_data, $bookingpress_debug_integration_log_id );
                return 1;
            }
        }

        function bookingpress_schedule_oc_group_appointments( $order_id ){
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

        function bookingpress_force_assign_appointment_to_staff(){
            global $BookingPress, $wpdb, $tbl_bookingpress_settings;

            $get_cron_data = $wpdb->get_results( $wpdb->prepare( "SELECT setting_name,setting_value FROM {$tbl_bookingpress_settings} WHERE setting_name LIKE %s AND setting_type = %s AND setting_value = %d", 'bpa_outlookc_cron_app_id_%', 'bpa_outlookc_cron', 0 )  ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_settings is a table name. false alarm
            if(!empty($get_cron_data)) {
                foreach( $get_cron_data  as $cron_data ){
                    if(!empty($cron_data)) {
                        $setting_name = $cron_data->setting_name;
                        $setting_value = $cron_data->setting_value;
                        $appointment_id = str_replace( 'bpa_outlookc_cron_app_id_', '', $setting_name );
                        $app_data = $BookingPress->bookingpress_get_settings( 'bpa_outlookc_cron_app_data_' . $appointment_id, 'bpa_outlookc_cron' );
                        $appointment_data = json_decode( $app_data, true );
                        $entry_id = isset($appointment_data['entry_id']) ? $appointment_data['entry_id']: '';
                        $payment_gateway_data = isset($appointment_data['payment_gateway_data']) ? $appointment_data['payment_gateway_data'] : '';
                        $BookingPress->bookingpress_update_settings( 'bpa_outlookc_cron_app_id_' . $appointment_id, 'bpa_outlookc_cron', 1 );
                        $this->bookingpress_assign_appointment_to_staff_member( $appointment_id, $entry_id, $payment_gateway_data );
                    }
                }
            }
        }


        function bookingpress_force_assign_appointment_to_staff_legacy(){
            global $BookingPress, $wpdb, $tbl_bookingpress_settings;
            $bookingpress_outlook_scheduler_data = get_option( 'bookingpress_outlook_scheduler_data' );
            if( !empty( $bookingpress_outlook_scheduler_data ) ){
        
                $bookingpress_outlook_scheduler_data = json_decode( $bookingpress_outlook_scheduler_data, true );
                if( !empty( $bookingpress_outlook_scheduler_data ) ){
                    foreach( $bookingpress_outlook_scheduler_data as $appointment_id => $appointment_data ){
                        $entry_id = $appointment_data['entry_id'];
                                
                        $payment_gateway_data = $appointment_data['payment_gateway_data'];
        
                        $counter = isset( $appointment_data['counter'] ) ? $appointment_data['counter'] : 1;
                                
                        $get_cron_flag = $wpdb->get_var( $wpdb->prepare( "SELECT setting_value FROM {$tbl_bookingpress_settings} WHERE setting_name = %s AND setting_type = %s", 'bpa_outlookc_cron_app_id_' . $appointment_id, 'bpa_outlookc_cron' )  ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_settings is a table name. false alarm
        
                        if( empty( $get_cron_flag ) ){ //blank or 0
                            if( 0 === $get_cron_flag || '0' === $get_cron_flag ){
                                $BookingPress->bookingpress_update_settings( 'bpa_outlookc_cron_app_id_' . $appointment_id, 'bpa_outlookc_cron', 1 );
                            } else {
                                $wpdb->insert(
                                    $tbl_bookingpress_settings,
                                    array(
                                        'setting_name' => 'bpa_outlookc_cron_app_id_' . $appointment_id,
                                        'setting_value' => 1,
                                        'setting_type' => 'bpa_outlookc_cron',
                                        'updated_at' => date('Y-m-d H:i:s', current_time('timestamp') )
                                    )
                                );
                            }
                                    
                            $this->bookingpress_assign_appointment_to_staff_member( $appointment_id, $entry_id, $payment_gateway_data, $counter );
                        } else {
                            /** Check if the email notification has been sent or not */
                            $get_email_notification_data = $wpdb->get_results( $wpdb->prepare( "SELECT option_name,option_value FROM " . $wpdb->options . " WHERE option_name LIKE %s", 'bookingpress_oc_send_notification_' . $appointment_id .'_'. $entry_id.'%'), ARRAY_A );
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
                                    update_option( 'bookingpress_oc_send_notification_' . $appointment_id . '_' . $entry_id .'_'. $template_type .'_'.$notification_name .'_is_sent', 1 );
                                }
                            }
                        }
                    }
                }
            }
        }

        function bookingpress_oc_synchronize_staff_calendar_recurring( $event_dates, $event_args, $next_link ){

            $event_response = wp_remote_get($next_link, $event_args);

            if (!is_wp_error($event_response)) {
                $event_body = wp_remote_retrieve_body($event_response);
                $events = json_decode($event_body, true);
                
                if(!empty($events) && isset($events['value'])) {
                    foreach ($events['value'] as $event) {
                        
                        if( 'free' == $event['showAs'] ){
                            continue;
                        }
						$start_datetime = isset($event['start']['dateTime']) ? $event['start']['dateTime'] : '';
                        $start_timezone = isset($event['start']['timeZone']) ? $event['start']['timeZone'] : '';
                        $end_datetime = isset($event['end']['dateTime']) ? $event['end']['dateTime'] : '';
                        $end_timezone = isset($event['end']['timeZone']) ? $event['end']['timeZone'] : '';
                        $event_id = isset($event['id']) ? $event['id'] : '';
                        $current_timezone = wp_timezone_string();

                        if( $event['isAllDay'] ){
                            if( '00:00:00' == date('H:i:s', strtotime( $end_datetime ) ) ) {
                                $start_datetime = date(  DATE_ISO8601, strtotime( $start_datetime ) );
                                $end_datetime = date(  DATE_ISO8601, strtotime( $end_datetime . ' -1 day' ) );
                            }
                            if( $start_datetime == $end_datetime ){

                                $event_dates[ $event_id ]  = array(
                                    'timezone' => $current_timezone,
                                    'start_date' => date('Y-m-d H:i:s', strtotime( $start_datetime ) ),
                                    'end_date' => date('Y-m-d', strtotime( $end_datetime ) ) . ' 23:59:59'
                                );

                            } else {

                                $start_dt = new DateTime( $start_datetime, new DateTimeZone( $start_timezone ) );
                                $start_dt->setTimeZone( new DateTimeZone( $current_timezone ) );
                                $start_time = $start_dt->format( 'Y-m-d');
                                
                                $end_dt = new DateTime( $end_datetime, new DateTimeZone( $start_timezone ) );
                                $end_dt->setTimeZone( new DateTimeZone( $current_timezone ) );
                                $end_time = $end_dt->format( 'Y-m-d' ) . ' 23:59:59';

                                $start_time_obj = new DateTime( $start_time );
                                $end_time_obj = new DateTime( $end_time );

                                $pd_interval = DateInterval::createFromDateString('1 day');
                                $pd = new DatePeriod( $start_time_obj, $pd_interval, $end_time_obj );

                                foreach( $pd as $pdt ){
                                    $event_dates[ $event_id ] = array(
                                        'timezone' => $current_timezone,
                                        'start_date' => date('Y-m-d H:i:s', strtotime( $start_time ) ),
                                        'end_date' => date('Y-m-d H:i:s', strtotime( $end_time ) ),
                                    );
                                }
                            }
                        } else {
            
                            $start_dt = new DateTime( $start_datetime, new DateTimeZone( $start_timezone ) );
                            $start_dt->setTimeZone( new DateTimeZone( $current_timezone ) );
                            $start_time = $start_dt->format( 'Y-m-d H:i:s');   
                            $end_dt = new DateTime( $end_datetime, new DateTimeZone( $end_timezone ) );
                            $end_dt->setTimeZone( new DateTimeZone( $current_timezone ) );
                            $end_time = $end_dt->format( 'Y-m-d H:i:s' );
                            
                            /** Add one second in start time to prevent blocking the previous time slot  */
                            $start_time_str = strtotime( $start_time );
                            
                            /** Substract one second in end time to prevent blocking the next time slot  */
                            $end_time_str = strtotime( $end_time );     
            
                            $event_dates[ $event_id ] = array(
                                'timezone' => $current_timezone,
                                'start_date' => date('Y-m-d H:i:s', $start_time_str ),
                                'end_date' => date('Y-m-d H:i:s', $end_time_str ),
                            );

                        }

                    }
                    if( !empty( $events['@odata.nextLink'] ) ){
                        $event_dates = $this->bookingpress_oc_synchronize_staff_calendar_recurring( $event_dates, $event_args, $events['@odata.nextLink'] );
                    }
                }
            }

            return $event_dates;

        }

        function bookingpress_sycnrhonize_outlook_events(){
            
            if( !empty( $_REQUEST['bpa_action'] ) && 'bpa_oc_event_sync' == $_REQUEST['bpa_action'] ) {

                global $bookingpress_pro_staff_members;
				
				if( !empty( $_REQUEST['validationToken'] ) ){
                    header('Content-Type: text/plain');
					echo $_REQUEST['validationToken'];
					status_header( 200 );
					die;
                }

                $request_body = file_get_contents('php://input');

                $oc_event_arr = json_decode( $request_body, true );

                $oc_event_data = $oc_event_arr['value'];

                $client_states = array();

                foreach( $oc_event_data as $sub_evnt_data ){
                    $clientState = $sub_evnt_data['clientState'];

                    if( preg_match( '/^(bpa-staff-id-[\d+])$/', $clientState ) && !in_array( $clientState, $client_states ) ){
                        $client_states[ $sub_evnt_data['subscriptionId'] ] = $clientState;
                    }
                }

                if( !empty( $client_states ) ){
                    
                    $processed_staff_id = array();

                    foreach( $client_states as $subscription_id => $client_state_data ){
                        $staffmember_id = preg_replace( '/(bpa-staff-id-)/','', $client_state_data );

                        if( !empty( $staffmember_id ) && ( empty( $processed_staff_id ) || !in_array( $staffmember_id, $processed_staff_id ) ) ){
                            $staff_subscription_details = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staffmember_id, 'bookingpress_oc_subscription_details' );//
                            $staff_subscription_details = !empty( $staff_subscription_details ) ? json_decode( $staff_subscription_details, true ) : array();

                            if( !empty( $staff_subscription_details ) && $staff_subscription_details['id'] == $subscription_id ){
                                $processed_staff_id[] = $staffmember_id;
                                $this->bookingpress_oc_synchronize_staff_calendar( $staffmember_id );
                            }
                        }
                    }
                }

                
                
            }

        }

        function bookingpress_oc_synchronize_staff_calendar($bookingpress_staffmember_id){

            global $wpdb, $BookingPress, $bookingpress_pro_staff_members, $bookingpress_pro_appointment_bookings, $bookingpress_global_options, $bookingpress_debug_integration_log_id;
            if( empty( $bookingpress_staffmember_id ) ){
                return;
            }

            $bookingpress_enable_outlook_calendar_tmp = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_enable_outlook_calendar' );
            $bookingpress_enable_outlook_calendar = ($bookingpress_enable_outlook_calendar_tmp == "true") ? true : false;

            if( true == $bookingpress_enable_outlook_calendar ){
                
                $calendarId = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_selected_ocalendar' );

                $staffmember_cal_oauth_data = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_ocalendar_oauthdata' );
                $staff_member_oauth_data = json_decode( $staffmember_cal_oauth_data, true );
                
                /* From current time to get event of next max day settings of general settings */
                $bookingpress_max_days_for_booking  = $BookingPress->bookingpress_get_settings( 'period_available_for_booking', 'general_setting' );
                $current_date = date('Y-m-d', current_time('timestamp'));
                $end_date = date( 'Y-m-d', strtotime('+' . $bookingpress_max_days_for_booking . ' days') );

                $iso_date_start = date('Y-m-d', current_time('timestamp')) . 'T00:00:00';
                $iso_date_end = date('Y-m-d',  strtotime($end_date)) . 'T00:00:00';
                
                $get_events_url = 'https://graph.microsoft.com/v1.0/me/calendars/'.$calendarId.'/calendarView';
                $get_events_url .= '?startDateTime='.$iso_date_start.'&endDateTime=' . $iso_date_end;

                $staff_token_data = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_ocalendar_oauthdata');   
                $staff_token_data = json_decode( $staff_token_data );
                $staff_access_token = $staff_token_data->access_token;            
    
                if( current_time( 'timestamp' ) > ( $staff_token_data->created + $staff_token_data->expires_in ) ){
                   $staff_access_token = $this->bookingpress_refresh_access_token( $staff_token_data, $bookingpress_staffmember_id );
                }

                $event_args = array(
                    'headers' => array(
                        'Authorization' => 'Bearer ' . $staff_access_token,
                    ),
                );
               
                $event_response = wp_remote_get($get_events_url, $event_args);

                $event_dates = array();
                $staff_oc_event_data = array(
                    $calendarId => array()
                );

                if (!is_wp_error($event_response)) {
                    $event_body = wp_remote_retrieve_body($event_response);
                    $events = json_decode($event_body, true);
                    
                    if(!empty($events) && isset($events['value'])) {
                        foreach ($events['value'] as $event) {

                            if( 'free' == $event['showAs'] ){
                                continue;
                            }

                            $start_datetime = isset($event['start']['dateTime']) ? $event['start']['dateTime'] : '';
                            $start_timezone = isset($event['start']['timeZone']) ? $event['start']['timeZone'] : '';
                            $end_datetime = isset($event['end']['dateTime']) ? $event['end']['dateTime'] : '';
                            $end_timezone = isset($event['end']['timeZone']) ? $event['end']['timeZone'] : '';
                            $event_id = isset($event['id']) ? $event['id'] : '';
                            $current_timezone = wp_timezone_string();

                            if( $event['isAllDay'] ){
                                
                                if( '00:00:00' == date('H:i:s', strtotime( $end_datetime ) ) ) {
                                    $start_datetime = date(  DATE_ISO8601, strtotime( $start_datetime ) );
                                    $end_datetime = date(  DATE_ISO8601, strtotime( $end_datetime . ' -1 day' ) );
                                }
                                if( $start_datetime == $end_datetime ){
                                    $event_dates[ $event_id ]  = array(
                                        'timezone' => $current_timezone,
                                        'start_date' => date('Y-m-d H:i:s', strtotime( $start_datetime ) ),
                                        'end_date' => date('Y-m-d', strtotime( $end_datetime ) ) . ' 23:59:59'
                                    );

                                } else {

                                    $start_dt = new DateTime( $start_datetime, new DateTimeZone( $start_timezone ) );
                                    $start_dt->setTimeZone( new DateTimeZone( $current_timezone ) );
                                    $start_time = $start_dt->format( 'Y-m-d');
                                    
                                    $end_dt = new DateTime( $end_datetime, new DateTimeZone( $start_timezone ) );
                                    $end_dt->setTimeZone( new DateTimeZone( $current_timezone ) );
                                    $end_time = $end_dt->format( 'Y-m-d' ) . ' 23:59:59';

                                    $start_time_obj = new DateTime( $start_time );
                                    $end_time_obj = new DateTime( $end_time );

                                    $pd_interval = DateInterval::createFromDateString('1 day');
                                    $pd = new DatePeriod( $start_time_obj, $pd_interval, $end_time_obj );

                                    foreach( $pd as $pdt ){
                                        $event_dates[ $event_id ] = array(
                                            'timezone' => $current_timezone,
                                            'start_date' => date('Y-m-d H:i:s', strtotime( $start_time ) ),
                                            'end_date' => date('Y-m-d H:i:s', strtotime( $end_time ) ),
                                        );
                                    }
                                }
                            } else {
                
                                $start_dt = new DateTime( $start_datetime, new DateTimeZone( $start_timezone ) );
                                $start_dt->setTimeZone( new DateTimeZone( $current_timezone ) );
                                $start_time = $start_dt->format( 'Y-m-d H:i:s');   
                                $end_dt = new DateTime( $end_datetime, new DateTimeZone( $end_timezone ) );
                                $end_dt->setTimeZone( new DateTimeZone( $current_timezone ) );
                                $end_time = $end_dt->format( 'Y-m-d H:i:s' );
                                
                                /** Add one second in start time to prevent blocking the previous time slot  */
                                $start_time_str = strtotime( $start_time );
                                
                                /** Substract one second in end time to prevent blocking the next time slot  */
                                $end_time_str = strtotime( $end_time );     
                
                                $event_dates[ $event_id ] = array(
                                    'timezone' => $current_timezone,
                                    'start_date' => date('Y-m-d H:i:s', $start_time_str ),
                                    'end_date' => date('Y-m-d H:i:s', $end_time_str ),
                                );
                            }


                        }
                        if( !empty( $events['@odata.nextLink'] ) ){
                            $event_dates = $this->bookingpress_oc_synchronize_staff_calendar_recurring( $event_dates, $event_args, $events['@odata.nextLink'] );
                        }
                    }


                    $staff_oc_event_data[ $calendarId ] = $event_dates;
                    $bookingpress_pro_staff_members->update_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_staff_oc_events', json_encode( $staff_oc_event_data ) );

                    $bpa_fetch_expiration = current_time( 'timestamp' ) + ( MINUTE_IN_SECONDS * 5 );
                    $bookingpress_pro_staff_members->update_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_staff_oc_event_fetch_expiration_time', $bpa_fetch_expiration );

                    
                } else {
                   // Handle the error
                   $debug_log_data = array(
                        'event_response' => $event_response,
                        'outlook_calendar_log_placement' => 'failed fetch event data',
                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                    );
                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', 'Outlook Calendar Error in event fetch data', 'Outlook Calendar Integration Event Fetch Error', 'Outlook Failed to fetch event data', $debug_log_data, $bookingpress_debug_integration_log_id );
                }
            }

            $this->bookingpress_outlook_calendar_subscribe_events( $bookingpress_staffmember_id );

        }

        function bookingpress_outlook_calendar_subscribe_events( $bookingpress_staffmember_id ){

            global $bookingpress_pro_staff_members, $bookingpress_pro_appointment_bookings, $bookingpress_global_options, $bookingpress_debug_integration_log_id;

            /** Check if the Outlook Subscription is created & check the expiration time */

            $staff_oc_watch = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_oc_subscription_details' );
            $staff_oc_expiration = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_oc_subscription_expiry' );
			global $BookingPress;
			
			
            if( empty( $staff_oc_watch ) || ( !empty( $staff_oc_expiration ) && current_time('timestamp') > $staff_oc_expiration ) ){

                $calendarId = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_selected_ocalendar' );

                $staffmember_cal_oauth_data = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_ocalendar_oauthdata' );
                $staff_member_oauth_data = json_decode( $staffmember_cal_oauth_data, true );
                $staff_access_token = $staff_member_oauth_data['access_token'];
                
                $webhook_address = get_home_url() . '/?bpa_action=bpa_oc_event_sync';

                /** Expiry can be set to maximum 6 days */
                $oc_watch_expiry = current_time('timestamp') + (60 * 60 * 24 * 6);

                $oc_watch_params = array(
                    'notificationUrl' => $webhook_address,
                    'resource' => 'me/calendars/'.$calendarId.'/events',
                    'changeType' => 'created,updated,deleted',
                    'expirationDateTime' => date('c',$oc_watch_expiry),
                    'clientState' => 'bpa-staff-id-' . $bookingpress_staffmember_id,
                    'latestSupportedTlsVersion' => 'v1_2',
                );
				
                $arguments = array(
                    'timeout' => 4500,
                    'sslverify' => true,
                    'headers' => array(
                        'Authorization' => 'Bearer ' . $staff_access_token,
                        'Content-Type' => 'application/json'
                    ),
                    'body' => json_encode( $oc_watch_params )
                );

                $oc_subscription_url = 'https://graph.microsoft.com/v1.0/subscriptions';

                $api_call = wp_remote_post( $oc_subscription_url, $arguments );
				
                if( !is_wp_error( $api_call) ){
                    $response = wp_remote_retrieve_body( $api_call );

                    $response_arr = json_decode( $response, true );

                    if( empty( $response_arr['id']) ){
                        /** Error handling if the subscription to event is not created successfully */
                    } else {
                        $bookingpress_pro_staff_members->update_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_oc_subscription_details', $response );
                        $bpa_oc_expiry_utc = $response_arr['expirationDateTime'];
                        $wp_timezone_offset = $bookingpress_global_options->bookingpress_get_site_timezone_offset();
                        $oc_utc_to_wp_timezone_date = $bookingpress_pro_appointment_bookings->bookingpress_appointment_change_to_client_timezone_func( $bpa_oc_expiry_utc, $wp_timezone_offset );
                        $oc_timestring = strtotime( $oc_utc_to_wp_timezone_date );
                        $bookingpress_pro_staff_members->update_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_oc_subscription_expiry', $oc_timestring );
                    }
                } else {
                    /** error handling for subscription creation error */
                    $debug_log_data = array(
                        'subscription_response' => $api_call,
                        'outlook_calendar_log_placement' => 'failed to create subscription',
                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                    );
                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', 'Outlook Calendar Subscription Creation Error', 'Outlook Calendar Integration Subsciprion Error', 'Outlook calendar subscription creation failed', $debug_log_data, $bookingpress_debug_integration_log_id );
                }

            }
        }

        function bookingpress_retrieve_outlook_calendar_events( $staff_member_id, $calendarId, $selected_date, $check_for_time = false ){

            global $BookingPress, $bookingpress_pro_staff_members;

            $return_data = array();

            if( empty( $staff_member_id ) ){
                return $return_data;
            }

            $calendar_event_data = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_staff_oc_events' );
            $calendar_event_fetch_date = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_staff_oc_event_fetch_expiration_time' );

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
                $this->bookingpress_oc_synchronize_staff_calendar( $staff_member_id );
                $calendar_event_data = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_staff_oc_events' );
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

        /* Function that will check for the event in the outllook cal via cron*/
        function bookingpress_assign_appointment_to_staff_member_with_cron_oc_cal($appointment_id, $entry_id = '', $payment_gateway_data = array()) {
            global $BookingPress, $wpdb, $tbl_bookingpress_settings, $bookingpress_debug_integration_log_id;

            if( empty( $appointment_id ) ){
                return;
            }

            $counter = get_option( 'bpa_oc_cron_counter_'. $appointment_id );

            if( !isset( $counter ) || empty( $counter ) ){
                $counter = 1;
            }

            if( $counter > 3 ){
                $debug_log_data = json_encode( func_get_args() );
                do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', 'Outlook Calendar Event Creation', 'Outlook Calendar Appointment Syncrhonization attempt 3 times', 'Outlook Calendar Stopped attempting Outlook Calendar Appointment Synchronization after 3 attempt.', $debug_log_data, $bookingpress_debug_integration_log_id );
                return;
            }

            $get_flag = $wpdb->get_var( $wpdb->prepare( "SELECT setting_value FROM {$tbl_bookingpress_settings} WHERE setting_name = %s AND setting_type = %s", 'bpa_outlookc_cron_app_id_' . $appointment_id, 'bpa_outlookc_cron' )  ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_settings is a table name. false alarm

            if( empty( $get_flag ) ){ //blank or 0
                if( 0 === $get_flag || '0' === $get_flag){
                    $BookingPress->bookingpress_update_settings( 'bpa_outlookc_cron_app_id_' . $appointment_id, 'bpa_outlookc_cron', 1 );
                } else {
                    $wpdb->insert(
                        $tbl_bookingpress_settings,
                        array(
                            'setting_name' => 'bpa_outlookc_cron_app_id_' . $appointment_id,
                            'setting_value' => 1,
                            'setting_type' => 'bpa_outlookc_cron',
                            'updated_at' => date('Y-m-d H:i:s', current_time('timestamp') )
                        )
                    );
                }

                $this->bookingpress_assign_appointment_to_staff_member( $appointment_id, $entry_id, $payment_gateway_data, $counter );
            }
        }
        function bookingpress_schedule_googleoutlook_event( $appointment_id, $entry_id = '', $payment_gateway_data = array() ){
            global $BookingPress, $bookingpress_pro_staff_members, $tbl_bookingpress_appointment_bookings, $wpdb;

            //apply filter for the waiting list addon
            $bookingpress_check_status = apply_filters( 'bookingpress_check_status_for_appointment_integration', false, $appointment_id, '', ''); 
            $backtrace = wp_debug_backtrace_summary( null, 0, true );
            if( !preg_match( '/bookingpress_book_front_appointment_func/', $backtrace ) ){
                return;
            }
            /** Insert value as 0 */
            $BookingPress->bookingpress_update_settings( 'bpa_outlookc_cron_app_id_' . $appointment_id, 'bpa_outlookc_cron', 0 );
            $arguments = array(
                'entry_id' => $entry_id,
                'payment_gateway_data' => $payment_gateway_data
            );
            $BookingPress->bookingpress_update_settings( 'bpa_outlookc_cron_app_data_' . $appointment_id, 'bpa_outlookc_cron', json_encode( $arguments ) );

            wp_schedule_single_event( strtotime('+5 seconds'), 'bookingpress_schedule_staffmember_oc_event', array( $appointment_id, $entry_id, $payment_gateway_data ) );
        }

        function bookingpress_assign_appointment_to_staff_member_from_admin( $appointment_id, $entry_id = '', $payment_gateway_data = array() ){

            $backtrace = wp_debug_backtrace_summary( null, 0, true );

            if( preg_match( '/bookingpress_book_front_appointment_func/', $backtrace ) ){
                return;
            } else {
                $this->bookingpress_assign_appointment_to_staff_member( $appointment_id, $entry_id, $payment_gateway_data, 0 );
            }

        }

        function bookingpress_assign_appointment_to_staff_member( $appointment_id, $entry_id = '', $payment_gateway_data = array() ){

            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $bookingpress_pro_staff_members, $tbl_bookingpress_entries, $bookingpress_pro_appointment_bookings,$tbl_bookingpress_staffmembers_services,$bookingpress_debug_integration_log_id;

            if( empty( $appointment_id ) ){
                return;
            }

            $is_staffmember_activated = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();
            if( !$is_staffmember_activated ){
                $debug_log_data = json_encode( array( 'staff_module_status' => $is_staffmember_activated ) );
                do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', 'Outlook Calendar Event Creation', 'BookingPress OC - Staffmember Module deactivated', 'Staff Member Module is deactivated while processing event', $debug_log_data, $bookingpress_debug_integration_log_id );
                $BookingPress->bookingpress_update_settings( 'bpa_outlookc_cron_app_id_' . $appointment_id, 'bpa_outlookc_cron', 0 );
                return;
            }
            
            $bookingpress_check_status = apply_filters( 'bookingpress_check_status_for_appointment_integration', false, $appointment_id, '', ''); 
            if($bookingpress_check_status){
                $debug_log_data = json_encode( array( 'status_from_filter' => $bookingpress_check_status, 'filter_name' => 'bookingpress_check_status_for_appointment_integration' ) );
                do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', 'Outllok Calendar Event Creation', 'BookingPress OC - Waiting List', 'Appointment is in waiting list', $debug_log_data, $bookingpress_debug_integration_log_id );
                $BookingPress->bookingpress_update_settings( 'bpa_outlookc_cron_app_id_' . $appointment_id, 'bpa_outlookc_cron', 0 );
                return;
            }

            $appointment_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $appointment_id ), ARRAY_A );
            
            $service_id = !empty($appointment_data['bookingpress_service_id']) ? intval($appointment_data['bookingpress_service_id']) : 0;
            $staff_member_id = !empty($appointment_data['bookingpress_staff_member_id']) ? intval($appointment_data['bookingpress_staff_member_id']) : 0;
            $bookingpress_entry_id = !empty($appointment_data['bookingpress_entry_id']) ? intval($appointment_data['bookingpress_entry_id']) : 0;            

            if( empty( $appointment_data ) || empty( $service_id ) || empty( $staff_member_id ) || empty( $bookingpress_entry_id ) ){
                $debug_log_data = json_encode( array( 'appointment_data' => $appointment_data, 'staff_id' => $staff_member_id, 'service_id' => $service_id  ) );
                do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', 'Outlook Calendar Event Creation', 'BookingPress OC - Empty data passed', 'Some of the data is empty', $debug_log_data, $bookingpress_debug_integration_log_id );
                $BookingPress->bookingpress_update_settings( 'bpa_outlookc_cron_app_id_' . $appointment_id, 'bpa_outlookc_cron', 0 );
                return;
            }                               

            $calendarId = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_selected_ocalendar' );
            if( empty( $calendarId ) ){
                $debug_log_data = json_encode( array( 'calendarID' => $calendarId, 'staff_id' => $staff_member_id ) );
                do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', 'Outlook Calendar Event Creation', 'BookingPress OC - Empty Calendar ID', 'Calendar not selected', $debug_log_data, $bookingpress_debug_integration_log_id );
                $BookingPress->bookingpress_update_settings( 'bpa_outlookc_cron_app_id_' . $appointment_id, 'bpa_outlookc_cron', 0 );
                return;
            }

            $staff_token_data = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_ocalendar_oauthdata');            
            if( empty( $staff_token_data ) ){
                $debug_log_data = json_encode( array( 'staff_token_data' => $staff_token_data, 'staff_id' => $staff_member_id ) );
                do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', 'Outlook Calendar Event Creation', 'BookingPress OC - Staff token data', 'Staff Token Data', $debug_log_data, $bookingpress_debug_integration_log_id );
                $BookingPress->bookingpress_update_settings( 'bpa_outlookc_cron_app_id_' . $appointment_id, 'bpa_outlookc_cron', 0 );
                return;
            }

            $bookingpress_enable_outlook_calendar = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_enable_outlook_calendar');
            if(empty($bookingpress_enable_outlook_calendar) || $bookingpress_enable_outlook_calendar == 'false' ) {
                $debug_log_data = json_encode( array( 'bookingpress_enable_outlook_calendar' => $bookingpress_enable_outlook_calendar, 'staff_id' => $staff_member_id ) );
                do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', 'Outlook Calendar Event Creation', 'BookingPress OC - Calendar Option is not activated', 'Calendar not Activated', $debug_log_data, $bookingpress_debug_integration_log_id );
                $BookingPress->bookingpress_update_settings( 'bpa_outlookc_cron_app_id_' . $appointment_id, 'bpa_outlookc_cron', 0 );
                return; 
            }
            $appointment_status = esc_html($appointment_data['bookingpress_appointment_status']);
            if($appointment_status == '3' || $appointment_status == '4' || $appointment_status == '5' || $appointment_status == '6' ) {
                $debug_log_data = json_encode( array( 'appointment_status' => $appointment_status, 'staff_id' => $staff_member_id ) );
                do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', 'Outlook Calendar Event Creation', 'BookingPress OC - Appointment is neither pending nor approved', 'Appointment could not be synchronized due to status', $debug_log_data, $bookingpress_debug_integration_log_id );
                $BookingPress->bookingpress_update_settings( 'bpa_outlookc_cron_app_id_' . $appointment_id, 'bpa_outlookc_cron', 0 );
                return;
            }                

            $staff_token_data = json_decode( $staff_token_data );
            $staff_access_token = $staff_token_data->access_token;            

            if( current_time( 'timestamp' ) > ( $staff_token_data->created + $staff_token_data->expires_in ) ){
                $staff_access_token = $this->bookingpress_refresh_access_token( $staff_token_data, $staff_member_id );
            }
            $bookingpress_appointment_date = $bookingpress_appointment_end_date = esc_html( $appointment_data['bookingpress_appointment_date'] );
            $bookingpress_start_time = esc_html( $appointment_data['bookingpress_appointment_time'] );
            $bookingpress_end_time   = esc_html( $appointment_data['bookingpress_appointment_end_time'] );
            $service_duration = esc_html( $appointment_data['bookingpress_service_duration_val'] );
            $bookingpress_duration_unit = esc_html( $appointment_data['bookingpress_service_duration_unit'] );

            if($bookingpress_duration_unit == 'd' && !empty($service_duration) && $service_duration > 1) { 
                $service_duration-- ;
                $bookingpress_appointment_end_date = date('Y-m-d', strtotime($service_duration.' day',strtotime($bookingpress_appointment_date)));
            }
            $bookingpress_start_date_time = date('Y-m-d',strtotime($bookingpress_appointment_date)).'T'.date('H:i:s',strtotime($bookingpress_start_time));
            $bookingpress_end_date_time   = date('Y-m-d',strtotime($bookingpress_appointment_end_date)).'T'.date('H:i:s',strtotime($bookingpress_end_time));

            if($bookingpress_duration_unit == 'd') {
                $bookingpress_start_date_time = date('Y-m-d',strtotime($bookingpress_appointment_date)).'T 00:00:00';
                $bookingpress_end_date_time   = date('Y-m-d',strtotime($bookingpress_appointment_end_date)).'T 23:59:59';
            } else {                                
                if($bookingpress_end_time == '24:00:00') {
                    $bookingpress_end_date_time = date('Y-m-d', strtotime($bookingpress_appointment_date . '+1 days')).'T00:00:00';
                }
            }

            $bookingpress_addon_popup_field_form = $this->bookingpress_get_outlook_calendar_credentials();
            $bookingpress_client_id =  $bookingpress_addon_popup_field_form['outlook_calendar_client_id'];
            $bookingpress_client_secret = $bookingpress_addon_popup_field_form['outlook_calendar_client_secret'];
            $user_timezone = wp_timezone_string();
            if( '+00:00' == $user_timezone ){
                $user_timezone = 'UTC';
            } else {
                $user_timezone = $bookingpress_pro_appointment_bookings->bookingpress_convert_offset_to_string( $user_timezone );
            }

            $already_booked_appointment = $wpdb->get_results( $wpdb->prepare( "SELECT bpa.* FROM {$tbl_bookingpress_appointment_bookings} bpa LEFT JOIN {$tbl_bookingpress_staffmembers_services} bps ON bpa.bookingpress_staff_member_id=bps.bookingpress_staffmember_id WHERE bpa.bookingpress_service_id = %d AND bpa.bookingpress_appointment_status != %d AND bpa.bookingpress_appointment_status != %d AND bpa.bookingpress_staff_member_id = %d AND bpa.bookingpress_appointment_date = %s AND bpa.bookingpress_appointment_time = %s AND bpa.bookingpress_appointment_booking_id != %d AND bps.bookingpress_service_capacity > %d AND bpa.bookingpress_outlook_calendar_event_id != '' ", $service_id, 3, 4, $staff_member_id, $bookingpress_appointment_date, $bookingpress_start_time, $appointment_id, 1),ARRAY_A);         

            $bookingpress_event_id = '';
            if(!empty($already_booked_appointment)) {
                foreach($already_booked_appointment as $key => $val) {
                    $bookingpress_outlook_calendar_event_id = !empty($val['bookingpress_outlook_calendar_event_id']) ? esc_html($val['bookingpress_outlook_calendar_event_id']) : '';
                    $bookingpress_appointment_booking_id = !empty($val['bookingpress_appointment_booking_id']) ? esc_html($val['bookingpress_appointment_booking_id']) : '';
                    if(!empty($bookingpress_outlook_calendar_event_id)) {
                        $bookignpress_is_metting_exist=$this->bookignpress_is_outlook_event_exist($calendarId,$bookingpress_outlook_calendar_event_id,$staff_access_token);

                        if( !empty($bookignpress_is_metting_exist) && $bookignpress_is_metting_exist == 1 ){
                            $bookingpress_event_id = $bookingpress_outlook_calendar_event_id;

                        } elseif($bookignpress_is_metting_exist == 0) {
                            $wpdb->update(
                                $tbl_bookingpress_appointment_bookings,
                                array(
                                    'bookingpress_outlook_calendar_event_id' => '',
                                ),
                                array(
                                    'bookingpress_appointment_booking_id' => $bookingpress_appointment_booking_id
                                )
                            );
                        }
                    }
                }
            }         

            $bookingpress_event_title = $this->bookingpress_outlook_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['outlook_calendar_event_title'], $appointment_data );
            $bookingpress_event_description = $this->bookingpress_outlook_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['outlook_calendar_event_description'], $appointment_data );
            $bookingpress_event_location = $this->bookingpress_retrieve_location_field_data( $bookingpress_addon_popup_field_form['outlook_calendar_event_location'], $appointment_data );

            if(!empty( $bookingpress_event_id )) {

                $event_data = array(
                    'subject' => $bookingpress_event_title,
                    'body' => array(
                        'contentType' => 'HTML',
                        'content' => $bookingpress_event_description
                    ),
                );
                if( !empty( $bookingpress_event_location ) ){
                    $event_data['location'] = array(
                        'displayName' => $bookingpress_event_location,
                        'address' => array(
                            'street' => $bookingpress_event_location
                        )
                    );
                }
                $args = array(
                    'timeout' => 5000,
                    'headers' => array(
                        'Authorization' => 'Bearer '.$staff_access_token,
                        'Content-Type'  => 'application/json'
                    ),
                    'body' => json_encode( $event_data )
                );

                $args['method'] = 'PATCH';
                $outlook_event_url = 'https://graph.microsoft.com/v1.0/me/calendar/events/'.$bookingpress_event_id; 
                $response = wp_remote_post( $outlook_event_url, $args );

                $wpdb->update(
                    $tbl_bookingpress_appointment_bookings,
                    array(
                        'bookingpress_outlook_calendar_event_id' => $bookingpress_event_id,
                    ),
                    array(
                        'bookingpress_appointment_booking_id' => $appointment_id
                    )
                );
                if( is_wp_error( $response ) ){
                    $debug_log_data = array(
                        'outlook_calendar_message' => $response->get_error_message(),
                        'outlook_calendar_log_placement' => 'error while updating event on Outlook calendar',
                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                    );
                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'Outlook Calendar event updation failed', $debug_log_data, $bookingpress_debug_integration_log_id );
                    return;
                }

            } else {

                /** Check if the appointment id has already linked with another Outlook Calendar ID */
                $get_event_id = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_outlook_calendar_event_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d" , $appointment_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally.

                if( !empty( $get_event_id ) && !empty( $get_event_id->bookingpress_outlook_calendar_event_id ) ){
                    /** If appointment contains the Outlook Calendar Event ID then remove the appointment id from the options and locked data */
                    $debug_log_data = wp_json_encode(
                        array(
                            'appointment_id' => $appointment_id,
                            'existing_event_id_in_db' => $get_event_id->bookingpress_outlook_calendar_event_id,
                            'backtrace' => wp_debug_backtrace_summary( null, 0, false )
                        )
                    );
                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', 'Duplicate Event Execution', 'Duplicate Event Execution', 'Duplicate Event Execution', $debug_log_data, $bookingpress_debug_integration_log_id );

                    /** Remove Appointment ID & Data from the schedular details */
		            $bookingpress_oc_cron_app_data_data = $BookingPress->bookingpress_get_settings( 'bpa_outlookc_cron_app_data_' . $appointment_id, 'bpa_outlookc_cron' );
                    $bookingpress_oc_cron_app_data_data = json_decode( $bookingpress_oc_cron_app_data_data, true );
                    if( !empty( $bookingpress_oc_cron_app_data_data ) ){
                        $bpa_schedular_data = json_encode( $bookingpress_oc_cron_app_data_data ) .' at ' . date('Y-m-d H:i:s', current_time('timestamp') ) . ' from duplicate execution';
                        do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', 'Duplicate Event Execution Schedular Data', 'Duplicate Event Execution Schedular Data', 'Duplicate Event Execution Schedular Data - ' . $appointment_id, $bpa_schedular_data, $bookingpress_debug_integration_log_id );
                        //$BookingPress->bookingpress_update_settings( 'bpa_outlookc_cron_app_data_' . $appointment_id, 'bpa_outlookc_cron', '' );
                    }
                    /** Return from here and stop synchronization process */
                    return;
                }
                $event_data = array(
                    'subject' => $bookingpress_event_title,
                    'body' => array(
                        'contentType' => 'HTML',
                        'content' => $bookingpress_event_description
                    ),
                    'start' => array(
                        'dateTime' => $bookingpress_start_date_time,
                        'timeZone' => $user_timezone
                    ),
                    'end' => array(
                        'dateTime' => $bookingpress_end_date_time,
                        'timeZone' => $user_timezone
                    )
                );

                if( !empty( $bookingpress_event_location ) ){
                    $event_data['location'] = array(
                        'displayName' => $bookingpress_event_location,
                        'address' => array(
                            'street' => $bookingpress_event_location
                        )
                    );
                }
    
                $args = array(
                    'timeout' => 5000,
                    'headers' => array(
                        'Authorization' => 'Bearer '.$staff_access_token,
                        'Content-Type'  => 'application/json'
                    ),
                    'body' => json_encode( $event_data )
                );

                $outlook_event_url = 'https://graph.microsoft.com/v1.0/me/calendars/' . $calendarId . '/events';
                $args['method'] = 'POST';     
                
                $response = wp_remote_post( $outlook_event_url, $args );

                if( is_wp_error( $response ) ){
                    $debug_log_data = array(
                        'outlook_calendar_message' => $response->get_error_message(),
                        'outlook_calendar_log_placement' => 'error while creating event on Outlook calendar',
                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                    );
                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'Outlook Calendar event creation failed', $debug_log_data, $bookingpress_debug_integration_log_id );

                    if( !isset( $counter ) ){
                        $counter  = 1;
                    }
                    $counter++;

                    $counter = update_option( 'bpa_oc_cron_counter_'. $appointment_id, $counter );
                    $next_occurence_time = current_time( 'timestamp' ) + ( 10 * 60 );

                    wp_schedule_single_event( $next_occurence_time, 'bookingpress_schedule_staffmember_oc_event', array( $appointment_id, $entry_id, $payment_gateway_data, $counter ) );

                    $debug_log_data = array(
                        'appointment_id' => $appointment_id,
                        'entry_id' => $entry_id,
                        'counter' => $counter
                    );
                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', 'Outlook Calendar Integration', 'Outlook Calendar - Appointment ID ' . $appointment_id . ' set for re-attempt('.$counter.') at '. date('Y-m-d H:i:s', $next_occurence_time ), 'Creating Outlook Calendar Event', $debug_log_data, $bookingpress_debug_integration_log_id );

                    $BookingPress->bookingpress_update_settings( 'bpa_outlookc_cron_app_id_' . $appointment_id, 'bpa_outlookc_cron', 0 );

                    return;
                }

                if( 201 != $response['response']['code'] ) {
                    $debug_log_data = array(
                        'outlook_calendar_message' => $response,
                        'outlook_calendar_log_placement' => 'status not 201 OK while creating event on Outlook calendar',
                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                    );
                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'Outlook Calendar event creation failed with status is not 200 OK', $debug_log_data, $bookingpress_debug_integration_log_id );

                    if( !isset( $counter ) ){
                        $counter  = 1;
                    }
                    $counter++;

                    $counter = update_option( 'bpa_oc_cron_counter_'. $appointment_id, $counter );
                    $next_occurence_time = current_time( 'timestamp' ) + ( 10 * 60 );

                    wp_schedule_single_event( $next_occurence_time, 'bookingpress_schedule_staffmember_oc_event', array( $appointment_id, $entry_id, $payment_gateway_data, $counter ) );

                    $debug_log_data = array(
                        'appointment_id' => $appointment_id,
                        'entry_id' => $entry_id,
                        'counter' => $counter
                    );
                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', 'Outlook Calendar Integration', 'Outlook Calendar - Appointment ID ' . $appointment_id . ' set for re-attempt('.$counter.') at '. date('Y-m-d H:i:s', $next_occurence_time ), 'Creating Outlook Calendar Event', $debug_log_data, $bookingpress_debug_integration_log_id );

                    $BookingPress->bookingpress_update_settings( 'bpa_outlookc_cron_app_id_' . $appointment_id, 'bpa_outlookc_cron', 0 );

                    return;

                } else {
                    $debug_log_data = array(
                        'outlook_calendar_message' => $response,
                        'outlook_calendar_log_placement' => 'status 201 OK while creating event on Outlook calendar',
                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                    );
                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'Outlook Calendar event creation with status is 200 OK', $debug_log_data, $bookingpress_debug_integration_log_id );
                }

                $resp_body = json_decode( $response['body'] );
                $appointment_event_id = $resp_body->id;

                $wpdb->update(
                    $tbl_bookingpress_appointment_bookings,
                    array(
                        'bookingpress_outlook_calendar_event_id' => $appointment_event_id
                    ),
                    array(
                        'bookingpress_appointment_booking_id' => $appointment_id
                    )
                );

                /** Update settings to value 2 as finished */
                $BookingPress->bookingpress_update_settings( 'bpa_outlookc_cron_app_id_' . $appointment_id, 'bpa_outlookc_cron', 2 );
                $this->bookingpress_outlook_calendar_send_remaining_notification( $appointment_id, $entry_id );
            }
        }

        function bookingpress_outlook_calendar_send_remaining_notification( $appointment_id, $entry_id ){
            global $wpdb;
            $get_email_notification_data = $wpdb->get_results( $wpdb->prepare( "SELECT option_name,option_value FROM " . $wpdb->options . " WHERE option_name LIKE %s", 'bookingpress_oc_send_notification_' . $appointment_id .'_'. $entry_id.'%'), ARRAY_A );
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
                    update_option( 'bookingpress_oc_send_notification_' . $appointment_id . '_' . $entry_id .'_'. $template_type .'_'.$notification_name .'_is_sent', 1 );
                }
            }
        }

        function bookingpress_retrieve_location_field_data( $bookingpress_location_field_id, $appointment_data ){

            $bookingpress_location_data = '';
            if( empty( $bookingpress_location_field_id ) || empty( $appointment_data ) ){
                return $bookingpress_location_data;
            }

            global $wpdb, $tbl_bookingpress_form_fields, $bookingpress_pro_appointment;
            
            $bookingpress_location_field_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_form_field_name,bookingpress_field_meta_key FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_form_field_id = %d", $bookingpress_location_field_id ) );
            
            
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

        function bookingpress_outlook_calendar_replace_shortcode( $bookingpress_content, $bookingpress_appointment_data,$event_from = 'insert' ){

            global $wpdb,$BookingPress;
            
            global $BookingPress,$BookingPressPro,$tbl_bookingpress_appointment_bookings,$wpdb;

            $bookingpress_appointment_id       = !empty( $bookingpress_appointment_data['bookingpress_appointment_booking_id'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_appointment_booking_id'] ) : '';
			$bookingpress_appointment_date       = !empty( $bookingpress_appointment_data['bookingpress_appointment_date'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_appointment_date'] ) : '';
			$bookingpress_appointment_start_time = !empty( $bookingpress_appointment_data['bookingpress_appointment_time'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_appointment_time'] ) : '';
			$bookingpress_appointment_end_time   = !empty( $bookingpress_appointment_data['bookingpress_appointment_end_time'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_appointment_end_time'] ) : '';
			$bookingpress_appointment_service_id   = !empty( $bookingpress_appointment_data['bookingpress_service_id'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_service_id'] ) : '';
			$bookingpress_staff_member_id = !empty( $bookingpress_appointment_data['bookingpress_staff_member_id'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_staff_member_id'] ) : '';

            if($event_from == 'insert') {

                $bpa_other_bookings = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_time = %s AND bookingpress_appointment_end_time = %s AND bookingpress_appointment_date = %s AND bookingpress_service_id = %d AND bookingpress_staff_member_id != '' AND bookingpress_staff_member_id = %d AND bookingpress_appointment_status != %d AND bookingpress_appointment_status != %d AND ((bookingpress_outlook_calendar_event_id != '') OR bookingpress_appointment_booking_id = %d ) ORDER BY bookingpress_appointment_booking_id ",$bookingpress_appointment_start_time,$bookingpress_appointment_end_time,$bookingpress_appointment_date,$bookingpress_appointment_service_id,$bookingpress_staff_member_id,3, 4,$bookingpress_appointment_id),ARRAY_A  );

            } else {
                $bpa_other_bookings = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_time = %s AND bookingpress_appointment_end_time = %s AND bookingpress_appointment_date = %s AND bookingpress_appointment_booking_id != %d AND bookingpress_service_id = %d AND bookingpress_staff_member_id != '' AND bookingpress_staff_member_id = %d AND bookingpress_appointment_status != %d AND bookingpress_appointment_status != %d AND ((bookingpress_outlook_calendar_event_id != '') OR bookingpress_appointment_booking_id = %d )  ORDER BY bookingpress_appointment_booking_id ",$bookingpress_appointment_start_time,$bookingpress_appointment_end_time,$bookingpress_appointment_date,$bookingpress_appointment_id,$bookingpress_appointment_service_id,$bookingpress_staff_member_id,3, 4,$bookingpress_appointment_id),ARRAY_A  );
            }

            if(method_exists( $BookingPressPro, 'bookingpress_replace_calendar_appointment_data' ) && !empty($bpa_other_bookings) ) {
                $bookingpress_content = $BookingPressPro->bookingpress_replace_calendar_appointment_data($bookingpress_content,$bookingpress_appointment_data,$bpa_other_bookings);
            } elseif(method_exists( $BookingPress, 'bookingpress_replace_appointment_data' )) {
                $bookingpress_content = $BookingPress->bookingpress_replace_appointment_data($bookingpress_content,$bookingpress_appointment_data);
            }            
           
            return $bookingpress_content;
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

        function bookingpress_modify_service_time_with_calendar_events( $total_booked_appointments, $selected_date, $service_timings, $service_id  ){

            global $wpdb, $bookingpress_pro_appointment_bookings, $tbl_bookingpress_default_workhours, $bookingpress_pro_staff_members, $tbl_bookingpress_appointment_bookings;            
            
            $is_staffmember_activated = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();
            if( !$is_staffmember_activated ){
                return $total_booked_appointments;
            }
           
            $staff_member_id = '';
            if( !empty( $_POST['selected_staffmember']) ){
                $staff_member_id = intval( $_POST['selected_staffmember'] );
            }

            if( empty( $staff_member_id ) && !empty( $_POST['bookingpress_selected_staffmember']['selected_staff_member_id'] ) ){
                $staff_member_id = intval( $_POST['bookingpress_selected_staffmember']['selected_staff_member_id'] );
            }

            if( empty( $staff_member_id ) && !empty( $_POST['appointment_data_obj']['bookingpress_selected_staff_member_details']['selected_staff_member_id'] ) ){
                $staff_member_id = $_POST['appointment_data_obj']['bookingpress_selected_staff_member_details']['selected_staff_member_id'];
            }

            if( empty( $staff_member_id ) ){
                return $total_booked_appointments;    
            }

            $calendarId = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_selected_ocalendar' );
            if( empty( $calendarId ) ){
                return $total_booked_appointments;
            }

            $staff_token_data = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_ocalendar_oauthdata');
            if( empty( $staff_token_data ) ){
                return $total_booked_appointments;
            }

            $staff_token_data = json_decode( $staff_token_data );            

            $staff_access_token = $staff_token_data->access_token;
            if( current_time( 'timestamp' ) > ( $staff_token_data->created + $staff_token_data->expires_in ) ){
                $staff_access_token = $this->bookingpress_refresh_access_token( $staff_token_data, $staff_member_id );
            }
            
            $bpa_unique_id = isset($_POST['appointment_data_obj']['bookingpress_uniq_id']) ? $_POST['appointment_data_obj']['bookingpress_uniq_id'] : '';

            $staff_ocalendar_events = $this->bookingpress_retrieve_outlook_calendar_events( $staff_member_id, $calendarId, $selected_date, true );
            if( empty( $staff_ocalendar_events ) ){
                return $total_booked_appointments;
            }

            $bookingpress_addon_popup_field_form = $this->bookingpress_get_outlook_calendar_credentials();
            $maxResults = $bookingpress_addon_popup_field_form['outlook_calendar_max_event'];

            $staffmember_capacity = get_transient( 'bkp_oc_staff_capacity_for_service_' . $staff_member_id .'_' . $service_id .'_' . $bpa_unique_id  );

            if( empty( $staff_capacity ) ){
                global $tbl_bookingpress_staffmembers_services;
                $staffmember_capacity = $wpdb->get_var( $wpdb->prepare( "SELECT bookingpress_service_capacity FROM `{$tbl_bookingpress_staffmembers_services}` WHERE bookingpress_staffmember_id = %d AND bookingpress_service_id = %d", $staff_member_id, $service_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_services is table name.

                set_transient( 'bkp_oc_staff_capacity_for_service_' . $staff_member_id .'_' . $service_id .'_' . $bpa_unique_id, $staffmember_capacity, HOUR_IN_SECONDS );
            }
            $event_counter = 0;
            foreach( $staff_ocalendar_events as $event_id => $event_times ){

                if( !empty( $maxResults ) && $event_counter >= $maxResults ){
                    break;
                }
                if( !empty( $event_id ) ){
                    /** check if Event is Registered With BookingPress */                    
                    $db_service_id = $wpdb->get_var( $wpdb->prepare( "SELECT bookingpress_service_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_outlook_calendar_event_id = %s", $event_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
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
                        'bookingpress_oc_blocked' => true,
                    );
                    
                } else if( $evt_start_date == $selected_date && $evt_end_date != $selected_date ){
                    $total_booked_appointments[] = array(
                        'bookingpress_appointment_time' => date('H:i:s', strtotime( $event_start_datetime ) ),
                        'bookingpress_appointment_end_time' => '23:59:59',
                        'bookingpress_selected_extra_members' => ( $staffmember_capacity ),
                        'bookingpress_oc_blocked' => true,
                    );
                
                } else if( $evt_start_date != $selected_date && $evt_end_date == $selected_date ){
                    $start_time_check = $evt_end_date .' 00:00:00';
                    if( !( $start_time_check == $event_end_datetime ) ){
                        $total_booked_appointments[] = array(
                            'bookingpress_appointment_time' => '00:00:00',
                            'bookingpress_appointment_end_time' => date('H:i:s', strtotime( $event_end_datetime ) ),
                            'bookingpress_selected_extra_members' => ( $staffmember_capacity ),
                            'bookingpress_oc_blocked' => true,
                        );  
                    }
                } else if( $evt_start_date != $selected_date && $evt_end_date != $selected_date ){
                    if( $selected_date > $evt_start_date && $selected_date < $evt_end_date ){
                        $total_booked_appointments[] = array(
                            'bookingpress_appointment_time' => date('H:i:s', strtotime( $event_start_datetime ) ),
                            'bookingpress_appointment_end_time' => date('H:i:s', strtotime( $event_end_datetime ) ),
                            'bookingpress_selected_extra_members' => ( $staffmember_capacity ),
                            'bookingpress_oc_blocked' => true,
                        );
                    }

                }

                $event_counter++;
            }

            return $total_booked_appointments;
        }

        function bookingpress_update_outlook_calendar_event( $appointment_id, $appointment_status = '3' ){
            
            global $bookingpress_pro_staff_members, $wpdb, $tbl_bookingpress_appointment_bookings,$bookingpress_debug_integration_log_id;  

            if( empty( $appointment_id ) || empty( $appointment_status ) ){
                return;
            }

            $is_staffmember_activated = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();            
            if( !$is_staffmember_activated ){
                return;
            }

            $bookingpress_check_status = apply_filters( 'bookingpress_check_status_for_appointment_integration', false, $appointment_id, $appointment_status, ''); 
            if($bookingpress_check_status){
                return;
            }

            $booking_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $appointment_id ),ARRAY_A );
            
            $event_id = !empty( $booking_data['bookingpress_outlook_calendar_event_id'] ) ? esc_html($booking_data['bookingpress_outlook_calendar_event_id']) : '';
            $staff_member_id = !empty( $booking_data['bookingpress_staff_member_id'] ) ? intval($booking_data['bookingpress_staff_member_id']) : 0;
            $service_id = !empty( $booking_data['bookingpress_service_id'] ) ? intval($booking_data['bookingpress_service_id']) : 0;

            if( empty( $staff_member_id ) || empty($service_id) ){
                return;
            }

            $calendarId = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_selected_ocalendar');
            if( empty( $calendarId ) ){
                return;
            }

            $staff_token_data = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_ocalendar_oauthdata');
            if( empty( $staff_token_data ) ){
                return;
            }

            $staff_token_data = json_decode( $staff_token_data );
            $staff_access_token = $staff_token_data->access_token;

            if( current_time( 'timestamp' ) > ( $staff_token_data->created + $staff_token_data->expires_in ) ){
                $staff_access_token = $this->bookingpress_refresh_access_token( $staff_token_data, $staff_member_id );
            }

            $outlook_event_url = 'https://graph.microsoft.com/v1.0/me/calendars/'.$calendarId.'/events/'.$event_id;
            $args = array(
                'timeout' => 4500,
                'headers' => array(
                    'Authorization' => 'Bearer ' . $staff_access_token,
                )
            );

            $bookingpress_addon_popup_field_form = $this->bookingpress_get_outlook_calendar_credentials();

            if(($appointment_status == '1' || $appointment_status == '2' ) && empty( $event_id ) ) {

                $this->bookingpress_assign_appointment_to_staff_member($appointment_id);

            } else {                                

                if( empty( $event_id ) ){
                    return;
                } 

                $bpa_other_bookings = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id != %d AND bookingpress_outlook_calendar_event_id = %s AND bookingpress_appointment_status != %d AND bookingpress_appointment_status != %d", $appointment_id, $event_id, 3, 4 ) );

                if($bpa_other_bookings > 0 ) {

                    if($appointment_status == '3' || $appointment_status == '4' ) {
                        $wpdb->update(
                            $tbl_bookingpress_appointment_bookings,
                            array(
                                'bookingpress_outlook_calendar_event_id' => ''
                            ),
                            array(
                                'bookingpress_appointment_booking_id' => $appointment_id
                            )
                        );
                    }                    

                    $bookingpress_event_title = $this->bookingpress_outlook_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['outlook_calendar_event_title'], $booking_data );
                                
                    $bookingpress_event_description = $this->bookingpress_outlook_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['outlook_calendar_event_description'], $booking_data );

                    $bookingpress_event_location = $this->bookingpress_retrieve_location_field_data( $bookingpress_addon_popup_field_form['outlook_calendar_event_location'], $appointment_data );
                    
                    $event_data = array(
                        'subject' => $bookingpress_event_title,
                        'body' => array(
                            'contentType' => 'HTML',
                            'content' => $bookingpress_event_description
                        )
                    );
                    if( !empty( $bookingpress_event_location ) ){
                        $event_data['location'] = array(
                            'displayName' => $bookingpress_event_location,
                            'address' => array(
                                'street' => $bookingpress_event_location
                            )
                        );
                    }
                    $args = array(
                        'timeout' => 5000,
                        'headers' => array(
                            'Authorization' => 'Bearer '.$staff_access_token,
                            'Content-Type'  => 'application/json'
                        ),
                        'body' => json_encode( $event_data )
                    );   

                    $args['method'] = 'PATCH';                    
                    $outlook_event_url = 'https://graph.microsoft.com/v1.0/me/calendar/events/'.$event_id; 
                    $response = wp_remote_post( $outlook_event_url, $args );

                    if( is_wp_error( $response ) ){
                        $debug_log_data = array(
                            'outlook_calendar_message' => $response->get_error_message(),
                            'outlook_calendar_log_placement' => 'error while updating event on Outlook calendar for change status backend side',
                            'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                        );
                        do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'Outlook Calendar event updation failed for change status backend side', $debug_log_data, $bookingpress_debug_integration_log_id );
                        return;
                    }

                    return;              
                }

                $args = array(
                    'timeout' => 4500,
                    'headers' => array(
                        'Authorization' => 'Bearer ' . $staff_access_token,
                    )
                );
                
                $is_exist_event = $this->bookignpress_is_outlook_event_exist($calendarId,$event_id,$staff_access_token);

                if( '3' == $appointment_status || '4' == $appointment_status ){

                    $wpdb->update(
                        $tbl_bookingpress_appointment_bookings,
                        array(
                            'bookingpress_outlook_calendar_event_id' => ''
                        ),
                        array(
                            'bookingpress_appointment_booking_id' => $appointment_id
                        )
                    );
                    
                    if($is_exist_event == 0) {
                        return;
                    }

                    $args = array(
                        'timeout' => 4500,
                        'headers' => array(
                            'Authorization' => 'Bearer ' . $staff_access_token,
                        )
                    );
                    $args['headers']['Content-Type'] = 'application/json';
                    $args['method'] = 'POST';
                    $args['body'] = json_encode(
                        array(
                            'Comment' => $appointment_status
                        )
                    );

                    $outlook_event_url = 'https://graph.microsoft.com/v1.0/me/calendars/'.$calendarId.'/events/'.$event_id.'/cancel';                    
                    $resp = wp_remote_request( $outlook_event_url, $args );

                    if( is_wp_error( $resp ) ){
                        $debug_log_data = array(
                            'outlook_calendar_message' => $resp->get_error_message(),
                            'outlook_calendar_log_placement' => 'error while cancelling events for Outlook calendar',
                            'outlook_calendar_status' => $appointment_status,
                            'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                        );
                        do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'Outlook Calendar event cancelling failed', $debug_log_data, $bookingpress_debug_integration_log_id );
                        return;
                    }

                    if( 200 != $resp['response']['code'] ){
                        $debug_log_data = array(
                            'outlook_calendar_message' => $resp,
                            'outlook_calendar_log_placement' => 'status not 200 OK while cancelling events from Outlook calendar for status change',
                            'outlook_calendar_status' => $appointment_status,
                            'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                        );
                        do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'status not 200 OK while cancelling Outlook Calendar event for status change', $debug_log_data, $bookingpress_debug_integration_log_id );
                        return;
                    } else {
                        $debug_log_data = array(
                            'outlook_calendar_message' => $resp,
                            'outlook_calendar_log_placement' => 'status 200 OK while cancelling events from Outlook calendar for status change',
                            'outlook_calendar_status' => $appointment_status,
                            'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                        );
                        do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'status 200 OK while cancelling Outlook Calendar event for status change', $debug_log_data, $bookingpress_debug_integration_log_id );
                    }
                    
                } else if( '1' == $appointment_status || '2' == $appointment_status ) {
                    if($is_exist_event == 0) {                 
                        $this->bookingpress_assign_appointment_to_staff_member( $appointment_id );
                    } else {

                        $bookingpress_event_title = $this->bookingpress_outlook_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['outlook_calendar_event_title'], $booking_data);
                                
                        $bookingpress_event_description = $this->bookingpress_outlook_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['outlook_calendar_event_description'], $booking_data );

                        $bookingpress_event_location = $this->bookingpress_retrieve_location_field_data( $bookingpress_addon_popup_field_form['outlook_calendar_event_location'], $appointment_data );
                        
                        $event_data = array(
                            'subject' => $bookingpress_event_title,
                            'body' => array(
                                'contentType' => 'HTML',
                                'content' => $bookingpress_event_description
                            )
                        );
                        if( !empty( $bookingpress_event_location ) ){
                            $event_data['location'] = array(
                                'displayName' => $bookingpress_event_location,
                                'address' => array(
                                    'street' => $bookingpress_event_location
                                )
                            );
                        }
                        $args = array(
                            'timeout' => 5000,
                            'headers' => array(
                                'Authorization' => 'Bearer '.$staff_access_token,
                                'Content-Type'  => 'application/json'
                            ),
                            'body' => json_encode( $event_data )
                        );   
                        $args['method'] = 'PATCH';
                        $outlook_event_url = 'https://graph.microsoft.com/v1.0/me/calendar/events/'.$event_id; 
                        $response = wp_remote_post( $outlook_event_url, $args );
            
                        if( is_wp_error( $response ) ){
                            $debug_log_data = array(
                                'outlook_calendar_message' => $response->get_error_message(),
                                'outlook_calendar_log_placement' => 'error while updating event on Outlook calendar',
                                'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                            );
                            do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'Outlook Calendar event updation failed', $debug_log_data, $bookingpress_debug_integration_log_id );
                            return;
                        }
                    }
                }
            }   
        }

        function bookingpress_before_delete_appointment_func($appointment_id) {

            global $wpdb, $tbl_bookingpress_appointment_bookings, $bookingpress_pro_staff_members, $BookingPress, $tbl_bookingpress_appointment_meta, $bookingpress_debug_integration_log_id, $bookingpress_pro_appointment_bookings;

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

            $appointment_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $appointment_id ), ARRAY_A );

            $staff_member_id = !empty($appointment_data['bookingpress_staff_member_id']) ? intval($appointment_data['bookingpress_staff_member_id']) : 0;
            $service_id = !empty($appointment_data['bookingpress_service_id']) ? intval($appointment_data['bookingpress_service_id']) : 0;
            
            if( empty( $appointment_data ) || empty( $service_id ) || empty($staff_member_id ) ){
                return;
            }

            $bookingpress_event_id = esc_html($appointment_data['bookingpress_outlook_calendar_event_id']);
            if(empty($bookingpress_event_id)) {
                return;
            }

            $calendarId = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_selected_ocalendar' );
            if( empty( $calendarId ) ){
                return;
            }            

            $staff_token_data = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_ocalendar_oauthdata');            
            if( empty( $staff_token_data ) ){
                return;
            }
            $bookingpress_enable_outlook_calendar = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_enable_outlook_calendar');

            if(empty($bookingpress_enable_outlook_calendar) || $bookingpress_enable_outlook_calendar == 'false' ) {
                return; 
            }

            $appointment_status = esc_html($appointment_data['bookingpress_appointment_status']);
            if( $appointment_status == '5'|| $appointment_status == '6' ) {
                return;
            }

            $staff_token_data = json_decode($staff_token_data);
            $staff_access_token = $staff_token_data->access_token;

            if( current_time( 'timestamp' ) > ( $staff_token_data->created + $staff_token_data->expires_in ) ){                
                $staff_access_token = $this->bookingpress_refresh_access_token( $staff_token_data, $staff_member_id );
            };

            $bpa_other_bookings = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id != %d AND bookingpress_outlook_calendar_event_id = %s AND bookingpress_appointment_status != %d AND bookingpress_appointment_status != %d", $appointment_id, $bookingpress_event_id, 3, 4 ) );

            $bookingpress_addon_popup_field_form = $this->bookingpress_get_outlook_calendar_credentials();

            if( $bpa_other_bookings > 0 ) {
                             
                $bookingpress_event_title = $this->bookingpress_outlook_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['outlook_calendar_event_title'], $appointment_data,'delete' );
                                
                $bookingpress_event_description = $this->bookingpress_outlook_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['outlook_calendar_event_description'], $appointment_data,'delete' );

                $bookingpress_event_location = $this->bookingpress_retrieve_location_field_data( $bookingpress_addon_popup_field_form['outlook_calendar_event_location'], $appointment_data );
                
                $event_data = array(
                    'subject' => $bookingpress_event_title,
                    'body' => array(
                        'contentType' => 'HTML',
                        'content' => $bookingpress_event_description
                    )
                );
                if( !empty( $bookingpress_event_location ) ){
                    $event_data['location'] = array(
                        'displayName' => $bookingpress_event_location,
                        'address' => array(
                            'street' => $bookingpress_event_location
                        )
                    );
                }
                $args = array(
                    'timeout' => 5000,
                    'headers' => array(
                        'Authorization' => 'Bearer '.$staff_access_token,
                        'Content-Type'  => 'application/json'
                    ),
                    'body' => json_encode( $event_data )
                );   
                $args['method'] = 'PATCH';                    
                $outlook_event_url = 'https://graph.microsoft.com/v1.0/me/calendar/events/'.$bookingpress_event_id; 
                $response = wp_remote_post( $outlook_event_url, $args );

                if( is_wp_error( $response ) ){
                    $debug_log_data = array(
                        'outlook_calendar_message' => $response->get_error_message(),
                        'outlook_calendar_log_placement' => 'error while updating event on Outlook calendar',
                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                    );
                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'Outlook Calendar event updation failed', $debug_log_data, $bookingpress_debug_integration_log_id );
                    return;
                }

            } else {                
                $is_exist_event = $this->bookignpress_is_outlook_event_exist($calendarId,$bookingpress_event_id,$staff_access_token);

                if($is_exist_event == 0) {
                    return;
                } 
                $outlook_event_url = 'https://graph.microsoft.com/v1.0/me/calendars/'.$calendarId.'/events/'.$bookingpress_event_id.'/cancel';
                $args = array(
                    'timeout' => 4500,
                    'headers' => array(
                        'Authorization' => 'Bearer ' . $staff_access_token,
                    )
                );
                $args['headers']['Content-Type'] = 'application/json';
                $args['method'] = 'POST';
                $args['body'] = json_encode(
                    array(
                        'Comment' => $appointment_status
                    )
                );

                /* if event exist the delete then delete event. */

                $resp = wp_remote_request( $outlook_event_url, $args );

                if( is_wp_error( $resp ) ){
                    $debug_log_data = array(
                        'outlook_calendar_message' => $resp->get_error_message(),
                        'outlook_calendar_log_placement' => 'error while cancelling events for Outlook calendar for delete appointment front side',
                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                    );
                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'Outlook Calendar event cancelling failed for delete appointment front side', $debug_log_data, $bookingpress_debug_integration_log_id );
                    return;
                }

                if( 200 == $resp['response']['code'] ){
                    $debug_log_data = array(
                        'outlook_calendar_message' => $resp,
                        'outlook_calendar_log_placement' => 'status not 200 OK while cancelling events from Outlook calendar for delete appointment front side',
                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                    );
                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'status not 200 OK while cancelling Outlook Calendar event for delete appointment front side', $debug_log_data, $bookingpress_debug_integration_log_id );
                    return;
                }
            }
        }

        function bookingpress_calendar_event_reschedule( $appointment_id ) {
            
            global $wpdb, $tbl_bookingpress_appointment_bookings, $bookingpress_pro_staff_members, $BookingPress, $tbl_bookingpress_appointment_meta, $bookingpress_debug_integration_log_id,$bookingpress_pro_appointment_bookings,$tbl_bookingpress_staffmembers_services;

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

            $appointment_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $appointment_id ), ARRAY_A );
            $staff_member_id = !empty($appointment_data['bookingpress_staff_member_id']) ? intval($appointment_data['bookingpress_staff_member_id']) : 0;
            $service_id = !empty($appointment_data['bookingpress_service_id']) ? intval($appointment_data['bookingpress_service_id']) : 0;

            if( empty( $appointment_data )|| empty( $staff_member_id ) || empty( $service_id ) ){
                return;
            }

            $calendarId = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_selected_ocalendar' );
            if( empty( $calendarId ) ){
                return;
            }

            $staff_token_data = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_ocalendar_oauthdata');            
            if( empty( $staff_token_data ) ){
                return;
            }

            $bookingpress_enable_outlook_calendar = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $staff_member_id, 'bookingpress_enable_outlook_calendar');
            if(empty($bookingpress_enable_outlook_calendar) || $bookingpress_enable_outlook_calendar == 'false' ) {
                return; 
            }

            $appointment_status = esc_html($appointment_data['bookingpress_appointment_status']);
            if($appointment_status == '5' || $appointment_status == '6' ) {
                return;
            }

            $staff_token_data = json_decode($staff_token_data);
            $staff_access_token = $staff_token_data->access_token;

            if( current_time( 'timestamp' ) > ( $staff_token_data->created + $staff_token_data->expires_in ) ){                
                $staff_access_token = $this->bookingpress_refresh_access_token( $staff_token_data, $staff_member_id );
            };

            $bookingpress_addon_popup_field_form = $this->bookingpress_get_outlook_calendar_credentials();            
            $bookingpress_event_id   = esc_html($appointment_data['bookingpress_outlook_calendar_event_id']);            
            $bookingpress_start_time = esc_html( $appointment_data['bookingpress_appointment_time'] );
            $bookingpress_appointment_date = $bookingpress_appointment_end_date = esc_html( $appointment_data['bookingpress_appointment_date'] );
            $bookingpress_end_time   = esc_html( $appointment_data['bookingpress_appointment_end_time'] );
            $service_duration = esc_html( $appointment_data['bookingpress_service_duration_val'] );
            $bookingpress_duration_unit = esc_html( $appointment_data['bookingpress_service_duration_unit'] );
            if($bookingpress_duration_unit == 'd' && !empty($service_duration) && $service_duration > 1) { 
                $service_duration-- ;
                $bookingpress_appointment_end_date = date('Y-m-d', strtotime($service_duration.' day',strtotime($bookingpress_appointment_date)));
            }
            $bookingpress_start_date_time = date('Y-m-d',strtotime($bookingpress_appointment_date)).'T'.date('H:i:s',strtotime($bookingpress_start_time));
            $bookingpress_end_date_time   = date('Y-m-d',strtotime($bookingpress_appointment_end_date)).'T'.date('H:i:s',strtotime($bookingpress_end_time));            
            if($bookingpress_duration_unit == 'd') {
                $bookingpress_start_date_time = date('Y-m-d',strtotime($bookingpress_appointment_date)).'T 00:00:00';
                $bookingpress_end_date_time   = date('Y-m-d',strtotime($bookingpress_appointment_end_date)).'T 23:59:59';
            } else {                                
                if($bookingpress_end_time == '24:00:00') {
                    $bookingpress_end_date_time = date('Y-m-d', strtotime($bookingpress_appointment_date . '+1 days')).'T00:00:00';
                }
            }
            
            $user_timezone = wp_timezone_string();
            $user_timezone = $bookingpress_pro_appointment_bookings->bookingpress_convert_offset_to_string( $user_timezone );            
            
            if(($appointment_status == '1' || $appointment_status == '2') && empty($bookingpress_event_id)) {
                                       
                $this->bookingpress_assign_appointment_to_staff_member( $appointment_id );

            } else {

                if(empty($bookingpress_event_id)) {
                    return;
                }
                
                /** last appointment details */

                $last_appointment_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_appointment_meta_value FROM {$tbl_bookingpress_appointment_meta} WHERE bookingpress_appointment_meta_key = %s AND bookingpress_appointment_id = %d", '_bpa_last_appointment_data', $appointment_id ),ARRAY_A );
                
                if( !empty( $last_appointment_data ) ) {

                    $last_appointment_data = json_decode( $last_appointment_data['bookingpress_appointment_meta_value'],true );
                    
                    if( $last_appointment_data['bookingpress_staff_member_id'] != $staff_member_id || $bookingpress_start_time != $last_appointment_data['bookingpress_appointment_time'] || $bookingpress_appointment_date != $last_appointment_data ['bookingpress_appointment_date'] || $service_id != $last_appointment_data['bookingpress_service_id'] ) {                       
           
                        if($appointment_status == '3' && $appointment_status == '4' ) {

                            $bpa_other_bookings = $wpdb->get_row( $wpdb->prepare( "SELECT count(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id != %d AND bookingpress_outlook_calendar_event_id = %s AND bookingpress_appointment_status != %d AND bookingpress_appointment_status != %d", $appointment_id, $bookingpress_event_id, 3, 4 ) );

                            $wpdb->update(
                                $tbl_bookingpress_appointment_bookings,
                                array(
                                    'bookingpress_outlook_calendar_event_id' => '',
                                ),
                                array(
                                    'bookingpress_appointment_booking_id' => $appointment_id
                                )
                            );

                            if($bpa_other_bookings > 0) {         
                                
                                /* Update event name and description */
                                
                                $bookingpress_event_title = $this->bookingpress_outlook_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['outlook_calendar_event_title'], $last_appointment_data );
                                
                                $bookingpress_event_description = $this->bookingpress_outlook_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['outlook_calendar_event_description'], $last_appointment_data );

                                $bookingpress_event_location = $this->bookingpress_retrieve_location_field_data( $bookingpress_addon_popup_field_form['outlook_calendar_event_location'], $appointment_data );
                                
                                $event_data = array(
                                    'subject' => $bookingpress_event_title,
                                    'body' => array(
                                        'contentType' => 'HTML',
                                        'content' => $bookingpress_event_description
                                    )
                                );
                                if( !empty( $bookingpress_event_location ) ){
                                    $event_data['location'] = array(
                                        'displayName' => $bookingpress_event_location,
                                        'address' => array(
                                            'street' => $bookingpress_event_location
                                        )
                                    );
                                }
                                $args = array(
                                    'timeout' => 5000,
                                    'headers' => array(
                                        'Authorization' => 'Bearer '.$staff_access_token,
                                        'Content-Type'  => 'application/json'
                                    ),
                                    'body' => json_encode( $event_data )
                                );   
                                $args['method'] = 'PATCH';                    
                                $outlook_event_url = 'https://graph.microsoft.com/v1.0/me/calendar/events/'.$bookingpress_event_id; 
                                $response = wp_remote_post( $outlook_event_url, $args );

                                if( is_wp_error( $response ) ){
                                    $debug_log_data = array(
                                        'outlook_calendar_message' => $response->get_error_message(),
                                        'outlook_calendar_log_placement' => 'error while updating event on Outlook calendar',
                                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                                    );
                                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'Outlook Calendar event updation failed', $debug_log_data, $bookingpress_debug_integration_log_id );
                                    return;
                                }

                            } else {

                                /* check event exist or not if the event exist the delete the event*/

                                $is_exist_event = $this->bookignpress_is_outlook_event_exist($calendarId,$bookingpress_event_id,$staff_access_token);
                                if($is_exist_event == 0) {
                                    return;
                                }
                                
                                $outlook_event_url = 'https://graph.microsoft.com/v1.0/me/calendars/'.$calendarId.'/events/'.$bookingpress_event_id.'/cancel';
                                $args = array(
                                    'timeout' => 4500,
                                    'headers' => array(
                                        'Authorization' => 'Bearer ' . $staff_access_token,
                                    )
                                );                
                                $args['headers']['Content-Type'] = 'application/json';
                                $args['method'] = 'POST';
                                $args['body'] = json_encode(
                                    array(
                                        'Comment' => $appointment_status
                                    )
                                );

                                /* if event exist the delete then delete event. */
                
                                $resp = wp_remote_request( $outlook_event_url, $args );
                
                                if( is_wp_error( $resp ) ){
                                    $debug_log_data = array(
                                        'outlook_calendar_message' => $resp->get_error_message(),
                                        'outlook_calendar_log_placement' => 'error while cancelling events for Outlook calendar',
                                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                                    );
                                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'Outlook Calendar event cancelling failed', $debug_log_data, $bookingpress_debug_integration_log_id );
                                    return;
                                }
                
                                if( 200 == $resp['response']['code'] ){

                                    $debug_log_data = array(
                                        'outlook_calendar_message' => $resp,
                                        'outlook_calendar_log_placement' => 'status not 200 OK while cancelling events from Outlook calendar for change appointment data',
                                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                                    );
                                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'status not 200 OK while cancelling Outlook Calendar event for change appointment data', $debug_log_data, $bookingpress_debug_integration_log_id );
                                    return;
                                }  
                            }

                        } else {

                            $bpa_other_bookings = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id != %d AND bookingpress_outlook_calendar_event_id = %s AND bookingpress_appointment_status != %d AND bookingpress_appointment_status != %d", $appointment_id, $bookingpress_event_id, 3, 4 ) );

                            if( $bpa_other_bookings > 0 ) {

                                $this->bookingpress_assign_appointment_to_staff_member( $appointment_id );

                                $bookingpress_event_title = $this->bookingpress_outlook_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['outlook_calendar_event_title'], $last_appointment_data );
                                
                                $bookingpress_event_description = $this->bookingpress_outlook_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['outlook_calendar_event_description'], $last_appointment_data );

                                $bookingpress_event_location = $this->bookingpress_retrieve_location_field_data( $bookingpress_addon_popup_field_form['outlook_calendar_event_location'], $appointment_data );

                                $event_data = array(
                                    'subject' => $bookingpress_event_title,
                                    'body' => array(
                                        'contentType' => 'HTML',
                                        'content' => $bookingpress_event_description
                                    )
                                );
                                if( !empty( $bookingpress_event_location ) ){
                                    $event_data['location'] = array(
                                        'displayName' => $bookingpress_event_location,
                                        'address' => array(
                                            'street' => $bookingpress_event_location
                                        )
                                    );
                                }
                                $args = array(
                                    'timeout' => 5000,
                                    'headers' => array(
                                        'Authorization' => 'Bearer '.$staff_access_token,
                                        'Content-Type'  => 'application/json'
                                    ),
                                    'body' => json_encode( $event_data )
                                );   

                                $args['method'] = 'PATCH';                    
                                $outlook_event_url = 'https://graph.microsoft.com/v1.0/me/calendar/events/'.$bookingpress_event_id; 
                                $response = wp_remote_post( $outlook_event_url, $args );

                                if( is_wp_error( $response ) ){
                                    $debug_log_data = array(
                                        'outlook_calendar_message' => $response->get_error_message(),
                                        'outlook_calendar_log_placement' => 'error while updating event on Outlook calendar while change appointment data',
                                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                                    );
                                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'Outlook Calendar event updation failed while change appointment data', $debug_log_data, $bookingpress_debug_integration_log_id );                                    
                                    return;
                                }                        

                            } else {
                                
                                $already_booked_appointment = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bpa.bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} bpa LEFT JOIN {$tbl_bookingpress_staffmembers_services} bps ON bpa.bookingpress_staff_member_id=bps.bookingpress_staffmember_id WHERE bpa.bookingpress_service_id = %d AND bpa.bookingpress_appointment_status != %d AND bpa.bookingpress_appointment_status != %d AND bpa.bookingpress_staff_member_id = %d AND bpa.bookingpress_appointment_date = %s AND bpa.bookingpress_appointment_time = %s AND bpa.bookingpress_appointment_booking_id != %d AND bps.bookingpress_service_capacity > %d", $service_id, 3, 4, $staff_member_id, $bookingpress_appointment_date, $bookingpress_start_time, $appointment_id, 1));

                                if($already_booked_appointment > 0) {

                                    $this->bookingpress_assign_appointment_to_staff_member( $appointment_id );

                                    $is_exist_event = $this->bookignpress_is_outlook_event_exist($calendarId,$bookingpress_event_id,$staff_access_token);
                                    if($is_exist_event == 0) {
                                        return;
                                    }

                                    $outlook_event_url = 'https://graph.microsoft.com/v1.0/me/calendars/'.$calendarId.'/events/'.$bookingpress_event_id.'/cancel';
                                    $args = array(
                                        'timeout' => 4500,
                                        'headers' => array(
                                            'Authorization' => 'Bearer ' . $staff_access_token,
                                        )
                                    );                                   
                                    $args['headers']['Content-Type'] = 'application/json';
                                    $args['method'] = 'POST';
                                    $args['body'] = json_encode(
                                        array(
                                            'Comment' => $appointment_status
                                        )
                                    );
                    
                                    $resp = wp_remote_request( $outlook_event_url, $args );
                    
                                    if( is_wp_error( $resp ) ){
                                        $debug_log_data = array(
                                            'outlook_calendar_message' => $resp->get_error_message(),
                                            'outlook_calendar_log_placement' => 'error while cancelling events for Outlook calendar for change appointment data',
                                            'outlook_calendar_status' => $appointment_status,
                                            'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                                        );
                                        do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'Outlook Calendar event cancelling failed for change appointment data', $debug_log_data, $bookingpress_debug_integration_log_id );
                                        return;
                                    }
                    
                                    if( 200 != $resp['response']['code'] ){
                                        $debug_log_data = array(
                                            'outlook_calendar_message' => $resp,
                                            'outlook_calendar_log_placement' => 'status not 200 OK while cancelling events from Outlook calendar for change appointment data',
                                            'outlook_calendar_status' => $appointment_status,
                                            'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                                        );
                                        do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'status not 200 OK while cancelling Outlook Calendar event for change appointment data', $debug_log_data, $bookingpress_debug_integration_log_id );
                                        return;
                                    }   

                                } else {

                                    /* update the event with the time */

                                    $bookingpress_event_title = $this->bookingpress_outlook_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['outlook_calendar_event_title'], $appointment_data );                                
                                    $bookingpress_event_description = $this->bookingpress_outlook_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['outlook_calendar_event_description'], $appointment_data );

                                    $bookingpress_event_location = $this->bookingpress_retrieve_location_field_data( $bookingpress_addon_popup_field_form['outlook_calendar_event_location'], $appointment_data );
                                    
                                    
                                    $event_data = array(
                                        'subject' => $bookingpress_event_title,
                                        'body' => array(
                                            'contentType' => 'HTML',
                                            'content' => $bookingpress_event_description
                                        ),
                                        'start' => array(
                                            'dateTime' => $bookingpress_start_date_time,
                                            'timeZone' => $user_timezone
                                        ),
                                        'end' => array(
                                            'dateTime' => $bookingpress_end_date_time,
                                            'timeZone' => $user_timezone
                                        )
                                    );
                                    if( !empty( $bookingpress_event_location ) ){
                                        $event_data['location'] = array(
                                            'displayName' => $bookingpress_event_location,
                                            'address' => array(
                                                'street' => $bookingpress_event_location
                                            )
                                        );
                                    }
                        
                                    $args = array(
                                        'timeout' => 5000,
                                        'headers' => array(
                                            'Authorization' => 'Bearer '.$staff_access_token,
                                            'Content-Type'  => 'application/json'
                                        ),
                                        'body' => json_encode( $event_data )
                                    );                                    
                                    $args['method'] = 'PATCH';
                                    $outlook_event_url = 'https://graph.microsoft.com/v1.0/me/calendar/events/'.$bookingpress_event_id; 
                                    $response = wp_remote_post( $outlook_event_url, $args );                   

                                    if( is_wp_error( $response ) ){
                                        $debug_log_data = array(
                                            'outlook_calendar_message' => $response->get_error_message(),
                                            'outlook_calendar_log_placement' => 'error while updating event on Outlook calendar',
                                            'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                                        );
                                        do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'Outlook Calendar event updation failed for change appointment data', $debug_log_data, $bookingpress_debug_integration_log_id );
                                        return;
                                    }
                                }
                            }
                        }
                    } else {
                        
                        if($appointment_status == '3' || $appointment_status == '4' ) {

                            $bpa_other_bookings = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id != %d AND bookingpress_outlook_calendar_event_id = %s AND bookingpress_appointment_status != %d AND bookingpress_appointment_status != %d", $appointment_id, $bookingpress_event_id, 3, 4 ) );

                            $wpdb->update(
                                $tbl_bookingpress_appointment_bookings,
                                array(
                                    'bookingpress_outlook_calendar_event_id' => ''
                                ),
                                array(
                                    'bookingpress_appointment_booking_id' => $appointment_id
                                )
                            );

                            if($bpa_other_bookings > 0) {
                                
                                $bookingpress_event_title = $this->bookingpress_outlook_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['outlook_calendar_event_title'], $appointment_data );
                                
                                $bookingpress_event_description = $this->bookingpress_outlook_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['outlook_calendar_event_description'], $appointment_data );

                                $bookingpress_event_location = $this->bookingpress_retrieve_location_field_data( $bookingpress_addon_popup_field_form['outlook_calendar_event_location'], $appointment_data );
                                
                                $event_data = array(
                                    'subject' => $bookingpress_event_title,
                                    'body' => array(
                                        'contentType' => 'HTML',
                                        'content' => $bookingpress_event_description
                                    )
                                );
                                if( !empty( $bookingpress_event_location ) ){
                                    $event_data['location'] = array(
                                        'displayName' => $bookingpress_event_location,
                                        'address' => array(
                                            'street' => $bookingpress_event_location
                                        )
                                    );
                                }
                                $args = array(
                                    'timeout' => 5000,
                                    'headers' => array(
                                        'Authorization' => 'Bearer '.$staff_access_token,
                                        'Content-Type'  => 'application/json'
                                    ),
                                    'body' => json_encode( $event_data )
                                );   
                                $args['method'] = 'PATCH';                    
                                $outlook_event_url = 'https://graph.microsoft.com/v1.0/me/calendar/events/'.$bookingpress_event_id; 
                                $response = wp_remote_post( $outlook_event_url, $args );
                
                                if( is_wp_error( $response ) ){
                                    $debug_log_data = array(
                                        'outlook_calendar_message' => $response->get_error_message(),
                                        'outlook_calendar_log_placement' => 'error while updating event on Outlook calendar',
                                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                                    );
                                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'Outlook Calendar event updation failed', $debug_log_data, $bookingpress_debug_integration_log_id );
                                    return;
                                }

                            } else {

                                $is_exist_event = $this->bookignpress_is_outlook_event_exist($calendarId,$bookingpress_event_id,$staff_access_token);
                                if($is_exist_event == 0) {
                                    return;
                                }

                                $outlook_event_url = 'https://graph.microsoft.com/v1.0/me/calendars/'.$calendarId.'/events/'.$bookingpress_event_id .'/cancel';
                                $args = array(
                                    'timeout' => 4500,
                                    'headers' => array(
                                        'Authorization' => 'Bearer ' . $staff_access_token,
                                    )
                                );                
                                $args['headers']['Content-Type'] = 'application/json';
                                $args['method'] = 'POST';
                                $args['body'] = json_encode(
                                    array(
                                        'Comment' => $appointment_status
                                    )
                                );
                
                                $resp = wp_remote_request( $outlook_event_url, $args );
                
                                if( is_wp_error( $resp ) ){
                                    $debug_log_data = array(
                                        'outlook_calendar_message' => $resp->get_error_message(),
                                        'outlook_calendar_log_placement' => 'error while cancelling events for Outlook calendar',
                                        'outlook_calendar_status' => $appointment_status,
                                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                                    );
                                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'Outlook Calendar event cancelling failed', $debug_log_data, $bookingpress_debug_integration_log_id );
                                    return;
                                }
                
                                if( 200 != $resp['response']['code'] ){
                                    $debug_log_data = array(
                                        'outlook_calendar_message' => $resp,
                                        'outlook_calendar_log_placement' => 'status not 200 OK while cancelling events from Outlook calendar for status change',
                                        'outlook_calendar_status' => $appointment_status,
                                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                                    );
                                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'status not 200 OK while cancelling Outlook Calendar event for status change', $debug_log_data, $bookingpress_debug_integration_log_id );
                                    return;
                                } else {
                                    $debug_log_data = array(
                                        'outlook_calendar_message' => $resp,
                                        'outlook_calendar_log_placement' => 'status 200 OK while cancelling events from Outlook calendar for status change',
                                        'outlook_calendar_status' => $appointment_status,
                                        'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                                    );
                                    do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'status 200 OK while cancelling Outlook Calendar event for status change', $debug_log_data, $bookingpress_debug_integration_log_id );
                                }                                             
                            }
                        } else {
                            $bookingpress_event_title = $this->bookingpress_outlook_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['outlook_calendar_event_title'], $appointment_data );
                                
                            $bookingpress_event_description = $this->bookingpress_outlook_calendar_replace_shortcode( $bookingpress_addon_popup_field_form['outlook_calendar_event_description'], $appointment_data );

                            $bookingpress_event_location = $this->bookingpress_retrieve_location_field_data( $bookingpress_addon_popup_field_form['outlook_calendar_event_location'], $appointment_data );
                            
                            $event_data = array(
                                'subject' => $bookingpress_event_title,
                                'body' => array(
                                    'contentType' => 'HTML',
                                    'content' => $bookingpress_event_description
                                )
                            );
                            if( !empty( $bookingpress_event_location ) ){
                                $event_data['location'] = array(
                                    'displayName' => $bookingpress_event_location,
                                    'address' => array(
                                        'street' => $bookingpress_event_location
                                    )
                                );
                            }
                            $args = array(
                                'timeout' => 5000,
                                'headers' => array(
                                    'Authorization' => 'Bearer '.$staff_access_token,
                                    'Content-Type'  => 'application/json'
                                ),
                                'body' => json_encode( $event_data )
                            );   
                            $args['method'] = 'PATCH';                    
                            $outlook_event_url = 'https://graph.microsoft.com/v1.0/me/calendar/events/'.$bookingpress_event_id; 
                            $response = wp_remote_post( $outlook_event_url, $args );
            
                            if( is_wp_error( $response ) ){
                                $debug_log_data = array(
                                    'outlook_calendar_message' => $response->get_error_message(),
                                    'outlook_calendar_log_placement' => 'error while updating event on Outlook calendar',
                                    'backtrace_summary' => wp_debug_backtrace_summary( null, 0, false )
                                );
                                do_action( 'bookingpress_integration_log_entry', 'outlook_calendar_debug_logs', '', 'Outlook Calendar Integration', 'Outlook Calendar event updation failed', $debug_log_data, $bookingpress_debug_integration_log_id );
                                return;
                            }
                        }
                    }
                }    

            }

        }

        function bookingpress_add_outlook_calendar_integration_logs( $bookingpress_integration_debug_logs_arr ){

            $bookingpress_integration_debug_logs_arr[] = array(
                'integration_name' => __('Outlook Calendar Debug Logs', 'bookingpress-outlook-calendar'),
                'integration_key' => 'outlook_calendar_debug_logs'
            );

            return $bookingpress_integration_debug_logs_arr;
        }

        function bookingpress_add_outlook_calendar_dynamic_data_fields( $bookingpress_dynamic_setting_data_fields ){
            
            global $BookingPress, $bookingpress_pro_staff_members,$bookingpress_notification_duration, $wpdb, $tbl_bookingpress_form_fields, $bookingpress_global_options;
            $this->bookingpress_global_data = $bookingpress_global_options->bookingpress_global_options();

            $bookingpress_dynamic_setting_data_fields['bookingpress_tab_list'][] = array(
                'tab_value' => 'outlook_calendar',
                'tab_name' => esc_html__('Outlook Calendar', 'bookingpress-outlook-calendar'),
            );

            $bookingpress_dynamic_setting_data_fields['debug_log_setting_form']['outlook_calendar_debug_logs'] = false;
            $bookingpress_dynamic_setting_data_fields['bookingpress_outlook_calendar']['outlook_calendar_client_id'] = '';
            $bookingpress_dynamic_setting_data_fields['bookingpress_outlook_calendar']['outlook_calendar_client_secret'] = '';
            $bookingpress_dynamic_setting_data_fields['bookingpress_outlook_calendar']['outlook_calendar_event_title'] = '';
            $bookingpress_dynamic_setting_data_fields['bookingpress_outlook_calendar']['outlook_calendar_event_description'] = '';
            $bookingpress_dynamic_setting_data_fields['bookingpress_outlook_calendar']['outlook_calendar_event_location'] = '';
            $bookingpress_dynamic_setting_data_fields['bookingpress_outlook_calendar']['outlook_calendar_max_event'] = '';

            $is_staffmember_activated = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();
			$bookingpress_dynamic_setting_data_fields['is_staffmember_activated'] = $is_staffmember_activated;
          
            $bookingpress_dynamic_setting_data_fields['bookingpress_outlook_calendar_customer_placeholder'] = json_decode($this->bookingpress_global_data['customer_placeholders'],true);
            $bookingpress_dynamic_setting_data_fields['bookingpress_outlook_calendar_service_placeholder'] = json_decode($this->bookingpress_global_data['service_placeholders'],true);
            $bookingpress_dynamic_setting_data_fields['bookingpress_outlook_calendar_company_placeholder'] = json_decode($this->bookingpress_global_data['company_placeholders'],true);
            $bookingpress_dynamic_setting_data_fields['bookingpress_outlook_calendar_staff_placeholder'] = json_decode($this->bookingpress_global_data['staff_member_placeholders'],true);

            $bpa_form_fields = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_field_label,bookingpress_form_field_id FROM {$tbl_bookingpress_form_fields} WHERE ( bookingpress_field_type = %s OR bookingpress_field_type = %s ) AND bookingpress_is_customer_field = %d ORDER BY bookingpress_field_position ASC", 'text', 'textarea', 0 ) );
            
            $bpa_form_fields_arr = array(
                array(
                    'value' => '',
                    'label' => esc_html__( 'Select Field','bookingpress-outlook-calendar' )
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
            $bookingpress_dynamic_setting_data_fields['bookingpress_ocalendar_form_fields'] = $bpa_form_fields_arr;

            $bookingpress_appointment_placeholders = json_decode($this->bookingpress_global_data['appointment_placeholders'],true);
            $bookingpress_appointment_custom_field_placeholder = json_decode($this->bookingpress_global_data['custom_fields_placeholders'],true);
            $bookingpress_appointment_field_list[] = array(
                'field_group_name' => esc_html__('Basic fields', 'bookingpress-outlook-calendar'),
                'field_list' => $bookingpress_appointment_placeholders
            );  
            if(!empty($bookingpress_appointment_custom_field_placeholder)) {
                $bookingpress_appointment_field_list[] = array(
                    'field_group_name'  => esc_html__('Advanced fields', 'bookingpress-outlook-calendar'),
                    'field_list' => $bookingpress_appointment_custom_field_placeholder
                );
            }                                     
            $bookingpress_dynamic_setting_data_fields['bookingpress_outlook_calendar_appointment_placeholder'] = $bookingpress_appointment_field_list;

            $bookingpress_dynamic_setting_data_fields['bookingpress_outlook_rules'] = array(                               
                'outlook_calendar_client_id'  => array(
                    array(
                        'required' => true,
                        'message'  => __( 'Please Enter the Client ID', 'bookingpress-outlook-calendar' ),
                        'trigger'  => 'change',
                    ),
                ),
                'outlook_calendar_client_secret' => array(
                    array(
                        'required' => true,
                        'message'  => __( 'Please Enter the Client Secret', 'bookingpress-outlook-calendar' ),
                        'trigger'  => 'change',
                    ),
                ),
            );           

            return $bookingpress_dynamic_setting_data_fields;
        }     

    }

    global $bookingpress_outlook_calendar;
	$bookingpress_outlook_calendar = new bookingpress_outlook_calendar;
}
