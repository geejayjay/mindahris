<?php

	class Creditdeductions extends CI_Model {
		private $empid = null;

		public function __construct() {
			parent::__construct();
			$this->load->model("v2main/Globalproc");
		}

		public function __setemp($empid) {
			$this->empid = $empid;
		}

		public function get_xvar($empid, $cd_type, $xvar_type) {

			$bm   	    = null; // benchmark
			$left_var   = null;
			$right_var  = null;

			switch($xvar_type) {
				case "T":
				case "U":
					// per month
					$bm 	   = 1;
					$right_var = date("F");
					break;				
				default:
					// per year
					$bm 	   = 2;
					$right_var = date("Y");
			}

			$sql = "select * from leaveapplications as l
					JOIN la_activity as la on 
						l.leaveid = la.leaveapplication_id
					where l.empid = '{$empid}' 
					and l.typeofleave_id = '{$cd_type}'
					and la.leavestatus > '1'";

			$xvar = $this->Globalproc->__getdata($sql);

			$count = 0;

			for ($i=0;$i<=count($xvar)-1;$i++) {
				if ($bm == 1) {
					$left_var = trim( explode(" ", explode(",",$xvar[$i]->dateoffiling)[$bm])[0] ," ");	
				} else if($bm == 2) {
					$left_var = trim(explode(",",$xvar[$i]->dateoffiling)[$bm]," ");	
				}
				
				if ($left_var == $right_var) {
					$count++;
				}
			}

			return $count;
		}

		public function computedeductibles($kindofdeductible,$__id,$status) {
			// this method is only for earned leave table
			// CTO is not included in the earned leave table

			// 1st :: compute what kind of deductible
			// 2nd :: compute where to deduct it and how much
			// 3rd :: reflect to earnedleaves table

			// for deductibles that are credited to vacation leave.
				// T  = tardiness
				// VL = vacation leave
			switch($kindofdeductible) {
				case "leave":
					$leaveid    = $__id;

					$sql 	    = "select * from leaveapplications where leaveid = '{$leaveid}'";
					$leave      = $this->Globalproc->__getdata($sql);

					$cd_id      = $this->Globalproc->__createuniqueid($leaveid);
					$emp_id 	  = $leave[0]->empid;

					$deductions = [
								"cd_id" 			  => $cd_id,
								"typeofcd" 			  => null,
								"xvar" 				  => null,
								"days" 				  => 0,
								"hrs" 				  => 0,
								"mins" 				  => 0,
								"status" 			  => "0",
								"leaveapplication_id" => "none"
								];

					$sql_els    = "select * from earnedleaves where emp_id = '{$emp_id}' 
							 	   and status='0' and el_id = (select max(el_id) from earnedleaves where 
								   emp_id = '{$emp_id}')";

					$els          = $this->Globalproc->__getdata($sql_els);

					$typeofleave  = $this->Globalproc->get_details_from_table("leaves",["leave_id"=>$leave[0]->typeofleave_id],['leave_code'])['leave_code'];
					//$typeofleave  = $typeofleave['leave_code'];

					$earnedleave_date = $this->Globalproc->__datetoday();
					$inclusivedate    = $leave[0]->inclusivedate;

					$earnedleaves = [
							"typeofleave"		=> $typeofleave,
							"vl_earned"			=> 0,
							"vl_abs_ut_wpay"	=> 0,
							"vl_balance"		=> $els[0]->vl_balance,
							"vl_abs_ut_wopay"	=> 0,
							"emp_id"			=> $emp_id,
							"date"				=> $inclusivedate,
							"cd_id"				=> $cd_id,
							"status"			=> "0",
							"sl_earned"			=> 0,
							"sl_abs_ut_wpay"	=> 0,
							"sl_balance"		=> $els[0]->sl_balance,
							"sl_abs_ut_wopay"	=> 0
							];

					
							// ===============================================
							// 4 hours is half day
								// if filed for CTO
									// deductions should be from CTO table
								// if not filed for CTO
									// deductions should be from VL table
							// ===============================================

							// ===============================================
								// tardiness || lates
									// if filed for CTO
										// deduct from CTO table
									// if not
										// deduct from VL table
							// ===============================================

							// ===============================================
							// <4 hours is undertime, deductible automatically from VL 
							// ===============================================

							$deductions['typeofcd'] = $typeofleave;
							$deductions["days"]	 	= $leave[0]->numofworkingdaysapplied;							
							$deductions['leaveapplication_id'] = $leaveid;

							switch($typeofleave) {
								case "PASSSLIP":	
									// unit is hours and mins
									break;	
								case "U":

									break;
								case "T":

									break;
								case "HD":

									break;
								case "VL":
									// earned leave
									$earnedleaves['vl_balance'] = $earnedleaves['vl_balance'] - $deductions['days'];

									// deductions
									$deductions['xvar'] 	= $this->get_xvar($emp_id,$leave[0]->typeofleave_id,"any");
									break;			
								case "SL":
									// earned leave
									$earnedleaves['sl_balance'] = $earnedleaves['sl_balance'] - $deductions['days'];

									// deductions 
									$deductions['xvar'] 	= $this->get_xvar($emp_id,$leave[0]->typeofleave_id,"any");
									break;
								case "FL":

									break;
								default:
								// to deductions
									$deductions['cd_id'] 	= $cd_id;
									$deductions['typeofcd'] = $typeofleave;
									$deductions['xvar'] 	= $this->get_xvar($emp_id,$leave[0]->typeofleave_id,"any");
									$deductions['status'] 	= "0";
							}

					// insert and return
							/*
					if ($pass) {
						if ($this->Globalproc->__save("earnedleaves",$earnedleaves))	{
							return true;
						}
					}
							*/
					
					if ($this->Globalproc->__save("Creditdeductions",$deductions)) {
						if ($this->Globalproc->__save("earnedleaves",$earnedleaves))	{
							return true;
						}
					}
					
					return false;
				break;
				case "earned":

				break;
				}
		}

		public function __tocreditdeductions($val) {
		//	var_dump($val);

		$this->load->model("v2main/Globalproc");

		// default values
			$var_defaults = [
					"cd_id" 			  => null,
					"typeofcd" 			  => null,
					"xvar" 				  => null,
					"days" 				  => 0,
					"hrs" 				  => 0,
					"mins" 				  => 0,
					"status" 			  => 0,
					"leaveapplication_id" => null
				];
		// end

		$newvalues = $this->Globalproc->merge_new_values($var_defaults, $val);
		return $this->Globalproc->__save("Creditdeductions",$newvalues);
		
			//$ret = $this->Globalproc->__save("Creditdeductions",$val);	
		/*
			$typeofcd = [1=>"Sick Leave",2=>"Vacation Leave"];
			if ($ret) {
				$this->load->model("v2main/Leavemgt");

				// typeofcd

				$d['cd_id']		= $val['cd_id'];
				$d['status']	= 0;
				$d['datecover']	= $this->Globalproc->monthcover();

					// vacation leave
				$d['vl_abs_ut_wpay']  = 0;
				$d['vl_abs_ut_wopay'] = 0;
				$d["vl_earned"]		  = $thismonth;
				$d["vl_balance"]	  = $vlbalance;

					// sick leave
				$d['sl_abs_ut_wpay']  = 0;
				$d['sl_abs_ut_wopay'] = 0;
				$d["sl_earned"]		  = $sl_this_month;
				$d["sl_balance"]	  = $slbalance;

			  	$ret = $this->Leavemgt->earned_a_leave($d);
			}
		*/

		}

	}

