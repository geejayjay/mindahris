<?php 
	
	class Hr extends CI_Controller {
		public function __construct() {
			parent::__construct();
			$this->load->model('admin_model');
			$this->load->model('attendance_model');
			$this->load->model('reports_model');
			$this->load->model('personnel_model');
			$this->load->model('leave_model');
			
		}
		
		function dashboard() {
			$this->load->model("Globalvars");
			$this->load->model("v2main/Dashboard");
			$this->load->model("v2main/Globalproc");
			
			$data['admin'] = ($this->Globalvars->usertype != "user")?true:false;
			
			if ($data['admin'] == false) {
				die("You are not allowed in here...");
			}
			
			$data['title'] = '| Human Resource Dashboard';
			
			$data['headscripts']['style'][0]    = base_url()."v2includes/style/hr_dashboard.style.css";
			
			//$data['headscripts']['style'][1]  = "https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css";
				
			$data['headscripts']['style'][1]    = "https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css";
			
			// for leave management
			$data['headscripts']['style'][2]  	= base_url()."v2includes/style/leavemgt.style.css";
			$data['headscripts']['style'][3] 	= base_url()."v2includes/style/empview_style.css";
			
			// new summary js 
				$data['headscripts']['js'][]	 = base_url()."v2includes/js/newsummary.js";
			
			// hr view of dtr processes
				$data['headscripts']['js'][] 	 = base_url()."v2includes/js/hrviewdtr.procs.js";
			// end 
			
			//$data['headscripts']['js'][]      = base_url()."v2includes/js/windowresize.js";
			//$data['headscripts']['js'][]      = base_url()."v2includes/js/leavemgt.procs.js";
			
			//$data['headscripts']['js'][] 	    = "https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.4/jspdf.debug.js";
			
			
			$isgetfrom = $this->uri->segment(3);
			
			$sql     = "select * from areas";
			$syncs   = $this->Globalproc->__getdata($sql);
			
			$data['syncs'] = [];
			foreach($syncs as $ss) {
				$last_update  = $this->attendance_model->get_attemndance_logs_last_update($ss->area_id); // mark 2
				
				if (count($last_update)==0) {
					$last_update = "Unknown";
				} else {
					$last_update  = $last_update[0]->last_update;
				}
				
				$loc = [];
				$loc['name']		= $ss->area_name;
				$loc['ip']			= $ss->ipaddress;
				$loc['code']		= $ss->area_code;
				$loc['last_update']	= $last_update;
				array_push($data['syncs'],$loc);
			}
			
			if ($isgetfrom == null) { // getfrom value from url is not present
				// javascript responsible for loading the DTR through an ajax request
				$data['headscripts']['js'][] = base_url()."v2includes/js/hr.dashboard.js";
			} else {
				if ($isgetfrom == "getfrom") {
					
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
			
					$area = $this->uri->segment(4);
					if ($area == null) {
						die("wrong way... go back");
					} else {
						$arcode     		= $this->uri->segment(4);
						$data['attendance'] = $this->Dashboard->getfrom($arcode); // mark here
						
					//	var_dump($data['attendance']); return;
						$data['issaved']	= $this->Dashboard->save_sync_data_log($data['attendance']['data'], $arcode);
						
					}
				}
			}
						
			$data['main_content'] = "v2views/hr_dashboard";
			$this->load->view('hrmis/admin_view',$data);
		}
		
		function displaydtr() {
			$this->load->model("v2main/Globalproc");
			
			$data['division'] =	$this->Globalproc->gdtf("Division",['1'=>1],"*"); 
			$data['office']   =	$this->Globalproc->gdtf("DBM_Sub_Pap",['1'=>1],"*"); 
			$this->load->view("v2views/hradmin/hrviewdtr",$data);
		}
		
		function printback() {
			echo $this->load->view("v2views/hradmin/printback",false,true);
		}
		
		function displayemps() {
			$type   = $this->input->post("type");
			$offdiv = $this->input->post("offdiv");
			$id 	= $this->input->post("id");
			
			$this->load->model("v2main/Globalproc");
			
			$where 	 = "employment_type='{$type}' and status=1";
			
			switch($offdiv) {
				case "off":
					$where .= " and DBM_Pap_id = '{$id}'";
					break;
				case "div":
					$where .= " and Division_id = '{$id}'";
					break;
				case "all":
					$where .= "";
					break;
			}
			
			$details = ["employee_id","f_name"];
			$emps 	 = $this->Globalproc->gdtf("employees", $where, $details);
			
			$data['emps']	= $emps;
			echo $this->load->view("v2views/employeelist",$data,true);
		}
		
		function whattodo(){
			$data['emp'] 	= true;
			$data['count'] 	= $this->input->post("numofemps");
			
			echo $this->load->view("v2views/hradmin/choosewhattodo",$data,true);
		}
		
		function adminpanel() {
			$from   = $this->input->post("datefrom");
			$to	    = $this->input->post("dateto");
			$empid  = $this->input->post("emp");
			$empbio = $this->input->post("empbio");
			
			$data   = false;
			
			$this->load->model("v2main/Globalproc");
			
			// 2019-10-31
			// 07/31/2019
			
			list($fyear,$fmo,$fd) = explode("-",$from);
			list($tyear,$tmo,$td) = explode("-",$to);
			
			$sql = "select 
							dsr.is_approved,
							cs.approval_status,
							dsr.dtr_coverage
						from hr_dtr_coverage as hdc 
						JOIN dtr_summary_reports as dsr on 
							hdc.dtr_cover_id = dsr.dtr_cover_id 
						JOIN countersign as cs on 
							dsr.sum_reports_id = cs.dtr_summary_rep
						where hdc.date_started = '{$fmo}/{$fd}/{$fyear}' 
								and hdc.date_ended = '{$tmo}/{$td}/{$tyear}'
							and dsr.employee_id = '{$empid}'";
			
			$a 			  = $this->Globalproc->__getdata($sql);
			
			if (count($a)==0) { die("No record found"); }
			
			$data['dets'] = $a;
			echo $this->load->view("v2views/hradmin/adminpanel",$data,true);
		}
		
		function massprint() {
			$this->load->model("v2main/Timerecords");
			$this->load->model("v2main/Globalproc");
				
			$calendar = $this->input->post("calendar_");
			$hrview	  = $this->input->post("hrviewdets"); 
			
			$from 	  = explode("-",$calendar)[0];
			$to 	  = explode("-",$calendar)[1];
			
			// 2016-07-08
			$this->Timerecords->setfrom( trim($from) );
			$this->Timerecords->setto( trim($to) );
			
			$dview 	  = "";
			
			foreach($hrview['selected'] as $hv) {
				$this->Timerecords->setemp($hv);
				
				$b_sql = "select 
							e.biometric_id, 
							e.f_name,
							p.position_name,
							e.employment_type,
							s.shift_name
						  from employees as e 
							JOIN positions as p 
								on e.position_id = p.position_id
							LEFT JOIN employee_schedule as es 
								on e.employee_id = es.employee_id
							LEFT JOIN shift_mgt as s 
								on es.shift_id = s.shift_id
							where e.employee_id = '{$hv}'";
				$bio   = $this->Globalproc->__getdata($b_sql);
				
				if (count($bio)==0){ break; }
				
				$this->Timerecords->setbio( $bio[0]->biometric_id );
				
				$dtr = $this->Timerecords->gettime();
				// loop from here
					$data['under'] = $this->Timerecords->return_("total_unders");
					$data['late']  = $this->Timerecords->return_("total_lates");
					$data['daysp'] = $this->Timerecords->return_("total_daysp");
					$data['hourp'] = $this->Timerecords->return_("total_hourp");
					
				
					$data['dtr']      = $dtr;
					$data['coverage'] = date("F d, Y", strtotime($from))."-".date("F d, Y",strtotime($to));
					$data['name']     = $bio[0]->f_name;
					$data['pos']	  = $bio[0]->position_name;
					$data['empid']	  = $hv;
					$data['emptype']  = strtolower($bio[0]->employment_type);
					$data['sked']	  = $bio[0]->shift_name;
					$dview		 	 .= $this->load->view('v2views/timetableprint',$data,true);
				// end 
			}
			echo $dview;
		}
		
		function mass_send() {
			$this->load->model("v2main/Timerecords");
			$this->load->model("v2main/Globalproc");
			
			$calendar = $this->input->post("calendar_");
			$hrview	  = $this->input->post("hrviewdets"); 
			
			$from 	  = explode("-",$calendar)[0];
			$to 	  = explode("-",$calendar)[1];

			$new_cal_a = date("j-n-Y",strtotime($from));
			$new_cal_b = date("j-n-Y",strtotime($to));
		
			$data['cals']  = $new_cal_a."_".$new_cal_b;
		
			foreach($hrview['selected'] as $hv) {
				$dview 	  = "";
				$this->Timerecords->setemp($hv);
				
				$b_sql = "select 
							e.biometric_id, 
							e.f_name,
							e.email_2,
							p.position_name,
							e.employment_type,
							s.shift_name
						  from employees as e 
							JOIN positions as p 
								on e.position_id = p.position_id
							LEFT JOIN employee_schedule as es 
								on e.employee_id = es.employee_id
							LEFT JOIN shift_mgt as s 
								on es.shift_id = s.shift_id
							where e.employee_id = '{$hv}'";
			
				$bio   = $this->Globalproc->__getdata($b_sql);
				
				if (count($bio)==0){ break; }
					$data['empid'] 	  = $hv;
					$data['bioid']	  = $bio[0]->biometric_id;
					$dview		 	 .= $this->load->view('dtrsend',$data,true);

					// send email
						$details['subject'] = 'DTR ('.date("F d, Y", strtotime($from))." - ".date("F d, Y", strtotime($to)).')';
						$details['from'] 	= 'Minda - HR';
						$details['to'] 		= $bio[0]->email_2;
						$details['message'] = $dview;
						$this->Globalproc->sendtoemail($details);
					// end send email
					
				// end 
			}
			
		}
		
		// temporarily replaces dtr() method
			function newdtrview() {
				$this->load->model("v2main/Timerecords");
					
				$date_ = $this->input->post("date_"); // 8/13/2019 - 8/22/2019
				
				$from  = explode("-",$date_)[0];
				$to    = explode("-",$date_)[1];
				
				$empid = $this->input->post("empid");
				
				$this->load->model("v2main/Globalproc");
				
				$b_sql = "select 
							e.biometric_id, 
							e.f_name,
							p.position_name,
							e.employment_type,
							s.shift_name
						  from employees as e 
							JOIN positions as p 
								on e.position_id = p.position_id
							LEFT JOIN employee_schedule as es 
								on e.employee_id = es.employee_id
							LEFT JOIN shift_mgt as s 
								on es.shift_id = s.shift_id
							where e.employee_id = '{$empid}'";
				
				$b   = $this->Globalproc->__getdata($b_sql);								
				/*
				$b	 = $this->Globalproc->get_details_from_table("employees", 
																['employee_id' => $empid],
																["biometric_id","f_name"]);
				*/
				$bio = $b[0]->biometric_id;
				
				$this->Timerecords->setfrom( trim($from) );
				$this->Timerecords->setto( trim($to) );
				$this->Timerecords->setemp( $empid );
				$this->Timerecords->setbio( $bio );
					
				$dtr 		   = $this->Timerecords->gettime();
				
				$data['under'] = $this->Timerecords->return_("total_unders");
				$data['late']  = $this->Timerecords->return_("total_lates");
				$data['daysp'] = $this->Timerecords->return_("total_daysp");
				$data['hourp'] = $this->Timerecords->return_("total_hourp");
				
				$data['dtr']  		= $dtr;
				$data['name'] 		= strtolower($b[0]->f_name);
				$data['pos']		= strtolower($b[0]->position_name);
				$data['emptype']	= strtolower($b[0]->employment_type);
				$data['sked']		= strtolower($b[0]->shift_name);
				
				$data['coverage']   = date("F d, Y",strtotime($from))." - ".date("F d, Y",strtotime($to));
				$dview		 = $this->load->view('v2views/timetableprint',$data,true);
					
				echo $dview;
			}
		// end 
		function dtr() {
			$this->load->model("v2main/Globalproc");
			$this->load->model('main/main_model');
			// called in ajax
			$data['title'] = '| Daily Time Records';

			if($this->session->userdata('is_logged_in')!=TRUE){
				  redirect('/accounts/login/', 'refresh');
			}else{
			
			$data['biometric_id'] 		= $this->session->userdata('biometric_id');
			$data['employee_id'] 		= $this->session->userdata('employee_id');
			$data['usertype'] 			= $this->session->userdata('usertype');
			$data['dbm_sub_pap_id'] 	= $this->session->userdata('dbm_sub_pap_id');
			$data['division_id'] 		= $this->session->userdata('division_id');
			$data['level_sub_pap_div'] 	= $this->session->userdata('level_sub_pap_div');
			$data['employment_type'] 	= $this->session->userdata('employment_type');
			$data['is_head'] 			= $this->session->userdata('is_head');
			
			/*
			$empdata 					  = $this->Globalproc->get_details_from_table("employees",
																					 ["employee_id"=>$data['employee_id']],
																					 ['e_signature']);
			$data['signature']["emp_sig"] = $empdata['e_signature'];
			*/
			
			$emp   = $this->input->post("emp_id");
			
			$data['retwat'] = $this->input->post("retwat");
			
			if ($emp != null || !empty($emp)) {
				
				$empid = $emp;
				
				$details = ['biometric_id',
							'DBM_Pap_id',
							'Division_id',
							'Level_sub_pap_div',
							'employment_type',
							'is_head',
							"e_signature",
							"area_id",
							"f_name"];
				$empdata = $this->main_model->array_utf8_encode_recursive( $this->Globalproc->get_details_from_table("employees",["employee_id"=>$empid],$details) );
				
				$data['biometric_id'] 		  = $empdata['biometric_id']; 
				$data['employee_id'] 		  = $empid; 
				$data['dbm_sub_pap_id'] 	  = $empdata['DBM_Pap_id']; 
				$data['division_id'] 		  = $empdata['Division_id']; 
				$data['level_sub_pap_div'] 	  = $empdata['Level_sub_pap_div']; 
				$data['employment_type'] 	  = $empdata['employment_type']; 
				$data['is_head'] 			  = $empdata['is_head']; 
				
				$utype = $this->Globalproc->get_details_from_table("users",["employee_id"=>$empid],['usertype']);
				
				$data['usertype'] 			  = $utype['usertype'];
				
				$data['get_margin'] = true;
				
				$data['signature']["emp_sig"] = $empdata['e_signature'];
				
				$token_details	   			  = $this->Globalproc->gdtf("countersign",
																		["emp_id"=>$empid,
																		 "conn"=>"and",
																		 "countersign_id"=>$this->input->post("cntid")],
																		['vercode',
																		 'dtr_summary_rep',
																		 'tobeapprovedby',
																		 'last_approving',
																		 'approval_status']);
			
				$data['token']['emp']	 	  = $token_details[0]->vercode."/".$token_details[0]->dtr_summary_rep."/".$empid;
				$data['token']['chief']		  = $token_details[0]->vercode."-".$token_details[0]->dtr_summary_rep."-".$token_details[0]->tobeapprovedby;
				$data['token']['last']		  = $token_details[0]->vercode."-".$token_details[0]->dtr_summary_rep."-".$token_details[0]->last_approving;
				$data['verificationcode']	  = $token_details[0]->vercode;
				
				// hide the print button beside the filter button 
				//	$data['print_hide'] = true;
				
				// cntid				
				$data['signature']['chief_sig'] = null;
				$data['signature']['last_sig']  = null;
				
				// user log in
					$data['uname'] = null;
					$data['upwd']  = null;
				// end 
				
				if($token_details[0]->approval_status == 1 || $token_details[0]->approval_status == 2) {
					$cs = $this->Globalproc->gdtf("employees",['employee_id'=>$token_details[0]->tobeapprovedby],["e_signature","email_2","f_name"]);
					$data['signature']['chief_sig'] = $cs[0]->e_signature;
					$data['chief_sig_name']			= $cs[0]->f_name;
				}
				
				if($token_details[0]->approval_status == 2) {
					$la = $this->Globalproc->gdtf("employees",['employee_id'=>$token_details[0]->last_approving],["e_signature","email_2","f_name"]);
					$data['signature']['last_sig']  = $la[0]->e_signature;
					$data['last_sig_name'] 			= $la[0]->f_name;
				}
				
				if ($token_details[0]->approval_status == 0) {
					$u_log 		   = $this->Globalproc->gdtf("users",["employee_id"=>$token_details[0]->tobeapprovedby],['Username','Password']);
					$data['uname'] = $u_log[0]->Username;
					$data['upwd']  = $u_log[0]->Password;
				} else if ($token_details[0]->approval_status == 1) {
					$u_log 		   = $this->Globalproc->gdtf("users",["employee_id"=>$token_details[0]->last_approving],['Username','Password']);
					$data['uname'] = $u_log[0]->Username;
					$data['upwd']  = $u_log[0]->Password;
				}
				
				//if (isset($this->input->post("coverage"))) {
					$coverage = $this->input->post("coverage");
					list($from,$to) = explode("-",$coverage);
					
					echo "<script>";
						echo "var getdate__  = true;";
						echo "var datefrom   = '{$from}';";
						echo "var dateto     = '{$to}';";
						echo "var bioid      = '{$data['biometric_id']}';";
						echo "var areaid     = '{$empdata['area_id']}';";
						echo "var empid      = '{$empid}';";
						echo "var empname    = '{$empdata['f_name']}';";
					echo "</script>";
					
					$from_ = date("m-d-Y",strtotime($from));
					$to_   = date("m-d-Y",strtotime($to));
					$data['accom_report'] = base_url()."/my/accomplishments/viewing/{$empid}/{$from_}/{$to_}";
				//}
			}
				
			$getemployees = $this->admin_model->getemployees();

			$getareas = $this->admin_model->getareas();

			$users = array();
		
			foreach ($getemployees as $rr) {
				$users[] = array('userid' => $rr->biometric_id , 'name' => $rr->f_name);
			}

			$data['areas'] = $getareas;

	
			$data['sub_pap_division_tree'] = $this->personnel_model->getsubpap_divisions_tree();
			$data['dtrformat'] 			   = $this->admin_model->getdtrformat();
			$data['dbusers'] 			   = $getemployees;
			
			$this->load->view("v2views/dtr_new_view", $data);

			}
		}
		
		function syncbio() {
			$this->load->model("v2main/Globalproc");
			
			$area_code 			   = $this->uri->segment(3);
			
			if ($area_code == null) {
				$data['area_null'] = "Area code is empty in the url. Please select an area.";
			} else {			
				$area   			   = $this->Globalproc->get_details_from_table("areas",["area_code"=>$area_code],["area_id","area_name"]);
				
				$data['name']		   = $area['area_name'];
				$data['area_code']	   = $area_code;
				
				$area_id 			   = $area['area_id'];
								
				$data['latest_update'] = $this->attendance_model->get_attemndance_logs_last_update($area_id); // mark 2
				$data['areas']		   = $this->admin_model->getareas();
			}
						
			$data['title'] 		   = '| Attendance Record';
			$data['main_content']  = 'hrmis/attendance/attendance_record_view';
			
			$this->load->view('hrmis/attendance/attendance_record_view');
		}
		
		function countersign() {
			$data['title'] 		   = '| Counter Sign';
			$data['main_content'] = "v2views/countersign";
			$this->load->view('hrmis/admin_view',$data);
		}

		
		function importdata() {
			if($this->session->userdata('is_logged_in')!=TRUE){
				  redirect('/accounts/login/', 'refresh');
			}else{
				$getareas = $this->admin_model->getareas();

				$data['title'] = '| Import Timelogs';
				$data['areas'] = $getareas;
				
				$this->load->view('hrmis/attendance/import_attendance_view', $data);
				// $this->load->view('hrmis/admin_view',$data);
			}
		}
		
		function signature() {
			if ($this->session->userdata('is_logged_in')!=TRUE) {
				redirect('/accounts/login/', 'refresh');
			} else {
				$this->load->view('hrmis/attendance/signature');
			}
		}
		
		function shifts() {
			$data['shifts'] =  $this->attendance_model->getshifts();
			$data['title'] = '| Shift Management';
			$this->load->view('hrmis/attendance/shift_schedule_view',$data);
		}
		
		function schedules() {
			$getallemployeeshift = $this->attendance_model->getallemployeeshift();
	
			$data['employees'] = $getallemployeeshift;
			$data['title'] = '| Employee Schedule';
			$data['shifts'] =  $this->attendance_model->getshifts();

			$this->load->view('hrmis/attendance/timetable_view',$data);
		}
		
		function offices() {
			$this->load->model('personnel_model');

			$data['sub_pap_division_tree'] = $this->personnel_model->getsubpap_divisions_tree();

			$data['title'] = '| Division Setup';
			$this->load->view('hrmis/personnel/department_view',$data);
		}
		
		function areas() {
			$getareas = $this->admin_model->getareas();

			$data['title'] = '| Area Setup';
			$data['areas'] = $getareas;
			$this->load->view('hrmis/personnel/area_view',$data);
		}
		
		function positions() {
			$this->load->model('personnel_model');

			$data['title'] = '| Positions';
			$data['get_positions'] = $this->personnel_model->getpositions('NULL');
			$this->load->view('hrmis/personnel/position_view',$data);
		}
		
		function employees($url = "") {
			if($this->session->userdata('is_logged_in')!=TRUE){
				  redirect('/accounts/login/', 'refresh');
			}else{
				$main_content = null;
				$getemployees = $this->admin_model->getemployees();
				$getareas = $this->admin_model->getareas();
				
				$this->load->model("v2main/Globalproc");
				
				$sql 			  = "select * from DBM_Sub_Pap as dsp left join Division as d on dsp.DBM_Sub_Pap_id = d.DBM_Sub_Pap_Id";
				$d 				  = $this->Globalproc->__getdata($sql);

				$sql_emp  		  = "select * from employees where employment_type = 'REGULAR' and status = '1' order by l_name ASC";
				$data['empdata']  = $this->Globalproc->__getdata($sql_emp);
				
				$off_divs 			   = [];
				$off_div_desc 		   = [];
				
				/*
				$data['off_divs_desc'] = [
					"OC"		=> "Office of the Chairman",
					"PPPD"		=> "Policy Planning and Project Development Office",
					"IPD - IRD"	=> "Investment Promotions and International Relations Office",
					"OFAS"		=> "Office of Finance and Administrative Services",
					"AMO"		=> "Office of Area Concerns and Project Management",
					"Others"	=> "Others",
					"ED"		=> "Office of the Executive Director"
					];
				*/
				
				foreach($d as $dd) {
					if (count($off_divs)==0) { // first instance 
						$off_divs[$dd->DBM_Sub_Pap_id][]	= [];
						array_push($off_divs[$dd->DBM_Sub_Pap_id],[$dd->Division_Desc,$dd->Division_Id]);
						
						$off_div_desc[$dd->DBM_Sub_Pap_id] = $dd->DBM_Sub_Pap_Desc;
					} else {
						if (in_array($dd->DBM_Sub_Pap_id,$off_divs)) {
							array_push($off_divs[$dd->DBM_Sub_Pap_id],[$dd->Division_Desc,$dd->Division_Id]);
						} else {
							$off_divs[$dd->DBM_Sub_Pap_id][] = [];
							array_push($off_divs[$dd->DBM_Sub_Pap_id],[$dd->Division_Desc,$dd->Division_Id]);
							
							if (!in_array($dd->DBM_Sub_Pap_id,$off_div_desc)) {
								$off_div_desc[$dd->DBM_Sub_Pap_id] = $dd->DBM_Sub_Pap_Desc;
							}
						}
					}
				}
				
				// var_dump($off_div_desc);
				// echo "<br/>";
				// echo "<br/>";
				// var_dump($off_divs);
				
				$data['off_divs'] 	   = $off_divs;
				$data['off_divs_desc'] = $off_div_desc;
				
				$data['title'] = '| Employees';

				if($url == ''){
					
					$data['employees'] = $getemployees;
					$data['areas'] = $getareas;
					$data['sub_pap_division_tree'] = $this->personnel_model->getsubpap_divisions_tree();
					$data['positions'] = $this->personnel_model->getpositions('NULL');
					$main_content      = 'hrmis/personnel/employee_view';

				}else if ($url == 'import'){

					$data['areas'] = $getareas;

					$data['page_type'] = 'Edit';
					$main_content  = 'hrmis/personnel/import_employee_view';	

				}

			$this->load->view($main_content,$data);
			
			}
		}
		
		function summary() {
			if($this->session->userdata('is_logged_in')!=TRUE){
			    redirect('/accounts/login/', 'refresh');
			}else{
				$data['title'] = '| Summary Reports';
				$data['usertype'] = $this->session->userdata('usertype');

				$this->load->model('personnel_model');
				$this->load->view('hrmis/reports/summary_view',$data);
			}
		}
		
		function newsummary() {
			if($this->session->userdata('is_logged_in')!=TRUE){
			    redirect('/accounts/login/', 'refresh');
			} else {
				$data['title'] = '| Summary Reports';
				$data['usertype'] = $this->session->userdata('usertype');
					
				// $data['main_content']	= 'hrmis/reports/newsummary';
				$this->load->view('hrmis/reports/newsummary',$data);
				// $this->load->view('hrmis/admin_view',$data);
			}
		}
		
		function displaydates(){
			$this->load->model("v2main/Globalproc");
			$emptype 	= $this->input->post("info")['emptype'];
			$empdets  	= $this->Globalproc->gdtf("hr_dtr_coverage",["employment_type"=>$emptype],"*");
			
			$a = array_map(function($b){
				$b->date_started  = date("F d, Y", strtotime($b->date_started));
				$b->date_ended    = date("F d, Y", strtotime($b->date_ended));
				$b->date_deadline = date("F d, Y", strtotime($b->date_deadline));
				return $b;
			},$empdets);
			echo json_encode($empdets);
		}
		
		function addnewdate() {
			$emp_type = "";
			
		}
		
		function s_active() {
			$this->load->model("v2main/Globalproc");
			$dtrcoverid = $this->input->post("info")['dtrcoverid'];
			$status	 	= $this->input->post("info")['status'];
			
			$stat = null;
			if ($status == "1" || $status == 1) {
				$stat = 0;
			} else if ($status == "0" || $status == 0) {
				$stat = 1;
			}

			$values = ["is_active"=> $stat];
			$where  = ['dtr_cover_id'=>$dtrcoverid];
			
			echo json_encode( $this->Globalproc->__update("hr_dtr_coverage",$values,$where) );

		}
		
		function a_submit(){
			$this->load->model("v2main/Globalproc");
			$dtrcoverid = $this->input->post("info")['dtrcoverid'];
			$status	 	= $this->input->post("info")['status'];
			
			$stat = null;
			if ($status == "1" || $status == 1) {
				$stat = 0;
			} else if ($status == "0" || $status == 0) {
				$stat = 1;
			}
			
			$values = ["is_allow_to_submit" => $stat];
			$where  = ['dtr_cover_id'    => $dtrcoverid];
			
			echo json_encode( $this->Globalproc->__update("hr_dtr_coverage",$values,$where) );

		}
		
		function getemployees() {
			$info 	  = $this->input->post("info");
			$office   = $info['office'];
			$division = $info['division'];
			
			$this->load->model("v2main/Globalproc");
			$data = $this->Globalproc->getemployees($office, $division);
			
			echo json_encode($data);
		}
		
		function changesig() {
			$info     = $this->input->post("info");
			$office   = $info['office'];
			$division = $info['division'];
			$emp	  = $info['emp'];
			
			/*
			$office = 1;
			$division = 0;
			$emp = 50;
			*/
			
			$this->load->model("v2main/Globalproc");
			// changesignatory($office, $division, $emp)
			
			echo json_encode($this->Globalproc->changesignatory($office, $division,$emp));
		}
		
		function changediv() {
			$info     = $this->input->post("info");
			$office   = $info["office"];
			$division = $info['division'];
			
			$emps 	  = $info['emps'];
			
			$this->load->model("v2main/Globalproc");
			echo json_encode($this->Globalproc->changediv($office,$division,$emps));
		}
		
		function getsigs() {
			$this->load->model("v2main/Globalproc");
			$a = $this->Globalproc->get_signatories(1444);
			var_dump($a);
		}
		
		function checkfornotimeinps($date_='') {
			
			// echo "<p style='text-align:center; font-size:20px; background:#f1f1f1;'> DO NOT CLOSE THIS WINDOW UNTIL PROCESSING IS DONE!!! </p>";
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
			
			$this->load->model("v2main/V2newmain");
			$this->load->model("v2main/Globalproc");
			// heckforpassslip($date_)
			
			if ($date_ == '') {
				$date_ = date("n/j/Y", strtotime("-1 days"));
			} else {
				// date_ = date("m/d/Y", strtotime($date_));
				list($mo,$d, $y) = explode("-", $date_);
				$date_ = $mo."/".$d."/".$y;
			}
			
			$ps = $this->V2newmain->checkfornotimepassslip($date_);
	
			if (count($ps)>0) {
				foreach($ps as $p) {
				
					$email   	 = $p->email_2;
					$date	 	 = date("F d, Y", strtotime($p->checkdate));
					$timeout 	 = $p->time_out;
					
					$empid   	 = $p->employee_id;
					$theps   	 = $this->Globalproc->get_timein_am($empid, $date);
			
					$thein   	 = $theps[0];
					$shifts      = $theps[1];
					
					$amin_exact  = $shifts[0];
					$amin_flex   = $shifts[1];
					
					$pmout_exact = $shifts[2];
					$pmout_flex  = $shifts[3];
					
					$exactid     =  $p->exact_id;
					$timein	 	 = null;
					if ($thein != false) {
						if ( strtotime($thein) <= strtotime($amin_exact) ) {
							$timein = $pmout_exact;
						}
						
						if ( strtotime($thein) <= strtotime($amin_flex) 
								&& strtotime($thein) >= strtotime($amin_exact) ) {
									list($hour,$mins) = explode(":",$thein);
									list($out_hr,$out_mn) = explode(":",$pmout_flex);
									$timein  = $out_hr.":".$mins." PM";
						}
						
						if (strtotime($thein) >= strtotime($amin_flex)) {
							$timein = $pmout_flex;
						}
					} else if ($thein == false) {
						$timein = $pmout_flex;
					}
					
					// $template = $this->load->view("v2views/forms/psview", $data);
					$template = "<div style='background:#9a9a9a;'>
									<div style='max-width:532px;margin:0 auto; font-family:arial; background: #fff;'>
										<table style='width: 100%;'>
											<tr>
												<td style='text-align: center;'>
													<div>
														<div>
															<div>
																<img src='".base_url()."assets/images/minda_logo.png' style='width: 115px;'/>
															</div>
														</div>
														<p style='color: #e28427; font-size: 31px; font-weight: bold;'> ATTENTION!!! </p>
														<hr style='height: 0px; border: 0px; border-top: 1px solid #d4d4d4; margin-bottom: 30px;'/>
														<p style='border-bottom: 1px solid #dadada; padding-bottom: 27px;'> You applied for Pass Slip but did not return. </p>
														<table style='width: 100%;'>
															<tr>
																<td style='text-align:right; min-width:50px; padding:5px;'> Pass slip date: </td>
																<td style='text-align:left; padding-left:10px; color:#e28427; font-weight:bold; padding:5px;'> {$date} </td>
															</tr>
															<tr>
																<td style='text-align:right; min-width:50px; padding:5px;'> Time Out: </td>
																<td style='text-align:left; padding-left:10px; color:#e28427; font-weight:bold; padding:5px;'> {$timeout} </td>
															</tr>
															<tr>
																<td style='text-align:right; min-width:50px; padding:5px;'> Time In: </td>
																<td style='text-align:left; padding-left:10px; color:#e28427; font-weight:bold; padding:5px;'> {$timein} <span style='color:red; font-weight:normal;font-style: italic;'> (Did not return) </span> </td>
															</tr>";
																if ($thein == false) {
																	$template .= "<tr>
																					<td style='text-align:left; padding-left:10px; color:#e28427; font-weight:bold; padding:5px;' colspan='2'> 
																						<p> We found no morning time from both of your AMS and biometrics. We used your flexi time-out instead. 
																							Don't forget to log your time next time. 
																						</p>
																					</td>
																				  </tr>";
																}
					$template .= "
														</table>
														
														<p style='line-height: 26px; width: 70%; margin: 15px auto 50px; color: #929292;'> You don't need to do anything, but if you find your time in wrong, please inform the HR. </p>
													</div>
												</td>
											</tr>
										</table>
									</div>
								</div>";
					
					// save to checkexact 
						$update = $this->Globalproc->__update("checkexact",['time_in'=>$timein],['exact_id'=>$exactid]);
					// end saving 
					
					if ($update) {
						$details['to']		= $email;
						$details['from']	= "HR";
						$details['subject']	= "Pass slip: Attention";
						$details['message'] = $template;
						// echo $template;
						$email_1 = $this->Globalproc->sendtoemail($details);
						
						echo "email submitted to {$email}. <br/>";
					} else {
						echo "There is something wrong in updating the data.";
					}
					sleep(5);
				}
				// 
			} else {
				echo "no pass slip found.";
			}
		}
		
		public function reports() {	
			$this->load->view("v2views/reportsview");
		}
		
		// set the status
		public function setstatus() {
			$info   = $this->input->post("info");
			$module = $info['module'];
			$field  = $info['field'];
			$value  = $info['value'];
			$status = $info['status'];
			
			$this->load->model("v2main/Globalproc");
			// __save($table, $values) {
				
			$values = ["settingmodule"	=> $module,
					   "settingfield"	=> $field,
					   "settingvalue"	=> $value,
					   "status"			=> $status];
			
			$ret = $this->Globalproc->__save("settings",$values);
			
			echo json_encode($ret);
		}
		
		public function addnew() {
			$info     = $this->input->post("info");
			$seldate  = $info['date_'];
			$emptype  = $info['emptype_'];
			$deadline = $info['deadline_'];
			
			$this->load->model("v2main/Globalproc");
			// 11/10/2019 - 24/10/2019
			list($from, $to) = explode("-",$seldate);
			
			$dateindex = $this->Globalproc->__getdata("select max(date_index) as maxin from hr_dtr_coverage where employment_type = '{$emptype}'");
			$di 	   = 0;
			
			if (count($dateindex)>0){
				$di = $dateindex[0]->maxin + 1;
			}
			
			$values = ['date_started'	    => trim($from),
					   'date_ended'  	    => trim($to),
					   'date_deadline' 	    => trim($deadline),
					   'employment_type'    => $emptype,
					   'is_active' 		    => true,
					   'is_allow_to_submit' => true,
					   'date_index'			=> $di];
				
			$save = $this->Globalproc->__save("hr_dtr_coverage",$values);
			
			echo json_encode($values);
		}
		
		public function delete__() {
			$info  = $this->input->post("info");
			$hrcov = $info['coverid'];
			
			$this->load->model("v2main/Globalproc");
			
			$delete = $this->Globalproc->run_sql("delete from hr_dtr_coverage where dtr_cover_id = '{$hrcov}'");
			
			echo json_encode($delete);
		}
		// end
		
		public function managetimelog() {
			
			$data['title']		  = "Timelog Management";
			$data['main_content'] = "v2views/hradmin/managetimelog";
			
		//	$file = "file:///C:/Users/Public/Documents/attendancerecord/timelog.xml";
		//	echo $file;
			
			// $file = "C:/Users/MinDA-PC2/Documents/c/test.c";
			
			$this->load->model("v2main/Globalproc");
			
			$empid    = $this->session->userdata('employee_id');
			$isfocal = $this->Globalproc->gdtf("employees",['employee_id'=>$empid],"isfocal");
			
			if (count($isfocal)>0) {
				if ($isfocal[0]->isfocal == false) {
					die("not allowed");
				}
			} else {
				die("not allowed");
			}

			if (isset($_POST['uploadfile'])) {
				$name 	 = $_FILES['tlfile']['name'];
				$type 	 = $_FILES['tlfile']['type'];
				$filetmp = $_FILES['tlfile']['tmp_name'];
			//	$fileext = strtolower(end(explode(".",$_FILES['tlfile']['name'])));
				
				$allowed_ext = array("text/xml");
				
				if (!in_array($type,$allowed_ext)) {
					echo "not allowed";
				} else {
					$newfilename = "uploads/".date("mdY")."_".$name;
					
					move_uploaded_file($filetmp,$newfilename);
					
					$root = $_SERVER['DOCUMENT_ROOT'];
					
					$file = fopen($root."/".$newfilename,"r") or die("unable to open the file");
					
					$xml = simplexml_load_file($root."/".$newfilename) or die("Error: cannot open file");
					
					$information = $xml->ROWDATA->ROW;
					
					$inf = $this->Globalproc->savetocheckinout($information);
					//$inf = 1;
					
					$data['return'] = $inf;
				}
			}
			
			if (isset($_POST['gfilebtn'])) {
				date_default_timezone_set("Asia/Manila");

				$name     = $_FILES['googlefile']['name'];
				$type     = $_FILES['googlefile']['type'];
				$filetmp = $_FILES['googlefile']['tmp_name'];
				
				$newfilename = "uploads/".date("mdY")."_timelog_".$name;
				move_uploaded_file($filetmp,$newfilename);
				
				$root 		= $_SERVER['DOCUMENT_ROOT'];
				
				$file 		= fopen($root."/".$newfilename,"r") or die("unable to open the file");
				
				$count 		= 0;
				
				$select 	= "select employee_id, biometric_id, id_number, email_2, f_name from employees where ";
				$theids 	= [];
				
				$ins_arr 	= [];
				$accom   	= [];
				$healthcons = [];
				
				$first = true;
				while(!feof ($file) ) {
					$tfile = fgetcsv($file);
					
					if ($count > 0) {
						// index 
						// 0 = time and date
						// 1 = working environment 
						// 2 = time of the day
						// 3 = log type
						// 4 = accomplishment report
						// 5 = ID number 
						// 6 = temperature
						// 7 = health issues
						
						$text = "";
						$pattern = "/-/i";
						$idnumber = trim(preg_replace($pattern,"",$tfile[5])," ");
						
						if (strlen($idnumber) > 0) {
							if (!in_array($idnumber,$theids)) {
								$theids[] = $idnumber;
								
								if (!feof($file) && !$first) {
									$select .= " or ";
								}
								
								$select .= "id_number = '{$idnumber}'";
								
							}
						}
						
						$ctype = "";
						if ($tfile[3]=="Time In") {
							$ctype = "C/In";
						} else if ($tfile[3]=="Time Out") {
							$ctype = "C/Out";
						} else {
							$ctype = "undefined";
						}
						
						$loc_arr = [
							"id_number"		=> $idnumber,
							"PIN"  			=> "",
							"MachineAlias" 	=> "Davao",
							"CHECKTIME" 	=> date("m/d/Y h:i A", strtotime($tfile[0])),
							"CheckType" 	=> $ctype,
							"date_added"    => null,
							"date_modified" => null,
							"status"		=> null,
							"is_active"		=> null
						];
						array_push($ins_arr,$loc_arr);
						
						if (strlen($tfile[4])>0) {
							$acc_loc = [
								"userid" 	=> null,
								"accomp" 	=> $tfile[4],
								"id_number"	=> $idnumber,
								"date_"		=> date("m/d/Y",strtotime($tfile[0]))
							];
							array_push($accom, $acc_loc);
						}
						
						if (strlen($idnumber) > 0) {
							array_push($healthcons,["idnumber"=>$idnumber,"timelog"=>date("m/d/Y h:i A", strtotime($tfile[0])),"temp"=>$tfile[6],"hiss"=>$tfile[7],"name"=>null]);
						}
						
						$first = false;
					}
					
					
					$count++;
				}
				
				//var_dump($healthcons); return;
				// get the data using the select statement
					// echo $select; return; 
					$empsdetails = $this->Globalproc->__getdata($select);
				// end
				//echo count($empsdetails);
				//var_dump($empsdetails);
				//return;
				
				for($a=0;$a<=count($ins_arr)-1;$a++) {
					for($i=0;$i<=count($empsdetails)-1;$i++) {
						if ($ins_arr[$a]['id_number'] == $empsdetails[$i]->id_number) {
							$ins_arr[$a]['PIN'] = $empsdetails[$i]->biometric_id;
						}
					}
				}
				
				for($x = 0; $x<=count($accom)-1;$x++) {
					for($m = 0;$m<=count($empsdetails)-1;$m++) {
						if($accom[$x]["id_number"]==$empsdetails[$m]->id_number) {
							$accom[$x]['userid'] = $empsdetails[$m]->employee_id;
						}
					}
				}
				
				for($z=0;$z<=count($healthcons)-1;$z++) {
					for($q=0;$q<=count($empsdetails)-1;$q++) {
						if($healthcons[$z]['idnumber'] == $empsdetails[$q]->id_number) {
							$healthcons[$z]['name']	= $empsdetails[$q]->f_name;
						}
					}
				}
				
				// return;
				
				$data['return'] = $ret = $this->Globalproc->savetocheckinout($ins_arr);
				 //echo $ret; return;
				//$ret = true;
				
				if ($ret) {
					$data['return'] = $this->Globalproc->savetoaccom($accom); 
				//	echo $data['return'];
					// $emails = [];
					$to = "";
					$htmlformat = $this->load->view("v2views/emailtemplate/notifyemps.php",0, true);
					$count = 0;
					
					$details = [
						"to" 		=> null,
						"message" 	=> $htmlformat,
						"subject"   => "Minda Employee Tracker",
						"from"		=> "HR - Minda"
					];
					
					$sent = false;
					foreach($empsdetails as $ed) {
						// $to .= $ed->email_2;
						// $to .= ($count == count($empsdetails)-1)?"":",";
						// $count++;
						$details['to']  = $ed->email_2;
						// $sent = $this->Globalproc->sendtoemail($details);
					}
					
					$data['return'] = $sent;
					
					// email to health committee
						$tbl = $this->load->view("v2views/emailtemplate/healthcons.php",["data"=>$healthcons],true);
						
						$sendtohealcom = [
							"to" 		=> "jon.miral@minda.gov.ph,romeo.montenegro@minda.gov.ph,cecil.trino@minda.gov.ph,joan.barrera@minda.gov.ph,rolando.pinsoy@minda.gov.ph",
							//"to"		=> "alvinjay.merto@minda.gov.ph",
							"bcc" 		=> "alvinjay.merto@minda.gov.ph",
							"message" 	=> $tbl,
							"from"		=> "Minda HR",
							"subject"   => "Temperature and health issues of minda employees"
						];
						
						//$data['return'] = $this->Globalproc->sendtoemail($sendtohealcom);
					// end 
					// -------------------------------------------------------
					// notify individual emps about timelog and accom posted.
					
					// end 
				}
				
			}
			
			// $file = fopen($file,"r") or die("Unable to open the file");
			
			// echo fread($file,filesize($file));
			$this->load->view('hrmis/admin_view',$data);
		}
		
		public function getdup() {
			$data['title']		  = "Timelog Management";
			$data['main_content'] = "v2views/hradmin/managetimelog";
			
		//	$file = "file:///C:/Users/Public/Documents/attendancerecord/timelog.xml";
		//	echo $file;
			
			// $file = "C:/Users/MinDA-PC2/Documents/c/test.c";
			echo "<form method='POST' enctype='multipart/form-data'>
					<input type='file' name='tlfile'/>
					<hr style='margin: 9px 0px;'/>
					<input type='submit' value='Push' class='btn btn-primary' id='btnpush' name='uploadfile'/>
				</form>";
			
			$this->load->model("v2main/Globalproc");
			
			$empid    = $this->session->userdata('employee_id');
			$isfocal = $this->Globalproc->gdtf("employees",['employee_id'=>$empid],"isfocal");
			
			if (count($isfocal)>0) {
				if ($isfocal[0]->isfocal == false) {
					die("not allowed");
				}
			} else {
				die("not allowed");
			}

			if (isset($_POST['uploadfile'])) {
				$name 	 = $_FILES['tlfile']['name'];
				$type 	 = $_FILES['tlfile']['type'];
				$filetmp = $_FILES['tlfile']['tmp_name'];
			//	$fileext = strtolower(end(explode(".",$_FILES['tlfile']['name'])));
				
				$allowed_ext = array("text/xml");
				
				if (!in_array($type,$allowed_ext)) {
					echo "not allowed :: getdup document";
				} else {
					$newfilename = "uploads/".date("mdY")."_".$name;
					
					move_uploaded_file($filetmp,$newfilename);
					
					$root = $_SERVER['DOCUMENT_ROOT'];
					
					$file = fopen($root."/".$newfilename,"r") or die("unable to open the file");
					
					$xml = simplexml_load_file($root."/".$newfilename) or die("Error: cannot open file");
					
					$information = $infs = $xml->ROWDATA->ROW;
					echo count($information)."<br/>";
					foreach($information as $d) {
						$userid   = $d['USERID'];
						$macalias = $d['MachineAlias'];
						$areaid   = null;
						$ctime 	  = date("n/j/Y g:i A", strtotime($d['CHECKTIME']));
						$ctype 	  = $d['CheckType'];
						
						foreach($infs as $dd) {
							$bioid 	  = $dd['PIN'];
							
							echo $userid."=".$bioid."<br/>";
						
						}
					}
					
				//	$inf = $this->Globalproc->savetocheckinout($information);
					$inf = 1;
					
					$data['return'] = $inf;
				}
			}
			
						
			// $file = fopen($file,"r") or die("Unable to open the file");
			
			// echo fread($file,filesize($file));
			//$this->load->view('hrmis/admin_view',$data);
		}
		
		public function php_info() {
			echo phpinfo();
		}
		
		public function decodetojson() {
			$this->load->model("v2main/Globalproc");
			
			$a = $this->Globalproc->__getdata("select 
e.f_name, e.email_2, u.Username, p.position_name	   
 from employees as e JOIN users as u on e.employee_id = u.employee_id join positions as p on e.position_id = p.position_id
 where e.status = 1");
			
			echo json_encode($a);
		}
		
		public function displayintable_emps() {
			$this->load->model("v2main/Globalproc");
			
			$a = $this->Globalproc->__getdata("select 
e.f_name, p.position_name, d.Division_Desc, e.gender, e.employment_type, e.DBM_Pap_id, dpi.DBM_Sub_Pap_Desc
 from employees as e JOIN users as u on e.employee_id = u.employee_id JOIN positions as p on e.position_id = p.position_id
 LEFT JOIN Division as d on e.Division_id = d.Division_Id
 LEFT JOIN DBM_Sub_Pap as dpi on e.DBM_Pap_id = dpi.DBM_Sub_Pap_id
 where e.status = 1 order by f_name");
 
		echo "<table>";
			foreach($a as $aa) {
				echo "<tr>";
					echo "<td>".$aa->f_name."</td>";
					echo "<td>".$aa->position_name."</td>";
					echo "<td>".$aa->DBM_Sub_Pap_Desc."</td>";
					echo "<td>".$aa->Division_Desc."</td>";
					echo "<td>".$aa->gender."</td>";
					echo "<td>".$aa->employment_type."</td>";
				echo "</tr>";
			}
		echo "</table>";
		}
		
		function utilities() {
			$data['title'] = '| HR Utilities';
			$data['main_content'] = "v2views/utilities.php";
			$data['headscripts']['style'][]	= base_url()."v2includes/utilities/style.css";
			$data['headscripts']['js'][]	= base_url()."v2includes/utilities/util.js";
			
			$this->load->view('hrmis/admin_view',$data);
		}
		
		function addnewto() {
			$a = $this->load->view("v2views/utils/addnew.php",'',true);
			echo $a;
		}
		
		function searchto() {
			$a = $this->load->view("v2views/utils/search.php",'',true);
			echo $a;
		}
	
		function saveasnewent() {
			$this->load->model("v2main/Globalproc");
			
			$data     = $this->input->post("a");
			
			$owner    = $this->session->userdata('employee_id');
			$fullname = $this->session->userdata("full_name");
			
			list($start,$anchor,$year,$month,$count) = explode("-",$data['contnum']);
			
			
/*			
			$target_dir    = "uploads/";
			$basename      = basename($_FILES["thefile"]["name"]);
			$transposedn   = md5($basename);
			$imageFileType = strtolower(pathinfo($basename,PATHINFO_EXTENSION));
			$target_file   = $target_dir.$transposedn.$imageFileType;
*/

			$basename    = "test";
			$target_file = "testing";
			
			$officeunder = $data['officeunder'];
			$colorid     = 0;
			switch($officeunder) {
				case "OC":		$colorid = 10; break;
				case "OED":		$colorid = 4; break;
				case "DED":		$colorid = 9; break;
				case "OFAS":	$colorid = 5; break;
				case "PPPDO":	$colorid = 7; break;
				case "IPPAO":	$colorid = 11; break;
				case "AMO":		$colorid = 6; break;
				case "IPURE":	$colorid = 8; break;
			}
			
			$values = [	"datefiled"		=> date("m/d/Y H:i:s A"),
						"controlno"		=> $data['contnum'],
						"nameoftravs"	=> $data['nmoftravs'],
						"dateoftrav"	=> $data['dtoftrav'],
						"dateoftrav_to" => $data['dtoftrav_to'],
						"natoftrav"		=> $data['natoftrav'],
						"destination"	=> $data['dest'],
						"purpose"		=> $data['purpose'],
						"reqby"			=> $data['reqby'],
						"status"		=> 0,
						"isforeign"     => $data['isforeign'],
						"owner"			=> $owner,
						"theyear"		=> $year,
						"thecount"		=> $count,
						"theoffice"		=> $anchor,
						"themonth"		=> $month,
						"attfile"		=> $basename,
						"location"		=> $target_file,
						"encodedby"		=> $fullname,
						"officeunder"	=> $officeunder,
						"colorid"		=> $colorid];
			 
			//if (move_uploaded_file($_FILES["thefile"]["tmp_name"], $target_file)) {
				$save = $this->Globalproc->__save("travelorders",$values);
				echo json_encode($save);
			// }
		}
		
		function findnow() {
			$this->load->model("v2main/Globalproc");
			
			$cat = $this->input->post("cat_");
			$key = trim($this->input->post("key_")," ");
			
			$sql = "select * from travelorders where {$cat} like '%{$key}%'";
			
			$data['dets'] = $this->Globalproc->__getdata($sql);
			
			$a = $this->load->view("v2views/utils/thelist.php",$data, true);
			
			echo $a;
			
		}
		
		function showall() {
			$this->load->model("v2main/Globalproc");
			
			$data['dets'] = $this->Globalproc->gdtf("travelorders",["status"=>0],"*");
			
			$a = $this->load->view("v2views/utils/thelist.php",$data, true);
			echo $a;
		}
		
		function cancel() {
			$this->load->model("v2main/Globalproc");
			
			$toid = $this->input->post("toid_");
			
			$values = ['status' => "1"];
			$where  = ["toid" => $toid];
			$a = $this->Globalproc->__update("travelorders",$values,$where);
			echo json_encode($a);
		}
		
		function showemployees() {
			$this->load->model("v2main/Globalproc");
			
			$sql = "select id_number, f_name, email_2 from employees where status = 1 order by f_name asc"; // and employment_type = 'REGULAR' 
	
			$a = $this->Globalproc->__getdata($sql);
			
			echo "<table>";
				echo "<thead>";
					echo "<th> ID Number </th>";
					echo "<th> Full Name</th>";
					echo "<th> Email</th>";
				echo "</thead>";
				foreach($a as $aa) {
					echo "<tr>";
						echo "<td> {$aa->id_number} </td>";
						echo "<td> {$aa->f_name} </td>";
						echo "<td> {$aa->email_2} </td>";
					echo "</tr>";
				}
			echo "</table>";
		}
		
		function approvenow() {
			$this->load->model("v2main/Globalproc");
			
			$toid = $this->input->post("toid_");
			
			$update = ["status"=>1];
			$where  = ["toid"=>$toid];
			
			// __update($table, $values, $where)
			$a      = $this->Globalproc->__update("travelorders", $update, $where);
			echo json_encode($a);
		}
		
		function converttime() {
			$this->load->model("v2main/Globalproc");
			
			$sql = "update checkinout set checktime = '".date("n/j/Y h:i:s A")."'";
		}
		
		function testtesttest() {
			echo $this->session->userdata('database_default');
		}
		
	}
