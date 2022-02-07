<?php
// check if accessed directly
    if ( ! defined( 'ABSPATH' ) ) exit;
    date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
?>
<script type='text/javascript'>
   	var wbk_cstuomer_email_on_from = '';
</script>
<?php

 	if( isset( $_GET['paypal_status'] ) ){
?>
    <div class="wbk-outer-container wbk_booking_form_container">
			<div class="wbk-inner-container">
				<div class="wbk-frontend-row">
					<div class="wbk-col-12-12">
						<div class="wbk-details-sub-title"><?php
							global $wbk_wording;
							$payment_title =  get_option( 'wbk_payment_result_title', '' );
							if( $payment_title  == '' ){
								$payment_title = sanitize_text_field( $wbk_wording['payment_title']	);
							}
							echo $payment_title;
							?></div>
					</div>
					<div class="wbk-col-12-12">
						<?php
							if( $_GET['paypal_status'] == 1 ){
							?>
								<div class="wbk-input-label wbk_payment_success"><?php
									global $wbk_wording;
									$payment_complete_label  =  get_option( 'wbk_payment_success_message', '' );
									if( $payment_complete_label == ''){
										$payment_complete_label = sanitize_text_field( $wbk_wording['payment_complete'] );
									}
									echo $payment_complete_label;
								 ?></div>
						<?php
						    }
						?>
						<?php
							if( $_GET['paypal_status'] == 5 ){
							?>
								<div class="wbk-input-label wbk_payment_cancel"><?php
		 							global $wbk_wording;
									$payment_canceled_label  =  get_option( 'wbk_payment_cancel_message', '' );
									if( $payment_canceled_label == ''){
										$payment_canceled_label = sanitize_text_field( $wbk_wording['payment_canceled'] );
									}
									echo $payment_canceled_label;

								?></div>
						<?php
						    }
						?>
						<?php
							if( $_GET['paypal_status'] == 2 ){
							?>
								<div class="wbk-input-label wbk_payment_error">Error 102</div>
						<?php
						    }
						?>
						<?php
							if( $_GET['paypal_status'] == 3 ){
							?>
								<div class="wbk-input-label wbk_payment_error">Error 103</div>
						<?php
						    }
						?>
						<?php
							if( $_GET['paypal_status'] == 4 ){
							?>
								<div class="wbk-input-label wbk_payment_error">Error 104</div>
						<?php
						    }
						?>
					</div>
				</div>
			</div>
		</div>

<?php
		date_default_timezone_set( 'UTC' );
		return;
	}
