<?php 
	
	class Approval extends CI_controller {
		function accomplishment($ot_exact = '', $approving_id = '', $ot_accom_id = '') {
			if ($ot_exact == '' || $approving_id == '' || $ot_accom_id == '') {
				die("error"); return;
			}
			
			// ==== allow auto login ========
				if($this->session->userdata("is_logged_in") != TRUE) {
				
					$this->load->model("Login_model");
					
					$DB2   = $this->load->database('sqlserver', TRUE);
					
					$query = "Select * from users where employee_id = '{$approving_id}'";
					
					$query  = $DB2->query($query);
					$result = $query->result();
					
					$result2      = $this->Login_model->getUserInformation($approving_id);
					
					$user_session = array(
							'employee_id' => $approving_id,
							'username' 	  => $result[0]->Username,
							'usertype'	  => $result[0]->usertype,
							
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
			// =========== end 
			
			$this->load->model("v2main/Globalproc");
			// check approval status 
			$accom_dets = $this->Globalproc->gdtf("ot_accom",["ot_accid"=>$ot_accom_id],"*");
			
			// get the applied OT date 
			$ot_dets 	= $this->Globalproc->gdtf("checkexact_ot",["checkexact_ot_id"=>$ot_exact],["ot_checkdate"]);
			
			// employee details 
			$emp_dets   = $this->Globalproc->gdtf("employees",["employee_id"=>$accom_dets[0]->emp_id],["f_name","email_2"]);
			
			// end 
			$ret 		   = false;
			$proceed_email = false;
			
			$approval_status = 0;
			$details 		 = [
				"to"		=> "",
				"from"		=> "",
				"subject"	=> "",
				"message"	=> ""
			];
			
			
			if ($accom_dets[0]->approval_status == 0) { // fresh from staff
				$approval_status 		= 1;
				
				$details["to"]			= $this->Globalproc->gdtf("employees",["employee_id"=>$accom_dets[0]->lapproval],["email_2"])[0]->email_2;
				$details["from"]		= strtoupper($emp_dets[0]->f_name);
				$details["subject"]		= "Overtime Accomplishment Report: For Approval";
				
				$this->load->model("v2main/Emailtemplate");
				$email_dets = [
					"Type"	    			 => "Overtime Accomplishment Report",
					"Name"	    			 => $details["from"],
					"Date"	    			 => date("m/d/Y"),
					"Work Accomplishment(s)" => urldecode($accom_dets[0]->accomplishment)
				]; 
				// $accom_dets[0]->am_timein." | ".$am_timeout
				if ($accom_dets[0]->am_timein != null || $accom_dets[0]->am_timeout != null) {
					$email_dets["Morning"]	 = $accom_dets[0]->am_timein." | ".$accom_dets[0]->am_timeout;
				}
				
				if ($accom_dets[0]->pm_timein != null || $accom_dets[0]->pm_timeout != null) {
					$email_dets["Afternoon"] = $accom_dets[0]->pm_timein." | ".$accom_dets[0]->pm_timeout;
				}
				
				$email_action = [
					"ot_exact"		=> $ot_exact,
					"approving_id"	=> $accom_dets[0]->lapproval,
					"ot_accom_id"	=> $ot_accom_id
				];
			
				$details['message'] = $this->Emailtemplate->ot_accom_template($email_dets, $email_action);
				
				$proceed_email = 1;
				
			} else if ($accom_dets[0]->approval_status == 1) { // fresh from chief or dbm or approved by chief 
				$approval_status = 2;
				$proceed_email   = 2;
				
				$s_dets = [
					"to"		=> $emp_dets[0]->email_2,
					"from"		=> strtoupper( $this->Globalproc->gdtf("employees",['employee_id'=>$accom_dets[0]->lapproval],["f_name"])[0]->f_name ),
					"subject"	=> "Overtime Accomplishment Report: Approved",
					"message"	=> "<p> Your Overtime Accomplishment Report has been approved. </p>"
				];
				
			} else if ($accom_dets[0]->approval_status == 2) { // approved by dbm... APPROVED 
				die("This form is already approved."); return;
			}
			
			$values = [
				"approval_status"	=> $approval_status
			];
			
			$where = [
				"ot_accid" 	=> $ot_accom_id
			];
			
			$ret = $this->Globalproc->__update("ot_accom",$values,$where);
			
			if ($ret) {
				if ($proceed_email == 1) {
					if ( $this->Globalproc->sendtoemail($details) ) {
						$ret = true;
					}
				} else if ($proceed_email == 2) {
					// send to leave_ot_credits.
					$checkdate = $ot_dets[0]->ot_checkdate;
					
					$values = [
						/*
						"am_in" 			  => null,
						"am_out"			  => null,
						"pm_in"				  => null,
						"pm_out"			  => null,
						*/
						"dates" 			  => [$checkdate],
						"empid"			      => $accom_dets[0]->emp_id,
						"typemode"			  => "OT",
						"exact_ot"			  => $ot_exact
					];
					
					$mult_dets = $this->Globalproc->gdtf("holidays",["holiday_date"=>date("m/d/Y",strtotime($checkdate))],["holiday_id"]);
					
					if (count($mult_dets) >= 1) {
						$values['mult'] = 1.5;
					} else {
						if (date("l",strtotime($checkdate)) == "Sunday" || date("l",strtotime($checkdate)) == "Saturday") {
							$values['mult'] = 1.5;
						} else {
							$values['mult'] = 1;
						}
					}
					
					if ( $accom_dets[0]->am_timein != null && $accom_dets[0]->am_timeout != null ) {
						$values['isam']		= true;
						$values['am_in']	= $accom_dets[0]->am_timein;
						$values['am_out']	= $accom_dets[0]->am_timeout;
					}
					
					if ( $accom_dets[0]->pm_timein != null && $accom_dets[0]->pm_timeout != null ) {
						$values['ispm']		= true;
						$values['pm_in']	= $accom_dets[0]->pm_timein;
						$values['pm_out']	= $accom_dets[0]->pm_timeout;
					}
					
					// isam :: toggle true if OT starts from AM
					// ispm :: toggle true if OT ends in PM
					
					$r = $this->Globalproc->saveto_ot($values);
					
					if( $this->Globalproc->sendtoemail($s_dets) ) {
						$ret = true;
					}
				}
			}
			
			echo json_encode($ret);
		}
		
		function test() {
			echo date("l",strtotime("1/2/2017"));
		}
		
	}
