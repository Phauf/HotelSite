<?php
// webba booking email notifications class and helper functions
if ( ! defined( 'ABSPATH' ) ) exit;
class WBK_Email_Notifications {
	// send email to customer status
	protected $customer_book_status;
	// send email to admin status
	protected $admin_book_status;
	// customer email message
	protected $secondary_email_message;
	// admin email message
	protected $admin_email_message;
	// customer email subject
	protected $customer_email_subject;
	// admin email subject
	protected $admin_email_subject;
	// from: email
	protected $from_email;
	// from: name
	protected $from_name;
	// service id
	protected $service_id;
	// appointment
	protected $appointment_id;
    // super admin email
    protected $super_admin_email;
    // current category (0 if not definded)
    protected $current_category;
	// service_id: int
	// appointment_id: int
	public function __construct( $service_id, $appointment_id, $current_category = 0 ) {
		if( $appointment_id != 0) {
			WBK_Db_Utils::switchLanguageByAppointmentId( $appointment_id );
		}
		$this->customer_book_status = get_option( 'wbk_email_customer_book_status', '' );
		$this->admin_book_status = get_option( 'wbk_email_admin_book_status', '' );
		$this->customer_daily_status = get_option( 'wbk_email_customer_daily_status', '' );
		$this->admin_daily_status = get_option( 'wbk_email_admin_daily_status', '' );
		$this->secondary_book_status = get_option( 'wbk_email_secondary_book_status', '' );
		$this->customer_email_message = get_option( 'wbk_email_customer_book_message', '' );
		$this->admin_email_message = get_option( 'wbk_email_admin_book_message', '' );
		$this->customer_daily_message = get_option( 'wbk_email_customer_daily_message', '' );
		$this->admin_daily_message = get_option( 'wbk_email_admin_daily_message', '' );
		$this->secondary_email_message = get_option( 'wbk_email_secondary_book_message', '' );
		$this->customer_email_subject = get_option( 'wbk_email_customer_book_subject', '' );
		$this->admin_email_subject = get_option( 'wbk_email_admin_book_subject', '' );
		$this->customer_daily_subject = get_option( 'wbk_email_customer_daily_subject', '' );
		$this->admin_daily_subject = get_option( 'wbk_email_admin_daily_subject', '' );
		$this->secondary_email_subject = get_option( 'wbk_email_secondary_book_subject', '' );
		$this->super_admin_email = get_option( 'wbk_super_admin_email', '' );
		$this->from_email = get_option( 'wbk_from_email' );
		$this->from_name = get_option( 'wbk_from_name' );
		$this->service_id = $service_id;
		$this->appointment_id = $appointment_id;
		$this->customer_approve_status = get_option( 'wbk_email_customer_approve_status', '' );
		$this->customer_approve_status_copy = get_option( 'wbk_email_customer_approve_copy_status', '' );
		$this->customer_approve_subject = get_option( 'wbk_email_customer_approve_subject', '' );
		$this->customer_approve_message = get_option( 'wbk_email_customer_approve_message', '' );
		$this->admin_cancel_status = get_option( 'wbk_email_adimn_appointment_cancel_status', '' );
		$this->admin_cancel_subject = get_option( 'wbk_email_adimn_appointment_cancel_subject', __( 'Appointment canceled', 'wbk' ) );
		$this->admin_cancel_message = get_option( 'wbk_email_adimn_appointment_cancel_message', '<p>#customer_name canceled the appointment with #service_name on #appointment_day at #appointment_time</p>' );
		$this->customer_cancel_status = get_option( 'wbk_email_customer_appointment_cancel_status', '' );
		$this->customer_cancel_subject = get_option( 'wbk_email_customer_appointment_cancel_subject', __( 'Your appointment canceled', 'wbk' ) );
		$this->customer_cancel_message = get_option( 'wbk_email_customer_appointment_cancel_message', '<p>Your appointment with #service_name on #appointment_day at #appointment_time has been canceled</p>' );
	 	$this->customer_invoice_subject = get_option( 'wbk_email_customer_invoice_subject', __( 'Invoice', 'wbk' ) );
	 	$this->current_category = $current_category;
 	}
 	public function set_email_content_type() {
 		return 'text/html';
 	}
 	public function send( $event, $send_single = FALSE ) {

 		global $wbk_wording;
		$date_format = WBK_Date_Time_Utils::getDateFormat();
		$time_format = WBK_Date_Time_Utils::getTimeFormat();
		switch ( $event ) {
		    case 'book':
				$appointment = new WBK_Appointment_deprecated();
				if ( !$appointment->setId( $this->appointment_id ) ) {
					return;
				}
				if ( !$appointment->load() ) {
					return;
				}
				$service = new WBK_Service_deprecated();
				if ( !$service->setId( $this->service_id ) ) {
					return;
				}
				if ( !$service->load() ) {
					return;
				}
		    	// email to cutomer

		    	if( $this->customer_book_status != '' ) {
		    		if(  $send_single == TRUE || ( get_option( 'wbk_multi_booking', 'disabled' ) != 'disabled' &&  get_option( 'wbk_email_customer_book_multiple_mode', 'foreach' ) == 'foreach' ) || get_option( 'wbk_multi_booking', 'disabled' ) == 'disabled'  ){
				    	//	validation
				    	if ( !WBK_Validator::checkStringSize( $this->customer_email_message, 1, 50000 ) ||
				    		 !WBK_Validator::checkStringSize( $this->customer_email_subject, 1, 200 ) ||
				    		 !WBK_Validator::checkEmail( $this->from_email ) ||
				    		 !WBK_Validator::checkStringSize( $this->from_name, 1, 200 )
				    	   ) {

				    	   return;
				        }

				        if( $service->getNotificationTemplate() != 0 ){
				        	$template = WBK_Db_Utils::getEmailTemplate( $service->getNotificationTemplate() ) ;
							$template = apply_filters( 'wbk_notification_template', $template, $this->appointment_id );
				        	if( !is_null( $template ) ){
				        		$this->customer_email_message = htmlspecialchars_decode( stripslashes( $template ));
				        	}
				        }

	 			        $message = $this->message_placeholder_processing( $this->customer_email_message, $appointment, $service  );
	    				$subject = 	$this->subject_placeholder_processing( $this->customer_email_subject, $appointment, $service );

						$message =  wbk_cleanup_loop( $message );
						$subject =  wbk_cleanup_loop( $subject );

 		       			$headers[] = 'From: ' . $this->from_name . ' <' . $this->from_email .'>';
						if( get_option( 'wbk_email_override_replyto', 'true' ) == 'true' ){
							$headers[] = 'Reply-To: '.  $this->from_name . ' <' . $service->getEmail() . '>';
						}
						$attachment = array();

 		       			if ( get_option( 'wbk_email_customer_book_status_generate_ical', '' ) != '' ) {
							$ical = WBK_Ical::generateICal( array( $this->appointment_id), 'customer' );
							if ( $ical  != '' ) {
								$attachment[] = $ical;
							}
						}

						add_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );
				    	wp_mail( $appointment->getEmail(), $subject, $message, $headers, $attachment );

	 					remove_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );
 					}
			    }
		    	// email to admin
		    	if( $this->admin_book_status != '' ) {
		    		if( $send_single == TRUE || ( get_option( 'wbk_multi_booking', 'disabled' ) != 'disabled' &&  get_option( 'wbk_email_admin_book_multiple_mode', 'foreach' ) == 'foreach' ) || get_option( 'wbk_multi_booking', 'disabled' ) == 'disabled' ){
				    	//	validation
				    	if ( !WBK_Validator::checkStringSize( $this->admin_email_message, 1, 50000 ) ||
				    		 !WBK_Validator::checkStringSize( $this->admin_email_subject, 1, 200 ) ||
				    		 !WBK_Validator::checkEmail( $this->from_email ) ||
				    		 !WBK_Validator::checkStringSize( $this->from_name, 1, 200 )
				    	   ) {
				    	   return;
				        }
					    $message = $this->message_placeholder_processing( $this->admin_email_message, $appointment, $service );
						$subject = 	$this->subject_placeholder_processing( $this->admin_email_subject, $appointment, $service );

						$message = str_replace( '[appointment_loop_start]', '', $message );
						$message = str_replace( '[appointment_loop_end]', '', $message );
						$subject = str_replace( '[appointment_loop_start]', '', $subject );
						$subject = str_replace( '[appointment_loop_end]', '', $subject );

						$headers = array();
						$headers[] = 'From: ' . $this->from_name . ' <' . $this->from_email .'>';
						if( get_option( 'wbk_email_override_replyto', 'true' ) == 'true' ){
							$headers[] = 'Reply-To: '.  $appointment->getName() . ' <' . $appointment->getEmail() . '>';
						}
						// attachments
						if( get_option( 'wbk_allow_attachemnt', 'no' ) == 'yes' ){
							$attachment =  $appointment->getAttachment();
							if( $attachment == '' ){
								$attachment = array();
							} else {
								$attachment = json_decode( $attachment );
							}
						} else {
							$attachment = array();
						}
						/* START: ICal Generation   */
						if ( get_option( 'wbk_email_admin_book_status_generate_ical', '' ) != '' ) {
							$ical = WBK_Ical::generateICal(array($this->appointment_id));
							if ($ical  != '' ) $attachment[] = $ical;
						}
						/* END: ICal Generation   */
						add_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );
				    	wp_mail( $service->getEmail(), $subject, $message, $headers, $attachment );
	 		 			if ( $this->super_admin_email != '' ) {
	 						wp_mail(  $this->super_admin_email, $subject, $message, $headers, $attachment );
	 					}

	 					remove_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );

