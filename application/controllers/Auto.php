<?php 
	
	class Auto extends CI_Controller {
		private $returned = 0;
		private $emps 	  = [];
		
		public function __construct(){
			parent::__construct();
			$this->load->model("v2main/Globalproc");
			
			// populate the emps
			$plantilla = $this->Globalproc->getplantilla();
				foreach($plantilla as $p) {
					array_push($this->emps,$p->employee_id);
				}
			
		}
		
		public function startaward() {
			if ($this->session->userdata('is_logged_in') != TRUE) {
				$user_session = array(
					'employee_id' 			=> 389,
					'username' 				=> "amerto",
					'usertype' 				=> "admin",
					'full_name' 			=> "Alvin Merto",
					'first_name' 			=> "Alvin",
					'last_name' 			=> "Merto",
					'biometric_id'  		=> "2sw248g69w598w65w5",
					'area_id' 				=> 1,
					'area_name' 			=> "Davao",
					'ip_address' 			=> $_SERVER["REMOTE_ADDR"],
					'is_logged_in' 			=> TRUE,
					'database_default'  	=> 'sqlserver',
					'employment_type' 		=> "JO",
					'employee_image' 		=> NULL,
					'level_sub_pap_div' 	=> 'Division',
					'division_id' 			=> 5,
					'dbm_sub_pap_id' 		=> 1,
					'is_head' 				=> 0,
					'office_division_name'  => "Knowledge Management Division",
					'position_name' 		=> "Software Programmer"
				);
				
				$this->session->set_userdata($user_session);
			}
			
			if ($this->returned <= count($this->emps)-1) {
				$ret = $this->Globalproc->awardcredit($this->emps[$this->returned]);
			
				if ($ret) {
					echo "<p> {$this->returned}. Awarded to ".$this->emps[$this->returned]."</p>";
					$this->returned += 1;
					$this->startaward();
				} else {
					echo "<p> not added ".$this->emps[$this->returned]." </p>";
					$this->returned += 1;
					$this->startaward();
				}
			} else {
				echo "---------end--------";
			}
		}

	}

