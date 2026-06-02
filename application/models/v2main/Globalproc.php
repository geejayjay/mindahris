<?php 
	Class Globalproc extends CI_Model {		
		public function __construct() {
			parent::__construct();
			$session_database = $this->session->userdata('database_default');
			$this->load->database($session_database, TRUE);
		}
		
		public function __save($table, $values) {
			// $a = $this->load->database();
			$session_database = $this->session->userdata('database_default');
			$this->load->database($session_database, TRUE);
			
			$sql = null;
			if (is_array($values)) {
				$sql = "insert into {$table} (";
				$count = 0;
				foreach (array_keys($values) as $ks) {
					$sql .= $ks;
					$sql .= ($count == count($values)-1) ? "" : ", ";
					$count++;
				}
				$sql .= ") values (";

				$count = 0;
				foreach($values as $key => $vals) {
					//$sql .= "'".$vals."'";
					$sql .= $this->db->escape($vals);
					$sql .= ($count == count($values)-1)? "": ",";
					$count++;
				}
				$sql .= ")";
			}
		// return $sql;
			$ret = $this->db->query($sql);
			$this->db->close();
			return $ret;
		}

		public function run_sql($sql) {
			$session_database = $this->session->userdata('database_default');
			$this->load->database($session_database, TRUE);
			
			$ret = $this->db->query($sql);
			$this->db->close();
			return $ret;
		}
		
		public function __update($table, $values, $where) {
			// $this->load->database();
			$session_database = $this->session->userdata('database_default');
			$this->load->database($session_database, TRUE);
			
			$sql = null;

			if (is_array($values)) {
				$count = 0;
				$sql = "update {$table} set ";

				foreach($values as $key => $vals) {
					$sql .= $key."='".$vals."'";
					$sql .= ($count==count($values)-1)?"":", ";
					$count++;
				}

				$count = 0;
				// conn = and 
				// conn = or
				if (is_array($where)){
					$sql .= " where ";
					foreach($where as $key => $val) {
						if ($key == "conn") {
							$sql .= " ".$val." ";
						} else {
							$sql .= $key."='".$val."'";	
						}
					}
				}
				
				$ret = $this->db->query($sql);
				$this->db->close();
				return $ret;
			}

			return false;

		}

		public function __getdata($sql) { //, $table = false
			// $this->load->database();
	
			$ret = null;

			if (!is_array($sql)) {
				@$ret = $this->db->query($sql);
			/*
				if( !$ret )
				{
				   return false;
				   // Do something with the error message or just show_404();
				}
			*/
			} else {
				// write a query for values sent in array
				// for the meantime run code in sql form
			}

			$this->db->close();
			return $ret->result();
		}

		public function getrecentsavedrecord($table, $callback) {
			$this->load->database();
			$sql = "SELECT IDENT_CURRENT('".$table."') as ".$callback;  
			$ret = $this->db->query($sql)->result();
			$this->db->close();
			return $ret;
		}


		public function __checkindb($table, $field, $value) {
			$this->load->database();

			$sql = "select * from {$table} where {$field}='{$value}'";
			$ret = $this->db->query($sql);

			$this->db->close();
			
			if (count($ret)-1 >= 1) {
				return true;
			} else {
				return false;
			}
		}

		public function __createuniqueid($word) {
			// md5 userid
			// date and time today
			return (substr(md5($word),0,6)."-".substr(md5(date("ldY:hisa")),0,6));
		}

		public function __datetoday() {
			return date("l, F d, Y");
		}
		
		public function thedate() {
			return date("F d, Y");
		}
		
		public function monthcover() {
			return date("M Y");
		}

		public function merge_new_values($default, $new) {
			foreach($new as $key => $vals) {
				$default[$key] = $vals;
			}

			return $default;
		}

		public function get_empdetails($details) {
			$this->load->model("Globalvars");

			$loggedid = $this->Globalvars->employeeid;

			$sql = "select * from employees where employee_id = '{$loggedid}'";
			$var = $this->__getdata($sql);

			$ret_vars = [];

			foreach($details as $det_key) {
				$ret_vars[$det_key] = $var[0]->$det_key;
			}

			return $ret_vars;
		}

		public function get_details_from_table($table, $where, $details) {
/*
			$this->load->model("Globalvars");
			$loggedid = $this->Globalvars->employeeid;
*/
			$sql = "select * from {$table} "; // "where employee_id = '{$loggedid}'";

			if (is_array($where)) {
				$sql .= " where ";
				
				foreach($where as $key => $val) {
					if ($key == "conn") {
						$sql .= " ".$val." ";
					} else {
						$sql .= $key."='".$val."'";
					}
				}

			} else {
				$sql .= " ".$where." ";
			}

			$var = $this->__getdata($sql);
			
			if (count($var) == 0) {
				return 0;
			}
			
			$ret_vars = [];

			foreach($details as $det_key) {
				$ret_vars[$det_key] = $var[0]->$det_key;
			}

			return $ret_vars;
			
		}
		
		public function gdtf($table, $where, $details, $orderby = false) {
			$sql = "SELECT ";
			
			if (is_array($details)){
				$count = 0;
				foreach($details as $d) {
					$sql .= $d;
					$sql .= (count($details)-1==$count)?"":",";
					$count++;
				}
			} else {
				$sql .= $details;
			}
			
			$sql .= " FROM ".$table;
			$sql .= " WHERE ";

			if (is_array($where)){
				$count = 0;
				foreach($where as $key => $val) {
					if ($key == "conn") {
						$sql .= " ".$val." ";
					} else {
						$sql .= $key."='".$val."'";
					}
				}
			} else {
				$sql .= " ".$where." ";
			}	
			
			if ($orderby != false) {
				$sql .= $orderby;
			}
			
			$var = $this->__getdata($sql);
			
			return $var;
		}

		public function get_leave_dbid($leavecode) {
			$sql = "select * from leaves where leave_code = '{$leavecode}'";

			$a   = $this->__getdata($sql);

			return $a[0]->leave_id;

		}

		public function compute_time_interval($from,$to) {
			// from 3:00 PM
			// to 6:00 PM
			$hrs  = "";
			$mins = "";
			$ampm = ""; // can be am or pm or both

			$interval = strtotime($from)-strtotime($to);
			return $interval/60/60;
		}
		
		public function sendmail($details) {
			$headers = 'From: '.$details['from']."\r\n".
   					   'Reply-To: '.$details['from']."\r\n" .
					   'X-Mailer: PHP/' . phpversion();
					
			mail($details['to'], $details['subject'], $details['message'], $headers);
		}
		
		public function sendtoemail($details){
			/*
			$to 	 = $details['to'];
			$subject = $details['subject'];
			$message = $details['message'];
			
			$headers = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
			$headers .= "From: ".$details["from"]. "\r\n";
			
			if (isset($details['cc'])) {
				$headers .= "Cc: ".$details["cc"]. "\r\n";
			}
			
			return mail($to, $subject, $message, $headers);
			
			// smtp email sender un: webmaster@minda.gov.ph :: GHTY%^rueiwoqp
									 minda.smtpsender@gmail.com :: ghty56rueiwoqp
			// 
			
			*/
			
			$config = Array(
				'protocol' => 'smtp',
				'smtp_host' => 'ssl://smtp.gmail.com',
				'smtp_port' => 465,
				'smtp_user' => 'no-reply@minda.gov.ph',
				'smtp_pass' => 'ridvgnjhqwhwsdlm',
				'mailtype'  => 'html', 
				'charset'   => 'iso-8859-1'
			);
			
			$this->load->library('email', $config);
			$this->email->set_newline("\r\n");
			
			$this->email->from('no-reply@minda.gov.ph', strtoupper($details["from"]));
			$this->email->to($details['to']);
			$this->email->subject($details['subject']);
			
			if (isset($details['cc'])){
				$this->email->cc($details['cc']); //.",webmaster@minda.gov.ph"
			}
			
			$this->email->message($details['message']);
			
			return $this->email->send();
			
			/*
			if (!$r){
				echo "jklsjdf";
			  $this->email->print_debugger();
			}
			
			return $r;
			*/
		}
		
		public function tokenizer_leave($exactid) {
			return substr(md5(substr(md5($exactid),0,11)),0,11);
		}
		
		public function check_leavecredits($empid,$return = false) {
			// check for no entry
			$elc = $this->gdtf("employee_leave_credits",["employee_id"=>$empid],["elc_id","vl_value","sl_value"]);
			
			if (count($elc)==0) {
				return;
			} else {
				$elc_latest = $this->gdtf("employee_leave_credits",
										  "elc_id = (select max(elc_id) from employee_leave_credits where employee_id = '{$empid}')",
									      "*");
				if ($return==true) {
					return $elc_latest;
				} else {
					return true;
				}
			}
			
			return false;
		}
		
		public function convert($value, $type) {
			
			$conversion = $this->gdtf("hours_minutes_conversation_fractions",["particular"=>$value,"conn"=>"and","type"=>$type],["equi_day"]);
			
			return $conversion[0]->equi_day;
		}
		
		public function calc_leavecredits($empid, $exactid, $type_details) {
			
		$flag = false;		
		/*
		type_details
			typemode
			leave_value 
			value 								// not in use
			date_inclusion
			no_days_applied
			hrs
			mins
		end type details
		*/

		//	$empid = 50;
			// ============================================================================================================================
			// DEDUCTIONS	
				// leave credits
				if ( strtoupper($type_details['typemode']) != "CTO") {
					$leavecredits = $this->gdtf("employee_leave_credits",
													"elc_id = (select max(elc_id) from employee_leave_credits where employee_id = '{$empid}')",
													"*");
					
					if (count($leavecredits)==0) {
						// return NULL if nothing is found in the database
						$leavecredits[] = (object) ["vl_value"	 => 0,
													"fl_value"	 => 0,
													"sl_value"	 => 0,
													"coc_value"	 => 0,
													"spl_value"  => 0
												    ];
					
					} 
					$details = [
							"employee_id" 		=> $empid,
							"vl_value" 			=> $leavecredits[0]->vl_value,
							"fl_value"			=> $leavecredits[0]->fl_value,
							"sl_value"			=> $leavecredits[0]->sl_value,
							"spl_value"			=> $leavecredits[0]->spl_value,
							"coc_value"			=> $leavecredits[0]->coc_value,
							"credits_as_of"		=> $this->thedate(),
							"is_beggining"		=> 1,
							"is_current"		=> 1,
							"exact_id"			=> $exactid,
							"vl_earned"			=> 0,
							"sl_earned"			=> 0,
							"elc_type"			=> $type_details['typemode'],
							"hrs"				=> 0,
							"mins"				=> 0,
							"withpay"			=> 0,
							"wopay"				=> 0,
							"formonet"			=> $type_details['formonet']
						];
				}
				// conversion 
				
				// LEAVE ======================================================================================
					if ( strtoupper($type_details['typemode']) == "LEAVE") {
						// sick leave
						$subtrahend = null;
						if ($type_details['leave_value'] == 1) {
							$subtrahend = $type_details['no_days_applied'];
							
							if (isset($type_details['hrs']) && !empty($type_details['hrs'])) {
								$subtrahend 		= $this->convert($type_details['hrs'], "h");
								$details['hrs']		= $type_details['hrs'];
							}
							
							if (isset($type_details['mins']) && !empty($type_details['mins'])) {
								$subtrahend 		+= $this->convert($type_details['mins'], "m");
								$details['mins']	= $type_details['mins'];
							}
						
							if (isset($type_details['value']) && $type_details['value'] == "declined") {
								$details["withpay"] = 0;
								$details["wopay"]   = $subtrahend;
							} else {
								// for days	
								if ($leavecredits[0]->sl_value < $subtrahend){
									if ($leavecredits[0]->sl_value <= 0) {
										$details["withpay"] = 0;
										$details["wopay"]   = $subtrahend;
									} else {
										$details["withpay"] = $leavecredits[0]->sl_value;
										$details["wopay"]   = $subtrahend - $leavecredits[0]->sl_value;
									}
									$details['sl_value'] = 0;
								} else if ($leavecredits[0]->sl_value == $subtrahend){
									$details["withpay"] = $leavecredits[0]->sl_value;
									$details["wopay"]   = 0;
									
									$details['sl_value'] = $leavecredits[0]->sl_value - $subtrahend;
								} else if ($leavecredits[0]->sl_value > $subtrahend){
									$details["withpay"] = $subtrahend;
									$details["wopay"]   = 0;
									
									$details['sl_value'] = $leavecredits[0]->sl_value - $subtrahend;
								} 
								// end for days

								/*
								$prev_sl 			 = $leavecredits[0]->sl_value;
								$details['sl_value'] = $prev_sl - $type_details['no_days_applied'];
								*/
							}
							/*
							rem_vl 			= 0;
							rem_sl			= $leavecredits[0]->sl_value
							rem_coc			= 0;
							rem_tot			= $leavecredits[0]->sl_value
							less_vl			= 0;
							less_sl			= $subtrahend;
							less_coc		= 0;
							less_tot		= $subtrahend;
							grp_id			= $exactid
							typeofpink		= "sl"
							*/
						}
						
						// vacation leave
						else if ( $type_details['leave_value'] == 2) {
							if (isset($type_details['value']) && $type_details['value'] == "declined") {
								$details["withpay"] = 0;
								$details["wopay"]   = $type_details['no_days_applied'];
							} else {
								if ($leavecredits[0]->vl_value < $type_details['no_days_applied']){
									if ($leavecredits[0]->vl_value <= 0) {
										$details["withpay"] = 0;
										$details["wopay"]   = $type_details['no_days_applied'];
									} else {
										$details["withpay"] = $leavecredits[0]->vl_value;
										$details["wopay"]   = $type_details['no_days_applied'] - $leavecredits[0]->vl_value;
									}
									$details['vl_value'] = 0;
								} else if ($leavecredits[0]->vl_value == $type_details['no_days_applied']){
									$details["withpay"] = $leavecredits[0]->vl_value;
									$details["wopay"]   = 0;
									
									$details['vl_value'] = $leavecredits[0]->vl_value - $type_details['no_days_applied'];
								} else if ($leavecredits[0]->vl_value > $type_details['no_days_applied']){
									$details["withpay"] = $type_details['no_days_applied'];
									$details["wopay"]   = 0;
									
									$details['vl_value'] = $leavecredits[0]->vl_value - $type_details['no_days_applied'];
								}
							}
							
							/*
							rem_vl 			= $leavecredits[0]->vl_value;
							rem_sl			= 0;
							rem_coc			= 0;
							rem_tot			= $leavecredits[0]->vl_value
							less_vl			= 0;
							less_sl			= $type_details['no_days_applied'];
							less_coc		= 0;
							less_tot		= $type_details['no_days_applied'];
							grp_id			= $exactid
							typeofpink		= "vl"
							*/
							
						}
						
					// SPL ============================================================================================
						else if ( strtoupper($type_details['typemode']) == "SPL" || $type_details['leave_value'] == 4) {
							// data for SPL here
							
							$details['spl_value']	  = $leavecredits[0]->spl_value - $type_details['no_days_applied']; // 1
							//$details['credits_as_of'] = $type_details['date_inclusion'];
						}
					// SPL ============================================================================================
					
					// FL :: forced leave =============================================================================
						else if ( strtoupper($type_details['typemode']) == "FL" || $type_details['typemode'] == "fl" || $type_details['leave_value'] == 6) {
							// data for FL here
							
							$details['fl_value']	  = $leavecredits[0]->fl_value - $type_details['no_days_applied'];
							//$details['credits_as_of'] = $type_details['date_inclusion'];
						} else {
							if (isset($type_details['value']) && $type_details['value'] == "declined") {
								$details["withpay"] = 0;
								$details["wopay"]   = $type_details['no_days_applied'];
							}
						}
					// end FL ============================================================================================
						$details['credits_as_of'] = $type_details['date_inclusion'];
						
					}
				// END LEAVE ======================================================================================
				
				
				
				// PS =============================================================================================
					if (strtoupper($type_details['typemode']) == "PS" 
						|| strtoupper($type_details['typemode']) == "T" 
							|| strtoupper($type_details['typemode']) == "UT") {
						// leave_value
						$details['hrs']			  = $type_details['hrs'];
						$details['mins']		  = $type_details['mins'];
						$details['credits_as_of'] = $type_details['date_inclusion'];
						
						$converted_hrs  = ($type_details['hrs'] == 0 || $type_details['hrs'] == null)?0:$this->convert($type_details['hrs'],"h");
						$converted_mins = ($type_details['mins'] == 0 || $type_details['mins'] == null)?0:$this->convert($type_details['mins'],"m");
							
						$total_hrs_mins = $converted_hrs + $converted_mins;
						
						$details["withpay"] = 0;
						$details["wopay"]   = 0;
						
						if ($type_details['leave_value'] == 1) { // official
							// record the PS but no deductions
							$details['wopay'] 	 = 0;
							$details['withpay']	 = $total_hrs_mins;
						} else if ($type_details['leave_value'] == 2 
							|| strtoupper($type_details['typemode']) == "T"
								|| strtoupper($type_details['typemode']) == "UT") { // personal, tardiness and undertime
							if ($leavecredits[0]->vl_value < $total_hrs_mins) {
								if ($leavecredits[0]->vl_value <= 0) {
									$details['wopay'] 	 = $total_hrs_mins;
									$details['withpay']	 = 0;
									$details['vl_value'] = 0;
								} else {
									$details['wopay'] 	 = $total_hrs_mins - $leavecredits[0]->vl_value;
									$details['withpay']	 = $leavecredits[0]->vl_value;
									$details['vl_value'] = 0;
								}
							} else if ($leavecredits[0]->vl_value == $total_hrs_mins) {
								$details['vl_value'] = 0;
								$details['wopay'] 	 = $total_hrs_mins;
								$details['withpay']	 = 0;
							} else if ($leavecredits[0]->vl_value > $total_hrs_mins) {
								$details['vl_value'] = $leavecredits[0]->vl_value - $total_hrs_mins;
								$details['wopay'] 	 = 0;
								$details['withpay']	 = $total_hrs_mins;
							}
						}
					}
				// end PS ==========================================================================================
							
				// OB ===================================================================================================
					$details['credits_as_of'] = $type_details['date_inclusion'];
				// end OB ===============================================================================================
				
				// PAF ========================================================================
					if ( strtoupper($type_details['typemode']) == "PAF" ) {
						$details['hrs']		= $type_details['hrs'];
					}
				// end PAF ====================================================================
				
				// mark cto global
				// cto =========================================================================
					if ($type_details['typemode'] == "CTO" || $type_details['typemode'] == "cto") {
						// adding of OT to OT database :: employee_ot_credits						
						
						// ** important
							// add CTO deductions
						// end 
						
					//	$start_time  = new DateTime('10:00 AM');
					//	$end_time    = new DateTime('3:00 PM');
					
					/*
					$amin  = "00:00";
					$amout = "00:00";
					// --------- //
					$pmin  = $cto[0]->time_in;
					$pmout = $cto[0]->time_out;
					
					// 
						$time1 = null;
						$time2 = null;
					// 
					
					if( date("A",strtotime($type_details["hrs_start"])) == "AM" ) {
						$amin  = $type_details["hrs_start"];
					
						if ( date("A",strtotime($type_details["hrs_end"])) == "AM" ) {
							$amout = $cto[0]->time_out;
						} else if ( date("A",strtotime($type_details["hrs_end"])) == "PM" ) {
							$amout = "12:00 PM";
							
							// pm 
								$pmin  = "1:00 PM";
								// $pmout = default;
							// end 
						}
		
					}
					*/
						
						$start_time  = new DateTime($type_details["hrs_start"]);
						$end_time    = new DateTime($type_details["hrs_end"]);
						$interval    = $start_time->diff($end_time);
						$cto_hours 	 = $interval->format('%h');
						$cto_mins    = $interval->format('%i');
						
						if( date("A",strtotime($type_details["hrs_start"])) == "AM" && date("A",strtotime($type_details["hrs_end"])) == "PM") {
							$cto_hours -= 1;
						}
						
						// list($h, $m) = explode(':', $type_details['cred_total']);
						$seconds = ($cto_hours * 3600) + ($cto_mins * 60);
						
						// $total_hours = $cto_hours.".".$cto_mins;
						$total_hours = $seconds;
						
						$sql = "select * from employee_ot_credits 
								where elc_otcto_id = (select max(elc_otcto_id) 
													  from employee_ot_credits as et 
													  where emp_id = '{$type_details['empid']}')";
						
						$ddata 		 = $this->Globalproc->__getdata($sql);
						
						if (count($ddata) >= 1) {
							$totalcredit = $ddata[0]->total_credit;
						} else {
							$totalcredit = strtotime("00:00");
						}
						
						$grandtotal  = $totalcredit - $total_hours;
						
						$ot_details = [
							"date_of_application"	=> $type_details['date_inclusion'],
							"total"					=> $total_hours,
							"cred_total"			=> $totalcredit,
							"total_credit"			=> "{$grandtotal}",
							"emp_id"				=> $type_details['empid'],
							"cto_hours"				=> $cto_hours,
							"cto_mins"				=> $cto_mins,
							"cto_start"				=> $type_details['hrs_start'],
							"cto_end"				=> $type_details['hrs_end'],
							"credit_type"			=> "CTO",
							"exact_ot"				=> $type_details['exact_ot']
						];
						
						/*
							rem_vl 			= 0;
							rem_sl			= 0;
							rem_coc			= $ddata[0]->total_credit;
							rem_tot			= $ddata[0]->total_credit;
							less_vl			= 0;
							less_sl			= 0;
							less_coc		= $ddata[0]->total_credit;
							less_tot		= $type_details['no_days_applied'];
							grp_id			= $exactid
							typeofpink		= "coc"
						*/
						
						$flag = $this->__save("employee_ot_credits",$ot_details);
						
						if ($flag) {
							return true;
						}
						return false;
					}
				// end of cto ==================================================================
				
			// END DEDUCTIONS
			// =====================================================================================================
			$flag = $this->__save("employee_leave_credits",$details);
			return $flag;
		}
		
		function saveto_ot($values) {
			
			/*
			$values = [
				"am_in" 			=> "10:00 AM",
				"am_out"			=> "12:00 PM",
				"calc_elc"			=> true,
				"dates"				=> ["2/18/2018"],
				"dbm_chief_id"		=> 0,
				"division_chief_id"	=> 0,
				"empid"				=> 62,
				"isam"				=> true,
				"ispm"				=> true,
				"mult"				=> 1.5,
				"pm_in"				=> "1:00 PM",
				"pm_out"			=> "5:00 PM",
				"remarks_ot"		=> "",
				"tasktobedone"		=> "",
				"tasktype"			=> null,
				"timein"			=> "",
				"timeout"			=> "",
				"typemode"			=> "OT"
			];
			*/
			
			$type_details = [
				"date_of_application" 	=> date("m/d/Y", strtotime($values['dates'][0])),
				"am_in"					=> null,
				"am_out"				=> null,
				"pm_in"					=> null,
				"pm_out"				=> null,
				"total"					=> null,
				"cred_total"			=> null,
				"mult"					=> $values['mult'],
				"total_credit"			=> null,
				"emp_id"				=> $values['empid'],
				"credit_type"			=> "OT",
				"exact_ot"				=> $values['exact_ot']
			];
	
			$am_total_hours = "00:00";
			$pm_total_hours = "00:00";
				
			$tot_am_secs 	= "0";
			$tot_pm_secs 	= "0";
			
			if (isset($values['isam'])) {
				$type_details["am_in"]  = $am_in  = $values['am_in'];
				$type_details["am_out"] = $am_out = $values['am_out'];
					
				$datetime1 = new DateTime($am_in);
				$datetime2 = new DateTime($am_out);
				$interval = $datetime1->diff($datetime2);
					
				$am_total_hours = $interval->format("%h").":".$interval->format("%i");
				
				// total AM hours in seconds
					$tot_am_secs = ($interval->format("%h") * 3600) + ($interval->format("%i") * 60);
			}
				
			if (isset($values['ispm'])) {
				$type_details["pm_in"]  = $pm_in  = $values['pm_in'];
				$type_details["pm_out"] = $pm_out = $values['pm_out'];
					
				$datetime1 = new DateTime($pm_in);
				$datetime2 = new DateTime($pm_out);
				$interval = $datetime1->diff($datetime2);
					
				$pm_total_hours = $interval->format("%h").":".$interval->format("%i");
				
				// total PM hours in seconds 
					$tot_pm_secs = ($interval->format("%h") * 3600) + ( $interval->format("%i")*60 );
			}
			
			$totalcreds_ot 	= $tot_am_secs + $tot_pm_secs;
		
			/*
			// mark here OT
			$seconds = ($cto_hours * 3600) + ($cto_mins * 60);
		
			// $total_hours = $cto_hours.".".$cto_mins;
			$total_hours = $seconds;
			*/
			
			$secs        = strtotime($pm_total_hours)-strtotime("00:00:00");
			$result      = date("H:i",strtotime($am_total_hours)+$secs);
	
			$result_hour = date("H",strtotime($am_total_hours)+$secs);
			$result_mins = date("i",strtotime($am_total_hours)+$secs);
			
			// result in minutes convert it into hours
				// mins divided by 60 = hours
				// then add the hours and the converted minutes
				// multiply it by either 1.5 or 1
					// if the answer has decimal : a_1
					// get the decimal and multiply it by 60
					// append the hour into the a_1 
					// equals converted hour
			
			// not converted hour into days :: unit used is in hour
				$total_hours_in_days 		   = $result_hour.".".$result_mins;
				$type_details['total'] 	       = $result;			// no_days_applied
				
				$mins_ 						   = $result_mins/60;
				$total_hrs_mins 			   = ($result_hour+$mins_);
				$cred_total					   = $total_hrs_mins * $type_details['mult'];
				
				/*
				echo $total_hrs_mins; echo "<br/>";
				echo "x <br/> ";
				echo $type_details['mult'];
				echo $cred_total;
				*/
				
				if(floor($cred_total) == $cred_total) {
					$cred_hrs  = $cred_total;
					$cred_mins = "00";
				} else {
					list($cred_hrs, $cred_mins)   = explode(".",$cred_total);
				
					// $cred_mins 				  = round(($cred_mins*60),2);
					// $cred_mins 				  = $cred_mins*60;
					// $cred_mins 				  = round(".".$cred_mins,2);
					$cred_mins 				      = $result_mins;
				}
				
				$type_details['cred_total']	      = $cred_hrs.":".$cred_mins;
				// $type_details['cred_total']   = $total_hours_in_days * $type_details['mult'];
				
				$sql = "select * from employee_ot_credits 
						where elc_otcto_id = (select max(elc_otcto_id) 
											  from employee_ot_credits as et 
											  where emp_id = '{$values['empid']}')";
				
				$rem = $this->__getdata($sql);
				
				list($h, $m) = explode(':', $type_details['cred_total']);
				$seconds = ($h * 3600) + ($m * 60);
				// days  = 86400
				
				$type_details['total_credit'] 	  = $seconds;
				
				$totalcredit = $rem[0]->total_credit + $seconds;
				if (count($rem) != 0){
					$type_details['total_credit'] = "{$totalcredit}";
				}
				
				// $type_details['total_credit'] = $cred_mins;
			// end 
			
			$save = $this->__save("employee_ot_credits",$type_details);
			
			return $save;
			
		}
		
		function getleaves() {
		//	$this->load->model("v2main/Globalproc");
			
			$ret = $this->__getdata("SELECT * from leaves");
			
			$data = [];
			for ($i = 0; $i <= count($ret)-1 ; $i++) {
				$data[$ret[$i]->leave_id] = $ret[$i]->leave_name;
			}
			
			return $data;
			
			//echo json_encode($ret[0]->leave_name);
			//echo json_encode($ret[0]->leave_id);
			
		}
		
		function get_spl_count($empid) {
			/*
			$sql = "select * from checkexact as ce
					JOIN checkexact_leave_logs as cll on ce.exact_id = cll.exact_id
					where ce.employee_id ='{$empid}' and ce.leave_id = 4 and is_approved = 1"; // 
			*/
			
			// $empid 	   = 62;
			$this_year = date("Y");
			/*
			$sql = "select * from checkexact_leave_logs as cll 
					JOIN checkexact as ce ON cll.grp_id = ce.grp_id 
					JOIN employees as e on ce.employee_id = e.employee_id
					where e.employee_id = '{$empid}' and ce.leave_id = 4 and ce.is_approved = 1";
					
					distinct(tb1.grp_id), 
			*/		
			$sql = "select 
						tb1.grp_id,
						YEAR (tb1.checkdate) as year
					from 
						(select 
							distinct(cll.grp_id),
							ce.checkdate
						from checkexact_leave_logs as cll 
							JOIN checkexact as ce ON cll.grp_id = ce.grp_id 
							JOIN employees as e on ce.employee_id = e.employee_id
						where e.employee_id = '{$empid}' 
						and ce.leave_id = 4 
						and ce.is_approved = 1)
						as tb1
					where YEAR( tb1.checkdate ) = '{$this_year}'";
				
			$spl = $this->__getdata($sql);
			
			/*
			$spl_count = 0;
			if ( count($spl) != 0 ) {
				$grp_id = 0;
				$this_year = date("Y");
				foreach($spl as $s) {
					$grp_id = $s->grp_id;;
					$year_app = date("Y", strtotime($s->checkdate));
					if ($year_app == $this_year && $s->grp_id != $grp_id) {
						$spl_count++;
					}
				}
			}
			
			return $spl_count;
			*/
			
			return count($spl);
		}
		
		function getvl_count($empid) {
			$sql = "select * from employee_leave_credits 
					where elc_id = (SELECT max(elc_id) from employee_leave_credits where employee_id = '{$empid}')";
			
			return $this->__getdata($sql);
		}
		
		function get_coc($empid) {	
			$sql = "select * from employee_ot_credits 
					where elc_otcto_id = (SELECT max(elc_otcto_id) from employee_ot_credits where emp_id = '{$empid}')";
			
			return $this->__getdata($sql);
		}
		
		function checkin_countersign($coverid, $empid) {
			$dtrsum_rep = $this->gdtf("dtr_summary_reports",["dtr_cover_id"=>$coverid,"conn"=>"and","employee_id"=>$empid],"sum_reports_id");
			
			if (count($dtrsum_rep) > 0) {
				return [$this->gdtf("countersign",["dtr_summary_rep"=>$dtrsum_rep[0]->sum_reports_id],'countersign_id'),$dtrsum_rep[0]->sum_reports_id];
			}
			return false; // not present in the dtr_summary_reports table:: meaning not submitted yet.
		}
		
		function check_prev_dtr($empid) {
			return $this->gdtf("countersign",['emp_id'=>$empid,"conn"=>"and","hrnotified"=>0],["approval_status","hrnotified","countersign_id"]);
		}
		
		function checkforactive_coverage($emp_type) {
			//return $this->gdtf("hr_dtr_coverage",['employment_type'=>$emp_type, "conn"=>"and", "is_active" => true],"*"); 
			return $this->__getdata("select * from hr_dtr_coverage where dtr_cover_id = (select max(dtr_cover_id) from hr_dtr_coverage where employment_type='{$emp_type}')");
		}
		
		function get_signatories($empid) {
			$this->load->model('main/main_model');
			
			$emp_dets 		= $this->gdtf("employees",["employee_id"=>$empid],["Division_id","DBM_Pap_id"]);
			$emp_div_id 	= $emp_dets[0]->Division_id;
			$emp_dbm_id     = $emp_dets[0]->DBM_Pap_id;
		
			// division
				$division 			    	= $this->main_model->array_utf8_encode_recursive( $this->gdtf("employees",
																										 ["Division_id" => $emp_div_id,
																										  "conn"	    => "and",
																										  "is_head"		=> 1],
																										 ["email_2",
																										  "f_name",
																										  "employee_id"])
																							);
				
				$division_chief_email 		= NULL; 
				$division_chief_fname 	 	= NULL; 
				$division_chief_id 			= NULL; 
				
				if ( count($division) > 0) {
					$division_chief_email 		= $division[0]->email_2; 
					$division_chief_fname 	 	= $division[0]->f_name;
					$division_chief_id 			= $division[0]->employee_id;
				}
				
			// end division
				
			// DBM 
				$the_dbm = $emp_dbm_id;
				
				/*
				if ($emp_dbm_id == 5) {
					$the_dbm = 3;
				}
				*/
				
				$sql			= "select email_2, employee_id, f_name 
								   from employees 
								   where DBM_Pap_id='{$the_dbm}' and 
								   Division_id = 0 and is_head = 1";
								   
				$dbm 			= $this->main_model->array_utf8_encode_recursive( $this->__getdata($sql) );
				
				$dbm_email 		= NULL;
				$dbm_fname 		= NULL;
				$dbm_id 		= NULL;
				
				if (count($dbm)>0) { 
					$dbm_email 		= $dbm[0]->email_2;
					$dbm_fname 		= $dbm[0]->f_name;
					$dbm_id 		= $dbm[0]->employee_id;
				}
				
				
			// end DBM
			
			
			// =========
				$division_sign  = [
					"div_name" 	=> $division_chief_fname,
					"div_email" => $division_chief_email,
					"div_empid" => $division_chief_id
					];
				$dbm_sign 	    = [
					"dbm_name"	=> $dbm_fname,
					"dbm_email"	=> $dbm_email,
					"dbm_empid"	=> $dbm_id
					];
				
				$div_other_sign = [];
				$dbm_other_sign = [];
			// =========
			
			// get the division emps 
				// $div_sql = $this->main_model->array_utf8_encode_recursive( $this->gdtf("employees",["Division_id"=>$emp_div_id,"conn"=>"and","status"=>1],["email_2","f_name","employee_id","employment_type"]) );
				$emps_sql = "select 
									f_name, 
									email_2, 
									employee_id,
									employment_type from employees 
								where status = 1 and employment_type = 'REGULAR'
								ORDER BY l_name ASC";
				$div_sql = $this->main_model->array_utf8_encode_recursive( $this->__getdata($emps_sql) );
				/*
				$div_sql = $this->main_model->array_utf8_encode_recursive( $this->gdtf("employees",
																						["employment_type"=>"REGULAR",
																						 "conn"=>"and",
																						 "status"=>1],
																						["email_2","f_name","employee_id","employment_type"]) );
				*/
					$count = 0;
					foreach($div_sql as $divs) {
						if ($divs->employment_type != "JO") {
							$div_other_sign[$count]["fname"]  = $divs->f_name;
							$div_other_sign[$count]["email"]  = $divs->email_2; // not really in use
							$div_other_sign[$count]["emp_id"] = $divs->employee_id;
							$count++;
						}
					}
				
				/*
					$div_other_sign[$count]["fname"]    = strtoupper("Secretary Datu HJ. Abul Khayr D. Alonto");
					$div_other_sign[$count]["email"]    = "ak.alonto@minda.gov.ph"; // not really in use
					$div_other_sign[$count]["emp_id"]   = 1443;
					
					$div_other_sign[$count+1]["fname"]  = strtoupper("Undersecretary Janet M. Lopoz");
					$div_other_sign[$count+1]["email"]  = "janet.lopoz@minda.gov.ph,janetlopoz@minda.gov.ph"; // might not really be in use
					$div_other_sign[$count+1]["emp_id"] = 1441;
				*/
			// end division emps
			
			// get the DBM signatories
				// $dbm_sql = $this->main_model->array_utf8_encode_recursive( $this->gdtf("employees",["DBM_Pap_id"=>$emp_dbm_id,"conn"=>"and","status"=>1],["email_2","f_name","employee_id","employment_type"]) );
				/*
				$dbm_sql_text = "select 
									f_name, 
									email_2, 
									employee_id,
									employment_type from employees 
								where status = 1 and employment_type = 'REGULAR'
								ORDER BY l_name ASC";
				
				$dbm_sql = $this->main_model->array_utf8_encode_recursive( $this->gdtf("employees",["employment_type"=>"REGULAR","conn"=>"and","status"=>1],["email_2","f_name","employee_id","employment_type"]) );
				*/
				$dbm_sql = $div_sql;
				
					$count_1 = 0;
					foreach($dbm_sql  as $ds) {
						if ($ds->employment_type != "JO") {	
							$dbm_other_sign[$count_1]["fname"]  = $ds->f_name;
							$dbm_other_sign[$count_1]["email"]  = $ds->email_2; // not really in use
							$dbm_other_sign[$count_1]["emp_id"] = $ds->employee_id;
							$count_1++;
						}
					}
			
			/*
				$dbm_other_sign[$count_1]["fname"]  = strtoupper("Secretary Datu HJ. Abul Khayr D. Alonto");
				$dbm_other_sign[$count_1]["email"]  = "ak.alonto@minda.gov.ph"; // not really in use
				$dbm_other_sign[$count_1]["emp_id"] = 1443;
				
				$dbm_other_sign[$count_1+1]["fname"]  = strtoupper("Undersecretary Janet M. Lopoz");
				$dbm_other_sign[$count_1+1]["email"]  = "janet.lopoz@minda.gov.ph,janetlopoz@minda.gov.ph"; // not really in use
				$dbm_other_sign[$count_1+1]["emp_id"] = 1441;
			*/
			// end DBM signatories
		
			return Array("division"		  => $division_sign,
						 "dbm" 	   		  => $dbm_sign,
						 "division_other" => $div_other_sign,
						 "dbm_other"	  => $dbm_other_sign);
			
		}
		
		public function get_signatories_forchiefs($level) {
			$this->load->model("Globalvars");
			$emp = $this->Globalvars->employeeid;
			if ($level == "division") {
				// get signatories for division chiefs
				$emp_des = $this->Globalproc->gdtf("employees",["employee_id"=>$emp],["Division_id","DBM_Pap_id"]);
				$sql 	 = "select * from employees where DBM_Pap_id = '{$emp_des[0]->DBM_Pap_id}' and Division_id = '0' and is_head = '1'";
				$app_	 = $this->Globalproc->__getdata($sql);
				var_dump($app_);
			} else if ($level == "director") {
				// get signatories for director level
			}
			
		}
		
		public function is_chief($level, $emp_id) {
			$sql = "select employee_id from employees where employee_id = '{$emp_id}'";
		
			switch($level) {
				case "division":
					$sql .= " and is_head = 1 and Division_id <> 0";
					break;
				case "director":
					$sql .= " and is_head = 1 and Division_id = 0";
					break;
			}
			$ret = $this->__getdata($sql);
			
			if (count($ret) == 0){
				return false;
			}
			return true;
		}
		
		public function allow_auto_login($details) {
			/*
			$username = $details['username'];
			$password = $details['password'];
			
			$info["username"] = $this->uri->segment(5);
			$info['password'] = $this->uri->segment(4);
			*/
			
			$a = $this->Login_model->authorizeUser($details);
		
			$emp_id   = $a[0]->employee_id;
			$username = $a[0]->username;
			$usertype = $a[0]->usertype;
			
			$result2 = $this->Login_model->getUserInformation($a[0]->employee_id);

			$user_session = array(
				'employee_id' => $emp_id,
				'username' => $username,
				'usertype' => $usertype,

				//'first_name' => $result2[0]->first_name,
				'full_name' => $result2[0]->f_name,
				'first_name' => $result2[0]->firstname,
				'last_name' => $result2[0]->l_name,
				'biometric_id' => $result2[0]->biometric_id,
				//'user_email' => $result2[0]->email,
				//'user_position' => $result2[0]->position_name,
				'area_id' => $result2[0]->area_id,
				'area_name' => $result2[0]->area_name,
			  
				//'display_name' => $result2[0]->display_name,
				'ip_address' => $_SERVER["REMOTE_ADDR"],
				//'theme_color' => $result2[0]->theme_color,
				//'bg_image' => $result2[0]->bg_image,
				//'font_color' => $result2[0]->font_color,
				//'ticket_log_order_start_on' => $result2[0]->ticket_log_order_start_on,
				'is_logged_in' => TRUE,
				'database_default' => 'sqlserver',
				'employment_type' => $result2[0]->employment_type,
				'employee_image' => $result2[0]->employee_image,
				'level_sub_pap_div' => $result2[0]->Level_sub_pap_div,
				'division_id' => $result2[0]->Division_id,
				'dbm_sub_pap_id' => $result2[0]->DBM_Pap_id,
				'is_head' => $result2[0]->is_head,
				'office_division_name' => $result2[0]->office_division_name,
				'position_name' => $result2[0]->position_name

				//'profile_picture' => $result2[0]->image_path
			);
			
			return $this->session->set_userdata($user_session);
		}
		
		public function return_remaining( $remainingwhat, $empid ) {
			$rem_ 		= null;
			$fld_value  = null;
			
			$iscoc = false;
			switch($remainingwhat) {
				case "VL":
					$rem_ = "vl_value";
					$fld_value = 2;
					break;
				case "SL":
					$rem_ = "sl_value";
					$fld_value = 1;
					break;
				case "SPL":
					$rem_ = "spl_value";
					$fld_value = 4;
					break;
				case "FL":
					$rem_ = "fl_value";
					$fld_value = 6;
					break;
				case "COC":
					$iscoc = true;
					$rem_ = "total_credit";
					break;
			}
			
			$sql = null;
			
			if ($iscoc == false) {
			$sql = "select {$rem_} 
					from employee_leave_credits
					where 
					elc_id = (select max(elc_id) 
							  from employee_leave_credits as elc
							  where elc.employee_id = '{$empid}')";
			} else {
				$sql = "select total_credit 
						from employee_ot_credits 
						where elc_otcto_id = (select max(elc_otcto_id) from employee_ot_credits as elc where elc.emp_id = '{$empid}')";
			}
			
					/*
					// join the checkexact and check what kind of leave it is.
					*/
					
					
		//	echo $sql;
			$ret = $this->__getdata($sql);
			
			return (count($ret)<=0)?0:$ret[0]->$rem_;
		}
		
		public function getcredrec($exactid,$empid,$getwhat) {
			$ret = $this->gdtf("creditrecords",
							  ['exact_id'=>$exactid, "conn"=>"and",'empid'=>$empid],
							   "{$getwhat}");
			if (count($ret)==0) {
				return false;
			}
			return $ret[0]->$getwhat;
		}
		
		public function allow_no_password($user_id) {
			
			$result2      = $this->Login_model->getUserInformation($user_id);
			
			$user_session = array(
				'employee_id' => $emp_id,
				'username' => $username,
				'usertype' => $usertype,

				//'first_name' => $result2[0]->first_name,
				'full_name' => $result2[0]->f_name,
				'first_name' => $result2[0]->firstname,
				'last_name' => $result2[0]->l_name,
				'biometric_id' => $result2[0]->biometric_id,
				//'user_email' => $result2[0]->email,
				//'user_position' => $result2[0]->position_name,
				'area_id' => $result2[0]->area_id,
				'area_name' => $result2[0]->area_name,
			  
				//'display_name' => $result2[0]->display_name,
				'ip_address' => $_SERVER["REMOTE_ADDR"],
				//'theme_color' => $result2[0]->theme_color,
				//'bg_image' => $result2[0]->bg_image,
				//'font_color' => $result2[0]->font_color,
				//'ticket_log_order_start_on' => $result2[0]->ticket_log_order_start_on,
				'is_logged_in' => TRUE,
				'database_default' => 'sqlserver',
				'employment_type' => $result2[0]->employment_type,
				'employee_image' => $result2[0]->employee_image,
				'level_sub_pap_div' => $result2[0]->Level_sub_pap_div,
				'division_id' => $result2[0]->Division_id,
				'dbm_sub_pap_id' => $result2[0]->DBM_Pap_id,
				'is_head' => $result2[0]->is_head,
				'office_division_name' => $result2[0]->office_division_name,
				'position_name' => $result2[0]->position_name

				//'profile_picture' => $result2[0]->image_path
			);
			
			return $this->session->set_userdata($user_session);
			
		}
		
		public function returndtrformat($f_name,$coverage,$vercode,$uname,$pword,$bodycode, $accom_view, $approvedby) {
			
			$m = "<div style='width:100%; background:#ededed; padding: 18px 0px; font-size: 15px;'>
						<div style='width: 85%; margin: auto; border: 1px solid #9c9c9c; background: #fff; border-radius: 2px; font-family: arial; box-shadow: 0px 0px 4px #9e9e9e;'>
							<table style='width:100%;'>
								<tr>
									<td style='width:30%; vertical-align: top;'>
										<table style='width:100%; border-collapse: collapse;'>
											<tr>
												<td style='width:43%; text-align: right; padding: 10px; border: 1px solid #ccc;'>
													From:
												</td>
												<td style='font-size: 14px;font-weight: bold; border: 1px solid #ccc; padding-left: 5px;'>
													{$f_name} 
												</td>
											</tr>	
											<tr>
												<td style='width:43%; text-align: right; padding: 10px; border: 1px solid #ccc;'>
													DTR Coverage:
												</td>
												<td style='font-size: 14px;font-weight: bold; border: 1px solid #ccc; padding-left: 5px;'>
													{$coverage}
												</td>
											</tr>
											{$accom_view}
											<tr>
												<td style='width:43%; text-align: right; padding: 10px; border: 1px solid #ccc;'>
													Approved By:
												</td>
												<td style='font-size: 14px;font-weight: bold; border: 1px solid #ccc; padding-left: 5px;'>
													{$approvedby}
												</td>
											</tr>
											<tr style=''>
												<td colspan=2 style='text-align: center; padding: 10px 5px;'>
													<a href='".base_url()."dtr/approval/".$vercode."/".$pword."/".$this->alcrypt($uname)."' style='text-decoration:none;'>
														<p style='padding: 15px;
																	text-decoration: none;
																	background: #77ece8;
																	font-size: 16px;
																	margin: 0px auto;
																	width: 83%;
																	color: #17625c;
																	font-weight: bold;
																	border: 1px solid #6ccec7;
																	border-radius: 99px;'> Approve 
														<p>
													</a>
												 <a href='".base_url()."/dtr/forapproval/".$pword."/".$uname."' style='text-decoration:none;'>
												   <p style='padding: 15px;
															margin: 0px auto;
															background: #efefef;
															border: 1px solid #b9b9b9;
															font-size: 16px;
															width: 83%;
															border-radius: 99px;'> View all DTR 
													</p>
													</a>
												</td>
											</tr>
										</table>
									</td>
									<td rowspan=6 style='padding: 10px;border: 1px solid #ccc;background: #eaeaea;'>
										".urldecode($bodycode)."
									</td>
								</tr>
								
							</table>
						</div>
						</div>";
			return $m;
		}
		
		function getemployees($office, $division) {
			$sql  = "select * from employees where DBM_Pap_id = '{$office}'";
				
				if ($division != 0) {
					$sql .= " and Division_id = '{$division}'";
				}
				
			$sql  .= " and status = '1' order by l_name ASC";
			return $this->__getdata($sql);
		}
	
		function changesignatory($office, $division, $emp) {
			
			//if ($division != 0) {
			// reset head 
				$sql_reset_divhead = ["is_head"=>0];
				$sql_reset_where   = ["DBM_Pap_id"=>$office,"conn"=>"and","Division_id"=>$division];
				
				$reset_update = $this->__update("employees",$sql_reset_divhead,$sql_reset_where);
			//}
			
			// update the signatory
			
			$sql = "update employees set 
						is_head = '1',
						DBM_Pap_id = '{$office}',
						Division_id = '{$division}'
						where employee_id = '{$emp}'";
						
			return $this->run_sql($sql);				  
		}
		
		function changediv($office, $division, $emps = Array()) {
			$this->load->model("v2main/Globalproc");
			
			$ups  = false;
			$sets = "update employees set ";
			if (!is_array($emps)) {
				return false;
			} else {
				$sets .= "DBM_Pap_id = '{$office}'";
				$sets .= ", Division_id = '{$division}'";
					
				$sets .= " where ";
				$count = 0;
				
				foreach($emps as $es) {
					$sets .= "employee_id = '{$es}'";
					$sets .= (count($emps)-1 != $count)?" or ":"";
					$count++;
				}
				//echo $sets;
				$ups = $this->run_sql($sets);
			}
			
			return $ups;
		}
		
		function checkundertime($time1, $time2) {
			$secs   = strtotime($time1)-strtotime("00:00:00");
			$a 	    = strtotime($time2)-$secs;
			$result = date("H:i",strtotime($time2)-$secs);
			
			$b = strtotime(date("h:i",strtotime($result)-strtotime("00:01:00"))); // unix form of the given time
			$c = strtotime("00:08:00");	// unix form of 8:00
			
			if ($b < $c) {
				return true;
			}
			return false;
		}
		
		function checkattachment($checkdate, $empid) {
			# //attachment types 
				# //CA
				# //PS
				# //LEAVE 
				# //PAF 
				# //OB 
				# //
				
				$checkdate = date("m/d/Y",strtotime($checkdate));
				$sqlex = "select ce.*, 
								 cel.checktime,
								 cel.checktype,
								 cel.shift_type,
								 cel.modify_by_id as mbi,
								 cel.is_modify,
								 cel.is_delete,
								 cel.date_added,
								 cel.date_modify,
								 l.leave_name
							 from checkexact as ce 
							LEFT JOIN checkexact_logs as cel 
								ON ce.exact_id = cel.exact_id
							LEFT JOIN leaves as l 
								ON ce.leave_id = l.leave_id
							where CONVERT(datetime, ce.checkdate) = '{$checkdate}' and employee_id = '{$empid}'";
			//	echo $sqlex."<br/><br/><br/>";
				$ret = $this->__getdata($sqlex);

			//	if ($ret == false) {return false;}
				return ["ret"=>$ret];
		}
		
		function delete_leavecredit($exactid, $empid, $is_elcid = false) {
			if ($is_elcid != false){
				// delete from employee_leave_credits using elc_id field 
			} else {
				// delete from employee_leave_credits using exact_id field
				$delete   = "delete from employee_leave_credits where employee_id = '{$empid}' and exact_id = '{$exactid}'";
				$isdelete = $this->run_sql($delete);
				
				return $isdelete;
			}
		}
		
		function getleavecredits($empid) {
			$sql 		= "select * from 
							(select 
								distinct(elc.exact_id) as grp_id,
								elc.elc_id,
								elc.employee_id,
								elc.vl_value,
								elc.fl_value,
								elc.sl_value,
								elc.spl_value,
								elc.coc_value,
								elc.credits_as_of,
								elc.vl_earned,
								elc.sl_earned,
								elc.elc_type,
								elc.hrs,
								elc.mins,
								elc.withpay,
								elc.wopay,
								elc.formonet,
								l.*,
								cll.no_days_applied,
								c.ps_type,
								c.type_mode_details,
								e.f_name
							 from employee_leave_credits as elc 
							 LEFT JOIN checkexact as c on elc.exact_id = c.grp_id
							 LEFT JOIN leaves as l on c.leave_id = l.leave_id
							 LEFT JOIN checkexact_leave_logs as cll on c.grp_id = cll.grp_id
							 LEFT JOIN checkexact_approvals  as ca on cll.grp_id = ca.grp_id
							 LEFT JOIN employees as e on e.employee_id = elc.employee_id 
							where elc.employee_id = '{$empid}') as tbl1 order by tbl1.elc_id DESC";
			$data 		= $this->Globalproc->__getdata($sql);
		
			$cons =	"select * from hours_minutes_conversation_fractions";
			$conversion_tbl = $this->Globalproc->__getdata($cons);
			
			$b = array_map(function($a){
				return (array) $a;
			},$conversion_tbl);
						
			$vacation 		= ["x" 	   	  => null,
							   "earned"   => null,
							   "balance"  => null,
							   "withpay"  => null,
							   "withopay" => null];
			
			$sick 			= ["x" 	   	  => null,
							   "earned"   => null,
							   "balance"  => null,
							   "withpay"  => null,
							   "withopay" => null];

			$global			= ["hrs"	  => null,
							   "mins"     => null,
							   "days"	  => null,
							   "period"   => null,
							   "desc"     => null,
							   "elc"	  => null,
							   "grpid"    => null,
							   "fullname" => null];
							   
			// $data['ledger'] = [];
				
				// vacation variables
					$vlbal    = 0;
					$vlearned = 0;
				
				// end vacation variables 
			
				// sick variables 
					$slbal    = 0;
					$slearned = 0;				
				// end sick variables 
			
				// global variables : DAYS 
					$days = 0;
				// end 
			$ledger = [];
				for($i=0,$l=count($data)-1;$i<=count($data)-1;$i++,$l--) {
					// reset minuses 
						$vlminus_mins  = 0;
						$vlminus_hours = 0;
					
					// reset VL
					$vlminus  = 0;  // reset 
					$slnopay  = 0;  // reset 
					
					// reset minuses 
						$slminus_mins  = 0;
						$slminus_hours = 0;
						
					// reset SL
					$slminus  = 0;	// reset
					$vlnopay  = 0;	// reset 
					
					
					$vacation = ["x" 	  => 0,
							   "earned"   => 0,
							   "balance"  => 0,
							   "withpay"  => 0,
							   "withopay" => 0];
			
					$sick 	  = ["x" 	   	=> 0,
								 "earned"   => 0,
								 "balance"  => 0,
								 "withpay"  => 0,
								 "withopay" => 0];

					$global	  = ["hrs"	  => 0,
								 "mins"   => 0,
								 "days"	  => 0,
								 "period" => 0,
								 "desc"   => 0,
								 "elc"	  => 0,
								 "grpid"  => 0];
							   
					if ($data[$l]->elc_type != "paf") {
						$vlearned = $data[$l]->vl_earned;
						$slearned = $data[$l]->sl_earned;
							
							if ($i==0) {
								// vl
								$vlbal   			 = $data[$l]->vl_earned;
								$vacation['earned']  = $vlearned;
								$vlearned 			 = 0;
								
								// sl
								$slbal          	 = $data[$l]->sl_earned;
								$sick['earned']		 = $slearned;
								$slearned 			 = 0;
								
								
							} else {
								$vacation['earned']  = $vlearned;  // vl
								$sick['earned']		 = $slearned;  // sl
							}
						
						// counting of days 
							if ($data[$l]->no_days_applied != null) {
								$temp_d = count(explode("-",explode(" ",$data[$l]->credits_as_of)[1]));
								$days = $global['days']    = $temp_d;
							}else {
								$days = $global['days']    = $data[$l]->no_days_applied;
							}
						// end of counting days 
						
						if ($data[$l]->elc_type == "t" || $data[$l]->elc_type == "ut" || $data[$l]->elc_type == "leave" || $data[$l]->elc_type == 'ps') {
							if ($data[$l]->leave_id == '2' || $data[$l]->elc_type == "t" || $data[$l]->elc_type == "ut" ||
								$data[$l]->leave_id == '6' || $data[$l]->ps_type == 2) { 
								
								// vl 
								/*
								$vlminus 			  = $data[$l]->withpay;
								$vacation['withopay'] = $data[$l]->wopay;
									
								$vlbal   			  = ($vlbal - $vlminus) + $vlearned;
								
								if ($vlminus == 0){
									$vlbal = 0;
								}
								
								if ($vlminus != 0 && $vacation['withopay'] != 0) {
									$vlbal = 0;
								}
								*/
								
								// hrs 
								foreach($b as $bs) {
									if ($bs['particular'] == $data[$l]->hrs && $bs['type'] == "h") {
										$vlminus_hours  = $bs['equi_day'];
									}
								}
								// mins 
								foreach($b as $bs) {
									if ($bs['particular'] == $data[$l]->mins && $bs['type'] == "m") {
										$vlminus_mins   = $bs['equi_day'];
									}
								}
								
								$vlminus 			  	  = $vlminus_mins + $vlminus_hours + $days;
								if ($vlminus >= $vlbal) {
									$vacation['withopay'] = $vlminus - $vlbal;
									$vlminus 			  = $vlbal;
									$vlbal   			  = 0;
									// $vlminus = 0;
								} else {
									$vacation['withopay'] = 0; // $data[$l]->wopay;
									$vlbal   			  = ($vlbal - $vlminus) + $vlearned;
								}
								
								// echo "(".$data[$l]->hrs.")".$vlminus_mins."+".$vlminus_hours."=".$vlminus."<br/>";	
								
								
							} else if ($data[$l]->leave_id == '1') {
								// hrs
								foreach($b as $bs) {
									if ($bs['particular'] == $data[$l]->hrs && $bs['type'] == "h") {
										$slminus_hours  = $bs['equi_day'];
									}
								}

								// mins
								foreach($b as $bs) {
									if ($bs['particular'] == $data[$l]->mins && $bs['type'] == "m") {
										$slminus_mins   = $bs['equi_day'];
									}
								}
								
								$slminus			  = $slminus_mins + $slminus_hours + $days;
								if ($slminus >= $slbal) {
									$sick['withopay']     = $slminus - $slbal;
									$slminus			  = $slbal;
									$slbal				  = 0;
								} else {
									$sick['withopay']     = 0;
									$slbal				  = ($slbal - $slminus) + $slearned;
								}
								// sl
								/*
								$sick['withopay']     = $data[$l]->wopay;
								$slminus 			  = $data[$l]->withpay;
								$slbal   			  = ($slbal - $slminus) + $slearned;
								*/
							}
						} else {
							/// vl
							$vlminus = $data[$l]->withpay;
							$vlbal   = ($vlbal - $vlminus) + $vlearned;
								
							// sl
							$slminus = $data[$l]->withpay;
							$slbal   = ($slbal - $slminus) + $slearned;
							
						}
						
						// global 
							$global['fullname'] = $data[$l]->f_name;
							$global['empid']    = $data[$l]->employee_id;
							
							$global['grpid']    = $data[$l]->grp_id;
							$global['elc']	    = $data[$l]->elc_id;
							$global['hrs']	    = $data[$l]->hrs;
							$global['mins']	    = $data[$l]->mins;
								
							$global['period']   = $data[$l]->credits_as_of;
								
							// $global['days']	   = $data[$l]->no_days_applied;
							

							
						//	$global['s']	   = array_search($data[]);
							
							$global['desc']    = $data[$l]->elc_type;
							$global['specs']   = ($data[$l]->leave_name == "Force")?"Forced":$data[$l]->leave_name;
							
							switch ($data[$l]->elc_type) {
								case "t":
									$global['desc'] = "Tardiness";
									break;
								case "ut":
									$global['desc'] = "Undertime";
									break;
								case "FB":
									$global['desc'] = "Forwarded Balance";
									break;
								case "ps":
									$global['desc'] = "PS - ";
									
									$pstype___ 		= null;
									if ($data[$l]->ps_type != NULL) {
										$global['desc'] .= ($data[$l]->ps_type==2)?"Personal":"Official";
										$pstype___       = ($data[$l]->ps_type==2)?"Personal":"Official";
									} else {
										$pt = $this->Globalproc->gdtf("checkexact",['exact_id'=>$data[$l]->grp_id],['ps_type']);
										$global['desc'] .= ($pt[0]->ps_type==2)?"Personal":"Official";
										$pstype___       = ($pt[0]->ps_type==2)?"Personal":"Official";
									}
									
									if ($pstype___ == "Personal") {
										
									}
									break;
							}
							
						// vl 
							$vacation['balance'] = $vlbal;
							$vacation['withpay'] = $vlminus;
						
						// sl 
							$sick['balance']	 = $slbal;
							$sick['withpay']     = $slminus;
					// 
						
						if ( $data[$l]->formonet == 1 || $data[$l]->formonet == "1") {
							
							if ($data[$l]->leave_id =='2') {
								$global['specs'] = "FOR MONETIZATION";
								$global['desc']	 = "vacation leave";	
							}
							
							if ($data[$l]->leave_id == '1') {
								$global['specs'] = "FOR MONETIZATION";
								$global['desc']	 = "sick leave";
							}
							
							// erase the date
							$global['period'] = null;
							
						}
						
						array_push($ledger,[$vacation,$sick,$global]);
					}
				}
				
				return $ledger;
		}
		
		public function getplantilla() {
			$plantilla = $this->gdtf("employees",["employment_type"=>"REGULAR","conn"=>"and","status"=>1],"*"," ORDER BY l_name ASC");
			
			return $plantilla;
		}
		
		public function getallemps() {
			$plantilla = $this->gdtf("employees",["status"=>1],"*");
			
			return $plantilla;
		}
		
		public function awardcredit($empid) {
			if ($empid == null) {
				return;
			}
			
			// see if there is already a previously credited leave
				$sql_1 = "select * from employee_leave_credits as elc 
							where elc_id = (select max(elc_id) 
								from employee_leave_credits 
									where CONVERT(varchar(3), credits_as_of) = ''";
			// end 
			
			// get previous balance 
				$sql   = "select * from employee_leave_credits
						  where elc_id = (select max(elc_id) from employee_leave_credits 
											where employee_id = '{$empid}')";	
				$_vals = $this->__getdata($sql);
			// end
			
			$vl_bal  = 0;
			$sl_bal  = 0;
			
			$spl_bal = 0;
			$fl_bal  = 0;
		
			$coc_value = 0;
			if (count($_vals) > 0) {
				$vl_bal  = $_vals[0]->vl_value;
				$sl_bal  = $_vals[0]->sl_value;
				
				$fl_bal  = $_vals[0]->fl_value;
				$spl_bal = $_vals[0]->spl_value;
				
				$coc_value = $_vals[0]->coc_value;
			}
			
			$vl_earned = 1.250;
			$sl_earned = 1.250;
			
			$slme_sql =  "select * from employee_leave_credits
						  where elc_id = (select max(elc_id) from 
											employee_leave_credits where employee_id = '{$empid}' and elc_type = 'earned')";
			
			$select_last_mo_earned = $this->__getdata($slme_sql);
			
			if (count($select_last_mo_earned)==0) {
				$last_mo = date("m/d/Y",strtotime("-2 month", strtotime(date("m/d/Y"))));
			} else {
				$last_mo = $select_last_mo_earned[0]->credits_as_of;
			}
			
			// === get the deductions for this month 
				$dd   = date("M",strtotime("-1 month", strtotime(date("M"))));
				$deds = "select 
								tb1.*,
							CASE 
								WHEN tb1.elc_type = 'leave' then (select tb2.leave_id from 
																	(select top 1 * from checkexact 
																		where grp_id = tb1.exact_id) 
																	as tb2 )
								ELSE '0'
							END as leavename
							from (select * from employee_leave_credits 
								where CONVERT(varchar(3), credits_as_of) = '{$dd}' and employee_id = '{$empid}') as tb1
									where tb1.elc_type = 't' or tb1.elc_type = 'ut' or tb1.elc_type = 'leave'";
				// echo $deds;
				$dets = $this->__getdata($deds);
				
				$sl_deds = 0;
				$vl_deds = 0;
				if (count($dets)>0){
					foreach($dets as $d) {
						if ($d->leavename == 1) { // compound without-pay to SL 
							$sl_deds += $d->wopay;
						} else if ($d->leavename == 2 || $d->elc_type == 't' || $d->elc_type == 'ut') { // compound without-pay to vl
							$vl_deds += $d->wopay;
						}
					}
				}
			// === end deductions 
			
			// setting new values
				$vl_earned = $vl_earned - $vl_deds;
				$sl_earned = $sl_earned - $sl_deds;
			// end 
			
			$comp_month = date("M. 1-t, Y", strtotime("-1 month", strtotime(date("M")))); // last month's month
			
			$addto 	    = date("M. 1-t, Y", strtotime("+1 month", strtotime($last_mo)));
			
			if ($last_mo == $comp_month) { // addto was formerly last_mo				
				// echo $last_mo."|".$comp_month;
				// return;
			}
			
			$details = [
				"employee_id"		=> $empid,
				"vl_value"			=> $vl_bal + $vl_earned,
				"fl_value"			=> $fl_bal + 0,
				"sl_value"			=> $sl_bal + $sl_earned,
				"spl_value"			=> $spl_bal + 0,
				"coc_value"			=> $coc_value, // for coc
				"vl_earned"			=> $vl_earned,
				"sl_earned"			=> $sl_earned,
				"credits_as_of"		=> $addto,
				"elc_type"			=> "earned"
			];
		
			return $this->__save("employee_leave_credits",$details);
		}
		
		public function get_rem($what, $year, $empid) {
			$leavecode = null;
			$_val   = null;
			
			switch($what) {
				case "FL":
					$leavecode = 6;
					$_val 	   = 5;
					break;
				case "SPL":
					$leavecode = 4;
					$_val 	   = 3;
					break;
			}
			
			$_sql = "select distinct(ce.grp_id),l.leave_name,elc.*  from employee_leave_credits as elc
						JOIN checkexact as ce on elc.exact_id = ce.grp_id
						JOIN leaves as l on ce.leave_id = l.leave_id 
						where l.leave_id = '{$leavecode}' and RIGHT(credits_as_of, 4) = '{$year}' and elc.employee_id ='{$empid}'";
			
			$_data = $this->__getdata($_sql);
			
			$count    = 0;
			foreach($_data as $l) {
				$count 	+= count(explode("-",explode(" ",$l->credits_as_of)[1]));
			}
			
			$total = ($_val-$count);
			return ($total);
		}
		
		public function getholidays($sdate,$edate) {
			$query = "SELECT 
						*
						FROM 
						  dbo.holidays
						WHERE 
						CAST (holiday_date as DATETIME) 
						BETWEEN CAST ('{$sdate}' as DATETIME)  AND CAST ('{$edate}' as DATETIME);
					  ";

			// $query =  $DB2->query($query);
			// return $query->result();
			
			return $this->__getdata($query);
		}
		
		public function check_holiday($holidays, $this_date){

		//function test(){

			//$this_date = '7/6/2016';

			//$holidays = $this->attendance_model->getholidays('07/01/2016' , '07/16/2016');	



			$holidays_array = array();

			
			foreach ($holidays as $rows) {
				$holidays_array[] = date('n/j/Y', strtotime($rows->holiday_date));;
			}



			if (in_array($this_date, $holidays_array)) {
			   return  true;
			}else{
			   return false;
			}


		}
		
		public function get_timein_am($empid, $date) {
			$date  = date("m/d/Y",strtotime($date));
			
			$thein  = null;
			
			$amin_exact  = null;
			$amin_flex   = null;
			
			$pmout_exact = null;
			$pmout_flex  = null;
				
			$amsin = $this->gdtf("checkexact_ams",
								['employee_id'=>$empid,"conn"=>"and","checkdate"=>$date],["a_in"]);
			
			// get the shift 
			$shift_sql = "select 
							es.*,
							sml.*,
							e.biometric_id 
						  from employee_schedule as es
								JOIN shift_mgt_logs as sml on es.shift_id = sml.shift_id 
								JOIN employees as e on es.employee_id = e.employee_id 
							  where es.employee_id = '{$empid}' and is_active = 1 
							  order by shift_mgt_logs_id ASC";
			
			$shift = $this->__getdata($shift_sql);
				
			if (count($shift)==0) {
				$amin_exact  = "8:00 AM";
				$amin_flex   = "8:00 AM";
				
				$pmout_exact = "5:00 PM";
				$pmout_flex  = "5:00 PM";
			} else {
				foreach($shift as  $s) {
					if ($s->shift_type == "AM_START") {
						$amin_exact  = $s->time_exact;
						$amin_flex   = $s->time_flexi_exact;
					} else if ($s->shift_type == "PM_END") {
						$pmout_exact  = $s->time_exact;
						$pmout_flex = $s->time_flexi_exact;
					}
				}
			}
			
			if (count($amsin)==0) {
				// get from checkinout
					$biometric = "select * from checkinout 
									where biometric_id = '{$shift[0]->biometric_id}'
									and checktime like '%{$date}%'";
					$bio 	= $this->__getdata($biometric);
					
					$thein 	= false;
					if (count($bio)>0){
						foreach($bio as $b) {
							if ( strtoupper( date("A",strtotime($b->checktime)) ) == "AM") {
								$thein = date("h:i A", strtotime($b->checktime));
							}
						}
					}		
			} else {
				$thein  = $amsin[0]->a_in;
			}
			
			return [$thein,[$amin_exact,$amin_flex,$pmout_exact,$pmout_flex]];
		}
		
		public function alcrypt($word) {
			$pattern   = "abcdefghijklmnopqrstuvwxyz123456789";
			
			$enc 	   = "";
			for ($i = 0; $i <= strlen($word)-1; $i++) {
				if ($word[$i] == ".") {
					$enc .= "0";
				} else if ($word[$i] == "@") {
					$enc .= "_";
				}
				
				for($o = 0; $o <= strlen($pattern)-1; $o++) {
					if ($word[$i] == $pattern[$o]) {
						$index = $o+1;
						if ($o == strlen($pattern)-1) {
							$index = 0;
						}
						$enc .= $pattern[$index];
					} 
				}
			}
			return $enc;
		}
		
		public function converttoseconds($time,$type) {
			$valtomult = null;
			switch($type) {
				case "d": $valtomult = 86400; break;
				case "h": $valtomult = 3600; break;
				case "m": $valtomult = 60; break;
			}
			return $time*$valtomult;
		}
		
		public function getsettings($module) {
			$sql = "select * from settings where setid = (select max(setid) from settings where settingmodule = '{$module}')";
			
			return $this->__getdata($sql);
		}
		
		public function getremaining($what) {
			
		}
		
		public function howmanysigs($empid) {
			$check = $this->gdtf("employees",['employee_id'=>$empid],["is_head","DBM_Pap_id","Division_id"]);
			
			if (count($check)==0) { return false; }
			
			if ($check[0]->is_head == true) {
				// for director and division heads/OIC
				return "1";
			} 
			
			if ($check[0]->Division_id == 0) {
				// for regular employees who reports directly to director
				return "1";
			}
			
			if ($check[0]->Division_id > 0) {
				return "2";
			}
		}
		
		public function savetocheckinout($data) {
			$sql = "";
			
			$areas = $this->__getdata("select * from areas");
			
			foreach($data as $d) {
				$bioid 	  = $d['PIN'];
				$macalias = $d['MachineAlias'];
				$areaid   = null;
				$ctime 	  = date("n/j/Y g:i A", strtotime($d['CHECKTIME']));
				$ctype 	  = $d['CheckType'];
				
				foreach($areas as $a) {
					if ($macalias == $a->area_name) {
						$areaid = $a->area_id;
						break;
					}
				}
				
				$find  = $this->gdtf("checkinout","checktime = '{$ctime}' and checktype = '{$ctype}' and biometric_id = '{$bioid}'","*");
				
				$bypass = false;
				if (count($find) > 0) {
					$bypass = true;
				}
				
				if (!$bypass) {
					$sql .= "insert into checkinout (biometric_id,area_id,checktime,checktype)
							values('{$bioid}','{$areaid}','{$ctime}','{$ctype}');";
				}
				
			}
			
			return $this->run_sql($sql);
			
		}
		
		public function getdiff_time($firsttime, $secondtime, $operator) {
			if ($firsttime == null) return;
			
			list($fh,$fm) = explode(":",$firsttime);
				
				$f_second1 = $fh*3600;
				$f_second2 = $fm*60;
					
					$fst   = $f_second1 + $f_second2;
					
			//-------------------------------------
			
			list($sh,$sm) = explode(":",$secondtime);
				
				$s_second1 = $sh*3600;
				$s_second2 = $sm*60;
				
					$sst   = $s_second1 + $s_second2;
					
			// ------------- compute the two ---------------------
			
			$ret = null;
			switch($operator) {
				case "+":
					$ret = $fst + $sst;
					break;
				case "-":
					$ret = $fst - $sst;
					break;
			}
			
			$h = floor($ret / 3600);
			$m = floor($ret / 60 % 60);
			return $h.":".$m;
		}
		
		
	}