?>
<?php
	if( get_option( 'wbk_allow_manage_by_link', 'no' ) == 'yes' ){
 		if( isset( $_GET['admin_cancel'] ) ){

			$cancelation =  $_GET['admin_cancel'];
			$cancelation = WBK_Db_Utils::wbk_sanitize( $cancelation );
			$appointment_ids = WBK_Db_Utils::getAppointmentIdsByGroupAdminToken( $cancelation );

			$valid = false;
			$i = 0;
			$customer_notification_mode = get_option( 'wbk_email_customer_cancel_multiple_mode', 'foreach' );
			if( $customer_notification_mode == 'one' && get_option( 'wbk_email_customer_appointment_cancel_status', '' ) == 'true' ){
				if( count( $appointment_ids ) > 0 ){
	 				$appointment = new WBK_Appointment_deprecated();
					if ( $appointment->setId( $appointment_ids[0] ) ) {
						if ( $appointment->load() ) {

							$recipient = $appointment->getEmail();

							$noifications = new WBK_Email_Notifications( null, null );

							$subject = get_option( 'wbk_email_customer_appointment_cancel_subject', '' );
							$message = get_option( 'wbk_email_customer_appointment_cancel_message', '' );

							$noifications->sendMultipleNotification( $appointment_ids, $message, $subject, $recipient );

                            // send to administrator
                            $service_id = $appointment->getService();
                            $service = WBK_Db_Utils::initServiceById( $service_id );
                            if( $service != FALSE) {
                                $subject = get_option( 'wbk_email_adimn_appointment_cancel_subject', '' );
                                $message = get_option( 'wbk_email_adimn_appointment_cancel_message', '' );

                                $noifications->sendMultipleNotification( $appointment_ids, $message, $subject, $service->getEmail() );
                                $super_admin_email = get_option( 'wbk_super_admin_email', '' );
                                if ( $super_admin_email != '' ) {
        	 						$noifications->sendMultipleNotification( $appointment_ids, $message, $subject, $super_admin_email );
        	 					}
                            }
						}
					}
				}
			}
			foreach( $appointment_ids as $appointment_id ){
				if( $appointment_id === false ){
		 			$valid = false;
		 		} else {
	 				$appointment = new WBK_Appointment_deprecated();
					if ( !$appointment->setId( $appointment_id ) ) {
						$valid = false;
					}
					if ( !$appointment->load() ) {
						$valid = false;
					}

					WBK_Db_Utils::deleteAppointmentDataAtGGCelendar( $appointment_id );
					WBK_Db_Utils::copyAppointmentToCancelled( $appointment_id, __( 'Service administrator', 'wbk' ) );
			        $service_id = WBK_Db_Utils::getServiceIdByAppointmentId( $appointment_id );
	        		if( $customer_notification_mode == 'foreach' ){
		        		$noifications = new WBK_Email_Notifications( $service_id, $appointment_id );
		        		$noifications->prepareOnCancelCustomer();
                        $noifications->prepareOnCancel();
		        	}
					if( $appointment->delete() === false ){

					} else {
						if( $customer_notification_mode == 'foreach' ){
							$noifications->sendOnCancelCustomer();
                            $noifications->sendOnCancel();
						}
						$valid = true;
						$i++;
					}
				}
			}


			if( $valid ){
				?>
					<div class="wbk-outer-container wbk_booking_form_container">
						<div class="wbk-inner-container">
							<div class="wbk-frontend-row">
								<div class="wbk-col-12-12">
									<div class="wbk-input-label">
									 	<?php
                                             $message = get_option( 'wbk_booking_canceled_message_admin', __( 'Appointment canceled #count', 'wbk' ) );
										if( $i > 1 ){
    									 	    $count =  '<span class="wbk_mutiple_counter">: ' .  $i . '</span>';
    										 } else {
                                                $count = '';
										}
                                             echo str_replace( '#count', $count, $message );
									 	?>
									</div>

								</div>
							</div>
							<div class="wbk-frontend-row wbk_payment" id="wbk-payment">
							</div>
						</div>
					</div>
				<?php
					date_default_timezone_set( 'UTC' );
					return;
			}
 		}
 	}
