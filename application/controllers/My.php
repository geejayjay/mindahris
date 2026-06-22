<?php

	class My extends CI_Controller {
		protected $myid;

		public function __construct() {
			parent::__construct();
			
			if($this->session->userdata('is_logged_in') != TRUE){
				$this->session->set_userdata('database_default', 'sqlserver');
				/*
				$s_emp = $this->uri->segment(7);
				$DB2   = $this->load->database('sqlserver', TRUE);
				
				$query = "select * from d_accomplishment where f_signatory = '{$s_emp}'";
				
				$query  = $DB2->query($query);
				$result = $query->result();
				
				if(count($result)==0) {
					$q2 	= "select * from d_accomplishment where s_signatory = '{$s_emp}'";
					$query  = $DB2->query($query);
					$result = $query->result();
					
					if (count($result)==0) {
						$bu  = base_url();
						//header("location: {$bu}");
						//return;
					}
				}
				
				$query = "Select * from users where employee_id = '{$s_emp}'";
				$query = $DB2->query($query);
				
				$result = $query->result();
				
				if (count($result) == 0) {
					//die("No user found");
					//return;
				}
				
				$this->load->model("Login_model");
				$result2      = $this->Login_model->getUserInformation($s_emp);
				
				$user_session = array(
					'employee_id' => $s_emp,
					'username' => $result[0]->Username,
					'usertype' => $result[0]->usertype,
					'full_name' => $result2[0]->f_name,
					'first_name' => $result2[0]->firstname,
					'last_name' => $result2[0]->l_name,
					'biometric_id' => $result2[0]->biometric_id,
					'area_id' => $result2[0]->area_id,
					'area_name' => $result2[0]->area_name,
					'ip_address' => $_SERVER["REMOTE_ADDR"],
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
				);
				
				$this->session->set_userdata($user_session);
				*/
 			}
			
			$this->load->Model("Globalvars");
		
			$this->load->Model("v2main/Actiononleave");

			$this->myid = $this->Globalvars->employeeid;
			
			$this->_upload_docs();
		}

		public function applications() {
			$this->load->model("v2main/Globalproc");

			$sql 		= "select * from leaveapplications as a
							JOIN employees as b 
								on a.empid = b.employee_id
							JOIN leaves as c 
								on a.typeofleave_id = c.leave_id
							where a.empid = '{$this->myid}'";
			
			$data['la'] = $this->Globalproc->__getdata($sql);


			$data['title'] 		  = '| My Applications';
			$data['main_content'] = "v2views/actiononapplication";

			$this->load->view('hrmis/admin_view',$data);
		
		}
		
		public function dashboard() {
			$this->load->model("Globalvars");
			$this->load->model("v2main/Dashboard");
			$this->load->model("v2main/Globalproc");
			
			$data['admin'] = ($this->Globalvars->usertype != "user")?true:false;
			
			$data['title'] = '| My Dashboard';
			
			$data['headscripts']['style'][0]  = base_url()."v2includes/style/hr_dashboard.style.css";
			
			//$data['headscripts']['style'][1]     = "https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css";
				
			$data['headscripts']['style'][1]  = "https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css";
			
			// for leave management
			$data['headscripts']['style'][2]  = base_url()."v2includes/style/leavemgt.style.css";
			
			$data['headscripts']['js'][0] 	  = "https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.4/jspdf.debug.js";
						
			$data['main_content'] = "v2views/hr_dashboard";
			$this->load->view('hrmis/admin_view',$data);
		}
		
		public function recallctoot() {
			$ctootid = $this->input->post("info")['ctoot'];
			
			$this->load->model("v2main/Globalproc");
			
			$getdata = $this->Globalproc->gdtf("employee_ot_credits",["elc_otcto_id"=>$ctootid],"*");
			
			if ($getdata[0]->credit_type == "CTO") {
				// delete from checkexact  :: not implemented yet
				
			} else if ($getdata[0]->credit_type == "OT") {
 				// delete from checkexact_ot :: not implemented yet
				
			}
			
			$delete = "delete from employee_ot_credits where elc_otcto_id = '{$ctootid}'";
			
			$stat = $this->Globalproc->run_sql($delete);
			
			echo json_encode($stat);
			
		}
		
		public function newledger($emp_id = '') { // currently being used
			$this->load->model("v2main/Ctomodel");
			$data['headscripts']['style'][] = base_url()."v2includes/style/ctoot.style.css";
			$data['headscripts']['js'][]	= base_url()."v2includes/js/newcto.procs.js";
			
			$a  	   		    			= $this->Ctomodel->getcto($emp_id);
		
			$name 							= null;
			$data['usertype']   			= $this->session->userdata("usertype");
		
			$data['ctodata']				= [];
			
			// set outside the for loop to preserve the previous data that was stored into the variable
			$remaining 						= 0; // by default is set to seconds
			
			$count      					= 0;
			$x 								= 0;
				
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
					$remaining 					= $this->Ctomodel->consec($this->Ctomodel->returntotimeformat($a[$n]->total_credit));
				
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
						$morningtimedif = $this->Ctomodel->gettimedif($time1, $time2);
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
						$afternoontimedif = $this->Ctomodel->gettimedif($time1,$time2);
					}
					
					if ($morningtimedif != "0:0" && $afternoontimedif != "0:0") {
						$totalot 	= ($this->Ctomodel->convertoseconds($morningtimedif) + $this->Ctomodel->convertoseconds($afternoontimedif));
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
						$totalot 	= $this->Ctomodel->returntotimeformat($totalot);
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
						
				/*
					echo "remaining: ".$remaining."=".$this->Ctomodel->returntotimeformat($remaining)."<br/>";
					echo "with X: ".$totalcreditswithx."<br/>";
					echo "with X timeformat: ".$this->Ctomodel->returntotimeformat($totalcreditswithx)."<br/>";
					echo "=================<br/>";
				*/
				
					$remaining 				 	= $this->Ctomodel->consec($this->Ctomodel->returntotimeformat($totalcreditswithx)) + $remaining;
					
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
					
					$vars['totalcreditswithx']	= $this->Ctomodel->returntotimeformat($totalcreditswithx);	
					
				} else if ($a[$n]->credit_type == "CTO") {
					$usedcochr 					= $a[$n]->cto_hours;
					$usedcocmm					= $a[$n]->cto_mins;
					
					$vars['daycto']				= $a[$n]->date_of_application;
					$vars['usedcochr']			= $usedcochr;
					$vars['usedcocmm']			= $usedcocmm;
					
					$usedcocmm					= (strlen($usedcocmm)==1)?"00":$usedcocmm;
					$timeinsec 					= $usedcochr.":".$usedcocmm;
					 
					$timeinsec  				= $this->Ctomodel->consec($timeinsec);
					 
					$remaining					= ($remaining - $timeinsec);
				}
				
				$vars['otctoid']   = $a[$n]->elc_otcto_id;
				
				$vars['remaining'] = $this->Ctomodel->standard_time($remaining);
				
				array_push($data['ctodata'],$vars);
			}
			
		//	var_dump($data['ctodata']);
			$data['emp_name']     = @$a[0]->f_name;
			
			$data['title']	 	  = "| Compensatory Time-Off";
			$data['main_content'] = "v2views/newcto";
			$this->load->view('hrmis/admin_view',$data);
		}
		
		public function testb() {
			$this->load->model("v2main/Ctomodel");
			
			/*
			$time  = "523860";
			$time2 = "523860";
			$time3 = $time + $time2;
			
			echo $this->Ctomodel->standard_time( $time3 );
				echo "<br/>";
			echo $this->Ctomodel->standard_time( $time2 );
			*/
			
			$time  = $this->Ctomodel->consec("13:45");
			$time2 = $this->Ctomodel->consec("16:59");
			
			$time3 = ($time+$time2);
			
			echo $this->Ctomodel->standard_time($time3);
		}
		
		public function ledger( $emp_id = '' , $tbl = '') { //$tbl = '',

			if ($tbl == "coc") {
				
				$this->load->model("v2main/Globalproc");
				$this->load->model("v2main/Ctomodel");
				$this->load->model("Globalvars");
				
				if ($emp_id == '' || $this->session->userdata("usertype") != "admin") {
					$emp_id = $this->Globalvars->employeeid;
				}
				
				echo "<script>";
					echo "var t_emp_id = '{$emp_id}'";
				echo "</script>";
				
				$data['emp_name'] 				= $this->Globalproc->gdtf("employees",["employee_id"=>$emp_id],["f_name"])[0]->f_name; 
				
				$data['title']					= "| Ledger (COC)";
				$data['headscripts']['style'][] = base_url()."v2includes/style/ctoot.style.css";
				$data['headscripts']['js'][] 	= base_url()."v2includes/js/ctoot.js";
				$data['main_content'] 			= "v2views/cto_ledger";
				
				$a = $this->Ctomodel->getcto($emp_id);
				
				
				$this->load->view('hrmis/admin_view',$data);
			} else if ($tbl == '') { 
				$data['noemp']	  = false;
				$data['notadmin'] = true;
				if ($emp_id == '') {
					$data['noemp']	= true;
					//redirect(base_url(),"refresh");
				}
				
			//	if ($this->usertype != "admin") {
			//		die("You are not allowed here"); return;
			//	}
				
				$this->load->model("v2main/Globalproc");
				
				$ledger 						 = $this->Globalproc->getleavecredits($emp_id);
				$data['employees']				 = [];
				$data['ledger'] 				 = $ledger;
					
				$data['title']					 = "| Ledger";
				
				// old leave management program :: used in the recording of leave
					//$data['headscripts']['js'][]	 = base_url()."v2includes/js/leavemgt.procs.js";
				
				// new leave mananagement program :: used in the recalling of leave 
					// $data['headscripts']['js'][]	 = base_url()."v2includes/js/newleavemgt.procs.js";
				
				$data['headscripts']['style'][]  = base_url()."v2includes/style/leavemgt.style.css";
				$data['main_content'] 			 = "v2views/displedger";
				
				$year 							 = date("Y");
				$data['fl']						 = $this->Globalproc->get_rem("FL",$year,$emp_id);
				$data['spl']					 = $this->Globalproc->get_rem("SPL",$year,$emp_id);
				$this->load->view('hrmis/admin_view',$data);
			}
		}
		
		public function checking() {
			$this->load->model("Globalvars");
			$this->load->model("v2main/Globalproc");
			
			$logged_in_id = $this->Globalvars->employeeid;
			
			// $emp_details = $this->Globalproc->gdtf("users",["employee_id"=>$logged_in_id],["usertype"]);
			$emp_sql = "select u.usertype, e.f_name from users as u
						JOIN employees as e on u.employee_id = e.employee_id
						where u.employee_id = '{$logged_in_id}'";
			$emp_details = $this->Globalproc->__getdata($emp_sql);
			
			echo json_encode( [$emp_details[0]->usertype , $logged_in_id, $emp_details[0]->f_name] );
		}
		
		public function notify_div_chief() {
			$info   = $this->input->post("info");
			$sum_id = $info['sum_id'];
			
			$this->load->model("v2main/Globalproc");
			
			$details = ["employee_id","date_start_cover","date_end_cover"];
			$where   = ["sum_reports_id"=>$sum_id];
			$data    = $this->Globalproc->get_details_from_table("dtr_summary_reports",$details,$where);
			
			$emp_id  = $data['employee_id'];
			$d_start = $data['date_start_cover'];
			$d_end   = $data['date_end_cover'];	
		}
				
		public function sendNotificationToChief() {
			// used in the submission of DTR
			$info  			= $this->input->post("info");
			
			$this->load->model("Globalvars");
			$this->load->model("v2main/Globalproc");
			$this->load->model("main/Main_model");
			
			$emp_id  		= $this->Globalvars->employeeid;
			
			$html 			= $info["htmlcode"];
			
			$sum_rep_id     = $info['sum_id'];
					
			$data  	  = $this->Globalproc->get_details_from_table("employees",
																 ["employee_id"=>$emp_id],
																 ["Division_id","DBM_Pap_id","f_name","firstname","l_name","employment_type","area_id"]);
			//var_dump($data);
			$divid 	  = $data['Division_id'];
			
			$fullname = $data['firstname']." ".$data['l_name'];

			// start here =================
				$chiefid  	 = $info['divchief'];
				$dbm_id   	 = $info['dbm'];
				
				/*
				$chiefid	= 62;
				$dbm_id     = 168;
				*/
				
				if ($this->Globalproc->is_chief("division",$emp_id) || $divid == 0) {
					$chiefid = $dbm_id;
				}
				
				if ($this->Globalproc->is_chief("director",$emp_id)) {
					if ($emp_id == 27) { // doc Cha
						$dbm_id = $chiefid	= 1443; // chairman's ID
					} else if ( $emp_id == 80 || $emp_id == 22 || $emp_id == 59) { // dir Rey or dir Olie or asec Yo, respectively
						$dbm_id = $chiefid	= 1441; // usec's ID
					} else if ( $data['DBM_Pap_id'] == 1 && $data['Division_id']==0 ) { // head of OC
						$dbm_id = $chiefid	= 1443; // chairman's ID
					}
				}
				
				if ($data['area_id'] != 1 && !$this->Globalproc->is_chief("division",$emp_id)) {
					$dbm_id = $chiefid;
				}
				
				//$data2    	 = $this->Globalproc->gdtf("employees",["employee_id"=>$chiefid],"*");
				$data2    	 = $this->Globalproc->gdtf("employees",["employee_id"=>$chiefid],["email_2"]);
				$chief_email = $data2[0]->email_2;

				// approving body
					$chief_user  = $this->Globalproc->get_details_from_table("users",['employee_id'=>$chiefid],['Username','Password']);
					$c_u 		 = $chief_user['Username'];
					$c_p 		 = $chief_user['Password'];
				// end approving body
			
				// secret verification code =============================================================================================================
					$vc       = md5($emp_id).md5( date("mdyhisA") );
					$vercode  = date("mdYHiA")."-".$chiefid."-".$dbm_id."-".substr(md5($vc),0,9);
				// end secret verification code =========================================================================================================
				
			// end here ===================
			
			//echo json_encode($data2);
			
			$sumrep   = $this->Globalproc->get_details_from_table("dtr_summary_reports",["sum_reports_id"=>$sum_rep_id],["dtr_coverage","date_start_cover","date_end_cover","dtr_cover_id"]);
			
			// dtr cover id database 
				$dtr_cover = $this->Globalproc->gdtf("hr_dtr_coverage",['dtr_cover_id'=>$sumrep['dtr_cover_id']],["date_started","date_ended","date_deadline"]);
			// end 
			
			// $coverage = $sumrep["dtr_coverage"];
			$coverage = $dtr_cover[0]->date_started."-".$dtr_cover[0]->date_ended;
			
			/*
			$from_    = date("m/d/Y",strtotime($sumrep['date_start_cover']));
			$to_      = date("m/d/Y",strtotime($sumrep['date_end_cover']));
			*/
			$from_    = date("m/d/Y",strtotime($dtr_cover[0]->date_started));
			$to_      = date("m/d/Y",strtotime($dtr_cover[0]->date_ended));
			
			$cover_id = $sumrep['dtr_cover_id'];
			
			$accom_view = null;
			// mark attachaccom
			if ($data['employment_type'] == "JO") {
				$update_accom_sql = "update d_accomplishment 
										set coverage_id = '{$cover_id}', 
											spl_grp_id = '{$sum_rep_id}',
											f_signatory = '{$chiefid}',
											s_signatory = '{$dbm_id}'
										where date between '{$from_}' and '{$to_}'
										and user_id = '{$emp_id}'";
										// $chiefid
										// $dbm_id
										
				$update_ 		  = $this->Globalproc->run_sql($update_accom_sql);
				// my/accomplishments/viewing/389/01-29-2018/02-08-2018
				$d_from = date("m-d-Y",strtotime($from_));
				$d_to   = date("m-d-Y",strtotime($to_));
				$accom_view 	  = "<tr>
										<td> </td>
										<td style='font-size: 14px;font-weight: bold; border: 1px solid #ccc; padding: 13px; text-align: center;'>
											<a href='".base_url()."/my/accomplishments/viewing/{$emp_id}/{$d_from}/{$d_to}/{$chiefid}'>View Accomplishment Report</a> 
										</td>
									</tr>";
			}
				
			// end mark accom 
			
			// get the division chief
				// if logged in account is division chief... neglect
			
			$bypass_email = false;
			
			//if ($is_initial_approved == false) {
				//if ($emp_id == $chiefid) {
				if ($this->Globalproc->is_chief("division",$emp_id) || $this->Globalproc->is_chief("director",$emp_id) || $data['area_id'] != 1 
					|| $divid == 0) {
					$isapproved 	= $this->Globalproc->__update("dtr_summary_reports",
																 ["is_approved"=>1,"approved_by"=>$chiefid], 
																 ["sum_reports_id"=>$sum_rep_id]);
					
					// $bypass_email   = true;
				} else {
					$isapproved 	= 0;
				}
				
			//	$isapproved 	= 0;

		//	} else {
		//		$ret = true;
		//	}
			
			// for accom 
				$ranges = explode("-",$coverage);
				$from_  = date("m-d-Y", strtotime($ranges[0]));
				$to_    = date("m-d-Y", strtotime($ranges[1]));
			// end for accom

			// encrypt cu 
				$alcrypted = $this->Globalproc->alcrypt($c_u);
			// end encrypt 
			$m = "	<div style='width:100%; background:#ededed; padding: 18px 0px; font-size: 15px;'>
						<div style='width: 85%; margin: auto; border: 1px solid #9c9c9c; background: #fff; border-radius: 2px; font-family: arial; box-shadow: 0px 0px 4px #9e9e9e;'>
							<table style='width:100%;'>
								<tr>
									<td style='width:30%; vertical-align: top;'>
										<table style='width:100%; border-collapse: collapse;'>
											<tr>
												<td style='width:43%; text-align: right; padding: 10px; border: 1px solid #ccc;'>
													From:
												</td>
												<td style='font-size: 14px;font-weight: bold; border: 1px solid #ccc; padding: 13px; text-align: center;'>
													{$data['f_name']} 
												</td>
											</tr>	
											<tr>
												<td style='width:43%; text-align: right; padding: 10px; border: 1px solid #ccc;'>
													DTR Coverage:
												</td>
												<td style='font-size: 14px;font-weight: bold; border: 1px solid #ccc; padding: 13px; text-align: center;'>
													{$coverage}
												</td>
											</tr>
											{$accom_view}
											<tr>
												<td style='width:43%; text-align: right; padding: 10px; border: 1px solid #ccc;'>
													Approved By:
												</td>
												<td style='font-size: 14px;font-weight: bold; border: 1px solid #ccc; padding: 13px; text-align: center;'>
													not yet approved
												</td>
											</tr>
											<tr style=''>
												<td colspan=2 style='text-align: center; padding: 10px 5px;'>";
										
												$m .= "<a href='".base_url()."dtr/approval/".$vercode."/".$c_p."/".$alcrypted."' style='text-decoration:none;'>
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
													</a>";
													
											$m .= "<a href='".base_url()."/dtr/forapproval/".$c_p."/".$c_u."' style='text-decoration:none;'>
												    <p style='padding: 15px;
															margin: 0px auto;
															background: #efefef;
															border: 1px solid #b9b9b9;
															font-size: 16px;
															width: 83%;
															border-radius: 99px;'> View all DTR 
													</p>
													</a>";
													
			$m .= "									</td>
											</tr>
										</table>
									</td>
									<td rowspan=6 style='padding: 10px;border: 1px solid #ccc;background: #eaeaea;'>
										".urldecode($html)."
									</td>
								</tr>
								
							</table>
						</div>
						</div>"; //  {$cntid[0]->cntid}/{$sum_rep_id}/{$c_u}/{$c_p}

			$ret 	  = false;
			
			// check for an entry :: ($table, $field, $value)
			$found   = false;
			$d_found = $this->Globalproc->gdtf("countersign",["dtr_summary_rep"=>$sum_rep_id],"countersign_id");
				
				if(count($d_found) > 0) {
					$found = true;
				}
			// end checking for entry
			
			$cntid = false;
			if ($found == false) { // start of saving to countersign
				$ret 	  = $this->Globalproc->__save("countersign", ["bodycode" 	   	  => urlencode($html),  //urlencode($m),
																	  "emp_id"   	   	  => $emp_id,
																	  "approval_status"   => $isapproved,//$isapproved,
																	  "tobeapprovedby"    => $chiefid,
																	  "last_approving"	  => $dbm_id,
																	  "dtr_summary_rep"   => $sum_rep_id,
																	  "vercode" 		  => $vercode,
																	  "hrnotified"		  => 0]);
				$cntid    = $this->Globalproc->getrecentsavedrecord("countersign", "cntid");
			// alvin here
			// start of updating countersign
			
			} else {	// table, values, where
				$update_vals  = ["tobeapprovedby"  => $chiefid, 
								 "last_approving"  => $dbm_id, 
								 "approval_status" => 1,//$isapproved,
								 "vercode" 		   => $vercode];
				$update_where = ["dtr_summary_rep" => $sum_rep_id];
				$ret 	  	  = $this->Globalproc->__update("countersign",$update_vals,$update_where);
			}
			
			// end of updating countersign
			
			if($ret && $bypass_email == false) {
				
				//$chief_email = trim($chief_email," "); '"'.
				//===================== send email ===========================
				
				$ret = $this->Globalproc->sendtoemail(["to"		 => $chief_email, 
													   "from"	 => strtoupper($fullname),
													   "subject" => "DTR: for your approval ( ".strtoupper($fullname)." )",
													   "message" => $m]); 
				
				//===================== send email ===========================
				if ($cntid != false) {
					$as_dets = [
						"asdtr_sumrep"	=> $sum_rep_id,
						"cnt_id"		=> $cntid[0]->cntid,
						"emp_id"		=> $emp_id,
						"status"		=> 0
					];
					$ret = $this->Globalproc->__save("allowedsubmit",$as_dets);
				} else {
					$as_update = [
						"status"	=> 0
					];
					$as_where = ["asdtr_sumrep" => $sum_rep_id];
					$ret = $this->Globalproc->__update("allowedsubmit",$as_update,$as_where);
				}
				/*
				$msg_p = "<div style='width: 100%;
									  background: #e0e0e0;
									  padding: 7px; font-size: 18px;'>
									<div style='width: 30%;
												margin: 10px auto;
												background: #fff;
												border: 1px solid #ccc;'>
										<div style='padding:10px;padding: 22px;background: #f1f1f1;border-bottom: 1px solid #ccc;'>
											<h3 style='margin:0px; font-family: calibri;'> DTR for your perusal </h3>
										</div>
										<div style='padding: 17px; font-family: calibri;'>
											<p style='margin:0px;'> Hi HR, </p>
											<p> Im sending to you my DTR for your evaluation. </p>
											<span> Regards, </span> <br/>
											<span> {$fullname} </span>
										</div>
										<div style='padding: 13px;
													background: #f1f1f1;
													border-top: 1px solid #ccc;'>
											<p style='margin:0px; text-align:center; font-family: calibri;'> Please <a href='#'>login</a> to your HRIS  </p>
										</div>
									</div>
								</div>";
				
				if ($ret) {
					$ret = $this->Globalproc->sendtoemail(["to"		 => "hr@minda.gov.ph",//"hr@minda.gov.ph",
														   "from"	 => $fullname,
														   "subject" => "DTR: for your perusal",
														   "message" => $msg_p]);
				}
				*/
				
			}
			
			// ==========================
				// record to activity
					
				// end
			// ==========================
			
			echo json_encode($ret);
			
		}
		
		public function date__() {
			echo date("m/d/Y");
		}
		
		public function resetdtr($sumrepid = '', $empid = '') {
			$this->load->model("v2main/Globalproc");
			$this->load->model("Globalvars");
			
			if ($empid != '') {
				if ($empid != $this->Globalvars->employeeid) {
					die("You cannot touch a dtr that is not yours. Go back!!!!!");
					return;
				}
			} else {
				die("employee is not recognized");
			}
			
			if ($sumrepid == '') {
				die("cannot proceed with an empty summary reports ID");
				return;
			}
						
			
			
			$dsr = $this->Globalproc->gdtf("dtr_summary_reports",["sum_reports_id"=>$sumrepid],["sum_reports_id"]);
			
			if (count($dsr)==0) {
				die("The information you provided is not in the database. Clearly this DTR is not yet submitted.");
				return;
			}
			
			$delete   = "delete from dtr_summary_reports where sum_reports_id = '{$sumrepid}' and employee_id = '{$empid}'";
			
			$isdelete = $this->Globalproc->run_sql($delete);
			// $empcl    = $this->Globalproc->delete_leavecredit($sumrepid,$empid);
			// :: delete from employee leave credits
			
			if ($isdelete) {
				redirect(base_url()."my/dtr","refresh");
			} else {
				die("the page encountered some error.");
			}
		}
		
		public function dtr() { // personal dtr of each employee
			
			if($this->session->userdata('is_logged_in')!=TRUE){
				  redirect('/accounts/login/', 'refresh');
			}else{

			$this->load->model("personnel_model");
			$this->load->model("admin_model");
			$this->load->model("v2main/Globalproc");
			
			$data['title'] = '| Daily Time Records';
			
			$this->load->model("Globalvars");
			$data['admin'] = ($this->Globalvars->usertype != "user")?true:false;
			
			$data['biometric_id'] 	   = $this->session->userdata('biometric_id');
			$data['employee_id'] 	   = $this->session->userdata('employee_id');
			$data['usertype'] 		   = $this->session->userdata('usertype');
			$data['dbm_sub_pap_id']    = $this->session->userdata('dbm_sub_pap_id');
			$data['division_id'] 	   = $this->session->userdata('division_id');
			$data['level_sub_pap_div'] = $this->session->userdata('level_sub_pap_div');
			$data['employment_type']   = $this->session->userdata('employment_type');
			$data['is_head'] 		   = $this->session->userdata('is_head');
			
			
			$empdata 					  = $this->Globalproc->get_details_from_table("employees",
																					 ["employee_id"=>$data['employee_id']],
																					 ['e_signature','area_id']);
			
			$data['signature']["emp_sig"] = $empdata['e_signature'];
			
		//	$chief = $this->Globalproc->gdtf("dtr_summary_reports",[''], $details)
			// chief_sig	
			
			$getemployees = $this->admin_model->getemployees();
			
			$getareas = $this->admin_model->getareas();

			$users = array();
		
			foreach ($getemployees as $rr) {
				$users[] = array('userid' => $rr->biometric_id , 'name' => $rr->f_name);
			}
			
			$data['areas'] 		 = $getareas;
			$data['margin_left'] = true;

			$data['sub_pap_division_tree'] = $this->personnel_model->getsubpap_divisions_tree();
			$data['dtrformat'] 	 		   = $this->admin_model->getdtrformat();
			$data['dbusers'] 	 		   = $getemployees;
			
			$data['signatories'] = $this->Globalproc->get_signatories($data['employee_id']);

			$data['ischief']	 = $this->Globalproc->is_chief("division",$data['employee_id']);
			$data['isdirector']	 = $this->Globalproc->is_chief("director",$data['employee_id']);
			$data['isamo']		 = $empdata['area_id'];
			
			$data['print_hide']  = true;
			// check if has an unsubmitted DTR
				// check in countersign 
				$prev_dtr_status = $this->Globalproc->check_prev_dtr( $data['employee_id'] );
				$coverage 		 = $this->Globalproc->checkforactive_coverage( $data['employment_type'] );
				
				$is_in_cs		 = $this->Globalproc->checkin_countersign($coverage[0]->dtr_cover_id,$data['employee_id']);
				
				if (is_array($is_in_cs)){
					if (count($is_in_cs[0])==0) {
						$deleted = $this->Globalproc->delete_leavecredit($is_in_cs[1] , $data['employee_id']); // delete from the employee leave credits table
						echo "<a href='".base_url()."my/resetdtr/{$is_in_cs[1]}/{$data['employee_id']}' style='color:#fff;'>
								<p class='resetdtrbtn'> 
									Your dtr encountered some error. Relax. Click this link to reset it and then you can send again.
								</p>
							  </a> ";
					}
				} else {
					echo "<p style='margin: 0px; text-align: center; padding: 7px; background: #b5f9f2;'> <i class='fa fa-check-circle' aria-hidden='true'></i> Please submit your DTR. </p>";
				}
			/*
				if ($coverage[0]->is_allow_to_submit == 1 || $coverage[0]->date_deadline >= date("m/d/Y")) {
					
				}
			*/

				if (count($prev_dtr_status)>0) {

					echo "<div class='notification_div'>";
					echo "<div class='hrnotify_div'>";
						//echo "<p> You have a pending DTR submitted to HR. You are only allowed to submit again when the previous one is allowed to go. </p>";
						echo "<p style='font-size: 35px; padding-bottom:0px;'> <i class='fa fa-exclamation-triangle'></i>  </p>";
						echo "<p style='padding-top:0px;'> DTR is on process <br/>
								<span style='font-size: 14px; font-style: italic;'> Reminder: Please settle your DTR before the deadline or you wont be able to submit it, therefore you won't get paid for your job. </span>	
							 </p>";
					echo "</div>";
					
					// look for some findings in the findings table 
						$findings 	   = $this->Globalproc->gdtf("allowedsubmit",["emp_id"=>$data['employee_id'],"conn"=>"and","status"=>1],"*");
						
						$email_status  = $this->Globalproc->gdtf("countersign",["countersign_id"=>$prev_dtr_status[0]->countersign_id],["approval_status","tobeapprovedby","last_approving","dtr_summary_rep"]);
						
						if (count($findings)>0) {
							$the_findings = json_decode($findings[0]->findings);
							// $evaluator    = $this->Globalproc->gdtf("employees",['employee_id'=>$findings[0]->correctedby],"f_name")[0]->f_name;
							
							$eval_dets  = ["department_id" =>19,
										   "conn"		   =>"and",
										   "is_head"	   =>1];
							$evaluator  = $this->Globalproc->gdtf("employees",$eval_dets,["f_name","email_2"]);
							$eval_email = null;
							
							if (count($evaluator)==0) {
								$eval_dets = ["department_id" => 22,
											  "conn"		  => "and",
											  "is_head" 	  => 1];
								$evaluator  = $this->Globalproc->gdtf("employees",$eval_dets,["f_name","email_2"]);
								
								$eval_email = $evaluator[0]->email_2;
								$evaluator  = $evaluator[0]->f_name;
							} else {
								$eval_email = $evaluator[0]->email_2;
								$evaluator = $evaluator[0]->f_name;
							}
							
							echo "<div class='boxwrapper' style='overflow:hidden;'>";
								echo "<div class='box_findings'>";
									echo "<h4 class='header_ff'> Your DTR needs fixing. Please fix the following </h4>";
									echo "<ul>";
										for($i = 0; $i <= count($the_findings)-1; $i++) {
											echo "<li>";
												echo "<strong> {$the_findings[$i]->date} </strong>";
												echo "<p> {$the_findings[$i]->msg} </p>";
											echo "</li>";
										}
									echo "</ul>";
									//05/04/2018 :: format
									// check if allowed to submit or not or the deadline has met
																		
									//echo $coverage[0]->is_allow_to_submit;
									// echo $coverage[0]->date_deadline; echo "|";
									// echo date("m/d/Y");
									
									if ($coverage[0]->is_allow_to_submit == 1 && $coverage[0]->date_deadline >= date("m/d/Y")) {
										echo "<button class='btn btn-primary' style='margin: 10px 0px;' 
													  id='resubmittohr' 
													  data-as_id = '{$findings[0]->as_id}' 
													  data-db_from = '{$findings[0]->from_db}'> Send Back </button>";
									} else {
										// echo "<p class='errormsg_text'> I'm sorry, deadline have passed but you haven't submitted your DTR. The submission is now closed. </p>";
										echo "<p class='errormsg_text'> Deadline have passed. The submission is now closed.</p>";
									}
									// end
									
									echo "<p class='evaluatedby'> evaluated by: {$evaluator}</p>";
								echo "</div>";		
							echo "</div>";		
						}
					// end 
					
					$submitted_dtr_details = $this->Globalproc->gdtf("dtr_summary_reports",["sum_reports_id"=>$email_status[0]->dtr_summary_rep],["dtr_coverage","date_submitted"]);
					
					echo "<p style='margin: 0px; padding: 5px 5px 5px 40px;'> Submitted DTR: &nbsp; <strong> {$submitted_dtr_details[0]->dtr_coverage} </strong> </p>";
					echo "<p style='margin: 0px; padding: 5px 5px 5px 40px;'> Date Submitted: &nbsp; <strong> ".date("F d, Y", strtotime($submitted_dtr_details[0]->date_submitted))." </strong> </p>";
					// echo "<p style='margin: 0px; padding: 5px 5px 5px 40px;'> Deadline: &nbsp; <strong> ".date("l F d, Y", strtotime($coverage[0]->date_deadline))."</strong> </p>";
					
						if ($coverage[0]->is_allow_to_submit == 1 && $coverage[0]->date_deadline >= date("m/d/Y") && count($findings) == 0) {
							if ($email_status[0]->approval_status == 0) { // not approved by chief, send to chief
								echo "<div class='notyetapproved_initial'>";
									echo "<p style='text-align: center; font-size: 18px; background: #bb6c0a; color: #fff;'> DTR: for initial signature. </p> ";
									echo "<p id='sendtochief' class='sendingstatushere'
											 data-cntid = '{$prev_dtr_status[0]->countersign_id}'
											 data-sendto = '{$email_status[0]->tobeapprovedby}'> <i class='fa fa-bell'></i> &nbsp;Click here to re-send your DTR. </p>";
									echo "<p id='sendtonewchief' 
											 data-dtrsumrep='{$email_status[0]->dtr_summary_rep}'> <i class='fa fa-paper-plane'></i> &nbsp; Click here to send your DTR to a different signatory</p>";
								echo "</div>";
							} else if ($email_status[0]->approval_status == 1) { // approved by chief, send to last approving official
								echo "<div class='notyetapproved_final'>";
									echo "<p style='text-align: center; font-size: 18px; background: #248a5f; color: #fff;'> DTR: for final signature. </p>";
									echo "<p id='sendtolast' class='sendingstatushere'
											 data-cntid = '{$prev_dtr_status[0]->countersign_id}'
											 data-sendto = '{$email_status[0]->last_approving}'> <i class='fa fa-bell'></i> &nbsp; Click here to notify. </p>";
									echo "<p id='sendtonewchief' 
											 data-dtrsumrep='{$email_status[0]->dtr_summary_rep}'> <i class='fa fa-paper-plane'></i> &nbsp; Send DTR to a different signatory</p>";
								echo "</div>"; 
							} 
							else if ($email_status[0]->approval_status == 2) {
								echo "<p class='withthehr'> &nbsp; Your DTR is ready for printing.  </p>";
							}
							
						} else {
							//if ($email_status[0]->approval_status == 2) {
								 echo "<p class='subover'> &nbsp; Submission of DTR is over. </p>";
							//} 
						}
						
						
							
						//echo "<div style='border-top: 1px solid #ccc; padding: 10px; text-align: right;'> <button class='btn btn-default' id='hidewindow'> Hide Window </button> </div>";
						echo "<div style='border-top: 1px solid #ccc; padding: 10px; overflow:hidden;'>";
						if ($coverage[0]->is_allow_to_submit == 1 && $coverage[0]->date_deadline >= date("m/d/Y")) {
							/*
							echo "<a id='canceldtr' style='cursor:pointer; float:left; background: #9c0f28;' class='btn btn-danger btn-sm'
										data-cntid='{$prev_dtr_status[0]->countersign_id}'
										data-dtrsumrep='{$email_status[0]->dtr_summary_rep}'> DELETE THIS DTR </a> ";
									//	<i ></i>
							*/
						}
						echo "	<a id='hidewindow' style='cursor:pointer; float:right;'> <i class='fa fa-compress' aria-hidden='true'></i>  Hide this pop-up window </a> 
							  </div>
						</div>";
					
					
					$data['cantsubmit'] = true;
				}
			// 
			
			
			
			$data['headscripts']['style'][0]  = base_url()."v2includes/style/hr_dashboard.style.css";
			
			echo "<div class='showwindow' id='showwindow'>
						<div id='pop_upwindow'>
							
						</div>
					</div>";
					
			$data['main_content'] = 'v2views/dtr_new_view';
			$this->load->view('hrmis/admin_view',$data);

			}
		}
		
		public function canceldtr() {
			// allowedsubmit
			// countersign
			// dtr_summary_reports
			
			// cntid_
			// sumrep_ 
			
			$info    = $this->input->post("info");
			
			$cntid   = $info['cntid_'];
			$sumrep  = $info['sumrep_'];
			
			/*
			$cntid   = 2308;
			$sumrep  = 2357;
			*/
			$this->load->model("v2main/Globalproc");
			
			$values = [$sumrep,$cntid];
			$tables = [
				['countersign',"null",'countersign_id'],
				['dtr_summary_reports','sum_reports_id',"null"],
				['allowedsubmit','asdtr_sumrep','cnt_id']
			];
			
			$sql 	   = null;
			$isdeleted = false;
			
			for($i=0;$i<=count($tables)-1;$i++) {
				$sql = "";
					$sql .= "delete from {$tables[$i][0]} where ";
					
					for($t=1,$x = 0;$t<=count($tables[$i])-1;$t++,$x++) {
						if ($tables[$i][$t] != "null") {
							$sql .= $tables[$i][$t] ."='". $values[$x]. "'"; 
							if ($t < count($tables[$i])-1 && $tables[$i][$t+1] != "null") {
								$sql .= " and ";
							}
						}
					}
				// echo $sql . "<br/>";
				
				if ($this->Globalproc->run_sql($sql)) {
					$isdeleted = true;
				} else {
					$isdeleted = false;
					break;
				}
				
			}
			
			echo json_encode($isdeleted);
			
		}
		
		public function documents() {
			$data['title'] = '| My Documents';
			
			$data['headscripts']['js'][0] 		= base_url()."v2includes/js/upload.js";
			$data['headscripts']['style'][0] 	= base_url()."v2includes/style/documents.style.css";
			
			$data['main_content'] = 'v2views/documents';
			$this->load->view('hrmis/admin_view',$data);
		}
		
		public function _upload_docs() {
			if (isset($_POST['uploaddocs'])) {
				$target_dir = "uploads/";
				$target_file = $target_dir . basename($_FILES["attachments_"]["name"]);
				
			}
		}
		
		public function uploads() {
			// called from an ajax request

			if ( 0 < $_FILES['file']['error'] ) {
				echo 'Error: ' . $_FILES['file']['error'] . '<br>';
			}
			else {
				move_uploaded_file($_FILES['file']['tmp_name'], './assets/documents/' . $_FILES['file']['name']);
			}
			echo json_encode("uploaded");

		}
		
		public function remaining() {
			$this->load->model("v2main/Globalproc");
			
			$details = $this->input->post("info");
			
			$empid   = $details['empid']; // 62
			$type    = $details['type']; //"FL";
			
			/*
			$empid   = 62; // 62
			$type    = "FL"; //"FL";
			*/
			
			$ret = $this->Globalproc->return_remaining($type,$empid);
			echo json_encode($ret);
			
		}
		
		public function remaining_spl() {
			
		}
		
		public function attach_accom() {
			$accom = $this->input->post("info");
			$accom = $accom['details'];
			
			$this->load->model("v2main/Globalproc");
			$this->load->model("Globalvars");
		
			$cover_id   = $accom["coverid"];
			$from_ 		= $accom["from_"];
			$to_ 		= $accom["to_"];
			
			$user_id    = $this->Globalvars->employeeid;
			
			// __createuniqueid($word)
			$spl_grp_id = $this->Globalproc->__createuniqueid($cover_id.$from_.$to_);
			
			$update_accom_sql = "update d_accomplishment 
									set coverage_id = '{$cover_id}', 
										spl_grp_id = '{$spl_grp_id}'
									where date between '{$from_}' and '{$to_}'
									and user_id = '{$user_id}'";
			
			$update_ = $this->Globalproc->run_sql($update_accom_sql);
			echo json_encode($update_);
			
		}
		
		public function accomplishments($isprint = '', $user_id = '', $r_from = '', $r_to = '', $s_emp = '') {
			//$this->session->set_userdata('database_default', 'sqlserver');
				/*
			if($this->session->userdata('is_logged_in') != TRUE){
				$this->session->set_userdata('database_default', 'sqlserver');
				echo "<p> Please login <a href='{base_url()}/accounts/login'>here</a> to view this accomplishment report. </p>";
				// return;
			} else {
				*/
			/*
			if($this->session->userdata('is_logged_in') != TRUE){
				$DB2   = $this->load->database('sqlserver', TRUE);
				
				$query = "select * from d_accomplishment where f_signatory = '{$s_emp}'";
				
				$query  = $DB2->query($query);
				$result = $query->result();
				
				if(count($result)==0) {
					$q2 	= "select * from d_accomplishment where s_signatory = '{$s_emp}'";
					$query  = $DB2->query($query);
					$result = $query->result();
					
					if (count($result)==0) {
						die("User not recognized");
						return;
					}
				}
				
				$query = "Select * from users where employee_id = '{$s_emp}'";
				$query = $DB2->query($query);
				
				$result = $query->result();
				
				if (count($result) == 0) {
					die("No user found");
					return;
				}
				
				$this->load->model("Login_model");
				$result2      = $this->Login_model->getUserInformation($s_emp);
				
				$user_session = array(
					'employee_id' => $s_emp,
					'username' => $result[0]->Username,
					'usertype' => $result[0]->usertype,
					'full_name' => $result2[0]->f_name,
					'first_name' => $result2[0]->firstname,
					'last_name' => $result2[0]->l_name,
					'biometric_id' => $result2[0]->biometric_id,
					'area_id' => $result2[0]->area_id,
					'area_name' => $result2[0]->area_name,
					'ip_address' => $_SERVER["REMOTE_ADDR"],
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
				);
				
				$this->session->set_userdata($user_session);
				
 			}
			*/
			$notloggedin = false;
			if($this->session->userdata('is_logged_in') != TRUE){
				// echo "<p> Please login <a href='{base_url()}/accounts/login'>here</a> to view this accomplishment report. </p>";
				// return;
				$notloggedin = true;
			} 
			
			$this->session->set_userdata('database_default', 'sqlserver');
			
			$this->load->model('Globalvars');
			$this->load->model("Globalproc");
			$this->load->model('main/main_model');
						
			$data['title'] = "| Accomplishment";
			
			$data['headscripts']['style'][] = base_url()."v2includes/style/accomplishments.style.css";
						
			// get all signs
			$get_from = $this->Globalvars->employeeid;
			if ($user_id != '') {
				$get_from = $user_id;
			}
			
			$signs 		= $this->Globalproc->get_signatories( $get_from );
			
			// ============ get division ==============
				$division 	    		= "select Division_Id, area_id from employees where employee_id = '{$get_from}'";
				$data['division_data']  = $this->main_model->array_utf8_encode_recursive( $this->Globalproc->__getdata($division) );
			// ============ end get division ==========
			
			// signatories
			$data['division']		= $signs['division'];
				$div_sql 			= "select position_name, Division_Desc from employees as e 
									   JOIN positions as p on e.position_id = p.position_id 
									   JOIN Division as d on e.Division_id = d.Division_Id
									   where e.employee_id = '{$signs['division']['div_empid']}'";
				$data['div_data']   = $this->main_model->array_utf8_encode_recursive( $this->Globalproc->__getdata($div_sql) );
			
			$data['dbm']			= $signs['dbm'];
				$dbm_sql 			= "select position_name, DBM_Sub_Pap_Desc from employees as e 
									   JOIN positions as p on e.position_id = p.position_id 
									   JOIN DBM_Sub_Pap as d on e.DBM_Pap_id = d.DBM_Sub_Pap_id
									   where e.employee_id = '{$signs['dbm']['dbm_empid']}'";
				
				$data['dbm_data']   = $this->main_model->array_utf8_encode_recursive( $this->Globalproc->__getdata($dbm_sql) );
				
			// if other than signatories
			$data['division_other']			 = $signs['division_other'];
			$data['dbm_other']			 	 = $signs['dbm_other'];
			
			$month 	  = date("n");
			$day   	  = date("j");
			$year  	  = date("Y");
			
				if (isset($_POST['view_accom'])) {
					$month 	  = $_POST['month_select'];
					$day   	  = $_POST['day_select'];
					$year  	  = $_POST['year_select'];
					
				}
			
			$data['selected_day']   = $day;
			$data['selected_year']  = $year;
			$data['selected_month'] = $month;
			
			$the_date = $month."/".$day."/".$year;
			
			
			$data['accom_this_day'] = $this->main_model->array_utf8_encode_recursive($this->Globalproc->gdtf("d_accomplishment",
																					 ["date"	=> $the_date,
																					  "conn"    => "and",
																					  "user_id" => $this->Globalvars->employeeid],
																					  "*") );
			
			// get approval status
			$data['div_sig'] 		= null;
			$data['dbm_sig']		= null;
			
			// end get the approval status
			
				if (isset($isprint) && $isprint == "print") {
					$data['headscripts']['js'][]  = base_url()."v2includes/js/accomprint.js";
					
					// personal information 
						$personal_sql  		   = "select f_name,position_name,e_signature from employees as e JOIN positions as p on e.position_id = p.position_id where e.employee_id = '{$this->Globalvars->employeeid}'";
						$data['personal_info'] = $this->main_model->array_utf8_encode_recursive( $this->Globalproc->__getdata($personal_sql) );
						
				
					// end 
					
					if (isset($_POST['print_accom'])) {
						$from_ 	= date("m/d/Y", strtotime($_POST['from_']));
						$to_    = date("m/d/Y", strtotime($_POST['to_']));
						
						$sql = "select * from d_accomplishment where date between '{$from_}' AND '{$to_}' and user_id = '{$this->Globalvars->employeeid}' order by date ASC";
						
						$data['accomplishments'] = $this->main_model->array_utf8_encode_recursive( $this->Globalproc->__getdata($sql) );
						
						if (count($data['accomplishments']) == 0) {
							echo "no data found. go back."; return;
						}
						
						//=================================================================================================
							if ($data['accomplishments'][0]->f_action == 1) {
								$div_sig 		 = $this->Globalproc->gdtf("employees",
																			  ['employee_id'=>$data['accomplishments'][0]->f_signatory],
																			   ['e_signature','f_name']);
									$data['div_sig']  = $div_sig[0]->e_signature;
									$data['div_name'] = $div_sig[0]->f_name;
									
									$div_sql 			= "select position_name, Division_Desc from employees as e 
														   JOIN positions as p on e.position_id = p.position_id 
														   JOIN Division as d on e.Division_id = d.Division_Id
														   where e.employee_id = '{$data['accomplishments'][0]->f_signatory}'";
									$data['div_data']   = $this->main_model->array_utf8_encode_recursive( $this->Globalproc->__getdata($div_sql) );
							}
								
							if ($data['accomplishments'][0]->s_action == 1) {
								$dbm_sig 		  = $this->Globalproc->gdtf("employees",
																			['employee_id'=>$data['accomplishments'][0]->s_signatory],
																			['e_signature','f_name']);
								$data['dbm_sig']  = $dbm_sig[0]->e_signature;
								$data['dbm_name'] = $dbm_sig[0]->f_name;
								
								$dbm_sql 			= "select position_name, DBM_Sub_Pap_Desc from employees as e 
													   JOIN positions as p on e.position_id = p.position_id 
													   JOIN DBM_Sub_Pap as d on e.DBM_Pap_id = d.DBM_Sub_Pap_id
													   where e.employee_id = '{$data['accomplishments'][0]->s_signatory}'";
				
								$data['dbm_data']   = $this->main_model->array_utf8_encode_recursive( $this->Globalproc->__getdata($dbm_sql) );
							}
						//=================================================================================================
						
						$emp_data 			= $this->Globalproc->gdtf("employees",['employee_id'=>$get_from],['e_signature','area_id']);
						$data['emp_sig']	= $emp_data[0]->e_signature;
						$data['area']		= $emp_data[0]->area_id;
							
						$dtr_coverage = "select * from hr_dtr_coverage as hdc
										where hdc.is_active = 'true' and is_allow_to_submit = 'true' ";
						
						
						// data
						$data['showshare'] = true;
						$data['empid']	   = $empid = $this->Globalvars->employeeid;
						$data['fname']     = $data['personal_info'][0]->f_name;
						// end 
						
						$data['from_']     = $from_;
						$data['to_'] 	   = $to_; 
						
						$newf = str_replace("/","-",$from_);
						$newt = str_replace("/","-",$to_);
						// shareable link
							// /my/accomplishments/viewing/99/06-01-2020/06-30-2020
							$data['link']  = base_url()."my/accomplishments/viewing/{$empid}/{$newf}/{$newt}";
						// end 
						
						$data['get_cover'] = $this->Globalproc->__getdata($dtr_coverage);
						
					}
					
					$data['main_content'] 		  = 'v2views/accom_print';
				} else if (isset($isprint) && $isprint == "viewing") {
					// allow no password here
					// date from 
					// date to
					// user id
					// $user_id = '', $r_from = '', $r_to = ''
				// http://office.minda.gov.ph:9003/my/accomplishments/viewing/389/01-29-2018/02-08-2018
					//$from_  = date("m/d/Y", strtotime($r_from));
					//$to_ 	= date("m/d/Y", strtotime($r_to));
					
					$from_  = $r_from;
					$to_    = $r_to;

					// $user_id = "389";
					// personal information 
						$personal_sql  		   = "select f_name,position_name,e_signature, area_id from employees as e JOIN positions as p on e.position_id = p.position_id where e.employee_id = '{$user_id}'";
						$data['personal_info'] = $this->main_model->array_utf8_encode_recursive( $this->Globalproc->__getdata($personal_sql) );
						$data['area']		   = $data['personal_info'][0]->area_id;
					// end

					$sql 					   = "select * from d_accomplishment where date between '{$from_}' AND '{$to_}' and user_id = '{$user_id}' order by date ASC";
				
				//	echo $sql;
					$data['accomplishments']   = $this->main_model->array_utf8_encode_recursive( $this->Globalproc->__getdata($sql) );
					
					if (count($data['accomplishments'])>=1) {
						
						//=================================================================================================
							if ($data['accomplishments'][0]->f_action == 1) {
									$div_sig 		 = $this->Globalproc->gdtf("employees",
																			  ['employee_id'=>$data['accomplishments'][0]->f_signatory],
																			   ['e_signature','f_name']);
									$data['div_sig']  = $div_sig[0]->e_signature;
									$data['div_name'] = $div_sig[0]->f_name;
									
									$div_sql 			= "select position_name, Division_Desc from employees as e 
														   JOIN positions as p on e.position_id = p.position_id 
														   JOIN Division as d on e.Division_id = d.Division_Id
														   where e.employee_id = '{$data['accomplishments'][0]->f_signatory}'";
									$data['div_data']   = $this->main_model->array_utf8_encode_recursive( $this->Globalproc->__getdata($div_sql) );
							}
							
							if ($data['accomplishments'][0]->s_action == 1) {
								$dbm_sig 		  = $this->Globalproc->gdtf("employees",
																			['employee_id'=>$data['accomplishments'][0]->s_signatory],
																			['e_signature','f_name']);
								$data['dbm_sig']  = $dbm_sig[0]->e_signature;
								$data['dbm_name'] = $dbm_sig[0]->f_name;
								
								$dbm_sql 			= "select position_name, DBM_Sub_Pap_Desc from employees as e 
													   JOIN positions as p on e.position_id = p.position_id 
													   JOIN DBM_Sub_Pap as d on e.DBM_Pap_id = d.DBM_Sub_Pap_id
													   where e.employee_id = '{$data['accomplishments'][0]->s_signatory}'";
				
								$data['dbm_data']   = $this->main_model->array_utf8_encode_recursive( $this->Globalproc->__getdata($dbm_sql) );
							}
						//=================================================================================================
					}
					
					// echo "<img src='".base_url()."/assets/esignatures/{$data['dbm_sig']}'/>";
					$data['emp_sig']	= $this->Globalproc->gdtf("employees",['employee_id'=>$get_from],"e_signature")[0]->e_signature;
					
					$data['isviewing']			  = true;
					$data['showshare'] 			  = true;
					$data['notloggedin']		  = $notloggedin;
					$data['main_content'] 		  = 'v2views/accom_print';
				} else {
					$data['headscripts']['js'][]  = base_url()."v2includes/js/accom.js";
					$data['main_content'] = 'v2views/accomplishments';
				}
			// end
			
			
			// if saving of accomplishments is set
				if (isset($_POST['proceed_accom_save'])) {
					
					
					$month = $_POST['month_select'];
					$day   = $_POST['day_select'];
					$year  = $_POST['year_select'];
					
					$accom_text = urlencode($_POST['accom_text']);
										
					$division   = $_POST['division_certified'];
					$dbm 		= $_POST['dbm_approved'];
					
					$thedate 	= $month."/".$day."/".$year;
					$values = [
						"date" 				=> $thedate,
						"accomplishment"	=> $accom_text,
						"user_id"			=> $this->Globalvars->employeeid,
						"f_signatory"		=> $division,
						"s_signatory"		=> $dbm
					];
					
					$info = $this->Globalproc->gdtf("d_accomplishment",["date"=>$thedate,"conn"=>"and","user_id"=>$this->Globalvars->employeeid],"*");
					
					if (count($info)>=1) {
						// update
						$data['saving_message'] = $this->Globalproc->__update("d_accomplishment",$values,['user_id'=>$this->Globalvars->employeeid,"conn"=>"and","date"=>$thedate]);
					} else {
						// save 
						$data['saving_message'] = $this->Globalproc->__save("d_accomplishment",$values);
					}
				}
			// end 
			
		
			
				$this->load->view('hrmis/admin_view',$data);
			// } end of else
		}
		
		public function sendlink() {
			// sendtoemail
			$this->load->model("v2main/Globalproc");
			
			$ds    = $this->input->post("info")['details'];
			
			$link  = $ds['link'];
			$eadds = $ds['eadds'];
			$fname = $ds['fname'];
			
			$message = "<h3> I'm sharing to you my accomplishment report. </h3>";
			$message .= "<p>".$link."</p>";
			$message .= "<br><br>
						 <hr> 
						 <p> This is a system generated email. Please do not reply or use to the email address minda.smtpsender@gmail.com as a recipient address when sending personal email to this person. </p>";
			
		//	$eadds = "alvinjay.merto@minda.gov.ph";
			
			$details = ["to" 	  => $eadds,
						"from" 	  => $fname,
						"message" => $message,
						"subject" => "Sharing you my accomplishment Report"];
			
			/*
			$details["to"] 	 	= $eadds;
			$details["from"] 	= "Sample";
			$details["message"] = $message;
			$details["subject"] = "Sharing you my accomplishment Report";
			*/
			
		//	var_dump($details);
			
			$d = $this->Globalproc->sendtoemail($details);
			
			$msg = null;
			if ($d) {
				$msg = "Message sent";
			} else {
				$msg = "error sending message";
			}
			echo json_encode($msg);
		}
		
		public function removeaccom() {
			$this->load->model("Globalproc");
			$this->load->model("Globalvars");
			
			$thedate = $this->input->post("info")['thedate'];
			$userid  = $this->Globalvars->employeeid;
			
			$sql = "DELETE from d_accomplishment where user_id = '{$userid}' and date='{$thedate}'";
			
			echo json_encode( $this->Globalproc->run_sql($sql) );
		}
		
		public function getcto_ot() {
			$this->load->model("v2main/Globalproc");
			
			$a 		= $this->input->post("info");
			$empid  = $a['empid'];
				
			//$empid = 389;
			
			$sql   = "select eot.*,e.f_name from employee_ot_credits as eot
					  JOIN employees as e on eot.emp_id = e.employee_id
					  where eot.emp_id = '{$empid}' ORDER BY eot.elc_otcto_id DESC";
			$data  = $this->Globalproc->__getdata($sql);
			
			// $data = [1,2,3,4,5];
			$b = array_map(function($a){
				$seconds 		 = $a->total_credit;
				$dtF     		 = new \DateTime('@0');
				$dtT 	 		 = new \DateTime("@$seconds");
				$a->total_credit = $dtF->diff($dtT)->format('%a:%h:%i');	
				
				return $a;
			},$data);
			
			echo json_encode($b);
		}
		
		public function save_ot_accom() {
			$data 		  = $this->input->post("info");
			$data 		  = $data['ssave'];
			
			$grp_id 	  = $data['grp_id'];
			$am_timein    = $data['am_timein'];
			$am_timeout   = $data['am_timeout'];
			$pm_timein    = $data['pm_timein'];
			$pm_timeout   = $data['pm_timeout'];
			$acc_report   = $data['acc_report'];
			
			/*
			$acc_report  = "%3Cul%3E%3Cli%3E%u200Byuiyui%3Cbr%3E%3C/li%3E%3Cli%3Eyuiyu%3C/li%3E%3C/ul%3E";
			$am_timein   = "12:00 AM";
			$am_timeout  = "12:00 AM";
			$grp_id		 = "1108";
			$pm_timein	 = "12:00 AM";
			$pm_timeout  = "12:00 AM";
			*/
			
			$this->load->model("v2main/Globalvars");
			$this->load->model("v2main/Globalproc");
			
			$empid  	  = $this->Globalvars->employeeid;
			
			// personal information 
				$personal_info = $this->Globalproc->gdtf("employees",["employee_id"=>$empid],["f_name","Division_id"]);
			// end 
			
			
			
			// get the approval status of OT
			// ================================================
				$dets = $this->Globalproc->gdtf("checkexact_ot",["checkexact_ot_id"=>$grp_id],["div_is_approved",
																							   "div_chief_id",
																							   "act_div_is_approved",
																							   "act_div_chief_id",
																							   "act_div_a_is_approved",
																							   "act_div_a_chief_id"]);
				if ($dets[0]->div_is_approved == 1) { // approved: chief level
					if ($dets[0]->act_div_is_approved == 1) { // approved: second level
						if ($dets[0]->act_div_a_is_approved == 1) {	// approved: last level
							if ($dets[0]->div_chief_id == $empid) {
								// send to last approving official
							}
							
							$approval_status = null;
							
							if ($personal_info[0]->Division_id == 0 
								|| $this->Globalproc->is_chief("division",$empid) 
								|| $this->Globalproc->is_chief("director",$empid)) {
								$approval_status = 1;
							} else {
								$approval_status = 0;
							}
							
							$details = [
								"am_timein"  	  => $am_timein,
								"am_timeout" 	  => $am_timeout,
								"pm_timein"  	  => $pm_timein,
								"pm_timeout" 	  => $pm_timeout,
								"accomplishment"  => $acc_report,
								"approval_status" => $approval_status,
								"fapproval"		  => $dets[0]->div_chief_id,
								"sapproval"		  => $dets[0]->act_div_chief_id,
								"lapproval"		  => $dets[0]->act_div_a_chief_id,
								"emp_id"		  => $empid,
								"ot_exact_id"	  => $grp_id,
								"date_added"	  => date("m/d/Y")
							];
							
							$ret = $this->Globalproc->__save("ot_accom",$details);
							
							if ($ret) {
								$ret_id = $this->Globalproc->getrecentsavedrecord("ot_accom", "returned_id");
								$ret_id = $ret_id[0]->returned_id;
								
								$approving_id = null;
								// email 
								$this->load->model("v2main/Emailtemplate");
								$email_dets = [
										"Type"	    			 => "Overtime Accomplishment Report",
										"Name"	    			 => $personal_info[0]->f_name,
										"Date"	    			 => date("m/d/Y"),
										"Work Accomplishment(s)" => urldecode($acc_report)
									]; 
								
								if ($am_timein != null || $am_timeout != null) {
									$email_dets['Morning'] 		= $am_timein." | ".$am_timeout;
								}
								
								if ($pm_timein != null || $am_timeout != null) {
									$email_dets['Afternoon']	= $pm_timein." | ".$pm_timeout;
								}
								
								$details = [
									"to"	  => null,
									"from"	  => strtoupper($personal_info[0]->f_name),
									"subject" => "Overtime Accomplishment Report: Needs Approval ",
									"message" => null
								];
								
								if ($approval_status >= 1) {
									// send to sapproval or lapproval
									$deta = $this->Globalproc->gdtf("employees",["employee_id"=>$dets[0]->act_div_a_chief_id],["email_2"]);
									$details['to']	= $deta[0]->email_2;
									$approving_id	= $dets[0]->act_div_a_chief_id;
								} else if ($approval_status == 0) {
									$deta 			= $this->Globalproc->gdtf("employees",["employee_id"=>$dets[0]->div_chief_id],["email_2"]);
									$details['to']	= $deta[0]->email_2;
									$approving_id	= $dets[0]->div_chief_id;
								}
								
								// approval($ot_exact = '', $approving_id = '', $ot_accom_id = '')
								$email_actions = [
												"ot_exact"	    => $grp_id,
												"approving_id"  => $approving_id,
												"ot_accom_id"  	=> $ret_id
											];
											
								$template 			= $this->Emailtemplate->ot_accom_template($email_dets,$email_actions);
								$details['message']	= $template;
								
								$emailed = $this->Globalproc->sendtoemail($details);
								if ($emailed) {
									echo json_encode(true);
								}
							}
						} else {
							echo json_encode(false);
						}
					} else {
						echo json_encode(false);
					}
				} else {
					echo json_encode(false);
				}
			// ================================================
			// end 
			
		}
		
		function resendot() {
			$ot_exact_id = $this->input->post("info")['otexact'];
			
			$this->load->model("v2main/Globalproc");
			$this->load->model("v2main/Emailtemplate");

			$sql = "select 
						oa.*,
						e.f_name 
					from ot_accom as oa
					JOIN employees as e on oa.emp_id = e.employee_id 
					where oa.ot_exact_id = '{$ot_exact_id}'";
		
			$accomdets = $this->Globalproc->__getdata($sql);
			
			$am_timein  = $accomdets[0]->am_timein;
			$am_timeout = $accomdets[0]->am_timeout;
			$pm_timein  = $accomdets[0]->pm_timein;
			$pm_timeout = $accomdets[0]->pm_timeout;
			
			$email_dets = ["Type"	    			 => "Overtime Accomplishment Report",
						   "Name"	    			 => $accomdets[0]->f_name,
						   "Date"	    			 => date("m/d/Y"),
						   "Work Accomplishment(s)"  => urldecode($accomdets[0]->accomplishment)];
			
			if (strlen($am_timein) > 0 || strlen($am_timeout) > 0) {
				$email_dets['Morning'] 		= $am_timein." | ".$am_timeout;
			}
			
			if (strlen($pm_timein) > 0 || strlen($am_timeout) > 0) {
				$email_dets['Afternoon']	= $pm_timein." | ".$pm_timeout;
			}
			
			$details = ["to"	  => null,
						"from"	  => strtoupper($accomdets[0]->f_name),
						"subject" => "Overtime Accomplishment Report: Needs Approval ",
						"message" => null];
						
			$approval_status = $accomdets[0]->approval_status;
			$act_div_chief   = $accomdets[0]->fapproval;
			$div_chief  	 = $accomdets[0]->sapproval;
			$approving_id 	 = null;
			$ret_id  		 = $accomdets[0]->ot_accid;
			
			if ($approval_status >= 1) {
				// send to sapproval or lapproval
				$deta = $this->Globalproc->gdtf("employees",["employee_id"=>$act_div_chief],["email_2"]);
				$details['to']	= $deta[0]->email_2;
				$approving_id	= $act_div_chief;
			} else if ($approval_status == 0) {
				$deta 			= $this->Globalproc->gdtf("employees",["employee_id"=>$div_chief],["email_2"]);
				$details['to']	= $deta[0]->email_2;
				$approving_id	= $div_chief;
			}
			
			$email_actions = ["ot_exact"	   => $ot_exact_id,
							  "approving_id"   => $approving_id,
							  "ot_accom_id"    => $ret_id];
		
			$template 			= $this->Emailtemplate->ot_accom_template($email_dets,$email_actions);
			$details['message']	= $template;
		
			$emailed = $this->Globalproc->sendtoemail($details);
			
			if ($emailed) {
				echo json_encode(true);
			} else {
				echo json_encode(false);
			}
		}

		function getot_accom() {
			$ot_accom = $this->input->post("info");
			$ot_accom = $ot_accom['otaccom'];
			
			$this->load->model("v2main/Globalproc");
			
			$dets = $this->Globalproc->gdtf("ot_accom",["ot_exact_id"=>$ot_accom],"*");
			
			echo json_encode($dets);
		}
		
		function delete_accom() {
			$ot_accid = $this->input->post("info");
			$ot_accid = $ot_accid['ot_accid'];
			
			$this->load->model("v2main/Globalproc");
			
			$del = "Delete from ot_accom where ot_accid = '{$ot_accid}'";
			
			$ret = $this->Globalproc->run_sql($del);
			
			echo json_encode($ret);
		}
		
		function changepass() {
			$info = $this->input->post("info");
			$pass = md5($info['pw']);
			
			$this->load->model("v2main/Globalvars");
			$this->load->model("v2main/Globalproc");
			
			$empid = $this->Globalvars->employeeid;
			
			$update = $this->Globalproc->__update("users",["Password"=>$pass,"isfirsttime"=>0],["employee_id"=>$empid]);
			
			echo json_encode($update);
		}
		
		function imokay() {
			$info = $this->input->post("info");
			$ok   = $info['ok'];
			
			$this->load->model("v2main/Globalvars");
			$this->load->model("v2main/Globalproc");
			
			$empid = $this->Globalvars->employeeid;
			$update = $this->Globalproc->__update("users",['isfirsttime'=>2],['employee_id'=>$empid]);
			
			echo json_encode($update);
		}
		
		function ot($accom = '', $ot_exact = '') {
			if ($accom == 'accomplishment' && $ot_exact != '') {
				$data['title'] 		  = '| My Overtime Accomplishment';
				$data['main_content'] = "v2views/forms/otaccom_report";
				
				$data['headscripts']['style'][0] = base_url()."v2includes/style/otaccom.style.css";
				
				$this->load->model("v2main/Globalproc");
				$data['details'] = $this->Globalproc->gdtf("ot_accom",['ot_exact_id'=>$ot_exact],'*');
				
				$empid 		= $data['details'][0]->emp_id;
				
				$f_approval = null;
				$f_name 	= null;
				
				$s_approval = null;
				$s_name 	= null;
				
				$l_approval = null;
				$l_name 	= null;
				
					if ($data['details'][0]->approval_status == 1 ) {
						if ($this->Globalproc->is_chief("division",$empid) || $this->Globalproc->is_chief("director",$empid)) {
							$f_approval == null;
							$f_name 	= null;
						} else {
							$dets       = $this->Globalproc->gdtf("employees",['employee_id'=>$data['details'][0]->fapproval],['e_signature','f_name']);
							$f_approval = $dets[0]->e_signature;
							$f_name     = $dets[0]->f_name;
						}
					} else if ($data['details'][0]->approval_status == 2 ) {
						if ($this->Globalproc->is_chief("division",$empid) || $this->Globalproc->is_chief("director",$empid)) {
							$f_approval = null;
							$f_name 	= null;
						} else {
							if ( $data['details'][0]->fapproval != 0) {
								$dets 	 	= $this->Globalproc->gdtf("employees",['employee_id'=>$data['details'][0]->fapproval],['e_signature','f_name']);
								$f_approval = $dets[0]->e_signature;
								$f_name 	= $dets[0]->f_name;
							}
						}
						$ldets 			= $this->Globalproc->gdtf("employees",['employee_id'=>$data['details'][0]->lapproval],['e_signature','f_name']);
						$l_approval		= $s_approval = $ldets[0]->e_signature;
						$l_name 		= $s_name 	  = $ldets[0]->f_name;
					}
				
				$data['signatories'] = [
						'fapproval'=> ['fname'=>$f_name , 'signature' => $f_approval],
						'sapproval'=> ['fname'=>$s_name , 'signature' => $s_approval],
						'lapproval'=> ['fname'=>$l_name , 'signature' => $l_approval]
					];
					
				$personal 			= $this->Globalproc->gdtf("employees",['employee_id'=>$data['details'][0]->emp_id],['e_signature','f_name','position_id']);
				$position 			= $this->Globalproc->gdtf("positions",['position_id'=>$personal[0]->position_id],["position_name"]);
				$data['position']	= $position[0]->position_name;
				
				$data['personal'] = [
					"fname"	=> $personal[0]->f_name,
					"sign"	=> $personal[0]->e_signature
				];
					
				$this->load->view('hrmis/admin_view',$data);
			} else {
				die("The page is not available");
				return;
			}
			
		}
						
		function mytest() {
			$dets = [
				"type"	=> "hi",
				"test"	=> "world",
				"third" => "hello"
			];
			
			foreach($dets as $key => $val) {
				echo $key."=".$val;
				echo "<br/>";
			}
		}
		
		function getlogins() {
			$this->load->model("v2main/Globalproc");
			$this->load->model('main/main_model');
			
			$sql  = "select * from users as u JOIN employees as e on u.employee_id = e.employee_id where u.isfirsttime = '2' and e.status = '1'";
			$data = $this->main_model->array_utf8_encode_recursive($this->Globalproc->__getdata($sql));
			
			echo "<table>";
			foreach($data as $a) {
				echo "<tr>";
					echo "<td>".$a->f_name."</td>";
					echo "<td>".$a->email_2."</td>";
				echo "</tr>";
			}
			echo "</table>";
		}
		
		function saveaccom_js() {
			$details = $this->input->post("info");
			
			$dates = $details['dates'];
			$accom = urlencode($details['accom']);
			
		//	$accom = substr($accom,3);
		//	$accom = substr($accom,count($accom)-3);
			
			$this->load->model("v2main/Globalproc");
			$this->load->model("v2main/Globalvars");
			
			$saved = false;
			for($i = 0 ; $i <= count($dates)-1 ; $i++) {
				$saved = $this->Globalproc->__save("d_accomplishment",
													['date'=>$dates[$i],
													 'accomplishment'=>$accom,
													 'user_id'=>$this->Globalvars->employeeid]);
				
				if (!$saved) {
					$saved = false;
					break;
				}
				
			}
			
			echo json_encode($saved);
			
		}
		
		function getaccom_js() {
			$details = $this->input->post("info");
			
			$dates  = $details['dates'];
			
			$this->load->model("v2main/Globalproc");
			$this->load->model("v2main/Globalvars");
			
			$userid = $this->Globalvars->employeeid;
			
			$dets = $this->Globalproc->gdtf("d_accomplishment",
											["date"=>$dates[0],
											 "conn"=>"and",
											 "user_id"=>$userid],
											["accomplishment","d_a_ID"]);
			
			$b = array_map(function($a){
				$a->accomplishment = urldecode($a->accomplishment);
				return $a;
			},$dets);
			
			echo json_encode($b);
		}
		
		function alteraccom() {
			$details = $this->input->post("info");
			
			$dates  = $details['dates'];
			$action = $details['alteraction'];
			$daid   = $details['daid'];
			$accom  = urlencode($details['accom']);
			
			$this->load->model("v2main/Globalproc");
			$this->load->model("v2main/Globalvars");
			
			$action_onapp = false;
			if ($action == "update") {
				$action_onapp = $this->Globalproc->__update("d_accomplishment",['accomplishment'=>$accom],["d_a_ID"=>$daid]);
			} else if($action == "delete") {
				$del_sql = "delete from d_accomplishment where d_a_ID = '{$daid}'";
				$action_onapp = $this->Globalproc->run_sql($del_sql);
			}
			
			echo json_encode($action_onapp);
		}
		
		function get_specific() {
			$details = $this->input->post("info");
			
			$this->load->model("v2main/Globalproc");
			$this->load->model("v2main/Globalvars");
			
			$daid    = $details['daid'];
			$userid  = $this->Globalvars->employeeid;
			
			$dets = $this->Globalproc->gdtf("d_accomplishment",["d_a_ID"=>$daid],['accomplishment','d_a_ID']);
			
			echo json_encode(["accom"=>urldecode($dets[0]->accomplishment),"daid"=>$dets[0]->d_a_ID]);
		}
		
		// dailytimerecords is a function that replaces the my/dtr	
			function dailytimerecords() {
				if($this->session->userdata('is_logged_in') != TRUE){
					redirect(base_url(),"refresh");
				}
				
				$data['title'] 					= "| Daily Time Records";
				
				$data['headscripts']['style'][] = base_url()."/v2includes/style/dailytimerecords.style.css";
				
				$this->load->model("v2main/Globalproc");
				$this->load->model("Globalvars");
				
				// 1 = my, 2 = dailytimerecords 
					$u_empid  = $this->uri->segment(3);
					$u_empbio = $this->uri->segment(4);
					$u_from   = $this->uri->segment(5);
					$u_to 	  = $this->uri->segment(6);
					
				// isadmin 
					$imadmin  = 0;
					$isowner  = 1;
				// end 
				
					if ($u_empid == NULL) {
						$u_empid = $this->Globalvars->employeeid;
					} else {
						// check if admin 
						$in_quest_id = $this->Globalvars->employeeid;
						$isadmin = $this->Globalproc->gdtf("users",["employee_id"=>$in_quest_id],['usertype','employee_id']);
						
						if (count($isadmin)==0) {
							// found nothing 
							die("The employee is not found."); return;
						}
		
						if ($isadmin[0]->usertype == "admin") {
							// show admin panel 
						//	if ($isadmin[0]->employee_id==$u_empid){
								$imadmin  = 1;
								$isowner  = 1;
						//	} else {
						//		$imadmin  = 1;
						//		$isowner  = 0;
						//	}
						} else if ($isadmin[0]->usertype == "user") {
							if ($u_empid != $isadmin[0]->employee_id) {
								$isowner  = 0;
								die("<p style='text-align: center; margin: 70px; background: #ffe5e5; padding: 32px; font-family: arial;'> You are not allowed to view somebody's DTR. Please return. </p>"); return;
							} else {
								$isowner = 1;
							}
						}
					}
				
				// used by personal users 	
					// $empdata = $this->Globalproc->gdtf("employees",['employee_id'=>$u_empid],['employment_type','biometric_id','f_name']);
					$edsql   = "select 
									e.employment_type,
									e.biometric_id,
									e.f_name,
									u.usertype from employees as e 
									JOIN users as u on e.employee_id = u.employee_id
									where e.employee_id = '{$u_empid}'";
					$empdata = $this->Globalproc->__getdata($edsql);
					$emptype = $empdata[0]->employment_type;
					
					if ($empdata[0]->usertype == "admin") {
						$data['admin'] = true;
					}
					
					$dates   		= $this->Globalproc->gdtf("hr_dtr_coverage",['employment_type'=>$emptype],"*");
					$data['dates']	= $dates;
					krsort($data['dates']);
					
					$activedate = array_map(function($key){
						if ($key->is_active == 1) {
							return $key;
						}
					},$data['dates']);
					
					$clean = [];
					foreach($activedate as $key => $val) {
						if ($val == NULL) {
							unset($activedate[$key]);
						} else {
							$clean = $activedate[$key];
						}
					}
			
				// end 
				
				if ($u_empbio == NULL) {
					$u_empbio = $empdata[0]->biometric_id;
				}
				
				if ($u_from == NULL) {
					$u_from = date("Y-m-d",strtotime($clean->date_started));
				}
					
				if ($u_to == NULL) {
					$u_to = date("Y-m-d",strtotime($clean->date_ended));
				}
				
				$data['from']	 = $u_from;
				$data['to']		 = $u_to;
	
				$data['empname'] = strtolower($empdata[0]->f_name);
				
				// isadmin
				$data['headscripts']['js'][] 	= base_url()."/v2includes/vars.js";
				echo "<script>";
					echo "var isadmin = '{$imadmin}';";
					echo "var isowner = '{$isowner}';";
					echo "var dtrurl = '".base_url()."';";
					echo "var empid  = '".$u_empid."';";
					echo "var empbio = '".$u_empbio."';";
					echo "var from   = '".$u_from."';";
					echo "var to     = '".$u_to."';";
				echo "</script>";
				
			//	$data['headscripts']['js'][] 	= base_url()."/v2includes/js/dtr.script.js";
				
					$data['headscripts']['js'][] 	= base_url()."/v2includes/js/json/dtr.values.json";
					$data['headscripts']['js'][] 	= base_url()."/v2includes/js/dtr.procs.js";
					$data['headscripts']['js'][] 	= base_url()."/v2includes/js/process.addingofatt.js";
				
				$data['main_content'] 			= 'v2views/dailytimerecords';
				
				// var_dump($data);
				$this->load->view('hrmis/admin_view',$data);
			}
			
			function testtime() {
				$this->load->model("v2main/Timerecords");
				
				// 2016-07-08
				$this->Timerecords->setfrom("2019-08-01");
				$this->Timerecords->setto("2019-08-31");
				$this->Timerecords->setemp("99");
				$this->Timerecords->setbio("168");
				
				$dtr = $this->Timerecords->gettime();
				
				$data['under'] = $this->Timerecords->return_("total_unders");
				$data['late']  = $this->Timerecords->return_("total_lates");
				$data['daysp'] = $this->Timerecords->return_("total_daysp");
				$data['hourp'] = $this->Timerecords->return_("total_hourp");
				
				$data['dtr'] 	  = $dtr;
				$data['coverage'] = "sample coverage";
				$data['name'] 	  = "name";
				$data['pos']      = "position here";
				$data['emptype']  = "jo";
				
				$dview		 = $this->load->view('v2views/timetableprint',$data,true);
			//	$dview		 .= $this->load->view('v2views/timetableprint',$data,true);
			//	
				echo $dview;
			}
			
			function testing() {
				$time1 = "09:30:00";
				$time2 = "16:09:00";
				
				$secs   = strtotime($time1)-strtotime("00:00:00");
				$result = date("h:i",strtotime($time2)-$secs);
				// $result = date("h:i",strtotime("-1 hour",strtotime(strtotime($time2)-$secs)));
				echo $result;
				echo "<br/><br/><br/>";
				
				$startTime  = new DateTime('09:30:00 AM');
				$endTime 	= new DateTime('06:09:00 PM');
				$duration 	= $startTime->diff($endTime); //$duration is a DateInterval object
				echo $duration->format("%H:%I:%S");
			}
			
			function sorry() {
				echo "sorry this page is currently under maintenance";
			}
			
			function timetable() {
				$this->load->model("v2main/Globalproc");
				
				$this->load->model("v2main/Timerecords");
				
				$data['title']			= "| Daily Time Records";
				
				$this->Timerecords->setfrom( $this->input->post("from") );
				$this->Timerecords->setto( $this->input->post("to") );
				$this->Timerecords->setemp( $this->input->post("emp") );
				$this->Timerecords->setbio( $this->input->post("empbio") );

				$data['dtr']	= $this->Timerecords->gettime();
				
				/*
				$this->load->model("v2main/Newtimerecords");
				
				$data['title']			= "| Daily Time Records";
				
				$this->Newtimerecords->setfrom( $this->input->post("from") );
				$this->Newtimerecords->setto( $this->input->post("to") );
				$this->Newtimerecords->setemp( $this->input->post("emp") );
				$this->Newtimerecords->setbio( $this->input->post("empbio") );
				
				
				$thetime        = $this->Newtimerecords->gettime();
				*/
				//$data['dtr']	= $thetime['dtr'];
				// $data['period'] = $thetime['period'];
				$this->load->view('v2views/timetable',$data);
			}
			
		// end
		
		function coc() {
			$data['title']		   = "| My Compensatory time-off";
			$data['main_content']  = "v2views/ctoledger_new";
			$this->load->view('hrmis/admin_view',$data);
		}
		
		function computepassslip() {
			$info = $this->input->post("info");
			$to   = $info['tbi'];
			$from = $info['tout'];
			
			$this->load->model("v2main/Globalproc");
			
			$timedif = $this->Globalproc->getdiff_time($to,$from,"-");
			
			list($h,$m) = explode(":",$timedif);
			
			echo json_encode([abs($h),abs($m)]);
		}
		
		function saveattachment() {
			$data  		= $this->input->post("info")['data_'];
			$checkdates = $this->input->post("info")['dates_'];
			
			$this->load->model("v2main/Globalproc");
			$this->load->model("Globalvars");
			
			$data  		= (array) $data;
			
			$ret = null;
			
			/*
				el-0: 
					attachment: null
					elid: "el-0"
					name: "CA"
					timeoftheday: null
			*/
			
			date_default_timezone_set("Asia/Manila");
			
			$empid = $this->Globalvars->employeeid;
			$checkexact_details = [
					"employee_id"			=> $empid,
					"type_mode"				=> null,
					"type_mode_details"		=> null,
					"modify_by_id"			=> null,
					"checkdate"				=> null,
					"remarks"				=> null,
					"reasons"				=> null,
					"attachments"			=> null,
					"date_added"			=> date('M d Y h:i A'),
					"is_approved"			=> 0,
					"aprroved_by_id"		=> 0,
					"date_approved"			=> 0,
					"ps_type"				=> null,
					"time_in"				=> null,
					"time_out"				=> null,
					"leave_id"				=> null,
					"leave_name"			=> null,
					"ps_guard_id"			=> null,
					"leave_is_halfday"		=> null,	
					"leave_is_am_pm_select"	=> null,
					"grp_id"				=> null
				];
			
			$fyear 	   = $tyear = date("Y");
			$shift_sql = "select * from employee_schedule as es 
								JOIN shift_mgt_logs as sml 
								ON es.shift_id = sml.shift_id
								where es.employee_id = '{$empid}' 
									and DATEPART(year, es.date_sch_started) = '{$fyear}' 
									and DATEPART(year, es.date_sch_ended) = '{$tyear}'";
			
			$shifts    = $this->Globalproc->__getdata($shift_sql);
			
			if (count($shifts) == 0) {
				$shifts = array(
					(object) array("time_exact" => "8:00 AM","type"=>"C/In","shift_type"=>"AM_START"),
					(object) array("time_exact" => "12:00 PM","type"=>"C/Out","shift_type"=>"AM_END"),
					(object) array("time_exact" => "1:00 PM","type"=>"C/In","shift_type"=>"PM_START"),
					(object) array("time_exact" => "5:00 PM","type"=>"C/Out","shift_type"=>"PM_END")
				);
			}
		//	var_dump($shifts);
		
			foreach($checkdates as $cd) {
				foreach( $data as $key => $value) {
					$checkexact_details['type_mode'] 	= $value['name'];
					$checkexact_details['checkdate'] 	= $cd;
					$checkexact_details['modify_by_id'] = $empid;
					$checkexact_details['attachments'] 	= (isset($value['attachment']))?json_encode($value['attachment']):NULL;
					
					switch($value['name']) {
						case "CA":
							// checkexact timelog
								if ($value['timeoftheday'] != null) {
									
									$timelog = [];
									foreach($shifts as $ss) {
										array_push($timelog,[$ss->time_exact,$ss->type,$ss->shift_type]);
									}
									
									$timein = null;
									$timeout = null;
									
									switch($value['timeoftheday']) {
										case "whole":
											$timein = $timelog[0][0];
											$timeout = $timelog[3][0];
											$checkexact_details['leave_is_am_pm_select'] = "wh";
											break;
										case "morning":
											$timein = $timelog[0][0];
											$timeout = $timelog[1][0];
											$checkexact_details['leave_is_am_pm_select'] = "am";
											break;
										case "afternoon":
											$timein = $timelog[2][0];
											$timeout = $timelog[3][0];
											$checkexact_details['leave_is_am_pm_select'] = "pm";
											break;
									}
									
									$checkexact_details['time_in'] 		= $timein;
									$checkexact_details['time_out'] 	= $timeout;
									
								}
							// end 
							break;
						case "AMS":
							$checkexact_details['remarks'] = $value['remarks'];
							
							
							break;
							
					}
					
					$ret  = $this->Globalproc->__save("checkexact",$checkexact_details);
					
					$checkexact_logs = [
						"exact_id"		=> $this->Globalproc->getrecentsavedrecord("checkexact","exact_id")[0]->exact_id,
						"checktime"		=> NULL,
						"checktype"		=> NULL, // type 
						"shift_type"	=> NULL, // shift type 
						"modify_by_id"	=> 0,
						"is_modify"		=> 0,
						"is_delete"		=> 0,
						"date_added"	=> date("M. d, Y h:i A"),
						"date_modify"	=> 0,
						"is_bypass"		=> 0
					];
								
					switch($value['name']) {
						case "CA":
							$startfrom = null; $endto = null;
							switch($checkexact_details['leave_is_am_pm_select']) {
								case "wh":
									$startfrom = 0;
									$endto     = 3;
									break;
								case "am":
									$startfrom = 0;
									$endto 	   = 1;
									break;
								case "pm":
									$startfrom = 2;
									$endto 	   = 3;
									break;
							}
							
							for($i = $startfrom ; $i <= $endto ; $i++) {
								$checkexact_logs["checktime"] 		= $cd." ".$timelog[$i][0];
								$checkexact_logs["checktype"]		= $timelog[$i][1]; // type 
								$checkexact_logs["shift_type"]		= $timelog[$i][2]; // shift type 

								$ret = $this->Globalproc->__save("checkexact_logs",$checkexact_logs);
							}
						break;
						case "AMS":
							foreach($value['ams'] as $key => $val) {
								if ( $key == "a_in" ) {
									$checkexact_logs["checktime"] 		= $cd." ".$val->a_in;
									$checkexact_logs["checktype"]		= "C/In"; // type 
									$checkexact_logs["shift_type"]		= "AM"; // shift type 
								}
								
								if ( $key == "a_out" ) {
									$checkexact_logs["checktime"] 		= $cd." ".$val->a_out;
									$checkexact_logs["checktype"]		= "C/Out"; // type 
									$checkexact_logs["shift_type"]		= "AM"; // shift type 
								}
								
								if ( $key == "p_in" ) {
									$checkexact_logs["checktime"] 		= $cd." ".$val->p_in;
									$checkexact_logs["checktype"]		= "C/In"; // type 
									$checkexact_logs["shift_type"]		= "PM"; // shift type 
								}
								
								if ( $key == "p_out" ) {
									$checkexact_logs["checktime"] 		= $cd." ".$val->p_out;
									$checkexact_logs["checktype"]		= "C/Out"; // type 
									$checkexact_logs["shift_type"]		= "PM"; // shift type 
								}
								
								$ret = $this->Globalproc->__save("checkexact_logs",$checkexact_logs);
							}
						break;
					}
				}
			}
			
			echo json_encode($ret);
		}
		
		function getams() {
			$date  = $this->input->post("info")["date"];
			
			$this->load->model("v2main/Globalproc");
			$this->load->model("Globalvars");
			
			$empid 	   = $this->Globalvars->employeeid;
			$checkdate = $date[0];
			
		//	$empid = 389;
		//	$checkdate = '06/28/2018';
			
			$ams = $this->Globalproc->gdtf("checkexact_ams",
												["employee_id"=>$empid,
												 "conn"=>"and",
												 "checkdate"=>$checkdate],
										   "*");
			
			$newams = array_map(function($val){
				$val->a_in	= ($val->a_in != null)?date("h:i A", strtotime($val->a_in)):"--:--";
				$val->a_out	= ($val->a_out != null)?date("h:i A", strtotime($val->a_out)):"--:--";
				$val->p_in	= ($val->p_in != null)?date("h:i A", strtotime($val->p_in)):"--:--";
				$val->p_out	= ($val->p_out != null)?date("h:i A", strtotime($val->p_out)):"--:--";
				return $val;
			},$ams);
			
			echo json_encode($newams);
			// select * from checkexact_ams where employee_id = '389' and checkdate = '6/28/2018'
		}
		
		function getattachment() {
			$dates = $this->input->post("info")['dates'];
			$empid = $this->input->post("info")['empid'];
			
			$this->load->model("v2main/Globalproc");
			// $attachment = $this->Globalproc->checkattachment( $value->format('m/d/Y'), $emp);
			
		}
		
		function delete_atm() {
			$exactid = $this->input->post("info")['exact_id'];
			
			$this->load->model("v2main/Globalproc");
			
			$getsql = $this->Globalproc->gdtf("checkexact",["exact_id"=>$exactid],['checkdate','employee_id','type_mode']);
			
			if (count($getsql)>0) {
				$sql = "delete from checkexact_logs where exact_id = '{$exactid}'";
				$del = $this->Globalproc->run_sql($sql);
				
				$sql_1 = "delete from checkexact_ams where checkdate = '{$getsql[0]->checkdate}' and employee_id = '{$getsql[0]->employee_id}'";
				$del_1 = $this->Globalproc->run_sql($sql_1);
			}
			
			$sql = "delete from checkexact where exact_id = '{$exactid}'";
			$del = $this->Globalproc->run_sql($sql);
			
			if ($del) {
				$this->Globalproc->run_sql("delete from checkexact_logs where exact_id = '{$exactid}'");
			}
			
			echo json_encode($del);
		}
		
		function display_ledger($emp_id = '') {
			$this->load->model("v2main/Globalproc");
			
			$sql 		  = "select * from employee_leave_credits as elc 
								left join checkexact as ce on 
									elc.exact_id = ce.grp_id
								left join leaves as l on
									ce.leave_id = l.leave_id
							where elc.employee_id = '438' order by elc.elc_id DESC";
			
			$data['data'] = $this->Globalproc->__getdata($sql);
			
			$this->load->view("v2views/displedger",$data);
		}
		
		function dtrstatus() {
			$date_start = $this->input->post("datefrom");
			$date_end   = $this->input->post("dateto");
			$emp_id   	= $this->input->post("emp");
			
			$no_ic_d	= $this->input->post("ic_dates");
			
			$date_start = date("m/d/Y",strtotime($date_start));
			$date_end   = date("m/d/Y",strtotime($date_end));
				
			$this->load->model("v2main/Globalproc");
			
			if ($no_ic_d == 0) {
				$checking = "select 
								dsr.*, 
								als.*,
								hdc.*, 
								cs.*
							from hr_dtr_coverage as hdc 
							JOIN dtr_summary_reports as dsr 
								on hdc.dtr_cover_id = dsr.dtr_cover_id 
							JOIN countersign as cs
								on dsr.sum_reports_id = cs.dtr_summary_rep
							JOIN allowedsubmit as als 
								on cs.dtr_summary_rep = als.asdtr_sumrep
							where hdc.date_started = '{$date_start}' 
								and hdc.date_ended = '{$date_end}' 
								and dsr.employee_id = '{$emp_id}'";
				
				$check = $this->Globalproc->__getdata($checking);
				
				$emps  = $this->Globalproc->gdtf("employees",["employment_type"=>"REGULAR","conn"=>"and","status"=>1],"*");
				
				$data['emps']	  = $emps;
				
				// $data['deadline'] = date("D - M. d, Y", strtotime($check[0]->date_deadline));
				
				$deadlinesql 	  = "select * from hr_dtr_coverage 
									 where date_started = '{$date_start}' and date_ended = '{$date_end}' 
									 and employment_type = (select employment_type from employees where employee_id = '{$emp_id}')";
								
				$deddets    	  = $this->Globalproc->__getdata($deadlinesql);
				
				$data['deadline'] = null;
				if (count($deddets)>0) {
					$data['deadline'] = date("D - M. d, Y",strtotime($deddets[0]->date_deadline));
				}
				
				// number of signatories 
					$data['numofsigs']	 = 1; //$this->Globalproc->howmanysigs($emp_id);
				// end 
					
				if (count($check)==0){
					// send new
					$recom = $this->Globalproc->__getdata("select employee_id, f_name from employees where 
														Division_id = (select Division_id from employees where employee_id ='{$emp_id}') 
														and is_head = 1");
															
					$dir   = $this->Globalproc->__getdata("select employee_id, f_name from employees where 
														DBM_Pap_id = (select DBM_Pap_id from employees where employee_id ='{$emp_id}') 
														and is_head = 1 and Division_id = 0");
				
					$data['recom']	= $recom[0]->employee_id;	
					$data['dir']	= $dir[0]->employee_id;
					
					$this->load->view("v2views/dtrcomponents/complete",$data);
				} else {
					// resend old
					
					if ($check[0]->approval_status == 0) {
						$data['atstat'] = "waiting for approval";
					} else if ($check[0]->approval_status == 1) {
						$data['approval_stat'] = 1;
						$data['atstat'] = "waiting for approval";
					} else if ($check[0]->approval_status==2) {
						$data['approval_stat'] = 2;
						$data['atstat'] = "APPROVED";
					}
					
					$data['recom']	= ["employee_id" => $check[0]->tobeapprovedby];
					$data['dir']	= ["employee_id" => $check[0]->last_approving];
					
					$data['dtrsumrep'] = $check[0]->sum_reports_id;
					$this->load->view("v2views/dtrcomponents/resend",$data);
				}
			} else {
				// show incomplete window
				$data['check']  = $this->Globalproc->gdtf("hr_dtr_coverage",["date_started"=>date('m/j/Y', strtotime($date_start)), "conn"=>"and", "date_ended"=>date('m/j/Y', strtotime($date_end))],"*");
				$data['incoms'] = $no_ic_d;
				$this->load->view("v2views/dtrcomponents/incomplete",$data);
			}
			
		}
		
		function sendemail() {
			$info    = $this->input->post("info");
			$empid   = $info['empid_'];
			$empbio  = $info['bio_'];
			$from    = $info['from_'];
			$to 	 = $info['to_'];
			
			$sendto  = $info['sendto'];
			
			// unique ID 
				$unid = $info['unid_'];
			// end 
			
			$subject = $info['subject'];
			
			$this->load->model("v2main/Globalproc");
			$this->load->model("v2main/Timerecords");
				
			$data['title']			= "| Daily Time Records";
/*
			$from   = "2019-10-01";
			$to     = "2019-10-31";
			$empid  = 99;
			$empbio = 168;
			$sendto = 33;
			$subject = "I need approval...testing mode";
			$unid    = "80c16699237";
*/		
			$this->Timerecords->setfrom( $from );
			$this->Timerecords->setto( $to );
			$this->Timerecords->setemp( $empid );
			$this->Timerecords->setbio( $empbio );

			$data['dtr']	= $this->Timerecords->gettime();
			$data['from']	= date("M. d, Y", strtotime($from));
			$data['to']		= date("M. d, Y", strtotime($to));
			
		//  details regarding whom to send the dtr to
		//  $sendtodets 	= $this->Globalproc->gdtf("employees",["employee_id"=>$sendto],["email_2","f_name"]);
			$stdsql 		= "select 
									u.Username, u.Password, e.f_name, e.email_2
								from users as u JOIN employees as e 
									on u.employee_id = e.employee_id 
								where e.employee_id = '{$sendto}'";
		// 	echo $stdsql; return;
			$sendtodets 	= $this->Globalproc->__getdata($stdsql);
		
		// employee details 
			// $empdets 		= $this->Globalproc->gdtf("employees",['employee_id'=>$empid],["f_name"]);
			$empdetssql 		= "select 
									c.vercode,
									e.email_2,
									e.f_name,
									u.Password,
									u.Username
								from dtr_summary_reports as dsr 
								JOIN countersign as c on 
									dsr.sum_reports_id = c.dtr_summary_rep
							    JOIN employees as e on	
									c.emp_id = e.employee_id 
								JOIN users as u on 
									e.employee_id = u.employee_id where dsr.unid = '{$unid}'";
			$empdets 			= $this->Globalproc->__getdata($empdetssql);
		// end 
			
			if (count($empdets)==0){ return;}
			if (count($sendtodets)==0) { return;}
			
			// employee name
			$data['empname'] = $empdets[0]->f_name;
			// end 
			
			// link
				/*
				// alcrypt
				$c_p = chief's encrypted password
				$alcrypted = chief's username encrypted using alvin's encryption
				$m .= "<a href='".base_url()."dtr/approval/".$vercode."/".$c_p."/".$alcrypted."' style='text-decoration:none;'>
				*/
				$c_p 	      = $sendtodets[0]->Password;
				$alcrypted    = $this->Globalproc->alcrypt($sendtodets[0]->Username);
				$vercode 	  = $empdets[0]->vercode;
				$data['link'] = base_url()."dtr/approval/".$vercode."/".$c_p."/".$alcrypted;
			// end
			
			$dtr 			= $this->load->view('v2views/emailtemplate/timetoemail',$data, true);
			//	echo $dtr;
		
			$details_mail['from'] 	 = strtoupper($empdets[0]->f_name);
			$details_mail['to'] 	 = $sendtodets[0]->email_2;
			$details_mail['subject'] = $subject;
			$details_mail['message'] = $dtr;
			
			$sent = $this->Globalproc->sendtoemail($details_mail);
			
			echo json_encode($sent);
		}
		
		function savefile() {
			$info   = $this->input->post("info");
			$att    = $info['att'];
			$dates  = $info['dates_'];
			$empid  = $info['empid_'];
			$vars   = $info['thevars'];
			
		//	echo json_encode($info);
			
			$this->load->model("v2main/Globalproc");
			$date_  = date("M d, Y h:i A");
			
			$saved   = true;
			$remarks = null;
			$reasons = null;
			$values  = null;
			
			$atts = null;
			if (isset($att['el-0']['attachment'])) { 
				$atts = json_encode($att['el-0']['attachment']);
			}
			
			$typemode = $att['el-0']['name'];
			
			// used in pass slip 
				$ps_type = null;
				$timein  = null;
				$timeout = null;
			// end 
			
			if ($typemode == "PS") {
				switch( strtolower($vars['pstype']) ) {
					case "personal":
						$ps_type = 2;
						break;
					case "official":
						$ps_type = 1;
						break;
				}
				
				$timein  = $vars['timein'];
				$timeout = $vars['timeout'];
			}
			
			if ($typemode == "J"){
				$typemode = "AMS";
				$remarks = $vars['tofday'];
				$reasons = $vars['reasons'];
			}
			
			$grp_id = null;
			
			foreach($dates as $cd) {
				$grp_id = $this->Globalproc->__createuniqueid( date("FldYhisa") );
				
				$cdate  = date("m/d/Y", strtotime($cd));
				$values = ["employee_id" 			=> $empid,
						   "type_mode"				=> $typemode,
						   "type_mode_details"		=> null,
						   "modify_by_id"			=> $empid,
						   "checkdate"				=> $cdate, 	// 10/9/2017 :: 10/02/2019
						   "remarks"				=> $remarks,
						   "reasons"				=> $reasons,
						   "attachments"			=> $atts,
						   "date_added"				=> $date_,
						   "is_approved"			=> 0,
						   "aprroved_by_id"			=> 0,
						   "date_approved"			=> 0,
						   "ps_type"				=> $ps_type,
						   "time_in"				=> $timein,
						   "time_out"				=> $timeout,
						   "leave_id"				=> 0,
						   "leave_name"				=> 0,
						   "ps_guard_id"			=> 0,
						   "leave_is_halfday"		=> 0,
						   "leave_is_am_pm_select"  => 0,
						   "grp_id"					=> $grp_id];
				
				// check in checkexact for existence 
					$sqlcheck   = "select exact_id from checkexact where checkdate = '{$cdate}' and employee_id = '{$empid}' and type_mode = '{$typemode}'";
					$checked_ce = $this->Globalproc->__getdata($sqlcheck);
					// echo $sqlcheck; return;
				// end 
				
				if (count($checked_ce)==0) {
					$saved 	      = $this->Globalproc->__save("checkexact",$values);
					$exact_id     = $this->Globalproc->getrecentsavedrecord("checkexact", "exactid");
					$exact_id     = $exact_id[0]->exactid;
				} else {
					$saved 		  = true;
					$exact_id 	  = $checked_ce[0]->exact_id;
				}
				
				
				$update_clogs = false;
				
				$amsupdate    = null;
				$ams_time     = null;
				$shifttype    = null;
				
				if ($typemode == "AMS") {
					// save to check exact ams 
					
					// check if date has existed 
						$time 	   = $vars['time'];
						$checkdate = date("m/d/Y", strtotime($cd));

							$amsvalue = ["employee_id" 	=> $empid,
									     "checkdate"	=> $checkdate,
									     "a_in"			=> null,
									     "a_out"		=> null,
									     "p_in"			=> null,
									     "p_out"		=> null];
							
							switch($remarks) {
								case "amin":
									$amsvalue['a_in']  = $checkdate." ".$time;
									$amsupdate['a_in'] = $checkdate." ".$time;
									$ams_time 		   = "a_in";
									$ams_ctype 		   = "C/In";
									break;
								case "amout":
									$amsvalue['a_out']  = $checkdate." ".$time;
									$amsupdate['a_out'] = $checkdate." ".$time;
									$ams_time 		   = "a_out";
									$ams_ctype 		   = "C/Out";
									break;
								case "pmin":
									$amsvalue['p_in']  = $checkdate." ".$time;
									$amsupdate['p_in'] = $checkdate." ".$time;
									$ams_time 		   = "p_in";
									$ams_ctype 		   = "C/In";
									break;
								case "pmout":
									$amsvalue['p_out']  = $checkdate." ".$time;
									$amsupdate['p_out'] = $checkdate." ".$time;
									$ams_time 		   = "p_out";
									$ams_ctype 		   = "C/Out";
									break;
							}
							
							if (count($checked_ce) == 0) {
								// save to the ams database
								$saved 			= $this->Globalproc->__save("checkexact_ams",$amsvalue);
								$update_clogs   = true; 
							} else {
								// update the ams input from the ams database
								$saved 			= $this->Globalproc->__update("checkexact_ams",$amsupdate,
												  							 ["employee_id"=>$empid,
																			  "conn"=>"and",
																			  "checkdate"=>$checkdate]);
								$update_clogs   = true;
							}
						// end 
				}
				
				$shifttype 						= date("A", strtotime($amsupdate[$ams_time]));
				if ($update_clogs == true && $saved) {
				// save to checkexact_logs 
					$checklogs = ["exact_id"	  => $exact_id,
								  "checktime"	  => $amsupdate[$ams_time],
								  "checktype"	  => $ams_ctype,	
								  "shift_type"	  => $shifttype,
								  "modify_by_id"  => 0,
								  "is_modify"	  => 0,
								  "is_delete"	  => 0,
								  "date_added"	  => date("m/d/Y h:i A"),
								  "date_modify"	  => 0,
								  "is_bypass"     => 0];
					$saved = $this->Globalproc->__save("checkexact_logs",$checklogs);
				} else {
					// update checkexact_logs 
					// $checklogs_update = [""];
					// var_dump($saved);
				}
				
				if (!$saved) {
					return false;
				}
				
			}
			
			echo json_encode($values);

		}
		
		function deductionlogs() {
			$this->load->model("v2main/Timerecords");
			$this->load->model("Globalvars");
			$this->load->model("v2main/Ctomodel");
			$this->load->model("v2main/Globalproc");
			
			// variables 
				$info 		= $this->input->post("info");
				$from 		= $info['from_'];
				$to 		= $info['to_'];
				$empbio     = $info['bio_'];
				$empid   	= $this->Globalvars->employeeid;
			// end 
			
			// empid_: "99", bio_: "168", from_: "2019-10-01", to_: "2019-10-31
			// $from   = "2019-10-01";
			// $to     = "2019-10-31";
			// $empid  = 99;
			// $empbio = 168;
			
		//	$from      = "2020-01-22";
		//	$to 	   = "2020-02-05";
		//	$empid 	   = "389";
		//	$empbio    = "306";
			
			$this->Timerecords->setfrom($from);
			$this->Timerecords->setto($to);
			$this->Timerecords->setemp($empid);
			$this->Timerecords->setbio($empbio);
			
			$dtr = $this->Timerecords->gettime();
			
			// set the date interval
				//--------------------------------------------------------------------------------------------------
						$begin = new DateTime($from); //2016-07-04
						$end   = clone $begin;
					
						$end->modify($to); 	 		  // 2016-07-08
						$end->setTime(0,0,1);   	  // new line

						$interval = new DateInterval('P1D');
						$period   = new DatePeriod($begin, $interval, $end);
				//--------------------------------------------------------------------------------------------------
			// end date interval	
			/*
				{"tardiness":
					[{"date_late":"25-Oct-18","final_late":"0:00:05"},
					 {"date_late":"31-Oct-18","final_late":"0:00:11"}],
				"undertime":[],
				"ps":[]}
			*/
			
			$tardiness = [];
			$undertime = [];
			$ps 	   = [];
			
			// var_dump($dtr); return;
			// var_dump( $dtr['10/07/2019']['tardiness_pm'] );
			// $unid = $this->Globalproc->tokenizer_leave($from.$to.$empid.$empbio);
			$unid 	 = $this->Globalproc->tokenizer_leave($from).$this->Globalproc->tokenizer_leave($to).$empid.$empbio;
			
			foreach($period as $key => $p) {
				$dateindex 		 = $p->format('m/d/Y');
				
				$icsql = "select elc_id from employee_leave_credits as elc where credits_as_of = '{$dateindex}'
					and exact_id = '{$unid}'";
				 
				$lcdets = $this->Globalproc->__getdata($icsql);
				
				if (count($lcdets)>0) {
					break;
				}
				
				// compute total tardiness 
					$ttard = 0;
					if (isset($dtr[$dateindex]['tardiness_am'])) {
						$ttard += $this->Ctomodel->convertoseconds( $dtr[$dateindex]['tardiness_am'] );
					}
					
					if (isset($dtr[$dateindex]['tardiness_pm'])) {
						$ttard += $this->Ctomodel->convertoseconds( $dtr[$dateindex]['tardiness_pm'] );
					}
					
					if ($ttard != 0) {
						$finallate = $this->Ctomodel->returntotimeformat($ttard);
						array_push($tardiness, ["date_late"=>$dateindex,"final_late"=> $finallate]);
						// save to employee_leave_credits
							list($d,$h,$m) = explode(":",$finallate);
							$details = ["typemode"			=> "T",
										"leave_value"		=> null,
										"date_inclusion"	=> $dateindex,
										"no_days_applied"	=> "0",
										"hrs"				=> $h,
										"mins"				=> $m];
							// save
							$this->Globalproc->calc_leavecredits($empid,$unid,$details);
						// end
					}
				// end tardiness 
				
				// undertime 
					$tunder = 0;
					if (isset($dtr[$dateindex]['undertime_am'])) {
						$tunder += $this->Ctomodel->convertoseconds( $dtr[$dateindex]['undertime_am'] );
					}
					
					if (isset($dtr[$dateindex]['undertime_pm'])) {
						$tunder += $this->Ctomodel->convertoseconds( $dtr[$dateindex]['undertime_pm'] );
					}
					
					if ($tunder != 0) {
						$finalunder = $this->Ctomodel->returntotimeformat($tunder);
						array_push($undertime, ["date_late"=>$dateindex,"final_undertime"=> $finalunder]);
						
						// save to employee_leave_credits
							list($d,$h,$m) = explode(":",$finalunder);
							$details = ["typemode"			=> "U",
										"leave_value"		=> null,
										"date_inclusion"	=> $dateindex,
										"no_days_applied"	=> "0",
										"hrs"				=> $h,
										"mins"				=> $m];
							// save
							
							$this->Globalproc->calc_leavecredits($empid,$unid,$details);
						// end
					}
				// end undertime 
				
				// pass slip 
				if (isset($dtr[$dateindex]['passslip'])) {
					$psvalue = null;
					$pstime  = null;
					
					$exactid = $dtr[$dateindex]['passslip']['exact_id'];
					
					if ($dtr[$dateindex]['passslip']['personal'] != NULL) {
						$psvalue = 2;
						$pstime  = $dtr[$dateindex]['passslip']['personal'];
					}
					
					if ($dtr[$dateindex]['passslip']['official'] != NULL) {
						$psvalue = 1;
						$pstime  = $dtr[$dateindex]['passslip']['official'];
					}
					
					$pextime = explode(":",$pstime);
					$psh	 = $pextime[0];
					$psm 	 = $pextime[1];
					
						$details = ["typemode"			=> "ps",
									"leave_value"		=> $psvalue, // 1 or 2
									"date_inclusion"	=> $dateindex,
									"no_days_applied"	=> "0",
									"hrs"				=> $psh,
									"mins"				=> $psm];
					
					$this->Globalproc->calc_leavecredits($empid,$exactid,$details);
					array_push($ps, ["date_ps"=>$dateindex,"final_ps"=> $pstime]);
				}
					
				// end of pass slip 
			}
			
			echo json_encode(["tardiness"=>$tardiness,"undertime"=>$undertime,"ps"=>$ps]);
			
			// echo $arr;
		}
		
		function submitdtr() {
			// save to dtr_summary_reports
			// save to countersign 
			$this->load->model("v2main/Globalproc");
			$this->load->model("Globalvars");
			
			$empid  = $this->Globalvars->employeeid;
			$info   = $this->input->post("info");
			$from   = date("m/d/Y", strtotime($info['from_']));
			$to     = date("m/d/Y", strtotime($info['to_']));
			$deds   = (isset($info['deds_']))?$info['deds_']:0;
			
			// approving officials 
				$recom = $empid; // $info['recom'];
				$final = $info['fappr'];
			// end 
			
			$unid   = $this->Globalproc->tokenizer_leave(date("mdYHis").$empid);
			
			$dci_sql = "select max(dtr_cover_id) as dtr_cover_id from hr_dtr_coverage where date_started ='{$from}' and date_ended = '{$to}'";
			$dtr_cover_id = $this->Globalproc->__getdata($dci_sql);
			
			if (count($dtr_cover_id)==0){ return; }
			
			$values = ["employee_id"		 => $empid,
					   "dtr_cover_id"		 => $dtr_cover_id[0]->dtr_cover_id,
					   "dtr_coverage"	 	 => "{$from} - {$to}",
					   "date_start_cover"	 => $from,
					   "date_end_cover"		 => $to,
					   "tardiness_undertime" => '0',
					   "services_rendered"	 => '0',
					   "leaves_log_id"		 => '0',
					   "remarks"			 => '0',
					   "is_approved"		 => '0',
					   "approved_by"		 => $final,
					   "date_submitted"		 => date("m/d/Y"),
					   "status"				 => false,
					   "is_submitted"		 => false,
					   "deduction_logs"		 => json_encode($deds),
					   "unid"				 => $unid];
		
			$saved = $this->Globalproc->__save("dtr_summary_reports",$values);
			if ($saved){
				echo json_encode($unid);
			}
		}
		
		function createcountersign() {
			/*
			bodycode 
			emp_id
			approval_status
			tobeapprovedby
			last_approving
			dtr_summary_rep
			vercode
			hrnotified
			*/
			
			$info  = $this->input->post("info");
			$recom = (isset($info['recom']))?$info['recom']:null;
			$fappr = (isset($info['fappr']))?$info['fappr']:null;
			
			// unique ID from the dtr_summary_reports 
				$unid = $info['unid_'];
			// end 
			
			$this->load->model("v2main/Globalproc");
			$this->load->model("Globalvars");
			
			// empid 
				$empid = $this->Globalvars->employeeid;
			// end 
			
			// get the dtr summary reports 
				$dsr = $this->Globalproc->gdtf("dtr_summary_reports",['unid'=>$unid],['sum_reports_id']);
				if (count($dsr)==0){ return; }
				
				$dtr_sum_rep_id = $dsr[0]->sum_reports_id;
			// end 	
			
			// create verification code 
				// secret verification code =============================================================================================================
					$vc       = md5($empid).md5($unid).md5( date("mdyhisA") );
					$vercode  = date("mdYHiA")."-".$recom."-".$fappr."-".substr(md5($vc),0,9);
				// end secret verification code =========================================================================================================
			// end 
			
			// send to whos email address
			$sendto = null;
		
			// approval status here 
			$approval_status = 1;
			
				// check if head or director 
					$howmany = "1"; //$this->Globalproc->howmanysigs($empid);
					
					// if (count($howmany)==0) { return; }
					
					if ($howmany == "1") {
						$approval_status = 1;
						$sendto 		 = $fappr;
					} else if ($howmany == "2") {
						$approval_status = 0;
						$sendto 		 = $recom;
					}
				// end 
			// end 
			
			$values = ["bodycode" 			=> "null",
					   "emp_id" 			=> $empid,
					   "approval_status" 	=> $approval_status,
					   "tobeapprovedby" 	=> $recom,
					   "last_approving" 	=> $fappr,
					   "dtr_summary_rep"	=> $dtr_sum_rep_id,
					   "vercode" 			=> $vercode,
					   "hrnotified" 		=> "0"];
			
			$saved = $this->Globalproc->__save("countersign",$values);
			
			// ----------- allowedsubmit table 
				$cntdets 	= $this->Globalproc->gdtf("countersign",["vercode"=>$vercode],["countersign_id"]);
				$cntid 		= $cntdets[0]->countersign_id;
				$as_details = ["asdtr_sumrep" 		=> $dtr_sum_rep_id,
							   "cnt_id" 			=> $cntid,
							   "emp_id" 			=> $empid,
							   "findings" 			=> null,
							   "status" 			=> "0",
							   "correctedby" 		=> null,
							   "from_db" 			=> null];
			// end 
			$saved = $this->Globalproc->__save("allowedsubmit",$as_details);
			
			echo json_encode($sendto);
		}
		
		function resenddtr() {
			$info 	   = $this->input->post("info");
			$dtrsumrep = $info['dtrsumrep_'];
			$recom 	   = (isset($info['recom_']))?$info['recom_']:null;
			$final 	   = (isset($info['final_']))?$info['final_']:null;
			
			/*
			$dtrsumrep = 11522;
			$recom 	   = "undefined";
			$final     = 50;
			*/
			
			$this->load->model("v2main/Globalproc");
			
			// get from countersign 
			$cdets = $this->Globalproc->gdtf("countersign",['dtr_summary_rep'=>$dtrsumrep],"*");
			
			// get from dtr_summary_reports
			$dsrdets = $this->Globalproc->gdtf("dtr_summary_reports",["sum_reports_id"=>$dtrsumrep],["unid"]);
			
			if (count($dsrdets)==0) {
				return;
			}
			
			if (count($cdets)==0) {
				return;
			}
			
			$approval_status = $cdets[0]->approval_status;
			
			$sendto 		 = null;
			$unid 			 = $dsrdets[0]->unid;
			
			$updatevalues 	 = [];
			if ($approval_status == 0) {
				$sendto    						= $recom;
				$updatevalues['tobeapprovedby'] = $recom;
				$updatevalues['last_approving'] = $final;
			} else if ($approval_status == 1) {
				$sendto    						= $final;
				$updatevalues['last_approving'] = $final;
			}
			
			$update = $this->Globalproc->__update("countersign",$updatevalues,["dtr_summary_rep"=>$dtrsumrep]);
			echo json_encode(["empid" => $sendto,"unid"=>$unid]);
		}
		
		function showwindow() {
			$what = $this->input->post("what");
			
			$this->load->view("v2views/dtrcomponents/{$what}");
		}
		
		function putname() {
			$data['name']  = $this->input->post("name_");
		
			$this->load->view("v2views/dtrcomponents/attname",$data);
		}
		
		function rightbtm_window() {
			$this->load->view("v2views/dtrcomponents/rightbottomdiv");
		}
		
		function testn($tbl='') {

			$data['title'] = '| My Leave Ledger';

			echo "<script>";
				echo "var isadmin = false;";
			echo "</script>";
			
			echo "<style>";
				echo ".leave_ledger_btn {
							background:#96d0f1;
							color: #333 !important;
						}";
			echo "</style>";
			
			if ($tbl == '') {
				$data['dont_display'] = true;
				$data['headscripts']['style'][0]  = base_url()."v2includes/style/hr_dashboard.style.css";	
				$data['headscripts']['style'][1]  = "https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css";
				
				$data['headscripts']['style'][2]  = base_url()."v2includes/style/leavemgt.style.css";
				
				$data['headscripts']['js'][0] 	  = "https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.4/jspdf.debug.js";				
				$data['headscripts']['js'][1]     = base_url()."v2includes/js/windowresize.js";				
				$data['headscripts']['js'][2]     = base_url()."v2includes/js/leavemgt.procs.js";
				$data['headscripts']['js'][3] 	  = base_url()."v2includes/js/my_leave_ledger.js";
				
				$data['main_content'] 			  = "v2views/leavemgt";
			} elseif ($tbl == "coc") {
				$this->load->model("v2main/Globalproc");
				$this->load->model("Globalvars");
				
				if ($emp_id == '' || $this->session->userdata("usertype") != "admin") {
					$emp_id = $this->Globalvars->employeeid;
				}
				
				echo "<script>";
					echo "var t_emp_id = '{$emp_id}'";
				echo "</script>";
				
				$data['emp_name'] 				= $this->Globalproc->gdtf("employees",["employee_id"=>$emp_id],["f_name"])[0]->f_name; 
				
				$data['headscripts']['style'][] = base_url()."v2includes/style/ctoot.style.css";
				$data['headscripts']['js'][] 	= base_url()."v2includes/js/ctoot.js";
				$data['main_content'] 			= "v2views/cto_ledger";
			}
			
			$this->load->view('hrmis/admin_view',$data);
		
		}
		
		function getamstime() {
			$info = $this->input->post("info");
			
			$this->load->model("v2main/Globalproc");
			
			$amstime = $this->Globalproc->gdtf("checkexact_logs",
											  ["exact_id"=>$info['ei']],
											  ['checktime','checktype','shift_type','exact_logs_id']);
			
			echo json_encode($amstime);
		}
		
		function chckams_delete() {
			$info 	  = $this->input->post("info");
			$checkams = $info['chcklogs'];
			$exactid  = $info['ei'];
			
			$this->load->model("v2main/Globalproc");
		
			$sql     = "delete from checkexact_logs where exact_logs_id = '{$checkams}'";
			$removed = $this->Globalproc->run_sql($sql);
			
			$refresh = false;
			if ($removed) {
				$sql_check = "select * from checkexact_logs where exact_id = '{$exactid}'";
				$sql_num   = $this->Globalproc->__getdata($sql_check);
				
				if (count($sql_num)==0) {
					$remsql    = "delete from checkexact where exact_id = '{$exactid}'";
					// echo $remsql;
					$removed = $this->Globalproc->run_sql($remsql);
					$refresh = true;
				}
			}
			
			echo json_encode([$removed,$refresh]);
		}
		
		function timedif() {
			$thetime 						 = "8:46 AM";
			$shift_time 					 = "5:59 PM";
			$start_date 					 = new DateTime($thetime);
			$since_start 					 = $start_date->diff(new DateTime( date("h:i A", strtotime($shift_time))));
			
			echo ($since_start->h-1);
			echo "<br/>";
			echo ($since_start->i);
		}
		
		function test_coctbl() {
			$empid = 33;
			
			$this->load->model("v2main/Globalproc");
			
			$sql  = "select * from employee_ot_credits where emp_id = '{$empid}' order by elc_otcto_id ASC";
			
			$data = $this->Globalproc->__getdata($sql);
			var_dump($data);
		}
		
		function printpeople() {
			$this->load->model("v2main/Globalproc");
			
			$sql = "select biometric_id, f_name from employees where area_id = 1 and status = 1 order by l_name ASC";
		
			$people = $this->Globalproc->__getdata($sql);
			
			echo "<table>";
				foreach($people as $p) {
					echo "<tr>";
						echo "<td style='border:1px solid #ccc;'>".$p->biometric_id."</td>";
						echo "<td style='border:1px solid #ccc;'>".$p->f_name."</td>";
					echo "</tr>";
				}
			echo "</table>";
		}
		
		function sendpassslip() {
			
		}
		
		function displayemps() {
			$this->load->model("v2main/Globalproc");
			$sql = "Select employee_id, biometric_id, f_name from employees";
			
			$people = $this->Globalproc->__getdata($sql);
			
			echo "<table>";
				echo "<tr>";
					echo "<th> Employee ID </th>";
					echo "<th> Biometric ID </th>";
					echo "<th> Full Name</th>";
				echo "</tr>";
				foreach($people as $p) {
					echo "<tr>";
						echo "<td> {$p->employee_id} </td>";
						echo "<td> {$p->biometric_id} </td>";
						echo "<td> {$p->f_name} </td>";
					echo "</tr>";
				}
			echo "</table>";
		}
		
		function uploadatts() {
			echo "<form method='post' enctype='multipart/form-data'>
					<input type='file' name='thefile'/> 
					<input type='submit' value='Attach' name='uploadfile'/>
				  </form>";
			
			if (isset($_POST['uploadfile'])){
				$target_dir    = "uploads";
				
				$basename 	   = basename($_FILES["thefile"]["name"]);
				$basename      = md5($basename)."_".date("mdyhisa");
				$target_file   = $target_dir."/".$basename;
				$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
				
				if (move_uploaded_file($_FILES["thefile"]["tmp_name"], $target_file)) {
					// save to database
					echo "The file has been saved. Save attach button to attach the file.";
				}
			}
		}
		
		function travelorders() {
			$data['title'] 					= '| Add travel order';
			$data['main_content'] 			= "v2views/utils/addnew.php";
			$data['fromemps']				= true;
			$data['headscripts']['style'][]	= base_url()."v2includes/utilities/style.css";
			
			echo "<script>";
				echo "var fromwhatwindow='travelorders';";
			echo "</script>";
			
			$data['headscripts']['js'][]	= base_url()."v2includes/js/dtr.procs.js";
			$data['headscripts']['js'][]	= base_url()."v2includes/utilities/util.js";
			
			$this->load->model("v2main/Globalproc");
			
			$office 		= $this->session->userdata('dbm_sub_pap_id');
			$division		= $this->session->userdata('division_id');
			
			$data['first']  = "TO";
			$data['year']   = $theyear = date("Y");
			$data['month']	= date("m");
			
			// gdtf($table, $where, $details) 
			
			$theanchor = null;
			$thevar    = null;
			if ($division == 0) {
				$theanchor  = $this->Globalproc->gdtf("DBM_Sub_Pap",["DBM_Sub_Pap_id"=>$office],"abbr_dbm");
				$thevar     = "abbr_dbm";
			} else {
				$theanchor 	= $this->Globalproc->gdtf("Division",["Division_Id"=>$division],"abbr");
				$thevar     = "abbr";
			}
			
			$datalist_dbm = $this->Globalproc->gdtf("DBM_Sub_Pap",["1"=>"1"],"abbr_dbm");
			$datalist_div = $this->Globalproc->gdtf("Division",["1"=>"1"],"abbr");
			
			$data['datalist']['dbm'] = $datalist_dbm;
			$data['datalist']['div'] = $datalist_div;
			
			$data['theanchor'] = $theanchor[0]->$thevar;
				
			$sql 			= "Select * from travelorders where thecount = (select max(thecount) from travelorders where theyear = '{$theyear}')";
			
			$a 				= $this->Globalproc->__getdata($sql);
			$data['start']  = (count($a)==0)?1:$a[0]->thecount+1;
			
			$this->load->view('hrmis/admin_view',$data);
		}
		
		function testsocket() {
		
		}
		
		function newtimetable() {
			$this->load->model("v2main/Globalproc");
			$this->load->model("v2main/Newtimerecords");
				
			$data['title']			= "| Daily Time Records";
				
			// $this->input->post("from")
			// $this->input->post("to")
			// $this->input->post("emp")
			// $this->input->post("empbio")
			
			$this->Newtimerecords->setfrom( "2022-4-1" );
			$this->Newtimerecords->setto( "2022-4-30" );
			$this->Newtimerecords->setemp( "389" );
			$this->Newtimerecords->setbio( "306" );

			$data['dtr']	= $this->Newtimerecords->gettime();
			
			var_dump($data['dtr']);
			
			// $this->load->view('v2views/timetable',$data);
		}
		
		function newdailytimerecords($u_empid, $u_empbio,$u_from,$u_to) {
				if($this->session->userdata('is_logged_in') != TRUE){
					redirect(base_url(),"refresh");
				}
				
				$data['title'] 					= "| Daily Time Records";
				
				$data['headscripts']['style'][] = base_url()."/v2includes/style/dailytimerecords.style.css";
				
				$this->load->model("v2main/Globalproc");
				$this->load->model("Globalvars");
				
				// 1 = my, 2 = dailytimerecords 
					//$u_empid  = $this->uri->segment(3);
					//$u_empbio = $this->uri->segment(4);
					//$u_from   = $this->uri->segment(5);
					//$u_to 	  = $this->uri->segment(6);
					
				// isadmin 
					$imadmin  = 0;
					$isowner  = 1;
				// end 
				
					if ($u_empid == NULL) {
						$u_empid = $this->Globalvars->employeeid;
					} else {
						// check if admin 
						$in_quest_id = $this->Globalvars->employeeid;
						$isadmin = $this->Globalproc->gdtf("users",["employee_id"=>$in_quest_id],['usertype','employee_id']);
						
						if (count($isadmin)==0) {
							// found nothing 
							die("The employee is not found."); return;
						}
		
						if ($isadmin[0]->usertype == "admin") {
							// show admin panel 
						//	if ($isadmin[0]->employee_id==$u_empid){
								$imadmin  = 1;
								$isowner  = 1;
						//	} else {
						//		$imadmin  = 1;
						//		$isowner  = 0;
						//	}
						} else if ($isadmin[0]->usertype == "user") {
							if ($u_empid != $isadmin[0]->employee_id) {
								$isowner  = 0;
								die("<p style='text-align: center; margin: 70px; background: #ffe5e5; padding: 32px; font-family: arial;'> You are not allowed to view somebody's DTR. Please return. </p>"); return;
							} else {
								$isowner = 1;
							}
						}
					}
				
				// used by personal users 	
					// $empdata = $this->Globalproc->gdtf("employees",['employee_id'=>$u_empid],['employment_type','biometric_id','f_name']);
					$edsql   = "select 
									e.employment_type,
									e.biometric_id,
									e.f_name,
									u.usertype from employees as e 
									JOIN users as u on e.employee_id = u.employee_id
									where e.employee_id = '{$u_empid}'";
									
					$empdata = $this->Globalproc->__getdata($edsql);
					$emptype = $empdata[0]->employment_type;
					
					if ($empdata[0]->usertype == "admin") {
						$data['admin'] = true;
					}
					
					$dates   		= $this->Globalproc->gdtf("hr_dtr_coverage",['employment_type'=>$emptype],"*");
					$data['dates']	= $dates;
					krsort($data['dates']);
					
					$activedate = array_map(function($key){
						if ($key->is_active == 1) {
							return $key;
						}
					},$data['dates']);
					
					$clean = [];
					foreach($activedate as $key => $val) {
						if ($val == NULL) {
							unset($activedate[$key]);
						} else {
							$clean = $activedate[$key];
						}
					}
			
				// end 
				
				if ($u_empbio == NULL) {
					$u_empbio = $empdata[0]->biometric_id;
				}
				
				if ($u_from == NULL) {
					$u_from = date("Y-m-d",strtotime($clean->date_started));
				}
					
				if ($u_to == NULL) {
					$u_to = date("Y-m-d",strtotime($clean->date_ended));
				}
				
				$data['from']	 = $u_from;
				$data['to']		 = $u_to;
	
				$data['empname'] = strtolower($empdata[0]->f_name);
				
				// isadmin
				$data['headscripts']['js'][] 	= base_url()."/v2includes/vars.js";
				echo "<script>";
					echo "var isadmin = '{$imadmin}';";
					echo "var isowner = '{$isowner}';";
					echo "var dtrurl = '".base_url()."';";
					echo "var empid  = '".$u_empid."';";
					echo "var empbio = '".$u_empbio."';";
					echo "var from   = '".$u_from."';";
					echo "var to     = '".$u_to."';";
				echo "</script>";
				
			//	$data['headscripts']['js'][] 	= base_url()."/v2includes/js/dtr.script.js";
				
					$data['headscripts']['js'][] 	= base_url()."/v2includes/js/json/dtr.values.json";
					$data['headscripts']['js'][] 	= base_url()."/v2includes/js/dtr.procs.js";
					$data['headscripts']['js'][] 	= base_url()."/v2includes/js/process.addingofatt.js";
				
				$data['main_content'] 			= 'v2views/dailytimerecords';
				
				// var_dump($data);
				$this->load->view('hrmis/admin_view',$data);
			}
			
		function addtwohrs() {
			echo date("h:i", strtotime("+1 hour +47 minutes", strtotime("7:3")));
		}
	}