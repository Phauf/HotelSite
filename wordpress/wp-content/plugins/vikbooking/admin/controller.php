<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

// import Joomla controller library
jimport('joomla.application.component.controller');

class VikBookingController extends JControllerVikBooking
{
	/**
	 * Default controller's method when no task is defined,
	 * or no method exists for that task. If a View is requested.
	 * attempts to set it, otherwise sets the default View.
	 */
	public function display($cachable = false, $urlparams = array()) {

		$view = VikRequest::getVar('view', '');
		$header_val = '';

		if (!empty($view)) {
			VikRequest::setVar('view', $view);
		} else {
			$header_val = '18';
			VikRequest::setVar('view', 'dashboard');
		}

		VikBookingHelper::printHeader($header_val);
		
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	/**
	 * AJAX request for building dynamic donut charts.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.12.1
	 */
	public function donut_charts_data() {
		$fromdt 	= VikRequest::getString('fromdt', date('Y-m-d'), 'request');
		$direction  = VikRequest::getString('direction', 'next', 'request');
		$days 		= VikRequest::getInt('days', 7, 'request');
		if (empty($fromdt) || !strtotime($fromdt) || empty($days) || $days < 1) {
			throw new Exception('Missing required data', 400);
		}

		$from_info = getdate(strtotime($fromdt));
		if ($direction != 'next') {
			// fromdt is always the next day after the end of the loop, so the very first next day from the last displayed
			$from_info = getdate(mktime(0, 0, 0, $from_info['mon'], ($from_info['mday'] - ($days * 2)), $from_info['year']));
		}
		// always push the start date to the last second (23:59:59)
		$from_info = getdate(mktime(23, 59, 59, $from_info['mon'], $from_info['mday'], $from_info['year']));

		// months front-end language map
		$monthsmap = array(
			JText::translate('VBSHORTMONTHONE'),
			JText::translate('VBSHORTMONTHTWO'),
			JText::translate('VBSHORTMONTHTHREE'),
			JText::translate('VBSHORTMONTHFOUR'),
			JText::translate('VBSHORTMONTHFIVE'),
			JText::translate('VBSHORTMONTHSIX'),
			JText::translate('VBSHORTMONTHSEVEN'),
			JText::translate('VBSHORTMONTHEIGHT'),
			JText::translate('VBSHORTMONTHNINE'),
			JText::translate('VBSHORTMONTHTEN'),
			JText::translate('VBSHORTMONTHELEVEN'),
			JText::translate('VBSHORTMONTHTWELVE'),
		);

		// weekdays front-end language map
		$wdaysmap = array(
			JText::translate('VBSUNDAY'),
			JText::translate('VBMONDAY'),
			JText::translate('VBTUESDAY'),
			JText::translate('VBWEDNESDAY'),
			JText::translate('VBTHURSDAY'),
			JText::translate('VBFRIDAY'),
			JText::translate('VBSATURDAY'),
		);

		// gather information about the rooms and availability
		$dbo = JFactory::getDbo();
		$all_rooms_ids = array();
		$unpublished_rooms = array();
		$todayymd = date('Y-m-d');
		$q = "SELECT `id`,`name`,`units`,`params`,`avail` FROM `#__vikbooking_rooms`;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$all_rooms = $dbo->loadAssocList();
			foreach ($all_rooms as $k => $r) {
				if ($r['avail'] < 1) {
					$unpublished_rooms[] = $r['id'];
				}
				$all_rooms_ids[$r['id']] = $r['name'];
			}
		}
		$q = "SELECT SUM(`units`) FROM `#__vikbooking_rooms` WHERE `avail`=1;";
		$dbo->setQuery($q);
		$dbo->execute();
		$tot_rooms_units = (int)$dbo->loadResult();

		// load busy records
		$busy = VikBooking::loadBusyRecordsUnclosed(array_keys($all_rooms_ids), $from_info[0]);

		// response body
		$response 		 	 = new stdClass;
		$response->prevweek  = ($todayymd != date('Y-m-d', $from_info[0]));
		$response->nextweek  = true;
		$response->fromd 	 = date('Y-m-d', $from_info[0]);
		$response->tot_units = $tot_rooms_units;
		$response->data  	 = array();

		for ($i = 0; $i < $days; $i++) {
			$tot_booked_today = 0;
			$today_ts = $from_info[0];
			$data_obj = new stdClass;
			$data_obj->ymd = date('Y-m-d', $from_info[0]);
			$data_obj->lbl = $wdaysmap[(int)$from_info['wday']] . ', ' . $from_info['mday'];
			$data_obj->lbl = $data_obj->ymd == $todayymd ? JText::translate('VBTODAY') . ', ' . $data_obj->lbl : $data_obj->lbl . ' ' . $monthsmap[($from_info['mon'] - 1)];
			foreach ($busy as $idroom => $rbusy) {
				if (in_array($idroom, $unpublished_rooms)) {
					continue;
				}
				foreach ($rbusy as $b) {
					$tmpone = getdate($b['checkin']);
					$ritts = mktime(0, 0, 0, $tmpone['mon'], $tmpone['mday'], $tmpone['year']);
					$tmptwo = getdate($b['checkout']);
					$conts = mktime(0, 0, 0, $tmptwo['mon'], $tmptwo['mday'], $tmptwo['year']);
					if ($today_ts >= $ritts && $today_ts < $conts) {
						$tot_booked_today++;
					}
				}
			}
			
			$data_obj->tot_booked = $tot_booked_today;
			$percentage_booked = round((100 * $tot_booked_today / $tot_rooms_units), 2);
			
			$data_obj->color = '#ff4d4d'; //red
			if ($percentage_booked > 33 && $percentage_booked <= 66) {
				$data_obj->color = '#ffa64d'; //orange
			} elseif ($percentage_booked > 66 && $percentage_booked < 100) {
				$data_obj->color = '#2a762c'; //green
			} elseif ($percentage_booked >= 100) {
				$data_obj->color = '#2482b4'; //light-blue
			}

			// push today's data
			array_push($response->data, $data_obj);

			// next day
			$from_info = getdate(mktime(23, 59, 59, $from_info['mon'], ($from_info['mday'] + 1), $from_info['year']));
		}

		// update last date (not displayed/included)
		$response->tod = date('Y-m-d', $from_info[0]);

		echo json_encode($response);
		exit;
	}

	/**
	 * AJAX request for adding a new fest.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.2.0
	 */
	public function add_fest() {
		$dt 	= VikRequest::getString('dt', '', 'request');
		$type 	= VikRequest::getString('type', '', 'request');
		$type 	= empty($type) ? 'custom' : $type;
		$name 	= VikRequest::getString('name', '', 'request');
		$descr 	= VikRequest::getString('descr', '', 'request');
		if (empty($name) || empty($dt) || !strtotime($dt)) {
			echo 'e4j.error.1';
			exit;
		}
		// build fest array
		$new_fest = array(
			'trans_name' => $name
		);

		$fests  = VikBooking::getFestivitiesInstance();
		$result = $fests->storeFestivity($dt, $new_fest, $type, $descr);
		if (!$result) {
			echo 'e4j.error.2';
			exit;
		}

		// reload all festivities for this day for the AJAX response
		$all_fests = $fests->loadFestDates($dt, $dt);
		foreach ($all_fests as $k => $v) {
			// we expect just one record to be returned due to the from/to date limit passed to loadFestDates()
			echo json_encode($v);
			exit;
		}

		// no fests found even after storing it
		echo 'e4j.error.3';
		exit;
	}

	/**
	 * AJAX request for removing a fest.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.2.0
	 */
	public function remove_fest() {
		$dt 	= VikRequest::getString('dt', '', 'request');
		$ind 	= VikRequest::getInt('ind', 0, 'request');
		$type 	= VikRequest::getString('type', '', 'request');
		$type 	= empty($type) ? 'custom' : $type;
		if (empty($dt) || !strtotime($dt)) {
			echo 'e4j.error.1';
			exit;
		}

		$fests  = VikBooking::getFestivitiesInstance();
		$result = $fests->deleteFestivity($dt, $ind, $type);
		if (!$result) {
			echo 'e4j.error.2';
			exit;
		}

		echo 'e4j.ok';
		exit;
	}

	public function einvoicing() {
		VikBookingHelper::printHeader("einvoicing");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'einvoicing'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function pmsreports() {
		VikBookingHelper::printHeader("pmsreports");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'pmsreports'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function ratesoverv() {
		VikBookingHelper::printHeader("20");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'ratesoverv'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function stats() {
		VikBookingHelper::printHeader("stats");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'stats'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function newcron() {
		VikBookingHelper::printHeader("crons");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecron'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function editcron() {
		VikBookingHelper::printHeader("crons");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecron'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function crons() {
		VikBookingHelper::printHeader("crons");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'crons'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function calc_rates() {
		$response = 'e4j.error.ErrorCode(1) Server is blocking the self-request';
		$response_code = 0;

		$currencysymb = VikBooking::getCurrencySymb();
		$vbo_df = VikBooking::getDateFormat();
		$df = $vbo_df == "%d/%m/%Y" ? 'd/m/Y' : ($vbo_df == "%m/%d/%Y" ? 'm/d/Y' : 'Y/m/d');
		$id_room = VikRequest::getInt('id_room', '', 'request');
		$checkin = VikRequest::getString('checkin', '', 'request');
		$nights = VikRequest::getInt('num_nights', 1, 'request');
		$adults = VikRequest::getInt('num_adults', 0, 'request');
		$children = VikRequest::getInt('num_children', 0, 'request');
		/**
		 * The page Calendar may call this task via AJAX to obtain information
		 * about the various rate plans and final costs associated.
		 * 
		 * @since 	1.3.0
		 */
		$only_rates = VikRequest::getInt('only_rates', 0, 'request');
		$units = VikRequest::getInt('units', 1, 'request');
		$checkinfdate = VikRequest::getString('checkinfdate', '', 'request');
		if (!empty($checkinfdate) && empty($checkin)) {
			$checkin = date('Y-m-d', VikBooking::getDateTimestamp($checkinfdate, 0, 0, 0));
		}
		$price_details = array();
		//
		$checkin_ts = strtotime($checkin);
		if (empty($checkin_ts)) {
			$checkin = date('Y-m-d');
			$checkin_ts = strtotime($checkin);
		}
		$is_dst = date('I', $checkin_ts);
		$checkout_ts = $checkin_ts;
		for ($i = 1; $i <= $nights; $i++) { 
			$checkout_ts += 86400;
			$is_now_dst = date('I', $checkout_ts);
			if ($is_dst != $is_now_dst) {
				if ((int)$is_dst == 1) {
					$checkout_ts += 3600;
				} else {
					$checkout_ts -= 3600;
				}
				$is_dst = $is_now_dst;
			}
		}
		$checkout = date('Y-m-d', $checkout_ts);
		if (function_exists('curl_init')) {
			$endpoint = JUri::root().'index.php?option=com_vikbooking&task=tac_av_l';
			/**
			 * @wponly 	Rewrite URI for front-end
			 */
			$model 	= JModel::getInstance('vikbooking', 'shortcodes');
			$itemid = $model->best('vikbooking');
			if ($itemid) {
				$endpoint = str_replace(JUri::root(), '', $endpoint);
				$endpoint = JRoute::rewrite($endpoint . "&Itemid={$itemid}", false);
			}
			//
			$rates_data = 'e4jauth=%s&req_type=hotel_availability&start_date='.$checkin.'&end_date='.$checkout.'&nights='.$nights.'&num_rooms=1&adults[]='.$adults.'&children[]='.$children.'&only_rates='.$only_rates;
			$ch = curl_init($endpoint);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
			curl_setopt($ch, CURLOPT_TIMEOUT, 20);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, sprintf($rates_data, md5('vbo.e4j.vbo')));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));
			$res = curl_exec($ch);
			if ($curl_errno = curl_errno($ch)) {
				$response = "e4j.error.curl Error (".curl_errno($ch)."): ".curl_error($ch);
			} else {
				$arr_res = json_decode($res, true);
				
				/**
				 * We try to check if decoding was unsuccessful, maybe because the response is mixed with HTML code of the Template/Theme.
				 * In this case we try to extract the JSON string from the plain response to decode only that text.
				 * 
				 * @since 	1.14 (J) - 1.3.9 (WP)
				 */
				if (function_exists('json_last_error') && json_last_error() !== JSON_ERROR_NONE) {
					$pattern = '/\{(?:[^{}]|(?R))*\}/x';
					$matchcount = preg_match_all($pattern, $res, $matches);
					if ($matchcount && isset($matches[0]) && count($matches[0])) {
						// we have found JSON strings inside the raw response, we get the last JSON string
						$arr_res = json_decode($matches[0][(count($matches[0]) - 1)], true);
					}
				}
				//
				
				if (is_array($arr_res)) {
					if (!array_key_exists('e4j.error', $arr_res)) {
						if (array_key_exists($id_room, $arr_res)) {
							$response = '';
							foreach ($arr_res[$id_room] as $rate) {
								// build pricing object
								$rplan_details = new stdClass;
								$rplan_details->idprice = $rate['idprice'];
								$rplan_details->name = $rate['pricename'];
								$rplan_details->net = $rate['cost'];
								$rplan_details->fnet = $currencysymb . ' ' . VikBooking::numberFormat($rate['cost']);
								$rplan_details->tax = $rate['taxes'];
								$rplan_details->ftax = $currencysymb . ' ' . VikBooking::numberFormat($rate['taxes']);
								$rplan_details->tot = $rate['cost'] + $rate['taxes'];
								$rplan_details->ftot = $currencysymb . ' ' . VikBooking::numberFormat(($rate['cost'] + $rate['taxes']));
								array_push($price_details, $rplan_details);
								//
								$extra_response = '';
								$response .= '<div class="vbo-calcrates-rateblock" data-idprice="' . $rate['idprice'] . '" data-idroom="' . $id_room . '" data-checkin="' . $checkin . '" data-checkout="' . $checkout . '" data-adults="' . $adults . '" data-children="' . $children . '">';
								$response .= '<span class="vbo-calcrates-ratename">'.$rate['pricename'].'</span>';
								$response .= '<span class="vbo-calcrates-pricedet vbo-calcrates-ratenet"><span>'.JText::translate('VBCALCRATESNET').'</span>'.$currencysymb.' '.VikBooking::numberFormat($rate['cost']).'</span>';
								$response .= '<span class="vbo-calcrates-pricedet vbo-calcrates-ratetax"><span>'.JText::translate('VBCALCRATESTAX').'</span>'.$currencysymb.' '.VikBooking::numberFormat($rate['taxes']).'</span>';
								if (!empty($rate['city_taxes'])) {
									$response .= '<span class="vbo-calcrates-pricedet vbo-calcrates-ratecitytax"><span>'.JText::translate('VBCALCRATESCITYTAX').'</span>'.$currencysymb.' '.VikBooking::numberFormat($rate['city_taxes']).'</span>';
								}
								if (!empty($rate['fees'])) {
									$response .= '<span class="vbo-calcrates-pricedet vbo-calcrates-ratefees"><span>'.JText::translate('VBCALCRATESFEES').'</span>'.$currencysymb.' '.VikBooking::numberFormat($rate['fees']).'</span>';
								}
								if (array_key_exists('affdays', $rate) && $rate['affdays'] > 0) {
									$extra_response .= '<span class="vbo-calcrates-extrapricedet vbo-calcrates-ratespaffdays"><span>'.JText::translate('VBCALCRATESSPAFFDAYS').'</span>'.$rate['affdays'].'</span>';
								}
								if (array_key_exists('diffusagediscount', $rate) && count($rate['diffusagediscount']) > 0) {
									foreach ($rate['diffusagediscount'] as $roomnumb => $disc) {
										$extra_response .= '<span class="vbo-calcrates-extrapricedet vbo-calcrates-rateoccupancydisc"><span>'.JText::sprintf('VBCALCRATESADUOCCUPANCY', $rate['diffusage']).'</span>- '.$currencysymb.' '.VikBooking::numberFormat($disc).'</span>';
										break;
									}
								} elseif (array_key_exists('diffusagecost', $rate) && count($rate['diffusagecost']) > 0) {
									foreach ($rate['diffusagecost'] as $roomnumb => $charge) {
										$extra_response .= '<span class="vbo-calcrates-extrapricedet vbo-calcrates-rateoccupancycharge"><span>'.JText::sprintf('VBCALCRATESADUOCCUPANCY', $rate['diffusage']).'</span>+ '.$currencysymb.' '.VikBooking::numberFormat($charge).'</span>';
										break;
									}
								}
								$tot = $rate['cost'] + $rate['taxes'] + $rate['city_taxes'] + $rate['fees'];
								$tot = round($tot, 2);
								$response .= '<span class="vbo-calcrates-ratetotal"><span>'.JText::translate('VBCALCRATESTOT').'</span>'.$currencysymb.' '.VikBooking::numberFormat($tot).'</span>';
								if (!empty($extra_response)) {
									$response .= '<div class="vbo-calcrates-info">'.$extra_response.'</div>';
								}
								$response .= '</div>';
							}
							//Debug
							//$response .= '<br/><pre>'.print_r($arr_res, true).'</pre><br/>';
						} else {
							$response = 'e4j.error.'.JText::sprintf('VBCALCRATESROOMNOTAVAILCOMBO', date($df, $checkin_ts), date($df, $checkout_ts));
							/**
							 * Set a response code so that the View calendar can understand that the room is not available or has no rates.
							 * 
							 * @since 	1.14 (J) - 1.4.0 (WP)
							 */
							if (isset($arr_res['fullybooked']) && in_array($id_room, $arr_res['fullybooked'])) {
								$response_code = -1;
							}
							//
						}
					} else {
						$response = 'e4j.error.'.$arr_res['e4j.error'];
						/**
						 * Set a response code so that the View calendar can understand that the room is not available or has no rates.
						 * 
						 * @since 	1.14 (J) - 1.4.0 (WP)
						 */
						if (isset($arr_res['fullybooked']) && in_array($id_room, $arr_res['fullybooked'])) {
							$response_code = -1;
						}
						//
					}
				} else {
					$response = (strpos($res, 'e4j.error') === false ? 'e4j.error' : '').$res;
				}
			}
			curl_close($ch);
		}

		if ($only_rates && strpos($response, 'e4j.error') === false) {
			echo json_encode($price_details);
			exit;
		}

		// do not do only echo trim($response); or the currency symbol will not be encoded on some servers
		$safe_response = array(trim($response));
		if ($only_rates && !empty($response_code)) {
			array_push($safe_response, $response_code);
		}

		echo json_encode($safe_response);
		exit;
	}

	public function cron_exec() {
		//modal box, so we do not set menu or footer
	
		VikRequest::setVar('view', VikRequest::getCmd('view', 'cronexec'));
	
		parent::display();
	}

	public function downloadcron() {
		/**
		 * @wponly lite - this task should redirect only
		 */
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking");
	}

	public function cronlogs() {
		$dbo = JFactory::getDBO();
		$pcron_id = VikRequest::getInt('cron_id', '', 'request');
		$q = "SELECT * FROM `#__vikbooking_cronjobs` WHERE `id`=".(int)$pcron_id.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$cron_data = $dbo->loadAssoc();
			$cron_data['logs'] = empty($cron_data['logs']) ? '--------' : $cron_data['logs'];
			echo '<pre>'.print_r($cron_data['logs'], true).'</pre>';
		}
	}

	public function updatecron() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$this->do_updatecron();
	}

	public function updatecronstay() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$this->do_updatecron(true);
	}

	private function do_updatecron($stay = false) {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$pwhereup = VikRequest::getInt('where', '', 'request');
		$pcron_name = VikRequest::getString('cron_name', '', 'request');
		$pclass_file = VikRequest::getString('class_file', '', 'request');
		$ppublished = VikRequest::getString('published', '', 'request');
		$ppublished = intval($ppublished) == 1 ? 1 : 0;
		$vikcronparams = VikRequest::getVar('vikcronparams', array(), 'request', 'none', VIKREQUEST_ALLOWHTML);
		$cronparamarr = array();
		$cronparamstr = '';
		if (count($vikcronparams) > 0) {
			foreach ($vikcronparams as $setting => $cont) {
				if (strlen($setting) > 0) {
					$cronparamarr[$setting] = $cont;
				}
			}
			if (count($cronparamarr) > 0) {
				$cronparamstr = json_encode($cronparamarr);
			}
		}
		$goto = "index.php?option=com_vikbooking&task=crons";
		if (empty($pcron_name) || empty($pclass_file) || empty($pwhereup)) {
			$mainframe->redirect($goto);
			exit;
		}
		//launch update() method if available
		if (file_exists(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'cronjobs'.DIRECTORY_SEPARATOR.$pclass_file)) {
			require_once(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'cronjobs'.DIRECTORY_SEPARATOR.$pclass_file);
			if (method_exists('VikCronJob', 'update')) {
				$cron_obj = new VikCronJob($pwhereup, $cronparamarr);
				$cron_obj->update();
			}
		}
		//
		$q = "UPDATE `#__vikbooking_cronjobs` SET `cron_name`=".$dbo->quote($pcron_name).",`class_file`=".$dbo->quote($pclass_file).",`params`=".$dbo->quote($cronparamstr).",`published`=".(int)$ppublished." WHERE `id`=".(int)$pwhereup.";";
		$dbo->setQuery($q);
		$dbo->execute();
		$mainframe->enqueueMessage(JText::translate('VBOCRONUPDATED'));
		if ($stay) {
			$goto = "index.php?option=com_vikbooking&task=editcron&cid[]=".$pwhereup;
		}
		$mainframe->redirect($goto);
	}

	public function createcron() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$this->do_createcron();
	}

	public function createcronstay() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$this->do_createcron(true);
	}

	private function do_createcron($stay = false) {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$pcron_name = VikRequest::getString('cron_name', '', 'request');
		$pclass_file = VikRequest::getString('class_file', '', 'request');
		$ppublished = VikRequest::getString('published', '', 'request');
		$ppublished = intval($ppublished) == 1 ? 1 : 0;
		$vikcronparams = VikRequest::getVar('vikcronparams', array(), 'request', 'none', VIKREQUEST_ALLOWHTML);
		$cronparamarr = array();
		$cronparamstr = '';
		if (count($vikcronparams) > 0) {
			foreach ($vikcronparams as $setting => $cont) {
				if (strlen($setting) > 0) {
					$cronparamarr[$setting] = $cont;
				}
			}
			if (count($cronparamarr) > 0) {
				$cronparamstr = json_encode($cronparamarr);
			}
		}
		$goto = "index.php?option=com_vikbooking&task=crons";
		if (empty($pcron_name) || empty($pclass_file)) {
			$goto = "index.php?option=com_vikbooking&task=newcron";
			$mainframe->redirect($goto);
			exit;
		}
		$q = "INSERT INTO `#__vikbooking_cronjobs` (`cron_name`,`class_file`,`params`,`published`) VALUES (".$dbo->quote($pcron_name).", ".$dbo->quote($pclass_file).", ".$dbo->quote($cronparamstr).", ".(int)$ppublished.");";
		$dbo->setQuery($q);
		$dbo->execute();
		$lid = $dbo->insertid();
		if (!empty($lid)) {
			//launch install() method if available
			if (file_exists(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'cronjobs'.DIRECTORY_SEPARATOR.$pclass_file)) {
				require_once(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'cronjobs'.DIRECTORY_SEPARATOR.$pclass_file);
				if (method_exists('VikCronJob', 'install')) {
					$cron_obj = new VikCronJob($lid, $cronparamarr);
					$cron_obj->install();
				}
			}
			//
			$mainframe->enqueueMessage(JText::translate('VBOCRONSAVED'));
			if ($stay) {
				$goto = "index.php?option=com_vikbooking&task=editcron&cid[]=".$lid;
			}
		}
		$mainframe->redirect($goto);
	}

	public function removecrons() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array());
		if (count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d){
				$q = "SELECT * FROM `#__vikbooking_cronjobs` WHERE `id`=".(int)$d.";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$cur_cron = $dbo->loadAssoc();
					//launch uninstall() method if available
					if (file_exists(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'cronjobs'.DIRECTORY_SEPARATOR.$cur_cron['class_file'])) {
						require_once(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'cronjobs'.DIRECTORY_SEPARATOR.$cur_cron['class_file']);
						if (method_exists('VikCronJob', 'uninstall')) {
							$cron_obj = new VikCronJob($cur_cron['id'], json_decode($cur_cron['params'], true));
							$cron_obj->uninstall();
						}
					}
					//
					$q = "DELETE FROM `#__vikbooking_cronjobs` WHERE `id`=".(int)$d.";";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=crons");
	}

	public function packages() {
		VikBookingHelper::printHeader("packages");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'packages'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function newpackage() {
		VikBookingHelper::printHeader("packages");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managepackage'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function editpackage() {
		VikBookingHelper::printHeader("packages");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managepackage'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function createpackage() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$this->do_createpackage();
	}

	public function createpackagestay() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$this->do_createpackage(true);
	}

	private function do_createpackage($stay = false) {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$pname = VikRequest::getString('name', '', 'request');
		$palias = VikRequest::getString('alias', '', 'request');
		$palias = empty($palias) ? $pname : $palias;
		$palias = JFilterOutput::stringURLSafe($palias);
		$pimg = VikRequest::getVar('img', null, 'files', 'array');
		$pfrom = VikRequest::getString('from', '', 'request');
		$pto = VikRequest::getString('to', '', 'request');
		$pexcludeday = VikRequest::getVar('excludeday', array());
		$strexcldates = array();
		foreach ($pexcludeday as $exclday) {
			if (!empty($exclday)) {
				$strexcldates[] = $exclday;
			}
		}
		$strexcldates = implode(';', $strexcldates);
		$prooms = VikRequest::getVar('rooms', array());
		$pminlos = VikRequest::getInt('minlos', '', 'request');
		$pminlos = $pminlos < 1 ? 1 : $pminlos;
		$pmaxlos = VikRequest::getInt('maxlos', '', 'request');
		$pmaxlos = $pmaxlos < 0 ? 0 : $pmaxlos;
		$pmaxlos = $pmaxlos < $pminlos ? 0 : $pmaxlos;
		$pcost = VikRequest::getFloat('cost', '', 'request');
		$paliq = VikRequest::getInt('aliq', '', 'request');
		$ppernight_total = VikRequest::getInt('pernight_total', '', 'request');
		$ppernight_total = $ppernight_total == 1 ? 1 : 2;
		$pperperson = VikRequest::getInt('perperson', '', 'request');
		$pperperson = $pperperson > 0 ? 1 : 0;
		$pshowoptions = VikRequest::getInt('showoptions', '', 'request');
		$pshowoptions = $pshowoptions >= 1 && $pshowoptions <= 3 ? $pshowoptions : 1;
		$pdescr = VikRequest::getString('descr', '', 'request', VIKREQUEST_ALLOWRAW);
		$pshortdescr = VikRequest::getString('shortdescr', '', 'request', VIKREQUEST_ALLOWHTML);
		$pconditions = VikRequest::getString('conditions', '', 'request', VIKREQUEST_ALLOWRAW);
		$pbenefits = VikRequest::getString('benefits', '', 'request', VIKREQUEST_ALLOWHTML);
		$ptsinit = VikBooking::getDateTimestamp($pfrom, '0', '0');
		$ptsend = VikBooking::getDateTimestamp($pto, '23', '59');
		$ptsinit = empty($ptsinit) ? time() : $ptsinit;
		$ptsend = empty($ptsend) || $ptsend < $ptsinit ? $ptsinit : $ptsend;
		//file upload
		jimport('joomla.filesystem.file');
		$gimg = "";
		if (isset($pimg) && strlen(trim($pimg['name']))) {
			$pautoresize = VikRequest::getString('autoresize', '', 'request');
			$presizeto = VikRequest::getInt('resizeto', '', 'request');
			$creativik = new vikResizer();
			$filename = JFile::makeSafe(str_replace(" ", "_", strtolower($pimg['name'])));
			$src = $pimg['tmp_name'];
			$dest = VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR;
			$j = "";
			if (file_exists($dest.$filename)) {
				$j = rand(171, 1717);
				while (file_exists($dest.$j.$filename)) {
					$j++;
				}
			}
			$finaldest = $dest.$j.$filename;
			$check = getimagesize($pimg['tmp_name']);
			if ($check[2] & imagetypes()) {
				if (VikBooking::uploadFile($src, $finaldest)) {
					$gimg = $j.$filename;
					//orig img
					$origmod = true;
					if ($pautoresize == "1" && !empty($presizeto)) {
						$origmod = $creativik->proportionalImage($finaldest, $dest.'big_'.$j.$filename, $presizeto, $presizeto);
					} else {
						VikBooking::uploadFile($finaldest, $dest.'big_'.$j.$filename, true);
					}
					//thumb
					$thumb = $creativik->proportionalImage($finaldest, $dest.'thumb_'.$j.$filename, 250, 250);
					if (!$thumb || !$origmod) {
						if (file_exists($dest.'big_'.$j.$filename)) @unlink($dest.'big_'.$j.$filename);
						if (file_exists($dest.'thumb_'.$j.$filename)) @unlink($dest.'thumb_'.$j.$filename);
						VikError::raiseWarning('', 'Error Uploading the File: '.$pimg['name']);
					}
					@unlink($finaldest);
				} else {
					VikError::raiseWarning('', 'Error while uploading image');
				}
			} else {
				VikError::raiseWarning('', 'Uploaded file is not an Image');
			}
		}
		//
		$goto = "index.php?option=com_vikbooking&task=packages";
		$q = "INSERT INTO `#__vikbooking_packages` (`name`,`alias`,`img`,`dfrom`,`dto`,`excldates`,`minlos`,`maxlos`,`cost`,`idiva`,`pernight_total`,`perperson`,`descr`,`shortdescr`,`benefits`,`conditions`,`showoptions`) VALUES (".$dbo->quote($pname).", ".$dbo->quote($palias).", ".$dbo->quote($gimg).", ".(int)$ptsinit.", ".(int)$ptsend.", ".$dbo->quote($strexcldates).", ".(int)$pminlos.", ".(int)$pmaxlos.", ".$dbo->quote($pcost).",'".$paliq."', ".(int)$ppernight_total.", ".(int)$pperperson.", ".$dbo->quote($pdescr).", ".$dbo->quote($pshortdescr).", ".$dbo->quote($pbenefits).", ".$dbo->quote($pconditions).", ".(int)$pshowoptions.");";
		$dbo->setQuery($q);
		$dbo->execute();
		$lid = $dbo->insertid();
		if (!empty($lid)) {
			$mainframe->enqueueMessage(JText::translate('VBOPKGSAVED'));
			if ($stay) {
				$goto = "index.php?option=com_vikbooking&task=editpackage&cid[]=".$lid;
			}
			foreach ($prooms as $roomid) {
				if (!empty($roomid)) {
					$q = "INSERT INTO `#__vikbooking_packages_rooms` (`idpackage`,`idroom`) VALUES (".(int)$lid.", ".(int)$roomid.");";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
		}
		$mainframe->redirect($goto);
	}

	public function updatepackage() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$this->do_updatepackage();
	}

	public function updatepackagestay() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$this->do_updatepackage(true);
	}

	private function do_updatepackage($stay = false) {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$pwhereup = VikRequest::getInt('whereup', '', 'request');
		$q = "SELECT * FROM `#__vikbooking_packages` WHERE `id`=".(int)$pwhereup.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$pkg_data = $dbo->loadAssoc();
		} else {
			VikError::raiseWarning('', 'Not Found.');
			$mainframe->redirect("index.php?option=com_vikbooking&task=packages");
			exit;
		}
		$pname = VikRequest::getString('name', '', 'request');
		$palias = VikRequest::getString('alias', '', 'request');
		$palias = empty($palias) ? $pname : $palias;
		$palias = JFilterOutput::stringURLSafe($palias);
		$pimg = VikRequest::getVar('img', null, 'files', 'array');
		$pfrom = VikRequest::getString('from', '', 'request');
		$pto = VikRequest::getString('to', '', 'request');
		$pexcludeday = VikRequest::getVar('excludeday', array());
		$strexcldates = array();
		foreach ($pexcludeday as $exclday) {
			if (!empty($exclday)) {
				$strexcldates[] = $exclday;
			}
		}
		$strexcldates = implode(';', $strexcldates);
		$prooms = VikRequest::getVar('rooms', array());
		$pminlos = VikRequest::getInt('minlos', '', 'request');
		$pminlos = $pminlos < 1 ? 1 : $pminlos;
		$pmaxlos = VikRequest::getInt('maxlos', '', 'request');
		$pmaxlos = $pmaxlos < 0 ? 0 : $pmaxlos;
		$pmaxlos = $pmaxlos < $pminlos ? 0 : $pmaxlos;
		$pcost = VikRequest::getFloat('cost', '', 'request');
		$paliq = VikRequest::getInt('aliq', '', 'request');
		$ppernight_total = VikRequest::getInt('pernight_total', '', 'request');
		$ppernight_total = $ppernight_total == 1 ? 1 : 2;
		$pperperson = VikRequest::getInt('perperson', '', 'request');
		$pperperson = $pperperson > 0 ? 1 : 0;
		$pshowoptions = VikRequest::getInt('showoptions', '', 'request');
		$pshowoptions = $pshowoptions >= 1 && $pshowoptions <= 3 ? $pshowoptions : 1;
		$pdescr = VikRequest::getString('descr', '', 'request', VIKREQUEST_ALLOWRAW);
		$pshortdescr = VikRequest::getString('shortdescr', '', 'request', VIKREQUEST_ALLOWHTML);
		$pconditions = VikRequest::getString('conditions', '', 'request', VIKREQUEST_ALLOWRAW);
		$pbenefits = VikRequest::getString('benefits', '', 'request', VIKREQUEST_ALLOWHTML);
		$ptsinit = VikBooking::getDateTimestamp($pfrom, '0', '0');
		$ptsend = VikBooking::getDateTimestamp($pto, '23', '59');
		$ptsinit = empty($ptsinit) ? time() : $ptsinit;
		$ptsend = empty($ptsend) || $ptsend < $ptsinit ? $ptsinit : $ptsend;
		//file upload
		jimport('joomla.filesystem.file');
		$gimg = "";
		if (isset($pimg) && strlen(trim($pimg['name']))) {
			$pautoresize = VikRequest::getString('autoresize', '', 'request');
			$presizeto = VikRequest::getInt('resizeto', '', 'request');
			$creativik = new vikResizer();
			$filename = JFile::makeSafe(str_replace(" ", "_", strtolower($pimg['name'])));
			$src = $pimg['tmp_name'];
			$dest = VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR;
			$j = "";
			if (file_exists($dest.$filename)) {
				$j = rand(171, 1717);
				while (file_exists($dest.$j.$filename)) {
					$j++;
				}
			}
			$finaldest = $dest.$j.$filename;
			$check = getimagesize($pimg['tmp_name']);
			if ($check[2] & imagetypes()) {
				if (VikBooking::uploadFile($src, $finaldest)) {
					$gimg = $j.$filename;
					//orig img
					$origmod = true;
					if ($pautoresize == "1" && !empty($presizeto)) {
						$origmod = $creativik->proportionalImage($finaldest, $dest.'big_'.$j.$filename, $presizeto, $presizeto);
					} else {
						VikBooking::uploadFile($finaldest, $dest.'big_'.$j.$filename, true);
					}
					//thumb
					$thumb = $creativik->proportionalImage($finaldest, $dest.'thumb_'.$j.$filename, 250, 250);
					if (!$thumb || !$origmod) {
						if (file_exists($dest.'big_'.$j.$filename)) @unlink($dest.'big_'.$j.$filename);
						if (file_exists($dest.'thumb_'.$j.$filename)) @unlink($dest.'thumb_'.$j.$filename);
						VikError::raiseWarning('', 'Error Uploading the File: '.$pimg['name']);
					}
					@unlink($finaldest);
				} else {
					VikError::raiseWarning('', 'Error while uploading image');
				}
			} else {
				VikError::raiseWarning('', 'Uploaded file is not an Image');
			}
		}
		//
		$goto = "index.php?option=com_vikbooking&task=packages";
		$q = "UPDATE `#__vikbooking_packages` SET `name`=".$dbo->quote($pname).",`alias`=".$dbo->quote($palias)."".(!empty($gimg) ? ",`img`=".$dbo->quote($gimg) : "").",`dfrom`=".(int)$ptsinit.",`dto`=".(int)$ptsend.",`excldates`=".$dbo->quote($strexcldates).",`minlos`=".(int)$pminlos.",`maxlos`=".(int)$pmaxlos.",`cost`=".$dbo->quote($pcost).",`idiva`='".$paliq."',`pernight_total`=".(int)$ppernight_total.",`perperson`=".(int)$pperperson.",`descr`=".$dbo->quote($pdescr).",`shortdescr`=".$dbo->quote($pshortdescr).",`benefits`=".$dbo->quote($pbenefits).",`conditions`=".$dbo->quote($pconditions).",`showoptions`=".(int)$pshowoptions." WHERE `id`=".(int)$pwhereup.";";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "DELETE FROM `#__vikbooking_packages_rooms` WHERE `idpackage`=".(int)$pwhereup.";";
		$dbo->setQuery($q);
		$dbo->execute();
		foreach ($prooms as $roomid) {
			if (!empty($roomid)) {
				$q = "INSERT INTO `#__vikbooking_packages_rooms` (`idpackage`,`idroom`) VALUES (".(int)$pwhereup.", ".(int)$roomid.");";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe->enqueueMessage(JText::translate('VBOPKGUPDATED'));
		if ($stay) {
			$goto = "index.php?option=com_vikbooking&task=editpackage&cid[]=".$pwhereup;
		}
		$mainframe->redirect($goto);
	}

	public function removepackages() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d){
				$q = "DELETE FROM `#__vikbooking_packages` WHERE `id`=".(int)$d.";";
				$dbo->setQuery($q);
				$dbo->execute();
				$q = "DELETE FROM `#__vikbooking_packages_rooms` WHERE `idpackage`=".(int)$d.";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=packages");
	}

	public function calendar() {
		VikBookingHelper::printHeader("19");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'calendar'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function rooms() {
		VikBookingHelper::printHeader("7");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'rooms'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function newroom() {
		VikBookingHelper::printHeader("7");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageroom'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function editroom() {
		VikBookingHelper::printHeader("7");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageroom'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function createroom() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$this->do_createroom();
	}

	public function createroomstay() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$this->do_createroom(true);
	}

	private function do_createroom($stay = false) {
		$mainframe = JFactory::getApplication();
		$pcname = VikRequest::getString('cname', '', 'request');
		$pccat = VikRequest::getVar('ccat', array(0));
		$pcdescr = VikRequest::getString('cdescr', '', 'request', VIKREQUEST_ALLOWRAW);
		$psmalldesc = VikRequest::getString('smalldesc', '', 'request', VIKREQUEST_ALLOWRAW);
		$pccarat = VikRequest::getVar('ccarat', array(0));
		$pcoptional = VikRequest::getVar('coptional', array(0));
		$pcavail = VikRequest::getString('cavail', '', 'request');
		$pautoresize = VikRequest::getString('autoresize', '', 'request');
		$presizeto = VikRequest::getString('resizeto', '', 'request');
		$pautoresizemore = VikRequest::getString('autoresizemore', '', 'request');
		$presizetomore = VikRequest::getString('resizetomore', '', 'request');
		$punits = VikRequest::getInt('units', '', 'request');
		$pimages = VikRequest::getVar('cimgmore', null, 'files', 'array');
		$pfromadult = VikRequest::getInt('fromadult', '', 'request');
		$ptoadult = VikRequest::getInt('toadult', '', 'request');
		$pfromchild = VikRequest::getInt('fromchild', '', 'request');
		$ptochild = VikRequest::getInt('tochild', '', 'request');
		$ptotpeople = VikRequest::getInt('totpeople', '', 'request');
		$pmintotpeople = VikRequest::getInt('mintotpeople', '', 'request');
		$pmintotpeople = $pmintotpeople < 1 ? 1 : $pmintotpeople;
		$plastavail = VikRequest::getString('lastavail', '', 'request');
		$plastavail = empty($plastavail) ? 0 : intval($plastavail);
		$psuggocc = VikRequest::getInt('suggocc', 1, 'request');
		$pcustprice = VikRequest::getString('custprice', '', 'request');
		$pcustprice = empty($pcustprice) ? '' : floatval($pcustprice);
		$pcustpricetxt = VikRequest::getString('custpricetxt', '', 'request', VIKREQUEST_ALLOWRAW);
		$pcustpricesubtxt = VikRequest::getString('custpricesubtxt', '', 'request', VIKREQUEST_ALLOWRAW);
		$preqinfo = VikRequest::getInt('reqinfo', '', 'request');
		$ppricecal = VikRequest::getInt('pricecal', '', 'request');
		$pdefcalcost = VikRequest::getString('defcalcost', '', 'request');
		$pmaxminpeople = VikRequest::getString('maxminpeople', '', 'request');
		$pcimgcaption = VikRequest::getVar('cimgcaption', array());
		$pmaxminpeople = in_array($pmaxminpeople, array('0', '1', '2', '3', '4', '5')) ? $pmaxminpeople : '0';
		$pseasoncal = VikRequest::getInt('seasoncal', 0, 'request');
		$pseasoncal = $pseasoncal >= 0 || $pseasoncal <= 3 ? $pseasoncal : 0;
		$pseasoncal_nights = VikRequest::getString('seasoncal_nights', '', 'request');
		$pseasoncal_prices = VikRequest::getString('seasoncal_prices', '', 'request');
		$pseasoncal_restr = VikRequest::getString('seasoncal_restr', '', 'request');
		$pmulti_units = VikRequest::getInt('multi_units', '', 'request');
		$pmulti_units = $punits > 1 ? $pmulti_units : 0;
		$psefalias = VikRequest::getString('sefalias', '', 'request');
		$psefalias = empty($psefalias) ? JFilterOutput::stringURLSafe($pcname) : JFilterOutput::stringURLSafe($psefalias);
		$pcustptitle = VikRequest::getString('custptitle', '', 'request');
		$pcustptitlew = VikRequest::getString('custptitlew', '', 'request');
		$pcustptitlew = in_array($pcustptitlew, array('before', 'after', 'replace')) ? $pcustptitlew : 'before';
		$pmetakeywords = VikRequest::getString('metakeywords', '', 'request');
		$pmetadescription = VikRequest::getString('metadescription', '', 'request');
		$pshare_with = VikRequest::getVar('share_with', array());
		$scalnights_arr = array();
		if (!empty($pseasoncal_nights)) {
			$scalnights = explode(',', $pseasoncal_nights);
			foreach ($scalnights as $scalnight) {
				if (intval(trim($scalnight)) > 0) {
					$scalnights_arr[] = intval(trim($scalnight));
				}
			}
		}
		if (count($scalnights_arr) > 0) {
			$pseasoncal_nights = implode(', ', $scalnights_arr);
		} else {
			$pseasoncal_nights = '';
			$pseasoncal = 0;
		}
		$roomparams = array('lastavail' => $plastavail, 'suggocc' => $psuggocc, 'custprice' => $pcustprice, 'custpricetxt' => $pcustpricetxt, 'custpricesubtxt' => $pcustpricesubtxt, 'reqinfo' => $preqinfo, 'pricecal' => $ppricecal, 'defcalcost' => floatval($pdefcalcost), 'maxminpeople' => $pmaxminpeople, 'seasoncal' => $pseasoncal, 'seasoncal_nights' => $pseasoncal_nights, 'seasoncal_prices' => $pseasoncal_prices, 'seasoncal_restr' => $pseasoncal_restr, 'multi_units' => $pmulti_units, 'custptitle' => $pcustptitle, 'custptitlew' => $pcustptitlew, 'metakeywords' => $pmetakeywords, 'metadescription' => $pmetadescription);
		//distinctive features
		$roomparams['features'] = array();
		if ($punits > 0) {
			for ($i=1; $i <= $punits; $i++) { 
				$distf_name = VikRequest::getVar('feature-name'.$i, array(0));
				$distf_lang = VikRequest::getVar('feature-lang'.$i, array(0));
				$distf_value = VikRequest::getVar('feature-value'.$i, array(0));
				foreach ($distf_name as $distf_k => $distf) {
					if (strlen($distf) > 0 && strlen($distf_value[$distf_k]) > 0) {
						$use_key = strlen($distf_lang[$distf_k]) > 0 ? $distf_lang[$distf_k] : $distf;
						$roomparams['features'][$i][$use_key] = $distf_value[$distf_k];
					}
				}
			}
		}

		/**
		 * Store room geo params information.
		 * 
		 * @since 	1.14 (J) - 1.4.0 (WP)
		 */
		$geo = VikBooking::getGeocodingInstance();
		$geo_params = $geo->getRoomGeoTransient(0);
		if ($geo_params !== false) {
			// make sure the geocoding service was not turned off
			$geo_enabled = VikRequest::getInt('geo_enabled', 0, 'request');
			if (!$geo_enabled) {
				$geo_params->enabled = 0;
			}
			//
			$roomparams['geo'] = $geo_params;
		}
		//

		$roomparamstr = json_encode($roomparams);

		jimport('joomla.filesystem.file');
		$updpath = VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR;
		if (!empty($pcname)) {
			if (intval($_FILES['cimg']['error']) == 0 && VikBooking::caniWrite($updpath) && trim($_FILES['cimg']['name'])!="") {
				if (@is_uploaded_file($_FILES['cimg']['tmp_name'])) {
					$safename = JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['cimg']['name'])));
					if (file_exists($updpath.$safename)) {
						$j = 1;
						while (file_exists($updpath.$j.$safename)) {
							$j++;
						}
						$pwhere = $updpath.$j.$safename;
					} else {
						$j = "";
						$pwhere = $updpath.$safename;
					}
					if (!getimagesize($_FILES['cimg']['tmp_name'])) {
						@unlink($pwhere);
						$picon = "";
					} else {
						VikBooking::uploadFile($_FILES['cimg']['tmp_name'], $pwhere);
						@chmod($pwhere, 0644);
						$picon = $j.$safename;
						if ($pautoresize=="1" && !empty($presizeto)) {
							$eforj = new vikResizer();
							$origmod = $eforj->proportionalImage($pwhere, $updpath.'r_'.$j.$safename, $presizeto, $presizeto);
							if ($origmod) {
								@unlink($pwhere);
								$picon = 'r_'.$j.$safename;
							}
						}
					}
				} else {
					$picon = "";
				}
			} else {
				$picon = "";
			}
			//more images
			$creativik = new vikResizer();
			$bigsdest = $updpath;
			$thumbsdest = $updpath;
			$dest = $updpath;
			$moreimagestr = "";
			$arrimgs = array();
			$captiontexts = array();
			$imgcaptions = array();
			foreach ($pimages['name'] as $kk=>$ci) {
				if (!empty($ci)) {
					$arrimgs[] = $kk;
					$captiontexts[] = isset($pcimgcaption[$kk]) ? $pcimgcaption[$kk] : '';
				}
			}
			foreach ($arrimgs as $ki => $imgk) {
				if (strlen(trim($pimages['name'][$imgk]))) {
					$filename = JFile::makeSafe(str_replace(" ", "_", strtolower($pimages['name'][$imgk])));
					$src = $pimages['tmp_name'][$imgk];
					$j = "";
					if (file_exists($dest.$filename)) {
						$j = rand(171, 1717);
						while (file_exists($dest.$j.$filename)) {
							$j++;
						}
					}
					$finaldest = $dest.$j.$filename;
					$check = getimagesize($pimages['tmp_name'][$imgk]);
					if ($check[2] & imagetypes()) {
						if (VikBooking::uploadFile($src, $finaldest)) {
							$gimg = $j.$filename;
							//orig img
							$origmod = true;
							if ($pautoresizemore == "1" && !empty($presizetomore)) {
								$origmod = $creativik->proportionalImage($finaldest, $bigsdest.'big_'.$j.$filename, $presizetomore, $presizetomore);
							} else {
								VikBooking::uploadFile($finaldest, $bigsdest.'big_'.$j.$filename, true);
							}
							//thumb
							$thumbsize = VikBooking::getThumbSize();
							$thumb = $creativik->proportionalImage($finaldest, $thumbsdest.'thumb_'.$j.$filename, $thumbsize, $thumbsize);
							if (!$thumb || !$origmod) {
								if (file_exists($bigsdest.'big_'.$j.$filename)) @unlink($bigsdest.'big_'.$j.$filename);
								if (file_exists($thumbsdest.'thumb_'.$j.$filename)) @unlink($thumbsdest.'thumb_'.$j.$filename);
								VikError::raiseWarning('', 'Error While Uploading the File: '.$pimages['name'][$imgk]);
							} else {
								$moreimagestr .= $j.$filename.";;";
								$imgcaptions[] = $captiontexts[$ki];
							}
							@unlink($finaldest);
						} else {
							VikError::raiseWarning('', 'Error While Uploading the File: '.$pimages['name'][$imgk]);
						}
					} else {
						VikError::raiseWarning('', 'Error While Uploading the File: '.$pimages['name'][$imgk]);
					}
				}
			}
			//end more images
			if (is_array($pccat) && count($pccat)) {
				$pccatdef="";
				foreach ($pccat as $ccat) {
					if (!empty($ccat)) {
						$pccatdef.=$ccat.";";
					}
				}
			} else {
				$pccatdef="";
			}
			if (is_array($pccarat) && count($pccarat)) {
				$pccaratdef="";
				foreach ($pccarat as $ccarat) {
					$pccaratdef.=$ccarat.";";
				}
			} else {
				$pccaratdef="";
			}
			if (is_array($pcoptional) && count($pcoptional)) {
				$pcoptionaldef="";
				foreach ($pcoptional as $coptional) {
					$pcoptionaldef.=$coptional.";";
				}
			} else {
				$pcoptionaldef="";
			}
			$pcavaildef=($pcavail=="yes" ? "1" : "0");
			if ($pfromadult > $ptoadult) {
				$pfromadult = 1;
				$ptoadult = 1;
			}
			if ($pfromchild > $ptochild) {
				$pfromchild = 1;
				$ptochild = 1;
			}
			$dbo = JFactory::getDbo();
			$q = "INSERT INTO `#__vikbooking_rooms` (`name`,`img`,`idcat`,`idcarat`,`idopt`,`info`,`avail`,`units`,`moreimgs`,`fromadult`,`toadult`,`fromchild`,`tochild`,`smalldesc`,`totpeople`,`mintotpeople`,`params`,`imgcaptions`,`alias`) VALUES(".$dbo->quote($pcname).",".$dbo->quote($picon).",".$dbo->quote($pccatdef).",".$dbo->quote($pccaratdef).",".$dbo->quote($pcoptionaldef).",".$dbo->quote($pcdescr).",".$dbo->quote($pcavaildef).",".($punits > 0 ? $dbo->quote($punits) : "'1'").", ".$dbo->quote($moreimagestr).", '".$pfromadult."', '".$ptoadult."', '".$pfromchild."', '".$ptochild."', ".$dbo->quote($psmalldesc).", ".$ptotpeople.", ".$pmintotpeople.", ".$dbo->quote($roomparamstr).", ".$dbo->quote(json_encode($imgcaptions)).",".$dbo->quote($psefalias).");";
			$dbo->setQuery($q);
			$dbo->execute();
			$lid = $dbo->insertid();
			if (!empty($lid)) {
				/**
				 * Share availability calendars with other rooms.
				 * 
				 * @since 	1.13
				 */
				// always reset relations for this main room
				$q = "DELETE FROM `#__vikbooking_calendars_xref` WHERE `mainroom`={$lid};";
				$dbo->setQuery($q);
				$dbo->execute();
				$newxref = array();
				foreach ($pshare_with as $cldroom) {
					if (!empty($cldroom)) {
						array_push($newxref, (int)$cldroom);
					}
				}
				foreach ($newxref as $cldroom) {
					$q = "INSERT INTO `#__vikbooking_calendars_xref` (`mainroom`, `childroom`) VALUES ({$lid}, {$cldroom});";
					$dbo->setQuery($q);
					$dbo->execute();
				}
				//

				if ($stay === true) {
					$mainframe->enqueueMessage(JText::translate('VBOROOMSAVEOK').' - <a href="index.php?option=com_vikbooking&task=tariffs&cid[]='.$lid.'">'.JText::translate('VBOGOTORATES').'</a>');
					$mainframe->redirect("index.php?option=com_vikbooking&task=editroom&cid[]=".$lid);
					exit;
				} else {
					$mainframe->redirect("index.php?option=com_vikbooking&task=tariffs&cid[]=".$lid);
				}
			} else {
				$mainframe->redirect("index.php?option=com_vikbooking&task=rooms");
			}
		} else {
			$mainframe->redirect("index.php?option=com_vikbooking&task=rooms");
		}
	}

	public function updateroom() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$this->do_updateroom();
	}

	public function updateroomstay() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$this->do_updateroom(true);
	}

	private function do_updateroom($stay = false) {
		$mainframe = JFactory::getApplication();
		$pcname = VikRequest::getString('cname', '', 'request');
		$pccat = VikRequest::getVar('ccat', array(0));
		$pcdescr = VikRequest::getString('cdescr', '', 'request', VIKREQUEST_ALLOWRAW);
		$psmalldesc = VikRequest::getString('smalldesc', '', 'request', VIKREQUEST_ALLOWRAW);
		$pccarat = VikRequest::getVar('ccarat', array(0));
		$pcoptional = VikRequest::getVar('coptional', array(0));
		$pcavail = VikRequest::getString('cavail', '', 'request');
		$pwhereup = VikRequest::getInt('whereup', 0, 'request');
		$pautoresize = VikRequest::getString('autoresize', '', 'request');
		$presizeto = VikRequest::getString('resizeto', '', 'request');
		$pautoresizemore = VikRequest::getString('autoresizemore', '', 'request');
		$presizetomore = VikRequest::getString('resizetomore', '', 'request');
		$punits = VikRequest::getInt('units', '', 'request');
		$pimages = VikRequest::getVar('cimgmore', null, 'files', 'array');
		$pactmoreimgs = VikRequest::getString('actmoreimgs', '', 'request');
		$pfromadult = VikRequest::getInt('fromadult', '', 'request');
		$ptoadult = VikRequest::getInt('toadult', '', 'request');
		$pfromchild = VikRequest::getInt('fromchild', '', 'request');
		$ptochild = VikRequest::getInt('tochild', '', 'request');
		$padultsdiffchdisc = VikRequest::getVar('adultsdiffchdisc', array(0));
		$padultsdiffval = VikRequest::getVar('adultsdiffval', array(0));
		$padultsdiffnum = VikRequest::getVar('adultsdiffnum', array(0));
		$padultsdiffvalpcent = VikRequest::getVar('adultsdiffvalpcent', array(0));
		$padultsdiffpernight = VikRequest::getVar('adultsdiffpernight', array(0));
		$ptotpeople = VikRequest::getInt('totpeople', '', 'request');
		$pmintotpeople = VikRequest::getInt('mintotpeople', '', 'request');
		$pmintotpeople = $pmintotpeople < 1 ? 1 : $pmintotpeople;
		$plastavail = VikRequest::getString('lastavail', '', 'request');
		$plastavail = empty($plastavail) ? 0 : intval($plastavail);
		$psuggocc = VikRequest::getInt('suggocc', 1, 'request');
		$pcustprice = VikRequest::getString('custprice', '', 'request');
		$pcustprice = empty($pcustprice) ? '' : floatval($pcustprice);
		$pcustpricetxt = VikRequest::getString('custpricetxt', '', 'request', VIKREQUEST_ALLOWRAW);
		$pcustpricesubtxt = VikRequest::getString('custpricesubtxt', '', 'request', VIKREQUEST_ALLOWRAW);
		$preqinfo = VikRequest::getInt('reqinfo', '', 'request');
		$ppricecal = VikRequest::getInt('pricecal', '', 'request');
		$pdefcalcost = VikRequest::getString('defcalcost', '', 'request');
		$pmaxminpeople = VikRequest::getString('maxminpeople', '', 'request');
		$pcimgcaption = VikRequest::getVar('cimgcaption', array());
		$pimgsorting = VikRequest::getVar('imgsorting', array());
		$pupdatecaption = VikRequest::getInt('updatecaption', '', 'request');
		$pmaxminpeople = in_array($pmaxminpeople, array('0', '1', '2', '3', '4', '5')) ? $pmaxminpeople : '0';
		$pseasoncal = VikRequest::getInt('seasoncal', 0, 'request');
		$pseasoncal = $pseasoncal >= 0 || $pseasoncal <= 3 ? $pseasoncal : 0;
		$pseasoncal_nights = VikRequest::getString('seasoncal_nights', '', 'request');
		$pseasoncal_prices = VikRequest::getString('seasoncal_prices', '', 'request');
		$pseasoncal_restr = VikRequest::getString('seasoncal_restr', '', 'request');
		$pmulti_units = VikRequest::getInt('multi_units', '', 'request');
		$pmulti_units = $punits > 1 ? $pmulti_units : 0;
		$psefalias = VikRequest::getString('sefalias', '', 'request');
		$psefalias = empty($psefalias) ? JFilterOutput::stringURLSafe($pcname) : JFilterOutput::stringURLSafe($psefalias);
		$pcustptitle = VikRequest::getString('custptitle', '', 'request');
		$pcustptitlew = VikRequest::getString('custptitlew', '', 'request');
		$pcustptitlew = in_array($pcustptitlew, array('before', 'after', 'replace')) ? $pcustptitlew : 'before';
		$pmetakeywords = VikRequest::getString('metakeywords', '', 'request');
		$pmetadescription = VikRequest::getString('metadescription', '', 'request');
		$pshare_with = VikRequest::getVar('share_with', array());
		$scalnights_arr = array();
		if (!empty($pseasoncal_nights)) {
			$scalnights = explode(',', $pseasoncal_nights);
			foreach ($scalnights as $scalnight) {
				if (intval(trim($scalnight)) > 0) {
					$scalnights_arr[] = intval(trim($scalnight));
				}
			}
		}
		if (count($scalnights_arr) > 0) {
			$pseasoncal_nights = implode(', ', $scalnights_arr);
		} else {
			$pseasoncal_nights = '';
			$pseasoncal = 0;
		}
		$roomparams = array('lastavail' => $plastavail, 'suggocc' => $psuggocc, 'custprice' => $pcustprice, 'custpricetxt' => $pcustpricetxt, 'custpricesubtxt' => $pcustpricesubtxt, 'reqinfo' => $preqinfo, 'pricecal' => $ppricecal, 'defcalcost' => floatval($pdefcalcost), 'maxminpeople' => $pmaxminpeople, 'seasoncal' => $pseasoncal, 'seasoncal_nights' => $pseasoncal_nights, 'seasoncal_prices' => $pseasoncal_prices, 'seasoncal_restr' => $pseasoncal_restr, 'multi_units' => $pmulti_units, 'custptitle' => $pcustptitle, 'custptitlew' => $pcustptitlew, 'metakeywords' => $pmetakeywords, 'metadescription' => $pmetadescription);
		//distinctive features
		$roomparams['features'] = array();
		$newfeatures = array();
		if ($punits > 0) {
			for ($i=1; $i <= $punits; $i++) { 
				$distf_name = VikRequest::getVar('feature-name'.$i, array(0));
				$distf_lang = VikRequest::getVar('feature-lang'.$i, array(0));
				$distf_value = VikRequest::getVar('feature-value'.$i, array(0));
				foreach ($distf_name as $distf_k => $distf) {
					if (strlen($distf) > 0 && strlen($distf_value[$distf_k]) > 0) {
						$use_key = strlen($distf_lang[$distf_k]) > 0 ? $distf_lang[$distf_k] : $distf;
						$roomparams['features'][$i][$use_key] = $distf_value[$distf_k];
						if ($distf_k < 1) {
							//check only the first feature
							$newfeatures[$i][$use_key] = $distf_value[$distf_k];
						}
					}
				}
			}
		}

		/**
		 * Store room geo params information.
		 * 
		 * @since 	1.14 (J) - 1.4.0 (WP)
		 */
		$geo = VikBooking::getGeocodingInstance();
		$geo_params = $geo->getRoomGeoTransient($pwhereup);
		if ($geo_params !== false) {
			// make sure the geocoding service was not turned off
			$geo_enabled = VikRequest::getInt('geo_enabled', 0, 'request');
			if (!$geo_enabled) {
				$geo_params->enabled = 0;
			}
			//
			$roomparams['geo'] = $geo_params;
		}
		//

		$roomparamstr = json_encode($roomparams);

		jimport('joomla.filesystem.file');
		$updpath = VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR;
		if (!empty($pcname)) {
			if (intval($_FILES['cimg']['error']) == 0 && VikBooking::caniWrite($updpath) && trim($_FILES['cimg']['name'])!="") {
				if (@is_uploaded_file($_FILES['cimg']['tmp_name'])) {
					$safename = JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['cimg']['name'])));
					if (file_exists($updpath.$safename)) {
						$j = 1;
						while (file_exists($updpath.$j.$safename)) {
							$j++;
						}
						$pwhere = $updpath.$j.$safename;
					} else {
						$j = "";
						$pwhere = $updpath.$safename;
					}
					if (!getimagesize($_FILES['cimg']['tmp_name'])) {
						@unlink($pwhere);
						$picon = "";
					} else {
						VikBooking::uploadFile($_FILES['cimg']['tmp_name'], $pwhere);
						@chmod($pwhere, 0644);
						$picon = $j.$safename;
						if ($pautoresize == "1" && !empty($presizeto)) {
							$eforj = new vikResizer();
							$origmod = $eforj->proportionalImage($pwhere, $updpath.'r_'.$j.$safename, $presizeto, $presizeto);
							if ($origmod) {
								@unlink($pwhere);
								$picon = 'r_'.$j.$safename;
							}
						}
					}
				} else {
					$picon = "";
				}
			} else {
				$picon = "";
			}
			//more images
			$creativik = new vikResizer();
			$bigsdest = $updpath;
			$thumbsdest = $updpath;
			$dest = $updpath;
			$moreimagestr = $pactmoreimgs;
			$arrimgs = array();
			$captiontexts = array();
			$imgcaptions = array();
			//captions of uploaded extra images
			if (!empty($pactmoreimgs)) {
				$sploimgs = explode(';;', $pactmoreimgs);
				foreach ($sploimgs as $ki => $oimg) {
					if (!empty($oimg)) {
						$oldcaption = VikRequest::getString('caption'.$ki, '', 'request', VIKREQUEST_ALLOWHTML);
						$imgcaptions[] = $oldcaption;
					}
				}
			}
			//
			foreach ($pimages['name'] as $kk=>$ci) {
				if (!empty($ci)) {
					$arrimgs[] = $kk;
					$captiontexts[] = isset($pcimgcaption[$kk]) ? $pcimgcaption[$kk] : '';
				}
			}
			foreach ($arrimgs as $ki => $imgk) {
				if (strlen(trim($pimages['name'][$imgk]))) {
					$filename = JFile::makeSafe(str_replace(" ", "_", strtolower($pimages['name'][$imgk])));
					$src = $pimages['tmp_name'][$imgk];
					$j = "";
					if (file_exists($dest.$filename)) {
						$j = rand(171, 1717);
						while (file_exists($dest.$j.$filename)) {
							$j++;
						}
					}
					$finaldest = $dest.$j.$filename;
					$check = getimagesize($pimages['tmp_name'][$imgk]);
					if ($check[2] & imagetypes()) {
						if (VikBooking::uploadFile($src, $finaldest)) {
							$gimg = $j.$filename;
							//orig img
							$origmod = true;
							if ($pautoresizemore == "1" && !empty($presizetomore)) {
								$origmod = $creativik->proportionalImage($finaldest, $bigsdest.'big_'.$j.$filename, $presizetomore, $presizetomore);
							} else {
								VikBooking::uploadFile($finaldest, $bigsdest.'big_'.$j.$filename, true);
							}
							//thumb
							$thumbsize = VikBooking::getThumbSize();
							$thumb = $creativik->proportionalImage($finaldest, $thumbsdest.'thumb_'.$j.$filename, $thumbsize, $thumbsize);
							if (!$thumb || !$origmod) {
								if (file_exists($bigsdest.'big_'.$j.$filename)) @unlink($bigsdest.'big_'.$j.$filename);
								if (file_exists($thumbsdest.'thumb_'.$j.$filename)) @unlink($thumbsdest.'thumb_'.$j.$filename);
								VikError::raiseWarning('', 'Error While Uploading the File: '.$pimages['name'][$imgk]);
							} else {
								$moreimagestr .= $j.$filename.";;";
								$imgcaptions[] = $captiontexts[$ki];
							}
							@unlink($finaldest);
						} else {
							VikError::raiseWarning('', 'Error While Uploading the File: '.$pimages['name'][$imgk]);
						}
					} else {
						VikError::raiseWarning('', 'Error While Uploading the File: '.$pimages['name'][$imgk]);
					}
				}
			}
			//sorting of extra images
			$sorted_extraim = array();
			$sorted_captions = array();
			$extraim_parts = explode(';;', $moreimagestr);
			foreach ($pimgsorting as $k => $v) {
				$capkey = -1;
				if (isset($extraim_parts[$k])) {
					$sorted_extraim[] = $v;
					foreach ($extraim_parts as $oldk => $oldv) {
						if ($oldv == $v) {
							$capkey = $oldk;
							break;
						}
					}
				}
				if (isset($imgcaptions[$capkey])) {
					$sorted_captions[] = $imgcaptions[$capkey];
				}
			}
			$tot_sorted_im = count($sorted_extraim);
			if ($tot_sorted_im != count($extraim_parts)) {
				foreach ($extraim_parts as $k => $v) {
					if ($k <= ($tot_sorted_im - 1)) {
						continue;
					}
					$sorted_extraim[] = $v;
					if (isset($imgcaptions[$k])) {
						$sorted_captions[] = $imgcaptions[$k];
					}
				}
			}
			$moreimagestr = implode(';;', $sorted_extraim);
			$imgcaptions = $sorted_captions;
			//end more images
			if (is_array($pccat) && count($pccat)) {
				$pccatdef = "";
				foreach ($pccat as $ccat) {
					if (!empty($ccat)) {
						$pccatdef .= $ccat.";";
					}
				}
			} else {
				$pccatdef = "";
			}
			if (is_array($pccarat) && count($pccarat)) {
				$pccaratdef = "";
				foreach ($pccarat as $ccarat) {
					$pccaratdef .= $ccarat.";";
				}
			} else {
				$pccaratdef = "";
			}
			if (is_array($pcoptional) && count($pcoptional)) {
				$pcoptionaldef = "";
				foreach ($pcoptional as $coptional) {
					$pcoptionaldef .= $coptional.";";
				}
			} else {
				$pcoptionaldef = "";
			}
			$pcavaildef=($pcavail=="yes" ? "1" : "0");
			if ($pfromadult > $ptoadult) {
				$pfromadult = 1;
				$ptoadult = 1;
			}
			if ($pfromchild > $ptochild) {
				$pfromchild = 1;
				$ptochild = 1;
			}
			$dbo = JFactory::getDBO();
			//adults charges/discounts
			$adchdisctouch = false;
			$q = "SELECT * FROM `#__vikbooking_rooms` WHERE `id`='".$pwhereup."';";
			$dbo->setQuery($q);
			$dbo->execute();
			$oldroom = $dbo->loadAssocList();
			$oldroom = $oldroom[0];
			if ($oldroom['fromadult'] == $pfromadult && $oldroom['toadult'] == $ptoadult) {
				if ($oldroom['toadult'] > 1 && $oldroom['fromadult'] < $oldroom['toadult'] && @count($padultsdiffnum) > 0) {
					$startadind = $oldroom['fromadult'] > 0 ? $oldroom['fromadult'] : 1;
					for($adi = $startadind; $adi <= $oldroom['toadult']; $adi++) {
						foreach ($padultsdiffnum as $kad=>$vad) {
							if (intval($vad) == intval($adi) && strlen($padultsdiffval[$kad]) > 0) {
								$adchdisctouch = true;
								$inschdisc = intval($padultsdiffchdisc[$kad]) == 1 ? 1 : 2;
								$insvalpcent = intval($padultsdiffvalpcent[$kad]) == 1 ? 1 : 2;
								$inspernight = intval($padultsdiffpernight[$kad]) == 1 ? 1 : 0;
								$insvalue = floatval($padultsdiffval[$kad]);
								//check if it exists
								$q = "SELECT `id` FROM `#__vikbooking_adultsdiff` WHERE `idroom`='".$oldroom['id']."' AND `adults`='".$adi."';";
								$dbo->setQuery($q);
								$dbo->execute();
								if ($dbo->getNumRows() > 0) {
									if ($insvalue > 0) {
										//update
										$q = "UPDATE `#__vikbooking_adultsdiff` SET `chdisc`='".$inschdisc."', `valpcent`='".$insvalpcent."', `value`='".$insvalue."', `pernight`='".$inspernight."' WHERE `idroom`='".$oldroom['id']."' AND `adults`='".$adi."';";
										$dbo->setQuery($q);
										$dbo->execute();
									} else {
										//delete
										$q = "DELETE FROM `#__vikbooking_adultsdiff` WHERE `idroom`='".$oldroom['id']."' AND `adults`='".$adi."';";
										$dbo->setQuery($q);
										$dbo->execute();
									}
								} else {
									//insert
									$q = "INSERT INTO `#__vikbooking_adultsdiff` (`idroom`,`chdisc`,`valpcent`,`value`,`adults`,`pernight`) VALUES('".$oldroom['id']."', '".$inschdisc."', '".$insvalpcent."', '".$insvalue."', '".$adi."', '".$inspernight."');";
									$dbo->setQuery($q);
									$dbo->execute();
								}
							}
						}
					}
				}
			} else {
				//min and max adults num have changed, delete
				$q = "DELETE FROM `#__vikbooking_adultsdiff` WHERE `idroom`='".$oldroom['id']."';";
				$dbo->setQuery($q);
				$dbo->execute();
			}
			if ($adchdisctouch == true) {
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::translate('VBUPDROOMADCHDISCSAVED'));
			}
			//
			//check distinctive features if there were any changes
			$old_rparams = json_decode($oldroom['params'], true);
			if (array_key_exists('features', $old_rparams)) {
				$oldfeatures = array();
				foreach ($old_rparams['features'] as $rnumunit => $oldfeat) {
					foreach ($oldfeat as $featname => $featval) {
						$oldfeatures[$rnumunit][$featname] = $featval;
						break;
					}
				}
				if ($oldfeatures != $newfeatures) {
					//changes were made to the first index (Room Number by default) of the distinctive features
					//set to NULL all the already set roomindexes in bookings
					$q = "UPDATE `#__vikbooking_ordersrooms` SET `roomindex`=NULL WHERE `idroom`=".(int)$oldroom['id'].";";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
			//
			$q = "UPDATE `#__vikbooking_rooms` SET `name`=".$dbo->quote($pcname).",".(strlen($picon) > 0 ? "`img`='".$picon."'," : "")."`idcat`=".$dbo->quote($pccatdef).",`idcarat`=".$dbo->quote($pccaratdef).",`idopt`=".$dbo->quote($pcoptionaldef).",`info`=".$dbo->quote($pcdescr).",`avail`=".$dbo->quote($pcavaildef).",`units`=".($punits > 0 ? $dbo->quote($punits) : "'1'").",`moreimgs`=".$dbo->quote($moreimagestr).",`fromadult`='".$pfromadult."',`toadult`='".$ptoadult."',`fromchild`='".$pfromchild."',`tochild`='".$ptochild."',`smalldesc`=".$dbo->quote($psmalldesc).",`totpeople`=".$ptotpeople.",`mintotpeople`=".$pmintotpeople.",`params`=".$dbo->quote($roomparamstr).",`imgcaptions`=".$dbo->quote(json_encode($imgcaptions)).",`alias`=".$dbo->quote($psefalias)." WHERE `id`=".$dbo->quote($pwhereup).";";
			$dbo->setQuery($q);
			$dbo->execute();

			/**
			 * Share availability calendars with other rooms.
			 * 
			 * @since 	1.13
			 */
			// always reset relations for this main room
			$q = "DELETE FROM `#__vikbooking_calendars_xref` WHERE `mainroom`={$pwhereup};";
			$dbo->setQuery($q);
			$dbo->execute();
			$newxref = array();
			foreach ($pshare_with as $cldroom) {
				if (!empty($cldroom)) {
					array_push($newxref, (int)$cldroom);
				}
			}
			foreach ($newxref as $cldroom) {
				$q = "INSERT INTO `#__vikbooking_calendars_xref` (`mainroom`, `childroom`) VALUES ({$pwhereup}, {$cldroom});";
				$dbo->setQuery($q);
				$dbo->execute();
			}
			//
		}
		$mainframe->enqueueMessage(JText::translate('VBUPDROOMOK'));
		if ($pupdatecaption == 1 || $stay === true) {
			$mainframe->redirect("index.php?option=com_vikbooking&task=editroom&cid[]=".$pwhereup);
		} else {
			$mainframe->redirect("index.php?option=com_vikbooking&task=rooms");
		}
	}

	public function modavail() {
		$cid = VikRequest::getVar('cid', array(0));
		$room = $cid[0];
		if (!empty($room)) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `avail` FROM `#__vikbooking_rooms` WHERE `id`=".$dbo->quote($room).";";
			$dbo->setQuery($q);
			$dbo->execute();
			$get = $dbo->loadAssocList();
			$q = "UPDATE `#__vikbooking_rooms` SET `avail`='".(intval($get[0]['avail'])==1 ? 0 : 1)."' WHERE `id`=".$dbo->quote($room).";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=rooms");
	}

	public function removeroom() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikbooking_rooms` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
				$q = "DELETE FROM `#__vikbooking_dispcost` WHERE `idroom`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=rooms");
	}

	public function tariffs() {
		VikBookingHelper::printHeader("fares");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'tariffs'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function removetariffs() {
		$ids = VikRequest::getVar('cid', array(0));
		$proomid = VikRequest::getInt('roomid', '', 'request');
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $r) {
				$x=explode(";", $r);
				foreach ($x as $rm) {
					if (!empty($rm)) {
						$q = "DELETE FROM `#__vikbooking_dispcost` WHERE `id`=".$dbo->quote($rm).";";
						$dbo->setQuery($q);
						$dbo->execute();
					}
				}
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=tariffs&cid[]=".$proomid);
	}

	public function editbusy() {
		VikBookingHelper::printHeader("8");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'editbusy'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function updatebusy() {
		$this->do_updatebusy();
	}

	public function updatebusydoinv() {
		$this->do_updatebusy('geninvoices');
	}

	private function do_updatebusy($callback = '') {
		$pidorder = VikRequest::getInt('idorder', 0, 'request');
		$pcheckindate = VikRequest::getString('checkindate', '', 'request');
		$pcheckoutdate = VikRequest::getString('checkoutdate', '', 'request');
		$pcheckinh = VikRequest::getString('checkinh', '', 'request');
		$pcheckinm = VikRequest::getString('checkinm', '', 'request');
		$pcheckouth = VikRequest::getString('checkouth', '', 'request');
		$pcheckoutm = VikRequest::getString('checkoutm', '', 'request');
		$pcustdata = VikRequest::getString('custdata', '', 'request');
		$pareprices = VikRequest::getString('areprices', '', 'request');
		$ptotpaid = VikRequest::getString('totpaid', '', 'request');
		$prefund = VikRequest::getString('refund', '', 'request');
		$pfrominv = VikRequest::getInt('frominv', '', 'request');
		$pvcm = VikRequest::getInt('vcm', '', 'request');
		$pgoto = VikRequest::getString('goto', '', 'request');
		$pextracn = VikRequest::getVar('extracn', array());
		$pextracc = VikRequest::getVar('extracc', array());
		$pextractx = VikRequest::getVar('extractx', array());
		$dbo = JFactory::getDbo();
		$user = JFactory::getUser();
		$mainframe = JFactory::getApplication();
		$actnow = time();
		$nowdf = VikBooking::getDateFormat(true);
		if ($nowdf == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($nowdf == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}
		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`=".$pidorder.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$ord = $dbo->loadAssocList();
			$q = "SELECT `or`.*,`r`.`name`,`r`.`idopt`,`r`.`units`,`r`.`fromadult`,`r`.`toadult` FROM `#__vikbooking_ordersrooms` AS `or`,`#__vikbooking_rooms` AS `r` WHERE `or`.`idorder`=".$ord[0]['id']." AND `or`.`idroom`=`r`.`id` ORDER BY `or`.`id` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$ordersrooms = $dbo->loadAssocList();
			//do not touch this array property because it's used by VCM
			$ord[0]['rooms_info'] = $ordersrooms;
			//Package or custom rate
			$is_package = !empty($ord[0]['pkg']) ? true : false;
			$is_cust_cost = false;
			foreach ($ordersrooms as $kor => $or) {
				if ($is_package !== true && !empty($or['cust_cost']) && $or['cust_cost'] > 0.00) {
					$is_cust_cost = true;
					break;
				}
			}
			//
			//VikBooking 1.5 room switching
			$toswitch = array();
			$idbooked = array();
			$rooms_units = array();
			$q = "SELECT `id`,`name`,`units` FROM `#__vikbooking_rooms`;";
			$dbo->setQuery($q);
			$dbo->execute();
			$all_rooms = $dbo->loadAssocList();
			foreach ($all_rooms as $rr) {
				$rooms_units[$rr['id']]['name'] = $rr['name'];
				$rooms_units[$rr['id']]['units'] = $rr['units'];
			}
			foreach ($ordersrooms as $ind => $or) {
				$switch_command = VikRequest::getString('switch_'.$or['id'], '', 'request');
				if (!empty($switch_command) && intval($switch_command) != $or['idroom'] && array_key_exists(intval($switch_command), $rooms_units)) {
					if (!isset($idbooked[$or['idroom']])) {
						$idbooked[$or['idroom']] = 0;
					}
					$idbooked[$or['idroom']]++;
					$orkey = count($toswitch);
					$toswitch[$orkey]['from'] = $or['idroom'];
					$toswitch[$orkey]['to'] = intval($switch_command);
					$toswitch[$orkey]['record'] = $or;
				}
			}
			if (count($toswitch) > 0 && (!empty($ordersrooms[0]['idtar']) || $is_package || $is_cust_cost)) {
				foreach ($toswitch as $ksw => $rsw) {
					$plusunit = array_key_exists($rsw['to'], $idbooked) ? $idbooked[$rsw['to']] : 0;
					if (!VikBooking::roomBookable($rsw['to'], ($rooms_units[$rsw['to']]['units'] + $plusunit), $ord[0]['checkin'], $ord[0]['checkout'])) {
						unset($toswitch[$ksw]);
						VikError::raiseWarning('', JText::sprintf('VBSWITCHRERR', $rsw['record']['name'], $rooms_units[$rsw['to']]['name']));
					}
				}
				if (count($toswitch) > 0) {
					//reset first record rate
					reset($ordersrooms);
					$q = "UPDATE `#__vikbooking_ordersrooms` SET `idtar`=NULL,`roomindex`=NULL,`room_cost`=NULL WHERE `id`=".$ordersrooms[0]['id'].";";
					$dbo->setQuery($q);
					$dbo->execute();
					//
					$app = JFactory::getApplication();
					foreach ($toswitch as $ksw => $rsw) {
						$q = "UPDATE `#__vikbooking_ordersrooms` SET `idroom`=".$rsw['to'].",`idtar`=NULL,`roomindex`=NULL,`room_cost`=NULL WHERE `id`=".$rsw['record']['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
						$app->enqueueMessage(JText::sprintf('VBSWITCHROK', $rsw['record']['name'], $rooms_units[$rsw['to']]['name']));
						//update Notes field for this booking to keep track of the previous room that was assigned
						$prev_room_name = array_key_exists($rsw['from'], $rooms_units) ? $rooms_units[$rsw['from']]['name'] : '';
						if (!empty($prev_room_name)) {
							$new_notes = JText::sprintf('VBOPREVROOMMOVED', $prev_room_name, date($df.' H:i:s'))."\n".$ord[0]['adminnotes'];
							$q = "UPDATE `#__vikbooking_orders` SET `adminnotes`=".$dbo->quote($new_notes)." WHERE `id`=".(int)$ord[0]['id'].";";
							$dbo->setQuery($q);
							$dbo->execute();
						}
						//
						if ($ord[0]['status'] == 'confirmed') {
							//update record in _busy
							$q = "SELECT `b`.`id`,`b`.`idroom`,`ob`.`idorder` FROM `#__vikbooking_busy` AS `b`,`#__vikbooking_ordersbusy` AS `ob` WHERE `b`.`idroom`=" . $rsw['from'] . " AND `b`.`id`=`ob`.`idbusy` AND `ob`.`idorder`=".$ord[0]['id']." LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
							if ($dbo->getNumRows() == 1) {
								$cur_busy = $dbo->loadAssocList();
								$q = "UPDATE `#__vikbooking_busy` SET `idroom`=".$rsw['to']." WHERE `id`=".$cur_busy[0]['id']." AND `idroom`=".$cur_busy[0]['idroom']." LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
							}
							//Invoke Channel Manager
							$vcm_autosync = VikBooking::vcmAutoUpdate();
							if ($vcm_autosync > 0) {
								$vcm_obj = VikBooking::getVcmInvoker();
								$vcm_obj->setOids(array($ord[0]['id']))->setSyncType('modify')->setOriginalBooking($ord[0]);
								$sync_result = $vcm_obj->doSync();
								if ($sync_result === false) {
									$vcm_err = $vcm_obj->getError();
									VikError::raiseWarning('', JText::translate('VBCHANNELMANAGERRESULTKO').' <a href="index.php?option=com_vikchannelmanager" target="_blank">'.JText::translate('VBCHANNELMANAGEROPEN').'</a> '.(strlen($vcm_err) > 0 ? '('.$vcm_err.')' : ''));
								}
							} elseif (file_exists(VCM_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "synch.vikbooking.php")) {
								VikError::raiseNotice('', JText::translate('VBCHANNELMANAGERINVOKEASK').' <form action="index.php?option=com_vikbooking" method="post"><input type="hidden" name="option" value="com_vikbooking"/><input type="hidden" name="task" value="invoke_vcm"/><input type="hidden" name="stype" value="modify"/><input type="hidden" name="cid[]" value="'.$ord[0]['id'].'"/><input type="hidden" name="origb" value="'.urlencode(json_encode($ord[0])).'"/><input type="hidden" name="returl" value="'.urlencode("index.php?option=com_vikbooking&task=editbusy".($pvcm == 1 ? '&vcm=1' : '')."&cid[]=".$ord[0]['id']).'"/><button type="submit" class="btn btn-primary">'.JText::translate('VBCHANNELMANAGERSENDRQ').'</button></form>');
							}
							//
						} elseif ($ord[0]['status'] == 'standby') {
							//remove record in _tmplock
							$q = "DELETE FROM `#__vikbooking_tmplock` WHERE `idorder`=" . intval($ord[0]['id']) . ";";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}

					// unset any previously booked room due to calendar sharing
					VikBooking::cleanSharedCalendarsBusy($ord[0]['id']);
					// check if some of the rooms booked have shared calendars
					VikBooking::updateSharedCalendars($ord[0]['id']);
					//

					//Booking History
					VikBooking::getBookingHistoryInstance()->setBid($ord[0]['id'])->store('MB', "({$user->name}) " . VikBooking::getLogBookingModification($ord[0]));
					//
					$app->redirect("index.php?option=com_vikbooking&task=editbusy".($pvcm == 1 ? '&vcm=1' : '').($pfrominv == 1 ? '&frominv=1' : '')."&cid[]=".$ord[0]['id'].($pgoto == 'overv' ? "&goto=overv" : ""));
					exit;
				}
			}
			//
			$first = VikBooking::getDateTimestamp($pcheckindate, $pcheckinh, $pcheckinm);
			$second = VikBooking::getDateTimestamp($pcheckoutdate, $pcheckouth, $pcheckoutm);
			if ($second > $first) {
				$secdiff = $second - $first;
				$daysdiff = $secdiff / 86400;
				if (is_int($daysdiff)) {
					if ($daysdiff < 1) {
						$daysdiff = 1;
					}
				} else {
					if ($daysdiff < 1) {
						$daysdiff = 1;
					} else {
						$sum = floor($daysdiff) * 86400;
						$newdiff = $secdiff - $sum;
						$maxhmore = VikBooking::getHoursMoreRb() * 3600;
						if ($maxhmore >= $newdiff) {
							$daysdiff = floor($daysdiff);
						} else {
							$daysdiff = ceil($daysdiff);
						}
					}
				}
				$groupdays = VikBooking::getGroupDays($first, $second, $daysdiff);
				$opertwounits = true;
				$units_counter = array();
				$prm_room_oid = VikRequest::getInt('rm_room_oid', '', 'request');
				foreach ($ordersrooms as $ind => $or) {
					if (!isset($units_counter[$or['idroom']])) {
						$units_counter[$or['idroom']] = -1;
					}
					if ($prm_room_oid != $or['id']) {
						$units_counter[$or['idroom']]++;
					}
				}

				/**
				 * We need to make sure the sub-units of the rooms involved are not being overbooked.
				 * In this case, we simply raise an error message by not stopping the process.
				 * 
				 * @since 	1.3.0
				 */
				$subunits_involved_bids = array();
				//

				foreach ($ordersrooms as $ind => $or) {
					$num = $ind + 1;
					$check = "SELECT `b`.`id`,`b`.`checkin`,`b`.`realback`,`ob`.`idorder` FROM `#__vikbooking_busy` AS `b`,`#__vikbooking_ordersbusy` AS `ob` WHERE `b`.`idroom`=" . $or['idroom'] . " AND `b`.`realback`>=" . $first . " AND `b`.`id`=`ob`.`idbusy` AND `ob`.`idorder`!=" . $ord[0]['id'] . ";";
					$dbo->setQuery($check);
					$dbo->execute();
					if ($dbo->getNumRows() > 0) {
						$busy = $dbo->loadAssocList();
						foreach ($groupdays as $gday) {
							$bfound = 0;
							foreach ($busy as $bu) {
								if ($gday >= $bu['checkin'] && $gday <= $bu['realback']) {
									$bfound++;
									// keep track of the IDs involved to avoid overbooking for the sub-units
									if (!empty($or['roomindex'])) {
										if (!isset($subunits_involved_bids[$bu['idorder']])) {
											$subunits_involved_bids[$bu['idorder']] = array();
										}
										array_push($subunits_involved_bids[$bu['idorder']], array(
											'idroom' 	=> $or['idroom'],
											'roomindex' => $or['roomindex'],
										));
									}
									//
								}
							}
							if ($bfound >= ($or['units'] - $units_counter[$or['idroom']]) || ($ord[0]['status'] == 'confirmed' && !VikBooking::roomNotLocked($or['idroom'], $or['units'], $first, $second))) {
								$opertwounits = false;
								break 2;
							}
						}
					}
				}

				/**
				 * Make sure no sub-units are overbooked even though the main room is available.
				 * 
				 * @since 	1.3.0
				 */
				if ($opertwounits === true && count($subunits_involved_bids)) {
					$subunits_involved_bids = array_unique($subunits_involved_bids);
					// grab all the information about the bids involved and the related rooms/indexes
					$q = "SELECT `or`.`idorder`, `or`.`idroom`, `or`.`roomindex`, `o`.`checkin`, `o`.`checkout`, `r`.`name`, `r`.`params` 
						FROM `#__vikbooking_ordersrooms` AS `or` LEFT JOIN `#__vikbooking_orders` AS `o` ON `or`.`idorder`=`o`.`id` 
						LEFT JOIN `#__vikbooking_rooms` AS `r` ON `or`.`idroom`=`r`.`id` 
						WHERE `or`.`idorder` IN (" . implode(', ', array_keys($subunits_involved_bids)) . ");";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($dbo->getNumRows()) {
						$involved_data = $dbo->loadAssocList();
						foreach ($involved_data as $invb) {
							if (empty($invb['roomindex'])) {
								continue;
							}
							foreach ($subunits_involved_bids[$invb['idorder']] as $bookedindex) {
								if ($bookedindex['idroom'] == $invb['idroom'] && $bookedindex['roomindex'] == $invb['roomindex']) {
									// this same sub-unit is occupied by this booking ID: raise an error message to inform the administrator
									$subunit_name = $invb['roomindex'];
									$room_params = json_decode($invb['params'], true);
									if (is_array($room_params) && isset($room_params['features']) && @count($room_params['features'])) {
										foreach ($room_params['features'] as $rind => $rfeatures) {
											if ($rind == $invb['roomindex']) {
												foreach ($rfeatures as $fname => $fval) {
													if (strlen($fval)) {
														$subunit_name = '#' . $rind . ' - ' . JText::translate($fname) . ': ' . $fval;
														break;
													}
												}
											}
										}
									}
									$adjust_link = '<br/><a class="btn btn-danger" target="_blank" href="index.php?option=com_vikbooking&task=editorder&cid[]=' . $invb['idorder'] . '">' . JText::translate('VBOSUBUNITOVERBOOKEDGOTO') . '</a>';
									VikError::raiseWarning('', JText::sprintf('VBOSUBUNITOVERBOOKEDERR', $subunit_name, $invb['name'], date($df, $invb['checkin']), date($df, $invb['checkout']), $invb['idorder']) . $adjust_link);
								}
							}
						}
					}
				}
				//

				$forcebooking = VikRequest::getInt('forcebooking', 0, 'request');
				if ($opertwounits === true || $forcebooking) {
					//update dates, customer information, amount paid and busy records before checking the rates
					$realback = VikBooking::getHoursRoomAvail() * 3600;
					$realback += $second;
					$newtotalpaid = strlen($ptotpaid) > 0 ? floatval($ptotpaid) : "";
					$newrefund = strlen($prefund) > 0 ? floatval($prefund) : null;
					$roomsnum = $ord[0]['roomsnum'];
					//Vik Booking 1.10 - Add Room to existing booking
					$room_added = false;
					$padd_room_id = VikRequest::getInt('add_room_id', '', 'request');
					$padd_room_adults = VikRequest::getInt('add_room_adults', 2, 'request');
					$padd_room_children = VikRequest::getInt('add_room_children', 0, 'request');
					$padd_room_fname = VikRequest::getString('add_room_fname', '', 'request');
					$padd_room_lname = VikRequest::getString('add_room_lname', '', 'request');
					$padd_room_price = VikRequest::getFloat('add_room_price', 0, 'request');
					$paliq_add_room = VikRequest::getInt('aliq_add_room', 0, 'request');
					if ($padd_room_id > 0 && ($padd_room_adults + $padd_room_children) > 0) {
						//no need to re-validate the availability for this new room, as it was made via JS in the View.
						//increase the rooms number for later update, and insert the new room record
						$roomsnum++;
						$q = "INSERT INTO `#__vikbooking_ordersrooms` (`idorder`,`idroom`,`adults`,`children`,`t_first_name`,`t_last_name`,`cust_cost`,`cust_idiva`) VALUES(".$ord[0]['id'].", ".$padd_room_id.", ".$padd_room_adults.", ".$padd_room_children.", ".$dbo->quote($padd_room_fname).", ".$dbo->quote($padd_room_lname).", ".($padd_room_price > 0 ? $dbo->quote($padd_room_price) : 'NULL').", ".($padd_room_price > 0 && !empty($paliq_add_room) ? $dbo->quote($paliq_add_room) : 'NULL').");";
						$dbo->setQuery($q);
						$dbo->execute();
						$room_added = true;
					}
					//Vik Booking 1.10 - Remove Room from existing booking
					$room_removed = false;
					if ($prm_room_oid > 0 && $roomsnum > 1) {
						//check if the requested room record exists for removal
						$q = "SELECT * FROM `#__vikbooking_ordersrooms` WHERE `id`=".$prm_room_oid." AND `idorder`=".$ord[0]['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() == 1) {
							$room_before_rm = $dbo->loadAssoc();
							//decrease the rooms number for later update, and remove the requested room record
							$roomsnum--;
							$q = "DELETE FROM `#__vikbooking_ordersrooms` WHERE `id`=".$prm_room_oid." AND `idorder`=".$ord[0]['id']." LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
							$room_removed = $room_before_rm['idroom'];
						}
					}
					//
					//update booking's basic information (customer data, dates, tot paid, number of rooms, refund)
					$q = "UPDATE `#__vikbooking_orders` SET `custdata`= " .$dbo->quote($pcustdata) . ", `days`=" . (int)$daysdiff . ", `checkin`='" . $first . "', `checkout`='" . $second . "'" . (strlen($newtotalpaid) > 0 ? ", `totpaid`='" . $newtotalpaid . "'" : "") . ", `roomsnum`=" . (int)$roomsnum . ($newrefund !== null ? ', refund=' . $dbo->quote($newrefund) : '') . " WHERE `id`=" . $ord[0]['id'] . ";";
					$dbo->setQuery($q);
					$dbo->execute();
					//
					// Booking History log for new amount paid (payment update)
					if ($newtotalpaid > 0 && $newtotalpaid > (float)$ord[0]['totpaid']) {
						$extra_data = new stdClass;
						$extra_data->amount_paid = ($newtotalpaid - (float)$ord[0]['totpaid']);
						VikBooking::getBookingHistoryInstance()->setBid($ord[0]['id'])->setExtraData($extra_data)->store('PU', JText::sprintf('VBOPREVAMOUNTPAID', VikBooking::numberFormat((float)$ord[0]['totpaid'])));
					}
					// booking history log for new refund amount
					if ($newrefund !== null && $newrefund != (float)$ord[0]['refund']) {
						// update current refund value
						$ord[0]['refund'] = $newrefund;
						// store event
						VikBooking::getBookingHistoryInstance()->setBid($ord[0]['id'])->setExtraData(null)->store('RU', JText::sprintf('VBO_NEWREFUND_AMOUNT', VikBooking::numberFormat($ord[0]['refund']), VikBooking::numberFormat($newrefund)));
					}
					//
					if ($ord[0]['status'] == 'confirmed') {
						$q = "SELECT `b`.`id`,`b`.`idroom` FROM `#__vikbooking_busy` AS `b`,`#__vikbooking_ordersbusy` AS `ob` WHERE `b`.`id`=`ob`.`idbusy` AND `ob`.`idorder`=".$ord[0]['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
						$allbusy = $dbo->loadAssocList();
						foreach ($allbusy as $bb) {
							$q = "UPDATE `#__vikbooking_busy` SET `checkin`='".$first."', `checkout`='".$second."', `realback`='".$realback."' WHERE `id`='".$bb['id']."';";
							$dbo->setQuery($q);
							$dbo->execute();
						}
						// Vik Booking 1.10 - Add Room to existing (Confirmed) booking
						if ($room_added === true) {
							//add busy record for the new room unit
							$q = "INSERT INTO `#__vikbooking_busy` (`idroom`,`checkin`,`checkout`,`realback`) VALUES(".$padd_room_id.", ".$dbo->quote($first).", ".$dbo->quote($second).", ".$dbo->quote($realback).");";
							$dbo->setQuery($q);
							$dbo->execute();
							$newbusyid = $dbo->insertid();
							$q = "INSERT INTO `#__vikbooking_ordersbusy` (`idorder`,`idbusy`) VALUES(".$ord[0]['id'].", ".(int)$newbusyid.");";
							$dbo->setQuery($q);
							$dbo->execute();
						}
						// Vik Booking 1.10 - Remove Room from existing (Confirmed) booking
						if ($room_removed !== false) {
							//remove busy record for the removed room
							foreach ($allbusy as $bb) {
								if ($bb['idroom'] == $room_removed) {
									//remove the first room with this ID that was booked
									$q = "DELETE FROM `#__vikbooking_busy` WHERE `id`=".$bb['id']." AND `idroom`=".$room_removed.";";
									$dbo->setQuery($q);
									$dbo->execute();
									$q = "DELETE FROM `#__vikbooking_ordersbusy` WHERE `idorder`=".$ord[0]['id']." AND `idbusy`=".$bb['id'].";";
									$dbo->setQuery($q);
									$dbo->execute();
									break;
								}
							}
						}
						
						if ($room_added === true || $room_removed !== false) {
							// unset any previously booked room due to calendar sharing
							VikBooking::cleanSharedCalendarsBusy($ord[0]['id']);
							// check if some of the rooms booked have shared calendars
							VikBooking::updateSharedCalendars($ord[0]['id'], array(), $first, $second);
							//
						}
						
						if ($ord[0]['checkin'] != $first || $ord[0]['checkout'] != $second || $room_added === true || $room_removed !== false) {
							//Invoke Channel Manager
							$vcm_autosync = VikBooking::vcmAutoUpdate();
							if ($vcm_autosync > 0) {
								$vcm_obj = VikBooking::getVcmInvoker();
								$vcm_obj->setOids(array($ord[0]['id']))->setSyncType('modify')->setOriginalBooking($ord[0]);
								$sync_result = $vcm_obj->doSync();
								if ($sync_result === false) {
									$vcm_err = $vcm_obj->getError();
									VikError::raiseWarning('', JText::translate('VBCHANNELMANAGERRESULTKO').' <a href="index.php?option=com_vikchannelmanager" target="_blank">'.JText::translate('VBCHANNELMANAGEROPEN').'</a> '.(strlen($vcm_err) > 0 ? '('.$vcm_err.')' : ''));
								}
							} elseif (file_exists(VCM_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "synch.vikbooking.php")) {
								VikError::raiseNotice('', JText::translate('VBCHANNELMANAGERINVOKEASK').' <form action="index.php?option=com_vikbooking" method="post"><input type="hidden" name="option" value="com_vikbooking"/><input type="hidden" name="task" value="invoke_vcm"/><input type="hidden" name="stype" value="modify"/><input type="hidden" name="cid[]" value="'.$ord[0]['id'].'"/><input type="hidden" name="origb" value="'.urlencode(json_encode($ord[0])).'"/><input type="hidden" name="returl" value="'.urlencode("index.php?option=com_vikbooking&task=editbusy".($pvcm == 1 ? '&vcm=1' : '')."&cid[]=".$ord[0]['id']).'"/><button type="submit" class="btn btn-primary">'.JText::translate('VBCHANNELMANAGERSENDRQ').'</button></form>');
							}
							//
						}
					}
					$upd_esit = JText::translate('RESUPDATED');
					//
					$isdue = 0;
					$tot_taxes = 0;
					$tot_city_taxes = 0;
					$tot_fees = 0;
					$doup = true;
					$tars = array();
					$cust_costs = array();
					$rooms_costs_map = array();
					$arrpeople = array();
					foreach ($ordersrooms as $kor => $or) {
						//Vik Booking 1.10 - Remove from existing booking
						if ($room_removed !== false) {
							if ($or['id'] == $prm_room_oid) {
								//do not consider this room for the calculation of the new total amount
								//we can unset this array for later use, because the channel manager has already been invoked.
								unset($ordersrooms[$kor]);
								continue;
							}
						}
						//
						$num = $kor + 1;
						$padults = VikRequest::getString('adults'.$num, '', 'request');
						$pchildren = VikRequest::getString('children'.$num, '', 'request');
						if (strlen($padults) || strlen($pchildren)) {
							$arrpeople[$num]['adults'] = (int)$padults;
							$arrpeople[$num]['children'] = (int)$pchildren;
						}
						$ppriceid = VikRequest::getString('priceid'.$num, '', 'request');
						$polderpriceid = VikRequest::getString('olderpriceid'.$num, '', 'request');
						$ppkgid = VikRequest::getString('pkgid'.$num, '', 'request');
						$pcust_cost = VikRequest::getString('cust_cost'.$num, '', 'request');
						$paliq = VikRequest::getString('aliq'.$num, '', 'request');
						if ($is_package === true && !empty($ppkgid)) {
							$pkg_cost = $or['cust_cost'];
							$pkg_idiva = $or['cust_idiva'];
							$pkg_info = VikBooking::getPackage($ppkgid);
							if (is_array($pkg_info) && count($pkg_info) > 0) {
								$use_adults = array_key_exists($num, $arrpeople) && array_key_exists('adults', $arrpeople[$num]) ? $arrpeople[$num]['adults'] : $or['adults'];
								$pkg_cost = $pkg_info['pernight_total'] == 1 ? ($pkg_info['cost'] * $daysdiff) : $pkg_info['cost'];
								$pkg_cost = $pkg_info['perperson'] == 1 ? ($pkg_cost * ($use_adults > 0 ? $use_adults : 1)) : $pkg_cost;
								$pkg_cost = VikBooking::sayPackagePlusIva($pkg_cost, $pkg_info['idiva']);
							}
							$cust_costs[$num] = array('pkgid' => $ppkgid, 'cust_cost' => $pkg_cost, 'aliq' => $pkg_idiva);
							$isdue += $pkg_cost;
							$cost_minus_tax = VikBooking::sayPackageMinusIva($pkg_cost, $pkg_idiva);
							$tot_taxes += ($pkg_cost - $cost_minus_tax);
							continue;
						}
						if (empty($ppriceid) && !empty($pcust_cost) && floatval($pcust_cost) > 0) {
							$cust_costs[$num] = array('cust_cost' => $pcust_cost, 'aliq' => $paliq);
							$cost_after_tax = VikBooking::sayPackagePlusIva((float)$pcust_cost, (int)$paliq);
							$isdue += $cost_after_tax;
							$cost_minus_tax = VikBooking::sayPackageMinusIva((float)$pcust_cost, (int)$paliq);
							$tot_taxes += ($cost_after_tax - $cost_minus_tax);
							continue;
						}
						$q = "SELECT * FROM `#__vikbooking_dispcost` WHERE `idroom`='".$or['idroom']."' AND `days`='".$daysdiff."' AND `idprice`='".$ppriceid."';";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() == 1) {
							$tar = $dbo->loadAssocList();
							/**
							 * The current price may be different from the price paid at the time of booking.
							 * Check whether it has been asked to keep the old price of the time of booking.
							 * 
							 * @since 	1.3.0
							 */
							$old_price_used = false;
							if (!empty($polderpriceid)) {
								$older_info = explode(':', $polderpriceid);
								if ((int)$older_info[0] == (int)$ppriceid) {
									$old_price = isset($older_info[1]) ? (float)$older_info[1] : 0;
									if ($old_price > 0) {
										// we override the 'cost' property of the tar array by taking the previous cost
										$old_price_used = true;
										$tar[0]['cost'] = $old_price;
									}
								}
							}
							//
							if (!$old_price_used) {
								$tar = VikBooking::applySeasonsRoom($tar, $ord[0]['checkin'], $ord[0]['checkout']);
							}
							//different usage
							if (!$old_price_used && $or['fromadult'] <= $or['adults'] && $or['toadult'] >= $or['adults']) {
								$diffusageprice = VikBooking::loadAdultsDiff($or['idroom'], $or['adults']);
								//Occupancy Override
								$occ_ovr = VikBooking::occupancyOverrideExists($tar, $or['adults']);
								$diffusageprice = $occ_ovr !== false ? $occ_ovr : $diffusageprice;
								//
								if (is_array($diffusageprice)) {
									//set a charge or discount to the price(s) for the different usage of the room
									foreach ($tar as $kpr => $vpr) {
										if ($diffusageprice['chdisc'] == 1) {
											//charge
											if ($diffusageprice['valpcent'] == 1) {
												//fixed value
												$tar[$kpr]['diffusagecostpernight'] = $diffusageprice['pernight'] == 1 ? 1 : 0;
												$aduseval = $diffusageprice['pernight'] == 1 ? $diffusageprice['value'] * $tar[$kpr]['days'] : $diffusageprice['value'];
												$tar[$kpr]['diffusagecost'] = "+".$aduseval;
												$tar[$kpr]['room_base_cost'] = $vpr['cost'];
												$tar[$kpr]['cost'] = $vpr['cost'] + $aduseval;
											} else {
												//percentage value
												$tar[$kpr]['diffusagecostpernight'] = $diffusageprice['pernight'] == 1 ? $vpr['cost'] : 0;
												$aduseval = $diffusageprice['pernight'] == 1 ? round(($vpr['cost'] * $diffusageprice['value'] / 100) * $tar[$kpr]['days'] + $vpr['cost'], 2) : round(($vpr['cost'] * (100 + $diffusageprice['value']) / 100), 2);
												$tar[$kpr]['diffusagecost'] = "+".$diffusageprice['value']."%";
												$tar[$kpr]['room_base_cost'] = $vpr['cost'];
												$tar[$kpr]['cost'] = $aduseval;
											}
										} else {
											//discount
											if ($diffusageprice['valpcent'] == 1) {
												//fixed value
												$tar[$kpr]['diffusagecostpernight'] = $diffusageprice['pernight'] == 1 ? 1 : 0;
												$aduseval = $diffusageprice['pernight'] == 1 ? $diffusageprice['value'] * $tar[$kpr]['days'] : $diffusageprice['value'];
												$tar[$kpr]['diffusagecost'] = "-".$aduseval;
												$tar[$kpr]['room_base_cost'] = $vpr['cost'];
												$tar[$kpr]['cost'] = $vpr['cost'] - $aduseval;
											} else {
												//percentage value
												$tar[$kpr]['diffusagecostpernight'] = $diffusageprice['pernight'] == 1 ? $vpr['cost'] : 0;
												$aduseval = $diffusageprice['pernight'] == 1 ? round($vpr['cost'] - ((($vpr['cost'] / $tar[$kpr]['days']) * $diffusageprice['value'] / 100) * $tar[$kpr]['days']), 2) : round(($vpr['cost'] * (100 - $diffusageprice['value']) / 100), 2);
												$tar[$kpr]['diffusagecost'] = "-".$diffusageprice['value']."%";
												$tar[$kpr]['room_base_cost'] = $vpr['cost'];
												$tar[$kpr]['cost'] = $aduseval;
											}
										}
									}
								}
							}
							//
							$cost_plus_tax = VikBooking::sayCostPlusIva($tar[0]['cost'], $tar[0]['idprice']);
							$isdue += $cost_plus_tax;
							if ($cost_plus_tax == $tar[0]['cost']) {
								$cost_minus_tax = VikBooking::sayCostMinusIva($tar[0]['cost'], $tar[0]['idprice']);
								$tot_taxes += ($tar[0]['cost'] - $cost_minus_tax);
							} else {
								$tot_taxes += ($cost_plus_tax - $tar[0]['cost']);
							}
							$tars[$num] = $tar;
							$rooms_costs_map[$num] = $tar[0]['cost'];
						} else {
							$doup = false;
							break;
						}
					}
					if ($doup === true) {
						if ($room_added === true) {
							//Vik Booking 1.10 - Add Room to existing booking may require to increase the total amount, and taxes
							$padd_room_price = VikRequest::getFloat('add_room_price', 0, 'request');
							$paliq_add_room = VikRequest::getInt('aliq_add_room', 0, 'request');
							if (!empty($padd_room_price) && floatval($padd_room_price) > 0) {
								$isdue += (float)$padd_room_price;
								$cost_minus_tax = VikBooking::sayPackageMinusIva((float)$padd_room_price, (int)$paliq_add_room);
								$tot_taxes += ((float)$padd_room_price - $cost_minus_tax);
							}
							//
						}
						$toptionals = '';
						$q = "SELECT * FROM `#__vikbooking_optionals` ORDER BY `#__vikbooking_optionals`.`ordering` ASC;";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() > 0) {
							$toptionals = $dbo->loadAssocList();
						}
						foreach ($ordersrooms as $kor => $or) {
							$num = $kor + 1;
							$pt_first_name = VikRequest::getString('t_first_name'.$num, '', 'request');
							$pt_last_name = VikRequest::getString('t_last_name'.$num, '', 'request');
							$wop = "";
							if (is_array($toptionals)) {
								foreach ($toptionals as $opt) {
									if (!empty($opt['ageintervals']) && ($or['children'] > 0 || (array_key_exists($num, $arrpeople) && array_key_exists('children', $arrpeople[$num]))) ) {
										$tmpvar = VikRequest::getVar('optid'.$num.$opt['id'], array(0));
										if (is_array($tmpvar) && count($tmpvar) > 0 && !empty($tmpvar[0])) {
											$opt['quan'] = 1;
											$optagenames = VikBooking::getOptionIntervalsAges($opt['ageintervals']);
											$optagepcent = VikBooking::getOptionIntervalsPercentage($opt['ageintervals']);
											$optageovrct = VikBooking::getOptionIntervalChildOverrides($opt, (isset($arrpeople[$num]) ? $arrpeople[$num]['adults'] : 0), (isset($arrpeople[$num]) ? $arrpeople[$num]['children'] : 0));
											$optorigname = $opt['name'];
											foreach ($tmpvar as $child_num => $chvar) {
												$ageintervals_child_string = isset($optageovrct['ageintervals_child' . ($child_num + 1)]) ? $optageovrct['ageintervals_child' . ($child_num + 1)] : $opt['ageintervals'];
												$optagecosts = VikBooking::getOptionIntervalsCosts($ageintervals_child_string);
												$optorigcost = $optagecosts[($chvar - 1)];
												if (array_key_exists(($chvar - 1), $optagepcent) && $optagepcent[($chvar - 1)] == 1) {
													//percentage value of the adults tariff
													if ($is_package !== true && array_key_exists($num, $tars)) {
														//type of price
														$optorigcost = $tars[$num][0]['cost'] * $optagecosts[($chvar - 1)] / 100;
													} elseif ($is_package === true && array_key_exists($num, $cust_costs)) {
														//package
														$optorigcost = $cust_costs[$num]['cust_cost'] * $optagecosts[($chvar - 1)] / 100;
													} elseif (array_key_exists($num, $cust_costs) && array_key_exists('cust_cost', $cust_costs[$num])) {
														//custom rate + custom tax rate
														$optorigcost = $cust_costs[$num]['cust_cost'] * $optagecosts[($chvar - 1)] / 100;
													}
												} elseif (array_key_exists(($chvar - 1), $optagepcent) && $optagepcent[($chvar - 1)] == 2) {
													//VBO 1.10 - percentage value of room base cost
													if ($is_package !== true && array_key_exists($num, $tars)) {
														//type of price
														$usecost = isset($tars[$num][0]['room_base_cost']) ? $tars[$num][0]['room_base_cost'] : $tars[$num][0]['cost'];
														$optorigcost = $usecost * $optagecosts[($chvar - 1)] / 100;
													} elseif ($is_package === true && array_key_exists($num, $cust_costs)) {
														//package
														$optorigcost = $cust_costs[$num]['cust_cost'] * $optagecosts[($chvar - 1)] / 100;
													} elseif (array_key_exists($num, $cust_costs) && array_key_exists('cust_cost', $cust_costs[$num])) {
														//custom rate + custom tax rate
														$optorigcost = $cust_costs[$num]['cust_cost'] * $optagecosts[($chvar - 1)] / 100;
													}
												}
												$opt['cost'] = $optorigcost;
												$opt['name'] = $optorigname.' ('.$optagenames[($chvar - 1)].')';
												$opt['chageintv'] = $chvar;
												$wop.=$opt['id'].":".$opt['quan']."-".$chvar.";";
												$realcost = (intval($opt['perday']) == 1 ? ($opt['cost'] * $daysdiff * $opt['quan']) : ($opt['cost'] * $opt['quan']));
												if (!empty($opt['maxprice']) && $opt['maxprice'] > 0 && $realcost > $opt['maxprice']) {
													$realcost = $opt['maxprice'];
												}
												$tmpopr = VikBooking::sayOptionalsPlusIva($realcost, $opt['idiva']);
												if ($opt['is_citytax'] == 1) {
													$tot_city_taxes += $tmpopr;
												} elseif ($opt['is_fee'] == 1) {
													$tot_fees += $tmpopr;
												}
												// VBO 1.11 - always calculate the amount of tax no matter if this is already a tax or a fee
												if ($tmpopr == $realcost) {
													$opt_minus_iva = VikBooking::sayOptionalsMinusIva($realcost, $opt['idiva']);
													$tot_taxes += ($realcost - $opt_minus_iva);
												} else {
													$tot_taxes += ($tmpopr - $realcost);
												}
												//
												$isdue += $tmpopr;
											}
										}
									} else {
										$tmpvar = VikRequest::getString('optid'.$num.$opt['id'], '', 'request');
										//options forced per child fix, no age intervals, like children tourist taxes
										$forcedquan = 1;
										$forceperday = false;
										$forceperchild = false;
										if (intval($opt['forcesel']) == 1 && strlen($opt['forceval']) > 0 && strlen($tmpvar) > 0) {
											$forceparts = explode("-", $opt['forceval']);
											$forcedquan = intval($forceparts[0]);
											$forceperday = intval($forceparts[1]) == 1 ? true : false;
											$forceperchild = intval($forceparts[2]) == 1 ? true : false;
											$tmpvar = $forcedquan;
											$tmpvar = $forceperchild === true && array_key_exists($num, $arrpeople) && array_key_exists('children', $arrpeople[$num]) ? ($tmpvar * $arrpeople[$num]['children']) : $tmpvar;
										}
										//
										if (!empty($tmpvar)) {
											$wop .= $opt['id'].":".$tmpvar.";";
											// VBO 1.11 - options percentage cost of the room total fee
											if ($is_package !== true && array_key_exists($num, $tars)) {
												//type of price
												$deftar_basecosts = $tars[$num][0]['cost'];
											} elseif ($is_package === true && array_key_exists($num, $cust_costs)) {
												//package
												$deftar_basecosts = $cust_costs[$num]['cust_cost'];
											} elseif (array_key_exists($num, $cust_costs) && array_key_exists('cust_cost', $cust_costs[$num])) {
												//custom rate + custom tax rate
												$deftar_basecosts = $cust_costs[$num]['cust_cost'];
											}
											$opt['cost'] = (int)$opt['pcentroom'] ? ($deftar_basecosts * $opt['cost'] / 100) : $opt['cost'];
											//
											$realcost = (intval($opt['perday']) == 1 ? ($opt['cost'] * $daysdiff * $tmpvar) : ($opt['cost'] * $tmpvar));
											if (!empty($opt['maxprice']) && $opt['maxprice'] > 0 && $realcost > $opt['maxprice']) {
												$realcost = $opt['maxprice'];
												if (intval($opt['hmany']) == 1 && intval($tmpvar) > 1) {
													$realcost = $opt['maxprice'] * $tmpvar;
												}
											}
											if ($opt['perperson'] == 1) {
												$num_adults = array_key_exists($num, $arrpeople) && array_key_exists('adults', $arrpeople[$num]) ? $arrpeople[$num]['adults'] : $num_adults;
												$realcost = $realcost * $num_adults;
											}
											$tmpopr = VikBooking::sayOptionalsPlusIva($realcost, $opt['idiva']);
											if ($opt['is_citytax'] == 1) {
												$tot_city_taxes += $tmpopr;
											} elseif ($opt['is_fee'] == 1) {
												$tot_fees += $tmpopr;
											}
											// VBO 1.11 - always calculate the amount of tax no matter if this is already a tax or a fee
											if ($tmpopr == $realcost) {
												$opt_minus_iva = VikBooking::sayOptionalsMinusIva($realcost, $opt['idiva']);
												$tot_taxes += ($realcost - $opt_minus_iva);
											} else {
												$tot_taxes += ($tmpopr - $realcost);
											}
											//
											$isdue += $tmpopr;
										}
									}
								}
							}
							$upd_fields = array();
							if ($is_package !== true && array_key_exists($num, $tars)) {
								//type of price
								$upd_fields[] = "`idtar`='".$tars[$num][0]['id']."'";
								$upd_fields[] = "`cust_cost`=NULL";
								$upd_fields[] = "`cust_idiva`=NULL";
								$upd_fields[] = "`room_cost`=".(array_key_exists($num, $rooms_costs_map) ? $dbo->quote($rooms_costs_map[$num]) : "NULL");
							} elseif ($is_package === true && array_key_exists($num, $cust_costs)) {
								//packages do not update name or cost, just set again the same package ID to avoid risks of empty upd_fields to update
								$upd_fields[] = "`idtar`=NULL";
								$upd_fields[] = "`pkg_id`='".$cust_costs[$num]['pkgid']."'";
								$upd_fields[] = "`cust_cost`='".$cust_costs[$num]['cust_cost']."'";
								$upd_fields[] = "`cust_idiva`='".$cust_costs[$num]['aliq']."'";
								$upd_fields[] = "`room_cost`=NULL";
							} elseif (array_key_exists($num, $cust_costs) && array_key_exists('cust_cost', $cust_costs[$num])) {
								//custom rate + custom tax rate
								$upd_fields[] = "`idtar`=NULL";
								$upd_fields[] = "`cust_cost`='".$cust_costs[$num]['cust_cost']."'";
								$upd_fields[] = "`cust_idiva`='".$cust_costs[$num]['aliq']."'";
								$upd_fields[] = "`room_cost`=NULL";
							}
							if (is_array($toptionals)) {
								$upd_fields[] = "`optionals`='".$wop."'";
							}
							if (!empty($pt_first_name) || !empty($pt_last_name)) {
								$upd_fields[] = "`t_first_name`=".$dbo->quote($pt_first_name);
								$upd_fields[] = "`t_last_name`=".$dbo->quote($pt_last_name);
							}
							if (array_key_exists($num, $arrpeople) && array_key_exists('adults', $arrpeople[$num])) {
								$upd_fields[] = "`adults`=".intval($arrpeople[$num]['adults']);
								$upd_fields[] = "`children`=".intval($arrpeople[$num]['children']);
							}
							//calculate the extra costs and increase taxes + isdue
							$extracosts_arr = array();
							if (count($pextracn) > 0 && count($pextracn[$num]) > 0) {
								foreach ($pextracn[$num] as $eck => $ecn) {
									if (strlen($ecn) > 0 && array_key_exists($eck, $pextracc[$num]) && is_numeric($pextracc[$num][$eck])) {
										$ecidtax = array_key_exists($eck, $pextractx[$num]) && intval($pextractx[$num][$eck]) > 0 ? (int)$pextractx[$num][$eck] : '';
										$extracosts_arr[] = array('name' => $ecn, 'cost' => (float)$pextracc[$num][$eck], 'idtax' => $ecidtax);
										$ecplustax = !empty($ecidtax) ? VikBooking::sayOptionalsPlusIva((float)$pextracc[$num][$eck], $ecidtax) : (float)$pextracc[$num][$eck];
										$ecminustax = !empty($ecidtax) ? VikBooking::sayOptionalsMinusIva((float)$pextracc[$num][$eck], $ecidtax) : (float)$pextracc[$num][$eck];
										$ectottax = (float)$pextracc[$num][$eck] - $ecminustax;
										$isdue += $ecplustax;
										$tot_taxes += $ectottax;
									}
								}
							}
							if (count($extracosts_arr) > 0) {
								$upd_fields[] = "`extracosts`=".$dbo->quote(json_encode($extracosts_arr));
							} else {
								$upd_fields[] = "`extracosts`=NULL";
							}
							//end extra costs
							if (count($upd_fields) > 0) {
								$q = "UPDATE `#__vikbooking_ordersrooms` SET ".implode(', ', $upd_fields)." WHERE `idorder`=".$ord[0]['id']." AND `idroom`='".$or['idroom']."' AND `id`='".$or['id']."';";
								$dbo->setQuery($q);
								$dbo->execute();
							}
						}
						// make sure to re-apply the discount with the coupon code
						if (strlen($ord[0]['coupon']) > 0) {
							$expcoupon = explode(";", $ord[0]['coupon']);
							$isdue -= $expcoupon[1];
						}
						// make sure to apply any previously refunded amount
						if ($ord[0]['refund'] > 0) {
							$isdue -= $ord[0]['refund'];
						}
						//
						$q = "UPDATE `#__vikbooking_orders` SET `total`='".$isdue."', `tot_taxes`='".$tot_taxes."', `tot_city_taxes`='".$tot_city_taxes."', `tot_fees`='".$tot_fees."' WHERE `id`=".$ord[0]['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
						$upd_esit = JText::translate('VBORESRATESUPDATED');
						//Customer Booking
						if ($ord[0]['status'] == 'confirmed') {
							$q = "SELECT `idcustomer` FROM `#__vikbooking_customers_orders` WHERE `idorder`=".$ord[0]['id'].";";
							$dbo->setQuery($q);
							$dbo->execute();
							if ($dbo->getNumRows() > 0) {
								$customer_id = $dbo->loadResult();
								$cpin = VikBooking::getCPinIstance();
								$cpin->is_admin = true;
								$cpin->updateBookingCommissions($ord[0]['id'], $customer_id);
							}
						}
						//
					}
					//Booking History
					$history_descr = "({$user->name}) " . VikBooking::getLogBookingModification($ord[0]);
					if (!$opertwounits && $forcebooking) {
						$history_descr .= "\n" . JText::translate('VBO_FORCED_BOOKDATES');
					}
					VikBooking::getBookingHistoryInstance()->setBid($ord[0]['id'])->store('MB', $history_descr);
					//
					$mainframe->enqueueMessage($upd_esit);
				} else {
					VikError::raiseWarning('', JText::translate('VBROOMNOTRIT')." ".date($df.' H:i', $first)." ".JText::translate('VBROOMNOTCONSTO')." ".date($df.' H:i', $second));
					$allow_force = 1;
					$mainframe->enqueueMessage(JText::translate('VBO_BOOKING_SHOULDFORCE'), 'notice');
				}
			} else {
				VikError::raiseWarning('', JText::translate('ERRPREV'));
			}
			if ($callback == 'geninvoices') {
				$mainframe->redirect("index.php?option=com_vikbooking&task=orders&cid[]=".$ord[0]['id']."&confirmgen=1");
			} else {
				$mainframe->redirect("index.php?option=com_vikbooking&task=editbusy".($pvcm == 1 ? '&vcm=1' : '').(isset($allow_force) ? '&canforce=1' : '').($pfrominv == 1 ? '&frominv=1' : '')."&cid[]=".$ord[0]['id'].($pgoto == 'overv' ? "&goto=overv" : ""));
			}
		} else {
			$mainframe->redirect("index.php?option=com_vikbooking&task=rooms");
		}
	}

	public function removebusy() {
		$dbo = JFactory::getDBO();
		$prev_conf_ids = array();
		$pidorder = VikRequest::getString('idorder', '', 'request');
		$pgoto = VikRequest::getString('goto', '', 'request');
		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`=".$dbo->quote($pidorder).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$rows = $dbo->loadAssocList();
			if ($rows[0]['status'] != 'cancelled') {
				$q = "UPDATE `#__vikbooking_orders` SET `status`='cancelled' WHERE `id`=".(int)$rows[0]['id'].";";
				$dbo->setQuery($q);
				$dbo->execute();
				$q = "DELETE FROM `#__vikbooking_tmplock` WHERE `idorder`=" . intval($rows[0]['id']) . ";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($rows[0]['status'] == 'confirmed') {
					$prev_conf_ids[] = $rows[0]['id'];
				}
				//Booking History
				VikBooking::getBookingHistoryInstance()->setBid($rows[0]['id'])->store('CB');
				//
			}
			$q = "SELECT * FROM `#__vikbooking_ordersbusy` WHERE `idorder`=".(int)$rows[0]['id'].";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$ordbusy = $dbo->loadAssocList();
				foreach ($ordbusy as $ob) {
					$q = "DELETE FROM `#__vikbooking_busy` WHERE `id`='".$ob['idbusy']."';";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
			$q = "DELETE FROM `#__vikbooking_ordersbusy` WHERE `idorder`=".(int)$rows[0]['id'].";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($rows[0]['status'] == 'cancelled') {
				$q = "DELETE FROM `#__vikbooking_customers_orders` WHERE `idorder`=" . intval($rows[0]['id']) . ";";
				$dbo->setQuery($q);
				$dbo->execute();
				$q = "DELETE FROM `#__vikbooking_ordersrooms` WHERE `idorder`=".(int)$rows[0]['id'].";";
				$dbo->setQuery($q);
				$dbo->execute();
				$q = "DELETE FROM `#__vikbooking_orderhistory` WHERE `idorder`=".(int)$rows[0]['id'].";";
				$dbo->setQuery($q);
				$dbo->execute();
				$q = "DELETE FROM `#__vikbooking_orders` WHERE `id`=".(int)$rows[0]['id'].";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::translate('VBMESSDELBUSY'));
		}
		if (count($prev_conf_ids) > 0) {
			$prev_conf_ids_str = '';
			foreach ($prev_conf_ids as $prev_id) {
				$prev_conf_ids_str .= '&cid[]='.$prev_id;
			}
			//Invoke Channel Manager
			$vcm_autosync = VikBooking::vcmAutoUpdate();
			if ($vcm_autosync > 0) {
				$vcm_obj = VikBooking::getVcmInvoker();
				$vcm_obj->setOids($prev_conf_ids)->setSyncType('cancel');
				$sync_result = $vcm_obj->doSync();
				if ($sync_result === false) {
					$vcm_err = $vcm_obj->getError();
					VikError::raiseWarning('', JText::translate('VBCHANNELMANAGERRESULTKO').' <a href="index.php?option=com_vikchannelmanager" target="_blank">'.JText::translate('VBCHANNELMANAGEROPEN').'</a> '.(strlen($vcm_err) > 0 ? '('.$vcm_err.')' : ''));
				}
			} elseif (file_exists(VCM_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "synch.vikbooking.php")) {
				$vcm_sync_url = 'index.php?option=com_vikbooking&task=invoke_vcm&stype=cancel'.$prev_conf_ids_str.'&returl='.urlencode('index.php?option=com_vikbooking&task=orders');
				VikError::raiseNotice('', JText::translate('VBCHANNELMANAGERINVOKEASK').' <button type="button" class="btn btn-primary" onclick="document.location.href=\''.$vcm_sync_url.'\';">'.JText::translate('VBCHANNELMANAGERSENDRQ').'</button>');
			}
			//
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=".($pgoto == 'overv' ? 'overv' : 'orders'));
	}

	public function unlockrecords() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikbooking_tmplock` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking");
	}

	public function sortoption() {
		$sortid = VikRequest::getVar('cid', array(0));
		$pmode = VikRequest::getString('mode', '', 'request');
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		if (!empty($pmode)) {
			$q = "SELECT `id`,`ordering` FROM `#__vikbooking_optionals` ORDER BY `#__vikbooking_optionals`.`ordering` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$totr = $dbo->getNumRows();
			if ($totr > 1) {
				$data = $dbo->loadAssocList();
				if ($pmode == "up") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid[0]) {
							$y = $v['ordering'];
						}
					}
					if ($y && $y > 1) {
						$vik = $y - 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering']) == intval($vik)) {
								$found = true;
								$q = "UPDATE `#__vikbooking_optionals` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikbooking_optionals` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikbooking_optionals` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				} elseif ($pmode == "down") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid[0]) {
							$y = $v['ordering'];
						}
					}
					if ($y) {
						$vik = $y + 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering']) == intval($vik)) {
								$found = true;
								$q = "UPDATE `#__vikbooking_optionals` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikbooking_optionals` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikbooking_optionals` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				}
			}
			$mainframe->redirect("index.php?option=com_vikbooking&task=optionals");
		} else {
			$mainframe->redirect("index.php?option=com_vikbooking");
		}
	}

	public function sortpayment() {
		$cid = VikRequest::getVar('cid', array(0));
		$sortid = $cid[0];
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$pmode = VikRequest::getString('mode', '', 'request');
		if (!empty($pmode) && !empty($sortid)) {
			$q = "SELECT `id`,`ordering` FROM `#__vikbooking_gpayments` ORDER BY `#__vikbooking_gpayments`.`ordering` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$totr=$dbo->getNumRows();
			if ($totr > 1) {
				$data = $dbo->loadAssocList();
				if ($pmode == "up") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid) {
							$y = $v['ordering'];
						}
					}
					if ($y && $y > 1) {
						$vik = $y - 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering']) == intval($vik)) {
								$found = true;
								$q = "UPDATE `#__vikbooking_gpayments` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikbooking_gpayments` SET `ordering`='".$vik."' WHERE `id`='".$sortid."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikbooking_gpayments` SET `ordering`='".$vik."' WHERE `id`='".$sortid."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				} elseif ($pmode == "down") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid) {
							$y = $v['ordering'];
						}
					}
					if ($y) {
						$vik = $y + 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering']) == intval($vik)) {
								$found=true;
								$q = "UPDATE `#__vikbooking_gpayments` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikbooking_gpayments` SET `ordering`='".$vik."' WHERE `id`='".$sortid."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikbooking_gpayments` SET `ordering`='".$vik."' WHERE `id`='".$sortid."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				}
			}
			$mainframe->redirect("index.php?option=com_vikbooking&task=payments");
		} else {
			$mainframe->redirect("index.php?option=com_vikbooking");
		}
	}

	public function sortcarat() {
		$sortid = VikRequest::getVar('cid', array(0));
		$pmode = VikRequest::getString('mode', '', 'request');
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		if (!empty($pmode)) {
			$q = "SELECT `id`,`ordering` FROM `#__vikbooking_characteristics` ORDER BY `#__vikbooking_characteristics`.`ordering` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$totr = $dbo->getNumRows();
			if ($totr > 1) {
				$data = $dbo->loadAssocList();
				if ($pmode == "up") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid[0]) {
							$y = $v['ordering'];
						}
					}
					if ($y && $y > 1) {
						$vik = $y - 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering']) == intval($vik)) {
								$found = true;
								$q = "UPDATE `#__vikbooking_characteristics` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikbooking_characteristics` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikbooking_characteristics` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				} elseif ($pmode == "down") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid[0]) {
							$y = $v['ordering'];
						}
					}
					if ($y) {
						$vik = $y + 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering']) == intval($vik)) {
								$found = true;
								$q = "UPDATE `#__vikbooking_characteristics` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikbooking_characteristics` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikbooking_characteristics` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				}
			}
			$mainframe->redirect("index.php?option=com_vikbooking&task=carat");
		} else {
			$mainframe->redirect("index.php?option=com_vikbooking");
		}
	}

	public function resendordemail() {
		$this->do_resendorderemail();
	}

	public function sendcancordemail() {
		$this->do_resendorderemail(true);
	}

	private function do_resendorderemail($cancellation = false) {
		$cid = VikRequest::getVar('cid', array(0));
		$oid = (int)$cid[0];
		$dbo = JFactory::getDBO();
		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`=".(int)$oid.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$order = $dbo->loadAssocList();
			$vbo_tn = VikBooking::getTranslator();
			//check if the language in use is the same as the one used during the checkout
			if (!empty($order[0]['lang'])) {
				$lang = JFactory::getLanguage();
				if ($lang->getTag() != $order[0]['lang']) {
					/**
					 * @wponly 	constant name for lang path is different
					 */
					$lang->load('com_vikbooking', VIKBOOKING_LANG, $order[0]['lang'], true);
				}
				if ($vbo_tn->getDefaultLang() != $order[0]['lang']) {
					// force the translation to start because contents should be translated
					$vbo_tn::$force_tolang = $order[0]['lang'];
				}
			}
			//
			$q = "SELECT `or`.*,`r`.`id` AS `r_reference_id`,`r`.`name`,`r`.`units`,`r`.`fromadult`,`r`.`toadult`,`r`.`params` FROM `#__vikbooking_ordersrooms` AS `or`,`#__vikbooking_rooms` AS `r` WHERE `or`.`idorder`=" . (int)$order[0]['id'] . " AND `or`.`idroom`=`r`.`id` ORDER BY `or`.`id` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$ordersrooms = $dbo->loadAssocList();
			$vbo_tn->translateContents($ordersrooms, '#__vikbooking_rooms', array('id' => 'r_reference_id'));
			$currencyname = VikBooking::getCurrencyName();
			$realback = VikBooking::getHoursRoomAvail() * 3600;
			$realback += $order[0]['checkout'];
			$rooms = array();
			$tars = array();
			$arrpeople = array();
			$is_package = !empty($order[0]['pkg']) ? true : false;
			//send mail
			$ftitle = VikBooking::getFrontTitle();
			$nowts = time();
			$viklink = JURI::root()."index.php?option=com_vikbooking&view=booking&sid=".$order[0]['sid']."&ts=".$order[0]['ts'];
			foreach ($ordersrooms as $kor => $or) {
				$num = $kor + 1;
				$rooms[$num] = $or;
				$arrpeople[$num]['adults'] = $or['adults'];
				$arrpeople[$num]['children'] = $or['children'];
				if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
					//package or custom cost set from the back-end
					continue;
				}
				$q = "SELECT * FROM `#__vikbooking_dispcost` WHERE `id`=" . (int)$or['idtar'] . ";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$tar = $dbo->loadAssocList();
					$tar = VikBooking::applySeasonsRoom($tar, $order[0]['checkin'], $order[0]['checkout']);
					//different usage
					if ($or['fromadult'] <= $or['adults'] && $or['toadult'] >= $or['adults']) {
						$diffusageprice = VikBooking::loadAdultsDiff($or['idroom'], $or['adults']);
						//Occupancy Override
						$occ_ovr = VikBooking::occupancyOverrideExists($tar, $or['adults']);
						$diffusageprice = $occ_ovr !== false ? $occ_ovr : $diffusageprice;
						//
						if (is_array($diffusageprice)) {
							//set a charge or discount to the price(s) for the different usage of the room
							foreach ($tar as $kpr => $vpr) {
								$tar[$kpr]['diffusage'] = $or['adults'];
								if ($diffusageprice['chdisc'] == 1) {
									//charge
									if ($diffusageprice['valpcent'] == 1) {
										//fixed value
										$tar[$kpr]['diffusagecostpernight'] = $diffusageprice['pernight'] == 1 ? 1 : 0;
										$aduseval = $diffusageprice['pernight'] == 1 ? $diffusageprice['value'] * $tar[$kpr]['days'] : $diffusageprice['value'];
										$tar[$kpr]['diffusagecost'] = "+".$aduseval;
										$tar[$kpr]['room_base_cost'] = $vpr['cost'];
										$tar[$kpr]['cost'] = $vpr['cost'] + $aduseval;
									} else {
										//percentage value
										$tar[$kpr]['diffusagecostpernight'] = $diffusageprice['pernight'] == 1 ? $vpr['cost'] : 0;
										$aduseval = $diffusageprice['pernight'] == 1 ? round(($vpr['cost'] * $diffusageprice['value'] / 100) * $tar[$kpr]['days'] + $vpr['cost'], 2) : round(($vpr['cost'] * (100 + $diffusageprice['value']) / 100), 2);
										$tar[$kpr]['diffusagecost'] = "+".$diffusageprice['value']."%";
										$tar[$kpr]['room_base_cost'] = $vpr['cost'];
										$tar[$kpr]['cost'] = $aduseval;
									}
								} else {
									//discount
									if ($diffusageprice['valpcent'] == 1) {
										//fixed value
										$tar[$kpr]['diffusagecostpernight'] = $diffusageprice['pernight'] == 1 ? 1 : 0;
										$aduseval = $diffusageprice['pernight'] == 1 ? $diffusageprice['value'] * $tar[$kpr]['days'] : $diffusageprice['value'];
										$tar[$kpr]['diffusagecost'] = "-".$aduseval;
										$tar[$kpr]['room_base_cost'] = $vpr['cost'];
										$tar[$kpr]['cost'] = $vpr['cost'] - $aduseval;
									} else {
										//percentage value
										$tar[$kpr]['diffusagecostpernight'] = $diffusageprice['pernight'] == 1 ? $vpr['cost'] : 0;
										$aduseval = $diffusageprice['pernight'] == 1 ? round($vpr['cost'] - ((($vpr['cost'] / $tar[$kpr]['days']) * $diffusageprice['value'] / 100) * $tar[$kpr]['days']), 2) : round(($vpr['cost'] * (100 - $diffusageprice['value']) / 100), 2);
										$tar[$kpr]['diffusagecost'] = "-".$diffusageprice['value']."%";
										$tar[$kpr]['room_base_cost'] = $vpr['cost'];
										$tar[$kpr]['cost'] = $aduseval;
									}
								}
							}
						}
					}
					//
					$tars[$num] = $tar[0];
				} else {
					VikError::raiseWarning('', JText::translate('VBERRNOFAREFOUND'));
				}
			}
			$pcheckin = $order[0]['checkin'];
			$pcheckout = $order[0]['checkout'];
			$secdiff = $pcheckout - $pcheckin;
			$daysdiff = $secdiff / 86400;
			if (is_int($daysdiff)) {
				if ($daysdiff < 1) {
					$daysdiff = 1;
				}
			} else {
				if ($daysdiff < 1) {
					$daysdiff = 1;
				} else {
					$sum = floor($daysdiff) * 86400;
					$newdiff = $secdiff - $sum;
					$maxhmore = VikBooking::getHoursMoreRb() * 3600;
					if ($maxhmore >= $newdiff) {
						$daysdiff = floor($daysdiff);
					} else {
						$daysdiff = ceil($daysdiff);
					}
				}
			}
			$isdue = 0;
			$pricestr = array();
			$optstr = array();
			foreach ($ordersrooms as $kor => $or) {
				$num = $kor + 1;
				if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
					// package cost or cust_cost may not be inclusive of taxes if prices tax included is off
					$calctar = VikBooking::sayPackagePlusIva($or['cust_cost'], $or['cust_idiva']);
					$isdue += $calctar;
					$pricestr[$num] = (!empty($or['pkg_name']) ? $or['pkg_name'] : (!empty($or['otarplan']) ? ucwords($or['otarplan']) : JText::translate('VBOROOMCUSTRATEPLAN'))).": ".$calctar." ".$currencyname;
				} elseif (array_key_exists($num, $tars) && is_array($tars[$num])) {
					$display_rate = !empty($or['room_cost']) ? $or['room_cost'] : $tars[$num]['cost'];
					$calctar = VikBooking::sayCostPlusIva($display_rate, $tars[$num]['idprice']);
					$tars[$num]['calctar'] = $calctar;
					$isdue += $calctar;
					$pricestr[$num] = VikBooking::getPriceName($tars[$num]['idprice'], $vbo_tn) . ": " . $calctar . " " . $currencyname . (!empty($tars[$num]['attrdata']) ? "\n" . VikBooking::getPriceAttr($tars[$num]['idprice'], $vbo_tn) . ": " . $tars[$num]['attrdata'] : "");
				}
				if (!empty($or['optionals'])) {
					$stepo = explode(";", $or['optionals']);
					foreach ($stepo as $roptkey => $oo) {
						if (!empty($oo)) {
							$stept = explode(":", $oo);
							$q = "SELECT * FROM `#__vikbooking_optionals` WHERE `id`=" . $dbo->quote($stept[0]) . ";";
							$dbo->setQuery($q);
							$dbo->execute();
							if ($dbo->getNumRows() == 1) {
								$actopt = $dbo->loadAssocList();
								$vbo_tn->translateContents($actopt, '#__vikbooking_optionals', array(), array(), (!empty($order[0]['lang']) ? $order[0]['lang'] : null));
								$chvar = '';
								if (!empty($actopt[0]['ageintervals']) && $or['children'] > 0 && strstr($stept[1], '-') != false) {
									$optagenames = VikBooking::getOptionIntervalsAges($actopt[0]['ageintervals']);
									$optagepcent = VikBooking::getOptionIntervalsPercentage($actopt[0]['ageintervals']);
									$optageovrct = VikBooking::getOptionIntervalChildOverrides($actopt[0], $or['adults'], $or['children']);
									$child_num 	 = VikBooking::getRoomOptionChildNumber($or['optionals'], $actopt[0]['id'], $roptkey, $or['children']);
									$optagecosts = VikBooking::getOptionIntervalsCosts(isset($optageovrct['ageintervals_child' . ($child_num + 1)]) ? $optageovrct['ageintervals_child' . ($child_num + 1)] : $actopt[0]['ageintervals']);
									$agestept = explode('-', $stept[1]);
									$stept[1] = $agestept[0];
									$chvar = $agestept[1];
									if (array_key_exists(($chvar - 1), $optagepcent) && $optagepcent[($chvar - 1)] == 1) {
										//percentage value of the adults tariff
										if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
											$optagecosts[($chvar - 1)] = $or['cust_cost'] * $optagecosts[($chvar - 1)] / 100;
										} else {
											$display_rate = !empty($or['room_cost']) ? $or['room_cost'] : $tars[$num]['cost'];
											$optagecosts[($chvar - 1)] = $display_rate * $optagecosts[($chvar - 1)] / 100;
										}
									} elseif (array_key_exists(($chvar - 1), $optagepcent) && $optagepcent[($chvar - 1)] == 2) {
										//VBO 1.10 - percentage value of room base cost
										if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
											$optagecosts[($chvar - 1)] = $or['cust_cost'] * $optagecosts[($chvar - 1)] / 100;
										} else {
											$display_rate = isset($tars[$num]['room_base_cost']) ? $tars[$num]['room_base_cost'] : (!empty($or['room_cost']) ? $or['room_cost'] : $tars[$num]['cost']);
											$optagecosts[($chvar - 1)] = $display_rate * $optagecosts[($chvar - 1)] / 100;
										}
									}
									$actopt[0]['chageintv'] = $chvar;
									$actopt[0]['name'] .= ' ('.$optagenames[($chvar - 1)].')';
									$actopt[0]['quan'] = $stept[1];
									$realcost = (intval($actopt[0]['perday']) == 1 ? (floatval($optagecosts[($chvar - 1)]) * $order[0]['days'] * $stept[1]) : (floatval($optagecosts[($chvar - 1)]) * $stept[1]));
								} else {
									$actopt[0]['quan'] = $stept[1];
									// VBO 1.11 - options percentage cost of the room total fee
									if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
										$deftar_basecosts = $or['cust_cost'];
									} else {
										$deftar_basecosts = !empty($or['room_cost']) ? $or['room_cost'] : $tars[$num]['cost'];
									}
									$actopt[0]['cost'] = (int)$actopt[0]['pcentroom'] ? ($deftar_basecosts * $actopt[0]['cost'] / 100) : $actopt[0]['cost'];
									//
									$realcost = (intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $order[0]['days'] * $stept[1]) : ($actopt[0]['cost'] * $stept[1]));
								}
								if (!empty($actopt[0]['maxprice']) && $actopt[0]['maxprice'] > 0 && $realcost > $actopt[0]['maxprice']) {
									$realcost = $actopt[0]['maxprice'];
									if (intval($actopt[0]['hmany']) == 1 && intval($stept[1]) > 1) {
										$realcost = $actopt[0]['maxprice'] * $stept[1];
									}
								}
								if ($actopt[0]['perperson'] == 1) {
									$realcost = $realcost * $or['adults'];
								}
								$tmpopr = VikBooking::sayOptionalsPlusIva($realcost, $actopt[0]['idiva']);
								$isdue += $tmpopr;
								$optstr[$num][] = ($stept[1] > 1 ? $stept[1] . " " : "") . $actopt[0]['name'] . ": " . $tmpopr . " " . $currencyname . "\n";
							}
						}
					}
				}
				//custom extra costs
				if (!empty($or['extracosts'])) {
					$cur_extra_costs = json_decode($or['extracosts'], true);
					foreach ($cur_extra_costs as $eck => $ecv) {
						$ecplustax = !empty($ecv['idtax']) ? VikBooking::sayOptionalsPlusIva($ecv['cost'], $ecv['idtax']) : $ecv['cost'];
						$isdue += $ecplustax;
						$optstr[$num][] = $ecv['name'] . ": " . $ecplustax . " " . $currencyname."\n";
					}
				}
				//
			}
			//vikbooking 1.1 coupon
			$usedcoupon = false;
			$origisdue = $isdue;
			if (strlen($order[0]['coupon']) > 0) {
				$usedcoupon = true;
				$expcoupon = explode(";", $order[0]['coupon']);
				$isdue = $isdue - $expcoupon[1];
			}
			// make sure to apply any previously refunded amount
			if ($order[0]['refund'] > 0) {
				$isdue -= $order[0]['refund'];
			}
			//
			//ConfirmationNumber
			$confirmnumber = $order[0]['confirmnumber'];
			//end ConfirmationNumber
			$esit_mess = JText::sprintf('VBORDEREMAILRESENT', $order[0]['custmail']);
			$status_str = JText::translate('VBCOMPLETED');
			if ($cancellation) {
				$confirmnumber = '';
				$esit_mess = JText::sprintf('VBCANCORDEREMAILSENT', $order[0]['custmail']);
				$status_str = JText::translate('VBCANCELLED');
			} elseif ($order[0]['status'] == 'standby') {
				$confirmnumber = '';
				$status_str = JText::translate('VBWAITINGFORPAYMENT');
			}
			$app = JFactory::getApplication();
			$app->enqueueMessage($esit_mess);
			//force the original total amount if rates have changed
			if (number_format($isdue, 2) != number_format($order[0]['total'], 2)) {
				$isdue = $order[0]['total'];
			}
			//
			
			// VikBooking::sendCustMailFromBack($order[0]['custmail'], strip_tags($ftitle)." ".JText::translate('VBRENTALORD'), $ftitle, $nowts, $order[0]['custdata'], $rooms, $order[0]['checkin'], $order[0]['checkout'], $pricestr, $optstr, $isdue, $viklink, $status_str, $order[0]['id'], $order[0]['coupon'], $arrpeople, $confirmnumber);

			// send email notification to guest
			VikBooking::sendBookingEmail($order[0]['id'], array('guest'));
			//

			if ($cancellation) {
				/**
				 * If "send cancellation email", we log the event in the history.
				 * 
				 * @since 	1.14 (J) - 1.4.0 (WP)
				 */
				VikBooking::getBookingHistoryInstance()->setBid($order[0]['id'])->store('EC');
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=".$oid);
	}

	public function setordconfirmed() {
		$cid = VikRequest::getVar('cid', array(0));
		$oid = $cid[0];
		$dbo = JFactory::getDBO();
		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`=".(int)$oid." AND `status` != 'confirmed';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$order = $dbo->loadAssocList();

			/**
			 * Memorize the original booking status for VCM in case of OTA booking.
			 * 
			 * @since 	1.14 (J) - 1.4.0 (WP)
			 */
			$original_book_status = null;
			if (!empty($order[0]['idorderota']) && !empty($order[0]['channel'])) {
				$original_book_status = $order[0]['status'];
			}
			//

			$vbo_tn = VikBooking::getTranslator();
			//check if the language in use is the same as the one used during the checkout
			if (!empty($order[0]['lang'])) {
				$lang = JFactory::getLanguage();
				if ($lang->getTag() != $order[0]['lang']) {
					/**
					 * @wponly 	constant name for lang path is different
					 */
					$lang->load('com_vikbooking', VIKBOOKING_LANG, $order[0]['lang'], true);
				}
				if ($vbo_tn->getDefaultLang() != $order[0]['lang']) {
					// force the translation to start because contents should be translated
					$vbo_tn::$force_tolang = $order[0]['lang'];
				}
			}
			//
			$q = "SELECT `or`.*,`r`.`id` AS `r_reference_id`,`r`.`name`,`r`.`units`,`r`.`fromadult`,`r`.`toadult`,`r`.`params` FROM `#__vikbooking_ordersrooms` AS `or`,`#__vikbooking_rooms` AS `r` WHERE `or`.`idorder`=" . (int)$order[0]['id'] . " AND `or`.`idroom`=`r`.`id` ORDER BY `or`.`id` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$ordersrooms = $dbo->loadAssocList();
			$vbo_tn->translateContents($ordersrooms, '#__vikbooking_rooms', array('id' => 'r_reference_id'));
			$currencyname = VikBooking::getCurrencyName();
			$realback = VikBooking::getHoursRoomAvail() * 3600;
			$realback += $order[0]['checkout'];
			$allbook = true;
			$notavail = array();

			/**
			 * We need to calculate a minus operator for each room that was booked more than once.
			 * In case we are confirming a booking for more than one unit of the same room, we need to
			 * make sure the calculation is made properly, as only one unit of that room could be free.
			 * 
			 * @since 	1.3.0
			 */
			$units_minus_oper = array();
			foreach ($ordersrooms as $ind => $or) {
				if (!isset($units_minus_oper[$or['idroom']])) {
					$units_minus_oper[$or['idroom']] = -1;
				}
				// increase counter
				$units_minus_oper[$or['idroom']]++;
			}
			//

			foreach ($ordersrooms as $ind => $or) {
				if (!VikBooking::roomBookable($or['idroom'], ($or['units'] - $units_minus_oper[$or['idroom']]), $order[0]['checkin'], $order[0]['checkout'])) {
					$allbook = false;
					$notavail[] = $or['name']." (".JText::translate('VBMAILADULTS').": ".$or['adults'].($or['children'] > 0 ? " - ".JText::translate('VBMAILCHILDREN').": ".$or['children'] : "").")";
				}
			}

			if (!$allbook) {
				VikError::raiseWarning('', JText::translate('VBERRCONFORDERNOTAVROOM').' '.implode(", ", $notavail).'<br/>'.JText::translate('VBUNABLESETRESCONF'));
			} else {
				$rooms = array();
				$tars = array();
				$arrpeople = array();
				$is_package = !empty($order[0]['pkg']) ? true : false;
				$rooms_booked = array();
				foreach ($ordersrooms as $ind => $or) {
					array_push($rooms_booked, (int)$or['idroom']);
					$q = "INSERT INTO `#__vikbooking_busy` (`idroom`,`checkin`,`checkout`,`realback`) VALUES(".(int)$or['idroom'].", ".(int)$order[0]['checkin'].", ".(int)$order[0]['checkout'].", ".(int)$realback.");";
					$dbo->setQuery($q);
					$dbo->execute();
					$lid = $dbo->insertid();
					$q = "INSERT INTO `#__vikbooking_ordersbusy` (`idorder`,`idbusy`) VALUES(".(int)$oid.", ".(int)$lid.");";
					$dbo->setQuery($q);
					$dbo->execute();
				}
				$q = "UPDATE `#__vikbooking_orders` SET `status`='confirmed' WHERE `id`=".(int)$order[0]['id'].";";
				$dbo->setQuery($q);
				$dbo->execute();
				$q = "DELETE FROM `#__vikbooking_tmplock` WHERE `idorder`=".(int)$order[0]['id'].";";
				$dbo->setQuery($q);
				$dbo->execute();
				//Booking History
				VikBooking::getBookingHistoryInstance()->setBid($order[0]['id'])->store('TC');
				//
				// check if some of the rooms booked have shared calendars
				VikBooking::updateSharedCalendars($order[0]['id'], $rooms_booked, $order[0]['checkin'], $order[0]['checkout']);
				//
				//send mail
				$ftitle = VikBooking::getFrontTitle();
				$nowts = time();
				$viklink = JURI::root()."index.php?option=com_vikbooking&view=booking&sid=".$order[0]['sid']."&ts=".$order[0]['ts'];
				//Assign room specific unit
				$set_room_indexes = VikBooking::autoRoomUnit();
				$room_indexes_usemap = array();
				//
				foreach ($ordersrooms as $kor => $or) {
					$num = $kor + 1;
					$rooms[$num] = $or;
					$arrpeople[$num]['adults'] = $or['adults'];
					$arrpeople[$num]['children'] = $or['children'];
					//Assign room specific unit
					if ($set_room_indexes === true) {
						$room_indexes = VikBooking::getRoomUnitNumsAvailable($order[0], $or['r_reference_id']);
						$use_ind_key = 0;
						if (count($room_indexes)) {
							if (!array_key_exists($or['r_reference_id'], $room_indexes_usemap)) {
								$room_indexes_usemap[$or['r_reference_id']] = $use_ind_key;
							} else {
								$use_ind_key = $room_indexes_usemap[$or['r_reference_id']];
							}
							$q = "UPDATE `#__vikbooking_ordersrooms` SET `roomindex`=".(int)$room_indexes[$use_ind_key]." WHERE `id`=".(int)$or['id'].";";
							$dbo->setQuery($q);
							$dbo->execute();
							//update rooms references for the customer email sending function
							$rooms[$num]['roomindex'] = (int)$room_indexes[$use_ind_key];
							//
							$room_indexes_usemap[$or['r_reference_id']]++;
						}
					}
					//
					if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
						//package or custom cost set from the back-end
						continue;
					}
					$q = "SELECT * FROM `#__vikbooking_dispcost` WHERE `id`=" . (int)$or['idtar'] . ";";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($dbo->getNumRows() > 0) {
						$tar = $dbo->loadAssocList();
						$tar = VikBooking::applySeasonsRoom($tar, $order[0]['checkin'], $order[0]['checkout']);
						//different usage
						if ($or['fromadult'] <= $or['adults'] && $or['toadult'] >= $or['adults']) {
							$diffusageprice = VikBooking::loadAdultsDiff($or['idroom'], $or['adults']);
							//Occupancy Override
							$occ_ovr = VikBooking::occupancyOverrideExists($tar, $or['adults']);
							$diffusageprice = $occ_ovr !== false ? $occ_ovr : $diffusageprice;
							//
							if (is_array($diffusageprice)) {
								//set a charge or discount to the price(s) for the different usage of the room
								foreach ($tar as $kpr => $vpr) {
									$tar[$kpr]['diffusage'] = $or['adults'];
									if ($diffusageprice['chdisc'] == 1) {
										//charge
										if ($diffusageprice['valpcent'] == 1) {
											//fixed value
											$tar[$kpr]['diffusagecostpernight'] = $diffusageprice['pernight'] == 1 ? 1 : 0;
											$aduseval = $diffusageprice['pernight'] == 1 ? $diffusageprice['value'] * $tar[$kpr]['days'] : $diffusageprice['value'];
											$tar[$kpr]['diffusagecost'] = "+".$aduseval;
											$tar[$kpr]['room_base_cost'] = $vpr['cost'];
											$tar[$kpr]['cost'] = $vpr['cost'] + $aduseval;
										} else {
											//percentage value
											$tar[$kpr]['diffusagecostpernight'] = $diffusageprice['pernight'] == 1 ? $vpr['cost'] : 0;
											$aduseval = $diffusageprice['pernight'] == 1 ? round(($vpr['cost'] * $diffusageprice['value'] / 100) * $tar[$kpr]['days'] + $vpr['cost'], 2) : round(($vpr['cost'] * (100 + $diffusageprice['value']) / 100), 2);
											$tar[$kpr]['diffusagecost'] = "+".$diffusageprice['value']."%";
											$tar[$kpr]['room_base_cost'] = $vpr['cost'];
											$tar[$kpr]['cost'] = $aduseval;
										}
									} else {
										//discount
										if ($diffusageprice['valpcent'] == 1) {
											//fixed value
											$tar[$kpr]['diffusagecostpernight'] = $diffusageprice['pernight'] == 1 ? 1 : 0;
											$aduseval = $diffusageprice['pernight'] == 1 ? $diffusageprice['value'] * $tar[$kpr]['days'] : $diffusageprice['value'];
											$tar[$kpr]['diffusagecost'] = "-".$aduseval;
											$tar[$kpr]['room_base_cost'] = $vpr['cost'];
											$tar[$kpr]['cost'] = $vpr['cost'] - $aduseval;
										} else {
											//percentage value
											$tar[$kpr]['diffusagecostpernight'] = $diffusageprice['pernight'] == 1 ? $vpr['cost'] : 0;
											$aduseval = $diffusageprice['pernight'] == 1 ? round($vpr['cost'] - ((($vpr['cost'] / $tar[$kpr]['days']) * $diffusageprice['value'] / 100) * $tar[$kpr]['days']), 2) : round(($vpr['cost'] * (100 - $diffusageprice['value']) / 100), 2);
											$tar[$kpr]['diffusagecost'] = "-".$diffusageprice['value']."%";
											$tar[$kpr]['room_base_cost'] = $vpr['cost'];
											$tar[$kpr]['cost'] = $aduseval;
										}
									}
								}
							}
						}
						//
						$tars[$num] = $tar[0];
					} else {
						VikError::raiseWarning('', JText::translate('VBERRNOFAREFOUND'));
					}
				}
				$pcheckin = $order[0]['checkin'];
				$pcheckout = $order[0]['checkout'];
				$secdiff = $pcheckout - $pcheckin;
				$daysdiff = $secdiff / 86400;
				if (is_int($daysdiff)) {
					if ($daysdiff < 1) {
						$daysdiff = 1;
					}
				} else {
					if ($daysdiff < 1) {
						$daysdiff = 1;
					} else {
						$sum = floor($daysdiff) * 86400;
						$newdiff = $secdiff - $sum;
						$maxhmore = VikBooking::getHoursMoreRb() * 3600;
						if ($maxhmore >= $newdiff) {
							$daysdiff = floor($daysdiff);
						} else {
							$daysdiff = ceil($daysdiff);
						}
					}
				}
				$isdue = 0;
				$pricestr = array();
				$optstr = array();
				foreach ($ordersrooms as $kor => $or) {
					$num = $kor + 1;
					if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
						// package cost or cust_cost may not be inclusive of taxes if prices tax included is off
						$calctar = VikBooking::sayPackagePlusIva($or['cust_cost'], $or['cust_idiva']);
						$isdue += $calctar;
						$pricestr[$num] = (!empty($or['pkg_name']) ? $or['pkg_name'] : (!empty($or['otarplan']) ? ucwords($or['otarplan']) : JText::translate('VBOROOMCUSTRATEPLAN'))).": ".$calctar." ".$currencyname;
					} elseif (array_key_exists($num, $tars) && is_array($tars[$num])) {
						$display_rate = !empty($or['room_cost']) ? $or['room_cost'] : $tars[$num]['cost'];
						$calctar = VikBooking::sayCostPlusIva($display_rate, $tars[$num]['idprice']);
						$tars[$num]['calctar'] = $calctar;
						$isdue += $calctar;
						$pricestr[$num] = VikBooking::getPriceName($tars[$num]['idprice'], $vbo_tn) . ": " . $calctar . " " . $currencyname . (!empty($tars[$num]['attrdata']) ? "\n" . VikBooking::getPriceAttr($tars[$num]['idprice'], $vbo_tn) . ": " . $tars[$num]['attrdata'] : "");
					}
					if (!empty($or['optionals'])) {
						$stepo = explode(";", $or['optionals']);
						foreach ($stepo as $roptkey => $oo) {
							if (!empty($oo)) {
								$stept = explode(":", $oo);
								$q = "SELECT * FROM `#__vikbooking_optionals` WHERE `id`=" . $dbo->quote($stept[0]) . ";";
								$dbo->setQuery($q);
								$dbo->execute();
								if ($dbo->getNumRows() == 1) {
									$actopt = $dbo->loadAssocList();
									$vbo_tn->translateContents($actopt, '#__vikbooking_optionals');
									$chvar = '';
									if (!empty($actopt[0]['ageintervals']) && $or['children'] > 0 && strstr($stept[1], '-') != false) {
										$optagenames = VikBooking::getOptionIntervalsAges($actopt[0]['ageintervals']);
										$optagepcent = VikBooking::getOptionIntervalsPercentage($actopt[0]['ageintervals']);
										$optageovrct = VikBooking::getOptionIntervalChildOverrides($actopt[0], $or['adults'], $or['children']);
										$child_num 	 = VikBooking::getRoomOptionChildNumber($or['optionals'], $actopt[0]['id'], $roptkey, $or['children']);
										$optagecosts = VikBooking::getOptionIntervalsCosts(isset($optageovrct['ageintervals_child' . ($child_num + 1)]) ? $optageovrct['ageintervals_child' . ($child_num + 1)] : $actopt[0]['ageintervals']);
										$agestept = explode('-', $stept[1]);
										$stept[1] = $agestept[0];
										$chvar = $agestept[1];
										if (array_key_exists(($chvar - 1), $optagepcent) && $optagepcent[($chvar - 1)] == 1) {
											//percentage value of the adults tariff
											if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
												$optagecosts[($chvar - 1)] = $or['cust_cost'] * $optagecosts[($chvar - 1)] / 100;
											} else {
												$display_rate = !empty($or['room_cost']) ? $or['room_cost'] : $tars[$num]['cost'];
												$optagecosts[($chvar - 1)] = $display_rate * $optagecosts[($chvar - 1)] / 100;
											}
										} elseif (array_key_exists(($chvar - 1), $optagepcent) && $optagepcent[($chvar - 1)] == 2) {
											//VBO 1.10 - percentage value of room base cost
											if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
												$optagecosts[($chvar - 1)] = $or['cust_cost'] * $optagecosts[($chvar - 1)] / 100;
											} else {
												$display_rate = isset($tars[$num]['room_base_cost']) ? $tars[$num]['room_base_cost'] : (!empty($or['room_cost']) ? $or['room_cost'] : $tars[$num]['cost']);
												$optagecosts[($chvar - 1)] = $display_rate * $optagecosts[($chvar - 1)] / 100;
											}
										}
										$actopt[0]['chageintv'] = $chvar;
										$actopt[0]['name'] .= ' ('.$optagenames[($chvar - 1)].')';
										$actopt[0]['quan'] = $stept[1];
										$realcost = (intval($actopt[0]['perday']) == 1 ? (floatval($optagecosts[($chvar - 1)]) * $order[0]['days'] * $stept[1]) : (floatval($optagecosts[($chvar - 1)]) * $stept[1]));
									} else {
										$actopt[0]['quan'] = $stept[1];
										// VBO 1.11 - options percentage cost of the room total fee
										if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
											$deftar_basecosts = $or['cust_cost'];
										} else {
											$deftar_basecosts = !empty($or['room_cost']) ? $or['room_cost'] : $tars[$num]['cost'];
										}
										$actopt[0]['cost'] = (int)$actopt[0]['pcentroom'] ? ($deftar_basecosts * $actopt[0]['cost'] / 100) : $actopt[0]['cost'];
										//
										$realcost = (intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $order[0]['days'] * $stept[1]) : ($actopt[0]['cost'] * $stept[1]));
									}
									if (!empty($actopt[0]['maxprice']) && $actopt[0]['maxprice'] > 0 && $realcost > $actopt[0]['maxprice']) {
										$realcost = $actopt[0]['maxprice'];
										if (intval($actopt[0]['hmany']) == 1 && intval($stept[1]) > 1) {
											$realcost = $actopt[0]['maxprice'] * $stept[1];
										}
									}
									if ($actopt[0]['perperson'] == 1) {
										$realcost = $realcost * $or['adults'];
									}
									$tmpopr = VikBooking::sayOptionalsPlusIva($realcost, $actopt[0]['idiva']);
									$isdue += $tmpopr;
									$optstr[$num][] = ($stept[1] > 1 ? $stept[1] . " " : "") . $actopt[0]['name'] . ": " . $tmpopr . " " . $currencyname . "\n";
								}
							}
						}
					}
					//custom extra costs
					if (!empty($or['extracosts'])) {
						$cur_extra_costs = json_decode($or['extracosts'], true);
						foreach ($cur_extra_costs as $eck => $ecv) {
							$ecplustax = !empty($ecv['idtax']) ? VikBooking::sayOptionalsPlusIva($ecv['cost'], $ecv['idtax']) : $ecv['cost'];
							$isdue += $ecplustax;
							$optstr[$num][] = $ecv['name'] . ": " . $ecplustax . " " . $currencyname."\n";
						}
					}
					//
				}
				//vikbooking 1.1 coupon
				$usedcoupon = false;
				$origisdue = $isdue;
				if (strlen($order[0]['coupon']) > 0) {
					$usedcoupon = true;
					$expcoupon = explode(";", $order[0]['coupon']);
					$isdue = $isdue - $expcoupon[1];
				}
				// make sure to apply any previously refunded amount
				if ($order[0]['refund'] > 0) {
					$isdue -= $order[0]['refund'];
				}
				//
				//ConfirmationNumber
				$confirmnumber = VikBooking::generateConfirmNumber($order[0]['id'], true);
				//end ConfirmationNumber
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::translate('VBORDERSETASCONF'));

				// notify the customer unless it was a re-confirmation
				$pskip = VikRequest::getInt('skip_notification', 0, 'request');
				if ($pskip < 1) {
					// customer email notification
					// VikBooking::sendCustMailFromBack($order[0]['custmail'], strip_tags($ftitle)." ".JText::translate('VBRENTALORD'), $ftitle, $nowts, $order[0]['custdata'], $rooms, $order[0]['checkin'], $order[0]['checkout'], $pricestr, $optstr, $isdue, $viklink, JText::translate('VBCOMPLETED'), $order[0]['id'], $order[0]['coupon'], $arrpeople, $confirmnumber);

					// send email notification to guest
					VikBooking::sendBookingEmail($order[0]['id'], array('guest'));
					//
					
					// SMS skipping the administrator
					VikBooking::sendBookingSMS($order[0]['id'], array('admin'));
				}
				
				//Invoke Channel Manager
				$vcm_autosync = VikBooking::vcmAutoUpdate();
				if ($vcm_autosync > 0) {
					$vcm_obj = VikBooking::getVcmInvoker();
					$vcm_obj->setOids(array($order[0]['id']))->setSyncType('new')->setOriginalStatuses(array($original_book_status));
					$sync_result = $vcm_obj->doSync();
					if ($sync_result === false) {
						$vcm_err = $vcm_obj->getError();
						VikError::raiseWarning('', JText::translate('VBCHANNELMANAGERRESULTKO').' <a href="index.php?option=com_vikchannelmanager" target="_blank">'.JText::translate('VBCHANNELMANAGEROPEN').'</a> '.(strlen($vcm_err) > 0 ? '('.$vcm_err.')' : ''));
					}
				} elseif (file_exists(VCM_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "synch.vikbooking.php")) {
					$vcm_sync_url = 'index.php?option=com_vikbooking&task=invoke_vcm&stype=new&cid[]='.$order[0]['id'].'&returl='.urlencode('index.php?option=com_vikbooking&task=editorder&cid[]='.$order[0]['id']);
					VikError::raiseNotice('', JText::translate('VBCHANNELMANAGERINVOKEASK').' <button type="button" class="btn btn-primary" onclick="document.location.href=\''.$vcm_sync_url.'\';">'.JText::translate('VBCHANNELMANAGERSENDRQ').'</button>');
				}
				//
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=".$oid);
	}

	public function payments() {
		VikBookingHelper::printHeader("14");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'payments'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function newpayment() {
		VikBookingHelper::printHeader("14");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managepayment'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function editpayment() {
		VikBookingHelper::printHeader("14");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managepayment'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function createpayment() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$mainframe = JFactory::getApplication();
		$pname = VikRequest::getString('name', '', 'request');
		$ppayment = VikRequest::getString('payment', '', 'request');
		$ppublished = VikRequest::getString('published', '', 'request');
		$pcharge = VikRequest::getFloat('charge', '', 'request');
		$psetconfirmed = VikRequest::getString('setconfirmed', '', 'request');
		$phidenonrefund = VikRequest::getInt('hidenonrefund', '', 'request');
		$ponlynonrefund = VikRequest::getInt('onlynonrefund', '', 'request');
		$pshownotealw = VikRequest::getString('shownotealw', '', 'request');
		$pnote = VikRequest::getString('note', '', 'request', VIKREQUEST_ALLOWHTML);
		$pval_pcent = VikRequest::getString('val_pcent', '', 'request');
		$pval_pcent = !in_array($pval_pcent, array('1', '2')) ? 1 : $pval_pcent;
		$pch_disc = VikRequest::getString('ch_disc', '', 'request');
		$pch_disc = !in_array($pch_disc, array('1', '2')) ? 1 : $pch_disc;
		$poutposition = VikRequest::getString('outposition', 'top', 'request');
		$plogo = VikRequest::getString('logo', '', 'request');
		$vikpaymentparams = VikRequest::getVar('vikpaymentparams', array(0));
		$payparamarr = array();
		$payparamstr = '';
		if (count($vikpaymentparams) > 0) {
			foreach ($vikpaymentparams as $setting => $cont) {
				if (strlen($setting) > 0) {
					$payparamarr[$setting] = $cont;
				}
			}
			if (count($payparamarr) > 0) {
				$payparamstr = json_encode($payparamarr);
			}
		}
		$dbo = JFactory::getDbo();
		if (!empty($pname) && !empty($ppayment)) {
			$setpub = $ppublished == "1" ? 1 : 0;
			$psetconfirmed = $psetconfirmed == "1" ? 1 : 0;
			$pshownotealw = $pshownotealw == "1" ? 1 : 0;
			$q = "SELECT `id` FROM `#__vikbooking_gpayments` WHERE `file`=".$dbo->quote($ppayment).";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() >= 0) {
				$q = "INSERT INTO `#__vikbooking_gpayments` (`name`,`file`,`published`,`note`,`charge`,`setconfirmed`,`shownotealw`,`val_pcent`,`ch_disc`,`params`,`hidenonrefund`,`onlynonrefund`,`outposition`,`logo`) VALUES(".$dbo->quote($pname).",".$dbo->quote($ppayment).",'".$setpub."',".$dbo->quote($pnote).",".$dbo->quote($pcharge).",'".$psetconfirmed."','".$pshownotealw."','".$pval_pcent."','".$pch_disc."',".$dbo->quote($payparamstr).",".($phidenonrefund > 0 ? '1' : '0').",".($ponlynonrefund > 0 ? '1' : '0').", " . $dbo->quote($poutposition) . ", " . $dbo->quote($plogo) . ");";
				$dbo->setQuery($q);
				$dbo->execute();
				$mainframe->enqueueMessage(JText::translate('VBPAYMENTSAVED'));
				$mainframe->redirect("index.php?option=com_vikbooking&task=payments");
			} else {
				VikError::raiseWarning('', JText::translate('ERRINVFILEPAYMENT'));
				$mainframe->redirect("index.php?option=com_vikbooking&task=newpayment");
			}
		} else {
			$mainframe->redirect("index.php?option=com_vikbooking&task=newpayment");
		}
	}

	public function updatepayment() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$mainframe = JFactory::getApplication();
		$pwhere = VikRequest::getString('where', '', 'request');
		$pname = VikRequest::getString('name', '', 'request');
		$ppayment = VikRequest::getString('payment', '', 'request');
		$ppublished = VikRequest::getString('published', '', 'request');
		$pcharge = VikRequest::getFloat('charge', '', 'request');
		$psetconfirmed = VikRequest::getString('setconfirmed', '', 'request');
		$phidenonrefund = VikRequest::getInt('hidenonrefund', '', 'request');
		$ponlynonrefund = VikRequest::getInt('onlynonrefund', '', 'request');
		$pshownotealw = VikRequest::getString('shownotealw', '', 'request');
		$pnote = VikRequest::getString('note', '', 'request', VIKREQUEST_ALLOWRAW);
		$pval_pcent = VikRequest::getString('val_pcent', '', 'request');
		$pval_pcent = !in_array($pval_pcent, array('1', '2')) ? 1 : $pval_pcent;
		$pch_disc = VikRequest::getString('ch_disc', '', 'request');
		$pch_disc = !in_array($pch_disc, array('1', '2')) ? 1 : $pch_disc;
		$poutposition = VikRequest::getString('outposition', 'top', 'request');
		$plogo = VikRequest::getString('logo', '', 'request');
		$vikpaymentparams = VikRequest::getVar('vikpaymentparams', array(0));
		$payparamarr = array();
		$payparamstr = '';
		if (count($vikpaymentparams) > 0) {
			foreach ($vikpaymentparams as $setting => $cont) {
				if (strlen($setting) > 0) {
					$payparamarr[$setting] = $cont;
				}
			}
			if (count($payparamarr) > 0) {
				$payparamstr = json_encode($payparamarr);
			}
		}
		$dbo = JFactory::getDbo();
		if (!empty($pname) && !empty($ppayment) && !empty($pwhere)) {
			$setpub = $ppublished == "1" ? 1 : 0;
			$psetconfirmed = $psetconfirmed == "1" ? 1 : 0;
			$pshownotealw = $pshownotealw == "1" ? 1 : 0;
			$q = "SELECT `id` FROM `#__vikbooking_gpayments` WHERE `file`=".$dbo->quote($ppayment)." AND `id`!='".$pwhere."';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() >= 0) {
				$q = "UPDATE `#__vikbooking_gpayments` SET `name`=".$dbo->quote($pname).",`file`=".$dbo->quote($ppayment).",`published`='".$setpub."',`note`=".$dbo->quote($pnote).",`charge`=".$dbo->quote($pcharge).",`setconfirmed`='".$psetconfirmed."',`shownotealw`='".$pshownotealw."',`val_pcent`='".$pval_pcent."',`ch_disc`='".$pch_disc."',`params`=".$dbo->quote($payparamstr).",`hidenonrefund`=".($phidenonrefund > 0 ? '1' : '0').",`onlynonrefund`=".($ponlynonrefund > 0 ? '1' : '0').",`outposition`=" . $dbo->quote($poutposition) . ",`logo`=" . $dbo->quote($plogo) . " WHERE `id`=".$dbo->quote($pwhere).";";
				$dbo->setQuery($q);
				$dbo->execute();
				$mainframe->enqueueMessage(JText::translate('VBPAYMENTUPDATED'));
				$mainframe->redirect("index.php?option=com_vikbooking&task=payments");
			} else {
				VikError::raiseWarning('', JText::translate('ERRINVFILEPAYMENT'));
				$mainframe->redirect("index.php?option=com_vikbooking&task=editpayment&cid[]=".$pwhere);
			}
		} else {
			$mainframe->redirect("index.php?option=com_vikbooking&task=editpayment&cid[]=".$pwhere);
		}
	}

	public function removepayments() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikbooking_gpayments` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=payments");
	}

	public function modavailpayment() {
		$cid = VikRequest::getVar('cid', array(0));
		$idp = $cid[0];
		if (!empty($idp)) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `published` FROM `#__vikbooking_gpayments` WHERE `id`=".intval($idp).";";
			$dbo->setQuery($q);
			$dbo->execute();
			$get = $dbo->loadAssocList();
			$q = "UPDATE `#__vikbooking_gpayments` SET `published`=".(intval($get[0]['published']) == 1 ? '0' : '1')." WHERE `id`=".intval($idp).";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=payments");
	}

	public function seasons() {
		VikBookingHelper::printHeader("13");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'seasons'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function newseason() {
		VikBookingHelper::printHeader("13");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageseason'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function editseason() {
		VikBookingHelper::printHeader("13");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageseason'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function updateseason() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$this->do_updateseason();
	}

	public function updateseasonstay() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$this->do_updateseason(true);
	}

	private function do_updateseason($stay = false) {
		$mainframe = JFactory::getApplication();
		$pwhere = VikRequest::getString('where', '', 'request');
		$pfrom = VikRequest::getString('from', '', 'request');
		$pto = VikRequest::getString('to', '', 'request');
		$ptype = VikRequest::getString('type', '', 'request');
		$pdiffcost = VikRequest::getFloat('diffcost', '', 'request');
		$pidrooms = VikRequest::getVar('idrooms', array(0));
		$pidprices = VikRequest::getVar('idprices', array(0));
		$pwdays = VikRequest::getVar('wdays', array());
		$pspname = VikRequest::getString('spname', '', 'request');
		$pcheckinincl = VikRequest::getString('checkinincl', '', 'request');
		$pcheckinincl = $pcheckinincl == 1 ? 1 : 0;
		$pyeartied = VikRequest::getString('yeartied', '', 'request');
		$pyeartied = $pyeartied == "1" ? 1 : 0;
		$tieyear = 0;
		$ppromo = VikRequest::getInt('promo', 0, 'request');
		$ppromo = $ppromo == 1 ? 1 : 0;
		$ppromodaysadv = VikRequest::getInt('promodaysadv', '', 'request');
		$ppromominlos = VikRequest::getInt('promominlos', '', 'request');
		$ppromotxt = VikRequest::getString('promotxt', '', 'request', VIKREQUEST_ALLOWHTML);
		$pval_pcent = VikRequest::getString('val_pcent', '', 'request');
		$pval_pcent = $pval_pcent == "1" ? 1 : 2;
		$proundmode = VikRequest::getString('roundmode', '', 'request');
		$proundmode = (!empty($proundmode) && in_array($proundmode, array('PHP_ROUND_HALF_UP', 'PHP_ROUND_HALF_DOWN')) ? $proundmode : '');
		$pnightsoverrides = VikRequest::getVar('nightsoverrides', array());
		$pvaluesoverrides = VikRequest::getVar('valuesoverrides', array());
		$pandmoreoverride = VikRequest::getVar('andmoreoverride', array());
		$padultsdiffchdisc = VikRequest::getVar('adultsdiffchdisc', array());
		$padultsdiffval = VikRequest::getVar('adultsdiffval', array());
		$padultsdiffvalpcent = VikRequest::getVar('adultsdiffvalpcent', array());
		$padultsdiffpernight = VikRequest::getVar('adultsdiffpernight', array());
		$ppromolastmind = VikRequest::getInt('promolastmind', 0, 'request');
		$ppromolastminh = VikRequest::getInt('promolastminh', 0, 'request');
		$promolastmin = ($ppromolastmind * 86400) + ($ppromolastminh * 3600);
		$ppromofinalprice = VikRequest::getInt('promofinalprice', 0, 'request');
		$ppromofinalprice = $ppromo ? $ppromofinalprice : 0;
		$occupancy_ovr = array();
		$losverridestr = "";
		$dbo = JFactory::getDbo();
		$session = JFactory::getSession();
		$updforvcm = $session->get('vbVcmRatesUpd', '');
		$updforvcm = empty($updforvcm) || !is_array($updforvcm) ? array() : $updforvcm;
		// check null dates
		if ($dbo->getNullDate() == $pfrom) {
			$pfrom = '';
		}
		if ($dbo->getNullDate() == $pto) {
			$pto = '';
		}
		//
		if ((!empty($pfrom) && !empty($pto)) || count($pwdays) > 0) {
			$skipseason = false;
			if (empty($pfrom) || empty($pto)) {
				$skipseason = true;
			}
			$skipdays = false;
			$wdaystr = null;
			if (count($pwdays) == 0) {
				$skipdays = true;
			} else {
				$wdaystr = "";
				foreach ($pwdays as $wd) {
					$wdaystr .= $wd.';';
				}
			}
			$roomstr = "";
			$roomids = array();
			if (@count($pidrooms) > 0) {
				foreach ($pidrooms as $room) {
					if (empty($room)) {
						continue;
					}
					$roomstr .= "-".$room."-,";
					$roomids[] = (int)$room;
				}
			}
			$pricestr = "";
			$priceids = array();
			if (@count($pidprices) > 0) {
				foreach ($pidprices as $price) {
					if (empty($price)) {
						continue;
					}
					$pricestr .= "-".$price."-,";
					$priceids[] = (int)$price;
				}
			}
			$valid = true;
			$double_records = array();
			$sfrom = null;
			$sto = null;
			// value overrides
			if (count($pnightsoverrides) > 0 && count($pvaluesoverrides) > 0) {
				foreach ($pnightsoverrides as $ko => $no) {
					if (!empty($no) && strlen(trim($pvaluesoverrides[$ko])) > 0) {
						$infiniteclause = intval($pandmoreoverride[$ko]) == 1 ? '-i' : '';
						$losverridestr .= intval($no).$infiniteclause.':'.trim($pvaluesoverrides[$ko]).'_';
					}
				}
			}
			//
			if (!$skipseason) {
				$first = VikBooking::getDateTimestamp($pfrom, 0, 0);
				$second = VikBooking::getDateTimestamp($pto, 0, 0);
				if ($second > 0 && $second == $first) {
					$second += 86399;
				}
				if ($second > $first) {
					$baseone = getdate($first);
					$basets = mktime(0, 0, 0, 1, 1, $baseone['year']);
					$sfrom = $baseone[0] - $basets;
					$basetwo = getdate($second);
					$basets = mktime(0, 0, 0, 1, 1, $basetwo['year']);
					$sto = $basetwo[0] - $basets;
					//check leap year
					if ($baseone['year'] % 4 == 0 && ($baseone['year'] % 100 != 0 || $baseone['year'] % 400 == 0)) {
						$leapts = mktime(0, 0, 0, 2, 29, $baseone['year']);
						if ($baseone[0] > $leapts) {
							$sfrom -= 86400;
							/**
							 * To avoid issue with leap years and dates near Feb 29th, we only reduce the seconds if these were reduced
							 * for the from-date of the seasons. Doing it just for the to-date in 2019 for 2020 (leap) produced invalid results.
							 * 
							 * @since 	July 2nd 2019
							 */
							if ($basetwo['year'] % 4 == 0 && ($basetwo['year'] % 100 != 0 || $basetwo['year'] % 400 == 0)) {
								$leapts = mktime(0, 0, 0, 2, 29, $basetwo['year']);
								if ($basetwo[0] > $leapts) {
									$sto -= 86400;
								}
							}
						}
					}
					//end leap year
					//tied to the year
					if ($pyeartied == 1) {
						$tieyear = $baseone['year'];
					}
					//
					//Occupancy Override
					if (count($padultsdiffval) > 0) {
						foreach ($padultsdiffval as $rid => $valovr_arr) {
							if (!is_array($valovr_arr) || !is_array($padultsdiffchdisc[$rid]) || !is_array($padultsdiffvalpcent[$rid]) || !is_array($padultsdiffpernight[$rid])) {
								continue;
							}
							foreach ($valovr_arr as $occ => $valovr) {
								if (!(strlen($valovr) > 0) || !(strlen($padultsdiffchdisc[$rid][$occ]) > 0) || !(strlen($padultsdiffvalpcent[$rid][$occ]) > 0) || !(strlen($padultsdiffpernight[$rid][$occ]) > 0)) {
									continue;
								}
								if (!array_key_exists($rid, $occupancy_ovr)) {
									$occupancy_ovr[$rid] = array();
								}
								$occupancy_ovr[$rid][$occ] = array('chdisc' => (int)$padultsdiffchdisc[$rid][$occ], 'valpcent' => (int)$padultsdiffvalpcent[$rid][$occ], 'pernight' => (int)$padultsdiffpernight[$rid][$occ], 'value' => (float)$valovr);
							}
						}
					}
					//
					//check if seasons dates are valid
					$q = "SELECT `id`,`spname` FROM `#__vikbooking_seasons` WHERE `from`<=".$dbo->quote($sfrom)." AND `to`>=".$dbo->quote($sfrom)." AND `id`!=".$dbo->quote($pwhere)." AND `idrooms`=".$dbo->quote($roomstr)."".(!$skipdays ? " AND `wdays`='".$wdaystr."'" : "").($skipdays ? " AND (`from` > 0 OR `to` > 0) AND `wdays`=''" : "").($pyeartied == 1 ? " AND `year`=".$tieyear : " AND `year` IS NULL")." AND `idprices`=".$dbo->quote($pricestr)." AND `promo`=".$ppromo." AND `losoverride`=".$dbo->quote($losverridestr)." AND `occupancy_ovr`".(count($occupancy_ovr) > 0 ? "=".$dbo->quote(json_encode($occupancy_ovr)) : " IS NULL").";";
					$dbo->setQuery($q);
					$dbo->execute();
					$totfirst = $dbo->getNumRows();
					if ($totfirst > 0) {
						$valid = false;
						$similar = $dbo->loadAssocList();
						foreach ($similar as $sim) {
							$double_records[] = $sim['spname'];
						}
					}
					$q = "SELECT `id`,`spname` FROM `#__vikbooking_seasons` WHERE `from`<=".$dbo->quote($sto)." AND `to`>=".$dbo->quote($sto)." AND `id`!=".$dbo->quote($pwhere)." AND `idrooms`=".$dbo->quote($roomstr)."".(!$skipdays ? " AND `wdays`='".$wdaystr."'" : "").($skipdays ? " AND (`from` > 0 OR `to` > 0) AND `wdays`=''" : "").($pyeartied == 1 ? " AND `year`=".$tieyear : " AND `year` IS NULL")." AND `idprices`=".$dbo->quote($pricestr)." AND `promo`=".$ppromo." AND `losoverride`=".$dbo->quote($losverridestr)." AND `occupancy_ovr`".(count($occupancy_ovr) > 0 ? "=".$dbo->quote(json_encode($occupancy_ovr)) : " IS NULL").";";
					$dbo->setQuery($q);
					$dbo->execute();
					$totsecond = $dbo->getNumRows();
					if ($totsecond > 0) {
						$valid = false;
						$similar = $dbo->loadAssocList();
						foreach ($similar as $sim) {
							$double_records[] = $sim['spname'];
						}
					}
					$q = "SELECT `id`,`spname` FROM `#__vikbooking_seasons` WHERE `from`>=".$dbo->quote($sfrom)." AND `from`<=".$dbo->quote($sto)." AND `to`>=".$dbo->quote($sfrom)." AND `to`<=".$dbo->quote($sto)." AND `id`!=".$dbo->quote($pwhere)." AND `idrooms`=".$dbo->quote($roomstr)."".(!$skipdays ? " AND `wdays`='".$wdaystr."'" : "").($skipdays ? " AND (`from` > 0 OR `to` > 0) AND `wdays`=''" : "").($pyeartied == 1 ? " AND `year`=".$tieyear : " AND `year` IS NULL")." AND `idprices`=".$dbo->quote($pricestr)." AND `promo`=".$ppromo." AND `losoverride`=".$dbo->quote($losverridestr)." AND `occupancy_ovr`".(count($occupancy_ovr) > 0 ? "=".$dbo->quote(json_encode($occupancy_ovr)) : " IS NULL").";";
					$dbo->setQuery($q);
					$dbo->execute();
					$totthird = $dbo->getNumRows();
					if ($totthird > 0) {
						$valid = false;
						$similar = $dbo->loadAssocList();
						foreach ($similar as $sim) {
							$double_records[] = $sim['spname'];
						}
					}
					//
				} else {
					VikError::raiseWarning('', JText::translate('ERRINVDATESEASON'));
					$mainframe->redirect("index.php?option=com_vikbooking&task=editseason&cid[]=".$pwhere);
				}
			}
			if ($valid) {
				$q = "UPDATE `#__vikbooking_seasons` SET `type`='".($ptype == "1" ? "1" : "2")."',`from`=".$dbo->quote($sfrom).",`to`=".$dbo->quote($sto).",`diffcost`=".$dbo->quote($pdiffcost).",`idrooms`=".$dbo->quote($roomstr).",`spname`=".$dbo->quote($pspname).",`wdays`='".$wdaystr."',`checkinincl`='".$pcheckinincl."',`val_pcent`='".$pval_pcent."',`losoverride`=".$dbo->quote($losverridestr).",`roundmode`=".(!empty($proundmode) ? "'".$proundmode."'" : "NULL").",`year`=".($pyeartied == 1 ? $tieyear : "NULL").",`idprices`=".$dbo->quote($pricestr).",`promo`=".$ppromo.",`promodaysadv`=".(!empty($ppromodaysadv) ? $ppromodaysadv : "null").",`promotxt`=".$dbo->quote($ppromotxt).",`promominlos`=".(!empty($ppromominlos) ? $ppromominlos : "0").",`occupancy_ovr`=".(count($occupancy_ovr) > 0 ? $dbo->quote(json_encode($occupancy_ovr)) : "NULL").",`promolastmin`=".(int)$promolastmin.",`promofinalprice`={$ppromofinalprice} WHERE `id`=".$dbo->quote($pwhere).";";
				$dbo->setQuery($q);
				$dbo->execute();
				$mainframe->enqueueMessage(JText::translate('VBSEASONUPDATED'));
				//update session values
				$updforvcm['count'] = array_key_exists('count', $updforvcm) && !empty($updforvcm['count']) ? ($updforvcm['count'] + 1) : 1;
				if (array_key_exists('dfrom', $updforvcm) && !empty($updforvcm['dfrom'])) {
					$updforvcm['dfrom'] = $updforvcm['dfrom'] > $first ? $first : $updforvcm['dfrom'];
				} else {
					$updforvcm['dfrom'] = $first;
				}
				if (array_key_exists('dto', $updforvcm) && !empty($updforvcm['dto'])) {
					$updforvcm['dto'] = $updforvcm['dto'] < $second ? $second : $updforvcm['dto'];
				} else {
					$updforvcm['dto'] = $second;
				}
				if (array_key_exists('rooms', $updforvcm) && is_array($updforvcm['rooms'])) {
					foreach ($roomids as $rid) {
						if (!in_array($rid, $updforvcm['rooms'])) {
							$updforvcm['rooms'][] = $rid;
						}
					}
				} else {
					$updforvcm['rooms'] = $roomids;
				}
				if (array_key_exists('rplans', $updforvcm) && is_array($updforvcm['rplans'])) {
					foreach ($roomids as $rid) {
						if (array_key_exists($rid, $updforvcm['rplans'])) {
							$updforvcm['rplans'][$rid] = $updforvcm['rplans'][$rid] + $priceids;
						} else {
							$updforvcm['rplans'][$rid] = $priceids;
						}
					}
				} else {
					$updforvcm['rplans'] = array();
					foreach ($roomids as $rid) {
						$updforvcm['rplans'][$rid] = $priceids;
					}
				}
				$session->set('vbVcmRatesUpd', $updforvcm);
				//
				if ($stay) {
					$mainframe->redirect("index.php?option=com_vikbooking&task=editseason&cid[]=".$pwhere);
				} else {
					$mainframe->redirect("index.php?option=com_vikbooking&task=seasons");
				}
			} else {
				VikError::raiseWarning('', JText::translate('ERRINVDATEROOMSLOCSEASON').(count($double_records) > 0 ? ' ('.implode(', ', $double_records).')' : ''));
				$mainframe->redirect("index.php?option=com_vikbooking&task=editseason&cid[]=".$pwhere);
			}
		} else {
			$mainframe->redirect("index.php?option=com_vikbooking&task=editseason&cid[]=".$pwhere);
		}
	}

	public function createseason() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$this->do_createseason();
	}

	public function createseason_new() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$this->do_createseason(true);
	}

	private function do_createseason($andnew = false) {
		$mainframe = JFactory::getApplication();
		$pfrom = VikRequest::getString('from', '', 'request');
		$pto = VikRequest::getString('to', '', 'request');
		$ptype = VikRequest::getString('type', '', 'request');
		$pdiffcost = VikRequest::getFloat('diffcost', '', 'request');
		$pidrooms = VikRequest::getVar('idrooms', array(0));
		$pidprices = VikRequest::getVar('idprices', array(0));
		$pwdays = VikRequest::getVar('wdays', array());
		$pspname = VikRequest::getString('spname', '', 'request');
		$pcheckinincl = VikRequest::getString('checkinincl', '', 'request');
		$pcheckinincl = $pcheckinincl == 1 ? 1 : 0;
		$pyeartied = VikRequest::getString('yeartied', '', 'request');
		$pyeartied = $pyeartied == "1" ? 1 : 0;
		$tieyear = 0;
		$pval_pcent = VikRequest::getString('val_pcent', '', 'request');
		$pval_pcent = $pval_pcent == "1" ? 1 : 2;
		$proundmode = VikRequest::getString('roundmode', '', 'request');
		$proundmode = (!empty($proundmode) && in_array($proundmode, array('PHP_ROUND_HALF_UP', 'PHP_ROUND_HALF_DOWN')) ? $proundmode : '');
		$ppromo = VikRequest::getInt('promo', 0, 'request');
		$ppromodaysadv = VikRequest::getInt('promodaysadv', '', 'request');
		$ppromominlos = VikRequest::getInt('promominlos', '', 'request');
		$ppromotxt = VikRequest::getString('promotxt', '', 'request', VIKREQUEST_ALLOWHTML);
		$pnightsoverrides = VikRequest::getVar('nightsoverrides', array());
		$pvaluesoverrides = VikRequest::getVar('valuesoverrides', array());
		$pandmoreoverride = VikRequest::getVar('andmoreoverride', array());
		$padultsdiffchdisc = VikRequest::getVar('adultsdiffchdisc', array());
		$padultsdiffval = VikRequest::getVar('adultsdiffval', array());
		$padultsdiffvalpcent = VikRequest::getVar('adultsdiffvalpcent', array());
		$padultsdiffpernight = VikRequest::getVar('adultsdiffpernight', array());
		$ppromolastmind = VikRequest::getInt('promolastmind', 0, 'request');
		$ppromolastminh = VikRequest::getInt('promolastminh', 0, 'request');
		$promolastmin = ($ppromolastmind * 86400) + ($ppromolastminh * 3600);
		$ppromofinalprice = VikRequest::getInt('promofinalprice', 0, 'request');
		$ppromofinalprice = $ppromo ? $ppromofinalprice : 0;
		$pchannels = VikRequest::getVar('channels', array());
		$occupancy_ovr = array();
		$losverridestr = "";
		$dbo = JFactory::getDbo();
		$session = JFactory::getSession();
		$updforvcm = $session->get('vbVcmRatesUpd', '');
		$updforvcm = empty($updforvcm) || !is_array($updforvcm) ? array() : $updforvcm;
		// check null dates
		if ($dbo->getNullDate() == $pfrom) {
			$pfrom = '';
		}
		if ($dbo->getNullDate() == $pto) {
			$pto = '';
		}
		//
		if ((!empty($pfrom) && !empty($pto)) || count($pwdays) > 0) {
			$skipseason = false;
			if (empty($pfrom) || empty($pto)) {
				$skipseason = true;
			}
			$skipdays = false;
			$wdaystr = null;
			if (count($pwdays) == 0) {
				$skipdays = true;
			} else {
				$wdaystr = "";
				foreach ($pwdays as $wd) {
					$wdaystr .= $wd.';';
				}
			}
			$roomstr = "";
			$roomids = array();
			if (@count($pidrooms) > 0) {
				foreach ($pidrooms as $room) {
					if (empty($room)) {
						continue;
					}
					$roomstr .= "-".$room."-,";
					$roomids[] = (int)$room;
				}
			}
			$pricestr = "";
			$priceids = array();
			if (@count($pidprices) > 0) {
				foreach ($pidprices as $price) {
					if (empty($price)) {
						continue;
					}
					$pricestr .= "-".$price."-,";
					$priceids[] = (int)$price;
				}
			}
			$valid = true;
			$double_records = array();
			$sfrom = null;
			$sto = null;
			// value overrides
			if (count($pnightsoverrides) > 0 && count($pvaluesoverrides) > 0) {
				foreach ($pnightsoverrides as $ko => $no) {
					if (!empty($no) && strlen(trim($pvaluesoverrides[$ko])) > 0) {
						$infiniteclause = intval($pandmoreoverride[$ko]) == 1 ? '-i' : '';
						$losverridestr .= intval($no).$infiniteclause.':'.trim($pvaluesoverrides[$ko]).'_';
					}
				}
			}
			//
			if (!$skipseason) {
				$first = VikBooking::getDateTimestamp($pfrom, 0, 0);
				$second = VikBooking::getDateTimestamp($pto, 0, 0);
				if ($second > 0 && $second == $first) {
					$second += 86399;
				}
				if ($second > $first) {
					$baseone = getdate($first);
					$basets = mktime(0, 0, 0, 1, 1, $baseone['year']);
					$sfrom = $baseone[0] - $basets;
					$basetwo = getdate($second);
					$basets = mktime(0, 0, 0, 1, 1, $basetwo['year']);
					$sto = $basetwo[0] - $basets;
					//check leap year
					if ($baseone['year'] % 4 == 0 && ($baseone['year'] % 100 != 0 || $baseone['year'] % 400 == 0)) {
						$leapts = mktime(0, 0, 0, 2, 29, $baseone['year']);
						if ($baseone[0] > $leapts) {
							$sfrom -= 86400;
							/**
							 * To avoid issue with leap years and dates near Feb 29th, we only reduce the seconds if these were reduced
							 * for the from-date of the seasons. Doing it just for the to-date in 2019 for 2020 (leap) produced invalid results.
							 * 
							 * @since 	July 2nd 2019
							 */
							if ($basetwo['year'] % 4 == 0 && ($basetwo['year'] % 100 != 0 || $basetwo['year'] % 400 == 0)) {
								$leapts = mktime(0, 0, 0, 2, 29, $basetwo['year']);
								if ($basetwo[0] > $leapts) {
									$sto -= 86400;
								}
							}
						}
					}
					//end leap year
					//tied to the year
					if ($pyeartied == 1) {
						$tieyear = $baseone['year'];
					}
					//
					//Occupancy Override
					if (count($padultsdiffval) > 0) {
						foreach ($padultsdiffval as $rid => $valovr_arr) {
							if (!is_array($valovr_arr) || !is_array($padultsdiffchdisc[$rid]) || !is_array($padultsdiffvalpcent[$rid]) || !is_array($padultsdiffpernight[$rid])) {
								continue;
							}
							foreach ($valovr_arr as $occ => $valovr) {
								if (!(strlen($valovr) > 0) || !(strlen($padultsdiffchdisc[$rid][$occ]) > 0) || !(strlen($padultsdiffvalpcent[$rid][$occ]) > 0) || !(strlen($padultsdiffpernight[$rid][$occ]) > 0)) {
									continue;
								}
								if (!array_key_exists($rid, $occupancy_ovr)) {
									$occupancy_ovr[$rid] = array();
								}
								$occupancy_ovr[$rid][$occ] = array('chdisc' => (int)$padultsdiffchdisc[$rid][$occ], 'valpcent' => (int)$padultsdiffvalpcent[$rid][$occ], 'pernight' => (int)$padultsdiffpernight[$rid][$occ], 'value' => (float)$valovr);
							}
						}
					}
					//
					//check if seasons dates are valid
					//VikBooking 1.6, clause `to`>=".$dbo->quote($sfrom)" was changed to `to`>".$dbo->quote($sfrom) to avoid issues with rates for leap years when not tied to the year and entered the year before the leap
					$q = "SELECT `id`,`spname` FROM `#__vikbooking_seasons` WHERE `from`<=".$dbo->quote($sfrom)." AND `to`>".$dbo->quote($sfrom)." AND `idrooms`=".$dbo->quote($roomstr)."".(!$skipdays ? " AND `wdays`='".$wdaystr."'" : "").($skipdays ? " AND (`from` > 0 OR `to` > 0) AND `wdays`=''" : "").($pyeartied == 1 ? " AND `year`=".$tieyear : " AND `year` IS NULL")." AND `idprices`=".$dbo->quote($pricestr)." AND `promo`=".$ppromo." AND `losoverride`=".$dbo->quote($losverridestr)." AND `occupancy_ovr`".(count($occupancy_ovr) > 0 ? "=".$dbo->quote(json_encode($occupancy_ovr)) : " IS NULL").";";
					$dbo->setQuery($q);
					$dbo->execute();
					$totfirst = $dbo->getNumRows();
					if ($totfirst > 0) {
						$valid = false;
						$similar = $dbo->loadAssocList();
						foreach ($similar as $sim) {
							$double_records[] = $sim['spname'];
						}
					}
					$q = "SELECT `id`,`spname` FROM `#__vikbooking_seasons` WHERE `from`<=".$dbo->quote($sto)." AND `to`>=".$dbo->quote($sto)." AND `idrooms`=".$dbo->quote($roomstr)."".(!$skipdays ? " AND `wdays`='".$wdaystr."'" : "").($skipdays ? " AND (`from` > 0 OR `to` > 0) AND `wdays`=''" : "").($pyeartied == 1 ? " AND `year`=".$tieyear : " AND `year` IS NULL")." AND `idprices`=".$dbo->quote($pricestr)." AND `promo`=".$ppromo." AND `losoverride`=".$dbo->quote($losverridestr)." AND `occupancy_ovr`".(count($occupancy_ovr) > 0 ? "=".$dbo->quote(json_encode($occupancy_ovr)) : " IS NULL").";";
					$dbo->setQuery($q);
					$dbo->execute();
					$totsecond = $dbo->getNumRows();
					if ($totsecond > 0) {
						$valid = false;
						$similar = $dbo->loadAssocList();
						foreach ($similar as $sim) {
							$double_records[] = $sim['spname'];
						}
					}
					$q = "SELECT `id`,`spname` FROM `#__vikbooking_seasons` WHERE `from`>=".$dbo->quote($sfrom)." AND `from`<=".$dbo->quote($sto)." AND `to`>=".$dbo->quote($sfrom)." AND `to`<=".$dbo->quote($sto)." AND `idrooms`=".$dbo->quote($roomstr)."".(!$skipdays ? " AND `wdays`='".$wdaystr."'" : "").($skipdays ? " AND (`from` > 0 OR `to` > 0) AND `wdays`=''" : "").($pyeartied == 1 ? " AND `year`=".$tieyear : " AND `year` IS NULL")." AND `idprices`=".$dbo->quote($pricestr)." AND `promo`=".$ppromo." AND `losoverride`=".$dbo->quote($losverridestr)." AND `occupancy_ovr`".(count($occupancy_ovr) > 0 ? "=".$dbo->quote(json_encode($occupancy_ovr)) : " IS NULL").";";
					$dbo->setQuery($q);
					$dbo->execute();
					$totthird = $dbo->getNumRows();
					if ($totthird > 0) {
						$valid = false;
						$similar = $dbo->loadAssocList();
						foreach ($similar as $sim) {
							$double_records[] = $sim['spname'];
						}
					}
					//
				} else {
					VikError::raiseWarning('', JText::translate('ERRINVDATESEASON'));
					$mainframe->redirect("index.php?option=com_vikbooking&task=newseason");
					exit;
				}
			}
			if ($valid || $ppromo === 1) {
				$q = "INSERT INTO `#__vikbooking_seasons` (`type`,`from`,`to`,`diffcost`,`idrooms`,`spname`,`wdays`,`checkinincl`,`val_pcent`,`losoverride`,`roundmode`,`year`,`idprices`,`promo`,`promodaysadv`,`promotxt`,`promominlos`,`occupancy_ovr`,`promolastmin`,`promofinalprice`) VALUES('".($ptype == "1" ? "1" : "2")."', ".$dbo->quote($sfrom).", ".$dbo->quote($sto).", ".$dbo->quote($pdiffcost).", ".$dbo->quote($roomstr).", ".$dbo->quote($pspname).", ".$dbo->quote($wdaystr).", '".$pcheckinincl."', '".$pval_pcent."', ".$dbo->quote($losverridestr).", ".(!empty($proundmode) ? "'".$proundmode."'" : "NULL").", ".($pyeartied == 1 ? $tieyear : "NULL").", ".$dbo->quote($pricestr).", ".($ppromo == 1 ? '1' : '0').", ".(!empty($ppromodaysadv) ? $ppromodaysadv : "NULL").", ".$dbo->quote($ppromotxt).", ".(!empty($ppromominlos) ? $ppromominlos : "0").", ".(count($occupancy_ovr) ? $dbo->quote(json_encode($occupancy_ovr)) : "NULL").", ".(int)$promolastmin.", {$ppromofinalprice});";
				$dbo->setQuery($q);
				$dbo->execute();
				$mainframe->enqueueMessage(JText::translate('VBSEASONSAVED'));
				//update session values
				$updforvcm['count'] = array_key_exists('count', $updforvcm) && !empty($updforvcm['count']) ? ($updforvcm['count'] + 1) : 1;
				if (array_key_exists('dfrom', $updforvcm) && !empty($updforvcm['dfrom'])) {
					$updforvcm['dfrom'] = $updforvcm['dfrom'] > $first ? $first : $updforvcm['dfrom'];
				} else {
					$updforvcm['dfrom'] = $first;
				}
				if (array_key_exists('dto', $updforvcm) && !empty($updforvcm['dto'])) {
					$updforvcm['dto'] = $updforvcm['dto'] < $second ? $second : $updforvcm['dto'];
				} else {
					$updforvcm['dto'] = $second;
				}
				if (array_key_exists('rooms', $updforvcm) && is_array($updforvcm['rooms'])) {
					foreach ($roomids as $rid) {
						if (!in_array($rid, $updforvcm['rooms'])) {
							$updforvcm['rooms'][] = $rid;
						}
					}
				} else {
					$updforvcm['rooms'] = $roomids;
				}
				if (array_key_exists('rplans', $updforvcm) && is_array($updforvcm['rplans'])) {
					foreach ($roomids as $rid) {
						if (array_key_exists($rid, $updforvcm['rplans'])) {
							$updforvcm['rplans'][$rid] = $updforvcm['rplans'][$rid] + $priceids;
						} else {
							$updforvcm['rplans'][$rid] = $priceids;
						}
					}
				} else {
					$updforvcm['rplans'] = array();
					foreach ($roomids as $rid) {
						$updforvcm['rplans'][$rid] = $priceids;
					}
				}
				if (!$ppromo) {
					$session->set('vbVcmRatesUpd', $updforvcm);
				}
				//
				
				/**
				 * Create the promotion also on the selected channels
				 * 
				 * @since 	1.3.0
				 */
				if ($ppromo && count($pchannels)) {
					foreach ($pchannels as $channel_key) {
						$promo_obj = VikBooking::getPromotionHandlers($channel_key);
						if (!is_object($promo_obj)) {
							continue;
						}
						$ch_result = $promo_obj->createPromotion(array(), 'new');
						if (!$ch_result) {
							VikError::raiseWarning('', $promo_obj->getName() . ': ' . $promo_obj->getError());
						} else {
							$resp = $promo_obj->getResponse();
							$mainframe->enqueueMessage($promo_obj->getName() . ': ' . JText::translate('VBOCHPROMOSUCCESS') . (!empty($resp) ? ' (' . str_replace('e4j.ok.', '', $resp) . ')' : ''));
							// in case of success, unset the current session values in VCM
							$session->set('vcmBPromo', '');
						}
					}
				}
				//

				$mainframe->redirect("index.php?option=com_vikbooking&task=".($andnew ? 'newseason' : 'seasons'));
			} else {
				VikError::raiseWarning('', JText::translate('ERRINVDATEROOMSLOCSEASON').(count($double_records) > 0 ? ' ('.implode(', ', $double_records).')' : ''));
				$mainframe->redirect("index.php?option=com_vikbooking&task=newseason");
			}
		} else {
			$mainframe->redirect("index.php?option=com_vikbooking&task=newseason");
		}
	}

	public function removeseasons () {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array(0));
		$pidroom = VikRequest::getInt('idroom', '', 'request');
		$pwhere = VikRequest::getInt('where', '', 'request');
		if (!empty($pwhere)) {
			$ids[] = $pwhere;
		}
		$tot_removed = 0;
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				if (empty($d)) {
					continue;
				}
				$q = "DELETE FROM `#__vikbooking_seasons` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
				$tot_removed++;
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->enqueueMessage(JText::sprintf('VBRECORDSREMOVED', $tot_removed));
		$mainframe->redirect("index.php?option=com_vikbooking&task=seasons".(!empty($pidroom) ? '&idroom='.$pidroom : ''));
	}

	public function updatecustomer() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$this->do_updatecustomer();
	}

	public function updatecustomerstay() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$this->do_updatecustomer(true);
	}

	private function do_updatecustomer($stay = false) {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$pfirst_name = VikRequest::getString('first_name', '', 'request');
		$plast_name = VikRequest::getString('last_name', '', 'request');
		$pcompany = VikRequest::getString('company', '', 'request');
		$pvat = VikRequest::getString('vat', '', 'request');
		$pemail = VikRequest::getString('email', '', 'request');
		$pphone = VikRequest::getString('phone', '', 'request');
		$pcountry = VikRequest::getString('country', '', 'request');
		$ppin = VikRequest::getString('pin', '', 'request');
		$pujid = VikRequest::getInt('ujid', '', 'request');
		$paddress = VikRequest::getString('address', '', 'request');
		$pcity = VikRequest::getString('city', '', 'request');
		$pzip = VikRequest::getString('zip', '', 'request');
		$pfisccode = VikRequest::getString('fisccode', '', 'request');
		$ppec = VikRequest::getString('pec', '', 'request');
		$precipcode = VikRequest::getString('recipcode', '', 'request');
		$pgender = VikRequest::getString('gender', '', 'request');
		$pgender = in_array($pgender, array('F', 'M')) ? $pgender : '';
		$pbdate = VikRequest::getString('bdate', '', 'request');
		$ppbirth = VikRequest::getString('pbirth', '', 'request');
		$pdoctype = VikRequest::getString('doctype', '', 'request');
		$pdocnum = VikRequest::getString('docnum', '', 'request');
		$pnotes = VikRequest::getString('notes', '', 'request');
		$pscandocimg = VikRequest::getString('scandocimg', '', 'request');
		$pischannel = VikRequest::getInt('ischannel', '', 'request');
		$pcommission = VikRequest::getFloat('commission', '', 'request');
		$pcalccmmon = VikRequest::getInt('calccmmon', '', 'request');
		$papplycmmon = VikRequest::getInt('applycmmon', '', 'request');
		$pchname = VikRequest::getString('chname', '', 'request');
		$pchcolor = VikRequest::getString('chcolor', '', 'request');
		$pwhere = VikRequest::getInt('where', '', 'request');
		$ptmpl = VikRequest::getString('tmpl', '', 'request');
		$pcheckin = VikRequest::getInt('checkin', '', 'request');
		$pbid = VikRequest::getInt('bid', '', 'request');
		$pgoto = VikRequest::getString('goto', '', 'request', VIKREQUEST_ALLOWRAW);
		if (!empty($pwhere) && !empty($pfirst_name) && !empty($plast_name) && !empty($pemail)) {
			$q = "SELECT * FROM `#__vikbooking_customers` WHERE `id`=".(int)$pwhere." LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$customer = $dbo->loadAssoc();
			} else {
				$mainframe->redirect("index.php?option=com_vikbooking&task=customers");
				exit;
			}
			/**
			 * Existing customers are recognized by equal first name, last name and email address.
			 * 
			 * @since 	1.3.0
			 */
			$q = "SELECT * FROM `#__vikbooking_customers` WHERE `first_name`=".$dbo->quote($pfirst_name)." AND `last_name`=".$dbo->quote($plast_name)." AND `email`=".$dbo->quote($pemail)." AND `id`!=".(int)$pwhere." LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 0) {
				$cpin = VikBooking::getCPinIstance();
				if (empty($ppin)) {
					$ppin = $customer['pin'];
				} elseif ($cpin->pinExists($ppin, $customer['pin'])) {
					$ppin = $cpin->generateUniquePin();
				}
				//file upload
				$pimg = VikRequest::getVar('docimg', null, 'files', 'array');
				jimport('joomla.filesystem.file');
				$gimg = "";
				if (isset($pimg) && strlen(trim($pimg['name']))) {
					$filename = JFile::makeSafe(rand(100, 9999).str_replace(" ", "_", strtolower($pimg['name'])));
					$src = $pimg['tmp_name'];
					$dest = VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'idscans'.DIRECTORY_SEPARATOR;
					$j = "";
					if (file_exists($dest.$filename)) {
						$j = rand(171, 1717);
						while (file_exists($dest.$j.$filename)) {
							$j++;
						}
					}
					$finaldest = $dest.$j.$filename;
					$check = getimagesize($pimg['tmp_name']);
					if (($check[2] & imagetypes()) || preg_match("/application\/(zip|pdf)$/", $pimg['type'])) {
						if (VikBooking::uploadFile($src, $finaldest)) {
							$gimg = $j.$filename;
						} else {
							VikError::raiseWarning('', 'Error while uploading image');
						}
					} else {
						VikError::raiseWarning('', 'Uploaded file is not an Image');
					}
				} elseif (!empty($pscandocimg)) {
					$gimg = $pscandocimg;
				}
				//
				$pischannel = $pischannel > 0 ? 1 : 0;
				$pcalccmmon = $pcalccmmon > 0 ? 1 : 0;
				$papplycmmon = $papplycmmon > 0 ? 1 : 0;
				$pchname = str_replace(' ', '', trim($pchname));
				$pchname = strlen($pchname) <= 0 && $pischannel > 0 ? str_replace(' ', '', trim($pfirst_name.' '.$plast_name)) : $pchname;
				$chparams = array(
					'commission' => ($pcommission > 0.00 ? $pcommission : 0),
					'calccmmon' => $pcalccmmon,
					'applycmmon' => $papplycmmon,
					'chcolor' => $pchcolor,
					'chname' => $pchname
				);

				// update customer object
				$new_customer = new stdClass;
				$new_customer->id = (int)$pwhere;
				$new_customer->first_name = $pfirst_name;
				$new_customer->last_name = $plast_name;
				$new_customer->email = $pemail;
				$new_customer->phone = $pphone;
				$new_customer->country = $pcountry;
				$new_customer->pin = $ppin;
				$new_customer->ujid = $pujid;
				$new_customer->address = $paddress;
				$new_customer->city = $pcity;
				$new_customer->zip = $pzip;
				$new_customer->doctype = $pdoctype;
				$new_customer->docnum = $pdocnum;
				if (!empty($gimg)) {
					$new_customer->docimg = $gimg;
				}
				$new_customer->notes = $pnotes;
				$new_customer->ischannel = $pischannel;
				$new_customer->chdata = json_encode($chparams);
				$new_customer->company = $pcompany;
				$new_customer->vat = $pvat;
				$new_customer->gender = $pgender;
				$new_customer->bdate = $pbdate;
				$new_customer->pbirth = $ppbirth;
				$new_customer->fisccode = $pfisccode;
				$new_customer->pec = $ppec;
				$new_customer->recipcode = $precipcode;
				/**
				 * We need to update the previous information stored through
				 * the custom fields when making a reservation for/by this client.
				 * 
				 * @since 	1.13
				 */
				$skip_prev_fields = array(
					'id',
					'ujid',
					'docimg',
					'ischannel',
					'chdata',
					'notes',
				);
				if (!empty($customer['cfields'])) {
					$custf_info = json_decode($customer['cfields'], true);
					foreach ($new_customer as $fname => $fnewval) {
						if (!isset($customer[$fname]) || in_array($fname, $skip_prev_fields)) {
							continue;
						}
						// seek for old value in custom fields submitted
						foreach ($custf_info as $k => $v) {
							if (!empty($customer[$fname]) && $v == $customer[$fname]) {
								// field found, replace it with the new value
								$custf_info[$k] = $fnewval;
							}
						}
					}
					// update record on db
					$new_customer->cfields = json_encode($custf_info);
				}
				//
				
				$dbo->updateObject('#__vikbooking_customers', $new_customer, 'id');

				$cpin->pluginCustomerSync($pwhere, 'update');
				//Update all the bookings affected by this Customer ID as a sales channel
				$source_name = 'customer'.$pwhere.'_'.$pchname;
				if ($pischannel > 0) {
					$oid_clause = '';
					if ($customer['ischannel'] < 1) {
						//Was not a sales channel but now it is, so update all his bookings
						$q = "SELECT `idorder` FROM `#__vikbooking_customers_orders` WHERE `idcustomer`=".$customer['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() > 0) {
							$all_bids = $dbo->loadAssocList();
							$bids = array();
							foreach ($all_bids as $bid) {
								if (!in_array($bid['idorder'], $bids)) {
									$bids[] = $bid['idorder'];
								}
							}
							$oid_clause = " OR `id` IN (".implode(',', $bids).")";
						}
					}
					$q = "UPDATE `#__vikbooking_orders` SET `channel`=".$dbo->quote($source_name)." WHERE `channel` LIKE 'customer".$pwhere."%'".$oid_clause.";";
				} else {
					$q = "UPDATE `#__vikbooking_orders` SET `channel`=NULL,`cmms`=NULL WHERE `channel` LIKE 'customer".$pwhere."%';";
				}
				$dbo->setQuery($q);
				$dbo->execute();
				//
				$mainframe->enqueueMessage(JText::translate('VBCUSTOMERSAVED'));
			} else {
				//email already exists
				$ex_customer = $dbo->loadAssoc();
				//check if coming from the Check-in view or not
				if (!empty($pcheckin) && !empty($pbid)) {
					VikError::raiseWarning('', JText::translate('VBERRCUSTOMEREMAILEXISTS').' ('.$ex_customer['first_name'].' '.$ex_customer['last_name'].')');
					/**
					 * @wponly - this task is executed via Ajax for the Modal forms listener. We must redirect to the booking details page and let the user restart the procedure
					 */
					$mainframe->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=".$pbid);
					//
					exit;
				} elseif (!empty($pgoto)) {
					// check if coming from a specific task
					VikError::raiseWarning('', JText::translate('VBERRCUSTOMEREMAILEXISTS').' ('.$ex_customer['first_name'].' '.$ex_customer['last_name'].')');
					$mainframe->redirect(base64_decode($pgoto));
					exit;
				} else {
					VikError::raiseWarning('', JText::translate('VBERRCUSTOMEREMAILEXISTS').'<br/><a href="index.php?option=com_vikbooking&task=editcustomer&cid[]='.$ex_customer['id'].'" target="_blank">'.$ex_customer['first_name'].' '.$ex_customer['last_name'].'</a>');
					$mainframe->redirect("index.php?option=com_vikbooking&task=editcustomer&cid[]=".$pwhere);
					exit;
				}
			}
		}
		//check if coming from the Check-in view
		if (!empty($pcheckin) && !empty($pbid)) {
			/**
			 * @wponly - this task is executed via Ajax for the Modal forms listener. We must redirect to the booking details page and let the user restart the procedure
			 */
			$mainframe->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=".$pbid);
			//
			exit;
		}
		// check if coming from a specific task
		if (!empty($pgoto)) {
			$mainframe->redirect(base64_decode($pgoto));
			exit;
		}
		//
		if ($stay) {
			$mainframe->redirect("index.php?option=com_vikbooking&task=editcustomer&cid[]=".$pwhere);
		} else {
			$mainframe->redirect("index.php?option=com_vikbooking&task=customers");
		}
	}

	public function savecustomer() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$pfirst_name = VikRequest::getString('first_name', '', 'request');
		$plast_name = VikRequest::getString('last_name', '', 'request');
		$pcompany = VikRequest::getString('company', '', 'request');
		$pvat = VikRequest::getString('vat', '', 'request');
		$pemail = VikRequest::getString('email', '', 'request');
		$pphone = VikRequest::getString('phone', '', 'request');
		$pcountry = VikRequest::getString('country', '', 'request');
		$ppin = VikRequest::getString('pin', '', 'request');
		$pujid = VikRequest::getInt('ujid', '', 'request');
		$paddress = VikRequest::getString('address', '', 'request');
		$pcity = VikRequest::getString('city', '', 'request');
		$pzip = VikRequest::getString('zip', '', 'request');
		$pfisccode = VikRequest::getString('fisccode', '', 'request');
		$ppec = VikRequest::getString('pec', '', 'request');
		$precipcode = VikRequest::getString('recipcode', '', 'request');
		$pgender = VikRequest::getString('gender', '', 'request');
		$pgender = in_array($pgender, array('F', 'M')) ? $pgender : '';
		$pbdate = VikRequest::getString('bdate', '', 'request');
		$ppbirth = VikRequest::getString('pbirth', '', 'request');
		$pdoctype = VikRequest::getString('doctype', '', 'request');
		$pdocnum = VikRequest::getString('docnum', '', 'request');
		$pnotes = VikRequest::getString('notes', '', 'request');
		$pscandocimg = VikRequest::getString('scandocimg', '', 'request');
		$pischannel = VikRequest::getInt('ischannel', '', 'request');
		$pcommission = VikRequest::getFloat('commission', '', 'request');
		$pcalccmmon = VikRequest::getInt('calccmmon', '', 'request');
		$papplycmmon = VikRequest::getInt('applycmmon', '', 'request');
		$pchname = VikRequest::getString('chname', '', 'request');
		$pchcolor = VikRequest::getString('chcolor', '', 'request');
		$ptmpl = VikRequest::getString('tmpl', '', 'request');
		$pcheckin = VikRequest::getInt('checkin', '', 'request');
		$pgoto = VikRequest::getString('goto', '', 'request', VIKREQUEST_ALLOWRAW);
		$pbid = VikRequest::getInt('bid', '', 'request');
		if (!empty($pfirst_name) && !empty($plast_name) && !empty($pemail)) {
			$cpin = VikBooking::getCPinIstance();
			/**
			 * Existing customers are recognized by equal first name, last name and email address.
			 * 
			 * @since 	1.3.0
			 */
			$q = "SELECT * FROM `#__vikbooking_customers` WHERE `first_name`=".$dbo->quote($pfirst_name)." AND `last_name`=".$dbo->quote($plast_name)." AND `email`=".$dbo->quote($pemail)." LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 0) {
				if (empty($ppin)) {
					$ppin = $cpin->generateUniquePin();
				} elseif ($cpin->pinExists($ppin)) {
					$ppin = $cpin->generateUniquePin();
				}
				//file upload
				$pimg = VikRequest::getVar('docimg', null, 'files', 'array');
				jimport('joomla.filesystem.file');
				$gimg = "";
				if (isset($pimg) && strlen(trim($pimg['name']))) {
					$filename = JFile::makeSafe(rand(100, 9999).str_replace(" ", "_", strtolower($pimg['name'])));
					$src = $pimg['tmp_name'];
					$dest = VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'idscans'.DIRECTORY_SEPARATOR;
					$j = "";
					if (file_exists($dest.$filename)) {
						$j = rand(171, 1717);
						while (file_exists($dest.$j.$filename)) {
							$j++;
						}
					}
					$finaldest = $dest.$j.$filename;
					$check = getimagesize($pimg['tmp_name']);
					if (($check[2] & imagetypes()) || preg_match("/application\/(zip|pdf)$/", $pimg['type'])) {
						if (VikBooking::uploadFile($src, $finaldest)) {
							$gimg = $j.$filename;
						} else {
							VikError::raiseWarning('', 'Error while uploading image');
						}
					} else {
						VikError::raiseWarning('', 'Uploaded file is not an Image');
					}
				} elseif (!empty($pscandocimg)) {
					$gimg = $pscandocimg;
				}
				//
				$pischannel = $pischannel > 0 ? 1 : 0;
				$pcalccmmon = $pcalccmmon > 0 ? 1 : 0;
				$papplycmmon = $papplycmmon > 0 ? 1 : 0;
				$pchname = str_replace(' ', '', trim($pchname));
				$pchname = strlen($pchname) <= 0 && $pischannel > 0 ? str_replace(' ', '', trim($pfirst_name.' '.$plast_name)) : $pchname;
				$chparams = array(
					'commission' => ($pcommission > 0.00 ? $pcommission : 0),
					'calccmmon' => $pcalccmmon,
					'applycmmon' => $papplycmmon,
					'chcolor' => $pchcolor,
					'chname' => $pchname
				);
				$q = "INSERT INTO `#__vikbooking_customers` (`first_name`,`last_name`,`email`,`phone`,`country`,`pin`,`ujid`,`address`,`city`,`zip`,`doctype`,`docnum`,`docimg`,`notes`,`ischannel`,`chdata`,`company`,`vat`,`gender`,`bdate`,`pbirth`,`fisccode`,`pec`,`recipcode`) VALUES(".$dbo->quote($pfirst_name).", ".$dbo->quote($plast_name).", ".$dbo->quote($pemail).", ".$dbo->quote($pphone).", ".$dbo->quote($pcountry).", ".$dbo->quote($ppin).", ".$dbo->quote($pujid).", ".$dbo->quote($paddress).", ".$dbo->quote($pcity).", ".$dbo->quote($pzip).", ".$dbo->quote($pdoctype).", ".$dbo->quote($pdocnum).", ".$dbo->quote($gimg).", ".$dbo->quote($pnotes).", ".$pischannel.", ".$dbo->quote(json_encode($chparams)).", ".$dbo->quote($pcompany).", ".$dbo->quote($pvat).", ".$dbo->quote($pgender).", ".$dbo->quote($pbdate).", ".$dbo->quote($ppbirth).", ".$dbo->quote($pfisccode).", ".$dbo->quote($ppec).", ".$dbo->quote($precipcode).");";
				$dbo->setQuery($q);
				$dbo->execute();
				$lid = $dbo->insertid();
				$cpin->pluginCustomerSync($lid, 'insert');
				if (!empty($lid)) {
					$mainframe->enqueueMessage(JText::translate('VBCUSTOMERSAVED'));
					//check if coming from the Check-in view
					if (!empty($pcheckin) && !empty($pbid)) {
						$cpin->setNewPin($ppin);
						$cpin->setNewCustomerId($lid);
						$cpin->saveCustomerBooking($pbid);
						/**
						 * @wponly - this task is executed via Ajax for the Modal forms listener. We must redirect to the booking details page and let the user restart the procedure
						 */
						$mainframe->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=".$pbid);
						//
						exit;
					}
					// check if coming from a specific task
					if (!empty($pgoto) && !empty($pbid)) {
						$cpin->setNewPin($ppin);
						$cpin->setNewCustomerId($lid);
						$cpin->saveCustomerBooking($pbid);
						$mainframe->redirect(base64_decode($pgoto));
						exit;
					}
				}
			} else {
				//email already exists
				$ex_customer = $dbo->loadAssoc();
				//check if coming from the Check-in view or not
				if (!empty($pcheckin) && !empty($pbid)) {
					$cpin->setNewPin($ex_customer['pin']);
					$cpin->setNewCustomerId($ex_customer['id']);
					$cpin->saveCustomerBooking($pbid);
					VikError::raiseWarning('', JText::translate('VBERRCUSTOMEREMAILEXISTS').' ('.$ex_customer['first_name'].' '.$ex_customer['last_name'].')');
					/**
					 * @wponly - this task is executed via Ajax for the Modal forms listener. We must redirect to the booking details page and let the user restart the procedure
					 */
					$mainframe->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=".$pbid);
					//
					exit;
				} elseif (!empty($pgoto) && !empty($pbid)) {
					// check if coming from a specific task
					$cpin->setNewPin($ex_customer['pin']);
					$cpin->setNewCustomerId($ex_customer['id']);
					$cpin->saveCustomerBooking($pbid);
					VikError::raiseWarning('', JText::translate('VBERRCUSTOMEREMAILEXISTS').' ('.$ex_customer['first_name'].' '.$ex_customer['last_name'].')');
					$mainframe->redirect(base64_decode($pgoto));
					exit;
				} else {
					VikError::raiseWarning('', JText::translate('VBERRCUSTOMEREMAILEXISTS').'<br/><a href="index.php?option=com_vikbooking&task=editcustomer&cid[]='.$ex_customer['id'].'" target="_blank">'.$ex_customer['first_name'].' '.$ex_customer['last_name'].'</a>');
				}
			}
		}
		$mainframe->redirect("index.php?option=com_vikbooking&task=customers");
	}

	public function customers() {
		VikBookingHelper::printHeader("22");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'customers'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function newcustomer() {
		VikBookingHelper::printHeader("22");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecustomer'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function editcustomer() {
		VikBookingHelper::printHeader("22");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecustomer'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function removecustomers() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			$cpin = VikBooking::getCPinIstance();
			foreach ($ids as $d) {
				$cpin->pluginCustomerSync($d, 'delete');
				$q = "DELETE FROM `#__vikbooking_customers` WHERE `id`=".(int)$d.";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=customers");
	}

	public function restrictions() {
		VikBookingHelper::printHeader("restrictions");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'restrictions'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function newrestriction() {
		VikBookingHelper::printHeader("restrictions");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managerestriction'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function editrestriction() {
		VikBookingHelper::printHeader("restrictions");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managerestriction'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function createrestriction() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$mainframe = JFactory::getApplication();
		$updforvcm = $session->get('vbVcmRatesUpd', '');
		$updforvcm = empty($updforvcm) || !is_array($updforvcm) ? array() : $updforvcm;
		$pname = VikRequest::getString('name', '', 'request');
		$pmonth = VikRequest::getInt('month', '', 'request');
		$pmonth = empty($pmonth) ? 0 : $pmonth;
		$pname = empty($pname) ? 'Restriction '.$pmonth : $pname;
		$pdfrom = VikRequest::getString('dfrom', '', 'request');
		$pdto = VikRequest::getString('dto', '', 'request');
		$pwday = VikRequest::getString('wday', '', 'request');
		$pwdaytwo = VikRequest::getString('wdaytwo', '', 'request');
		$pwdaytwo = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday == $pwdaytwo ? '' : $pwdaytwo;
		$pcomboa = VikRequest::getString('comboa', '', 'request');
		$pcomboa = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo ? $pcomboa : '';
		$pcombob = VikRequest::getString('combob', '', 'request');
		$pcombob = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo ? $pcombob : '';
		$pcomboc = VikRequest::getString('comboc', '', 'request');
		$pcomboc = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo ? $pcomboc : '';
		$pcombod = VikRequest::getString('combod', '', 'request');
		$pcombod = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo ? $pcombod : '';
		$combostr = '';
		$combostr .= strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo && !empty($pcomboa) ? $pcomboa.':' : ':';
		$combostr .= strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo && !empty($pcombob) ? $pcombob.':' : ':';
		$combostr .= strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo && !empty($pcomboc) ? $pcomboc.':' : ':';
		$combostr .= strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo && !empty($pcombod) ? $pcombod : '';
		$pminlos = VikRequest::getInt('minlos', '', 'request');
		$pminlos = $pminlos < 1 ? 1 : $pminlos;
		$pmaxlos = VikRequest::getInt('maxlos', '', 'request');
		$pmaxlos = empty($pmaxlos) ? 0 : $pmaxlos;
		$pmultiplyminlos = VikRequest::getString('multiplyminlos', '', 'request');
		$pmultiplyminlos = empty($pmultiplyminlos) ? 0 : 1;
		$pallrooms = VikRequest::getString('allrooms', '', 'request');
		$pallrooms = $pallrooms == "1" ? 1 : 0;
		$pidrooms = VikRequest::getVar('idrooms', array(0));
		$ridr = '';
		$roomidsforsess = array();
		if (!empty($pidrooms) && @count($pidrooms) && $pallrooms == 0) {
			foreach ($pidrooms as $idr) {
				if (empty($idr)) {
					continue;
				}
				$ridr .= '-'.$idr.'-;';
				$roomidsforsess[] = (int)$idr;
			}
		} elseif ($pallrooms > 0) {
			$q = "SELECT `id` FROM `#__vikbooking_rooms`;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$fetchids = $dbo->loadAssocList();
				foreach ($fetchids as $fetchid) {
					$roomidsforsess[] = (int)$fetchid['id'];
				}
			}
		}
		$pcta = VikRequest::getInt('cta', '', 'request');
		$pctd = VikRequest::getInt('ctd', '', 'request');
		$pctad = VikRequest::getVar('ctad', array());
		$pctdd = VikRequest::getVar('ctdd', array());
		if ($pminlos == 1 && strlen($pwday) == 0 && empty($pctad) && empty($pctdd) && $pmaxlos < 1) {
			// VBO 1.11 - we now allow restrictions with just 1 night of stay
			// VikError::raiseWarning('', JText::translate('VBUSELESSRESTRICTION'));
			// $mainframe->redirect("index.php?option=com_vikbooking&task=newrestriction");
			// exit;
		}

		//check if there are restrictions for this month
		if ($pmonth > 0) {
			$q = "SELECT `id` FROM `#__vikbooking_restrictions` WHERE `month`='".$pmonth."';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				VikError::raiseWarning('', JText::translate('VBRESTRICTIONMONTHEXISTS'));
				$mainframe->redirect("index.php?option=com_vikbooking&task=newrestriction");
				exit;
			}
			$pdfrom = 0;
			$pdto = 0;
		} else {
			//dates range
			if (empty($pdfrom) || empty($pdto)) {
				VikError::raiseWarning('', JText::translate('VBRESTRICTIONERRDRANGE'));
				$mainframe->redirect("index.php?option=com_vikbooking&task=newrestriction");
				exit;
			} else {
				$housto = $pdfrom == $pdto ? 23 : 0;
				$minsto = $pdfrom == $pdto ? 59 : 0;
				$secsto = $pdfrom == $pdto ? 59 : 0;
				$pdfrom = VikBooking::getDateTimestamp($pdfrom, 0, 0);
				$pdto = VikBooking::getDateTimestamp($pdto, $housto, $minsto, $secsto);
			}
			if ($pdfrom > $pdto) {
				// invalid dates in the past
				VikError::raiseWarning('', JText::translate('VBRESTRICTIONERRDRANGE'));
				$mainframe->redirect("index.php?option=com_vikbooking&task=newrestriction");
				exit;
			}
		}
		//CTA and CTD
		$setcta = array();
		$setctd = array();
		if ($pcta > 0 && count($pctad) > 0) {
			foreach ($pctad as $ctwd) {
				if (strlen($ctwd)) {
					$setcta[] = '-'.(int)$ctwd.'-';
				}
			}
		}
		if ($pctd > 0 && count($pctdd) > 0) {
			foreach ($pctdd as $ctwd) {
				if (strlen($ctwd)) {
					$setctd[] = '-'.(int)$ctwd.'-';
				}
			}
		}
		//
		//update session values
		if (!($pdfrom > 0)) {
			$attemptyear = (int)date('Y');
			$attemptfrom = mktime(0, 0, 0, $pmonth, 1, $attemptyear);
			if ($attemptfrom < time()) {
				$attemptyear++;
				$attemptfrom = mktime(0, 0, 0, $pmonth, 1, $attemptyear);
			}
			$attemptto = mktime(0, 0, 0, $pmonth, date('t', $attemptfrom), $attemptyear);
		} else {
			$attemptfrom = $pdfrom;
			$attemptto = $pdto;
		}
		$updforvcm['count'] = array_key_exists('count', $updforvcm) && !empty($updforvcm['count']) ? ($updforvcm['count'] + 1) : 1;
		if (array_key_exists('dfrom', $updforvcm) && !empty($updforvcm['dfrom'])) {
			$updforvcm['dfrom'] = $updforvcm['dfrom'] > $attemptfrom ? $attemptfrom : $updforvcm['dfrom'];
		} else {
			$updforvcm['dfrom'] = $attemptfrom;
		}
		if (array_key_exists('dto', $updforvcm) && !empty($updforvcm['dto'])) {
			$updforvcm['dto'] = $updforvcm['dto'] < $attemptto ? $attemptto : $updforvcm['dto'];
		} else {
			$updforvcm['dto'] = $attemptto;
		}
		if (array_key_exists('rooms', $updforvcm) && is_array($updforvcm['rooms'])) {
			foreach ($roomidsforsess as $rid) {
				if (!in_array($rid, $updforvcm['rooms'])) {
					$updforvcm['rooms'][] = $rid;
				}
			}
		} else {
			$updforvcm['rooms'] = $roomidsforsess;
		}
		if (!array_key_exists('rplans', $updforvcm) || !is_array($updforvcm['rplans'])) {
			$updforvcm['rplans'] = array();
		}
		$session->set('vbVcmRatesUpd', $updforvcm);
		//
		$q = "INSERT INTO `#__vikbooking_restrictions` (`name`,`month`,`wday`,`minlos`,`multiplyminlos`,`maxlos`,`dfrom`,`dto`,`wdaytwo`,`wdaycombo`,`allrooms`,`idrooms`,`ctad`,`ctdd`) VALUES(".$dbo->quote($pname).", '".$pmonth."', ".(strlen($pwday) > 0 ? "'".$pwday."'" : "NULL").", '".$pminlos."', '".$pmultiplyminlos."', '".$pmaxlos."', ".$pdfrom.", ".$pdto.", ".(strlen($pwday) > 0 && strlen($pwdaytwo) > 0 ? intval($pwdaytwo) : "NULL").", ".(strlen($combostr) > 0 ? $dbo->quote($combostr) : "NULL").", ".$pallrooms.", ".(strlen($ridr) > 0 ? $dbo->quote($ridr) : "NULL").", ".(count($setcta) > 0 ? $dbo->quote(implode(',', $setcta)) : "NULL").", ".(count($setctd) > 0 ? $dbo->quote(implode(',', $setctd)) : "NULL").");";
		$dbo->setQuery($q);
		$dbo->execute();
		$lid = $dbo->insertid();
		if (!empty($lid)) {
			/**
			 * Repeat restriction on the selected week days until the limit
			 * 
			 * @since 	1.13
			 */
			$prepeat = VikRequest::getInt('repeat', 0, 'request');
			$prepeatuntil = VikRequest::getString('repeatuntil', '', 'request');
			if ($prepeat > 0 && !empty($prepeatuntil) && $pdfrom > 0 && $pdto > 0) {
				$repeat_intervals = array();
				$start = getdate($pdfrom);
				$end = getdate($pdto);
				$wdays = array();
				while ($start[0] <= $end[0]) {
					// push requested week day
					array_push($wdays, $start['wday']);
					// next day
					$start = getdate(mktime($start['hours'], $start['minutes'], $start['seconds'], $start['mon'], ($start['mday'] + 1), $start['year']));
				}
				$dtuntil = VikBooking::getDateTimestamp($prepeatuntil, 23, 59, 59);
				if (count($wdays) < 7 && $dtuntil > $pdto) {
					// increment end date for the repeat
					$end = getdate(mktime($end['hours'], $end['minutes'], $end['seconds'], $end['mon'], ($end['mday'] + 1), $end['year']));
					//
					$until_info = getdate($dtuntil);
					$interval = array();
					while ($end[0] <= $until_info[0]) {
						if (in_array($end['wday'], $wdays)) {
							if (!isset($interval['from'])) {
								$interval['from'] = $end[0];
							}
							$interval['to'] = $end[0];
						} else {
							if (isset($interval['from'])) {
								// append interval
								array_push($repeat_intervals, $interval);
								// reset interval
								$interval = array();
							}
						}
						// next day
						$end = getdate(mktime($end['hours'], $end['minutes'], $end['seconds'], $end['mon'], ($end['mday'] + 1), $end['year']));
					}
					if (isset($interval['from'])) {
						// append last hanging interval
						array_push($repeat_intervals, $interval);
					}
					if (count($repeat_intervals)) {
						// create the repeated records for the calculated intervals
						$repeat_count = 2;
						foreach ($repeat_intervals as $rp) {
							if (date('Y-m-d', $rp['from']) == date('Y-m-d', $rp['to'])) {
								// adjust time in case of equal dates (1 single day restriction)
								$rpfrom = getdate($rp['from']);
								$rpto = getdate($rp['to']);
								$rp['from'] = mktime(0, 0, 0, $rpfrom['mon'], $rpfrom['mday'], $rpfrom['year']);
								$rp['to'] = mktime(0, 0, 0, $rpto['mon'], $rpto['mday'], $rpto['year']);
							}
							// adjust name
							$restr_rp_name = $pname . " #{$repeat_count}";
							//
							$q = "INSERT INTO `#__vikbooking_restrictions` (`name`,`month`,`wday`,`minlos`,`multiplyminlos`,`maxlos`,`dfrom`,`dto`,`wdaytwo`,`wdaycombo`,`allrooms`,`idrooms`,`ctad`,`ctdd`) VALUES(".$dbo->quote($restr_rp_name).", '".$pmonth."', ".(strlen($pwday) > 0 ? "'".$pwday."'" : "NULL").", '".$pminlos."', '".$pmultiplyminlos."', '".$pmaxlos."', ".$rp['from'].", ".$rp['to'].", ".(strlen($pwday) > 0 && strlen($pwdaytwo) > 0 ? intval($pwdaytwo) : "NULL").", ".(strlen($combostr) > 0 ? $dbo->quote($combostr) : "NULL").", ".$pallrooms.", ".(strlen($ridr) > 0 ? $dbo->quote($ridr) : "NULL").", ".(count($setcta) > 0 ? $dbo->quote(implode(',', $setcta)) : "NULL").", ".(count($setctd) > 0 ? $dbo->quote(implode(',', $setctd)) : "NULL").");";
							$dbo->setQuery($q);
							$dbo->execute();
							$lid = $dbo->insertid();
							if (!empty($lid)) {
								$repeat_count++;
							}
						}
					}
				}
			}
			//
			$mainframe->enqueueMessage(JText::translate('VBRESTRICTIONSAVED'));
			$mainframe->redirect("index.php?option=com_vikbooking&task=restrictions");
		} else {
			VikError::raiseWarning('', 'Error while saving');
			$mainframe->redirect("index.php?option=com_vikbooking&task=newrestriction");
		}
	}

	public function updaterestriction() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$mainframe = JFactory::getApplication();
		$updforvcm = $session->get('vbVcmRatesUpd', '');
		$updforvcm = empty($updforvcm) || !is_array($updforvcm) ? array() : $updforvcm;
		$pwhere = VikRequest::getInt('where', '', 'request');
		$pname = VikRequest::getString('name', '', 'request');
		$pmonth = VikRequest::getInt('month', '', 'request');
		$pmonth = empty($pmonth) ? 0 : $pmonth;
		$pname = empty($pname) ? 'Restriction '.$pmonth : $pname;
		$pdfrom = VikRequest::getString('dfrom', '', 'request');
		$pdto = VikRequest::getString('dto', '', 'request');
		$pwday = VikRequest::getString('wday', '', 'request');
		$pwdaytwo = VikRequest::getString('wdaytwo', '', 'request');
		$pwdaytwo = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday == $pwdaytwo ? '' : $pwdaytwo;
		$pcomboa = VikRequest::getString('comboa', '', 'request');
		$pcomboa = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo ? $pcomboa : '';
		$pcombob = VikRequest::getString('combob', '', 'request');
		$pcombob = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo ? $pcombob : '';
		$pcomboc = VikRequest::getString('comboc', '', 'request');
		$pcomboc = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo ? $pcomboc : '';
		$pcombod = VikRequest::getString('combod', '', 'request');
		$pcombod = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo ? $pcombod : '';
		$combostr = '';
		$combostr .= strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo && !empty($pcomboa) ? $pcomboa.':' : ':';
		$combostr .= strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo && !empty($pcombob) ? $pcombob.':' : ':';
		$combostr .= strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo && !empty($pcomboc) ? $pcomboc.':' : ':';
		$combostr .= strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo && !empty($pcombod) ? $pcombod : '';
		$pminlos = VikRequest::getInt('minlos', '', 'request');
		$pminlos = $pminlos < 1 ? 1 : $pminlos;
		$pmaxlos = VikRequest::getInt('maxlos', '', 'request');
		$pmaxlos = empty($pmaxlos) ? 0 : $pmaxlos;
		$pmultiplyminlos = VikRequest::getString('multiplyminlos', '', 'request');
		$pmultiplyminlos = empty($pmultiplyminlos) ? 0 : 1;
		$pallrooms = VikRequest::getString('allrooms', '', 'request');
		$pallrooms = $pallrooms == "1" ? 1 : 0;
		$pidrooms = VikRequest::getVar('idrooms', array(0));
		$ridr = '';
		$roomidsforsess = array();
		if (!empty($pidrooms) && @count($pidrooms) && $pallrooms == 0) {
			foreach ($pidrooms as $idr) {
				if (empty($idr)) {
					continue;
				}
				$ridr .= '-'.$idr.'-;';
				$roomidsforsess[] = (int)$idr;
			}
		} elseif ($pallrooms > 0) {
			$q = "SELECT `id` FROM `#__vikbooking_rooms`;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$fetchids = $dbo->loadAssocList();
				foreach ($fetchids as $fetchid) {
					$roomidsforsess[] = (int)$fetchid['id'];
				}
			}
		}
		$pcta = VikRequest::getInt('cta', '', 'request');
		$pctd = VikRequest::getInt('ctd', '', 'request');
		$pctad = VikRequest::getVar('ctad', array());
		$pctdd = VikRequest::getVar('ctdd', array());
		if ($pminlos == 1 && strlen($pwday) == 0 && empty($pctad) && empty($pctdd) && $pmaxlos < 1) {
			// VBO 1.11 - we now allow restrictions with just 1 night of stay
			// VikError::raiseWarning('', JText::translate('VBUSELESSRESTRICTION'));
			// $mainframe->redirect("index.php?option=com_vikbooking&task=editrestriction&cid[]=".$pwhere);
			// exit;
		}
		//check if there are restrictions for this month
		if ($pmonth > 0) {
			$q = "SELECT `id` FROM `#__vikbooking_restrictions` WHERE `month`='".$pmonth."' AND `id`!='".$pwhere."';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				VikError::raiseWarning('', JText::translate('VBRESTRICTIONMONTHEXISTS'));
				$mainframe->redirect("index.php?option=com_vikbooking&task=editrestriction&cid[]=".$pwhere);
				exit;
			}
			$pdfrom = 0;
			$pdto = 0;
		} else {
			//dates range
			if (empty($pdfrom) || empty($pdto)) {
				VikError::raiseWarning('', JText::translate('VBRESTRICTIONERRDRANGE'));
				$mainframe->redirect("index.php?option=com_vikbooking&task=editrestriction&cid[]=".$pwhere);
				exit;
			} else {
				$housto = $pdfrom == $pdto ? 23 : 0;
				$minsto = $pdfrom == $pdto ? 59 : 0;
				$secsto = $pdfrom == $pdto ? 59 : 0;
				$pdfrom = VikBooking::getDateTimestamp($pdfrom, 0, 0);
				$pdto = VikBooking::getDateTimestamp($pdto, $housto, $minsto, $secsto);
			}
			if ($pdfrom > $pdto) {
				// invalid dates in the past
				VikError::raiseWarning('', JText::translate('VBRESTRICTIONERRDRANGE'));
				$mainframe->redirect("index.php?option=com_vikbooking&task=editrestriction&cid[]=".$pwhere);
				exit;
			}
		}
		//CTA and CTD
		$setcta = array();
		$setctd = array();
		if ($pcta > 0 && count($pctad) > 0) {
			foreach ($pctad as $ctwd) {
				if (strlen($ctwd)) {
					$setcta[] = '-'.(int)$ctwd.'-';
				}
			}
		}
		if ($pctd > 0 && count($pctdd) > 0) {
			foreach ($pctdd as $ctwd) {
				if (strlen($ctwd)) {
					$setctd[] = '-'.(int)$ctwd.'-';
				}
			}
		}
		//
		//update session values
		if (!($pdfrom > 0)) {
			$attemptyear = (int)date('Y');
			$attemptfrom = mktime(0, 0, 0, $pmonth, 1, $attemptyear);
			if ($attemptfrom < time()) {
				$attemptyear++;
				$attemptfrom = mktime(0, 0, 0, $pmonth, 1, $attemptyear);
			}
			$attemptto = mktime(0, 0, 0, $pmonth, date('t', $attemptfrom), $attemptyear);
		} else {
			$attemptfrom = $pdfrom;
			$attemptto = $pdto;
		}
		$updforvcm['count'] = array_key_exists('count', $updforvcm) && !empty($updforvcm['count']) ? ($updforvcm['count'] + 1) : 1;
		if (array_key_exists('dfrom', $updforvcm) && !empty($updforvcm['dfrom'])) {
			$updforvcm['dfrom'] = $updforvcm['dfrom'] > $attemptfrom ? $attemptfrom : $updforvcm['dfrom'];
		} else {
			$updforvcm['dfrom'] = $attemptfrom;
		}
		if (array_key_exists('dto', $updforvcm) && !empty($updforvcm['dto'])) {
			$updforvcm['dto'] = $updforvcm['dto'] < $attemptto ? $attemptto : $updforvcm['dto'];
		} else {
			$updforvcm['dto'] = $attemptto;
		}
		if (array_key_exists('rooms', $updforvcm) && is_array($updforvcm['rooms'])) {
			foreach ($roomidsforsess as $rid) {
				if (!in_array($rid, $updforvcm['rooms'])) {
					$updforvcm['rooms'][] = $rid;
				}
			}
		} else {
			$updforvcm['rooms'] = $roomidsforsess;
		}
		if (!array_key_exists('rplans', $updforvcm) || !is_array($updforvcm['rplans'])) {
			$updforvcm['rplans'] = array();
		}
		$session->set('vbVcmRatesUpd', $updforvcm);
		//
		$q = "UPDATE `#__vikbooking_restrictions` SET `name`=".$dbo->quote($pname).",`month`='".$pmonth."',`wday`=".(strlen($pwday) > 0 ? "'".$pwday."'" : "NULL").",`minlos`='".$pminlos."',`multiplyminlos`='".$pmultiplyminlos."',`maxlos`='".$pmaxlos."',`dfrom`=".$pdfrom.",`dto`=".$pdto.",`wdaytwo`=".(strlen($pwday) > 0 && strlen($pwdaytwo) > 0 ? intval($pwdaytwo) : "NULL").",`wdaycombo`=".(strlen($combostr) > 0 ? $dbo->quote($combostr) : "NULL").",`allrooms`=".$pallrooms.",`idrooms`=".(strlen($ridr) > 0 ? $dbo->quote($ridr) : "NULL").", `ctad`=".(count($setcta) > 0 ? $dbo->quote(implode(',', $setcta)) : "NULL").", `ctdd`=".(count($setctd) > 0 ? $dbo->quote(implode(',', $setctd)) : "NULL")." WHERE `id`='".$pwhere."';";
		$dbo->setQuery($q);
		$dbo->execute();
		$mainframe->enqueueMessage(JText::translate('VBRESTRICTIONSAVED'));
		$mainframe->redirect("index.php?option=com_vikbooking&task=restrictions");
	}

	public function removerestrictions() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikbooking_restrictions` WHERE `id`=".(int)$d.";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=restrictions");
	}

	public function prices() {
		VikBookingHelper::printHeader("1");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'prices'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function newprice() {
		VikBookingHelper::printHeader("1");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageprice'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function editprice() {
		VikBookingHelper::printHeader("1");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageprice'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function createprice() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$this->do_createprice();
	}

	public function createprice_new() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$this->do_createprice(true);
	}

	private function do_createprice($new = false) {
		$pprice = VikRequest::getString('price', '', 'request');
		$pattr = VikRequest::getString('attr', '', 'request');
		$ppraliq = VikRequest::getInt('praliq', '', 'request');
		$pbreakfast_included = VikRequest::getInt('breakfast_included', '', 'request');
		$pbreakfast_included = $pbreakfast_included == 1 ? 1 : 0;
		$pfree_cancellation = VikRequest::getInt('free_cancellation', '', 'request');
		$pfree_cancellation = $pfree_cancellation == 1 ? 1 : 0;
		$pcanc_deadline = VikRequest::getInt('canc_deadline', '', 'request');
		$pminlos = VikRequest::getInt('minlos', '', 'request');
		$pminlos = $pminlos < 1 ? 1 : $pminlos;
		$pminhadv = VikRequest::getInt('minhadv', '', 'request');
		$pminhadv = $pminhadv < 0 ? 0 : $pminhadv;
		$pcanc_policy = VikRequest::getString('canc_policy', '', 'request', VIKREQUEST_ALLOWHTML);
		if (!empty($pprice)) {
			$dbo = JFactory::getDBO();
			$q = "INSERT INTO `#__vikbooking_prices` (`name`,`attr`,`idiva`,`breakfast_included`,`free_cancellation`,`canc_deadline`,`canc_policy`,`minlos`,`minhadv`) VALUES(".$dbo->quote($pprice).", ".$dbo->quote($pattr).", ".$dbo->quote($ppraliq).", ".$pbreakfast_included.", ".$pfree_cancellation.", ".$pcanc_deadline.", ".$dbo->quote($pcanc_policy).", ".$pminlos.", ".$pminhadv.");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=" . ($new ? 'newprice' : 'prices'));
	}

	public function updateprice() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$this->do_updateprice();
	}

	public function updatepricestay() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$this->do_updateprice(true);
	}

	private function do_updateprice($stay = false) {
		$pprice = VikRequest::getString('price', '', 'request');
		$pattr = VikRequest::getString('attr', '', 'request');
		$ppraliq = VikRequest::getInt('praliq', '', 'request');
		$pbreakfast_included = VikRequest::getInt('breakfast_included', '', 'request');
		$pbreakfast_included = $pbreakfast_included == 1 ? 1 : 0;
		$pfree_cancellation = VikRequest::getInt('free_cancellation', '', 'request');
		$pfree_cancellation = $pfree_cancellation == 1 ? 1 : 0;
		$pcanc_deadline = VikRequest::getInt('canc_deadline', '', 'request');
		$pminlos = VikRequest::getInt('minlos', '', 'request');
		$pminlos = $pminlos < 1 ? 1 : $pminlos;
		$pminhadv = VikRequest::getInt('minhadv', '', 'request');
		$pminhadv = $pminhadv < 0 ? 0 : $pminhadv;
		$pcanc_policy = VikRequest::getString('canc_policy', '', 'request', VIKREQUEST_ALLOWHTML);
		$pwhereup = VikRequest::getString('whereup', '', 'request');
		if (!empty($pprice)) {
			$dbo = JFactory::getDBO();
			$q = "UPDATE `#__vikbooking_prices` SET `name`=".$dbo->quote($pprice).",`attr`=".$dbo->quote($pattr).",`idiva`=".$dbo->quote($ppraliq).",`breakfast_included`=".$pbreakfast_included.",`free_cancellation`=".$pfree_cancellation.",`canc_deadline`=".$pcanc_deadline.",`canc_policy`=".$dbo->quote($pcanc_policy).",`minlos`=".$pminlos.",`minhadv`=".$pminhadv." WHERE `id`=".$dbo->quote($pwhereup).";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=" . ($stay ? 'editprice&cid[]=' . $pwhereup : 'prices'));
	}

	public function removeprice() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikbooking_prices` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
				$q = "DELETE FROM `#__vikbooking_dispcost` WHERE `idprice`=".intval($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=prices");
	}

	public function iva() {
		VikBookingHelper::printHeader("2");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'iva'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function newiva() {
		VikBookingHelper::printHeader("2");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageiva'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function editiva() {
		VikBookingHelper::printHeader("2");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageiva'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function createiva() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$paliqname = VikRequest::getString('aliqname', '', 'request');
		$paliqperc = VikRequest::getFloat('aliqperc', '', 'request');
		$pbreakdown_name = VikRequest::getVar('breakdown_name', array());
		$pbreakdown_rate = VikRequest::getVar('breakdown_rate', array());
		$ptaxcap = VikRequest::getFloat('taxcap', 0, 'request');
		if (!empty($paliqperc)) {
			$dbo = JFactory::getDBO();
			$breakdown_str = '';
			if (count($pbreakdown_name) > 0) {
				$breakdown_values = array();
				$bkcount = 0;
				$tot_sub_aliq = 0;
				foreach ($pbreakdown_name as $key => $subtax) {
					if (!empty($subtax) && floatval($pbreakdown_rate[$key]) > 0) {
						$breakdown_values[$bkcount]['name'] = $subtax;
						$breakdown_values[$bkcount]['aliq'] = (float)$pbreakdown_rate[$key];
						$tot_sub_aliq += (float)$pbreakdown_rate[$key];
						$bkcount++;
					}
				}
				if (count($breakdown_values) > 0) {
					$breakdown_str = json_encode($breakdown_values);
					if ($tot_sub_aliq < (float)$paliqperc || $tot_sub_aliq > (float)$paliqperc) {
						VikError::raiseWarning('', JText::translate('VBOTAXBKDWNERRNOMATCH'));
					}
				}
			}
			$q = "INSERT INTO `#__vikbooking_iva` (`name`,`aliq`,`breakdown`,`taxcap`) VALUES(".$dbo->quote($paliqname).", ".$dbo->quote($paliqperc).", ".(empty($breakdown_str) ? 'NULL' : $dbo->quote($breakdown_str)).", ".($ptaxcap > 0 ? $dbo->quote($ptaxcap) : 'NULL').");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=iva");
	}

	public function updateiva() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$paliqname = VikRequest::getString('aliqname', '', 'request');
		$paliqperc = VikRequest::getFloat('aliqperc', '', 'request');
		$pbreakdown_name = VikRequest::getVar('breakdown_name', array());
		$pbreakdown_rate = VikRequest::getVar('breakdown_rate', array());
		$ptaxcap = VikRequest::getFloat('taxcap', 0, 'request');
		$pwhereup = VikRequest::getInt('whereup', 0, 'request');
		if (!empty($paliqperc)) {
			$dbo = JFactory::getDBO();
			$breakdown_str = '';
			if (count($pbreakdown_name) > 0) {
				$breakdown_values = array();
				$bkcount = 0;
				$tot_sub_aliq = 0;
				foreach ($pbreakdown_name as $key => $subtax) {
					if (!empty($subtax) && floatval($pbreakdown_rate[$key]) > 0) {
						$breakdown_values[$bkcount]['name'] = $subtax;
						$breakdown_values[$bkcount]['aliq'] = (float)$pbreakdown_rate[$key];
						$tot_sub_aliq += (float)$pbreakdown_rate[$key];
						$bkcount++;
					}
				}
				if (count($breakdown_values) > 0) {
					$breakdown_str = json_encode($breakdown_values);
					if ($tot_sub_aliq < (float)$paliqperc || $tot_sub_aliq > (float)$paliqperc) {
						VikError::raiseWarning('', JText::translate('VBOTAXBKDWNERRNOMATCH'));
					}
				}
			}
			$q = "UPDATE `#__vikbooking_iva` SET `name`=".$dbo->quote($paliqname).",`aliq`=".$dbo->quote($paliqperc).",`breakdown`=".(empty($breakdown_str) ? 'NULL' : $dbo->quote($breakdown_str)).",`taxcap`=".($ptaxcap > 0 ? $dbo->quote($ptaxcap) : 'NULL')." WHERE `id`=".$dbo->quote($pwhereup).";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=iva");
	}

	public function removeiva() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikbooking_iva` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=iva");
	}

	public function categories() {
		VikBookingHelper::printHeader("4");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'categories'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function newcat() {
		VikBookingHelper::printHeader("4");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecategory'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function editcat() {
		VikBookingHelper::printHeader("4");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecategory'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function createcat() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$pcatname = VikRequest::getString('catname', '', 'request');
		$pdescr = VikRequest::getString('descr', '', 'request', VIKREQUEST_ALLOWHTML);
		if (!empty($pcatname)) {
			$dbo = JFactory::getDBO();
			$q = "INSERT INTO `#__vikbooking_categories` (`name`,`descr`) VALUES(".$dbo->quote($pcatname).", ".$dbo->quote($pdescr).");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=categories");
	}

	public function updatecat() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$pcatname = VikRequest::getString('catname', '', 'request');
		$pdescr = VikRequest::getString('descr', '', 'request', VIKREQUEST_ALLOWHTML);
		$pwhereup = VikRequest::getString('whereup', '', 'request');
		if (!empty($pcatname)) {
			$dbo = JFactory::getDBO();
			$q = "UPDATE `#__vikbooking_categories` SET `name`=".$dbo->quote($pcatname).", `descr`=".$dbo->quote($pdescr)." WHERE `id`=".$dbo->quote($pwhereup).";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=categories");
	}

	public function removecat() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikbooking_categories` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=categories");
	}

	public function carat() {
		VikBookingHelper::printHeader("5");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'carat'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function newcarat() {
		VikBookingHelper::printHeader("5");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecarat'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function editcarat() {
		VikBookingHelper::printHeader("5");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecarat'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function createcarat() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$pcaratname = VikRequest::getString('caratname', '', 'request');
		$pcarattextimg = VikRequest::getString('carattextimg', '', 'request', VIKREQUEST_ALLOWHTML);
		$pautoresize = VikRequest::getString('autoresize', '', 'request');
		$presizeto = VikRequest::getString('resizeto', '', 'request');
		$pidrooms = VikRequest::getVar('idrooms', array());
		if (!empty($pcaratname)) {
			if (intval($_FILES['caraticon']['error']) == 0 && VikBooking::caniWrite(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR) && trim($_FILES['caraticon']['name'])!="") {
				jimport('joomla.filesystem.file');
				$updpath = VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR;
				if (@is_uploaded_file($_FILES['caraticon']['tmp_name'])) {
					$safename=JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['caraticon']['name'])));
					if (file_exists($updpath.$safename)) {
						$j=1;
						while (file_exists($updpath.$j.$safename)) {
							$j++;
						}
						$pwhere=$updpath.$j.$safename;
					} else {
						$j="";
						$pwhere=$updpath.$safename;
					}
					if (!getimagesize($_FILES['caraticon']['tmp_name'])) {
						@unlink($pwhere);
						$picon="";
					} else {
						VikBooking::uploadFile($_FILES['caraticon']['tmp_name'], $pwhere);
						@chmod($pwhere, 0644);
						$picon=$j.$safename;
						if ($pautoresize=="1" && !empty($presizeto)) {
							$eforj = new vikResizer();
							$origmod = $eforj->proportionalImage($pwhere, $updpath.'r_'.$j.$safename, $presizeto, $presizeto);
							if ($origmod) {
								@unlink($pwhere);
								$picon='r_'.$j.$safename;
							}
						}
					}
				} else {
					$picon="";
				}
			} else {
				$picon="";
			}
			$dbo = JFactory::getDbo();
			// get new ordering
			$q = "SELECT `ordering` FROM `#__vikbooking_characteristics` ORDER BY `#__vikbooking_characteristics`.`ordering` DESC LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows()) {
				$newsortnum = $dbo->loadResult() + 1;
			} else {
				$newsortnum = 1;
			}
			$pordering = VikRequest::getInt('ordering', 0, 'request');
			$newsortnum = !empty($pordering) ? $pordering : $newsortnum;
			//
			$q = "INSERT INTO `#__vikbooking_characteristics` (`name`,`icon`,`textimg`,`ordering`) VALUES(".$dbo->quote($pcaratname).", ".$dbo->quote($picon).", ".$dbo->quote($pcarattextimg).", {$newsortnum});";
			$dbo->setQuery($q);
			$dbo->execute();
			
			$new_carat_id = $dbo->insertid();
			if (!empty($new_carat_id)) {
				// assign/unset carat-rooms relations
				$rooms_with_carat = array();
				if (count($pidrooms)) {
					// assign this new carat to the requested rooms
					foreach ($pidrooms as $idroom) {
						if (empty($idroom)) {
							continue;
						}
						$q = "SELECT `id`, `idcarat` FROM `#__vikbooking_rooms` WHERE `id`=" . (int)$idroom . ";";
						$dbo->setQuery($q);
						$dbo->execute();
						if (!$dbo->getNumRows()) {
							continue;
						}
						$room_data = $dbo->loadAssoc();
						array_push($rooms_with_carat, $room_data['id']);
						$current_carats = empty($room_data['idcarat']) ? array() : explode(';', rtrim($room_data['idcarat'], ';'));
						if (in_array((string)$new_carat_id, $current_carats)) {
							continue;
						}
						if (count($current_carats) === 1 && (string)$current_carats[0] == '0') {
							// make sure we do not concatenate a real ID to 0
							$current_carats = array();
						}
						array_push($current_carats, $new_carat_id);
						$new_opts = implode(';', $current_carats) . ';';
						$q = "UPDATE `#__vikbooking_rooms` SET `idcarat`=" . $dbo->quote($new_opts) . " WHERE `id`={$room_data['id']};";
						$dbo->setQuery($q);
						$dbo->execute();
					}
				}
				if (!count($rooms_with_carat)) {
					// get all rooms to unset this carat (if previously set)
					array_push($rooms_with_carat, '0');
				}
				// unset the carat from the other rooms that may have it
				$q = "SELECT `id`, `idcarat` FROM `#__vikbooking_rooms` WHERE `id` NOT IN (" . implode(', ', $rooms_with_carat) . ");";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows()) {
					$unset_rooms_carat = $dbo->loadAssocList();
					foreach ($unset_rooms_carat as $room_data) {
						$current_carats = empty($room_data['idcarat']) ? array() : explode(';', rtrim($room_data['idcarat'], ';'));
						if (!in_array((string)$new_carat_id, $current_carats)) {
							// this room is not using this carat
							continue;
						}
						$caratkey = array_search((string)$new_carat_id, $current_carats);
						if ($caratkey === false) {
							// key not found
							continue;
						}
						// unset this carat ID from the string
						unset($current_carats[$caratkey]);
						if (!count($current_carats)) {
							// a room with no carats assigned will be listed as "0;"
							$current_carats = array(0);
						}
						$new_opts = implode(';', $current_carats) . ';';
						$q = "UPDATE `#__vikbooking_rooms` SET `idcarat`=" . $dbo->quote($new_opts) . " WHERE `id`={$room_data['id']};";
						$dbo->setQuery($q);
						$dbo->execute();
					}
				}
				//
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=carat");
	}

	public function updatecarat() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$pcaratname = VikRequest::getString('caratname', '', 'request');
		$pcarattextimg = VikRequest::getString('carattextimg', '', 'request', VIKREQUEST_ALLOWHTML);
		$pwhereup = VikRequest::getString('whereup', '', 'request');
		$pautoresize = VikRequest::getString('autoresize', '', 'request');
		$presizeto = VikRequest::getString('resizeto', '', 'request');
		$pidrooms = VikRequest::getVar('idrooms', array());
		$pordering = VikRequest::getInt('ordering', 1, 'request');
		if (!empty($pcaratname)) {
			if (intval($_FILES['caraticon']['error']) == 0 && VikBooking::caniWrite(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR) && trim($_FILES['caraticon']['name'])!="") {
				jimport('joomla.filesystem.file');
				$updpath = VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR;
				if (@is_uploaded_file($_FILES['caraticon']['tmp_name'])) {
					$safename=JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['caraticon']['name'])));
					if (file_exists($updpath.$safename)) {
						$j=1;
						while (file_exists($updpath.$j.$safename)) {
							$j++;
						}
						$pwhere=$updpath.$j.$safename;
					} else {
						$j="";
						$pwhere=$updpath.$safename;
					}
					if (!getimagesize($_FILES['caraticon']['tmp_name'])) {
						@unlink($pwhere);
						$picon="";
					} else {
						VikBooking::uploadFile($_FILES['caraticon']['tmp_name'], $pwhere);
						@chmod($pwhere, 0644);
						$picon=$j.$safename;
						if ($pautoresize=="1" && !empty($presizeto)) {
							$eforj = new vikResizer();
							$origmod = $eforj->proportionalImage($pwhere, $updpath.'r_'.$j.$safename, $presizeto, $presizeto);
							if ($origmod) {
								@unlink($pwhere);
								$picon='r_'.$j.$safename;
							}
						}
					}
				} else {
					$picon="";
				}
			} else {
				$picon="";
			}
			$dbo = JFactory::getDbo();
			$q = "UPDATE `#__vikbooking_characteristics` SET `name`=".$dbo->quote($pcaratname).",".(strlen($picon) > 0 ? "`icon`='".$picon."'," : "")."`textimg`=".$dbo->quote($pcarattextimg).",`ordering`={$pordering} WHERE `id`=".$dbo->quote($pwhereup).";";
			$dbo->setQuery($q);
			$dbo->execute();

			// assign/unset carat-rooms relations
			$rooms_with_carat = array();
			if (count($pidrooms)) {
				// assign this new carat to the requested rooms
				foreach ($pidrooms as $idroom) {
					if (empty($idroom)) {
						continue;
					}
					$q = "SELECT `id`, `idcarat` FROM `#__vikbooking_rooms` WHERE `id`=" . (int)$idroom . ";";
					$dbo->setQuery($q);
					$dbo->execute();
					if (!$dbo->getNumRows()) {
						continue;
					}
					$room_data = $dbo->loadAssoc();
					array_push($rooms_with_carat, $room_data['id']);
					$current_carats = empty($room_data['idcarat']) ? array() : explode(';', rtrim($room_data['idcarat'], ';'));
					if (in_array((string)$pwhereup, $current_carats)) {
						continue;
					}
					if (count($current_carats) === 1 && (string)$current_carats[0] == '0') {
						// make sure we do not concatenate a real ID to 0
						$current_carats = array();
					}
					array_push($current_carats, $pwhereup);
					$new_carats = implode(';', $current_carats) . ';';
					$q = "UPDATE `#__vikbooking_rooms` SET `idcarat`=" . $dbo->quote($new_carats) . " WHERE `id`={$room_data['id']};";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
			if (!count($rooms_with_carat)) {
				// get all rooms to unset this carat (if previously set)
				array_push($rooms_with_carat, '0');
			}
			// unset the carat from the other rooms that may have it
			$q = "SELECT `id`, `idcarat` FROM `#__vikbooking_rooms` WHERE `id` NOT IN (" . implode(', ', $rooms_with_carat) . ");";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows()) {
				$unset_rooms_carat = $dbo->loadAssocList();
				foreach ($unset_rooms_carat as $room_data) {
					$current_carats = empty($room_data['idcarat']) ? array() : explode(';', rtrim($room_data['idcarat'], ';'));
					if (!in_array((string)$pwhereup, $current_carats)) {
						// this room is not using this carat
						continue;
					}
					$caratkey = array_search((string)$pwhereup, $current_carats);
					if ($caratkey === false) {
						// key not found
						continue;
					}
					// unset this carat ID from the string
					unset($current_carats[$caratkey]);
					if (!count($current_carats)) {
						// a room with no carats assigned will be listed as "0;"
						$current_carats = array(0);
					}
					$new_carats = implode(';', $current_carats) . ';';
					$q = "UPDATE `#__vikbooking_rooms` SET `idcarat`=" . $dbo->quote($new_carats) . " WHERE `id`={$room_data['id']};";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
			//
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=carat");
	}

	public function removecarat() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "SELECT `icon` FROM `#__vikbooking_characteristics` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$rows = $dbo->loadAssocList();
					if (!empty($rows[0]['icon']) && file_exists(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.$rows[0]['icon'])) {
						@unlink(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.$rows[0]['icon']);
					}
				}	
				$q = "DELETE FROM `#__vikbooking_characteristics` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=carat");
	}

	public function coupons() {
		VikBookingHelper::printHeader("17");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'coupons'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function newcoupon() {
		VikBookingHelper::printHeader("17");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecoupon'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function editcoupon() {
		VikBookingHelper::printHeader("17");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecoupon'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function createcoupon() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$pcode = VikRequest::getString('code', '', 'request');
		$pvalue = VikRequest::getString('value', '', 'request');
		$pfrom = VikRequest::getString('from', '', 'request');
		$pto = VikRequest::getString('to', '', 'request');
		$pidrooms = VikRequest::getVar('idrooms', array(0));
		$ptype = VikRequest::getString('type', '', 'request');
		$ptype = $ptype == "1" ? 1 : 2;
		$ppercentot = VikRequest::getString('percentot', '', 'request');
		$ppercentot = $ppercentot == "1" ? 1 : 2;
		$pallvehicles = VikRequest::getString('allvehicles', '', 'request');
		$pallvehicles = $pallvehicles == "1" ? 1 : 0;
		$pmintotord = VikRequest::getFloat('mintotord', '', 'request');
		$pexcludetaxes = VikRequest::getInt('excludetaxes', 0, 'request');
		$stridrooms = "";
		if (@count($pidrooms) > 0 && $pallvehicles != 1) {
			foreach ($pidrooms as $ch) {
				if (!empty($ch)) {
					$stridrooms .= ";".$ch.";";
				}
			}
		}
		$strdatevalid = "";
		if (strlen($pfrom) > 0 && strlen($pto) > 0) {
			$first = VikBooking::getDateTimestamp($pfrom, 0, 0);
			$second = VikBooking::getDateTimestamp($pto, 0, 0);
			if ($first < $second) {
				$strdatevalid .= $first."-".$second;
			}
		}
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();
		$q = "SELECT * FROM `#__vikbooking_coupons` WHERE `code`=".$dbo->quote($pcode).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			VikError::raiseWarning('', JText::translate('VBCOUPONEXISTS'));
		} else {
			$app->enqueueMessage(JText::translate('VBCOUPONSAVEOK'));
			$q = "INSERT INTO `#__vikbooking_coupons` (`code`,`type`,`percentot`,`value`,`datevalid`,`allvehicles`,`idrooms`,`mintotord`,`excludetaxes`) VALUES(".$dbo->quote($pcode).",'".$ptype."','".$ppercentot."',".$dbo->quote($pvalue).",'".$strdatevalid."','".$pallvehicles."','".$stridrooms."', ".$dbo->quote($pmintotord).", {$pexcludetaxes});";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$app->redirect("index.php?option=com_vikbooking&task=coupons");
	}

	public function updatecoupon() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$pcode = VikRequest::getString('code', '', 'request');
		$pvalue = VikRequest::getString('value', '', 'request');
		$pfrom = VikRequest::getString('from', '', 'request');
		$pto = VikRequest::getString('to', '', 'request');
		$pidrooms = VikRequest::getVar('idrooms', array(0));
		$pwhere = VikRequest::getString('where', '', 'request');
		$ptype = VikRequest::getString('type', '', 'request');
		$ptype = $ptype == "1" ? 1 : 2;
		$ppercentot = VikRequest::getString('percentot', '', 'request');
		$ppercentot = $ppercentot == "1" ? 1 : 2;
		$pallvehicles = VikRequest::getString('allvehicles', '', 'request');
		$pallvehicles = $pallvehicles == "1" ? 1 : 0;
		$pmintotord = VikRequest::getFloat('mintotord', '', 'request');
		$pexcludetaxes = VikRequest::getInt('excludetaxes', 0, 'request');
		$stridrooms = "";
		if (@count($pidrooms) > 0 && $pallvehicles != 1) {
			foreach ($pidrooms as $ch) {
				if (!empty($ch)) {
					$stridrooms .= ";".$ch.";";
				}
			}
		}
		$strdatevalid = "";
		if (strlen($pfrom) > 0 && strlen($pto) > 0) {
			$first = VikBooking::getDateTimestamp($pfrom, 0, 0);
			$second = VikBooking::getDateTimestamp($pto, 0, 0);
			if ($first < $second) {
				$strdatevalid .= $first."-".$second;
			}
		}
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();
		$q = "SELECT * FROM `#__vikbooking_coupons` WHERE `code`=".$dbo->quote($pcode)." AND `id`!='".$pwhere."';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			VikError::raiseWarning('', JText::translate('VBCOUPONEXISTS'));
		} else {
			$app->enqueueMessage(JText::translate('VBCOUPONSAVEOK'));
			$q = "UPDATE `#__vikbooking_coupons` SET `code`=".$dbo->quote($pcode).",`type`='".$ptype."',`percentot`='".$ppercentot."',`value`=".$dbo->quote($pvalue).",`datevalid`='".$strdatevalid."',`allvehicles`='".$pallvehicles."',`idrooms`='".$stridrooms."',`mintotord`=".$dbo->quote($pmintotord).",`excludetaxes`={$pexcludetaxes} WHERE `id`='".$pwhere."';";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$app->redirect("index.php?option=com_vikbooking&task=coupons");
	}

	public function removecoupons() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikbooking_coupons` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=coupons");
	}

	public function removemoreimgs() {
		$mainframe = JFactory::getApplication();
		$proomid = VikRequest::getInt('roomid', '', 'request');
		$pimgind = VikRequest::getInt('imgind', '', 'request');
		if (!strlen($pimgind)) {
			$mainframe->redirect("index.php?option=com_vikbooking");
			exit;
		}
		$dbo = JFactory::getDBO();
		$q = "SELECT `moreimgs`,`imgcaptions` FROM `#__vikbooking_rooms` WHERE `id`='".$proomid."';";
		$dbo->setQuery($q);
		$dbo->execute();
		$row = $dbo->loadAssoc();
		$actmore = $row['moreimgs'];
		if (!empty($actmore)) {
			$actsplit = explode(';;', $actmore);
			$captions = json_decode($row['imgcaptions'], true);
			$captions = !is_array($captions) ? array() : $captions;
			if ($pimgind < 0) {
				foreach ($actsplit as $img) {
					@unlink(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'big_'.$img);
					@unlink(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'thumb_'.$img);
				}
				// reset images and captions
				$actsplit = array();
				$captions = array();
			} else {
				if (array_key_exists($pimgind, $actsplit)) {
					@unlink(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'big_'.$actsplit[$pimgind]);
					@unlink(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'thumb_'.$actsplit[$pimgind]);
					// unset current image
					unset($actsplit[$pimgind]);
					// unset caption if exists
					if (isset($captions[$pimgind])) {
						unset($captions[$pimgind]);
						$captions = array_values($captions);
					}
				}
			}
			$newstr = "";
			foreach ($actsplit as $oi) {
				if (!empty($oi)) {
					$newstr .= $oi.';;';
				}
			}
			$q = "UPDATE `#__vikbooking_rooms` SET `moreimgs`=".$dbo->quote($newstr).", `imgcaptions`=".$dbo->quote(json_encode($captions))." WHERE `id`='".$proomid."';";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe->redirect("index.php?option=com_vikbooking&task=editroom&cid[]=".$proomid);
	}

	public function sortfield() {
		$mainframe = JFactory::getApplication();
		$sortid = VikRequest::getVar('cid', array(0));
		$pmode = VikRequest::getString('mode', '', 'request');
		$dbo = JFactory::getDBO();
		if (!empty($pmode)) {
			$q = "SELECT `id`,`ordering` FROM `#__vikbooking_custfields` ORDER BY `#__vikbooking_custfields`.`ordering` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$totr=$dbo->getNumRows();
			if ($totr > 1) {
				$data = $dbo->loadAssocList();
				if ($pmode == "up") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid[0]) {
							$y = $v['ordering'];
						}
					}
					if ($y && $y > 1) {
						$vik = $y - 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering']) == intval($vik)) {
								$found=true;
								$q = "UPDATE `#__vikbooking_custfields` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikbooking_custfields` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikbooking_custfields` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				} elseif ($pmode == "down") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid[0]) {
							$y = $v['ordering'];
						}
					}
					if ($y) {
						$vik = $y + 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering']) == intval($vik)) {
								$found=true;
								$q = "UPDATE `#__vikbooking_custfields` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikbooking_custfields` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikbooking_custfields` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				}
			}
			$mainframe->redirect("index.php?option=com_vikbooking&task=customf");
		} else {
			$mainframe->redirect("index.php?option=com_vikbooking");
		}
	}

	public function customf() {
		VikBookingHelper::printHeader("16");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'customf'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function newcustomf() {
		VikBookingHelper::printHeader("16");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecustomf'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function editcustomf() {
		VikBookingHelper::printHeader("16");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecustomf'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function createcustomf() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$pname = VikRequest::getString('name', '', 'request', VIKREQUEST_ALLOWHTML);
		$ptype = VikRequest::getString('type', '', 'request');
		$pchoose = VikRequest::getVar('choose', array(0));
		$prequired = VikRequest::getString('required', '', 'request');
		$prequired = $prequired == "1" ? 1 : 0;
		$pflag = VikRequest::getString('flag', '', 'request');
		$pisemail = $pflag == 'isemail' ? 1 : 0;
		$pisnominative = $pflag == 'isnominative' && $ptype == 'text' ? 1 : 0;
		$pisphone = $pflag == 'isphone' && $ptype == 'text' ? 1 : 0;
		$pisaddress = $pflag == 'isaddress' && $ptype == 'text' ? 1 : 0;
		$piscity = $pflag == 'iscity' && $ptype == 'text' ? 1 : 0;
		$piszip = $pflag == 'iszip' && $ptype == 'text' ? 1 : 0;
		$piscompany = $pflag == 'iscompany' && $ptype == 'text' ? 1 : 0;
		$pisvat = $pflag == 'isvat' && $ptype == 'text' ? 1 : 0;
		$pisfisccode = $pflag == 'isfisccode' && $ptype == 'text' ? 1 : 0;
		$pispec = $pflag == 'ispec' && $ptype == 'text' ? 1 : 0;
		$pisrecipcode = $pflag == 'isrecipcode' && $ptype == 'text' ? 1 : 0;
		$fieldflag = '';
		if ($pisaddress == 1) {
			$fieldflag = 'address';
		} elseif ($piscity == 1) {
			$fieldflag = 'city';
		} elseif ($piszip == 1) {
			$fieldflag = 'zip';
		} elseif ($piscompany == 1) {
			$fieldflag = 'company';
		} elseif ($pisvat == 1) {
			$fieldflag = 'vat';
		} elseif ($pisfisccode == 1) {
			$fieldflag = 'fisccode';
		} elseif ($pispec == 1) {
			$fieldflag = 'pec';
		} elseif ($pisrecipcode == 1) {
			$fieldflag = 'recipcode';
		}
		$ppoplink = VikRequest::getString('poplink', '', 'request');
		$choosestr = "";
		if (@count($pchoose) > 0) {
			foreach ($pchoose as $ch) {
				if (!empty($ch)) {
					$choosestr .= $ch.";;__;;";
				}
			}
		}
		$dbo = JFactory::getDBO();
		$q = "SELECT `ordering` FROM `#__vikbooking_custfields` ORDER BY `#__vikbooking_custfields`.`ordering` DESC LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$getlast = $dbo->loadResult();
			$newsortnum = $getlast + 1;
		} else {
			$newsortnum = 1;
		}
		$q = "INSERT INTO `#__vikbooking_custfields` (`name`,`type`,`choose`,`required`,`ordering`,`isemail`,`poplink`,`isnominative`,`isphone`,`flag`) VALUES(".$dbo->quote($pname).", ".$dbo->quote($ptype).", ".$dbo->quote($choosestr).", ".$dbo->quote($prequired).", ".$dbo->quote($newsortnum).", ".$dbo->quote($pisemail).", ".$dbo->quote($ppoplink).", ".$pisnominative.", ".$pisphone.", ".$dbo->quote($fieldflag).");";
		$dbo->setQuery($q);
		$dbo->execute();
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=customf");
	}

	public function updatecustomf() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$pname = VikRequest::getString('name', '', 'request', VIKREQUEST_ALLOWHTML);
		$ptype = VikRequest::getString('type', '', 'request');
		$pchoose = VikRequest::getVar('choose', array(0));
		$prequired = VikRequest::getString('required', '', 'request');
		$prequired = $prequired == "1" ? 1 : 0;
		$pflag = VikRequest::getString('flag', '', 'request');
		$pisemail = $pflag == 'isemail' ? 1 : 0;
		$pisnominative = $pflag == 'isnominative' && $ptype == 'text' ? 1 : 0;
		$pisphone = $pflag == 'isphone' && $ptype == 'text' ? 1 : 0;
		$pisaddress = $pflag == 'isaddress' && $ptype == 'text' ? 1 : 0;
		$piscity = $pflag == 'iscity' && $ptype == 'text' ? 1 : 0;
		$piszip = $pflag == 'iszip' && $ptype == 'text' ? 1 : 0;
		$piscompany = $pflag == 'iscompany' && $ptype == 'text' ? 1 : 0;
		$pisvat = $pflag == 'isvat' && $ptype == 'text' ? 1 : 0;
		$pisfisccode = $pflag == 'isfisccode' && $ptype == 'text' ? 1 : 0;
		$pispec = $pflag == 'ispec' && $ptype == 'text' ? 1 : 0;
		$pisrecipcode = $pflag == 'isrecipcode' && $ptype == 'text' ? 1 : 0;
		$fieldflag = '';
		if ($pisaddress == 1) {
			$fieldflag = 'address';
		} elseif ($piscity == 1) {
			$fieldflag = 'city';
		} elseif ($piszip == 1) {
			$fieldflag = 'zip';
		} elseif ($piscompany == 1) {
			$fieldflag = 'company';
		} elseif ($pisvat == 1) {
			$fieldflag = 'vat';
		} elseif ($pisfisccode == 1) {
			$fieldflag = 'fisccode';
		} elseif ($pispec == 1) {
			$fieldflag = 'pec';
		} elseif ($pisrecipcode == 1) {
			$fieldflag = 'recipcode';
		}
		$ppoplink = VikRequest::getString('poplink', '', 'request');
		$pwhere = VikRequest::getInt('where', '', 'request');
		$choosestr = "";
		if (@count($pchoose) > 0) {
			foreach ($pchoose as $ch) {
				if (!empty($ch)) {
					$choosestr .= $ch.";;__;;";
				}
			}
		}
		$dbo = JFactory::getDBO();
		$q = "UPDATE `#__vikbooking_custfields` SET `name`=".$dbo->quote($pname).",`type`=".$dbo->quote($ptype).",`choose`=".$dbo->quote($choosestr).",`required`=".$dbo->quote($prequired).",`isemail`=".$dbo->quote($pisemail).",`poplink`=".$dbo->quote($ppoplink).",`isnominative`=".$pisnominative.",`isphone`=".$pisphone.",`flag`=".$dbo->quote($fieldflag)." WHERE `id`=".$dbo->quote($pwhere).";";
		$dbo->setQuery($q);
		$dbo->execute();
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=customf");
	}

	public function removecustomf() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikbooking_custfields` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=customf");
	}

	public function overv() {
		VikBookingHelper::printHeader("15");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'overv'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function translations() {
		VikBookingHelper::printHeader("21");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'translations'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function savetranslation() {
		$this->do_savetranslation();
	}

	public function savetranslationstay() {
		$this->do_savetranslation(true);
	}

	private function do_savetranslation($stay = false) {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$vbo_tn = VikBooking::getTranslator();
		$table = VikRequest::getString('vbo_table', '', 'request');
		$cur_langtab = VikRequest::getString('vbo_lang', '', 'request');
		$langs = $vbo_tn->getLanguagesList();
		$xml_tables = $vbo_tn->getTranslationTables();
		if (!empty($table) && array_key_exists($table, $xml_tables)) {
			$tn = VikRequest::getVar('tn', array(), 'request', 'array', VIKREQUEST_ALLOWRAW);
			$tn_saved = 0;
			$table_cols = $vbo_tn->getTableColumns($table);
			$table = $vbo_tn->replacePrefix($table);
			foreach ($langs as $ltag => $lang) {
				if ($ltag == $vbo_tn->default_lang) {
					continue;
				}
				if (array_key_exists($ltag, $tn) && count($tn[$ltag]) > 0) {
					foreach ($tn[$ltag] as $reference_id => $translation) {
						$lang_translation = array();
						foreach ($table_cols as $field => $fdetails) {
							if (!array_key_exists($field, $translation)) {
								continue;
							}
							$ftype = $fdetails['type'];
							if ($ftype == 'skip') {
								continue;
							}
							if ($ftype == 'json') {
								$translation[$field] = json_encode($translation[$field]);
							}
							$lang_translation[$field] = $translation[$field];
						}
						if (count($lang_translation) > 0) {
							$q = "SELECT `id` FROM `#__vikbooking_translations` WHERE `table`=".$dbo->quote($table)." AND `lang`=".$dbo->quote($ltag)." AND `reference_id`=".$dbo->quote((int)$reference_id).";";
							$dbo->setQuery($q);
							$dbo->execute();
							if ($dbo->getNumRows() > 0) {
								$last_id = $dbo->loadResult();
								$q = "UPDATE `#__vikbooking_translations` SET `content`=".$dbo->quote(json_encode($lang_translation))." WHERE `id`=".(int)$last_id.";";
							} else {
								$q = "INSERT INTO `#__vikbooking_translations` (`table`,`lang`,`reference_id`,`content`) VALUES (".$dbo->quote($table).", ".$dbo->quote($ltag).", ".$dbo->quote((int)$reference_id).", ".$dbo->quote(json_encode($lang_translation)).");";
							}
							$dbo->setQuery($q);
							$dbo->execute();
							$tn_saved++;
						}
					}
				}
			}
			if ($tn_saved > 0) {
				$mainframe->enqueueMessage(JText::translate('VBOTRANSLSAVEDOK'));
			}
		} else {
			VikError::raiseWarning('', JText::translate('VBTRANSLATIONERRINVTABLE'));
		}
		$mainframe->redirect("index.php?option=com_vikbooking".($stay ? '&task=translations&vbo_table='.$vbo_tn->replacePrefix($table).'&vbo_lang='.$cur_langtab : '').'&limitstart='.$vbo_tn->lim0.'&limit='.$vbo_tn->lim);
	}

	public function choosebusy() {
		VikBookingHelper::printHeader("8");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'choosebusy'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function orders() {
		VikBookingHelper::printHeader("8");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'orders'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function vieworders() {
		//alias method of orders() for backward compatibility with VCM
		$this->orders();
	}

	public function editorder() {
		VikBookingHelper::printHeader("8");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'editorder'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function removeorders() {
		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();
		$ids = VikRequest::getVar('cid', array(0));
		$pgoto = VikRequest::getString('goto', '', 'request', VIKREQUEST_ALLOWRAW);
		$prev_conf_ids = array();
		if (is_array($ids) && count($ids)) {
			foreach ($ids as $d) {
				$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$rows = $dbo->loadAssocList();
					if ($rows[0]['status'] != 'cancelled') {
						$q = "UPDATE `#__vikbooking_orders` SET `status`='cancelled' WHERE `id`=".(int)$rows[0]['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
						$q = "DELETE FROM `#__vikbooking_tmplock` WHERE `idorder`=" . intval($rows[0]['id']) . ";";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($rows[0]['status'] == 'confirmed') {
							$prev_conf_ids[] = $rows[0]['id'];
						}
						//Booking History
						VikBooking::getBookingHistoryInstance()->setBid($rows[0]['id'])->store('CB');
						//
					}
					$q = "SELECT * FROM `#__vikbooking_ordersbusy` WHERE `idorder`=".(int)$rows[0]['id'].";";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($dbo->getNumRows() > 0) {
						$ordbusy = $dbo->loadAssocList();
						foreach ($ordbusy as $ob) {
							$q = "DELETE FROM `#__vikbooking_busy` WHERE `id`='".$ob['idbusy']."';";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
					$q = "DELETE FROM `#__vikbooking_ordersbusy` WHERE `idorder`=".(int)$rows[0]['id'].";";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($rows[0]['status'] == 'cancelled') {
						$q = "DELETE FROM `#__vikbooking_customers_orders` WHERE `idorder`=" . intval($rows[0]['id']) . ";";
						$dbo->setQuery($q);
						$dbo->execute();
						$q = "DELETE FROM `#__vikbooking_ordersrooms` WHERE `idorder`=".(int)$rows[0]['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
						$q = "DELETE FROM `#__vikbooking_orderhistory` WHERE `idorder`=".(int)$rows[0]['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
						$q = "DELETE FROM `#__vikbooking_orders` WHERE `id`=".(int)$rows[0]['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
					}
				}
			}
			$mainframe->enqueueMessage(JText::translate('VBMESSDELBUSY'));
		}
		if (count($prev_conf_ids) > 0) {
			$prev_conf_ids_str = '';
			foreach ($prev_conf_ids as $prev_id) {
				$prev_conf_ids_str .= '&cid[]='.$prev_id;
			}
			//Invoke Channel Manager
			$vcm_autosync = VikBooking::vcmAutoUpdate();
			if ($vcm_autosync > 0) {
				$vcm_obj = VikBooking::getVcmInvoker();
				$vcm_obj->setOids($prev_conf_ids)->setSyncType('cancel');
				$sync_result = $vcm_obj->doSync();
				if ($sync_result === false) {
					$vcm_err = $vcm_obj->getError();
					VikError::raiseWarning('', JText::translate('VBCHANNELMANAGERRESULTKO').' <a href="index.php?option=com_vikchannelmanager" target="_blank">'.JText::translate('VBCHANNELMANAGEROPEN').'</a> '.(strlen($vcm_err) > 0 ? '('.$vcm_err.')' : ''));
				}
			} elseif (file_exists(VCM_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "synch.vikbooking.php")) {
				$vcm_sync_url = 'index.php?option=com_vikbooking&task=invoke_vcm&stype=cancel'.$prev_conf_ids_str.'&returl='.urlencode('index.php?option=com_vikbooking&task=orders');
				VikError::raiseNotice('', JText::translate('VBCHANNELMANAGERINVOKEASK').' <button type="button" class="btn btn-primary" onclick="document.location.href=\''.$vcm_sync_url.'\';">'.JText::translate('VBCHANNELMANAGERSENDRQ').'</button>');
			}
			//
		}

		if (!empty($pgoto)) {
			if (is_numeric($pgoto) && is_array($ids) && count($ids) === 1) {
				$mainframe->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=" . (int)$ids[0]);
				exit;
			}
			// we expect the goto URL to be base64 encoded
			$mainframe->redirect(base64_decode($pgoto));
			exit;
		}
		// go back to the bookings list page
		$mainframe->redirect("index.php?option=com_vikbooking&task=orders");
	}

	public function config() {
		VikBookingHelper::printHeader("11");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'config'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function saveconfig() {
		$dbo = JFactory::getDbo();
		$pallowbooking = VikRequest::getString('allowbooking', '', 'request');
		$pdisabledbookingmsg = VikRequest::getString('disabledbookingmsg', '', 'request', VIKREQUEST_ALLOWHTML);
		$ptimeopenstorefh = VikRequest::getString('timeopenstorefh', '', 'request');
		$ptimeopenstorefm = VikRequest::getString('timeopenstorefm', '', 'request');
		$ptimeopenstoreth = VikRequest::getString('timeopenstoreth', '', 'request');
		$ptimeopenstoretm = VikRequest::getString('timeopenstoretm', '', 'request');
		$phoursmorebookingback = VikRequest::getString('hoursmorebookingback', '', 'request');
		$phoursmoreroomavail = VikRequest::getString('hoursmoreroomavail', '', 'request');
		$pdateformat = VikRequest::getString('dateformat', '', 'request');
		$pdatesep = VikRequest::getString('datesep', '', 'request');
		$pdatesep = empty($pdatesep) ? "/" : $pdatesep;
		$presmodcanc = VikRequest::getInt('resmodcanc', 1, 'request');
		$presmodcancmin = VikRequest::getInt('resmodcancmin', 1, 'request');
		$pshowcategories = VikRequest::getString('showcategories', '', 'request');
		$pshowchildren = VikRequest::getString('showchildren', '', 'request');
		$psearchsuggestions = VikRequest::getInt('searchsuggestions', '', 'request');
		$ptokenform = VikRequest::getString('tokenform', '', 'request');
		$padminemail = VikRequest::getString('adminemail', '', 'request');
		$psenderemail = VikRequest::getString('senderemail', '', 'request');
		$pminuteslock = VikRequest::getString('minuteslock', '', 'request');
		$pminautoremove = VikRequest::getInt('minautoremove', '', 'request');
		$pfooterordmail = VikRequest::getString('footerordmail', '', 'request', VIKREQUEST_ALLOWHTML);
		$ptermsconds = VikRequest::getString('termsconds', '', 'request', VIKREQUEST_ALLOWHTML);
		$prequirelogin = VikRequest::getString('requirelogin', '', 'request');
		$pautoroomunit = VikRequest::getInt('autoroomunit', '', 'request');
		$ptodaybookings = VikRequest::getInt('todaybookings', '', 'request');
		$ptodaybookings = $ptodaybookings === 1 ? 1 : 0;
		$ploadbootstrap = VikRequest::getInt('loadbootstrap', '', 'request');
		$ploadbootstrap = $ploadbootstrap === 1 ? 1 : 0;
		$pusefa = VikRequest::getInt('usefa', '', 'request');
		$pusefa = $pusefa > 0 ? 1 : 0;
		$ploadjquery = VikRequest::getString('loadjquery', '', 'request');
		$ploadjquery = $ploadjquery == "yes" ? "1" : "0";
		$pcalendar = VikRequest::getString('calendar', '', 'request');
		$pcalendar = $pcalendar == "joomla" ? "joomla" : "jqueryui";
		$penablecoupons = VikRequest::getString('enablecoupons', '', 'request');
		$penablecoupons = $penablecoupons == "1" ? 1 : 0;
		$penablepin = VikRequest::getString('enablepin', '', 'request');
		$penablepin = $penablepin == "1" ? 1 : 0;
		$pmindaysadvance = VikRequest::getInt('mindaysadvance', '', 'request');
		$pmindaysadvance = $pmindaysadvance < 0 ? 0 : $pmindaysadvance;
		$pautodefcalnights = VikRequest::getInt('autodefcalnights', '', 'request');
		$pautodefcalnights = $pautodefcalnights >= 1 ? $pautodefcalnights : '1';
		$pnumrooms = VikRequest::getInt('numrooms', '', 'request');
		$pnumrooms = $pnumrooms > 0 ? $pnumrooms : '5';
		$pnumadultsfrom = VikRequest::getString('numadultsfrom', '', 'request');
		$pnumadultsfrom = intval($pnumadultsfrom) >= 0 ? $pnumadultsfrom : '1';
		$pnumadultsto = VikRequest::getString('numadultsto', '', 'request');
		$pnumadultsto = intval($pnumadultsto) > 0 ? $pnumadultsto : '10';
		if (intval($pnumadultsfrom) > intval($pnumadultsto)) {
			$pnumadultsfrom = '1';
			$pnumadultsto = '10';
		}
		$pnumchildrenfrom = VikRequest::getString('numchildrenfrom', '', 'request');
		$pnumchildrenfrom = intval($pnumchildrenfrom) >= 0 ? $pnumchildrenfrom : '1';
		$pnumchildrento = VikRequest::getString('numchildrento', '', 'request');
		$pnumchildrento = intval($pnumchildrento) > 0 ? $pnumchildrento  : '4';
		if (intval($pnumchildrenfrom) > intval($pnumchildrento)) {
			$pnumadultsfrom = '1';
			$pnumadultsto = '4';
		}
		$confnumadults = $pnumadultsfrom.'-'.$pnumadultsto;
		$confnumchildren = $pnumchildrenfrom.'-'.$pnumchildrento;
		$pmaxdate = VikRequest::getString('maxdate', '', 'request');
		$pmaxdate = intval($pmaxdate) < 1 ? 2 : $pmaxdate;
		$pmaxdateinterval = VikRequest::getString('maxdateinterval', '', 'request');
		$pmaxdateinterval = !in_array($pmaxdateinterval, array('d', 'w', 'm', 'y')) ? 'y' : $pmaxdateinterval;
		$maxdate_str = '+'.$pmaxdate.$pmaxdateinterval;
		$pcronkey = VikRequest::getString('cronkey', '', 'request');
		$pcdsfrom = VikRequest::getVar('cdsfrom', array());
		$pcdsto = VikRequest::getVar('cdsto', array());
		$closing_dates = array();
		if (count($pcdsfrom)) {
			foreach ($pcdsfrom as $kcd => $vcdfrom) {
				if (!empty($vcdfrom) && array_key_exists($kcd, $pcdsto) && !empty($pcdsto[$kcd])) {
					$tscdfrom = VikBooking::getDateTimestamp($vcdfrom, '0', '0');
					$tscdto = VikBooking::getDateTimestamp($pcdsto[$kcd], '0', '0');
					if (!empty($tscdfrom) && !empty($tscdto) && $tscdto >= $tscdfrom) {
						$cdval = array('from' => $tscdfrom, 'to' => $tscdto);
						if (!in_array($cdval, $closing_dates)) {
							$closing_dates[] = $cdval;
						}
					}
				}
			}
		}
		$psmartsearch = VikRequest::getString('smartsearch', '', 'request');
		$psmartsearch = $psmartsearch == "dynamic" ? "dynamic" : "automatic";
		$pvbosef = VikRequest::getInt('vbosef', '', 'request');
		$vbosef = file_exists(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'router.php');
		if ($pvbosef === 1) {
			if (!$vbosef) {
				rename(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'_router.php', VBO_SITE_PATH.DIRECTORY_SEPARATOR.'router.php');
			}
		} else {
			if ($vbosef) {
				rename(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'router.php', VBO_SITE_PATH.DIRECTORY_SEPARATOR.'_router.php');
			}
		}
		$pmultilang = VikRequest::getString('multilang', '', 'request');
		$pmultilang = $pmultilang == "1" ? 1 : 0;
		$pvcmautoupd = VikRequest::getInt('vcmautoupd', '', 'request');
		$pvcmautoupd = $pvcmautoupd > 0 ? 1 : 0;
		/**
		 * Chat params and configuration settings
		 * 
		 * @since 	1.12
		 */
		$pchatenabled = VikRequest::getInt('chatenabled', 0, 'request');
		if (is_file(VCM_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'lib.vikchannelmanager.php')) {
			$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pchatenabled)." WHERE `param`='chatenabled';";
			$dbo->setQuery($q);
			$dbo->execute();
			
			// chat params
			$pchat_res_status = explode(';', VikRequest::getString('chat_res_status', '', 'request'));
			$chat_res_status = array();
			foreach ($pchat_res_status as $chatrs) {
				if (!empty($chatrs)) {
					array_push($chat_res_status, $chatrs);
				}
			}
			$chatparams = new stdClass;
			$chatparams->res_status = $chat_res_status;
			$chatparams->av_type = VikRequest::getString('chat_av_type', '', 'request');
			$chatparams->av_days = VikRequest::getInt('chat_av_days', 0, 'request');

			$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote(json_encode($chatparams))." WHERE `param`='chatparams';";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		
		/**
		 * Pre check-in configuration settings
		 * 
		 * @since 	1.12
		 */
		$pprecheckinenabled = VikRequest::getInt('precheckinenabled', 0, 'request');
		$pprecheckinenabled = $pprecheckinenabled > 0 ? 1 : 0;
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pprecheckinenabled)." WHERE `param`='precheckinenabled';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote(VikRequest::getInt('precheckinminoffset', 0, 'request'))." WHERE `param`='precheckinminoffset';";
		$dbo->setQuery($q);
		$dbo->execute();

		$pupsellingenabled = VikRequest::getInt('upsellingenabled', 0, 'request');
		$pupsellingenabled = $pupsellingenabled > 0 ? 1 : 0;
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pupsellingenabled)." WHERE `param`='upselling';";
		$dbo->setQuery($q);
		$dbo->execute();

		$porphanscal = VikRequest::getString('orphanscal', 'next', 'request');
		$porphanscal = $porphanscal == 'prevnext' ? 'prevnext' : 'next';
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($porphanscal)." WHERE `param`='orphanscalculation';";
		$dbo->setQuery($q);
		$dbo->execute();

		$psrcrtpl = VikRequest::getString('srcrtpl', 'compact', 'request');
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($psrcrtpl)." WHERE `param`='searchrestmpl';";
		$dbo->setQuery($q);
		$dbo->execute();

		/**
		 * Guest Reviews settings
		 * 
		 * @since 	1.13
		 */
		$pgrenabled = VikRequest::getInt('grenabled', 0, 'request');
		$pgrminchars = VikRequest::getInt('grminchars', 0, 'request');
		$pgrappr = VikRequest::getString('grappr', 'auto', 'request');
		$pgrappr = $pgrappr == 'auto' ? 'auto' : 'manual';
		$pgrtype = VikRequest::getString('grtype', 'service', 'request');
		$pgrtype = $pgrtype == 'service' ? 'service' : 'global';
		$pgrsrv = VikRequest::getVar('grsrv', array(), 'request', 'array');
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pgrenabled)." WHERE `param`='grenabled';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pgrminchars)." WHERE `param`='grminchars';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pgrappr)." WHERE `param`='grappr';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pgrtype)." WHERE `param`='grtype';";
		$dbo->setQuery($q);
		$dbo->execute();
		try {
			// always truncate service names (this query may require special permissions)
			$q = "TRUNCATE TABLE `#__vikbooking_greview_service`;";
			$dbo->setQuery($q);
			$dbo->execute();
		} catch (Exception $e) {
			// do nothing
		}
		foreach ($pgrsrv as $srvname) {
			$q = "INSERT INTO `#__vikbooking_greview_service` (`service_name`) VALUES (" . $dbo->quote($srvname) . ");";
			$dbo->setQuery($q);
			$dbo->execute();
		}

		/**
		 * Preferred countries ordering, or custom countries.
		 * 
		 * @since 	1.3.11
		 * @since 	1.4.1 we also support "cust_pref_countries"
		 */
		$pref_countries = VikRequest::getVar('pref_countries', array());
		$cust_pref_countries = VikRequest::getString('cust_pref_countries', '', 'request');
		$pref_countries = !is_array($pref_countries) || empty($pref_countries[0]) ? VikBooking::preferredCountriesOrdering() : $pref_countries;
		if (!empty($cust_pref_countries)) {
			$all_custom_prefcountries = array();
			$cust_pref_countries = explode(',', $cust_pref_countries);
			foreach ($cust_pref_countries as $cust_pref_country) {
				$cust_pref_country = trim(strtolower($cust_pref_country));
				if (empty($cust_pref_country) || strlen($cust_pref_country) != 2) {
					continue;
				}
				array_push($all_custom_prefcountries, $cust_pref_country);
			}
			if (count($all_custom_prefcountries)) {
				$pref_countries = $all_custom_prefcountries;
			}
		}
		$q = "UPDATE `#__vikbooking_config` SET `setting`=" . $dbo->quote(json_encode($pref_countries)) . " WHERE `param`='preferred_countries';";
		$dbo->setQuery($q);
		$dbo->execute();
		//

		$gmapskey = VikRequest::getString('gmapskey', '', 'request');
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($gmapskey)." WHERE `param`='gmapskey';";
		$dbo->setQuery($q);
		$dbo->execute();

		$pref_textcolor = VikRequest::getString('pref_textcolor', '', 'request');
		$pref_bgcolor = VikRequest::getString('pref_bgcolor', '', 'request');
		$pref_fontcolor = VikRequest::getString('pref_fontcolor', '', 'request');
		$pref_bgcolorhov = VikRequest::getString('pref_bgcolorhov', '', 'request');
		$pref_fontcolorhov = VikRequest::getString('pref_fontcolorhov', '', 'request');
		$pref_colors = array(
			'textcolor' => $pref_textcolor,
			'bgcolor' => $pref_bgcolor,
			'fontcolor' => $pref_fontcolor,
			'bgcolorhov' => $pref_bgcolorhov,
			'fontcolorhov' => $pref_fontcolorhov,
		);
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote(json_encode($pref_colors))." WHERE `param`='pref_colors';";
		$dbo->setQuery($q);
		$dbo->execute();

		$interactive_map = VikRequest::getInt('interactive_map', 0, 'request');
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($interactive_map)." WHERE `param`='interactive_map';";
		$dbo->setQuery($q);
		$dbo->execute();

		$noemptydecimals = VikRequest::getInt('noemptydecimals', 0, 'request');
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($noemptydecimals)." WHERE `param`='noemptydecimals';";
		$dbo->setQuery($q);
		$dbo->execute();
		
		$res_backend_path = VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR;
		$picon="";
		if (intval($_FILES['sitelogo']['error']) == 0 && trim($_FILES['sitelogo']['name'])!="") {
			jimport('joomla.filesystem.file');
			if (@is_uploaded_file($_FILES['sitelogo']['tmp_name'])) {
				$safename=JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['sitelogo']['name'])));
				if (file_exists($res_backend_path.$safename)) {
					$j=1;
					while (file_exists($res_backend_path.$j.$safename)) {
						$j++;
					}
					$pwhere=$res_backend_path.$j.$safename;
				} else {
					$j="";
					$pwhere=$res_backend_path.$safename;
				}
				if (!getimagesize($_FILES['sitelogo']['tmp_name'])) {
					@unlink($pwhere);
					$picon="";
				} else {
					VikBooking::uploadFile($_FILES['sitelogo']['tmp_name'], $pwhere);
					@chmod($pwhere, 0644);
					$picon=$j.$safename;
				}
			}
			if (!empty($picon)) {
				$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($picon)." WHERE `param`='sitelogo';";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$pbackicon = "";
		if (intval($_FILES['backlogo']['error']) == 0 && trim($_FILES['backlogo']['name'])!="") {
			jimport('joomla.filesystem.file');
			if (@is_uploaded_file($_FILES['backlogo']['tmp_name'])) {
				$safename=JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['backlogo']['name'])));
				if (file_exists($res_backend_path.$safename)) {
					$j=1;
					while (file_exists($res_backend_path.$j.$safename)) {
						$j++;
					}
					$pwhere=$res_backend_path.$j.$safename;
				} else {
					$j="";
					$pwhere=$res_backend_path.$safename;
				}
				if (!getimagesize($_FILES['backlogo']['tmp_name'])) {
					@unlink($pwhere);
					$pbackicon="";
				} else {
					VikBooking::uploadFile($_FILES['backlogo']['tmp_name'], $pwhere);
					@chmod($pwhere, 0644);
					$pbackicon=$j.$safename;
				}
			}
			if (!empty($pbackicon)) {
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='backlogo';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pbackicon)." WHERE `param`='backlogo';";
					$dbo->setQuery($q);
					$dbo->execute();
				} else {
					$q = "INSERT INTO `#__vikbooking_config` (`param`,`setting`) VALUES ('backlogo',".$dbo->quote($pbackicon).");";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
		}
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pvcmautoupd)." WHERE `param`='vcmautoupd';";
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($pallowbooking) || $pallowbooking!="1") {
			$q = "UPDATE `#__vikbooking_config` SET `setting`='0' WHERE `param`='allowbooking';";
		} else {
			$q = "UPDATE `#__vikbooking_config` SET `setting`='1' WHERE `param`='allowbooking';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($pshowcategories) || $pshowcategories!="yes") {
			$q = "UPDATE `#__vikbooking_config` SET `setting`='0' WHERE `param`='showcategories';";
		} else {
			$q = "UPDATE `#__vikbooking_config` SET `setting`='1' WHERE `param`='showcategories';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($pshowchildren) || $pshowchildren!="yes") {
			$q = "UPDATE `#__vikbooking_config` SET `setting`='0' WHERE `param`='showchildren';";
		} else {
			$q = "UPDATE `#__vikbooking_config` SET `setting`='1' WHERE `param`='showchildren';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$psearchsuggestions."' WHERE `param`='searchsuggestions';";
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($ptokenform) || $ptokenform!="yes") {
			$q = "UPDATE `#__vikbooking_config` SET `setting`='0' WHERE `param`='tokenform';";
		} else {
			$q = "UPDATE `#__vikbooking_config` SET `setting`='1' WHERE `param`='tokenform';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_texts` SET `setting`=".$dbo->quote($pfooterordmail)." WHERE `param`='footerordmail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_texts` SET `setting`=".$dbo->quote($pdisabledbookingmsg)." WHERE `param`='disabledbookingmsg';";
		$dbo->setQuery($q);
		$dbo->execute();
		//terms and conditions
		$q = "SELECT `id`,`setting` FROM `#__vikbooking_texts` WHERE `param`='termsconds';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$q = "UPDATE `#__vikbooking_texts` SET `setting`=".$dbo->quote($ptermsconds)." WHERE `param`='termsconds';";
			$dbo->setQuery($q);
			$dbo->execute();
		} else {
			$q = "INSERT INTO `#__vikbooking_texts` (`param`,`exp`,`setting`) VALUES ('termsconds','Terms and Conditions',".$dbo->quote($ptermsconds).");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		//
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($padminemail)." WHERE `param`='adminemail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($psenderemail)." WHERE `param`='senderemail';";
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($pdateformat)) {
			$pdateformat="%d/%m/%Y";
		}
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pdateformat)." WHERE `param`='dateformat';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pdatesep)." WHERE `param`='datesep';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($presmodcanc)." WHERE `param`='resmodcanc';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($presmodcancmin)." WHERE `param`='resmodcancmin';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pminuteslock)." WHERE `param`='minuteslock';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pminautoremove)." WHERE `param`='minautoremove';";
		$dbo->setQuery($q);
		$dbo->execute();
		$openingh=$ptimeopenstorefh * 3600;
		$openingm=$ptimeopenstorefm * 60;
		$openingts=$openingh + $openingm;
		$closingh=$ptimeopenstoreth * 3600;
		$closingm=$ptimeopenstoretm * 60;
		$closingts=$closingh + $closingm;
		//check if the check-in/out times have changed and if there are future bookings with the old time to prevent availability errors
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='timeopenstore';";
		$dbo->setQuery($q);
		$dbo->execute();
		$prevtimes = $dbo->loadResult();
		if ($prevtimes != $openingts."-".$closingts) {
			$q = "SELECT `id` FROM `#__vikbooking_orders` WHERE `checkout`>".time().";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				VikError::raiseWarning('', JText::translate('VBOCONFIGWARNDIFFCHECKINOUT'));
				/**
				 * VBO 1.10 Patch - we concatenate a button to unify the check-in/out times
				 * for all reservations to avoid issues with the availability.
				 * 
				 * @since 	August 29th 2018
				 */
				VikError::raiseWarning('', '<br/><a href="index.php?option=com_vikbooking&task=unifycheckinout&fh='.$ptimeopenstorefh.'&fm='.$ptimeopenstorefm.'&th='.$ptimeopenstoreth.'&tm='.$ptimeopenstoretm.'" class="btn btn-large btn-warning">'.JText::translate('VBAPPLY').'</a>');
				//
			}
		}
		//
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$openingts."-".$closingts."' WHERE `param`='timeopenstore';";
		$dbo->setQuery($q);
		$dbo->execute();
		//set the hours of extended gratuity period to the difference between checkin and checkout if checkout is later
		$phoursmorebookingback = "0";
		if ($closingts > $openingts) {
			$diffcheck = ($closingts - $openingts) / 3600;
			$phoursmorebookingback = ceil($diffcheck);
		}
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$phoursmorebookingback."' WHERE `param`='hoursmorebookingback';";
		$dbo->setQuery($q);
		$dbo->execute();
		$phoursmoreroomavail = "0";
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$phoursmoreroomavail."' WHERE `param`='hoursmoreroomavail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$pmultilang."' WHERE `param`='multilang';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".($prequirelogin == "1" ? "1" : "0")."' WHERE `param`='requirelogin';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".($pautoroomunit == 1 ? "1" : "0")."' WHERE `param`='autoroomunit';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".(string)$ptodaybookings."' WHERE `param`='todaybookings';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".(string)$ploadbootstrap."' WHERE `param`='bootstrap';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".(string)$pusefa."' WHERE `param`='usefa';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$ploadjquery."' WHERE `param`='loadjquery';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$pcalendar."' WHERE `param`='calendar';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$penablecoupons."' WHERE `param`='enablecoupons';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$penablepin."' WHERE `param`='enablepin';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$pmindaysadvance."' WHERE `param`='mindaysadvance';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$pautodefcalnights."' WHERE `param`='autodefcalnights';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$pnumrooms."' WHERE `param`='numrooms';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$confnumadults."' WHERE `param`='numadults';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$confnumchildren."' WHERE `param`='numchildren';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".json_encode($closing_dates)."' WHERE `param`='closingdates';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$psmartsearch."' WHERE `param`='smartsearch';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$maxdate_str."' WHERE `param`='maxdate';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pcronkey)." WHERE `param`='cronkey';";
		$dbo->setQuery($q);
		$dbo->execute();
		
		$pfronttitle = VikRequest::getString('fronttitle', '', 'request');
		$pfronttitletag = VikRequest::getString('fronttitletag', '', 'request');
		$pfronttitletagclass = VikRequest::getString('fronttitletagclass', '', 'request');
		$pshowfooter = VikRequest::getString('showfooter', '', 'request');
		$pintromain = VikRequest::getString('intromain', '', 'request', VIKREQUEST_ALLOWHTML);
		$pclosingmain = VikRequest::getString('closingmain', '', 'request', VIKREQUEST_ALLOWHTML);
		$pcurrencyname = VikRequest::getString('currencyname', '', 'request', VIKREQUEST_ALLOWHTML);
		$pcurrencysymb = VikRequest::getString('currencysymb', '', 'request', VIKREQUEST_ALLOWHTML);
		$pcurrencycodepp = VikRequest::getString('currencycodepp', '', 'request');
		$pnumdecimals = VikRequest::getString('numdecimals', '', 'request');
		$pnumdecimals = intval($pnumdecimals);
		$pdecseparator = VikRequest::getString('decseparator', '', 'request');
		$pdecseparator = empty($pdecseparator) ? '.' : $pdecseparator;
		$pthoseparator = VikRequest::getString('thoseparator', '', 'request');
		$numberformatstr = $pnumdecimals.':'.$pdecseparator.':'.$pthoseparator;
		$pshowpartlyreserved = VikRequest::getString('showpartlyreserved', '', 'request');
		$pshowpartlyreserved = $pshowpartlyreserved == "yes" ? 1 : 0;
		$pshowcheckinoutonly = VikRequest::getInt('showcheckinoutonly', '', 'request');
		$pshowcheckinoutonly = $pshowcheckinoutonly > 0 ? 1 : 0;
		$pnumcalendars = VikRequest::getInt('numcalendars', '', 'request');
		$pnumcalendars = $pnumcalendars > -1 ? $pnumcalendars : 3;
		$pthumbsize = VikRequest::getInt('thumbsize', '', 'request');
		$pfirstwday = VikRequest::getString('firstwday', '', 'request');
		$pfirstwday = intval($pfirstwday) >= 0 && intval($pfirstwday) <= 6 ? $pfirstwday : '0';
		$pbctagname = VikRequest::getVar('bctagname', array());
		$pbctagcolor = VikRequest::getVar('bctagcolor', array());
		$pbctagrule = VikRequest::getVar('bctagrule', array());
		$bctags_arr = array();
		$bctags_rules = array();
		if (count($pbctagname) > 0) {
			foreach ($pbctagname as $bctk => $bctv) {
				if (!empty($bctv) && !empty($pbctagcolor[$bctk]) && strlen($pbctagrule[$bctk]) > 0) {
					if (intval($pbctagrule[$bctk]) == 0 || !in_array($pbctagrule[$bctk], $bctags_rules)) {
						$bctags_rules[] = $pbctagrule[$bctk];
						$bctags_arr[] = array('color' => $pbctagcolor[$bctk], 'name' => $bctv, 'rule' => $pbctagrule[$bctk]);
					}
				}
			}
		}
		//theme
		$ptheme = VikRequest::getString('theme', '', 'request');
		if (empty($ptheme) || $ptheme == 'default') {
			$ptheme = 'default';
		} else {
			$validtheme = false;
			$themes = glob(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.'*');
			if (count($themes) > 0) {
				$strip = VBO_SITE_PATH.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR;
				foreach ($themes as $th) {
					if (is_dir($th)) {
						$tname = str_replace($strip, '', $th);
						if ($tname == $ptheme) {
							$validtheme = true;
							break;
						}
					}
				}
			}
			if ($validtheme == false) {
				$ptheme = 'default';
			}
		}
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($ptheme)." WHERE `param`='theme';";
		$dbo->setQuery($q);
		$dbo->execute();
		//
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pshowpartlyreserved)." WHERE `param`='showpartlyreserved';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pshowcheckinoutonly)." WHERE `param`='showcheckinoutonly';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pnumcalendars)." WHERE `param`='numcalendars';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pthumbsize)." WHERE `param`='thumbsize';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pfirstwday)." WHERE `param`='firstwday';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_texts` SET `setting`=".$dbo->quote($pfronttitle)." WHERE `param`='fronttitle';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pfronttitletag)." WHERE `param`='fronttitletag';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pfronttitletagclass)." WHERE `param`='fronttitletagclass';";
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($pshowfooter) || $pshowfooter!="yes") {
			$q = "UPDATE `#__vikbooking_config` SET `setting`='0' WHERE `param`='showfooter';";
		} else {
			$q = "UPDATE `#__vikbooking_config` SET `setting`='1' WHERE `param`='showfooter';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_texts` SET `setting`=".$dbo->quote($pintromain)." WHERE `param`='intromain';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_texts` SET `setting`=".$dbo->quote($pclosingmain)." WHERE `param`='closingmain';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pcurrencyname)." WHERE `param`='currencyname';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pcurrencysymb)." WHERE `param`='currencysymb';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pcurrencycodepp)." WHERE `param`='currencycodepp';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($numberformatstr)." WHERE `param`='numberformat';";
		$dbo->setQuery($q);
		$dbo->execute();
		//Bookings color tags
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='bookingsctags';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote(json_encode($bctags_arr))." WHERE `param`='bookingsctags';";
			$dbo->setQuery($q);
			$dbo->execute();
		} else {
			$q = "INSERT INTO `#__vikbooking_config` (`param`,`setting`) VALUES ('bookingsctags',".$dbo->quote(json_encode($bctags_arr)).");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		//
		
		$pivainclusa = VikRequest::getString('ivainclusa', '', 'request');
		$ptaxsummary = VikRequest::getString('taxsummary', '', 'request');
		$ptaxsummary = empty($ptaxsummary) || $ptaxsummary != "yes" ? "0" : "1";
		$pccpaypal = VikRequest::getString('ccpaypal', '', 'request');
		$ppaytotal = VikRequest::getString('paytotal', '', 'request');
		$ppayaccpercent = VikRequest::getString('payaccpercent', '', 'request');
		$ptypedeposit = VikRequest::getString('typedeposit', '', 'request');
		$ptypedeposit = $ptypedeposit == 'fixed' ? 'fixed' : 'pcent';
		$pdepoverrides = VikRequest::getString('depoverrides', '', 'request');
		$ppaymentname = VikRequest::getString('paymentname', '', 'request');
		$pmultipay = VikRequest::getString('multipay', '', 'request');
		$pmultipay = $pmultipay == "yes" ? 1 : 0;
		$pdepifdaysadv = VikRequest::getInt('depifdaysadv', '', 'request');
		$pnodepnonrefund = VikRequest::getInt('nodepnonrefund', '', 'request');
		$pdepcustchoice = VikRequest::getString('depcustchoice', '', 'request');
		$pdepcustchoice = $pdepcustchoice == "yes" ? 1 : 0;
		if (empty($pivainclusa) || $pivainclusa!="yes") {
			$q = "UPDATE `#__vikbooking_config` SET `setting`='0' WHERE `param`='ivainclusa';";
		} else {
			$q = "UPDATE `#__vikbooking_config` SET `setting`='1' WHERE `param`='ivainclusa';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$ptaxsummary."' WHERE `param`='taxsummary';";
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($ppaytotal) || $ppaytotal!="yes") {
			$q = "UPDATE `#__vikbooking_config` SET `setting`='0' WHERE `param`='paytotal';";
		} else {
			$q = "UPDATE `#__vikbooking_config` SET `setting`='1' WHERE `param`='paytotal';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pccpaypal)." WHERE `param`='ccpaypal';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_texts` SET `setting`=".$dbo->quote($ppaymentname)." WHERE `param`='paymentname';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($ppayaccpercent)." WHERE `param`='payaccpercent';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($ptypedeposit)." WHERE `param`='typedeposit';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pdepoverrides)." WHERE `param`='depoverrides';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$pmultipay."' WHERE `param`='multipay';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$pdepifdaysadv."' WHERE `param`='depifdaysadv';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$pnodepnonrefund."' WHERE `param`='nodepnonrefund';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$pdepcustchoice."' WHERE `param`='depcustchoice';";
		$dbo->setQuery($q);
		$dbo->execute();
		
		$pdisclaimer = VikRequest::getString('disclaimer', '', 'request', VIKREQUEST_ALLOWHTML);
		$psendemailwhen = VikRequest::getInt('sendemailwhen', '', 'request');
		$psendemailwhen = $psendemailwhen > 1 ? 2 : 1;
		$pattachical = VikRequest::getInt('attachical', 0, 'request');
		$pattachical = $pattachical >= 0 && $pattachical <= 3 ? $pattachical : 1;
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($psendemailwhen)." WHERE `param`='emailsendwhen';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pattachical)." WHERE `param`='attachical';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_texts` SET `setting`=".$dbo->quote($pdisclaimer)." WHERE `param`='disclaimer';";
		$dbo->setQuery($q);
		$dbo->execute();

		//SMS APIs
		$psmsapi = VikRequest::getString('smsapi', '', 'request');
		$psmsautosend = VikRequest::getString('smsautosend', '', 'request');
		$psmsautosend = intval($psmsautosend) > 0 ? 1 : 0;
		$psmssendto = VikRequest::getVar('smssendto', array());
		$sms_sendto = array();
		foreach ($psmssendto as $sto) {
			if (in_array($sto, array('admin', 'customer'))) {
				$sms_sendto[] = $sto;
			}
		}
		$psmssendwhen = VikRequest::getInt('smssendwhen', '', 'request');
		$psmssendwhen = $psmssendwhen > 1 ? 2 : 1;
		$psmsadminphone = VikRequest::getString('smsadminphone', '', 'request');
		$psmsadmintpl = VikRequest::getString('smsadmintpl', '', 'request', VIKREQUEST_ALLOWRAW);
		$psmscustomertpl = VikRequest::getString('smscustomertpl', '', 'request', VIKREQUEST_ALLOWRAW);
		$psmsadmintplpend = VikRequest::getString('smsadmintplpend', '', 'request', VIKREQUEST_ALLOWRAW);
		$psmscustomertplpend = VikRequest::getString('smscustomertplpend', '', 'request', VIKREQUEST_ALLOWRAW);
		$psmsadmintplcanc = VikRequest::getString('smsadmintplcanc', '', 'request', VIKREQUEST_ALLOWRAW);
		$psmscustomertplcanc = VikRequest::getString('smscustomertplcanc', '', 'request', VIKREQUEST_ALLOWRAW);
		$viksmsparams = VikRequest::getVar('viksmsparams', array());
		$smsparamarr = array();
		if (count($viksmsparams) > 0) {
			foreach ($viksmsparams as $setting => $cont) {
				if (strlen($setting) > 0) {
					$smsparamarr[$setting] = $cont;
				}
			}
		}
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$psmsapi."' WHERE `param`='smsapi';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$psmsautosend."' WHERE `param`='smsautosend';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote(json_encode($sms_sendto))." WHERE `param`='smssendto';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$psmssendwhen."' WHERE `param`='smssendwhen';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$psmsadminphone."' WHERE `param`='smsadminphone';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote(json_encode($smsparamarr))." WHERE `param`='smsparams';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_texts` SET `setting`=".$dbo->quote($psmsadmintpl)." WHERE `param`='smsadmintpl';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_texts` SET `setting`=".$dbo->quote($psmscustomertpl)." WHERE `param`='smscustomertpl';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_texts` SET `setting`=".$dbo->quote($psmsadmintplpend)." WHERE `param`='smsadmintplpend';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_texts` SET `setting`=".$dbo->quote($psmscustomertplpend)." WHERE `param`='smscustomertplpend';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_texts` SET `setting`=".$dbo->quote($psmsadmintplcanc)." WHERE `param`='smsadmintplcanc';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_texts` SET `setting`=".$dbo->quote($psmscustomertplcanc)." WHERE `param`='smscustomertplcanc';";
		$dbo->setQuery($q);
		$dbo->execute();
		//
		
		$mainframe = JFactory::getApplication();
		$mainframe->enqueueMessage(JText::translate('VBSETTINGSAVED'));
		$mainframe->redirect("index.php?option=com_vikbooking&task=config");
	}

	/**
	 * Unify the check-in and check-out times for all reservations.
	 * 
	 * @since 	1.10 - Patch August 29th 2018
	 */
	public function unifycheckinout() {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$fh = VikRequest::getInt('fh', 12, 'request');
		$fm = VikRequest::getInt('fm', 0, 'request');
		$th = VikRequest::getInt('th', 10, 'request');
		$tm = VikRequest::getInt('tm', 0, 'request');
		$totmod = 0;
		$totbookmod = 0;
		$q = "SELECT * FROM `#__vikbooking_busy`;";
		$dbo->setQuery($q);
		$dbo->execute();
		$records = $dbo->loadAssocList();
		foreach ($records as $v) {
			$info_start = getdate($v['checkin']);
			$info_end = getdate($v['checkout']);
			$new_start = mktime($fh, $fm, 0, $info_start['mon'], $info_start['mday'], $info_start['year']);
			$new_end = mktime($th, $tm, 0, $info_end['mon'], $info_end['mday'], $info_end['year']);
			$q = "UPDATE `#__vikbooking_busy` SET `checkin`=".$new_start.",`checkout`=".$new_end.",`realback`=".$new_end." WHERE `id`=".$v['id']." LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			$totmod++;
		}

		$q = "SELECT * FROM `#__vikbooking_orders`;";
		$dbo->setQuery($q);
		$dbo->execute();
		$records = $dbo->loadAssocList();
		foreach ($records as $v) {
			$info_start = getdate($v['checkin']);
			$info_end = getdate($v['checkout']);
			$new_start = mktime($fh, $fm, 0, $info_start['mon'], $info_start['mday'], $info_start['year']);
			$new_end = mktime($th, $tm, 0, $info_end['mon'], $info_end['mday'], $info_end['year']);
			$q = "UPDATE `#__vikbooking_orders` SET `checkin`=".$new_start.",`checkout`=".$new_end." WHERE `id`=".$v['id']." LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			$totbookmod++;
		}
		$mainframe->enqueueMessage('OK: '.$totbookmod);
		$mainframe->redirect("index.php?option=com_vikbooking&task=config");
	}

	public function savetmplfile() {
		$fpath = VikRequest::getString('path', '', 'request', VIKREQUEST_ALLOWRAW);
		$pcont = VikRequest::getString('cont', '', 'request', VIKREQUEST_ALLOWRAW);
		$mainframe = JFactory::getApplication();
		$exists = file_exists($fpath) ? true : false;
		if (!$exists) {
			$fpath = urldecode($fpath);
		}
		$fpath = file_exists($fpath) ? $fpath : '';
		if (!empty($fpath)) {
			$fp = fopen($fpath, 'wb');
			$byt = (int)fwrite($fp, $pcont);
			fclose($fp);
			if ($byt > 0) {
				$mainframe->enqueueMessage(JText::translate('VBOUPDTMPLFILEOK'));
				/**
				 * @wponly  call the UpdateManager Class to temporarily store modifications made to template files
				 */
				VikBookingUpdateManager::storeTemplateContent($fpath, $pcont);
				//
			} else {
				VikError::raiseWarning('', JText::translate('VBOUPDTMPLFILENOBYTES'));
			}
		} else {
			VikError::raiseWarning('', JText::translate('VBOUPDTMPLFILEERR'));
		}
		$mainframe->redirect("index.php?option=com_vikbooking&task=edittmplfile&path=".$fpath."&tmpl=component");

		exit;
	}

	public function edittmplfile() {
		//modal box, so we do not set menu or footer
	
		VikRequest::setVar('view', VikRequest::getCmd('view', 'edittmplfile'));
	
		parent::display();
	}

	public function tmplfileprew() {
		//modal box, so we do not set menu or footer
	
		VikRequest::setVar('view', VikRequest::getCmd('view', 'tmplfileprew'));
	
		parent::display();
	}

	public function invoices() {
		VikBookingHelper::printHeader("invoices");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'invoices'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function newmaninvoice() {
		VikBookingHelper::printHeader("invoices");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managemaninvoice'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function editmaninvoice() {
		VikBookingHelper::printHeader("invoices");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managemaninvoice'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function savemaninvoice() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$this->do_storemaninvoice('save');
		$mainframe = JFactory::getApplication();
		$mainframe->enqueueMessage(JText::sprintf('VBOTOTINVOICESGEND', 1, 0));
		$pgoto = VikRequest::getString('goto', '', 'request', VIKREQUEST_ALLOWRAW);
		if (!empty($pgoto)) {
			$mainframe->redirect(base64_decode($pgoto));
			exit;
		}
		$mainframe->redirect("index.php?option=com_vikbooking&task=invoices");
	}

	public function updatemaninvoice() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$invid = VikRequest::getInt('whereup', 0, 'request');
		$this->do_storemaninvoice('update', $invid);
		$mainframe = JFactory::getApplication();
		$mainframe->enqueueMessage(JText::sprintf('VBOTOTINVOICESGEND', 1, 0));
		$pgoto = VikRequest::getString('goto', '', 'request', VIKREQUEST_ALLOWRAW);
		if (!empty($pgoto)) {
			$mainframe->redirect(base64_decode($pgoto));
			exit;
		}
		$mainframe->redirect("index.php?option=com_vikbooking&task=invoices");
	}

	public function updatemaninvoicestay() {
		$invid = VikRequest::getInt('whereup', 0, 'request');
		$this->do_storemaninvoice('updatestay', $invid);
		$mainframe = JFactory::getApplication();
		$mainframe->enqueueMessage(JText::sprintf('VBOTOTINVOICESGEND', 1, 0));
		$pgoto = VikRequest::getString('goto', '', 'request', VIKREQUEST_ALLOWRAW);
		if (!empty($pgoto)) {
			$mainframe->redirect(base64_decode($pgoto));
			exit;
		}
		$mainframe->redirect("index.php?option=com_vikbooking&task=editmaninvoice&cid[]=".$invid);
	}

	private function do_storemaninvoice($action, $invid = 0) {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$pinvoice_num = VikRequest::getInt('invoice_num', '', 'request');
		$pinvoice_num = $pinvoice_num <= 0 ? 1 : $pinvoice_num;
		$pinvoice_suff = VikRequest::getString('invoice_suff', '', 'request');
		$pcompany_info = VikRequest::getString('company_info', '', 'request', VIKREQUEST_ALLOWHTML);
		$pcompany_info = strpos($pcompany_info, '<') !== false ? $pcompany_info : nl2br($pcompany_info);
		$pinvoice_notes = VikRequest::getString('invoice_notes', '', 'request', VIKREQUEST_ALLOWHTML);
		$pinvoice_notes = strpos($pinvoice_notes, '<') !== false ? $pinvoice_notes : nl2br($pinvoice_notes);
		$pidcustomer = VikRequest::getInt('idcustomer', '', 'request');
		$error_uri = strpos($action, 'update') !== false && !empty($invid) ? 'index.php?option=com_vikbooking&task=editmaninvoice&cid[]='.$invid : 'index.php?option=com_vikbooking&task=newmaninvoice';
		if (empty($pidcustomer)) {
			VikError::raiseWarning('', JText::translate('VBNOCUSTOMERS'));
			$mainframe->redirect($error_uri);
			exit;
		}
		$services = VikRequest::getVar('service', array());
		$nets = VikRequest::getVar('net', array());
		$aliqs = VikRequest::getVar('aliq', array());
		$taxs = VikRequest::getVar('tax', array());
		$tots = VikRequest::getVar('tot', array());
		$ptotalnet = VikRequest::getFloat('totalnet', 0, 'request');
		$ptotaltax = VikRequest::getFloat('totaltax', 0, 'request');
		$ptotaltot = VikRequest::getFloat('totaltot', 0, 'request');
		if (!count($services) || count($services) != count($nets) || count($services) != count($taxs) || count($services) != count($tots)) {
			VikError::raiseWarning('', 'Missing data.');
			$mainframe->redirect($error_uri);
			exit;
		}
		$rawcont = array(
			'rows' => array(),
			'totalnet' => $ptotalnet,
			'totaltax' => $ptotaltax,
			'totaltot' => $ptotaltot,
			'notes' => $pinvoice_notes,
		);
		foreach ($services as $k => $service) {
			if (empty($service)) {
				continue;
			}
			array_push($rawcont['rows'], array(
				'service' => $service,
				'net' => (float)$nets[$k],
				'aliq' => (isset($aliqs[$k]) ? (float)$aliqs[$k] : 0),
				'tax' => (float)$taxs[$k],
				'tot' => (float)$tots[$k],
			));
		}
		// store/update manual invoice
		$nowts = time();
		$retval = 0;
		if (strpos($action, 'save') !== false) {
			$pdffname = $nowts . '_' . rand() . '.pdf';
			$q = "INSERT INTO `#__vikbooking_invoices` (`number`,`file_name`,`idorder`,`idcustomer`,`created_on`,`for_date`,`rawcont`) VALUES (".$dbo->quote($pinvoice_num.$pinvoice_suff).", ".$dbo->quote($pdffname).", ".($pinvoice_num - ($pinvoice_num * 2)).", ".$dbo->quote($pidcustomer).", ".$nowts.", ".$nowts.", ".$dbo->quote(json_encode($rawcont)).");";
			$dbo->setQuery($q);
			$dbo->execute();
			$retval = $dbo->insertid();
		} else {
			// fetch old record
			$q = "SELECT * FROM `#__vikbooking_invoices` WHERE `id`=".(int)$invid.";";
			$dbo->setQuery($q);
			$dbo->execute();
			if (!$dbo->getNumRows()) {
				VikError::raiseWarning('', JText::translate('VBNOINVOICESFOUND'));
				$mainframe->redirect($error_uri);
				exit;
			}
			$previnvoice = $dbo->loadAssoc();
			//
			$q = "UPDATE `#__vikbooking_invoices` SET `number`=".$dbo->quote($pinvoice_num.$pinvoice_suff).",`file_name`=".$dbo->quote($previnvoice['file_name']).",`idorder`=".($pinvoice_num - ($pinvoice_num * 2)).",`idcustomer`=".$dbo->quote($pidcustomer).",`created_on`=".$nowts.",`rawcont`=".$dbo->quote(json_encode($rawcont))." WHERE `id`=".(int)$previnvoice['id'].";";
			$dbo->setQuery($q);
			$dbo->execute();
			$retval = $previnvoice['id'];
		}
		// update config values for the invoice
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pcompany_info)." WHERE `param`='invcompanyinfo';";
		$dbo->setQuery($q);
		$dbo->execute();
		// generate the custom invoice
		$result = VikBooking::generateCustomInvoice($retval);
		//
		$nextinv = VikBooking::getNextInvoiceNumber();
		$updatenum = ($pinvoice_num >= $nextinv);
		if ($updatenum) {
			/**
			 * IMPORTANT: update the next invoice number after calling the e-Invocing drivers
			 * to avoid conflicts with the drivers for the e-invoices generation.
			 */
			$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote(($pinvoice_num - 1))." WHERE `param`='invoiceinum';";
			$dbo->setQuery($q);
			$dbo->execute();
		}

		return $retval;
	}

	public function downloadinvoices() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids) > 0) {
			$dbo = JFactory::getDBO();
			$q = "SELECT * FROM `#__vikbooking_invoices` WHERE `id` IN (".implode(', ', $ids).");";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$invoices = $dbo->loadAssocList();
				if (!(count($invoices) > 1)) {
					//Single Invoice Download
					if (file_exists(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'invoices'.DIRECTORY_SEPARATOR.'generated'.DIRECTORY_SEPARATOR.$invoices[0]['file_name'])) {
						header("Content-type:application/pdf");
						header("Content-Disposition:attachment;filename=".$invoices[0]['file_name']);
						readfile(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'invoices'.DIRECTORY_SEPARATOR.'generated'.DIRECTORY_SEPARATOR.$invoices[0]['file_name']);
						exit;
					}
				} else {
					//Multiple Invoices Download
					$to_zip = array();
					foreach ($invoices as $k => $invoice) {
						$to_zip[$k]['name'] = $invoice['file_name'];
						$to_zip[$k]['path'] = VBO_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'invoices'.DIRECTORY_SEPARATOR.'generated'.DIRECTORY_SEPARATOR.$invoice['file_name'];
					}
					if (class_exists('ZipArchive')) {
						$zip_path = VBO_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'invoices'.DIRECTORY_SEPARATOR.'generated'.DIRECTORY_SEPARATOR.date('Y-m-d').'-invoices.zip';
						$zip = new ZipArchive;
						$zip->open($zip_path, ZipArchive::CREATE);
						foreach ($to_zip as $k => $zipv) {
							$zip->addFile($zipv['path'], $zipv['name']);
						}
						$zip->close();
						header("Content-type:application/zip");
						header("Content-Disposition:attachment;filename=".date('Y-m-d').'-invoices.zip');
						header("Content-Length:".filesize($zip_path));
						readfile($zip_path);
						unlink($zip_path);
						exit;
					} else {
						//Class ZipArchive does not exist
						VikError::raiseWarning('', 'Class ZipArchive does not exist on your server. Download the files one by one.');
					}
				}
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=invoices");
	}

	public function resendinvoices() {
		$ids = VikRequest::getVar('cid', array(0));
		$mainframe = JFactory::getApplication();
		if (!(count($ids) > 0)) {
			$mainframe->redirect("index.php?option=com_vikbooking&task=invoices");
			exit;
		}
		$dbo = JFactory::getDBO();
		$invoices = array();
		$q = "SELECT `i`.*,`o`.`custmail`,`c`.`email` AS `customer_email`, CONCAT_WS(' ',`c`.`first_name`,`c`.`last_name`) AS `customer_name`,`c`.`country` AS `customer_country`,`nat`.`country_name` ".
			"FROM `#__vikbooking_invoices` AS `i` " .
			"LEFT JOIN `#__vikbooking_orders` `o` ON `o`.`id`=`i`.`idorder` " .
			"LEFT JOIN `#__vikbooking_customers` `c` ON `c`.`id`=`i`.`idcustomer` " .
			"LEFT JOIN `#__vikbooking_countries` `nat` ON `nat`.`country_3_code`=`c`.`country` " .
			"WHERE `i`.`id` IN (".implode(', ', $ids).") AND (`i`.`idorder` < 0 OR (`o`.`status`='confirmed' AND `o`.`total` > 0)) ORDER BY `o`.`id` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$invoices = $dbo->loadAssocList();
		}
		if (!(count($invoices) > 0)) {
			VikError::raiseWarning('', JText::translate('VBOGENINVERRNOBOOKINGS'));
			$mainframe->redirect("index.php?option=com_vikbooking&task=invoices");
			exit;
		}
		$tot_generated = 0;
		$tot_sent = 0;
		foreach ($invoices as $bkey => $invoice) {
			$invoice['custmail'] = empty($invoice['custmail']) && !empty($invoice['customer_email']) ? $invoice['customer_email'] : $invoice['custmail'];
			$invoices[$bkey] = $invoice;
			$send_res = VikBooking::sendBookingInvoice($invoice['id'], $invoice);
			if ($send_res !== false) {
				$tot_sent++;
			}
		}
		$mainframe->enqueueMessage(JText::sprintf('VBOTOTINVOICESGEND', $tot_generated, $tot_sent));
		$mainframe->redirect("index.php?option=com_vikbooking&task=invoices");
	}

	public function removeinvoices() {
		$ids = VikRequest::getVar('cid', array(0));
		$tot_removed = 0;
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d){
				$q = "SELECT * FROM `#__vikbooking_invoices` WHERE `id`=".(int)$d.";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$cur_invoice = $dbo->loadAssoc();
					if (file_exists(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'invoices'.DIRECTORY_SEPARATOR.'generated'.DIRECTORY_SEPARATOR.$cur_invoice['file_name'])) {
						unlink(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'invoices'.DIRECTORY_SEPARATOR.'generated'.DIRECTORY_SEPARATOR.$cur_invoice['file_name']);
					}
					$q = "DELETE FROM `#__vikbooking_invoices` WHERE `id`=".(int)$d.";";
					$dbo->setQuery($q);
					$dbo->execute();
					$tot_removed++;
				}
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->enqueueMessage(JText::sprintf('VBOTOTINVOICESRMVD', $tot_removed));
		$mainframe->redirect("index.php?option=com_vikbooking&task=invoices");
	}

	public function geninvoices() {
		$ids = VikRequest::getVar('cid', array(0));
		$mainframe = JFactory::getApplication();
		if (!count($ids)) {
			$mainframe->redirect("index.php?option=com_vikbooking&task=orders");
			exit;
		}
		$dbo = JFactory::getDBO();
		$pinvoice_num = VikRequest::getInt('invoice_num', '', 'request');
		$pinvoice_num = $pinvoice_num <= 0 ? 1 : $pinvoice_num;
		$pinvoice_suff = VikRequest::getString('invoice_suff', '', 'request');
		$pinvoice_date = VikRequest::getString('invoice_date', '', 'request');
		$pcompany_info = VikRequest::getString('company_info', '', 'request', VIKREQUEST_ALLOWHTML);
		$pcompany_info = strpos($pcompany_info, '<') !== false ? $pcompany_info : nl2br($pcompany_info);
		$pinvoice_send = VikRequest::getInt('invoice_send', '', 'request');
		$pinvoice_send = $pinvoice_send > 0 ? true : false;
		$increment_inv = true;
		$pconfirmgen = VikRequest::getInt('confirmgen', '', 'request');
		//if editing an invoice (re-creating an existing invoice for a booking), do not increment the invoice number
		if (count($ids) == 1) {
			$q = "SELECT `number` FROM `#__vikbooking_invoices` WHERE `idorder`=".(int)$ids[0].";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$increment_inv = false;
			}
		}
		//
		$bookings = array();
		$q = "SELECT `o`.*,`co`.`idcustomer`,CONCAT_WS(' ',`c`.`first_name`,`c`.`last_name`) AS `customer_name`,`c`.`pin` AS `customer_pin`,`nat`.`country_name` FROM `#__vikbooking_orders` AS `o` LEFT JOIN `#__vikbooking_customers_orders` `co` ON `co`.`idorder`=`o`.`id` LEFT JOIN `#__vikbooking_customers` `c` ON `c`.`id`=`co`.`idcustomer` LEFT JOIN `#__vikbooking_countries` `nat` ON `nat`.`country_3_code`=`o`.`country` WHERE `o`.`id` IN (".implode(', ', $ids).") AND `o`.`status`='confirmed' AND `o`.`total` > 0 ORDER BY `o`.`id` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$bookings = $dbo->loadAssocList();
		}
		if (!count($bookings)) {
			VikError::raiseWarning('', JText::translate('VBOGENINVERRNOBOOKINGS'));
			$mainframe->redirect("index.php?option=com_vikbooking&task=orders");
			exit;
		}
		$tot_generated = 0;
		$tot_sent = 0;
		foreach ($bookings as $bkey => $booking) {
			$gen_res = VikBooking::generateBookingInvoice($booking, $pinvoice_num, $pinvoice_suff, $pinvoice_date, $pcompany_info);
			if ($gen_res !== false && $gen_res > 0) {
				$tot_generated++;
				$pinvoice_num++;
				if ($pinvoice_send) {
					$send_res = VikBooking::sendBookingInvoice($gen_res, $booking);
					if ($send_res !== false) {
						$tot_sent++;
					}
				}
			} else {
				VikError::raiseWarning('', JText::sprintf('VBOGENINVERRBOOKING', $booking['id']));
			}
		}
		if ($tot_generated > 0 && $increment_inv === true) {
			/**
			 * IMPORTANT: update the next invoice number after calling generateBookingInvoice()
			 * to avoid conflicts with the drivers for the e-invoices generation.
			 */
			$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote(($pinvoice_num - 1))." WHERE `param`='invoiceinum';";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pinvoice_suff)." WHERE `param`='invoicesuffix';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pcompany_info)." WHERE `param`='invcompanyinfo';";
		$dbo->setQuery($q);
		$dbo->execute();
		$mainframe->enqueueMessage(JText::sprintf('VBOTOTINVOICESGEND', $tot_generated, $tot_sent));
		if ($pconfirmgen > 0) {
			$mainframe->redirect("index.php?option=com_vikbooking&task=invoices&show=".$pconfirmgen);
		} elseif (count($bookings) === 1) {
			// go to the back-end booking details page
			$mainframe->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=" . $bookings[0]['id']);
		} else {
			$mainframe->redirect("index.php?option=com_vikbooking&task=orders");
		}
	}

	public function optionals() {
		VikBookingHelper::printHeader("6");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'optionals'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function newoptionals() {
		VikBookingHelper::printHeader("6");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageoptional'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function editoptional() {
		VikBookingHelper::printHeader("6");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageoptional'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function updateoptional() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$this->do_updateoptional();
	}

	public function updateoptionalstay() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$this->do_updateoptional(true);
	}

	private function do_updateoptional($stay = false) {
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();
		$poptname = VikRequest::getString('optname', '', 'request');
		$poptdescr = VikRequest::getString('optdescr', '', 'request', VIKREQUEST_ALLOWHTML);
		$poptcost = VikRequest::getFloat('optcost', '', 'request');
		$poptperday = VikRequest::getString('optperday', '', 'request');
		$poptperperson = VikRequest::getString('optperperson', '', 'request');
		$pmaxprice = VikRequest::getFloat('maxprice', '', 'request');
		$popthmany = VikRequest::getString('opthmany', '', 'request');
		$poptaliq = VikRequest::getInt('optaliq', '', 'request');
		$pwhereup = VikRequest::getString('whereup', '', 'request');
		$pautoresize = VikRequest::getString('autoresize', '', 'request');
		$presizeto = VikRequest::getString('resizeto', '', 'request');
		$pifchildren = VikRequest::getString('ifchildren', '', 'request');
		$pifchildren = $pifchildren == "1" ? 1 : 0;
		$pmaxquant = VikRequest::getString('maxquant', '', 'request');
		$pmaxquant = empty($pmaxquant) ? 0 : intval($pmaxquant);
		$pforcesel = VikRequest::getString('forcesel', '', 'request');
		$pforceval = VikRequest::getString('forceval', '', 'request');
		$pforcevalperday = VikRequest::getString('forcevalperday', '', 'request');
		$pforcevalperchild = VikRequest::getString('forcevalperchild', '', 'request');
		$pforcesummary = VikRequest::getString('forcesummary', '', 'request');
		$pforcesel = $pforcesel == "1" ? 1 : 0;
		$pis_citytax = VikRequest::getString('is_citytax', '', 'request');
		$pis_fee = VikRequest::getString('is_fee', '', 'request');
		$pis_citytax = $pis_citytax == "1" && $pis_fee != "1" ? 1 : 0;
		$pis_fee = $pis_fee == "1" && $pis_citytax == 0 ? 1 : 0;
		$pagefrom = VikRequest::getVar('agefrom', array());
		$pageto = VikRequest::getVar('ageto', array());
		$pagecost = VikRequest::getVar('agecost', array());
		$pagectype = VikRequest::getVar('agectype', array());
		$palwaysav = VikRequest::getInt('alwaysav', 0, 'request');
		$pavfrom = VikRequest::getString('avfrom', '', 'request');
		$pavto = VikRequest::getString('avto', '', 'request');
		$ppcentroom = VikRequest::getInt('pcentroom', 0, 'request');
		$pidrooms = VikRequest::getVar('idrooms', array());
		$optavstr = empty($palwaysav) && !empty($pavfrom) && !empty($pavto) ? VikBooking::getDateTimestamp($pavfrom, 0, 0, 0).';'.VikBooking::getDateTimestamp($pavto, 23, 59, 59) : '';
		if ($pforcesel == 1) {
			$strforceval = intval($pforceval)."-".($pforcevalperday == "1" ? "1" : "0")."-".($pforcevalperchild == "1" ? "1" : "0")."-".($pforcesummary == "1" ? "1" : "0");
		} else {
			$strforceval = "";
		}
		$minguestsnum = VikRequest::getInt('minguestsnum', 0, 'request');
		$mingueststype = VikRequest::getString('mingueststype', 'guests', 'request');
		$minguestsnum = $minguestsnum < 0 ? 0 : $minguestsnum;
		$mingueststype = !empty($mingueststype) && !in_array($mingueststype, array('adults', 'guests')) ? 'guests' : $mingueststype;
		$maxguestsnum = VikRequest::getInt('maxguestsnum', 0, 'request');
		$maxgueststype = VikRequest::getString('maxgueststype', 'guests', 'request');
		$maxguestsnum = $maxguestsnum < 0 ? 0 : $maxguestsnum;
		$maxgueststype = !empty($maxgueststype) && !in_array($maxgueststype, array('adults', 'guests')) ? 'guests' : $maxgueststype;
		$minguests = VikRequest::getInt('minguests', 0, 'request');
		$minguests_conflict = false;
		if ($minguests > 0 && $minguestsnum > 0 && $maxguestsnum > 0) {
			if ($minguestsnum >= $maxguestsnum) {
				$minguests_conflict = JText::translate('VBOMINMAXGUESTSOPTCONFL1');
			} elseif (($maxguestsnum - $minguestsnum) < 2) {
				$minguests_conflict = JText::translate('VBOMINMAXGUESTSOPTCONFL2');
			}
		}
		if (!$minguests || $minguests_conflict !== false) {
			$minguestsnum = 0;
			$maxguestsnum = 0;
			if ($minguests_conflict !== false) {
				// raise warning, but do not stop the process
				VikError::raiseWarning('', $minguests_conflict);
			}
		}
		$damagedep = VikRequest::getInt('damagedep', 0, 'request');
		$oparams = array(
			'minguestsnum' 	=> $minguestsnum,
			'mingueststype' => $mingueststype,
			'maxguestsnum' 	=> $maxguestsnum,
			'maxgueststype' => $maxgueststype,
			'damagedep' 	=> $damagedep,
		);
		/**
		 * We fetch the previous params to merge them with the new ones 
		 * in case some properties have been set somewhere else.
		 * For example, the damage deposit transmission to Booking.com.
		 */
		$cur_oparams = array();
		$q = "SELECT `oparams` FROM `#__vikbooking_optionals` WHERE `id`=" . (int)$pwhereup . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$cur_oparams = $dbo->loadResult();
			$cur_oparams = !empty($cur_oparams) ? json_decode($cur_oparams, true) : array();
			$cur_oparams = !is_array($cur_oparams) ? array() : $cur_oparams;
			// merge previous params with the new ones to get the new values
			$oparams = array_merge($cur_oparams, $oparams);
		}
		//
		if (!empty($poptname)) {
			if (intval($_FILES['optimg']['error']) == 0 && VikBooking::caniWrite(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR) && trim($_FILES['optimg']['name'])!="") {
				jimport('joomla.filesystem.file');
				$updpath = VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR;
				if (@is_uploaded_file($_FILES['optimg']['tmp_name'])) {
					$safename=JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['optimg']['name'])));
					if (file_exists($updpath.$safename)) {
						$j=1;
						while (file_exists($updpath.$j.$safename)) {
							$j++;
						}
						$pwhere=$updpath.$j.$safename;
					} else {
						$j="";
						$pwhere=$updpath.$safename;
					}
					if (!getimagesize($_FILES['optimg']['tmp_name'])) {
						@unlink($pwhere);
						$picon="";
					} else {
						VikBooking::uploadFile($_FILES['optimg']['tmp_name'], $pwhere);
						@chmod($pwhere, 0644);
						$picon=$j.$safename;
						if ($pautoresize=="1" && !empty($presizeto)) {
							$eforj = new vikResizer();
							$origmod = $eforj->proportionalImage($pwhere, $updpath.'r_'.$j.$safename, $presizeto, $presizeto);
							if ($origmod) {
								@unlink($pwhere);
								$picon='r_'.$j.$safename;
							}
						}
					}
				} else {
					$picon="";
				}
			} else {
				$picon="";
			}
			($poptperday=="each" ? $poptperday="1" : $poptperday="0");
			$poptperperson=($poptperperson=="each" ? "1" : "0");
			($popthmany=="yes" ? $popthmany="1" : $popthmany="0");
			$ageintervalstr = '';
			if ($pifchildren == 1 && count($pagefrom) > 0 && count($pagecost) > 0 && count($pagefrom) == count($pagecost)) {
				foreach ($pagefrom as $kage => $vage) {
					$afrom = intval($vage);
					$ato = intval($pageto[$kage]);
					$acost = floatval($pagecost[$kage]);
					if (strlen($vage) > 0 && strlen($pagecost[$kage]) > 0) {
						if ($ato < $afrom) $ato = $afrom;
						$ageintervalstr .= $afrom.'_'.$ato.'_'.$acost.(array_key_exists($kage, $pagectype) && strpos($pagectype[$kage], '%') !== false ? '_%'.(strpos($pagectype[$kage], '%b') !== false ? 'b' : '') : '').';;';
					}
				}
				$ageintervalstr = rtrim($ageintervalstr, ';;');
				if (!empty($ageintervalstr)) {
					$pforcesel = 1;
				}
			}
			$q = "UPDATE `#__vikbooking_optionals` SET `name`=".$dbo->quote($poptname).",`descr`=".$dbo->quote($poptdescr).",`cost`=".$dbo->quote($poptcost).",`perday`=".$dbo->quote($poptperday).",`hmany`=".$dbo->quote($popthmany).",".(strlen($picon)>0 ? "`img`='".$picon."'," : "")."`idiva`=".$dbo->quote($poptaliq).", `maxprice`=".$dbo->quote($pmaxprice).", `forcesel`='".$pforcesel."', `forceval`='".$strforceval."', `perperson`='".$poptperperson."', `ifchildren`='".$pifchildren."', `maxquant`='".$pmaxquant."', `ageintervals`='".$ageintervalstr."',`is_citytax`=".$pis_citytax.",`is_fee`=".$pis_fee.",`alwaysav`=".$dbo->quote($optavstr).",`pcentroom`=".$dbo->quote($ppcentroom).",`oparams`=" . $dbo->quote(json_encode($oparams)) . " WHERE `id`=".$dbo->quote($pwhereup).";";
			$dbo->setQuery($q);
			$dbo->execute();
			$app->enqueueMessage(JText::translate('VBOSUCCUPDOPTION'));

			// assign/unset option-rooms relations
			$rooms_with_opt = array();
			if (count($pidrooms)) {
				// assign this new option to the requested rooms
				foreach ($pidrooms as $idroom) {
					if (empty($idroom)) {
						continue;
					}
					$q = "SELECT `id`, `idopt` FROM `#__vikbooking_rooms` WHERE `id`=" . (int)$idroom . ";";
					$dbo->setQuery($q);
					$dbo->execute();
					if (!$dbo->getNumRows()) {
						continue;
					}
					$room_data = $dbo->loadAssoc();
					array_push($rooms_with_opt, $room_data['id']);
					$current_opts = empty($room_data['idopt']) ? array() : explode(';', rtrim($room_data['idopt'], ';'));
					if (in_array((string)$pwhereup, $current_opts)) {
						continue;
					}
					if (count($current_opts) === 1 && (string)$current_opts[0] == '0') {
						// make sure we do not concatenate a real ID to 0
						$current_opts = array();
					}
					array_push($current_opts, $pwhereup);
					$new_opts = implode(';', $current_opts) . ';';
					$q = "UPDATE `#__vikbooking_rooms` SET `idopt`=" . $dbo->quote($new_opts) . " WHERE `id`={$room_data['id']};";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
			if (!count($rooms_with_opt)) {
				// get all rooms to unset this option (if previously set)
				array_push($rooms_with_opt, '0');
			}
			// unset the option from the other rooms that may have it
			$q = "SELECT `id`, `idopt` FROM `#__vikbooking_rooms` WHERE `id` NOT IN (" . implode(', ', $rooms_with_opt) . ");";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows()) {
				$unset_rooms_opt = $dbo->loadAssocList();
				foreach ($unset_rooms_opt as $room_data) {
					$current_opts = empty($room_data['idopt']) ? array() : explode(';', rtrim($room_data['idopt'], ';'));
					if (!in_array((string)$pwhereup, $current_opts)) {
						// this room is not using this option
						continue;
					}
					$optkey = array_search((string)$pwhereup, $current_opts);
					if ($optkey === false) {
						// key not found
						continue;
					}
					// unset this option ID from the string
					unset($current_opts[$optkey]);
					if (!count($current_opts)) {
						// a room with no options assigned will be listed as "0;"
						$current_opts = array(0);
					}
					$new_opts = implode(';', $current_opts) . ';';
					$q = "UPDATE `#__vikbooking_rooms` SET `idopt`=" . $dbo->quote($new_opts) . " WHERE `id`={$room_data['id']};";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
			//

		}
		$app->redirect("index.php?option=com_vikbooking&task=" . ($stay ? 'editoptional&cid[]=' . $pwhereup : 'optionals'));
	}

	public function createoptionals() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$this->do_createoptionals();
	}

	public function createoptionalsstay() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$this->do_createoptionals(true);
	}

	private function do_createoptionals($stay = false) {
		$dbo = JFactory::getDbo();
		$poptname = VikRequest::getString('optname', '', 'request');
		$poptdescr = VikRequest::getString('optdescr', '', 'request', VIKREQUEST_ALLOWHTML);
		$poptcost = VikRequest::getFloat('optcost', '', 'request');
		$poptperday = VikRequest::getString('optperday', '', 'request');
		$poptperperson = VikRequest::getString('optperperson', '', 'request');
		$pmaxprice = VikRequest::getFloat('maxprice', '', 'request');
		$popthmany = VikRequest::getString('opthmany', '', 'request');
		$poptaliq = VikRequest::getInt('optaliq', '', 'request');
		$pautoresize = VikRequest::getString('autoresize', '', 'request');
		$presizeto = VikRequest::getString('resizeto', '', 'request');
		$pifchildren = VikRequest::getString('ifchildren', '', 'request');
		$pifchildren = $pifchildren == "1" ? 1 : 0;
		$pmaxquant = VikRequest::getString('maxquant', '', 'request');
		$pmaxquant = empty($pmaxquant) ? 0 : intval($pmaxquant);
		$pforcesel = VikRequest::getString('forcesel', '', 'request');
		$pforceval = VikRequest::getString('forceval', '', 'request');
		$pforcevalperday = VikRequest::getString('forcevalperday', '', 'request');
		$pforcevalperchild = VikRequest::getString('forcevalperchild', '', 'request');
		$pforcesummary = VikRequest::getString('forcesummary', '', 'request');
		$pforcesel = $pforcesel == "1" ? 1 : 0;
		$pis_citytax = VikRequest::getString('is_citytax', '', 'request');
		$pis_fee = VikRequest::getString('is_fee', '', 'request');
		$pis_citytax = $pis_citytax == "1" && $pis_fee != "1" ? 1 : 0;
		$pis_fee = $pis_fee == "1" && $pis_citytax == 0 ? 1 : 0;
		$pagefrom = VikRequest::getVar('agefrom', array());
		$pageto = VikRequest::getVar('ageto', array());
		$pagecost = VikRequest::getVar('agecost', array());
		$pagectype = VikRequest::getVar('agectype', array());
		$palwaysav = VikRequest::getInt('alwaysav', 0, 'request');
		$pavfrom = VikRequest::getString('avfrom', '', 'request');
		$pavto = VikRequest::getString('avto', '', 'request');
		$ppcentroom = VikRequest::getInt('pcentroom', 0, 'request');
		$pidrooms = VikRequest::getVar('idrooms', array());
		$optavstr = empty($palwaysav) && !empty($pavfrom) && !empty($pavto) ? VikBooking::getDateTimestamp($pavfrom, 0, 0, 0).';'.VikBooking::getDateTimestamp($pavto, 23, 59, 59) : '';
		if ($pforcesel == 1) {
			$strforceval = intval($pforceval)."-".($pforcevalperday == "1" ? "1" : "0")."-".($pforcevalperchild == "1" ? "1" : "0")."-".($pforcesummary == "1" ? "1" : "0");
		} else {
			$strforceval = "";
		}
		$minguestsnum = VikRequest::getInt('minguestsnum', 0, 'request');
		$mingueststype = VikRequest::getString('mingueststype', 'guests', 'request');
		$minguestsnum = $minguestsnum < 0 ? 0 : $minguestsnum;
		$mingueststype = !empty($mingueststype) && !in_array($mingueststype, array('adults', 'guests')) ? 'guests' : $mingueststype;
		$maxguestsnum = VikRequest::getInt('maxguestsnum', 0, 'request');
		$maxgueststype = VikRequest::getString('maxgueststype', 'guests', 'request');
		$maxguestsnum = $maxguestsnum < 0 ? 0 : $maxguestsnum;
		$maxgueststype = !empty($maxgueststype) && !in_array($maxgueststype, array('adults', 'guests')) ? 'guests' : $maxgueststype;
		$minguests = VikRequest::getInt('minguests', 0, 'request');
		$minguests_conflict = false;
		if ($minguests > 0 && $minguestsnum > 0 && $maxguestsnum > 0) {
			if ($minguestsnum >= $maxguestsnum) {
				$minguests_conflict = JText::translate('VBOMINMAXGUESTSOPTCONFL1');
			} elseif (($maxguestsnum - $minguestsnum) < 2) {
				$minguests_conflict = JText::translate('VBOMINMAXGUESTSOPTCONFL2');
			}
		}
		if (!$minguests || $minguests_conflict !== false) {
			$minguestsnum = 0;
			$maxguestsnum = 0;
			if ($minguests_conflict !== false) {
				// raise warning, but do not stop the process
				VikError::raiseWarning('', $minguests_conflict);
			}
		}
		$damagedep = VikRequest::getInt('damagedep', 0, 'request');
		$oparams = array(
			'minguestsnum' 	=> $minguestsnum,
			'mingueststype' => $mingueststype,
			'maxguestsnum' 	=> $maxguestsnum,
			'maxgueststype' => $maxgueststype,
			'damagedep' 	=> $damagedep,
		);
		if (!empty($poptname)) {
			if (intval($_FILES['optimg']['error']) == 0 && VikBooking::caniWrite(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR) && trim($_FILES['optimg']['name'])!="") {
				jimport('joomla.filesystem.file');
				$updpath = VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR;
				if (@is_uploaded_file($_FILES['optimg']['tmp_name'])) {
					$safename=JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['optimg']['name'])));
					if (file_exists($updpath.$safename)) {
						$j = 1;
						while (file_exists($updpath.$j.$safename)) {
							$j++;
						}
						$pwhere = $updpath.$j.$safename;
					} else {
						$j = "";
						$pwhere = $updpath.$safename;
					}
					if (!getimagesize($_FILES['optimg']['tmp_name'])) {
						@unlink($pwhere);
						$picon = "";
					} else {
						VikBooking::uploadFile($_FILES['optimg']['tmp_name'], $pwhere);
						@chmod($pwhere, 0644);
						$picon = $j.$safename;
						if ($pautoresize == "1" && !empty($presizeto)) {
							$eforj = new vikResizer();
							$origmod = $eforj->proportionalImage($pwhere, $updpath.'r_'.$j.$safename, $presizeto, $presizeto);
							if ($origmod) {
								@unlink($pwhere);
								$picon = 'r_'.$j.$safename;
							}
						}
					}
				} else {
					$picon = "";
				}
			} else {
				$picon = "";
			}
			$poptperday = ($poptperday == "each" ? "1" : "0");
			$poptperperson = ($poptperperson == "each" ? "1" : "0");
			($popthmany == "yes" ? $popthmany = "1" : $popthmany = "0");
			$ageintervalstr = '';
			if ($pifchildren == 1 && count($pagefrom) > 0 && count($pagecost) > 0 && count($pagefrom) == count($pagecost)) {
				foreach ($pagefrom as $kage => $vage) {
					$afrom = intval($vage);
					$ato = intval($pageto[$kage]);
					$acost = floatval($pagecost[$kage]);
					if (strlen($vage) > 0 && strlen($pagecost[$kage]) > 0) {
						if ($ato < $afrom) $ato = $afrom;
						$ageintervalstr .= $afrom.'_'.$ato.'_'.$acost.(array_key_exists($kage, $pagectype) && strpos($pagectype[$kage], '%') !== false ? '_%'.(strpos($pagectype[$kage], '%b') !== false ? 'b' : '') : '').';;';
					}
				}
				$ageintervalstr = rtrim($ageintervalstr, ';;');
				if (!empty($ageintervalstr)) {
					$pforcesel = 1;
				}
			}
			$q = "SELECT `ordering` FROM `#__vikbooking_optionals` ORDER BY `#__vikbooking_optionals`.`ordering` DESC LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$getlast = $dbo->loadResult();
				$newsortnum = $getlast + 1;
			} else {
				$newsortnum = 1;
			}
			$q = "INSERT INTO `#__vikbooking_optionals` (`name`,`descr`,`cost`,`perday`,`hmany`,`img`,`idiva`,`maxprice`,`forcesel`,`forceval`,`perperson`,`ifchildren`,`maxquant`,`ordering`,`ageintervals`,`is_citytax`,`is_fee`,`alwaysav`,`pcentroom`,`oparams`) VALUES(".$dbo->quote($poptname).", ".$dbo->quote($poptdescr).", ".$dbo->quote($poptcost).", ".$dbo->quote($poptperday).", ".$dbo->quote($popthmany).", '".$picon."', ".$dbo->quote($poptaliq).", ".$dbo->quote($pmaxprice).", '".$pforcesel."', '".$strforceval."', '".$poptperperson."', '".$pifchildren."', '".$pmaxquant."', '".$newsortnum."', '".$ageintervalstr."', '".$pis_citytax."', '".$pis_fee."', ".$dbo->quote($optavstr).", ".$dbo->quote($ppcentroom).", " . $dbo->quote(json_encode($oparams)) . ");";
			$dbo->setQuery($q);
			$dbo->execute();
			$newoptid = $dbo->insertid();

			if (!empty($newoptid)) {
				// assign/unset option-rooms relations
				$rooms_with_opt = array();
				if (count($pidrooms)) {
					// assign this new option to the requested rooms
					foreach ($pidrooms as $idroom) {
						if (empty($idroom)) {
							continue;
						}
						$q = "SELECT `id`, `idopt` FROM `#__vikbooking_rooms` WHERE `id`=" . (int)$idroom . ";";
						$dbo->setQuery($q);
						$dbo->execute();
						if (!$dbo->getNumRows()) {
							continue;
						}
						$room_data = $dbo->loadAssoc();
						array_push($rooms_with_opt, $room_data['id']);
						$current_opts = empty($room_data['idopt']) ? array() : explode(';', rtrim($room_data['idopt'], ';'));
						if (in_array((string)$newoptid, $current_opts)) {
							continue;
						}
						if (count($current_opts) === 1 && (string)$current_opts[0] == '0') {
							// make sure we do not concatenate a real ID to 0
							$current_opts = array();
						}
						array_push($current_opts, $newoptid);
						$new_opts = implode(';', $current_opts) . ';';
						$q = "UPDATE `#__vikbooking_rooms` SET `idopt`=" . $dbo->quote($new_opts) . " WHERE `id`={$room_data['id']};";
						$dbo->setQuery($q);
						$dbo->execute();
					}
				}
				if (!count($rooms_with_opt)) {
					// get all rooms to unset this option (if previously set)
					array_push($rooms_with_opt, '0');
				}
				// unset the option from the other rooms that may have it
				$q = "SELECT `id`, `idopt` FROM `#__vikbooking_rooms` WHERE `id` NOT IN (" . implode(', ', $rooms_with_opt) . ");";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows()) {
					$unset_rooms_opt = $dbo->loadAssocList();
					foreach ($unset_rooms_opt as $room_data) {
						$current_opts = empty($room_data['idopt']) ? array() : explode(';', rtrim($room_data['idopt'], ';'));
						if (!in_array((string)$newoptid, $current_opts)) {
							// this room is not using this option
							continue;
						}
						$optkey = array_search((string)$newoptid, $current_opts);
						if ($optkey === false) {
							// key not found
							continue;
						}
						// unset this option ID from the string
						unset($current_opts[$optkey]);
						if (!count($current_opts)) {
							// a room with no options assigned will be listed as "0;"
							$current_opts = array(0);
						}
						$new_opts = implode(';', $current_opts) . ';';
						$q = "UPDATE `#__vikbooking_rooms` SET `idopt`=" . $dbo->quote($new_opts) . " WHERE `id`={$room_data['id']};";
						$dbo->setQuery($q);
						$dbo->execute();
					}
				}
				//
			}

		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=" . ($stay && isset($newoptid) && !empty($newoptid) ? 'editoptional&cid[]=' . $newoptid : 'optionals'));
	}

	public function removeoptionals() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDbo();
			foreach ($ids as $d) {
				$q = "SELECT `img` FROM `#__vikbooking_optionals` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$rows = $dbo->loadAssocList();
					if (!empty($rows[0]['img']) && file_exists(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.$rows[0]['img'])) {
						@unlink(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.$rows[0]['img']);
					}
				}	
				$q = "DELETE FROM `#__vikbooking_optionals` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=optionals");
	}

	public function sendcustomsms() {
		$mainframe = JFactory::getApplication();
		$pphone = VikRequest::getString('phone', '', 'request');
		$psmscont = VikRequest::getString('smscont', '', 'request');
		$pgoto = VikRequest::getString('goto', '', 'request', VIKREQUEST_ALLOWRAW);
		$pgoto = !empty($pgoto) ? urldecode($pgoto) : 'index.php?option=com_vikbooking';
		if (!empty($pphone) && !empty($psmscont)) {
			$sms_api = VikBooking::getSMSAPIClass();
			$sms_api_params = VikBooking::getSMSParams();
			if (!empty($sms_api) && file_exists(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'smsapi'.DIRECTORY_SEPARATOR.$sms_api) && !empty($sms_api_params)) {
				require_once(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'smsapi'.DIRECTORY_SEPARATOR.$sms_api);
				$sms_obj = new VikSmsApi(array(), $sms_api_params);
				$response_obj = $sms_obj->sendMessage($pphone, $psmscont);
				if ( !$sms_obj->validateResponse($response_obj) ) {
					VikError::raiseWarning('', $sms_obj->getLog());
				} else {
					$mainframe->enqueueMessage(JText::translate('VBSENDSMSOK'));
				}
			} else {
				VikError::raiseWarning('', JText::translate('VBSENDSMSERRMISSAPI'));
			}
		} else {
			VikError::raiseWarning('', JText::translate('VBSENDSMSERRMISSDATA'));
		}
		$mainframe->redirect($pgoto);
	}

	public function sendcustomemail() {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$vbo_tn = VikBooking::getTranslator();
		$pbid = VikRequest::getInt('bid', '', 'request');
		$pemailsubj = VikRequest::getString('emailsubj', '', 'request');
		$pemail = VikRequest::getString('email', '', 'request');
		$pemailcont = VikRequest::getString('emailcont', '', 'request', VIKREQUEST_ALLOWRAW);
		$pemailfrom = VikRequest::getString('emailfrom', '', 'request');
		$pgoto = VikRequest::getString('goto', '', 'request', VIKREQUEST_ALLOWRAW);
		$pgoto = !empty($pgoto) ? urldecode($pgoto) : 'index.php?option=com_vikbooking';
		if (!empty($pemail) && !empty($pemailcont)) {
			$email_attach = null;
			jimport('joomla.filesystem.file');
			$pemailattch = VikRequest::getVar('emailattch', null, 'files', 'array');
			if (isset($pemailattch) && strlen(trim($pemailattch['name']))) {
				$filename = JFile::makeSafe(str_replace(" ", "_", strtolower($pemailattch['name'])));
				$src = $pemailattch['tmp_name'];
				$dest = VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR;
				$j = "";
				if (file_exists($dest.$filename)) {
					$j = rand(171, 1717);
					while (file_exists($dest.$j.$filename)) {
						$j++;
					}
				}
				$finaldest = $dest.$j.$filename;
				if (VikBooking::uploadFile($src, $finaldest)) {
					$email_attach = $finaldest;
				} else {
					VikError::raiseWarning('', 'Error uploading the attachment. Email not sent.');
					$mainframe->redirect($pgoto);
					exit;
				}
			}
			//VBO 1.10 - special tags for the custom email template files and messages
			$orig_mail_cont = $pemailcont;
			if (strpos($pemailcont, '{') !== false && strpos($pemailcont, '}') !== false) {
				$booking = array();
				$q = "SELECT `o`.*,`co`.`idcustomer`,CONCAT_WS(' ',`c`.`first_name`,`c`.`last_name`) AS `customer_name`,`c`.`pin` AS `customer_pin`,`nat`.`country_name` FROM `#__vikbooking_orders` AS `o` LEFT JOIN `#__vikbooking_customers_orders` `co` ON `co`.`idorder`=`o`.`id` AND `co`.`idorder`=".(int)$pbid." LEFT JOIN `#__vikbooking_customers` `c` ON `c`.`id`=`co`.`idcustomer` LEFT JOIN `#__vikbooking_countries` `nat` ON `nat`.`country_3_code`=`o`.`country` WHERE `o`.`id`=".(int)$pbid.";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$booking = $dbo->loadAssoc();
				}
				$booking_rooms = array();
				$q = "SELECT `or`.*,`r`.`name` AS `room_name` FROM `#__vikbooking_ordersrooms` AS `or` LEFT JOIN `#__vikbooking_rooms` `r` ON `r`.`id`=`or`.`idroom` WHERE `or`.`idorder`=".(int)$pbid.";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$booking_rooms = $dbo->loadAssocList();
					if (!empty($booking['lang'])) {
						$vbo_tn->translateContents($booking_rooms, '#__vikbooking_rooms', array('id' => 'idroom', 'room_name' => 'name'), array(), $booking['lang']);
					}
				}
				//we use the same parsing function as the one for the Customer SMS Template
				$pemailcont = VikBooking::parseCustomerSMSTemplate($booking, $booking_rooms, null, $pemailcont);
			}
			//
			// allow the use of token {booking_id} in subject
			$pemailsubj = str_replace('{booking_id}', $pbid, $pemailsubj);
			//
			$is_html = (strpos($pemailcont, '<') !== false && strpos($pemailcont, '>') !== false);
			$pemailcont = $is_html ? nl2br($pemailcont) : $pemailcont;
			$vbo_app = new VboApplication();
			$vbo_app->sendMail($pemailfrom, $pemailfrom, $pemail, $pemailfrom, $pemailsubj, $pemailcont, $is_html, 'base64', $email_attach);
			$mainframe->enqueueMessage(JText::translate('VBSENDEMAILOK'));
			if ($email_attach !== null) {
				@unlink($email_attach);
			}
			//Booking History
			VikBooking::getBookingHistoryInstance()->setBid($pbid)->store('CE', nl2br($pemailsubj . "\n\n" . $pemailcont));
			//
			//Save email template for future sending
			$config_rec_exists = false;
			$emtpl = array(
				'emailsubj' => $pemailsubj,
				'emailcont' => $orig_mail_cont,
				'emailfrom' => $pemailfrom
			);
			$cur_emtpl = array();
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='customemailtpls';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$config_rec_exists = true;
				$cur_emtpl = $dbo->loadResult();
				$cur_emtpl = empty($cur_emtpl) ? array() : json_decode($cur_emtpl, true);
				$cur_emtpl = is_array($cur_emtpl) ? $cur_emtpl : array();
			}
			if (count($cur_emtpl) > 0) {
				$existing_subj = false;
				foreach ($cur_emtpl as $emk => $emv) {
					if (array_key_exists('emailsubj', $emv) && $emv['emailsubj'] == $emtpl['emailsubj']) {
						$cur_emtpl[$emk] = $emtpl;
						$existing_subj = true;
						break;
					}
				}
				if ($existing_subj === false) {
					$cur_emtpl[] = $emtpl;
				}
			} else {
				$cur_emtpl[] = $emtpl;
			}
			if (count($cur_emtpl) > 10) {
				//Max 10 templates to avoid problems with the size of the field and truncated json strings
				$exceed = count($cur_emtpl) - 10;
				for ($tl=0; $tl < $exceed; $tl++) { 
					unset($cur_emtpl[$tl]);
				}
				$cur_emtpl = array_values($cur_emtpl);
			}
			if ($config_rec_exists === true) {
				$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote(json_encode($cur_emtpl))." WHERE `param`='customemailtpls';";
				$dbo->setQuery($q);
				$dbo->execute();
			} else {
				$q = "INSERT INTO `#__vikbooking_config` (`param`,`setting`) VALUES ('customemailtpls', ".$dbo->quote(json_encode($cur_emtpl)).");";
				$dbo->setQuery($q);
				$dbo->execute();
			}
			//
		} else {
			VikError::raiseWarning('', JText::translate('VBSENDEMAILERRMISSDATA'));
		}
		$mainframe->redirect($pgoto);
	}

	public function rmcustomemailtpl() {
		$cid = VikRequest::getVar('cid', array(0));
		$oid = $cid[0];
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$tplind = VikRequest::getInt('tplind', '', 'request');
		if (empty($oid) || !(strlen($tplind) > 0)) {
			VikError::raiseWarning('', 'Missing Data.');
			$mainframe->redirect('index.php?option=com_vikbooking');
			exit;
		}
		$cur_emtpl = array();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='customemailtpls';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$cur_emtpl = $dbo->loadResult();
			$cur_emtpl = empty($cur_emtpl) ? array() : json_decode($cur_emtpl, true);
			$cur_emtpl = is_array($cur_emtpl) ? $cur_emtpl : array();
		} else {
			VikError::raiseWarning('', 'Missing Templates Record.');
			$mainframe->redirect('index.php?option=com_vikbooking');
			exit;
		}
		if (array_key_exists($tplind, $cur_emtpl)) {
			unset($cur_emtpl[$tplind]);
			$cur_emtpl = count($cur_emtpl) > 0 ? array_values($cur_emtpl) : array();
			$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote(json_encode($cur_emtpl))." WHERE `param`='customemailtpls';";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe->redirect('index.php?option=com_vikbooking&task=editorder&cid[]='.$oid.'&customemail=1');
		exit;
	}

	public function exportcustomers() {
		//we do not set the menu for this view
	
		VikRequest::setVar('view', VikRequest::getCmd('view', 'exportcustomers'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function csvexportprepare() {
		//modal box, so we do not set menu or footer
	
		VikRequest::setVar('view', VikRequest::getCmd('view', 'csvexportprepare'));
	
		parent::display();
	}

	public function icsexportprepare() {
		//modal box, so we do not set menu or footer
	
		VikRequest::setVar('view', VikRequest::getCmd('view', 'icsexportprepare'));
	
		parent::display();
	}

	public function bookingcheckin() {
		//modal box, so we do not set menu or footer
	
		VikRequest::setVar('view', VikRequest::getCmd('view', 'bookingcheckin'));
	
		parent::display();
	}

	public function gencheckindoc() {
		//modal box, so we do not set menu or footer
	
		VikRequest::setVar('view', VikRequest::getCmd('view', 'gencheckindoc'));
	
		parent::display();
	}

	public function checkversion() {
		//to be called via ajax
		$params = new stdClass;
		$params->version 	= VIKBOOKING_SOFTWARE_VERSION;
		$params->alias 		= 'com_vikbooking';

		$result = array();

		if (!count($result)) {
			$result = new stdClass;
			$result->status = 0;
		} else {
			$result = $result[0];
		}

		echo json_encode($result);
		exit;
	}

	public function updateprogram() {
		$params = new stdClass;
		$params->version 	= VIKBOOKING_SOFTWARE_VERSION;
		$params->alias 		= 'com_vikbooking';

		$result = array();

		if (!count($result) || !$result[0]) {
			if (class_exists('JEventDispatcher')) {
				$result = $dispatcher->trigger('checkVersion', array(&$params));
			} else {
				$app = JFactory::getApplication();
				if (method_exists($app, 'triggerEvent')) {
					$result = $app->triggerEvent('checkVersion', array(&$params));
				}
			}
		}

		if (!count($result) || !$result[0]->status || !$result[0]->response->status) {
			exit('Error, plugin disabled');
		}

		JToolbarHelper::title(JText::translate('VBMAINTITLEUPDATEPROGRAM'));

		VikBookingHelper::pUpdateProgram($result[0]->response);
	}

	public function updateprogramlaunch() {
		$params = new stdClass;
		$params->version 	= VIKBOOKING_SOFTWARE_VERSION;
		$params->alias 		= 'com_vikbooking';

		$json = new stdClass;
		$json->status = false;

		echo json_encode($json);
		exit;
	}

	public function invoke_vcm() {
		$oids = VikRequest::getVar('cid', array(0));
		$mainframe = JFactory::getApplication();
		$sync_type = VikRequest::getString('stype', 'new', 'request');
		$sync_type = !in_array($sync_type, array('new', 'modify', 'cancel')) ? 'new' : $sync_type;
		$original_booking_js = VikRequest::getString('origb', '', 'request', VIKREQUEST_ALLOWRAW);
		$return_url = VikRequest::getString('returl', '', 'request');
		$return_url = !empty($return_url) ? urldecode($return_url) : $return_url;
		if (!(count($oids) > 0) || !file_exists(VCM_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "synch.vikbooking.php")) {
			$mainframe->redirect("index.php?option=com_vikbooking&task=orders");
			exit;
		}

		$vcm_obj = VikBooking::getVcmInvoker();
		$vcm_obj->setOids($oids)->setSyncType($sync_type)->setOriginalBooking($original_booking_js, true);
		$result = $vcm_obj->doSync();

		if ($result === true) {
			$mainframe->enqueueMessage(JText::translate('VBCHANNELMANAGERRESULTOK'));
		} else {
			VikError::raiseWarning('', JText::translate('VBCHANNELMANAGERRESULTKO').' <a href="index.php?option=com_vikchannelmanager" target="_blank">'.JText::translate('VBCHANNELMANAGEROPEN').'</a>');
		}

		if (!empty($return_url)) {
			$mainframe->redirect($return_url);
		} else {
			$mainframe->redirect("index.php?option=com_vikbooking&task=orders");
		}
	}

	public function multiphotosupload() {
		jimport('joomla.filesystem.file');

		$dbo = JFactory::getDBO();
		$proomid = VikRequest::getInt('roomid', '', 'request');
		
		$resp = array('files' => array());
		$error_messages = array(
			1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
			2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
			3 => 'The uploaded file was only partially uploaded',
			4 => 'No file was uploaded',
			6 => 'Missing a temporary folder',
			7 => 'Failed to write file to disk',
			8 => 'A PHP extension stopped the file upload',
			'post_max_size' => 'The uploaded file exceeds the post_max_size directive in php.ini',
			'max_file_size' => 'File is too big',
			'min_file_size' => 'File is too small',
			'accept_file_types' => 'Filetype not allowed',
			'max_number_of_files' => 'Maximum number of files exceeded',
			'max_width' => 'Image exceeds maximum width',
			'min_width' => 'Image requires a minimum width',
			'max_height' => 'Image exceeds maximum height',
			'min_height' => 'Image requires a minimum height',
			'abort' => 'File upload aborted',
			'image_resize' => 'Failed to resize image',
			'vbo_type' => 'The file type cannot be accepted',
			'vbo_jupload' => 'The upload has failed. Check the Joomla Configuration',
			'vbo_perm' => 'Error moving the uploaded files. Check your permissions'
		);

		$creativik = new vikResizer();
		$updpath = VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR;
		$bigsdest = $updpath;
		$thumbsdest = $updpath;
		$dest = $updpath;
		$moreimagestr = '';
		$cur_captions = json_encode(array());

		$q = "SELECT `moreimgs`,`imgcaptions` FROM `#__vikbooking_rooms` WHERE `id`=".$proomid.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$photo_data = $dbo->loadAssocList();
			$cur_captions = $photo_data[0]['imgcaptions'];
			$cur_photos = $photo_data[0]['moreimgs'];
			if (!empty($cur_photos)) {
				$moreimagestr .= $cur_photos;
			} 
		}

		$bulkphotos = VikRequest::getVar('bulkphotos', null, 'files', 'array');

		if (is_array($bulkphotos) && count($bulkphotos) > 0 && array_key_exists('name', $bulkphotos) && count($bulkphotos['name']) > 0) {
			foreach ($bulkphotos['name'] as $updk => $photoname) {
				$uploaded_image = array();
				$filename = JFile::makeSafe(str_replace(" ", "_", strtolower($photoname)));
				$src = $bulkphotos['tmp_name'][$updk];
				$j = "";
				if (file_exists($dest.$filename)) {
					$j = rand(171, 1717);
					while (file_exists($dest.$j.$filename)) {
						$j++;
					}
				}
				$finaldest=$dest.$j.$filename;
				$is_error = false;
				$err_key = '';
				if (array_key_exists('error', $bulkphotos) && array_key_exists($updk, $bulkphotos['error']) && !empty($bulkphotos['error'][$updk])) {
					if (array_key_exists($bulkphotos['error'][$updk], $error_messages)) {
						$is_error = true;
						$err_key = $bulkphotos['error'][$updk];
					}
				}
				if (!$is_error) {
					$check = getimagesize($bulkphotos['tmp_name'][$updk]);
					if ($check[2] & imagetypes()) {
						if (VikBooking::uploadFile($src, $finaldest)) {
							$gimg = $j.$filename;
							//orig img
							$origmod = true;
							VikBooking::uploadFile($finaldest, $bigsdest.'big_'.$j.$filename, true);
							//thumb
							$thumbsize = VikBooking::getThumbSize();
							$thumb = $creativik->proportionalImage($finaldest, $thumbsdest.'thumb_'.$j.$filename, $thumbsize, $thumbsize);
							if (!$thumb || !$origmod) {
								if (file_exists($bigsdest.'big_'.$j.$filename)) @unlink($bigsdest.'big_'.$j.$filename);
								if (file_exists($thumbsdest.'thumb_'.$j.$filename)) @unlink($thumbsdest.'thumb_'.$j.$filename);
								$is_error = true;
								$err_key = 'vbo_perm';
							} else {
								$moreimagestr.=$j.$filename.";;";
							}
							@unlink($finaldest);
						} else {
							$is_error = true;
							$err_key = 'vbo_jupload';
						}
					} else {
						$is_error = true;
						$err_key = 'vbo_type';
					}
				}
				$img = new stdClass();
				if ($is_error) {
					$img->name = '';
					$img->size = '';
					$img->type = '';
					$img->url = '';
					$img->error = array_key_exists($err_key, $error_messages) ? $error_messages[$err_key] : 'Generic Error for Upload';
				} else {
					$img->name = $photoname;
					$img->size = $bulkphotos['size'][$updk];
					$img->type = $bulkphotos['type'][$updk];
					$img->url = VBO_SITE_URI.'resources/uploads/big_'.$j.$filename;
				}
				$resp['files'][] = $img;
			}
		} else {
			$res = new stdClass();
			$res->name = '';
			$res->size = '';
			$res->type = '';
			$res->url = '';
			$res->error = 'No images received for upload';
			$resp['files'][] = $res;
		}
		//Update current extra images string
		$q = "UPDATE `#__vikbooking_rooms` SET `moreimgs`=".$dbo->quote($moreimagestr)." WHERE `id`=".$proomid.";";
		$dbo->setQuery($q);
		$dbo->execute();
		$resp['actmoreimgs'] = $moreimagestr;
		//Update current extra images uploaded
		$cur_thumbs = '';
		$morei=explode(';;', $moreimagestr);
		if (@count($morei) > 0) {
			$imgcaptions = json_decode($cur_captions, true);
			$usecaptions = empty($imgcaptions) || is_null($imgcaptions) || !is_array($imgcaptions) || !(count($imgcaptions) > 0) ? false : true;
			$cur_thumbs .= '<ul class="vbo-sortable">';
			foreach ($morei as $ki => $mi) {
				if (!empty($mi)) {
					$cur_thumbs .= '<li class="vbo-editroom-currentphoto">';
					$cur_thumbs .= '<a href="'.VBO_SITE_URI.'resources/uploads/big_'.$mi.'" target="_blank" class="vbomodal"><img src="'.VBO_SITE_URI.'resources/uploads/thumb_'.$mi.'" class="maxfifty"/></a>';
					$cur_thumbs .= '<a class="vbo-toggle-imgcaption" href="javascript: void(0);" onclick="vbOpenImgDetails(\''.$ki.'\', this)"><i class="'.VikBookingIcons::i('cog').'"></i></a>';
					$cur_thumbs .= '<div id="vbimgdetbox'.$ki.'" class="vbimagedetbox" style="display: none;"><div class="captionlabel"><span>'.JText::translate('VBIMGCAPTION').'</span><input type="text" name="caption'.$ki.'" value="'.($usecaptions === true && isset($imgcaptions[$ki]) ? $imgcaptions[$ki] : "").'" size="40"/></div><input type="hidden" name="imgsorting[]" value="'.$mi.'"/><input class="captionsubmit" type="button" name="updcatpion" value="'.JText::translate('VBIMGUPDATE').'" onclick="javascript: updateCaptions();"/><div class="captionremoveimg"><a class="vbimgrm btn btn-danger" href="index.php?option=com_vikbooking&task=removemoreimgs&roomid='.$proomid.'&imgind='.$ki.'" title="'.JText::translate('VBREMOVEIMG').'"><i class="icon-remove"></i>'.JText::translate('VBREMOVEIMG').'</a></div></div>';
					$cur_thumbs .= '</li>';
				}
			}
			$cur_thumbs .= '</ul>';
			$cur_thumbs .= '<br clear="all"/>';
		}
		$resp['currentthumbs'] = $cur_thumbs;

		echo json_encode($resp);
		exit;
	}

	public function loadsmsbalance() {
		//to be called via ajax
		$html = 'Error1 [N/A]';
		$sms_api = VikBooking::getSMSAPIClass();
		if (file_exists(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'smsapi'.DIRECTORY_SEPARATOR.$sms_api)) {
			require_once(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'smsapi'.DIRECTORY_SEPARATOR.$sms_api);
			$sms_obj = new VikSmsApi(array(), VikBooking::getSMSParams());
			if (method_exists('VikSmsApi', 'estimate')) {
				$array_result = $sms_obj->estimate("+393711271611", "estimate credit");
				if ( $array_result->errorCode != 0 ) {
					$html = 'Error3 ['.$array_result->errorMsg.']';
				} else {
					$html = VikBooking::getCurrencySymb().' '.$array_result->userCredit;
				}
			} else {
				$html = 'Error2 [N/A]';
			}
		}
		echo $html;
		exit;
	}

	public function loadsmsparams() {
		//to be called via ajax
		$html = '---------';
		$phpfile = VikRequest::getString('phpfile', '', 'request');
		if (!empty($phpfile)) {
			$sms_api = VikBooking::getSMSAPIClass();
			$sms_params = $sms_api == $phpfile ? VikBooking::getSMSParams(false) : '';
			$html = VikBooking::displaySMSParameters($phpfile, $sms_params);
		}
		echo $html;
		exit;
	}

	public function loadcronparams() {
		//to be called via ajax
		$html = '---------';
		$phpfile = VikRequest::getString('phpfile', '', 'request');
		if (!empty($phpfile)) {
			$html = VikBooking::displayCronParameters($phpfile);
		}
		echo $html;
		exit;
	}

	public function loadpaymentparams() {
		//to be called via ajax
		$html = '<p>---------</p>';
		$phpfile = VikRequest::getString('phpfile', '', 'request');
		if (!empty($phpfile)) {
			$html = VikBooking::displayPaymentParameters($phpfile);
		}
		echo $html;
		exit;
	}

	public function setbookingtag() {
		//to be called via ajax
		$dbo = JFactory::getDBO();
		$pidorder = VikRequest::getInt('idorder', '', 'request');
		$ptagkey = VikRequest::getInt('tagkey', '', 'request');
		if (!empty($pidorder) && $ptagkey >= 0) {
			$all_tags = VikBooking::loadBookingsColorTags();
			if (array_key_exists($ptagkey, $all_tags)) {
				$q = "SELECT `id` FROM `#__vikbooking_orders` WHERE `id`=".(int)$pidorder.";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$newcolortag = json_encode($all_tags[$ptagkey]);
					$q = "UPDATE `#__vikbooking_orders` SET `colortag`=".$dbo->quote($newcolortag)." WHERE `id`=".(int)$pidorder.";";
					$dbo->setQuery($q);
					$dbo->execute();
					$newcolortag = $all_tags[$ptagkey];
					$newcolortag['name'] = JText::translate($newcolortag['name']);
					$newcolortag['fontcolor'] = VikBooking::getBestColorContrast($newcolortag['color']);
					echo json_encode($newcolortag);
				} else {
					echo 'e4j.error.Booking ('.$pidorder.') not found';
				}
			} else {
				echo 'e4j.error.Color Tag ('.$ptagkey.') not found';
			}
		} else {
			echo 'e4j.error.Missing Data';
		}
		exit;
	}

	public function updatereceiptnum() {
		//to be called via ajax
		$pnewnum = VikRequest::getInt('newnum', '', 'request');
		$pnewnotes = VikRequest::getString('newnotes', '', 'request', VIKREQUEST_ALLOWRAW);
		$poid = VikRequest::getInt('oid', '', 'request');
		if ($pnewnum > 0) {
			VikBooking::getNextReceiptNumber($poid, $pnewnum);
			VikBooking::getReceiptNotes($pnewnotes);
			//Booking History
			VikBooking::getBookingHistoryInstance()->setBid($poid)->store('BR', JText::translate('VBOFISCRECEIPTNUM').': '.$pnewnum);
			//
			echo 'e4j.ok';
			exit;
		}
		echo 'e4j.error';
		exit;
	}

	public function isroombookable() {
		//to be called via ajax
		$dbo = JFactory::getDBO();
		$res = array(
			'status' => 0,
			'err' => ''
		);
		$prid = VikRequest::getInt('rid', '', 'request');
		$pfdate = VikRequest::getString('fdate', '', 'request');
		$ptdate = VikRequest::getString('tdate', '', 'request');
		$room_info = array();
		$q = "SELECT * FROM `#__vikbooking_rooms` WHERE `id`=".(int)$prid.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$room_info = $dbo->loadAssoc();
		}
		$pcheckinh = 0;
		$pcheckinm = 0;
		$pcheckouth = 0;
		$pcheckoutm = 0;
		$timeopst = VikBooking::getTimeOpenStore();
		if (is_array($timeopst)) {
			$opent = VikBooking::getHoursMinutes($timeopst[0]);
			$closet = VikBooking::getHoursMinutes($timeopst[1]);
			$pcheckinh = $opent[0];
			$pcheckinm = $opent[1];
			$pcheckouth = $closet[0];
			$pcheckoutm = $closet[1];
		}
		$from_ts = VikBooking::getDateTimestamp($pfdate, $pcheckinh, $pcheckinm);
		$to_ts = VikBooking::getDateTimestamp($ptdate, $pcheckouth, $pcheckoutm);
		if (
			count($room_info) > 0 && 
			(!empty($pfdate) && !empty($ptdate) && !empty($from_ts) && !empty($to_ts)) && 
			VikBooking::roomBookable($room_info['id'], $room_info['units'], $from_ts, $to_ts)) 
		{
			$res['status'] = 1;
		} else {
			if (!(count($room_info) > 0)) {
				$res['err'] = 'Room not found';
			} elseif (empty($pfdate) || empty($ptdate) || empty($from_ts) || empty($to_ts)) {
				$res['err'] = 'Invalid dates';
			} else {
				//not available
				$res['err'] = JText::sprintf('VBOBOOKADDROOMERR', $room_info['name'], $pfdate, $ptdate);
			}
		}

		echo json_encode($res);
		exit;
	}

	public function uploadsnapshot() {
		$snap_base_path = VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'idscans';
		/**
		 * We no longer access the uploaded file from php://input, we now retrieve it as a regular file upload.
		 * The old snapshot collection script with Flash no longer works in 2021.
		 * 
		 * @since 	1.14 (J) - 1.4.0 (WP)
		 */
		$result = null;
		try {
			$result = VikBooking::uploadFileFromRequest(VikRequest::getVar('snapshot', null, 'files', 'array'), $snap_base_path);
		} catch (RuntimeException $e) {
			echo "e4j.error.Error " . $e->getMessage();
			exit;
		}

		if (!is_object($result)) {
			echo "e4j.error.Invalid upload response";
			exit;
		}

		echo $result->filename;
		exit;
	}

	public function checkvcmrateschanges() {
		//to be called via ajax
		$session = JFactory::getSession();
		$ret = array('changesCount' => 0, 'changesData' => '');
		$updforvcm = $session->get('vbVcmRatesUpd', '');
		if (!empty($updforvcm) && is_array($updforvcm) && count($updforvcm) > 0) {
			$ret['changesCount'] = $updforvcm['count'];
			$ret['changesData'] = $updforvcm;
		}

		echo json_encode($ret);
		exit;
	}

	public function getbookingsinfo() {
		//to be called via ajax
		$dbo = JFactory::getDbo();
		$booking_infos = array();
		$bookings = array();
		$pidorders = VikRequest::getString('idorders', '', 'request');
		$psubroom = VikRequest::getString('subroom', '', 'request');
		if (!empty($pidorders)) {
			$bookings = explode(',', $pidorders);
			foreach ($bookings as $k => $v) {
				$v = intval(str_replace('-', '', $v));
				if (empty($v)) {
					unset($bookings[$k]);
					continue;
				}
				$bookings[$k] = $v;
			}
		}
		$bookings = array_values($bookings);
		if (!count($bookings)) {
			/**
			 * AJAX requests made by the page availability overview may contain empty booking IDs
			 * due to SQL errors that only booked the room, but could not save the booking record.
			 * Clean up busy records where the busy relations contain empty booking IDs.
			 * 
			 * @since 	1.14 (J) - 1.4.0 (WP)
			 */
			$q = "SELECT `idbusy` FROM `#__vikbooking_ordersbusy` WHERE `idorder` = 0 OR `idorder` IS NULL;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows()) {
				$removelist = $dbo->loadAssocList();
				$hanging_busy_ids = array();
				foreach ($removelist as $hanging_busy) {
					$hanging_busy_id = (int)$hanging_busy['idbusy'];
					if (!in_array($hanging_busy_id, $hanging_busy_ids)) {
						array_push($hanging_busy_ids, $hanging_busy_id);
					}
				}
				if (count($hanging_busy_ids)) {
					$q = "DELETE FROM `#__vikbooking_busy` WHERE `id` IN (" . implode(', ', $hanging_busy_ids) . ");";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
			//

			echo 'e4j.error.1 '.addslashes(JText::translate('VBOVWGETBKERRMISSDATA'));
			exit;
		}
		$nowdf = VikBooking::getDateFormat(true);
		if ($nowdf=="%d/%m/%Y") {
			$df='d/m/Y';
		} elseif ($nowdf=="%m/%d/%Y") {
			$df='m/d/Y';
		} else {
			$df='Y/m/d';
		}
		$datesep = VikBooking::getDateSeparator(true);
		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id` IN (".implode(', ', $bookings).") AND (`status`='confirmed' OR `status`='standby');";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$booking_infos = $dbo->loadAssocList();
			foreach ($booking_infos as $k => $row) {
				//rooms, amounts and guests information
				$rooms = VikBooking::loadOrdersRoomsData($row['id']);
				$room_names = array();
				$totadults = 0;
				$totchildren = 0;
				foreach ($rooms as $rr) {
					$totadults += $rr['adults'];
					$totchildren += $rr['children'];
					$room_names[] = $rr['room_name'];
				}
				$booking_infos[$k]['status_lbl'] = ($row['status'] != 'confirmed' && $row['status'] != 'standby' ? $row['status'] : ($row['status'] == 'confirmed' ? JText::translate('VBCONFIRMED') : JText::translate('VBSTANDBY')));
				$booking_infos[$k]['colortag'] = VikBooking::applyBookingColorTag($row);
				if (count($booking_infos[$k]['colortag']) > 0) {
					$booking_infos[$k]['colortag']['name'] = JText::translate($booking_infos[$k]['colortag']['name']);
				}
				$booking_infos[$k]['room_names'] = implode(', ', $room_names);
				$booking_infos[$k]['tot_adults'] = $totadults;
				$booking_infos[$k]['tot_children'] = $totchildren;
				$booking_infos[$k]['format_tot'] = VikBooking::numberFormat($row['total']);
				$booking_infos[$k]['format_totpaid'] = VikBooking::numberFormat($row['totpaid']);
				//Rooms Indexes
				$rindexes = array();
				$optindexes = array();
				$subroomdata = !empty($psubroom) ? explode('-', $psubroom) : array();
				foreach ($rooms as $or) {
					if ($row['status'] == "confirmed" && !empty($or['params']) && strlen($or['roomindex'])) {
						$room_params = json_decode($or['params'], true);
						if (is_array($room_params) && array_key_exists('features', $room_params) && @count($room_params['features']) > 0) {
							foreach ($room_params['features'] as $rind => $rfeatures) {
								if ($rind == $or['roomindex']) {
									$ind_str = '';
									foreach ($rfeatures as $fname => $fval) {
										if (strlen($fval)) {
											$ind_str = '#'.$rind.' - '.JText::translate($fname).': '.$fval;
											break;
										}
									}
									if (!array_key_exists($or['room_name'], $rindexes)) {
										$rindexes[$or['room_name']] = $ind_str;
									} else {
										$rindexes[$or['room_name']] .= ', '.$ind_str;
									}
									break;
								}
							}
							if (count($subroomdata) && !count($optindexes) && $or['idroom'] == (int)$subroomdata[0]) {
								// build the options for switching the room index for this room
								foreach ($room_params['features'] as $rind => $rfeatures) {
									foreach ($rfeatures as $fname => $fval) {
										if (strlen($fval)) {
											$optindexes[] = '<option value="'.$rind.'"'.($rind == (int)$subroomdata[1] ? ' selected="selected"' : '').'>#'.$rind.' - '.JText::translate($fname).': '.$fval.'</option>';
											break;
										}
									}
								}
							}
						}
					}
				}
				if (count($rindexes)) {
					$booking_infos[$k]['rindexes'] = $rindexes;
				}
				if (count($optindexes)) {
					$booking_infos[$k]['optindexes'] = $optindexes;
				}
				//Channel Provenience
				$ota_logo_img = JText::translate('VBORDFROMSITE');
				if (!empty($row['channel'])) {
					$channelparts = explode('_', $row['channel']);
					$otachannel = array_key_exists(1, $channelparts) && strlen($channelparts[1]) > 0 ? $channelparts[1] : ucwords($channelparts[0]);
					$ota_logo_img = VikBooking::getVcmChannelsLogo($row['channel']);
					if ($ota_logo_img === false) {
						$ota_logo_img = $otachannel;
					} else {
						$ota_logo_img = '<img src="'.$ota_logo_img.'" class="vbo-channelimg-small"/>';
					}
				}
				$booking_infos[$k]['channelimg'] = $ota_logo_img;
				//Customer Details
				$custdata = $row['custdata'];
				$custdata_parts = explode("\n", $row['custdata']);
				if (count($custdata_parts) > 2 && strpos($custdata_parts[0], ':') !== false && strpos($custdata_parts[1], ':') !== false) {
					//get the first two fields
					$custvalues = array();
					foreach ($custdata_parts as $custdet) {
						if (strlen($custdet) < 1) {
							continue;
						}
						$custdet_parts = explode(':', $custdet);
						if (count($custdet_parts) >= 2) {
							unset($custdet_parts[0]);
							array_push($custvalues, trim(implode(':', $custdet_parts)));
						}
						if (count($custvalues) > 1) {
							break;
						}
					}
					if (count($custvalues) > 1) {
						$custdata = implode(' ', $custvalues);
					}
				}
				if (strlen($custdata) > 45) {
					$custdata = substr($custdata, 0, 45)." ...";
				}

				$q = "SELECT `c`.*,`co`.`idorder` FROM `#__vikbooking_customers` AS `c` LEFT JOIN `#__vikbooking_customers_orders` `co` ON `c`.`id`=`co`.`idcustomer` WHERE `co`.`idorder`=".$row['id'].";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$cust_country = $dbo->loadAssocList();
					$cust_country = $cust_country[0];
					if (!empty($cust_country['first_name'])) {
						$custdata = $cust_country['first_name'].' '.$cust_country['last_name'];
						if (!empty($cust_country['country'])) {
							if (file_exists(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'countries'.DIRECTORY_SEPARATOR.$cust_country['country'].'.png')) {
								$custdata .= '<img src="'.VBO_ADMIN_URI.'resources/countries/'.$cust_country['country'].'.png'.'" title="'.$cust_country['country'].'" class="vbo-country-flag vbo-country-flag-left"/>';
							}
						}
					}
				}
				$custdata = JText::translate('VBDBTEXTROOMCLOSED') == $row['custdata'] ? '<span class="vbordersroomclosed">'.JText::translate('VBDBTEXTROOMCLOSED').'</span>' : $custdata;
				$booking_infos[$k]['cinfo'] = $custdata;
				//Formatted dates
				$booking_infos[$k]['ts'] = date(str_replace("/", $datesep, $df).' H:i', $row['ts']);
				$booking_infos[$k]['checkin'] = date(str_replace("/", $datesep, $df).' H:i', $row['checkin']);
				$booking_infos[$k]['checkout'] = date(str_replace("/", $datesep, $df).' H:i', $row['checkout']);
			}
		}
		if (!(count($booking_infos) > 0)) {
			echo 'e4j.error.2 '.addslashes(JText::translate('VBOVWGETBKERRMISSDATA'));
			exit;
		}

		echo json_encode($booking_infos);
		exit;
	}

	public function switchRoomIndex() {
		//to be called via ajax
		$dbo = JFactory::getDBO();
		$bid = VikRequest::getInt('bid', '', 'request');
		$rid = VikRequest::getInt('rid', '', 'request');
		$old_rindex = VikRequest::getInt('old_rindex', '', 'request');
		$new_rindex = VikRequest::getInt('new_rindex', '', 'request');
		if (empty($bid) || empty($rid) || empty($old_rindex) || empty($new_rindex)) {
			echo 'e4j.error.#1 Missing Data';
			exit;
		}
		$q = "SELECT * FROM `#__vikbooking_ordersrooms` WHERE `idorder`=".$bid." AND `idroom`=".$rid." AND `roomindex`=".$old_rindex.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() < 1) {
			echo 'e4j.error.#2 Record not found';
			exit;
		}
		$rows = $dbo->loadAssocList();
		$q = "UPDATE `#__vikbooking_ordersrooms` SET `roomindex`=".$new_rindex." WHERE `id`=".$rows[0]['id'].";";
		$dbo->setQuery($q);
		$dbo->execute();
		echo 'e4j.ok';
		exit;
	}

	public function searchcustomer() {
		//to be called via ajax
		$kw = VikRequest::getString('kw', '', 'request');
		$nopin = VikRequest::getInt('nopin', '', 'request');
		$email = VikRequest::getInt('email', 0, 'request');
		$cstring = '';
		if (strlen($kw) > 0) {
			$dbo = JFactory::getDBO();
			if ($nopin > 0) {
				//page all bookings
				$q = "SELECT * FROM `#__vikbooking_customers` WHERE CONCAT_WS(' ', `first_name`, `last_name`) LIKE ".$dbo->quote("%".$kw."%")." OR `email` LIKE ".$dbo->quote("%".$kw."%")." ORDER BY `first_name` ASC LIMIT 30;";
			} elseif ($email > 0) {
				// page calendar for checking if an email exists
				$q = "SELECT `first_name`, `last_name`, `email` FROM `#__vikbooking_customers` WHERE `email`=".$dbo->quote($kw).";";
			} else {
				//page calendar
				$q = "SELECT * FROM `#__vikbooking_customers` WHERE CONCAT_WS(' ', `first_name`, `last_name`) LIKE ".$dbo->quote("%".$kw."%")." OR `email` LIKE ".$dbo->quote("%".$kw."%")." OR `pin` LIKE ".$dbo->quote("%".$kw."%")." ORDER BY `first_name` ASC;";
			}
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$customers = $dbo->loadAssocList();
				if ($email > 0) {
					echo json_encode($customers[0]);
					exit;
				}
				$cust_old_fields = array();
				$cstring_search = '<div class="vbo-custsearchres-inner">' . "\n";
				foreach ($customers as $k => $v) {
					$cstring_search .= '<div class="vbo-custsearchres-entry" data-custid="'.$v['id'].'" data-email="'.$v['email'].'" data-phone="'.addslashes($v['phone']).'" data-country="'.$v['country'].'" data-pin="'.$v['pin'].'" data-firstname="'.addslashes($v['first_name']).'" data-lastname="'.addslashes($v['last_name']).'">'."\n";
					$cstring_search .= '<span class="vbo-custsearchres-cflag">';
					if (is_file(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'countries'.DIRECTORY_SEPARATOR.$v['country'].'.png')) {
						$cstring_search .= '<img src="'.VBO_ADMIN_URI.'resources/countries/'.$v['country'].'.png'.'" title="'.$v['country'].'" class="vbo-country-flag"/>'."\n";
					} else {
						$cstring_search .= '<i class="' . VikBookingIcons::i('globe') . '"></i>';
					}
					$cstring_search .= '</span>';
					$cstring_search .= '<span class="vbo-custsearchres-name" title="'.$v['email'].'">'.$v['first_name'].' '.$v['last_name'].'</span>'."\n";
					if (!($nopin > 0)) {
						$cstring_search .= '<span class="vbo-custsearchres-pin">'.$v['pin'].'</span>'."\n";
					}
					$cstring_search .= '</div>'."\n";
					if (!empty($v['cfields'])) {
						$oldfields = json_decode($v['cfields'], true);
						if (is_array($oldfields) && count($oldfields)) {
							$cust_old_fields[$v['id']] = $oldfields;
						}
					}
				}
				$cstring_search .= '</div>'."\n";
				/**
				 * Add the necessary JS code for the arrow navigation.
				 * 
				 * @since 	1.13
				 */
				$cstring_search .= '<script type="text/javascript">';
				$cstring_search .= '
var vboCust = jQuery(".vbo-custsearchres-entry");
var vboCustSelected = null;
jQuery(window).keydown(function(e) {
	if (e.which === 40) {
		if (vboCustSelected) {
			vboCustSelected.removeClass("vbo-custsearchres-entry-highligthed");
			next = vboCustSelected.next();
			if (next.length > 0) {
				vboCustSelected = next.addClass("vbo-custsearchres-entry-highligthed");
			} else {
				vboCustSelected = vboCust.eq(0).addClass("vbo-custsearchres-entry-highligthed");
			}
		} else {
			vboCustSelected = vboCust.eq(0).addClass("vbo-custsearchres-entry-highligthed");
		}
	} else if (e.which === 38) {
		if (vboCustSelected) {
			vboCustSelected.removeClass("vbo-custsearchres-entry-highligthed");
			next = vboCustSelected.prev();
			if (next.length > 0) {
				vboCustSelected = next.addClass("vbo-custsearchres-entry-highligthed");
			} else {
				vboCustSelected = vboCust.last().addClass("vbo-custsearchres-entry-highligthed");
			}
		} else {
			vboCustSelected = vboCust.last().addClass("vbo-custsearchres-entry-highligthed");
		}
	} else if (e.which === 13) {
		if (vboCustSelected) {
			vboCustSelected.trigger("click");
		}
	}
});
jQuery(".vbo-custsearchres-entry").hover(function() {
	if (vboCustSelected) {
		vboCustSelected.removeClass("vbo-custsearchres-entry-highligthed");
		vboCustSelected = null;
	}
	vboCustSelected = jQuery(this).addClass("vbo-custsearchres-entry-highligthed");
}, function() {
	if (vboCustSelected) {
		vboCustSelected.removeClass("vbo-custsearchres-entry-highligthed");
		vboCustSelected = null;
	}
	jQuery(this).removeClass("vbo-custsearchres-entry-highligthed");
});';
				$cstring_search .= '</script>';
				//

				$cstring = json_encode(array(($nopin > 0 ? '' : $cust_old_fields), $cstring_search));
			}
		}
		echo $cstring;
		exit;
	}

	public function sharesignaturelink() {
		//to be called via ajax
		$dbo = JFactory::getDBO();
		$response = array(
			'status' => 0,
			'error' => 'Generic Error'
		);
		$pbid = VikRequest::getInt('bid', '', 'request');
		$phow = VikRequest::getString('how', '', 'request');
		$pto = VikRequest::getString('to', '', 'request');
		$pcustomer = VikRequest::getInt('customer', '', 'request');
		$cpin = VikBooking::getCPinIstance();
		$customer_info = $cpin->getCustomerByID($pcustomer);
		if (!empty($pbid) && !empty($phow) && !empty($pto) && count($customer_info) > 0) {
			$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`=".(int)$pbid." AND `status`='confirmed' AND `checked` > 0;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$row = $dbo->loadAssoc();
				$share_link = JUri::root().'index.php?option=com_vikbooking&task=signature&sid='.$row['sid'].'&ts='.$row['ts'];
				/**
				 * @wponly 	Rewrite URI for front-end signature
				 */
				$share_link = str_replace(JUri::root(), '', $share_link);
				$model 		= JModel::getInstance('vikbooking', 'shortcodes');
				$itemid 	= $model->all('post_id');
				if (count($itemid)) {
					$share_link = JRoute::rewrite($share_link . "&Itemid={$itemid[0]->post_id}", false);
				}
				//
				$share_message = JText::sprintf('VBOSIGNSHAREMESSAGE', ltrim($customer_info['first_name'].' '.$customer_info['last_name']), $share_link, VikBooking::getFrontTitle());
				if ($phow == 'email') {
					$sender = VikBooking::getSenderMail();
					$vbo_app = new VboApplication();
					$vbo_app->sendMail($sender, $sender, $pto, $sender, JText::translate('VBOSIGNSHARESUBJECT'), $share_message, false);
					$response['status'] = 1;
				} elseif ($phow == 'sms') {
					$share_message = JText::sprintf('VBOSIGNSHAREMESSAGESMS', ltrim($customer_info['first_name'].' '.$customer_info['last_name']), $share_link, VikBooking::getFrontTitle());
					$sms_api = VikBooking::getSMSAPIClass();
					$sms_api_params = VikBooking::getSMSParams();
					if (!empty($sms_api) && file_exists(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'smsapi'.DIRECTORY_SEPARATOR.$sms_api) && !empty($sms_api_params)) {
						require_once(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'smsapi'.DIRECTORY_SEPARATOR.$sms_api);
						$sms_obj = new VikSmsApi(array(), $sms_api_params);
						$response_obj = $sms_obj->sendMessage($pto, $share_message);
						if ($sms_obj->validateResponse($response_obj)) {
							$response['status'] = 1;
						} else {
							$response['error'] = $sms_obj->getLog();
						}
					} else {
						$response['error'] = 'No SMS Provider Configured';
					}
				} else {
					$response['error'] = 'Invalid Sending Method';
				}
			} else {
				$response['error'] = 'Invalid Booking ID';
			}
		} else {
			$response['error'] = 'Empty values';
		}

		echo json_encode($response);
		exit;
	}

	public function dayselectioncount() {
		//to be called via ajax
		$tsinit = VikRequest::getString('dinit', '', 'request');
		$tsend = VikRequest::getString('dend', '', 'request');
		if (strlen($tsinit) > 0 && strlen($tsend) > 0) {
			$ptsinit=VikBooking::getDateTimestamp($tsinit, '0', '0');
			$ptsend=VikBooking::getDateTimestamp($tsend, '23', '59');
			$diff = $ptsend - $ptsinit;
			if ($diff >= 172800) {
				$datef = VikBooking::getDateFormat(true);
				if ($datef=="%d/%m/%Y") {
					$df = 'd-m-Y';
				} else {
					$df = 'Y-m-d';
				}
				//minimum 2 days for excluding some days
				$daysdiff = floor($diff / 86400);
				$infoinit = getdate($ptsinit);
				$select = '';
				$select .= '<div style="display: inline-block;"><select name="excludeday[]" multiple="multiple" size="'.($daysdiff > 8 ? 8 : $daysdiff).'" id="vboexclusion">';
				for($i = 0; $i <= $daysdiff; $i++) {
					$ts = $i > 0 ? mktime(0, 0, 0, $infoinit['mon'], ((int)$infoinit['mday'] + $i), $infoinit['year']) : $ptsinit;
					$infots = getdate($ts);
					$optval = $infots['mon'].'-'.$infots['mday'].'-'.$infots['year'];
					$select .= '<option value="'.$optval.'">'.date($df, $ts).'</option>';
				}
				$select .= '</select></div>';
				//excluded days of the week
				if ($daysdiff >= 14) {
					$select .= '<div style="display: inline-block; margin-left: 40px;"><select name="excludewdays[]" multiple="multiple" size="8" id="excludewdays" onchange="vboExcludeWDays();">';
					$select .= '<optgroup label="'.JText::translate('VBOEXCLWEEKD').'">';
					$select .= '<option value="0">'.JText::translate('VBSUNDAY').'</option><option value="1">'.JText::translate('VBMONDAY').'</option><option value="2">'.JText::translate('VBTUESDAY').'</option><option value="3">'.JText::translate('VBWEDNESDAY').'</option><option value="4">'.JText::translate('VBTHURSDAY').'</option><option value="5">'.JText::translate('VBFRIDAY').'</option><option value="6">'.JText::translate('VBSATURDAY').'</option>';
					$select .= '</optgroup>';
					$select .= '</select></div>';
				}
				//
				echo $select;
			} else {
				echo '';
			}
		} else {
			echo '';
		}
		exit;
	}

	public function createcheckindoc() {
		$cid = VikRequest::getVar('cid', array(0));
		$id = $cid[0];

		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$vbo_tn = VikBooking::getTranslator();
		$lang = JFactory::getLanguage();
		$ptmpl = VikRequest::getString('tmpl', '', 'request');
		$psignature = VikRequest::getString('signature', '', 'request', VIKREQUEST_ALLOWRAW);
		$ppad_width = VikRequest::getInt('pad_width', '', 'request');
		$ppad_ratio = VikRequest::getInt('pad_ratio', '', 'request');
		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`=".(int)$id." AND `status`='confirmed' AND `checked` > 0;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() < 1) {
			$mainframe->redirect('index.php');
			exit;
		}
		$row = $dbo->loadAssoc();
		if (!empty($row['lang'])) {
			if ($lang->getTag() != $row['lang']) {
				/**
				 * @wponly 	constant name for lang path is different
				 */
				$lang->load('com_vikbooking', VIKBOOKING_LANG, $row['lang'], true);
			}
			if ($vbo_tn->getDefaultLang() != $row['lang']) {
				// force the translation to start because contents should be translated
				$vbo_tn::$force_tolang = $row['lang'];
			}
		}
		$customer = array();
		$q = "SELECT `c`.*,`co`.`idorder`,`co`.`signature`,`co`.`pax_data`,`co`.`comments`,`co`.`checkindoc` FROM `#__vikbooking_customers` AS `c` LEFT JOIN `#__vikbooking_customers_orders` `co` ON `c`.`id`=`co`.`idcustomer` WHERE `co`.`idorder`=".$row['id'].";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$customer = $dbo->loadAssoc();
			if (!empty($customer['country'])) {
				if (file_exists(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'countries'.DIRECTORY_SEPARATOR.$customer['country'].'.png')) {
					$customer['country_img'] = '<img src="'.VBO_ADMIN_URI.'resources/countries/'.$customer['country'].'.png'.'" title="'.$customer['country'].'" class="vbo-country-flag vbo-country-flag-left"/>';
				}
			}
		}
		if (!(count($customer) > 0)) {
			VikError::raiseWarning('', JText::translate('VBOCHECKINERRNOCUSTOMER'));
			$mainframe->redirect('index.php?option=com_vikbooking&task=newcustomer&checkin=1&bid='.$row['id'].($ptmpl == 'component' ? '&tmpl=component' : ''));
			exit;
		}
		$customer['pax_data'] = !empty($customer['pax_data']) ? json_decode($customer['pax_data'], true) : array();
		//check if the signature has been submitted
		$signature_data = '';
		$cont_type = '';
		if (!empty($psignature)) {
			//check whether the format is accepted
			if (strpos($psignature, 'image/png') !== false || strpos($psignature, 'image/jpeg') !== false || strpos($psignature, 'image/svg') !== false) {
				$parts = explode(';base64,', $psignature);
				$cont_type_parts = explode('image/', $parts[0]);
				$cont_type = $cont_type_parts[1];
				if (!empty($parts[1])) {
					$signature_data = base64_decode($parts[1]);
				}
			}
		}
		if (!empty($signature_data)) {
			//write file
			$sign_fname = $row['id'].'_'.$row['sid'].'_'.$customer['id'].'.'.$cont_type;
			$filepath = VBO_ADMIN_PATH . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'idscans' . DIRECTORY_SEPARATOR . $sign_fname;
			$fp = fopen($filepath, 'w+');
			$bytes = fwrite($fp, $signature_data);
			fclose($fp);
			if ($bytes !== false && $bytes > 0) {
				//update the signature in the DB
				$q = "UPDATE `#__vikbooking_customers_orders` SET `signature`=".$dbo->quote($sign_fname)." WHERE `idorder`=".(int)$row['id'].";";
				$dbo->setQuery($q);
				$dbo->execute();
				$customer['signature'] = $sign_fname;
				//resize image for screens with high resolution
				if ($ppad_ratio > 1) {
					$new_width = floor(($ppad_width / 2));
					$creativik = new vikResizer();
					$creativik->proportionalImage($filepath, $filepath, $new_width, $new_width);
				} else {
					/**
					 * @wponly - trigger files mirroring
					 */
					VikBookingLoader::import('update.manager');
					VikBookingUpdateManager::triggerUploadBackup($filepath);
					//
				}
				//
			} else {
				VikError::raiseWarning('', JText::translate('VBOERRSTORESIGNFILE'));
			}
		}
		//
		//generate PDF for check-in document by parsing the apposite template file
		$booking_rooms = array();
		$q = "SELECT `or`.*,`r`.`name` AS `room_name`,`r`.`fromadult`,`r`.`toadult` FROM `#__vikbooking_ordersrooms` AS `or` LEFT JOIN `#__vikbooking_rooms` `r` ON `r`.`id`=`or`.`idroom` WHERE `or`.`idorder`=".(int)$row['id'].";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$booking_rooms = $dbo->loadAssocList();
			if (!empty($row['lang'])) {
				$vbo_tn->translateContents($booking_rooms, '#__vikbooking_rooms', array('id' => 'idroom', 'room_name' => 'name'), array(), $row['lang']);
			}
		}
		if (!class_exists('TCPDF')) {
			require_once(VBO_SITE_PATH . DS . "helpers" . DS . "tcpdf" . DS . 'tcpdf.php');
		}
		$usepdffont = file_exists(VBO_SITE_PATH . DS . "helpers" . DS . "tcpdf" . DS . "fonts" . DS . "dejavusans.php") ? 'dejavusans' : 'helvetica';
		list($checkintpl, $pdfparams) = VikBooking::loadCheckinDocTmpl($row, $booking_rooms, $customer);
		$checkin_body = VikBooking::parseCheckinDocTemplate($checkintpl, $row, $booking_rooms, $customer);
		$pdffname = $row['id'] . '_' . $row['sid'] . '.pdf';
		$pathpdf = VBO_SITE_PATH . DS . "helpers" . DS . "checkins" . DS . "generated" . DS . $pdffname;
		if (file_exists($pathpdf)) @unlink($pathpdf);
		$pdf_page_format = is_array($pdfparams['pdf_page_format']) ? $pdfparams['pdf_page_format'] : constant($pdfparams['pdf_page_format']);
		$pdf = new TCPDF(constant($pdfparams['pdf_page_orientation']), constant($pdfparams['pdf_unit']), $pdf_page_format, true, 'UTF-8', false);
		$pdf->SetTitle(JText::translate('VBOCHECKINDOCTITLE'));
		//Header for each page of the pdf
		if ($pdfparams['show_header'] == 1 && count($pdfparams['header_data']) > 0) {
			$pdf->SetHeaderData($pdfparams['header_data'][0], $pdfparams['header_data'][1], $pdfparams['header_data'][2], $pdfparams['header_data'][3], $pdfparams['header_data'][4], $pdfparams['header_data'][5]);
		}
		//header and footer fonts
		$pdf->setHeaderFont(array($usepdffont, '', $pdfparams['header_font_size']));
		$pdf->setFooterFont(array($usepdffont, '', $pdfparams['footer_font_size']));
		//margins
		$pdf->SetMargins(constant($pdfparams['pdf_margin_left']), constant($pdfparams['pdf_margin_top']), constant($pdfparams['pdf_margin_right']));
		$pdf->SetHeaderMargin(constant($pdfparams['pdf_margin_header']));
		$pdf->SetFooterMargin(constant($pdfparams['pdf_margin_footer']));
		//
		$pdf->SetAutoPageBreak(true, constant($pdfparams['pdf_margin_bottom']));
		$pdf->setImageScale(constant($pdfparams['pdf_image_scale_ratio']));
		$pdf->SetFont($usepdffont, '', (int)$pdfparams['body_font_size']);
		if ($pdfparams['show_header'] == 0 || !(count($pdfparams['header_data']) > 0)) {
			$pdf->SetPrintHeader(false);
		}
		if ($pdfparams['show_footer'] == 0) {
			$pdf->SetPrintFooter(false);
		}
		$pdf->AddPage();
		$pdf->writeHTML($checkin_body, true, false, true, false, '');
		$pdf->lastPage();
		$pdf->Output($pathpdf, 'F');
		if (!file_exists($pathpdf)) {
			VikError::raiseWarning('', JText::translate('VBOERRGENCHECKINDOC'));
		} else {
			$q = "UPDATE `#__vikbooking_customers_orders` SET `checkindoc`=".$dbo->quote($pdffname)." WHERE `idorder`=".(int)$row['id'].";";
			$dbo->setQuery($q);
			$dbo->execute();
			$mainframe->enqueueMessage(JText::translate('VBOGENCHECKINDOCSUCCESS'));
			/**
			 * @wponly - trigger files mirroring
			 */
			VikBookingLoader::import('update.manager');
			VikBookingUpdateManager::triggerUploadBackup($pathpdf);
			//
		}
		//
		/**
		 * @wponly - this task is executed via Ajax for the Modal forms listener. We cannot redirect to tmpl=component
		 */
		$mainframe->redirect('index.php?option=com_vikbooking&task=bookingcheckin&cid[]='.$row['id']);
		exit;
	}

	public function updatebookingcheckin() {
		$cid = VikRequest::getVar('cid', array(0));
		$id = $cid[0];

		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();
		$ptmpl = VikRequest::getString('tmpl', '', 'request');
		$pnewtotpaid = VikRequest::getFloat('newtotpaid', 0, 'request');
		$pguests = VikRequest::getVar('guests', array());
		$pcomments = VikRequest::getString('comments', '', 'request', VIKREQUEST_ALLOWHTML);
		$pcheckin_action = VikRequest::getInt('checkin_action', '', 'request');
		$valid_actions = array(-1, 0, 1, 2);
		if (!in_array($pcheckin_action, $valid_actions)) {
			$mainframe->redirect('index.php');
			exit;
		}
		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`=".(int)$id." AND `status`='confirmed';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() < 1) {
			$mainframe->redirect('index.php');
			exit;
		}
		$row = $dbo->loadAssoc();
		$q = "SELECT * FROM `#__vikbooking_customers_orders` WHERE `idorder`=".$row['id'].";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() < 1) {
			VikError::raiseWarning('', JText::translate('VBOCHECKINERRNOCUSTOMER'));
			$mainframe->redirect('index.php?option=com_vikbooking&task=bookingcheckin&cid[]='.$row['id'].($ptmpl == 'component' ? '&tmpl=component' : ''));
			exit;
		}
		$custorder = $dbo->loadAssoc();
		//update checked status and new total paid
		$q = "UPDATE `#__vikbooking_orders` SET `checked`=".$pcheckin_action."".($pnewtotpaid > 0 ? ', `totpaid`='.$pnewtotpaid : '')." WHERE `id`=".$row['id'].";";
		$dbo->setQuery($q);
		$dbo->execute();
		// Booking History log for new amount paid (payment update)
		if ($pnewtotpaid > 0 && $pnewtotpaid > (float)$row['totpaid']) {
			$extra_data = new stdClass;
			$extra_data->amount_paid = ($pnewtotpaid - (float)$row['totpaid']);
			VikBooking::getBookingHistoryInstance()->setBid($row['id'])->setExtraData($extra_data)->store('PU', JText::sprintf('VBOPREVAMOUNTPAID', VikBooking::numberFormat((float)$row['totpaid'])));
		}
		//
		//Booking History
		$hist_type = 'A';
		if ($pcheckin_action < 0) {
			$hist_type = 'Z';
		} elseif ($pcheckin_action == 1) {
			$hist_type = 'B';
		} elseif ($pcheckin_action == 2) {
			$hist_type = 'C';
		}
		VikBooking::getBookingHistoryInstance()->setBid($row['id'])->store('R'.$hist_type);
		//
		//Guests Details
		$guests_details = array();
		list($pax_fields, $pax_fields_attributes) = VikBooking::getPaxFields();
		// grab also the fields for front-end pre check-in
		list($pre_pax_fields, $pre_pax_fields_attributes) = VikBooking::getPaxFields(true);
		//
		foreach ($pguests as $ind => $adults) {
			foreach ($adults as $aduind => $details) {
				foreach ($pax_fields as $key => $v) {
					if (isset($details[$key]) && !empty($details[$key])) {
						if (!isset($guests_details[$ind])) {
							$guests_details[$ind] = array();
						}
						if (!isset($guests_details[$ind][$aduind])) {
							$guests_details[$ind][$aduind] = array();
						}
						$guests_details[$ind][$aduind][$key] = $details[$key];
					}
				}
				foreach ($pre_pax_fields as $key => $v) {
					if (isset($details[$key]) && !empty($details[$key])) {
						if (!isset($guests_details[$ind])) {
							$guests_details[$ind] = array();
						}
						if (!isset($guests_details[$ind][$aduind])) {
							$guests_details[$ind][$aduind] = array();
						}
						if (!isset($guests_details[$ind][$aduind][$key])) {
							$guests_details[$ind][$aduind][$key] = $details[$key];
						}
					}
				}
			}
		}
		if (count($guests_details)) {
			// current pax data may contain some extra information collected via front-end pre-checkin so we need to merge them
			$curpaxdata = json_decode($custorder['pax_data'], true);
			if (is_array($curpaxdata) && count($curpaxdata)) {
				foreach ($guests_details as $ind => $groom) {
					foreach ($groom as $aduind => $aduinfo) {
						if (isset($curpaxdata[$ind][$aduind])) {
							$guests_details[$ind][$aduind] = array_merge($curpaxdata[$ind][$aduind], $guests_details[$ind][$aduind]);
							// unset some default pax fields that were not specified now, or data cannot be deleted for guests
							foreach ($guests_details[$ind][$aduind] as $key => $det) {
								if (isset($pguests[$ind][$aduind][$key]) && empty($pguests[$ind][$aduind][$key])) {
									// this default pax field was specified as empty now, so we cannot merge it
									unset($guests_details[$ind][$aduind][$key]);
								}
							}
						}
					}
				}
			}
			//
			$q = "UPDATE `#__vikbooking_customers_orders` SET `pax_data`=".$dbo->quote(json_encode($guests_details))." WHERE `id`=".$custorder['id'].";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		//'checked' status comments
		$q = "UPDATE `#__vikbooking_customers_orders` SET `comments`=".$dbo->quote($pcomments)." WHERE `id`=".$custorder['id'].";";
		$dbo->setQuery($q);
		$dbo->execute();
		$mainframe->enqueueMessage(JText::translate('VBOCHECKINSTATUSUPDATED'));
		$mainframe->redirect('index.php?option=com_vikbooking&task=bookingcheckin&cid[]='.$row['id'].($pcheckin_action != $row['checked'] ? '&changed=1' : '').($ptmpl == 'component' ? '&tmpl=component' : ''));
		exit;
	}

	public function alterbooking() {
		$dbo = JFactory::getDbo();
		$user = JFactory::getUser();
		$response = array('esit' => 1, 'message' => '', 'vcm' => '');
		$pdebug = VikRequest::getInt('e4j_debug', '', 'request');
		$pidorder = VikRequest::getString('idorder', '', 'request');
		$pidorder = intval(str_replace('-', '', $pidorder));
		$poldidroom = VikRequest::getInt('oldidroom', '', 'request');
		$pidroom = VikRequest::getInt('idroom', '', 'request');
		$pfromdate = VikRequest::getString('fromdate', '', 'request');
		$ptodate = VikRequest::getString('todate', '', 'request');
		if ($pdebug == 1) {
			echo 'e4j.error.'.print_r($_POST, true);
			exit;
		}
		$nowdf = VikBooking::getDateFormat(true);
		if ($nowdf == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($nowdf == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}
		$pcheckinh = 0;
		$pcheckinm = 0;
		$pcheckouth = 0;
		$pcheckoutm = 0;
		$timeopst = VikBooking::getTimeOpenStore();
		if (is_array($timeopst)) {
			$opent = VikBooking::getHoursMinutes($timeopst[0]);
			$closet = VikBooking::getHoursMinutes($timeopst[1]);
			$pcheckinh = $opent[0];
			$pcheckinm = $opent[1];
			$pcheckouth = $closet[0];
			$pcheckoutm = $closet[1];
		}
		$info_tsto = getdate(strtotime($ptodate));
		$actualtsto = mktime(0, 0, 0, $info_tsto['mon'], ($info_tsto['mday'] + 1), $info_tsto['year']);
		$first = VikBooking::getDateTimestamp(date($df, strtotime($pfromdate)), $pcheckinh, $pcheckinm);
		$second = VikBooking::getDateTimestamp(date($df, $actualtsto), $pcheckouth, $pcheckoutm);
		$ptodate = date('Y-m-d', $second);
		if (!($second > $first)) {
			echo 'e4j.error.1 '.addslashes(JText::translate('VBOVWALTBKERRMISSDATA'));
			exit;
		}
		if (!($pidorder > 0) || !($pidroom > 0) || empty($pfromdate) || empty($ptodate)) {
			echo 'e4j.error.2 '.addslashes(JText::translate('VBOVWALTBKERRMISSDATA'));
			exit;
		}
		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`='".$pidorder."' AND `status`='confirmed';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() != 1) {
			echo 'e4j.error.3 '.addslashes(JText::translate('VBOVWALTBKERRMISSDATA'));
			exit;
		}
		$ord = $dbo->loadAssocList();
		$q = "SELECT `or`.*,`r`.`name`,`r`.`idopt`,`r`.`units`,`r`.`fromadult`,`r`.`toadult` FROM `#__vikbooking_ordersrooms` AS `or`,`#__vikbooking_rooms` AS `r` WHERE `or`.`idorder`=".$ord[0]['id']." AND `or`.`idroom`=`r`.`id` ORDER BY `or`.`id` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		$ordersrooms = $dbo->loadAssocList();
		$ord[0]['rooms_info'] = $ordersrooms;
		//Package or custom rate
		$is_package = !empty($ord[0]['pkg']) ? true : false;
		$is_cust_cost = false;
		foreach ($ordersrooms as $kor => $or) {
			if ($is_package !== true && !empty($or['cust_cost']) && $or['cust_cost'] > 0.00) {
				$is_cust_cost = true;
				break;
			}
		}
		//
		$toswitch = array();
		$idbooked = array();
		$rooms_units = array();
		$q = "SELECT `id`,`name`,`units` FROM `#__vikbooking_rooms`;";
		$dbo->setQuery($q);
		$dbo->execute();
		$all_rooms = $dbo->loadAssocList();
		foreach ($all_rooms as $rr) {
			$rooms_units[$rr['id']]['name'] = $rr['name'];
			$rooms_units[$rr['id']]['units'] = $rr['units'];
		}
		//Switch room
		if ($poldidroom != $pidroom) {
			foreach ($ordersrooms as $ind => $or) {
				if ($poldidroom == $or['idroom'] && array_key_exists($pidroom, $rooms_units)) {
					if (!isset($idbooked[$or['idroom']])) {
						$idbooked[$or['idroom']] = 0;
					}
					//$idbooked is not really needed as switch is never made for the same room id
					$idbooked[$or['idroom']]++;
					//
					$orkey = count($toswitch);
					$toswitch[$orkey]['from'] = $or['idroom'];
					$toswitch[$orkey]['to'] = intval($pidroom);
					$toswitch[$orkey]['record'] = $or;
					break;
				}
			}
		}
		if (count($toswitch) > 0) {
			foreach ($toswitch as $ksw => $rsw) {
				$plusunit = array_key_exists($rsw['to'], $idbooked) ? $idbooked[$rsw['to']] : 0;
				if (!VikBooking::roomBookable($rsw['to'], ($rooms_units[$rsw['to']]['units'] + $plusunit), $ord[0]['checkin'], $ord[0]['checkout'])) {
					unset($toswitch[$ksw]);
					echo 'e4j.error.'.JText::sprintf('VBSWITCHRERR', $rsw['record']['name'], $rooms_units[$rsw['to']]['name']);
					exit;
				}
			}
			if (count($toswitch) > 0) {
				//reset first record rate so that rates can be set again (rates are unset only if the room is switched, if just the dates are different the rates are kept equal as the num nights is the same)
				reset($ordersrooms);
				$q = "UPDATE `#__vikbooking_ordersrooms` SET `idtar`=NULL,`roomindex`=NULL,`room_cost`=NULL WHERE `id`=".$ordersrooms[0]['id'].";";
				$dbo->setQuery($q);
				$dbo->execute();
				//
				foreach ($toswitch as $ksw => $rsw) {
					$q = "UPDATE `#__vikbooking_ordersrooms` SET `idroom`=".$rsw['to'].",`idtar`=NULL,`roomindex`=NULL,`room_cost`=NULL WHERE `id`=".$rsw['record']['id'].";";
					$dbo->setQuery($q);
					$dbo->execute();
					$response['message'] .= JText::sprintf('VBOVWALTBKSWITCHROK', $rsw['record']['name'], $rooms_units[$rsw['to']]['name'])."\n";
					//update Notes field for this booking to keep track of the previous room that was assigned
					$prev_room_name = array_key_exists($rsw['from'], $rooms_units) ? $rooms_units[$rsw['from']]['name'] : '';
					if (!empty($prev_room_name)) {
						$new_notes = JText::sprintf('VBOPREVROOMMOVED', $prev_room_name, date($df.' H:i:s'))."\n".$ord[0]['adminnotes'];
						$q = "UPDATE `#__vikbooking_orders` SET `adminnotes`=".$dbo->quote($new_notes)." WHERE `id`=".(int)$ord[0]['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
					}
					//
					if ($ord[0]['status'] == 'confirmed') {
						//update record in _busy
						$q = "SELECT `b`.`id`,`b`.`idroom`,`ob`.`idorder` FROM `#__vikbooking_busy` AS `b`,`#__vikbooking_ordersbusy` AS `ob` WHERE `b`.`idroom`=" . $rsw['from'] . " AND `b`.`id`=`ob`.`idbusy` AND `ob`.`idorder`=".$ord[0]['id']." LIMIT 1;";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() == 1) {
							$cur_busy = $dbo->loadAssocList();
							$q = "UPDATE `#__vikbooking_busy` SET `idroom`=".$rsw['to']." WHERE `id`=".$cur_busy[0]['id']." AND `idroom`=".$cur_busy[0]['idroom']." LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
						//if automated updates enabled, keep $response['vcm'] empty
						//Invoke Channel Manager
						if (file_exists(VCM_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "synch.vikbooking.php")) {
							$response['vcm'] = JText::translate('VBCHANNELMANAGERINVOKEASK').' <form action="index.php?option=com_vikbooking" method="post"><input type="hidden" name="option" value="com_vikbooking"/><input type="hidden" name="task" value="invoke_vcm"/><input type="hidden" name="stype" value="modify"/><input type="hidden" name="cid[]" value="'.$ord[0]['id'].'"/><input type="hidden" name="origb" value="'.urlencode(json_encode($ord[0])).'"/><input type="hidden" name="returl" value="'.urlencode("index.php?option=com_vikbooking&task=overview").'"/><button type="submit" class="btn btn-primary">'.JText::translate('VBCHANNELMANAGERSENDRQ').'</button></form>';
						}
						//
					} elseif ($ord[0]['status'] == 'standby') {
						//remove record in _tmplock
						$q = "DELETE FROM `#__vikbooking_tmplock` WHERE `idorder`=" . intval($ord[0]['id']) . ";";
						$dbo->setQuery($q);
						$dbo->execute();
					}
				}
				//do not terminate the process when there is a switch, proceed to check the dates.
			}
		}
		//end Switch room
		//Change Dates
		if (date('Y-m-d', $ord[0]['checkin']) != $pfromdate || date('Y-m-d', $ord[0]['checkout']) != $ptodate) {
			$daysdiff = $ord[0]['days'];
			//re-read ordersrooms (as rooms may have been switched)
			$q = "SELECT `or`.*,`r`.`name`,`r`.`idopt`,`r`.`units`,`r`.`fromadult`,`r`.`toadult` FROM `#__vikbooking_ordersrooms` AS `or`,`#__vikbooking_rooms` AS `r` WHERE `or`.`idorder`=".$ord[0]['id']." AND `or`.`idroom`=`r`.`id` ORDER BY `or`.`id` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$ordersrooms = $dbo->loadAssocList();
			//
			$groupdays = VikBooking::getGroupDays($first, $second, $daysdiff);
			$opertwounits = true;
			$units_counter = array();
			foreach ($ordersrooms as $ind => $or) {
				if (!isset($units_counter[$or['idroom']])) {
					$units_counter[$or['idroom']] = -1;
				}
				$units_counter[$or['idroom']]++;
			}
			foreach ($ordersrooms as $ind => $or) {
				$num = $ind + 1;
				$check = "SELECT `b`.`id`,`b`.`checkin`,`b`.`realback`,`ob`.`idorder` FROM `#__vikbooking_busy` AS `b`,`#__vikbooking_ordersbusy` AS `ob` WHERE `b`.`idroom`=" . $or['idroom'] . " AND `b`.`realback`>=" . $first . " AND `b`.`id`=`ob`.`idbusy` AND `ob`.`idorder`!=" . $ord[0]['id'] . ";";
				$dbo->setQuery($check);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$busy = $dbo->loadAssocList();
					foreach ($groupdays as $gday) {
						$bfound = 0;
						foreach ($busy as $bu) {
							if ($gday >= $bu['checkin'] && $gday <= $bu['realback']) {
								$bfound++;
							}
						}
						if ($bfound >= ($or['units'] - $units_counter[$or['idroom']]) || !VikBooking::roomNotLocked($or['idroom'], $or['units'], $first, $second)) {
							$opertwounits = false;
							break 2;
						}
					}
				}
			}
			if ($opertwounits !== true) {
				$response['esit'] = 0;
				$response['message'] = JText::translate('VBROOMNOTRIT')." ".date($df.' H:i', $first)." ".JText::translate('VBROOMNOTCONSTO')." ".date($df.' H:i', $second);
				echo json_encode($response);
				exit;
			}
			//update dates and busy records
			$realback = VikBooking::getHoursRoomAvail() * 3600;
			$realback += $second;
			$q = "UPDATE `#__vikbooking_orders` SET `checkin`='".$first."', `checkout`='".$second."' WHERE `id`=".$ord[0]['id'].";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($ord[0]['status'] == 'confirmed') {
				$q = "SELECT `b`.`id` FROM `#__vikbooking_busy` AS `b`,`#__vikbooking_ordersbusy` AS `ob` WHERE `b`.`id`=`ob`.`idbusy` AND `ob`.`idorder`=".$ord[0]['id'].";";
				$dbo->setQuery($q);
				$dbo->execute();
				$allbusy = $dbo->loadAssocList();
				foreach ($allbusy as $bb) {
					$q = "UPDATE `#__vikbooking_busy` SET `checkin`='".$first."', `checkout`='".$second."', `realback`='".$realback."' WHERE `id`='".$bb['id']."';";
					$dbo->setQuery($q);
					$dbo->execute();
				}
				//if automated updates enabled, keep $response['vcm'] empty
				//Invoke Channel Manager
				if (file_exists(VCM_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "synch.vikbooking.php")) {
					$response['vcm'] = JText::translate('VBCHANNELMANAGERINVOKEASK').' <form action="index.php?option=com_vikbooking" method="post"><input type="hidden" name="option" value="com_vikbooking"/><input type="hidden" name="task" value="invoke_vcm"/><input type="hidden" name="stype" value="modify"/><input type="hidden" name="cid[]" value="'.$ord[0]['id'].'"/><input type="hidden" name="origb" value="'.urlencode(json_encode($ord[0])).'"/><input type="hidden" name="returl" value="'.urlencode("index.php?option=com_vikbooking&task=overview").'"/><button type="submit" class="btn btn-primary">'.JText::translate('VBCHANNELMANAGERSENDRQ').'</button></form>';
				}
				//
			}
			$response['message'] .= JText::translate('RESUPDATED')."\n";
			//
		}
		//end Change Dates
		
		if (count($toswitch) > 0) {
			//TODO: rooms have changed so the new rates must be re-calculated. Maybe they should be calculated in any case, even if just the dates have changed. For the moment the rates are reset
		}

		// unset any previously booked room due to calendar sharing
		VikBooking::cleanSharedCalendarsBusy($ord[0]['id']);
		// check if some of the rooms booked have shared calendars
		VikBooking::updateSharedCalendars($ord[0]['id']);
		//

		//Booking History
		VikBooking::getBookingHistoryInstance()->setBid($ord[0]['id'])->store('MB', "({$user->name}) " . VikBooking::getLogBookingModification($ord[0]));
		//

		$vcm_autosync = VikBooking::vcmAutoUpdate();
		if ($vcm_autosync > 0 && !empty($response['vcm'])) {
			//unset the vcm property as no buttons should be displayed when in auto-sync
			$response['vcm'] = '';
			$vcm_obj = VikBooking::getVcmInvoker();
			$vcm_obj->setOids(array($ord[0]['id']))->setSyncType('modify')->setOriginalBooking($ord[0]);
			$sync_result = $vcm_obj->doSync();
			if ($sync_result === false) {
				$response['message'] .= JText::translate('VBCHANNELMANAGERRESULTKO')." (".$vcm_obj->getError().")\n";
			}
		}

		//in case of error but not empty VCM message, set an error that will be displayed after the mustReload
		if ($response['esit'] < 1 && !empty($response['vcm'])) {
			VikError::raiseNotice('', $response['vcm']);
		}
		//
		
		$response['message'] = nl2br($response['message']);
		echo json_encode($response);
		exit;
	}

	public function modroomrateplans() {
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$updforvcm = $session->get('vbVcmRatesUpd', '');
		$updforvcm = empty($updforvcm) || !is_array($updforvcm) ? array() : $updforvcm;
		$pid_room = VikRequest::getInt('id_room', '', 'request');
		$pid_price = VikRequest::getInt('id_price', '', 'request');
		$ptype = VikRequest::getString('type', '', 'request');
		$pfromdate = VikRequest::getString('fromdate', '', 'request');
		$ptodate = VikRequest::getString('todate', '', 'request');
		if (empty($pid_room) || empty($pid_price) || empty($ptype) || empty($pfromdate) || empty($ptodate) || !(strtotime($pfromdate) > 0)  || !(strtotime($ptodate) > 0)) {
			echo 'e4j.error.'.addslashes(JText::translate('VBRATESOVWERRMODRPLANS'));
			exit;
		}
		$price_record = array();
		$q = "SELECT * FROM `#__vikbooking_prices` WHERE `id`=".$pid_price.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$price_record = $dbo->loadAssoc();
		}
		if (!count($price_record) > 0) {
			echo 'e4j.error.'.addslashes(JText::translate('VBRATESOVWERRMODRPLANS')).'.';
			exit;
		}
		$current_closed = array();
		if (!empty($price_record['closingd'])) {
			$current_closed = json_decode($price_record['closingd'], true);
		}
		$current_closed = !is_array($current_closed) ? array() : $current_closed;
		$start_ts = strtotime($pfromdate);
		$end_ts = strtotime($ptodate);
		$infostart = getdate($start_ts);
		$all_days = array();
		$output = array();
		while ($infostart[0] > 0 && $infostart[0] <= $end_ts) {
			$all_days[] = date('Y-m-d', $infostart[0]);
			$indkey = $infostart['mday'].'-'.$infostart['mon'].'-'.$infostart['year'].'-'.$pid_price;
			$output[$indkey] = array();
			$infostart = getdate(mktime(0, 0, 0, $infostart['mon'], ($infostart['mday'] + 1), $infostart['year']));
		}
		if ($ptype == 'close') {
			if (!array_key_exists($pid_room, $current_closed)) {
				$current_closed[$pid_room] = array();
			}
			foreach ($all_days as $daymod) {
				if (!in_array($daymod, $current_closed[$pid_room])) {
					$current_closed[$pid_room][] = $daymod;
				}
			}
		} else {
			//open
			if (array_key_exists($pid_room, $current_closed)) {
				foreach ($all_days as $daymod) {
					if (in_array($daymod, $current_closed[$pid_room])) {
						foreach ($current_closed[$pid_room] as $ck => $cv) {
							if ($daymod == $cv) {
								unset($current_closed[$pid_room][$ck]);
							}
						}
					}
				}
			} else {
				$current_closed[$pid_room] = array();
			}
		}
		if (!count($current_closed[$pid_room]) > 0) {
			unset($current_closed[$pid_room]);
		}
		$q = "UPDATE `#__vikbooking_prices` SET `closingd`=".(count($current_closed) > 0 ? $dbo->quote(json_encode($current_closed)) : "NULL")." WHERE `id`=".(int)$pid_price.";";
		$dbo->setQuery($q);
		$dbo->execute();
		$oldcsscls = $ptype == 'close' ? 'vbo-roverw-rplan-on' : 'vbo-roverw-rplan-off';
		$newcsscls = $ptype == 'close' ? 'vbo-roverw-rplan-off' : 'vbo-roverw-rplan-on';
		foreach ($output as $ok => $ov) {
			$output[$ok] = array('oldcls' => $oldcsscls, 'newcls' => $newcsscls);
		}
		//update session values
		$updforvcm['count'] = array_key_exists('count', $updforvcm) && !empty($updforvcm['count']) ? ($updforvcm['count'] + 1) : 1;
		if (array_key_exists('dfrom', $updforvcm) && !empty($updforvcm['dfrom'])) {
			$updforvcm['dfrom'] = $updforvcm['dfrom'] > $start_ts ? $start_ts : $updforvcm['dfrom'];
		} else {
			$updforvcm['dfrom'] = $start_ts;
		}
		if (array_key_exists('dto', $updforvcm) && !empty($updforvcm['dto'])) {
			$updforvcm['dto'] = $updforvcm['dto'] < $end_ts ? $end_ts : $updforvcm['dto'];
		} else {
			$updforvcm['dto'] = $end_ts;
		}
		if (array_key_exists('rooms', $updforvcm) && is_array($updforvcm['rooms'])) {
			if (!in_array($pid_room, $updforvcm['rooms'])) {
				$updforvcm['rooms'][] = $pid_room;
			}
		} else {
			$updforvcm['rooms'] = array($pid_room);
		}
		if (array_key_exists('rplans', $updforvcm) && is_array($updforvcm['rplans'])) {
			if (array_key_exists($pid_room, $updforvcm['rplans'])) {
				if (!in_array($pid_price, $updforvcm['rplans'][$pid_room])) {
					$updforvcm['rplans'][$pid_room][] = $pid_price;
				}
			} else {
				$updforvcm['rplans'][$pid_room] = array($pid_price);
			}
		} else {
			$updforvcm['rplans'] = array($pid_room => array($pid_price));
		}
		$session->set('vbVcmRatesUpd', $updforvcm);
		//
		$pdebug = VikRequest::getInt('e4j_debug', '', 'request');
		if ($pdebug == 1) {
			echo "e4j.error.\n".print_r($current_closed, true)."\n";
			echo print_r($output, true)."\n\n";
			echo print_r($all_days, true)."\n";
		}
		echo json_encode($output);
		exit;
	}

	function setnewrates() {
		// @wponly lite - the seasonal prices function is not available with this version of the framework
		echo 'e4j.error.'.__('This Pricing Model is only supported in the Pro version.', 'vikbooking');
		exit;
	}

	public function icsexportlaunch() {
		$dbo = JFactory::getDBO();
		$pcheckindate = VikRequest::getString('checkindate', '', 'request');
		$pcheckoutdate = VikRequest::getString('checkoutdate', '', 'request');
		$pstatus = VikRequest::getString('status', '', 'request');
		$validstatus = array('confirmed', 'standby', 'cancelled');
		$filterstatus = '';
		$filterfirst = 0;
		$filtersecond = 0;
		$nowdf = VikBooking::getDateFormat(true);
		if ($nowdf == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($nowdf == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}
		$currencyname = VikBooking::getCurrencyName();
		if (!empty($pstatus) && in_array($pstatus, $validstatus)) {
			$filterstatus = $pstatus;
		}
		if (!empty($pcheckindate)) {
			if (VikBooking::dateIsValid($pcheckindate)) {
				$first=VikBooking::getDateTimestamp($pcheckindate, '0', '0');
				$filterfirst = $first;
			}
		}
		if (!empty($pcheckoutdate)) {
			if (VikBooking::dateIsValid($pcheckoutdate)) {
				$second=VikBooking::getDateTimestamp($pcheckoutdate, '23', '59');
				if ($second > $first) {
					$filtersecond = $second;
				}
			}
		}
		$clause = array();
		if ($filterfirst > 0) {
			$clause[] = "`o`.`checkin` >= ".$filterfirst;
		}
		if ($filtersecond > 0) {
			$clause[] = "`o`.`checkout` <= ".$filtersecond;
		}
		if (!empty($filterstatus)) {
			$clause[] = "`o`.`status` = '".$filterstatus."'";
		}
		$q = "SELECT `o`.*,`or`.`idroom`,`or`.`adults`,`or`.`children`,`r`.`name` FROM `#__vikbooking_orders` AS `o` LEFT JOIN `#__vikbooking_ordersrooms` `or` ON `or`.`idorder`=`o`.`id` LEFT JOIN `#__vikbooking_rooms` `r` ON `or`.`idroom`=`r`.`id` ".(count($clause) > 0 ? "WHERE ".implode(" AND ", $clause)." " : "")."ORDER BY `o`.`checkin` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$orders = $dbo->loadAssocList();
			$icscontent = "BEGIN:VCALENDAR\n";
			$icscontent .= "VERSION:2.0\n";
			$icscontent .= "PRODID:-//e4j//VikBooking//EN\n";
			$icscontent .= "CALSCALE:GREGORIAN\n";
			$str = "";
			foreach ($orders as $kord => $ord) {
				if (isset($orders[($kord + 1)]) && $orders[($kord + 1)]['id'] == $ord['id']) {
					continue;
				}
				$usecurrencyname = $currencyname;
				$usecurrencyname = !empty($ord['idorderota']) && !empty($ord['chcurrency']) ? $ord['chcurrency'] : $usecurrencyname;
				$statusstr = '';
				if ($ord['status'] == 'confirmed') {
					$statusstr = JText::translate('VBCSVSTATUSCONFIRMED');
				} elseif ($ord['status'] == 'standby') {
					$statusstr = JText::translate('VBCSVSTATUSSTANDBY');
				} elseif ($ord['status'] == 'cancelled') {
					$statusstr = JText::translate('VBCSVSTATUSCANCELLED');
				}
				$uri = JURI::root().'index.php?option=com_vikbooking&view=booking&sid='.$ord['sid'].'&ts='.$ord['ts'];
				/**
				 * @wponly 	Rewrite URI for front-end
				 */
				$uri 		= str_replace(JUri::root(), '', $uri);
				$model 		= JModel::getInstance('vikbooking', 'shortcodes');
				$itemid 	= $model->best('booking');
				if ($itemid) {
					$uri = JRoute::rewrite($uri . "&Itemid={$itemid}", false);
				}
				//
				$ordnumbstr = $ord['id'].(!empty($ord['confirmnumber']) ? ' - '.$ord['confirmnumber'] : '').(!empty($ord['idorderota']) ? ' ('.ucwords($ord['channel']).')' : '').' - '.$statusstr;
				$peoplestr = ($ord['adults'] + $ord['children']).($ord['children'] > 0 ? ' ('.JText::translate('VBCSVCHILDREN').': '.$ord['children'].')' : '');
				$totalstring = ($ord['total'] > 0 ? ($usecurrencyname.' '.VikBooking::numberFormat($ord['total'])) : '');
				$totalpaidstring = ($ord['totpaid'] > 0 ? (' ('.VikBooking::numberFormat($ord['totpaid']).')') : '');
				$description = JText::sprintf('VBICSEXPDESCRIPTION', $ordnumbstr."\\n", $peoplestr."\\n", $ord['days']."\\n", $totalstring.$totalpaidstring."\\n", "\\n".str_replace("\n", "\\n", trim($ord['custdata'])));
				$str .= "BEGIN:VEVENT\n";
				$str .= "DTEND:".date('Ymd\THis\Z', $ord['checkout'])."\n";
				$str .= "UID:".uniqid()."\n";
				$str .= "DTSTAMP:".date('Ymd\THis\Z', time())."\n";
				$str .= ((strlen($description) > 0 ) ? "DESCRIPTION:".preg_replace('/([\,;])/','\\\$1', $description)."\n" : "");
				$str .= "URL;VALUE=URI:".preg_replace('/([\,;])/','\\\$1', $uri)."\n";
				$str .= "SUMMARY:".JText::sprintf('VBICSEXPSUMMARY', date($df, $ord['checkin']))."\n";
				$str .= "DTSTART:".date('Ymd\THis\Z', $ord['checkin'])."\n";
				$str .= "END:VEVENT\n";
			}
			$icscontent .= $str;
			$icscontent .= "END:VCALENDAR\n";
			//download file from buffer
			header("Content-Type: application/octet-stream; ");
			header("Cache-Control: no-store, no-cache");
			header('Content-Disposition: attachment; filename="bookings_export.ics"');
			$f = fopen('php://output', "w");
			fwrite($f, $icscontent);
			fclose($f);
			exit;
		} else {
			VikError::raiseWarning('', JText::translate('VBICSEXPNORECORDS'));
			$mainframe = JFactory::getApplication();
			$mainframe->redirect("index.php?option=com_vikbooking&task=icsexportprepare&checkindate=".$pcheckindate."&checkoutdate=".$pcheckoutdate."&status=".$pstatus."&tmpl=component");
		}
	}

	public function csvexportlaunch() {
		$dbo = JFactory::getDbo();
		$pdatefilt = VikRequest::getString('datefilt', '', 'request');
		$proomfilt = VikRequest::getString('roomfilt', '', 'request');
		$pchfilt = VikRequest::getString('chfilt', '', 'request');
		$ppayfilt = VikRequest::getString('payfilt', '', 'request');
		$pcheckindate = VikRequest::getString('checkindate', '', 'request');
		$pcheckoutdate = VikRequest::getString('checkoutdate', '', 'request');
		$pstatus = VikRequest::getString('status', '', 'request');
		$validstatus = array('confirmed', 'standby', 'cancelled');
		$validdates = array('ts', 'checkin', 'checkout');
		$filterdate = '';
		$filterstatus = '';
		$first = 0;
		$filterfirst = 0;
		$filtersecond = 0;
		$nowdf = VikBooking::getDateFormat(true);
		if ($nowdf == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($nowdf == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}
		$datesep = VikBooking::getDateSeparator(true);
		$currencyname = VikBooking::getCurrencyName();
		if (!empty($pstatus) && in_array($pstatus, $validstatus)) {
			$filterstatus = $pstatus;
		}
		if (!empty($pdatefilt) && in_array($pdatefilt, $validdates)) {
			$filterdate = $pdatefilt;
		}
		if (!empty($pcheckindate) && !empty($filterdate)) {
			if (VikBooking::dateIsValid($pcheckindate)) {
				$first = VikBooking::getDateTimestamp($pcheckindate, '0', '0');
				$filterfirst = $first;
			}
		}
		if (!empty($pcheckoutdate) && !empty($filterdate)) {
			if (VikBooking::dateIsValid($pcheckoutdate)) {
				$second = VikBooking::getDateTimestamp($pcheckoutdate, '23', '59');
				if ($second > $first) {
					$filtersecond = $second;
				}
			}
		}
		$clause = array();
		if ($filterfirst > 0) {
			$clause[] = "`o`.`".$filterdate."` >= ".$filterfirst;
		}
		if ($filtersecond > 0) {
			$clause[] = "`o`.`".$filterdate."` <= ".$filtersecond;
		}
		if (!empty($filterstatus)) {
			$clause[] = "`o`.`status` = '".$filterstatus."'";
		}
		if (!empty($pchfilt)) {
			$clause[] = "`o`.`channel` LIKE ".$dbo->quote("%".$pchfilt."%");
		}
		if (!empty($ppayfilt)) {
			$clause[] = "`o`.`idpayment` LIKE '".$ppayfilt."=%'";
		}
		if (!empty($proomfilt)) {
			$clause[] = "`or`.`idroom` = '".(int)$proomfilt."'";
		}
		$q = "SELECT `o`.*,`or`.`idroom`,`or`.`adults`,`or`.`children`,`or`.`idtar`,`or`.`optionals`,`or`.`t_first_name`,`or`.`t_last_name`,`or`.`extracosts`,`or`.`cust_cost`,`or`.`cust_idiva`,`or`.`room_cost`,`r`.`name`,`d`.`idprice`,`p`.`idiva`,`t`.`aliq`,`t`.`breakdown` FROM `#__vikbooking_orders` AS `o` LEFT JOIN `#__vikbooking_ordersrooms` `or` ON `or`.`idorder`=`o`.`id` LEFT JOIN `#__vikbooking_rooms` `r` ON `or`.`idroom`=`r`.`id` LEFT JOIN `#__vikbooking_dispcost` `d` ON `or`.`idtar`=`d`.`id` LEFT JOIN `#__vikbooking_prices` `p` ON `d`.`idprice`=`p`.`id` LEFT JOIN `#__vikbooking_iva` `t` ON `p`.`idiva`=`t`.`id` ".(count($clause) > 0 ? "WHERE ".implode(" AND ", $clause)." " : "")."ORDER BY `o`.`checkin` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$orders = $dbo->loadAssocList();
			//options
			$all_options = array();
			$q = "SELECT * FROM `#__vikbooking_optionals` ORDER BY `#__vikbooking_optionals`.`ordering` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$options = $dbo->loadAssocList();
				foreach ($options as $ok => $ov) {
					$all_options[$ov['id']] = $ov;
				}
			}
			//
			$orderscsv = array();
			$orderscsv[] = array(
				JText::translate('VBDASHBOOKINGID'),
				JText::translate('VBCSVCHECKIN'),
				JText::translate('VBCSVCHECKOUT'),
				JText::translate('VBCSVNIGHTS'),
				JText::translate('VBCSVROOM'),
				JText::translate('VBCSVPEOPLE'),
				JText::translate('VBCSVCUSTINFO'),
				JText::translate('ORDER_SPREQUESTS'),
				JText::translate('ORDER_NOTES'),
				JText::translate('VBCSVCREATEDBY'),
				JText::translate('VBCSVCUSTMAIL'),
				JText::translate('ORDER_PHONE'),
				JText::translate('VBCSVOPTIONS'),
				JText::translate('VBCSVPAYMENTMETHOD'),
				JText::translate('VBCSVORDIDCONFNUMB'),
				JText::translate('VBCSVEXPFILTBSTATUS'),
				JText::translate('VBCSVTOTAL'),
				JText::translate('VBCSVTOTPAID'),
				JText::translate('VBCSVTOTTAXES')
			);
			foreach ($orders as $kord => $ord) {
				$usecurrencyname = $currencyname;
				$usecurrencyname = !empty($ord['idorderota']) && !empty($ord['chcurrency']) ? $ord['chcurrency'] : $usecurrencyname;
				$peoplestr = ($ord['adults'] + $ord['children']).($ord['children'] > 0 ? ' ('.JText::translate('VBCSVCHILDREN').': '.$ord['children'].')' : '');
				$custinfostr = str_replace(",", " ", $ord['custdata']);
				$customer = VikBooking::getCPinIstance()->getCustomerFromBooking($ord['id']);
				if (count($customer)) {
					$custinfostr = $customer['first_name'] . ' ' . $customer['last_name'];
				}
				$special_requests = '';
				if (preg_match("/(?:special requests:\s*)(.*?)$/is", $ord['custdata'], $match)) {
					$special_requests = $match[1];
				} elseif (preg_match("/(?:special request:\s*)(.*?)$/is", $ord['custdata'], $match)) {
					$special_requests = $match[1];
				} elseif (preg_match("/(?:special request\s*)(.*?)$/is", $ord['custdata'], $match)) {
					$special_requests = $match[1]; 
				}
				$paystr = '';
				if (!empty($ord['idpayment'])) {
					$payparts = explode('=', $ord['idpayment']);
					$paystr = $payparts[1];
				}
				$ordnumbstr = $ord['id'].' - '.$ord['confirmnumber'].(!empty($ord['idorderota']) ? ' ('.ucwords($ord['channel']).')' : ''); 
				$statusstr = '';
				if ($ord['status'] == 'confirmed') {
					$statusstr = JText::translate('VBCSVSTATUSCONFIRMED');
				} elseif ($ord['status'] == 'standby') {
					$statusstr = JText::translate('VBCSVSTATUSSTANDBY');
				} elseif ($ord['status'] == 'cancelled') {
					$statusstr = JText::translate('VBCSVSTATUSCANCELLED');
				}
				$totalstring = $usecurrencyname . ' ' . VikBooking::numberFormat($ord['total']);
				if ($ord['roomsnum'] > 1) {
					// take the cost for the individual room
					$totalstring = !empty($ord['cust_cost']) && $ord['cust_cost'] > 0 ? ($usecurrencyname . ' ' . VikBooking::numberFormat($ord['cust_cost'])) : ($usecurrencyname . ' ' . VikBooking::numberFormat($ord['room_cost']));
				}
				$totalpaidstring = $usecurrencyname . ' ' . VikBooking::numberFormat($ord['totpaid']);
				if (isset($orders[($kord + 1)]) && $orders[($kord + 1)]['id'] == $ord['id']) {
					// total paid will be printed only for the last room booked
					$totalpaidstring = '';
				}
				$options_str = '';
				if (!empty($ord['optionals'])) {
					$stepo = explode(";", $ord['optionals']);
					foreach ($stepo as $roptkey => $oo) {
						if (!empty($oo)) {
							$stept = explode(":", $oo);
							if (array_key_exists($stept[0], $all_options)) {
								$actopt = $all_options[$stept[0]];
								$optpcent = false;
								if (!empty($actopt['ageintervals']) && $ord['children'] > 0 && strstr($stept[1], '-') != false) {
									$optagenames = VikBooking::getOptionIntervalsAges($actopt['ageintervals']);
									$optagepcent = VikBooking::getOptionIntervalsPercentage($actopt['ageintervals']);
									$optageovrct = VikBooking::getOptionIntervalChildOverrides($actopt, $ord['adults'], $ord['children']);
									$child_num 	 = VikBooking::getRoomOptionChildNumber($ord['optionals'], $actopt['id'], $roptkey, $ord['children']);
									$optagecosts = VikBooking::getOptionIntervalsCosts(isset($optageovrct['ageintervals_child' . ($child_num + 1)]) ? $optageovrct['ageintervals_child' . ($child_num + 1)] : $actopt['ageintervals']);
									$agestept = explode('-', $stept[1]);
									$stept[1] = $agestept[0];
									$chvar = $agestept[1];
									if (array_key_exists(($chvar - 1), $optagepcent) && $optagepcent[($chvar - 1)] > 0) {
										$optpcent = true;
									}
									$actopt['chageintv'] = $chvar;
									$actopt['name'] .= ' ('.$optagenames[($chvar - 1)].')';
									$realcost = (intval($actopt['perday']) == 1 ? (floatval($optagecosts[($chvar - 1)]) * $ord['days'] * $stept[1]) : (floatval($optagecosts[($chvar - 1)]) * $stept[1]));
								} else {
									// VBO 1.11 - options percentage cost of the room total fee
									$optpcent = (int)$actopt['pcentroom'] ? true : $optpcent;
									//
									$realcost = (intval($actopt['perday']) == 1 ? ($actopt['cost'] * $ord['days'] * $stept[1]) : ($actopt['cost'] * $stept[1]));
								}
								if ($actopt['maxprice'] > 0 && $realcost > $actopt['maxprice']) {
									$realcost=$actopt['maxprice'];
									if (intval($actopt['hmany']) == 1 && intval($stept[1]) > 1) {
										$realcost = $actopt['maxprice'] * $stept[1];
									}
								}
								$realcost = $actopt['perperson'] == 1 ? ($realcost * $ord['adults']) : $realcost;
								$tmpopr = VikBooking::sayOptionalsPlusIva($realcost, $actopt['idiva']);
								$options_str .= ($stept[1] > 1 ? $stept[1]." " : "").$actopt['name'].": ".(!$optpcent ? $currencyname : '')." ".VikBooking::numberFormat($tmpopr).($optpcent ? ' %' : '')." \r\n";
							}
						}
					}
				}
				//custom extra costs
				if (!empty($ord['extracosts'])) {
					$cur_extra_costs = json_decode($ord['extracosts'], true);
					foreach ($cur_extra_costs as $eck => $ecv) {
						$ecplustax = !empty($ecv['idtax']) ? VikBooking::sayOptionalsPlusIva($ecv['cost'], $ecv['idtax']) : $ecv['cost'];
						$options_str .= $ecv['name'].": ".$currencyname." ".VikBooking::numberFormat($ecplustax)." \r\n";
					}
				}
				//
				//taxes
				$taxes_str = '';
				if ($ord['tot_taxes'] > 0.00) {
					$taxes_str .= $usecurrencyname.' '.VikBooking::numberFormat($ord['tot_taxes']);
					if (!empty($ord['aliq']) && !empty($ord['breakdown'])) {
						$tax_breakdown = json_decode($ord['breakdown'], true);
						$tax_breakdown = is_array($tax_breakdown) && count($tax_breakdown) > 0 ? $tax_breakdown : array();
						if (count($tax_breakdown)) {
							foreach ($tax_breakdown as $tbkk => $tbkv) {
								$tax_break_cost = $ord['tot_taxes'] * floatval($tbkv['aliq']) / $ord['aliq'];
								$taxes_str .= "\r\n".$tbkv['name'].": ".$usecurrencyname.' '.VikBooking::numberFormat($tax_break_cost);
							}
						}
					}
				}
				if (isset($orders[($kord + 1)]) && $orders[($kord + 1)]['id'] == $ord['id']) {
					// total taxes will be printed only for the last room booked
					$taxes_str = '';
				}
				//
				//created by
				$created_by = '';
				if (!empty($ord['ujid'])) {
					$creator = new JUser($ord['ujid']);
					if (property_exists($creator, 'name')) {
						$created_by = $creator->name.' ('.$creator->username.')';
					}
				}
				if (empty($created_by) && !empty($ord['t_first_name'])) {
					$created_by = $ord['t_first_name'].' '.$ord['t_last_name'];
				}
				//
				$orderscsv[] = array(
					$ord['id'],
					date(str_replace("/", $datesep, $df), $ord['checkin']),
					date(str_replace("/", $datesep, $df), $ord['checkout']),
					$ord['days'],
					$ord['name'],
					$peoplestr,
					$custinfostr,
					$special_requests,
					$ord['adminnotes'],
					$created_by,
					$ord['custmail'],
					$ord['phone'],
					$options_str,
					$paystr,
					$ordnumbstr,
					$statusstr,
					$totalstring,
					$totalpaidstring,
					$taxes_str,
				);
			}
			header("Content-type: text/csv");
			header("Cache-Control: no-store, no-cache");
			header('Content-Disposition: attachment; filename="bookings_export_'.date('Y-m-d').'.csv"');
			$outstream = fopen("php://output", 'w');
			foreach ($orderscsv as $csvline) {
				fputcsv($outstream, $csvline);
			}
			fclose($outstream);
			exit;
		} else {
			VikError::raiseWarning('', JText::translate('VBCSVEXPNORECORDS'));
			$mainframe = JFactory::getApplication();
			$mainframe->redirect("index.php?option=com_vikbooking&task=csvexportprepare&checkindate=".$pcheckindate."&checkoutdate=".$pcheckoutdate."&status=".$pstatus."&tmpl=component");
		}
	}

	public function exportcustomerslaunch() {
		$cid = VikRequest::getVar('cid', array(0));
		$dbo = JFactory::getDBO();
		$pnotes = VikRequest::getInt('notes', '', 'request');
		$pscanimg = VikRequest::getInt('scanimg', '', 'request');
		$ppin = VikRequest::getInt('pin', '', 'request');
		$pcountry = VikRequest::getString('country', '', 'request');
		$pfromdate = VikRequest::getString('fromdate', '', 'request');
		$ptodate = VikRequest::getString('todate', '', 'request');
		$pdatefilt = VikRequest::getInt('datefilt', '', 'request');
		$clauses = array();
		if (count($cid) > 0 && !empty($cid[0])) {
			$clauses[] = "`c`.`id` IN (".implode(', ', $cid).")";
		}
		if (!empty($pcountry)) {
			$clauses[] = "`c`.`country`=".$dbo->quote($pcountry);
		}
		$datescol = '`bk`.`ts`';
		if ($pdatefilt > 0) {
			if ($pdatefilt == 1) {
				$datescol = '`bk`.`ts`';
			} elseif ($pdatefilt == 2) {
				$datescol = '`bk`.`checkin`';
			} elseif ($pdatefilt == 3) {
				$datescol = '`bk`.`checkout`';
			}
		}
		if (!empty($pfromdate)) {
			$from_ts = VikBooking::getDateTimestamp($pfromdate, 0, 0);
			$clauses[] = $datescol.">=".$from_ts;
		}
		if (!empty($ptodate)) {
			$to_ts = VikBooking::getDateTimestamp($ptodate, 23, 59);
			$clauses[] = $datescol."<=".$to_ts;
		}
		//this query below is safe with the error #1055 when sql_mode=only_full_group_by
		$q = "SELECT `c`.`id`,`c`.`first_name`,`c`.`last_name`,`c`.`email`,`c`.`phone`,`c`.`country`,`c`.`cfields`,`c`.`pin`,`c`.`ujid`,`c`.`address`,`c`.`city`,`c`.`zip`,`c`.`doctype`,`c`.`docnum`,`c`.`docimg`,`c`.`notes`,`c`.`ischannel`,`c`.`chdata`,`c`.`company`,`c`.`vat`,`c`.`gender`,`c`.`bdate`,`c`.`pbirth`,".
			"(SELECT COUNT(*) FROM `#__vikbooking_customers_orders` AS `co` WHERE `co`.`idcustomer`=`c`.`id`) AS `tot_bookings`,".
			"`cy`.`country_3_code`,`cy`.`country_name` ".
			"FROM `#__vikbooking_customers` AS `c` LEFT JOIN `#__vikbooking_countries` `cy` ON `cy`.`country_3_code`=`c`.`country` ".
			"LEFT JOIN `#__vikbooking_customers_orders` `co` ON `co`.`idcustomer`=`c`.`id` ".
			"LEFT JOIN `#__vikbooking_orders` `bk` ON `bk`.`id`=`co`.`idorder`".
			(count($clauses) > 0 ? " WHERE ".implode(' AND ', $clauses) : "")." 
			GROUP BY `c`.`id`,`c`.`first_name`,`c`.`last_name`,`c`.`email`,`c`.`phone`,`c`.`country`,`c`.`cfields`,`c`.`pin`,`c`.`ujid`,`c`.`address`,`c`.`city`,`c`.`zip`,`c`.`doctype`,`c`.`docnum`,`c`.`docimg`,`c`.`notes`,`c`.`ischannel`,`c`.`chdata`,`c`.`company`,`c`.`vat`,`c`.`gender`,`c`.`bdate`,`c`.`pbirth`,`cy`.`country_3_code`,`cy`.`country_name` ".
			"ORDER BY `c`.`last_name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!($dbo->getNumRows() > 0)) {
			VikError::raiseWarning('', JText::translate('VBONORECORDSCSVCUSTOMERS'));
			$mainframe = JFactory::getApplication();
			$mainframe->redirect("index.php?option=com_vikbooking&task=customers");
			exit;
		}
		$customers = $dbo->loadAssocList();
		$csvlines = array();
		$csvheadline = array('ID', JText::translate('VBCUSTOMERLASTNAME'), JText::translate('VBCUSTOMERFIRSTNAME'), JText::translate('VBCUSTOMEREMAIL'), JText::translate('VBCUSTOMERPHONE'), JText::translate('VBCUSTOMERADDRESS'), JText::translate('VBCUSTOMERCITY'), JText::translate('VBCUSTOMERZIP'), JText::translate('VBCUSTOMERCOUNTRY'), JText::translate('VBCUSTOMERTOTBOOKINGS'));
		if ($ppin > 0) {
			$csvheadline[] = JText::translate('VBCUSTOMERPIN');
		}
		if ($pscanimg > 0) {
			$csvheadline[] = JText::translate('VBCUSTOMERDOCTYPE');
			$csvheadline[] = JText::translate('VBCUSTOMERDOCNUM');
			$csvheadline[] = JText::translate('VBCUSTOMERDOCIMG');
		}
		if ($pnotes > 0) {
			$csvheadline[] = JText::translate('VBCUSTOMERNOTES');
		}
		$csvlines[] = $csvheadline;
		foreach ($customers as $customer) {
			$csvcustomerline = array($customer['id'], $customer['last_name'], $customer['first_name'], $customer['email'], $customer['phone'], $customer['address'], $customer['city'], $customer['zip'], $customer['country_name'], $customer['tot_bookings']);
			if ($ppin > 0) {
				$csvcustomerline[] = $customer['pin'];
			}
			if ($pscanimg > 0) {
				$csvcustomerline[] = $customer['doctype'];
				$csvcustomerline[] = $customer['docnum'];
				$csvcustomerline[] = (!empty($customer['docimg']) ? VBO_ADMIN_URI.'resources/idscans/'.$customer['docimg'] : '');
			}
			if ($pnotes > 0) {
				$csvcustomerline[] = $customer['notes'];
			}	
			$csvlines[] = $csvcustomerline;
		}
		header("Content-type: text/csv");
		header("Cache-Control: no-store, no-cache");
		header('Content-Disposition: attachment; filename="customers_export_'.(!empty($pcountry) ? strtolower($pcountry).'_' : '').date('Y-m-d').'.csv"');
		$outstream = fopen("php://output", 'w');
		foreach ($csvlines as $csvline) {
			fputcsv($outstream, $csvline);
		}
		fclose($outstream);
		exit;
	}

	public function renewsession() {
		/*
		 * @wponly
		 * We just destroy the session
		 */
		JSessionHandler::destroy();
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=config");
	}

	public function trackings() {
		VikBookingHelper::printHeader("trackings");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'trackings'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function trkconfig() {
		VikBookingHelper::printHeader("trackings");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'trkconfig'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function savetrkconfigstay() {
		$this->do_savetrkconfig(true);
	}

	public function savetrkconfig() {
		$this->do_savetrkconfig();
	}

	private function do_savetrkconfig($stay = false) {
		$dbo = JFactory::getDBO();
		$trkenabled = VikRequest::getInt('trkenabled', 0, 'request');
		$trkenabled = $trkenabled == 1 ? 1 : 0;
		$trkcookierfrdur = VikRequest::getFloat('trkcookierfrdur', 1, 'request');
		$trkcookierfrdur = $trkcookierfrdur < 0.1 ? 1 : $trkcookierfrdur;
		$trkcampname = VikRequest::getVar('trkcampname', array());
		$trkcampkey = VikRequest::getVar('trkcampkey', array());
		$trkcampval = VikRequest::getVar('trkcampval', array());
		$trkcampaigns = array();
		foreach ($trkcampname as $k => $v) {
			if (empty($trkcampkey[$k])) {
				continue;
			}
			$trkcampkey[$k] = str_replace(' ', '', trim($trkcampkey[$k]));
			$name = !empty($v) ? $v : date('Y-m-d').' '.(count($trkcampaigns) + 1);
			$trkcampaigns[$trkcampkey[$k]] = array(
				'key' => $trkcampkey[$k],
				'value' => $trkcampval[$k],
				'name' => $name,
			);
		}

		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($trkenabled)." WHERE `param`='trkenabled';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($trkcookierfrdur)." WHERE `param`='trkcookierfrdur';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote(json_encode($trkcampaigns))." WHERE `param`='trkcampaigns';";
		$dbo->setQuery($q);
		$dbo->execute();
		
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=".($stay ? 'trkconfig' : 'trackings'));
	}

	public function modtracking() {
		$dbo = JFactory::getDbo();
		$cid = VikRequest::getVar('cid', array());
		foreach ($cid as $id) {
			if (!empty($id)) {
				$q = "SELECT `id`,`published` FROM `#__vikbooking_trackings` WHERE `id`=".(int)$id.";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows()) {
					$data = $dbo->loadAssoc();
					$q = "UPDATE `#__vikbooking_trackings` SET `published`=".($data['published'] ? '0' : '1')." WHERE `id`=".(int)$data['id'].";";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=trackings");
	}

	public function removetrackings() {
		$ids = VikRequest::getVar('cid', array());
		if (count($ids)) {
			$dbo = JFactory::getDbo();
			foreach ($ids as $d){
				$q = "DELETE FROM `#__vikbooking_trackings` WHERE `id`=".(int)$d.";";
				$dbo->setQuery($q);
				$dbo->execute();
				$q = "DELETE FROM `#__vikbooking_tracking_infos` WHERE `idtracking`=".(int)$d.";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=trackings");
	}

	/**
	 * Invokes the Tracker class to obtain
	 * geo information about the IP addresses.
	 * This task is called via ajax.
	 *
	 * @since 	1.11
	 */
	public function getgeoinfo() {
		$ips = VikRequest::getVar('ips', array());
		if (!count($ips)) {
			echo 'e4j.error.empty IPs';
			exit;
		}

		// require the Tracker class without instantiating the object
		VikBooking::getTracker(true);
		$geo_info = VikBookingTracker::getIpGeoInfo($ips);

		if ($geo_info === false) {
			echo 'e4j.error.Tracker error, could not get geo info from IPs';
			exit;
		}

		// update db values and compose response
		$dbo = JFactory::getDbo();
		$resp = array();
		foreach ($geo_info as $id => $geo) {
			if (is_null($geo) || $geo === false) {
				continue;
			}
			// compose geo info string
			$geovals = array();
			if (!empty($geo['city'])) {
				array_push($geovals, $geo['city']);
			}
			if (!empty($geo['region'])) {
				array_push($geovals, $geo['region']);
			}
			$threecode = '';
			$cname = '';
			if (!empty($geo['country'])) {
				// returned country is a 2-char code, get the 3-char country code
				$q = "SELECT `country_3_code`,`country_name` FROM `#__vikbooking_countries` WHERE `country_2_code`=".$dbo->quote($geo['country']).";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows()) {
					$cinfo = $dbo->loadAssoc();
					$threecode = $cinfo['country_3_code'];
					$cname = $cinfo['country_name'];
				}
				array_push($geovals, (empty($cname) ? $geo['country'] : $cname));
			}

			// full geo information string
			$geoinfostr = implode(', ', $geovals);

			// push data to the response pool
			$resp[$id] = array();
			$resp[$id]['geo'] = $geoinfostr;
			if (!empty($cname)) {
				$resp[$id]['country'] = $cname;
			}
			if (!empty($threecode)) {
				$resp[$id]['country3'] = $threecode;
			}

			// update main tracking record
			$q = "UPDATE `#__vikbooking_trackings` SET `geo`=".$dbo->quote($geoinfostr).(!empty($threecode) ? ', `country`='.$dbo->quote($threecode) : '')." WHERE `id`=".(int)$id.";";
			$dbo->setQuery($q);
			$dbo->execute();
		}

		// output the JSON response
		echo json_encode($resp);
		exit;
	}

	/**
	 * Counts the orphan dates for all published rooms
	 * depending on their restrictions and booked dates.
	 * By default, the task takes up to 3 months ahead.
	 * It is possible to filter the request by rooms and months.
	 * This task should be called via ajax.
	 *
	 * @since 	1.11
	 */
	public function orphanscount()
	{
		$dbo = JFactory::getDbo();
		$orphans = array();

		$nowdf = VikBooking::getDateFormat();
		if ($nowdf == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($nowdf == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}

		// global min los
		$glob_minlos = VikBooking::getDefaultNightsCalendar();
		$glob_minlos = $glob_minlos < 1 ? 1 : $glob_minlos;
		
		// rooms and dates
		$roomids = VikRequest::getVar('roomids', array(), 'request', 'int');
		$months = VikRequest::getInt('months', 3, 'request');
		$from = VikRequest::getString('from', '', 'request');
		$today = strtotime(date('Y').'-'.date('m').'-'.date('d'));
		if (!empty($from)) {
			$fromts = VikBooking::getDateTimestamp($from, 0, 0);
			if (!empty($fromts)) {
				// custom starting date
				$today = $fromts;
			}
		}
		$until = strtotime("+{$months} months", $today);

		// load all rooms
		$rooms = array();
		$q = "SELECT `id`,`name`,`units` FROM `#__vikbooking_rooms` WHERE `avail`=1".(count($roomids) ? ' AND `id` IN ('.implode(', ', $roomids).')' : '').";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$allrooms = $dbo->loadAssocList();
			foreach ($allrooms as $r) {
				$rooms[$r['id']] = $r;
			}
		}
		if (!count($rooms)) {
			// no rooms found, exit
			echo json_encode($orphans);
			exit;
		}

		// load availabilities
		$q = "SELECT `b`.*,`ob`.`idorder` FROM `#__vikbooking_busy` AS `b`,`#__vikbooking_ordersbusy` AS `ob` WHERE `b`.`idroom` IN (".implode(', ', array_keys($rooms)).") AND `b`.`id`=`ob`.`idbusy` AND (`b`.`checkin`>=".$today." OR `b`.`checkout`>=".$today.") AND (`b`.`checkin`<=".$until." OR `b`.`checkout`<=".$today.");";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			// no booked dates found, exit
			echo json_encode($orphans);
			exit;
		}
		$busy = $dbo->loadAssocList();

		// sort booked dates by room id
		$rooms_busy = array();
		foreach ($busy as $b) {
			if (!isset($rooms_busy[$b['idroom']])) {
				$rooms_busy[$b['idroom']] = array();
			}
			array_push($rooms_busy[$b['idroom']], $b);
		}

		// load restrictions
		$rooms_restr = array();
		foreach ($rooms as $rid => $r) {
			$restrictions = VikBooking::loadRestrictions(true, array($rid));
			if (count($restrictions)) {
				$rooms_restr[$rid] = $restrictions;
			}
		}
		if (!count($rooms_restr) && $glob_minlos < 2) {
			// no restrictions found and minlos=1, exit
			echo json_encode($orphans);
			exit;
		}

		// count availability and minlos per day
		$rooms_data = array();
		foreach ($rooms as $rid => $r) {
			$rooms_data[$rid] = array(
				'avail' => array(),
				'restr' => array()
			);
			$nowts = getdate($today);
			while ($nowts[0] <= $until) {
				$dateind = date('Y-m-d', $nowts[0]);
				
				// remaining availability
				if (!isset($rooms_busy[$rid])) {
					// no bookings for this room, set full availability for this day
					$rooms_data[$rid]['avail'][] = array(
						'dt' => $dateind, 
						'units' => $r['units']
					);
				} else {
					// check remaining availability for this day
					$totfound = 0;
					foreach ($rooms_busy[$rid] as $b) {
						$tmpone = getdate($b['checkin']);
						$rit = ($tmpone['mon'] < 10 ? "0".$tmpone['mon'] : $tmpone['mon'])."/".($tmpone['mday'] < 10 ? "0".$tmpone['mday'] : $tmpone['mday'])."/".$tmpone['year'];
						$ritts = strtotime($rit);
						$tmptwo = getdate($b['checkout']);
						$con = ($tmptwo['mon'] < 10 ? "0".$tmptwo['mon'] : $tmptwo['mon'])."/".($tmptwo['mday'] < 10 ? "0".$tmptwo['mday'] : $tmptwo['mday'])."/".$tmptwo['year'];
						$conts = strtotime($con);
						if ($nowts[0] >= $ritts && $nowts[0] < $conts) {
							$totfound++;
						}
					}
					$totfound = $totfound > $r['units'] ? $r['units'] : $totfound;
					$rooms_data[$rid]['avail'][] = array(
						'dt' => $dateind, 
						'units' => ($r['units'] - $totfound)
					);
				}

				// restrictions
				if (!isset($rooms_restr[$rid])) {
					// no restrictions for this room, set global minlos for this day
					$rooms_data[$rid]['restr'][] = array(
						'dt' => $dateind,
						'minlos' => $glob_minlos
					);
				} else {
					// get restriction for this day
					$today_tsin  = mktime(0, 0, 0, $nowts['mon'], $nowts['mday'], $nowts['year']);
					$today_tsout = mktime(0, 0, 0, $nowts['mon'], ($nowts['mday'] + 1), $nowts['year']);

					$restr 	= VikBooking::parseSeasonRestrictions($today_tsin, $today_tsout, 1, $rooms_restr[$rid]);
					$minlos = count($restr) ? $restr['minlos'] : $glob_minlos;

					$rooms_data[$rid]['restr'][] = array(
						'dt' => $dateind,
						'minlos' => $minlos
					);
				}

				// next loop
				$dayts = mktime(0, 0, 0, $nowts['mon'], ($nowts['mday'] + 1), $nowts['year']);
				$nowts = getdate($dayts);
			}
		}

		// week days and months labels
		$days_labels = array(
			JText::translate('VBSUNDAY'),
			JText::translate('VBMONDAY'),
			JText::translate('VBTUESDAY'),
			JText::translate('VBWEDNESDAY'),
			JText::translate('VBTHURSDAY'),
			JText::translate('VBFRIDAY'),
			JText::translate('VBSATURDAY')
		);
		$months_labels = array(
			JText::translate('VBMONTHONE'),
			JText::translate('VBMONTHTWO'),
			JText::translate('VBMONTHTHREE'),
			JText::translate('VBMONTHFOUR'),
			JText::translate('VBMONTHFIVE'),
			JText::translate('VBMONTHSIX'),
			JText::translate('VBMONTHSEVEN'),
			JText::translate('VBMONTHEIGHT'),
			JText::translate('VBMONTHNINE'),
			JText::translate('VBMONTHTEN'),
			JText::translate('VBMONTHELEVEN'),
			JText::translate('VBMONTHTWELVE')
		);

		// orphan dates calculation method
		$calc_method = VikBooking::orphansCalculation();

		// parse data and build orphans if any
		foreach ($rooms_data as $rid => $data) {
			foreach ($data['avail'] as $ind => $av) {
				if (!isset($data['restr'][$ind]) || $av['units'] < 1) {
					// continue, no restriction set or no availability for this day
					continue;
				}
				if ($data['restr'][$ind]['minlos'] < 2) {
					// continue, no min los > 1 set for this day
					continue;
				}
				// check if any night after today, until min los, is fully booked
				$hasorphans = false;
				$forward_count = 0;
				for ($i = 1; $i < $data['restr'][$ind]['minlos']; $i++) {
					if (!isset($data['avail'][($ind + $i)])) {
						// break loop, no info for this day after
						break;
					}
					if ($data['avail'][($ind + $i)]['units'] > 0) {
						// continue, availability found for tomorrow, we need a non available next-day
						continue;
					}
					// orphan found
					$hasorphans = true;
					$forward_count = $i;
					break;
				}

				/**
				 * Backward calculation method only if "prevnext".
				 * 
				 * @since 	1.3.0
				 */
				$backward_count = 0;
				for ($i = 1; $i <= $data['restr'][$ind]['minlos']; $i++) {
					if (!isset($data['avail'][($ind - $i)])) {
						// break loop, no info for this prev day
						break;
					}
					if ($data['avail'][($ind - $i)]['units'] > 0) {
						// increase free nights going backward
						$backward_count++;
					}
				}
				if ($calc_method == 'prevnext' && $hasorphans && $backward_count > 0 && ($backward_count >= $data['restr'][$ind]['minlos'] || ($backward_count + $forward_count) >= $data['restr'][$ind]['minlos'])) {
					// this should not be an orphan date because of enough free days back, or enough free days in between
					$hasorphans = false;
				}
				//
				
				if ($hasorphans) {
					// we pass the name of the room, the list of raw dates (Y-m-d), the list of readable dates, and the fist date with the VBO format
					if (!isset($orphans[$rid])) {
						$orphans[$rid] = array(
							'name'   => $rooms[$rid]['name'],
							'dates'  => array(),
							'rdates' => array(),
							'linkd'  => date($df, strtotime($av['dt']))
						);
					}
					array_push($orphans[$rid]['dates'], $av['dt']);
					// build the value for the readable date
					$dtinfo = getdate(strtotime($av['dt']));
					$rdate = $days_labels[$dtinfo['wday']] . ', ' . $months_labels[($dtinfo['mon'] - 1)] . ' ' . $dtinfo['mday'] . ' ' . $dtinfo['year'];
					array_push($orphans[$rid]['rdates'], $rdate);
				}
			}
		}

		// output response
		echo json_encode($orphans);
		exit;
	}

	public function tableaux() {
		VikBookingHelper::printHeader("tableaux");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'tableaux'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function operators() {
		VikBookingHelper::printHeader("operators");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'operators'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function newoperator() {
		VikBookingHelper::printHeader("operators");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageoperator'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function editoperator() {
		VikBookingHelper::printHeader("operators");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageoperator'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function updateoperator() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$this->do_updateoperator();
	}

	public function updateoperatorstay() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$this->do_updateoperator(true);
	}

	private function do_updateoperator($stay = false) {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$pfirst_name = VikRequest::getString('first_name', '', 'request');
		$plast_name = VikRequest::getString('last_name', '', 'request');
		$pemail = VikRequest::getString('email', '', 'request');
		$pphone = VikRequest::getString('phone', '', 'request');
		$pcode = VikRequest::getString('code', '', 'request');
		$pujid = VikRequest::getInt('ujid', '', 'request');
		$pwhere = VikRequest::getInt('where', '', 'request');
		if (!empty($pfirst_name) && !empty($pemail) && (!empty($pcode) || !empty($pujid))) {
			$q = "SELECT * FROM `#__vikbooking_operators` WHERE `id`=".(int)$pwhere." LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$customer = $dbo->loadAssoc();
			} else {
				$mainframe->redirect("index.php?option=com_vikbooking&task=operators");
				exit;
			}
			$q = "SELECT * FROM `#__vikbooking_operators` WHERE (`email`=".$dbo->quote($pemail)." OR ".(!empty($pcode) ? "`code`=".$dbo->quote($pcode) : "`ujid`=".$dbo->quote($pujid)).") AND `id`!=".(int)$pwhere." LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 0) {
				// update fingerprint for the operator
				$fingpt = md5($pwhere.$pemail);
				//
				$q = "UPDATE `#__vikbooking_operators` SET `first_name`=".$dbo->quote($pfirst_name).",`last_name`=".$dbo->quote($plast_name).",`email`=".$dbo->quote($pemail).",`phone`=".$dbo->quote($pphone).",`code`=".$dbo->quote($pcode).",`ujid`=".$dbo->quote($pujid).",`fingpt`=".$dbo->quote($fingpt)." WHERE `id`=".(int)$pwhere.";";
				$dbo->setQuery($q);
				$dbo->execute();
				$mainframe->enqueueMessage(JText::translate('VBOPERATORSAVED'));
			} else {
				//email already exists
				$ex_operator = $dbo->loadAssoc();
				VikError::raiseWarning('', JText::translate('VBERROPERATOREXISTS').'<br/><a href="index.php?option=com_vikbooking&task=editoperator&cid[]='.$ex_operator['id'].'" target="_blank">'.$ex_operator['first_name'].' '.$ex_operator['last_name'].'</a>');
				$mainframe->redirect("index.php?option=com_vikbooking&task=editoperator&cid[]=".$pwhere);
				exit;
			}
		} else {
			VikError::raiseWarning('', JText::translate('VBERROPERATORDATA'));
		}
		if ($stay) {
			$mainframe->redirect("index.php?option=com_vikbooking&task=editoperator&cid[]=".$pwhere);
		} else {
			$mainframe->redirect("index.php?option=com_vikbooking&task=operators");
		}
	}

	public function saveoperator() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();
		$pfirst_name = VikRequest::getString('first_name', '', 'request');
		$plast_name = VikRequest::getString('last_name', '', 'request');
		$pemail = VikRequest::getString('email', '', 'request');
		$pphone = VikRequest::getString('phone', '', 'request');
		$pcode = VikRequest::getString('code', '', 'request');
		$pujid = VikRequest::getInt('ujid', '', 'request');
		if (!empty($pfirst_name) && !empty($pemail) && (!empty($pcode) || !empty($pujid))) {
			$q = "SELECT * FROM `#__vikbooking_operators` WHERE `email`=".$dbo->quote($pemail)." OR ".(!empty($pcode) ? "`code`=".$dbo->quote($pcode) : "`ujid`=".$dbo->quote($pujid))." LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 0) {
				$q = "INSERT INTO `#__vikbooking_operators` (`first_name`,`last_name`,`email`,`phone`,`code`,`ujid`) VALUES(".$dbo->quote($pfirst_name).", ".$dbo->quote($plast_name).", ".$dbo->quote($pemail).", ".$dbo->quote($pphone).", ".$dbo->quote($pcode).", ".$dbo->quote($pujid).");";
				$dbo->setQuery($q);
				$dbo->execute();
				$lid = $dbo->insertid();
				if (!empty($lid)) {
					$mainframe->enqueueMessage(JText::translate('VBOPERATORSAVED'));
					// generate fingerprint for the operator
					$q = "UPDATE `#__vikbooking_operators` SET `fingpt`=".$dbo->quote(md5($lid.$pemail))." WHERE `id`=".(int)$lid.";";
					$dbo->setQuery($q);
					$dbo->execute();
					//
				}
			} else {
				//email already exists
				$ex_operator = $dbo->loadAssoc();
				VikError::raiseWarning('', JText::translate('VBERROPERATOREXISTS').'<br/><a href="index.php?option=com_vikbooking&task=editoperator&cid[]='.$ex_operator['id'].'" target="_blank">'.$ex_operator['first_name'].' '.$ex_operator['last_name'].'</a>');
			}
		} else {
			VikError::raiseWarning('', JText::translate('VBERROPERATORDATA'));
		}
		$mainframe->redirect("index.php?option=com_vikbooking&task=operators");
	}

	public function removeoperators() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikbooking_operators` WHERE `id`=".(int)$d.";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=operators");
	}

	public function operatorperms() {
		$dbo = JFactory::getDbo();
		$permtype = VikRequest::getString('permtype', 'tableaux', 'request');
		$permschanged = 0;

		if ($permtype == 'tableaux') {
			$oper_id = VikRequest::getVar('oper_id', array());
			$oper_days = VikRequest::getVar('oper_days', array());
			$oper_rooms = VikRequest::getVar('oper_rooms', array());
			$oper_rm = VikRequest::getVar('oper_rm', array());
			if (count($oper_rm)) {
				// remove permissions first
				foreach ($oper_rm as $operatorid) {
					$q = "SELECT `id`,`perms` FROM `#__vikbooking_operators` WHERE `id`=".(int)$operatorid.";";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($dbo->getNumRows()) {
						$current = $dbo->loadAssoc();
						$perms = !empty($current['perms']) ? json_decode($current['perms'], true) : array();
						$perms = !is_array($perms) ? array() : $perms;
						foreach ($perms as $kp => $perm) {
							if (isset($perm['type']) && $perm['type'] == $permtype) {
								unset($perms[$kp]);
								break;
							}
						}
						// update permissions for this operator
						$q = "UPDATE `#__vikbooking_operators` SET `perms`=".$dbo->quote(json_encode($perms))." WHERE `id`=".$current['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
						$permschanged++;
					}
				}
			}
			foreach ($oper_id as $k => $v) {
				if (empty($v) || !isset($oper_days[$k])) {
					// missing data
					continue;
				}
				// get operator
				$q = "SELECT `id`,`perms` FROM `#__vikbooking_operators` WHERE `id`=".(int)$v.";";
				$dbo->setQuery($q);
				$dbo->execute();
				if (!$dbo->getNumRows()) {
					continue;
				}
				$current = $dbo->loadAssoc();
				$perms = !empty($current['perms']) ? json_decode($current['perms'], true) : array();
				$perms = !is_array($perms) ? array() : $perms;
				foreach ($perms as $kp => $perm) {
					if (isset($perm['type']) && $perm['type'] == $permtype) {
						unset($perms[$kp]);
						break;
					}
				}
				// push new permission
				array_push($perms, array(
					'type'  => $permtype,
					'perms' => array(
						'days'  => (int)$oper_days[$k],
						'rooms' => isset($oper_rooms[$k]) && is_array($oper_rooms[$k]) ? $oper_rooms[$k] : array()
					)
				));
				// update permissions for this operator
				$q = "UPDATE `#__vikbooking_operators` SET `perms`=".$dbo->quote(json_encode($perms))." WHERE `id`=".$current['id'].";";
				$dbo->setQuery($q);
				$dbo->execute();
				$permschanged++;
			}
		}

		$mainframe = JFactory::getApplication();
		if ($permschanged > 0) {
			$mainframe->enqueueMessage(JText::translate('VBOPERMSUPDOPEROK'));
		}
		$mainframe->redirect("index.php?option=com_vikbooking&task=".$permtype);
	}

	public function canceloperator() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=operators");
	}

	public function cancelcrons() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=crons");
	}

	public function cancelpackages() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=packages");
	}

	public function cancelcustomer() {
		$mainframe = JFactory::getApplication();
		$pgoto = VikRequest::getString('goto', '', 'request', VIKREQUEST_ALLOWRAW);
		if (!empty($pgoto)) {
			$mainframe->redirect(base64_decode($pgoto));
			exit;
		}
		$mainframe->redirect("index.php?option=com_vikbooking&task=customers");
	}

	public function cancelbusyvcm() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikchannelmanager&task=oversight");
	}

	public function cancelrestriction() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=restrictions");
	}

	public function cancelcoupon() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=coupons");
	}

	public function cancelcustomf() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=customf");
	}

	public function cancelpayment() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=payments");
	}

	public function cancelseason() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=seasons");
	}

	public function goconfig() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=config");
	}

	public function canceledorder() {
		$pgoto = VikRequest::getString('goto', 'orders', 'request');
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=" . $pgoto);
	}

	public function cancelbusy() {
		$pidorder = VikRequest::getString('idorder', '', 'request');
		$pgoto = VikRequest::getString('goto', '', 'request');
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=".$pidorder.($pgoto == 'overv' ? '&goto=overv' : ''));
	}

	public function canceloverv() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=overv");
	}

	public function cancelcalendar() {
		$pidroom = VikRequest::getString('idroom', '', 'request');
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=calendar&cid[]=".$pidroom);
	}

	public function canceloptionals() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=optionals");
	}

	public function cancel() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=rooms");
	}

	public function cancelcarat() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=carat");
	}

	public function cancelcat() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=categories");
	}

	public function cancelprice() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=prices");
	}

	public function canceliva() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=iva");
	}

	public function canceltrk() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=trackings");
	}

	public function canceldash() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking");
	}

	public function cancelinvoice() {
		$mainframe = JFactory::getApplication();
		$pgoto = VikRequest::getString('goto', '', 'request', VIKREQUEST_ALLOWRAW);
		if (!empty($pgoto)) {
			$mainframe->redirect(base64_decode($pgoto));
			exit;
		}
		$mainframe->redirect("index.php?option=com_vikbooking&task=invoices");
	}

	/**
	 * AJAX upload the customer documents.
	 *
	 * @return 	void
	 *
	 * @throws 	Exception
	 */
	public function upload_customer_document()
	{
		$input = JFactory::getApplication()->input;
		$dbo   = JFactory::getDbo();

		$customer_id = $input->getUint('customer', 0);

		$result = new stdClass;
		$result->status = 0;

		try
		{			
			$q = $dbo->getQuery(true)
				->select($dbo->qn(array(
					'id',
					'first_name',
					'last_name',
					'email',
					'docsfolder',
				)))
				->from($dbo->qn('#__vikbooking_customers'))
				->where($dbo->qn('id') . ' = ' . $customer_id);

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if (!$dbo->getNumRows())
			{
				throw new Exception(sprintf('Customer [%d] not found', $customer_id), 404);
			}

			$customer = $dbo->loadObject();

			// fetch documents folder path
			$dirpath = VBO_CUSTOMERS_PATH . DIRECTORY_SEPARATOR;

			// check if we have a valid directory
			if (empty($customer->docsfolder) || !is_dir($dirpath . $customer->docsfolder))
			{
				// randomize string
				$customer->seed = uniqid();

				// create blocks for hashed folder
				$parts = [
					$customer->first_name,
					$customer->last_name,
					md5(serialize($customer)),
				];

				// join fetched partss
				$customer->docsfolder = strtolower(implode('-', array_filter($parts)));

				if (strlen($customer->docsfolder) < 16)
				{
					throw new Exception('Possible security breach. Please specify the most details as possible.', 400);
				}

				jimport('joomla.filesystem.folder');

				// create a folder for this customer
				$created = JFolder::create($dirpath . $customer->docsfolder);

				if (!$created)
				{
					throw new Exception(sprintf('Unable to create the folder [%s]', $dirpath . $customer->docsfolder), 403);
				}

				unset($customer->seed);

				// update docs folder
				$dbo->updateObject('#__vikbooking_customers', $customer, 'id');
			}

			// get file from request
			$file = $input->files->get('file', array(), 'array');

			// try to upload the file
			$result = VikBooking::uploadFileFromRequest($file, $dirpath . $customer->docsfolder, "/(image\/.+)|(application\/(zip|rar|pdf|msword|vnd.*?))|(text\/(plain|markdown|csv))$/i");
			$result->status = 1;

			$result->size = JHtml::fetch('number.bytes', filesize($result->path), 'auto', 0);
			$result->url  = str_replace(DIRECTORY_SEPARATOR, '/', str_replace(VBO_CUSTOMERS_PATH . DIRECTORY_SEPARATOR, VBO_CUSTOMERS_URI, $result->path));
		}
		catch (Exception $e)
		{
			$result->error = $e->getMessage();
			$result->code  = $e->getCode();
		}

		echo json_encode($result);
		exit;
	}

	/**
	 * AJAX delete the customer documents.
	 *
	 * @return 	void
	 *
	 * @throws 	Exception
	 */
	public function delete_customer_document()
	{
		$input = JFactory::getApplication()->input;
		$dbo   = JFactory::getDbo();

		$customer_id = $input->getUint('customer', 0);

		$result = new stdClass;
		$result->status = 0;

		$q = $dbo->getQuery(true)
			->select($dbo->qn('docsfolder'))
			->from($dbo->qn('#__vikbooking_customers'))
			->where($dbo->qn('id') . ' = ' . $customer_id);

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			throw new Exception(sprintf('Customer [%d] not found', $customer_id), 404);
		}

		$folder = $dbo->loadResult();

		if (!$folder)
		{
			throw new Exception('The customer does not have any documents', 500);
		}

		$file = $input->getString('file');

		if (!$file)
		{
			throw new Exception('File to remove not specified', 400);
		}

		$path = implode(DIRECTORY_SEPARATOR, array(VBO_CUSTOMERS_PATH, $folder, $file));

		if (!is_file($path)) 
		{
			throw new Exception(sprintf('File [%s] not found', $path), 404);
		}

		jimport('joomla.filesystem.file');

		$removed = JFile::delete($path);

		echo json_encode(array('status' => (int) $removed));
		exit;
	}

	/**
	 * AJAX task to invoke a specific report and obtain information.
	 * 
	 * @since 	1.3.0
	 */
	public function get_report_data()
	{
		$report_name = VikRequest::getString('report_name', '', 'request');
		$current_fest = VikRequest::getString('current_fest', '', 'request');
		$current_fromdate = VikRequest::getString('current_fromdate', '', 'request');
		$current_todate = VikRequest::getString('current_todate', '', 'request');
		$step = VikRequest::getString('step', 'weekend', 'request');
		$direction = VikRequest::getString('direction', 'load', 'request');
		$period = VikRequest::getString('period', 'full', 'request');
		$krsort = VikRequest::getString('krsort', 'occupancy', 'request');
		$krorder = VikRequest::getString('krorder', 'DESC', 'request');
		$chart_datatype = VikRequest::getVar('chart_datatype', array(), 'request');
		$chart_meta_data = VikRequest::getString('chart_meta_data', '', 'request', VIKREQUEST_ALLOWRAW);
		$chart_meta_data = !empty($chart_meta_data) ? json_decode($chart_meta_data, true) : array();
		// idroom can be an array of IDs or just one ID as int/string
		$idroom = VikRequest::getVar('idroom', null, 'request');
		//
		
		if (empty($report_name) || empty($current_fromdate) || empty($current_todate)) {
			throw new Exception("Missing request data", 400);
		}

		// get requested report instance
		$report = VikBooking::getReportInstance($report_name);
		if (!$report) {
			throw new Exception("Report not found", 404);
		}

		// chart data
		if (empty($chart_datatype)) {
			$chart_datatype = array(
				'type' => 'doughnut',
				'depth' => 1,
				'keys' => array($krsort),
			);
		}

		// website date format
		$df = $report->getDateFormat();

		// prepare request params for the report
		$rparams = array(
			'fromdate' => $current_fromdate,
			'todate'   => $current_todate,
			'period'   => $period,
			'krsort'   => $krsort,
			'krorder'  => $krorder,
			'idroom'   => $idroom,
		);

		// starting dates info and timestamps
		$from_ts = VikBooking::getDateTimestamp($current_fromdate, 0, 0, 0);
		$to_ts = VikBooking::getDateTimestamp($current_todate, 23, 59, 59);
		$from_info = getdate($from_ts);
		$to_info = getdate($to_ts);

		// the name of the period requested and whether it's a fest
		$period_name = '';
		$is_fest = null;

		if ($direction == 'prev' || $direction == 'next') {
			// calculate prev or next dates
			if ($step == 'weekend') {
				$period_name = JText::translate('VBOWEEKND');
				if ($direction == 'next') {
					// next weekend from current end date
					$next_ts = strtotime("next friday", $to_ts);
				} else {
					// prev weekend from current start date
					$next_ts = strtotime("previous friday", $from_ts);
				}
				$next_info = getdate($next_ts);
				$new_from_ts = $next_ts;
				$new_to_ts = mktime(23, 59, 59, $next_info['mon'], ($next_info['mday'] + 1), $next_info['year']);
				$rparams['fromdate'] = date($df, $new_from_ts);
				$rparams['todate'] = date($df, $new_to_ts);
			} elseif ($step == 'week') {
				$period_name = JText::translate('VBOWEEK');
				if ($direction == 'next') {
					// start next week from the current end date
					$new_from_ts = $to_ts;
					$new_to_ts = mktime(23, 59, 59, $to_info['mon'], ($to_info['mday'] + 7), $to_info['year']);
					$rparams['fromdate'] = $rparams['todate'];
					$rparams['todate'] = date($df, $new_to_ts);
				} else {
					// end prev week from the current from date
					$new_from_ts = mktime(0, 0, 0, $from_info['mon'], ($from_info['mday'] - 7), $from_info['year']);
					$new_to_ts = $from_ts;
					$rparams['todate'] = $rparams['fromdate'];
					$rparams['fromdate'] = date($df, $new_from_ts);
				}
			} else {
				// month
				$period_name = JText::translate('VBPVIEWRESTRICTIONSTWO');
				if ($direction == 'next') {
					// next month from the current from date
					$nextmonts = mktime(0, 0, 0, ($from_info['mon'] + 1), 1, $from_info['year']);
					$new_from_ts = $nextmonts;
					$new_to_ts = mktime(23, 59, 59, ($from_info['mon'] + 1), date('t', $nextmonts), $from_info['year']);
					$rparams['fromdate'] = date($df, $new_from_ts);
					$rparams['todate'] = date($df, $new_to_ts);
				} else {
					// prev month from the current from date
					$nextmonts = mktime(0, 0, 0, ($from_info['mon'] - 1), 1, $from_info['year']);
					$new_from_ts = $nextmonts;
					$new_to_ts = mktime(23, 59, 59, ($from_info['mon'] - 1), date('t', $nextmonts), $from_info['year']);
					$rparams['fromdate'] = date($df, $new_from_ts);
					$rparams['todate'] = date($df, $new_to_ts);
				}
			}

			// get the next festivities
			$fests = VikBooking::getFestivitiesInstance();
			$next_fests = $fests->loadFestDates();
			if (count($next_fests)) {
				// check whether a festivity should be displayed rather than the calculated period of dates
				foreach ($next_fests as $fest) {
					$fest_found = false;
					if ($direction == 'next' && $fest['festinfo'][0]->from_ts > $from_ts && $fest['festinfo'][0]->from_ts <= $new_to_ts) {
						$fest_found = true;
					} elseif ($direction == 'prev' && $fest['festinfo'][0]->from_ts < $to_ts && $fest['festinfo'][0]->from_ts >= $new_from_ts) {
						$fest_found = true;
					}
					if ($fest_found && (string)$fest['festinfo'][0]->next_ts != $current_fest) {
						// festivity found before next calculated period
						$is_fest = $fest['festinfo'][0]->next_ts;
						$period_name = $fest['festinfo'][0]->trans_name;
						$new_from_ts = $fest['festinfo'][0]->from_ts;
						$new_to_ts = $fest['festinfo'][0]->to_ts;
						$rparams['fromdate'] = date($df, $new_from_ts);
						$rparams['todate'] = date($df, $new_to_ts);
						break;
					}
				}
			}
		} else {
			// load requested dates by skipping the festivities
			$new_from_ts = $from_ts;
			$new_to_ts = $to_ts;
		}

		// invoke report
		$report->injectParams($rparams);
		$report_values = $report->getReportValues(1);
		$report_cols = $report->getColumnsValues();
		$report_chart = null;
		$report_chart_metas = array();
		$chart_meta_data = array(
			'keys' => array(
				'occupancy',
				'tot_bookings',
				'nights_booked',
			),
		);
		$error = null;

		if (!count($report_values)) {
			$error = strlen($report->getError()) ? $report->getError() : JText::translate('VBNOTRACKINGS');
		} else {
			// get doughnut Chart for the requested key
			$report_chart = $report->getChart($chart_datatype);

			// get Chart meta data
			$all_chart_metas = $report->getChartMetaData(null, $chart_meta_data);
			if (count($all_chart_metas)) {
				// merge all positions into one array
				foreach ($all_chart_metas as $pos_metas) {
					$report_chart_metas = array_merge($report_chart_metas, $pos_metas);
				}
			}

			if (empty($period_name)) {
				$period_name = $report->getProperty('chartTitle');
			}
		}

		// build response
		$response = new stdClass;
		$response->error 		 = $error;
		$response->fromdate 	 = $rparams['fromdate'];
		$response->todate 		 = $rparams['todate'];
		$response->in_days 		 = $report->countDaysTo($new_from_ts);
		$response->in_days_to	 = $report->countDaysTo($new_to_ts);
		$response->in_days_avg	 = $report->countAverageDays($response->in_days, $response->in_days_to);
		$response->period_name	 = $period_name;
		$response->period_date	 = count($report_values) && isset($report_values['day']) ? $report_values['day']['display_value'] : '';
		$response->is_fest 		 = $is_fest;
		$response->report_chart  = $report_chart;
		$response->report_cols 	 = $report_cols;
		$response->report_values = $report_values;
		$response->report_script = $report->getScript();
		$response->chart_labels  = $report->getProperty('chartJsLabels');
		$response->dataset_label = $report->getProperty('chartJsDataSetLabel');
		$response->chart_colors  = $report->getProperty('chartJsColors');
		$response->chart_data 	 = $report->getProperty('chartJsData');
		$response->report_chart_metas = $report_chart_metas;

		echo json_encode($response);
		exit;
	}

	/**
	 * Go to the previous booking.
	 * 
	 * @uses 	navigateToBooking()
	 * 
	 * @since 	1.3.0
	 */
	public function prev_booking()
	{
		$this->navigateToBooking('prev');
	}

	/**
	 * Go to the next booking.
	 * 
	 * @uses 	navigateToBooking()
	 * 
	 * @since 	1.3.0
	 */
	public function next_booking()
	{
		$this->navigateToBooking('next');
	}

	/**
	 * Given the current booking ID in the request, we navigate
	 * either to the next or to the previous reservation (if any).
	 * 
	 * @param 	string 	$direction 	either next or prev.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.3.0
	 */
	private function navigateToBooking($direction = 'next')
	{
		$bid = VikRequest::getInt('whereup', 0, 'request');
		if (empty($bid) || $bid < 1 || !in_array($direction, array('prev', 'next'))) {
			throw new Exception("Invalid request", 400);
		}

		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();

		$q = "SELECT `id` FROM `#__vikbooking_orders` WHERE `id`" . ($direction == 'next' ? '>' : '<') . "{$bid} ORDER BY `id` " . ($direction == 'next' ? 'ASC' : 'DESC');
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			VikError::raiseWarning('', JText::translate('VBPEDITBUSYONE'));
			$app->redirect("index.php?option=com_vikbooking&task=orders");
			exit;
		}

		$app->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=" . $dbo->loadResult());
		exit;
	}

	/**
	 * AJAX request: from a list of reservation IDs, we return the ones
	 * that have a review with the related review ID on VCM.
	 * 
	 * @since 	1.13
	 */
	public function bookings_have_reviews()
	{
		$dbo = JFactory::getDbo();
		$bids = VikRequest::getVar('bids', array(), 'request', 'array');
		$vcm_installed = is_file(VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikchannelmanager.php');
		$withreviews = array();

		if ($vcm_installed && is_array($bids) && count($bids)) {
			try {
				$q = "SELECT `id`, `idorder` FROM `#__vikchannelmanager_otareviews` WHERE `idorder` IN (" . implode(', ', $bids) . ");";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows()) {
					$reviews = $dbo->loadAssocList();
					foreach ($reviews as $r) {
						$withreviews[$r['idorder']] = $r['id'];
					}
				}
			} catch (Exception $e) {
				// do nothing, outdated version
			}
		}

		echo json_encode($withreviews);
		exit;
	}

	/**
	 * AJAX request for adding a new room-day note.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.13.5
	 */
	public function add_roomdaynote()
	{
		$dt 	 = VikRequest::getString('dt', '', 'request');
		$idroom  = VikRequest::getInt('idroom', 0, 'request');
		$subunit = VikRequest::getInt('subunit', 0, 'request');
		$type 	 = VikRequest::getString('type', '', 'request');
		$type 	 = empty($type) ? 'custom' : $type;
		$name 	 = VikRequest::getString('name', '', 'request');
		$descr 	 = VikRequest::getString('descr', '', 'request');
		$cdays   = VikRequest::getInt('cdays', 0, 'request');
		$cdays 	 = $cdays < 0 ? 0 : $cdays;
		$cdays 	 = $cdays > 365 ? 365 : $cdays;
		if (empty($idroom) || empty($dt) || !strtotime($dt)) {
			echo 'e4j.error.1';
			exit;
		}

		// reload end date
		$end_date = $dt;
		
		// build critical date object
		$new_note = array(
			'name'  => $name,
			'type'  => $type,
			'descr' => $descr,
		);

		// get object
		$notes  = VikBooking::getCriticalDatesInstance();

		// store the notes for all consecutive dates
		for ($i = 0; $i <= $cdays; $i++) {
			$store_dt = $dt;
			if ($i > 0) {
				$dt_info = getdate(strtotime($store_dt));
				$store_dt = date('Y-m-d', mktime(0, 0, 0, $dt_info['mon'], ($dt_info['mday'] + $i), $dt_info['year']));
				$end_date = $store_dt;
			}
			$result = $notes->storeDayNote($new_note, $store_dt, $idroom, $subunit);
			if (!$result) {
				echo 'e4j.error.2';
				exit;
			}
		}

		// reload all room day notes for this day for the AJAX response
		$all_notes = $notes->loadRoomDayNotes($dt, $end_date, $idroom, $subunit);

		if (!$all_notes || !count($all_notes)) {
			// no notes found even after storing it
			echo 'e4j.error.3';
			exit;
		}

		echo json_encode($all_notes);
		exit;
	}

	/**
	 * AJAX request for removing a room day note.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.13.5
	 */
	public function remove_roomdaynote()
	{
		$dt 	 = VikRequest::getString('dt', '', 'request');
		$idroom  = VikRequest::getInt('idroom', 0, 'request');
		$subunit = VikRequest::getInt('subunit', 0, 'request');
		$type 	 = VikRequest::getString('type', '', 'request');
		$type 	 = empty($type) ? 'custom' : $type;
		$ind 	 = VikRequest::getInt('ind', 0, 'request');
		if (empty($dt) || !strtotime($dt)) {
			echo 'e4j.error.1';
			exit;
		}

		$notes  = VikBooking::getCriticalDatesInstance();
		$result = $notes->deleteDayNote($ind, $dt, $idroom, $subunit, $type);
		if (!$result) {
			echo 'e4j.error.2';
			exit;
		}

		echo 'e4j.ok';
		exit;
	}

	/**
	 * AJAX request for storing an event for a booking.
	 * Firstly developed for the VCM Reporting API - Guest Misconduct,
	 * but it can be used for any other purpose.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.13.5
	 */
	public function store_booking_history_event()
	{
		$bid 	= VikRequest::getInt('bid', 0, 'request');
		$event  = VikRequest::getString('event', '', 'request');
		$descr  = VikRequest::getString('descr', '', 'request');

		if (empty($bid) || empty($event)) {
			throw new Exception("Missing required information", 500);
		}

		// Booking History
		VikBooking::getBookingHistoryInstance()->setBid($bid)->store($event, $descr);
		//

		echo 'e4j.ok';
		exit;
	}

	/**
	 * AJAX request for updating an option/extra service.
	 * Firstly developed for the VCM Vacation Rentals Essentials API - Damage Deposit,
	 * but it can be used for any other purpose.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.13.5
	 */
	public function update_option_params()
	{
		$optid 	 = VikRequest::getInt('optid', 0, 'request');
		$oparams = VikRequest::getVar('oparams', array(), 'request', 'array');

		if (empty($optid) || !is_array($oparams) || empty($oparams)) {
			throw new Exception("Missing required information", 500);
		}

		$dbo = JFactory::getDbo();
		$q = "SELECT `oparams` FROM `#__vikbooking_optionals` WHERE `id`=" . (int)$optid . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			throw new Exception("Option not found", 404);
		}
		$cur_params = $dbo->loadResult();
		$cur_params = !empty($cur_params) ? json_decode($cur_params, true) : array();
		$cur_params = !is_array($cur_params) ? array() : $cur_params;

		foreach ($oparams as $k => $v) {
			if (empty($k)) {
				continue;
			}
			$cur_params[$k] = $v;
		}

		$q = "UPDATE `#__vikbooking_optionals` SET `oparams`=" . $dbo->quote(json_encode($cur_params)) ." WHERE `id`=" . (int)$optid . ";";
		$dbo->setQuery($q);
		$dbo->execute();

		echo 'e4j.ok';
		exit;
	}

	/**
	 * Hidden task to clean up duplicate records in certain database tables
	 * due to a double execution of the installation queries.
	 * 
	 * @since 	November 4th 2020
	 */
	public function clean_duplicate_records() {
		$dbo = JFactory::getDbo();

		$tables_with_duplicates = array(
			'#__vikbooking_config' => array(
				'id_key' 	  => 'id',
				'compare_key' => 'param',
			),
			'#__vikbooking_countries' => array(
				'id_key' 	  => 'id',
				'compare_key' => 'country_3_code',
			),
			'#__vikbooking_custfields' => array(
				'id_key' 	  => 'id',
				'compare_key' => 'name',
			),
			'#__vikbooking_texts' => array(
				'id_key' 	  => 'id',
				'compare_key' => 'param',
			),
		);

		foreach ($tables_with_duplicates as $tblname => $data) {
			$doubles = array();
			$storage = array();
			$rmlist = array();
			$q = "SELECT * FROM `{$tblname}` ORDER BY `{$data['id_key']}` DESC;";
			$dbo->setQuery($q);
			$dbo->execute();
			if (!$dbo->getNumRows()) {
				echo "<p>No records found in table {$tblname}</p>";
				continue;
			}
			$rows = $dbo->loadAssocList();
			foreach ($rows as $row) {
				if (!isset($doubles[$row[$data['compare_key']]])) {
					$doubles[$row[$data['compare_key']]] = 0;
				}
				$doubles[$row[$data['compare_key']]]++;
				if (!isset($storage[$row[$data['compare_key']]])) {
					$storage[$row[$data['compare_key']]] = array();
				}
				array_push($storage[$row[$data['compare_key']]], $row[$data['id_key']]);
			}
			foreach ($doubles as $paramkey => $paramcount) {
				if ($paramcount < 2 || !isset($storage[$paramkey]) || count($storage[$paramkey]) < 2 || $paramcount != count($storage[$paramkey])) {
					continue;
				}
				$exceeding = $paramcount - 1;
				for ($x = 0; $x < $exceeding; $x++) {
					array_push($rmlist, $storage[$paramkey][$x]);
				}
			}
			echo "<p>Total records found in table {$tblname}: " . count($rows) . "</p>";
			echo '<p>Total records to remove: ' . count($rmlist) . '</p>';
			echo '<pre style="display: none;">'.print_r($rmlist, true).'</pre><br/>';
			if (count($rmlist)) {
				$q = "DELETE FROM `{$tblname}` WHERE `{$data['id_key']}` IN (" . implode(', ', $rmlist) . ");";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
	}

	/**
	 * Loads a specific admin widget ID and executes the requested method.
	 * Useful for loading a newly added widget, or to execute custom functions.
	 * 
	 * @throws 	Exception 	this is an AJAX endpoint.
	 * 
	 * @since 	1.4.0
	 */
	public function exec_admin_widget()
	{
		$widget_id  = VikRequest::getString('widget_id', '', 'request');
		$call 		= VikRequest::getString('call', '', 'request');
		
		if (empty($widget_id)) {
			throw new Exception("Empty Admin Widget ID", 500);
		}

		if (empty($call)) {
			throw new Exception("Empty Admin Widget Callback", 500);
		}

		// invoke admin widgets helper
		$widgets_helper = VikBooking::getAdminWidgetsInstance();
		$widget = $widgets_helper->getWidget($widget_id);
		
		if ($widget === false) {
			throw new Exception("Requested Admin Widget not found", 404);
		}

		if (!method_exists($widget, $call) || !is_callable(array($widget, $call))) {
			throw new Exception("Admin Widget Callback not found or forbidden", 403);
		}

		// invoke the widget's method within a buffer
		ob_start();
		$widget->{$call}();
		$widget_response = ob_get_contents();
		ob_end_clean();

		// prepare response object with a property equal to the called method
		$response = new stdClass;
		$response->{$call} = $widget_response;

		// echo the response and exit
		echo json_encode($response);
		exit;
	}

	/**
	 * Updates the map of admin widgets.
	 * 
	 * @throws 	Exception 	this is an AJAX endpoint.
	 * 
	 * @since 	1.4.0
	 */
	public function save_admin_widgets()
	{
		// make sure permissions are sufficient
		if (!JFactory::getUser()->authorise('core.vbo.global', 'com_vikbooking')) {
			throw new Exception("You are not authorized to modify the widgets.", 403);
		}

		$psections = VikRequest::getVar('sections', array(), 'request', 'array');
		if (!is_array($psections) || !count($psections)) {
			throw new Exception("No sections found in map", 500);
		}

		// request values are all converted to arrays, so restore the object styling
		$psections = json_decode(json_encode($psections));

		// update map
		$result = VikBooking::getAdminWidgetsInstance()->updateWidgetsMap($psections);

		$response = new stdClass;
		$response->status = (int)$result;

		// echo the response and exit
		echo json_encode($response);
		exit;
	}

	/**
	 * Restores the default admin widgets map.
	 * 
	 * @since 	1.4.0
	 */
	public function reset_admin_widgets()
	{
		// reset map and redirect to dashboard
		VikBooking::getAdminWidgetsInstance()->restoreDefaultWidgetsMap();

		JFactory::getApplication()->redirect('index.php?option=com_vikbooking');
		exit;
	}

	/**
	 * Updates the welcome message status for the widget's customizer via AJAX.
	 * 
	 * @since 	1.4.0
	 */
	public function admin_widgets_welcome()
	{
		$hide_welcome = VikRequest::getInt('hide_welcome', 0, 'request');
		// update configuration value
		VikBooking::getAdminWidgetsInstance()->updateWelcome($hide_welcome);

		$response = new stdClass;
		$response->status = $hide_welcome;

		// echo the response and exit
		echo json_encode($response);
		exit;
	}

	public function newcondtext()
	{
		VikBookingHelper::printHeader("11");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecondtext'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function editcondtext()
	{
		VikBookingHelper::printHeader("11");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecondtext'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	public function cancelcondtext()
	{
		JFactory::getApplication()->redirect('index.php?option=com_vikbooking&task=config&tab=7');
	}

	public function createcondtext()
	{
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$this->_doCreateCondText();
	}

	public function createcondtextstay()
	{
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$this->_doCreateCondText(true);
	}

	private function _doCreateCondText($stay = false)
	{
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();
		$rules_helper = VikBooking::getConditionalRulesInstance();
		$rules_list = $rules_helper->composeRulesParamsFromRequest();

		$condtextname = VikRequest::getString('condtextname', '', 'request');
		$condtexttkn = VikRequest::getString('condtexttkn', '', 'request');
		$msg = VikRequest::getString('msg', '', 'request', VIKREQUEST_ALLOWRAW);
		$debug = VikRequest::getInt('debug', 0, 'request');
		if (empty($condtextname)) {
			$condtextname = date('Y-m-dHis');
			$condtexttkn = '{condition: ' . date('YmdHis') . '}';
		}

		$existing_tokens = $rules_helper->getSpecialTags();
		if (count($existing_tokens) && isset($existing_tokens[$condtexttkn])) {
			VikError::raiseWarning('', 'Another conditional text with the same special tag already exists');
			$app->redirect('index.php?option=com_vikbooking&task=newcondtext');
			exit;
		}

		$data = new stdClass;
		$data->name = $condtextname;
		$data->token = $condtexttkn;
		$data->rules = json_encode($rules_list);
		$data->msg = $msg;
		$data->lastupd = JDate::getInstance()->toSql();
		$data->debug = $debug;

		$dbo->insertObject('#__vikbooking_condtexts', $data, 'id');

		if (isset($data->id)) {
			$app->enqueueMessage(JText::translate('VBSEASONUPDATED'));
		}

		if (!$stay || !isset($data->id)) {
			$this->cancelcondtext();
			exit;
		}

		$app->redirect('index.php?option=com_vikbooking&task=editcondtext&cid[]=' . $data->id);
	}

	public function updatecondtext()
	{
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$this->_doUpdateCondText();
	}

	public function updatecondtextstay()
	{
		if (!JSession::checkToken()) {
			throw new Exception(JText::translate('JINVALID_TOKEN'), 403);
		}
		$this->_doUpdateCondText(true);
	}

	private function _doUpdateCondText($stay = false)
	{
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();
		$rules_helper = VikBooking::getConditionalRulesInstance();
		$rules_list = $rules_helper->composeRulesParamsFromRequest();

		$pwhere = VikRequest::getInt('where', '', 'request');
		$condtextname = VikRequest::getString('condtextname', '', 'request');
		$condtexttkn = VikRequest::getString('condtexttkn', '', 'request');
		$msg = VikRequest::getString('msg', '', 'request', VIKREQUEST_ALLOWRAW);
		$debug = VikRequest::getInt('debug', 0, 'request');
		if (empty($condtextname)) {
			$condtextname = date('Y-m-dHis');
			$condtexttkn = '{condition: ' . date('YmdHis') . '}';
		}

		$existing_tokens = $rules_helper->getSpecialTags();
		if (count($existing_tokens) && isset($existing_tokens[$condtexttkn]) && ($existing_tokens[$condtexttkn]['id'] != $pwhere)) {
			VikError::raiseWarning('', 'Another conditional text with the same special tag already exists (' . $existing_tokens[$condtexttkn]['name'] . ')');
			$app->redirect('index.php?option=com_vikbooking&task=editcondtext&cid[]=' . $pwhere);
			exit;
		}

		$data = new stdClass;
		$data->id = $pwhere;
		$data->name = $condtextname;
		$data->token = $condtexttkn;
		$data->rules = json_encode($rules_list);
		$data->msg = $msg;
		$data->lastupd = JDate::getInstance()->toSql();
		$data->debug = $debug;

		$dbo->updateObject('#__vikbooking_condtexts', $data, 'id');

		$app->enqueueMessage(JText::translate('VBSEASONUPDATED'));

		if (!$stay) {
			$this->cancelcondtext();
			exit;
		}

		$app->redirect('index.php?option=com_vikbooking&task=editcondtext&cid[]=' . $data->id);
	}

	public function removecondtext()
	{
		$dbo = JFactory::getDbo();
		$ids = VikRequest::getVar('cid', array());
		if (count($ids)) {
			foreach ($ids as $d){
				$q = "DELETE FROM `#__vikbooking_condtexts` WHERE `id`=".(int)$d.";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$this->cancelcondtext();
	}

	/**
	 * AJAX endpoint to update one template file with the given tag or styles.
	 * A JSON response will be echoed by exiting the process.
	 */
	public function condtext_update_tmpl()
	{
		VikBooking::getConditionalRulesInstance(true);

		$tagaction = VikRequest::getString('tagaction', '', 'request');
		$tag = VikRequest::getString('tag', '', 'request');
		$file = VikRequest::getString('file', '', 'request', VIKREQUEST_ALLOWRAW);
		$newcontent = VikRequest::getString('newcontent', '', 'request', VIKREQUEST_ALLOWRAW);
		$custom_classes = VikRequest::getVar('custom_classes', array(), 'request', 'array');

		$allowed_actions = array(
			'add',
			'remove',
			'styles',
			'restore',
		);

		if (empty($tagaction) || empty($file) || !in_array($tagaction, $allowed_actions)) {
			throw new Exception("Invalid request submitted", 500);
		}

		if (in_array($tagaction, array('add', 'remove')) && empty($tag)) {
			throw new Exception("Invalid request submitted - missing tag", 500);
		}

		if (in_array($tagaction, array('add', 'styles')) && empty($newcontent)) {
			throw new Exception("Invalid request submitted - missing new HTML content", 500);
		}

		if ($tagaction == 'styles' && (!is_array($custom_classes) || !count($custom_classes))) {
			throw new Exception("No custom CSS classes to parse", 500);
		}

		if ($tagaction == 'restore') {
			// immediately restore the requested file to avoid script interruptions
			VikBookingHelperConditionalRules::restoreTemplateFileCode($file);
		}

		// get requested file content
		$fcontent = VikBookingHelperConditionalRules::getTemplateFileCode($file);
		if (empty($fcontent) || !is_string($fcontent)) {
			throw new Exception("File not found or its code is unreadable", 404);
		}

		if ($tagaction == 'remove') {
			// remove tag from code content
			$fcontent = str_replace($tag, '', $fcontent);
		} elseif ($tagaction == 'add') {
			// add tag to code content in the same exact position
			$fcontent = VikBookingHelperConditionalRules::addTagByComparingSources($tag, $file, $newcontent, $fcontent);
		} elseif ($tagaction == 'styles') {
			// apply the same styling rules
			$fcontent = VikBookingHelperConditionalRules::addStylesByComparingSources($custom_classes, $file, $newcontent, $fcontent);
		}

		// update the file code
		$res = VikBookingHelperConditionalRules::writeTemplateFileCode($file, $fcontent);

		if (!$res) {
			throw new Exception("Could not update the source code of the template file", 500);
		}

		// parse new HTML content
		$newhtmls = VikBookingHelperConditionalRules::getTemplateFilesContents($file);
		if (!is_array($newhtmls) || !isset($newhtmls[$file])) {
			throw new Exception("Could not parse new template file content", 404);
		}

		// trigger backup/mirroring, if available
		if (defined('ABSPATH')) {
			VikBookingUpdateManager::storeTemplateContent($file, $newhtmls[$file]);
		}

		// build output
		$output = new stdClass;
		$output->newhtml = $newhtmls[$file];
		$output->log = VikBookingHelperConditionalRules::getEditingLog();
		
		echo json_encode($output);
		exit;
	}

	/**
	 * AJAX endpoint to invoke methods of the geocoding helper.
	 */
	public function geocoding_endpoint()
	{
		$geo = VikBooking::getGeocodingInstance();
		$callback = VikRequest::getString('callback', '', 'request');

		if (empty($callback) || !method_exists($geo, $callback) || !is_callable(array($geo, $callback))) {
			throw new Exception("Callback not available", 403);
		}

		// invoke requested method
		$res = $geo->{$callback}();

		// prepare response
		$response = new stdClass;
		$response->{$callback} = $res;

		echo json_encode($response);
		exit;
	}

	public function refundtn()
	{
		//modal box, so we do not set menu or footer
	
		VikRequest::setVar('view', VikRequest::getCmd('view', 'refundtn'));
	
		parent::display();
	}

	public function do_refundtn()
	{
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();

		$bid = VikRequest::getInt('bid', 0, 'request');
		$amount = VikRequest::getFloat('amount', 0, 'request');
		$refund_reason = VikRequest::getString('refund_reason', '', 'request');
		$tmpl = VikRequest::getString('tmpl', '', 'request');
		$nav_suffix = $tmpl == 'component' ? '&tmpl=component' : '';

		$currencysymb = VikBooking::getCurrencySymb();

		if (empty($bid) || $amount <= 0) {
			VikError::raiseWarning('', JText::translate('VBO_PLEASE_FILL_FIELDS'));
			$app->redirect('index.php?option=com_vikbooking');
			exit;
		}

		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`=" . $bid . " AND `status`!='standby';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() < 1) {
			VikError::raiseWarning('', 'Booking not found');
			$app->redirect('index.php?option=com_vikbooking');
			exit;
		}
		$row = $dbo->loadAssoc();

		// get booking history instance
		$history_obj = VikBooking::getBookingHistoryInstance();
		$history_obj->setBid($row['id']);
		
		// get payment information
		$payment = VikBooking::getPayment($row['idpayment']);
		$tn_driver = is_array($payment) ? $payment['file'] : null;

		// transaction data validation callback
		$tn_data_callback = function($data) use ($tn_driver) {
			return (is_object($data) && isset($data->driver) && $data->driver == $tn_driver);
		};
		// get previous transactions
		$prev_tn_data = $history_obj->getEventsWithData(array('P0', 'PN'), $tn_data_callback);

		if (!is_array($prev_tn_data) || !count($prev_tn_data)) {
			// no previous transactions found
			VikError::raiseWarning('', 'No previous transactions found, unable to issue the refund');
			$app->redirect('index.php?option=com_vikbooking&task=refundtn&cid[]=' . $row['id'] . $nav_suffix);
			exit;
		}

		// push refund information for the payment gateway
		$row['total_to_refund'] = $amount;
		$row['transaction'] = $prev_tn_data;
		$row['refund_reason'] = $refund_reason;

		/**
		 * @wponly 	The payment gateway is loaded 
		 * 			through the apposite dispatcher.
		 */
		JLoader::import('adapter.payment.dispatcher');
		$obj = JPaymentDispatcher::getInstance('vikbooking', $payment['file'], $row, $payment['params']);

		if (!method_exists($obj, 'isRefundSupported') || !$obj->isRefundSupported()) {
			// refund not supported
			VikError::raiseWarning('', 'The selected payment method does not support refunds');
			$app->redirect('index.php?option=com_vikbooking&task=refundtn&cid[]=' . $row['id'] . $nav_suffix);
			exit;
		}

		// perform the refund transaction
		$array_result = $obj->refund();

		if ($array_result['verified'] != 1) {
			// raise warning by getting the message
			if (!empty($array_result['log']) && is_string($array_result['log'])) {
				VikError::raiseWarning('', $array_result['log']);
			} else {
				VikError::raiseWarning('', 'Operation failed');
			}
			$app->redirect('index.php?option=com_vikbooking&task=refundtn&cid[]=' . $row['id'] . $nav_suffix);
			exit;
		}

		// update total paid, total and refund columns for the booking
		$booking = new stdClass;
		$booking->id = $row['id'];
		if ($row['totpaid'] > 0) {
			$booking->totpaid = $row['totpaid'] - $amount;
		}
		if ($row['total'] > 0) {
			$booking->total = $row['total'] - $amount;
		}
		$booking->refund = (float)$row['refund'] + $amount;
		// update record in db
		$dbo->updateObject('#__vikbooking_orders', $booking, 'id');

		// store the refund event
		$event_descr = array(
			'(' . $payment['name'] . ')',
			$refund_reason,
			$currencysymb . ' ' . VikBooking::numberFormat($amount),
		);
		$history_obj->store('RF', implode("\n", $event_descr));

		// display success message and redirect
		$app->enqueueMessage(JText::translate('VBO_REFUND_SUCCESS'));
		$app->redirect('index.php?option=com_vikbooking&task=refundtn&cid[]=' . $row['id'] . '&success=1' . $nav_suffix);
		exit;
	}

}