?>
<?php
	if( get_option( 'wbk_allow_manage_by_link', 'no' ) == 'yes' ){
 		if( isset( $_GET['admin_approve'] ) ){
	 		$admin_approve =  $_GET['admin_approve'];
			$admin_approve = str_replace('"', '', $admin_approve );
			$admin_approve = str_replace('<', '', $admin_approve );
			$admin_approve = str_replace('\'', '', $admin_approve );
			$admin_approve = str_replace('>', '', $admin_approve );
			$admin_approve = str_replace('/', '', $admin_approve );
			$admin_approve = str_replace('\\',  '', $admin_approve );
			$valid = true;

            $appointment_ids = WBK_Db_Utils::getAppointmentIdsByGroupAdminToken( $admin_approve );

            $valid = false;
            $i = 0;

            foreach( $appointment_ids as $appointment_id ){

	 			$status = WBK_Db_Utils::getAppointmentStatus( $appointment_id );

	 			if( $status == 'pending' || $status == 'paid' ){
                    $i++;
	 				if( $status == 'pending' ){
	 					WBK_Db_Utils::setAppointmentStatus( $appointment_id, 'approved' );
	 				}
	 				if( $status == 'paid' ){
	 					WBK_Db_Utils::setAppointmentStatus( $appointment_id, 'paid_approved' );
	 				}
                    $valid = true;
                    $service_id = WBK_Db_Utils::getServiceIdByAppointmentId( $appointment_id );

                    if( !WBK_Validator::checkEmailLoop( get_option( 'wbk_email_customer_approve_message', '' ) ) ){
                        $noifications = new WBK_Email_Notifications( $service_id, $appointment_id );
                        $noifications->sendOnApprove();
                        if( get_option( 'wbk_email_customer_send_invoice', 'disabled' ) == 'onapproval' ){
                            $noifications->sendSingleInvoice();
                        }
                    }
                    $expiration_mode = get_option( 'wbk_appointments_delete_not_paid_mode', 'disabled' );
                    if( $expiration_mode == 'on_approve' ){
                        WBK_Db_Utils::setAppointmentsExpiration( $appointment_id );
                    }
                    if( get_option( 'wbk_gg_when_add', 'onbooking' ) == 'onpaymentorapproval' ){
                        if( !WBK_Db_Utils::idEventAddedToGoogle( $appointment_id ) ){
                            date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
                            WBK_Db_Utils::addAppointmentDataToGGCelendar( $service_id, $appointment_id );
                            date_default_timezone_set( 'UTC' );
                        }
                    } else {
                        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
                        WBK_Db_Utils::updateAppointmentDataAtGGCelendar( $appointment_id );
                        date_default_timezone_set( 'UTC' );
                    }
	 			}
            }

			if( $valid ){

                if( WBK_Validator::checkEmailLoop( get_option( 'wbk_email_customer_approve_message', '' ) ) ){
                    if( get_option( 'wbk_email_customer_approve_status', '' ) == 'true' ){
                       $appointment = new WBK_Appointment_deprecated();
                       if ( $appointment->setId( $appointment_ids[0] ) ) {
                           if ( $appointment->load() ) {
                               $service = WBK_Db_Utils::initServiceById( $appointment->getService() );
                               if( $service != FALSE ){
                                   $recipient = $appointment->getEmail();
                                   $subject = get_option( 'wbk_email_customer_approve_subject', '' );
                                   $message = get_option( 'wbk_email_customer_approve_message', '' );
                                   $notifications = new WBK_Email_Notifications( null, null );
                                   $notifications->sendMultipleNotification( $appointment_ids, $message, $subject, $recipient );
                               }
                           }
                       }
                   }
                }
				?>
					<div class="wbk-outer-container wbk_booking_form_container">
						<div class="wbk-inner-container">
							<div class="wbk-frontend-row">
								<div class="wbk-col-12-12">
									<div class="wbk-input-label">
									 	<?php
                                            $message = get_option( 'wbk_booking_approved_message_admin', __( 'Appointment approved #count', 'wbk' ) );
                                            if( $i > 1 ){
                                               $count =  '<span class="wbk_mutiple_counter">: ' .  $i . '</span>';
                                            } else {
                                               $count = '';
                                            }
                                            echo str_replace( '#count', $count, $message );
									 	?>
									</div>
								</div>
							</div>
							<div class="wbk-frontend-row wbk_payment" id="wbk-payment">
							</div>
						</div>
					</div>
				<?php
					date_default_timezone_set( 'UTC' );
					return;
			}
 		}
 	}
