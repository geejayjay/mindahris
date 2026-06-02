<?php

Class Admin_model extends CI_Model{


		public function __construct(){
			parent::__construct();

			$this->load->model('main/main_model');
			//$this->load->database('sqlserver', TRUE);

		}



		function getemployee_id($biometric_id , $area_id = 1){
			$session_database = $this->session->userdata('database_default');
			$DB2 = $this->load->database($session_database, TRUE);
			$query =  $DB2->query("SELECT employee_id  FROM employees where biometric_id = '{$biometric_id}' AND area_id = '{$area_id}'"); 
			return $query->result();


		}

		

		function date_range($strDateFrom,$strDateTo)
		{
		    $aryRange=array();

		    $iDateFrom=mktime(1,0,0,substr($strDateFrom,5,2),substr($strDateFrom,8,2),substr($strDateFrom,0,4));
		    $iDateTo=mktime(1,0,0,substr($strDateTo,5,2),substr($strDateTo,8,2),substr($strDateTo,0,4));

		    if ($iDateTo>=$iDateFrom)
		    {
		        array_push($aryRange,date('n/j/Y l',$iDateFrom)); // first entry
		        while ($iDateFrom<$iDateTo)
		        {
		            $iDateFrom+=86400; // add 24 hours
		            array_push($aryRange,date('n/j/Y l',$iDateFrom));
		        }
		    }
		    return $aryRange;
		}


		function createDateRangeArray($first, $last, $step = '+1 day', $output_format = 'n/j/Y l' ) {

		    $dates = array();
		    $current = strtotime($first);
		    $last = strtotime($last);

		    while( $current <= $last ) {

		        $dates[] = date($output_format, $current);
		        $current = strtotime($step, $current);
		    }

		    return $dates;
		}




		function getdtrformat(){

			$header = "http://localhost/treport/admin/printprev";
			$footer = "Footer";

			return  array('header' => $header , 'footer' => $footer);
		}


		/* ATTENDANCE MODEL */


		function getareas(){

			$session_database = $this->session->userdata('database_default');
			$DB2 = $this->load->database($session_database, TRUE);
			$query =  $DB2->query("SELECT * FROM areas ORDER BY area_id ASC"); 
			$result = $this->array_utf8_encode_recursive($query->result());
			return $result;

		}



		function getcheckinoutfields(){

			$session_database = $this->session->userdata('database_default');
			$DB2 = $this->load->database($session_database, TRUE);
			$query =  $DB2->query("SELECT COLUMN_NAME as columns , DATA_TYPE as datatype FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'checkinout' AND COLUMN_NAME  IN ('biometric_id','checktime','checktype')"); 
			return $query->result();

		}


		/* EMPLOYEES  MODEL */

		function getemployees(){

				$class = 'btn btn-xs btn-success';
				$session_database = $this->session->userdata('database_default');
				$DB2 = $this->load->database($session_database, TRUE);
				$query =  $DB2->query("SELECT e.status as status_1 ,  e.* , e.status as is_status ,  CASE e.status 
													WHEN 1 THEN '<span style=background-color:#449d44;color:#fff!important;padding:2px;border-radius:2px;>Active</span>'
													ELSE 
													'<span style=background-color:#d9534f;color:#fff!important;padding:2px;border-radius:2px;>Inactive</span>'
													END
													as is_active ,
													a.*,
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
													ORDER BY e.f_name ASC"); 
				$result = $this->array_utf8_encode_recursive($query->result());
				return $result;

		}

			function array_utf8_encode_recursive($dat) 
	        { if (is_string($dat)) { 
	            return utf8_encode($dat); 
	          } 
	          if (is_object($dat)) { 
	            $ovs= get_object_vars($dat); 
	            $new=$dat; 
	            foreach ($ovs as $k =>$v)    { 
	                $new->$k=$this->array_utf8_encode_recursive($new->$k); 
	            } 
	            return $new; 
	          } 
	          
	          if (!is_array($dat)) return $dat; 
	          $ret = array(); 
	          foreach($dat as $i=>$d) $ret[$i] = $this->array_utf8_encode_recursive($d); 
	          return $ret; 
	        } 



		/*test data from sql  import from biometrics */
		function getdailytimerecordss($biometric_id, $area_id,  $employee_id , $sdate , $edate){

			$session_database = $this->session->userdata('database_default');
			$DB2 = $this->load->database($session_database, TRUE);
 
			$jo = date('n/j/Y h:i:s A',strtotime($sdate.' 00:00:01'));
			$ji = date('n/j/Y h:i:s A',strtotime($edate.' 23:59:59'));
			
			// $jo = date('Y-m-d H:i:s',strtotime($sdate.' 00:00:01'));
			// $ji = date('Y-m-d H:i:s',strtotime($edate.' 23:59:59'));
			
		//	echo $jo." to ".$ji;
			
			// CONVERT(VARCHAR(10), cc.checktime, 101)
			$sql = "SELECT * FROM
											(
											  SELECT '1' as logs , '' as leave_code , '0' as exact_id, '0' as grp_id , '0' as exact_log_id,  '0' as  type_mode ,  '' as is_approved , employees.employee_id , checkinout.checktime ,  checkinout.checktype , employees.f_name , '0' as leave_is_halfday
											  FROM checkinout 
											  LEFT JOIN employees ON employees.biometric_id = checkinout.biometric_id
											  WHERE cast(checkinout.checktime as datetime)
											  BETWEEN  cast ('$jo' as datetime) AND  cast ('$ji' as datetime)
											  AND checkinout.biometric_id = '$biometric_id' AND checkinout.area_id = '$area_id'
											  UNION ALL
											  SELECT  '2' as logs , l.leave_code as leave_code , checkexact.exact_id as exact_id, checkexact.grp_id as grp_id , checkexact_logs.exact_logs_id  as exact_log_id ,  checkexact.type_mode  as  type_mode , checkexact.is_approved as is_approved ,  checkexact.employee_id , checkexact_logs.checktime , checkexact_logs.checktype  , '' as  f_name , checkexact.leave_is_halfday FROM checkexact 
											  LEFT JOIN leaves l ON l.leave_id = checkexact.leave_id
											  LEFT JOIN checkexact_logs ON checkexact.exact_id = checkexact_logs.exact_id
											  WHERE cast (checkexact_logs.checktime as datetime) 
											  BETWEEN cast ('$jo' as datetime) AND  cast ('$ji' as datetime)
											  AND checkexact.employee_id = '$employee_id' AND checkexact.is_approved IN (0,1)
											  ) ct
								ORDER BY ct.logs , cast (ct.checktime as datetime) ASC
								";
		//	echo $sql;
			$query = $DB2->query($sql);
			
			$result = $this->array_utf8_encode_recursive($query->result());
			
			return $result;

		}

		function getledgerleaverecords( $employee_id , $sdate , $edate){

			$session_database = $this->session->userdata('database_default');
			$DB2 = $this->load->database($session_database, TRUE);

			$jo = date('n/j/Y h:i:s A',strtotime($sdate.' 00:00:01'));
			$ji = date('n/j/Y h:i:s A',strtotime($edate.' 23:59:59'));


			$query = $DB2->query("SELECT
								    ct.date_added, 
								    ct.exact_id, COUNT(*) as days , 
								    ct.leave_code,
								    ct.is_approved,
								    ct.checkdate,
								    ct.leave_is_halfday
								 
								FROM
								(
								      SELECT
								     	 cc.date_added,
								         cc.exact_id ,
								         cc.leave_code,
								         CONVERT(VARCHAR(10), cc.checktime, 101)as dates,
								         cc.is_approved,
								         cc.checkdate,
								         cc.leave_is_halfday
								                
								      FROM(
								      SELECT  
								          checkexact.date_added , 
								          l.leave_code as leave_code , 
								          checkexact.checkdate,
								          checkexact.exact_id as exact_id ,   
								          checkexact.type_mode  as  type_mode , 
								          checkexact.is_approved as is_approved,  
								          checkexact_logs.checktime ,
								          checkexact_logs.checktype ,
								          checkexact.leave_is_halfday
								       FROM checkexact 
								          LEFT JOIN leaves l ON l.leave_id = checkexact.leave_id
								          LEFT JOIN checkexact_logs ON checkexact.exact_id = checkexact_logs.exact_id
								          WHERE cast (checkexact.date_added as datetime) 
								          BETWEEN cast ('$jo' as datetime) AND  cast ('$ji' as datetime)
								          AND checkexact.employee_id = '$employee_id' AND checkexact.type_mode = 'LEAVE' AND checkexact.is_approved IN (0,1)
								       ) cc
								      GROUP BY
								          CONVERT(VARCHAR(10), cc.checktime, 101) , cc.exact_id , cc.date_added , cc.leave_code , cc.is_approved , cc.checkdate , cc.leave_is_halfday
								) ct

								   
								GROUP BY
								    ct.date_added, ct.exact_id , ct.leave_code , ct.is_approved , ct.checkdate , ct.leave_is_halfday
								HAVING 
								    COUNT(*) >= 1
								    
								ORDER BY 
								   CONVERT(DateTime, ct.date_added,101)  ASC");



			$result = $this->array_utf8_encode_recursive($query->result());
			return $result;

		}







		function getdailyrec($userid, $sdate , $edate){
			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);

			$jo = date('n/j/Y h:i:s A',strtotime($sdate.' 00:00:01'));
			$ji = date('n/j/Y h:i:s A',strtotime($edate.' 23:59:59'));

			$query = $DB2->query("SELECT checkinout.* , employees.f_name
									   FROM checkinout 
									   LEFT JOIN employees ON employees.biometric_id = checkinout.biometric_id
									   WHERE  cast (checkinout.checktime as datetime) 
									   BETWEEN  cast ('$jo' as datetime) AND  cast ('$ji' as datetime)
									   AND checkinout.biometric_id = '$userid' ORDER BY cast (checkinout.checktime as datetime) ASC");

			$result = $this->array_utf8_encode_recursive($query->result());
			return $result;
		}



		function updatecheckexact($info){

			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);


			$employee_id = $DB2->escape($info['employee_id']);
			$type_mode = $DB2->escape($info['type_mode']);
			$remarks = $DB2->escape($info['remarks']);
			$reasons = $DB2->escape($info['reasons']);
			$checkdate = $DB2->escape($info['checkdate']);
			$time_in = $DB2->escape($info['time_in']);
			$time_out = $DB2->escape($info['time_out']);
			$leave_id = $DB2->escape($info['leave_id']);
			$division_chief_id = $DB2->escape($info['division_chief_id']);

			if(isset($info['is_halfday'])){
				$is_halfday = $DB2->escape($info['is_halfday']);
			}else{
				 $is_halfday = 0;	
			}

			if(isset($info['am_pm_select'])){
				$am_pm_select = $DB2->escape($info['am_pm_select']);
			}else{
				$am_pm_select = $DB2->escape("");
			}
			
			$type_mode_details = $DB2->escape($info['type_mode_details']);
			
			$ps_type = $DB2->escape($info['ps_type']);  /* for passslips*/
			
			/* 0 = not yet approved or on progress , 1 = approved , 2 = not approved :: comment as of Anjo*/
			
			// get the div chief id 
			
			$is_approved = "0"; 
			$aprroved_by_id = "0";
			$date_approved = "0";

			$filenames = $info['attachments'] ? $info['attachments'] : NULL;

			if (!empty($filenames)) {
	            $checkFilenames = unserialize('');

	            foreach($filenames as $row){
	                ((is_array($checkFilenames)) ? array_push($checkFilenames,$row) : $checkFilenames=array($row));
	            }
	            $attachments = serialize($checkFilenames);
	        }else{
	            $attachments = NULL;
	            $checkFilenames = '';
	        }

			$insert_attachments = $DB2->escape($attachments);




			/* INSERT */
			if ($info['exact_id'] == 0){


				$query="";

				$query.="INSERT INTO checkexact
									  (employee_id,type_mode,modify_by_id,checkdate,remarks,reasons,attachments,date_added,is_approved,aprroved_by_id,date_approved,ps_type,time_in,time_out,leave_id,type_mode_details,leave_is_halfday, leave_is_am_pm_select)
						  VALUES (".$employee_id.",
						  		  ".$type_mode.",
						  		  ".$employee_id.",
						  		  ".$checkdate.",
						  		  ".$remarks.",
						  		  ".$reasons.",
						  		  ".$insert_attachments.",
						  		  CAST(GETDATE() AS DATETIME),
						  		  ".$is_approved.",
						  		  ".$aprroved_by_id.",
						  		  ".$date_approved.",
						  		  ".$ps_type.",
						  		  ".$time_in.",
						  		  ".$time_out.",
						  		  ".$leave_id.",
						  		  ".$type_mode_details.",
						  		  ".$is_halfday.",
						  		  ".$am_pm_select."
						  		  );";

				$insert = $DB2->query($query);	

				if($insert){
					$query = $DB2->query("SELECT IDENT_CURRENT('checkexact') as last_id");
					$res = $query->result();

					$exact_id = $res[0]->last_id;

					$approvals = $this->updatecheckexactapprovals($exact_id , $info);
				
					if($approvals){

						return array('exact_id' => $exact_id , 'type' => 'insert');
					}
					
				}	

			}

			else /* UPDATE */
			{

				$session_employee_id = $this->session->userdata('employee_id');	

				$exact_id = $DB2->escape($info['exact_id']);


				$filenames = $info['attachments'] ? $info['attachments'] : NULL;


				if (!empty($filenames)) {
		            $checkFilenames = unserialize('');

		            foreach($filenames as $row){
		                ((is_array($checkFilenames)) ? array_push($checkFilenames,$row) : $checkFilenames=array($row));
		            }
		            $attachments = serialize($checkFilenames);
		        }else{
		            $attachments = NULL;
		            $checkFilenames = '';
		        }

				$insert_attachments = $DB2->escape($attachments);


				$query_attachments = "SELECT attachments  , leave_id FROM checkexact WHERE exact_id = ".$exact_id."";
				$query_attachments = $DB2->query($query_attachments);	

				$getattachments = $query_attachments->result();




				if($getattachments[0]->attachments){
					

					$filenames_1  = unserialize($getattachments[0]->attachments);
					$checkFilenames_1 = unserialize('');

					foreach($filenames_1 as $row){
		                ((is_array($checkFilenames_1)) ? array_push($checkFilenames_1,$row) : $checkFilenames_1=array($row));
		            }	

		            if($checkFilenames != ""){
		            	 $checkFilenames_1 = array_merge($checkFilenames_1,$checkFilenames);
		           
		            } 
		            $attachments_1 = serialize($checkFilenames_1);
		            $insert_attachments_1 = $DB2->escape($attachments_1);
				}else{
					$insert_attachments_1 = $insert_attachments;
				}


				$query = "";	

				$query .="UPDATE 
							  dbo.checkexact  
							SET 
							  employee_id = ".$employee_id.",
							  type_mode = ".$type_mode.",
							  type_mode_details = ".$type_mode_details.",
							  modify_by_id = ".$session_employee_id.",
							  checkdate = ".$checkdate.",
							  remarks = ".$remarks.",
							  reasons = ".$reasons.",
							  attachments = ".$insert_attachments_1.",
							  ps_type = ".$ps_type.",
							  time_in = ".$time_in.",
							  time_out = ".$time_out.",
							  leave_id = ".$leave_id.",
							  leave_is_halfday = ".$is_halfday.",
							  leave_is_am_pm_select = ".$am_pm_select."

							WHERE 
							  exact_id = ".$exact_id.";";

				$update = $DB2->query($query);	

				if($update){


						$delete_checkexact_logs = "DELETE FROM checkexact_logs WHERE exact_id = ".$exact_id."";

						$delete_1 = $DB2->query($delete_checkexact_logs);	

						$delete_check_exact_approvals = "DELETE FROM checkexact_approvals WHERE exact_id = ".$exact_id."";

						$delete_2 = $DB2->query($delete_check_exact_approvals);

						if($info['type_mode'] == 'LEAVE'){
						  $delete_leave_logs = "DELETE FROM checkexact_leave_logs WHERE exact_id = ".$exact_id."";
						  $delete_leave = $DB2->query($delete_leave_logs);	
						}

						if($delete_2){

							$approvals = $this->updatecheckexactapprovals($exact_id , $info);

							if($approvals){
								$checkexact_id = $info['exact_id'];
								return array('exact_id' => $checkexact_id , 'type' => 'update');
							}
						}

				}


			}

		}




		function updatecheckexactlogs($exact_id, $info , $exact_id_logs = "") {

			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);


			if($exact_id != "" && $exact_id_logs == ""){  /* INSERT checkexact logs*/

				$data = json_decode(json_encode($info), true);
				
				// get holidays
				$this->load->model("Attendance_model");
				
				
				foreach ($data as $key => $value) {
				
					// $checktime = $DB2->escape($value['checktime']);
					$checktime = $value['checktime'];
					$time_	   = date("h:i A", strtotime($checktime));
					
					$holidays = $this->attendance_model->getholidays( date("m/01/Y",strtotime($checktime)) , date("m/t/Y",strtotime($checktime)) );
					
					if ( strtoupper(date("D", strtotime($checktime))) == "MON" &&
						$this->checkifholiday($holidays, date("n/j/Y",strtotime($checktime))) == false ) {
							
							
								$checktime = date("m/d/Y", strtotime($checktime)); //.' '."8:00 AM";
								
								if ($value['shift_type'] == "AM_START") {
									$checktime .= " 8:00 AM";
								} else if ($value['shift_type'] == "AM_END") {
									$checktime .= " 12:00 PM";
								} else if ($value['shift_type'] == "PM_START") {
									$checktime .= " 1:00 PM";
								} else if ($value['shift_type'] == "PM_END") {
									$checktime .= " 5:00 PM";
								} 
								
							
					}

					if ( strtoupper(date("D",strtotime($checktime))) == "TUE" && 
						$this->checkifholiday($holidays, date("n/j/Y",strtotime($checktime))) == false &&
						$this->checkifholiday($holidays, date('n/j/Y', strtotime('-1 day', strtotime($checktime)))) == true ) {
							$checktime = date("m/d/Y", strtotime($checktime));
							
						if ($value['shift_type'] == "AM_START") {
							$checktime .= " 8:00 AM";
						} else if ($value['shift_type'] == "AM_END") {
							$checktime .= " 12:00 PM";
						} else if ($value['shift_type'] == "PM_START") {
							$checktime .= " 1:00 PM";
						} else if ($value['shift_type'] == "PM_END") {
							$checktime .= " 5:00 PM";
						}
						
						
						
					} 

					if ( strtoupper(date("D",strtotime($checktime))) == "WED" && 
						$this->checkifholiday($holidays, date("n/j/Y",strtotime($checktime))) == false &&
						$this->checkifholiday($holidays, date('n/j/Y', strtotime('-1 day', strtotime($checktime)))) == true && 
						$this->checkifholiday($holidays, date('n/j/Y', strtotime('-2 day', strtotime($checktime)))) == true) {
							$checktime = date("m/d/Y", strtotime($checktime)); // .' '."8:00 AM";
							
							if ($value['shift_type'] == "AM_START") {
								$checktime .= " 8:00 AM";
							} else if ($value['shift_type'] == "AM_END") {
								$checktime .= " 12:00 PM";
							} else if ($value['shift_type'] == "PM_START") {
								$checktime .= " 1:00 PM";
							} else if ($value['shift_type'] == "PM_END") {
								$checktime .= " 5:00 PM";
							}
							
							
							
					} 

					if ( strtoupper(date("D",strtotime($checktime))) == "THU" && 
						$this->checkifholiday($holidays, date("n/j/Y",strtotime($checktime))) == false &&
						$this->checkifholiday($holidays, date('n/j/Y', strtotime('-1 day', strtotime($checktime)))) == true && 
						$this->checkifholiday($holidays, date('n/j/Y', strtotime('-2 day', strtotime($checktime)))) == true &&
						$this->checkifholiday($holidays, date('n/j/Y', strtotime('-3 day', strtotime($checktime)))) == true	) {
							$checktime = date("m/d/Y", strtotime($checktime)); // .' '."8:00 AM";
							
							if ($value['shift_type'] == "AM_START") {
								$checktime .= " 8:00 AM";
							} else if ($value['shift_type'] == "AM_END") {
								$checktime .= " 12:00 PM";
							} else if ($value['shift_type'] == "PM_START") {
								$checktime .= " 1:00 PM";
							} else if ($value['shift_type'] == "PM_END") {
								$checktime .= " 5:00 PM";
							}
							
							
							
					} 

					if ( strtoupper(date("D",strtotime($checktime))) == "FRI" && 
						$this->checkifholiday($holidays, date("n/j/Y",strtotime($checktime))) == false &&
						$this->checkifholiday($holidays, date('n/j/Y', strtotime('-1 day', strtotime($checktime)))) == true && 
						$this->checkifholiday($holidays, date('n/j/Y', strtotime('-2 day', strtotime($checktime)))) == true &&
						$this->checkifholiday($holidays, date('n/j/Y', strtotime('-3 day', strtotime($checktime)))) == true && 
						$this->checkifholiday($holidays, date('n/j/Y', strtotime('-4 day', strtotime($checktime)))) == true ) {
							$checktime = date("m/d/Y", strtotime($checktime)); // .' '."8:00 AM";
							
							if ($value['shift_type'] == "AM_START") {
								$checktime .= " 8:00 AM";
							} else if ($value['shift_type'] == "AM_END") {
								$checktime .= " 12:00 PM";
							} else if ($value['shift_type'] == "PM_START") {
								$checktime .= " 1:00 PM";
							} else if ($value['shift_type'] == "PM_END") {
								$checktime .= " 5:00 PM";
							}
							
					}
				
					if ($value['shift_type'] == "PM" || 
							$value['shift_type'] == "AM") {
								// $a = $DB2->escape($checktime);
								$checktime = date("m/d/Y", strtotime($checktime))." ".$time_;
					}
					
					$checktime  = $DB2->escape($checktime);
					
					$checktype  = $DB2->escape($value['checktype']);
					$shift_type = $DB2->escape($value['shift_type']);
					$modify_by_id = "0"; /* who modify this attendance */
					$date_modified = "0";
					
					// check if existed in the checkexact_approvals
						// if exist, dont update anything into the checkexact_logs  
					
						$query="INSERT INTO checkexact_logs
											  (exact_id,checktime,checktype,shift_type,modify_by_id,is_modify,is_delete,date_added,date_modify)
								  VALUES (".$exact_id.",".$checktime.",".$checktype.",".$shift_type.",".$modify_by_id.",'0', '0',CAST(GETDATE() AS DATETIME),".$date_modified.");";
					
						$insert = $DB2->query($query);	
					
				}

			}

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


		function updateactivitylogs($exact_id , $employee_id , $description = "" ){

			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);


			$query_assignees = $DB2->query("SELECT * FROM checkexact_approvals LEFT JOIN checkexact c ON c.exact_id = '$exact_id' WHERE checkexact_approvals.exact_id = '$exact_id'");
			$assignees = $query_assignees->result();

			$description = $DB2->escape($description);
			$owner_id = $DB2->escape($assignees[0]->employee_id);
			$division_chief_id = $DB2->escape($assignees[0]->division_chief_id);
			$paf_recorded_by_id = $DB2->escape($assignees[0]->paf_recorded_by_id);
			$paf_approved_by_id = $DB2->escape($assignees[0]->paf_approved_by_id);
			$leave_authorized_official_id = $DB2->escape($assignees[0]->leave_authorized_official_id);
			$hrmd_approved_id = $DB2->escape($assignees[0]->hrmd_approved_id);

			$query = "";
			$query .= "INSERT INTO 
					  dbo.activity
					(
					  creator_id,
					  exact_id,
					  date_added,
					  description
					) 
					VALUES (
					  ".$employee_id.",
					  ".$exact_id.",
					  CAST({fn NOW()} as datetime),
					  ".$description."
					);";

			$insert = $DB2->query($query);	

			if($insert){

					$query = $DB2->query("SELECT IDENT_CURRENT('activity') as last_id");
					$res = $query->result();

					$activity_id = $res[0]->last_id;

					if($activity_id){

						$query_logs = "";

						if($employee_id){
							$query_logs .="INSERT INTO dbo.activity_logs(activity_id,assign_id,is_view) VALUES (".$activity_id.",".$employee_id.", 0);";
						}

						if($owner_id == TRUE && $owner_id != $employee_id){
							$query_logs .="INSERT INTO dbo.activity_logs(activity_id,assign_id,is_view) VALUES (".$activity_id.",".$owner_id.", 0);";
						}

						if($assignees[0]->division_chief_id == TRUE && $assignees[0]->division_chief_id != $employee_id){
							$query_logs .="INSERT INTO dbo.activity_logs(activity_id,assign_id,is_view) VALUES (".$activity_id.",".$division_chief_id.", 0);";
						}

						if($assignees[0]->paf_recorded_by_id == TRUE && $assignees[0]->paf_recorded_by_id != $employee_id){
							$query_logs .="INSERT INTO dbo.activity_logs(activity_id,assign_id,is_view) VALUES (".$activity_id.",".$paf_recorded_by_id.", 0);";
						}						

						if($assignees[0]->paf_approved_by_id == TRUE && $assignees[0]->paf_approved_by_id != $employee_id){
							$query_logs .="INSERT INTO dbo.activity_logs(activity_id,assign_id,is_view) VALUES (".$activity_id.",".$paf_approved_by_id.", 0);";
						}						

						if($assignees[0]->leave_authorized_official_id && $assignees[0]->leave_authorized_official_id != $employee_id){
							$query_logs .="INSERT INTO dbo.activity_logs(activity_id,assign_id,is_view) VALUES (".$activity_id.",".$leave_authorized_official_id.", 0);";
						}						

						if($assignees[0]->hrmd_approved_id && $assignees[0]->hrmd_approved_id != $employee_id){
							$query_logs .="INSERT INTO dbo.activity_logs(activity_id,assign_id,is_view) VALUES (".$activity_id.",".$hrmd_approved_id.", 0);";
						}

						$insert_logs = $DB2->query($query_logs);	

						if($insert_logs){

							$query_result = $DB2->query("SELECT * FROM activity_logs WHERE activity_id = '$activity_id'");
							$result = $query_result->result();

							return $result;

						}

					}
			}

		}



		function updatecheckexactapprovals($exact_id , $info , $checkexact_approval_id = ""){

			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);


			$division_chief_id = $DB2->escape($info['division_chief_id']);
			$paf_recorded_by_id = $DB2->escape($info['paf_recorded_by_id']);
			$paf_approved_by_id = $DB2->escape($info['paf_approved_by_id']);
			$leave_authorized_official_id = $DB2->escape($info['leave_authorized_official_id']);
			$hrmd_approved_id = $DB2->escape($info['hrmd_approved_id']);

			if($checkexact_approval_id == ""){

				$query = "";

				$query .="INSERT INTO 
						  checkexact_approvals 
						(		 
						  exact_id,
						  division_chief_id,
						  paf_recorded_by_id,
						  paf_approved_by_id,
						  leave_authorized_official_id,
						  hrmd_approved_id
						) 
						VALUES (
						  ".$exact_id.",
						  ".$division_chief_id.",
						  ".$paf_recorded_by_id.",
						  ".$paf_approved_by_id.",
						  ".$leave_authorized_official_id.",
						  ".$hrmd_approved_id."
						);";	

						$insert = $DB2->query($query);	

						if($insert){
							return true;
						}

			}

		}


		function updatecheckexactleaveslog($exact_id , $info , $checkexact_leave_log_id =""){

			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);


			if($checkexact_leave_log_id == ""){

				$no_days_applied = $DB2->escape($info['no_days_applied']);
				$leave_application_details = $DB2->escape($info['leave_application_details']);
				$spl_personal_milestone = $DB2->escape($info['spl_personal_milestone']);
				$spl_filial_obligations = $DB2->escape($info['spl_filial_obligations']);
				$spl_personal_transaction = $DB2->escape($info['spl_personal_transaction']);
				$spl_parental_obligations = $DB2->escape($info['spl_parental_obligations']);
				$spl_domestic_emergencies = $DB2->escape($info['spl_domestic_emergencies']);
				$spl_calamity_acc = $DB2->escape($info['spl_calamity_acc']);
				$spl_first = $DB2->escape($info['spl_first']);
				$spl_second = $DB2->escape($info['spl_second']);
				$spl_third = $DB2->escape($info['spl_third']);


				$query = "";

				$query .="INSERT INTO 
						  dbo.checkexact_leave_logs
						(
						  exact_id,
						  no_days_applied,
						  leave_application_details,
						  spl_personal_milestone,
						  spl_filial_obligations,
						  spl_personal_transaction,
						  spl_parental_obligations,
						  spl_domestic_emergencies,
						  spl_calamity_acc,
						  spl_first,
						  spl_second,
						  spl_third
						) 
						VALUES (
						  ".$exact_id.",
						  ".$no_days_applied.",
						  ".$leave_application_details.",
						  ".$spl_personal_milestone.",
						  ".$spl_filial_obligations.",
						  ".$spl_personal_transaction.",
						  ".$spl_parental_obligations.",
						  ".$spl_domestic_emergencies.",
						  ".$spl_calamity_acc.",
						  ".$spl_first.",
						  ".$spl_second.",
						  ".$spl_third."
						);";

						$insert = $DB2->query($query);	

						if($insert){
							return true;
						}

			}


		}




		function is_approvedcheckexact($exact_id , $approved_id , $is_approved){

			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);


			$query="UPDATE checkexact SET is_approved ='{$is_approved}' , aprroved_by_id = '{$approved_id}' WHERE exact_id = '{$exact_id}'";

			$update = $DB2->query($query);	

			if($update){
				return true;
			}
		}




		function getcheckexactinfo($exact_id  ,$type = ''){
			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);


			
			if($type == ''){
				$query = "SELECT e.* , ce.* , ca.* , cl.* , (SELECT e.firstname + ' ' + LEFT(e.m_name, 1) + '. ' + e.l_name FROM employees e WHERE e.employee_id = ce.employee_id) as employee_full_name,
				(SELECT e.firstname + ' ' + LEFT(e.m_name, 1) + '. ' + e.l_name FROM employees e WHERE e.employee_id = ca.division_chief_id) as division_chief_full_name ,
				(SELECT e.firstname + ' ' + LEFT(e.m_name, 1) + '. ' + e.l_name FROM employees e WHERE e.employee_id = ca.hrmd_approved_id) as hrmd_approved_full_name ,
				(SELECT e.firstname + ' ' + LEFT(e.m_name, 1) + '. ' + e.l_name FROM employees e WHERE e.employee_id = ca.paf_recorded_by_id) as paf_recorded_by_full_name ,
				(SELECT e.firstname + ' ' + LEFT(e.m_name, 1) + '. ' + e.l_name FROM employees e WHERE e.employee_id = ca.paf_approved_by_id) as paf_approved_by_full_name ,
				(SELECT e.firstname + ' ' + LEFT(e.m_name, 1) + '. ' + e.l_name FROM employees e WHERE e.employee_id = ca.leave_authorized_official_id) as leave_auth_by_full_name,
				(SELECT p.position_name FROM positions p LEFT JOIN employees e ON p.position_id = e.position_id  WHERE e.employee_id = ca.division_chief_id) as div_position ,
				(SELECT e.e_signature FROM employees e WHERE e.employee_id = ca.division_chief_id) as division_chief_e_signature,
				(SELECT e.e_signature FROM employees e WHERE e.employee_id = ca.paf_approved_by_id) as paf_approved_e_signature,
				(SELECT e.e_signature FROM employees e WHERE e.employee_id = ca.leave_authorized_official_id) as leave_auth_e_signature,
				(SELECT e.e_signature FROM employees e WHERE e.employee_id = ca.hrmd_approved_id) as leave_hrmd_e_signature,
				(SELECT e.firstname + ' ' + LEFT(e.m_name, 1) + '. ' + e.l_name  FROM employees e WHERE e.employee_id = ce.ps_guard_id) as guard_name,
				CAST(MONTH(ca.division_date) AS VARCHAR(2)) + '/' +  CAST(DAY(ca.division_date) AS VARCHAR(2)) + '/' +  CAST(YEAR(ca.division_date) AS VARCHAR(4)) + ' ' + LTRIM(STUFF(RIGHT(CONVERT(VarChar(19), ca.division_date , 0), 7), 6, 0, ' '))   as new_division_date,
				CAST(MONTH(ca.paf_date) AS VARCHAR(2)) + '/' +  CAST(DAY(ca.paf_date) AS VARCHAR(2)) + '/' +  CAST(YEAR(ca.paf_date) AS VARCHAR(4)) + ' ' + LTRIM(STUFF(RIGHT(CONVERT(VarChar(19), ca.paf_date , 0), 7), 6, 0, ' '))   as new_paf_date,				
				CAST(MONTH(ca.leave_authorized_date) AS VARCHAR(2)) + '/' +  CAST(DAY(ca.leave_authorized_date) AS VARCHAR(2)) + '/' +  CAST(YEAR(ca.leave_authorized_date) AS VARCHAR(4)) + ' ' + LTRIM(STUFF(RIGHT(CONVERT(VarChar(19), ca.leave_authorized_date , 0), 7), 6, 0, ' '))   as new_leave_auth_date,				
				CAST(MONTH(ca.hrmd_date) AS VARCHAR(2)) + '/' +  CAST(DAY(ca.hrmd_date) AS VARCHAR(2)) + '/' +  CAST(YEAR(ca.hrmd_date) AS VARCHAR(4)) + ' ' + LTRIM(STUFF(RIGHT(CONVERT(VarChar(19), ca.hrmd_date , 0), 7), 6, 0, ' '))   as new_leave_hrmd_date,
				p.position_name ,
				ce.exact_id as c_id,
				CASE e.Level_sub_pap_div
				WHEN 'Division' THEN d.Division_Desc
				WHEN 'DBM_Sub_Pap' THEN dsp.DBM_Sub_Pap_Desc
				END as office_division_name
				FROM checkexact ce 
				LEFT JOIN checkexact_approvals ca ON ca.exact_id = ce.exact_id 
				LEFT JOIN checkexact_leave_logs cl ON cl.exact_id = ce.exact_id  
				LEFT JOIN employees e ON e.employee_id = ce.employee_id 
				LEFT JOIN positions p ON p.position_id = e.position_id 
				LEFT JOIN Division d ON e.Division_id = d.Division_Id
				LEFT JOIN DBM_Sub_Pap dsp ON e.DBM_Pap_id = dsp.DBM_Sub_Pap_id
				 WHERE ce.exact_id = '{$exact_id}'";
			}else{
				$query = "SELECT CONVERT(VARCHAR(5),CONVERT(DATETIME, ce.checktime , 0), 108) as time_24_hour , * FROM checkexact_logs ce WHERE ce.exact_id = '{$exact_id}' ORDER BY exact_logs_id ASC";
			}

			$get = $DB2->query($query);	

			$result = $this->main_model->array_utf8_encode_recursive($get->result());
			return $result;	
		}




		/* leaves */

		function getleaves(){
			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);

			$query = "SELECT * FROM leaves ORDER BY leave_id ASC";
			$get = $DB2->query($query);	

			return $get->result();
		}





		function insertattendancelog($fieldmap,$uploadeddacsv, $areaid){

				set_time_limit(200);

			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);



				$area_id = $areaid;					


				$total = count($uploadeddacsv) + 1;
				$count = 0;
				foreach ($uploadeddacsv as $key => $data){
				$count = 1;	

				$status='';
				$checktime='';
				$biometric_id='';

				$query = '';	
				$query.= 'IF NOT EXISTS (SELECT * FROM checkinout WHERE ';

 
 				   		    foreach ($fieldmap as $row => $d){

	 				   		    $field =  $d['checkinout'];
                        		$header =  $d['csv'];

                        		$$field = $DB2->escape($data[$header]);

				            	$query.= $field.' = '.$$field.' AND ' ;
				   		   	 }  

								$query.='area_id = '.$area_id.' )';



				        	$query.= ' INSERT INTO checkinout
	                                    (';
	        
							   		    foreach ($fieldmap as $row => $r){
							            	 $query.= $r['checkinout'].',';
							   		   	 }

							   		 		 $query.='area_id ) VALUES';
							   		 		 $query.='(';


					                      foreach ($fieldmap as $row => $r){
						        					$query.= $$r['checkinout'].',';
						        		  }

										  $query.=  $DB2->escape($area_id) . '';


							   		 $query.= ');';


					$insert = $DB2->query($query);	

					if($insert){
						$count++;	
					}

				}


				return array('total' => $total , 'count' => '1');

		}


		function insertattendancelogv2($fieldmap,$uploadeddacsv, $areaid){

			//	set_time_limit(200);

				$session_database = $this->session->userdata('database_default');			
				$DB2 = $this->load->database($session_database, TRUE);

				$area_id = $areaid;

				$total = count($uploadeddacsv) + 1;
				$count = 0;
				$query = '';
				$status='';
				$checktime='';
				$biometric_id='';
				
				// var_dump($uploadeddacsv); return;
				// format :: $uploadeddacsv['data']
				foreach ($uploadeddacsv as $data){
				
				// $data = (array) $data;
				
				$count = 1;	
					
				$query.= 'IF NOT EXISTS (SELECT * FROM checkinout WHERE ';
						
 				   		    foreach ($fieldmap as $row => $d){
							//  format: $uploadeddacsv['data'][0]['biometric_id']; ----> old format when the index 'data' save_sync_data_log method is not sent
							//	format: $uploadeddacsv[0]['biometric_id'];	 	   ----> when the index 'data' from save_sync_data_log method  is sent

	 				   		    $field  = $d['checkinout'];
                        		$header = $d['csv'];
								
								$$field = $DB2->escape($data[$header]);
				            	$query .= $field.' = '.$$field.' AND ' ;
							
						//		$query .= $data['fullname'];
						//		$query .= "biometric_id = '".$data['biometric_id']."' AND " ;
						//		$query .= "checktime = '".$data['checktime']."' AND " ;
						//		$query .= "checktype = '".$data['status']."' AND " ;
				   		   	}
						
								$query.='area_id = '.$area_id.' )';

				        	$query.= ' INSERT INTO checkinout
	                                    (';	       
										foreach ($fieldmap as $row => $r){
							            	$query.= $r['checkinout'].',';
							   		   	 }			
							   		 		 $query.='area_id ) VALUES';
							   		 		 $query.='(';
													
					                      foreach ($fieldmap as $row => $r){
						        			$query.= $$r['checkinout'].',';
						        		  }	
													
										 $query.=  $DB2->escape($area_id) . '';

							   		 $query.= ');';
				}
				
			// 	return $query;		
				
				$insert = $DB2->query($query);	
				
				if($insert){
					return array('total' => $total , 'count' => '1');
				}
				
		}




		function getemployeefields(){

			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);

			$query =  $DB2->query("SELECT COLUMN_NAME as columns , DATA_TYPE as datatype FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'employees' AND COLUMN_NAME = 'biometric_id' OR COLUMN_NAME = 'f_name'"); 
			return $query->result();

		}



		function insertemployees($fieldmap,$uploadeddacsv, $areaid){

				$session_database = $this->session->userdata('database_default');			
				$DB2 = $this->load->database($session_database, TRUE);



				$area_id = $areaid;


				$total = count($uploadeddacsv) + 1;
				$count = 0;
				foreach ($uploadeddacsv as $key => $data){
				$count = 1;	

				$query = '';	
				$query.= 'IF NOT EXISTS (SELECT * FROM employees WHERE ';

 
 				   		    foreach ($fieldmap as $row => $d){

	 				   		    $field =  $d['checkinout'];
                        		$header =  $d['csv'];

                        		$$field = $DB2->escape($data[$header]);

				            	$query.= $field.' = '.$$field.' AND ' ;
				   		   	 }  

								$query.='area_id = '.$area_id.' )';



				        	$query.= ' INSERT INTO employees
	                                    (';
	        
							   		    foreach ($fieldmap as $row => $r){
							            	 $query.= $r['checkinout'].',';
							   		   	 }

							   		 		 $query.='area_id ) VALUES';
							   		 		 $query.='(';	


					                      foreach ($fieldmap as $row => $r){
						        					$query.= $$r['checkinout'].',';
						        		  }

										  $query.=  $DB2->escape($area_id) . '';


							   		 $query.= ');';


					$insert = $DB2->query($query);	

					if($insert){
						$count++;	
					}

				}

				return array('total' => $total , 'count' => $count);
			
		}




		function getuserlevel($level){


			if($level == '1'){
				$lvl = 'WHERE level_id = 1';
			}else{
				$lvl = '';
			}


			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);

			$query =  $DB2->query("SELECT * FROM employees $lvl ;"); 

			$result = $this->array_utf8_encode_recursive($query->result());
			return $result;


		}



		public function encodeToUtf8($string) {
		     return mb_convert_encoding($string, "UTF-8", mb_detect_encoding($string, "UTF-8, ISO-8859-1, ISO-8859-15", true));
		}




		function get_employee_id($biometric_id , $area_id = 1){
			$session_database = $this->session->userdata('database_default');			
			$DB2 = $this->load->database($session_database, TRUE);

			$query =  $DB2->query("SELECT e.employee_id FROM employees e WHERE  e.biometric_id = '{$biometric_id}' AND e.area_id = '{$area_id}';"); 
			$res =  $query->result();

			return $res[0]->employee_id;
		}



}
	
