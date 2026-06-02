<?php
	class Action extends CI_Controller {
		protected $action = null;
		protected $id 	  = null;

		public function __construct() {
			parent::__construct();
			
			/*
			if($this->session->userdata('is_logged_in') != true){ 
				redirect('/accounts/login/', 'refresh'); 
			} 
			*/
			
			if($this->session->userdata('is_logged_in') == true){ 
				$this->load->model("v2main/Actiononleave");
			}
			
			/*
			if ( $this->uri->segment(3) != null && $this->uri->segment(4) != null ) {
				// segment 3 is for type of action
				$this->action = $this->uri->segment(3);
				// segment 4 is for id
				$this->id = $this->uri->segment(4);	

				// call on action
				$this->onaction();
			}
			*/
		}

		public function application() {
			$this->load->model("v2main/Globalproc");

			$action  = $this->uri->segment(3);
			$leaveid = $this->uri->segment(4);

			// for disapprove
			$message = $this->uri->segment(5);

			$title = null;
			$main  = null;

			// get the hrisranking
			$hrisrank = $this->Actiononleave->__gethrisrank();
			$hrisrank = $hrisrank[0]->hrisrank;

			
			if (isset($action)) {
				// GET APPROVAL STATUS
					$this->load->model("v2main/Leaveprocs");
					$approval_status = $this->Leaveprocs->get_approval_status($leaveid);
					$data['status']	 = $approval_status;

				// END
				
				switch($action) {
					case "view":
						$title = "| View Application";

						// browse for the applied leave
						$a = $this->Actiononleave->getleavedetails($leaveid);
						
						// $typeofleave = strtolower($a['leave_name']);
						$typeofleave  = strtolower($a['leave_code']);

						$data['leave_dets'] = $a;

						$data['returned'] 	= "for_approval";

						switch($typeofleave){
							case "spl":
								$this->load->model("v2main/Formfetcher");
								$main  			         = "v2views/forms/spl";
								$data['headscripts']['style'] = base_url()."v2includes/style/spl.style.css";

								$this->load->model("v2main/Leaveprocs");
								$spl_restrict = $this->Leaveprocs->spl($a['empid']);
								
									$class = null;
									$text  = null;

									// if exhausted
									$exhaust = false;
									$e_text  = null;
										
										switch( $spl_restrict ) {
											case "1":
												$class = "__1of3";
												$text  = "ONE";
											break;
											case "2":
												$class = "__2of3";
												$text  = "TWO";
											break;
											case "3":
												$class = "__3of3";
												$text  = "THREE";
												$e_text = "You are only allowed three SPL per year";
												$exhaust = true;
											break;
											case null:
												$class = "__0of3";
												$text  = "NONE";
											break;
										}
									$data['class'] = $class;
									$data['text']  = $text;

									// if exhausted
									$data['exhausted'] = $exhaust;
									$data['caption']   = $e_text;
									// end

								break;
							case "paf":
								$this->load->model("v2main/Formfetcher");
								$main  			         = "v2views/forms/paf";
								$data['headscripts']['style'] = base_url()."v2includes/style/paf.style.css";
								break;

							case "sl":
								$this->load->model("v2main/Formfetcher");
								$main  			         = "v2views/forms/sickleave";
								$data['headscripts']['style'] = base_url()."v2includes/style/sickleave.style.css";
								break;
							case "vl":
								$this->load->model("v2main/Formfetcher");
								$main 				 	 = "v2views/forms/vacationleave";
								$data['headscripts']['style'] = base_url()."v2includes/style/genericform.style.css";
								$data['leavetype'] = "vl";
								$data['leavename'] = $this->Globalproc->get_details_from_table("leaves",["leave_code"=>"VL"],["leave_name"])["leave_name"];
								break;
							case "ml":
								$this->load->model("v2main/Formfetcher");
								$main 				 	 = "v2views/forms/vacationleave";
								$data['headscripts']['style'] = base_url()."v2includes/style/genericform.style.css";
								$data['leavetype'] = "ml";
								$data['leavename'] = $this->Globalproc->get_details_from_table("leaves",["leave_code"=>"ML"],["leave_name"])["leave_name"];
								break;
							case "pl":
								$this->load->model("v2main/Formfetcher");
								$main 				 	 = "v2views/forms/vacationleave";
								$data['headscripts']['style'] = base_url()."v2includes/style/genericform.style.css";
								$data['leavetype'] = "pl";
								$data['leavename'] = $this->Globalproc->get_details_from_table("leaves",["leave_code"=>"PL"],["leave_name"])["leave_name"];
								break;
							case "fl":
								$this->load->model("v2main/Formfetcher");
								$main 				 	 = "v2views/forms/vacationleave";
								$data['headscripts']['style'] = base_url()."v2includes/style/genericform.style.css";
								$data['leavetype'] = "fl";
								$data['leavename'] = $this->Globalproc->get_details_from_table("leaves",["leave_code"=>"FL"],["leave_name"])["leave_name"];
								break;
							case "rl":
								$this->load->model("v2main/Formfetcher");
								$main 				 	 = "v2views/forms/vacationleave";
								$data['headscripts']['style'] = base_url()."v2includes/style/genericform.style.css";
								$data['leavetype'] = "rl";
								$data['leavename'] = $this->Globalproc->get_details_from_table("leaves",["leave_code"=>"RL"],["leave_name"])["leave_name"];
								break;
							case "csc":
								$this->load->model("v2main/Formfetcher");
								$main 				 	 = "v2views/forms/vacationleave";
								$data['headscripts']['style'] = base_url()."v2includes/style/genericform.style.css";
								$data['leavetype'] = "csc";
								$data['leavename'] = $this->Globalproc->get_details_from_table("leaves",["leave_code"=>"CSC"],["leave_name"])["leave_name"];
								break;
							case "gyne":
								$this->load->model("v2main/Formfetcher");
								$main 				 	 = "v2views/forms/vacationleave";
								$data['headscripts']['style'] = base_url()."v2includes/style/genericform.style.css";
								$data['leavetype'] = "gyne";
								$data['leavename'] = $this->Globalproc->get_details_from_table("leaves",["leave_code"=>"gyne"],["leave_name"])["leave_name"];
								break;
							case "sp":
								$this->load->model("v2main/Formfetcher");
								$main 				 	 = "v2views/forms/vacationleave";
								$data['headscripts']['style'] = base_url()."v2includes/style/genericform.style.css";
								$data['leavetype'] = "sp";
								$data['leavename'] = $this->Globalproc->get_details_from_table("leaves",["leave_code"=>"SP"],["leave_name"])["leave_name"];
								break;
							case "vawcy":
								$this->load->model("v2main/Formfetcher");
								$main 				 	 = "v2views/forms/vacationleave";
								$data['headscripts']['style'] = base_url()."v2includes/style/genericform.style.css";
								$data['leavetype'] = "vawcy";
								$data['leavename'] = $this->Globalproc->get_details_from_table("leaves",["leave_code"=>"VAWCY"],["leave_name"])["leave_name"];
								break; 
							case "tl":
								$this->load->model("v2main/Formfetcher");
								$main 				 	 = "v2views/forms/vacationleave";
								$data['headscripts']['style'] = base_url()."v2includes/style/genericform.style.css";
								$data['leavetype'] = "tl";
								$data['leavename'] = $this->Globalproc->get_details_from_table("leaves",["leave_code"=>"TL"],["leave_name"])["leave_name"];
								break; 
							case "mplap":
								$this->load->model("v2main/Formfetcher");
								$main 				 	 = "v2views/forms/vacationleave";
								$data['headscripts']['style'] = base_url()."v2includes/style/genericform.style.css";
								$data['leavetype'] = "mplap";
								$data['leavename'] = $this->Globalproc->get_details_from_table("leaves",["leave_code"=>"MPLAP"],["leave_name"])["leave_name"];
								break; 
							case "study":
								$this->load->model("v2main/Formfetcher");
								$main 				 	 = "v2views/forms/vacationleave";
								$data['headscripts']['style'] = base_url()."v2includes/style/genericform.style.css";
								$data['leavetype'] = "study";
								$data['leavename'] = $this->Globalproc->get_details_from_table("leaves",["leave_code"=>"STUDY"],["leave_name"])["leave_name"];
								break; 
							case "el":
								$this->load->model("v2main/Formfetcher");
								$main 				 	 = "v2views/forms/vacationleave";
								$data['headscripts']['style'] = base_url()."v2includes/style/genericform.style.css";
								$data['leavetype'] = "el";
								$data['leavename'] = $this->Globalproc->get_details_from_table("leaves",["leave_code"=>"EL"],["leave_name"])["leave_name"];
								break;
						}

						break;

					case "approve":
						$title = "| Approve Application";

						// process
						if ($this->Actiononleave->__approve($leaveid, $hrisrank)){
						// end process
							// finally
							echo "Approved. You are being redirected...";
							
							echo "<script>";
								echo "setTimeout(function() {";
									redirect("action/application/", "refresh");
								echo "}, 10000)";	
							echo "</script>";
						} else {
							echo "an error occured...";
						}
						break;

					case "disapprove":
						$title = "| Disapprove Application";

						if ($this->Actiononleave->__disapprove($leaveid, $message, $hrisrank."d")) {
							echo "Disapproved. You are being redirected...";

							echo "<script>";
								echo "setTimeout(function() {";
									redirect("action/application/", "refresh");
								echo "}, 10000)";	
							echo "</script>";
						}

						break;
				}
				
				$data['title'] 		  = $title;
				$data['main_content'] = $main;
				$this->load->view('hrmis/admin_view',$data);

				return;
			}

			// get the ranking of the logged in officer
			// formerly 
			#$details = ["hrisrank","org_tree"];

			$details = ["hrisrank","DBM_Pap_id"];
			$retdet  = $this->Globalproc->get_empdetails($details);

			$division  = $retdet['DBM_Pap_id'];

			// if logged in account's hrisrank is 1
				// get employees with 0
			// if logged in account's hrisrank is 2
				// get employees with 1
			// if logged in account's hrisrank is above 2
				// get employees with 2
			$emp_level = null;

			if ($retdet['hrisrank'] == '1' || $retdet['hrisrank'] == 1) { // division chief is logged in
				$emp_level = 0; // get low ranking employee
			} else if ($retdet['hrisrank']=='2' || $retdet['hrisrank']==2) { // director is logged in
				$emp_level = 1; // get division chief level employee
			} else if ($retdet['hrisrank']>'2' || $retdet['hrisrank']>2) { // above director level is logged in
				$emp_level = 2; // get director level employee
			}

			if ($emp_level = null || $emp_level = 0) {	
				die("Your ranking is below 1... cannot proceed!!!");
			}
			
			$data['la'] = $this->Actiononleave->getapplications($division,$retdet['hrisrank']);
			
			$data['title'] 		  = '| Action on application';
			$data['main_content'] = "v2views/actiononapplication";
			$this->load->view('hrmis/admin_view',$data);
		}
		
		function form() {
			// https://office.minda.gov.ph:9003/action/form/approve/1ec23b-1f9113/33/25aee0e8ce9/false

			$action  		= $this->uri->segment(3); 
			$checkid 		= $this->uri->segment(4); // grp_id
			$approvedby 	= $this->uri->segment(5); 
			$passedtoken 	= $this->uri->segment(6); 
			$isfinal 		= $this->uri->segment(7); 
			
			// allow login here ##############################################
				$DB2   = $this->load->database('sqlserver', TRUE);
				
				$query = "Select * from users where employee_id = '{$approvedby}'";
				
				$query  = $DB2->query($query);
				$result = $query->result();
				
				if (count($result)==0) {
					die("Approving official is not listed in the database. This activity is recorded.");
					return;
				}
				
				
				$this->load->model("Login_model");
				$result2      = $this->Login_model->getUserInformation($approvedby);
				
				$user_session = array(
					'employee_id' => $approvedby,
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
				
			// allow login here ##############################################
			
			$this->load->model("v2main/Globalproc");
			$this->load->model("Attendance_model");
			$this->load->model("Globalvars");
			//  http://office.minda.gov.ph:9003/action/form/approve/6507/349/0a090174382/true 		
			
			$logged_id = $this->Globalvars->employeeid;
			
			$token = $this->Globalproc->tokenizer_leave($approvedby.$checkid);
		//	echo $token;
			
			if ($isfinal == "true") {
				$isfinal = true;
			} else if ($isfinal == "false") {
				$isfinal = false;
			} else {
				$isfinal = null;
			}
			
			if ($passedtoken != $token) { die("The Token is invalid.");}
			if ($passedtoken == NULL || $approvedby == NULL || $checkid == NULL || $action == NULL){
				die("Some Parameter is missing.");
			}
			
			//$status 	= $this->Globalproc->gdtf("checkexact",["exact_id"=>$checkid],['is_approved','aprroved_by_id','employee_id','checkdate']);
			$status 	= $this->Globalproc->gdtf("checkexact",["grp_id"=>$checkid],"*"); // ['is_approved','aprroved_by_id','employee_id','checkdate','type_mode']
			
			// if not in status variable, meaning its in different table, can be OT or CTO.
				if (count($status) == 0){
					$in_ot = $this->Globalproc->gdtf("checkexact_ot",['checkexact_ot_id'=>$checkid],"*");
					
					if (count($in_ot)==0) {
						die("The form you requested is not found. It could have been cancelled by the user.");
					}
					
					// approval of OT 
						// check if approved by the first approving official
							if ($in_ot[0]->div_is_approved == 0) {
								if ($action == "approve") {
									$r = $this->Globalproc->__update('checkexact_ot',
																	["div_is_approved"=>1,
																	 "div_date_approved"=>date("m/d/Y")],
																	['checkexact_ot_id'=>$checkid]);
									if (!$r) {
										die("error");
									}
								} else if ($action == "decline") {
									$r = $this->Globalproc->__update('checkexact_ot',
																	["div_is_approved"=>0,
																	 "div_date_approved"=>date("m/d/Y")],
																	['checkexact_ot_id'=>$checkid]);
								}
							} else if ($in_ot[0]->div_is_approved == 1) {
								if ($in_ot[0]->act_div_is_approved == 0) {
									if ($action == "approve") {
										$r = $this->Globalproc->__update('checkexact_ot',
																		["act_div_is_approved"=>1,
																		 "act_div_date_approved"=>date("m/d/Y"),
																		 "act_div_a_is_approved"=>1,
																		 "act_div_a_date_approved"=>date("m/d/Y")],
																		['checkexact_ot_id'=>$checkid]);
										// save to OT 
										$start = $in_ot[0]->ot_requested_time_in;
										$end   = $in_ot[0]->ot_requested_time_out;
											
										$in    = date("h:i A", strtotime($start));
										$out   = date("h:i A", strtotime($end));
										
										// =============================										
										$is_start   = date("A", strtotime($start));
										$is_end     = date("A", strtotime($end));

										$ot_date = date("m/d/Y",strtotime($start));
										
										$values = [
												"am_in" 			  => null,
												"am_out"			  => null,
												"dates" 			  => [$ot_date],
												"empid"			      => $in_ot[0]->employee_id,
												"mult"				  => null,
												"pm_in"				  => null,
												"pm_out"			  => null,
												"typemode"			  => "OT",
												"exact_ot"			  => $checkid
											];
												// 
												$am_in  = null;
												$am_out = null;
												$pm_in  = null;
												$pm_out = null;
												
												$is_am  = false;
												if ($is_start == "AM" || $is_start == "am") {
													$values['am_in']  = $in;													
													if ($is_end == "PM" || $is_end == "pm") {
														$values['am_out'] = "12:00 PM";
														$values['pm_in']  = "1:00 PM";
														$values['pm_out'] = date("h:i A", strtotime($end));
														$values['isam']   = true;
														$values['ispm']   = true;
													}
													
													if ($is_end == "AM" || $is_end == "AM") {
														$values['am_out'] = date("h:i A", strtotime($end));
														$values['isam'] = true;
													}
												} elseif($is_start == "PM" || $is_start == "pm") {
													$values['pm_in']  = $in;
													$values['pm_out'] = date("h:i A", strtotime($end));
													$values['ispm'] = true;
												}
											// =============================
												
												if ($in_ot[0]->is_ot_type == 1) {
													$values['mult']	= 1; // rw
												} elseif ($in_ot[0]->is_ot_type == 2) {
													$values['mult']	= 1.5; // st
												}
											
											// the variable values here is not in used... the crediting of OT is based on the approval of the OT accomplishment Report.
											//$r = $this->Globalproc->saveto_ot($values);
											$r = true;
											// mark ot sendemail
												if ($r) {
													// .",merto.alvinjay@gmail.com"
													// send email to employee and HR
													$applicant  = $this->Globalproc->gdtf("employees",["employee_id"=>$in_ot[0]->employee_id],['email_2',"f_name"]);
													$appby      = $this->Globalproc->gdtf("employees",["employee_id"=>$in_ot[0]->act_div_chief_id],["email_2","f_name","firstname","l_name"]);
													$details = [
														"to"   	  => $applicant[0]->email_2,
														"from" 	  => "'{$appby[0]->firstname} {$appby[0]->l_name}'",
														"subject" => "FINAL: Overtime Approved",
														"message" => "The Overtime application of {$applicant[0]->f_name} has been approved by {$appby[0]->f_name}"
													];
													// mark send email 3
													$ret = $this->Globalproc->sendtoemail($details);
													//$ret = true;
												}
											
										if(!$r) {
											die("error");
										}
									} else if ($action == "decline") {
										$r = $this->Globalproc->__update('checkexact_ot',
																		["act_div_is_approved"=>0,
																		 "act_div_date_approved"=>date("m/d/Y"),
																		 "act_div_a_is_approved"=>0,
																		 "act_div_a_date_approved"=>date("m/d/Y")],
																		['checkexact_ot_id'=>$checkid]);
										if (!$r) {
											die("error");
										}
									}
								} else if ( $in_ot[0]->act_div_is_approved ==1 ) {
									die("This form is already been approved.");
								}
							}
							
						// mark send email 1	
						$ret = $this->Attendance_model->send_email($checkid, $in_ot[0]->employee_id,"OT");
						//$ret = true;
							
							echo "<head>
										<link rel='stylesheet' href='".base_url()."assets/bower_components/font-awesome/css/font-awesome.min.css'/>
								  </head>";
									  
							if ($ret && $action == "approve") {
								echo "<style>
										html {
											background: #30b1b5;
										}
										
										.success_p {
											text-align: center;
											color: #fff;
											font-size: 22px;
											font-family: arial;
											text-align: center;
										}
										
										.checkicon {
											font-size: 142px;
											margin-bottom: 30px;
											text-shadow: 0px 3px 6px #175658;
										}
									  </style>";
									  
								echo "<p class='success_p'> 
										<i class='checkicon fa fa-check' aria-hidden='true'></i><br/>
										You have successfully approved the form. 
									</p>";
							} else if ($ret && $action == "decline") {
								echo "<style>
										html {
											background: rgba(16, 16, 16, 0.85);
										}
										
										.success_p {
											text-align: center;
											color: #fff;
											font-size: 22px;
											font-family: arial;
											text-align: center;
										}
										
										.checkicon {
											font-size: 142px;
											margin-bottom: 30px;
											text-shadow: 0px 3px 6px rgba(0, 0, 0, 0.3);
										}
									  </style>";
									  
								echo "<p class='success_p'> 
										<i class='checkicon fa fa-frown-o' aria-hidden='true'></i> <br/>
										You have declined the application. 
									</p>";	
							}
							
						// end 
						
					// end Approval of OT
					
					return;
				}
			// end
			
			$appby      = $this->Globalproc->gdtf("employees",['employee_id'=>$approvedby],["f_name",'firstname','l_name']);
				//	echo $appby[0]->firstname;
				//	return;
			
			// applicant
			$applicant  = $this->Globalproc->gdtf("employees",["employee_id"=>$status[0]->employee_id],['email_2',"f_name"]);
			
			if ($status[0]->is_approved == 2) {
				echo "<p> This form has already been declined. </p>";
				return;
			} else if ($status[0]->is_approved == 1) {
				echo "<p> This form has already been approved. </p>";
				return;
			}
				
			$ret = false; // change to false

						// calculate leave credits
						/**/	$empid = $status[0]->employee_id;
						/**/	if ($status[0]->type_mode != "PS") {
						/**/					
						/**/		$leave_details = [
						/**/			"typemode" 		  => strtolower($status[0]->type_mode),
						/**/			"leave_value" 	  => $status[0]->leave_id,
						/**/			"no_days_applied" => count($status),
						/**/			"date_inclusion"  => null
						/**/		];
						/**/				
						/**/		$themonth = null;
						/**/		$theday   = null;
						/**/		$theyear  = null;
						/**/		
						/**/		if ($status[0]->type_mode == "PAF") {
						/**/			if($status[0]->type_mode_details == "half") {
						/**/				$leave_details['hrs'] 	= "4";
						/**/			} else if ($status[0]->type_mode_details == "whole") {
						/**/				$leave_details['hrs'] 	= "8";
						/**/			}
						/**/		}
						/**/		
						/**/			for ($i = 0; $i <= count($status)-1; $i++) {
						/**/				// "date_inclusion"  => $status[0]->type_mode,
						/**/				$theday   = date("d", strtotime($status[$i]->checkdate));
						/**/				$theyear  = date("Y", strtotime($status[$i]->checkdate));
						/**/				
						/**/				if ( $themonth == null ) {
						/**/					$themonth 						 = date("M", strtotime($status[$i]->checkdate));
						/**/					$leave_details['date_inclusion'] = $themonth ." ".$theday;
						/**/					
						/**/					if ($i != count($status)-1) {
						/**/						$leave_details['date_inclusion'] .= "-";
						/**/					}
						/**/					
						/**/				} else {
						/**/					if ( $themonth != date("M", strtotime($status[$i]->checkdate)) ) {
						/**/						$themonth = date("M", strtotime($status[$i]->checkdate));
						/**/						$leave_details['date_inclusion'] .= " || ". $themonth . " ";
						/**/					}
						/**/					
						/**/					$leave_details['date_inclusion'] .= "".$theday;
						/**/					
						/**/					if ($i != count($status)-1) {
						/**/						$leave_details['date_inclusion'] .= "-";
						/**/					}
						/**/					
						/**/				}
						/**/				
						/**/			}
						/**/			
						/**/		$leave_details['date_inclusion'] .= ", ".$theyear;
						/**/		//$ret    = $this->Globalproc->calc_leavecredits($empid, $checkid, $leave_details); 
						/**/	}
						// end calculation 
			
			if ($action == "approve") {
				$empid = $status[0]->employee_id;
				
				$check_initial_status = $this->Globalproc->gdtf("checkexact_approvals",["grp_id"=>$checkid],["division_chief_is_approved","division_chief_id","leave_authorized_is_approved","leave_authorized_official_id"]);
				
				// chief 
					$is_div_approved = $check_initial_status[0]->division_chief_is_approved;
					$div_chief_id    = $check_initial_status[0]->division_chief_id;
				// end chief

				// DBM 
					//$is_dbm_approved = $check_initial_status[0]->leave_authorized_is_approved;
					//$dbm_id 		   = $check_initial_status[0]->leave_authorized_official_id;
				// end DBM
								
				$ret = false;
				$phase = "FINAL: ";
				$final = true;
				
				if (!$is_div_approved) { // first approval
					
					if ($logged_id != $div_chief_id) {
						die("Approving official not recognized.");
						return;
					}
					$phase = "INITIAL: ";
					$final = false;
					$ret = $this->Globalproc->__update('checkexact_approvals',
														["division_chief_is_approved"=>1,
														 "division_chief_id"=>$approvedby,
														 "division_date" => date("m/d/Y")],
														['grp_id'=>$checkid]);
						
						if ($status[0]->type_mode == "PS") {
							$ret = $this->Globalproc->__update('checkexact_approvals',
														["leave_authorized_is_approved"=>1,
														 "leave_authorized_official_id"=>$approvedby,
														 "leave_authorized_date" => date("m/d/Y")],
														['grp_id'=>$checkid]);
							if ($ret) {
								$ret = $this->Globalproc->__update("checkexact",
															["is_approved"	  => 1,
															 "aprroved_by_id" => $approvedby],
														    ["grp_id"=>$checkid]);
								
								//$ret    = $this->Globalproc->calc_leavecredits($empid, $checkid, $ps_details); 
							}
						}
				
				} else if ($is_div_approved) { // last approval
					if ($logged_id != $check_initial_status[0]->leave_authorized_official_id) {
						die("Approving official not recognized.");
						return;
					}
					$ret = $this->Globalproc->__update('checkexact_approvals',
														["leave_authorized_is_approved"=>1,
														 "leave_authorized_official_id"=>$approvedby,
														 "leave_authorized_date" => date("m/d/Y"),
														 "paf_is_approved"=>1],
														['grp_id'=>$checkid]);
					
					if ($ret) {
						$ret = $this->Globalproc->__update("checkexact",
															["is_approved"	  => 1,
															 "aprroved_by_id" => $approvedby],
														    ["grp_id"=>$checkid]);
					}
					
				}
			
			// mark send email 2
				$ret = $this->Attendance_model->send_email($checkid, $status[0]->employee_id);
			//	$ret = true;
				if ($ret) {
					if ($isfinal || $isfinal == "true" || $final) {
						$empid = $status[0]->employee_id;
						//$ret   = $this->Globalproc->calc_leavecredits($empid, $checkid, $leave_details); 
							
							// calculate leave credits
							if ($status[0]->type_mode != "PS") {
											
								$leave_details = [
									"typemode" 		  => strtolower($status[0]->type_mode),
									"leave_value" 	  => $status[0]->leave_id,
									"no_days_applied" => count($status),
									"date_inclusion"  => null
								];
										
								$themonth = null;
								$theday   = null;
								$theyear  = null;
								
								if ($status[0]->type_mode == "PAF") {
									if($status[0]->type_mode_details == "half") {
										$leave_details['hrs'] 	= "4";
									} else if ($status[0]->type_mode_details == "whole") {
										$leave_details['hrs'] 	= "8";
									}
								}
								
									for ($i = 0; $i <= count($status)-1; $i++) {
										// "date_inclusion"  => $status[0]->type_mode,
										$theday   = date("d", strtotime($status[$i]->checkdate));
										$theyear  = date("Y", strtotime($status[$i]->checkdate));
										
										if ( $themonth == null ) {
											$themonth 						 = date("M", strtotime($status[$i]->checkdate));
											$leave_details['date_inclusion'] = $themonth ." ".$theday;
											
											if ($i != count($status)-1) {
												$leave_details['date_inclusion'] .= "-";
											}
											
										} else {
											if ( $themonth != date("M", strtotime($status[$i]->checkdate)) ) {
												$themonth = date("M", strtotime($status[$i]->checkdate));
												$leave_details['date_inclusion'] .= " || ". $themonth . " ";
											}
											
											$leave_details['date_inclusion'] .= "".$theday;
											
											if ($i != count($status)-1) {
												$leave_details['date_inclusion'] .= "-";
											}
											
										}
										
									}
									
								if ($status[0]->type_mode == "CTO") {
									/*
									$leave_details["hrs_start"] = $status[0]->time_in;
									$leave_details["hrs_end"]	= $status[0]->time_out;
									$leave_details["empid"]		= $status[0]->employee_id;
									$leave_details["exact_ot"]	= $checkid;
									*/
									
									// $leave_details['date_inclusion'] .= ", ".$theyear;
									
									for ($i = 0; $i <= count($status)-1; $i++) {
										$leave_details["hrs_start"]       = $status[$i]->time_in;
										$leave_details["hrs_end"] 	      = $status[$i]->time_out;
										$leave_details["empid"]		      = $status[$i]->employee_id;
										$leave_details["exact_ot"]	      = $checkid;	
										$leave_details['date_inclusion']  = date("F d, Y", strtotime($status[$i]->checkdate));
										$leave_details['formonet']		  = 0;
										$ret    						  = $this->Globalproc->calc_leavecredits($empid, $checkid, $leave_details); 
									}
								}
								
								if ($status[0]->type_mode != "CTO") {
									$leave_details['date_inclusion'] .= ", ".$theyear;
									$ret    						  = $this->Globalproc->calc_leavecredits($empid, $checkid, $leave_details); 
								}
							} else {
								// ps 
							}
							// end calculation
							
					} // end isfinal if
					
				// send email to employee and HR .",merto.alvinjay@gmail.com"
					$details = [
						"to"   	  => $applicant[0]->email_2,
						"from" 	  => "'{$appby[0]->firstname} {$appby[0]->l_name}'",
						"subject" => "{$phase} Approved",
						"message" => "The {$status[0]->type_mode} application of {$applicant[0]->f_name} has been approved by {$appby[0]->f_name}"
					];
					// mark send email 3
					$ret = $this->Globalproc->sendtoemail($details);
					//$ret = true;
				}

			} else if ($action == "decline") {
				$officially_declined = false;
				
				if (isset($_POST['sendremarks'])) {
					$remarks 			 = $_POST['remarkstext'];	
					$ret 				 = false;
					
					$a_offs 			 = $this->Globalproc->gdtf("checkexact_approvals",["grp_id"=>$checkid],"*");
				
					if ( $logged_id == $a_offs[0]->division_chief_id ) { 
						$ret = $this->Globalproc->__update("checkexact_approvals",["division_chief_remarks"=>$remarks],["grp_id"=>$checkid]);
						// email to last approving official
						$ret 				 = $this->Attendance_model->send_email($checkid, $status[0]->employee_id);
						$officially_declined = false;
						// set officially_declined = false;
					} else if ($logged_id == $a_offs[0]->leave_authorized_official_id) {
						$ret 					 = $this->Globalproc->__update("checkexact_approvals",["leave_authotrized_remarks"=>$remarks],["grp_id"=>$checkid]);
						if ($ret) {
							$officially_declined = true;
						}
					} else {
						die("the approving official is invalid."); return;
					}
					
				}
				echo "<style>";
					echo "
						html {
							background: #f3f3f3;
						}
						
						body {
							font-family:arial;
						}
						
						.sendremarks {
							width: 45%;
							margin: auto;
							border: 1px solid #cacaca;
							padding: 3px 17px 15px;
							border-radius: 3px;
							box-shadow: 0px 2px 2px #dadada;
							background: #e8e8e8;
						}
						
						textarea {
							width: 100%;
							min-height:200px;
							resize:vertical;
						}
						
						input[type='submit'] {
							padding: 10px 20px;
							font-size: 14px;	
						}
						
						.caption {
						    font-size: 15px;
							padding: 8px 0px;
						}
					";
				echo "</style>";
				
				echo "<form method='POST'>";
				echo "<table class='sendremarks'>
						<tr> 
							<td> <p class='caption'> <i class='fa fa-paper-plane-o' aria-hidden='true'></i> Send Remarks </p> </td>
						</tr>
						<tr>
							<td> <textarea name='remarkstext'></textarea> </td>
						</tr>
						<tr>
							<td> <input type='submit' value='Send and Decline' name='sendremarks'/> </td>
						</tr>
					  </table>
					";
				echo "</form>";
			if (isset($_POST['sendremarks'])) {
				$remarks = $_POST['remarkstext'];
					
					/*
					=========================
						$ret = $this->Globalproc->__update("checkexact",
														  ["is_approved"=>$approval_type,"aprroved_by_id" => $approvedby,"remarks"=>$remarks],
														  ["exact_id"=>$checkid]);
						*/
						/*
						$check_initial_status = $this->Globalproc->gdtf("checkexact_approvals",
																		["grp_id"=>$checkid],
																		["division_chief_is_approved",
																		 "division_chief_id",
																		 "leave_authorized_is_approved",
																		 "leave_authorized_official_id"]);
						
						$is_div_chief_status   = $check_initial_status[0]->division_chief_is_approved;
						
						if ($is_div_chief_status == true) {
							$ret = $this->Globalproc->__update('checkexact_approvals',
														  ["leave_authorized_is_approved"=>0,"leave_authotrized_remarks"=>$remarks],
														  ['grp_id'=>$checkid]);
						} else if ($is_div_chief_status == false) {
							
						}
					==========================
					*/
					
					// mark cancel
					
					// check if the id in the url is in the approving officials
						// check what level is the ID
					
					// if first level, 
						// then mark decline and save declining message to first approving official
					
					// if second level
						// then mark the decline and save the declining message to second approving officials	
						// mark in the checkexact table to approval status to 2, meaning declined.
						// update employee_leave_credits
					
					// $approvedby
					// $checkid 
					
				
					
					if ($ret) {
						
						// $ret = $this->Globalproc->__update("checkexact",["is_approved"=>2,"aprroved_by_id"=>$approvedby],["grp_id"=>$checkid]);
						/*
						if ($ret) {
							if ($isfinal) {
								$ret = $this->Globalproc->__update('checkexact_approvals',
													  ["leave_authorized_is_approved"=>0,"leave_authotrized_remarks"=>$remarks],
													  ['grp_id'=>$checkid]);
							} else {
								$ret = $this->Globalproc->__update('checkexact_approvals',
													  ["division_chief_is_approved"=>0,"division_chief_remarks"=>$remarks],
													  ['grp_id'=>$checkid]);
							}
						}
						*/
						
						if ($officially_declined) {
							$ret = $this->Globalproc->__update("checkexact",["is_approved"=>2],["grp_id"=>$checkid]);
							if ($ret) {
								// transfer this function to the choose method 
								
								$leave_details['value']	= "declined";
								//var_dump($leave_details);
								//$ret = $this->Globalproc->calc_leavecredits($empid, $checkid, $leave_details); 	
								$ret = true;
							}
							
						}
						
						// leave 
						$leave_template = null;
						if(strtolower($status[0]->type_mode) == "leave") {
							if ($officially_declined == true) {
							$leave_template = "<p> <strong> Choose what to do. </strong> </p>
												<table style='width:100%;'>
													<tr>
														<td style='width: 50%; text-align:right;'>
															<a href='".base_url()."action/choose/{$checkid}/{$empid}/continue' style='border-radius: 50px; text-decoration:none; color:#3e474d; border: 1px solid #ccc; padding: 5px 11px; background: #a4fff9; font-size: 15px;'> Continue with the leave </a>
														</td>
														<td style='width: 50%; text-align:left;'>
															<a href='".base_url()."action/choose/{$checkid}/{$empid}/cancel' style='border-radius: 50px; text-decoration:none; color:#3e474d; border: 1px solid #ccc; padding: 5px 11px; background: #a4fff9; font-size: 15px;'> Cancel </a>
														</td>
													</tr>	
												</table>";
							}
						}
						// end leave 
						// send email to employee and HR
						$template = "<html>
										<body>
											<div style='width: 100%;
														background: #e0e0e0;
														padding: 7px; font-size:18px;'>
												<div style='width: 30%;
															margin: 10px auto;
															background: #fff;
															border: 1px solid #ccc;'>
													<div style='padding:10px; padding: 22px; background: #f7d2d2;'>
														<h3 style='margin:0px; font-family: calibri;'> Your Leave application was declined </h3>
													</div>
													<div style='padding: 17px; font-family: calibri;'>
														<p style='margin:0px;'> Approving official: <strong> {$appby[0]->f_name} </strong> </p>
														<hr style='margin: 23px 0px; height: 0px; border: 0px; border-bottom: 1px solid #ccc;'/>
														<p> <strong> Remarks: </strong> </p>
														<p> {$remarks} </p>
														<hr style='margin: 23px 0px; height: 0px; border: 0px; border-bottom: 1px solid #ccc;'/>
													</div>
													{$leave_template}
													<div style='padding: 13px;
																background: #f1f1f1;
																border-top: 1px solid #ccc;'>
														<p style='margin:0px; text-align:center; font-family: calibri; color: #959595;'> MinDa </p>
													</div>
												</div>
											</div>
										</body>
									</html>";
									//.",merto.alvinjay@gmail.com"
						$details = [ 
							"to"   	  => $applicant[0]->email_2,
							"from" 	  => "'{$appby[0]->firstname} {$appby[0]->l_name}'",
							"subject" => "Sorry to disapprove your application.",
							"message" => $template
						];
						
						if ($ret) {
							// mark sendemail 4
							$ret = $this->Globalproc->sendtoemail($details);
							//$ret = true;
							//echo "email sent...";
						}
					}
					
				}
			}
			
			echo "<head>
						<link rel='stylesheet' href='".base_url()."assets/bower_components/font-awesome/css/font-awesome.min.css'/>
					  </head>";
					  
			if ($ret && $action == "approve") {
				echo "<style>
						html {
							background: #30b1b5;
						}
						
						.success_p {
							text-align: center;
							color: #fff;
							font-size: 22px;
							font-family: arial;
							text-align: center;
						}
						
						.checkicon {
							font-size: 142px;
							margin-bottom: 30px;
							text-shadow: 0px 3px 6px #175658;
						}
					  </style>";
					  
				echo "<p class='success_p'> 
						<i class='checkicon fa fa-check' aria-hidden='true'></i><br/>
						You have successfully approved the leave. 
					</p>";
			} else if ($ret && $action == "decline") {
				echo "<style>
						html {
							background: rgba(16, 16, 16, 0.85);
						}
						
						.success_p {
							text-align: center;
							color: #fff;
							font-size: 22px;
							font-family: arial;
							text-align: center;
						}
						
						.checkicon {
							font-size: 142px;
							margin-bottom: 30px;
							text-shadow: 0px 3px 6px rgba(0, 0, 0, 0.3);
						}
					  </style>";
					  
				echo "<p class='success_p'> 
						<i class='checkicon fa fa-frown-o' aria-hidden='true'></i> <br/>
						You have declined the application. 
					</p>";	
			}
		/*	
			else {
				echo "<style>
						html {
							background: #5f0303;
						}
						
						.checkicon {
							font-size: 142px;
							margin-bottom: 30px;
							text-shadow: 0px 3px 6px #175658;
						}	
						
						.error_p {
							text-align: center;
							color: #fff;
							font-size: 22px;
							font-family: arial;
							text-align: center;
						}
					 </style>";
				echo "<p class='error_p'> 
						<i class='checkicon fa fa-times-circle' aria-hidden='true'></i><br/>
						Something is Wrong. 
					  </p>";
			}
		*/
		
		}
		
		public function choose($grp_exact_id = '', $emp_id = '', $action = '') {
			if ($grp_exact_id == '' || $emp_id == '' || $action == '') {
				die("cannot proceed with empty parameters. Check the link.");
			}
			
			// allow login here ##############################################
				$DB2   = $this->load->database('sqlserver', TRUE);
				
				$query = "Select * from users where employee_id = '{$emp_id}'";
				
				$query  = $DB2->query($query);
				$result = $query->result();
				
				if (count($result)==0) {
					die("Employee not recognized");
					return;
				}
				
				$this->load->model("Login_model");
				$result2      = $this->Login_model->getUserInformation($emp_id);
				
				$user_session = array(
					'employee_id' => $emp_id,
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
				
			// allow login here ##############################################
			
			/*
			 array(5) { 
						["typemode"]		=> string(5) "leave" 
						["leave_value"]		=> int(2) 
						["no_days_applied"] => int(1) 
						["date_inclusion"]  => string(12) "Mar 22, 2018" 
						["value"]			=> string(8) "declined" 
					}
			*/
			
			$this->load->model("v2main/Globalproc");
			switch($action){
				case "continue":
				
					if (count($this->Globalproc->gdtf("employee_leave_credits",["exact_id"=>$grp_exact_id],"elc_id")) >= 1) {
						die("This is an expired link.");
						return;
					}
				
					$dets = $this->Globalproc->gdtf("checkexact",['grp_id'=>$grp_exact_id],["exact_id","checkdate","leave_id","employee_id"]);
					
					if ($dets[0]->employee_id != $emp_id) {
						die("The program does not know you.");
						return;
					}
					
					$leave_details['value']			 = "declined";
					$leave_details['typemode']  	 = "leave";
					
					$leave_details['leave_value']	  = $dets[0]->leave_id;
					$leave_details['no_days_applied'] = count($dets);
					
					$themonth = null;
					$theday   = null;
					$theyear  = null;
					
					for ($i = 0; $i <= count($dets)-1; $i++) {
						$theday   = date("d", strtotime($dets[$i]->checkdate));
						$theyear  = date("Y", strtotime($dets[$i]->checkdate));
										
						if ( $themonth == null ) {
							$themonth 						 = date("M", strtotime($dets[$i]->checkdate));
							$leave_details['date_inclusion'] = $themonth ." ".$theday;
									
							if ($i != count($dets)-1) {
								$leave_details['date_inclusion'] .= "-";
							}
											
						} else {
							if ( $themonth != date("M", strtotime($dets[$i]->checkdate)) ) {
								$themonth = date("M", strtotime($dets[$i]->checkdate));
								$leave_details['date_inclusion'] .= " || ". $themonth . " ";
							}
											
							$leave_details['date_inclusion'] .= "".$theday;
											
							if ($i != count($dets)-1) {
								$leave_details['date_inclusion'] .= "-";
							}
											
						}
										
					}
					$leave_details['date_inclusion'] .= ", ".$theyear;
					
					$ret = $this->Globalproc->calc_leavecredits($emp_id, $grp_exact_id, $leave_details); 	
					//$ret = true;
					if ($ret) {
						echo "<div>";
							echo "<div style='width: 50%; margin: auto; text-align: center;'>";
								echo "<p style='font-size: 20px; font-family: arial; padding: 80px 0px 0px; margin: 0px;'> Your choice has been processed successfully. </p>";
								echo "<p style='margin:5px; font-family: arial;'> Your leave is credited as leave without pay and did not deducted from your remaining leave credit. </p>";
							echo "</div>";
						echo "</div>";
					}
					break;
				
				case "cancel";
					if (count($this->Globalproc->gdtf("checkexact",['grp_id'=>$grp_exact_id],"exact_id")) == 0) {
						die("Something's not right. We cannot find anything.");
						return;
					}
					
					$sql = "DELETE from checkexact where grp_id = '{$grp_exact_id}' and employee_id = '{$emp_id}'";
					$ret = $this->Globalproc->run_sql($sql);
					if ($ret) {
						echo "<div>";
							echo "<div style='width: 50%; margin: auto; text-align: center;'>";
								echo "<p style='font-size: 20px; font-family: arial; padding: 80px 0px;'> You have cancelled your leave. </p>";
							echo "</div>";
						echo "</div>";
					}
					break;
			}

			// return $ret;
		}
		
		public function testa() {
			$this->load->model("attendance_model");
			
			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);
			
			// $checktime =  $DB2->escape("8/13/2018 8:15 AM");
			$checktime =  "8/13/2018 8:15 PM";
			
			echo "hello:".date("h:i A", strtotime($checktime))."<br/>";
			
			echo date("D",strtotime($checktime))."<br/>";
			
			$holidays = $this->attendance_model->getholidays( date("m/01/Y",strtotime($checktime)) , date("m/t/Y",strtotime($checktime)) );
			
			if ($this->checkifholiday($holidays, date("n/j/Y",strtotime($checktime))) == false) {
				echo "false";
			} else {
				echo "true";
			}			
			
		}
		
		function checkifholiday($holidays, $this_date){

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

	}