?>
<?php
 	if( isset( $_GET['order_payment'] ) ){
 		$order_payment =  $_GET['order_payment'];

		$order_payment = str_replace('"', '', $order_payment );
		$order_payment = str_replace('<', '', $order_payment );
		$order_payment = str_replace('\'', '', $order_payment );
		$order_payment = str_replace('>', '', $order_payment );
		$order_payment = str_replace('/', '', $order_payment );
		$order_payment = str_replace('\\',  '', $order_payment );

 		$appointment_ids = WBK_Db_Utils::getAppointmentIdsByGroupToken ( $order_payment );

 		if( count( $appointment_ids ) == 0 ){
 			$valid = false;
 		} else {
 				$title = '';
 				$found_valid_appointments = 0;
 				foreach( $appointment_ids as $appointment_id ){
	 				$service_id = WBK_Db_Utils::getServiceIdByAppointmentId( $appointment_id );
	 				if( $service_id == FALSE ){
	 					continue;
	 				}
	 				$valid = true;
	 				$appointment = new WBK_Appointment_deprecated();
					if ( !$appointment->setId( $appointment_id ) ) {
						continue;
					}
					if ( !$appointment->load() ) {
						continue;
					}
					$service = new WBK_Service_deprecated();
					if ( !$service->setId( $service_id ) ) {
						continue;
					}
					if ( !$service->load() ) {
						continue;
					}
					$appointment_status = WBK_Db_Utils::getStatusByAppointmentId( $appointment_id );
					if(  $appointment_status != 'paid' && $appointment_status != 'paid_approved' && $appointment_status != 'woocommerce' ){
						global $wbk_wording;
						$title_this = get_option( 'wbk_appointment_information', '' );
						if( $title_this == '' ){
							$title_this = $wbk_wording['appointment_info'];
						}
						$title_this = WBK_Db_Utils::landing_appointment_data_processing( $title_this, $appointment, $service ) . '<br>';
						$title .= $title_this;
						$found_valid_appointments++;

					}
					$payment_methods = json_decode( $service->getPayementMethods() );
				}
				if( $found_valid_appointments == 0 ){
					global $wbk_wording;
					$title = get_option( 'wbk_nothing_to_pay_message', '' );
					if( $title == ''){
						$title = $wbk_wording['nothing_to_pay'];
					}
				} else {

					$title .= WBK_PayPal::renderPaymentMethods( $service_id, $appointment_ids );
					$title .= WBK_Stripe::renderPaymentMethods( $service_id, $appointment_ids );
					$title .= WBK_WooCommerce::renderPaymentMethods( $service_id, $appointment_ids );

					if( $title != '' ){
						if( get_option( 'wbk_allow_coupons', 'disabled' ) == 'enabled' ){
							$title = '<input class="wbk-input" id="wbk-coupon" placeholder="' . __( 'coupon code', 'wbk' ) . '" >' . $title;
						}
					}
					if( in_array( 'arrival', $payment_methods ) ){
						$button_text = get_option( 'wbk_pay_on_arrival_button_text', __( 'Pay on arrival', 'wbk' ) );
                        if( $button_text == '' ){
                            $button_text = __( 'Pay on arrival', 'wbk' );
                        }
						$title .= '<input class="wbk-button wbk-width-100 wbk-mt-10-mb-10 wbk-payment-init" data-method="arrival" data-app-id="'. implode(',',  $appointment_ids ) . '"  value="' . $button_text . '  " type="button">';
					}
					if( in_array( 'bank', $payment_methods ) ){
						$button_text =  get_option( 'wbk_bank_transfer_button_text', __( 'Pay by bank transfer', 'wbk' ) );
						$title .= '<input class="wbk-button wbk-width-100 wbk-mt-10-mb-10 wbk-payment-init" data-method="bank" data-app-id="'. implode(',',  $appointment_ids ) . '"  value="' . $button_text . '  " type="button">';
					}
				}

			}

			if( $valid == true ){
				?>
					<div class="wbk-outer-container wbk_booking_form_container">
						<div class="wbk-inner-container">
							<div class="wbk-frontend-row">
								<div class="wbk-col-12-12">
									<div class="wbk-input-label">
										<input class="wbk-input" style="display:none;">
									 	<?php echo $title; ?>
									</div>
								</div>
							</div>
							<div class="wbk-frontend-row wbk_payment" id="wbk-payment">
							</div>
						</div>
					</div>
					<?php
					date_default_timezone_set( 'UTC' );
					return;
			}

?>
<?php
	}