						if(  $appointment->getAttachment() != '' ){
							$file = json_decode( $appointment->getAttachment() );
							if( is_array( $file ) ){
								$file = $file[0];
								if( get_option( 'wbk_delete_attachemnt', 'no' ) == 'yes' ){
									if( file_exists( $file ) ){
										unlink( $file );
									}
								}
							}
						}
	 				}
			    }
		    break;
		    case 'daily':
			    	//	validation
			    	if ( !WBK_Validator::checkStringSize( $this->admin_daily_message, 1, 50000 ) ||
			    		 !WBK_Validator::checkStringSize( $this->admin_daily_subject, 1, 200 ) ||
			    		 !WBK_Validator::checkEmail( $this->from_email ) ||
			    		 !WBK_Validator::checkStringSize( $this->from_name, 1, 100 )
			    	   ) {
				    	$this->admin_daily_status = false;
			        }
 			    	if ( !WBK_Validator::checkStringSize( $this->customer_daily_message, 1, 50000 ) ||
			    		 !WBK_Validator::checkStringSize( $this->customer_daily_subject, 1, 200 ) ||
			    		 !WBK_Validator::checkEmail( $this->from_email ) ||
			    		 !WBK_Validator::checkStringSize( $this->from_name, 1, 100 )
			    	   ) {
				    	$this->customer_daily_status = false;
			        }
					$headers = 'From: ' . $this->from_name . ' <' . $this->from_email .'>' . "\r\n";
					add_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );
					//iterating over services
					$service_ids = WBK_Db_Utils::getServices();
					$customer_remider_ids = array();
					foreach ( $service_ids as $service_id ) {
					 	$service = new WBK_Service_deprecated();
						if ( !$service->setId( $service_id ) ) {
							continue;
						}
						if ( !$service->load() ) {
							continue;
						}
						// send to the admin of the service
						$appointment_ids =	WBK_Db_Utils::getTomorrowAppointmentsForService( $service_id );
						$agenda = '<table style="text-align:left;">';
						$agenda .= '<tr>
									  <th style="margin:0;background:#ccc;border:1px solid #fff;padding:5px;">' . __( 'Service', 'wbk')  . '</th>
									  <th style="margin:0;background:#ccc;border:1px solid #fff;padding:5px;">' . __( 'Time', 'wbk')  . '</th>
									  <th style="margin:0;background:#ccc;border:1px solid #fff;padding:5px;">' . __( 'Name', 'wbk')  . '</th>
									  <th style="margin:0;background:#ccc;border:1px solid #fff;padding:5px;">' . __( 'Email', 'wbk')  . '</th>
									  <th style="margin:0;background:#ccc;border:1px solid #fff;padding:5px;">' . __( 'Phone', 'wbk')  . '</th>
   								  	  <th style="margin:0;background:#ccc;border:1px solid #fff;padding:5px;">' . __( 'Places booked', 'wbk')  . '</th>
									  <th style="margin:0;background:#ccc;border:1px solid #fff;padding:5px;">' . __( 'Status', 'wbk')  . '</th>
									  <th style="margin:0;background:#ccc;border:1px solid #fff;padding:5px;">' . __( 'Additional information', 'wbk')  . '</th>
								   </tr>';
                        $app_found = false;
                        $days_before = intval( get_option( 'wbk_email_reminder_days', '1' ) );
						foreach ( $appointment_ids as $appointment_id ) {
							WBK_Db_Utils::switchLanguageByAppointmentId( $appointment_id );
							$this->customer_daily_message = get_option( 'wbk_email_customer_daily_message', '' );
							$this->customer_daily_subject = get_option( 'wbk_email_customer_daily_subject', '' );

						 	$appointment = new WBK_Appointment_deprecated();
							if ( !$appointment->setId( $appointment_id ) ) {
								continue;
							}
							if ( !$appointment->load() ) {
								continue;
							}
							if ( get_option( 'wbk_email_reminders_only_for_approved', '' ) == 'true' ){
								$status =  WBK_Db_Utils::getStatusByAppointmentId( $appointment_id );
								$skip_status = array( 'pending', 'paid', 'arrived' );
								if( in_array( $status, $skip_status) ){
									continue;
								}

							}
                            $app_found = true;
							$time_format = WBK_Date_Time_Utils::getTimeFormat();

							date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
							$time_string = wp_date( $time_format, $appointment->getTime(), new DateTimeZone( date_default_timezone_get() ) );
							date_default_timezone_set('UTC');

							$extra_data = trim( $appointment->getExtra() );
							$extra_content = '';
							if( $extra_data != '' ){
								$extra = json_decode( $extra_data );
								foreach( $extra as $item ){
						    		if( count( $item ) <> 3 ){
						    			continue;
						    		}
									$extra_content .= $item[1] . ': ' . $item[2] . '. ';
								}
							}
							$status = WBK_Db_Utils::getStatusByAppointmentId( $appointment->getId() );
							$status_list = WBK_Db_Utils::getAppointmentStatusList();
							if( isset( $status_list[$status] ) ){
								$status = $status_list[$status][0];
							} else {
								$status = '';
							}

							$agenda .= '<tr>
											  <td style="margin:0;border:1px solid #ccc;padding:5px;">' . $service->getName() . '</td>
											  <td style="margin:0;border:1px solid #ccc;padding:5px;">' . $time_string . '</td>
											  <td style="margin:0;border:1px solid #ccc;padding:5px;">' . $appointment->getName() . '</td>
											  <td style="margin:0;border:1px solid #ccc;padding:5px;">' . $appointment->getEmail()  . '</td>
											  <td style="margin:0;border:1px solid #ccc;padding:5px;">' . $appointment->getPhone() . '</td>
								   		      <td style="margin:0;border:1px solid #ccc;padding:5px;">'.  $appointment->getQuantity() . '</td>
											  <td style="margin:0;border:1px solid #ccc;padding:5px;">'.  $status . '</td>
											  <td style="margin:0;border:1px solid #ccc;padding:5px;">' . $extra_content. '</td>
									   </tr>';
							if( $days_before == 1 ){
								if( $this->customer_daily_status ){
									$customer_daily_message = '';
									if( $service->getReminderTemplate() != 0 ){
							        	$template = WBK_Db_Utils::getEmailTemplate( $service->getReminderTemplate() ) ;
							        	if( !is_null( $template ) ){
							        		$customer_daily_message = htmlspecialchars_decode( stripslashes( $template ));
							        	}
							        } else {
							        	$customer_daily_message = $this->customer_daily_message;
							        }
							       	date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
									if( !WBK_Validator::checkEmailLoop( $customer_daily_message ) ){
										$customer_daily_message = $this->message_placeholder_processing( $customer_daily_message, $appointment, $service );
										$subject = 	$this->subject_placeholder_processing( $this->customer_daily_subject, $appointment, $service );
										$subject = wbk_cleanup_loop( $subject );
										$customer_daily_message = wbk_cleanup_loop( $customer_daily_message );
										date_default_timezone_set('UTC');
										wp_mail( $appointment->getEmail(), $subject, $customer_daily_message, $headers );
									} else {
										$customer_remider_ids[ $appointment->getEmail() ][] = $appointment_id;
									}
								}
							}


						}
						$agenda .= '</table>';
						if ( $this->admin_daily_status && $app_found ){

				     		$category_names = WBK_Db_Utils::getCategoryNamesByService( $service->getId() );
							$admin_daily_message = str_replace( '#service_name', $service->getName(), $this->admin_daily_message );
							$admin_daily_message = str_replace( '#category_names', $category_names, $admin_daily_message );
							$admin_daily_message = str_replace( '#tomorrow_agenda', $agenda, $admin_daily_message );
							date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
							$admin_daily_message = WBK_Placeholder_Processor::process_placeholders( $admin_daily_message, $appointment_ids );
							date_default_timezone_set(  'UTC' );

					    	wp_mail( $service->getEmail(), $this->admin_daily_subject, $admin_daily_message, $headers );
							if ( $this->super_admin_email != '' ) {
								wp_mail( $this->super_admin_email, $this->admin_daily_subject, $admin_daily_message, $headers );

							}
						}
						if( $service->getReminderTemplate() != 0 ){
				        	$template = WBK_Db_Utils::getEmailTemplate( $service->getReminderTemplate() );
				        	if( !is_null( $template ) ){
				        		$customer_daily_message = htmlspecialchars_decode( stripslashes( $template ));
				        	}
				        } else {
				        	$customer_daily_message = $this->customer_daily_message;
				        }
						if( $days_before == 0){
							if( $this->customer_daily_status ){
								$appointment_ids =	WBK_Model_Utils::get_booking_ids_for_today_by_service( $service_id );
								foreach ( $appointment_ids as $appointment_id ) {
									if ( get_option( 'wbk_email_reminders_only_for_approved', '' ) == 'true' ){
										$status =  WBK_Db_Utils::getStatusByAppointmentId( $appointment_id );
										$skip_status = array( 'pending', 'paid', 'arrived' );
										if( in_array( $status, $skip_status ) ){
											continue;
										}
									}
									WBK_Db_Utils::switchLanguageByAppointmentId( $appointment_id );
									if( $service->getReminderTemplate() != 0 ){
							        	$template = WBK_Db_Utils::getEmailTemplate( $service->getReminderTemplate() ) ;
							        	if( !is_null( $template ) ){
							        		$customer_daily_message = htmlspecialchars_decode( stripslashes( $template ));
							        	}
							        } else {
							        	$customer_daily_message = $this->customer_daily_message;
							        }
									$customer_daily_subject = get_option( 'wbk_email_customer_daily_subject', '' );
									WBK_Db_Utils::switchLanguageByAppointmentId( $appointment_id );
								 	$appointment = new WBK_Appointment_deprecated();
									if ( !$appointment->setId( $appointment_id ) ) {
										continue;
									}
									if ( !$appointment->load() ) {
										continue;
									}
							       	date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
									if( !WBK_Validator::checkEmailLoop( $customer_daily_message ) ){
										$customer_daily_message = $this->message_placeholder_processing( $customer_daily_message, $appointment, $service );
										$subject = 	$this->subject_placeholder_processing( $customer_daily_subject, $appointment, $service );
										$customer_daily_message = wbk_cleanup_loop( $customer_daily_message );
										$subject = wbk_cleanup_loop( $subject );
										date_default_timezone_set('UTC');
										wp_mail( $appointment->getEmail(), $subject, $customer_daily_message, $headers );
									} else {
										$customer_remider_ids[ $appointment->getEmail() ][] = $appointment_id;

									}
								}
							}
						}
						if( $days_before > 1 ){
							if( $this->customer_daily_status ){
								$appointment_ids =	WBK_Db_Utils::getFutureAppointmentsForService( $service_id, $days_before );
								foreach ( $appointment_ids as $appointment_id ) {
									if ( get_option( 'wbk_email_reminders_only_for_approved', '' ) == 'true' ){
										$status =  WBK_Db_Utils::getStatusByAppointmentId( $appointment_id );
										$skip_status = array( 'pending', 'paid', 'arrived' );
										if( in_array( $status, $skip_status ) ){
											continue;
										}
									}
									WBK_Db_Utils::switchLanguageByAppointmentId( $appointment_id );
									if( $service->getReminderTemplate() != 0 ){
							        	$template = WBK_Db_Utils::getEmailTemplate( $service->getReminderTemplate() ) ;
							        	if( !is_null( $template ) ){
							        		$customer_daily_message = htmlspecialchars_decode( stripslashes( $template ));
							        	}
							        } else {
							        	$customer_daily_message = $this->customer_daily_message;
							        }
									$customer_daily_subject = get_option( 'wbk_email_customer_daily_subject', '' );
									WBK_Db_Utils::switchLanguageByAppointmentId( $appointment_id );
								 	$appointment = new WBK_Appointment_deprecated();
									if ( !$appointment->setId( $appointment_id ) ) {
										continue;
									}
									if ( !$appointment->load() ) {
										continue;
									}
							       	date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
									if( !WBK_Validator::checkEmailLoop( $customer_daily_message ) ){
										$customer_daily_message = $this->message_placeholder_processing( $customer_daily_message, $appointment, $service );
										$subject = 	$this->subject_placeholder_processing( $customer_daily_subject, $appointment, $service );
										$customer_daily_message = wbk_cleanup_loop( $customer_daily_message );
										$subject = wbk_cleanup_loop( $subject );
										date_default_timezone_set('UTC');
										wp_mail( $appointment->getEmail(), $subject, $customer_daily_message, $headers );
									} else {
										$customer_remider_ids[ $appointment->getEmail() ][] = $appointment_id;

									}
								}
							}
						}

 					}
					if( count( $customer_remider_ids ) > 0 ){
						foreach( $customer_remider_ids as $appointment_ids ){
							if( $service->getReminderTemplate() != 0 ){
					        	$template = WBK_Db_Utils::getEmailTemplate( $service->getReminderTemplate() );
					        	if( !is_null( $template ) ){
					        		$customer_daily_message = htmlspecialchars_decode( stripslashes( $template ));
					        	}
					        } else {
					        	$customer_daily_message = $this->customer_daily_message;
					        }
							$customer_daily_subject = get_option( 'wbk_email_customer_daily_subject', '' );
							$appointment = WBK_Db_Utils::initAppointmentById( $appointment_ids[0] );
						 	if( $appointment != FALSE ){
								$this->sendMultipleNotification($appointment_ids, $customer_daily_message, $customer_daily_subject, $appointment->getEmail() );
							}
						}
					}
 					remove_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );
		    break;
		}
 	}
	public function sendMultipleNotification( $appointment_ids, $message, $subject, $recipient, $generate_ical = '' ) {
		if( count( $appointment_ids ) > 0 ){
			WBK_Db_Utils::switchLanguageByAppointmentId( $appointment_ids[0] );
		}
		date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
	 	$date_format = WBK_Date_Time_Utils::getDateFormat();
		$time_format = WBK_Date_Time_Utils::getTimeFormat();
		if( count( $appointment_ids ) == 0 ){
			date_default_timezone_set( 'UTC' );
			return;
		}
	 	//	validation
	 	if ( !WBK_Validator::checkStringSize( $message, 1, 50000 ) ){
			date_default_timezone_set( 'UTC' );

			return;
		}
		if (!WBK_Validator::checkStringSize( $subject, 1, 200 ) ){
			date_default_timezone_set( 'UTC' );

			return;
		}
		if ( !WBK_Validator::checkEmail( $this->from_email )  ){
			date_default_timezone_set( 'UTC' );

			return;
		}
		if (  !WBK_Validator::checkStringSize( $this->from_name, 1, 200 )  ){
			date_default_timezone_set( 'UTC' );

			return;
		}
		// sort ids
	    // get total price
	    $price_format = get_option( 'wbk_payment_price_format', '$#price' );
	    $total = WBK_Price_Processor::get_total_tax_fess( $appointment_ids );
		 
		$total_price = str_replace( '#price', number_format( $total,  get_option( 'wbk_price_fractional', '2' ), get_option( 'wbk_price_separator', '.' ), ''  ), $price_format );
 		// end get total price
		// start processing message
		$looped_html = '';
		$token_arr = array();
		$token_arr_admin = array();
		$attachment_all = array();
		$looped = '';
		$app_price_total  = 0;
		if( WBK_Validator::checkEmailLoop( $message ) ){
			$looped = $this->get_string_between( $message, '[appointment_loop_start]', '[appointment_loop_end]' );
		 	foreach ( $appointment_ids as $appointment_id ){
				$appointment = new WBK_Appointment_deprecated();
				if ( !$appointment->setId( $appointment_id ) ) {
					continue;
				}
				if ( !$appointment->load() ) {
					continue;
				}
				$app_price_total += WBK_Db_Utils::getAppointmentMomentPrice( $appointment->getId() );
				$looped_html .= WBK_Db_Utils::message_placeholder_processing_multi_service( $looped, $appointment, null );
				$token_arr[] = WBK_Db_Utils::getTokenByAppointmentId( $appointment_id );
				$token_arr_admin[] = WBK_Db_Utils::getAdminTokenByAppointmentId( $appointment_id );
				if( $generate_ical != 'customer' ){
					if( get_option( 'wbk_allow_attachemnt', 'no' ) == 'yes' ){
						$attachment =  $appointment->getAttachment();
						if( $attachment == '' ){
						} else {
							if( count( $attachment_all ) < 1 ){
								$attachment_all = json_decode( $attachment );
							}
						}
					}
				}
		 	}
		}
		$appointment_id = $appointment_ids[0];
		$appointment = new WBK_Appointment_deprecated();
		if ( !$appointment->setId( $appointment_id ) ) {
			return;
		}
		if ( !$appointment->load() ) {
			return;
		}
	 	if( count( $token_arr ) > 0 ){
	 		$multi_token = implode( '-', $token_arr );
	 	} else {
	 		$multi_token = null;
	 	}
 		if( count( $token_arr_admin ) > 0 ){
	 		$multi_token_admnin = implode( '-', $token_arr_admin );
	 	} else {
	 		$multi_token_admnin = null;
	 	}
		$search_tag =  '[appointment_loop_start]' . $looped . '[appointment_loop_end]';
		$message = str_replace( $search_tag, $looped_html, $message );

		// recalc total
 	 	$message = WBK_Db_Utils::message_placeholder_processing_multi_service( $message, $appointment, $total_price, $this->current_category, $multi_token, $multi_token_admnin, $app_price_total );
		$message = str_replace( '#selected_count', count( $appointment_ids ), $message );
 		// todo: add ranges
		$message = $this->replaceRanges( $message, $appointment_ids );
		// end processing message

		// start processing subject
		$looped_html = '';
		$token_arr = array();

		if( WBK_Validator::checkEmailLoop( $subject ) ){
			$looped = $this->get_string_between( $subject, '[appointment_loop_start]', '[appointment_loop_end]' );
		 	foreach ( $appointment_ids as $appointment_id ){
				$appointment = new WBK_Appointment_deprecated();
				if ( !$appointment->setId( $appointment_id ) ) {
					continue;
				}
				if ( !$appointment->load() ) {
					continue;
				}
				$looped_html .= WBK_Db_Utils::subject_placeholder_processing_multi_service( $looped, $appointment, null );
		 	}
		}
		if( count( $token_arr ) > 0 ){
			$multi_token = implode( '-', $token_arr );
		} else {
			$multi_token = null;
		}
		$appointment_id = $appointment_ids[0];
		$appointment = new WBK_Appointment_deprecated();
		if ( !$appointment->setId( $appointment_id ) ) {
			return;
		}
		if ( !$appointment->load() ) {
			return;
		}

		$search_tag =  '[appointment_loop_start]' . $looped . '[appointment_loop_end]';
		$subject = str_replace( $search_tag, $looped_html, $subject );
		$subject = WBK_Db_Utils::subject_placeholder_processing_multi_service( $subject, $appointment, $total_price );
		$subject = str_replace( '#selected_count', count( $appointment_ids ), $subject );
		// end processing subject
	 	$headers = 'From: ' . $this->from_name . ' <' . $this->from_email .'>' . "\r\n";
		if( $generate_ical == 'admin' ){
			$headers = array();
			$headers[] = 'From: ' . $this->from_name . ' <' . $this->from_email .'>' . "\r\n";
			if( get_option( 'wbk_email_override_replyto', 'true' ) == 'true' ){
				$headers[] = 'Reply-To: '.  $appointment->getName() . ' <' . $appointment->getEmail() . '>' . "\r\n";
			}
		}

		add_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );
		/* START: ICal Generation  */
		if ( (get_option( 'wbk_email_admin_book_status_generate_ical', '' ) != '') && $generate_ical == 'admin' ) {
			$ical = WBK_Ical::generateICal( $appointment_ids );
			if ($ical != '' ) $attachment_all[] = $ical;
		}
		if ( (get_option( 'wbk_email_customer_book_status_generate_ical', '' ) != '') && $generate_ical == 'customer' ) {
			$ical = WBK_Ical::generateICal( $appointment_ids, 'customer' );
			if ( $ical  != ''  ){
				$attachment_all[] = $ical;
			}
		}
		/* END: ICal Generation */
		wp_mail( $recipient, $subject, $message, $headers, $attachment_all );

		remove_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );
		date_default_timezone_set( 'UTC' );


		if(  $appointment->getAttachment() != '' && $generate_ical != 'customer' ){
			$file = json_decode( $appointment->getAttachment() );
			if( is_array( $file ) ){
				$file = $file[0];
				if( get_option( 'wbk_delete_attachemnt', 'no' ) == 'yes' ){
					if( file_exists( $file ) ){
						unlink( $file );
					}
				}
			}
		}
		return;
	}
	public function sendMultipleCustomerNotification( $appointment_ids ) {
		if( $this->customer_book_status != '' ) {
			if( count( $appointment_ids ) > 0 ){
				WBK_Db_Utils::switchLanguageByAppointmentId( $appointment_ids[0] );
			}
			$message = $this->customer_email_message;
		    $subject = $this->customer_email_subject;
		    $appointment = new WBK_Appointment_deprecated();
			if ( !$appointment->setId( $appointment_ids[0] ) ) {
				return;
			}
			if ( !$appointment->load() ) {
				return;
			}
			$service = WBK_Db_Utils::initServiceById( WBK_Db_Utils::getServiceIdByAppointmentId( $appointment->getId() ) );
			$template = WBK_Db_Utils::getEmailTemplate( $service->getNotificationTemplate() ) ;
			$template = apply_filters( 'wbk_notification_template', $template, $this->appointment_id );
			if( !is_null( $template ) ){
				$message = htmlspecialchars_decode( stripslashes( $template ));
			}
			$this->sendMultipleNotification( $appointment_ids, $message, $subject, $appointment->getEmail(), 'customer' );
		}
		return;
 	}
 	public function sendMultipleAdminNotification( $appointment_ids ) {
		if( $this->admin_book_status != '' ) {
			if( count( $appointment_ids ) > 0 ){
				WBK_Db_Utils::switchLanguageByAppointmentId( $appointment_ids[0] );
			}
		 	if( count( $appointment_ids ) == 0 ){
		 		return;
		 	}
		 	$message = $this->admin_email_message;
		    $subject = $this->admin_email_subject;
		    $appointment = new WBK_Appointment_deprecated();
			if ( !$appointment->setId( $appointment_ids[0] ) ) {
				return;
			}
			if ( !$appointment->load() ) {
				return;
			}
			$service = WBK_Db_Utils::initServiceById( WBK_Db_Utils::getServiceIdByAppointmentId( $appointment->getId() ) );
			$this->sendMultipleNotification( $appointment_ids, $message, $subject, $service->getEmail(), 'admin' );
			if ($this->super_admin_email != '' ) {
				$this->sendMultipleNotification( $appointment_ids, $message, $subject, $this->super_admin_email, 'admin' );
			}
		}
		return;
 	}
	public function sendMultipleToSecondary( $appointment_ids, $data ) {
		if( count( $appointment_ids ) > 0 ){
			WBK_Db_Utils::switchLanguageByAppointmentId( $appointment_ids[0] );
		}
		//	generall class validation
	   if ( !WBK_Validator::checkStringSize( $this->secondary_email_message, 1, 50000 ) ||
			!WBK_Validator::checkStringSize( $this->secondary_email_subject, 1, 200 ) ||
			!WBK_Validator::checkEmail( $this->from_email ) ||
			!WBK_Validator::checkStringSize( $this->from_name, 1, 200 )
		  ) {
		   return;
	   }
	   if( !WBK_Validator::checkEmailLoop( $this->secondary_email_message ) ){
		   return;
	   }
	   if( $this->secondary_book_status != '' ) {
		   foreach ( $data as $person ) {
		   		if( isset( $person->name ) ){
		   			$name = $person->name;
		   		}
		   		if( isset( $person->email ) ){
		   			$email = $person->email;
		   		}
		   		if ( !WBK_Validator::checkStringSize( $name, 1, 200 ) || !WBK_Validator::checkEmail( $email ) ){
		   			return;
		   		}

		   		$message =  $this->secondary_email_message;
		   		$subject = 	$this->secondary_email_subject;

		   		$message = str_replace( '#group_customer_name', $name, $message );
		   		$subject = str_replace( '#group_customer_name', $name, $subject );
				$this->sendMultipleNotification( $appointment_ids, $message, $subject, $email );

		   }
	   }
	}
	public function sendToSecondary( $data ) {
	    //	general class validation
	    if ( !WBK_Validator::checkStringSize( $this->secondary_email_message, 1, 50000 ) ||
	    	 !WBK_Validator::checkStringSize( $this->secondary_email_subject, 1, 200 ) ||
	    	 !WBK_Validator::checkEmail( $this->from_email ) ||
	    	 !WBK_Validator::checkStringSize( $this->from_name, 1, 200 )
	       ) {
	    	return;
	    }

		// data validation
		if ( !is_array( $data ) ){
			return;
		}
		foreach( $data as $person ) {
		}
		$date_format = WBK_Date_Time_Utils::getDateFormat();
		$time_format = WBK_Date_Time_Utils::getTimeFormat();
		$appointment = new WBK_Appointment_deprecated();
		if ( !$appointment->setId( $this->appointment_id ) ) {
			return;
		}
		if ( !$appointment->load() ) {
			return;
		}
		$service = new WBK_Service_deprecated();
		if ( !$service->setId( $this->service_id ) ) {
			return;
		}
		if ( !$service->load() ) {
			return;
		}
    	// email to cutomer from group
    	if( $this->secondary_book_status != '' ) {
    		foreach ( $data as $person ) {
				if( isset( $person->name ) ){
					$name = $person->name;
				}
				if( isset( $person->email ) ){
					$email = $person->email;
				}
				if ( !WBK_Validator::checkStringSize( $name, 1, 200 ) || !WBK_Validator::checkEmail( $email ) ){
					return;
				}

				$message = $this->message_placeholder_processing( $this->secondary_email_message, $appointment, $service  );
				$subject = 	$this->subject_placeholder_processing( $this->secondary_email_subject, $appointment, $service );

				$message = str_replace( '#group_customer_name', $name, $message );
				$subject = str_replace( '#group_customer_name', $name, $subject );

				$headers = 'From: ' . $this->from_name . ' <' . $this->from_email .'>' . "\r\n";
				add_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );
		    	wp_mail( $email, $subject, $message, $headers );
					remove_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );
			}
	    }
	}
	public function sendOnApprove(){
		date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
		global $wbk_wording;
		$date_format = WBK_Date_Time_Utils::getDateFormat();
		$time_format = WBK_Date_Time_Utils::getTimeFormat();
		$appointment = new WBK_Appointment_deprecated();
		if ( !$appointment->setId( $this->appointment_id ) ) {
			date_default_timezone_set( 'UTC' );
			return;
		}
		if ( !$appointment->load() ) {
			date_default_timezone_set( 'UTC' );
			return;
		}
		$service = new WBK_Service_deprecated();
		if ( !$service->setId( $this->service_id ) ) {
			date_default_timezone_set( 'UTC' );
			return;
		}
		if ( !$service->load() ) {
			date_default_timezone_set( 'UTC' );
			return;
		}
		if( $this->customer_approve_status != '' ) {
			//	validation
			if ( !WBK_Validator::checkStringSize( $this->customer_approve_message, 1, 50000 ) ||
				 !WBK_Validator::checkStringSize( $this->customer_approve_subject, 1, 200 ) ||
				 !WBK_Validator::checkEmail( $this->from_email ) ||
				 !WBK_Validator::checkStringSize( $this->from_name, 1, 200 )
				) {
					date_default_timezone_set( 'UTC' );
					return;
				}
				$subject = 	$this->subject_placeholder_processing( $this->customer_approve_subject, $appointment, $service );
				$app_price_total = WBK_Db_Utils::getAppointmentMomentPrice( $appointment->getId() );

				$message = 	$this->message_placeholder_processing( $this->customer_approve_message , $appointment, $service, null, null, $app_price_total );

				$message = wbk_cleanup_loop( $message );
				$subject = wbk_cleanup_loop( $subject );

				$headers = 'From: ' . $this->from_name . ' <' . $this->from_email .'>' . "\r\n";
				add_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );
			    wp_mail( $appointment->getEmail(), $subject, $message, $headers );
				if( $this->customer_approve_status_copy != '' ) {
					wp_mail( $service->getEmail(), $subject, $message, $headers );
					if ( $this->super_admin_email != '' ) {
						wp_mail(  $this->super_admin_email, $subject, $message, $headers );
					}
				}

 				remove_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );
			}
			date_default_timezone_set( 'UTC' );

	}
	public function prepareOnCancel(){
		if( $this->admin_cancel_status != '' ){
			$date_format = WBK_Date_Time_Utils::getDateFormat();
			$time_format = WBK_Date_Time_Utils::getTimeFormat();
			$appointment = new WBK_Appointment_deprecated();
			if ( !$appointment->setId( $this->appointment_id ) ) {
				return;
			}
			if ( !$appointment->load() ) {
				return;
			}
			$service = new WBK_Service_deprecated();
			if ( !$service->setId( $this->service_id ) ) {
				return;
			}
			if ( !$service->load() ) {
				return;
			}
			//	validation
			if ( !WBK_Validator::checkStringSize( $this->admin_cancel_message, 1, 50000 ) ||
				 !WBK_Validator::checkStringSize( $this->admin_cancel_subject, 1, 200 ) ||
				 !WBK_Validator::checkEmail( $this->from_email ) ||
				 !WBK_Validator::checkStringSize( $this->from_name, 1, 200 )
				) {
					return;
			}
			$message = $this->message_placeholder_processing( $this->admin_cancel_message, $appointment, $service );
    		$subject = 	$this->subject_placeholder_processing( $this->admin_cancel_subject, $appointment, $service );
			$message = wbk_cleanup_loop( $message );
			$subject = wbk_cleanup_loop( $subject );
    		$this->admin_cancel_message = $message;
    		$this->admin_cancel_subject = $subject;
		}
	}
	public function sendOnCancel(){

		if( $this->admin_cancel_status != '' ) {
			$service = new WBK_Service_deprecated();
			if ( !$service->setId( $this->service_id ) ) {
				return;
			}
			if ( !$service->load() ) {
				return;
			}
			$headers = 'From: ' . $this->from_name . ' <' . $this->from_email .'>' . "\r\n";
			add_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );
			if( WBK_Validator::checkEmail( $service->getEmail() ) ){
			    wp_mail( $service->getEmail(), $this->admin_cancel_subject, $this->admin_cancel_message, $headers );
			}
		    if ( $this->super_admin_email != ''  ) {
				wp_mail(  $this->super_admin_email, $this->admin_cancel_subject, $this->admin_cancel_message, $headers );
			}
			remove_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );
		}
		return;
 	}
	public function prepareOnCancelCustomer( $by_customer = false ){
		if( $this->customer_cancel_status != '' ){
			$date_format = WBK_Date_Time_Utils::getDateFormat();
			$time_format = WBK_Date_Time_Utils::getTimeFormat();
			$appointment = new WBK_Appointment_deprecated();
			if ( !$appointment->setId( $this->appointment_id ) ) {
				return;
			}
			if ( !$appointment->load() ) {
				return;
			}
			$service = new WBK_Service_deprecated();
			if ( !$service->setId( $this->service_id ) ) {
				return;
			}
			if ( !$service->load() ) {
				return;
			}
			//	validation
			if ( !WBK_Validator::checkStringSize( $this->customer_cancel_message, 1, 50000 ) ||
				 !WBK_Validator::checkStringSize( $this->customer_cancel_subject, 1,200 ) ||
				 !WBK_Validator::checkEmail( $this->from_email ) ||
				 !WBK_Validator::checkStringSize( $this->from_name, 1, 200 )
				) {
					return;
			}
			if( $by_customer == false ){
				$message = $this->message_placeholder_processing( $this->customer_cancel_message, $appointment, $service );
			} else {
				$message = get_option( 'wbk_email_customer_bycustomer_appointment_cancel_message' );
				$message = $this->message_placeholder_processing( $message, $appointment, $service );
			}
    		$subject = 	$this->subject_placeholder_processing( $this->customer_cancel_subject, $appointment, $service );

			$message = wbk_cleanup_loop( $message );
			$subject = wbk_cleanup_loop( $subject );


    		$this->customer_cancel_message = $message;
    		$this->customer_cancel_subject = $subject;

    		$this->customer_cancel_email = $appointment->getEmail();
		}
	}
	public function sendOnCancelCustomer(){
		if( $this->customer_cancel_status != '' ) {
			$headers = 'From: ' . $this->from_name . ' <' . $this->from_email .'>' . "\r\n";
			add_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );

			if( WBK_Validator::checkEmail( $this->customer_cancel_email ) ){
				wp_mail( $this->customer_cancel_email, $this->customer_cancel_subject, $this->customer_cancel_message, $headers );
			}

			remove_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );
		}
		return;
 	}
	protected  function get_string_between( $string, $start, $end ){
	    $string = ' ' . $string;
	    $ini = strpos($string, $start);
	    if ($ini == 0) return '';
	    $ini += strlen($start);
	    $len = strpos($string, $end, $ini) - $ini;
	    return substr($string, $ini, $len);
	}
	protected function subject_placeholder_processing( $message, $appointment, $service ){
		if( $this->current_category == 0 ){
			$current_category_name = '';
		} else {
			$current_category_name = WBK_Db_Utils::getCategoryNameByCategoryId( $this->current_category );
			if( $current_category_name == false  ){
				$current_category_name = '';
			}
		}
		$date_format = WBK_Date_Time_Utils::getDateFormat();
		$time_format = WBK_Date_Time_Utils::getTimeFormat();
		if( function_exists( 'pll__' ) ){
			$message = str_replace( '#service_name', pll__( $service->getName() ), $message );
		} else {
			$message = str_replace( '#service_name', $service->getName(), $message );
		}
		$message = str_replace( '#appointment_day', wp_date( $date_format, $appointment->getDay(), new DateTimeZone( date_default_timezone_get() ) ), $message );
		$message = str_replace( '#appointment_time', wp_date( $time_format, $appointment->getTime(), new DateTimeZone( date_default_timezone_get() ) ), $message );
		$message = str_replace( '#current_category_name', $current_category_name, $message );
		$message = WBK_Db_Utils::message_placeholder_processing( $message, $appointment, $service );

		return $message;
	}
	protected function message_placeholder_processing( $message, $appointment, $service, $total_amount = null, $multi_token = null, $app_price_total = null ){
		return WBK_Db_Utils::message_placeholder_processing( $message, $appointment, $service, $total_amount, $this->current_category, $multi_token, null, $app_price_total );
	}
	public function sendSingleInvoice(){
		$appointment = new WBK_Appointment_deprecated();
		if ( !$appointment->setId( $this->appointment_id ) ) {
			return;
		}
		if ( !$appointment->load() ) {
			return;
		}
		$service = new WBK_Service_deprecated();
		if ( !$service->setId( $appointment->getService() ) ) {
			return;
		}
		if ( !$service->load() ) {
			return;
		}
 		if( $service->getInvoiceTemplate() != 0 ){
		    $template = WBK_Db_Utils::getEmailTemplate( $service->getInvoiceTemplate() ) ;
			if( !is_null( $template ) ){
				$message= htmlspecialchars_decode( stripslashes( $template ));
			} else {
				return;
			}
		} else {
			return;
		}
		//	validation
   		if ( !WBK_Validator::checkStringSize( $message, 1, 50000 ) ||
			!WBK_Validator::checkStringSize( $this->customer_invoice_subject, 1, 200 ) ||
			!WBK_Validator::checkEmail( $this->from_email ) ||
			!WBK_Validator::checkStringSize( $this->from_name, 1, 200 )
		) {
       	    return;
		}
		$message = $this->message_placeholder_processing( $message, $appointment, $service  );
		$subject = 	$this->subject_placeholder_processing( $this->customer_invoice_subject, $appointment, $service );
		$headers = 'From: ' . $this->from_name . ' <' . $this->from_email .'>' . "\r\n";
		add_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );
    	wp_mail( $appointment->getEmail(), $subject, $message, $headers );
    	if( get_option( 'wbk_email_send_invoice_copy','' ) == 'true' ){
    		wp_mail( $service->getEmail(), $subject, $message, $headers );
    	}
		remove_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );
	}
	public function sendMultipleCustomerInvoice( $appointment_ids ) {
		if( count( $appointment_ids ) == 0 ){
			return;
		}
		if( count( $appointment_ids ) > 0 ){
			WBK_Db_Utils::switchLanguageByAppointmentId( $appointment_ids[0] );
		}
		$appointment = new WBK_Appointment_deprecated();
		if ( !$appointment->setId( $appointment_ids[0] ) ) {
			return;
		}
		if ( !$appointment->load() ) {
			return;
		}
		$service = WBK_Db_Utils::initServiceById( WBK_Db_Utils::getServiceIdByAppointmentId( $appointment->getId() ) );
		$template = WBK_Db_Utils::getEmailTemplate( $service->getInvoiceTemplate() ) ;
		if( !is_null( $template ) ){
			$message = htmlspecialchars_decode( stripslashes( $template ));
		} else {
			return;
		}
	   	$subject = $this->customer_invoice_subject;
	   	$this->sendMultipleNotification( $appointment_ids, $message, $subject, $appointment->getEmail() );
		if( get_option( 'wbk_email_send_invoice_copy','' ) == 'true' ){
			$service = new WBK_Service_deprecated();
			if ( !$service->setId( $appointment->getService() ) ) {
				return;
			}
			if ( !$service->load() ) {
				return;
			}
			$this->sendMultipleNotification( $appointment_ids, $message, $subject, $service->getEmail() );
    	}

		return;
 	}
 	public function sendSinglePaymentReceived( $to ){
		$appointment = new WBK_Appointment_deprecated();
		if ( !$appointment->setId( $this->appointment_id ) ) {
			return;
		}
		if ( !$appointment->load() ) {
			return;
		}
		$service = new WBK_Service_deprecated();
		if ( !$service->setId( $appointment->getService() ) ) {
			return;
		}
		if ( !$service->load() ) {
			return;
		}
 		if( $to == 'customer' ){
 			$message = get_option( 'wbk_email_customer_paymentrcvd_message', '' );
 			$subject = get_option( 'wbk_email_customer_paymentrcvd_subject', '' );
 		} elseif ( $to == 'admin' ) {
 			$message = get_option( 'wbk_email_admin_paymentrcvd_message', '' );
 			$subject = get_option( 'wbk_email_admin_paymentrcvd_subject', '' );
 		}

		//	validation
   		if ( !WBK_Validator::checkStringSize( $message, 1, 50000 ) ||
			!WBK_Validator::checkStringSize( $subject, 1, 200 ) ||
			!WBK_Validator::checkEmail( $this->from_email ) ||
			!WBK_Validator::checkStringSize( $this->from_name, 1, 200 )
		) {
       	    return;
		}
		$message = $this->message_placeholder_processing( $message, $appointment, $service  );
		$subject = 	$this->subject_placeholder_processing( $subject, $appointment, $service );

		$headers = 'From: ' . $this->from_name . ' <' . $this->from_email .'>' . "\r\n";
		add_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );
    	if( $to == 'customer' ){
    		wp_mail( $appointment->getEmail(), $subject, $message, $headers );
		} elseif ( $to == 'admin' ) {
			wp_mail( $service->getEmail(), $subject, $message, $headers );
			if ( $this->super_admin_email != ''  ) {
				wp_mail(  $this->super_admin_email, $subject, $message, $headers );
			}
		}
		remove_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );
	}
	public function sendSingleArrived(){
		$appointment = new WBK_Appointment_deprecated();
		if ( !$appointment->setId( $this->appointment_id ) ) {
			return;
		}
		if ( !$appointment->load() ) {
			return;
		}
		$service = new WBK_Service_deprecated();
		if ( !$service->setId( $appointment->getService() ) ) {
			return;
		}
		if ( !$service->load() ) {
			return;
		}

 		$message = get_option( 'wbk_email_customer_arrived_message', '' );
 		$subject = get_option( 'wbk_email_customer_arrived_subject', '' );

		//	validation
   		if ( !WBK_Validator::checkStringSize( $message, 1, 50000 ) ||
			 !WBK_Validator::checkStringSize( $subject, 1, 200 ) ||
			 !WBK_Validator::checkEmail( $this->from_email ) ||
			 !WBK_Validator::checkStringSize( $this->from_name, 1, 200 )
		) {
       	    return;
		}
		$message = $this->message_placeholder_processing( $message, $appointment, $service  );
		$subject = 	$this->subject_placeholder_processing( $subject, $appointment, $service );
		$headers = 'From: ' . $this->from_name . ' <' . $this->from_email .'>' . "\r\n";
		add_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );
    	wp_mail( $appointment->getEmail(), $subject, $message, $headers );
		remove_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );

	}
	public function send_single_notification( $booking_id, $message, $subject ){
		$appointment = new WBK_Appointment_deprecated();
		if ( !$appointment->setId( $booking_id ) ) {
			return;
		}
		if ( !$appointment->load() ) {
			return;
		}
		$service = new WBK_Service_deprecated();
		if ( !$service->setId( $appointment->getService() ) ) {
			return;
		}
		if ( !$service->load() ) {
			return;
		}


		//	validation
		if ( !WBK_Validator::checkStringSize( $message, 1, 50000 ) ||
			 !WBK_Validator::checkStringSize( $subject, 1, 200 ) ||
			 !WBK_Validator::checkEmail( $this->from_email ) ||
			 !WBK_Validator::checkStringSize( $this->from_name, 1, 200 )
		) {
			return;
		}
		$message = WBK_Placeholder_Processor::process_placeholders( $message, $booking_id );
		$subject = WBK_Placeholder_Processor::process_placeholders( $subject, $booking_id );

 		$headers = 'From: ' . $this->from_name . ' <' . $this->from_email .'>' . "\r\n";
		add_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );
		wp_mail( $appointment->getEmail(), $subject, $message, $headers );
		remove_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );

	}

	public function sendSingleBookedManually(){
		$appointment = new WBK_Appointment_deprecated();
		if ( !$appointment->setId( $this->appointment_id ) ) {
			return;
		}
		if ( !$appointment->load() ) {
			return;
		}
		$service = new WBK_Service_deprecated();
		if ( !$service->setId( $appointment->getService() ) ) {
			return;
		}
		if ( !$service->load() ) {
			return;
		}

		// send to customer
		if( $this->customer_book_status != '' ) {
			$message = get_option( 'wbk_email_customer_manual_book_message', '' );
			$subject = get_option( 'wbk_email_customer_manual_book_subject', '' );
			$attachment = array();
			if ( get_option( 'wbk_email_customer_book_status_generate_ical', '' ) != '' ) {
				$ical = WBK_Ical::generateICal( array( $this->appointment_id), 'customer' );
				if ( $ical  != '' ) {
					$attachment[] = $ical;
				}
			}
			//	validation
			if ( !WBK_Validator::checkStringSize( $message, 1, 50000 ) ||
				 !WBK_Validator::checkStringSize( $subject, 1, 200 ) ||
				 !WBK_Validator::checkEmail( $this->from_email ) ||
				 !WBK_Validator::checkStringSize( $this->from_name, 1, 200 )
			) {
				return;
			}
			$message = $this->message_placeholder_processing( $message, $appointment, $service  );
			$subject = 	$this->subject_placeholder_processing( $subject, $appointment, $service );
			$headers[] = 'From: ' . $this->from_name . ' <' . $this->from_email .'>';
			if( get_option( 'wbk_email_override_replyto', 'true' ) == 'true' ){
				$headers[] = 'Reply-To: '.  $this->from_name . ' <' . $service->getEmail() . '>';
			}
			add_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );
			wp_mail( $appointment->getEmail(), $subject, $message, $headers, $attachment );
			remove_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );
		}
		if( $this->admin_book_status != ''   ){
			// send to admin
			$message = get_option( 'wbk_email_admin_book_message', '' );
			$subject = get_option( 'wbk_email_admin_book_subject', '' );
			//	validation
			if ( !WBK_Validator::checkStringSize( $message, 1, 50000 ) ||
				 !WBK_Validator::checkStringSize( $subject, 1, 200 ) ||
				 !WBK_Validator::checkEmail( $this->from_email ) ||
				 !WBK_Validator::checkStringSize( $this->from_name, 1, 200 )
			) {
				return;
			}
			$message = $this->message_placeholder_processing( $message, $appointment, $service  );
			$subject = 	$this->subject_placeholder_processing( $subject, $appointment, $service );
			$headers[] = 'From: ' . $this->from_name . ' <' . $this->from_email .'>';
			if( get_option( 'wbk_email_override_replyto', 'true' ) == 'true' ){
				$headers[] = 'Reply-To: '.  $this->from_name . ' <' . $service->getEmail() . '>';
			}
			add_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );
			wp_mail( $service->getEmail(), $subject, $message, $headers, $attachment );
			if ( $this->super_admin_email != ''  ) {
				wp_mail(  $this->super_admin_email, $subject, $message, $headers );
			}
			remove_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );
		}


	}
	public function sendMultiplePaymentReceived( $to, $appointment_ids ) {
		if( count( $appointment_ids ) > 0 ){
			WBK_Db_Utils::switchLanguageByAppointmentId( $appointment_ids[0] );
		}
		if( $to == 'customer' ){
 			$message = get_option( 'wbk_email_customer_paymentrcvd_message', '' );
 			$subject = get_option( 'wbk_email_customer_paymentrcvd_subject', '' );
 		} elseif ( $to == 'admin' ) {
 			$message = get_option( 'wbk_email_admin_paymentrcvd_message', '' );
 			$subject = get_option( 'wbk_email_admin_paymentrcvd_subject', '' );
 		}
		if( count( $appointment_ids ) == 0 ){
					return;
		}
		$appointment = new WBK_Appointment_deprecated();
		if ( !$appointment->setId( $appointment_ids[0] ) ) {
			return;
		}
		if ( !$appointment->load() ) {
			return;
		}
		$service = WBK_Db_Utils::initServiceById(  $appointment->getService() );
		if( $to == 'customer' ){
			$this->sendMultipleNotification( $appointment_ids, $message, $subject, $appointment->getEmail() );
		} else {
			$this->sendMultipleNotification( $appointment_ids, $message, $subject, $service->getEmail() );
			if ( $this->super_admin_email != '' ) {
				$this->sendMultipleNotification( $appointment_ids, $message, $subject, $this->super_admin_email  );
			}

		}
		return;
   	}
	function replaceRanges( $message, $appointment_ids ){
		$start = 2554146984;
		$end = 0;
		$date_format = WBK_Date_Time_Utils::getDateFormat();
		$time_format = WBK_Date_Time_Utils::getTimeFormat();
		foreach ($appointment_ids as $id ) {
			$appointment = WBK_Db_Utils::initAppointmentById( $id );
			if( $appointment == FALSE ){
				continue;
			}
			$service = WBK_Db_Utils::initServiceById( $appointment->getService() );
		 	$cur_start = $appointment->getTime();
			$cur_end = $cur_start + $service->getDuration() * 60;
			if( $cur_start < $start  ){
				$start = $cur_start;
			}
			if( $cur_end > $end ){
				$end = $cur_end;
			}
		}
		$time_range = wp_date( $time_format, $start, new DateTimeZone( date_default_timezone_get() )) . ' - ' . wp_date( $time_format, $end, new DateTimeZone( date_default_timezone_get() ) );

		$date_time_range = wp_date( $date_format, $start, new DateTimeZone( date_default_timezone_get() ) ) . ' ' . wp_date( $time_format, $start, new DateTimeZone( date_default_timezone_get() ) ) . ' - ' . wp_date( $date_format, $end, new DateTimeZone( date_default_timezone_get() ) ) . ' ' . wp_date( $time_format, $end, new DateTimeZone( date_default_timezone_get() ) );
		$message = str_replace( '#timerange', $time_range, $message );
		$message = str_replace( '#timedaterange', $date_time_range, $message );
		return $message;
	}
	public function send_gg_calendar_issue_alert_to_admin( $error_message = '' ){
		if( get_option( 'wbk_gg_send_alerts_to_admin', 'no' ) == 'no' ){
			return;
		}
		$service = new WBK_Service_deprecated();
		if ( !$service->setId( $this->service_id ) ) {
			return;
		}
		if ( !$service->load() ) {
			return;
		}
		$headers = 'From: ' . $this->from_name . ' <' . $this->from_email .'>' . "\r\n";
		add_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );
    	wp_mail( $service->getEmail(), 'Issue with the Google calendar intgration.', 'Webba Booking plugin was unable to connect with the Google Calendar, please check the settings. Details: ' . $error_message, $headers );
		remove_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );
	}
}
function wbk_cleanup_loop( $value ){
	$value = str_replace( '[appointment_loop_start]', '', $value );
	$value = str_replace( '[appointment_loop_end]', '', $value );
	$value = str_replace( '#timerange', '', $value );
	$value = str_replace( '#timedaterange', '', $value );

	return $value;
}

