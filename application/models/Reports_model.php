<?php

Class Reports_model extends CI_Model{


		public function __construct(){
			parent::__construct();
			$this->load->model('main/main_model');

		}

		function update_summary_reports($info){
			
			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);


			$employee_id = $DB2->escape($info['employee_id']);
			$dtr_cover_id = $DB2->escape($info['dtr_cover_id']);
				  
			$dtr_coverage = $DB2->escape($info['dtr_coverage']);
			$date_start_cover = $DB2->escape($info['date_start_cover']);
			$date_end_cover = $DB2->escape($info['date_end_cover']);
			$tardiness_undertime = $DB2->escape($info['tardiness_undertime']);
			$services_rendered = $DB2->escape($info['services_rendered']);
			$summary_report_deductions = $DB2->escape($info['summary_report_deductions']);


			$query = '';
			$query.= "INSERT INTO 
					  dbo.dtr_summary_reports
					(
					  employee_id,
					  dtr_cover_id,
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
					  is_submitted,
					  deduction_logs
					) 
					VALUES (
					  $employee_id,
					  $dtr_cover_id,
					  $dtr_coverage,
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
					  0,
					  $summary_report_deductions
					);
					";


				$insert = $DB2->query($query);	

				if($insert){
					$query = $DB2->query("SELECT IDENT_CURRENT('dtr_summary_reports') as last_id");
					$res = $query->result();
					return $res[0]->last_id;
				}	
		
		}





		function get_dtr_summary_reports($info){

			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);

			$dtr_cover_id = $info['dtr_cover_id'];

			$query =  $DB2->query("SELECT CASE DATENAME(MONTH, dsr.date_start_cover)
											 WHEN DATENAME(MONTH, dsr.date_end_cover)  THEN  
											 LEFT(DATENAME(month, dsr.date_end_cover),3) + ' ' +  CAST(DAY(dsr.date_start_cover) AS VARCHAR(2)) + ' - '  + 
											 CAST(DAY(dsr.date_end_cover) AS VARCHAR(2)) + ', ' +  CAST(YEAR(dsr.date_end_cover) AS VARCHAR(4))
											 ELSE 
											 LEFT(DATENAME(month, dsr.date_start_cover),3) + ' ' + CAST(DAY(dsr.date_start_cover) AS VARCHAR(2)) + ' - '  + 
											 LEFT(DATENAME(month, dsr.date_end_cover),3) + ' ' + CAST(DAY(dsr.date_end_cover) AS VARCHAR(2)) + ', ' + CAST(YEAR(dsr.date_end_cover) AS VARCHAR(4))
											END as new_dtr_coverage
											 ,ROW_NUMBER() OVER (ORDER BY e.f_name) as id_count , CASE dsr.tardiness_undertime 
											WHEN '0:00:00' THEN 'none'
											ELSE
											dsr.tardiness_undertime
											END as t_u_reports , 
											e.f_name , e.f_name , dsr.* ,
											p.position_name , e.daily_rate , e.employment_type_date ,
											CASE e.Level_sub_pap_div
											WHEN 'Division' THEN d.Division_Desc
											WHEN 'DBM_Sub_Pap' THEN dsp.DBM_Sub_Pap_Desc
											END as office_division_name
											FROM dtr_summary_reports dsr 
											LEFT JOIN employees e ON e.employee_id = dsr.employee_id 
											LEFT JOIN positions p ON p.position_id = e.position_id 
											LEFT JOIN Division d ON e.Division_id = d.Division_Id
											LEFT JOIN DBM_Sub_Pap dsp ON e.DBM_Pap_id = dsp.DBM_Sub_Pap_id
											WHERE e.employment_type = 'JO' AND dsr.dtr_cover_id = '$dtr_cover_id'  
											ORDER BY e.f_name ASC"); 
			
			$result = $this->main_model->array_utf8_encode_recursive($query->result());
			return $result;

		}




		function get_dtr_summary_reportsjo_notsubmitted($info){
			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);

			$dtr_cover_id = $info['dtr_cover_id'];

			$query =  $DB2->query("SELECT 
									e.employee_id,
									e.f_name,
									'' as id_count,
									'' as dtr_coverage,
									'' as t_u_reports,
									'' as position,
									'' as remarks,
									'' as date_submitted,
									'' as services_rendered,
									'' as id_count
									FROM employees e
									WHERE e.employment_type = 'JO' AND 
									e.employee_id NOT IN (
									                      SELECT 
									                      dsr.employee_id 
									                      FROM dtr_summary_reports dsr 
									                      LEFT JOIN employees es ON es.employee_id = dsr.employee_id 
									                      WHERE es.employment_type = 'JO' 
									                      AND dsr.dtr_cover_id = '$dtr_cover_id' 
														  )
									ORDER BY e.f_name ASC"); 

			$result = $this->main_model->array_utf8_encode_recursive($query->result());
			return $result;
		}


		function checkexact_isapproved($exact_id){

			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);

			$query = $DB2->query("SELECT c.is_approved FROM checkexact c WHERE c.exact_id = $exact_id");

			$res = $query->result();

			return $res[0]->is_approved;

		}


		function get_active_dtrcoverage(){

			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);

			$query = $DB2->query("SELECT * FROM hr_dtr_coverage hdc WHERE hdc.is_active = 1");

			$res = $query->result();

			return $res;

		}

		function update_dtr_coverage($info){

			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);


			$dtr_cover_id = $info['dtr_cover_id'];
			$date_index = $info['date_index'];

			$date_started = '';
			$date_ended = '';
			$date_deadline = '';
			$is_active = '';
			$is_allow_to_submit = '';


			if($info['date_started'] != ''){
				$date_started = 'date_started = '.employment_type.',';
			}

			if($info['date_ended'] != ''){
				$date_ended = 'date_ended = '. $DB2->escape($info['date_ended']).',';
			}

			if($info['date_deadline'] != ''){
				$date_deadline = 'date_deadline = '. $DB2->escape($info['date_deadline']).',';
			}

			if($info['is_active'] != ''){
				$is_active = 'is_active = '. $DB2->escape($info['is_active']).',';
			}

			if($info['is_allow_to_submit'] != ''){
				$is_allow_to_submit = 'is_allow_to_submit = '. $DB2->escape($info['is_allow_to_submit']).',';
			}
			


			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);

			$query = $DB2->query("UPDATE 
							  dbo.hr_dtr_coverage  
							SET 
							  ".$date_started."
							  ".$date_ended."
							  ".$date_deadline."
							  ".$is_active."
							  ".$is_allow_to_submit."
							  date_index = '$date_index'
							WHERE 
							  dtr_cover_id = '$dtr_cover_id'");

			if($query){
				$query = $DB2->query("SELECT * FROM hr_dtr_coverage hdc WHERE hdc.dtr_cover_id = '$dtr_cover_id'");

				$res = $query->result();

				return $res;
			}

			

		}


		function get_remaining_dtr_cover($info){


			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);


			$employee_id = $info['employee_id'];
			$view_all = $info['view_all'];

			if($view_all == 1){

				$query = "";
				$query = "SELECT 
						ISNULL((SELECT  dsr.dtr_cover_id FROM dtr_summary_reports dsr WHERE dsr.dtr_cover_id = hd.dtr_cover_id AND dsr.employee_id = '$employee_id'), 0) as is_submitted,
						hd.* , CASE hd.is_active 
						           WHEN 1      THEN '<span style=color:green; >CURRENT:</span>' 
						           WHEN 0 THEN 'DATE COVERED:' 
						           ELSE ''
						       END + ' '  +  hd.date_started + ' - '+ hd.date_ended  as date_covered_label,
				        CASE DATENAME(MONTH, hd.date_started)
				        WHEN DATENAME(MONTH, hd.date_ended)  THEN  
				        LEFT(DATENAME(month, hd.date_ended),3) + ' ' +  CAST(DAY(hd.date_started) AS VARCHAR(2)) + ' - '  + 
				        CAST(DAY(hd.date_ended) AS VARCHAR(2)) + ', ' +  CAST(YEAR(hd.date_ended) AS VARCHAR(4))
				        ELSE 
				        LEFT(DATENAME(month, hd.date_started),3) + ' ' + CAST(DAY(hd.date_started) AS VARCHAR(2)) + ' - '  + 
				        LEFT(DATENAME(month, hd.date_ended),3) + ' ' + CAST(DAY(hd.date_ended) AS VARCHAR(2)) + ', ' + CAST(YEAR(hd.date_ended) AS VARCHAR(4))
				        END as new_sch_date_cover , 	
				         DATENAME(month, hd.date_deadline)  + ' ' + CAST(DAY(hd.date_deadline) AS VARCHAR(2)) + ', ' + CAST(YEAR(hd.date_deadline) AS VARCHAR(4)) as nwe_date_submission					         
						FROM hr_dtr_coverage hd 
						WHERE 
						hd.employment_type = (SELECT e.employment_type FROM employees e WHERE e.employee_id = '$employee_id') ORDER BY hd.is_active DESC";
			}else{

				$query = "";
				$query = "SELECT 0 as is_submitted, hd.* ,  'DATE COVERED: '+  hd.date_started + ' - '+ hd.date_ended  as date_covered_label  FROM hr_dtr_coverage hd 
						  WHERE hd.dtr_cover_id NOT IN (
						  SELECT 
						  hdc.dtr_cover_id,
				        CASE DATENAME(MONTH, hd.date_started)
				        WHEN DATENAME(MONTH, hd.date_ended)  THEN  
				        LEFT(DATENAME(month, hd.date_ended),3) + ' ' +  CAST(DAY(hd.date_started) AS VARCHAR(2)) + ' - '  + 
				        CAST(DAY(hd.date_ended) AS VARCHAR(2)) + ', ' +  CAST(YEAR(hd.date_ended) AS VARCHAR(4))
				        ELSE 
				        LEFT(DATENAME(month, hd.date_started),3) + ' ' + CAST(DAY(hd.date_started) AS VARCHAR(2)) + ' - '  + 
				        LEFT(DATENAME(month, hd.date_ended),3) + ' ' + CAST(DAY(hd.date_ended) AS VARCHAR(2)) + ', ' + CAST(YEAR(hd.date_ended) AS VARCHAR(4))
				        END as new_sch_date_cover ,
				         DATENAME(month, hd.date_deadline)  + ' ' + CAST(DAY(hd.date_deadline) AS VARCHAR(2)) + ', ' + CAST(YEAR(hd.date_deadline) AS VARCHAR(4)) as nwe_date_submission 						   
						  FROM  hr_dtr_coverage hdc
					      LEFT JOIN dtr_summary_reports dtr ON dtr.dtr_cover_id = hdc.dtr_cover_id 
						  WHERE dtr.employee_id = '$employee_id') AND hd.employment_type = (SELECT e.employment_type FROM employees e WHERE e.employee_id = '$employee_id')
						  ";
			}




			$select = $DB2->query($query);			  

			$res = $select->result();

			return $res;


		}


		function getdtrcoverages($info){

			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);


			$employment_type = $DB2->escape($info['employment_type']);

			
			$query = $DB2->query("SELECT hdc.* , CASE hdc.is_active 
											           WHEN 1  THEN '<span style=color:green>CURRENT:</span>' 
											           WHEN 0 THEN 'DATE COVERED:' 
											           ELSE ''		
											       END + ' '  +  hdc.date_started + ' - '+ hdc.date_ended  as date_covered_label FROM hr_dtr_coverage hdc WHERE hdc.employment_type = ".$employment_type." ORDER BY hdc.date_index DESC");
			$res = $query->result();

			return $res;
		}


		function insertnewdatecover($info){

			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);



			if($info['employment_type'] == 'JO'){


				$dtr_cover_id = $info['dtr_cover_id'];
				$date_ended = $info['date_ended'];


				$date_st = strtotime("+1 days", strtotime($date_ended));
				$date_started =  $DB2->escape(date("m/d/Y", $date_st));


				$date_ed = strtotime("+10 days", strtotime($date_ended));
				$date_ended_1 =  $DB2->escape(date("m/d/Y", $date_ed));


				$date_s = strtotime("+13 days", strtotime($date_ended));
				$date_submmision =  $DB2->escape(date("m/d/Y", $date_s));


			}else if($info['employment_type'] == 'REGULAR'){

				$dtr_cover_id = $info['dtr_cover_id'];
				$date_ended = $info['date_ended'];

				$date_started =  $DB2->escape(date("m/d/Y", strtotime($info['date_started'])));
				$date_ended_1 =  $DB2->escape(date("m/d/Y", strtotime($date_ended)));

				$date_s = strtotime("+4 days", strtotime($date_ended));
				$date_submmision =  $DB2->escape(date("m/d/Y", $date_s));

			}



			$date_index = $DB2->escape($info['date_index']);
			$employment_type = $DB2->escape($info['employment_type']);

			$update =  $DB2->query("UPDATE hr_dtr_coverage SET is_active = 0 WHERE dtr_cover_id = '$dtr_cover_id'");

			if($update){

				$insert = "INSERT INTO 
							  dbo.hr_dtr_coverage
							(
							  date_started,
							  date_ended,
							  date_deadline,
							  employment_type,
							  is_active,
							  is_allow_to_submit,
							  date_index
							) 
							VALUES (
							  ".$date_started.",
							  ".$date_ended_1.",
							  ".$date_submmision.",
							  ".$employment_type.",
							  1,
							  0,
							  ".$date_index."
							);";

					$result = $DB2->query($insert);			  

					if($result){

							$query = $DB2->query("SELECT hdc.* , CASE hdc.is_active 
											           WHEN 1  THEN '<span style=color:green>CURRENT:</span>' 
											           WHEN 0 THEN 'DATE COVERED:' 
											           ELSE ''		
											       END + ' '  +  hdc.date_started + ' - '+ hdc.date_ended  as date_covered_label FROM hr_dtr_coverage hdc WHERE hdc.employment_type = ".$employment_type." ORDER BY hdc.is_active DESC");

							$res = $query->result();

							return $res;
					}

			}


		}



		function getsubpap_divisions_tree_employees($info){
			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);


			$dtr_cover_id = $info['dtr_cover_id'];

			$query =  $DB2->query("EXEC dbo.getemployeestree @dtr_cover_id = '$dtr_cover_id'"); 
			$result = $this->main_model->array_utf8_encode_recursive($query->result());
			return $result;

		}



		function getsubpap_divisions_tree_employees_ams($input_date){
			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);


			$query =  $DB2->query("EXEC dbo.getemployeestreeplantillaams @input_date = '$input_date'"); 
			$result = $this->main_model->array_utf8_encode_recursive($query->result());
			return $result;

		}


		function get_all_applications($info){

			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);


			$ses_employee_id = $info['ses_employee_id'];

			$query =  $DB2->query("EXEC dbo.getallapplications @ses_employee_id = '$ses_employee_id'"); 
			$result = $this->main_model->array_utf8_encode_recursive($query->result());
			return $result;


		}

		function cancel_applications($info){

				$exact_id = $info['exact_id'];

				$session_database = $this->session->userdata('database_default');			
				$DB2 = $this->load->database($session_database, TRUE);


				 $query =  $DB2->query("DELETE FROM checkexact WHERE exact_id = $exact_id;
										DELETE FROM checkexact_approvals WHERE exact_id = $exact_id;
										DELETE FROM checkexact_logs WHERE exact_id = $exact_id;
										DELETE FROM checkexact_leave_logs WHERE exact_id = $exact_id;
										"); 

			 if($query){
			 	return true;
			 }

		}


		function get_approved_ps_applications(){

			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);


			$query = $DB2->query("SELECT
								  CASE 
								  WHEN ce.type_mode = 'PS' THEN ce.type_mode + ' - ' + 
								  CASE 
								   WHEN ce.ps_type = 1 THEN 'OFFICIAL'
								   ELSE 'PERSONAL'
								  END
								  ELSE 
								  ce.type_mode
								  END as new_type_mode,
								  ce.exact_id,
								  ce.type_mode,
								  ce.time_out,
								  ce.time_in,
								  ce.reasons,
								  CAST( ce.date_added  AS VARCHAR) as date_added,
								  e.f_name as fullname
								  FROM checkexact ce
								  LEFT JOIN checkexact_approvals ca ON ca.exact_id = ce.exact_id
								  LEFT JOIN employees e ON e.employee_id = ce.employee_id
								  WHERE ce.type_mode IN ('PS') AND CAST(ce.checkdate AS DATE) = CAST(GETDATE() AS DATE) AND ca.division_chief_is_approved = 1");


			$result = $this->main_model->array_utf8_encode_recursive($query->result());

			return $result;

		}



		function update_time_ps($info){


			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);

			$exact_id = $info['exact_id'];	
			$guard_id = $info['ps_guard_id'];	
			$type = $info['type'];

			if($type == 'out'){
				$set_time = "time_out = (SELECT REPLACE(REPLACE(CONVERT(varchar(15), CAST({fn NOW()} AS TIME), 100), 'P', ' P'), 'A', ' A'))";
			}else{
				$set_time = "time_in = (SELECT REPLACE(REPLACE(CONVERT(varchar(15), CAST({fn NOW()} AS TIME), 100), 'P', ' P'), 'A', ' A'))";
			}

			$query = $DB2->query("UPDATE 
				  dbo.checkexact  
				SET 
				  ".$set_time.",
				  ps_guard_id = '$guard_id'
				WHERE 
				  exact_id = '$exact_id'");

			if($query){

				return TRUE;

			}



		}


		function update_time_ams($info){

			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);

			$c_ams_id = $info['c_ams_id'];
			$employee_id = $info['employee_id'];	
			$column = $info['column'];	
			$value = $info['value'];

			


			if(empty($c_ams_id)){ /* insert */

				

				$query = $DB2->query("INSERT INTO 
									  dbo.checkexact_ams
									(
									  employee_id,
									  checkdate,
									  ".$column."
									) 
									VALUES (
									  ".$employee_id.",
									  CAST({fn NOW()} as date),
									  '".$value."'
									);");


					if($query){
						// insert to checkexact 
							/*
							$this->load->model("v2main/Globalproc");
							$this->load->model("Globalvars");
							
							$proc_emp = $this->Globalvars->employeeid;
							
							$insert_q = [
								"employee_id"     => $employee_id,
								"type_mode"       => "AMS",
								"modify_by_id"    => $proc_emp,
								"checkdate"       => date("m/d/Y"),
								"date_added"      => date("m/d/Y"),
								"is_approved"     => "",
								"aprroved_by_id"  => "",
								"ps_type"  		  => ""
							];
							*/
						// insert to checkexact
						
						$query = $DB2->query("SELECT IDENT_CURRENT('checkexact_ams') as last_id");
						$res = $query->result();

							$ams_id = $res[0]->last_id;

							return $ams_id;
					}

			}else{

				if($value == ""){
					$update_column = $column ." = NULL";
				}else{
					$update_column = $column ." = ". "'$value'";
				}
					
				$query = $DB2->query("UPDATE 
						  dbo.checkexact_ams  
						SET 
						  ".$update_column."
						WHERE 
						  c_ams_id = '$c_ams_id';");

				if($query){
					return $c_ams_id;
				}
			}




		}

		function get_employee_ams($info){

			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);


			$area_id = $info['area_id'];


			$get_query = $DB2->query("SELECT cha.employee_id FROM checkexact_ams cha
								      WHERE CAST(cha.checkdate AS date) = CAST({fn NOW()} as date)");

			$get_result = $this->main_model->array_utf8_encode_recursive($get_query->result());


			$employee_id_list = array();
			$employee_ids = array();
			foreach ($get_result as $row) {
				$employee_ids[]= $row->employee_id;	
			}

				if(count($employee_ids) != 0){
					$employee_id_list = implode(",", $employee_ids);
				}else{
					$employee_id_list = "''";
				}

				


			$query =  $DB2->query("SELECT ams.* 
									FROM (
									      SELECT 
									      CAST({fn NOW()} as date) as 'date',
									      cha.c_ams_id,
									      cha.a_in,
									      cha.a_out,
									      cha.p_in,
									      cha.p_out,
									      cha.remarks,
									      cha.ams_guard_id,
									 	 e.* , e.status as is_status , 
									      a.area_name,
									      e.Division_id as div_id,
									      e.DBM_Pap_id as sub_pap_id,
									      d.Division_Desc as division_name,
									      p.position_name as p_name ,
									      CASE e.Level_sub_pap_div
									      WHEN 'Division' THEN d.Division_Desc
									      WHEN 'DBM_Sub_Pap' THEN dsp.DBM_Sub_Pap_Desc
									      END as office_division_name
									      FROM employees e 
									      LEFT JOIN areas a ON e.area_id =  a.area_id  
									      LEFT JOIN positions p ON e.position_id =  p.position_id
									      LEFT JOIN Division d ON e.Division_id = d.Division_Id
									      LEFT JOIN DBM_Sub_Pap dsp ON e.DBM_Pap_id = dsp.DBM_Sub_Pap_id
									      LEFT JOIN checkexact_ams cha ON e.employee_id = cha.employee_id
									      WHERE e.status = 1 AND a.area_id = '$area_id' AND CAST(cha.checkdate AS date) = CAST({fn NOW()} as date) 
									      UNION ALL
									      SELECT 
									      CAST({fn NOW()} as date) as 'date',
									      NULL as c_ams_id,
									      NULL as a_in,
									      NULL as a_out,
									      NULL as p_in,
									      NULL as p_out,
									      NULL as remarks,
									      NULL as ams_guard_id,
										  e.* , e.status as is_status , 
									      a.area_name,
									      e.Division_id as div_id,
									      e.DBM_Pap_id as sub_pap_id,
									      d.Division_Desc as division_name,
									      p.position_name as p_name ,
									      CASE e.Level_sub_pap_div
									      WHEN 'Division' THEN d.Division_Desc
									      WHEN 'DBM_Sub_Pap' THEN dsp.DBM_Sub_Pap_Desc
									      END as office_division_name
									      FROM employees e 
									      LEFT JOIN areas a ON e.area_id =  a.area_id  
									      LEFT JOIN positions p ON e.position_id =  p.position_id
									      LEFT JOIN Division d ON e.Division_id = d.Division_Id
									    LEFT JOIN DBM_Sub_Pap dsp ON e.DBM_Pap_id = dsp.DBM_Sub_Pap_id
									  	WHERE e.status = 1 AND a.area_id = '$area_id' AND e.employee_id NOT IN ($employee_id_list) /* static */
									  )ams 
									ORDER by ams.f_name ASC
									"); 


			$result = $this->main_model->array_utf8_encode_recursive($query->result());
			return $result;	

		}


		function get_ams_employee($date, $employee_id){

			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);


			$query =  $DB2->query("SELECT 
										checkexact_ams.c_ams_id,
										checkexact_ams.checkdate,
										CONVERT(VARCHAR(8), a_in , 101) + ' ' + STUFF(REPLACE(RIGHT(CONVERT(VarChar(19), a_in, 0), 7), ' ', '0'), 6, 0, ' ') as a_in,
										CONVERT(VARCHAR(8), a_out , 101) + ' ' + STUFF(REPLACE(RIGHT(CONVERT(VarChar(19), a_out, 0), 7), ' ', '0'), 6, 0, ' ') as a_out,
										CONVERT(VARCHAR(8), p_in , 101) + ' ' + STUFF(REPLACE(RIGHT(CONVERT(VarChar(19), p_in, 0), 7), ' ', '0'), 6, 0, ' ') as p_in,
										CONVERT(VARCHAR(8), p_out , 101) + ' ' + STUFF(REPLACE(RIGHT(CONVERT(VarChar(19), p_out, 0), 7), ' ', '0'), 6, 0, ' ') as p_out 
								  FROM checkexact_ams 
								  WHERE employee_id = '$employee_id' AND CAST(checkdate AS date) = CAST('$date' as date) ");

			$result = $this->main_model->array_utf8_encode_recursive($query->result());
			return $result;	

		}



		function get_activities($info){

			$employee_id = $info['employee_id'];

			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);

			$query = $DB2->query("SELECT 
									a.activity_id,
									a.creator_id,
									a.date_added,
									a.description,
									al.is_view,
									c.exact_id,
									(SELECT employees.firstname + ' ' + employees.l_name FROM  employees WHERE employees.employee_id = a.creator_id )  as creator_name,
									(SELECT employees.employee_image  FROM  employees WHERE employees.employee_id = a.creator_id )  as creator_image ,
									al.assign_id,
									(SELECT employees.firstname + ' ' + employees.l_name FROM  employees WHERE employees.employee_id = al.assign_id )  as assign_name,
									(SELECT employees.employee_image  FROM  employees WHERE employees.employee_id = al.assign_id )  as assign_image ,
									c.employee_id as owner_id,
									(SELECT employees.firstname + ' ' + employees.l_name FROM  employees WHERE employees.employee_id = c.employee_id )  as owner_name,
									(SELECT employees.employee_image  FROM  employees WHERE employees.employee_id = c.employee_id )  as owner_image ,
									al.is_view,
									c.type_mode,
									(SELECT 
									  CASE 
									  WHEN c.type_mode = 'PS' THEN c.type_mode + ' - ' + 
									  CASE 
									   WHEN c.ps_type = 1 THEN 'OFFICIAL'
									   ELSE 'PERSONAL'
									  END
									  WHEN c.type_mode = 'LEAVE' THEN c.type_mode + ' - ' + (SELECT l.leave_name FROM leaves l WHERE l.leave_id = c.leave_id)
									  ELSE 
									  c.type_mode
									  END) as new_type_mode
									FROM
									activity a 
									LEFT JOIN activity_logs al on a.activity_id = al.activity_id
									LEFT JOIN checkexact c ON c.exact_id = a.exact_id WHERE al.assign_id = $employee_id ORDER BY al.activity_log_id DESC");


			$result = $this->main_model->array_utf8_encode_recursive($query->result());
			return $result;

			return false;

		}	


		function update_notification($info){
			$employee_id = $info['employee_id'];
			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);


			$query = $DB2->query("UPDATE 
									  dbo.activity_logs  
									SET 
									  is_view = 1
									WHERE 
									  assign_id = '$employee_id' AND is_view != 1");

			if($query){
				return TRUE;
			}
			
		}


		function update_activities($info){

			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);

			$activity_id = $info['activity_id'];

			if($activity_id){ /* insert activity */

				$query = $DB2->query("INSERT INTO 
										  dbo.activity
										(
										  creator_id,
										  desc_type,
										  activity_description,
										  date_added,
										  exact_id,
										  is_all,
										  is_admin
										) 
										VALUES (
										  :creator_id,
										  :desc_type,
										  :activity_description,
										  :date_added,
										  :exact_id,
										  :is_all,
										  :is_admin
										);");


			}else{ /* update activity */

				$query = "";

			}

		}







}