?>
<?php
 	if( isset( $_GET['cancelation'] ) ){
 	 		$cancelation =  $_GET['cancelation'];
			$cancelation = WBK_Db_Utils::wbk_sanitize( $cancelation );
			$appointment_ids = WBK_Db_Utils::getAppointmentIdsByGroupToken( $cancelation );
	 		if( count( $appointment_ids ) == 0  ){
				$valid = false;
				?>
				<div class="wbk-outer-container wbk_booking_form_container">
						<div class="wbk-inner-container">
							<div class="wbk-frontend-row">
								<div class="wbk-col-12-12">
									<div class="wbk-input-label">
									 	<?php echo __( 'appointment(s) not found', 'wbk' ) ?>
									</div>
								</div>
							</div>
							<div class="wbk-frontend-row" id="wbk-cancel-result">
							</div>
						</div>
					</div>
				<?php
				exit;
	 		} else {
 				$service_id = WBK_Db_Utils::getServiceIdByAppointmentId( $appointment_ids[0] );
 				$valid = false;
				$service = new WBK_Service_deprecated();
				if ( !$service->setId( $service_id ) ) {
				}
				if ( !$service->load() ) {
				}
				$title_all = '';
				$valid_items = 0;
				$token_result = array();
				foreach( $appointment_ids as $appointment_id ){
	 				$appointment = new WBK_Appointment_deprecated();
					if ( !$appointment->setId( $appointment_id ) ) {
						$contine;
					}
					if ( !$appointment->load() ) {
						$contine;
					}
					$valid = true;
					global $wbk_wording;
					$title = get_option( 'wbk_appointment_information', '' );
					if( $title == '' ){
						$title = $wbk_wording['appointment_info'];
					}
					$title = WBK_Db_Utils::landing_appointment_data_processing( $title, $appointment, $service );
			 		$appointment_status = WBK_Db_Utils::getStatusByAppointmentId( $appointment_id );
					if( $appointment_status == 'paid' || $appointment_status == 'paid_approved'  ){
						if( get_option( 'wbk_appointments_allow_cancel_paid', 'disallow' ) == 'disallow' ){
							global $wbk_wording;
							$paid_error_message = get_option( 'wbk_booking_couldnt_be_canceled',  '' );
							if( $paid_error_message == '' ){
								$paid_error_message = sanitize_text_field( $wbk_wording['paid_booking_cancel'] );
							}
							$title .= ' - ' . $paid_error_message;
							$title_all .= $title . '<br>';
							continue;
						}
					}
					// check buffer
					$buffer = get_option( 'wbk_cancellation_buffer', '' );
					if( $buffer != '' ){
						if( intval( $buffer ) > 0 ){
							$buffer_point = ( intval( $appointment->getTime() - intval( $buffer ) * 60 ) );

							if( time() >  $buffer_point ){
								$cancel_error_message = get_option( 'wbk_booking_couldnt_be_canceled2', '' );
								if( $cancel_error_message == ''){
									global $wbk_wording;
									$cancel_error_message = $wbk_wording['paid_booking_cancel2'];
								}
								$title .= ' - ' . $cancel_error_message;
								$title_all .= $title . '<br>';
								continue;
							}
						}
					}
					// end check buffer
					$valid_items++;
					$title_all .= $title . '<br>';
					$token_result[] = WBK_Db_Utils::getTokenByAppointmentId( $appointment_id );

				}
				$title = $title_all;
				if( $valid_items > 0 ){
					global $wbk_wording;
					$email_cancel_label = get_option( 'wbk_booking_cancel_email_label', '' );
					if( $email_cancel_label == '' ){
						$email_cancel_label =  sanitize_text_field( $wbk_wording['cancelation_email'] );
					}
					$content = '<label class="wbk-input-label" for="wbk-customer_email">'. $email_cancel_label .'</label>';
					$content .= '<input name="wbk-email" class="wbk-input wbk-width-100 wbk-mb-10" id="wbk-customer_email" type="text">';
					$cancel_label =  get_option( 'wbk_cancel_button_text', '' );
					if( $cancel_label == '' ){
						$cancel_label = sanitize_text_field( $wbk_wording['cancel_label'] );
					}
					$content .= '<input class="wbk-button wbk-width-100 wbk-mt-10-mb-10" id="wbk-cancel_booked_appointment" data-appointment="'. implode( '-', $token_result ) .'" value="' . $cancel_label . '" type="button">';
				} else {
					$content = '';
				}
			}
 				if( $valid == true ){
			?>
					<div class="wbk-outer-container wbk_booking_form_container">
						<div class="wbk-inner-container">
							<div class="wbk-frontend-row">
								<div class="wbk-col-12-12">
									<div class="wbk-input-label">
									 	<?php echo $title . $content; ?>
									</div>
								</div>
							</div>
							<div class="wbk-frontend-row" id="wbk-cancel-result">
							</div>
						</div>
					</div>
					<?php
					date_default_timezone_set( 'UTC' );
					return;
				}
 	}
