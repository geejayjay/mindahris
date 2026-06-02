<?php 
		
	class Actiononleave extends CI_Model {
		protected $leavetype; // tentative
		protected $variable;
		protected $days;
		protected $hrs;
		protected $mins;

		protected $la_id = null; 
		protected $typeofleaves = ["1"=>"Sick Leave", "2" => "Vacation Leave"];

		// holder of data
		protected $leaves;
		// ------

		public function __construct() {
			$this->load->model("v2main/Globalproc");
		}

		public function __setdata($la_id) {
			$this->la_id 		= $la_id;
		}

		public function __gethrisrank() {
			$this->load->model("Globalvars");
			$empid = $this->Globalvars->employeeid;

			$sql = "select hrisrank from employees where employee_id = '{$empid}'";
			return $this->Globalproc->__getdata($sql);
		}

		public function __fetchdata() {
			$sql = "select * from leaveapplications where leaveid = '{$this->la_id}'";

			$this->leaves = $this->Globalproc->__getdata($sql);

			//var_dump($this->leaves);
			// setting of data
			$this->leavetype = "";
			$this->variable  = "";
			$this->days 	 = "";
			$this->hrs 		 = "";
			$this->mins 	 = "";

			return $this->leaves;
		}

		public function getapplications($org_tree, $emplvl) {

		if (!isset($org_tree) && !isset($emplvl)) { 
			// die("Division and Employee rank is not defined.");
			redirect(base_url()."accounts/login","refresh");
		}

		/*
			$sql  = "select * from la_activity as laa 
					JOIN leaveapplications as la on 
					laa.leaveapplication_id = la.leaveid
					JOIN employees as e on 
					la.empid = e.employee_id
					JOIN leaves as l on
					la.typeofleave_id = l.leave_id
					where laa.leavestatus = '0' and la.status = '0'
					and e.org_tree = '{$org_tree}' and e.hrisrank = '{$emplvl}'
					";
		*/

			$sql  = "select * from la_activity as laa 
					JOIN leaveapplications as la on 
					laa.leaveapplication_id = la.leaveid
					JOIN employees as e on 
					la.empid = e.employee_id
					JOIN leaves as l on
					la.typeofleave_id = l.leave_id
					where la.status = '0' ";

			if ($emplvl == 1 || $emplvl == "1") { 		// division chief
				$sql .= "and laa.leavestatus = '0' and e.DBM_Pap_id = '{$org_tree}' ";
			} else if ($emplvl=='2' || $emplvl==2) { 	// director 
				$sql .= "and laa.leavestatus = '1' and e.DBM_Pap_id = '{$org_tree}' ";
			} else if ($emplvl>'2' || $emplvl>2) {
				// chief of staff
				// get all applications coming from the directors
				// $sql .= "laa.leavestatus = '1' ";
			}
			//var_dump($this->Globalproc->__getdata($sql));
			return $this->Globalproc->__getdata($sql);

		}

		public function getleavedetails($leaveid){
			$sql = "select * from la_activity as la 
					JOIN leaveapplications as laa
						on la.leaveapplication_id = laa.leaveid
					JOIN employees as e 
						on laa.empid = e.employee_id
					JOIN Division as d 
						on e.Division_id = d.Division_id
					JOIN leaves as l 
						on laa.typeofleave_id = l.leave_id
					where laa.leaveid = '{$leaveid}'";
			
			$det = $this->Globalproc->__getdata($sql);
			
			return array(
					"leaveid"   	  => $det[0]->leaveapplication_id,
					"leavetype" 	  => $det[0]->leave_id,
					"leave_name"	  => $det[0]->leave_name,
					"leave_code"	  => $det[0]->leave_code,
					"leavespecific"	  => $det[0]->leavespecific,
					"signature"		  => $det[0]->e_signature,
					"position"	      => $det[0]->position,
					"fullname"		  => $det[0]->f_name,
					"empid"		      => $det[0]->employee_id,
					"monthlysalary"   => $det[0]->daily_rate,
					"division" 		  => $det[0]->Division_Desc,
					"dateoffiling"	  => $det[0]->dateoffiling,
					"noofworkingdays" => $det[0]->numofworkingdaysapplied,
					"inclusivedate"   => $det[0]->inclusivedate,
					"commutation"	  => $det[0]->commutation
				);
			
		}

		public function __approve($leaveid, $status) {
			// include deduction script
			$this->load->model("v2main/Creditdeductions");

			$values = ['leavestatus'=>$status];
			$where  = ["leaveapplication_id" => $leaveid];

			if ( $this->Globalproc->__update("la_activity",$values,$where) ) {
				// if the for-approval-document is equal to T, U, HD
					// then the first parameter of computeddeductibles 
					// method is its leavetype (T, U, HD)
				if ( $this->Creditdeductions->computedeductibles("leave", $leaveid, $status) ) {
					return true;
				} 
			}

			return false;
		}

		public function __disapprove($leaveid, $message, $status) {
			$values = ["leavestatus" => $status];
			$where  = ["leaveapplication_id" => $leaveid];

			return $this->Globalproc->__update("la_activity", $values, $where);
		}

		public function save_to_leave_activity_table($vals) {

			$rank = ["hrisrank"];
			$retdet  = $this->Globalproc->get_empdetails($rank);

			$la_activity = [
				// 2 means this is level 2 approved
				"leavestatus"			=> $retdet['hrisrank'], 
				"status"				=> "0",
				"leaveapplication_id"	=> $vals['leaveid'] 
			];

			$ret = $this->Globalproc->__save("leaveapplications",$vals);
			if ($ret) {
				$ret = $this->Globalproc->__save("la_activity", $la_activity);
				if ($ret) {
					return "<p class='success'>Success</p>";	
				}
			}
			return "<p class='failure'>Failed...something is technically wrong...</p>";

		}

	}

