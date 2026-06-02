<?php

	class Leavemgt extends CI_Model {
		private $empid = null;

		public function __construct() {
			parent::__construct();
			$this->load->model("v2main/Globalproc");
		}	

		public function __setid($empid) {
			$this->empid = $empid;
		}

		//  earned leave methods
		//  enclosed in this code block are methods used only for earned_a_leave method
		//  # start
		public function earned_a_leave() {
		
		}

		private function compute_this_months_slearned() {
			return 1.250;
		}

		private function compute_this_months_vlearned() {
			return 1.250;
		}

		private function getprev_els() {
			$sql = "select * from earnedleaves where el_id = (select max(el_id) from earnedleaves where emp_id = '{$this->empid}')";

			return $this->Globalproc->__getdata($sql);
		}
		
		public function getemployees($isplantilla = false) {
			$emps = null;
			if ($isplantilla == false) {
				// get all 
				$sql = "select * from employees";
			} else {
				// get plantilla employees
				$sql  = "select * from employees where employment_type = 'REGULAR'";
				$emps = $this->Globalproc->__getdata($sql);
			}
			return $emps;
		}
		// #end :: earned_a_leave methods

		public function leaveapplications() {
			// there are two types of leave deductibles
				// vacation leave
					// personal pass slip
					// tardiness
					// undertime
					
				// sick leave
				// other leaves are not

			$sql = "select * from leaveapplications 
					JOIN la_activity 
						on leaveapplications.leaveid = la_activity.leaveapplication_id
					JOIN leaves 
						on leaveapplications.typeofleave_id = leaves.leave_id
					where leaveapplications.empid = '{$this->empid}'";

			$application = $this->Globalproc->__getdata($sql);

			var_dump($application);

		}

		public function __getearnedleaves() {
			// earned leaves
			
			$sql  = "select * from earnedleaves 
					LEFT JOIN creditdeductions on
					 earnedleaves.cd_id = creditdeductions.cd_id
					LEFT JOIN leaveapplications on
					 creditdeductions.leaveapplication_id = leaveapplications.leaveid
					where earnedleaves.emp_id ='{$this->employeeid}' order by el_id DESC";
			
			return $this->Globalproc->__getdata($sql);		
		}
		
		public function __get_approving_personnel($tree) {
			$hr 	  = $this->Globalproc->get_details_from_table("employees",['Division_id'=>10,"conn"=>"and","is_head"=>1],["f_name","e_signature"]);
			$chief 	  = $this->Globalproc->get_details_from_table("employees",['Division_id'=>$tree[1],"conn"=>"and","is_head"=>1],["f_name","e_signature"]);
			$director = $this->Globalproc->get_details_from_table("employees",['DBM_Pap_id'=>$tree[2],"conn"=>"and","position_id"=>17],["f_name","e_signature"]);
			
			$approving_personnel  = array(
									"hr" => array(
										"fullname"     => $hr['f_name'],
										"signature"    => $hr['e_signature'],
										"designation"  => "HRMD UNIT"
									),
									"division" => array(
										"fullname"     => $chief['f_name'],
										"signature"    => $chief['e_signature'],
										"designation"  => "Division Chief/OIC"
									),
									"director" => array(
										"fullname"     => $director['f_name'],
										"signature"    => $director['e_signature'],
										"designation"  => "Director"
									)
								);
										
			return $approving_personnel;
		}

	}