?>
<?php
 	if( isset( $_GET['code'] ) ){
 		$code = $_GET['code'];
 		if( isset( $_SESSION['wbk_ggeventaddtoken'] ) && $_SESSION['wbk_ggeventaddtoken']  != '' ){
			$token =  WBK_Db_Utils::wbk_sanitize( $_SESSION['wbk_ggeventaddtoken'] );
			$appointment_ids = WBK_Db_Utils::getAppointmentIdsByGroupToken( $token );
	 		if( count( $appointment_ids ) == 0 ){
	 			$valid = FALSE;
	 		} else {

 				$service_id = WBK_Db_Utils::getServiceIdByAppointmentId( $appointment_ids[0] );


 				$adding_result = WBK_Db_Utils::addAppointmentDataToCustomerGGCelendar( $service_id, $appointment_ids, $code );

 				if( $adding_result > 0 ){
 					$title = get_option( 'wbk_gg_calendar_add_event_success', __( 'Appointment data added to Google Calendar.', 'wbk' ) );
 					if( $title == '' ){
						global $wbk_wording;
						$title = $wbk_wording[ 'add_event_sucess' ];
					}
 				} else {
 					$title = get_option( 'wbk_gg_calendar_add_event_canceled', __( 'Appointment data not added to Google Calendar.', 'wbk' ) );
 					if( $title == '' ){
						global $wbk_wording;
						$title = $wbk_wording[ 'add_event_canceled' ];
					}
 				}
 				$content = '';
 				$valid = TRUE;
	 		}
		} else {
			$valid = false;
		}
		if( $valid == true ){
		?>
			<div class="wbk-outer-container wbk_booking_form_container">
				<div class="wbk-inner-container">
					<div class="wbk-frontend-row">
						<div class="wbk-col-12-12">
							<div class="wbk-input-label">
							 	<?php echo $title . $content; ?>
							</div>
						</div>
					</div>
				</div>
			</div>
					<?php
					date_default_timezone_set( 'UTC' );
					return;
		}
 	}
?>
<?php
 	if( isset( $_GET['ggeventadd'] ) ){
 	 		$ggeventadd = $_GET['ggeventadd'];
 	 		$ggeventadd = WBK_Db_Utils::wbk_sanitize( $ggeventadd );
 			$appointment_ids = WBK_Db_Utils::getAppointmentIdsByGroupToken( $ggeventadd );
 			$appointment_ids = array_unique( $appointment_ids );
	 		if( count( $appointment_ids ) == 0 ){
				$valid = false;
				$content = '';
 				$title = get_option( 'wbk_email_landing_text_invalid_token', '' );
				if( $title == '' ){
					global $wbk_wording;
					$title = sanitize_text_field( $wbk_wording['add_event_canceled'] );
					$valid = false;
				}

	 		} else {
 				$service_id = WBK_Db_Utils::getServiceIdByAppointmentId( $appointment_ids[0] );
 				$valid = true;

 				$title = '';

 				foreach( $appointment_ids as $appointment_id ){
	 				$appointment = new WBK_Appointment_deprecated();
					if ( !$appointment->setId( $appointment_id ) ) {
						continue;
					}
					if ( !$appointment->load() ) {
						$continue;
					}
					$service = new WBK_Service_deprecated();
					if ( !$service->setId( $service_id ) ) {
						$continue;
					}
					if ( !$service->load() ) {
						$continue;
					}

					global $wbk_wording;
					$title_this = get_option( 'wbk_appointment_information', '' );
					if( $title_this == '' ){
						$title_this = $wbk_wording['appointment_info'];
					}
					$title .= WBK_Db_Utils::landing_appointment_data_processing( $title_this, $appointment, $service ) . '<br>';
				}


				// prepare and render auth url
				$google = new WBK_Google();
		  		if( $google->init( null ) == TRUE ){
		  		} else {
		  			$valid = false;
		  		}
		  		$auth_url = $google->getAuthUrl();
		  		$link_text = get_option( 'wbk_add_gg_button_text', 'Add to my Google Calendar' );
		  		if( $link_text == '' ){
		 	 		global $wbk_wording;
		 	 		$link_text =  sanitize_text_field( $wbk_wording['email_landing_text_gg_event_add'] );
		 	 	}
		  		$content = '<input type="button" class="wbk-button wbk-width-100 wbk-addgg-link" data-link="'. $auth_url . '" value="' . $link_text . '"	>';
				// end prepare and render auth url
			}
			?>
				<div class="wbk-outer-container wbk_booking_form_container">
					<div class="wbk-inner-container">
						<div class="wbk-frontend-row">
							<div class="wbk-col-12-12">
								<div class="wbk-input-label">
								 	<?php echo $title . $content; ?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php
				date_default_timezone_set( 'UTC' );
				return;
 	}
