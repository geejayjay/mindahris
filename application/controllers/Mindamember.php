<?php

	class Mindamember extends CI_Controller {

		public function checkemail() {
			$this->load->model("v2main/V2newmain");
			$this->load->model("v2main/Globalproc");

			$isexist 	  = false;
			$email        = $this->input->post("info");
		//	$email 		  = "alvinjay.merto@minda.gov.ph";

			// check if minda email
			$ismindaemail = (explode("@",$email)[1] == "minda.gov.ph")?true:false;
		
			// check if it exist in the database
			$isindb 	  = $this->Globalproc->__checkindb("users","Username",$email);
		
			if ($ismindaemail) {
				if ($isindb) {
					$isexist = "indb";
				} else {
					$isexist = $this->V2newmain->pingemail($email);	
				}
			} else {
				$isexist = false;
			}

			$isexist = ($isexist=="true")?md5($email):$isexist;

			echo json_encode( $isexist );

		}

		public function sendtempaccount() {

			$this->load->model("v2main/V2newmain");
			$this->load->model("v2main/Globalproc");

			$info 			 = $this->input->post("info");

			$email 			 = $info['email'];

			$username 		 = substr($email, 0,1).explode("@",explode(".",$email)[1])[0];

			$visiblepassword = substr(md5($email),0,6);
			$encryptedpword	 = md5($visiblepassword);

			// needed but unused
			$token = $info['token'];

			// first name
			$fname 			 = explode(".", $email)[0];

			// last name
			$lastname 		 = explode("@", explode(".", $email)[1])[0];

			// full name
			$fullname 		 = $lastname. ", ".$fname;

			// call function the will insert an employee
			$infodet = array(
				//"employee_id" 			=> "",
				"id_number" 			=> "0",
				"biometric_id" 			=> "0",
				"f_name" 				=> $fullname,
				"m_name" 				=> "0",
				"l_name" 				=> $lastname,
				"status" 				=> "0",
				"birthday" 				=> "0",
				"gender" 				=> "0",
				"employee_image" 		=> "0",
				"date_hired" 			=> "0",
				"date_added" 			=> "0",
				"date_modified" 		=> "0",
				"address_1" 			=> "0",
				"address_2" 			=> "0",
				"city" 					=> "0",
				"state" 				=> "0",
				"zip" 					=> "0",				
				"email_1" 			    => "0",
				"email_2" 			    => $email,
				"contact_1" 			=> "0",
				"contact_2" 			=> "0",				
				"area_id" 				=> "0",
				"department_id" 		=> "0",				
				"position_id" 			=> "0",
				"level_id" 				=> "0",				
				"employment_type" 		=> "JO",
				"employment_type_date" 	=> "0",
				"DBM_Pap_id" 			=> "0",
				"Division_id" 			=> "0",
				"Level_sub_pap_div" 	=> "0",
				"tin_number" 			=> "0",
				"sss_number" 			=> "0",
				"firstname" 			=> "0",
				"daily_rate" 			=> "0",
				"is_head" 			    => "0",
				"employee_index_tree" 	=> "0"
				);

			if ( $this->Globalproc->__save("dbo.employees",$infodet) ) {
				$emp_id = $this->Globalproc->getrecentsavedrecord("employees","recent_id");
				//var_dump($emp_id);
				if ($this->Globalproc->__save("dbo.users",array(
															"Username"    => $username,
															"Password"    => $encryptedpword,
															"usertype"    => "user",
															"employee_id" => $emp_id[0]->recent_id
															)) ){
					$details = array("uname"=>$username,"password"=>$visiblepassword);
					$flag    = $this->V2newmain->sendcredentials($details);
					echo json_encode($flag);
				}	
			}
			
		}	
	}
	
