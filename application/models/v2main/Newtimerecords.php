<?php 

	class Newtimerecords extends CI_Model {
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
				
			$dbfrom = date("n/j/Y", strtotime($from));
			$dbto   = date("n/j/Y", strtotime($to));
				
			$holidays = $this->Globalproc->getholidays($dbfrom,$dbto); 
				
			$dbto_p_1 = new DateTime($dbto);

			$dbto_p_1->modify('+1 day');
			
			$sql = "select
						cio.*,
						e.f_name
					from checkinout as cio
					JOIN employees as e
						on cio.biometric_id = e.biometric_id
					where cio.biometric_id = '{$empbio}' and convert(datetime,cio.checktime) between '{$dbfrom}' and '{$dbto_p_1->format('m/d/Y')}'
					ORDER BY CAST(cio.checktime as datetime) ASC";
			
			$thedtr = $this->Globalproc->__getdata($sql);
			
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
			
			$currentweek 		= null;
			$totalnumberofhours = null;
			$numberofdays 		= 0;
			
			foreach ($period as $key => $value) { // large "for	loop"
				$dateindex      = $value->format('m/d/Y');
				$thewknum       = date("W", strtotime($dateindex));
				$weeknumber  	= $value->format('m/Y')."-".date("W", strtotime($dateindex));
				
				if ($currentweek == $thewknum) {
					// count the number of days in a week
					$numberofdays++;
				}
				
				$currentweek 			  	  = $thewknum;
				
				$timelogs  				  	  = ["am_in"=>NULL,"am_out"=>NULL,"pm_in"=>NULL,"pm_out"=>NULL];
				$hoursrenderedfortheweek  	  = "0:00";
				
				$period_val 			  	  = $value->format('m/d/Y');
				
				$dtr[$weeknumber]["hoursrenderedfortheweek"] = "0:00";
				$dtr[$weeknumber]["totaltardi"] 			 = "0:00";
				$dtr[$weeknumber]["totalunder"] 			 = "0:00";
				$dtr[$weeknumber]["numberofdays"] 			 = "0";
				$dtr[$weeknumber][$dateindex] 				 = [];
				
				foreach($thedtr as $d) {
					$time_in_db = date("m/d/Y", strtotime($d->checktime));
					
					if ($period_val	== $time_in_db) {
						$isampm = date("A", strtotime($d->checktime));
						
						// C IN
						if ($d->checktype == "C/In") {
							// check if am or pm
							if ($isampm == "AM") {
								$timelogs['am_in'] = date("h:i A", strtotime($d->checktime));

							}
							// end morning
								
							// afternoon
							if ($isampm == "PM") {
								$timelogs['pm_in'] = date("h:i A", strtotime($d->checktime));

							}
							// end of afternoon	
						} // end of C IN
						
						if ($d->checktype == "C/Out") {
							if ($isampm == "AM") {
								$timelogs['am_out'] = date("h:i A", strtotime($d->checktime));
							}
							
							if ($isampm == "PM") {
								if (date("H", strtotime($d->checktime)) == 12 /* && date("H", strtotime($d->checktime)) <= 13 */) {
									$timelogs['am_out'] = date("h:i A", strtotime($d->checktime));
								} else {
									$timelogs['pm_out'] = date("h:i A", strtotime($d->checktime));
								}
							}
						}
						
					}
				}
				
				/*
				$dtr[$weeknumber]["hoursrenderedfortheweek"] = null;
				$dtr[$weeknumber]["totaltardi"] 			 = null;
				$dtr[$weeknumber]["totalunder"] 			 = null;
				$dtr[$weeknumber]["numberofdays"] 			 = null;*/
				array_push($dtr[$weeknumber][$dateindex], ["tardiness"=>[]]);
				array_push($dtr[$weeknumber][$dateindex], ["undertime"=>[]]);
				array_push($dtr[$weeknumber][$dateindex],$timelogs);
				
			} // end large "for loop"
			
			foreach($dtr as $weekkey => $perweek) {
				foreach($period as $key => $thedates) {
					$wk = date("m/d/Y", strtotime($thedates->format("m/d/Y")));
					
					if (isset($perweek[$wk])) {
						$timediff_morn 	   						  = $this->computetotal($perweek[$wk][2]['am_in'],$perweek[$wk][2]['am_out']);
						$timediff_aft  	   						  = $this->computetotal($perweek[$wk][2]['pm_in'],$perweek[$wk][2]['pm_out']);
						$totalrenderedhour 						  = $this->totalbothdiff($timediff_morn, $timediff_aft);
						$dtr[$weekkey]["hoursrenderedfortheweek"] = $this->totalbothdiff($dtr[$weekkey]["hoursrenderedfortheweek"],$totalrenderedhour);
						
						// tardiness morning computation 
							$morningtimein  			 	 	  = "9:00 AM";
							
							$tardy_timediff 			 	 	  = "0:00";
							if (strtotime($morningtimein > strtotime($perweek[$wk][2]['am_in']))) {
								echo $perweek[$wk][2]['am_in']."<br/>";
								$tardy_timediff 			 	  = $this->totalbothdiff($morningtimein,$perweek[$wk][2]['am_in']);
							}
							
							array_push($dtr[$weekkey][$wk][0]['tardiness'],["am"=>[$wk => $tardy_timediff]]);
							$dtr[$weekkey]['totaltardi'] 		  = $this->computetotal($tardy_timediff,$dtr[$weekkey]['totaltardi']);
						// end of tardiness computation	
						
						// tardiness afternoon computation 
							$afternoontimein 			 		  = "1:00 PM";
							
							$tardy_timediff_aft 				  = "0:00";
							if (strtotime($afternoontimein) > strtotime($perweek[$wk][2]['pm_in'])) {
								$tardy_timediff_aft 		 		  = $this->totalbothdiff($afternoontimein,$perweek[$wk][2]['pm_in']);
							}
							
							array_push($dtr[$weekkey][$wk][0]['tardiness'],["pm"=>[$wk=>$tardy_timediff_aft]]);
							$dtr[$weekkey]['totaltardi'] 		  = $this->computetotal($tardy_timediff_aft,$dtr[$weekkey]['totaltardi']);
							
							
						// undertime morning computation
							$morningtimeout 			 		  = "12:00 PM";
							
							$under_timediff 					  = "0:00";
							
							if ( date("H", strtotime($perweek[$wk][2]['am_out'])) < date("H", strtotime($morningtimeout)) ) { 
								$under_timediff 					  = $this->totalbothdiff($morningtimeout, $perweek[$wk][2]['am_out']);
							}
							
							array_push($dtr[$weekkey][$wk][1]['undertime'],["am"=>[$wk=>$under_timediff]]);
							$dtr[$weekkey]['totalunder']		  = $this->computetotal($under_timediff,$dtr[$weekkey]['totalunder']);
						
						// undertime afternoon computation 
							$afternoontimeout 					  = "4:00 PM";
							
							$under_aftertimediff 				  = "0:00";
							
							if (date("H", strtotime($perweek[$wk][2]['pm_out'])) < date("H", strtotime($afternoontimeout))) {
								$under_aftertimediff 			  = $this->totalbothdiff($afternoontimeout,$perweek[$wk][2]['pm_out']);
							}
							
							array_push($dtr[$weekkey][$wk][1]['undertime'],["pm" => [$wk => $under_aftertimediff]]);
							$dtr[$weekkey]['totalunder']		  = $this->computetotal($under_timediff, $dtr[$weekkey]['totalunder']);
					}
				}
			}
			
			// var_dump($dtr); 
			 return ["dtr"=>$dtr,"period"=>$period];
		}
		
		function computetotal($timein, $timeout) {
			if ($timein == null) {$timein = "0:00";}
			if ($timeout == null) {$timeout = "0:00";}
			
			list($timein_hr, $timein_min) = explode(":",$timein);
			
			$timein_unit_hr  = ($timein_hr <= 1)?"hour":"hours";
			$timein_unit_min = ($timein_min <= 1)?"minute":"minutes";

			$start_date 					 = new DateTime($timein);
			$since_start 					 = $start_date->diff(new DateTime( date("h:i A", strtotime($timeout))));
			$hour 							 = ($since_start->h == 0)?"00":$since_start->h;				
			$mins							 = (strlen($since_start->i) == 1)?"0".$since_start->i:$since_start->i;
			// echo "hello".$hour.":".$mins;
			return $hour.":".$mins;
		}
		
		function totalbothdiff($start, $end) {
									
			list($ren_hr, $ren_min)  = explode(":", $start);
			$ren_unit_hr             = ($ren_hr  <= 1)?"hour":"hours";
			$ren_unit_min 			 = ($ren_min <= 1)?"minute":"minutes";
			// echo "hello".date("h:i", strtotime("+{$ren_hr} {$ren_unit_hr} +{$ren_min} {$ren_unit_min}", strtotime($end)));
			return date("h:i", strtotime("+{$ren_hr} {$ren_unit_hr} +{$ren_min} {$ren_unit_min}", strtotime($end)));
		}
		
		/*
		public function gettime() {
				$from 		= $this->from;
				$to 		= $this->to;
				$emp 		= $this->emp;
				$empbio 	= $this->empbio;
				
				$this->load->model("v2main/Globalproc");
				
				$fyear 	= date("Y",strtotime($from));
				$tyear 	= date("Y",strtotime($to));
				
				$dbfrom = date("n/j/Y", strtotime($from));
				$dbto   = date("n/j/Y", strtotime($to));
				
				$holidays = $this->Globalproc->getholidays($dbfrom,$dbto); 
				
				$dbto_p_1 = new DateTime($dbto);

				$dbto_p_1->modify('+1 day');
		//		echo $from."-".$to."-";
		//		echo $dbto_p_1->format('m/d/Y');
				$sql = "select
							cio.*,
							e.f_name
						from checkinout as cio
						JOIN employees as e
							on cio.biometric_id = e.biometric_id
						where cio.biometric_id = '{$empbio}' and convert(datetime,cio.checktime) between '{$dbfrom}' and '{$dbto_p_1->format('m/d/Y')}'
						ORDER BY CAST(cio.checktime as datetime) ASC";
//echo $sql;
				$shift_sql = "select * from employee_schedule as es 
								JOIN shift_mgt_logs as sml 
								ON es.shift_id = sml.shift_id
								where es.employee_id = '{$emp}' 
									and DATEPART(year, es.date_sch_started) = '{$fyear}'
									and DATEPART(year, es.date_sch_ended) >= '{$tyear}'";
			// echo $shift_sql; 
			// and DATEPART(year, es.date_sch_ended) = '{$tyear}'
				$shifts    = $this->Globalproc->__getdata($shift_sql);
 //var_dump($shifts);
				if (count($shifts) == 0) { // no shift in the employee schedule log
					$shifts   = [];
					$shifts[] = (object) array("type"=>"C/In" ,"shift_type"=>"AM_START","time_flexi_exact"=>"9:00 AM","time_exact" =>"7:00 AM");
					$shifts[] = (object) array("type"=>"C/Out","shift_type"=>"AM_END"  ,"time_flexi_exact"=>"12:00 PM","time_exact"=>"12:00 PM");
					$shifts[] = (object) array("type"=>"C/In" ,"shift_type"=>"PM_START","time_flexi_exact"=>"1:00 PM","time_exact" =>"1:00 PM");
					$shifts[] = (object) array("type"=>"C/Out","shift_type"=>"PM_END"  ,"time_flexi_exact"=>"7:00 PM","time_exact" =>"4:00 PM");
				}
				
				$thedtr = $this->Globalproc->__getdata($sql);	
			//	var_dump($shifts);
			
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
					
					foreach($thedtr as $d) {
						//	echo $d->checktime."-".$d->checktype."<br/>";
							$time_in_db = date("m/d/Y", strtotime($d->checktime));
							
							//var_dump($this->Globalproc->check_holiday($holidays, date("n/j/Y",strtotime($time_in_db)))); return;
							
							$hour = 0;
							$mins = 0;
							$thetime    = date("h:i A", strtotime($d->checktime));
							// echo $time_in_db."-".$period_val."<br/>";
							if ( $time_in_db == $period_val ) {
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
														if (date("H",strtotime($d->checktime)) > 12) {
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
															$shift_time 	= "4:00 PM";
															$isfirst 		= true;
														}
														
														if (strtolower(date("l",strtotime($time_in_db))) == "tuesday" &&
															$this->Globalproc->check_holiday($holidays, date("n/j/Y",strtotime($time_in_db))) == false &&
															$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-1 day', strtotime($time_in_db)))) == true ) {
																$shift_time 	= "4:00 PM";
																$isfirst 		= true;
														}
													
														if (strtolower(date("l",strtotime($time_in_db))) == "wednesday" &&
															$this->Globalproc->check_holiday($holidays, date('n/j/Y',strtotime($time_in_db))) == false && 
															$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-1 day', strtotime($time_in_db)))) == true &&
															$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-2 day', strtotime($time_in_db)))) == true) {
																$shift_time 	= "4:00 PM";
																$isfirst 		= true;
														}
																
														if (strtolower(date("l",strtotime($time_in_db))) == "thursday" &&
															$this->Globalproc->check_holiday($holidays, date('n/j/Y',strtotime($time_in_db))) == false && 
															$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-1 day', strtotime($time_in_db)))) == true &&
															$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-2 day', strtotime($time_in_db)))) == true &&
															$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-3 day', strtotime($time_in_db)))) == true) {
																$shift_time 	= "4:00 PM";
																$isfirst 		= true;
														}
																
														if (strtolower(date("l",strtotime($time_in_db))) == "friday" &&
															$this->Globalproc->check_holiday($holidays, date('n/j/Y',strtotime($time_in_db))) == false && 
															$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-1 day', strtotime($time_in_db)))) == true &&
															$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-2 day', strtotime($time_in_db)))) == true &&
															$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-3 day', strtotime($time_in_db)))) == true && 
															$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-4 day', strtotime($time_in_db)))) == true) {
																$shift_time 	= "4:00 PM";
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
																		// undertime here 
																		$shift_time = strtotime("+9 hours", strtotime($am_shift_time));
																	 	$shift_time = date("h:i A",$shift_time);
																	
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
											if ($atm->type_mode=="AMS") {
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
												if ($atm->type_mode=="AMS") {
													$timelogs['am_out']	= date("h:i A", strtotime($atm->checktime));
												}
											} else {
												$attachment['ret'] = null;
											}
										} else if (date("H",strtotime($atm->checktime)) > 12) {
											if ($timelogs['pm_out'] == NULL || $timelogs['pm_out'] == null) {
												if ($atm->type_mode=="AMS") {
													$timelogs['pm_out']	= date("h:i A", strtotime($atm->checktime));
												}
											}
										}
									} else if ($atm->checktype == "C/In") {
										if ($timelogs['pm_in'] == NULL || $timelogs['pm_in'] == null) {
											if ($atm->type_mode=="AMS") {
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
								//$shift_time = "8:00 AM";
							}

							if (strtolower(date("l",strtotime($time_in_db))) == "tuesday" &&
								$this->Globalproc->check_holiday($holidays, date("n/j/Y",strtotime($time_in_db))) == false &&
								$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-1 day', strtotime($time_in_db)))) == true ) {
									//$shift_time = "8:00 AM";
							} 

							if (strtolower(date("l",strtotime($time_in_db))) == "wednesday" &&
								$this->Globalproc->check_holiday($holidays, date('n/j/Y',strtotime($time_in_db))) == false && 
								$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-1 day', strtotime($time_in_db)))) == true &&
								$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-2 day', strtotime($time_in_db)))) == true) {
									//$shift_time = "8:00 AM";
							}

							if (strtolower(date("l",strtotime($time_in_db))) == "thursday" &&
								$this->Globalproc->check_holiday($holidays, date('n/j/Y',strtotime($time_in_db))) == false && 
								$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-1 day', strtotime($time_in_db)))) == true &&
								$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-2 day', strtotime($time_in_db)))) == true &&
								$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-3 day', strtotime($time_in_db)))) == true) {
									//$shift_time = "8:00 AM";
							}

							if (strtolower(date("l",strtotime($time_in_db))) == "friday" &&
								$this->Globalproc->check_holiday($holidays, date('n/j/Y',strtotime($time_in_db))) == false && 
								$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-1 day', strtotime($time_in_db)))) == true &&
								$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-2 day', strtotime($time_in_db)))) == true &&
								$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-3 day', strtotime($time_in_db)))) == true && 
								$this->Globalproc->check_holiday($holidays, date('n/j/Y', strtotime('-4 day', strtotime($time_in_db)))) == true) {
									//$shift_time = "8:00 AM";
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
		*/
		
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
