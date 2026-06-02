<?php

Class Attendance_model extends CI_Model{


		public function __construct(){
			parent::__construct();
			$this->load->model('main/main_model');

		}


		function update_shifts($info){

			$session_database = $this->session->userdata('database_default');
			$DB2 = $this->load->database($session_database, TRUE);

			$shift_id = $info['shift_id'];
			$shift_name = $DB2->escape($info['shift_name']);
			$shift_logs = $info['shift_logs'];

			if(empty($shift_id)){ /* add */


				$query="";
				$query.="INSERT INTO 
						  dbo.shift_mgt
						(
						  shift_name,
						  date_added
						) 
						VALUES (
						  ".$shift_name.",
						  CAST(GETDATE() AS DATETIME)
						);";


				$insert = $DB2->query($query);	

				if($insert){
					$query = $DB2->query("SELECT IDENT_CURRENT('shift_mgt') as last_id");
					$res = $query->result();
					$new_shift_id = $res[0]->last_id;


					foreach ($shift_logs as $row) {

						$shift_type = $DB2->escape($row['shift_type']);
						$type = $DB2->escape($row['type']);
						$time_start = $DB2->escape($row['time_start']);
						$time_exact = $DB2->escape($row['time_exact']);
						$time_flexi_exact = $DB2->escape($row['time_flexi_exact']);
						$time_end = $DB2->escape($row['time_end']);
						$index_shift = $DB2->escape($row['index_shift']);

						$query = "";
						$query.="INSERT INTO 
						  dbo.shift_mgt_logs
						(
						  shift_id,
						  time_start,
						  time_exact,
						  time_flexi_exact,
						  time_end,
						  type,
						  shift_type,
						  index_shift
						) 
						VALUES (
						  ".$new_shift_id.",
						  ".$time_start.",
						  ".$time_exact.",
						  ".$time_flexi_exact.",
						  ".$time_end.",
						  ".$type.",
						  ".$shift_type.",
						  ".$index_shift."
						);";

						$insert_shift_logs = $DB2->query($query);	

					}


					if($insert_shift_logs){
						return $new_shift_id;
					}

				}	

			}else{	/* update */


				$query = "";
				$query = "SELECT 
						  employee_id
						FROM 
						  dbo.employee_schedule
						 WHERE shift_id = $shift_id;";

				$has_assigned_employee = $DB2->query($query);	
				$res = $has_assigned_employee->result();	

				if(count($res) == 0){

					$query = "";
					$query.="UPDATE 
							  dbo.shift_mgt  
							SET 
							  shift_name = ".$shift_name."
							WHERE 
							  shift_id = ".$shift_id.";";


						$update = $DB2->query($query);	

						if($update){

							foreach ($shift_logs as $row) {



								$shift_mgt_logs_id = $DB2->escape($row['shift_mgt_logs_id']);
								$time_start = $DB2->escape($row['time_start']);
								$time_exact = $DB2->escape($row['time_exact']);
								$time_flexi_exact = $DB2->escape($row['time_flexi_exact']);
								$time_end = $DB2->escape($row['time_end']);

								$query = "";
								$query.="UPDATE 
										  dbo.shift_mgt_logs  
										SET 
										  time_start = ".$time_start.",
										  time_exact = ".$time_exact.",
										  time_flexi_exact = ".$time_flexi_exact.",
										  time_end = ".$time_end."
										WHERE  shift_mgt_logs_id = ".$shift_mgt_logs_id.";";


								$update_shift_logs = $DB2->query($query);	
							}


							if($update_shift_logs){
								return $shift_id;
							}

						}


				}else{ /* if exists*/

					return '-1';


				}


			}

		}




		function update_summary_reports($info){
			
			$session_database = $this->session->userdata('database_default');
			$DB2 = $this->load->database($session_database, TRUE);

			$employee_id = $DB2->escape($info['employee_id']);
				  
			$date_start_cover = $DB2->escape($info['date_start_cover']);
			$date_end_cover = $DB2->escape($info['date_end_cover']);
			$tardiness_undertime = $DB2->escape($info['tardiness_undertime']);
			$services_rendered = $DB2->escape($info['services_rendered']);


			$query = '';
			$query.= "INSERT INTO 
					  dbo.dtr_summary_reports
					(
					  employee_id,
					  dtr_coverage,
					  date_start_cover,
					  date_end_cover,
					  tardiness_undertime,
					  services_rendered,
					  leaves_log_id,
					  remarks,
					  is_approved,
					  approved_by,
					  date_submitted,
					  status,
					  is_submitted
					) 	
					VALUES (
					  $employee_id,
					  '',
					  $date_start_cover,
					  $date_end_cover,
					  $tardiness_undertime,
					  $services_rendered,
					  '',
					  '',
					  0,
					  0,
					  CAST(GETDATE() AS DATETIME),
					  0,
					  0
					);
					";

				$insert = $DB2->query($query);	

				if($insert){
					$query = $DB2->query("SELECT IDENT_CURRENT('dtr_summary_reports') as last_id");
					$res = $query->result();
					return $res[0]->last_id;
				}	
		
		}



		function getshifts(){

			$session_database = $this->session->userdata('database_default');
			$DB2 = $this->load->database($session_database, TRUE);

			$query =  $DB2->query("SELECT * FROM shift_mgt sm ORDER BY sm.shift_id ASC"); 
			return $query->result();

		}


		function getshiftslogs($shift_id){

			$session_database = $this->session->userdata('database_default');
			$DB2 = $this->load->database($session_database, TRUE);
			$query =  $DB2->query("SELECT * FROM shift_mgt_logs sm_l  WHERE sm_l.shift_id = '{$shift_id}' ORDER BY sm_l.index_shift ASC"); 
			return $query->result();

		}



		function getemployeeshift($employee_id , $this_date){

			$session_database = $this->session->userdata('database_default');
			$DB2 = $this->load->database($session_database, TRUE);

			$query = "SELECT 
						* 
						FROM employee_schedule es
						LEFT JOIN shift_mgt sm ON sm.shift_id = es.shift_id
						LEFT JOIN shift_mgt_logs sml ON sml.shift_id = sm.shift_id
						WHERE cast ('{$this_date}' as datetime) 
						BETWEEN cast (es.date_sch_started as datetime) AND  cast (es.date_sch_ended as datetime) 
						AND 
						es.employee_id = '{$employee_id}'
						ORDER BY
						sml.index_shift ASC;
					";


			$query =  $DB2->query($query);


			if(count($query->result()) == 0){ /* default to 8*/

				$query_1 = "SELECT 
						*,
						0 as is_temporary
						FROM shift_mgt sm
						LEFT JOIN shift_mgt_logs sml ON sml.shift_id = sm.shift_id
						WHERE sm.shift_id = 1
						ORDER BY
						sml.index_shift ASC ;
					";

					$query_1 =  $DB2->query($query_1);
					return array('result' => $ret = $query_1->result() , 'msg' => 'No time shift, automatically set to the default timeshift');			
					//return array('result' => $ret = 0, 'msg' => 'No time shift, automatically set to the default timeshift');			
			}else{

				return array('result' => $query->result(), 'msg' => '');
			}

		

		}


		function getallemployeeshift(){

			$session_database = $this->session->userdata('database_default');
			$DB2 = $this->load->database($session_database, TRUE);

			$query = "SELECT
						*, 
						CASE DATENAME(MONTH, es.date_sch_started)
						WHEN DATENAME(MONTH, es.date_sch_ended)  THEN  
						LEFT(DATENAME(month, es.date_sch_ended),3) + ' ' +  CAST(DAY(es.date_sch_started) AS VARCHAR(2)) + ' - '  + 
						CAST(DAY(es.date_sch_ended) AS VARCHAR(2)) + ', ' +  CAST(YEAR(es.date_sch_ended) AS VARCHAR(4))
						ELSE 
						LEFT(DATENAME(month, es.date_sch_started),3) + ' ' + CAST(DAY(es.date_sch_started) AS VARCHAR(2)) + ' - '  + 
						LEFT(DATENAME(month, es.date_sch_ended),3) + ' ' + CAST(DAY(es.date_sch_ended) AS VARCHAR(2)) + ', ' + CAST(YEAR(es.date_sch_ended) AS VARCHAR(4))
						END as new_sch_date_cover

						FROM employee_schedule es 
						LEFT JOIN shift_mgt sm ON sm.shift_id = es.shift_id RIGHT JOIN employees e 
						ON e.employee_id = es.employee_id ORDER BY es.employee_id DESC; 
					";


			$query =  $DB2->query($query);

			$result = $this->main_model->array_utf8_encode_recursive($query->result());
			return $result;

		}


		function getattendancelogs(){

			$session_database = $this->session->userdata('database_default');
			$DB2 = $this->load->database($session_database, TRUE);

			$query = "SELECT c.* , e.f_name  FROM checkinout c
						LEFT JOIN employees e ON e.biometric_id = c.biometric_id AND e.area_id = c.area_id
						WHERE  cast (c.checktime as date) = CAST(GETDATE() AS date)
						ORDER BY cast (c.checktime as datetime) ASC; 
											";

			$query =  $DB2->query($query);

			$result = $this->main_model->array_utf8_encode_recursive($query->result());
			return $result;	
		}


		function updateemployeeshift($info){

			$session_database = $this->session->userdata('database_default');
			$DB2 = $this->load->database($session_database, TRUE);

			$employee_ids = $info['employee_ids'];	


			$date_start_cover = $DB2->escape($info['date_start_cover']);
			$date_end_cover = $DB2->escape($info['date_end_cover']);
			$shift_id = $DB2->escape($info['shift_id']);
			$is_temporary = $DB2->escape($info['is_temporary']);


			foreach ($employee_ids as $row) {

				$employee_id = $DB2->escape($row['employee_id']);



				$query = "INSERT INTO 
					  dbo.employee_schedule
					(
					  employee_id,
					  shift_id,
					  date_sch_started,
					  date_sch_ended,
					  is_active,
					  is_temporary
					) 
					VALUES (
					  $employee_id,
					  $shift_id,
					  $date_start_cover,
					  $date_end_cover,
					  1,
					  $is_temporary
					);";


					$insert = $DB2->query($query);	
				
			}


		
			if($insert){
				$query = $this->getallemployeeshift();
				return $query;
			}

		}


		function getholidays($sdate ,$edate){


			$session_database = $this->session->userdata('database_default');
			$DB2 = $this->load->database($session_database, TRUE);
			$query = "SELECT 
						*
						FROM 
						  dbo.holidays
						WHERE 
						CAST (holiday_date as DATETIME) 
						BETWEEN CAST ('{$sdate}' as DATETIME)  AND CAST ('{$edate}' as DATETIME);
					  ";

			$query =  $DB2->query($query);

			return $query->result();


		}



		function updateattachmentsonly($info){

			$session_database = $this->session->userdata('database_default');
			$DB2 = $this->load->database($session_database, TRUE);
			$exact_id = $info['exact_id'];

			$filenames = $info['attachments'] ? $info['attachments'] : NULL;
			
			if (!empty($filenames)) {	
	            $checkFilenames = unserialize('');
				
	            foreach($filenames as $row){
	                ((is_array($checkFilenames)) ? array_push($checkFilenames,$row) : $checkFilenames=array($row));
	            }
	            $attachments = serialize($checkFilenames);
				
	        }else{
	            $attachments = '';
	            $checkFilenames = '';
	        }
			
			$insert_attachments = $DB2->escape($attachments);
			
			$query_attachments = "SELECT attachments  , leave_id FROM checkexact WHERE exact_id = ".$exact_id."";
			
			$query_attachments = $DB2->query($query_attachments);	
			
			$getattachments = $query_attachments->result();
			//var_dump( unserialize($getattachments[0]->attachments) ); return;
			
			if($getattachments[0]->attachments != '' || $getattachments[0]->attachments != NULL || $getattachments[0]->attachments != ' '){

				$filenames_1  	  = unserialize($getattachments[0]->attachments);
				$checkFilenames_1 = unserialize('');
				//echo "hello:".$filenames_1; return;
				if (count($filenames_1) > 0 || $filenames_1 = "") {
					foreach($filenames_1 as $row){
						((is_array($checkFilenames_1)) ? array_push($checkFilenames_1,$row) : $checkFilenames_1=array($row));
					}

					if($checkFilenames != ""){
						 $checkFilenames_1 = array_merge($checkFilenames_1 , $checkFilenames);
					} 

					$attachments_1 = serialize($checkFilenames_1);
					$insert_attachments_1 = $DB2->escape($attachments_1);
				} else {
					$insert_attachments_1 = $insert_attachments;
				}
			}else{
				
				$insert_attachments_1 = $insert_attachments;
			}

				$query = "";
				$query .="UPDATE 
							  dbo.checkexact  
							SET 
							  attachments = ".$insert_attachments_1."
							WHERE 
							  exact_id = ".$exact_id.";";

				$update = $DB2->query($query);	

				if($update){
					$updated_attachments  = "SELECT attachments  FROM checkexact WHERE exact_id = ".$exact_id."";
					$updated_attachments = $DB2->query($updated_attachments);

					$list_of_attachments =  $updated_attachments->result();	

					$files  = unserialize($list_of_attachments[0]->attachments);

					return $files;

				}else{
					return $files = array();
				}



		}


		function get_attemndance_logs_last_update($area_id){

			$session_database = $this->session->userdata('database_default');
			$DB2 = $this->load->database($session_database, TRUE);

			$query = "";
			$query .="SELECT 
							TOP 1
							DATENAME(month, c.checktime) + ' ' +  
							CAST(DAY(c.checktime) AS VARCHAR(2)) + ', ' +  
							CAST(YEAR(c.checktime) AS VARCHAR(4)) + ' ' +
							DATENAME(dw, c.checktime) + ' ' +
							LTRIM(STUFF(RIGHT(CONVERT(VarChar(19), c.checktime, 0), 7), 6, 0, ''))  as last_update,
							c.checktime as checktime
							FROM 
							 dbo.checkinout c
							  WHERE area_id = '{$area_id}' 
							ORDER BY
							CONVERT(DATETIME, c.checktime,101)  
							DESC;
						";
		//	echo $query;
			$query =  $DB2->query($query);

			return $query->result();

		}
		
		function sendleave_email($exactid,$emp_id) {
			// ============================================this function is deprecated=========================================
			$this->load->model("v2main/Globalproc");
			$this->load->model("v2main/Emailtemplate");
				// exact_id
				// type :: insert or update
			
			// exactid:info['exact_id'],empid:info['employee_id']
		
		/*
			if ($exactid == false && $emp_id	== false) {
				$info 	 = $this->input->post("info");
				
				$exactid = $info['exactid'];
				$emp_id  = $info['empid'];
			}
		*/
		
		//	$exactid = 6484;
		//	$emp_id  = 50;
			
			$empdetails = $this->Globalproc->gdtf("employees",['employee_id'=>$emp_id],"*");
			$q 		 	= $this->Globalproc->gdtf("checkexact",['exact_id'=>$exactid],"*");
			
			// leave application status 
			$application_status = $this->Globalproc->gdtf("checkexact_approvals",["exact_id"=>$exactid],"*");
			//var_dump($application_status);
			//return;

			$details = ["to"	  => null,
						"subject" => null,
						"message" => null,
						"from"	  => null];
			
			$ret = false;

			$typemode 	= $q[0]->type_mode;
			$isapproved = null;
			
			if ($application_status[0]->division_chief_is_approved == 0) {
				$isapproved = 0;
			} else {
				if ($application_status[0]->leave_authorized_is_approved == 0) {
					$isapproved = 1;
				}
			}
			
			$approvedby = $q[0]->aprroved_by_id;
			
			if ($typemode == "LEAVE" || $typemode == "PS" || $typemode == "PAF") {
				
				if ($typemode == "LEAVE") {
					$typeofleave   = $q[0]->leave_id;
					$leave_details = $this->Globalproc->gdtf("leaves",['leave_id'=>$q[0]->leave_id],"*");
					$ce_details    = $this->Globalproc->gdtf("checkexact_leave_logs",["exact_id"=>$q[0]->exact_id],"*");
				}
				
				$position 	   = $this->Globalproc->gdtf("positions",['position_id'=>$empdetails[0]->position_id],"*");
				$division 	   = $this->Globalproc->gdtf("Division",['Division_id'=>$empdetails[0]->Division_id],"*");

				// $this->classarray = array_map(array($this, 'dash'), $data);
				
				$type = null;
				$noofdays = null;
				
				if ($typemode == "LEAVE") {
					$type 	  = $leave_details[0]->leave_name ." Leave";
					$noofdays = $ce_details[0]->no_days_applied;
				} else if ($typemode == "PS") {
					$type = "Pass Slip";
					// $noofdays = 
				} else if ($typemode == "PAF") {
					$type = "Personal Attendance Form";
				}
				
				$data = [
					"no_days_applied" => $noofdays,
					"type"			  => $type,
					"specific"		  => null,
					"name"			  => $empdetails[0]->f_name,
					"position"		  => $position[0]->position_name,
					"office"		  => $division[0]->Division_Desc,
					"dateoffiling"	  => $q[0]->date_added,
					"inc_dates"		  => $q[0]->checkdate,
					"link"			  => base_url()."reports/applications/".$q[0]->exact_id."/".$typemode,
					"approvedby"	  => "NOT YET",
					"exactid"		  => $exactid,
					"typemode"		  => $typemode,
					"approvingid"	  => null,
					"token"			  => null,
					"isfinal"		  => "false"
				];
				
				// hash the exact id to md5 then get the characters from 0 to 11
				// - then hash it to md5 and get the characters from 0 to 7
				
				$specifics_vl = [
					1 => "Within the Philippines ",
					2 => "Abroad "
				];
				
				$specifics_sl = [
					1 => "Out Patient",
					2 => "In Hospital"
				];
				
				if ($typemode == "LEAVE") {
					if ($typeofleave == 2) {		// vacation
						$data['specific'] = $specifics_vl[$ce_details[0]->leave_application_details];
					} else if ($typeofleave == 1) { // sick
						$data['specific'] = $specifics_sl[$ce_details[0]->leave_application_details];
					}
				}
				
					if (!empty($q[0]->reasons) || strlen($q[0]->reasons) != 0 || $q[0]->reasons != NULL) {
						$data['specific'] .= " -".$q[0]->reasons;
					}
				
				if ( $isapproved==0 && $approvedby==0 ) {
					
					// send email to division chief
					/*
					$division  = $this->Globalproc->gdtf("employees",
														['employee_id'=>$q[0]->employee_id],
														["Division_id"]);
						
					$div_id    = $division[0]->Division_id;
					
					
					$div_chief = $this->Globalproc->gdtf("employees",
														["Division_id"=>$div_id,
														 "conn"		  =>"and",
														 "is_head"	  =>1],
														['e_signature','employee_id','email_2']);
					*/
					$div_chief = $this->Globalproc->gdtf("employees",
														["employee_id"=>$application_status[0]->division_chief_id],
														['e_signature','employee_id','email_2']);
					
					$data["approvingid"] = $div_chief[0]->employee_id;
					$data['token']		 = $this->Globalproc->tokenizer_leave($data["approvingid"].$exactid);
					
					$details['to'] 	= $div_chief[0]->email_2;
				//	$details['to'] 		= "alvinjay.merto@minda.gov.ph";
					$details['subject'] = "I need your approval for my {$data['type']}";
					$details['from'] 	= "'{$empdetails[0]->firstname} {$empdetails[0]->l_name}'";
					$details['message']	= $this->Emailtemplate->leavetemplate($data);
					
				//	echo $details['message'];
				//	echo $div_chief[0]->email_2;
				 $ret = $this->Globalproc->sendtoemail($details);
					
				} else if ( $isapproved == 1 && $typemode != "PS") {
					// this has been approved by the chief 
					// -- send email to director
					
				//	$app_by  	 = $this->Globalproc->gdtf("checkexact",['exact_id'=>$exactid],['aprroved_by_id']);
					$divchief    = $this->Globalproc->gdtf("employees",['employee_id'=>$application_status[0]->division_chief_id],['f_name']);
					
					$data["approvedby"]	  = $divchief[0]->f_name;
					
				//	$director 	 = $this->Globalproc->gdtf("employees",['employee_id'=>$q[0]->employee_id],['DBM_Pap_id']);
				//	$dbm_pap_id  = $director[0]->DBM_Pap_id;
					
				//	$where		 = " DBM_Pap_id={$dbm_pap_id} and is_head = 1 and Division_id = 0";
					$dir_details = $this->Globalproc->gdtf("employees",
															["employee_id"=>$application_status[0]->leave_authorized_official_id],
															['employee_id','l_name','firstname','email_2','Division_id']);

					$data["approvingid"] = $dir_details[0]->employee_id;
					$data['token']		 = $this->Globalproc->tokenizer_leave($data["approvingid"].$exactid);
					$data["isfinal"]	 = "true";
					
					$details['to'] 		= $dir_details[0]->email_2;
				//	$details['to'] 		= "alvinjay.merto@minda.gov.ph";
					$details['subject'] = "I need your final approval for my {$data['type']}";
					$details['from'] 	= "'{$empdetails[0]->firstname} {$empdetails[0]->l_name}'";
					$details['message']	= $this->Emailtemplate->leavetemplate($data);
					
				// echo $details['message'];
				//	echo $dir_details[0]->email_2;
					$ret = $this->Globalproc->sendtoemail($details);
				
				}
			}
			
			return Array("status"=>$ret,"exactid"=>$exactid);
			// ============================================ this function is deprecated =========================================
		}
		
		function send_email($grp_id, $empid, $type = false) {
		
			$this->load->model("v2main/Globalproc");
			$this->load->model("v2main/Emailtemplate");
			
		//	$this->load->helper('url');
		//	echo json_encode("here");
			
			// employee details ################################################################
				$emp_details = $this->Globalproc->gdtf("employees",
													  ["employee_id" => $empid],
													  ["Division_id","DBM_Pap_id","firstname","l_name"]);
				$emp_fullname = $emp_details[0]->firstname . " " . $emp_details[0]->l_name;
				
			// employee details ################################################################
			
			$division_id = $emp_details[0]->Division_id;
			$dbm_pap_id  = $emp_details[0]->DBM_Pap_id;
				
			$typemode = "OT";
			
			$official_declined = false;
				if ($type == false) {
					
					// get signatories from checkexact approvals table	
					// division 
						$division_sql   = "select * 
										   from checkexact_approvals as cea
										   JOIN employees as e on 
										   cea.division_chief_id = e.employee_id
										   where cea.grp_id = '{$grp_id}'";
						$division  		= $this->Globalproc->__getdata($division_sql);
						
						if ($division_id == 0) {
							$dbm_sql   = "select * 
										   from checkexact_approvals as cea
										   JOIN employees as e on 
										   cea.leave_authorized_official_id = e.employee_id
										   where cea.grp_id = '{$grp_id}'";
							$dbm_data  = $this->Globalproc->__getdata($division_sql);
						
							$division_chief_id 		= $dbm_data[0]->employee_id;
							$division_chief_email 	= $dbm_data[0]->email_2;
							$division_chief_name    = $division[0]->f_name;
						} else if (count($division) > 0) {
							$division_chief_id 		= $division[0]->employee_id;
							$division_chief_email 	= $division[0]->email_2;
							$division_chief_name    = $division[0]->f_name;
						}
					// end division 

					// dbm 
						$dbm_sql = "select * from checkexact_approvals as cea
									JOIN employees as e on 
									cea.leave_authorized_official_id = e.employee_id
									where cea.grp_id = '{$grp_id}'";
						//	echo $dbm_sql;
						$dbm     			= $this->Globalproc->__getdata($dbm_sql);
						$dbm_chief_id 		= $dbm[0]->employee_id;
						$dbm_chief_email 	= $dbm[0]->email_2;
						$dbm_chief_name 	= $dbm[0]->f_name;
					// end dbm
				// end get data 
				
			
				// get the data from the checkexact 
					// $check_exact = $this->Globalproc->gdtf("checkexact",["grp_id" => $grp_id, "conn" => "and", "employee_id"=>$empid],"*");
					$sql = "select l.leave_name as lv_name, ce.*, cll.*,cea.*,
							e.f_name, p.position_name, d.Division_Desc 
							from checkexact as ce
							LEFT JOIN leaves as l on ce.leave_id = l.leave_id
							LEFT JOIN employees as e on ce.employee_id = e.employee_id
							LEFT JOIN positions as p on e.position_id = p.position_id
							LEFT JOIN Division as d on e.Division_id = d.Division_Id
							LEFT JOIN checkexact_leave_logs as cll on cll.exact_id = ce.exact_id
							LEFT JOIN checkexact_approvals as cea on cea.exact_id = ce.exact_id
							WHERE ce.grp_id = '{$grp_id}' and ce.employee_id = '{$empid}'
							";
					$check_exact = $this->Globalproc->__getdata($sql);
				//	var_dump($check_exact);
				//	echo "<br/>";echo "<br/>";echo "<br/>";	
				// end
					
					$typemode = $check_exact[0]->type_mode;
					$details  = [];
					
					
					$approved_by 	   = null;
					$toapproval_person = null;
					
					if ($check_exact[0]->division_chief_is_approved == 0) {
						if ($check_exact[0]->leave_authotrized_remarks == null || $check_exact[0]->leave_authotrized_remarks == "null") {
							if ($check_exact[0]->division_chief_remarks == null || $check_exact[0]->division_chief_remarks == "null") {
								// freshly applied
								$approved_by = ["Approved By:","NOT YET"];
							} else {
								$approved_by = ["status:","DECLINED by {$division_chief_name} with Remarks: '{$check_exact[0]->division_chief_remarks}'"];
								$official_declined = true;
								$toapproval_person = $dbm_chief_id;
							}
						} else {
							$approved_by = ["status:","DECLINED by {$dbm_chief_name} with Remarks: '{$check_exact[0]->leave_authotrized_remarks}'"];
							$official_declined = true;
							$toapproval_person = $dbm_chief_id;
						}
					} else if ($check_exact[0]->division_chief_is_approved == 1) {
						if ($check_exact[0]->leave_authorized_is_approved == 0) {
						//	$ab  = $this->Globalproc->gdtf("employees",['employee_id' => $check_exact[0]->division_chief_id],["f_name"]);
						//	$ab  = $ab[0]->f_name;
							$name_to_appear = $division_chief_name;
							
							if ( $this->Globalproc->is_chief("division",$empid) ) {
								$name_to_appear = "Not yet approved";	
							} else if ( $this->Globalproc->is_chief("director",$empid) ) {
								$name_to_appear = "Not yet approved";
							}
							
							$approved_by = ["Approved By:",$name_to_appear];
						}
					}
				}
				
				$url 		  = base_url();
				$subject_type = null;
				$dateincs 	  = "";
				switch($typemode) {
					case "LEAVE":
						$leave_details_vl = [
							'1' => "Within Philippines",
							'2' => "Abroad"
							];
							
						$leave_details_sl = [
							'1' => "Out Patient",
							'2' => "In Hospital"
 						];
						
						$leave_details_spl = [
							"spl_personal_milestone" 	=> "Personal Milestone",
							"spl_filial_obligations" 	=> "Filial Obligations",
							"spl_personal_transaction"	=> "Personal Transaction",
							"spl_parental_obligations"	=> "Parental Obligations",
							"spl_domestic_emergencies"	=> "Domestic Emergencies",
							"spl_calamity_acc"			=> "Calamity Accident Hospitalization Leave"
						];
						
						$leave_details = $this->Globalproc->gdtf("checkexact_leave_logs",["grp_id"=>$grp_id],['leave_application_details']);

						$details[0] = ["Type of Leave:","LEAVE: ".$check_exact[0]->lv_name];
						$details[1] = ["specific:",[]];
							if ($check_exact[0]->leave_id == 2) {		 // vl
								$details[1][1][] = $leave_details_vl[$check_exact[0]->leave_application_details] . " - " . $check_exact[0]->reasons;
								$subject_type = "Vacation Leave ";
							} else if ($check_exact[0]->leave_id == 1) { // sl
								$details[1][1][] = $leave_details_sl[$check_exact[0]->leave_application_details] . " - " . $check_exact[0]->reasons;
								$subject_type = "Sick Leave ";
							} else if ($check_exact[0]->leave_id == 4) { // spl
								foreach( $leave_details_spl as $key => $val ) {
									if ( $check_exact[0]->$key == 1) {
										$details[1][1][] = $val;
									}
								}
								$subject_type = "Special Leave ";
							} else { // other kind of leave
								$details[1][1][] = $check_exact[0]->reasons;
								$subject_type = "LEAVE ";
							}
							// (($check_exact[0]->leave_name == "Vacation")?$leave_details_vl[$check_exact[0]->leave_id]:$leave_details_sl[$check_exact[0]->leave_id]) . " - " . $check_exact[0]->reasons ]
							
						$details[2] = ["Full Name:",$check_exact[0]->f_name];
						$details[3] = ["Position:",$check_exact[0]->position_name];
						$details[4] = ["Office:",$check_exact[0]->Division_Desc];
						$details[5] = ["Date Filing:",$check_exact[0]->date_added];
						$details[6] = ["Inclusive Dates:", []];

							foreach($check_exact as $ce) {
								$details[6][1][] = $ce->checkdate;
							}
						
						$dateincs   = implode(" ",$details[6][1]);
						$details[7] = ["Number of Working days Applied",count($check_exact)];
						$details[8] = ["View the form","<a href='{$url}/view/form/{$grp_id}'/> View Link </a>"];

						$details[9] = $approved_by;
						break;
					case "PAF":	
							$details[0] = ["Type:","Personal Attendance Form (PAF)"];
							$details[1] = ["Fullname:",$check_exact[0]->f_name];
							$details[2] = ["Date Added:", $check_exact[0]->date_added];
							$details[3] = ["Inclusive Dates:", date("l F d, Y", strtotime($check_exact[0]->checkdate))];
							$details[4] = ["From:",$check_exact[0]->time_in];
							$details[5] = ["To:",$check_exact[0]->time_out];
							$details[6] = ["Reason:", $check_exact[0]->reasons];
							$details[7] = ["Remarks:",$check_exact[0]->remarks];
							
							$details[8] = ["View the form","<a href='{$url}/reports/applications/{$grp_id}'/PAF/> View Link </a>"];
							$details[9] = $approved_by;
								
							$dateincs   = date("l F d, Y", strtotime($check_exact[0]->checkdate));
							$subject_type = "PAF ";
						break;
					case "PS":
							$ps_type = [
								1 => "Official",
								2 => "Personal"
							];
							
							$details[0] = ["Type:","Pass Slip (PS)"];
							$details[1] = ["Fullname:",$check_exact[0]->f_name];
							$details[2] = ["Pass Slip Type:", $ps_type[$check_exact[0]->ps_type]];
							$details[3] = ["Date Added:", $check_exact[0]->date_added];
							$details[4] = ["Inclusive Dates:", date("l F d, Y", strtotime($check_exact[0]->checkdate))];
							$details[5] = ["Reason:", $check_exact[0]->reasons];
							
							$details[6] = ["View the form","<a href='{$url}/reports/applications/{$grp_id}/PS'/> View Link </a>"];
							$details[7] = $approved_by;
								
							$dateincs   = date("l F d, Y", strtotime($check_exact[0]->checkdate));
							$subject_type = "Pass Slip ";
						break;
					case "OB":
							$details[0] = ["Type:","Official Business (OB)"];
							$details[1] = ["Specific:",$check_exact[0]->type_mode_details];
							$details[2] = ["Fullname:",$check_exact[0]->f_name];
							$details[3] = ["Date Added:", $check_exact[0]->date_added];
							$details[4] = ["Inclusive Dates:", date("l F d, Y", strtotime($check_exact[0]->checkdate))];
							$details[5] = ["Reason:", $check_exact[0]->remarks];
							
							$dateincs   = date("l F d, Y", strtotime($check_exact[0]->checkdate));
							$details[6] = $approved_by;
							$subject_type = "Official Business ";
						break;
					case "CTO":
							$details[0] = ["Type:","Compensatory Time-Off (CTO)"];
							$details[1] = ["Fullname:",$check_exact[0]->f_name];
							$details[2] = ["Date Added:", $check_exact[0]->date_added];
							// $details[3] = ["Inclusive Dates:", date("l F d, Y", strtotime($check_exact[0]->checkdate))];
							$details[3] = ["Inclusive Dates:", []];
								
								foreach($check_exact as $ce) {
									$details[3][1][] = date("l F d, Y", strtotime($ce->checkdate));
								}
								
							$dateincs = implode("-",$details[3][1]);
							
							//$details[4] = ["Time Start",$check_exact[0]->time_in];
							//$details[5] = ["Time End",$check_exact[0]->time_out];
							
							$start 		  = date("H", strtotime($check_exact[0]->time_in));
							$end 		  = date("H", strtotime($check_exact[0]->time_out));
							
							$numofhrs     = $end-$start;
							
							if ($numofhrs >= 5) {
								$numofhrs--;
							}
							
							$details[4]	  = ["Number of Hours",$numofhrs];
							
							$details[5]   = ["Remarks:", $check_exact[0]->remarks];
							$details[6]   = ["View the form","<a href='{$url}/view/form/{$grp_id}'/> View Link </a>"];
							$details[7]   = $approved_by;
							$subject_type = "Compensatory Time-Off ";
						break;
					case "OT": // mark OT send email
							
							$sql_ot    = "select *, co.date_added as da from checkexact_ot as co
										  JOIN employees as e on co.employee_id = e.employee_id
										  where checkexact_ot_id = '{$grp_id}'";
							$ot_dets   = $this->Globalproc->__getdata($sql_ot);
							
							$ot_type = ['&nbsp;','Regular Work (RW)','Special Task (ST)'];
							
							$details[] = ["Type:","Overtime Time"];
							$details[] = ["Fullname:", $ot_dets[0]->firstname . " " .$ot_dets[0]->l_name];
							$details[] = ["Date Added:", date("F l d, Y", strtotime($ot_dets[0]->da))];
							$details[] = ["Overtime Type:", $ot_type[$ot_dets[0]->is_ot_type]];
							$details[] = ["Reason for Overtime (RW):", $ot_dets[0]->ot_reasons_if_rw];
							$details[] = ["Requested Time-In:", date("h:i A", strtotime($ot_dets[0]->ot_requested_time_in))];
							$details[] = ["Requested Time-Out:", date("h:i A", strtotime($ot_dets[0]->ot_requested_time_out))];
							$details[] = ["Requested Date of Overtime:", date("F l d, Y", strtotime($ot_dets[0]->ot_checkdate))];
							$details[] = ["Task to be done:", $ot_dets[0]->ot_task_done];
							$details[] = ["Employee Remarks:", $ot_dets[0]->ot_remarks];
							$details[] = ["View the form","<a href='{$url}/reports/applications/{$grp_id}/OT'/> View Link </a>"];
							
							$dateincs  = date("F l d, Y", strtotime($ot_dets[0]->ot_checkdate));
							$subject_type = "Overtime ";
						break;
					
				}

				$sendTo  			 = null;
				$subject 			 = null;
				$approving_person_id = null;
				$isfinal 			 = null;
				
				$proceed_email       = false;
				
				if ($type == false) {
				//	echo "youre here <br/>";
					if ($check_exact[0]->division_chief_is_approved == 0 || $check_exact[0]->division_chief_is_approved == NULL) {
						// freshly applied
						$sendTo 			 = $division_chief_email;
						$approving_person_id = $division_chief_id;
						$isfinal 			 = false;
						$subject 			 = $subject_type.": Needs Approval";
						$proceed_email 		 = true;
						// echo $sendto."initial<br/>";
					} else if ($check_exact[0]->division_chief_is_approved == 1) {
						if ($check_exact[0]->leave_authorized_is_approved == 0) {
							$sendTo 			 = $dbm_chief_email;
							$approving_person_id = $dbm_chief_id;
							$isfinal 			 = true;
							$subject			 = $subject_type.": Needs Approval";
							$proceed_email 		 = true;
						//	echo $sendto."final <br/>";
						}
					}
				} 
				
				if ($official_declined == true) {
					$approving_person_id = $toapproval_person;
				}
				
				if ($typemode == "OT") {
					if ($ot_dets[0]->div_is_approved == 0 || $ot_dets[0]->div_is_approved == null) {
						// freshly applied
						$sendTo 			 = $this->Globalproc->gdtf("employees",["employee_id" => $ot_dets[0]->act_div_chief_id],["email_2"])[0]->email_2;
						$approving_person_id = $ot_dets[0]->act_div_chief_id;
						$isfinal 			 = false;
						$subject 			 = "Overtime: Needs Approval";
						$proceed_email 		 = true;
					} else if ($ot_dets[0]->div_is_approved == 1) {
						if ($ot_dets[0]->act_div_is_approved == 0 || $ot_dets[0]->act_div_is_approved == null) {
							$sendTo 			 = $this->Globalproc->gdtf("employees",["employee_id" => $ot_dets[0]->act_div_a_chief_id],["email_2"])[0]->email_2; 
							$approving_person_id = $ot_dets[0]->act_div_a_chief_id;
							$isfinal 			 = true;
							$subject			 = "Overtime: Needs Approval";
							$proceed_email 		 = true;
						}
					}
				}
				
				/*
				// director level
					if ( $this->Globalproc->is_chief("director",$empid) ) {
						// get the details from the directors employee id and send to respective approving official
						$fordir 	 		 = "select email_2, employee_id from employees where DBM_Pap_id = '1' and is_head = '1' and Division_id = '0'";
						$fordir_data 		 = $this->Globalproc->__getdata($fordir);
						$sendTo 			 = $fordir_data[0]->email_2;
						$approving_person_id = $fordir_data[0]->employee_id;
						$isfinal 			 = true;
						$subject 			 = $subject_type."Needs Approval";
						$proceed_email 		 = true;
					}
				// director level
				*/
				
				$action_details = [
							"grp_id"			  => $grp_id,
							"approving_person_id" => $approving_person_id,
							"token" 		      => $this->Globalproc->tokenizer_leave($approving_person_id.$grp_id),
							"isfinal"			  => $isfinal
							]; 
							
				$template = $this->Emailtemplate->new_leavetemplate($details, $action_details);
				
				if ($proceed_email) { // $emp_fullname // "cc"		=> "merto.alvinjay@gmail.com", ."(".$dateincs.")"
					$sent 	  = $this->Globalproc->sendtoemail(["to"		=> $sendTo,
																"from"		=> $emp_fullname,
																"subject"	=> $subject." ( ".strtoupper($emp_fullname)." )"."(".$dateincs.")",
																"message"	=> $template
																]);
					return $sent;
				}
			
			return true;
			
		}
		
		function checkifholiday($holidays, $this_date){

		//function test(){

			//$this_date = '7/6/2016';

			//$holidays = $this->attendance_model->getholidays('07/01/2016' , '07/16/2016');	



			$holidays_array = array();

			
			foreach ($holidays as $rows) {
				$holidays_array[] = date('n/j/Y', strtotime($rows->holiday_date));;
			}

			if (in_array($this_date, $holidays_array)) {
			   return  true;
			}else{
			   return false;
			}


		}
		
}