?>
<?php
if( isset( $_GET['ggadd_cancelled'] ) ){
?>
	<div class="wbk-outer-container wbk_booking_form_container">
		<div class="wbk-inner-container">
			<div class="wbk-frontend-row">
				<div class="wbk-col-12-12">
					<div class="wbk-input-label">
					 	<?php
					 		$message =  get_option( 'wbk_gg_calendar_add_event_canceled', __( 'Appointment data not added to Google Calendar.', 'wbk' ) );
					 		if( $message == '' ){
					 			global $wbk_wording;
					 	 		$message =  sanitize_text_field( $wbk_wording['email_landing_text_gg_event_add'] );
					 		}
					 		echo $message;
				 		?>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
return;
}
?>
<div class="wbk-outer-container wbk_booking_form_container">
	<div class="wbk-inner-container">
    <?php
        do_action('webba_before_booking_form');

    ?>
 	<img src=<?php echo  WP_WEBBA_BOOKING__PLUGIN_URL . '/frontend/images/loading.svg' ?> style="display:block;width:0px;height:0px;">
    <img src=<?php echo  WP_WEBBA_BOOKING__PLUGIN_URL . '/frontend/images/loading_small.svg' ?> style="display:block;width:0px;height:0px;">
		<div class="wbk-frontend-row" id="wbk-service-container" >
			<div class="wbk-col-12-12" >
				 <?php
				 	if ( $data[0] <> 0 ){
				 		$service = new WBK_Service_deprecated();
			 			if ( !$service->setId( $data[0] ) ) {
			 				return;
			 			}
			 			if ( !$service->load() ) {
			 				return;
			 			}
                        if(  get_option( 'wbk_show_service_description', 'disabled' ) == 'enabled' ){
                        ?>
                            <div class="wbk_description_holder" id="wbk_description_holder">
                                <label class="wbk-input-label">
                                    <p><?php echo $service->getDescription();?></p>
                                </label>
                            </div>
                        <?php
                        }
				 		echo '<input type="hidden" class="wbk_services" data-attribute_name="service-id" id="wbk-service-id" data-multi-low-limit="' . $service->getMultipleLowLimit() . '" data-multi-limit="' . $service->getMultipleLimit() . '" value="' . $data[0] . '" />';
				 		echo '<input type="hidden" id="wbk_current_category" value="0">';
				 	} else {
					 	if( get_option( 'wbk_allow_service_in_url', 'no' ) == 'yes'  && isset( $_GET['service'] ) && is_numeric( $_GET['service'] ) ){
					 		$service = new WBK_Service_deprecated();
				 			if ( !$service->setId( $_GET['service'] ) ) {
				 				return;
				 			}
				 			if ( !$service->load() ) {
				 				return;
				 			}
                            if(  get_option( 'wbk_show_service_description', 'disabled' ) == 'enabled' ){
                            ?>
                                <div class="wbk_description_holder" id="wbk_description_holder">
                                    <label class="wbk-input-label">
                                        <p><?php echo $service->getDescription();?></p>
                                    </label>
                                </div>
                            <?php
                            }
					 		echo '<input type="hidden" class="wbk_services" id="wbk-service-id" data-multi-low-limit="' . $service->getMultipleLowLimit() . '" data-multi-limit="' . $service->getMultipleLimit() . '" value="' . $_GET['service'] . '" />';
					 		echo '<input type="hidden" id="wbk_current_category" value="0">';
					 	} else {
					 		// add category list
					 		do_action( 'wbk_render_frontend_category_list', $data[3] );
					 		// end add category list
						 	$label = get_option( 'wbk_service_label',  __( 'Select service', 'wbk' ) );
					 	 	if( $label == '' ){
					 	 		global $wbk_wording;
					 	 		$label =  sanitize_text_field( $wbk_wording['service_label'] );
					 	 	}
					 	 	if( $data[3] == '1' ){
					 	 		echo '<div id="wbk_service_list_holder" style="display: none;">';
					 	 	} else {
					 	 		echo '<div id="wbk_service_list_holder">';
					 	 	}
							echo  '<label class="wbk-input-label">' . $label . '</label>';
					 		echo '<select class="wbk-select wbk-input wbk_services" id="wbk-service-id">';
					 		echo '<option value="0" selected="selected">' . __( 'select...', 'wbk' ) . '</option>';
							if( $data[1] == 0 ){
						 		$arrIds = WBK_Db_Utils::getServices();
					 		} else {
						 		$arrIds = WBK_Db_Utils::getServicesInCategory( $data[1] );
					 		}
					 		$show_desc = '';
					 		foreach ( $arrIds as $id ) {
					 			$service = new WBK_Service_deprecated();
					 			if ( !$service->setId( $id ) ) {
					 				continue;
					 			}
					 			if ( !$service->load() ) {
					 				continue;
					 			}
					 			$show_desc =  get_option( 'wbk_show_service_description', 'disabled' );
                                if( function_exists('pll__' ) ){
                                    $service_name =  pll__( $service->getName( true ) );
                                } else{
                                    $service_name = $service->getName( true );
                                }
                                $service_name = apply_filters( 'wpml_translate_single_string', $service_name, 'wbk', 'Service name id ' . $service->getId() );
        					 	$service_description = apply_filters( 'wpml_translate_single_string', $service->getDescription( false ), 'wbk', 'Service description id ' . $service->getId() );



					 			if( $show_desc == 'disabled' ){
						 			echo '<option value="' . $service->getId() . '"  data-multi-low-limit="' . $service->getMultipleLowLimit() . '" data-multi-limit="' . $service->getMultipleLimit() . '" >' . $service_name . '</option>';
					 			} else {
						 			echo '<option data-desc="' . htmlspecialchars( $service_description )  . '" value="' . $service->getId() . '"  data-multi-low-limit="' . $service->getMultipleLowLimit() . '"  data-multi-limit="' . $service->getMultipleLimit() . '" >' . $service_name . '</option>';
					 			}

					 		}
					 		echo '</select>';
					 		echo '</div>';
					 		echo '<input type="hidden" id="wbk_current_category" value="' . $data[1] . '">';
					 		if( $show_desc == 'enabled' ){
					 			echo '<div class="wbk_description_holder" id="wbk_description_holder"></div>';
					 		}
				 		}
				 	}
				 ?>
			</div>
			<?php
				// add get parameters
				$html_get  = '<script type=\'text/javascript\'>';
      			$html_get .= 'var wbk_get_converted = {';
				foreach ( $_GET as $key => $value ) {
					$value = urldecode($value);
					$key = urldecode($key);
			 		$value = str_replace('"', '', $value);
			 		$key = str_replace('"', '', $key);
			 		$value = str_replace('\'', '', $value);
			 		$key = str_replace('\'', '', $key);
			 		$value = str_replace('/', '', $value);
			 		$key = str_replace('/', '', $key);
			 		$value = str_replace('\\', '', $value);
			 		$key = str_replace('\\', '', $key);
					$value = sanitize_text_field($value);
					$key = sanitize_text_field($key);
					if ( $key != 'action' && $key != 'time' && $key != 'service' && $key != 'step' ){
					}
					$html_get .= '"'.$key.'"'. ':"' . $value . '",';
				}
				$html_get .= '"blank":"blank"';
  				$html_get .= '};</script>';
  				echo $html_get;
			?>

		</div>
		<div class="wbk-frontend-row wbk_date_container" id="wbk-date-container">
		</div>
		<?php
			if( get_option( 'wbk_mode', 'extended' ) == 'extended' ){
		?>
			<div class="wbk-frontend-row wbk_time_container" id="wbk-time-container">
			</div>
		<?php
			}
		?>
		<div class="wbk-frontend-row wbk_slots_container" id="wbk-slots-container">
		</div>
		<div class="wbk-frontend-row wbk_booking_form_container" id="wbk-booking-form-container">
		</div>
		<div class="wbk-frontend-row wbk_booking_done" id="wbk-booking-done">
		</div>
		<div class="wbk-frontend-row wbk_payment" id="wbk-payment">
		</div>
	</div>
</div>
<?php
	date_default_timezone_set( 'UTC' );
?>
