<?php 
	
	class Leave extends CI_Controller {
		public $employeeid;
		public $usertype;
		public $emptype;
		
		public function __construct() {
			parent::__construct();
			//$this->employeeid = $this->session->userdata('employee_id');
			$this->usertype   = $this->session->userdata('usertype');
			$this->emptype	  = $this->session->userdata('employment_type');
			
		}
		
		public function send_loginaccount() {
			$ret = false;
			$this->load->model("v2main/Globalproc");
			
			$emp      = $this->input->post("info");
			$emp      = $emp['dets'];
			
			$empid    = $emp['empid'];
			$username = $emp['uname'];
			$email    = $emp['email'];
			$password = $emp['password'];
			
			//$sql  = "select email_2 from employees where employee_id = '{$empid}'";
			//$data = $this->__getdata($sql);
			$baseurl  = base_url();
			if ($email != null || strlen($email) != 0) {			
				$details["to"]   	= $email;
				$details['from'] 	= "HRIS Account";
				$details['subject'] = "Your temporary HRIS Login Account.";
				
				$details['message'] = "<html>
										<body style='font-family:arial;'>
											<div style='width: 73%; border: 1px solid #ccc; margin: auto; padding: 17px;'>
											<div style='text-align: center; border-bottom: 1px solid #ccc; padding-bottom: 18px; margin-bottom: 18px; font-size: 35px;
												text-align: center;
												margin-bottom: 25px;
												font-weight: 300;'>
												 <img style='width:100px; margin-right: 12px;' src='{$baseurl}assets/images/minda_logo.png'> 
												 <b style='position: absolute;'>MinDA</b>
												 <span style='font-size: 20px; position: absolute; margin-top: 40px;'>Time &amp; Attendance</span>
											</div>
											<p style='font-weight: bold;'> Account Login Information </p>
											<table>
												<tr>											
													<td> Log in to: </td>
													<td> {$baseurl}accounts/login </td>
												</tr>
												<tr>											
													<td> Username: </td>
													<td> {$username} </td>
												</tr>
												<tr>											
													<td> Password: </td>
													<td> {$password} </td>
												</tr>
											</table>
											</div>
										</body>
										</html>
									  ";
				//$ret = $details;
				$ret = $this->Globalproc->sendtoemail($details);
			}
			
			echo json_encode( $ret );
		}
		
		public function update_leavecredits() {
			// deductions to the employee_leave_credits table

			$this->load->model("v2main/Globalproc");
			$status 	    = false;
			$info     		= $this->input->post("info");
			//echo json_encode($info);
			
			/*
			$info 			= array(
								"a" => array("exact_id"=> 6139,"type"=>'insert')
								);
			*/
			
			$exact_id 		= $info['a']['exact_id'];
		/* not in use*/	$type = $info['a']['type']; // type of process || insert || update || delete :: not used by me, Alvin
			
			$ck_details 	= $this->Globalproc->get_details_from_table("checkexact",
																		["exact_id"=>$exact_id],
																		["type_mode","leave_id","employee_id","checkdate"]); 

			$ck_type 		= $ck_details['type_mode'];
			$ck_leaveid 	= $ck_details['leave_id'];
			
			
			$ck_inc_dates 	= $ck_details['checkdate'];   			// check_dates values  // full date	
			$count_d_days   = null;
			
				// ====== formats of dates ===== 
					// leave 	: Sep 3,  2017
					// ams     	: 8/17/2017
				// ========== end ==============
			
			// new values 
			$sick_leave 			= null;
			$vacation_leave 		= null;
			
			// temp fields: employee_leave_credits DB fields 
				$fields = array(
							"vl_value"      => null,
							"fl_value"      => null,
							"sl_value"      => null,
							"spl_value"     => null,
							"coc_value"     => null,
							"employee_id"   => $ck_details['employee_id'],
							"exact_id"      => $exact_id,
							"credits_as_of" => null
							);
			// end

			switch($ck_type) {
				case "LEAVE":
					$d_ds 			= substr($ck_inc_dates,3,-4);		
					$d_days 		= explode(",",$d_ds);
					
					$count_d_days 	= count($d_days)-1;				// count
					
					$details 		= ['elc_id','vl_value','fl_value','sl_value','spl_value','coc_value','credits_as_of'];
					$where 			= "where elc_id = (select max(elc_id) from employee_leave_credits where employee_id = '{$ck_details['employee_id']}')";
					
					$session_database = $this->session->userdata('database_default');
					$this->load->database($session_database, TRUE);
					$leavecredits   = $this->Globalproc->get_details_from_table("employee_leave_credits",$where,$details);
					
					// copy data from db to temp fields :: stands as default values :: data inside will be altered on stage 2
						$fields["vl_value"]	 	 = $leavecredits['vl_value'];
						$fields["fl_value"]  	 = $leavecredits['fl_value'];
						$fields["sl_value"]  	 = $leavecredits['sl_value'];
						$fields["spl_value"] 	 = $leavecredits['spl_value'];
						$fields["coc_value"] 	 = $leavecredits['coc_value'];
						$fields["credits_as_of"] = date("m/d/Y");
					// end
					
					// stage 2
					if ($ck_leaveid == 1) { // sick leave 
						// get the last value of sick leave from employee_leave_credits
							// =====================================================================
							// get_details($table, $where, $details)
							$sl 	       		= $leavecredits['sl_value'];
							// =====================================================================
							// subtract the value with the supplied data from database
							$fields['sl_value'] = $sl - (1.250*$count_d_days);
							// =====================================================================
					} else if ($ck_leaveid == 2) { // vacation leave
						// get the last value of vacation leave from employee_leave_credits
						$vl 					= $leavecredits['vl_value'];
						$fields['vl_value']		= $vl - (1.250*$count_d_days);
					} 
					
					// ================ call the update function here 
						$status = $this->Globalproc->__save("employee_leave_credits", $fields);
					// ================
			
					break;
				case "AMS":
						
					break;
				
			}
			
			echo json_encode($status); 
			
		}
		
		public function getaccom_reps() {
			$this->load->model("Globalvars");
			$this->load->model("v2main/Globalproc");
			
			$emp_id = $this->Globalvars->employeeid;
			
			$sql  = "select * from d_accomplishment where user_id = '{$emp_id}'";
			$data = $this->Globalproc->gdtf("d_accomplishment",['user_id'=>$emp_id],"*");
			//var_dump($data);
			if (count($data) == 0) {
				echo json_encode(false);
			} else {
				echo json_encode($data);
			}
			
		}
				
		public function get_emps() {
			$this->load->model('main/main_model');
			$this->load->model("v2main/Leavemgt");
			$a = $this->main_model->array_utf8_encode_recursive( $this->Leavemgt->getemployees(true) );
			echo json_encode($a);
		}
		
		public function conversions() {
			$this->load->model("v2main/Globalproc");
			$sql = "select * from hours_minutes_conversation_fractions";
			echo json_encode( $this->Globalproc->__getdata($sql) );
		}
		
		public function delete_from_elc(){
			$this->load->model("v2main/Globalproc");
			
			$elc_id = $this->input->post("info");
			$elc_id = $elc_id['elcid'];
			
			$sql 	= "delete from employee_leave_credits where elc_id = '{$elc_id}'";
			
			$is_del = $this->Globalproc->run_sql($sql);
			echo json_encode($is_del);
		}
		
		public function return_the_values() {
			$this->load->model("v2main/Globalproc");
			// get the info
			$info	 	= $this->input->post("info");
			
			// employee id 
			$empid   	= $info["emp_id"];
			
			// employee leave credits (ELC) ids			
			$elc_ids 	= $info['elcids']['id'];
			//  $elc_ids 	= ["249","248","247","246"];
				
			// elc id to be deleted
			$delete_id  = $info["delete_"];
			//	$delete_id  = "247";
			
			$delete_at   = array_search($delete_id, $elc_ids, true);
				
			//$is_deleted = true;
			// if ($is_deleted) { 
				
			array_splice($elc_ids,$delete_at);
				
				// get the data from the delete item
					$info_deleted   = $this->Globalproc->gdtf("employee_leave_credits",['elc_id' => $delete_id],"*");
					$del_grp_id 	= $info_deleted[0]->exact_id;
					
					$with_pay_value = $info_deleted[0]->withpay;
					$vl_value       = $info_deleted[0]->vl_value; 	// vacation leave
					$sl_value 		= $info_deleted[0]->sl_value;	// sick leave
					$fl_value    	= $info_deleted[0]->fl_value;	// force leave
					$spl_value		= $info_deleted[0]->spl_value;  // special leave
					$coc_value 		= $info_deleted[0]->coc_value;	// coc value
				// end
				
				// new values 
					$new_value      = null;
				// end 
					$where_to_minus = null;
				
					$ded_type 		= "default";
					
					switch($info_deleted[0]->elc_type) {
						case "leave":
							$sql  = "select ce.leave_id,l.leave_name,l.leave_code from checkexact as ce
								     JOIN leaves as l on ce.leave_id = l.leave_id 
									 WHERE ce.grp_id = '{$del_grp_id}'";
							$check_type     = $this->Globalproc->__getdata($sql);
							$ded_type       = $check_type[0]->leave_code;
							break;
						case "COC":
							
							break;
						case "ps":
							$sql = "select ce.ps_type from checkexact as ce where ce.grp_id = '{$del_grp_id}'";
							$check_type     = $this->Globalproc->__getdata($sql);
							
							if ($check_type[0]->ps_type == 2) {
								$ded_type   = "PS2"; // .$check_type[0]->ps_type
							} else {
								$ded_type 	= "default";
							}
							
							break;
						case "earned":
						case "FB":
							$ded_type = "earned";
							break;
						case "t":
							$ded_type = "T";
							break;
						case "ut":
							$ded_type = "UT";
							break;
					}
										
				// end
				
				$is_updated = false;
				$do_deduct  = false;
				// update the remaining elc_ids
				if ($ded_type != "default") {
					for($i = count($elc_ids)-1; $i >= 0 ; $i--){
						$for_sql_update = $this->Globalproc->gdtf("employee_leave_credits",["elc_id"=>$elc_ids[$i]],"*");
						
						$add_to_ = null;
						switch($ded_type) {
							case "VL":
							case "PS2":
							case "UT":
							case "T":
								$add_to_ = "vl_value";
								$do_deduct = true;
								break;
							case "SL":
								$add_to_ = "sl_value";
								$do_deduct = true;
								break;
							case "SPL":
								$add_to_ = "spl_value";
								$do_deduct = true;
								break;
							case "FL":
								$add_to_ = "fl_value";
								$do_deduct = true;
								break;
							case "earned":
									$add_to_ = "atik2";
									
									$sl_deduct = $for_sql_update[0]->sl_value - $info_deleted[0]->sl_earned;
									$vl_deduct = $for_sql_update[0]->vl_value - $info_deleted[0]->vl_earned;;
									
									$spl_earned = 0;
									$fl_earned = 0;
									
									$coc_earned = 0; // tentative
								
									$is_updated = $this->Globalproc->__update("employee_leave_credits",
																			  ["vl_value" => $vl_deduct,
																			   "sl_value" => $sl_deduct],
																			  ["elc_id" => $elc_ids[$i]]
																			);
								break;
						}
						
						if ($add_to_ == null) { return; }
						
						if ( $do_deduct ) {
							$new_value = $with_pay_value + $for_sql_update[0]->$add_to_;
							
							// handle null value $new_value variable
								if ($new_value == null) { return; }
							// end 
							
							$is_updated = $this->Globalproc->__update("employee_leave_credits" , [$add_to_ => $new_value] , ["elc_id"=>$elc_ids[$i]]);
						} else {
							$is_updated = true;
							if ($ded_type != "earned") {
								break;
							}
						}
					}
				}
				// end
			// }
			
			//if ($is_updated) {
				// delete the selected ID
					$sql 		= "DELETE from employee_leave_credits where elc_id = '{$delete_id}'";
					$is_deleted = $this->Globalproc->run_sql($sql);
					
					if ($is_deleted) {$is_updated = true;} else { $is_updated = false;}
				// end
			//}
			
			echo json_encode( [$is_updated,$del_grp_id] );
			// var_dump($data);
			
			// echo json_encode( [$elc_ids, $delete_id, $delete_at] );
			
		}
		
		function getleavecredits() {
			$this->load->model("v2main/Globalproc");
			
			$empid = $this->input->post("info");
			$empid = $empid['empid'];
			
			/*
			$sql   = "select * from employee_leave_credits as elc 
					  LEFT JOIN checkexact as c on elc.exact_id = c.exact_id
					  LEFT JOIN leaves as l on c.leave_id = l.leave_id
					  where elc.employee_id = '{$empid}'";
			*/
			
			/*
			$sql = "select * from employee_leave_credits as elc 
					LEFT JOIN checkexact as c on elc.exact_id = c.exact_id
					LEFT JOIN leaves as l on c.leave_id = l.leave_id
					LEFT JOIN checkexact_leave_logs as cll on c.exact_id = cll.exact_id
					LEFT JOIN checkexact_approvals  as ca on cll.exact_id = ca.exact_id
					where elc.employee_id = '{$empid}' order by elc.elc_id ASC";
			*/
			
			/*
			$sql = "select * from employee_leave_credits as elc 
					LEFT JOIN checkexact as c on elc.exact_id = c.grp_id
					LEFT JOIN leaves as l on c.leave_id = l.leave_id
					LEFT JOIN checkexact_leave_logs as cll on c.grp_id = cll.grp_id
					LEFT JOIN checkexact_approvals  as ca on cll.grp_id = ca.grp_id
					where elc.employee_id = '{$empid}' order by elc.elc_id ASC";
			*/
		
		//	$empid = 62;	
		//	$empid = 389;	
			$sql = "select * from 
					(select 
						distinct(elc.exact_id) as grp_id,
						elc.elc_id,
						elc.employee_id,
						elc.vl_value,
						elc.fl_value,
						elc.sl_value,
						elc.spl_value,
						elc.coc_value,
						elc.credits_as_of,
					 	elc.vl_earned,
						elc.sl_earned,
						elc.elc_type,
						elc.hrs,
						elc.mins,
						elc.withpay,
						elc.wopay,
						l.*,
						cll.no_days_applied,
						c.ps_type,
						c.type_mode_details
					 from employee_leave_credits as elc 
					 LEFT JOIN checkexact as c on elc.exact_id = c.grp_id
					 LEFT JOIN leaves as l on c.leave_id = l.leave_id
					 LEFT JOIN checkexact_leave_logs as cll on c.grp_id = cll.grp_id
					 LEFT JOIN checkexact_approvals  as ca on cll.grp_id = ca.grp_id
					where elc.employee_id = '{$empid}') as tbl1 order by tbl1.elc_id DESC";
			//  or ca.leave_authorized_is_approved = '1' 	
			
			$data  = $this->Globalproc->__getdata($sql);
		//	var_dump($data); return;
		
			$type_of_credit = [
				"ps1" 			 => "Official",
				"ps2" 			 => "Personal",
				"FB"  			 => "Forwarded Balance",
				"1"   			 => "Sick",
				"2"   			 => "Vacation",
				"3"   			 => "Maternity",
				"6"   			 => "Force",
				"4"   			 => "special",
				"7"   			 => "rehabilitation",
				"8"   			 => "RA 9710 s. 2010 and CSC",
				"9"   			 => "Gynaecological Disorder",
				"10"  			 => "solo parent",
				"11"  			 => "paternity",
				"12"  			 => "anti-violence against women",
				"13"  			 => "terminal",
				"15"  			 => "study",
 				"ut"  			 => "Undertime",
				"t"   			 => "Tardiness",
				"paf" 			 => "PAF",
				"ob_travel"  	 => "Travel",
				"ob_activities"  => "Activities",
				"cto"			 => "Compensatory Time-Off", // for CTO
				"earned"		 => "earned credits"
			];
			
			$details = [];
			
			$grp_id  = false;
			//$_days = 0;
			for($i = 0; $i <= count($data)-1;$i++) {
				$details[$i]['grp_id']					  = $data[$i]->grp_id;
				$details[$i]['emp_id']					  = $data[$i]->employee_id;
				$details[$i]["elc_id"] 					  = $data[$i]->elc_id;
				$details[$i]["period_date"] 			  = $data[$i]->credits_as_of;
				
			//	echo $data[$i]->grp_id;
				/*
				if ($grp_id == false) {
					$grp_id = $data[$i]->grp_id;
					$_days++;
				} else {
					if ($grp_id != $data[$i]->grp_id) {
						$grp_id = $data[$i]->grp_id;
						$_days = 0;
					} else {
						$_days++;
					}
				}
				*/
			//	echo $grp_id."<br/>";
				
				$_days = "&nbsp;";
			//	echo$details[$i]["period_date"] . var_dump($data[$i]->no_days_applied) . "<br/>";
				list($m, $d, $y) = explode(" ",$details[$i]["period_date"]);
				$_days = explode("-",$d);
				
				/*
				if ( $data[$i]->no_days_applied==NULL ) {
					$_days = "&nbsp;";
				} else {
					
				}
				*/
				
				$details[$i]["type_of_credit"]				  = null;
				
				if ($data[$i]->elc_type=="leave") {
					$details[$i]["type_of_credit"]			  = $type_of_credit[ $data[$i]->leave_id ];
				} else if ($data[$i]->elc_type=="ps") {
					$details[$i]["type_of_credit"]			  = $type_of_credit["ps".$data[$i]->ps_type];
				} else if ($data[$i]->elc_type=="OB") { // mark ob 1
					$details[$i]["type_of_credit"]			  = $type_of_credit["ob_".strtolower($data[$i]->type_mode_details)];
					$data[$i]->no_days_applied 				  = true;
				} else {
					$details[$i]["type_of_credit"]			  = $type_of_credit[$data[$i]->elc_type];
				}
				
				
				$part_typeofcredit = null;
				
				if ($data[$i]->elc_type=="earned") {
					$part_typeofcredit = "&nbsp;";
				} else {
					$part_typeofcredit = $data[$i]->elc_type;
				}
				
				$details[$i]["particulars"]['type_of_credit'] = $part_typeofcredit;
				$details[$i]["particulars"]['count_x'] 		  = null; // mark the count variable
				$details[$i]["particulars"]['days'] 		  = ($data[$i]->no_days_applied==NULL)?"&nbsp;": count($_days); //$data[$i]->no_days_applied;
				$details[$i]["particulars"]['hrs'] 		  	  = ($data[$i]->hrs==null)?"&nbsp;":$data[$i]->hrs;
				$details[$i]["particulars"]['mins'] 		  = ($data[$i]->mins==null)?"&nbsp;":$data[$i]->mins;
			
				$details[$i]["vacation"]['earned'] 		  	  = ($data[$i]->vl_earned==0)?"&nbsp;":$data[$i]->vl_earned;
				$details[$i]["vacation"]['balance'] 		  = ($data[$i]->vl_value==0)?"&nbsp;":$data[$i]->vl_value;
				$details[$i]["vacation"]['abs_w_pay'] 		  = "&nbsp;";
				$details[$i]["vacation"]['abs_wo_pay'] 		  = "&nbsp;";
				
				$elc_types = ["ut","t"];
				
				if ($data[$i]->elc_type == "ps" || in_array($data[$i]->elc_type, $elc_types) || $data[$i]->leave_id == 2) {					
					$details[$i]["vacation"]['abs_w_pay'] 	  = ($data[$i]->withpay==0)?"&nbsp;":$data[$i]->withpay;
					$details[$i]["vacation"]['abs_wo_pay'] 	  = ($data[$i]->wopay==0)?"&nbsp;":$data[$i]->wopay;
				}
				
				$details[$i]["sick"]['earned'] 		  	  	  = ($data[$i]->sl_earned==0)?"&nbsp;":$data[$i]->sl_earned;
				$details[$i]["sick"]['balance'] 		 	  = ($data[$i]->sl_value==0)?"&nbsp;":$data[$i]->sl_value;
				$details[$i]["sick"]['abs_w_pay']  	  		  = "&nbsp;";
				$details[$i]["sick"]['abs_wo_pay'] 	  		  = "&nbsp;";
	
				if ($data[$i]->leave_id == 1) { // $data[$i]->ps_type == 1 || 
					$details[$i]["sick"]['abs_w_pay']  	  	  = ($data[$i]->withpay==0)?"&nbsp;":$data[$i]->withpay;
					$details[$i]["sick"]['abs_wo_pay'] 	  	  = ($data[$i]->wopay==0)?"&nbsp;":$data[$i]->wopay;
				}
			
			}
		//	var_dump($details); return;
			echo json_encode($details);
		}
		
		public function management($empid = '') {
			/*
			// get and set the employee id from url
			$this->employeeid = $this->uri->segment(3);
			// end
			
		//	if ($this->employeeid == null) { 
				
		//	} else {
				$this->load->model("leave_model");
				$this->load->model("v2main/Globalproc");
			
				//$getemployees = $this->leave_model->get_employee_with_credits('');
	
				$data['title'] = '| Ledger';
				
				//$data['dbusers'] 	 	 = $getemployees;
				//$data['employee_id'] 	 = ''; //$this->$employeeid;
				//$data['usertype'] 	 	 = $this->usertype;
				//$data['employment_type'] = $this->emptype;
				echo "<script>";
					echo "var isadmin = true;";
				echo "</script>";
			
				$data['headscripts']['style'][2]  	= base_url()."v2includes/style/leavemgt.style.css";
				$data['headscripts']['js'][0]       = base_url()."v2includes/js/windowresize.js";
				$data['headscripts']['js'][1]       = base_url()."v2includes/js/leavemgt.procs.js";
			
				if ($this->usertype == "user") {
				// die("This is an admin-only area.. please go back.");
					$data['notadmin']				  = true;
					//$data['headscripts']['js'][2]   = base_url()."v2includes/js/my_leave_ledger.js";
				} else {
					$data['admin']		= true;
				}
				
				//----------------------- load leave management model ---------------------
				$this->load->model("v2main/Leavemgt");

				$this->Leavemgt->__setid($this->employeeid);
				
				// record an earned leave this month
				#$ret = $this->Leavemgt->earned_a_leave();
					
				//$this->load->model("v2main/Creditdeductions");
				//$this->Creditdeductions->computedeductibles("T","44f683-9b8b15",2);

				// get the earned leaves
				$data['vals']	= $this->Leavemgt->__getearnedleaves();
				// __getearnedleaves
				//----------------------------------end-----------------------------------

				 $data['main_content'] = 'v2views/leavemgt';
				 $this->load->view('hrmis/admin_view',$data);
				//$this->load->view("v2views/leavemgt", $data);
		//	}
			*/
			
			$data['noemp']	= false;
			if ($empid == '') {
				$data['noemp']	= true;
				//redirect(base_url(),"refresh");
			}
			
			if ($this->usertype != "admin") {
				die("You are not allowed here"); return;
			}
			
			$this->load->model("v2main/Globalproc");
			
			$ledger 						 = $this->Globalproc->getleavecredits($empid);
			$data['employees']				 = $this->Globalproc->getplantilla();
			// $data['employees']				 = $this->Globalproc->getallemps();
			$data['ledger'] 				 = $ledger;
				
			$data['title']					 = "| Ledger";
			
			// old leave management program :: used in the recording of leave
				$data['headscripts']['js'][]	 = base_url()."v2includes/js/leavemgt.procs.js";
			
			// new leave mananagement program :: used in the recalling of leave 
				$data['headscripts']['js'][]	 = base_url()."v2includes/js/newleavemgt.procs.js";
			
			
				
			$data['headscripts']['style'][]  = base_url()."v2includes/style/leavemgt.style.css";
			$data['main_content'] 			 = "v2views/displedger";
			
			$year 							 = date("Y");
			$data['fl']						 = $this->Globalproc->get_rem("FL",$year,$empid);
			$data['spl']					 = $this->Globalproc->get_rem("SPL",$year,$empid);
			
			$this->load->view('hrmis/admin_view',$data);
			
		}
		
		public function application() {
			$this->load->model("v2main/Formpopulate");
			$this->load->model("v2main/Formfetcher");

			$type = $this->uri->segment(3);
			$content = null;	
						
			if ($type == null) { die("Please choose a type of form."); }
			switch($type) {
				case "spl":
					// check if leave credit is exhausted
					$this->load->model("v2main/Leaveprocs");
					$spl_restrict = $this->Leaveprocs->spl();

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

					$data['title'] = '| Special Leave Priviledges Application';
				
					$data['headscripts']['style'][1] = base_url()."v2includes/style/spl.style.css";
					

					$data['empdata'] = $this->Formpopulate->__getempdata();

					$content = "v2views/forms/spl";
					break;

				case "paf":
					$data['title'] = '| PAF Application';
				
					$data['headscripts']['style'][1] = base_url()."v2includes/style/paf.style.css";
					
					$data['empdata'] = $this->Formpopulate->__getempdata();
					$content = "v2views/forms/paf";
					break;

				case "sick":
					$data['title'] = '| Sick Leave Application';
				
					$data['headscripts']['style'][1] = base_url()."v2includes/style/sickleave.style.css";
					$data['headscripts']['style'][2] = "//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css";
					
					$data['headscripts']['js'][3] 	 = base_url()."v2includes/js/sick.leave.js";
					
					$data['empdata'] 				 = $this->Formpopulate->__getempdata();
					$content = "v2views/forms/sickleave";
					break;

				case "vacation":
					$data['title'] = '| Vacation Leave Application';
				
					$data['headscripts']['style'][1] = base_url()."v2includes/style/genericform.style.css";
					
					$data['empdata'] = $this->Formpopulate->__getempdata();
					$content = "v2views/forms/vacationleave"; // pink form

					$data['leavetype'] = 'vl';
					$data['leavename'] = $this->Globalproc->get_details_from_table("leaves",["leave_code"=>"VL"],['leave_name'])["leave_name"];
					break;

				case "maternity":
					$data['title'] = '| Maternity Leave Application';
				
					$data['headscripts']['style'][1] = base_url()."v2includes/style/genericform.style.css";
					
					$data['empdata'] = $this->Formpopulate->__getempdata();
					$content = "v2views/forms/vacationleave"; // pink form

					$data['leavetype'] = 'ml';
					$data['leavename'] = $this->Globalproc->get_details_from_table("leaves",["leave_code"=>"ML"],['leave_name'])["leave_name"];
					break;

				case "paternity":
					$data['title'] = '| Paternity Leave Application';
				
					$data['headscripts']['style'][1] = base_url()."v2includes/style/genericform.style.css";
					
					$data['empdata'] = $this->Formpopulate->__getempdata();
					$content = "v2views/forms/vacationleave"; // pink form

					$data['leavetype'] = 'pl';
					$data['leavename'] = $this->Globalproc->get_details_from_table("leaves",["leave_code"=>"PL"],['leave_name'])["leave_name"];
					break;

				case "rl":
					$data['title'] = '| Rehabilitation Leave Application';
				
					$data['headscripts']['style'][1] = base_url()."v2includes/style/genericform.style.css";

					
					$data['empdata'] = $this->Formpopulate->__getempdata();
					$content = "v2views/forms/vacationleave"; // pink form

					$data['leavetype'] = 'rl';
					$data['leavename'] = $this->Globalproc->get_details_from_table("leaves",["leave_code"=>"RL"],['leave_name'])["leave_name"];
					break;

				case "fl":
					$data['title'] = '| Forced/Mandatory Leave Application';
				
					$data['headscripts']['style'][1] = base_url()."v2includes/style/genericform.style.css";

					
					$data['empdata'] = $this->Formpopulate->__getempdata();
					$content = "v2views/forms/vacationleave"; // pink form

					$data['leavetype'] = 'fl';
					$data['leavename'] = $this->Globalproc->get_details_from_table("leaves",["leave_code"=>"FL"],['leave_name'])["leave_name"];
					break;
				case "csc":
					$data['title'] = '| RA 9710 s. 2010 and CSC MC 25 s. 2010 Leave Application';
				
					$data['headscripts']['style'][1] = base_url()."v2includes/style/genericform.style.css";

					
					$data['empdata'] = $this->Formpopulate->__getempdata();
					$content = "v2views/forms/vacationleave"; // pink form

					$data['leavetype'] = 'csc';
					$data['leavename'] = $this->Globalproc->get_details_from_table("leaves",["leave_code"=>"CSC"],['leave_name'])["leave_name"];
					
					break;
				case "gyne":
					$data['title'] = '| Gynaecological Disorder Leave Application';
				
					$data['headscripts']['style'][1] = base_url()."v2includes/style/genericform.style.css";

					
					$data['empdata'] = $this->Formpopulate->__getempdata();
					$content = "v2views/forms/vacationleave"; // pink form

					$data['leavetype'] = 'gyne';
					$data['leavename'] = $this->Globalproc->get_details_from_table("leaves",["leave_code"=>"GYNE"],['leave_name'])["leave_name"];
					
					break;
				case "soloparent":
					$data['title'] = '| Solo Parent Leave Application';
				
					$data['headscripts']['style'][1] = base_url()."v2includes/style/genericform.style.css";

					
					$data['empdata'] = $this->Formpopulate->__getempdata();
					$content = "v2views/forms/vacationleave"; // pink form

					$data['leavetype'] = 'sp';
					$data['leavename'] = $this->Globalproc->get_details_from_table("leaves",["leave_code"=>"SP"],['leave_name'])["leave_name"];
					
					break;
				case "vawcy":
					$data['title'] = '| Anti-Violence Againts Women and Their Children Act of 2004  Leave Application';
				
					$data['headscripts']['style'][1] = base_url()."v2includes/style/genericform.style.css";

					
					$data['empdata'] = $this->Formpopulate->__getempdata();
					$content = "v2views/forms/vacationleave"; // pink form

					$data['leavetype'] = 'vawcy';
					$data['leavename'] = $this->Globalproc->get_details_from_table("leaves",["leave_code"=>"VAWCY"],['leave_name'])["leave_name"];
					
					break;
				case "tl":
					$data['title'] = '| Terminal Leave Application';
				
					$data['headscripts']['style'][1] = base_url()."v2includes/style/genericform.style.css";

					
					$data['empdata'] = $this->Formpopulate->__getempdata();
					$content = "v2views/forms/vacationleave"; // pink form

					$data['leavetype'] = 'tl';
					$data['leavename'] = $this->Globalproc->get_details_from_table("leaves",["leave_code"=>"TL"],['leave_name'])["leave_name"];
					
					break;
				case "mplap":
					$data['title'] = '| Maternity / Paternity Leave for Adoptive Children Leave Application';
				
					$data['headscripts']['style'][1] = base_url()."v2includes/style/genericform.style.css";

					
					$data['empdata'] = $this->Formpopulate->__getempdata();
					$content = "v2views/forms/vacationleave"; // pink form

					$data['leavetype'] = 'mplap';
					$data['leavename'] = $this->Globalproc->get_details_from_table("leaves",["leave_code"=>"MPLAP"],['leave_name'])["leave_name"];
					
					break;
				case "study":
					$data['title'] = '| Study Leave Application';
				
					$data['headscripts']['style'][1] = base_url()."v2includes/style/genericform.style.css";

					
					$data['empdata'] = $this->Formpopulate->__getempdata();
					$content = "v2views/forms/vacationleave"; // pink form

					$data['leavetype'] = 'study';
					$data['leavename'] = $this->Globalproc->get_details_from_table("leaves",["leave_code"=>"STUDY"],['leave_name'])["leave_name"];
					
					break;
				case "sel":
					$data['title'] = '| Study Leave Application';
				
					$data['headscripts']['style'][1] = base_url()."v2includes/style/genericform.style.css";

					
					$data['empdata'] = $this->Formpopulate->__getempdata();
					$content = "v2views/forms/vacationleave"; // pink form

					$data['leavetype'] = 'el';
					$data['leavename'] = $this->Globalproc->get_details_from_table("leaves",["leave_code"=>"EL"],['leave_name'])["leave_name"];
					break;
				
			}
			
			$this->load->model("v2main/Leavemgt");
			
			$data['approving'] = $this->Leavemgt->__get_approving_personnel([10, 
																	 $data['empdata'][0]->Division_id ,
																	 $data['empdata'][0]->DBM_Sub_Pap_Id]);
			
			
			$data['headscripts']['js'][2] 		= base_url()."assets/js/v2js/jquery-ui.multidatespicker.js";
			$data['headscripts']['js'][0] 		= "https://code.jquery.com/ui/1.12.1/jquery-ui.js";
			
			$data['headscripts']['style'][0]	= base_url()."v2includes/style/leave.cabinet.css";
			$data['headscripts']['style'][3]	= base_url()."assets/css/jquery-ui.multidatespicker.css";
			
			$data['returned']     = $this->Formfetcher->return;
			$data['main_content'] = $content;
			$this->load->view('hrmis/admin_view',$data);
		}

		public function cabinet() {
			$data['title'] = '| Leave form Cabinet';

			$data['headscripts']['js'] 		= base_url()."v2includes/js/windowresize.js";
			$data['headscripts']['style']	= base_url()."v2includes/style/leave.cabinet.css";
			
			
			$data['main_content'] = "v2views/cabinet";
			$this->load->view("hrmis/admin_view", $data);
		}
		
		public function forwardbalance() {
			$this->load->model("v2main/Globalproc");
			
			$fbalance = $this->input->post("info");
			$fbalance = $fbalance['f_details'];
			
			$empid    = (int) $fbalance['empid'];
		//	$empid = 62;
			// get previous balance 
				$sql = "select * from employee_leave_credits
						where elc_id = (select max(elc_id) from employee_leave_credits where employee_id = '{$empid}')"; //  and elc_type ='FB'
				
				$_vals = $this->Globalproc->__getdata($sql);	
			// end 
			
			$vl_bal  = 0;
			$fl_bal  = 0;
			$sl_bal  = 0;
			$spl_bal = 0;
			$coc 	 = 0;
			
			if (count($_vals) > 0) {
				$vl_bal  = $_vals[0]->vl_value;
				$fl_bal  = $_vals[0]->fl_value;
				$sl_bal  = $_vals[0]->sl_value;
				$spl_bal = $_vals[0]->spl_value;
				$coc 	 = $_vals[0]->coc_value;
			}
			
			$details = [
				"employee_id"		=> $empid,
				"vl_value"			=> $vl_bal + $fbalance['vlbal'],
				"fl_value"			=> $fl_bal + $fbalance['flbal'],
				"sl_value"			=> $sl_bal + $fbalance['slbal'],
				"spl_value"			=> $spl_bal + $fbalance['splbal'],
				"coc_value"			=> $fbalance['coc'], // for coc
				"vl_earned"			=> $fbalance['vlbal'],
				"sl_earned"			=> $fbalance['slbal'],
				"credits_as_of"		=> date("M d, Y", strtotime($fbalance['dateasof'])),
				"elc_type"			=> "FB"
			];
			
			//return;
			$ret = $this->Globalproc->__save("employee_leave_credits",$details);
			echo json_encode($ret);
			
		}
		
		public function award_credit() {
			$this->load->model("v2main/Globalproc");
			
			$awarded = $this->input->post("info");
			$empid   = $awarded['a_emp_id'];
			$ret     = $this->Globalproc->awardcredit($empid);
			/*
		//	$empid = 6;
			// get previous balance 
				$sql   = "select * from employee_leave_credits
						  where elc_id = (select max(elc_id) from employee_leave_credits 
											where employee_id = '{$empid}')";	
				$_vals = $this->Globalproc->__getdata($sql);
			// end
			
			$vl_bal  = 0;
			$sl_bal  = 0;
			
			$spl_bal = 0;
			$fl_bal  = 0;
		
			$coc_value = 0;
			if (count($_vals) > 0) {
				$vl_bal  = $_vals[0]->vl_value;
				$sl_bal  = $_vals[0]->sl_value;
				
				$fl_bal  = $_vals[0]->fl_value;
				$spl_bal = $_vals[0]->spl_value;
				
				$coc_value = $_vals[0]->coc_value;
			}
			
			$vl_earned = 1.250;
			$sl_earned = 1.250;
			
			$slme_sql =  "select * from employee_leave_credits
						  where elc_id = (select max(elc_id) from 
											employee_leave_credits where employee_id = '{$empid}' and elc_type = 'earned')";
			
			$select_last_mo_earned = $this->Globalproc->__getdata($slme_sql);
			
			if (count($select_last_mo_earned)==0) {
				$last_mo = date("m/d/Y",strtotime("-2 month", strtotime(date("m/d/Y"))));
			} else {
				$last_mo = $select_last_mo_earned[0]->credits_as_of;
			}
			
			// === get the deductions for this month 
				$dd   = date("M",strtotime("-1 month", strtotime(date("M"))));
				$deds = "select 
								tb1.*,
							CASE 
								WHEN tb1.elc_type = 'leave' then (select tb2.leave_id from 
																	(select top 1 * from checkexact 
																		where grp_id = tb1.exact_id) 
																	as tb2 )
								ELSE '0'
							END as leavename
							from (select * from employee_leave_credits 
								where CONVERT(varchar(3), credits_as_of) = '{$dd}' and employee_id = '{$empid}') as tb1
									where tb1.elc_type = 't' or tb1.elc_type = 'ut' or tb1.elc_type = 'leave'";
				
				$dets = $this->Globalproc->__getdata($deds);
				
				$sl_deds = 0;
				$vl_deds = 0;
				if (count($dets)>0){
					foreach($dets as $d) {
						if ($d->leavename == 1) { // compound without-pay to SL 
							$sl_deds += $d->wopay;
						} else if ($d->leavename == 2 || $d->elc_type == 't' || $d->elc_type == 'ut') { // compound without-pay to vl
							$vl_deds += $d->wopay;
						}
					}
				}
			// === end deductions 
			
			// setting new values
				$vl_earned = $vl_earned - $vl_deds;
				$sl_earned = $sl_earned - $sl_deds;
			// end 
			
			$details = [
				"employee_id"		=> $empid,
				"vl_value"			=> $vl_bal + $vl_earned,
				"fl_value"			=> $fl_bal + 0,
				"sl_value"			=> $sl_bal + $sl_earned,
				"spl_value"			=> $spl_bal + 0,
				"coc_value"			=> $coc_value, // for coc
				"vl_earned"			=> $vl_earned,
				"sl_earned"			=> $sl_earned,
				"credits_as_of"		=> date("M. 1-t, Y", strtotime("+1 month", strtotime($last_mo))),
				"elc_type"			=> "earned"
			];
		
			$ret = $this->Globalproc->__save("employee_leave_credits",$details);
			*/
			echo json_encode($ret);
		}
		
		public function addleave_mgt() {
			$this->load->model("v2main/Globalproc");
			
			$ret     = false;
			$details = $this->input->post("info");
			$details = $details['data'];
			
			/*
				$details['cto_end'] = "05:00 PM";
				$details['cto_start'] = "08:00 AM";
				$details['dates'] = ["Oct 17, 2018"];
				$details['days'] = "";
				$details['empid'] = 6;
				$details['hrs']  = "";
				$details['mins'] = "";
				$details['type'] = "cto";
			*/
			
			
			$empid    = (int) $details['empid'];
			$days     = (int) $details['days'];
			$hrs      = $details['hrs'];
			$mins     = $details['mins'];
			$dates    = $details['dates'];
			$type     = $details['type']; // deduction type
			$formonet = $details['formonet'];
			
			$grp_id = $this->Globalproc->__createuniqueid( date("FldYhisa") );
			
				$type_details = [
					"typemode"			=> null,
					"leave_value"		=> null,
					"value" 			=> null,
					"date_inclusion"	=> null,
					"no_days_applied"	=> null,
					"hrs"	  		 	=> null,
					"mins" 		 		=> null
				];
			
			$themonth = null;
			$theday   = null;
			$theyear  = null;
			
			$iscto = false;
			
			for ($_ds = 0; $_ds <= count($dates)-1; $_ds++) {		// for 
				$leavetype = null;
				if($type == "leave") {
					$leavetype = (int) $details['leavetype']; // leave type
				}
				
				$pstype   = null;
				if ($type == "ps") {
					$pstype   = (int) $details['pstype'];
				}
				
				// save to checkexact
				$db_details = [
				  "employee_id"				=> $empid,
				  "type_mode"			    => strtoupper($type),
				  "type_mode_details"		=> null,
				  "modify_by_id"			=> null,
				  "checkdate"				=> $dates[$_ds],
				  "remarks"					=> null,
				  "reasons"					=> null,
				  "attachments"				=> "null",
				  "date_added"				=> $this->Globalproc->thedate(),
				  "is_approved"				=> 1,
				  "aprroved_by_id"			=> 52,
				  "date_approved"			=> $this->Globalproc->thedate(),
				  "ps_type"					=> null,
				  "time_in"					=> null,
				  "time_out"				=> null,
				  "leave_id"				=> $leavetype,
				  "leave_name"				=> null,
				  "ps_guard_id"				=> null,
				  "leave_is_halfday"		=> null,
				  "leave_is_am_pm_select"	=> null,
				  "grp_id"					=> $grp_id
				];
				//echo json_encode($db_details);
				
				if ($type == "ps") {
					$db_details['ps_type']   = $pstype;
					$db_details['time_out']  = $details['timeout'];
					$db_details['time_in']   = $details['timebackin'];
				}
				
				$ret = $this->Globalproc->__save("checkexact",$db_details);
				
				// save to checkexact_approvals
					if ($ret) {
						// data for the earned leave 
						
						// end 
							
						$exactid = $this->Globalproc->getrecentsavedrecord("checkexact","exact_id");
						$exactid = $exactid[0]->exact_id;
						
						// formatting of date
						$theday   = date("d", strtotime($dates[$_ds]));
						$theyear  = date("Y", strtotime($dates[$_ds]));
									
							if ( $themonth == null ) {
								$themonth 						= date("M", strtotime($dates[$_ds]));
								$type_details['date_inclusion'] = $themonth ." ".$theday;
										
								if ($_ds != count($dates)-1) {
									$type_details['date_inclusion'] .= "-";
								}
									
							} else {
								if ( $themonth != date("M", strtotime($dates[$_ds])) ) {
									$themonth = date("M", strtotime($dates[$_ds]));
									$type_details['date_inclusion'] .= " || ". $themonth . " ";
								}
										
								$type_details['date_inclusion'] .= "".$theday;
										
								if ($_ds != count($dates)-1) {
									$type_details['date_inclusion'] .= "-";
								}		
							}
						// formatting of the date
									
						if ( $type == "spl" || $type == "leave" ) {
							
							$emp_details   = $this->Globalproc->gdtf("employees",['employee_id'=>$empid],['Division_id','DBM_Pap_id','f_name']);
							
							$chief_details = $this->Globalproc->gdtf("employees",
																	['Division_id'=>$emp_details[0]->Division_id,
																	 'conn'=>"and",
																	 "is_head"=>1],['employee_id','f_name']);
																	 
							$director_details = $this->Globalproc->gdtf("employees",
																		"DBM_Pap_id = '{$emp_details[0]->DBM_Pap_id}' and Division_id='0' and is_head='1'",
																		["employee_id"]);
						//	echo json_encode($chief_details);
						
							// save to checkexact approvals
							$approvals = [
								"exact_id" 							=> $exactid,
								"division_chief_id"					=> $chief_details[0]->employee_id,
								"division_chief_is_approved"		=> 1,
								"division_chief_remarks"			=> NULL,
								"division_date"						=> NULL,
								"paf_recorded_by_id"				=> $empid,
								"paf_recorded_by_is_approved"		=> 1,
								"paf_recorded_remarks"				=> NULL,
								"paf_recorded_date"					=> NULL,
								"paf_approved_by_id"				=> 0,
								"paf_is_approved"					=> false,
								"paf_approved_remarks"				=> NULL,
								"paf_date"							=> NULL,
								"leave_authorized_official_id"		=> $director_details[0]->employee_id,
								"leave_authorized_is_approved"		=> 1,
								"leave_authotrized_remarks"			=> NULL,
								"leave_authorized_date"				=> NULL,
								"hrmd_approved_id"					=> 52,
								"hrmd_is_approved"					=> 1,
								"hrmd_remarks"						=> NULL,
								"hrmd_date"							=> NULL,
								"grp_id"							=> $grp_id
							];
						//	echo json_encode($approvals);
							
							$ret = $this->Globalproc->__save("checkexact_approvals",$approvals);
							
							if ($ret) {
								// ============ for leave and SPL =============
								$proceed = false;
								$for_leave = [
										"exact_id"					  => $exactid,
										"no_days_applied"			  => $days,
										"leave_application_details"	  => null,
										"spl_personal_milestone"	  => 0,
										"spl_filial_obligations"	  => 0,
										"spl_personal_transaction"    => 0,
										"spl_parental_obligations"    => 0,
										"spl_domestic_emergencies"	  => 0,
										"spl_calamity_acc"			  => 0,
										"spl_first"					  => 0,
										"spl_second"				  => 0,
										"spl_third"					  => 0
									];
									
								if ($type == "spl") {
								// if leave or SPL, save to checkexact_leave_logs
									$for_leave["spl_personal_milestone"]	=	0;
									$for_leave["spl_filial_obligations"]	=	0;
									$for_leave["spl_personal_transaction"]	=	0;
									$for_leave["spl_parental_obligations"]	=	0;
									$for_leave["spl_domestic_emergencies"]	=	0;
									$for_leave["spl_calamity_acc"]			=	0;
									$for_leave["spl_first"]					=	0;
									$for_leave["spl_second"]				=	0;
									$for_leave["spl_third"]					=	0;
									
									//$proceed = true;
								} else if ($type="leave") {
									$for_leave["leave_application_details"]	=	$leavetype;
									
									$proceed = true;
								}
								
								if ($proceed) {
									$for_leave['grp_id'] = $grp_id;
									$ret = $this->Globalproc->__save("checkexact_leave_logs",$for_leave);
								}
								
								
								// generate shift
									$get_shift_id = $this->Globalproc->gdtf("employee_schedule",["employee_id" => $empid],['shift_id']);
									$shift_id 	  = $get_shift_id[0]->shift_id;
								
									$__shift	  = $this->Globalproc->gdtf("shift_mgt_logs",
																			['shift_id' => $shift_id],
																			['time_flexi_exact','time_end','type','shift_type']);
									
									$shifts = null;
								
									for ($a = 0; $a <= count($__shift)-1; $a++) {
										$shifts[$a]['checktype']  = $__shift[$a]->type;
										$shifts[$a]['shift_type'] = $__shift[$a]->shift_type;
										$shifts[$a]['checktime']  = $__shift[$a]->time_flexi_exact;
									}
									
										for ($m=0;$m<=count($shifts)-1;$m++) {
											$chk_logs = date("n/j/Y", strtotime($dates[$_ds]));
											// for checkexact_logs 
												$checkexact_logs = [
													"exact_id"		  => $exactid,
													"checktime"		  => $chk_logs." ".$shifts[$m]['checktime'],
													"checktype"		  => $shifts[$m]['checktype'],
													"shift_type"	  => $shifts[$m]['shift_type'],
													"modify_by_id"	  => 0,
													"is_modify"		  => 0,
													"is_delete"		  => 0,
													"date_added"	  => date("M d Y h:iA"),
													"date_modify"	  => 0,
													"is_bypass"		  => 0
												];
												$ret = $this->Globalproc->__save("checkexact_logs",$checkexact_logs);
											// end for checkexact_logs
										}
									
								// end generate shift
								
								
								
								// ============ save to earned leave :: leave and spl ==================
									if ($type == "leave" || $type == "spl") {
										### no. of days applied ###
											$type_details["no_days_applied"] = $days;
											$type_details["typemode"]		 = $type;
										### end ###

										#### hrs, mins value ####
										if ( $hrs != null || $hrs != 0 || $hrs != "" || $hrs != " ") {										
											$type_details['hrs']  		 	 = $hrs;
										}
										
										if ($mins != null || $mins != 0 || $mins != "" || $mins != " ") {
											$type_details['mins'] 		 	 = $mins;
										}
										#### end ####
										
										#### leave value ####
											$type_details["leave_value"]	 = $leavetype;
										#### end ####
										// $type_details["date_inclusion"]  = $dates[$_ds];
									}
								// =====================================================================
								
							}
							
						} else if ($type == "ps" || $type=="t" || $type =="ut") {
							$type_details['hrs']  		 	= $hrs;
							$type_details['mins'] 		 	= $mins;
							//$type_details['date_inclusion'] = $dates[$_ds];
							$type_details['typemode']	 	= $type;
							
							if ($type == "ps") {
								$type_details['leave_value'] 	= $pstype;
							}
							
						} else if ($type == "cto") {
							$iscto = true;
							$type_details['typemode']  = "CTO";
							$type_details['hrs_start'] = $details['cto_start'];
							$type_details['hrs_end']   = $details['cto_end'];
							$type_details['empid']	   = $empid;
							$type_details['exact_ot']  = $exactid;
						} // mark addleave
						
						$type_details['formonet'] = $formonet;
					}
				} // end for :: dates
				
			$type_details['date_inclusion'] .= ", ".$theyear;
			$ret = $this->Globalproc->calc_leavecredits($empid, $grp_id, $type_details);
			
			echo json_encode($ret); 
			
		}
		
		public function fminda() {
			//echo date("h:i A", strtotime("Wed Feb 28 2018 08:00:00 GMT+0800 (Taipei Standard Time)"));
			$__stime     = date("h:i A", strtotime("Wed Feb 28 2018 08:00:00 GMT+0800 (Taipei Standard Time)"));
			$__etime     = date("h:i A", strtotime("Fri Feb 23 2018 17:00:00 GMT+0800 (Taipei Standard Time)"));
			
			echo $__stime;
			echo "<br/>";
			echo $__etime;
			
			$start_time  = new DateTime("10:00 AM");
			$end_time    = new DateTime("6:00 PM");
			$interval    = $start_time->diff($end_time);
			$cto_hours 	 = $interval->format('%h');
			$cto_mins    = $interval->format('%i');
			
			echo "<br/>hours:".$cto_hours;
			echo "<br/>mins:".$cto_mins;
			
			echo "<br/>round:".round(.4500,2);
		}
		
		public function saveapplication() {
			$this->load->model("v2main/Globalproc");
			$this->load->model("Globalvars");
			$this->load->model("attendance_model");
			
			$details = $this->input->post("info");
			$details = $details['details'];
			
			$group_id   = $this->Globalproc->__createuniqueid( date("m/d/y h:i:s A") );
			$leave_data = $this->Globalproc->getleaves();
			
			$emp_id    = $this->Globalvars->employeeid;
			
			// employee data
			$data	   = $this->Globalproc->gdtf("employees",["employee_id"=>$emp_id],["DBM_Pap_id","Division_id"]);
			// no chiefs: those employees that reports directly to directors.
			$no_chiefs = $this->Globalproc->gdtf("employees",["employee_id"=>$emp_id],["Division_id","DBM_Pap_id"]);
			// end 
			
			$ret 	   = false;
			$exact_id  = null;
			
			// for leave 
				$leavespecifics = null;
			// for leave 
			
			// generate shift
			/*|*/	$get_shift_id = $this->Globalproc->gdtf("employee_schedule",["employee_id" => $emp_id,"conn"=>"and","is_active"=>1],['shift_id']);

					$__shift = null;
					if (count($get_shift_id)== 0) {
						$__shift[] = (object) array("type"=>"C/In" ,"shift_type"=>"AM_START","time_flexi_exact"=>"8:00 AM");
						$__shift[] = (object) array("type"=>"C/Out","shift_type"=>"AM_END"  ,"time_flexi_exact"=>"12:00 PM");
						$__shift[] = (object) array("type"=>"C/In" ,"shift_type"=>"PM_START","time_flexi_exact"=>"1:00 PM");
						$__shift[] = (object) array("type"=>"C/Out","shift_type"=>"PM_END"  ,"time_flexi_exact"=>"5:00 PM");
					} else {
						
			/*|*/		$shift_id 	  = $get_shift_id[0]->shift_id;
			/*|*/		
			/*|*/		$__shift	  = $this->Globalproc->gdtf("shift_mgt_logs",
			/*|*/											   ['shift_id' => $shift_id],
			/*|*/											   ['time_flexi_exact','time_end','type','shift_type']);
			/*|*/	}
			/*|*/	
			/*|*/   $shifts = null;
			/*|*/	for ($a = 0; $a <= count($__shift)-1; $a++) {
			/*|*/		$shifts[$a]['checktype']  = $__shift[$a]->type;
			/*|*/		$shifts[$a]['shift_type'] = $__shift[$a]->shift_type;
			/*|*/		$shifts[$a]['checktime']  = $__shift[$a]->time_flexi_exact;
			/*|*/	}
			// end generate shift
				
				$d_chief_id    = null;
				$d_chief_email = null;
				
				$d_dbm_id 	   = null;
				$d_dbm_email   = null;
				
				$chairs_id 	   = 1443; // chairmans ID 
				$usecs_id 	   = 1441; // usecs ID
				
				if ($this->Globalproc->is_chief("division",$emp_id)) {
					//$d_chief_email = 0;
					//$d_chief_id    = 0;
					
					$d_chief_email = $d_dbm_email  = $details['dbm_chief_email'];
					$d_chief_id    = $d_dbm_id 	   = $details['dbm_chief_id'];
					
				} else if ($no_chiefs[0]->Division_id == 0 && !$this->Globalproc->is_chief("director",$emp_id)) {
					// employees who reports directly to director
					$d_chief_email = $d_dbm_email  = $details['dbm_chief_email'];
					$d_chief_id    = $d_dbm_id 	   = $details['dbm_chief_id'];
				} else if ($this->Globalproc->is_chief("director",$emp_id)) {
					$dbm_id_loc    = null;
					$dbm_email_loc = null;
					
					if ($emp_id == 27) { // doc Cha
						$dbm_id_loc	= $chairs_id; // chairmans ID
					} else if ( $emp_id == 80 || $emp_id == 22 || $emp_id == 59) { // dir Rey or dir Olie or asec Yo, respectively
						$dbm_id_loc	= $usecs_id; // usec's ID
					} else if ( $data[0]->DBM_Pap_id == 1 && $data[0]->Division_id==0 ) { // head of OC
						$dbm_id_loc	= $chairs_id; // chairmans ID
					} else if ($emp_id == 1441) { // the USEC's ID
						$dbm_id_loc	= $chairs_id; // chairmans ID
					}
					
					//$d_chief_email = 0;
					//$d_chief_id    = 0;
					
					$d_chief_email = $d_dbm_email  = $this->Globalproc->gdtf("employees",["employee_id"=>$dbm_id_loc],["email_2"])[0]->email_2;
					$d_chief_id    = $d_dbm_id 	   = $dbm_id_loc;
				} else {
					$d_chief_email = $details['division_chief_email'];
					$d_chief_id    = $details['division_chief_id'];
					
					$d_dbm_email   = $details['dbm_chief_email'];
					$d_dbm_id 	   = $details['dbm_chief_id'];
				}
				
				// division chief
					$division_chief_email = $d_chief_email;
					$division_chief_id    = $d_chief_id;
				// end division chief
					
				// last approving official 
					$dbm_email 			  = $d_dbm_email;
					$dbm_id 			  = $d_dbm_id;
				// end last approving official

				
			// end getting the officers from javascript
			
			// checkexact variable 
				$checkexact = [
						"employee_id"			=> $emp_id,
						"type_mode"			    => $details['_for'],
					//	"attachments"			=> null,
						"date_added"			=> date("M d, Y"),
						"is_approved"			=> 0,
						"aprroved_by_id"		=> 0,
						"grp_id"				=> $group_id
					];
			// checkexact variable
			
			// for SPL
				if ($details['_for'] == "LEAVE" && $details['leave_type'] == 4) {
					$spl_count = $this->Globalproc->get_spl_count($emp_id);
					
						if ($spl_count >= 3) {
							// spl has been exhausted 
							return;
						}
				}
			// end for SPL
			
			// used in OB :: mark ob 3
				$leave_details = [
					"typemode"			=> "OB",
					//	"leave_value"		=> null,
					//	"value" 			=> null,
					"date_inclusion"	=> null,
					"no_days_applied"	=> null
				];
				
				$themonth = null;
				$theday   = null;
				$theyear  = null;
			// end ob
			
			for ($i = 0; $i <= count($details['dates'])-1; $i++) { // start for
				if ($details['_for'] == "LEAVE") {
					// for leave and SPL
							$checkexact['leave_id']   = $details['leave_type'];
						//	$checkexact['leave_name'] = $leave_data[$details['leave_type']];
						
						$leavespecifics 		    = $details['leave_specific'];
						$checkexact['reasons']	    = $details['specify'];
							
						// checkdates 
						$checkexact['checkdate']    = date("M d,  Y", strtotime($details['dates'][$i]));
							
				} else if ($details['_for'] == "PS") {
					$checkexact['ps_type']      = $details['leave_type'];
					$checkexact['checkdate']    = date("n/j/Y", strtotime($details['dates'][$i]));
					$checkexact['ps_guard_id']  = null;
					$checkexact['reasons']	    = $details['specify'];
				} else if ($details['_for'] == "PAF") {
					$checkexact['checkdate']  		  = date("n/j/Y", strtotime($details['dates'][$i]));
					$checkexact['type_mode_details']  = $details['halfwhole'];
					$checkexact['remarks']    		  = $details['leave_specific'];
					$checkexact['reasons']	  		  = $details['specify'];
					$checkexact['time_in']	  		  = $details['timein'];
					$checkexact['time_out']	  		  = $details['timeout'];
				} else if ($details['_for'] == "OB") { // mark ob 2
					$checkexact['checkdate']  		  = date("n/j/Y", strtotime($details['dates'][$i]));
					$checkexact['type_mode_details']  = $details['leave_specific'];
					$checkexact['remarks']	   		  = $details['specify'];
					
					$checkexact['is_approved']		  = 1;
					$checkexact['aprroved_by_id']     = $division_chief[0]->employee_id;
					
						$theday   = date("d", strtotime($details['dates'][$i]));
						$theyear  = date("Y", strtotime($details['dates'][$i]));
										
						if ( $themonth == null ) {
							$themonth 						 = date("M", strtotime($details['dates'][$i]));
							$leave_details['date_inclusion'] = $themonth ." ".$theday;
							
							if ($i != count($details['dates'])-1) {
								$leave_details['date_inclusion'] .= "";
							}
							
						} else {
							if ( $themonth != date("M", strtotime($details['dates'][$i])) ) {
								$themonth = date("M", strtotime($details['dates'][$i]));
								$leave_details['date_inclusion'] .= " || ". $themonth . " ";
							}

							$leave_details['date_inclusion'] .= "-".$theday."";

							if ($i != count($details['dates'])-1) {
								$leave_details['date_inclusion'] .= "";
							}
						}
						
				} else if ($details['_for'] == "CTO") {
					$checkexact['checkdate']  = date("n/j/Y", strtotime($details['dates'][$i]));
					
					$start = "8";
					$add   = $details['hours'];
					// $ampm  = "AM";
					
					if ($add > 4) {
						$add++;
						// $ampm  = "";
					}
					
					$end   = $start+$add;
					
					// $checkexact['time_in']	  = $details['timein'];
					// $checkexact['time_out']	  = $details['timeout'];
					
					$checkexact['time_in']	  = $start.":00 AM";
					
					$endtime 				  = $end.":00 ";
					
					$checkexact['time_out']	  = date("h:i A", strtotime($endtime));
					
					/*
					if (count($details['dates']) > 1) {
						$checkexact['time_in']	  = "8:00 AM";
						$checkexact['time_out']	  = "5:00 PM";
					}
					*/
					
					// $details['hours'] :: 
					
					$checkexact['remarks']	  = $details['cto_remarks'];
					
					//$checkexact['aprroved_by_id']     = $division_chief[0]->employee_id;
				}
				
				// save to checkexact ===============================================================================
				/*|*/	$ret 	  = $this->Globalproc->__save("checkexact",$checkexact);
				/*|*/	// get the recent inserted data
				/*|*/		$recent   = $this->Globalproc->getrecentsavedrecord("checkexact", "exact_id");
				/*|*/		$exact_id = $recent[0]->exact_id;
				/*|*/	// end getting recent inserted data
				// save to checkexact ===============================================================================	
						
						// if leave =========================================================================================
						/*|*/	if ($details['_for'] == "LEAVE") {
						/*|*/		// save to leave logs variable
						/*|*/			$var_leave_logs = [
						/*|*/					"exact_id"						=> $exact_id,
						/*|*/					"no_days_applied"				=> 1,
						/*|*/					"leave_application_details"		=> $leavespecifics,
						/*|*/					"grp_id"						=> $group_id
						/*|*/					];
						/*|*/					
						/*|*/		if ($details['leave_type'] == "4") { // spl
						/*|*/			$var_leave_logs[$details['specify']] = TRUE;
						/*|*/		}
						/*|*/
						/*|*/		// end variable 
						/*|*/		
						/*|*/		// start saving
						/*|*/			$ret 	 = $this->Globalproc->__save("checkexact_leave_logs",$var_leave_logs);
						/*|*/		// end saving to leave logs
						/*|*/	}
						// end if leave =====================================================================================
				
				// for approvals
					// "division_chief_id"					=> $division_chief[0]->employee_id,
					// "leave_authorized_official_id"		=> $last_approving[0]->employee_id,
					$for_approvals = [
								"exact_id"							=> $exact_id,			
								"division_chief_id"					=> $division_chief_id,
								"leave_authorized_official_id"		=> $dbm_id,
								"hrmd_approved_id"					=> 52, // maam Ces Trino
								"hrmd_date"							=> date("m/d/Y"),
								"grp_id"							=> $group_id
					];
					
				//if ($emp_id == $division_chief[0]->employee_id) {
				//	if ($emp_id == $division_chief_id) {
				
					if ( $this->Globalproc->is_chief("division", $emp_id)){ 
						$for_approvals["division_chief_is_approved"] = 1;
					}
					
					if ( $no_chiefs[0]->Division_id == 0 && !$this->Globalproc->is_chief("director",$emp_id) ){ // or employees reporting directly to directors
						$for_approvals["division_chief_is_approved"] = 1;
					}
					
				//	if ($emp_id == $last_approving[0]->employee_id) {
				//	if ($emp_id == $dbm_id) {
					if ( $this->Globalproc->is_chief("director",$emp_id) ) {
						$for_approvals["division_chief_is_approved"]   = 1;
						
						/*
						$fordir 	 		 = "select employee_id from employees where DBM_Pap_id = '1' and is_head = '1' and Division_id = '0'";
						$fordir_data 		 = $this->Globalproc->__getdata($fordir);
						*/
			
						$lao_id = null;
						if ($emp_id == 27) { // doc Cha
							$lao_id	= $chairs_id; // chairmans ID
						} else if ( $emp_id == 80 || $emp_id == 22 || $emp_id == 59) { // dir Rey or dir Olie or asec Yo, respectively
							$lao_id	= $usecs_id; // usec's ID
						} else if ( $data[0]->DBM_Pap_id == 1 && $data[0]->Division_id==0 ) { // head of OC
							$lao_id	= $chairs_id; // chairmans ID
						}else if ($emp_id == 1441) { // the USEC's ID
							$lao_id	= $chairs_id; // chairmans ID
						}
						
						$for_approvals["leave_authorized_is_approved"] = 0;
						$for_approvals["leave_authorized_official_id"] = $lao_id;
						
					}
					
					if ($details['_for'] == "OB" ) {
						$for_approvals["division_chief_is_approved"]   = 1;
						$for_approvals["leave_authorized_is_approved"] = 1;
					}
					
				// end for approvals
			
				// start PAF
					if ($details['_for'] == "PAF") {
						$for_approvals["paf_recorded_by_id"] = $emp_id;
						$for_approvals["paf_recorded_by_is_approved"]		= 1;
						
					/*	
					//	if ($emp_id == $division_chief[0]->employee_id) {
						if ( $this->Globalproc->is_chief("division", $emp_id) ) {
							//$for_approvals["paf_approved_by_id"] = $division_chief[0]->employee_id;
							$for_approvals["paf_approved_by_id"] = $division_chief_id;
						}
					*/
					
					// last approving body 
						$for_approvals["paf_approved_by_id"] = $dbm_id;
					// end last approving body
					
					//	if ($emp_id == $last_approving[0]->employee_id) {
						if ( $this->Globalproc->is_chief("director",$emp_id) ) {
							// $for_approvals["paf_approved_by_id"] = $dbm_id;
							/*
							$fordir 	 		 = "select employee_id from employees where DBM_Pap_id = '1' and is_head = '1' and Division_id = '0'";
							$fordir_data 		 = $this->Globalproc->__getdata($fordir);
							*/
							$lao_id = null;
							if ($emp_id == 27) { // doc Cha
								$lao_id	= 1443; // chairmans ID
							} else if ( $emp_id == 80 || $emp_id == 22 || $emp_id == 59) { // dir Rey or dir Olie or asec Yo, respectively
								$lao_id	= 1441; // usec's ID
							} else if ( $data[0]->DBM_Pap_id == 1 && $data[0]->Division_id==0 ) { // head of OC
								$lao_id	= 1443; // chairmans ID
							}
						
							$for_approvals["paf_is_approved"] 	 = 0;
							$for_approvals["paf_approved_by_id"] = $lao_id ;
						}
					
					//	$for_approvals["paf_recorded_remarks"]				= $details['leave_specific'],
					//	$for_approvals["paf_recorded_date"]					= date("m "),
					//	$for_approvals["paf_approved_by_id"]				= 0,
					//	$for_approvals["paf_is_approved"]					= false,
					//	$for_approvals["paf_approved_remarks"]				= NULL,
					//	$for_approvals["paf_date"]							= NULL,
					//	$for_approvals["hrmd_approved_id"]					= 29,
					//	$for_approvals["hrmd_is_approved"]					= 1
							
					}
				// end PAF
			
				// start PS 
					if ($details['_for'] == 'PS') {
						$for_approvals["division_chief_is_approved"] = null;
					}
				// end PS
				
				// SAVE: for approvals
				$ret 	 	 = $this->Globalproc->__save("checkexact_approvals",$for_approvals);
				// end approvals
				
				for ($m=0;$m<=count($shifts)-1;$m++) {
					$chk_logs 	 = date("n/j/Y", strtotime($checkexact['checkdate']));
					$checktime__ = $chk_logs." ".$shifts[$m]['checktime'];
					// for checkexact_logs 
						
							$holidays = $this->attendance_model->getholidays( date("m/01/Y",strtotime($checkexact['checkdate'])) , date("m/t/Y",strtotime($checkexact['checkdate'])) );
					
							if ( strtoupper(date("D", strtotime($chk_logs))) == "MON" &&
								$this->attendance_model->checkifholiday($holidays, date("n/j/Y",strtotime($chk_logs))) == false ) {
									
									if ($details['_for'] != "PS") {
										$checktime__ = $chk_logs; //.' '."8:00 AM";
										
										if ($shifts[$m]['shift_type'] == "AM_START") {
											$checktime__ .= " 8:00 AM";
										} else if ($shifts[$m]['shift_type'] == "AM_END") {
											$checktime__ .= " 12:00 PM";
										} else if ($shifts[$m]['shift_type'] == "PM_START") {
											$checktime__ .= " 1:00 PM";
										} else if ($shifts[$m]['shift_type'] == "PM_END") {
											$checktime__ .= " 5:00 PM";
										} 
									}
							}
							
							if ( strtoupper(date("D",strtotime($chk_logs))) == "TUE" && 
								$this->attendance_model->checkifholiday($holidays, date("n/j/Y",strtotime($chk_logs))) == false &&
								$this->attendance_model->checkifholiday($holidays, date('n/j/Y', strtotime('-1 day', strtotime($chk_logs)))) == true ) {
									
									if ($details['_for'] != "PS") {
										$checktime__ = $chk_logs; //.' '."8:00 AM";
										
										if ($shifts[$m]['shift_type'] == "AM_START") {
											$checktime__ .= " 8:00 AM";
										} else if ($shifts[$m]['shift_type'] == "AM_END") {
											$checktime__ .= " 12:00 PM";
										} else if ($shifts[$m]['shift_type'] == "PM_START") {
											$checktime__ .= " 1:00 PM";
										} else if ($shifts[$m]['shift_type'] == "PM_END") {
											$checktime__ .= " 5:00 PM";
										}
									}
							} 

							if ( strtoupper(date("D",strtotime($chk_logs))) == "WED" && 
								$this->attendance_model->checkifholiday($holidays, date("n/j/Y",strtotime($chk_logs))) == false &&
								$this->attendance_model->checkifholiday($holidays, date('n/j/Y', strtotime('-1 day', strtotime($chk_logs)))) == true && 
								$this->attendance_model->checkifholiday($holidays, date('n/j/Y', strtotime('-2 day', strtotime($chk_logs)))) == true) {
									
									if ($details['_for'] != "PS") {
										$checktime__ = $chk_logs; //.' '."8:00 AM";
										
										if ($shifts[$m]['shift_type'] == "AM_START") {
											$checktime__ .= " 8:00 AM";
										} else if ($shifts[$m]['shift_type'] == "AM_END") {
											$checktime__ .= " 12:00 PM";
										} else if ($shifts[$m]['shift_type'] == "PM_START") {
											$checktime__ .= " 1:00 PM";
										} else if ($shifts[$m]['shift_type'] == "PM_END") {
											$checktime__ .= " 5:00 PM";
										}
									}
									
							} 

							if ( strtoupper(date("D",strtotime($chk_logs))) == "THU" && 
								$this->attendance_model->checkifholiday($holidays, date("n/j/Y",strtotime($chk_logs))) == false &&
								$this->attendance_model->checkifholiday($holidays, date('n/j/Y', strtotime('-1 day', strtotime($chk_logs)))) == true && 
								$this->attendance_model->checkifholiday($holidays, date('n/j/Y', strtotime('-2 day', strtotime($chk_logs)))) == true &&
								$this->attendance_model->checkifholiday($holidays, date('n/j/Y', strtotime('-3 day', strtotime($chk_logs)))) == true	) {
									
									if ($details['_for'] != "PS") {
										$checktime__ = $chk_logs; //.' '."8:00 AM";
										
										if ($shifts[$m]['shift_type'] == "AM_START") {
											$checktime__ .= " 8:00 AM";
										} else if ($shifts[$m]['shift_type'] == "AM_END") {
											$checktime__ .= " 12:00 PM";
										} else if ($shifts[$m]['shift_type'] == "PM_START") {
											$checktime__ .= " 1:00 PM";
										} else if ($shifts[$m]['shift_type'] == "PM_END") {
											$checktime__ .= " 5:00 PM";
										}
									}
							} 

							if ( strtoupper(date("D",strtotime($chk_logs))) == "FRI" && 
								$this->attendance_model->checkifholiday($holidays, date("n/j/Y",strtotime($chk_logs))) == false &&
								$this->attendance_model->checkifholiday($holidays, date('n/j/Y', strtotime('-1 day', strtotime($chk_logs)))) == true && 
								$this->attendance_model->checkifholiday($holidays, date('n/j/Y', strtotime('-2 day', strtotime($chk_logs)))) == true &&
								$this->attendance_model->checkifholiday($holidays, date('n/j/Y', strtotime('-3 day', strtotime($chk_logs)))) == true && 
								$this->attendance_model->checkifholiday($holidays, date('n/j/Y', strtotime('-4 day', strtotime($chk_logs)))) == true ) {
									
									if ($details['_for'] != "PS") {
										$checktime__ = $chk_logs; //.' '."8:00 AM";
										
										if ($shifts[$m]['shift_type'] == "AM_START") {
											$checktime__ .= " 8:00 AM";
										} else if ($shifts[$m]['shift_type'] == "AM_END") {
											$checktime__ .= " 12:00 PM";
										} else if ($shifts[$m]['shift_type'] == "PM_START") {
											$checktime__ .= " 1:00 PM";
										} else if ($shifts[$m]['shift_type'] == "PM_END") {
											$checktime__ .= " 5:00 PM";
										}
									}
							} 
					
						$checkexact_logs = [
							"exact_id"		  => $exact_id,
							"checktime"		  => $checktime__,
							"checktype"		  => $shifts[$m]['checktype'],
							"shift_type"	  => $shifts[$m]['shift_type'],
							"modify_by_id"	  => 0,
							"is_modify"		  => 0,
							"is_delete"		  => 0,
							"date_added"	  => date("M d Y h:iA"),
							"date_modify"	  => 0,
							"is_bypass"		  => 0
						];
						$ret = $this->Globalproc->__save("checkexact_logs",$checkexact_logs);
					// end for checkexact_logs
				}
			
				if ($ret == false) {
					return false;
				}
				
				//echo $ret;
				
				
			
			}  // end for 
			
			// OB :: saving to employee_leave_credits table
			// mark ob
					if ($details['_for'] == "OB") {
						$leave_details['date_inclusion']  .= ", ".$theyear;
					//	$leave_details['no_days_applied'] = count($details['dates']);
						
						$ret = $this->Globalproc->calc_leavecredits($emp_id, $group_id, $leave_details);
					}
			// end OB
				
			echo json_encode( ["result"=>$ret,"grp_id"=>$group_id,"empid"=>$emp_id]);
			
		}
		
		public function test() {
			$this->load->model("v2main/Globalproc");
			$details = ["to" 		=> "alvinjay.merto@minda.gov.ph",
			            "message"   => "hello world",
						"subject"   => "subject",
						"from"		=> "testing"];
			$ret = $this->Globalproc->sendtoemail($details);
			
			var_dump($ret);
		}
		
		public function get_applied_leaves() {
			// emp id
			$this->load->model("v2main/Globalproc");
			$this->load->model("Globalvars");
			
			$empid = $this->Globalvars->employeeid;
		//	$empid = $this->input->post("info");
		//	$empid = $empid["empid"];
			
			$sql 	= "select 
							exact_id, 
							type_mode,
							checkdate, 
							is_approved,
							grp_id,
							type_mode_details,
							leaves.leave_name,
							employee_id
					   from checkexact 
					   LEFT JOIN leaves on checkexact.leave_id = leaves.leave_id
					   where employee_id = '{$empid}' and type_mode <> 'AMS' and type_mode <> 'CA'"; //  and is_approved <> '2'
			$leaves = $this->Globalproc->__getdata($sql);
			/*
			$leaves = $this->Globalproc->gdtf("checkexact",['employee_id'=>$empid,"conn" => "and","is_approved"=>0],
											 ["exact_id",
											  "type_mode",
											  "checkdate",
											  "is_approved"]);
			*/
		//	var_dump($leaves);
		//	return;
			
			echo json_encode($leaves);

		}
		
		public function get_applied_ot() {
			$this->load->model("v2main/Globalproc");
			$this->load->model("Globalvars");
			
			$empid = $this->Globalvars->employeeid;
			
			$sql = "select * from checkexact_ot where employee_id = '{$empid}'";
			
			$ots = $this->Globalproc->__getdata($sql);
			
			echo json_encode($ots);
		}
		
		public function get_leave() {
			// grp_id 
			
			$this->load->model("v2main/Globalproc");
			$this->load->model("Globalvars");
		
			$empid = $this->Globalvars->employeeid;
			
			$grp_id = $this->input->post("info");
			$grp_id = $grp_id['group_id'];
			
			$sql 	= "select * from checkexact as ce 
					   JOIN checkexact_approvals as cea 
					   on ce.exact_id = cea.exact_id
					   where ce.grp_id = '{$grp_id}'";
					   
			$ret    = $this->Globalproc->__getdata($sql);
			
			$division = $this->Globalproc->gdtf("employees",
											   ["employee_id"=>$ret[0]->division_chief_id],
											   ["f_name","email_2","employee_id"]);
											   
			$dbm  	  = $this->Globalproc->gdtf("employees",
												["employee_id"=>$ret[0]->leave_authorized_official_id],
												["f_name","email_2","employee_id"]);
			
			echo json_encode( ["data"=>$ret, "division"=>$division, "dbm"=>$dbm] );
		}
		
		public function update_the_leave() {
			/*
			$this->load->model("v2main/Globalproc");
			// called from an ajax request
				// needs 
					// grp_id
			
			$details = $this->input->post("info");
			$details = $details['updates'];
			
			// LEAVE 
			// PS 
			// OB 
			// PAF 
			
			$spec_label = $details['leave_specific'];
			$spec_val   = $details['specify'];
			$type 		= $details['leave_type'];

			$grp_id 	= $details["grp_id"];
			
			$sql = "update checkexact set ";
			switch($details['_for']) {
				case "LEAVE":
					
					break;
				case "PS":
					
					break;
				case "OB":
					
					break;
				case "PAF":
				$time_in  = $details['timein'];
				$time_out = $details['timeout'];
				
					$sql .= "remarks = '{$spec_label}'";
						$sql .= ",";
					$sql .= "reasons = '{$spec_val}'";
						$sql .= ",";
					$sql .= "time_in = '{$time_in}'";
						$sql .= ",";
					$sql .= "time_out = '{$time_out}'";
						
					$sql .= " where grp_id = '{$grp_id}'";
					break;
			}
	//		$sql = "update checkexact set remarks = 'approve please',reasons = 'approve please',time_in = '08:00 AM',time_out = '12:00 PM' where grp_id = '2b7794-94bb66'";
			$ret = $this->Globalproc->run_sql($sql);
			echo json_encode($ret);
			*/
		}
		
		public function get_grp_id() {
			$this->load->model("v2main/Globalproc");
			
			$type = $this->input->post("info");
			$id   = $type['id'];
			
			$sql = "select 
						ce.grp_id
					from checkexact as ce 
					JOIN employee_leave_credits as cll on ce.grp_id = cll.grp_id
					";
		}
		
		public function cancel_leave() {
			$this->load->model("v2main/Globalproc");
			// called from an ajax request 
			
			$grp_id    = $this->input->post("info");
			$grp_id    = $grp_id['group_id'];
			
			//$grp_id    = "ffc314-35246e";
			//$grp_id    = "1022";
			
			$tables    = ["checkexact",
						  "checkexact_approvals",
						  "checkexact_leave_logs",
						  "checkexact_logs",
						  "employee_leave_credits"]; 
						  // "checkexact_ot"
			
			$exact_ids = $this->Globalproc->gdtf("checkexact",['grp_id'=>$grp_id],['exact_id','type_mode']);
			
			if (count($exact_ids) > 0){

				$ret = true;
				for($i = 0; $i <= count($tables)-1; $i++) {
					$sql = null;
					// if ($i != count($tables)-1) {
					if ( $tables[$i] == "checkexact_logs" ) {
						for ($a = 0; $a <= count($exact_ids)-1; $a++) {
							$sql = "DELETE FROM {$tables[$i]} where exact_id = '{$exact_ids[$a]->exact_id}'";
							
							if ($ret == false) {
								// return false;
							} else {
								$ret = $this->Globalproc->run_sql($sql);
							}
							
						}
					} else {
						if ($tables[$i]=="employee_leave_credits") {
							$sql = "DELETE from {$tables[$i]} where exact_id = '{$grp_id}'";
						} else if ($tables[$i]=="checkexact_ot") {
							// check for cancellation of OT	
							$sql = "DELETE from checkexact_ot where checkexact_ot_id = '{$grp_id}'";
						} else {
							$sql = "DELETE from {$tables[$i]} where grp_id = '{$grp_id}'";
						}
						
						if ($ret == false) {
							// return false;
						} else {
							$ret = $this->Globalproc->run_sql($sql);
						}
					}
				}
			} else { 
				
				// cancellation of OT
				// not found in the checkexact,
				// meaning it is an OT, an application stored on a different table named checkexact_ot
				$ot_tables = ["checkexact_ot","employee_leave_credits","employee_ot_credits","ot_accom"];
				$ot_fields = ["checkexact_ot_id","exact_id","exact_ot","ot_exact_id"];
				for ($i = 0 ; $i <= count($ot_tables)-1; $i++) {
					$sql = "DELETE from {$ot_tables[$i]} where {$ot_fields[$i]} = '{$grp_id}'";
					$ret = $this->Globalproc->run_sql($sql);
				}
			}
			
			echo json_encode($ret);
		}
		
		public function test_date() {
			$this->load->model("v2main/Globalproc");
				
				/*
				$datetime1 = new DateTime('9:30 AM');
				$datetime2 = new DateTime('12:00 PM');
				$interval = $datetime1->diff($datetime2);
				$a =  $interval->format('%h').":".$interval->format('%i');
				
				echo "<br/>";
				
				$datetime1 = new DateTime('10:00 AM');
				$datetime2 = new DateTime('3:00 PM');
				$interval = $datetime1->diff($datetime2);
				$b =  $interval->format('%h').":".$interval->format('%i');
				
				$secs = strtotime($b)-strtotime("00:00:00");
				$result_hour = date("H",strtotime($a)+$secs);
				$result_mins = date("i",strtotime($a)+$secs);
					
				$x = $this->Globalproc->convert($result_hour, "h");
				$z = $this->Globalproc->convert($result_mins, "m");
				echo $x+$z;
				echo "<br/>=============<br/>";
				
				echo $result_hour;
				echo ":";
				echo $result_mins;
				echo "<br/>";
				echo $a."|".$b;
				*/
				/*
				$datetime1 = new DateTime('10:00 AM');
				$datetime2 = new DateTime('3:00 PM');
				$interval = $datetime1->diff($datetime2);
				$a =  $interval->format('%h').":".$interval->format('%i');
				echo $a;
			
			$values = [];
			$a = $this->Globalproc->saveto_ot($values);
			echo "hello".$a;
			*/
			
			$s = "3/11/2018 9:00:00 AM";
			$d = "3/11/2018 3:00:00 PM";
			
			echo date("h:i A", strtotime($d));
			echo "<br/>";
			echo date("h:i A", strtotime($s));
		}
		
		
		public function fileot() {
			
			$values = $this->input->post("info");
			$values = $values['details'];
			
			$this->load->model("Globalvars");
			$this->load->model("v2main/Globalproc");
			
			$emp_id  = $this->Globalvars->employeeid;
			
			/*
			$values = [
				"am_in" => "8:00 AM",
				"am_out" => "10:00 AM",
				"calc_elc" => true,
				"dates" => [ "11/9/2018" ],
				"dbm_chief_id" => 0,
				"division_chief_id" => 0,
				"empid" => 33,
				"isam" => true,
				"mult" => "1",
				"remarks_ot" => "",
				"tasktobedone" => "",
				"tasktype" => 1.5,
				"timein" => "8:00 AM",
				"timeout" => "10:00 AM",
				"typemode" => "OT"
			];
			*/
			
			if (isset($values['empid'])) {
				$emp_id = $values["empid"];
			}
			
			$checkdate  = $values['dates'][0];
			
			$task_done  = $values['tasktobedone'];
			$ot_type    = $values['tasktype'];
			
			$remarks_ot = $values['remarks_ot'];
			
			$timein     = $values['timein'];
			$timeout    = $values['timeout'];
				
			// ot requested time in and out and date added
				$requested_time_in  = $checkdate." ".$timein;
				$requested_time_out = $checkdate." ".$timeout;
				$date_added 		= date("m/d/Y h:i:s A");
			// end 
			
			// division chief 
				$div_id = $values['division_chief_id'];
			// end division 
			
			// dbm 
				$dbm_id = $values['dbm_chief_id'];
			// end dbm 
			//echo json_encode($values);
			
			// get the personal information 
			$personal = $this->Globalproc->gdtf("employees",["employee_id"=>$emp_id],["Division_id","area_id"]);
			
			if ($personal[0]->Division_id == 0) { // can be a director level or an employee directly reporting to a director
				$div_id = 0;
			} else if ( $this->Globalproc->is_chief("division", $emp_id) ) { // division chief level
				$div_id = 0;
			} else if ( $this->Globalproc->is_chief("director", $emp_id) ) { // director level
				$div_id = 0;
			} else if ($personal[0]->area_id != 1) {
				
			}
			
			$details = [
				"employee_id"				=> $emp_id,
				"ot_checkdate"				=> $checkdate,
				"ot_task_done"				=> $task_done,
				"ot_remarks"				=> $remarks_ot,
				"ot_requested_time_in"		=> $requested_time_in,
				"ot_requested_time_out"		=> $requested_time_out,	
				"date_added"				=> $date_added,
				"is_ot_type"				=> $ot_type,
				"div_chief_id"				=> $div_id,
				"act_div_chief_id"			=> $dbm_id,
				"act_div_a_chief_id"		=> $dbm_id
			];
			
			// if ot_type is 1
				if ($ot_type == 1) {
					if (isset($values['reason_rw'])) {
						$details["ot_reasons_if_rw"]	= $values['reason_rw'];
					} else {
						$details["ot_reasons_if_rw"]    = "inputted by the HR";
					}
				}
			// end
			
				if ( $this->Globalproc->is_chief("director", $emp_id) || $values['dbm_chief_id'] == 0) { // director level
					/*
					$details["div_date_approved"]		= $date_added;
					$details["act_div_is_approved"]		= 1;
					$details["div_is_approved"] 		= 1;
					$details["act_div_date_approved"] 	= $date_added;
					$details["act_div_a_is_approved"] 	= 1;
					$details["act_div_a_date_approved"] = $date_added;
					*/
					$details["div_is_approved"] 	    = 1;
				} else if ( $this->Globalproc->is_chief("division", $emp_id) || $values['division_chief_id'] == 0) { // division chief level
					$details["div_is_approved"] 	    = 1;
				} else if ( $personal[0]->Division_id == 0 ) {
					$details["div_is_approved"] 	    = 1; // can be a director or an employee directly reporting to a director
				}
				
				
			// echo json_encode($details);
			
			$ret = $this->Globalproc->__save("checkexact_ot",$details);
			$ot_exactid = null;
			
	
			if ($ret) {
				$ot_exactid = $this->Globalproc->getrecentsavedrecord("checkexact_ot","ot_exact");
				$ot_exactid = $ot_exactid[0]->ot_exact;
			}
			
			// called in leave management throught an ajax request
			if (isset($values['calc_elc'])) {
				$vals = [
					"dates" 			  => [$checkdate],
					"empid"			      => $emp_id,
					"mult"				  => $values['mult'],
					"typemode"			  => "OT",
					"exact_ot"			  => $ot_exactid
				];
				
				if ( isset($values['isam']) ) {
					$vals['am_in']  = $values['am_in'];
					$vals['am_out'] = $values['am_out'];
					$vals['isam']	= true;
				}
				
				if ( isset($values['ispm']) ) {
					$vals['pm_in']  = $values['pm_in'];
					$vals['pm_out'] = $values['pm_out'];
					$vals['ispm']	= true;
				}
				
				$saved = $this->Globalproc->saveto_ot($vals);
			}
			
			echo json_encode(["empid"=>$emp_id,"exactot"=>$ot_exactid]);
	
		}
		
		public function kindofleave() {
			
			$this->load->model("v2main/Globalproc");
			
			$info   = $this->input->post("info");
			$grpid  = $info['grpid'];
			
		//	$grpid = 17;
			// if not found in the checkexact table 
				// use OT form 
				// return OT ping
			// if not 
				// use appropriate form
				// return the type of form 
			
		//	$sql = "select * from checkexact as ce where grp_id = '{$grpid}'"; // 2245
		//	$grpid = 2245;
			$d   = $this->Globalproc->gdtf("checkexact",['grp_id'=>$grpid],['type_mode','exact_id','is_approved']);
			
			$returnwhat  = null;
			$exact_id    = null;
			$is_approved = null;
			if ( count($d) == 0 ) {
				$returnwhat = "OT";
				$exact_id   = $grpid;
			} else {
				$returnwhat = $d[0]->type_mode;
				$exact_id   = $d[0]->exact_id;
				$is_approved = $d[0]->is_approved;
			}
			
			echo json_encode( [$returnwhat,$exact_id] );
			
		}
		
		public function saveto_OT() {
			$this->load->model("v2main/Globalproc");
			
			$details = $this->input->post("info");
			$details = $details['cocdet'];
			
			/*
			$details = [
				"empid"   => 33,
				"cocval"  => "00:24:50",
				"thedate" => date("M d, Y")
			];
			*/
			
			// get the previous remaining balance
			$sql = "select * from employee_ot_credits 
					where elc_otcto_id = (select max(elc_otcto_id) 
                      from employee_ot_credits as et 
                      where emp_id = '{$details['empid']}')"; //  and credit_type='OT' or credit_type ='FB'
					  
			$data 	 = $this->Globalproc->__getdata($sql);
			
			if (count($data) >= 1){
				$rem_cdt = $data[0]->total_credit;
			} else {
				$rem_cdt = 0;
			}
			
			// dd:hh:mm
			// time format 
			
			// $details['cocval']
			
			// 00:24:50
			$coc_details = $details['cocval'];

			list($d, $h, $m) = explode(':', $coc_details);
			
			if (strlen($m)==1) { $m = "0".$m; }
			if (strlen($h)==1) { $h = "0".$h; }
			if (strlen($d)==1) { $d = "0".$d; }	
			
			$seconds 	 = ((int)$d * 86400) + ((int)$h * 3600) + ((int)$m * 60);
			
			$totalcredit = $rem_cdt + $seconds;
			$details = [
				"date_of_application" 	=> date("m/d/Y", strtotime($details['thedate'])),
				"total"					=> $details['cocval'],
				"total_credit"			=> "{$totalcredit}",
				"emp_id"				=> $details['empid'],
				"credit_type"			=> "FB"
			];
			
			$issave = $this->Globalproc->__save("employee_ot_credits",$details);
			
			echo json_encode(["saved"=>$issave]);
		}
		
		/*
		public function ledger($empid = '') {
			$data['noemp']	= null;
			if ($empid == '') {
				redirect(base_url(),"refresh");
			}
			
			$this->load->model("v2main/Globalproc");
			
			$ledger 						 = $this->Globalproc->getleavecredits($empid);
			$data['employees']				 = $this->Globalproc->getplantilla();
			$data['ledger'] 				 = $ledger;
				
			$data['title']					 = "| Ledger";
			
			// old leave management program :: used in the recording of leave
				$data['headscripts']['js'][]	 = base_url()."v2includes/js/leavemgt.procs.js";
			
			// new leave mananagement program :: used in the recalling of leave 
				$data['headscripts']['js'][]	 = base_url()."v2includes/js/newleavemgt.procs.js";
			
			$data['headscripts']['style'][]  = base_url()."v2includes/style/leavemgt.style.css";
			$data['main_content'] 			 = "v2views/displedger";
			
			$this->load->view('hrmis/admin_view',$data);
			
		}
		*/
		
		public function checkforexact_ids() {
			$grp_id  = $this->input->post("info")['grpid'];
			
			$this->load->model("v2main/Globalproc");
			
			// $grp_id = "dd52e2-0c5784";
			$ce = $this->Globalproc->gdtf("checkexact",["grp_id"=>$grp_id],"*");
			
			$b = array_map(function($a){
				$a->checkdate   = date("F d, Y", strtotime($a->checkdate)); //&nbsp; <i class='fa fa-share' aria-hidden='true'></i> &nbsp; 
				$a->is_approved = ($a->is_approved == 1)?" <span class='apprvd'> Approved </span> ":null;
				return $a;
			},$ce);
			
			//var_dump($b);
 			echo json_encode( $b );
		}
		
		public function recallthis() {
			// requires
				// elc id
				// checkexact id 
				
			// optional 
				// group id 
			
			// get from checkexact, checkexact_logs,
			// get from employee_leave_credits
			
			$details = $this->input->post("info");
			$elc_id  = $details["elcid"];
			$exactid = $details["exactid"];
			$grpid   = $details["grpid"];
			
		//	$elc_id  = 5599;
		//	$exactid = null;
		//	$grpid   = 2844;
			
			$this->load->model("v2main/Globalproc");
			
			$del_statements = [
				"DELETE from checkexact where exact_id = '{$exactid}'",				// checkexact 
				"DELETE from checkexact_logs where exact_id = '{$exactid}'",		// checkexact_logs 
				"DELETE from employee_leave_credits where elc_id = '{$elc_id}'"		// employee_leave_credits
			];
			
			$ret = false;
				for($i = 0 ; $i <= count($del_statements)-1 ; $i++) {
					if ($exactid != null) {
						//echo $del_statements[$i]; ;
						$ret = $this->Globalproc->run_sql($del_statements[$i]);
					} else {
						if ($i > 1) {
							// echo $del_statements[$i];
							$ret = $this->Globalproc->run_sql($del_statements[$i]);
						}
					}
				}
			echo json_encode($ret);
		}
		
		public function updatethefield() {
			$info   = $this->input->post("info");
			$field  = $info["field"];
			$value  = $info["value"];
			$elc    = $info["elc"];
			
			$update = [$field   => $value];
			$where  = ["elc_id" => $elc];
			
			$this->load->model("v2main/Globalproc");
			$isupdate = $this->Globalproc->__update("employee_leave_credits",$update,$where);
			
			echo json_encode($isupdate);
		}
		
		public function testdate() {
			$comp_month = date("M. 1-t, Y", strtotime("-1 month"));
			$addto 	    = date("M. t, Y", strtotime("+1 month"));
			
			echo $comp_month;
				echo "<br/>";
			echo $addto;
		}

		public function request_recall() {
			
		}
	
	}

