<?php 
	
	class Ams_model extends CI_Model {
		public $empid     = null;
		public $datetime  = null;
		public $checktype = null;
		public $remarks   = null;
		public $is_ams    = null;
		
		function save_checkexact($amsid) {
			$this->load->model("v2main/Globalproc");
			
			$checkexact["employee_id"]		= $this->empid;
			$checkexact["type_mode"]		= "AMS";
			$checkexact["modify_by_id"]		= $this->empid;
			$checkexact["checkdate"]		= date("m/d/Y",strtotime($this->datetime));
			$checkexact["date_added"]		= date("m/d/Y h:iA");
			$checkexact["is_approved"]		= 1;
			$checkexact["aprroved_by_id"]	= 0;
			$checkexact["date_approved"]	= 0;
			$checkexact["ps_type"]			= 1;
			$checkexact["leave_id"]			= 0;
			$checkexact["ps_guard_id"]		= 0;
			$checkexact["grp_id"]			= $amsid;
			
			$exact_id = null;
			
			// $check_thece 	= $this->Globalproc->gdtf("checkexact",["checkdate"=>$checkexact['checkdate']],['exact_id']); 
			$check_thece 	= $this->Globalproc->gdtf("checkexact",['grp_id' => $amsid],['exact_id']); 
			
			if (count($check_thece)==0) {
				$savecheckexact = $this->Globalproc->__save("checkexact",$checkexact);
				if ($checkexact) {
					$exact_id		= $this->Globalproc->getrecentsavedrecord("checkexact","exact_id")[0]->exact_id;
				}
			} else {
				$exact_id = $check_thece[0]->exact_id;
			}
			
			return $exact_id;
		}
		
		function tocheck_logs($checkexact) {
			$this->load->model("v2main/Globalproc");
			
			$c_logs["exact_id"]			= $checkexact;
			$c_logs["checktime"]		= $this->datetime;
			$c_logs["checktype"]		= $this->checktype; // should be set accordingly .. AMS, checkexact_logs etc.
			$c_logs["shift_type"]		= date("A", strtotime($this->datetime));
			$c_logs["modify_by_id"]		= 0;
			$c_logs["is_modify"]		= 0;
			$c_logs["is_delete"]		= 0;
			$c_logs["date_added"]		= date("m/d/Y h:iA");
			$c_logs["date_modify"]		= 0;
			$c_logs["is_bypass"]		= 0;
			
		//	$check_cel 	= $this->Globalproc->gdtf("checkexact_logs",["exact_id"=>$checkexact],['']);
			
			return $this->Globalproc->__save("checkexact_logs",$c_logs);
		}
		
		function save_check_ams() {
			$this->load->model("v2main/Globalproc");
			
			$contime 		= date("m/d/Y h:i:0 A", strtotime($this->datetime));
			$checkexact_ams = [];
			
			$checkexact_ams['employee_id'] 		= $this->empid;
			$checkexact_ams['checkdate']   		= date("m/d/Y", strtotime($this->datetime));
			$checkexact_ams[$this->checktype] 	= $contime;//$this->checktype;
			$checkexact_ams['remarks'] 	   		= $this->remarks;
			
			$check_ckams = $this->Globalproc->gdtf("checkexact_ams",
													["checkdate"=>$checkexact_ams['checkdate'],
													"conn"=>"and",
													"employee_id"=>$checkexact_ams['employee_id']],["c_ams_id"]);
			/// $status = $this->Globalproc->__save("checkexact_ams",$new);
			
			$status  = null;
			$amsid   = null;
			
			if (count($check_ckams) == 0) {
				$status = $this->Globalproc->__save("checkexact_ams",$checkexact_ams);
				
				if ($status == true) {
					$amsid	= $this->Globalproc->getrecentsavedrecord("checkexact_ams",'c_ams_id')[0]->c_ams_id;
				}
			} else {
				$amsid    = $check_ckams[0]->c_ams_id;
				$updates_ = $this->Globalproc->__update("checkexact_ams",[$this->checktype=>$contime],["c_ams_id"=>$amsid]);
			}
			
			return $amsid;
		}
		
		function save_amspix($pic,$amsid) {
			$this->load->model("v2main/Globalproc");
			$amspix_save = ["cea_id"	  => $amsid,
							"pm_out_snap" => $pic,
							"empid"		  => $this->empid];
			
			return $this->Globalproc->__save("amspix",$amspix_save);
		}
		
		// for pass slip checking ======================
		
		function checkif_has_ps($date,$empid) {
			$this->load->model("v2main/Globalproc");
			
			$checkdate = date("n/j/Y",strtotime($date));
			
			$sql 	   = "select tb1.* from (select * from checkexact as ce 
								where employee_id = '{$empid}' 
							and checkdate like '%{$checkdate}%' 
							and is_approved = 1 
							and type_mode = 'PS') as tb1 
								where tb1.time_out IS NULL 
									OR tb1.time_in IS NULL";
		
			/*
			
			and time_out IS NULL 
			and time_in IS NULL
			*/
			
			$ps 	   = $this->Globalproc->__getdata($sql);
			
			if (count($ps) == 0) { // nothing found
				return false;
			} else {
				return [$ps[0]->exact_id,$ps[0]->time_out, $ps[0]->time_in];
			}
		}
		
				
		// end for pass slip checking ==================
	}
