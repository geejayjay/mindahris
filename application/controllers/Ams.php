<?php 
	
	class Ams extends CI_Controller {
		
		public function index() {
			$data['title']	 				= "| Attendance Monitoring v2";
			$data['headscripts']['style'][] = "https://fonts.googleapis.com/css?family=Source+Sans+Pro' rel='stylesheet'";
			$data['headscripts']['style'][]	= base_url()."v2includes/style/ams.style.css";
			$data['headscripts']['js']		= base_url()."v2includes/js/ams.proc.js";
			
			$DB2 = $this->load->database('sqlserver', TRUE);
			
			/*
			if (isset($_POST['unlock'])) {
				$username = $_POST['username'];
				$password = md5($_POST['password']);
				
				$sql = "select u.*, e.f_name, e.firstname, e.l_name from users as u
						LEFT JOIN employees as e on 
							u.employee_id = e.employee_id
						where u.Username = '{$username}' and u.Password = '{$password}'";
				
				
				
				$ret = $this->db->query($sql)->result();
				$this->db->close();
				
				if (count($ret)==0) {
					die("User not recognized.");
					return;
				} else {
					// $this->speciallogin();
					
					$user_session = array(
						'employee_id'   		=> $ret[0]->employee_id,
						'username' 	    		=> $ret[0]->Username,
						'usertype' 				=> $ret[0]->usertype,
						'full_name' 			=> $ret[0]->f_name,
						'first_name' 			=> $ret[0]->firstname,
						'last_name' 			=> $ret[0]->l_name,
						'biometric_id'  		=> "2sw248g69w598w65w5",
						'area_id' 				=> 1,
						'area_name' 			=> "Philippines",
						'ip_address' 			=> $_SERVER["REMOTE_ADDR"],
						'is_logged_in' 	  		=> TRUE,
						'database_default' 		=> 'sqlserver',
						'employment_type' 		=> "REGULAR",
						'employee_image' 		=> NULL,
						'level_sub_pap_div' 	=> 1,
						'division_id' 			=> 0,
						'dbm_sub_pap_id' 		=> 0,
						'is_head' 				=> 1,
						'office_division_name'  => NULL,
						'position_name' 		=> NULL
					);
				
					$this->session->set_userdata($user_session);
					
				}
			
				if ($this->session->userdata('is_logged_in') != TRUE) {
					$base = base_url();
					redirect("{$base}accounts/login", 'refresh');
				} 
			} else {
				if ($this->session->userdata('is_logged_in') != TRUE) {
					$this->load->view("v2views/proclogin", $data);
					return;
				} 
			}
			*/
			
			/*
			if ($this->session->userdata("employee_id") == 389 ||
				$this->session->userdata("employee_id") == 50 || 
				$this->session->userdata("employee_id") == 149 || 
				$this->session->userdata("employee_id") == 29 || 
				$this->session->userdata("employee_id") == 27 ) {
					// 389 = the programmer 
					// 50  = head IT 
					// 29  = head HR 
					// 27  = director of OFAS
					$data['headscripts']['js']		= base_url()."v2includes/js/ams.proc.js";
					$this->load->view("v2views/ams_view", $data);
			} else {
				$this->session->unset_userdata('is_logged_in');
				$this->session->unset_userdata('employee_id');
				$this->session->unset_userdata('username');
				$this->session->unset_userdata('usertype');
				$this->session->unset_userdata('full_name');

				$this->session->unset_userdata('area_id');
				$this->session->unset_userdata('ip_address');
				$this->session->unset_userdata('area_name');
				$this->session->unset_userdata('database_default');

				$this->session->sess_destroy();
						
				die("Im sorry but you are not allowed in here. You are being logged out.");
						
			}
			*/
			$this->load->view("v2views/ams_view", $data);
		}
		
		public function procidno(){
			$id    = $this->input->post("info")['idno'];
			
			$this->speciallogin();
			// 10/6/2017
			$date_ = date("m/d/Y");
			
			$dateandtime = $date_; //." ".$time;
			/*
			$check = "select 
							e.employee_id,
							e.f_name,
							cea.checkdate,
							cea.a_in,
							cea.a_out,
							cea.p_in,
							cea.p_out,
							d.Division_Desc,
							p.position_name
							from employees as e
							left join checkexact_ams as cea
							on e.employee_id = cea.employee_id
							left join Division as d 
							on e.Division_id = d.Division_Id
							left join positions as p 
							on e.position_id = p.position_id
						where e.id_number = '{$id}' and CONVERT(datetime, cea.checkdate) = '{$date_}'";
			*/
			$check = "select 
							e.employee_id,
							e.f_name,
							d.Division_Desc,
							p.position_name
						from employees as e 
							left join Division as d 
						on e.Division_id = d.Division_Id 
							left join positions as p 
						on e.position_id = p.position_id
						where e.id_number = '{$id}'";
						
			$this->load->model("v2main/Globalproc");
			$returned = $this->Globalproc->__getdata($check);
			
			$data = [];
			$ret  = null;
			if (count($returned)==0) {
				$ret = 0;
			} else {
				/*
				$col    = null;
				$ams_id = null;
				if ( $returned[0]->a_in == NULL ) {
					// save to a_in
					// 10/6/2017 12:00:00 PM
					$ret  = $this->Globalproc->__save("checkexact_ams",['a_in'=>$dateandtime,
																		'employee_id'=>$returned[0]->employee_id,
																		'checkdate'=>$date_]);
					$col    = "a_in";
					$ams_id = $this->Globalproc->getrecentsavedrecord("checkexact_ams", "c_ams_id")[0]->c_ams_id;
				} else if ( $returned[0]->a_out == NULL ) {
					// save to a_out 
					$ret	 = $this->Globalproc->__update("checkexact_ams",["a_out"=>$dateandtime],['c_ams_id'=>$returned[0]->c_ams_id]);
					$ams_id	 = $returned[0]->c_ams_id;
					$col  	 = "a_out";
				} else if ( $returned[0]->p_in == NULL ) {
					// save to p_in 
					$ret = $this->Globalproc->__update("checkexact_ams",["p_in"=>$dateandtime],['c_ams_id'=>$returned[0]->c_ams_id]);
					$ams_id	 = $returned[0]->c_ams_id;
					$col  = "p_in";
				} else if ( $returned[0]->p_out == NULL ) {
					// save to p_out
					$ret = $this->Globalproc->__update("checkexact_ams",["p_out"=>$dateandtime],['c_ams_id'=>$returned[0]->c_ams_id]);
					$ams_id	 = $returned[0]->c_ams_id;
					$col  = "p_out";
				} else {
					// everything is filled up
					$ret = "full";
				}
				*/
				// if ($ret != "full") {
				//	$data['amsid'] 		  = $ams_id; // $returned[0]->c_ams_id;
				//	$data['col']   		  = $col;
				//	$data['time']  		  = $time;
					$ret = true;
					$data['name'] 		  = strtolower($returned[0]->f_name);
					$data['empid']		  = $returned[0]->employee_id;
					$data['designation']  = strtolower($returned[0]->Division_Desc.", ".$returned[0]->position_name);
				// }
				
			}
			
			echo json_encode(["ret"=>$ret,"data"=>$data]);
		}
		
		function getallemps() {
			$sql = "select 
							e.employee_id,
							e.f_name,
							e.id_number,
							d.Division_Desc,
							p.position_name
						from employees as e 
							left join Division as d 
						on e.Division_id = d.Division_Id 
							left join positions as p 
						on e.position_id = p.position_id";
						
			$this->load->model("v2main/Globalproc");
			$q = $this->Globalproc->__getdata($sql);
			
			echo json_encode($q);
		}
		
		function addtime() {
			$time  		 = $this->input->post("info")['time'];
			$pic   		 = $this->input->post("info")['pic'].".png";
			$id    		 = $this->input->post("info")['idno'];
			$date  		 = date("m/d/Y", strtotime($this->input->post("info")['date']));
			$timereflect = $this->input->post("info")['timereflect'];
			
			$this->speciallogin();
			
			/*
			$date 		 = "October 10, 2018";
			$idno 		 = 389;
			$pic  		 = "October82018136PMmertoalvinjayb";
			$time 		 = "1:36 PM";
			$timereflect = "a_out";
			*/
			
			$ams = "select * 
						from checkexact_ams as cea 
							where cea.employee_id = '{$id}' and checkdate = '{$date}'";
			
			$this->load->model("v2main/Globalproc");
			$this->load->model("Ams_model");
			
			$ret = $this->Globalproc->__getdata($ams);
			
			$dateandtime = $date." ".$time;
			$status 	 = null;
			$reflectas   = null;
			
			$amsid  	 = null;
			// $timereflect = "p_in";
			if ($timereflect != null) {
				$this->Ams_model->empid	 	= $id;
				$this->Ams_model->datetime  = $dateandtime;
				$this->Ams_model->checktype = $timereflect; // for ams 
				
				// save to ams 
					$amsid 		= $this->Ams_model->save_check_ams();
				
				// save pic to ams_pix 
					$stat  		= $this->Ams_model->save_amspix($pic,$amsid);
				
				// save to checkexact ::is halted
				// $checkexact 	= null; // set the checkexact variable to null; this is to bypass the conditional statement under this line
				 $checkexact = $this->Ams_model->save_checkexact( $this->Globalproc->tokenizer_leave(date("m/d/Y").$amsid."ams") );
				// ---- bypass 
					if ($checkexact != null) { 
						// save to checkexactlogs 
						$checktype = null;
						switch($timereflect) {
							case "a_in":  $checktype = "C/In";  break;
							case "a_out": $checktype = "C/Out"; break;
							case "p_in":  $checktype = "C/In";  break;
							case "p_out": $checktype = "C/Out"; break;
						}
						
						
						$this->Ams_model->checktype = $checktype;
						$this->Ams_model->tocheck_logs($checkexact); 
					}
				// ---- bypass 
				// variable checkexact passed through here; code block within the comment line was bypassed
					// ----------------- for future updates
				
			} else { // if the timereflect was set to null
				if (count($ret)==0) {
					
					// add new
					$new = [];
					$amspix_save = [];
					if ( strtoupper(date("A", strtotime($time))) == "AM") {
						$reflectas = "a_in";
						$new = ['employee_id' => $id,
								'checkdate'	  => $date,
								'a_in'		  => $dateandtime,
								'remarks'	  => null
								];
						$amspix_save = ["cea_id"=>null,"am_in_snap"=>$pic,"empid"=>$id];
					} else if (strtoupper(date("A", strtotime($time))) == "PM") {
						$reflectas = "p_in";
						$new = ['employee_id' => $id,
								'checkdate'	  => $date,
								'p_in'		  => $dateandtime,
								'remarks'	  => null
								];
						$amspix_save = ["cea_id"=>null,"pm_in_snap"=>$pic,"empid"=>$id];
					}

					$status = $this->Globalproc->__save("checkexact_ams",$new);
					$amsid	= $this->Globalproc->getrecentsavedrecord("checkexact_ams",'c_ams_id')[0]->c_ams_id;
					
					// save to pix table 
						$amspix_save['cea_id'] = $amsid;
						$amspix = $this->Globalproc->__save("amspix",$amspix_save);
					// end saving to pix table
					
					// save to checkexact 
						$checkexact = [
							"employee_id"		=> $id,
							"type_mode"			=> "AMS",
							"modify_by_id"		=> $id,
							"checkdate"			=> $date,
							"date_added"		=> date("m/d/Y h:iA"),
							"is_approved"		=> 1,
							"aprroved_by_id"	=> 0,
							"date_approved"		=> 0,
							"ps_type"			=> 1,
							"leave_id"			=> 0,
							"ps_guard_id"		=> 0,
							"grp_id"			=> $this->Globalproc->tokenizer_leave(date("m/d/Y").$amsid."ams") // change the value of this
						];
						// $amsid_tokenize = $this->Globalproc->tokenizer_leave($exactid);
						$savecheckexact = $this->Globalproc->__save("checkexact",$checkexact);
						$checkexact	= $this->Globalproc->getrecentsavedrecord("checkexact","exact_id")[0]->exact_id;
						
					// save to checkexact_logs
						$c_logs = [
							"exact_id"			=> $checkexact,
							"checktime"			=> $dateandtime,
							"checktype"			=> ($reflectas == "a_in")?"C/In":"C/Out",
							"shift_type"		=> date("A", strtotime($time)),
							"modify_by_id"		=> 0,
							"is_modify"			=> 0,
							"is_delete"			=> 0,
							"date_added"		=> date("m/d/Y h:iA"),
							"date_modify"		=> 0,
							"is_bypass"			=> 0
						];
						$save_checklogs = $this->Globalproc->__save("checkexact_logs",$c_logs);
					
				} else {
					// update 
					$details = [];
					$amsid 	 = $ret[0]->c_ams_id;
					
					$where   = ["c_ams_id"=>$amsid];
					$bypass  = false;
					
					
					$update_tbl = [];
					
					$clogtype = null;
					if ( $ret[0]->a_out != NULL && $ret[0]->p_in == NULL ) { 
						$details['p_in'] 		   = $dateandtime;
						$reflectas 				   = "p_in";
						$clogtype				   = "C/In";
						$update_tbl['pm_in_snap']  = $pic;
					} else if ( $ret[0]->a_in != NULL && $ret[0]->a_out == NULL && $ret[0]->p_in == NULL ) {
						$details['a_out'] 		   = $dateandtime;
						$reflectas 				   = "a_out";
						$clogtype				   = "C/Out";
						$update_tbl['am_out_snap'] = $pic;
					} else if ( $ret[0]->p_in != NULL && $ret[0]->p_out == NULL ) {
						$details['p_out'] 	       = $dateandtime;
						$reflectas 				   = "p_out";
						$clogtype				   = "C/Out";
						$update_tbl['pm_out_snap'] = $pic;
					} else {
						// full 
						$bypass = true;
					}
					
					/*
					$has_ps = $this->ams_model->checkif_has_ps($date,$id);
						
					if ($has_ps != false) {
							// has pass slip
						// echo json_encode(['hasps',$has_ps]);
						//return;
					}
					*/
					
					if(!$bypass) {	
							$status   = $this->Globalproc->__update("checkexact_ams",$details,$where);
							
							// watch this
							$exact_id = $this->Globalproc->gdtf("checkexact",["grp_id"=>$this->Globalproc->tokenizer_leave(date("m/d/Y").$amsid."ams")],"exact_id")[0]->exact_id;
							// end watch
							// save to checkexact_logs
							$c_logs = [
								"exact_id"			=> $exact_id,
								"checktime"			=> $dateandtime,
								"checktype"			=> $clogtype,
								"shift_type"		=> date("A", strtotime($time)),
								"modify_by_id"		=> 0,
								"is_modify"			=> 0,
								"is_delete"			=> 0,
								"date_added"		=> date("m/d/Y h:iA"),
								"date_modify"		=> 0,
								"is_bypass"			=> 0
							];
							
							$save_checklogs = $this->Globalproc->__save("checkexact_logs",$c_logs);
							
							if ($status) {
								// update to pix table 
									$amspix = $this->Globalproc->__update("amspix",$update_tbl,['cea_id'=>$amsid]);
								// end saving to pix table
							}
					}
				}
			}
			
			$ams_time   = $this->Globalproc->gdtf("checkexact_ams",["c_ams_id"=>$amsid],["a_in","a_out","p_in","p_out"]);
			
			$a_timelogs = array_map(function($a){
				$a->a_in  = ($a->a_in != null)?date("h:i A", strtotime($a->a_in)):"----";
				$a->a_out = ($a->a_out != null)?date("h:i A", strtotime($a->a_out)):"----";
				$a->p_in  = ($a->p_in != null)?date("h:i A", strtotime($a->p_in)):"----";
				$a->p_out = ($a->p_out != null)?date("h:i A", strtotime($a->p_out)):"----";
				
				return $a;
			},$ams_time);
			
			echo json_encode([$status,$reflectas,$amsid,$a_timelogs]);
		}
		
		function checkfortimelog() {
			$time  = $this->input->post("info")['time'];
			$id    = $this->input->post("info")['idno'];
			$date  = date("m/d/Y", strtotime($this->input->post("info")['date']));
			
			$this->speciallogin();
			$this->load->model("Ams_model");
			
			$ams = "select * 
						from checkexact_ams as cea 
							where cea.employee_id = '{$id}' and checkdate = '{$date}'";
			
			$this->load->model("v2main/Globalproc");
			
			$ret = $this->Globalproc->__getdata($ams);
		
			$show    = false;
			$what    = null;
			
			$pslog   = [];
			// check if there is a pass slip in the database 
				$rets = $this->Ams_model->checkif_has_ps($date,$id);
			
			if (count($ret) == 0 && date("A",strtotime($time)) == "PM") {
				$show = true;
				$what = "ams";
			} else {
				if (date("H",strtotime($time)) >= 13 && 
					$ret[0]->a_out == null && 
					$ret[0]->p_in == null) {
					$show = true;
					$what = "ams";
				}
			}
			
			// checkif_has_ps($date,$empid)
			if ( $rets[0] != false ) {
				// rets[1] = time_out 
				// rets[2] = time_in
				// has Pass slip with no time out just yet
				$show 				= true;
				$what 				= "ps";
				$pslog['timeout'] 	= $rets[1];
				$pslog['timein']	= $rets[2];
			}
			
		//	echo json_encode( $this->Ams_model->checkif_has_ps($date,$id) );
			 echo json_encode([$show,$what,$rets[0],$pslog]);
			
			// echo json_encode($show);
		}
		
		function saveto_ps() {
			$this->load->model("Ams_model");
			$this->load->model("v2main/Globalproc");
			
			$empid   = $this->input->post("info")["empid"];
			$exactid = $this->input->post("info")["exactid"];
			$date 	 = $this->input->post("info")["date"];
			$time    = $this->input->post("info")["time"];
			
			//$ps_	 = $this->Globalproc->gdtf("checkexact",["exact_id"=>$exactid],["time_out","time_in"]); 
			$ps_sql  = "select ce.time_out, ce.time_in, e.f_name 
							from checkexact as ce JOIN employees as e 
						on ce.employee_id = e.employee_id
							where ce.exact_id = '{$exactid}'";
			$ps_	 = $this->Globalproc->__getdata($ps_sql);
			
			$return_val = [];
			if (count($ps_) == 0) {
				// no pass slip found
			} else {
				$values = [];
				if ($ps_[0]->time_out == null) {
					// save to time out 
					$values['time_out']	   = $return_val['timeout'] = $time;
					$return_val['timein']  = null;
				} else if ($ps_[0]->time_in == null) {
					// save to time in 
					$values['time_in']	= $return_val['timein'] = $time;
					$return_val['timeout'] = $ps_[0]->time_out;
				}
				
				$return_val['fname'] = $ps_[0]->f_name;
			
				$isupdate = $this->Globalproc->__update("checkexact",$values,["exact_id"=>$exactid]);
			
				echo json_encode([$isupdate,$return_val]);
			}
		}
		
		function getamstoday() {
			
			$this->speciallogin();
			
			$date = $this->input->post("info")['date'];
			// $date = date("m/d/Y");
			
			$sql = "select cea.*, e.f_name, ap.* from checkexact_ams as cea 
						left join employees as e 
					on cea.employee_id = e.employee_id 
						left join amspix as ap 
					on cea.c_ams_id = ap.cea_id
					where CONVERT(datetime, cea.checkdate) = '{$date}'";
			
			$this->load->model("v2main/Globalproc");
			$ret = $this->Globalproc->__getdata($sql);
			
			$bigpipe = [];
			foreach($ret as $r) {
				$data = ["amsid"	=> $r->c_ams_id,
						 "name" 	=> $r->f_name,
						 "a_in"		=> ($r->a_in  != NULL) ? date("h:i A", strtotime($r->a_in))." <a href='file:/home/minda/Pictures/amspix/{$r->am_in_snap}' target='_blank' class='smallme fa fa-file-image-o' aria-hidden='true'></a>"  : "",
						 "a_out"	=> ($r->a_out != NULL) ? date("h:i A", strtotime($r->a_out))." <a href='file:/home/minda/Pictures/amspix/{$r->am_out_snap}' target='_blank' class='smallme fa fa-file-image-o' aria-hidden='true'></a>" : "",
						 "p_in"		=> ($r->p_in  != NULL) ? date("h:i A", strtotime($r->p_in))." <a href='file:/home/minda/Pictures/amspix/{$r->pm_in_snap}' target='_blank' class='smallme fa fa-file-image-o' aria-hidden='true'></a>"  : "",
						 "p_out"	=> ($r->p_out != NULL) ? date("h:i A", strtotime($r->p_out))." <a href='file:/home/minda/Pictures/amspix/{$r->pm_out_snap}' target='_blank' class='smallme fa fa-file-image-o' aria-hidden='true'></a>" : ""
						];
				array_push($bigpipe,$data);
			}
			
			echo json_encode($bigpipe);
		}
		
		function speciallogin() {
			$this->session->set_userdata('database_default', 'sqlserver');
			/*
			if ($this->session->userdata('is_logged_in') != TRUE) {
				$user_session = array(
					'employee_id' => 35879821484651321683,
					'username' => "minda",
					'usertype' => "admin",
					'full_name' => "Mindanao Development Authority",
					'first_name' => "MinDa",
					'last_name' => "Baby",
					'biometric_id' => "2sw248g69w598w65w5",
					'area_id' => 1,
					'area_name' => "Philippines",
					'ip_address' => $_SERVER["REMOTE_ADDR"],
					'is_logged_in' => TRUE,
					'database_default' => 'sqlserver',
					'employment_type' => "REGULAR",
					'employee_image' => NULL,
					'level_sub_pap_div' => 1,
					'division_id' => 0,
					'dbm_sub_pap_id' => 0,
					'is_head' => 1,
					'office_division_name' => NULL,
					'position_name' => NULL
				);
				
				$this->session->set_userdata($user_session);
			}
			*/
		}
		
		function settonull() {
			$amsid = $this->input->post("info")['amsid'];
			$field = $this->input->post("info")['field'];
			
			$this->load->model("v2main/Globalproc");
			
			$values = [$field => null];
			$where  = ["c_ams_id" => $amsid];
			
			$update = $this->Globalproc->__update("checkexact_ams",$values,$where);
			
			echo json_encode($update);
		}
		
		function savetoaccom() {
			$data_ = $this->input->post("info")['data'];
			
			// add speciallogin
			$this->speciallogin();
			
			$this->load->model("v2main/Globalproc");
			
			$date__ = date("m/d/Y");
			$accom  = urlencode($data_['accomtext']);
			
			$values = ["date"				=> $date__,
					   "accomplishment"		=> $accom,
					   "user_id"			=> $data_['empid'],
					   "f_signatory"		=> null,
					   "s_signatory"		=> null,
					   "f_action"			=> null,
					   "s_action"			=> null,
					   "coverage_id"		=> null,
					   "spl_grp_id"			=> null];
			
			// check if accom is present 
				$sql 	= "select * from d_accomplishment where user_id = '".$data_['empid']."' and date = '".$date__."'";
				$exist  = $this->Globalproc->__getdata($sql);
				
				if (count($exist) > 0) {
					// existed therefore update 
						$values['accomplishment']  = $exist[0]->accomplishment."<br/>".$accom;
						$save = $this->Globalproc->__update("d_accomplishment",$values,["d_a_ID" => $exist[0]->d_a_ID]);
					// end 
				} else {
					// save new 
					$save = $this->Globalproc->__save("d_accomplishment",$values);
				}
			// end 
			
			
			
			echo json_encode($save);
		}
		
		function test() {
			/*
			$this->load->model("v2main/Globalproc");
		//	echo  date("m/d/Y");
			$a =  $this->Globalproc->getrecentsavedrecord("checkexact_ams", "c_ams_id")[0]->c_ams_id;
			
			echo date("A",strtotime("02:40 PM"));
		//	var_dump($a);
			*/
			
			   # we are a PNG image
			header('Content-type: image/png');
			 
			# we are an attachment (eg download), and we have a name
			header('Content-Disposition: attachment; filename="' . $_GET['name'] .'"');
			 
			#capture, replace any spaces w/ plusses, and decode
			$encoded = $_GET['imgdata'];
			$encoded = str_replace(' ', '+', $encoded);
			$decoded = base64_decode($encoded);
			 
			#write decoded data
			echo $decoded;
		}
		
		function testurl() {
			echo date("n/j/Y",strtotime("January 9, 2019"));
			/*
			// redirect("file:///home/minda/Pictures/amspix/zPNoHG_m","refresh");
			$this->load->model("v2main/Globalproc");
			
			$s   = "select * from employees";
			$sql = $this->Globalproc->__getdata($s);
			
			echo "<table>";
				echo "<thead>";
					echo "<th> ID Number </th>";
					echo "<th> Fullname </th>";
					echo "<th> First Name </th>";
					echo "<th> Middle Name </th>";
					echo "<th> Last Name </th>";
					echo "<th> Employment Status </th>";
					echo "<th> Employment Type </th>";
				echo "</thead>";
				foreach($sql as $q) {
					echo "<tr>";
						echo "<td style='border:1px solid #ccc;'>";
							echo $q->id_number;
						echo "</td>";
						echo "<td style='border:1px solid #ccc;'>";
							echo $q->f_name;
						echo "</td>";
						echo "<td style='border:1px solid #ccc;'>";
							echo $q->firstname;
						echo "</td>";
						echo "<td style='border:1px solid #ccc;'>";
							echo $q->m_name;
						echo "</td>";
						echo "<td style='border:1px solid #ccc;'>";
							echo $q->l_name;
						echo "</td>";
						echo "<td style='border:1px solid #ccc;'>";
							echo ($q->status==1)?"active":"inactive";
						echo "</td>";
						echo "<td style='border:1px solid #ccc;'>";
							echo $q->employment_type;
						echo "</td>";
					echo "</tr>";
				}
			echo "</table>";
			*/
		}
	}

