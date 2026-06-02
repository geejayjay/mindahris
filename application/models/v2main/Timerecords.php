<?php 

	class Timerecords extends CI_Model {
		private $from 		= null;
		private $to 		= null;
		private $emp 		= null;
		private $empbio		= null;
		
		private $total_lates  = 0;
		private $total_unders = 0;
		private $total_daysp  = 0;
		private $total_hourp  = 0;
		
		public function setfrom($from_) {
			$this->from = $from_;
		}
		
		public function setto($to_) {
			$this->to = $to_;
		}
		
		public function setemp($emp_) {
			$this->emp = $emp_;
		}
		
		public function setbio($bio_) {
			$this->empbio = $bio_;
		}
		
		public function return_($what){
			return $this->$what;
			/*
			if ($this->$what != 0) {
				// return date("h:i",strtotime($this->$what));
				echo $this->$what;
			} else {
				return 0;
			}
			*/
		}
		
		public function gettime() {
				$from 		= $this->from;
				$to 		= $this->to;
				$emp 		= $this->emp;
				$empbio 	= $this->empbio;
				
				$this->load->model("v2main/Globalproc");
				
				$fyear 	= date("Y",strtotime($from));
				$tyear 	= date("Y",strtotime($to));
				
				// $dbfrom = date("n/j/Y", strtotime($from));
				// $dbto   = date("n/j/Y", strtotime($to));
				$dbfrom = date("Y-m-d", strtotime($from));
				$dbto   = date("Y-m-d", strtotime($to));
				
				$holidays = $this->Globalproc->getholidays($dbfrom,$dbto); 
				
				$dbto_p_1   = new DateTime($dbto);
				$dbfrom_p_1 = new DateTime($dbfrom);

				$dbto_p_1->modify('+1 day');
		//		echo $from."-".$to."-";
		//		echo $dbto_p_1->format('m/d/Y');
		
				$sql = "select
							cio.*,
							e.f_name
						from checkinout as cio
							JOIN employees as e
							on cio.biometric_id = e.biometric_id
						where 
							cio.biometric_id = '{$empbio}' 
						and convert(varchar,cio.checktime,107) between '{$dbfrom}' and '{$dbto_p_1->format('Y-m-d')}'
						ORDER BY CAST(cio.checktime as datetime) ASC";
		
		/*
		$sql = "select cio.*, e.f_name from checkinout as cio 
					JOIN employees as e on cio.biometric_id = e.biometric_id 
				where cio.biometric_id = '667' and convert(varchar,cio.checktime,107) 
					between '2024-02-29' and '2024-03-07' 
				ORDER BY CAST(cio.checktime as datetime) ASC";
		*/
 // echo $sql;
// convert(datetime,cio.checktime,101)
				$shift_sql = "select * from employee_schedule as es 
								JOIN shift_mgt_logs as sml 
								ON es.shift_id = sml.shift_id
								where es.employee_id = '{$emp}' 
									and DATEPART(year, es.date_sch_started) = '{$fyear}'
									and DATEPART(year, es.date_sch_ended) >= '{$tyear}'";
			// echo $shift_sql; 
			// and DATEPART(year, es.date_sch_ended) = '{$tyear}'
				$shifts    = $this->Globalproc->__getdata($shift_sql);
// var_dump($shifts);
				if (count($shifts) == 0) { // no shift in the employee schedule log
					$shifts   = [];
					$shifts[] = (object) array("type"=>"C/In" ,"shift_type"=>"AM_START","time_flexi_exact"=>"8:00 AM","time_exact" =>"8:00 AM");
					$shifts[] = (object) array("type"=>"C/Out","shift_type"=>"AM_END"  ,"time_flexi_exact"=>"12:00 PM","time_exact"=>"12:00 PM");
					$shifts[] = (object) array("type"=>"C/In" ,"shift_type"=>"PM_START","time_flexi_exact"=>"1:00 PM","time_exact" =>"1:00 PM");
					$shifts[] = (object) array("type"=>"C/Out","shift_type"=>"PM_END"  ,"time_flexi_exact"=>"5:00 PM","time_exact" =>"5:00 PM");
				}
				
				$thedtr = $this->Globalproc->__getdata($sql);	
				// var_dump($thedtr);
			
				$dtr    = []; // mother array 

		// set the date interval
		//--------------------------------------------------------------------------------------------------
				//list($d,$m,$y) = explode("/",$from);
				//$from  = $y."-".$m."-".$d;
				$begin = new DateTime($from); //2016-07-04
		
				$end = clone $begin;
			
				// this will default to a time of 00:00:00
				// list($td,$tm,$ty) = explode("/",$to);
				// $to  = $ty."-".$tm."-".$td;
				$end->modify($to); // 2016-07-08

				$end->setTime(0,0,1);     // new line

				$interval = new DateInterval('P1D');
				$period   = new DatePeriod($begin, $interval, $end);
		//--------------------------------------------------------------------------------------------------
		// end date interval	
				
				// totals 
					$total_lates = "00:00";
					$total_under = "00:00";
					$total_daysp = 0;
						// day indicator 
							$daytext = "";
						// end 
					$total_hourp = "00:00";
				// end totals
				
				foreach ($period as $key => $value) { // large "for	loop"
					// var_dump($value);
					$dateindex 		 = $value->format('m/d/Y');
					$dtr[$dateindex] = ["tardiness_am"	=> NULL]; 
					$dtr[$dateindex] = ["tardiness_pm"	=> NULL]; 
					
					$dtr[$dateindex] = ["undertime_am"	=> NULL]; 
					$dtr[$dateindex] = ["undertime_pm"	=> NULL]; 
					
					$dtr[$dateindex] = ["attachment" 	=> NULL];
					$dtr[$dateindex] = ["nicename" 	    => $value->format('D. M. d, Y')];
					
					$timelogs  = ["am_in"=>NULL,"am_out"=>NULL,"pm_in"=>NULL,"pm_out"=>NULL];
					
					$passslip  = ["personal" => NULL, "official" => NULL];
					// indicator 
					
					// check for an attachment 
					//	echo $value->format("m/d/Y");
						$attachment = $this->Globalproc->checkattachment($value->format('m/d/Y'), $emp);
					//	var_dump($attachment);
						if ($attachment == false) {break;}
						if (count($attachment) != 0) {
							$dtr[$dateindex]['attachment'] = $attachment;
						}
					// end checking attachment  
					
					$indicator = false;
					$period_val = $value->format('m/d/Y');
					// $period_val = strtotime($value->format('m/d/Y'));
					
					// $period_val = new DateTime($value->format('m/d/Y'));
				
					foreach($thedtr as $d) {
						//	echo $d->checktime."-".$d->checktype."<br/>";
							$ttime = $d->checktime;

							$time_in_db = date("m/d/Y", strtotime($d->checktime));
					
							//var_dump($this->Globalproc->check_holiday($holidays, date("n/j/Y",strtotime($time_in_db)))); return;
							
							$hour 		= 0;
							$mins 		= 0;
							$thetime    = date("h:i A", strtotime($ttime));
							 // echo $time_in_db."-".$period_val."<br/>";
							if ( $time_in_db === $period_val ) {
								// echo $time_in_db."=".$period_val."<br/>";
								if ($period_val != $daytext) {
									$total_daysp += 1;
									$daytext = $period_val;
								}
								foreach($shifts as $ss) {
									// echo $dateindex."=".date("H",strtotime($thetime))." | ".$thetime."<br/>";
									if ( $d->checktype == "C/In" ) {
										// if ( strcmp($ss->shift_type,"AM_START") == 0 && date("A", strtotime($ss->time_exact)) == "AM" ) {
											if ( strtoupper(date("A", strtotime($d->checktime))) == "AM") { // $timelogs['am_in'] == NULL
														// tardiness
														$start 	    = $thetime;
														
														$shift_time = $ss->time_flexi_exact;
														// echo $shift_time."<br/>";
														// $shift_time = $ss->time_exact;
													
													//if (date("H",strtotime($d->checktime)) < 12 ) {
														$indicator = "am_in";
														
														if ($timelogs['am_in'] == null || $timelogs['am_in'] == NULL) {
															$timelogs['am_in'] = $thetime;
														}
													// }
											} else if (date("A", strtotime($d->checktime)) == "PM") {
												$indicator = "pm_in";
												if (date("A", strtotime($d->checktime)) != date("A", strtotime($timelogs['am_in']))) { // $timelogs['pm_in'] == NULL && 
													// $dtr[$dateindex]["tardiness_pm"] = null;

													if ($timelogs['pm_in'] == null || $timelogs['pm_in'] == NULL) {
														if (date("H",strtotime($d->checktime)) > 12 /*&& date("H",strtotime($d->checktime)) < 12 */) {
															$shift_time = "1:00 PM";
															$start_date 					 = new DateTime($thetime);
															$since_start 					 = $start_date->diff(new DateTime( date("h:i A", strtotime($shift_time))));
															$hour 							 = ($since_start->h == 0)?"00":$since_start->h;
															$mins							 = (strlen($since_start->i) == 1)?"0".$since_start->i:$since_start->i;
															$dtr[$dateindex]["tardiness_pm"] = $hour.":".$mins;
														}
														$timelogs['pm_in'] = $thetime;	
													}
												}
											}
												
									} else if ( $d->checktype == "C/Out" ) {
											if (date("H",strtotime($thetime)) <= 12) { // am out  && $timelogs['am_out'] == NULL
												$indicator 			 = "am_out";
												
												if ($timelogs['am_out'] == null || $timelogs['am_out'] == NULL){
													$timelogs['am_out']	 = $thetime; // for am out 
													
													if (date("H",strtotime($d->checktime)) < 12) {
												//	if ( strtotime($d->checktime) < strtotime("00:12:00") ) {
														$shift_time = "12:00 PM";
														$start_date 					 = new DateTime($thetime);
														$since_start 					 = $start_date->diff(new DateTime( date("h:i A", strtotime($shift_time))));
														$hour 							 = ($since_start->h == 0)?"00":$since_start->h;
														$mins							 = (strlen($since_start->i) == 1)?"0".$since_start->i:$since_start->i;
														$dtr[$dateindex]["undertime_am"] = $hour.":".$mins;
													}
												}
											}
											if (date("H",strtotime($thetime)) > 12 && $ss->shift_type == "PM_END") { //&& $ss->shift_type == "PM_END") { //  && $timelogs['pm_out'] == NULL
												
												$indicator 			 = "pm_out";
												
												if ($timelogs['pm_out'] == null || $timelogs['pm_out'] == NULL) {
													$timelogs['pm_out']	 = $thetime; // for pm out 
													
													$shift_time			 = $shifts[3]->time_flexi_exact;
												//	$shift_time			 = $ss->time_flexi_exact;
													
													// first day yes
													$isfirst = false;
														if (strtolower(date("l",strtotime($time_in_db))) == "monday") {
															$shift_time 	= "5:00 PM";
															$isfirst 		= true;
														}
														
														if (strtolower(date("l",strtotime($time_in_db))) == "tuesday" &&
															$this->Globalproc->check_holiday($holidays, date("n/j/Y",strtotime($time_in_db))) == false &&
															$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-1 day', strtotime($time_in_db)))) == true ) {
																$shift_time 	= "5:00 PM";
																$isfirst 		= true;
														}
													
														if (strtolower(date("l",strtotime($time_in_db))) == "wednesday" &&
															$this->Globalproc->check_holiday($holidays, date('n/j/Y',strtotime($time_in_db))) == false && 
															$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-1 day', strtotime($time_in_db)))) == true &&
															$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-2 day', strtotime($time_in_db)))) == true) {
																$shift_time 	= "5:00 PM";
																$isfirst 		= true;
														}
																
														if (strtolower(date("l",strtotime($time_in_db))) == "thursday" &&
															$this->Globalproc->check_holiday($holidays, date('n/j/Y',strtotime($time_in_db))) == false && 
															$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-1 day', strtotime($time_in_db)))) == true &&
															$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-2 day', strtotime($time_in_db)))) == true &&
															$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-3 day', strtotime($time_in_db)))) == true) {
																$shift_time 	= "5:00 PM";
																$isfirst 		= true;
														}
																
														if (strtolower(date("l",strtotime($time_in_db))) == "friday" &&
															$this->Globalproc->check_holiday($holidays, date('n/j/Y',strtotime($time_in_db))) == false && 
															$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-1 day', strtotime($time_in_db)))) == true &&
															$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-2 day', strtotime($time_in_db)))) == true &&
															$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-3 day', strtotime($time_in_db)))) == true && 
															$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-4 day', strtotime($time_in_db)))) == true) {
																$shift_time 	= "5:00 PM";
																$isfirst 		= true;
														}
														
														if( strtotime($d->checktime) < strtotime($shift_time) ) {
															if (!$isfirst) {
																$am_shift_time  = $shifts[0]->time_flexi_exact;
																$pm_shift_time  = $shifts[3]->time_flexi_exact;
																$timein 	    = "";
																	
																	if ( strtotime($timelogs['am_in']) > strtotime($am_shift_time) ) {
																		// late
																		$timein = $am_shift_time;
																	} else if ( strtotime($timelogs['am_in']) < strtotime($am_shift_time) ) {
																		// not late
																		$timein = $timelogs['am_in'];
																		
																	}
																
																	$shift_time = strtotime("+9 hours", strtotime($timein));
														
																	if ( strtotime(date("h:i A",strtotime($d->checktime))) < $shift_time ){
																	//	echo strtotime(date("h:i A",strtotime($d->checktime)))." is less than ".$shift_time."<br/>";	
																		// undertime here 
																		$shift_time = strtotime("+9 hours", strtotime($am_shift_time));
																	 	$shift_time = date("h:i A",$shift_time);
																	//	$shift_time = $am_shift_time;
												
																		$start_date 					 = new DateTime($thetime);
																		$since_start 					 = $start_date->diff(new DateTime( date("h:i A", strtotime($shift_time))));
																		
																		$hour 							 = ($since_start->h == 0)?"00":$since_start->h;
																		$mins							 = (strlen($since_start->i) == 1)?"0".$since_start->i:$since_start->i;
																		
																		$dtr[$dateindex]["undertime_pm"] = $hour.":".$mins;
																	}
															}
														}
												}
											}
									}
								}
								
							}
						
					}
					
					if (count($attachment['ret']) != 0) {
						foreach($attachment['ret'] as $atm){
							switch($atm->shift_type) {
								case "AM":
								case "AM_START":
								case "AM_END":
									if ($atm->checktype == "C/In") {
										if ($timelogs['am_in'] == NULL || $timelogs['am_in'] == null) {
											if ($atm->type_mode=="AMS" /*|| $atm->type_mode=="PS"*/) {
												$timelogs['am_in']	= date("h:i A", strtotime($atm->checktime));
											} 
										}
									}
									break;
								case "PM":
								case "PM_START":
								case "PM_END":
									if ($atm->checktype == "C/Out") {
										if (date("H",strtotime($atm->checktime)) == 12) {
											if ($timelogs['am_out'] == NULL || $timelogs['am_out'] == null) {
												if ($atm->type_mode=="AMS" /*|| $atm->type_mode=="PS"*/) {
													$timelogs['am_out']	= date("h:i A", strtotime($atm->checktime));
												}
											} else {
												$attachment['ret'] = null;
											}
										} else if (date("H",strtotime($atm->checktime)) > 12) {
											if ($timelogs['pm_out'] == NULL || $timelogs['pm_out'] == null) {
												if ($atm->type_mode=="AMS" /*|| $atm->type_mode=="PS"*/) {
													$timelogs['pm_out']	= date("h:i A", strtotime($atm->checktime));
												}
											}
										}
									} else if ($atm->checktype == "C/In") {
										if ($timelogs['pm_in'] == NULL || $timelogs['pm_in'] == null) {
											if ($atm->type_mode=="AMS" /*|| $atm->type_mode=="PS"*/) {
												$timelogs['pm_in']	= date("h:i A", strtotime($atm->checktime));
											}
										}
									}
									break;
							}
						
							if ($atm->type_mode == "PS") {
								$pass_timeout = $atm->time_out;
								$pass_timein  = $atm->time_in;
								
								$time 		  = $this->Globalproc->getdiff_time($pass_timein,$pass_timeout,"-");
								
								switch($atm->ps_type) {
									case 2:	// personal
										$passslip['personal'] = $time;
										break;
									case 1: // official
										$passslip['official'] = $time;
										break;
								}
								
								$passslip['exact_id'] = $atm->exact_id;
								$dtr[$dateindex]['passslip'] = $passslip;
							}
						
						}
					}
					
					array_push( $dtr[$dateindex], $timelogs );
					
					### computation of tardiness ###
						// ===== tardiness AM
						$time_in_db = $value->format('n/j/Y');
						$shift_time = $shifts[0]->time_flexi_exact;
						$start_am 	= date("h:i A", strtotime($dtr[$dateindex][0]['am_in']));
					
					// indicator, if with tardiness or undertime 
					$iswithtardy = false;
					
						if ($dtr[$dateindex][0]['am_in'] != NULL || 
							$dtr[$dateindex][0]['pm_in'] != NULL ||
							$dtr[$dateindex][0]['pm_out'] != NULL ||
							$dtr[$dateindex][0]['am_out'] != NULL) {
								if ($dtr[$dateindex][0]['am_in'] == NULL) { 
									$start_am = "12:00 PM";
									// $shift_time = "8:00 AM";
									//$iswithtardy 					 = true;
								}
						}
						// echo $start; 
							if (strtolower(date("l",strtotime($time_in_db))) == "monday") {
								$shift_time = "8:00 AM";
							}

							if (strtolower(date("l",strtotime($time_in_db))) == "tuesday" &&
								$this->Globalproc->check_holiday($holidays, date("n/j/Y",strtotime($time_in_db))) == false &&
								$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-1 day', strtotime($time_in_db)))) == true ) {
									$shift_time = "8:00 AM";
							} 

							if (strtolower(date("l",strtotime($time_in_db))) == "wednesday" &&
								$this->Globalproc->check_holiday($holidays, date('n/j/Y',strtotime($time_in_db))) == false && 
								$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-1 day', strtotime($time_in_db)))) == true &&
								$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-2 day', strtotime($time_in_db)))) == true) {
									$shift_time = "8:00 AM";
							}

							if (strtolower(date("l",strtotime($time_in_db))) == "thursday" &&
								$this->Globalproc->check_holiday($holidays, date('n/j/Y',strtotime($time_in_db))) == false && 
								$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-1 day', strtotime($time_in_db)))) == true &&
								$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-2 day', strtotime($time_in_db)))) == true &&
								$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-3 day', strtotime($time_in_db)))) == true) {
									$shift_time = "8:00 AM";
							}

							if (strtolower(date("l",strtotime($time_in_db))) == "friday" &&
								$this->Globalproc->check_holiday($holidays, date('n/j/Y',strtotime($time_in_db))) == false && 
								$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-1 day', strtotime($time_in_db)))) == true &&
								$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-2 day', strtotime($time_in_db)))) == true &&
								$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-3 day', strtotime($time_in_db)))) == true && 
								$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-4 day', strtotime($time_in_db)))) == true) {
									$shift_time = "8:00 AM";
							}
							
							if(strtotime($start_am) > strtotime($shift_time) || $dtr[$dateindex][0]['am_in'] == NULL) {
								if ( strtolower(date("D",strtotime($time_in_db))) != "sat" && strtolower(date("D",strtotime($time_in_db))) != "sun") {
									$start_date 					 = new DateTime($start_am);
									$since_start 					 = $start_date->diff(new DateTime( date("h:i A", strtotime($shift_time))));
									$hour 							 = ($since_start->h == 0)?"00":$since_start->h;
									$mins							 = (strlen($since_start->i) == 1)?"0".$since_start->i:$since_start->i;
									$dtr[$dateindex]["tardiness_am"] = $hour.":".$mins;
								}
							}
						
							// resetting the computed tardiness if its respective index key is null
								if ($dtr[$dateindex][0]['am_in'] == NULL && 
									$dtr[$dateindex][0]['pm_in'] == NULL &&
									$dtr[$dateindex][0]['pm_out'] == NULL &&
									$dtr[$dateindex][0]['am_out'] == NULL) {
										// all is blank
									}

								if ($dtr[$dateindex][0]['am_in'] == NULL) {
									// $dtr[$dateindex]['tardiness_am'] = null;
									if (!$iswithtardy) {
										unset($dtr[$dateindex]['tardiness_am']);
									}
								}
								
								if ($dtr[$dateindex][0]['pm_in'] == NULL) {
									// $dtr[$dateindex]['tardiness_pm'] = null;
									if (!$iswithtardy) {
										unset($dtr[$dateindex]['tardiness_pm']);
									}
								}
									
								if ($dtr[$dateindex][0]['pm_out'] == NULL) {
									if (($dtr[$dateindex][0]['am_in'] == NULL)) {
										// $dtr[$dateindex]['undertime_pm'] = null;
										if (!$iswithtardy) {
											unset($dtr[$dateindex]['undertime_pm']);
										}
									}
								}
									
								if ($dtr[$dateindex][0]['am_out'] == NULL) {
									// $dtr[$dateindex]['undertime_am'] = null;
									if (!$iswithtardy) {
										unset($dtr[$dateindex]['undertime_am']);
									}
								}
								
								if ( strtolower(date("D",strtotime($time_in_db))) == "sat" || 
									 strtolower(date("D",strtotime($time_in_db))) == "sun" || 
									 $this->Globalproc->check_holiday($holidays, $time_in_db) == true ) {
										// $dtr[$dateindex]['undertime_am'] = null;
										// $dtr[$dateindex]['undertime_pm'] = null;
										// $dtr[$dateindex]['tardiness_pm'] = null;
										// $dtr[$dateindex]['tardiness_am'] = null;
									if (!$iswithtardy) {
										unset($dtr[$dateindex]['tardiness_am']);
										unset($dtr[$dateindex]['tardiness_pm']);
										unset($dtr[$dateindex]['undertime_pm']);
										unset($dtr[$dateindex]['undertime_am']);
									}
								}
							// end resetting
						// ======== end tardiness AM
					### computation of tardiness ###
				
				// consolidation of the total tardiness and undertime 
					if (isset($dtr[$dateindex]['tardiness_am'])) {
						$time1   		= $dtr[$dateindex]['tardiness_am'];
						$time2   		= $total_lates;
						
						$secs 			= strtotime($time2)-strtotime("00:00:00");
						$result 		= date("H:i",strtotime($time1)+$secs);
						
						$total_lates   = $result;
					}
					
					if (isset($dtr[$dateindex]['tardiness_pm'])) {
						// $l_pm = strtotime($dtr[$dateindex]['tardiness_pm']);
						// $total_lates  += $l_pm;
						
						$time1   		= $dtr[$dateindex]['tardiness_pm'];
						$time2   		= $total_lates;
						
						$secs 			= strtotime($time2)-strtotime("00:00:00");
						$result 		= date("H:i",strtotime($time1)+$secs);
						
						$total_lates   = $result;
					}
					
					if (isset($dtr[$dateindex]['undertime_am'])){
						// $u_am = strtotime($dtr[$dateindex]['undertime_am']);
						// $total_under += $u_am;
						
						$time1   		= $dtr[$dateindex]['undertime_am'];
						$time2   		= $total_under;
						
						$secs 			= strtotime($time2)-strtotime("00:00:00");
						$result 		= date("H:i",strtotime($time1)+$secs);
						
						$total_under   = $result;
						
					}
					
					if (isset($dtr[$dateindex]['undertime_pm'])) {
						// $u_pm = strtotime($dtr[$dateindex]['undertime_pm']);
						// $total_under += $u_pm;
						
						$time1   		= $dtr[$dateindex]['undertime_pm'];
						$time2   		= $total_under;
						
						$secs 			= strtotime($time2)-strtotime("00:00:00");
						$result 		= date("H:i",strtotime($time1)+$secs);
						
						$total_under   = $result;
					}
					// break;
				// end of consolidation 
				
					// holidays 
						if ($this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime($dateindex)))) {
							$dtr[$dateindex]['holiday'] = "holiday";
						}
					// end holidays 
				} // end for
					
					$this->total_lates  = $total_lates;
					$this->total_unders = $total_under;
					$this->total_daysp  = $total_daysp;
					$this->total_hourp  = $total_hourp;
					
				// var_dump($dtr);
					
				return $dtr;
				
		}
		
		function getridof($what, $from){
			$ret = "";
			for($i=0;$i<=strlen($from)-1;$i++) {
				if($from[$i]!=$what){
					$ret .= $from[$i];
				}
			}
			return $ret;
		}
	}
