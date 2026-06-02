<?php 
	
	class Dtr extends CI_Controller {
		
		public function __construct() {
			parent::__construct();
		}
		
		public function approval() {
			//$info["username"] = urldecode($this->uri->segment(5));
			$info["username"] = iconv("UTF-8", "Windows-1252", urldecode($this->uri->segment(5)));
				
			$this->load->model("v2main/Jaycrypt");
			$info['username'] = $this->Jaycrypt->now($info['username']);
			
			$DB2   = $this->load->database('sqlserver', TRUE);
			$sql = "select * from users where Username = '{$info['username']}'";

			$query  = $DB2->query($sql);			
			$result = $query->result();
			
			if (count($result) == 0) {
				die("Approving official not recognized. Check Username.");
				return;
			}
			
			$info['password'] = $result[0]->Password;
			
			// $info['password'] = $this->uri->segment(4);
			
			$this->load->model("Login_model");

			$result2 = $this->Login_model->getUserInformation($result[0]->employee_id);

			$emp_id   = $result[0]->employee_id;
			$username = $result[0]->Username;
			$usertype = $result[0]->usertype;
			
			$user_session = array(
				'employee_id' => $emp_id,
				'username' => $username,
				'usertype' => $usertype,

				//'first_name' => $result2[0]->first_name,
				'full_name' => $result2[0]->f_name,
				'first_name' => $result2[0]->firstname,
				'last_name' => $result2[0]->l_name,
				'biometric_id' => $result2[0]->biometric_id,
				//'user_email' => $result2[0]->email,
				//'user_position' => $result2[0]->position_name,
				'area_id' => $result2[0]->area_id,
				'area_name' => $result2[0]->area_name,
			  
				//'display_name' => $result2[0]->display_name,
				'ip_address' => $_SERVER["REMOTE_ADDR"],
				//'theme_color' => $result2[0]->theme_color,
				//'bg_image' => $result2[0]->bg_image,
				//'font_color' => $result2[0]->font_color,
				//'ticket_log_order_start_on' => $result2[0]->ticket_log_order_start_on,
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

				//'profile_picture' => $result2[0]->image_path
			);
			
			$this->session->set_userdata($user_session);

			//$u_info = $this->segment->uri(4);
			//$p_info = $this->segment->uri(5);
			
			//$info   = 
			
			$this->load->model("v2main/Globalproc");
			$this->load->model("v2main/Dtr_new_model");
				
			$ret = false;
			
			// needs token from 
			$token = $this->uri->segment(3);
			
			if ($token == null) {
				die("cannot proceed");
			}
						
			$verify = $this->Globalproc->get_details_from_table("countersign",
																['vercode'=>$token],
																["vercode","emp_id",
																 "countersign_id","approval_status",
																 "dtr_summary_rep","tobeapprovedby",
																 "last_approving"]);
			
			if (count($verify) == 0 || $verify == 0){
				echo "<p style='font-size: 21px; text-align: center;
								background: grey;
								padding: 10px;
								color: #fff;
								margin: 0px;'> The DTR cannot be found. <p>";
				echo "<p style='text-align: center;'> The error may have been caused by the following: </p>";
				echo "<p style='text-align: center;'> 1. The DTR has been removed and you have clicked an old link in your email. Please ask your staff to resend the DTR again. Thank you.</p>";
				echo "<p style='text-align: center;'> 2. The DTR was sent to a new approving official for an approval. </p> ";
				
				echo "<p style='position: absolute; bottom: 0px; background: #ababab; margin: 0px; width: 100%; text-align: center;
								padding: 10px; font-size: 17px; color: #696868; text-shadow: 0px 1px 0px #d0d0d0;'> MinDa </p>";
				return;
			}
			
			// see if there is a pending findings 
				$findings = $this->Globalproc->gdtf("allowedsubmit",
												    ["asdtr_sumrep"=>$verify['dtr_summary_rep'],
													 "conn"=>"and",
													 "cnt_id"=>$verify['countersign_id']],
													"*");
				
				if (count($findings) > 0 && $findings[0]->status >= 1) {
					if ($findings[0]->status == 1) {
						die("There's an uncorrected issue(s) found in this DTR. You cannot approve this for now. Allow him/her to re-submit his/her DTR with corrections to it.");
						return;
					} else if ($findings[0]->status == 2) {
						die("The HR has already cleared this DTR.");
						return;
					}
				}
			// end 
			
			$emp    = $this->Globalproc->get_details_from_table("employees",
																['employee_id'=>$verify['emp_id']],
																["f_name","Division_id","email_2","firstname","l_name","employment_type"]);
		
			$clean_emp_name = $emp['firstname']." ".$emp['l_name'];
			
			/*
			$chief  = $this->Globalproc->get_details_from_table("employees",
																['Division_id'=>$emp['Division_id'],"conn"=>"and","is_head"=>1],
																['employee_id']);
			*/
			
			// ----> here get the first approving personnel: for directors and chiefs,
			$chief  = $this->Globalproc->get_details_from_table("employees",
																['employee_id'=>$verify['tobeapprovedby']],
																['employee_id','f_name']);
			// ----> end 
			
			if ($verify['vercode']==null || empty($verify['vercode'])) {
				die("cannot authenticate the link you provided. Thanks.");
			}
			
			$approver = null;
			
			$final = false;
			
			if ($verify['approval_status'] == 1) {
				if ($emp_id != $verify["last_approving"]) {
					die("<p style='background: red; text-align: center; color: #fff; padding: 40px; font-size: 20px;'> You are not allowed to sign this document.. Go back</p>"); return;
				}
				
				// check if the approving official has already approved this document 
					// check if the approving official is the last approving official stored in the database 
					// if not, then throw warning that "the approving official has already approved this document on his/her end"
						// and only waiting for the last approving official to finally approved this file.
						if ($emp_id != $verify['last_approving']) {
							die("You have already approved this file.");
						}
				// :: end
			
				$ret = $this->Globalproc->__update("countersign",["approval_status"=>2],["vercode"=>$token]);
				if ($ret && $emp['employment_type'] == 'JO') {
					$ret = $this->Globalproc->__update("d_accomplishment",["s_action"=>1,"f_action"=>1],["spl_grp_id"=>$verify['dtr_summary_rep']]);
				}
				
				$final = true;
			} else if ($verify['approval_status'] == 0) {		
				if ($emp_id != $verify["tobeapprovedby"]) {
					die("<p style='background: red; text-align: center; color: #fff; padding: 40px; font-size: 20px;'> You are not allowed to sign this document.. Go back</p>"); return;
				}
				
				$ret = $this->Globalproc->__update("countersign",["approval_status"=>1],["vercode"=>$token]);
				
				if ($ret) {
					$ret = $this->Globalproc->__update("dtr_summary_reports",
													  ["is_approved"=>1,"approved_by"=>$chief['employee_id']],
													  ["sum_reports_id"=>$verify['dtr_summary_rep']]);
					if ($ret && $emp['employment_type'] == 'JO') {
						$ret = $this->Globalproc->__update("d_accomplishment",["f_action"=>1],["spl_grp_id"=>$verify['dtr_summary_rep']]);
					}
				}
			}
			/*
			else {
				die("approval status is unknown.");
			}
			*/
			
			// payroll period 
				
				$coverid_sql = "select 
									hdc.date_started, 
									hdc.date_ended, 
									hdc.date_deadline
								from hr_dtr_coverage as hdc
								JOIN dtr_summary_reports as dsr 
									on hdc.dtr_cover_id = dsr.dtr_cover_id
								where dsr.sum_reports_id = '{$verify['dtr_summary_rep']}'";
				
				// echo $coverid_sql;
				
				$coverdet   = $this->Globalproc->__getdata($coverid_sql);
				$coverdate  = $coverdet[0]->date_started." - ".$coverdet[0]->date_ended;
				
			// end payroll period
			
			if ($ret) {
				// notify the HR
				$baseurl = base_url();
				
				$to_hr = "<html>
							<body style='font-family:calibri; background: #d6d2d2;'>
								<div style='width: 70%;
							margin: auto;
							background: #fff;
							border: 1px solid #ccc;
							border-radius: 3px;
							box-shadow: 0px 2px 3px #ded9d9;'>
									<div role='header' style='padding: 13px;
							border-bottom: 1px solid #ccc; background: #72d1ff;'>
										<div style='text-align: center;'> 
										<img src='{$baseurl}assets/images/approved.png'>
										</div>
										<p style='    text-align: center;
							font-size: 25px;
							color: #072533;
							text-shadow: 0px 1px 0px #fff;'> A DTR is APPROVED! </p>
									</div>
									<div role='content' style='    text-align: left;
							padding: 20px 30px;'>
										<p style='font-size: 17px; border-bottom: 1px solid #4c5d65; padding-bottom: 15px;'> <span style='font-size:17px; color: #7d7979;'> Employee: </span>  {$emp['f_name']} </p>
										<p style='    font-size: 17px;'> The DTR of the above mentioned employee has been approved by {$result2[0]->firstname} {$result2[0]->l_name}. </p>
										<p style='    font-size: 17px;'> Payroll Period: <strong> {$coverdate} </strong> </p>
									</div>
									<div role='footer' style='text-align:center; border-top: 1px solid #ccc;'>
										<a href='#' style=' text-decoration: none; color: #427b96;'> <p style='margin: 0px;
							padding: 20px;
							font-size: 14px;
							background: #d8e6ec;'> LOGIN </p> </a>
									</div>
								</div>
							</body>
						</html>";
						
				$to_emp = "<html>
							<body style='font-family:calibri; background: #d6d2d2; padding: 50px 0px;'>
								<div style='width: 70%;
							margin: auto;
							background: #fff;
							border: 1px solid #ccc;
							border-radius: 3px;
							box-shadow: 0px 2px 3px #ded9d9;'>
									<div role='header' style='padding: 13px;
							border-bottom: 1px solid #45a49d; background: #72d1ff;'>
										<div style='text-align: center;'> 
										<img src='{$baseurl}assets/images/approved.png'>
										</div>
										<p style='text-align: center;
							font-size: 25px;
							color: #072533;
							text-shadow: 0px 1px 0px #fff;'> Your DTR has been APPROVED! </p>
									</div>
									<div role='content' style='    text-align: center;
							padding: 0px;'>
										<p style='font-family: calibri;margin-left: 42px;font-size: 17px;'> 
										Approving official: <strong> {$result2[0]->firstname} {$result2[0]->l_name} </strong> 
										</p>
										<p style='font-family: calibri;margin-left: 42px;font-size: 17px;'>  
											Payroll Period:  <strong> {$coverdate} </strong>
										</p>
									</div>
									<div role='footer' style='text-align:center; border-top: 1px solid #ccc;'>
										<a href='{$baseurl}' style=' text-decoration: none; color: #427b96;'> <p style='    margin: 0px;
							padding: 20px;
							font-size: 14px;
							background: #d8e6ec;'> LOGIN </p> </a>
									</div>
								</div>
							</body>
						</html>";
					
			$ret = $this->Globalproc->sendtoemail(["to"		 => "hr@minda.gov.ph",// hr@minda.gov.ph
												   "from"	 => $clean_emp_name,
												   "subject" => "A DTR has been approved.",
												   "message" => $to_hr]);
												//  "cc"		 => "alvinjay.merto@minda.gov.ph"]); // cc to employee email address
			
				if ($ret) {
					// send to employee
					$ret = $this->Globalproc->sendtoemail(["to"	 => $emp['email_2'],
														   "from"	 => "HR",
														   "subject" => "Your DTR is Approved",
														   "message" => $to_emp]); 
					
					if ($ret && $final == false) {
						// last_approving 
						// send to last approving official
							// -----------------------------------------------------------------------------------------------
								$thedtr = $this->Globalproc->gdtf("countersign",["vercode"=>$verify['vercode']],"*");
								$la     = $this->Globalproc->gdtf("employees",
																 ["employee_id"=>$thedtr[0]->last_approving],
																  ["email_2","f_name"]); // la = last approving 
								$all_sumrep = $this->Globalproc->gdtf("dtr_summary_reports",["sum_reports_id"=>$verify['dtr_summary_rep']],["dtr_coverage","dtr_cover_id"]);
							//	$sumrep   	= $all_sumrep[0]->dtr_coverage;
								
								$coverrep   = $this->Globalproc->gdtf("hr_dtr_coverage",["dtr_cover_id"=>$all_sumrep[0]->dtr_cover_id],["date_started","date_ended"]);
								$sumrep   	= $coverrep[0]->date_started."-".$coverrep[0]->date_ended;
								
								$la_uname   = $this->Globalproc->gdtf("users",["employee_id"=>$thedtr[0]->last_approving],["Username","Password"]);
								
								// $approvedby = $this->Globalproc->gdtf("employees",['employee_id'=>$thedtr[0]->tobeapprovedby]);
						// http://office.minda.gov.ph:9003//my/accomplishments/viewing/389/02-08-2018/02-20-2018
								$accom_view = null;
								if ($emp['employment_type'] == "JO") {
									list($from_,$to_) = explode("-",$sumrep);
									// my/accomplishments/viewing/389/01-29-2018/02-08-2018
									$d_from = date("m-d-Y",strtotime($from_));
									$d_to   = date("m-d-Y",strtotime($to_));
									$accom_view 	  = "<tr>
															<td> </td>
															<td style='font-size: 14px;font-weight: bold; border: 1px solid #ccc; padding-left: 5px;'>
																<a href='".base_url()."/my/accomplishments/viewing/{$verify['emp_id']}/{$d_from}/{$d_to}/{$thedtr[0]->last_approving}'>View Accomplishment Report</a> 
															</td>
														</tr>";
											
								}
								$m = $this->Globalproc->returndtrformat($clean_emp_name,
																		$sumrep,
																		$verify['vercode'],
																		$la_uname[0]->Username,
																		$la_uname[0]->Password,
																		urldecode($thedtr[0]->bodycode),
																		$accom_view,
																		$chief['f_name']);
								
								$ret = $this->Globalproc->sendtoemail(["to"		 => $la[0]->email_2, 
																	   "from"	 => strtoupper($clean_emp_name),
																	   "subject" => "DTR: for approval ( ".strtoupper($clean_emp_name)." )",
																	   "message" => $m]); 
								
								//echo $m;
								//$m = "";
								
							// -----------------------------------------------------------------------------------------------
						// end 
						
					}
					
					if ($ret) {
						$dom['html'] = "<p style='text-align: center;
										margin: 44px auto;
										font-size: 16px;
										width: 80%;
										border: 1px solid #ccc;
										padding: 29px;
										border-radius: 3px;
										box-shadow: 0px 2px 2px #d8d8d8;
										background: #e1fbff;
										color: #444;
										font-family: arial;'> 
										<img src='".base_url()."/assets/images/approved.png' style='clear: both;
													display: table-cell;
													vertical-align: middle;
													margin: auto;
													margin-bottom: 30px;'/>
										The DTR for <strong>{$emp['f_name']}</strong> has been Verified.</p>";
					}
				}
			} else {
				// die("The DTR you are trying to access has possibly been approved already.");
				die("This DTR is approved.");
			}
			
			$this->load->view("v2views/dtrapproval", $dom);
		}
		
		public function returned() {
			$this->load->model("v2main/Globalproc");
			$this->load->model("Globalvars");
			$data['main_content'] = 'v2views/returned_dtr.php';
			
			$data['headscripts']['style'][0] = base_url()."v2includes/style/returned_dtr.style.css";
			
			echo "<script>";
				echo "var return_wat = '2';";
				echo "var viewdtr    = false;";
			echo "</script>";
			
			$data['headscripts']['js'][0]	 = base_url()."v2includes/js/returned_dtr.procs.js";
			
			$data['admin'] = ($this->Globalvars->usertype != "user")?true:false;
			
			if ($data['admin'] == false) {
				die("You are not allowed in here...");
			}
			
			$get_divisions = "select * from Division";
			$data['divs']  = $this->Globalproc->__getdata($get_divisions);
			
			$data['title'] = "| Returned DTR's";
			
			$this->load->view('hrmis/admin_view',$data);
		}
		
		public function getsubmitteddtrs() {
			// division code
			$this->load->model("v2main/Globalproc");
			$this->load->model('main/main_model');
			
			$divid  = $this->input->post("info");
			
			$retwat = $divid['returnwhat'];

			$unit = "=";
			if ($retwat == 2) {
				$retwat = '1';
				$unit = ">=";
			}
			
			$divid  = $divid['divid'];
			
			/*
			$sql = "select 
						employees.f_name, 
						employees.employee_id, 
						employees.employment_type, 
						dtr_summary_reports.dtr_coverage, 
						countersign.countersign_id from employees 
					JOIN countersign on employees.employee_id = countersign.emp_id
					JOIN dtr_summary_reports on countersign.dtr_summary_rep = dtr_summary_reports.sum_reports_id
					WHERE employees.Division_id = '{$divid}' and dtr_summary_reports.is_approved = '{$retwat}' and 
					countersign.hrnotified='0'"; 
			*/
				
			$sql = "select 
						employees.f_name, 
						employees.employee_id, 
						employees.employment_type, 
						dtr_summary_reports.dtr_coverage, 
						countersign.countersign_id from employees 
					JOIN countersign on employees.employee_id = countersign.emp_id
					JOIN dtr_summary_reports on countersign.dtr_summary_rep = dtr_summary_reports.sum_reports_id
					WHERE employees.Division_id = '{$divid}' and countersign.approval_status {$unit} '{$retwat}' and 
					countersign.hrnotified='0'";
			
			$divcode = $this->main_model->array_utf8_encode_recursive( $this->Globalproc->__getdata($sql) );
			
			echo json_encode($divcode);
		}
		
		public function printed() {
			$this->load->model("v2main/Globalproc");
			$info = $this->input->post("info");
			$cid  = $info["cid"];
			
			$ret  = $this->Globalproc->__update("countersign",["hrnotified"=>1],['countersign_id'=>$cid]);
			echo json_encode($ret);
		}
		
		public function resenddtr() {
			/*
				$ret = $this->Globalproc->sendtoemail(["to"		 => $chief_email, 
												       "from"	 => $fullname,
												       "subject" => "I need your signature.",
												       "message" => $m."<div style='clear:both;'></div>".urldecode($html)]); 
			*/
		}
		
		public function forapproval($p = '', $u = '' , $cnt = '' , $sumrep = '') {
			// ===================================================================
			
			if ($u != '' && $p != '')  {
				$this->load->model("Login_model");
				$info["username"] = $u;
				
				
					$DB2 = $this->load->database('sqlserver', TRUE);

					$this->load->library('encrypt');
					$username =  $this->main_model->encode_special_characters($info['username']);
				//	$password = $info['password'];


					$query = "SELECT 1 as success , u.Password as pw, u.employee_id as employee_id , u.username , u.usertype, u.isfirsttime FROM users u
							  WHERE u.username = '{$username}'
							  ";
					
					$query  = $DB2->query($query);
					$result = $query->result();
				
				$info['password'] = $result[0]->pw;				
				
				$a = $this->Login_model->authorizeUser($info);
				
				$emp_id   = $a[0]->employee_id;
				$username = $a[0]->username;
				$usertype = $a[0]->usertype;
				
				$result2 = $this->Login_model->getUserInformation($a[0]->employee_id);
			
				
				$user_session = array(
					'employee_id' => $emp_id,
					'username' => $username,
					'usertype' => $usertype,
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
					'position_name' => $result2[0]->position_name,
					'isfocal' => $result2[0]->isfocal
				);
				
				$this->session->set_userdata($user_session);
				/*
				echo "<script>";
					echo "var viewdtr      = true;";
					echo "var frm_empid    = '{$a[0]->employee_id}';";
					echo "var frm_cntid    = '{$cnt}';";
					echo "var frm_sumrep   = '{$sumrep}';";
					echo "var getdate__    = true;";
				echo "</script>";
				*/
			}
			// ===================================================================
			
			
			/*
			$this->load->model("v2main/Globalproc");
			$this->load->model("Globalvars");
			
			$data['main_content'] = 'v2views/returned_dtr.php';
			
			$data['headscripts']['style'][] = base_url()."v2includes/style/new.returned_dtr.style.css";
			$data['headscripts']['style'][] = base_url()."v2includes/style/returned_dtr.style.css";
			
			$loggedinid = $this->Globalvars->employeeid;
			$emp = $this->Globalproc->gdtf("employees",['employee_id'=>$loggedinid],['DBM_Pap_id','is_head',"Division_id"]);
			
			$returnwhat = null;
			$getwhat    = null;
			
			if ($emp[0]->is_head == 1 && $emp[0]->Division_id == 0) { // last approving official
				$returnwhat = 1;
				$getwhat 	= ["DBM_Sub_Pap_Id"=>$emp[0]->DBM_Pap_id];
			} else if ($emp[0]->is_head == 1 && $emp[0]->Division_id != 0) { // division chief
				$returnwhat = 0;
				$getwhat 	= ["Division_id"=>$emp[0]->Division_id];
			} else if ($emp[0]->is_head != 1) {
				die("You are not allowed here");
			}
			
			echo "<script>";
				echo "var return_wat = '{$returnwhat}';";
			echo "</script>";
			
			$data['headscripts']['js'][0]	 = base_url()."v2includes/js/returned_dtr.procs.js";
			
			$data['admin'] = ($this->Globalvars->usertype != "user")?true:false;
			
			$div = $this->Globalproc->gdtf("Division",$getwhat,"*");
			
			//$get_divisions = "select * from Division";
			// $data['divs']  = $this->Globalproc->__getdata($get_divisions);
			$data['divs']  = $div;
			
			$data['title'] = "| For Approval";
			
			$this->load->view('hrmis/admin_view',$data);
			*/
			$this->load->model("v2main/Globalproc");
			$this->load->model("Globalvars");
			
			$data['headscripts']['style'][]	= base_url()."v2includes/style/dtrforreview.style.css";
			$data['headscripts']['js'][]	= base_url()."v2includes/js/dtrforreview.js";
			
			$data['loggedin']   = $loggedinid = $this->Globalvars->employeeid;
			$emp 				= $this->Globalproc->gdtf("employees",['employee_id'=>$loggedinid],['DBM_Pap_id','is_head',"Division_id","area_id","isfocal"]);
			$emp_u				= $this->Globalproc->gdtf("users",["employee_id"=>$loggedinid],["Username","Password"]);
			
			$returnwhat = null;
			$getwhat    = null;

			// approve all variables 
				$app_status = null;
			// end 
			
			$data['isadmin']	= $this->session->userdata("usertype");
			$data['selectdate'] = null;
			
			$special_sql = null;
			if ($emp[0]->is_head == 1 && $emp[0]->Division_id == 0) { // last approving official
				$returnwhat 		   = 1;
				$app_status 		   = 2;
				$data['findingsfrom']  = 3; // last approving official
				$data['chief_here']    = true;
				$special_sql 		   = "and e.DBM_Pap_id = '{$emp[0]->DBM_Pap_id}'";
				
				if (isset($_GET['division'])) {
					$special_sql 	   = $special_sql." and Division_id = '{$_GET['division']}'";
				}
				
				$data['get_divs'] 	   = $this->Globalproc->gdtf("Division",['DBM_Sub_Pap_Id'=>$emp[0]->DBM_Pap_id],["Division_desc","Division_Id"]);
				
				// if returnwhat is 1; get DBM
			} else if ($emp[0]->is_head == 1 && $emp[0]->Division_id != 0 && $emp[0]->area_id == 1) { // division chief
					$returnwhat 		   = 0;
					$app_status 		   = 1;
					$data['findingsfrom']  = 2; // chief 
					$data['chief_here']    = true;
					$special_sql 		   = "and e.Division_id = '{$emp[0]->Division_id}'";
					$data['nav'] 		   = null;
				// if returnwhat is 0; get division 
			} else if ($emp[0]->is_head != 1 && $emp[0]->isfocal != 1) {
				die("You are not allowed here");
			} else if ($emp[0]->area_id != 1 && $emp[0]->is_head == 1) {
					$returnwhat 		   = 1;
					$app_status 		   = 2;
					$data['findingsfrom']  = 3; // last approving official
					$data['chief_here']    = true;
					//$special_sql 		   = "and e.DBM_Pap_id = '{$emp[0]->DBM_Pap_id}'";
					
					$special_sql 		   = "and e.Division_id = '{$emp[0]->Division_id}'";
			} else if ($emp[0]->isfocal == 1 && $emp[0]->Division_id != 0) { // focal person who is reporting to chief
				$returnwhat 		   = 2; // change to 0 if you want the focal person to receive unsigned dtr, 1 to chief-level approved dtr, 2 for approved DTR
				$app_status 		   = 1;
				$data['isfocal']	   = true;
				$data['findingsfrom']  = 2; // chief 
				$special_sql 		   = "and e.Division_id = '{$emp[0]->Division_id}'";
				$data['nav'] 		   = null;
			} else if ($emp[0]->isfocal == 1 && $emp[0]->Division_id == 0) { // focal person who is directly reporting to a director
				$returnwhat 		   = 2; // change to 0 if you want the focal person to receive unsigned dtr, 1 to chief-level approved dtr, 2 for approved DTR
				$app_status 		   = 2;
				$data['isfocal']	   = true;
				$data['findingsfrom']  = 3; // last approving official
				$special_sql 		   = "and e.DBM_Pap_id = '{$emp[0]->DBM_Pap_id}'";
				
				if (isset($_GET['division'])) {
					$special_sql 	   = $special_sql." and Division_id = '{$_GET['division']}'";
				}
				
				$data['get_divs'] 	   = $this->Globalproc->gdtf("Division",['DBM_Sub_Pap_Id'=>$emp[0]->DBM_Pap_id],["Division_desc","Division_Id"]);
			}
			
			$data['main_content'] = "v2views/dtrforreview";
			$data['title']		  = "| For Approval";
			$data['admin']		  = false;
			
			$forrev = "select 
							aw.*, 
							e.f_name,
							dsr.date_start_cover,
							dsr.date_end_cover,
							dsr.dtr_cover_id,
							cs.approval_status,
							cs.vercode,
							hdc.date_started,
							hdc.date_ended
						from allowedsubmit as aw
						join employees as e on aw.emp_id = e.employee_id
						join dtr_summary_reports as dsr on aw.asdtr_sumrep = dsr.sum_reports_id
						JOIN hr_dtr_coverage as hdc on dsr.dtr_cover_id = hdc.dtr_cover_id
						JOIN countersign as cs on dsr.sum_reports_id = cs.dtr_summary_rep
						where cs.approval_status = '{$returnwhat}' {$special_sql} and aw.status = '0'";
		//	echo $forrev;
				// aw.status = '0' and 
			//echo $forrev;	
			// JOIN Division as d on e.Division_id = d.Division_Id	
			// d.Division_desc
			// http://office.minda.gov.ph:9003/dtr/approval/f055a1323a87deca41e29f5f00d7112589428c44d373e69318c0f24686557af7/81dc9bdb52d04dc20036dbd8313ed055/aalonto
			// http://office.minda.gov.ph:9003/dtr/approval/token/p/u
			$data['forrev'] 		= $this->Globalproc->__getdata($forrev);
			// $data['approvinglink']	= "http://office.minda.gov.ph:9003/dtr/approval/{$data['forrev']}/p/u";
			
			if (isset($_POST['approveall'])){
				$tba = $data['forrev'];
				
				if (count($tba) == 0) {
					echo "<p> hmmmm.... No entry found</p>";
					echo "<a href='".base_url()."dtr/forapproval'> Click here to go back. </a>";
					return;
				}
				
				$up_details = [
					"approval_status"	=> $app_status
				];
				
				$where  = "";
				$count  = 1;
				
				// email sql 
				$emails    = "";
				$email_sql = " where ";
				// end 
				
				// approving officials 
					$first  = null;
					$second = null;
				// end 
				
				foreach($tba as $t) {
					$where .= "countersign_id = '{$t->cnt_id}'";
					$where .= (count($tba) == $count)?"" : " or " ;
					
					// get email 
						$email_sql .= "employee_id = '{$t->emp_id}'";
						$email_sql .= (count($tba) == $count)?"" : " or " ;
					// end 
					
					// populate approving official
						$first = $t->tobeapprovedby;
						$last  = $t->last_approving;
					// end 
					
					$count++;
				}
				
				$email_sql = "select email_2, employee_id from employees ".$email_sql;
				$getemail  = $this->Globalproc->__getdata($email_sql);
				
				if (count($getemail) >=1) {
					$l_count = 1;
					foreach($getemail as $ems) {
						$emails  .= $getemail[0]->email_2;
						$emails	 .= (count($emails) == $l_count)?"":", ";		
					}
				}

				$update_all = $this->Globalproc->__update("countersign",$up_details,$where);
				if ($update_all) {
					// Division_id
					// DBM_Pap_id
					
					
					
					/*
					$to 	 = $details['to'];
					$subject = $details['subject'];
					$message = $details['message'];
					
					$headers = "MIME-Version: 1.0" . "\r\n";
					$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
					$headers .= "From: ".$details["from"]. "\r\n";
					*/
					
					$sendemail = [
						"to"		=> null,
						"subject"	=> null,
						"message"	=> null,
						"from"		=> null
					];
					
					if ($app_status == 1) { // send to DBM
						$sendemail['to']		= $this->Globalproc->gdtf("employees",["employee_id"=>$last_approving],["email_2"])[0]->email_2;
						$sendemail['subject']	= "";
					} else if ($app_status == 2) { // end of route
						
					}
					
					$data['approvedall'] 	= "<p class='allapproved'> All DTRs are approved. </p>";
					$data['forrev'] 		= $this->Globalproc->__getdata($forrev);
				}
				
			}
			
			$this->load->view("hrmis/admin_view", $data);
		}
		
		public function review($tag = '', $argc = '', $seldate = '') {
			$data['headscripts']['style'][]	= base_url()."v2includes/style/dtrforreview.style.css";
			$data['headscripts']['js'][]	= base_url()."v2includes/js/dtrforreview.js";
			
			$data['main_content'] = "v2views/dtrforreview";
			$data['title']		  = "| For Review";
			$data['admin']		  = true;
			$data['findingsfrom'] = "1"; // HR
			
			$this->load->model("v2main/Globalproc");
			
			$data['selectdate'] = null;
			$data['isadmin']	= $this->session->userdata("usertype");
			$nav = null;
			
			if ($tag == '' || $tag == 'forreview') {
				$emptype = null;
				if ($argc == '' || $argc == "joborder") {
					$emptype = "JO";
				} else if ($argc == "plantilla") {
					$emptype = "REGULAR";
				}
				$forrev = "select 
								aw.*, 
								e.f_name,
								dsr.date_start_cover,
								dsr.date_end_cover,
								dsr.dtr_cover_id,
								cs.approval_status,
								hdc.date_started,
								hdc.date_ended
							from allowedsubmit as aw
							JOIN employees as e on aw.emp_id = e.employee_id
							JOIN dtr_summary_reports as dsr on aw.asdtr_sumrep = dsr.sum_reports_id
							JOIN countersign as cs on dsr.sum_reports_id = cs.dtr_summary_rep
							JOIN hr_dtr_coverage as hdc on dsr.dtr_cover_id = hdc.dtr_cover_id
							where aw.status = 0 and cs.approval_status > 1 and e.employment_type = '{$emptype}'"; // change to 0 to consider the AMO 
						$nav = "fr";
					
			} else if ($tag == 'waitingforapproval') {		
				$emptype = null;
				
				if ($argc == '' || $argc == "joborder") {
					$emptype = "JO";
				} else if ($argc == "plantilla") {
					$emptype = "REGULAR";
				}
				$forrev = "select 
							aw.*, 
							e.f_name,
							dsr.date_start_cover,
							dsr.date_end_cover,
							dsr.dtr_cover_id,
							cs.approval_status,
							hdc.date_started,
							hdc.date_ended
						from allowedsubmit as aw
						JOIN employees as e on aw.emp_id = e.employee_id
						JOIN dtr_summary_reports as dsr on aw.asdtr_sumrep = dsr.sum_reports_id
						JOIN countersign as cs on dsr.sum_reports_id = cs.dtr_summary_rep
						JOIN hr_dtr_coverage as hdc on dsr.dtr_cover_id = hdc.dtr_cover_id
						where aw.status = '0' and cs.approval_status <= 1 and e.employment_type = '{$emptype}'"; // change to 0 to consider the AMO 
						$nav = "wa";
			} else if ($tag == 'notsubmitted') {
				echo "<p style='color:#fff; background:red; margin:0px; text-align:center;'> not working for now... </p>";
				$forrev = "select 
								aw.*, 
								e.f_name,
								dsr.date_start_cover,
								dsr.date_end_cover,
								dsr.dtr_cover_id,
								cs.approval_status,
								hdc.date_started,
								hdc.date_ended
							from allowedsubmit as aw
							JOIN employees as e on aw.emp_id = e.employee_id
							JOIN dtr_summary_reports as dsr on aw.asdtr_sumrep = dsr.sum_reports_id
							JOIN countersign as cs on dsr.sum_reports_id = cs.dtr_summary_rep
							JOIN hr_dtr_coverage as hdc on dsr.dtr_cover_id = hdc.dtr_cover_id
							where aw.status = '0' and cs.approval_status > 1"; // change to 0 to consider the AMO 
				$nav = 'ns';
			} else if ($tag == 'cleared') {
				
				$data['url']		= base_url().$this->uri->segment(1)."/".$this->uri->segment(2)."/".$this->uri->segment(3)."/".$this->uri->segment(4);
				
				$dtr_cover 			= null;
				
				$data['jobtype'] 	= null;
				if ($argc == '' || $argc == "joborder") {
					$jo_dtrcover = $this->Globalproc->gdtf("hr_dtr_coverage",['employment_type'=>'JO',"conn"=>"and","is_active"=>1],"dtr_cover_id");
					$dtr_cover   = $jo_dtrcover;
					
					$jobtype 	 = "JO";

				} else if ($argc == "plantilla") {
					$rg_dtrcover = $this->Globalproc->gdtf("hr_dtr_coverage",['employment_type'=>'REGULAR',"conn"=>"and","is_active"=>1],"dtr_cover_id");
					$dtr_cover   = $rg_dtrcover;
					
					$jobtype 	 = "REGULAR";
				
				}
				
				$data['jobtype'] 	= $jobtype;
				
				if ($argc != '') {
					$j_dates    		= $this->Globalproc->__getdata("select * from hr_dtr_coverage where employment_type = '{$jobtype}' order by date_started DESC");
					$data['selectdate'] = $j_dates;
				}

				$forrev = "select 
								aw.*, 
								e.f_name,
								dsr.date_start_cover,
								dsr.date_end_cover,
								dsr.dtr_cover_id,
								cs.approval_status,
								hdc.date_started,
								hdc.date_ended
							from allowedsubmit as aw
							JOIN employees as e on aw.emp_id = e.employee_id
							JOIN dtr_summary_reports as dsr on aw.asdtr_sumrep = dsr.sum_reports_id
							JOIN countersign as cs on dsr.sum_reports_id = cs.dtr_summary_rep
							JOIN hr_dtr_coverage as hdc on dsr.dtr_cover_id = hdc.dtr_cover_id
							where aw.status = '2'";
				
				if ($seldate != '') {
					$forrev 	= "select * from 
										(select aw.*, e.f_name, dsr.date_start_cover, dsr.date_end_cover, dsr.dtr_cover_id, cs.approval_status, hdc.date_started, hdc.date_ended 
										from allowedsubmit as aw
										RIGHT JOIN employees as e on aw.emp_id = e.employee_id 
										RIGHT JOIN dtr_summary_reports as dsr on aw.asdtr_sumrep = dsr.sum_reports_id 
										RIGHT JOIN countersign as cs on dsr.sum_reports_id = cs.dtr_summary_rep 
										RIGHT JOIN hr_dtr_coverage as hdc on dsr.dtr_cover_id = hdc.dtr_cover_id 
										where aw.status = '2') as tb1 where tb1.dtr_cover_id = '{$seldate}'";
				}
				
			//	echo $forrev;
				
				if ($seldate == '') {
					$count = 0;
					
					foreach($dtr_cover as $dc) {
						$forrev .= ($count == 0)?" and ":" or ";
						$forrev .= "dsr.dtr_cover_id = '{$dc->dtr_cover_id}'";
						$count++;
					}
					
					$forrev	.= " and cs.hrnotified = '1'";
				}
				
				$nav = 'cl';
			//	echo $forrev;
			}
			// JOIN Division as d on e.Division_id = d.Division_Id	
			// d.Division_desc
			// echo $forrev;
			$data['nav']	   = $nav;
			$data['argc']	   = "| ".$argc;
			$data['forrev']	   = $x = $this->Globalproc->__getdata($forrev);
			
			// var_dump($data['forrev']);
			
			$data['date_']	   = null;
				
			if ($seldate != '') {
				$xx = $this->Globalproc->gdtf("hr_dtr_coverage",['dtr_cover_id' => $seldate],['date_started','date_ended']);
				
				if (count($xx) > 0) {
					$data['date_'] = date("M. d, Y", strtotime($xx[0]->date_started))." - ".date("M. d, Y", strtotime($xx[0]->date_ended));
				}
			}
			
			$dec   = json_encode($data['forrev']);
			
			$this->load->view("hrmis/admin_view", $data);
		}
		
		public function returnwithfindings() {
			
			$details  	 = $this->input->post("info");
			$findings 	 = $details['details'];
			$emp 	  	 = $details['emp'];
			$cnterid     = $details['cnterid'];
			$commentfrom = $details['com_from']; // 1 for HR, 2: chief, 3: last approving official
			
			// [{"date":"November 23, 2018","msg":"test test test test test test"}]", emp: 389, cnterid: 9129, com_from: 1
			/*
			$findings    = '[{"date":"November 23, 2018","msg":"test test test test test test"}]';
			$emp 	     = 389;
			$cnterid     = 9129;
			$commentfrom = 1;
			*/
		
			$this->load->model("v2main/Globalproc");
			$this->load->model("Globalvars");
			
			$ret = $this->Globalproc->__update("allowedsubmit",
											   ["findings"=>$findings,
											    "status"=>1,
												"correctedby"=>$this->Globalvars->employeeid,
												"from_db"=>$commentfrom],
											   ["cnt_id"=>$cnterid,"conn"=>"and","emp_id"=>$emp]);
			
			// evaluator
			$eval_dets  = ["department_id" =>19,
						   "conn"		   =>"and",
						   "is_head"	   =>1];
			$evaluator  = $this->Globalproc->gdtf("employees",$eval_dets,["f_name","email_2"]);
			$eval_email = null;
			
			if (count($evaluator)==0) {
				$eval_dets = ["department_id" => 22,
							  "conn"		  => "and",
							  "is_head" 	  => 1];
				$evaluator  = $this->Globalproc->gdtf("employees",$eval_dets,["f_name","email_2"]);
				
				$eval_email = $evaluator[0]->email_2;
				$evaluator  = $evaluator[0]->f_name;
			} else {
				$eval_email = $evaluator[0]->email_2;
				$evaluator = $evaluator[0]->f_name;
			}
			
			//$evaluator = $this->Globalproc->gdtf("employees",["employee_id"=>$this->Globalvars->employeeid],["f_name"])[0]->f_name;
			
			
			// send email to 
			$emp_dets = $this->Globalproc->gdtf("employees",["employee_id"=>$emp],["email_2"]);
			
			$items = "";
			
			$findings = json_decode($findings);
			
			$s = null;
			
			if (count($findings) > 1 ) {
				$s = "s";
			}
			
			for($i = 0; $i <= count($findings)-1; $i++) {
				$items .= "<li>";
					$items .= "<strong>".$findings[$i]->date."</strong>";
					$items .= "<p style='margin:0px;'> ".$findings[$i]->msg."</p>";
				$items .= "</li>";
			}
			
			$baseurl = base_url();
			$msg_p = "<div style='width: 100%;
								background: #e0e0e0;
								padding: 7px; font-size:18px;'>
						<div style='width: 30%;
									margin: 10px auto;
									background: #fff;
									border: 1px solid #ccc;'>
							<div style='padding:10px; padding: 22px; background: #f7d2d2;'>
								<h3 style='margin:0px; font-family: calibri;'> I have found an issue with your DTR</h3>
							</div>
							<div style='padding: 17px; font-family: calibri;'>
								<p style='margin:0px;'> Please fix the following </p>
								<ul>
									{$items}
								</ul>
								<p> Your DTR was evaluated by: {$evaluator} </p>
							</div>
							<div style='padding: 13px;
										background: #f1f1f1;
										border-top: 1px solid #ccc;'>
								<p style='margin:0px; text-align:center; font-family: calibri;'> Please <a href='{$baseurl}'>login</a> to your HRIS account to fix the problem{$s} </p>
							</div>
						</div>
					</div>";
		
			$sendemail = [
					"to" 		=> $emp_dets[0]->email_2.",".$eval_email,
					"from"		=> $evaluator,
					"subject" 	=> "DTR: Needs fixing",
					"message"	=> $msg_p
				];
				
			$ret = $this->Globalproc->sendtoemail($sendemail);
			
			echo json_encode($ret);
		}
		
		public function return_good() {
			//  emp 
			// cnterid 
			// used in 'cleared' in the for review area of HR
			$details = $this->input->post("info");
			$emp     = $details['emp'];
			$cnterid = $details['cnterid'];
						
			$this->load->model("v2main/Globalproc");
			$this->load->model("Globalvars");
			
			// update allow submit
			$ret = $this->Globalproc->__update("allowedsubmit",
											   ["status"=>2,"correctedby"=>$this->Globalvars->employeeid],
											   ["cnt_id"=>$cnterid,"conn"=>"and","emp_id"=>$emp]);
			
			// update countersign
			$ret = $this->Globalproc->__update("countersign",
											   ["hrnotified"=>1],
											   ["countersign_id"=>$cnterid]);
			
			/*
			if($ret) {
			// send email to approving official
				$counter_data = $this->Globalproc->gdtf("countersign",["countersign_id"=>$cnterid],["bodycode","approval_status","tobeapprovedby","last_approving"]);
				
				// sending details
					$sendto  = null;
					$html    = urldecode($counter_data[0]->bodycode);
				// end 
				
				$emp_details = $this->Globalproc->gdtf("employees",["employee_id"=>$emp],["email_2","f_name"]);
				$fullname    = $emp_details[0]->f_name;
				
				$approving_off_id = null;
				if ($counter_data[0]->approval_status == 0) {
					$approving_off_id = $counter_data[0]->tobeapprovedby;
				} else if($counter_data[0]->approval_status == 1) {
					$approving_off_id = $counter_data[0]->last_approving;
				}
				
				$_dets   = $this->Globalproc->gdtf("employees",['employee_id'=>$approving_off_id],["email_2"]);
				$sendto  = $_dets[0]->email_2;
				
				$ret = $this->Globalproc->sendtoemail(["to"		 => $sendto,
													   "from"	 => $fullname,
													   "subject" => "DTR: for approval",
													   "message" => $html]);
				
			// end 
			}
			*/
			echo json_encode($ret);

		}
		
		public function getstatus() {
			$details = $this->input->post("info");
			$dci     = $details['dci'];
			$emp 	 = $details['emp'];
			
			$this->load->model("v2main/Globalproc");
			
			$sumrep 			 = $this->Globalproc->gdtf("dtr_summary_reports",["dtr_cover_id"=>$dci,"conn"=>"and","employee_id"=>$emp],["sum_reports_id"]);
			$getfrom_countersign = $this->Globalproc->gdtf("countersign",['dtr_summary_rep'=>$sumrep[0]->sum_reports_id],["countersign_id"]);
			$get_stat_sql 		 = "select a_ss.*, e.f_name from allowedsubmit as a_ss
									JOIN employees as e on a_ss.correctedby = e.employee_id
									where a_ss.asdtr_sumrep = '{$sumrep[0]->sum_reports_id}' 
									and a_ss.cnt_id = '{$getfrom_countersign[0]->countersign_id}'
									and a_ss.emp_id = '{$emp}' and a_ss.status = '1'";
			
			$stat_data 			 = $this->Globalproc->__getdata($get_stat_sql);
			
			if (count($stat_data) > 0 ) {
				echo json_encode($stat_data);
			} else {
				echo json_encode("false");
			}
		}
		
		public function resubmit() {
			$dets     = $this->input->post("info");
			$as_id    = $dets['details'];
			$com_from = $dets['com_from'];
			
			$this->load->model("v2main/Globalproc");
			$ret = $this->Globalproc->__update("allowedsubmit",["status"=>0],['as_id'=>$as_id]);
			
			$m       = null;
			$toemail = null;
			
			
				$sql = "select 
							as_s.*, 
							cs.bodycode,
							cs.approval_status,
							cs.tobeapprovedby,
							cs.last_approving,
							cs.vercode,
							cs.emp_id,
							dsr.dtr_coverage,
							e.f_name,
							e.employment_type
							from allowedsubmit as as_s
						JOIN countersign as cs on as_s.cnt_id = cs.countersign_id
						JOIN dtr_summary_reports as dsr on as_s.asdtr_sumrep = dsr.sum_reports_id
						JOIN employees as e on as_s.emp_id = e.employee_id
						where as_s.as_id = '{$as_id}'";
				
				$data = $this->Globalproc->__getdata($sql);
			
			if ($com_from >= 2) {
				$accom_view = null;
				$approvedby = null;
				$chiefid    = null;
				
				if ($data[0]->approval_status == 0) {
					$approvedby = "Not yet approved";
					$chiefid = $data[0]->tobeapprovedby;
				} else if ($data[0]->approval_status == 1) {
					$approvedby = "Chief"; // here
					$chiefid = $data[0]->last_approving;
				}
				
				$chief    = $this->Globalproc->gdtf("employees",["employee_id"=>$chiefid],["email_2"]);
				
				/*else if ($data[0]->approval_status == 2) {
					$approvedby = "Last approving official";
				}*/
			
			
				if ($data[0]->employment_type == "JO") {
					$dtr_coverage = $data[0]->dtr_coverage;
					list($from,$to) = explode("-",$dtr_coverage);
					
					$from = date("m-d-Y",strtotime($from));
					$to   = date("m-d-Y",strtotime($to));
					
					
					$accom_view = "<tr>
										<td> </td>
										<td style='font-size: 14px;font-weight: bold; border: 1px solid #ccc; padding: 13px; text-align: center;'>
										<a href='".base_url()."/my/accomplishments/viewing/{$data[0]->emp_id}/{$from}/{$to}/{$chiefid}'>View Accomplishment Report</a> 
										</td>
								  </tr>";
				}
				
				$approvingbody = $this->Globalproc->gdtf("users",["employee_id"=>$chiefid],["Username","Password"]);
				$toemail = $chief[0]->email_2;
				
				$m = $this->Globalproc->returndtrformat($data[0]->f_name,
														$data[0]->dtr_coverage,
														$data[0]->vercode,
														$approvingbody[0]->Username,
														$approvingbody[0]->Password,
														urldecode($data[0]->bodycode), 
														$accom_view, 
														$approvedby);
			} else {
				$toemail = "hr@minda.gov.ph"; // hr@minda.gov.ph
				$m = "<div style='width: 100%;
								background: #e0e0e0;
								padding: 7px; font-size:18px;'>
						<div style='width: 30%;
									margin: 10px auto;
									background: #fff;
									border: 1px solid #ccc;'>
							<div style='padding:10px; padding: 22px; background: #f7d2d2;'>
								<h3 style='margin:0px; font-family: calibri;'> My DTR is fixed </h3>
							</div>
							<div style='padding: 17px; font-family: calibri;'>
								<p> Hi HR, </p>
								<p> I sent you my DTR for your perusal </p>
							</div>
							<div style='padding: 13px;
										background: #f1f1f1;
										border-top: 1px solid #ccc;'>
								<p style='margin:0px; text-align:center; font-family: calibri;'> Please <a href='".base_url()."'>login</a> to HRIS. </p>
							</div>
						</div>
					</div>";
			}
			
			$ret = $this->Globalproc->sendtoemail(["to" 	 => $toemail,
												   "from" 	 => strtoupper($data[0]->f_name),
												   "subject" => "DTR: re-submitted with corrections (".strtoupper($data[0]->f_name).")",
												   "message" => $m]);
			echo json_encode($ret);
		}
		
		public function gettoken() {
			$dets  = $this->input->post("info");
			$cntid = $dets['cntid_'];
			
			$this->load->model("Globalvars");
			$this->load->model("v2main/Globalproc");
			$c_dets = $this->Globalproc->gdtf("countersign",["countersign_id"=>$cntid],["vercode"]);
			$u_dets = $this->Globalproc->gdtf("users",["employee_id"=>$this->Globalvars->employeeid],["Username","Password","user_id"]);
			
			echo json_encode(["vercode"=>$c_dets[0]->vercode, 
							  "username"=>$u_dets[0]->Username,
							  "password"=>$u_dets[0]->Password,
							  "userid"=>$u_dets[0]->user_id]);
		}
		
		public function senddtrtoemail() {
			$dets    = $this->input->post("info");
			
			
			$countid = $dets['cntid_'];
			$sendto  = $dets['sendto_'];
			
			
			/*
			$countid = 2280;
			$sendto  = 50;
			*/
			
			
			$this->load->model("v2main/Globalproc");
			$this->load->model("main/Main_model");
			
			$ret = false;
		
			$sql = "select 
						cs.bodycode,
						cs.emp_id,
						cs.approval_status,
						cs.tobeapprovedby,
						cs.last_approving,
						cs.dtr_summary_rep,
						cs.vercode,
						dsr.dtr_coverage,
						e.f_name,
						e.employment_type
						from countersign as cs
						JOIN dtr_summary_reports as dsr on
						cs.dtr_summary_rep = dsr.sum_reports_id
						JOIN employees as e on 
						cs.emp_id = e.employee_id
						WHERE cs.countersign_id = '{$countid}'
					";
					// 
			$countersign_dets = $this->Main_model->array_utf8_encode_recursive($this->Globalproc->__getdata($sql));
			
			// send to email and login credentials
				$sendto_email = $this->Globalproc->gdtf("employees",['employee_id'=>$sendto],['email_2']);
				$sendto_creds = $this->Globalproc->gdtf("users",['employee_id'=>$sendto],["Username","Password"]);
			// end 
			
			$accom_view = null;
			if ($countersign_dets[0]->employment_type == "JO") {
				list($d_from,$d_to) = explode("-",$countersign_dets[0]->dtr_coverage);
				
				$d_from = date("m-d-Y",strtotime($d_from));
				$d_to   = date("m-d-Y",strtotime($d_to));
				
				$accom_view = "<tr>
									<td> </td>
									<td style='font-size: 14px;font-weight: bold; border: 1px solid #ccc; padding: 13px; text-align: center;'>
										<a href='".base_url()."/my/accomplishments/viewing/{$countersign_dets[0]->emp_id}/{$d_from}/{$d_to}/{$sendto}'>View Accomplishment Report</a> 
									</td>
							   </tr>";
			}
			
			$approvedby = "NOT YET APPROVED";
			
			if ($countersign_dets[0]->approval_status == 1 
				|| !$this->Globalproc->is_chief("division",$countersign_dets[0]->emp_id)
				|| !$this->Globalproc->is_chief("director",$countersign_dets[0]->emp_id)
				) {
				$approvedby = strtoupper($this->Globalproc->gdtf("employees",["employee_id"=>$countersign_dets[0]->tobeapprovedby],["f_name"])[0]->f_name);
			}
			
			$template      = $this->Globalproc->returndtrformat(htmlentities($countersign_dets[0]->f_name),
																$countersign_dets[0]->dtr_coverage,
																$countersign_dets[0]->vercode,
																$sendto_creds[0]->Username,
																$sendto_creds[0]->Password,
																urldecode($countersign_dets[0]->bodycode),
																$accom_view,
																$approvedby);
			
			// echo $this->Main_model->array_utf8_encode_recursive($countersign_dets[0]->f_name);
			// echo $this->Main_model->array_utf8_encode_recursive($template);
			
			/*
			echo htmlentities($countersign_dets[0]->f_name);
			echo $this->Main_model->array_utf8_encode_recursive($template);
			*/
			// $sendto_email[0]->email_2
			
			$ret = $this->Globalproc->sendtoemail(["to"  	 => $sendto_email[0]->email_2,
												   "from"	 => htmlentities($countersign_dets[0]->f_name),
												   "subject" => "DTR: Reminding you for approval (".htmlentities($countersign_dets[0]->f_name).") ",
												   "message" => $this->Main_model->array_utf8_encode_recursive($template)
												  ]);
			
			echo json_encode($ret);
		}
		
		function revertbackdtr() {
			$cnt_id = $this->input->post("info")["cntid"];
		//	$cnt_id = 2529;
			$this->load->model("v2main/Globalproc");
			
			// update countersign 
			$ret = $this->Globalproc->__update("countersign",["hrnotified"=>0],["countersign_id"=>$cnt_id]);
				// end countersign 
				
			// update allowsubmit 
				if ($ret) {
					$ret = $this->Globalproc->__update("allowedsubmit",['status'=>0],['cnt_id'=>$cnt_id]);
				}
				// end allowsubmit 
			
			echo json_encode($ret);
		}
		
		function clearfocal() {
			$info  = $this->input->post("info");
			$cntid = $info['cntid'];
			
			$this->load->model("v2main/Globalproc");
			
			$update = $this->Globalproc->__update("countersign",['approval_status'=>2],["countersign_id"=>$cntid]);
			
			echo json_encode($update);
		}
	}

