<?php 
	class Formfetcher extends CI_Model {
		public $return = null;
		public function __construct() {
			$this->load->model("v2main/Globalproc");

			if (isset($_POST['sickleavebtn'])) { // listen to sick leave form btn
				$this->return = $this->__fsickleave();
			} else if (isset($_POST['genericleave'])) {
				$this->return = $this->__genericleave();
			} else if (isset($_POST['splbtn'])) {
				$this->return = $this->__fetch_spl();
			} else if (isset($_POST['disapprove'])) { // listen to disapproval btn
				$leaveid = $_POST['lid'];
				redirect(base_url()."action/application/disapprove/{$leaveid}",'refresh');
			}
		}

		public function __fetch_spl() {
			$this->load->model("v2main/Leaveprocs");
			$this->load->model("v2main/Actiononleave");

			$spl_restrict = $this->Leaveprocs->spl();
			
			if ($spl_restrict >= 3) {
				return "<p class='warning'> You are only allowed three SPL per year. </p>";
			} 

			$inputs = [	
				"spl_details",
				"numofdaysapplied",
				"inclusivedates"
				];

			$values = [];

			foreach($inputs as $ins){
				$values[$ins] = $this->input->post($ins);
			}

			$la_id = $this->Globalproc->__createuniqueid($this->Globalvars->employeeid);
			
			$ls['spl_type'] = $values['spl_details'][0];
			// leave applications
			$vals = [
				"leaveid"					=> $la_id,
				"typeofleave_id" 			=> 4,
				"leavespecific"	 			=> json_encode($ls),
				"empid" 		 			=> $this->Globalvars->employeeid,
				"dateoffiling"	 			=> date("l, F d, Y"),
				"numofworkingdaysapplied"	=> $values['numofdaysapplied'],
				"inclusivedate"				=> $values['inclusivedates'],
				"commutation"				=> null,
				"status"					=> "0" // 1 for cancelled by user
			];

			return $this->Actiononleave->save_to_leave_activity_table($vals);

		}

		public function __genericleave() { // pink leave form
		$this->load->model("v2main/Actiononleave");

		$inputs = [
				"leavetype",
				"lspecs",
				"lspecs_det",
				"dateoffiling",
				"noofworkingdays",
				"inclusivedate",
				"commutation"
			];

			$values = [];

			for ($i=0;$i<=count($inputs)-1;$i++) {
				$values[$inputs[$i]] = $this->input->post( $inputs[$i] );
			}

			$typeofleave = null;
			$ls 		 = null;
			
			if ($values['leavetype'] == "vl") {
				$typeofleave 	= 2; // vacation leave
				$ls["lspec"]	= $values['lspecs'][0];
				$ls['specific'] = ($ls['lspec']=="w_inphil")?"Within Philippines":$values['lspecs_det'];
			} else {
				// get value from leaves table;

				$typeofleave    = $this->Globalproc->get_leave_dbid($values['leavetype']);
				$ls['specific'] = $values['lspecs_det'];
			}

			$la_id = $this->Globalproc->__createuniqueid($this->Globalvars->employeeid);

			// leave applications
			$vals = [
				"leaveid"					=> $la_id,
				"typeofleave_id" 			=> $typeofleave,
				"leavespecific"	 			=> json_encode($ls),
				"empid" 		 			=> $this->Globalvars->employeeid,
				"dateoffiling"	 			=> $values['dateoffiling'],
				"numofworkingdaysapplied"	=> $values['noofworkingdays'],
				"inclusivedate"				=> $values['inclusivedate'],
				"commutation"				=> $values['commutation'][0],
				"status"					=> "0" // 1 for cancelled by user
			];

			return $this->Actiononleave->save_to_leave_activity_table($vals);

		}

		function __fsickleave() {
			$this->load->model("v2main/Actiononleave");
			$a = ["ip"=>"In Patient", "op"=>"Out Patient"];

			$inputs =  [
				"patienttype",
				"specifics",
				"fullname",
				"drate",
				"off_div",
				"datefiling",
				"nowda",
				"incdates",
				"commutation"
			];

			$values = [];

			for ($i=0;$i<=count($inputs)-1;$i++) {
				$values[$inputs[$i]] = $this->input->post( $inputs[$i] );
			}

			$la_id = $this->Globalproc->__createuniqueid($this->Globalvars->employeeid);

			$ls = array("sl_type"=>$values['patienttype'][0],
						"specific"=> $values["specifics"]
						);

			// leave applications
			$vals = [
				"leaveid"					=> $la_id,
				"typeofleave_id" 			=> 1,
				"leavespecific"	 			=> json_encode($ls),
				"empid" 		 			=> $this->Globalvars->employeeid,
				"dateoffiling"	 			=> $values['datefiling'],
				"numofworkingdaysapplied"	=> $values['nowda'],
				"inclusivedate"				=> $values['incdates'],
				"commutation"				=> $values['commutation'][0],
				"status"					=> "0" // 1 for cancelled by user
			];

			$la_activity = [
				"leavestatus"			=> "0", // 2 means this is level 2 approved
				"status"				=> "0",
				"leaveapplication_id"	=> $la_id 
			];

			return $this->Actiononleave->save_to_leave_activity_table($vals);

		}
	}
