<?php 

	class Ctomodel extends CI_Model {
		
		public function getcto($empid) {
			$this->load->model("v2main/Globalproc");
			
			$sql   = "select eot.*,e.f_name from employee_ot_credits as eot
					  LEFT JOIN employees as e on eot.emp_id = e.employee_id
					  where eot.emp_id = '{$empid}' ORDER BY eot.elc_otcto_id DESC";
			$data  = $this->Globalproc->__getdata($sql);
		  
			return $data;
		}
		
		public function gettimedif($time1, $time2) {
			$thetime 						 = $time1;
			$shift_time 					 = $time2;
			$start_date 					 = new DateTime($thetime);
			$since_start 					 = $start_date->diff(new DateTime( date("h:i A", strtotime($shift_time)) ));
			
			return ($since_start->h).":".($since_start->i);
		}
		
		public function convertoseconds($time) {
			list($hour,$mins) = explode(":",$time);
			
			return ($hour*3600)+($mins*60);		
		}
	
		public function returntotimeformat($seconds) {

			$dtF  = new \DateTime('@0');
			$dtT  = new \DateTime("@$seconds");

			$d 	  = $dtF->diff($dtT)->format('%d');
			$h    = $dtF->diff($dtT)->format('%h');
			$m    = $dtF->diff($dtT)->format('%i');

			// return ($h+($d*8)).":".$m;
			return $d.":".$h.":".$m;

		}
		
		public function standard_time($time) {
		// accepts the time format of 'dd:hh:mm'
			// but return only the 'hh:mm'
		
			$hr = floor($time/32400);  // for 1 hour
			
			$mm = floor(($time/60)%60);
			 
			// compute the exceeding 60mins
				// round the time/32400 as a in two decimal points
					// then round the a in 1 decimal point
				// then explode the decimal
				$ex = explode(".",round(round($time/32400,2),1));
				if (count($ex)>1){
					if ($ex[1] > 1) {
						// add 1 to hour if the first index of ex is greated than 1
						if ($ex[1] == 2) {
							$sub = ($ex[1]-1);
						} else if ($ex[1] >= 3) {
							$sub = ($ex[1]);
						}
						$hr += $sub;
					}
				}
			// end
	
			return $hr.":".$mm;;
		}

		public function consec($time) {
		// converts to office time computation and format
			$check  = explode(":",$time);
			
			if (count($check)==2) {
				$time = "00:".$time;
			}
			
			list($day,$hour,$min) = explode(":",$time);
			
			$dd = ($day*259200);
			$hr = ($hour*32400);
			$mn = ($min*60);
			
			//echo $dd+$hr+$mn."<br/>";
			
			return ($dd+$hr+$mn);
		}
		
		public function returntoofficetimeformat($time) {
			// $dy = floor($time/3200);
			$hr = floor($time/10800);
			$mn = floor(($time/180)%180);
			
			return $hr.":".$mn;
		}
	
		public function compute_empcredits($emp_id) {
			$a  			 = $this->getcto($emp_id);
			
			$data['ctodata'] = [];
			for($n = count($a)-1; $n >= 0 ; $n--) {
				$vars = ["day"					=> null,
						 "amin"					=> null,
						 "amout"				=> null,
						 "pmin"					=> null,
						 "pmout"				=> null,
						 "totalovertime"		=> null,
						 "creditsx1hh"			=> null,
						 "creditsx1mm"			=> null,
						 "creditsx15xhh"		=> null,
						 "creditsx15xmm"		=> null,
						 "totalcreditswithx"	=> null,
						 "daycto"				=> null,
						 "usedcochr"			=> null,
						 "usedcocmm"			=> null,
						 "remaining"			=> null];

				if($a[$n]->credit_type == "FB") {
					$remaining 					= $this->consec($this->returntotimeformat($a[$n]->total_credit));
				
					$vars['day']				= $a[$n]->date_of_application;
					$vars['totalcreditswithx']	= $a[$n]->total;
					
				} else if ($a[$n]->credit_type == "OT") {
					$totalot = "";
					
					// mark 1
					$morningtimedif = "00:00";
					if ($a[$n]->am_in != null) {
						$time1 = null;
						$time2 = null;
						
						if ($a[$n]->am_in != null && $a[$n]->am_out == null) {
							$a[$n]->am_out == "12:00 PM";
						}
	
						$time1 			= $a[$n]->am_in;
						$time2 			= $a[$n]->am_out;
						$morningtimedif = $this->gettimedif($time1, $time2);
					}
					
					$afternoontimedif = "00:00";
					if ($a[$n]->pm_in != null && $a[$n]->pm_out != null) {
						$time1 = null;
						$time2 = null;
						
						if ($a[$n]->pm_in == null && $a[$n]->pm_out != null) {
							$a[$n]->pm_in == "1:00 PM";
						}
						
						$time1 			  = $a[$n]->pm_in;
						$time2 			  = $a[$n]->pm_out;
						$afternoontimedif = $this->gettimedif($time1,$time2);
					}
					
					if ($morningtimedif != "0:0" && $afternoontimedif != "0:0") {
						$totalot 	= ($this->convertoseconds($morningtimedif) + $this->convertoseconds($afternoontimedif));
					} else if ($morningtimedif != "0:0" && $afternoontimedif == "0:0") {
						$totalot = $morningtimedif;
					} else if ($morningtimedif == "0:0" && $afternoontimedif != "0:0") {
						$totalot = $afternoontimedif;
					}
					
					// multiplier ===================		
					// times 1
					$times1hh  = null;
					$times1mm  = null;
					
					// times 1.5
					$times15hh = null;
					$times15mm = null;
					
					$totalcreditswithx = "";
					$totalinsecs 	   = "";
					
					if (is_int($totalot)) {
						$totalot 	= $this->returntotimeformat($totalot);
					} 
					
					// $totalot = '216523'; // for example purposes only
					// counting the explosion thrown from totalot 
						// if the count is less than 3, then there is no day available in the given value 
							// format should be dd:hh:mm, given in this state is hh:mm lacks dd
							// add dd to the given value format 
						$explosion = explode(":",$totalot);
					
						if (count($explosion) < 3 ) {
							$new_totalot = "0:".$totalot;
							$totalot 	 = $new_totalot;
						}
					// end of counting 
					
					if( $a[$n]->mult == "1" ) {
						$times1hh	 	   		 = date("h", strtotime($totalot));
						$times1mm 		   		 = date("i", strtotime($totalot));
						
						list($day, $hour, $mins) = explode(":",$totalot);
						$totalinsecs 	   		 = ($hour*3600) + ($mins*60);
						
						$totalcreditswithx 		 = ($totalinsecs*1);
					} else if ( $a[$n]->mult == "1.5" ) {
						// $times15hh = explode(":",$totalot)[1];
						// $times15mm = explode(":",$totalot)[2];
						$times15hh	  = date("h", strtotime($totalot));
						$times15mm 	  = date("i", strtotime($totalot));
						
						list($day, $hour, $mins) 	= explode(":",$totalot);
						$totalinsecs 	   			= ($hour*3600) + ($mins*60);
						
						$totalcreditswithx 			= ($totalinsecs*1.5);
					}
				
					$remaining 				 	= $this->consec($this->returntotimeformat($totalcreditswithx)) + $remaining;
					
					$vars['day']				= $a[$n]->date_of_application;
					$vars['amin']				= $a[$n]->am_in;
					$vars['amout']				= $a[$n]->am_out;
					$vars['pmin']				= $a[$n]->pm_in;
					$vars['pmout']				= $a[$n]->pm_out;
					$vars['totalovertime']		= $totalot;
					$vars['creditsx1hh']		= $times1hh;
					$vars['creditsx1mm']		= $times1mm;
					$vars['creditsx15xhh']		= $times15hh;
					$vars['creditsx15xmm']		= $times15mm;
					
					$vars['totalcreditswithx']	= $this->returntotimeformat($totalcreditswithx);	
					
				} else if ($a[$n]->credit_type == "CTO") {
					$usedcochr 					= $a[$n]->cto_hours;
					$usedcocmm					= $a[$n]->cto_mins;
					
					$vars['daycto']				= $a[$n]->date_of_application;
					$vars['usedcochr']			= $usedcochr;
					$vars['usedcocmm']			= $usedcocmm;
					
					$usedcocmm					= (strlen($usedcocmm)==1)?"00":$usedcocmm;
					$timeinsec 					= $usedcochr.":".$usedcocmm;
					 
					$timeinsec  				= $this->consec($timeinsec);
					 
					$remaining					= ($remaining - $timeinsec);
				}
				
				$vars['otctoid']   = $a[$n]->elc_otcto_id;
				
				$vars['remaining'] = $this->standard_time($remaining);
				
				array_push($data['ctodata'],$vars);
			}
			return $data;
		}
		
	}