function wbk_email_processing_send_on_payment( $app_ids ){
	date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
	if( count( $app_ids ) == 0 ){
		date_default_timezone_set( 'UTC' );
		return;
	}
	$service_id = WBK_Db_Utils::getServiceIdByAppointmentId( $app_ids[0] );
	$notifications = new WBK_Email_Notifications( $service_id, $app_ids[0] );
	// send confitmation about the transaction to admin
	if( get_option( 'wbk_email_admin_paymentrcvd_status', '' ) != '' ){
		if( get_option( 'wbk_multi_booking', 'disabled' ) != 'disabled' ) {
			// case for multiple mode
			if( get_option( 'wbk_email_admin_book_multiple_mode', 'one' ) == 'one' ){
				$notifications->sendMultiplePaymentReceived( 'admin', $app_ids );
			} else {
				foreach( $app_ids as $app_id ){
					$notifications = new WBK_Email_Notifications( $service_id, $app_id );
					$notifications->sendSinglePaymentReceived( 'admin' );
				}
			}
		} else {
			$notifications->sendSinglePaymentReceived( 'admin' );
		}
	}
	// send confitmation about the transaction to customer
	if( get_option( 'wbk_email_customer_paymentrcvd_status', '' ) != '' ){
		if( get_option( 'wbk_multi_booking', 'disabled' ) != 'disabled' ) {
			// case for multiple mode
			if( get_option( 'wbk_email_customer_book_multiple_mode', 'one' ) == 'one' ){
				$notifications-> sendMultiplePaymentReceived( 'customer', $app_ids );
			} else {
				foreach( $app_ids as $app_id ){
					$notifications = new WBK_Email_Notifications( $service_id, $app_id );
					$notifications->sendSinglePaymentReceived( 'customer' );
				}
			}
		} else {
			$notifications->sendSinglePaymentReceived( 'customer' );
		}
	}

	if(	get_option( 'wbk_email_customer_send_invoice', 'disabled' ) == 'onpayment' ){
		// mutiple booking disabled or foreach mode is used
		if( ( get_option( 'wbk_multi_booking', 'disabled' ) != 'disabled' &&  get_option( 'wbk_email_customer_book_multiple_mode', 'foreach' ) == 'foreach' ) || get_option( 'wbk_multi_booking', 'disabled' ) == 'disabled' ){
			foreach( $app_ids as $app_id ){
				$notifications = new WBK_Email_Notifications( $service_id, $app_id );

				$notifications->sendSingleInvoice();
			}
		} else {
			$notifications->sendMultipleCustomerInvoice( $app_ids );
		}
	}


	date_default_timezone_set( 'UTC' );
}
?>
