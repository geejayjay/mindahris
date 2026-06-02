<?php 
	
	class Leaveprocs extends CI_Model {
		public function __construct() {
			parent::__construct();
			
		}

		public function spl($empid = false) {
			$this->load->model("Globalproc");

			// employee id
			$emp_id = null;

			if ($empid == false) {
				$this->load->model("Globalvars");
				$emp_id = $this->Globalvars->employeeid;	
			} else {
				$emp_id = $empid;
			}
						
			$sql 	= "select * from leaveapplications 
						join la_activity on 
							leaveapplications.leaveid = la_activity.leaveapplication_id
					   where leaveapplications.empid='{$emp_id}'
					   and leaveapplications.typeofleave_id = '4'
					   and la_activity.leavestatus = '2'";
			
			$data   		= $this->Globalproc->__getdata($sql);

			$inclusivedates = [];
			$thisyear 		= date("Y");

			$numberofapplication = 0;
			
			for ($i=0;$i<=count($data)-1;$i++) {

				$date = $data[$i]->inclusivedate;

				$a = null;
				$dd = explode("|", $date);

				$to   = null;
				$from = null;

				$yearfrmdb = null;

				$count = 0;
				foreach( $dd as $d ) {
					$a = explode(" ",$d);
			// the pattern of the inclusive date is march 1, 2017 | march 2, 2017
			// get the last index in this pattern march 1, 2017 separated by space
						if ($count % 2 == 0) {
							// date from
							$to = $a[2];
							
						} else {
							// date to
							$from = $a[3];

						}
					
					$count++;
				}

				if ($to != $from ) {
					// return false;
				} else {
					$yearfrmdb = $to;
					$yearfrmdb = $from;
				}	

				if ($yearfrmdb == $thisyear) { //  && $data[$i]->leavestatus == "1"
					$numberofapplication++;
				}
				
			}

			
			return $numberofapplication;

		}

		public function get_approval_status($leaveid) {
			$this->load->model("Globalproc");
			$this->load->model("Globalvars");

			$status = [
				"emplvl"	=> ["rank" 	=> null],
				"approval" 	=> ["level" => null],
				"division"	=> [
								"chief_name"	=> null,
								"chief_sign"	=> null,
								"status"		=> null,
								"remarks"		=> null
							   ],
				"director"	=> [
								"director_name" => null,
								"director_sign" => null,
								"status"		=> null,
								"remarks"		=> null
							   ]
			];

		# get details of the leave
			$sql_get_leave = "select * from la_activity as a
							  JOIN leaveapplications as b
							    on a.leaveapplication_id = b.leaveid
							  JOIN employees as e 
							  	on b.empid = e.employee_id
							  where a.leaveapplication_id = '{$leaveid}'
							 ";

			$leave 	  = $this->Globalproc->__getdata($sql_get_leave);

			$empid       = $leave[0]->empid;
			$division    = $leave[0]->Division_id;
			$pap_id      = $leave[0]->DBM_Pap_id;
			$leavestatus = $leave[0]->leavestatus;
			$remarks 	 = $leave[0]->reason;

			// set the approving body's emp type
			$tbl 	= "employees";
			$w 		= ['employee_id' => $this->Globalvars->employeeid];
			$det 	= ['hrisrank'];
			$ab     = $this->Globalproc->get_details_from_table($tbl, $w, $det);
			$status['emplvl']['rank'] = $ab['hrisrank'];

			// get the approving body
			// division
			$sql_div = "select f_name, e_signature from employees 
					    where hrisrank = '1' and Division_id = '{$division}'";

			$sql_dir = "select f_name, e_signature from employees
						where DBM_Pap_id='{$pap_id}' and hrisrank='2'";

			$_div  = $this->Globalproc->__getdata($sql_div);
			$_dir  = $this->Globalproc->__getdata($sql_dir);

			switch($leavestatus) {
				case "0": // freshly applied
					$status['approval']['level']	  = "new";
					break;
				case "1": // approved on division level
					$status["division"]['status'] 	  = "approved";
					$status["division"]['chief_name'] = $_div[0]->f_name;
					$status["division"]['chief_sign'] = $_div[0]->e_signature;
					$status['approval']['level'] 	  = "division";
					break;
				case "1d": // disapproved on division level
					$status["division"]['status'] 	  = "disapproved";
					$status["division"]['chief_name'] = $_div[0]->f_name;
					$status["division"]['chief_sign'] = $_div[0]->e_signature;
					$status["division"]['remarks']    = $remarks;
					$status['approval']['level'] 	  = "division";
					break;
				// director level
				case "2": // approved director level
					$status["division"]['status'] 	  = "Approved";
					$status["division"]['chief_name'] = $_dir[0]->f_name;
					$status["division"]['chief_sign'] = $_dir[0]->e_signature;
					$status['approval']['level'] 	  = "director";
					break;
				case "2d":	// disapproved director level
					$status["division"]['status'] 	  = "disapproved";
					$status["division"]['chief_name'] = $_dir[0]->f_name;
					$status["division"]['chief_sign'] = $_dir[0]->e_signature;
					$status["division"]['remarks']    = $remarks;
					$status['approval']['level'] 	  = "director";
					break;
			}
			return $status;
		}
	}

